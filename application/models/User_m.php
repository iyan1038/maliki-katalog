<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| User_m Model
|--------------------------------------------------------------------------
| Akses data tabel users: autentikasi manual & Google, profil, reset password.
*/

class User_m extends CI_Model
{
	protected $table = 'users';

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * Ambil satu user berdasarkan id.
	 */
	public function get_by_id($id)
	{
		return $this->db->where('id', $id)->get($this->table)->row();
	}

	/**
	 * Ambil semua user.
	 */
	public function get_all_users()
	{
		return $this->db->order_by('created_at', 'DESC')->get($this->table)->result();
	}

	/**
	 * Member yang memiliki nomor WhatsApp (target pengiriman promo).
	 */
	public function get_members_with_wa()
	{
		return $this->db
			->where('role', 'member')
			->where('is_active', 1)
			->where('wa_number IS NOT NULL')
			->where('wa_number !=', '')
			->order_by('name', 'ASC')
			->get($this->table)
			->result();
	}

	/**
	 * Ambil satu user berdasarkan email.
	 */
	public function get_by_email($email)
	{
		return $this->db->where('email', $email)->get($this->table)->row();
	}

	/**
	 * Ambil satu user berdasarkan google_id.
	 */
	public function get_by_google_id($google_id)
	{
		return $this->db->where('google_id', $google_id)->get($this->table)->row();
	}

	/**
	 * Cek kredensial login manual.
	 *
	 * @return object|NULL  User bila cocok, NULL bila gagal / tidak aktif.
	 */
	public function login_by_credentials($email, $password)
	{
		$user = $this->get_by_email($email);

		if ( ! $user || ! $user->password)
		{
			return NULL;
		}

		if ( ! password_verify($password, $user->password))
		{
			return NULL;
		}

		if ( ! (int) $user->is_active)
		{
			return NULL;
		}

		return $user;
	}

	/**
	 * Insert user baru (register manual).
	 *
	 * @return int|NULL  Id user baru, atau NULL bila gagal.
	 */
	public function insert($data)
	{
		if ($this->db->insert($this->table, $data))
		{
			return $this->db->insert_id();
		}

		return NULL;
	}

	/**
	 * Update data user.
	 */
	public function update($id, $data)
	{
		return $this->db->where('id', $id)->update($this->table, $data);
	}

	/**
	 * Perbarui profil member.
	 */
	public function update_profile($id, $data)
	{
		return $this->update($id, $data);
	}

	/**
	 * Ganti password (di-hash bcrypt).
	 */
	public function change_password($id, $new_password)
	{
		return $this->update($id, array(
			'password' => password_hash($new_password, PASSWORD_BCRYPT)
		));
	}

	/**
	 * Set preferensi dark mode.
	 */
	public function set_dark_mode($id, $value)
	{
		return $this->update($id, array('dark_mode' => (int) (bool) $value));
	}

	/**
	 * Simpan atau buat user dari data Google.
	 *
	 * @return object  User yang aktif (baru atau sudah ada).
	 */
	public function find_or_create_google_user($google_data)
	{
		$user = $this->get_by_google_id($google_data['id']);

		if ($user)
		{
			return $this->get_by_id($user->id);
		}

		$existing = $this->get_by_email($google_data['email']);

		if ($existing)
		{
			// Email sudah terdaftar manual — tautkan google_id.
			$this->update($existing->id, array(
				'google_id' => $google_data['id']
			));

			return $this->get_by_id($existing->id);
		}

		$new_id = $this->insert(array(
			'google_id' => $google_data['id'],
			'name'      => $google_data['name'],
			'email'     => $google_data['email'],
			'avatar'    => 'avatar1.png',
			'role'      => 'member',
			'is_active' => 1
		));

		return $new_id ? $this->get_by_id($new_id) : NULL;
	}

	/**
	 * Buat token reset password untuk email tertentu.
	 *
	 * @return string|false  Token, atau FALSE jika email tidak terdaftar.
	 */
	public function create_reset_token($email, $expiry_minutes = 60)
	{
		if ( ! $this->get_by_email($email))
		{
			return FALSE;
		}

		$token = bin2hex(random_bytes(32));

		$this->db->insert('password_resets', array(
			'email'      => $email,
			'token'      => $token,
			'expires_at' => date('Y-m-d H:i:s', time() + ($expiry_minutes * 60))
		));

		return $token;
	}

	/**
	 * Ambil data reset token yang masih berlaku.
	 */
	public function get_valid_reset_token($token)
	{
		return $this->db
			->where('token', $token)
			->where('expires_at >', date('Y-m-d H:i:s'))
			->order_by('id', 'DESC')
			->limit(1)
			->get('password_resets')
			->row();
	}

	/**
	 * Ganti password user dan hapus semua token reset miliknya.
	 */
	public function reset_password_by_token($token, $new_password)
	{
		$reset = $this->get_valid_reset_token($token);

		if ( ! $reset)
		{
			return FALSE;
		}

		$updated = $this->update($this->get_by_email($reset->email)->id, array(
			'password' => password_hash($new_password, PASSWORD_BCRYPT)
		));

		if ($updated)
		{
			$this->db->where('email', $reset->email)->delete('password_resets');
			return TRUE;
		}

		return FALSE;
	}
}
