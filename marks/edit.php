<?php
include __DIR__ . '/../config/config.php';
//include '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Only faculty can edit marks
require_faculty();

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

// Build a JS array of all subjects for semester-wise filtering
$all_subjects = array();
while ($subj = mysqli_fetch_assoc($subjects)) {
    $all_subjects[] = $subj;
}
mysqli_data_seek($subjects, 0);
$subjects_json = json_encode($all_subjects);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = (int)$_REQUEST['student_id'];
    $subject_id = (int)$_REQUEST['subject_id'];
    $semester = (int)$_REQUEST['semester'];
    $internal_marks = (float)$_REQUEST['internal_marks'];
    $external_marks = (float)$_REQUEST['external_marks'];
    $exam_year = (int)$_REQUEST['exam_year'];

    $sub = mysqli_query($conn, "SELECT max_marks FROM subjects WHERE id = $subject_id");
    $sub_row = mysqli_fetch_assoc($sub);
    $max = $sub_row['max_marks'];
    $total = $internal_marks + $external_marks;

    // Validate: Internal ≤ subject max, External ≤ subject max, Total ≤ 100
    if ($internal_marks < 0 || $external_marks < 0) {
        $error = 'Marks cannot be negative!';
    } elseif ($internal_marks > $max) {
        $error = 'Internal marks cannot exceed ' . $max . '!';
    } elseif ($external_marks > $max) {
        $error = 'External marks cannot exceed ' . $max . '!';
    } elseif ($total > 100) {
        $error = 'Total marks (Internal + External) cannot exceed 100!';
    }

    if (empty($error)) {
        $percentage = ($total / $max) * 100;
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
                                <label class="form-label">Semester</label>
                                <select name="semester" id="semester_select" class="form-select" required onchange="filterSubjects()">
                                    <?php for ($i = 1; $i <= 6; $i++): ?>
                                        <option value="<?php echo $i; ?>" <?php if($mark['semester'] == $i) echo 'selected'; ?>><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Subject</label>
                                <select name="subject_id" id="subject_select" class="form-select" required onchange="updateMaxMarks()">
                                    <option value="">Select Subject</option>
                                </select>
                                <small class="text-muted">Subjects are filtered by the selected semester. Max Marks: <strong id="max_marks_label">—</strong></small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Internal Marks</label>
                                <input type="number" step="0.01" name="internal_marks" id="internal_marks" class="form-control" value="<?php echo $mark['internal_marks']; ?>" min="0" oninput="validateMarks()">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">External Marks</label>
                                <input type="number" step="0.01" name="external_marks" id="external_marks" class="form-control" value="<?php echo $mark['external_marks']; ?>" min="0" oninput="validateMarks()">
                            </div>
                            <div class="col-12">
                                <div id="marks_alert" class="alert alert-danger alert-custom d-none">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <span id="marks_alert_msg">Internal & External marks each cannot exceed 50!</span>
                                </div>
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
    <script>
    // All subjects data (id, subject_code, subject_name, semester)
    var allSubjects = <?php echo $subjects_json; ?>;
    var currentSubjectId = <?php echo (int)$mark['subject_id']; ?>;
    var currentSemester = <?php echo (int)$mark['semester']; ?>;

    function updateMaxMarks() {
        var subjectId = document.getElementById('subject_select').value;
        var maxLabel = document.getElementById('max_marks_label');
        if (subjectId) {
            var found = allSubjects.find(function(s) { return String(s.id) === String(subjectId); });
            if (found) {
                maxLabel.textContent = found.max_marks;
            }
        } else {
            maxLabel.textContent = '—';
        }
        validateMarks();
    }

    function validateMarks() {
        var subjectId = document.getElementById('subject_select').value;
        var internal = parseFloat(document.getElementById('internal_marks').value) || 0;
        var external = parseFloat(document.getElementById('external_marks').value) || 0;
        var total = internal + external;
        var alertBox = document.getElementById('marks_alert');
        var alertMsg = document.getElementById('marks_alert_msg');

        if (!subjectId) {
            alertBox.classList.add('d-none');
            return;
        }

        var found = allSubjects.find(function(s) { return String(s.id) === String(subjectId); });
        var max = found ? parseFloat(found.max_marks) : 100;

        if (internal > max) {
            alertMsg.textContent = 'Internal marks cannot exceed ' + max + '!';
            alertBox.classList.remove('d-none');
        } else if (external > max) {
            alertMsg.textContent = 'External marks cannot exceed ' + max + '!';
            alertBox.classList.remove('d-none');
        } else if (total > 100) {
            alertMsg.textContent = 'Total marks (Internal + External) cannot exceed 100!';
            alertBox.classList.remove('d-none');
        } else {
            alertBox.classList.add('d-none');
        }
    }

    function filterSubjects() {
        var sem = document.getElementById('semester_select').value;
        var subjectSelect = document.getElementById('subject_select');
        // Clear current options
        subjectSelect.innerHTML = '';

        if (!sem) {
            var opt = document.createElement('option');
            opt.value = '';
            opt.textContent = 'Select Semester First';
            subjectSelect.appendChild(opt);
            return;
        }

        // Filter subjects by selected semester
        var filtered = allSubjects.filter(function(s) {
            return String(s.semester) === String(sem);
        });

        if (filtered.length === 0) {
            var opt = document.createElement('option');
            opt.value = '';
            opt.textContent = 'No subjects for Semester ' + sem;
            subjectSelect.appendChild(opt);
            return;
        }

        var opt = document.createElement('option');
        opt.value = '';
        opt.textContent = 'Select Subject';
        subjectSelect.appendChild(opt);

        filtered.forEach(function(s) {
            var o = document.createElement('option');
            o.value = s.id;
            o.textContent = s.subject_name + ' (' + s.subject_code + ')';
            if (String(s.id) === String(currentSubjectId)) {
                o.selected = true;
            }
            subjectSelect.appendChild(o);
        });
    }

    // Initialize on page load with current semester selected
    filterSubjects();
    updateMaxMarks();
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php mysqli_close($conn); ?>