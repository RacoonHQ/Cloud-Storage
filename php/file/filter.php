<?php
ini_set('display_errors', 0); // Nonaktifkan tampilan error di browser
ini_set('log_errors', 1); // Aktifkan pencatatan error ke log server
error_reporting(E_ALL); // Tampilkan semua jenis error (untuk logging)

session_start(); // Mulai session PHP
require_once __DIR__ . '/../../includes/config.php'; // Load konfigurasi database

// Cek apakah user sudah login (ada session user_id)
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Set HTTP status 401 Unauthorized
    header('Content-Type: application/json'); // Set response ke JSON
    echo json_encode(['error' => 'Unauthorized']); // Kirim pesan error
    exit;
}

$user_id = $_SESSION['user_id']; // Ambil user_id dari session
$type = isset($_GET['type']) ? $_GET['type'] : ''; // Ambil tipe filter dari parameter GET

$result = []; // Inisialisasi array hasil

if ($type === 'folder') {
    // Jika filter hanya folder
    $sql = "SELECT id, name FROM folders WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql); // Siapkan query
    $stmt->bind_param("i", $user_id); // Bind user_id
    $stmt->execute(); // Eksekusi query
    $folders = $stmt->get_result(); // Ambil hasil query
    while ($row = $folders->fetch_assoc()) { // Loop setiap folder
        $result[] = [
            'item_type' => 'folder', // Tipe item folder
            'id' => $row['id'], // ID folder
            'name' => $row['name'] // Nama folder
        ];
    }
} elseif ($type === 'document') {
    // Jika filter hanya file (dokumen, gambar, dll)
    $sql = "SELECT id, filename, size, uploaded_at FROM files WHERE user_id = ? ORDER BY uploaded_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $files = $stmt->get_result();
    while ($row = $files->fetch_assoc()) {
        $result[] = [
            'item_type' => 'file', // Tipe item file
            'id' => $row['id'], // ID file
            'filename' => $row['filename'], // Nama file
            'size' => $row['size'], // Ukuran file
            'uploaded_at' => $row['uploaded_at'] // Tanggal upload
        ];
    }
} elseif ($type === 'alphabet') {
    // Jika filter urut abjad (folder dan file)
    // Folder
    $sql = "SELECT id, name FROM folders WHERE user_id = ? ORDER BY name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $folders = $stmt->get_result();
    while ($row = $folders->fetch_assoc()) {
        $result[] = [
            'item_type' => 'folder',
            'id' => $row['id'],
            'name' => $row['name']
        ];
    }
    // File
    $sql = "SELECT id, filename, size FROM files WHERE user_id = ? ORDER BY filename ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $files = $stmt->get_result();
    while ($row = $files->fetch_assoc()) {
        $result[] = [
            'item_type' => 'file',
            'id' => $row['id'],
            'filename' => $row['filename'],
            'size' => $row['size']
        ];
    }
} else {
    // Default: urut terbaru (folder dan file)
    // Folder
    $sql = "SELECT id, name, created_at FROM folders WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $folders = $stmt->get_result();
    while ($row = $folders->fetch_assoc()) {
        $result[] = [
            'item_type' => 'folder',
            'id' => $row['id'],
            'name' => $row['name'],
            'created_at' => $row['created_at']
        ];
    }
    // File
    $sql = "SELECT id, filename, size, uploaded_at FROM files WHERE user_id = ? ORDER BY uploaded_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $files = $stmt->get_result();
    while ($row = $files->fetch_assoc()) {
        $result[] = [
            'item_type' => 'file',
            'id' => $row['id'],
            'filename' => $row['filename'],
            'size' => $row['size'],
            'uploaded_at' => $row['uploaded_at']
        ];
    }
}

header('Content-Type: application/json'); // Set response ke JSON
echo json_encode($result); // Kirim hasil dalam format JSON
exit; // Hentikan eksekusi script

?>