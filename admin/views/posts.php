<?php
/** admin/views/posts.php */
$edit = null;
if (!empty($_GET['edit'])) {
    $e = db()->prepare("SELECT * FROM posts WHERE id=?");
    $e->execute([(int)$_GET['edit']]);
    $edit = $e->fetch();
}
$showForm = isset($_GET['new']) || $edit;
$publishedValue = !empty($edit['published_at']) ? date('Y-m-d\TH:i', strtotime($edit['published_at'])) : date('Y-m-d\TH:i');
?>
<div class="page-title flexbetween">
  <div><h1>Post Builder</h1><p class="muted">Kelola artikel blog seperti salespage builder. Tiga post terbaru akan tampil di homepage.</p></div>
  <?php if (!$showForm): ?><a href="<?= e(admin_url('posts', ['new'=>1])) ?>" class="btn btn-primary">+ Post Baru</a><?php endif; ?>
</div>

<?php if ($showForm): ?>
  <form method="post" enctype="multipart/form-data" class="card form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save_post">
    <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
    <div class="form-row">
      <label class="field"><span>Judul Post</span><input type="text" name="title" value="<?= e($edit['title'] ?? '') ?>" required></label>
      <label class="field"><span>Slug (URL)</span><input type="text" name="slug" value="<?= e($edit['slug'] ?? '') ?>" placeholder="otomatis dari judul"></label>
    </div>
    <div class="form-row">
      <label class="field"><span>Jadwal Tayang</span><input type="datetime-local" name="published_at" value="<?= e($publishedValue) ?>"></label>
      <label class="field"><span>Status</span><select name="status">
        <option value="publish" <?= (($edit['status'] ?? '')==='publish')?'selected':'' ?>>Publish</option>
        <option value="draft" <?= (($edit['status'] ?? 'draft')==='draft')?'selected':'' ?>>Draft</option></select></label>
    </div>
    <label class="field"><span>Ringkasan Singkat</span><input type="text" name="excerpt" value="<?= e($edit['excerpt'] ?? '') ?>" maxlength="255"><small class="hint">Akan dipakai di homepage dan daftar blog. Jika kosong, sistem ambil ringkasan otomatis dari isi artikel.</small></label>
    <label class="field"><span>Konten HTML</span>
      <textarea name="html" rows="14" class="code" placeholder="<article>... isi artikel blog ...</article>"><?= e($edit['html'] ?? '') ?></textarea>
      <small class="hint">Mendukung HTML seperti salespage builder. Cocok untuk artikel blog, tutorial, dan SEO content.</small></label>
    <div class="form-row">
      <label class="field"><span>Meta Title (SEO)</span><input type="text" name="meta_title" value="<?= e($edit['meta_title'] ?? '') ?>"></label>
      <label class="field"><span>Meta Description (SEO)</span><input type="text" name="meta_desc" value="<?= e($edit['meta_desc'] ?? '') ?>"></label>
    </div>
    <label class="field"><span>Featured Image</span><input type="file" name="featured_image" accept=".jpg,.jpeg,.png,.webp">
      <?php if (!empty($edit['featured_image'])): ?><small class="hint">Saat ini: <?= e(basename($edit['featured_image'])) ?></small><?php endif; ?></label>
    <div class="form-actions">
      <button class="btn btn-primary">Simpan Post</button>
      <a href="<?= e(admin_url('posts')) ?>" class="btn btn-ghost">Batal</a>
    </div>
  </form>
<?php else: ?>
  <?php $rows = db()->query("SELECT * FROM posts ORDER BY COALESCE(published_at, created_at) DESC, id DESC")->fetchAll(); ?>
  <?php if (!$rows): ?>
    <div class="empty"><div class="empty-ic">📝</div><h3>Belum ada post</h3><p>Buat artikel pertama untuk blog dan SEO website Anda.</p>
      <a href="<?= e(admin_url('posts', ['new'=>1])) ?>" class="btn btn-primary">+ Post Baru</a></div>
  <?php else: ?>
    <div class="table-wrap"><table class="table">
      <thead><tr><th>Judul</th><th>Slug</th><th>Tayang</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= e($r['title']) ?></td>
          <td><a href="<?= e(url('post', ['slug'=>$r['slug']])) ?>" target="_blank"><?= e($r['slug']) ?> ↗</a></td>
          <td><?= e(date('d M Y H:i', strtotime($r['published_at'] ?: $r['created_at']))) ?></td>
          <td><span class="badge badge-<?= $r['status']==='publish'?'approved':'pending' ?>"><?= e(ucfirst($r['status'])) ?></span></td>
          <td class="td-actions">
            <a href="<?= e(admin_url('posts', ['edit'=>$r['id']])) ?>" class="btn btn-ghost btn-sm">Edit</a>
            <form method="post" onsubmit="return confirm('Hapus post ini?')" style="display:inline">
              <?= csrf_field() ?><input type="hidden" name="action" value="delete_post"><input type="hidden" name="id" value="<?= $r['id'] ?>">
              <button class="btn btn-danger btn-sm">Hapus</button></form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  <?php endif; ?>
<?php endif; ?>
