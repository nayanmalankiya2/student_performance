<div class="header">
    <div class="d-flex align-items-center gap-3">
        <button class="mobile-toggle" id="mobileToggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <div class="page-title">
            <h4>
                <?php
                $page = basename($_SERVER['PHP_SELF']);
                $folder = basename(dirname($_SERVER['PHP_SELF']));
                
                $titles = [
                    'index.php' => 'Dashboard',
                    'login.php' => 'Login',
                ];
                
                if (isset($titles[$page])) {
                    echo $titles[$page];
                } elseif ($folder == 'students') {
                    echo 'Student Management';
                } elseif ($folder == 'subjects') {
                    echo 'Subject Management';
                } elseif ($folder == 'marks') {
                    echo 'Marks Management';
                } elseif ($folder == 'performance') {
                    echo 'Performance Index';
                } else {
                    echo 'Student Performance Index';
                }
                ?>
            </h4>
        </div>
        <?php
        // Determine back button URL based on current page
        $back_url = $base_url . '/index.php';
        if ($folder == 'students' && in_array($page, ['add.php', 'edit.php', 'performance.php'])) {
            $back_url = $base_url . '/students/index.php';
        } elseif ($folder == 'subjects' && in_array($page, ['add.php', 'edit.php'])) {
            $back_url = $base_url . '/subjects/index.php';
        } elseif ($folder == 'marks' && in_array($page, ['add.php', 'edit.php'])) {
            $back_url = $base_url . '/marks/index.php';
        } elseif ($folder == 'performance') {
            $back_url = $base_url . '/index.php';
        }
        
        // Don't show back button on main pages
        if (!in_array($page, ['index.php', 'login.php', 'setup.php'])): 
        ?>
        <a href="<?php echo $back_url; ?>" class="btn btn-outline-custom btn-sm-custom btn-custom ms-2" title="Back">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <?php endif; ?>
    </div>
    <div class="user-info">
        <span>
            <i class="fas fa-user-circle me-1"></i>
            <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User'; ?>
            <span class="badge bg-primary ms-1"><?php echo isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role']) : ''; ?></span>
        </span>
        <a href="<?php echo $base_url; ?>/logout.php" class="btn btn-outline-custom btn-sm-custom btn-custom" title="Logout">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>

<script>
function toggleSidebar() {
    var sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('show');
}
</script>

