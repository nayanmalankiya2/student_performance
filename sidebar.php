<div class="sidebar" id="sidebar">
    <div class="brand">
        <h4><i class="fas fa-graduation-cap"></i> SPI</h4>
    </div>
<div class="nav-menu">
        <?php if (!is_student()): ?>
        <a href="<?php echo $base_url; ?>/index.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
            <i class="fas fa-th-large"></i>
            <span>Dashboard</span>
        </a>
        <?php endif; ?>
<div class="nav-section-label">MANAGEMENT</div>
        <?php if (is_student()): ?>
        <a href="<?php echo $base_url; ?>/students/performance.php?id=<?php echo get_student_id(); ?>" class="nav-item active">
            <i class="fas fa-user-graduate"></i>
            <span>My Profile</span>
        </a>
        <?php else: ?>
        <a href="<?php echo $base_url; ?>/students/index.php" class="nav-item <?php echo strpos($_SERVER['PHP_SELF'], '/students/') !== false ? 'active' : ''; ?>">
            <i class="fas fa-user-graduate"></i>
            <span>Students</span>
        </a>
        <?php endif; ?>
        <?php if (!is_student()): ?>
        <a href="<?php echo $base_url; ?>/subjects/index.php" class="nav-item <?php echo strpos($_SERVER['PHP_SELF'], '/subjects/') !== false ? 'active' : ''; ?>">
            <i class="fas fa-book"></i>
            <span>Subjects</span>
        </a>
        <a href="<?php echo $base_url; ?>/marks/index.php" class="nav-item <?php echo strpos($_SERVER['PHP_SELF'], '/marks/') !== false ? 'active' : ''; ?>">
            <i class="fas fa-file-alt"></i>
            <span>Marks</span>
        </a>
        <div class="nav-section-label">REPORTS</div>
        <a href="<?php echo $base_url; ?>/performance/index.php" class="nav-item <?php echo strpos($_SERVER['PHP_SELF'], '/performance/') !== false ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar"></i>
            <span>Performance Index</span>
        </a>
        <?php endif; ?>
        <?php if (is_admin()): ?>
        <div class="nav-section-label">SYSTEM</div>
        <a href="<?php echo $base_url; ?>/users/index.php" class="nav-item <?php echo strpos($_SERVER['PHP_SELF'], '/users/') !== false ? 'active' : ''; ?>">
            <i class="fas fa-users-cog"></i>
            <span>Manage Users</span>
        </a>
        <?php endif; ?>
        <hr>
        <a href="<?php echo $base_url; ?>/logout.php" class="nav-item">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

