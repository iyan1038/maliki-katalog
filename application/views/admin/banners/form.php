<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="card border-0 shadow-sm" style="max-width: 640px;">
	<div class="card-body">
		<?php echo form_open_multipart($item_id ? 'admin/banners/edit/'.$item_id : 'admin/banners/create'); ?>
			<div class="mb-3">
				<label for="title" class="form-label">Judul Banner</label>
				<input type="text" class="form-control" id="title" name="title" value="<?php echo set_value('title', $item ? $item->title : ''); ?>" required>
				<?php echo form_error('title', '<small class="text-danger d-block">', '</small>'); ?>
			</div>
			<div class="mb-3">
				<label for="image" class="form-label">Gambar</label>
				<input type="file" class="form-control" id="image" name="image" accept="image/*" <?php echo $item ? '' : 'required'; ?>>
				<div class="form-text">Ukuran banner disarankan maksimal <strong>1920 × 440 px</strong>. Format: <strong>JPG, PNG, atau WEBP</strong>. Ukuran file maksimal <strong>4 MB</strong>.</div>
				<?php if ($item && $item->image): ?>
					<div class="mt-2">
						<img src="<?php echo base_url('assets/uploads/banners/'.$item->image); ?>" alt="banner" height="60" class="border rounded">
					</div>
				<?php endif; ?>
			</div>
			<div class="mb-3">
				<label for="url" class="form-label">URL Tujuan</label>
				<input type="url" class="form-control" id="url" name="url" value="<?php echo set_value('url', $item ? $item->url : ''); ?>">
				<?php echo form_error('url', '<small class="text-danger d-block">', '</small>'); ?>
			</div>
			<div class="row">
				<div class="col-md-6">
					<div class="mb-3">
						<label for="position" class="form-label">Posisi</label>
						<select class="form-select" id="position" name="position" required>
							<option value="top" <?php echo set_select('position', 'top', $item && $item->position == 'top'); ?>>Top</option>
							<option value="middle" <?php echo set_select('position', 'middle', $item && $item->position == 'middle'); ?>>Middle</option>
							<option value="bottom" <?php echo set_select('position', 'bottom', $item && $item->position == 'bottom'); ?>>Bottom</option>
						</select>
					</div>
				</div>
				<div class="col-md-6">
					<div class="mb-3">
						<label for="sort_order" class="form-label">Urutan</label>
						<input type="number" class="form-control" id="sort_order" name="sort_order" value="<?php echo set_value('sort_order', $item ? $item->sort_order : 0); ?>">
					</div>
				</div>
			</div>
			<div class="form-check mb-3">
				<input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" <?php echo ! $item || $item->is_active ? 'checked' : ''; ?>>
				<label class="form-check-label" for="is_active">Aktif</label>
			</div>
			<button type="submit" class="btn btn-primary">Simpan</button>
			<a href="<?php echo site_url('admin/banners'); ?>" class="btn btn-outline-secondary">Batal</a>
		<?php echo form_close(); ?>
	</div>
</div>
