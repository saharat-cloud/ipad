<?php
$pageTitle = 'ประวัติการยืม-คืน';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';
?>

<!-- Filters -->
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5 mb-5">
  <form method="GET" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
    <input type="hidden" name="page" value="history">
    <div class="relative lg:col-span-2">
      <input type="text" name="search" value="<?= sanitize($filters['search']) ?>"
        placeholder="ค้นหาชื่อ รหัส..."
        class="w-full border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700/50 rounded-xl px-4 py-2 pl-9 text-sm text-slate-800 dark:text-white focus:outline-none focus:border-indigo-400">
      <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
    </div>
    <select name="status" class="border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700/50 rounded-xl px-3 py-2 text-sm text-slate-800 dark:text-white focus:outline-none focus:border-indigo-400">
      <option value="">ทุกสถานะ</option>
      <option value="active"   <?= $filters['status'] === 'active'   ? 'selected' : '' ?>>กำลังยืม</option>
      <option value="returned" <?= $filters['status'] === 'returned' ? 'selected' : '' ?>>คืนแล้ว</option>
      <option value="overdue"  <?= $filters['status'] === 'overdue'  ? 'selected' : '' ?>>เกินกำหนด</option>
    </select>
    <input type="date" name="date_from" value="<?= $filters['date_from'] ?>"
      class="border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700/50 rounded-xl px-3 py-2 text-sm text-slate-800 dark:text-white focus:outline-none focus:border-indigo-400">
    <input type="date" name="date_to" value="<?= $filters['date_to'] ?>"
      class="border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700/50 rounded-xl px-3 py-2 text-sm text-slate-800 dark:text-white focus:outline-none focus:border-indigo-400">
    <div class="flex gap-2">
      <button type="submit" class="flex-1 bg-indigo-500 hover:bg-indigo-600 text-white rounded-xl px-3 py-2 text-sm font-medium transition-colors">
        <i class="fas fa-filter mr-1"></i>กรอง
      </button>
      <a href="?page=history" class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 text-sm transition-colors flex items-center">
        <i class="fas fa-times"></i>
      </a>
    </div>
  </form>
</div>

<!-- Export buttons -->
<div class="flex justify-between items-center mb-4">
  <p class="text-sm text-slate-500 dark:text-slate-400">พบ <strong class="text-slate-800 dark:text-white"><?= count($records) ?></strong> รายการ</p>
  <div class="flex gap-2">
    <button onclick="exportToExcel()" class="flex items-center gap-2 px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-sm font-medium transition-colors shadow-sm">
      <i class="fas fa-file-excel"></i>Excel
    </button>
    <button onclick="exportToPDF()" class="flex items-center gap-2 px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl text-sm font-medium transition-colors shadow-sm">
      <i class="fas fa-file-pdf"></i>PDF
    </button>
  </div>
</div>

<!-- Table -->
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
  <div class="overflow-x-auto">
    <table id="historyTable" class="w-full text-sm">
      <thead class="bg-slate-50 dark:bg-slate-700/50">
        <tr>
          <th class="px-4 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">#</th>
          <th class="px-4 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">ผู้ยืม</th>
          <th class="px-4 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">iPad</th>
          <th class="px-4 py-3 text-left font-semibold text-slate-500 dark:text-slate-400 hidden lg:table-cell">เวลายืม</th>
          <th class="px-4 py-3 text-left font-semibold text-slate-500 dark:text-slate-400 hidden md:table-cell">กำหนดคืน</th>
          <th class="px-4 py-3 text-left font-semibold text-slate-500 dark:text-slate-400 hidden xl:table-cell">เวลาคืน</th>
          <th class="px-4 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">สถานะ</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
        <?php foreach ($records as $i => $r): ?>
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors <?= $r['status'] === 'overdue' ? 'bg-red-50/50 dark:bg-red-900/10' : '' ?>">
          <td class="px-4 py-3 text-slate-400"><?= $i+1 ?></td>
          <td class="px-4 py-3">
            <div class="flex items-center gap-2">
              <?php $av = getAvatarUrl($r['avatar']); ?>
              <img src="<?= $av ?>" alt="" class="w-8 h-8 rounded-lg object-cover flex-shrink-0">
              <div>
                <p class="font-semibold text-slate-800 dark:text-white"><?= sanitize($r['first_name'].' '.$r['last_name']) ?></p>
                <p class="text-xs text-slate-400"><?= sanitize($r['class_position']) ?></p>
              </div>
            </div>
          </td>
          <td class="px-4 py-3">
            <p class="font-semibold text-slate-800 dark:text-white"><?= sanitize($r['device_code']) ?></p>
            <p class="text-xs text-slate-400"><?= sanitize($r['model']) ?></p>
          </td>
          <td class="px-4 py-3 hidden lg:table-cell text-slate-600 dark:text-slate-300"><?= formatDateTimeTH($r['borrowed_at']) ?></td>
          <td class="px-4 py-3 hidden md:table-cell <?= isOverdue($r['due_date']) && $r['status'] !== 'returned' ? 'text-red-500 font-bold' : 'text-slate-600 dark:text-slate-300' ?>">
            <?= formatDateTimeTH($r['due_date']) ?>
            <?php if (isOverdue($r['due_date']) && $r['status'] !== 'returned'): ?>
            <span class="block text-xs text-red-500">⚠️ เกิน <?= timeDiffHuman($r['due_date']) ?></span>
            <?php endif; ?>
          </td>
          <td class="px-4 py-3 hidden xl:table-cell text-slate-600 dark:text-slate-300">
            <?= $r['returned_at'] ? formatDateTimeTH($r['returned_at']) : '-' ?>
            <?php if ($r['returned_at'] && $r['ret_first']): ?>
            <p class="text-xs text-slate-400">โดย <?= sanitize($r['ret_first'].' '.$r['ret_last']) ?></p>
            <?php endif; ?>
          </td>
          <td class="px-4 py-3">
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= getStatusClass($r['status']) ?>">
              <?= getStatusLabel($r['status']) ?>
            </span>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($records)): ?>
        <tr><td colspan="7" class="text-center py-12 text-slate-400"><i class="fas fa-history text-3xl mb-2 block"></i>ไม่พบรายการ</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script>
$(document).ready(function() {
  if ($('#historyTable tbody tr').length > 15) {
    $('#historyTable').DataTable({
      language: { search:'ค้นหา:', lengthMenu:'แสดง _MENU_ รายการ', info:'แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ',
        paginate:{previous:'ก่อนหน้า',next:'ถัดไป'}, zeroRecords:'ไม่พบข้อมูล' },
      order:[[0,'desc']], pageLength:25, searching: false,
    });
  }
});

function exportToExcel() {
  const rows = [['#','ผู้ยืม','ชั้น/ตำแหน่ง','iPad','รุ่น','เวลายืม','กำหนดคืน','เวลาคืน','สถานะ']];
  <?php foreach ($records as $i => $r): ?>
  rows.push([
    <?= $i+1 ?>,
    '<?= addslashes($r['first_name'].' '.$r['last_name']) ?>',
    '<?= addslashes($r['class_position']) ?>',
    '<?= addslashes($r['device_code']) ?>',
    '<?= addslashes($r['model']) ?>',
    '<?= addslashes(formatDateTimeTH($r['borrowed_at'])) ?>',
    '<?= addslashes(formatDateTimeTH($r['due_date'])) ?>',
    '<?= addslashes($r['returned_at'] ? formatDateTimeTH($r['returned_at']) : '-') ?>',
    '<?= addslashes(getStatusLabel($r['status'])) ?>',
  ]);
  <?php endforeach; ?>
  const wb = XLSX.utils.book_new();
  const ws = XLSX.utils.aoa_to_sheet(rows);
  XLSX.utils.book_append_sheet(wb, ws, 'ประวัติยืม-คืน');
  XLSX.writeFile(wb, 'history_' + new Date().toISOString().slice(0,10) + '.xlsx');
}

function exportToPDF() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
  const rows = [];
  <?php foreach ($records as $i => $r): ?>
  rows.push([
    <?= $i+1 ?>,
    '<?= addslashes($r['first_name'].' '.$r['last_name']) ?>',
    '<?= addslashes($r['class_position']) ?>',
    '<?= addslashes($r['device_code']) ?>',
    '<?= addslashes(formatDateTimeTH($r['borrowed_at'])) ?>',
    '<?= addslashes(formatDateTimeTH($r['due_date'])) ?>',
    '<?= addslashes(getStatusLabel($r['status'])) ?>',
  ]);
  <?php endforeach; ?>
  doc.autoTable({
    head: [['#','ผู้ยืม','ชั้น','iPad','เวลายืม','กำหนดคืน','สถานะ']],
    body: rows,
    styles: { fontSize: 8 },
    headStyles: { fillColor: [99,102,241] },
  });
  doc.save('history_' + new Date().toISOString().slice(0,10) + '.pdf');
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
