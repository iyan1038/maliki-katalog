<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row justify-content-center">
	<div class="col-md-5 col-lg-4">
		<div class="card shadow-sm">
			<div class="card-body p-4">
				<h4 class="card-title text-center mb-4">Login</h4>

				<?php echo form_open('auth/login'); ?>
					<div class="mb-3">
						<label for="email" class="form-label">Email</label>
						<input type="email" class="form-control" id="email" name="email" value="<?php echo set_value('email'); ?>" required>
						<?php echo form_error('email', '<small class="text-danger d-block">', '</small>'); ?>
					</div>
					<div class="mb-3">
						<label for="password" class="form-label">Password</label>
						<input type="password" class="form-control" id="password" name="password" required>
						<?php echo form_error('password', '<small class="text-danger d-block">', '</small>'); ?>
					</div>
					<button type="submit" class="btn btn-primary w-100">Masuk</button>
				<?php echo form_close(); ?>

				<?php if ($google_available): ?>
					<hr>
					<a href="<?php echo $google_url; ?>" class="btn btn-outline-danger w-100">
						Daftar / Login dengan Google
					</a>
				<?php else: ?>
					<hr>
					<div class="alert alert-warning py-2 small mb-0">
						Fitur login Google belum dikonfigurasi.
					</div>
				<?php endif; ?>

			<hr>
			<div class="text-center small">
				<a href="<?php echo site_url('auth/forgot_password'); ?>">Lupa password?</a>
			</div>
			</div>
		</div>
	</div>
</div>
