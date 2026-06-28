<?php
/**
 * app/views/layout.php — Kerangka tampilan utama.
 * Variabel: $title, $metadesc, $content (nama file view), $vars (array), $bare (bool)
 */
$vars = $vars ?? [];
$bare = $bare ?? false;
$u = current_user();
$site = setting('site_name', 'The Travel CEO');
$pagePixel = trim((string)($vars['page_facebook_pixel'] ?? ''));
$pixel = $pagePixel !== '' ? $pagePixel : trim((string)setting('facebook_pixel'));
$canonicalHref = canonical_url();
$styleFile = dirname(__DIR__, 2) . '/assets/style.css';
$styleVer = is_file($styleFile) ? (string)filemtime($styleFile) : date('YmdHis');
$styleHref = base_url('assets/style.css') . '?v=' . rawurlencode($styleVer);
$bodyClass = [];
if ($content === 'home') $bodyClass[] = 'theme-home';
if (strpos($content, 'member/') === 0 || $content === 'access_page') $bodyClass[] = 'theme-member';
if (!empty($vars['body_class'])) $bodyClass[] = trim((string)$vars['body_class']);
if ($bare) $bodyClass[] = 'is-bare';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($title ?? $site) ?></title>
<meta name="description" content="<?= e($metadesc ?? '') ?>">
<link rel="canonical" href="<?= e($canonicalHref) ?>">
<?php if ($fav = setting('favicon')): ?><link rel="icon" href="<?= e(base_url($fav)) ?>"><?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e($styleHref) ?>">
<?php if ($ga = setting('google_analytics')): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($ga) ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= e($ga) ?>');</script>
<?php endif; ?>
<?php if ($pixel !== ''): ?>
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '<?= e($pixel) ?>');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none" alt="" src="https://www.facebook.com/tr?id=<?= e($pixel) ?>&ev=PageView&noscript=1"></noscript>
<?php endif; ?>
</head>
<body class="<?= e(implode(' ', $bodyClass)) ?>">

<header class="site-header">
  <div class="container header-inner">
    <a class="brand" href="<?= e(url('home')) ?>">
      <?php if ($logo = setting('logo')): ?>
        <img src="<?= e(base_url($logo)) ?>" alt="<?= e($site) ?>" class="brand-logo">
      <?php else: ?>
        <span class="brand-mark">✈</span><span class="brand-name"><?= e($site) ?></span>
      <?php endif; ?>
    </a>
    <nav class="nav-actions">
      <a href="<?= e(url('products')) ?>" class="nav-link">Kelas</a>
      <a href="<?= e(url('blog')) ?>" class="nav-link">Blog</a>
      <?php if ($u): ?>
        <a href="<?= e(url('dashboard')) ?>" class="nav-link">Dashboard</a>
        <?php if ($u['role'] === 'admin'): ?><a href="<?= e(admin_url('dashboard')) ?>" class="nav-link">Admin</a><?php endif; ?>
        <a href="<?= e(url('logout')) ?>" class="btn btn-ghost btn-sm">Keluar</a>
      <?php else: ?>
        <a href="<?= e(login_member_url()) ?>" class="nav-link">Masuk</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<?php require __DIR__ . '/partials/flash.php'; ?>

<main class="<?= $bare ? 'main-bare' : 'main' ?>">
  <?php
    extract($vars);
    require __DIR__ . '/' . $content . '.php';
  ?>
</main>

<?php if (!$bare): ?>
<footer class="site-footer">
  <div class="container footer-grid">
    <div>
      <div class="brand brand-footer"><span class="brand-mark">✈</span><span class="brand-name"><?= e($site) ?></span></div>
      <p class="muted"><?= e(setting('seo_desc', 'Platform edukasi & membership untuk pengusaha travel Indonesia.')) ?></p>
    </div>
    <div>
      <h4>Tautan</h4>
      <a href="<?= e(url('home')) ?>">Beranda</a>
      <a href="<?= e(url('products')) ?>">Semua Kelas</a>
      <a href="<?= e(url('blog')) ?>">Blog</a>
    </div>
    <div>
      <h4>Kontak</h4>
      <?php if ($em = setting('site_email')): ?><a href="mailto:<?= e($em) ?>"><?= e($em) ?></a><?php endif; ?>
      <?php if ($al = setting('site_address')): ?><p class="muted"><?= e($al) ?></p><?php endif; ?>
    </div>
  </div>
  <div class="container footer-bottom">
    <span class="muted">© <?= date('Y') ?> <?= e($site) ?>. Semua hak dilindungi.</span>
  </div>
</footer>
<?php endif; ?>

<?php require __DIR__ . '/partials/wa_button.php'; ?>
<script src="<?= e(base_url('assets/script.js')) ?>"></script>
</body>
</html>
