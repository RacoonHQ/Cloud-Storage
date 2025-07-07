<?php
// Mendapatkan protocol (http atau https) dari server
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
// Mendapatkan nama host dari server (misal: localhost atau domain)
$host = $_SERVER['HTTP_HOST'];
// Mendefinisikan base path project (ubah sesuai nama folder project Anda)
$basePath = '/cloud-drive/'; // set manual sesuai nama folder project Anda
// Mendefinisikan BASE_URL sebagai konstanta global
define('BASE_URL', $protocol . $host . $basePath);

// Meng-include file utility functions
require_once __DIR__ . '/../php/utils/functions.php';

// Membuat koneksi ke database MySQL
$conn = new mysqli("localhost", "root", "", "cloud_drive");
// Jika koneksi gagal, tampilkan pesan error dan hentikan eksekusi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Memulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mengecek apakah user login dengan Remember Me token
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    // Ambil token dari cookie
    $token = $_COOKIE['remember_token'];
    // Query untuk mencari user berdasarkan token_login
    $stmt = $conn->prepare("SELECT id FROM users WHERE token_login=?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    // Jika token valid, set session user_id
    if ($row = $result->fetch_assoc()) {
        $_SESSION['user_id'] = $row['id'];
    }
}