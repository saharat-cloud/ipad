<?php
$pageTitle = 'จัดการผู้ใช้';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';
?>

<div class="flex items-center justify-between mb-6">
  <div class="flex gap-2">
    <form method="GET" class="flex gap-2">
      <input type="hidden" name="page" value="users">
      <div class="relative">
        <input type="text" name="search" value="<?= sanitize($_GET['search'] ?? '') ?>"
          placeholder="ค้นหาชื่อ รหัส..."
          class="border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 rounded-xl px-4 py-2 pl-9 text-sm text-slate-800 dark:text-white focus:outline-none focus:border-indigo-400 transition-all w-52">
        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
      </div>
      <select name="role" class="border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 rounded-xl px-3 py-2 text-sm text-slate-800 dark:text-white focus:outline-none focus:border-indigo-400">
        <option value="">ทุกประเภท</option>
        <option value="student" <?= ($_GET['role']??'') === 'student' ? 'selected' : '' ?>>นักเรียน</option>
        <option value="teacher" <?= ($_GET['role']??'') === 'teacher' ? 'selected' : '' ?>>ครู</option>
        <option value="staff"   <?= ($_GET['role']??'') === 'staff'   ? 'selected' : '' ?>>เจ้าหน้าที่</option>
      </select>
      <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white rounded-xl px-4 py-2 text-sm font-medium transition-colors">
        <i class="fas fa-search mr-1"></i>ค้นหา
      </button>
    </form>
  </div>
  <a href="?page=user_create" class="flex items-center gap-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl px-5 py-2.5 text-sm font-semibold hover:opacity-90 transition-all shadow-md hover:shadow-indigo-500/30">
    <i class="fas fa-plus"></i>เพิ่มผู้ใช้
  </a>
</div>

<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
  <div class="overflow-x-auto">
    <table id="usersTable" class="w-full text-sm">
      <thead class="bg-slate-50 dark:bg-slate-700/50">
        <tr>
          <th class="px-5 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">ผู้ใช้</th>
          <th class="px-5 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">รหัส / Barcode</th>
          <th class="px-5 py-3 text-left font-semibold text-slate-500 dark:text-slate-400 hidden md:table-cell">ชั้น/ตำแหน่ง</th>
          <th class="px-5 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">ประเภท</th>
          <th class="px-5 py-3 text-left font-semibold text-slate-500 dark:text-slate-400 hidden lg:table-cell">สถานะ</th>
          <th class="px-5 py-3 text-right font-semibold text-slate-500 dark:text-slate-400">จัดการ</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
        <?php foreach ($users as $u): ?>
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
          <td class="px-5 py-3">
            <div class="flex items-center gap-3">
              <?php $av = getAvatarUrl($u['avatar']); ?>
              <img src="<?= $av ?>" alt="" class="w-10 h-10 rounded-xl object-cover flex-shrink-0 border border-slate-200 dark:border-slate-600">
              <div>
                <p class="font-semibold text-slate-800 dark:text-white"><?= sanitize($u['first_name'].' '.$u['last_name']) ?></p>
              </div>
            </div>
          </td>
          <td class="px-5 py-3">
            <p class="font-mono text-sm text-slate-800 dark:text-white"><?= sanitize($u['user_code']) ?></p>
            <p class="text-xs text-slate-400 font-mono"><?= sanitize($u['barcode_id']) ?></p>
          </td>
          <td class="px-5 py-3 hidden md:table-cell text-slate-600 dark:text-slate-300"><?= sanitize($u['class_position']) ?></td>
          <td class="px-5 py-3">
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold
              <?= $u['role'] === 'student' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
               : ($u['role'] === 'teacher' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400'
               : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300') ?>">
              <?= getRoleLabel($u['role']) ?>
            </span>
          </td>
          <td class="px-5 py-3 hidden lg:table-cell">
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= $u['is_active'] ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' ?>">
              <?= $u['is_active'] ? 'ใช้งาน' : 'ระงับ' ?>
            </span>
          </td>
          <td class="px-5 py-3">
            <div class="flex items-center justify-end gap-2">
              <a href="?page=user_edit&id=<?= $u['id'] ?>" class="p-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all">
                <i class="fas fa-edit text-sm"></i>
              </a>
              <button onclick="deleteUser(<?= $u['id'] ?>, '<?= sanitize($u['first_name'].' '.$u['last_name']) ?>')"
                class="p-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-red-100 dark:hover:bg-red-900/30 hover:text-red-600 dark:hover:text-red-400 transition-all">
                <i class="fas fa-trash-alt text-sm"></i>
              </button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($users)): ?>
        <tr><td colspan="6" class="text-center py-12 text-slate-400"><i class="fas fa-users-slash text-3xl mb-2 block"></i>ไม่พบผู้ใช้</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function deleteUser(id, name) {
  Swal.fire({
    title: 'ลบผู้ใช้?',
    html: `ต้องการลบ <b>${name}</b> ?<br><small class="text-gray-400">การกระทำนี้ไม่สามารถย้อนกลับได้</small>`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i>ลบ',
    cancelButtonText: 'ยกเลิก',
    confirmButtonColor: '#ef4444',
    background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#fff',
    color: document.documentElement.classList.contains('dark') ? '#f1f5f9' : '#1e293b',
  }).then(r => { if (r.isConfirmed) window.location.href = '?page=user_delete&id=' + id; });
}

// DataTable
$(document).ready(function() {
  if ($('#usersTable tbody tr').length > 10) {
    $('#usersTable').DataTable({
      language: { search:'ค้นหา:', lengthMenu:'แสดง _MENU_ รายการ', info:'แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ',
        paginate:{ previous:'ก่อนหน้า', next:'ถัดไป' }, zeroRecords:'ไม่พบข้อมูล' },
      order: [[0,'asc']], pageLength: 25, searching: false,
    });
  }
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
