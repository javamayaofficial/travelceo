<?php
$errors = $errors ?? [];
$otpPhone = $otp_phone ?? '';
$showOtpVerify = !empty($show_otp_verify);
$googleClientId = $google_client_id ?? '';
$siteName = setting('site_name', 'The Travel CEO');
?>
<section class="auth-clean">
  <div class="auth-clean-brand">
    <?php if ($logo = setting('logo')): ?>
      <img src="<?= e(base_url($logo)) ?>" alt="<?= e($siteName) ?>" class="auth-clean-logo">
    <?php else: ?>
      <span class="auth-clean-mark">✈</span>
    <?php endif; ?>
    <strong><?= e($siteName) ?></strong>
  </div>
  <div class="auth-clean-card">
    <h1>Masuk</h1>

    <?php if ($errors): ?><div class="flash flash-err"><span>⚠️</span><div><?= e($errors[0]) ?></div></div><?php endif; ?>

    <form method="post" action="<?= e(url('login-otp-request')) ?>" class="form auth-clean-form">
      <?= csrf_field() ?>
      <label class="field"><span>Nomor WhatsApp</span>
        <input type="tel" name="phone" value="<?= e($otpPhone) ?>" required inputmode="tel" placeholder="08xxxxxxxxxx"></label>
      <button class="btn btn-otp btn-lg btn-block">Kirim Kode OTP</button>
    </form>

    <?php if ($showOtpVerify): ?>
      <form method="post" action="<?= e(url('login-otp-verify')) ?>" class="form auth-clean-form auth-otp-verify">
        <?= csrf_field() ?>
        <input type="hidden" name="phone" value="<?= e($otpPhone) ?>">
        <label class="field"><span>Kode OTP</span>
          <input type="text" name="otp_code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required placeholder="6 digit kode OTP"></label>
        <button class="btn btn-primary btn-lg btn-block">Verifikasi &amp; Masuk</button>
      </form>
    <?php endif; ?>

    <?php if ($googleClientId !== ''): ?>
      <div class="auth-clean-divider"><span>ATAU</span></div>
      <form method="post" action="<?= e(url('login-google')) ?>" id="google-login-form" class="auth-google-form">
        <?= csrf_field() ?>
        <input type="hidden" name="credential" id="google-login-credential">
        <div id="google-login-button"></div>
      </form>
      <script src="https://accounts.google.com/gsi/client" async defer></script>
      <script>
      window.addEventListener('load', function () {
        if (!window.google || !google.accounts || !google.accounts.id) return;
        google.accounts.id.initialize({
          client_id: <?= json_encode($googleClientId) ?>,
          callback: function (response) {
            var field = document.getElementById('google-login-credential');
            var form = document.getElementById('google-login-form');
            if (!field || !form || !response || !response.credential) return;
            field.value = response.credential;
            form.submit();
          }
        });
        var target = document.getElementById('google-login-button');
        if (target) {
          google.accounts.id.renderButton(target, {
            theme: 'outline',
            size: 'large',
            text: 'signin_with',
            shape: 'pill',
            width: 410
          });
        }
      });
      </script>
    <?php endif; ?>

    <div class="auth-foot">
      <a href="<?= e(url('forgot')) ?>">Lupa Password</a>
    </div>
  </div>
</section>
