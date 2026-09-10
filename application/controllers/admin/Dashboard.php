<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Admin Dashboard Controller
|--------------------------------------------------------------------------
| Ringkasan statistik untuk panel admin.
*/

class Dashboard extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('User_m');
		$this->load->model('Company_m');
		$this->load->model('Product_m');
		$this->load->model('Platform_m');
		$this->load->model('Banner_m');
		$this->load->model('Kbli_m');
	}

	public function index()
	{
		$data['title'] = 'Dashboard';
		$data['active_menu'] = 'dashboard';
		$data['counts'] = array(
			'users'    => count($this->User_m->get_all_users()),
			'companies'=> count($this->Company_m->get_all()),
			'products' => count($this->Product_m->get_all()),
			'platforms'=> count($this->Platform_m->get_all()),
			'banners'  => count($this->Banner_m->get_all())
		);
		$data['latest_products'] = array_slice($this->Product_m->get_all(), 0, 5);
		$data['kbli_list'] = $this->Kbli_m->get_all();

		$this->render('admin/dashboard/index', $data);
	}
}
