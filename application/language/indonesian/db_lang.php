<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Indonesian Language - Database (CodeIgniter)
|--------------------------------------------------------------------------
*/

$lang['db_invalid_connection_str'] = 'Tidak dapat menentukan pengaturan database berdasarkan string koneksi yang Anda kirimkan.';
$lang['db_unable_to_connect'] = 'Tidak dapat terhubung ke server database menggunakan pengaturan yang diberikan.';
$lang['db_unable_to_select'] = 'Tidak dapat memilih database: %s';
$lang['db_unable_to_create'] = 'Tidak dapat membuat database: %s';
$lang['db_invalid_query'] = 'Query yang Anda kirimkan tidak valid.';
$lang['db_must_set_table'] = 'Anda harus menentukan tabel database untuk digunakan dalam query.';
$lang['db_must_use_set'] = 'Anda harus menggunakan metode "set" untuk memperbarui sebuah entri.';
$lang['db_must_use_index'] = 'Anda harus menentukan indeks untuk pencocokan pada pembaruan batch.';
$lang['db_batch_missing_index'] = 'Satu atau lebih baris yang dikirim untuk pembaruan batch tidak memiliki indeks yang ditentukan.';
$lang['db_must_use_where'] = 'Pembaruan tidak diizinkan kecuali mengandung klausa "where".';
$lang['db_del_must_use_where'] = 'Penghapusan tidak diizinkan kecuali mengandung klausa "where" atau "like".';
$lang['db_field_param_missing'] = 'Untuk mengambil field diperlukan nama tabel sebagai parameter.';
$lang['db_unsupported_function'] = 'Fitur ini tidak tersedia untuk database yang Anda gunakan.';
$lang['db_transaction_failure'] = 'Kegagalan transaksi: Rollback telah dilakukan.';
$lang['db_unable_to_drop'] = 'Tidak dapat menghapus database yang ditentukan.';
$lang['db_unsupported_feature'] = 'Fitur tidak didukung pada platform database yang Anda gunakan.';
$lang['db_unsupported_compression'] = 'Format kompresi file yang Anda pilih tidak didukung oleh server Anda.';
$lang['db_filepath_error'] = 'Tidak dapat menulis data ke lokasi file yang Anda kirimkan.';
$lang['db_invalid_cache_path'] = 'Lokasi cache yang Anda kirimkan tidak valid atau tidak dapat ditulisi.';
$lang['db_table_name_required'] = 'Nama tabel diperlukan untuk operasi tersebut.';
$lang['db_column_name_required'] = 'Nama kolom diperlukan untuk operasi tersebut.';
$lang['db_column_definition_required'] = 'Definisi kolom diperlukan untuk operasi tersebut.';
$lang['db_unable_to_set_charset'] = 'Tidak dapat mengatur charset koneksi klien: %s';
$lang['db_error_heading'] = 'Terjadi Kesalahan Database';