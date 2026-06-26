<?php /** vars: $products */ ?>
<section class="container page-head">
  <h1>Semua Kelas &amp; Produk</h1>
  <p class="muted">Pilih kelas yang paling Anda butuhkan untuk membawa travel naik kelas.</p>
</section>
<section class="container">
  <?php if (!$products): ?>
    <div class="empty">
      <div class="empty-ic">📦</div>
      <h3>Produk segera hadir</h3>
      <p>Kelas dan produk digital akan muncul di sini. Pantau terus, ya!</p>
    </div>
  <?php else: ?>
    <div class="product-grid">
      <?php foreach ($products as $p): ?>
        <a class="pcard" href="<?= e(url('product', ['slug' => $p['slug']])) ?>">
          <div class="pthumb">
            <?php if ($p['thumbnail']): ?><img src="<?= e(base_url($p['thumbnail'])) ?>" alt="<?= e($p['title']) ?>" loading="lazy"><?php else: ?><span class="pthumb-ph">✈ <?= e(ucfirst($p['type'])) ?></span><?php endif; ?>
          </div>
          <div class="pbody">
            <span class="ptype"><?= e(ucfirst($p['type'])) ?></span>
            <h3><?= e($p['title']) ?></h3>
            <p class="muted"><?= e($p['short_desc']) ?></p>
            <div class="pprice"><?= (int)$p['price'] === 0 ? 'GRATIS' : rupiah($p['price']) ?></div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
