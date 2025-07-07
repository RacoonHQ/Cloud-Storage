<?php
require_once '../../includes/config.php'; // Memuat konfigurasi database
require_once '../../includes/session_check.php'; // Mengecek apakah user sudah login
require_once '../../classes/FileManager.php'; // Memuat class FileManager

if (isset($_GET['id'])) {
    $folder_id = (int)$_GET['id']; // Ambil id folder dari parameter GET

    // Ambil nama folder untuk hapus folder fisik
    $sql = "SELECT name FROM folders WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $folder_id, $_SESSION['user_id']);
    $stmt->execute();
    $folder = $stmt->get_result()->fetch_assoc();
    $folder_name = $folder ? $folder['name'] : null;

    // Hapus file di folder (soft delete)
    $sql = "SELECT id FROM files WHERE folder_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $folder_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $fileManager = new FileManager($conn);
    while ($row = $result->fetch_assoc()) {
        $file_id = $row['id'];
        $fileManager->deleteFile($file_id); // Soft delete file
    }

    // Hapus folder di database
    $sql = "DELETE FROM folders WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $folder_id, $_SESSION['user_id']);
    $stmt->execute();

    // Hapus folder fisik jika ada
    if ($folder_name) {
        $user_dir = dirname(__DIR__, 2) . '/uploads/' . $_SESSION['user_id'] . '/';
        $folder_path = $user_dir . $folder_name;
        if (is_dir($folder_path)) {
            // Hapus semua file/subfolder di dalam folder (rekursif)
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($folder_path, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $fileinfo) {
                $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink'); // Jika folder, hapus dengan rmdir, jika file dengan unlink
                $todo($fileinfo->getRealPath());
            }
            rmdir($folder_path); // Hapus folder utama
        }
    }

    logActivity($conn, $_SESSION['user_id'], "Delete folder ID: $folder_id"); // Catat aktivitas hapus folder
    header("Location: ../../pages/dashboard.php?success=folder_deleted"); // Redirect ke dashboard dengan pesan sukses
    exit();
}