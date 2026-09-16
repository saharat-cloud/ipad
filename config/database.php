<?php
date_default_timezone_set('Asia/Bangkok');
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'ipad_system');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'ระบบยืม-คืน iPad');
$app_url = getenv('APP_URL') ?: (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . "/ipad";
if (getenv('VERCEL') == '1') {
    $app_url = "https://$_SERVER[HTTP_HOST]";
}
define('APP_URL', rtrim($app_url, '/'));

define('UPLOAD_PATH', __DIR__ . '/../uploads/avatars/');
define('UPLOAD_URL', APP_URL . '/uploads/avatars/');
define('DEFAULT_AVATAR', APP_URL . '/assets/img/default_avatar.png');

try {
    $db_driver = getenv('DB_DRIVER') ?: 'pgsql';
    $db_port = getenv('DB_PORT') ?: '5432';
    
    if ($db_driver === 'pgsql') {
        $dsn = "pgsql:host=" . DB_HOST . ";port=" . $db_port . ";dbname=" . DB_NAME . ";options='--client_encoding=" . DB_CHARSET . "'";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
    } else {
        // Fallback for local MySQL if needed
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
    }
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    if ($db_driver === 'pgsql') {
        $pdo->exec("SET TIME ZONE 'Asia/Bangkok'");
    } else {
        $pdo->exec("SET time_zone = '+07:00'");
    }
} catch (PDOException $e) {
    if (defined('DOING_INSTALL') && DOING_INSTALL) {
        $pdo = null;
    } else {
        http_response_code(500);
        die(json_encode(['success' => false, 'message' => 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้: ' . $e->getMessage()]));
    }
}
