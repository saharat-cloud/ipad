<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="ระบบบริหารจัดการยืม-คืน iPad สำหรับโรงเรียน">
  <title><?= $pageTitle ?? 'ระบบยืม-คืน iPad' ?> | iPad Borrow System</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          fontFamily: { sans: ['Sarabun', 'sans-serif'] },
          colors: {
            primary: { 50:'#eef2ff',100:'#e0e7ff',200:'#c7d2fe',300:'#a5b4fc',400:'#818cf8',500:'#6366f1',600:'#4f46e5',700:'#4338ca',800:'#3730a3',900:'#312e81' },
          },
          animation: {
            'slide-in': 'slideIn 0.3s ease-out',
            'fade-in':  'fadeIn 0.4s ease-out',
            'bounce-in':'bounceIn 0.5s cubic-bezier(.36,.07,.19,.97)',
            'pulse-slow':'pulse 3s ease-in-out infinite',
          },
          keyframes: {
            slideIn:  { from:{ opacity:0, transform:'translateX(-20px)' }, to:{ opacity:1, transform:'translateX(0)' } },
            fadeIn:   { from:{ opacity:0, transform:'translateY(10px)' },  to:{ opacity:1, transform:'translateY(0)' } },
            bounceIn: { '0%':{ transform:'scale(0.3)', opacity:0 }, '50%':{ transform:'scale(1.05)' }, '70%':{ transform:'scale(0.9)' }, '100%':{ transform:'scale(1)', opacity:1 } },
          }
        }
      }
    }
  </script>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.tailwindcss.min.css">

  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/custom.css">
</head>
<body class="bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 font-sans min-h-screen">
<!-- Loading Overlay -->
<div id="pageLoader" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 backdrop-blur-sm hidden">
  <div class="flex flex-col items-center gap-4">
    <div class="w-16 h-16 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
    <p class="text-white text-lg font-medium">กำลังโหลด...</p>
  </div>
</div>
