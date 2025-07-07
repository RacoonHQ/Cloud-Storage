<?php
require_once '../../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $new_password = $_POST['new_password'];

    if (empty($email) || empty($new_password)) {
        header("Location: ../../pages/forgot.php?error=empty");
        exit();
    }

    // Cek apakah email terdaftar
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        // Update password
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt->close();

        $update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $update->bind_param("ss", $hashed, $email);
        if ($update->execute()) {
            header("Location: ../../pages/login.php?reset=success");
            exit();
        } else {
            header("Location: ../../pages/forgot.php?error=updatefail");
            exit();
        }
    } else {
        // Email tidak ditemukan
        header("Location: ../../pages/forgot.php?error=notfound");
        exit();
    }
}

// Jika bukan POST, tampilkan form reset password
?>
<!DOCTYPE html>
<html>
<head>
    <title>Lupa Password - Cloud Drive</title>
    <link rel="icon" type="image/png" href="../../assets/img/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container d-flex justify-content-center align-items-center" style="min-height:100vh;">
    <div class="card p-4" style="max-width:400px; width:100%;">
        <h3 class="text-center mb-4 fw-bold">Reset Password</h3>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger" role="alert">
                <?php
                if ($_GET['error'] === 'empty') echo 'Email dan password baru wajib diisi!';
                elseif ($_GET['error'] === 'notfound') echo 'Email tidak ditemukan!';
                elseif ($_GET['error'] === 'updatefail') echo 'Gagal memperbarui password!';
                else echo 'Terjadi kesalahan!';
                ?>
            </div>
        <?php endif; ?>
        <form action="forgot.php" method="POST">
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required placeholder="Masukkan email anda">
            </div>
            <div class="mb-3">
                <label>Password Baru</label>
                <input type="password" name="new_password" class="form-control" required placeholder="Password baru">
            </div>
            <button type="submit" class="btn btn-primary w-100">Reset Password</button>
        </form>
        <div class="text-center mt-3">
            <a href="../../pages/login.php">Kembali ke Login</a>
        </div>
    </div>
</div>
</body>
</html>