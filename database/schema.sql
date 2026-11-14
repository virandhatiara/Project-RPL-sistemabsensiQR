CREATE DATABASE IF NOT EXISTS db_sistemabsensi;
USE db_sistemabsensi;

-- ==========================================
-- 1. TABEL UTAMA (Independen)
-- ==========================================

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    nama VARCHAR(100) NULL, -- Nama lengkap / pemegang akun (terutama admin & pengguna)
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'guru', 'kepala_sekolah', 'wali_kelas', 'siswa') NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB;

-- Tabel guru dibuat lebih dulu karena ID-nya dibutuhkan di tabel kelas (sebagai wali_kelas)
CREATE TABLE guru (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL, -- Ditambahkan untuk relasi ke akun login (users)
    nip VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    barcode_id VARCHAR(50) UNIQUE,
    no_wa VARCHAR(20) NULL,
    no_hp VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_nip (nip),
    INDEX idx_barcode (barcode_id)
) ENGINE=InnoDB;

CREATE TABLE kelas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kelas VARCHAR(50) NOT NULL UNIQUE,
    wali_kelas_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (wali_kelas_id) REFERENCES guru(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ==========================================
-- 2. TABEL BERELASI
-- ==========================================

CREATE TABLE siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL, -- Ditambahkan untuk relasi ke akun login (users)
    nis VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    kelas_id INT NULL,
    barcode_id VARCHAR(50) UNIQUE,
    no_wa VARCHAR(20) NULL,
    nama_ortu VARCHAR(100) NULL,
    no_hp_ortu VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_nis (nis),
    INDEX idx_barcode_siswa (barcode_id)
) ENGINE=InnoDB;

CREATE TABLE absensi_siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    siswa_id INT NOT NULL,
    tanggal DATE NOT NULL,
    jam TIME NOT NULL,
    status ENUM('hadir', 'sakit', 'izin', 'alpa', 'terlambat') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_tanggal_siswa (tanggal, siswa_id)
) ENGINE=InnoDB;

CREATE TABLE absensi_guru (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guru_id INT NOT NULL,
    tanggal DATE NOT NULL,
    jam_masuk TIME NOT NULL,
    jam_pulang TIME NULL,
    status ENUM('hadir', 'terlambat', 'sakit', 'izin', 'alpa') NOT NULL DEFAULT 'hadir',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guru_id) REFERENCES guru(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_tanggal_guru (tanggal, guru_id)
) ENGINE=InnoDB;

CREATE TABLE jadwal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kelas_id INT NOT NULL,
    guru_id INT NOT NULL,
    mata_pelajaran VARCHAR(100) NOT NULL DEFAULT '-',
    hari ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu') NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (guru_id) REFERENCES guru(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_hari_kelas (hari, kelas_id)
) ENGINE=InnoDB;

CREATE TABLE chat_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pertanyaan TEXT NOT NULL,
    jawaban TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB;

-- ==========================================
-- 3. SEED DATA (DUMMY DATA)
-- ==========================================

-- Catatan: Password untuk semua user adalah 'password' (di-hash menggunakan bcrypt bawaan PHP: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi)

-- Data Users
INSERT INTO users (id, username, password, role) VALUES 
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
(2, 'kepsek', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'kepala_sekolah'),
(3, 'guru1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'guru'),
(4, 'guru2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'guru'),
(5, 'walikelas1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'wali_kelas'),
(6, 'siswa1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'siswa'),
(7, 'siswa2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'siswa'),
(8, 'siswa3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'siswa');

-- Data Guru (Berelasi dengan user_id)
INSERT INTO guru (id, user_id, nip, nama, barcode_id) VALUES 
(1, 3, '198001012005011001', 'Budi Santoso, S.Pd', 'BC-GURU-001'),
(2, 4, '198202022006022002', 'Siti Aminah, M.Pd', 'BC-GURU-002'),
(3, 5, '198503032008031003', 'Ahmad Dahlan, S.Kom', 'BC-GURU-003');

-- Data Kelas (Berelasi dengan wali_kelas_id -> guru)
INSERT INTO kelas (id, nama_kelas, wali_kelas_id) VALUES 
(1, 'X RPL 1', 3),
(2, 'X RPL 2', NULL),
(3, 'XI TKJ 1', NULL);

-- Data Siswa (Berelasi dengan user_id dan kelas_id)
INSERT INTO siswa (id, user_id, nis, nama, kelas_id, barcode_id) VALUES 
(1, 6, '20231001', 'Andi Pratama', 1, 'BC-SISWA-001'),
(2, 7, '20231002', 'Bunga Citra', 1, 'BC-SISWA-002'),
(3, 8, '20231003', 'Cahyo Purnomo', 2, 'BC-SISWA-003');

-- Data Absensi Siswa
INSERT INTO absensi_siswa (siswa_id, tanggal, jam, status) VALUES 
(1, CURRENT_DATE, '06:45:00', 'hadir'),
(2, CURRENT_DATE, '06:50:00', 'hadir'),
(3, CURRENT_DATE, '07:15:00', 'terlambat');

-- Data Absensi Guru
INSERT INTO absensi_guru (guru_id, tanggal, jam_masuk, jam_pulang, status) VALUES 
(1, CURRENT_DATE, '06:30:00', '15:00:00', 'hadir'),
(3, CURRENT_DATE, '06:40:00', NULL, 'hadir');

-- Data Jadwal
INSERT INTO jadwal (kelas_id, guru_id, hari, jam_mulai, jam_selesai) VALUES 
(1, 1, 'Senin', '07:00:00', '08:30:00'),
(1, 3, 'Senin', '08:30:00', '10:00:00'),
(2, 2, 'Senin', '07:00:00', '09:15:00');

-- Data Chat History
INSERT INTO chat_history (user_id, pertanyaan, jawaban) VALUES 
(1, 'Bagaimana cara melihat laporan absensi?', 'Anda dapat melihat laporan absensi melalui menu Laporan di sidebar sebelah kiri.');
