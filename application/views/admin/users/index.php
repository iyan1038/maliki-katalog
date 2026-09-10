<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="card border-0 shadow-sm">
	<div class="card-header bg-white d-flex justify-content-between align-items-center">
		<span>Daftar Users</span>
		<a href="<?php echo site_url('admin/users/create'); ?>" class="btn btn-primary btn-sm">+ Tambah</a>
	</div>
	<div class="card-body p-0">
		<table class="table table-hover align-middle mb-0">
			<thead>
				<tr>
					<th style="width: 60px;">ID</th>
					<th style="width: 60px;">Avatar</th>
					<th>Nama</th>
					<th>Email</th>
					<th class="text-center">Role</th>
					<th class="text-center">Status</th>
					<th class="text-end" style="width: 160px;">Aksi</th>
				</tr>
			</thead>
			<tbody>
				<?php if (empty($items)): ?>
					<tr><td colspan="7" class="text-center text-muted py-4">Belum ada user.</td></tr>
				<?php else: ?>
					<?php foreach ($items as $item): ?>
					<tr>
						<td><?php echo $item->id; ?></td>
						<td>
							<?php if ($item->avatar): ?>
								<?php if (strpos($item->avatar, 'http') === 0): ?>
									<img src="<?php echo htmlspecialchars($item->avatar); ?>" alt="" height="30" width="30" class="rounded-circle">
								<?php else: ?>
									<img src="<?php echo base_url('assets/uploads/avatars/'.$item->avatar); ?>" alt="" height="30" width="30" class="rounded-circle">
								<?php endif; ?>
							<?php else: ?>
								<img src="<?php echo base_url('assets/uploads/avatars/avatar1.png'); ?>" alt="" height="30" width="30" class="rounded-circle">
							<?php endif; ?>
						</td>
						<td><?php echo htmlspecialchars($item->name); ?></td>
						<td><?php echo htmlspecialchars($item->email); ?></td>
						<td class="text-center">
							<?php if ($item->role === 'admin'): ?>
								<span class="badge text-bg-primary">Admin</span>
							<?php else: ?>
								<span class="badge text-bg-info">Member</span>
							<?php endif; ?>
						</td>
						<td class="text-center"><?php echo $item->is_active ? '<span class="badge text-bg-success">Aktif</span>' : '<span class="badge text-bg-secondary">Nonaktif</span>'; ?></td>
						<td class="text-end">
							<a href="<?php echo site_url('admin/users/edit/'.$item->id); ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
							<a href="<?php echo site_url('admin/users/delete/'.$item->id); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus user ini?');">Hapus</a>
						</td>
					</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
