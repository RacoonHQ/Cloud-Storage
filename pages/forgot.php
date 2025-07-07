<?php
// Meng-include file konfigurasi utama (database, BASE_URL, dsb)
require_once '../includes/config.php';

// Jika user sudah login, redirect ke dashboard
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <!-- Judul halaman -->
    <title>Forgot Password - Cloud Drive</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
    <!-- Tombol info mengambang -->
    <a href="about.php" class="floating-info-btn" title="Tentang Cloud Drive">
        <i class="bi bi-info-circle"></i>
    </a>
<body class="bg-light">
<div class="container-fluid">
    <div class="row min-vh-100">
        <!-- Kolom kiri: Logo -->
        <div class="col-md-6 d-none d-md-flex login-left justify-content-center align-items-center">
            <img src="../assets/img/logo.png" alt="Logo" style="height:500px;">
        </div>
        <!-- Kolom kanan: Form lupa password -->
        <div class="col-md-6 d-flex align-items-center justify-content-center bg-white">
            <div class="card login-form-card p-4 w-100" style="max-width:400px;">
                <h3 class="text-center mb-4 fw-bold">Forgot Password</h3>
                <!-- Menampilkan pesan error jika ada parameter error di URL -->
                <?php if (isset($_GET['error']) && $_GET['error'] === 'notfound'): ?>
                    <div class="alert alert-danger" role="alert">
                        Email tidak ditemukan!
                    </div>
                <?php elseif (isset($_GET['error']) && $_GET['error'] === 'updatefail'): ?>
                    <div class="alert alert-danger" role="alert">
                        Gagal mengubah password. Silakan coba lagi.
                    </div>
                <?php endif; ?>
                <!-- Form lupa password -->
                <form action="../php/auth/forgot.php" method="POST">
                    <div class="mb-3">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" required placeholder="Enter your email">
                    </div>
                    <div class="mb-3">
                        <label>New Password</label>
                        <div class="input-group">
                            <input type="password" name="new_password" class="form-control" id="forgotPasswordInput" required placeholder="Enter new password">
                            <!-- Tombol show/hide password -->
                            <button class="btn btn-outline-secondary" type="button" id="toggleForgotPassword" tabindex="-1">
                                <span id="forgotEyeIcon" class="bi bi-eye"></span>
                            </button>
                        </div>
                    </div>
                    <!-- Tombol submit -->
                    <button type="submit" class="btn btn-warning w-100 mb-3" style="color:#fff;font-weight:bold;">Change Password</button>
                </form>
                <!-- Link ke halaman login -->
                <div class="text-center mt-2" style="font-size:0.97em;">
                    Remember your password? <a href="login.php">Login</a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Script untuk toggle show/hide password -->
<script>
document.getElementById('toggleForgotPassword').addEventListener('click', function () {
    const passwordInput = document.getElementById('forgotPasswordInput');
    const eyeIcon = document.getElementById('forgotEyeIcon');
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