<?php
$currentPage = $_GET['page'] ?? 'borrow';
$currentUser = getCurrentUser();
$kioskNav = [
    ['page' => 'borrow',     'icon' => 'fa-arrow-circle-down','label' => 'ยืม iPad',   'color' => 'blue'],
    ['page' => 'return',     'icon' => 'fa-arrow-circle-up',  'label' => 'คืน iPad',   'color' => 'emerald'],
];
?>
<!DOCTYPE html>
<html lang="th" class="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?? 'Kiosk' ?> - ระบบยืม-คืน iPad</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<link href="<?= APP_URL ?>/assets/css/custom.css" rel="stylesheet">
<script>
  tailwind.config = {
    darkMode: 'class',
    theme: { extend: { fontFamily: { sans: ['Sarabun', 'sans-serif'] } } }
  }
</script>
</head>
<body class="bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 min-h-screen flex flex-col transition-colors duration-200">

<!-- Kiosk Top Navigation -->
<nav class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 shadow-sm sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16">
      
      <!-- Brand & Kiosk Title -->
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-gradient-to-br from-amber-400 to-orange-500 rounded-xl flex items-center justify-center shadow-lg">
          <i class="fas fa-desktop text-white"></i>
        </div>
        <div>
          <h1 class="font-bold text-slate-800 dark:text-white leading-tight">ระบบยืม-คืน (หน้าเคาน์เตอร์)</h1>
          <p class="text-xs text-slate-500 dark:text-slate-400" id="liveClock"></p>
        </div>
      </div>

      <!-- Center Tabs (Desktop) -->
      <div class="hidden md:flex items-center space-x-2">
        <?php foreach ($kioskNav as $nav): 
          $active = ($currentPage === $nav['page']);
          $col = $nav['color'];
        ?>
        <a href="?page=<?= $nav['page'] ?>" 
           class="flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold transition-all
           <?= $active 
               ? "bg-{$col}-500 text-white shadow-lg shadow-{$col}-500/30" 
               : "text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-800 dark:hover:text-white" ?>">
          <i class="fas <?= $nav['icon'] ?>"></i> <?= $nav['label'] ?>
        </a>
        <?php endforeach; ?>
      </div>

      <!-- Right Side (Back to Admin & Dark Mode) -->
      <div class="flex items-center gap-3">
        <button id="darkModeToggle" onclick="toggleDarkMode()"
          class="p-2 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-yellow-400 hover:bg-slate-200 dark:hover:bg-slate-600 transition-all">
          <i class="fas fa-sun dark:hidden"></i>
          <i class="fas fa-moon hidden dark:inline"></i>
        </button>
        <a href="?page=dashboard" class="flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-600 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
          <i class="fas fa-chart-pie"></i> <span class="hidden sm:inline">ระบบหลังบ้าน</span>
        </a>
      </div>

    </div>
  </div>
</nav>

<!-- Mobile Center Tabs (Bottom of nav on mobile) -->
<div class="md:hidden bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex justify-around p-2">
  <?php foreach ($kioskNav as $nav): 
    $active = ($currentPage === $nav['page']);
    $col = $nav['color'];
  ?>
  <a href="?page=<?= $nav['page'] ?>" 
     class="flex-1 text-center py-2 rounded-xl font-bold text-sm transition-all
     <?= $active ? "bg-{$col}-500 text-white" : "text-slate-500 dark:text-slate-400" ?>">
    <i class="fas <?= $nav['icon'] ?> block mb-1"></i> <?= $nav['label'] ?>
  </a>
  <?php endforeach; ?>
</div>

<main class="flex-1 max-w-7xl mx-auto w-full p-4 sm:p-6 lg:p-8 animate-fade-in">
<?php $flash = getFlash(); if ($flash): ?>
<div id="flashMsg" class="mb-4 p-4 rounded-xl flex items-center gap-3 animate-fade-in
  <?= $flash['type'] === 'success' ? 'bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 text-emerald-800 dark:text-emerald-300'
      : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-300' ?>">
  <i class="fas <?= $flash['type'] === 'success' ? 'fa-check-circle text-emerald-500' : 'fa-exclamation-circle text-red-500' ?> text-xl"></i>
  <span class="font-medium"><?= sanitize($flash['message']) ?></span>
  <button onclick="this.parentElement.remove()" class="ml-auto opacity-60 hover:opacity-100"><i class="fas fa-times"></i></button>
</div>
<?php endif; ?>
