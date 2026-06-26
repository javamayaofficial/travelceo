<?php
/**
 * index.php — Pintu masuk utama (router) untuk halaman publik & member.
 * Routing memakai query string (?p=halaman) agar berjalan di semua
 * shared hosting tanpa perlu mod_rewrite.
 */
require __DIR__ . '/app/bootstrap.php';

require __DIR__ . '/app/controllers/PageController.php';
require __DIR__ . '/app/controllers/AuthController.php';
require __DIR__ . '/app/controllers/CheckoutController.php';
require __DIR__ . '/app/controllers/MemberController.php';

$requestPath = trim((string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$scriptDir = trim((string)dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/.');
if ($scriptDir !== '' && str_starts_with($requestPath, $scriptDir . '/')) {
    $requestPath = substr($requestPath, strlen($scriptDir) + 1);
}

$p = $_GET['p'] ?? '';
if ($p === '' && $requestPath === 'login-member') $p = 'login';
if ($p === '' && $requestPath === 'login-panel') $p = 'login-panel';
if ($p === '') $p = 'home';

switch ($p) {
    // Publik
    case 'home':        page_home(); break;
    case 'sales':       page_salespage($_GET['slug'] ?? ''); break;
    case 'products':    page_products(); break;
    case 'product':     page_product($_GET['slug'] ?? ''); break;
    case 'access':      page_access($_GET['slug'] ?? ''); break;
    case 'blog':        page_blog(); break;
    case 'post':        page_post($_GET['slug'] ?? ''); break;

    // Auth
    case 'register':    auth_register(); break;
    case 'login':       auth_login(); break;
    case 'login-panel': auth_admin_login(); break;
    case 'login-panel-otp-request': auth_admin_otp_request(); break;
    case 'login-panel-otp-verify': auth_admin_otp_verify(); break;
    case 'login-otp-request': auth_login_otp_request(); break;
    case 'login-otp-verify': auth_login_otp_verify(); break;
    case 'login-google': auth_google_login(); break;
    case 'logout':      auth_logout(); break;
    case 'forgot':      auth_forgot(); break;
    case 'reset-password': auth_reset_password(); break;

    // Checkout
    case 'checkout':    checkout_show(); break;
    case 'checkout-process': checkout_process(); break;
    case 'ticket':      member_ticket_view(); break;
    case 'ticket-verify': member_ticket_verify(); break;

    // Member
    case 'dashboard':   member_dashboard(); break;
    case 'learn':       member_learn(); break;
    case 'complete':    member_complete_lesson(); break;
    case 'affiliate':   member_affiliate(); break;
    case 'profile':     member_profile(); break;

    default:
        http_response_code(404);
        page_home();
}
