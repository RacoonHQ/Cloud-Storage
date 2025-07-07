<?php
require_once '../includes/config.php'; // Include konfigurasi (koneksi db, BASE_URL, dsb)
require_once '../includes/session_check.php'; // Cek apakah user sudah login
require_once '../classes/FileManager.php'; // Include class FileManager untuk operasi file/folder
require_once '../php/utils/functions.php'; // Include fungsi-fungsi utilitas

$fileManager = new FileManager($conn); // Membuat objek FileManager
$current_folder = isset($_GET['folder']) ? (int)$_GET['folder'] : null; // Ambil folder aktif dari URL jika ada
$files = $fileManager->getFolderContents($current_folder, $_SESSION['user_id']); // Ambil file di folder aktif
$breadcrumb = getFolderBreadcrumb($conn, $current_folder, $_SESSION['user_id']); // Ambil breadcrumb folder
$userProfile = $fileManager->getUserProfile($_SESSION['user_id']); // Ambil data profil user

// Query untuk ambil daftar folder utama user untuk sidebar
$sql = "SELECT * FROM folders WHERE user_id = ? AND parent_id IS NULL ORDER BY name ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$sidebar_folders = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Cloud Drive</title> <!-- Judul halaman -->
    <link rel="icon" type="image/png" href="../assets/img/favicon.png"> <!-- Favicon -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"> <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"> <!-- Bootstrap Icons -->
    <link href="../assets/css/style.css" rel="stylesheet"> <!-- Custom CSS -->
</head>
    <!-- Floating Info Button -->
    <a href="about.php" class="floating-info-btn" title="Tentang Cloud Drive">
        <i class="bi bi-info-circle"></i>
    </a>
<body>
<div class="container-fluid"> <!-- Container utama -->
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-12 col-md-2 sidebar-drive py-4 bg-light"
                style="position:sticky; top:0; height:100vh; z-index:10;">
            <div class="d-flex flex-column justify-content-between h-100">
                <!-- Bagian Atas Sidebar -->
                <div>
                    <div class="d-grid mb-4">
                        <!-- Tombol buat file/folder baru -->
                        <button class="btn btn-drive-new" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="bi bi-plus-lg"></i> Baru
                        </button>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item mb-2">
                            <span class="text-muted small ms-3">Drive Saya</span>
                        </li>
                        <li class="nav-item mb-2">
                            <!-- Link ke beranda -->
                            <a class="nav-link<?php if(!$current_folder) echo ' active'; ?>" href="dashboard.php">
                                <i class="bi bi-house-door me-2"></i> Beranda
                            </a>
                            <!-- Link ke halaman manajemen folder -->
                            <a class="nav-link<?php if(!$current_folder) echo ' active'; ?>" href="manage_folder.php">
                                <i class="bi bi-folder me-2"></i> My Folder
                            </a>
                        </li>

                        <!-- Scrollable folder list -->
                        <div style="max-height: 200px; overflow-y: auto; margin: 10px 0">
                            <?php while($f = $sidebar_folders->fetch_assoc()): ?>
                            <li class="nav-item">
                                <!-- Link ke folder tertentu -->
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
                            <!-- Link ke halaman sampah -->
                            <a class="nav-link" href="trash.php"><i class="bi bi-trash me-2"></i> Sampah</a>
                        </li>
                        <li class="nav-item">
                            <!-- Link ke halaman penyimpanan -->
                            <a class="nav-link" href=""><i class="bi bi-cloud me-2"></i> Penyimpanan</a>
                            <?php include '../php/file/adresbar.php'; // Include bar penyimpanan ?>
                        </li>  
                    </ul>
                </div>

                <!-- Bagian Bawah Sidebar -->
                <div class="sidebar-profile d-flex align-items-center mb-2 px-2">
                    <!-- Foto profil user -->
                    <img src="<?php echo $userProfile['photo_path'] ? htmlspecialchars($userProfile['photo_path']) : '../assets/img/pp.jpg'; ?>" 
                        alt="Profile" class="rounded-circle me-2" style="width:38px;height:38px;object-fit:cover;">
                    <div>
                        <!-- Nama user, link ke settings -->
                        <a href="settings.php" class="fw-bold nav-link p-0" style="font-size:1em;">
                            <?php echo htmlspecialchars($userProfile['first_name']); ?>
                        </a>
                    </div>
                </div>
                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <!-- Tombol logout -->
                        <a class="nav-link" href="../php/auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </nav>
        <!-- Main Content -->
        <main class="col-md-10 ms-sm-auto px-md-4">
            <div class="drive-main">
                <!-- Notifikasi sukses/error -->
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
                        <button type="button" class="search-filter-btn px-2" data-bs-toggle="modal" data-bs-target="#filterModal">
                            <i class="bi bi-sliders"></i>
                        </button>
                        <button class="btn btn-primary search-btn-custom" type="submit">Cari</button>
                    </div>
                </form>
                
                <!-- Breadcrumb navigasi folder -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb drive-breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php" style="color:#232323; text-decoration:none;">Root</a></li>
                        <?php foreach($breadcrumb as $b): ?>
                            <li class="breadcrumb-item">
                                <a href="dashboard.php?folder=<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['name']); ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </nav>

                <!-- Drag & Drop Upload -->
                <div id="drop-area" class="border p-3 mb-4 text-center bg-light rounded">
                    <p class="mb-1"><i class="bi bi-cloud-arrow-up" style="font-size:2rem;color:#1967d2"></i></p>
                    <p>Tarik file ke sini atau klik untuk mengupload</p>
                    <input type="file" id="fileElem" name="file" style="display:none" onchange="handleFiles(this.files)">
                    <button class="btn btn-outline-primary" onclick="document.getElementById('fileElem').click();">Pilih File</button>
                </div>
                <script>
                // Drag & drop upload handler
                var dropArea = document.getElementById('drop-area');
                dropArea.addEventListener('dragover', function(e) { e.preventDefault(); dropArea.classList.add('bg-info'); });
                dropArea.addEventListener('dragleave', function(e) { e.preventDefault(); dropArea.classList.remove('bg-info'); });
                dropArea.addEventListener('drop', function(e) {
                    e.preventDefault();
                    dropArea.classList.remove('bg-info');
                    handleFiles(e.dataTransfer.files);
                });
                function handleFiles(files) {
                    var formData = new FormData();
                    formData.append('file', files[0]);
                    <?php if($current_folder): ?>
                    formData.append('folder_id', <?php echo $current_folder; ?>);
                    <?php endif; ?>
                    fetch('../php/file/upload.php', { method: 'POST', body: formData })
                        .then(response => window.location.reload());
                }
                </script>

                <div id="searchResults" style="display:none;"></div>

                <!-- File & Folder Grid -->
                <div class="drive-grid mt-4" id="mainGrid">
                    <!-- Folder List -->
                    <?php
                    // Query folder di dalam folder aktif
                    $sql = "SELECT * FROM folders WHERE user_id = ? AND parent_id " . ($current_folder ? "= ?" : "IS NULL") . " ORDER BY name ASC";
                    $stmt = $conn->prepare($sql);
                    if ($current_folder) {
                        $stmt->bind_param("ii", $_SESSION['user_id'], $current_folder);
                    } else {
                        $stmt->bind_param("i", $_SESSION['user_id']);
                    }
                    $stmt->execute();
                    $subfolders = $stmt->get_result();
                    while($folder = $subfolders->fetch_assoc()):
                    ?>
                    <div class="drive-item position-relative">
                        <!-- Tombol aksi folder (rename/delete) -->
                        <button class="btn btn-light btn-sm p-1 border-0 shadow-none position-absolute top-0 end-0 m-2"
                            data-bs-toggle="modal" data-bs-target="#folderActionModal"
                            data-id="<?php echo $folder['id']; ?>"
                            data-name="<?php echo htmlspecialchars($folder['name']); ?>">
                            <i class="bi bi-three-dots-vertical fs-5" style="color:#1967d2;"></i>
                        </button>
                        <!-- Link buka folder -->
                        <a href="dashboard.php?folder=<?php echo $folder['id']; ?>" style="text-decoration:none;color:inherit;">
                            <div><i class="bi bi-folder-fill"></i></div>
                            <div class="mt-2 fw-bold text-truncate" title="<?php echo htmlspecialchars($folder['name']); ?>" style="max-width:150px; overflow:hidden;">
                                <?php echo htmlspecialchars($folder['name']); ?>
                            </div>
                        </a>
                    </div>
                    <?php endwhile; ?>

                    <!-- File List -->
                    <?php while($file = $files->fetch_assoc()): ?>
                    <div class="drive-item position-relative">
                        <!-- Tombol aksi file (rename/delete/preview) -->
                        <button class="btn btn-light btn-sm p-1 border-0 shadow-none position-absolute top-0 end-0 m-2"
                            data-bs-toggle="modal" data-bs-target="#fileActionModal"
                            data-id="<?php echo $file['id']; ?>"
                            data-name="<?php echo htmlspecialchars($file['filename']); ?>">
                            <i class="bi bi-three-dots-vertical fs-5" style="color:#1967d2;"></i>
                        </button>
                        <div>
                            <?php
                            // Pilih icon file sesuai ekstensi
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
                        <!-- Nama file -->
                        <div class="mt-2 fw-bold text-truncate" title="<?php echo htmlspecialchars($file['filename']); ?>" style="max-width:150px; overflow:hidden;">
                            <?php echo htmlspecialchars($file['filename']); ?>
                        </div>
                        <!-- Ukuran file -->
                        <div class="small text-muted"><?php echo formatFileSize($file['size']); ?></div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <!-- end grid -->
            </div>
        </main>
    </div>
</div>

<!-- Modal Aksi Folder (rename/delete) -->
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
          <a href="#" id="folderActionRename" class="list-group-item list-group-item-action"><i class="bi bi-pencil-square me-2"></i>Rename</a>
          <a href="#" id="folderActionDelete" class="list-group-item list-group-item-action text-danger"><i class="bi bi-trash me-2"></i>Delete</a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Rename Folder -->
<div class="modal fade" id="renameFolderModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" id="renameFolderForm" method="POST" action="../php/folder/rename.php">
      <div class="modal-header">
        <h5 class="modal-title">Rename Folder</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="folder_id" id="renameFolderId">
        <div class="mb-3">
          <label for="renameFolderName" class="form-label">Nama Baru</label>
          <input type="text" class="form-control" name="new_name" id="renameFolderName" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Rename</button>
      </div>
    </form>
  </div>
</div>
<script>
// Script untuk aksi modal folder (rename/delete)
var folderActionModal = document.getElementById('folderActionModal');
folderActionModal.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    var folderId = button.getAttribute('data-id');
    var folderName = button.getAttribute('data-name');
    document.getElementById('folderActionName').textContent = folderName;

    // Rename Folder
    document.getElementById('folderActionRename').onclick = function(e) {
        e.preventDefault();
        document.getElementById('renameFolderId').value = folderId;
        document.getElementById('renameFolderName').value = folderName;
        var renameFolderModal = new bootstrap.Modal(document.getElementById('renameFolderModal'));
        renameFolderModal.show();
        var folderActionModalInstance = bootstrap.Modal.getInstance(folderActionModal);
        folderActionModalInstance.hide();
    };

    // Delete Folder
    document.getElementById('folderActionDelete').onclick = function(e) {
        e.preventDefault();
        if(confirm('Yakin hapus folder ini?')) {
            window.location.href = '../php/folder/delete.php?id=' + folderId;
        }
    };
});
</script>

<!-- Modal Rename File -->
<div class="modal fade" id="renameModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" id="renameForm" method="POST" action="../php/file/rename.php">
      <div class="modal-header">
        <h5 class="modal-title">Rename File</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="file_id" id="renameFileId">
        <div class="mb-3">
          <label for="renameFileName" class="form-label">Nama Baru</label>
          <input type="text" class="form-control" name="new_name" id="renameFileName" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Rename</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Aksi File (preview/rename/delete) -->
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
          <a href="#" id="fileActionView" class="list-group-item list-group-item-action"><i class="bi bi-eye me-2"></i>Preview</a>
          <a href="#" id="fileActionRename" class="list-group-item list-group-item-action"><i class="bi bi-pencil-square me-2"></i>Rename</a>
          <a href="#" id="fileActionDelete" class="list-group-item list-group-item-action text-danger"><i class="bi bi-trash me-2"></i>Delete</a>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
// Script untuk aksi modal file (preview/rename/delete)
var fileActionModal = document.getElementById('fileActionModal');
fileActionModal.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    var fileId = button.getAttribute('data-id');
    var fileName = button.getAttribute('data-name');
    document.getElementById('fileActionName').textContent = fileName;

    document.getElementById('fileActionView').href = 'view_file.php?id=' + fileId;
    document.getElementById('fileActionDelete').onclick = function(e) {
        e.preventDefault();
        if(confirm('Yakin hapus file ini?')) {
            window.location.href = '../php/file/delete.php?id=' + fileId;
        }
    };

    // Rename
    document.getElementById('fileActionRename').onclick = function(e) {
        e.preventDefault();
        document.getElementById('renameFileId').value = fileId;
        document.getElementById('renameFileName').value = fileName;
        var renameModal = new bootstrap.Modal(document.getElementById('renameModal'));
        renameModal.show();
        var fileActionModalInstance = bootstrap.Modal.getInstance(fileActionModal);
        fileActionModalInstance.hide();
    };
});
</script>

<!-- Upload Modal (pilih upload file atau buat folder baru) -->
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
// Upload file tunggal dari modal
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

<!-- Modal Filter File/Folder -->
<div class="modal fade" id="filterModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Filter File & Folder</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="filterForm">
          <div class="mb-3">
            <label class="form-label">Urutkan Berdasarkan</label>
            <select class="form-select" name="type">
              <option value="latest">Terbaru</option>
              <option value="folder">Folder</option>
              <option value="document">Dokumen</option>
              <option value="alphabet">Abjad</option>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary">Terapkan</button>
      </div>
    </div>
  </div>
</div>

<script>
// Fungsi hapus file (konfirmasi)
function deleteFile(id) {
    if(confirm('Are you sure you want to delete this file?')) {
        window.location.href = '../php/file/delete.php?id=' + id;
    }
}

// Search & Filter
const searchInput = document.getElementById('searchInput');
const searchResults = document.getElementById('searchResults');
const mainGrid = document.getElementById('mainGrid');
const filterForm = document.getElementById('filterForm');
const filterModal = document.getElementById('filterModal');
const filterButton = filterModal.querySelector('.btn-primary');
let searchTimeout = null;
const userId = <?php echo json_encode($_SESSION['user_id']); ?>;

// Fungsi fetch dan render hasil pencarian/filter
function fetchAndRender({q = '', type = ''} = {}) {
    let url = '';
    if (q) {
        url = '../php/file/search.php?q=' + encodeURIComponent(q);
    } else {
        url = '../php/file/filter.php?type=' + encodeURIComponent(type);
    }
    fetch(url)
        .then(res => res.json())
        .then(data => {
            let html = '';
            if (data.length === 0) {
                html = '<div class="search-result-empty">Tidak ada hasil ditemukan.</div>';
            } else {
                html = '<div class="drive-grid mt-4">';
                data.forEach(item => {
                    if (item.item_type === 'folder') {
                        // Render folder hasil pencarian/filter
                        html += `
                        <div class="drive-item position-relative">
                            <button class="btn btn-light btn-sm p-1 border-0 shadow-none position-absolute top-0 end-0 m-2"
                                data-bs-toggle="modal" data-bs-target="#folderActionModal"
                                data-id="${item.id}" data-name="${item.name}">
                                <i class="bi bi-three-dots-vertical fs-5" style="color:#1967d2;"></i>
                            </button>
                            <a href="dashboard.php?folder=${item.id}" style="text-decoration:none;color:inherit;">
                                <div><i class="bi bi-folder-fill"></i></div>
                                <div class="mt-2 fw-bold text-truncate" title="${item.name}" style="max-width:150px; overflow:hidden;">
                                    ${item.name}
                                </div>
                            </a>
                        </div>`;
                    } else {
                        // Render file hasil pencarian/filter
                        let icon = '<i class="bi bi-file-earmark"></i>';
                        let ext = (item.filename || '').split('.').pop().toLowerCase();
                        if (['jpg','jpeg','png','gif'].includes(ext)) {
                            let rel = 'uploads/' + userId + '/' + encodeURIComponent(item.filename);
                            icon = '<i class="bi bi-file-earmark-image"></i>';
                        } else if (ext === 'pdf') {
                            icon = '<i class="bi bi-file-earmark-pdf"></i>';
                        } else if (['doc','docx'].includes(ext)) {
                            icon = '<i class="bi bi-file-earmark-word"></i>';
                        } else if (['xls','xlsx'].includes(ext)) {
                            icon = '<i class="bi bi-file-earmark-excel"></i>';
                        } else if (['ppt','pptx'].includes(ext)) {
                            icon = '<i class="bi bi-file-earmark-ppt"></i>';
                        }
                        html += `
                        <div class="drive-item position-relative">
                            <button class="btn btn-light btn-sm p-1 border-0 shadow-none position-absolute top-0 end-0 m-2"
                                data-bs-toggle="modal" data-bs-target="#fileActionModal"
                                data-id="${item.id}" data-name="${item.filename}">
                                <i class="bi bi-three-dots-vertical fs-5" style="color:#1967d2;"></i>
                            </button>
                            <div>${icon}</div>
                            <div class="mt-2 fw-bold text-truncate" title="${item.filename}" style="max-width:150px; overflow:hidden;">
                                ${item.filename}
                            </div>
                            <div class="small text-muted">${item.size ? formatFileSize(item.size) : ''}</div>
                        </div>`;
                    }
                });
                html += '</div>';
            }
            mainGrid.style.display = 'none';
            searchResults.innerHTML = html;
            searchResults.style.display = '';
        });
}

// Event search (input)
searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    let q = this.value.trim();
    if (!q) {
        searchResults.style.display = 'none';
        mainGrid.style.display = '';
        return;
    }
    searchTimeout = setTimeout(() => {
        fetchAndRender({q});
    }, 300);
});

// Event filter (klik Terapkan)
filterButton.addEventListener('click', function() {
    const type = filterForm.type.value;
    fetchAndRender({type});
    // Sembunyikan modal filter setelah klik Terapkan
    var modal = bootstrap.Modal.getInstance(filterModal);
    modal.hide();
});

// Format file size (JS, simple version)
function formatFileSize(bytes) {
    if (bytes >= 1024*1024*1024) return (bytes/(1024*1024*1024)).toFixed(2) + ' GB';
    if (bytes >= 1024*1024) return (bytes/(1024*1024)).toFixed(2) + ' MB';
    if (bytes >= 1024) return (bytes/1024).toFixed(2) + ' KB';
    return bytes + ' B';
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script> <!-- Bootstrap JS -->
</body>
</html>