<?php
class StaffTransactionController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function index() {
        requireRole(['admin', 'staff']);
        
        $ipadModel = new Ipad($this->pdo);
        $userModel = new User($this->pdo);

        $availableIpads = $ipadModel->getAll(['status' => 'available']);
        
        require_once __DIR__ . '/../views/staff_transaction/index.php';
    }
}
