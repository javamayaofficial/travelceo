<?php /** vars: $u, $clicks, $sum, $list, $available, $withdrawSummary, $withdrawals */
$reflink = base_url('index.php') . '?ref=' . $u['ref_code'];
$site = setting('site_name', 'The Travel CEO');
$share = rawurlencode("Belajar bisnis travel di $site yuk! Daftar lewat link saya: $reflink");
?>
<section class="container dash">
  <h1>Program Affiliate 🤝</h1>
  <p class="muted">Bagikan link Anda, dapatkan komisi dari setiap penjualan.</p>

  <div class="reflink-card card">
    <span class="muted">Link affiliate Anda</span>
    <div class="reflink-row">
      <input type="text" id="reflink" value="<?= e($reflink) ?>" readonly>
      <button class="btn btn-ghost" type="button" onclick="copyRef()">Salin</button>
    </div>
    <a class="btn btn-primary btn-block" target="_blank" rel="noopener"
       href="https://wa.me/?text=<?= $share ?>">Bagikan via WhatsApp</a>
  </div>

  <div class="stat-grid">
    <div class="stat"><span class="muted">Total Klik</span><strong><?= (int)$clicks ?></strong></div>
    <div class="stat"><span class="muted">Total Penjualan</span><strong><?= (int)$sum['sales'] ?></strong></div>
    <div class="stat"><span class="muted">Total Komisi</span><strong><?= rupiah($sum['total']) ?></strong></div>
    <div class="stat"><span class="muted">Komisi Pending</span><strong><?= rupiah($sum['pending']) ?></strong></div>
    <div class="stat"><span class="muted">Komisi Disetujui</span><strong><?= rupiah($sum['approved']) ?></strong></div>
    <div class="stat"><span class="muted">Komisi Dibayar</span><strong><?= rupiah($sum['paid']) ?></strong></div>
    <div class="stat"><span class="muted">Siap Dicairkan</span><strong><?= rupiah($available) ?></strong></div>
    <div class="stat"><span class="muted">Request Pencairan</span><strong><?= rupiah($withdrawSummary['pending_total']) ?></strong></div>
  </div>

  <h2 class="dash-h2">Pencairan Komisi</h2>
  <div class="grid-2">
    <div class="card">
      <h3 class="mb8">Ajukan Pencairan</h3>
      <p class="muted">Request akan memakai seluruh komisi approved yang belum pernah diajukan.</p>
      <p><strong><?= rupiah($available) ?></strong> siap dicairkan saat ini.</p>
      <?php if ($available <= 0): ?>
        <div class="empty small">
          <p>Belum ada saldo komisi approved yang bisa dicairkan.</p>
        </div>
      <?php else: ?>
        <form method="post" class="form-grid">
          <?= csrf_field() ?>
          <label class="field"><span>Nama Bank / E-Wallet</span><input type="text" name="bank_name" required placeholder="BCA, Mandiri, BSI, DANA, OVO"></label>
          <label class="field"><span>Nama Pemilik Rekening</span><input type="text" name="account_name" required placeholder="Nama sesuai rekening"></label>
          <label class="field"><span>Nomor Rekening / Nomor Tujuan</span><input type="text" name="account_number" required placeholder="Contoh: 1234567890"></label>
          <label class="field"><span>Catatan (opsional)</span><textarea name="note" rows="3" placeholder="Contoh: prioritas transfer sore ini"></textarea></label>
          <button class="btn btn-primary" type="submit" onclick="return confirm('Ajukan pencairan semua komisi approved yang tersedia sekarang?')">Ajukan Pencairan</button>
        </form>
      <?php endif; ?>
    </div>

    <div class="card">
      <h3 class="mb8">Riwayat Pencairan</h3>
      <?php if (!$withdrawals): ?>
        <div class="empty small">
          <p>Belum ada request pencairan komisi.</p>
        </div>
      <?php else: ?>
        <div class="table-wrap"><table class="table">
          <thead><tr><th>Tanggal</th><th>Jumlah</th><th>Tujuan</th><th>Status</th></tr></thead>
          <tbody>
          <?php foreach ($withdrawals as $wd): ?>
            <tr>
              <td><?= e(date('d/m/Y H:i', strtotime($wd['created_at']))) ?></td>
              <td><?= rupiah($wd['amount']) ?></td>
              <td><?= e($wd['bank_name']) ?><br><span class="muted small"><?= e($wd['account_name']) ?> - <?= e($wd['account_number']) ?></span></td>
              <td><span class="badge badge-<?= e($wd['status']) ?>"><?= e(ucfirst($wd['status'])) ?></span></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table></div>
      <?php endif; ?>
    </div>
  </div>

  <h2 class="dash-h2">Riwayat Komisi</h2>
  <?php if (!$list): ?>
    <div class="empty small">
      <p>Belum ada komisi. Hasil akan muncul di sini setelah ada penjualan dari link Anda. Bagikan sekarang! 🚀</p>
    </div>
  <?php else: ?>
    <div class="table-wrap"><table class="table">
      <thead><tr><th>Tanggal</th><th>Order</th><th>Komisi</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($list as $r): ?>
        <tr><td><?= e(date('d/m/Y', strtotime($r['tdate']))) ?></td><td><?= e($r['code']) ?></td>
        <td><?= rupiah($r['amount']) ?></td><td><span class="badge badge-<?= e($r['status']) ?>"><?= e(ucfirst($r['status'])) ?></span></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  <?php endif; ?>
</section>
<script>
function copyRef(){var i=document.getElementById('reflink');i.select();i.setSelectionRange(0,9999);
try{navigator.clipboard.writeText(i.value);}catch(e){document.execCommand('copy');}
event.target.textContent='Tersalin!';setTimeout(function(){event.target.textContent='Salin';},1500);}
</script>
