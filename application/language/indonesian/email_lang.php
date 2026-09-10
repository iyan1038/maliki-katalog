<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Indonesian Language - Email (CodeIgniter)
|--------------------------------------------------------------------------
*/

$lang['email_must_be_array'] = 'Metode validasi email harus menerima sebuah array.';
$lang['email_invalid_address'] = 'Alamat email tidak valid: %s';
$lang['email_attachment_missing'] = 'Tidak dapat menemukan lampiran email berikut: %s';
$lang['email_attachment_unreadable'] = 'Tidak dapat membuka lampiran ini: %s';
$lang['email_no_from'] = 'Tidak dapat mengirim email tanpa header "From".';
$lang['email_no_recipients'] = 'Anda harus menyertakan penerima: To, Cc, atau Bcc.';
$lang['email_send_failure_phpmail'] = 'Tidak dapat mengirim email menggunakan PHP mail(). Server Anda mungkin tidak dikonfigurasi untuk mengirim email dengan metode ini.';
$lang['email_send_failure_sendmail'] = 'Tidak dapat mengirim email menggunakan PHP Sendmail. Server Anda mungkin tidak dikonfigurasi untuk mengirim email dengan metode ini.';
$lang['email_send_failure_smtp'] = 'Tidak dapat mengirim email menggunakan PHP SMTP. Server Anda mungkin tidak dikonfigurasi untuk mengirim email dengan metode ini.';
$lang['email_sent'] = 'Pesan Anda berhasil dikirim menggunakan protokol berikut: %s';
$lang['email_no_socket'] = 'Tidak dapat membuka socket untuk Sendmail. Silakan periksa pengaturan.';
$lang['email_no_hostname'] = 'Anda tidak menentukan hostname SMTP.';
$lang['email_smtp_error'] = 'Terjadi kesalahan SMTP berikut: %s';
$lang['email_no_smtp_unpw'] = 'Kesalahan: Anda harus menetapkan username dan password SMTP.';
$lang['email_failed_smtp_login'] = 'Gagal mengirim perintah AUTH LOGIN. Kesalahan: %s';
$lang['email_smtp_auth_un'] = 'Gagal mengautentikasi username. Kesalahan: %s';
$lang['email_smtp_auth_pw'] = 'Gagal mengautentikasi password. Kesalahan: %s';
$lang['email_smtp_data_failure'] = 'Tidak dapat mengirim data: %s';
$lang['email_exit_status'] = 'Kode status keluar: %s';