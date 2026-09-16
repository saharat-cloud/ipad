<?php
class SystemUser {
    public function __construct(private PDO $pdo) {}

    public function findById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM system_users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByUsername(string $username): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM system_users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }

    public function getAll(string $search = '', string $role = ''): array {
        $sql = "SELECT * FROM system_users WHERE 1=1";
        $params = [];
        
        if ($search) {
            $sql .= " AND (username LIKE ? OR display_name LIKE ?)";
            $s = "%$search%";
            $params = [$s, $s];
        }
        
        if ($role) {
            $sql .= " AND role = ?";
            $params[] = $role;
        }
        
        $sql .= " ORDER BY role ASC, username ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO system_users (username, password, display_name, role, is_active)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['username'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['display_name'] ?? $data['username'],
            $data['role'] ?? 'staff',
            $data['is_active'] ?? 1
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void {
        $sql = "UPDATE system_users SET username=?, display_name=?, role=?, is_active=? ";
        $params = [
            $data['username'], 
            $data['display_name'], 
            $data['role'], 
            $data['is_active']
        ];

        if (!empty($data['password'])) {
            $sql .= ", password=? ";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $sql .= " WHERE id=?";
        $params[] = $id;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    public function delete(int $id): void {
        // Prevent deleting the main admin
        if ($id === 1) {
            throw new Exception("ไม่สามารถลบบัญชีแอดมินหลักได้");
        }
        $stmt = $this->pdo->prepare("DELETE FROM system_users WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function isUsernameTaken(string $username, int $excludeId = 0): bool {
        $sql = "SELECT COUNT(*) FROM system_users WHERE username = ?";
        $params = [$username];
        if ($excludeId > 0) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
}
