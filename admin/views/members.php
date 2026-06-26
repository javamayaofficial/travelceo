<?php
/** admin/views/members.php */
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $st = db()->prepare("SELECT * FROM users WHERE role='member' AND (name LIKE ? OR email LIKE ? OR wa LIKE ?) ORDER BY id DESC LIMIT 300");
    $like = '%' . $q . '%';
    $st->execute([$like, $like, $like]);
    $rows = $st->fetchAll();
} else {
    $rows = db()->query("SELECT * FROM users WHERE role='member' ORDER BY id DESC LIMIT 300")->fetchAll();
}
?>
<div class="page-title"><h1>Member</h1><p class="muted">Daftar member terdaftar.</p></div>

<form method="get" class="search-bar">
  <input type="hidden" name="p" value="members">
  <input type="text" name="q" value="<?= e($q) ?>" placeholder="Cari nama, email, atau WhatsApp...">
  <button class="btn btn-ghost">Cari</button>
</form>

<?php if (!$rows): ?>
  <div class="empty"><div class="empty-ic">👥</div><h3>Belum ada member</h3><p>Member yang mendaftar akan tampil di sini.</p></div>
<?php else: ?>
  <div class="table-wrap"><table class="table">
    <thead><tr><th>Nama</th><th>Email</th><th>WhatsApp</th><th>Kode Ref</th><th>Status</th><th>Daftar</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= e($r['name']) ?></td>
        <td><?= e($r['email']) ?></td>
        <td><?= e($r['wa']) ?></td>
        <td><?= e($r['ref_code']) ?></td>
        <td><span class="badge badge-<?= $r['status']==='active'?'approved':'rejected' ?>"><?= e(ucfirst($r['status'])) ?></span></td>
        <td><?= e(date('d/m/Y', strtotime($r['created_at']))) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
<?php endif; ?>
