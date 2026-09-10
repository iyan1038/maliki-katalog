<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="card border-0 shadow-sm">
	<div class="card-header bg-white d-flex justify-content-between align-items-center">
		<span>Daftar Perusahaan</span>
		<a href="<?php echo site_url('admin/companies/create'); ?>" class="btn btn-primary btn-sm">+ Tambah</a>
	</div>
	<div class="card-body p-0">
		<table class="table table-hover align-middle mb-0">
			<thead>
				<tr>
					<th style="width: 60px;">ID</th>
					<th style="width: 60px;">Logo</th>
					<th>Nama</th>
					<th>Pemilik</th>
					<th>Kota</th>
					<th class="text-center">Status</th>
					<th class="text-end" style="width: 160px;">Aksi</th>
				</tr>
			</thead>
			<tbody>
				<?php if (empty($items)): ?>
					<tr><td colspan="7" class="text-center text-muted py-4">Belum ada perusahaan.</td></tr>
				<?php else: ?>
					<?php foreach ($items as $item): ?>
					<tr>
						<td><?php echo $item->id; ?></td>
						<td>
							<?php if ($item->logo): ?>
								<img src="<?php echo base_url('assets/uploads/companies/'.$item->logo); ?>" alt="" height="30">
							<?php else: ?>
								<span class="text-muted">-</span>
							<?php endif; ?>
						</td>
						<td><?php echo htmlspecialchars($item->name); ?></td>
						<td><?php echo htmlspecialchars($item->owner_name); ?></td>
						<td><?php echo htmlspecialchars($item->city); ?></td>
						<td class="text-center"><?php echo $item->is_active ? '<span class="badge text-bg-success">Aktif</span>' : '<span class="badge text-bg-secondary">Nonaktif</span>'; ?></td>
						<td class="text-end">
							<a href="<?php echo site_url('admin/companies/edit/'.$item->id); ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
							<a href="<?php echo site_url('admin/companies/delete/'.$item->id); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus perusahaan ini? Produk terkait juga akan terhapus.');">Hapus</a>
						</td>
					</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
