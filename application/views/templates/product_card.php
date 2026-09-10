<?php defined('BASEPATH') OR exit('No direct script access allowed');
$card_price    = ($product->is_promo && $product->promo_price !== NULL) ? $product->promo_price : $product->price;
$card_old_price = ($product->is_promo && $product->promo_price !== NULL) ? $product->price : NULL;
$card_img = $product->image ? base_url('assets/uploads/products/'.$product->image) : base_url('assets/images/no-image.png');
?>
<div class="col">
	<div class="card ek-product">
		<a href="<?php echo site_url('product/'.$product->id); ?>" class="text-decoration-none">
			<div class="ek-product-img">
				<img src="<?php echo $card_img; ?>" alt="<?php echo htmlspecialchars($product->name); ?>" loading="lazy">
				<?php if ($product->is_promo): ?>
					<span class="ek-badge-promo">PROMO</span>
				<?php endif; ?>
				<?php if ($this->session->userdata('logged_in') && $this->session->userdata('role') === 'member'): ?>
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
				<div class="ek-product-rating">
					<span class="ek-star">&#9733;</span>
					<?php echo number_format((float) $product->avg_rating, 1); ?>
					<span class="text-muted">(<?php echo (int) $product->rating_count; ?>)</span>
				</div>
				<div class="ek-product-company"><?php echo htmlspecialchars($product->company_name); ?></div>
			</div>
		</a>
		<div class="card-body pt-0">
			<a href="<?php echo site_url('product/'.$product->id); ?>" class="btn ek-btn-marketplace w-100">Lihat Detail</a>
		</div>
	</div>
</div>
