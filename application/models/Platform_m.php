<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Platform_m Model
|--------------------------------------------------------------------------
| Akses data tabel platforms (marketplace tujuan).
*/

class Platform_m extends CI_Model
{
	protected $table = 'platforms';

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	public function get_all()
	{
		return $this->db->order_by('name', 'ASC')->get($this->table)->result();
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
		$this->db->where('platform_id', $id)->delete('product_platforms');
		$this->db->where('platform_id', $id)->update('user_behaviors', array('platform_id' => NULL));
		$this->db->where('id', $id)->delete($this->table);
		$this->db->trans_complete();

		return $this->db->trans_status();
	}
}
