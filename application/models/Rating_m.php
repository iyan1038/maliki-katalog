<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Rating_m Model
|--------------------------------------------------------------------------
| Akses data tabel ratings (rating 1-5 + komentar) dan perhitungan rata-rata.
*/

class Rating_m extends CI_Model
{
	protected $table = 'ratings';

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * Rating user tertentu untuk satu produk.
	 */
	public function get_by_product_user($product_id, $user_id)
	{
		return $this->db
			->where('product_id', $product_id)
			->where('user_id', $user_id)
			->get($this->table)
			->row();
	}

	/**
	 * Daftar rating sebuah produk (dengan nama/avatar pemberi).
	 */
	public function get_by_product($product_id, $limit = 20)
	{
		return $this->db
			->select('ratings.*, users.name AS user_name, users.avatar AS user_avatar')
			->join('users', 'users.id = ratings.user_id')
			->where('ratings.product_id', $product_id)
			->order_by('ratings.created_at', 'DESC')
			->limit($limit)
			->get($this->table)
			->result();
	}

	public function insert($data)
	{
		return $this->db->insert($this->table, $data);
	}

	public function update($id, $data)
	{
		return $this->db->where('id', $id)->update($this->table, $data);
	}

	/**
	 * Hitung ulang avg_rating & rating_count di tabel products.
	 */
	public function recalc_product($product_id)
	{
		$row = $this->db
			->select('AVG(rating) AS avg_rating, COUNT(*) AS rating_count')
			->where('product_id', $product_id)
			->get($this->table)
			->row();

		$avg = $row->avg_rating ? round((float) $row->avg_rating, 1) : 0.0;
		$cnt = (int) $row->rating_count;

		return $this->db
			->where('id', $product_id)
			->update('products', array(
				'avg_rating'   => $avg,
				'rating_count' => $cnt
			));
	}
}
