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
$unreturnedActions = $input['unreturned_actions'] ?? [];

if (empty($recordIds) || !is_array($recordIds)) {
    jsonResponse(['success' => false, 'message' => 'กรุณาเลือกรายการที่ต้องการคืน']);
}

$borrowModel = new BorrowRecord($pdo);
$ipadModel = new Ipad($pdo);
$logModel = new ActivityLog($pdo);

try {
    $pdo->beginTransaction();

    // 1. Handle Returned iPads (pending_return)
    foreach ($recordIds as $rid) {
        $record = $borrowModel->findById((int)$rid);
        if (!$record) continue;

        if (!in_array($record['status'], ['active', 'overdue'])) continue;

        // Update record status to pending_return
        $stmt = $pdo->prepare("UPDATE borrow_records SET status = 'pending_return', notes = CASE WHEN ? != '' THEN ? ELSE notes END WHERE id = ?");
        $stmt->execute([$notes, $notes, $rid]);

        // Update iPad status to checking
        $ipadModel->updateStatus($record['ipad_id'], 'checking');

        $logModel->log('return_request', 'borrow_record', $rid,
            "ส่งคำขอคืน {$record['device_name']} ({$record['device_code']}) รอตรวจสอบ"
        );
    }
    
    // 2. Handle Unreturned iPads actions
    if (!empty($unreturnedActions) && is_array($unreturnedActions)) {
        foreach ($unreturnedActions as $action) {
            $rid = (int)($action['record_id'] ?? 0);
            $act = $action['action'] ?? '';
            $detail = $action['detail'] ?? '';
            
            $record = $borrowModel->findById($rid);
            if (!$record) continue;
            
            if ($act === 'extend') {
                // Update due date
                $stmt = $pdo->prepare("UPDATE borrow_records SET due_date = ? WHERE id = ?");
                $stmt->execute([$detail, $rid]);
                $logModel->log('extend_borrow', 'borrow_record', $rid, "ขอยืมต่อจนถึง " . formatDateTimeTH($detail));
            } else if ($act === 'reason') {
                // Append note
                $newNote = trim($record['notes'] . " [แจ้งคืนไม่ครบ: $detail]");
                $stmt = $pdo->prepare("UPDATE borrow_records SET notes = ? WHERE id = ?");
                $stmt->execute([$newNote, $rid]);
                $logModel->log('partial_return_reason', 'borrow_record', $rid, "แจ้งเหตุผลไม่ส่งคืน: $detail");
            }
        }
    }

    $pdo->commit();

    jsonResponse([
        'success' => true,
        'message' => 'ส่งคำขอคืนสำเร็จ'
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    jsonResponse(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
