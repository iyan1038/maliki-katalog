<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Wa Controller
|--------------------------------------------------------------------------
| Kirim promo & rekomendasi produk via WhatsApp (API WAAJO).
| Dibatasi untuk admin.
*/

class Wa extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Wa_m');
		$this->load->model('User_m');
		$this->load->model('Product_m');
		$this->load->library('waajo');
	}

	public function index()
	{
		$data['title'] = 'Kirim WhatsApp';
		$data['active_menu'] = 'wa';
		$data['configured']  = $this->waajo->is_configured();
		$data['members']     = $this->User_m->get_members_with_wa();
		$data['promo_products'] = $this->db
			->where('is_promo', 1)
			->where('is_active', 1)
			->order_by('created_at', 'DESC')
			->get('products')
			->result();
		$data['logs'] = $this->Wa_m->get_all();
		$data['dsp_token'] = $this->dsp_token();

		$this->render('admin/wa/index', $data);
	}

	/**
	 * Uji koneksi ke API WAAJO (tanpa mengirim pesan).
	 */
	public function test_connection()
	{
		if ( ! $this->waajo->is_configured())
		{
			$this->session->set_flashdata('error', 'WAAJO belum dikonfigurasi. Isi application/config/waajo.php.');
			redirect('admin/wa');
		}

		$online = $this->waajo->is_online();

		if ($online['ok'])
		{
			$this->session->set_flashdata('success', 'Koneksi WAAJO OK: '.$online['message']);
		}
		else
		{
			$this->session->set_flashdata('error', 'Koneksi WAAJO gagal: '.$online['message']);
		}

		redirect('admin/wa');
	}

	/**
	 * Kirim promo ke semua member yang punya nomor WA.
	 */
	public function send_promo()
	{
		if ( ! $this->dsp_verify('admin/wa'))
		{
			return;
		}

		$product_ids = (array) $this->input->post('product_ids');
		$products    = array();

		if ( ! empty($product_ids))
		{
			$this->db->select("products.*, (SELECT pi.filename FROM product_images pi WHERE pi.product_id = products.id ORDER BY pi.position ASC LIMIT 1) AS image");
			$this->db->where_in('id', $product_ids);
			$products = $this->db->where('is_promo', 1)->get('products')->result();
		}

		if (empty($products))
		{
			$this->session->set_flashdata('error', 'Pilih minimal satu produk promo.');
			redirect('admin/wa');
		}

		$send_all = (bool) $this->input->post('send_all');
		$user_id  = (int) $this->input->post('user_id');
		$members  = array();

		if ($send_all)
		{
			$members = $this->User_m->get_members_with_wa();

			if (empty($members))
			{
				$this->session->set_flashdata('error', 'Belum ada member dengan nomor WhatsApp terdaftar.');
				redirect('admin/wa');
			}
		}
		elseif ($user_id > 0)
		{
			$user = $this->User_m->get_by_id($user_id);

			if ( ! $user || $user->role !== 'member' || ! $user->wa_number)
			{
				$this->session->set_flashdata('error', 'Member atau nomor WA tidak ditemukan.');
				redirect('admin/wa');
			}

			$members[] = $user;
		}
		else
		{
			$this->session->set_flashdata('error', 'Pilih member tujuan (tombol Kirim Promo di baris member).');
			redirect('admin/wa');
		}

		$media_base = $this->waajo->media_base();
		$sent          = 0;
		$images_sent   = 0;
		$images_skipped = 0;

		foreach ($members as $m)
		{
			$lines = array('*PROMO TERBARU UNTUKMU, '.$m->name.'*', '');
			foreach ($products as $p)
			{
				$price = $p->promo_price !== NULL ? $p->promo_price : $p->price;
				$lines[] = '- '.$p->name.' (Rp '.number_format($price, 0, ',', '.').')';
			}
			$lines[] = '';
			$lines[] = 'Kunjungi: '.base_url();
			$message = implode("\n", $lines);

			$result = $this->waajo->send_message($m->wa_number, $message);

			if ( ! $result)
			{
				$this->Wa_m->log($m->id, $m->wa_number, 'promo', 'failed', 'kredensial belum dikonfigurasi');
				continue;
			}

			$this->Wa_m->log($m->id, $m->wa_number, 'promo', $result['status'], substr($result['message'].' '.$result['body'].' '.$result['error'], 0, 255));

			if ($result['status'] !== 'sent')
			{
				$this->Wa_m->log($m->id, $m->wa_number, 'promo', 'failed', 'Pesan pengantar gagal, gambar dilewati: '.substr($result['message'].' '.$result['body'].' '.$result['error'], 0, 200));
				continue;
			}

			$member_sent = 0;

			foreach ($products as $p)
			{
				if (trim((string) $p->image) === '')
				{
					continue;
				}

				if ($media_base === '')
				{
					$images_skipped++;
					$this->Wa_m->log($m->id, $m->wa_number, 'promo', 'failed', 'Gambar dilewati: waajo_media_url kosong (localhost/tunnel belum aktif).');
					continue;
				}

				$price   = $p->promo_price !== NULL ? $p->promo_price : $p->price;
				$caption = $p->name.' (Rp '.number_format($price, 0, ',', '.').")\n";

				$desc = trim(strip_tags((string) $p->description));
				if ($desc !== '')
				{
					$caption .= (function_exists('mb_substr') ? mb_substr($desc, 0, 120) : substr($desc, 0, 120))."\n";
				}

				$caption .= 'Lihat: '.site_url('product/'.$p->id);

				$file_url = $media_base.'/assets/uploads/products/'.$p->image;
				$res      = $this->waajo->send_file($m->wa_number, $file_url, $caption, 'image');

				if ($res && $res['status'] === 'sent')
				{
					$member_sent++;
					$images_sent++;
				}

				$this->Wa_m->log(
					$m->id,
					$m->wa_number,
					'promo',
					$res ? $res['status'] : 'failed',
					substr($file_url.' :: '.($res ? $res['message'].' '.$res['body'].' '.$res['error'] : 'kredensial belum dikonfigurasi'), 0, 255)
				);
			}

			if ($member_sent > 0)
			{
				$sent++;
			}
			else
			{
				$this->Wa_m->log($m->id, $m->wa_number, 'promo', 'failed', 'Tidak ada gambar promo terkirim untuk member ini.');
			}
		}

		$this->session->set_flashdata('success', 'Pengiriman selesai: '.$sent.' dari '.count($members).' member sukses (gambar: '.$images_sent.' terkirim, '.$images_skipped.' dilewati).');
		redirect('admin/wa');
	}

	/**
	 * Kirim rekomendasi personal ke satu member.
	 */
	public function send_recommendation($user_id)
	{
		if ( ! $this->dsp_verify('admin/wa'))
		{
			return;
		}

		$user = $this->User_m->get_by_id((int) $user_id);

		if ( ! $user || $user->role !== 'member' || ! $user->wa_number)
		{
			$this->session->set_flashdata('error', 'Member atau nomor WA tidak ditemukan.');
			redirect('admin/wa');
		}

		$this->load->library('sorter');
		$products = $this->Product_m->public_query('', NULL, NULL, 'recommended', 3, 0, (int) $user_id);

		if (empty($products))
		{
			$this->session->set_flashdata('error', 'Tidak ada rekomendasi untuk member ini.');
			redirect('admin/wa');
		}

		$images     = 0;
		$skipped    = 0;
		$media_base = $this->waajo->media_base();

		$greeting = '*Rekomendasi untuk Anda, '.$user->name.'*'."\n\n".'Lihat: '.base_url();

		if ( ! $this->waajo->is_configured())
		{
			$this->Wa_m->log($user->id, $user->wa_number, 'rekomendasi', 'failed', 'kredensial belum dikonfigurasi (teks pengantar)');
		}
		else
		{
			$intro = $this->waajo->send_message($user->wa_number, $greeting);
			$this->Wa_m->log(
				$user->id,
				$user->wa_number,
				'rekomendasi',
				$intro ? $intro['status'] : 'failed',
				substr($greeting." :: ".($intro ? $intro['message'].' '.$intro['body'].' '.$intro['error'] : 'kredensial belum dikonfigurasi'), 0, 255)
			);
		}

		foreach ($products as $p)
		{
			if (trim((string) $p->image) === '')
			{
				continue;
			}

			if ($media_base === '')
			{
				$skipped++;
				$this->Wa_m->log($user->id, $user->wa_number, 'rekomendasi', 'failed', 'Gambar dilewati: waajo_media_url kosong (localhost/tunnel belum aktif) — tidak ada pesan terkirim.');
				continue;
			}

			$price   = ($p->is_promo && $p->promo_price !== NULL) ? $p->promo_price : $p->price;
			$caption = $p->name.' (Rp '.number_format($price, 0, ',', '.').")\n";

			$desc = trim(strip_tags((string) $p->description));
			if ($desc !== '')
			{
				$caption .= (function_exists('mb_substr') ? mb_substr($desc, 0, 120) : substr($desc, 0, 120))."\n";
			}

			$caption .= 'Lihat: '.site_url('product/'.$p->id);

			$file_url = $media_base.'/assets/uploads/products/'.$p->image;
			$res      = $this->waajo->send_file($user->wa_number, $file_url, $caption, 'image');

			if ($res && $res['status'] === 'sent')
			{
				$images++;
			}

			$this->Wa_m->log(
				$user->id,
				$user->wa_number,
				'rekomendasi',
				$res ? $res['status'] : 'failed',
				substr($file_url.' :: '.($res ? $res['message'].' '.$res['body'].' '.$res['error'] : 'kredensial belum dikonfigurasi'), 0, 255)
			);
		}

		if ($images === 0)
		{
			if ( ! $this->waajo->is_configured())
			{
				$msg = 'WAAJO belum dikonfigurasi — gambar tidak terkirim.';
			}
			else
			{
				$msg = $skipped > 0
					? 'Tidak ada gambar yang terkirim (media URL kosong / gambar dilewati: '.$skipped.').'
					: 'Tidak ada gambar yang terkirim (produk tanpa gambar).';
			}
			$this->session->set_flashdata('error', $msg);
		}
		else
		{
			$this->session->set_flashdata('success', 'Gambar rekomendasi terkirim: '.$images.($skipped ? ' (dilewati: '.$skipped.')' : '').'.');
		}

		redirect('admin/wa');
	}
}
