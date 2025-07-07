<?php
require_once '../includes/config.php';
require_once '../includes/session_check.php';
require_once '../classes/FileManager.php';
require_once '../php/utils/functions.php';

$fileManager = new FileManager($conn);
$current_folder = isset($_GET['folder']) ? (int)$_GET['folder'] : null;
$userProfile = $fileManager->getUserProfile($_SESSION['user_id']);

// Hapus permanen file/folder yang sudah lebih dari 1 hari di trash
$now = date('Y-m-d H:i:s');
$expire = date('Y-m-d H:i:s', strtotime('-1 day'));

// Hapus file
$stmt = $conn->prepare("DELETE FROM files WHERE deleted = 1 AND deleted_at <= ? AND user_id = ?");
$stmt->bind_param("si", $expire, $_SESSION['user_id']);
$stmt->execute();

// Hapus folder
$stmt = $conn->prepare("DELETE FROM folders WHERE deleted = 1 AND deleted_at <= ? AND user_id = ?");
$stmt->bind_param("si", $expire, $_SESSION['user_id']);
$stmt->execute();

// Ambil folder yang dihapus
$sql = "SELECT * FROM folders WHERE user_id = ? AND deleted = 1 ORDER BY deleted_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$subfolders = $stmt->get_result();

// Ambil file yang dihapus
$sql = "SELECT * FROM files WHERE user_id = ? AND deleted = 1 ORDER BY deleted_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$files = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Cloud Drive</title>
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
    <!-- Floating Info Button -->
    <a href="about.php" class="floating-info-btn" title="Tentang Cloud Drive">
        <i class="bi bi-info-circle"></i>
    </a>
<body>
<?php
// Query untuk sidebar folder list (hanya folder aktif, bukan yang dihapus)
$sidebar_folders = $conn->query("SELECT * FROM folders WHERE user_id = {$_SESSION['user_id']} AND deleted = 0 ORDER BY name ASC");
?>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-2 sidebar-drive py-4 bg-light"
                style="position:sticky; top:0; height:100vh; z-index:10;">
            <div class="d-flex flex-column justify-content-between h-100">
                <!-- Bagian Atas Sidebar -->
                <div>
                    <div class="d-grid mb-4">
                        <button class="btn btn-drive-new" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="bi bi-plus-lg"></i> Baru
                        </button>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item mb-2">
                            <span class="text-muted small ms-3">Drive Saya</span>
                        </li>
                        <li class="nav-item mb-2">
                            <a class="nav-link<?php if(!$current_folder) echo ' active'; ?>" href="dashboard.php">
                                <i class="bi bi-house-door me-2"></i> Beranda
                            </a>
                            <a class="nav-link<?php if(!$current_folder) echo ' active'; ?>" href="manage_folder.php">
                                <i class="bi bi-folder me-2"></i> My Folder
                            </a>
                        </li>
                        <!-- Scrollable folder list -->
                        <div style="max-height: 200px; overflow-y: auto; margin: 10px 0">
                            <?php while($f = $sidebar_folders->fetch_assoc()): ?>
                            <li class="nav-item">
                                <a class="nav-link<?php if($current_folder == $f['id']) echo ' active'; ?>" href="dashboard.php?folder=<?php echo $f['id']; ?>">
                                    <i class="bi bi-folder me-2"></i>
                                    <span class="text-truncate d-inline-block" style="max-width:100px; vertical-align:middle;" title="<?php echo htmlspecialchars($f['name']); ?>">
                                        <?php echo htmlspecialchars($f['name']); ?>
                                    </span>
                                </a>
                            </li>
                            <?php endwhile; ?>
                        </div>
                        <!-- End scrollable folder list -->
                        <li class="nav-item">
                            <a class="nav-link" href="trash.php"><i class="bi bi-trash me-2"></i> Sampah</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href=""><i class="bi bi-cloud me-2"></i> Penyimpanan</a>
                            <?php include '../php/file/adresbar.php'; ?>
                        </li>  
                    </ul>
                </div>
                <!-- Bagian Bawah Sidebar -->
                <div class="sidebar-profile d-flex align-items-center mb-2 px-2">
                    <img src="<?php echo $userProfile['photo_path'] ? htmlspecialchars($userProfile['photo_path']) : '../assets/img/pp.jpg'; ?>" 
                        alt="Profile" class="rounded-circle me-2" style="width:38px;height:38px;object-fit:cover;">
                    <div>
                        <a href="settings.php" class="fw-bold nav-link p-0" style="font-size:1em;">
                            <?php echo htmlspecialchars($userProfile['first_name']); ?>
                        </a>
                    </div>
                </div>
                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a class="nav-link" href="../php/auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </nav>
        <!-- Main Content -->
        <main class="col-md-10 ms-sm-auto px-md-4">
            <div class="drive-main">
                <?php if(isset($_GET['success'])): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
                <?php elseif(isset($_GET['error'])): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>
                <!-- Search Bar -->
                <form id="searchForm" action="javascript:void(0)" method="GET" class="mb-4">
                    <div class="search-bar-custom d-flex">
                        <span class="input-group-text bg-transparent border-0 px-2"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-0 search-input-custom" name="q" id="searchInput" placeholder="Telusuri di Drive" autocomplete="off" style="background:transparent;">
                        <button type="button" class="search-filter-btn px-2"><i class="bi bi-sliders"></i></button>
                        <button class="btn btn-primary search-btn-custom" type="submit">Cari</button>
                    </div>
                </form>
                <!-- Info Trash Expired -->
                <div class="alert alert-warning mb-0 d-flex justify-content-between align-items-center" style="font-size:15px; margin-bottom:24px;">
                    <span>
                        <i class="bi bi-info-circle me-2"></i>
                        File dan folder di sampah akan <b>terhapus permanen setelah 1 hari</b> sejak dihapus.
                    </span>
                    <?php if ($subfolders->num_rows > 0 || $files->num_rows > 0): ?>
                    <a href="../php/file/delete_permanent.php?all=1"
                    class="btn btn-trash-all btn-sm ms-3"
                    onclick="return confirm('Yakin hapus semua file & folder di sampah secara permanen?')">
                        <i class="bi bi-trash"></i> Hapus Semua
                    </a>
                    <?php endif; ?>
                </div>
                <div id="searchResults" style="display:none;"></div>
                <!-- File & Folder Grid -->
                <div class="drive-grid">
                    <!-- Folder List di Trash -->
                    <?php while($folder = $subfolders->fetch_assoc()): ?>
                        <div class="drive-item position-relative">
                            <button class="btn btn-light btn-sm p-1 border-0 shadow-none position-absolute top-0 end-0 m-2"
                                data-bs-toggle="modal" data-bs-target="#folderActionModal"
                                data-id="<?php echo $folder['id']; ?>"
                                data-name="<?php echo htmlspecialchars($folder['name']); ?>">
                                <i class="bi bi-three-dots-vertical fs-5" style="color:#1967d2;"></i>
                            </button>
                            <div><i class="bi bi-folder-fill"></i></div>
                            <div class="mt-2 fw-bold text-truncate" title="<?php echo htmlspecialchars($folder['name']); ?>" style="max-width:150px; overflow:hidden;">
                                <?php echo htmlspecialchars($folder['name']); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    <!-- File List di Trash -->
                    <?php while($file = $files->fetch_assoc()): ?>
                        <div class="drive-item position-relative">
                            <button class="btn btn-light btn-sm p-1 border-0 shadow-none position-absolute top-0 end-0 m-2"
                                data-bs-toggle="modal" data-bs-target="#fileActionModal"
                                data-id="<?php echo $file['id']; ?>"
                                data-name="<?php echo htmlspecialchars($file['filename']); ?>">
                                <i class="bi bi-three-dots-vertical fs-5" style="color:#1967d2;"></i>
                            </button>
                            <div>
                                <?php
                                $ext = strtolower(pathinfo($file['filename'], PATHINFO_EXTENSION));
                                if(in_array($ext, ['jpg','jpeg','png','gif'])) {
                                    echo '<i class="bi bi-file-earmark-image"></i>';
                                } elseif($ext === 'pdf') {
                                    echo '<i class="bi bi-file-earmark-pdf"></i>';
                                } elseif(in_array($ext, ['doc','docx'])) {
                                    echo '<i class="bi bi-file-earmark-word"></i>';
                                } elseif(in_array($ext, ['xls','xlsx'])) {
                                    echo '<i class="bi bi-file-earmark-excel"></i>';
                                } elseif(in_array($ext, ['ppt','pptx'])) {
                                    echo '<i class="bi bi-file-earmark-ppt"></i>';
                                } else {
                                    echo '<i class="bi bi-file-earmark"></i>';
                                }
                                ?>
                            </div>
                            <div class="mt-2 fw-bold text-truncate" title="<?php echo htmlspecialchars($file['filename']); ?>" style="max-width:150px; overflow:hidden;">
                                <?php echo htmlspecialchars($file['filename']); ?>
                            </div>
                            <div class="small text-muted"><?php echo formatFileSize($file['size']); ?></div>
                        </div>
                    <?php endwhile; ?>
                </div> <!-- end drive-grid -->
                <!-- end grid -->
                 <?php if ($subfolders->num_rows == 0 && $files->num_rows == 0): ?>
                    <div class="search-result-empty">Sampah kosong</div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<!-- Modal Aksi File -->
<div class="modal fade" id="fileActionModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Aksi File</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="fileActionName" class="fw-bold mb-3"></div>
        <div class="list-group">
          <a href="#" id="fileActionRestore" class="list-group-item list-group-item-action">
            <i class="bi bi-arrow-counterclockwise me-2"></i>Restore
          </a>
          <a href="#" id="fileActionDelete" class="list-group-item list-group-item-action text-danger">
            <i class="bi bi-trash me-2"></i>Delete Permanent
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
var fileActionModal = document.getElementById('fileActionModal');
fileActionModal.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    var fileId = button.getAttribute('data-id');
    var fileName = button.getAttribute('data-name');
    document.getElementById('fileActionName').textContent = fileName;

    // Restore File
    document.getElementById('fileActionRestore').onclick = function(e) {
        e.preventDefault();
        window.location.href = '../php/file/restore.php?type=file&id=' + fileId;
    };

    // Delete File
    document.getElementById('fileActionDelete').onclick = function(e) {
        e.preventDefault();
        if(confirm('Yakin hapus file ini secara permanen?')) {
            window.location.href = '../php/file/delete_permanent.php?id=' + fileId + '&redirect=dashboard';
        }
    };
});
</script>

<!-- Modal Aksi Folder -->
<div class="modal fade" id="folderActionModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Aksi Folder</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="folderActionName" class="fw-bold mb-3"></div>
        <div class="list-group">
          <a href="#" id="folderActionRestore" class="list-group-item list-group-item-action">
            <i class="bi bi-arrow-counterclockwise me-2"></i>Restore
          </a>
          <a href="#" id="folderActionDelete" class="list-group-item list-group-item-action text-danger">
            <i class="bi bi-trash me-2"></i>Delete Permanent
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
// Modal aksi folder di trash
var folderActionModal = document.getElementById('folderActionModal');
folderActionModal.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    var folderId = button.getAttribute('data-id');
    var folderName = button.getAttribute('data-name');
    document.getElementById('folderActionName').textContent = folderName;

    // Restore Folder
    document.getElementById('folderActionRestore').onclick = function(e) {
        e.preventDefault();
        window.location.href = '../php/folder/restore.php?id=' + folderId;
    };

    // Delete Folder
    document.getElementById('folderActionDelete').onclick = function(e) {
        e.preventDefault();
        if(confirm('Yakin hapus folder ini secara permanen?')) {
            window.location.href = '../php/folder/delete_permanent.php?id=' + folderId + '&redirect=dashboard';
        }
    };
});
</script>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Aksi Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="list-group">
                    <!-- Folder Baru -->
                    <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-2"
                        data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#folderModal">
                        <i class="bi bi-folder-plus"></i> Folder Baru
                    </button>
                    <!-- Upload File -->
                    <label class="list-group-item list-group-item-action d-flex align-items-center gap-2" style="cursor:pointer;">
                        <i class="bi bi-file-earmark-arrow-up"></i> Upload File
                        <input type="file" name="file" class="d-none" onchange="handleSingleFile(this)">
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
// Upload file tunggal
function handleSingleFile(input) {
    if (!input.files.length) return;
    var formData = new FormData();
    formData.append('file', input.files[0]);
    <?php if($current_folder): ?>
    formData.append('folder_id', <?php echo $current_folder; ?>);
    <?php endif; ?>
    fetch('../php/file/upload.php', { method: 'POST', body: formData })
        .then(() => window.location.reload());
}
</script>

<!-- New Folder Modal -->
<div class="modal fade" id="folderModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Folder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="../php/folder/create.php" method="POST">
                    <div class="mb-3">
                        <label>Folder Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <?php if($current_folder): ?>
                        <input type="hidden" name="parent_id" value="<?php echo $current_folder; ?>">
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">Create</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>