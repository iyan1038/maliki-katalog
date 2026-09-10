<?php defined('BASEPATH') OR exit('No direct script access allowed');
$logo_url = $company->logo ? base_url('assets/uploads/companies/'.$company->logo) : '';
?>
<nav aria-label="breadcrumb" class="mb-3">
	<ol class="breadcrumb small mb-0">
		<li class="breadcrumb-item"><a href="<?php echo site_url('catalog'); ?>">Katalog</a></li>
		<li class="breadcrumb-item"><a href="<?php echo site_url('company/'.$company->id); ?>"><?php echo htmlspecialchars($company->name); ?></a></li>
		<li class="breadcrumb-item active" aria-current="page">Etalase</li>
	</ol>
</nav>

<!-- Profil perusahaan -->
<div class="ek-cv-profile card border-0 shadow-sm mb-4">
	<div class="card-body p-3 p-md-4">
		<div class="ek-cv-profile-head">
			<div class="d-flex align-items-start gap-3 flex-grow-1 min-w-0">
				<?php if ($logo_url): ?>
					<img src="<?php echo $logo_url; ?>" alt="logo" class="ek-cv-logo border rounded flex-shrink-0">
				<?php endif; ?>
				<div class="flex-grow-1 min-w-0 ek-cv-info">
					<h4 class="mb-1"><?php echo htmlspecialchars($company->name); ?></h4>
					<div class="small text-muted mb-2">
						<?php if ($company->city): ?>
							<span>&#128205; <?php echo htmlspecialchars($company->city); ?></span>
						<?php endif; ?>
						<?php if ($company->npwp): ?>
							<span class="ms-2">NPWP: <?php echo htmlspecialchars($company->npwp); ?></span>
						<?php endif; ?>
					</div>
					<?php if ($company->description): ?>
						<p class="mb-2"><?php echo nl2br(htmlspecialchars($company->description)); ?></p>
					<?php endif; ?>
					<?php if ($company->address): ?>
						<div class="small text-muted mb-2"><?php echo htmlspecialchars($company->address); ?></div>
					<?php endif; ?>
				</div>
			</div>
			<div class="flex-shrink-0">
				<button type="button" class="btn ek-btn-marketplace ek-btn-contact" data-bs-toggle="modal" data-bs-target="#ekContactModal">Hubungi</button>
			</div>
		</div>
	</div>
</div>

<div class="ek-cv-etalase">

	<?php
	$ek_url = function ($params) use ($company) {
		$qs = '';
		foreach ($params as $k => $v)
		{
			if ($v === '' || $v === NULL)
			{
				continue;
			}
			$qs .= '&'.$k.'='.urlencode($v);
		}
		return site_url('company/cv/'.$company->id).($qs === '' ? '' : '?'.ltrim($qs, '&'));
	};
	$ek_sort_val = ($sort !== 'recommended') ? $sort : '';
	?>

	<!-- b. Baris rekomendasi (kategori produk yang dikunjungi) -->
	<?php if ( ! empty($row_products)): ?>
		<div class="ek-cv-row mb-4">
			<h6 class="mb-2">Rekomendasi</h6>
			<div class="ek-scroll-row">
				<?php foreach ($row_products as $rp): ?>
					<?php $this->load->view('templates/product_card', array('product' => $rp)); ?>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<!-- a. Filter kategori & urutan -->
	<div class="ek-filter-bar mb-3">
		<div class="d-flex flex-wrap align-items-center gap-2">
			<a href="<?php echo $ek_url(array('sort' => $ek_sort_val)); ?>" class="btn <?php echo ! $category_id ? 'btn-primary' : 'btn-outline-primary'; ?> btn-sm">Semua</a>
			<?php foreach ($categories as $cat): ?>
				<a href="<?php echo $ek_url(array('cat' => $cat->id, 'sort' => $ek_sort_val)); ?>" class="btn <?php echo $category_id === (int) $cat->id ? 'btn-primary' : 'btn-outline-primary'; ?> btn-sm">
					<?php echo htmlspecialchars($cat->name); ?>
				</a>
			<?php endforeach; ?>

			<div class="ms-auto">
				<select class="form-select form-select-sm ek-sort" onchange="if(this.value){window.location='<?php echo site_url('company/cv/'.$company->id).'?'.(($category_id) ? 'cat='.$category_id.'&' : ''); ?>sort='+this.value;}">
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

	<!-- c. Daftar produk (menyesuaikan filter & urutan) -->
	<h6 class="mt-2 mb-3">Semua Produk</h6>
	<?php if (empty($products)): ?>
		<div class="text-center py-5">
			<p class="text-muted">Belum ada produk yang cocok.</p>
		</div>
	<?php else: ?>
		<div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-3">
			<?php foreach ($products as $product): ?>
				<?php $this->load->view('templates/product_card', array('product' => $product)); ?>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>

<!-- Modal Hubungi -->
<?php if ( ! empty($owner_wa)): ?>
<div class="modal fade" id="ekContactModal" tabindex="-1" aria-labelledby="ekContactModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content ek-card">
			<div class="modal-header">
				<h5 class="modal-title" id="ekContactModalLabel">Hubungi <?php echo htmlspecialchars($company->name); ?></h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
			</div>
			<div class="modal-body">
				<?php if ($wa_configured): ?>
					<form id="ekContactForm">
						<div class="mb-2">
							<label class="form-label small fw-semibold mb-1">Nama Anda</label>
							<input type="text" name="name" class="form-control" maxlength="100">
						</div>
						<div class="mb-2">
							<label class="form-label small fw-semibold mb-1">Nomor WhatsApp</label>
							<input type="text" name="phone" class="form-control" maxlength="20" placeholder="Contoh: 628123456789">
						</div>
						<div class="mb-2">
							<label class="form-label small fw-semibold mb-1">Pesan</label>
							<textarea name="message" class="form-control" rows="3" maxlength="1000"></textarea>
						</div>
						<button type="submit" class="btn ek-btn-marketplace w-100" id="ekContactSubmit">Kirim Pesan</button>
					</form>
				<?php else: ?>
					<div class="alert alert-warning mb-0">
						Fitur WhatsApp belum dapat digunakan saat ini. Mohon coba lagi nanti.
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<?php if ($wa_configured): ?>
<script>
	(function () {
		var form = document.getElementById('ekContactForm');
		var btn = document.getElementById('ekContactSubmit');
		if (!form) return;

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			btn.disabled = true;
			btn.textContent = 'Mengirim...';

			var fd = new FormData(form);
			fd.append('company_id', <?php echo (int) $company->id; ?>);

			fetch('<?php echo site_url('company/contact'); ?>', {
				method: 'POST',
				body: fd,
				headers: { 'X-Requested-With': 'XMLHttpRequest' }
			})
			.then(function (res) { return res.json(); })
			.then(function (data) {
				var y = document.createElement('div');
				y.style.color = '#fff';
				y.style.background = data.ok ? 'var(--ek-green)' : 'var(--ek-red)';
				y.style.padding = '.6rem .9rem';
				y.style.borderRadius = '6px';
				y.style.marginTop = '.75rem';
				y.textContent = data.message;

				var old = document.querySelector('.ek-contact-result');
				if (old) old.remove();
				y.className = 'ek-contact-result';
				form.appendChild(y);

				if (data.ok) {
					setTimeout(function () {
						bootstrap.Modal.getInstance(document.getElementById('ekContactModal')).hide();
						form.reset();
					}, 1600);
				}
			})
			.catch(function () {
				var y = document.createElement('div');
				y.style.color = '#fff';
				y.style.background = 'var(--ek-red)';
				y.style.padding = '.6rem .9rem';
				y.style.borderRadius = '6px';
				y.style.marginTop = '.75rem';
				y.textContent = 'Terjadi kesalahan. Coba lagi.';
				var old = document.querySelector('.ek-contact-result');
				if (old) old.remove();
				y.className = 'ek-contact-result';
				form.appendChild(y);
			})
			.finally(function () {
				btn.disabled = false;
				btn.textContent = 'Kirim Pesan';
			});
		});
	})();
</script>
<?php endif; ?>
<?php endif; ?>
