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

		$this->render('admin/wa/index', $data);
	}

	/**
	 * Kirim promo ke semua member yang punya nomor WA.
	 */
	public function send_promo()
	{
		$product_ids = (array) $this->input->post('product_ids');
		$products    = array();

		if ( ! empty($product_ids))
		{
			$this->db->where_in('id', $product_ids);
			$products = $this->db->where('is_promo', 1)->get('products')->result();
		}

		if (empty($products))
		{
			$this->session->set_flashdata('error', 'Pilih minimal satu produk promo.');
			redirect('admin/wa');
		}

		$members = $this->User_m->get_members_with_wa();

		if (empty($members))
		{
			$this->session->set_flashdata('error', 'Belum ada member dengan nomor WhatsApp terdaftar.');
			redirect('admin/wa');
		}

		$lines = array('*PROMO TERBARU E-KATALOG*', '');
		foreach ($products as $p)
		{
			$price = $p->promo_price !== NULL ? $p->promo_price : $p->price;
			$lines[] = '- '.$p->name.' (Rp '.number_format($price, 0, ',', '.').')';
		}
		$lines[] = '';
		$lines[] = 'Kunjungi: '.base_url();
		$message = implode("\n", $lines);

		$sent = 0;
		foreach ($members as $m)
		{
			$result = $this->waajo->send_message($m->wa_number, $message);

			if ( ! $result)
			{
				$this->Wa_m->log($m->id, $m->wa_number, 'promo', 'failed', 'kredensial belum dikonfigurasi');
				continue;
			}

			$this->Wa_m->log($m->id, $m->wa_number, 'promo', $result['status'], substr($result['body'].' '.$result['error'], 0, 255));
			if ($result['status'] === 'sent')
			{
				$sent++;
			}
		}

		$this->session->set_flashdata('success', 'Pengiriman selesai: '.$sent.' dari '.count($members).' member sukses.');
		redirect('admin/wa');
	}

	/**
	 * Kirim rekomendasi personal ke satu member.
	 */
	public function send_recommendation($user_id)
	{
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

		$lines = array('*Rekomendasi Untuk Anda, '.$user->name.'*', '');
		foreach ($products as $p)
		{
			$price = ($p->is_promo && $p->promo_price !== NULL) ? $p->promo_price : $p->price;
			$lines[] = '- '.$p->name.' (Rp '.number_format($price, 0, ',', '.').')';
		}
		$lines[] = '';
		$lines[] = 'Lihat: '.base_url();
		$message = implode("\n", $lines);

		$result = $this->waajo->send_message($user->wa_number, $message);

		if ( ! $result)
		{
			$this->Wa_m->log($user->id, $user->wa_number, 'rekomendasi', 'failed', 'kredensial belum dikonfigurasi');
			$this->session->set_flashdata('error', 'WAAJO belum dikonfigurasi — log disimpan sebagai failed.');
			redirect('admin/wa');
		}

		$this->Wa_m->log($user->id, $user->wa_number, 'rekomendasi', $result['status'], substr($result['body'].' '.$result['error'], 0, 255));
		$this->session->set_flashdata('success', 'Rekomendasi terkirim (status: '.$result['status'].').');
		redirect('admin/wa');
	}
}
