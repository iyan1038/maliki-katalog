<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="card border-0 shadow-sm" style="max-width: 760px;">
	<div class="card-body">
		<?php echo form_open($item_id ? 'admin/categories/edit/'.$item_id : 'admin/categories/create'); ?>
			<div class="row">
				<div class="col-md-8">
					<div class="mb-3">
						<label for="name" class="form-label">Nama Kategori</label>
						<input type="text" class="form-control" id="name" name="name" value="<?php echo set_value('name', $item ? $item->name : ''); ?>" placeholder="contoh: Pakaian, Elektronik, Furnitur" required>
						<?php echo form_error('name', '<small class="text-danger d-block">', '</small>'); ?>
					</div>
				</div>
				<div class="col-md-4">
					<div class="mb-3">
						<label for="slug" class="form-label">Slug</label>
						<input type="text" class="form-control" id="slug" name="slug" value="<?php echo set_value('slug', $item ? $item->slug : ''); ?>" placeholder="pakaian" required>
						<?php echo form_error('slug', '<small class="text-danger d-block">', '</small>'); ?>
					</div>
				</div>
				<div class="col-md-4">
					<div class="mb-3">
						<label for="sort_order" class="form-label">Urutan</label>
						<input type="number" class="form-control" id="sort_order" name="sort_order" value="<?php echo set_value('sort_order', $item ? $item->sort_order : '0'); ?>">
						<div class="form-text">Semakin kecil, semakin awal tampil.</div>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-check form-switch mt-4">
						<input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" id="is_active" <?php echo ! $item || $item->is_active ? 'checked' : ''; ?>>
						<label class="form-check-label" for="is_active">Aktif di katalog</label>
					</div>
				</div>

				<div class="col-12">
					<hr>
					<h6 class="fw-semibold">Hubungkan ke Kode KBLI</h6>
					<p class="form-text mb-2">Centang kode KBLI yang termasuk ke kategori ini. Relasi diatur sekali ini saja &mdash; semua produk yang memiliki KBLI terpilih otomatis masuk ke kategori ini. Contoh: kategori <em>Pakaian</em> &harr; KBLI <em>14110 industri pakaian jadi konveksi</em> &amp; <em>14200 industri pakaian jadi dan barang dari kulit berbulu</em>.</p>
					<div class="border rounded p-3" style="max-height: 240px; overflow-y: auto;">
						<?php if (empty($kbli_list)): ?>
							<span class="text-muted">Belum ada KBLI. Tambahkan lewat menu KBLI terlebih dahulu.</span>
						<?php else: ?>
							<?php foreach ($kbli_list as $kb): ?>
								<div class="form-check">
									<input class="form-check-input" type="checkbox" name="kbli[]" value="<?php echo $kb->id; ?>" id="cat_kb_<?php echo $kb->id; ?>" <?php echo in_array((int) $kb->id, $selected_kbli) ? 'checked' : ''; ?>>
									<label class="form-check-label" for="cat_kb_<?php echo $kb->id; ?>"><?php echo htmlspecialchars($kb->code.' - '.$kb->name); ?></label>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<button type="submit" class="btn btn-primary mt-3">Simpan</button>
			<a href="<?php echo site_url('admin/categories'); ?>" class="btn btn-outline-secondary mt-3">Batal</a>
		<?php echo form_close(); ?>
	</div>
</div>

<script>
	(function () {
		var nameEl  = document.getElementById('name');
		var slugEl  = document.getElementById('slug');
		var changed = false;
		slugEl.addEventListener('input', function () { changed = true; });
		nameEl.addEventListener('input', function () {
			if (changed) return;
			var slug = nameEl.value.toLowerCase().trim()
				.replace(/[^a-z0-9\s-]/g, '')
				.replace(/[\s_-]+/g, '-')
				.replace(/^-+|-+$/g, '');
			slugEl.value = slug;
		});
	})();
</script>