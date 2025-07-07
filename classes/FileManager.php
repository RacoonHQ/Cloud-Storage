<?php
// Mendefinisikan kelas FileManager untuk manajemen file dan folder
class FileManager {
    // Properti koneksi database
    private $conn;
    // Properti path dasar folder uploads
    private $base_upload_dir;

    // Konstruktor: menerima koneksi database dan set path upload
    public function __construct($db) {
        $this->conn = $db;
        $this->base_upload_dir = dirname(__DIR__) . '/uploads/';
    }

    // Mengambil profil user (nama depan dan path foto)
    public function getUserProfile($user_id) {
        $stmt = $this->conn->prepare("SELECT first_name, photo_path FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($first_name, $photo_path);
        $stmt->fetch();
        $stmt->close();
        return [
            'first_name' => $first_name,
            'photo_path' => $photo_path
        ];
    }

    // Mengupload file ke server dan simpan data ke database
    public function uploadFile($file, $user_id, $folder_id) {
    // Tentukan direktori user
    $user_dir = $this->base_upload_dir . $user_id . '/';
    if (!is_dir($user_dir)) {
        mkdir($user_dir, 0777, true);
    }

    // Ambil nama file asli
    $filename = $file['name'];
    $type = pathinfo($filename, PATHINFO_EXTENSION);
    $size = $file['size'];

    // Cek apakah upload ke folder tertentu
    if ($folder_id) {
            // Ambil nama folder dari database
            $stmt = $this->conn->prepare("SELECT name FROM folders WHERE id=? AND user_id=?");
            $stmt->bind_param("ii", $folder_id, $user_id);
            $stmt->execute();
            $folder = $stmt->get_result()->fetch_assoc();
            $folder_name = $folder ? $folder['name'] : null;
            $target_dir = $user_dir . $folder_name . '/';
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
        } else {
            $target_dir = $user_dir;
        }

        // Buat path file unik dengan timestamp
        $filepath = $target_dir . time() . '_' . $filename;

        // Pindahkan file ke folder tujuan
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Simpan data file ke database
            $sql = "INSERT INTO files (user_id, folder_id, filename, filepath, size, type) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("iissis", $user_id, $folder_id, $filename, $filepath, $size, $type);
            return $stmt->execute();
        }
        return false;
    }

    // Soft delete file (tidak hapus fisik, hanya tandai di database)
    public function deleteFile($file_id) {
        $sql = "UPDATE files SET deleted = 1, deleted_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $file_id);
        return $stmt->execute();
    }

    // Soft delete folder (tidak hapus fisik, hanya tandai di database)
    public function deleteFolder($folder_id) {
        $sql = "UPDATE folders SET deleted = 1, deleted_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $folder_id);
        return $stmt->execute();
    }

    // Hapus file secara permanen (hapus fisik dan database)
    public function deleteFilePermanent($file_id) {
        // Ambil path file
        $sql = "SELECT filepath FROM files WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $file_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        // Hapus file fisik jika ada
        if ($result && file_exists($result['filepath'])) {
            unlink($result['filepath']);
        }

        // Hapus token share jika ada
        $sql = "DELETE FROM shared_files WHERE file_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $file_id);
        $stmt->execute();
        
        // Hapus data file di database
        $sql = "DELETE FROM files WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $file_id);
        return $stmt->execute();
    }

    // Hapus folder secara permanen (rekursif hapus file & subfolder)
    public function deleteFolderPermanent($folder_id) {
        // Ambil user_id dan nama folder sebelum hapus database
        $stmt = $this->conn->prepare("SELECT user_id, name FROM folders WHERE id = ?");
        $stmt->bind_param("i", $folder_id);
        $stmt->execute();
        $stmt->bind_result($user_id, $folder_name);
        $stmt->fetch();
        $stmt->close();

        // 1. Hapus semua file di folder ini (dan file fisik)
        $stmt = $this->conn->prepare("SELECT id FROM files WHERE folder_id = ?");
        $stmt->bind_param("i", $folder_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $this->deleteFilePermanent($row['id']);
        }
        $stmt->close();

        // 2. Hapus semua subfolder (rekursif)
        $stmt = $this->conn->prepare("SELECT id FROM folders WHERE parent_id = ?");
        $stmt->bind_param("i", $folder_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $this->deleteFolderPermanent($row['id']);
        }
        $stmt->close();

        // 3. Hapus folder dari database
        $stmt = $this->conn->prepare("DELETE FROM folders WHERE id = ?");
        $stmt->bind_param("i", $folder_id);
        $stmt->execute();
        $stmt->close();

        // 4. Hapus folder fisik beserta isinya
        if ($user_id && $folder_name) {
            $folder_path = $this->base_upload_dir . $user_id . '/' . $folder_name;
            if (is_dir($folder_path)) {
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($folder_path, \RecursiveDirectoryIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::CHILD_FIRST
                );
                foreach ($files as $fileinfo) {
                    $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                    $todo($fileinfo->getRealPath());
                }
                rmdir($folder_path);
            }
        }
        return true;
    }
    
    // Membuat token share untuk file
    public function shareFile($file_id) {
        $share_token = bin2hex(random_bytes(16));
        $sql = "INSERT INTO shared_files (file_id, share_token) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $file_id, $share_token);
        return $stmt->execute() ? $share_token : false;
    }
    
    // Membuat folder baru untuk user (dan di database)
    public function createFolder($name, $user_id, $parent_id = null) {
        $user_dir = $this->base_upload_dir . $user_id . '/';
        if (!is_dir($user_dir)) {
            mkdir($user_dir, 0777, true);
        }
        $folder_path = $user_dir . $name;
        if (!is_dir($folder_path)) {
            mkdir($folder_path, 0777, true);
        }
        $sql = "INSERT INTO folders (name, user_id, parent_id) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sii", $name, $user_id, $parent_id);
        return $stmt->execute();
    }
    
    // Mengambil daftar file dalam folder tertentu milik user
    public function getFolderContents($folder_id, $user_id) {
        if ($folder_id === null) {
            $sql = "SELECT * FROM files WHERE folder_id IS NULL AND user_id = ? AND deleted = 0";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
        } else {
            $sql = "SELECT * FROM files WHERE folder_id = ? AND user_id = ? AND deleted = 0";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $folder_id, $user_id);
        }
        $stmt->execute();
        return $stmt->get_result();
    }

    // Mengambil data file berdasarkan ID dan user
    public function getFileById($file_id, $user_id) {
        $sql = "SELECT * FROM files WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $file_id, $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Rename file di database (bukan file fisik)
    public function renameFile($file_id, $new_name, $user_id) {
        $sql = "UPDATE files SET filename = ? WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sii", $new_name, $file_id, $user_id);
        return $stmt->execute();
    }

    // Download file (mengirim header dan file ke browser)
    public function downloadFile($file_id, $user_id) {
        $file = $this->getFileById($file_id, $user_id);
        if ($file) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file['filename']) . '"');
            header('Content-Length: ' . filesize($file['filepath']));
            readfile($file['filepath']);
            exit();
        }
        return false;
    }

    // Preview file (khusus gambar/pdf)
    public function previewFile($file_id, $user_id) {
        $file = $this->getFileById($file_id, $user_id);
        if ($file) {
            $ext = strtolower(pathinfo($file['filename'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                header('Content-Type: image/' . $ext);
                readfile($file['filepath']);
                exit();
            } elseif ($ext === 'pdf') {
                header('Content-Type: application/pdf');
                readfile($file['filepath']);
                exit();
            }
        }
        return false;
    }

    // Mengambil ID file sebelumnya (ID lebih kecil)
    public function getPrevFileId($current_id, $user_id) {
        $sql = "SELECT id FROM files WHERE user_id = ? AND deleted = 0 AND id < ? ORDER BY id DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $current_id);
        $stmt->execute();
        $stmt->bind_result($prev_id);
        if ($stmt->fetch()) {
            return $prev_id;
        }
        return $current_id; // Jika tidak ada, tetap di file sekarang
    }

    // Mengambil ID file selanjutnya (ID lebih besar)
    public function getNextFileId($current_id, $user_id) {
        $sql = "SELECT id FROM files WHERE user_id = ? AND deleted = 0 AND id > ? ORDER BY id ASC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $current_id);
        $stmt->execute();
        $stmt->bind_result($next_id);
        if ($stmt->fetch()) {
            return $next_id;
        }
        return $current_id; // Jika tidak ada, tetap di file sekarang
    }

    // Mengambil file berdasarkan token share
    public function getFileByShareToken($token) {
        $sql = "SELECT f.* FROM files f
                INNER JOIN shared_files s ON f.id = s.file_id
                WHERE s.share_token = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Mengambil atau membuat token share untuk file
    public function getOrCreateShareToken($file_id) {
        // Cek apakah sudah ada token
        $stmt = $this->conn->prepare("SELECT share_token FROM shared_files WHERE file_id = ?");
        $stmt->bind_param("i", $file_id);
        $stmt->execute();
        $stmt->bind_result($token);
        if ($stmt->fetch()) {
            $stmt->close();
            return $token;
        }
        $stmt->close();

        // Jika belum ada, buat token baru
        $token = md5(uniqid($file_id, true));
        $stmt = $this->conn->prepare("INSERT INTO shared_files (file_id, share_token) VALUES (?, ?)");
        $stmt->bind_param("is", $file_id, $token);
        if ($stmt->execute()) {
            $stmt->close();
            return $token;
        }
        $stmt->close();
        return false;
    }
}