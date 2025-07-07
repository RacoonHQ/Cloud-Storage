<?php
require_once '../../includes/config.php'; // Memuat konfigurasi database
require_once '../../includes/session_check.php'; // Mengecek apakah user sudah login

// Pastikan parameter ada
if (!isset($_GET['type']) || !isset($_GET['id'])) {
    header("Location: ../../pages/trash.php?error=invalid_param"); // Redirect jika parameter tidak lengkap
    exit();
}

$type = $_GET['type']; // Ambil tipe (file/folder) dari parameter GET
$id = (int)$_GET['id']; // Ambil id file/folder dari parameter GET
$user_id = $_SESSION['user_id']; // Ambil user_id dari session

if ($type === 'file') {
    // Restore file: set deleted=0 pada tabel files
    $stmt = $conn->prepare("UPDATE files SET deleted=0 WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $id, $user_id);
    if ($stmt->execute()) {
        header("Location: ../../pages/trash.php?success=restore_file"); // Redirect jika sukses
    } else {
        header("Location: ../../pages/trash.php?error=restore_failed"); // Redirect jika gagal
    }
    $stmt->close();
} elseif ($type === 'folder') {
    // Restore folder: set deleted=0 pada tabel folders
    $stmt = $conn->prepare("UPDATE folders SET deleted=0 WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $id, $user_id);
    if ($stmt->execute()) {
        header("Location: ../../pages/trash.php?success=restore_folder"); // Redirect jika sukses
    } else {
        header("Location: ../../pages/trash.php?error=restore_failed"); // Redirect jika gagal
    }
    $stmt->close();
} else {
    header("Location: ../../pages/trash.php?error=invalid_type"); // Redirect jika tipe tidak valid
    exit();
}
exit(); // Hentikan eksekusi script