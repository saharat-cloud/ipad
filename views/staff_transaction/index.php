<?php
$pageTitle = 'ทำรายการยืม-คืน (เจ้าหน้าที่)';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';
?>

<div class="mb-6">
  <h1 class="text-2xl font-bold text-slate-800 dark:text-white mb-2">ทำรายการยืม-คืน</h1>
  <p class="text-slate-500 dark:text-slate-400">ทำรายการให้ยืม รับคืน และรายงานปัญหาสำหรับเจ้าหน้าที่</p>
</div>

<!-- Tabs -->
<div class="flex gap-2 mb-6 border-b border-slate-200 dark:border-slate-700 pb-2">
  <button onclick="switchTab('lend')" id="tabBtn_lend" class="px-6 py-2 rounded-t-lg font-medium text-indigo-600 border-b-2 border-indigo-600">ให้ยืม (Lend)</button>
  <button onclick="switchTab('return')" id="tabBtn_return" class="px-6 py-2 rounded-t-lg font-medium text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">รับคืน (Return)</button>
  <button onclick="switchTab('issue')" id="tabBtn_issue" class="px-6 py-2 rounded-t-lg font-medium text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">รายงานปัญหา (Issue)</button>
</div>

<!-- Tab: Lend -->
<div id="tab_lend" class="space-y-6">
  <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
    <h2 class="text-lg font-bold text-slate-800 dark:text-white mb-4"><i class="fas fa-hand-holding mr-2"></i> ข้อมูลผู้ยืม</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">เบอร์โทรศัพท์ (ค้นหาหรือสร้างใหม่) <span class="text-red-500">*</span></label>
        <div class="flex gap-2">
            <input type="tel" id="lend_phone" class="w-full border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700/50 rounded-xl px-4 py-2 text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500" placeholder="08XXXXXXXX">
            <button onclick="searchUser()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-xl transition-colors whitespace-nowrap"><i class="fas fa-search"></i> ค้นหา</button>
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">ชั้น / ตำแหน่ง <span class="text-red-500">*</span></label>
        <input type="text" id="lend_class" class="w-full border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700/50 rounded-xl px-4 py-2 text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500" placeholder="เช่น ม.4/1, ครูวิชาการ">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">ชื่อจริง <span class="text-red-500">*</span></label>
        <input type="text" id="lend_fname" class="w-full border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700/50 rounded-xl px-4 py-2 text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">นามสกุล <span class="text-red-500">*</span></label>
        <input type="text" id="lend_lname" class="w-full border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700/50 rounded-xl px-4 py-2 text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500">
      </div>
    </div>
  </div>

  <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
    <h2 class="text-lg font-bold text-slate-800 dark:text-white mb-4"><i class="fas fa-tablet-alt mr-2"></i> เลือกเครื่องที่จะให้ยืม</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 max-h-64 overflow-y-auto p-1">
      <?php foreach($availableIpads as $ipad): ?>
      <label class="cursor-pointer">
        <input type="checkbox" name="lend_ipads[]" value="<?= $ipad['id'] ?>" class="peer sr-only">
        <div class="p-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-900/30 transition-all text-center relative">
            <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 text-indigo-500">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="font-bold text-slate-800 dark:text-white"><?= sanitize($ipad['device_code']) ?></div>
        </div>
      </label>
      <?php endforeach; ?>
      <?php if(empty($availableIpads)): ?>
        <div class="col-span-full text-center py-4 text-slate-500">ไม่มีเครื่องว่างในขณะนี้</div>
      <?php endif; ?>
    </div>
    
    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">กำหนดคืน <span class="text-red-500">*</span></label>
        <?php $defaultDueDate = date('Y-m-d\T16:00', strtotime('tomorrow')); ?>
        <input type="datetime-local" id="lend_due_date" value="<?= $defaultDueDate ?>" class="w-full border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700/50 rounded-xl px-4 py-2 text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">หมายเหตุ (ไม่บังคับ)</label>
        <input type="text" id="lend_notes" class="w-full border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700/50 rounded-xl px-4 py-2 text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500" placeholder="เช่น ยืมไปทัศนศึกษา">
      </div>
    </div>

    <div class="mt-6 text-right">
      <button onclick="submitLend()" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium transition-colors shadow-lg shadow-indigo-500/30">
        <i class="fas fa-check mr-2"></i> ยืนยันการให้ยืม
      </button>
    </div>
  </div>
</div>

<!-- Tab: Return -->
<div id="tab_return" class="hidden space-y-6">
  <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
    <h2 class="text-lg font-bold text-slate-800 dark:text-white mb-4"><i class="fas fa-undo mr-2"></i> รับคืน iPad</h2>
    <div class="flex gap-2 max-w-md mb-6">
        <input type="text" id="return_search" class="w-full border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700/50 rounded-xl px-4 py-2 text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500" placeholder="พิมพ์เบอร์โทร หรือ รหัส iPad...">
        <button onclick="searchBorrows()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition-colors whitespace-nowrap"><i class="fas fa-search"></i> ค้นหา</button>
    </div>

    <div id="return_results" class="hidden">
      <h3 class="font-semibold text-slate-700 dark:text-slate-300 mb-3 border-b border-slate-200 dark:border-slate-700 pb-2">พบรายการที่ยังไม่คืน:</h3>
      <div id="return_list" class="space-y-3">
        <!-- Rendered via JS -->
      </div>
      <div class="mt-6 text-right">
        <button onclick="submitReturn()" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-medium transition-colors shadow-lg shadow-emerald-500/30">
          <i class="fas fa-check-double mr-2"></i> ยืนยันการรับคืน
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Tab: Issue -->
<div id="tab_issue" class="hidden space-y-6">
  <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
    <h2 class="text-lg font-bold text-slate-800 dark:text-white mb-4"><i class="fas fa-exclamation-triangle mr-2"></i> แจ้งปัญหา iPad (ชำรุด/สูญหาย)</h2>
    <div class="max-w-xl space-y-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">ค้นหาเครื่อง iPad (รหัสเครื่อง)</label>
        <div class="flex gap-2">
            <input type="text" id="issue_search" class="w-full border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700/50 rounded-xl px-4 py-2 text-slate-800 dark:text-white focus:outline-none focus:border-red-500" placeholder="เช่น IPD-001">
            <button onclick="searchIpadIssue()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-xl transition-colors whitespace-nowrap"><i class="fas fa-search"></i> ค้นหา</button>
        </div>
      </div>
      
      <div id="issue_form" class="hidden space-y-4 pt-4 border-t border-slate-200 dark:border-slate-700">
        <div class="p-3 bg-slate-50 dark:bg-slate-700 rounded-xl mb-2">
            <span class="block text-xs text-slate-500">รหัสเครื่องที่เลือก</span>
            <span class="font-bold text-slate-800 dark:text-white text-lg" id="issue_selected_code"></span>
            <input type="hidden" id="issue_ipad_id">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">ระบุปัญหา</label>
          <select id="issue_status" class="w-full border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700/50 rounded-xl px-4 py-2 text-slate-800 dark:text-white focus:outline-none focus:border-red-500">
            <option value="broken">ชำรุด / ส่งซ่อม</option>
            <option value="lost">สูญหาย</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">รายละเอียดเพิ่มเติม</label>
          <textarea id="issue_notes" rows="3" class="w-full border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700/50 rounded-xl px-4 py-2 text-slate-800 dark:text-white focus:outline-none focus:border-red-500" placeholder="ระบุรายละเอียดอาการชำรุด..."></textarea>
        </div>
        <div class="text-right mt-4">
          <button onclick="submitIssue()" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl font-medium transition-colors shadow-lg shadow-red-500/30">
            <i class="fas fa-save mr-2"></i> บันทึกปัญหา
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function switchTab(tabId) {
    ['lend', 'return', 'issue'].forEach(t => {
        document.getElementById('tab_'+t).classList.add('hidden');
        const btn = document.getElementById('tabBtn_'+t);
        btn.classList.remove('text-indigo-600', 'border-b-2', 'border-indigo-600');
        btn.classList.add('text-slate-500');
    });
    document.getElementById('tab_'+tabId).classList.remove('hidden');
    const activeBtn = document.getElementById('tabBtn_'+tabId);
    activeBtn.classList.remove('text-slate-500');
    activeBtn.classList.add('text-indigo-600', 'border-b-2', 'border-indigo-600');
}

// ---------------- LEND ----------------
function searchUser() {
    const phone = document.getElementById('lend_phone').value.trim();
    if(!phone) return;
    
    fetch('?page=staff_transaction_api&action=search_user', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({phone})
    }).then(r=>r.json()).then(res => {
        if(res.success && res.user) {
            document.getElementById('lend_fname').value = res.user.first_name;
            document.getElementById('lend_lname').value = res.user.last_name;
            document.getElementById('lend_class').value = res.user.class_position;
            Swal.fire({toast:true, position:'top-end', icon:'success', title:'พบข้อมูลผู้ใช้', showConfirmButton:false, timer:1500});
        } else {
            Swal.fire({toast:true, position:'top-end', icon:'info', title:'ไม่พบข้อมูล จะสร้างผู้ใช้ใหม่เมื่อยืนยัน', showConfirmButton:false, timer:2000});
            document.getElementById('lend_fname').value = '';
            document.getElementById('lend_lname').value = '';
            document.getElementById('lend_class').value = '';
        }
    });
}

function submitLend() {
    const phone = document.getElementById('lend_phone').value.trim();
    const fname = document.getElementById('lend_fname').value.trim();
    const lname = document.getElementById('lend_lname').value.trim();
    const cpos = document.getElementById('lend_class').value.trim();
    const due = document.getElementById('lend_due_date').value;
    const notes = document.getElementById('lend_notes').value.trim();
    
    const checkboxes = document.querySelectorAll('input[name="lend_ipads[]"]:checked');
    const ipads = Array.from(checkboxes).map(c => c.value);
    
    if(!phone || !fname || !lname || !cpos || !due || ipads.length === 0) {
        Swal.fire({icon:'warning', title:'ข้อมูลไม่ครบ', text:'กรุณากรอกข้อมูลและเลือกเครื่องให้ครบถ้วน'});
        return;
    }
    
    fetch('?page=staff_transaction_api&action=lend', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({phone, fname, lname, class_position: cpos, due_date: due, notes, ipads})
    }).then(r=>r.json()).then(res => {
        if(res.success) {
            Swal.fire({icon:'success', title:'บันทึกการให้ยืมสำเร็จ'}).then(() => window.location.reload());
        } else {
            Swal.fire({icon:'error', title:'เกิดข้อผิดพลาด', text:res.message});
        }
    });
}

// ---------------- RETURN ----------------
let pendingReturnBorrows = [];
function searchBorrows() {
    const query = document.getElementById('return_search').value.trim();
    if(!query) return;
    
    fetch('?page=staff_transaction_api&action=search_borrows', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({query})
    }).then(r=>r.json()).then(res => {
        if(res.success && res.borrows.length > 0) {
            pendingReturnBorrows = res.borrows;
            const container = document.getElementById('return_list');
            container.innerHTML = '';
            res.borrows.forEach(b => {
                let div = document.createElement('div');
                div.className = 'p-4 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-900/50';
                div.innerHTML = `
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <div class="font-bold text-slate-800 dark:text-white">${b.device_code} (${b.device_name})</div>
                            <div class="text-xs text-slate-500">ยืมโดย: ${b.first_name} ${b.last_name} (${b.user_code})</div>
                        </div>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="return_record_ids[]" value="${b.id}" class="peer sr-only">
                            <div class="w-10 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-emerald-500 relative"></div>
                            <span class="ml-2 text-sm font-medium text-slate-700 dark:text-slate-300">รับคืน</span>
                        </label>
                    </div>
                `;
                container.appendChild(div);
            });
            document.getElementById('return_results').classList.remove('hidden');
        } else {
            Swal.fire({icon:'info', title:'ไม่พบรายการ', text:'ไม่พบเครื่องที่ถูกยืมจากคำค้นหานี้'});
            document.getElementById('return_results').classList.add('hidden');
        }
    });
}

function submitReturn() {
    const checkboxes = document.querySelectorAll('input[name="return_record_ids[]"]:checked');
    const records = Array.from(checkboxes).map(c => c.value);
    
    if(records.length === 0) {
        Swal.fire({icon:'warning', title:'เลือกรายการ', text:'กรุณาเลือกเครื่องที่ต้องการรับคืน'});
        return;
    }
    
    fetch('?page=staff_transaction_api&action=return', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({records})
    }).then(r=>r.json()).then(res => {
        if(res.success) {
            Swal.fire({icon:'success', title:'รับคืนสำเร็จ'}).then(() => window.location.reload());
        } else {
            Swal.fire({icon:'error', title:'เกิดข้อผิดพลาด', text:res.message});
        }
    });
}

// ---------------- ISSUE ----------------
function searchIpadIssue() {
    const code = document.getElementById('issue_search').value.trim();
    if(!code) return;
    
    fetch('api/staff_transaction.php?action=search_ipad', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({code})
    }).then(r=>r.json()).then(res => {
        if(res.success && res.ipad) {
            document.getElementById('issue_ipad_id').value = res.ipad.id;
            document.getElementById('issue_selected_code').innerText = res.ipad.device_code + ' - ' + res.ipad.device_name;
            document.getElementById('issue_form').classList.remove('hidden');
        } else {
            Swal.fire({icon:'error', title:'ไม่พบเครื่อง', text:'ไม่พบ iPad รหัสนี้ในระบบ'});
            document.getElementById('issue_form').classList.add('hidden');
        }
    });
}

function submitIssue() {
    const ipadId = document.getElementById('issue_ipad_id').value;
    const status = document.getElementById('issue_status').value;
    const notes = document.getElementById('issue_notes').value.trim();
    
    fetch('api/staff_transaction.php?action=issue', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ipad_id: ipadId, status, notes})
    }).then(r=>r.json()).then(res => {
        if(res.success) {
            Swal.fire({icon:'success', title:'บันทึกข้อมูลสำเร็จ'}).then(() => window.location.reload());
        } else {
            Swal.fire({icon:'error', title:'เกิดข้อผิดพลาด', text:res.message});
        }
    });
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
