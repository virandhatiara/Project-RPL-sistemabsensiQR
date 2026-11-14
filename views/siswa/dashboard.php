<?php
$profil  = $data['profil'];
$rekap   = $data['rekap'];
$riwayat = $data['riwayat'] ?? [];
$filters = $data['filters'] ?? [];
$jadwalHariIni = $data['jadwal_hari_ini'] ?? [];
$jadwalSemua   = $data['jadwal_semua'] ?? [];
$hari          = $data['hari'] ?? 'Senin';

$hariMap = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];

// Status absensi hari ini
$absensiHariIni = null;
foreach ($riwayat as $r) {
    if ($r['tanggal'] === date('Y-m-d')) { $absensiHariIni = $r; break; }
}
?>

<?php Flasher::flash(); ?>

<!-- Page Header + Profil -->
<div class="row g-4 mb-4">
    <!-- Profil Card -->
    <div class="col-lg-4">
        <div class="table-card text-center py-4 h-100">
            <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" style="width:88px;height:88px;border-radius:50%;background:#eff6ff;">
                <i class="bi bi-person-circle text-primary" style="font-size:3.5rem;"></i>
            </div>
            <h5 class="fw-bold mb-1"><?= e($profil['nama']) ?></h5>
            <p class="text-muted small mb-2">NIS: <strong><?= e($profil['nis']) ?></strong></p>
            <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill mb-3">
                <i class="bi bi-building me-1"></i><?= e($profil['nama_kelas']) ?>
            </span>
            <hr>
            <div class="d-flex align-items-center justify-content-between px-3 py-1 text-start">
                <small class="text-muted"><i class="bi bi-upc-scan me-2"></i>Status Barcode</small>
                <span class="badge <?= $profil['barcode_id'] ? 'badge-hadir' : 'badge-alpa' ?>">
                    <?= $profil['barcode_id'] ? 'Terdaftar' : 'Belum Terdaftar' ?>
                </span>
            </div>
            <div class="d-flex align-items-center justify-content-between px-3 py-1 text-start">
                <small class="text-muted"><i class="bi bi-people me-2"></i>Orang Tua / Wali</small>
                <span class="fw-semibold small text-dark">
                    <?= !empty($profil['nama_ortu']) ? e($profil['nama_ortu']) : '<span class="text-muted fst-italic">Belum diisi</span>' ?>
                </span>
            </div>
            <div class="d-flex align-items-center justify-content-between px-3 py-1 text-start">
                <small class="text-muted"><i class="bi bi-whatsapp me-2 text-success"></i>WA Notifikasi</small>
                <span class="fw-semibold small text-dark">
                    <?php 
                        $noWaOrtu = !empty($profil['no_hp_ortu']) ? $profil['no_hp_ortu'] : (!empty($profil['no_wa']) ? $profil['no_wa'] : null);
                        echo !empty($noWaOrtu) ? e($noWaOrtu) : '<span class="text-muted fst-italic">Belum diisi</span>';
                    ?>
                </span>
            </div>
            <div class="px-3 pt-1 text-start">
                <small class="text-muted fst-italic" style="font-size:10.5px;"><i class="bi bi-info-circle me-1"></i>Data orang tua & no WA dikelola langsung oleh Admin.</small>
            </div>
            <hr class="my-3">
            <!-- Kartu QR Code Presensi Siswa -->
            <div class="px-3 text-center">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <small class="fw-bold text-muted text-uppercase" style="letter-spacing: 0.5px;">
                        <i class="bi bi-qr-code text-primary me-1"></i>Kode QR Presensi
                    </small>
                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" data-bs-toggle="modal" data-bs-target="#modalQrSiswa">
                        <i class="bi bi-arrows-fullscreen me-1"></i>Perbesar
                    </button>
                </div>
                <div class="d-inline-flex p-2 bg-white rounded-3 shadow-sm border mb-2">
                    <div id="qrcode-siswa" style="width:130px;height:130px;display:flex;align-items:center;justify-content:center;"></div>
                </div>
                <div class="font-monospace fw-bold text-dark small mb-1"><?= e($profil['barcode_id'] ?: $profil['nis']) ?></div>
                <div class="text-muted" style="font-size:11px;">Tunjukkan kode QR ini ke kamera scanner absensi untuk presensi otomatis</div>
            </div>
        </div>
    </div>

    <!-- Status Hari Ini + Rekap -->
    <div class="col-lg-8">
        <!-- Status Absensi Hari Ini -->
        <div class="table-card mb-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-calendar-check me-2 text-primary"></i>Status Absensi Hari Ini</h6>
            <?php if ($absensiHariIni && (int)$absensiHariIni['status'] !== 3): ?>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <?php
                    $rawSt = $absensiHariIni['status'];
                    switch ((int)$rawSt) {
                        case 1:
                            $cls = 'warning'; $icon = 'bi-thermometer-half'; $labelSt = 'Sakit';
                            break;
                        case 2:
                            $cls = 'info';    $icon = 'bi-envelope-fill';     $labelSt = 'Izin';
                            break;
                        case 0:
                        default:
                            $cls = 'success'; $icon = 'bi-check-circle-fill'; $labelSt = 'Hadir';
                            break;
                    }
                    ?>
                    <div class="d-flex align-items-center p-3 rounded-3" style="background:#f8fafc;flex:1;min-width:200px;">
                        <i class="bi <?= $icon ?> text-<?= $cls ?> me-3 fs-2"></i>
                        <div>
                            <div class="fw-bold"><?= e($labelSt) ?></div>
                            <div class="text-muted small">Jam scan: <?= substr($absensiHariIni['jam'], 0, 5) ?></div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="d-flex align-items-center justify-content-between p-3 rounded-3 flex-wrap gap-2" style="background:#fef2f2; border: 1px solid #fecaca;">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-x-circle-fill text-danger me-3 fs-2"></i>
                        <div>
                            <div class="fw-bold text-danger mb-0">
                                <?= ($absensiHariIni && (int)$absensiHariIni['status'] === 3) ? 'Status: Alpha (Belum Presensi)' : 'Belum Presensi Hari Ini' ?>
                            </div>
                            <small class="text-muted">Klik tombol di samping untuk presensi langsung atau gunakan kamera.</small>
                        </div>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <form action="<?= e(BASEURL); ?>/siswa/absenMandiri" method="POST" class="d-inline">
                            <input type="hidden" name="csrf_token" value="<?= e(Csrf::getToken()); ?>">
                            <button type="submit" class="btn btn-danger btn-sm px-3 shadow-sm">
                                <i class="bi bi-check2-circle me-1"></i>Presensi Sekarang
                            </button>
                        </form>
                        <a href="<?= e(BASEURL); ?>/absensi/siswa" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-camera-video me-1"></i>Scan Kamera
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Rekap Total -->
        <div class="row g-3">
            <div class="col-6 col-sm-3">
                <div class="stat-card card border-0 p-3 text-center">
                    <div class="stat-icon-wrapper stat-icon-success mx-auto mb-2"><i class="bi bi-check2-circle"></i></div>
                    <div class="fw-bold fs-4"><?= (int)$rekap['total_hadir'] ?></div>
                    <div class="text-muted small">Hadir</div>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="stat-card card border-0 p-3 text-center">
                    <div class="stat-icon-wrapper stat-icon-warning mx-auto mb-2"><i class="bi bi-thermometer-half"></i></div>
                    <div class="fw-bold fs-4"><?= (int)$rekap['total_sakit'] ?></div>
                    <div class="text-muted small">Sakit</div>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="stat-card card border-0 p-3 text-center">
                    <div class="stat-icon-wrapper stat-icon-indigo mx-auto mb-2"><i class="bi bi-envelope-fill"></i></div>
                    <div class="fw-bold fs-4"><?= (int)$rekap['total_izin'] ?></div>
                    <div class="text-muted small">Izin</div>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="stat-card card border-0 p-3 text-center">
                    <div class="stat-icon-wrapper stat-icon-danger mx-auto mb-2"><i class="bi bi-x-circle-fill"></i></div>
                    <div class="fw-bold fs-4"><?= (int)$rekap['total_alpa'] ?></div>
                    <div class="text-muted small">Alpa</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row 2: Jadwal Belajar Siswa (Hari Ini & Seminggu Penuh) -->
<div class="row g-4 mb-4" id="jadwal-belajar">
    <!-- Jadwal Hari Ini -->
    <div class="col-lg-5">
        <div class="table-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-calendar2-day-fill me-2 text-primary"></i>Jadwal Belajar Hari Ini (<?= e($hari) ?>)
                </h6>
                <span class="badge bg-primary-subtle text-primary">Kelas <?= e($profil['nama_kelas']) ?></span>
            </div>
            <?php if (!empty($jadwalHariIni)): ?>
                <?php foreach ($jadwalHariIni as $j): ?>
                    <div class="d-flex align-items-center p-3 mb-2 rounded-3 border-start border-4 border-primary" style="background:#f8fafc;">
                        <div class="me-3 text-center" style="min-width:65px;">
                            <div class="fw-bold text-primary small"><?= substr($j['jam_mulai'], 0, 5) ?></div>
                            <div class="text-muted" style="font-size:10px;">s/d</div>
                            <div class="fw-bold text-primary small"><?= substr($j['jam_selesai'], 0, 5) ?></div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold text-dark"><?= e($j['mata_pelajaran'] ?? '-') ?></div>
                            <div class="text-muted small"><i class="bi bi-person-badge me-1"></i><?= e($j['nama_guru']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state py-4">
                    <i class="bi bi-calendar-x text-muted" style="font-size:2.5rem;"></i>
                    <p class="mt-2 text-muted">Tidak ada jadwal belajar untuk hari <?= e($hari) ?>.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Jadwal Pelajaran Seminggu -->
    <div class="col-lg-7">
        <div class="table-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-calendar-week-fill me-2 text-primary"></i>Jadwal Belajar Mingguan
                </h6>
                <span class="text-muted small">Senin – Sabtu</span>
            </div>
            <?php if (!empty($jadwalSemua)): ?>
                <div class="table-responsive" style="max-height:360px; overflow-y:auto;">
                    <table class="table custom-table mb-0 align-middle">
                        <thead class="sticky-top bg-white">
                            <tr>
                                <th>Hari</th>
                                <th>Jam</th>
                                <th>Mata Pelajaran</th>
                                <th>Guru Pengampu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jadwalSemua as $j): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary fw-semibold"><?= e($j['hari']) ?></span>
                                    </td>
                                    <td class="text-nowrap text-muted small fw-medium">
                                        <?= substr($j['jam_mulai'], 0, 5) ?> – <?= substr($j['jam_selesai'], 0, 5) ?>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark"><?= e($j['mata_pelajaran'] ?? '-') ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= e($j['nama_guru']) ?></div>
                                        <small class="text-muted" style="font-size:11px;">NIP: <?= e($j['nip']) ?></small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state py-4">
                    <i class="bi bi-calendar-x"></i>
                    <p>Belum ada jadwal belajar yang terdaftar untuk kelas Anda.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Filter + Riwayat -->
<div class="table-card">
    <div class="mb-4">
        <h6 class="fw-bold mb-3"><i class="bi bi-funnel me-2 text-primary"></i>Filter Riwayat</h6>
        <form action="<?= e(BASEURL) ?>/siswa/dashboard" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Rentang Tanggal</label>
                <div class="input-group">
                    <input type="date" name="tgl_awal" class="form-control" value="<?= e($filters['tgl_awal'] ?? '') ?>">
                    <span class="input-group-text">s/d</span>
                    <input type="date" name="tgl_akhir" class="form-control" value="<?= e($filters['tgl_akhir'] ?? '') ?>">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label">Bulan</label>
                <input type="month" name="bulan" class="form-control" value="<?= e($filters['bulan'] ?? date('Y-m')) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">– Semua Status –</option>
                    <option value="0" <?= (($filters['status'] ?? '') === '0' || ($filters['status'] ?? '') === 0) ? 'selected' : '' ?>>Hadir</option>
                    <option value="1" <?= (($filters['status'] ?? '') === '1' || ($filters['status'] ?? '') === 1) ? 'selected' : '' ?>>Sakit</option>
                    <option value="2" <?= (($filters['status'] ?? '') === '2' || ($filters['status'] ?? '') === 2) ? 'selected' : '' ?>>Izin</option>
                    <option value="3" <?= (($filters['status'] ?? '') === '3' || ($filters['status'] ?? '') === 3) ? 'selected' : '' ?>>Alpha</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search me-1"></i>Cari</button>
                <a href="<?= e(BASEURL) ?>/siswa/dashboard" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i></a>
            </div>
        </form>
    </div>

    <hr>

    <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-2 text-primary"></i>Riwayat Absensi</h6>
    <?php if (!empty($riwayat)): ?>
        <div class="table-responsive">
            <table class="table custom-table mb-0">
                <thead>
                    <tr><th>No</th><th>Tanggal</th><th>Jam Scan</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($riwayat as $r): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= $hariMap[date('l', strtotime($r['tanggal']))] ?>, <?= date('d M Y', strtotime($r['tanggal'])) ?></td>
                            <td><?= substr($r['jam'], 0, 5) ?></td>
                            <td>
                                <?php
                                $rawR = $r['status'];
                                switch ((int)$rawR) {
                                    case 1:
                                        $cls = 'badge-sakit';
                                        $labelR = 'Sakit';
                                        break;
                                    case 2:
                                        $cls = 'badge-izin';
                                        $labelR = 'Izin';
                                        break;
                                    case 3:
                                        $cls = 'badge-alpa';
                                        $labelR = 'Alpha';
                                        break;
                                    case 0:
                                    default:
                                        $cls = 'badge-hadir';
                                        $labelR = 'Hadir';
                                        break;
                                }
                                ?>
                                <span class="badge <?= $cls ?>"><?= $labelR ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="bi bi-clipboard-x"></i>
            <p>Tidak ada data absensi pada periode yang dipilih.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Modal QR Code Siswa Fullscreen / Preview -->
<div class="modal fade" id="modalQrSiswa" tabindex="-1" aria-labelledby="modalQrSiswaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalQrSiswaLabel">
                    <i class="bi bi-qr-code-scan text-primary me-2"></i>Kode QR Presensi Siswa
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="d-inline-flex p-3 bg-white rounded-4 shadow border mb-3">
                    <div id="qrcode-siswa-modal" style="width:220px;height:220px;display:flex;align-items:center;justify-content:center;"></div>
                </div>
                <h5 class="fw-bold mb-1"><?= e($profil['nama']) ?></h5>
                <p class="text-muted mb-2">NIS: <strong class="text-dark font-monospace"><?= e($profil['nis']) ?></strong> &bull; Kelas: <strong><?= e($profil['nama_kelas']) ?></strong></p>
                <div class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill font-monospace fs-6 mb-3">
                    ID: <?= e($profil['barcode_id'] ?: $profil['nis']) ?>
                </div>
                <div class="alert alert-info border-0 rounded-3 py-2 px-3 small text-muted mb-0">
                    <i class="bi bi-info-circle me-1 text-primary"></i> Arahkan layar HP ini ke kamera scanner absensi sekolah. Keterangan presensi akan langsung muncul di layar.
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary px-4" onclick="downloadQrSiswa()">
                    <i class="bi bi-download me-1"></i> Unduh Gambar QR
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= e(BASEURL); ?>/js/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const qrCodeVal = <?= json_encode((string)($profil['barcode_id'] ?: $profil['nis'])) ?>;
    if (!qrCodeVal) return;

    // Render QR Code pada kartu profil
    const elCard = document.getElementById('qrcode-siswa');
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
    const elModal = document.getElementById('qrcode-siswa-modal');
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

function downloadQrSiswa() {
    const img = document.querySelector('#qrcode-siswa-modal img') || document.querySelector('#qrcode-siswa img');
    if (img && img.src) {
        const a = document.createElement('a');
        a.href = img.src;
        a.download = 'QR_Absen_<?= preg_replace('/[^a-zA-Z0-9_-]/', '_', $profil['nama']) ?>.png';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }
}
</script>
