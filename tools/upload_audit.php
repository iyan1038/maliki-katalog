<?php
/**
 * Audit file upload: bandingkan isi assets/uploads dengan baris database.
 *
 * SKRIP INI HANYA MEMBACA. Tidak menulis file, tidak mengubah database.
 * Wajib dijalankan lewat command line sehingga tidak bisa dipanggil lewat web.
 *
 * Pemakaian:
 *   php tools/upload_audit.php              -> laporan ringkas di terminal
 *   php tools/upload_audit.php --list       -> Additionally menampilkan path file yatim
 *   php tools/upload_audit.php --json       -> keluaran JSON untuk diproses lanjut
 *
 * Keluar dengan kode 0 jika tidak ada file yatim, 1 jika ada.
 */

if (php_sapi_name() !== 'cli')
{
	http_response_code(403);
	header('Content-Type: text/plain; charset=utf-8');
	exit("403 Forbidden\n\nSkrip ini hanya boleh dijalankan lewat command line.\n");
}

/* -------------------------------------------------------------------
   KONFIGURASI
   Nilai default mengikuti application/config/database.php grup 'xampp'.
   Bisa dioverride lewat environment variable bila perlu.
------------------------------------------------------------------- */

$config = array(
	'host'     => getenv('EK_DB_HOST')     ?: 'localhost',
	'user'     => getenv('EK_DB_USER')     ?: 'root',
	'password' => getenv('EK_DB_PASSWORD') ?: '',
	'database' => getenv('EK_DB_NAME')     ?: 'ekatalog',
	'root'     => dirname(__DIR__).DIRECTORY_SEPARATOR,
	'uploads'  => 'assets'.DIRECTORY_SEPARATOR.'uploads',
);

/**
 * Folder upload dan sumber acuan nama file di database.
 * Kalau tabelnya belum ada, semua file di folder dianggap yatim.
 */
$FOLDERS = array(
	'products'  => array('table' => 'product_images', 'column' => 'filename'),
	'companies' => array('table' => 'companies',       'column' => 'logo'),
	'platforms' => array('table' => 'platforms',       'column' => 'logo'),
	'banners'   => array('table' => 'banners',         'column' => 'image'),
	'avatars'   => array('table' => 'users',           'column' => 'avatar'),
);

/**
 * File yang dirujuk langsung di kode, bukan lewat baris database.
 * JANGAN dihapus meski tidak ada di tabel.
 */
$CODE_REFERENCED = array(
	'avatars' => array('avatar1.png'),
);

/* -------------------------------------------------------------------
   KONEKSI
------------------------------------------------------------------- */

try
{
	$dsn = 'mysql:host='.$config['host'].';dbname='.$config['database'].';charset=utf8';
	$pdo = new PDO($dsn, $config['user'], $config['password'], array(
		PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	));
	$db_tersambung = TRUE;
}
catch (PDOException $e)
{
	$db_tersambung = FALSE;
	$pesan_db = $e->getMessage();
}

/**
 * Ambil semua nama file yang dirujuk tabel untuk satu kolom tertentu.
 *
 * @return array NULL kalau tabelnya tidak ada
 */
function ek_referenced_files(PDO $pdo, $table, $column)
{
	$cek = $pdo->prepare('SHOW COLUMNS FROM `'.str_replace('`', '', $table).'` LIKE ?');
	$cek->execute(array($column));

	if ( ! $cek->fetch())
	{
		return NULL;
	}

	$stmt = $pdo->query('SELECT `'.$column.'` FROM `'.str_replace('`', '', $table).'` WHERE `'.$column.'` IS NOT NULL AND `'.$column.'` <> \'\'');

	return array_map('basename', array_column($stmt->fetchAll(), $column));
}

/* -------------------------------------------------------------------
   AUDIT
------------------------------------------------------------------- */

$option_list = in_array('--list', $argv, TRUE);
$option_json = in_array('--json', $argv, TRUE);

$hasil = array(
	'waktu'   => date('Y-m-d H:i:s'),
	'database' => $db_tersambung ? $config['database'] : NULL,
	'error'   => $db_tersambung ? NULL : $pesan_db,
	'folder'  => array(),
);

$total_disk = 0;
$total_terpakai = 0;
$total_yatim = 0;

foreach ($FOLDERS as $folder => $sumber)
{
	$dir = $config['root'].$config['uploads'].DIRECTORY_SEPARATOR.$folder;

	$di_disk = array();

	if (is_dir($dir))
	{
		foreach (scandir($dir) as $entri)
		{
			if ($entri === '.' OR $entri === '..')
			{
				continue;
			}

			$path = $dir.DIRECTORY_SEPARATOR.$entri;

			if (is_file($path))
			{
				$di_disk[$entri] = filesize($path);
			}
		}
	}

	$dirujuk_kode = isset($CODE_REFERENCED[$folder]) ? $CODE_REFERENCED[$folder] : array();
	$dirujuk_db = array();

	if ($db_tersambung)
	{
		$hasil_db = ek_referenced_files($pdo, $sumber['table'], $sumber['column']);

		if ($hasil_db === NULL)
		{
			$catatan = 'tabel '.$sumber['table'].' tidak ada';
		}
		else
		{
			$dirujuk_db = $hasil_db;
			$catatan = NULL;
		}
	}
	else
	{
		$catatan = 'database tidak tersambung';
	}

	$terpakai = array();
	$yatim = array();

	foreach ($di_disk as $nama => $ukuran)
	{
		if (in_array($nama, $dirujuk_db, TRUE) OR in_array($nama, $dirujuk_kode, TRUE))
		{
			$terpakai[$nama] = $ukuran;
		}
		else
		{
			$yatim[$nama] = $ukuran;
		}
	}

	// Baris database yang menunjuk file yang sudah tidak ada di disk.
	$hilang = array_values(array_diff($dirujuk_db, array_keys($di_disk)));

	$total_disk += count($di_disk);
	$total_terpakai += count($terpakai);
	$total_yatim += count($yatim);

	$hasil['folder'][] = array(
		'folder'      => $folder,
		'sumber'      => $sumber['table'].'.'.$sumber['column'],
		'path'        => $dir,
		'ada'         => is_dir($dir),
		'catatan'     => $catatan,
		'di_disk'     => count($di_disk),
		'terpakai'    => count($terpakai),
		'yatim'       => count($yatim),
		'hilang'      => count($hilang),
		'ukuran_yatim'=> array_sum($yatim),
		'daftar_yatim'=> array_keys($yatim),
		'daftar_hilang' => $hilang,
	);
}

$hasil['ringkasan'] = array(
	'di_disk'  => $total_disk,
	'terpakai' => $total_terpakai,
	'yatim'    => $total_yatim,
);

/* -------------------------------------------------------------------
   KELUARAN
------------------------------------------------------------------- */

if ($option_json)
{
	echo json_encode($hasil, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n";
	exit($total_yatim > 0 ? 1 : 0);
}

echo "Audit upload — ".$hasil['waktu']."\n";
echo "Database: ".($db_tersambung ? $config['database'] : 'GAGAL — '.$pesan_db)."\n\n";

printf("%-12s %8s %10s %8s %8s %12s\n", 'FOLDER', 'DI DISK', 'TERPAKAI', 'YATIM', 'HILANG', 'UKURAN YATIM');
echo str_repeat('-', 64)."\n";

foreach ($hasil['folder'] as $f)
{
	printf(
		"%-12s %8d %10d %8d %8d %9s KB\n",
		$f['folder'],
		$f['di_disk'],
		$f['terpakai'],
		$f['yatim'],
		$f['hilang'],
		number_format($f['ukuran_yatim'] / 1024, 1)
	);
}

echo str_repeat('-', 64)."\n";
printf(
	"%-12s %8d %10d %8d %8d %9s KB\n",
	'TOTAL',
	$hasil['ringkasan']['di_disk'],
	$hasil['ringkasan']['terpakai'],
	$hasil['ringkasan']['yatim'],
	0,
	number_format(array_sum(array_column($hasil['folder'], 'ukuran_yatim')) / 1024, 1)
);

foreach ($hasil['folder'] as $f)
{
	if ($f['catatan'])
	{
		echo "\nCatatan ".$f['folder'].": ".$f['catatan']."\n";
	}

	if ($f['daftar_hilang'])
	{
		echo "\nFile hilang dari disk tapi dirujuk database (".$f['folder']."):\n";
		foreach ($f['daftar_hilang'] as $nama)
		{
			echo "  - ".$nama."\n";
		}
	}
}

if ($option_list AND $total_yatim > 0)
{
	echo "\nPath file yatim:\n";
	foreach ($hasil['folder'] as $f)
	{
		foreach ($f['daftar_yatim'] as $nama)
		{
			echo "  ".$f['path'].DIRECTORY_SEPARATOR.$nama."\n";
		}
	}
}

echo "\n".($total_yatim > 0
	? $total_yatim." file yatim tidak dirujuk database maupun kode.\n"
	: "Tidak ada file yatim.\n");

exit($total_yatim > 0 ? 1 : 0);
