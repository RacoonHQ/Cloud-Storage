<?php
require_once '../includes/config.php';
require_once '../includes/session_check.php';
require_once '../classes/FileManager.php';
require_once '../includes/file_icon.php';

$fileManager = new FileManager($conn);
$file_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$file = $fileManager->getFileById($file_id, $_SESSION['user_id']);

if(!$file) {
    header("Location: dashboard.php");
    exit();
}

$ext = strtolower(pathinfo($file['filename'], PATHINFO_EXTENSION));
$relative_path = 'uploads/' . $_SESSION['user_id'] . '/' . basename($file['filepath']);
$web_path = BASE_URL . $relative_path;
?>

<!DOCTYPE html>
<html>
<head>
    <title>View File - <?php echo htmlspecialchars($file['filename']); ?></title>
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4" style="position:relative;">
        <div class="d-flex align-items-center mb-2 justify-content-between">
            <a href="view_file.php?id=<?php echo $fileManager->getPrevFileId($file_id, $_SESSION['user_id']); ?>"
            class="btn btn-dark nav-btn nav-btn-left">
                <i class="bi bi-chevron-left" style="font-size:2rem;"></i>
            </a>
            <a href="view_file.php?id=<?php echo $fileManager->getNextFileId($file_id, $_SESSION['user_id']); ?>"
            class="btn btn-dark nav-btn nav-btn-right">
                <i class="bi bi-chevron-right" style="font-size:2rem;"></i>
            </a>

            <!-- HEADER -->
            <div class="d-flex align-items-center">
                <i class="bi <?php echo getFileIcon($ext); ?> me-2" style="font-size: 1.7rem;"></i>
                <span class="fw-bold"><?php echo htmlspecialchars($file['filename']); ?></span>
            </div>
            <div>
                <!-- Share button -->
                <button id="shareBtn" class="btn btn-light btn-sm me-2" title="Share">
                    <i class="bi bi-share" style="font-size: 1.5rem;"></i>
                </button>
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
                            Not Preview
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Share Link -->
    <div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px;">
                <div class="modal-body">
                    <!-- Header -->
                    <div class="fw-bold mb-1" style="font-size: 1.1rem;">Akses File</div>
                    <!-- Dropdown Access -->
                    <div class="dropdown mb-2">
                        <button class="btn btn-light dropdown-toggle d-flex align-items-center" type="button" id="accessDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="icon-bg"><i id="accessIcon" class="bi bi-lock-fill"></i></span>
                            <span id="accessText" class="fw-semibold">Private</span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="accessDropdown">
                            <li>
                                <a class="dropdown-item d-flex align-items-center active" href="#" data-access="restricted">
                                    <span class="icon-bg"><i class="bi bi-lock-fill"></i></span> Private
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="#" data-access="anyone">
                                    <span class="icon-bg"><i class="bi bi-globe2"></i></span> Public
                                </a>
                            </li>
                        </ul>
                    </div>
                    <!-- Description -->
                    <div id="accessDesc" class="text-muted mb-3" style="font-size: 0.95rem;">
                        Hanya orang yang memiliki akses yang dapat membuka dengan link
                    </div>
                    <!-- Share Link Input -->
                    <div id="shareLinkGroup" class="input-group mb-3" style="display: none;">
                        <input type="text" id="shareLinkInput" class="form-control" readonly>
                        <button class="btn btn-outline-primary" type="button" id="copyShareLinkBtn">
                            <i class="bi bi-link-45deg"></i> Salin link
                        </button>
                    </div>
                    <!-- Footer -->
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Selesai</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

<script>
// Dropdown akses
document.querySelectorAll('.dropdown-item').forEach(function(item) {
    item.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('.dropdown-item').forEach(i => i.classList.remove('active'));
        this.classList.add('active');
        const access = this.getAttribute('data-access');
        if(access === 'restricted') {
            document.getElementById('accessIcon').className = 'bi bi-lock-fill me-2';
            document.getElementById('accessText').textContent = 'Dibatasi';
            document.getElementById('accessDesc').textContent = 'Hanya orang yang memiliki akses yang dapat membuka dengan link';
            document.getElementById('shareLinkGroup').style.display = 'none';
            // Update akses ke backend
            fetch('../php/file/update_access.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id: <?php echo $file['id']; ?>, access: 'restricted'})
            });
        } else {
            document.getElementById('accessIcon').className = 'bi bi-globe2 me-2';
            document.getElementById('accessText').textContent = 'Public';
            document.getElementById('accessDesc').textContent = 'Siapa saja yang memiliki link dapat melihat file ini';
            document.getElementById('shareLinkGroup').style.display = '';
            // Update akses ke backend
            fetch('../php/file/update_access.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id: <?php echo $file['id']; ?>, access: 'anyone'})
            });
        }
    });
});

// Tampilkan modal dan input link hanya jika akses "anyone"
document.getElementById('shareBtn').onclick = function() {
    // Default: sembunyikan share link group
    document.getElementById('shareLinkGroup').style.display = 
        document.querySelector('.dropdown-item.active').getAttribute('data-access') === 'anyone' ? '' : 'none';

    fetch('../php/file/share_link.php?id=<?php echo $file['id']; ?>')
        .then(res => res.json())
        .then(data => {
            if(data.success && data.share_url) {
                document.getElementById('shareLinkInput').value = data.share_url;
                var shareModal = new bootstrap.Modal(document.getElementById('shareModal'));
                shareModal.show();
            } else {
                alert('Gagal membuat link share!');
            }
        });
};

document.getElementById('copyShareLinkBtn').onclick = function() {
    const link = document.getElementById('shareLinkInput').value;
    if(link) {
        navigator.clipboard.writeText(link);
        this.innerHTML = '<i class="bi bi-check2"></i> Tersalin!';
        setTimeout(() => {
            this.innerHTML = '<i class="bi bi-link-45deg"></i> Salin link';
        }, 1500);
    }
};
</script>
</html>