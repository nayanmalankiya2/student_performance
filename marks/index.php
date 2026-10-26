<?php
include __DIR__ . '/../config/config.php';
//include '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Students can view marks (read-only), Admin can view all marks (read-only), only Faculty can modify
$is_student_view = is_student();
$is_readonly_view = is_student() || is_admin();

if ($is_student_view) {
    // Students only see their own marks
    $my_student_id = get_student_id();
    if ($my_student_id <= 0) {
        header("Location: index.php");
        exit();
    }
}

// Delete (faculty only) - students & admin cannot delete
if (isset($_REQUEST['delete'])) {
    if ($is_readonly_view) {
        header("Location: index.php");
        exit();
    }
    $id = (int)$_REQUEST['delete'];
    mysqli_query($conn, "DELETE FROM marks WHERE id = $id");
    $_SESSION['message'] = 'Mark record deleted successfully!';
    $_SESSION['msg_type'] = 'success';
    header("Location: index.php");
    exit();
}

$where = '';
if ($is_student_view) {
    $where = "WHERE m.student_id = $my_student_id";
}
$result = mysqli_query($conn, "SELECT m.*, s.first_name, s.last_name, s.enrollment_no, sub.subject_name, sub.subject_code
                       FROM marks m
                       JOIN students s ON m.student_id = s.id
                       JOIN subjects sub ON m.subject_id = sub.id
                       $where
                       ORDER BY m.created_at DESC");

$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$msg_type = isset($_SESSION['msg_type']) ? $_SESSION['msg_type'] : '';
unset($_SESSION['message'], $_SESSION['msg_type']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marks - Student Performance Index</title>
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
                    <h5><i class="fas fa-file-alt me-2"></i><?php echo $is_student_view ? 'My Marks Records' : 'All Marks Records'; ?></h5>
                    <div class="d-flex align-items-center gap-3">
                        <div class="search-box">
                            <i class="fas fa-search me-2"></i>
                            <input type="text" id="marksSearch" class="form-control form-control-sm" placeholder="Search by name, enrollment, subject...">
                        </div>
                        <?php if (!$is_readonly_view): ?>
                        <a href="add.php" class="btn btn-primary-custom btn-custom"><i class="fas fa-plus me-1"></i> Add Marks</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student</th>
                                <th>Enrollment</th>
                                <th>Subject</th>
                                <th>Sem</th>
                                <th>Internal</th>
                                <th>External</th>
                                <th>Total</th>
<th>Grade</th>
                                <th>Year</th>
                                <?php if (!$is_readonly_view): ?><th class="text-end">Actions</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody id="marksTableBody">
                            <?php if ($result && mysqli_num_rows($result) > 0): $i = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td class="fw-medium"><?php echo $i++; ?></td>
                                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['enrollment_no']); ?></td>
                                    <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                                    <td><span class="badge bg-info"><?php echo $row['semester']; ?></span></td>
                                    <td><?php echo $row['internal_marks']; ?></td>
                                    <td><?php echo $row['external_marks']; ?></td>
                                    <td><strong><?php echo $row['total_marks']; ?></strong></td>
                                    <td>
                                        <?php 
                                        $gr = $row['grade'];
                                        $gr_bg = 'success';
                                        if ($gr == 'F') $gr_bg = 'danger';
                                        elseif ($gr == 'D' || $gr == 'C') $gr_bg = 'warning';
                                        ?>
                                        <span class="badge bg-<?php echo $gr_bg; ?>"><?php echo $gr; ?></span>
                                    </td>
<td><?php echo $row['exam_year']; ?></td>
                                    <?php if (!$is_readonly_view): ?>
                                    <td class="text-end">
                                        <div class="d-flex gap-1 justify-content-end">
                                            <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning-custom btn-sm-custom btn-custom" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-danger-custom btn-sm-custom btn-custom" title="Delete" onclick="return confirm('Are you sure you want to delete this record?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endwhile; else: ?>
                                <tr>
                                    <td colspan="<?php echo $is_readonly_view ? 10 : 11; ?>" class="text-center py-5">
                                        <i class="fas fa-file-alt fa-3x text-muted mb-3 d-block"></i>
                                        <p class="text-muted mb-2">No marks records found.</p>
                                        <?php if (!$is_readonly_view): ?>
                                        <a href="add.php" class="btn btn-primary-custom btn-custom">
                                            <i class="fas fa-plus me-1"></i> Add Your First Record
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
    // Live search for marks table
    document.getElementById('marksSearch').addEventListener('input', function() {
        var search = this.value.toLowerCase();
        var rows = document.querySelectorAll('#marksTableBody tr');

        var visibleCount = 0;
        rows.forEach(function(row) {
            // Skip the "no marks found" row
            if (row.colSpan) return;

            var name = row.cells[1]?.textContent?.toLowerCase() || '';
            var enrollment = row.cells[2]?.textContent?.toLowerCase() || '';
            var subject = row.cells[3]?.textContent?.toLowerCase() || '';
            var semester = row.cells[4]?.textContent?.toLowerCase() || '';
            var internal = row.cells[5]?.textContent?.toLowerCase() || '';
            var external = row.cells[6]?.textContent?.toLowerCase() || '';
            var total = row.cells[7]?.textContent?.toLowerCase() || '';
            var grade = row.cells[8]?.textContent?.toLowerCase() || '';
            var year = row.cells[9]?.textContent?.toLowerCase() || '';

            if (name.includes(search) || enrollment.includes(search) || subject.includes(search) || semester.includes(search) || internal.includes(search) || external.includes(search) || total.includes(search) || grade.includes(search) || year.includes(search)) {
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
                var tbody = document.getElementById('marksTableBody');
                var newRow = document.createElement('tr');
                newRow.id = 'noSearchResults';
                var colspan = document.querySelector('#marksTableBody tr:not([colspan])')?.cells.length || 10;
                newRow.innerHTML = '<td colspan="' + colspan + '" class="text-center py-5"><i class="fas fa-search fa-3x text-muted mb-3 d-block"></i><p class="text-muted mb-2">No marks records found matching your search.</p></td>';
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

