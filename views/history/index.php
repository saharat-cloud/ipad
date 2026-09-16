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
        'notes' => $r['notes'],
        'due_date' => $r['due_date']
    ];
}

$groupedRecords = array_values($groupedRecordsMap);

// Filter by group_status
$groupFilter = $_GET['group_status'] ?? 'all';
$filteredGroupedRecords = [];
foreach ($groupedRecords as $r) {
    $isComplete = true;
    foreach ($r['ipads'] as $ip) {
        if (in_array($ip['status'], ['active', 'overdue', 'pending_return'])) {
            $isComplete = false;
            break;
        }
    }
    
    if ($groupFilter === 'complete' && !$isComplete) continue;
    if ($groupFilter === 'incomplete' && $isComplete) continue;
    
    $filteredGroupedRecords[] = $r;
}
$groupedRecords = $filteredGroupedRecords;
?>

<!-- Filters -->
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5 mb-5">
  <form method="GET" id="filterForm" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
    <input type="hidden" name="page" value="history">
    <input type="hidden" name="group_status" value="<?= sanitize($groupFilter) ?>">
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

<!-- Group Status Tabs -->
<div class="flex items-center gap-2 mb-4 bg-white dark:bg-slate-800 p-1.5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-x-auto">
  <?php
    function tabUrl($status) {
        $params = $_GET;
        $params['group_status'] = $status;
        return '?' . http_build_query($params);
    }
  ?>
  <a href="<?= tabUrl('all') ?>" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap <?= $groupFilter === 'all' ? 'bg-indigo-500 text-white shadow-md' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-700/50' ?>">
    <i class="fas fa-list mr-1"></i> ทั้งหมด
  </a>
  <a href="<?= tabUrl('complete') ?>" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap <?= $groupFilter === 'complete' ? 'bg-emerald-500 text-white shadow-md' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-700/50' ?>">
    <i class="fas fa-check-double mr-1"></i> ส่งคืนสำเร็จครบถ้วน
  </a>
  <a href="<?= tabUrl('incomplete') ?>" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap <?= $groupFilter === 'incomplete' ? 'bg-orange-500 text-white shadow-md' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-700/50' ?>">
    <i class="fas fa-exclamation-triangle mr-1"></i> ยังส่งคืนไม่ครบ
  </a>
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
          // Determine if any iPad is overdue or pending, and check for partial returns/extensions
          $hasOverdue = false;
          $hasPending = false;
          $hasExtended = false;
          $activeCount = 0;
          $returnedCount = 0;
          foreach ($r['ipads'] as $ip) {
              if (in_array($ip['status'], ['active', 'overdue'])) {
                  $activeCount++;
              }
              if (in_array($ip['status'], ['returned', 'pending_return'])) {
                  $returnedCount++;
              }
              if ($ip['status'] === 'pending_return') {
                  $hasPending = true;
              }
              if (isOverdue($r['due_date']) && $ip['status'] !== 'returned' && $ip['status'] !== 'pending_return') {
                  $hasOverdue = true;
              }
              if (strpos((string)$ip['notes'], 'ขอยืมต่อ') !== false || $ip['due_date'] !== $r['due_date']) {
                  $hasExtended = true;
              }
          }
          $isPartialReturn = ($activeCount > 0 && $returnedCount > 0);
        ?>
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors <?= $hasPending ? 'bg-orange-50/50 dark:bg-orange-900/10 border-l-4 border-orange-400' : ($hasOverdue ? 'bg-red-50/50 dark:bg-red-900/10 border-l-4 border-red-400' : '') ?>">
          <td class="px-4 py-3 text-slate-400"><?= $i+1 ?></td>
          <td class="px-4 py-3">
            <div class="flex items-center gap-2">
              <div>
                <p class="font-semibold text-slate-800 dark:text-white">
                  <?= sanitize($r['first_name'].' '.$r['last_name']) ?>
                  <?php if ($isPartialReturn): ?>
                    <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300">⚠️ คืนไม่ครบ</span>
                  <?php endif; ?>
                </p>
                <p class="text-xs text-slate-400">
                  <i class="fas fa-phone-alt text-[10px] mr-1"></i><?= sanitize($r['user_code']) ?>
                  <?php if ($r['class_position']): ?>
                    <span class="mx-1">•</span><?= sanitize($r['class_position']) ?>
                  <?php endif; ?>
                </p>
              </div>
            </div>
          </td>
          <td class="px-4 py-3">
            <div class="flex flex-wrap gap-1 max-w-[200px]">
              <?php foreach ($r['ipads'] as $ip): ?>
                <?php
                   $badgeClass = 'bg-slate-100 border-slate-200 text-slate-700 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-300';
                   if ($ip['status'] === 'returned') $badgeClass = 'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-900/30 dark:border-emerald-700 dark:text-emerald-300';
                   if (in_array($ip['status'], ['active', 'overdue'])) $badgeClass = 'bg-orange-50 border-orange-200 text-orange-700 dark:bg-orange-900/30 dark:border-orange-700 dark:text-orange-300';
                   if ($ip['status'] === 'pending_return') $badgeClass = 'bg-blue-50 border-blue-200 text-blue-700 dark:bg-blue-900/30 dark:border-blue-700 dark:text-blue-300';
                ?>
                <span class="inline-block px-2 py-1 <?= $badgeClass ?> border text-xs rounded-md">
                  <?= sanitize($ip['device_code']) ?>
                </span>
              <?php endforeach; ?>
            </div>
            <div class="mt-2 space-y-1">
              <?php foreach ($r['ipads'] as $ip): ?>
                  <?php if (in_array($ip['status'], ['active', 'overdue'])): ?>
                     <div class="text-[10px] text-orange-600 dark:text-orange-400 leading-tight">
                       <span class="font-bold"><?= sanitize($ip['device_code']) ?>:</span> 
                       <?php if (!empty($ip['notes'])): ?>
                         <?= sanitize($ip['notes']) ?>
                       <?php else: ?>
                         [กำหนดคืน: <?= formatDateTimeTH($ip['due_date']) ?>]
                       <?php endif; ?>
                     </div>
                  <?php endif; ?>
              <?php endforeach; ?>
            </div>
          </td>
          <td class="px-4 py-3 text-slate-600 dark:text-slate-300"><?= formatDateTimeTH($r['borrowed_at']) ?></td>
          <td class="px-4 py-3 <?= $hasOverdue ? 'text-red-500 font-bold' : 'text-slate-600 dark:text-slate-300' ?>">
            <?= formatDateTimeTH($r['due_date']) ?>
            <?php if ($hasExtended && $activeCount > 0): ?>
              <span class="block text-[10px] text-orange-500 mt-0.5 font-semibold"><i class="fas fa-clock mr-1"></i>มีการขอยืมต่อ</span>
            <?php endif; ?>
            <?php if ($hasOverdue): ?>
            <span class="block text-xs text-red-500">⚠️ เกิน <?= timeDiffHuman($r['due_date']) ?></span>
            <?php endif; ?>
          </td>
          <td class="px-4 py-3 text-right whitespace-nowrap">
            <button onclick='showDetails(<?= json_encode($r) ?>)' class="px-3 py-1.5 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-400 dark:hover:bg-indigo-900/50 rounded-lg text-xs font-medium transition-colors border border-indigo-100 dark:border-indigo-800">
              <i class="fas fa-file-alt mr-1"></i> รายละเอียด
            </button>
            <?php 
            $activeIds = [];
            foreach ($r['ipads'] as $ip) {
                if (in_array($ip['status'], ['active', 'overdue'])) {
                    $activeIds[] = $ip['id'];
                }
            }
            if (!empty($activeIds) && isset($_GET['status']) && $_GET['status'] === 'overdue'): 
            ?>
            <button onclick='staffReturnGroup(<?= json_encode($activeIds) ?>)' class="ml-1 px-3 py-1.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400 dark:hover:bg-emerald-900/50 rounded-lg text-xs font-medium transition-colors border border-emerald-100 dark:border-emerald-800">
              <i class="fas fa-check-circle mr-1"></i> ติดตามคืนแล้ว
            </button>
            <?php endif; ?>
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
<script src="<?= APP_URL ?>/assets/js/sarabun.js"></script>
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
  const rows = [['#','ผู้ยืม','iPad','เวลายืม','กำหนดคืน','สถานะ','หมายเหตุ','เบอร์ติดต่อ']];
  const exportData = <?= json_encode(prepareExportRows($records), JSON_UNESCAPED_UNICODE) ?>;
  
  exportData.forEach(row => rows.push(row));
  
  const wb = XLSX.utils.book_new();
  const ws = XLSX.utils.aoa_to_sheet(rows);
  XLSX.utils.book_append_sheet(wb, ws, 'ประวัติยืม-คืน');
  XLSX.writeFile(wb, 'history_' + new Date().toISOString().slice(0,10) + '.xlsx');
}

function exportToPDF() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
  
  if (window.SarabunBase64) {
    doc.addFileToVFS('Sarabun-Regular.ttf', window.SarabunBase64);
    doc.addFont('Sarabun-Regular.ttf', 'Sarabun', 'normal');
    doc.setFont('Sarabun');
  }
  
  const exportData = <?= json_encode(prepareExportRows($records), JSON_UNESCAPED_UNICODE) ?>;
  const rows = exportData;
  
  doc.autoTable({
    head: [['#','ผู้ยืม','iPad','เวลายืม','กำหนดคืน','สถานะ','หมายเหตุ','เบอร์ติดต่อ']],
    body: rows,
    styles: { font: 'Sarabun', fontSize: 10 },
    headStyles: { font: 'Sarabun', fillColor: [99,102,241] },
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
    
    let pendingIds = [];
    let returnedCount = 0;
    let activeCount = 0;
    
    r.ipads.forEach(ip => {
        if (ip.status === 'returned' || ip.status === 'pending_return') returnedCount++;
        else if (ip.status === 'active' || ip.status === 'overdue') activeCount++;
    });

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
        
        <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-xl p-3 flex justify-between items-center text-xs">
          <div class="text-center">
            <span class="block text-slate-500 mb-1">ยืมทั้งหมด</span>
            <span class="font-bold text-indigo-600 dark:text-indigo-400 text-sm">${r.ipads.length}</span>
          </div>
          <div class="text-center">
            <span class="block text-slate-500 mb-1">คืน/รอตรวจสอบ</span>
            <span class="font-bold text-emerald-600 dark:text-emerald-400 text-sm">${returnedCount}</span>
          </div>
          <div class="text-center">
            <span class="block text-slate-500 mb-1">ยังไม่คืน</span>
            <span class="font-bold text-orange-600 dark:text-orange-400 text-sm">${activeCount}</span>
          </div>
        </div>

        <div class="mt-4">
          <span class="text-slate-500 block mb-2 font-bold">รายการเครื่องที่ยืม:</span>
          <div class="space-y-2 max-h-40 overflow-y-auto pr-1">
    `;

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
             retInfo += `<div class="text-[10px] text-slate-500 mt-1">คืนเมื่อ: ${dt(ip.returned_at)} (รับคืนโดยเจ้าหน้าที่)</div>`;
        }
        
        // Always show due_date if it's active or overdue
        if (ip.due_date && (ip.status === 'active' || ip.status === 'overdue')) {
             retInfo += `<div class="text-[10px] text-orange-600 dark:text-orange-400 mt-1"><i class="fas fa-clock mr-1"></i>กำหนดคืน: ${dt(ip.due_date)}</div>`;
        }

        let notesInfo = '';
        if (ip.notes) {
             notesInfo = `<div class="text-[11px] text-orange-500 mt-1.5 p-1.5 bg-orange-50 dark:bg-orange-900/20 rounded border border-orange-100 dark:border-orange-800/50"><i class="fas fa-info-circle mr-1"></i>${ip.notes}</div>`;
        }
        
        html += `
          <div class="p-2 border border-slate-200 dark:border-slate-700 rounded-lg flex justify-between items-center bg-slate-50 dark:bg-slate-800">
             <div class="flex-1 min-w-0 pr-2">
               <div class="font-bold text-slate-800 dark:text-white">${ip.device_code}</div>
               <div class="text-[10px] text-slate-500">${ip.device_name}</div>
               ${retInfo}
               ${notesInfo}
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
    fetch('?page=api_approve_return', {
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

function staffReturnGroup(recordIds) {
    Swal.fire({
        title: 'ยืนยันการรับคืน',
        text: 'คุณต้องการบันทึกว่าได้รับ iPad เหล่านี้คืนมาแล้วใช่หรือไม่?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'ใช่, ได้รับคืนแล้ว',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'กำลังบันทึก...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            fetch('?page=staff_transaction_api&action=return', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ records: recordIds })
            })
            .then(r => r.json())
            .then(res => {
                if(res.success) {
                    Swal.fire({ icon: 'success', title: 'บันทึกการรับคืนสำเร็จ', showConfirmButton: false, timer: 1500 })
                    .then(() => location.reload());
                } else {
                    Swal.fire('ข้อผิดพลาด', res.message || 'เกิดข้อผิดพลาด', 'error');
                }
            })
            .catch(e => {
                console.error(e);
                Swal.fire('ข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
            });
        }
    });
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
