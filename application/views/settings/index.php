<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h5 class="mb-4">Pengaturan</h5>

<div class="row g-4">
	<div class="col-md-7">
		<div class="card border-0 shadow-sm">
			<div class="card-body">
				<h6 class="fw-semibold mb-1">Tampilan</h6>
				<p class="text-muted small">Mode gelap (dark mode) untuk kenyamanan di lingkungan redup.</p>

				<?php echo form_open('setting/toggle_dark_mode', array('id' => 'darkFormSet')); ?>
					<input type="hidden" name="dark_mode" id="darkValueSet" value="<?php echo $dark_mode ? 0 : 1; ?>">
					<div class="form-check form-switch">
						<input class="form-check-input" type="checkbox" role="switch" id="darkSwitch" <?php echo $dark_mode ? 'checked' : ''; ?>>
						<label class="form-check-label" for="darkSwitch">Aktifkan mode gelap</label>
					</div>
				<?php echo form_close(); ?>
			</div>
		</div>

		<div class="card border-0 shadow-sm mt-4">
			<div class="card-body">
				<h6 class="fw-semibold mb-1">Akun &amp; Profil</h6>
				<p class="text-muted small">Kelola profil pribadi, nomor WhatsApp, dan password.</p>
				<a href="<?php echo site_url('profile'); ?>" class="btn btn-outline-secondary btn-sm">Buka Profil</a>
			</div>
		</div>

		<?php if ($company): ?>
			<div class="card border-0 shadow-sm mt-4">
				<div class="card-body">
					<h6 class="fw-semibold mb-1">Profil Perusahaan</h6>
					<div class="d-flex align-items-center gap-3 mt-2">
						<?php if ($company->logo): ?>
							<img src="<?php echo base_url('assets/uploads/companies/'.$company->logo); ?>" alt="logo" height="48" class="border rounded">
						<?php endif; ?>
						<div>
							<div class="fw-semibold"><?php echo htmlspecialchars($company->name); ?></div>
							<div class="small text-muted">
								<?php echo htmlspecialchars($company->city); ?>
								<?php if ($company->npwp): ?> &middot; NPWP <?php echo htmlspecialchars($company->npwp); ?><?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>

	<div class="col-md-5">
		<div class="card border-0 shadow-sm">
			<div class="card-body">
				<h6 class="fw-semibold mb-3">Akun</h6>
				<ul class="list-unstyled small mb-0">
					<li class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Nama</span><span><?php echo htmlspecialchars($user->name); ?></span></li>
					<li class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Email</span><span><?php echo htmlspecialchars($user->email); ?></span></li>
					<li class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Role</span><span><?php echo ucfirst($user->role); ?></span></li>
					<li class="d-flex justify-content-between py-1"><span class="text-muted">WhatsApp</span><span><?php echo $user->wa_number ? htmlspecialchars($user->wa_number) : '-'; ?></span></li>
				</ul>
			</div>
		</div>
	</div>
</div>

<script>
	(function () {
		var sw = document.getElementById('darkSwitch');
		if (sw) {
			sw.addEventListener('change', function () {
				document.getElementById('darkValueSet').value = sw.checked ? 1 : 0;
				document.getElementById('darkFormSet').submit();
			});
		}
	})();
</script>
