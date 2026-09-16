<?php
$pageTitle = 'รายงาน';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';

$thMonths = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
$dLabels = $dData = $mLabels = $mData = $cLabels = $cData = [];

foreach ($dailyStats as $d) {
    $dt = new DateTime($d['date']);
    $dLabels[] = $dt->format('j') . ' ' . $thMonths[(int)$dt->format('n')];
    $dData[]   = (int)$d['count'];
}
foreach ($monthlyStats as $m) {
    $dt = DateTime::createFromFormat('Y-m', $m['month']);
    $mLabels[] = $thMonths[(int)$dt->format('n')] . ' ' . ((int)$dt->format('Y') + 543);
    $mData[]   = (int)$m['count'];
}
$cLabels = array_column($classStats, 'class_position');
$cData   = array_map('intval', array_column($classStats, 'count'));
?>

<!-- Filter Panel -->
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5 mb-5">
  <form method="GET" class="flex flex-wrap gap-3 items-end">
    <input type="hidden" name="page" value="reports">
    <div>
      <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 block mb-1">ตั้งแต่วันที่</label>
      <input type="date" name="date_from" value="<?= $filters['date_from'] ?>"
        class="border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700/50 rounded-xl px-3 py-2 text-sm text-slate-800 dark:text-white focus:outline-none focus:border-indigo-400">
    </div>
    <div>
      <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 block mb-1">ถึงวันที่</label>
      <input type="date" name="date_to" value="<?= $filters['date_to'] ?>"
        class="border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700/50 rounded-xl px-3 py-2 text-sm text-slate-800 dark:text-white focus:outline-none focus:border-indigo-400">
    </div>
    <div>
      <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 block mb-1">ชั้นเรียน</label>
      <select name="class" class="border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700/50 rounded-xl px-3 py-2 text-sm text-slate-800 dark:text-white focus:outline-none focus:border-indigo-400">
        <option value="">ทุกชั้น</option>
        <?php foreach ($classes as $c): ?>
        <option value="<?= sanitize($c) ?>" <?= $filters['class'] === $c ? 'selected':'' ?>><?= sanitize($c) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 block mb-1">สถานะ</label>
      <select name="status" class="border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700/50 rounded-xl px-3 py-2 text-sm text-slate-800 dark:text-white focus:outline-none focus:border-indigo-400">
        <option value="">ทุกสถานะ</option>
        <option value="active"   <?= $filters['status']==='active'   ?'selected':'' ?>>กำลังยืม</option>
        <option value="returned" <?= $filters['status']==='returned' ?'selected':'' ?>>คืนแล้ว</option>
        <option value="overdue"  <?= $filters['status']==='overdue'  ?'selected':'' ?>>เกินกำหนด</option>
      </select>
    </div>
    <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white rounded-xl px-5 py-2 text-sm font-semibold transition-colors flex items-center gap-2">
      <i class="fas fa-chart-bar"></i>สร้างรายงาน
    </button>
    <a href="?page=reports" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 text-sm transition-colors flex items-center gap-1">
      <i class="fas fa-redo"></i>รีเซ็ต
    </a>
  </form>
</div>

<!-- Summary Stat -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
  <?php
  $totalBorrow   = count($records);
  $totalReturned = count(array_filter($records, fn($r) => $r['status'] === 'returned'));
  $totalActive   = count(array_filter($records, fn($r) => $r['status'] === 'active'));
  $totalOverdue  = count(array_filter($records, fn($r) => $r['status'] === 'overdue'));
  $summaryCards = [
    ['label'=>'รายการทั้งหมด','count'=>$totalBorrow,  'color'=>'indigo'],
    ['label'=>'คืนแล้ว',       'count'=>$totalReturned,'color'=>'emerald'],
    ['label'=>'กำลังยืม',      'count'=>$totalActive,  'color'=>'blue'],
    ['label'=>'เกินกำหนด',     'count'=>$totalOverdue,  'color'=>'red'],
  ];
  foreach ($summaryCards as $c): ?>
  <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700 shadow-sm text-center">
    <p class="text-3xl font-extrabold text-<?= $c['color'] ?>-600 dark:text-<?= $c['color'] ?>-400"><?= $c['count'] ?></p>
    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1"><?= $c['label'] ?></p>
  </div>
  <?php endforeach; ?>
</div>

<!-- Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
  <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm">
    <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2 mb-5">
      <i class="fas fa-chart-bar text-indigo-500"></i>สถิติรายวัน (30 วัน)
    </h3>
    <canvas id="reportDailyChart" height="150"></canvas>
  </div>
  <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm">
    <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2 mb-5">
      <i class="fas fa-chart-line text-cyan-500"></i>สถิติรายเดือน (6 เดือน)
    </h3>
    <canvas id="reportMonthlyChart" height="150"></canvas>
  </div>
</div>
<?php if (!empty($cLabels)): ?>
<div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm mb-6">
  <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2 mb-5">
    <i class="fas fa-graduation-cap text-pink-500"></i>การยืมตามชั้นเรียน (เดือนนี้)
  </h3>
  <canvas id="reportClassChart" height="100"></canvas>
</div>
<?php endif; ?>

<!-- Export -->
<div class="flex justify-between items-center mb-4">
  <p class="text-sm text-slate-500 dark:text-slate-400">ผลการค้นหา: <strong class="text-slate-800 dark:text-white"><?= count($records) ?></strong> รายการ</p>
  <div class="flex gap-2">
    <button onclick="exportReportExcel()" class="flex items-center gap-2 px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-sm font-medium transition-colors shadow-sm">
      <i class="fas fa-file-excel"></i>Export Excel
    </button>
    <button onclick="exportReportPDF()" class="flex items-center gap-2 px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl text-sm font-medium transition-colors shadow-sm">
      <i class="fas fa-file-pdf"></i>Export PDF
    </button>
  </div>
</div>

<!-- Data Table -->
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
  <div class="overflow-x-auto">
    <table id="reportTable" class="w-full text-sm">
      <thead class="bg-slate-50 dark:bg-slate-700/50">
        <tr>
          <th class="px-4 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">ผู้ยืม</th>
          <th class="px-4 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">iPad</th>
          <th class="px-4 py-3 text-left font-semibold text-slate-500 dark:text-slate-400 hidden md:table-cell">เวลายืม</th>
          <th class="px-4 py-3 text-left font-semibold text-slate-500 dark:text-slate-400 hidden md:table-cell">กำหนดคืน</th>
          <th class="px-4 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">สถานะ</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
        <?php foreach ($records as $r): ?>
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
          <td class="px-4 py-3">
            <p class="font-semibold text-slate-800 dark:text-white"><?= sanitize($r['first_name'].' '.$r['last_name']) ?></p>
            <p class="text-xs text-slate-400"><?= sanitize($r['class_position']) ?></p>
          </td>
          <td class="px-4 py-3">
            <p class="font-semibold text-slate-800 dark:text-white"><?= sanitize($r['device_code']) ?></p>
            <p class="text-xs text-slate-400"><?= sanitize($r['model']) ?></p>
          </td>
          <td class="px-4 py-3 hidden md:table-cell text-slate-600 dark:text-slate-300"><?= formatDateTimeTH($r['borrowed_at']) ?></td>
          <td class="px-4 py-3 hidden md:table-cell <?= isOverdue($r['due_date']) && $r['status'] !== 'returned' ? 'text-red-500 font-bold' : 'text-slate-600 dark:text-slate-300' ?>"><?= formatDateTimeTH($r['due_date']) ?></td>
          <td class="px-4 py-3">
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= getStatusClass($r['status']) ?>"><?= getStatusLabel($r['status']) ?></span>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($records)): ?>
        <tr><td colspan="5" class="text-center py-12 text-slate-400">ไม่พบรายการในช่วงที่เลือก</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/sarabun.js"></script>
<script>
const isDark = document.documentElement.classList.contains('dark');
const gridC  = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
const textC  = isDark ? '#94a3b8' : '#64748b';

new Chart(document.getElementById('reportDailyChart'), {
  type:'bar',
  data:{ labels:<?= json_encode($dLabels,JSON_UNESCAPED_UNICODE) ?>, datasets:[{label:'การยืม',data:<?= json_encode($dData) ?>,backgroundColor:'rgba(99,102,241,0.7)',borderColor:'#6366f1',borderWidth:2,borderRadius:6}] },
  options:{ responsive:true, plugins:{legend:{display:false}}, scales:{ y:{beginAtZero:true,ticks:{color:textC},grid:{color:gridC}}, x:{ticks:{color:textC},grid:{display:false}} } }
});
new Chart(document.getElementById('reportMonthlyChart'), {
  type:'line',
  data:{ labels:<?= json_encode($mLabels,JSON_UNESCAPED_UNICODE) ?>, datasets:[{label:'การยืม',data:<?= json_encode($mData) ?>,borderColor:'#06b6d4',backgroundColor:'rgba(6,182,212,0.1)',tension:0.4,fill:true,pointBackgroundColor:'#06b6d4',pointRadius:5}] },
  options:{ responsive:true, plugins:{legend:{display:false}}, scales:{ y:{beginAtZero:true,ticks:{color:textC},grid:{color:gridC}}, x:{ticks:{color:textC},grid:{display:false}} } }
});
<?php if (!empty($cLabels)): ?>
new Chart(document.getElementById('reportClassChart'), {
  type:'bar',
  data:{ labels:<?= json_encode($cLabels,JSON_UNESCAPED_UNICODE) ?>, datasets:[{data:<?= json_encode($cData) ?>,backgroundColor:'rgba(236,72,153,0.7)',borderColor:'#ec4899',borderWidth:2,borderRadius:6}] },
  options:{ responsive:true, plugins:{legend:{display:false}}, scales:{ x:{beginAtZero:true,ticks:{color:textC},grid:{color:gridC}}, y:{ticks:{color:textC},grid:{display:false}} }, indexAxis:'y' }
});
<?php endif; ?>

function exportReportExcel() {
  const rows = [['#','ผู้ยืม','เบอร์ติดต่อ','iPad','เวลายืม','กำหนดคืน','สถานะ','หมายเหตุ']];
  const exportData = <?= json_encode(prepareExportRows($records), JSON_UNESCAPED_UNICODE) ?>;
  exportData.forEach(row => rows.push(row));
  
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, XLSX.utils.aoa_to_sheet(rows), 'รายงาน');
  XLSX.writeFile(wb, 'report_' + new Date().toISOString().slice(0,10) + '.xlsx');
}

function exportReportPDF() {
  const {jsPDF} = window.jspdf;
  const doc = new jsPDF({orientation:'landscape',unit:'mm',format:'a4'});
  
  if (window.SarabunBase64) {
    doc.addFileToVFS('Sarabun-Regular.ttf', window.SarabunBase64);
    doc.addFont('Sarabun-Regular.ttf', 'Sarabun', 'normal');
    doc.setFont('Sarabun');
  }
  
  const exportData = <?= json_encode(prepareExportRows($records), JSON_UNESCAPED_UNICODE) ?>;
  const rows = exportData;
  
  doc.setFontSize(14);
  doc.text("บันทึกการยืม-คืน ipad ของวิทยาลัยเทคโนโลยีขอนแก่น", doc.internal.pageSize.width / 2, 15, { align: 'center' });
  
  doc.autoTable({
    startY: 20,
    head:[['#','ผู้ยืม','เบอร์ติดต่อ','iPad','เวลายืม','กำหนดคืน','สถานะ','หมายเหตุ']],
    body: rows,
    styles: { font: 'Sarabun', fontSize: 10 },
    headStyles: { font: 'Sarabun', fillColor: [99,102,241] },
  });
  doc.save('report_' + new Date().toISOString().slice(0,10) + '.pdf');
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
