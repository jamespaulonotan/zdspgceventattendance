<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>ZDSPGC QR Event Attendance System</title>
<link rel="stylesheet" href="assets/login.css">
<script src="https://kit.fontawesome.com/022f226857.js" crossorigin="anonymous"></script>
<style>
.role-tabs{display:flex;gap:4px;background:rgba(255,255,255,.08);border-radius:10px;padding:4px;margin-bottom:4px}
.role-tab{flex:1;padding:8px;border:none;border-radius:8px;background:transparent;color:rgba(255,255,255,.6);font-size:.85rem;font-weight:600;cursor:pointer;transition:background .15s,color .15s;display:flex;align-items:center;justify-content:center;gap:6px}
.role-tab.active{background:rgba(82,183,136,.25);color:#52b788}
.role-tab:hover:not(.active){color:#fff}
</style>
</head>
<body>
<div class="login-bg">
  <div class="blob blob-1"></div><div class="blob blob-2"></div><div class="blob blob-3"></div>
  <div class="login-card">
    <div class="login-logo-wrap">
      <img src="assets/logo.png" alt="ZDSPGC Logo" class="login-logo"
           onerror="this.style.display='none';document.getElementById('logo-fallback').style.display='flex'">
      <div id="logo-fallback" class="logo-fallback" style="display:none">
        <i class="fa-solid fa-graduation-cap fa-3x" style="color:#a7f3d0"></i>
      </div>
    </div>
    <div class="login-title-wrap">
      <p class="login-school">ZDSPGC V. SAGUN CAMPUS</p>
      <h1 class="login-title">Event Attendance System</h1>
    </div>
    <div class="role-tabs">
      <button class="role-tab active" data-role="admin" id="tab-admin">
        <i class="fa-solid fa-user-shield"></i> Admin
      </button>
      <button class="role-tab" data-role="teacher" id="tab-teacher">
        <i class="fa-solid fa-chalkboard-user"></i> Teacher
      </button>
    </div>
    <form class="login-form" id="login-form">
      <input type="hidden" id="login-role" value="admin">
      <div id="login-error" class="login-error" style="display:none"></div>
      <div class="login-field">
        <label id="username-label">Username</label>
        <div class="input-wrap">
          <i class="fa-solid fa-id-card input-icon" style="width:18px;text-align:center"></i>
          <input type="text" id="username" name="username" placeholder="Enter username" autocomplete="username" required>
        </div>
      </div>
      <div class="login-field">
        <label>Password</label>
        <div class="input-wrap">
          <i class="fa-solid fa-lock input-icon" style="width:18px;text-align:center"></i>
          <input type="password" id="password" name="password" placeholder="Enter password" required>
          <button type="button" class="toggle-pw" id="toggle-pw" aria-label="Show password">
            <i id="eye-show" class="fa-solid fa-eye"></i>
            <i id="eye-hide" class="fa-solid fa-eye-slash" style="display:none"></i>
          </button>
        </div>
      </div>
      <button type="submit" class="login-btn" id="login-btn">
        <span id="login-btn-text">Log In</span>
        <i id="login-spinner" class="fa-solid fa-spinner fa-spin" style="display:none"></i>
      </button>
      <button type="button" class="forgot-btn-full" id="forgot-btn" style="width:100%;padding:9px;background:#1b4332;color:#52b788;border:none;border-radius:10px;font-size:.85rem;font-weight:650;cursor:pointer;letter-spacing:.02em;box-shadow:0 4px 16px rgba(82,183,136,.35);display:flex;align-items:center;justify-content:center;margin-top:0">
        Forgot Password?
      </button>
    </form>
    <p id="register-link" style="text-align:center;margin-top:10px;font-size:.82rem;color:rgba(255,255,255,.45);display:none">
      New teacher? <a href="teacher_register.php" style="color:#95d5b2;font-weight:600;text-decoration:underline">Register here</a>
    </p>
    <p class="login-footer">© <?= date('Y') ?> ZDSPGC &mdash; Event Attendance System</p>
  </div>
</div>

<!-- Forgot Password Modal -->
<div class="fp-overlay" id="fp-overlay">
  <div class="fp-modal">
    <button class="fp-close" id="fp-close"><i class="fa-solid fa-xmark"></i></button>
    <!-- Admin forgot -->
    <div id="fp-admin">
      <div class="fp-icon"><i class="fa-solid fa-key fa-2x" style="color:#52b788"></i></div>
      <h2>Reset Password</h2>
      <p class="fp-sub">Enter the recovery key to set a new admin password.</p>
      <div id="fp-error" class="login-error" style="display:none"></div>
      <div class="fp-field"><label>Recovery Key</label><input type="text" id="fp-key" placeholder="Enter recovery key" autocomplete="off"></div>
      <button class="login-btn" id="fp-verify-btn" style="margin-top:8px">Verify Key</button>
      <p class="fp-hint">Default key: <code>ZDSPGC-RESET</code></p>
    </div>
    <div id="fp-admin-step2" style="display:none">
      <div class="fp-icon"><i class="fa-solid fa-lock fa-2x" style="color:#52b788"></i></div>
      <h2>New Password</h2>
      <p class="fp-sub">Choose a new admin password.</p>
      <div id="fp-success" class="fp-success" style="display:none"></div>
      <div id="fp-error-2" class="login-error" style="display:none"></div>
      <div class="fp-field"><label>New Password</label><input type="password" id="fp-new-pw" placeholder="At least 6 characters"></div>
      <div class="fp-field"><label>Confirm Password</label><input type="password" id="fp-confirm-pw" placeholder="Repeat new password"></div>
      <button class="login-btn" id="fp-save-btn" style="margin-top:8px">Save New Password</button>
    </div>
    <!-- Teacher forgot -->
    <div id="fp-teacher" style="display:none">
      <div class="fp-icon"><i class="fa-solid fa-key fa-2x" style="color:#52b788"></i></div>
      <h2>Forgot Password?</h2>
      <p class="fp-sub">Enter your Teacher ID and registered email. The admin will send you a temporary password.</p>
      <div id="fp-teacher-err" class="login-error" style="display:none"></div>
      <div id="fp-teacher-success" class="fp-success" style="display:none"></div>
      <div class="fp-field"><label>Teacher ID</label><input type="text" id="fp-teacher-id" placeholder="e.g. T-2024-001"></div>
      <div class="fp-field"><label>Email Address</label><input type="email" id="fp-teacher-email" placeholder="juan@email.com"></div>
      <button class="login-btn" id="fp-teacher-submit" style="margin-top:8px">Submit Request</button>
    </div>
  </div>
</div>
<script src="assets/login.js"></script>
<script>
document.querySelectorAll('.role-tab').forEach(tab=>{
  tab.addEventListener('click',()=>{
    document.querySelectorAll('.role-tab').forEach(t=>t.classList.remove('active'));
    tab.classList.add('active');
    const role=tab.dataset.role;
    document.getElementById('login-role').value=role;
    document.getElementById('login-error').style.display='none';
    document.getElementById('username').value='';
    document.getElementById('password').value='';
    const isTeacher=role==='teacher';
    document.getElementById('register-link').style.display=isTeacher?'block':'none';
    document.getElementById('username-label').textContent=isTeacher?'Teacher ID':'Username';
    document.getElementById('username').placeholder=isTeacher?'Enter your Teacher ID':'Enter username';
  });
});

// Teacher forgot password submit
document.getElementById('fp-teacher-submit').addEventListener('click',async()=>{
  const tid=document.getElementById('fp-teacher-id').value.trim();
  const email=document.getElementById('fp-teacher-email').value.trim();
  const errEl=document.getElementById('fp-teacher-err');
  const succEl=document.getElementById('fp-teacher-success');
  const btn=document.getElementById('fp-teacher-submit');
  errEl.style.display='none';succEl.style.display='none';
  if(!tid||!email){errEl.textContent='Please fill in both fields.';errEl.style.display='block';return;}
  btn.disabled=true;btn.textContent='Submitting…';
  const fd=new FormData();fd.append('action','teacher_forgot_verify');fd.append('teacher_id',tid);fd.append('email',email);
  try{
    const res=await fetch('api.php',{method:'POST',body:fd});const data=await res.json();
    if(data.success){
      succEl.textContent=data.message||'Request submitted! The admin will email you a temporary password.';
      succEl.style.display='block';btn.textContent='Request Sent';
      setTimeout(()=>{document.getElementById('fp-overlay').classList.remove('open');btn.disabled=false;btn.textContent='Submit Request';},3000);
    } else {
      // Live countdown if cooldown message
      const minMatch=data.message&&data.message.match(/(\d+)\s*min\s*(?:(\d+)\s*sec)?/);
      const secMatch=data.message&&data.message.match(/(\d+)\s*seconds/);
      let secs=0;
      if(minMatch){secs=parseInt(minMatch[1])*60+(parseInt(minMatch[2])||0);}
      else if(secMatch){secs=parseInt(secMatch[1]);}
      if(secs>0){
        errEl.style.display='block';btn.disabled=true;
        if(window._tchCountdown)clearInterval(window._tchCountdown);
        const tick=()=>{
          const m=Math.floor(secs/60),s=secs%60;
          errEl.textContent=m>0?`Please wait ${m} min ${s>0?s+' sec ':''} before submitting again.`:`Please wait ${s} second${s!==1?'s':''} before submitting again.`;
        };
        tick();
        window._tchCountdown=setInterval(()=>{
          secs--;
          if(secs<=0){clearInterval(window._tchCountdown);errEl.style.display='none';btn.disabled=false;btn.textContent='Submit Request';}
          else tick();
        },1000);
      } else {
        errEl.textContent=data.message;errEl.style.display='block';
        btn.disabled=false;btn.textContent='Submit Request';
      }
    }
  }catch{
    errEl.textContent='Server error. Please try again.';errEl.style.display='block';
    btn.disabled=false;btn.textContent='Submit Request';
  }
});
</script>
</body>
</html>
