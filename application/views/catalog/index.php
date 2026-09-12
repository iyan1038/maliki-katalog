<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php if ( ! empty($banners_top)): ?>
	<div class="mb-4">
		<?php if (count($banners_top) > 1): ?>
			<div id="ekTopCarousel" class="carousel slide" data-bs-ride="carousel">
				<div class="carousel-indicators">
					<?php foreach ($banners_top as $k => $b): ?>
						<button type="button" data-bs-target="#ekTopCarousel" data-bs-slide-to="<?php echo $k; ?>" class="<?php echo $k === 0 ? 'active' : ''; ?>" <?php echo $k === 0 ? 'aria-current="true"' : ''; ?> aria-label="Slide <?php echo $k + 1; ?>"></button>
					<?php endforeach; ?>
				</div>
				<div class="carousel-inner">
					<?php foreach ($banners_top as $k => $b): ?>
						<div class="carousel-item <?php echo $k === 0 ? 'active' : ''; ?>">
							<a href="<?php echo $b->url ? htmlspecialchars($b->url) : site_url('catalog'); ?>" class="d-block">
								<img src="<?php echo base_url('assets/uploads/banners/'.$b->image); ?>" class="ek-banner d-block w-100" alt="<?php echo htmlspecialchars($b->title); ?>">
							</a>
						</div>
					<?php endforeach; ?>
				</div>
				<button class="carousel-control-prev" type="button" data-bs-target="#ekTopCarousel" data-bs-slide="prev">
					<span class="carousel-control-prev-icon" aria-hidden="true"></span>
					<span class="visually-hidden">Sebelumnya</span>
				</button>
				<button class="carousel-control-next" type="button" data-bs-target="#ekTopCarousel" data-bs-slide="next">
					<span class="carousel-control-next-icon" aria-hidden="true"></span>
					<span class="visually-hidden">Berikutnya</span>
				</button>
			</div>
		<?php else: ?>
			<?php foreach ($banners_top as $b): ?>
				<a href="<?php echo $b->url ? htmlspecialchars($b->url) : site_url('catalog'); ?>" class="d-block mb-2">
					<img src="<?php echo base_url('assets/uploads/banners/'.$b->image); ?>" class="ek-banner" alt="<?php echo htmlspecialchars($b->title); ?>">
				</a>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
<?php endif; ?>

<?php if ($search !== ''): ?>
	<h5 class="mb-3">Hasil pencarian "<em><?php echo htmlspecialchars($search); ?></em>" — <?php echo $total; ?> produk</h5>
<?php else: ?>
	<h5 class="mb-3">Katalog Produk — <?php echo $total; ?> produk</h5>
<?php endif; ?>

<?php
$ek_url = function ($params) {
	$qs = '';
	foreach ($params as $k => $v)
	{
		if ($v === '' || $v === NULL)
		{
			continue;
		}
		$qs .= '&'.$k.'='.urlencode($v);
	}
	return site_url('catalog').($qs === '' ? '' : '?'.ltrim($qs, '&'));
};
$ek_sort_val = ($sort !== 'recommended') ? $sort : '';
?>

<div class="ek-filter-bar mb-3">
	<div class="d-flex flex-wrap align-items-center gap-2">
		<a href="<?php echo $ek_url(array('q' => $search, 'sort' => $ek_sort_val, 'cat' => $category_id)); ?>" class="btn <?php echo ! $platform_id ? 'btn-primary' : 'btn-outline-primary'; ?> btn-sm">Semua</a>
		<?php foreach ($platforms as $pf): ?>
			<a href="<?php echo $ek_url(array('q' => $search, 'sort' => $ek_sort_val, 'cat' => $category_id, 'platform' => $pf->id)); ?>" class="btn <?php echo $platform_id === (int) $pf->id ? 'btn-primary' : 'btn-outline-primary'; ?> btn-sm">
				<?php echo htmlspecialchars($pf->name); ?>
			</a>
		<?php endforeach; ?>

		<div class="ms-auto">
			<select class="form-select form-select-sm ek-sort" onchange="if(this.value){window.location='<?php echo site_url('catalog').'?'.(($search !== '') ? 'q='.urlencode($search).'&' : '').(($platform_id) ? 'platform='.$platform_id.'&' : '').(($category_id) ? 'cat='.$category_id.'&' : ''); ?>sort='+this.value;}">
				<option value="">Urutkan</option>
				<option value="recommended" <?php echo $sort === 'recommended' ? 'selected' : ''; ?>>Rekomendasi</option>
				<option value="promo" <?php echo $sort === 'promo' ? 'selected' : ''; ?>>Promo Terbaik</option>
				<option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Rating Tertinggi</option>
				<option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Terbaru</option>
				<option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Harga Terendah</option>
				<option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Harga Tertinggi</option>
			</select>
		</div>
	</div>
</div>

<?php if ( ! empty($categories)): ?>
<div class="ek-category-strip mb-3">
	<div class="ek-category-wrap">
		<button type="button" class="ek-cat-arrow ek-cat-arrow-prev" aria-label="Kategori sebelumnya">&#10094;</button>
		<button type="button" class="ek-cat-arrow ek-cat-arrow-next" aria-label="Kategori berikutnya">&#10095;</button>
		<div class="ek-cat-nav" id="ekCatNav">
			<a href="<?php echo $ek_url(array('q' => $search, 'platform' => $platform_id, 'sort' => $ek_sort_val)); ?>" class="ek-cat-item <?php echo ! $category_id ? 'active' : ''; ?>">Semua</a>
			<?php foreach ($categories as $cat): ?>
				<a href="<?php echo $ek_url(array('q' => $search, 'platform' => $platform_id, 'sort' => $ek_sort_val, 'cat' => $cat->id)); ?>" class="ek-cat-item <?php echo $category_id === (int) $cat->id ? 'active' : ''; ?>">
					<?php echo htmlspecialchars($cat->name); ?>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</div>
<?php endif; ?>

<?php if (empty($products)): ?>
	<div class="text-center py-5">
		<p class="text-muted">Belum ada produk yang cocok.</p>
	</div>
<?php else: ?>
	<?php
	$mid_slots  = isset($banner_mid_count) ? (int) $banner_mid_count : 0;
	$mid_offset = isset($banner_mid_offset) ? (int) $banner_mid_offset : 0;
	?>
	<div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-3">
		<?php $i = 0; $slot = 0; foreach ($products as $p): $i++; ?>
			<?php $this->load->view('templates/product_card', array('product' => $p)); ?>

			<?php if ($i >= 10 && $slot < $mid_slots && count($banners_middle) > 0): ?>
				</div>
				<div class="my-3">
					<?php $banner = $banners_middle[($mid_offset + $slot) % count($banners_middle)]; ?>
					<a href="<?php echo $banner->url ? htmlspecialchars($banner->url) : site_url('catalog'); ?>" class="d-block">
						<img src="<?php echo base_url('assets/uploads/banners/'.$banner->image); ?>" class="ek-banner" alt="<?php echo htmlspecialchars($banner->title); ?>">
					</a>
				</div>
				<div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-3">
				<?php $slot++; $i = 0; ?>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>

	<?php if ($total_pages > 1): ?>
		<nav class="mt-4">
			<ul class="pagination justify-content-center">
				<?php
				$qs = '';
				if ($search !== '') { $qs .= '&q='.urlencode($search); }
				if ($platform_id) { $qs .= '&platform='.$platform_id; }
				if ($category_id) { $qs .= '&cat='.$category_id; }
				if ($sort !== 'recommended') { $qs .= '&sort='.$sort; }
				$url = site_url('catalog');
				for ($pg = 1; $pg <= $total_pages; $pg++):
				?>
					<li class="page-item <?php echo $pg === $page ? 'active' : ''; ?>">
						<a class="page-link" href="<?php echo $url.'?page='.$pg.$qs; ?>"><?php echo $pg; ?></a>
					</li>
				<?php endfor; ?>
			</ul>
		</nav>
	<?php endif; ?>
<?php endif; ?>

<?php if ( ! empty($banners_bottom)): ?>
	<div class="mt-4">
		<?php foreach ($banners_bottom as $b): ?>
			<a href="<?php echo $b->url ? htmlspecialchars($b->url) : site_url('catalog'); ?>" class="d-block mb-2">
				<img src="<?php echo base_url('assets/uploads/banners/'.$b->image); ?>" class="ek-banner" alt="<?php echo htmlspecialchars($b->title); ?>">
			</a>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
