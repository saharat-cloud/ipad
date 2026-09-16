<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/BorrowRecord.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$phone = trim($input['phone'] ?? '');

if (empty($phone)) {
    jsonResponse(['success' => false, 'message' => 'กรุณากรอกเบอร์มือถือ']);
}

$userModel = new User($pdo);
// Search by phone, which is mapped to user_code or barcode_id
$user = null;
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_code = ? OR barcode_id = ? LIMIT 1");
$stmt->execute([$phone, $phone]);
$user = $stmt->fetch();

if (!$user) {
    jsonResponse(['success' => false, 'message' => 'ไม่พบข้อมูลผู้ยืมจากเบอร์นี้']);
}

// Get all active borrows for this user
$stmt = $pdo->prepare("
    SELECT br.*, ip.device_code, ip.device_name, ip.serial_number, ip.model 
    FROM borrow_records br
    JOIN ipads ip ON br.ipad_id = ip.id
    WHERE br.user_id = ? AND br.status IN ('active', 'overdue')
    ORDER BY br.borrowed_at DESC
");
$stmt->execute([$user['id']]);
$borrows = $stmt->fetchAll();

if (empty($borrows)) {
    jsonResponse(['success' => false, 'message' => 'ผู้ใช้นี้ไม่มีรายการยืมที่ค้างอยู่']);
}

jsonResponse([
    'success' => true,
    'user' => [
        'id' => $user['id'],
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name'],
        'full_name' => $user['first_name'] . ' ' . $user['last_name'],
        'role_label' => getRoleLabel($user['role']),
        'avatar' => getAvatarUrl($user['avatar'])
    ],
    'borrows' => $borrows
]);
