<?php
/**
 * app/controllers/CheckoutController.php
 */

function _find_product_by_slug($slug) {
    $st = db()->prepare("SELECT * FROM products WHERE slug = ? AND status = 'publish'");
    $st->execute([$slug]);
    return $st->fetch();
}

function _checkout_payment_config() {
    $methods = [];
    $legacy = [
        1 => ['name' => 'BCA', 'account' => setting('bank_bca', '')],
        2 => ['name' => 'Mandiri', 'account' => setting('bank_mandiri', '')],
        3 => ['name' => 'BSI', 'account' => setting('bank_bsi', '')],
        4 => ['name' => 'BRI', 'account' => ''],
        5 => ['name' => 'BNI', 'account' => ''],
        6 => ['name' => 'CIMB Niaga', 'account' => ''],
    ];

    for ($i = 1; $i <= 6; $i++) {
        $fallback = $legacy[$i] ?? ['name' => '', 'account' => ''];
        $name = trim((string)setting('checkout_bank_' . $i . '_name', $fallback['name']));
        $account = trim((string)setting('checkout_bank_' . $i . '_account', $fallback['account']));
        $enabled = setting('checkout_bank_' . $i . '_enabled', $i <= 3 ? '1' : '0') === '1';
        if ($enabled && $name !== '') {
            $methods[] = [
                'key' => 'bank_' . $i,
                'label' => $name,
                'detail' => $account !== '' ? $account : 'Nomor rekening diatur admin',
                'type' => 'bank',
            ];
        }
    }

    if (setting('checkout_qris_enabled', '0') === '1') {
        $methods[] = [
            'key' => 'qris',
            'label' => trim((string)setting('checkout_qris_label', 'QRIS')),
            'detail' => trim((string)setting('checkout_qris_note', 'Scan QR code berikut untuk melakukan pembayaran via QRIS.')),
            'type' => 'qris',
            'image' => trim((string)setting('checkout_qris_image', '')),
        ];
    }

    $gateways = [];
    foreach ([
        'xendit' => 'Xendit',
        'duitku' => 'Duitku',
        'midtrans' => 'Midtrans',
        'tripay' => 'Tripay',
    ] as $key => $label) {
        if (setting('checkout_gateway_' . $key, '0') === '1') {
            $gateways[] = ['key' => $key, 'label' => $label];
        }
    }

    return [
        'methods' => $methods,
        'gateways' => $gateways,
    ];
}

function _checkout_old_input($source = []) {
    return [
        'coupon' => trim((string)($source['coupon'] ?? '')),
        'bank' => trim((string)($source['bank'] ?? '')),
        'note' => trim((string)($source['note'] ?? '')),
        'buyer_name' => trim((string)($source['buyer_name'] ?? '')),
        'buyer_email' => trim((string)($source['buyer_email'] ?? '')),
        'buyer_wa' => trim((string)($source['buyer_wa'] ?? '')),
    ];
}

function _checkout_render($product, $errors = [], $old = [], $buyer = null) {
    $paymentConfig = _checkout_payment_config();
    $seatStats = product_seat_stats((int)$product['id']);
    view('layout', [
        'title'   => 'Checkout — ' . $product['title'],
        'content' => 'checkout',
        'vars'    => [
            'product' => $product,
            'seat_stats' => $seatStats,
            'payment_methods' => $paymentConfig['methods'],
            'gateway_options' => $paymentConfig['gateways'],
            'errors' => $errors,
            'old' => $old,
            'buyer' => $buyer,
            'is_logged_in' => $buyer !== null,
        ],
    ]);
}

function _checkout_assign_login($user) {
    $_SESSION['uid'] = $user['id'];
    $_SESSION['urole'] = $user['role'];
    $_SESSION['last_activity'] = time();
    session_regenerate_id(true);
}

function _checkout_make_ref_code($name, PDO $pdo) {
    $base = preg_replace('/[^a-z0-9]/', '', strtolower(explode(' ', trim((string)$name))[0] ?? '')) ?: 'member';
    $ref = $base;
    $i = 1;
    while (true) {
        $q = $pdo->prepare("SELECT 1 FROM users WHERE ref_code = ?");
        $q->execute([$ref]);
        if (!$q->fetchColumn()) return $ref;
        $ref = $base . $i;
        $i++;
    }
}

function _checkout_generate_password($length = 10) {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
    $max = strlen($chars) - 1;
    $pass = '';
    for ($i = 0; $i < $length; $i++) {
        $pass .= $chars[random_int(0, $max)];
    }
    return $pass;
}

/** Hitung diskon dari kupon. Return [valid(bool), discount(int), coupon|null, message] */
function _apply_coupon($code, $product, $forUpdate = false) {
    $code = trim($code);
    if ($code === '') return [true, 0, null, ''];
    $sql = "SELECT * FROM coupons WHERE code = ? AND status = 'active'";
    if ($forUpdate) $sql .= " FOR UPDATE";
    $st = db()->prepare($sql);
    $st->execute([$code]);
    $c = $st->fetch();
    if (!$c) return [false, 0, null, 'Kode kupon tidak ditemukan.'];

    $today = date('Y-m-d');
    if ($c['start_date'] && $today < $c['start_date']) return [false, 0, null, 'Kupon belum berlaku.'];
    if ($c['end_date']   && $today > $c['end_date'])   return [false, 0, null, 'Kupon sudah kedaluwarsa.'];
    if ($c['max_use'] > 0 && $c['used_count'] >= $c['max_use']) return [false, 0, null, 'Kupon sudah mencapai batas pemakaian.'];
    if ($c['product_id'] && (int)$c['product_id'] !== (int)$product['id']) return [false, 0, null, 'Kupon tidak berlaku untuk produk ini.'];

    $disc = 0;
    if ($c['percent'] > 0) $disc += (int)round($product['price'] * $c['percent'] / 100);
    if ($c['nominal'] > 0) $disc += (int)$c['nominal'];
    if ($disc > $product['price']) $disc = $product['price'];
    return [true, $disc, $c, 'Kupon berhasil diterapkan.'];
}

function checkout_show() {
    $product = _find_product_by_slug($_GET['slug'] ?? '');
    if (!$product) { flash('error', 'Produk tidak ditemukan.'); redirect(url('products')); }
    $buyer = !empty($_SESSION['uid']) ? current_user() : null;
    _checkout_render($product, [], [], $buyer);
}

function checkout_process() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect(url('products'));
    check_csrf();
    $product = _find_product_by_slug($_POST['slug'] ?? '');
    if (!$product) { flash('error', 'Produk tidak ditemukan.'); redirect(url('products')); }

    $isLoggedIn = !empty($_SESSION['uid']);
    $u = $isLoggedIn ? current_user() : null;
    $bank = $_POST['bank'] ?? '';
    $note = trim($_POST['note'] ?? '');
    $couponCode = trim($_POST['coupon'] ?? '');
    $old = _checkout_old_input($_POST);
    $errors = [];
    $normalizedWa = '';
    $existingUser = null;
    $accountCreated = false;
    $generatedPassword = '';
    $paymentMethods = _checkout_payment_config()['methods'];
    $methodMap = [];
    foreach ($paymentMethods as $method) {
        $methodMap[$method['key']] = $method;
    }

    [$cvalid, $discount, $coupon, $cmsg] = _apply_coupon($couponCode, $product);
    if (!$cvalid) $errors[] = $cmsg;
    $seatStats = product_seat_stats((int)$product['id']);
    if (!empty($seatStats['is_full'])) $errors[] = 'Maaf, kuota peserta untuk program ini sudah penuh.';
    if (!$methodMap) $errors[] = 'Metode pembayaran belum diatur admin.';
    if ($bank === '' || !isset($methodMap[$bank])) $errors[] = 'Pilih metode pembayaran.';

    if (!$u) {
        $buyerName = trim($_POST['buyer_name'] ?? '');
        $buyerEmail = trim($_POST['buyer_email'] ?? '');
        $buyerWa = trim($_POST['buyer_wa'] ?? '');
        $normalizedWa = wa_normalize($buyerWa);

        if (mb_strlen($buyerName) < 3) $errors[] = 'Nama pembeli minimal 3 karakter.';
        if (!filter_var($buyerEmail, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email pembeli tidak valid.';
        if (!preg_match('/^(08|62|\+62)[0-9]{7,13}$/', preg_replace('/[\s\-]/', '', $buyerWa))) {
            $errors[] = 'Nomor WhatsApp pembeli tidak valid (contoh: 08xxxxxxxxxx).';
        }

        if (!$errors) {
            $st = db()->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $st->execute([$buyerEmail]);
            $existingUser = $st->fetch() ?: null;

            if ($existingUser) {
                $errors[] = 'Email ini sudah terdaftar. Silakan login dulu sebelum melakukan checkout.';
            }
            if (!$existingUser && $normalizedWa !== '') {
                $chkWa = db()->prepare("SELECT 1 FROM users WHERE wa = ?");
                $chkWa->execute([$normalizedWa]);
                if ($chkWa->fetchColumn()) $errors[] = 'Nomor WhatsApp sudah dipakai akun lain. Silakan login dulu dengan akun yang sudah ada.';
            }
        }
    }

    if ($errors) {
        _checkout_render($product, $errors, $old, $u);
        return;
    }

    $pdo = db();
    $code = 'TRX' . date('ymd') . strtoupper(bin2hex(random_bytes(2)));
    $proofFile = null;

    try {
        $pdo->beginTransaction();

        [$cvalid, $discount, $coupon, $cmsg] = _apply_coupon($couponCode, $product, true);
        if (!$cvalid) throw new RuntimeException($cmsg);

        if (!$u) {
            $buyerName = trim($_POST['buyer_name'] ?? '');
            $buyerEmail = trim($_POST['buyer_email'] ?? '');

            if ($existingUser) {
                $u = $existingUser;
                $updates = [];
                $params = [];
                if ($buyerName !== '' && $buyerName !== (string)$u['name']) {
                    $updates[] = 'name = ?';
                    $params[] = $buyerName;
                    $u['name'] = $buyerName;
                }
                if ($normalizedWa !== '' && $normalizedWa !== (string)($u['wa'] ?? '')) {
                    $updates[] = 'wa = ?';
                    $params[] = $normalizedWa;
                    $u['wa'] = $normalizedWa;
                }
                if ($updates) {
                    $params[] = $u['id'];
                    $pdo->prepare("UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?")->execute($params);
                }
            } else {
                $refCodeUser = _checkout_make_ref_code($buyerName, $pdo);
                $referredBy = null;
                $generatedPassword = _checkout_generate_password();
                if (!empty($_COOKIE['tc_ref'])) {
                    $r = $pdo->prepare("SELECT id FROM users WHERE ref_code = ?");
                    $r->execute([preg_replace('/[^a-zA-Z0-9_\-]/', '', $_COOKIE['tc_ref'])]);
                    $referredBy = $r->fetchColumn() ?: null;
                }

                $pdo->prepare("INSERT INTO users (name,email,password,wa,role,status,ref_code,referred_by,created_at)
                               VALUES (?,?,?,?,?,?,?,?,NOW())")
                    ->execute([
                        $buyerName,
                        $buyerEmail,
                        password_hash($generatedPassword, PASSWORD_DEFAULT),
                        $normalizedWa,
                        'member',
                        'active',
                        $refCodeUser,
                        $referredBy,
                    ]);

                $u = [
                    'id' => (int)$pdo->lastInsertId(),
                    'name' => $buyerName,
                    'email' => $buyerEmail,
                    'wa' => $normalizedWa,
                    'role' => 'member',
                    'status' => 'active',
                    'ref_code' => $refCodeUser,
                    'referred_by' => $referredBy,
                ];
                $accountCreated = true;
            }
        }

        $total = max(0, (int)$product['price'] - (int)$discount);
        $ref = !empty($_COOKIE['tc_ref']) ? preg_replace('/[^a-zA-Z0-9_\-]/', '', $_COOKIE['tc_ref']) : null;
        if ($coupon && $coupon['affiliate_id']) {
            $r = $pdo->prepare("SELECT ref_code FROM users WHERE id = ?");
            $r->execute([$coupon['affiliate_id']]);
            $ref = $r->fetchColumn() ?: $ref;
        }

        $ins = $pdo->prepare("INSERT INTO transactions
            (code,user_id,product_id,bank,amount,coupon_code,discount,total,proof,note,ref_code,status,created_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?, 'pending', NOW())");
        $ins->execute([
            $code, $u['id'], $product['id'], $methodMap[$bank]['label'], $product['price'],
            $coupon ? $coupon['code'] : null, $discount, $total,
            $proofFile, $note, $ref
        ]);

        if ($coupon) {
            $pdo->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?")->execute([$coupon['id']]);
        }

        $pdo->commit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        _checkout_render($product, [$e->getMessage()], $old, $u);
        return;
    }

    if (!$isLoggedIn) {
        _checkout_assign_login($u);
    }

    if ($accountCreated) {
        log_activity('register_checkout', 'User baru dari checkout: ' . $u['email']);
        $loginLink = login_member_url();
        fonnte_send($u['wa'], wa_template('checkout_access', [
            'nama' => $u['name'],
            'email' => $u['email'],
            'wa' => $u['wa'],
            'login' => $loginLink,
        ]));
        $accessMail = mail_template('checkout_access', [
            'nama' => $u['name'],
            'email' => $u['email'],
            'wa' => $u['wa'],
            'login' => $loginLink,
        ]);
        mailketing_send($u['email'], $accessMail['subject'], $accessMail['html']);
    }
    log_activity('checkout', "Order $code untuk {$product['title']}");
    fonnte_send($u['wa'], wa_template('purchase', [
        'nama' => $u['name'], 'produk' => $product['title'], 'kode' => $code, 'total' => rupiah($total),
    ]));
    $mail = mail_template('purchase', [
        'nama' => $u['name'], 'produk' => $product['title'], 'kode' => $code, 'total' => rupiah($total),
    ]);
    mailketing_send($u['email'], $mail['subject'], $mail['html']);
    // Notifikasi ke admin
    if ($adminWa = setting('site_wa', '')) {
        fonnte_send($adminWa, "🔔 Order baru {$code}\nProduk: {$product['title']}\nDari: {$u['name']}\nTotal: " . rupiah($total) . "\nCek admin untuk verifikasi.");
    }
    if ($adminEmail = trim((string)setting('site_email', ''))) {
        $adminMail = mail_template('admin_purchase', [
            'kode' => $code, 'produk' => $product['title'], 'nama' => $u['name'], 'total' => rupiah($total),
        ]);
        mailketing_send($adminEmail, $adminMail['subject'], $adminMail['html']);
    }

    flash('success', 'Pesanan diterima! Mohon tunggu verifikasi pembayaran dari admin.');
    view('layout', [
        'title' => 'Pesanan Diterima', 'content' => 'checkout_success',
        'vars' => ['code' => $code, 'product' => $product, 'total' => $total, 'account_created' => $accountCreated],
    ]);
}
