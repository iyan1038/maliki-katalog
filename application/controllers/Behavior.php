<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Behavior Controller
|--------------------------------------------------------------------------
| Endpoint ringan untuk tracking perilaku user (klik marketplace, dsb.)
| via AJAX. Hanya mencatat integer id — aman tanpa CSRF token
| (URI dicantumkan di csrf_exclude_uris).
*/

class Behavior extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Behavior_m');
	}

	public function track()
	{
		$this->output->set_content_type('application/json');

		if ( ! $this->session->userdata('logged_in'))
		{
			echo json_encode(array('ok' => FALSE));
			return;
		}

		$user_id    = (int) $this->session->userdata('user_id');
		$product_id = (int) $this->input->post('product_id');
		$type       = $this->input->post('type') === 'click' ? 'click' : 'view';
		$platform_id = (int) $this->input->post('platform_id') ? (int) $this->input->post('platform_id') : NULL;

		if ($product_id)
		{
			$this->Behavior_m->track($user_id, $product_id, $type, $platform_id);
			echo json_encode(array('ok' => TRUE));
			return;
		}

		echo json_encode(array('ok' => FALSE));
	}
}
