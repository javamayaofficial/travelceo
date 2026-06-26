<?php
/** admin/views/members.php */
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $st = db()->prepare("SELECT * FROM users
                         WHERE role='member' AND deleted_at IS NULL
                           AND (name LIKE ? OR email LIKE ? OR wa LIKE ?)
                         ORDER BY id DESC LIMIT 300");
    $like = '%' . $q . '%';
    $st->execute([$like, $like, $like]);
    $rows = $st->fetchAll();
} else {
    $rows = db()->query("SELECT * FROM users WHERE role='member' AND deleted_at IS NULL ORDER BY id DESC LIMIT 300")->fetchAll();
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
    <thead><tr><th>Nama</th><th>Email</th><th>WhatsApp</th><th>Status</th><th>Daftar</th><th>Aksi</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <?php
        $ms = strtolower((string)($r['member_status'] ?? ''));
        if ($ms === '') $ms = ($r['status'] === 'active') ? 'approved' : 'pending';
        if (!in_array($ms, ['pending','approved','rejected'], true)) $ms = 'pending';
      ?>
      <tr>
        <td><?= e($r['name']) ?></td>
        <td><?= e($r['email']) ?></td>
        <td><?= e($r['wa']) ?></td>
        <td><span class="badge badge-<?= e($ms) ?>"><?= e($ms) ?></span></td>
        <td><?= e(date('d/m/Y', strtotime($r['created_at']))) ?></td>
        <td>
          <div class="actions">
            <form method="post" style="display:inline">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="approve_member">
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <button class="btn btn-primary btn-sm">Approve</button>
            </form>
            <form method="post" style="display:inline">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="reject_member">
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <button class="btn btn-ghost btn-sm">Reject</button>
            </form>
            <form method="post" style="display:inline" onsubmit="return confirm('Hapus member ini?');">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete_member">
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <button class="btn btn-danger btn-sm">Delete</button>
            </form>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
<?php endif; ?>
