<?php
define('DOING_INSTALL', true);
require_once __DIR__ . '/config/database.php';

$step    = $_GET['step'] ?? 1;
$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host    = trim($_POST['db_host'] ?? 'localhost');
    $db_user    = trim($_POST['db_user'] ?? 'root');
    $db_pass    = $_POST['db_pass'] ?? '';
    $db_name    = trim($_POST['db_name'] ?? 'ipad_system');
    $admin_user = trim($_POST['admin_user'] ?? 'admin');
    $admin_pass = $_POST['admin_pass'] ?? 'admin1234';
    $staff_user = trim($_POST['staff_user'] ?? 'staff1');
    $staff_pass = $_POST['staff_pass'] ?? 'staff1234';

    try {
        // Use existing config logic to get DB connection
        $db_driver = getenv('DB_DRIVER') ?: 'pgsql';
        $db_port = getenv('DB_PORT') ?: '5432';
        
        if ($db_driver === 'pgsql') {
            $dsn = "pgsql:host=$db_host;port=$db_port;dbname=$db_name;options='--client_encoding=utf8mb4'";
            $tempPdo = new PDO($dsn, $db_user, $db_pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        } else {
            $tempPdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        }

        // Create tables (Postgres & MySQL compatible data types)
        $sql = "
        CREATE TABLE IF NOT EXISTS system_users (
          id SERIAL PRIMARY KEY,
          username VARCHAR(50) NOT NULL UNIQUE,
          password VARCHAR(255) NOT NULL,
          role VARCHAR(20) DEFAULT 'staff',
          display_name VARCHAR(100),
          is_active SMALLINT DEFAULT 1,
          last_login TIMESTAMP NULL,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS users (
          id SERIAL PRIMARY KEY,
          user_code VARCHAR(20) NOT NULL UNIQUE,
          barcode_id VARCHAR(50) NOT NULL UNIQUE,
          first_name VARCHAR(100) NOT NULL,
          last_name VARCHAR(100) NOT NULL,
          class_position VARCHAR(100) DEFAULT '',
          role VARCHAR(20) DEFAULT 'student',
          avatar TEXT NULL,
          is_active SMALLINT DEFAULT 1,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS ipads (
          id SERIAL PRIMARY KEY,
          device_code VARCHAR(20) NOT NULL UNIQUE,
          device_name VARCHAR(100) NOT NULL,
          barcode VARCHAR(50) NOT NULL UNIQUE,
          serial_number VARCHAR(100) NOT NULL UNIQUE,
          model VARCHAR(100) NOT NULL,
          status VARCHAR(20) DEFAULT 'available',
          notes TEXT NULL,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS borrow_records (
          id SERIAL PRIMARY KEY,
          user_id INT NOT NULL,
          ipad_id INT NOT NULL,
          borrowed_at TIMESTAMP NOT NULL,
          due_date TIMESTAMP NOT NULL,
          returned_at TIMESTAMP NULL,
          returned_by INT NULL,
          staff_id INT NULL,
          status VARCHAR(20) DEFAULT 'active',
          notes TEXT NULL,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
          FOREIGN KEY (ipad_id) REFERENCES ipads(id) ON DELETE RESTRICT,
          FOREIGN KEY (returned_by) REFERENCES users(id) ON DELETE SET NULL
        );

        CREATE TABLE IF NOT EXISTS activity_logs (
          id SERIAL PRIMARY KEY,
          system_user_id INT NULL,
          action VARCHAR(50) NOT NULL,
          target_type VARCHAR(50) NULL,
          target_id INT NULL,
          description TEXT,
          ip_address VARCHAR(45),
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        ";

        // In postgres we can't easily use IF NOT EXISTS with SERIAL in all versions, 
        // but modern Postgres supports it. Also we need to fix SERIAL for MySQL if they use fallback.
        // For simplicity, we just run it and catch errors.
        if ($db_driver !== 'pgsql') {
             $sql = str_replace('SERIAL PRIMARY KEY', 'INT PRIMARY KEY AUTO_INCREMENT', $sql);
             $sql = str_replace('TIMESTAMP DEFAULT CURRENT_TIMESTAMP', 'DATETIME DEFAULT CURRENT_TIMESTAMP', $sql);
        }

        foreach (explode(';', $sql) as $q) {
            $q = trim($q);
            if ($q) {
                try { $tempPdo->exec($q); } catch(Exception $e) {}
            }
        }

        // Insert system users
        $adminHash = password_hash($admin_pass, PASSWORD_DEFAULT);
        $staffHash = password_hash($staff_pass, PASSWORD_DEFAULT);

        $tempPdo->exec("DELETE FROM system_users");
        $stmt = $tempPdo->prepare("INSERT INTO system_users (username, password, role, display_name) VALUES (?, ?, ?, ?)");
        $stmt->execute([$admin_user, $adminHash, 'admin', 'ผู้ดูแลระบบ']);
        $stmt->execute([$staff_user, $staffHash, 'staff', 'เจ้าหน้าที่']);

        // Insert seed data - teachers
        $teachers = [
            ['T001','TCH-T001','สมศรี','ใจดี','ครูประจำชั้น ม.1/1','teacher'],
            ['T002','TCH-T002','วิชัย','ศรีสวัสดิ์','ครูคณิตศาสตร์','teacher'],
            ['T003','TCH-T003','นภาพร','มีสุข','ครูวิทยาศาสตร์','teacher'],
            ['T004','TCH-T004','ประสิทธิ์','ดีงาม','ครูภาษาอังกฤษ','teacher'],
            ['T005','TCH-T005','จิราพร','สุขใจ','ครูศิลปะ','teacher'],
        ];
        $tempPdo->exec("DELETE FROM users");
        $uStmt = $tempPdo->prepare("INSERT INTO users (user_code,barcode_id,first_name,last_name,class_position,role) VALUES (?,?,?,?,?,?)");
        foreach ($teachers as $t) $uStmt->execute($t);

        // Students
        $students = [
            ['64001','STD-64001','อภิชาติ','สมบูรณ์','ม.4/1','student'],
            ['64002','STD-64002','วรรณภา','ทองดี','ม.4/1','student'],
            ['64003','STD-64003','ธนภัทร','พงษ์ไทย','ม.4/1','student'],
            ['64004','STD-64004','สุดารัตน์','แก้วใส','ม.4/2','student'],
            ['64005','STD-64005','ชนินทร์','รักดี','ม.4/2','student'],
            ['64006','STD-64006','พิมพ์ชนก','สวัสดิ์','ม.4/2','student'],
            ['64007','STD-64007','กฤษณะ','แสงสว่าง','ม.5/1','student'],
            ['64008','STD-64008','อรอุมา','ลมเย็น','ม.5/1','student'],
            ['64009','STD-64009','ณัฐวุฒิ','เจริญ','ม.5/2','student'],
            ['64010','STD-64010','ปริยากร','สุดสวย','ม.5/2','student'],
            ['64011','STD-64011','สุรศักดิ์','มั่นคง','ม.6/1','student'],
            ['64012','STD-64012','นันทิชา','บุญมี','ม.6/1','student'],
            ['64013','STD-64013','วีรยุทธ','ยิ่งใหญ่','ม.6/2','student'],
            ['64014','STD-64014','กัลยาณี','สุขสันต์','ม.6/2','student'],
            ['64015','STD-64015','ประวิทย์','ใจเย็น','ม.4/3','student'],
            ['64016','STD-64016','ศิริพร','ดีมาก','ม.4/3','student'],
            ['64017','STD-64017','ทรงวุฒิ','แก้วงาม','ม.5/3','student'],
            ['64018','STD-64018','พิชญา','รุ่งเรือง','ม.5/3','student'],
            ['64019','STD-64019','ภาณุวัฒน์','สดใส','ม.6/3','student'],
            ['64020','STD-64020','อัจฉรา','ยิ้มสวย','ม.6/3','student'],
        ];
        foreach ($students as $s) $uStmt->execute($s);

        // iPads
        $ipads = [
            ['IPD-001','iPad หมายเลข 1','IPAD-001','DLXCR2XF001','iPad 10th Gen (Wi-Fi 64GB)','available'],
            ['IPD-002','iPad หมายเลข 2','IPAD-002','DLXCR2XF002','iPad 10th Gen (Wi-Fi 64GB)','available'],
            ['IPD-003','iPad หมายเลข 3','IPAD-003','DLXCR2XF003','iPad 10th Gen (Wi-Fi 64GB)','available'],
            ['IPD-004','iPad หมายเลข 4','IPAD-004','DLXCR2XF004','iPad 10th Gen (Wi-Fi 64GB)','available'],
            ['IPD-005','iPad หมายเลข 5','IPAD-005','DLXCR2XF005','iPad Air M1 (Wi-Fi 256GB)','available'],
            ['IPD-006','iPad หมายเลข 6','IPAD-006','DLXCR2XF006','iPad Air M1 (Wi-Fi 256GB)','available'],
            ['IPD-007','iPad หมายเลข 7','IPAD-007','DLXCR2XF007','iPad Air M1 (Wi-Fi 256GB)','available'],
            ['IPD-008','iPad หมายเลข 8','IPAD-008','DLXCR2XF008','iPad Air M1 (Wi-Fi 256GB)','borrowed'],
            ['IPD-009','iPad หมายเลข 9','IPAD-009','DLXCR2XF009','iPad Pro 11" M2','borrowed'],
            ['IPD-010','iPad หมายเลข 10','IPAD-010','DLXCR2XF010','iPad Pro 11" M2','maintenance'],
            ['IPD-011','iPad หมายเลข 11','IPAD-011','DLXCR2XF011','iPad Pro 11" M2','available'],
            ['IPD-012','iPad หมายเลข 12','IPAD-012','DLXCR2XF012','iPad Pro 12.9" M2','available'],
            ['IPD-013','iPad หมายเลข 13','IPAD-013','DLXCR2XF013','iPad Pro 12.9" M2','available'],
            ['IPD-014','iPad หมายเลข 14','IPAD-014','DLXCR2XF014','iPad mini 6 (Wi-Fi 64GB)','available'],
            ['IPD-015','iPad หมายเลข 15','IPAD-015','DLXCR2XF015','iPad mini 6 (Wi-Fi 64GB)','disabled'],
        ];
        $tempPdo->exec("DELETE FROM ipads");
        $iStmt = $tempPdo->prepare("INSERT INTO ipads (device_code,device_name,barcode,serial_number,model,status) VALUES (?,?,?,?,?,?)");
        foreach ($ipads as $i) $iStmt->execute($i);

        // Sample borrow records
        $tempPdo->exec("DELETE FROM borrow_records");
        $now = date('Y-m-d H:i:s');
        $yesterday = date('Y-m-d H:i:s', strtotime('-1 day'));
        $dueToday = date('Y-m-d') . ' 16:00:00';
        $duePast  = date('Y-m-d H:i:s', strtotime('-2 hours'));

        $bStmt = $tempPdo->prepare("INSERT INTO borrow_records (user_id,ipad_id,borrowed_at,due_date,status) VALUES (?,?,?,?,?)");
        $bStmt->execute([1, 8, date('Y-m-d H:i:s', strtotime('-2 hours')), $dueToday, 'active']);
        $bStmt->execute([6, 9, $yesterday, $duePast, 'overdue']);

        // Update overdue
        $tempPdo->exec("UPDATE borrow_records SET status='overdue' WHERE status='active' AND due_date < NOW()");

        // Activity logs
        $tempPdo->exec("DELETE FROM activity_logs");
        $logStmt = $tempPdo->prepare("INSERT INTO activity_logs (system_user_id,action,target_type,target_id,description,ip_address) VALUES (?,?,?,?,?,?)");
        $logStmt->execute([1,'borrow','borrow_record',1,'ยืม iPad-008 โดย สมศรี ใจดี','127.0.0.1']);
        $logStmt->execute([1,'borrow','borrow_record',2,'ยืม iPad-009 โดย พิมพ์ชนก สวัสดิ์','127.0.0.1']);
        $logStmt->execute([1,'install','system',null,'ติดตั้งระบบเสร็จสมบูรณ์','127.0.0.1']);

        $success = 'ติดตั้งระบบสำเร็จ! คุณสามารถลบไฟล์ install.php ออกเพื่อความปลอดภัย';
    } catch (PDOException $e) {
        $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ติดตั้งระบบยืม-คืน iPad</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
  @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap');
  * { font-family: 'Sarabun', sans-serif; }
  body { background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%); min-height: 100vh; }
</style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
<div class="w-full max-w-lg">
  <div class="text-center mb-8">
    <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl shadow-2xl mb-4">
      <i class="fas fa-tablet-alt text-white text-4xl"></i>
    </div>
    <h1 class="text-3xl font-bold text-white">ติดตั้งระบบ</h1>
    <p class="text-indigo-300 mt-1">iPad Borrowing Management System</p>
  </div>

  <div class="bg-white/10 backdrop-blur-xl rounded-3xl p-8 shadow-2xl border border-white/20">
    <?php if ($success): ?>
    <div class="bg-emerald-500/20 border border-emerald-500/50 rounded-2xl p-5 mb-6 text-center">
      <i class="fas fa-check-circle text-emerald-400 text-3xl mb-2"></i>
      <p class="text-emerald-300 font-semibold"><?= $success ?></p>
      <a href="?page=login" class="inline-block mt-4 bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-8 py-3 rounded-xl font-semibold hover:opacity-90 transition">
        <i class="fas fa-sign-in-alt mr-2"></i>เข้าสู่ระบบ
      </a>
    </div>
    <?php elseif ($error): ?>
    <div class="bg-red-500/20 border border-red-500/50 rounded-2xl p-4 mb-6">
      <p class="text-red-300"><i class="fas fa-exclamation-triangle mr-2"></i><?= $error ?></p>
    </div>
    <?php endif; ?>

    <?php if (!$success): ?>
    <form method="POST">
      <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
        <i class="fas fa-database text-indigo-400"></i> ตั้งค่าฐานข้อมูล
      </h2>
      <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
          <label class="text-indigo-200 text-sm font-medium block mb-1">DB Host</label>
          <input name="db_host" value="localhost" class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-2.5 text-white placeholder-white/40 focus:outline-none focus:border-indigo-400" required>
        </div>
        <div>
          <label class="text-indigo-200 text-sm font-medium block mb-1">DB Name</label>
          <input name="db_name" value="ipad_system" class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-2.5 text-white placeholder-white/40 focus:outline-none focus:border-indigo-400" required>
        </div>
        <div>
          <label class="text-indigo-200 text-sm font-medium block mb-1">DB User</label>
          <input name="db_user" value="root" class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-2.5 text-white placeholder-white/40 focus:outline-none focus:border-indigo-400" required>
        </div>
        <div>
          <label class="text-indigo-200 text-sm font-medium block mb-1">DB Password</label>
          <input name="db_pass" type="password" placeholder="(ว่างสำหรับ XAMPP)" class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-2.5 text-white placeholder-white/40 focus:outline-none focus:border-indigo-400">
        </div>
      </div>

      <h2 class="text-xl font-bold text-white mb-4 mt-6 flex items-center gap-2">
        <i class="fas fa-user-shield text-indigo-400"></i> บัญชีผู้ดูแลระบบ
      </h2>
      <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
          <label class="text-indigo-200 text-sm font-medium block mb-1">ชื่อผู้ใช้ (Admin)</label>
          <input name="admin_user" value="admin" class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-indigo-400" required>
        </div>
        <div>
          <label class="text-indigo-200 text-sm font-medium block mb-1">รหัสผ่าน (Admin)</label>
          <input name="admin_pass" type="password" value="admin1234" class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-indigo-400" required>
        </div>
        <div>
          <label class="text-indigo-200 text-sm font-medium block mb-1">ชื่อผู้ใช้ (Staff)</label>
          <input name="staff_user" value="staff1" class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-indigo-400" required>
        </div>
        <div>
          <label class="text-indigo-200 text-sm font-medium block mb-1">รหัสผ่าน (Staff)</label>
          <input name="staff_pass" type="password" value="staff1234" class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-indigo-400" required>
        </div>
      </div>

      <button type="submit" class="w-full mt-4 bg-gradient-to-r from-indigo-500 to-purple-600 text-white py-4 rounded-xl font-bold text-lg hover:opacity-90 transition-all hover:shadow-lg hover:shadow-indigo-500/30">
        <i class="fas fa-rocket mr-2"></i>ติดตั้งระบบ
      </button>

      <p class="text-white/40 text-xs text-center mt-4">
        <i class="fas fa-info-circle mr-1"></i>ระบบจะสร้างฐานข้อมูลและข้อมูลตัวอย่างให้อัตโนมัติ
      </p>
    </form>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
