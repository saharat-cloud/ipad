<?php
class BorrowController {
    public function __construct(private PDO $pdo) {}

    public function index(): void {
        $pdo = $this->pdo;
        require_once __DIR__ . '/../views/borrow/index.php';
    }

}
