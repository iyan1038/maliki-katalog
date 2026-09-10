<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Company Controller
|--------------------------------------------------------------------------
| Halaman profil perusahaan publik: info perusahaan + daftar produk.
*/

class Company extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Company_m');
		$this->load->model('Product_m');
		$this->load->model('Category_m');
		$this->load->model('Favorite_m');
	}	protected $allowed_sorts = array('recommended', 'promo', 'rating', 'newest', 'price_asc', 'price_desc');

	public function index($id)
	{
		$id = (int) $id;

		$company = $this->Company_m->get_detail($id);

		if ( ! $company || ! $company->is_active)
		{
			show_404();
		}

		$logged_in = $this->session->userdata('logged_in');
		$is_member = $logged_in && $this->session->userdata('role') === 'member';
		$user_id   = $is_member ? (int) $this->session->userdata('user_id') : NULL;

		$products = $this->Product_m->get_by_company($id);

		$favorited_ids = array();
		if ($is_member)
		{
			$favorited_ids = $this->Favorite_m->get_favorited_ids($user_id);
		}

		foreach ($products as $p)
		{
			$p->is_favorited = in_array((int) $p->id, $favorited_ids, TRUE);
		}

		$data['company']   = $company;
		$data['products']  = $products;
		$data['title']     = $company->name;

		$this->load->view('templates/header', $data);
		$this->load->view('company/index', $data);
		$this->load->view('templates/footer');
	}

	/**
	 * Halaman CV perusahaan publik: profil perusahaan + etalase toko
	 * (baris rekomendasi, filter kategori & urutan, daftar produk).
	 */
	public function cv($id)
	{
		$id = (int) $id;

		$company = $this->Company_m->get_detail($id);

		if ( ! $company || ! $company->is_active)
		{
			show_404();
		}

		$category_id = (int) $this->input->get('cat');
		$row_cat     = (int) $this->input->get('row_cat');
		$sort        = (string) $this->input->get('sort');

		if ( ! in_array($sort, $this->allowed_sorts, TRUE))
		{
			$sort = 'recommended';
		}

		$category_filter = $category_id > 0 ? $category_id : NULL;

		$logged_in = $this->session->userdata('logged_in');
		$is_member = $logged_in && $this->session->userdata('role') === 'member';
		$user_id   = $is_member ? (int) $this->session->userdata('user_id') : NULL;

		$categories = $this->Category_m->get_all(TRUE);

		// Baris rekomendasi: kategori produk yang dikunjungi (row_cat),
		// fallback ke kategori pertama yang punya produk di perusahaan ini.
		$row_products = $this->Product_m->get_by_company_with_categories($id, $row_cat);
		if (empty($row_products))
		{
			$row_cat = NULL;
			$row_products = $this->Product_m->get_by_company_with_categories($id);
		}
		$row_name = ! empty($row_products) ? $row_products[0]->category_name : '';

		// Daftar produk penuh sesuai filter kategori & urutan.
		$products = $this->Product_m->get_by_company($id, $category_filter, $sort);

		$favorited_ids = array();
		if ($is_member)
		{
			$favorited_ids = $this->Favorite_m->get_favorited_ids($user_id);
		}

		foreach ($products as $p)
		{
			$p->is_favorited = in_array((int) $p->id, $favorited_ids, TRUE);
		}

		foreach ($row_products as $p)
		{
			$p->is_favorited = in_array((int) $p->id, $favorited_ids, TRUE);
		}

		$this->load->library('waajo');
		$owner_wa = ( ! empty($company->owner) && ! empty($company->owner->wa_number)) ? $company->owner->wa_number : '';

		$data['company']        = $company;
		$data['categories']     = $categories;
		$data['row_products']   = $row_products;
		$data['row_name']       = $row_name;
		$data['products']       = $products;
		$data['category_id']    = $category_id;
		$data['sort']           = $sort;
		$data['wa_configured']  = (bool) $this->waajo->is_configured();
		$data['owner_wa']       = $owner_wa;
		$data['allowed_sorts']  = $this->allowed_sorts;
		$data['title']          = $company->name.' - Etalase';

		$this->load->view('templates/header', $data);
		$this->load->view('company/cv', $data);
		$this->load->view('templates/footer');
	}

	/**
	 * Kirim pesan "Hubungi" ke pemilik perusahaan via API WAAJO.
	 * Dipanggil via POST (AJAX) dari modal di halaman CV.
	 */
	public function contact()
	{
		$id = (int) $this->input->post('company_id');

		$company = $this->Company_m->get_detail($id);

		if ( ! $company || ! $company->is_active)
		{
			$this->output->set_content_type('application/json');
			$this->output->set_output(json_encode(array('ok' => FALSE, 'message' => 'Perusahaan tidak ditemukan.')));
			return;
		}

		$this->load->library('waajo');

		if ( ! $this->waajo->is_configured())
		{
			$this->output->set_content_type('application/json');
			$this->output->set_output(json_encode(array('ok' => FALSE, 'message' => 'Layanan WhatsApp belum tersedia. Mohon coba lagi nanti.')));
			return;
		}

		$owner_wa = ( ! empty($company->owner) && ! empty($company->owner->wa_number)) ? $company->owner->wa_number : '';

		if ($owner_wa === '')
		{
			$this->output->set_content_type('application/json');
			$this->output->set_output(json_encode(array('ok' => FALSE, 'message' => 'Pemilik perusahaan belum memiliki nomor WhatsApp.')));
			return;
		}

		$this->load->library('form_validation');
		$this->form_validation->set_rules('name', 'Nama', 'trim|required|max_length[100]');
		$this->form_validation->set_rules('phone', 'Nomor WhatsApp', 'trim|required|max_length[20]');
		$this->form_validation->set_rules('message', 'Pesan', 'trim|required|max_length[1000]');

		$this->output->set_content_type('application/json');

		if ($this->form_validation->run() === FALSE)
		{
			$this->output->set_output(json_encode(array('ok' => FALSE, 'message' => 'Mohon lengkapi nama, nomor WhatsApp, dan pesan.')));
			return;
		}

		$name    = $this->input->post('name', TRUE);
		$phone   = $this->input->post('phone', TRUE);
		$message = $this->input->post('message', TRUE);

		$text = '*Halo, saya tertarik dengan produk Anda di E-Katalog*'."\n\n"
			.'Nama: '.$name."\n"
			.'WhatsApp: '.$phone."\n"
			.'Pesan: '.$message."\n\n"
			.'- Dikirim dari '.base_url();

		$result = $this->waajo->send_message($owner_wa, $text);

		if ( ! $result)
		{
			$this->output->set_output(json_encode(array('ok' => FALSE, 'message' => 'Pengiriman pesan gagal. Coba lagi nanti.')));
			return;
		}

		if ($result['status'] === 'failed')
		{
			$this->output->set_output(json_encode(array('ok' => FALSE, 'message' => 'Pesan gagal terkirim ke pemilik. Coba lagi nanti.')));
			return;
		}

		$this->output->set_output(json_encode(array('ok' => TRUE, 'message' => 'Pesan berhasil dikirim ke pemilik.')));
	}
}
