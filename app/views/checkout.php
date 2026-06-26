<?php /** app/views/checkout.php — vars: $product, $payment_methods, $gateway_options, $errors, $old, $buyer, $is_logged_in */
$old = $old ?? [];
$errors = $errors ?? [];
$buyer = $buyer ?? null;
$isLoggedIn = !empty($is_logged_in);
$paymentMethods = $payment_methods ?? [];
$gatewayOptions = $gateway_options ?? [];
$seatStats = $seat_stats ?? ['quota' => 0, 'approved' => 0, 'remaining' => null, 'is_full' => false];
?>
<section class="container narrow checkout">
  <a href="<?= e(url('product', ['slug' => $product['slug']])) ?>" class="back-link">← Kembali</a>
  <h1>Checkout</h1>

  <div class="order-summary">
    <div class="os-row"><span>Produk</span><strong><?= e($product['title']) ?></strong></div>
    <div class="os-row"><span>Harga</span><strong><?= rupiah($product['price']) ?></strong></div>
    <?php if (!empty($product['event_start_at'])): ?><div class="os-row"><span>Jadwal</span><strong><?= e(date('d M Y, H:i', strtotime($product['event_start_at']))) ?> WIB</strong></div><?php endif; ?>
    <?php if (!empty($product['event_location']) || !empty($product['event_city'])): ?><div class="os-row"><span>Lokasi</span><strong><?= e(trim(($product['event_location'] ?? '') . (!empty($product['event_city']) ? ', ' . $product['event_city'] : ''), ', ')) ?></strong></div><?php endif; ?>
    <?php if (!empty($seatStats['quota'])): ?><div class="os-row"><span>Seat Tersisa</span><strong><?= (int)$seatStats['remaining'] ?> dari <?= (int)$seatStats['quota'] ?></strong></div><?php endif; ?>
  </div>

  <?php if ($errors): ?>
    <div class="flash flash-err"><span>⚠️</span><div><ul class="err-list"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div></div>
  <?php endif; ?>

  <form method="post" action="<?= e(url('checkout-process')) ?>" class="card form">
    <?= csrf_field() ?>
    <input type="hidden" name="slug" value="<?= e($product['slug']) ?>">

    <?php if ($isLoggedIn && $buyer): ?>
      <div class="checkout-account-box">
        <strong><?= e($buyer['name']) ?></strong>
        <span class="muted"><?= e($buyer['email']) ?></span>
        <span class="muted"><?= e($buyer['wa'] ? wa_mask($buyer['wa']) : 'Nomor WhatsApp belum diisi') ?></span>
      </div>
    <?php else: ?>
      <div class="checkout-intro">
        <strong>Data pembeli</strong>
        <p class="muted">Isi data pembeli sekali di sini. Untuk produk membership, akses login baru dikirim setelah pembelian disetujui admin.</p>
      </div>

      <label class="field">
        <span>Nama Lengkap</span>
        <input type="text" name="buyer_name" value="<?= e($old['buyer_name'] ?? '') ?>" placeholder="Nama sesuai rekening / identitas" autocomplete="name" required>
      </label>

      <label class="field">
        <span>Email</span>
        <input type="email" name="buyer_email" value="<?= e($old['buyer_email'] ?? '') ?>" placeholder="nama@email.com" autocomplete="email" required>
      </label>

      <label class="field">
        <span>Nomor WhatsApp</span>
        <input type="tel" name="buyer_wa" value="<?= e($old['buyer_wa'] ?? '') ?>" placeholder="08xxxxxxxxxx" autocomplete="tel" required>
        <small class="hint">Notifikasi pesanan dan pembaruan status akan dikirim ke nomor ini.</small>
      </label>
    <?php endif; ?>

    <label class="field">
      <span>Kode Kupon (opsional)</span>
      <input type="text" name="coupon" value="<?= e($old['coupon'] ?? '') ?>" placeholder="Contoh: HEMAT10" autocomplete="off">
      <small class="hint">Punya kode dari affiliate? Masukkan di sini untuk dapat diskon.</small>
    </label>

    <fieldset class="field">
      <span>Pilih Metode Pembayaran</span>
      <?php if (!$paymentMethods): ?>
        <div class="empty small"><p>Metode pembayaran belum diatur admin.</p></div>
      <?php else: ?>
      <div class="bank-options">
        <?php foreach ($paymentMethods as $method): ?>
          <label class="bank-opt">
            <input type="radio" name="bank" value="<?= e($method['key']) ?>" <?= (($old['bank'] ?? '') === $method['key']) ? 'checked' : '' ?>>
            <span class="bank-card">
              <strong><?= e($method['label']) ?></strong>
              <small class="muted"><?= e($method['detail']) ?></small>
              <?php if (($method['type'] ?? '') === 'qris' && !empty($method['image'])): ?>
                <img class="qris-thumb" src="<?= e(public_asset_url($method['image'])) ?>" alt="<?= e($method['label']) ?>">
              <?php endif; ?>
              <?php if (($method['type'] ?? '') === 'gateway' && (($method['gateway'] ?? '') === 'duitku') && empty($method['is_ready'])): ?>
                <small class="muted">Konfigurasi Duitku belum lengkap di admin.</small>
              <?php endif; ?>
            </span>
          </label>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </fieldset>

    <?php if ($gatewayOptions): ?>
      <div class="field">
        <span>Payment Gateway Yang Ditampilkan</span>
        <div class="gateway-pills">
          <?php foreach ($gatewayOptions as $gateway): ?>
            <span class="gateway-pill"><?= e($gateway['label']) ?></span>
          <?php endforeach; ?>
        </div>
        <small class="hint">Opsi gateway ditampilkan sesuai centang admin dan bisa dipakai untuk tahap integrasi berikutnya.</small>
      </div>
    <?php endif; ?>

    <label class="field">
      <span>Catatan (opsional)</span>
      <textarea name="note" rows="2" placeholder="Misal: transfer atas nama ..."><?= e($old['note'] ?? '') ?></textarea>
    </label>

    <?php if (!empty($seatStats['is_full'])): ?>
      <button type="button" class="btn btn-ghost btn-lg btn-block" disabled>Kuota Sudah Penuh</button>
      <p class="hint center">Kuota workshop ini sudah penuh. Silakan hubungi admin jika ingin masuk waiting list.</p>
    <?php else: ?>
      <button type="submit" class="btn btn-gold btn-lg btn-block">Kirim &amp; Konfirmasi Pesanan</button>
      <p class="hint center">Setelah dikirim, pesanan akan diproses sesuai metode pembayaran yang Anda pilih.</p>
    <?php endif; ?>
  </form>
</section>
