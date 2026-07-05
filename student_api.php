<?php
header('Content-Type: application/json');
set_error_handler(function($errno,$errstr){echo json_encode(['success'=>false,'message'=>"PHP Error: $errstr"]);exit;});
require_once 'db.php';
require_once 'auth.php'; // For CSRF functions
// session_start(); // Already started by auth.php


$action=$_REQUEST['action']??'';

// Validate CSRF Token for non-GET requests (except public actions)
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    $public_post_actions = ['register', 'login', 'forgot_verify'];
    if (!in_array($action, $public_post_actions)) {
        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!validateCsrfToken($csrf_token)) {
            echo json_encode(['success' => false, 'message' => 'CSRF validation failed. Please refresh the page and try again.']);
            exit;
        }
    }
}

function generateToken($db){
    for($i=0;$i<30;$i++){
        $t=str_pad(random_int(100000,999999),6,'0',STR_PAD_LEFT);
        $c=$db->prepare("SELECT id FROM students WHERE qr_token=?");
        $c->bind_param('s',$t);$c->execute();
        if($c->get_result()->num_rows===0)return $t;
    }
    return null;
}
function logActivity($db,$type,$description,$actor=''){
    $s=$db->prepare("INSERT INTO activity_log (type,description,actor) VALUES (?,?,?)");
    $s->bind_param('sss',$type,$description,$actor);$s->execute();
}

switch($action){
case 'register':
    $name=trim($_POST['name']??'');$uname=trim($_POST['username']??'');$phone=trim($_POST['phone']??'');
    $cy=trim($_POST['course_year']??'');$block=trim($_POST['block']??'');
    $pw=trim($_POST['password']??'');$cfm=trim($_POST['confirm']??'');
    if(!$name||!$uname||!$phone||!$cy||!$block||!$pw){echo json_encode(['success'=>false,'message'=>'All fields are required.']);break;}
    if(strlen($uname)<3){echo json_encode(['success'=>false,'message'=>'Student ID min 3 characters.']);break;}
    if(!preg_match('/^[a-zA-Z0-9._\-]+$/',$uname)){echo json_encode(['success'=>false,'message'=>'Student ID: letters, numbers, dots, hyphens, underscores only.']);break;}
    if(strlen($pw)<6){echo json_encode(['success'=>false,'message'=>'Password min 6 characters.']);break;}
    if($pw!==$cfm){echo json_encode(['success'=>false,'message'=>'Passwords do not match.']);break;}
    $db=getDB();
    $chkU=$db->prepare("SELECT id FROM students WHERE username=?");$chkU->bind_param('s',$uname);$chkU->execute();
    if($chkU->get_result()->num_rows>0){echo json_encode(['success'=>false,'message'=>'Student ID already registered.']);break;}
    $chkP=$db->prepare("SELECT id FROM students WHERE phone=?");$chkP->bind_param('s',$phone);$chkP->execute();
    if($chkP->get_result()->num_rows>0){echo json_encode(['success'=>false,'message'=>'Email already registered.']);break;}
    $token=generateToken($db);
    if(!$token){echo json_encode(['success'=>false,'message'=>'Could not generate ID. Try again.']);break;}
    $hash=password_hash($pw,PASSWORD_DEFAULT);
    $s=$db->prepare("INSERT INTO students (name,username,phone,course_year,block,status,password_hash,qr_token) VALUES (?,?,?,?,?,'pending',?,?)");
    $s->bind_param('sssssss',$name,$uname,$phone,$cy,$block,$hash,$token);
    if($s->execute()){
        logActivity($db,'registration',"New student registered: $name (ID: $uname) — awaiting approval",$name);
        echo json_encode(['success'=>true,'pending'=>true,'message'=>'Registration submitted! Await admin approval.']);
    }else{echo json_encode(['success'=>false,'message'=>$db->error]);}
    break;

case 'login':
    $uname=trim($_POST['username']??'');$pw=trim($_POST['password']??'');
    if(!$uname||!$pw){echo json_encode(['success'=>false,'message'=>'Username and password required.']);break;}
    $db=getDB();
    $s=$db->prepare("SELECT * FROM students WHERE username=?");$s->bind_param('s',$uname);$s->execute();
    $student=$s->get_result()->fetch_assoc();
    if(!$student||!password_verify($pw,$student['password_hash'])){echo json_encode(['success'=>false,'message'=>'Invalid username or password.']);break;}
    if($student['status']==='pending'){echo json_encode(['success'=>false,'message'=>'Account pending admin approval.']);break;}
    if($student['status']==='rejected'){echo json_encode(['success'=>false,'message'=>'Account rejected. Contact admin.']);break;}
    $_SESSION['student_id']=$student['id'];
    $_SESSION['student_name']=$student['name'];
    $_SESSION['must_change_password']=intval($student['must_change_password']??0);
    logActivity($db,'login',"Student logged in: {$student['name']} ({$student['username']})",$student['name']);
    echo json_encode(['success'=>true,'must_change_password'=>!empty($student['must_change_password'])]);
    break;

case 'logout':
    session_destroy();
    echo json_encode(['success'=>true]);
    break;

case 'forgot_verify':
    $uname=trim($_POST['username']??'');$phone=trim($_POST['phone']??'');
    if(!$uname||!$phone){echo json_encode(['success'=>false,'message'=>'All fields required.']);break;}
    $db=getDB();
    // Ensure table exists
    $db->query("CREATE TABLE IF NOT EXISTS password_reset_requests (
        id INT AUTO_INCREMENT PRIMARY KEY, student_id INT NOT NULL,
        status ENUM('pending','sent','rejected') DEFAULT 'pending',
        requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        handled_at TIMESTAMP NULL DEFAULT NULL,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE)");
    $s=$db->prepare("SELECT id,name FROM students WHERE username=? AND phone=? AND status='approved'");
    $s->bind_param('ss',$uname,$phone);$s->execute();$row=$s->get_result()->fetch_assoc();
    if(!$row){echo json_encode(['success'=>false,'message'=>'Student ID and email do not match any approved account.']);break;}
    // Check if already has a pending request OR submitted one within the last 5 minutes
    $chk=$db->prepare("SELECT id, TIMESTAMPDIFF(SECOND, requested_at, NOW()) AS seconds_ago FROM password_reset_requests WHERE student_id=? AND status='pending' ORDER BY requested_at DESC LIMIT 1");
    $chk->bind_param('i',$row['id']);$chk->execute();$existing=$chk->get_result()->fetch_assoc();
    if($existing){
        $secondsAgo=(int)$existing['seconds_ago'];
        $remaining=300-$secondsAgo; // 5 minutes = 300 seconds
        if($remaining>0){
            $mins=floor($remaining/60);$secs=$remaining%60;
            $waitMsg=$mins>0?"Please wait {$mins} min ".($secs>0?"{$secs} sec ":"")."before submitting again."
                            :"Please wait {$secs} seconds before submitting again.";
            echo json_encode(['success'=>false,'message'=>$waitMsg]);break;
        }
        // 5 minutes passed — allow resubmit, delete old request
        $del=$db->prepare("DELETE FROM password_reset_requests WHERE id=?");
        $del->bind_param('i',$existing['id']);$del->execute();
    }
    // Create new pending request
    $ins=$db->prepare("INSERT INTO password_reset_requests (student_id) VALUES (?)");
    $ins->bind_param('i',$row['id']);$ins->execute();
    logActivity($db,'user_mgmt',"Password reset requested by: {$row['name']} ($uname)",$row['name']);
    echo json_encode(['success'=>true,'message'=>'Request submitted! The admin will send you a temporary password via email.']);
    break;

case 'student_change_password':
    if(empty($_SESSION['student_id'])){echo json_encode(['success'=>false,'message'=>'Not logged in.']);break;}
    $pw=trim($_POST['password']??'');$cfm=trim($_POST['confirm']??'');
    if(strlen($pw)<6){echo json_encode(['success'=>false,'message'=>'Password min 6 characters.']);break;}
    if($pw!==$cfm){echo json_encode(['success'=>false,'message'=>'Passwords do not match.']);break;}
    $hash=password_hash($pw,PASSWORD_DEFAULT);$sid=intval($_SESSION['student_id']);
    $db=getDB();$s=$db->prepare("UPDATE students SET password_hash=?,must_change_password=0 WHERE id=?");
    $s->bind_param('si',$hash,$sid);$s->execute();
    $_SESSION['must_change_password']=0;
    echo json_encode(['success'=>true]);
    break;

case 'get_dashboard':
    if(empty($_SESSION['student_id'])){echo json_encode(['success'=>false,'message'=>'Not logged in.']);break;}
    $sid=intval($_SESSION['student_id']);$db=getDB();
    $sS=$db->prepare("SELECT id,name,phone,course_year,block,qr_token,registered_at,face_descriptor FROM students WHERE id=?");
    $sS->bind_param('i',$sid);$sS->execute();$student=$sS->get_result()->fetch_assoc();
    $r1=$db->prepare("SELECT COUNT(*) AS c FROM attendees WHERE student_id=?");$r1->bind_param('i',$sid);$r1->execute();
    $treg=$r1->get_result()->fetch_assoc()['c'];
    $r2=$db->prepare("SELECT COUNT(DISTINCT event_id) AS c FROM attendance_log WHERE student_id=?");$r2->bind_param('i',$sid);$r2->execute();
    $tatt=$r2->get_result()->fetch_assoc()['c'];
    // Get all events: past 7 days + future (to let client categorize)
    $allEventsRes=$db->query("SELECT id,name,event_date,event_time,event_end_time,attendance_started,location FROM events
        WHERE event_date>=DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        ORDER BY event_date ASC, event_time ASC");
    $events=[];while($row=$allEventsRes->fetch_assoc())$events[]=$row;
    $lS=$db->prepare("SELECT e.name AS event_name,e.event_date,e.location,al.scanned_at FROM attendance_log al JOIN events e ON e.id=al.event_id WHERE al.student_id=? ORDER BY al.scanned_at DESC LIMIT 20");
    $lS->bind_param('i',$sid);$lS->execute();$logs=[];$lR=$lS->get_result();while($row=$lR->fetch_assoc())$logs[]=$row;
    echo json_encode(['success'=>true,'student'=>$student,'total_registered'=>$treg,'total_attended'=>$tatt,'events'=>$events,'logs'=>$logs]);
    break;
case 'get_events':
    if(empty($_SESSION['student_id'])){echo json_encode(['success'=>false,'message'=>'Not logged in.']);break;}
    $db=getDB();
    // Get all events
    $allRes=$db->query("SELECT id,name,event_date,event_time,event_end_time,attendance_started,location FROM events ORDER BY event_date ASC, event_time ASC");
    $events=[];while($row=$allRes->fetch_assoc())$events[]=$row;
    echo json_encode(['success'=>true,'events'=>$events]);
    break;

case 'save_face_descriptor':
    if(empty($_SESSION['student_id'])){echo json_encode(['success'=>false,'message'=>'Not logged in.']);break;}
    $desc=trim($_POST['descriptor']??'');
    if(!$desc){echo json_encode(['success'=>false,'message'=>'No descriptor provided.']);break;}
    $arr=json_decode($desc,true);
    if(!is_array($arr)||count($arr)!==128){echo json_encode(['success'=>false,'message'=>'Invalid face descriptor.']);break;}
    $sid=intval($_SESSION['student_id']);$db=getDB();
    $s=$db->prepare("UPDATE students SET face_descriptor=? WHERE id=?");$s->bind_param('si',$desc,$sid);$s->execute();
    echo json_encode(['success'=>true]);
    break;

case 'get_face_descriptors':
    $db=getDB();
    $r=$db->query("SELECT id,name,qr_token,face_descriptor FROM students WHERE status='approved' AND face_descriptor IS NOT NULL");
    $rows=[];while($row=$r->fetch_assoc()){
        $rows[]=['id'=>intval($row['id']),'name'=>$row['name'],'qr_token'=>$row['qr_token'],'descriptor'=>json_decode($row['face_descriptor'],true)];
    }
    echo json_encode(['success'=>true,'data'=>$rows]);
    break;

case 'lookup_roster':
    $sid=trim($_GET['student_id']??'');
    if(!$sid){echo json_encode(['success'=>false,'message'=>'student_id required.']);break;}
    $db=getDB();
    $chk=$db->prepare("SELECT id,status FROM students WHERE username=?");$chk->bind_param('s',$sid);$chk->execute();
    $existing=$chk->get_result()->fetch_assoc();
    if($existing){echo json_encode(['success'=>false,'already_exists'=>true,'message'=>'Student ID already has an account. Please log in.']);break;}
    $s=$db->prepare("SELECT student_id,full_name,course,year_level,block FROM student_roster WHERE student_id=?");
    $s->bind_param('s',$sid);$s->execute();$row=$s->get_result()->fetch_assoc();
    echo $row?json_encode(['success'=>true,'data'=>$row]):json_encode(['success'=>false,'message'=>'No record found. Contact your teacher or admin.']);
    break;

case 'check_session':
    echo json_encode(['logged_in'=>!empty($_SESSION['student_id']),'name'=>$_SESSION['student_name']??'']);
    break;

default:
    echo json_encode(['success'=>false,'message'=>'Unknown action.']);
}
