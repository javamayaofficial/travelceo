<?php
/**
 * index.php — Pintu masuk utama (router) untuk halaman publik & member.
 * Mendukung URL rapi berbasis path dan tetap kompatibel dengan query string lama.
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
if ($p === '') {
    $segments = $requestPath === '' ? [] : explode('/', $requestPath);
    switch ($segments[0] ?? '') {
        case '':
        case 'index.php':
            $p = 'home';
            break;
        case 'login-member':
            $p = 'login';
            break;
        case 'login-panel':
            $p = 'login-panel';
            break;
        case 'sales':
            $p = 'sales';
            $_GET['slug'] = $_GET['slug'] ?? ($segments[1] ?? '');
            break;
        case 'products':
            if (!empty($segments[1])) {
                $p = 'product';
                $_GET['slug'] = $_GET['slug'] ?? $segments[1];
            } else {
                $p = 'products';
            }
            break;
        case 'checkout':
            $p = 'checkout';
            $_GET['slug'] = $_GET['slug'] ?? ($segments[1] ?? '');
            break;
        case 'blog':
            if (!empty($segments[1])) {
                $p = 'post';
                $_GET['slug'] = $_GET['slug'] ?? $segments[1];
            } else {
                $p = 'blog';
            }
            break;
        case 'access':
            $p = 'access';
            $_GET['slug'] = $_GET['slug'] ?? ($segments[1] ?? '');
            break;
        case 'register':
            $p = 'register';
            break;
        case 'logout':
            $p = 'logout';
            break;
        case 'forgot':
            $p = 'forgot';
            break;
        case 'reset-password':
            $p = 'reset-password';
            $_GET['token'] = $_GET['token'] ?? ($segments[1] ?? '');
            break;
        case 'dashboard':
            $p = 'dashboard';
            break;
        case 'affiliate':
            $p = 'affiliate';
            break;
        case 'profile':
            $p = 'profile';
            break;
        case 'ticket':
            if (($segments[1] ?? '') === 'verify') {
                $p = 'ticket-verify';
                $_GET['token'] = $_GET['token'] ?? ($segments[2] ?? '');
            } else {
                $p = 'ticket';
                $_GET['token'] = $_GET['token'] ?? ($segments[1] ?? '');
            }
            break;
        default:
            $p = '__404__';
            break;
    }
}

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
    case 'checkout-result': checkout_result(); break;
    case 'checkout-duitku-callback': checkout_duitku_callback(); break;
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
