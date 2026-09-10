<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Admin Kbli Controller
|--------------------------------------------------------------------------
| CRUD Kode KBLI (klasifikasi baku lapangan usaha).
*/

class Kbli extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Kbli_m');
		$this->load->library('form_validation');
	}

	public function index()
	{
		$data['title'] = 'KBLI';
		$data['active_menu'] = 'kbli';
		$data['items'] = $this->Kbli_m->get_all();

		$this->render('admin/kbli/index', $data);
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
		$this->form_validation->set_rules('code', 'Kode KBLI', 'required|max_length[10]|callback__code_unique['.$id.']');
		$this->form_validation->set_rules('name', 'Nama KBLI', 'required|max_length[45]');
		$this->form_validation->set_rules('description', 'Deskripsi', 'max_length[5000]');

		if ($this->form_validation->run() === FALSE)
		{
			$data['title'] = $id ? 'Edit KBLI' : 'Tambah KBLI';
			$data['active_menu'] = 'kbli';
			$data['form_errors'] = validation_errors();
			$data['item'] = $id ? $this->Kbli_m->get_by_id($id) : NULL;
			$data['item_id'] = $id;

			if ( ! $data['item'] && $id)
			{
				$this->session->set_flashdata('error', 'Data KBLI tidak ditemukan.');
				redirect('admin/kbli');
			}

			$this->render('admin/kbli/form', $data);
			return;
		}

		$post = array(
			'code' => trim($this->input->post('code', TRUE)),
			'name' => $this->input->post('name', TRUE),
			'description' => $this->input->post('description', TRUE)
		);

		if ($id)
		{
			$ok = $this->Kbli_m->update($id, $post);
			$msg = 'Data KBLI berhasil diperbarui.';
		}
		else
		{
			$ok = $this->Kbli_m->insert($post);
			$msg = 'Data KBLI berhasil ditambahkan.';
		}

		if ($ok)
		{
			$this->session->set_flashdata('success', $msg);
		}
		else
		{
			$this->session->set_flashdata('error', 'Gagal menyimpan data KBLI.');
		}

		redirect('admin/kbli');
	}

	public function delete($id)
	{
		if ($this->Kbli_m->delete($id))
		{
			$this->session->set_flashdata('success', 'Data KBLI berhasil dihapus.');
		}
		else
		{
			$this->session->set_flashdata('error', 'Gagal menghapus data KBLI.');
		}

		redirect('admin/kbli');
	}

	public function _code_unique($code, $id)
	{
		$code = trim($code);
		$id   = (int) $id;

		$this->db->from('kbli')->where('code', $code);
		if ($id)
		{
			$this->db->where('id !=', $id);
		}

		if ($this->db->count_all_results() > 0)
		{
			$this->form_validation->set_message('_code_unique', 'Kode KBLI %s sudah digunakan.');
			return FALSE;
		}

		return TRUE;
	}
}
