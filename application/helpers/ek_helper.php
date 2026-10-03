<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Ekatalog Helper
|--------------------------------------------------------------------------
| Fungsi bantu umum milik proyek (prefix ekatalog_ / ek_).
*/

if ( ! function_exists('ek_time_ago'))
{
	/**
	 * Ubah timestamp database menjadi label waktu relatif, contoh "2 jam lalu".
	 *
	 * Tabel menyimpan wall clock WIB (lihat WIB_NOW di index.php), sedangkan
	 * date_default_timezone_set('UTC') di index.php membuat strtotime()
	 * membaca string tanpa timezone sebagai UTC. Karena WIB = UTC+7 tanpa
	 * DST, WIB_NOW langsung menyeimbangkan kedua konvensi itu.
	 *
	 * @param string $datetime Format Y-m-d H:i:s
	 * @return string Label relatif, atau tanggal absolut bila sudah > 7 hari.
	 */
	function ek_time_ago($datetime)
	{
		$ts = strtotime((string) $datetime);

		if ($ts === FALSE || $ts <= 0)
		{
			return '';
		}

		$now = defined('WIB_NOW') ? WIB_NOW : time() + 7 * 3600;
		$diff = $now - $ts;

		if ($diff < 60)
		{
			return 'baru saja';
		}

		if ($diff < 3600)
		{
			return floor($diff / 60).' menit lalu';
		}

		if ($diff < 86400)
		{
			return floor($diff / 3600).' jam lalu';
		}

		if ($diff < 604800)
		{
			return floor($diff / 86400).' hari lalu';
		}

		$bulan = array(
			1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
			'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
		);

		return (int) date('j', $ts).' '.$bulan[(int) date('n', $ts)].' '.date('Y', $ts);
	}
}
