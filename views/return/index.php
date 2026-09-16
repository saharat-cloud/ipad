<?php
$pageTitle = 'คืน iPad';
require_once __DIR__ . '/../layout/kiosk_header.php';
?>

<div class="max-w-2xl mx-auto">
  
  <!-- Step 1: Search User -->
  <div id="step1" class="animate-fade-in">
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm text-center">
      <div class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-emerald-500/20">
        <i class="fas fa-undo-alt text-white text-2xl"></i>
      </div>
      <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-2">คืน iPad</h2>
      <p class="text-slate-500 dark:text-slate-400 mb-6">กรอกเบอร์มือถือของคุณเพื่อตรวจสอบรายการที่ยืม</p>

      <form id="searchUserForm" class="max-w-sm mx-auto space-y-4">
        <div class="relative text-left">
          <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 block mb-1">เบอร์มือถือ / รหัสประจำตัว</label>
          <input type="text" id="userPhone" placeholder="08xxxxxxxx" required
            class="w-full border-2 border-emerald-200 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-950/30 rounded-xl px-4 py-3 pl-11 text-slate-800 dark:text-white focus:outline-none focus:border-emerald-500 transition-all font-semibold">
          <i class="fas fa-phone absolute left-4 top-[38px] text-emerald-500 text-lg"></i>
        </div>
        <button type="submit" id="searchBtn" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3 rounded-xl transition-all shadow-md flex justify-center items-center gap-2">
          <i class="fas fa-search"></i> ค้นหารายการยืม
        </button>
      </form>
    </div>
  </div>

  <!-- Step 2: Select iPads to Return -->
  <div id="step2" class="hidden animate-bounce-in">
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm mb-4">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
          <span class="w-7 h-7 bg-emerald-500 text-white rounded-full text-sm flex items-center justify-center font-bold">2</span>
          เลือก iPad ที่ต้องการคืน
        </h2>
        <button type="button" onclick="resetFlow()" class="text-sm text-slate-400 hover:text-emerald-500 transition-colors">
          <i class="fas fa-arrow-left mr-1"></i>กลับ
        </button>
      </div>

      <!-- User Profile Card -->
      <div class="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl mb-4 border border-slate-100 dark:border-slate-600">
        <img id="userInfoAvatar" src="" alt="" class="w-12 h-12 rounded-xl object-cover">
        <div>
          <h3 class="font-bold text-slate-800 dark:text-white" id="userInfoName"></h3>
          <p class="text-xs text-slate-500 dark:text-slate-400" id="userInfoRole"></p>
        </div>
      </div>

      <form id="returnForm" class="space-y-4">
        <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 block">
          รายการ iPad ที่กำลังยืม <span class="text-emerald-500">*</span>
        </label>
        
        <div id="borrowedIpadsContainer" class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-64 overflow-y-auto pr-2">
          <!-- iPads rendered via JS -->
        </div>

        <div class="pt-2">
          <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 block mb-2">
            <i class="fas fa-sticky-note text-slate-400 mr-1"></i>หมายเหตุ (ไม่บังคับ)
          </label>
          <textarea id="returnNotes" rows="2" placeholder="เช่น มีรอยขีดข่วน หรือ อุปกรณ์ไม่ครบ..."
            class="w-full border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 rounded-xl px-4 py-2.5 text-slate-800 dark:text-white focus:outline-none focus:border-emerald-400 transition-all resize-none"></textarea>
        </div>

        <div class="pt-4">
          <button type="button" id="nextToStep3Btn" onclick="handleStep2Submit()" class="w-full bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-emerald-500/30">
            <i class="fas fa-arrow-right mr-2"></i>ดำเนินการต่อ
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Step 3: Partial Return (Incomplete) -->
  <div id="step3" class="hidden animate-slide-in max-w-2xl mx-auto">
    <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-xl border border-slate-100 dark:border-slate-700/50 p-6 md:p-8">
      <div class="text-center mb-6">
        <div class="w-16 h-16 bg-orange-100 dark:bg-orange-500/20 text-orange-500 rounded-full flex items-center justify-center text-3xl mx-auto mb-4">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h2 class="text-2xl font-bold text-slate-800 dark:text-white">คืนไม่ครบทุกเครื่อง</h2>
        <p class="text-slate-500 dark:text-slate-400 mt-2">มีบางเครื่องที่คุณไม่ได้เลือกคืน กรุณาระบุเหตุผลหรือกำหนดวันคืนใหม่</p>
      </div>

      <div id="unreturnedIpadsContainer" class="space-y-4 mb-6 max-h-72 overflow-y-auto pr-2">
        <!-- Rendered via JS -->
      </div>

      <div class="flex gap-3 pt-4">
        <button type="button" onclick="goBackToStep2()" class="flex-1 py-3 rounded-xl border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition-all">
          <i class="fas fa-arrow-left mr-2"></i>ย้อนกลับ
        </button>
        <button type="button" id="confirmPartialReturnBtn" onclick="submitPartialReturn()" class="flex-1 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-bold py-3 rounded-xl transition-all shadow-lg shadow-orange-500/30">
          <i class="fas fa-check-circle mr-2"></i>ยืนยันส่งข้อมูล
        </button>
      </div>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let currentUser = null;
let activeBorrows = [];

document.getElementById('searchUserForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const phone = document.getElementById('userPhone').value.trim();
  if(!phone) return;

  const btn = document.getElementById('searchBtn');
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังค้นหา...';
  btn.disabled = true;

  fetch('api/get_user_borrows.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ phone: phone })
  })
  .then(r => r.json())
  .then(data => {
    btn.innerHTML = '<i class="fas fa-search"></i> ค้นหารายการยืม';
    btn.disabled = false;
    
    if (data.success) {
      currentUser = data.user;
      activeBorrows = data.borrows;
      renderStep2();
    } else {
      Swal.fire({
        icon: 'error',
        title: 'ไม่พบข้อมูล',
        text: data.message,
        confirmButtonColor: '#10b981'
      });
    }
  })
  .catch(err => {
    btn.innerHTML = '<i class="fas fa-search"></i> ค้นหารายการยืม';
    btn.disabled = false;
    Swal.fire({icon: 'error', title: 'เกิดข้อผิดพลาด', text: err.message});
  });
});

function renderStep2() {
  document.getElementById('step1').classList.add('hidden');
  document.getElementById('step2').classList.remove('hidden');

  document.getElementById('userInfoAvatar').src = currentUser.avatar;
  document.getElementById('userInfoName').textContent = currentUser.full_name;
  document.getElementById('userInfoRole').textContent = currentUser.role_label;

  const container = document.getElementById('borrowedIpadsContainer');
  container.innerHTML = '';

  activeBorrows.forEach(br => {
    // Format date
    let bDate = new Date(br.borrowed_at).toLocaleDateString('th-TH', {month:'short', day:'numeric', hour:'2-digit', minute:'2-digit'});
    
    let isOverdue = br.status === 'overdue';
    let borderClass = isOverdue ? 'border-red-300 dark:border-red-700' : 'border-slate-200 dark:border-slate-700';

    const label = document.createElement('label');
    label.className = `relative flex items-start gap-3 p-3 border-2 ${borderClass} rounded-xl cursor-pointer hover:border-emerald-500 dark:hover:border-emerald-500 transition-all [&:has(input:checked)]:border-emerald-500 [&:has(input:checked)]:bg-emerald-50 dark:[&:has(input:checked)]:bg-emerald-900/20`;
    label.innerHTML = `
      <div class="pt-0.5">
        <input type="checkbox" name="returnRecords[]" value="${br.id}" class="w-5 h-5 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded" checked>
      </div>
      <div class="flex-1 min-w-0">
        <div class="font-bold text-sm text-slate-800 dark:text-white truncate">${br.device_code}</div>
        <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate">${br.device_name}</div>
        <div class="text-[10px] text-slate-400 mt-1 flex justify-between">
          <span>ยืมเมื่อ: ${bDate}</span>
          ${isOverdue ? '<span class="text-red-500 font-bold">เกินกำหนด</span>' : ''}
        </div>
      </div>
    `;
    container.appendChild(label);
  });
}

function handleStep2Submit() {
  const checked = document.querySelectorAll('input[name="returnRecords[]"]:checked');
  if (checked.length === 0) {
    Swal.fire({icon: 'warning', title: 'กรุณาเลือก iPad', text: 'เลือก iPad ที่ต้องการคืนอย่างน้อย 1 เครื่อง', confirmButtonColor: '#10b981'});
    return;
  }

  const recordIds = Array.from(checked).map(cb => cb.value);
  const notes = document.getElementById('returnNotes').value;

  if (checked.length < activeBorrows.length) {
    // Show Step 3
    const unreturnedBorrows = activeBorrows.filter(br => !recordIds.includes(br.id.toString()));
    renderStep3(recordIds, notes, unreturnedBorrows);
  } else {
    // Return all
    Swal.fire({
      title: 'ยืนยันการคืน iPad?',
      html: `คุณต้องการส่งคำขอคืน iPad จำนวน <b>${recordIds.length}</b> เครื่องใช่หรือไม่?<br><span class="text-sm text-gray-500">สถานะจะเปลี่ยนเป็น "รอตรวจสอบ"</span>`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'ยืนยัน',
      cancelButtonText: 'ยกเลิก',
      confirmButtonColor: '#10b981'
    }).then(result => {
      if (result.isConfirmed) {
        submitReturn(recordIds, notes, []);
      }
    });
  }
}

function renderStep3(returnIds, returnNotes, unreturnedBorrows) {
  document.getElementById('step2').classList.add('hidden');
  document.getElementById('step3').classList.remove('hidden');

  const container = document.getElementById('unreturnedIpadsContainer');
  container.innerHTML = '';
  
  // Save global state for submission
  window.pendingReturnData = { returnIds, returnNotes, unreturnedBorrows };

  // Set default due date to tomorrow 16:00
  const now = new Date();
  now.setHours(16, 0, 0, 0);
  if (now < new Date()) now.setDate(now.getDate() + 1);
  const pad = n => String(n).padStart(2,'0');
  const defaultDueDate = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}T16:00`;
  const minDate = new Date().toISOString().slice(0,16);

  unreturnedBorrows.forEach((br, index) => {
    const div = document.createElement('div');
    div.className = 'p-4 border border-orange-200 dark:border-orange-700/50 bg-orange-50/50 dark:bg-orange-900/10 rounded-xl';
    div.innerHTML = `
      <div class="font-bold text-slate-800 dark:text-white mb-2"><i class="fas fa-tablet-alt text-orange-500 mr-2"></i>${br.device_code} (${br.device_name})</div>
      <div class="space-y-3">
        <div class="flex gap-4">
          <label class="flex items-center gap-2 text-sm cursor-pointer text-slate-700 dark:text-slate-300">
            <input type="radio" name="unreturnedAction_${br.id}" value="extend" onchange="toggleActionDetail(${br.id})" class="text-orange-500 focus:ring-orange-500" checked> ยืมต่อ
          </label>
          <label class="flex items-center gap-2 text-sm cursor-pointer text-slate-700 dark:text-slate-300">
            <input type="radio" name="unreturnedAction_${br.id}" value="reason" onchange="toggleActionDetail(${br.id})" class="text-orange-500 focus:ring-orange-500"> ระบุเหตุผลอื่น
          </label>
        </div>
        <div id="extendContainer_${br.id}" class="block mt-2">
          <label class="block text-xs font-semibold text-orange-600 dark:text-orange-400 mb-1">วันที่กำหนดคืน:</label>
          <input type="datetime-local" id="extendDate_${br.id}" value="${defaultDueDate}" min="${minDate}"
            class="w-full border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 rounded-lg px-3 py-2 text-sm text-slate-800 dark:text-white focus:outline-none focus:border-orange-400">
        </div>
        <div id="reasonContainer_${br.id}" class="hidden mt-2">
          <label class="block text-xs font-semibold text-orange-600 dark:text-orange-400 mb-1">เหตุผล:</label>
          <input type="text" id="reasonText_${br.id}" placeholder="โปรดระบุเหตุผล (เช่น ทำหาย, ลืมไว้ที่บ้าน)"
            class="w-full border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 rounded-lg px-3 py-2 text-sm text-slate-800 dark:text-white focus:outline-none focus:border-orange-400">
        </div>
      </div>
    `;
    container.appendChild(div);
  });
}

function toggleActionDetail(id) {
  const action = document.querySelector(`input[name="unreturnedAction_${id}"]:checked`).value;
  if (action === 'extend') {
    document.getElementById(`extendContainer_${id}`).classList.remove('hidden');
    document.getElementById(`reasonContainer_${id}`).classList.add('hidden');
  } else {
    document.getElementById(`extendContainer_${id}`).classList.add('hidden');
    document.getElementById(`reasonContainer_${id}`).classList.remove('hidden');
  }
}

function goBackToStep2() {
  document.getElementById('step3').classList.add('hidden');
  document.getElementById('step2').classList.remove('hidden');
}

function submitPartialReturn() {
  const data = window.pendingReturnData;
  const unreturnedActions = [];
  
  for (let br of data.unreturnedBorrows) {
    const action = document.querySelector(`input[name="unreturnedAction_${br.id}"]:checked`).value;
    let detail = '';
    if (action === 'extend') {
      detail = document.getElementById(`extendDate_${br.id}`).value;
      if (!detail) {
        Swal.fire({icon:'warning', title:'ข้อมูลไม่ครบ', text:'กรุณาระบุวันกำหนดคืนสำหรับเครื่อง ' + br.device_code});
        return;
      }
    } else {
      detail = document.getElementById(`reasonText_${br.id}`).value.trim();
      if (!detail) {
        Swal.fire({icon:'warning', title:'ข้อมูลไม่ครบ', text:'กรุณาระบุเหตุผลสำหรับเครื่อง ' + br.device_code});
        return;
      }
    }
    unreturnedActions.push({ record_id: br.id, action, detail });
  }

  Swal.fire({
    title: 'ยืนยันข้อมูล?',
    html: `คุณกำลังคืน <b>${data.returnIds.length}</b> เครื่อง และมีอีก <b>${unreturnedActions.length}</b> เครื่องที่ยังไม่คืน`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'ยืนยันส่งข้อมูล',
    cancelButtonText: 'ยกเลิก',
    confirmButtonColor: '#f97316'
  }).then(result => {
    if (result.isConfirmed) {
      submitReturn(data.returnIds, data.returnNotes, unreturnedActions);
    }
  });
}

function submitReturn(recordIds, notes, unreturnedActions) {
  const btn = document.getElementById('confirmPartialReturnBtn') || document.getElementById('nextToStep3Btn');
  btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>กำลังดำเนินการ...';
  btn.disabled = true;

  fetch('api/return.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      record_ids: recordIds,
      notes: notes,
      unreturned_actions: unreturnedActions
    })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      Swal.fire({
        icon: 'success',
        title: 'ดำเนินการสำเร็จ!',
        text: 'ระบบได้บันทึกข้อมูลเรียบร้อยแล้ว',
        confirmButtonText: 'รับทราบ',
        confirmButtonColor: '#10b981'
      }).then(() => {
        resetFlow();
      });
    } else {
      btn.disabled = false;
      Swal.fire({icon: 'error', title: 'ผิดพลาด', text: data.message});
    }
  })
  .catch(err => {
    btn.disabled = false;
    Swal.fire({icon: 'error', title: 'เกิดข้อผิดพลาด', text: err.message});
  });
}

function resetFlow() {
  document.getElementById('step3').classList.add('hidden');
  document.getElementById('step2').classList.add('hidden');
  document.getElementById('step1').classList.remove('hidden');
  document.getElementById('userPhone').value = '';
  document.getElementById('returnNotes').value = '';
  currentUser = null;
  activeBorrows = [];
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
