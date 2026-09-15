<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Ipad.php';
require_once __DIR__ . '/../models/BorrowRecord.php';
require_once __DIR__ . '/../models/ActivityLog.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);

$userId   = (int)($_POST['user_id'] ?? 0);
$ipadId   = (int)($_POST['ipad_id'] ?? 0);
$dueDate  = trim($_POST['due_date'] ?? '');
$notes    = trim($_POST['notes'] ?? '');

if (!$userId || !$ipadId || !$dueDate) {
    jsonResponse(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']);
}

// Validate due date
try {
    $dueDt = new DateTime($dueDate);
    if ($dueDt <= new DateTime()) {
        jsonResponse(['success' => false, 'message' => 'กำหนดคืนต้องเป็นเวลาในอนาคต']);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'รูปแบบวันที่ไม่ถูกต้อง']);
}

$userModel   = new User($pdo);
$ipadModel   = new Ipad($pdo);
$borrowModel = new BorrowRecord($pdo);
$logModel    = new ActivityLog($pdo);

$user = $userModel->findById($userId);
$ipad = $ipadModel->findById($ipadId);

if (!$user) jsonResponse(['success' => false, 'message' => 'ไม่พบผู้ใช้']);
if (!$ipad) jsonResponse(['success' => false, 'message' => 'ไม่พบ iPad']);
if ($ipad['status'] !== 'available') {
    jsonResponse(['success' => false, 'message' => 'iPad นี้ไม่พร้อมใช้งาน สถานะ: ' . getStatusLabel($ipad['status'])]);
}

try {
    $pdo->beginTransaction();

    $recordId = $borrowModel->create([
        'user_id'    => $userId,
        'ipad_id'    => $ipadId,
        'borrowed_at'=> date('Y-m-d H:i:s'),
        'due_date'   => $dueDt->format('Y-m-d H:i:s'),
        'staff_id'   => $_SESSION['system_user_id'] ?? null,
        'notes'      => $notes,
    ]);

    $ipadModel->updateStatus($ipadId, 'borrowed');
    $logModel->log('borrow', 'borrow_record', $recordId,
        "ยืม {$ipad['device_name']} ({$ipad['device_code']}) โดย {$user['first_name']} {$user['last_name']} ({$user['user_code']})"
    );

    $pdo->commit();

    jsonResponse([
        'success'   => true,
        'message'   => 'ยืม iPad สำเร็จ!',
        'record_id' => $recordId,
        'due_date'  => formatDateTimeTH($dueDt->format('Y-m-d H:i:s')),
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    jsonResponse(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
