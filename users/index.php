<?php
include '../config/config.php';
require_admin();

if (isset($_REQUEST['delete'])) {
    $id = (int)$_REQUEST['delete'];
    if ($id != $_SESSION['user_id']) {
        mysqli_query($conn, "DELETE FROM users WHERE id = $id");
        $_SESSION['message'] = 'User deleted successfully!';
        $_SESSION['msg_type'] = 'success';
    } else {
        $_SESSION['message'] = 'You cannot delete your own account!';
        $_SESSION['msg_type'] = 'danger';
    }
    header("Location: index.php");
    exit();
}

$result = mysqli_query($conn, "SELECT u.*, s.first_name, s.last_name, s.enrollment_no 
                               FROM users u 
                               LEFT JOIN students s ON u.student_id = s.id 
                               ORDER BY u.role, u.username");

$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$msg_type = isset($_SESSION['msg_type']) ? $_SESSION['msg_type'] : '';
unset($_SESSION['message'], $_SESSION['msg_type']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Student Performance Index</title>
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
                    <h5><i class="fas fa-users-cog me-2"></i>All Users (Faculty & Students)</h5>
                    <a href="add.php" class="btn btn-primary-custom btn-custom"><i class="fas fa-plus me-1"></i> Add User</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Linked Student</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && mysqli_num_rows($result) > 0): $i = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td class="fw-medium"><?php echo $i++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($row['username']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td>
                                        <?php
                                        $role_class = 'secondary';
                                        if ($row['role'] == 'admin') $role_class = 'danger';
                                        elseif ($row['role'] == 'faculty') $role_class = 'primary';
                                        elseif ($row['role'] == 'student') $role_class = 'success';
                                        ?>
                                        <span class="badge bg-<?php echo $role_class; ?>"><?php echo ucfirst($row['role']); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($row['student_id']): ?>
                                            <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name'] . ' (' . $row['enrollment_no'] . ')'); ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                        <div class="d-flex gap-1 justify-content-end">
                                            <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning-custom btn-sm-custom btn-custom" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-danger-custom btn-sm-custom btn-custom" title="Delete" onclick="return confirm('Are you sure you want to delete this user?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                        <?php else: ?>
                                        <span class="badge bg-secondary">You</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="fas fa-users fa-3x text-muted mb-3 d-block"></i>
                                        <p class="text-muted mb-2">No users found.</p>
                                        <a href="add.php" class="btn btn-primary-custom btn-custom">
                                            <i class="fas fa-plus me-1"></i> Add Your First User
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
