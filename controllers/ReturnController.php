<?php
class ReturnController {
    public function __construct(private PDO $pdo) {}

    public function index(): void {
        $pdo = $this->pdo;
        require_once __DIR__ . '/../views/return/index.php';
    }
}
