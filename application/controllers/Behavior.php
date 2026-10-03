<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Behavior Controller
|--------------------------------------------------------------------------
| Endpoint ringan untuk tracking kunjungan produk (klik marketplace, dsb.)
| via sendBeacon. Maksimal satu kunjungan per produk+platform per 30 menit.
| Hanya mencatat integer id — aman tanpa CSRF token
| (URI dicantumkan di csrf_exclude_uris).
*/

class Behavior extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Behavior_m');
	}

	/**
	 * Catat kunjungan produk. Dapat dipanggil anonim: jumlah kunjungan
	 * tetap dihitung, hanya baris user_behaviors yang dilewati.
	 */
	public function track()
	{
		$this->output->set_content_type('application/json');

		$product_id  = (int) $this->input->post('product_id');
		$platform_id = (int) $this->input->post('platform_id');
		$user_id     = $this->session->userdata('logged_in')
			? (int) $this->session->userdata('user_id')
			: NULL;

		if ($product_id)
		{
			$this->Behavior_m->register_visit($user_id, $product_id, $platform_id ?: NULL);
			echo json_encode(array('ok' => TRUE));
			return;
		}

		echo json_encode(array('ok' => FALSE));
	}
}
