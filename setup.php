<?php
$server = "localhost";
$uname = "root";
$pwd = "";

$temp_conn = mysqli_connect($server, $uname, $pwd);

$setup_success = false;
$setup_message = '';

if ($temp_conn) {
    mysqli_query($temp_conn, "CREATE DATABASE IF NOT EXISTS student_performance");
    mysqli_select_db($temp_conn, "student_performance");
    
    $str1 = "CREATE TABLE IF NOT EXISTS users (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'teacher') DEFAULT 'teacher',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $str2 = "CREATE TABLE IF NOT EXISTS students (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        enrollment_no VARCHAR(20) NOT NULL UNIQUE,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(15) NOT NULL,
        gender ENUM('Male', 'Female', 'Other') NOT NULL,
        date_of_birth DATE NOT NULL,
        address TEXT,
        semester INT(2) NOT NULL,
        course VARCHAR(100) NOT NULL,
        profile_image VARCHAR(255) DEFAULT 'default.png',
        created_by INT(11),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $str3 = "CREATE TABLE IF NOT EXISTS subjects (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        subject_code VARCHAR(20) NOT NULL UNIQUE,
        subject_name VARCHAR(100) NOT NULL,
        semester INT(2) NOT NULL,
        max_marks INT(11) DEFAULT 100,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $str4 = "CREATE TABLE IF NOT EXISTS marks (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        student_id INT(11) NOT NULL,
        subject_id INT(11) NOT NULL,
        semester INT(2) NOT NULL,
        internal_marks DECIMAL(5,2) DEFAULT 0.00,
        external_marks DECIMAL(5,2) DEFAULT 0.00,
        total_marks DECIMAL(5,2) DEFAULT 0.00,
        grade VARCHAR(2),
        exam_year YEAR NOT NULL,
        created_by INT(11),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $t1 = mysqli_query($temp_conn, $str1);
    $t2 = mysqli_query($temp_conn, $str2);
    $t3 = mysqli_query($temp_conn, $str3);
    $t4 = mysqli_query($temp_conn, $str4);
    
    if ($t1 && $t2 && $t3 && $t4) {
        $check_admin = mysqli_query($temp_conn, "SELECT id FROM users WHERE username = 'admin'");
        if (mysqli_num_rows($check_admin) == 0) {
            mysqli_query($temp_conn, "INSERT INTO users (username, email, password, role) VALUES ('admin', 'admin@example.com', 'admin123', 'admin')");
        }
        $setup_success = true;
        $setup_message = 'Database and tables created successfully!';
    } else {
        $setup_message = 'Error creating tables: ' . mysqli_error($temp_conn);
    }
    
    mysqli_close($temp_conn);
} else {
    $setup_message = 'Cannot connect to MySQL. Make sure XAMPP MySQL is running!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - Student Performance Index</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="logo">
                <i class="fa-solid fa-cogs"></i>
            </div>
            <h2>Database Setup</h2>

            <?php if ($setup_message): ?>
                <div class="alert alert-<?php echo $setup_success ? 'success' : 'danger'; ?> alert-custom d-flex align-items-center">
                    <i class="fas fa-<?php echo $setup_success ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                    <?php echo $setup_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($setup_success): ?>
                <div class="alert alert-info alert-custom">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Default Login:</strong> Username: <strong>admin</strong> | Password: <strong>admin123</strong>
                </div>
                <a href="login.php" class="btn btn-primary-custom btn-custom" style="width:100%;justify-content:center;padding:12px;">
                    <i class="fas fa-sign-in-alt me-2"></i> Go to Login
                </a>
            <?php else: ?>
                <p class="text-muted mb-2">Please make sure:</p>
                <ol class="text-muted mb-3 text-start">
                    <li>XAMPP MySQL is running (start via XAMPP Control Panel)</li>
                    <li>No other service is using port 3306</li>
                </ol>
                <a href="setup.php" class="btn btn-primary-custom btn-custom" style="width:100%;justify-content:center;padding:12px;">
                    <i class="fas fa-sync me-2"></i> Retry Setup
                </a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

