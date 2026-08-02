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
    } elseif ($username == 'admin' && $password == 'admin123') {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'admin';
        $_SESSION['role'] = 'admin';
        header("Location: index.php");
        exit();
    } else {
        $error = 'Invalid username or password!';
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
    </script>
</body>
</html>

