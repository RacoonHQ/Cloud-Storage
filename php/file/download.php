<?php
require_once '../../includes/config.php'; // Memuat konfigurasi database
require_once '../../includes/session_check.php'; // Mengecek apakah user sudah login
require_once '../../classes/FileManager.php'; // Memuat class FileManager

$file_id = isset($_GET['id']) ? (int)$_GET['id'] : 0; // Ambil id file dari parameter GET
$fileManager = new FileManager($conn); // Membuat objek FileManager
$file = $fileManager->getFileById($file_id, $_SESSION['user_id']); // Ambil data file dari database

if ($file) {
    // Jika file ditemukan, lakukan proses download
    header('Content-Type: application/octet-stream'); // Set header tipe file
    header('Content-Disposition: attachment; filename="' . basename($file['filename']) . '"'); // Set nama file yang akan diunduh
    header('Content-Length: ' . filesize($file['filepath'])); // Set ukuran file
    readfile($file['filepath']); // Kirim file ke browser untuk diunduh
    exit(); // Hentikan eksekusi script setelah download
}