<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Admin Settings Controller
|--------------------------------------------------------------------------
| CRUD pengaturan global aplikasi (key-value).
*/

class Settings extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Settings_m');
		$this->load->library('form_validation');
	}

	public function index()
	{
		$data['title'] = 'Pengaturan';
		$data['active_menu'] = 'settings';
		$data['items'] = $this->Settings_m->get_all();

		$this->render('admin/settings/index', $data);
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
		$this->form_validation->set_rules('key', 'Key', 'required|max_length[100]');
		$this->form_validation->set_rules('value', 'Value', 'required|max_length[5000]');

		if ($id)
		{
			$this->form_validation->set_rules('key', 'Key', 'required|max_length[100]|callback_unique_key_edit['.$id.']');
		}
		else
		{
			$this->form_validation->set_rules('key', 'Key', 'required|max_length[100]|is_unique[settings.key]');
		}

		if ($this->form_validation->run() === FALSE)
		{
			$data['title'] = $id ? 'Edit Pengaturan' : 'Tambah Pengaturan';
			$data['active_menu'] = 'settings';
			$data['form_errors'] = validation_errors();
			$data['item'] = $id ? $this->Settings_m->get_by_id($id) : NULL;
			$data['item_id'] = $id;

			if ( ! $data['item'] && $id)
			{
				$this->session->set_flashdata('error', 'Data pengaturan tidak ditemukan.');
				redirect('admin/settings');
			}

			$this->render('admin/settings/form', $data);
			return;
		}

		$post = array(
			'key'   => $this->input->post('key', TRUE),
			'value' => $this->input->post('value', TRUE)
		);

		if ($id)
		{
			$ok = $this->Settings_m->update($id, $post);
			$msg = 'Pengaturan berhasil diperbarui.';
		}
		else
		{
			$new_id = $this->Settings_m->insert($post);
			$ok = (bool) $new_id;
			$msg = 'Pengaturan berhasil ditambahkan.';
		}

		if ($ok)
		{
			$this->session->set_flashdata('success', $msg);
		}
		else
		{
			$this->session->set_flashdata('error', 'Gagal menyimpan pengaturan.');
		}

		redirect('admin/settings');
	}

	public function delete($id)
	{
		if ($this->Settings_m->delete($id))
		{
			$this->session->set_flashdata('success', 'Pengaturan berhasil dihapus.');
		}
		else
		{
			$this->session->set_flashdata('error', 'Gagal menghapus pengaturan.');
		}

		redirect('admin/settings');
	}

	/**
	 * Callback validasi: key unik kecuali milik record yang sedang di-edit.
	 */
	public function unique_key_edit($key, $id)
	{
		$existing = $this->Settings_m->get_by_key($key);

		if ($existing && $existing->id != $id)
		{
			$this->form_validation->set_message('unique_key_edit', 'Key "%s" sudah digunakan.');
			return FALSE;
		}

		return TRUE;
	}
}
