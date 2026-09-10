<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php if ( ! $configured): ?>
	<div class="alert alert-warning">
		<strong>WAAJO belum dikonfigurasi.</strong> Isi <code>application/config/waajo.php</code> (API key &amp; device).
		Pengiriman akan dicatat sebagai <em>failed</em> di log sampai kredensial valid.
	</div>
<?php endif; ?>

<div class="row g-4">
	<div class="col-lg-7">
		<div class="card border-0 shadow-sm">
			<div class="card-header bg-white">Kirim Promo ke Semua Member</div>
			<div class="card-body">
				<?php echo form_open('admin/wa/send_promo'); ?>
					<p class="small text-muted">Pilih produk promo yang akan dikirim. Pesan dikirim ke semua member yang punya nomor WhatsApp.</p>
					<div class="border rounded p-3 mb-3" style="max-height: 200px; overflow-y: auto;">
						<?php if (empty($promo_products)): ?>
							<p class="text-muted mb-0">Belum ada produk promo.</p>
						<?php else: ?>
							<?php foreach ($promo_products as $p): ?>
								<div class="form-check">
									<input class="form-check-input" type="checkbox" name="product_ids[]" value="<?php echo $p->id; ?>" id="wa_prod_<?php echo $p->id; ?>">
									<label class="form-check-label" for="wa_prod_<?php echo $p->id; ?>">
										<?php echo htmlspecialchars($p->name); ?>
										<span class="text-danger">Rp <?php echo number_format($p->promo_price !== NULL ? $p->promo_price : $p->price, 0, ',', '.'); ?></span>
									</label>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
					<button type="submit" class="btn ek-btn-marketplace">Kirim Promo</button>
				<?php echo form_close(); ?>
			</div>
		</div>

		<div class="card border-0 shadow-sm mt-4">
			<div class="card-header bg-white">Member dengan Nomor WhatsApp</div>
			<div class="card-body p-0">
				<table class="table table-hover align-middle mb-0">
					<thead>
						<tr>
							<th>Nama</th>
							<th>WhatsApp</th>
							<th class="text-end">Aksi</th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($members)): ?>
							<tr><td colspan="3" class="text-center text-muted py-4">Belum ada member yang mendaftarkan nomor WA.</td></tr>
						<?php else: ?>
							<?php foreach ($members as $m): ?>
							<tr>
								<td><?php echo htmlspecialchars($m->name); ?></td>
								<td><?php echo htmlspecialchars($m->wa_number); ?></td>
								<td class="text-end">
									<a href="<?php echo site_url('admin/wa/send_recommendation/'.$m->id); ?>" class="btn btn-sm btn-outline-primary">Kirim Rekomendasi</a>
								</td>
							</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div class="col-lg-5">
		<div class="card border-0 shadow-sm">
			<div class="card-header bg-white">Riwayat Pengiriman (wa_logs)</div>
			<div class="card-body p-0">
				<table class="table table-sm table-hover align-middle mb-0">
					<thead>
						<tr>
							<th>Member</th>
							<th>Tipe</th>
							<th>Status</th>
							<th>Waktu</th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($logs)): ?>
							<tr><td colspan="4" class="text-center text-muted py-4">Belum ada log pengiriman.</td></tr>
						<?php else: ?>
							<?php foreach ($logs as $log): ?>
							<tr>
								<td><?php echo $log->user_name ? htmlspecialchars($log->user_name) : '-'; ?></td>
								<td><span class="badge text-bg-light"><?php echo $log->message_type; ?></span></td>
								<td>
									<?php if ($log->status === 'sent'): ?>
										<span class="badge text-bg-success">sent</span>
									<?php elseif ($log->status === 'pending'): ?>
										<span class="badge text-bg-warning">pending</span>
									<?php else: ?>
										<span class="badge text-bg-danger">failed</span>
									<?php endif; ?>
								</td>
								<td class="small text-muted"><?php echo date('d M H:i', strtotime($log->created_at)); ?></td>
							</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
