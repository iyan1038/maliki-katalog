<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Waajo Library
|--------------------------------------------------------------------------
| Integrasi API WhatsApp WAAJO (https://waajo.id) untuk kirim promo &
| rekomendasi produk.
|
| Format API (diverifikasi dari Postman collection "WhatsappV2Public"):
|   POST {base_url}/go-omni-v2/public/whatsapp/send-text
|   Headers : apikey: {waajo_api_key}
|   Body JSON: { "recipient_number": "628xxx", "text": "...", "check_session": false }
|
| Sukses : HTTP 200, body {"data":{"id":"..."},"msg":"OK","status":200}
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
	 * Nomor perangkat WhatsApp yang terdaftar di akun WAAJO.
	 * Dipakai sebagai nomor tujuan fallback bila pemilik belum punya nomor.
	 */
	public function get_device()
	{
		return $this->device ? (string) $this->device : '';
	}

	/**
	 * Base URL media yang dapat diakses publik (config waajo_media_url).
	 * Dipakai untuk membentuk file_url gambar produk yang akan dikirim.
	 *
	 * @return string '' bila belum diisi (maka kirim gambar dilewati).
	 */
	public function media_base()
	{
		return rtrim((string) $this->ci->config->item('waajo_media_url'), '/');
	}

	/**
	 * Normalisasi nomor HP ke format internasional (62xxx).
	 * Sumber: catatan.md — pengguna bisa mengisi "08xx" ataupun "62xx".
	 */
	protected function normalize_phone($phone)
	{
		$phone = preg_replace('/[^0-9]/', '', $phone);

		if ($phone !== '' && substr($phone, 0, 2) === '08')
		{
			$phone = '62'.substr($phone, 1);
		}

		return $phone;
	}

	/**
	 * Kirim satu request HTTP JSON ke API WAAJO.
	 *
	 * @param string $path   contoh: /go-omni-v2/public/whatsapp/is_online
	 * @param array  $body   payload JSON (opsional)
	 * @return array         ['http_code', 'body', 'error']
	 */
	protected function request($path, array $body = NULL)
	{
		$headers = array(
			'apikey: '.$this->api_key
		);

		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL            => $this->base_url.'/'.ltrim($path, '/'),
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_TIMEOUT        => 20,
			CURLOPT_HTTPHEADER     => $headers,
			CURLOPT_SSL_VERIFYPEER => $this->should_verify_ssl(),
			CURLOPT_SSL_VERIFYHOST => $this->should_verify_ssl() ? 2 : 0
		));

		if (is_array($body))
		{
			curl_setopt_array($ch, array(
				CURLOPT_POST       => TRUE,
				CURLOPT_POSTFIELDS => json_encode($body)
			));
			$headers[] = 'Content-Type: application/json';
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}

		$resp = curl_exec($ch);
		$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$err  = curl_error($ch);
		curl_close($ch);

		log_message('debug', 'Waajo::request '.$this->base_url.'/'.ltrim($path, '/').' http='.$code.' err='.$err);

		return array(
			'http_code' => $code,
			'body'      => (string) $resp,
			'error'     => $err
		);
	}

	/**
	 * Verifikasi sertifikat TLS aktif kecuali CA bundle tidak ditemukan
	 * (mis. XAMPP tanpa curl-ca-bundle.crt) atau config memaksa dinonaktifkan.
	 */
	protected function should_verify_ssl()
	{
		$forced = $this->ci->config->item('waajo_ssl_verify');

		if (is_bool($forced))
		{
			return $forced;
		}

		$cainfo = (string) ini_get('curl.cainfo');

		if ($cainfo !== '' && ! file_exists($cainfo))
		{
			log_message('error', 'Waajo: CA bundle tidak ditemukan ('.$cainfo.'), verifikasi TLS dinonaktifkan.');
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Cek status koneksi perangkat ke API WAAJO (tidak mengirim pesan).
	 *
	 * @return array ['ok' => bool, 'message' => string]
	 */
	public function is_online()
	{
		if ( ! $this->configured)
		{
			return array('ok' => FALSE, 'message' => 'Kredensial belum dikonfigurasi.');
		}

		$resp = $this->request('/go-omni-v2/public/whatsapp/is_online', array());

		if ($resp['error'] !== '')
		{
			return array('ok' => FALSE, 'message' => 'Error koneksi: '.$resp['error']);
		}

		$json = json_decode($resp['body'], TRUE);

		if ( ! is_array($json) || ! isset($json['status']))
		{
			return array('ok' => FALSE, 'message' => 'Respons tidak valid (HTTP '.$resp['http_code'].').');
		}

		$ok = ($resp['http_code'] >= 200 && $resp['http_code'] < 300)
			&& (int) $json['status'] === 200
			&& ! empty($json['data']);

		return array(
			'ok'      => $ok,
			'message' => $ok ? 'Online! ('.(isset($json['msg']) ? $json['msg'] : '').')' : (isset($json['msg']) ? $json['msg'] : 'Offline / unauthorized.')
		);
	}

	/**
	 * Kirim pesan WhatsApp.
	 *
	 * @param string $phone   Nomor tujuan (format internasional, mis. 628xxx)
	 * @param string $message Teks pesan
	 * @return bool|array     FALSE bila belum dikonfigurasi / gagal;
	 *                        array ['status' => 'sent'|'failed', 'http_code', 'body', 'error', 'message'] saat dipanggil.
	 */
	public function send_message($phone, $message)
	{
		if ( ! $this->configured)
		{
			log_message('debug', 'Waajo::send_message dibatalkan — kredensial belum dikonfigurasi.');
			return FALSE;
		}

		$phone = $this->normalize_phone($phone);

		if ($phone === '' || $message === '')
		{
			return FALSE;
		}

		$payload = array(
			'recipient_number' => $phone,
			'text'             => $message,
			'check_session'    => FALSE
		);

		$resp = $this->request('/go-omni-v2/public/whatsapp/send-text', $payload);

		$json = json_decode($resp['body'], TRUE);
		$msg  = (is_array($json) && isset($json['msg'])) ? (string) $json['msg'] : '';
		$resp_status = (is_array($json) && isset($json['status'])) ? (int) $json['status'] : NULL;

		$ok = ($resp['error'] === '')
			&& ($resp['http_code'] >= 200 && $resp['http_code'] < 300)
			&& ($resp_status === 200 || $resp_status === NULL)
			&& (is_array($json) ? ! empty($json['data']) : TRUE);

		return array(
			'status'    => $ok ? 'sent' : 'failed',
			'http_code' => $resp['http_code'],
			'body'      => $resp['body'],
			'error'     => $resp['error'],
			'message'   => $msg
		);
	}

	/**
	 * Kirim file/gambar WhatsApp.
	 *
	 * Endpoint (dari Postman collection "WhatsappV2Public"):
	 *   POST {base_url}/go-omni-v2/public/whatsapp/send-file
	 *   Body: { "recipient_number": "628xxx", "text": "{caption}",
	 *           "message_type": "image", "file_url": "{url}" }
	 *
	 * @param string $phone    Nomor tujuan (format internasional, mis. 628xxx)
	 * @param string $file_url URL file yang dapat diakses publik oleh server WAAJO
	 * @param string $caption  Teks/caption
	 * @param string $type     image|document
	 * @return bool|array      FALSE bila belum dikonfigurasi / parameter kosong;
	 *                         array ['status' => 'sent'|'failed', 'http_code', 'body', 'error', 'message'] saat dipanggil.
	 */
	public function send_file($phone, $file_url, $caption, $type = 'image')
	{
		if ( ! $this->configured)
		{
			log_message('debug', 'Waajo::send_file dibatalkan — kredensial belum dikonfigurasi.');
			return FALSE;
		}

		$phone = $this->normalize_phone($phone);

		if ($phone === '' || $file_url === '')
		{
			return FALSE;
		}

		$type = in_array($type, array('image', 'document'), TRUE) ? $type : 'image';

		$payload = array(
			'recipient_number' => $phone,
			'text'             => (string) $caption,
			'message_type'     => $type,
			'file_url'         => $file_url
		);

		$resp = $this->request('/go-omni-v2/public/whatsapp/send-file', $payload);

		$json = json_decode($resp['body'], TRUE);
		$msg  = (is_array($json) && isset($json['msg'])) ? (string) $json['msg'] : '';
		$resp_status = (is_array($json) && isset($json['status'])) ? (int) $json['status'] : NULL;

		$ok = ($resp['error'] === '')
			&& ($resp['http_code'] >= 200 && $resp['http_code'] < 300)
			&& ($resp_status === 200 || $resp_status === NULL)
			&& (is_array($json) ? ! empty($json['data']) : TRUE);

		return array(
			'status'    => $ok ? 'sent' : 'failed',
			'http_code' => $resp['http_code'],
			'body'      => $resp['body'],
			'error'     => $resp['error'],
			'message'   => $msg
		);
	}
}