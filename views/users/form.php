<?php
$isEdit     = ($action ?? 'create') === 'edit';
$pageTitle  = $isEdit ? 'แก้ไขผู้ใช้' : 'เพิ่มผู้ใช้';
$currentData= $data ?? [];
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';
?>

<div class="max-w-2xl mx-auto">
  <a href="?page=users" class="inline-flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 mb-6 transition-colors">
    <i class="fas fa-arrow-left"></i>กลับรายการผู้ใช้
  </a>

  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
    <h2 class="text-xl font-bold text-slate-800 dark:text-white mb-6 flex items-center gap-2">
      <div class="w-9 h-9 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
        <i class="fas <?= $isEdit ? 'fa-edit' : 'fa-user-plus' ?> text-white text-sm"></i>
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

    <form method="POST" enctype="multipart/form-data" id="userForm">
      <!-- Avatar Upload -->
      <div class="flex items-center gap-5 mb-6 p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
        <div class="relative">
          <?php $av = getAvatarUrl($currentData['avatar'] ?? null); ?>
          <img id="avatarPreview" src="<?= $av ?>" alt="" class="w-20 h-20 rounded-xl object-cover border-2 border-white dark:border-slate-600 shadow-md">
          <label for="avatarInput" class="absolute -bottom-1 -right-1 w-7 h-7 bg-indigo-500 hover:bg-indigo-600 rounded-full flex items-center justify-center cursor-pointer shadow-md transition-colors">
            <i class="fas fa-camera text-white text-xs"></i>
          </label>
          <input type="file" id="avatarInput" name="avatar" accept="image/*" class="hidden" onchange="previewAvatar(this)">
        </div>
        <div>
          <p class="font-semibold text-slate-800 dark:text-white">รูปภาพผู้ใช้</p>
          <p class="text-sm text-slate-400">JPG, PNG, WEBP (max 2MB)</p>
          <button type="button" onclick="document.getElementById('avatarInput').click()"
            class="text-sm text-indigo-500 hover:text-indigo-700 underline mt-1">เปลี่ยนรูป</button>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 block mb-1.5">รหัสผู้ใช้ <span class="text-red-500">*</span></label>
          <input type="text" name="user_code" value="<?= sanitize($currentData['user_code'] ?? '') ?>"
            class="field" placeholder="เช่น T001 หรือ 64001" required>
        </div>
        <div>
          <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 block mb-1.5">Barcode ID <span class="text-red-500">*</span></label>
          <div class="relative">
            <input type="text" name="barcode_id" id="barcodeField" value="<?= sanitize($currentData['barcode_id'] ?? '') ?>"
              class="field pr-12" placeholder="Barcode บัตร" required>
            <button type="button" onclick="openBarcodeCamera()" class="absolute right-3 top-1/2 -translate-y-1/2 text-indigo-400 hover:text-indigo-600 transition-colors">
              <i class="fas fa-camera"></i>
            </button>
          </div>
        </div>
        <div>
          <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 block mb-1.5">ชื่อ <span class="text-red-500">*</span></label>
          <input type="text" name="first_name" value="<?= sanitize($currentData['first_name'] ?? '') ?>" class="field" placeholder="ชื่อจริง" required>
        </div>
        <div>
          <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 block mb-1.5">นามสกุล <span class="text-red-500">*</span></label>
          <input type="text" name="last_name" value="<?= sanitize($currentData['last_name'] ?? '') ?>" class="field" placeholder="นามสกุล" required>
        </div>
        <div>
          <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 block mb-1.5">ชั้น/ตำแหน่ง</label>
          <input type="text" name="class_position" value="<?= sanitize($currentData['class_position'] ?? '') ?>" class="field" placeholder="เช่น ม.4/1 หรือ ครูวิทยาศาสตร์">
        </div>
        <div>
          <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 block mb-1.5">ประเภท</label>
          <select name="role" class="field">
            <?php foreach (['student'=>'นักเรียน','teacher'=>'ครู','staff'=>'เจ้าหน้าที่'] as $val => $lbl): ?>
            <option value="<?= $val ?>" <?= ($currentData['role'] ?? 'student') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php if ($isEdit): ?>
        <div class="md:col-span-2">
          <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 block mb-1.5">สถานะ</label>
          <select name="is_active" class="field">
            <option value="1" <?= ($currentData['is_active'] ?? 1) ? 'selected' : '' ?>>ใช้งาน</option>
            <option value="0" <?= !($currentData['is_active'] ?? 1) ? 'selected' : '' ?>>ระงับการใช้งาน</option>
          </select>
        </div>
        <?php endif; ?>
      </div>

      <div class="flex gap-3 mt-6">
        <a href="?page=users" class="flex-1 py-3 rounded-xl border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition-all text-center">
          ยกเลิก
        </a>
        <button type="submit" class="flex-1 bg-gradient-to-r from-indigo-500 to-purple-600 text-white py-3 rounded-xl font-bold hover:opacity-90 transition-all shadow-md hover:shadow-indigo-500/30">
          <i class="fas <?= $isEdit ? 'fa-save' : 'fa-user-plus' ?> mr-2"></i>
          <?= $isEdit ? 'บันทึกการแก้ไข' : 'เพิ่มผู้ใช้' ?>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Camera for barcode -->
<div id="barcodeCamera" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/70 p-4">
  <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 w-full max-w-sm shadow-2xl">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-bold text-slate-800 dark:text-white"><i class="fas fa-camera mr-2 text-indigo-500"></i>สแกน Barcode</h3>
      <button onclick="closeBarcodeCamera()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><i class="fas fa-times text-xl"></i></button>
    </div>
    <div id="barcodeCameraReader" class="rounded-xl overflow-hidden bg-black"></div>
  </div>
</div>

<style>
.field {
  @apply w-full border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700/50 rounded-xl px-4 py-2.5 text-slate-800 dark:text-white focus:outline-none focus:border-indigo-400 transition-all text-sm;
}
</style>

<script>
function previewAvatar(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => { document.getElementById('avatarPreview').src = e.target.result; };
    reader.readAsDataURL(input.files[0]);
  }
}

let barcodeCameraHtml5 = null;
function openBarcodeCamera() {
  document.getElementById('barcodeCamera').classList.remove('hidden');
  barcodeCameraHtml5 = new Html5Qrcode('barcodeCameraReader');
  barcodeCameraHtml5.start({facingMode:'environment'},{fps:10,qrbox:{width:250,height:120}},
    (text) => { closeBarcodeCamera(); document.getElementById('barcodeField').value = text; },
    () => {}
  ).catch(() => closeBarcodeCamera());
}
function closeBarcodeCamera() {
  document.getElementById('barcodeCamera').classList.add('hidden');
  if (barcodeCameraHtml5) barcodeCameraHtml5.stop().catch(() => {});
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
