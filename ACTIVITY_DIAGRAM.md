# DOKUMENTASI UML ACTIVITY DIAGRAM
## SISTEM INFORMASI PRESENSI & ABSENSI SISWA & GURU
### SMK NU WARGABINANGUN

Dokumen ini memuat perancangan **UML Activity Diagram (Diagram Aktivitas)** resmi untuk alur kerja operasional pada **Sistem Absensi SMK NU WARGABINANGUN**, mencakup integrasi pemindaian **Kamera Barcode/QR**, pengiriman **Notifikasi WhatsApp Real-time (Fonnte API)**, asisten **AI Chatbot**, dan otomatisasi status kehadiran.

---

## DAFTAR ALUR PROSES (ACTIVITY DIAGRAMS)

1. **Activity Diagram 1**: Autentikasi Pengguna & Pengalihan Hak Akses (Login Flow)
2. **Activity Diagram 2**: Pemindaian Presensi Scanner Universal & Pengiriman Notifikasi WhatsApp (Core Attendance Flow)
3. **Activity Diagram 3**: Presensi Mandiri & Pengaturan Nomor WhatsApp Guru (Guru Dashboard Flow)
4. **Activity Diagram 4**: Konsultasi Interaktif Asisten Cerdas AI Chatbot (Live DB RAG Context Flow)
5. **Activity Diagram 5**: Sinkronisasi & Penanganan Status Alpha Otomatis (Auto-Alpha Flow)
6. **Activity Diagram 6**: Manajemen Master Data Siswa & Kontak WhatsApp Orang Tua (Admin Data Flow)

---

## 1. ACTIVITY DIAGRAM 1: AUTENTIKASI PENGGUNA & PENGALIHAN ROLE

Diagram ini menggambarkan alur saat pengguna (Admin, Guru, Siswa, Wali Kelas, Kepala Sekolah) masuk ke dalam sistem absensi melalui validasi kredensial, proteksi CSRF, enkripsi bcrypt, dan pengalihan ke dashboard masing-masing.

```mermaid
flowchart TD
    %% Node Awal
    StartNode((● Mulai)) --> A1[Pengguna membuka URL /auth]

    subgraph User_Lane["👤 Pengguna"]
        A1
        A2[Memasukkan Username / NIS / NIP & Password]
        A3[Menekan tombol 'Masuk ke Sistem']
    end

    subgraph System_Lane["🖥️ Sistem Backend (AuthController)"]
        B1[Generate & Validasi Token CSRF]
        B2{Token CSRF Valid?}
        B3[Cari Akun di Tabel users]
        B4{Akun Ditemukan & Aktif?}
        B5[Verifikasi Password via password_verify]
        B6{Password Cocok?}
        B7[Regenerasi Sesi ID session_regenerate_id]
        B8[Simpan Data Sesi: user_id, username, role, nama]
        B9[Cek Hak Akses Role Pengguna]
        B10[Redirect ke Dashboard Admin]
        B11[Redirect ke Dashboard Guru]
        B12[Redirect ke Dashboard Siswa]
        B13[Redirect ke Dashboard Wali Kelas]
        B14[Redirect ke Dashboard Kepala Sekolah]
        B_Err1[Set Flash Message: 'CSRF Token Invalid']
        B_Err2[Set Flash Message: 'Akun Tidak Ditemukan / Nonaktif']
        B_Err3[Set Flash Message: 'Password Salah']
        B_Redirect[Redirect kembali ke /auth]
    end

    subgraph DB_Lane["🗄️ Database (MariaDB)"]
        D1[(Tabel users)]
        D2[(Tabel guru / siswa)]
    end

    A1 --> A2 --> A3 --> B1
    B1 --> B2
    B2 -- Tidak --> B_Err1 --> B_Redirect --> A1
    B2 -- Ya --> B3
    B3 <--> D1
    B3 --> B4
    B4 -- Tidak --> B_Err2 --> B_Redirect
    B4 -- Ya --> B5
    B5 --> B6
    B6 -- Tidak --> B_Err3 --> B_Redirect
    B6 -- Ya --> B7 --> B8
    B8 <--> D2
    B8 --> B9
    
    B9 -- role = 'admin' --> B10
    B9 -- role = 'guru' --> B11
    B9 -- role = 'siswa' --> B12
    B9 -- role = 'wali_kelas' --> B13
    B9 -- role = 'kepala_sekolah' --> B14

    B10 --> End1((● Selesai))
    B11 --> End1
    B12 --> End1
    B13 --> End1
    B14 --> End1
```

---

## 2. ACTIVITY DIAGRAM 2: PEMINDAIAN SCANNER UNIVERSAL & NOTIFIKASI WHATSAPP

Diagram inti ini menunjukkan alur kerja dari pemindaian kode QR/Barcode di kamera scanner sekolah, identifikasi entitas (Siswa vs Guru), pencatatan jam dan status presensi ke database, hingga pengiriman **Notifikasi WhatsApp Otomatis** ke Orang Tua atau Guru melalui **Fonnte API Gateway**.

```mermaid
flowchart TD
    StartScan((● Mulai)) --> S1[Arahkan Kartu QR / Barcode ke Kamera]

    subgraph Lane_User["👨‍🎓 Siswa / 👨‍🏫 Guru"]
        S1
        S_Success[Melihat Notifikasi Sukses & Mendengar Suara Beep]
    end

    subgraph Lane_Scanner["📷 Scanner Frontend (Html5-Qrcode)"]
        C1[Kamera mendeteksi pola QR / Barcode]
        C2[Ekstrak string kode]
        C3[Kirim AJAX POST ke /proses_absen.php]
    end

    subgraph Lane_Backend["🖥️ Backend Core (proses_absen.php)"]
        P1[Validasi input kode & waktu server WIB]
        P2[Query: Cari kode di Tabel siswa]
        P3{Apakah Siswa?}
        
        %% Cabang Siswa
        P_S1[Cek absensi_siswa tanggal hari ini]
        P_S2{Sudah Absen Hari Ini?}
        P_S3{Apakah Status Sebelumnya Alpha Otomatis?}
        P_S4[Update record Alpha menjadi Hadir/Terlambat]
        P_S5[Insert Record Baru absensi_siswa]
        P_S6[Tentukan Status: Hadir <= 07:00 atau Terlambat > 07:00]
        P_S7[Ambil nomor no_hp_ortu & nama_ortu siswa]
        P_S8[Susun format pesan teks resmi WhatsApp untuk Ortu]
        P_S9[Eksekusi cURL HTTP POST ke Fonnte API]

        %% Cabang Guru
        P_G1[Query: Cari kode di Tabel guru]
        P_G2{Apakah Guru Terdaftar?}
        P_G3[Cek absensi_guru tanggal hari ini]
        P_G4{Ada Riwayat Hari Ini?}
        P_G5[Catat Jam Masuk & Tentukan Status]
        P_G6{Jam Pulang Masih Kosong?}
        P_G7{Jeda Waktu Kepulangan >= 10 Menit?}
        P_G8[Catat Jam Pulang absensi_guru]
        P_G9[Ambil no_wa guru & susun pesan WA konfirmasi Masuk/Pulang]
        P_G10[Eksekusi cURL HTTP POST ke Fonnte API]

        P_RespSuccess[Kirim Response JSON: Status Success, Data Kehadiran]
        P_RespWarning[Kirim Response JSON: Status Warning / Cooldown]
        P_RespError[Kirim Response JSON: Status Error]
    end

    subgraph Lane_DB["🗄️ Database"]
        DB_Siswa[(siswa)]
        DB_Guru[(guru)]
        DB_AbsSiswa[(absensi_siswa)]
        DB_AbsGuru[(absensi_guru)]
    end

    subgraph Lane_WA["📲 Fonnte WhatsApp Gateway"]
        WA1[Menerima request POST via HTTPS]
        WA2[Format pesan ke nomor tujuan country code 62]
        WA3[Kirim pesan WhatsApp real-time ke nomor HP Ortu / Guru]
    end

    subgraph Lane_Receiver["📱 HP Penerima (Ortu / Guru)"]
        R1[Notifikasi WhatsApp masuk ke HP Orang Tua Siswa]
        R2[Notifikasi WhatsApp masuk ke HP Guru]
    end

    S1 --> C1 --> C2 --> C3 --> P1 --> P2
    P2 <--> DB_Siswa
    P2 --> P3
    
    %% Alur Siswa
    P3 -- Ya --> P_S1
    P_S1 <--> DB_AbsSiswa
    P_S1 --> P_S2
    P_S2 -- Sudah Absen --> P_S3
    P_S3 -- Ya (Pernah Alpha) --> P_S4 --> P_S6
    P_S3 -- Tidak (Sudah Hadir) --> P_RespWarning
    P_S2 -- Belum Absen --> P_S5 --> P_S6
    P_S6 --> P_S7 --> P_S8 --> P_S9
    P_S9 --> WA1
    P_S8 --> P_RespSuccess

    %% Alur Guru
    P3 -- Tidak --> P_G1
    P_G1 <--> DB_Guru
    P_G1 --> P_G2
    P_G2 -- Tidak --> P_RespError
    P_G2 -- Ya --> P_G3
    P_G3 <--> DB_AbsGuru
    P_G3 --> P_G4
    P_G4 -- Belum Ada / Alpha --> P_G5 --> P_G9
    P_G4 -- Sudah Ada Jam Masuk --> P_G6
    P_G6 -- Belum Pulang --> P_G7
    P_G6 -- Sudah Pulang Lengkap --> P_RespWarning
    P_G7 -- Ya (>= 10 menit) --> P_G8 --> P_G9
    P_G7 -- Terlalu Cepat (< 10 menit) --> P_RespWarning
    P_G9 --> P_G10 --> WA1
    P_G9 --> P_RespSuccess

    %% Pengiriman Fonnte
    WA1 --> WA2 --> WA3
    WA3 -- Untuk Siswa --> R1
    WA3 -- Untuk Guru --> R2

    P_RespSuccess --> S_Success --> EndScan((● Selesai))
    P_RespWarning --> S_Success
    P_RespError --> S_Success
```

---

## 3. ACTIVITY DIAGRAM 3: PRESENSI MANDIRI & KELOLA NOMOR WHATSAPP GURU

Diagram ini menunjukkan interaksi guru pada portal pribadinya untuk mengelola kontak WhatsApp dan melakukan presensi kehadiran mandiri (Jam Masuk dan Jam Pulang) langsung dari tombol dashboard.

```mermaid
flowchart TD
    StartGuru((● Mulai)) --> G1[Guru login ke Portal Guru /guru/dashboard]

    subgraph Guru_Action["👨‍🏫 Guru"]
        G1
        G_Choice{Pilih Aksi?}
        
        %% Cabang Update No HP
        G_HP1[Klik 'Tambah / Ubah Nomor WhatsApp']
        G_HP2[Modal #modalNoHpGuru Terbuka]
        G_HP3[Masukkan Nomor WhatsApp Aktif Contoh: 0812xxx]
        G_HP4[Klik 'Simpan Nomor WhatsApp']

        %% Cabang Presensi Mandiri
        G_Abs1[Klik Tombol 'Presensi Masuk' / 'Presensi Pulang']
    end

    subgraph Guru_Backend["🖥️ Controller Guru (GuruController.php)"]
        %% Alur No HP
        H1[Validasi Token CSRF Form]
        H2[Sanitasi angka regex non-digit]
        H3{Panjang digit 9 - 16 angka?}
        H4[Update kolom no_wa & no_hp di tabel guru]
        H_Err[Set Flash: 'Nomor WhatsApp Tidak Valid']
        H_Ok[Set Flash: 'Nomor WhatsApp Berhasil Disimpan']

        %% Alur Presensi Mandiri
        A1[Validasi Token CSRF]
        A2[Ambil profil guru berdasarkan user_id sesi]
        A3[Cek absensi_guru hari ini]
        A4{Status Presensi Hari Ini?}
        A5[Catat Jam Masuk & Status Hadir/Terlambat]
        A6{Belum Pulang & Jeda Cukup?}
        A7[Catat Jam Pulang]
        A8[Ambil target nomor WhatsApp guru]
        A9{Nomor WhatsApp Terdaftar?}
        A10[Kirim Pesan Konfirmasi via Fonnte API]
        A11[Set Flash Success Presensi]
        A_Warn[Set Flash Warning: Presensi Sudah Lengkap]
        A_Redirect[Redirect kembali ke /guru/dashboard]
    end

    subgraph Guru_DB["🗄️ Database"]
        DB_GuruTable[(tabel guru)]
        DB_AbsGuruTable[(tabel absensi_guru)]
    end

    G1 --> G_Choice
    G_Choice -- Kelola Nomor HP WA --> G_HP1 --> G_HP2 --> G_HP3 --> G_HP4 --> H1
    H1 --> H2 --> H3
    H3 -- Tidak --> H_Err --> A_Redirect
    H3 -- Ya --> H4
    H4 <--> DB_GuruTable
    H4 --> H_Ok --> A_Redirect

    G_Choice -- Presensi Mandiri --> G_Abs1 --> A1
    A1 --> A2 <--> DB_GuruTable
    A2 --> A3 <--> DB_AbsGuruTable
    A3 --> A4
    A4 -- Belum Masuk / Alpha --> A5
    A4 -- Sudah Masuk --> A6
    A6 -- Ya --> A7
    A6 -- Sudah Pulang --> A_Warn --> A_Redirect

    A5 <--> DB_AbsGuruTable
    A7 <--> DB_AbsGuruTable

    A5 --> A8
    A7 --> A8
    A8 --> A9
    A9 -- Ya --> A10
    A9 -- Belum Diisi --> A11
    A10 --> A11 --> A_Redirect
    A_Redirect --> G1
```

---

## 4. ACTIVITY DIAGRAM 4: KONSULTASI INTERAKTIF ASISTEN CERDAS AI CHATBOT

Diagram ini memperlihatkan mekanisme pemrosesan asisten cerdas AI Chatbot yang menyuntikkan data riil database (*Live DB Context Injection*) ke dalam *prompt* sebelum dikirimkan ke penyedia LLM API (NVIDIA, Gemini, OpenAI, atau DeepSeek).

```mermaid
flowchart TD
    StartAI((● Mulai)) --> U1[Pengguna membuka widget AI Chatbot di layar]

    subgraph User_Lane["👤 Pengguna (Semua Role)"]
        U1
        U2[Mengetik pertanyaan Contoh: 'Bagaimana rekap absensi saya bulan ini?']
        U3[Menekan tombol kirim pesan]
        U4[Menerima & membaca balasan faktual dari AI]
    end

    subgraph Chatbot_Engine["🖥️ Chatbot Controller (ChatbotController.php)"]
        C1[Ambil sesi login pengguna: role, user_id, nama]
        C2[Deteksi intensitas pertanyaan pengguna]
        C3[Query Konteks Data Nyata dari Database]
        C4[Ambil data profil pengguna]
        C5[Ambil rekap kehadiran Hadir, Sakit, Izin, Alpha]
        C6[Ambil jadwal pelajaran / mengajar hari ini]
        C7[Ambil konfigurasi API Key & Model dari ai_settings]
        C8[Susun System Prompt + Live Data Konteks Sekolah]
        C9[Kirim HTTP POST cURL ke Endpoint API LLM Eksternal]
        C10[Terima payload respons JSON dari LLM API]
        C11[Parsing teks jawaban asistensi]
        C12[Simpan log percakapan ke tabel chat_history]
        C13[Kirimkan respons teks ke widget antarmuka pengguna]
    end

    subgraph DB_System["🗄️ Database Sekolah"]
        D_Profile[(Profil Siswa / Guru)]
        D_Abs[(Rekap Absensi)]
        D_Jadwal[(Jadwal Pelajaran)]
        D_AiConfig[(ai_settings)]
        D_ChatHistory[(chat_history)]
    end

    subgraph LLM_Cloud["🌐 Cloud LLM API (NVIDIA / Gemini / OpenAI)"]
        AI1[Menerima request prompt terstruktur]
        AI2[Generasi inferensi teks berbasis konteks data sekolah]
        AI3[Kirim balik respons completion]
    end

    U1 --> U2 --> U3 --> C1 --> C2 --> C3
    C3 <--> D_Profile
    C3 <--> D_Abs
    C3 <--> D_Jadwal
    C3 --> C4 --> C5 --> C6 --> C7
    C7 <--> D_AiConfig
    C7 --> C8 --> C9
    C9 --> AI1 --> AI2 --> AI3 --> C10
    C10 --> C11 --> C12
    C12 <--> D_ChatHistory
    C12 --> C13 --> U4 --> EndAI((● Selesai))
```

---

## 5. ACTIVITY DIAGRAM 5: SINKRONISASI STATUS ALPHA OTOMATIS (AUTO-ALPHA)

Diagram ini mengilustrasikan proses otomatisasi sistem dalam mengidentifikasi siswa dan guru yang belum hadir pada hari aktif sekolah dan mencatatnya sebagai status Alpha secara otomatis.

```mermaid
flowchart TD
    StartAlpha((● Mulai)) --> T1[Pemicu: Waktu sistem melewati jam cutoff 07:00 WIB / Akses Dashboard Admin]

    subgraph Cron_System["⚙️ Scheduler / Admin Controller Core"]
        T1
        T2[Ambil parameter tanggal hari ini Y-m-d]
        
        %% Alpha Siswa
        T3[Jalankan query sub-select autoDataAlphaSiswa]
        T4[Filter siswa yang id-nya TIDAK ADA di absensi_siswa tanggal ini]
        T5[Insert batch: status = 3 Alpha, jam = '07:00:00']
        
        %% Alpha Guru
        T6[Jalankan query sub-select autoDataAlphaGuru]
        T7[Filter guru yang id-nya TIDAK ADA di absensi_guru tanggal ini]
        T8[Insert batch: status = 'alpa', jam_masuk = '07:00:00']
        
        T9[Selesai mencatat status Alpha awal]
    end

    subgraph DB_Storage["🗄️ Basis Data"]
        T_Siswa[(siswa)]
        T_Guru[(guru)]
        T_AbsSiswa[(absensi_siswa)]
        T_AbsGuru[(absensi_guru)]
    end

    subgraph Recovery_Flow["🔄 Pembaruan Status saat Hadir Terlambat"]
        R1[Siswa / Guru yang berstatus Alpha melakukan scan QR di kamera]
        R2[Sistem mendeteksi status sebelumnya = 3 Alpha]
        R3[Sistem memperbarui UPDATE status menjadi Hadir / Terlambat]
        R4[Jam absensi diperbarui ke jam aktual saat scan]
        R5[Kirim Notifikasi WhatsApp pembaruan kehadiran]
    end

    T1 --> T2 --> T3
    T3 <--> T_Siswa
    T3 <--> T_AbsSiswa
    T3 --> T4 --> T5 --> T_AbsSiswa
    T5 --> T6
    T6 <--> T_Guru
    T6 <--> T_AbsGuru
    T6 --> T7 --> T8 --> T_AbsGuru
    T8 --> T9

    T9 -.->|Kondisional saat siswa/guru tiba di sekolah| R1
    R1 --> R2 --> R3 --> R4 --> R5 --> EndAlpha((● Selesai))
```

---

## 6. ACTIVITY DIAGRAM 6: MANAJEMEN MASTER DATA SISWA & KONTAK ORTU

Diagram ini menggambarkan alur kerja Administrator dalam menginput dan memperbarui data siswa beserta informasi nomor WhatsApp orang tua untuk integrasi notifikasi absensi.

```mermaid
flowchart TD
    StartAdmin((● Mulai)) --> M1[Admin login & membuka menu /admin-siswa]

    subgraph Admin_Lane["👤 Administrator"]
        M1
        M2[Klik tombol 'Tambah Siswa' atau ikon 'Edit']
        M3[Mengisi formulir: NIS, Nama, Kelas, Barcode ID]
        M4[Mengisi Nama Orang Tua / Wali]
        M5[Mengisi Nomor HP / WhatsApp Orang Tua]
        M6[Mengisi Username & Password Akun Siswa]
        M7[Klik tombol 'Simpan' / 'Update']
        M8[Melihat data pada tabel lengkap dengan tombol link hijau WhatsApp wa.me]
    end

    subgraph Controller_Lane["🖥️ AdminSiswaController.php"]
        C1[Validasi Token Keamanan CSRF]
        C2[Sanitasi input teks & nama ortu]
        C3[Sanitasi angka nomor WhatsApp preg_replace non-digit]
        C4{Validasi Duplikasi NIS pada Siswa Lain?}
        C5{Validasi Duplikasi Username pada Pengguna Lain?}
        C6[Sinkronisasi user_id di tabel users]
        C7[Generate Barcode ID unik otomatis jika kosong]
        C8[Simpan / Update record ke tabel siswa]
        C9[Set Flash Success: 'Data Siswa Berhasil Disimpan']
        C_Err[Set Flash Error: Pesan Duplikasi Data]
        C_Redirect[Redirect kembali ke /admin-siswa]
    end

    subgraph DB_Admin["🗄️ Database"]
        D_Users[(users)]
        D_Siswa[(siswa)]
        D_Kelas[(kelas)]
    end

    M1 --> M2 --> M3 --> M4 --> M5 --> M6 --> M7 --> C1
    C1 --> C2 --> C3 --> C4
    C4 -- NIS Sudah Terdaftar --> C_Err --> C_Redirect
    C4 -- NIS Aman --> C5
    C5 -- Username Sudah Digunakan --> C_Err
    C5 -- Username Aman --> C6
    C6 <--> D_Users
    C6 --> C7 --> C8
    C8 <--> D_Siswa
    C8 <--> D_Kelas
    C8 --> C9 --> C_Redirect --> M8 --> EndAdmin((● Selesai))
```
