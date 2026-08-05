<?php
include '../config/config.php';
require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$usr = mysqli_query($conn, "SELECT * FROM users WHERE id = $id");
$user = mysqli_fetch_assoc($usr);

if (!$user) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_REQUEST['username']);
    $email = trim($_REQUEST['email']);
    $password = $_REQUEST['password'];
    $role = $_REQUEST['role'];
    $student_id = isset($_REQUEST['student_id']) ? (int)$_REQUEST['student_id'] : 0;

    if (!in_array($role, ['admin', 'faculty', 'student'])) {
        $error = 'Invalid role selected.';
    }

    if (empty($error)) {
        $check = mysqli_query($conn, "SELECT id FROM users WHERE (username = '$username' OR email = '$email') AND id != $id");
        if ($check && mysqli_num_rows($check) > 0) {
            $error = 'Username or email already exists!';
        } else {
            $sid = $role == 'student' ? $student_id : 'NULL';
            $sid_sql = ($sid === 'NULL') ? 'NULL' : $sid;
            if (!empty($password)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $str = "UPDATE users SET username = '$username', email = '$email', password = '$hash', role = '$role', student_id = $sid_sql WHERE id = $id";
            } else {
                $str = "UPDATE users SET username = '$username', email = '$email', role = '$role', student_id = $sid_sql WHERE id = $id";
            }
            if (mysqli_query($conn, $str)) {
                $_SESSION['message'] = 'User updated successfully!';
                $_SESSION['msg_type'] = 'success';
                header("Location: index.php");
                exit();
            } else {
                $error = 'Error: ' . mysqli_error($conn);
            }
        }
    }
}

$students_query = mysqli_query($conn, "SELECT id, enrollment_no, first_name, last_name FROM students ORDER BY first_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Student Performance Index</title>
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
                    <i class="fas fa-user-edit me-2"></i>Edit User: <?php echo htmlspecialchars($user['username']); ?>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-custom"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form method="POST" action="">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" placeholder="Leave empty to keep current password">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Role <span class="text-danger">*</span></label>
                                <select name="role" id="role" class="form-select" required onchange="toggleStudentSelect()">
                                    <option value="admin" <?php if($user['role']=='admin') echo 'selected'; ?>>Admin</option>
                                    <option value="faculty" <?php if($user['role']=='faculty') echo 'selected'; ?>>Faculty (Teacher)</option>
                                    <option value="student" <?php if($user['role']=='student') echo 'selected'; ?>>Student</option>
                                </select>
                            </div>
                            <div class="col-md-8" id="student_select_wrap">
                                <label class="form-label">Link Student</label>
                                <select name="student_id" id="student_select" class="form-select">
                                    <option value="">Select Student</option>
                                    <?php while ($s = mysqli_fetch_assoc($students_query)): ?>
                                        <option value="<?php echo $s['id']; ?>" <?php if($user['student_id']==$s['id']) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name'] . ' (' . $s['enrollment_no'] . ')'); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <hr class="my-2">
                                <button type="submit" class="btn btn-primary-custom btn-custom">
                                    <i class="fas fa-save me-2"></i>Update User
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
        if (role === 'student') {
            wrap.style.display = 'block';
        } else {
            wrap.style.display = 'none';
        }
    }
    toggleStudentSelect();
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php mysqli_close($conn); ?>
