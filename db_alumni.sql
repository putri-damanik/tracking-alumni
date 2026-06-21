-- =========================================================
-- DATABASE: db_alumni
-- Sistem Tracking Alumni Berbasis Web dengan Visualisasi Karir
-- Import file ini melalui phpMyAdmin XAMPP
-- =========================================================

CREATE DATABASE IF NOT EXISTS db_alumni
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE db_alumni;

-- =========================================================
-- TABEL: tabel_users
-- Menyimpan kredensial login untuk admin dan alumni
-- =========================================================
DROP TABLE IF EXISTS tabel_karir;
DROP TABLE IF EXISTS tabel_alumni;
DROP TABLE IF EXISTS tabel_users;

CREATE TABLE tabel_users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'alumni') NOT NULL DEFAULT 'alumni',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- TABEL: tabel_alumni
-- Menyimpan data biodata alumni, terhubung ke tabel_users
-- =========================================================
CREATE TABLE tabel_alumni (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    nim VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    jurusan VARCHAR(100) NOT NULL,
    tahun_lulus YEAR NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_alumni_user
        FOREIGN KEY (user_id) REFERENCES tabel_users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- TABEL: tabel_karir
-- Menyimpan data perjalanan karir alumni, terhubung ke tabel_alumni
-- =========================================================
CREATE TABLE tabel_karir (
    id INT(11) NOT NULL AUTO_INCREMENT,
    alumni_id INT(11) NOT NULL,
    status ENUM('Bekerja', 'Wirausaha', 'Kuliah', 'Mencari Kerja') NOT NULL,
    sektor_industri VARCHAR(100) DEFAULT NULL,
    nama_perusahaan VARCHAR(150) DEFAULT NULL,
    gaji_pertama DECIMAL(15,2) DEFAULT 0,
    waktu_tunggu_bulan INT(11) DEFAULT 0,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_alumni_karir (alumni_id),
    CONSTRAINT fk_karir_alumni
        FOREIGN KEY (alumni_id) REFERENCES tabel_alumni(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- DATA SAMPEL
-- Password asli untuk kedua akun di bawah adalah: 123
-- Password sudah di-hash menggunakan bcrypt (password_hash PHP)
-- =========================================================

-- 1. Akun Admin
INSERT INTO tabel_users (username, password, role) VALUES
('admin', '$2b$10$o9IV10FgWzh4EeV.69AmoePstydWsiLnTLcWXD/Moajvf2WkY08.m', 'admin');

-- 2. Akun Alumni (user_id = 2)
INSERT INTO tabel_users (username, password, role) VALUES
('budianto', '$2b$10$uDV7pEOxxuaJdXUNPC1lV.YKSG3b/P2.IhWejd/KpuH.qay0sNOEO', 'alumni');

-- Tambahan beberapa akun alumni lain agar data dashboard lebih representatif
INSERT INTO tabel_users (username, password, role) VALUES
('siti_aminah', '$2b$10$uDV7pEOxxuaJdXUNPC1lV.YKSG3b/P2.IhWejd/KpuH.qay0sNOEO', 'alumni'),
('rendra_saputra', '$2b$10$uDV7pEOxxuaJdXUNPC1lV.YKSG3b/P2.IhWejd/KpuH.qay0sNOEO', 'alumni'),
('dewi_lestari', '$2b$10$uDV7pEOxxuaJdXUNPC1lV.YKSG3b/P2.IhWejd/KpuH.qay0sNOEO', 'alumni');

-- =========================================================
-- DATA SAMPEL: tabel_alumni
-- =========================================================
INSERT INTO tabel_alumni (user_id, nim, nama, jurusan, tahun_lulus, email) VALUES
(2, '2018110001', 'Budianto Pratama', 'Teknik Informatika', 2022, 'budianto@example.com'),
(3, '2018110002', 'Siti Aminah', 'Sistem Informasi', 2022, 'siti.aminah@example.com'),
(4, '2017110015', 'Rendra Saputra', 'Manajemen Informatika', 2021, 'rendra.saputra@example.com'),
(5, '2019110023', 'Dewi Lestari', 'Teknik Informatika', 2023, 'dewi.lestari@example.com');

-- =========================================================
-- DATA SAMPEL: tabel_karir
-- =========================================================
INSERT INTO tabel_karir (alumni_id, status, sektor_industri, nama_perusahaan, gaji_pertama, waktu_tunggu_bulan) VALUES
(1, 'Bekerja', 'Teknologi Informasi', 'PT Sinergi Digital Nusantara', 6500000, 3),
(2, 'Bekerja', 'Perbankan', 'Bank Mandiri Tbk', 7200000, 2),
(3, 'Wirausaha', 'Perdagangan', 'Toko Online Rendra Store', 5000000, 1),
(4, 'Mencari Kerja', NULL, NULL, 0, 0);

-- =========================================================
-- SELESAI
-- Database siap digunakan oleh aplikasi tracking-alumni
-- =========================================================
