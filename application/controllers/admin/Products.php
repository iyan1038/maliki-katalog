<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Admin Products Controller
|--------------------------------------------------------------------------
| CRUD produk: data utama, maksimal 4 gambar, kategori KBLI, dan
| tautan platform marketplace (tombol redirect).
*/

class Products extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Product_m');
		$this->load->model('Company_m');
		$this->load->model('Kbli_m');
		$this->load->model('Platform_m');
		$this->load->helper('upload');
		$this->load->library('form_validation');
	}

	public function index()
	{
		$data['title'] = 'Produk';
		$data['active_menu'] = 'products';
		$data['items'] = $this->Product_m->get_all();

		$this->render('admin/products/index', $data);
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
	 * Hapus satu gambar produk (admin/products/delete_image/<id>).
	 */
	public function delete_image($image_id)
	{
		$image = $this->db->where('id', $image_id)->get('product_images')->row();

		if ( ! $image)
		{
			$this->session->set_flashdata('error', 'Gambar tidak ditemukan.');
			redirect('admin/products');
		}

		$this->Product_m->delete_image($image_id);
		$this->session->set_flashdata('success', 'Gambar produk berhasil dihapus.');
		redirect('admin/products/edit/'.$image->product_id);
	}

	private function _save($id = NULL)
	{
		$this->form_validation->set_rules('company_id', 'Perusahaan', 'required|integer');
		$this->form_validation->set_rules('name', 'Nama Produk', 'required|max_length[200]');
		$this->form_validation->set_rules('price', 'Harga', 'required|numeric');
		$this->form_validation->set_rules('unit', 'Satuan', 'max_length[50]');
		$this->form_validation->set_rules('promo_price', 'Harga Promo', 'numeric');
		$this->form_validation->set_rules('description', 'Deskripsi', 'max_length[5000]');
		$this->form_validation->set_rules('long_description', 'Deskripsi Panjang', 'max_length[5000]');
		$this->form_validation->set_rules('platform_url[]', 'URL Platform', 'max_length[255]');
		$this->form_validation->set_rules('kbli[]', 'KBLI', 'integer');

		if ($this->form_validation->run() === FALSE)
		{
			$data['title'] = $id ? 'Edit Produk' : 'Tambah Produk';
			$data['active_menu'] = 'products';
			$data['form_errors'] = validation_errors();
			$data['item'] = $id ? $this->Product_m->get_detail($id) : NULL;
			$data['item_id'] = $id;
			$data['companies'] = $this->Company_m->get_all_active();
			$data['kbli_list'] = $this->Kbli_m->get_all();
			$data['platforms'] = $this->Platform_m->get_all();
			$data['max_images'] = 4;

			if ( ! $data['item'] && $id)
			{
				$this->session->set_flashdata('error', 'Data produk tidak ditemukan.');
				redirect('admin/products');
			}

			$this->render('admin/products/form', $data);
			return;
		}

		$is_promo = (int) (bool) $this->input->post('is_promo');

		$post = array(
			'company_id'  => (int) $this->input->post('company_id'),
			'name'        => $this->input->post('name', TRUE),
			'price'       => (float) $this->input->post('price'),
			'unit'        => $this->input->post('unit', TRUE),
			'description' => $this->input->post('description', TRUE),
			'long_description' => $this->input->post('long_description', TRUE),
			'is_promo'    => $is_promo,
			'promo_price' => $is_promo ? (float) $this->input->post('promo_price') : NULL,
			'is_featured' => (int) (bool) $this->input->post('is_featured'),
			'is_active'   => (int) (bool) $this->input->post('is_active')
		);

		if ($id)
		{
			$ok = $this->Product_m->update($id, $post);
			$msg = 'Data produk berhasil diperbarui.';
		}
		else
		{
			$new_id = $this->Product_m->insert($post);
			$ok = (bool) $new_id;
			$id = $new_id;
			$msg = 'Data produk berhasil ditambahkan.';
		}

		if ( ! $ok)
		{
			$this->session->set_flashdata('error', 'Gagal menyimpan data produk.');
			redirect($id ? 'admin/products/edit/'.$id : 'admin/products');
		}

		// Upload gambar baru (maksimal 4 total).
		$existing_count = count($this->Product_m->get_images($id));
		$max_new = 4 - $existing_count;

		if ($max_new > 0 && ! empty($_FILES['images']['name'][0]))
		{
			$up = ekatalog_upload_many('images', 'products', $max_new);
			$position = $this->Product_m->next_image_position($id);

			foreach ($up['files'] as $filename)
			{
				$this->Product_m->add_image($id, $filename, $position);
				$position++;
			}

			if ( ! empty($up['errors']))
			{
				$this->session->set_flashdata('error', 'Beberapa gambar gagal diunggah: '.implode('; ', $up['errors']));
			}
		}

		// Relasi KBLI & platform.
		$this->Product_m->set_kbli($id, (array) $this->input->post('kbli'));

		$platforms_post = $this->input->post('platform_url');
		$visible_post   = $this->input->post('platform_visible');

		$platforms = array();
		if (is_array($platforms_post))
		{
			foreach ($platforms_post as $platform_id => $url)
			{
				$platforms[(int) $platform_id] = array(
					'url'     => trim($url),
					'visible' => (int) (bool) (isset($visible_post[$platform_id]) ? $visible_post[$platform_id] : 0)
				);
			}
		}
		$this->Product_m->set_platforms($id, $platforms);

		$this->session->set_flashdata('success', $msg);
		redirect('admin/products');
	}

	public function delete($id)
	{
		if ($this->Product_m->delete($id))
		{
			$this->session->set_flashdata('success', 'Data produk beserta gambarnya berhasil dihapus.');
		}
		else
		{
			$this->session->set_flashdata('error', 'Gagal menghapus data produk.');
		}

		redirect('admin/products');
	}
}
