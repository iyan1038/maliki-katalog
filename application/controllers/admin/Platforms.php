<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Admin Platforms Controller
|--------------------------------------------------------------------------
| CRUD platform marketplace tujuan (Siplah, Tokoladang, dll).
*/

class Platforms extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Platform_m');
		$this->load->helper('upload');
		$this->load->library('form_validation');
	}

	public function index()
	{
		$data['title'] = 'Platform';
		$data['active_menu'] = 'platforms';
		$data['items'] = $this->Platform_m->get_all();

		$this->render('admin/platforms/index', $data);
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
		$this->form_validation->set_rules('name', 'Nama Platform', 'required|max_length[30]');
		$this->form_validation->set_rules('slug', 'Slug', 'required|alpha_dash|max_length[30]');
		$this->form_validation->set_rules('url', 'URL', 'required|valid_url|max_length[255]');

		if ($this->form_validation->run() === FALSE)
		{
			$data['title'] = $id ? 'Edit Platform' : 'Tambah Platform';
			$data['active_menu'] = 'platforms';
			$data['form_errors'] = validation_errors();
			$data['item'] = $id ? $this->Platform_m->get_by_id($id) : NULL;
			$data['item_id'] = $id;

			if ( ! $data['item'] && $id)
			{
				$this->session->set_flashdata('error', 'Data platform tidak ditemukan.');
				redirect('admin/platforms');
			}

			$this->render('admin/platforms/form', $data);
			return;
		}

		$post = array(
			'name' => $this->input->post('name', TRUE),
			'slug' => $this->input->post('slug', TRUE),
			'url'  => $this->input->post('url', TRUE)
		);

		// Logo (opsional)
		if ( ! empty($_FILES['logo']['name']))
		{
			$up = ekatalog_upload('logo', 'platforms');

			if ( ! $up['status'])
			{
				$this->session->set_flashdata('error', 'Logo gagal diunggah: '.$up['error']);
				redirect($id ? 'admin/platforms/edit/'.$id : 'admin/platforms/create');
			}

			if ($id)
			{
				$old = $this->Platform_m->get_by_id($id);
				if ($old && $old->logo)
				{
					@unlink(FCPATH.'assets/uploads/platforms/'.$old->logo);
				}
			}

			$post['logo'] = $up['filename'];
		}

		if ($id)
		{
			$ok = $this->Platform_m->update($id, $post);
			$msg = 'Data platform berhasil diperbarui.';
		}
		else
		{
			$ok = $this->Platform_m->insert($post);
			$msg = 'Data platform berhasil ditambahkan.';
		}

		if ($ok)
		{
			$this->session->set_flashdata('success', $msg);
		}
		else
		{
			$this->session->set_flashdata('error', 'Gagal menyimpan data platform.');
		}

		redirect('admin/platforms');
	}

	public function delete($id)
	{
		if ($this->Platform_m->delete($id))
		{
			$this->session->set_flashdata('success', 'Data platform berhasil dihapus.');
		}
		else
		{
			$this->session->set_flashdata('error', 'Gagal menghapus data platform.');
		}

		redirect('admin/platforms');
	}
}
