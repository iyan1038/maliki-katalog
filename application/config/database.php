<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| Dua group koneksi database untuk dua environment:
|   - 'xampp'   : lingkungan testing lokal (XAMPP)
|   - 'laragon' : lingkungan server produksi (Laragon)
|
| Group aktif dipilih otomatis (auto-detect) berdasarkan HTTP_HOST:
|   - host mengandung 'localhost' / '127.0.0.1' / berakhiran '.local'  -> xampp
|   - selain itu (domain/server asli)                                    -> laragon
|
| Nama database di kedua environment sama ('ekatalog') sesuai database.sql.
*/

function _ekatalog_detect_env()
{
	if (php_sapi_name() === 'cli')
	{
		return 'xampp';
	}

	$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST']
		: (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '');

	if ($host === '')
	{
		return 'xampp';
	}

	if (preg_match('#(localhost|127\.0\.0\.1|\.local)(:\d+)?$#i', trim($host, '/:')))
	{
		return 'xampp';
	}

	return 'laragon';
}

$active_group = _ekatalog_detect_env();
$query_builder = TRUE;

/* -------------------------------------------------------------------
   XAMPP — testing lokal
------------------------------------------------------------------- */
$db['xampp'] = array(
	'dsn'	=> '',
	'hostname' => 'localhost',
	'username' => 'root',
	'password' => '',
	'database' => 'ekatalog',
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);

/* -------------------------------------------------------------------
   Laragon — server produksi
   (Isi hostname/port & kredensial sesuai server Laragon Anda)
------------------------------------------------------------------- */
$db['laragon'] = array(
	'dsn'	=> '',
	'hostname' => 'localhost',          // ganti ke host/server Laragon produksi
	'username' => 'root',
	'password' => '',
	'database' => 'ekatalog',
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);
