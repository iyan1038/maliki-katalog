<?php defined('BASEPATH') OR exit('No direct script access allowed');
$logo_url = $company->logo ? base_url('assets/uploads/companies/'.$company->logo) : '';
?>
<nav aria-label="breadcrumb" class="mb-3">
	<ol class="breadcrumb small mb-0">
		<li class="breadcrumb-item"><a href="<?php echo site_url('catalog'); ?>">Katalog</a></li>
		<li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($company->name); ?></li>
	</ol>
</nav>

<div class="card border-0 shadow-sm mb-4">
	<div class="card-body p-3 p-md-4">
		<div class="d-flex align-items-start gap-3">
			<?php if ($logo_url): ?>
				<img src="<?php echo $logo_url; ?>" alt="logo" height="64" class="border rounded flex-shrink-0">
			<?php endif; ?>
			<div class="flex-grow-1">
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
				<?php if ( ! empty($company->kbli)): ?>
					<div class="mb-1">
						<span class="small text-muted">Kode KBLI:</span>
						<?php foreach ($company->kbli as $k): ?>
							<span class="badge text-bg-light border me-1"><?php echo htmlspecialchars($k->code.' - '.$k->name); ?></span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<h5 class="mb-3">Produk dari <?php echo htmlspecialchars($company->name); ?></h5>

<?php if (empty($products)): ?>
	<div class="alert alert-info">Perusahaan ini belum memiliki produk.</div>
<?php else: ?>
	<div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-3">
		<?php foreach ($products as $product): ?>
			<?php $this->load->view('templates/product_card', array('product' => $product)); ?>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
