<?php
include __DIR__ . '/../config/config.php';
//include '../config/config.php';

// Initialize form variables (must be before use in value attributes)
$enrollment_no = '';
$first_name = '';
$last_name = '';
$email = '';
$phone = '';
$gender = '';
$date_of_birth = '';
$course = '';
$semester = '';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Only admin can add students (faculty can only edit existing students)
require_admin();

// AJAX: Check if enrollment number already exists (called from same file)
if (isset($_POST['action']) && $_POST['action'] === 'check_enrollment') {
    header('Content-Type: application/json');
    $response = ['exists' => false];
    if (isset($_POST['enrollment_no'])) {
        $enrollment_no = trim($_POST['enrollment_no']);
        $check = mysqli_query($conn, "SELECT id FROM students WHERE enrollment_no = '$enrollment_no'");
        if ($check && mysqli_num_rows($check) > 0) {
            $response['exists'] = true;
        }
    }
    echo json_encode($response);
    mysqli_close($conn);
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $enrollment_no = trim($_REQUEST['enrollment_no']);
    $first_name = $_REQUEST['first_name'];

    // Validate enrollment number: exactly 5 digits, numbers only
    if (!preg_match('/^[0-9]{5}$/', $enrollment_no)) {
        $error = 'Enrollment number must be exactly 5 digits (numbers only)!';
    }
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

    // Validate username & password are compulsory
    if (empty($username)) {
        $error = 'Login Username is required!';
    } elseif (empty($password)) {
        $error = 'Login Password is required!';
    }

    // Validate email format
    if (empty($error) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format!';
    }

    // Validate first name: letters and spaces only
    if (empty($error) && !preg_match('/^[a-zA-Z\s]+$/', $first_name)) {
        $error = 'First name must contain letters only!';
    }
    // Validate last name: letters and spaces only
    if (empty($error) && !preg_match('/^[a-zA-Z\s]+$/', $last_name)) {
        $error = 'Last name must contain letters only!';
    }
    if (empty($error) && !preg_match('/^[0-9]{10}$/', $phone)) {
        $error = 'Mobile number must be exactly 10 digits (numbers only)!';
    }

    // Validate date of birth: student must be at least 15 years old
    if (empty($error)) {
        if (!empty($date_of_birth)) {
            $dob = new DateTime($date_of_birth);
            $today = new DateTime();
            $age = $today->diff($dob)->y;
            if ($age < 15) {
                $error = 'Student must be at least 15 years old!';
            }
        } else {
            $error = 'Date of birth is required!';
        }
    }

    if (empty($error)) {
        $check = mysqli_query($conn, "SELECT id FROM students WHERE enrollment_no = '$enrollment_no'");
        if ($check && mysqli_num_rows($check) > 0) {
            $error = 'Enrollment number already exists!';
        } elseif (!empty($username) && mysqli_query($conn, "SELECT id FROM users WHERE username = '$username'") && mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users WHERE username = '$username'")) > 0) {
            $error = 'Username already exists! Please choose a different one.';
        } elseif (!empty($email) && mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'") && mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'")) > 0) {
            $error = 'Email already exists! Please use a different email address.';
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
                                <input type="text" name="enrollment_no" class="form-control" value="<?php echo $enrollment_no; ?>" pattern="[0-9]{5}" minlength="5" maxlength="5" required placeholder="e.g., 12345" onblur="checkEnrollmentExists(this.value)">
                                <span id="enrollment_exists_error" class="text-danger small d-none"></span>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control" value="<?php echo $first_name; ?>" placeholder="Enter first name" required onblur="validateName(this, 'first_name_error')">
                                <span id="first_name_error" class="text-danger small d-none"></span>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control" value="<?php echo $last_name; ?>" placeholder="Enter last name" required onblur="validateName(this, 'last_name_error')">
                                <span id="last_name_error" class="text-danger small d-none"></span>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" value="<?php echo $email; ?>" placeholder="student@example.com" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control" value="<?php echo $phone; ?>" pattern="[0-9]{10}" minlength="10" maxlength="10" required placeholder="e.g., 9876543210">
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
                                <input type="date" name="date_of_birth" class="form-control" value="<?php echo $date_of_birth; ?>" required min="1995-01-01">
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
                                <select name="course" class="form-control" required>
                                    <option value="">Select Course</option>
                                    <option value="BCA">BCA</option>
                                    <option value="B.ED">B.ED</option>
                                    <option value="BBA">BBA</option>
                                    <option value="B.COM">B.COM</option>
                                    <option value="BA">BA</option>
                                </select>
                            </div>
<div class="col-md-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="3" placeholder="Enter address (optional)"></textarea>
                            </div>
                            <div class="col-12">
                                <div class="alert alert-info alert-custom">
                                    <i class="fas fa-key me-2"></i>
                                    <strong>Student Login Credentials (Required):</strong> Username & password are required so this student can login and view their performance.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Login Username <span class="text-danger">*</span></label>
                                <input type="text" name="student_username" class="form-control" placeholder="e.g., student123" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Login Password <span class="text-danger">*</span></label>
                                <input type="password" name="student_password" class="form-control" placeholder="Password" required>
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
    <script>
    // Check if enrollment number already exists (AJAX)
    function checkEnrollmentExists(enrollment_no) {
        var errorSpan = document.getElementById('enrollment_exists_error');

        if (enrollment_no.length !== 5 || !/^[0-9]{5}$/.test(enrollment_no)) {
            errorSpan.classList.add('d-none');
            errorSpan.textContent = '';
            return;
        }

        var formData = new FormData();
        formData.append('action', 'check_enrollment');
        formData.append('enrollment_no', enrollment_no);

        fetch('add.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                errorSpan.textContent = 'Enrollment number already exists!';
                errorSpan.classList.remove('d-none');
            } else {
                errorSpan.classList.add('d-none');
                errorSpan.textContent = '';
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    // Prevent form submission if enrollment number already exists
    document.querySelector('form').addEventListener('submit', function(e) {
        var errorSpan = document.getElementById('enrollment_exists_error');
        if (!errorSpan.classList.contains('d-none') && errorSpan.textContent !== '') {
            e.preventDefault();
            alert('Please fix the enrollment number error before submitting.');
        }
    })

    // Validate name fields on blur
    function validateName(field, errorId) {
        var value = field.value.trim();
        var errorSpan = document.getElementById(errorId);
        if (!/^[a-zA-Z\s]+$/.test(value)) {
            errorSpan.classList.remove('d-none');
            errorSpan.textContent = 'Name must contain letters only!';
            return false;
        } else {
            errorSpan.classList.add('d-none');
            errorSpan.textContent = '';
            return true;
        }
    };
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php mysqli_close($conn); ?>

