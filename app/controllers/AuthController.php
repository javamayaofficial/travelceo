<?php
/**
 * app/controllers/AuthController.php
 */

function auth_render_login($errors = [], $otpPhone = '', $showOtpVerify = false) {
    view('layout', [
        'title'   => 'Masuk — ' . setting('site_name', 'The Travel CEO'),
        'content' => 'auth/login',
        'vars'    => [
            'errors' => $errors,
            'otp_phone' => $otpPhone,
            'show_otp_verify' => $showOtpVerify,
            'google_client_id' => google_client_id(),
        ],
        'bare'    => true,
    ]);
}

function auth_render_admin_login($errors = [], $email = '', $otpPhone = '', $showOtpVerify = false) {
    view('layout', [
        'title'   => 'Login Admin Panel — ' . setting('site_name', 'The Travel CEO'),
        'content' => 'auth/admin_login',
        'vars'    => [
            'errors' => $errors,
            'email' => $email,
            'otp_phone' => $otpPhone,
            'show_otp_verify' => $showOtpVerify,
        ],
        'bare'    => true,
    ]);
}

function auth_home_url($user = null) {
    $user = $user ?: current_user();
    return (($user['role'] ?? 'member') === 'admin') ? admin_url('dashboard') : url('dashboard');
}

function auth_finish_login($user) {
    $_SESSION['uid'] = $user['id'];
    $_SESSION['urole'] = $user['role'];
    $_SESSION['last_activity'] = time();
    unset($_SESSION['otp_login_user_id']);
    session_regenerate_id(true);
    log_activity('login', $user['email']);
    redirect(auth_home_url($user));
}

function auth_find_login_user_by_wa($waInput, &$error = null) {
    $waInput = trim((string)$waInput);
    if ($waInput === '') {
        $error = 'Nomor WhatsApp wajib diisi.';
        return null;
    }

    if (!preg_match('/^(08|62|\+62)[0-9]{7,13}$/', preg_replace('/[\s\-]/', '', $waInput))) {
        $error = 'Masukkan nomor WhatsApp yang valid.';
        return null;
    }

    $wa = wa_normalize($waInput);
    $st = db()->prepare("SELECT * FROM users WHERE wa = ? ORDER BY id DESC LIMIT 2");
    $st->execute([$wa]);
    $rows = $st->fetchAll();
    if (count($rows) > 1) {
        $error = 'Nomor WhatsApp dipakai lebih dari satu akun. Hubungi admin untuk pengecekan data akun.';
        return null;
    }
    return $rows[0] ?? null;
}

function auth_find_login_user_by_email($email) {
    $email = trim((string)$email);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return null;
    $st = db()->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $st->execute([$email]);
    return $st->fetch() ?: null;
}

function auth_google_verify_token($credential) {
    $credential = trim((string)$credential);
    $clientId = google_client_id();
    if ($credential === '' || $clientId === '') return null;

    $data = http_get_json('https://oauth2.googleapis.com/tokeninfo?id_token=' . rawurlencode($credential));
    if (!$data) return null;
    if (($data['aud'] ?? '') !== $clientId) return null;
    if (($data['email_verified'] ?? '') !== 'true') return null;
    if (empty($data['email'])) return null;
    return $data;
}

function auth_find_login_admin_by_wa($waInput, &$error = null) {
    $u = auth_find_login_user_by_wa($waInput, $error);
    if ($error || !$u) return $u;
    if (($u['role'] ?? 'member') !== 'admin') {
        $error = 'Nomor WhatsApp ini bukan akun admin.';
        return null;
    }
    return $u;
}

function auth_register() {
    if (is_logged_in()) redirect(auth_home_url());
    $errors = [];
    $old = ['name' => '', 'email' => '', 'wa' => ''];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_csrf();
        $old['name']  = trim($_POST['name'] ?? '');
        $old['email'] = trim($_POST['email'] ?? '');
        $old['wa']    = trim($_POST['wa'] ?? '');
        $pass         = $_POST['password'] ?? '';
        $normalizedWa = wa_normalize($old['wa']);

        if (mb_strlen($old['name']) < 3) $errors[] = 'Nama minimal 3 karakter.';
        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid.';
        if (!preg_match('/^(08|62|\+62)[0-9]{7,13}$/', preg_replace('/[\s\-]/', '', $old['wa']))) $errors[] = 'Nomor WhatsApp tidak valid (contoh: 08xxxxxxxxxx).';
        if (strlen($pass) < 8) $errors[] = 'Password minimal 8 karakter.';

        if (!$errors) {
            $chk = db()->prepare("SELECT 1 FROM users WHERE email = ?");
            $chk->execute([$old['email']]);
            if ($chk->fetchColumn()) $errors[] = 'Email sudah terdaftar. Silakan login.';
        }

        if (!$errors && $normalizedWa !== '') {
            $chkWa = db()->prepare("SELECT 1 FROM users WHERE wa = ?");
            $chkWa->execute([$normalizedWa]);
            if ($chkWa->fetchColumn()) $errors[] = 'Nomor WhatsApp sudah dipakai akun lain. Gunakan nomor lain atau login dengan akun yang sudah ada.';
        }

        if (!$errors) {
            // ref_code unik dari nama
            $base = preg_replace('/[^a-z0-9]/', '', strtolower(explode(' ', $old['name'])[0])) ?: 'member';
            $ref = $base; $i = 1;
            while (true) {
                $q = db()->prepare("SELECT 1 FROM users WHERE ref_code = ?");
                $q->execute([$ref]);
                if (!$q->fetchColumn()) break;
                $ref = $base . $i; $i++;
            }
            // referrer dari cookie
            $referred_by = null;
            if (!empty($_COOKIE['tc_ref'])) {
                $r = db()->prepare("SELECT id FROM users WHERE ref_code = ?");
                $r->execute([preg_replace('/[^a-zA-Z0-9_\-]/', '', $_COOKIE['tc_ref'])]);
                $referred_by = $r->fetchColumn() ?: null;
            }

            $ins = db()->prepare("INSERT INTO users (name,email,password,wa,role,status,ref_code,referred_by,created_at)
                                  VALUES (?,?,?,?,?,?,?,?,NOW())");
            $ins->execute([
                $old['name'], $old['email'], password_hash($pass, PASSWORD_DEFAULT),
                $normalizedWa, 'member', 'active', $ref, $referred_by
            ]);
            $uid = db()->lastInsertId();
            $_SESSION['uid'] = $uid;
            $_SESSION['urole'] = 'member';
            $_SESSION['last_activity'] = time();
            session_regenerate_id(true);

            log_activity('register', 'User baru: ' . $old['email']);
            fonnte_send($old['wa'], wa_template('register', ['nama' => $old['name']]));
            $mail = mail_template('register', ['nama' => $old['name']]);
            mailketing_send($old['email'], $mail['subject'], $mail['html']);
            flash('success', 'Pendaftaran berhasil. Selamat datang, ' . $old['name'] . '! 🎉');
            redirect(url('dashboard'));
        }
    }

    view('layout', [
        'title'   => 'Daftar — ' . setting('site_name', 'The Travel CEO'),
        'content' => 'auth/register',
        'vars'    => ['errors' => $errors, 'old' => $old],
        'bare'    => true,
    ]);
}

function auth_login() {
    if (is_logged_in()) redirect(auth_home_url());
    auth_render_login();
}

function auth_admin_login() {
    if (is_logged_in()) {
        $u = current_user();
        redirect(($u['role'] ?? 'member') === 'admin' ? admin_url('dashboard') : url('dashboard'));
    }

    $errors = [];
    $email = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_csrf();
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';

        $rate = login_rate_limit_status($email);
        if ($rate['blocked']) {
            $wait = max(1, (int)ceil($rate['retry_after'] / 60));
            $errors[] = 'Terlalu banyak percobaan login. Coba lagi dalam ' . $wait . ' menit.';
        } else {
            $st = db()->prepare("SELECT * FROM users WHERE email = ?");
            $st->execute([$email]);
            $u = $st->fetch();
            if ($u && password_verify($pass, $u['password'])) {
                login_rate_limit_clear($email);
                if ($u['status'] !== 'active') {
                    $errors[] = 'Akun admin belum aktif. Silakan hubungi super admin.';
                } elseif (($u['role'] ?? 'member') !== 'admin') {
                    $errors[] = 'Halaman ini khusus admin panel.';
                } else {
                    auth_finish_login($u);
                }
            } else {
                login_rate_limit_hit($email);
                $errors[] = 'Email atau password admin salah.';
            }
        }
    }

    auth_render_admin_login($errors, $email);
}

function auth_admin_otp_request() {
    if (is_logged_in()) redirect(auth_home_url());
    $errors = [];
    $phone = trim($_POST['phone'] ?? '');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_csrf();
        $findError = null;
        $u = auth_find_login_admin_by_wa($phone, $findError);
        if ($findError) {
            $errors[] = $findError;
        } elseif (!$u || $u['status'] !== 'active') {
            $errors[] = 'Akun admin tidak ditemukan atau belum aktif.';
        } else {
            $issued = otp_issue((int)$u['id'], 'admin_login');
            if (!$issued['ok']) {
                $errors[] = $issued['error'];
            } elseif (empty($u['wa'])) {
                $errors[] = 'Akun admin ini belum memiliki nomor WhatsApp aktif.';
            } elseif (!fonnte_token()) {
                $errors[] = 'Token Fonnte belum diatur.';
            } else {
                $message = wa_template('otp_login', [
                    'nama' => $u['name'],
                    'kode' => $issued['code'],
                    'expired' => '5 menit',
                ]);
                if (!fonnte_send($u['wa'], $message)) {
                    $errors[] = 'Gagal mengirim OTP ke WhatsApp admin.';
                } else {
                    log_activity('otp_admin_login_sent_wa', $u['email']);
                    flash('success', 'Kode OTP admin sudah dikirim ke WhatsApp ' . wa_mask($u['wa']) . '.');
                    auth_render_admin_login([], '', $phone, true);
                    return;
                }
            }
        }
    }

    auth_render_admin_login($errors, '', $phone, !empty($phone));
}

function auth_admin_otp_verify() {
    if (is_logged_in()) redirect(auth_home_url());
    $errors = [];
    $phone = trim($_POST['phone'] ?? '');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_csrf();
        $code = trim($_POST['otp_code'] ?? '');
        $findError = null;
        $u = auth_find_login_admin_by_wa($phone, $findError);
        if ($findError) {
            $errors[] = $findError;
        } elseif (!$u || $u['status'] !== 'active') {
            $errors[] = 'Akun admin tidak ditemukan atau belum aktif.';
        } else {
            $verified = otp_verify((int)$u['id'], $code, 'admin_login');
            if (!$verified['ok']) {
                $errors[] = $verified['error'];
            } else {
                log_activity('otp_admin_login_verified', $u['email']);
                auth_finish_login($u);
            }
        }
    }

    auth_render_admin_login($errors, '', $phone, true);
}

function auth_login_otp_request() {
    if (is_logged_in()) redirect(auth_home_url());
    $errors = [];
    $phone = trim($_POST['phone'] ?? '');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_csrf();
        $findError = null;
        $u = auth_find_login_user_by_wa($phone, $findError);
        if ($findError) {
            $errors[] = $findError;
        } elseif (!$u || $u['status'] !== 'active') {
            $errors[] = 'Akun tidak ditemukan atau belum aktif.';
        } else {
            $issued = otp_issue((int)$u['id'], 'login');
            if (!$issued['ok']) {
                $errors[] = $issued['error'];
            } else {
                if (empty($u['wa'])) {
                    $errors[] = 'Akun ini belum memiliki nomor WhatsApp aktif.';
                } elseif (!fonnte_token()) {
                    $errors[] = 'Token Fonnte belum diatur.';
                } else {
                    $message = wa_template('otp_login', [
                        'nama' => $u['name'],
                        'kode' => $issued['code'],
                        'expired' => '5 menit',
                    ]);
                    if (!fonnte_send($u['wa'], $message)) {
                        $errors[] = 'Gagal mengirim OTP ke WhatsApp. Periksa token Fonnte Anda.';
                    } else {
                        log_activity('otp_login_sent_wa', $u['email']);
                        flash('success', 'Kode OTP sudah dikirim ke WhatsApp ' . wa_mask($u['wa']) . '.');
                        auth_render_login([], $phone, true);
                        return;
                    }
                }
            }
        }
    }

    auth_render_login($errors, $phone, !empty($phone));
}

function auth_login_otp_verify() {
    if (is_logged_in()) redirect(auth_home_url());
    $errors = [];
    $phone = trim($_POST['phone'] ?? '');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_csrf();
        $code = trim($_POST['otp_code'] ?? '');
        $findError = null;
        $u = auth_find_login_user_by_wa($phone, $findError);
        if ($findError) {
            $errors[] = $findError;
        } elseif (!$u || $u['status'] !== 'active') {
            $errors[] = 'Akun tidak ditemukan atau belum aktif.';
        } else {
            $verified = otp_verify((int)$u['id'], $code, 'login');
            if (!$verified['ok']) {
                $errors[] = $verified['error'];
            } else {
                log_activity('otp_login_verified', $u['email']);
                auth_finish_login($u);
            }
        }
    }

    auth_render_login($errors, $phone, true);
}

function auth_google_login() {
    if (is_logged_in()) redirect(auth_home_url());
    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_csrf();
        $credential = trim((string)($_POST['credential'] ?? ''));
        if ($credential === '') {
            $errors[] = 'Token login Google tidak ditemukan.';
        } else {
            $payload = auth_google_verify_token($credential);
            if (!$payload) {
                $errors[] = 'Verifikasi akun Google gagal. Coba lagi.';
            } else {
                $u = auth_find_login_user_by_email((string)$payload['email']);
                if (!$u || ($u['role'] ?? 'member') !== 'member') {
                    $errors[] = 'Email Google ini belum terdaftar sebagai member.';
                } elseif ($u['status'] !== 'active') {
                    $errors[] = 'Akun member belum aktif.';
                } else {
                    log_activity('google_login', $u['email']);
                    auth_finish_login($u);
                }
            }
        }
    }

    auth_render_login($errors, '', false);
}

function auth_logout() {
    log_activity('logout');
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'] ?? '', (bool)$params['secure'], (bool)$params['httponly']);
    }
    session_destroy();
    redirect(url('home'));
}

function auth_forgot() {
    if (is_logged_in()) redirect(auth_home_url());
    $errors = [];
    $email = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_csrf();
        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Masukkan alamat email yang valid.';
        } else {
            $st = db()->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $st->execute([$email]);
            $u = $st->fetch();

            if ($u && $u['status'] === 'active' && mailketing_token() && mail_sender_email()) {
                $issued = password_reset_issue((int)$u['id']);
                if (!$issued['ok']) {
                    $errors[] = $issued['error'];
                } else {
                    $link = url('reset-password', ['token' => $issued['token']]);
                    $mail = mail_template('reset_password', [
                        'nama' => $u['name'],
                        'link' => $link,
                        'expired' => '30 menit',
                    ]);
                    if (mailketing_send($u['email'], $mail['subject'], $mail['html'])) {
                        log_activity('password_reset_request', $u['email']);
                    } else {
                        log_activity('password_reset_email_failed', $u['email']);
                    }
                }
            } elseif ($u) {
                log_activity('password_reset_mail_unavailable', $u['email']);
            }

            if (!$errors) {
                flash('success', 'Jika email Anda terdaftar, kami sudah mengirim link reset password ke inbox Anda.');
                redirect(url('forgot'));
            }
        }
    }

    view('layout', [
        'title'   => 'Lupa Password — ' . setting('site_name', 'The Travel CEO'),
        'content' => 'auth/forgot',
        'vars'    => ['errors' => $errors, 'email' => $email],
        'bare'    => true,
    ]);
}

function auth_reset_password() {
    if (is_logged_in()) redirect(auth_home_url());
    $errors = [];
    $token = trim($_GET['token'] ?? $_POST['token'] ?? '');
    $check = password_reset_verify($token);
    $reset = $check['reset'] ?? null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_csrf();
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        if (!$check['ok']) {
            $errors[] = $check['error'];
        } else {
            if (strlen($password) < 8) $errors[] = 'Password baru minimal 8 karakter.';
            if (!hash_equals($password, $passwordConfirm)) $errors[] = 'Konfirmasi password tidak cocok.';
            if (!$errors) {
                db()->prepare("UPDATE users SET password = ? WHERE id = ?")
                    ->execute([password_hash($password, PASSWORD_DEFAULT), (int)$reset['user_id']]);
                password_reset_mark_used((int)$reset['id']);
                login_rate_limit_clear($reset['uemail'] ?? '');
                log_activity('password_reset_success', $reset['uemail'] ?? '');
                flash('success', 'Password berhasil diperbarui. Silakan login dengan password baru Anda.');
                redirect(login_member_url());
            }
        }
    } elseif (!$check['ok']) {
        $errors[] = $check['error'];
    }

    view('layout', [
        'title'   => 'Reset Password — ' . setting('site_name', 'The Travel CEO'),
        'content' => 'auth/reset_password',
        'vars'    => ['errors' => $errors, 'token' => $token, 'reset' => $reset],
        'bare'    => true,
    ]);
}
