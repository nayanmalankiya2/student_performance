<?php
include __DIR__ . '/../config/config.php';
//include '../config/config.php';
require_admin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_REQUEST['username']);
    $email = trim($_REQUEST['email']);
    $password = $_REQUEST['password'];
    $role = $_REQUEST['role'];
    $student_id = isset($_REQUEST['student_id']) ? (int)$_REQUEST['student_id'] : 0;

    // Validate role
    if (!in_array($role, ['faculty', 'student'])) {
        $error = 'Invalid role selected.';
    }

    if (empty($error)) {
        $check = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username' OR email = '$email'");
        if ($check && mysqli_num_rows($check) > 0) {
            $error = 'Username or email already exists!';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sid = $role == 'student' ? $student_id : 'NULL';
            $str = "INSERT INTO users (username, email, password, role, student_id) 
                    VALUES ('$username', '$email', '$hash', '$role', " . ($sid === 'NULL' ? 'NULL' : $sid) . ")";
            if (mysqli_query($conn, $str)) {
                $_SESSION['message'] = 'User added successfully!';
                $_SESSION['msg_type'] = 'success';
                header("Location: index.php");
                exit();
            } else {
                $error = 'Error: ' . mysqli_error($conn);
            }
        }
    }
}

$students_query = mysqli_query($conn, "SELECT id, enrollment_no, first_name, last_name FROM students WHERE id NOT IN (SELECT COALESCE(student_id,0) FROM users WHERE role='student') ORDER BY first_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User - Student Performance Index</title>
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
                    <i class="fas fa-user-plus me-2"></i>Add New User (Faculty / Student)
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-custom"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form method="POST" action="">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" name="username" class="form-control" placeholder="Login username" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" placeholder="user@example.com" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" placeholder="Password" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Role <span class="text-danger">*</span></label>
                                <select name="role" id="role" class="form-select" required onchange="toggleStudentSelect()">
                                    <option value="faculty">Faculty (Teacher)</option>
                                    <option value="student">Student</option>
                                </select>
                            </div>
                            <div class="col-md-8" id="student_select_wrap">
                                <label class="form-label">Link Student <span class="text-danger">*</span></label>
                                <select name="student_id" id="student_select" class="form-select">
                                    <option value="">Select Student</option>
                                    <?php while ($s = mysqli_fetch_assoc($students_query)): ?>
                                        <option value="<?php echo $s['id']; ?>">
                                            <?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name'] . ' (' . $s['enrollment_no'] . ')'); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <small class="text-muted">Select student record to link this login to.</small>
                            </div>
                            <div class="col-12">
                                <hr class="my-2">
                                <button type="submit" class="btn btn-primary-custom btn-custom">
                                    <i class="fas fa-save me-2"></i>Save User
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
    <script>
    function toggleStudentSelect() {
        var role = document.getElementById('role').value;
        var wrap = document.getElementById('student_select_wrap');
        var sel = document.getElementById('student_select');
        if (role === 'student') {
            wrap.style.display = 'block';
            sel.required = true;
        } else {
            wrap.style.display = 'none';
            sel.required = false;
        }
    }
    toggleStudentSelect();
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php mysqli_close($conn); ?>
