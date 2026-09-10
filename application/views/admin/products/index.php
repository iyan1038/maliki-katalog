<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="card border-0 shadow-sm">
	<div class="card-header bg-white d-flex justify-content-between align-items-center">
		<span>Daftar Produk</span>
		<a href="<?php echo site_url('admin/products/create'); ?>" class="btn btn-primary btn-sm">+ Tambah</a>
	</div>
	<div class="card-body p-0">
		<table class="table table-hover align-middle mb-0">
			<thead>
				<tr>
					<th style="width: 60px;">ID</th>
					<th>Nama Produk</th>
					<th>Perusahaan</th>
					<th class="text-end">Harga</th>
					<th class="text-center">Promo</th>
					<th class="text-center">Rating</th>
					<th class="text-center">Status</th>
					<th class="text-end" style="width: 160px;">Aksi</th>
				</tr>
			</thead>
			<tbody>
				<?php if (empty($items)): ?>
					<tr><td colspan="8" class="text-center text-muted py-4">Belum ada produk.</td></tr>
				<?php else: ?>
					<?php foreach ($items as $item): ?>
					<tr>
						<td><?php echo $item->id; ?></td>
						<td><?php echo htmlspecialchars($item->name); ?></td>
						<td><?php echo htmlspecialchars($item->company_name); ?></td>
						<td class="text-end">
							<?php if ($item->is_promo && $item->promo_price !== NULL): ?>
								<span class="text-danger">Rp <?php echo number_format($item->promo_price, 0, ',', '.'); ?></span>
								<del class="text-muted small">Rp <?php echo number_format($item->price, 0, ',', '.'); ?></del>
							<?php else: ?>
								Rp <?php echo number_format($item->price, 0, ',', '.'); ?>
							<?php endif; ?>
						</td>
						<td class="text-center"><?php echo $item->is_promo ? '<span class="badge text-bg-danger">Promo</span>' : '-'; ?></td>
						<td class="text-center"><?php echo number_format($item->avg_rating, 1); ?> (<?php echo $item->rating_count; ?>)</td>
						<td class="text-center"><?php echo $item->is_active ? '<span class="badge text-bg-success">Aktif</span>' : '<span class="badge text-bg-secondary">Nonaktif</span>'; ?></td>
						<td class="text-end">
							<a href="<?php echo site_url('admin/products/edit/'.$item->id); ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
							<a href="<?php echo site_url('admin/products/delete/'.$item->id); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus produk beserta gambarnya?');">Hapus</a>
						</td>
					</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
