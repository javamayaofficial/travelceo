<?php
/**
 * app/controllers/MemberController.php
 */

function _owned_product_ids($uid) {
    $st = db()->prepare("SELECT product_id FROM enrollments WHERE user_id = ?");
    $st->execute([$uid]);
    return array_map('intval', array_column($st->fetchAll(), 'product_id'));
}

function member_ticket_view() {
    $token = trim((string)($_GET['token'] ?? ''));
    $ticket = ticket_find_by_token($token);
    if (!$ticket) {
        http_response_code(404);
        flash('error', 'E-ticket tidak ditemukan atau sudah tidak berlaku.');
        redirect(url('home'));
    }

    view('layout', [
        'title' => 'E-Ticket — ' . ($ticket['product_title'] ?? setting('site_name', 'The Travel CEO')),
        'content' => 'ticket',
        'vars' => ['ticket' => $ticket],
        'body_class' => 'theme-ticket',
    ]);
}

function member_ticket_verify() {
    $token = trim((string)($_GET['token'] ?? ''));
    $ticket = ticket_find_by_token($token);
    if (!$ticket) {
        http_response_code(404);
        view('layout', [
            'title' => 'Tiket Tidak Ditemukan',
            'content' => 'ticket_verify',
            'vars' => ['ticket' => null],
            'body_class' => 'theme-ticket',
        ]);
        return;
    }

    view('layout', [
        'title' => 'Validasi E-Ticket — ' . ($ticket['product_title'] ?? setting('site_name', 'The Travel CEO')),
        'content' => 'ticket_verify',
        'vars' => ['ticket' => $ticket],
        'body_class' => 'theme-ticket',
    ]);
}

function member_dashboard() {
    require_member();
    $u = current_user();

    $owned = _owned_product_ids($u['id']);
    $courses = [];
    if ($owned) {
        $in = implode(',', array_fill(0, count($owned), '?'));
        $st = db()->prepare("SELECT p.*, a.slug AS access_slug
                             FROM products p
                             LEFT JOIN access_pages a ON a.product_id = p.id AND a.status = 'publish'
                             WHERE p.id IN ($in)
                             ORDER BY p.title");
        $st->execute($owned);
        $courses = $st->fetchAll();
    }

    // transaksi terbaru
    $tx = db()->prepare("SELECT t.*, p.title AS ptitle, et.ticket_code, et.ticket_token
                         FROM transactions t
                         JOIN products p ON p.id = t.product_id
                         LEFT JOIN event_tickets et ON et.transaction_id = t.id
                         WHERE t.user_id = ? ORDER BY t.id DESC LIMIT 10");
    $tx->execute([$u['id']]);
    $transactions = $tx->fetchAll();

    $resume = null;
    if ($owned) {
        $latestSt = db()->prepare("SELECT p.id AS product_id, p.title AS product_title, p.thumbnail,
                                          l.id AS lesson_id, l.title AS lesson_title, l.short_desc AS lesson_desc,
                                          l.sort, l.duration_minutes, pr.updated_at
                                   FROM progress pr
                                   JOIN lessons l ON l.id = pr.lesson_id
                                   JOIN products p ON p.id = l.product_id
                                   WHERE pr.user_id = ? AND p.id IN ($in)
                                   ORDER BY pr.updated_at DESC, pr.lesson_id DESC
                                   LIMIT 1");
        $latestSt->execute(array_merge([$u['id']], $owned));
        $latest = $latestSt->fetch() ?: null;
        if ($latest) {
            $nextLesson = db()->prepare("SELECT id, title, short_desc, duration_minutes
                                         FROM lessons
                                         WHERE product_id = ?
                                           AND (sort > ? OR (sort = ? AND id > ?))
                                         ORDER BY sort, id
                                         LIMIT 1");
            $nextLesson->execute([
                (int)$latest['product_id'],
                (int)$latest['sort'],
                (int)$latest['sort'],
                (int)$latest['lesson_id'],
            ]);
            $next = $nextLesson->fetch() ?: null;
            $resume = [
                'product_id' => (int)$latest['product_id'],
                'product_title' => $latest['product_title'],
                'thumbnail' => $latest['thumbnail'],
                'lesson_id' => (int)($next['id'] ?? $latest['lesson_id']),
                'lesson_title' => $next['title'] ?? $latest['lesson_title'],
                'lesson_desc' => $next['short_desc'] ?? $latest['lesson_desc'],
                'duration_minutes' => (int)($next['duration_minutes'] ?? $latest['duration_minutes']),
            ];
        }
        if (!$resume) {
            $resumeSt = db()->prepare("SELECT p.id AS product_id, p.title AS product_title, p.thumbnail, l.id AS lesson_id, l.title AS lesson_title,
                                              l.short_desc AS lesson_desc, l.duration_minutes,
                                              (SELECT COUNT(*) FROM lessons lx WHERE lx.product_id = p.id) AS total_lessons,
                                              (SELECT COUNT(*) FROM progress px
                                               JOIN lessons lx2 ON lx2.id = px.lesson_id
                                               WHERE px.user_id = ? AND lx2.product_id = p.id) AS done_lessons
                                       FROM products p
                                       JOIN lessons l ON l.product_id = p.id
                                       WHERE p.id IN ($in)
                                       ORDER BY p.title, l.sort, l.id
                                       LIMIT 1");
            $resumeSt->execute(array_merge([$u['id']], $owned));
            $resume = $resumeSt->fetch() ?: null;
        }
        if ($resume) {
            $countSt = db()->prepare("SELECT
                                        (SELECT COUNT(*) FROM lessons WHERE product_id = ?) AS total_lessons,
                                        (SELECT COUNT(*) FROM progress px
                                         JOIN lessons lx ON lx.id = px.lesson_id
                                         WHERE px.user_id = ? AND lx.product_id = ?) AS done_lessons");
            $countSt->execute([(int)$resume['product_id'], $u['id'], (int)$resume['product_id']]);
            $counts = $countSt->fetch() ?: ['total_lessons' => 0, 'done_lessons' => 0];
            $resume['total_lessons'] = (int)$counts['total_lessons'];
            $resume['done_lessons'] = (int)$counts['done_lessons'];
        }
    }

    view('layout', [
        'title' => 'Dashboard — ' . setting('site_name', 'The Travel CEO'),
        'content' => 'member/dashboard',
        'vars' => ['u' => $u, 'courses' => $courses, 'transactions' => $transactions, 'resume' => $resume],
    ]);
}

function member_learn() {
    require_member();
    $u = current_user();
    $pid = (int)($_GET['product'] ?? 0);

    $st = db()->prepare("SELECT * FROM products WHERE id = ?");
    $st->execute([$pid]);
    $product = $st->fetch();
    if (!$product) { flash('error', 'Kelas tidak ditemukan.'); redirect(url('dashboard')); }

    // Cek akses
    $chk = db()->prepare("SELECT 1 FROM enrollments WHERE user_id = ? AND product_id = ?");
    $chk->execute([$u['id'], $pid]);
    if (!$chk->fetchColumn()) {
        flash('error', 'Anda belum memiliki akses ke kelas ini.');
        redirect(url('product', ['slug' => $product['slug']]));
    }

    $ls = db()->prepare("SELECT * FROM lessons WHERE product_id = ? ORDER BY sort, id");
    $ls->execute([$pid]);
    $lessons = $ls->fetchAll();
    if (!$lessons) { flash('error', 'Materi belum tersedia.'); redirect(url('dashboard')); }

    // progress yang sudah selesai
    $pr = db()->prepare("SELECT p.lesson_id
                         FROM progress p
                         JOIN lessons l ON l.id = p.lesson_id
                         WHERE p.user_id = ? AND l.product_id = ?");
    $pr->execute([$u['id'], $pid]);
    $done = array_map('intval', array_column($pr->fetchAll(), 'lesson_id'));

    $unlocked = [];
    foreach ($lessons as $i => $lesson) {
        if ($i === 0) {
            $unlocked[] = (int)$lesson['id'];
            continue;
        }
        $prevLessonId = (int)$lessons[$i - 1]['id'];
        if (in_array($prevLessonId, $done, true)) $unlocked[] = (int)$lesson['id'];
    }

    $defaultLessonId = (int)$lessons[0]['id'];
    foreach ($lessons as $lesson) {
        if (!in_array((int)$lesson['id'], $done, true)) {
            $defaultLessonId = (int)$lesson['id'];
            break;
        }
    }
    if (!in_array($defaultLessonId, $unlocked, true)) $defaultLessonId = (int)$lessons[0]['id'];

    $lessonId = (int)($_GET['lesson'] ?? $defaultLessonId);
    if (!in_array($lessonId, $unlocked, true) && !in_array($lessonId, $done, true)) $lessonId = $defaultLessonId;

    $current = null; $idx = 0;
    foreach ($lessons as $i => $l) { if ((int)$l['id'] === $lessonId) { $current = $l; $idx = $i; break; } }
    if (!$current) { $current = $lessons[0]; $idx = 0; }

    $prev = $idx > 0 ? $lessons[$idx - 1] : null;
    $next = $idx < count($lessons) - 1 ? $lessons[$idx + 1] : null;
    $currentDone = in_array((int)$current['id'], $done, true);
    $totalLessons = count($lessons);
    $doneCount = count($done);
    $pct = $totalLessons ? (int)round($doneCount / $totalLessons * 100) : 0;
    $totalDuration = 0;
    foreach ($lessons as $lesson) $totalDuration += (int)($lesson['duration_minutes'] ?? 0);
    $remainingCount = max(0, $totalLessons - $doneCount);
    $lessonPosition = $idx + 1;

    view('layout', [
        'title' => $current['title'] . ' — ' . $product['title'],
        'content' => 'member/learn',
        'vars' => ['product' => $product, 'lessons' => $lessons, 'current' => $current,
                   'done' => $done, 'unlocked' => $unlocked, 'prev' => $prev, 'next' => $next,
                   'current_done' => $currentDone, 'progress_percent' => $pct,
                   'total_lessons' => $totalLessons, 'done_count' => $doneCount,
                   'remaining_count' => $remainingCount, 'total_duration' => $totalDuration,
                   'lesson_position' => $lessonPosition],
        'bare' => true,
    ]);
}

function member_complete_lesson() {
    require_member();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect(url('dashboard'));
    check_csrf();
    $u = current_user();
    $lid = (int)($_POST['lesson'] ?? 0);
    $pid = (int)($_POST['product'] ?? 0);

    $chk = db()->prepare("SELECT 1 FROM enrollments WHERE user_id = ? AND product_id = ?");
    $chk->execute([$u['id'], $pid]);
    if ($chk->fetchColumn()) {
        $ls = db()->prepare("SELECT id FROM lessons WHERE product_id = ? ORDER BY sort, id");
        $ls->execute([$pid]);
        $lessonIds = array_map('intval', array_column($ls->fetchAll(), 'id'));
        $currentIdx = array_search($lid, $lessonIds, true);

        if ($currentIdx !== false) {
            $allowed = $currentIdx === 0;
            if (!$allowed) {
                $prevLessonId = $lessonIds[$currentIdx - 1];
                $doneSt = db()->prepare("SELECT 1 FROM progress WHERE user_id = ? AND lesson_id = ? LIMIT 1");
                $doneSt->execute([$u['id'], $prevLessonId]);
                $allowed = (bool)$doneSt->fetchColumn();
            }

            if ($allowed) {
                db()->prepare("INSERT INTO progress (user_id, lesson_id, updated_at) VALUES (?, ?, NOW())
                               ON DUPLICATE KEY UPDATE updated_at = NOW()")->execute([$u['id'], $lid]);
            } else {
                flash('error', 'Selesaikan materi sebelumnya terlebih dahulu.');
                redirect(url('learn', ['product' => $pid]));
            }
        }
    }
    redirect(url('learn', ['product' => $pid, 'lesson' => (int)($_POST['next'] ?? $lid)]));
}

function member_affiliate() {
    require_member();
    $u = current_user();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_csrf();
        $bankName = trim($_POST['bank_name'] ?? '');
        $accountName = trim($_POST['account_name'] ?? '');
        $accountNumber = preg_replace('/\s+/', '', trim($_POST['account_number'] ?? ''));
        $note = trim($_POST['note'] ?? '');

        if ($bankName === '' || $accountName === '' || $accountNumber === '') {
            flash('error', 'Lengkapi data rekening untuk pencairan komisi.');
            redirect(url('affiliate'));
        }

        try {
            $pdo = db();
            $pdo->beginTransaction();

            $sumSt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) AS total
                                    FROM commissions
                                    WHERE affiliate_id = ?
                                      AND status = 'approved'
                                      AND withdrawal_id IS NULL
                                    FOR UPDATE");
            $sumSt->execute([$u['id']]);
            $available = (int)$sumSt->fetchColumn();

            if ($available <= 0) {
                $pdo->rollBack();
                flash('error', 'Belum ada komisi approved yang siap dicairkan.');
                redirect(url('affiliate'));
            }

            $ins = $pdo->prepare("INSERT INTO commission_withdrawals
                                  (affiliate_id, amount, bank_name, account_name, account_number, note, status, created_at)
                                  VALUES (?, ?, ?, ?, ?, ?, 'requested', NOW())");
            $ins->execute([
                $u['id'],
                $available,
                substr($bankName, 0, 100),
                substr($accountName, 0, 150),
                substr($accountNumber, 0, 100),
                $note !== '' ? substr($note, 0, 500) : null,
            ]);
            $withdrawalId = (int)$pdo->lastInsertId();

            $upd = $pdo->prepare("UPDATE commissions
                                  SET withdrawal_id = ?
                                  WHERE affiliate_id = ?
                                    AND status = 'approved'
                                    AND withdrawal_id IS NULL");
            $upd->execute([$withdrawalId, $u['id']]);

            $pdo->commit();
            log_activity('commission_withdraw_requested', 'withdrawal_id=' . $withdrawalId . '; amount=' . $available);
            flash('success', 'Request pencairan komisi berhasil dikirim ke admin.');
        } catch (Exception $e) {
            if (db()->inTransaction()) db()->rollBack();
            log_activity('commission_withdraw_request_failed', $e->getMessage());
            flash('error', 'Request pencairan gagal diproses. Silakan coba lagi.');
        }
        redirect(url('affiliate'));
    }

    $clicks = (int)db()->query("SELECT COUNT(*) FROM clicks WHERE affiliate_id = " . (int)$u['id'])->fetchColumn();
    $st = db()->prepare("SELECT
            COUNT(*) sales,
            COALESCE(SUM(amount),0) total,
            COALESCE(SUM(CASE WHEN status='pending'  THEN amount ELSE 0 END),0) pending,
            COALESCE(SUM(CASE WHEN status='approved' THEN amount ELSE 0 END),0) approved,
            COALESCE(SUM(CASE WHEN status='paid'     THEN amount ELSE 0 END),0) paid
        FROM commissions WHERE affiliate_id = ?");
    $st->execute([$u['id']]);
    $sum = $st->fetch();

    $rows = db()->prepare("SELECT c.*, t.code, t.created_at AS tdate FROM commissions c
                           JOIN transactions t ON t.id = c.transaction_id
                           WHERE c.affiliate_id = ? ORDER BY c.id DESC LIMIT 30");
    $rows->execute([$u['id']]);
    $list = $rows->fetchAll();

    $availableSt = db()->prepare("SELECT COALESCE(SUM(amount),0)
                                  FROM commissions
                                  WHERE affiliate_id = ?
                                    AND status = 'approved'
                                    AND withdrawal_id IS NULL");
    $availableSt->execute([$u['id']]);
    $available = (int)$availableSt->fetchColumn();

    $wdSt = db()->prepare("SELECT
            COALESCE(SUM(CASE WHEN status IN ('requested','approved') THEN amount ELSE 0 END),0) AS pending_total,
            COALESCE(SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END),0) AS paid_total
        FROM commission_withdrawals
        WHERE affiliate_id = ?");
    $wdSt->execute([$u['id']]);
    $withdrawSummary = $wdSt->fetch();

    $wdRows = db()->prepare("SELECT * FROM commission_withdrawals
                             WHERE affiliate_id = ?
                             ORDER BY id DESC LIMIT 20");
    $wdRows->execute([$u['id']]);
    $withdrawals = $wdRows->fetchAll();

    $productRows = db()->query("SELECT id, title, slug, short_desc, type
                                FROM products
                                WHERE status = 'publish'
                                ORDER BY id DESC")->fetchAll();
    $affiliateProducts = [];
    foreach ($productRows as $product) {
        $productLink = url('product', ['slug' => $product['slug'], 'ref' => $u['ref_code']]);
        $affiliateProducts[] = [
            'id' => (int)$product['id'],
            'title' => $product['title'],
            'type' => $product['type'],
            'short_desc' => $product['short_desc'],
            'link' => $productLink,
            'share_text' => rawurlencode('Yuk cek program ' . $product['title'] . ' dari ' . setting('site_name', 'The Travel CEO') . ': ' . $productLink),
        ];
    }

    view('layout', [
        'title' => 'Program Affiliate — ' . setting('site_name', 'The Travel CEO'),
        'content' => 'member/affiliate',
        'vars' => [
            'u' => $u,
            'clicks' => $clicks,
            'sum' => $sum,
            'list' => $list,
            'available' => $available,
            'withdrawSummary' => $withdrawSummary,
            'withdrawals' => $withdrawals,
            'affiliate_products' => $affiliateProducts,
        ],
    ]);
}

function member_profile() {
    require_member();
    $u = current_user();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_csrf();
        $name = trim($_POST['name'] ?? '');
        $wa   = trim($_POST['wa'] ?? '');
        $normalizedWa = $wa !== '' ? wa_normalize($wa) : null;
        $currentPassword = $_POST['current_password'] ?? '';
        $newpass = $_POST['password'] ?? '';

        if (mb_strlen($name) < 3) {
            flash('error', 'Nama minimal 3 karakter.');
            redirect(url('profile'));
        }
        if ($wa !== '' && !preg_match('/^(08|62|\+62)[0-9]{7,13}$/', preg_replace('/[\s\-]/', '', $wa))) {
            flash('error', 'Nomor WhatsApp tidak valid.');
            redirect(url('profile'));
        }
        if ($normalizedWa !== null) {
            $chkWa = db()->prepare("SELECT 1 FROM users WHERE wa = ? AND id <> ? LIMIT 1");
            $chkWa->execute([$normalizedWa, $u['id']]);
            if ($chkWa->fetchColumn()) {
                flash('error', 'Nomor WhatsApp sudah dipakai akun lain.');
                redirect(url('profile'));
            }
        }
        if ($newpass !== '' && strlen($newpass) < 8) {
            flash('error', 'Password baru minimal 8 karakter.');
            redirect(url('profile'));
        }
        if ($newpass !== '' && !password_verify($currentPassword, (string)$u['password'])) {
            flash('error', 'Password lama tidak sesuai.');
            redirect(url('profile'));
        }

        db()->prepare("UPDATE users SET name = ?, wa = ? WHERE id = ?")
           ->execute([$name, $normalizedWa, $u['id']]);
        if ($newpass !== '') {
            db()->prepare("UPDATE users SET password = ? WHERE id = ?")
               ->execute([password_hash($newpass, PASSWORD_DEFAULT), $u['id']]);
        }
        flash('success', 'Profil berhasil diperbarui.');
        redirect(url('profile'));
    }
    view('layout', [
        'title' => 'Profil Saya', 'content' => 'member/profile', 'vars' => ['u' => $u],
    ]);
}
