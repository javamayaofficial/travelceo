<?php
/** admin/views/coupons.php */
$showForm = isset($_GET['new']);
$products = db()->query("SELECT id, title FROM products ORDER BY title")->fetchAll();
$affiliates = db()->query("SELECT id, name, ref_code FROM users WHERE role='member' ORDER BY name LIMIT 200")->fetchAll();
?>
<div class="page-title flexbetween">
  <div><h1>Kupon</h1><p class="muted">Buat kupon global, per produk, atau kupon affiliate.</p></div>
  <?php if (!$showForm): ?><a href="<?= e(admin_url('coupons', ['new'=>1])) ?>" class="btn btn-primary">+ Kupon Baru</a><?php endif; ?>
</div>

<?php if ($showForm): ?>
  <form method="post" class="card form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save_coupon">
    <div class="form-row">
      <label class="field"><span>Nama Kupon</span><input type="text" name="name" required placeholder="Promo Ramadan"></label>
      <label class="field"><span>Kode Kupon</span><input type="text" name="code" required placeholder="HEMAT10" style="text-transform:uppercase"></label>
    </div>
    <div class="form-row">
      <label class="field"><span>Diskon Persen (%)</span><input type="number" name="percent" value="0" min="0" max="100"></label>
      <label class="field"><span>Diskon Nominal (Rp)</span><input type="number" name="nominal" value="0" min="0"></label>
    </div>
    <small class="hint">Isi salah satu: persen ATAU nominal. Boleh juga keduanya (akan dijumlahkan).</small>
    <div class="form-row">
      <label class="field"><span>Berlaku untuk Produk</span><select name="product_id">
        <option value="">Semua Produk (Global)</option>
        <?php foreach ($products as $p): ?><option value="<?= $p['id'] ?>"><?= e($p['title']) ?></option><?php endforeach; ?>
      </select></label>
      <label class="field"><span>Kupon Affiliate (opsional)</span><select name="affiliate_id">
        <option value="">Bukan kupon affiliate</option>
        <?php foreach ($affiliates as $a): ?><option value="<?= $a['id'] ?>"><?= e($a['name']) ?> (<?= e($a['ref_code']) ?>)</option><?php endforeach; ?>
      </select><small class="hint">Jika dipilih, penjualan & komisi tercatat ke affiliate ini.</small></label>
    </div>
    <div class="form-row">
      <label class="field"><span>Tanggal Mulai</span><input type="date" name="start_date"></label>
      <label class="field"><span>Tanggal Berakhir</span><input type="date" name="end_date"></label>
    </div>
    <label class="field"><span>Batas Penggunaan (0 = tanpa batas)</span><input type="number" name="max_use" value="0" min="0"></label>
    <div class="form-actions">
      <button class="btn btn-primary">Buat Kupon</button>
      <a href="<?= e(admin_url('coupons')) ?>" class="btn btn-ghost">Batal</a>
    </div>
  </form>
<?php else: ?>
  <?php $rows = db()->query("SELECT c.*, p.title ptitle, u.name aname FROM coupons c
            LEFT JOIN products p ON p.id=c.product_id LEFT JOIN users u ON u.id=c.affiliate_id
            ORDER BY c.id DESC")->fetchAll(); ?>
  <?php if (!$rows): ?>
    <div class="empty"><div class="empty-ic">🎟️</div><h3>Belum ada kupon</h3><p>Buat kupon untuk menarik lebih banyak pembeli.</p>
      <a href="<?= e(admin_url('coupons', ['new'=>1])) ?>" class="btn btn-primary">+ Kupon Baru</a></div>
  <?php else: ?>
    <div class="table-wrap"><table class="table">
      <thead><tr><th>Kode</th><th>Diskon</th><th>Produk</th><th>Affiliate</th><th>Dipakai</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><strong><?= e($r['code']) ?></strong><br><span class="muted small"><?= e($r['name']) ?></span></td>
          <td><?= $r['percent']>0 ? $r['percent'].'%' : '' ?><?= ($r['percent']>0 && $r['nominal']>0)?' + ':'' ?><?= $r['nominal']>0 ? rupiah($r['nominal']) : '' ?></td>
          <td><?= $r['ptitle'] ? e($r['ptitle']) : 'Global' ?></td>
          <td><?= $r['aname'] ? e($r['aname']) : '—' ?></td>
          <td><?= (int)$r['used_count'] ?><?= $r['max_use']>0 ? '/'.$r['max_use'] : '' ?></td>
          <td><form method="post" onsubmit="return confirm('Hapus kupon ini?')" style="display:inline">
            <?= csrf_field() ?><input type="hidden" name="action" value="delete_coupon"><input type="hidden" name="id" value="<?= $r['id'] ?>">
            <button class="btn btn-danger btn-sm">Hapus</button></form></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  <?php endif; ?>
<?php endif; ?>
