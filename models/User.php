<?php
class User {
    public function __construct(private PDO $pdo) {}

    public function findByBarcode(string $barcode): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE barcode_id = ? AND is_active = 1");
        $stmt->execute([$barcode]);
        return $stmt->fetch() ?: null;
    }

    public function findByCode(string $code): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE user_code = ? AND is_active = 1");
        $stmt->execute([$code]);
        return $stmt->fetch() ?: null;
    }

    public function findById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getAll(string $search = '', string $role = ''): array {
        $sql = "SELECT * FROM users WHERE 1=1";
        $params = [];
        if ($search) {
            $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR user_code LIKE ? OR barcode_id LIKE ?)";
            $s = "%$search%";
            $params = array_merge($params, [$s, $s, $s, $s]);
        }
        if ($role) {
            $sql .= " AND role = ?";
            $params[] = $role;
        }
        $sql .= " ORDER BY role, user_code ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (user_code, barcode_id, first_name, last_name, class_position, role, avatar)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['user_code'],
            $data['barcode_id'],
            $data['first_name'],
            $data['last_name'],
            $data['class_position'] ?? '',
            $data['role'] ?? 'student',
            $data['avatar'] ?? null,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void {
        $stmt = $this->pdo->prepare("
            UPDATE users SET user_code=?, barcode_id=?, first_name=?, last_name=?,
            class_position=?, role=?, is_active=? WHERE id=?
        ");
        $stmt->execute([
            $data['user_code'],
            $data['barcode_id'],
            $data['first_name'],
            $data['last_name'],
            $data['class_position'] ?? '',
            $data['role'] ?? 'student',
            $data['is_active'] ?? 1,
            $id,
        ]);
    }

    public function updateAvatar(int $id, string $filename): void {
        $stmt = $this->pdo->prepare("UPDATE users SET avatar=? WHERE id=?");
        $stmt->execute([$filename, $id]);
    }

    public function delete(int $id): void {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id=?");
        $stmt->execute([$id]);
    }

    public function getStats(): array {
        $rows = $this->pdo->query("SELECT role, COUNT(*) as cnt FROM users WHERE is_active=1 GROUP BY role")->fetchAll();
        $s = ['student' => 0, 'teacher' => 0, 'staff' => 0, 'total' => 0];
        foreach ($rows as $r) {
            $s[$r['role']] = (int)$r['cnt'];
            $s['total'] += (int)$r['cnt'];
        }
        return $s;
    }

    public function isCodeTaken(string $code, int $excludeId = 0): bool {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE user_code=? AND id<>?");
        $stmt->execute([$code, $excludeId]);
        return (bool)$stmt->fetch();
    }

    public function isBarcodeTaken(string $barcode, int $excludeId = 0): bool {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE barcode_id=? AND id<>?");
        $stmt->execute([$barcode, $excludeId]);
        return (bool)$stmt->fetch();
    }

    public function getClasses(): array {
        $rows = $this->pdo->query("SELECT DISTINCT class_position FROM users WHERE class_position != '' ORDER BY class_position")->fetchAll();
        return array_column($rows, 'class_position');
    }
}
