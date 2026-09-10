<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="card border-0 shadow-sm" style="max-width: 640px;">
	<div class="card-body">
		<?php echo form_open($item_id ? 'admin/kbli/edit/'.$item_id : 'admin/kbli/create'); ?>
			<div class="mb-3">
				<label for="code" class="form-label">Kode KBLI</label>
				<input type="text" class="form-control" id="code" name="code" value="<?php echo set_value('code', $item ? $item->code : ''); ?>" required>
				<?php echo form_error('code', '<small class="text-danger d-block">', '</small>'); ?>
			</div>
			<div class="mb-3">
				<label for="name" class="form-label">Nama KBLI</label>
				<input type="text" class="form-control" id="name" name="name" value="<?php echo set_value('name', $item ? $item->name : ''); ?>" required>
				<?php echo form_error('name', '<small class="text-danger d-block">', '</small>'); ?>
			</div>
			<div class="mb-3">
				<label for="description" class="form-label">Deskripsi KBLI</label>
				<textarea class="form-control" id="description" name="description" rows="3" placeholder="Deskripsi rinci dari kode KBLI ini..."><?php echo set_value('description', $item ? $item->description : ''); ?></textarea>
			</div>
			<button type="submit" class="btn btn-primary">Simpan</button>
			<a href="<?php echo site_url('admin/kbli'); ?>" class="btn btn-outline-secondary">Batal</a>
		<?php echo form_close(); ?>
	</div>
</div>
