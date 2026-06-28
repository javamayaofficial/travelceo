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

    if (setting('checkout_gateway_duitku', '0') === '1') {
        $methods[] = [
            'key' => 'duitku',
            'label' => 'Duitku',
            'detail' => 'Lanjutkan pembayaran otomatis melalui payment gateway Duitku.',
            'type' => 'gateway',
            'gateway' => 'duitku',
            'is_ready' => _duitku_is_ready(),
        ];
    }

    $gateways = [];
    foreach ([
        'xendit' => 'Xendit',
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

function _duitku_config() {
    return [
        'merchant_code' => trim((string)setting('duitku_merchant_code', '')),
        'api_key' => trim((string)setting('duitku_api_key', '')),
        'sandbox' => setting('duitku_sandbox', '1') === '1',
        'expiry_period' => max(5, (int)setting('duitku_expiry_period', '60')),
    ];
}

function _duitku_is_ready() {
    $cfg = _duitku_config();
    return $cfg['merchant_code'] !== '' && $cfg['api_key'] !== '';
}

function _duitku_create_invoice_url() {
    $cfg = _duitku_config();
    return $cfg['sandbox']
        ? 'https://api-sandbox.duitku.com/api/merchant/createInvoice'
        : 'https://api-prod.duitku.com/api/merchant/createInvoice';
}

function _duitku_create_payment(array $transaction, array $user, array $product) {
    $cfg = _duitku_config();
    if (!$cfg['merchant_code'] || !$cfg['api_key']) {
        throw new RuntimeException('Konfigurasi Duitku belum lengkap. Silakan isi Merchant Code dan API Key di pengaturan admin.');
    }

    $amount = (int)($transaction['total'] ?? 0);
    $orderId = (string)($transaction['code'] ?? '');
    $merchantCode = (string)$cfg['merchant_code'];
    $apiKey = (string)$cfg['api_key'];
    $fullName = trim((string)($user['name'] ?? 'Pelanggan'));
    $nameParts = preg_split('/\s+/', $fullName) ?: [];
    $firstName = $nameParts[0] ?? 'Pelanggan';
    $lastName = trim(implode(' ', array_slice($nameParts, 1)));
    if ($lastName === '') $lastName = '-';
    $phone = trim((string)($user['wa'] ?? ''));
    $email = trim((string)($user['email'] ?? ''));
    $callbackUrl = url('checkout-duitku-callback');
    $returnUrl = url('checkout-result', ['code' => $orderId]);
    $timestamp = (string)round(microtime(true) * 1000);
    $signature = hash_hmac('sha256', $merchantCode . $timestamp, $apiKey);

    $address = [
        'firstName' => $firstName,
        'lastName' => $lastName,
        'address' => trim((string)setting('site_address', 'Indonesia')),
        'city' => 'Jakarta',
        'postalCode' => '10110',
        'phone' => $phone,
        'countryCode' => 'ID',
    ];
    $payload = [
        'paymentAmount' => $amount,
        'merchantOrderId' => $orderId,
        'productDetails' => (string)($product['title'] ?? 'Pembelian produk'),
        'additionalParam' => (string)($product['slug'] ?? ''),
        'merchantUserInfo' => (string)($transaction['user_id'] ?? ''),
        'customerVaName' => $fullName,
        'email' => $email,
        'phoneNumber' => $phone,
        'itemDetails' => [[
            'name' => (string)($product['title'] ?? 'Produk'),
            'price' => $amount,
            'quantity' => 1,
        ]],
        'customerDetail' => [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'phoneNumber' => $phone,
            'billingAddress' => $address,
            'shippingAddress' => $address,
        ],
        'callbackUrl' => $callbackUrl,
        'returnUrl' => $returnUrl,
        'expiryPeriod' => $cfg['expiry_period'],
        'paymentMethod' => '',
    ];

    $result = http_post_json(_duitku_create_invoice_url(), $payload, [
        'x-duitku-signature: ' . $signature,
        'x-duitku-timestamp: ' . $timestamp,
        'x-duitku-merchantcode: ' . $merchantCode,
    ]);
    if (!$result['ok'] || empty($result['body']['paymentUrl'])) {
        $message = 'Gagal membuat invoice Duitku.';
        if (!empty($result['body']['Message'])) $message .= ' ' . $result['body']['Message'];
        elseif (!empty($result['error'])) $message .= ' ' . $result['error'];
        throw new RuntimeException(trim($message));
    }

    return [
        'payment_url' => (string)($result['body']['paymentUrl'] ?? ''),
        'reference' => (string)($result['body']['reference'] ?? ''),
        'payload' => $result['body'],
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

function _checkout_archive_deleted_user_identity(PDO $pdo, array $user, $clearEmail = true, $clearWa = true) {
    $updates = [];
    $params = [];
    $stamp = date('YmdHis');

    if ($clearEmail && !empty($user['email'])) {
        $archivedEmail = 'deleted+' . (int)$user['id'] . '.' . $stamp . '@deleted.local';
        $updates[] = 'email = ?';
        $params[] = $archivedEmail;
    }
    if ($clearWa && !empty($user['wa'])) {
        $updates[] = 'wa = NULL';
    }
    if (!$updates) {
        return;
    }

    $params[] = (int)$user['id'];
    $pdo->prepare("UPDATE users SET " . implode(', ', $updates) . " WHERE id = ? AND deleted_at IS NOT NULL")
        ->execute($params);
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

function checkout_result() {
    $code = trim((string)($_GET['code'] ?? ''));
    if ($code === '') { flash('error', 'Kode transaksi tidak valid.'); redirect(url('products')); }

    $st = db()->prepare("SELECT t.*, p.title AS product_title, p.slug AS product_slug, p.type AS product_type
                         FROM transactions t
                         JOIN products p ON p.id = t.product_id
                         WHERE t.code = ?
                         LIMIT 1");
    $st->execute([$code]);
    $tx = $st->fetch();
    if (!$tx) { flash('error', 'Transaksi tidak ditemukan.'); redirect(url('products')); }

    $paymentMethod = null;
    $paymentConfig = _checkout_payment_config();
    foreach ($paymentConfig['methods'] as $method) {
        if (($method['label'] ?? '') === ($tx['bank'] ?? '') || ($method['key'] ?? '') === ($tx['payment_provider'] ?? '')) {
            $paymentMethod = $method;
            break;
        }
    }
    if (!$paymentMethod && ($tx['payment_provider'] ?? '') === 'duitku') {
        $paymentMethod = [
            'type' => 'gateway',
            'gateway' => 'duitku',
            'label' => 'Duitku',
            'detail' => 'Pembayaran diproses melalui Duitku.',
        ];
    }

    view('layout', [
        'title' => 'Status Pesanan',
        'content' => 'checkout_success',
        'vars' => [
            'code' => $tx['code'],
            'product' => ['title' => $tx['product_title'], 'slug' => $tx['product_slug'], 'type' => $tx['product_type']],
            'total' => (int)$tx['total'],
            'account_created' => false,
            'payment_method' => $paymentMethod,
            'payment_status' => (string)($tx['status'] ?? 'pending'),
            'payment_url' => (string)($tx['payment_url'] ?? ''),
        ],
    ]);
}

function checkout_duitku_callback() {
    $merchantOrderId = trim((string)($_POST['merchantOrderId'] ?? $_POST['merchant_order_id'] ?? ''));
    if ($merchantOrderId === '') {
        http_response_code(400);
        echo 'INVALID';
        return;
    }

    $pdo = db();
    $notifyApproved = false;
    $notifyRejected = false;
    $txForNotify = null;
    try {
        $pdo->beginTransaction();
        $st = $pdo->prepare("SELECT t.*, p.title AS ptitle, p.type AS ptype, u.name AS uname, u.wa AS uwa, u.email AS uemail
                             FROM transactions t
                             JOIN products p ON p.id = t.product_id
                             JOIN users u ON u.id = t.user_id
                             WHERE t.code = ?
                             LIMIT 1
                             FOR UPDATE");
        $st->execute([$merchantOrderId]);
        $t = $st->fetch();
        if (!$t) throw new RuntimeException('Transaksi tidak ditemukan.');

        $payload = json_encode($_POST);
        $resultCode = strtoupper(trim((string)($_POST['resultCode'] ?? $_POST['result_code'] ?? '')));
        $reference = trim((string)($_POST['reference'] ?? ''));
        $status = ($resultCode === '00') ? 'approved' : (($resultCode === '01') ? 'pending' : 'rejected');

        $pdo->prepare("UPDATE transactions
                       SET payment_provider = 'duitku',
                           payment_reference = ?,
                           payment_payload = ?,
                           payment_url = COALESCE(payment_url, ?)
                       WHERE id = ?")
            ->execute([$reference ?: null, $payload ?: null, $t['payment_url'] ?? null, $t['id']]);

        if ($status === 'approved' && $t['status'] === 'pending') {
            if (ticket_is_eligible($t['ptype'] ?? '', $t['ptitle'] ?? '')) {
                $productLock = $pdo->prepare("SELECT seat_quota FROM products WHERE id = ? LIMIT 1 FOR UPDATE");
                $productLock->execute([(int)$t['product_id']]);
                $seatQuota = (int)$productLock->fetchColumn();
                $approvedCount = 0;
                if ($seatQuota > 0) {
                    $approvedSt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE product_id = ? AND status = 'approved'");
                    $approvedSt->execute([(int)$t['product_id']]);
                    $approvedCount = (int)$approvedSt->fetchColumn();
                }
                if ($seatQuota > 0 && $approvedCount >= $seatQuota) {
                    throw new RuntimeException('Kuota peserta sudah penuh.');
                }
            }

            $pdo->prepare("UPDATE transactions
                           SET status = 'approved', approved_at = NOW(), paid_at = NOW()
                           WHERE id = ? AND status = 'pending'")->execute([$t['id']]);
            $pdo->prepare("INSERT IGNORE INTO enrollments (user_id,product_id,created_at) VALUES (?,?,NOW())")
                ->execute([$t['user_id'], $t['product_id']]);
            $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?")->execute([$t['user_id']]);
            $notifyApproved = true;
        } elseif ($status === 'rejected' && $t['status'] === 'pending') {
            $pdo->prepare("UPDATE transactions SET status = 'rejected' WHERE id = ? AND status = 'pending'")->execute([$t['id']]);
            $notifyRejected = true;
        }

        $pdo->commit();
        $txForNotify = $t;
        if ($notifyApproved) {
            fonnte_send($t['uwa'], wa_template('approved', ['nama' => $t['uname'], 'produk' => $t['ptitle']]));
            $mail = mail_template('approved', ['nama' => $t['uname'], 'produk' => $t['ptitle']]);
            mailketing_send($t['uemail'] ?? '', $mail['subject'], $mail['html']);
            $ticket = ticket_issue_for_transaction($t);
            if ($ticket) {
                $ticketLink = ticket_url($ticket['ticket_token']);
                fonnte_send($t['uwa'], wa_template('ticket_ready', [
                    'nama' => $t['uname'],
                    'produk' => $t['ptitle'],
                    'ticket' => $ticket['ticket_code'],
                    'link' => $ticketLink,
                ]));
                $ticketMail = mail_template('ticket_ready', [
                    'nama' => $t['uname'],
                    'produk' => $t['ptitle'],
                    'ticket' => $ticket['ticket_code'],
                    'link' => $ticketLink,
                ]);
                mailketing_send($t['uemail'] ?? '', $ticketMail['subject'], $ticketMail['html']);
            }
            log_activity('duitku_callback_approved', $merchantOrderId);
        } elseif ($notifyRejected) {
            fonnte_send($t['uwa'], wa_template('rejected', ['nama' => $t['uname'], 'produk' => $t['ptitle']]));
            $mail = mail_template('rejected', ['nama' => $t['uname'], 'produk' => $t['ptitle']]);
            mailketing_send($t['uemail'] ?? '', $mail['subject'], $mail['html']);
            log_activity('duitku_callback_rejected', $merchantOrderId);
        }
        http_response_code(200);
        echo 'OK';
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        log_activity('duitku_callback_error', $e->getMessage());
        http_response_code(500);
        echo 'ERROR';
    }
}

function checkout_process() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect(url('products'));
    check_csrf();
    $product = _find_product_by_slug($_POST['slug'] ?? '');
    if (!$product) { flash('error', 'Produk tidak ditemukan.'); redirect(url('products')); }
    $isMembership = (($product['type'] ?? '') === 'membership');

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
    if ($bank === 'duitku' && !_duitku_is_ready()) $errors[] = 'Payment gateway Duitku belum siap digunakan. Silakan hubungi admin.';

    $existingUser = null;
    $deletedMatches = [];
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
            $emailUser = null;
            $waUser = null;

            $st = db()->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $st->execute([$buyerEmail]);
            $emailUser = $st->fetch() ?: null;

            if ($normalizedWa !== '') {
                $chkWa = db()->prepare("SELECT * FROM users WHERE wa = ? LIMIT 1");
                $chkWa->execute([$normalizedWa]);
                $waUser = $chkWa->fetch() ?: null;
            }

            $activeEmailUser = $emailUser && empty($emailUser['deleted_at']) ? $emailUser : null;
            $activeWaUser = $waUser && empty($waUser['deleted_at']) ? $waUser : null;

            if ($activeEmailUser && $activeWaUser && (int)$activeEmailUser['id'] !== (int)$activeWaUser['id']) {
                $errors[] = 'Email dan nomor WhatsApp yang Anda masukkan terhubung ke akun yang berbeda. Silakan gunakan data yang sesuai dengan salah satu akun Anda, atau hubungi admin jika membutuhkan bantuan.';
            }

            if ($emailUser && !empty($emailUser['deleted_at'])) {
                $deletedMatches[(int)$emailUser['id']] = [
                    'user' => $emailUser,
                    'clear_email' => true,
                    'clear_wa' => false,
                ];
            }
            if ($waUser && !empty($waUser['deleted_at'])) {
                if (!isset($deletedMatches[(int)$waUser['id']])) {
                    $deletedMatches[(int)$waUser['id']] = [
                        'user' => $waUser,
                        'clear_email' => false,
                        'clear_wa' => true,
                    ];
                } else {
                    $deletedMatches[(int)$waUser['id']]['clear_wa'] = true;
                }
            }

            $existingUser = $activeEmailUser ?: $activeWaUser;
        }
    }

    if ($errors) {
        _checkout_render($product, $errors, $old, $u);
        return;
    }

    $pdo = db();
    $code = 'TRX' . date('ymd') . strtoupper(bin2hex(random_bytes(2)));
    $proofFile = null;
    $selectedMethod = $methodMap[$bank] ?? null;

    try {
        $pdo->beginTransaction();

        [$cvalid, $discount, $coupon, $cmsg] = _apply_coupon($couponCode, $product, true);
        if (!$cvalid) throw new RuntimeException($cmsg);

        if (!$u) {
            $buyerName = trim($_POST['buyer_name'] ?? '');
            $buyerEmail = trim($_POST['buyer_email'] ?? '');

            foreach ($deletedMatches as $match) {
                _checkout_archive_deleted_user_identity(
                    $pdo,
                    $match['user'],
                    !empty($match['clear_email']),
                    !empty($match['clear_wa'])
                );
            }

            if ($existingUser) {
                $u = $existingUser;
                $updates = [];
                $params = [];
                if ($buyerName !== '' && $buyerName !== (string)$u['name']) {
                    $updates[] = 'name = ?';
                    $params[] = $buyerName;
                    $u['name'] = $buyerName;
                }
                if ($normalizedWa !== '' && ($u['wa'] ?? '') === '') {
                    $updates[] = 'wa = ?';
                    $params[] = $normalizedWa;
                    $u['wa'] = $normalizedWa;
                }
                if ($buyerEmail !== '' && ($u['email'] ?? '') === '') {
                    $updates[] = 'email = ?';
                    $params[] = $buyerEmail;
                    $u['email'] = $buyerEmail;
                }
                if ($updates) {
                    $params[] = $u['id'];
                    $pdo->prepare("UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?")->execute($params);
                }
            } else {
                $refCodeUser = _checkout_make_ref_code($buyerName, $pdo);
                $referredBy = null;
                $generatedPassword = $isMembership ? '' : _checkout_generate_password();
                if (!empty($_COOKIE['tc_ref'])) {
                    $r = $pdo->prepare("SELECT id FROM users WHERE ref_code = ?");
                    $r->execute([preg_replace('/[^a-zA-Z0-9_\-]/', '', $_COOKIE['tc_ref'])]);
                    $referredBy = $r->fetchColumn() ?: null;
                }

                $pdo->prepare("INSERT INTO users (name,email,password,wa,role,status,member_status,approved_at,ref_code,referred_by,created_at)
                               VALUES (?,?,?,?,?, 'inactive', 'pending', NULL, ?, ?, NOW())")
                    ->execute([
                        $buyerName,
                        $buyerEmail,
                        null,
                        $normalizedWa,
                        'member',
                        $refCodeUser,
                        $referredBy,
                    ]);

                $u = [
                    'id' => (int)$pdo->lastInsertId(),
                    'name' => $buyerName,
                    'email' => $buyerEmail,
                    'wa' => $normalizedWa,
                    'role' => 'member',
                    'status' => 'inactive',
                    'member_status' => 'pending',
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

        $paymentProvider = $bank === 'duitku' ? 'duitku' : null;
        $paymentLabel = $paymentProvider ? 'Duitku' : $methodMap[$bank]['label'];
        $ins = $pdo->prepare("INSERT INTO transactions
            (code,user_id,product_id,bank,payment_provider,amount,coupon_code,discount,total,proof,note,ref_code,status,created_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?, 'pending', NOW())");
        $ins->execute([
            $code, $u['id'], $product['id'], $paymentLabel, $paymentProvider, $product['price'],
            $coupon ? $coupon['code'] : null, $discount, $total,
            $proofFile, $note, $ref
        ]);

        $transactionId = (int)$pdo->lastInsertId();

        if ($coupon) {
            $pdo->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?")->execute([$coupon['id']]);
        }

        $pdo->commit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        _checkout_render($product, [$e->getMessage()], $old, $u);
        return;
    }

    if (($selectedMethod['gateway'] ?? '') === 'duitku') {
        try {
            $transaction = [
                'id' => $transactionId,
                'code' => $code,
                'user_id' => $u['id'],
                'total' => $total,
            ];
            $duitku = _duitku_create_payment($transaction, $u, $product);
            db()->prepare("UPDATE transactions
                           SET payment_reference = ?, payment_url = ?, payment_payload = ?
                           WHERE id = ?")
                ->execute([
                    $duitku['reference'] ?: null,
                    $duitku['payment_url'],
                    json_encode($duitku['payload']),
                    $transactionId,
                ]);
            redirect($duitku['payment_url']);
        } catch (Exception $e) {
            db()->prepare("UPDATE transactions SET payment_payload = ? WHERE id = ?")
                ->execute([json_encode(['error' => $e->getMessage()]), $transactionId]);
            _checkout_render($product, [$e->getMessage()], $old, $u);
            return;
        }
    }

    log_activity('checkout', "Order $code untuk {$product['title']}");
    $buyerWaMessage = wa_template('purchase', [
        'nama' => $u['name'],
        'produk' => $product['title'],
        'kode' => $code,
        'total' => rupiah($total),
    ]);
    $buyerMail = mail_template('purchase', [
        'nama' => $u['name'],
        'produk' => $product['title'],
        'kode' => $code,
        'total' => rupiah($total),
    ]);

    if ($selectedMethod && (($selectedMethod['type'] ?? '') === 'bank')) {
        $bankLabel = trim((string)($selectedMethod['label'] ?? ''));
        $bankDetail = trim((string)($selectedMethod['detail'] ?? ''));
        if ($bankLabel !== '' && $bankDetail !== '') {
            $buyerWaMessage .= "\n\nSilakan melakukan pembayaran ke rekening berikut:\n"
                . "Bank Tujuan: {$bankLabel}\n"
                . "Nomor Rekening: {$bankDetail}\n"
                . "Nominal Pembayaran: " . rupiah($total) . "\n"
                . "Kode Referensi: {$code}\n\n"
                . "Mohon melakukan pembayaran sesuai nominal yang tertera agar proses verifikasi dapat dilakukan dengan lebih cepat.";
            $buyerMail['html'] .= '<hr>'
                . '<p>Silakan melakukan pembayaran ke rekening berikut:</p>'
                . '<p><strong>Bank Tujuan:</strong> ' . e($bankLabel) . '<br>'
                . '<strong>Nomor Rekening:</strong> ' . e($bankDetail) . '<br>'
                . '<strong>Nominal Pembayaran:</strong> ' . e(rupiah($total)) . '<br>'
                . '<strong>Kode Referensi:</strong> ' . e($code) . '</p>'
                . '<p>Mohon melakukan pembayaran sesuai nominal yang tertera agar proses verifikasi dapat dilakukan dengan lebih cepat.</p>';
        }
    }

    fonnte_send($u['wa'], $buyerWaMessage);
    mailketing_send($u['email'], $buyerMail['subject'], $buyerMail['html']);
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
        'vars' => [
            'code' => $code,
            'product' => $product,
            'total' => $total,
            'account_created' => $accountCreated,
            'payment_status' => 'pending',
            'payment_url' => '',
            'payment_method' => $selectedMethod ? [
                'type' => (string)($selectedMethod['type'] ?? ''),
                'label' => (string)($selectedMethod['label'] ?? ''),
                'detail' => (string)($selectedMethod['detail'] ?? ''),
                'image' => (string)($selectedMethod['image'] ?? ''),
                'gateway' => (string)($selectedMethod['gateway'] ?? ''),
            ] : null,
        ],
    ]);
}
