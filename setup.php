<?php
$conn = new mysqli('localhost','root','','');
if($conn->connect_error) die('Connection failed: '.$conn->connect_error);
$conn->query("CREATE DATABASE IF NOT EXISTS qr_attendance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db('qr_attendance');

// Add event_end_time column if it doesn't exist yet (safe migration)
$conn->query("ALTER TABLE events ADD COLUMN IF NOT EXISTS event_end_time TIME NULL AFTER event_time");
// Add attendance_started flag
$conn->query("ALTER TABLE events ADD COLUMN IF NOT EXISTS attendance_started TINYINT(1) DEFAULT 0 AFTER event_end_time");
// Add status column to teachers for pending registration
$conn->query("ALTER TABLE teachers ADD COLUMN IF NOT EXISTS status ENUM('pending','approved') DEFAULT 'approved' AFTER password_hash");
$conn->query("ALTER TABLE teachers ADD COLUMN IF NOT EXISTS teacher_id VARCHAR(100) NULL AFTER username");
$conn->query("ALTER TABLE teachers ADD COLUMN IF NOT EXISTS email VARCHAR(255) NULL AFTER display_name");
// Add password_reset_requests table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS password_reset_requests (
    id INT AUTO_INCREMENT PRIMARY KEY, student_id INT NOT NULL,
    status ENUM('pending','sent','rejected') DEFAULT 'pending',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    handled_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE)");

$tables = [
"CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) NOT NULL,
    description TEXT, event_date DATE NOT NULL, event_time TIME, event_end_time TIME, location VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
"CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL, phone VARCHAR(100),
    course_year VARCHAR(100), block VARCHAR(50), status ENUM('pending','approved','rejected') DEFAULT 'pending',
    password_hash VARCHAR(255) NOT NULL, qr_token VARCHAR(6) UNIQUE NOT NULL,
    face_descriptor LONGTEXT DEFAULT NULL, must_change_password TINYINT(1) DEFAULT 0,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
"CREATE TABLE IF NOT EXISTS teachers (
    id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(100) UNIQUE NOT NULL,
    display_name VARCHAR(255) NOT NULL, password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
"CREATE TABLE IF NOT EXISTS attendees (
    id INT AUTO_INCREMENT PRIMARY KEY, event_id INT NOT NULL, student_id INT NOT NULL,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    UNIQUE KEY unique_event_student (event_id,student_id))",
"CREATE TABLE IF NOT EXISTS attendance_log (
    id INT AUTO_INCREMENT PRIMARY KEY, student_id INT NOT NULL, event_id INT NOT NULL,
    scanned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE)",
"CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL, description TEXT, actor VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
"CREATE TABLE IF NOT EXISTS student_roster (
    id INT AUTO_INCREMENT PRIMARY KEY, student_id VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL, course VARCHAR(50), year_level INT DEFAULT 1,
    block VARCHAR(10), course_year VARCHAR(100) GENERATED ALWAYS AS (CONCAT(course,'-',year_level)) STORED,
    uploaded_by VARCHAR(255), uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
"CREATE TABLE IF NOT EXISTS password_reset_requests (
    id INT AUTO_INCREMENT PRIMARY KEY, student_id INT NOT NULL,
    status ENUM('pending','sent','rejected') DEFAULT 'pending',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    handled_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    UNIQUE KEY one_pending_per_student (student_id, status))"
];

foreach($tables as $sql){
    if(!$conn->query($sql)) die('Error: '.$conn->error.'<br>SQL: '.$sql);
}
$conn->close();
echo '<div style="font-family:sans-serif;padding:40px;max-width:500px;margin:auto;text-align:center;">
  <h2 style="color:#22c55e;">Setup Complete!</h2>
  <p>Database and tables created successfully.</p>
  <a href="index.php" style="display:inline-block;margin-top:16px;padding:10px 24px;background:#2d6a4f;color:#fff;border-radius:8px;text-decoration:none;font-weight:600">Go to Admin &rarr;</a>
  &nbsp;
  <a href="student_login.php" style="display:inline-block;margin-top:16px;padding:10px 24px;background:#52b788;color:#1b4332;border-radius:8px;text-decoration:none;font-weight:600">Student Portal &rarr;</a>
</div>';
