<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Google OAuth 2.0 Configuration
|--------------------------------------------------------------------------
| Isi dengan kredensial OAuth dari Google Cloud Console:
|   https://console.cloud.google.com/apis/credentials
|
| Selama placeholder (belum diisi), fitur login Google dinonaktifkan
| secara otomatis oleh GoogleAuth library.
*/

$config['google_client_id']     = '9225366754-hli6d17merq0q4ppcj00fq44e1eba9as.apps.googleusercontent.com';
$config['google_client_secret'] = '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';
$config['google_redirect_uri']  = 'http://'.($_SERVER['HTTP_HOST'] ?? 'localhost').'/katalog/auth/google_callback';
$config['google_scopes']        = array('email', 'profile');
