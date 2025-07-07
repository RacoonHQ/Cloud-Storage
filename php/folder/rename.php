<?php
require_once '../../includes/config.php'; // Memuat konfigurasi database
require_once '../../includes/session_check.php'; // Mengecek apakah user sudah login

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Cek jika request POST
    $folder_id = (int)$_POST['folder_id']; // Ambil ID folder dari POST
    $new_name = sanitizeInput($_POST['new_name']); // Ambil nama baru dan sanitasi input
    $sql = "UPDATE folders SET name = ? WHERE id = ? AND user_id = ?"; // Query update nama folder
    $stmt = $conn->prepare($sql); // Siapkan statement
    $stmt->bind_param("sii", $new_name, $folder_id, $_SESSION['user_id']); // Bind parameter
    if ($stmt->execute()) { // Jika berhasil update
        logActivity($conn, $_SESSION['user_id'], "Rename folder ID: $folder_id to $new_name"); // Catat aktivitas
        header("Location: ../../pages/dashboard.php?success=folder_renamed"); // Redirect ke dashboard dengan pesan sukses
    } else {
        header("Location: ../../pages/dashboard.php?error=folder_rename"); // Redirect ke dashboard dengan pesan error
    }
    exit(); // Hentikan eksekusi script
}