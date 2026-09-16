<?php
require_once __DIR__ . '/../models/SystemUser.php';

class UserController {
    private SystemUser $model;
    private ActivityLog $log;

    public function __construct(private PDO $pdo) {
        $this->model = new SystemUser($pdo);
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
                'username'      => trim($_POST['username'] ?? ''),
                'password'      => $_POST['password'] ?? '',
                'display_name'  => trim($_POST['display_name'] ?? ''),
                'role'          => $_POST['role'] ?? 'staff',
                'is_active'     => isset($_POST['is_active']) ? 1 : 0
            ];
            
            // Validate
            if (!$data['username'])   $errors[] = 'กรุณาระบุ Username';
            if (!$data['password'])   $errors[] = 'กรุณาระบุ Password';
            if (!$data['display_name'])  $errors[] = 'กรุณาระบุชื่อแสดงผล';
            if ($this->model->isUsernameTaken($data['username'])) $errors[] = 'Username นี้มีอยู่แล้ว';

            if (empty($errors)) {
                $id = $this->model->create($data);
                $this->log->log('create_system_user', 'system_user', $id, "เพิ่มเจ้าหน้าที่: {$data['display_name']} ({$data['username']})");
                setFlash('success', 'เพิ่มเจ้าหน้าที่สำเร็จ');
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
        if (!$user) {
            setFlash('error', 'ไม่พบเจ้าหน้าที่');
            redirect('?page=users');
        }

        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'username'      => trim($_POST['username'] ?? ''),
                'password'      => $_POST['password'] ?? '',
                'display_name'  => trim($_POST['display_name'] ?? ''),
                'role'          => $_POST['role'] ?? 'staff',
                'is_active'     => isset($_POST['is_active']) ? 1 : 0
            ];

            if (!$data['username'])   $errors[] = 'กรุณาระบุ Username';
            if (!$data['display_name'])  $errors[] = 'กรุณาระบุชื่อแสดงผล';
            if ($this->model->isUsernameTaken($data['username'], $id)) {
                $errors[] = 'Username นี้มีอยู่แล้ว';
            }
            if ($id === 1 && $data['role'] !== 'admin') {
                $errors[] = 'ไม่สามารถลดสิทธิ์แอดมินหลักได้';
            }
            if ($id === 1 && $data['is_active'] == 0) {
                $errors[] = 'ไม่สามารถระงับการใช้งานแอดมินหลักได้';
            }

            if (empty($errors)) {
                $this->model->update($id, $data);
                $this->log->log('update_system_user', 'system_user', $id, "แก้ไขข้อมูลเจ้าหน้าที่: {$data['display_name']}");
                setFlash('success', 'บันทึกการแก้ไขสำเร็จ');
                redirect('?page=users');
            }
        }
        $action = 'edit';
        $pdo    = $this->pdo;
        require_once __DIR__ . '/../views/users/form.php';
    }

    public function delete(): void {
        $id = (int)($_GET['id'] ?? 0);
        
        try {
            $user = $this->model->findById($id);
            if ($user) {
                $this->model->delete($id);
                $this->log->log('delete_system_user', 'system_user', $id, "ลบเจ้าหน้าที่: {$user['display_name']}");
                setFlash('success', 'ลบเจ้าหน้าที่สำเร็จ');
            }
        } catch (Exception $e) {
            setFlash('error', $e->getMessage());
        }
        redirect('?page=users');
    }
}
