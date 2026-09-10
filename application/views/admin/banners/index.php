<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="card border-0 shadow-sm">
	<div class="card-header bg-white d-flex justify-content-between align-items-center">
		<span>Daftar Banner</span>
		<a href="<?php echo site_url('admin/banners/create'); ?>" class="btn btn-primary btn-sm">+ Tambah</a>
	</div>
	<div class="card-body p-0">
		<table class="table table-hover align-middle mb-0">
			<thead>
				<tr>
					<th style="width: 60px;">ID</th>
					<th style="width: 140px;">Gambar</th>
					<th>Judul</th>
					<th>Posisi</th>
					<th class="text-center">Urutan</th>
					<th class="text-center">Status</th>
					<th class="text-end" style="width: 160px;">Aksi</th>
				</tr>
			</thead>
			<tbody>
				<?php if (empty($items)): ?>
					<tr><td colspan="7" class="text-center text-muted py-4">Belum ada banner.</td></tr>
				<?php else: ?>
					<?php foreach ($items as $item): ?>
					<tr>
						<td><?php echo $item->id; ?></td>
						<td><img src="<?php echo base_url('assets/uploads/banners/'.$item->image); ?>" alt="" height="40"></td>
						<td><?php echo htmlspecialchars($item->title); ?></td>
						<td><span class="badge text-bg-light"><?php echo $item->position; ?></span></td>
						<td class="text-center"><?php echo $item->sort_order; ?></td>
						<td class="text-center"><?php echo $item->is_active ? '<span class="badge text-bg-success">Aktif</span>' : '<span class="badge text-bg-secondary">Nonaktif</span>'; ?></td>
						<td class="text-end">
							<a href="<?php echo site_url('admin/banners/edit/'.$item->id); ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
							<a href="<?php echo site_url('admin/banners/delete/'.$item->id); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus banner ini?');">Hapus</a>
						</td>
					</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
