<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Banner_m Model
|--------------------------------------------------------------------------
| Akses data tabel banners.
*/

class Banner_m extends CI_Model
{
	protected $table = 'banners';

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	public function get_all()
	{
		return $this->db->order_by('sort_order', 'ASC')->get($this->table)->result();
	}

	public function get_by_id($id)
	{
		return $this->db->where('id', $id)->get($this->table)->row();
	}

	/**
	 * Banner aktif pada posisi tertentu, diurutkan.
	 */
	public function get_active_by_position($position)
	{
		return $this->db
			->where('is_active', 1)
			->where('position', $position)
			->order_by('sort_order', 'ASC')
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

	public function delete($id)
	{
		$banner = $this->get_by_id($id);

		if ( ! $this->db->where('id', $id)->delete($this->table))
		{
			return FALSE;
		}

		if ($banner && $banner->image)
		{
			$path = FCPATH.'assets/uploads/banners/'.$banner->image;
			if (is_file($path))
			{
				@unlink($path);
			}
		}

		return TRUE;
	}
}
