<?php
/** admin/views/access_pages.php */
$products = db()->query("SELECT id, title, type FROM products ORDER BY title")->fetchAll();
$edit = null;
if (!empty($_GET['edit'])) {
    $e = db()->prepare("SELECT * FROM access_pages WHERE id=?");
    $e->execute([(int)$_GET['edit']]);
    $edit = $e->fetch();
}
$showForm = isset($_GET['new']) || $edit;
?>
<div class="page-title flexbetween">
  <div><h1>Akses Produk</h1><p class="muted">Builder halaman akses setelah produk dibeli. Satu access page untuk satu produk.</p></div>
  <?php if (!$showForm): ?><a href="<?= e(admin_url('access_pages', ['new'=>1])) ?>" class="btn btn-primary">+ Akses Produk Baru</a><?php endif; ?>
</div>

<?php if ($showForm): ?>
  <form method="post" enctype="multipart/form-data" class="card form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save_access_page">
    <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
    <div class="form-row">
      <label class="field"><span>Produk</span>
        <select name="product_id" required>
          <option value="">Pilih produk</option>
          <?php foreach ($products as $p): ?>
            <option value="<?= $p['id'] ?>" <?= (int)($edit['product_id'] ?? 0) === (int)$p['id'] ? 'selected' : '' ?>><?= e($p['title']) ?> (<?= e(ucfirst($p['type'])) ?>)</option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="field"><span>Judul Akses Page</span><input type="text" name="title" value="<?= e($edit['title'] ?? '') ?>" required></label>
    </div>
    <div class="form-row">
      <label class="field"><span>Slug (URL)</span><input type="text" name="slug" value="<?= e($edit['slug'] ?? '') ?>" placeholder="otomatis dari judul"></label>
      <label class="field"><span>Status</span><select name="status">
        <option value="publish" <?= (($edit['status'] ?? '')==='publish')?'selected':'' ?>>Publish</option>
        <option value="draft" <?= (($edit['status'] ?? 'draft')==='draft')?'selected':'' ?>>Draft</option></select></label>
    </div>
    <label class="field"><span>Script HTML</span>
      <textarea name="html" rows="12" class="code" placeholder="<section>... HTML access page Anda ...</section>"><?= e($edit['html'] ?? '') ?></textarea>
      <small class="hint">Halaman ini akan dibuka member setelah produk terkait sudah dibeli dan aktif.</small></label>
    <div class="form-row">
      <label class="field"><span>Meta Title (SEO)</span><input type="text" name="meta_title" value="<?= e($edit['meta_title'] ?? '') ?>"></label>
      <label class="field"><span>Meta Description (SEO)</span><input type="text" name="meta_desc" value="<?= e($edit['meta_desc'] ?? '') ?>"></label>
    </div>
    <label class="field"><span>Featured Image</span><input type="file" name="featured_image" accept=".jpg,.jpeg,.png,.webp">
      <?php if (!empty($edit['featured_image'])): ?><small class="hint">Saat ini: <?= e(basename($edit['featured_image'])) ?></small><?php endif; ?></label>
    <div class="form-actions">
      <button class="btn btn-primary">Simpan Akses Produk</button>
      <a href="<?= e(admin_url('access_pages')) ?>" class="btn btn-ghost">Batal</a>
    </div>
  </form>
<?php else: ?>
  <?php $rows = db()->query("SELECT a.*, p.title product_title, p.type product_type FROM access_pages a JOIN products p ON p.id = a.product_id ORDER BY a.id DESC")->fetchAll(); ?>
  <?php if (!$rows): ?>
    <div class="empty"><div class="empty-ic">🔓</div><h3>Belum ada akses produk</h3><p>Buat access page untuk produk yang ingin dibuka setelah pembelian.</p>
      <a href="<?= e(admin_url('access_pages', ['new'=>1])) ?>" class="btn btn-primary">+ Akses Produk Baru</a></div>
  <?php else: ?>
    <div class="table-wrap"><table class="table">
      <thead><tr><th>Produk</th><th>Judul</th><th>Slug</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= e($r['product_title']) ?><br><span class="muted small"><?= e(ucfirst($r['product_type'])) ?></span></td>
          <td><?= e($r['title']) ?></td>
          <td><a href="<?= e(url('access', ['slug'=>$r['slug']])) ?>" target="_blank"><?= e($r['slug']) ?> ↗</a></td>
          <td><span class="badge badge-<?= $r['status']==='publish'?'approved':'pending' ?>"><?= e(ucfirst($r['status'])) ?></span></td>
          <td class="td-actions">
            <a href="<?= e(admin_url('access_pages', ['edit'=>$r['id']])) ?>" class="btn btn-ghost btn-sm">Edit</a>
            <form method="post" onsubmit="return confirm('Hapus akses produk ini?')" style="display:inline">
              <?= csrf_field() ?><input type="hidden" name="action" value="delete_access_page"><input type="hidden" name="id" value="<?= $r['id'] ?>">
              <button class="btn btn-danger btn-sm">Hapus</button></form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  <?php endif; ?>
<?php endif; ?>
