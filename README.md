# Dokumentasi Website Cloud Drive

---

## Deskripsi Singkat

**Cloud Drive** adalah aplikasi manajemen file berbasis web yang dikembangkan dengan PHP, MySQL, dan Bootstrap.  
Aplikasi ini memungkinkan pengguna untuk mengunggah, membuat folder, mengelola, mencari, dan menghapus file/folder secara online, mirip seperti Google Drive versi sederhana.

---

## Struktur Folder Project

```
ROOT/
├── assets/
│   ├── css/style.css      # Seluruh Tampilan UI/UX
│   └── img/               # Ikon, foto profil, logo
│
├── classes/
│   ├── Auth.php           # Berisi Fungsi untuk Auth (Login, 
|                            Register, DLL)
│   ├── FileManager.php    # Berisi class Manajemen file dan folder
│   └── User.php           # Berisi Fungsi Manajemen data User 
|                            (Seperti mengambil profil user, update 
|                            data user, dan fitur terkait user 
|                            lainnya.)
│
├── includes/
│   ├── config.php         # Koneksi database
│   ├── header.php         # Header HTML
│   └── session_check.php  # Cek login user
│
├── pages/
|   ├── about.php          # Halaman tentang aplikasi
│   ├── dashboard.php      # Dashboard utama
|   ├── forgot.php         # Halaman form lupa password
│   ├── login.php          # Login user
|   ├── manage_folder.php  # Manajemen folder (opsional: create/
|   |                        rename/delete)
│   ├── register.php       # Registrasi user
│   ├── trash.php          # Halaman sampah
│   ├── settings.php       # Pengaturan user
│   ├── shared_file.php    # Share file via link
│   └── view_file.php      # Preview file
│
├── php/
|   ├── auth/
|   │   ├── forgot.php         # Proses lupa password
|   │   ├── login.php          # Proses login user
|   │   ├── logout.php         # Proses logout user
|   │   └── register.php       # Proses registrasi user
|   │
|   ├── file/
|   │   ├── adresbar.php           # Mendapatkan path/breadcrumb  
|   |   |                            folder
|   │   ├── delete_permanent.php   # Hapus file/folder secara 
|   |   |                            permanen
|   │   ├── delete.php             # Hapus file (soft delete ke 
|   |   |                            trash)
|   │   ├── download.php           # Download file
|   │   ├── filter.php             # Filter file (berdasarkan tipe, 
|   |   |                            dsb)
|   │   ├── preview.php            # Preview file (misal gambar/pdf)
|   │   ├── rename.php             # Rename file/folder
|   │   ├── restore.php            # Restore file/folder dari trash
|   │   ├── search.php             # Cari file/folder
|   │   ├── settings.php           # Update pengaturan file 
|   |   |                            (misal:share, dsb)
|   │   ├── share_link.php         # Generate link share file
|   │   └── upload.php             # Upload file ke server
|   │
|   ├── folder/
|   │   ├── create.php             # Membuat folder baru
|   │   ├── delete.php             # Hapus folder (soft delete ke 
|   |   |                            trash)
|   │   └── rename.php             # Rename folder
|   │
|   └── utils/
|       └── functions.php          # Fungsi-fungsi utilitas 
|                                    (sanitize, log, dsb)
│
├── uploads/               # Folder penyimpanan file user (per user)
│
├── db.sql                 # Skrip struktur database
└── index.php              # Entry point aplikasi
```

---

## Cara Setup Database

1. **Buat Database dan Tabel**
   - Pastikan MySQL/MariaDB sudah berjalan di komputer Anda.
   - Buka phpMyAdmin atau gunakan terminal/command prompt.
   - Import file `db.sql` yang ada di root project:
     - **Via phpMyAdmin:**
       1. Login ke phpMyAdmin.
       2. Klik menu "Import" pada database yang diinginkan (atau buat database baru bernama `cloud_drive`).
       3. Pilih file `db.sql` lalu klik "Go".
     - **Via Terminal/Command Prompt:**
       ```bash
       mysql -u [username] -p < db.sql
       ```
       Ganti `[username]` dengan user MySQL Anda. Masukkan password jika diminta.

2. **Konfigurasi Koneksi Database**
   - Buka file `includes/config.php`.
   - Pastikan konfigurasi host, username, password, dan nama database sesuai dengan pengaturan MySQL Anda.
   - Contoh:
     ```php
     $host = 'localhost';
     $db   = 'cloud_drive';
     $user = 'root';
     $pass = '';
     ```

---

## Alur Kerja Utama

1. **Autentikasi**
   - User login melalui `pages/login.php`.
   - Setelah login, session `user_id` disimpan.

2. **Dashboard**
   - Setelah login, user diarahkan ke `pages/dashboard.php`.
   - User dapat melihat, mencari, mengupload, membuat folder, menghapus, dan mengelola file/folder.

3. **Manajemen File & Folder**
   - Semua aksi (upload, delete, rename, dsb) dilakukan via request ke file di `php/file/` dan `php/folder/`.
   - File yang diupload akan disimpan di `uploads/{user_id}/[nama_folder]/`.

4. **Sampah (Trash)**
   - File/folder yang dihapus akan masuk ke trash (`deleted=1` di database).
   - User dapat menghapus permanen atau restore file/folder dari trash.

5. **Database**
   - Semua query menggunakan koneksi dari `includes/config.php`.
   - Struktur tabel utama: `users`, `folders`, `files`, `activity_log`.

---

## Fitur Utama

- **Login & Logout**
- **Upload File**
- **Buat Folder & Subfolder**
- **Rename, Delete, Restore File/Folder**
- **Trash (Sampah)**
- **Pencarian & Filter File/Folder**
- **Share File via Link**
- **Tampilan icon file sesuai tipe**
- **Responsive UI (Bootstrap)**
- **Log aktivitas user**

---

## Catatan Developer

- Semua request AJAX/backend ke `php/file/` dan `php/folder/`.
- Untuk menambah tipe file baru, tambahkan icon di `assets/img/icons/` dan update logic JS di dashboard.
- Koneksi database hanya di-include dari `includes/config.php`.
- Kode utama frontend ada di `pages/dashboard.php`.
- Folder `uploads/` harus writable oleh web server.

---

## Cara Menambah/Mengubah Fitur

- **Tambah fitur baru:**  
  Buat file PHP di `php/file/` atau `php/folder/` dan endpoint AJAX di JS.
- **Tambah kolom/tabel:**  
  Update `db.sql` dan sesuaikan query di file PHP terkait.
- **Custom tampilan:**  
  Edit `assets/css/style.css` dan file di `pages/`.

---

## Kontak Developer

**Sayyid Abdullah Azzam**  
Email: aeonx31@gmail.com  
GitHub: [https://github.com/RacoonHQ](https://github.com/RacoonHQ)

---

> Dokumentasi ini dibuat untuk memudahkan pengembangan dan pemeliharaan project Cloud Drive.