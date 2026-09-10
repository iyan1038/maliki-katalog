<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="card border-0 shadow-sm">
	<div class="card-header bg-white d-flex justify-content-between align-items-center">
		<span>Daftar Platform Marketplace</span>
		<a href="<?php echo site_url('admin/platforms/create'); ?>" class="btn btn-primary btn-sm">+ Tambah</a>
	</div>
	<div class="card-body p-0">
		<table class="table table-hover align-middle mb-0">
			<thead>
				<tr>
					<th style="width: 60px;">ID</th>
					<th style="width: 80px;">Logo</th>
					<th>Nama</th>
					<th>Slug</th>
					<th>URL</th>
					<th class="text-end" style="width: 160px;">Aksi</th>
				</tr>
			</thead>
			<tbody>
				<?php if (empty($items)): ?>
					<tr><td colspan="6" class="text-center text-muted py-4">Belum ada platform.</td></tr>
				<?php else: ?>
					<?php foreach ($items as $item): ?>
					<tr>
						<td><?php echo $item->id; ?></td>
						<td>
							<?php if ($item->logo): ?>
								<img src="<?php echo base_url('assets/uploads/platforms/'.$item->logo); ?>" alt="" height="30">
							<?php else: ?>
								<span class="text-muted">-</span>
							<?php endif; ?>
						</td>
						<td><?php echo htmlspecialchars($item->name); ?></td>
						<td><span class="badge text-bg-light"><?php echo htmlspecialchars($item->slug); ?></span></td>
						<td class="text-truncate" style="max-width: 220px;"><?php echo htmlspecialchars($item->url); ?></td>
						<td class="text-end">
							<a href="<?php echo site_url('admin/platforms/edit/'.$item->id); ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
							<a href="<?php echo site_url('admin/platforms/delete/'.$item->id); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus platform ini?');">Hapus</a>
						</td>
					</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
