<?php
include '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Only admin/faculty can edit students (students are read-only)
require_faculty_or_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stu = mysqli_query($conn, "SELECT * FROM students WHERE id = $id");
$student = mysqli_fetch_assoc($stu);

if (!$student) {
    header("Location: index.php");
    exit();
}

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

$str = "UPDATE students SET 
            enrollment_no = '$enrollment_no',
            first_name = '$first_name',
            last_name = '$last_name',
            email = '$email',
            phone = '$phone',
            gender = '$gender',
            date_of_birth = '$date_of_birth',
            address = '$address',
            semester = $semester,
            course = '$course'
            WHERE id = $id";
    
    if (mysqli_query($conn, $str)) {
        // Update student login credentials
        $login_username = trim($_REQUEST['login_username']);
        $login_password = $_REQUEST['login_password'];

        $existing = mysqli_query($conn, "SELECT id, username FROM users WHERE student_id = $id AND role = 'student'");
        $has_login = ($existing && mysqli_num_rows($existing) > 0);

        if ($has_login) {
            $ex = mysqli_fetch_assoc($existing);
            $uid = $ex['id'];
            if (!empty($login_username)) {
                $check_u = mysqli_query($conn, "SELECT id FROM users WHERE username = '$login_username' AND id != $uid");
                if ($check_u && mysqli_num_rows($check_u) > 0) {
                    $error = 'Username already exists!';
                } else {
                    if (!empty($login_password)) {
                        $hash = password_hash($login_password, PASSWORD_DEFAULT);
                        mysqli_query($conn, "UPDATE users SET username = '$login_username', password = '$hash' WHERE id = $uid");
                    } else {
                        mysqli_query($conn, "UPDATE users SET username = '$login_username' WHERE id = $uid");
                    }
                }
            } elseif (!empty($login_password)) {
                $hash = password_hash($login_password, PASSWORD_DEFAULT);
                mysqli_query($conn, "UPDATE users SET password = '$hash' WHERE id = $uid");
            }
        } elseif (!empty($login_username) && !empty($login_password)) {
            $hash = password_hash($login_password, PASSWORD_DEFAULT);
            mysqli_query($conn, "INSERT INTO users (username, email, password, role, student_id) 
                                 VALUES ('$login_username', '$email', '$hash', 'student', $id)");
        }

        if (empty($error)) {
            $_SESSION['message'] = 'Student updated successfully!';
            $_SESSION['msg_type'] = 'success';
            header("Location: index.php");
            exit();
        }
    } else {
        $error = 'Error: ' . mysqli_error($conn);
    }
}

// Get existing login info
$existing_login = mysqli_query($conn, "SELECT id, username FROM users WHERE student_id = $id AND role = 'student'");
$saved_login = ($existing_login && mysqli_num_rows($existing_login) > 0) ? mysqli_fetch_assoc($existing_login) : null;
$saved_username = $saved_login ? $saved_login['username'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student - Student Performance Index</title>
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
                    <i class="fas fa-user-edit me-2"></i>Edit Student
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-custom"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form method="POST" action="">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Enrollment No <span class="text-danger">*</span></label>
                                <input type="text" name="enrollment_no" class="form-control" value="<?php echo htmlspecialchars($student['enrollment_no']); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($student['first_name']); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($student['last_name']); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($student['phone']); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Gender <span class="text-danger">*</span></label>
                                <select name="gender" class="form-select" required>
                                    <option value="Male" <?php if($student['gender']=='Male') echo 'selected'; ?>>Male</option>
                                    <option value="Female" <?php if($student['gender']=='Female') echo 'selected'; ?>>Female</option>
                                    <option value="Other" <?php if($student['gender']=='Other') echo 'selected'; ?>>Other</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" name="date_of_birth" class="form-control" value="<?php echo $student['date_of_birth']; ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Semester <span class="text-danger">*</span></label>
                                <select name="semester" class="form-select" required>
                                    <?php for ($i = 1; $i <= 6; $i++): ?>
                                        <option value="<?php echo $i; ?>" <?php if($student['semester']==$i) echo 'selected'; ?>>Semester <?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Course <span class="text-danger">*</span></label>
                                <input type="text" name="course" class="form-control" value="<?php echo htmlspecialchars($student['course']); ?>" required>
                            </div>
<div class="col-md-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($student['address']); ?></textarea>
                            </div>
                            <div class="col-12">
                                <div class="alert alert-info alert-custom">
                                    <i class="fas fa-key me-2"></i>
                                    <strong>Student Login Credentials:</strong> Update the username/password so this student can login and view their performance.
                                    <?php if ($saved_username): ?>
                                        <br><span class="text-muted">Current login username: <strong><?php echo htmlspecialchars($saved_username); ?></strong></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Login Username</label>
                                <input type="text" name="login_username" class="form-control" value="<?php echo htmlspecialchars($saved_username); ?>" placeholder="Username">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Login Password</label>
                                <input type="password" name="login_password" class="form-control" placeholder="Leave empty to keep current password">
                            </div>
                            <div class="col-12">
                                <hr class="my-2">
                                <button type="submit" class="btn btn-primary-custom btn-custom">
                                    <i class="fas fa-save me-2"></i>Update Student
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

