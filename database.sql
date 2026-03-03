-- Database: parkir_siswa

CREATE DATABASE IF NOT EXISTS parkir_siswa;
USE parkir_siswa;

-- Tabel Users
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'petugas', 'owner') NOT NULL,
    nama VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Kendaraan
CREATE TABLE kendaraan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    plat_nomor VARCHAR(20) UNIQUE NOT NULL,
    jenis_kendaraan ENUM('motor', 'mobil') NOT NULL,
    waktu_masuk DATETIME NOT NULL,
    waktu_keluar DATETIME NULL,
    durasi INT NULL,
    biaya INT NULL,
    status ENUM('parkir', 'keluar') DEFAULT 'parkir',
    petugas_masuk INT,
    petugas_keluar INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (petugas_masuk) REFERENCES users(id),
    FOREIGN KEY (petugas_keluar) REFERENCES users(id)
);

-- Tabel Tarif
CREATE TABLE tarif (
    id INT PRIMARY KEY AUTO_INCREMENT,
    jenis_kendaraan ENUM('motor', 'mobil') NOT NULL,
    tarif_per_jam INT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Kapasitas
CREATE TABLE kapasitas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    jenis_kendaraan ENUM('motor', 'mobil') NOT NULL,
    total_slot INT NOT NULL,
    slot_terisi INT DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert Data Default Users
INSERT INTO users (username, password, role, nama) VALUES
('admin', MD5('admin123'), 'admin', 'Administrator'),
('petugas1', MD5('petugas123'), 'petugas', 'Petugas 1'),
('owner', MD5('owner123'), 'owner', 'Owner Parkir');

-- Insert Data Default Tarif
INSERT INTO tarif (jenis_kendaraan, tarif_per_jam) VALUES
('motor', 2000),
('mobil', 5000);

-- Insert Data Default Kapasitas
INSERT INTO kapasitas (jenis_kendaraan, total_slot, slot_terisi) VALUES
('motor', 50, 0),
('mobil', 30, 0);
