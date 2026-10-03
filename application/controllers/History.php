<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| History Controller
|--------------------------------------------------------------------------
| Member melihat riwayat yang dipecah dua tab: produk yang pernah dibuka
| /diklik dan kata kunci yang pernah dicari, serta dapat menghapusnya satu per
| satu, sebagian (hapus terpilih), atau seluruhnya.
|
| Halaman "pilih beberapa" dinyalakan lewat query string ?select=1 supaya
| tetap bisa dipakai walau JavaScript tidak termuat — JS menambah interaktif
| saja: "pilih semua" + penghitung item terpilih.
|
| Pakai User_Controller (login cukup) supaya admin juga bisa memakai halaman
| ini untuk keperluan tes.
*/

class History extends User_Controller
{
	/** Jumlah produk & kata kunci yang ditampilkan (tanpa pagination). */
	const PRODUCT_LIMIT = 24;
	const KEYWORD_LIMIT = 10;

	/**
	 * Tab yang valid pada query string ?tab=.
	 *
	 * Nilai yang sama juga dipakai sebagai "scope" hapus terpilih
	 * (produk -> user_behaviors, pencarian -> search_logs).
	 */
	protected $allowed_tabs = array('produk', 'pencarian');

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Behavior_m');
	}

	/**
	 * Halaman riwayat member, dipecah dua tab:
	 * - produk     : produk yang pernah dibuka / diklik (user_behaviors)
	 * - pencarian  : kata kunci yang pernah dicari (search_logs)
	 *
	 * Hanya tab aktif yang diambil datanya, supaya tidak sia-sia query.
	 *
	 * ?select=1 menyalakan mode "pilih beberapa": checkbox menggantikan
	 * ikon trash di tiap item. Nilainya dibaca server, bukan lewat JS,
	 * supaya hapus terpilih tetap jalan tanpa JavaScript.
	 */
	public function index()
	{
		$user_id = (int) $this->session->userdata('user_id');

		$tab = (string) $this->input->get('tab', TRUE);

		if ( ! in_array($tab, $this->allowed_tabs, TRUE))
		{
			$tab = 'produk';
		}

		$data['title']       = 'Riwayat';
		$data['tab']         = $tab;
		$data['select_mode'] = $this->input->get('select', TRUE) === '1';

		if ($tab === 'pencarian')
		{
			$data['keywords'] = $this->Behavior_m->get_recent_keywords($user_id, self::KEYWORD_LIMIT);

			foreach ($data['keywords'] as $k)
			{
				$k->ago = ek_time_ago($k->last_used);
			}

			$data['products'] = array();
		}
		else
		{
			$data['products'] = $this->Behavior_m->get_recent_products($user_id, self::PRODUCT_LIMIT);
			$data['keywords'] = array();

			foreach ($data['products'] as $p)
			{
				$p->note = ek_time_ago($p->last_seen);
			}
		}

		// Kosongnya riwayat dihitung per tab, bukan global, supaya empty
		// state di tiap tab jujur menggambarkan isinya sendiri.
		$data['is_empty'] = $tab === 'pencarian'
			? empty($data['keywords'])
			: empty($data['products']);

		$this->load->view('templates/header', $data);
		$this->load->view('history/index', $data);
		$this->load->view('templates/footer');
	}

	/**
	 * Hapus satu produk dari riwayat member — ikon trash di kartu produk
	 * pada halaman "Riwayat" (POST).
	 *
	 * Yang dihapus hanya user_behaviors milik user ini untuk produk itu
	 * (view + click). Wishlist dan visit_count produk tetap utuh.
	 */
	public function delete_product($product_id = 0)
	{
		if ($this->input->method() !== 'post')
		{
			redirect('history?tab=produk');
		}

		$user_id    = (int) $this->session->userdata('user_id');
		$product_id = (int) $product_id;

		if ($product_id && $this->Behavior_m->delete_product_history($user_id, $product_id))
		{
			$this->session->set_flashdata('success', 'Produk dihapus dari riwayat.');
		}
		else
		{
			$this->session->set_flashdata('error', 'Produk itu tidak ada di riwayat.');
		}

		redirect('history?tab=produk');
	}

	/**
	 * Hapus riwayat yang dicentang user (POST).
	 *
	 * Satu endpoint dipakai untuk dua hal:
	 * - hapus terpilih    : items[] berisi banyak nilai
	 * - hapus satu item   : items[] berisi satu nilai (ikon trash kecil di
	 *   tiap badge kata kunci)
	 *
	 * $scope divalidasi terhadap allowed_tabs dan sekaligus menentukan tab
	 * tujuan redirect, jadi tidak ada input yang dipakai untuk redirect.
	 */
	public function delete_items()
	{
		if ($this->input->method() !== 'post')
		{
			redirect('history');
		}

		$scope = (string) $this->input->post('scope', TRUE);

		if ( ! in_array($scope, $this->allowed_tabs, TRUE))
		{
			$scope = 'produk';
		}

		$user_id = (int) $this->session->userdata('user_id');
		$deleted = $this->Behavior_m->delete_items($user_id, $scope, (array) $this->input->post('items', TRUE));

		if ($deleted)
		{
			$this->session->set_flashdata(
				'success',
				$deleted.' '.($scope === 'pencarian' ? 'kata kunci' : 'produk').' dihapus dari riwayat.'
			);
		}
		else
		{
			$this->session->set_flashdata('error', 'Centang minimal satu item sebelum menghapus.');
		}

		redirect('history?tab='.$scope);
	}

	/**
	 * Hapus seluruh riwayat member (POST).
	 *
	 * Hanya user_behaviors & search_logs milik user ini yang terhapus.
	 * Wishlist (tabel favorites) dan visit_count produk TIDAK ikut terhapus.
	 */
	public function clear()
	{
		if ($this->input->method() !== 'post')
		{
			redirect('history');
		}

		$user_id = (int) $this->session->userdata('user_id');

		$this->Behavior_m->clear_history($user_id);

		$this->session->set_flashdata('success', 'Riwayat kamu sudah dihapus.');
		redirect('history');
	}
}
