<?php
header('Content-Type: application/json');
set_error_handler(function($errno, $errstr) {
    echo json_encode(['success' => false, 'message' => "PHP Error: $errstr"]); exit;
});
require_once 'db.php';
require_once 'auth.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Define which actions require which roles
$public_actions = ['scan', 'teacher_register', 'teacher_forgot_verify']; // Actions that don't require login
$teacher_actions = ['teacher_change_password', 'teacher_add_student', 'get_teacher_dashboard_stats', 'get_no_account_students', 'get_no_face_students', 'upload_roster', 'lookup_roster', 'get_roster', 'delete_roster_entry', 'get_all_students_with_status', 'get_students_for_event', 'get_events', 'get_stats', 'register_attendee', 'remove_from_event', 'start_attendance', 'stop_attendance', 'get_pending_students', 'approve_student', 'reject_student', 'get_activity_log', 'get_password_reset_requests', 'send_temp_password', 'reject_password_reset', 'get_face_enrollment_stats'];
$admin_actions = ['create_event', 'update_event', 'delete_event', 'delete_student', 'get_all_students', 'get_activity_log', 'delete_log', 'delete_selected_logs', 'clear_all_logs', 'get_pending_teachers', 'approve_teacher_reg', 'reject_teacher_reg', 'get_teachers', 'add_teacher', 'update_teacher', 'delete_teacher', 'get_teacher_pw_reset_requests', 'send_teacher_temp_password', 'reject_teacher_pw_reset', 'admin_reset_student_password', 'clear_roster', 'upload_roster'];

$action = $_REQUEST['action'] ?? '';

// Check authentication
if (!in_array($action, $public_actions)) {
    if (empty($_SESSION['logged_in'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized: Please login.']);
        exit;
    }
    
    // Check if user has permission for this action
    $role = $_SESSION['role'] ?? '';
    if ($role === 'admin') {
        // Admin can do everything
    } elseif ($role === 'teacher') {
        // Check if teacher can perform this action
        if (!in_array($action, $teacher_actions) && !in_array($action, $admin_actions)) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized: Not enough permissions.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Unauthorized: Invalid role.']);
        exit;
    }
}

// Validate CSRF Token for non-GET requests (except public actions that are POST)
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    $public_post_actions = ['teacher_register', 'teacher_forgot_verify'];
    if (!in_array($action, $public_post_actions)) {
        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!validateCsrfToken($csrf_token)) {
            echo json_encode(['success' => false, 'message' => 'CSRF validation failed. Please refresh the page and try again.']);
            exit;
        }
    }
}

function logActivity($db, $type, $description, $actor = '') {
    $stmt = $db->prepare("INSERT INTO activity_log (type, description, actor) VALUES (?,?,?)");
    if ($stmt) { $stmt->bind_param('sss', $type, $description, $actor); $stmt->execute(); }
}

// Safe column adder — avoids ALTER TABLE IF NOT EXISTS compatibility issues
function addColSafe($db, $table, $col, $def) {
    $r = @$db->query("SHOW COLUMNS FROM `$table` LIKE '$col'");
    if ($r && $r->num_rows === 0) { @$db->query("ALTER TABLE `$table` ADD COLUMN `$col` $def"); }
}

$action = $_REQUEST['action'] ?? '';
switch ($action) {

// ── EVENTS ──────────────────────────────────────────────
case 'create_event':
    $name=$_POST['name']??''; $desc=$_POST['description']??'';
    $date=$_POST['event_date']??''; $time=$_POST['event_time']??''; $endtime=$_POST['event_end_time']??''; $loc=$_POST['location']??'';
    if (!trim($name)||!trim($date)){echo json_encode(['success'=>false,'message'=>'Name and date required.']);break;}
    $db=getDB();
    $s=$db->prepare("INSERT INTO events (name,description,event_date,event_time,event_end_time,location) VALUES (?,?,?,?,?,?)");
    $s->bind_param('ssssss',$name,$desc,$date,$time,$endtime,$loc);
    if(!$s->execute()){echo json_encode(['success'=>false,'message'=>$db->error]);break;}
    $eid=$db->insert_id;
    $db->query("INSERT IGNORE INTO attendees (event_id,student_id) SELECT $eid,id FROM students WHERE status='approved'");
    echo json_encode(['success'=>true,'id'=>$eid]);
    break;

case 'start_attendance':
    $id=intval($_POST['id']??0);$db=getDB();
    addColSafe($db,'events','attendance_started','TINYINT(1) DEFAULT 0');
    $s=$db->prepare("UPDATE events SET attendance_started=1 WHERE id=?");$s->bind_param('i',$id);$s->execute();
    echo json_encode(['success'=>true]);
    break;

case 'stop_attendance':
    $id=intval($_POST['id']??0);$db=getDB();
    addColSafe($db,'events','attendance_started','TINYINT(1) DEFAULT 0');
    $s=$db->prepare("UPDATE events SET attendance_started=0 WHERE id=?");$s->bind_param('i',$id);$s->execute();
    echo json_encode(['success'=>true]);
    break;

case 'get_events':
    $db=getDB();
    addColSafe($db,'events','attendance_started','TINYINT(1) DEFAULT 0');
    $r=$db->query("SELECT e.*,COUNT(DISTINCT a.student_id) AS total_registered,COUNT(DISTINCT al.student_id) AS total_attended
        FROM events e LEFT JOIN attendees a ON a.event_id=e.id LEFT JOIN attendance_log al ON al.event_id=e.id
        GROUP BY e.id ORDER BY e.event_date DESC");
    $rows=[];while($row=$r->fetch_assoc())$rows[]=$row;
    echo json_encode(['success'=>true,'data'=>$rows]);
    break;

case 'update_event':
    $id=intval($_POST['id']??0); $name=$_POST['name']??''; $desc=$_POST['description']??'';
    $date=$_POST['event_date']??''; $time=$_POST['event_time']??''; $endtime=$_POST['event_end_time']??''; $loc=$_POST['location']??'';
    if(!$id||!trim($name)||!trim($date)){echo json_encode(['success'=>false,'message'=>'ID, name and date required.']);break;}
    $db=getDB();
    $s=$db->prepare("UPDATE events SET name=?,description=?,event_date=?,event_time=?,event_end_time=?,location=? WHERE id=?");
    $s->bind_param('ssssssi',$name,$desc,$date,$time,$endtime,$loc,$id);
    echo $s->execute()?json_encode(['success'=>true]):json_encode(['success'=>false,'message'=>$db->error]);
    break;

case 'delete_event':
    $id=intval($_POST['id']??0); $db=getDB();
    $s=$db->prepare("DELETE FROM events WHERE id=?");$s->bind_param('i',$id);$s->execute();
    echo json_encode(['success'=>true]);
    break;

// ── STUDENTS ─────────────────────────────────────────────
case 'get_students_for_event':
    $eid=intval($_GET['event_id']??0);
    if(!$eid){echo json_encode(['success'=>false,'message'=>'event_id required']);break;}
    $db=getDB();
    // Return ALL approved students with their attendance status for this event
    $s=$db->prepare("SELECT s.id, s.name, s.username, s.phone, s.course_year, s.block, s.qr_token,
        (SELECT al.scanned_at FROM attendance_log al WHERE al.student_id=s.id AND al.event_id=?
         ORDER BY al.scanned_at DESC LIMIT 1) AS scanned_at
        FROM students s
        WHERE s.status='approved'
        ORDER BY s.name ASC");
    $s->bind_param('i',$eid);$s->execute();
    $rows=[];$r=$s->get_result();while($row=$r->fetch_assoc())$rows[]=$row;
    echo json_encode(['success'=>true,'data'=>$rows]);
    break;

case 'get_all_students':
    $db=getDB();
    $r=$db->query("SELECT s.id,s.name,s.username,s.phone,s.course_year,s.block,s.qr_token,s.registered_at,s.status,
        CASE WHEN s.face_descriptor IS NOT NULL THEN 1 ELSE 0 END AS face_enrolled,
        COUNT(DISTINCT a.event_id) AS events_registered, COUNT(DISTINCT al.event_id) AS events_attended
        FROM students s LEFT JOIN attendees a ON a.student_id=s.id LEFT JOIN attendance_log al ON al.student_id=s.id
        GROUP BY s.id ORDER BY s.registered_at DESC");
    $rows=[];while($row=$r->fetch_assoc())$rows[]=$row;
    echo json_encode(['success'=>true,'data'=>$rows]);
    break;

case 'get_all_students_with_status':
    $db=getDB();
    $r=$db->query("SELECT id,name,username,phone,course_year,block,status,registered_at,qr_token FROM students ORDER BY name ASC");
    $rows=[];while($row=$r->fetch_assoc())$rows[]=$row;
    echo json_encode(['success'=>true,'data'=>$rows]);
    break;

case 'delete_student':
    $id=intval($_POST['id']??0);$db=getDB();
    $s=$db->prepare("DELETE FROM students WHERE id=?");$s->bind_param('i',$id);$s->execute();
    echo json_encode(['success'=>true]);
    break;

case 'get_stats':
    $eid=intval($_GET['event_id']??0);
    if(!$eid){echo json_encode(['success'=>false]);break;}
    $db=getDB();
    // registered = all approved students (everyone can attend any event)
    $r1=$db->query("SELECT COUNT(*) AS c FROM students WHERE status='approved'");
    $registered=$r1->fetch_assoc()['c'];
    $r2=$db->prepare("SELECT COUNT(DISTINCT student_id) AS c FROM attendance_log WHERE event_id=?");
    $r2->bind_param('i',$eid);$r2->execute();
    $attended=$r2->get_result()->fetch_assoc()['c'];
    echo json_encode(['success'=>true,'registered'=>$registered,'attended'=>$attended]);
    break;

case 'register_attendee':
    $eid=intval($_POST['event_id']??0);$sid=intval($_POST['student_id']??0);
    if(!$eid||!$sid){echo json_encode(['success'=>false,'message'=>'event_id and student_id required']);break;}
    $db=getDB();
    $s=$db->prepare("INSERT IGNORE INTO attendees (event_id,student_id) VALUES (?,?)");
    $s->bind_param('ii',$eid,$sid);
    echo $s->execute()?json_encode(['success'=>true]):json_encode(['success'=>false,'message'=>$db->error]);
    break;

case 'remove_from_event':
    $eid=intval($_POST['event_id']??0);$sid=intval($_POST['student_id']??0);$db=getDB();
    $s=$db->prepare("DELETE FROM attendees WHERE event_id=? AND student_id=?");
    $s->bind_param('ii',$eid,$sid);$s->execute();
    echo json_encode(['success'=>true]);
    break;

// ── QR SCAN ───────────────────────────────────────────────
case 'scan':
    $token=trim($_POST['token']??'');$eid=intval($_POST['event_id']??0);
    if(!$token){echo json_encode(['success'=>false,'message'=>'No token provided.']);break;}
    $db=getDB();
    $s=$db->prepare("SELECT * FROM students WHERE qr_token=?");
    $s->bind_param('s',$token);$s->execute();
    $student=$s->get_result()->fetch_assoc();
    if(!$student){echo json_encode(['success'=>false,'message'=>'Invalid QR code.']);break;}
    if(!$eid){echo json_encode(['success'=>false,'message'=>'Please select an event first.']);break;}
    $chk=$db->prepare("SELECT id FROM attendees WHERE event_id=? AND student_id=?");
    $chk->bind_param('ii',$eid,$student['id']);$chk->execute();
    if($chk->get_result()->num_rows===0){
        $ins=$db->prepare("INSERT IGNORE INTO attendees (event_id,student_id) VALUES (?,?)");
        $ins->bind_param('ii',$eid,$student['id']);$ins->execute();
    }
    $ev=$db->prepare("SELECT name FROM events WHERE id=?");
    $ev->bind_param('i',$eid);$ev->execute();
    $evRow=$ev->get_result()->fetch_assoc();
    $student['event_name']=$evRow?$evRow['name']:'';
    $dup=$db->prepare("SELECT id,scanned_at FROM attendance_log WHERE student_id=? AND event_id=?");
    $dup->bind_param('ii',$student['id'],$eid);$dup->execute();
    $existing=$dup->get_result()->fetch_assoc();
    if($existing){echo json_encode(['success'=>true,'already_in'=>true,'attendee'=>$student,'scanned_at'=>$existing['scanned_at']]);break;}
    $log=$db->prepare("INSERT INTO attendance_log (student_id,event_id) VALUES (?,?)");
    $log->bind_param('ii',$student['id'],$eid);$log->execute();
    echo json_encode(['success'=>true,'already_in'=>false,'attendee'=>$student,'message'=>'Check-in successful!']);
    break;

// ── PENDING / APPROVALS ──────────────────────────────────
case 'get_pending_students':
    $db=getDB();
    $r=$db->query("SELECT id,name,username,phone,course_year,block,registered_at FROM students WHERE status='pending' ORDER BY registered_at DESC");
    $rows=[];while($row=$r->fetch_assoc())$rows[]=$row;
    echo json_encode(['success'=>true,'data'=>$rows]);
    break;

case 'approve_student':
    $id=intval($_POST['id']??0);$db=getDB();
    $s=$db->prepare("UPDATE students SET status='approved' WHERE id=?");
    $s->bind_param('i',$id);$s->execute();
    $n=$db->prepare("SELECT name,username FROM students WHERE id=?");
    $n->bind_param('i',$id);$n->execute();
    $row=$n->get_result()->fetch_assoc();
    if($row){logActivity($db,'approval',"Approved student: {$row['name']} ({$row['username']})",'Admin');}
    $db->query("INSERT IGNORE INTO attendees (event_id,student_id) SELECT id,$id FROM events");
    echo json_encode(['success'=>true]);
    break;

case 'reject_student':
    $id=intval($_POST['id']??0);$db=getDB();
    $s=$db->prepare("UPDATE students SET status='rejected' WHERE id=?");
    $s->bind_param('i',$id);$s->execute();
    $n=$db->prepare("SELECT name,username FROM students WHERE id=?");
    $n->bind_param('i',$id);$n->execute();
    $row=$n->get_result()->fetch_assoc();
    if($row){logActivity($db,'rejection',"Rejected student: {$row['name']} ({$row['username']})",'Admin');}
    echo json_encode(['success'=>true]);
    break;

case 'get_activity_log':
    $db=getDB();$limit=intval($_GET['limit']??100);
    $r=$db->query("SELECT * FROM activity_log ORDER BY created_at DESC LIMIT $limit");
    $rows=[];while($row=$r->fetch_assoc())$rows[]=$row;
    echo json_encode(['success'=>true,'data'=>$rows]);
    break;

case 'delete_log':
    $id=intval($_POST['id']??0);
    if(!$id){echo json_encode(['success'=>false,'message'=>'ID required.']);break;}
    $db=getDB();
    $s=$db->prepare("DELETE FROM activity_log WHERE id=?");$s->bind_param('i',$id);$s->execute();
    echo json_encode(['success'=>true]);
    break;

case 'delete_selected_logs':
    $raw=trim($_POST['ids']??'');
    if(!$raw){echo json_encode(['success'=>false,'message'=>'No IDs provided.']);break;}
    $ids=array_filter(array_map('intval',explode(',',$raw)));
    if(empty($ids)){echo json_encode(['success'=>false,'message'=>'Invalid IDs.']);break;}
    $db=getDB();
    $placeholders=implode(',',array_fill(0,count($ids),'?'));
    $types=str_repeat('i',count($ids));
    $s=$db->prepare("DELETE FROM activity_log WHERE id IN ($placeholders)");
    $s->bind_param($types,...$ids);$s->execute();
    echo json_encode(['success'=>true,'deleted'=>$db->affected_rows]);
    break;

case 'clear_all_logs':
    $db=getDB();
    $db->query("TRUNCATE TABLE activity_log");
    echo json_encode(['success'=>true]);
    break;

case 'get_password_reset_requests':
    $db=getDB();
    $db->query("CREATE TABLE IF NOT EXISTS password_reset_requests (
        id INT AUTO_INCREMENT PRIMARY KEY, student_id INT NOT NULL,
        status ENUM('pending','sent','rejected') DEFAULT 'pending',
        requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        handled_at TIMESTAMP NULL DEFAULT NULL,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE)");
    $r=$db->query("SELECT pr.id, pr.status, pr.requested_at, s.id AS student_id,
        s.name, s.username, s.phone, s.course_year, s.block
        FROM password_reset_requests pr
        JOIN students s ON s.id=pr.student_id
        WHERE pr.status='pending'
        ORDER BY pr.requested_at DESC");
    $rows=[];while($row=$r->fetch_assoc())$rows[]=$row;
    echo json_encode(['success'=>true,'data'=>$rows]);
    break;

case 'send_temp_password':
    $rid=intval($_POST['request_id']??0);
    if(!$rid){echo json_encode(['success'=>false,'message'=>'request_id required.']);break;}
    $db=getDB();
    $s=$db->prepare("SELECT pr.id, s.id AS sid, s.name, s.username, s.phone
        FROM password_reset_requests pr JOIN students s ON s.id=pr.student_id
        WHERE pr.id=? AND pr.status='pending'");
    $s->bind_param('i',$rid);$s->execute();$row=$s->get_result()->fetch_assoc();
    if(!$row){echo json_encode(['success'=>false,'message'=>'Request not found or already handled.']);break;}
    // Generate 6-digit numeric temp password
    $tempPw=str_pad(random_int(0,999999),6,'0',STR_PAD_LEFT);
    $hash=password_hash($tempPw,PASSWORD_DEFAULT);
    // Update student password and force change
    $u=$db->prepare("UPDATE students SET password_hash=?,must_change_password=1 WHERE id=?");
    $u->bind_param('si',$hash,$row['sid']);$u->execute();
    // Mark request as sent
    $m=$db->prepare("UPDATE password_reset_requests SET status='sent',handled_at=NOW() WHERE id=?");
    $m->bind_param('i',$rid);$m->execute();
    // Send email
    $emailResult=['success'=>false,'error'=>'Email not configured'];
    if(filter_var($row['phone'],FILTER_VALIDATE_EMAIL)){
        require_once __DIR__.'/mailer.php';
        $subject='Your Temporary Password — ZDSPGC QR Attendance';
        $loginUrl='http://'.($_SERVER['HTTP_HOST']??'localhost').'/qr-event-attendance/student_login.php';
        $html="<div style='font-family:sans-serif;max-width:520px;margin:0 auto;background:#f9fafb;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb'>
          <div style='background:#1b4332;padding:28px 32px;text-align:center'>
            <h1 style='color:#fff;margin:0;font-size:1.3rem'>ZDSPGC QR Attendance</h1>
            <p style='color:#a7f3d0;margin:6px 0 0;font-size:.88rem'>Password Reset</p>
          </div>
          <div style='padding:32px'>
            <p style='margin:0 0 16px;color:#111827'>Hi <strong>".htmlspecialchars($row['name'])."</strong>,</p>
            <p style='color:#374151;font-size:.9rem;line-height:1.6;margin:0 0 20px'>
              Your temporary password has been set by the admin. <strong>You must change it after logging in.</strong>
            </p>
            <div style='background:#fff;border:1px solid #d1fae5;border-radius:8px;padding:20px;margin-bottom:24px'>
              <table style='width:100%;border-collapse:collapse;font-size:.9rem'>
                <tr><td style='color:#6b7280;padding:6px 0;width:40%'>Student ID</td>
                    <td style='font-weight:700;color:#1b4332;font-family:monospace'>".htmlspecialchars($row['username'])."</td></tr>
                <tr><td style='color:#6b7280;padding:6px 0'>Temporary Password</td>
                    <td style='font-weight:700;color:#dc2626;font-family:monospace;font-size:1.1rem'>".htmlspecialchars($tempPw)."</td></tr>
              </table>
            </div>
            <a href='".htmlspecialchars($loginUrl)."'
               style='display:inline-block;background:#2d6a4f;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600'>
              Log In &amp; Change Password →
            </a>
          </div>
          <div style='background:#f3f4f6;padding:14px 32px;text-align:center;font-size:.75rem;color:#9ca3af'>
            © ".date('Y')." Zamboanga Del Sur Provincial Government College
          </div>
        </div>";
        $emailResult=sendMail($row['phone'],$row['name'],$subject,$html);
    }
    logActivity($db,'user_mgmt',"Temp password sent to: {$row['name']} ({$row['username']})",'Admin');
    echo json_encode(['success'=>true,'email_sent'=>$emailResult['success'],'temp_password'=>$tempPw]);
    break;

case 'reject_password_reset':
    $rid=intval($_POST['request_id']??0);
    if(!$rid){echo json_encode(['success'=>false,'message'=>'request_id required.']);break;}
    $db=getDB();
    $s=$db->prepare("UPDATE password_reset_requests SET status='rejected',handled_at=NOW() WHERE id=? AND status='pending'");
    $s->bind_param('i',$rid);$s->execute();
    echo json_encode(['success'=>$db->affected_rows>0]);
    break;

case 'get_face_enrollment_stats':
    $db=getDB();
    $r=$db->query("SELECT COUNT(*) AS total,
        SUM(CASE WHEN face_descriptor IS NOT NULL THEN 1 ELSE 0 END) AS enrolled,
        SUM(CASE WHEN face_descriptor IS NULL THEN 1 ELSE 0 END) AS not_enrolled
        FROM students WHERE status='approved'");
    echo json_encode(['success'=>true,'data'=>$r->fetch_assoc()]);
    break;

case 'get_teacher_dashboard_stats':
    $db=getDB();
    $rTotal=$db->query("SELECT COUNT(*) AS c FROM student_roster");
    $rosterTotal=(int)$rTotal->fetch_assoc()['c'];
    $rNoAcc=$db->query("SELECT COUNT(*) AS c FROM student_roster sr
        WHERE NOT EXISTS (SELECT 1 FROM students s WHERE s.username=sr.student_id)");
    $noAccount=(int)$rNoAcc->fetch_assoc()['c'];
    $rNoFace=$db->query("SELECT COUNT(*) AS c FROM students
        WHERE status='approved' AND (face_descriptor IS NULL OR face_descriptor='')");
    $noFace=(int)$rNoFace->fetch_assoc()['c'];
    echo json_encode(['success'=>true,'data'=>[
        'roster_total'  => $rosterTotal,
        'no_account'    => $noAccount,
        'no_face'       => $noFace,
    ]]);
    break;

case 'get_no_account_students':
    // Roster entries that have no matching student account
    $db=getDB();
    $r=$db->query("SELECT sr.student_id, sr.full_name, sr.course, sr.year_level, sr.block
        FROM student_roster sr
        WHERE NOT EXISTS (SELECT 1 FROM students s WHERE s.username=sr.student_id)
        ORDER BY sr.course ASC, sr.year_level ASC, sr.block ASC, sr.full_name ASC");
    $rows=[];while($row=$r->fetch_assoc())$rows[]=$row;
    echo json_encode(['success'=>true,'data'=>$rows]);
    break;

case 'get_all_roster_students':
    // All roster entries plus account status if available
    $db=getDB();
    $r=$db->query("SELECT sr.student_id, sr.full_name, sr.course, sr.year_level, sr.block,
        s.id AS student_account_id, s.name AS student_name, s.status AS student_status,
        s.face_descriptor IS NOT NULL AS is_face_enrolled
        FROM student_roster sr
        LEFT JOIN students s ON s.username=sr.student_id
        ORDER BY sr.course ASC, sr.year_level ASC, sr.block ASC, sr.full_name ASC");
    $rows=[];while($row=$r->fetch_assoc())$rows[]=$row;
    echo json_encode(['success'=>true,'data'=>$rows]);
    break;

case 'get_no_face_students':
    // Approved students who haven't enrolled their face, grouped info included
    $db=getDB();
    $r=$db->query("SELECT id, name, username, course_year, block
        FROM students
        WHERE status='approved' AND (face_descriptor IS NULL OR face_descriptor='')
        ORDER BY course_year ASC, block ASC, name ASC");
    $rows=[];while($row=$r->fetch_assoc())$rows[]=$row;
    echo json_encode(['success'=>true,'data'=>$rows]);
    break;

// ── TEACHERS ─────────────────────────────────────────────
case 'teacher_register':
    $tid=trim($_POST['teacher_id']??'');$dn=trim($_POST['display_name']??'');
    $email=trim($_POST['email']??'');$pw=trim($_POST['password']??'');$cfm=trim($_POST['confirm']??'');
    if(!$tid||!$dn||!$email||!$pw||!$cfm){echo json_encode(['success'=>false,'message'=>'All fields are required.']);break;}
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)){echo json_encode(['success'=>false,'message'=>'Invalid email address.']);break;}
    if(strlen($pw)<6){echo json_encode(['success'=>false,'message'=>'Password min 6 characters.']);break;}
    if($pw!==$cfm){echo json_encode(['success'=>false,'message'=>'Passwords do not match.']);break;}
    if(!preg_match('/^[a-zA-Z0-9._\-]+$/',$tid)){echo json_encode(['success'=>false,'message'=>'Teacher ID: letters, numbers, dots, hyphens, underscores only.']);break;}
    $db=getDB();
    addColSafe($db,'teachers','status',"ENUM('pending','approved') DEFAULT 'approved'");
    addColSafe($db,'teachers','teacher_id','VARCHAR(100) NULL');
    addColSafe($db,'teachers','email','VARCHAR(255) NULL');
    $chk=$db->prepare("SELECT id FROM teachers WHERE username=? OR teacher_id=?");$chk->bind_param('ss',$tid,$tid);$chk->execute();
    if($chk->get_result()->num_rows>0){echo json_encode(['success'=>false,'message'=>'Teacher ID already registered.']);break;}
    $hash=password_hash($pw,PASSWORD_DEFAULT);
    $s=$db->prepare("INSERT INTO teachers (username,teacher_id,display_name,email,password_hash,status) VALUES (?,?,?,?,?,'approved')");
    $s->bind_param('sssss',$tid,$tid,$dn,$email,$hash);
    if($s->execute()){
        logActivity($db,'user_mgmt',"Teacher registration request: $dn ($tid)",'System');
        echo json_encode(['success'=>true]);
    } else {echo json_encode(['success'=>false,'message'=>$db->error]);}
    break;

case 'teacher_forgot_verify':
    $tid=trim($_POST['teacher_id']??'');$email=trim($_POST['email']??'');
    if(!$tid||!$email){echo json_encode(['success'=>false,'message'=>'All fields required.']);break;}
    $db=getDB();
    addColSafe($db,'teachers','email','VARCHAR(255) NULL');
    addColSafe($db,'teachers','teacher_id','VARCHAR(100) NULL');
    // Find teacher
    $s=$db->prepare("SELECT id,display_name FROM teachers WHERE (username=? OR teacher_id=?) AND email=? AND (status='approved' OR status IS NULL)");
    $s->bind_param('sss',$tid,$tid,$email);$s->execute();$row=$s->get_result()->fetch_assoc();
    if(!$row){echo json_encode(['success'=>false,'message'=>'Teacher ID and email do not match any approved account.']);break;}
    // Create table if needed
    $db->query("CREATE TABLE IF NOT EXISTS teacher_pw_reset_requests (
        id INT AUTO_INCREMENT PRIMARY KEY, teacher_id INT NOT NULL,
        status ENUM('pending','sent','rejected') DEFAULT 'pending',
        requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        handled_at TIMESTAMP NULL DEFAULT NULL)");
    // Check cooldown
    $cool=$db->prepare("SELECT id, TIMESTAMPDIFF(SECOND, requested_at, NOW()) AS sec_ago FROM teacher_pw_reset_requests WHERE teacher_id=? AND status='pending' ORDER BY requested_at DESC LIMIT 1");
    $cool->bind_param('i',$row['id']);$cool->execute();$existing=$cool->get_result()->fetch_assoc();
    if($existing){
        $rem=300-intval($existing['sec_ago']);
        if($rem>0){
            $mins=floor($rem/60);$secs=$rem%60;
            $msg=$mins>0?"Please wait {$mins} min ".($secs>0?"{$secs} sec ":"")."before submitting again."
                        :"Please wait {$secs} seconds before submitting again.";
            echo json_encode(['success'=>false,'message'=>$msg]);break;
        }
        $del=$db->prepare("DELETE FROM teacher_pw_reset_requests WHERE id=?");
        $del->bind_param('i',$existing['id']);$del->execute();
    }
    $ins=$db->prepare("INSERT INTO teacher_pw_reset_requests (teacher_id) VALUES (?)");
    $ins->bind_param('i',$row['id']);$ins->execute();
    logActivity($db,'user_mgmt',"Teacher password reset requested: {$row['display_name']} ($tid)",$row['display_name']);
    echo json_encode(['success'=>true,'message'=>'Request submitted! The admin will send you a temporary password via email.']);
    break;

case 'approve_teacher_reg':
    $id=intval($_POST['id']??0);$db=getDB();
    addColSafe($db,'teachers','status',"ENUM('pending','approved') DEFAULT 'approved'");
    $s=$db->prepare("UPDATE teachers SET status='approved' WHERE id=?");$s->bind_param('i',$id);$s->execute();
    $n=$db->prepare("SELECT display_name,username FROM teachers WHERE id=?");$n->bind_param('i',$id);$n->execute();
    $row=$n->get_result()->fetch_assoc();
    if($row)logActivity($db,'user_mgmt',"Approved teacher registration: {$row['display_name']} ({$row['username']})",'Admin');
    echo json_encode(['success'=>true]);
    break;

case 'reject_teacher_reg':
    $id=intval($_POST['id']??0);$db=getDB();
    addColSafe($db,'teachers','status',"ENUM('pending','approved') DEFAULT 'approved'");
    $n=$db->prepare("SELECT display_name,username FROM teachers WHERE id=?");$n->bind_param('i',$id);$n->execute();
    $row=$n->get_result()->fetch_assoc();
    $s=$db->prepare("DELETE FROM teachers WHERE id=?");$s->bind_param('i',$id);$s->execute();
    if($row)logActivity($db,'user_mgmt',"Rejected teacher registration: {$row['display_name']} ({$row['username']})",'Admin');
    echo json_encode(['success'=>$db->affected_rows>0]);
    break;

case 'get_pending_teachers':
    $db=getDB();
    // Ensure columns exist safely
    addColSafe($db,'teachers','status',"ENUM('pending','approved') DEFAULT 'approved'");
    addColSafe($db,'teachers','teacher_id','VARCHAR(100) NULL');
    $r=$db->query("SELECT id,username,teacher_id,display_name,created_at FROM teachers WHERE status='pending' ORDER BY created_at DESC");
    $rows=[];while($row=$r->fetch_assoc())$rows[]=$row;
    echo json_encode(['success'=>true,'data'=>$rows]);
    break;

case 'get_teacher_pw_reset_requests':
    $db=getDB();
    addColSafe($db,'teachers','email','VARCHAR(255) NULL');
    addColSafe($db,'teachers','teacher_id','VARCHAR(100) NULL');
    $db->query("CREATE TABLE IF NOT EXISTS teacher_pw_reset_requests (
        id INT AUTO_INCREMENT PRIMARY KEY, teacher_id INT NOT NULL,
        status ENUM('pending','sent','rejected') DEFAULT 'pending',
        requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        handled_at TIMESTAMP NULL DEFAULT NULL)");
    $r=$db->query("SELECT r.id, r.requested_at, t.id AS tid, t.display_name, t.username,
        IFNULL(t.teacher_id,t.username) AS t_id, IFNULL(t.email,'') AS email
        FROM teacher_pw_reset_requests r JOIN teachers t ON t.id=r.teacher_id
        WHERE r.status='pending' ORDER BY r.requested_at DESC");
    if(!$r){echo json_encode(['success'=>false,'message'=>$db->error]);break;}
    $rows=[];while($row=$r->fetch_assoc())$rows[]=$row;
    echo json_encode(['success'=>true,'data'=>$rows]);
    break;

case 'send_teacher_temp_password':
    $rid=intval($_POST['request_id']??0);
    if(!$rid){echo json_encode(['success'=>false,'message'=>'request_id required.']);break;}
    $db=getDB();
    $s=$db->prepare("SELECT r.id, t.id AS tid, t.display_name, t.username, t.teacher_id AS t_id, t.email
        FROM teacher_pw_reset_requests r JOIN teachers t ON t.id=r.teacher_id
        WHERE r.id=? AND r.status='pending'");
    $s->bind_param('i',$rid);$s->execute();$row=$s->get_result()->fetch_assoc();
    if(!$row){echo json_encode(['success'=>false,'message'=>'Request not found or already handled.']);break;}
    // Generate 6-digit numeric temp password
    $tempPw=str_pad(random_int(0,999999),6,'0',STR_PAD_LEFT);
    $hash=password_hash($tempPw,PASSWORD_DEFAULT);
    $u=$db->prepare("UPDATE teachers SET password_hash=? WHERE id=?");$u->bind_param('si',$hash,$row['tid']);$u->execute();
    $m=$db->prepare("UPDATE teacher_pw_reset_requests SET status='sent',handled_at=NOW() WHERE id=?");$m->bind_param('i',$rid);$m->execute();
    $emailResult=['success'=>false,'error'=>'No email'];
    if($row['email']&&filter_var($row['email'],FILTER_VALIDATE_EMAIL)){
        require_once __DIR__.'/mailer.php';
        $loginUrl='http://'.($_SERVER['HTTP_HOST']??'localhost').'/qr-event-attendance/login.php';
        $subject='Your Temporary Password — ZDSPGC QR Attendance';
        $html="<div style='font-family:sans-serif;max-width:520px;margin:0 auto;background:#f9fafb;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb'>
          <div style='background:#1b4332;padding:28px 32px;text-align:center'>
            <h1 style='color:#fff;margin:0;font-size:1.3rem'>ZDSPGC QR Attendance</h1>
            <p style='color:#a7f3d0;margin:6px 0 0;font-size:.88rem'>Teacher Password Reset</p>
          </div>
          <div style='padding:32px'>
            <p style='margin:0 0 16px;color:#111827'>Hi <strong>".htmlspecialchars($row['display_name'])."</strong>,</p>
            <p style='color:#374151;font-size:.9rem;line-height:1.6;margin:0 0 20px'>Your temporary password has been set by the admin. Use it to log in, then update your password in Settings.</p>
            <div style='background:#fff;border:1px solid #d1fae5;border-radius:8px;padding:20px;margin-bottom:24px'>
              <table style='width:100%;font-size:.9rem'>
                <tr><td style='color:#6b7280;padding:6px 0;width:40%'>Teacher ID</td>
                    <td style='font-weight:700;color:#1b4332;font-family:monospace'>".htmlspecialchars($row['t_id']??$row['username'])."</td></tr>
                <tr><td style='color:#6b7280;padding:6px 0'>Temporary Password</td>
                    <td style='font-weight:700;color:#dc2626;font-family:monospace;font-size:1.1rem'>".htmlspecialchars($tempPw)."</td></tr>
              </table>
            </div>
            <a href='".htmlspecialchars($loginUrl)."' style='display:inline-block;background:#2d6a4f;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600'>Log In →</a>
          </div>
        </div>";
        $emailResult=sendMail($row['email'],$row['display_name'],$subject,$html);
    }
    logActivity($db,'user_mgmt',"Temp password sent to teacher: {$row['display_name']}",'Admin');
    echo json_encode(['success'=>true,'email_sent'=>$emailResult['success'],'temp_password'=>$tempPw]);
    break;

case 'reject_teacher_pw_reset':
    $rid=intval($_POST['request_id']??0);
    if(!$rid){echo json_encode(['success'=>false,'message'=>'request_id required.']);break;}
    $db=getDB();
    $s=$db->prepare("UPDATE teacher_pw_reset_requests SET status='rejected',handled_at=NOW() WHERE id=? AND status='pending'");
    $s->bind_param('i',$rid);$s->execute();
    echo json_encode(['success'=>$db->affected_rows>0]);
    break;

case 'get_teachers':
    $db=getDB();
    addColSafe($db,'teachers','status',"ENUM('pending','approved') DEFAULT 'approved'");
    addColSafe($db,'teachers','teacher_id','VARCHAR(100) NULL');
    $r=$db->query("SELECT id,username,teacher_id,display_name,created_at FROM teachers WHERE status='approved' OR status IS NULL ORDER BY created_at ASC");
    $rows=[];while($row=$r->fetch_assoc())$rows[]=$row;
    echo json_encode(['success'=>true,'data'=>$rows]);
    break;

case 'add_teacher':
    $u=trim($_POST['username']??'');$dn=trim($_POST['display_name']??'');$pw=trim($_POST['password']??'');
    if(!$u||!$dn||!$pw){echo json_encode(['success'=>false,'message'=>'All fields required.']);break;}
    if(strlen($pw)<6){echo json_encode(['success'=>false,'message'=>'Password min 6 chars.']);break;}
    if(!preg_match('/^[a-zA-Z0-9._\-]+$/',$u)){echo json_encode(['success'=>false,'message'=>'Invalid username chars.']);break;}
    $db=getDB();
    $chk=$db->prepare("SELECT id FROM teachers WHERE username=?");$chk->bind_param('s',$u);$chk->execute();
    if($chk->get_result()->num_rows>0){echo json_encode(['success'=>false,'message'=>'Username already exists.']);break;}
    $hash=password_hash($pw,PASSWORD_DEFAULT);
    $s=$db->prepare("INSERT INTO teachers (username,display_name,password_hash) VALUES (?,?,?)");
    $s->bind_param('sss',$u,$dn,$hash);
    if($s->execute()){logActivity($db,'user_mgmt',"Added teacher: $dn ($u)",'Admin');echo json_encode(['success'=>true,'id'=>$db->insert_id]);}
    else{echo json_encode(['success'=>false,'message'=>$db->error]);}
    break;

case 'update_teacher':
    $id=intval($_POST['id']??0);$dn=trim($_POST['display_name']??'');$pw=trim($_POST['password']??'');
    if(!$id||!$dn){echo json_encode(['success'=>false,'message'=>'ID and display name required.']);break;}
    $db=getDB();
    if($pw!==''){
        if(strlen($pw)<6){echo json_encode(['success'=>false,'message'=>'Password min 6 chars.']);break;}
        $hash=password_hash($pw,PASSWORD_DEFAULT);
        $s=$db->prepare("UPDATE teachers SET display_name=?,password_hash=? WHERE id=?");
        $s->bind_param('ssi',$dn,$hash,$id);
    } else {
        $s=$db->prepare("UPDATE teachers SET display_name=? WHERE id=?");
        $s->bind_param('si',$dn,$id);
    }
    if($s->execute()){logActivity($db,'user_mgmt',"Updated teacher: $dn",'Admin');echo json_encode(['success'=>true]);}
    else{echo json_encode(['success'=>false,'message'=>$db->error]);}
    break;

case 'delete_teacher':
    $id=intval($_POST['id']??0);$db=getDB();
    $n=$db->prepare("SELECT username,display_name FROM teachers WHERE id=?");
    $n->bind_param('i',$id);$n->execute();$row=$n->get_result()->fetch_assoc();
    $s=$db->prepare("DELETE FROM teachers WHERE id=?");$s->bind_param('i',$id);$s->execute();
    if($row){logActivity($db,'user_mgmt',"Deleted teacher: {$row['display_name']} ({$row['username']})",'Admin');}
    echo json_encode(['success'=>true]);
    break;

// ── TEACHER ACTIONS ───────────────────────────────────────
case 'teacher_change_password':
    if(empty($_SESSION['teacher_id'])){echo json_encode(['success'=>false,'message'=>'Not logged in.']);break;}
    $cur=trim($_POST['current_password']??'');$new=trim($_POST['new_password']??'');$cfm=trim($_POST['confirm_password']??'');
    if(!$cur||!$new||!$cfm){echo json_encode(['success'=>false,'message'=>'All fields required.']);break;}
    if(strlen($new)<6){echo json_encode(['success'=>false,'message'=>'New password min 6 chars.']);break;}
    if($new!==$cfm){echo json_encode(['success'=>false,'message'=>'Passwords do not match.']);break;}
    $db=getDB();$tid=intval($_SESSION['teacher_id']);
    $s=$db->prepare("SELECT password_hash,username,display_name FROM teachers WHERE id=?");
    $s->bind_param('i',$tid);$s->execute();$row=$s->get_result()->fetch_assoc();
    if(!$row||!password_verify($cur,$row['password_hash'])){echo json_encode(['success'=>false,'message'=>'Current password incorrect.']);break;}
    $hash=password_hash($new,PASSWORD_DEFAULT);
    $u=$db->prepare("UPDATE teachers SET password_hash=? WHERE id=?");$u->bind_param('si',$hash,$tid);$u->execute();
    logActivity($db,'user_mgmt',"Teacher changed password: {$row['display_name']}",$row['display_name']);
    echo json_encode(['success'=>true]);
    break;

case 'teacher_add_student':
    $name=trim($_POST['name']??'');$uname=trim($_POST['username']??'');$phone=trim($_POST['phone']??'');
    $cy=trim($_POST['course_year']??'');$block=trim($_POST['block']??'');$pw=trim($_POST['password']??'');
    if(!$name||!$uname||!$phone||!$cy||!$block||!$pw){echo json_encode(['success'=>false,'message'=>'All fields required.']);break;}
    if(strlen($pw)<6){echo json_encode(['success'=>false,'message'=>'Password min 6 chars.']);break;}
    if(!preg_match('/^[a-zA-Z0-9._\-]+$/',$uname)){echo json_encode(['success'=>false,'message'=>'Invalid Student ID chars.']);break;}
    $db=getDB();
    $chk=$db->prepare("SELECT id FROM students WHERE username=?");$chk->bind_param('s',$uname);$chk->execute();
    if($chk->get_result()->num_rows>0){echo json_encode(['success'=>false,'message'=>'Student ID already exists.']);break;}
    $token=null;
    for($i=0;$i<30;$i++){
        $t=str_pad(random_int(100000,999999),6,'0',STR_PAD_LEFT);
        $c=$db->prepare("SELECT id FROM students WHERE qr_token=?");$c->bind_param('s',$t);$c->execute();
        if($c->get_result()->num_rows===0){$token=$t;break;}
    }
    if(!$token){echo json_encode(['success'=>false,'message'=>'Could not generate QR token.']);break;}
    $hash=password_hash($pw,PASSWORD_DEFAULT);
    $s=$db->prepare("INSERT INTO students (name,username,phone,course_year,block,status,password_hash,qr_token,must_change_password) VALUES (?,?,?,?,?,'approved',?,?,1)");
    $s->bind_param('sssssss',$name,$uname,$phone,$cy,$block,$hash,$token);
    if($s->execute()){
        $sid=$db->insert_id;
        $db->query("INSERT IGNORE INTO attendees (event_id,student_id) SELECT id,$sid FROM events");
        $roster=$db->prepare("INSERT IGNORE INTO student_roster (student_id,full_name,course,year_level,block,uploaded_by) VALUES (?,?,?,?,?,?)");
        [$course,$year]=array_pad(explode('-',$cy,2),2,'1');$uploader='Teacher';
        $roster->bind_param('sssiss',$uname,$name,$course,$year,$block,$uploader);$roster->execute();
        logActivity($db,'registration',"Teacher added student: $name ($uname)",'Teacher');
        $mailResult=['success'=>false,'error'=>'Email not sent'];
        if(filter_var($phone,FILTER_VALIDATE_EMAIL)){
            require_once __DIR__.'/mailer.php';
            $mailResult=sendWelcomeEmail($phone,$name,$uname,$pw);
        }
        echo json_encode(['success'=>true,'id'=>$sid,'qr_token'=>$token,'email_sent'=>$mailResult['success'],'email_note'=>$mailResult['success']?'Welcome email sent':'Email not sent: '.($mailResult['error']??'Invalid email')]);
    } else {echo json_encode(['success'=>false,'message'=>$db->error]);}
    break;

// ── ADMIN PASSWORD RESETS ─────────────────────────────────
case 'admin_reset_student_password':
    $id=intval($_POST['id']??0);$pw=trim($_POST['password']??'');
    if(!$id||!$pw){echo json_encode(['success'=>false,'message'=>'ID and password required.']);break;}
    if(strlen($pw)<6){echo json_encode(['success'=>false,'message'=>'Password min 6 chars.']);break;}
    $db=getDB();$hash=password_hash($pw,PASSWORD_DEFAULT);
    $s=$db->prepare("UPDATE students SET password_hash=? WHERE id=?");$s->bind_param('si',$hash,$id);
    if($s->execute()){
        $n=$db->prepare("SELECT name,username FROM students WHERE id=?");$n->bind_param('i',$id);$n->execute();
        $row=$n->get_result()->fetch_assoc();
        if($row)logActivity($db,'user_mgmt',"Admin reset password for student: {$row['name']} ({$row['username']})",'Admin');
        echo json_encode(['success'=>true]);
    }else{echo json_encode(['success'=>false,'message'=>$db->error]);}
    break;

// ── ROSTER ────────────────────────────────────────────────
case 'upload_roster':
    $raw=trim($_POST['records']??'');$uploader=trim($_POST['uploaded_by']??'Teacher');
    if(!$raw){echo json_encode(['success'=>false,'message'=>'No data provided.']);break;}
    $records=json_decode($raw,true);
    if(!is_array($records)||empty($records)){echo json_encode(['success'=>false,'message'=>'Invalid data format.']);break;}
    $db=getDB();
    $s=$db->prepare("INSERT INTO student_roster (student_id,full_name,course,year_level,block,uploaded_by)
        VALUES (?,?,?,?,?,?) ON DUPLICATE KEY UPDATE full_name=VALUES(full_name),course=VALUES(course),
        year_level=VALUES(year_level),block=VALUES(block),uploaded_by=VALUES(uploaded_by),uploaded_at=CURRENT_TIMESTAMP");
    $inserted=0;$updated=0;$errors=[];
    foreach($records as $i=>$row){
        $sid=trim($row['student_id']??'');$name=trim($row['full_name']??'');
        $crs=strtoupper(trim($row['course']??''));$yr=intval($row['year_level']??0);$blk=strtoupper(trim($row['block']??''));
        if(!$sid||!$name){$errors[]="Row ".($i+1).": student_id and full_name required.";continue;}
        if($yr<1||$yr>5){$errors[]="Row ".($i+1).": year_level must be 1-4.";continue;}
        $s->bind_param('sssiss',$sid,$name,$crs,$yr,$blk,$uploader);$s->execute();
        if($db->affected_rows===1)$inserted++;elseif($db->affected_rows===2)$updated++;
    }
    logActivity($db,'roster',"Roster upload: $inserted new, $updated updated by $uploader",$uploader);
    echo json_encode(['success'=>true,'inserted'=>$inserted,'updated'=>$updated,'errors'=>$errors,
        'message'=>"$inserted added, $updated updated.".(count($errors)?' '.count($errors).' skipped.':'')]);
    break;

case 'lookup_roster':
    $sid=trim($_GET['student_id']??'');
    if(!$sid){echo json_encode(['success'=>false,'message'=>'student_id required.']);break;}
    $db=getDB();
    $s=$db->prepare("SELECT student_id,full_name,course,year_level,block FROM student_roster WHERE student_id=?");
    $s->bind_param('s',$sid);$s->execute();$row=$s->get_result()->fetch_assoc();
    echo $row?json_encode(['success'=>true,'data'=>$row]):json_encode(['success'=>false,'message'=>'No record found. Contact your teacher or admin.']);
    break;

case 'get_roster':
    $db=getDB();$q=trim($_GET['q']??'');
    if($q){
        $like='%'.$q.'%';
        $s=$db->prepare("SELECT * FROM student_roster WHERE student_id LIKE ? OR full_name LIKE ? ORDER BY full_name ASC LIMIT 100");
        $s->bind_param('ss',$like,$like);$s->execute();$result=$s->get_result();
    }else{$result=$db->query("SELECT * FROM student_roster ORDER BY full_name ASC LIMIT 500");}
    $rows=[];while($row=$result->fetch_assoc())$rows[]=$row;
    echo json_encode(['success'=>true,'data'=>$rows,'total'=>count($rows)]);
    break;

case 'clear_roster':
    $db=getDB();$db->query("TRUNCATE TABLE student_roster");
    logActivity($db,'roster','Roster cleared','System');
    echo json_encode(['success'=>true]);
    break;

case 'delete_roster_entry':
    $sid=trim($_POST['student_id']??'');
    if(!$sid){echo json_encode(['success'=>false,'message'=>'student_id required.']);break;}
    $db=getDB();
    $s=$db->prepare("DELETE FROM student_roster WHERE student_id=?");
    $s->bind_param('s',$sid);$s->execute();
    if($db->affected_rows>0){
        logActivity($db,'roster',"Removed roster entry: $sid",'Teacher');
        echo json_encode(['success'=>true]);
    } else {
        echo json_encode(['success'=>false,'message'=>'Entry not found.']);
    }
    break;

default:
    echo json_encode(['success'=>false,'message'=>'Unknown action.']);
}
