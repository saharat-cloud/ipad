<?php
class ActivityLog {
    public function __construct(private PDO $pdo) {}

    public function log(string $action, ?string $targetType, ?int $targetId, string $description): void {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO activity_logs (system_user_id, action, target_type, target_id, description, ip_address)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_SESSION['system_user_id'] ?? null,
                $action,
                $targetType,
                $targetId,
                $description,
                $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            ]);
        } catch (Exception $e) {}
    }

    public function getAll(int $limit = 200, string $action = ''): array {
        $sql = "
            SELECT al.*, su.username, su.display_name
            FROM activity_logs al
            LEFT JOIN system_users su ON al.system_user_id = su.id
            WHERE 1=1
        ";
        $params = [];
        if ($action) {
            $sql .= " AND al.action = ?";
            $params[] = $action;
        }
        $sql .= " ORDER BY al.created_at DESC LIMIT ?";
        $params[] = $limit;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
