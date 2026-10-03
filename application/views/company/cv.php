<?php defined('BASEPATH') OR exit('No direct script access allowed');
$logo_url = $company->logo ? base_url('assets/uploads/companies/'.$company->logo) : '';

$wa_link_number = preg_replace('/[^0-9]/', '', (string) $target_wa);
if ($wa_link_number !== '' && substr($wa_link_number, 0, 2) === '08')
{
	$wa_link_number = '62'.substr($wa_link_number, 1);
}
$wa_link = ($wa_link_number !== '')
	? 'https://wa.me/'.$wa_link_number.'?text='.rawurlencode('Halo '.$company->name.', saya tertarik dengan produk Anda di E-Katalog.')
	: '';
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
			<div class="flex-shrink-0 d-flex flex-wrap gap-2">
				<?php if ($wa_link !== ''): ?>
					<a href="<?php echo $wa_link; ?>" target="_blank" rel="noopener" class="btn ek-btn-marketplace ek-btn-contact"><i class="bi bi-whatsapp me-1"></i>WhatsApp</a>
				<?php endif; ?>
				<button type="button" class="btn ek-btn-marketplace ek-btn-contact" data-bs-toggle="modal" data-bs-target="#ekContactModal">Hubungi</button>
			</div>
		</div>
	</div>
</div>

<div class="ek-cv-etalase">

	<?php
	$ek_row_cat = ($row_cat > 0) ? (int) $row_cat : '';
	$ek_url = function ($params) use ($company, $ek_row_cat) {
		$params = array_merge(array('row_cat' => $ek_row_cat), $params);
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
				<select class="form-select form-select-sm ek-sort" onchange="if(this.value){window.location='<?php echo site_url('company/cv/'.$company->id).'?'.(($row_cat > 0) ? 'row_cat='.(int) $row_cat.'&' : '').(($category_id) ? 'cat='.$category_id.'&' : ''); ?>sort='+this.value;}">
					<option value="recommended" <?php echo $sort === 'recommended' ? 'selected' : ''; ?>>Rekomendasi</option>
					<option value="promo" <?php echo $sort === 'promo' ? 'selected' : ''; ?>>Promo Terbaik</option>
					<option value="visit" <?php echo $sort === 'visit' ? 'selected' : ''; ?>>Kunjungan Terbanyak</option>
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
<div class="modal fade" id="ekContactModal" tabindex="-1" aria-labelledby="ekContactModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content ek-card">
			<div class="modal-header">
				<h5 class="modal-title" id="ekContactModalLabel">Hubungi <?php echo htmlspecialchars($company->name); ?></h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
			</div>
			<div class="modal-body">
				<?php if (empty($owner_email)): ?>
					<div class="alert alert-warning mb-0">
						Pemilik belum memiliki email untuk dihubungi.
					</div>
				<?php else: ?>
					<form id="ekContactForm">
						<div class="mb-2">
							<label class="form-label small fw-semibold mb-1">Nama Anda</label>
							<input type="text" name="name" class="form-control" maxlength="100">
						</div>
						<div class="mb-2">
							<label class="form-label small fw-semibold mb-1">Nomor WhatsApp <span class="text-muted fw-normal">(opsional)</span></label>
							<input type="text" name="phone" class="form-control" maxlength="20" placeholder="Contoh: 628123456789">
						</div>
						<div class="mb-2">
							<label class="form-label small fw-semibold mb-1">Pesan</label>
							<textarea name="message" class="form-control" rows="3" maxlength="1000"></textarea>
						</div>
						<a href="#" target="_blank" rel="noopener" class="btn ek-btn-marketplace w-100" id="ekContactSubmit" role="button">Kirim via Email</a>
					</form>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<script>
	(function () {
		var form = document.getElementById('ekContactForm');
		var btn = document.getElementById('ekContactSubmit');
		if (!form || !btn) return;

		var email = <?php echo json_encode((string) $owner_email); ?>;

		var subject = <?php echo json_encode($company->name.' - Permintaan dari E-Katalog'); ?>;

		function buildBody() {
			var name    = form.elements.item('name').value.trim();
			var phone   = form.elements.item('phone').value.trim();
			var message = form.elements.item('message').value.trim();

			return 'Nama: ' + name + '\n'
				+ (phone ? 'WhatsApp: ' + phone + '\n' : '')
				+ 'Pesan: ' + message + '\n\n'
				+ '- Dikirim dari <?php echo base_url(); ?>';
		}

		function buildGmailUrl() {
			var name    = form.elements.item('name').value.trim();
			var message = form.elements.item('message').value.trim();
			if (!name || !message) return '';

			return 'https://mail.google.com/mail/?view=cm&fs=1'
				+ '&to=' + encodeURIComponent(email)
				+ '&su=' + encodeURIComponent(subject)
				+ '&body=' + encodeURIComponent(buildBody());
		}

		function syncHref() {
			btn.href = buildGmailUrl() || '#';
		}

		function clearResult() {
			var old = document.querySelector('.ek-contact-result');
			if (old) old.remove();
		}

		function showWarning(text) {
			clearResult();
			var w = document.createElement('div');
			w.className = 'alert alert-warning ek-contact-result';
			w.style.marginTop = '.75rem';
			w.textContent = text;
			form.appendChild(w);
		}

		['name', 'phone', 'message'].forEach(function (name) {
			form.elements.item(name).addEventListener('input', syncHref);
		});
		syncHref();

		btn.addEventListener('click', function (e) {
			if (buildGmailUrl() === '') {
				e.preventDefault();
				showWarning('Mohon lengkapi nama dan pesan.');
			}
		});

		btn.addEventListener('keydown', function (e) {
			if (e.key === ' ' || e.key === 'Spacebar') {
				e.preventDefault();
				btn.click();
			}
		});

		form.addEventListener('keydown', function (e) {
			if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
				e.preventDefault();
				btn.click();
			}
		});

		form.addEventListener('submit', function (e) {
			e.preventDefault();
		});
	})();
</script>
