<?php /** vars: $code, $product, $total, $account_created */ ?>
<section class="container narrow">
  <div class="success-box">
    <div class="success-ic">🎉</div>
    <h1>Pesanan Diterima!</h1>
    <p>Terima kasih. Pesanan <strong><?= e($code) ?></strong> untuk <strong><?= e($product['title']) ?></strong> sebesar <strong><?= rupiah($total) ?></strong> sedang menunggu verifikasi admin.</p>
    <?php if (!empty($account_created)): ?>
      <p class="muted">Akun member Anda sudah dibuat otomatis. Info akses login sudah dikirim ke WhatsApp dan email, dan Anda bisa masuk memakai OTP WhatsApp atau tombol Google dengan email yang terdaftar.</p>
    <?php endif; ?>
    <p class="muted">Anda juga akan menerima notifikasi status pesanan melalui WhatsApp dan email.</p>
    <div class="success-actions">
      <a href="<?= e(url('dashboard')) ?>" class="btn btn-primary btn-lg">Ke Dashboard Saya</a>
      <?php if ($wa = setting('site_wa')): ?>
        <a href="https://wa.me/<?= e(wa_normalize($wa)) ?>?text=<?= rawurlencode('Halo admin, saya sudah transfer untuk order ' . $code) ?>" target="_blank" rel="noopener" class="btn btn-ghost btn-lg">Konfirmasi via WhatsApp</a>
      <?php endif; ?>
    </div>
  </div>
</section>
