<?php
/** admin/views/commissions.php */
$withdrawals = db()->query("SELECT w.*, u.name aname, u.ref_code
        FROM commission_withdrawals w
        JOIN users u ON u.id = w.affiliate_id
        ORDER BY FIELD(w.status,'requested','approved','paid','rejected'), w.id DESC
        LIMIT 200")->fetchAll();
$rows = db()->query("SELECT c.*, u.name aname, u.ref_code, t.code tcode FROM commissions c
        JOIN users u ON u.id=c.affiliate_id JOIN transactions t ON t.id=c.transaction_id
        ORDER BY c.id DESC LIMIT 300")->fetchAll();
$totalPaid = db()->query("SELECT COALESCE(SUM(amount),0) FROM commissions WHERE status='paid'")->fetchColumn();
$totalDue  = db()->query("SELECT COALESCE(SUM(amount),0) FROM commissions WHERE status='approved'")->fetchColumn();
$totalRequested = db()->query("SELECT COALESCE(SUM(amount),0) FROM commission_withdrawals WHERE status IN ('requested','approved')")->fetchColumn();
?>
<div class="page-title"><h1>Komisi Affiliate</h1><p class="muted">Kelola dan tandai pembayaran komisi.</p></div>

<div class="stat-grid">
  <div class="stat"><span class="muted">Perlu Dibayar</span><strong><?= rupiah($totalDue) ?></strong></div>
  <div class="stat"><span class="muted">Sudah Dibayar</span><strong><?= rupiah($totalPaid) ?></strong></div>
  <div class="stat"><span class="muted">Request Pencairan</span><strong><?= rupiah($totalRequested) ?></strong></div>
</div>

<div class="page-title" style="margin-top:18px"><h2>Request Pencairan</h2><p class="muted">Approve, tolak, atau tandai sudah dibayar.</p></div>

<?php if (!$withdrawals): ?>
  <div class="empty"><div class="empty-ic">🏦</div><h3>Belum ada request pencairan</h3><p>Request dari affiliate akan tampil di sini.</p></div>
<?php else: ?>
  <div class="table-wrap"><table class="table">
    <thead><tr><th>Affiliate</th><th>Jumlah</th><th>Rekening</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody>
    <?php foreach ($withdrawals as $w): ?>
      <tr>
        <td><?= e($w['aname']) ?><br><span class="muted small"><?= e($w['ref_code']) ?> · <?= e(date('d/m/Y H:i', strtotime($w['created_at']))) ?></span></td>
        <td><?= rupiah($w['amount']) ?></td>
        <td><?= e($w['bank_name']) ?><br><span class="muted small"><?= e($w['account_name']) ?> - <?= e($w['account_number']) ?></span><?php if (!empty($w['note'])): ?><br><span class="muted small"><?= e($w['note']) ?></span><?php endif; ?></td>
        <td><span class="badge badge-<?= e($w['status']) ?>"><?= e(ucfirst($w['status'])) ?></span></td>
        <td>
          <?php if ($w['status'] === 'requested'): ?>
            <form method="post" style="display:inline">
              <?= csrf_field() ?><input type="hidden" name="action" value="approve_withdrawal"><input type="hidden" name="id" value="<?= $w['id'] ?>">
              <button class="btn btn-primary btn-sm">Approve</button>
            </form>
            <form method="post" onsubmit="return confirm('Tolak request pencairan ini?')" style="display:inline">
              <?= csrf_field() ?><input type="hidden" name="action" value="reject_withdrawal"><input type="hidden" name="id" value="<?= $w['id'] ?>">
              <button class="btn btn-ghost btn-sm">Tolak</button>
            </form>
          <?php elseif ($w['status'] === 'approved'): ?>
            <form method="post" onsubmit="return confirm('Tandai request pencairan ini sudah dibayar?')" style="display:inline">
              <?= csrf_field() ?><input type="hidden" name="action" value="pay_withdrawal"><input type="hidden" name="id" value="<?= $w['id'] ?>">
              <button class="btn btn-primary btn-sm">Tandai Dibayar</button>
            </form>
            <form method="post" onsubmit="return confirm('Tolak request pencairan ini dan kembalikan saldo ke affiliate?')" style="display:inline">
              <?= csrf_field() ?><input type="hidden" name="action" value="reject_withdrawal"><input type="hidden" name="id" value="<?= $w['id'] ?>">
              <button class="btn btn-ghost btn-sm">Batalkan</button>
            </form>
          <?php elseif ($w['status'] === 'paid'): ?>
            ✓
          <?php else: ?>
            <span class="muted small">Ditolak</span>
          <?php endif; ?>
          <?php if (!empty($w['admin_note'])): ?><br><span class="muted small"><?= e($w['admin_note']) ?></span><?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
<?php endif; ?>

<div class="page-title" style="margin-top:18px"><h2>Riwayat Komisi</h2><p class="muted">Komisi per transaksi dan status pembayarannya.</p></div>
<?php if (!$rows): ?>
  <div class="empty"><div class="empty-ic">💸</div><h3>Belum ada komisi</h3><p>Komisi akan muncul saat transaksi dari affiliate disetujui.</p></div>
<?php else: ?>
  <div class="table-wrap"><table class="table">
    <thead><tr><th>Affiliate</th><th>Order</th><th>Komisi</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= e($r['aname']) ?><br><span class="muted small"><?= e($r['ref_code']) ?></span></td>
        <td><?= e($r['tcode']) ?></td>
        <td><?= rupiah($r['amount']) ?></td>
        <td><span class="badge badge-<?= e($r['status']) ?>"><?= e(ucfirst($r['status'])) ?></span></td>
        <td>
          <?php if ($r['status'] !== 'paid' && empty($r['withdrawal_id'])): ?>
            <form method="post" onsubmit="return confirm('Tandai komisi ini sudah dibayar?')" style="display:inline">
              <?= csrf_field() ?><input type="hidden" name="action" value="pay_commission"><input type="hidden" name="id" value="<?= $r['id'] ?>">
              <button class="btn btn-primary btn-sm">Tandai Dibayar</button></form>
          <?php elseif (!empty($r['withdrawal_id']) && $r['status'] !== 'paid'): ?>
            <span class="muted small">Masuk request #<?= (int)$r['withdrawal_id'] ?></span>
          <?php else: ?>✓<?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
<?php endif; ?>
