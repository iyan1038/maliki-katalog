<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Setting Controller
|--------------------------------------------------------------------------
| Menu setting aplikasi: toggle dark mode, ringkasan akun & profil,
| serta info profil perusahaan (jika user memiliki perusahaan).
*/

class Setting extends User_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('User_m');
		$this->load->model('Company_m');
	}

	public function index()
	{
		$user_id = (int) $this->session->userdata('user_id');

		$data['title']   = 'Pengaturan';
		$data['user']    = $this->User_m->get_by_id($user_id);
		$data['company'] = $this->Company_m->get_by_owner($user_id);
		$data['dark_mode'] = (int) $data['user']->dark_mode;

		$this->load->view('templates/header', $data);
		$this->load->view('settings/index', $data);
		$this->load->view('templates/footer');
	}

	/**
	 * Toggle dark mode (POST).
	 */
	public function toggle_dark_mode()
	{
		$user_id = (int) $this->session->userdata('user_id');
		$value   = (int) (bool) $this->input->post('dark_mode');

		$this->User_m->set_dark_mode($user_id, $value);
		$this->session->set_userdata('dark_mode', $value);
		$this->session->set_flashdata('success', 'Tampilan gelap '.($value ? 'diaktifkan' : 'dinonaktifkan').'.');

		redirect('setting');
	}
}
