<?php /** vars: $errors, $email */ $errors = $errors ?? []; $email = $email ?? ''; ?>
<section class="auth-wrap">
  <div class="auth-card card">
    <h1>Lupa Password</h1>
    <p class="muted">Masukkan email akun Anda. Kami akan kirim link untuk membuat password baru.</p>
    <?php if ($errors): ?><div class="flash flash-err"><span>⚠️</span><div><?= e($errors[0]) ?></div></div><?php endif; ?>
    <form method="post" action="<?= e(url('forgot')) ?>" class="form">
      <?= csrf_field() ?>
      <label class="field"><span>Email</span>
        <input type="email" name="email" value="<?= e($email) ?>" required autocomplete="email" placeholder="nama@email.com"></label>
      <button class="btn btn-primary btn-lg btn-block">Kirim Link Reset Password</button>
      <small class="hint">Link reset berlaku 30 menit dan hanya bisa dipakai satu kali.</small>
    </form>
    <div class="auth-foot"><a href="<?= e(login_member_url()) ?>">← Kembali ke Masuk</a></div>
  </div>
</section>
