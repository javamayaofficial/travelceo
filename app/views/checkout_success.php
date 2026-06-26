<?php /** vars: $code, $product, $total, $account_created, $payment_method */ ?>
<section class="container narrow">
  <div class="success-box">
    <div class="success-ic">🎉</div>
    <h1>Pesanan Diterima!</h1>
    <p>Terima kasih. Pesanan <strong><?= e($code) ?></strong> untuk <strong><?= e($product['title']) ?></strong> sebesar <strong><?= rupiah($total) ?></strong> sedang menunggu verifikasi admin.</p>
    <?php if (!empty($account_created)): ?>
      <p class="muted">Data Anda sudah tersimpan. Akses login akan dikirim setelah disetujui Admin.</p>
    <?php endif; ?>
    <?php if (!empty($payment_method) && (($payment_method['type'] ?? '') === 'bank')): ?>
      <div class="card" style="margin-top:14px">
        <div class="card-head"><strong>Instruksi Transfer</strong></div>
        <div class="card-body">
          <p>Silakan transfer ke rekening <strong><?= e($payment_method['label'] ?? '') ?></strong>:</p>
          <div class="copyline"><strong><?= e($payment_method['detail'] ?? '') ?></strong></div>
          <p class="muted" style="margin-top:10px">Nominal transfer: <strong><?= rupiah($total) ?></strong>. Setelah transfer, klik tombol konfirmasi WhatsApp di bawah.</p>
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
