<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row justify-content-center">
	<div class="col-md-6 col-lg-5">
		<div class="card shadow-sm">
			<div class="card-body p-4">
				<h4 class="card-title text-center mb-4">Reset Password</h4>

				<?php echo form_open('auth/reset_password/'.$token); ?>
					<div class="mb-3">
						<label for="password" class="form-label">Password Baru</label>
						<input type="password" class="form-control" id="password" name="password" required>
						<?php echo form_error('password', '<small class="text-danger d-block">', '</small>'); ?>
					</div>
					<div class="mb-3">
						<label for="password_confirm" class="form-label">Konfirmasi Password</label>
						<input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
						<?php echo form_error('password_confirm', '<small class="text-danger d-block">', '</small>'); ?>
					</div>
					<button type="submit" class="btn btn-primary w-100">Simpan Password Baru</button>
				<?php echo form_close(); ?>
			</div>
		</div>
	</div>
</div>
