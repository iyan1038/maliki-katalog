<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Product Controller
|--------------------------------------------------------------------------
| Halaman detail produk: galeri gambar, info, harga, tombol redirect
| ke marketplace, rating (Fase 4), favorit, dan produk terkait.
*/

class Product extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Product_m');
		$this->load->model('Platform_m');
		$this->load->model('Company_m');
		$this->load->model('Rating_m');
		$this->load->model('Favorite_m');
	}

	public function index($id)
	{
		$id = (int) $id;

		$product = $this->Product_m->get_detail($id);

		if ( ! $product || ! $product->is_active)
		{
			show_404();
		}

		// Naikkan penghitung views.
		$this->Product_m->increment_views($id);

		$logged_in = $this->session->userdata('logged_in');
		$user_id   = (int) $this->session->userdata('user_id');
		$is_member = $logged_in && $this->session->userdata('role') === 'member';

		// Track perilaku 'view' untuk personalisasi.
		if ($is_member)
		{
			$this->load->model('Behavior_m');
			$this->Behavior_m->track($user_id, $id, 'view');
		}

		$data['product']       = $product;
		$data['company']       = $product->company;
		$data['images']        = $product->images;
		$data['kbli']          = $product->kbli;
		$data['platforms']     = $product->platforms;
		$data['all_platforms'] = $this->Platform_m->get_all();
		$data['related']       = $this->Product_m->get_related($id, 4);
		$data['title']         = $product->name;
		$data['is_member']     = $is_member;
		$data['is_favorited']  = $is_member && $this->Favorite_m->is_favorited($user_id, $id);
		$data['my_rating']     = $is_member ? $this->Rating_m->get_by_product_user($id, $user_id) : NULL;
		$data['ratings']       = $this->Rating_m->get_by_product($id);

		$cat = $this->Product_m->get_first_category($id);
		$data['cv_category_id'] = $cat ? (int) $cat->id : NULL;

		foreach ($data['related'] as $rp)
		{
			$rp->is_favorited = $is_member && in_array((int) $rp->id, $this->Favorite_m->get_favorited_ids($user_id), TRUE);
		}

		$this->load->view('templates/header', $data);
		$this->load->view('product/detail', $data);
		$this->load->view('templates/footer');
	}
}
