<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Wa_m Model
|--------------------------------------------------------------------------
| Akses data tabel wa_logs (riwayat pengiriman pesan WhatsApp).
*/

class Wa_m extends CI_Model
{
	protected $table = 'wa_logs';

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * Catat pengiriman pesan WA.
	 *
	 * @param int    $user_id
	 * @param string $phone
	 * @param string $type     promo|rekomendasi
	 * @param string $status   pending|sent|failed
	 * @param string $response
	 */
	public function log($user_id, $phone, $type, $status, $response = '')
	{
		return $this->db->insert($this->table, array(
			'user_id'      => $user_id ? (int) $user_id : NULL,
			'phone'        => $phone,
			'message_type' => $type,
			'status'       => $status,
			'response'     => $response
		));
	}

	/**
	 * Riwayat log.
	 */
	public function get_all($limit = 50)
	{
		return $this->db
			->select('wa_logs.*, users.name AS user_name')
			->join('users', 'users.id = wa_logs.user_id', 'left')
			->order_by('wa_logs.id', 'DESC')
			->limit($limit)
			->get($this->table)
			->result();
	}
}
