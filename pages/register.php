<?php
require_once '../includes/config.php';

if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - Cloud Drive</title>
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
    <!-- Floating Info Button -->
    <a href="about.php" class="floating-info-btn" title="Tentang Cloud Drive">
        <i class="bi bi-info-circle"></i>
    </a>
<body class="bg-light">
<div class="container-fluid">
    <div class="row min-vh-100">
        <!-- Left Logo -->
        <div class="col-md-6 d-none d-md-flex login-left justify-content-center align-items-center">
            <img src="../assets/img/logo.png" alt="Logo" style="height:500px;">
        </div>
        <!-- Right Register Form -->
        <div class="col-md-6 d-flex align-items-center justify-content-center bg-white">
            <div class="card login-form-card p-4 w-100" style="max-width:400px;">
                <h3 class="text-center mb-4 fw-bold">Create Account</h3>
                <?php if (isset($_GET['error']) && $_GET['error'] === 'email_exists'): ?>
                <div class="alert alert-danger" role="alert">
                    Email sudah terdaftar!
                </div>
                <?php endif; ?>
                <?php if (isset($_GET['success']) && $_GET['success'] === 'registered'): ?>
                <div class="alert alert-success" role="alert">
                    Registrasi berhasil! Silakan login.
                </div>
                <?php endif; ?>
                <form action="../php/auth/register.php" method="POST">
                    <div class="row">
                        <div class="mb-3 col-6">
                            <label>First Name</label>
                            <input type="text" name="first_name" class="form-control" required placeholder="First Name">
                        </div>
                        <div class="mb-3 col-6">
                            <label>Last Name</label>
                            <input type="text" name="last_name" class="form-control" required placeholder="Last Name">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required placeholder="Enter your email">
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <div class="input-group">
                            <input type="password" name="password" class="form-control" id="passwordInput" placeholder="Password" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1">
                                <span id="eyeIcon" class="bi bi-eye"></span>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-warning w-100 mb-3" style="color:#fff;font-weight:bold;">Register</button>
                </form>
                <div class="text-center mt-2" style="font-size:0.97em;">
                    Already have account? <a href="login.php">Login</a>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('togglePassword').addEventListener('click', function () {
    const passwordInput = document.getElementById('passwordInput');
    const eyeIcon = document.getElementById('eyeIcon');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.classList.remove('bi-eye');
        eyeIcon.classList.add('bi-eye-slash');
    } else {
        passwordInput.type = 'password';
        eyeIcon.classList.remove('bi-eye-slash');
        eyeIcon.classList.add('bi-eye');
    }
});
</script>
</body>
</html>