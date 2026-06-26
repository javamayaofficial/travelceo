<?php
/** vars: $errors, $token, $reset */
$errors = $errors ?? [];
$token = $token ?? '';
$reset = $reset ?? null;
?>
<section class="auth-wrap">
  <div class="auth-card card">
    <h1>Reset Password</h1>
    <p class="muted">Buat password baru untuk akun Anda.</p>
    <?php if ($errors): ?><div class="flash flash-err"><span>⚠️</span><div><?= e($errors[0]) ?></div></div><?php endif; ?>
    <?php if ($reset && !$errors): ?>
      <p class="hint" style="margin-bottom:14px">Akun: <strong><?= e($reset['uemail']) ?></strong></p>
    <?php endif; ?>
    <?php if ($reset): ?>
      <form method="post" action="<?= e(url('reset-password')) ?>" class="form">
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <label class="field"><span>Password Baru</span>
          <input type="password" name="password" required minlength="8" autocomplete="new-password" placeholder="Minimal 8 karakter"></label>
        <label class="field"><span>Konfirmasi Password Baru</span>
          <input type="password" name="password_confirm" required minlength="8" autocomplete="new-password" placeholder="Ulangi password baru"></label>
        <button class="btn btn-primary btn-lg btn-block">Simpan Password Baru</button>
      </form>
    <?php else: ?>
      <div class="auth-foot"><a href="<?= e(url('forgot')) ?>">Minta link reset baru</a></div>
    <?php endif; ?>
    <div class="auth-foot"><a href="<?= e(login_member_url()) ?>">← Kembali ke Masuk</a></div>
  </div>
</section>
