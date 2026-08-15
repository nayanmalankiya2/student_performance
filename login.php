<?php
include 'config/config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_REQUEST['username']);
    $password = trim($_REQUEST['password']);

    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password.';
    } else {
        // DB-based authentication
        $stmt = $conn->prepare("SELECT id, username, email, password, role, student_id FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['student_id'] = $user['student_id'];

// Role-based redirect
            if ($user['role'] == 'student' && !empty($user['student_id']) && $user['student_id'] > 0) {
                header("Location: students/performance.php?id=" . (int)$user['student_id']);
                exit();
            }
            // Admin, faculty, or student without valid linked student → go to dashboard
            header("Location: index.php");
            exit();
        } else {
            $error = 'Invalid username or password!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Student Performance Index</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="logo">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <h2>Welcome Back</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-custom d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="input-box">
                    <i class="fa-solid fa-user left"></i>
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="input-box">
                    <i class="fa-solid fa-lock left"></i>
                    <input type="password" id="pass" name="password" placeholder="Password" required>
                    <i class="fa-solid fa-eye right" onclick="showPass()"></i>
                </div>
                <button type="submit">Login</button>
                <div class="signup">
                    <span>Default Login: </span>
                    <a href="#">admin / admin123</a>
                </div>
            </form>
        </div>
    </div>

    <script>
    function showPass(){
        let p = document.getElementById("pass");
        if(p.type === "password"){
            p.type = "text";
        } else {
            p.type = "password";
        }
    }

    // Apply saved theme on page load (from dashboard toggle)
    (function initTheme() {
        var savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
        }
    })();
    </script>
</body>
</html>

