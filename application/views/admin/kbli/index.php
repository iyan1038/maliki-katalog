<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="card border-0 shadow-sm">
	<div class="card-header bg-white d-flex justify-content-between align-items-center">
		<span>Daftar KBLI</span>
		<a href="<?php echo site_url('admin/kbli/create'); ?>" class="btn btn-primary btn-sm">+ Tambah</a>
	</div>
	<div class="card-body p-0">
		<table class="table table-hover align-middle mb-0">
			<thead>
				<tr>
					<th style="width: 60px;">ID</th>
					<th style="width: 140px;">Kode</th>
					<th>Nama</th>
					<th>Deskripsi</th>
					<th class="text-end" style="width: 160px;">Aksi</th>
				</tr>
			</thead>
			<tbody>
				<?php if (empty($items)): ?>
					<tr><td colspan="5" class="text-center text-muted py-4">Belum ada data KBLI.</td></tr>
				<?php else: ?>
					<?php foreach ($items as $item): ?>
					<tr>
						<td><?php echo $item->id; ?></td>
						<td><span class="badge text-bg-info"><?php echo htmlspecialchars($item->code); ?></span></td>
						<td><?php echo htmlspecialchars($item->name); ?></td>
						<td class="text-truncate" style="max-width: 300px;"><?php echo htmlspecialchars($item->description ?: '-'); ?></td>
						<td class="text-end">
							<a href="<?php echo site_url('admin/kbli/edit/'.$item->id); ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
							<a href="<?php echo site_url('admin/kbli/delete/'.$item->id); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus KBLI ini?');">Hapus</a>
						</td>
					</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
