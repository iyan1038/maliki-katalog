<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="card border-0 shadow-sm" style="max-width: 640px;">
	<div class="card-body">
		<?php echo form_open($item_id ? 'admin/settings/edit/'.$item_id : 'admin/settings/create'); ?>
			<div class="mb-3">
				<label for="key" class="form-label">Key</label>
				<input type="text" class="form-control" id="key" name="key" value="<?php echo set_value('key', $item ? $item->key : ''); ?>" <?php echo $item_id ? 'readonly' : ''; ?> required>
				<?php if ($item_id): ?>
					<div class="form-text">Key tidak dapat diubah.</div>
				<?php endif; ?>
				<?php echo form_error('key', '<small class="text-danger d-block">', '</small>'); ?>
			</div>
			<div class="mb-3">
				<label for="value" class="form-label">Value</label>
				<textarea class="form-control" id="value" name="value" rows="3" required><?php echo set_value('value', $item ? $item->value : ''); ?></textarea>
				<?php echo form_error('value', '<small class="text-danger d-block">', '</small>'); ?>
			</div>
			<button type="submit" class="btn btn-primary">Simpan</button>
			<a href="<?php echo site_url('admin/settings'); ?>" class="btn btn-outline-secondary">Batal</a>
		<?php echo form_close(); ?>
	</div>
</div>
