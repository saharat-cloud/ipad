<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$phone     = trim($_POST['phone'] ?? '');
$firstName = trim($_POST['first_name'] ?? '');
$lastName  = trim($_POST['last_name'] ?? '');
$role      = trim($_POST['role'] ?? '');

if (!$phone || !$firstName || !$lastName || !$role) {
    jsonResponse(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
}

$userModel = new User($pdo);

// ค้นหาจากเบอร์โทร (ใช้ user_code ในการเก็บเบอร์โทร)
$user = $userModel->findByCode($phone);

if ($user) {
    // ถ้ามีผู้ใช้นี้อยู่แล้ว อัปเดตข้อมูลให้เป็นปัจจุบัน
    $userModel->update($user['id'], [
        'user_code'      => $phone,
        'barcode_id'     => $phone, // ใช้เบอร์โทรเป็นบาร์โค้ดด้วย
        'first_name'     => $firstName,
        'last_name'      => $lastName,
        'class_position' => $user['class_position'], // เก็บค่าเดิมไว้
        'role'           => $role,
        'is_active'      => 1
    ]);
    
    // ดึงข้อมูลล่าสุด
    $user = $userModel->findById($user['id']);
} else {
    // ถ้ายังไม่มี สร้างใหม่เลย
    $newId = $userModel->create([
        'user_code'      => $phone,
        'barcode_id'     => $phone,
        'first_name'     => $firstName,
        'last_name'      => $lastName,
        'class_position' => '',
        'role'           => $role,
    ]);
    
    $user = $userModel->findById($newId);
}

$avatarUrl = getAvatarUrl($user['avatar'] ?? null);

// Check if user has any unreturned iPads
require_once __DIR__ . '/../models/BorrowRecord.php';
$borrowModel = new BorrowRecord($pdo);
$activeBorrows = $borrowModel->getActiveByUser($user['id']);
$unreturnedCount = count($activeBorrows);

jsonResponse([
    'success' => true,
    'unreturned_count' => $unreturnedCount,
    'user' => [
        'id'             => $user['id'],
        'user_code'      => $user['user_code'],
        'first_name'     => $user['first_name'],
        'last_name'      => $user['last_name'],
        'full_name'      => $user['first_name'] . ' ' . $user['last_name'],
        'role'           => $user['role'],
        'role_label'     => getRoleLabel($user['role']),
        'avatar_url'     => $avatarUrl,
    ],
]);
