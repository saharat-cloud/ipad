<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

// Allow only Admin
if (!isAdmin()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized. Only admin can perform this action.'], 403);
}

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
}

try {
    $pdo->beginTransaction();

    // 1. Reset all iPads to 'available'
    $stmt = $pdo->prepare("UPDATE ipads SET status = 'available'");
    $stmt->execute();

    // 2. Clear borrow records
    // Use DELETE FROM instead of TRUNCATE if there are foreign key constraints, 
    // or TRUNCATE with CASCADE. Since we might want to reset ID, TRUNCATE is good.
    // However, PostgreSQL TRUNCATE restarts identity. Let's use it.
    if (getenv('DB_DRIVER') === 'pgsql') {
        $pdo->exec("TRUNCATE TABLE borrow_records RESTART IDENTITY CASCADE");
        $pdo->exec("TRUNCATE TABLE activity_logs RESTART IDENTITY CASCADE");
    } else {
        // For MySQL
        $pdo->exec("TRUNCATE TABLE borrow_records");
        $pdo->exec("TRUNCATE TABLE activity_logs");
    }

    $pdo->commit();
    jsonResponse(['success' => true, 'message' => 'ล้างข้อมูลประวัติและรีเซ็ตสถานะ iPad ทั้งหมดสำเร็จ']);
} catch (Exception $e) {
    $pdo->rollBack();
    jsonResponse(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], 500);
}
