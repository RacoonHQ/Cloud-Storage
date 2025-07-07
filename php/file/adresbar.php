<?php
// Jika session belum dimulai, mulai session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Ambil user_id dari session (pastikan user sudah login)
$userId = $_SESSION['user_id']; // pastikan session user_id sudah di-set

// Tentukan batas maksimum penyimpanan (50MB dalam byte)
$maxSize = 50 * 1024 * 1024; // 50MB dalam byte

// Tentukan path folder user di server
$userFolder = __DIR__ . "/../../uploads/$userId/";

// Inisialisasi total ukuran file
$totalSize = 0;

// Jika folder user ada
if (is_dir($userFolder)) {
    // Iterasi semua file di dalam folder user (termasuk subfolder)
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($userFolder)) as $file) {
        // Jika item adalah file (bukan folder)
        if ($file->isFile()) {
            // Tambahkan ukuran file ke totalSize
            $totalSize += $file->getSize();
        }
    }
}

// Hitung persentase penggunaan storage (maksimal 100%)
$percent = min(100, ($totalSize / $maxSize) * 100);

// Konversi totalSize ke MB dengan 2 desimal
$usedMB = number_format($totalSize / (1024 * 1024), 2);

// Konversi maxSize ke MB tanpa desimal
$maxMB = number_format($maxSize / (1024 * 1024), 0);

// Cek apakah storage sudah penuh
$isFull = $totalSize >= $maxSize;
?>

<?php if ($isFull): ?>
<script>
    // Jika storage penuh, tampilkan alert ke user
    alert('Kapasitas penyimpanan Anda telah mencapai batas maksimum (<?= $maxMB ?> MB). Anda tidak dapat mengunggah file lagi.');
</script>
<?php endif; ?>

<script>
    // Variabel JS global untuk cek status storage pada form upload
    window.storageFull = <?= $isFull ? 'true' : 'false' ?>;
</script>

<!-- Tampilan bar penggunaan storage -->
<div style="margin-bottom:16px;">
    <!-- Bar abu-abu sebagai background -->
    <div style="background:#e0e0e0;border-radius:4px;height:5px;width:180px;position:relative;margin: 8px auto;">
        <!-- Bar biru sebagai progress penggunaan storage -->
        <div style="background:#1976d2;height:5px;border-radius:4px;width:<?= $percent ?>%;"></div>
    </div>
    <!-- Teks info penggunaan storage -->
    <div style="font-size:13px;color:#444;width:180px;margin:8px auto 0 auto;">
        <?= $usedMB ?> MB dari <?= $maxMB ?> MB telah digunakan
        <?php if ($isFull): ?>
            <span style="color:red;">(Penuh)</span>
        <?php endif; ?>
    </div>
</div>