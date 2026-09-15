<?php
class Ipad {
    public function __construct(private PDO $pdo) {}

    public function findByBarcode(string $barcode): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM ipads WHERE barcode = ?");
        $stmt->execute([$barcode]);
        return $stmt->fetch() ?: null;
    }

    public function findById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM ipads WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getAll(string $search = '', string $status = ''): array {
        $sql = "SELECT * FROM ipads WHERE 1=1";
        $params = [];
        if ($search) {
            $sql .= " AND (device_code LIKE ? OR device_name LIKE ? OR serial_number LIKE ? OR barcode LIKE ? OR model LIKE ?)";
            $s = "%$search%";
            $params = [$s, $s, $s, $s, $s];
        }
        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY device_code ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO ipads (device_code, device_name, barcode, serial_number, model, status, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['device_code'],
            $data['device_name'],
            $data['barcode'],
            $data['serial_number'],
            $data['model'],
            $data['status'] ?? 'available',
            $data['notes'] ?? null,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void {
        $stmt = $this->pdo->prepare("
            UPDATE ipads SET device_code=?, device_name=?, barcode=?, serial_number=?, model=?, status=?, notes=?
            WHERE id=?
        ");
        $stmt->execute([
            $data['device_code'],
            $data['device_name'],
            $data['barcode'],
            $data['serial_number'],
            $data['model'],
            $data['status'],
            $data['notes'] ?? null,
            $id,
        ]);
    }

    public function updateStatus(int $id, string $status): void {
        $stmt = $this->pdo->prepare("UPDATE ipads SET status=? WHERE id=?");
        $stmt->execute([$status, $id]);
    }

    public function delete(int $id): void {
        $stmt = $this->pdo->prepare("DELETE FROM ipads WHERE id=?");
        $stmt->execute([$id]);
    }

    public function getStats(): array {
        $rows = $this->pdo->query("SELECT status, COUNT(*) as cnt FROM ipads GROUP BY status")->fetchAll();
        $s = ['total' => 0, 'available' => 0, 'borrowed' => 0, 'maintenance' => 0, 'disabled' => 0];
        foreach ($rows as $r) {
            $s[$r['status']] = (int)$r['cnt'];
            $s['total'] += (int)$r['cnt'];
        }
        return $s;
    }

    public function isCodeTaken(string $code, int $excludeId = 0): bool {
        $stmt = $this->pdo->prepare("SELECT id FROM ipads WHERE device_code=? AND id<>?");
        $stmt->execute([$code, $excludeId]);
        return (bool)$stmt->fetch();
    }

    public function isBarcodeTaken(string $barcode, int $excludeId = 0): bool {
        $stmt = $this->pdo->prepare("SELECT id FROM ipads WHERE barcode=? AND id<>?");
        $stmt->execute([$barcode, $excludeId]);
        return (bool)$stmt->fetch();
    }

    public function isSerialTaken(string $serial, int $excludeId = 0): bool {
        $stmt = $this->pdo->prepare("SELECT id FROM ipads WHERE serial_number=? AND id<>?");
        $stmt->execute([$serial, $excludeId]);
        return (bool)$stmt->fetch();
    }
}
