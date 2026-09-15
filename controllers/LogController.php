<?php
class LogController {
    private ActivityLog $model;

    public function __construct(private PDO $pdo) {
        $this->model = new ActivityLog($pdo);
    }

    public function index(): void {
        $pdo  = $this->pdo;
        $action = $_GET['action'] ?? '';
        $logs   = $this->model->getAll(300, $action);
        require_once __DIR__ . '/../views/logs/index.php';
    }
}
