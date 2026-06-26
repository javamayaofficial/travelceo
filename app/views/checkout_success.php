<?php /** vars: $code, $product, $total, $account_created, $payment_method, $payment_status, $payment_url */ ?>
<section class="container narrow">
  <div class="success-box">
    <div class="success-ic">🎉</div>
    <h1><?= (($payment_status ?? 'pending') === 'approved') ? 'Pembayaran Berhasil!' : 'Pesanan Diterima!' ?></h1>
    <p>
      Terima kasih. Pesanan <strong><?= e($code) ?></strong> untuk <strong><?= e($product['title']) ?></strong> sebesar <strong><?= rupiah($total) ?></strong>
      <?= (($payment_status ?? 'pending') === 'approved') ? 'telah kami terima dan pembayaran Anda sudah terverifikasi.' : 'sedang kami proses.' ?>
    </p>
    <?php if (!empty($account_created)): ?>
      <p class="muted">Data Anda sudah tersimpan. Akses login akan dikirim setelah disetujui Admin.</p>
    <?php endif; ?>
    <?php if (!empty($payment_method) && (($payment_method['type'] ?? '') === 'bank')): ?>
      <div class="card" style="margin-top:14px">
        <div class="card-body">
          <p>Silakan melakukan pembayaran ke rekening berikut:</p>
          <div class="copyline"><strong><?= e($payment_method['detail'] ?? '') ?></strong></div>
          <p class="muted" style="margin-top:10px">Bank tujuan: <strong><?= e($payment_method['label'] ?? '') ?></strong><br>Nominal pembayaran: <strong><?= rupiah($total) ?></strong><br>Kode referensi: <strong><?= e($code) ?></strong></p>
        </div>
      </div>
    <?php endif; ?>
    <?php if (!empty($payment_method) && (($payment_method['type'] ?? '') === 'qris') && !empty($payment_method['image'])): ?>
      <div class="card" style="margin-top:14px">
        <div class="card-body">
          <p>Silakan melakukan pembayaran dengan memindai QR berikut.</p>
          <p class="muted"><?= e($payment_method['detail'] ?? '') ?></p>
          <p><img src="<?= e(public_asset_url($payment_method['image'])) ?>" alt="<?= e($payment_method['label'] ?? 'QRIS') ?>" style="max-width:280px;width:100%;height:auto;border-radius:12px"></p>
          <p class="muted">Nominal pembayaran: <strong><?= rupiah($total) ?></strong><br>Kode referensi: <strong><?= e($code) ?></strong></p>
        </div>
      </div>
    <?php endif; ?>
    <?php if (!empty($payment_method) && (($payment_method['gateway'] ?? '') === 'duitku')): ?>
      <div class="card" style="margin-top:14px">
        <div class="card-body">
          <p>Pembayaran Anda diproses melalui <strong>Duitku</strong>.</p>
          <?php if (!empty($payment_url) && (($payment_status ?? 'pending') !== 'approved')): ?>
            <p class="muted">Jika halaman pembayaran belum terbuka atau Anda ingin melanjutkan kembali, gunakan tombol di bawah ini.</p>
            <p><a href="<?= e($payment_url) ?>" class="btn btn-primary" target="_blank" rel="noopener">Lanjutkan Pembayaran Duitku</a></p>
          <?php endif; ?>
        </div>
      </div>
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
