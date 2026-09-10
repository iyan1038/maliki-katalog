<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Favorite Controller
|--------------------------------------------------------------------------
| Member menyimpan/menghapus produk favorit dan melihat daftar favorit.
*/

class Favorite extends Member_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Favorite_m');
		$this->load->model('Product_m');
	}

	/**
	 * Daftar produk favorit member.
	 */
	public function index()
	{
		$user_id = (int) $this->session->userdata('user_id');

		$products = $this->Favorite_m->get_by_user($user_id);

		foreach ($products as $p)
		{
			$p->is_favorited = TRUE;
		}

		$data['title'] = 'Favorit';
		$data['products'] = $products;

		$this->load->view('templates/header', $data);
		$this->load->view('favorite/index', $data);
		$this->load->view('templates/footer');
	}

	/**
	 * Tambah / hapus favorit (POST). Redirect kembali ke halaman asal.
	 */
	public function toggle()
	{
		$product_id = (int) $this->input->post('product_id');

		if ( ! $product_id || ! $this->Product_m->get_by_id($product_id))
		{
			$this->session->set_flashdata('error', 'Produk tidak ditemukan.');
			redirect('catalog');
		}

		$user_id = (int) $this->session->userdata('user_id');

		if ($this->Favorite_m->is_favorited($user_id, $product_id))
		{
			$this->Favorite_m->remove($user_id, $product_id);
			$this->session->set_flashdata('success', 'Produk dihapus dari favorit.');
		}
		else
		{
			$this->Favorite_m->add($user_id, $product_id);
			$this->session->set_flashdata('success', 'Produk ditambahkan ke favorit.');
		}

		$back = $this->input->post('redirect');

		if ($back && strpos($back, base_url()) === 0)
		{
			redirect($back);
		}

		redirect('product/'.$product_id);
	}
}
