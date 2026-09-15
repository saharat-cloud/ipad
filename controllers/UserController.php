<?php
class UserController {
    private User $model;
    private ActivityLog $log;

    public function __construct(private PDO $pdo) {
        $this->model = new User($pdo);
        $this->log   = new ActivityLog($pdo);
    }

    public function index(): void {
        $pdo    = $this->pdo;
        $search = $_GET['search'] ?? '';
        $role   = $_GET['role'] ?? '';
        $users  = $this->model->getAll($search, $role);
        require_once __DIR__ . '/../views/users/index.php';
    }

    public function create(): void {
        $errors = [];
        $data   = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'user_code'      => trim($_POST['user_code'] ?? ''),
                'barcode_id'     => trim($_POST['barcode_id'] ?? ''),
                'first_name'     => trim($_POST['first_name'] ?? ''),
                'last_name'      => trim($_POST['last_name'] ?? ''),
                'class_position' => trim($_POST['class_position'] ?? ''),
                'role'           => $_POST['role'] ?? 'student',
            ];
            // Validate
            if (!$data['user_code'])   $errors[] = 'กรุณาระบุรหัสผู้ใช้';
            if (!$data['barcode_id'])  $errors[] = 'กรุณาระบุ Barcode ID';
            if (!$data['first_name'])  $errors[] = 'กรุณาระบุชื่อ';
            if (!$data['last_name'])   $errors[] = 'กรุณาระบุนามสกุล';
            if ($this->model->isCodeTaken($data['user_code']))    $errors[] = 'รหัสผู้ใช้นี้มีอยู่แล้ว';
            if ($this->model->isBarcodeTaken($data['barcode_id'])) $errors[] = 'Barcode นี้มีอยู่แล้ว';

            if (empty($errors)) {
                // Handle avatar upload
                if (!empty($_FILES['avatar']['tmp_name'])) {
                    $tmpId = 0; // will update after insert
                    $id = $this->model->create($data);
                    $filename = handleUploadedAvatar($_FILES['avatar'], $id);
                    if ($filename) $this->model->updateAvatar($id, $filename);
                } else {
                    $id = $this->model->create($data);
                }
                $this->log->log('create_user', 'user', $id, "เพิ่มผู้ใช้: {$data['first_name']} {$data['last_name']}");
                setFlash('success', 'เพิ่มผู้ใช้สำเร็จ');
                redirect('?page=users');
            }
        }
        $action = 'create';
        $pdo    = $this->pdo;
        require_once __DIR__ . '/../views/users/form.php';
    }

    public function edit(): void {
        $id   = (int)($_GET['id'] ?? 0);
        $user = $this->model->findById($id);
        if (!$user) { setFlash('error', 'ไม่พบผู้ใช้'); redirect('?page=users'); }

        $errors = [];
        $data   = $user;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'user_code'      => trim($_POST['user_code'] ?? ''),
                'barcode_id'     => trim($_POST['barcode_id'] ?? ''),
                'first_name'     => trim($_POST['first_name'] ?? ''),
                'last_name'      => trim($_POST['last_name'] ?? ''),
                'class_position' => trim($_POST['class_position'] ?? ''),
                'role'           => $_POST['role'] ?? 'student',
                'is_active'      => (int)($_POST['is_active'] ?? 1),
            ];
            if (!$data['user_code'])  $errors[] = 'กรุณาระบุรหัสผู้ใช้';
            if (!$data['barcode_id']) $errors[] = 'กรุณาระบุ Barcode ID';
            if (!$data['first_name']) $errors[] = 'กรุณาระบุชื่อ';
            if (!$data['last_name'])  $errors[] = 'กรุณาระบุนามสกุล';
            if ($this->model->isCodeTaken($data['user_code'], $id))    $errors[] = 'รหัสผู้ใช้นี้มีอยู่แล้ว';
            if ($this->model->isBarcodeTaken($data['barcode_id'], $id)) $errors[] = 'Barcode นี้มีอยู่แล้ว';

            if (empty($errors)) {
                $this->model->update($id, $data);
                if (!empty($_FILES['avatar']['tmp_name'])) {
                    $filename = handleUploadedAvatar($_FILES['avatar'], $id);
                    if ($filename) $this->model->updateAvatar($id, $filename);
                }
                $this->log->log('update_user', 'user', $id, "แก้ไขผู้ใช้: {$data['first_name']} {$data['last_name']}");
                setFlash('success', 'แก้ไขข้อมูลสำเร็จ');
                redirect('?page=users');
            }
        }
        $action = 'edit';
        $pdo    = $this->pdo;
        require_once __DIR__ . '/../views/users/form.php';
    }

    public function delete(): void {
        $id = (int)($_GET['id'] ?? 0);
        $user = $this->model->findById($id);
        if ($user) {
            // Check if user has active borrows
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM borrow_records WHERE user_id=? AND status IN ('active','overdue')");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                setFlash('error', 'ไม่สามารถลบได้ เนื่องจากผู้ใช้มีการยืมที่ยังไม่คืน');
            } else {
                $this->model->delete($id);
                $this->log->log('delete_user', 'user', $id, "ลบผู้ใช้: {$user['first_name']} {$user['last_name']}");
                setFlash('success', 'ลบผู้ใช้สำเร็จ');
            }
        }
        redirect('?page=users');
    }
}
