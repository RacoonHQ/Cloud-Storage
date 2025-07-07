<?php
require_once '../../includes/config.php'; // Memuat konfigurasi database
session_start(); // Mulai session

$user_id = $_SESSION['user_id'] ?? null; // Ambil user_id dari session, jika tidak ada set null
if (!$user_id) {
    header('Location: ../../pages/login.php'); // Redirect ke login jika belum login
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $first_name = trim($_POST['first_name'] ?? ''); // Nama depan
    $last_name = trim($_POST['last_name'] ?? ''); // Nama belakang
    $email = trim($_POST['email'] ?? ''); // Email
    $phone_number = trim($_POST['phone_number'] ?? ''); // Nomor telepon
    $country = trim($_POST['country'] ?? ''); // Negara

    // Update profile photo jika ada file diupload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION)); // Ambil ekstensi file
        $allowed = ['png', 'jpg', 'jpeg', 'gif']; // Ekstensi yang diizinkan
        if (in_array($ext, $allowed) && $_FILES['photo']['size'] <= 1048576) { // Maks 1MB
            $newName = 'profile_' . $user_id . '_' . time() . '.' . $ext; // Nama file baru unik
            $target = '../../assets/img/' . $newName; // Path tujuan upload
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) { // Upload file
                // Simpan path relatif ke database
                $photo_path = '../assets/img/' . $newName;
                $stmt = $conn->prepare("UPDATE users SET photo_path=? WHERE id=?");
                $stmt->bind_param("si", $photo_path, $user_id);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    // Update data profil (tanpa password)
    $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=?, phone_number=?, country=? WHERE id=?");
    $stmt->bind_param("sssssi", $first_name, $last_name, $email, $phone_number, $country, $user_id);
    $stmt->execute();
    $stmt->close();

    // Ganti password jika diisi
    if (!empty($_POST['current_password']) && !empty($_POST['new_password'])) {
        $stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($hashed);
        $stmt->fetch();
        $stmt->close();

        if (password_verify($_POST['current_password'], $hashed)) { // Cek password lama benar
            $new_hashed = password_hash($_POST['new_password'], PASSWORD_DEFAULT); // Hash password baru
            $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
            $stmt->bind_param("si", $new_hashed, $user_id);
            $stmt->execute();
            $stmt->close();
        } else {
            header("Location: ../../pages/settings.php?error=wrongpassword"); // Password lama salah
            exit();
        }
    }

    header("Location: ../../pages/settings.php?success=Profile updated"); // Sukses update profil
    exit();
}
header("Location: ../../pages/settings.php"); // Redirect jika bukan POST
exit();