# 🗄️ Dokumentasi Visual ERD Basis Data & Prototipe Interaktif UI/UX (Figma)
**SMK NU WARGABINANGUN**  
*Arsitektur Data Relasional MariaDB InnoDB & Simulasi Antarmuka Pengguna Interaktif*

---

## 📌 Ringkasan Eksekutif

Dokumen ini memuat dokumentasi visual dan teknis lengkap untuk dua pilar utama pengembangan sistem informasi presensi berbasis QR Code dan kecerdasan buatan (*AI Chatbot RAG*):
1. **ERD (Entity Relationship Diagram) Basis Data:** Memodelkan struktur database relasional MariaDB InnoDB yang terdiri dari 8 entitas tabel, kunci identitas (*Primary Key*), kunci relasi (*Foreign Key*), aturan integritas referensial, dan kardinalitas hubungan (1:1 dan 1:N).
2. **Prototipe Interaktif UI/UX (Figma Presentation Mode):** Simulasi prototipe antarmuka interaktif siap klik (*clickable prototype*) yang mengimplementasikan 4 alur kerja utama sistem (Login multi-role, Dashboard monitoring, Universal QR Scanner dengan simulasi popup notifikasi WhatsApp, Manajemen Siswa & ID Card Barcode, serta Asisten Cerdas AI Chatbot).

---

## 🖼️ Berkas Visual & Tautan Interaktif

### A. Berkas ERD Basis Data (Entity Relationship Diagram)
| Format Berkas | Deskripsi | Tautan Akses |
| :--- | :--- | :--- |
| **PNG (High Resolution)** | Gambar visual beresolusi 1860 × 1250 px, tajam untuk dokumen laporan | [erd_diagram.png](file:///c:/xampp/htdocs/sistemabsensi/erd_diagram.png) |
| **SVG (Vector Graphic)** | Gambar vektor skala tak terbatas, tajam di semua level pembesaran | [erd_diagram.svg](file:///c:/xampp/htdocs/sistemabsensi/erd_diagram.svg) |
| **HTML Interaktif** | Penampil web interaktif dilengkapi kontrol Pan, Zoom In/Out, Reset, dan Cetak | [erd_diagram.html](file:///c:/xampp/htdocs/sistemabsensi/erd_diagram.html) |

### B. Berkas Prototipe Interaktif UI/UX (Figma Presentation Mode)
| Format Berkas | Deskripsi | Tautan Akses |
| :--- | :--- | :--- |
| **HTML Prototipe Interaktif** | Simulasi aplikasi Figma interaktif yang dapat diklik langsung di browser | [prototipe_figma_interactive.html](file:///c:/xampp/htdocs/sistemabsensi/prototipe_figma_interactive.html) |
| **PNG Tangkapan Layar** | Preview tampilan mode presentasi Figma beresolusi 1380 × 920 px | [prototipe_figma_preview.png](file:///c:/xampp/htdocs/sistemabsensi/prototipe_figma_preview.png) |

---

## 🗄️ Bagian 1: Uraian Mendalam ERD Basis Data

### 1. Struktur 8 Entitas Tabel Utama

```
[users] 1 -------- 1 [guru]  1 -------- N [absensi_guru]
   |                   |
   | (1:1)             | (1:N Wali Kelas)
   |                   v
   |                [kelas] 1 --------- N [jadwal]
   |                   |
   | (1:1)             | (1:N Siswa per Kelas)
   v                   v
[users] 1 -------- 1 [siswa] 1 --------- N [absensi_siswa]
   |
   +-------------- N [chat_history]
```

1. **`users` (Tabel Akun & Hak Akses):**
   - Kolom: `id` (PK), `username` (UQ), `nama`, `password` (Hash Bcrypt), `role` (`admin`, `guru`, `kepala_sekolah`, `wali_kelas`, `siswa`), `is_active`, `created_at`, `updated_at`.
2. **`guru` (Master Data Tenaga Pendidik):**
   - Kolom: `id` (PK), `user_id` (FK $\rightarrow$ `users.id`), `nip` (UQ), `nama`, `barcode_id` (UQ), `no_wa`, `no_hp`, `created_at`, `updated_at`.
3. **`kelas` (Master Data Rombongan Belajar / Rombel):**
   - Kolom: `id` (PK), `nama_kelas` (UQ), `wali_kelas_id` (FK $\rightarrow$ `guru.id`), `created_at`, `updated_at`.
4. **`siswa` (Master Data Peserta Didik):**
   - Kolom: `id` (PK), `user_id` (FK $\rightarrow$ `users.id`), `nis` (UQ), `nama`, `kelas_id` (FK $\rightarrow$ `kelas.id`), `barcode_id` (UQ), `no_wa`, `nama_ortu`, `no_hp_ortu`, `created_at`, `updated_at`.
5. **`absensi_siswa` (Transaksi Presensi Harian Siswa):**
   - Kolom: `id` (PK), `siswa_id` (FK $\rightarrow$ `siswa.id`), `tanggal` (DATE), `jam` (TIME), `status` (`hadir`, `sakit`, `izin`, `alpa`, `terlambat`), `created_at`.
6. **`absensi_guru` (Transaksi Presensi Harian Guru):**
   - Kolom: `id` (PK), `guru_id` (FK $\rightarrow$ `guru.id`), `tanggal` (DATE), `jam_masuk` (TIME), `jam_pulang` (TIME NULL), `status`, `created_at`.
7. **`jadwal` (Jadwal Mengajar Pelajaran Mingguan):**
   - Kolom: `id` (PK), `kelas_id` (FK $\rightarrow$ `kelas.id`), `guru_id` (FK $\rightarrow$ `guru.id`), `mata_pelajaran`, `hari`, `jam_mulai`, `jam_selesai`, `created_at`.
8. **`chat_history` (Log Percakapan Asisten AI):**
   - Kolom: `id` (PK), `user_id` (FK $\rightarrow$ `users.id`), `pertanyaan` (TEXT), `jawaban` (TEXT), `created_at`.

### 2. Aturan Integritas & Kardinalitas (Crow's Foot Notation)
- **1 : 1 (One-to-One):** `users` ke `guru` dan `users` ke `siswa` — Setiap data akun pengguna hanya berpasangan tepat dengan satu entitas fisik profil. Menggunakan `ON DELETE SET NULL` agar rekam jejak riwayat tidak hilang jika akun login dinonaktifkan.
- **1 : N (One-to-Many):**
  - Satu `kelas` menaungi banyak `siswa`.
  - Satu `siswa` memiliki banyak riwayat di `absensi_siswa`.
  - Satu `guru` memiliki banyak riwayat di `absensi_guru`.
  - Satu `users` memiliki banyak riwayat percakapan di `chat_history`.
  - Menggunakan `ON DELETE CASCADE` untuk integritas referensial bersih: jika siswa/guru dihapus, histori absensi terkait otomatis ikut terhapus.

---

## 🎨 Bagian 2: Uraian Prototipe Interaktif UI/UX (Figma)

Prototipe interaktif dirancang untuk menyimulasikan pengalaman penggunaan sistem sebelum atau sesudah tahap kodingan:

1. **Layar 1 — Autentikasi Login Multi-Role:**
   - Menyediakan pemilih peran instan (*Admin*, *Guru*, *Siswa*).
   - Validasi sesi dengan proteksi token keamanan CSRF.
2. **Layar 2 — Dashboard Monitoring Real-Time:**
   - 4 Kartu Metrik KPI: Hadir Tepat Waktu (92.4%), Terlambat (3.5%), Sakit/Izin (2.3%), dan Guru Hadir (100%).
   - Tabel Log Presensi Terkini yang tersinkron langsung dengan basis data MariaDB InnoDB.
   - Status notifikasi WhatsApp gateway (Fonnte) hijau terkirim ganda (*double-check*).
3. **Layar 3 — Universal QR Scanner (Simulasi Kamera & WhatsApp):**
   - Tampilan *viewfinder* webcam dilengkapi animasi garis laser merah dinamis.
   - Tombol pengalih mode: Siswa vs Guru (dengan aturan jeda *cooldown* 10 menit).
   - Tombol *"Simulasikan Scan Barcode"* yang memunculkan **Popup Notifikasi Sukses Presensi** dan mockup pesan WhatsApp resmi yang masuk ke nomor orang tua siswa.
4. **Layar 4 — Manajemen Siswa & ID Card Barcode:**
   - Tabel direktori siswa beserta kode Barcode ID unik (`BC-SISWA-001`, dsb).
   - Fitur klik *"Preview Kartu Pelajar"* yang menampilkan kartu tanda pelajar berdesain resmi siap cetak lengkap dengan barcode dan QR Code.
5. **Widget Melayang — AI Chatbot Live Database Context (RAG):**
   - Widget obrolan cerdas yang dapat diklik dari layar mana saja.
   - Dilengkapi tombol pertanyaan cepat (*"Kehadiran Saya"* dan *"Jadwal Hari Ini"*).
   - Menghasilkan respon faktual berbasis query data riil tanpa halusinasi.

---

## 🎙️ Bagian 3: Naskah / Script Siap Pakai untuk Presentasi / Video

Gunakan panduan naskah berikut saat kamu mempresentasikan **ERD Basis Data** dan **Prototipe Figma**:

```
"Halo semuanya! Pada kesempatan kali ini, saya akan menjelaskan dua komponen penting 
dalam perancangan Sistem Absensi SMK NU WARGABINANGUN, yaitu ERD Basis Data dan Prototipe 
Interaktif UI/UX di Figma.

Pertama, mari kita lihat ERD atau Entity Relationship Diagram.
ERD ini adalah fondasi di balik layar yang menggambarkan bagaimana data disimpan di database 
MariaDB InnoDB. Sistem kami memiliki 8 tabel utama:
1. Tabel users untuk autentikasi login dengan enkripsi password bcrypt.
2. Tabel guru dan siswa sebagai master data pengguna. Hubungannya adalah 1 to 1 dengan tabel users.
3. Tabel kelas dan jadwal untuk mengatur rombongan belajar dan jadwal pelajaran mingguan.
4. Tabel absensi_siswa dan absensi_guru yang memiliki relasi 1 to Many dari data induknya. 
   Di tabel ini tersimpan waktu scan dan status kehadiran dengan aturan cutoff jam 07.00 pagi.
5. Dan tabel chat_history untuk menyimpan log interaksi kecerdasan buatan AI Chatbot.
Semua tabel telah dilengkapi Primary Key, Foreign Key, dan aturan ON DELETE CASCADE untuk menjamin 
integritas data.

Kedua, mari kita lihat Prototipe Interaktif UI/UX Figma.
Jika ERD adalah struktur di balik layar, maka Prototipe Figma adalah simulasi antarmuka di depan layar 
yang bisa dicoba dan diklik langsung oleh pengguna.
Pada prototipe kami:
- Kita memiliki alur Login Multi-Role untuk Admin, Guru, dan Siswa.
- Masuk ke Dashboard Utama, kita disajikan 4 kartu metrik kehadiran real-time dan log absensi harian.
- Pada halaman Universal QR Scanner, kamera secara otomatis membaca barcode kartu pelajar. 
  Begitu scan berhasil, sistem menampilkan notifikasi hijau 'Hadir Tepat Waktu' dan memicu pengiriman 
  WhatsApp otomatis ke nomor orang tua melalui gateway Fonnte.
- Terdapat juga halaman cetak Kartu Pelajar ber-barcode unik.
- Serta widget Asisten Cerdas AI Chatbot di pojok layar yang menggunakan teknologi Live DB Context (RAG) 
  sehingga siswa atau guru dapat bertanya tentang jumlah kehadiran atau jadwal KBM secara instan dan akurat.

Dengan adanya ERD yang rapi dan Prototipe Figma yang interaktif, sistem absensi SMK NU WARGABINANGUN 
dapat dikembangkan secara terstruktur, fungsional, dan ramah pengguna. 
Terima kasih!"
```
