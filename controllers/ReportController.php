<?php
class ReportController {
    private BorrowRecord $borrowModel;
    private User $userModel;

    public function __construct(private PDO $pdo) {
        $this->borrowModel = new BorrowRecord($pdo);
        $this->userModel   = new User($pdo);
    }

    public function history(): void {
        $filters = [
            'search'    => $_GET['search'] ?? '',
            'status'    => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to'   => $_GET['date_to'] ?? '',
            'class'     => $_GET['class'] ?? '',
        ];
        $records = $this->borrowModel->getAll($filters);
        $classes = $this->userModel->getClasses();
        $pdo     = $this->pdo;
        require_once __DIR__ . '/../views/history/index.php';
    }

    public function index(): void {
        $dailyStats   = $this->borrowModel->getDailyStats(30);
        $monthlyStats = $this->borrowModel->getMonthlyStats(6);
        $classStats   = $this->borrowModel->getClassStats();
        $filters = [
            'date_from' => $_GET['date_from'] ?? date('Y-m-01'),
            'date_to'   => $_GET['date_to']   ?? date('Y-m-d'),
            'status'    => $_GET['status'] ?? '',
            'class'     => $_GET['class'] ?? '',
        ];
        $records = $this->borrowModel->getAll($filters);
        $classes = $this->userModel->getClasses();
        $pdo     = $this->pdo;
        require_once __DIR__ . '/../views/reports/index.php';
    }

    public function approveReturn(): void {
        if (!isset($_SESSION['system_user_id'])) {
            jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $recordIds = $input['record_ids'] ?? [];
        
        if (empty($recordIds) || !is_array($recordIds)) {
            jsonResponse(['success' => false, 'message' => 'กรุณาเลือกรายการที่ต้องการอนุมัติ']);
        }
        
        $ipadModel = new Ipad($this->pdo);
        $logModel = new ActivityLog($this->pdo);
        $adminId = $_SESSION['system_user_id'];
        
        try {
            $this->pdo->beginTransaction();
        
            foreach ($recordIds as $rid) {
                $record = $this->borrowModel->findById((int)$rid);
                if (!$record) continue;
        
                if ($record['status'] !== 'pending_return') {
                    continue; 
                }
        
                $this->borrowModel->doReturn((int)$rid, $adminId, '');
                $ipadModel->updateStatus($record['ipad_id'], 'available');
        
                $logModel->log('approve_return', 'borrow_record', $rid,
                    "อนุมัติการคืน {$record['device_name']} ({$record['device_code']})"
                );
            }
        
            $this->pdo->commit();
        
            jsonResponse([
                'success' => true,
                'message' => 'อนุมัติการคืนสำเร็จ'
            ]);
        } catch (Exception $e) {
            $this->pdo->rollBack();
            jsonResponse(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    }
}
