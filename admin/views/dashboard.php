<?php
/** admin/views/dashboard.php */
$pdo = db();
$stat = [
    'member'    => $pdo->query("SELECT COUNT(*) FROM users WHERE role='member'")->fetchColumn(),
    'aktif'     => $pdo->query("SELECT COUNT(*) FROM users WHERE role='member' AND status='active'")->fetchColumn(),
    'penjualan' => $pdo->query("SELECT COALESCE(SUM(total),0) FROM transactions WHERE status='approved'")->fetchColumn(),
    'produk'    => $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'affiliate' => $pdo->query("SELECT COUNT(DISTINCT affiliate_id) FROM commissions")->fetchColumn(),
    'komisi'    => $pdo->query("SELECT COALESCE(SUM(amount),0) FROM commissions")->fetchColumn(),
    'transaksi' => $pdo->query("SELECT COUNT(*) FROM transactions")->fetchColumn(),
    'pending'   => $pdo->query("SELECT COUNT(*) FROM transactions WHERE status='pending'")->fetchColumn(),
];
$recent = $pdo->query("SELECT t.*, p.title ptitle, u.name uname FROM transactions t
                       JOIN products p ON p.id=t.product_id JOIN users u ON u.id=t.user_id
                       ORDER BY t.id DESC LIMIT 8")->fetchAll();
?>
<div class="page-title"><h1>Dashboard</h1><p class="muted">Ringkasan platform Anda hari ini.</p></div>

<?php if ($stat['pending']): ?>
  <div class="callout"><strong><?= $stat['pending'] ?> transaksi</strong> menunggu verifikasi.
    <a href="<?= e(admin_url('transactions')) ?>" class="link-more">Verifikasi sekarang →</a></div>
<?php endif; ?>

<div class="stat-grid admin-stats">
  <div class="stat"><span class="muted">Total Member</span><strong><?= (int)$stat['member'] ?></strong></div>
  <div class="stat"><span class="muted">Member Aktif</span><strong><?= (int)$stat['aktif'] ?></strong></div>
  <div class="stat"><span class="muted">Total Penjualan</span><strong><?= rupiah($stat['penjualan']) ?></strong></div>
  <div class="stat"><span class="muted">Total Produk</span><strong><?= (int)$stat['produk'] ?></strong></div>
  <div class="stat"><span class="muted">Total Affiliate</span><strong><?= (int)$stat['affiliate'] ?></strong></div>
  <div class="stat"><span class="muted">Total Komisi</span><strong><?= rupiah($stat['komisi']) ?></strong></div>
  <div class="stat"><span class="muted">Total Transaksi</span><strong><?= (int)$stat['transaksi'] ?></strong></div>
  <div class="stat"><span class="muted">Menunggu Verifikasi</span><strong><?= (int)$stat['pending'] ?></strong></div>
</div>

<div class="card admin-card">
  <h2>Transaksi Terbaru</h2>
  <?php if (!$recent): ?>
    <div class="empty small"><p>Belum ada transaksi. Data penjualan akan muncul di sini.</p></div>
  <?php else: ?>
    <div class="table-wrap"><table class="table">
      <thead><tr><th>Kode</th><th>Member</th><th>Produk</th><th>Total</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($recent as $t): ?>
        <tr><td><?= e($t['code']) ?></td><td><?= e($t['uname']) ?></td><td><?= e($t['ptitle']) ?></td>
        <td><?= rupiah($t['total']) ?></td><td><span class="badge badge-<?= e($t['status']) ?>"><?= e(ucfirst($t['status'])) ?></span></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  <?php endif; ?>
</div>
