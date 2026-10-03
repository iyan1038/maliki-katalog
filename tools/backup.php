<?php
/**
 * Backup katalog: dump database + arsipkan file upload yang sedang terpakai.
 *
 * SKRIP INI MENULIS ke folder backup di luar web root. Tidak menyentuh
 * folder karantina dan tidak mengubah isi assets/uploads.
 * Wajib dijalankan lewat command line.
 *
 * Pemakaian:
 *   php tools/backup.php                 -> backup sekali, simpan 7 Arsip terakhir
 *   php tools/backup.php --keep=14       -> simpan 14 arsip terakhir
 *   php tools/backup.php --force         -> lanjutkan meski ada file yatim
 *   php tools/backup.php --skip-audit    -> hilangkan pemeriksaan file yatim
 *   php tools/backup.php --dry-run       -> hanya tampilkan rencana, tidak menulis
 *
 * Keluar dengan kode 0 bila sukses, 1 bila gagal.
 */

if (php_sapi_name() !== 'cli')
{
	http_response_code(403);
	header('Content-Type: text/plain; charset=utf-8');
	exit("403 Forbidden\n\nSkrip ini hanya boleh dijalankan lewat command line.\n");
}

/* -------------------------------------------------------------------
   KONFIGURASI
------------------------------------------------------------------- */

$config = array(
	'db' => array(
		'host'     => getenv('EK_DB_HOST')     ?: 'localhost',
		'user'     => getenv('EK_DB_USER')     ?: 'root',
		'password' => getenv('EK_DB_PASSWORD') ?: '',
		'name'     => getenv('EK_DB_NAME')     ?: 'ekatalog',
	),
	// Dicari berurutan. Boleh dioverride lewat EK_MYSQLDUMP.
	'mysqldump' => array(
		getenv('EK_MYSQLDUMP') ?: '',
		'C:\hehe\mysql\bin\mysqldump.exe',
		'D:\Server\xampp\mysql\bin\mysqldump.exe',
		'/usr/bin/mysqldump',
		'/usr/local/bin/mysqldump',
	),
	// WAJIB di luar web root. Folder di dalam htdocs akan bisa diakses publik.
	'backup_dir'  => getenv('EK_BACKUP_DIR') ?: 'C:\hehe\katalog-storage\backup',
	'root'        => dirname(__DIR__),
	'uploads'     => 'assets'.DIRECTORY_SEPARATOR.'uploads',
	'keep'        => 7,
	// Tabel yang wajib ada di dalam dump. Kalau salah satu hilang, dump dianggap gagal.
	'wajib' => array('users', 'companies', 'products', 'categories', 'kbli', 'platforms'),
);

/* -------------------------------------------------------------------
   DAFTAR OPSI
------------------------------------------------------------------- */

$option = array('force' => FALSE, 'skip_audit' => FALSE, 'dry_run' => FALSE);

foreach (array_slice($argv, 1) as $arg)
{
	if ($arg === '--force')      { $option['force'] = TRUE; }
	elseif ($arg === '--skip-audit') { $option['skip_audit'] = TRUE; }
	elseif ($arg === '--dry-run')    { $option['dry_run'] = TRUE; }
	elseif (strpos($arg, '--keep=') === 0)
	{
		$config['keep'] = max(1, (int) substr($arg, 7));
	}
	else
	{
		exit("Opsi tidak dikenal: ".$arg."\n");
	}
}

/* -------------------------------------------------------------------
   PEMBANTU
------------------------------------------------------------------- */

function ek_gagal($pesan)
{
	fwrite(STDERR, "GAGAL: ".$pesan."\n");
	exit(1);
}

function ek_bytes($nilai)
{
	$unit = array('B', 'KB', 'MB', 'GB');
	$i = 0;

	while ($nilai >= 1024 AND $i < 3)
	{
		$nilai /= 1024;
		$i++;
	}

	return round($nilai, 1).' '.$unit[$i];
}

function ek_cari_mysqldump($kandidat)
{
	foreach ($kandidat as $path)
	{
		if ($path !== '' AND (is_file($path) OR strpos($path, '/') === 0))
		{
			if (is_file($path))
			{
				return $path;
			}

			$which = trim((string) @shell_exec('command -v '.escapeshellarg($path).' 2>/dev/null'));

			if ($which !== '')
			{
				return $which;
			}
		}
	}

	return NULL;
}

/* -------------------------------------------------------------------
   0. CEK KONDISI AWAL
------------------------------------------------------------------- */

echo "== Backup katalog ==\n\n";

$mysqldump = ek_cari_mysqldump($config['mysqldump']);

if ( ! $mysqldump)
{
	ek_gagal("mysqldump tidak ditemukan. Set EK_MYSQLDUMP ke path lengkapnya.");
}

echo "  mysqldump   : ".$mysqldump."\n";
echo "  database    : ".$config['db']['name']."\n";
echo "  backup ke   : ".$config['backup_dir']."\n";
echo "  arsip disimpan: ".$config['keep']."\n";

// Cegah folder backup berada di dalam web root.
$root_web = realpath($config['root'].DIRECTORY_SEPARATOR.'..');

if ( ! is_dir($config['root']))
{
	ek_gagal("Folder proyek tidak ditemukan: ".$config['root']);
}

$backup_abs = rtrim(str_replace('/', '\\', $config['backup_dir']), '\\');
$root_abs = rtrim(str_replace('/', '\\', $config['root']), '\\');
$web_abs = rtrim(str_replace('/', '\\', (string) $root_web), '\\');

if (strpos($backup_abs, $web_abs.DIRECTORY_SEPARATOR) === 0 OR $backup_abs === $web_abs)
{
	ek_gagal("Folder backup berada di dalam web root (".$web_abs."). Isinya akan bisa diakses lewat browser. Pilih folder lain.");
}

$dir_upload = $config['root'].DIRECTORY_SEPARATOR.$config['uploads'];

if ( ! is_dir($dir_upload))
{
	echo "  CATATAN    : ".$config['uploads']." tidak ada, arsip upload dilewati.\n";
}

/* -------------------------------------------------------------------
   1. PEMERIKSAAN FILE YATIM
------------------------------------------------------------------- */

if ( ! $option['skip_audit'] AND is_dir($dir_upload))
{
	echo "\n-- Pemeriksaan file yatim --\n";

	$audit = array();
	$exec = @shell_exec(escapeshellarg(PHP_BINARY).' '.escapeshellarg($config['root'].DIRECTORY_SEPARATOR.'tools'.DIRECTORY_SEPARATOR.'upload_audit.php').' --json 2>&1');
	$hasil = json_decode((string) $exec, TRUE);

	if (is_array($hasil) AND isset($hasil['ringkasan']))
	{
		$audit = $hasil['ringkasan'];

		echo "  file di disk : ".$audit['di_disk']."\n";
		echo "  terpakai     : ".$audit['terpakai']."\n";
		echo "  yatim        : ".$audit['yatim']."\n";

		if ($audit['yatim'] > 0)
		{
			if ( ! $option['force'])
			{
				echo "\n  Ada file yatim. Jalankan ulang dengan --force untuk tetap membackup,\n";
				echo "  atau pindahkan dulu file yatim ke folder karantina.\n";
				exit(1);
			}

			echo "  Peringatan   : --force dipakai, file yatim tetap ikut terarsip.\n";
		}
	}
	else
	{
		echo "  GAGAL menjalankan audit, lanjut tanpa pemeriksaan.\n";
	}
}
else
{
	echo "\n-- Pemeriksaan file yatim: dilewati --\n";
}

/* -------------------------------------------------------------------
   2. BUAT FOLDER BACKUP
------------------------------------------------------------------- */

$stempel = date('Ymd-His');
$target = $config['backup_dir'].DIRECTORY_SEPARATOR.$stempel;

echo "\n-- Menyiapkan folder --\n";
echo "  ".$target."\n";

if ($option['dry_run'])
{
	echo "\nDry run. Tidak ada yang ditulis.\n";
	exit(0);
}

if ( ! is_dir($target) AND ! @mkdir($target, 0777, TRUE))
{
	ek_gagal("Gagal membuat folder: ".$target);
}

/* -------------------------------------------------------------------
   3. DUMP DATABASE
------------------------------------------------------------------- */

echo "\n-- Dump database --\n";

$file_dump = $target.DIRECTORY_SEPARATOR.$config['db']['name'].'.sql';

// --result-file dipakai, bukan redirect shell, supaya tidak ada file SQL
// yang bisa menyalakan perintah USE ke database lain.
$args = array(
	$mysqldump,
	'--host='.$config['db']['host'],
	'--user='.$config['db']['user'],
	'--single-transaction',
	'--quick',
	'--routines',
	'--triggers',
	'--default-character-set=utf8mb4',
	'--add-drop-table',
	'--result-file='.$file_dump,
);

if ($config['db']['password'] !== '')
{
	$args[] = '--password='.$config['db']['password'];
}

$args[] = $config['db']['name'];

$perintah = implode(' ', array_map('escapeshellarg', $args));
$exit = 0;
$keluaran = array();
exec($perintah.' 2>&1', $keluaran, $exit);

if ($exit !== 0)
{
	ek_gagal("mysqldump gagal (kode ".$exit."): ".implode("\n", $keluaran));
}

if ( ! is_file($file_dump) OR filesize($file_dump) < 100)
{
	ek_gagal("Dump tidak terbentuk atau terlalu kecil: ".$file_dump);
}

$isi = (string) file_get_contents($file_dump);
$hilang_tabel = array();

foreach ($config['wajib'] as $tabel)
{
	if (stripos($isi, 'CREATE TABLE `'.$tabel.'`') === FALSE)
	{
		$hilang_tabel[] = $tabel;
	}
}

if ($hilang_tabel)
{
	ek_gagal("Dump tidak lengkap, tabel berikut tidak ada: ".implode(', ', $hilang_tabel).". Dump dibuang.");
}

echo "  ".$config['db']['name'].".sql  ".ek_bytes(filesize($file_dump))."  (".count($config['wajib'])." tabel wajib lengkap)\n";

/* -------------------------------------------------------------------
   4. ARSIP FILE UPLOAD
------------------------------------------------------------------- */

$file_zip = NULL;

if (is_dir($dir_upload))
{
	echo "\n-- Arsip file upload --\n";

	$file_zip = $target.DIRECTORY_SEPARATOR.'uploads.zip';
	$zip = new ZipArchive();

	if ($zip->open($file_zip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE)
	{
		ek_gagal("Gagal membuat arsip zip: ".$file_zip);
	}

	$jumlah = 0;
	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir_upload, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::SELF_FIRST
	);

	foreach ($iterator as $item)
	{
		if ( ! $item->isFile())
		{
			continue;
		}

		$relatif = 'uploads/'.str_replace('\\', '/', substr($item->getPathname(), strlen($dir_upload) + 1));
		$zip->addFile($item->getPathname(), $relatif);
		$jumlah++;
	}

	$zip->close();

	echo "  uploads.zip  ".ek_bytes(filesize($file_zip))."  (".$jumlah." file)\n";
}
else
{
	echo "\n-- Arsip file upload: dilewati --\n";
}

/* -------------------------------------------------------------------
   5. CATATAN BACKUP
------------------------------------------------------------------- */

$catatan = "Backup katalog\n";
$catatan .= "Waktu    : ".date('Y-m-d H:i:s')."\n";
$catatan .= "Database : ".$config['db']['name']."\n";
$catatan .= "Server   : ".$config['db']['host']."\n";
$catatan .= "PHP      : ".PHP_VERSION."\n";
$catatan .= "Perintah: mysqldump --single-transaction --routines --triggers\n\n";
$catatan .= "Isi folder ini:\n";
$catatan .= "  ".$config['db']['name'].".sql  - dump seluruh tabel\n";

if ($file_zip)
{
	$catatan .= "  uploads.zip        - arsip file di assets/uploads\n";
}

$catatan .= "\nCatatan:\n";
$catatan .= "- Dump memakai --add-drop-table. Saat memulihkan, database yang ada\n";
$catatan .= "  akan ditimpa. Pastikan tidak ada data yang belum dibackup.\n";
$catatan .= "- File di dalam uploads.zip hanya file yang dirujuk baris database\n";
$catatan .= "  pada saat backup dibuat.\n";

file_put_contents($target.DIRECTORY_SEPARATOR.'BACKUP-'.date('Y-m-d_His').'.txt', $catatan);

/* -------------------------------------------------------------------
   6. HAPUS ARSIP LAMA
------------------------------------------------------------------- */

echo "\n-- Membersihkan arsip lama --\n";

$arsip = array();

foreach ((array) @scandir($config['backup_dir']) as $entri)
{
	if ($entri === '.' OR $entri === '..')
	{
		continue;
	}

	$path = $config['backup_dir'].DIRECTORY_SEPARATOR.$entri;

	if (is_dir($path) AND preg_match('/^\d{8}-\d{6}$/', $entri))
	{
		$arsip[$entri] = $path;
	}
}

krsort($arsip);

$pertahankan = array_slice($arsip, 0, $config['keep'], TRUE);
$hapus = array_slice($arsip, $config['keep'], NULL, TRUE);

echo "  total arsip : ".count($arsip)."\n";
echo "  dipertahankan: ".count($pertahankan)."\n";

foreach ($hapus as $nama => $path)
{
	$item = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);

	foreach ($item as $sub)
	{
		$sub->isDir() ? @rmdir($sub->getPathname()) : @unlink($sub->getPathname());
	}

	@rmdir($path);
	echo "  dihapus     : ".$nama."\n";
}

if ( ! $hapus)
{
	echo "  tidak ada yang perlu dihapus.\n";
}

/* -------------------------------------------------------------------
   SELESAI
------------------------------------------------------------------- */

echo "\nSelesai. Folder backup: ".$target."\n";
exit(0);
