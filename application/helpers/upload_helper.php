<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Upload Helper
|--------------------------------------------------------------------------
| Fungsi bantu upload gambar (produk, banner, logo perusahaan).
| File disimpan di assets/uploads/<folder>/ dengan nama unik.
*/

if ( ! function_exists('ekatalog_upload'))
{
	/**
	 * Upload satu file gambar.
	 *
	 * @param string $field  Nama field input file
	 * @param string $folder Subfolder di assets/uploads (mis. 'products', 'banners')
	 * @return array ['status' => bool, 'filename' => string|NULL, 'error' => string]
	 */
	function ekatalog_upload($field, $folder)
	{
		$ci =& get_instance();
		$ci->load->library('upload');
		$ci->load->helper('string');

		$upload_path = FCPATH.'assets/uploads/'.$folder.'/';

		if ( ! is_dir($upload_path))
		{
			mkdir($upload_path, 0777, TRUE);
		}

		$config = array(
			'upload_path'   => $upload_path,
			'allowed_types' => 'jpg|jpeg|png|webp|svg',
			'max_size'      => 4096, // KB
			'max_width'     => 5000,
			'max_height'    => 5000,
			'encrypt_name'  => TRUE,
			'file_name'     => $folder.'_'.random_string('alnum', 12)
		);

		$ci->upload->initialize($config);

		if ( ! $ci->upload->do_upload($field))
		{
			return array(
				'status'   => FALSE,
				'filename' => NULL,
				'error'    => $ci->upload->display_errors('', '')
			);
		}

		$data = $ci->upload->data();

		return array(
			'status'   => TRUE,
			'filename' => $data['file_name'],
			'error'    => ''
		);
	}
}

if ( ! function_exists('ekatalog_upload_many'))
{
	/**
	 * Upload beberapa file gambar dari satu field (input multiple).
	 *
	 * @param string $field  Nama field input file (berakhiran [])
	 * @param string $folder Subfolder di assets/uploads
	 * @param int    $max    Jumlah maksimal file yang diizinkan
	 * @return array ['status', 'files' => [filename], 'errors' => [string]]
	 */
	function ekatalog_upload_many($field, $folder, $max)
	{
		$ci =& get_instance();
		$ci->load->library('upload');
		$ci->load->helper('string');

		if (empty($_FILES[$field]['name'][0]))
		{
			return array('status' => TRUE, 'files' => array(), 'errors' => array());
		}

		$upload_path = FCPATH.'assets/uploads/'.$folder.'/';

		if ( ! is_dir($upload_path))
		{
			mkdir($upload_path, 0777, TRUE);
		}

		$files   = array();
		$errors  = array();
		$total   = count($_FILES[$field]['name']);

		for ($i = 0; $i < $total; $i++)
		{
			if ($i >= $max)
			{
				$errors[] = 'Jumlah file melebihi batas maksimal '.$max.'.';
				break;
			}

			$_FILES['ekatalog_multi']['name']     = $_FILES[$field]['name'][$i];
			$_FILES['ekatalog_multi']['type']     = $_FILES[$field]['type'][$i];
			$_FILES['ekatalog_multi']['tmp_name'] = $_FILES[$field]['tmp_name'][$i];
			$_FILES['ekatalog_multi']['error']    = $_FILES[$field]['error'][$i];
			$_FILES['ekatalog_multi']['size']     = $_FILES[$field]['size'][$i];

			$config = array(
				'upload_path'   => $upload_path,
				'allowed_types' => 'jpg|jpeg|png|webp|svg',
				'max_size'      => 4096,
				'max_width'     => 5000,
				'max_height'    => 5000,
				'encrypt_name'  => TRUE,
				'file_name'     => $folder.'_'.random_string('alnum', 12)
			);

			$ci->upload->initialize($config);

			if ( ! $ci->upload->do_upload('ekatalog_multi'))
			{
				$errors[] = 'File #'.($i + 1).': '.$ci->upload->display_errors('', '');
				continue;
			}

			$files[] = $ci->upload->data('file_name');
		}

		return array(
			'status'  => empty($errors) || ! empty($files),
			'files'   => $files,
			'errors'  => $errors
		);
	}
}
