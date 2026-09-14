<?php defined('BASEPATH') OR exit('No direct script access allowed');
$ek_dark = (bool) $this->session->userdata('dark_mode');
$ek_logged = $this->session->userdata('logged_in');
$ek_role = $this->session->userdata('role');
$ek_route = $this->router->class.'/'.$this->router->method;
?>
<!DOCTYPE html>
<html lang="id" <?php echo $ek_dark ? 'data-bs-theme="dark"' : ''; ?>>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo isset($title) ? $title . ' - ' : ''; ?>E-Katalog</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
	<link rel="stylesheet" href="<?php echo base_url('assets/css/style.css?v='.@filemtime(FCPATH.'assets/css/style.css')); ?>">
	<link rel="stylesheet" href="<?php echo base_url('assets/css/dark.css?v='.@filemtime(FCPATH.'assets/css/dark.css')); ?>">
</head>
<body class="ek-bg<?php echo $ek_dark ? ' ek-dark' : ''; ?>"<?php echo $ek_logged ? ' data-track-url="'.site_url('behavior/track').'"' : ''; ?>>
	<header class="ek-topbar<?php echo empty($hide_search) ? '' : ' ek-header-auth'; ?>">
		<div class="container py-3">
			<div class="d-flex align-items-center gap-2 flex-nowrap">
				<div class="flex-shrink-0">
					<a class="ek-logo" href="<?php echo site_url('catalog'); ?>">
						<img src="<?php echo base_url('assets/images/logo.png'); ?>" alt="Maliki" class="ek-logo-img">
					</a>
				</div>
				<?php if (empty($hide_search)): ?>
				<div class="ek-header-search">
					<form action="<?php echo site_url('catalog'); ?>" method="get" class="d-flex">
						<div class="ek-search flex-grow-1">
							<input type="text" name="q" class="form-control" placeholder="Cari produk, perusahaan..." value="<?php echo isset($search) ? htmlspecialchars($search) : ''; ?>">
							<?php if ( ! empty($platform_id)): ?>
								<input type="hidden" name="platform" value="<?php echo (int) $platform_id; ?>">
							<?php endif; ?>
							<?php if ( ! empty($sort) && $sort !== 'recommended'): ?>
								<input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
							<?php endif; ?>
						</div>
						<button type="submit" class="btn ek-btn-search ms-2"><span aria-hidden="true">&#128269;</span> <span class="ek-search-txt">Cari</span></button>
					</form>
				</div>
				<?php endif; ?>
				<div class="flex-shrink-0">
					<?php if ($ek_logged): ?>
						<div class="d-flex align-items-center gap-2 flex-wrap">
							<?php
								$this->load->model('User_m');
								$ek_user   = get_instance()->User_m->get_by_id($this->session->userdata('user_id'));

								$ek_avatar = $ek_user ? $ek_user->avatar : $this->session->userdata('avatar');
								$ek_name   = $ek_user ? $ek_user->name   : $this->session->userdata('name');
								$ek_email  = $ek_user ? $ek_user->email  : $this->session->userdata('email');

								if ($ek_user && (
									$ek_user->avatar !== $this->session->userdata('avatar') ||
									$ek_user->name   !== $this->session->userdata('name')
								))
								{
									$this->session->set_userdata(array(
										'avatar' => $ek_user->avatar,
										'name'   => $ek_user->name
									));
								}
							?>
							<div class="ek-nav-user dropdown">
								<button type="button" class="ek-nav-avatar-btn dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
								<?php if ($ek_avatar): ?>
									<img src="<?php echo (strpos($ek_avatar, 'http') === 0) ? htmlspecialchars($ek_avatar) : base_url('assets/uploads/avatars/'.$ek_avatar); ?>" alt="profil" class="ek-nav-avatar">
							<?php else: ?>
								<img src="<?php echo base_url('assets/uploads/avatars/avatar1.png'); ?>" alt="profil" class="ek-nav-avatar">
							<?php endif; ?>
								</button>
								<ul class="dropdown-menu dropdown-menu-end shadow">
									<li><span class="dropdown-item-text fw-semibold text-truncate"><?php echo htmlspecialchars($ek_name); ?></span></li>
									<li><span class="dropdown-item-text small text-muted text-truncate"><?php echo htmlspecialchars($ek_email); ?></span></li>
									<li><hr class="dropdown-divider"></li>
									<li><a class="dropdown-item" href="<?php echo site_url('profile'); ?>">Profil Saya</a></li>
									<?php if ($ek_role === 'member'): ?>
										<li><a class="dropdown-item" href="<?php echo site_url('favorite'); ?>">Wishlist</a></li>
									<?php endif; ?>
									<li><a class="dropdown-item" href="<?php echo site_url('setting'); ?>">Pengaturan</a></li>
									<?php if ($ek_role === 'admin'): ?>
										<li><hr class="dropdown-divider"></li>
										<li><a class="dropdown-item" href="<?php echo site_url('admin/dashboard'); ?>">Dashboard Admin</a></li>
									<?php endif; ?>
									<li><hr class="dropdown-divider"></li>
									<li><a class="dropdown-item text-danger" href="<?php echo site_url('auth/logout'); ?>">Keluar</a></li>
								</ul>
							</div>
						</div>
					<?php else: ?>
						<div class="d-flex align-items-center gap-2">
							<a href="<?php echo site_url('auth/login'); ?>" class="ek-navlink<?php echo $ek_route === 'auth/login' ? ' ek-navlink-active' : ''; ?>">Masuk</a>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</header>

	<main class="container py-4">
		<?php $flash_success = $this->session->flashdata('success'); ?>
		<?php $flash_error   = $this->session->flashdata('error'); ?>
		<?php $flash_reset   = $this->session->flashdata('reset_link'); ?>

		<?php $this->load->view('templates/toast', array(
			'form_errors'       => isset($form_errors) ? $form_errors : NULL,
			'toast_flash_error' => $flash_error
		)); ?>

		<?php if ($flash_success): ?>
			<div class="alert alert-success alert-dismissible fade show">
				<?php echo $flash_success; ?>
				<?php if ($flash_reset): ?>
					<div class="alert alert-info mt-2 mb-0">
						<strong><?php echo $flash_reset; ?></strong>
					</div>
				<?php endif; ?>
				<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
			</div>
		<?php endif; ?>
