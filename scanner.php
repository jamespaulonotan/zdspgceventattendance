<?php require_once 'auth.php'; requireLogin(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>ZDSPGC Attendance Scanner</title>
<link rel="stylesheet" href="assets/style.css">
<link rel="stylesheet" href="assets/scanner.css">
<script src="https://kit.fontawesome.com/022f226857.js" crossorigin="anonymous"></script>
<style>
/* ── Result card improvements ── */
.scan-result{border-radius:14px;padding:24px 20px;text-align:center;display:flex;flex-direction:column;align-items:center;gap:6px;animation:fadeIn .3s ease}
.scan-result.hidden{display:none}
.scan-result.ok  {background:#052e16;border:2px solid #16a34a}
.scan-result.warn{background:#1c1307;border:2px solid #d97706}
.scan-result.err {background:#1f0a0a;border:2px solid #dc2626}
.result-icon{line-height:1;margin-bottom:2px}
.scan-result h2{font-size:1.1rem;font-weight:800;color:#fff;margin:0}
#result-name{font-size:1rem!important;font-weight:700;color:#fff!important}
#result-event{font-size:.82rem;color:#94a3b8!important}
#result-time{font-size:.78rem;color:#94a3b8!important}
#result-method{font-size:.75rem;color:#64748b!important;margin-top:2px}
.scan-result p{font-size:.88rem;color:#cbd5e1;margin:0}
</style>
</head>
<body class="scanner-body">
<div class="scanner-app">
  <header class="scanner-header">
    <div class="scanner-brand">
      <div class="scanner-brand-icon"><i class="fa-solid fa-calendar-check fa-lg"></i></div>
      <div class="scanner-brand-text">
        <span class="scanner-title">ZDSPGC Attendance Scanner</span>
        <span class="scanner-subtitle" id="scanner-subtitle">Face Recognition · QR Fallback</span>
      </div>
    </div>
    <a href="<?= ($_SESSION['role']??'') === 'teacher' ? 'teacher_dashboard.php' : 'index.php' ?>" class="back-btn">
      <i class="fa-solid fa-arrow-left"></i> Back
    </a>
  </header>

  <?php if(!isset($_SERVER['HTTPS'])||$_SERVER['HTTPS']!=='on'): ?>
  <div style="background:#1e3a5f;color:#93c5fd;padding:10px 28px;font-size:.82rem;display:flex;align-items:center;gap:10px;flex-wrap:wrap">
    <span><i class="fa-solid fa-mobile-screen-button"></i> <strong>Using your phone?</strong></span>
    <span>Open over HTTPS: <a href="https://<?= $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ?>" style="color:#60a5fa;font-weight:700">https://<?= $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ?></a></span>
  </div>
  <?php endif; ?>

  <div class="mode-tabs">
    <button class="mode-tab face active" data-mode="face" onclick="switchMode('face')"><i class="fa-solid fa-face-smile"></i> Face Recognition</button>
    <button class="mode-tab qr" data-mode="qr" onclick="switchMode('qr')"><i class="fa-solid fa-qrcode"></i> QR Code</button>
  </div>

  <div class="scanner-content">
    <div class="scanner-left">
      <div class="event-select-wrap"><label>Event</label><select id="scanner-event-select" onchange="updateEventStatus()"><option value="">— Select Event —</option></select></div>
      <div class="event-select-wrap"><label>Camera</label><select id="camera-select" onchange="onCameraChange()"><option value="">— Detecting… —</option></select></div>
      <!-- Event status banner -->
      <div id="event-status-banner" style="display:none;align-items:center;gap:10px;padding:10px 14px;border-radius:10px;border:1.5px solid;font-size:.82rem;font-weight:600;margin-bottom:4px;flex-wrap:wrap"></div>

      <!-- FACE MODE -->
      <div id="mode-face">
        <div class="face-scanner-wrap">
          <video id="face-scan-video" autoplay muted playsinline></video>
          <canvas id="face-scan-canvas"></canvas>
          <div class="face-scan-overlay"><div class="face-scan-oval" id="face-oval"></div></div>
        </div>
        <div class="face-confidence-bar"><div class="face-confidence-fill" id="face-conf-fill"></div></div>
        <div class="face-status looking" id="face-status">Loading face recognition models…</div>
        <div style="display:flex;gap:8px;margin-top:4px">
          <button class="btn btn-secondary" style="flex:1;font-size:.82rem" onclick="toggleFacePause()" id="face-pause-btn"><i class="fa-solid fa-pause"></i> Pause</button>
        </div>
        <p style="font-size:.72rem;color:#475569;text-align:center;margin-top:6px">Students must enroll their face in the Student Portal.</p>
      </div>

      <!-- QR MODE -->
      <div id="mode-qr" style="display:none">
        <div class="scan-area" id="scan-area">
          <div id="reader"></div>
          <div class="scan-overlay"><div class="scan-frame"><div class="corner tl"></div><div class="corner tr"></div><div class="corner bl"></div><div class="corner br"></div><div class="scan-line"></div></div></div>
          <p class="scan-hint">Point camera at student's QR code</p>
        </div>
        <div class="manual-input-wrap" style="margin-top:12px">
          <p class="divider-text">— or enter 6-digit ID manually —</p>
          <div class="manual-row">
            <input type="text" id="manual-token" placeholder="e.g. 482031" maxlength="6" onkeydown="if(event.key==='Enter')manualScan()">
            <button class="btn btn-primary" onclick="manualScan()"><i class="fa-solid fa-right-to-bracket"></i> Check In</button>
          </div>
        </div>
      </div>
    </div>

    <div class="scanner-right">
      <div id="scan-result" class="scan-result hidden">
        <div class="result-icon" id="result-icon"></div>
        <h2 id="result-title"></h2>
        <p id="result-name"></p>
        <p id="result-event"></p>
        <p id="result-time" class="result-time"></p>
        <p id="result-method" style="font-size:.72rem;color:#64748b;margin-top:4px"></p>
      </div>
      <div class="scan-log">
        <h3><i class="fa-solid fa-clock-rotate-left" style="margin-right:6px;opacity:.7"></i>Recent Check-ins</h3>
        <ul id="scan-log-list"><li class="log-empty"><i class="fa-solid fa-inbox" style="opacity:.4"></i> No check-ins yet</li></ul>
      </div>
    </div>
  </div>
</div>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
// Absolute models URL — works over ngrok, LAN, localhost
window.MODELS_URL_OVERRIDE = 'https://justadudewhohacks.github.io/face-api.js/models';
</script>
<script src="assets/scanner.js"></script>
</body>
</html>
