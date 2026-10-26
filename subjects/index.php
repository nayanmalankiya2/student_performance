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
    <style>
    .table-header {
        background: #f8f9fa;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid #e9ecef;
    }
    .search-box {
        position: relative;
        width: 280px;
    }
    .search-box i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #6b7280;
        font-size: 14px;
    }
    .search-box input {
        padding-left: 35px;
    }
    </style>
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
<div class="table-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-book me-2"></i>All Subjects</h5>
                    <div class="d-flex align-items-center gap-3">
                        <div class="search-box">
                            <i class="fas fa-search me-2"></i>
                            <input type="text" id="subjectSearch" class="form-control form-control-sm" placeholder="Search by subject code, name, semester...">
                        </div>
                        <?php if (!$is_readonly_view): ?>
                        <a href="add.php" class="btn btn-primary-custom btn-custom"><i class="fas fa-plus me-1"></i> Add Subject</a>
                        <?php endif; ?>
                    </div>
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
                        <tbody id="subjectTableBody">
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
    <script>
    // Live search for subjects table
    document.getElementById('subjectSearch').addEventListener('input', function() {
        var search = this.value.toLowerCase();
        var rows = document.querySelectorAll('#subjectTableBody tr');

        var visibleCount = 0;
        rows.forEach(function(row) {
            // Skip the "no subjects found" row
            if (row.colSpan) return;

            var subjectCode = row.cells[1]?.textContent?.toLowerCase() || '';
            var subjectName = row.cells[2]?.textContent?.toLowerCase() || '';
            var semester = row.cells[3]?.textContent?.toLowerCase() || '';

            if (subjectCode.includes(search) || subjectName.includes(search) || semester.includes(search)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Show/hide "no results" message
        var noResultsRow = document.getElementById('noSearchResults');
        if (visibleCount === 0 && search !== '') {
            if (!noResultsRow) {
                var tbody = document.getElementById('subjectTableBody');
                var newRow = document.createElement('tr');
                newRow.id = 'noSearchResults';
                var colspan = document.querySelector('#subjectTableBody tr:not([colspan])')?.cells.length || 7;
                newRow.innerHTML = '<td colspan="' + colspan + '" class="text-center py-5"><i class="fas fa-search fa-3x text-muted mb-3 d-block"></i><p class="text-muted mb-2">No subjects found matching your search.</p></td>';
                tbody.appendChild(newRow);
            }
        } else if (noResultsRow) {
            noResultsRow.remove();
        }
    });
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>

