<?php
$currentPage = $_GET['page'] ?? 'dashboard';
$currentUser = getCurrentUser();

$navItems = [
    ['page'=>'dashboard',         'icon'=>'fa-chart-pie',       'label'=>'ภาพรวม',         'roles'=>['admin','staff']],
    ['page'=>'staff_transaction', 'icon'=>'fa-exchange-alt',    'label'=>'ทำรายการยืม-คืน', 'roles'=>['admin','staff']],
    ['page'=>'history',           'icon'=>'fa-history',          'label'=>'ประวัติการยืม',    'roles'=>['admin','staff']],
    ['page'=>'ipads',             'icon'=>'fa-tablet-alt',       'label'=>'จัดการ iPad',     'roles'=>['admin']],
    ['page'=>'users',             'icon'=>'fa-users-cog',        'label'=>'จัดการเจ้าหน้าที่',    'roles'=>['admin']],
    ['page'=>'reports',           'icon'=>'fa-file-chart-column','label'=>'รายงาน',          'roles'=>['admin','staff']],
    ['page'=>'logs',              'icon'=>'fa-scroll',           'label'=>'บันทึกกิจกรรม',   'roles'=>['admin']],
];
?>

<!-- Sidebar Overlay (mobile) -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black/60 z-30 hidden lg:hidden" onclick="toggleSidebar()"></div>

<!-- Sidebar -->
<aside id="sidebar" class="fixed top-0 left-0 h-full w-64 z-40 flex flex-col transition-transform duration-300 -translate-x-full lg:translate-x-0
  bg-gradient-to-b from-slate-900 via-indigo-950 to-slate-900 border-r border-white/5 shadow-2xl">

  <!-- Logo -->
  <div class="flex items-center gap-3 px-6 py-5 border-b border-white/10">
    <div class="w-10 h-10 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-xl flex items-center justify-center shadow-lg flex-shrink-0">
      <i class="fas fa-tablet-alt text-white text-lg"></i>
    </div>
    <div>
      <h1 class="text-white font-bold text-sm leading-tight">ระบบยืม-คืน iPad</h1>
      <p class="text-indigo-300 text-xs">School iPad Manager</p>
    </div>
  </div>

  <!-- Nav -->
  <nav class="flex-1 px-3 py-4 overflow-y-auto space-y-1 scrollbar-thin">
    <?php foreach ($navItems as $item):
      if (!in_array($currentUser['role'], $item['roles'])) continue;
      $active = ($currentPage === $item['page']);
    ?>
    <a href="?page=<?= $item['page'] ?>"
       class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 group
              <?= $active
                ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg shadow-indigo-500/20'
                : 'text-slate-300 hover:bg-white/10 hover:text-white' ?>">
      <i class="fas <?= $item['icon'] ?> w-5 text-center
         <?= $active ? 'text-white' : 'text-slate-400 group-hover:text-indigo-400' ?>
         transition-colors"></i>
      <span><?= $item['label'] ?></span>
      <?php if ($active): ?>
      <span class="ml-auto w-1.5 h-1.5 bg-white rounded-full"></span>
      <?php endif; ?>
    </a>
    <?php endforeach; ?>
  </nav>

  <!-- User Info + Logout -->
  <div class="p-4 border-t border-white/10">
    <div class="flex items-center gap-3 mb-3 px-2">
      <div class="w-9 h-9 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-full flex items-center justify-center flex-shrink-0">
        <span class="text-white text-sm font-bold"><?= mb_substr($currentUser['display_name'] ?? $currentUser['username'], 0, 1) ?></span>
      </div>
      <div class="overflow-hidden">
        <p class="text-white text-sm font-semibold truncate"><?= sanitize($currentUser['display_name'] ?? $currentUser['username']) ?></p>
        <span class="text-xs px-2 py-0.5 rounded-full <?= $currentUser['role'] === 'admin' ? 'bg-indigo-500/30 text-indigo-300' : 'bg-emerald-500/30 text-emerald-300' ?>">
          <?= $currentUser['role'] === 'admin' ? 'ผู้ดูแลระบบ' : 'เจ้าหน้าที่' ?>
        </span>
      </div>
    </div>
    <a href="?page=logout" class="flex items-center gap-2 w-full px-4 py-2.5 rounded-xl text-sm text-slate-300 hover:bg-red-500/20 hover:text-red-400 transition-all">
      <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
    </a>
  </div>
</aside>

<!-- Top Bar -->
<div class="lg:ml-64">
<div class="sticky top-0 z-20 flex items-center justify-between px-4 md:px-6 py-3
            bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border-b border-slate-200 dark:border-slate-700/50 shadow-sm">
  <!-- Mobile menu button -->
  <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
    <i class="fas fa-bars text-xl"></i>
  </button>

  <!-- Page Title -->
  <div class="flex items-center gap-3">
    <h2 class="text-lg font-bold text-slate-800 dark:text-white hidden sm:block"><?= $pageTitle ?? '' ?></h2>
  </div>

  <!-- Right actions -->
  <div class="flex items-center gap-2 md:gap-3">
    <!-- Date/Time -->
    <div class="hidden md:flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
      <i class="fas fa-clock text-indigo-400"></i>
      <span id="liveClock"></span>
    </div>

    <!-- Kiosk Mode -->
    <a href="?page=borrow" class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 shadow-md shadow-orange-500/20 transition-all">
      <i class="fas fa-desktop"></i> โหมดหน้าเคาน์เตอร์
    </a>

    <!-- Dark Mode Toggle -->
    <button id="darkModeToggle" onclick="toggleDarkMode()"
      class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-yellow-400 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">
      <i class="fas fa-sun dark:hidden"></i>
      <i class="fas fa-moon hidden dark:inline"></i>
    </button>

    <!-- Notifications -->
    <?php
    if (!isset($pdo) || !$pdo instanceof PDO) {
        global $pdo;
    }
    $overdueCountNav = 0;
    $pendingReturnCountNav = 0;
    if (isset($pdo) && $pdo instanceof PDO) {
        try {
            $overdueCountNav = (int)$pdo->query("SELECT COUNT(*) FROM borrow_records WHERE status='overdue'")->fetchColumn();
            $pendingReturnCountNav = (int)$pdo->query("SELECT COUNT(*) FROM borrow_records WHERE status='pending_return'")->fetchColumn();
        } catch (Exception $e) {}
    }
    ?>
    
    <!-- Pending Return Notification -->
    <?php if ($pendingReturnCountNav > 0): ?>
    <a href="?page=history&status=pending_return" title="รออนุมัติคืน" class="relative p-2 rounded-xl bg-orange-50 dark:bg-orange-500/10 text-orange-500 hover:bg-orange-100 dark:hover:bg-orange-500/20 transition-all">
      <i class="fas fa-clipboard-check"></i>
      <span class="absolute -top-1 -right-1 w-5 h-5 bg-orange-500 text-white text-xs rounded-full flex items-center justify-center font-bold animate-pulse"><?= $pendingReturnCountNav ?></span>
    </a>
    <?php endif; ?>

    <!-- Overdue Notification -->
    <?php if ($overdueCountNav > 0): ?>
    <a href="?page=history&status=overdue" title="เกินกำหนดคืน" class="relative p-2 rounded-xl bg-red-50 dark:bg-red-500/10 text-red-500 hover:bg-red-100 dark:hover:bg-red-500/20 transition-all">
      <i class="fas fa-bell"></i>
      <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-bold"><?= $overdueCountNav ?></span>
    </a>
    <?php endif; ?>
  </div>
</div>

<!-- Main Content -->
<main class="p-4 md:p-6 animate-fade-in">
<?php $flash = getFlash(); if ($flash): ?>
<div id="flashMsg" class="mb-4 p-4 rounded-xl flex items-center gap-3 animate-fade-in
  <?= $flash['type'] === 'success' ? 'bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 text-emerald-800 dark:text-emerald-300'
      : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-300' ?>">
  <i class="fas <?= $flash['type'] === 'success' ? 'fa-check-circle text-emerald-500' : 'fa-exclamation-circle text-red-500' ?> text-xl"></i>
  <span class="font-medium"><?= sanitize($flash['message']) ?></span>
  <button onclick="this.parentElement.remove()" class="ml-auto opacity-60 hover:opacity-100"><i class="fas fa-times"></i></button>
</div>
<?php endif; ?>
