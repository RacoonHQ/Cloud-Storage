<?php
require_once '../../includes/config.php';
require_once '../../classes/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Cek apakah email sudah terdaftar
    $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        // Email sudah ada, redirect dengan pesan error
        header("Location: ../../pages/register.php?error=email_exists");
        exit();
    }

    // ...lanjut proses register seperti biasa...
    $user = new User($conn);
    $user->register($_POST);
    header("Location: ../../pages/login.php?register=success");
    exit();
}