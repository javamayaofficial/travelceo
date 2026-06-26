<?php
/**
 * app/bootstrap.php — Dijalankan di awal setiap request.
 */
error_reporting(E_ALL);
ini_set('display_errors', '0'); // error tidak ditampilkan ke pengunjung
ini_set('log_errors', '1');
@ini_set('error_log', dirname(__DIR__) . '/storage/logs/php-error.log');

$ROOT = dirname(__DIR__);
$installUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if (basename($installUrl) === 'admin') $installUrl = dirname($installUrl);
$installUrl = ($installUrl === '.' || $installUrl === DIRECTORY_SEPARATOR) ? '' : $installUrl;
$installUrl = rtrim($installUrl, '/');
$installUrl .= '/install.php';

/* Pastikan sudah terinstal */
if (!file_exists($ROOT . '/config.php')) {
    header('Location: ' . $installUrl);
    exit;
}
$GLOBALS['CONFIG'] = require $ROOT . '/config.php';
if (empty($GLOBALS['CONFIG']['installed'])) {
    header('Location: ' . $installUrl);
    exit;
}

require $ROOT . '/app/helpers.php';

/* Session hardening */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    session_name('TRAVELCEO_SESS');
    session_start();
}

/* Session timeout */
if (!empty($_SESSION['uid'])) {
    $role = $_SESSION['urole'] ?? 'member';
    $idleTimeout = $role === 'admin' ? 30 * 60 : 2 * 60 * 60;
    $lastActivity = (int)($_SESSION['last_activity'] ?? 0);
    if ($lastActivity > 0 && (time() - $lastActivity) > $idleTimeout) {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'] ?? '', (bool)$params['secure'], (bool)$params['httponly']);
        }
        session_destroy();
        session_start();
        flash('error', 'Sesi Anda berakhir karena tidak ada aktivitas. Silakan login kembali.');
        redirect($role === 'admin' ? login_panel_url() : login_member_url());
    }
    $_SESSION['last_activity'] = time();
}

/* Koneksi database aman */
try {
    db();
    ensure_runtime_schema();
} catch (Exception $ex) {
    http_response_code(500);
    die('Koneksi database gagal. Periksa kembali config.php Anda.');
}

/* Tracking klik affiliate (?ref=kode) */
if (!empty($_GET['ref'])) {
    $ref = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['ref']);
    if ($ref) {
        setcookie('tc_ref', $ref, [
            'expires' => time() + 60 * 60 * 24 * 30,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        ]);
        try {
            $st = db()->prepare("SELECT id FROM users WHERE ref_code = ?");
            $st->execute([$ref]);
            if ($aid = $st->fetchColumn()) {
                db()->prepare("INSERT INTO clicks (affiliate_id, created_at) VALUES (?, NOW())")->execute([$aid]);
            }
        } catch (Exception $e) {}
    }
}
