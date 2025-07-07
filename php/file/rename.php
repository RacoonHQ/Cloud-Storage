<?php
require_once '../../includes/config.php'; // Memuat konfigurasi database
require_once '../../includes/session_check.php'; // Mengecek apakah user sudah login
require_once '../../classes/FileManager.php'; // Memuat class FileManager

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rename File
    if (isset($_POST['file_id']) && isset($_POST['new_name'])) {
        $file_id = (int)$_POST['file_id']; // Ambil ID file dari POST
        $new_name = sanitizeInput($_POST['new_name']); // Ambil nama baru dan sanitasi input
        $fileManager = new FileManager($conn); // Buat objek FileManager

        // Jika berhasil rename file
        if ($fileManager->renameFile($file_id, $new_name, $_SESSION['user_id'])) {
            logActivity($conn, $_SESSION['user_id'], "Rename file ID: $file_id to $new_name"); // Catat aktivitas
            header("Location: ../../pages/dashboard.php?success=renamed"); // Redirect ke dashboard dengan pesan sukses
        } else {
            header("Location: ../../pages/dashboard.php?error=rename_failed"); // Redirect ke dashboard dengan pesan error
        }
        exit();
    }

    // Rename Folder
    if (isset($_POST['folder_id']) && isset($_POST['new_name'])) {
        $folder_id = (int)$_POST['folder_id']; // Ambil ID folder dari POST
        $new_name = sanitizeInput($_POST['new_name']); // Ambil nama baru dan sanitasi input

        // Ambil nama lama folder sebelum update
        $stmt = $conn->prepare("SELECT name FROM folders WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $folder_id, $_SESSION['user_id']);
        $stmt->execute();
        $stmt->bind_result($old_name);
        $stmt->fetch();
        $stmt->close();

        $folderManager = new FolderManager($conn); // Buat objek FolderManager

        // Jika berhasil rename folder di database
        if ($folderManager->renameFolder($folder_id, $new_name, $_SESSION['user_id'])) {
            // Rename folder di filesystem
            $user_dir = dirname(__DIR__, 2) . '/uploads/' . $_SESSION['user_id'] . '/';
            $old_path = $user_dir . $old_name;
            $new_path = $user_dir . $new_name;
            if (is_dir($old_path)) {
                rename($old_path, $new_path);

                // Update semua filepath file di folder ini di database
                $sql = "UPDATE files SET filepath = REPLACE(filepath, ?, ?) WHERE folder_id = ? AND user_id = ?";
                $old_path_db = $old_path;
                $new_path_db = $new_path;
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssii", $old_path_db, $new_path_db, $folder_id, $_SESSION['user_id']);
                $stmt->execute();
                $stmt->close();

                // Pindahkan file fisik ke folder baru jika masih ada di folder lama
                $files = scandir($new_path);
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..') {
                        $old_file = $old_path . '/' . $file;
                        $new_file = $new_path . '/' . $file;
                        if (file_exists($old_file)) {
                            rename($old_file, $new_file);
                        }
                    }
                }
            }

            logActivity($conn, $_SESSION['user_id'], "Rename folder ID: $folder_id to $new_name");
            header("Location: ../../pages/dashboard.php?success=folder_renamed");
        } else {
            header("Location: ../../pages/dashboard.php?error=folder_rename_failed");
        }
        exit();
    }
}