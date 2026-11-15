# 🎬 Naskah Lengkap Video Presentasi (Durasi Maksimal 24 Menit)
**Proyek Rekayasa Perangkat Lunak (RPL): Sistem Informasi Presensi & Absensi QR Code Berbasis Web dengan Integrasi WhatsApp Gateway & AI Chatbot RAG**  
**Instansi:** SMK NU WARGABINANGUN  
**Estimasi Total Durasi:** 22 – 24 Menit  
**Kecepatan Bicara Standar:** 120 – 130 kata per menit (Tenang, jelas, tegas, dan profesional)

---

## ⏱️ TABEL TIMELINE & RUNDOWN PRESENTASI

| Bagian | Topik Pembahasan | Estimasi Waktu | Petunjuk Layar / Visual |
| :--- | :--- | :--- | :--- |
| **00:00 - 02:00** | **Pembukaan & Pengenalan Proyek** | 2 Menit | Wajah presenter (kamera depan) & Slide Judul Utama |
| **02:00 - 05:30** | **Bagian 1: Product Requirement Document (PRD)** | 3.5 Menit | Slide PRD, tabel fitur, dan arsitektur kebutuhan |
| **05:30 - 15:30** | **Bagian 2: Perancangan UML, Basis Data & UI/UX** | 10 Menit | Layar penuh menampilkan diagram-diagram teknis: |
| *05:30 - 07:00* | • 2.1 Use Case Diagram (Aktor & Batasan Sistem) | 1.5 Menit | File `use_case_diagram.html` / visual diagram |
| *07:00 - 09:00* | • 2.2 Activity Diagram (Alur Presensi & AI) | 2 Menit | File `activity_diagram_swimlane.png` |
| *09:00 - 10:45* | • 2.3 Class Diagram (Arsitektur MVC) | 1.75 Menit | File `class_diagram.png` |
| *10:45 - 12:30* | • 2.4 Sequence Diagram (Alur Runtime & Gateway) | 1.75 Menit | File `sequence_diagram.png` |
| *12:30 - 14:15* | • 2.5 ERD Basis Data (MariaDB InnoDB 8 Entitas) | 1.75 Menit | File `erd_diagram.png` |
| *14:15 - 15:30* | • 2.6 Prototipe Interaktif UI/UX Figma | 1.25 Menit | File `prototipe_figma_interactive.html` |
| **15:30 - 22:30** | **Bagian 3: Live Demo Aplikasi Nyata** | 7 Menit | Perekaman layar aplikasi web berjalan (`localhost/sistemabsensi`): |
| *15:30 - 16:45* | • 3.1 Autentikasi Login & Dashboard Admin | 1.15 Menit | Layar browser form login & widget ringkasan statistik |
| *16:45 - 19:00* | • 3.2 Demo Pemindaian QR Kamera & WhatsApp | 2.15 Menit | Layar scanner kamera, scan kartu, popup & pesan WA |
| *19:00 - 20:15* | • 3.3 Demo Manajemen Siswa & Cetak Kartu QR | 1.15 Menit | Tabel data siswa, filter kelas, dan cetak kartu |
| *20:15 - 21:30* | • 3.4 Demo Asisten Cerdas AI Chatbot (RAG) | 1.15 Menit | Floating widget chat AI, ajukan pertanyaan data live |
| *21:30 - 22:30* | • 3.5 Demo Rekap Laporan Absensi Excel/PDF | 1 Menit | Halaman filter tanggal, ekspor rekapan kehadiran |
| **22:30 - 24:00** | **Penutup, Evaluasi & Rencana Lanjutan** | 1.5 Menit | Wajah presenter & Slide penutup / kontak |

---

## 🎙️ NASKAH DETAIL KATA-DEMI-KATA (SPOKEN SCRIPT)

---

### 🟢 MENIT 00:00 – 02:00 | PEMBUKAAN & PENGENALAN PROYEK

> **[PETUNJUK VISUAL]:**  
> *Kamera mengarah ke Presenter (tengah layar) dengan pencahayaan rapi. Pada detik ke-20, munculkan title card di pojok bawah: "Nama Kamu — Pengembang Sistem Absensi SMK NU WARGABINANGUN". Kemudian transisi split screen antara wajah presenter dan slide judul proyek.*

**[DIALOG PRESENTER]:**  
"Assalamu’alaikum Warahmatullahi Wabarakatuh. Selamat pagi/siang kepada Bapak/Ibu penguji, guru pembimbing, serta rekan-rekan sekalian.

Perkenalkan, nama saya **[Sebutkan Nama Kamu]**, dari kompetensi keahlian Rekayasa Perangkat Lunak. 

Pada kesempatan hari ini, saya dengan bangga mempresentasikan karya proyek akhir perangkat lunak yang telah saya rancang dan bangun, yaitu: **'Sistem Informasi Presensi & Absensi Siswa-Guru Berbasis Web Menggunakan Universal QR Code, Fonnte WhatsApp Gateway, dan Asisten Cerdas AI Chatbot RAG di SMK NU WARGABINANGUN'**.

Dalam video berdurasi sekitar 24 menit ini, saya akan membedah proyek ini secara menyeluruh ke dalam tiga pilar utama:
1. **Product Requirement Document (PRD)** — latar belakang masalah, tujuan, serta spesifikasi kebutuhan sistem.
2. **Perancangan Teknis Lengkap UML, Basis Data & UI/UX** — mulai dari Use Case, Activity Diagram swimlane alur bisnis dan AI, Class Diagram MVC, Sequence Diagram runtime, ERD MariaDB InnoDB, hingga Prototipe Interaktif UI/UX Figma.
3. **Live Demo Aplikasi Secara Langsung** — pengujian operasional fitur pemindaian webcam, pembuktian pengiriman pesan WhatsApp otomatis ke ponsel orang tua, serta interaksi asisten AI yang terhubung langsung dengan basis data nyata.

Tanpa berlama-lama, mari kita masuk ke bagian yang pertama: Product Requirement Document."

---

### 🟢 MENIT 02:00 – 05:30 | BAGIAN 1: PRODUCT REQUIREMENT DOCUMENT (PRD)

> **[PETUNJUK VISUAL]:**  
> *Tampilkan slide dokumen PRD. Sorot poin-poin permasalahan nyata di sekolah: kertas absensi basah/hilang, orang tua yang cemas di rumah, serta rekapitulasi data yang memakan waktu berhari-hari.*

**[DIALOG PRESENTER]:**  
"Mari kita mulai dari **Latar Belakang Masalah**.
Di SMK NU WARGABINANGUN, proses pencatatan kehadiran sebelumnya masih menggunakan lembaran kertas presensi manual. Metode konvensional ini menimbulkan sejumlah kendala krusial:
- **Pertama, rawan kecurangan dan ketidakteraturan waktu.** Siswa dapat dengan mudah menitip absen pada temannya, dan jam kedatangan aktual sering kali tidak tercatat secara presisi.
- **Kedua, kesenjangan komunikasi dengan pihak orang tua.** Banyak orang tua di rumah yang tidak mengetahui apakah putra-putrinya sudah sampai di gerbang sekolah dengan selamat atau justru membolos di luar.
- **Ketiga, inefisiensi administrasi.** Wali kelas dan pihak kurikulum membutuhkan waktu berhari-hari pada setiap akhir bulan hanya untuk merekap ribuan baris data kertas ke dalam format laporan.

Oleh karena itu, kami menyusun **Product Requirement Document (PRD)** sebagai acuan teknis pengembangan sistem digital yang modern, otomatis, dan transparan.

**Tujuan Utama Pengembangan Sistem Ini Ada Tiga:**
1. Mempercepat proses presensi harian di gerbang sekolah melalui pemindaian kartu barcode/QR Code berkecepatan di bawah 1 detik per orang.
2. Menerapkan transparansi penuh dengan mengirimkan notifikasi instan WhatsApp ke ponsel wali murid saat itu juga melalui integrasi API Gateway Fonnte.
3. Menyediakan asisten kecerdasan buatan berbasis *Retrieval-Augmented Generation (RAG)* yang mampu menjawab pertanyaan murid, guru, atau pimpinan terkait kehadiran dan jadwal KBM secara faktual langsung dari basis data.

**Kebutuhan Fungsional Utama (Functional Requirements):**
- **Universal Scanner:** Kamera peramban mampu mengenali barcode kartu siswa maupun kartu pegawai guru secara otomatis.
- **Aturan Cutoff Waktu:** Sistem secara otomatis membandingkan jam pemindaian server dengan batas toleransi tepat waktu, yaitu pukul **07:00:00 WIB**. Jika scan dilakukan pada pukul 07:00:01 WIB ke atas, status otomatis tercatat 'Terlambat'.
- **Jeda Cooldown 10 Menit untuk Guru:** Mencegah guru melakukan scan ganda kepulangan sebelum waktu jeda minimum terpenuhi.
- **Manajemen Master Data:** Pengelolaan siswa, guru, rombongan belajar kelas, jadwal mata pelajaran, dan cetak kartu ID fisik.
- **Ekspor Laporan:** Rekapitulasi absensi otomatis ke format Microsoft Excel dan dokumen PDF siap cetak.

**Kebutuhan Non-Fungsional (Non-Functional Requirements):**
- **Keamanan:** Proteksi terhadap serangan CSRF melalui token dinamis, sanitasi parameter PDO untuk mencegah SQL Injection, dan pengacakan password akun menggunakan algoritma Bcrypt bawaan PHP.
- **Performa:** Responsivitas kueri di bawah 200 milidetik dan antarmuka web yang sepenuhnya responsif diakses dari laptop, PC admin, maupun tablet/ponsel petugas gerbang.

Setelah kebutuhan produk terdefinisi dengan jelas, langkah berikutnya adalah memodelkan arsitektur perangkat lunak menggunakan UML dan perancangan basis data."

---

### 🟢 MENIT 05:30 – 15:30 | BAGIAN 2: PERANCANGAN UML, BASIS DATA & UI/UX

---

#### 🔹 2.1 Use Case Diagram (Menit 05:30 – 07:00)

> **[PETUNJUK VISUAL]:**  
> *Buka berkas `use_case_diagram.html` atau tampilkan diagram Use Case di layar. Arahkan kursor ke masing-masing aktor di sisi kiri dan kanan sistem.*

**[DIALOG PRESENTER]:**  
"Diagram pertama yang kita bahas adalah **Use Case Diagram**. Diagram ini memetakan batasan sistem serta siapa saja aktor yang berinteraksi dengan aplikasi.

Sistem kami memiliki **5 Aktor Manusia** dan **2 Aktor Sistem Eksternal**:
1. **Administrator:** Memiliki hak akses tertinggi (*super user*) untuk mengelola master data siswa, guru, akun pengguna, konfigurasi gateway, dan mencetak laporan keseluruhan.
2. **Guru:** Dapat melakukan presensi masuk dan pulang, memantau riwayat kehadirannya, serta melihat jadwal mengajar mingguan.
3. **Siswa:** Pengguna presensi harian yang juga dapat login untuk melihat rekapan kehadiran pribadinya.
4. **Wali Kelas:** Memiliki wewenang khusus untuk memantau rekap absensi satu rombongan belajar yang dipimpinnya.
5. **Kepala Sekolah:** Memiliki hak akses pemantauan dashboard eksekutif untuk melihat persentase kehadiran harian dan bulanan tanpa hak mengubah data master.

Di sisi eksternal, terdapat:
- **Fonnte WA Gateway:** Sistem pihak ketiga yang dipicu oleh use case *Kirim Notifikasi WhatsApp*.
- **Cloud LLM API:** Mesin pemroses bahasa alami yang dipicu oleh use case *Konsultasi AI Chatbot*.

Relasi antar-use case dirancang modular, di mana use case *Scan QR Barcode* memiliki relasi *<<include>>* ke use case *Validasi Aturan Cutoff Jam 07:00* dan *<<include>>* ke *Kirim Notifikasi WhatsApp*."

---

#### 🔹 2.2 Activity Diagram: Alur Bisnis Presensi & Alur AI (Menit 07:00 – 09:00)

> **[PETUNJUK VISUAL]:**  
> *Buka berkas `activity_diagram_swimlane.png`. Zoom in pada swimlane 4 kolom: Siswa/Guru, Scanner Kamera, Sistem Absensi, dan WhatsApp Gateway.*

**[DIALOG PRESENTER]:**  
"Selanjutnya, mari kita amati **Activity Diagram**. Kami membaginya ke dalam format *Swimlane* agar pembagian tanggung jawab tiap komponen terlihat tegas.

**Pertama, Alur Bisnis Presensi Harian:**
1. Siswa atau Guru memulai aktivitas dengan mengarahkan kartu barcode fisik ke lensa webcam.
2. Pada swimlane *Scanner Kamera*, pustaka `Html5-Qrcode` menangkap aliran frame video secara real-time dan mendekode string barcode ID.
3. Data dikirim melalui permintaan asinkron ke sisi *Sistem Absensi (Backend Controller)*.
4. Sistem memvalidasi token CSRF dan melakukan pencarian ke database.
5. Di sini terjadi *Decision Node* pertama: Apakah barcode terdaftar sebagai Siswa atau Guru?
   - Jika **Siswa**: Sistem menguji jam server terhadap cutoff `07:00:00 WIB`. Jika lebih awal, status diset 'Hadir', jika lewat, diset 'Terlambat'. Data disimpan ke tabel `absensi_siswa`.
   - Jika **Guru**: Sistem memeriksa apakah ini scan pertama (masuk) atau scan kedua (pulang). Pada scan pulang, sistem menerapkan validasi jeda minimum 10 menit agar tidak terjadi *double scan*.
6. Setelah data tersimpan, Sistem membuat panggilan asinkron ke swimlane *WhatsApp Gateway*. Fonnte secara instan mengirim pesan teks resmi berkop SMK NU WARGABINANGUN ke ponsel orang tua atau guru.
7. Alur berakhir pada antarmuka kamera yang membunyikan nada audio *beep* konfirmasi dan memunculkan pop-up modal hijau di layar gerbang.

**Kedua, Alur Aktivitas Asisten Cerdas AI Chatbot:**
Pengguna mengetik pesan pada floating widget $\rightarrow$ ChatbotController membaca sesi akun $\rightarrow$ sistem mengeksekusi kueri SQL live ke MariaDB untuk mengambil statistik kehadiran bulan berjalan $\rightarrow$ AiService menyusun System Prompt Augmented yang menyatukan aturan sekolah dengan data live database $\rightarrow$ Cloud LLM menghasilkan teks inferensi $\rightarrow$ balasan dikirimkan kembali dan dirender dengan animasi mengetik interaktif."

---

#### 🔹 2.3 Class Diagram: Arsitektur MVC (Menit 09:00 – 10:45)

> **[PETUNJUK VISUAL]:**  
> *Buka berkas `class_diagram.png`. Sorot pembagian 3 layer: Controllers di atas, Models di tengah, dan Database/Services di kanan/bawah.*

**[DIALOG PRESENTER]:**  
"Sekarang kita beralih ke **Class Diagram**. Diagram ini memetakan struktur kode program berbasis pola desain **MVC (Model-View-Controller)** pada PHP Native 8.2.

Arsitektur aplikasi terbagi menjadi tiga lapisan utama:
1. **Lapisan Controller:**
   - `AbsensiController`: Menangani logika inti presensi, verifikasi barcode ID, perhitungan cutoff waktu, dan pemicu cURL WhatsApp.
   - `ChatbotController`: Bertanggung jawab atas autentikasi obrolan, pengumpulan data faktual live, dan orkestrasi prompt AI.
   - `AdminBarcodeController`, `GuruController`, dan `SiswaController`: Menangani operasi CRUD master data.
   - `AuthController`: Mengatur alur login multi-role, verifikasi hash Bcrypt, dan pembersihan sesi saat logout.
2. **Lapisan Model:**
   - `AbsensiModel`, `SiswaModel`, `GuruModel`, `KelasModel`, dan `UserModel`. Seluruh model mengimplementasikan abstraksi PDO (*PHP Data Objects*) dengan *prepared statements* untuk menjamin keamanan dari celah SQL Injection.
3. **Lapisan Core & Services:**
   - `Database`: Menerapkan pola *Singleton* untuk mengelola satu koneksi efisien ke server MySQL/MariaDB.
   - `AiService`: Modul klien REST API yang berkomunikasi dengan mesin Cloud LLM (OpenAI/Gemini/NVIDIA NIM) dengan dukungan timeout dan penanganan error yang tangguh.
   - `Csrf`: Kelas helper statis penghasil dan pemverifikasi token kriptografi di setiap transaksi formulir.

Relasi antar-kelas mencerminkan prinsip *Object-Oriented Programming (OOP)* modern, seperti asosiasi controller terhadap model, serta dependensi service terhadap konfigurasi sistem."

---

#### 🔹 2.4 Sequence Diagram: Alur Komunikasi Runtime (Menit 10:45 – 12:30)

> **[PETUNJUK VISUAL]:**  
> *Buka berkas `sequence_diagram.png`. Jelaskan runutan garis pesan horizontal dari kiri ke kanan yang membedakan panah solid (Synchronous), putus-putus (Return), dan panah ungu (Asynchronous cURL).*

**[DIALOG PRESENTER]:**  
"Berikutnya adalah **Sequence Diagram**. Diagram ini menunjukkan kronologi pesan antar-objek berdasarkan urutan waktu (*time-ordered sequence*).

Kami memodelkan dua skenario runtime krusial:
- **Skenario 1: Presensi Universal QR & WhatsApp Gateway (19 Langkah Rinci):**
  Perhatikan fragment `alt` pada diagram. Ketika `AbsensiController` menerima request AJAX dari modul kamera:
  - Pada cabang pertama `[alt: Data Siswa]`, controller membandingkan timestamp server dengan cutoff 07:00:00 WIB, melakukan INSERT ke `absensi_siswa`, lalu memicu panggilan asinkron cURL POST ke endpoint `https://api.fonnte.com/send`. Gateway mengirim notifikasi ke ponsel orang tua, dan peramban menampilkan umpan balik sukses tanpa memblokir antrean siswa di belakangnya.
  - Pada cabang kedua `[else: Data Guru]`, sistem memeriksa riwayat jam masuk hari ini dan menguji toleransi jeda 10 menit sebelum memperbarui `jam_pulang` di tabel `absensi_guru`, dilanjutkan pengiriman konfirmasi ke WhatsApp pribadi dewan guru.
- **Skenario 2: Konsultasi AI Chatbot Live Database Context (14 Langkah Rinci):**
  Menggambarkan alur Retrieval-Augmented Generation (RAG). Pertanyaan pengguna tidak langsung dilempar mentah-mentah ke AI. `ChatbotController` terlebih dahulu menginterogasi `MariaDB Live DB` untuk menarik data faktual pengguna (misalnya: 'Hadir=18 kali, Terlambat=2 kali, Jadwal: Pemrograman Web jam 08.00'). Data riil ini diinjeksikan ke dalam *System Prompt* oleh `AiService`, sehingga jawaban yang dikembalikan Cloud LLM 100% akurat sesuai basis data sekolah dan bebas halusinasi."

---

#### 🔹 2.5 ERD Basis Data: Skema MariaDB InnoDB (Menit 12:30 – 14:15)

> **[PETUNJUK VISUAL]:**  
> *Buka berkas `erd_diagram.png`. Tunjukkan 8 tabel dengan kode warna berbeda, badge PK (biru), FK (oranye), UQ (hijau), dan garis relasi Crow's Foot.*

**[DIALOG PRESENTER]:**  
"Kini kita beralih ke fondasi penyimpanan data di balik layar, yaitu **Entity Relationship Diagram (ERD) Basis Data**.

Database kami diberi nama `db_sistemabsensi` dengan mesin penyimpanan **MariaDB InnoDB** yang sepenuhnya mendukung standar *ACID (Atomicity, Consistency, Isolation, Durability)*. Terdapat **8 Entitas Tabel Utama**:
1. **`users`:** Menyimpan kredensial autentikasi seluruh pengguna (`id`, `username`, `password`, `role`).
2. **`guru` & `siswa`:** Berelasi **1 : 1** dengan tabel `users` melalui kolom `user_id`. Pada tabel siswa, tersimpan kolom kunci `barcode_id` unik dan `no_hp_ortu` sebagai sasaran WhatsApp Gateway.
3. **`kelas` & `jadwal`:** Tabel kelas menaungi siswa dengan relasi **1 : N (One-to-Many)**, di mana satu kelas menampung puluhan siswa. Kolom `wali_kelas_id` merujuk balik ke `guru.id`. Tabel jadwal memetakan hari, jam KBM, kelas, dan guru pengajar.
4. **`absensi_siswa` & `absensi_guru`:** Tabel transaksi harian yang berelasi **1 : N** dari tabel induknya.
5. **`chat_history`:** Menyimpan log tanya-jawab antara pengguna dengan asisten AI (`user_id`, `pertanyaan`, `jawaban`, `created_at`).

Seluruh relasi foreign key telah dibekali klausul integritas referensial:
- `ON DELETE CASCADE` pada riwayat absensi dan jadwal, sehingga jika suatu data induk dihapus, relasi anak otomatis terbersihkan dan database terhindar dari *data yatim (orphaned records)*.
- `ON DELETE SET NULL` pada akun login profil guru dan siswa."

---

#### 🔹 2.6 Prototipe Interaktif UI/UX Figma (Menit 14:15 – 15:30)

> **[PETUNJUK VISUAL]:**  
> *Buka berkas `prototipe_figma_interactive.html` di browser. Klik tombol tab atas untuk mendemonstrasikan transisi layar Figma secara halus.*

**[DIALOG PRESENTER]:**  
"Sebelum masuk ke implementasi kodingan riil, kami terlebih dahulu merancang **Prototipe Interaktif UI/UX di Figma**.

Prototipe ini menyimulasikan pengalaman pengguna secara utuh (*clickable prototype*):
- **Layar Login Multi-Role:** Memiliki selector peran instan (*Admin, Guru, Siswa*) dengan tata letak minimalis dan elegan.
- **Dashboard Utama:** Dilengkapi kartu metrik KPI persentase kehadiran, feed log real-time, dan status WhatsApp Gateway aktif.
- **Universal Scanner:** Menampilkan bingkai bidik kamera lengkap dengan animasi garis laser pemindai merah dan tombol simulasi scan sukses.
- **Manajemen Kartu Siswa:** Menampilkan tabel data yang dapat memunculkan mockup fisik Kartu Tanda Pelajar ber-barcode siap cetak.
- **Floating AI Chatbot:** Tombol interaktif di pojok kanan bawah yang bisa dibuka untuk menguji interaksi percakapan kecerdasan buatan.

Setelah seluruh rancangan arsitektur dan antarmuka tervalidasi, kami mewujudkannya ke dalam aplikasi nyata. Mari kita saksikan live demo aplikasi pada bagian ketiga!"

---

### 🟢 MENIT 15:30 – 22:30 | BAGIAN 3: LIVE DEMO APLIKASI NYATA

---

#### 🔹 3.1 Login Multi-Role & Dashboard Administrator (Menit 15:30 – 16:45)

> **[PETUNJUK VISUAL]:**  
> *Buka peramban di alamat `http://localhost/sistemabsensi/`. Masukkan username `admin` dan password `password`. Klik tombol Login. Masuk ke halaman Dashboard Admin.*

**[DIALOG PRESENTER]:**  
"Sekarang kita berada di tahap **Live Demo Aplikasi**. Aplikasi ini berjalan secara lokal di server web Apache dan MariaDB XAMPP pada lingkungan PHP 8.2 Native.

Pertama, kita uji proses autentikasi. Saya memasukkan username `admin` dan password `password`. Begitu tombol login diklik, sistem memverifikasi hash Bcrypt dan membuat sesi aman.

Kita langsung disambut oleh **Dashboard Administrator**:
- Di bagian atas, terlihat tanggal hari ini beserta indikator jam cutoff toleransi: `07:00:00 WIB`.
- Terdapat **4 Kartu Ringkasan Kehadiran Real-Time**: jumlah siswa yang Hadir Tepat Waktu, Terlambat, Sakit/Izin, serta kehadiran Guru.
- Di sisi kanan bawah, kita melihat indikator koneksi **Fonnte WA Gateway Online** bertanda hijau, siap memproses pesan secara otomatis."

---

#### 🔹 3.2 Demo Pemindaian QR Kamera & WhatsApp Gateway Real-Time (Menit 16:45 – 19:00)

> **[PETUNJUK VISUAL]:**  
> *Buka menu 'Universal Scanner QR'. Kamera webcam aktif. Tunjukkan kartu pelajar atau arahkan file QR Code `BC-SISWA-001` (Andi Pratama) ke kamera. Dengarkan suara nada audio beep sukses. Di layar muncul popup hijau notifikasi presensi. Tampilkan preview WhatsApp atau tangkapan layar pesan WhatsApp yang masuk ke ponsel orang tua.*

**[DIALOG PRESENTER]:**  
"Sekarang kita masuk ke fitur utama aplikasi: **Universal Scanner QR Code**.

Saya membuka menu scanner. Sistem secara otomatis meminta izin kamera webcam dan mengaktifkan streaming video berkecepatan 30 frame per detik menggunakan pustaka `Html5-Qrcode`.

Di sini saya memegang Kartu Pelajar milik siswa bernama **Andi Pratama (X RPL 1)** dengan Barcode ID `BC-SISWA-001`.
Perhatikan: Saya arahkan barcode ke lensa kamera...
*(Suara Beep Berbunyi: 'BEEP!')*

Luar biasa! Dalam waktu kurang dari 0,5 detik:
1. Layar peramban langsung menampilkan popup modal hijau: *'Presensi Berhasil — Hadir Tepat Waktu'*.
2. Sistem mencatat waktu presensi ke dalam database MariaDB pada tabel `absensi_siswa`.
3. Pada saat yang bersamaan, mari kita lihat layar ponsel orang tua:
   *(Tunjukkan ponsel atau preview chat WhatsApp)*  
   Pesan WhatsApp otomatis langsung masuk dari Gateway Fonnte dengan bunyi pesan:
   *'Pemberitahuan Presensi SMK NU WARGABINANGUN: Yth. Bpk/Ibu Orang Tua dari Andi Pratama, putra/i Anda telah hadir di sekolah pada pukul 06:48 WIB dengan status HADIR TEPAT WAKTU. Terima kasih.'*

Semua terjadi secara instan, otomatis, dan transparan tanpa petugas perlu mengetik pesan manual satu per satu!"

---

#### 🔹 3.3 Demo Manajemen Siswa & Cetak Kartu ID Barcode (Menit 19:00 – 20:15)

> **[PETUNJUK VISUAL]:**  
> *Buka menu 'Data Siswa'. Tunjukkan fitur pencarian data, filter per kelas (misal kelas X RPL 1). Klik tombol 'Cetak Kartu' atau preview kartu ID.*

**[DIALOG PRESENTER]:**  
"Selanjutnya, mari kita lihat menu **Manajemen Master Data Siswa**.

Di halaman ini, pihak admin atau tata usaha dapat mengelola data peserta didik dengan mudah:
- Terdapat fitur pencarian instan berdasarkan nama, NIS, maupun Barcode ID.
- Filter data berdasarkan rombongan belajar kelas.
- Dan yang paling istimewa adalah fitur **Generate & Cetak Kartu Pelajar Barcode**.  
Saya klik tombol 'Cetak Kartu'...
*(Layar menampilkan jendela cetak Kartu Siswa)*  
Sistem secara dinamis merender Kartu Tanda Pelajar berstandar ukuran ID Card, lengkap dengan foto siswa, data NISN, serta Barcode dan QR Code unik beresolusi tinggi yang siap diprint pada kartu PVC fisik."

---

#### 🔹 3.4 Demo Asisten Cerdas AI Chatbot (Live DB RAG) (Menit 20:15 – 21:30)

> **[PETUNJUK VISUAL]:**  
> *Klik ikon robot melayang di pojok kanan bawah. Jendela chat terbuka. Ketik pertanyaan: "Berapa kali kehadiran saya bulan ini dan bagaimana rekapitulasi saya?" Tekan Kirim. Perhatikan animasi indikator AI mengetik dan balasan teks data riil yang muncul.*

**[DIALOG PRESENTER]:**  
"Kini kita uji fitur inovatif berikutnya: **Asisten Cerdas AI Chatbot**.

Di pojok kanan bawah setiap halaman, terdapat widget asisten pintar. Fitur ini bukan sekadar chatbot biasa yang menjawab berdasarkan teks template, melainkan menggunakan teknologi **Retrieval-Augmented Generation (RAG)**.

Saya buka widget obrolan, lalu saya ketik pertanyaan:
*'Halo AI, tolong sebutkan berapa kali saya sudah hadir bulan ini dan apakah saya punya catatan terlambat?'*
Saya tekan tombol kirim...
*(Perhatikan animasi typing bubble)*

Mari kita cermati balasannya:  
AI merespons: *'Halo Andi Pratama! Berdasarkan catatan basis data resmi SMK NU WARGABINANGUN per bulan November 2026, Anda telah hadir sebanyak 18 kali tepat waktu, tercatat terlambat 1 kali, dan tidak memiliki catatan alpa. Tingkat persentase kehadiran Anda adalah 94.7% (Sangat Baik).'*

Jawaban ini terbukti 100% faktual karena `ChatbotController` secara otomatis menginjeksi rekap tabel database langsung ke model bahasa AI sebelum jawaban dirangkai."

---

#### 🔹 3.5 Demo Rekap Laporan Absensi Excel / PDF (Menit 21:30 – 22:30)

> **[PETUNJUK VISUAL]:**  
> *Buka menu 'Laporan Absensi'. Pilih rentang tanggal awal dan akhir bulan berjalan, pilih kelas X RPL 1, lalu klik tombol 'Ekspor Excel'. Tunjukkan file spreadsheet yang terunduh rapi.*

**[DIALOG PRESENTER]:**  
"Fitur terakhir pada demo ini adalah **Rekapitulasi Laporan Kehadiran**.

Pihak kurikulum dan wali kelas tidak perlu lagi menghitung manual lembaran kertas di akhir semester. Pada menu Laporan:
1. Kita cukup memilih rentang tanggal awal dan akhir.
2. Memilih kelas yang diinginkan, misalnya kelas X RPL 1.
3. Klik tombol **Ekspor Excel**.
*(File Excel langsung terunduh secara instan)*

Di dalam file spreadsheet ini, seluruh kalkulasi persentase, total hadir, terlambat, izin, sakit, dan alpa telah dikelompokkan rapi per kolom dan siap diserahkan kepada Kepala Sekolah maupun Dinas Pendidikan."

---

### 🟢 MENIT 22:30 – 24:00 | PENUTUP, EVALUASI & RENCANA PENGEMBANGAN

> **[PETUNJUK VISUAL]:**  
> *Kamera kembali mengarah penuh ke Wajah Presenter. Tampilkan slide ringkasan capaian proyek dan ucapan terima kasih.*

**[DIALOG PRESENTER]:**  
"Bapak/Ibu penguji dan hadirin sekalian,

Dari serangkaian pemaparan yang telah saya sampaikan—mulai dari dokumen PRD, perancangan diagram UML yang komprehensif, struktur basis data berstandar industri, rancangan prototipe Figma, hingga demo aplikasi riil yang bekerja secara sempurna—dapat disimpulkan bahwa:

1. **Sistem Presensi SMK NU WARGABINANGUN berhasil mengatasi kelemahan sistem konvensional**, memangkas waktu presensi menjadi kurang dari 1 detik, dan meniadakan kecurangan titip absen.
2. **Integrasi WhatsApp Gateway Fonnte menciptakan transparansi komunikasi real-time** yang memberikan ketenangan bagi para orang tua murid.
3. **Penerapan AI Chatbot RAG membuktikan bahwa teknologi kecerdasan buatan masa kini dapat diterapkan secara praktis** untuk mempermudah akses informasi data sekolah secara cepat dan akurat.

**Rencana Pengembangan ke Depan:**
Untuk pengembangan selanjutnya, sistem ini direncanakan dapat diperluas dengan fitur pengenalan wajah (*Face Recognition*) sebagai autentikasi biometrik sekunder, serta integrasi notifikasi push pada aplikasi mobile berbasis Android/iOS.

Demikian presentasi proyek Rekayasa Perangkat Lunak ini saya sampaikan. Terima kasih yang sebesar-besarnya atas bimbingan para guru dan perhatian Bapak/Ibu penguji. Mohon maaf atas segala kekurangan.

Wabillahi taufiq wal hidayah,  
Wassalamu’alaikum Warahmatullahi Wabarakatuh."

---

## 💡 TIPS SUKSES SAAT REKAMAN VIDEO

1. **Gunakan Mikrofon Clip-on / Headset yang Jernih:** Pastikan suara vokal terdengar renyah tanpa dengung (*noise*).
2. **Gunakan Software Perekam Layar yang Andal:** Disarankan menggunakan **OBS Studio** (gratis dan tanpa watermark) dengan resolusi minimal Full HD 1080p (1920 × 1080).
3. **Atur Posisi Layar:** Siapkan tab-tab browser terlebih dahulu sebelum mulai merekam:
   - Tab 1: Slide Presentasi / PRD
   - Tab 2: `use_case_diagram.html`
   - Tab 3: `activity_diagram_swimlane.png`
   - Tab 4: `class_diagram.png`
   - Tab 5: `sequence_diagram.png`
   - Tab 6: `erd_diagram.png`
   - Tab 7: `prototipe_figma_interactive.html`
   - Tab 8: `localhost/sistemabsensi` (Aplikasi Nyata)
4. **Jeda Nafas Antar Poin:** Jangan terburu-buru. Berikan jeda 1-2 detik saat berpindah slide atau berganti topik agar video terasa profesional dan nyaman disimak.
