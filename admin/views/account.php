<?php $admin = current_user(); ?>
<section class="admin-card card">
  <h1>Akun Admin</h1>
  <form method="post" action="<?= e(admin_url('account')) ?>" class="form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="update_admin_password">
    <label class="field"><span>Email Admin</span>
      <input type="email" value="<?= e($admin['email'] ?? '') ?>" disabled></label>
    <label class="field"><span>WhatsApp Admin</span>
      <input type="text" value="<?= e($admin['wa'] ?? '') ?>" disabled></label>
    <label class="field"><span>Password Lama</span>
      <input type="password" name="current_password" autocomplete="current-password" required placeholder="Password saat ini"></label>
    <label class="field"><span>Password Baru</span>
      <input type="password" name="new_password" autocomplete="new-password" minlength="8" required placeholder="Minimal 8 karakter"></label>
    <label class="field"><span>Konfirmasi Password Baru</span>
      <input type="password" name="confirm_password" autocomplete="new-password" minlength="8" required placeholder="Ulangi password baru"></label>
    <div class="form-actions">
      <button class="btn btn-primary">Simpan Password</button>
      <a href="<?= e(admin_url('dashboard')) ?>" class="btn btn-ghost">Kembali</a>
    </div>
  </form>
</section>
