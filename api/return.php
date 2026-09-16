<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/Ipad.php';
require_once __DIR__ . '/../models/BorrowRecord.php';
require_once __DIR__ . '/../models/ActivityLog.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$recordIds = $input['record_ids'] ?? [];
$notes = trim($input['notes'] ?? '');

if (empty($recordIds) || !is_array($recordIds)) {
    jsonResponse(['success' => false, 'message' => 'กรุณาเลือกรายการที่ต้องการคืน']);
}

$borrowModel = new BorrowRecord($pdo);
$ipadModel = new Ipad($pdo);
$logModel = new ActivityLog($pdo);

try {
    $pdo->beginTransaction();

    foreach ($recordIds as $rid) {
        $record = $borrowModel->findById((int)$rid);
        if (!$record) continue;

        if (!in_array($record['status'], ['active', 'overdue'])) {
            continue; // Already returned or pending
        }

        // Update record status to pending_return
        $stmt = $pdo->prepare("UPDATE borrow_records SET status = 'pending_return', notes = CASE WHEN ? != '' THEN ? ELSE notes END WHERE id = ?");
        $stmt->execute([$notes, $notes, $rid]);

        // Update iPad status to checking (or keep it borrowed, but checking is safer)
        $ipadModel->updateStatus($record['ipad_id'], 'checking');

        $logModel->log('return_request', 'borrow_record', $rid,
            "ส่งคำขอคืน {$record['device_name']} ({$record['device_code']}) รอตรวจสอบ"
        );
    }
    $pdo->commit();

    // Collect info for LINE Notify
    $returnedIpads = [];
    $borrowerName = '';
    
    foreach ($recordIds as $rid) {
        $record = $borrowModel->findById((int)$rid);
        if ($record) {
            $returnedIpads[] = "- " . $record['device_code'] . " (" . $record['model'] . ")";
            if (!$borrowerName) {
                $borrowerName = $record['first_name'] . ' ' . $record['last_name'];
                if ($record['class_position']) $borrowerName .= " (" . $record['class_position'] . ")";
            }
        }
    }
    
    if (!empty($returnedIpads)) {
        $msg = "\n🔔 มีรายการขอคืน iPad (รออนุมัติ)\n";
        $msg .= "👨‍🎓 ผู้คืน: " . $borrowerName . "\n";
        $msg .= "📱 เครื่องที่คืน:\n" . implode("\n", $returnedIpads);
        if (!empty($notes)) {
            $msg .= "\n📝 หมายเหตุ: " . $notes;
        }
        sendLineNotify($msg);
    }

    jsonResponse([
        'success' => true,
        'message' => 'ส่งคำขอคืนสำเร็จ'
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    jsonResponse(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
