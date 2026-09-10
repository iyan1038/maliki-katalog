<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Auth Controller
|--------------------------------------------------------------------------
| Autentikasi: login manual, Google OAuth, lupa & reset password.
*/

class Auth extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('User_m');
		$this->load->library('googleauth');
		$this->load->library('form_validation');
	}

	/**
	 * Jika sudah login, arahkan ke halaman sesuai role.
	 */
	private function redirect_logged_in()
	{
		if ($this->session->userdata('logged_in'))
		{
			redirect($this->_home_route());
		}
	}

	/**
	 * Route tujuan setelah login berdasarkan role.
	 */
	private function _home_route()
	{
		return $this->session->userdata('role') === 'admin' ? 'admin/dashboard' : 'catalog';
	}

	/* ------------------------------------------------------------------ */
	/*  LOGIN                                                              */
	/* ------------------------------------------------------------------ */

	public function login()
	{
		$this->redirect_logged_in();

		$this->form_validation->set_rules('email', 'Email', 'required|valid_email|max_length[30]');
		$this->form_validation->set_rules('password', 'Password', 'required|max_length[72]');

		if ($this->form_validation->run() === TRUE)
		{
			$email    = $this->input->post('email', TRUE);
			$password = $this->input->post('password', TRUE);

			$user = $this->User_m->login_by_credentials($email, $password);

			if ($user)
			{
				$this->_set_session($user);
				$this->session->set_flashdata('success', 'Selamat datang, '.$user->name.'!');
				redirect($this->_home_route());
			}

			$this->session->set_flashdata('error', 'Email atau password salah, atau akun dinonaktifkan.');
			redirect('auth/login');
		}

		$data['title'] = 'Login';
		$data['hide_search'] = TRUE;
		$data['form_errors'] = validation_errors();
		$data['google_available'] = $this->googleauth->is_configured();
		$data['google_url'] = $data['google_available'] ? $this->googleauth->get_auth_url() : '';

		$this->load->view('templates/header', $data);
		$this->load->view('auth/login', $data);
		$this->load->view('templates/footer');
	}

	/* ------------------------------------------------------------------ */
	/*  LOGOUT                                                             */
	/* ------------------------------------------------------------------ */

	public function logout()
	{
		$this->session->sess_destroy();
		redirect('auth/login');
	}

	/* ------------------------------------------------------------------ */
	/*  GOOGLE OAUTH                                                       */
	/* ------------------------------------------------------------------ */

	public function google()
	{
		if ( ! $this->googleauth->is_configured())
		{
			$this->session->set_flashdata('error', 'Fitur login Google belum dikonfigurasi.');
			redirect('auth/login');
		}

		redirect($this->googleauth->get_auth_url());
	}

	public function google_callback()
	{
		if ( ! $this->googleauth->is_configured())
		{
			redirect('auth/login');
		}

		$code = $this->input->get('code');

		if ( ! $code)
		{
			$this->session->set_flashdata('error', 'Autentikasi Google dibatalkan.');
			redirect('auth/login');
		}

		$token = $this->googleauth->authenticate($code);

		if ( ! $token)
		{
			$this->session->set_flashdata('error', 'Gagal memproses autentikasi Google.');
			redirect('auth/login');
		}

		$info = $this->googleauth->get_user_info();

		if ( ! $info)
		{
			$this->session->set_flashdata('error', 'Gagal mengambil profil Google.');
			redirect('auth/login');
		}

		$user = $this->User_m->find_or_create_google_user(array(
			'id'      => $info->getId(),
			'name'    => $info->getName(),
			'email'   => $info->getEmail(),
			'picture' => $info->getPicture()
		));

		if ( ! $user || ! (int) $user->is_active)
		{
			$this->session->set_flashdata('error', 'Akun Anda tidak aktif.');
			redirect('auth/login');
		}

		$this->_set_session($user);
		$this->session->set_flashdata('success', 'Selamat datang, '.$user->name.'!');

		if ( ! $user->password)
		{
			$this->session->set_flashdata('success', 'Selamat datang, '.$user->name.'! Akun Anda berhasil dibuat melalui Google. Untuk dapat login secara manual, silakan buat password melalui menu <strong>Profil &rarr; Ubah Password</strong>.');
		}

		redirect($this->_home_route());
	}

	/* ------------------------------------------------------------------ */
	/*  LUPA PASSWORD                                                      */
	/* ------------------------------------------------------------------ */

	public function forgot_password()
	{
		$this->redirect_logged_in();

		$this->form_validation->set_rules('email', 'Email', 'required|valid_email|max_length[30]');

		if ($this->form_validation->run() === TRUE)
		{
			$email = $this->input->post('email', TRUE);

			if ( ! $this->User_m->get_by_email($email))
			{
				$this->session->set_flashdata('error', 'Email tidak terdaftar.');
				redirect('auth/forgot_password');
			}

			$token = $this->User_m->create_reset_token($email);
			$link  = site_url('auth/reset_password/'.$token);

			// Tanpa mail server lokal, tampilkan link reset langsung sebagai notice.
			$this->session->set_flashdata('reset_link', $link);
			$this->session->set_flashdata('success', 'Link reset password telah dibuat. Salin link berikut untuk melanjutkan:');
			redirect('auth/forgot_password');
		}

		$data['title'] = 'Lupa Password';
		$data['hide_search'] = TRUE;
		$data['form_errors'] = validation_errors();

		$this->load->view('templates/header', $data);
		$this->load->view('auth/forgot_password', $data);
		$this->load->view('templates/footer');
	}

	/* ------------------------------------------------------------------ */
	/*  RESET PASSWORD                                                     */
	/* ------------------------------------------------------------------ */

	public function reset_password($token = NULL)
	{
		$this->redirect_logged_in();

		if ( ! $token)
		{
			$token = $this->uri->segment(3);
		}

		$reset = $this->User_m->get_valid_reset_token($token);

		if ( ! $reset)
		{
			$this->session->set_flashdata('error', 'Token reset tidak valid atau sudah kedaluwarsa.');
			redirect('auth/forgot_password');
		}

		$this->form_validation->set_rules('password', 'Password Baru', 'required|min_length[6]|max_length[72]');
		$this->form_validation->set_rules('password_confirm', 'Konfirmasi Password', 'required|matches[password]');

		if ($this->form_validation->run() === TRUE)
		{
			if ($this->User_m->reset_password_by_token($token, $this->input->post('password')))
			{
				$this->session->set_flashdata('success', 'Password berhasil diubah. Silakan login.');
				redirect('auth/login');
			}

			$this->session->set_flashdata('error', 'Gagal mengubah password. Coba lagi.');
			redirect('auth/forgot_password');
		}

		$data['title']    = 'Reset Password';
		$data['hide_search'] = TRUE;
		$data['token']    = $token;
		$data['form_errors'] = validation_errors();

		$this->load->view('templates/header', $data);
		$this->load->view('auth/reset_password', $data);
		$this->load->view('templates/footer');
	}

	/* ------------------------------------------------------------------ */
	/*  INTERNAL                                                           */
	/* ------------------------------------------------------------------ */

	private function _set_session($user)
	{
		$this->session->set_userdata(array(
			'logged_in' => TRUE,
			'user_id'   => $user->id,
			'name'      => $user->name,
			'email'     => $user->email,
			'avatar'    => $user->avatar,
			'role'      => $user->role,
			'dark_mode' => (int) $user->dark_mode
		));
	}
}
