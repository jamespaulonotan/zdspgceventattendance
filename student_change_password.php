<?php
session_start();
if(empty($_SESSION['student_id'])){header('Location: student_login.php');exit;}
if(empty($_SESSION['must_change_password'])){header('Location: student_dashboard.php');exit;}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Set Password — ZDSPGC QR Attendance</title>
<link rel="stylesheet" href="assets/login.css">
<script src="https://kit.fontawesome.com/022f226857.js" crossorigin="anonymous"></script>
<style>
.notice-box{background:rgba(234,179,8,.15);border:1px solid rgba(234,179,8,.4);border-radius:10px;padding:12px 16px;font-size:.83rem;color:#fef08a;margin-bottom:18px;display:flex;gap:10px;align-items:flex-start;line-height:1.5}
</style>
</head>
<body>
<div class="login-bg">
  <div class="blob blob-1"></div><div class="blob blob-2"></div><div class="blob blob-3"></div>
  <div class="login-card" style="max-width:420px">
    <div class="login-logo-wrap">
      <img src="assets/logo.png" alt="ZDSPGC Logo" class="login-logo" onerror="this.style.display='none'">
    </div>
    <div class="login-title-wrap">
      <p class="login-school">Zamboanga Del Sur Provincial Government College</p>
      <h1 class="login-title">Set Your Password</h1>
    </div>
    <div class="notice-box">
      <i class="fa-solid fa-triangle-exclamation" style="font-size:1.1rem;flex-shrink:0;margin-top:2px"></i>
      <span>Your account was created with a temporary password. Please set a new personal password before continuing.</span>
    </div>
    <form class="login-form" id="change-pw-form">
      <div id="change-error" class="login-error" style="display:none"></div>
      <div class="login-field">
        <label>New Password *</label>
        <div class="input-wrap">
          <i class="fa-solid fa-lock input-icon" style="width:18px;text-align:center"></i>
          <input type="password" name="new_password" id="new_password"
            placeholder="At least 6 characters" required autocomplete="new-password">
        </div>
      </div>
      <div class="login-field">
        <label>Confirm Password *</label>
        <div class="input-wrap">
          <i class="fa-solid fa-lock input-icon" style="width:18px;text-align:center"></i>
          <input type="password" name="confirm_password" id="confirm_password"
            placeholder="Repeat new password" required autocomplete="new-password">
        </div>
      </div>
      <!-- Password strength bar -->
      <div style="margin-top:-6px;margin-bottom:4px">
        <div style="height:4px;background:rgba(255,255,255,.1);border-radius:2px;overflow:hidden">
          <div id="pw-strength-bar" style="height:100%;width:0;border-radius:2px;transition:width .3s,background .3s"></div>
        </div>
        <p id="pw-strength-label" style="font-size:.75rem;color:rgba(255,255,255,.4);margin:4px 0 0"></p>
      </div>
      <button type="submit" class="login-btn" id="change-btn">
        <i class="fa-solid fa-floppy-disk"></i>
        <span id="change-btn-text">Set New Password</span>
      </button>
    </form>
    <p class="login-footer">© <?= date('Y') ?> ZDSPGC — Official System</p>
  </div>
</div>
<script>
document.getElementById('new_password').addEventListener('input',function(){
  const val=this.value;const bar=document.getElementById('pw-strength-bar');const label=document.getElementById('pw-strength-label');
  let score=0;
  if(val.length>=6)score++;if(val.length>=10)score++;
  if(/[A-Z]/.test(val))score++;if(/[0-9]/.test(val))score++;if(/[^A-Za-z0-9]/.test(val))score++;
  const levels=[{w:'0%',bg:'transparent',text:''},{w:'25%',bg:'#ef4444',text:'Weak'},{w:'50%',bg:'#f97316',text:'Fair'},{w:'75%',bg:'#eab308',text:'Good'},{w:'100%',bg:'#22c55e',text:'Strong'}];
  const lvl=levels[Math.min(score,4)];
  bar.style.width=lvl.w;bar.style.background=lvl.bg;label.textContent=lvl.text;label.style.color=lvl.bg;
});
document.getElementById('change-pw-form').addEventListener('submit',async e=>{
  e.preventDefault();
  const errEl=document.getElementById('change-error');const btn=document.getElementById('change-btn');
  const pw=document.getElementById('new_password').value;const cfm=document.getElementById('confirm_password').value;
  errEl.style.display='none';
  if(pw.length<6){errEl.textContent='Password must be at least 6 characters.';errEl.style.display='block';return;}
  if(pw!==cfm){errEl.textContent='Passwords do not match.';errEl.style.display='block';return;}
  btn.disabled=true;document.getElementById('change-btn-text').textContent='Saving…';
  const fd=new FormData();fd.append('action','student_change_password');fd.append('password',pw);fd.append('confirm',cfm);
  try{
    const res=await fetch('student_api.php',{method:'POST',body:fd});const data=await res.json();
    if(data.success){window.location.href='student_dashboard.php';}
    else{errEl.textContent=data.message||'Failed. Please try again.';errEl.style.display='block';btn.disabled=false;document.getElementById('change-btn-text').textContent='Set New Password';}
  }catch{errEl.textContent='Server error.';errEl.style.display='block';btn.disabled=false;document.getElementById('change-btn-text').textContent='Set New Password';}
});
</script>
</body>
</html>
