<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row g-3">
	<div class="col-6 col-md-3">
		<div class="card border-0 shadow-sm">
			<div class="card-body">
				<h6 class="text-muted small">Users</h6>
				<h3><?php echo $counts['users']; ?></h3>
			</div>
		</div>
	</div>
	<div class="col-6 col-md-3">
		<div class="card border-0 shadow-sm">
			<div class="card-body">
				<h6 class="text-muted small">Perusahaan</h6>
				<h3><?php echo $counts['companies']; ?></h3>
			</div>
		</div>
	</div>
	<div class="col-6 col-md-3">
		<div class="card border-0 shadow-sm">
			<div class="card-body">
				<h6 class="text-muted small">Produk</h6>
				<h3><?php echo $counts['products']; ?></h3>
			</div>
		</div>
	</div>
	<div class="col-6 col-md-3">
		<div class="card border-0 shadow-sm">
			<div class="card-body">
				<h6 class="text-muted small">Banner</h6>
				<h3><?php echo $counts['banners']; ?></h3>
			</div>
		</div>
	</div>
</div>

<div class="card border-0 shadow-sm mt-4">
	<div class="card-header bg-white">Produk Terbaru</div>
	<div class="card-body p-0">
		<table class="table table-hover align-middle mb-0">
			<thead>
				<tr>
					<th>Nama Produk</th>
					<th>Perusahaan</th>
					<th class="text-end">Harga</th>
					<th class="text-center">Promo</th>
					<th class="text-center">Status</th>
				</tr>
			</thead>
			<tbody>
				<?php if (empty($latest_products)): ?>
					<tr><td colspan="5" class="text-center text-muted py-4">Belum ada produk.</td></tr>
				<?php else: ?>
					<?php foreach ($latest_products as $p): ?>
					<tr>
						<td><?php echo htmlspecialchars($p->name); ?></td>
						<td><?php echo htmlspecialchars($p->company_name); ?></td>
						<td class="text-end">Rp <?php echo number_format($p->price, 0, ',', '.'); ?></td>
						<td class="text-center"><?php echo $p->is_promo ? '<span class="badge text-bg-danger">Promo</span>' : '-'; ?></td>
						<td class="text-center"><?php echo $p->is_active ? '<span class="badge text-bg-success">Aktif</span>' : '<span class="badge text-bg-secondary">Nonaktif</span>'; ?></td>
					</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>

<div class="card border-0 shadow-sm mt-4">
	<div class="card-header bg-white">Daftar KBLI</div>
	<div class="card-body p-0">
		<table class="table table-hover align-middle mb-0">
			<thead>
				<tr>
					<th style="width: 140px;">Kode</th>
					<th style="width: 30%;">Nama</th>
					<th>Deskripsi</th>
				</tr>
			</thead>
			<tbody>
				<?php if (empty($kbli_list)): ?>
					<tr><td colspan="3" class="text-center text-muted py-4">Belum ada data KBLI.</td></tr>
				<?php else: ?>
					<?php foreach ($kbli_list as $k): ?>
					<tr>
						<td><span class="badge text-bg-info"><?php echo htmlspecialchars($k->code); ?></span></td>
						<td><?php echo htmlspecialchars($k->name); ?></td>
						<td><?php echo htmlspecialchars($k->description ?: '-'); ?></td>
					</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
