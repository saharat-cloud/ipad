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
}
