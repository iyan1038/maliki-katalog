<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="text-center py-5">
	<h1 class="display-6 fw-bold mb-2">Selamat datang, <?php echo htmlspecialchars($user['name']); ?>!</h1>
	<p class="text-muted"><?php echo htmlspecialchars($user['email']); ?> &mdash; <?php echo ucfirst($user['role']); ?></p>

	<div class="mt-4">
		<div class="alert alert-info d-inline-block">
			Halaman beranda penuh (katalog produk, filter, dan fitur member) akan tersedia pada fase pengembangan berikutnya.
		</div>
	</div>

	<div class="mt-3">
		<a href="<?php echo site_url('auth/logout'); ?>" class="btn btn-outline-secondary">Keluar</a>
	</div>
</div>
