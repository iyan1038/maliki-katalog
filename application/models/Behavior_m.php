<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Behavior_m Model
|--------------------------------------------------------------------------
| Tracking perilaku user (view/click/search) untuk personalisasi katalog.
*/

class Behavior_m extends CI_Model
{
	/** Jeda minimal (menit) sebelum kunjungan produk+platform yang sama dihitung lagi. */
	const VISIT_THROTTLE_MINUTES = 30;

	/** Batas item riwayat yang boleh dihapus dalam satu request hapus terpilih. */
	const MAX_BULK_ITEMS = 100;

	/** Panjang maksimum keyword yang dibaca dari POST (search_logs.keyword VARCHAR(150)). */
	const KEYWORD_MAX_LEN = 150;

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * Catat kunjungan (klik ke marketplace) ke sebuah produk.
	 *
	 * Berlaku untuk SEMUA pengunjung, termasuk anonim:
	 * - products.visit_count dinaikkan (denormalized, tidak butuh user_id).
	 * - user_behaviors hanya diisi bila user login, agar skor kebiasaan
	 *   personalisasi (KBLI) tetap bekerja.
	 *
	 * Dibatasi satu kunjungan per produk+platform per 30 menit agar reload
	 * atau klik berulang tidak menginflasi angka.
	 *
	 * @param int $user_id     NULL bila anonim
	 * @param int $product_id
	 * @param int $platform_id NULL bila tidak spesifik platform
	 * @return bool TRUE bila kunjungan terhitung
	 */
	public function register_visit($user_id, $product_id, $platform_id = NULL)
	{
		$product_id = (int) $product_id;

		if ( ! $product_id)
		{
			return FALSE;
		}

		$platform_id = $platform_id ? (int) $platform_id : 0;

		if ($this->visit_throttled($product_id, $platform_id))
		{
			return FALSE;
		}

		$this->mark_visit($product_id, $platform_id);

		$this->db->query(
			'UPDATE products SET visit_count = visit_count + 1 WHERE id = ? AND is_active = 1',
			array($product_id)
		);

		if ($user_id)
		{
			$this->track($user_id, $product_id, 'click', $platform_id ?: NULL);
		}

		return TRUE;
	}

	/**
	 * Sudah pernah dihitung dalam jendela throttle?
	 */
	public function visit_throttled($product_id, $platform_id = 0)
	{
		$seen = $this->session->userdata('ek_visit_seen');

		if ( ! is_array($seen) || ! isset($seen[$this->visit_key($product_id, $platform_id)]))
		{
			return FALSE;
		}

		return (time() - (int) $seen[$this->visit_key($product_id, $platform_id)]) < (self::VISIT_THROTTLE_MINUTES * 60);
	}

	/**
	 * Tandai waktu kunjungan terakhir untuk throttle.
	 */
	public function mark_visit($product_id, $platform_id = 0)
	{
		$seen = $this->session->userdata('ek_visit_seen');
		$seen = is_array($seen) ? $seen : array();

		$seen[$this->visit_key($product_id, $platform_id)] = time();

		// Jaga session tidak membengkak.
		if (count($seen) > 200)
		{
			asort($seen);
			$seen = array_slice($seen, -200, NULL, TRUE);
		}

		$this->session->set_userdata('ek_visit_seen', $seen);
	}

	/**
	 * Kunci unik throttle per produk + platform (+ user bila login) supaya
	 * satu browser yang dipakai beberapa akun tetap terpisah.
	 */
	private function visit_key($product_id, $platform_id = 0)
	{
		$user_id = (int) $this->session->userdata('user_id');

		return $product_id.'_'.$platform_id.'_'.$user_id;
	}

	/**
	 * Catat perilaku user terhadap produk.
	 *
	 * @param int    $user_id
	 * @param int    $product_id
	 * @param string $type   view|click
	 * @param int    $platform_id NULL jika bukan klik marketplace
	 */
	public function track($user_id, $product_id, $type, $platform_id = NULL)
	{
		if ( ! $user_id || ! $product_id)
		{
			return FALSE;
		}

		return $this->db->insert('user_behaviors', array(
			'user_id'       => $user_id,
			'product_id'    => $product_id,
			'behavior_type' => $type === 'click' ? 'click' : 'view',
			'platform_id'   => $platform_id ? (int) $platform_id : NULL,
			'created_at'    => date('Y-m-d H:i:s', WIB_NOW)
		));
	}

	/**
	 * Catat kata kunci pencarian.
	 */
	public function track_search($user_id, $keyword)
	{
		return $this->db->insert('search_logs', array(
			'user_id'  => $user_id ? (int) $user_id : NULL,
			'keyword'  => $keyword
		));
	}

	/**
	 * Skor preferensi user per KBLI.
	 * - user_behaviors: view = 1, click = 3 (dipetakan via product_kbli).
	 * - search_logs: kata kunci yang cocok dengan nama/kode KBLI = +2.
	 *
	 * @return array kbli_id => score (diurutkan menurun, dibatasi).
	 */
	public function get_kbli_scores($user_id, $limit = 12)
	{
		if ( ! $user_id)
		{
			return array();
		}

		// Bersihkan state query builder (bisa terisi dari query lain).
		$this->db->reset_query();

		$scores = array();

		// Skor dari perilaku produk.
		$beh = $this->db->query(
			"SELECT pk.kbli_id,
			        SUM(CASE ub.behavior_type WHEN 'click' THEN 3 ELSE 1 END) AS score
			 FROM user_behaviors ub
			 JOIN product_kbli pk ON pk.product_id = ub.product_id
			 WHERE ub.user_id = ?
			 GROUP BY pk.kbli_id",
			array($user_id)
		)->result();

		foreach ($beh as $r)
		{
			$scores[(int) $r->kbli_id] = (int) $r->score;
		}

		// Boost dari kata kunci pencarian yang cocok dengan KBLI.
		$this->db->reset_query();
		$logs = $this->db
			->select('keyword')
			->where('user_id', $user_id)
			->order_by('id', 'DESC')
			->limit(20)
			->get('search_logs')
			->result();

		foreach ($logs as $log)
		{
			$kw = trim($log->keyword);

			if ($kw === '')
			{
				continue;
			}

			$this->db->reset_query();
			$kbs = $this->db
				->select('id')
				->group_start()
					->like('name', $kw)
					->or_like('code', $kw)
				->group_end()
				->get('kbli')
				->result();

			foreach ($kbs as $k)
			{
				$kid = (int) $k->id;
				$scores[$kid] = isset($scores[$kid]) ? $scores[$kid] + 2 : 2;
			}
		}

		arsort($scores);
		$scores = array_slice($scores, 0, $limit, TRUE);

		return $scores;
	}

	/**
	 * Produk yang pernah dibuka (view) atau diklik (click) oleh member.
	 *
	 * Dipakai untuk halaman "Riwayat". Satu produk hanya muncul sekali,
	 * diurutkan dari kunjungan terakhir.
	 *
	 * Sub-select di dalam FROM dipakai karena MySQL 5.7+ (ONLY_FULL_GROUP_BY)
	 * tidak mengizinkan "SELECT p.*" bersama "GROUP BY product_id".
	 * LIMIT di dalam sub-select juga membatasi pekerjaan grup hanya pada
	 * kunjungan terbaru, bukan seluruh riwayat.
	 *
	 * Hanya produk yang aktif DAN perusahaannya aktif yang dikembalikan,
	 * supaya konsisten dengan katalog (Product_m::_public_base) dan etalase
	 * perusahaan. Produk dari perusahaan yang dinonaktifkan admin tidak
	 * muncul di riwayat walau produknya sendiri belum dinonaktifkan.
	 *
	 * @param int $user_id
	 * @param int $limit
	 * @return object[] Baris produk siap pakai templates/product_card.
	 */
	public function get_recent_products($user_id, $limit = 24)
	{
		$user_id = (int) $user_id;
		$limit   = max(1, min(100, (int) $limit));

		if ( ! $user_id)
		{
			return array();
		}

		$this->db->reset_query();

		return $this->db->query(
			'SELECT p.*, c.name AS company_name, c.city AS company_city,
			        (SELECT pi.filename FROM product_images pi
			          WHERE pi.product_id = p.id ORDER BY pi.position ASC LIMIT 1) AS image,
			        (SELECT pl.name FROM product_platforms pp JOIN platforms pl ON pl.id = pp.platform_id
			          WHERE pp.product_id = p.id AND pp.is_visible = 1 ORDER BY pl.name ASC LIMIT 1) AS marketplace_name,
			        (SELECT pp.product_url FROM product_platforms pp
			          WHERE pp.product_id = p.id AND pp.is_visible = 1 ORDER BY pp.id ASC LIMIT 1) AS marketplace_url,
			        ub.last_seen
			 FROM (
			     SELECT product_id, MAX(created_at) AS last_seen
			     FROM user_behaviors
			     WHERE user_id = ? AND behavior_type IN (\'view\',\'click\')
			     GROUP BY product_id
			     ORDER BY last_seen DESC
			     LIMIT '.$limit.'
			 ) ub
			 JOIN products p ON p.id = ub.product_id
			 JOIN companies c ON c.id = p.company_id
			 WHERE p.is_active = 1 AND c.is_active = 1
			 ORDER BY ub.last_seen DESC',
			array($user_id)
		)->result();
	}

	/**
	 * Kata kunci yang pernah dicari member, terbaru dulu, tanpa duplikat.
	 *
	 * @param int $user_id
	 * @param int $limit
	 * @return object[] Baris: keyword, last_used.
	 */
	public function get_recent_keywords($user_id, $limit = 10)
	{
		$user_id = (int) $user_id;
		$limit   = max(1, min(50, (int) $limit));

		if ( ! $user_id)
		{
			return array();
		}

		$this->db->reset_query();

		return $this->db->query(
			'SELECT keyword, MAX(created_at) AS last_used
			 FROM search_logs
			 WHERE user_id = ?
			 GROUP BY keyword
			 ORDER BY last_used DESC
			 LIMIT '.$limit,
			array($user_id)
		)->result();
	}

	/**
	 * Hapus seluruh riwayat member: kunjungan produk dan kata kunci pencarian.
	 *
	 * Catatan: menghapus user_behaviors sekaligus mengembalikan skor
	 * personalisasi KBLI ke netral, karena get_kbli_scores() sumbernya
	 * tabel yang sama.
	 *
	 * @param int $user_id
	 * @return bool
	 */
	public function clear_history($user_id)
	{
		$user_id = (int) $user_id;

		if ( ! $user_id)
		{
			return FALSE;
		}

		$this->db->where('user_id', $user_id)->delete('user_behaviors');
		$this->db->where('user_id', $user_id)->delete('search_logs');

		return TRUE;
	}

	/**
	 * Hapus satu produk dari riwayat member (baris view + click).
	 *
	 * Dipakai ikon trash di pojok kanan kartu produk pada halaman "Riwayat".
	 * Tabel favorites dan products.visit_count TIDAK ikut terhapus, sama
	 * seperti clear_history() — yang hilang hanya catatan riwayat produk itu.
	 *
	 * @param int $user_id
	 * @param int $product_id
	 * @return int Jumlah baris behavior yang terhapus
	 */
	public function delete_product_history($user_id, $product_id)
	{
		$user_id    = (int) $user_id;
		$product_id = (int) $product_id;

		if ( ! $user_id || ! $product_id)
		{
			return 0;
		}

		return (int) $this->db
			->where('user_id', $user_id)
			->where('product_id', $product_id)
			->delete('user_behaviors');
	}

	/**
	 * Hapus item riwayat yang dipilih user.
	 *
	 * $scope menentukan tabel & kolom yang dipakai:
	 * - produk    : product_id di user_behaviors (view + click)
	 * - pencarian : keyword di search_logs (seluruh kemunculan kata itu,
	 *               bukan hanya satu baris terakhir)
	 *
	 * Nilai dari POST tidak pernah dipakai mentah: produk di-(int) dan
	 * kata kunci di-trim + dipotong. Duplikat dibuang lewat array key,
	 * dan jumlah item dibatasi MAX_BULK_ITEMS supaya satu POST tidak
	 * bisa mengosongkan seluruh riwayat tanpa sengaja.
	 *
	 * @param int    $user_id
	 * @param string $scope  produk|pencarian
	 * @param array  $items  Nilai mentah dari POST items[]
	 * @return int Jumlah item unik yang dihapus (produk atau kata kunci)
	 */
	public function delete_items($user_id, $scope, array $items)
	{
		$user_id = (int) $user_id;

		if ( ! $user_id || ! $items)
		{
			return 0;
		}

		$is_product = ($scope === 'produk');
		$clean      = array();

		foreach (array_slice($items, 0, self::MAX_BULK_ITEMS) as $raw)
		{
			if ($is_product)
			{
				$id = (int) $raw;

				if ($id)
				{
					$clean[$id] = $id;
				}

				continue;
			}

			$keyword = trim((string) $raw);

			if ($keyword !== '')
			{
				$keyword        = mb_substr($keyword, 0, self::KEYWORD_MAX_LEN);
				$clean[$keyword] = $keyword;
			}
		}

		if ( ! $clean)
		{
			return 0;
		}

		$clean = array_values($clean);

		if ($is_product)
		{
			$this->db
				->where('user_id', $user_id)
				->where_in('product_id', $clean)
				->delete('user_behaviors');
		}
		else
		{
			$this->db
				->where('user_id', $user_id)
				->where_in('keyword', $clean)
				->delete('search_logs');
		}

		return count($clean);
	}
}
