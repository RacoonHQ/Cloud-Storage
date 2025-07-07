-- Skrip SQL untuk membuat database dan tabel yang diperlukan untuk aplikasi Cloud Drive
CREATE DATABASE IF NOT EXISTS cloud_drive;
USE cloud_drive;

-- Table structure for table `users`
CREATE TABLE IF NOT EXISTS users (
  id int(11) NOT NULL AUTO_INCREMENT,
  first_name varchar(50) DEFAULT NULL,
  last_name varchar(50) DEFAULT NULL,
  country varchar(50) DEFAULT NULL,
  photo_path varchar(255) DEFAULT NULL,
  phone_number varchar(30) DEFAULT NULL,
  token_login varchar(255) DEFAULT NULL,
  email varchar(100) DEFAULT NULL,
  password varchar(255) DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `folders`
CREATE TABLE IF NOT EXISTS folders (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) DEFAULT NULL,
  name varchar(100) DEFAULT NULL,
  parent_id int(11) DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  deleted tinyint(1) DEFAULT 0,
  deleted_at datetime DEFAULT NULL,
  PRIMARY KEY (id),
  KEY user_id (user_id),
  KEY parent_id (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `files`
CREATE TABLE IF NOT EXISTS files (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) DEFAULT NULL,
  folder_id int(11) DEFAULT NULL,
  filename varchar(255) DEFAULT NULL,
  filepath text DEFAULT NULL,
  size int(11) DEFAULT NULL,
  type varchar(50) DEFAULT NULL,
  uploaded_at datetime DEFAULT current_timestamp(),
  deleted tinyint(1) DEFAULT 0,
  deleted_at datetime DEFAULT NULL,
  PRIMARY KEY (id),
  KEY user_id (user_id),
  KEY folder_id (folder_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `shared_files`
CREATE TABLE IF NOT EXISTS shared_files (
  id int(11) NOT NULL AUTO_INCREMENT,
  file_id int(11) DEFAULT NULL,
  share_token varchar(255) DEFAULT NULL,
  is_public tinyint(1) DEFAULT 0,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY file_id (file_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `activity_log`
CREATE TABLE IF NOT EXISTS activity_log (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) DEFAULT NULL,
  action text DEFAULT NULL,
  timestamp datetime DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Foreign key constraints
ALTER TABLE files ADD CONSTRAINT files_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE;
ALTER TABLE files ADD CONSTRAINT files_ibfk_2 FOREIGN KEY (folder_id) REFERENCES folders (id) ON DELETE SET NULL;
ALTER TABLE folders ADD CONSTRAINT folders_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE;
ALTER TABLE folders ADD CONSTRAINT folders_ibfk_2 FOREIGN KEY (parent_id) REFERENCES folders (id) ON DELETE SET NULL;
ALTER TABLE shared_files ADD CONSTRAINT shared_files_ibfk_1 FOREIGN KEY (file_id) REFERENCES files (id) ON DELETE CASCADE;
ALTER TABLE activity_log ADD CONSTRAINT activity_log_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL;