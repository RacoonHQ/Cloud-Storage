<?php
require_once '../../includes/config.php'; // Memuat konfigurasi database
require_once '../../includes/session_check.php'; // Mengecek apakah user sudah login
require_once '../../classes/FileManager.php'; // Memuat class FileManager

$file_id = isset($_GET['id']) ? (int)$_GET['id'] : 0; // Ambil id file dari parameter GET
$fileManager = new FileManager($conn); // Membuat objek FileManager
$fileManager->previewFile($file_id, $_SESSION['user_id']); // Tampilkan preview file ke browser
exit(); // Hentikan eksekusi script