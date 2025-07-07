<?php
require_once '../../includes/config.php'; // Memuat konfigurasi database
require_once '../../includes/session_check.php'; // Mengecek apakah user sudah login
require_once '../../classes/FileManager.php'; // Memuat class FileManager

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Cek jika request POST
    $fileManager = new FileManager($conn); // Buat objek FileManager
    $name = sanitizeInput($_POST['name']); // Ambil dan sanitasi nama folder dari input
    $parent_id = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : null; // Ambil parent_id jika ada

    // Jika berhasil membuat folder baru
    if ($fileManager->createFolder($name, $_SESSION['user_id'], $parent_id)) {
        logActivity($conn, $_SESSION['user_id'], "Create folder: $name"); // Catat aktivitas pembuatan folder
        header("Location: ../../pages/dashboard.php?success=folder_created"); // Redirect ke dashboard dengan pesan sukses
    } else {
        header("Location: ../../pages/dashboard.php?error=folder_failed"); // Redirect ke dashboard dengan pesan error
    }
    exit(); // Hentikan eksekusi script
}