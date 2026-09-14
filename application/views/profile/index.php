<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h5 class="mb-4">Profil Saya</h5>

<div class="row g-4">
	<div class="col-md-7">
		<div class="card border-0 shadow-sm">
			<div class="card-body">
				<h6 class="fw-semibold mb-3">Edit Profil</h6>
				<?php echo form_open_multipart('profile/update'); ?>
					<div class="mb-3">
						<label class="form-label">Avatar</label>
						<div class="d-flex align-items-center gap-3">
							<?php if ($user->avatar): ?>
								<img src="<?php echo (strpos($user->avatar, 'http') === 0) ? htmlspecialchars($user->avatar) : base_url('assets/uploads/avatars/'.$user->avatar); ?>" alt="avatar" height="56" width="56" class="rounded-circle border">
						<?php else: ?>
							<img src="<?php echo base_url('assets/uploads/avatars/avatar1.png'); ?>" alt="avatar" height="56" width="56" class="rounded-circle border">
						<?php endif; ?>
							<input type="file" class="form-control" name="avatar" accept="image/*">
						</div>
					</div>
					<div class="mb-3">
						<label for="name" class="form-label">Nama</label>
						<input type="text" class="form-control" id="name" name="name" value="<?php echo set_value('name', $user->name); ?>" required>
					</div>
					<div class="mb-3">
						<label for="email" class="form-label">Email</label>
						<input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user->email); ?>" disabled>
						<div class="form-text">Email tidak dapat diubah.</div>
					</div>
					<div class="mb-3">
						<label for="wa_number" class="form-label">Nomor WhatsApp</label>
						<input type="text" class="form-control" id="wa_number" name="wa_number" value="<?php echo set_value('wa_number', $user->wa_number); ?>" placeholder="mis. 628123456789">
						<div class="form-text">Format internasional tanpa tanda +. Digunakan untuk kirim promo & rekomendasi.</div>
					</div>
					<button type="submit" class="btn ek-btn-marketplace">Simpan Profil</button>
				<?php echo form_close(); ?>
			</div>
		</div>

		<div class="card border-0 shadow-sm mt-4">
			<div class="card-body">
				<h6 class="fw-semibold mb-3"><?php echo $user->password ? 'Ganti Password' : 'Buat Password'; ?></h6>
				<?php echo form_open('profile/change_password'); ?>
					<?php if ($user->password): ?>
					<div class="mb-3">
						<label for="current_password" class="form-label">Password Lama</label>
						<input type="password" class="form-control" id="current_password" name="current_password" required>
					</div>
					<?php else: ?>
					<p class="text-muted small">Akun Anda terdaftar melalui Google. Buat password baru agar bisa login secara manual.</p>
					<?php endif; ?>
					<div class="mb-3">
						<label for="new_password" class="form-label">Password Baru</label>
						<input type="password" class="form-control" id="new_password" name="new_password" required>
					</div>
					<div class="mb-3">
						<label for="new_password_confirm" class="form-label">Konfirmasi Password Baru</label>
						<input type="password" class="form-control" id="new_password_confirm" name="new_password_confirm" required>
					</div>
					<button type="submit" class="btn btn-outline-danger"><?php echo $user->password ? 'Ganti Password' : 'Buat Password'; ?></button>
				<?php echo form_close(); ?>
			</div>
		</div>
	</div>

	<div class="col-md-5">
		<div class="card border-0 shadow-sm">
			<div class="card-body">
				<h6 class="fw-semibold mb-3">Ringkasan Akun</h6>
				<ul class="list-unstyled small mb-0">
					<li class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Role</span><span><?php echo ucfirst($user->role); ?></span></li>
					<li class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Email</span><span><?php echo htmlspecialchars($user->email); ?></span></li>
					<li class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">WhatsApp</span><span><?php echo $user->wa_number ? htmlspecialchars($user->wa_number) : '-'; ?></span></li>
					<li class="d-flex justify-content-between py-1"><span class="text-muted">Bergabung</span><span><?php echo date('d M Y', strtotime($user->created_at)); ?></span></li>
				</ul>
			</div>
		</div>

		<?php if ($company): ?>
			<div class="card border-0 shadow-sm mt-4">
				<div class="card-body">
					<h6 class="fw-semibold mb-3">Perusahaan Saya</h6>
					<div class="d-flex align-items-center gap-3">
						<?php if ($company->logo): ?>
							<img src="<?php echo base_url('assets/uploads/companies/'.$company->logo); ?>" alt="logo" height="48" class="border rounded">
						<?php endif; ?>
						<div>
							<div class="fw-semibold"><?php echo htmlspecialchars($company->name); ?></div>
							<div class="small text-muted"><?php echo htmlspecialchars($company->city); ?></div>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
