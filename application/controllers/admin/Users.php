<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Admin Users Controller
|--------------------------------------------------------------------------
| CRUD manajemen user (admin & member).
*/

class Users extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('User_m');
		$this->load->helper('upload');
		$this->load->library('form_validation');
	}

	public function index()
	{
		$data['title'] = 'Users';
		$data['active_menu'] = 'users';
		$data['items'] = $this->User_m->get_all_users();

		$this->render('admin/users/index', $data);
	}

	public function create()
	{
		$this->_save();
	}

	public function edit($id)
	{
		$this->_save($id);
	}

	private function _save($id = NULL)
	{
		$this->form_validation->set_rules('name', 'Nama', 'required|max_length[20]');
		$this->form_validation->set_rules('email', 'Email', 'required|max_length[30]|valid_email');
		$this->form_validation->set_rules('role', 'Role', 'required|in_list[admin,member]');

		if ( ! $id)
		{
			$this->form_validation->set_rules('password', 'Password', 'required|min_length[6]|max_length[72]');
		}
		else
		{
			$this->form_validation->set_rules('password', 'Password', 'min_length[6]|max_length[72]');
		}

		if ($this->form_validation->run() === FALSE)
		{
			$data['title'] = $id ? 'Edit User' : 'Tambah User';
			$data['active_menu'] = 'users';
			$data['form_errors'] = validation_errors();
			$data['item'] = $id ? $this->User_m->get_by_id($id) : NULL;
			$data['item_id'] = $id;

			if ( ! $data['item'] && $id)
			{
				$this->session->set_flashdata('error', 'Data user tidak ditemukan.');
				redirect('admin/users');
			}

			$this->render('admin/users/form', $data);
			return;
		}

		$post = array(
			'name'      => $this->input->post('name', TRUE),
			'email'     => $this->input->post('email', TRUE),
			'role'      => $this->input->post('role', TRUE),
			'wa_number' => $this->input->post('wa_number', TRUE),
			'is_active' => (int) (bool) $this->input->post('is_active')
		);

		if ( ! empty($_FILES['avatar']['name']))
		{
			$up = ekatalog_upload('avatar', 'avatars');

			if ( ! $up['status'])
			{
				$this->session->set_flashdata('error', 'Avatar gagal diunggah: '.$up['error']);
				redirect($id ? 'admin/users/edit/'.$id : 'admin/users/create');
			}

			if ($id)
			{
				$old = $this->User_m->get_by_id($id);
				if ($old && $old->avatar && strpos($old->avatar, 'http') === FALSE)
				{
					@unlink(FCPATH.'assets/uploads/avatars/'.$old->avatar);
				}
			}

			$post['avatar'] = $up['filename'];
		}

		if ( ! isset($post['avatar']))
		{
			$post['avatar'] = 'avatar1.png';
		}

		$password = $this->input->post('password');

		if ($id)
		{
			$ok = $this->User_m->update($id, $post);
			$msg = 'Data user berhasil diperbarui.';

			if ($ok && $password !== '')
			{
				$this->User_m->change_password($id, $password);
			}
		}
		else
		{
			$post['password'] = password_hash($password, PASSWORD_BCRYPT);
			$new_id = $this->User_m->insert($post);
			$ok = (bool) $new_id;
			$msg = 'Data user berhasil ditambahkan.';
		}

		if ($ok)
		{
			$this->session->set_flashdata('success', $msg);
		}
		else
		{
			$this->session->set_flashdata('error', 'Gagal menyimpan data user.');
		}

		redirect('admin/users');
	}

	public function delete($id)
	{
		$current = $this->User_m->get_by_id($id);

		if ($current && $current->role === 'admin')
		{
			$admins = $this->db->where('role', 'admin')->count_all_results('users');

			if ($admins <= 1)
			{
				$this->session->set_flashdata('error', 'Tidak dapat menghapus admin terakhir.');
				redirect('admin/users');
				return;
			}
		}

		if ($current && $current->avatar && strpos($current->avatar, 'http') === FALSE)
		{
			@unlink(FCPATH.'assets/uploads/avatars/'.$current->avatar);
		}

		$this->db->where('id', $id)->delete('users');

		$this->session->set_flashdata('success', 'Data user berhasil dihapus.');
		redirect('admin/users');
	}
}
