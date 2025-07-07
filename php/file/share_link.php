<?php
require_once __DIR__ . '/../../includes/config.php'; // Memuat konfigurasi database
require_once __DIR__ . '/../../includes/session_check.php'; // Mengecek apakah user sudah login
require_once __DIR__ . '/../../classes/FileManager.php'; // Memuat class FileManager

header('Content-Type: application/json'); // Set response ke JSON

$fileManager = new FileManager($conn); // Membuat objek FileManager
$file_id = isset($_GET['id']) ? (int)$_GET['id'] : 0; // Ambil id file dari parameter GET

if (!$file_id) {
    // Jika id file tidak valid, kirim error
    echo json_encode([
        'success' => false,
        'message' => 'Invalid file ID.'
    ]);
    exit;
}

$share_token = $fileManager->getOrCreateShareToken($file_id); // Ambil/generate token share file
if ($share_token) {
    $share_url = BASE_URL . 'pages/shared_file.php?token=' . $share_token; // Buat URL share
    echo json_encode([
        'success' => true,
        'share_url' => $share_url
    ]);
} else {
    // Jika gagal generate token
    echo json_encode([
        'success' => false,
        'message' => 'Unable to generate share link.'
    ]);
}