<?php
// Print barcode page - standalone
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>พิมพ์ Barcode: <?= sanitize($ipad['device_code']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<style>
  * { font-family: 'Sarabun', sans-serif; }
  body { margin: 0; padding: 20px; background: #f8f9fa; }
  .print-area { background: white; border-radius: 12px; padding: 30px; max-width: 400px; margin: 0 auto; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
  .badge { display: inline-flex; align-items: center; background: #6366f1; color: white; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; margin-bottom: 8px; }
  h1 { margin: 0 0 4px; font-size: 22px; color: #1e293b; }
  .meta { font-size: 13px; color: #64748b; margin: 0 0 16px; }
  table { width: 100%; font-size: 13px; color: #475569; border-collapse: collapse; }
  td { padding: 4px 0; }
  td:first-child { font-weight: 600; color: #1e293b; width: 40%; }
  .barcode-box { text-align: center; margin: 16px 0; padding: 16px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; }
  .btn-row { display: flex; gap: 10px; margin-top: 20px; }
  .btn { flex: 1; padding: 10px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; }
  .btn-print { background: #6366f1; color: white; }
  .btn-close { background: #f1f5f9; color: #475569; }
  @media print {
    body { background: white; padding: 0; }
    .btn-row { display: none; }
    .print-area { box-shadow: none; padding: 20px; }
  }
</style>
</head>
<body>
<div class="print-area">
  <span class="badge"><i class="fas fa-tablet-alt" style="margin-right:6px"></i>iPad</span>
  <h1><?= sanitize($ipad['device_name']) ?></h1>
  <p class="meta"><?= sanitize($ipad['model']) ?></p>

  <table>
    <tr><td>รหัสเครื่อง</td><td><?= sanitize($ipad['device_code']) ?></td></tr>
    <tr><td>Serial Number</td><td style="font-family:monospace;font-size:12px"><?= sanitize($ipad['serial_number']) ?></td></tr>
    <tr><td>Barcode</td><td style="font-family:monospace"><?= sanitize($ipad['barcode']) ?></td></tr>
  </table>

  <div class="barcode-box">
    <svg id="barcodeSvg"></svg>
    <p style="margin:8px 0 0;font-size:12px;color:#94a3b8"><?= sanitize($ipad['barcode']) ?></p>
  </div>

  <div class="barcode-box" style="margin-top:8px">
    <div id="qrCode"></div>
    <p style="margin:8px 0 0;font-size:12px;color:#94a3b8">QR Code</p>
  </div>

  <div class="btn-row">
    <button class="btn btn-print" onclick="window.print()">🖨️ พิมพ์</button>
    <button class="btn btn-close" onclick="window.close()">ปิด</button>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>
<script>
JsBarcode('#barcodeSvg', '<?= $ipad['barcode'] ?>', {
  format: 'CODE128',
  width: 2,
  height: 60,
  displayValue: false,
  margin: 10
});

QRCode.toCanvas = undefined;
const qrDiv = document.getElementById('qrCode');
const canvas = document.createElement('canvas');
qrDiv.appendChild(canvas);
QRCode.toCanvas(canvas, '<?= $ipad['barcode'] ?>', { width: 120, margin: 1 });
</script>
</body>
</html>
