<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Login — ZDSPGC QR Attendance</title>
<link rel="stylesheet" href="assets/login.css">
<script src="https://kit.fontawesome.com/022f226857.js" crossorigin="anonymous"></script>
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
      <h1 class="login-title">Student Login</h1>
    </div>
    <form class="login-form" id="login-form">
      <div id="login-error" class="login-error" style="display:none"></div>
      <div class="login-field">
        <label>Student ID</label>
        <div class="input-wrap">
          <i class="fa-solid fa-id-card input-icon" style="width:18px;text-align:center"></i>
          <input type="text" name="username" placeholder="Ex. 25VS****" autocomplete="username" required>
        </div>
      </div>
      <div class="login-field">
        <label>Password</label>
        <div class="input-wrap">
          <i class="fa-solid fa-lock input-icon" style="width:18px;text-align:center"></i>
          <input type="password" name="password" placeholder="Enter password" required>
          <button type="button" class="toggle-pw" id="toggle-pw" aria-label="Show password">
            <i id="eye-show" class="fa-solid fa-eye"></i>
            <i id="eye-hide" class="fa-solid fa-eye-slash" style="display:none"></i>
          </button>
        </div>
      </div>
      <button type="submit" class="login-btn" id="login-btn">
        <span id="login-btn-text">Log In</span>
      </button>
      <button type="button" class="forgot-btn-full" id="forgot-btn" style="width:100%;padding:9px;background:#1b4332;color:#52b788;border:none;border-radius:10px;font-size:.85rem;font-weight:650;cursor:pointer;letter-spacing:.02em;box-shadow:0 4px 16px rgba(82,183,136,.35);display:flex;align-items:center;justify-content:center;margin-top:0">
        Forgot Password?
      </button>
    </form>
    <p style="text-align:center;margin-top:12px;font-size:.82rem;color:rgba(255,255,255,.45)">
      Not registered? <a href="student_register.php" style="color:#95d5b2;font-weight:600;text-decoration:underline">Register here</a>
    </p>
    <p class="login-footer">© <?= date('Y') ?> ZDSPGC — Event Attendance System</p>
  </div>
</div>

<!-- Forgot Password Modal -->
<div class="fp-overlay" id="fp-overlay">
  <div class="fp-modal">
    <button class="fp-close" id="fp-close"><i class="fa-solid fa-xmark"></i></button>
    <div id="fp-step-1">
      <div class="fp-icon"><i class="fa-solid fa-key fa-2x" style="color:#52b788"></i></div>
      <h2>Forgot Password?</h2>
      <p class="fp-sub">Enter your Student ID and registered email. The admin will send you a temporary password.</p>
      <div id="fp-err-1" class="login-error" style="display:none"></div>
      <div id="fp-success-1" class="fp-success" style="display:none"></div>
      <div class="fp-field"><label>Student ID</label><input type="text" id="fp-username" placeholder="e.g. 25VS0001"></div>
      <div class="fp-field"><label>Email Address</label><input type="email" id="fp-phone" placeholder="juan@email.com"></div>
      <button class="login-btn" id="fp-verify-btn" style="margin-top:8px">Submit Request</button>
    </div>
  </div>
</div>

<script>
document.getElementById('toggle-pw').addEventListener('click',()=>{
  const input=document.querySelector('input[name="password"]');const show=input.type==='password';
  input.type=show?'text':'password';
  document.getElementById('eye-show').style.display=show?'none':'';
  document.getElementById('eye-hide').style.display=show?'':'none';
});
document.getElementById('login-form').addEventListener('submit',async(e)=>{
  e.preventDefault();
  const btn=document.getElementById('login-btn');const errEl=document.getElementById('login-error');
  btn.disabled=true;document.getElementById('login-btn-text').textContent='Logging in…';errEl.style.display='none';
  const fd=new FormData(e.target);fd.append('action','login');
  try{
    const res=await fetch('student_api.php',{method:'POST',body:fd});const data=await res.json();
    if(data.success){window.location.href=data.must_change_password?'student_change_password.php':'student_dashboard.php';}
    else{errEl.textContent=data.message;errEl.style.display='block';btn.disabled=false;document.getElementById('login-btn-text').textContent='Log In';document.querySelector('.login-card').classList.add('shake');setTimeout(()=>document.querySelector('.login-card').classList.remove('shake'),500);}
  }catch{errEl.textContent='Server error.';errEl.style.display='block';btn.disabled=false;document.getElementById('login-btn-text').textContent='Log In';}
});
const fpOverlay=document.getElementById('fp-overlay');
document.getElementById('forgot-btn').addEventListener('click',()=>{
  fpOverlay.classList.add('open');
  document.getElementById('fp-err-1').style.display='none';
  document.getElementById('fp-success-1').style.display='none';
  document.getElementById('fp-username').value='';
  document.getElementById('fp-phone').value='';
});
document.getElementById('fp-close').addEventListener('click',()=>fpOverlay.classList.remove('open'));
fpOverlay.addEventListener('click',e=>{if(e.target===fpOverlay)fpOverlay.classList.remove('open');});

document.getElementById('fp-verify-btn').addEventListener('click',async()=>{
  const username=document.getElementById('fp-username').value.trim();
  const phone=document.getElementById('fp-phone').value.trim();
  const errEl=document.getElementById('fp-err-1');
  const succEl=document.getElementById('fp-success-1');
  const btn=document.getElementById('fp-verify-btn');
  errEl.style.display='none';succEl.style.display='none';
  if(!username||!phone){errEl.textContent='Please fill in both fields.';errEl.style.display='block';return;}
  btn.disabled=true;btn.textContent='Submitting…';
  const fd=new FormData();fd.append('action','forgot_verify');fd.append('username',username);fd.append('phone',phone);
  try{
    const res=await fetch('student_api.php',{method:'POST',body:fd});const data=await res.json();
    if(data.success){
      succEl.textContent=data.message||'Request submitted! The admin will email you a temporary password.';
      succEl.style.display='block';
      btn.textContent='Request Sent';
      // auto-close after 3s
      setTimeout(()=>{fpOverlay.classList.remove('open');btn.disabled=false;btn.textContent='Submit Request';},3000);
    } else {
      // Parse remaining seconds from cooldown message and do live countdown
      const minMatch=data.message&&data.message.match(/(\d+)\s*min\s*(?:(\d+)\s*sec)?/);
      const secMatch=data.message&&data.message.match(/(\d+)\s*seconds/);
      let secs=0;
      if(minMatch){secs=parseInt(minMatch[1])*60+(parseInt(minMatch[2])||0);}
      else if(secMatch){secs=parseInt(secMatch[1]);}
      if(secs>0){
        errEl.style.display='block';btn.disabled=true;
        if(window._stuCountdown)clearInterval(window._stuCountdown);
        const tick=()=>{
          const m=Math.floor(secs/60),s=secs%60;
          errEl.textContent=m>0?`Please wait ${m} min ${s>0?s+' sec ':''} before submitting again.`:`Please wait ${s} second${s!==1?'s':''} before submitting again.`;
        };
        tick();
        window._stuCountdown=setInterval(()=>{
          secs--;
          if(secs<=0){clearInterval(window._stuCountdown);errEl.style.display='none';btn.disabled=false;btn.textContent='Submit Request';}
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
