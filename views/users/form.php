<?php
$pageTitle = ($action === 'create' ? 'เพิ่มเจ้าหน้าที่' : 'แก้ไขข้อมูลเจ้าหน้าที่');
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';
?>

<div class="max-w-2xl mx-auto">
  <div class="flex items-center gap-4 mb-6">
    <a href="?page=users" class="w-10 h-10 bg-white dark:bg-slate-800 rounded-xl flex items-center justify-center text-slate-500 hover:text-indigo-600 shadow-sm border border-slate-200 dark:border-slate-700 transition-colors">
      <i class="fas fa-arrow-left"></i>
    </a>
    <div>
      <h1 class="text-2xl font-bold text-slate-800 dark:text-white"><?= $pageTitle ?></h1>
      <p class="text-slate-500 dark:text-slate-400">กรอกข้อมูลบัญชีเจ้าหน้าที่ระบบหลังบ้าน</p>
    </div>
  </div>

  <?php if (!empty($errors)): ?>
  <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl p-4 mb-6">
    <div class="flex items-center gap-2 text-red-700 dark:text-red-400 font-bold mb-2">
      <i class="fas fa-exclamation-circle"></i> โปรดแก้ไขข้อผิดพลาดต่อไปนี้:
    </div>
    <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-300 space-y-1">
      <?php foreach($errors as $e): ?>
        <li><?= sanitize($e) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

  <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
    <form method="POST" action="?page=users&action=<?= $action ?><?= $action === 'edit' ? '&id='.$user['id'] : '' ?>">
      <div class="p-6 space-y-6">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
              Username <span class="text-red-500">*</span>
            </label>
            <input type="text" name="username" value="<?= sanitize($_POST['username'] ?? $user['username'] ?? '') ?>" required
                   class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500 transition-colors">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
              ชื่อแสดงผล (Display Name) <span class="text-red-500">*</span>
            </label>
            <input type="text" name="display_name" value="<?= sanitize($_POST['display_name'] ?? $user['display_name'] ?? '') ?>" required
                   class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500 transition-colors">
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
            รหัสผ่าน (Password) <?= $action === 'create' ? '<span class="text-red-500">*</span>' : '<span class="text-xs text-slate-400 font-normal ml-2">(เว้นว่างไว้หากไม่ต้องการเปลี่ยน)</span>' ?>
          </label>
          <input type="password" name="password" <?= $action === 'create' ? 'required' : '' ?>
                 class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500 transition-colors">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
              สิทธิ์การใช้งาน (Role)
            </label>
            <select name="role" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500 transition-colors">
              <?php $r = $_POST['role'] ?? $user['role'] ?? 'staff'; ?>
              <option value="admin" <?= $r === 'admin' ? 'selected' : '' ?>>ผู้ดูแลระบบ (Admin)</option>
              <option value="staff" <?= $r === 'staff' ? 'selected' : '' ?>>เจ้าหน้าที่ (Staff)</option>
            </select>
            <?php if (isset($user['id']) && $user['id'] === 1): ?>
              <p class="text-xs text-red-500 mt-1">* แอดมินหลักต้องเป็น Admin เสมอ</p>
            <?php endif; ?>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
              สถานะการใช้งาน
            </label>
            <div class="flex items-center gap-3 mt-3">
              <label class="relative inline-flex items-center cursor-pointer">
                <?php $isActive = $_POST['is_active'] ?? $user['is_active'] ?? 1; ?>
                <input type="checkbox" name="is_active" value="1" class="sr-only peer" <?= $isActive ? 'checked' : '' ?> <?= (isset($user['id']) && $user['id'] === 1) ? 'disabled' : '' ?>>
                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-emerald-500"></div>
                <span class="ml-3 text-sm font-medium text-slate-700 dark:text-slate-300">เปิดใช้งาน (Active)</span>
              </label>
              <?php if (isset($user['id']) && $user['id'] === 1): ?>
                <input type="hidden" name="is_active" value="1">
              <?php endif; ?>
            </div>
          </div>
        </div>

      </div>
      
      <div class="p-6 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-3">
        <a href="?page=users" class="px-6 py-2.5 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors">
          ยกเลิก
        </a>
        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium shadow-lg shadow-indigo-500/30 transition-colors">
          <i class="fas fa-save mr-2"></i> บันทึกข้อมูล
        </button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
