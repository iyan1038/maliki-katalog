<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Favorite_m Model
|--------------------------------------------------------------------------
| Akses data tabel favorites (daftar produk favorit member).
*/

class Favorite_m extends CI_Model
{
	protected $table = 'favorites';

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * Tambah favorit.
	 */
	public function add($user_id, $product_id)
	{
		$exists = $this->db
			->where('user_id', $user_id)
			->where('product_id', $product_id)
			->get($this->table)
			->row();

		if ($exists)
		{
			return TRUE;
		}

		return $this->db->insert($this->table, array(
			'user_id'    => $user_id,
			'product_id' => $product_id
		));
	}

	/**
	 * Hapus favorit.
	 */
	public function remove($user_id, $product_id)
	{
		return $this->db
			->where('user_id', $user_id)
			->where('product_id', $product_id)
			->delete($this->table);
	}

	/**
	 * Apakah produk difavoritkan user ini?
	 */
	public function is_favorited($user_id, $product_id)
	{
		if ( ! $user_id)
		{
			return FALSE;
		}

		return (bool) $this->db
			->where('user_id', $user_id)
			->where('product_id', $product_id)
			->get($this->table)
			->num_rows();
	}

	/**
	 * Daftar product_id yang difavoritkan user (untuk status heart di kartu).
	 */
	public function get_favorited_ids($user_id)
	{
		if ( ! $user_id)
		{
			return array();
		}

		$rows = $this->db->select('product_id')->where('user_id', $user_id)->get($this->table)->result();

		$ids = array();
		foreach ($rows as $r)
		{
			$ids[] = (int) $r->product_id;
		}

		return $ids;
	}

	/**
	 * Daftar produk favorit user (lengkap dengan data untuk kartu produk).
	 */
	public function get_by_user($user_id)
	{
		return $this->db
			->select('p.*, c.name AS company_name, c.city AS company_city')
			->select('(SELECT pi.filename FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.position ASC LIMIT 1) AS image')
			->select('(SELECT pl.name FROM product_platforms pp JOIN platforms pl ON pl.id = pp.platform_id WHERE pp.product_id = p.id AND pp.is_visible = 1 ORDER BY pl.name ASC LIMIT 1) AS marketplace_name')
			->select('(SELECT pp.product_url FROM product_platforms pp WHERE pp.product_id = p.id AND pp.is_visible = 1 ORDER BY pp.id ASC LIMIT 1) AS marketplace_url')
			->from('favorites f')
			->join('products p', 'p.id = f.product_id')
			->join('companies c', 'c.id = p.company_id')
			->where('f.user_id', $user_id)
			->where('p.is_active', 1)
			->order_by('f.created_at', 'DESC')
			->get()
			->result();
	}
}
