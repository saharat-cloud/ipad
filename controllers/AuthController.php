<?php
class AuthController {
    public function __construct(private ?PDO $pdo) {}

    public function handle(): void {
        if ($_GET['page'] === 'logout') {
            $this->logout();
            return;
        }
        $this->login();
    }

    private function login(): void {
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($this->pdo) {
                $stmt = $this->pdo->prepare("SELECT * FROM system_users WHERE username=? AND is_active=1");
                $stmt->execute([$username]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['system_user_id'] = $user['id'];
                    $_SESSION['system_user']    = $user;
                    // Update last login
                    $this->pdo->prepare("UPDATE system_users SET last_login=NOW() WHERE id=?")->execute([$user['id']]);
                    logActivity($this->pdo, 'login', 'system_user', $user['id'], "เข้าสู่ระบบ: {$user['username']}");
                    redirect('?page=dashboard');
                } else {
                    $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
                }
            } else {
                $error = 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้ กรุณาติดตั้งระบบก่อน';
            }
        }
        require_once __DIR__ . '/../views/auth/login.php';
    }

    private function logout(): void {
        if ($this->pdo && isLoggedIn()) {
            logActivity($this->pdo, 'logout', 'system_user', $_SESSION['system_user_id'] ?? null, 'ออกจากระบบ');
        }
        session_destroy();
        redirect('?page=login');
    }
}
