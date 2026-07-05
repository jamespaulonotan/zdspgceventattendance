<?php require_once 'auth.php'; requireAdminLogin(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
<meta name="csrf-token" content="<?= generateCsrfToken() ?>">
<title>Admin Panel — ZDSPGC QR Attendance</title>
<link rel="stylesheet" href="assets/style.css?v=<?= filemtime('assets/style.css') ?>">
<script src="https://kit.fontawesome.com/022f226857.js" crossorigin="anonymous"></script>
</head>
<body>
<div class="app">
<aside class="sidebar" id="sidebar">
  <div class="sidebar-profile">
    <div class="profile-avatar"><i class="fa-solid fa-user-shield fa-lg"></i></div>
    <div class="profile-info">
      <span class="profile-name">Administrator</span>
      <span class="profile-status"><span class="status-dot"></span> Online</span>
    </div>
  </div>
  <nav class="sidebar-nav">
    <p class="nav-group-label">Overview</p>
    <a href="#" class="nav-item active" data-page="dashboard">
      <i class="fa-solid fa-gauge-high fa-fw"></i> Dashboard
    </a>
    <p class="nav-group-label" style="margin-top:12px">Admin</p>
    <a href="#" class="nav-item" data-page="users">
      <i class="fa-solid fa-users-gear fa-fw"></i> User Management
      <span class="pending-badge" id="pending-badge" style="display:none">0</span>
    </a>
    <a href="#" class="nav-item" id="nav-approvals" data-page="approvals">
      <i class="fa-solid fa-user-check fa-fw"></i> Student Approvals
      <span class="pending-badge" id="pending-badge-approvals" style="display:none">0</span>
    </a>
    <a href="#" class="nav-item" data-page="pwresets">
      <i class="fa-solid fa-key fa-fw"></i> Password Resets
      <span class="pending-badge" id="pending-badge-pwresets" style="display:none">0</span>
    </a>
    <a href="#" class="nav-item" data-page="reports">
      <i class="fa-solid fa-chart-bar fa-fw"></i> Reports &amp; Logs
    </a>
  </nav>
  <div class="sidebar-footer">
    <a href="logout.php" class="logout-btn">
      <i class="fa-solid fa-right-from-bracket"></i> Log Out
    </a>
    <p>ZDSPGC &mdash; Admin Panel</p>
  </div>
</aside>
<div class="sidebar-overlay" id="sidebar-overlay"></div>
<div class="main-wrapper">
  <header class="topbar">
    <button class="topbar-hamburger" id="hamburger-btn" aria-label="Toggle menu"><span></span><span></span><span></span></button>
    <div class="topbar-title" id="topbar-title">Dashboard</div>
    <div class="topbar-brand">
      <img src="assets/logo.png" alt="ZDSPGC" class="topbar-brand-logo"
           onerror="this.style.display='none'">
      <span class="topbar-brand-text">ZDSPGC</span>
    </div>
  </header>
  <main class="main">

<!-- DASHBOARD PAGE -->
<div id="page-dashboard" class="page active">
  <div class="page-header"><div><h1>Dashboard</h1><p class="subtitle">System overview at a glance</p></div></div>
  <div class="dashboard-stats">
    <div class="dash-card">
      <div class="dash-icon blue"><i class="fa-solid fa-users fa-lg"></i></div>
      <div class="dash-info"><span class="dash-num" id="dash-total-students">—</span><span class="dash-label">Total Students</span></div>
    </div>
    <div class="dash-card clickable" onclick="navigateTo('users')">
      <div class="dash-icon orange"><i class="fa-solid fa-clock fa-lg"></i></div>
      <div class="dash-info"><span class="dash-num" id="dash-pending">—</span><span class="dash-label">Pending Approvals</span></div>
    </div>
    <div class="dash-card">
      <div class="dash-icon green"><i class="fa-solid fa-chalkboard-user fa-lg"></i></div>
      <div class="dash-info"><span class="dash-num" id="dash-total-teachers">—</span><span class="dash-label">Teacher Accounts</span></div>
    </div>
    <div class="dash-card">
      <div class="dash-icon purple"><i class="fa-solid fa-calendar-days fa-lg"></i></div>
      <div class="dash-info"><span class="dash-num" id="dash-total-events">—</span><span class="dash-label">Total Events</span></div>
    </div>
    <div class="dash-card">
      <div class="dash-icon" style="background:#fce7f3;color:#db2777"><i class="fa-solid fa-face-smile fa-lg"></i></div>
      <div class="dash-info"><span class="dash-num" id="dash-face-enrolled">—</span><span class="dash-label">Face Enrolled</span></div>
    </div>
    <div class="dash-card">
      <div class="dash-icon" style="background:#fef3c7;color:#d97706"><i class="fa-solid fa-face-meh fa-lg"></i></div>
      <div class="dash-info"><span class="dash-num" id="dash-face-not-enrolled">—</span><span class="dash-label">No Face Enrolled</span></div>
    </div>
  </div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:24px">
    <div class="recent-events-card">
      <div class="card-header-row">
        <h3><i class="fa-solid fa-hourglass-half" style="color:#f59e0b;margin-right:6px"></i>Pending Approvals</h3>
        <a href="#" class="card-link" data-page="users">Manage &rarr;</a>
      </div>
      <div id="dash-pending-list"><p class="empty-msg" style="padding:16px">Loading…</p></div>
    </div>
    <div class="recent-events-card">
      <div class="card-header-row">
        <h3><i class="fa-solid fa-list-check" style="color:var(--primary);margin-right:6px"></i>Recent Activity</h3>
        <a href="#" class="card-link" data-page="reports">View all &rarr;</a>
      </div>
      <div id="dash-recent-activity"><p class="empty-msg" style="padding:16px">Loading…</p></div>
    </div>
  </div>
</div>

<!-- USER MANAGEMENT PAGE -->
<div id="page-users" class="page">
  <div class="page-header"><div><h1>User Management</h1><p class="subtitle">Manage teacher and student accounts</p></div></div>
  <div class="sub-tabs" style="margin-bottom:20px">
    <button class="sub-tab active" data-usertab="teachers"><i class="fa-solid fa-chalkboard-user"></i> Teachers</button>
    <button class="sub-tab" data-usertab="students"><i class="fa-solid fa-users"></i> Students</button>
  </div>
  <div id="usertab-teachers" style="display:block">
    <div class="page-header" style="margin-bottom:16px">
      <p style="color:var(--muted);font-size:.9rem;max-width:560px">Teacher accounts log in to manage events and view attendance.</p>
      <button class="btn btn-primary" onclick="openModal('modal-add-teacher')"><i class="fa-solid fa-plus"></i> Add Teacher</button>
    </div>
    <div id="teachers-wrap"><p class="loading-spinner">Loading…</p></div>
  </div>
  <div id="usertab-students" style="display:none">
    <div class="reports-section" style="margin-top:8px">
      <div class="reports-section-header">
        <h3><i class="fa-solid fa-users" style="color:var(--primary);margin-right:6px"></i>All Students</h3>
        <div class="filter-row" style="display:flex;gap:8px;flex-wrap:wrap">
          <input type="text" id="sp-search" placeholder="Search name or ID…" oninput="filterStudentList()"
            class="filter-input" style="padding:10px 14px;border:1px solid var(--border);border-radius:10px;font-size:.9rem;outline:none;min-width:200px;background:#fff">
          <select id="sp-face-filter" onchange="filterStudentList()"
            class="filter-select" style="padding:10px 14px;border:1px solid var(--border);border-radius:10px;font-size:.9rem;outline:none;background:#fff;cursor:pointer">
            <option value="">All Students</option>
            <option value="enrolled">Face Enrolled</option>
            <option value="not_enrolled">Not Enrolled</option>
          </select>
        </div>
      </div>
      <div id="student-pw-wrap"><p class="loading-spinner">Loading…</p></div>
    </div>
  </div>
</div>

<!-- STUDENT APPROVALS PAGE -->
<div id="page-approvals" class="page">
  <div class="page-header">
    <div>
      <h1>Student Approvals</h1>
      <p class="subtitle">Review and approve or reject student registration requests</p>
    </div>
    <button class="btn btn-secondary btn-sm" onclick="loadApprovalsPage()"><i class="fa-solid fa-rotate"></i> Refresh</button>
  </div>
  <div class="reports-section">
    <div class="reports-section-header">
      <h3><i class="fa-solid fa-hourglass-half" style="color:#f59e0b;margin-right:6px"></i>Pending Registrations <span class="pending-count-badge" id="ap-pending-count">0</span></h3>
    </div>
    <div id="ap-pending-wrap"><p class="loading-spinner">Loading…</p></div>
  </div>
  <div class="reports-section" style="margin-top:24px">
    <div class="reports-section-header">
      <h3><i class="fa-solid fa-circle-check" style="color:#16a34a;margin-right:6px"></i>Recently Approved / Rejected</h3>
    </div>
    <div id="ap-resolved-wrap"><p class="loading-spinner">Loading…</p></div>
  </div>
</div>

<!-- PASSWORD RESETS PAGE -->
<div id="page-pwresets" class="page">
  <div class="page-header">
    <div>
      <h1>Password Resets</h1>
      <p class="subtitle">Manage student and teacher password reset requests</p>
    </div>
    <button class="btn btn-secondary btn-sm" onclick="loadPwResetsPage()"><i class="fa-solid fa-rotate"></i> Refresh</button>
  </div>
  <!-- Student Password Reset Requests -->
  <div class="reports-section">
    <div class="reports-section-header">
      <h3><i class="fa-solid fa-key" style="color:#7c3aed;margin-right:6px"></i>Student Password Reset Requests <span class="pending-count-badge" id="pw-reset-count" style="background:#7c3aed">0</span></h3>
    </div>
    <div id="pw-reset-wrap"><p class="loading-spinner">Loading…</p></div>
  </div>
  <!-- Teacher Password Reset Requests -->
  <div class="reports-section" style="margin-top:24px">
    <div class="reports-section-header">
      <h3><i class="fa-solid fa-chalkboard-user" style="color:#0369a1;margin-right:6px"></i>Teacher Password Reset Requests <span class="pending-count-badge" id="teacher-pw-reset-count" style="background:#0369a1">0</span></h3>
    </div>
    <div id="teacher-pw-reset-wrap"><p class="loading-spinner">Loading…</p></div>
  </div>
</div>

<!-- REPORTS & LOGS PAGE -->
<div id="page-reports" class="page">
  <div class="page-header">
    <div><h1>Reports &amp; Logs</h1><p class="subtitle">Full system activity history</p></div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <button class="btn btn-secondary btn-sm" onclick="loadReports()"><i class="fa-solid fa-rotate"></i> Refresh</button>
      <button class="btn btn-danger btn-sm" onclick="clearAllLogs()"><i class="fa-solid fa-trash"></i> Clear All Logs</button>
      <button class="btn btn-danger btn-sm" id="btn-delete-selected" onclick="deleteSelectedLogs()" style="display:none"><i class="fa-solid fa-trash-can"></i> Delete Selected (<span id="log-selected-count">0</span>)</button>
    </div>
  </div>
  <div class="reports-section">
    <div class="reports-section-header">
        <h3><i class="fa-solid fa-timeline" style="color:var(--primary);margin-right:6px"></i>Activity Log</h3>
        <div class="filter-row" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
          <select id="log-type-filter" onchange="filterActivityLog()"
            class="filter-select" style="padding:10px 14px;border:1px solid var(--border);border-radius:10px;font-size:.9rem;outline:none;background:#fff;cursor:pointer">
            <option value="">All Types</option>
            <option value="registration">Registration</option>
            <option value="login">Login</option>
            <option value="approval">Approval</option>
            <option value="rejection">Rejection</option>
            <option value="user_mgmt">User Mgmt</option>
            <option value="roster">Roster</option>
          </select>
          <input type="text" id="log-search" placeholder="Search description or actor…"
            oninput="filterActivityLog()"
            class="filter-input" style="padding:10px 14px;border:1px solid var(--border);border-radius:10px;font-size:.9rem;outline:none;min-width:200px;flex:1;background:#fff">
        </div>
      </div>
    <!-- Selection toolbar — shown only when rows are checked -->
    <div id="log-selection-bar" style="display:none;align-items:center;gap:10px;padding:10px 16px;background:#fef3c7;border-bottom:1px solid #fde68a">
      <input type="checkbox" id="log-select-all" onchange="toggleSelectAllLogs(this)" style="width:16px;height:16px;cursor:pointer" title="Select / deselect all">
      <span style="font-size:.88rem;font-weight:600;color:#92400e">Select all visible</span>
      <button class="btn btn-sm btn-ghost" onclick="clearLogSelection()" style="margin-left:auto">Cancel</button>
    </div>
    <div id="rp-log-wrap"><p class="loading-spinner">Loading…</p></div>
  </div>
</div>

  </main>
</div>
</div>

<!-- MODAL: Add Teacher -->
<div id="modal-add-teacher" class="modal-overlay" onclick="closeModalOutside(event,'modal-add-teacher')">
  <div class="modal">
    <div class="modal-header"><h2>Add Teacher Account</h2><button class="modal-close" onclick="closeModal('modal-add-teacher')"><i class="fa-solid fa-xmark"></i></button></div>
    <form id="form-add-teacher">
      <div class="form-group"><label>Teacher ID *</label><input type="text" name="username" required placeholder="e.g. T-2024-001" autocomplete="off"><p class="field-hint">This will be used to log in. Letters, numbers, dots, hyphens, underscores only.</p></div>
      <div class="form-group"><label>Display Name *</label><input type="text" name="display_name" required placeholder="e.g. Juan Dela Cruz"></div>
      <div class="form-group"><label>Password *</label><input type="password" name="password" required placeholder="At least 6 characters" autocomplete="new-password"></div>
      <div class="form-actions">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-add-teacher')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add Teacher</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL: Edit Teacher -->
<div id="modal-edit-teacher" class="modal-overlay" onclick="closeModalOutside(event,'modal-edit-teacher')">
  <div class="modal">
    <div class="modal-header"><h2>Edit Teacher</h2><button class="modal-close" onclick="closeModal('modal-edit-teacher')"><i class="fa-solid fa-xmark"></i></button></div>
    <form id="form-edit-teacher">
      <input type="hidden" name="id" id="edit-teacher-id">
      <div class="form-group"><label>Teacher ID</label><input type="text" id="edit-teacher-username" disabled style="background:var(--bg);color:var(--muted);cursor:not-allowed"><p class="field-hint">Teacher ID cannot be changed.</p></div>
      <div class="form-group"><label>Display Name *</label><input type="text" name="display_name" id="edit-teacher-name" required></div>
      <div class="form-group"><label>New Password</label><input type="password" name="password" placeholder="Leave blank to keep current" autocomplete="new-password"><p class="field-hint">At least 6 characters if changing.</p></div>
      <div class="form-actions">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-edit-teacher')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL: Reset Student Password -->
<div id="modal-reset-student-pw" class="modal-overlay" onclick="closeModalOutside(event,'modal-reset-student-pw')">
  <div class="modal">
    <div class="modal-header"><h2>Reset Student Password</h2><button class="modal-close" onclick="closeModal('modal-reset-student-pw')"><i class="fa-solid fa-xmark"></i></button></div>
    <form id="form-reset-student-pw">
      <input type="hidden" name="id" id="rsp-student-id">
      <div class="form-group"><label>Student</label><p id="rsp-student-name" style="font-weight:700;color:var(--text);margin:4px 0 0"></p></div>
      <div class="form-group"><label>New Password *</label><input type="password" name="password" id="rsp-password" required placeholder="At least 6 characters" autocomplete="new-password"></div>
      <div class="form-group"><label>Confirm Password *</label><input type="password" name="confirm" id="rsp-confirm" required placeholder="Repeat new password"></div>
      <div class="form-actions">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-reset-student-pw')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-key"></i> Set New Password</button>
      </div>
    </form>
  </div>
</div>

<div id="toast" class="toast"></div>
<script src="assets/admin.js?v=<?= filemtime('assets/admin.js') ?>"></script>
</body>
</html>
