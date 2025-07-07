<?php
require_once '../../includes/config.php'; // Memuat konfigurasi database
require_once '../../includes/session_check.php'; // Mengecek apakah user sudah login
require_once '../../classes/FileManager.php'; // Memuat class FileManager

$file_id = isset($_GET['id']) ? (int)$_GET['id'] : 0; // Ambil id file dari parameter GET
$fileManager = new FileManager($conn); // Membuat objek FileManager

// Jika berhasil menghapus file (soft delete, pindah ke trash)
if ($fileManager->deleteFile($file_id)) {
    logActivity($conn, $_SESSION['user_id'], "Delete file ID: $file_id"); // Catat aktivitas hapus file
    header("Location: ../../pages/dashboard.php?success=deleted"); // Redirect ke dashboard dengan pesan sukses
} else {
    header("Location: ../../pages/dashboard.php?error=delete_failed"); // Redirect ke dashboard dengan pesan error
}
exit(); // Hentikan eksekusi script