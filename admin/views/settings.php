<?php
/** admin/views/settings.php */
if (!function_exists("s")) { function s($k, $d=''){ return e(setting($k, $d)); } }
$bankSuggestions = ['BCA','Mandiri','BRI','BNI','BSI','CIMB Niaga','Permata','Danamon','BTN','OCBC NISP','Maybank','SeaBank','Jago','Neo Commerce'];
$duitkuMerchantCode = strtoupper(preg_replace('/\s+/', '', trim((string)setting('duitku_merchant_code', ''))));
$duitkuSandbox = setting('duitku_sandbox', '1') === '1';
$duitkuEnvironmentLabel = $duitkuSandbox ? 'Sandbox' : 'Production';
$duitkuStatusNote = '';
if ($duitkuMerchantCode === '') {
  $duitkuStatusNote = 'Merchant Code Duitku belum diisi.';
} elseif (!$duitkuSandbox && $duitkuMerchantCode !== 'D19346') {
  $duitkuStatusNote = 'Mode Production untuk project ini wajib memakai Merchant Code D19346.';
} elseif (!$duitkuSandbox && $duitkuMerchantCode === 'D19346') {
  $duitkuStatusNote = 'Konfigurasi Production sudah mengarah ke Merchant Code D19346.';
}
$legacyBanks = [
  1 => ['name' => 'BCA', 'account' => setting('bank_bca', '')],
  2 => ['name' => 'Mandiri', 'account' => setting('bank_mandiri', '')],
  3 => ['name' => 'BSI', 'account' => setting('bank_bsi', '')],
  4 => ['name' => 'BRI', 'account' => ''],
  5 => ['name' => 'BNI', 'account' => ''],
  6 => ['name' => 'CIMB Niaga', 'account' => ''],
];
?>
<div class="page-title"><h1>Pengaturan Website</h1><p class="muted">Atur identitas, rekening, WhatsApp, dan integrasi.</p></div>

<form method="post" enctype="multipart/form-data" class="form settings-form">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="save_settings">

  <div class="card admin-card">
    <h2>Identitas Website</h2>
    <div class="form-row">
      <label class="field"><span>Nama Website</span><input type="text" name="site_name" value="<?= s('site_name','The Travel CEO') ?>"></label>
      <label class="field"><span>Email</span><input type="email" name="site_email" value="<?= s('site_email') ?>"></label>
    </div>
    <div class="form-row">
      <label class="field"><span>Nomor WhatsApp (admin)</span><input type="text" name="site_wa" value="<?= s('site_wa') ?>" placeholder="08xxxxxxxxxx"></label>
      <label class="field"><span>Alamat</span><input type="text" name="site_address" value="<?= s('site_address') ?>"></label>
    </div>
    <div class="form-row">
      <label class="field"><span>Logo</span><input type="file" name="logo" accept=".png,.jpg,.jpeg,.webp"><?php if (setting('logo')): ?><small class="hint">✓ Sudah ada logo</small><?php endif; ?></label>
      <label class="field"><span>Favicon</span><input type="file" name="favicon" accept=".png,.ico,.jpg,.jpeg"><?php if (setting('favicon')): ?><small class="hint">✓ Sudah ada favicon</small><?php endif; ?></label>
    </div>
  </div>

  <div class="card admin-card">
    <h2>Rekening Pembayaran</h2>
    <p class="muted">Nama bank bisa diisi manual dan tiap metode bisa dicentang agar tampil atau disembunyikan di checkout.</p>
    <datalist id="bank-suggestions">
      <?php foreach ($bankSuggestions as $bankName): ?>
        <option value="<?= e($bankName) ?>"></option>
      <?php endforeach; ?>
    </datalist>
    <?php for ($i = 1; $i <= 6; $i++): ?>
      <?php
        $legacy = $legacyBanks[$i] ?? ['name' => '', 'account' => ''];
        $bankName = setting('checkout_bank_' . $i . '_name', $legacy['name']);
        $bankAccount = setting('checkout_bank_' . $i . '_account', $legacy['account']);
        $bankEnabled = setting('checkout_bank_' . $i . '_enabled', $i <= 3 ? '1' : '0') === '1';
      ?>
      <div class="card admin-card" style="margin-top:14px">
        <label class="check"><input type="checkbox" name="checkout_bank_<?= $i ?>_enabled" value="1" <?= $bankEnabled ? 'checked' : '' ?>> Tampilkan Bank Slot <?= $i ?> di checkout</label>
        <div class="form-row">
          <label class="field"><span>Nama Bank</span><input type="text" name="checkout_bank_<?= $i ?>_name" value="<?= e($bankName) ?>" list="bank-suggestions" placeholder="Pilih / ketik nama bank"></label>
          <label class="field"><span>Nomor Rekening / Keterangan</span><input type="text" name="checkout_bank_<?= $i ?>_account" value="<?= e($bankAccount) ?>" placeholder="1234567890 a.n. Nama / info transfer"></label>
        </div>
      </div>
    <?php endfor; ?>
  </div>

  <div class="card admin-card">
    <h2>Pembayaran QRIS</h2>
    <label class="check"><input type="checkbox" name="checkout_qris_enabled" value="1" <?= setting('checkout_qris_enabled', '0') === '1' ? 'checked' : '' ?>> Tampilkan QRIS di checkout</label>
    <div class="form-row">
      <label class="field"><span>Label QRIS</span><input type="text" name="checkout_qris_label" value="<?= s('checkout_qris_label', 'QRIS') ?>" placeholder="QRIS"></label>
      <label class="field"><span>Catatan QRIS</span><input type="text" name="checkout_qris_note" value="<?= s('checkout_qris_note', 'Scan QR code berikut untuk melakukan pembayaran via QRIS.') ?>" placeholder="Catatan singkat QRIS"></label>
    </div>
    <label class="field"><span>Upload QR Code</span><input type="file" name="checkout_qris_image" accept=".png,.jpg,.jpeg,.webp"><?php if (setting('checkout_qris_image')): ?><small class="hint">✓ QRIS sudah ada</small><?php endif; ?></label>
  </div>

  <div class="card admin-card">
    <h2>Payment Gateway</h2>
    <p class="muted">Centang gateway yang ingin dimunculkan di checkout. Duitku sudah bisa dipakai langsung jika kredensialnya diisi.</p>
    <p class="hint">Mode aktif saat ini: <strong><?= e($duitkuEnvironmentLabel) ?></strong><?= $duitkuMerchantCode !== '' ? ' | Merchant: <strong>' . e($duitkuMerchantCode) . '</strong>' : '' ?></p>
    <p class="hint">Untuk project ini, kredensial Production harus memakai Merchant Code <strong>D19346</strong> dan opsi Sandbox harus dimatikan.</p>
    <p class="hint">Project Duitku yang masih <strong>Inactive</strong> tidak bisa dipakai membuat invoice.</p>
    <?php if ($duitkuStatusNote !== ''): ?>
      <p class="hint"><?= e($duitkuStatusNote) ?></p>
    <?php endif; ?>
    <div class="form-row">
      <label class="check"><input type="checkbox" name="checkout_gateway_xendit" value="1" <?= setting('checkout_gateway_xendit', '0') === '1' ? 'checked' : '' ?>> Tampilkan Xendit</label>
      <label class="check"><input type="checkbox" name="checkout_gateway_duitku" value="1" <?= setting('checkout_gateway_duitku', '0') === '1' ? 'checked' : '' ?>> Tampilkan Duitku</label>
    </div>
    <div class="form-row">
      <label class="check"><input type="checkbox" name="checkout_gateway_midtrans" value="1" <?= setting('checkout_gateway_midtrans', '0') === '1' ? 'checked' : '' ?>> Tampilkan Midtrans</label>
      <label class="check"><input type="checkbox" name="checkout_gateway_tripay" value="1" <?= setting('checkout_gateway_tripay', '0') === '1' ? 'checked' : '' ?>> Tampilkan Tripay</label>
    </div>
    <div class="form-row">
      <label class="field"><span>Duitku Merchant Code</span><input type="text" name="duitku_merchant_code" value="<?= s('duitku_merchant_code') ?>" placeholder="DXXXX"></label>
      <label class="field"><span>Duitku API Key</span><input type="text" name="duitku_api_key" value="<?= s('duitku_api_key') ?>" placeholder="Masukkan API Key Duitku"></label>
    </div>
    <div class="form-row">
      <label class="check"><input type="checkbox" name="duitku_sandbox" value="1" <?= setting('duitku_sandbox', '1') === '1' ? 'checked' : '' ?>> Gunakan Sandbox Duitku</label>
      <label class="field"><span>Expiry Duitku (menit)</span><input type="number" name="duitku_expiry_period" value="<?= s('duitku_expiry_period', '60') ?>" min="5" max="1440"></label>
    </div>
  </div>

  <div class="card admin-card">
    <h2>Integrasi WhatsApp (Fonnte)</h2>
    <label class="field"><span>Token Fonnte</span><input type="text" name="fonnte_token" value="<?= s('fonnte_token') ?>" placeholder="Tempel token dari fonnte.com"><small class="hint">Runtime akan memprioritaskan token di <code>config.php</code>. Field ini hanya fallback.</small></label>
    <label class="field"><span>Komisi Affiliate (%)</span><input type="number" name="commission_percent" value="<?= s('commission_percent','10') ?>" min="0" max="100"></label>
    <h3 class="sub">Template Pesan WhatsApp</h3>
    <small class="hint">Variabel tersedia: {nama}, {produk}, {kode}, {total}</small>
    <label class="field"><span>Saat Registrasi</span><textarea name="wa_register" rows="2"><?= s('wa_register') ?></textarea></label>
    <label class="field"><span>Saat Pembelian</span><textarea name="wa_purchase" rows="2"><?= s('wa_purchase') ?></textarea></label>
    <label class="field"><span>Saat Disetujui</span><textarea name="wa_approved" rows="2"><?= s('wa_approved') ?></textarea></label>
    <label class="field"><span>Saat Ditolak</span><textarea name="wa_rejected" rows="2"><?= s('wa_rejected') ?></textarea></label>
  </div>

  <div class="card admin-card">
    <h2>Integrasi Email (Mailketing)</h2>
    <label class="field"><span>Token Mailketing</span><input type="text" name="mailketing_token" value="<?= s('mailketing_token') ?>" placeholder="Tempel token dari mailketing.co.id"><small class="hint">Jika token di <code>config.php</code> kosong, sistem akan memakai nilai dari sini.</small></label>
    <label class="field"><span>Email Pengirim</span><input type="email" name="mail_sender" value="<?= s('mail_sender', s('site_email')) ?>" placeholder="admin@domainanda.com"><small class="hint">Gunakan email sender yang sudah diverifikasi di Mailketing.</small></label>
  </div>

  <div class="card admin-card">
    <h2>Login Google Member</h2>
    <label class="field"><span>Google Client ID</span><input type="text" name="google_client_id" value="<?= s('google_client_id') ?>" placeholder="1234567890-xxxx.apps.googleusercontent.com"><small class="hint">Dipakai untuk tombol login Google di halaman member. Hanya email yang sudah terdaftar di sistem yang bisa masuk.</small></label>
  </div>

  <div class="card admin-card">
    <h2>SEO &amp; Pelacakan</h2>
    <div class="form-row">
      <label class="field"><span>SEO Title Homepage</span><input type="text" name="seo_title" value="<?= s('seo_title') ?>"></label>
      <label class="field"><span>SEO Description Homepage</span><input type="text" name="seo_desc" value="<?= s('seo_desc') ?>"></label>
    </div>
    <div class="form-row">
      <label class="field"><span>Google Analytics ID</span><input type="text" name="google_analytics" value="<?= s('google_analytics') ?>" placeholder="G-XXXXXXX"></label>
      <label class="field"><span>Facebook Pixel ID</span><input type="text" name="facebook_pixel" value="<?= s('facebook_pixel') ?>"></label>
    </div>
  </div>

  <div class="card admin-card">
    <h2>Link Tombol Homepage</h2>
    <p class="muted">Semua CTA utama di homepage bisa diarahkan manual dari sini. Boleh isi URL penuh seperti <code>https://...</code> atau path lokal seperti <code>?p=products</code>.</p>
    <div class="form-row">
      <label class="field"><span>Tombol Hero Utama</span><input type="text" name="home_link_apply" value="<?= s('home_link_apply') ?>" placeholder="https://wa.me/... atau ?p=register"></label>
      <label class="field"><span>Tombol Hero Kedua</span><input type="text" name="home_link_programs" value="<?= s('home_link_programs') ?>" placeholder="?p=products"></label>
    </div>
    <div class="form-row">
      <label class="field"><span>Tombol Konsultasi Seat</span><input type="text" name="home_link_consult" value="<?= s('home_link_consult') ?>" placeholder="https://wa.me/..."></label>
      <label class="field"><span>Link Semua Produk</span><input type="text" name="home_link_products_more" value="<?= s('home_link_products_more') ?>" placeholder="?p=products"></label>
    </div>
    <div class="form-row">
      <label class="field"><span>Link Semua Blog</span><input type="text" name="home_link_blog_more" value="<?= s('home_link_blog_more') ?>" placeholder="?p=blog"></label>
      <label class="field"><span>Tombol Featured Salespage</span><input type="text" name="home_link_featured_sales" value="<?= s('home_link_featured_sales') ?>" placeholder="?p=sales&amp;slug=nama-salespage"></label>
    </div>
    <div class="form-row">
      <label class="field"><span>Tombol CTA Bawah Utama</span><input type="text" name="home_link_final_apply" value="<?= s('home_link_final_apply') ?>" placeholder="https://wa.me/..."></label>
      <label class="field"><span>Tombol CTA Bawah Kedua</span><input type="text" name="home_link_final_register" value="<?= s('home_link_final_register') ?>" placeholder="?p=register"></label>
    </div>
  </div>

  <div class="settings-save">
    <button class="btn btn-primary btn-lg">Simpan Pengaturan</button>
  </div>
</form>

<div class="card admin-card">
  <h2>Backup Database</h2>
  <p class="muted">Unduh cadangan seluruh data Anda (format .sql). Lakukan secara berkala.</p>
  <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="backup_db">
    <button class="btn btn-ghost">⬇ Unduh Backup Sekarang</button></form>
  <p class="hint">Untuk restore: buka phpMyAdmin → pilih database → Import → pilih file backup → Go.</p>
</div>
