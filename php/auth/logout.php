<?php
require_once '../../includes/config.php';
require_once '../../classes/Auth.php';

// Hapus token_login di database jika user login
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("UPDATE users SET token_login=NULL WHERE id=?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
}

// Hapus cookie remember_token
setcookie('remember_token', '', time() - 3600, "/");

Auth::logout();
header("Location: ../../pages/login.php");
exit();
