<?php
$profil      = $data['profil'] ?? null;
$absensiHari = $data['absensi_hari_ini'] ?? null;
$jadwalHari  = $data['jadwal_hari_ini'] ?? [];
$jadwalSemua = $data['jadwal_semua'] ?? [];
$riwayat     = $data['riwayat'] ?? [];
$rekapBulan  = $data['rekap_bulan'] ?? [];
$tanggalFmt  = isset($data['tanggal']) ? date('d F Y', strtotime($data['tanggal'])) : date('d F Y');
$hari        = $data['hari'] ?? '';
?>

<?php Flasher::flash(); ?>

<?php if (!$profil): ?>
    <div class="alert alert-custom alert-warning">
        <i class="bi bi-exclamation-circle"></i>
        <div>Data profil guru <strong>belum terhubung ke akun ini.</strong> Silakan hubungi Admin untuk menghubungkan akun ke data guru.</div>
    </div>
<?php else: ?>
<?php $noWaGuru = !empty($profil['no_wa']) ? $profil['no_wa'] : (!empty($profil['no_hp']) ? $profil['no_hp'] : null); ?>

<!-- Banner Pengingat Nomor WhatsApp jika belum diisi -->
<?php if (empty($noWaGuru)): ?>
    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center justify-content-between mb-4 rounded-3 p-3 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:42px;height:42px;">
                <i class="bi bi-whatsapp fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0 text-dark">Nomor WhatsApp Absensi Belum Diisi</h6>
                <div class="small text-muted">Tambahkan nomor HP / WhatsApp Anda agar notifikasi kehadiran resmi otomatis langsung masuk ke WhatsApp saat Anda melakukan absensi.</div>
            </div>
        </div>
        <button type="button" class="btn btn-success btn-sm px-3 flex-shrink-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNoHpGuru">
            <i class="bi bi-plus-lg me-1"></i>Masukkan No. HP
        </button>
    </div>
<?php endif; ?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold mb-0">Dashboard Guru</h2>
        <p class="text-muted mb-0"><i class="bi bi-calendar3 me-1"></i><?= $tanggalFmt ?> &bull; <?= e($hari) ?></p>
    </div>
</div>

<!-- Row 1: Profil + Status Absensi Hari Ini -->
<div class="row g-4 mb-4">
    <!-- Kartu Profil -->
    <div class="col-md-4">
        <div class="table-card text-center h-100 py-4">
            <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" style="width:80px;height:80px;border-radius:50%;background:#eff6ff;">
                <i class="bi bi-person-circle text-primary" style="font-size:3rem;"></i>
            </div>
            <h5 class="fw-bold mb-1"><?= e($profil['nama']) ?></h5>
            <p class="text-muted mb-1 small">NIP: <strong><?= e($profil['nip']) ?></strong></p>
            <p class="text-muted small mb-2">Barcode: <code><?= e($profil['barcode_id'] ?? '-') ?></code></p>
            <hr class="my-2">
            <div class="d-flex align-items-center justify-content-between px-3 py-1 text-start">
                <small class="text-muted"><i class="bi bi-upc-scan me-2"></i>Status Barcode</small>
                <span class="badge <?= !empty($profil['barcode_id']) ? 'badge-hadir' : 'badge-alpa' ?>">
                    <?= !empty($profil['barcode_id']) ? 'Terdaftar' : 'Belum Terdaftar' ?>
                </span>
            </div>
            <div class="d-flex align-items-center justify-content-between px-3 py-1 text-start">
                <small class="text-muted"><i class="bi bi-whatsapp me-2 text-success"></i>No. WhatsApp</small>
                <div class="text-end">
                    <?php if (!empty($noWaGuru)): ?>
                        <span class="badge bg-success-subtle text-success fw-semibold">
                            <i class="bi bi-check2-circle me-1"></i><?= e($noWaGuru) ?>
                        </span>
                    <?php else: ?>
                        <span class="badge bg-warning-subtle text-warning fw-semibold">
                            <i class="bi bi-exclamation-triangle me-1"></i>Belum diisi
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="px-3 pt-2 text-center">
                <button type="button" class="btn btn-sm <?= !empty($noWaGuru) ? 'btn-outline-success' : 'btn-success text-white' ?> w-100 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNoHpGuru">
                    <i class="bi bi-whatsapp me-1"></i><?= !empty($noWaGuru) ? 'Ubah Nomor WhatsApp' : 'Tambah Nomor WhatsApp' ?>
                </button>
                <div class="text-muted mt-1" style="font-size: 11px;">
                    <i class="bi bi-bell-fill text-success me-1"></i>Notifikasi absensi dikirim ke WhatsApp ini
                </div>
            </div>
            <hr class="my-3">
            <div class="row text-center g-0 mb-3">
                <div class="col-6">
                    <div class="fw-bold text-primary fs-4"><?= (int)($rekapBulan['hadir_bulan_ini'] ?? 0) ?></div>
                    <div class="text-muted small">Hadir Bulan Ini</div>
                </div>
                <div class="col-6">
                    <div class="fw-bold text-success fs-4"><?= (int)($rekapBulan['sudah_pulang'] ?? 0) ?></div>
                    <div class="text-muted small">Pulang Tepat</div>
                </div>
            </div>
            <hr class="my-3">
            <!-- Kartu QR Code Presensi Guru -->
            <div class="px-3 text-center">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <small class="fw-bold text-muted text-uppercase" style="letter-spacing: 0.5px;">
                        <i class="bi bi-qr-code text-primary me-1"></i>Kode QR Presensi
                    </small>
                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" data-bs-toggle="modal" data-bs-target="#modalQrGuru">
                        <i class="bi bi-arrows-fullscreen me-1"></i>Perbesar
                    </button>
                </div>
                <div class="d-inline-flex p-2 bg-white rounded-3 shadow-sm border mb-2">
                    <div id="qrcode-guru" style="width:130px;height:130px;display:flex;align-items:center;justify-content:center;"></div>
                </div>
                <div class="font-monospace fw-bold text-dark small mb-1"><?= e($profil['barcode_id'] ?: $profil['nip']) ?></div>
                <div class="text-muted" style="font-size:11px;">Tunjukkan kode QR ini ke kamera scanner absensi untuk presensi Masuk / Pulang</div>
            </div>
        </div>
    </div>

    <!-- Status Absensi Hari Ini -->
    <div class="col-md-4">
        <div class="table-card h-100 d-flex flex-column justify-content-center">
            <h6 class="fw-bold mb-3"><i class="bi bi-clock-fill me-2 text-primary"></i>Status Absensi Hari Ini</h6>
            <?php if ($absensiHari && $absensiHari['status'] !== 'alpa'): ?>
                <div class="alert alert-success border-0 d-flex align-items-center mb-3">
                    <i class="bi bi-check-circle-fill me-2 fs-4"></i>
                    <div>
                        <div class="fw-semibold">Sudah Absen</div>
                        <div class="small">Masuk: <strong><?= substr($absensiHari['jam_masuk'], 0, 5) ?></strong> (Status: <?= ucfirst($absensiHari['status']) ?>)</div>
                    </div>
                </div>
                <?php if ($absensiHari['jam_pulang']): ?>
                    <div class="alert alert-info border-0 d-flex align-items-center">
                        <i class="bi bi-door-open-fill me-2 fs-4"></i>
                        <div>
                            <div class="fw-semibold">Sudah Pulang</div>
                            <div class="small">Jam: <strong><?= substr($absensiHari['jam_pulang'], 0, 5) ?></strong></div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning border-0 mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-door-closed-fill me-2 fs-4"></i>
                            <div>
                                <div class="fw-semibold">Belum Presensi Pulang</div>
                                <div class="small text-muted">Klik tombol di bawah saat hendak pulang</div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <form action="<?= e(BASEURL); ?>/guru/absenMandiri" method="POST" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= e(Csrf::getToken()); ?>">
                                <button type="submit" class="btn btn-warning btn-sm fw-semibold shadow-sm">
                                    <i class="bi bi-door-open-fill me-1"></i>Presensi Pulang
                                </button>
                            </form>
                            <a href="<?= e(BASEURL) ?>/absensi/guru" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-camera-video me-1"></i>Scan Kamera
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-state py-3 text-center">
                    <i class="bi bi-x-circle" style="font-size:2.5rem; color:#ef4444;"></i>
                    <p class="mt-2 text-danger fw-semibold mb-1">
                        <?= ($absensiHari && $absensiHari['status'] === 'alpa') ? 'Status: Alpha (Belum Absen)' : 'Belum presensi hari ini' ?>
                    </p>
                    <small class="text-muted d-block mb-3">Segera lakukan presensi kehadiran masuk.</small>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <form action="<?= e(BASEURL); ?>/guru/absenMandiri" method="POST" class="d-inline">
                            <input type="hidden" name="csrf_token" value="<?= e(Csrf::getToken()); ?>">
                            <button type="submit" class="btn btn-success btn-sm px-3 shadow-sm">
                                <i class="bi bi-check2-circle me-1"></i>Presensi Masuk
                            </button>
                        </form>
                        <a href="<?= e(BASEURL) ?>/absensi/guru" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-camera-video me-1"></i>Scan Kamera
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Jadwal Hari Ini -->
    <div class="col-md-4">
        <div class="table-card h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-calendar-day-fill me-2 text-primary"></i>Jadwal Hari Ini (<?= e($hari) ?>)</h6>
            <?php if (!empty($jadwalHari)): ?>
                <?php foreach ($jadwalHari as $j): ?>
                    <div class="d-flex align-items-center p-2 mb-2 rounded-3" style="background:#f8fafc;">
                        <div class="me-3 text-center" style="min-width:50px;">
                            <div class="fw-bold text-primary small"><?= substr($j['jam_mulai'], 0, 5) ?></div>
                            <div class="text-muted" style="font-size:10px;">–</div>
                            <div class="fw-bold text-primary small"><?= substr($j['jam_selesai'], 0, 5) ?></div>
                        </div>
                        <div>
                            <div class="fw-semibold"><?= e($j['nama_kelas']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state py-3">
                    <i class="bi bi-calendar-x" style="font-size:2.5rem;"></i>
                    <p class="mt-2">Tidak ada jadwal hari ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Row 2: Jadwal Seminggu + Riwayat Absensi -->
<div class="row g-4">
    <!-- Jadwal Seminggu -->
    <div class="col-lg-5">
        <div class="table-card h-100" id="jadwal-mengajar">
            <h6 class="fw-bold mb-3"><i class="bi bi-calendar-week me-2 text-primary"></i>Jadwal Mengajar</h6>
            <?php if (!empty($jadwalSemua)): ?>
                <div class="table-responsive">
                    <table class="table custom-table mb-0">
                        <thead><tr><th>Hari</th><th>Kelas</th><th>Jam</th></tr></thead>
                        <tbody>
                        <?php foreach ($jadwalSemua as $j): ?>
                            <tr>
                                <td><span class="badge bg-primary-subtle text-primary"><?= e($j['hari']) ?></span></td>
                                <td class="fw-semibold"><?= e($j['nama_kelas']) ?></td>
                                <td class="text-muted"><?= substr($j['jam_mulai'], 0, 5) ?>–<?= substr($j['jam_selesai'], 0, 5) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state"><i class="bi bi-calendar-x"></i><p>Belum ada jadwal.</p></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Riwayat Absensi Pribadi -->
    <div class="col-lg-7">
        <div class="table-card h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-2 text-primary"></i>Riwayat Absensi Pribadi (15 Terakhir)</h6>
            <?php if (!empty($riwayat)): ?>
                <div class="table-responsive">
                    <table class="table custom-table mb-0">
                        <thead><tr><th>Tanggal</th><th>Masuk</th><th>Pulang</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach ($riwayat as $r): ?>
                            <tr>
                                <td><?= date('d M Y', strtotime($r['tanggal'])) ?></td>
                                <td><?= substr($r['jam_masuk'], 0, 5) ?></td>
                                <td><?= $r['jam_pulang'] ? substr($r['jam_pulang'], 0, 5) : '<span class="text-muted">–</span>' ?></td>
                                <td><span class="badge badge-hadir"><?= ucfirst(e($r['status'])) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state"><i class="bi bi-clipboard-x"></i><p>Belum ada riwayat absensi.</p></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal QR Code Guru Fullscreen / Preview -->
<div class="modal fade" id="modalQrGuru" tabindex="-1" aria-labelledby="modalQrGuruLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalQrGuruLabel">
                    <i class="bi bi-qr-code-scan text-primary me-2"></i>Kode QR Presensi Guru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="d-inline-flex p-3 bg-white rounded-4 shadow border mb-3">
                    <div id="qrcode-guru-modal" style="width:220px;height:220px;display:flex;align-items:center;justify-content:center;"></div>
                </div>
                <h5 class="fw-bold mb-1"><?= e($profil['nama']) ?></h5>
                <p class="text-muted mb-2">NIP: <strong class="text-dark font-monospace"><?= e($profil['nip']) ?></strong> &bull; Tenaga Pendidik</p>
                <div class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill font-monospace fs-6 mb-3">
                    ID: <?= e($profil['barcode_id'] ?: $profil['nip']) ?>
                </div>
                <div class="alert alert-info border-0 rounded-3 py-2 px-3 small text-muted mb-0">
                    <i class="bi bi-info-circle me-1 text-primary"></i> Arahkan layar HP ini ke kamera scanner absensi sekolah. Keterangan <strong>SUDAH ABSEN</strong> akan langsung tercatat dan tampil di layar.
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary px-4" onclick="downloadQrGuru()">
                    <i class="bi bi-download me-1"></i> Unduh Gambar QR
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Atur Nomor HP / WhatsApp Guru -->
<div class="modal fade" id="modalNoHpGuru" tabindex="-1" aria-labelledby="modalNoHpGuruLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-start shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalNoHpGuruLabel">
                    <i class="bi bi-whatsapp text-success me-2"></i>Atur Nomor WhatsApp Absensi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= e(BASEURL) ?>/guru/updateNoHp" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e(Csrf::getToken()); ?>">
                <div class="modal-body pt-3">
                    <div class="alert alert-success bg-success-subtle border-0 d-flex align-items-start gap-2 mb-3">
                        <i class="bi bi-info-circle-fill fs-5 mt-1 text-success"></i>
                        <div class="small text-success-emphasis">
                            Nomor WhatsApp ini digunakan sistem untuk mengirimkan notifikasi presensi otomatis (Jam Masuk dan Jam Pulang) via WhatsApp (Fonnte) ke HP Anda setiap kali Anda melakukan absensi.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="input_no_hp" class="form-label fw-semibold">Nomor HP / WhatsApp Aktif <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-success"><i class="bi bi-whatsapp"></i></span>
                            <input type="tel" class="form-control form-control-lg" id="input_no_hp" name="no_hp" 
                                   value="<?= e($noWaGuru ?? '') ?>" 
                                   placeholder="Contoh: 081234567890" required maxlength="20">
                        </div>
                        <div class="form-text">Mendukung format angka lokal maupun internasional (contoh: <code>081234567890</code> atau <code>6281234567890</code>).</div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4 fw-semibold">
                        <i class="bi bi-check-lg me-1"></i>Simpan Nomor WhatsApp
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= e(BASEURL); ?>/js/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const qrCodeVal = <?= json_encode((string)($profil['barcode_id'] ?: $profil['nip'])) ?>;
    if (!qrCodeVal) return;

    // Render QR Code pada kartu profil
    const elCard = document.getElementById('qrcode-guru');
    if (elCard) {
        new QRCode(elCard, {
            text: qrCodeVal,
            width: 130,
            height: 130,
            colorDark: "#0f172a",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.M
        });
    }

    // Render QR Code pada modal pop-up
    const elModal = document.getElementById('qrcode-guru-modal');
    if (elModal) {
        new QRCode(elModal, {
            text: qrCodeVal,
            width: 220,
            height: 220,
            colorDark: "#0f172a",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    }
});

function downloadQrGuru() {
    const img = document.querySelector('#qrcode-guru-modal img') || document.querySelector('#qrcode-guru img');
    if (img && img.src) {
        const a = document.createElement('a');
        a.href = img.src;
        a.download = 'QR_Guru_<?= preg_replace('/[^a-zA-Z0-9_-]/', '_', $profil['nama']) ?>.png';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }
}
</script>

<?php endif; ?>
