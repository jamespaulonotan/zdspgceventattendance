<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'qr_attendance');

function getDB() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            $test = new mysqli(DB_HOST, DB_USER, DB_PASS);
            if ($test->connect_error) {
                echo json_encode(['success' => false, 'message' => 'Cannot connect to MySQL. Is XAMPP running?']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database "' . DB_NAME . '" not found. Please visit setup.php first.']);
            }
            exit;
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}
