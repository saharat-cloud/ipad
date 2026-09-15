<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

// Auto-update overdue records every page load
if (isset($pdo)) {
    try {
        $pdo->exec("UPDATE borrow_records SET status='overdue' WHERE status='active' AND due_date < NOW()");
    } catch (Exception $e) {}
}

$page = $_GET['page'] ?? 'borrow';
$public_pages = ['login', 'borrow', 'return'];

// Auth guard
if (!isLoggedIn() && !in_array($page, $public_pages)) {
    redirect('?page=login');
}
if (isLoggedIn() && $page === 'login') {
    redirect('?page=dashboard');
}

// Load models
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Ipad.php';
require_once __DIR__ . '/models/BorrowRecord.php';
require_once __DIR__ . '/models/ActivityLog.php';

// Router
switch ($page) {


    case 'login':
    case 'logout':
        require_once __DIR__ . '/controllers/AuthController.php';
        (new AuthController($pdo))->handle();
        break;

    case 'dashboard':
        requireLogin();
        require_once __DIR__ . '/controllers/DashboardController.php';
        (new DashboardController($pdo))->index();
        break;

    case 'borrow':
        require_once __DIR__ . '/controllers/BorrowController.php';
        (new BorrowController($pdo))->index();
        break;

    case 'return':
        require_once __DIR__ . '/controllers/ReturnController.php';
        (new ReturnController($pdo))->index();
        break;

    case 'users':
        requireRole('admin');
        require_once __DIR__ . '/controllers/UserController.php';
        (new UserController($pdo))->index();
        break;

    case 'user_create':
        requireRole('admin');
        require_once __DIR__ . '/controllers/UserController.php';
        (new UserController($pdo))->create();
        break;

    case 'user_edit':
        requireRole('admin');
        require_once __DIR__ . '/controllers/UserController.php';
        (new UserController($pdo))->edit();
        break;

    case 'user_delete':
        requireRole('admin');
        require_once __DIR__ . '/controllers/UserController.php';
        (new UserController($pdo))->delete();
        break;

    case 'ipads':
        requireRole('admin');
        require_once __DIR__ . '/controllers/IpadController.php';
        (new IpadController($pdo))->index();
        break;

    case 'ipad_create':
        requireRole('admin');
        require_once __DIR__ . '/controllers/IpadController.php';
        (new IpadController($pdo))->create();
        break;

    case 'ipad_edit':
        requireRole('admin');
        require_once __DIR__ . '/controllers/IpadController.php';
        (new IpadController($pdo))->edit();
        break;

    case 'ipad_delete':
        requireRole('admin');
        require_once __DIR__ . '/controllers/IpadController.php';
        (new IpadController($pdo))->delete();
        break;

    case 'ipad_print':
        requireLogin();
        require_once __DIR__ . '/controllers/IpadController.php';
        (new IpadController($pdo))->printBarcode();
        break;

    case 'history':
        requireLogin();
        require_once __DIR__ . '/controllers/ReportController.php';
        (new ReportController($pdo))->history();
        break;

    case 'reports':
        requireLogin();
        require_once __DIR__ . '/controllers/ReportController.php';
        (new ReportController($pdo))->index();
        break;

    case 'logs':
        requireRole('admin');
        require_once __DIR__ . '/controllers/LogController.php';
        (new LogController($pdo))->index();
        break;

    default:
        http_response_code(404);
        echo '<div style="text-align:center;padding:50px;font-family:sans-serif"><h1>404</h1><p>ไม่พบหน้าที่ต้องการ</p><a href="?page=dashboard">กลับหน้าหลัก</a></div>';
}
