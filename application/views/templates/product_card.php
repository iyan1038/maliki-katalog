<?php defined('BASEPATH') OR exit('No direct script access allowed');
$card_price    = ($product->is_promo && $product->promo_price !== NULL) ? $product->promo_price : $product->price;
$card_old_price = ($product->is_promo && $product->promo_price !== NULL) ? $product->price : NULL;
$card_img = $product->image ? base_url('assets/uploads/products/'.$product->image) : base_url('assets/images/no-image.png');

/*
| Varian kartu:
| - (kosong)  : katalog, produk terkait, favorit — tombol wishlist (heart)
| - 'history' : halaman Riwayat — ikon trash, atau checkbox saat mode
|               "pilih beberapa" dinyalakan (card_select). Trash dan
|               checkbox berbagi satu slot pojok kanan atas gambar, jadi
|               hanya salah satunya yang dirender.
| $card_bulk_form = id form hapus-terpilih di toolbar. Checkbox sengaja
|               TIDAK diletakkan di dalam form itu (form tidak boleh
|               bersarang: tiap kartu punya form trash sendiri), melainkan
|               ditautkan lewat atribut HTML5 form="...".
*/
$card_variant = isset($card_variant) ? $card_variant : '';
$card_select  = ! empty($card_select);
$card_bulk    = isset($card_bulk_form) ? $card_bulk_form : '';
?>
<div class="col">
	<div class="card ek-product">
		<a href="<?php echo site_url('product/'.$product->id); ?>" class="text-decoration-none">
			<div class="ek-product-img">
				<img src="<?php echo $card_img; ?>" alt="<?php echo htmlspecialchars($product->name); ?>" loading="lazy">
				<?php if ($product->is_promo): ?>
					<span class="ek-badge-promo">PROMO</span>
				<?php endif; ?>
				<?php if ($card_variant === 'history'): ?>
					<?php if ($card_select): ?>
						<label class="ek-hist-check-wrap" for="ekHist<?php echo (int) $product->id; ?>">
							<input type="checkbox" class="ek-hist-check" id="ekHist<?php echo (int) $product->id; ?>"
								form="<?php echo htmlspecialchars($card_bulk); ?>" name="items[]"
								value="<?php echo (int) $product->id; ?>"
								title="Pilih produk ini" aria-label="Pilih produk ini untuk dihapus dari riwayat">
						</label>
					<?php else: ?>
						<?php echo form_open('history/delete_product/'.$product->id, array('class' => 'ek-hist-trash', 'onsubmit' => 'return confirm(\'Hapus produk ini dari riwayat?\');')); ?>
							<button type="submit" class="ek-fav-btn ek-hist-trash-btn" title="Hapus dari riwayat" aria-label="Hapus produk ini dari riwayat"><i class="bi bi-trash" aria-hidden="true"></i></button>
						<?php echo form_close(); ?>
					<?php endif; ?>
				<?php elseif ($this->session->userdata('logged_in') && $this->session->userdata('role') === 'member'): ?>
					<?php
					$back_url = current_url();
					if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '')
					{
						$back_url .= '?'.$_SERVER['QUERY_STRING'];
					}
					?>
					<?php echo form_open('favorite/toggle', array('class' => 'ek-fav')); ?>
						<input type="hidden" name="product_id" value="<?php echo $product->id; ?>">
						<input type="hidden" name="redirect" value="<?php echo htmlspecialchars($back_url); ?>">
						<button type="submit" class="ek-fav-btn <?php echo ! empty($product->is_favorited) ? 'ek-fav-active' : ''; ?>" title="Favorit">&#9829;</button>
					<?php echo form_close(); ?>
				<?php endif; ?>
			</div>			<div class="card-body pb-1">
				<div class="ek-product-title"><?php echo htmlspecialchars($product->name); ?></div>
				<div class="ek-product-price">
					Rp <?php echo number_format($card_price, 0, ',', '.'); ?>
					<?php if ($card_old_price !== NULL): ?>
						<del class="ek-product-oldprice"><?php echo number_format($card_old_price, 0, ',', '.'); ?></del>
					<?php endif; ?>
				</div>
				<div class="ek-product-visit">
					<span class="ek-visit-icon" aria-hidden="true">&#128100;</span>
					<?php echo number_format((int) $product->visit_count, 0, ',', '.'); ?> kunjungan
				</div>
				<div class="ek-product-company"><?php echo htmlspecialchars($product->company_name); ?></div>
				<?php if ( ! empty($product->note)): ?>
					<div class="ek-product-note"><?php echo htmlspecialchars($product->note); ?></div>
				<?php endif; ?>
			</div>
		</a>
		<div class="card-body pt-0">
			<a href="<?php echo site_url('product/'.$product->id); ?>" class="btn ek-btn-marketplace w-100">Lihat Detail</a>
		</div>
	</div>
</div>
