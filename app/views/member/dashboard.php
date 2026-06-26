<?php /** vars: $u, $courses, $transactions */ ?>
<section class="container dash">
  <div class="dash-hero">
    <h1>Halo, <?= e(explode(' ', $u['name'])[0]) ?> 👋</h1>
    <p class="muted">Selamat datang di dashboard belajar Anda.</p>
  </div>

  <div class="dash-quick">
    <a href="<?= e(url('products')) ?>" class="qcard"><span>📚</span> Jelajah Kelas</a>
    <a href="<?= e(url('affiliate')) ?>" class="qcard"><span>🤝</span> Program Affiliate</a>
    <a href="<?= e(url('profile')) ?>" class="qcard"><span>⚙️</span> Profil Saya</a>
  </div>

  <h2 class="dash-h2">Kelas Saya</h2>
  <?php if (!$courses): ?>
    <div class="empty">
      <div class="empty-ic">📭</div>
      <h3>Anda belum memiliki kelas</h3>
      <p>Kelas yang Anda beli akan muncul di sini. Yuk mulai naik kelas!</p>
      <a href="<?= e(url('products')) ?>" class="btn btn-primary">Lihat Katalog Kelas</a>
    </div>
  <?php else: ?>
    <div class="product-grid">
      <?php foreach ($courses as $c): ?>
        <?php $target = !empty($c['access_slug']) ? url('access', ['slug' => $c['access_slug']]) : url('learn', ['product' => $c['id']]); ?>
        <div class="pcard course-owned">
          <div class="pthumb">
            <?php if ($c['thumbnail']): ?><img src="<?= e(base_url($c['thumbnail'])) ?>" alt="<?= e($c['title']) ?>"><?php else: ?><span class="pthumb-ph">✈</span><?php endif; ?>
          </div>
          <div class="pbody">
            <h3><?= e($c['title']) ?></h3>
            <a href="<?= e($target) ?>" class="btn btn-primary btn-block"><?= !empty($c['access_slug']) ? 'Buka Akses Produk' : 'Buka Akses Kelas' ?></a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <h2 class="dash-h2">Riwayat Transaksi</h2>
  <?php if (!$transactions): ?>
    <div class="empty small"><p>Belum ada transaksi. Riwayat pembelian akan muncul di sini.</p></div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Kode</th><th>Produk</th><th>Total</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($transactions as $t): ?>
          <tr>
            <td><?= e($t['code']) ?></td>
            <td><?= e($t['ptitle']) ?></td>
            <td><?= rupiah($t['total']) ?></td>
            <td><span class="badge badge-<?= e($t['status']) ?>"><?= e(ucfirst($t['status'])) ?></span></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>
