<?php
include __DIR__ . '/../config/config.php';
//include '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Only students can access this page
if (!is_student()) {
    header("Location: index.php");
    exit();
}

// Students can only edit their OWN profile
$my_student_id = get_student_id();
if ($my_student_id <= 0) {
    header("Location: index.php");
    exit();
}

$stu = mysqli_query($conn, "SELECT * FROM students WHERE id = $my_student_id");
$student = mysqli_fetch_assoc($stu);

if (!$student) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $enrollment_no = trim($_REQUEST['enrollment_no']);
    $first_name = trim($_REQUEST['first_name']);
    $last_name = trim($_REQUEST['last_name']);
    $email = trim($_REQUEST['email']);
    $phone = trim($_REQUEST['phone']);
    $gender = $_REQUEST['gender'];
    $date_of_birth = $_REQUEST['date_of_birth'];
    $address = trim($_REQUEST['address']);

    // Validate required fields
    if (empty($enrollment_no) || empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($gender) || empty($date_of_birth)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Check if enrollment number is already used by another student
        $check = mysqli_query($conn, "SELECT id FROM students WHERE enrollment_no = '$enrollment_no' AND id != $my_student_id");
        if ($check && mysqli_num_rows($check) > 0) {
            $error = 'Enrollment number already exists!';
        } else {
            // Update only editable fields (NOT semester, course, username, password)
            $str = "UPDATE students SET 
                    enrollment_no = '$enrollment_no',
                    first_name = '$first_name',
                    last_name = '$last_name',
                    email = '$email',
                    phone = '$phone',
                    gender = '$gender',
                    date_of_birth = '$date_of_birth',
                    address = '$address'
                    WHERE id = $my_student_id";

            if (mysqli_query($conn, $str)) {
                $success = 'Your profile has been updated successfully!';
                // Refresh student data
                $stu = mysqli_query($conn, "SELECT * FROM students WHERE id = $my_student_id");
                $student = mysqli_fetch_assoc($stu);
            } else {
                $error = 'Error: ' . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit My Profile - Student Performance Index</title>
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
                    <i class="fas fa-user-edit me-2"></i>Edit My Profile
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-custom"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-custom">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-info alert-custom">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> You can update your personal details below. 
                        <strong>Semester, Course, Username & Password</strong> can only be changed by Admin/Faculty.
                    </div>

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
                                <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
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
                                <label class="form-label">Semester <span class="text-muted">(Read-only)</span></label>
                                <input type="text" class="form-control" value="Semester <?php echo $student['semester']; ?>" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Course <span class="text-muted">(Read-only)</span></label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['course']); ?>" disabled>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($student['address']); ?></textarea>
                            </div>
                            <div class="col-12">
                                <hr class="my-2">
                                <button type="submit" class="btn btn-primary-custom btn-custom">
                                    <i class="fas fa-save me-2"></i>Update My Profile
                                </button>
                                <a href="performance.php?id=<?php echo $my_student_id; ?>" class="btn btn-outline-custom btn-custom">
                                    <i class="fas fa-arrow-left me-2"></i>Back to My Performance
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