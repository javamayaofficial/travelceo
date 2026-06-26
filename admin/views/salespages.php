<?php
/** admin/views/salespages.php */
$edit = null;
if (!empty($_GET['edit'])) {
    $e = db()->prepare("SELECT * FROM salespages WHERE id=?");
    $e->execute([(int)$_GET['edit']]);
    $edit = $e->fetch();
}
$showForm = isset($_GET['new']) || $edit;
?>
<div class="page-title flexbetween">
  <div><h1>Salespage Builder</h1><p class="muted">Buat halaman jualan dengan HTML. Pilih satu untuk tampil di homepage.</p></div>
  <?php if (!$showForm): ?><a href="<?= e(admin_url('salespages', ['new'=>1])) ?>" class="btn btn-primary">+ Salespage Baru</a><?php endif; ?>
</div>

<?php if ($showForm): ?>
  <form method="post" enctype="multipart/form-data" class="card form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save_salespage">
    <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
    <div class="form-row">
      <label class="field"><span>Judul Salespage</span><input type="text" name="title" value="<?= e($edit['title'] ?? '') ?>" required></label>
      <label class="field"><span>Slug (URL)</span><input type="text" name="slug" value="<?= e($edit['slug'] ?? '') ?>" placeholder="otomatis dari judul"></label>
    </div>
    <label class="field"><span>Script HTML</span>
      <textarea name="html" rows="12" class="code" placeholder="<section>... HTML salespage Anda ...</section>"><?= e($edit['html'] ?? '') ?></textarea>
      <small class="hint">Tempel kode HTML lengkap. Mendukung tag &lt;section&gt;, &lt;img&gt;, dll.</small></label>
    <div class="form-row">
      <label class="field"><span>Meta Title (SEO)</span><input type="text" name="meta_title" value="<?= e($edit['meta_title'] ?? '') ?>"></label>
      <label class="field"><span>Meta Description (SEO)</span><input type="text" name="meta_desc" value="<?= e($edit['meta_desc'] ?? '') ?>"></label>
    </div>
    <div class="form-row">
      <label class="field"><span>ID Piksel Facebook</span><input type="text" name="facebook_pixel_id" value="<?= e($edit['facebook_pixel_id'] ?? '') ?>" inputmode="numeric" placeholder="Contoh: 123456789012345"></label>
      <div class="field"><span>&nbsp;</span><small class="hint">Opsional. Jika diisi, salespage ini memakai Pixel Facebook sendiri. Jika kosong, sistem memakai pixel global dari pengaturan.</small></div>
    </div>
    <div class="form-row">
      <label class="field"><span>Featured Image</span><input type="file" name="featured_image" accept=".jpg,.jpeg,.png,.webp"></label>
      <label class="field"><span>Status</span><select name="status">
        <option value="publish" <?= (($edit['status'] ?? '')==='publish')?'selected':'' ?>>Publish</option>
        <option value="draft" <?= (($edit['status'] ?? 'draft')==='draft')?'selected':'' ?>>Draft</option></select></label>
    </div>
    <label class="check"><input type="checkbox" name="show_home" value="1" <?= !empty($edit['show_home'])?'checked':'' ?>> Tampilkan di Homepage</label>
    <div class="form-actions">
      <button class="btn btn-primary">Simpan Salespage</button>
      <a href="<?= e(admin_url('salespages')) ?>" class="btn btn-ghost">Batal</a>
    </div>
  </form>
<?php else: ?>
  <?php $rows = db()->query("SELECT * FROM salespages ORDER BY id DESC")->fetchAll(); ?>
  <?php if (!$rows): ?>
    <div class="empty"><div class="empty-ic">📄</div><h3>Belum ada salespage</h3><p>Buat salespage pertama Anda untuk promosi produk.</p>
      <a href="<?= e(admin_url('salespages', ['new'=>1])) ?>" class="btn btn-primary">+ Salespage Baru</a></div>
  <?php else: ?>
    <div class="table-wrap"><table class="table">
      <thead><tr><th>Judul</th><th>Slug</th><th>Status</th><th>Homepage</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= e($r['title']) ?></td>
          <td><a href="<?= e(url('sales', ['slug'=>$r['slug']])) ?>" target="_blank"><?= e($r['slug']) ?> ↗</a></td>
          <td><span class="badge badge-<?= $r['status']==='publish'?'approved':'pending' ?>"><?= e(ucfirst($r['status'])) ?></span></td>
          <td><?= $r['show_home'] ? '⭐ Ya' : '—' ?></td>
          <td class="td-actions">
            <a href="<?= e(admin_url('salespages', ['edit'=>$r['id']])) ?>" class="btn btn-ghost btn-sm">Edit</a>
            <form method="post" onsubmit="return confirm('Hapus salespage ini?')" style="display:inline">
              <?= csrf_field() ?><input type="hidden" name="action" value="delete_salespage"><input type="hidden" name="id" value="<?= $r['id'] ?>">
              <button class="btn btn-danger btn-sm">Hapus</button></form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  <?php endif; ?>
<?php endif; ?>
