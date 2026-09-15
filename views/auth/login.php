<!DOCTYPE html>
<html lang="th" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>เข้าสู่ระบบ | iPad Borrow System</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: { extend: { fontFamily: { sans: ['Sarabun','sans-serif'] } } }
    }
  </script>
  <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/custom.css">
  <style>
    .login-bg {
      background: radial-gradient(ellipse at top left, #312e81 0%, #0f172a 40%, #1e1b4b 100%);
    }
    .glass-card {
      background: rgba(255,255,255,0.05);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255,255,255,0.1);
    }
    .floating-shapes div {
      position: absolute;
      border-radius: 50%;
      opacity: 0.05;
      animation: float 8s ease-in-out infinite;
    }
    @keyframes float {
      0%,100% { transform: translateY(0) rotate(0deg); }
      50% { transform: translateY(-30px) rotate(180deg); }
    }
  </style>
</head>
<body class="login-bg min-h-screen flex items-center justify-center p-4 overflow-hidden">

<!-- Floating shapes -->
<div class="floating-shapes fixed inset-0 pointer-events-none">
  <div style="width:300px;height:300px;background:#6366f1;top:-100px;left:-100px;animation-delay:0s"></div>
  <div style="width:200px;height:200px;background:#8b5cf6;bottom:100px;right:-50px;animation-delay:3s"></div>
  <div style="width:150px;height:150px;background:#06b6d4;top:50%;left:50%;animation-delay:1.5s"></div>
</div>

<div class="w-full max-w-md relative z-10">
  <!-- Logo -->
  <div class="text-center mb-8">
    <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-indigo-400 to-purple-600 rounded-2xl shadow-2xl mb-4
                hover:scale-105 transition-transform duration-300">
      <i class="fas fa-tablet-alt text-white text-4xl"></i>
    </div>
    <h1 class="text-3xl font-extrabold text-white mb-1">iPad Borrow System</h1>
    <p class="text-indigo-300 text-sm">ระบบบริหารจัดการยืม-คืน iPad</p>
  </div>

  <!-- Login Card -->
  <div class="glass-card rounded-3xl p-8 shadow-2xl">
    <?php if (!empty($error)): ?>
    <div class="mb-5 flex items-center gap-3 p-4 bg-red-500/10 border border-red-500/30 rounded-xl text-red-300">
      <i class="fas fa-exclamation-triangle"></i>
      <span class="text-sm"><?= sanitize($error) ?></span>
    </div>
    <?php endif; ?>

    <form method="POST" id="loginForm">
      <div class="space-y-5">
        <div>
          <label class="text-indigo-200 text-sm font-semibold block mb-2">
            <i class="fas fa-user mr-1"></i>ชื่อผู้ใช้
          </label>
          <div class="relative">
            <input type="text" name="username" id="username" autocomplete="username"
              value="<?= sanitize($_POST['username'] ?? '') ?>"
              placeholder="กรอกชื่อผู้ใช้"
              class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 pl-11 text-white placeholder-white/30
                     focus:outline-none focus:border-indigo-400 focus:bg-white/10 transition-all"
              required>
            <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-indigo-400"></i>
          </div>
        </div>

        <div>
          <label class="text-indigo-200 text-sm font-semibold block mb-2">
            <i class="fas fa-lock mr-1"></i>รหัสผ่าน
          </label>
          <div class="relative">
            <input type="password" name="password" id="password" autocomplete="current-password"
              placeholder="กรอกรหัสผ่าน"
              class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 pl-11 pr-12 text-white placeholder-white/30
                     focus:outline-none focus:border-indigo-400 focus:bg-white/10 transition-all"
              required>
            <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-indigo-400"></i>
            <button type="button" onclick="togglePassword()"
              class="absolute right-4 top-1/2 -translate-y-1/2 text-white/40 hover:text-white/80 transition-colors">
              <i id="eyeIcon" class="fas fa-eye"></i>
            </button>
          </div>
        </div>

        <button type="submit" id="loginBtn"
          class="w-full bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700
                 text-white py-3.5 rounded-xl font-bold text-base transition-all hover:shadow-xl hover:shadow-indigo-500/30
                 hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2">
          <i class="fas fa-sign-in-alt"></i>
          <span>เข้าสู่ระบบ</span>
        </button>
      </div>
    </form>

    <!-- Install link -->
    <?php if (!isset($pdo) || !$pdo): ?>
    <div class="mt-5 text-center">
      <a href="?page=install" class="text-indigo-400 hover:text-indigo-300 text-sm underline">
        <i class="fas fa-cog mr-1"></i>ติดตั้งระบบ
      </a>
    </div>
    <?php endif; ?>
  </div>

  <p class="text-center text-white/20 text-xs mt-6">
    &copy; <?= date('Y') ?> iPad Borrow System — School Edition
  </p>
</div>

<script>
function togglePassword() {
  const pwd = document.getElementById('password');
  const icon = document.getElementById('eyeIcon');
  if (pwd.type === 'password') {
    pwd.type = 'text';
    icon.className = 'fas fa-eye-slash';
  } else {
    pwd.type = 'password';
    icon.className = 'fas fa-eye';
  }
}
document.getElementById('loginForm').addEventListener('submit', function() {
  const btn = document.getElementById('loginBtn');
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>กำลังเข้าสู่ระบบ...</span>';
  btn.disabled = true;
});
document.getElementById('username').focus();
</script>
</body>
</html>
