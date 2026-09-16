<?php
$pageTitle = 'ยืม iPad';
require_once __DIR__ . '/../layout/kiosk_header.php';
$ipadModel = new Ipad($pdo);
$availableIpads = $ipadModel->getAll('', 'available');
?>

<div class="max-w-2xl mx-auto">
  <!-- Step Indicator -->
  <div class="flex items-center justify-center mb-8">
    <?php $steps = [['num'=>1,'label'=>'ข้อมูลผู้ยืม'],['num'=>2,'label'=>'สแกน iPad'],['num'=>3,'label'=>'ยืนยัน']]; ?>
    <?php foreach ($steps as $i => $step): ?>
      <div class="flex items-center <?= $i > 0 ? '' : '' ?>">
        <div class="flex flex-col items-center">
          <div id="step-circle-<?= $step['num'] ?>"
            class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold border-2 transition-all duration-300
            <?= $step['num'] === 1 ? 'bg-indigo-500 border-indigo-500 text-white' : 'bg-transparent border-slate-300 dark:border-slate-600 text-slate-400' ?>">
            <?= $step['num'] ?>
          </div>
          <span class="text-xs mt-1 font-medium text-slate-500 dark:text-slate-400"><?= $step['label'] ?></span>
        </div>
        <?php if ($i < count($steps)-1): ?>
        <div id="step-line-<?= $step['num'] ?>" class="w-20 md:w-32 h-0.5 mx-1 mb-5 bg-slate-200 dark:bg-slate-700 transition-all duration-500"></div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Step 1: Scan User -->
  <div id="step1" class="animate-fade-in">
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm mb-4">
      <h2 class="text-xl font-bold text-slate-800 dark:text-white mb-1 flex items-center gap-2">
        <span class="w-7 h-7 bg-indigo-500 text-white rounded-full text-sm flex items-center justify-center font-bold">1</span>
        ข้อมูลผู้ยืม
      </h2>
      <p class="text-slate-500 dark:text-slate-400 text-sm mb-5">กรุณากรอกข้อมูลผู้ยืมให้ครบถ้วน</p>

      <form id="userForm" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">เบอร์โทรศัพท์มือถือ</label>
          <input type="tel" id="userPhone" required placeholder="08xxxxxxxx"
            class="w-full border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 rounded-xl px-4 py-2 text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500 transition-all">
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">ชื่อ</label>
            <input type="text" id="userFirstName" required placeholder="ชื่อจริง"
              class="w-full border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 rounded-xl px-4 py-2 text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500 transition-all">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">นามสกุล</label>
            <input type="text" id="userLastName" required placeholder="นามสกุล"
              class="w-full border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 rounded-xl px-4 py-2 text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500 transition-all">
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">สถานะ</label>
          <div class="flex gap-4">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" name="userRole" value="student" checked class="text-indigo-500 focus:ring-indigo-500">
              <span class="text-slate-700 dark:text-slate-300">นักเรียน</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" name="userRole" value="teacher" class="text-indigo-500 focus:ring-indigo-500">
              <span class="text-slate-700 dark:text-slate-300">ครู</span>
            </label>
          </div>
        </div>
        <div class="pt-2">
          <button type="submit" class="w-full bg-indigo-500 hover:bg-indigo-600 text-white font-semibold py-2.5 rounded-xl transition-all shadow-md">
            ถัดไป <i class="fas fa-arrow-right ml-1"></i>
          </button>
        </div>
      </form>
    </div>

  </div>

  <!-- Step 2: Scan iPad -->
  <div id="step2" class="hidden animate-fade-in">
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm mb-4">
      <h2 class="text-xl font-bold text-slate-800 dark:text-white mb-1 flex items-center gap-2">
        <span class="w-7 h-7 bg-indigo-500 text-white rounded-full text-sm flex items-center justify-center font-bold">2</span>
        เลือก iPad
      </h2>
      <div class="flex items-center justify-between mb-5">
        <p class="text-slate-500 dark:text-slate-400 text-sm">เลือกเครื่องที่ต้องการยืมจากรายการด้านล่าง</p>
        <button onclick="resetAll()" class="text-xs text-indigo-500 hover:text-indigo-600 dark:text-indigo-400 font-semibold bg-indigo-50 dark:bg-indigo-900/30 px-3 py-1.5 rounded-lg transition-colors"><i class="fas fa-arrow-left mr-1"></i>กลับไปแก้ไขข้อมูล</button>
      </div>

      <form id="ipadForm" class="space-y-4">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 max-h-64 overflow-y-auto pr-2">
          <?php if (empty($availableIpads)): ?>
            <div class="col-span-full p-4 text-center text-slate-500 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-200 dark:border-slate-700">
              ไม่มี iPad ว่างให้ยืมในขณะนี้
            </div>
          <?php else: ?>
            <?php foreach ($availableIpads as $ipad): ?>
              <label class="relative flex items-start gap-2 p-2 border-2 border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:border-indigo-500 dark:hover:border-indigo-500 transition-all [&:has(input:checked)]:border-indigo-500 [&:has(input:checked)]:bg-indigo-50 dark:[&:has(input:checked)]:bg-indigo-900/20">
                <div class="pt-0.5">
                  <input type="checkbox" name="selectedIpads[]" value="<?= htmlspecialchars(json_encode($ipad)) ?>" class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                </div>
                <div class="flex-1 min-w-0">
                  <div class="font-bold text-sm text-slate-800 dark:text-white truncate" title="<?= htmlspecialchars($ipad['device_code']) ?>"><?= htmlspecialchars($ipad['device_code']) ?></div>
                  <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5 truncate"><?= htmlspecialchars($ipad['device_name']) ?></div>
                </div>
              </label>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <div class="pt-2">
          <button type="submit" class="w-full bg-indigo-500 hover:bg-indigo-600 text-white font-semibold py-2.5 rounded-xl transition-all shadow-md" <?= empty($availableIpads) ? 'disabled' : '' ?>>
            ถัดไป <i class="fas fa-arrow-right ml-1"></i>
          </button>
        </div>
      </form>
    </div>

    <!-- iPad Info Card was here, but removed since we select directly -->
  </div>

  <!-- Step 3: Confirm -->
  <div id="step3" class="hidden animate-fade-in">
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm">
      <h2 class="text-xl font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
        <span class="w-7 h-7 bg-indigo-500 text-white rounded-full text-sm flex items-center justify-center font-bold">3</span>
        ยืนยันการยืม
      </h2>

      <!-- Summary -->
      <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4 mb-5 space-y-3">
        <div class="flex items-center justify-between">
          <span class="text-slate-500 dark:text-slate-400 text-sm">ผู้ยืม</span>
          <span class="font-semibold text-slate-800 dark:text-white" id="confirmUser"></span>
        </div>
        <div class="border-t border-slate-200 dark:border-slate-600 pt-3 mt-3">
          <div class="flex items-center justify-between mb-2">
            <span class="text-slate-500 dark:text-slate-400 text-sm">iPad ที่ยืม</span>
            <span class="font-semibold text-indigo-600 dark:text-indigo-400 text-sm" id="confirmIpadCount"></span>
          </div>
          <ul id="confirmIpadList" class="space-y-1.5 max-h-40 overflow-y-auto"></ul>
        </div>
        <div class="border-t border-slate-200 dark:border-slate-600 pt-3">
          <label class="text-slate-500 dark:text-slate-400 text-sm font-medium block mb-2">
            <i class="fas fa-calendar-alt text-indigo-400 mr-1"></i>กำหนดคืน <span class="text-red-500">*</span>
          </label>
          <input type="datetime-local" id="dueDate"
            class="w-full border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 rounded-xl px-4 py-2.5 text-slate-800 dark:text-white focus:outline-none focus:border-indigo-400 transition-all">
        </div>
        <div>
          <label class="text-slate-500 dark:text-slate-400 text-sm font-medium block mb-2">
            <i class="fas fa-sticky-note text-indigo-400 mr-1"></i>หมายเหตุ (ไม่บังคับ)
          </label>
          <textarea id="borrowNotes" rows="2" placeholder="หมายเหตุเพิ่มเติม..."
            class="w-full border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 rounded-xl px-4 py-2.5 text-slate-800 dark:text-white focus:outline-none focus:border-indigo-400 transition-all resize-none"></textarea>
        </div>
      </div>

      <input type="hidden" id="borrowUserId">
      <div class="flex gap-3">
        <button onclick="goBackToStep2()"
          class="flex-1 py-3 rounded-xl border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition-all">
          <i class="fas fa-arrow-left mr-2"></i>ย้อนกลับ
        </button>
        <button id="confirmBorrowBtn" onclick="confirmBorrow()"
          class="flex-1 bg-gradient-to-r from-indigo-500 to-purple-600 text-white py-3 rounded-xl font-bold hover:opacity-90 transition-all hover:shadow-lg hover:shadow-indigo-500/30">
          <i class="fas fa-check mr-2"></i>ยืนยันการยืม
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Camera Scanner Modal -->
<div id="cameraModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/70 p-4">
  <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 w-full max-w-sm shadow-2xl">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-bold text-slate-800 dark:text-white"><i class="fas fa-camera mr-2 text-indigo-500"></i>สแกนด้วยกล้อง</h3>
      <button onclick="closeCameraScanner()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
        <i class="fas fa-times text-xl"></i>
      </button>
    </div>
    <div id="cameraReader" class="rounded-xl overflow-hidden bg-black"></div>
    <p class="text-sm text-slate-400 text-center mt-3">จ่อกล้องไปที่ Barcode หรือ QR Code</p>
  </div>
</div>

<script>
let userData = null;
let ipadData = null;
let currentScanTarget = 'user';
let html5QrCode = null;

// Focus phone on load
document.getElementById('userPhone').focus();

// Handle User Form Submit
document.getElementById('userForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const phone = document.getElementById('userPhone').value.trim();
  const firstName = document.getElementById('userFirstName').value.trim();
  const lastName = document.getElementById('userLastName').value.trim();
  const role = document.querySelector('input[name="userRole"]:checked').value;

  showLoader(true);
  fetch('api/find_or_create_user.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: `phone=${encodeURIComponent(phone)}&first_name=${encodeURIComponent(firstName)}&last_name=${encodeURIComponent(lastName)}&role=${encodeURIComponent(role)}`
  })
  .then(r => r.json())
  .then(data => {
    showLoader(false);
    if (data.success) {
      if (data.unreturned_count > 0) {
        Swal.fire({
          icon: 'error',
          title: 'ไม่สามารถยืมได้',
          text: `คุณมี iPad ที่ยังไม่ได้ส่งคืนจำนวน ${data.unreturned_count} เครื่อง กรุณาส่งคืนก่อนยืมใหม่`,
          confirmButtonColor: '#6366f1'
        });
        return;
      }
      
      userData = data.user;
      
      // We don't display user info card in step 1 anymore since it's a form.
      // We just transition to step 2 immediately.
      
      document.getElementById('borrowUserId').value = userData.id;
      goToStep2();
    } else {
      Swal.fire({icon: 'error', title: 'เกิดข้อผิดพลาด', text: data.message, confirmButtonColor: '#6366f1'});
    }
  })
  .catch(() => {
    showLoader(false);
    Swal.fire({icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'ไม่สามารถเชื่อมต่อได้', confirmButtonColor: '#6366f1'});
  });
});

// Handle iPad Selection Submit
document.getElementById('ipadForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const selectedCheckboxes = document.querySelectorAll('input[name="selectedIpads[]"]:checked');
  if (selectedCheckboxes.length === 0) {
    Swal.fire({icon: 'warning', title: 'กรุณาเลือก iPad อย่างน้อย 1 เครื่อง', confirmButtonColor: '#6366f1'});
    return;
  }
  
  ipadData = Array.from(selectedCheckboxes).map(cb => JSON.parse(cb.value));
  goToStep3();
});

function goToStep2() {
  setStepActive(2);
  document.getElementById('step1').classList.add('hidden');
  document.getElementById('step2').classList.remove('hidden');
}

function goToStep3() {
  setStepActive(3);
  document.getElementById('step2').classList.add('hidden');
  document.getElementById('step3').classList.remove('hidden');
  document.getElementById('confirmUser').textContent = userData.full_name + ' (' + userData.role_label + ')';
  
  const listEl = document.getElementById('confirmIpadList');
  listEl.innerHTML = '';
  ipadData.forEach(ipad => {
    const li = document.createElement('li');
    li.className = 'flex justify-between items-center bg-white dark:bg-slate-800 p-2 rounded-lg border border-slate-200 dark:border-slate-700';
    li.innerHTML = `
      <span class="font-semibold text-sm text-slate-800 dark:text-white">${ipad.device_code}</span>
      <span class="text-xs text-slate-500 dark:text-slate-400">${ipad.device_name}</span>
    `;
    listEl.appendChild(li);
  });
  document.getElementById('confirmIpadCount').textContent = ipadData.length + ' เครื่อง';

  // Default due date: today 16:00
  const now = new Date();
  now.setHours(16, 0, 0, 0);
  if (now < new Date()) now.setDate(now.getDate() + 1);
  const pad = n => String(n).padStart(2,'0');
  document.getElementById('dueDate').value = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}T16:00`;
  document.getElementById('dueDate').min = new Date().toISOString().slice(0,16);
}

function confirmBorrow() {
  const dueDate = document.getElementById('dueDate').value;
  const notes = document.getElementById('borrowNotes').value;
  if (!dueDate) { Swal.fire({icon:'warning',title:'กรุณาระบุกำหนดคืน',confirmButtonColor:'#6366f1'}); return; }

  Swal.fire({
    title: 'ยืนยันการยืม?',
    html: `<b>${userData.full_name}</b> ต้องการยืม iPad จำนวน <b>${ipadData.length}</b> เครื่อง`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: '<i class="fas fa-check mr-1"></i>ยืนยัน',
    cancelButtonText: 'ยกเลิก',
    confirmButtonColor: '#6366f1',
    background: isDark() ? '#1e293b' : '#fff',
    color: isDark() ? '#f1f5f9' : '#1e293b',
  }).then(result => {
    if (!result.isConfirmed) return;

    const btn = document.getElementById('confirmBorrowBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>กำลังบันทึก...';

    fetch('api/borrow.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({
        user_id: userData.id,
        ipad_ids: ipadData.map(i => i.id),
        due_date: dueDate,
        notes: notes
      })
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        Swal.fire({
          icon: 'success',
          title: '🎉 ยืม iPad สำเร็จ!',
          html: `<b>${userData.full_name}</b> ยืม iPad จำนวน <b>${ipadData.length}</b> เครื่อง<br><small class="text-gray-500">กำหนดคืน: ${data.due_date}</small>`,
          confirmButtonText: 'รับทราบ',
          confirmButtonColor: '#6366f1',
          background: isDark() ? '#1e293b' : '#fff',
          color: isDark() ? '#f1f5f9' : '#1e293b',
        }).then(() => location.reload());
      } else {
        Swal.fire({icon:'error',title:'เกิดข้อผิดพลาด',text:data.message,confirmButtonColor:'#6366f1',
          background: isDark() ? '#1e293b' : '#fff', color: isDark() ? '#f1f5f9' : '#1e293b'});
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check mr-2"></i>ยืนยันการยืม';
      }
    });
  });
}

function resetAll() {
  userData = null;
  ipadData = null;
  document.getElementById('userPhone').value = '';
  document.getElementById('userFirstName').value = '';
  document.getElementById('userLastName').value = '';
  const selectedCheckboxes = document.querySelectorAll('input[name="selectedIpads[]"]:checked');
  selectedCheckboxes.forEach(cb => cb.checked = false);
  document.getElementById('step2').classList.add('hidden');
  document.getElementById('step3').classList.add('hidden');
  document.getElementById('step1').classList.remove('hidden');
  setStepActive(1);
  document.getElementById('userPhone').focus();
  const btn = document.getElementById('confirmBorrowBtn');
  btn.disabled = false;
  btn.innerHTML = '<i class="fas fa-check mr-2"></i>ยืนยันการยืม';
}

function goBackToStep2() {
  document.getElementById('step3').classList.add('hidden');
  document.getElementById('step2').classList.remove('hidden');
  setStepActive(2);
}

function setStepActive(n) {
  for (let i = 1; i <= 3; i++) {
    const c = document.getElementById('step-circle-'+i);
    if (i < n) {
      c.className = c.className.replace('bg-transparent border-slate-300 dark:border-slate-600 text-slate-400', '');
      c.classList.add('bg-emerald-500','border-emerald-500','text-white');
      c.innerHTML = '<i class="fas fa-check text-xs"></i>';
    } else if (i === n) {
      c.classList.remove('bg-emerald-500','border-emerald-500');
      c.classList.remove('bg-transparent','border-slate-300','text-slate-400');
      c.classList.add('bg-indigo-500','border-indigo-500','text-white');
      c.textContent = i;
    } else {
      c.classList.remove('bg-indigo-500','border-indigo-500','bg-emerald-500','border-emerald-500');
      c.classList.add('bg-transparent','border-slate-300','dark:border-slate-600','text-slate-400');
      c.textContent = i;
    }
    if (i < 3) {
      const line = document.getElementById('step-line-'+i);
      line.style.background = i < n ? '#6366f1' : '';
    }
  }
}

function openCameraScanner(target) {
  currentScanTarget = target;
  document.getElementById('cameraModal').classList.remove('hidden');
  html5QrCode = new Html5Qrcode('cameraReader');
  html5QrCode.start({ facingMode: 'environment' },
    { fps: 10, qrbox: { width: 250, height: 150 } },
    (decodedText) => {
      closeCameraScanner();
      if (currentScanTarget === 'ipad') {
        document.getElementById('ipadBarcode').value = decodedText;
        scanIpad(decodedText);
      }
    }, () => {}
  ).catch(err => {
    closeCameraScanner();
    Swal.fire({icon:'error',title:'ไม่สามารถเปิดกล้องได้',text:'กรุณาอนุญาตการใช้กล้อง',confirmButtonColor:'#6366f1'});
  });
}

function closeCameraScanner() {
  document.getElementById('cameraModal').classList.add('hidden');
  if (html5QrCode) html5QrCode.stop().catch(() => {});
}

function showLoader(show) {
  const loader = document.getElementById('pageLoader');
  if (loader) loader.classList.toggle('hidden', !show);
}

function isDark() {
  return document.documentElement.classList.contains('dark');
}
</script>

<?php require_once __DIR__ . '/../layout/kiosk_footer.php'; ?>
