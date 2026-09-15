<?php
$pageTitle = 'จัดการ iPad';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';
$ipadModel = new Ipad($pdo);
$stats = $ipadModel->getStats();
?>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
  <?php $cards = [
    ['label'=>'ทั้งหมด',    'count'=>$stats['total'],       'icon'=>'fa-tablet-alt',   'col'=>'indigo'],
    ['label'=>'พร้อมใช้',   'count'=>$stats['available'],   'icon'=>'fa-check-circle', 'col'=>'emerald'],
    ['label'=>'กำลังยืม',   'count'=>$stats['borrowed'],    'icon'=>'fa-hand-holding', 'col'=>'blue'],
    ['label'=>'ซ่อมบำรุง',  'count'=>$stats['maintenance'], 'icon'=>'fa-tools',        'col'=>'amber'],
  ];
  foreach ($cards as $c): ?>
  <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center gap-3">
    <div class="w-10 h-10 bg-<?= $c['col'] ?>-100 dark:bg-<?= $c['col'] ?>-900/30 rounded-xl flex items-center justify-center">
      <i class="fas <?= $c['icon'] ?> text-<?= $c['col'] ?>-500 text-lg"></i>
    </div>
    <div>
      <p class="text-2xl font-extrabold text-slate-800 dark:text-white"><?= $c['count'] ?></p>
      <p class="text-xs text-slate-400"><?= $c['label'] ?></p>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="flex items-center justify-between mb-4">
  <form method="GET" class="flex gap-2">
    <input type="hidden" name="page" value="ipads">
    <div class="relative">
      <input type="text" name="search" value="<?= sanitize($_GET['search'] ?? '') ?>"
        placeholder="รหัส, ชื่อ, Serial..."
        class="border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 rounded-xl px-4 py-2 pl-9 text-sm text-slate-800 dark:text-white focus:outline-none focus:border-indigo-400 w-52">
      <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
    </div>
    <select name="status" class="border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 rounded-xl px-3 py-2 text-sm text-slate-800 dark:text-white focus:outline-none focus:border-indigo-400">
      <option value="">ทุกสถานะ</option>
      <?php foreach (['available'=>'พร้อมใช้','borrowed'=>'กำลังยืม','maintenance'=>'ซ่อมบำรุง','disabled'=>'ปิดใช้'] as $v => $l): ?>
      <option value="<?= $v ?>" <?= ($_GET['status']??'') === $v ? 'selected':'' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white rounded-xl px-4 py-2 text-sm font-medium transition-colors"><i class="fas fa-search mr-1"></i>ค้นหา</button>
  </form>
  <a href="?page=ipad_create" class="flex items-center gap-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl px-5 py-2.5 text-sm font-semibold hover:opacity-90 transition-all shadow-md">
    <i class="fas fa-plus"></i>เพิ่ม iPad
  </a>
</div>

<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 dark:bg-slate-700/50">
        <tr>
          <th class="px-5 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">รหัส / ชื่อ</th>
          <th class="px-5 py-3 text-left font-semibold text-slate-500 dark:text-slate-400 hidden md:table-cell">รุ่น</th>
          <th class="px-5 py-3 text-left font-semibold text-slate-500 dark:text-slate-400 hidden lg:table-cell">Serial Number</th>
          <th class="px-5 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">สถานะ</th>
          <th class="px-5 py-3 text-right font-semibold text-slate-500 dark:text-slate-400">จัดการ</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
        <?php foreach ($ipads as $ip): ?>
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
          <td class="px-5 py-3">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900/30 dark:to-purple-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-tablet-alt text-indigo-500"></i>
              </div>
              <div>
                <p class="font-bold text-slate-800 dark:text-white"><?= sanitize($ip['device_code']) ?></p>
                <p class="text-xs text-slate-400"><?= sanitize($ip['device_name']) ?></p>
              </div>
            </div>
          </td>
          <td class="px-5 py-3 hidden md:table-cell text-slate-600 dark:text-slate-300"><?= sanitize($ip['model']) ?></td>
          <td class="px-5 py-3 hidden lg:table-cell text-slate-400 font-mono text-xs"><?= sanitize($ip['serial_number']) ?></td>
          <td class="px-5 py-3">
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= getStatusClass($ip['status']) ?>">
              <?= getStatusLabel($ip['status']) ?>
            </span>
          </td>
          <td class="px-5 py-3">
            <div class="flex items-center justify-end gap-2">
              <a href="?page=ipad_print&id=<?= $ip['id'] ?>" target="_blank"
                class="p-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-purple-100 dark:hover:bg-purple-900/30 hover:text-purple-600 dark:hover:text-purple-400 transition-all" title="พิมพ์ Barcode">
                <i class="fas fa-print text-sm"></i>
              </a>
              <a href="?page=ipad_edit&id=<?= $ip['id'] ?>"
                class="p-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all">
                <i class="fas fa-edit text-sm"></i>
              </a>
              <button onclick="deleteIpad(<?= $ip['id'] ?>, '<?= sanitize($ip['device_name']) ?>')"
                class="p-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-red-100 dark:hover:bg-red-900/30 hover:text-red-600 dark:hover:text-red-400 transition-all">
                <i class="fas fa-trash-alt text-sm"></i>
              </button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($ipads)): ?>
        <tr><td colspan="5" class="text-center py-12 text-slate-400"><i class="fas fa-tablet-alt text-3xl mb-2 block"></i>ไม่พบ iPad</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function deleteIpad(id, name) {
  Swal.fire({
    title: 'ลบ iPad?',
    html: `ต้องการลบ <b>${name}</b> ?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i>ลบ',
    cancelButtonText: 'ยกเลิก',
    confirmButtonColor: '#ef4444',
    background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#fff',
    color: document.documentElement.classList.contains('dark') ? '#f1f5f9' : '#1e293b',
  }).then(r => { if (r.isConfirmed) window.location.href = '?page=ipad_delete&id=' + id; });
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
