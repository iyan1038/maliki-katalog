<?php defined('BASEPATH') OR exit('No direct script access allowed');
$selected_kbli  = array();
$platform_urls  = array();
$platform_visible = array();

if ($item)
{
	if ( ! empty($item->kbli))
	{
		foreach ($item->kbli as $k)
		{
			$selected_kbli[] = $k->id;
		}
	}

	if ( ! empty($item->platforms))
	{
		foreach ($item->platforms as $pp)
		{
			$platform_urls[$pp->platform_id]    = $pp->product_url;
			$platform_visible[$pp->platform_id] = (int) $pp->is_visible;
		}
	}
}

$existing_images = $item ? count($item->images) : 0;
$remaining_slots = max(0, $max_images - $existing_images);
?>
<div class="card border-0 shadow-sm">
	<div class="card-body">
		<?php echo form_open_multipart($item_id ? 'admin/products/edit/'.$item_id : 'admin/products/create'); ?>
			<div class="row">
				<div class="col-md-6">
					<div class="mb-3">
						<label for="company_id" class="form-label">Perusahaan</label>
						<select class="form-select" id="company_id" name="company_id" required>
							<option value="">-- Pilih Perusahaan --</option>
							<?php foreach ($companies as $c): ?>
								<option value="<?php echo $c->id; ?>" <?php echo set_select('company_id', $c->id, $item && $item->company_id == $c->id); ?>>
									<?php echo htmlspecialchars($c->name); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<?php echo form_error('company_id', '<small class="text-danger d-block">', '</small>'); ?>
					</div>
				</div>
				<div class="col-md-6">
					<div class="mb-3">
						<label for="name" class="form-label">Nama Produk</label>
						<input type="text" class="form-control" id="name" name="name" value="<?php echo set_value('name', $item ? $item->name : ''); ?>" required>
						<?php echo form_error('name', '<small class="text-danger d-block">', '</small>'); ?>
					</div>
				</div>
				<div class="col-md-4">
					<div class="mb-3">
						<label for="price" class="form-label">Harga</label>
						<input type="number" step="0.01" class="form-control" id="price" name="price" value="<?php echo set_value('price', $item ? $item->price : ''); ?>" required>
						<?php echo form_error('price', '<small class="text-danger d-block">', '</small>'); ?>
					</div>
				</div>
				<div class="col-md-4">
					<div class="mb-3">
						<label for="unit" class="form-label">Satuan</label>
						<input type="text" class="form-control" id="unit" name="unit" value="<?php echo set_value('unit', $item ? $item->unit : ''); ?>" placeholder="mis. pcs, kg">
					</div>
				</div>
				<div class="col-md-4">
					<div class="mb-3">
						<label for="description" class="form-label">Deskripsi (Pendek)</label>
						<textarea class="form-control" id="description" name="description" rows="3"><?php echo set_value('description', $item ? $item->description : ''); ?></textarea>
					</div>
				</div>
				<div class="col-12">
					<div class="mb-3">
						<label for="long_description" class="form-label">Deskripsi Panjang</label>
						<textarea class="form-control" id="long_description" name="long_description" rows="5" placeholder="Deskripsi detail produk yang tampil di bawah kartu perusahaan..."><?php echo set_value('long_description', $item ? $item->long_description : ''); ?></textarea>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-check form-switch mb-2">
						<input class="form-check-input" type="checkbox" role="switch" name="is_promo" value="1" id="is_promo" <?php echo $item && $item->is_promo ? 'checked' : ''; ?>>
						<label class="form-check-label" for="is_promo">Produk Promo</label>
						<div class="form-text">Tandai produk sedang diskon. Harga lama dicoret dan dipakai Harga Promo, serta diutamakan di urutan katalog.</div>
					</div>
				</div>
				<div class="col-md-4" id="promo_price_wrap">
					<div class="mb-3">
						<label for="promo_price" class="form-label">Harga Promo</label>
						<input type="number" step="0.01" class="form-control" id="promo_price" name="promo_price" value="<?php echo set_value('promo_price', $item ? $item->promo_price : ''); ?>">
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-check form-switch mb-2">
						<input class="form-check-input" type="checkbox" role="switch" name="is_featured" value="1" id="is_featured" <?php echo $item && $item->is_featured ? 'checked' : ''; ?>>
						<label class="form-check-label" for="is_featured">Unggulan</label>
						<div class="form-text">Produk diutamakan/dinaikkan peringkatnya di daftar kartu produk dan katalog.</div>
					</div>
					<div class="form-check form-switch mb-2">
						<input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" id="is_active" <?php echo ! $item || $item->is_active ? 'checked' : ''; ?>>
						<label class="form-check-label" for="is_active">Aktif</label>
						<div class="form-text">Produk tampil dan dapat diakses publik. Matikan untuk menyembunyikan dari katalog.</div>
					</div>
				</div>

				<div class="col-12">
					<hr>
					<h6 class="fw-semibold">Gambar Produk (maksimal <?php echo $max_images; ?>)</h6>

					<?php if ($item && $existing_images > 0): ?>
						<div class="d-flex flex-wrap gap-3 mb-3">
							<?php foreach ($item->images as $img): ?>
								<div class="text-center">
									<img src="<?php echo base_url('assets/uploads/products/'.$img->filename); ?>" alt="gambar <?php echo $img->position; ?>" height="80" class="border rounded">
									<div class="small text-muted">Posisi <?php echo $img->position; ?></div>
									<a href="<?php echo site_url('admin/products/delete_image/'.$img->id); ?>" class="btn btn-sm btn-outline-danger mt-1" onclick="return confirm('Hapus gambar ini?');">Hapus</a>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<?php if ($remaining_slots > 0): ?>
						<div class="mb-3">
							<label for="images" class="form-label">Tambah Gambar (sisa <?php echo $remaining_slots; ?> slot)</label>
							<input type="file" class="form-control" id="images" name="images[]" accept="image/*" multiple data-max-slots="<?php echo $remaining_slots; ?>">
							<div class="form-text">JPG/PNG/WebP, maks 4MB per file. Pilih maksimal <?php echo $remaining_slots; ?> foto sekaligus.</div>
							<div id="imagesPreview" class="d-flex flex-wrap gap-2 mt-2"></div>
						</div>
					<?php else: ?>
						<div class="alert alert-info py-2">Slot gambar sudah penuh (<?php echo $max_images; ?>). Hapus salah satu untuk menambah.</div>
					<?php endif; ?>
				</div>

				<div class="col-md-6">
					<hr>
					<h6 class="fw-semibold">Kategori (KBLI)</h6>
					<div class="border rounded p-3" style="max-height: 180px; overflow-y: auto;">
						<?php foreach ($kbli_list as $kb): ?>
							<div class="form-check">
								<input class="form-check-input" type="checkbox" name="kbli[]" value="<?php echo $kb->id; ?>" id="pkb_<?php echo $kb->id; ?>" <?php echo in_array($kb->id, $selected_kbli) ? 'checked' : ''; ?>>
								<label class="form-check-label" for="pkb_<?php echo $kb->id; ?>"><?php echo htmlspecialchars($kb->code.' - '.$kb->name); ?></label>
							</div>
						<?php endforeach; ?>
					</div>
				</div>

				<div class="col-md-6">
					<hr>
					<h6 class="fw-semibold">Marketplace (URL Produk)</h6>
					<?php foreach ($platforms as $platform): ?>
						<div class="border rounded p-2 mb-2">
							<div class="form-check form-switch">
								<input class="form-check-input" type="checkbox" role="switch" name="platform_visible[<?php echo $platform->id; ?>]" value="1" id="pv_<?php echo $platform->id; ?>" <?php echo ! empty($platform_visible[$platform->id]) ? 'checked' : ''; ?>>
								<label class="form-check-label" for="pv_<?php echo $platform->id; ?>"><?php echo htmlspecialchars($platform->name); ?></label>
							</div>
							<div class="mt-1" id="pf_wrap_<?php echo $platform->id; ?>">
								<input type="url" class="form-control form-control-sm" id="pf_<?php echo $platform->id; ?>" name="platform_url[<?php echo $platform->id; ?>]" value="<?php echo isset($platform_urls[$platform->id]) ? htmlspecialchars($platform_urls[$platform->id]) : ''; ?>" placeholder="URL produk di marketplace ini">
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<button type="submit" class="btn btn-primary mt-3">Simpan</button>
			
			<a href="<?php echo site_url('admin/products'); ?>" class="btn btn-outline-secondary mt-3">Batal</a>
		<?php echo form_close(); ?>
	</div>
</div>

<script>
	(function () {
		var imagesInput = document.getElementById('images');
		var imagesPreview = document.getElementById('imagesPreview');

		if (imagesInput && imagesPreview)
		{
			var maxSlots = parseInt(imagesInput.getAttribute('data-max-slots') || '0', 10);
			var selectedFiles = [];

			function rebuildInput(files)
			{
				if (window.DataTransfer)
				{
					try
					{
						var dt = new DataTransfer();
						files.forEach(function (f) { dt.items.add(f); });
						imagesInput.files = dt.files;
						return;
					}
					catch (e) {}
				}
			}

			function renderPreview()
			{
				imagesPreview.innerHTML = '';

				var oldWarning = document.getElementById('imagesWarning');
				if (oldWarning) oldWarning.remove();

				selectedFiles.forEach(function (file, index) {
					var wrap = document.createElement('div');
					wrap.className = 'position-relative';
					wrap.style.cssText = 'display:inline-block;width:64px;height:64px;';

					var img = document.createElement('img');
					img.src = URL.createObjectURL(file);
					img.alt = file.name;
					img.className = 'border rounded';
					img.style.cssText = 'width:64px;height:64px;object-fit:cover;';
					wrap.appendChild(img);

					var rm = document.createElement('button');
					rm.type = 'button';
					rm.className = 'btn btn-danger btn-sm';
					rm.setAttribute('aria-label', 'Hapus');
					rm.title = 'Hapus';
					rm.textContent = '\u00d7';
					rm.style.cssText = 'position:absolute;top:-8px;right:-8px;line-height:1;border:none;border-radius:50%;width:22px;height:22px;padding:0;font-size:14px;color:#fff;background-color:#dc3545;';
					rm.addEventListener('click', function () {
						try { URL.revokeObjectURL(img.src); } catch (e) {}
						selectedFiles.splice(index, 1);
						rebuildInput(selectedFiles);
						renderPreview();
					});
					wrap.appendChild(rm);

					imagesPreview.appendChild(wrap);
				});

				if (selectedFiles.length > 0)
				{
					var counter = document.createElement('div');
					counter.className = 'w-100 small text-muted';
					counter.textContent = selectedFiles.length + ' dipilih (maksimal ' + maxSlots + ')';
					imagesPreview.appendChild(counter);
				}
			}

			imagesInput.addEventListener('change', function () {
				var incoming = Array.prototype.slice.call(this.files || []);

				if (incoming.length === 0)
				{
					return;
				}

				var overLimit = false;

				incoming.forEach(function (file) {
					var exists = selectedFiles.some(function (f) {
						return f.name === file.name && f.size === file.size && f.lastModified === file.lastModified;
					});

					if (exists)
					{
						return;
					}

					if (selectedFiles.length >= maxSlots)
					{
						overLimit = true;
						return;
					}

					selectedFiles.push(file);
				});

				rebuildInput(selectedFiles);
				renderPreview();

				if (overLimit)
				{
					var warn = document.createElement('div');
					warn.id = 'imagesWarning';
					warn.className = 'w-100 small text-danger';
					warn.textContent = 'Maksimal ' + maxSlots + ' foto. Kelebihan dibatalkan; pilihan yang sudah ada tetap utuh.';
					imagesPreview.appendChild(warn);
				}
			});
		}

		var promo = document.getElementById('is_promo');
		var wrap  = document.getElementById('promo_price_wrap');
		function toggle() {
			wrap.style.display = promo.checked ? '' : 'none';
		}
		promo.addEventListener('change', toggle);
		toggle();

		var pvSwitches = document.querySelectorAll('input[type="checkbox"][name^="platform_visible"]');
		pvSwitches.forEach(function (sw) {
			var pid = sw.name.match(/\d+/)[0];
			var inputWrap = document.getElementById('pf_wrap_' + pid);
			function togglePv() {
				inputWrap.style.display = sw.checked ? '' : 'none';
			}
			sw.addEventListener('change', togglePv);
			togglePv();
		});
	})();
</script>
