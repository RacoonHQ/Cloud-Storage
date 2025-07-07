<?php
// Meng-include file konfigurasi utama (database, BASE_URL, dsb)
require_once 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <!-- Judul halaman -->
    <title>Cloud Drive</title>
    <!-- Link ke Bootstrap CSS dari CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Link ke file CSS custom project -->
    <link href="<?php echo BASE_URL; ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
<!-- Navbar utama -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <!-- Brand/logo Cloud Drive -->
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>">Cloud Drive</a>
        <!-- Menu navigasi kanan -->
        <div class="navbar-nav ms-auto">
            <!-- Link ke halaman Sampah -->
            <a href="<?php echo BASE_URL; ?>pages/settings.php" class="nav-link">Sampah</a>
            <!-- Link ke halaman Penyimpanan -->
            <a href="<?php echo BASE_URL; ?>pages/settings.php" class="nav-link">Penyimpanan</a>
            <!-- Link ke halaman Settings -->
            <a href="<?php echo BASE_URL; ?>pages/settings.php" class="nav-link">Settings</a>
            <!-- Link untuk Logout -->
            <a href="<?php echo BASE_URL; ?>php/auth/logout.php" class="nav-link">Logout</a>
        </div>
    </div>
</nav>
<!-- Container utama untuk konten halaman -->
<div class="container mt-4">