<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Google\Client as Google_Client;
use Google\Service\Oauth2 as Google_Service_Oauth2;

/*
|--------------------------------------------------------------------------
| GoogleAuth Library
|--------------------------------------------------------------------------
| Wrapper Google OAuth 2.0 (google/apiclient).
| Jika kredensial masih placeholder, is_configured() mengembalikan FALSE
| sehingga tombol "Login dengan Google" disembunyikan.
*/

class GoogleAuth
{
	protected $ci;
	protected $client;
	protected $configured = FALSE;

	public function __construct()
	{
		$this->ci =& get_instance();
		$this->ci->config->load('google');

		$client_id     = $this->ci->config->item('google_client_id');
		$client_secret = $this->ci->config->item('google_client_secret');
		$redirect_uri  = $this->ci->config->item('google_redirect_uri');

		if ($client_id && $client_secret
			&& strpos($client_id, 'YOUR_') === FALSE
			&& strpos($client_secret, 'YOUR_') === FALSE)
		{
			$this->configured = TRUE;
			$this->client = new Google_Client();
			$this->client->setClientId($client_id);
			$this->client->setClientSecret($client_secret);
			$this->client->setRedirectUri($redirect_uri);
			$this->client->addScope($this->ci->config->item('google_scopes'));
			$this->client->setAccessType('offline');
			$this->client->setPrompt('select_account');
		}
	}

	/**
	 * Apakah kredensial Google sudah dikonfigurasi?
	 */
	public function is_configured()
	{
		return $this->configured;
	}

	/**
	 * URL otorisasi Google.
	 */
	public function get_auth_url()
	{
		return $this->client ? $this->client->createAuthUrl() : '';
	}

	/**
	 * Tukar kode otorisasi menjadi access token.
	 */
	public function authenticate($code)
	{
		if ( ! $this->client)
		{
			return FALSE;
		}

		try
		{
			$this->client->authenticate($code);
			return $this->client->getAccessToken();
		}
		catch (Exception $e)
		{
			log_message('error', 'GoogleAuth::authenticate - '.$e->getMessage());
			return FALSE;
		}
	}

	/**
	 * Data profil pengguna Google (name, email, id, picture).
	 */
	public function get_user_info()
	{
		if ( ! $this->client)
		{
			return NULL;
		}

		try
		{
			$oauth = new Google_Service_Oauth2($this->client);
			return $oauth->userinfo->get();
		}
		catch (Exception $e)
		{
			log_message('error', 'GoogleAuth::get_user_info - '.$e->getMessage());
			return NULL;
		}
	}
}
