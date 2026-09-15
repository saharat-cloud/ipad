<?php
class DashboardController {
    private User $userModel;
    private Ipad $ipadModel;
    private BorrowRecord $borrowModel;

    public function __construct(private PDO $pdo) {
        $this->userModel   = new User($pdo);
        $this->ipadModel   = new Ipad($pdo);
        $this->borrowModel = new BorrowRecord($pdo);
    }

    public function index(): void {
        $this->borrowModel->updateOverdue();

        $pdo           = $this->pdo; // expose to views
        $ipadStats     = $this->ipadModel->getStats();
        $userStats     = $this->userModel->getStats();
        $recentRecords = $this->borrowModel->getAll(['limit' => 10]);
        $overdueList   = $this->borrowModel->getOverdueList();
        $todayCount    = $this->borrowModel->getTodayCount();
        $dailyStats    = $this->borrowModel->getDailyStats(14);
        $monthlyStats  = $this->borrowModel->getMonthlyStats(6);
        $classStats    = $this->borrowModel->getClassStats();

        require_once __DIR__ . '/../views/dashboard/index.php';
    }
}
