<?php
header('Content-Type: application/json');
define('RECOVERY_KEY','ZDSPGC-RESET');
$auth_file = __DIR__.'/auth.php';
$action = $_POST['action']??'';

if($action==='verify_key'){
    $key=trim($_POST['key']??'');
    if($key===RECOVERY_KEY){
        session_start();$_SESSION['reset_verified']=true;
        echo json_encode(['success'=>true]);
    }else{echo json_encode(['success'=>false,'message'=>'Invalid recovery key.']);}
    exit;
}
if($action==='save_password'){
    session_start();
    if(empty($_SESSION['reset_verified'])){echo json_encode(['success'=>false,'message'=>'Session expired.']);exit;}
    $pw=$_POST['password']??'';$cfm=$_POST['confirm']??'';
    if(strlen($pw)<6){echo json_encode(['success'=>false,'message'=>'Password min 6 characters.']);exit;}
    if($pw!==$cfm){echo json_encode(['success'=>false,'message'=>'Passwords do not match.']);exit;}
    $safe=addslashes($pw);
    $content=file_get_contents($auth_file);
    $updated=preg_replace("/define\('ADMIN_PASS',\s*'.*?'\);/","define('ADMIN_PASS', '$safe');",$content);
    if($updated===null||$updated===$content){echo json_encode(['success'=>false,'message'=>'Could not update password. Check file permissions.']);exit;}
    file_put_contents($auth_file,$updated);
    unset($_SESSION['reset_verified']);
    echo json_encode(['success'=>true,'message'=>'Password updated! You can now log in.']);
    exit;
}
echo json_encode(['success'=>false,'message'=>'Invalid action.']);
