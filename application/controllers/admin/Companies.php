<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Admin Companies Controller
|--------------------------------------------------------------------------
| CRUD perusahaan beserta pemilik, logo, dan KBLI.
*/

class Companies extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Company_m');
		$this->load->model('Kbli_m');
		$this->load->model('User_m');
		$this->load->helper('upload');
		$this->load->library('form_validation');
	}

	public function index()
	{
		$data['title'] = 'Perusahaan';
		$data['active_menu'] = 'companies';
		$data['items'] = $this->Company_m->get_all();

		$this->render('admin/companies/index', $data);
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
		$this->form_validation->set_rules('user_id', 'Pemilik', 'required|integer');
		$this->form_validation->set_rules('name', 'Nama Perusahaan', 'required|max_length[30]');
		$this->form_validation->set_rules('npwp', 'NPWP', 'max_length[30]');
		$this->form_validation->set_rules('city', 'Kota', 'max_length[30]');
		$this->form_validation->set_rules('description', 'Deskripsi', 'max_length[5000]');
		$this->form_validation->set_rules('address', 'Alamat', 'max_length[5000]');

		if ($this->form_validation->run() === FALSE)
		{
			$data['title'] = $id ? 'Edit Perusahaan' : 'Tambah Perusahaan';
			$data['active_menu'] = 'companies';
			$data['form_errors'] = validation_errors();
			$data['item'] = $id ? $this->Company_m->get_detail($id) : NULL;
			$data['item_id'] = $id;
			$data['owners'] = $this->User_m->get_all_users();
			$data['kbli_list'] = $this->Kbli_m->get_all();

			if ( ! $data['item'] && $id)
			{
				$this->session->set_flashdata('error', 'Data perusahaan tidak ditemukan.');
				redirect('admin/companies');
			}

			$this->render('admin/companies/form', $data);
			return;
		}

		$post = array(
			'user_id'     => (int) $this->input->post('user_id'),
			'name'        => $this->input->post('name', TRUE),
			'npwp'        => $this->input->post('npwp', TRUE),
			'description' => $this->input->post('description', TRUE),
			'address'     => $this->input->post('address', TRUE),
			'city'        => $this->input->post('city', TRUE),
			'is_active'   => (int) (bool) $this->input->post('is_active')
		);

		if ( ! empty($_FILES['logo']['name']))
		{
			$up = ekatalog_upload('logo', 'companies');

			if ( ! $up['status'])
			{
				$this->session->set_flashdata('error', 'Logo gagal diunggah: '.$up['error']);
				redirect($id ? 'admin/companies/edit/'.$id : 'admin/companies/create');
			}

			if ($id)
			{
				$old = $this->Company_m->get_by_id($id);
				if ($old && $old->logo)
				{
					@unlink(FCPATH.'assets/uploads/companies/'.$old->logo);
				}
			}

			$post['logo'] = $up['filename'];
		}

		if ($id)
		{
			$ok = $this->Company_m->update($id, $post);
			$msg = 'Data perusahaan berhasil diperbarui.';
		}
		else
		{
			$new_id = $this->Company_m->insert($post);
			$ok = (bool) $new_id;
			$id = $new_id;
			$msg = 'Data perusahaan berhasil ditambahkan.';
		}

		if ($ok)
		{
			$this->Company_m->set_kbli($id, (array) $this->input->post('kbli'));
			$this->session->set_flashdata('success', $msg);
		}
		else
		{
			$this->session->set_flashdata('error', 'Gagal menyimpan data perusahaan.');
		}

		redirect('admin/companies');
	}

	public function delete($id)
	{
		if ($this->Company_m->delete($id))
		{
			$this->session->set_flashdata('success', 'Data perusahaan berhasil dihapus.');
		}
		else
		{
			$this->session->set_flashdata('error', 'Gagal menghapus data perusahaan.');
		}

		redirect('admin/companies');
	}
}
