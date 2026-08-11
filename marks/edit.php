<?php
include __DIR__ . '/../config/config.php';
//include '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Only admin/faculty can edit marks
require_faculty_or_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mar = mysqli_query($conn, "SELECT * FROM marks WHERE id = $id");
$mark = mysqli_fetch_assoc($mar);

if (!$mark) {
    header("Location: index.php");
    exit();
}

$error = '';

$students = mysqli_query($conn, "SELECT id, enrollment_no, first_name, last_name, semester FROM students ORDER BY first_name");
$subjects = mysqli_query($conn, "SELECT id, subject_code, subject_name, semester, max_marks FROM subjects ORDER BY semester, subject_name");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = (int)$_REQUEST['student_id'];
    $subject_id = (int)$_REQUEST['subject_id'];
    $semester = (int)$_REQUEST['semester'];
    $internal_marks = (float)$_REQUEST['internal_marks'];
    $external_marks = (float)$_REQUEST['external_marks'];
    $exam_year = (int)$_REQUEST['exam_year'];

    $sub = mysqli_query($conn, "SELECT max_marks FROM subjects WHERE id = $subject_id");
    $sub_row = mysqli_fetch_assoc($sub);
    $total = $internal_marks + $external_marks;
    
    $percentage = ($total / $sub_row['max_marks']) * 100;
    if ($percentage >= 90) $grade = 'A+';
    elseif ($percentage >= 80) $grade = 'A';
    elseif ($percentage >= 70) $grade = 'B+';
    elseif ($percentage >= 60) $grade = 'B';
    elseif ($percentage >= 50) $grade = 'C';
    elseif ($percentage >= 40) $grade = 'D';
    else $grade = 'F';

    $str = "UPDATE marks SET 
            student_id = $student_id, subject_id = $subject_id, semester = $semester,
            internal_marks = $internal_marks, external_marks = $external_marks, total_marks = $total,
            grade = '$grade', exam_year = $exam_year
            WHERE id = $id";
    if (mysqli_query($conn, $str)) {
        $_SESSION['message'] = 'Marks updated successfully!';
        $_SESSION['msg_type'] = 'success';
        header("Location: index.php");
        exit();
    } else {
        $error = 'Error: ' . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Marks - Student Performance Index</title>
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
                    <i class="fas fa-edit me-2"></i>Edit Marks Record
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-custom"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form method="POST" action="">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Student</label>
                                <select name="student_id" class="form-select" required>
                                    <?php while ($s = mysqli_fetch_assoc($students)): ?>
                                        <option value="<?php echo $s['id']; ?>" <?php if($mark['student_id'] == $s['id']) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name'] . ' (' . $s['enrollment_no'] . ')'); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Subject</label>
                                <select name="subject_id" class="form-select" required>
                                    <?php while ($sub = mysqli_fetch_assoc($subjects)): ?>
                                        <option value="<?php echo $sub['id']; ?>" <?php if($mark['subject_id'] == $sub['id']) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($sub['subject_name'] . ' (' . $sub['subject_code'] . ')'); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Semester</label>
                                <select name="semester" class="form-select" required>
                                    <?php for ($i = 1; $i <= 6; $i++): ?>
                                        <option value="<?php echo $i; ?>" <?php if($mark['semester'] == $i) echo 'selected'; ?>><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Internal Marks</label>
                                <input type="number" step="0.01" name="internal_marks" class="form-control" value="<?php echo $mark['internal_marks']; ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">External Marks</label>
                                <input type="number" step="0.01" name="external_marks" class="form-control" value="<?php echo $mark['external_marks']; ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Exam Year</label>
                                <select name="exam_year" class="form-select" required>
                                    <?php $cy = date('Y'); for ($y = $cy - 4; $y <= $cy; $y++): ?>
                                        <option value="<?php echo $y; ?>" <?php if($mark['exam_year'] == $y) echo 'selected'; ?>><?php echo $y; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <hr class="my-2">
                                <button type="submit" class="btn btn-primary-custom btn-custom">
                                    <i class="fas fa-save me-2"></i>Update Marks
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

