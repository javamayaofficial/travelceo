<?php
$errors = $errors ?? [];
$email = $email ?? '';
$otpPhone = $otp_phone ?? '';
$showOtpVerify = !empty($show_otp_verify);
?>
<section class="auth-wrap">
  <div class="auth-card card">
    <h1>Admin Panel</h1>
    <?php if ($errors): ?><div class="flash flash-err"><span>⚠️</span><div><?= e($errors[0]) ?></div></div><?php endif; ?>
    <form method="post" action="<?= e(login_panel_url()) ?>" class="form">
      <?= csrf_field() ?>
      <label class="field"><span>Email Admin</span>
        <input type="email" name="email" value="<?= e($email) ?>" required autocomplete="email" placeholder="admin@email.com"></label>
      <label class="field"><span>Password Admin</span>
        <input type="password" name="password" required autocomplete="current-password" placeholder="••••••••"></label>
      <button class="btn btn-primary btn-lg btn-block">Masuk</button>
    </form>
    <div class="auth-clean-divider"><span>ATAU</span></div>
    <form method="post" action="<?= e(url('login-panel-otp-request')) ?>" class="form">
      <?= csrf_field() ?>
      <label class="field"><span>WhatsApp Admin</span>
        <input type="tel" name="phone" value="<?= e($otpPhone) ?>" required inputmode="tel" placeholder="08xxxxxxxxxx"></label>
      <button class="btn btn-otp btn-lg btn-block">Kirim OTP Admin</button>
    </form>
    <?php if ($showOtpVerify): ?>
      <form method="post" action="<?= e(url('login-panel-otp-verify')) ?>" class="form auth-otp-verify">
        <?= csrf_field() ?>
        <input type="hidden" name="phone" value="<?= e($otpPhone) ?>">
        <label class="field"><span>Kode OTP</span>
          <input type="text" name="otp_code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required placeholder="6 digit kode OTP"></label>
        <button class="btn btn-primary btn-lg btn-block">Verifikasi &amp; Masuk</button>
      </form>
    <?php endif; ?>
    <div class="auth-foot">
      <a href="<?= e(login_member_url()) ?>">Masuk sebagai Member</a>
    </div>
  </div>
</section>
