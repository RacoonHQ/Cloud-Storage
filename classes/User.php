<?php
// Mendefinisikan kelas User untuk manajemen data dan autentikasi user
class User {
    // Properti koneksi database
    private $conn;
    
    // Konstruktor: menerima koneksi database
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Fungsi untuk registrasi user baru
    public function register($data) {
        // Escape input user untuk mencegah SQL Injection
        $first_name = $this->conn->real_escape_string($data['first_name']);
        $last_name = $this->conn->real_escape_string($data['last_name']);
        $email = $this->conn->real_escape_string($data['email']);
        // Hash password sebelum disimpan ke database
        $password = password_hash($data['password'], PASSWORD_DEFAULT);

        // Query insert user baru ke database
        $sql = "INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssss", $first_name, $last_name, $email, $password);
        return $stmt->execute();
    }
    
    // Fungsi untuk login user
    public function login($email, $password) {
        // Query ambil data user berdasarkan email
        $sql = "SELECT id, password FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Jika user ditemukan, verifikasi password
        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                // Set session user_id jika login berhasil
                $_SESSION['user_id'] = $row['id'];
                return true;
            }
        }
        // Login gagal
        return false;
    }
    
    // Fungsi untuk mengambil data user berdasarkan ID
    public function getUserById($id) {
        $stmt = $this->conn->prepare("SELECT id, first_name, last_name, country, photo_path, phone_number, email FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    // Fungsi untuk update profil user
    public function updateProfile($user_id, $data) {
        // Escape input user
        $name = $this->conn->real_escape_string($data['name']);
        $email = $this->conn->real_escape_string($data['email']);
        
        // Query update data user
        $sql = "UPDATE users SET name = ?, email = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $name, $email, $user_id);
        return $stmt->execute();
    }
    
    // Fungsi untuk mengganti password user
    public function changePassword($user_id, $old_password, $new_password) {
        // Ambil password lama dari database
        $sql = "SELECT password FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        // Verifikasi password lama
        if (password_verify($old_password, $result['password'])) {
            // Hash password baru
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            // Update password di database
            $sql = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("si", $hashed_password, $user_id);
            return $stmt->execute();
        }
        // Password lama salah
        return false;
    }
}