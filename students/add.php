<?php
include '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Only admin/faculty can add students (students are read-only)
require_faculty_or_admin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $enrollment_no = $_REQUEST['enrollment_no'];
    $first_name = $_REQUEST['first_name'];
    $last_name = $_REQUEST['last_name'];
    $email = $_REQUEST['email'];
    $phone = $_REQUEST['phone'];
    $gender = $_REQUEST['gender'];
    $date_of_birth = $_REQUEST['date_of_birth'];
    $address = $_REQUEST['address'];
    $semester = (int)$_REQUEST['semester'];
    $course = $_REQUEST['course'];
    $user_id = $_SESSION['user_id'];

$username = trim($_REQUEST['student_username']);
    $password = $_REQUEST['student_password'];

    $check = mysqli_query($conn, "SELECT id FROM students WHERE enrollment_no = '$enrollment_no'");
    if ($check && mysqli_num_rows($check) > 0) {
        $error = 'Enrollment number already exists!';
    } elseif (!empty($username) && mysqli_query($conn, "SELECT id FROM users WHERE username = '$username'") && mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users WHERE username = '$username'")) > 0) {
        $error = 'Username already exists! Please choose a different one.';
    } else {
        $str = "INSERT INTO students (enrollment_no, first_name, last_name, email, phone, gender, date_of_birth, address, semester, course, created_by) 
                VALUES ('$enrollment_no', '$first_name', '$last_name', '$email', '$phone', '$gender', '$date_of_birth', '$address', $semester, '$course', $user_id)";
        
        if (mysqli_query($conn, $str)) {
            $new_student_id = mysqli_insert_id($conn);

            // Create login for the student if username provided
            if (!empty($username) && !empty($password)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                mysqli_query($conn, "INSERT INTO users (username, email, password, role, student_id) 
                                     VALUES ('$username', '$email', '$hash', 'student', $new_student_id)");
            }

            $_SESSION['message'] = 'Student added successfully!';
            $_SESSION['msg_type'] = 'success';
            header("Location: index.php");
            exit();
        } else {
            $error = 'Error: ' . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student - Student Performance Index</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../sidebar.php'; ?>
    <div class="main-content">
        <?php include '../header.php'; ?>
        <div class="content-area">
            <div class="custom-card">
                <div class="card-header">
                    <i class="fas fa-user-plus me-2"></i>Add New Student
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-custom"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form method="POST" action="">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Enrollment No <span class="text-danger">*</span></label>
                                <input type="text" name="enrollment_no" class="form-control" placeholder="e.g., BCA2023001" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control" placeholder="Enter first name" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control" placeholder="Enter last name" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" placeholder="student@example.com" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control" placeholder="Enter phone number" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Gender <span class="text-danger">*</span></label>
                                <select name="gender" class="form-select" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" name="date_of_birth" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Semester <span class="text-danger">*</span></label>
                                <select name="semester" class="form-select" required>
                                    <option value="">Select Semester</option>
                                    <?php for ($i = 1; $i <= 6; $i++): ?>
                                        <option value="<?php echo $i; ?>">Semester <?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Course <span class="text-danger">*</span></label>
                                <input type="text" name="course" class="form-control" value="BCA" required>
                            </div>
<div class="col-md-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="3" placeholder="Enter address (optional)"></textarea>
                            </div>
                            <div class="col-12">
                                <div class="alert alert-info alert-custom">
                                    <i class="fas fa-key me-2"></i>
                                    <strong>Student Login Credentials (Optional):</strong> Set a username & password so this student can login and view their performance.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Login Username</label>
                                <input type="text" name="student_username" class="form-control" placeholder="e.g., student123">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Login Password</label>
                                <input type="password" name="student_password" class="form-control" placeholder="Password">
                            </div>
                            <div class="col-12">
                                <hr class="my-2">
                                <button type="submit" class="btn btn-primary-custom btn-custom">
                                    <i class="fas fa-save me-2"></i>Save Student
                                </button>
                                <a href="index.php" class="btn btn-outline-custom btn-custom">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php mysqli_close($conn); ?>

