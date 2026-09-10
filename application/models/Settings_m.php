<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Settings_m Model
|--------------------------------------------------------------------------
| Akses data tabel settings (key-value konfigurasi global aplikasi).
*/

class Settings_m extends CI_Model
{
	protected $table = 'settings';

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	public function get_all()
	{
		return $this->db->order_by('`key`', 'ASC')->get($this->table)->result();
	}

	public function get_by_id($id)
	{
		return $this->db->where('id', $id)->get($this->table)->row();
	}

	public function get_by_key($key)
	{
		return $this->db->where('`key`', $key)->get($this->table)->row();
	}

	public function insert($data)
	{
		if ( ! $this->db->insert($this->table, $data))
		{
			return FALSE;
		}

		return $this->db->insert_id();
	}

	public function update($id, $data)
	{
		return $this->db->where('id', $id)->update($this->table, $data);
	}

	public function delete($id)
	{
		return $this->db->where('id', $id)->delete($this->table);
	}
}
