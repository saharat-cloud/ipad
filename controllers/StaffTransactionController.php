<?php
class StaffTransactionController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function index() {
        requireLogin();
        $user = getCurrentUser();
        if (!$user || !in_array($user['role'], ['admin', 'staff'])) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้'];
            redirect('?page=dashboard');
        }
        
        $ipadModel = new Ipad($this->pdo);
        $userModel = new User($this->pdo);

        $availableIpads = $ipadModel->getAll('', 'available');
        
        require_once __DIR__ . '/../views/staff_transaction/index.php';
    }
}
