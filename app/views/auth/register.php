<?php /** vars: $errors, $old */ $errors = $errors ?? []; $old = $old ?? []; ?>
<section class="auth-wrap">
  <div class="auth-card card">
    <h1>Daftar Member</h1>
    <p class="muted">Mulai perjalanan Anda menjadi Travel CEO.</p>
    <?php if ($errors): ?>
      <div class="flash flash-err"><span>⚠️</span><div><ul class="err-list"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div></div>
    <?php endif; ?>
    <form method="post" action="<?= e(url('register')) ?>" class="form">
      <?= csrf_field() ?>
      <label class="field"><span>Nama Lengkap</span>
        <input type="text" name="name" value="<?= e($old['name'] ?? '') ?>" required placeholder="Nama Anda"></label>
      <label class="field"><span>Email</span>
        <input type="email" name="email" value="<?= e($old['email'] ?? '') ?>" required placeholder="nama@email.com"></label>
      <label class="field"><span>Nomor WhatsApp</span>
        <input type="text" name="wa" value="<?= e($old['wa'] ?? '') ?>" required placeholder="08xxxxxxxxxx">
        <small class="hint">Notifikasi penting akan dikirim ke WhatsApp ini.</small></label>
      <label class="field"><span>Password</span>
        <input type="password" name="password" required minlength="8" placeholder="Minimal 8 karakter"></label>
      <button class="btn btn-gold btn-lg btn-block">Buat Akun Saya</button>
    </form>
    <div class="auth-foot"><span>Sudah punya akun? <a href="<?= e(login_member_url()) ?>">Masuk</a></span></div>
  </div>
</section>
