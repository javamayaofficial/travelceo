<?php
/**
 * admin/index.php — Panel Admin.
 */
require __DIR__ . '/../app/bootstrap.php';
require_admin();

$p   = $_GET['p'] ?? 'dashboard';
$act = $_POST['action'] ?? ($_GET['action'] ?? '');

/* ============ AKSI (POST) ============ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    /* --- Verifikasi transaksi --- */
    if ($act === 'approve_tx' || $act === 'reject_tx') {
        $id = (int)$_POST['id'];
        $pdo = db();
        $t = null;
        try {
            $pdo->beginTransaction();
            $st = $pdo->prepare("SELECT t.*, p.title ptitle, p.type ptype, u.name uname, u.wa uwa, u.email uemail
                                 FROM transactions t
                                 JOIN products p ON p.id=t.product_id
                                 JOIN users u ON u.id=t.user_id
                                 WHERE t.id=?
                                 FOR UPDATE");
            $st->execute([$id]);
            $t = $st->fetch();
            if (!$t) {
                throw new RuntimeException('Transaksi tidak ditemukan.');
            }
            if ($t['status'] !== 'pending') {
                throw new RuntimeException('Transaksi ini sudah diproses sebelumnya.');
            }

            if ($act === 'approve_tx') {
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
                        throw new RuntimeException('Kuota peserta workshop sudah penuh. Tidak bisa approve transaksi ini.');
                    }
                }
                $upd = $pdo->prepare("UPDATE transactions SET status='approved', approved_at=NOW() WHERE id=? AND status='pending'");
                $upd->execute([$id]);
                if ($upd->rowCount() !== 1) throw new RuntimeException('Status transaksi gagal diperbarui.');

                $pdo->prepare("INSERT IGNORE INTO enrollments (user_id,product_id,created_at) VALUES (?,?,NOW())")
                    ->execute([$t['user_id'], $t['product_id']]);
                $pdo->prepare("UPDATE users SET status='active' WHERE id=?")->execute([$t['user_id']]);

                if (!empty($t['ref_code'])) {
                    $a = $pdo->prepare("SELECT id FROM users WHERE ref_code=? LIMIT 1");
                    $a->execute([$t['ref_code']]);
                    $aid = (int)$a->fetchColumn();
                    if ($aid > 0 && $aid !== (int)$t['user_id']) {
                        $exists = $pdo->prepare("SELECT 1 FROM commissions WHERE affiliate_id=? AND transaction_id=? LIMIT 1");
                        $exists->execute([$aid, $id]);
                        if (!$exists->fetchColumn()) {
                            $pct = (int)setting('commission_percent', 10);
                            $amt = (int)round($t['total'] * $pct / 100);
                            $pdo->prepare("INSERT INTO commissions (affiliate_id,transaction_id,amount,status,created_at)
                                           VALUES (?,?,?, 'approved', NOW())")->execute([$aid, $id, $amt]);
                        }
                    }
                }

                $pdo->commit();
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
                log_activity('approve_tx', $t['code']);
                flash('success', 'Transaksi disetujui. Kelas member sudah dibuka.');
            } else {
                $upd = $pdo->prepare("UPDATE transactions SET status='rejected' WHERE id=? AND status='pending'");
                $upd->execute([$id]);
                if ($upd->rowCount() !== 1) throw new RuntimeException('Status transaksi gagal diperbarui.');

                $pdo->commit();
                fonnte_send($t['uwa'], wa_template('rejected', ['nama' => $t['uname'], 'produk' => $t['ptitle']]));
                $mail = mail_template('rejected', ['nama' => $t['uname'], 'produk' => $t['ptitle']]);
                mailketing_send($t['uemail'] ?? '', $mail['subject'], $mail['html']);
                log_activity('reject_tx', $t['code']);
                flash('success', 'Transaksi ditolak.');
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            flash('error', $e->getMessage());
        }
        redirect(admin_url('transactions'));
    }

    if ($act === 'approve_member' || $act === 'reject_member' || $act === 'delete_member' || $act === 'restore_member') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash('error', 'Member tidak valid.');
            redirect(admin_url('members'));
        }
        if (!empty($_SESSION['uid']) && $id === (int)$_SESSION['uid']) {
            flash('error', 'Anda tidak bisa memproses akun yang sedang dipakai login.');
            redirect(admin_url('members'));
        }

        $pdo = db();
        try {
            $pdo->beginTransaction();
            $memberSt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'member' LIMIT 1 FOR UPDATE");
            $memberSt->execute([$id]);
            $member = $memberSt->fetch();
            if (!$member) throw new RuntimeException('Member tidak ditemukan.');

            if ($act === 'restore_member') {
                if (empty($member['deleted_at'])) {
                    $pdo->commit();
                    flash('success', 'Member sudah aktif (tidak dalam status terhapus).');
                    redirect(admin_url('members'));
                }
                $pdo->prepare("UPDATE users
                               SET deleted_at = NULL, member_status = 'pending', status = 'inactive',
                                   password = NULL, approved_at = NULL
                               WHERE id = ? AND role = 'member'")->execute([$id]);
                $pdo->prepare("DELETE FROM login_otps WHERE user_id = ?")->execute([$id]);
                $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$id]);
                $pdo->commit();
                log_activity('restore_member', 'member_id=' . $id . '; email=' . ($member['email'] ?? ''));
                flash('success', 'Member berhasil direstore. Status kembali pending dan menunggu approve.');
                redirect(admin_url('members'));
            }

            if ($act === 'approve_member') {
                if (($member['member_status'] ?? '') === 'approved' && ($member['status'] ?? '') === 'active') {
                    $pdo->commit();
                    flash('success', 'Member sudah berstatus approved.');
                    redirect(admin_url('members'));
                }

                $plain = generate_secure_password(12);
                $pdo->prepare("UPDATE users
                               SET member_status = 'approved', status = 'active',
                                   password = ?, approved_at = NOW()
                               WHERE id = ? AND role = 'member'")
                    ->execute([password_hash($plain, PASSWORD_DEFAULT), $id]);
                $pdo->prepare("DELETE FROM login_otps WHERE user_id = ?")->execute([$id]);
                $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$id]);
                $txSt = $pdo->prepare("SELECT t.*, p.title ptitle, p.type ptype, u.name uname, u.wa uwa, u.email uemail
                                       FROM transactions t
                                       JOIN products p ON p.id = t.product_id
                                       JOIN users u ON u.id = t.user_id
                                       WHERE t.user_id = ?
                                       ORDER BY t.id DESC
                                       LIMIT 1");
                $txSt->execute([$id]);
                $tx = $txSt->fetch() ?: null;

                $ticket = null;
                $ticketLink = '';
                $qrUrl = '';
                if ($tx) {
                    $ticket = ticket_issue_for_transaction_any($tx);
                    if ($ticket) {
                        $ticketLink = ticket_url($ticket['ticket_token']);
                        $qrUrl = ticket_qr_image_url($ticket['ticket_token']);
                    }
                }

                $pdo->commit();

                $loginLink = login_member_url();
                $mail = mail_template('member_approved', [
                    'nama' => $member['name'],
                    'username' => $member['email'],
                    'password' => $plain,
                    'login' => $loginLink,
                    'kode' => (string)($tx['code'] ?? '-'),
                    'ticket' => (string)($ticket['ticket_code'] ?? '-'),
                    'ticket_link' => $ticketLink !== '' ? $ticketLink : $loginLink,
                    'qr_url' => $qrUrl !== '' ? $qrUrl : '',
                ]);
                if (!empty($member['email']) && mailketing_token() && mail_sender_email()) {
                    mailketing_send($member['email'], $mail['subject'], $mail['html']);
                }
                if (!empty($member['wa']) && fonnte_token()) {
                    $msg = "Halo {$member['name']},\n\nMembership Anda telah disetujui.\n\nSilakan login menggunakan akun berikut.\n\nUsername:\n{$member['email']}\n\nPassword:\n{$plain}\n\nLogin:\n{$loginLink}";
                    if (!empty($tx['code'])) {
                        $msg .= "\n\nKode Invoice:\n{$tx['code']}";
                    }
                    if (!empty($ticket['ticket_code'])) {
                        $msg .= "\n\nKode E-Ticket:\n{$ticket['ticket_code']}";
                    }
                    if ($ticketLink !== '') {
                        $msg .= "\n\nE-Ticket & QR:\n{$ticketLink}";
                    }
                    fonnte_send($member['wa'], $msg);
                }
                log_activity('approve_member', 'member_id=' . $id . '; email=' . ($member['email'] ?? ''));
                flash('success', 'Member berhasil di-approve.');
                redirect(admin_url('members'));
            }

            if ($act === 'reject_member') {
                $pdo->prepare("UPDATE users
                               SET member_status = 'rejected', status = 'inactive',
                                   password = NULL, approved_at = NULL
                               WHERE id = ? AND role = 'member'")->execute([$id]);
                $pdo->prepare("DELETE FROM login_otps WHERE user_id = ?")->execute([$id]);
                $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$id]);
                $pdo->commit();
                log_activity('reject_member', 'member_id=' . $id . '; email=' . ($member['email'] ?? ''));
                flash('success', 'Member berhasil di-reject.');
                redirect(admin_url('members'));
            }

            $pdo->prepare("UPDATE users
                           SET deleted_at = NOW(), member_status = 'rejected', status = 'inactive',
                               password = NULL
                           WHERE id = ? AND role = 'member'")->execute([$id]);
            $pdo->prepare("DELETE FROM login_otps WHERE user_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM enrollments WHERE user_id = ?")->execute([$id]);
            $pdo->prepare("DELETE p FROM progress p INNER JOIN lessons l ON l.id = p.lesson_id WHERE p.user_id = ?")->execute([$id]);
            $pdo->commit();
            log_activity('delete_member', 'member_id=' . $id . '; email=' . ($member['email'] ?? ''));
            flash('success', 'Member berhasil dihapus.');
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            flash('error', $e->getMessage());
        }
        redirect(admin_url('members'));
    }

    /* --- Simpan produk --- */
    if ($act === 'save_product') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title']);
        $slug  = $_POST['slug'] ? slugify($_POST['slug']) : slugify($title);
        if (unique_value_exists('products', 'slug', $slug, $id)) {
            flash('error', 'Slug produk sudah dipakai. Gunakan slug lain.');
            redirect(admin_url('products', $id ? ['edit' => $id] : ['new' => 1]));
        }
        $eventStartRaw = trim((string)($_POST['event_start_at'] ?? ''));
        $eventStartTs = $eventStartRaw !== '' ? strtotime($eventStartRaw) : false;
        if ($eventStartRaw !== '' && $eventStartTs === false) {
            flash('error', 'Format tanggal event tidak valid.');
            redirect(admin_url('products', $id ? ['edit' => $id] : ['new' => 1]));
        }
        $eventStartAt = $eventStartTs ? date('Y-m-d H:i:s', $eventStartTs) : null;
        $data = [$_POST['type'], $title, $slug, (int)$_POST['price'],
                 trim($_POST['short_desc']), trim($_POST['long_desc']),
                 $_POST['status'] === 'draft' ? 'draft' : 'publish',
                 $eventStartAt,
                 trim((string)($_POST['event_location'] ?? '')) ?: null,
                 trim((string)($_POST['event_city'] ?? '')) ?: null,
                 trim((string)($_POST['event_maps_url'] ?? '')) ?: null,
                 trim((string)($_POST['event_notes'] ?? '')) ?: null,
                 max(0, (int)($_POST['seat_quota'] ?? 0))];
        $up = handle_upload('thumbnail', 'uploads', [
            'allowed' => [
                'jpg' => ['image/jpeg', 'image/pjpeg'],
                'jpeg' => ['image/jpeg', 'image/pjpeg'],
                'png' => ['image/png'],
                'webp' => ['image/webp'],
            ],
        ]);
        if (isset($up['error'])) {
            flash('error', $up['error']);
            redirect(admin_url('products', $id ? ['edit' => $id] : ['new' => 1]));
        }
        $thumb = (isset($up['file'])) ? $up['file'] : null;
        if ($id) {
            $sql = "UPDATE products SET type=?,title=?,slug=?,price=?,short_desc=?,long_desc=?,status=?,event_start_at=?,event_location=?,event_city=?,event_maps_url=?,event_notes=?,seat_quota=?"
                 . ($thumb ? ",thumbnail=?" : "") . " WHERE id=?";
            $params = $data; if ($thumb) $params[] = $thumb; $params[] = $id;
            db()->prepare($sql)->execute($params);
        } else {
            $data[] = $thumb;
            db()->prepare("INSERT INTO products (type,title,slug,price,short_desc,long_desc,status,event_start_at,event_location,event_city,event_maps_url,event_notes,seat_quota,thumbnail,created_at)
                           VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())")->execute($data);
        }
        flash('success', 'Produk disimpan.');
        redirect(admin_url('products'));
    }
    if ($act === 'delete_product') {
        db()->prepare("DELETE FROM products WHERE id=?")->execute([(int)$_POST['id']]);
        db()->prepare("DELETE FROM lessons WHERE product_id=?")->execute([(int)$_POST['id']]);
        flash('success', 'Produk dihapus.');
        redirect(admin_url('products'));
    }

    /* --- Simpan materi (lesson) --- */
    if ($act === 'save_lesson') {
        $id = (int)($_POST['id'] ?? 0);
        $pid = (int)$_POST['product_id'];
        $title = trim((string)($_POST['title'] ?? ''));
        $shortDesc = trim((string)($_POST['short_desc'] ?? ''));
        $contentText = trim((string)($_POST['content_text'] ?? ''));
        $youtube = trim((string)($_POST['youtube'] ?? ''));
        $duration = max(0, (int)($_POST['duration_minutes'] ?? 0));
        $resourceTitle = trim((string)($_POST['resource_title'] ?? ''));
        $resourceUrl = trim((string)($_POST['resource_url'] ?? ''));
        $sort = (int)($_POST['sort'] ?? 0);
        if ($id) {
            db()->prepare("UPDATE lessons
                           SET product_id=?, title=?, short_desc=?, content_text=?, youtube=?, duration_minutes=?, resource_title=?, resource_url=?, sort=?
                           WHERE id=?")
               ->execute([$pid, $title, $shortDesc, $contentText, $youtube, $duration, $resourceTitle ?: null, $resourceUrl ?: null, $sort, $id]);
        } else {
            db()->prepare("INSERT INTO lessons
                           (product_id, title, short_desc, content_text, youtube, duration_minutes, resource_title, resource_url, sort)
                           VALUES (?,?,?,?,?,?,?,?,?)")
              ->execute([$pid, $title, $shortDesc, $contentText, $youtube, $duration, $resourceTitle ?: null, $resourceUrl ?: null, $sort]);
        }
        flash('success', 'Materi disimpan.');
        redirect(admin_url('lessons', ['product' => $pid]));
    }
    if ($act === 'delete_lesson') {
        $pid = (int)$_POST['product_id'];
        db()->prepare("DELETE FROM lessons WHERE id=?")->execute([(int)$_POST['id']]);
        flash('success', 'Materi dihapus.');
        redirect(admin_url('lessons', ['product' => $pid]));
    }

    /* --- Simpan salespage --- */
    if ($act === 'save_salespage') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title']);
        $slug  = $_POST['slug'] ? slugify($_POST['slug']) : slugify($title);
        $showHome = isset($_POST['show_home']) ? 1 : 0;
        $salesPixel = preg_replace('/\D+/', '', (string)($_POST['facebook_pixel_id'] ?? ''));
        if (unique_value_exists('salespages', 'slug', $slug, $id)) {
            flash('error', 'Slug salespage sudah dipakai. Gunakan slug lain.');
            redirect(admin_url('salespages', $id ? ['edit' => $id] : ['new' => 1]));
        }
        if ($showHome) db()->query("UPDATE salespages SET show_home=0");
        $up = handle_upload('featured_image', 'uploads', [
            'allowed' => [
                'jpg' => ['image/jpeg', 'image/pjpeg'],
                'jpeg' => ['image/jpeg', 'image/pjpeg'],
                'png' => ['image/png'],
                'webp' => ['image/webp'],
            ],
        ]);
        if (isset($up['error'])) {
            flash('error', $up['error']);
            redirect(admin_url('salespages', $id ? ['edit' => $id] : ['new' => 1]));
        }
        $img = isset($up['file']) ? $up['file'] : null;
        $vals = [$title, $slug, $_POST['html'], trim($_POST['meta_title']), trim($_POST['meta_desc']), $salesPixel ?: null,
                 $_POST['status'] === 'publish' ? 'publish' : 'draft', $showHome];
        if ($id) {
            $sql = "UPDATE salespages SET title=?,slug=?,html=?,meta_title=?,meta_desc=?,facebook_pixel_id=?,status=?,show_home=?"
                 . ($img ? ",featured_image=?" : "") . " WHERE id=?";
            $params = $vals; if ($img) $params[] = $img; $params[] = $id;
            db()->prepare($sql)->execute($params);
        } else {
            $vals[] = $img;
            db()->prepare("INSERT INTO salespages (title,slug,html,meta_title,meta_desc,facebook_pixel_id,status,show_home,featured_image,created_at)
                           VALUES (?,?,?,?,?,?,?,?,?,NOW())")->execute($vals);
        }
        flash('success', 'Salespage disimpan.');
        redirect(admin_url('salespages'));
    }
    if ($act === 'delete_salespage') {
        db()->prepare("DELETE FROM salespages WHERE id=?")->execute([(int)$_POST['id']]);
        flash('success', 'Salespage dihapus.');
        redirect(admin_url('salespages'));
    }

    /* --- Simpan akses produk --- */
    if ($act === 'save_access_page') {
        $id = (int)($_POST['id'] ?? 0);
        $productId = (int)($_POST['product_id'] ?? 0);
        $slug = slugify($_POST['slug'] ?: $_POST['title']);
        $dup = db()->prepare("SELECT id, product_id, slug FROM access_pages WHERE (product_id = ? OR slug = ?) AND id <> ? LIMIT 1");
        $dup->execute([$productId, $slug, $id]);
        $exists = $dup->fetch();
        if ($exists) {
            flash('error', (int)$exists['product_id'] === $productId
                ? 'Produk ini sudah punya access page. Edit yang lama atau pilih produk lain.'
                : 'Slug access page sudah dipakai. Gunakan slug lain.');
            redirect(admin_url('access_pages', $id ? ['edit' => $id] : ['new' => 1]));
        }
        $up = handle_upload('featured_image', 'uploads', [
            'allowed' => [
                'jpg' => ['image/jpeg', 'image/pjpeg'],
                'jpeg' => ['image/jpeg', 'image/pjpeg'],
                'png' => ['image/png'],
                'webp' => ['image/webp'],
            ],
        ]);
        if (isset($up['error'])) {
            flash('error', $up['error']);
            redirect(admin_url('access_pages', $id ? ['edit' => $id] : ['new' => 1]));
        }
        $img = $up['file'] ?? null;
        if ($id) {
            $sql = "UPDATE access_pages SET product_id=?,title=?,slug=?,html=?,meta_title=?,meta_desc=?,status=?"
                 . ($img ? ",featured_image=?" : "") . " WHERE id=?";
            $data = [(int)$_POST['product_id'], trim($_POST['title']), $slug, $_POST['html'], trim($_POST['meta_title']), trim($_POST['meta_desc']), $_POST['status']];
            if ($img) $data[] = $img;
            $data[] = $id;
            db()->prepare($sql)->execute($data);
        } else {
            db()->prepare("INSERT INTO access_pages (product_id,title,slug,html,meta_title,meta_desc,status,featured_image,created_at)
                           VALUES (?,?,?,?,?,?,?,?,NOW())")
               ->execute([$productId, trim($_POST['title']), $slug, $_POST['html'],
                         trim($_POST['meta_title']), trim($_POST['meta_desc']), $_POST['status'], $img]);
        }
        flash('success', 'Akses produk disimpan.');
        redirect(admin_url('access_pages'));
    }
    if ($act === 'delete_access_page') {
        db()->prepare("DELETE FROM access_pages WHERE id=?")->execute([(int)$_POST['id']]);
        flash('success', 'Akses produk dihapus.');
        redirect(admin_url('access_pages'));
    }

    /* --- Simpan post/blog --- */
    if ($act === 'save_post') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $slug = ($_POST['slug'] ?? '') ? slugify($_POST['slug']) : slugify($title);
        $status = ($_POST['status'] ?? '') === 'publish' ? 'publish' : 'draft';
        if (unique_value_exists('posts', 'slug', $slug, $id)) {
            flash('error', 'Slug post sudah dipakai. Gunakan slug lain.');
            redirect(admin_url('posts', $id ? ['edit' => $id] : ['new' => 1]));
        }
        $publishedAt = trim($_POST['published_at'] ?? '');
        $publishedAt = $publishedAt !== '' ? str_replace('T', ' ', substr($publishedAt, 0, 16)) . ':00' : null;
        $excerpt = trim($_POST['excerpt'] ?? '');
        $html = (string)($_POST['html'] ?? '');

        $up = handle_upload('featured_image', 'uploads', [
            'allowed' => [
                'jpg' => ['image/jpeg', 'image/pjpeg'],
                'jpeg' => ['image/jpeg', 'image/pjpeg'],
                'png' => ['image/png'],
                'webp' => ['image/webp'],
            ],
        ]);
        if (isset($up['error'])) {
            flash('error', $up['error']);
            redirect(admin_url('posts', $id ? ['edit' => $id] : ['new' => 1]));
        }
        $img = $up['file'] ?? null;
        $metaDesc = trim($_POST['meta_desc'] ?? '');
        if ($metaDesc === '') $metaDesc = $excerpt !== '' ? $excerpt : excerpt_text($html, 160);

        $vals = [$title, $slug, $excerpt !== '' ? $excerpt : excerpt_text($html, 220), $html, trim($_POST['meta_title'] ?? ''), $metaDesc, $status, $publishedAt];
        if ($id) {
            $sql = "UPDATE posts SET title=?,slug=?,excerpt=?,html=?,meta_title=?,meta_desc=?,status=?,published_at=?"
                 . ($img ? ",featured_image=?" : "") . " WHERE id=?";
            $params = $vals;
            if ($img) $params[] = $img;
            $params[] = $id;
            db()->prepare($sql)->execute($params);
        } else {
            $vals[] = $img;
            db()->prepare("INSERT INTO posts (title,slug,excerpt,html,meta_title,meta_desc,status,published_at,featured_image,created_at)
                           VALUES (?,?,?,?,?,?,?,?,?,NOW())")->execute($vals);
        }
        flash('success', 'Post berhasil disimpan.');
        redirect(admin_url('posts'));
    }
    if ($act === 'delete_post') {
        db()->prepare("DELETE FROM posts WHERE id=?")->execute([(int)$_POST['id']]);
        flash('success', 'Post berhasil dihapus.');
        redirect(admin_url('posts'));
    }

    /* --- Simpan kupon --- */
    if ($act === 'save_coupon') {
        $code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $_POST['code']));
        if ($code === '') {
            flash('error', 'Kode kupon wajib diisi.');
            redirect(admin_url('coupons'));
        }
        if (unique_value_exists('coupons', 'code', $code)) {
            flash('error', 'Kode kupon sudah dipakai. Gunakan kode lain.');
            redirect(admin_url('coupons'));
        }
        db()->prepare("INSERT INTO coupons (name,code,percent,nominal,product_id,affiliate_id,start_date,end_date,max_use,status,created_at)
                       VALUES (?,?,?,?,?,?,?,?,?, 'active', NOW())")
           ->execute([
                trim($_POST['name']), $code, (int)$_POST['percent'], (int)$_POST['nominal'],
                $_POST['product_id'] ?: null, $_POST['affiliate_id'] ?: null,
                $_POST['start_date'] ?: null, $_POST['end_date'] ?: null, (int)$_POST['max_use'],
           ]);
        flash('success', 'Kupon dibuat.');
        redirect(admin_url('coupons'));
    }
    if ($act === 'delete_coupon') {
        db()->prepare("DELETE FROM coupons WHERE id=?")->execute([(int)$_POST['id']]);
        flash('success', 'Kupon dihapus.');
        redirect(admin_url('coupons'));
    }

    /* --- Komisi: tandai dibayar --- */
    if ($act === 'pay_commission') {
        db()->prepare("UPDATE commissions SET status='paid' WHERE id=?")->execute([(int)$_POST['id']]);
        flash('success', 'Komisi ditandai sudah dibayar.');
        redirect(admin_url('commissions'));
    }
    if ($act === 'approve_withdrawal') {
        db()->prepare("UPDATE commission_withdrawals
                       SET status='approved', approved_at=NOW(), admin_note=?
                       WHERE id=? AND status='requested'")
           ->execute([trim($_POST['admin_note'] ?? ''), (int)$_POST['id']]);
        flash('success', 'Request pencairan disetujui.');
        redirect(admin_url('commissions'));
    }
    if ($act === 'reject_withdrawal') {
        $id = (int)$_POST['id'];
        $note = trim($_POST['admin_note'] ?? '');
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $pdo->prepare("UPDATE commission_withdrawals
                           SET status='rejected', rejected_at=NOW(), admin_note=?
                           WHERE id=? AND status IN ('requested','approved')")
                ->execute([$note, $id]);
            $pdo->prepare("UPDATE commissions
                           SET withdrawal_id=NULL
                           WHERE withdrawal_id=? AND status='approved'")
                ->execute([$id]);
            $pdo->commit();
            flash('success', 'Request pencairan ditolak dan saldo dikembalikan ke affiliate.');
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            flash('error', 'Request pencairan gagal ditolak.');
        }
        redirect(admin_url('commissions'));
    }
    if ($act === 'pay_withdrawal') {
        $id = (int)$_POST['id'];
        $note = trim($_POST['admin_note'] ?? '');
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $pdo->prepare("UPDATE commission_withdrawals
                           SET status='paid', paid_at=NOW(), admin_note=?
                           WHERE id=? AND status IN ('requested','approved')")
                ->execute([$note, $id]);
            $pdo->prepare("UPDATE commissions
                           SET status='paid'
                           WHERE withdrawal_id=? AND status='approved'")
                ->execute([$id]);
            $pdo->commit();
            flash('success', 'Pencairan ditandai sudah dibayar.');
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            flash('error', 'Pencairan gagal ditandai dibayar.');
        }
        redirect(admin_url('commissions'));
    }

    /* --- Simpan pengaturan --- */
    if ($act === 'save_settings') {
        $up = handle_upload('logo', 'uploads', [
            'allowed' => [
                'jpg' => ['image/jpeg', 'image/pjpeg'],
                'jpeg' => ['image/jpeg', 'image/pjpeg'],
                'png' => ['image/png'],
                'webp' => ['image/webp'],
            ],
        ]);
        if (isset($up['error'])) {
            flash('error', $up['error']);
            redirect(admin_url('settings'));
        }
        if (isset($up['file'])) set_setting('logo', $up['file']);

        $upf = handle_upload('favicon', 'uploads', [
            'allowed' => [
                'png' => ['image/png'],
                'jpg' => ['image/jpeg', 'image/pjpeg'],
                'jpeg' => ['image/jpeg', 'image/pjpeg'],
                'ico' => ['image/x-icon', 'image/vnd.microsoft.icon', 'application/octet-stream'],
            ],
        ]);
        if (isset($upf['error'])) {
            flash('error', $upf['error']);
            redirect(admin_url('settings'));
        }
        if (isset($upf['file'])) set_setting('favicon', $upf['file']);

        $upq = handle_upload('checkout_qris_image', 'uploads', [
            'allowed' => [
                'jpg' => ['image/jpeg', 'image/pjpeg'],
                'jpeg' => ['image/jpeg', 'image/pjpeg'],
                'png' => ['image/png'],
                'webp' => ['image/webp'],
            ],
        ]);
        if (isset($upq['error'])) {
            flash('error', $upq['error']);
            redirect(admin_url('settings'));
        }
        if (isset($upq['file'])) set_setting('checkout_qris_image', $upq['file']);

        foreach (['site_name','site_email','site_wa','site_address','bank_bca','bank_mandiri','bank_bsi',
                  'fonnte_token','mailketing_token','mail_sender','google_client_id','commission_percent','google_analytics','facebook_pixel',
                  'seo_title','seo_desc','wa_register','wa_purchase','wa_approved','wa_rejected',
                  'home_link_apply','home_link_programs','home_link_consult','home_link_products_more',
                  'home_link_blog_more','home_link_featured_sales','home_link_final_apply','home_link_final_register',
                  'checkout_qris_label','checkout_qris_note',
                  'duitku_merchant_code','duitku_api_key','duitku_expiry_period'] as $k) {
            if (array_key_exists($k, $_POST)) set_setting($k, trim($_POST[$k]));
        }
        foreach ([
            'checkout_qris_enabled',
            'duitku_sandbox',
            'checkout_gateway_xendit','checkout_gateway_duitku','checkout_gateway_midtrans','checkout_gateway_tripay',
            'checkout_bank_1_enabled','checkout_bank_2_enabled','checkout_bank_3_enabled',
            'checkout_bank_4_enabled','checkout_bank_5_enabled','checkout_bank_6_enabled',
        ] as $k) {
            set_setting($k, isset($_POST[$k]) ? '1' : '0');
        }
        foreach ([
            'checkout_bank_1_name','checkout_bank_1_account',
            'checkout_bank_2_name','checkout_bank_2_account',
            'checkout_bank_3_name','checkout_bank_3_account',
            'checkout_bank_4_name','checkout_bank_4_account',
            'checkout_bank_5_name','checkout_bank_5_account',
            'checkout_bank_6_name','checkout_bank_6_account',
        ] as $k) {
            set_setting($k, trim((string)($_POST[$k] ?? '')));
        }
        flash('success', 'Pengaturan disimpan.');
        redirect(admin_url('settings'));
    }

    /* --- Ubah password admin --- */
    if ($act === 'update_admin_password') {
        $u = current_user();
        $currentPassword = (string)($_POST['current_password'] ?? '');
        $newPassword = (string)($_POST['new_password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');

        if (!$u || ($u['role'] ?? 'member') !== 'admin') {
            flash('error', 'Akun admin tidak valid.');
            redirect(admin_url('account'));
        }
        if (!password_verify($currentPassword, (string)$u['password'])) {
            flash('error', 'Password lama tidak sesuai.');
            redirect(admin_url('account'));
        }
        if (strlen($newPassword) < 8) {
            flash('error', 'Password baru minimal 8 karakter.');
            redirect(admin_url('account'));
        }
        if (!hash_equals($newPassword, $confirmPassword)) {
            flash('error', 'Konfirmasi password baru tidak cocok.');
            redirect(admin_url('account'));
        }

        db()->prepare("UPDATE users SET password = ? WHERE id = ? AND role = 'admin'")
            ->execute([password_hash($newPassword, PASSWORD_DEFAULT), (int)$u['id']]);
        log_activity('admin_password_updated', $u['email']);
        flash('success', 'Password admin berhasil diperbarui.');
        redirect(admin_url('account'));
    }

    /* --- Backup database (ekspor sederhana) --- */
    if ($act === 'backup_db') {
        $tables = ['settings','users','products','lessons','salespages','access_pages','posts','coupons','transactions','enrollments','progress','commissions','commission_withdrawals','clicks','categories','activity_logs'];
        $out = "-- Backup The Travel CEO " . date('Y-m-d H:i:s') . "\nSET FOREIGN_KEY_CHECKS=0;\n";
        foreach ($tables as $tb) {
            $rows = db()->query("SELECT * FROM `$tb`")->fetchAll();
            foreach ($rows as $row) {
                $cols = array_map(function($c){ return "`$c`"; }, array_keys($row));
                $vals = array_map(function($v){ return $v === null ? 'NULL' : db()->quote($v); }, array_values($row));
                $out .= "INSERT INTO `$tb` (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ");\n";
            }
        }
        $out .= "SET FOREIGN_KEY_CHECKS=1;\n";
        $dir = dirname(__DIR__) . '/storage/backups';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $file = 'backup_' . date('Ymd_His') . '.sql';
        file_put_contents($dir . '/' . $file, $out);
        log_activity('backup_db', $file);
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        echo $out; exit;
    }
}

/* ============ RENDER VIEW ============ */
require __DIR__ . '/views/layout.php';
