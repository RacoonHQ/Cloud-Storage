<?php
require_once '../../includes/config.php'; // Memuat konfigurasi database
require_once '../../includes/session_check.php'; // Mengecek apakah user sudah login
require_once '../../classes/FileManager.php'; // Memuat class FileManager

$fileManager = new FileManager($conn); // Membuat objek FileManager

// Jika parameter 'all' = 1, maka hapus semua file & folder di trash user
if (isset($_GET['all']) && $_GET['all'] == 1) {
    // Ambil semua file yang dihapus (deleted=1) milik user
    $stmt = $conn->prepare("SELECT id FROM files WHERE user_id = ? AND deleted = 1");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    // Hapus file satu per satu secara permanen (termasuk file fisik)
    while ($row = $result->fetch_assoc()) {
        $fileManager->deleteFilePermanent($row['id']);
    }

    // Ambil semua folder yang dihapus (deleted=1) milik user
    $stmt = $conn->prepare("SELECT id FROM folders WHERE user_id = ? AND deleted = 1");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    // Hapus folder satu per satu secara permanen (termasuk isi folder)
    while ($row = $result->fetch_assoc()) {
        $fileManager->deleteFolderPermanent($row['id']);
    }

    // Redirect ke halaman trash dengan pesan sukses
    header("Location: ../../pages/trash.php?success=Semua file dan folder dihapus permanen");
    exit;
}

// Jika hapus satu file/folder saja
$file_id = isset($_GET['id']) ? (int)$_GET['id'] : 0; // Ambil id file dari parameter GET
$redirect = '../../pages/trash.php?success=deleted'; // Redirect ke trash setelah hapus

// Hapus file secara permanen (termasuk file fisik)
if ($fileManager->deleteFilePermanent($file_id)) {
    header("Location: $redirect");
} else {
    header("Location: $redirect&error=delete_failed");
}
exit();