<?php
// Tidak perlu session check di halaman about (halaman ini bisa diakses siapa saja)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"> <!-- Set karakter encoding -->
    <title>About Me - ZDRIVE</title> <!-- Judul halaman -->
    <link rel="icon" type="image/png" href="../assets/img/favicon.png"> <!-- Favicon -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"> <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"> <!-- Bootstrap Icons -->
    <link href="../assets/css/style.css" rel="stylesheet"> <!-- Custom CSS -->
</head>
<body>
    <!-- Tombol silang pojok kanan atas untuk kembali ke halaman sebelumnya -->
    <a href="javascript:history.back()" 
       class="btn-close-topright"
       title="Kembali">
        <i class="bi bi-x-lg"></i>
    </a>
    <div class="container py-5"> <!-- Container utama dengan padding vertikal -->
        <!-- About Me Section -->
        <div class="row align-items-center mb-5"> <!-- Baris dengan align center dan margin bawah -->
            <div class="col-lg-7 col-md-8 order-2 order-md-1"> <!-- Kolom utama teks -->
                <div class="main-header mb-2 text-md-start">About Me</div> <!-- Judul utama -->
                <div class="main-subtitle mb-3 text-md-start">Mahasiswa Informatika</div> <!-- Subjudul -->
                <div class="about-desc text-md-start">
                    Project ini merupakan tugas UAS Pemrograman Web.
                    Saya adalah seorang mahasiswa yang sedang belajar membangun aplikasi web modern.
                    ZDRIVE adalah hasil dari dedikasi dan semangat belajar saya di bidang teknologi.
                </div>
                <div class="about-info-row flex-md-row flex-column"> <!-- Baris info tambahan -->
                    <div class="about-info-col">
                        <div class="about-info-title">Culture</div> <!-- Judul info -->
                        <div class="about-info-text">
                            Selalu terbuka untuk belajar hal baru dan meningkatkan skill setiap hari.
                            Setiap ide dan solusi diuji secara mandiri.
                        </div>
                    </div>
                    <div class="about-info-col">
                        <div class="about-info-title">Workplace</div>
                        <div class="about-info-text">
                            Fleksibel, bisa belajar dan mengerjakan project dari mana saja dan kapan saja.
                            Semua proses dikerjakan sendiri, dari backend hingga frontend.
                        </div>
                    </div>
                </div>
            </div>
            <!-- Kolom foto profil -->
            <div class="col-lg-5 col-md-4 text-center order-1 order-md-2 mb-4 mb-md-0 d-flex align-items-center justify-content-md-end justify-content-center">
                <img src="../assets/img/profile.jpg" alt="My Photo" class="profile-photo">
            </div>
        </div>

        <hr class="my-5" > <!-- Garis pemisah -->

        <!-- About ZDRIVE Section -->
        <div class="row align-items-center">
            <div class="col-lg-7 col-md-8 order-2 order-md-1">
                <div class="about-zdrive-header text-md-start">About ZDRIVE</div> <!-- Judul section -->
                <div class="about-desc text-md-start">
                    ZDRIVE adalah proyek cloud storage yang saya bangun sendiri.
                    Fokus pada kemudahan penggunaan, keamanan, dan desain modern.
                    Semua proses mulai dari desain, backend, hingga frontend dikerjakan secara mandiri.
                </div>
                <?php
                    // Selalu tampilkan tombol Dashboard saja
                    echo '<a href="../pages/dashboard.php" class="btn btn-primary mt-3">Dashboard</a>';
                ?>
            </div>
            <!-- Kolom logo ZDRIVE -->
            <div class="col-lg-5 col-md-4 text-center order-1 order-md-2 mb-4 mb-md-0 d-flex align-items-center justify-content-md-end justify-content-center">
                <img src="../assets/img/about.png" alt="ZDRIVE Logo" class="logo-zdrive">
            </div>
        </div>

        <!-- Fitur utama ZDRIVE -->
        <div class="row about-section">
            <div class="col-md-6">
                <div class="about-card h-100">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-cloud-arrow-up text-primary"></i>
                        <span class="fw-bold ms-2">Upload & Download</span>
                    </div>
                    <div class="mb-3 text-muted">
                        Upload file ke cloud dengan mudah dan cepat. Download file kapan saja di mana saja secara aman.
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="about-card h-100">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-folder2-open text-primary"></i>
                        <span class="fw-bold ms-2">Manajemen Folder</span>
                    </div>
                    <div class="mb-3 text-muted" >
                        Buat, ubah nama, dan hapus folder untuk mengatur file sesuai kebutuhan. Navigasi folder yang mudah dan intuitif.
                    </div>
                </div>
            </div>
        </div>
        <div class="row about-section">
            <div class="col-md-6">
                <div class="about-card h-100">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-share-fill text-primary" ></i>
                        <span class="fw-bold ms-2">Berbagi File</span>
                    </div>
                    <div class="mb-3 text-muted">
                        Bagikan file dengan mudah melalui tautan. Atur akses file sesuai kebutuhan privasi Anda.
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="about-card h-100">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-shield-lock text-primary"></i>
                        <span class="fw-bold ms-2">Keamanan Data</span>
                    </div>
                    <div class="mb-3 text-muted">
                        Setiap file dienkripsi dan hanya dapat diakses oleh pemiliknya. Sistem login aman dengan proteksi data.
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer -->
    <footer class="text-center py-4" >
        &copy; 2025 ZDRIVE. All rights reserved.
    </footer>
</body>
</html>