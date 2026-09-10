<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Waajo Library
|--------------------------------------------------------------------------
| Integrasi API WhatsApp WAAJO untuk kirim promo & rekomendasi produk.
|
| Endpoint/asumsi (sesuaikan dengan akun WAAJO Anda):
|   POST {base_url}/send-message
|   Headers : apikey: {waajo_api_key}, device: {waajo_device}
|   Body JSON: { "to": "<phone>", "text": "<message>" }
|
| Jika kredensial masih placeholder (belum dikonfigurasi), send_message()
| mengembalikan FALSE tanpa memanggil API.
*/

class Waajo
{
	protected $ci;
	protected $configured = FALSE;
	protected $api_key;
	protected $base_url;
	protected $device;

	public function __construct()
	{
		$this->ci =& get_instance();
		$this->ci->config->load('waajo');

		$this->api_key  = $this->ci->config->item('waajo_api_key');
		$this->base_url = rtrim((string) $this->ci->config->item('waajo_base_url'), '/');
		$this->device   = $this->ci->config->item('waajo_device');

		if ($this->api_key && $this->device
			&& strpos($this->api_key, 'YOUR_') === FALSE
			&& strpos($this->device, 'YOUR_') === FALSE)
		{
			$this->configured = TRUE;
		}
	}

	/**
	 * Apakah kredensial WAAJO sudah dikonfigurasi?
	 */
	public function is_configured()
	{
		return $this->configured;
	}

	/**
	 * Kirim pesan WhatsApp.
	 *
	 * @param string $phone   Nomor tujuan (format internasional, mis. 628xxx)
	 * @param string $message Teks pesan
	 * @return bool|array     FALSE bila belum dikonfigurasi / gagal;
	 *                        array ['status' => 'sent'|'failed', 'http_code', 'body'] saat dipanggil.
	 */
	public function send_message($phone, $message)
	{
		if ( ! $this->configured)
		{
			log_message('debug', 'Waajo::send_message dibatalkan — kredensial belum dikonfigurasi.');
			return FALSE;
		}

		$phone = preg_replace('/[^0-9]/', '', $phone);

		if ($phone === '' || $message === '')
		{
			return FALSE;
		}

		$payload = json_encode(array(
			'to'   => $phone,
			'text' => $message
		));

		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL            => $this->base_url.'/send-message',
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_POST           => TRUE,
			CURLOPT_POSTFIELDS     => $payload,
			CURLOPT_TIMEOUT        => 20,
			CURLOPT_HTTPHEADER     => array(
				'Content-Type: application/json',
				'apikey: '.$this->api_key,
				'device: '.$this->device
			)
		));

		$body = curl_exec($ch);
		$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$err  = curl_error($ch);
		curl_close($ch);

		$ok = ($code >= 200 && $code < 300);

		log_message('debug', 'Waajo::send_message to '.$phone.' http='.$code.' err='.$err);

		return array(
			'status'    => $ok ? 'sent' : 'failed',
			'http_code' => $code,
			'body'      => (string) $body,
			'error'     => $err
		);
	}
}
