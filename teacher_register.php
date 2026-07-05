<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Teacher Registration — ZDSPGC QR Attendance</title>
<link rel="stylesheet" href="assets/login.css">
<script src="https://kit.fontawesome.com/022f226857.js" crossorigin="anonymous"></script>
<style>
.login-card{max-width:460px}
.login-form{gap:14px}
.success-card{text-align:center;display:none;flex-direction:column;align-items:center;gap:14px}
.success-note{font-size:.82rem;color:#95d5b2}
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
      <h1 class="login-title">Teacher Registration</h1>
    </div>

    <form class="login-form" id="reg-form">
      <div id="reg-error" class="login-error" style="display:none"></div>

      <div class="login-field">
        <label>Teacher ID *</label>
        <div class="input-wrap">
          <i class="fa-solid fa-id-card input-icon" style="width:18px;text-align:center"></i>
          <input type="text" name="teacher_id" placeholder="Ex. 25VS****" autocomplete="off" required>
        </div>
      </div>

      <div class="login-field">
        <label>Full Name *</label>
        <div class="input-wrap">
          <i class="fa-solid fa-user input-icon" style="width:18px;text-align:center"></i>
          <input type="text" name="display_name" placeholder="Juan Dela Cruz" required>
        </div>
      </div>

      <div class="login-field">
        <label>Email Address *</label>
        <div class="input-wrap">
          <i class="fa-solid fa-envelope input-icon" style="width:18px;text-align:center"></i>
          <input type="email" name="email" placeholder="juan@email.com" required>
        </div>
      </div>

      <div class="login-field">
        <label>Password *</label>
        <div class="input-wrap">
          <i class="fa-solid fa-lock input-icon" style="width:18px;text-align:center"></i>
          <input type="password" name="password" id="input-password" placeholder="At least 6 characters" required>
          <button type="button" class="toggle-pw" onclick="togglePw('input-password','eye-pw-show','eye-pw-hide')" aria-label="Show password">
            <i id="eye-pw-show" class="fa-solid fa-eye"></i>
            <i id="eye-pw-hide" class="fa-solid fa-eye-slash" style="display:none"></i>
          </button>
        </div>
      </div>

      <div class="login-field">
        <label>Confirm Password *</label>
        <div class="input-wrap">
          <i class="fa-solid fa-lock input-icon" style="width:18px;text-align:center"></i>
          <input type="password" name="confirm" id="input-confirm" placeholder="Repeat password" required>
          <button type="button" class="toggle-pw" onclick="togglePw('input-confirm','eye-cf-show','eye-cf-hide')" aria-label="Show confirm password">
            <i id="eye-cf-show" class="fa-solid fa-eye"></i>
            <i id="eye-cf-hide" class="fa-solid fa-eye-slash" style="display:none"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="login-btn" id="reg-btn">
        <span id="reg-btn-text">Register</span>
      </button>
    </form>

    <div class="success-card" id="success-card">
      <i class="fa-solid fa-circle-check fa-3x" style="color:#52b788"></i>
      <h2 style="color:#fff;font-size:1.2rem">Registration Successful!</h2>
      <p class="success-note">Your account has been created. You can now log in.</p>
      <a href="login.php" class="login-btn" style="text-decoration:none;margin-top:8px">
        <i class="fa-solid fa-right-to-bracket"></i> Go to Login
      </a>
    </div>

    <p style="text-align:center;margin-top:10px;font-size:.82rem;color:rgba(255,255,255,.45)">
      Already registered? <a href="login.php" style="color:#95d5b2;font-weight:600;text-decoration:underline">Log in here</a>
    </p>
    <p class="login-footer">© <?= date('Y') ?> ZDSPGC — Event Attendance System</p>
  </div>
</div>

<script>
function togglePw(inputId,showIconId,hideIconId){
  const inp=document.getElementById(inputId);const show=inp.type==='password';
  inp.type=show?'text':'password';
  document.getElementById(showIconId).style.display=show?'none':'';
  document.getElementById(hideIconId).style.display=show?'':'none';
}

document.getElementById('reg-form').addEventListener('submit',async(e)=>{
  e.preventDefault();
  const btn=document.getElementById('reg-btn');
  const errEl=document.getElementById('reg-error');
  btn.disabled=true;document.getElementById('reg-btn-text').textContent='Registering…';errEl.style.display='none';
  const fd=new FormData(e.target);fd.append('action','teacher_register');
  try{
    const res=await fetch('api.php',{method:'POST',body:fd});
    const data=await res.json();
    if(data.success){
      document.getElementById('reg-form').style.display='none';
      document.getElementById('success-card').style.display='flex';
    } else {
      errEl.textContent=data.message;errEl.style.display='block';
      btn.disabled=false;document.getElementById('reg-btn-text').textContent='Register';
    }
  }catch{
    errEl.textContent='Server error.';errEl.style.display='block';
    btn.disabled=false;document.getElementById('reg-btn-text').textContent='Register';
  }
});
</script>
</body>
</html>
