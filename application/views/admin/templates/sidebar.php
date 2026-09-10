<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<nav class="admin-sidebar px-3 py-3 flex-shrink-0" style="width: 240px;">
	<a class="sidebar-brand d-block mb-3" href="<?php echo site_url('admin'); ?>">Dashboard Admin</a>
	<ul class="nav flex-column gap-1">
		<li class="nav-item">
			<a class="nav-link <?php echo $active_menu === 'dashboard' ? 'active' : ''; ?>" href="<?php echo site_url('admin'); ?>">Dashboard</a>
		</li>
		<li class="nav-item">
			<a class="nav-link <?php echo $active_menu === 'products' ? 'active' : ''; ?>" href="<?php echo site_url('admin/products'); ?>">Produk</a>
		</li>
		<li class="nav-item">
			<a class="nav-link <?php echo $active_menu === 'companies' ? 'active' : ''; ?>" href="<?php echo site_url('admin/companies'); ?>">Perusahaan</a>
		</li>
		<li class="nav-item">
			<a class="nav-link <?php echo $active_menu === 'kbli' ? 'active' : ''; ?>" href="<?php echo site_url('admin/kbli'); ?>">KBLI</a>
		</li>
		<li class="nav-item">
			<a class="nav-link <?php echo $active_menu === 'categories' ? 'active' : ''; ?>" href="<?php echo site_url('admin/categories'); ?>">Kategori</a>
		</li>
		<li class="nav-item">
			<a class="nav-link <?php echo $active_menu === 'platforms' ? 'active' : ''; ?>" href="<?php echo site_url('admin/platforms'); ?>">Platform</a>
		</li>
		<li class="nav-item">
			<a class="nav-link <?php echo $active_menu === 'banners' ? 'active' : ''; ?>" href="<?php echo site_url('admin/banners'); ?>">Banner</a>
		</li>
		<li class="nav-item">
			<a class="nav-link <?php echo $active_menu === 'wa' ? 'active' : ''; ?>" href="<?php echo site_url('admin/wa'); ?>">Kirim WhatsApp</a>
		</li>
		<li class="nav-item">
			<a class="nav-link <?php echo $active_menu === 'users' ? 'active' : ''; ?>" href="<?php echo site_url('admin/users'); ?>">Users</a>
		</li>
		<li class="nav-item">
			<a class="nav-link <?php echo $active_menu === 'settings' ? 'active' : ''; ?>" href="<?php echo site_url('admin/settings'); ?>">Pengaturan</a>
		</li>
		<li class="nav-item sidebar-divider"><hr class="border-secondary my-2"></li>
		<li class="nav-item"><a class="nav-link small" href="<?php echo site_url('home'); ?>">Lihat Situs</a></li>
		<li class="nav-item"><a class="nav-link small" href="<?php echo site_url('auth/logout'); ?>">Keluar</a></li>
	</ul>
</nav>

<div class="flex-grow-1">
	<nav class="navbar navbar-light bg-white border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2 py-2 px-3 px-md-4">
		<span class="fw-semibold"><?php echo isset($title) ? $title : ''; ?></span>
		<span class="small text-muted text-truncate ms-auto">
			<?php echo isset($admin_name) ? htmlspecialchars($admin_name) : ''; ?> (Admin)
		</span>
	</nav>
	<main class="admin-main p-3 p-md-4">
		<?php $flash_success = $this->session->flashdata('success'); ?>
		<?php $flash_error   = $this->session->flashdata('error'); ?>
		<?php $this->load->view('templates/toast', array(
			'form_errors'       => isset($form_errors) ? $form_errors : NULL,
			'toast_flash_error' => $flash_error
		)); ?>
		<?php if ($flash_success): ?>
			<div class="alert alert-success alert-dismissible fade show">
				<?php echo $flash_success; ?>
				<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
			</div>
		<?php endif; ?>
