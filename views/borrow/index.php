<?php
$pageTitle = 'ยืม iPad';
require_once __DIR__ . '/../layout/kiosk_header.php';
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
        สแกน iPad
      </h2>
      <div class="flex items-center justify-between mb-5">
        <p class="text-slate-500 dark:text-slate-400 text-sm">สแกน Barcode ที่ติดอยู่บน iPad</p>
        <button onclick="resetAll()" class="text-xs text-indigo-500 hover:text-indigo-600 dark:text-indigo-400 font-semibold bg-indigo-50 dark:bg-indigo-900/30 px-3 py-1.5 rounded-lg transition-colors"><i class="fas fa-arrow-left mr-1"></i>กลับไปแก้ไขข้อมูล</button>
      </div>

      <div class="flex gap-2">
        <div class="flex-1 relative">
          <input type="text" id="ipadBarcode" placeholder="สแกน Barcode iPad..."
            class="w-full border-2 border-purple-200 dark:border-purple-700 bg-purple-50 dark:bg-purple-950/30
                   rounded-xl px-4 py-3 pl-11 text-slate-800 dark:text-white
                   focus:outline-none focus:border-purple-500 transition-all text-base"
            autocomplete="off">
          <i class="fas fa-tablet-alt absolute left-4 top-1/2 -translate-y-1/2 text-purple-400 text-lg"></i>
        </div>
        <button onclick="openCameraScanner('ipad')" title="สแกนด้วยกล้อง"
          class="px-4 rounded-xl bg-purple-100 dark:bg-purple-900/50 text-purple-600 dark:text-purple-300 hover:bg-purple-200 dark:hover:bg-purple-800/50 transition-all border border-purple-200 dark:border-purple-700">
          <i class="fas fa-camera text-xl"></i>
        </button>
      </div>
    </div>

    <!-- iPad Info Card -->
    <div id="ipadInfoCard" class="hidden animate-bounce-in">
      <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border-2 border-purple-200 dark:border-purple-700 shadow-sm">
        <div class="flex items-center gap-4">
          <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-md flex-shrink-0">
            <i class="fas fa-tablet-alt text-white text-2xl"></i>
          </div>
          <div class="flex-1">
            <div class="flex items-center gap-2 mb-1">
              <span class="px-2.5 py-0.5 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-xs font-bold rounded-full">✓ พร้อมใช้งาน</span>
            </div>
            <p class="font-bold text-slate-800 dark:text-white text-lg" id="ipadName"></p>
            <p class="text-slate-500 dark:text-slate-400 text-sm" id="ipadModel"></p>
            <p class="text-slate-400 text-xs mt-0.5">S/N: <span id="ipadSerial"></span></p>
          </div>
          <input type="hidden" id="ipadId">
        </div>
      </div>
    </div>
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
        <div class="flex items-center justify-between">
          <span class="text-slate-500 dark:text-slate-400 text-sm">iPad ที่ยืม</span>
          <span class="font-semibold text-slate-800 dark:text-white" id="confirmIpad"></span>
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
      userData = data.user;
      
      // We don't display user info card in step 1 anymore since it's a form.
      // We just transition to step 2 immediately.
      
      document.getElementById('borrowUserId').value = userData.id;

      // Update Step Indicators
      document.getElementById('step1').classList.add('hidden');
      document.getElementById('step2').classList.remove('hidden');
      document.getElementById('step-circle-1').classList.replace('bg-indigo-500', 'bg-emerald-500');
      document.getElementById('step-circle-1').classList.replace('border-indigo-500', 'border-emerald-500');
      document.getElementById('step-circle-1').innerHTML = '<i class="fas fa-check"></i>';
      document.getElementById('step-line-1').classList.replace('bg-slate-200', 'bg-emerald-500');
      document.getElementById('step-circle-2').classList.replace('bg-transparent', 'bg-indigo-500');
      document.getElementById('step-circle-2').classList.replace('border-slate-300', 'border-indigo-500');
      document.getElementById('step-circle-2').classList.replace('text-slate-400', 'text-white');
      
      setTimeout(() => document.getElementById('ipadBarcode').focus(), 100);
    } else {
      Swal.fire({icon: 'error', title: 'ข้อผิดพลาด', text: data.message});
    }
  })
  .catch(err => {
    showLoader(false);
    Swal.fire({icon: 'error', title: 'ข้อผิดพลาด', text: 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้'});
  });
});

// iPad barcode scan
document.getElementById('ipadBarcode').addEventListener('keydown', function(e) {
  if (e.key === 'Enter' && this.value.trim()) {
    e.preventDefault();
    scanIpad(this.value.trim());
  }
});



function goToStep2() {
  setStepActive(2);
  document.getElementById('step1').classList.add('hidden');
  document.getElementById('step2').classList.remove('hidden');
  setTimeout(() => document.getElementById('ipadBarcode').focus(), 100);
}

function scanIpad(barcode) {
  showLoader(true);
  fetch('api/scan_ipad.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: 'barcode=' + encodeURIComponent(barcode)
  })
  .then(r => r.json())
  .then(data => {
    showLoader(false);
    if (data.success) {
      ipadData = data.ipad;
      document.getElementById('ipadName').textContent = data.ipad.device_name;
      document.getElementById('ipadModel').textContent = data.ipad.model;
      document.getElementById('ipadSerial').textContent = data.ipad.serial_number;
      document.getElementById('ipadId').value = data.ipad.id;
      document.getElementById('ipadInfoCard').classList.remove('hidden');
      setTimeout(() => goToStep3(), 600);
    } else if (data.blocked) {
      let msg = data.message;
      if (data.borrower) {
        msg += `\n\nผู้ยืม: ${data.borrower.name}\nยืมเมื่อ: ${data.borrower.borrowed_at}`;
        if (data.borrower.is_overdue) msg += '\n⚠️ เกินกำหนดคืนแล้ว!';
      }
      Swal.fire({ icon:'warning', title:'ไม่สามารถยืมได้', text: msg.trim(),
        confirmButtonColor:'#6366f1', background: isDark() ? '#1e293b' : '#fff', color: isDark() ? '#f1f5f9' : '#1e293b' });
      document.getElementById('ipadBarcode').value = '';
      document.getElementById('ipadBarcode').focus();
    } else {
      Swal.fire({ icon:'error', title:'ไม่พบ iPad', text: data.message,
        confirmButtonColor:'#6366f1', background: isDark() ? '#1e293b' : '#fff', color: isDark() ? '#f1f5f9' : '#1e293b' });
      document.getElementById('ipadBarcode').value = '';
      document.getElementById('ipadBarcode').focus();
    }
  })
  .catch(() => { showLoader(false); Swal.fire({icon:'error',title:'เกิดข้อผิดพลาด',text:'ไม่สามารถเชื่อมต่อได้',confirmButtonColor:'#6366f1'}); });
}

function goToStep3() {
  setStepActive(3);
  document.getElementById('step2').classList.add('hidden');
  document.getElementById('step3').classList.remove('hidden');
  document.getElementById('confirmUser').textContent = userData.full_name + ' (' + userData.role_label + ')';
  document.getElementById('confirmIpad').textContent = ipadData.device_name + ' - ' + ipadData.model;
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
    html: `<b>${userData.full_name}</b> ยืม <b>${ipadData.device_name}</b>`,
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
      headers: {'Content-Type':'application/x-www-form-urlencoded'},
      body: new URLSearchParams({
        user_id: userData.id,
        ipad_id: ipadData.id,
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
          html: `<b>${userData.full_name}</b> ยืม <b>${ipadData.device_name}</b><br><small class="text-gray-500">กำหนดคืน: ${data.due_date}</small>`,
          confirmButtonText: 'ยืมเครื่องถัดไป',
          confirmButtonColor: '#6366f1',
          background: isDark() ? '#1e293b' : '#fff',
          color: isDark() ? '#f1f5f9' : '#1e293b',
        }).then(() => resetAll());
      } else {
        Swal.fire({icon:'error',title:'เกิดข้อผิดพลาด',text:data.message,confirmButtonColor:'#6366f1',
          background: isDark() ? '#1e293b' : '#fff', color: isDark() ? '#f1f5f9' : '#1e293b'});
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check mr-2"></i>ยืนยันการยืม';
      }
    });
  });
}

function resetStep1() {
  userData = null;
  document.getElementById('userBarcode').value = '';
  document.getElementById('userInfoCard').classList.add('hidden');
  document.getElementById('userBarcode').focus();
}

function resetAll() {
  userData = null;
  ipadData = null;
  document.getElementById('userBarcode').value = '';
  document.getElementById('ipadBarcode').value = '';
  document.getElementById('userInfoCard').classList.add('hidden');
  document.getElementById('ipadInfoCard').classList.add('hidden');
  document.getElementById('step2').classList.add('hidden');
  document.getElementById('step3').classList.add('hidden');
  document.getElementById('step1').classList.remove('hidden');
  setStepActive(1);
  document.getElementById('userBarcode').focus();
  const btn = document.getElementById('confirmBorrowBtn');
  btn.disabled = false;
  btn.innerHTML = '<i class="fas fa-check mr-2"></i>ยืนยันการยืม';
}

function goBackToStep2() {
  document.getElementById('step3').classList.add('hidden');
  document.getElementById('step2').classList.remove('hidden');
  setStepActive(2);
  document.getElementById('ipadBarcode').focus();
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
      if (currentScanTarget === 'user') {
        document.getElementById('userBarcode').value = decodedText;
        scanUser(decodedText);
      } else {
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
  document.getElementById('pageLoader').classList.toggle('hidden', !show);
}

function isDark() {
  return document.documentElement.classList.contains('dark');
}
</script>

<?php require_once __DIR__ . '/../layout/kiosk_footer.php'; ?>
