<?php require_once 'auth.php'; requireTeacherLogin(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="csrf-token" content="<?= generateCsrfToken() ?>">
<title>Teacher Portal — ZDSPGC QR Attendance</title>
<link rel="stylesheet" href="assets/style.css?v=11">
<script src="https://kit.fontawesome.com/022f226857.js" crossorigin="anonymous"></script>
<style>:root{--primary:#2d6a4f;--primary-dark:#1b4332;--accent:#52b788}</style>
</head>
<body>
<div class="app">
<aside class="sidebar" id="sidebar">
  <div class="sidebar-profile">
    <div class="profile-avatar"><i class="fa-solid fa-chalkboard-user fa-lg"></i></div>
    <div class="profile-info">
      <span class="profile-name"><?= htmlspecialchars($_SESSION['teacher_name'] ?? $_SESSION['admin_user'] ?? 'Teacher') ?></span>
      <span class="profile-status"><span class="status-dot"></span> Teacher / Adviser</span>
    </div>
  </div>
  <nav class="sidebar-nav">
    <p class="nav-group-label">Overview</p>
    <a href="#" class="nav-item active" data-page="dashboard">
      <i class="fa-solid fa-gauge-high fa-fw"></i> Dashboard
    </a>
    <p class="nav-group-label" style="margin-top:12px">Management</p>
    <a href="#" class="nav-item" data-page="events">
      <i class="fa-solid fa-calendar-days fa-fw"></i> Events
    </a>
    <a href="#" class="nav-item" data-page="registry">
      <i class="fa-solid fa-users fa-fw"></i> Student Registry
    </a>
    <a href="#" class="nav-item" data-page="attendance">
      <i class="fa-solid fa-clipboard-check fa-fw"></i> Attendance
    </a>
    <a href="scanner.php" class="nav-item" target="_blank">
      <i class="fa-solid fa-qrcode fa-fw"></i> Scanner <i class="fa-solid fa-arrow-up-right-from-square fa-xs" style="margin-left:2px;opacity:.6"></i>
    </a>
    <p class="nav-group-label" style="margin-top:12px">Account</p>
    <a href="#" class="nav-item" data-page="settings">
      <i class="fa-solid fa-gear fa-fw"></i> Settings
    </a>
  </nav>
  <div class="sidebar-footer">
    <a href="logout.php" class="logout-btn">
      <i class="fa-solid fa-right-from-bracket"></i> Log Out
    </a>
    <p>ZDSPGC &mdash; Teacher Portal</p>
  </div>
</aside>
<div class="sidebar-overlay" id="sidebar-overlay"></div>
<div class="main-wrapper">
  <header class="topbar">
    <button class="topbar-hamburger" id="hamburger-btn"><span></span><span></span><span></span></button>
    <div class="topbar-title" id="topbar-title">Dashboard</div>
    <div class="topbar-brand">
      <img src="assets/logo.png" alt="ZDSPGC" class="topbar-brand-logo"
           onerror="this.style.display='none'">
      <span class="topbar-brand-text">ZDSPGC</span>
    </div>
  </header>
  <main class="main">

<!-- DASHBOARD -->
<div id="page-dashboard" class="page active">
  <div class="page-header">
    <div><h1>Dashboard</h1><p class="subtitle">Welcome back, <?= htmlspecialchars($_SESSION['teacher_name'] ?? 'Teacher') ?></p></div>
  </div>
  <div class="dashboard-stats">
    <div class="dash-card clickable" onclick="tNavigateTo('events')">
      <div class="dash-icon green"><i class="fa-solid fa-calendar-days fa-lg"></i></div>
      <div class="dash-info"><span class="dash-num" id="d-total-events">—</span><span class="dash-label">Total Events</span></div>
    </div>
    <div class="dash-card clickable" onclick="openAllStudentsModal()">
      <div class="dash-icon blue"><i class="fa-solid fa-users fa-lg"></i></div>
      <div class="dash-info"><span class="dash-num" id="d-total-students">—</span><span class="dash-label">Total Students</span></div>
    </div>
    <div class="dash-card clickable" onclick="openNoAccountModal()">
      <div class="dash-icon orange"><i class="fa-solid fa-user-clock fa-lg"></i></div>
      <div class="dash-info"><span class="dash-num" id="d-no-account">—</span><span class="dash-label">No Account Yet</span></div>
    </div>
    <div class="dash-card clickable" onclick="tNavigateTo('attendance')">
      <div class="dash-icon purple"><i class="fa-solid fa-circle-check fa-lg"></i></div>
      <div class="dash-info"><span class="dash-num" id="d-total-checkins">—</span><span class="dash-label">Total Check-ins</span></div>
    </div>
    <div class="dash-card clickable" onclick="openNoFaceModal()">
      <div class="dash-icon" style="background:#fce7f3;color:#db2777"><i class="fa-solid fa-face-meh fa-lg"></i></div>
      <div class="dash-info"><span class="dash-num" id="d-no-face">—</span><span class="dash-label">Face Not Yet Enrolled</span></div>
    </div>
    <div class="dash-card">
      <div class="dash-icon" style="background:#dcfce7;color:#16a34a"><i class="fa-solid fa-face-smile fa-lg"></i></div>
      <div class="dash-info"><span class="dash-num" id="d-face-enrolled">—</span><span class="dash-label">Face Enrolled</span></div>
    </div>
  </div>
  <div style="margin-top:24px">
    <div class="recent-events-card">
      <div class="card-header-row">
        <h3><i class="fa-solid fa-calendar-days" style="color:var(--primary);margin-right:6px"></i>Recent Events</h3>
        <a href="#" class="card-link" data-page="events">View all &rarr;</a>
      </div>
      <div id="d-recent-events"><p class="empty-msg" style="padding:16px">Loading…</p></div>
    </div>
  </div>
</div>

<!-- EVENTS -->
<div id="page-events" class="page">
  <div class="page-header">
    <div><h1>Events</h1><p class="subtitle">Create, edit and manage events</p></div>
    <button class="btn btn-primary" onclick="openModal('modal-create-event')"><i class="fa-solid fa-plus"></i> New Event</button>
  </div>
  <div id="events-grid" class="events-grid"><div class="loading-spinner">Loading…</div></div>
</div>

<!-- STUDENT REGISTRY -->
<div id="page-registry" class="page">
  <div class="page-header">
    <div><h1>Student Registry</h1><p class="subtitle">Manage all students</p></div>
    <button class="btn btn-primary" onclick="openModal('modal-add-student-reg')"><i class="fa-solid fa-user-plus"></i> Add to Registry</button>
  </div>
  <div class="reg-filter-bar">
    <input type="text" id="reg-search" placeholder="Search name or ID…" oninput="filterRegStudents()">
    <select id="reg-status-filter" onchange="filterRegStudents()">
      <option value="">All Status</option>
      <option value="approved">Approved</option>
      <option value="pending">Pending</option>
      <option value="rejected">Rejected</option>
    </select>
    <select id="reg-course-filter" onchange="filterRegStudents()">
      <option value="">All Programs</option>
      <option value="BTVTED">BTVTED</option>
      <option value="ACT">ACT</option>
      <option value="BSIS">BSIS</option>
    </select>
    <select id="reg-year-filter" onchange="filterRegStudents()">
      <option value="">All Year Levels</option>
      <option value="1">1st Year</option>
      <option value="2">2nd Year</option>
      <option value="3">3rd Year</option>
      <option value="4">4th Year</option>
    </select>
    <select id="reg-block-filter" onchange="filterRegStudents()">
      <option value="">All Sections</option>
      <option value="Lone">Lone</option>
      <option value="A">A</option>
      <option value="B">B</option>
      <option value="C">C</option>
      <option value="D">D</option>
    </select>
  </div>
  <div id="reg-all-wrap"><p class="loading-spinner">Loading…</p></div>
</div>

<!-- ATTENDANCE -->
<div id="page-attendance" class="page">
  <!-- Event grid view -->
  <div id="att-event-view">
    <div class="page-header">
      <div><h1>Attendance</h1><p class="subtitle">Select an event to view attendance</p></div>
      <button class="btn btn-secondary btn-sm" onclick="loadAttendanceEvents()"><i class="fa-solid fa-rotate"></i> Refresh</button>
    </div>
    <div id="att-event-grid" class="events-grid"><p class="loading-spinner">Loading…</p></div>
  </div>

  <!-- Detail view (hidden until event selected) -->
  <div id="att-detail-view" style="display:none">
    <div class="page-header">
      <div>
        <button class="btn btn-ghost btn-sm" onclick="closeAttendanceDetail()" style="margin-bottom:8px;padding:5px 10px">
          <i class="fa-solid fa-arrow-left"></i> Back to Events
        </button>
        <h1 id="att-detail-title">Event</h1>
        <p class="subtitle" id="att-detail-meta"></p>
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap">
        <button class="btn btn-secondary" onclick="exportCSV()"><i class="fa-solid fa-file-arrow-down"></i> Export CSV</button>
        <button class="btn btn-primary" onclick="exportFullAttendancePDF()"><i class="fa-solid fa-file-pdf"></i> Download Full PDF</button>
      </div>
    </div>

    <!-- Stats -->
    <div class="stats-bar" id="t-stats-bar" style="margin-bottom:20px">
      <div class="stat-card"><span class="stat-num" id="t-registered">0</span><span class="stat-label">Registered</span></div>
      <div class="stat-card accent"><span class="stat-num" id="t-attended">0</span><span class="stat-label">Attended</span></div>
      <div class="stat-card muted"><span class="stat-num" id="t-absent">0</span><span class="stat-label">Absent</span></div>
      <div class="stat-card green"><span class="stat-num" id="t-rate">0%</span><span class="stat-label">Rate</span></div>
    </div>

    <!-- ── FILTER SELECTOR ── -->
    <div class="att-filter-selector" style="flex-direction:row;flex-wrap:wrap;gap:12px">
      <div style="position:relative;min-width:160px;flex:1">
          <div class="att-cdd" id="att-cdd-program" tabindex="0" data-type="program">
            <i class="fa-solid fa-book" style="position:absolute;left:11px;color:var(--primary);font-size:.85rem"></i>
            <span class="att-cdd-sel" data-placeholder="All Programs">All Programs</span>
            <i class="fa-solid fa-chevron-down att-cdd-chev"></i>
            <div class="att-cdd-list">
              <div class="att-cdd-item" data-val="">All Programs</div>
              <div class="att-cdd-item" data-val="BTVTED">BTVTED</div>
              <div class="att-cdd-item" data-val="ACT">ACT</div>
              <div class="att-cdd-item" data-val="BSIS">BSIS</div>
            </div>
          </div>
      </div>
      <div style="position:relative;min-width:160px;flex:1">
          <div class="att-cdd" id="att-cdd-year" tabindex="0" data-type="year">
            <i class="fa-solid fa-layer-group" style="position:absolute;left:11px;color:var(--primary);font-size:.85rem"></i>
            <span class="att-cdd-sel" data-placeholder="All Year Levels">All Year Levels</span>
            <i class="fa-solid fa-chevron-down att-cdd-chev"></i>
            <div class="att-cdd-list">
              <div class="att-cdd-item" data-val="">All Year Levels</div>
              <div class="att-cdd-item" data-val="1">1st Year</div>
              <div class="att-cdd-item" data-val="2">2nd Year</div>
              <div class="att-cdd-item" data-val="3">3rd Year</div>
              <div class="att-cdd-item" data-val="4">4th Year</div>
            </div>
          </div>
      </div>
      <div style="position:relative;min-width:160px;flex:1">
          <div class="att-cdd" id="att-cdd-block" tabindex="0" data-type="block">
            <i class="fa-solid fa-table-cells" style="position:absolute;left:11px;color:var(--primary);font-size:.85rem"></i>
            <span class="att-cdd-sel" data-placeholder="All Sections">All Sections</span>
            <i class="fa-solid fa-chevron-down att-cdd-chev"></i>
            <div class="att-cdd-list">
              <div class="att-cdd-item" data-val="">All Sections</div>
              <div class="att-cdd-item" data-val="Lone">Lone</div>
              <div class="att-cdd-item" data-val="A">A</div>
              <div class="att-cdd-item" data-val="B">B</div>
              <div class="att-cdd-item" data-val="C">C</div>
              <div class="att-cdd-item" data-val="D">D</div>
            </div>
          </div>
      </div>
    </div>

    <!-- ── RESULT SQUARES (the circled area — show combos matching the filter) ── -->
    <div id="att-group-sections" style="display:none;margin-top:20px">
      <div id="att-sq-program" class="att-sq-grid"></div>
    </div>
  </div>
</div>

<!-- SETTINGS -->
<div id="page-settings" class="page">
  <div class="page-header"><div><h1>Settings</h1><p class="subtitle">Manage your account</p></div></div>
  <div class="settings-container">
    <div class="recent-events-card settings-card">
      <h3 class="settings-section-title"><i class="fa-solid fa-circle-user" style="color:var(--primary);margin-right:6px"></i>Account Info</h3>
      <div class="account-info-list">
        <div class="account-info-row">
          <span class="account-info-label">Display Name</span>
          <strong id="s-display-name">—</strong>
        </div>
        <div class="account-info-row">
          <span class="account-info-label">Username</span>
          <code id="s-username" class="username-code">—</code>
        </div>
        <div class="account-info-row">
          <span class="account-info-label">Role</span>
          <span>Teacher / Adviser</span>
        </div>
      </div>
    </div>
    <div class="recent-events-card settings-card">
      <h3 class="settings-section-title"><i class="fa-solid fa-key" style="color:var(--primary);margin-right:6px"></i>Change Password</h3>
      <form id="form-change-password">
        <div class="form-group"><label>Current Password *</label><input type="password" name="current_password" required placeholder="Enter current password" autocomplete="current-password"></div>
        <div class="form-group"><label>New Password *</label><input type="password" name="new_password" required placeholder="At least 6 characters" autocomplete="new-password"></div>
        <div class="form-group"><label>Confirm New Password *</label><input type="password" name="confirm_password" required placeholder="Repeat new password" autocomplete="new-password"></div>
        <div id="pw-change-msg" style="display:none;padding:10px 14px;border-radius:8px;font-size:.88rem;margin-bottom:12px"></div>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Update Password</button>
      </form>
    </div>
  </div>
</div>

<!-- MODAL: Attendance student list -->
<div id="modal-att-students" class="modal-overlay" onclick="if(event.target.id==='modal-att-students')closeStudentModal()">
  <div class="modal modal-lg" style="max-height:90vh;display:flex;flex-direction:column">
    <div class="modal-header" style="flex-shrink:0;padding-bottom:14px;border-bottom:1px solid var(--border)">
      <div style="flex:1;min-width:0">
        <h2 id="att-modal-title" style="font-size:1.1rem;font-weight:800"></h2>
        <p id="att-modal-sub" style="font-size:.82rem;color:var(--muted);margin-top:3px"></p>
      </div>
      <div style="display:flex;gap:6px;align-items:center;flex-shrink:0">
        <button class="btn btn-sm btn-secondary" onclick="printAttendanceModal()" title="Print"><i class="fa-solid fa-print"></i> Print</button>
        <button class="btn btn-sm btn-primary" onclick="exportAttendanceModalPDF()" title="Export PDF"><i class="fa-solid fa-file-pdf"></i> PDF</button>
        <button class="modal-close" onclick="closeStudentModal()"><i class="fa-solid fa-xmark"></i></button>
      </div>
    </div>
    <!-- mini stats inside modal -->
    <div id="att-modal-stats" style="flex-shrink:0;display:flex;gap:10px;flex-wrap:wrap;padding:14px 24px;border-bottom:1px solid var(--border);background:var(--bg)"></div>
    <!-- search inside modal -->
    <div style="flex-shrink:0;padding:12px 24px;border-bottom:1px solid var(--border)">
      <input type="text" id="att-modal-search" placeholder="Search by name…" oninput="filterModalStudents()"
        style="width:100%;padding:9px 14px;border:1px solid var(--border);border-radius:8px;font-size:.88rem;outline:none">
    </div>
    <!-- scrollable student list -->
    <div style="flex:1;overflow-y:auto;padding:0 24px 24px">
      <div id="att-modal-body" style="padding-top:16px"></div>
    </div>
  </div>
</div>

  </main>
</div>
</div>

<!-- MODAL: Add to Registry -->
<div id="modal-add-student-reg" class="modal-overlay" onclick="closeModalOutside(event,'modal-add-student-reg')">
  <div class="modal modal-lg">
    <div class="modal-header">
      <h2><i class="fa-solid fa-user-plus" style="color:var(--primary);margin-right:6px"></i>Add to Registry</h2>
      <button class="modal-close" onclick="closeModal('modal-add-student-reg')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="add-tabs" style="margin:0 0 4px">
      <button type="button" class="add-tab active" data-addtab="single" onclick="switchAddTab('single')"><i class="fa-solid fa-user"></i> Single Student</button>
      <button type="button" class="add-tab" data-addtab="csv" onclick="switchAddTab('csv')"><i class="fa-solid fa-file-csv"></i> Import CSV</button>
    </div>
    <div id="addtab-single" style="display:block">
      <p style="color:var(--muted);font-size:.85rem;margin:8px 24px 12px">Adds the student to the registry. They can self-register an account later using their Student ID.</p>
      <form id="form-add-student-single" style="padding-top:4px">
        <div class="form-row">
          <div class="form-group"><label>Student ID *</label><input type="text" name="student_id" required placeholder="Ex. 25VS****" autocomplete="off"><p class="field-hint">Student ID used by the student to register an account.</p></div>
          <div class="form-group"><label>Full Name *</label><input type="text" name="full_name" required placeholder="Juan Dela Cruz"></div>
        </div>
        <div id="add-single-msg" style="display:none;padding:10px 14px;border-radius:8px;font-size:.88rem;margin-bottom:8px"></div>
        <div class="form-actions">
          <button type="button" class="btn btn-ghost" onclick="closeModal('modal-add-student-reg')">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add to Registry</button>
        </div>
      </form>
    </div>
    <div id="addtab-csv" style="display:none;padding:0 24px 24px">
      <p style="color:var(--muted);font-size:.85rem;margin:8px 0 12px">Upload a CSV. Required columns: <code>student_id</code>, <code>full_name</code>.</p>
      <a href="assets/roster_template.csv" download class="btn btn-secondary btn-sm" style="margin-bottom:12px;display:inline-block"><i class="fa-solid fa-file-arrow-down"></i> Download Template</a>
      <div class="csv-drop-zone" id="roster-drop-zone">
        <i class="fa-solid fa-cloud-arrow-up fa-2x" style="color:var(--muted)"></i>
        <p>Drag &amp; drop CSV here, or <label class="csv-browse" for="roster-file">browse</label></p>
        <p class="csv-file-name" id="roster-file-name">No file chosen</p>
        <input type="file" id="roster-file" accept=".csv,text/csv" style="display:none">
      </div>
      <div id="roster-preview" style="display:none;margin-top:12px;padding:10px 14px;background:#f0faf5;border:1px solid #a7f3d0;border-radius:8px;font-size:.85rem"></div>
      <div id="roster-upload-result" style="display:none;margin-top:10px;padding:10px 14px;border-radius:8px;font-size:.88rem"></div>
      <div style="display:flex;gap:10px;margin-top:14px;justify-content:flex-end">
        <button class="btn btn-ghost" onclick="closeModal('modal-add-student-reg')">Cancel</button>
        <button class="btn btn-primary" id="roster-upload-btn" onclick="uploadRoster()" style="display:none"><i class="fa-solid fa-cloud-arrow-up"></i> Upload</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL: Create Event -->
<div id="modal-create-event" class="modal-overlay" onclick="closeModalOutside(event,'modal-create-event')">
  <div class="modal">
    <div class="modal-header">
      <h2><i class="fa-solid fa-calendar-plus" style="color:var(--primary);margin-right:6px"></i>Create New Event</h2>
      <button class="modal-close" onclick="closeModal('modal-create-event')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form id="form-create-event">
      <div class="form-group"><label>Event Name *</label><input type="text" name="name" required placeholder="e.g. Foundation Day 2026"></div>
      <div class="form-row">
        <div class="form-group"><label>Date *</label><input type="date" name="event_date" required></div>
        <div class="form-group"><label>Start Time</label><input type="time" name="event_time"></div>
        <div class="form-group"><label>End Time</label><input type="time" name="event_end_time"></div>
      </div>
      <div class="form-group"><label>Location</label><input type="text" name="location" placeholder="e.g. Main Hall"></div>
      <div class="form-group"><label>Description</label><textarea name="description" rows="3" placeholder="Optional details…"></textarea></div>
      <div class="form-actions">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-create-event')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Create Event</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL: Edit Event -->
<div id="modal-edit-event" class="modal-overlay" onclick="closeModalOutside(event,'modal-edit-event')">
  <div class="modal">
    <div class="modal-header">
      <h2><i class="fa-solid fa-pen-to-square" style="color:var(--primary);margin-right:6px"></i>Edit Event</h2>
      <button class="modal-close" onclick="closeModal('modal-edit-event')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form id="form-edit-event">
      <input type="hidden" name="id" id="edit-event-id">
      <div class="form-group"><label>Event Name *</label><input type="text" name="name" id="edit-event-name" required></div>
      <div class="form-row">
        <div class="form-group"><label>Date *</label><input type="date" name="event_date" id="edit-event-date" required></div>
        <div class="form-group"><label>Start Time</label><input type="time" name="event_time" id="edit-event-time"></div>
        <div class="form-group"><label>End Time</label><input type="time" name="event_end_time" id="edit-event-end-time"></div>
      </div>
      <div class="form-group"><label>Location</label><input type="text" name="location" id="edit-event-location"></div>
      <div class="form-group"><label>Description</label><textarea name="description" id="edit-event-desc" rows="3"></textarea></div>
      <div class="form-actions">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-edit-event')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
      </div>
    </form>
  </div>
</div>

<div id="toast" class="toast"></div>

<!-- MODAL: All Students -->
<div id="modal-all-students" class="modal-overlay" onclick="if(event.target.id==='modal-all-students')closeModal('modal-all-students')">
  <div class="modal modal-lg" style="max-height:90vh;display:flex;flex-direction:column">
    <div class="modal-header" style="flex-shrink:0;padding-bottom:14px;border-bottom:1px solid var(--border)">
      <div>
        <h2 style="font-size:1.1rem;font-weight:800"><i class="fa-solid fa-users" style="color:#2563eb;margin-right:8px"></i>All Students</h2>
        <p style="font-size:.82rem;color:var(--muted);margin-top:3px">All students in the registry, including account status</p>
      </div>
      <button class="modal-close" onclick="closeModal('modal-all-students')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div style="flex-shrink:0;padding:12px 24px;border-bottom:1px solid var(--border)">
      <div style="display:flex;gap:8px;flex-wrap:wrap">
        <input type="text" id="all-students-search" placeholder="Search by name or ID…" oninput="filterAllStudents()"
          style="flex:1;min-width:160px;padding:9px 14px;border:1px solid var(--border);border-radius:8px;font-size:.88rem;outline:none">
        <select id="all-students-status-filter" onchange="filterAllStudents()"
          style="padding:9px 14px;border:1px solid var(--border);border-radius:8px;font-size:.88rem;outline:none">
          <option value="">All Statuses</option>
          <option value="no_account">No Account Yet</option>
          <option value="pending">Pending Approval</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
        </select>
      </div>
    </div>
    <div style="flex:1;overflow-y:auto;padding:16px 24px">
      <div id="all-students-body"><p class="loading-spinner">Loading…</p></div>
    </div>
  </div>
</div>

<!-- MODAL: No Account Yet -->
<div id="modal-no-account" class="modal-overlay" onclick="if(event.target.id==='modal-no-account')closeModal('modal-no-account')">
  <div class="modal modal-lg" style="max-height:90vh;display:flex;flex-direction:column">
    <div class="modal-header" style="flex-shrink:0;padding-bottom:14px;border-bottom:1px solid var(--border)">
      <div>
        <h2 style="font-size:1.1rem;font-weight:800"><i class="fa-solid fa-user-clock" style="color:#ea580c;margin-right:8px"></i>No Account Yet</h2>
        <p style="font-size:.82rem;color:var(--muted);margin-top:3px">Students added to the roster who haven't registered an account</p>
      </div>
      <button class="modal-close" onclick="closeModal('modal-no-account')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div style="flex-shrink:0;padding:12px 24px;border-bottom:1px solid var(--border)">
      <input type="text" id="no-acc-search" placeholder="Search by name or ID…" oninput="filterNoAccount()"
        style="width:100%;padding:9px 14px;border:1px solid var(--border);border-radius:8px;font-size:.88rem;outline:none">
    </div>
    <div style="flex:1;overflow-y:auto;padding:16px 24px">
      <div id="no-acc-body"><p class="loading-spinner">Loading…</p></div>
    </div>
  </div>
</div>

<!-- MODAL: Face Not Yet Enrolled -->
<div id="modal-no-face" class="modal-overlay" onclick="if(event.target.id==='modal-no-face')closeModal('modal-no-face')">
  <div class="modal modal-lg" style="max-height:90vh;display:flex;flex-direction:column">
    <div class="modal-header" style="flex-shrink:0;padding-bottom:14px;border-bottom:1px solid var(--border)">
      <div>
        <h2 style="font-size:1.1rem;font-weight:800"><i class="fa-solid fa-face-meh" style="color:#db2777;margin-right:8px"></i>Face Not Yet Enrolled</h2>
        <p style="font-size:.82rem;color:var(--muted);margin-top:3px">Approved students who haven't enrolled their face yet, grouped by program &amp; year level</p>
      </div>
      <button class="modal-close" onclick="closeModal('modal-no-face')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <!-- Group filter pills -->
    <div style="flex-shrink:0;display:flex;gap:8px;flex-wrap:wrap;padding:12px 24px;border-bottom:1px solid var(--border);align-items:center">
      <span style="font-size:.78rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.05em">Filter:</span>
      <div id="nf-pills" style="display:flex;gap:6px;flex-wrap:wrap"></div>
      <input type="text" id="no-face-search" placeholder="Search by name…" oninput="filterNoFace()"
        style="margin-left:auto;padding:7px 12px;border:1px solid var(--border);border-radius:8px;font-size:.85rem;outline:none;min-width:160px">
    </div>
    <div style="flex:1;overflow-y:auto;padding:16px 24px">
      <div id="no-face-body"><p class="loading-spinner">Loading…</p></div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
window._teacherName     = <?= json_encode($_SESSION['teacher_name'] ?? '—') ?>;
window._teacherUsername = <?= json_encode($_SESSION['admin_user']   ?? '—') ?>;
window._teacherUploader = <?= json_encode($_SESSION['teacher_name'] ?? $_SESSION['admin_user'] ?? 'Teacher') ?>;
</script>
<script src="assets/app.js?v=<?= time() ?>"></script>
</body>
</html>
