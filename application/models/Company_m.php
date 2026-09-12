<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Company_m Model
|--------------------------------------------------------------------------
| Akses data tabel companies, company_kbli, dan relasinya.
*/

class Company_m extends CI_Model
{
	protected $table = 'companies';

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	public function get_all()
	{
		return $this->db
			->select('companies.*, users.name AS owner_name, users.email AS owner_email')
			->join('users', 'users.id = companies.user_id', 'left')
			->order_by('companies.name', 'ASC')
			->get($this->table)
			->result();
	}

	public function get_all_active()
	{
		return $this->db
			->where('companies.is_active', 1)
			->order_by('name', 'ASC')
			->get($this->table)
			->result();
	}

	/**
	 * Perusahaan milik seorang user.
	 */
	public function get_by_owner($user_id)
	{
		return $this->db->where('user_id', $user_id)->get($this->table)->row();
	}

	public function get_by_id($id)
	{
		return $this->db->where('id', $id)->get($this->table)->row();
	}

	/**
	 * Ambil perusahaan beserta data pemilik & daftar KBLI.
	 */
	public function get_detail($id)
	{
		$company = $this->get_by_id($id);

		if ( ! $company)
		{
			return NULL;
		}

		$company->owner = $this->db->where('id', $company->user_id)->get('users')->row();
		$company->kbli  = $this->get_kbli($id);

		return $company;
	}

	public function get_kbli($company_id)
	{
		return $this->db
			->select('kbli.id, kbli.code, kbli.name, kbli.description')
			->join('kbli', 'kbli.id = company_kbli.kbli_id')
			->where('company_kbli.company_id', $company_id)
			->get('company_kbli')
			->result();
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
		$company = $this->get_by_id($id);

		$this->db->trans_start();
		$this->db->where('company_id', $id)->delete('company_kbli');
		$this->db->where('id', $id)->delete($this->table);
		$this->db->trans_complete();

		if ($this->db->trans_status() && $company && $company->logo)
		{
			$path = FCPATH.'assets/uploads/companies/'.$company->logo;
			if (is_file($path))
			{
				@unlink($path);
			}
		}

		return $this->db->trans_status();
	}

	/**
	 * Sinkronisasi relasi KBLI perusahaan (hapus semua, lalu insert baru).
	 */
	public function set_kbli($company_id, array $kbli_ids)
	{
		$this->db->where('company_id', $company_id)->delete('company_kbli');

		if (empty($kbli_ids))
		{
			return TRUE;
		}

		$data = array();
		foreach ($kbli_ids as $kbli_id)
		{
			$data[] = array('company_id' => $company_id, 'kbli_id' => (int) $kbli_id);
		}

		return $this->db->insert_batch('company_kbli', $data);
	}
}
