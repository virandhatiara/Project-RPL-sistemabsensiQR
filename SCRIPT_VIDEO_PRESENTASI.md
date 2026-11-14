# NASKAH SKRIP PRESENTASI VIDEO
## SISTEM INFORMASI PRESENSI & ABSENSI SISWA & GURU
### SMK NU WARGABINANGUN

---

## 📌 PANDUAN PEREKAMAN & PERSIAPAN

* **Target Durasi**: 7 – 10 Menit (Bisa disesuaikan menjadi 5 menit untuk presentasi kilat).
* **Software Rekomendasi**: OBS Studio, Loom, atau Camtasia (Screen Recording + Webcam PiP di pojok kanan bawah).
* **Tab / Aplikasi yang Dibuka Sebelum Rekam**:
  1. Halaman Login / Dashboard Web Absensi (`http://localhost/sistemabsensi/public`).
  2. File PRD: [`PRD.md`](file:///c:/xampp/htdocs/sistemabsensi/PRD.md).
  3. Visual Use Case Diagram: [`use_case_diagram.html`](file:///c:/xampp/htdocs/sistemabsensi/use_case_diagram.html).
  4. Visual Activity Diagram: [`activity_diagram.html`](file:///c:/xampp/htdocs/sistemabsensi/activity_diagram.html).
* **Gaya Pembawaan**: Percaya diri, lugas, artikulasi jelas, dengan intonasi antusias dan profesional.

---

## 🎬 SUSUNAN RUNDOWN PRESENTASI

| Segmen | Menit | Topik Utama | Fokus Layar (Screen) |
| :---: | :---: | :--- | :--- |
| **Scene 1** | 00:00 – 01:15 | Pembukaan, Latar Belakang & Hook | Kamera Pengantar / Halaman Depan Web |
| **Scene 2** | 01:15 – 03:30 | Penjelasan PRD (Product Requirement) | File `PRD.md` |
| **Scene 3** | 03:30 – 06:00 | Penjelasan Use Case Diagram | File `use_case_diagram.html` |
| **Scene 4** | 06:00 – 08:30 | Penjelasan Activity Diagram | File `activity_diagram.html` |
| **Scene 5** | 08:30 – 09:30 | Kesimpulan & Penutup | Ringkasan Sistem / Demo Singkat |

---

## 📜 SKRIP DETIL PER SEGMEN

---

### SCENE 1: PEMBUKAAN, HOOK & LATAR BELAKANG
* **Estimasi Waktu**: `00:00 – 01:15`
* **Visual di Layar**: Wajah pembicara (kamera penuh) atau tampilan halaman Login Sistem Absensi SMK NU WARGABINANGUN.

#### [Narasi Pembicara]
> *"Halo semuanya! Assalamu’alaikum Warahmatullahi Wabarakatuh.*  
> 
> *Perkenalkan, saya [Sebutkan Nama Anda]. Pada kesempatan kali ini, saya akan mempresentasikan perancangan dan arsitektur dari proyek yang telah saya kembangkan, yaitu **Sistem Informasi Presensi & Absensi Siswa & Guru SMK NU WARGABINANGUN** berbasis Web dan Scanner Barcode/QR, yang telah terintegrasi langsung dengan **WhatsApp Gateway (Fonnte API)** dan asisten cerdas **AI Chatbot**.*
> 
> *Proyek ini dilatarbelakangi oleh permasalahan nyata di lingkungan sekolah, di mana presensi manual sering kali memicu antrean panjang, rekapitulasi data yang memakan waktu berhari-hari, dan yang paling krusial: **orang tua sering kali tidak mengetahui apakah anak mereka benar-benar sudah sampai di sekolah atau tidak**.*
> 
> *Melalui sistem ini, proses presensi dapat diselesaikan dalam hitungan detik via scanner kamera, dan secara otomatis mengirimkan **notifikasi pesan WhatsApp real-time ke ponsel orang tua siswa maupun guru**, serta menyediakan **AI Chatbot** untuk membantu menjawab informasi jadwal dan kehadiran sekolah secara cerdas.*
> 
> *Mari kita bedah arsitektur sistem ini mulai dari **PRD**, **Use Case Diagram**, hingga **Activity Diagram**-nya."*

---

### SCENE 2: PENJELASAN PRD (PRODUCT REQUIREMENT DOCUMENT)
* **Estimasi Waktu**: `01:15 – 03:30`
* **Visual di Layar**: Buka dan *scroll* file [`PRD.md`](file:///c:/xampp/htdocs/sistemabsensi/PRD.md), sorot bagian KPI, Arsitektur, dan Kebutuhan Fungsional.

#### [Narasi Pembicara]
> *"[Visual: Tampilkan file PRD.md pada bagian Tujuan Bisnis & KPI]*  
> 
> *Pertama, mari kita tinjau **Product Requirement Document (PRD)** dari sistem ini.*
> 
> *Dalam dokumen PRD, kami menetapkan metrik keberhasilan (KPI) yang sangat terukur:*
> 1. *Kecepatan pemindaian presensi per individu ditargetkan **di bawah 1 detik** menggunakan kamera webcam atau scanner laser.*
> 2. *Kecepatan pengiriman notifikasi WhatsApp ke orang tua ditargetkan **di bawah 5 detik** sesaat setelah scan berhasil.*
> 3. *Tingkat akurasi data mencapai **99.9%** dengan validasi timestamp server dan eliminasi kesalahan manual.*
> 
> *[Visual: Scroll ke bagian Arsitektur & User Persona]*  
> 
> *Sistem ini melayani 5 persona pengguna utama di sekolah:*
> 1. * **Administrator**: Pengelola penuh master data siswa, guru, kelas, jadwal, akun pengguna, dan konfigurasi API AI.*
> 2. * **Guru**: Dapat melakukan presensi masuk dan pulang (baik via kamera maupun presensi mandiri di dashboard), serta mengelola nomor WhatsApp pribadi.*
> 3. * **Siswa**: Dapat melihat kode QR presensi pribadi, mengecek nomor kontak orang tua yang terdaftar, dan melihat riwayat kehadiran.*
> 4. * **Wali Kelas**: Memiliki hak akses monitoring absensi khusus kelas binaannya.*
> 5. * **Kepala Sekolah**: Memantau rekapitulasi eksekutif dan statistik kehadiran sekolah.*
> 6. *Dan satu penerima manfaat eksternal, yaitu **Orang Tua / Wali Murid** yang menerima pesan WhatsApp otomatis.*
> 
> *[Visual: Scroll ke bagian Kebutuhan Fungsional Terinci]*  
> 
> *Dari sisi fungsionalitas, sistem dibangun menggunakan arsitektur **Native PHP 8.2 MVC** dengan database **MariaDB/MySQL InnoDB**, Bootstrap 5, dan terhubung dengan dua layanan API pihak ketiga utama:*
> * *Pertama, **Fonnte WhatsApp API Gateway** untuk pengiriman notifikasi otomatis.*
> * *Kedua, **Cloud LLM API** (mendukung provider NVIDIA NIM, Google Gemini, OpenAI, dan DeepSeek) untuk mentenagai fitur asisten cerdas sekolah.*
> * *Selain itu, sistem dilengkapi fitur **Auto-Data Alpha**, yang secara otomatis mendata siswa dan guru yang belum hadir setelah jam cutoff pukul 07:00 pagi, dan akan otomatis meng-update status jika mereka kemudian hadir di sekolah."*

---

### SCENE 3: PENJELASAN UML USE CASE DIAGRAM
* **Estimasi Waktu**: `03:30 – 06:00`
* **Visual di Layar**: Buka file visual [`use_case_diagram.html`](file:///c:/xampp/htdocs/sistemabsensi/use_case_diagram.html), arahkan kursor ke aktor kiri, elips tengah, elips kanan, dan relasi panah.

#### [Narasi Pembicara]
> *"[Visual: Tampilkan poster use_case_diagram.html secara utuh, lalu zoom perlahan]*  
> 
> *Sekarang, mari kita bedah **UML Use Case Diagram** dari sistem absensi ini.*
> 
> *Bisa kita lihat pada diagram visual ini, strukturnya dibagi menjadi 3 bagian yang sangat jelas:*
> 
> *[Visual: Arahkan mouse ke sisi kiri - Human Actors]*  
> *Di sebelah kiri, terdapat **Aktor Pengguna Utama (Human Actors)** yang dibedakan dengan kartu warna:*
> * *Warna ungu untuk **Administrator**, yang terhubung ke Use Case UC-01 sampai UC-07 untuk pengelolaan master data, akun, cetak kartu QR, dan rekapitulasi sekolah.*
> * *Warna hijau untuk **Guru**, yang terhubung ke UC-08 untuk Presensi Mandiri, UC-09 untuk Atur Nomor WhatsApp, jadwal mengajar, dan riwayat absensi.*
> * *Warna biru untuk **Siswa**, yang terhubung ke UC-12 untuk unduh kode QR, cek kontak ortu terdaftar, dan jadwal pelajaran.*
> 
> *[Visual: Arahkan mouse ke tengah - Batas Sistem]*  
> *Di dalam kotak **Batas Sistem Aplikasi Web**, pada kolom tengah terdapat use case inti bersama:*
> * ***UC-16: Login Sistem** yang menangani autentikasi multi-role.*
> * ***UC-17: Scan Barcode / QR Presensi**, yang menjadi pusat pemindaian universal kamera di gerbang sekolah.*
> * *Dan perhatikan dua relasi penting di sini:*
>   1. *Hubungan `«include»` ke **UC-18: Validasi Kode & Jam**, artinya setiap kali scan dilakukan, sistem **wajib** memvalidasi kode unik dan mengecek batas waktu.*
>   2. *Hubungan `«extend»` dari **UC-19: Auto-Data Alpha**, artinya status Alpha yang terbuat otomatis bisa diperbarui kondisional saat siswa melakukan scan presensi.*
> 
> *[Visual: Arahkan mouse ke sisi kanan - Fitur AI, WhatsApp & Aktor Eksternal]*  
> *Di sebelah kanan batas sistem, terdapat use case integrasi:*
> * ***UC-20: Interaksi AI Chatbot Asisten** dan **UC-21: Konfigurasi API Multi-Provider**.*
> * ***UC-22: Notifikasi WA Presensi ke Ortu Siswa**.*
> * ***UC-23: Notifikasi WA Presensi ke Guru**.*
> 
> *Perhatikan relasi panah `«include (Siswa)»`: saat kamera membaca kartu siswa, sistem secara otomatis memicu pengiriman notifikasi ke **Fonnte WhatsApp Gateway** yang diteruskan ke WhatsApp orang tua.*  
> *Begitu pula saat guru melakukan scan atau presensi mandiri, relasi `«include (Guru)»` secara otomatis mengirimkan konfirmasi jam masuk dan kepulangan ke WhatsApp guru.*
> 
> *Semua ini bermuara pada **Secondary Actor** di kotak kuning emas sebelah kanan, yaitu **AI Chatbot & WhatsApp Gateway**."*

---

### SCENE 4: PENJELASAN UML ACTIVITY DIAGRAM
* **Estimasi Waktu**: `06:00 – 08:30`
* **Visual di Layar**: Buka file visual [`activity_diagram.html`](file:///c:/xampp/htdocs/sistemabsensi/activity_diagram.html), klik tab navigasi untuk berpindah antar alur proses.

#### [Narasi Pembicara]
> *"[Visual: Tampilkan activity_diagram.html pada Tab 1 - Scan QR & Notif WA]*  
> 
> *Selanjutnya, bagaimana proses bisnis ini berjalan secara dinamis? Kita dapat melihatnya pada **UML Activity Diagram**.*
> 
> *Mari kita sorot diagram paling penting, yaitu **Alur Pemindaian Presensi & Pengiriman WhatsApp Real-time**:*
> * * **Swimlane Pengguna**: Siswa atau Guru mengarahkan kartu barcode/QR ke kamera.*
> * * **Swimlane Scanner Frontend**: Library `Html5-Qrcode` membaca pola kode, mengekstrak string teks, dan mengirimkannya via AJAX POST.*
> * * **Swimlane Backend & Database**: Backend menerima kode, lalu melakukan pengecekan ke database:*
>   * *Jika terdeteksi sebagai **Siswa**, sistem mengecek apakah sudah absen hari ini. Jika sebelumnya ditandai Alpha otomatis, sistem meng-update record tersebut menjadi Hadir atau Terlambat berdasarkan batas jam 07:00 pagi.*
>   * *Setelah data kehadiran tersimpan, sistem mengambil nomor WhatsApp orang tua (`no_hp_ortu`), menyusun format pesan resmi dengan kop sekolah SMK NU WARGABINANGUN, dan mengeksekusi request HTTP POST cURL ke Fonnte.*
> * * **Swimlane WhatsApp Gateway**: Fonnte menerima data dan dalam waktu kurang dari 5 detik, pesan notifikasi resmi sudah berbunyi di ponsel orang tua.*
> 
> *[Visual: Klik Tab 3 - Presensi Mandiri & WA Guru]*  
> *Pada **Alur Guru**, guru memiliki dua jalur fleksibel:*
> 1. *Mereka bisa memperbarui nomor WhatsApp mereka secara mandiri melalui modal pop-up di dashboard, yang langsung divalidasi oleh sistem.*
> 2. *Guru dapat menekan tombol 'Presensi Masuk' atau 'Presensi Pulang' secara mandiri, di mana sistem menerapkan proteksi jeda waktu minimal 10 menit sebelum jam pulang dapat dicatat.*
> 
> *[Visual: Klik Tab 4 - Konsultasi AI Chatbot]*  
> *Terakhir, pada **Alur Asisten AI Chatbot**:*
> * *Sistem ini menerapkan konsep **Live DB Context Injection**. Saat pengguna mengirim pertanyaan (misalnya: 'Bagaimana rekap absensi saya bulan ini?'), controller AI terlebih dahulu mengambil data riil dari database (jadwal dan riwayat kehadiran) dan memasukkannya ke dalam system prompt sebelum memanggil LLM API.*
> * *Hasilnya, jawaban yang diberikan oleh AI adalah data faktual sekolah yang akurat, personal, dan bebas dari halusinasi."*

---

### SCENE 5: KESIMPULAN & PENUTUP
* **Estimasi Waktu**: `08:30 – 09:30`
* **Visual di Layar**: Kembali ke wajah pembicara atau perlihatkan ringkasan dashboard sistem absensi.

#### [Narasi Pembicara]
> *"[Visual: Tampilan Dashboard Admin / Wajah Pembicara]*  
> 
> *Sebagai kesimpulan, integrasi antara **PRD**, **Use Case Diagram**, dan **Activity Diagram** yang telah kita rancang berhasil menjawab seluruh tantangan presensi di SMK NU WARGABINANGUN:*
> 1. *Presensi berjalan sangat cepat dan paperless.*
> 2. *Orang tua mendapatkan ketenangan hati dan kepastian kehadiran putra-putrinya melalui notifikasi WhatsApp otomatis.*
> 3. *Guru memiliki kemudahan fleksibilitas presensi mandiri dan pengelolaan kontak pribadi.*
> 4. *Serta sekolah memiliki asisten cerdas berbasis AI untuk efisiensi informasi.*
> 
> *Seluruh dokumen arsitektur ini—mulai dari PRD, spesifikasi Use Case, file diagram interaktif, hingga skrip presentasi ini—telah tersimpan rapi di dalam repositori proyek.*
> 
> *Demikian presentasi dari saya. Terima kasih banyak atas perhatiannya, semoga bermanfaat!*  
> *Wabillahi taufiq wal hidayah, Wassalamu’alaikum Warahmatullahi Wabarakatuh."*

---

## 💡 TIPS SUKSES SAAT REKAMAN
1. **Lakukan gladi resik sekali**: Baca skrip di atas satu kali sebelum merekam agar tidak terbata-bata.
2. **Kursor Mouse sebagai Pointer**: Gunakan kursor mouse untuk menunjuk bagian diagram yang sedang dijelaskan (misalnya saat menyebut relasi `«include»` atau kotak aktor Fonnte).
3. **Pencahayaan & Audio**: Pastikan mikrofon dekat dengan mulut dan ruangan minim gema agar suara narasi terdengar renyah dan profesional.
