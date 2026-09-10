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
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
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
			'created_at'    => date('Y-m-d H:i:s')
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
}
