# UML ACTIVITY DIAGRAM (SWIMLANE 4 JALUR)
## SISTEM INFORMASI PRESENSI & ABSENSI SISWA & GURU
### SMK NU WARGABINANGUN

---

## 📌 BERKAS DIAGRAM RESMI
- **Format Gambar Resolusi Tinggi (PNG):** [`activity_diagram_swimlane.png`](./activity_diagram_swimlane.png)
- **Format Gambar Vektor (SVG):** [`activity_diagram_swimlane.svg`](./activity_diagram_swimlane.svg)
- **Penampil Interaktif (HTML dengan Pan & Zoom):** [`activity_diagram_swimlane.html`](./activity_diagram_swimlane.html) atau [`activity_diagram.html`](./activity_diagram.html)

---

## 🏛️ STRUKTUR SWIMLANE (4 JALUR ENTITAS)

Diagram aktivitas ini dirancang menggunakan standar UML dengan pemisahan tanggung jawab (*Separation of Concerns*) ke dalam 4 swimlane vertikal:

| No | Swimlane (Jalur) | Warna Header | Entitas / Aktor | Tanggung Jawab Utama |
| :-: | :--- | :---: | :--- | :--- |
| **1** | **Siswa & Guru** | 🔵 *Biru* | Pengguna / Peserta Didik & Pendidik | Login akun, melihat kartu QR pribadi, interaksi AI Chatbot, memindai presensi QR/barcode di gerbang, dan presensi mandiri guru. |
| **2** | **Sistem Absensi** | 🟠 *Oranye* | Web Engine, MySQL & Gateway | Validasi otentikasi Bcrypt, pengenalan QR kamera (Html5-Qrcode), logika status jam 07:00 WIB, cooldown jam pulang guru 10 menit, scheduler Auto-Alpha, injeksi data database riil (RAG), dan pengiriman cURL ke Fonnte WA API & LLM API. |
| **3** | **Admin (Tata Usaha)** | 🟢 *Hijau* | Operator / Staf Tata Usaha | Mengelola seluruh master data (siswa + nomor HP orang tua, guru + no WA, kelas, jadwal), generate kartu QR unik, cetak kartu massal (*Batch Print*), dan setting kredensial API. |
| **4** | **Wali Kelas, Kepsek & Ortu** | 🟣 *Berry/Pink* | Pihak Monitoring & Penerima Notifikasi | Menerima notifikasi WhatsApp presensi real-time (Orang Tua), menerima konfirmasi WA masuk/pulang (Guru), monitoring kelas binaan & validasi dispensasi (Wali Kelas), serta analisis tren kedisiplinan eksekutif (Kepala Sekolah). |

---

## 🔄 ALUR KERJA LENGKAP DARI AWAL HINGGA AKHIR

```
[Mulai: Siswa/Guru]
       │
       ▼
[Akses Web & Input Kredensial] ──► [Validasi Bcrypt & CSRF: Sistem]
                                              │
                                              ▼
                                    ◄── [Valid?] ──► [Cek Role?]
                                         (Tidak)          │
       ┌──────────────────────────────────────────────────┼────────────────────────────────┐
       │ (Siswa/Guru)                                     │ (Admin)                        │ (Wali Kelas)
       ▼                                                  ▼                                ▼
[Dashboard Siswa/Guru]                          [Dashboard Admin]               [Dashboard Wali Kelas]
       │                                                  │                                │
       ├─► [Tanya AI?]                                    ├─► [Monitoring Gerbang]         └─► [Rekap Binaan]
       │     ├─► (Ya) ──► [Live DB RAG + LLM API]         └─► [Input Dispensasi]
       │     └─► (Tidak)
       ▼
[Pilih Scan QR / Mandiri]
       │
       ▼
[Dekode Kamera: Sistem]
       │
       ▼
[Pengguna: Siswa vs Guru?]
 ├─► Siswa: [Jam <= 07:00 WIB?] ──► Hadir (0) / Terlambat ──► Simpan `absensi_siswa`
 └─► Guru:  [Scan 1 vs 2?]       ──► Masuk / Pulang (Cooldown 10m) ──► Simpan `absensi_guru`
                                              │
                                              ▼
                               [Trigger Fonnte WhatsApp Gateway]
                                              │
                    ┌─────────────────────────┴─────────────────────────┐
                    ▼ (Async Webhook WA)                                ▼ (Layar Kamera)
  [Pesan WA Masuk ke Ponsel Ortu / Guru]                 [Popup Visual & Beep Sukses]
                    │
                    ▼
  [Scheduler Auto-Alpha Jam 07:00:00 WIB]
  (Sistem otomatis tandai siswa tanpa keterangan jadi Alpha)
                    │
                    ▼
  [Wali Kelas & Kepsek: Verifikasi Izin & Pantau Statistik]
                    │
                    ▼
  [Rekapitulasi Bulanan & Ekspor Laporan Excel/PDF: Sistem]
                    │
                    ▼
                (Selesai)
```

---

## 📋 KAMUS SIMBOL UML DIAGRAM

1. **Titik Awal (Start Node):** Lingkaran hitam solid (`●`) yang menandai awal interaksi pengguna saat membuka aplikasi web sistem absensi.
2. **Aktivitas / Proses (Activity Node):** Persegi panjang bersudut tumpul (*rounded rectangle*) dengan warna latar pastel sesuai jalur swimlane dan teks penjelas alur fungsional.
3. **Keputusan (Decision Node):** Belah ketupat kuning keemasan yang mengevaluasi kondisi logika bisnis:
   - `Valid?`: Memeriksa apakah kombinasi username/password dan token CSRF cocok.
   - `Role?`: Mengarahkan pengguna ke dasbor spesifik (`siswa`, `guru`, `admin`, `wali_kelas`).
   - `Tanya AI?`: Percabangan opsional untuk membuka asisten cerdas AI Chatbot.
   - `Pengguna?`: Memisahkan jalur pemrosesan data antara Siswa dan Guru.
   - `Jam <= 07:00?`: Menentukan status kehadiran Siswa apakah tepat waktu (Hadir) atau melewati batas jam sekolah (Terlambat).
   - `Scan Ke-?`: Menentukan apakah presensi guru dicatat sebagai Jam Masuk atau Jam Kepulangan (dengan validasi jeda waktu minimal 10 menit).
4. **Alur Kontrol (Control Flow):** Garis panah solid (`──►`) yang menghubungkan urutan eksekusi antar langkah.
5. **Alur Notifikasi & Integrasi API (Object / Asynchronous Flow):** Garis panah putus-putus (`- - -►`) yang menggambarkan komunikasi eksternal non-blocking ke Fonnte WhatsApp API Gateway dan OpenAI/NVIDIA LLM API.
6. **Titik Akhir (End Node):** Lingkaran ganda (*bullseye*) hitam-putih (`◉`) yang menandai selesainya siklus operasional harian presensi dan pelaporan.
