<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/Ipad.php';
require_once __DIR__ . '/../models/BorrowRecord.php';
require_once __DIR__ . '/../models/ActivityLog.php';

if (!isset($_SESSION['system_user_id'])) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$recordIds = $input['record_ids'] ?? [];

if (empty($recordIds) || !is_array($recordIds)) {
    jsonResponse(['success' => false, 'message' => 'กรุณาเลือกรายการที่ต้องการอนุมัติ']);
}

$borrowModel = new BorrowRecord($pdo);
$ipadModel = new Ipad($pdo);
$logModel = new ActivityLog($pdo);
$adminId = $_SESSION['system_user_id'];

try {
    $pdo->beginTransaction();

    foreach ($recordIds as $rid) {
        $record = $borrowModel->findById((int)$rid);
        if (!$record) continue;

        if ($record['status'] !== 'pending_return') {
            continue; // Only approve pending returns
        }

        // Call doReturn to finalize
        $borrowModel->doReturn((int)$rid, $adminId, '');

        // Update iPad status to available
        $ipadModel->updateStatus($record['ipad_id'], 'available');

        $logModel->log('approve_return', 'borrow_record', $rid,
            "อนุมัติการคืน {$record['device_name']} ({$record['device_code']})"
        );
    }

    $pdo->commit();

    jsonResponse([
        'success' => true,
        'message' => 'อนุมัติการคืนสำเร็จ'
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    jsonResponse(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
