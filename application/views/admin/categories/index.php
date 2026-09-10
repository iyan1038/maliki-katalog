<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="card border-0 shadow-sm">
	<div class="card-header bg-white d-flex justify-content-between align-items-center">
		<span>Daftar Kategori Produk</span>
		<a href="<?php echo site_url('admin/categories/create'); ?>" class="btn btn-primary btn-sm">+ Tambah</a>
	</div>
	<div class="card-body p-0">
		<table class="table table-hover align-middle mb-0">
			<thead>
				<tr>
					<th style="width: 60px;">ID</th>
					<th>Nama</th>
					<th>Slug</th>
					<th>KBLI Terhubung</th>
					<th style="width: 90px;">Urutan</th>
					<th style="width: 80px;">Aktif</th>
					<th class="text-end" style="width: 160px;">Aksi</th>
				</tr>
			</thead>
			<tbody>
				<?php if (empty($items)): ?>
					<tr><td colspan="7" class="text-center text-muted py-4">Belum ada data kategori.</td></tr>
				<?php else: ?>
					<?php foreach ($items as $item): ?>
					<tr>
						<td><?php echo $item->id; ?></td>
						<td><?php echo htmlspecialchars($item->name); ?></td>
						<td><span class="badge text-bg-light"><?php echo htmlspecialchars($item->slug); ?></span></td>
						<td>
							<span class="badge text-bg-info"><?php echo (int) $item->kbli_count; ?> KBLI</span>
						</td>
						<td><?php echo (int) $item->sort_order; ?></td>
						<td>
							<?php if ($item->is_active): ?>
								<span class="badge text-bg-success">Aktif</span>
							<?php else: ?>
								<span class="badge text-bg-secondary">Nonaktif</span>
							<?php endif; ?>
						</td>
						<td class="text-end">
							<a href="<?php echo site_url('admin/categories/edit/'.$item->id); ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
							<a href="<?php echo site_url('admin/categories/delete/'.$item->id); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus kategori ini? Produk tidak ikut terhapus.');">Hapus</a>
						</td>
					</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>