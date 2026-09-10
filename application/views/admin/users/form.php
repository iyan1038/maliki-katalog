<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="card border-0 shadow-sm" style="max-width: 640px;">
	<div class="card-body">
		<?php echo form_open_multipart($item_id ? 'admin/users/edit/'.$item_id : 'admin/users/create'); ?>
			<div class="row">
				<div class="col-md-6">
					<div class="mb-3">
						<label for="name" class="form-label">Nama</label>
						<input type="text" class="form-control" id="name" name="name" value="<?php echo set_value('name', $item ? $item->name : ''); ?>" required>
						<?php echo form_error('name', '<small class="text-danger d-block">', '</small>'); ?>
					</div>
				</div>
				<div class="col-md-6">
					<div class="mb-3">
						<label for="email" class="form-label">Email</label>
						<input type="email" class="form-control" id="email" name="email" value="<?php echo set_value('email', $item ? $item->email : ''); ?>" required>
						<?php echo form_error('email', '<small class="text-danger d-block">', '</small>'); ?>
					</div>
				</div>
				<div class="col-md-6">
					<div class="mb-3">
						<label for="password" class="form-label">Password</label>
						<input type="password" class="form-control" id="password" name="password" <?php echo $item ? '' : 'required'; ?>>
						<?php if ($item): ?>
							<div class="form-text">Kosongkan jika tidak diubah.</div>
						<?php endif; ?>
						<?php echo form_error('password', '<small class="text-danger d-block">', '</small>'); ?>
					</div>
				</div>
				<div class="col-md-6">
					<div class="mb-3">
						<label for="role" class="form-label">Role</label>
						<select class="form-select" id="role" name="role" required>
							<option value="member" <?php echo set_select('role', 'member', $item && $item->role === 'member'); ?>>Member</option>
							<option value="admin" <?php echo set_select('role', 'admin', $item && $item->role === 'admin'); ?>>Admin</option>
						</select>
					</div>
				</div>
				<div class="col-md-6">
					<div class="mb-3">
						<label for="wa_number" class="form-label">Nomor WhatsApp</label>
						<input type="text" class="form-control" id="wa_number" name="wa_number" value="<?php echo set_value('wa_number', $item ? $item->wa_number : ''); ?>" placeholder="62xxxx">
					</div>
				</div>
				<div class="col-md-6">
					<div class="mb-3">
						<label for="avatar" class="form-label">Avatar</label>
						<input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
						<?php if ($item && $item->avatar): ?>
							<div class="mt-2">
								<?php if (strpos($item->avatar, 'http') === 0): ?>
									<img src="<?php echo htmlspecialchars($item->avatar); ?>" alt="avatar" height="50" class="rounded-circle">
								<?php else: ?>
									<img src="<?php echo base_url('assets/uploads/avatars/'.$item->avatar); ?>" alt="avatar" height="50" class="rounded-circle">
								<?php endif; ?>
							</div>
						<?php else: ?>
							<img src="<?php echo base_url('assets/uploads/avatars/avatar1.png'); ?>" alt="avatar" height="50" class="rounded-circle mt-2">
						<?php endif; ?>
					</div>
				</div>
				<div class="col-12">
					<div class="form-check mb-3">
						<input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" <?php echo ! $item || $item->is_active ? 'checked' : ''; ?>>
						<label class="form-check-label" for="is_active">Aktif</label>
					</div>
				</div>
			</div>
			<button type="submit" class="btn btn-primary">Simpan</button>
			<a href="<?php echo site_url('admin/users'); ?>" class="btn btn-outline-secondary">Batal</a>
		<?php echo form_close(); ?>
	</div>
</div>
