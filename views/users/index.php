<?php
$pageTitle = 'จัดการเจ้าหน้าที่';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';
?>

<div class="flex justify-between items-center mb-6">
  <div>
    <h1 class="text-2xl font-bold text-slate-800 dark:text-white mb-2">จัดการเจ้าหน้าที่</h1>
    <p class="text-slate-500 dark:text-slate-400">เพิ่ม ลบ แก้ไข ข้อมูลและสิทธิ์การเข้าถึงระบบหลังบ้าน</p>
  </div>
</div>

<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
  <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row justify-between gap-4">
    <form method="GET" class="flex flex-1 gap-2">
      <input type="hidden" name="page" value="users">
      <div class="relative flex-1 max-w-sm">
        <input type="text" name="search" value="<?= sanitize($search ?? '') ?>" placeholder="ค้นหา Username, ชื่อ..."
               class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:border-indigo-500 text-slate-800 dark:text-white">
        <i class="fas fa-search absolute left-3 top-2.5 text-slate-400"></i>
      </div>
      <select name="role" class="px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:border-indigo-500 text-slate-800 dark:text-white">
        <option value="">ทุกสิทธิ์</option>
        <option value="admin" <?= ($role ?? '') === 'admin' ? 'selected' : '' ?>>ผู้ดูแลระบบ (Admin)</option>
        <option value="staff" <?= ($role ?? '') === 'staff' ? 'selected' : '' ?>>เจ้าหน้าที่ (Staff)</option>
      </select>
      <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition-colors">
        <i class="fas fa-search mr-1"></i> ค้นหา
      </button>
    </form>
    <a href="?page=user_create" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-sm font-medium transition-colors shadow-lg shadow-purple-500/30 whitespace-nowrap">
      <i class="fas fa-plus mr-1"></i> เพิ่มเจ้าหน้าที่
    </a>
  </div>

  <div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
      <thead>
        <tr class="bg-slate-50/50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-xs uppercase tracking-wider">
          <th class="p-4 font-medium first:rounded-tl-2xl">ชื่อแสดงผล</th>
          <th class="p-4 font-medium">Username</th>
          <th class="p-4 font-medium">สิทธิ์ (Role)</th>
          <th class="p-4 font-medium">เข้าใช้งานล่าสุด</th>
          <th class="p-4 font-medium">สถานะ</th>
          <th class="p-4 font-medium text-right last:rounded-tr-2xl">จัดการ</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-sm">
        <?php foreach ($users as $u): ?>
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
          <td class="p-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white font-bold flex-shrink-0">
                <?= mb_substr($u['display_name'] ?? $u['username'], 0, 1) ?>
              </div>
              <div class="font-medium text-slate-800 dark:text-white"><?= sanitize($u['display_name']) ?></div>
            </div>
          </td>
          <td class="p-4 text-slate-600 dark:text-slate-400">
            <?= sanitize($u['username']) ?>
          </td>
          <td class="p-4">
            <?php if ($u['role'] === 'admin'): ?>
              <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">แอดมิน</span>
            <?php else: ?>
              <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">เจ้าหน้าที่</span>
            <?php endif; ?>
          </td>
          <td class="p-4 text-slate-500 text-xs">
            <?= $u['last_login'] ? formatDateTimeTH($u['last_login']) : '-' ?>
          </td>
          <td class="p-4">
            <?php if ($u['is_active']): ?>
              <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">ใช้งาน</span>
            <?php else: ?>
              <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">ระงับ</span>
            <?php endif; ?>
          </td>
          <td class="p-4 text-right">
            <div class="flex items-center justify-end gap-2">
              <a href="?page=user_edit&id=<?= $u['id'] ?>" class="p-2 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 bg-white dark:bg-slate-800 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg transition-colors border border-slate-200 dark:border-slate-700">
                <i class="fas fa-edit"></i>
              </a>
              <?php if ($u['id'] !== 1): ?>
              <button type="button" onclick="confirmDelete(<?= $u['id'] ?>, '<?= sanitize($u['username']) ?>')" class="p-2 text-slate-400 hover:text-red-600 dark:hover:text-red-400 bg-white dark:bg-slate-800 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors border border-slate-200 dark:border-slate-700">
                <i class="fas fa-trash-alt"></i>
              </button>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($users)): ?>
        <tr>
          <td colspan="6" class="p-8 text-center text-slate-500">ไม่พบข้อมูลเจ้าหน้าที่</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<form id="deleteForm" method="POST" action="?page=user_delete" class="hidden">
  <input type="hidden" name="id" id="deleteId">
</form>

<script>
function confirmDelete(id, name) {
    Swal.fire({
        title: 'ยืนยันการลบ?',
        text: `คุณต้องการลบเจ้าหน้าที่ "${name}" ใช่หรือไม่?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteForm').submit();
        }
    });
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
