<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Sorter Library
|--------------------------------------------------------------------------
| Menghitung skor preferensi user per kategori KBLI dan menyusun urutan
| sortir adaptif katalog. Prioritas README: Promo > Kebiasaan user > Rating
| > Produk terbaru.
*/

class Sorter
{
	protected $ci;

	public function __construct()
	{
		$this->ci =& get_instance();
	}

	/**
	 * Skor preferensi user per KBLI.
	 * Berasal dari user_behaviors (view=1, click=3) dan search_logs (2).
	 *
	 * @return array kbli_id => score
	 */
	public function preference_scores($user_id)
	{
		if ( ! $user_id)
		{
			return array();
		}

		$this->ci->load->model('Behavior_m');

		return $this->ci->Behavior_m->get_kbli_scores($user_id);
	}

	/**
	 * Apakah preferensi user ada (katalog adaptif aktif)?
	 */
	public function is_adaptive(array $scores)
	{
		return ! empty($scores);
	}

	/**
	 * Subquery SQL untuk kolom pref_score sebuah produk
	 * (skor terbesar di antara KBLI produk tersebut).
	 */
	public function score_subquery(array $scores)
	{
		if (empty($scores))
		{
			return '0';
		}

		$rows = array();
		foreach ($scores as $kid => $score)
		{
			$rows[] = 'SELECT '.(int) $kid.' AS kid, '.(int) $score.' AS score';
		}

		$union = implode(' UNION ALL ', $rows);

		return '(SELECT COALESCE(MAX(pr.score), 0)
			FROM product_kbli pk2
			JOIN ('.$union.') pr ON pr.kid = pk2.kbli_id
			WHERE pk2.product_id = p.id)';
	}
}
