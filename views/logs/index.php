<?php
$pageTitle = 'บันทึกกิจกรรม';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';

$actionIcons = [
    'login'        => ['icon'=>'fa-sign-in-alt',   'color'=>'text-emerald-500'],
    'logout'       => ['icon'=>'fa-sign-out-alt',  'color'=>'text-slate-400'],
    'borrow'       => ['icon'=>'fa-hand-holding',  'color'=>'text-blue-500'],
    'return'       => ['icon'=>'fa-undo',          'color'=>'text-teal-500'],
    'create_user'  => ['icon'=>'fa-user-plus',     'color'=>'text-indigo-500'],
    'update_user'  => ['icon'=>'fa-user-edit',     'color'=>'text-amber-500'],
    'delete_user'  => ['icon'=>'fa-user-minus',    'color'=>'text-red-500'],
    'create_ipad'  => ['icon'=>'fa-tablet-alt',    'color'=>'text-purple-500'],
    'update_ipad'  => ['icon'=>'fa-pencil-alt',    'color'=>'text-amber-500'],
    'delete_ipad'  => ['icon'=>'fa-trash-alt',     'color'=>'text-red-500'],
];
?>

<div class="flex items-center justify-between mb-5">
  <div class="flex gap-2">
    <form method="GET" class="flex gap-2">
      <input type="hidden" name="page" value="logs">
      <select name="action" class="border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 rounded-xl px-3 py-2 text-sm text-slate-800 dark:text-white focus:outline-none focus:border-indigo-400">
        <option value="">ทุกกิจกรรม</option>
        <?php foreach (array_keys($actionIcons) as $a): ?>
        <option value="<?= $a ?>" <?= ($_GET['action']??'')===$a?'selected':'' ?>><?= $a ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white rounded-xl px-4 py-2 text-sm font-medium transition-colors">
        <i class="fas fa-filter mr-1"></i>กรอง
      </button>
    </form>
  </div>
  <p class="text-sm text-slate-400"><?= count($logs) ?> รายการล่าสุด</p>
</div>

<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 dark:bg-slate-700/50">
        <tr>
          <th class="px-5 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">กิจกรรม</th>
          <th class="px-5 py-3 text-left font-semibold text-slate-500 dark:text-slate-400 hidden md:table-cell">ผู้ดำเนินการ</th>
          <th class="px-5 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">รายละเอียด</th>
          <th class="px-5 py-3 text-left font-semibold text-slate-500 dark:text-slate-400 hidden lg:table-cell">IP</th>
          <th class="px-5 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">เวลา</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
        <?php foreach ($logs as $log):
          $ai = $actionIcons[$log['action']] ?? ['icon'=>'fa-circle','color'=>'text-slate-400'];
        ?>
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
          <td class="px-5 py-3">
            <div class="flex items-center gap-2">
              <i class="fas <?= $ai['icon'] ?> <?= $ai['color'] ?> w-5 text-center"></i>
              <span class="font-medium text-slate-700 dark:text-slate-300"><?= sanitize($log['action']) ?></span>
            </div>
          </td>
          <td class="px-5 py-3 hidden md:table-cell">
            <p class="font-medium text-slate-800 dark:text-white"><?= sanitize($log['display_name'] ?? $log['username'] ?? 'ระบบ') ?></p>
          </td>
          <td class="px-5 py-3 text-slate-600 dark:text-slate-300 max-w-xs">
            <p class="truncate"><?= sanitize($log['description'] ?? '-') ?></p>
          </td>
          <td class="px-5 py-3 hidden lg:table-cell text-slate-400 font-mono text-xs"><?= sanitize($log['ip_address'] ?? '-') ?></td>
          <td class="px-5 py-3 text-slate-400 text-xs whitespace-nowrap"><?= formatDateTimeTH($log['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($logs)): ?>
        <tr><td colspan="5" class="text-center py-12 text-slate-400"><i class="fas fa-scroll text-3xl mb-2 block"></i>ยังไม่มีบันทึก</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
