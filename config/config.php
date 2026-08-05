<?php
$server = "localhost";
$uname = "root";
$pwd = "";
$dbname = "student_performance";

$conn = mysqli_connect($server, $uname, $pwd, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

session_start();

// Base URL for absolute path references (fixes sidebar links in subdirectories)
$base_url = '/student_performance';
define('BASE_URL', '/student_performance');

// Auto-migrate database schema (adds student_id column & updates role enum if missing)
try {
    $col_check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'student_id'");
    if ($col_check && mysqli_num_rows($col_check) == 0) {
        mysqli_query($conn, "ALTER TABLE users ADD COLUMN student_id INT(11) DEFAULT NULL AFTER role");
    }
    $role_col = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'role'");
    if ($role_col) {
        $rc = mysqli_fetch_assoc($role_col);
        if ($rc && strpos($rc['Type'], 'faculty') === false) {
            mysqli_query($conn, "ALTER TABLE users MODIFY COLUMN role ENUM('admin','faculty','student') DEFAULT 'faculty'");
        }
    }
// Ensure admin user exists (create if missing) = admin/admin123
    $admin_fetch = mysqli_query($conn, "SELECT id, password FROM users WHERE username = 'admin' LIMIT 1");
    if ($admin_fetch && mysqli_num_rows($admin_fetch) > 0) {
        $af = mysqli_fetch_assoc($admin_fetch);
        if ($af['password'] === 'admin123') {
            $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE users SET password = '$new_hash' WHERE id = " . (int)$af['id']);
        }
    } else {
        $admin_hash = password_hash('admin123', PASSWORD_DEFAULT);
        mysqli_query($conn, "INSERT INTO users (username, email, password, role) VALUES ('admin', 'admin@example.com', '$admin_hash', 'admin')");
    }
} catch (Exception $e) {
    // Ignore migration errors (e.g., table not yet created)
}

// Include auth helpers
require_once __DIR__ . '/../inc/auth.php';
?>

