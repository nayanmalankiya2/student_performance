<?php
include __DIR__ . '/../config/config.php';
//include '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Students can only view their own performance
if (is_student()) {
    $myid = get_student_id();
    if ($myid <= 0 || $id != $myid) {
        header("Location: index.php");
        exit();
    }
}

$stu = mysqli_query($conn, "SELECT * FROM students WHERE id = $id");
$student = mysqli_fetch_assoc($stu);

if (!$student) {
    header("Location: index.php");
    exit();
}

$marks_result = mysqli_query($conn, "SELECT m.*, s.subject_name, s.subject_code, s.max_marks 
                             FROM marks m 
                             JOIN subjects s ON m.subject_id = s.id 
                             WHERE m.student_id = $id 
                             ORDER BY m.semester, s.subject_name");

$total_obtained = 0;
$total_max = 0;
$semester_wise = array();

while ($mark = mysqli_fetch_assoc($marks_result)) {
    // Effective total = internal + external (capped at 100)
    $effective_total = min($mark['internal_marks'] + $mark['external_marks'], 100);
    $mark['effective_total'] = $effective_total;
    $total_obtained += $effective_total;
    // Each subject has internal (max_marks) + external (max_marks) = 2 * max_marks total possible
    $total_max += $mark['max_marks'] * 2;
    $semester_wise[$mark['semester']][] = $mark;
}

$cgpa = 0;
$level = 'N/A';
$level_class = 'secondary';

if ($total_max > 0) {
    $pct = ($total_obtained / $total_max) * 100;
    $cgpa = min(10, round(($pct / 100) * 10, 2));
    
    if ($cgpa >= 9.0) { $level = 'Outstanding'; $level_class = 'success'; }
    elseif ($cgpa >= 8.0) { $level = 'Excellent'; $level_class = 'success'; }
    elseif ($cgpa >= 7.0) { $level = 'Good'; $level_class = 'success'; }
    elseif ($cgpa >= 6.0) { $level = 'Average'; $level_class = 'warning'; }
    elseif ($cgpa >= 5.0) { $level = 'Below Average'; $level_class = 'warning'; }
    else { $level = 'Poor'; $level_class = 'danger'; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Performance - <?php echo $student['first_name'] . ' ' . $student['last_name']; ?></title>
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
            <div class="row g-4">
                <!-- Student Profile Card -->
                <div class="col-lg-4">
                    <div class="custom-card">
                        <div class="card-body text-center">
                            <div class="display-4 mb-3 text-primary">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <h4 class="fw-bold"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h4>
                            <p class="text-muted mb-1">
                                <i class="fas fa-id-card me-1"></i>
                                Enrollment: <?php echo htmlspecialchars($student['enrollment_no']); ?>
                            </p>
                            <p class="mb-2">
                                <span class="badge bg-info fs-6">Semester <?php echo $student['semester']; ?></span>
                                <span class="badge bg-primary fs-6"><?php echo htmlspecialchars($student['course']); ?></span>
                            </p>
                            <hr>
                            <div class="pi-display">
                                <div class="pi-circle <?php echo $cgpa >= 7 ? 'high' : ($cgpa >= 5 ? 'medium' : 'low'); ?>">
                                    <?php echo $cgpa; ?>
                                </div>
                                <div class="pi-label">Performance Index (CGPA)</div>
                                <span class="badge bg-<?php echo $level_class; ?> fs-6 mt-2 px-3 py-2"><?php echo $level; ?></span>
                            </div>
                            <hr>
                            <div class="row text-center g-2">
                                <div class="col-6">
                                    <h5 class="text-primary fw-bold mb-0"><?php echo $total_obtained; ?></h5>
                                    <small class="text-muted">Marks Obtained</small>
                                </div>
                                <div class="col-6">
                                    <h5 class="text-success fw-bold mb-0"><?php echo $cgpa; ?></h5>
                                    <small class="text-muted">CGPA</small>
                                </div>
                            </div>
                            <?php if (is_student()): ?>
                            <hr>
                            <a href="edit_my_profile.php" class="btn btn-primary-custom btn-custom w-100">
                                <i class="fas fa-user-edit me-2"></i> Edit My Profile
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Academic Records -->
                <div class="col-lg-8">
                    <div class="custom-card">
                        <div class="card-header">
                            <i class="fas fa-chart-bar me-2"></i>Academic Records
                        </div>
                        <div class="card-body">
                            <?php if (count($semester_wise) > 0): ?>
                                <?php foreach ($semester_wise as $sem => $marks): ?>
                                    <?php 
                                    // Calculate semester CGPA using effective totals
                                    $sem_total = 0; 
                                    $sem_max = 0; 
                                    foreach ($marks as $m) {
                                        $sem_total += $m['effective_total'];
                                        $sem_max += $m['max_marks'] * 2;
                                    }
                                    $sem_cgpa = ($sem_max > 0) ? min(10, round(($sem_total / $sem_max) * 10, 2)) : 0;
                                    ?>
                                    <div class="d-flex align-items-center gap-2 mb-3 mt-2">
                                        <span class="badge bg-primary fs-6 px-3 py-2">
                                            <i class="fas fa-layer-group me-1"></i> Semester <?php echo $sem; ?>
                                        </span>
                                        <span class="badge bg-success fs-6 px-3 py-2">
                                            <i class="fas fa-chart-pie me-1"></i> CGPA: <?php echo $sem_cgpa; ?>/10
                                        </span>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Subject</th>
                                                    <th>Code</th>
                                                    <th>Internal</th>
                                                    <th>External</th>
                                                    <th>Total</th>
                                                    <th>Grade</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $sem_total = 0; $sem_max = 0; ?>
                                                <?php foreach ($marks as $m): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($m['subject_name']); ?></td>
                                                    <td><strong><?php echo htmlspecialchars($m['subject_code']); ?></strong></td>
                                                    <td><?php echo $m['internal_marks']; ?></td>
                                                    <td><?php echo $m['external_marks']; ?></td>
                                                    <td><strong><?php echo $m['effective_total']; ?></strong></td>
                                                    <td>
                                                        <?php 
                                                        $grade = $m['grade'];
                                                        $grade_bg = 'success';
                                                        if ($grade == 'F') $grade_bg = 'danger';
                                                        elseif ($grade == 'D') $grade_bg = 'warning';
                                                        ?>
                                                        <span class="badge bg-<?php echo $grade_bg; ?>"><?php echo $grade; ?></span>
                                                    </td>
                                                </tr>
                                                <?php $sem_total += $m['effective_total']; $sem_max += $m['max_marks'] * 2; ?>
                                                <?php endforeach; ?>
                                                <tr class="table-primary fw-bold">
                                                    <td colspan="4" class="text-end">Semester Total:</td>
                                                    <td><?php echo $sem_total; ?> / <?php echo $sem_max; ?></td>
                                                    <td>
                                                        <?php 
                                                        $sem_pct = ($sem_total / $sem_max) * 100; 
                                                        echo min(10, round(($sem_pct / 100) * 10, 2)); 
                                                        ?> PI
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endforeach; ?>
                                <hr>
                                <div class="row text-center g-3">
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light rounded-3">
                                            <h3 class="text-primary fw-bold mb-1"><?php echo $total_obtained; ?></h3>
                                            <small class="text-muted">Total Marks Obtained</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light rounded-3">
                                            <h3 class="text-success fw-bold mb-1"><?php echo $cgpa; ?></h3>
                                            <small class="text-muted">Performance Index (CGPA)</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light rounded-3">
                                            <h3 class="text-info fw-bold mb-1"><?php echo count($semester_wise); ?></h3>
                                            <small class="text-muted">Semesters Completed</small>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No academic records found.</h5>
                                    <p class="text-muted mb-3"><?php echo is_student() ? 'Marks will appear here once added by your faculty.' : 'Add marks to see performance analysis.'; ?></p>
                                    <?php if (is_faculty()): ?>
                                    <a href="../marks/add.php?student_id=<?php echo $id; ?>" class="btn btn-primary-custom btn-custom">
                                        <i class="fas fa-plus me-1"></i> Add Marks
                                    </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php mysqli_close($conn); ?>

