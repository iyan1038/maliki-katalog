<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Admin_Controller
|--------------------------------------------------------------------------
| Base controller untuk halaman admin. Memastikan hanya user dengan
| role 'admin' yang dapat mengakses, serta memuat template sidebar admin.
*/

class Admin_Controller extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->library('session');

		$logged_in = $this->session->userdata('logged_in');
		$role      = $this->session->userdata('role');

		if ( ! $logged_in || $role !== 'admin')
		{
			$this->session->set_flashdata('error', 'Akses ditolak. Silakan login sebagai admin.');
			redirect('auth/login');
		}
	}

	/**
	 * Muat layout halaman admin.
	 *
	 * @param string $view      Path view (mis. 'admin/dashboard')
	 * @param array  $data      Data untuk view
	 */
	protected function render($view, $data = array())
	{
		$data['active_menu'] = isset($data['active_menu']) ? $data['active_menu'] : '';
		$data['admin_name']  = $this->session->userdata('name');

		$this->load->view('admin/templates/header', $data);
		$this->load->view('admin/templates/sidebar', $data);
		$this->load->view($view, $data);
		$this->load->view('admin/templates/footer');
	}
}

/*
|--------------------------------------------------------------------------
| User_Controller
|--------------------------------------------------------------------------
| Base untuk halaman yang membutuhkan login (member maupun admin).
*/

class User_Controller extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->library('session');

		if ( ! $this->session->userdata('logged_in'))
		{
			$this->session->set_flashdata('error', 'Silakan login terlebih dahulu.');
			redirect('auth/login');
		}
	}
}

/*
|--------------------------------------------------------------------------
| Member_Controller
|--------------------------------------------------------------------------
| Base untuk fitur khusus member: rating & favorit.
*/

class Member_Controller extends User_Controller
{
	public function __construct()
	{
		parent::__construct();

		if ($this->session->userdata('role') !== 'member')
		{
			$this->session->set_flashdata('error', 'Fitur ini khusus untuk member.');
			redirect('catalog');
		}
	}
}
