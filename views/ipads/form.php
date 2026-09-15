<?php
$isEdit     = ($action ?? 'create') === 'edit';
$pageTitle  = $isEdit ? 'แก้ไข iPad' : 'เพิ่ม iPad';
$currentData= $data ?? [];
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';
?>

<div class="max-w-2xl mx-auto">
  <a href="?page=ipads" class="inline-flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 mb-6 transition-colors">
    <i class="fas fa-arrow-left"></i>กลับรายการ iPad
  </a>

  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
    <h2 class="text-xl font-bold text-slate-800 dark:text-white mb-6 flex items-center gap-2">
      <div class="w-9 h-9 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
        <i class="fas fa-tablet-alt text-white text-sm"></i>
      </div>
      <?= $pageTitle ?>
    </h2>

    <?php if (!empty($errors)): ?>
    <div class="mb-5 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-xl">
      <p class="font-semibold text-red-700 dark:text-red-400 mb-2"><i class="fas fa-exclamation-circle mr-1"></i>กรุณาตรวจสอบ</p>
      <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-300 space-y-1">
        <?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>

    <form method="POST">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 block mb-1.5">รหัสเครื่อง <span class="text-red-500">*</span></label>
          <input type="text" name="device_code" value="<?= sanitize($currentData['device_code'] ?? '') ?>"
            class="w-full border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700/50 rounded-xl px-4 py-2.5 text-slate-800 dark:text-white focus:outline-none focus:border-indigo-400 transition-all text-sm"
            placeholder="เช่น IPD-001" required>
        </div>
        <div>
          <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 block mb-1.5">ชื่อเครื่อง <span class="text-red-500">*</span></label>
          <input type="text" name="device_name" value="<?= sanitize($currentData['device_name'] ?? '') ?>"
            class="w-full border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700/50 rounded-xl px-4 py-2.5 text-slate-800 dark:text-white focus:outline-none focus:border-indigo-400 transition-all text-sm"
            placeholder="เช่น iPad หมายเลข 1" required>
        </div>
        <div>
          <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 block mb-1.5">Barcode <span class="text-red-500">*</span></label>
          <input type="text" name="barcode" value="<?= sanitize($currentData['barcode'] ?? '') ?>"
            class="w-full border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700/50 rounded-xl px-4 py-2.5 text-slate-800 dark:text-white focus:outline-none focus:border-indigo-400 transition-all text-sm font-mono"
            placeholder="เช่น IPAD-001" required>
        </div>
        <div>
          <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 block mb-1.5">Serial Number <span class="text-red-500">*</span></label>
          <input type="text" name="serial_number" value="<?= sanitize($currentData['serial_number'] ?? '') ?>"
            class="w-full border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700/50 rounded-xl px-4 py-2.5 text-slate-800 dark:text-white focus:outline-none focus:border-indigo-400 transition-all text-sm font-mono"
            placeholder="Serial Number" required>
        </div>
        <div>
          <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 block mb-1.5">รุ่น <span class="text-red-500">*</span></label>
          <input type="text" name="model" value="<?= sanitize($currentData['model'] ?? '') ?>"
            list="modelSuggestions"
            class="w-full border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700/50 rounded-xl px-4 py-2.5 text-slate-800 dark:text-white focus:outline-none focus:border-indigo-400 transition-all text-sm"
            placeholder="เช่น iPad 10th Gen" required>
          <datalist id="modelSuggestions">
            <option value="iPad 9th Gen (Wi-Fi 64GB)">
            <option value="iPad 10th Gen (Wi-Fi 64GB)">
            <option value="iPad Air M1 (Wi-Fi 256GB)">
            <option value="iPad Air M2 (Wi-Fi 256GB)">
            <option value="iPad Pro 11&quot; M2">
            <option value="iPad Pro 12.9&quot; M2">
            <option value="iPad mini 6 (Wi-Fi 64GB)">
          </datalist>
        </div>
        <div>
          <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 block mb-1.5">สถานะ</label>
          <select name="status" class="w-full border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700/50 rounded-xl px-4 py-2.5 text-slate-800 dark:text-white focus:outline-none focus:border-indigo-400 transition-all text-sm">
            <?php foreach (['available'=>'พร้อมใช้งาน','maintenance'=>'ซ่อมบำรุง','disabled'=>'ปิดใช้งาน'] as $v => $l): ?>
            <option value="<?= $v ?>" <?= ($currentData['status'] ?? 'available') === $v ? 'selected' : '' ?>><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="md:col-span-2">
          <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 block mb-1.5">หมายเหตุ</label>
          <textarea name="notes" rows="2" placeholder="หมายเหตุเพิ่มเติม..."
            class="w-full border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700/50 rounded-xl px-4 py-2.5 text-slate-800 dark:text-white focus:outline-none focus:border-indigo-400 transition-all text-sm resize-none"><?= sanitize($currentData['notes'] ?? '') ?></textarea>
        </div>
      </div>

      <div class="flex gap-3 mt-6">
        <a href="?page=ipads" class="flex-1 py-3 rounded-xl border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition-all text-center">
          ยกเลิก
        </a>
        <button type="submit" class="flex-1 bg-gradient-to-r from-indigo-500 to-purple-600 text-white py-3 rounded-xl font-bold hover:opacity-90 transition-all shadow-md hover:shadow-indigo-500/30">
          <i class="fas <?= $isEdit ? 'fa-save' : 'fa-tablet-alt' ?> mr-2"></i>
          <?= $isEdit ? 'บันทึกการแก้ไข' : 'เพิ่ม iPad' ?>
        </button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
