<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$systemUser = $_SESSION['system_user'] ?? null;
if (!isLoggedIn() || !$systemUser || !in_array($systemUser['role'], ['admin', 'staff'])) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized']);
}

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Ipad.php';
require_once __DIR__ . '/../models/BorrowRecord.php';
require_once __DIR__ . '/../models/ActivityLog.php';

$userModel = new User($pdo);
$ipadModel = new Ipad($pdo);
$borrowModel = new BorrowRecord($pdo);
$logModel = new ActivityLog($pdo);

$action = $_GET['action'] ?? '';
$data = json_decode(file_get_contents('php://input'), true);

if ($action === 'search_user') {
    $phone = sanitize($data['phone'] ?? '');
    if (!$phone) {
        jsonResponse(['success' => false, 'message' => 'เบอร์โทรศัพท์ไม่ถูกต้อง']);
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_code = ? LIMIT 1");
    $stmt->execute([$phone]);
    $user = $stmt->fetch();

    if ($user) {
        jsonResponse(['success' => true, 'user' => $user]);
    } else {
        jsonResponse(['success' => false, 'message' => 'User not found']);
    }

} elseif ($action === 'lend') {
    $phone = sanitize($data['phone'] ?? '');
    $fname = sanitize($data['fname'] ?? '');
    $lname = sanitize($data['lname'] ?? '');
    $cpos = sanitize($data['class_position'] ?? '');
    $dueDate = $data['due_date'] ?? '';
    $notes = sanitize($data['notes'] ?? '');
    $ipads = $data['ipads'] ?? [];

    if (!$phone || !$fname || !$lname || !$cpos || !$dueDate || empty($ipads)) {
        jsonResponse(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']);
    }

    try {
        $pdo->beginTransaction();

        // Find or create user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE user_code = ? LIMIT 1");
        $stmt->execute([$phone]);
        $u = $stmt->fetch();

        if ($u) {
            $userId = $u['id'];
            // Update user info if changed
            $stmt = $pdo->prepare("UPDATE users SET first_name=?, last_name=?, class_position=? WHERE id=?");
            $stmt->execute([$fname, $lname, $cpos, $userId]);
        } else {
            // Create new user
            $userId = $userModel->create([
                'user_code' => $phone,
                'first_name' => $fname,
                'last_name' => $lname,
                'class_position' => $cpos,
                'role' => 'user'
            ]);
        }

        // Create borrow records
        $dueDt = new DateTime($dueDate);
        $now = date('Y-m-d H:i:s');
        $staffId = $_SESSION['system_user_id'];

        foreach ($ipads as $ipadId) {
            $ipad = $ipadModel->findById((int)$ipadId);
            if (!$ipad || $ipad['status'] !== 'available') {
                throw new Exception("iPad ({$ipad['device_code']}) ไม่พร้อมใช้งาน");
            }

            $recordId = $borrowModel->create([
                'user_id'    => $userId,
                'ipad_id'    => $ipad['id'],
                'borrowed_at'=> $now,
                'due_date'   => $dueDt->format('Y-m-d H:i:s'),
                'staff_id'   => $staffId,
                'notes'      => $notes,
            ]);

            $ipadModel->updateStatus($ipad['id'], 'borrowed');
            $logModel->log('staff_lend', 'borrow_record', $recordId,
                "เจ้าหน้าที่ให้ยืม {$ipad['device_name']} ({$ipad['device_code']}) แก่ {$fname} {$lname}"
            );
        }

        $pdo->commit();
        jsonResponse(['success' => true]);

    } catch (Exception $e) {
        $pdo->rollBack();
        jsonResponse(['success' => false, 'message' => $e->getMessage()]);
    }

} elseif ($action === 'search_borrows') {
    $query = sanitize($data['query'] ?? '');
    if (!$query) {
        jsonResponse(['success' => false, 'message' => 'คำค้นหาไม่ถูกต้อง']);
    }

    $sql = "
        SELECT br.*, 
               ip.device_code, ip.device_name, 
               u.first_name, u.last_name, u.user_code
        FROM borrow_records br
        JOIN ipads ip ON br.ipad_id = ip.id
        JOIN users u ON br.user_id = u.id
        WHERE br.status IN ('active', 'overdue')
          AND (ip.device_code LIKE ? OR u.user_code = ?)
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['%'.$query.'%', $query]);
    $borrows = $stmt->fetchAll();

    jsonResponse(['success' => true, 'borrows' => $borrows]);

} elseif ($action === 'return') {
    $records = $data['records'] ?? [];
    if (empty($records)) {
        jsonResponse(['success' => false, 'message' => 'ไม่ได้เลือกรายการ']);
    }

    try {
        $pdo->beginTransaction();
        $staffId = $_SESSION['system_user_id'];
        $now = date('Y-m-d H:i:s');

        foreach ($records as $rid) {
            $record = $borrowModel->findById((int)$rid);
            if (!$record || !in_array($record['status'], ['active', 'overdue', 'pending_return'])) {
                continue;
            }

            // Mark returned
            $stmt = $pdo->prepare("UPDATE borrow_records SET status = 'returned', returned_at = ?, returned_by = ? WHERE id = ?");
            $stmt->execute([$now, $staffId, $rid]);

            // Update iPad status to available
            $ipadModel->updateStatus($record['ipad_id'], 'available');

            $logModel->log('staff_return', 'borrow_record', $rid, "เจ้าหน้าที่รับคืน {$record['device_code']}");
        }

        $pdo->commit();
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonResponse(['success' => false, 'message' => $e->getMessage()]);
    }

} elseif ($action === 'search_ipad') {
    $code = sanitize($data['code'] ?? '');
    if (!$code) {
        jsonResponse(['success' => false]);
    }

    $stmt = $pdo->prepare("SELECT * FROM ipads WHERE device_code = ? LIMIT 1");
    $stmt->execute([$code]);
    $ipad = $stmt->fetch();

    if ($ipad) {
        jsonResponse(['success' => true, 'ipad' => $ipad]);
    } else {
        jsonResponse(['success' => false]);
    }

} elseif ($action === 'issue') {
    $ipadId = (int)($data['ipad_id'] ?? 0);
    $status = sanitize($data['status'] ?? ''); // broken, lost
    $notes = sanitize($data['notes'] ?? '');

    if (!$ipadId || !in_array($status, ['broken', 'lost'])) {
        jsonResponse(['success' => false, 'message' => 'ข้อมูลไม่ถูกต้อง']);
    }

    $ipad = $ipadModel->findById($ipadId);
    if (!$ipad) {
        jsonResponse(['success' => false, 'message' => 'ไม่พบ iPad']);
    }

    $ipadModel->updateStatus($ipadId, $status);
    $stmt = $pdo->prepare("UPDATE ipads SET notes = ? WHERE id = ?");
    $stmt->execute([$notes, $ipadId]);

    $logModel->log('ipad_issue', 'ipad', $ipadId, "เจ้าหน้าที่แจ้งปัญหา: " . getStatusLabel($status) . " - " . $notes);

    jsonResponse(['success' => true]);
}

jsonResponse(['success' => false, 'message' => 'Invalid action']);
