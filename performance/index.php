<?php
include '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$result = mysqli_query($conn, "SELECT s.*, 
                       COALESCE(SUM(m.total_marks), 0) as total_obtained,
                       COALESCE(SUM(sub.max_marks), 0) as total_max,
                       COUNT(DISTINCT m.id) as subject_count,
                       COUNT(DISTINCT m.semester) as semester_count
                       FROM students s
                       LEFT JOIN marks m ON s.id = m.student_id
                       LEFT JOIN subjects sub ON m.subject_id = sub.id
                       GROUP BY s.id
                       ORDER BY total_obtained DESC");

$stats = mysqli_query($conn, "SELECT COUNT(DISTINCT s.id) as total_students,
                      AVG(m.total_marks) as avg_marks,
                      COUNT(m.id) as total_records
                      FROM students s
                      LEFT JOIN marks m ON s.id = m.student_id");
$stats_row = mysqli_fetch_assoc($stats);

$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$msg_type = isset($_SESSION['msg_type']) ? $_SESSION['msg_type'] : '';
unset($_SESSION['message'], $_SESSION['msg_type']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Index - Student Performance Index</title>
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
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $msg_type; ?> alert-custom alert-dismissible fade show" role="alert">
                    <i class="fas fa-<?php echo $msg_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon blue"><i class="fas fa-user-graduate"></i></div>
                        <h3><?php echo $stats_row['total_students']; ?></h3>
                        <p>Students Evaluated</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon green"><i class="fas fa-chart-line"></i></div>
                        <h3><?php echo $stats_row['avg_marks'] ? round($stats_row['avg_marks'], 2) : 0; ?></h3>
                        <p>Average Score</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon orange"><i class="fas fa-file-alt"></i></div>
                        <h3><?php echo $stats_row['total_records']; ?></h3>
                        <p>Total Records</p>
                    </div>
                </div>
            </div>

            <!-- Performance Table -->
            <div class="table-wrapper">
                <div class="table-header">
                    <h5><i class="fas fa-trophy me-2"></i>Student Performance Ranking</h5>
                    <div class="d-flex gap-2">
                        <button onclick="window.print()" class="btn btn-outline-custom btn-custom btn-sm-custom no-print">
                            <i class="fas fa-print me-1"></i> Print
                        </button>
                        <a href="../students/index.php" class="btn btn-primary-custom btn-custom btn-sm-custom">
                            <i class="fas fa-arrow-left me-1"></i> Back to Students
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Student</th>
                                <th>Enrollment</th>
                                <th>Semester</th>
                                <th>Course</th>
                                <th>Subjects</th>
                                <th>Total Marks</th>
                                <th>Performance Index</th>
                                <th>Level</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && mysqli_num_rows($result) > 0): $rank = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                                <?php 
                                    if ($row['total_max'] == 0) {
                                        $cgpa = 0;
                                    } else {
                                        $pct = ($row['total_obtained'] / $row['total_max']) * 100;
                                        $cgpa = round(($pct / 100) * 10, 2);
                                    }
                                    if ($cgpa >= 9.0) $level = 'Outstanding';
                                    elseif ($cgpa >= 8.0) $level = 'Excellent';
                                    elseif ($cgpa >= 7.0) $level = 'Good';
                                    elseif ($cgpa >= 6.0) $level = 'Average';
                                    elseif ($cgpa >= 5.0) $level = 'Below Average';
                                    else $level = 'Poor';
                                    $level_class = $cgpa >= 7 ? 'success' : ($cgpa >= 5 ? 'warning' : 'danger');
                                    
                                    $rank_class = 'default';
                                    if ($rank == 1) $rank_class = 'gold';
                                    elseif ($rank == 2) $rank_class = 'silver';
                                    elseif ($rank == 3) $rank_class = 'bronze';
                                ?>
                                <tr>
                                    <td>
                                        <span class="rank-badge <?php echo $rank_class; ?>">
                                            <?php if ($rank == 1): ?>
                                                <i class="fas fa-crown"></i>
                                            <?php else: ?>
                                                <?php echo $rank; ?>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['enrollment_no']); ?></td>
                                    <td><span class="badge bg-info">Sem <?php echo $row['semester']; ?></span></td>
                                    <td><?php echo htmlspecialchars($row['course']); ?></td>
                                    <td><?php echo $row['subject_count']; ?></td>
                                    <td><strong><?php echo $row['total_obtained']; ?> / <?php echo $row['total_max']; ?></strong></td>
                                    <td>
                                        <span class="badge bg-<?php echo $level_class; ?> fs-6 px-3 py-2">
                                            <?php echo $cgpa; ?>
                                        </span>
                                    </td>
                                    <td><span class="badge bg-<?php echo $level_class; ?>"><?php echo $level; ?></span></td>
                                    <td>
                                        <a href="../students/performance.php?id=<?php echo $row['id']; ?>" class="btn btn-info-custom btn-sm-custom btn-custom" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php $rank++; endwhile; else: ?>
                                <tr>
                                    <td colspan="10" class="text-center py-5">
                                        <i class="fas fa-chart-bar fa-3x text-muted mb-3 d-block"></i>
                                        <p class="text-muted mb-2">No performance data available.</p>
                                        <a href="../marks/add.php" class="btn btn-primary-custom btn-custom">
                                            <i class="fas fa-plus me-1"></i> Add Marks Records
                                        </a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php mysqli_close($conn); ?>

