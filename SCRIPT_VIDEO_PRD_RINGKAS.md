# 🎬 Naskah Lengkap Video Presentasi: Product Requirement Document (PRD)
**Berdasarkan Dokumen:** `PRD_Ringkas.docx` (Status: Production Ready v2.1)  
**Sistem:** Sistem Informasi Presensi & Absensi Siswa & Guru Berbasis Web, WhatsApp Gateway & AI  
**Instansi:** SMK NU WARGABINANGUN  
**Estimasi Durasi Video:** 10 – 15 Menit  
**Gaya Penyampaian:** Jelas, percaya diri, lugas, profesional, dan mengalir natural

---

## ⏱️ RUNDOWN & STRUKTUR PRESENTASI PRD

| Bagian | Topik Pembahasan | Estimasi Waktu | Petunjuk Visual di Layar |
| :---: | :--- | :---: | :--- |
| **00:00 – 01:30** | **Pembukaan & Identitas Dokumen PRD** | 1.5 Menit | Wajah presenter & Tampilan Cover Dokumen PRD |
| **01:30 – 03:30** | **Bagian 1: Ringkasan Eksekutif & Target KPI** | 2 Menit | Tampilkan tabel target KPI (Kecepatan, WA, Rekap) |
| **03:30 – 06:00** | **Bagian 2: Aktor Pengguna & Hak Akses (User Personas)** | 2.5 Menit | Tampilkan bagan 5 peran pengguna + Orang Tua Murid |
| **06:00 – 10:30** | **Bagian 3: 5 Fitur Utama & Spesifikasi Fungsional** | 4.5 Menit | Tampilkan poin fitur: Scanner, WA, AI RAG, Auto-Alpha, ID Card |
| **10:30 – 12:30** | **Bagian 4: Arsitektur Teknis & 4 Lapis Keamanan Data** | 2 Menit | Tampilkan diagram stack: PHP 8.2 MVC, MariaDB, Bcrypt, CSRF |
| **12:30 – 14:00** | **Bagian 5: Struktur 9 Entitas Basis Data Relasional** | 1.5 Menit | Tampilkan daftar 9 tabel database InnoDB |
| **14:00 – 15:00** | **Penutup & Kesimpulan Kesiapan Sistem** | 1 Menit | Wajah presenter & Status Production Ready v2.1 |

---

## 🎙️ NASKAH KATA-DEMI-KATA (SPOKEN SCRIPT)

---

### 🟢 MENIT 00:00 – 01:30 | PEMBUKAAN & IDENTITAS DOKUMEN PRD

> **[PETUNJUK VISUAL]:**  
> *Kamera menyorot wajah presenter secara rapi. Di sudut bawah layar tampilkan teks nama kamu dan identitas sekolah. Di samping presenter atau pada layar monitor, tampilkan judul dokumen: "PRODUCT REQUIREMENT DOCUMENT (PRD) — SMK NU WARGABINANGUN".*

**[DIALOG PRESENTER]:**  
"Assalamu’alaikum Warahmatullahi Wabarakatuh.  
Selamat pagi, siang, atau sore kepada Bapak/Ibu penguji, guru pembimbing, serta rekan-rekan sekalian.

Perkenalkan, nama saya **[Sebutkan Nama Lengkap Kamu]**, siswa dari kompetensi keahlian Rekayasa Perangkat Lunak di SMK NU WARGABINANGUN.

Pada video kali ini, saya akan mempresentasikan secara mendalam dokumen **Product Requirement Document (PRD)** dari proyek perangkat lunak yang saya kembangkan, yaitu:  
**'Sistem Absensi Digital Terintegrasi Web, WhatsApp Gateway & Asisten Cerdas AI di SMK NU WARGABINANGUN'**.

Dokumen PRD ini berstatus *Production Ready Versi 2.1* dan menjadi acuan utama perancangan sistem, mulai dari target indikator kinerja (KPI), hak akses pengguna, spesifikasi fungsional fitur, arsitektur keamanan, hingga struktur basis data relasional.

Mari kita masuk ke bagian yang pertama, yaitu Ringkasan Eksekutif dan Target KPI."

---

### 🟢 MENIT 01:30 – 03:30 | BAGIAN 1: RINGKASAN EKSEKUTIF & TARGET KPI

> **[PETUNJUK VISUAL]:**  
> *Tampilkan slide Bab 1 PRD. Sorot tabel 3 indikator Target KPI: Kecepatan Scan, Notifikasi WhatsApp, dan Akurasi Rekap Bulanan.*

**[DIALOG PRESENTER]:**  
"Kita mulai dari **Ringkasan Eksekutif**.  
Sistem Informasi Presensi dan Absensi SMK NU WARGABINANGUN dibangun sebagai solusi digital modern untuk menggantikan sistem absensi manual berbasis kertas yang selama ini digunakan. Sistem ini mengotomatisasi pencatatan kehadiran menggunakan kamera pemindai QR Code dan Barcode, mengirimkan notifikasi instan secara otomatis ke WhatsApp orang tua via API Fonnte, serta menyediakan asisten cerdas AI Chatbot yang terhubung langsung dengan database sekolah.

Untuk mengukur keberhasilan implementasi produk ini, kami menetapkan **3 Target KPI Utama**:

1. **Target Pertama — Kecepatan Scan per Orang:**  
   Target kami adalah **kurang dari 1 detik** per siswa atau guru. Dampak langsungnya adalah meniadakan antrean panjang di gerbang sekolah saat jam masuk pagi hari. Siswa cukup menunjukkan kartu, dan dalam sepersekian detik sistem langsung mengenali identitasnya.
   
2. **Target Kedua — Waktu Pengiriman Notifikasi WhatsApp:**  
   Target kami adalah **kurang dari 5 detik** setelah scan berhasil. Dampak nyatanya adalah orang tua di rumah segera mendapatkan kepastian bahwa putra-putrinya telah sampai di lingkungan sekolah dengan selamat dan tepat waktu.
   
3. **Target Ketiga — Akurasi Rekapitulasi Kehadiran:**  
   Target kami adalah **99,9% akurat dalam 1 kali klik**. Sistem ini mengeliminasi kesalahan manusia (*human error*) saat menghitung absensi, serta menghemat waktu wali kelas dan guru piket yang biasanya memakan waktu berhari-hari pada setiap akhir bulan.

Ketiga target KPI ini menjadi tolok ukur bahwa sistem ini dirancang dengan standar efisiensi yang sangat tinggi."

---

### 🟢 MENIT 03:30 – 06:00 | BAGIAN 2: AKTOR PENGGUNA & HAK AKSES (USER PERSONAS)

> **[PETUNJUK VISUAL]:**  
> *Tampilkan slide Bab 2 PRD mengenai User Personas. Sorot bagan 5 peran pengguna di sekolah dan 1 peran penerima manfaat eksternal.*

**[DIALOG PRESENTER]:**  
"Selanjutnya pada Bab kedua PRD, kita memetakan **Aktor Pengguna dan Hak Akses (User Personas)**. Sistem ini dirancang untuk melayani 5 kelompok pengguna internal dan 1 kelompok penerima manfaat eksternal:

1. **Administrator (Superuser):**  
   Memiliki kendali penuh atas sistem. Admin bertugas mengelola master data siswa beserta kontak orang tua, data dewan guru, rombongan belajar kelas, jadwal mata pelajaran, manajemen akun login, konfigurasi API AI, serta mencetak kartu barcode secara massal.

2. **Dewan Guru:**  
   Guru dapat melakukan presensi mandiri (baik jam masuk maupun jam kepulangan) melalui kamera scanner gerbang atau dashboard guru, memperbarui nomor WhatsApp pribadinya secara mandiri, serta melihat jadwal mengajar mingguan dan riwayat absensinya.

3. **Siswa:**  
   Siswa memiliki akses ke kartu QR pribadi digital yang dapat diunduh langsung ke galeri ponsel mereka, dapat mengecek kebenaran nomor kontak orang tua yang terdaftar, melihat jadwal pelajaran kelas, dan memantau riwayat kehadiran pribadinya.

4. **Wali Kelas:**  
   Memiliki hak akses khusus untuk memantau kehadiran siswa di rombel binaannya secara terfokus serta mengunduh rekapitulasi absensi kelasnya.

5. **Kepala Sekolah:**  
   Memiliki akses ke dashboard eksekutif untuk memantau grafik statistik dan persentase kehadiran seluruh sekolah secara transparan tanpa hak mengubah data master.

6. **Orang Tua / Wali Murid (Penerima Manfaat Eksternal):**  
   Orang tua tidak perlu menginstal aplikasi tambahan yang memberatkan ponsel. Mereka bertindak sebagai penerima manfaat langsung yang secara otomatis menerima pesan WhatsApp resmi setiap kali anaknya berhasil melakukan absensi di sekolah, lengkap memuat nama, kelas, jam, dan status kehadirannya."

---

### 🟢 MENIT 06:00 – 10:30 | BAGIAN 3: 5 FITUR UTAMA & SPESIFIKASI FUNGSIONAL

> **[PETUNJUK VISUAL]:**  
> *Tampilkan slide Bab 3 PRD. Bahas satu per satu dari 5 fitur unggulan: Universal Live Scanner, Fonnte WA, AI Chatbot RAG, Auto-Data Alpha, dan Cetak Kartu Massal.*

**[DIALOG PRESENTER]:**  
"Sekarang kita masuk ke bagian inti dari PRD, yaitu **5 Fitur Utama & Spesifikasi Fungsional Sistem**:

**Fitur 3.1: Universal Live Scanner (Kamera & Barcode Laser)**  
Sistem ini menggunakan satu antarmuka scanner universal yang sangat cerdas. Kamera dapat mendeteksi barcode siswa maupun guru secara otomatis tanpa perlu berpindah-pindah menu. Fitur ini fleksibel karena mendukung webcam laptop, kamera smartphone petugas, hingga barcode scanner laser USB eksternal. Begitu barcode terbaca, sistem langsung memberikan umpan balik visual pop-up di layar dan bunyi nada audio *beep* konfirmasi.

**Fitur 3.2: Integrasi Notifikasi WhatsApp Real-Time via Fonnte API**  
Sistem terhubung langsung ke WhatsApp Gateway resmi Fonnte:
- Ketika siswa memindai kartu: Pesan WhatsApp otomatis terkirim detik itu juga ke nomor HP orang tua, memuat salam pembuka, nama siswa, NIS, rombel kelas, waktu presensi, serta status apakah 'Hadir Tepat Waktu' atau 'Terlambat'.
- Ketika guru memindai kartu: Pesan konfirmasi WhatsApp terkirim ke ponsel pribadi guru, baik saat scan jam masuk maupun jam kepulangan.

**Fitur 3.3: Asisten Cerdas AI Chatbot (Live Database Context RAG)**  
Di pojok aplikasi, terdapat floating widget asisten cerdas. Sistem ini memanfaatkan integrasi multi-provider LLM API seperti NVIDIA NIM, Google Gemini, OpenAI, maupun DeepSeek. Yang membedakan AI ini dengan chatbot biasa adalah penerapan teknologi **Retrieval-Augmented Generation (RAG)**: sistem kami secara otomatis menginjeksi data riil dari database sekolah—seperti profil pengguna, rekapitulasi kehadiran bulan berjalan, dan jadwal pelajaran hari ini—langsung ke dalam prompt AI. Hasilnya, AI mampu menjawab pertanyaan pengguna secara faktual, akurat, dan bebas dari halusinasi data!

**Fitur 3.4: Otomatisasi Status Alpha (Auto-Data Alpha)**  
Sistem ditanamkan aturan otomatisasi pada batas toleransi waktu, yaitu pukul **07:00:00 WIB**. Pada jam tersebut, sistem secara otomatis mendata seluruh siswa dan guru yang belum hadir dengan status awal 'Alpha'. Namun jika siswa atau guru tersebut datang terlambat dan memindai kartunya di gerbang, sistem secara cerdas memperbarui status Alpha tersebut menjadi 'Terlambat' beserta jam kedatangan aslinya.

**Fitur 3.5: Generator & Cetak Kartu Barcode/QR Massal**  
Sistem secara otomatis mengenerate format kode unik berstandar: `QR-S-NIS-xxx` untuk siswa dan `QR-G-NIP-xxx` untuk dewan guru. Dilengkapi fitur pratinjau dan tombol cetak fisik satu per satu maupun cetak massal satu kelas sekaligus yang siap diprint ke kartu PVC berstandar ID card."

---

### 🟢 MENIT 10:30 – 12:30 | BAGIAN 4: ARSITEKTUR TEKNIS & KEAMANAN DATA

> **[PETUNJUK VISUAL]:**  
> *Tampilkan slide Bab 4 PRD: Tabel Arsitektur Backend, Database, Frontend, dan 4 Lapis Keamanan Data.*

**[DIALOG PRESENTER]:**  
"Pada Bab keempat PRD, kita menguraikan **Arsitektur Teknis dan Keamanan Data**:

1. **Arsitektur Backend:**  
   Dibangun menggunakan **PHP 8.2 Native dengan pola MVC (Model-View-Controller)**. Kami sengaja memilih arsitektur native tanpa framework berat agar performa sistem sangat ringan, waktu pemrosesan ultra-cepat, dan konsumsi memori server sangat efisien.
   
2. **Database Engine:**  
   Menggunakan **MariaDB / MySQL 10.4+ dengan engine InnoDB**. InnoDB dipilih karena menjamin integritas data bertaraf ACID (*Atomicity, Consistency, Isolation, Durability*), mendukung *foreign key relational constraints*, dan menggunakan charset `utf8mb4` untuk mendukung karakter modern.

3. **Frontend & Antarmuka:**  
   Menggunakan HTML5, CSS3, framework **Bootstrap 5.3**, Bootstrap Icons, grafik statistik interaktif dengan **Chart.js**, serta pemrosesan kamera berbasis **Html5-Qrcode**.

4. **Keamanan Data (Security Stack):**  
   Sistem menerapkan 4 lapis perlindungan:
   - **Enkripsi Kata Sandi:** Menggunakan algoritma hash Bcrypt standar industri.
   - **Token CSRF:** Diterapkan di seluruh form dan request AJAX mutasi untuk mencegah serangan *Cross-Site Request Forgery*.
   - **PDO Prepared Statements:** Seluruh kueri ke database menggunakan parameter PDO terikat, sehingga 100% kebal dari ancaman *SQL Injection*.
   - **Sanitasi XSS:** Seluruh data masukan disaring menggunakan fungsi sanitasi htmlspecialchars untuk mencegah *Cross-Site Scripting*.

5. **Integrasi Eksternal:**  
   Menggunakan koneksi HTTPS cURL dengan konfigurasi *timeout fail-safe* 8 hingga 10 detik pada Fonnte WhatsApp API dan Cloud LLM API, sehingga jika koneksi internet gateway pihak ketiga mengalami kendala, proses absensi lokal di sekolah tetap berjalan lancar tanpa mengalami *hang* atau macet."

---

### 🟢 MENIT 12:30 – 14:00 | BAGIAN 5: STRUKTUR 9 ENTITAS BASIS DATA UTAMA

> **[PETUNJUK VISUAL]:**  
> *Tampilkan slide Bab 5 PRD mengenai 9 Tabel Relasional Database.*

**[DIALOG PRESENTER]:**  
"Pada Bab kelima PRD, sistem menyimpan seluruh data secara terstruktur pada **9 Tabel Relasional Utama**:

1. **`users`:** Menyimpan data kredensial login (username, hash password Bcrypt, role otorisasi, dan status aktif).
2. **`siswa`:** Menyimpan data master siswa (NIS, nama lengkap, kelas, barcode ID unik, nomor WA siswa, nama orang tua, dan nomor WA orang tua).
3. **`guru`:** Menyimpan master data tenaga pendidik (NIP, nama lengkap beserta gelar, barcode ID pegawai, dan nomor kontak WA).
4. **`kelas`:** Menyimpan data rombongan belajar beserta Foreign Key `wali_kelas_id` yang merujuk ke tabel guru.
5. **`jadwal`:** Memetakan jadwal pelajaran mingguan berdasarkan hari, kelas, jam KBM, dan guru pengampu.
6. **`absensi_siswa`:** Mencatat log presensi harian siswa (tanggal, jam, dan status kehadiran: Hadir, Sakit, Izin, atau Alpha).
7. **`absensi_guru`:** Mencatat log presensi harian guru (tanggal, jam masuk, jam pulang, dan status).
8. **`ai_settings`:** Menyimpan konfigurasi API Key, penyedia provider aktif (NVIDIA, Gemini, atau OpenAI), dan model LLM yang digunakan.
9. **`chat_history`:** Menyimpan seluruh riwayat log percakapan tanya-jawab antara pengguna dengan asisten AI.

Struktur sembilan tabel ini saling terhubung secara relasional menggunakan aturan *Foreign Key ON DELETE CASCADE*, memastikan kebersihan dan konsistensi data tetap terjaga."

---

### 🟢 MENIT 14:00 – 15:00 | PENUTUP & KESIMPULAN KESIAPAN SISTEM

> **[PETUNJUK VISUAL]:**  
> *Kamera kembali menyorot wajah presenter secara penuh. Tampilkan status dokumen: "Status: Production Ready v2.1 • SMK NU WARGABINANGUN".*

**[DIALOG PRESENTER]:**  
"Bapak/Ibu penguji dan hadirin sekalian,

Sebagai kesimpulan, dokumen Product Requirement Document ini menegaskan bahwa Sistem Presensi SMK NU WARGABINANGUN telah dirancang secara matang dan komprehensif. Dokumen ini telah disetujui dengan status **Production Ready Versi 2.1**.

Dengan perpaduan pemindaian cepat di bawah 1 detik, notifikasi transparan ke orang tua melalui WhatsApp, otomatisasi data alpha jam 7 pagi, serta kecerdasan buatan AI Chatbot RAG, sistem ini siap mentransformasi presensi sekolah menjadi lebih modern, disiplin, dan efisien.

Demikian presentasi Product Requirement Document ini saya sampaikan. Terima kasih atas perhatian Bapak dan Ibu sekalian.

Wabillahi taufiq wal hidayah,  
Wassalamu’alaikum Warahmatullahi Wabarakatuh."

---

### 💡 PANDUAN PRAKTIS SAAT MEREKAM:
1. **Buka Dokumen PRD di Sebelah Layar Kamera:** Kamu bisa membuka file `PRD_Ringkas.docx` atau slide presentasi di layar laptop kamu sambil membaca naskah ini.
2. **Gunakan Intonasi yang Tegas:** Tekankan angka-angka penting seperti *"kurang dari 1 detik"*, *"jam 07:00:00 WIB"*, dan *"99,9% akurat"*.
3. **Posisikan Pandangan:** Sesekali lihat ke arah kamera webcam agar terkesan berbicara langsung dengan penguji.
