<?php
class IpadController {
    private Ipad $model;
    private ActivityLog $log;

    public function __construct(private PDO $pdo) {
        $this->model = new Ipad($pdo);
        $this->log   = new ActivityLog($pdo);
    }

    public function index(): void {
        $pdo    = $this->pdo;
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $ipads  = $this->model->getAll($search, $status);
        require_once __DIR__ . '/../views/ipads/index.php';
    }

    public function create(): void {
        $errors = [];
        $data   = ['status' => 'available'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'device_code'   => trim($_POST['device_code'] ?? ''),
                'device_name'   => trim($_POST['device_name'] ?? ''),
                'barcode'       => trim($_POST['barcode'] ?? ''),
                'serial_number' => trim($_POST['serial_number'] ?? ''),
                'model'         => trim($_POST['model'] ?? ''),
                'status'        => $_POST['status'] ?? 'available',
                'notes'         => trim($_POST['notes'] ?? ''),
            ];
            if (!$data['device_code'])   $errors[] = 'กรุณาระบุรหัสเครื่อง';
            if (!$data['device_name'])   $errors[] = 'กรุณาระบุชื่อเครื่อง';
            if (!$data['barcode'])       $errors[] = 'กรุณาระบุ Barcode';
            if (!$data['serial_number']) $errors[] = 'กรุณาระบุ Serial Number';
            if (!$data['model'])         $errors[] = 'กรุณาระบุรุ่น';
            if ($this->model->isCodeTaken($data['device_code']))    $errors[] = 'รหัสเครื่องนี้มีอยู่แล้ว';
            if ($this->model->isBarcodeTaken($data['barcode']))      $errors[] = 'Barcode นี้มีอยู่แล้ว';
            if ($this->model->isSerialTaken($data['serial_number'])) $errors[] = 'Serial Number นี้มีอยู่แล้ว';

            if (empty($errors)) {
                $id = $this->model->create($data);
                $this->log->log('create_ipad', 'ipad', $id, "เพิ่ม iPad: {$data['device_name']}");
                setFlash('success', 'เพิ่ม iPad สำเร็จ');
                redirect('?page=ipads');
            }
        }
        $action = 'create';
        $pdo    = $this->pdo;
        require_once __DIR__ . '/../views/ipads/form.php';
    }

    public function edit(): void {
        $id   = (int)($_GET['id'] ?? 0);
        $ipad = $this->model->findById($id);
        if (!$ipad) { setFlash('error', 'ไม่พบ iPad'); redirect('?page=ipads'); }

        $errors = [];
        $data   = $ipad;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'device_code'   => trim($_POST['device_code'] ?? ''),
                'device_name'   => trim($_POST['device_name'] ?? ''),
                'barcode'       => trim($_POST['barcode'] ?? ''),
                'serial_number' => trim($_POST['serial_number'] ?? ''),
                'model'         => trim($_POST['model'] ?? ''),
                'status'        => $_POST['status'] ?? 'available',
                'notes'         => trim($_POST['notes'] ?? ''),
            ];
            if (!$data['device_code'])   $errors[] = 'กรุณาระบุรหัสเครื่อง';
            if (!$data['device_name'])   $errors[] = 'กรุณาระบุชื่อเครื่อง';
            if (!$data['barcode'])       $errors[] = 'กรุณาระบุ Barcode';
            if (!$data['serial_number']) $errors[] = 'กรุณาระบุ Serial Number';
            if (!$data['model'])         $errors[] = 'กรุณาระบุรุ่น';
            if ($this->model->isCodeTaken($data['device_code'], $id))    $errors[] = 'รหัสเครื่องนี้มีอยู่แล้ว';
            if ($this->model->isBarcodeTaken($data['barcode'], $id))      $errors[] = 'Barcode นี้มีอยู่แล้ว';
            if ($this->model->isSerialTaken($data['serial_number'], $id)) $errors[] = 'Serial Number นี้มีอยู่แล้ว';

            if (empty($errors)) {
                $this->model->update($id, $data);
                $this->log->log('update_ipad', 'ipad', $id, "แก้ไข iPad: {$data['device_name']}");
                setFlash('success', 'แก้ไข iPad สำเร็จ');
                redirect('?page=ipads');
            }
        }
        $action = 'edit';
        $pdo    = $this->pdo;
        require_once __DIR__ . '/../views/ipads/form.php';
    }

    public function delete(): void {
        $id   = (int)($_GET['id'] ?? 0);
        $ipad = $this->model->findById($id);
        if ($ipad) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM borrow_records WHERE ipad_id=? AND status IN ('active','overdue')");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                setFlash('error', 'ไม่สามารถลบได้ เนื่องจาก iPad นี้กำลังถูกยืม');
            } else {
                $this->model->delete($id);
                $this->log->log('delete_ipad', 'ipad', $id, "ลบ iPad: {$ipad['device_name']}");
                setFlash('success', 'ลบ iPad สำเร็จ');
            }
        }
        redirect('?page=ipads');
    }

    public function printBarcode(): void {
        $id   = (int)($_GET['id'] ?? 0);
        $ipad = $this->model->findById($id);
        if (!$ipad) { die('ไม่พบ iPad'); }
        $pdo = $this->pdo;
        require_once __DIR__ . '/../views/ipads/print_barcode.php';
    }
}
