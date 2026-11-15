# 📊 Dokumentasi UML Sequence Diagram — Sistem Presensi & Absensi QR
**SMK NU WARGABINANGUN**  
*Arsitektur Runtime Alur Presensi Universal QR, Fonnte WhatsApp Gateway & AI Chatbot Live DB Context (RAG)*

---

## 📌 Ringkasan Eksekutif

Sequence Diagram ini memodelkan interaksi dinamis antar-objek dan komponen perangkat lunak pada aplikasi web **Sistem Absensi SMK NU WARGABINANGUN** secara runut waktu (*time-ordered sequence*). 

Sistem ini dirancang berbasis arsitektur **PHP 8.2 Native MVC** dengan dukungan basis data relasional **MariaDB InnoDB**, integrasi gateway pihak ketiga **Fonnte WhatsApp API**, serta asistensi kecerdasan buatan (*AI Chatbot*) yang mengimplementasikan **Live Database Context Injection (RAG)**.

Diagram ini memvisualisasikan dua skenario operasional inti:
1. **Skenario 1:** Pemindaian Universal QR/Barcode Siswa & Guru, Evaluasi Jam Masuk (Cutoff 07:00:00 WIB), Pencegahan Duplikasi Masuk/Pulang Guru (Cooldown 10 Menit), dan Notifikasi Otomatis WhatsApp ke Orang Tua / Guru.
2. **Skenario 2:** Konsultasi Cerdas AI Chatbot dengan Injeksi Basis Data Nyata (*Live DB Context RAG*) bebas halusinasi.

---

## 🖼️ Berkas Diagram

| Format Berkas | Deskripsi | Tautan Akses |
| :--- | :--- | :--- |
| **PNG (High Definition)** | Gambar render visual siap pakai resolusi tinggi (1680 × 2120 px) | [sequence_diagram.png](file:///c:/xampp/htdocs/sistemabsensi/sequence_diagram.png) |
| **SVG (Vector Graphic)** | Gambar vektor tanpa pecah, tajam di semua level pembesaran | [sequence_diagram.svg](file:///c:/xampp/htdocs/sistemabsensi/sequence_diagram.svg) |
| **HTML Interaktif** | Penampil web dengan kontrol Pan, Zoom in/out, Reset, dan Cetak | [sequence_diagram.html](file:///c:/xampp/htdocs/sistemabsensi/sequence_diagram.html) |

---

## 🛠️ Pemetaan Lifeline / Partisipan ke Kode Sumber

| Partisipan (Lifeline) | Tipe UML | Peran & Tanggung Jawab | File Sumber Riil |
| :--- | :--- | :--- | :--- |
| `Siswa / Guru` / `Pengguna` | **Actor** | Entitas manusia yang berinteraksi memindai kartu atau mengirimkan pesan prompt AI. | Pengguna Fisik |
| `Scanner Kamera (Web)` / `Floating Chat Widget` | **Boundary / View** | Antarmuka pengguna penangkap stream video webcam, dekoder QR/Barcode, dan widget obrolan interaktif. | `views/absensi/scan.php`, `assets/js/html5-qrcode.min.js`, `views/partials/chatbot_widget.php` |
| `AbsensiController` / `ChatbotController` | **Control** | Pengontrol logika alur kerja, validasi token CSRF, perhitungan timestamp, dan koordinator query. | `controllers/AbsensiController.php`, `proses_absen.php`, `controllers/ChatbotController.php` |
| `Database MariaDB` | **Database** | Repositori persisten MySQL/MariaDB penyimpan tabel siswa, guru, absensi, dan riwayat obrolan. | `config/database.php`, `models/AbsensiModel.php`, `models/SiswaModel.php` |
| `Fonnte WA Gateway` | **External Service** | Layanan penyedia API pesan instan WhatsApp otomatis berbasis cURL HTTPS. | Endpoint `https://api.fonnte.com/send` |
| `Ponsel Ortu / Guru` | **Actor (Receiver)** | Penerima pesan teks notifikasi real-time di perangkat seluler. | Aplikasi WhatsApp Android/iOS |
| `AiService (RAG Client)` | **Service** | Penyusun prompt sistem yang menginjeksikan data riil sekolah ke model kecerdasan buatan. | `app/services/AiService.php` |
| `Cloud LLM Engine` | **External Service** | Mesin komputasi model bahasa besar (OpenAI / Gemini / NVIDIA NIM) penghasil respons generatif cerdas. | API Provider Cloud LLM |

---

## ⚡ Skenario 1: Universal QR Scan, Jam Masuk, & Notifikasi WhatsApp Real-Time

```
Siswa/Guru          Scanner Web           AbsensiController         Database MariaDB      Fonnte Gateway      Ponsel Ortu/Guru
    |                     |                       |                         |                   |                    |
    |-- 1: Tunjukkan QR ->|                       |                         |                   |                    |
    |                     |-- 2: Dekode Kamera -->|                         |                   |                    |
    |                     |-- 3: POST Scan ------>|                         |                   |                    |
    |                     |                       |-- 4: Validate CSRF ---->|                   |                    |
    |                     |                       |-- 5: SELECT Siswa ----->|                   |                    |
    |                     |                       |<- 6: Data Siswa --------|                   |                    |
    |                     |                       |                                             |                    |
    |                     |                   [alt: Kode Ditemukan di Siswa]                    |                    |
    |                     |                       |-- 7: Cek Jam Masuk ---->|                   |                    |
    |                     |                       |-- 8: INSERT Absensi --->|                   |                    |
    |                     |                       |<- 9: Sukses Simpan -----|                   |                    |
    |                     |                       |-- 10: cURL POST WA ------------------------>|                    |
    |                     |                       |                                             |-- 11: Notif Masuk->|
    |                     |                       |<- 12: JSON Success -------------------------|                    |
    |                     |                   [else: Kode Ditemukan di Guru]                    |                    |
    |                     |                       |-- 13: Cek Cooldown 10m->|                   |                    |
    |                     |                       |<- 14: Simpan Masuk/Plg -|                   |                    |
    |                     |                       |-- 15: cURL POST WA ------------------------>|                    |
    |                     |                       |                                             |-- 16: Notif Masuk->|
    |                     |                       |<- 17: JSON Success -------------------------|                    |
    |                     |<- 18: JSON Feedback --|                                                                  |
    |<-- 19: Audio Beep --|                                                                                          |
```

### Penjelasan Rinci Alur Langkah (1 - 19):
1. **Langkah 1:** Siswa atau Guru menunjukkan kartu fisik (berisi QR Code / Barcode ID) ke arah lensa webcam/kamera smartphone.
2. **Langkah 2:** Pustaka JavaScript `Html5-Qrcode` di sisi peramban memproses frame video secara *real-time* hingga barcode ID berhasil terbaca.
3. **Langkah 3:** Peramban mengirimkan HTTP POST secara asinkron (AJAX Fetch) ke `AbsensiController` (`/absensi/scanSiswa` atau `proses_absen.php`) disertai data `barcode_id`, status radio, dan `csrf_token`.
4. **Langkah 4:** Controller melakukan validasi keamanan token CSRF dan mengambil timestamp server saat ini (`date('H:i:s')`).
5. **Langkah 5:** Controller menjalankan kueri SQL berparameter:
   ```sql
   SELECT id, nis, nama, kelas_id, nama_ortu, no_hp_ortu FROM siswa WHERE barcode_id = :code LIMIT 1;
   ```
6. **Langkah 6:** MariaDB mengembalikan baris data siswa yang cocok.
7. **Langkah 7 (Fragment `alt` Siswa):** Controller mengevaluasi jam server terhadap ambang batas toleransi kedatangan:
   - Apabila jam saat scan $\le$ `07:00:00 WIB`, maka status kehadiran otomatis diset **Hadir**.
   - Apabila jam saat scan $>$ `07:00:00 WIB`, maka status kehadiran otomatis diset **Terlambat**.
8. **Langkah 8:** Controller menjalankan prosedur `tambahOrUpdateAbsensi()` ke tabel `absensi_siswa` mencatat tanggal, jam masuk, dan status kehadiran.
9. **Langkah 9:** MariaDB mengonfirmasi penyimpanan berhasil dan menerbitkan ID absensi.
10. **Langkah 10:** Controller menyusun template pesan WhatsApp resmi berkop **SMK NU WARGABINANGUN** dan memicu cURL HTTPS POST non-blocking ke Fonnte API (`https://api.fonnte.com/send`):
    - Header: `Authorization: <FONNTE_TOKEN>`
    - Payload: `target: <no_hp_ortu>`, `message: "Pemberitahuan: Putra/i Anda [Nama] telah hadir di sekolah pukul [Jam] dengan status [Hadir/Terlambat]."`
11. **Langkah 11:** Gateway Fonnte mendistribusikan pesan teks terenkripsi langsung ke nomor WhatsApp orang tua murid.
12. **Langkah 12:** Fonnte mengembalikan status JSON `{ "status": true, "target": "628...", "message_id": 1042 }`.
13. **Langkah 13 (Fragment `else` Guru):** Jika barcode_id merupakan milik Guru:
    - Sistem memeriksa riwayat absensi guru hari ini.
    - Sistem menguji apakah sudah melewati batas *cooldown* minimal **10 menit** dari jam masuk agar tidak terjadi *double scan*.
14. **Langkah 14:** Basis data mencatat Jam Masuk (jika scan pertama) atau memperbarui Jam Pulang (jika scan kedua di atas jeda 10 menit).
15. **Langkah 15:** Controller memicu cURL POST ke Fonnte API dengan target nomor WhatsApp pribadi Guru yang bersangkutan.
16. **Langkah 16:** Konfirmasi jam kedatangan/kepulangan masuk ke ponsel guru.
17. **Langkah 17:** Gateway Fonnte mengembalikan respons HTTP 200 OK.
18. **Langkah 18:** Controller merespons panggilan AJAX kamera dengan payload JSON sukses:
    ```json
    {
      "status": "success",
      "nama": "Ahmad Fauzi",
      "role": "siswa",
      "status_teks": "Hadir Tepat Waktu",
      "jam": "06:48:12"
    }
    ```
19. **Langkah 19:** Antarmuka kamera memunculkan popup notifikasi hijau/kuning dan memutar audio nada *beep* sukses, kemudian kamera kembali siaga untuk pemindaian kartu berikutnya.

---

## 🤖 Skenario 2: Konsultasi AI Chatbot Berbasis Live DB Context (RAG)

```
Pengguna             Chat Widget          ChatbotController         MariaDB Live DB        AiService (RAG)       Cloud LLM
   |                      |                       |                         |                   |                    |
   |-- 1: Ketik Prompt -->|                       |                         |                   |                    |
   |                      |-- 2: AJAX POST ------>|                         |                   |                    |
   |                      |                       |-- 3: Validasi Sesi ---->|                   |                    |
   |                      |                       |-- 4: Kueri SQL Riil --->|                   |                    |
   |                      |                       |<- 5: Data Faktual ------|                   |                    |
   |                      |                       |-- 6: chatWithContext() -------------------->|                    |
   |                      |                       |                                             |-- 7: Susun System->|
   |                      |                       |                                             |-- 8: HTTPS POST -->|
   |                      |                       |                                             |<- 9: JSON Choice --|
   |                      |                       |<- 10: Teks Bebas Halusinasi ----------------|                    |
   |                      |                       |-- 11: INSERT chat_log ->|                   |                    |
   |                      |                       |<- 12: Log Tersimpan ----|                   |                    |
   |                      |<- 13: JSON Reply -----|                                                                  |
   |<-- 14: Render Teks --|                                                                                          |
```

### Penjelasan Rinci Alur Langkah (1 - 14):
1. **Langkah 1:** Pengguna (Siswa/Guru/Admin) membuka widget asisten cerdas di pojok layar dan mengetik pertanyaan natural (contoh: *"Berapa kali saya hadir bulan ini dan apa jadwal mengajar saya hari ini?"*).
2. **Langkah 2:** Skrip `chatbot.js` mengirimkan pesan via AJAX POST ke `/chatbot/send`.
3. **Langkah 3:** `ChatbotController` memvalidasi otentikasi sesi aktif pengguna (`user_id`, nama lengkap, kelas, role).
4. **Langkah 4:** Controller mengeksekusi kueri SQL live ke MariaDB untuk merangkum riwayat kehadiran aktual pengguna pada bulan berjalan serta jadwal mata pelajaran hari ini.
5. **Langkah 5:** MariaDB mengembalikan data statistik riil (contoh: `Hadir: 18, Terlambat: 2, Izin: 1, Alpha: 0`, jadwal: `Pemrograman Web - Jam 08.00`).
6. **Langkah 6:** Controller memanggil `AiService::chatWithContext($userMessage, $liveDbContext)`.
7. **Langkah 7:** `AiService` menyusun *System Prompt Augmented* yang mengombinasikan instruksi peran asisten SMK NU WARGABINANGUN dengan data faktual hasil kueri database.
8. **Langkah 8:** `AiService` mengirimkan permintaan HTTPS POST berkecepatan tinggi ke endpoint Cloud LLM API.
9. **Langkah 9:** Mesin LLM mengembalikan inferensi teks bahasa alami yang akurat sesuai data riil pengguna tanpa halusinasi.
10. **Langkah 10:** `AiService` menyaring dan mengekstrak balasan teks bersih untuk diserahkan kembali ke Controller.
11. **Langkah 11:** Controller menyimpan percakapan tanya-jawab ke dalam tabel `chat_history`.
12. **Langkah 12:** Basis data MariaDB mengonfirmasi log percakapan telah tercatat.
13. **Langkah 13:** Controller merespons peramban dengan JSON status sukses berisi balasan asisten AI.
14. **Langkah 14:** Antarmuka obrolan merender gelembung pesan dengan efek animasi mengetik (*typewriter animation*) interaktif.

---

## 📐 Simbol & Notasi Standar UML yang Digunakan

1. **Lifeline (Garis Hidup Objek):** Garis putus-putus vertikal berwarna abu-abu yang merepresentasikan eksistensi suatu komponen sepanjang waktu eksekusi.
2. **Synchronous Message (Panah Biru Pekat):** Pemanggilan fungsi atau permintaan HTTP pemblokir (*blocking call*) di mana pemanggil menunggu balasan sebelum melanjutkan.
3. **Return Message (Panah Garis Putus-Putus Abu-abu):** Pengembalian data atau respons nilai dari penerima kembali ke pemanggil.
4. **Asynchronous / Webhook Message (Panah Ungu/Hijau):** Pengiriman pesan tanpa menunggu pemblokiran proses utama (seperti pengiriman cURL WhatsApp ke Fonnte Gateway).
5. **Self-Call (Panah Melingkar ke Diri Sendiri):** Operasi internal yang dijalankan oleh objek tersebut (seperti evaluasi cutoff waktu atau validasi CSRF).
6. **Combined Fragment `alt` (Alternatif Bersyarat):** Kotak pembatas dengan label `[kondisi]` dan pemisah `[else]` yang merepresentasikan percabangan alur logika (*if-else branching*).
