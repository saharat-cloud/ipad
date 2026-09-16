<?php
$pageTitle = 'ประวัติการยืม-คืน';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';

// Group records by user_id and borrowed_at
$groupedRecordsMap = [];
foreach ($records as $r) {
    $key = $r['user_id'] . '_' . strtotime($r['borrowed_at']);
    if (!isset($groupedRecordsMap[$key])) {
        $groupedRecordsMap[$key] = $r;
        $groupedRecordsMap[$key]['ipads'] = [];
    }
    $groupedRecordsMap[$key]['ipads'][] = [
        'id' => $r['id'], // borrow_record id
        'device_code' => $r['device_code'],
        'device_name' => $r['device_name'],
        'serial_number' => $r['serial_number'],
        'model' => $r['model'],
        'status' => $r['status'],
        'returned_at' => $r['returned_at'],
        'ret_first' => $r['ret_first'],
        'ret_last' => $r['ret_last'],
        'notes' => $r['notes']
    ];
}
$groupedRecords = array_values($groupedRecordsMap);
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
      <option value="pending_return" <?= $filters['status'] === 'pending_return' ? 'selected' : '' ?>>รออนุมัติคืน</option>
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
  <p class="text-sm text-slate-500 dark:text-slate-400">พบ <strong class="text-slate-800 dark:text-white"><?= count($groupedRecords) ?></strong> รายการ (จากทั้งหมด <?= count($records) ?> เครื่อง)</p>
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
          <th class="px-4 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">เครื่องที่ยืม</th>
          <th class="px-4 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">เวลายืม</th>
          <th class="px-4 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">กำหนดคืน</th>
          <th class="px-4 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">จัดการ</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
        <?php foreach ($groupedRecords as $i => $r): ?>
        <?php
          // Determine if any iPad is overdue or pending
          $hasOverdue = false;
          $hasPending = false;
          foreach ($r['ipads'] as $ip) {
              if ($ip['status'] === 'pending_return') {
                  $hasPending = true;
              }
              if (isOverdue($r['due_date']) && $ip['status'] !== 'returned' && $ip['status'] !== 'pending_return') {
                  $hasOverdue = true;
              }
          }
        ?>
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors <?= $hasPending ? 'bg-orange-50/50 dark:bg-orange-900/10 border-l-4 border-orange-400' : ($hasOverdue ? 'bg-red-50/50 dark:bg-red-900/10 border-l-4 border-red-400' : '') ?>">
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
            <div class="flex flex-wrap gap-1 max-w-[200px]">
              <?php foreach ($r['ipads'] as $ip): ?>
                <span class="inline-block px-2 py-1 bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-700 text-indigo-700 dark:text-indigo-300 text-xs rounded-md">
                  <?= sanitize($ip['device_code']) ?>
                </span>
              <?php endforeach; ?>
            </div>
          </td>
          <td class="px-4 py-3 text-slate-600 dark:text-slate-300"><?= formatDateTimeTH($r['borrowed_at']) ?></td>
          <td class="px-4 py-3 <?= $hasOverdue ? 'text-red-500 font-bold' : 'text-slate-600 dark:text-slate-300' ?>">
            <?= formatDateTimeTH($r['due_date']) ?>
            <?php if ($hasOverdue): ?>
            <span class="block text-xs text-red-500">⚠️ เกิน <?= timeDiffHuman($r['due_date']) ?></span>
            <?php endif; ?>
          </td>
          <td class="px-4 py-3">
            <button onclick="showDetails(this.dataset.info)" data-info="<?= htmlspecialchars(json_encode($r)) ?>" class="px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-lg text-sm font-semibold hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors">
              <i class="fas fa-file-alt mr-1"></i> รายละเอียด
            </button>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($groupedRecords)): ?>
        <tr><td colspan="6" class="text-center py-12 text-slate-400"><i class="fas fa-history text-3xl mb-2 block"></i>ไม่พบรายการ</td></tr>
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

function showDetails(dataStr) {
  try {
    const r = typeof dataStr === 'string' ? JSON.parse(dataStr) : dataStr;
    const dt = d => {
      if(!d) return '-';
      const x = new Date(d);
      return x.toLocaleString('th-TH');
    };
    
    let html = `
      <div class="text-left space-y-3 mt-4 text-sm">
        <div class="flex justify-between border-b border-slate-100 dark:border-slate-700 pb-2">
          <span class="text-slate-500">เวลายืม:</span>
          <span class="font-medium text-slate-800 dark:text-white">${dt(r.borrowed_at)}</span>
        </div>
        <div class="flex justify-between border-b border-slate-100 dark:border-slate-700 pb-2">
          <span class="text-slate-500">กำหนดคืน:</span>
          <span class="font-medium text-slate-800 dark:text-white">${dt(r.due_date)}</span>
        </div>
        <div class="mt-4">
          <span class="text-slate-500 block mb-2 font-bold">รายการเครื่องที่ยืม:</span>
          <div class="space-y-2 max-h-40 overflow-y-auto pr-1">
    `;

    let pendingIds = [];
    r.ipads.forEach(ip => {
        let statusClass = 'bg-slate-100 text-slate-700';
        let statusText = 'ไม่ระบุ';
        if(ip.status === 'active') { statusClass = 'bg-blue-100 text-blue-700'; statusText = 'กำลังยืม'; }
        if(ip.status === 'returned') { statusClass = 'bg-emerald-100 text-emerald-700'; statusText = 'คืนแล้ว'; }
        if(ip.status === 'overdue') { statusClass = 'bg-red-100 text-red-700'; statusText = 'เลยกำหนด'; }
        if(ip.status === 'pending_return') { 
            statusClass = 'bg-orange-100 text-orange-700'; 
            statusText = 'รออนุมัติ'; 
            pendingIds.push(ip.id);
        }
        
        let retInfo = '';
        if (ip.returned_at) {
             retInfo = `<div class="text-[10px] text-slate-500 mt-1">คืนเมื่อ: ${dt(ip.returned_at)} ${ip.ret_first ? `(โดย ${ip.ret_first})` : ''}</div>`;
        }
        
        html += `
          <div class="p-2 border border-slate-200 dark:border-slate-700 rounded-lg flex justify-between items-center bg-slate-50 dark:bg-slate-800">
             <div>
               <div class="font-bold text-slate-800 dark:text-white">${ip.device_code}</div>
               <div class="text-[10px] text-slate-500">${ip.device_name}</div>
               ${retInfo}
             </div>
             <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold ${statusClass}">${statusText}</span>
          </div>
        `;
    });

    html += `
          </div>
        </div>
      </div>
    `;
    
    let showApprove = pendingIds.length > 0;

    Swal.fire({
      title: 'รายละเอียดการยืม',
      html: html,
      showCancelButton: showApprove,
      showConfirmButton: true,
      confirmButtonText: showApprove ? '<i class="fas fa-check-circle mr-1"></i> อนุมัติการคืน' : 'ปิด',
      cancelButtonText: 'ปิด',
      confirmButtonColor: showApprove ? '#10b981' : '#6366f1',
      cancelButtonColor: '#64748b',
      background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#fff',
      color: document.documentElement.classList.contains('dark') ? '#f1f5f9' : '#1e293b'
    }).then((result) => {
      if (showApprove && result.isConfirmed) {
          approveReturns(pendingIds);
      }
    });
  } catch(e) { console.error(e); }
}

function approveReturns(recordIds) {
    Swal.fire({
        title: 'กำลังดำเนินการ...',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });
    fetch('api/approve_return.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ record_ids: recordIds })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ',
                text: 'อนุมัติการคืนเรียบร้อยแล้ว',
                confirmButtonColor: '#10b981'
            }).then(() => location.reload());
        } else {
            Swal.fire('ข้อผิดพลาด', data.message, 'error');
        }
    })
    .catch(e => {
        Swal.fire('ข้อผิดพลาด', e.message, 'error');
    });
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
