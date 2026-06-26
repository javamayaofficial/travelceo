<?php
/**
 * app/helpers.php — Fungsi-fungsi bantu yang dipakai di seluruh aplikasi.
 */

/* ---------- Keamanan output ---------- */
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

/* ---------- Database (PDO singleton) ---------- */
function db() {
    static $pdo = null;
    if ($pdo === null) {
        $c = $GLOBALS['CONFIG'];
        $dsn = "mysql:host={$c['db_host']};dbname={$c['db_name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $c['db_user'], $c['db_pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

function config_value($key, $default = null) {
    return array_key_exists($key, $GLOBALS['CONFIG'] ?? []) ? $GLOBALS['CONFIG'][$key] : $default;
}

/* ---------- Pengaturan website (tabel settings) ---------- */
function setting($key, $default = '') {
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            foreach (db()->query("SELECT skey, svalue FROM settings") as $r) {
                $cache[$r['skey']] = $r['svalue'];
            }
        } catch (Exception $ex) { $cache = []; }
    }
    return array_key_exists($key, $cache) ? $cache[$key] : $default;
}

function set_setting($key, $value) {
    $st = db()->prepare("INSERT INTO settings (skey, svalue) VALUES (?, ?)
                         ON DUPLICATE KEY UPDATE svalue = VALUES(svalue)");
    $st->execute([$key, $value]);
}

/* ---------- URL & navigasi ---------- */
function base_url($path = '') {
    $b = $GLOBALS['CONFIG']['base_url'] ?? '';
    if ($b === '') {
        $https  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        $scheme = $https ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $dir    = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
        if (basename($dir) === 'admin') $dir = dirname($dir);
        $b = $scheme . '://' . $host . $dir;
    }
    return rtrim($b, '/') . '/' . ltrim($path, '/');
}

function url($page, $params = []) {
    $q = array_merge(['p' => $page], $params);
    return base_url('index.php') . '?' . http_build_query($q);
}

function admin_url($page = 'dashboard', $params = []) {
    $q = array_merge(['p' => $page], $params);
    return base_url('admin/index.php') . '?' . http_build_query($q);
}

function login_member_url() {
    return base_url('login-member/');
}

function login_panel_url() {
    return base_url('login-panel/');
}

function google_client_id() {
    $clientId = trim((string)config_value('google_client_id', ''));
    if ($clientId !== '') return $clientId;
    return trim((string)setting('google_client_id', ''));
}

function setting_url($key, $default = '') {
    $value = trim((string)setting($key, ''));
    if ($value === '') return $default;
    if (preg_match('~^(https?:)?//~i', $value)) return $value;
    if (preg_match('~^(mailto:|tel:|#)~i', $value)) return $value;
    if ($value[0] === '?') return base_url('index.php') . $value;
    return base_url(ltrim($value, '/'));
}

function redirect($to) { header('Location: ' . $to); exit; }

/* ---------- Format ---------- */
function rupiah($n) { return 'Rp ' . number_format((int)$n, 0, ',', '.'); }

function slugify($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-') ?: 'item-' . time();
}

function excerpt_text($text, $length = 180) {
    $text = trim(preg_replace('/\s+/', ' ', strip_tags((string)$text)));
    if ($text === '') return '';
    if (mb_strlen($text) <= $length) return $text;
    return rtrim(mb_substr($text, 0, $length - 1)) . '...';
}

function unique_value_exists($table, $field, $value, $excludeId = 0) {
    $table = (string)$table;
    $field = (string)$field;
    if (!preg_match('/^[a-z_][a-z0-9_]*$/i', $table) || !preg_match('/^[a-z_][a-z0-9_]*$/i', $field)) {
        throw new InvalidArgumentException('Identifier database tidak valid.');
    }
    $sql = "SELECT id FROM `$table` WHERE `$field` = ?";
    $params = [$value];
    if ((int)$excludeId > 0) {
        $sql .= " AND id <> ?";
        $params[] = (int)$excludeId;
    }
    $sql .= " LIMIT 1";
    $st = db()->prepare($sql);
    $st->execute($params);
    return (bool)$st->fetchColumn();
}

function youtube_video_id($url) {
    $url = trim((string)$url);
    if ($url === '') return '';
    if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/|v/))([A-Za-z0-9_-]{11})~', $url, $m)) {
        return $m[1];
    }
    return '';
}

function youtube_embed_url($url) {
    $id = youtube_video_id($url);
    if ($id === '') return '';
    $params = [
        'rel' => 0,
        'modestbranding' => 1,
        'playsinline' => 1,
        'enablejsapi' => 1,
        'controls' => 1,
        'fs' => 0,
        'iv_load_policy' => 3,
        'disablekb' => 1,
    ];
    return 'https://www.youtube-nocookie.com/embed/' . rawurlencode($id) . '?' . http_build_query($params);
}

function wa_normalize($no) {
    $no = preg_replace('/[^0-9]/', '', $no);
    if ($no === '') return '';
    if (strpos($no, '0') === 0)  $no = '62' . substr($no, 1);
    if (strpos($no, '62') !== 0) $no = '62' . $no;
    return $no;
}

function wa_mask($no) {
    $no = wa_normalize($no);
    if (strlen($no) <= 6) return $no;
    return substr($no, 0, 4) . str_repeat('*', max(0, strlen($no) - 6)) . substr($no, -2);
}

/* ---------- Flash message (notifikasi sekali tampil) ---------- */
function flash($type, $msg = null) {
    if ($msg === null) {
        $f = $_SESSION['flash'][$type] ?? null;
        unset($_SESSION['flash'][$type]);
        return $f;
    }
    $_SESSION['flash'][$type] = $msg;
}

/* ---------- CSRF ---------- */
function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field() { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function check_csrf() {
    if (($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? '_')) {
        http_response_code(419);
        die('Sesi kedaluwarsa. Silakan muat ulang halaman dan coba lagi.');
    }
}

/* ---------- Storage path ---------- */
function storage_path($path = '') {
    $base = dirname(__DIR__) . '/storage';
    if ($path === '') return $base;
    return $base . '/' . ltrim(str_replace('\\', '/', $path), '/');
}

/* ---------- Login throttle ---------- */
function _auth_rate_file($email) {
    $seed = strtolower(trim((string)$email)) . '|' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $secret = $GLOBALS['CONFIG']['app_key'] ?? __FILE__;
    $key = hash_hmac('sha256', $seed, $secret);
    $dir = storage_path('logs/auth_rate');
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    return $dir . '/' . $key . '.json';
}

function _auth_rate_read($file) {
    $raw = @file_get_contents($file);
    if (!$raw) return ['fails' => [], 'blocked_until' => 0];
    $data = json_decode($raw, true);
    if (!is_array($data)) return ['fails' => [], 'blocked_until' => 0];
    return [
        'fails' => array_values(array_map('intval', (array)($data['fails'] ?? []))),
        'blocked_until' => (int)($data['blocked_until'] ?? 0),
    ];
}

function _auth_rate_write($file, $state) {
    @file_put_contents($file, json_encode($state));
}

function login_rate_limit_status($email) {
    $window = 15 * 60;
    $file = _auth_rate_file($email);
    $now = time();
    $state = _auth_rate_read($file);
    $state['fails'] = array_values(array_filter($state['fails'], function ($ts) use ($now, $window) {
        return ($now - (int)$ts) < $window;
    }));
    if ($state['blocked_until'] <= $now) $state['blocked_until'] = 0;
    if (!$state['fails'] && !$state['blocked_until']) {
        if (is_file($file)) @unlink($file);
        return ['blocked' => false, 'retry_after' => 0];
    }
    _auth_rate_write($file, $state);
    return [
        'blocked' => $state['blocked_until'] > $now,
        'retry_after' => max(0, $state['blocked_until'] - $now),
    ];
}

function login_rate_limit_hit($email) {
    $window = 15 * 60;
    $maxAttempts = 5;
    $cooldown = 15 * 60;
    $file = _auth_rate_file($email);
    $now = time();
    $state = _auth_rate_read($file);
    $state['fails'] = array_values(array_filter($state['fails'], function ($ts) use ($now, $window) {
        return ($now - (int)$ts) < $window;
    }));
    $state['fails'][] = $now;
    if (count($state['fails']) >= $maxAttempts) $state['blocked_until'] = $now + $cooldown;
    _auth_rate_write($file, $state);
}

function login_rate_limit_clear($email) {
    $file = _auth_rate_file($email);
    if (is_file($file)) @unlink($file);
}

/* ---------- Migrasi runtime ringan ---------- */
function ensure_runtime_schema() {
    static $done = false;
    if ($done) return;
    $done = true;
    try {
        $userColumns = [
            'member_status' => "ALTER TABLE `users` ADD COLUMN `member_status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending' AFTER `status`",
            'approved_at' => "ALTER TABLE `users` ADD COLUMN `approved_at` DATETIME NULL AFTER `member_status`",
            'deleted_at' => "ALTER TABLE `users` ADD COLUMN `deleted_at` DATETIME NULL AFTER `created_at`",
        ];
        foreach ($userColumns as $column => $sql) {
            $hasColumn = db()->query("SHOW COLUMNS FROM `users` LIKE " . db()->quote($column))->fetch();
            if (!$hasColumn) db()->exec($sql);
        }

        $passwordCol = db()->query("SHOW COLUMNS FROM `users` LIKE 'password'")->fetch();
        if ($passwordCol && (($passwordCol['Null'] ?? 'NO') === 'NO')) {
            db()->exec("ALTER TABLE `users` MODIFY COLUMN `password` VARCHAR(255) NULL");
        }

        $hasMemberStatus = db()->query("SHOW COLUMNS FROM `users` LIKE 'member_status'")->fetch();
        if ($hasMemberStatus) {
            db()->exec("UPDATE `users`
                        SET member_status = 'approved', approved_at = COALESCE(approved_at, created_at)
                        WHERE role = 'member'
                          AND status = 'active'
                          AND (member_status IS NULL OR member_status = 'pending')");
        }

        db()->exec("CREATE TABLE IF NOT EXISTS `login_otps` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` INT UNSIGNED NOT NULL,
            `purpose` VARCHAR(40) NOT NULL DEFAULT 'login',
            `code_hash` VARCHAR(64) NOT NULL,
            `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
            `expires_at` DATETIME NOT NULL,
            `used_at` DATETIME NULL,
            `created_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            KEY `k_user_purpose` (`user_id`,`purpose`,`created_at`),
            KEY `k_expires` (`expires_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        db()->exec("CREATE TABLE IF NOT EXISTS `password_resets` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` INT UNSIGNED NOT NULL,
            `token_hash` VARCHAR(64) NOT NULL,
            `expires_at` DATETIME NOT NULL,
            `used_at` DATETIME NULL,
            `created_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            KEY `k_user_created` (`user_id`,`created_at`),
            KEY `k_token_hash` (`token_hash`),
            KEY `k_reset_expires` (`expires_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        db()->exec("UPDATE users SET wa = NULL WHERE wa = ''");
        $hasWaIndex = db()->query("SHOW INDEX FROM `users` WHERE Key_name = 'uq_wa'")->fetch();
        if (!$hasWaIndex) {
            $dupWa = db()->query("SELECT wa
                                  FROM users
                                  WHERE wa IS NOT NULL AND wa <> ''
                                  GROUP BY wa
                                  HAVING COUNT(*) > 1
                                  LIMIT 1")->fetchColumn();
            if (!$dupWa) {
                db()->exec("ALTER TABLE `users`
                            ADD UNIQUE KEY `uq_wa` (`wa`)");
            }
        }
        $hasWithdrawalId = db()->query("SHOW COLUMNS FROM `commissions` LIKE 'withdrawal_id'")->fetch();
        if (!$hasWithdrawalId) {
            db()->exec("ALTER TABLE `commissions`
                        ADD COLUMN `withdrawal_id` BIGINT UNSIGNED NULL AFTER `status`,
                        ADD KEY `k_withdrawal` (`withdrawal_id`)");
        }
        db()->exec("CREATE TABLE IF NOT EXISTS `commission_withdrawals` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `affiliate_id` INT UNSIGNED NOT NULL,
            `amount` INT UNSIGNED NOT NULL DEFAULT 0,
            `bank_name` VARCHAR(100) NOT NULL,
            `account_name` VARCHAR(150) NOT NULL,
            `account_number` VARCHAR(100) NOT NULL,
            `note` VARCHAR(500) NULL,
            `admin_note` VARCHAR(500) NULL,
            `status` ENUM('requested','approved','rejected','paid') NOT NULL DEFAULT 'requested',
            `created_at` DATETIME NOT NULL,
            `approved_at` DATETIME NULL,
            `rejected_at` DATETIME NULL,
            `paid_at` DATETIME NULL,
            PRIMARY KEY (`id`),
            KEY `k_affiliate_status` (`affiliate_id`,`status`,`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        db()->exec("CREATE TABLE IF NOT EXISTS `access_pages` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `product_id` INT UNSIGNED NOT NULL,
            `title` VARCHAR(190) NOT NULL,
            `slug` VARCHAR(190) NOT NULL,
            `html` MEDIUMTEXT NULL,
            `meta_title` VARCHAR(190) NULL,
            `meta_desc` VARCHAR(255) NULL,
            `featured_image` VARCHAR(255) NULL,
            `status` ENUM('publish','draft') NOT NULL DEFAULT 'draft',
            `created_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_access_slug` (`slug`),
            UNIQUE KEY `uq_access_product` (`product_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $hasSalesPixel = db()->query("SHOW COLUMNS FROM `salespages` LIKE 'facebook_pixel_id'")->fetch();
        if (!$hasSalesPixel) {
            db()->exec("ALTER TABLE `salespages`
                        ADD COLUMN `facebook_pixel_id` VARCHAR(50) NULL AFTER `meta_desc`");
        }
        db()->exec("CREATE TABLE IF NOT EXISTS `posts` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `title` VARCHAR(190) NOT NULL,
            `slug` VARCHAR(190) NOT NULL,
            `excerpt` VARCHAR(255) NULL,
            `html` MEDIUMTEXT NULL,
            `meta_title` VARCHAR(190) NULL,
            `meta_desc` VARCHAR(255) NULL,
            `featured_image` VARCHAR(255) NULL,
            `status` ENUM('publish','draft') NOT NULL DEFAULT 'draft',
            `published_at` DATETIME NULL,
            `created_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_post_slug` (`slug`),
            KEY `k_post_status_date` (`status`,`published_at`,`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        db()->exec("CREATE TABLE IF NOT EXISTS `event_tickets` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `transaction_id` INT UNSIGNED NOT NULL,
            `user_id` INT UNSIGNED NOT NULL,
            `product_id` INT UNSIGNED NOT NULL,
            `ticket_code` VARCHAR(40) NOT NULL,
            `ticket_token` VARCHAR(80) NOT NULL,
            `attendee_name` VARCHAR(150) NOT NULL,
            `attendee_email` VARCHAR(190) NOT NULL,
            `attendee_wa` VARCHAR(30) NULL,
            `status` ENUM('active','cancelled') NOT NULL DEFAULT 'active',
            `issued_at` DATETIME NOT NULL,
            `created_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_ticket_tx` (`transaction_id`),
            UNIQUE KEY `uq_ticket_code` (`ticket_code`),
            UNIQUE KEY `uq_ticket_token` (`ticket_token`),
            KEY `k_ticket_user` (`user_id`,`status`,`issued_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $lessonColumns = [
            'content_text' => "ALTER TABLE `lessons` ADD COLUMN `content_text` TEXT NULL AFTER `short_desc`",
            'duration_minutes' => "ALTER TABLE `lessons` ADD COLUMN `duration_minutes` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `youtube`",
            'resource_title' => "ALTER TABLE `lessons` ADD COLUMN `resource_title` VARCHAR(190) NULL AFTER `duration_minutes`",
            'resource_url' => "ALTER TABLE `lessons` ADD COLUMN `resource_url` VARCHAR(255) NULL AFTER `resource_title`",
        ];
        foreach ($lessonColumns as $column => $sql) {
            $hasColumn = db()->query("SHOW COLUMNS FROM `lessons` LIKE " . db()->quote($column))->fetch();
            if (!$hasColumn) db()->exec($sql);
        }
        $productColumns = [
            'event_start_at' => "ALTER TABLE `products` ADD COLUMN `event_start_at` DATETIME NULL AFTER `thumbnail`",
            'event_location' => "ALTER TABLE `products` ADD COLUMN `event_location` VARCHAR(190) NULL AFTER `event_start_at`",
            'event_city' => "ALTER TABLE `products` ADD COLUMN `event_city` VARCHAR(120) NULL AFTER `event_location`",
            'event_maps_url' => "ALTER TABLE `products` ADD COLUMN `event_maps_url` VARCHAR(255) NULL AFTER `event_city`",
            'event_notes' => "ALTER TABLE `products` ADD COLUMN `event_notes` TEXT NULL AFTER `event_maps_url`",
            'seat_quota' => "ALTER TABLE `products` ADD COLUMN `seat_quota` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `event_notes`",
        ];
        foreach ($productColumns as $column => $sql) {
            $hasColumn = db()->query("SHOW COLUMNS FROM `products` LIKE " . db()->quote($column))->fetch();
            if (!$hasColumn) db()->exec($sql);
        }
    } catch (Exception $e) {
        log_activity('schema_runtime_error', $e->getMessage());
    }
}

/* ---------- Autentikasi ---------- */
function current_user() {
    static $u = null;
    if ($u === null && !empty($_SESSION['uid'])) {
        $st = db()->prepare("SELECT * FROM users WHERE id = ?");
        $st->execute([$_SESSION['uid']]);
        $u = $st->fetch() ?: false;
    }
    return $u ?: null;
}
function is_logged_in() { return current_user() !== null; }
function is_admin() { $u = current_user(); return $u && $u['role'] === 'admin'; }

function member_block_message($status) {
    $status = strtolower(trim((string)$status));
    if ($status === 'rejected') return 'Pendaftaran Anda ditolak. Silakan hubungi Admin.';
    return 'Pendaftaran Anda sedang menunggu persetujuan Admin.';
}

function member_is_approved($u) {
    if (!$u) return false;
    if (($u['role'] ?? 'member') !== 'member') return true;
    if (!empty($u['deleted_at'])) return false;
    if (($u['status'] ?? 'inactive') !== 'active') return false;
    if (($u['member_status'] ?? 'approved') !== 'approved') return false;
    return true;
}

function require_login() {
    if (!is_logged_in()) { flash('error', 'Silakan login dulu untuk melanjutkan.'); redirect(login_member_url()); }
}
function require_member() {
    if (!is_logged_in()) {
        flash('error', 'Silakan login dulu untuk melanjutkan.');
        redirect(login_member_url());
    }
    if (is_admin()) {
        redirect(admin_url('dashboard'));
    }
    $u = current_user();
    if (!member_is_approved($u)) {
        $msg = member_block_message($u['member_status'] ?? 'pending');
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'] ?? '', (bool)$params['secure'], (bool)$params['httponly']);
        }
        session_destroy();
        session_start();
        flash('error', $msg);
        redirect(login_member_url());
    }
}
function require_admin() {
    if (!is_admin()) { flash('error', 'Silakan login ke admin panel terlebih dahulu.'); redirect(login_panel_url()); }
}

/* ---------- Audit log ---------- */
function log_activity($action, $detail = '') {
    try {
        $st = db()->prepare("INSERT INTO activity_logs (user_id, action, detail, ip, created_at)
                             VALUES (?, ?, ?, ?, NOW())");
        $uid = $_SESSION['uid'] ?? null;
        $st->execute([$uid, $action, mb_substr($detail, 0, 500), $_SERVER['REMOTE_ADDR'] ?? '', ]);
    } catch (Exception $e) { /* abaikan agar tidak mengganggu alur utama */ }
}

/* ---------- Integrasi WhatsApp (Fonnte) ---------- */
function fonnte_token() {
    $token = trim((string)config_value('fonnte_token', ''));
    if ($token !== '') return $token;
    return trim((string)setting('fonnte_token', ''));
}

function fonnte_send($to, $message) {
    $token = fonnte_token();
    if (!$token || !$to) return false;
    $to = wa_normalize($to);
    $ch = curl_init('https://api.fonnte.com/send');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => ['Authorization: ' . $token],
        CURLOPT_POSTFIELDS     => ['target' => $to, 'message' => $message],
    ]);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) { log_activity('wa_gagal', $err); return false; }
    $json = json_decode((string)$res, true);
    if (is_array($json)) {
        $ok = $json['status'] ?? $json['Status'] ?? null;
        if ($ok === false || $ok === 'false' || $ok === 0 || $ok === '0') {
            $reason = $json['reason'] ?? $json['detail'] ?? 'Respons API Fonnte menolak request.';
            log_activity('wa_gagal', (string)$reason);
            return false;
        }
    }
    return $res;
}

function wa_template($key, $vars = []) {
    $tpl = setting('wa_' . $key, '');
    if (!$tpl) {
        $defaults = [
            'register'  => "Halo {nama}, selamat datang di " . setting('site_name', 'The Travel CEO') . "! Akun Anda berhasil dibuat. Yuk mulai naik kelas. 🚀",
            'purchase'  => "Halo {nama}, pesanan {produk} ({kode}) sebesar {total} kami terima. Mohon tunggu verifikasi pembayaran ya. 🙏",
            'checkout_access' => "Halo {nama}, akun Anda di " . setting('site_name', 'The Travel CEO') . " berhasil dibuat.\nEmail: {email}\nWhatsApp: {wa}\nLogin member: {login}\nSilakan masuk memakai OTP WhatsApp atau tombol Google dengan email yang terdaftar.",
            'ticket_ready' => "Halo {nama}, pembayaran {produk} sudah disetujui.\nE-ticket Anda siap diunduh.\nKode tiket: {ticket}\nLink tiket: {link}\nTerima kasih sudah ikut workshop bersama kami.",
            'approved'  => "Halo {nama}, pembayaran untuk {produk} sudah DISETUJUI ✅. Kelas Anda kini terbuka. Selamat belajar!",
            'rejected'  => "Halo {nama}, mohon maaf pembayaran untuk {produk} belum dapat kami verifikasi. Silakan hubungi admin untuk bantuan.",
            'otp_login' => "Kode OTP login Anda di " . setting('site_name', 'The Travel CEO') . " adalah {kode}. Berlaku {expired}. Jangan bagikan kode ini kepada siapa pun.",
        ];
        $tpl = $defaults[$key] ?? '';
    }
    foreach ($vars as $k => $v) $tpl = str_replace('{' . $k . '}', $v, $tpl);
    return $tpl;
}

function generate_secure_password($length = 12) {
    $length = max(10, (int)$length);
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%';
    $max = strlen($chars) - 1;
    $pass = '';
    for ($i = 0; $i < $length; $i++) {
        $pass .= $chars[random_int(0, $max)];
    }
    return $pass;
}

/* ---------- Integrasi Email (Mailketing) ---------- */
function mailketing_token() {
    $token = trim((string)config_value('mailketing_token', ''));
    if ($token !== '') return $token;
    return trim((string)setting('mailketing_token', ''));
}

function mail_sender_email() {
    $sender = trim((string)config_value('mail_sender', ''));
    if ($sender !== '') return $sender;
    $sender = trim((string)setting('mail_sender', ''));
    if ($sender !== '') return $sender;
    $sender = trim((string)setting('site_email', ''));
    return $sender;
}

function mail_sender_name() {
    $name = trim((string)setting('site_name', 'The Travel CEO'));
    return $name !== '' ? $name : 'The Travel CEO';
}

function http_get_json($url) {
    $url = trim((string)$url);
    if ($url === '') return null;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($err || $status < 200 || $status >= 300 || !$res) return null;
    $json = json_decode((string)$res, true);
    return is_array($json) ? $json : null;
}

function mail_template($key, $vars = []) {
    $site = setting('site_name', 'The Travel CEO');
    $templates = [
        'register' => [
            'subject' => 'Selamat datang di ' . $site,
            'html' => '<p>Halo {nama},</p><p>Akun Anda di <strong>' . e($site) . '</strong> berhasil dibuat.</p><p>Silakan login dan mulai akses membership Anda.</p>',
        ],
        'purchase' => [
            'subject' => 'Pesanan Anda diterima: {kode}',
            'html' => '<p>Halo {nama},</p><p>Pesanan untuk <strong>{produk}</strong> dengan kode <strong>{kode}</strong> sudah kami terima.</p><p>Total pembayaran: <strong>{total}</strong>.</p><p>Mohon tunggu verifikasi pembayaran dari admin.</p>',
        ],
        'checkout_access' => [
            'subject' => 'Akses akun Anda di ' . $site,
            'html' => '<p>Halo {nama},</p><p>Akun Anda di <strong>' . e($site) . '</strong> berhasil dibuat.</p><p>Email: <strong>{email}</strong><br>WhatsApp: <strong>{wa}</strong></p><p>Masuk di sini: <a href="{login}">{login}</a></p><p>Silakan login memakai OTP WhatsApp atau tombol Google dengan email yang terdaftar.</p>',
        ],
        'member_approved' => [
            'subject' => 'Akun membership Anda sudah aktif — ' . $site,
            'html' => '<p>Halo {nama},</p><p>Membership Anda sudah disetujui.</p><p>Silakan login menggunakan akun berikut:</p><p>Username: <strong>{username}</strong><br>Password: <strong>{password}</strong></p><p>Login: <a href="{login}">{login}</a></p>',
        ],
        'otp_login_email' => [
            'subject' => 'Kode OTP login Anda',
            'html' => '<p>Halo {nama},</p><p>Kode OTP login Anda di <strong>' . e($site) . '</strong> adalah:</p><p style="font-size:28px;font-weight:700;letter-spacing:.15em"><strong>{kode}</strong></p><p>Kode ini berlaku selama {expired}. Jangan bagikan ke siapa pun.</p>',
        ],
        'ticket_ready' => [
            'subject' => 'E-ticket Anda siap: {produk}',
            'html' => '<p>Halo {nama},</p><p>Terima kasih sudah ikut workshop <strong>{produk}</strong>.</p><p>E-ticket Anda sudah siap diunduh.</p><p>Kode tiket: <strong>{ticket}</strong><br>Link tiket: <a href="{link}">{link}</a></p><p>Simpan tiket ini dan tunjukkan saat dibutuhkan.</p>',
        ],
        'approved' => [
            'subject' => 'Pembayaran disetujui: {produk}',
            'html' => '<p>Halo {nama},</p><p>Pembayaran untuk <strong>{produk}</strong> sudah disetujui.</p><p>Kelas Anda sekarang sudah terbuka dan siap diakses.</p>',
        ],
        'rejected' => [
            'subject' => 'Pembayaran perlu ditinjau ulang: {produk}',
            'html' => '<p>Halo {nama},</p><p>Mohon maaf, pembayaran untuk <strong>{produk}</strong> belum dapat kami verifikasi.</p><p>Silakan hubungi admin untuk bantuan lebih lanjut.</p>',
        ],
        'admin_purchase' => [
            'subject' => 'Order baru masuk: {kode}',
            'html' => '<p>Order baru masuk.</p><ul><li>Kode: <strong>{kode}</strong></li><li>Produk: <strong>{produk}</strong></li><li>Nama: <strong>{nama}</strong></li><li>Total: <strong>{total}</strong></li></ul><p>Silakan cek panel admin untuk verifikasi.</p>',
        ],
        'reset_password' => [
            'subject' => 'Reset password akun Anda',
            'html' => '<p>Halo {nama},</p><p>Kami menerima permintaan reset password untuk akun Anda.</p><p>Klik tautan berikut untuk membuat password baru:</p><p><a href="{link}">{link}</a></p><p>Link ini berlaku selama {expired}. Jika Anda tidak merasa meminta reset password, abaikan email ini.</p>',
        ],
    ];
    $tpl = $templates[$key] ?? ['subject' => $site, 'html' => '<p>{content}</p>'];
    foreach ($vars as $k => $v) {
        $tpl['subject'] = str_replace('{' . $k . '}', (string)$v, $tpl['subject']);
        $replacement = $k === 'link' ? e((string)$v) : e((string)$v);
        $tpl['html'] = str_replace('{' . $k . '}', $replacement, $tpl['html']);
    }
    return $tpl;
}

function mailketing_send($to, $subject, $content, $options = []) {
    $token = mailketing_token();
    $fromEmail = trim((string)($options['from_email'] ?? mail_sender_email()));
    $fromName = trim((string)($options['from_name'] ?? mail_sender_name()));
    $recipient = trim((string)$to);

    if (!$token || !$fromEmail || !$fromName || !$recipient || !$subject || !$content) return false;
    if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) return false;

    $params = [
        'api_token' => $token,
        'from_name' => $fromName,
        'from_email' => $fromEmail,
        'recipient' => $recipient,
        'subject' => (string)$subject,
        'content' => (string)$content,
    ];

    if (!empty($options['attach1'])) $params['attach1'] = (string)$options['attach1'];
    if (!empty($options['attach2'])) $params['attach2'] = (string)$options['attach2'];

    $ch = curl_init('https://api.mailketing.co.id/api/v1/send');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_POSTFIELDS => http_build_query($params),
    ]);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) {
        log_activity('email_gagal', $err);
        return false;
    }
    $json = json_decode((string)$res, true);
    if (is_array($json)) {
        $status = strtolower((string)($json['status'] ?? ''));
        if ($status !== 'success') {
            log_activity('email_gagal', (string)($json['response'] ?? 'Mailketing gagal mengirim email.'));
            return false;
        }
    }
    return $res;
}

function ticket_url($token) {
    return url('ticket', ['token' => $token]);
}

function ticket_verify_url($token) {
    return url('ticket-verify', ['token' => $token]);
}

function ticket_qr_image_url($token) {
    $verify = ticket_verify_url($token);
    return 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . rawurlencode($verify);
}

function ticket_is_eligible($productType, $productTitle = '') {
    $type = strtolower(trim((string)$productType));
    if ($type === 'workshop') return true;
    return stripos((string)$productTitle, 'workshop') !== false || stripos((string)$productTitle, 'masterclass') !== false;
}

function product_seat_stats($productId) {
    ensure_runtime_schema();
    $st = db()->prepare("SELECT seat_quota FROM products WHERE id = ? LIMIT 1");
    $st->execute([(int)$productId]);
    $quota = (int)$st->fetchColumn();
    $count = db()->prepare("SELECT COUNT(*)
                            FROM transactions
                            WHERE product_id = ?
                              AND status = 'approved'");
    $count->execute([(int)$productId]);
    $approved = (int)$count->fetchColumn();
    return [
        'quota' => $quota,
        'approved' => $approved,
        'remaining' => $quota > 0 ? max(0, $quota - $approved) : null,
        'is_full' => $quota > 0 ? $approved >= $quota : false,
    ];
}

function ticket_issue_for_transaction(array $transaction) {
    ensure_runtime_schema();
    if (!ticket_is_eligible($transaction['ptype'] ?? '', $transaction['ptitle'] ?? '')) return null;

    $pdo = db();
    $check = $pdo->prepare("SELECT * FROM event_tickets WHERE transaction_id = ? LIMIT 1");
    $check->execute([(int)$transaction['id']]);
    $existing = $check->fetch();
    if ($existing) return $existing;

    $ticketCode = 'ETK' . date('ymd') . strtoupper(bin2hex(random_bytes(2)));
    $ticketToken = bin2hex(random_bytes(24));
    $issuedAt = !empty($transaction['approved_at']) ? $transaction['approved_at'] : date('Y-m-d H:i:s');

    $ins = $pdo->prepare("INSERT INTO event_tickets
        (transaction_id, user_id, product_id, ticket_code, ticket_token, attendee_name, attendee_email, attendee_wa, status, issued_at, created_at)
        VALUES (?,?,?,?,?,?,?,?, 'active', ?, NOW())");
    $ins->execute([
        (int)$transaction['id'],
        (int)$transaction['user_id'],
        (int)$transaction['product_id'],
        $ticketCode,
        $ticketToken,
        (string)$transaction['uname'],
        (string)$transaction['uemail'],
        trim((string)($transaction['uwa'] ?? '')) ?: null,
        $issuedAt,
    ]);

    $fetch = $pdo->prepare("SELECT * FROM event_tickets WHERE transaction_id = ? LIMIT 1");
    $fetch->execute([(int)$transaction['id']]);
    return $fetch->fetch() ?: null;
}

function ticket_find_by_token($token) {
    ensure_runtime_schema();
    $token = trim((string)$token);
    if ($token === '') return null;
    $st = db()->prepare("SELECT et.*, t.code AS order_code, t.total, t.approved_at,
                                p.title AS product_title, p.type AS product_type, p.thumbnail,
                                p.event_start_at, p.event_location, p.event_city, p.event_maps_url, p.event_notes, p.seat_quota,
                                u.name AS user_name, u.email AS user_email, u.wa AS user_wa
                         FROM event_tickets et
                         JOIN transactions t ON t.id = et.transaction_id
                         JOIN products p ON p.id = et.product_id
                         JOIN users u ON u.id = et.user_id
                         WHERE et.ticket_token = ?
                         LIMIT 1");
    $st->execute([$token]);
    return $st->fetch() ?: null;
}

/* ---------- OTP login ---------- */
function otp_issue($userId, $purpose = 'login') {
    ensure_runtime_schema();
    $userId = (int)$userId;
    $purpose = trim((string)$purpose) ?: 'login';
    $cooldownSeconds = 60;
    $maxPerHour = 5;

    $last = db()->prepare("SELECT created_at
                           FROM login_otps
                           WHERE user_id = ? AND purpose = ?
                           ORDER BY id DESC
                           LIMIT 1");
    $last->execute([$userId, $purpose]);
    $lastCreated = $last->fetchColumn();
    if ($lastCreated && (time() - strtotime((string)$lastCreated)) < $cooldownSeconds) {
        return ['ok' => false, 'error' => 'Tunggu sekitar 1 menit sebelum meminta OTP lagi.'];
    }

    $count = db()->prepare("SELECT COUNT(*)
                            FROM login_otps
                            WHERE user_id = ? AND purpose = ?
                              AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $count->execute([$userId, $purpose]);
    if ((int)$count->fetchColumn() >= $maxPerHour) {
        return ['ok' => false, 'error' => 'Terlalu banyak permintaan OTP. Coba lagi dalam 1 jam.'];
    }

    $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $hash = hash_hmac('sha256', $code, (string)config_value('app_key', __FILE__));
    db()->prepare("UPDATE login_otps
                   SET used_at = NOW()
                   WHERE user_id = ? AND purpose = ? AND used_at IS NULL")
        ->execute([$userId, $purpose]);
    db()->prepare("INSERT INTO login_otps (user_id, purpose, code_hash, attempts, expires_at, created_at)
                   VALUES (?, ?, ?, 0, DATE_ADD(NOW(), INTERVAL 5 MINUTE), NOW())")
        ->execute([$userId, $purpose, $hash]);

    return ['ok' => true, 'code' => $code, 'expires_in' => 300];
}

function otp_verify($userId, $code, $purpose = 'login') {
    ensure_runtime_schema();
    $userId = (int)$userId;
    $purpose = trim((string)$purpose) ?: 'login';
    $code = preg_replace('/\D/', '', (string)$code);
    if (strlen($code) !== 6) return ['ok' => false, 'error' => 'Kode OTP harus 6 digit.'];

    $st = db()->prepare("SELECT *
                         FROM login_otps
                         WHERE user_id = ? AND purpose = ? AND used_at IS NULL
                         ORDER BY id DESC
                         LIMIT 1");
    $st->execute([$userId, $purpose]);
    $otp = $st->fetch();
    if (!$otp) return ['ok' => false, 'error' => 'OTP tidak ditemukan. Silakan minta kode baru.'];
    if (strtotime((string)$otp['expires_at']) < time()) {
        db()->prepare("UPDATE login_otps SET used_at = NOW() WHERE id = ?")->execute([$otp['id']]);
        return ['ok' => false, 'error' => 'OTP sudah kedaluwarsa. Silakan minta kode baru.'];
    }
    if ((int)$otp['attempts'] >= 5) {
        db()->prepare("UPDATE login_otps SET used_at = NOW() WHERE id = ?")->execute([$otp['id']]);
        return ['ok' => false, 'error' => 'Terlalu banyak percobaan OTP. Silakan minta kode baru.'];
    }

    $hash = hash_hmac('sha256', $code, (string)config_value('app_key', __FILE__));
    if (!hash_equals((string)$otp['code_hash'], $hash)) {
        db()->prepare("UPDATE login_otps SET attempts = attempts + 1 WHERE id = ?")->execute([$otp['id']]);
        return ['ok' => false, 'error' => 'Kode OTP salah.'];
    }

    db()->prepare("UPDATE login_otps SET used_at = NOW() WHERE id = ?")->execute([$otp['id']]);
    return ['ok' => true];
}

/* ---------- Reset password ---------- */
function password_reset_issue($userId) {
    ensure_runtime_schema();
    $userId = (int)$userId;
    $cooldownSeconds = 60;
    $maxPerHour = 5;

    $last = db()->prepare("SELECT created_at
                           FROM password_resets
                           WHERE user_id = ?
                           ORDER BY id DESC
                           LIMIT 1");
    $last->execute([$userId]);
    $lastCreated = $last->fetchColumn();
    if ($lastCreated && (time() - strtotime((string)$lastCreated)) < $cooldownSeconds) {
        return ['ok' => false, 'error' => 'Tunggu sekitar 1 menit sebelum meminta link reset lagi.'];
    }

    $count = db()->prepare("SELECT COUNT(*)
                            FROM password_resets
                            WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $count->execute([$userId]);
    if ((int)$count->fetchColumn() >= $maxPerHour) {
        return ['ok' => false, 'error' => 'Terlalu banyak permintaan reset password. Coba lagi dalam 1 jam.'];
    }

    $token = bin2hex(random_bytes(24));
    $hash = hash_hmac('sha256', $token, (string)config_value('app_key', __FILE__));
    db()->prepare("UPDATE password_resets SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL")
        ->execute([$userId]);
    db()->prepare("INSERT INTO password_resets (user_id, token_hash, expires_at, created_at)
                   VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 MINUTE), NOW())")
        ->execute([$userId, $hash]);

    return ['ok' => true, 'token' => $token, 'expires_in' => 1800];
}

function password_reset_verify($token) {
    ensure_runtime_schema();
    $token = trim((string)$token);
    if ($token === '') return ['ok' => false, 'error' => 'Token reset tidak valid.'];

    $hash = hash_hmac('sha256', $token, (string)config_value('app_key', __FILE__));
    $st = db()->prepare("SELECT pr.*, u.id uid, u.name uname, u.email uemail, u.status ustatus
                         FROM password_resets pr
                         JOIN users u ON u.id = pr.user_id
                         WHERE pr.token_hash = ?
                         ORDER BY pr.id DESC
                         LIMIT 1");
    $st->execute([$hash]);
    $row = $st->fetch();
    if (!$row) return ['ok' => false, 'error' => 'Link reset password tidak ditemukan atau tidak valid.'];
    if (!empty($row['used_at'])) return ['ok' => false, 'error' => 'Link reset password sudah dipakai.'];
    if (strtotime((string)$row['expires_at']) < time()) return ['ok' => false, 'error' => 'Link reset password sudah kedaluwarsa.'];
    if (($row['ustatus'] ?? '') !== 'active') return ['ok' => false, 'error' => 'Akun tidak aktif.'];

    return ['ok' => true, 'reset' => $row];
}

function password_reset_mark_used($resetId) {
    db()->prepare("UPDATE password_resets SET used_at = NOW() WHERE id = ?")->execute([(int)$resetId]);
}

/* ---------- Upload aman ---------- */
function handle_upload($field, $folder = 'uploads', $options = []) {
    if (empty($_FILES[$field])) return null;
    $f = $_FILES[$field];
    if ($f['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($f['error'] !== UPLOAD_ERR_OK) return ['error' => 'Upload file gagal. Silakan coba lagi.'];

    $allowed = $options['allowed'] ?? [
        'jpg' => ['image/jpeg', 'image/pjpeg'],
        'jpeg' => ['image/jpeg', 'image/pjpeg'],
        'png' => ['image/png'],
        'webp' => ['image/webp'],
        'pdf' => ['application/pdf', 'application/x-pdf'],
    ];
    $maxSize = (int)($options['max_size'] ?? (5 * 1024 * 1024));

    if ($f['size'] > $maxSize) return ['error' => 'Ukuran file maksimal ' . (int)round($maxSize / 1024 / 1024) . ' MB.'];
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!isset($allowed[$ext])) return ['error' => 'Format file harus ' . strtoupper(implode(', ', array_keys($allowed))) . '.'];
    if (!is_uploaded_file($f['tmp_name'])) return ['error' => 'File upload tidak valid.'];

    $mime = '';
    if (function_exists('finfo_open')) {
        $fi = finfo_open(FILEINFO_MIME_TYPE);
        if ($fi) {
            $mime = (string)finfo_file($fi, $f['tmp_name']);
            finfo_close($fi);
        }
    } elseif (function_exists('mime_content_type')) {
        $mime = (string)mime_content_type($f['tmp_name']);
    }
    if ($mime === '' || !in_array($mime, $allowed[$ext], true)) {
        return ['error' => 'Tipe file tidak sesuai dengan format yang diizinkan.'];
    }

    $dir = storage_path($folder);
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    $name = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $name)) return ['error' => 'Gagal mengunggah file.'];
    return ['file' => 'storage/' . $folder . '/' . $name];
}

/* ---------- Render view ---------- */
function view($path, $data = []) {
    extract($data);
    require dirname(__DIR__) . '/app/views/' . $path . '.php';
}
