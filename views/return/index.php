<?php
$pageTitle = 'คืน iPad';
require_once __DIR__ . '/../layout/kiosk_header.php';
?>

<div class="max-w-2xl mx-auto">
  <div class="grid gap-4">
    <!-- Scan iPad -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm">
      <h2 class="text-xl font-bold text-slate-800 dark:text-white mb-1 flex items-center gap-2">
        <div class="w-9 h-9 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center">
          <i class="fas fa-tablet-alt text-white"></i>
        </div>
        สแกน iPad ที่ต้องการคืน
      </h2>
      <p class="text-slate-500 dark:text-slate-400 text-sm mb-5">สแกน Barcode ที่ด้านหลังเครื่อง iPad</p>

      <div class="flex gap-2">
        <div class="flex-1 relative">
          <input type="text" id="returnIpadBarcode" placeholder="สแกน Barcode iPad..."
            class="w-full border-2 border-emerald-200 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-950/30
                   rounded-xl px-4 py-3 pl-11 text-slate-800 dark:text-white
                   focus:outline-none focus:border-emerald-500 transition-all text-base"
            autofocus autocomplete="off">
          <i class="fas fa-barcode absolute left-4 top-1/2 -translate-y-1/2 text-emerald-500 text-lg"></i>
        </div>
        <button onclick="openReturnCamera()" title="สแกนด้วยกล้อง"
          class="px-4 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-300 hover:bg-emerald-200 dark:hover:bg-emerald-800/50 transition-all border border-emerald-200 dark:border-emerald-700">
          <i class="fas fa-camera text-xl"></i>
        </button>
      </div>
    </div>

    <!-- Return Info Card -->
    <div id="returnInfoCard" class="hidden animate-bounce-in">
      <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-emerald-500 to-teal-600 p-5">
          <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center">
              <i class="fas fa-tablet-alt text-white text-2xl"></i>
            </div>
            <div>
              <p class="text-white/80 text-sm">iPad ที่กำลังคืน</p>
              <p class="text-white text-xl font-bold" id="returnIpadName"></p>
              <p class="text-white/70 text-sm" id="returnIpadModel"></p>
            </div>
          </div>
        </div>

        <!-- Borrower Info -->
        <div class="p-5">
          <h3 class="font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
            <i class="fas fa-user-circle text-indigo-500"></i> ข้อมูลผู้ยืม
          </h3>
          <div class="flex items-center gap-4 mb-5">
            <img id="borrowerAvatar" src="" alt="" class="w-16 h-16 rounded-xl object-cover flex-shrink-0">
            <div>
              <p class="text-xl font-bold text-slate-800 dark:text-white" id="borrowerName"></p>
              <p class="text-slate-500 dark:text-slate-400" id="borrowerClass"></p>
              <p class="text-slate-400 text-sm" id="borrowerCode"></p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3 mb-5">
            <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-3">
              <p class="text-xs text-slate-400 mb-1"><i class="fas fa-clock mr-1"></i>เวลายืม</p>
              <p class="font-semibold text-slate-800 dark:text-white text-sm" id="borrowedAt"></p>
            </div>
            <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-3">
              <p class="text-xs text-slate-400 mb-1"><i class="fas fa-calendar-check mr-1"></i>กำหนดคืน</p>
              <p class="font-semibold text-sm" id="dueAt"></p>
            </div>
            <div class="col-span-2 bg-indigo-50 dark:bg-indigo-950/30 rounded-xl p-3">
              <p class="text-xs text-slate-400 mb-1"><i class="fas fa-hourglass-half mr-1"></i>ระยะเวลาที่ยืม</p>
              <p class="font-bold text-indigo-600 dark:text-indigo-400" id="borrowDuration"></p>
            </div>
          </div>

          <!-- Return by -->
          <div class="mb-4">
            <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 block mb-2">
              <i class="fas fa-user-check text-emerald-500 mr-1"></i>ผู้รับคืน (สแกนบัตร หรือเลือก)
            </label>
            <div class="flex gap-2">
              <div class="flex-1 relative">
                <input type="text" id="returnerBarcode" placeholder="สแกนบัตรผู้รับคืน..."
                  class="w-full border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 rounded-xl px-4 py-2.5 pl-10 text-slate-800 dark:text-white focus:outline-none focus:border-emerald-400 transition-all"
                  autocomplete="off">
                <i class="fas fa-id-card absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
              </div>
            </div>
            <div id="returnerInfo" class="hidden mt-2 flex items-center gap-2 p-2 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl">
              <img id="returnerAvatar" src="" alt="" class="w-8 h-8 rounded-full object-cover">
              <span class="text-sm font-medium text-emerald-800 dark:text-emerald-300" id="returnerName"></span>
              <input type="hidden" id="returnerUserId">
            </div>
          </div>

          <div class="mb-4">
            <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 block mb-2">
              <i class="fas fa-sticky-note text-slate-400 mr-1"></i>หมายเหตุ
            </label>
            <textarea id="returnNotes" rows="2" placeholder="หมายเหตุ (ไม่บังคับ)..."
              class="w-full border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 rounded-xl px-4 py-2.5 text-slate-800 dark:text-white focus:outline-none focus:border-emerald-400 transition-all resize-none"></textarea>
          </div>

          <input type="hidden" id="returnRecordId">
          <div class="flex gap-3">
            <button onclick="resetReturn()"
              class="flex-1 py-3 rounded-xl border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition-all">
              <i class="fas fa-redo mr-2"></i>เริ่มใหม่
            </button>
            <button id="confirmReturnBtn" onclick="confirmReturn()"
              class="flex-1 bg-gradient-to-r from-emerald-500 to-teal-600 text-white py-3 rounded-xl font-bold hover:opacity-90 transition-all hover:shadow-lg hover:shadow-emerald-500/30">
              <i class="fas fa-check mr-2"></i>ยืนยันคืน iPad
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Camera Scanner Modal -->
<div id="cameraModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/70 p-4">
  <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 w-full max-w-sm shadow-2xl">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-bold text-slate-800 dark:text-white"><i class="fas fa-camera mr-2 text-indigo-500"></i>สแกนด้วยกล้อง</h3>
      <button onclick="closeCameraScanner()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><i class="fas fa-times text-xl"></i></button>
    </div>
    <div id="cameraReader" class="rounded-xl overflow-hidden bg-black"></div>
    <p class="text-sm text-slate-400 text-center mt-3">จ่อกล้องไปที่ Barcode หรือ QR Code</p>
  </div>
</div>

<script>
let html5QrCode = null;
let currentCameraTarget = 'ipad';
let currentRecordData = null;

document.getElementById('returnIpadBarcode').focus();

document.getElementById('returnIpadBarcode').addEventListener('keydown', function(e) {
  if (e.key === 'Enter' && this.value.trim()) {
    e.preventDefault();
    scanReturnIpad(this.value.trim());
  }
});

document.getElementById('returnerBarcode').addEventListener('keydown', function(e) {
  if (e.key === 'Enter' && this.value.trim()) {
    e.preventDefault();
    scanReturner(this.value.trim());
  }
});

function scanReturnIpad(barcode) {
  showLoader(true);
  fetch('api/scan_ipad.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: 'barcode=' + encodeURIComponent(barcode)
  })
  .then(r => r.json())
  .then(data => {
    showLoader(false);
    if (data.blocked && data.status === 'borrowed' && data.borrower) {
      const b = data.borrower;
      currentRecordData = b;
      document.getElementById('returnIpadName').textContent = data.ipad.device_name;
      document.getElementById('returnIpadModel').textContent = data.ipad.model;
      document.getElementById('borrowerAvatar').src = '<?= DEFAULT_AVATAR ?>';
      document.getElementById('borrowerName').textContent = b.name;
      document.getElementById('borrowerClass').textContent = b.class_position;
      document.getElementById('borrowerCode').textContent = 'รหัส: ' + b.user_code;
      document.getElementById('borrowedAt').textContent = b.borrowed_at;
      const dueEl = document.getElementById('dueAt');
      dueEl.textContent = b.due_date;
      if (b.is_overdue) { dueEl.className = 'font-semibold text-sm text-red-500 font-bold'; }
      else { dueEl.className = 'font-semibold text-sm text-slate-800 dark:text-white'; }
      document.getElementById('borrowDuration').textContent = b.duration + (b.is_overdue ? ' (⚠️ เกินกำหนด!)' : '');
      document.getElementById('returnRecordId').value = b.record_id;
      const infoCard = document.getElementById('returnInfoCard');
      infoCard.classList.remove('hidden');
      infoCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
      document.getElementById('returnerBarcode').focus();
    } else if (!data.blocked && data.success) {
      Swal.fire({icon:'info',title:'iPad นี้ยังไม่ได้ถูกยืม',text:`${data.ipad.device_name} มีสถานะ: ${data.ipad.status_label}`,confirmButtonColor:'#6366f1',
        background: isDark() ? '#1e293b' : '#fff', color: isDark() ? '#f1f5f9' : '#1e293b'});
      document.getElementById('returnIpadBarcode').value = '';
      document.getElementById('returnIpadBarcode').focus();
    } else if (data.blocked && data.status !== 'borrowed') {
      Swal.fire({icon:'warning',title:'iPad นี้ไม่ได้ถูกยืม',text:data.message,confirmButtonColor:'#6366f1',
        background: isDark() ? '#1e293b' : '#fff', color: isDark() ? '#f1f5f9' : '#1e293b'});
      document.getElementById('returnIpadBarcode').value = '';
      document.getElementById('returnIpadBarcode').focus();
    } else {
      Swal.fire({icon:'error',title:'ไม่พบ iPad',text:data.message,confirmButtonColor:'#6366f1',
        background: isDark() ? '#1e293b' : '#fff', color: isDark() ? '#f1f5f9' : '#1e293b'});
      document.getElementById('returnIpadBarcode').value = '';
      document.getElementById('returnIpadBarcode').focus();
    }
  })
  .catch(() => { showLoader(false); Swal.fire({icon:'error',title:'เกิดข้อผิดพลาด',confirmButtonColor:'#6366f1'}); });
}

function scanReturner(barcode) {
  fetch('api/scan_user.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: 'barcode=' + encodeURIComponent(barcode)
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      document.getElementById('returnerAvatar').src = data.user.avatar_url;
      document.getElementById('returnerName').textContent = data.user.full_name;
      document.getElementById('returnerUserId').value = data.user.id;
      document.getElementById('returnerInfo').classList.remove('hidden');
    } else {
      Swal.fire({icon:'error',title:'ไม่พบผู้ใช้',text:data.message,confirmButtonColor:'#6366f1',
        background: isDark() ? '#1e293b' : '#fff', color: isDark() ? '#f1f5f9' : '#1e293b'});
    }
  });
}

function confirmReturn() {
  const recordId  = document.getElementById('returnRecordId').value;
  const returnedBy = document.getElementById('returnerUserId').value;
  const notes     = document.getElementById('returnNotes').value;

  if (!returnedBy) {
    Swal.fire({icon:'warning',title:'กรุณาสแกนบัตรผู้รับคืน',confirmButtonColor:'#6366f1',
      background: isDark() ? '#1e293b' : '#fff', color: isDark() ? '#f1f5f9' : '#1e293b'});
    return;
  }

  Swal.fire({
    title: 'ยืนยันการคืน?',
    html: `คืน <b>${document.getElementById('returnIpadName').textContent}</b>`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: '<i class="fas fa-check mr-1"></i>ยืนยัน',
    cancelButtonText: 'ยกเลิก',
    confirmButtonColor: '#10b981',
    background: isDark() ? '#1e293b' : '#fff',
    color: isDark() ? '#f1f5f9' : '#1e293b',
  }).then(result => {
    if (!result.isConfirmed) return;

    const btn = document.getElementById('confirmReturnBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>กำลังบันทึก...';

    fetch('api/return.php', {
      method: 'POST',
      headers: {'Content-Type':'application/x-www-form-urlencoded'},
      body: new URLSearchParams({ record_id: recordId, returned_by: returnedBy, notes })
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        Swal.fire({
          icon: 'success',
          title: '✅ คืน iPad สำเร็จ!',
          html: `<b>${document.getElementById('returnIpadName').textContent}</b><br><small class="text-gray-500">ระยะเวลาที่ยืม: ${data.duration}</small>`,
          confirmButtonText: 'คืนเครื่องถัดไป',
          confirmButtonColor: '#10b981',
          background: isDark() ? '#1e293b' : '#fff',
          color: isDark() ? '#f1f5f9' : '#1e293b',
        }).then(() => resetReturn());
      } else {
        Swal.fire({icon:'error',title:'เกิดข้อผิดพลาด',text:data.message,confirmButtonColor:'#6366f1',
          background: isDark() ? '#1e293b' : '#fff', color: isDark() ? '#f1f5f9' : '#1e293b'});
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check mr-2"></i>ยืนยันคืน iPad';
      }
    });
  });
}

function resetReturn() {
  document.getElementById('returnIpadBarcode').value = '';
  document.getElementById('returnerBarcode').value = '';
  document.getElementById('returnerUserId').value = '';
  document.getElementById('returnNotes').value = '';
  document.getElementById('returnInfoCard').classList.add('hidden');
  document.getElementById('returnerInfo').classList.add('hidden');
  currentRecordData = null;
  const btn = document.getElementById('confirmReturnBtn');
  btn.disabled = false;
  btn.innerHTML = '<i class="fas fa-check mr-2"></i>ยืนยันคืน iPad';
  document.getElementById('returnIpadBarcode').focus();
}

function openReturnCamera() {
  currentCameraTarget = 'ipad';
  document.getElementById('cameraModal').classList.remove('hidden');
  html5QrCode = new Html5Qrcode('cameraReader');
  html5QrCode.start({facingMode:'environment'},{fps:10,qrbox:{width:250,height:150}},
    (text) => { closeCameraScanner(); document.getElementById('returnIpadBarcode').value = text; scanReturnIpad(text); },
    () => {}
  ).catch(() => { closeCameraScanner(); Swal.fire({icon:'error',title:'ไม่สามารถเปิดกล้องได้',confirmButtonColor:'#6366f1'}); });
}

function closeCameraScanner() {
  document.getElementById('cameraModal').classList.add('hidden');
  if (html5QrCode) html5QrCode.stop().catch(() => {});
}

function showLoader(show) { 
  const loader = document.getElementById('pageLoader');
  if (loader) loader.classList.toggle('hidden', !show); 
}
function isDark() { return document.documentElement.classList.contains('dark'); }
</script>

<?php require_once __DIR__ . '/../layout/kiosk_footer.php'; ?>
