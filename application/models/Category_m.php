<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Category_m Model
|--------------------------------------------------------------------------
| Akses data tabel categories + relasi category_kbli (kategori produk
| berdasar kode KBLI). Relasi diatur sekali oleh admin, produk masuk
| kategori otomatis melalui KBLI yang dimilikinya.
*/

class Category_m extends CI_Model
{
	protected $table = 'categories';

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * Daftar kategori, urut berdasarkan sort_order lalu nama.
	 */
	public function get_all($active_only = FALSE)
	{
		$this->db->order_by('sort_order', 'ASC')->order_by('name', 'ASC');

		if ($active_only)
		{
			$this->db->where('is_active', 1);
		}

		return $this->db->get($this->table)->result();
	}

	public function get_by_id($id)
	{
		return $this->db->where('id', $id)->get($this->table)->row();
	}

	public function get_by_slug($slug)
	{
		return $this->db->where('slug', $slug)->get($this->table)->row();
	}

	public function insert($data)
	{
		return $this->db->insert($this->table, $data);
	}

	public function update($id, $data)
	{
		return $this->db->where('id', $id)->update($this->table, $data);
	}

	public function delete($id)
	{
		$this->db->trans_start();
		$this->db->where('category_id', $id)->delete('category_kbli');
		$this->db->where('id', $id)->delete($this->table);
		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	/**
	 * Simpan relasi kategori -> KBLI (hapus lama, sisip batch baru).
	 */
	public function set_kbli($category_id, array $kbli_ids)
	{
		$this->db->where('category_id', $category_id)->delete('category_kbli');

		if (empty($kbli_ids))
		{
			return TRUE;
		}

		$data = array();
		foreach ($kbli_ids as $kbli_id)
		{
			$data[] = array('category_id' => (int) $category_id, 'kbli_id' => (int) $kbli_id);
		}

		return $this->db->insert_batch('category_kbli', $data);
	}

	/**
	 * ID KBLI yang terhubung ke sebuah kategori.
	 */
	public function get_kbli_ids($category_id)
	{
		$rows = $this->db
			->select('kbli_id')
			->where('category_id', $category_id)
			->get('category_kbli')
			->result();

		$ids = array();
		foreach ($rows as $row)
		{
			$ids[] = (int) $row->kbli_id;
		}

		return $ids;
	}

	/**
	 * Hapus relasi sebuah KBLI dari semua kategori
	 * (dipanggil saat KBLI dihapus dari master).
	 */
	public function remove_kbli($kbli_id)
	{
		return $this->db->where('kbli_id', $kbli_id)->delete('category_kbli');
	}
}