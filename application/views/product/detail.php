<?php defined('BASEPATH') OR exit('No direct script access allowed');
$main_image = ! empty($images) ? base_url('assets/uploads/products/'.$images[0]->filename) : base_url('assets/images/no-image.png');
$display_price = ($product->is_promo && $product->promo_price !== NULL) ? $product->promo_price : $product->price;
?>
<nav aria-label="breadcrumb" class="mb-3">
	<ol class="breadcrumb small mb-0">
		<li class="breadcrumb-item"><a href="<?php echo site_url('catalog'); ?>">Katalog</a></li>
		<li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product->name); ?></li>
	</ol>
</nav>

<div class="card border-0 shadow-sm">
	<div class="card-body p-3 p-md-4">
		<div class="row g-4">
			<!-- Galeri -->
			<div class="col-12 col-md-5 col-lg-4">
				<div class="ek-gallery">
					<div class="ek-gallery-main">
						<img id="ekMainImage" src="<?php echo $main_image; ?>" alt="<?php echo htmlspecialchars($product->name); ?>">
						<?php if ($product->is_promo): ?>
							<span class="ek-badge-promo">PROMO</span>
						<?php endif; ?>
					</div>
					<?php if (count($images) > 1): ?>
						<div class="d-flex gap-2 mt-2 flex-wrap">
							<?php foreach ($images as $img): ?>
								<a href="#" class="ek-thumb" data-src="<?php echo base_url('assets/uploads/products/'.$img->filename); ?>">
									<img src="<?php echo base_url('assets/uploads/products/'.$img->filename); ?>" alt="gambar <?php echo $img->position; ?>">
								</a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<!-- Info -->
			<div class="col-12 col-md-7 col-lg-8">
				<h4 class="mb-1"><?php echo htmlspecialchars($product->name); ?></h4>
				<div class="ek-product-rating mb-2">
					<span class="ek-star">&#9733;</span>
					<?php echo number_format((float) $product->avg_rating, 1); ?>
					<span class="text-muted">(<?php echo (int) $product->rating_count; ?> ulasan)</span>
					<span class="text-muted ms-2"><?php echo (int) $product->total_views; ?> dilihat</span>
				</div>

				<div class="ek-detail-price mb-3">
					<?php if ($product->is_promo && $product->promo_price !== NULL): ?>
						<span class="ek-price-promo">Rp <?php echo number_format($product->promo_price, 0, ',', '.'); ?></span>
						<del class="ms-2 text-muted">Rp <?php echo number_format($product->price, 0, ',', '.'); ?></del>
						<span class="badge text-bg-danger ms-2">Promo</span>
					<?php else: ?>
						<span class="ek-price">Rp <?php echo number_format($product->price, 0, ',', '.'); ?></span>
					<?php endif; ?>
					<?php if ($product->unit): ?>
						<span class="text-muted">/ <?php echo htmlspecialchars($product->unit); ?></span>
					<?php endif; ?>
				</div>

				<?php if ($product->description): ?>
					<p class="text-muted"><?php echo nl2br(htmlspecialchars($product->description)); ?></p>
				<?php endif; ?>

				<?php if ( ! empty($kbli)): ?>
					<div class="mb-3">
						<?php foreach ($kbli as $k): ?>
							<span class="badge text-bg-light border me-1" title="<?php echo htmlspecialchars($k->description); ?>"><?php echo htmlspecialchars($k->code.' - '.$k->name); ?></span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php
				$visible_platforms = array();
				foreach ($platforms as $pp)
				{
					if ( ! empty($pp->is_visible))
					{
						$visible_platforms[] = $pp;
					}
				}
				?>
				<?php if ( ! empty($visible_platforms)): ?>
				<div class="border-top pt-3">
					<div class="small text-muted mb-2">Beli di marketplace:</div>
						<div class="d-flex flex-wrap gap-2">
							<?php foreach ($visible_platforms as $pp): ?>
						<?php $pp_target = trim($pp->product_url) !== '' ? $pp->product_url : $pp->url; ?>
						<?php if ( ! empty($pp->logo)): ?>
							<a href="<?php echo htmlspecialchars($pp_target); ?>" target="_blank" rel="noopener" class="btn ek-market-logo ek-market-link" data-product-id="<?php echo $product->id; ?>" data-platform-id="<?php echo $pp->platform_id; ?>" title="<?php echo htmlspecialchars($pp->name); ?>">
								<img src="<?php echo base_url('assets/uploads/platforms/'.$pp->logo); ?>" alt="<?php echo htmlspecialchars($pp->name); ?>" height="26">
							</a>
						<?php else: ?>
						<a href="<?php echo htmlspecialchars($pp_target); ?>" target="_blank" rel="noopener" class="btn ek-btn-marketplace ek-market-link" data-product-id="<?php echo $product->id; ?>" data-platform-id="<?php echo $pp->platform_id; ?>">
							<?php echo htmlspecialchars($pp->name); ?>
						</a>
						<?php endif; ?>
							<?php endforeach; ?>
						</div>
				</div>
				<?php endif; ?>

				<?php if ($is_member): ?>
						<div class="mt-3">
							<?php echo form_open('favorite/toggle', array('class' => 'd-inline')); ?>
								<input type="hidden" name="product_id" value="<?php echo $product->id; ?>">
								<input type="hidden" name="redirect" value="<?php echo site_url('product/'.$product->id); ?>">
								<button type="submit" class="btn <?php echo $is_favorited ? 'btn-danger' : 'btn-outline-danger'; ?> btn-sm">
									&#9829; <?php echo $is_favorited ? 'Sudah Difavoritkan' : 'Simpan ke Favorit'; ?>
								</button>
							<?php echo form_close(); ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Info perusahaan -->
<?php if ($company): ?>
<div class="card border-0 shadow-sm mt-4 ek-company-card" style="position: relative;">
	<a href="<?php echo site_url('company/cv/'.$company->id).( ! empty($cv_category_id) ? '?row_cat='.$cv_category_id : ''); ?>" class="btn ek-btn-marketplace ek-cv-btn">Lihat Etalase</a>
	<div class="card-body d-flex align-items-center gap-3">
		<?php if ($company->logo): ?>
			<img src="<?php echo base_url('assets/uploads/companies/'.$company->logo); ?>" alt="logo" class="border rounded flex-shrink-0 ek-company-logo">
		<?php endif; ?>
		<div class="ek-company-info flex-grow-1 min-w-0">
			<div class="fw-semibold"><?php echo htmlspecialchars($company->name); ?></div>
			<div class="small text-muted">
				<?php echo htmlspecialchars($company->city); ?>
				<?php if ($company->description): ?> &middot; <?php echo htmlspecialchars($company->description); ?><?php endif; ?>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>

<!-- Deskripsi produk panjang -->
<?php if ($product->long_description): ?>
<div class="card border-0 shadow-sm mt-4">
	<div class="card-body">
		<h5 class="mb-3">Deskripsi Produk</h5>
		<p class="mb-0"><?php echo nl2br(htmlspecialchars($product->long_description)); ?></p>
	</div>
</div>
<?php endif; ?>

<!-- Rating & ulasan -->
<div class="card border-0 shadow-sm mt-4">
	<div class="card-body">
		<h5 class="mb-3">Rating &amp; Ulasan</h5>

		<?php if ($is_member): ?>
			<div class="border rounded p-3 mb-4">
				<h6 class="mb-2"><?php echo $my_rating ? 'Ubah rating Anda' : 'Beri rating Anda'; ?></h6>
				<?php echo form_open('rating/submit'); ?>
					<input type="hidden" name="product_id" value="<?php echo $product->id; ?>">
					<div class="mb-2">
						<div class="ek-stars">
							<?php for ($s = 5; $s >= 1; $s--): ?>
								<input type="radio" name="rating" value="<?php echo $s; ?>" id="star<?php echo $s; ?>" <?php echo $my_rating && (int) $my_rating->rating === $s ? 'checked' : ''; ?>>
								<label for="star<?php echo $s; ?>" class="ek-star-label">&#9733;</label>
							<?php endfor; ?>
						</div>
					</div>
					<div class="mb-2">
						<textarea class="form-control" name="comment" rows="2" placeholder="Tulis komentar (opsional)..."><?php echo $my_rating ? htmlspecialchars($my_rating->comment) : ''; ?></textarea>
					</div>
					<button type="submit" class="btn ek-btn-marketplace">Kirim Rating</button>
				<?php echo form_close(); ?>
			</div>
		<?php else: ?>
			<div class="alert alert-info py-2 small">
				<a href="<?php echo site_url('auth/login'); ?>">Login</a> sebagai member untuk memberi rating.
			</div>
		<?php endif; ?>

		<?php if (empty($ratings)): ?>
			<p class="text-muted mb-0">Belum ada ulasan.</p>
		<?php else: ?>
			<div class="list-group">
				<?php foreach ($ratings as $rt): ?>
					<div class="list-group-item list-group-item-action">
						<div class="d-flex align-items-center gap-2">
							<?php if ($rt->user_avatar): ?>
								<img src="<?php echo htmlspecialchars($rt->user_avatar); ?>" alt="" height="28" width="28" class="rounded-circle">
							<?php else: ?>
								<img src="<?php echo base_url('assets/uploads/avatars/avatar1.png'); ?>" alt="" height="28" width="28" class="rounded-circle">
							<?php endif; ?>
							<div>
								<div class="fw-semibold small"><?php echo htmlspecialchars($rt->user_name); ?></div>
								<div class="ek-stars ek-stars-readonly">
									<?php for ($s = 1; $s <= 5; $s++): ?>
										<span class="<?php echo $s <= (int) $rt->rating ? 'ek-star-on' : 'ek-star-off'; ?>">&#9733;</span>
									<?php endfor; ?>
									<small class="text-muted ms-1"><?php echo date('d M Y', strtotime($rt->created_at)); ?></small>
								</div>
							</div>
						</div>
						<?php if ($rt->comment): ?>
							<p class="mb-0 mt-2 small"><?php echo htmlspecialchars($rt->comment); ?></p>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</div>

<!-- Produk terkait -->
<?php if ( ! empty($related)): ?>
	<h5 class="mt-4 mb-3">Produk Terkait</h5>
	<div class="row row-cols-2 row-cols-md-4 g-3">
		<?php foreach ($related as $rp): ?>
			<?php $this->load->view('templates/product_card', array('product' => $rp)); ?>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<script>
	(function () {
		var main = document.getElementById('ekMainImage');
		var thumbs = document.querySelectorAll('.ek-thumb');
		thumbs.forEach(function (t) {
			t.addEventListener('click', function (e) {
				e.preventDefault();
				main.src = t.getAttribute('data-src');
			});
		});
	})();
</script>
