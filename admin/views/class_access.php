<?php
/** admin/views/class_access.php */
$rows = db()->query("SELECT
    p.id,
    p.title,
    p.slug,
    p.type,
    p.status,
    COUNT(DISTINCT l.id) AS lesson_count,
    COUNT(DISTINCT e.id) AS member_count
    FROM products p
    LEFT JOIN lessons l ON l.product_id = p.id
    LEFT JOIN enrollments e ON e.product_id = p.id
    GROUP BY p.id, p.title, p.slug, p.type, p.status
    ORDER BY p.title")->fetchAll();
?>
<div class="page-title flexbetween">
  <div>
    <h1>Akses Kelas</h1>
    <p class="muted">Semua kelas memakai format LMS berurutan. Member hanya bisa membuka materi berikutnya setelah materi sebelumnya selesai ditonton.</p>
  </div>
</div>

<div class="callout">
  Video YouTube diputar di dalam platform dengan mode embed privasi. Link mentah video tidak ditampilkan ke member dari halaman kelas.
</div>

<?php if (!$rows): ?>
  <div class="empty">
    <div class="empty-ic">🎓</div>
    <h3>Belum ada kelas</h3>
    <p>Tambahkan produk dan materi terlebih dahulu agar akses kelas bisa dipakai member.</p>
    <a href="<?= e(admin_url('products', ['new' => 1])) ?>" class="btn btn-primary">+ Produk Baru</a>
  </div>
<?php else: ?>
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>Kelas</th><th>Materi</th><th>Member</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td>
            <?= e($r['title']) ?><br>
            <span class="muted small"><?= e(ucfirst($r['type'])) ?></span>
          </td>
          <td><?= (int)$r['lesson_count'] ?> materi</td>
          <td><?= (int)$r['member_count'] ?> member</td>
          <td><span class="badge badge-<?= $r['status'] === 'publish' ? 'approved' : 'pending' ?>"><?= e(ucfirst($r['status'])) ?></span></td>
          <td class="td-actions">
            <a href="<?= e(admin_url('lessons', ['product' => $r['id']])) ?>" class="btn btn-ghost btn-sm">Kelola Materi</a>
            <a href="<?= e(url('product', ['slug' => $r['slug']])) ?>" class="btn btn-ghost btn-sm" target="_blank">Lihat Produk</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
