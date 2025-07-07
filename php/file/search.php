<?php
// filepath: c:\xampp\htdocs\cloud-drive\php\file\search.php
require_once '../../includes/config.php'; // Memuat konfigurasi database
require_once '../../includes/session_check.php'; // Mengecek apakah user sudah login

$q = isset($_GET['q']) ? trim($_GET['q']) : ''; // Ambil query pencarian dari parameter GET
$user_id = $_SESSION['user_id']; // Ambil user_id dari session

$results = []; // Inisialisasi array hasil pencarian

// Cari folder
$stmt = $conn->prepare("SELECT id, name FROM folders WHERE user_id = ? AND deleted = 0 AND name LIKE ?");
$like = "%$q%"; // Format pencarian LIKE untuk SQL
$stmt->bind_param("is", $user_id, $like); // Bind parameter user_id dan query LIKE
$stmt->execute(); // Eksekusi query
$res = $stmt->get_result(); // Ambil hasil query
while ($row = $res->fetch_assoc()) { // Loop setiap hasil folder
    $row['item_type'] = 'folder'; // Tandai tipe item sebagai folder
    $results[] = $row; // Tambahkan ke array hasil
}

// Cari file
$stmt = $conn->prepare("SELECT id, filename, size FROM files WHERE user_id = ? AND deleted = 0 AND filename LIKE ?");
$stmt->bind_param("is", $user_id, $like); // Bind parameter user_id dan query LIKE
$stmt->execute(); // Eksekusi query
$res = $stmt->get_result(); // Ambil hasil query
while ($row = $res->fetch_assoc()) { // Loop setiap hasil file
    $row['item_type'] = 'file'; // Tandai tipe item sebagai file
    $results[] = $row; // Tambahkan ke array hasil
}

header('Content-Type: application/json'); // Set response ke JSON
echo json_encode($results); // Kirim hasil pencarian dalam format JSON