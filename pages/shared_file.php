<?php
require_once '../includes/config.php';
require_once '../classes/FileManager.php';

// Ambil token dari URL
$token = isset($_GET['token']) ? $_GET['token'] : '';

$fileManager = new FileManager($conn);
$file = $fileManager->getFileByShareToken($token);

if (!$file) {
    echo "<h3>File tidak ditemukan atau link sudah tidak berlaku.</h3>";
    exit;
}

$ext = pathinfo($file['filename'], PATHINFO_EXTENSION);
// Gunakan filepath untuk akses file fisik
$filepath = $file['filepath'];
$relative_path = str_replace('\\', '/', substr($filepath, strpos($filepath, 'uploads/')));
$web_path = BASE_URL . $relative_path;

// Fungsi getFileIcon bisa diambil dari view_file.php
function getFileIcon($ext) {
    $ext = strtolower($ext);
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) return 'bi-file-image';
    if (in_array($ext, ['pdf'])) return 'bi-file-earmark-pdf';
    if (in_array($ext, ['doc', 'docx'])) return 'bi-file-earmark-word';
    if (in_array($ext, ['xls', 'xlsx'])) return 'bi-file-earmark-excel';
    if (in_array($ext, ['ppt', 'pptx'])) return 'bi-file-earmark-ppt';
    return 'bi-file-earmark';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shared File - <?php echo htmlspecialchars($file['filename']); ?></title>
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4" style="position:relative;">
        <div class="d-flex align-items-center mb-2 justify-content-between">
            <!-- HEADER -->
            <div class="d-flex align-items-center">
                <i class="bi <?php echo getFileIcon($ext); ?> me-2" style="font-size: 1.7rem;"></i>
                <span class="fw-bold"><?php echo htmlspecialchars($file['filename']); ?></span>
            </div>
            <div>
                <!-- Print button -->
                <button onclick="window.print()" class="btn btn-light btn-sm me-2" title="Print">
                    <i class="bi bi-printer" style="font-size: 1.5rem;"></i>
                </button>
                <!-- Download button -->
                <a href="../php/file/download.php?id=<?php echo $file['id']; ?>" class="btn btn-light btn-sm" title="Download">
                    <i class="bi bi-download" style="font-size: 1.5rem;"></i>
                </a>
                <!-- Close button -->
                <a href="dashboard.php" class="btn btn-light btn-sm" title="Close">
                    <i class="bi bi-x-lg" style="font-size: 1.5rem;"></i>
                </a>
            </div>
        </div>

        <!-- PREVIEW FILE -->
        <div class="row justify-content-center mt-4" style="margin-bottom:0; position:relative;">
            <div class="col-12" style="background:#222; border-radius:0px; height:85vh; display:flex; align-items:flex-start; justify-content:center; padding:2px;">
                <?php if(in_array($ext, ['jpg','jpeg','png','gif'])): ?>
                    <img src="../php/file/preview.php?id=<?php echo $file['id']; ?>" class="img-fluid" style="height:100%; width:auto; max-width:100%; border-radius:0; display:block;">
                <?php elseif($ext === 'pdf'): ?>
                    <embed src="../php/file/preview.php?id=<?php echo $file['id']; ?>" type="application/pdf" width="100%" height="100%" style="border-radius:0; background:#fff; display:block;">
                <?php elseif(in_array($ext, ['xls','xlsx','doc','docx','ppt','pptx'])): ?>
                    <iframe src="https://docs.google.com/gview?url=<?php echo urlencode($web_path); ?>&embedded=true" style="width:100%; height:100%; border:0; background:#fff;"></iframe>
                <?php elseif(in_array($ext, ['txt','csv','log'])): ?>
                    <iframe src="../php/file/preview.php?id=<?php echo $file['id']; ?>" style="width:100%; height:100%; border:0; background:#fff;"></iframe>
                <?php else: ?>
                    <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center;">
                        <div style="color:#fff; font-size:2.5rem; font-weight:600; background:rgba(40,40,40,0.85); padding:32px 48px; border-radius:18px; box-shadow:0 4px 24px 0 rgba(0,0,0,0.18); letter-spacing:1px;">
                            <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                            Tidak Dapat Ditampilkan atau token sudah tidak berlaku
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>