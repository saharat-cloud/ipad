<?php
function isLoggedIn(): bool {
    return isset($_SESSION['system_user_id']);
}

function getCurrentUser(): ?array {
    return $_SESSION['system_user'] ?? null;
}

function isAdmin(): bool {
    $user = getCurrentUser();
    return $user && $user['role'] === 'admin';
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        redirect('?page=login');
    }
}

function requireRole(string $role): void {
    requireLogin();
    if ($role === 'admin' && !isAdmin()) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้'];
        redirect('?page=dashboard');
    }
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}

function sanitize(string $str): string {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function formatDateTimeTH(?string $datetime): string {
    if (!$datetime || $datetime === '0000-00-00 00:00:00') return '-';
    $dt = new DateTime($datetime);
    $thMonths = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
    $day   = $dt->format('j');
    $month = $thMonths[(int)$dt->format('n')];
    $year  = (int)$dt->format('Y') + 543;
    $time  = $dt->format('H:i');
    return "$day $month $year $time น.";
}

function formatDateTH(?string $datetime): string {
    if (!$datetime || $datetime === '0000-00-00 00:00:00') return '-';
    $dt = new DateTime($datetime);
    $thMonths = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
    $day   = $dt->format('j');
    $month = $thMonths[(int)$dt->format('n')];
    $year  = (int)$dt->format('Y') + 543;
    return "$day $month $year";
}

function timeDiffHuman(string $start, ?string $end = null): string {
    $startDt = new DateTime($start);
    $endDt   = $end ? new DateTime($end) : new DateTime();
    $diff    = $startDt->diff($endDt);

    $parts = [];
    if ($diff->days > 0)  $parts[] = $diff->days . ' วัน';
    if ($diff->h > 0)     $parts[] = $diff->h . ' ชั่วโมง';
    if ($diff->i > 0)     $parts[] = $diff->i . ' นาที';
    if (empty($parts))    $parts[] = 'น้อยกว่า 1 นาที';
    return implode(' ', $parts);
}

function isOverdue(?string $dueDate): bool {
    if (!$dueDate) return false;
    return new DateTime() > new DateTime($dueDate);
}

function logActivity(PDO $pdo, string $action, ?string $targetType, ?int $targetId, string $description): void {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO activity_logs (system_user_id, action, target_type, target_id, description, ip_address)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['system_user_id'] ?? null,
            $action,
            $targetType,
            $targetId,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
        ]);
    } catch (Exception $e) {
        // Silently fail log errors
    }
}

function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function getRoleLabel(string $role): string {
    return match($role) {
        'student' => 'นักเรียน',
        'teacher' => 'ครู',
        'staff'   => 'เจ้าหน้าที่',
        'admin'   => 'ผู้ดูแลระบบ',
        default   => $role,
    };
}

function getStatusLabel(string $status): string {
    return match($status) {
        'available'   => 'พร้อมใช้งาน',
        'borrowed'    => 'กำลังถูกยืม',
        'maintenance' => 'ซ่อมบำรุง',
        'disabled'    => 'ปิดใช้งาน',
        'active'      => 'กำลังยืม',
        'returned'    => 'คืนแล้ว',
        'overdue'     => 'เกินกำหนด',
        default       => $status,
    };
}

function getStatusClass(string $status): string {
    return match($status) {
        'available', 'returned' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
        'borrowed', 'active'    => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        'maintenance'           => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
        'disabled'              => 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400',
        'overdue'               => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
        default                 => 'bg-gray-100 text-gray-800',
    };
}

function generateBarcodeSVG(string $code): string {
    // Simple Code128-like display (visual barcode using CSS)
    $bars = '';
    $hash = md5($code);
    for ($i = 0; $i < 60; $i++) {
        $width = (hexdec($hash[$i % 32]) % 3) + 1;
        $color = ($i % 2 === 0) ? 'black' : 'white';
        $bars .= "<rect x='" . ($i * 2.5) . "' y='0' width='$width' height='60' fill='$color'/>";
    }
    return "<svg xmlns='http://www.w3.org/2000/svg' width='150' height='70' viewBox='0 0 150 70'>
        <rect width='150' height='70' fill='white'/>
        $bars
        <text x='75' y='68' text-anchor='middle' font-size='8' font-family='monospace' fill='black'>$code</text>
    </svg>";
}

function handleUploadedAvatar(array $file, int $userId): ?string {
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed)) return null;
    if ($file['size'] > 2 * 1024 * 1024) return null;

    $fileData = file_get_contents($file['tmp_name']);
    if (!$fileData) return null;
    
    return 'data:' . $file['type'] . ';base64,' . base64_encode($fileData);
}

function getAvatarUrl(?string $avatar): string {
    if (!$avatar) return DEFAULT_AVATAR;
    if (str_starts_with($avatar, 'data:image/')) return $avatar;
    if (str_starts_with($avatar, 'http')) return $avatar;
    return UPLOAD_URL . $avatar;
}

function sendLineNotify(string $message): bool {
    $token = getenv('LINE_NOTIFY_TOKEN');
    if (!$token) return false;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://notify-api.line.me/api/notify");
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "message=" . urlencode($message));
    $headers = [
        'Content-type: application/x-www-form-urlencoded',
        'Authorization: Bearer ' . $token,
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
    
    return $result !== false;
}
