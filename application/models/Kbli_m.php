<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Kbli_m Model
|--------------------------------------------------------------------------
| Akses data tabel kbli.
*/

class Kbli_m extends CI_Model
{
	protected $table = 'kbli';

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	public function get_all()
	{
		return $this->db->order_by('code', 'ASC')->get($this->table)->result();
	}

	public function get_by_id($id)
	{
		return $this->db->where('id', $id)->get($this->table)->row();
	}

	public function get_by_code($code)
	{
		return $this->db->where('code', $code)->get($this->table)->row();
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
		$this->load->model('Category_m');

		$this->db->trans_start();
		$this->db->where('kbli_id', $id)->delete('company_kbli');
		$this->db->where('kbli_id', $id)->delete('product_kbli');
		$this->Category_m->remove_kbli($id);
		$this->db->where('id', $id)->delete($this->table);
		$this->db->trans_complete();

		return $this->db->trans_status();
	}
}
