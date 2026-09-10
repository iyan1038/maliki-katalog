<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Home Controller
|--------------------------------------------------------------------------
| Landing aplikasi. Semua user diarahkan ke halaman katalog publik.
*/

class Home extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		redirect('catalog');
	}
}
