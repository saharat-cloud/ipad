<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/User.php';

$barcode = trim($_POST['barcode'] ?? $_GET['barcode'] ?? '');
if (!$barcode) jsonResponse(['success' => false, 'message' => 'กรุณาระบุ Barcode']);

$userModel = new User($pdo);
$user = $userModel->findByBarcode($barcode);

if (!$user) {
    // Try searching by user_code
    $user = $userModel->findByCode($barcode);
}

if (!$user) {
    jsonResponse(['success' => false, 'message' => 'ไม่พบข้อมูลผู้ใช้ กรุณาตรวจสอบ Barcode อีกครั้ง']);
}

$avatarUrl = getAvatarUrl($user['avatar']);

jsonResponse([
    'success' => true,
    'user' => [
        'id'             => $user['id'],
        'user_code'      => $user['user_code'],
        'first_name'     => $user['first_name'],
        'last_name'      => $user['last_name'],
        'full_name'      => $user['first_name'] . ' ' . $user['last_name'],
        'class_position' => $user['class_position'],
        'role'           => $user['role'],
        'role_label'     => getRoleLabel($user['role']),
        'avatar_url'     => $avatarUrl,
    ],
]);
