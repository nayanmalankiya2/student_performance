<?php
include __DIR__ . '/../config/config.php';
//include '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Students & Faculty can view subjects (read-only), only Admin can modify
$is_readonly_view = is_student() || is_faculty();

// Delete (admin only) - students & faculty cannot delete
if (isset($_REQUEST['delete'])) {
    if ($is_readonly_view) {
        header("Location: index.php");
        exit();
    }
    $id = (int)$_REQUEST['delete'];
    mysqli_query($conn, "DELETE FROM subjects WHERE id = $id");
    $_SESSION['message'] = 'Subject deleted successfully!';
    $_SESSION['msg_type'] = 'success';
    header("Location: index.php");
    exit();
}

$result = mysqli_query($conn, "SELECT s.*, (SELECT COUNT(*) FROM marks WHERE subject_id = s.id) as usage_count FROM subjects s ORDER BY s.semester, s.subject_name");
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$msg_type = isset($_SESSION['msg_type']) ? $_SESSION['msg_type'] : '';
unset($_SESSION['message'], $_SESSION['msg_type']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subjects - Student Performance Index</title>
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
                    <h5><i class="fas fa-book me-2"></i>All Subjects</h5>
                    <?php if (!$is_readonly_view): ?>
                    <a href="add.php" class="btn btn-primary-custom btn-custom"><i class="fas fa-plus me-1"></i> Add Subject</a>
                    <?php endif; ?>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Subject Code</th>
                                <th>Subject Name</th>
                                <th>Semester</th>
                                <th>Max Marks</th>
                                <th>Used In Records</th>
                                <?php if (!$is_readonly_view): ?><th class="text-end">Actions</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && mysqli_num_rows($result) > 0): $i = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td class="fw-medium"><?php echo $i++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($row['subject_code']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                                    <td><span class="badge bg-info">Sem <?php echo $row['semester']; ?></span></td>
                                    <td><?php echo $row['max_marks']; ?></td>
<td><span class="badge bg-secondary"><?php echo $row['usage_count']; ?></span></td>
                                    <?php if (!$is_readonly_view): ?>
                                    <td class="text-end">
                                        <div class="d-flex gap-1 justify-content-end">
                                            <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning-custom btn-sm-custom btn-custom" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-danger-custom btn-sm-custom btn-custom" title="Delete" onclick="return confirm('Are you sure you want to delete this subject?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endwhile; else: ?>
                                <tr>
                                    <td colspan="<?php echo $is_readonly_view ? 6 : 7; ?>" class="text-center py-5">
                                        <i class="fas fa-book fa-3x text-muted mb-3 d-block"></i>
                                        <p class="text-muted mb-2">No subjects found.</p>
                                        <?php if (!$is_readonly_view): ?>
                                        <a href="add.php" class="btn btn-primary-custom btn-custom">
                                            <i class="fas fa-plus me-1"></i> Add Your First Subject
                                        </a>
                                        <?php endif; ?>
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

