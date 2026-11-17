# Sistem Absensi Sekolah Terintegrasi

[![CI Sistem Absensi SMK NU WARGABINANGUN](https://github.com/virandhatiara/Project-RPL-sistemabsensiQR/actions/workflows/ci.yml/badge.svg)](https://github.com/virandhatiara/Project-RPL-sistemabsensiQR/actions/workflows/ci.yml)
![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)
![PHPUnit](https://img.shields.io/badge/PHPUnit-10.5-green.svg)
![Status](https://img.shields.io/badge/CI%2FCD-Passing-brightgreen.svg)

Sistem Absensi Sekolah adalah aplikasi berbasis web (PHP MVC) yang dirancang untuk mendigitalisasi dan mempermudah proses pencatatan kehadiran siswa dan guru. Sistem ini menggunakan teknologi pindai (scan) barcode/QR Code melalui kamera perangkat (laptop/smartphone) maupun scanner USB fisik. 

Selain fitur utama absensi, sistem ini dilengkapi dengan **Dashboard Dinamis**, **Sistem Pelaporan**, serta **AI Helpdesk Chatbot** pintar untuk membantu pengguna.

---

## 🎯 Fitur Utama Berdasarkan Role

Aplikasi ini mendukung 5 jenis role (hak akses) pengguna:

### 1. Admin
- **Data Master**: Melakukan proses CRUD pada data Siswa, Guru, Kelas, User, Jadwal, dan Barcode.
- **Scanner Kehadiran**: Membuka halaman *scan* kamera khusus untuk siswa dan guru.
- **Manajemen Akun**: Mereset password atau menonaktifkan/mengaktifkan akun pengguna lain.
- **Assign Barcode**: Memasangkan UID/Barcode fisik dengan data siswa maupun guru secara presisi tanpa ganda.

### 2. Kepala Sekolah
- **Dashboard Eksekutif**: Melihat persentase tingkat kehadiran secara _real-time_.
- **Laporan Keseluruhan**: Memonitor laporan kehadiran seluruh Siswa dan Guru.
- **Export Data**: Mengunduh (Export) laporan data absensi ke format **CSV**.

### 3. Wali Kelas
- **Dashboard Wali Kelas**: Menampilkan jumlah dan statistik siswa di kelas yang dikelolanya.
- **Monitoring Kelas**: Melihat absensi harian dan rekap khusus untuk siswa binaan (per kelas).
- **Update Manual**: Mengatur status siswa (Sakit, Izin, Alpa) jika siswa tidak sempat *scan*.

### 4. Guru
- **Dashboard Pribadi**: Menampilkan riwayat absensi pribadi (hadir, tepat waktu, dll).
- **Jadwal Mengajar**: Menampilkan jam mengajar pada hari tersebut.
- **Scan Kamera Mandiri**: Dapat melakukan absensi langsung lewat kamera perangkat pribadi.

### 5. Siswa
- **Dashboard Kehadiran**: Melacak riwayat kehadiran, total sakit, izin, dan alpa.
- **Scan Kamera Mandiri**: Absensi melalui kamera HP/Laptop langsung dari halaman dashboard.

---

## 🤖 AI Helpdesk Chatbot
Aplikasi ini sudah terintegrasi dengan kecerdasan buatan (AI) yang berfungsi sebagai Asisten/Helpdesk. 
- Berjalan menggunakan **NVIDIA NIM API** (Model: `meta/llama-3.2-11b-vision-instruct`).
- Membantu memberikan jawaban teknis cara menggunakan sistem absensi.
- Tidak memiliki batasan dan dapat membantu menjawab semua pertanyaan umum (General Knowledge).
- Tersedia pada halaman "Beranda" (Login) maupun di dalam Dashboard (Public & Protected).

---

## 🛠️ Teknologi yang Digunakan
Sistem ini tidak menggunakan framework full-stack yang berat, melainkan dibangun dari dasar dengan pendekatan yang terstruktur:
- **Arsitektur**: Model-View-Controller (MVC) murni.
- **Backend**: PHP 8+ (Native) dan PDO (PHP Data Objects).
- **Database**: MySQL.
- **Frontend**: HTML5, Vanilla JavaScript, CSS3.
- **UI Framework**: Bootstrap 5 (Responsive, Modern, Profesional).
- **Scanner Library**: [html5-qrcode](https://github.com/mebjas/html5-qrcode) untuk akses kamera.
- **Security**: 
  - Validasi Input secara Client-Side dan Server-Side.
  - Implementasi *Anti-CSRF (Cross-Site Request Forgery) Token*.
  - *Password Hashing* (Bcrypt) untuk seluruh data user.

---

## ⚙️ Panduan Instalasi (Local Development)

1. **Persiapan Environtment**
   - Pastikan Anda sudah menginstal XAMPP (Apache & MySQL).
   - Pastikan PHP memiliki ekstensi `PDO` dan `cURL` aktif.

2. **Kloning / Pindahkan File**
   - Pindahkan seluruh folder project ke dalam `C:\xampp\htdocs\sistemabsensi`.

3. **Database Setup**
   - Buka `http://localhost/phpmyadmin/`.
   - Buat database baru bernama `db_sistemabsensi`.
   - Import file `database/schema.sql` (atau file dump SQL yang tersedia) ke dalam database tersebut.

4. **Konfigurasi Database**
   - Buka file `config/config.php` (buat jika belum ada, copy dari sample).
   - Sesuaikan konfigurasi berikut:
     ```php
     define('BASEURL', 'http://localhost/sistemabsensi/public');
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'db_sistemabsensi');
     ```

5. **Menjalankan Aplikasi**
   - Aktifkan modul **Apache** dan **MySQL** di XAMPP Control Panel.
   - Buka browser dan jalankan: `http://localhost/sistemabsensi/public`

---

## 📸 Penggunaan Scanner Barcode (Kamera)
1. Akses menu **Scan Kehadiran** dari Dashboard masing-masing.
2. Saat muncul *prompt*, klik **Allow** (Izinkan) browser untuk mengakses kamera Anda (Laptop/HP).
3. Arahkan **Barcode / QR Code** ID Anda ke arah kamera.
4. Sistem akan berbunyi dan seketika langsung memproses tanpa perlu menekan tombol submit. Status kehadiran (Hadir/Terlambat/Pulang) otomatis tercatat!

*(Jika tidak ingin menggunakan kamera, cursor juga otomatis berada di kolom Input sehingga Anda dapat langsung menggunakan Scanner Fisik USB).*

### 6. WhatsApp Notification Gateway (Fonnte)
- **Notifikasi Ortu**: Otomatis mengirimkan pesan WhatsApp ke nomor HP orang tua/wali siswa saat presensi berhasil dipindai (berisi nama siswa, waktu absen, dan status kehadiran).
- **Notifikasi Guru**: Mengirimkan konfirmasi kehadiran jam masuk dan jam pulang ke nomor WhatsApp pribadi guru.

---

## 📚 Dokumentasi Proyek Lengkap
Dokumentasi teknis dan perancangan sistem telah disertakan secara lengkap:
- **Product Requirement Document (PRD)**: [`PRD.md`](./PRD.md) & [`PRD_Ringkasan_Lengkap.docx`](./PRD_Ringkasan_Lengkap.docx)
- **UML Use Case Diagram**: [`USE_CASE.md`](./USE_CASE.md) & [`use_case_diagram.html`](./use_case_diagram.html)
- **UML Activity Diagram**: [`ACTIVITY_DIAGRAM.md`](./ACTIVITY_DIAGRAM.md) & [`activity_diagram.html`](./activity_diagram.html)
- **Script Presentasi Video**: [`SCRIPT_VIDEO_PRESENTASI.md`](./SCRIPT_VIDEO_PRESENTASI.md)

---

## 🧪 Pengujian Otomatis & CI/CD Pipeline (GitHub Actions)

Project ini telah menerapkan integrasi berkelanjutan (**Continuous Integration / CI**) menggunakan **GitHub Actions** dan framework pengujian **PHPUnit 10**:

### 1. Cara Menjalankan Pengujian Lokal (Local Unit Test)
Cukup jalankan script batch otomatis di terminal:
```bash
.\test.bat
```
Atau menggunakan PHP CLI secara manual:
```bash
C:\xampp\php\php.exe vendor/bin/phpunit --colors=always --testdox
```

### 2. Cakupan Pengujian Unit (Unit Test Suites)
- **`AttendanceRulesTest`**: Memverifikasi logika bisnis batas waktu (cutoff 07:00 WIB) status `hadir` vs `terlambat`, kalkulasi selisih menit, dan proteksi cooldown 10 menit presensi guru.
- **`CsrfTest`**: Memverifikasi pembuatan token anti-CSRF berkekuatan 64 karakter heksadesimal serta validasi proteksi form POST.
- **`SecurityHelperTest`**: Memverifikasi sanitasi input terhadap serangan XSS (Cross-Site Scripting), escaping tanda petik, dan null safety.
- **`FlasherTest`**: Memverifikasi manajemen flash message session dan rendering komponen alert bootstrap.
- **`ConfigTest`**: Memverifikasi fungsi parsing environment `env()` dan konstanta keamanan sistem tanpa kredensial hardcoded.

### 3. Pipeline Otomatis GitHub Actions (`.github/workflows/ci.yml`)
Setiap kali ada `push` atau `pull request` ke repository GitHub, workflow akan otomatis:
1. Menyiapkan environment Ubuntu dengan PHP 8.2 & ekstensi pendukung.
2. Memvalidasi integritas `composer.json` dan `composer.lock`.
3. Memeriksa sintaks seluruh file PHP aplikasi (*PHP Syntax Lint*).
4. Melakukan caching dependensi Composer untuk mempercepat proses build.
5. Menjalankan seluruh pengujian PHPUnit secara otomatis hingga menghasilkan status **Success (Hijau)**.

---
*Dikembangkan dengan 🩵 untuk Digitalisasi Sekolah SMK NU WARGABINANGUN.*
