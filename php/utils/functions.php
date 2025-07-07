<?php
// Fungsi untuk memformat ukuran file (bytes ke KB, MB, GB, dst)
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) { // Jika >= 1GB
        return number_format($bytes / 1073741824, 2) . ' GB';
    } else if ($bytes >= 1048576) { // Jika >= 1MB
        return number_format($bytes / 1048576, 2) . ' MB';
    } else if ($bytes >= 1024) { // Jika >= 1KB
        return number_format($bytes / 1024, 2) . ' KB';
    }
    return $bytes . ' bytes'; // Jika < 1KB
}

// Fungsi untuk membersihkan input dari user (hindari XSS)
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Fungsi untuk mengambil ekstensi file dari nama file
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

// Fungsi untuk mencatat aktivitas user ke tabel activity_log
function logActivity($conn, $user_id, $action) {
    $stmt = $conn->prepare("INSERT INTO activity_log (user_id, action) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $action);
    $stmt->execute();
}

// Fungsi untuk mendapatkan breadcrumb folder (navigasi folder)
function getFolderBreadcrumb($conn, $folder_id, $user_id) {
    $breadcrumb = [];
    while ($folder_id) { // Selama masih ada parent folder
        $stmt = $conn->prepare("SELECT id, name, parent_id FROM folders WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $folder_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        if ($result) {
            array_unshift($breadcrumb, $result); // Tambahkan ke awal array
            $folder_id = $result['parent_id']; // Lanjut ke parent
        } else {
            break; // Stop jika tidak ditemukan
        }
    }
    return $breadcrumb; // Kembalikan array breadcrumb
}