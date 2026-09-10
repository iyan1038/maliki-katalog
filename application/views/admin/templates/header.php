<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Admin - <?php echo isset($title) ? $title : 'Dashboard'; ?> | E-Katalog</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo base_url('assets/css/style.css?v='.@filemtime(FCPATH.'assets/css/style.css')); ?>">
	<style>
		body { background: #f4f6f9; }
		.admin-sidebar {
			background: #1e293b;
			min-height: 100vh;
		}
		.admin-sidebar .nav-link {
			color: #cbd5e1;
			border-radius: .375rem;
			display: inline-flex;
			align-items: center;
			padding: .5rem .75rem;
		}
		.admin-sidebar .nav-link:hover { background: #334155; color: #fff; }
		.admin-sidebar .nav-link.active { background: #0ea5e9; color: #fff; }
		.admin-sidebar .sidebar-brand {
			color: #fff;
			font-weight: 700;
			text-decoration: none;
		}
		.admin-main .card-body.p-0 { overflow-x: auto; }
		.admin-main .card-header { flex-wrap: wrap; gap: .5rem; }
		@media (max-width: 767.98px) {
			.admin-sidebar { min-height: auto; width: 100% !important; padding-bottom: .5rem !important; }
			.admin-sidebar ul { flex-direction: row !important; flex-wrap: nowrap; overflow-x: auto; padding-bottom: .25rem; align-items: center; }
			.admin-sidebar .nav-link { white-space: nowrap; }
			.admin-sidebar hr, .admin-sidebar .sidebar-divider { display: none; }
			.admin-sidebar .sidebar-brand { margin-bottom: .5rem !important; }
		}
	</style>
</head>
<body>
<div class="d-flex flex-column flex-md-row">
