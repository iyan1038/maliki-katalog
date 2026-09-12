<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Product_m Model
|--------------------------------------------------------------------------
| Akses data tabel products, product_images, product_kbli, product_platforms.
*/

class Product_m extends CI_Model
{
	protected $table = 'products';

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	public function get_all($search = '', $company_id = NULL)
	{
		$this->db
			->select('products.*, companies.name AS company_name')
			->join('companies', 'companies.id = products.company_id', 'left');

		if ($search !== '')
		{
			$this->db
				->group_start()
				->like('products.name', $search)
				->group_end();
		}

		if ($company_id !== NULL && $company_id > 0)
		{
			$this->db->where('products.company_id', $company_id);
		}

		return $this->db
			->order_by('products.created_at', 'DESC')
			->get($this->table)
			->result();
	}

	public function get_by_id($id)
	{
		return $this->db->where('id', $id)->get($this->table)->row();
	}

	/**
	 * Ambil produk lengkap (company + gambar + KBLI + platform).
	 */
	public function get_detail($id)
	{
		$product = $this->get_by_id($id);

		if ( ! $product)
		{
			return NULL;
		}

		$product->company    = $this->db->where('id', $product->company_id)->get('companies')->row();
		$product->images     = $this->get_images($id);
		$product->kbli       = $this->get_kbli($id);
		$product->platforms  = $this->get_platforms($id);

		return $product;
	}

	public function get_images($product_id)
	{
		return $this->db
			->where('product_id', $product_id)
			->order_by('position', 'ASC')
			->get('product_images')
			->result();
	}

	public function get_kbli($product_id)
	{
		return $this->db
			->select('kbli.id, kbli.code, kbli.name, kbli.description')
			->join('kbli', 'kbli.id = product_kbli.kbli_id')
			->where('product_kbli.product_id', $product_id)
			->get('product_kbli')
			->result();
	}

	public function get_platforms($product_id)
	{
		return $this->db
			->select('product_platforms.*, platforms.name, platforms.slug, platforms.logo, platforms.url')
			->join('platforms', 'platforms.id = product_platforms.platform_id')
			->where('product_platforms.product_id', $product_id)
			->order_by('platforms.name', 'ASC')
			->get('product_platforms')
			->result();
	}

	/**
	 * Ambil produk aktif milik sebuah perusahaan (untuk halaman profil perusahaan).
	 * Opsional: filter kategori ($category_id, via relasi KBLI) dan urutan.
	 *
	 * @param int    $company_id
	 * @param int    $category_id NULL = semua
	 * @param string $sort        recommended|promo|rating|newest|price_asc|price_desc
	 * @param int    $limit
	 * @param int    $offset
	 */
	public function get_by_company($company_id, $category_id = NULL, $sort = 'recommended', $limit = NULL, $offset = 0)
	{
		$this->db
			->select('products.*, companies.name AS company_name, companies.city AS company_city')
			->select('(SELECT pi.filename FROM product_images pi WHERE pi.product_id = products.id ORDER BY pi.position ASC LIMIT 1) AS image')
			->select('(SELECT pl.name FROM product_platforms pp JOIN platforms pl ON pl.id = pp.platform_id WHERE pp.product_id = products.id AND pp.is_visible = 1 ORDER BY pl.name ASC LIMIT 1) AS marketplace_name')
			->select('(SELECT pp.product_url FROM product_platforms pp WHERE pp.product_id = products.id AND pp.is_visible = 1 ORDER BY pp.id ASC LIMIT 1) AS marketplace_url')
			->join('companies', 'companies.id = products.company_id', 'left')
			->where('products.company_id', $company_id)
			->where('products.is_active', 1)
			->where('companies.is_active', 1);

		if ($category_id !== NULL && $category_id > 0)
		{
			$this->db->where("EXISTS (SELECT 1 FROM product_kbli pk JOIN category_kbli ck ON ck.kbli_id = pk.kbli_id WHERE pk.product_id = products.id AND ck.category_id = ".(int) $category_id.")");
		}

		// Urutan mengikuti prioritas README: Promo > Rating > Terbaru (default).
		switch ($sort)
		{
			case 'promo':
				$this->db->order_by('products.is_promo', 'DESC');
				$this->db->order_by('products.promo_price', 'ASC');
				break;
			case 'rating':
				$this->db->order_by('products.avg_rating', 'DESC');
				$this->db->order_by('products.rating_count', 'DESC');
				break;
			case 'price_asc':
				$this->db->order_by('products.price', 'ASC');
				break;
			case 'price_desc':
				$this->db->order_by('products.price', 'DESC');
				break;
			case 'newest':
				$this->db->order_by('products.created_at', 'DESC');
				break;
			case 'recommended':
			default:
				$this->db->order_by('products.is_promo', 'DESC');
				$this->db->order_by('products.avg_rating', 'DESC');
				$this->db->order_by('products.rating_count', 'DESC');
				$this->db->order_by('products.created_at', 'DESC');
				break;
		}

		if ($limit !== NULL)
		{
			$this->db->limit($limit, $offset);
		}

		return $this->db->get($this->table)->result();
	}

	/**
	 * Kategori pertama (urut sort_order lalu nama) dari sebuah produk.
	 * Produk bisa terkait banyak kategori via KBLI; diambil satu saja.
	 *
	 * @return object|NULL  {id, name, slug} atau NULL bila tanpa kategori.
	 */
	public function get_first_category($product_id)
	{
		return $this->db
			->select('categories.id, categories.name, categories.slug')
			->join('category_kbli', 'category_kbli.kbli_id = product_kbli.kbli_id', 'inner')
			->join('categories', 'categories.id = category_kbli.category_id', 'inner')
			->where('product_kbli.product_id', $product_id)
			->where('categories.is_active', 1)
			->order_by('categories.sort_order', 'ASC')
			->order_by('categories.name', 'ASC')
			->limit(1)
			->get('product_kbli')
			->row();
	}

	/**
	 * Produk aktif sebuah perusahaan yang dilengkapi kategori (via KBLI).
	 * Opsional difilter ke satu kategori (mis. untuk baris rekomendasi CV).
	 */
	public function get_by_company_with_categories($company_id, $category_id = NULL)
	{
		$this->db
			->select('products.*, companies.name AS company_name')
			->select('(SELECT pi.filename FROM product_images pi WHERE pi.product_id = products.id ORDER BY pi.position ASC LIMIT 1) AS image')
			->select('(SELECT pl.name FROM product_platforms pp JOIN platforms pl ON pl.id = pp.platform_id WHERE pp.product_id = products.id AND pp.is_visible = 1 ORDER BY pl.name ASC LIMIT 1) AS marketplace_name')
			->select('(SELECT pp.product_url FROM product_platforms pp WHERE pp.product_id = products.id AND pp.is_visible = 1 ORDER BY pp.id ASC LIMIT 1) AS marketplace_url')
			->select('categories.id AS category_id, categories.name AS category_name, categories.slug AS category_slug')
			->from('products')
			->join('companies', 'companies.id = products.company_id', 'left')
			->join('product_kbli', 'product_kbli.product_id = products.id', 'inner')
			->join('category_kbli', 'category_kbli.kbli_id = product_kbli.kbli_id', 'inner')
			->join('categories', 'categories.id = category_kbli.category_id', 'inner')
			->where('products.company_id', $company_id)
			->where('products.is_active', 1)
			->where('companies.is_active', 1)
			->where('categories.is_active', 1);

		if ($category_id !== NULL && $category_id > 0)
		{
			$this->db->where('categories.id', (int) $category_id);
		}

		return $this->db
			->group_by('products.id')
			->order_by('categories.sort_order', 'ASC')
			->order_by('categories.name', 'ASC')
			->order_by('products.created_at', 'DESC')
			->get()
			->result();
	}

	public function insert($data)
	{
		if ( ! $this->db->insert($this->table, $data))
		{
			return FALSE;
		}

		return $this->db->insert_id();
	}

	public function update($id, $data)
	{
		return $this->db->where('id', $id)->update($this->table, $data);
	}

	/**
	 * Hapus produk + relasi + file gambar dari server.
	 */
	public function delete($id)
	{
		$images = $this->get_images($id);

		$this->db->trans_start();
		$this->db->where('product_id', $id)->delete('product_images');
		$this->db->where('product_id', $id)->delete('product_kbli');
		$this->db->where('product_id', $id)->delete('product_platforms');
		$this->db->where('id', $id)->delete($this->table);
		$this->db->trans_complete();

		if ($this->db->trans_status())
		{
			foreach ($images as $img)
			{
				$path = FCPATH.'assets/uploads/products/'.$img->filename;
				if (is_file($path))
				{
					@unlink($path);
				}
			}
		}

		return $this->db->trans_status();
	}

	public function set_kbli($product_id, array $kbli_ids)
	{
		$this->db->where('product_id', $product_id)->delete('product_kbli');

		if (empty($kbli_ids))
		{
			return TRUE;
		}

		$data = array();
		foreach ($kbli_ids as $kbli_id)
		{
			$data[] = array('product_id' => $product_id, 'kbli_id' => (int) $kbli_id);
		}

		return $this->db->insert_batch('product_kbli', $data);
	}

	/**
	 * Sinkronisasi platform + URL produk di tiap marketplace.
	 * $platforms = array(platform_id => array('url' => ..., 'visible' => 0|1))
	 */
	public function set_platforms($product_id, array $platforms)
	{
		$this->db->where('product_id', $product_id)->delete('product_platforms');

		$data = array();
		foreach ($platforms as $platform_id => $info)
		{
			if ( ! $platform_id)
			{
				continue;
			}

			if (is_array($info))
			{
				$url     = isset($info['url']) ? $info['url'] : '';
				$visible = isset($info['visible']) ? (int) $info['visible'] : 0;
			}
			else
			{
				$url     = $info;
				$visible = 0;
			}

			$data[] = array(
				'product_id'  => $product_id,
				'platform_id' => (int) $platform_id,
				'is_visible'  => $visible,
				'product_url' => $url
			);
		}

		if (empty($data))
		{
			return TRUE;
		}

		return $this->db->insert_batch('product_platforms', $data);
	}

	/**
	 * Posisi berikutnya untuk gambar produk (maksimal 4).
	 */
	public function next_image_position($product_id)
	{
		$row = $this->db
			->select('COUNT(*) AS total')
			->where('product_id', $product_id)
			->get('product_images')
			->row();

		return (int) $row->total + 1;
	}

	public function add_image($product_id, $filename, $position)
	{
		return $this->db->insert('product_images', array(
			'product_id' => $product_id,
			'filename'   => $filename,
			'position'   => $position
		));
	}

	public function delete_image($image_id)
	{
		$img = $this->db->where('id', $image_id)->get('product_images')->row();

		if ( ! $img)
		{
			return FALSE;
		}

		// Rapatkan posisi gambar lain.
		$this->db->query('UPDATE product_images SET position = position - 1 WHERE product_id = ? AND position > ?', array($img->product_id, $img->position));

		$this->db->where('id', $image_id)->delete('product_images');

		$path = FCPATH.'assets/uploads/products/'.$img->filename;
		if (is_file($path))
		{
			@unlink($path);
		}

		return TRUE;
	}

	/* ------------------------------------------------------------------ */
	/*  QUERY PUBLIK (KATALOG)                                             */
	/* ------------------------------------------------------------------ */

	/**
	 * Daftar produk untuk katalog publik.
	 * Hanya produk aktif dari perusahaan aktif.
	 *
	 * @param string $search       Kata kunci (nama produk/perusahaan/deskripsi)
	 * @param int    $platform_id  Filter marketplace tujuan (NULL = semua)
	 * @param int    $category_id  Filter kategori produk berdasar KBLI (NULL = semua)
	 * @param string $sort         recommended|promo|rating|newest|price_asc|price_desc
	 * @param int    $limit
	 * @param int    $offset
	 */
	public function public_query($search = '', $platform_id = NULL, $category_id = NULL, $sort = 'recommended', $limit = 20, $offset = 0, $user_id = NULL)
	{
		// Skor preferensi dihitung lebih dulu (men-reset query builder),
		// sehingga tidak merusak state _public_base di bawah.
		$this->load->library('sorter');
		$scores   = array();
		$adaptive = FALSE;

		if ($sort === 'recommended' && $user_id)
		{
			$scores = $this->sorter->preference_scores($user_id);
			$adaptive = $this->sorter->is_adaptive($scores);
		}

		$this->_public_base($search, $platform_id, $category_id);

		if ($adaptive)
		{
			$this->db->select('('.$this->sorter->score_subquery($scores).') AS pref_score', FALSE);
		}

		switch ($sort)
		{
			case 'promo':
				$this->db->order_by('p.is_promo', 'DESC');
				$this->db->order_by('p.promo_price', 'ASC');
				break;
			case 'rating':
				$this->db->order_by('p.avg_rating', 'DESC');
				$this->db->order_by('p.rating_count', 'DESC');
				break;
			case 'newest':
				$this->db->order_by('p.created_at', 'DESC');
				break;
			case 'price_asc':
				$this->db->order_by('p.price', 'ASC');
				break;
			case 'price_desc':
				$this->db->order_by('p.price', 'DESC');
				break;
			case 'recommended':
			default:
				// Prioritas README: Promo > Kebiasaan user > Rating > Terbaru
				$this->db->order_by('p.is_promo', 'DESC');
				if ($adaptive)
				{
					$this->db->order_by('pref_score', 'DESC');
				}
				$this->db->order_by('p.avg_rating', 'DESC');
				$this->db->order_by('p.rating_count', 'DESC');
				$this->db->order_by('p.created_at', 'DESC');
				break;
		}

		return $this->db->limit($limit, $offset)->get()->result();
	}

	/**
	 * Jumlah produk untuk katalog publik (untuk pagination).
	 */
	public function count_public($search = '', $platform_id = NULL, $category_id = NULL)
	{
		$this->_public_base($search, $platform_id, $category_id);
		$row = $this->db->select('COUNT(p.id) AS total')->get()->row();

		return (int) $row->total;
	}

	/**
	 * Base query publik.
	 */
	private function _public_base($search = '', $platform_id = NULL, $category_id = NULL)
	{
		$this->db
			->select('p.*, c.name AS company_name, c.city AS company_city')
			->select('(SELECT pi.filename FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.position ASC LIMIT 1) AS image')
			->select('(SELECT pl.name FROM product_platforms pp JOIN platforms pl ON pl.id = pp.platform_id WHERE pp.product_id = p.id AND pp.is_visible = 1 ORDER BY pl.name ASC LIMIT 1) AS marketplace_name')
			->select('(SELECT pp.product_url FROM product_platforms pp WHERE pp.product_id = p.id AND pp.is_visible = 1 ORDER BY pp.id ASC LIMIT 1) AS marketplace_url')
			->from('products p')
			->join('companies c', 'c.id = p.company_id')
			->where('p.is_active', 1)
			->where('c.is_active', 1);

		if ($search !== '')
		{
			$this->db->group_start();
			$this->db->like('p.name', $search);
			$this->db->or_like('c.name', $search);
			$this->db->or_like('p.description', $search);
			$this->db->group_end();
		}

		if ($platform_id !== NULL && $platform_id > 0)
		{
			$this->db->where("EXISTS (SELECT 1 FROM product_platforms pp WHERE pp.product_id = p.id AND pp.platform_id = ".(int) $platform_id." AND pp.is_visible = 1)");
		}

		if ($category_id !== NULL && $category_id > 0)
		{
			$this->db->where("EXISTS (SELECT 1 FROM product_kbli pk JOIN category_kbli ck ON ck.kbli_id = pk.kbli_id WHERE pk.product_id = p.id AND ck.category_id = ".(int) $category_id.")");
		}
	}

	/**
	 * Gambar pertama sebuah produk.
	 */
	public function get_featured_image($product_id)
	{
		$img = $this->db
			->where('product_id', $product_id)
			->order_by('position', 'ASC')
			->limit(1)
			->get('product_images')
			->row();

		return $img ? $img->filename : NULL;
	}

	/**
	 * Naikkan penghitung views produk.
	 */
	public function increment_views($id)
	{
		return $this->db->query('UPDATE products SET total_views = total_views + 1 WHERE id = ?', array($id));
	}

	/**
	 * Produk terkait berdasarkan KBLI yang sama.
	 */
	public function get_related($product_id, $limit = 4)
	{
		$kbli = $this->get_kbli($product_id);

		if (empty($kbli))
		{
			return array();
		}

		$kbli_ids = array();
		foreach ($kbli as $k)
		{
			$kbli_ids[] = $k->id;
		}

		return $this->db
			->select('p.*, c.name AS company_name')
			->select('(SELECT pi.filename FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.position ASC LIMIT 1) AS image')
			->select('(SELECT pl.name FROM product_platforms pp JOIN platforms pl ON pl.id = pp.platform_id WHERE pp.product_id = p.id AND pp.is_visible = 1 ORDER BY pl.name ASC LIMIT 1) AS marketplace_name')
			->select('(SELECT pp.product_url FROM product_platforms pp WHERE pp.product_id = p.id AND pp.is_visible = 1 ORDER BY pp.id ASC LIMIT 1) AS marketplace_url')
			->from('products p')
			->join('companies c', 'c.id = p.company_id')
			->join('product_kbli pk', 'pk.product_id = p.id')
			->where_in('pk.kbli_id', $kbli_ids)
			->where('p.is_active', 1)
			->where('c.is_active', 1)
			->where('p.id !=', $product_id)
			->group_by('p.id')
			->order_by('p.avg_rating', 'DESC')
			->order_by('p.rating_count', 'DESC')
			->limit($limit)
			->get()
			->result();
	}
}
