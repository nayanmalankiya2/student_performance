<?php
include '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Students can only view (read-only). Redirect to their own profile.
if (is_student()) {
    $myid = get_student_id();
    if ($myid > 0) {
        header("Location: performance.php?id=$myid");
    }
    exit();
}

// Delete (admin/faculty only)
if (isset($_REQUEST['delete'])) {
    $id = (int)$_REQUEST['delete'];
    $str = "DELETE FROM students WHERE id = $id";
    mysqli_query($conn, $str);
    $_SESSION['message'] = 'Student deleted successfully!';
    $_SESSION['msg_type'] = 'success';
    header("Location: index.php");
    exit();
}

$str = "SELECT s.*, COUNT(m.id) as marks_count 
        FROM students s 
        LEFT JOIN marks m ON s.id = m.student_id 
        GROUP BY s.id 
        ORDER BY s.created_at DESC";
$result = mysqli_query($conn, $str);

$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$msg_type = isset($_SESSION['msg_type']) ? $_SESSION['msg_type'] : '';
unset($_SESSION['message'], $_SESSION['msg_type']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - Student Performance Index</title>
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
            <div class="table-wrapper">
                <div class="table-header">
                    <h5><i class="fas fa-user-graduate me-2"></i>All Students</h5>
                    <a href="add.php" class="btn btn-primary-custom btn-custom"><i class="fas fa-plus me-1"></i> Add New Student</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Enrollment No</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Semester</th>
                                <th>Course</th>
                                <th>Records</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                                <?php $i = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td class="fw-medium"><?php echo $i++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($row['enrollment_no']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                    <td><span class="badge bg-info">Sem <?php echo $row['semester']; ?></span></td>
                                    <td><?php echo htmlspecialchars($row['course']); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo $row['marks_count']; ?></span></td>
                                    <td class="text-end">
                                        <div class="d-flex gap-1 justify-content-end">
                                            <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning-custom btn-sm-custom btn-custom" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="performance.php?id=<?php echo $row['id']; ?>" class="btn btn-info-custom btn-sm-custom btn-custom" title="Performance">
                                                <i class="fas fa-chart-line"></i>
                                            </a>
                                            <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-danger-custom btn-sm-custom btn-custom" title="Delete" onclick="return confirm('Are you sure you want to delete this student?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <i class="fas fa-user-graduate fa-3x text-muted mb-3 d-block"></i>
                                        <p class="text-muted mb-2">No students found.</p>
                                        <a href="add.php" class="btn btn-primary-custom btn-custom">
                                            <i class="fas fa-plus me-1"></i> Add Your First Student
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

