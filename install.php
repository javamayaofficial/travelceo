<?php
/**
 * install.php — Installer Wizard The Travel CEO.
 * Jalankan lewat browser: https://domainanda.id/install.php
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
$ROOT = __DIR__;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    session_name('TRAVELCEO_INSTALL');
    session_start();
}

function install_csrf_token() {
    if (empty($_SESSION['install_csrf'])) $_SESSION['install_csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['install_csrf'];
}

function install_check_csrf() {
    if (($_POST['csrf'] ?? '') !== ($_SESSION['install_csrf'] ?? '_')) {
        http_response_code(419);
        die('Sesi instalasi kedaluwarsa. Muat ulang halaman lalu coba lagi.');
    }
}

/* Jika sudah terinstal, kunci installer */
if (file_exists($ROOT . '/config.php')) {
    $cfg = @include $ROOT . '/config.php';
    if (is_array($cfg) && !empty($cfg['installed'])) {
        $done = true;
    }
}

$errors = [];
$success = false;

/* Cek requirement */
$reqs = [
    'PHP versi 7.4+'        => version_compare(PHP_VERSION, '7.4.0', '>='),
    'Ekstensi PDO MySQL'    => extension_loaded('pdo_mysql'),
    'Ekstensi cURL (WA)'    => extension_loaded('curl'),
    'Folder storage/ bisa ditulis' => is_writable($ROOT . '/storage') || @mkdir($ROOT . '/storage', 0755, true),
    'Root folder bisa ditulis (config.php)' => is_writable($ROOT),
];
$reqOk = !in_array(false, $reqs, true);

if (empty($done) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    install_check_csrf();
    $dbHost = trim($_POST['db_host'] ?? 'localhost');
    $dbName = trim($_POST['db_name'] ?? '');
    $dbUser = trim($_POST['db_user'] ?? '');
    $dbPass = $_POST['db_pass'] ?? '';
    $adminName  = trim($_POST['admin_name'] ?? '');
    $adminEmail = trim($_POST['admin_email'] ?? '');
    $adminWa    = trim($_POST['admin_wa'] ?? '');
    $adminPass  = $_POST['admin_pass'] ?? '';

    if ($dbName === '' || $dbUser === '') $errors[] = 'Nama database dan user database wajib diisi.';
    if (strlen($adminName) < 3) $errors[] = 'Nama admin minimal 3 karakter.';
    if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email admin tidak valid.';
    if (strlen($adminPass) < 8) $errors[] = 'Password admin minimal 8 karakter.';

    $pdo = null;
    if (!$errors) {
        try {
            $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
        } catch (Exception $e) {
            $errors[] = 'Koneksi database gagal. Periksa kembali host, nama database, user, dan password.';
        }
    }

    if (!$errors && $pdo) {
        try {
            // Jalankan skema
            $sql = file_get_contents($ROOT . '/database.sql');
            foreach (_split_sql($sql) as $stmt) {
                if (trim($stmt) !== '') $pdo->exec($stmt);
            }

            // Buat akun admin
            $wa = preg_replace('/[^0-9]/', '', $adminWa);
            if ($wa === '') $wa = null;
            if ($wa !== null && strpos($wa, '0') === 0) $wa = '62' . substr($wa, 1);
            $chk = $pdo->prepare("SELECT id FROM users WHERE email=?");
            $chk->execute([$adminEmail]);
            if (!$chk->fetchColumn()) {
                $ins = $pdo->prepare("INSERT INTO users (name,email,password,wa,role,status,ref_code,created_at)
                                      VALUES (?,?,?,?, 'admin','active','admin', NOW())");
                $ins->execute([$adminName, $adminEmail, password_hash($adminPass, PASSWORD_DEFAULT), $wa]);
            }

            // Pengaturan default
            $defaults = [
                'site_name' => 'The Travel CEO',
                'site_email' => $adminEmail,
                'site_wa' => $wa,
                'seo_title' => 'The Travel CEO — Akademi Bisnis Travel Indonesia',
                'seo_desc'  => 'Platform edukasi, mentoring & komunitas untuk owner travel umroh & wisata.',
                'commission_percent' => '10',
            ];
            $sst = $pdo->prepare("INSERT INTO settings (skey,svalue) VALUES (?,?) ON DUPLICATE KEY UPDATE svalue=svalue");
            foreach ($defaults as $k => $v) $sst->execute([$k, $v]);

            // Tulis config.php
            $appKey = bin2hex(random_bytes(16));
            $config = "<?php\nreturn " . var_export([
                'db_host' => $dbHost, 'db_name' => $dbName, 'db_user' => $dbUser, 'db_pass' => $dbPass,
                'base_url' => '',
                'app_key' => $appKey,
                'fonnte_token' => '',
                'mailketing_token' => '',
                'mail_sender' => $adminEmail,
                'installed' => true,
            ], true) . ";\n";
            if (@file_put_contents($ROOT . '/config.php', $config) === false) {
                $errors[] = 'Gagal menulis config.php. Pastikan folder bisa ditulis (CHMOD 755).';
            } else {
                $success = true;
            }
        } catch (Exception $e) {
            $errors[] = 'Gagal menyiapkan database: ' . $e->getMessage();
        }
    }
}

function _split_sql($sql) {
    $lines = explode("\n", $sql);
    $clean = [];
    foreach ($lines as $l) {
        if (preg_match('/^\s*--/', $l)) continue; // buang komentar
        $clean[] = $l;
    }
    return explode(';', implode("\n", $clean));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Installer — The Travel CEO</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{--navy:#0B2447;--navy2:#13315C;--gold:#D4A24E;--bg:#F6F7FB;--line:#E6E9F0;--ok:#16A34A;--err:#DC2626;}
*{box-sizing:border-box}body{margin:0;font-family:Inter,sans-serif;background:var(--bg);color:#1C2230;line-height:1.6;padding:24px 16px}
.wrap{max-width:560px;margin:0 auto}
.logo{text-align:center;font-family:Poppins;font-weight:800;font-size:1.5rem;color:var(--navy);margin-bottom:4px}
.logo span{color:var(--gold)}
.sub{text-align:center;color:#6B7280;margin-bottom:24px}
.card{background:#fff;border:1px solid var(--line);border-radius:20px;padding:24px;box-shadow:0 10px 30px rgba(0,0,0,.06);margin-bottom:18px}
h2{font-family:Poppins;font-size:1.15rem;margin:0 0 14px}
.req{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--line);font-size:.95rem}
.req:last-child{border:0}.req .y{color:var(--ok);font-weight:600}.req .n{color:var(--err);font-weight:600}
label{display:block;margin-bottom:14px}label span{display:block;font-weight:600;font-size:.9rem;margin-bottom:6px}
input{width:100%;padding:12px 14px;border:1px solid var(--line);border-radius:12px;font:inherit;background:#fff}
input:focus{outline:none;border-color:var(--navy);box-shadow:0 0 0 3px rgba(11,36,71,.1)}
.btn{display:block;width:100%;text-align:center;padding:14px;border:none;border-radius:14px;background:var(--navy);color:#fff;font:inherit;font-weight:700;cursor:pointer;text-decoration:none;font-size:1rem}
.btn:hover{background:var(--navy2)}
.gold{background:var(--gold);color:#3a2a08}.gold:hover{filter:brightness(.96)}
.alert{padding:12px 14px;border-radius:12px;margin-bottom:14px;font-size:.92rem}
.alert-err{background:#FEECEC;color:#9B1C1C}.alert-ok{background:#E7F6EC;color:#176B3A}
.hint{color:#6B7280;font-size:.82rem;font-weight:400;margin-top:4px}
.divider{font-family:Poppins;font-weight:700;color:var(--navy);margin:18px 0 10px;font-size:.95rem}
ul{margin:6px 0 0;padding-left:18px}
.note{background:#FFF8EC;border:1px solid #F0E0BE;border-radius:12px;padding:12px 14px;font-size:.9rem;margin-top:12px}
</style>
</head>
<body>
<div class="wrap">
  <div class="logo">✈ The Travel <span>CEO</span></div>
  <div class="sub">Installer Wizard — Pemasangan Otomatis</div>

  <?php if (!empty($done)): ?>
    <div class="card">
      <div class="alert alert-ok">Aplikasi sudah terinstal. ✅</div>
      <p>Demi keamanan, <strong>hapus atau ganti nama file <code>install.php</code></strong> lewat File Manager cPanel.</p>
      <a class="btn" href="admin/index.php">Masuk ke Panel Admin →</a>
    </div>
  <?php elseif ($success): ?>
    <div class="card">
      <div class="alert alert-ok">🎉 Instalasi berhasil! Akun admin sudah dibuat.</div>
      <div class="note"><strong>Langkah terakhir (penting):</strong> hapus file <code>install.php</code> lewat File Manager cPanel agar tidak bisa diakses orang lain.</div>
      <p style="margin-top:16px">Setelah itu, login menggunakan email &amp; password admin yang tadi Anda buat.</p>
      <a class="btn gold" href="admin/index.php">Login Admin Sekarang →</a>
    </div>
  <?php else: ?>

    <div class="card">
      <h2>1. Cek Kebutuhan Server</h2>
      <?php foreach ($reqs as $label => $ok): ?>
        <div class="req"><span><?= htmlspecialchars($label) ?></span><span class="<?= $ok?'y':'n' ?>"><?= $ok?'✓ OK':'✕ Belum' ?></span></div>
      <?php endforeach; ?>
      <?php if (!$reqOk): ?><div class="alert alert-err" style="margin-top:12px">Perbaiki item bertanda ✕ terlebih dahulu (hubungi penyedia hosting bila perlu).</div><?php endif; ?>
    </div>

    <?php if ($errors): ?>
      <div class="alert alert-err"><strong>Periksa kembali:</strong><ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <form method="post">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(install_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
      <div class="card">
        <h2>2. Data Database</h2>
        <p class="hint" style="margin-top:-8px;margin-bottom:14px">Buat dulu database &amp; user di cPanel → MySQL Databases, lalu isi di sini.</p>
        <label><span>Host Database</span><input name="db_host" value="<?= htmlspecialchars($_POST['db_host'] ?? 'localhost') ?>"><span class="hint">Biasanya: localhost</span></label>
        <label><span>Nama Database</span><input name="db_name" value="<?= htmlspecialchars($_POST['db_name'] ?? '') ?>" required></label>
        <label><span>User Database</span><input name="db_user" value="<?= htmlspecialchars($_POST['db_user'] ?? '') ?>" required></label>
        <label><span>Password Database</span><input type="password" name="db_pass" value=""></label>
      </div>

      <div class="card">
        <h2>3. Akun Admin</h2>
        <label><span>Nama Lengkap</span><input name="admin_name" value="<?= htmlspecialchars($_POST['admin_name'] ?? '') ?>" required></label>
        <label><span>Email Admin</span><input type="email" name="admin_email" value="<?= htmlspecialchars($_POST['admin_email'] ?? '') ?>" required></label>
        <label><span>Nomor WhatsApp</span><input name="admin_wa" value="<?= htmlspecialchars($_POST['admin_wa'] ?? '') ?>" placeholder="08xxxxxxxxxx"></label>
        <label><span>Password Admin</span><input type="password" name="admin_pass" required><span class="hint">Minimal 8 karakter</span></label>
      </div>

      <button class="btn gold" <?= $reqOk?'':'disabled style=opacity:.5' ?>>🚀 Pasang Sekarang</button>
    </form>
  <?php endif; ?>
</div>
</body>
</html>
