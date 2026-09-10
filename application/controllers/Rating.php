<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Rating Controller
|--------------------------------------------------------------------------
| Member memberi rating 1-5 bintang + komentar untuk sebuah produk.
*/

class Rating extends Member_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Rating_m');
		$this->load->model('Product_m');
		$this->load->library('form_validation');
	}

	/**
	 * Simpan / perbarui rating member untuk produk.
	 */
	public function submit()
	{
		$product_id = (int) $this->input->post('product_id');

		if ( ! $product_id || ! $this->Product_m->get_by_id($product_id))
		{
			$this->session->set_flashdata('error', 'Produk tidak ditemukan.');
			redirect('catalog');
		}

		$this->form_validation->set_rules('rating', 'Rating', 'required|greater_than[0]|less_than[6]');
		$this->form_validation->set_rules('comment', 'Komentar', 'max_length[1000]');

		if ($this->form_validation->run() === FALSE)
		{
			$this->session->set_flashdata('error', 'Pilih rating 1-5 bintang.');
			redirect('product/'.$product_id);
		}

		$user_id = (int) $this->session->userdata('user_id');
		$rating  = (int) $this->input->post('rating');
		$comment = $this->input->post('comment', TRUE);

		$existing = $this->Rating_m->get_by_product_user($product_id, $user_id);

		if ($existing)
		{
			$this->Rating_m->update($existing->id, array(
				'rating'  => $rating,
				'comment' => $comment
			));
			$msg = 'Rating Anda telah diperbarui.';
		}
		else
		{
			$this->Rating_m->insert(array(
				'product_id' => $product_id,
				'user_id'    => $user_id,
				'rating'     => $rating,
				'comment'    => $comment
			));
			$msg = 'Terima kasih atas rating Anda.';
		}

		$this->Rating_m->recalc_product($product_id);

		$this->session->set_flashdata('success', $msg);
		redirect('product/'.$product_id);
	}
}
