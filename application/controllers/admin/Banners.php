<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Admin Banners Controller
|--------------------------------------------------------------------------
| CRUD banner promosi.
*/

class Banners extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Banner_m');
		$this->load->helper('upload');
		$this->load->library('form_validation');
	}

	public function index()
	{
		$data['title'] = 'Banner';
		$data['active_menu'] = 'banners';
		$data['items'] = $this->Banner_m->get_all();

		$this->render('admin/banners/index', $data);
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
		$this->form_validation->set_rules('title', 'Judul', 'required|max_length[30]');
		$this->form_validation->set_rules('url', 'URL Tujuan', 'valid_url|max_length[255]');
		$this->form_validation->set_rules('position', 'Posisi', 'required|in_list[top,middle,bottom]');
		$this->form_validation->set_rules('sort_order', 'Urutan', 'integer');

		if ($this->form_validation->run() === FALSE)
		{
			$data['title'] = $id ? 'Edit Banner' : 'Tambah Banner';
			$data['active_menu'] = 'banners';
			$data['form_errors'] = validation_errors();
			$data['item'] = $id ? $this->Banner_m->get_by_id($id) : NULL;
			$data['item_id'] = $id;

			if ( ! $data['item'] && $id)
			{
				$this->session->set_flashdata('error', 'Data banner tidak ditemukan.');
				redirect('admin/banners');
			}

			$this->render('admin/banners/form', $data);
			return;
		}

		// Gambar wajib untuk banner baru; opsional untuk edit.
		if ( ! $id && empty($_FILES['image']['name']))
		{
			$this->session->set_flashdata('error', 'Gambar banner wajib diunggah.');
			redirect('admin/banners/create');
		}

		$post = array(
			'title'      => $this->input->post('title', TRUE),
			'url'        => $this->input->post('url', TRUE),
			'position'   => $this->input->post('position', TRUE),
			'sort_order' => (int) $this->input->post('sort_order'),
			'is_active'  => (int) (bool) $this->input->post('is_active')
		);

		if ( ! empty($_FILES['image']['name']))
		{
			$up = ekatalog_upload('image', 'banners');

			if ( ! $up['status'])
			{
				$this->session->set_flashdata('error', 'Gambar banner gagal diunggah: '.$up['error']);
				redirect($id ? 'admin/banners/edit/'.$id : 'admin/banners/create');
			}

			if ($id)
			{
				$old = $this->Banner_m->get_by_id($id);
				if ($old && $old->image)
				{
					@unlink(FCPATH.'assets/uploads/banners/'.$old->image);
				}
			}

			$post['image'] = $up['filename'];
		}

		if ($id)
		{
			$ok = $this->Banner_m->update($id, $post);
			$msg = 'Banner berhasil diperbarui.';
		}
		else
		{
			$ok = $this->Banner_m->insert($post);
			$msg = 'Banner berhasil ditambahkan.';
		}

		if ($ok)
		{
			$this->session->set_flashdata('success', $msg);
		}
		else
		{
			$this->session->set_flashdata('error', 'Gagal menyimpan banner.');
		}

		redirect('admin/banners');
	}

	public function delete($id)
	{
		if ($this->Banner_m->delete($id))
		{
			$this->session->set_flashdata('success', 'Banner berhasil dihapus.');
		}
		else
		{
			$this->session->set_flashdata('error', 'Gagal menghapus banner.');
		}

		redirect('admin/banners');
	}
}
