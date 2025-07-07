<?php
require_once '../../includes/config.php'; // Memuat konfigurasi database
require_once '../../includes/session_check.php'; // Mengecek apakah user sudah login
require_once '../../classes/FileManager.php'; // Memuat class FileManager

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) { // Cek jika request POST dan ada file diupload
    $fileManager = new FileManager($conn); // Buat objek FileManager
    $folder_id = isset($_POST['folder_id']) ? (int)$_POST['folder_id'] : null; // Ambil folder_id jika ada

    // Proses upload file
    if ($fileManager->uploadFile($_FILES['file'], $_SESSION['user_id'], $folder_id)) {
        logActivity($conn, $_SESSION['user_id'], "Upload file: " . $_FILES['file']['name']); // Catat aktivitas upload
        header("Location: ../../pages/dashboard.php?success=uploaded"); // Redirect ke dashboard jika sukses
    } else {
        header("Location: ../../pages/dashboard.php?error=upload_failed"); // Redirect ke dashboard jika gagal
    }
    exit(); // Hentikan eksekusi script
}