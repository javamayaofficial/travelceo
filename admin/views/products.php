<?php
/** admin/views/products.php */
$edit = null;
if (!empty($_GET['edit'])) {
    $st = db()->prepare("SELECT * FROM products WHERE id=?");
    $st->execute([(int)$_GET['edit']]);
    $edit = $st->fetch();
}
$showForm = isset($_GET['new']) || $edit;
$types = ['ecourse'=>'Ecourse','workshop'=>'Workshop','webinar'=>'Webinar Rekaman','membership'=>'Membership Premium','ebook'=>'Ebook','toolkit'=>'Toolkit','template'=>'Template'];
?>
<div class="page-title flexbetween">
  <div><h1>Produk</h1><p class="muted">Kelola ecourse, workshop, ebook, dan produk lainnya.</p></div>
  <?php if (!$showForm): ?><a href="<?= e(admin_url('products', ['new'=>1])) ?>" class="btn btn-primary">+ Produk Baru</a><?php endif; ?>
</div>

<?php if ($showForm): ?>
  <form method="post" enctype="multipart/form-data" class="card form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save_product">
    <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
    <div class="form-row">
      <label class="field"><span>Judul</span><input type="text" name="title" value="<?= e($edit['title'] ?? '') ?>" required></label>
      <label class="field"><span>Slug (URL)</span><input type="text" name="slug" value="<?= e($edit['slug'] ?? '') ?>" placeholder="otomatis dari judul"></label>
    </div>
    <div class="form-row">
      <label class="field"><span>Jenis Produk</span>
        <select name="type"><?php foreach ($types as $k=>$v): ?><option value="<?= $k ?>" <?= (($edit['type'] ?? '')===$k)?'selected':'' ?>><?= $v ?></option><?php endforeach; ?></select></label>
      <label class="field"><span>Harga (Rp)</span><input type="number" name="price" value="<?= (int)($edit['price'] ?? 0) ?>" min="0"></label>
    </div>
    <label class="field"><span>Deskripsi Singkat</span><input type="text" name="short_desc" value="<?= e($edit['short_desc'] ?? '') ?>" maxlength="255"></label>
    <label class="field"><span>Deskripsi Lengkap</span><textarea name="long_desc" rows="5"><?= e($edit['long_desc'] ?? '') ?></textarea></label>
    <div class="form-row">
      <label class="field"><span>Thumbnail</span><input type="file" name="thumbnail" accept=".jpg,.jpeg,.png,.webp">
        <?php if (!empty($edit['thumbnail'])): ?><small class="hint">Saat ini: <?= e(basename($edit['thumbnail'])) ?></small><?php endif; ?></label>
      <label class="field"><span>Status</span><select name="status">
        <option value="publish" <?= (($edit['status'] ?? '')==='publish')?'selected':'' ?>>Publish</option>
        <option value="draft" <?= (($edit['status'] ?? '')==='draft')?'selected':'' ?>>Draft</option></select></label>
    </div>
    <div class="form-actions">
      <button class="btn btn-primary">Simpan Produk</button>
      <a href="<?= e(admin_url('products')) ?>" class="btn btn-ghost">Batal</a>
    </div>
  </form>
<?php else: ?>
  <?php $rows = db()->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(); ?>
  <?php if (!$rows): ?>
    <div class="empty"><div class="empty-ic">📦</div><h3>Belum ada produk</h3><p>Tambahkan produk pertama Anda untuk mulai berjualan.</p>
      <a href="<?= e(admin_url('products', ['new'=>1])) ?>" class="btn btn-primary">+ Produk Baru</a></div>
  <?php else: ?>
    <div class="table-wrap"><table class="table">
      <thead><tr><th>Judul</th><th>Jenis</th><th>Harga</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= e($r['title']) ?></td>
          <td><?= e(ucfirst($r['type'])) ?></td>
          <td><?= (int)$r['price']===0?'Gratis':rupiah($r['price']) ?></td>
          <td><span class="badge badge-<?= $r['status']==='publish'?'approved':'pending' ?>"><?= e(ucfirst($r['status'])) ?></span></td>
          <td class="td-actions">
            <a href="<?= e(admin_url('lessons', ['product'=>$r['id']])) ?>" class="btn btn-ghost btn-sm">Materi</a>
            <a href="<?= e(admin_url('products', ['edit'=>$r['id']])) ?>" class="btn btn-ghost btn-sm">Edit</a>
            <form method="post" onsubmit="return confirm('Hapus produk ini beserta materinya?')" style="display:inline">
              <?= csrf_field() ?><input type="hidden" name="action" value="delete_product"><input type="hidden" name="id" value="<?= $r['id'] ?>">
              <button class="btn btn-danger btn-sm">Hapus</button></form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  <?php endif; ?>
<?php endif; ?>
