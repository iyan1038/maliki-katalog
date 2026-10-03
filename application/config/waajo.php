<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| WAAJO API Configuration
|--------------------------------------------------------------------------
| API key & device ID dari akun WAAJO (https://waajo.id).
|
| Format API (diverifikasi dari Postman collection "WhatsappV2Public"):
|   Base URL : https://api.waajo.id
|   Kirim teks : POST /go-omni-v2/public/whatsapp/send-text
|   Header  : apikey: {waajo_api_key}
|   Body    : { "recipient_number": "628xxx", "text": "...", "check_session": false }
|
| Selama placeholder (belum diisi), kirim pesan WhatsApp dinonaktifkan
| oleh Waajo library.
*/

$config['waajo_api_key']  = '9c7233af7e2e454';
$config['waajo_base_url'] = 'https://api.waajo.id';
$config['waajo_device']   = '6289654641336';


$config['waajo_media_url'] = 'https://tests-traditional-modelling-tire.trycloudflare.com/katalog';