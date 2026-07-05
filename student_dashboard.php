<?php
require_once 'auth.php';
if(empty($_SESSION['student_id'])){header('Location: student_login.php');exit;}
if(!empty($_SESSION['must_change_password'])){header('Location: student_change_password.php');exit;}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="csrf-token" content="<?= generateCsrfToken() ?>">
<title>My Dashboard — ZDSPGC</title>
<link rel="stylesheet" href="assets/style.css?v=8">
<link rel="stylesheet" href="assets/student.css?v=<?= time() ?>">
<script src="https://kit.fontawesome.com/022f226857.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<style>
:root{
    --primary:#2d6a4f;
    --primary-dark:#1b4332;
    --accent:#52b788;
}
html, body {
    overflow-x: hidden;
    width: 100%;
}
</style>
</head>
<body>
<div class="app">
<aside class="sidebar" id="sidebar">
  <div class="sidebar-profile">
        <div class="profile-avatar"><i class="fa-solid fa-user-graduate fa-lg"></i></div>
        <div class="profile-info">
          <span class="profile-name" id="s-name">Loading…</span>
          <span class="profile-status"><span class="status-dot"></span> Student</span>
        </div>
      </div>
      <nav class="sidebar-nav">
        <p class="nav-group-label">Overview</p>
        <a href="#" class="nav-item active" data-page="dashboard">
          <i class="fa-solid fa-gauge-high fa-fw"></i> Dashboard
        </a>
        <a href="#" class="nav-item" data-page="events">
          <i class="fa-solid fa-calendar-days fa-fw"></i> Events
        </a>
        <a href="#" class="nav-item" data-page="attendance-log">
          <i class="fa-solid fa-clipboard-check fa-fw"></i> Attendance Log
        </a>
        <p class="nav-group-label" style="margin-top:12px">My Account</p>
        <a href="#" class="nav-item" data-page="qr-code">
          <i class="fa-solid fa-qrcode fa-fw"></i> Show QR Code
        </a>
        <a href="#" class="nav-item" data-page="enroll-face">
          <i class="fa-solid fa-camera-retro fa-fw"></i> Enroll Face
        </a>
      </nav>
  <div class="sidebar-footer">
    <a href="#" class="logout-btn" id="nav-logout">
      <i class="fa-solid fa-right-from-bracket"></i> Log Out
    </a>
    <p>ZDSPGC — Student Portal</p>
  </div>
</aside>
<div class="sidebar-overlay" id="sidebar-overlay"></div>
<div class="main-wrapper">
  <header class="topbar">
    <button class="topbar-hamburger" id="hamburger-btn"><span></span><span></span><span></span></button>
    <div class="topbar-title" id="topbar-title">Dashboard</div>
    <div class="topbar-brand">
      <img src="assets/logo.png" alt="ZDSPGC" class="topbar-brand-logo" onerror="this.style.display='none'">
      <span class="topbar-brand-text">ZDSPGC</span>
    </div>
  </header>
  <main class="main">
    <div id="page-dashboard" class="page active">
      <div class="page-header">
        <div><h1>Dashboard</h1><p class="subtitle">Welcome back, <span id="welcome-name">Loading…</span></p></div>
      </div>
      <div class="dashboard-stats">
        <div class="dash-card">
          <div class="dash-icon green"><i class="fa-solid fa-calendar-days fa-lg"></i></div>
          <div class="dash-info">
            <span class="dash-num" id="s-total-registered">—</span>
            <span class="dash-label">Events Registered</span>
          </div>
          </div>
        <div class="dash-card">
          <div class="dash-icon blue"><i class="fa-solid fa-circle-check fa-lg"></i></div>
          <div class="dash-info">
            <span class="dash-num" id="s-total-attended">—</span>
            <span class="dash-label">Events Attended</span>
          </div>
        </div>
        <div class="dash-card">
          <div class="dash-icon purple"><i class="fa-solid fa-percent fa-lg"></i></div>
          <div class="dash-info">
            <span class="dash-num" id="s-rate">—</span>
            <span class="dash-label">Attendance Rate</span>
          </div>
        </div>
      </div>
      <div style="margin-top:28px">
        <div class="recent-events-card">
          <div class="card-header-row">
            <h3><i class="fa-solid fa-calendar-days" style="color:var(--primary);margin-right:6px"></i>Upcoming Events</h3>
          </div>
          <div id="s-upcoming" style="padding:16px 20px">
            <p class="empty-msg" style="padding:14px">Loading…</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Attendance Log Page -->
    <div id="page-attendance-log" class="page">
      <div class="page-header">
        <div><h1>Attendance Log</h1><p class="subtitle">View all your attendance records</p></div>
        <button class="btn btn-secondary btn-sm" onclick="loadAttendanceLog()"><i class="fa-solid fa-rotate"></i> Refresh</button>
      </div>
      <div style="margin-top:28px">
        <div class="recent-events-card">
          <div class="card-header-row">
            <h3><i class="fa-solid fa-list" style="color:var(--primary);margin-right:6px"></i>Records</h3>
          </div>
          <div id="s-logs" style="padding:16px 20px">
            <p class="empty-msg" style="padding:14px">Loading…</p>
          </div>
        </div>
      </div>
    </div>
    <div id="page-events" class="page">
      <div class="page-header">
        <div><h1>Events</h1><p class="subtitle">View all events</p></div>
        <button class="btn btn-secondary btn-sm" onclick="loadEvents()"><i class="fa-solid fa-rotate"></i> Refresh</button>
      </div>
      <div class="dashboard-stats" id="events-stats">
        <div class="dash-card">
          <div class="dash-icon blue"><i class="fa-solid fa-calendar-days fa-lg"></i></div>
          <div class="dash-info">
            <span class="dash-num" id="s-total-events">—</span>
            <span class="dash-label">Total Events</span>
          </div>
        </div>
        <div class="dash-card clickable" onclick="openEventsModal('started')" style="cursor: pointer;">
          <div class="dash-icon green"><i class="fa-solid fa-play-circle fa-lg"></i></div>
          <div class="dash-info">
            <span class="dash-num" id="s-started-events">—</span>
            <span class="dash-label">Started</span>
          </div>
        </div>
        <div class="dash-card clickable" onclick="openEventsModal('upcoming')" style="cursor: pointer;">
          <div class="dash-icon orange"><i class="fa-solid fa-hourglass-half fa-lg"></i></div>
          <div class="dash-info">
            <span class="dash-num" id="s-upcoming-events">—</span>
            <span class="dash-label">Upcoming</span>
          </div>
        </div>
        <div class="dash-card clickable" onclick="openEventsModal('ended')" style="cursor: pointer;">
          <div class="dash-icon purple"><i class="fa-solid fa-check-circle fa-lg"></i></div>
          <div class="dash-info">
            <span class="dash-num" id="s-ended-events">—</span>
            <span class="dash-label">Ended</span>
          </div>
        </div>
      </div>
      <div style="margin-top:28px">
        <div class="recent-events-card">
          <div class="card-header-row">
            <h3><i class="fa-solid fa-list" style="color:var(--primary);margin-right:6px"></i>All Events</h3>
          </div>
          <div id="s-all-events" style="padding:16px 20px">
            <p class="empty-msg" style="padding:14px">Loading…</p>
          </div>
        </div>
      </div>
    </div>

    <!-- QR Code Page -->
    <div id="page-qr-code" class="page">
      <div class="page-header">
        <div><h1>My QR Code</h1><p class="subtitle">Use this to check in to events</p></div>
      </div>
      <div style="display:flex;flex-direction:column;align-items:center;gap:16px;padding:24px;background:var(--surface);border-radius:var(--radius);border:1px solid var(--border)">
        <div id="qr-code-page-container" style="display:inline-block;background:#fff;padding:16px;border-radius:12px;box-shadow:var(--shadow)"></div>
        <div style="font-family:monospace;font-size:1.2rem;font-weight:800;letter-spacing:4px;color:var(--primary-dark)" id="qr-code-page-id"></div>
        <p class="subtitle" id="qr-code-page-name" style="margin:0"></p>
        <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;margin-top:8px">
          <button class="btn btn-primary" id="qr-page-download"><i class="fa-solid fa-file-arrow-down"></i> Download</button>
          <button class="btn btn-secondary" id="qr-page-print"><i class="fa-solid fa-print"></i> Print</button>
        </div>
      </div>
    </div>

    <!-- Enroll Face Page -->
    <div id="page-enroll-face" class="page">
      <div class="page-header">
        <div><h1>Enroll Your Face</h1><p class="subtitle" id="enroll-face-subtitle">Look into the camera — auto-captures when your face is detected</p></div>
      </div>
      <div id="face-enrolled-prompt" style="display:none;background:#dcfce7;border:1px solid #22c55e;border-radius:var(--radius);padding:20px;text-align:center;margin-bottom:16px">
        <p style="font-weight:700;color:#15803d;margin-bottom:12px"><i class="fa-solid fa-check-circle"></i> You have already enrolled your face!</p>
        <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap">
          <button class="btn btn-secondary" onclick="showFaceEnrollCamera()">Re-enroll Face</button>
        </div>
      </div>
      <div id="face-enroll-camera-section" style="display:flex;flex-direction:column;align-items:center;gap:16px">
        <div style="position:relative;border-radius:12px;overflow:hidden;background:#000;width:100%;max-width:420px;aspect-ratio:1">
          <video id="face-page-video" autoplay muted playsinline style="width:100%;height:100%;object-fit:cover;display:block"></video>
          <canvas id="face-page-canvas" style="position:absolute;inset:0;width:100%;height:100%"></canvas>
          <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;pointer-events:none">
            <div id="face-page-enroll-oval" style="width:160px;height:200px;border:2px dashed rgba(99,102,241,.7);border-radius:50%;transition:border-color .3s,border-style .3s"></div>
          </div>
        </div>
        <div id="face-page-enroll-status" style="text-align:center;font-size:.86rem;color:var(--muted);min-height:24px"></div>
      </div>
    </div>
  </main>
</div>
</div>

<!-- Events Category Modal -->
<div class="modal-overlay" id="events-category-modal" onclick="if(event.target===this)closeEventsCategoryModal()">
  <div class="modal" style="max-width:520px">
    <div class="modal-header">
      <h2 id="events-modal-title">Events</h2>
      <button class="modal-close" onclick="closeEventsCategoryModal()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div style="padding:0 24px 24px">
      <div id="events-modal-content" style="max-height:400px;overflow-y:auto">
        <p class="empty-msg" style="padding:14px">Loading…</p>
      </div>
    </div>
  </div>
</div>

<script>
let currentStudentName='',currentQrToken='',currentFaceEnrolled=false;
function esc(s){return String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}
function getCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute('content') : '';
}
function formatDate(d){return d?new Date(d+'T00:00:00').toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'}):''}
function formatTime(t){if(!t)return'';const[h,m]=t.split(':'),d=new Date();d.setHours(h,m);return d.toLocaleTimeString('en-PH',{hour:'numeric',minute:'2-digit'});}
function formatDateTime(s){return s?new Date(s).toLocaleString('en-PH',{month:'short',day:'numeric',year:'numeric',hour:'numeric',minute:'2-digit'}):''}

// ── Sidebar functions (matching teacher dashboard)
const sidebar=document.getElementById('sidebar');
const overlay=document.getElementById('sidebar-overlay');
const hamBtn=document.getElementById('hamburger-btn');
hamBtn.addEventListener('click',()=>{
  const open=sidebar.classList.toggle('open');
  hamBtn.classList.toggle('open',open);
  overlay.classList.toggle('show',open);
  toggleBodyScroll(open);
});
overlay.addEventListener('click',()=>{
  closeSidebar();
});

function toggleBodyScroll(lock) {
  if (lock) {
    document.documentElement.style.overflow = 'hidden';
    document.body.style.overflow = 'hidden';
    // Prevent touch scrolling on mobile
    document.body.addEventListener('touchmove', preventScroll, { passive: false });
  } else {
    document.documentElement.style.overflow = '';
    document.body.style.overflow = '';
    document.body.removeEventListener('touchmove', preventScroll);
  }
}

function preventScroll(e) {
  e.preventDefault();
}

function closeSidebar(){
  sidebar.classList.remove('open');
  hamBtn.classList.remove('open');
  overlay.classList.remove('show');
  toggleBodyScroll(false);
}

// ── Page navigation
document.querySelectorAll('.nav-item[data-page]').forEach(navItem=>{
  navItem.addEventListener('click',e=>{
    e.preventDefault();
    closeSidebar();
    const page=navItem.getAttribute('data-page');
    switchPage(page);
  });
});

let facePageStream=null,facePageEnrollTimer=null,facePageEnrolling=false;

function switchPage(pageName){
  // Save current page to localStorage
  localStorage.setItem('student_last_page', pageName);
  // Update nav items
  document.querySelectorAll('.nav-item[data-page]').forEach(ni=>{
    ni.classList.toggle('active',ni.getAttribute('data-page')===pageName);
  });
  // Update pages
  document.querySelectorAll('.page').forEach(p=>p.classList.remove('active'));
  const targetPage=document.getElementById(`page-${pageName}`);
  if(targetPage)targetPage.classList.add('active');
  // Update topbar title
  const topbarTitle=document.getElementById('topbar-title');
  if(topbarTitle){
    if(pageName==='qr-code')topbarTitle.textContent='My QR Code';
    else if(pageName==='enroll-face')topbarTitle.textContent='Enroll Your Face';
    else if(pageName==='attendance-log')topbarTitle.textContent='Attendance Log';
    else topbarTitle.textContent=pageName.charAt(0).toUpperCase()+pageName.slice(1);
  }
  // Load page data
  if(pageName==='events')loadEvents();
  else if(pageName==='qr-code')loadQRCodePage();
  else if(pageName==='enroll-face')openFaceEnrollPage();
  else if(pageName==='attendance-log')loadAttendanceLog();
  else stopFaceEnrollPage();
}

function loadQRCodePage(){
  document.getElementById('qr-code-page-name').textContent=currentStudentName;
  document.getElementById('qr-code-page-id').textContent=currentQrToken;
  const mc=document.getElementById('qr-code-page-container');mc.innerHTML='';
  new QRCode(mc,{text:currentQrToken,width:220,height:220,colorDark:'#1b4332',colorLight:'#ffffff',correctLevel:QRCode.CorrectLevel.H});
  
  // Download button
  document.getElementById('qr-page-download').onclick=()=>{
    const canvas=document.querySelector('#qr-code-page-container canvas');if(!canvas){alert('QR code not loaded yet!');return;}
    const pad=16,lh=56,out=document.createElement('canvas');out.width=canvas.width+pad*2;out.height=canvas.height+pad*2+lh;
    const ctx=out.getContext('2d');ctx.fillStyle='#fff';ctx.fillRect(0,0,out.width,out.height);ctx.drawImage(canvas,pad,pad);
    ctx.fillStyle='#1b4332';ctx.font='bold 14px sans-serif';ctx.textAlign='center';ctx.fillText(currentStudentName,out.width/2,canvas.height+pad+20);
    ctx.font='bold 18px monospace';ctx.fillText(currentQrToken,out.width/2,canvas.height+pad+44);
    const a=document.createElement('a');a.href=out.toDataURL('image/png');a.download=`QR-${currentStudentName.replace(/\s+/g,'-')}-${currentQrToken}.png`;a.click();
  };
  
  // Print button
  document.getElementById('qr-page-print').onclick=()=>{
    const canvas=document.querySelector('#qr-code-page-container canvas');const src=canvas?canvas.toDataURL():'';
    const w=window.open('','_blank');
    w.document.write(`<html><head><title>QR — ${currentStudentName}</title><style>body{font-family:sans-serif;text-align:center;padding:40px}.id{font-family:monospace;font-size:22px;font-weight:800;letter-spacing:6px;background:#1b4332;color:#fff;padding:6px 18px;border-radius:6px;display:inline-block;margin-top:8px}</style></head><body><h2>${currentStudentName}</h2>${src?`<br><img src="${src}" width="200" height="200">`:''}<p><span class="id">${currentQrToken}</span></p><script>window.onload=()=>window.print();<\/script></body></html>`);
    w.document.close();
  };
}

async function openFaceEnrollPage(){
  const promptEl=document.getElementById('face-enrolled-prompt');
  const cameraSectionEl=document.getElementById('face-enroll-camera-section');
  
  // Stop any existing camera stream first
  stopFaceEnrollPage();
  
  if(currentFaceEnrolled){
    promptEl.style.display='block';
    cameraSectionEl.style.display='none';
  }else{
    promptEl.style.display='none';
    cameraSectionEl.style.display='flex';
    startFaceEnrollCamera();
  }
}

function showFaceEnrollCamera(){
  const promptEl=document.getElementById('face-enrolled-prompt');
  const cameraSectionEl=document.getElementById('face-enroll-camera-section');
  promptEl.style.display='none';
  cameraSectionEl.style.display='flex';
  startFaceEnrollCamera();
}

async function startFaceEnrollCamera(){
  const statusEl=document.getElementById('face-page-enroll-status');
  const ovalEl=document.getElementById('face-page-enroll-oval');
  const videoEl=document.getElementById('face-page-video');
  const canvasEl=document.getElementById('face-page-canvas');
  
  statusEl.textContent='Loading face models… (first time may take a few seconds)';
  ovalEl.style.borderColor='rgba(99,102,241,.5)';
  window._facePageHoldStart=null;
  facePageEnrolling=false;
  
  const ok=await loadFaceModels();
  if(!ok){
    statusEl.textContent='Could not load face models. Check your connection and try again.';
    return;
  }
  
  try{
    facePageStream=await navigator.mediaDevices.getUserMedia({video:{facingMode:'user',width:{ideal:640},height:{ideal:640}}});
    videoEl.srcObject=facePageStream;await videoEl.play();videoEl.style.transform='scaleX(-1)';
    statusEl.textContent='Position your face inside the oval — auto-captures in 3s';
    stopFacePageEnrollLoop();facePageEnrollTimer=setInterval(runFacePageEnrollDetection,300);
  }catch(e){statusEl.textContent='Camera unavailable: '+e.message;}
}

function stopFaceEnrollPage(){
  stopFacePageEnrollLoop();facePageEnrolling=false;
  if(facePageStream){facePageStream.getTracks().forEach(t=>t.stop());facePageStream=null;}
  const canvas=document.getElementById('face-page-canvas');if(canvas)canvas.getContext('2d').clearRect(0,0,canvas.width,canvas.height);
}

function stopFacePageEnrollLoop(){if(facePageEnrollTimer){clearInterval(facePageEnrollTimer);facePageEnrollTimer=null;}}

async function runFacePageEnrollDetection(){
  if(facePageEnrolling)return;
  const videoEl=document.getElementById('face-page-video');const canvasEl=document.getElementById('face-page-canvas');const statusEl=document.getElementById('face-page-enroll-status');const ovalEl=document.getElementById('face-page-enroll-oval');
  if(!videoEl||videoEl.readyState<2)return;
  const detection=await faceapi.detectSingleFace(videoEl,new faceapi.TinyFaceDetectorOptions({inputSize:224,scoreThreshold:0.5})).withFaceLandmarks(true).withFaceDescriptor();
  canvasEl.width=videoEl.videoWidth;canvasEl.height=videoEl.videoHeight;const ctx=canvasEl.getContext('2d');ctx.clearRect(0,0,canvasEl.width,canvasEl.height);
  if(!detection){ovalEl.style.borderColor='rgba(99,102,241,.5)';ovalEl.style.borderStyle='dashed';if(!facePageEnrolling)statusEl.textContent='Position your face inside the oval — auto-captures in 3s';return;}
  const dims=faceapi.matchDimensions(canvasEl,videoEl,true);faceapi.draw.drawDetections(canvasEl,[faceapi.resizeResults(detection,dims)]);
  ovalEl.style.borderColor='#22c55e';ovalEl.style.borderStyle='solid';
  if(!window._facePageHoldStart)window._facePageHoldStart=Date.now();
  const elapsed=Date.now()-window._facePageHoldStart;
  if(elapsed>=3000){await saveFacePageEnrolledFace(detection);}
  else{statusEl.textContent=`Face detected — capturing in ${Math.ceil((3000-elapsed)/1000)}s… hold still!`;}
}

async function saveFacePageEnrolledFace(detection){
  facePageEnrolling=true;stopFacePageEnrollLoop();
  const statusEl=document.getElementById('face-page-enroll-status');const ovalEl=document.getElementById('face-page-enroll-oval');
  statusEl.textContent='Saving face data…';ovalEl.style.borderColor='#6366f1';
  const descriptor=Array.from(detection.descriptor);const fd=new FormData();fd.append('action','save_face_descriptor');fd.append('csrf_token', getCsrfToken());fd.append('descriptor',JSON.stringify(descriptor));
  try{
    const res=await fetch('student_api.php',{method:'POST',body:fd});const data=await res.json();
    if(data.success){
      currentFaceEnrolled=true;
      statusEl.textContent='Face enrolled! You can now check in using face recognition.';
      ovalEl.style.borderColor='#22c55e';
      // Show the success prompt after a short delay
      setTimeout(() => {
        const promptEl=document.getElementById('face-enrolled-prompt');
        const cameraSectionEl=document.getElementById('face-enroll-camera-section');
        promptEl.style.display='block';
        cameraSectionEl.style.display='none';
        stopFaceEnrollPage();
      }, 2000);
    }else{
      statusEl.textContent=(data.message||'Failed to save. Please try again.');
      facePageEnrolling=false;window._facePageHoldStart=null;facePageEnrollTimer=setInterval(runFacePageEnrollDetection,300);
    }
  }catch(err){
    statusEl.textContent='Network error: '+err.message;
    facePageEnrolling=false;window._facePageHoldStart=null;facePageEnrollTimer=setInterval(runFacePageEnrollDetection,300);
  }
}

function closeSidebar(){
  sidebar.classList.remove('open');
  hamBtn.classList.remove('open');
  overlay.classList.remove('show');
}

// ── Logout
document.getElementById('nav-logout').addEventListener('click',async e=>{
  e.preventDefault();
  closeSidebar();
  const fd=new FormData();fd.append('action','logout');fd.append('csrf_token', getCsrfToken());
  await fetch('student_api.php',{method:'POST',body:fd});
  window.location.href='student_login.php';
});

function getEventStatus(ev){
  if(parseInt(ev.attendance_started||0)) return 'live';
  const now=new Date();
  if(!ev.event_date) return 'upcoming';
  const[yr,mo,dy]=ev.event_date.split('-').map(Number);
  if(ev.event_end_time){
    const[eh,em]=ev.event_end_time.split(':').map(Number);
    const end=new Date(yr,mo-1,dy,eh,em,0);
    if(now>=end) return 'ended';
  }else if(ev.event_time){
    const[sh,sm]=ev.event_time.split(':').map(Number);
    const end=new Date(yr,mo-1,dy,sh,sm+120,0);
    if(now>=end) return 'ended';
  }else{
    const endOfDay=new Date(yr,mo-1,dy,23,59,59);
    if(now>endOfDay) return 'ended';
  }
  return 'upcoming';
}

function openEventsModal(category){
  const events=(window._events||[]);
  const modalTitle=document.getElementById('events-modal-title');
  const modalContent=document.getElementById('events-modal-content');
  
  let filteredEvents=[];
  let title='';
  if(category==='started'){
    title='Started Events';
    filteredEvents=events.filter(ev=>getEventStatus(ev)==='live');
  }else if(category==='upcoming'){
    title='Upcoming Events';
    filteredEvents=events.filter(ev=>getEventStatus(ev)==='upcoming');
  }else if(category==='ended'){
    title='Ended Events';
    filteredEvents=events.filter(ev=>getEventStatus(ev)==='ended');
    filteredEvents.reverse(); // Show most recent first
  }
  
  modalTitle.textContent=title;
  
  if(filteredEvents.length===0){
    modalContent.innerHTML='<p class="empty-msg" style="padding:14px">No events in this category.</p>';
  }else{
    let html='';
    filteredEvents.forEach(ev=>{
      const st=getEventStatus(ev);
      let badge,badgeStyle;
      if(st==='live'){
        badge='<i class="fa-solid fa-circle-dot fa-beat"></i> Live';
        badgeStyle='background:#dcfce7;color:#15803d';
      }else if(st==='upcoming'){
        badge='<i class="fa-solid fa-clock"></i> Upcoming';
        badgeStyle='background:#dbeafe;color:#1d4ed8';
      }else{
        badge='<i class="fa-solid fa-check"></i> Ended';
        badgeStyle='background:#e2e8f0;color:#64748b';
      }
      html+=`<div class="recent-event-row" style="${st==='ended'?'opacity:.65':''}">
        <div>
          <div class="recent-event-name">${esc(ev.name)}</div>
          <div class="recent-event-meta">
            <span class="meta-part"><i class="fa-solid fa-calendar-day"></i> ${formatDate(ev.event_date)}${ev.event_time?' · '+formatTime(ev.event_time):''}</span>
            ${ev.location?`<span class="meta-part"><i class="fa-solid fa-location-dot"></i> ${esc(ev.location)}</span>`:''}
          </div>
        </div>
        <span class="badge" style="${badgeStyle}">${badge}</span>
      </div>`;
    });
    modalContent.innerHTML=html;
  }
  
  document.getElementById('events-category-modal').classList.add('open');
}

function closeEventsCategoryModal(){
  document.getElementById('events-category-modal').classList.remove('open');
}

function renderUpcomingEvents(){
  const upEl=document.getElementById('s-upcoming');
  if(!upEl) return;
  const events=(window._events||[]);
  const stillUpcoming=[],allEnded=[];
  events.forEach(ev=>{
    const st=getEventStatus(ev);
    if(st==='ended') allEnded.push(ev);
    else stillUpcoming.push(ev);
  });
  // Reverse ended to show most recent first
  allEnded.reverse();
  let html='';

  if(stillUpcoming.length){
    html+=stillUpcoming.map(ev=>{
      const status=getEventStatus(ev);
      const badge = status==='live'
        ? `<span class="badge" style="background:#dcfce7;color:#15803d"><i class="fa-solid fa-circle-dot fa-beat"></i> Event Started</span>`
        : `<span class="badge badge-muted"><i class="fa-solid fa-clock"></i> Upcoming</span>`;
      return `<div class="recent-event-row">
        <div>
          <div class="recent-event-name">${esc(ev.name)}</div>
          <div class="recent-event-meta">
            <span class="meta-part"><i class="fa-solid fa-calendar-day"></i> ${formatDate(ev.event_date)}${ev.event_time?' · '+formatTime(ev.event_time):''}</span>
            ${ev.location?`<span class="meta-part"><i class="fa-solid fa-location-dot"></i> ${esc(ev.location)}</span>`:''}
          </div>
        </div>
        ${badge}
      </div>`;
    }).join('');
  } else {
    html+='<p class="empty-msg" style="padding:14px">No upcoming events.</p>';
  }

  if(allEnded.length){
    html+=`<div style="margin-top:16px;padding-top:12px;border-top:1px solid var(--border)">
      <p style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin-bottom:10px;padding-left:8px">
        <i class="fa-solid fa-circle-xmark"></i> Recently Ended
      </p>`;
    html+=allEnded.map(ev=>`
      <div class="recent-event-row" style="opacity:.65">
        <div>
          <div class="recent-event-name">${esc(ev.name)}</div>
          <div class="recent-event-meta">
            <span class="meta-part"><i class="fa-solid fa-calendar-day"></i> ${formatDate(ev.event_date)}${ev.event_time?' · '+formatTime(ev.event_time):''}</span>
            ${ev.location?`<span class="meta-part"><i class="fa-solid fa-location-dot"></i> ${esc(ev.location)}</span>`:''}
          </div>
        </div>
        <span class="badge badge-muted"><i class="fa-solid fa-circle-xmark"></i> Ended</span>
      </div>`).join('');
    html+='</div>';
  }
  upEl.innerHTML=html;
}

async function loadDashboard(){
  const fd=new FormData();fd.append('action','get_dashboard');fd.append('csrf_token', getCsrfToken());
  const res=await fetch('student_api.php',{method:'POST',body:fd});const data=await res.json();
  if(!data.success){window.location.href='student_login.php';return;}
  const s=data.student;
  currentStudentName=s.name;currentQrToken=s.qr_token;currentFaceEnrolled=!!s.face_descriptor;
  document.getElementById('s-name').textContent=s.name;
  document.getElementById('welcome-name').textContent=s.name;
  document.getElementById('s-total-registered').textContent=data.total_registered;
  document.getElementById('s-total-attended').textContent=data.total_attended;
  document.getElementById('s-rate').textContent=data.total_registered>0?Math.round(data.total_attended/data.total_registered*100)+'%':'—';

  window._events=data.events||[];
  renderUpcomingEvents();
  if(window._upcomingTimer)clearInterval(window._upcomingTimer);
  window._upcomingTimer=setInterval(renderUpcomingEvents,30000);
}

async function loadAttendanceLog(){
  const fd=new FormData();fd.append('action','get_dashboard');fd.append('csrf_token', getCsrfToken());
  const res=await fetch('student_api.php',{method:'POST',body:fd});const data=await res.json();
  if(!data.success){return;}
  const logEl=document.getElementById('s-logs');
  logEl.innerHTML=!data.logs.length?'<p class="empty-msg" style="padding:14px">No attendance records yet.</p>':data.logs.map(l=>`
    <div class="recent-event-row">
      <div>
        <div class="recent-event-name">${esc(l.event_name)}</div>
        <div class="recent-event-meta">
          <span class="meta-part"><i class="fa-solid fa-calendar-day"></i> ${formatDate(l.event_date)}</span>
          ${l.location?`<span class="meta-part"><i class="fa-solid fa-location-dot"></i> ${esc(l.location)}</span>`:''}
        </div>
      </div>
      <div style="text-align:right">
        <span class="badge" style="background:#dcfce7;color:#15803d"><i class="fa-solid fa-circle-check"></i> Attended</span>
        <div style="font-size:.75rem;color:var(--muted);margin-top:4px">${formatDateTime(l.scanned_at)}</div>
      </div>
    </div>`).join('');
}

async function loadEvents(){
  const fd=new FormData();fd.append('action','get_events');fd.append('csrf_token', getCsrfToken());
  const res=await fetch('student_api.php',{method:'POST',body:fd});const data=await res.json();
  if(!data.success){return;}
  const events=data.events||[];
  window._events=events; // Store events for modal use
  // Calculate stats
  let total=0,started=0,upcoming=0,ended=0;
  events.forEach(ev=>{
    total++;
    const st=getEventStatus(ev);
    if(st==='live')started++;
    else if(st==='upcoming')upcoming++;
    else if(st==='ended')ended++;
  });
  // Update stats
  document.getElementById('s-total-events').textContent=total;
  document.getElementById('s-started-events').textContent=started;
  document.getElementById('s-upcoming-events').textContent=upcoming;
  document.getElementById('s-ended-events').textContent=ended;
  // Render events
  const allEventsEl=document.getElementById('s-all-events');
  if(allEventsEl){
    if(events.length===0){
      allEventsEl.innerHTML='<p class="empty-msg" style="padding:14px">No events available.</p>';
    }else{
      let html='';
      events.forEach(ev=>{
        const st=getEventStatus(ev);
        let badge,badgeStyle;
        if(st==='live'){
          badge='<i class="fa-solid fa-circle-dot fa-beat"></i> Live';
          badgeStyle='background:#dcfce7;color:#15803d';
        }else if(st==='upcoming'){
          badge='<i class="fa-solid fa-clock"></i> Upcoming';
          badgeStyle='background:#dbeafe;color:#1d4ed8';
        }else{
          badge='<i class="fa-solid fa-check"></i> Ended';
          badgeStyle='background:#e2e8f0;color:#64748b';
        }
        html+=`<div class="recent-event-row" style="${st==='ended'?'opacity:.65':''}">
          <div>
            <div class="recent-event-name">${esc(ev.name)}</div>
            <div class="recent-event-meta">
              <span class="meta-part"><i class="fa-solid fa-calendar-day"></i> ${formatDate(ev.event_date)}${ev.event_time?' · '+formatTime(ev.event_time):''}</span>
              ${ev.location?`<span class="meta-part"><i class="fa-solid fa-location-dot"></i> ${esc(ev.location)}</span>`:''}
            </div>
          </div>
          <span class="badge" style="${badgeStyle}">${badge}</span>
        </div>`;
      });
      allEventsEl.innerHTML=html;
    }
  }
}

// ── Face Enrollment
const MODELS_URL='https://justadudewhohacks.github.io/face-api.js/models';
const AUTO_HOLD_MS=3000;const DETECT_INTERVAL=300;
let faceModelsReady=false;

async function loadFaceModels(){
  if(faceModelsReady)return true;
  try{
    const timeout=new Promise((_,reject)=>setTimeout(()=>reject(new Error('timeout')),30000));
    await Promise.race([
      Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri(MODELS_URL),
        faceapi.nets.faceLandmark68TinyNet.loadFromUri(MODELS_URL),
        faceapi.nets.faceRecognitionNet.loadFromUri(MODELS_URL),
      ]),
      timeout
    ]);
    faceModelsReady=true;return true;
  }catch(e){return false;}
}

// Function to restore last page
function restoreLastPage() {
  const lastPage = localStorage.getItem('student_last_page');
  if (lastPage && lastPage !== 'dashboard') {
    switchPage(lastPage);
  }
}

// Load dashboard then restore last page
loadDashboard().then(() => {
  // Small delay to ensure everything is ready
  setTimeout(restoreLastPage, 100);
});
</script>
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
</body>
</html>
