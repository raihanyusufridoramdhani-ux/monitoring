CREATE DATABASE IF NOT EXISTS `pcc-110` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `pcc-110`;

CREATE TABLE IF NOT EXISTS regu (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_regu VARCHAR(50) NOT NULL,
  ketua VARCHAR(100) NULL,
  anggota TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  nama VARCHAR(100) NOT NULL,
  role ENUM('operator','pamapta','pimpinan') NOT NULL,
  regu_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_regu FOREIGN KEY (regu_id) REFERENCES regu(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS laporan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tiket VARCHAR(30) NOT NULL UNIQUE,
  nomor_laporan VARCHAR(30) NOT NULL UNIQUE,
  token_akses VARCHAR(64) NOT NULL UNIQUE,
  lokasi TEXT NOT NULL,
  jenis_kejadian VARCHAR(100) NOT NULL,
  prioritas ENUM('tinggi','sedang','rendah') NOT NULL,
  deskripsi TEXT NULL,
  waktu_laporan DATETIME NOT NULL,
  waktu_input DATETIME NOT NULL,
  waktu_kirim DATETIME NULL,
  waktu_buka DATETIME NULL,
  waktu_berangkat DATETIME NULL,
  waktu_tiba DATETIME NULL,
  waktu_selesai DATETIME NULL,
  hasil TEXT NULL,
  tanda_tangan VARCHAR(255) NULL,
  status ENUM('baru','proses','selesai') NOT NULL DEFAULT 'baru',
  regu_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_laporan_status(status),
  INDEX idx_laporan_created(created_at),
  INDEX idx_laporan_prioritas(prioritas),
  CONSTRAINT fk_laporan_regu FOREIGN KEY (regu_id) REFERENCES regu(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS foto (
  id INT AUTO_INCREMENT PRIMARY KEY,
  laporan_id INT NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_foto_laporan(laporan_id),
  CONSTRAINT fk_foto_laporan FOREIGN KEY (laporan_id) REFERENCES laporan(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS audit_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  aktivitas VARCHAR(100) NOT NULL,
  keterangan TEXT NULL,
  ip_address VARCHAR(45) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_audit_created(created_at),
  CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
