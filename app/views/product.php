<?php /** vars: $product, $lessons, $owned */ ?>
<section class="container product-detail">
  <div class="pd-grid">
    <div class="pd-media">
      <?php if ($product['thumbnail']): ?>
        <img src="<?= e(base_url($product['thumbnail'])) ?>" alt="<?= e($product['title']) ?>">
      <?php else: ?>
        <div class="pd-media-ph">✈ <?= e(ucfirst($product['type'])) ?></div>
      <?php endif; ?>
    </div>
    <div class="pd-info">
      <span class="ptype"><?= e(ucfirst($product['type'])) ?></span>
      <h1><?= e($product['title']) ?></h1>
      <p class="muted"><?= e($product['short_desc']) ?></p>
      <?php if (!empty($product['event_start_at']) || !empty($product['event_location']) || !empty($product['event_city']) || !empty($seat_stats['quota'])): ?>
        <div class="pd-meta-list">
          <?php if (!empty($product['event_start_at'])): ?>
            <div class="pd-meta-item"><span>Tanggal Event</span><strong><?= e(date('d M Y, H:i', strtotime($product['event_start_at']))) ?> WIB</strong></div>
          <?php endif; ?>
          <?php if (!empty($product['event_location']) || !empty($product['event_city'])): ?>
            <div class="pd-meta-item"><span>Lokasi</span><strong><?= e(trim(($product['event_location'] ?? '') . (!empty($product['event_city']) ? ', ' . $product['event_city'] : ''), ', ')) ?></strong></div>
          <?php endif; ?>
          <?php if (!empty($seat_stats['quota'])): ?>
            <div class="pd-meta-item"><span>Seat Tersedia</span><strong><?= (int)$seat_stats['approved'] ?>/<?= (int)$seat_stats['quota'] ?> terisi<?php if (isset($seat_stats['remaining'])): ?> · sisa <?= (int)$seat_stats['remaining'] ?><?php endif; ?></strong></div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
      <div class="pd-price"><?= (int)$product['price'] === 0 ? 'GRATIS' : rupiah($product['price']) ?></div>
      <?php if ($owned): ?>
        <a href="<?= e(!empty($product['access_slug']) ? url('access', ['slug' => $product['access_slug']]) : url('learn', ['product' => $product['id']])) ?>" class="btn btn-primary btn-lg btn-block"><?= !empty($product['access_slug']) ? 'Buka Akses Produk' : 'Buka Akses Kelas' ?></a>
      <?php elseif (!empty($seat_stats['is_full'])): ?>
        <button class="btn btn-ghost btn-lg btn-block" type="button" disabled>Kuota Sudah Penuh</button>
      <?php else: ?>
        <a href="<?= e(url('checkout', ['slug' => $product['slug']])) ?>" class="btn btn-gold btn-lg btn-block">Pesan Sekarang</a>
      <?php endif; ?>
      <?php if (!empty($product['event_maps_url'])): ?>
        <a href="<?= e($product['event_maps_url']) ?>" target="_blank" rel="noopener" class="btn btn-ghost btn-block">Lihat Lokasi / Maps</a>
      <?php endif; ?>
    </div>
  </div>

  <?php if (!empty($product['event_notes'])): ?>
    <div class="pd-desc"><h2>Info Event</h2><div class="prose"><?= nl2br(e($product['event_notes'])) ?></div></div>
  <?php endif; ?>

  <?php if ($product['long_desc']): ?>
    <div class="pd-desc"><h2>Tentang Kelas Ini</h2><div class="prose"><?= nl2br(e($product['long_desc'])) ?></div></div>
  <?php endif; ?>

  <div class="pd-lessons">
    <h2>Daftar Materi</h2>
    <?php if (!$lessons): ?>
      <div class="empty small"><p>Materi akan muncul di sini setelah ditambahkan admin.</p></div>
    <?php else: ?>
      <ol class="lesson-list">
        <?php foreach ($lessons as $i => $l): ?>
          <li class="lesson-row">
            <span class="lnum"><?= $i + 1 ?></span>
            <div class="linfo"><strong><?= e($l['title']) ?></strong>
              <?php if ($l['short_desc']): ?><span class="muted"><?= e($l['short_desc']) ?></span><?php endif; ?></div>
            <span class="llock"><?= $owned ? '▶' : '🔒' ?></span>
          </li>
        <?php endforeach; ?>
      </ol>
      <?php if (!$owned): ?><p class="muted center">🔒 Materi terkunci. Beli untuk membuka seluruh kelas.</p><?php endif; ?>
    <?php endif; ?>
  </div>
</section>
