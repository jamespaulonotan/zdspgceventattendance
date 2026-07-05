<?php
header('Content-Type: application/json');
require_once 'auth.php';

$user = trim($_POST['username'] ?? '');
$pass = trim($_POST['password'] ?? '');
$role = trim($_POST['role']     ?? 'admin');

if (!$user || !$pass) {
    echo json_encode(['success' => false, 'message' => 'Please enter username and password.']);
    exit;
}

$result = doLogin($user, $pass, $role);
if ($result === true) {
    $redirect = $role === 'teacher' ? 'teacher_dashboard.php' : 'index.php';
    echo json_encode(['success' => true, 'redirect' => $redirect]);
} elseif ($result === 'pending') {
    echo json_encode(['success' => false, 'message' => 'Your account is pending admin approval.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid Teacher ID or password.']);
}
