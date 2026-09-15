<?php
class BorrowRecord {
    public function __construct(private PDO $pdo) {}

    public function getActiveByIpad(int $ipadId): ?array {
        $stmt = $this->pdo->prepare("
            SELECT br.*,
                u.first_name, u.last_name, u.user_code, u.class_position, u.avatar, u.role as user_role,
                ip.device_code, ip.device_name, ip.model, ip.serial_number, ip.barcode as ipad_barcode
            FROM borrow_records br
            JOIN users u  ON br.user_id  = u.id
            JOIN ipads ip ON br.ipad_id  = ip.id
            WHERE br.ipad_id = ? AND br.status IN ('active','overdue')
            ORDER BY br.borrowed_at DESC LIMIT 1
        ");
        $stmt->execute([$ipadId]);
        return $stmt->fetch() ?: null;
    }

    public function findById(int $id): ?array {
        $stmt = $this->pdo->prepare("
            SELECT br.*,
                u.first_name, u.last_name, u.user_code, u.class_position, u.avatar,
                ip.device_code, ip.device_name, ip.model, ip.serial_number,
                ru.first_name as ret_first, ru.last_name as ret_last
            FROM borrow_records br
            JOIN users u  ON br.user_id = u.id
            JOIN ipads ip ON br.ipad_id = ip.id
            LEFT JOIN users ru ON br.returned_by = ru.id
            WHERE br.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO borrow_records (user_id, ipad_id, borrowed_at, due_date, staff_id, status, notes)
            VALUES (?, ?, ?, ?, ?, 'active', ?)
        ");
        $stmt->execute([
            $data['user_id'],
            $data['ipad_id'],
            $data['borrowed_at'] ?? date('Y-m-d H:i:s'),
            $data['due_date'],
            $data['staff_id'] ?? null,
            $data['notes'] ?? null,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function doReturn(int $id, int $returnedBy, string $notes = ''): void {
        $stmt = $this->pdo->prepare("
            UPDATE borrow_records
            SET returned_at = NOW(), returned_by = ?, status = 'returned',
                notes = CASE WHEN ? != '' THEN ? ELSE notes END
            WHERE id = ?
        ");
        $stmt->execute([$returnedBy, $notes, $notes, $id]);
    }

    public function getAll(array $filters = []): array {
        $sql = "
            SELECT br.*,
                u.first_name, u.last_name, u.user_code, u.class_position, u.avatar,
                ip.device_code, ip.device_name, ip.model, ip.serial_number,
                ru.first_name as ret_first, ru.last_name as ret_last
            FROM borrow_records br
            JOIN users u  ON br.user_id = u.id
            JOIN ipads ip ON br.ipad_id = ip.id
            LEFT JOIN users ru ON br.returned_by = ru.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND br.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.user_code LIKE ? OR ip.device_code LIKE ? OR ip.serial_number LIKE ?)";
            $s = '%' . $filters['search'] . '%';
            array_push($params, $s, $s, $s, $s, $s);
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(br.borrowed_at) >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(br.borrowed_at) <= ?";
            $params[] = $filters['date_to'];
        }
        if (!empty($filters['class'])) {
            $sql .= " AND u.class_position LIKE ?";
            $params[] = '%' . $filters['class'] . '%';
        }
        if (!empty($filters['ipad_id'])) {
            $sql .= " AND br.ipad_id = ?";
            $params[] = $filters['ipad_id'];
        }

        $sql .= " ORDER BY br.borrowed_at DESC";
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getDailyStats(int $days = 30): array {
        $stmt = $this->pdo->prepare("
            SELECT DATE(borrowed_at) as date, COUNT(*) as count
            FROM borrow_records
            WHERE borrowed_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(borrowed_at)
            ORDER BY date ASC
        ");
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }

    public function getMonthlyStats(int $months = 6): array {
        $stmt = $this->pdo->prepare("
            SELECT DATE_FORMAT(borrowed_at,'%Y-%m') as month, DATE_FORMAT(borrowed_at,'%b %Y') as label, COUNT(*) as count
            FROM borrow_records
            WHERE borrowed_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
            GROUP BY DATE_FORMAT(borrowed_at,'%Y-%m')
            ORDER BY month ASC
        ");
        $stmt->execute([$months]);
        return $stmt->fetchAll();
    }

    public function getClassStats(): array {
        $stmt = $this->pdo->query("
            SELECT u.class_position, COUNT(*) as count
            FROM borrow_records br
            JOIN users u ON br.user_id = u.id
            WHERE MONTH(br.borrowed_at) = MONTH(NOW()) AND YEAR(br.borrowed_at) = YEAR(NOW())
              AND u.class_position != ''
            GROUP BY u.class_position
            ORDER BY count DESC
            LIMIT 10
        ");
        return $stmt->fetchAll();
    }

    public function updateOverdue(): int {
        return (int)$this->pdo->exec(
            "UPDATE borrow_records SET status='overdue' WHERE status='active' AND due_date < NOW()"
        );
    }

    public function getOverdueList(): array {
        $stmt = $this->pdo->query("
            SELECT br.*,
                u.first_name, u.last_name, u.user_code, u.class_position,
                ip.device_code, ip.device_name
            FROM borrow_records br
            JOIN users u  ON br.user_id = u.id
            JOIN ipads ip ON br.ipad_id = ip.id
            WHERE br.status = 'overdue'
            ORDER BY br.due_date ASC
        ");
        return $stmt->fetchAll();
    }

    public function getTodayCount(): int {
        return (int)$this->pdo->query(
            "SELECT COUNT(*) FROM borrow_records WHERE DATE(borrowed_at) = CURDATE()"
        )->fetchColumn();
    }
}
