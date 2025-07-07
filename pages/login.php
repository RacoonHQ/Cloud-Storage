<?php
require_once '../includes/config.php'; // Meng-include file konfigurasi utama (database, BASE_URL, dsb)

// Jika user sudah login, redirect ke dashboard
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Cloud Drive</title> <!-- Judul halaman -->
    <link rel="icon" type="image/png" href="../assets/img/favicon.png"> <!-- Favicon -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"  rel="stylesheet"> <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"> <!-- Bootstrap CSS -->
    <link href="../assets/css/style.css" rel="stylesheet"> <!-- Custom CSS -->
</head>
    <!-- Floating Info Button -->
    <a href="about.php" class="floating-info-btn" title="Tentang Cloud Drive">
        <i class="bi bi-info-circle"></i>
    </a>
<body class="bg-light"> <!-- Background halaman login -->
<div class="container-fluid"> <!-- Container utama -->
    <div class="row min-vh-100"> <!-- Baris dengan tinggi minimal 100vh -->
        <!-- Left Info -->
        <div class="col-md-6 d-none d-md-flex login-left justify-content-center align-items-center">
            <img src="../assets/img/logo.png" alt="Logo" style="height:500px;"> <!-- Logo aplikasi -->
        </div>
        <!-- Right Login Form -->
        <div class="col-md-6 d-flex align-items-center justify-content-center bg-white">
            <div class="card login-form-card p-4 w-100" style="max-width:400px;">
                <h3 class="text-center mb-4 fw-bold">Secure Client Login</h3> <!-- Judul form login -->
                <!-- Tampilkan pesan error jika login gagal -->
                <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid'): ?>
                <div class="alert alert-danger" role="alert">
                    Email atau password salah!
                </div>
                <?php endif; ?>
                <!-- Form login -->
                <form action="../php/auth/login.php" method="POST">
                    <div class="mb-3">
                        <label>Email Address</label>
                        <!-- Input email, auto terisi jika ada cookie remember_email -->
                        <input type="email" name="email" class="form-control" required placeholder="Enter email"
                            value="<?php echo isset($_COOKIE['remember_email']) ? $_COOKIE['remember_email'] : ''; ?>">
                    </div>
                    <div class="text-end mt-1">
                        <!-- Link ke halaman lupa password -->
                        <a href="forgot.php" style="font-size:0.95em;">Forgot?</a>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <div class="input-group">
                            <!-- Input password -->
                            <input type="password" name="password" class="form-control" id="passwordInput" placeholder="Password" required>
                            <!-- Tombol show/hide password -->
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1">
                                <span id="eyeIcon" class="bi bi-eye"></span>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3 form-check">
                        <!-- Checkbox remember me -->
                        <input type="checkbox" class="form-check-input" id="rememberMe" name="remember">
                        <label class="form-check-label" for="rememberMe">Remember Me</label>
                    </div>
                    <!-- Tombol login -->
                    <button type="submit" class="btn btn-warning w-100 mb-3" style="color:#fff;font-weight:bold;">Login</button>
                </form>
                <!-- Link ke halaman register -->
                <div class="text-center mt-2" style="font-size:0.97em;">
                    Not a member yet? <a href="register.php">Create a New Account</a>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
// Script untuk toggle show/hide password
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