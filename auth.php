<?php
// Secure session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

// CSRF Token Functions
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Admin credentials - TODO: Move to database for production!
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'zdspgc2024');

function requireLogin() {
    if (empty($_SESSION['logged_in'])) {
        header('Location: login.php'); exit;
    }
}
function requireAdminLogin() {
    if (empty($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'admin') {
        header('Location: login.php'); exit;
    }
}
function requireTeacherLogin() {
    if (empty($_SESSION['logged_in']) || !in_array($_SESSION['role'] ?? '', ['admin','teacher'])) {
        header('Location: login.php'); exit;
    }
}

function doLogin($user, $pass, $role = 'admin') {
    if ($role === 'admin') {
        if ($user === ADMIN_USER && $pass === ADMIN_PASS) {
            $_SESSION['logged_in']  = true;
            $_SESSION['admin_user'] = $user;
            $_SESSION['role']       = 'admin';
            return true;
        }
        return false;
    }
    if ($role === 'teacher') {
        try {
            require_once __DIR__ . '/db.php';
            $db   = getDB();
            $stmt = $db->prepare("SELECT id, username, display_name, password_hash, status FROM teachers WHERE (username=? OR teacher_id=?) LIMIT 1");
            if ($stmt) {
                $stmt->bind_param('ss', $user, $user);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                if ($row && password_verify($pass, $row['password_hash'])) {
                    if (($row['status'] ?? 'approved') === 'pending') {
                        return 'pending';
                    }
                    $_SESSION['logged_in']    = true;
                    $_SESSION['admin_user']   = $row['username'];
                    $_SESSION['teacher_name'] = $row['display_name'];
                    $_SESSION['teacher_id']   = $row['id'];
                    $_SESSION['role']         = 'teacher';
                    return true;
                }
                if ($row) return false;
            }
        } catch (Throwable $e) {}
        return false;
    }
    return false;
}

function doLogout() {
    session_destroy();
    header('Location: login.php'); exit;
}
