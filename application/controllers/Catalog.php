<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Catalog Controller
|--------------------------------------------------------------------------
| Halaman katalog publik (mirip Tokopedia): grid produk, pencarian,
| filter platform marketplace, sorting, dan pagination.
*/

class Catalog extends CI_Controller
{
	protected $allowed_sorts = array('recommended', 'promo', 'rating', 'newest', 'price_asc', 'price_desc');

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Product_m');
		$this->load->model('Platform_m');
		$this->load->model('Category_m');
		$this->load->model('Banner_m');
		$this->load->model('Favorite_m');
		$this->load->model('Behavior_m');
	}

	public function index()
	{
		$search      = trim((string) $this->input->get('q', TRUE));
		$platform_id = (int) $this->input->get('platform');
		$category_id = (int) $this->input->get('cat');
		$sort        = (string) $this->input->get('sort');
		$page        = max(1, (int) $this->input->get('page'));

		if ( ! in_array($sort, $this->allowed_sorts, TRUE))
		{
			$sort = 'recommended';
		}

		$per_page = 20;
		$offset   = ($page - 1) * $per_page;

		$platform_filter = $platform_id > 0 ? $platform_id : NULL;
		$category_filter = $category_id > 0 ? $category_id : NULL;

		$logged_in = $this->session->userdata('logged_in');
		$user_id   = $logged_in ? (int) $this->session->userdata('user_id') : NULL;

		// Catat kata kunci pencarian (personalization).
		if ($search !== '')
		{
			$this->Behavior_m->track_search($user_id, $search);
		}

		$products = $this->Product_m->public_query($search, $platform_filter, $category_filter, $sort, $per_page, $offset, $user_id);

		// Status favorit untuk member yang login.
		$favorited_ids = array();
		if ($logged_in && $this->session->userdata('role') === 'member')
		{
			$favorited_ids = $this->Favorite_m->get_favorited_ids((int) $this->session->userdata('user_id'));
		}

		foreach ($products as $p)
		{
			$p->is_favorited = in_array((int) $p->id, $favorited_ids, TRUE);
		}

		$data['products']   = $products;
		$data['total']      = $this->Product_m->count_public($search, $platform_filter, $category_filter);
		$data['platforms']  = $this->Platform_m->get_all();
		$data['categories'] = $this->Category_m->get_all(TRUE);
		$data['search']     = $search;
		$data['platform_id']= $platform_id;
		$data['category_id']= $category_id;
		$data['sort']       = $sort;
		$data['per_page']   = $per_page;
		$data['page']       = $page;
		$data['total_pages']= max(1, (int) ceil($data['total'] / $per_page));

		// Banner promosi: posisi top di atas grid, middle di sela-sela.
		$data['banners_top']    = $this->Banner_m->get_active_by_position('top');
		$data['banners_middle'] = $this->Banner_m->get_active_by_position('middle');
		$data['banners_bottom'] = $this->Banner_m->get_active_by_position('bottom');
		$data['banner_mid_count'] = min(
			(int) floor(count($products) / 15),
			count($data['banners_middle'])
		);

		$data['title'] = 'Katalog';

		$this->load->view('templates/header', $data);
		$this->load->view('catalog/index', $data);
		$this->load->view('templates/footer');
	}
}
