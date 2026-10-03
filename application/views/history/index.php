<?php defined('BASEPATH') OR exit('No direct script access allowed');

$ek_tab_url = function ($tab) {
	return site_url('history').'?tab='.urlencode($tab);
};

$ek_is_products = ($tab === 'produk');
$ek_scope       = $ek_is_products ? 'produk' : 'pencarian';

// Mode "pilih beberapa" dibaca dari query string, bukan dari JS, supaya
// checkbox tetap bisa dipakai walau app.js gagal dimuat.
$ek_select = ! empty($select_mode);
$ek_bulk   = 'ekHistoryBulk';

// Form hapus-terpilih diletakkan di toolbar, sedangkan checkbox-nya ada di
// dalam tiap kartu. Dua-duanya tidak boleh jadi form bersarang (setiap kartu
// punya form trash sendiri), jadi checkbox ditautkan lewat atribut form="".
$ek_count = $ek_is_products ? count($products) : count($keywords);
?>

<div class="ek-history">
	<div class="ek-history-head">
		<h5 class="mb-0">Riwayat Saya</h5>

		<?php if ( ! $is_empty): ?>
			<div class="ek-history-actions">
				<?php /* Slot 1: "Pilih Beberapa" dan "Batal" saling ganti seperti
					   trash vs checkbox di kartu produk — hanya salah satunya yang
					   dirender, di slot yang sama, jadi posisinya tidak bergeser. */ ?>
				<?php if ($ek_select): ?>
					<a href="<?php echo $ek_tab_url($tab); ?>" class="btn btn-sm btn-primary ek-btn-cancel">Batal</a>
				<?php else: ?>
					<a href="<?php echo $ek_tab_url($tab).'&select=1'; ?>" class="btn btn-sm btn-outline-primary ek-btn-select">Pilih Beberapa</a>
				<?php endif; ?>

				<?php /* Slot 2: kontrol hapus terpilih hanya ada di mode pilih, jadi
					   tidak ada tombol "Hapus Terpilih" yang mati di mode normal. */ ?>
				<?php if ($ek_select): ?>
					<?php echo form_open('history/delete_items', array('id' => $ek_bulk, 'class' => 'ek-history-form ek-history-bulk')); ?>
						<input type="hidden" name="scope" value="<?php echo $ek_scope; ?>">

						<span class="ek-history-checkall">
							<input type="checkbox" id="ekSelectAll" data-ek-select-all="<?php echo $ek_bulk; ?>">
							<label for="ekSelectAll">Pilih<span class="ek-checkall-txt"> semua</span><span class="ek-checkall-num"> (<?php echo (int) $ek_count; ?>)</span></label>
						</span>

						<button type="submit" class="btn btn-sm btn-danger" data-ek-bulk-submit>
							Hapus<span class="ek-bulk-count" data-ek-bulk-count> (0)</span>
						</button>
					<?php echo form_close(); ?>
				<?php endif; ?>

				<?php /* Slot 3: "Hapus Semua" selalu ada, tidak ikut berganti. */ ?>
				<?php echo form_open('history/clear', array('class' => 'ek-history-form', 'onsubmit' => 'return confirm(\'Hapus seluruh riwayat? Tindakan ini tidak bisa dibatalkan.\');')); ?>
					<button type="submit" class="btn btn-sm btn-outline-danger">Hapus Semua</button>
				<?php echo form_close(); ?>
			</div>
		<?php endif; ?>
	</div>

	<ul class="nav nav-tabs mb-4">
		<li class="nav-item">
			<a class="nav-link <?php echo $ek_is_products ? 'active' : ''; ?>"
				href="<?php echo $ek_tab_url('produk'); ?>"
				<?php echo $ek_is_products ? 'aria-current="page"' : ''; ?>>Produk</a>
		</li>
		<li class="nav-item">
			<a class="nav-link <?php echo ! $ek_is_products ? 'active' : ''; ?>"
				href="<?php echo $ek_tab_url('pencarian'); ?>"
				<?php echo ! $ek_is_products ? 'aria-current="page"' : ''; ?>>Pencarian</a>
		</li>
	</ul>

	<?php if ($ek_is_products): ?>

		<?php if ($is_empty): ?>

			<div class="text-center py-5">
				<p class="text-muted">Kamu belum membuka produk apa pun.</p>
				<p class="text-muted small mb-3">
					Riwayat produk terisi otomatis setiap kali kamu membuka detail
					produk atau klik tombol marketplace.
				</p>
				<a href="<?php echo site_url('catalog'); ?>" class="btn ek-btn-marketplace">Jelajahi Katalog</a>
			</div>

		<?php else: ?>

			<div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-3">
				<?php foreach ($products as $p): ?>
					<?php $this->load->view('templates/product_card', array(
						'product'        => $p,
						'card_variant'   => 'history',
						'card_select'    => $ek_select,
						'card_bulk_form' => $ek_bulk,
					)); ?>
				<?php endforeach; ?>
			</div>

		<?php endif; ?>

	<?php else: ?>

		<?php if ($is_empty): ?>

			<div class="text-center py-5">
				<p class="text-muted">Kamu belum melakukan pencarian apa pun.</p>
				<p class="text-muted small mb-3">
					Riwayat pencarian terisi otomatis setiap kali kamu memakai kolom
					pencarian di katalog.
				</p>
				<a href="<?php echo site_url('catalog'); ?>" class="btn ek-btn-marketplace">Jelajahi Katalog</a>
			</div>

		<?php else: ?>

			<p class="text-muted small mb-3">
				<?php if ($ek_select): ?>
					Centang kata kunci yang mau dihapus dari riwayat, lalu tekan
					<strong>Hapus Terpilih</strong>.
				<?php else: ?>
					Klik salah satu kata kunci untuk mencari ulang di katalog.
					Tekan ikon tong sampah di sebelahnya untuk menghapus kata kunci itu.
				<?php endif; ?>
			</p>
			<div class="d-flex flex-wrap gap-2">
				<?php foreach ($keywords as $i => $k): ?>
					<?php $ek_kw = htmlspecialchars($k->keyword); ?>
					<span class="ek-hist-key">
						<?php if ($ek_select): ?>
							<input type="checkbox" class="ek-hist-key-check"
								id="ekHistKw<?php echo (int) $i; ?>"
								form="<?php echo $ek_bulk; ?>" name="items[]"
								value="<?php echo $ek_kw; ?>"
								title="Pilih kata kunci ini" aria-label="Pilih kata kunci ini untuk dihapus dari riwayat">
						<?php endif; ?>
						<a href="<?php echo site_url('catalog?q='.urlencode($k->keyword)); ?>"
							class="badge rounded-pill text-bg-secondary border ek-history-keyword"
							title="Dicari <?php echo htmlspecialchars($k->ago); ?>">
							<?php echo $ek_kw; ?>
						</a>
						<?php if ( ! $ek_select): ?>
							<?php
							// Ikon trash per kata kunci memakai endpoint yang sama
							// dengan hapus terpilih: items[] berisi satu nilai.
							echo form_open('history/delete_items', array('class' => 'ek-hist-key-del', 'onsubmit' => 'return confirm(\'Hapus kata kunci ini dari riwayat?\');'));
							?>
								<input type="hidden" name="scope" value="pencarian">
								<input type="hidden" name="items[]" value="<?php echo $ek_kw; ?>">
								<button type="submit" class="ek-hist-key-del-btn" title="Hapus dari riwayat" aria-label="Hapus kata kunci ini dari riwayat"><i class="bi bi-trash" aria-hidden="true"></i></button>
							<?php echo form_close(); ?>
						<?php endif; ?>
					</span>
				<?php endforeach; ?>
			</div>

		<?php endif; ?>

	<?php endif; ?>
</div>
