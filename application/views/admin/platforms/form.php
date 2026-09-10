<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="card border-0 shadow-sm" style="max-width: 640px;">
	<div class="card-body">
		<?php echo form_open_multipart($item_id ? 'admin/platforms/edit/'.$item_id : 'admin/platforms/create'); ?>
			<div class="mb-3">
				<label for="name" class="form-label">Nama Platform</label>
				<input type="text" class="form-control" id="name" name="name" value="<?php echo set_value('name', $item ? $item->name : ''); ?>" required>
				<?php echo form_error('name', '<small class="text-danger d-block">', '</small>'); ?>
			</div>
			<div class="mb-3">
				<label for="slug" class="form-label">Slug</label>
				<input type="text" class="form-control" id="slug" name="slug" value="<?php echo set_value('slug', $item ? $item->slug : ''); ?>" required>
				<?php echo form_error('slug', '<small class="text-danger d-block">', '</small>'); ?>
			</div>
			<div class="mb-3">
				<label for="url" class="form-label">URL Marketplace</label>
				<input type="url" class="form-control" id="url" name="url" value="<?php echo set_value('url', $item ? $item->url : ''); ?>" required>
				<?php echo form_error('url', '<small class="text-danger d-block">', '</small>'); ?>
			</div>
			<div class="mb-3">
				<label for="logo" class="form-label">Logo</label>
				<input type="file" class="form-control" id="logo" name="logo" accept="image/*">
				<?php if ($item && $item->logo): ?>
					<div class="mt-2">
						<img src="<?php echo base_url('assets/uploads/platforms/'.$item->logo); ?>" alt="logo" height="40">
					</div>
				<?php endif; ?>
			</div>
			<button type="submit" class="btn btn-primary">Simpan</button>
			<a href="<?php echo site_url('admin/platforms'); ?>" class="btn btn-outline-secondary">Batal</a>
		<?php echo form_close(); ?>
	</div>
</div>
