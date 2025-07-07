<?php
require_once '../../includes/config.php';
require_once '../../classes/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = new User($conn);
    
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']); // cek apakah dicentang

    // ...existing code...
    if ($user->login($email, $password)) {
        logActivity($conn, $_SESSION['user_id'], "Login");

        if ($remember) {
            // Generate token unik
            $token = bin2hex(random_bytes(32));
            // Simpan token ke database
            $stmt = $conn->prepare("UPDATE users SET token_login=? WHERE email=?");
            $stmt->bind_param("ss", $token, $email);
            $stmt->execute();
            // Simpan token ke cookie
            setcookie('remember_token', $token, time() + (86400 * 30), "/"); // 30 hari
        } else {
            setcookie('remember_token', '', time() - 3600, "/");
            // Hapus token di database
            $stmt = $conn->prepare("UPDATE users SET token_login=NULL WHERE email=?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
        }

        header("Location: ../../pages/dashboard.php");
        exit();
     } else {
        header("Location: ../../pages/login.php?error=invalid");
        exit();
    }
}
