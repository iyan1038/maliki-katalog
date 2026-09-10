<?php defined('BASEPATH') OR exit('No direct script access allowed');
$selected_kbli = array();
if ($item && ! empty($item->kbli))
{
	foreach ($item->kbli as $k)
	{
		$selected_kbli[] = $k->id;
	}
}
?>
<div class="card border-0 shadow-sm">
	<div class="card-body">
		<?php echo form_open_multipart($item_id ? 'admin/companies/edit/'.$item_id : 'admin/companies/create'); ?>
			<div class="row">
				<div class="col-md-6">
					<div class="mb-3">
						<label for="user_id" class="form-label">Pemilik (User)</label>
						<select class="form-select" id="user_id" name="user_id" required>
							<option value="">-- Pilih Pemilik --</option>
							<?php foreach ($owners as $owner): ?>
								<option value="<?php echo $owner->id; ?>" <?php echo set_select('user_id', $owner->id, $item && $item->user_id == $owner->id); ?>>
									<?php echo htmlspecialchars($owner->name).' ('.$owner->email.')'; ?>
								</option>
							<?php endforeach; ?>
						</select>
						<?php echo form_error('user_id', '<small class="text-danger d-block">', '</small>'); ?>
					</div>
				</div>
				<div class="col-md-6">
					<div class="mb-3">
						<label for="name" class="form-label">Nama Perusahaan</label>
						<input type="text" class="form-control" id="name" name="name" value="<?php echo set_value('name', $item ? $item->name : ''); ?>" required>
						<?php echo form_error('name', '<small class="text-danger d-block">', '</small>'); ?>
					</div>
				</div>
				<div class="col-md-6">
					<div class="mb-3">
						<label for="npwp" class="form-label">NPWP</label>
						<input type="text" class="form-control" id="npwp" name="npwp" value="<?php echo set_value('npwp', $item ? $item->npwp : ''); ?>">
					</div>
				</div>
				<div class="col-md-6">
					<div class="mb-3">
						<label for="city" class="form-label">Kota</label>
						<input type="text" class="form-control" id="city" name="city" value="<?php echo set_value('city', $item ? $item->city : ''); ?>">
					</div>
				</div>
				<div class="col-md-6">
					<div class="mb-3">
						<label for="address" class="form-label">Alamat</label>
						<textarea class="form-control" id="address" name="address" rows="3"><?php echo set_value('address', $item ? $item->address : ''); ?></textarea>
					</div>
				</div>
				<div class="col-md-6">
					<div class="mb-3">
						<label for="description" class="form-label">Deskripsi</label>
						<textarea class="form-control" id="description" name="description" rows="3"><?php echo set_value('description', $item ? $item->description : ''); ?></textarea>
					</div>
				</div>
				<div class="col-md-6">
					<div class="mb-3">
						<label for="logo" class="form-label">Logo</label>
						<input type="file" class="form-control" id="logo" name="logo" accept="image/*">
						<?php if ($item && $item->logo): ?>
							<div class="mt-2">
								<img src="<?php echo base_url('assets/uploads/companies/'.$item->logo); ?>" alt="logo" height="50">
							</div>
						<?php endif; ?>
					</div>
				</div>
				<div class="col-md-6">
					<div class="mb-3">
						<label class="form-label">Kode KBLI</label>
						<div class="border rounded p-3" style="max-height: 160px; overflow-y: auto;">
							<?php foreach ($kbli_list as $kb): ?>
								<div class="form-check">
									<input class="form-check-input" type="checkbox" name="kbli[]" value="<?php echo $kb->id; ?>" id="kbli_<?php echo $kb->id; ?>" <?php echo in_array($kb->id, $selected_kbli) ? 'checked' : ''; ?>>
									<label class="form-check-label" for="kbli_<?php echo $kb->id; ?>"><?php echo htmlspecialchars($kb->code.' - '.$kb->name); ?></label>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
				<div class="col-12">
					<div class="form-check mb-3">
						<input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" <?php echo ! $item || $item->is_active ? 'checked' : ''; ?>>
						<label class="form-check-label" for="is_active">Aktif</label>
					</div>
				</div>
			</div>
			<button type="submit" class="btn btn-primary">Simpan</button>
			<a href="<?php echo site_url('admin/companies'); ?>" class="btn btn-outline-secondary">Batal</a>
		<?php echo form_close(); ?>
	</div>
</div>
