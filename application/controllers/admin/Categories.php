<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Admin Categories Controller
|--------------------------------------------------------------------------
| CRUD kategori produk + relasi kategori -> KBLI. Relasi diatur sekali,
| produk otomatis masuk kategori lewat KBLI yang dimilikinya.
*/

class Categories extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Category_m');
		$this->load->model('Kbli_m');
		$this->load->library('form_validation');
	}

	public function index()
	{
		$data['title'] = 'Kategori';
		$data['active_menu'] = 'categories';

		$items = $this->Category_m->get_all();
		foreach ($items as $item)
		{
			$item->kbli_count = count($this->Category_m->get_kbli_ids($item->id));
		}

		$data['items'] = $items;

		$this->render('admin/categories/index', $data);
	}

	public function create()
	{
		$this->_save();
	}

	public function edit($id)
	{
		$this->_save($id);
	}

	/**
	 * Validasi slug unik (abaikan diri sendiri saat edit).
	 */
	public function _slug_unique($str, $id = NULL)
	{
		$this->db->where('slug', $str);

		if ($id !== NULL)
		{
			$this->db->where('id !=', (int) $id);
		}

		if ($this->db->count_all_results('categories') > 0)
		{
			$this->form_validation->set_message('_slug_unique', 'Slug sudah digunakan oleh kategori lain.');
			return FALSE;
		}

		return TRUE;
	}

	private function _save($id = NULL)
	{
		$this->form_validation->set_rules('name', 'Nama Kategori', 'required|max_length[30]');
		$this->form_validation->set_rules('slug', 'Slug', 'required|alpha_dash|max_length[30]|callback__slug_unique'.($id ? '['.$id.']' : ''));
		$this->form_validation->set_rules('sort_order', 'Urutan', 'integer');
		$this->form_validation->set_rules('kbli[]', 'KBLI', 'integer');

		if ($this->form_validation->run() === FALSE)
		{
			$data['title'] = $id ? 'Edit Kategori' : 'Tambah Kategori';
			$data['active_menu'] = 'categories';
			$data['form_errors'] = validation_errors();
			$data['item'] = $id ? $this->Category_m->get_by_id($id) : NULL;
			$data['item_id'] = $id;
			$data['kbli_list'] = $this->Kbli_m->get_all();
			$data['selected_kbli'] = $id ? $this->Category_m->get_kbli_ids($id) : array();

			if ( ! $data['item'] && $id)
			{
				$this->session->set_flashdata('error', 'Data kategori tidak ditemukan.');
				redirect('admin/categories');
			}

			$this->render('admin/categories/form', $data);
			return;
		}

		$post = array(
			'name'       => $this->input->post('name', TRUE),
			'slug'       => strtolower($this->input->post('slug', TRUE)),
			'sort_order' => (int) $this->input->post('sort_order'),
			'is_active'  => (int) (bool) $this->input->post('is_active')
		);

		if ($id)
		{
			$ok = $this->Category_m->update($id, $post);
			$msg = 'Data kategori berhasil diperbarui.';
		}
		else
		{
			$ok = $this->Category_m->insert($post);
			$id = $ok ? (int) $this->db->insert_id() : NULL;
			$msg = 'Data kategori berhasil ditambahkan.';
		}

		if ($ok && $id)
		{
			$this->Category_m->set_kbli($id, (array) $this->input->post('kbli'));
			$this->session->set_flashdata('success', $msg);
		}
		else
		{
			$this->session->set_flashdata('error', 'Gagal menyimpan data kategori.');
		}

		redirect('admin/categories');
	}

	public function delete($id)
	{
		if ($this->Category_m->delete($id))
		{
			$this->session->set_flashdata('success', 'Data kategori berhasil dihapus.');
		}
		else
		{
			$this->session->set_flashdata('error', 'Gagal menghapus data kategori.');
		}

		redirect('admin/categories');
	}
}