<?php
/** admin/views/transactions.php */
$filter = $_GET['status'] ?? 'pending';
$allowed = ['pending','approved','rejected','all'];
if (!in_array($filter, $allowed, true)) $filter = 'pending';
$sql = "SELECT t.*, p.title ptitle, u.name uname, u.wa uwa FROM transactions t
        JOIN products p ON p.id=t.product_id JOIN users u ON u.id=t.user_id";
if ($filter !== 'all') $sql .= " WHERE t.status = " . db()->quote($filter);
$sql .= " ORDER BY t.id DESC LIMIT 200";
$rows = db()->query($sql)->fetchAll();
?>
<div class="page-title"><h1>Transaksi</h1><p class="muted">Verifikasi pembayaran member di sini.</p></div>

<div class="tabs">
  <?php foreach (['pending'=>'Menunggu','approved'=>'Disetujui','rejected'=>'Ditolak','all'=>'Semua'] as $k=>$lab): ?>
    <a class="tab <?= $filter===$k?'active':'' ?>" href="<?= e(admin_url('transactions', ['status'=>$k])) ?>"><?= $lab ?></a>
  <?php endforeach; ?>
</div>

<?php if (!$rows): ?>
  <div class="empty"><div class="empty-ic">✅</div><h3>Tidak ada transaksi <?= e($filter==='all'?'':$filter) ?></h3><p>Semua sudah beres di sini.</p></div>
<?php else: ?>
  <div class="tx-list">
    <?php foreach ($rows as $t): ?>
      <div class="card tx-card">
        <div class="tx-head">
          <div>
            <strong><?= e($t['code']) ?></strong> · <span class="badge badge-<?= e($t['status']) ?>"><?= e(ucfirst($t['status'])) ?></span>
            <div class="muted small"><?= e(date('d/m/Y H:i', strtotime($t['created_at']))) ?></div>
          </div>
          <div class="tx-total"><?= rupiah($t['total']) ?></div>
        </div>
        <div class="tx-grid">
          <div><span class="muted">Member</span><br><?= e($t['uname']) ?> · <?= e($t['uwa']) ?></div>
          <div><span class="muted">Produk</span><br><?= e($t['ptitle']) ?></div>
          <div><span class="muted">Bank</span><br><?= e($t['bank']) ?></div>
          <div><span class="muted">Kupon</span><br><?= $t['coupon_code'] ? e($t['coupon_code']) . ' (−' . rupiah($t['discount']) . ')' : '—' ?></div>
        </div>
        <?php if ($t['note']): ?><div class="tx-note">📝 <?= e($t['note']) ?></div><?php endif; ?>
        <?php if ($t['proof']): ?>
          <a href="<?= e(base_url($t['proof'])) ?>" target="_blank" class="btn btn-ghost btn-sm">Lihat Bukti Transfer ↗</a>
        <?php endif; ?>
        <?php if ($t['status'] === 'pending'): ?>
          <div class="tx-actions">
            <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="approve_tx"><input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
              <button class="btn btn-primary">✓ Setujui</button></form>
            <form method="post" onsubmit="return confirm('Tolak transaksi ini?')"><?= csrf_field() ?><input type="hidden" name="action" value="reject_tx"><input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
              <button class="btn btn-danger">✕ Tolak</button></form>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
