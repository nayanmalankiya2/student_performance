<?php
include '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject_code = $_REQUEST['subject_code'];
    $subject_name = $_REQUEST['subject_name'];
    $semester = (int)$_REQUEST['semester'];
    $max_marks = (int)$_REQUEST['max_marks'];

    $check = mysqli_query($conn, "SELECT id FROM subjects WHERE subject_code = '$subject_code'");
    if ($check && mysqli_num_rows($check) > 0) {
        $error = 'Subject code already exists!';
    } else {
        $str = "INSERT INTO subjects (subject_code, subject_name, semester, max_marks) VALUES ('$subject_code', '$subject_name', $semester, $max_marks)";
        if (mysqli_query($conn, $str)) {
            $_SESSION['message'] = 'Subject added successfully!';
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
    <title>Add Subject - Student Performance Index</title>
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
                    <i class="fas fa-book-medical me-2"></i>Add New Subject
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-custom"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form method="POST" action="">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Subject Code <span class="text-danger">*</span></label>
                                <input type="text" name="subject_code" class="form-control" placeholder="e.g., BCA501" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Subject Name <span class="text-danger">*</span></label>
                                <input type="text" name="subject_name" class="form-control" placeholder="Enter subject name" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Semester <span class="text-danger">*</span></label>
                                <select name="semester" class="form-select" required>
                                    <option value="">Select</option>
                                    <?php for ($i = 1; $i <= 6; $i++): ?>
                                        <option value="<?php echo $i; ?>">Sem <?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Max Marks <span class="text-danger">*</span></label>
                                <input type="number" name="max_marks" class="form-control" value="100" required>
                            </div>
                            <div class="col-12">
                                <hr class="my-2">
                                <button type="submit" class="btn btn-primary-custom btn-custom">
                                    <i class="fas fa-save me-2"></i>Save Subject
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

