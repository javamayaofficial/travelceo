<?php
/** admin/views/lessons.php — kelola materi per produk */
$pid = (int)($_GET['product'] ?? 0);
$st = db()->prepare("SELECT * FROM products WHERE id=?");
$st->execute([$pid]);
$product = $st->fetch();
if (!$product) { echo '<div class="empty"><p>Produk tidak ditemukan. <a href="'.e(admin_url('products')).'">Kembali</a></p></div>'; return; }

$edit = null;
if (!empty($_GET['edit'])) {
    $e = db()->prepare("SELECT * FROM lessons WHERE id=? AND product_id=?");
    $e->execute([(int)$_GET['edit'], $pid]);
    $edit = $e->fetch();
}
$ls = db()->prepare("SELECT * FROM lessons WHERE product_id=? ORDER BY sort, id");
$ls->execute([$pid]);
$lessons = $ls->fetchAll();
$nextSort = $lessons ? (max(array_column($lessons, 'sort')) + 1) : 1;
?>
<div class="page-title flexbetween">
  <div><h1>Materi: <?= e($product['title']) ?></h1><p class="muted"><a href="<?= e(admin_url('products')) ?>">← Semua produk</a></p></div>
</div>

<div class="two-col">
  <div class="card form-card">
    <h2><?= $edit ? 'Edit Materi' : 'Tambah Materi' ?></h2>
    <form method="post" class="form">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save_lesson">
      <input type="hidden" name="product_id" value="<?= $pid ?>">
      <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
      <label class="field"><span>Judul Materi</span><input type="text" name="title" value="<?= e($edit['title'] ?? '') ?>" required></label>
      <label class="field"><span>Deskripsi Singkat</span><input type="text" name="short_desc" value="<?= e($edit['short_desc'] ?? '') ?>"></label>
      <label class="field"><span>Link YouTube</span><input type="text" name="youtube" value="<?= e($edit['youtube'] ?? '') ?>" placeholder="https://youtu.be/..."><small class="hint">Tempel link YouTube biasa. Sistem akan memutar video dalam mode embed di dalam platform dan materi berikutnya baru terbuka setelah materi ini selesai.</small></label>
      <label class="field"><span>Urutan</span><input type="number" name="sort" value="<?= (int)($edit['sort'] ?? $nextSort) ?>" min="0"></label>
      <div class="form-actions">
        <button class="btn btn-primary"><?= $edit ? 'Simpan' : 'Tambah' ?></button>
        <?php if ($edit): ?><a href="<?= e(admin_url('lessons', ['product'=>$pid])) ?>" class="btn btn-ghost">Batal</a><?php endif; ?>
      </div>
    </form>
  </div>

  <div class="card">
    <h2>Daftar Materi (<?= count($lessons) ?>)</h2>
    <?php if (!$lessons): ?>
      <div class="empty small"><p>Belum ada materi. Tambahkan materi pertama lewat form di samping.</p></div>
    <?php else: ?>
      <ol class="admin-lesson-list">
        <?php foreach ($lessons as $l): ?>
          <li>
            <span class="all-num"><?= (int)$l['sort'] ?></span>
            <div class="all-info"><strong><?= e($l['title']) ?></strong><?php if ($l['short_desc']): ?><span class="muted small"><?= e($l['short_desc']) ?></span><?php endif; ?></div>
            <div class="all-act">
              <a href="<?= e(admin_url('lessons', ['product'=>$pid,'edit'=>$l['id']])) ?>" class="btn btn-ghost btn-sm">Edit</a>
              <form method="post" onsubmit="return confirm('Hapus materi ini?')" style="display:inline">
                <?= csrf_field() ?><input type="hidden" name="action" value="delete_lesson"><input type="hidden" name="product_id" value="<?= $pid ?>"><input type="hidden" name="id" value="<?= $l['id'] ?>">
                <button class="btn btn-danger btn-sm">Hapus</button></form>
            </div>
          </li>
        <?php endforeach; ?>
      </ol>
    <?php endif; ?>
  </div>
</div>
