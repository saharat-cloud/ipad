<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/Ipad.php';
require_once __DIR__ . '/../models/BorrowRecord.php';

$barcode = trim($_POST['barcode'] ?? $_GET['barcode'] ?? '');
if (!$barcode) jsonResponse(['success' => false, 'message' => 'กรุณาระบุ Barcode']);

$ipadModel   = new Ipad($pdo);
$borrowModel = new BorrowRecord($pdo);

$ipad = $ipadModel->findByBarcode($barcode);
if (!$ipad) jsonResponse(['success' => false, 'message' => 'ไม่พบ iPad กรุณาตรวจสอบ Barcode อีกครั้ง']);

$activeRecord = null;
$borrower     = null;

if (in_array($ipad['status'], ['borrowed', 'maintenance', 'disabled'])) {
    if ($ipad['status'] === 'borrowed') {
        $activeRecord = $borrowModel->getActiveByIpad($ipad['id']);
        if ($activeRecord) {
            $borrower = [
                'name'           => $activeRecord['first_name'] . ' ' . $activeRecord['last_name'],
                'user_code'      => $activeRecord['user_code'],
                'class_position' => $activeRecord['class_position'],
                'borrowed_at'    => formatDateTimeTH($activeRecord['borrowed_at']),
                'due_date'       => formatDateTimeTH($activeRecord['due_date']),
                'duration'       => timeDiffHuman($activeRecord['borrowed_at']),
                'record_id'      => $activeRecord['id'],
                'is_overdue'     => isOverdue($activeRecord['due_date']),
                'status'         => $activeRecord['status'],
            ];
        }
    }
    $statusLabel = getStatusLabel($ipad['status']);
    jsonResponse([
        'success'      => false,
        'blocked'      => true,
        'status'       => $ipad['status'],
        'status_label' => $statusLabel,
        'message'      => "iPad นี้มีสถานะ: $statusLabel",
        'ipad'         => [
            'id'            => $ipad['id'],
            'device_code'   => $ipad['device_code'],
            'device_name'   => $ipad['device_name'],
            'model'         => $ipad['model'],
            'serial_number' => $ipad['serial_number'],
        ],
        'borrower' => $borrower,
    ]);
}

jsonResponse([
    'success' => true,
    'ipad' => [
        'id'            => $ipad['id'],
        'device_code'   => $ipad['device_code'],
        'device_name'   => $ipad['device_name'],
        'model'         => $ipad['model'],
        'serial_number' => $ipad['serial_number'],
        'barcode'       => $ipad['barcode'],
        'status'        => $ipad['status'],
        'status_label'  => getStatusLabel($ipad['status']),
    ],
]);
