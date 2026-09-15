<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/BorrowRecord.php';

if (!isLoggedIn()) jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);

$file = $_FILES['avatar'] ?? null;
$userId = (int)($_POST['user_id'] ?? 0);

if (!$file || !$userId) jsonResponse(['success' => false, 'message' => 'ข้อมูลไม่ครบ']);

$userModel = new User($pdo);
$user = $userModel->findById($userId);
if (!$user) jsonResponse(['success' => false, 'message' => 'ไม่พบผู้ใช้']);

$allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($file['type'], $allowed)) {
    jsonResponse(['success' => false, 'message' => 'ชนิดไฟล์ไม่รองรับ (รองรับ JPG, PNG, GIF, WEBP)']);
}
if ($file['size'] > 2 * 1024 * 1024) {
    jsonResponse(['success' => false, 'message' => 'ไฟล์ขนาดเกิน 2MB']);
}

$fileData = file_get_contents($file['tmp_name']);
$base64 = 'data:' . $file['type'] . ';base64,' . base64_encode($fileData);

// Delete old avatar from local storage if it was a file (legacy support)
if ($user['avatar'] && !str_starts_with($user['avatar'], 'data:image/') && file_exists(UPLOAD_PATH . $user['avatar'])) {
    @unlink(UPLOAD_PATH . $user['avatar']);
}

$userModel->updateAvatar($userId, $base64);
jsonResponse(['success' => true, 'url' => $base64, 'filename' => 'base64_image']);
