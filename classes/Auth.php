<?php
// Mendefinisikan kelas Auth untuk manajemen autentikasi user
class Auth {
    // Fungsi statis untuk mengecek apakah user sudah login
    public static function isLoggedIn() {
        // Mengembalikan true jika session 'user_id' sudah diset, false jika tidak
        return isset($_SESSION['user_id']);
    }
    // Fungsi statis untuk logout user
    public static function logout() {
        // Menghancurkan seluruh session yang aktif
        session_destroy();
    }
}