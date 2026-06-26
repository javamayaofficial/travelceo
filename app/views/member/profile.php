<?php /** vars: $u */ ?>
<section class="container narrow dash">
  <h1>Profil Saya</h1>
  <form method="post" action="<?= e(url('profile')) ?>" class="card form">
    <?= csrf_field() ?>
    <label class="field"><span>Nama Lengkap</span><input type="text" name="name" value="<?= e($u['name']) ?>" required></label>
    <label class="field"><span>Email</span><input type="email" value="<?= e($u['email']) ?>" disabled></label>
    <label class="field"><span>Nomor WhatsApp</span><input type="text" name="wa" value="<?= e($u['wa']) ?>"></label>
    <label class="field"><span>Password Lama</span><input type="password" name="current_password" autocomplete="current-password" placeholder="Isi jika ingin ganti password"></label>
    <label class="field"><span>Password Baru (kosongkan jika tidak diubah)</span><input type="password" name="password" minlength="8" placeholder="Minimal 8 karakter"></label>
    <button class="btn btn-primary btn-lg btn-block">Simpan Perubahan</button>
  </form>
</section>
