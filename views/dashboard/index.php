<?php
$pageTitle = 'ภาพรวม Dashboard';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';

$thMonths = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
// $dailyStats, $monthlyStats, $classStats already provided by DashboardController

$dLabels = $dData = [];
foreach ($dailyStats as $d) {
    $dt = new DateTime($d['date']);
    $dLabels[] = $dt->format('j') . ' ' . $thMonths[(int)$dt->format('n')];
    $dData[]   = (int)$d['count'];
}
$mLabels = $mData = [];
foreach ($monthlyStats as $m) {
    $dt = DateTime::createFromFormat('Y-m', $m['month']);
    $mLabels[] = $thMonths[(int)$dt->format('n')] . ' ' . ((int)$dt->format('Y') + 543);
    $mData[]   = (int)$m['count'];
}
$cLabels = array_column($classStats, 'class_position');
$cData   = array_map('intval', array_column($classStats, 'count'));
?>

<!-- Stat Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <?php
  $cards = [
    ['label'=>'iPad ทั้งหมด',       'value'=>$ipadStats['total'],       'icon'=>'fa-tablet-alt',   'grad'=>'from-indigo-500 to-purple-600', 'bg'=>'from-indigo-50 to-purple-50 dark:from-indigo-950/40 dark:to-purple-950/40'],
    ['label'=>'กำลังถูกยืม',         'value'=>$ipadStats['borrowed'],    'icon'=>'fa-hand-holding', 'grad'=>'from-blue-500 to-cyan-600',    'bg'=>'from-blue-50 to-cyan-50 dark:from-blue-950/40 dark:to-cyan-950/40'],
    ['label'=>'พร้อมใช้งาน',         'value'=>$ipadStats['available'],   'icon'=>'fa-check-circle', 'grad'=>'from-emerald-500 to-teal-600', 'bg'=>'from-emerald-50 to-teal-50 dark:from-emerald-950/40 dark:to-teal-950/40'],
    ['label'=>'ซ่อมบำรุง',           'value'=>$ipadStats['maintenance'], 'icon'=>'fa-tools',        'grad'=>'from-amber-500 to-orange-600', 'bg'=>'from-amber-50 to-orange-50 dark:from-amber-950/40 dark:to-orange-950/40'],
  ];
  foreach ($cards as $card): ?>
  <div class="rounded-2xl p-5 bg-gradient-to-br <?= $card['bg'] ?> border border-white dark:border-white/5 shadow-sm hover:shadow-md transition-all hover:-translate-y-0.5">
    <div class="flex items-center justify-between mb-3">
      <div class="w-10 h-10 bg-gradient-to-br <?= $card['grad'] ?> rounded-xl flex items-center justify-center shadow-md">
        <i class="fas <?= $card['icon'] ?> text-white"></i>
      </div>
    </div>
    <p class="text-3xl font-extrabold text-slate-800 dark:text-white stat-counter" data-target="<?= $card['value'] ?>">0</p>
    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 font-medium"><?= $card['label'] ?></p>
  </div>
  <?php endforeach; ?>
</div>

<!-- Second row: Today + Overdue + Users -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
  <div class="rounded-2xl p-5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center gap-4">
    <div class="w-12 h-12 bg-gradient-to-br from-violet-500 to-purple-600 rounded-xl flex items-center justify-center shadow-md flex-shrink-0">
      <i class="fas fa-calendar-day text-white text-xl"></i>
    </div>
    <div>
      <p class="text-2xl font-extrabold text-slate-800 dark:text-white"><?= $todayCount ?></p>
      <p class="text-sm text-slate-500 dark:text-slate-400">ยืมวันนี้</p>
    </div>
  </div>
  <div class="rounded-2xl p-5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center gap-4">
    <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-rose-600 rounded-xl flex items-center justify-center shadow-md flex-shrink-0">
      <i class="fas fa-exclamation-triangle text-white text-xl"></i>
    </div>
    <div>
      <p class="text-2xl font-extrabold text-red-600 dark:text-red-400"><?= count($overdueList) ?></p>
      <p class="text-sm text-slate-500 dark:text-slate-400">เกินกำหนดคืน</p>
    </div>
  </div>
  <div class="rounded-2xl p-5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center gap-4">
    <div class="w-12 h-12 bg-gradient-to-br from-pink-500 to-rose-600 rounded-xl flex items-center justify-center shadow-md flex-shrink-0">
      <i class="fas fa-users text-white text-xl"></i>
    </div>
    <div>
      <p class="text-2xl font-extrabold text-slate-800 dark:text-white"><?= $userStats['total'] ?></p>
      <p class="text-sm text-slate-500 dark:text-slate-400">ผู้ใช้ทั้งหมด</p>
    </div>
  </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
  <!-- Daily Chart -->
  <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm">
    <div class="flex items-center justify-between mb-5">
      <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2">
        <i class="fas fa-chart-bar text-indigo-500"></i> สถิติการยืม 14 วันล่าสุด
      </h3>
      <span class="text-xs text-slate-400">อัปเดตอัตโนมัติ</span>
    </div>
    <canvas id="dailyChart" height="120"></canvas>
  </div>

  <!-- Donut Chart -->
  <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm">
    <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2 mb-5">
      <i class="fas fa-chart-pie text-purple-500"></i> สถานะ iPad
    </h3>
    <canvas id="statusChart" height="180"></canvas>
    <div class="mt-4 space-y-2">
      <?php
      $statusItems = [
        ['label'=>'พร้อมใช้งาน','count'=>$ipadStats['available'],'color'=>'bg-emerald-500'],
        ['label'=>'กำลังถูกยืม','count'=>$ipadStats['borrowed'],'color'=>'bg-blue-500'],
        ['label'=>'ซ่อมบำรุง','count'=>$ipadStats['maintenance'],'color'=>'bg-amber-500'],
        ['label'=>'ปิดใช้งาน','count'=>$ipadStats['disabled'],'color'=>'bg-gray-400'],
      ];
      foreach ($statusItems as $si): if ($si['count'] === 0) continue; ?>
      <div class="flex items-center justify-between text-sm">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full <?= $si['color'] ?>"></span>
          <span class="text-slate-600 dark:text-slate-400"><?= $si['label'] ?></span>
        </div>
        <span class="font-bold text-slate-800 dark:text-white"><?= $si['count'] ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Monthly + Class Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
  <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm">
    <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2 mb-5">
      <i class="fas fa-chart-line text-cyan-500"></i> สถิติรายเดือน
    </h3>
    <canvas id="monthlyChart" height="140"></canvas>
  </div>
  <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm">
    <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2 mb-5">
      <i class="fas fa-graduation-cap text-pink-500"></i> ยืมมากสุดรายชั้น (เดือนนี้)
    </h3>
    <canvas id="classChart" height="140"></canvas>
  </div>
</div>

<!-- Overdue Alert -->
<?php if (!empty($overdueList)): ?>
<div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl p-5 mb-6">
  <h3 class="font-bold text-red-800 dark:text-red-300 flex items-center gap-2 mb-4">
    <i class="fas fa-exclamation-circle animate-pulse"></i> iPad เกินกำหนดคืน (<?= count($overdueList) ?> เครื่อง)
  </h3>
  <div class="space-y-2">
    <?php foreach (array_slice($overdueList, 0, 5) as $o): ?>
    <div class="flex items-center justify-between bg-white dark:bg-red-900/30 rounded-xl p-3 text-sm">
      <div>
        <span class="font-semibold text-slate-800 dark:text-white"><?= sanitize($o['first_name'].' '.$o['last_name']) ?></span>
        <span class="text-slate-500 dark:text-slate-400 mx-1">/</span>
        <span class="text-red-600 dark:text-red-400"><?= sanitize($o['device_name']) ?></span>
      </div>
      <span class="text-red-600 dark:text-red-400 font-medium"><?= timeDiffHuman($o['due_date']) ?> เกิน</span>
    </div>
    <?php endforeach; ?>
  </div>
  <?php if (count($overdueList) > 5): ?>
  <a href="?page=history&status=overdue" class="text-sm text-red-600 dark:text-red-400 hover:underline mt-2 block">ดูทั้งหมด...</a>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- Recent Records Table -->
<div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm">
  <div class="flex items-center justify-between mb-5">
    <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2">
      <i class="fas fa-clock-rotate-left text-indigo-500"></i> รายการล่าสุด
    </h3>
    <a href="?page=history" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">ดูทั้งหมด</a>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="border-b border-slate-200 dark:border-slate-700">
          <th class="pb-3 text-left font-semibold text-slate-500 dark:text-slate-400">ผู้ยืม</th>
          <th class="pb-3 text-left font-semibold text-slate-500 dark:text-slate-400">iPad</th>
          <th class="pb-3 text-left font-semibold text-slate-500 dark:text-slate-400 hidden md:table-cell">เวลายืม</th>
          <th class="pb-3 text-left font-semibold text-slate-500 dark:text-slate-400 hidden lg:table-cell">กำหนดคืน</th>
          <th class="pb-3 text-left font-semibold text-slate-500 dark:text-slate-400">สถานะ</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
        <?php foreach ($recentRecords as $r): ?>
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
          <td class="py-3">
            <div class="flex items-center gap-2">
              <div>
                <p class="font-semibold text-slate-800 dark:text-white"><?= sanitize($r['first_name'].' '.$r['last_name']) ?></p>
                <p class="text-xs text-slate-400"><?= sanitize($r['class_position']) ?></p>
              </div>
            </div>
          </td>
          <td class="py-3">
            <p class="font-medium text-slate-800 dark:text-white"><?= sanitize($r['device_code']) ?></p>
            <p class="text-xs text-slate-400"><?= sanitize($r['model']) ?></p>
          </td>
          <td class="py-3 hidden md:table-cell text-slate-600 dark:text-slate-300"><?= formatDateTimeTH($r['borrowed_at']) ?></td>
          <td class="py-3 hidden lg:table-cell <?= isOverdue($r['due_date']) && $r['status'] !== 'returned' ? 'text-red-500 font-medium' : 'text-slate-600 dark:text-slate-300' ?>">
            <?= formatDateTimeTH($r['due_date']) ?>
          </td>
          <td class="py-3">
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= getStatusClass($r['status']) ?>">
              <?= getStatusLabel($r['status']) ?>
            </span>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($recentRecords)): ?>
        <tr><td colspan="5" class="text-center py-8 text-slate-400">ยังไม่มีรายการ</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
// Stat counters
document.querySelectorAll('.stat-counter').forEach(el => {
  const target = parseInt(el.dataset.target);
  let cur = 0;
  const inc = Math.max(1, Math.ceil(target / 30));
  const timer = setInterval(() => {
    cur = Math.min(cur + inc, target);
    el.textContent = cur;
    if (cur >= target) clearInterval(timer);
  }, 40);
});

// Charts
const isDark = document.documentElement.classList.contains('dark');
const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
const textColor = isDark ? '#94a3b8' : '#64748b';

// Daily Chart
new Chart(document.getElementById('dailyChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($dLabels, JSON_UNESCAPED_UNICODE) ?>,
    datasets: [{
      label: 'จำนวนการยืม',
      data: <?= json_encode($dData) ?>,
      backgroundColor: 'rgba(99,102,241,0.7)',
      borderColor: '#6366f1',
      borderWidth: 2,
      borderRadius: 8,
      borderSkipped: false,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      y: { beginAtZero: true, ticks: { color: textColor, stepSize: 1 }, grid: { color: gridColor } },
      x: { ticks: { color: textColor }, grid: { display: false } }
    }
  }
});

// Donut Chart
new Chart(document.getElementById('statusChart'), {
  type: 'doughnut',
  data: {
    labels: ['พร้อมใช้งาน','กำลังถูกยืม','ซ่อมบำรุง','ปิดใช้งาน'],
    datasets: [{
      data: [<?= $ipadStats['available'] ?>,<?= $ipadStats['borrowed'] ?>,<?= $ipadStats['maintenance'] ?>,<?= $ipadStats['disabled'] ?>],
      backgroundColor: ['#10b981','#3b82f6','#f59e0b','#6b7280'],
      borderWidth: 0,
      hoverOffset: 8,
    }]
  },
  options: {
    responsive: true,
    cutout: '70%',
    plugins: { legend: { display: false } }
  }
});

// Monthly Chart
new Chart(document.getElementById('monthlyChart'), {
  type: 'line',
  data: {
    labels: <?= json_encode($mLabels, JSON_UNESCAPED_UNICODE) ?>,
    datasets: [{
      label: 'การยืม',
      data: <?= json_encode($mData) ?>,
      borderColor: '#06b6d4',
      backgroundColor: 'rgba(6,182,212,0.1)',
      tension: 0.4,
      fill: true,
      pointBackgroundColor: '#06b6d4',
      pointRadius: 5,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      y: { beginAtZero: true, ticks: { color: textColor }, grid: { color: gridColor } },
      x: { ticks: { color: textColor }, grid: { display: false } }
    }
  }
});

// Class Chart
new Chart(document.getElementById('classChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($cLabels, JSON_UNESCAPED_UNICODE) ?>,
    datasets: [{
      data: <?= json_encode($cData) ?>,
      backgroundColor: 'rgba(236,72,153,0.7)',
      borderColor: '#ec4899',
      borderWidth: 2,
      borderRadius: 6,
    }]
  },
  options: {
    indexAxis: 'y',
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { beginAtZero: true, ticks: { color: textColor, stepSize: 1 }, grid: { color: gridColor } },
      y: { ticks: { color: textColor }, grid: { display: false } }
    }
  }
});

// Auto-refresh stats every 30s
setInterval(() => {
  fetch('api/dashboard_stats.php')
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        // Could update counters here
      }
    }).catch(() => {});
}, 30000);
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
