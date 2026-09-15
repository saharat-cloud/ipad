<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Ipad.php';
require_once __DIR__ . '/../models/BorrowRecord.php';
require_once __DIR__ . '/../models/ActivityLog.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);

$recordId    = (int)($_POST['record_id'] ?? 0);
$returnedBy  = (int)($_POST['returned_by'] ?? 0);
$notes       = trim($_POST['notes'] ?? '');

if (!$recordId || !$returnedBy) {
    jsonResponse(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']);
}

$borrowModel = new BorrowRecord($pdo);
$ipadModel   = new Ipad($pdo);
$logModel    = new ActivityLog($pdo);

$record = $borrowModel->findById($recordId);
if (!$record) jsonResponse(['success' => false, 'message' => 'ไม่พบรายการยืม']);
if (!in_array($record['status'], ['active', 'overdue'])) {
    jsonResponse(['success' => false, 'message' => 'รายการนี้ถูกคืนแล้ว']);
}

try {
    $pdo->beginTransaction();

    $borrowModel->doReturn($recordId, $returnedBy, $notes);
    $ipadModel->updateStatus($record['ipad_id'], 'available');
    $logModel->log('return', 'borrow_record', $recordId,
        "คืน {$record['device_name']} ({$record['device_code']}) โดย {$record['first_name']} {$record['last_name']}"
    );

    $pdo->commit();

    jsonResponse([
        'success' => true,
        'message' => 'คืน iPad สำเร็จ!',
        'duration'=> timeDiffHuman($record['borrowed_at']),
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    jsonResponse(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
