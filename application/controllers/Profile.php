<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Profile Controller
|--------------------------------------------------------------------------
| Profil member: nama, nomor WhatsApp, avatar, dan ganti password.
*/

class Profile extends User_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('User_m');
		$this->load->model('Company_m');
		$this->load->helper('upload');
		$this->load->library('form_validation');
	}

	public function index()
	{
		$user_id = (int) $this->session->userdata('user_id');
		$user    = $this->User_m->get_by_id($user_id);

		$data['title'] = 'Profil';
		$data['user']  = $user;
		$data['company'] = $this->Company_m->get_by_owner($user_id);

		$this->load->view('templates/header', $data);
		$this->load->view('profile/index', $data);
		$this->load->view('templates/footer');
	}

	public function update()
	{
		$user_id = (int) $this->session->userdata('user_id');

		$this->form_validation->set_rules('name', 'Nama', 'required|min_length[3]|max_length[20]');
		$this->form_validation->set_rules('wa_number', 'Nomor WhatsApp', 'regex_match[/^[0-9+\-\s]{9,20}$/]');

		if ($this->form_validation->run() === FALSE)
		{
			$this->session->set_flashdata('error', validation_errors());
			redirect('profile');
		}

		$post = array(
			'name'       => $this->input->post('name', TRUE),
			'wa_number'  => $this->input->post('wa_number', TRUE)
		);

		if ( ! empty($_FILES['avatar']['name']))
		{
			$up = ekatalog_upload('avatar', 'avatars');

			if ( ! $up['status'])
			{
				$this->session->set_flashdata('error', 'Avatar gagal diunggah: '.$up['error']);
				redirect('profile');
			}

			$old = $this->User_m->get_by_id($user_id);
			if ($old && $old->avatar && strpos($old->avatar, 'http') !== 0)
			{
				@unlink(FCPATH.'assets/uploads/avatars/'.$old->avatar);
			}

			$post['avatar'] = $up['filename'];
		}

		$this->User_m->update_profile($user_id, $post);
		$this->session->set_userdata('name', $post['name']);

		if (isset($post['avatar']))
		{
			$this->session->set_userdata('avatar', $post['avatar']);
		}

		$this->session->set_flashdata('success', 'Profil berhasil diperbarui.');
		redirect('profile');
	}

	public function change_password()
	{
		$user_id = (int) $this->session->userdata('user_id');

		$this->form_validation->set_rules('current_password', 'Password Lama', 'required');
		$this->form_validation->set_rules('new_password', 'Password Baru', 'required|min_length[6]|max_length[72]');
		$this->form_validation->set_rules('new_password_confirm', 'Konfirmasi Password Baru', 'required|matches[new_password]');

		if ($this->form_validation->run() === FALSE)
		{
			$this->session->set_flashdata('error', validation_errors());
			redirect('profile');
		}

		$user = $this->User_m->get_by_id($user_id);

		if ( ! $user->password || ! password_verify($this->input->post('current_password'), $user->password))
		{
			$this->session->set_flashdata('error', 'Password lama salah.');
			redirect('profile');
		}

		$this->User_m->change_password($user_id, $this->input->post('new_password'));
		$this->session->set_flashdata('success', 'Password berhasil diganti.');
		redirect('profile');
	}
}
