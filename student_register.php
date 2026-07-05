<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Registration — ZDSPGC Attendance </title>
<link rel="stylesheet" href="assets/login.css">
<script src="https://kit.fontawesome.com/022f226857.js" crossorigin="anonymous"></script>
<style>
.login-card{max-width:480px}
.form-row-2{display:flex;gap:12px}
.form-row-2 .login-field{flex:1}
@media(max-width:480px){.form-row-2{flex-direction:column;gap:12px}}
.login-form{gap:12px}
.id-lookup-box{border-radius:8px;padding:10px 14px;font-size:.82rem;margin-top:6px;display:none;line-height:1.5}
.id-lookup-box.found{background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;display:block}
.id-lookup-box.not-found{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;display:block}
.id-lookup-box.already-exists{background:#fef3c7;color:#92400e;border:1px solid #fde68a;display:block}
.id-lookup-box.looking{background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;display:block}
.autofilled input,.autofilled select,.autofilled .cdd{background:rgba(82,183,136,.12)!important;border-color:#52b788!important}
.success-card{text-align:center;display:none;flex-direction:column;align-items:center;gap:14px}
.success-note{font-size:.82rem;color:#95d5b2}
/* Custom dropdown */
.cdd{width:100%;padding:11px 42px 11px 40px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:10px;color:#fff;font-size:.92rem;cursor:pointer;display:flex;align-items:center;position:relative;transition:border-color .2s,background .2s;box-sizing:border-box;user-select:none}
.cdd:focus,.cdd.open{outline:none;border-color:#52b788;background:rgba(255,255,255,.12)}
.cdd-selected{flex:1;color:rgba(255,255,255,.35);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.cdd-selected.chosen{color:#fff}
.cdd-chevron{position:absolute;right:13px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.45);pointer-events:none;font-size:.8rem;transition:transform .2s}
.cdd.open .cdd-chevron{transform:translateY(-50%) rotate(180deg)}
.cdd-list{display:none;position:absolute;top:calc(100% + 4px);left:0;right:0;background:#1a3d2b;border:1px solid rgba(82,183,136,.3);border-radius:10px;overflow:hidden;z-index:100;box-shadow:0 8px 24px rgba(0,0,0,.4);max-height:220px;overflow-y:auto}
.cdd.open .cdd-list{display:block}
.cdd-item{padding:10px 16px;color:rgba(255,255,255,.8);font-size:.9rem;cursor:pointer;transition:background .12s}
.cdd-item:hover,.cdd-item.active{background:rgba(82,183,136,.2);color:#fff}
/* make login-field containing a cdd relative so dropdown positions correctly */
.login-field:has(.cdd){position:relative}
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
      <h1 class="login-title">Student Registration</h1>
    </div>
    <form class="login-form" id="reg-form">
      <div id="reg-error" class="login-error" style="display:none"></div>

      <!-- Student ID with live lookup -->
      <div class="login-field">
        <label>Student ID *</label>
        <div class="input-wrap">
          <i class="fa-solid fa-id-card input-icon" style="width:18px;text-align:center"></i>
          <input type="text" name="username" id="input-username" placeholder="Ex. 25VS****" autocomplete="off" required>
        </div>
        <div class="id-lookup-box" id="id-lookup-box"></div>
      </div>

      <div class="login-field" id="field-name">
        <label>Full Name *</label>
        <div class="input-wrap">
          <i class="fa-solid fa-user input-icon" style="width:18px;text-align:center"></i>
          <input type="text" name="name" id="input-name" placeholder="Juan Dela Cruz" required>
        </div>
      </div>

      <div class="login-field">
        <label>Email Address *</label>
        <div class="input-wrap">
          <i class="fa-solid fa-envelope input-icon" style="width:18px;text-align:center"></i>
          <input type="email" name="phone" placeholder="juan@email.com" required>
        </div>
      </div>

      <div class="form-row-2">
        <div class="login-field" id="field-course">
          <label>Program *</label>
          <input type="hidden" name="course" id="input-course">
          <div class="cdd" id="cdd-course" tabindex="0">
            <i class="fa-solid fa-book" style="position:absolute;left:13px;color:#52b788;font-size:.9rem"></i>
            <div class="cdd-selected" data-placeholder="— Program —">— Program —</div>
            <i class="fa-solid fa-chevron-down cdd-chevron"></i>
            <div class="cdd-list">
              <div class="cdd-item" data-val="BTVTED">BTVTED</div>
              <div class="cdd-item" data-val="ACT">ACT</div>
              <div class="cdd-item" data-val="BSIS">BSIS</div>
            </div>
          </div>
        </div>
        <div class="login-field" id="field-year">
          <label>Year Level *</label>
          <input type="hidden" name="year_level" id="input-year">
          <div class="cdd" id="cdd-year" tabindex="0">
            <i class="fa-solid fa-layer-group" style="position:absolute;left:13px;color:#52b788;font-size:.9rem"></i>
            <div class="cdd-selected" data-placeholder="— Year Level —">— Year Level —</div>
            <i class="fa-solid fa-chevron-down cdd-chevron"></i>
            <div class="cdd-list">
              <div class="cdd-item" data-val="1">1st Year</div>
              <div class="cdd-item" data-val="2">2nd Year</div>
              <div class="cdd-item" data-val="3">3rd Year</div>
              <div class="cdd-item" data-val="4">4th Year</div>
            </div>
          </div>
        </div>
      </div>

      <div class="login-field" id="field-block">
        <label>Block *</label>
        <input type="hidden" name="block" id="input-block">
        <div class="cdd" id="cdd-block" tabindex="0">
          <i class="fa-solid fa-table-cells" style="position:absolute;left:13px;color:#52b788;font-size:.9rem"></i>
          <div class="cdd-selected" data-placeholder="— Block —">— Block —</div>
          <i class="fa-solid fa-chevron-down cdd-chevron"></i>
          <div class="cdd-list">
            <div class="cdd-item" data-val="Lone">Lone</div>
            <div class="cdd-item" data-val="A">A</div>
            <div class="cdd-item" data-val="B">B</div>
            <div class="cdd-item" data-val="C">C</div>
            <div class="cdd-item" data-val="D">D</div>
          </div>
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
      <i class="fa-solid fa-hourglass-half fa-3x" style="color:#52b788"></i>
      <h2 style="color:#fff;font-size:1.2rem">Registration Submitted!</h2>
      <p class="success-note">Your account is <strong style="color:#52b788">pending admin approval</strong>.</p>
      <p class="success-note">You will be able to log in once approved.</p>
      <a href="student_login.php" class="login-btn" style="text-decoration:none;margin-top:8px">
        <i class="fa-solid fa-right-to-bracket"></i> Go to Login
      </a>
    </div>

    <p style="text-align:center;margin-top:10px;font-size:.82rem;color:rgba(255,255,255,.45)">
      Already registered? <a href="student_login.php" style="color:#95d5b2;font-weight:600;text-decoration:underline">Log in here</a>
    </p>
    <p class="login-footer">© <?= date('Y') ?> ZDSPGC — Event Attendace System</p>
  </div>
</div>

<script>
function togglePw(inputId,showIconId,hideIconId){
  const inp=document.getElementById(inputId);const show=inp.type==='password';
  inp.type=show?'text':'password';
  document.getElementById(showIconId).style.display=show?'none':'';
  document.getElementById(hideIconId).style.display=show?'':'none';
}

/* ── Custom dropdown (cdd) ── */
document.querySelectorAll('.cdd').forEach(cdd=>{
  const sel=cdd.querySelector('.cdd-selected');
  const list=cdd.querySelector('.cdd-list');
  const hidden=cdd.parentElement.querySelector('input[type=hidden]');

  cdd.addEventListener('click',e=>{
    e.stopPropagation();
    // close all others
    document.querySelectorAll('.cdd.open').forEach(c=>{if(c!==cdd)c.classList.remove('open');});
    cdd.classList.toggle('open');
  });

  list.querySelectorAll('.cdd-item').forEach(item=>{
    item.addEventListener('click',e=>{
      e.stopPropagation();
      const val=item.dataset.val;
      const label=item.textContent;
      hidden.value=val;
      sel.textContent=label;
      sel.classList.add('chosen');
      list.querySelectorAll('.cdd-item').forEach(i=>i.classList.remove('active'));
      item.classList.add('active');
      cdd.classList.remove('open');
      // trigger autofill highlight if field has id
      const fieldId=cdd.closest('.login-field')?.id;
      if(fieldId){const el=document.getElementById(fieldId);if(el)el.classList.add('autofilled');}
    });
  });
});
document.addEventListener('click',()=>document.querySelectorAll('.cdd.open').forEach(c=>c.classList.remove('open')));

let lookupTimer=null;
const idInput=document.getElementById('input-username');
const lookupBox=document.getElementById('id-lookup-box');
const nameInput=document.getElementById('input-name');
const courseInput=document.getElementById('input-course');
const yearInput=document.getElementById('input-year');
const blockInput=document.getElementById('input-block');

function setAutoFill(field,value){
  const inp=field==='name'?nameInput:field==='course'?courseInput:field==='year'?yearInput:blockInput;
  const el=document.getElementById('field-'+field);
  inp.value=value;
  if(el)el.classList.add('autofilled');
  // Update custom dropdown display if applicable
  if(field==='course'||field==='year'||field==='block'){
    const cddId='cdd-'+(field==='course'?'course':field==='year'?'year':'block');
    const cdd=document.getElementById(cddId);
    if(cdd){
      const item=[...cdd.querySelectorAll('.cdd-item')].find(i=>i.dataset.val===value);
      const sel=cdd.querySelector('.cdd-selected');
      if(item&&sel){sel.textContent=item.textContent;sel.classList.add('chosen');}
    }
  }
}
function clearAutoFill(){
  ['name','course','year','block'].forEach(f=>{
    const el=document.getElementById('field-'+f);if(el)el.classList.remove('autofilled');
  });
  // Reset custom dropdowns
  ['course','year','block'].forEach(f=>{
    const cdd=document.getElementById('cdd-'+f);
    if(cdd){
      const sel=cdd.querySelector('.cdd-selected');
      if(sel){sel.textContent=sel.dataset.placeholder;sel.classList.remove('chosen');}
      cdd.querySelectorAll('.cdd-item').forEach(i=>i.classList.remove('active'));
      const hidden=cdd.parentElement.querySelector('input[type=hidden]');
      if(hidden)hidden.value='';
    }
  });
}
function escHtml(s){return String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}

idInput.addEventListener('input',()=>{
  clearTimeout(lookupTimer);const val=idInput.value.trim();
  if(val.length<3){lookupBox.className='id-lookup-box';clearAutoFill();return;}
  lookupBox.className='id-lookup-box looking';
  lookupBox.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Looking up Student ID…';
  lookupTimer=setTimeout(()=>doLookup(val),500);
});

async function doLookup(id){
  try{
    const res=await fetch('student_api.php?action=lookup_roster&student_id='+encodeURIComponent(id));
    const data=await res.json();
    if(data.success){
      const d=data.data;lookupBox.className='id-lookup-box found';
      lookupBox.innerHTML=`<i class="fa-solid fa-circle-check"></i> <strong>Record found!</strong><br>
        <i class="fa-solid fa-user" style="width:14px"></i> ${escHtml(d.full_name)}<br>
        <i class="fa-solid fa-book" style="width:14px"></i> ${escHtml(d.course)} · Year ${d.year_level} · Block ${escHtml(d.block)}`;
      setAutoFill('name',d.full_name);setAutoFill('course',d.course);setAutoFill('year',String(d.year_level));setAutoFill('block',d.block);
    }else if(data.already_exists){
      lookupBox.className='id-lookup-box already-exists';
      lookupBox.innerHTML=`<i class="fa-solid fa-triangle-exclamation"></i> This Student ID already has an account. <a href="student_login.php" style="color:#92400e;font-weight:700">Log in instead</a>.`;
      clearAutoFill();
    }else{
      lookupBox.className='id-lookup-box not-found';
      lookupBox.innerHTML=`<i class="fa-solid fa-circle-xmark"></i> No student record found. Contact your teacher or admin.`;
      clearAutoFill();
    }
  }catch{lookupBox.className='id-lookup-box';clearAutoFill();}
}

document.getElementById('reg-form').addEventListener('submit',async(e)=>{
  e.preventDefault();const btn=document.getElementById('reg-btn');const errEl=document.getElementById('reg-error');
  btn.disabled=true;document.getElementById('reg-btn-text').textContent='Registering…';errEl.style.display='none';
  const fd=new FormData(e.target);
  const course=fd.get('course');const year=fd.get('year_level');const block=fd.get('block');
  // Validate custom dropdowns manually
  if(!course||!year||!block){
    errEl.textContent='Please select Program, Year Level, and Block.';errEl.style.display='block';
    btn.disabled=false;document.getElementById('reg-btn-text').textContent='Register';return;
  }
  fd.set('course_year',course&&year?course+'-'+year:course||year);
  fd.delete('year_level');fd.append('action','register');
  try{
    const res=await fetch('student_api.php',{method:'POST',body:fd});const data=await res.json();
    if(data.success){document.getElementById('reg-form').style.display='none';document.getElementById('success-card').style.display='flex';}
    else{errEl.textContent=data.message;errEl.style.display='block';btn.disabled=false;document.getElementById('reg-btn-text').textContent='Register';}
  }catch{errEl.textContent='Server error.';errEl.style.display='block';btn.disabled=false;document.getElementById('reg-btn-text').textContent='Register';}
});
</script>
</body>
</html>
