<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h5 class="mb-3">Produk Favorit Saya</h5>

<?php if (empty($products)): ?>
	<div class="text-center py-5">
		<p class="text-muted">Belum ada produk favorit.</p>
		<a href="<?php echo site_url('catalog'); ?>" class="btn ek-btn-marketplace">Jelajahi Katalog</a>
	</div>
<?php else: ?>
	<div class="row row-cols-2 row-cols-md-4 row-cols-lg-5 g-3">
		<?php foreach ($products as $p): ?>
			<?php $this->load->view('templates/product_card', array('product' => $p)); ?>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
