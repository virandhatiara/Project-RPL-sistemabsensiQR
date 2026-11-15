# UML CLASS DIAGRAM — ARSITEKTUR 3-TIER MVC
## SISTEM INFORMASI PRESENSI & ABSENSI SISWA & GURU
### SMK NU WARGABINANGUN

---

## 📌 BERKAS DIAGRAM RESMI
- **Format Gambar Resolusi Tinggi (PNG):** [`class_diagram.png`](./class_diagram.png) *(Resolusi 1800 × 2320 px)*
- **Format Vektor Grafis (SVG):** [`class_diagram.svg`](./class_diagram.svg) *(Skalabilitas tajam bebas pecah)*
- **Penampil Interaktif (HTML dengan Pan & Zoom):** [`class_diagram.html`](./class_diagram.html)

---

## 🏛️ ARSITEKTUR SISTEM (3-TIER MVC)

Sistem Absensi SMK NU WARGABINANGUN dibangun menggunakan pola desain **Model-View-Controller (MVC)** murni berbasis PHP 8.2 Native dan MariaDB InnoDB. Diagram kelas ini memetakan seluruh komponen perangkat lunak ke dalam 3 layer utama:

```
┌──────────────────────────────────────────────────────────────────────────────┐
│                    LAYER 1: CORE & SERVICE PROVIDERS                         │
│  [Database]  [App / Router]  [Csrf]  [AiService]  [FonnteWhatsApp] [Flasher] │
└──────────────────────────────────────┬───────────────────────────────────────┘
                                       │ (Dependency / Service Calls)
┌──────────────────────────────────────▼───────────────────────────────────────┐
│                    LAYER 2: APPLICATION CONTROLLERS                          │
│                             [Controller] (Base)                              │
│                                      │                                       │
│    ┌──────────────┬──────────────┬───┴──────────┬──────────────┬─────────┐   │
│ [AuthController] [Absensi] [AdminSiswa] [AdminGuru] [AdminBarcode] [Chatbot] │
│ [GuruController] [SiswaController] [WaliKelas] [KepalaSekolah] [AdminKelas]   │
└──────────────────────────────────────┬───────────────────────────────────────┘
                                       │ (Uses / Interacts)
┌──────────────────────────────────────▼───────────────────────────────────────┐
│                    LAYER 3: DOMAIN ENTITIES & MODELS                         │
│   [User] ──(1:0..1)──► [SiswaModel] ──(1:N)──► [AbsensiSiswaModel]          │
│   [User] ──(1:0..1)──► [GuruModel]  ──(1:N)──► [AbsensiGuruModel]           │
│   [GuruModel] ──(1:0..1)──► [KelasModel] ──(1:N)──► [SiswaModel]             │
│   [KelasModel] & [GuruModel] ──(1:N)──► [JadwalModel]                        │
│   [User] ──(1:N)──► [ChatbotModel]  &  [AiSettingModel]                      │
└──────────────────────────────────────────────────────────────────────────────┘
```

---

## 📦 DETAIL TIER & SPESIFIKASI KELAS

### 1. LAYER 1: CORE & SERVICE PROVIDERS
Layer ini bertindak sebagai fondasi infrastruktur sistem dan antarmuka integrasi pihak ketiga:

1. **`Database` (Core / Singleton)**:
   - *Atribut:* `- host`, `- user`, `- pass`, `- db_name`, `- dbh: PDO`, `- stmt: PDOStatement`
   - *Metode:* `+ query(query)`, `+ bind(param, val, type)`, `+ execute()`, `+ resultSet()`, `+ single()`, `+ rowCount()`
   - *Fungsi:* Mengelola koneksi database persisten menggunakan driver PDO dengan *Prepared Statements* untuk mencegah injeksi SQL.

2. **`App` (Front Controller Router)**:
   - *Atribut:* `# controller: string`, `# method: string`, `# params: array`
   - *Metode:* `+ __construct()`, `+ parseUrl(): array`
   - *Fungsi:* Menangkap dan mem-parsing parameter URL `GET['url']` untuk mendispatch permintaan ke Controller dan Method yang sesuai.

3. **`Csrf` (Security Token Helper)**:
   - *Atribut:* `- sessionKey: string = 'csrf_token'`
   - *Metode:* `+ getToken(): string`, `+ validate(): bool`, `+ getInput(): string`
   - *Fungsi:* Menghasilkan dan memverifikasi token anti-Cross-Site Request Forgery pada setiap transaksi formulir POST.

4. **`AiService` (Service Provider)**:
   - *Atribut:* `- apiKey`, `- apiUrl`, `- model`, `- provider`, `- temperature`, `- maxTokens`
   - *Metode:* `+ chat(messages)`, `+ injectLiveDbContext(userId)`, `+ fetchAvailableModels()`
   - *Fungsi:* Menghubungkan sistem ke provider LLM (NVIDIA NIM / Gemini / OpenAI) dan melakukan injeksi data presensi & jadwal riil (RAG).

5. **`FonnteWhatsAppGateway` (External Service)**:
   - *Atribut:* `- token`, `- apiUrl`, `- timeout: int = 10`
   - *Metode:* `+ kirimNotifikasiOrtu(noHp, pesan)`, `+ kirimKonfirmasiGuru(noWa, pesan)`, `- sendHttpRequest(postData)`
   - *Fungsi:* Menangani pengiriman pesan notifikasi WhatsApp resmi secara real-time melalui webhook Fonnte API.

6. **`Flasher` (UI Helper)**:
   - *Metode:* `+ setFlash(pesan, aksi, tipe)`, `+ flash()`
   - *Fungsi:* Menyimpan dan menampilkan pesan notifikasi sesi visual (popup alert).

---

### 2. LAYER 2: APPLICATION CONTROLLERS (MVC)
Layer ini mengendalikan alur logika bisnis, otorisasi peran, pemrosesan formulir, dan pemanggilan view:

1. **`Controller` (Abstract Base Class)**:
   - Seluruh controller mewarisi (*inherits*) kelas ini.
   - *Metode:* `+ view(view, data)`, `+ model(model)`, `+ redirect(url)`, `+ checkAuth(allowedRoles)`
   - *Fungsi:* Menyediakan fungsi render template HTML, instantiasi model, proteksi sesi, dan pengecekan Role-Based Access Control (RBAC).

2. **`AuthController`**:
   - *Metode:* `+ login()`, `+ prosesLogin()`, `+ logout()`
   - *Fungsi:* Memverifikasi kata sandi Bcrypt pengguna dan mengarahkan ke dashboard yang relevan sesuai role.

3. **`AbsensiController`**:
   - *Metode:* `+ siswa()`, `+ guru()`, `+ scanSiswa()`, `+ scanGuru()`, `- processSiswaScan(barcode, status)`, `- processGuruScan(barcode)`, `- kirimNotifikasiWhatsApp(no, msg)`, `+ syncAutoAlpha()`
   - *Fungsi:* Inti pemrosesan scanner kamera QR/Barcode, logika penentuan jam masuk ($\le$ 07:00 Hadir vs Terlambat), cooldown jam pulang guru 10 menit, serta pemicu webhook notifikasi WhatsApp.

4. **`AdminSiswaController` & `AdminGuruController`**:
   - *Metode:* `+ index()`, `+ create()`, `+ store()`, `+ edit(id)`, `+ update(id)`, `+ delete(id)`
   - *Fungsi:* Operasi CRUD master data siswa (termasuk kolom nama dan no. HP WhatsApp orang tua) dan master guru (termasuk no. WhatsApp pribadi).

5. **`AdminBarcodeController`**:
   - *Metode:* `+ index()`, `+ assign(type, id)`, `+ printCard(type, id)`, `+ batchPrint(type, kelasId)`, `+ generateUniqueCode(type)`
   - *Fungsi:* Generator kode unik QR dan fasilitas pencetakan kartu fisik siswa/guru secara massal per kelas.

6. **`ChatbotController`**:
   - *Metode:* `+ send()`, `+ history()`, `+ clearHistory()`, `+ getModels()`
   - *Fungsi:* Mengelola sesi obrolan interaktif asisten AI di pojok layar pengguna.

7. **`GuruController`, `SiswaController`, `WaliKelasController`, `KepalaSekolahController`**:
   - Menyediakan fitur portal khusus peran: presensi mandiri guru, unduh kartu QR pribadi siswa, monitoring kelas binaan wali kelas, dan laporan statistik eksekutif kepala sekolah.

---

### 3. LAYER 3: DOMAIN ENTITIES & MODELS (DATA ACCESS)
Layer ini merepresentasikan tabel-tabel database MariaDB InnoDB dan memuat fungsi manipulasi data:

| Nama Kelas Model | Tabel Database | Primary Key & Foreign Keys | Deskripsi Tanggung Jawab Data |
| :--- | :--- | :--- | :--- |
| **`User`** | `users` | `PK: id`<br>`UK: username` | Menyimpan data autentikasi login, hash password Bcrypt, dan role pengguna (`admin`, `guru`, `siswa`, `wali_kelas`, `kepala_sekolah`). |
| **`SiswaModel`** | `siswa` | `PK: id`<br>`FK: user_id -> users`<br>`FK: kelas_id -> kelas`<br>`UK: nis, barcode_id` | Identitas siswa, relasi akun pengguna, rombel kelas, serta kontak resmi wali murid (`nama_ortu`, `no_hp_ortu`). |
| **`GuruModel`** | `guru` | `PK: id`<br>`FK: user_id -> users`<br>`UK: nip, barcode_id` | Identitas tenaga pendidik, relasi akun login, nomor barcode unik, dan nomor WhatsApp pribadi (`no_wa`, `no_hp`). |
| **`KelasModel`** | `kelas` | `PK: id`<br>`FK: wali_kelas_id -> guru`<br>`UK: nama_kelas` | Data rombongan belajar tingkat X, XI, XII dan penetapan guru sebagai wali kelas binaan. |
| **`JadwalModel`** | `jadwal` | `PK: id`<br>`FK: kelas_id -> kelas`<br>`FK: guru_id -> guru` | Jadwal mata pelajaran berdasarkan hari, rentang jam belajar, kelas, dan guru pengampu. |
| **`AbsensiSiswaModel`** | `absensi_siswa` | `PK: id`<br>`FK: siswa_id -> siswa` | Rekam presensi harian siswa (`tanggal`, `jam`, `status: 0=Hadir, 1=Sakit, 2=Izin, 3=Alpha`) dan logika auto-alpha. |
| **`AbsensiGuruModel`** | `absensi_guru` | `PK: id`<br>`FK: guru_id -> guru` | Rekam presensi harian guru (`tanggal`, `jam_masuk`, `jam_pulang`, `status`). |
| **`ChatbotModel`** | `chat_history` | `PK: id`<br>`FK: user_id -> users` | Riwayat log percakapan tanya-jawab antara pengguna dengan asisten AI. |
| **`AiSettingModel`** | `ai_settings` | `PK: id` | Konfigurasi API Key, URL endpoint, provider aktif (NVIDIA/Gemini/OpenAI), dan model LLM. |

---

## 🔗 RELASI DAN MULTIPLISITAS ANTAR ENTITAS

1. **Generalization / Inheritance (`--|>`):**
   - Seluruh Controller (`AuthController`, `AbsensiController`, `AdminSiswaController`, dll.) diturunkan dari kelas induk `Controller`.
2. **Association & Multiplicity (`1 : N` / `1 : 0..1`):**
   - `User` **1 ──── 0..1** `SiswaModel` *(Satu akun user dapat memiliki satu profil siswa)*.
   - `User` **1 ──── 0..1** `GuruModel` *(Satu akun user dapat memiliki satu profil guru)*.
   - `GuruModel` **1 ──── 0..1** `KelasModel` *(Satu guru dapat ditugaskan menjadi wali kelas di satu rombel)*.
   - `KelasModel` **1 ──── 0..*** `SiswaModel` *(Satu kelas memiliki banyak siswa)*.
   - `KelasModel` **1 ──── 0..*** `JadwalModel` *(Satu kelas memiliki banyak jadwal pelajaran)*.
   - `GuruModel` **1 ──── 0..*** `JadwalModel` *(Satu guru mengajar di banyak jadwal pelajaran)*.
   - `SiswaModel` **1 ──── 0..*** `AbsensiSiswaModel` *(Satu siswa memiliki banyak riwayat presensi harian)*.
   - `GuruModel` **1 ──── 0..*** `AbsensiGuruModel` *(Satu guru memiliki banyak riwayat presensi harian)*.
   - `User` **1 ──── 0..*** `ChatbotModel` *(Satu pengguna memiliki banyak catatan pesan di riwayat chat AI)*.
3. **Dependency (`..>`):**
   - `AbsensiController` bergantung pada layanan eksternal `FonnteWhatsAppGateway` untuk pengiriman pesan instan.
   - `ChatbotController` bergantung pada layanan `AiService` untuk inferensi LLM dan injeksi data database riil.
