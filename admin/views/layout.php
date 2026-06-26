<?php
/** admin/views/layout.php — kerangka panel admin. $p = halaman aktif */
$site = setting('site_name', 'The Travel CEO');
$menu = [
    'dashboard'   => ['📊', 'Dashboard'],
    'transactions'=> ['🧾', 'Transaksi'],
    'products'    => ['📦', 'Produk'],
    'class_access'=> ['🎓', 'Akses Kelas'],
    'salespages'  => ['📄', 'Salespage'],
    'access_pages'=> ['🔓', 'Akses Produk'],
    'posts'       => ['📝', 'Post Builder'],
    'coupons'     => ['🎟️', 'Kupon'],
    'commissions' => ['💸', 'Komisi Affiliate'],
    'members'     => ['👥', 'Member'],
    'settings'    => ['⚙️', 'Pengaturan'],
    'account'     => ['🔐', 'Akun Admin'],
];
$pendingCount = (int)db()->query("SELECT COUNT(*) FROM transactions WHERE status='pending'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin — <?= e($site) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(base_url('assets/style.css')) ?>">
</head>
<body class="admin-body">
<input type="checkbox" id="navtoggle" hidden>
<header class="admin-top">
  <label for="navtoggle" class="hamb" aria-label="Menu">☰</label>
  <span class="admin-brand">✈ <?= e($site) ?> · Admin</span>
  <a href="<?= e(url('home')) ?>" class="admin-top-link" target="_blank">Lihat Situs ↗</a>
  <a href="<?= e(url('logout')) ?>" class="admin-top-link">Keluar</a>
</header>

<div class="admin-shell">
  <aside class="admin-side">
    <nav>
      <?php foreach ($menu as $key => $m): ?>
        <a class="aside-link <?= $p === $key ? 'active' : '' ?>" href="<?= e(admin_url($key)) ?>">
          <span class="ai"><?= $m[0] ?></span><?= e($m[1]) ?>
          <?php if ($key === 'transactions' && $pendingCount): ?><span class="aside-badge"><?= $pendingCount ?></span><?php endif; ?>
        </a>
      <?php endforeach; ?>
    </nav>
  </aside>

  <main class="admin-main">
    <?php require __DIR__ . '/../../app/views/partials/flash.php'; ?>
    <?php
      $page = preg_replace('/[^a-z_]/', '', $p);
      $file = __DIR__ . '/' . $page . '.php';
      require is_file($file) ? $file : __DIR__ . '/dashboard.php';
    ?>
  </main>
</div>
<script src="<?= e(base_url('assets/script.js')) ?>"></script>
</body>
</html>
