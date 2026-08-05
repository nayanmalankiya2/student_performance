<?php
include 'config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Students see their own performance dashboard
if (is_student()) {
    $sid = get_student_id();
    if ($sid > 0) {
        // Verify the student exists
        $chk = mysqli_query($conn, "SELECT id FROM students WHERE id = $sid");
        if ($chk && mysqli_num_rows($chk) > 0) {
            header("Location: students/performance.php?id=$sid");
            exit();
        }
    }
    // Student has no valid linked student record - show a friendly page
    $student_orphan = true;
}

$total_students = 0;
$total_subjects = 0;
$total_marks = 0;
$avg_cgpa = 0;

$stu = mysqli_query($conn, "SELECT COUNT(*) as count FROM students");
if ($stu) {
    $row = mysqli_fetch_assoc($stu);
    $total_students = $row['count'];
}

$sub = mysqli_query($conn, "SELECT COUNT(*) as count FROM subjects");
if ($sub) {
    $row = mysqli_fetch_assoc($sub);
    $total_subjects = $row['count'];
}

$mar = mysqli_query($conn, "SELECT COUNT(*) as count FROM marks");
if ($mar) {
    $row = mysqli_fetch_assoc($mar);
    $total_marks = $row['count'];
}

$avg = mysqli_query($conn, "SELECT AVG(total_marks) as avg_marks FROM marks");
if ($avg) {
    $row = mysqli_fetch_assoc($avg);
    if ($row['avg_marks'] > 0) {
        $percent = $row['avg_marks'];
        $avg_cgpa = round(($percent / 100) * 10, 2);
    }
}

$top_student = mysqli_query($conn, "SELECT s.first_name, s.last_name, s.enrollment_no, SUM(m.total_marks) as total
                            FROM marks m JOIN students s ON m.student_id = s.id
                            GROUP BY m.student_id ORDER BY total DESC LIMIT 1");
$top = null;
if ($top_student) {
    $top = mysqli_fetch_assoc($top_student);
}

// Recent students
$recent = mysqli_query($conn, "SELECT * FROM students ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Student Performance Index</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <?php include 'header.php'; ?>
        <div class="content-area">
<!-- Welcome Text -->
            <div class="welcome-text">
                Welcome back, <strong><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin'; ?></strong>! Here's your overview.
            </div>

            <?php if (isset($student_orphan) && $student_orphan): ?>
                <div class="alert alert-warning alert-custom d-flex align-items-center mb-4">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <div>
                        <strong>No student profile linked to your account.</strong><br>
                        Please contact your admin/faculty to link your student profile. Once linked, your performance will appear here.
                    </div>
                </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card">
                        <div class="stat-icon blue"><i class="fas fa-user-graduate"></i></div>
                        <h3><?php echo $total_students; ?></h3>
                        <p>Total Students</p>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card">
                        <div class="stat-icon green"><i class="fas fa-book"></i></div>
                        <h3><?php echo $total_subjects; ?></h3>
                        <p>Total Subjects</p>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card">
                        <div class="stat-icon orange"><i class="fas fa-file-alt"></i></div>
                        <h3><?php echo $total_marks; ?></h3>
                        <p>Total Records</p>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card">
                        <div class="stat-icon red"><i class="fas fa-chart-line"></i></div>
                        <h3><?php echo $avg_cgpa; ?></h3>
                        <p>Avg Performance Index</p>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Top Performer -->
                <div class="col-xl-4">
                    <div class="custom-card">
                        <div class="card-header">
                            <i class="fas fa-trophy text-warning me-2"></i> Top Performer
                        </div>
                        <div class="card-body">
                            <?php if ($top): ?>
                                <div class="text-center py-3">
                                    <div class="display-1 text-warning mb-3">
                                        <i class="fas fa-crown"></i>
                                    </div>
                                    <h5 class="fw-bold"><?php echo htmlspecialchars($top['first_name'] . ' ' . $top['last_name']); ?></h5>
                                    <p class="text-muted mb-2">
                                        <i class="fas fa-id-card me-1"></i>
                                        Enrollment: <?php echo htmlspecialchars($top['enrollment_no']); ?>
                                    </p>
                                    <span class="badge bg-success fs-6 px-3 py-2">
                                        <i class="fas fa-star me-1"></i> Total: <?php echo $top['total']; ?> marks
                                    </span>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-trophy fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No data available yet.</p>
                                    <a href="students/add.php" class="btn btn-primary-custom btn-custom">
                                        <i class="fas fa-user-plus me-1"></i> Add First Student
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Students -->
                <div class="col-xl-4">
                    <div class="custom-card">
                        <div class="card-header">
                            <i class="fas fa-user-graduate text-primary me-2"></i> Recent Students
                        </div>
                        <div class="card-body p-0">
                            <?php if ($recent && mysqli_num_rows($recent) > 0): ?>
                                <div class="list-group list-group-flush">
                                    <?php while ($r = mysqli_fetch_assoc($recent)): ?>
                                        <div class="list-group-item d-flex align-items-center gap-3 px-4 py-3">
                                            <div class="bg-primary bg-opacity-10 rounded-circle p-2">
                                                <i class="fas fa-user text-primary"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0 fw-semibold"><?php echo htmlspecialchars($r['first_name'] . ' ' . $r['last_name']); ?></h6>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($r['enrollment_no']); ?> • Sem <?php echo $r['semester']; ?>
                                                </small>
                                            </div>
                                            <a href="students/performance.php?id=<?php echo $r['id']; ?>" class="btn btn-info-custom btn-sm-custom btn-custom">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                                <div class="p-3 border-top text-center">
                                    <a href="students/index.php" class="btn btn-outline-custom btn-custom btn-sm-custom">
                                        View All Students <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No students added yet.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="col-xl-4">
                    <div class="custom-card">
                        <div class="card-header">
                            <i class="fas fa-bolt text-warning me-2"></i> Quick Actions
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="setup.php" class="btn btn-primary-custom btn-custom">
                                    <i class="fas fa-database me-2"></i> Setup Database
                                </a>
                                <a href="students/add.php" class="btn btn-primary-custom btn-custom">
                                    <i class="fas fa-user-plus me-2"></i> Add New Student
                                </a>
                                <a href="subjects/add.php" class="btn btn-success-custom btn-custom">
                                    <i class="fas fa-book me-2"></i> Add New Subject
                                </a>
                                <a href="marks/add.php" class="btn btn-info-custom btn-custom">
                                    <i class="fas fa-plus-circle me-2"></i> Add Marks Record
                                </a>
                                <a href="performance/index.php" class="btn btn-warning-custom btn-custom">
                                    <i class="fas fa-chart-bar me-2"></i> View Performance Reports
                                </a>
                            </div>
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

