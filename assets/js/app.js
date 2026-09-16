/**
 * iPad Borrow System - Main Application JS
 */

// ===================== Dark Mode =====================
(function initDarkMode() {
  const saved = localStorage.getItem('darkMode');
  if (saved === 'dark') {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }
})();

function toggleDarkMode() {
  const html = document.documentElement;
  const isDark = html.classList.toggle('dark');
  localStorage.setItem('darkMode', isDark ? 'dark' : 'light');
}

// ===================== Sidebar =====================
function toggleSidebar() {
  const sidebar  = document.getElementById('sidebar');
  const overlay  = document.getElementById('sidebarOverlay');
  const isOpen   = sidebar.classList.toggle('open');
  sidebar.style.transform = isOpen ? 'translateX(0)' : '';
  overlay.classList.toggle('hidden', !isOpen);
  document.body.style.overflow = isOpen ? 'hidden' : '';
}

// ===================== Live Clock =====================
function updateClock() {
  const el = document.getElementById('liveClock');
  if (!el) return;
  const now = new Date();
  const thMonths = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
  const pad = n => String(n).padStart(2, '0');
  const day   = now.getDate();
  const month = thMonths[now.getMonth() + 1];
  const year  = now.getFullYear() + 543;
  const time  = `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
  el.textContent = `${day} ${month} ${year}  ${time}`;
}
updateClock();
setInterval(updateClock, 1000);

// ===================== Flash Message Auto-hide =====================
document.addEventListener('DOMContentLoaded', function () {
  const flash = document.getElementById('flashMsg');
  if (flash) {
    setTimeout(() => {
      flash.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
      flash.style.opacity = '0';
      flash.style.transform = 'translateY(-10px)';
      setTimeout(() => flash.remove(), 500);
    }, 4000);
  }

  // Sidebar keyboard ESC close
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      const sidebar = document.getElementById('sidebar');
      if (sidebar && sidebar.classList.contains('open')) toggleSidebar();
    }
  });
});

// ===================== Helper: isDark =====================
function isDark() {
  return document.documentElement.classList.contains('dark');
}

// ===================== Confirm Delete =====================
function confirmDelete(url, name) {
  Swal.fire({
    title: 'ยืนยันการลบ?',
    html: `ต้องการลบ <b>${name}</b>?<br><small class="text-gray-400">การกระทำนี้ไม่สามารถย้อนกลับได้</small>`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i>ลบ',
    cancelButtonText: 'ยกเลิก',
    confirmButtonColor: '#ef4444',
    background: isDark() ? '#1e293b' : '#fff',
    color: isDark() ? '#f1f5f9' : '#1e293b',
  }).then(result => {
    if (result.isConfirmed) window.location.href = url;
  });
}

// ===================== Global Loader =====================
function showLoader(show) {
  const el = document.getElementById('pageLoader');
  if (el) el.classList.toggle('hidden', !show);
}

// ===================== Fetch Wrapper =====================
async function apiPost(url, params) {
  const body = new URLSearchParams(params);
  const response = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  });
  return response.json();
}

// ===================== Toast Notification =====================
function toast(type, message, duration = 3000) {
  const colors = {
    success: 'bg-emerald-500',
    error:   'bg-red-500',
    warning: 'bg-amber-500',
    info:    'bg-indigo-500',
  };
  const icons = {
    success: 'fa-check-circle',
    error:   'fa-exclamation-circle',
    warning: 'fa-exclamation-triangle',
    info:    'fa-info-circle',
  };
  const container = document.getElementById('toastContainer') || createToastContainer();
  const toast = document.createElement('div');
  toast.className = `flex items-center gap-3 ${colors[type] || colors.info} text-white px-5 py-3 rounded-xl shadow-xl text-sm font-medium translate-x-full transition-transform duration-300`;
  toast.innerHTML = `<i class="fas ${icons[type] || icons.info}"></i><span>${message}</span>`;
  container.appendChild(toast);
  requestAnimationFrame(() => { toast.classList.remove('translate-x-full'); });
  setTimeout(() => {
    toast.classList.add('translate-x-full');
    setTimeout(() => toast.remove(), 300);
  }, duration);
}

function createToastContainer() {
  const div = document.createElement('div');
  div.id = 'toastContainer';
  div.className = 'fixed top-4 right-4 z-[100] flex flex-col gap-2';
  document.body.appendChild(div);
  return div;
}

// ===================== Format Duration =====================
function formatDuration(ms) {
  const s = Math.floor(ms / 1000);
  const m = Math.floor(s / 60);
  const h = Math.floor(m / 60);
  const d = Math.floor(h / 24);
  if (d > 0) return `${d} วัน ${h % 24} ชั่วโมง`;
  if (h > 0) return `${h} ชั่วโมง ${m % 60} นาที`;
  if (m > 0) return `${m} นาที`;
  return 'น้อยกว่า 1 นาที';
}

// ===================== Image Preview =====================
function previewImage(input, previewId) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      const img = document.getElementById(previewId);
      if (img) img.src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
  }
}

// ===================== Auto-submit on barcode scan =====================
// USB scanners typically end with Enter key — inputs with class 'barcode-scan'
// will auto-submit when Enter is pressed after a short delay
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('input.barcode-scan').forEach(input => {
    let scanBuffer = '';
    let scanTimer  = null;

    input.addEventListener('keypress', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        if (scanBuffer.length > 2) {
          input.value = scanBuffer;
          input.dispatchEvent(new Event('scan', { bubbles: true }));
        }
        scanBuffer = '';
        clearTimeout(scanTimer);
      } else {
        scanBuffer += e.key;
        clearTimeout(scanTimer);
        scanTimer = setTimeout(() => { scanBuffer = ''; }, 100);
      }
    });
  });
});
