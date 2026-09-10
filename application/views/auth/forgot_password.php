<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row justify-content-center">
	<div class="col-md-6 col-lg-5">
		<div class="card shadow-sm">
			<div class="card-body p-4">
				<h4 class="card-title text-center mb-1">Lupa Password</h4>
				<p class="text-muted text-center small mb-4">Masukkan email terdaftar untuk mendapatkan link reset password.</p>

				<?php echo form_open('auth/forgot_password'); ?>
					<div class="mb-3">
						<label for="email" class="form-label">Email</label>
						<input type="email" class="form-control" id="email" name="email" value="<?php echo set_value('email'); ?>" required>
						<?php echo form_error('email', '<small class="text-danger d-block">', '</small>'); ?>
					</div>
					<button type="submit" class="btn btn-primary w-100">Kirim Link Reset</button>
				<?php echo form_close(); ?>

				<hr>
				<div class="text-center small">
					<a href="<?php echo site_url('auth/login'); ?>">Kembali ke login</a>
				</div>
			</div>
		</div>
	</div>
</div>
