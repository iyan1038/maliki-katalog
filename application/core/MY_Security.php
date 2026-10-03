<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| MY_Security
|--------------------------------------------------------------------------
| Menimpa perilaku gagal CSRF bawaan CI ("An Error Was Encountered -
| The action you have requested is not allowed.").
|
| Pada gagal CSRF (spam klik / double-submit):
|   - Admin : redirect ke halaman daftar section (admin/<controller>),
|             bukan ke form. Admin tidak terkecoh mengira datanya belum
|             tersimpan sehingga tidak submit ulang (mencegah redundansi).
|   - Publik: redirect balik ke halaman asal.
|   - Keduanya memicu toast "Jangan spam klik!" di pojok kanan atas via
|     cookie ek_spam_notice yang dibaca templates/toast.php.
|
| Pencegahan redundansi tetap utuh: csrf_verify() tetap menolak request
| duplikat; hanya rute tampilannya yang diubah.
*/

class MY_Security extends CI_Security
{
	/**
	 * Tangani gagal CSRF tanpa bergantung pada session/view (terjadi saat
	 * inisialisasi inti framework, sebelum controller siap).
	 *
	 * @return void
	 */
	public function csrf_show_error()
	{
		// Penanda untuk memicu toast di halaman tujuan.
		if ( ! headers_sent())
		{
			setcookie('ek_spam_notice', '1', time() + 30, '/');
		}

		$target = $this->_resolve_fallback_target();
		header('Location: '.$target, TRUE, 302);
		exit;
	}

	/**
	 * Tentukan URL tujuan redirect saat CSRF gagal.
	 *
	 * @return string URL absolut/relatif tujuan.
	 */
	protected function _resolve_fallback_target()
	{
		$base_url = config_item('base_url');
		if ( ! is_string($base_url) || $base_url === '')
		{
			$base_url = '/';
		}

		$uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
		$path = preg_replace('/\?.*$/', '', $uri);

		// Admin: arahkan ke halaman daftar section, bukan ke form/aksi.
		if (preg_match('#(.*/admin/[A-Za-z0-9_\-]+)#', $path, $m))
		{
			return $m[1];
		}

		// Publik: balik ke halaman asal selama satu-origin dengan base_url.
		$back = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		if (is_string($back) && $back !== '' && strpos($back, rtrim((string) $base_url, '/')) === 0)
		{
			return $back;
		}

		return (string) $base_url;
	}
}