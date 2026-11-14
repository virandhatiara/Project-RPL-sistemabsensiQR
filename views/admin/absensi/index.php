<?php
$csrf      = e($_SESSION['csrf_token'] ?? '');
$baseUrl   = e(BASEURL);
$tanggal   = e($data['tanggal'] ?? date('Y-m-d'));
$kelasId   = (int)($data['kelas_id'] ?? 0);
$statusSel = isset($data['status']) && $data['status'] !== null && $data['status'] !== '' ? (string)$data['status'] : '';
$keyword   = e($data['keyword'] ?? '');
$no        = (($data['currentPage'] - 1) * 15) + 1;
$stats     = $data['statistik'] ?? [];
?>

<?php Flasher::flash(); ?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/admin/dashboard" class="text-decoration-none">Dashboard</a></li>
        <li class="breadcrumb-item active">Kelola Absensi Siswa</li>
    </ol>
</nav>

<!-- Summary Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Total Hadir</span>
                    <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="bi bi-person-check-fill fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-success mb-1"><?= (int)($stats['total_hadir'] ?? 0) ?></h3>
                <small class="text-muted d-block" style="font-size: 11px;">
                    <?= (int)($stats['tepat_waktu'] ?? 0) ?> tepat waktu &bull; <?= (int)($stats['terlambat'] ?? 0) ?> terlambat
                </small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Sakit</span>
                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="bi bi-heart-pulse-fill fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-warning mb-1"><?= (int)($stats['sakit'] ?? 0) ?></h3>
                <small class="text-muted d-block" style="font-size: 11px;">Keterangan Sakit</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Izin</span>
                    <div class="rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="bi bi-envelope-paper-fill fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-info mb-1"><?= (int)($stats['izin'] ?? 0) ?></h3>
                <small class="text-muted d-block" style="font-size: 11px;">Izin Keperluan</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Alpha</span>
                    <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="bi bi-x-circle-fill fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-danger mb-1"><?= (int)($stats['alpa'] ?? 0) ?></h3>
                <small class="text-muted d-block" style="font-size: 11px;">Tanpa Keterangan</small>
            </div>
        </div>
    </div>
</div>

<div class="table-card">
    <!-- Toolbar -->
    <div class="table-toolbar flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0"><i class="bi bi-calendar-check-fill me-2 text-primary"></i>Kelola Data Absensi Siswa</h5>
            <small class="text-muted">Kelola riwayat presensi harian, ubah status atau tambah data absensi manual.</small>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <form action="<?= $baseUrl ?>/admin-absensi/sinkronisasiAlpha" method="POST" class="d-inline" onsubmit="return confirm('Jalankan pendataan Alpha otomatis untuk semua siswa dan guru yang belum mengisi absen pada tanggal ini?');">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="tanggal" value="<?= $tanggal ?>">
                <button type="submit" class="btn btn-warning btn-sm fw-semibold shadow-sm text-dark" title="Otomatis data Alpha siswa & guru yang belum absen">
                    <i class="bi bi-person-x-fill me-1 text-danger"></i>Data Alpha Otomatis
                </button>
            </form>
            <a href="<?= $baseUrl ?>/absensi/siswa" target="_blank" class="btn btn-outline-success btn-sm">
                <i class="bi bi-camera-video-fill me-1"></i>Buka Scanner
            </a>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahAbsensi">
                <i class="bi bi-plus-lg me-1"></i>Tambah Absensi Manual
            </button>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-light p-3 rounded-3 mb-4 border">
        <form action="<?= $baseUrl ?>/admin-absensi" method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-sm-6 col-md-3">
                <label class="form-label small fw-semibold mb-1"><i class="bi bi-calendar3 me-1"></i>Tanggal</label>
                <input type="date" name="tanggal" class="form-control form-control-sm" value="<?= $tanggal ?>">
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <label class="form-label small fw-semibold mb-1"><i class="bi bi-building me-1"></i>Kelas</label>
                <select name="kelas_id" class="form-select form-select-sm">
                    <option value="">Semua Kelas</option>
                    <?php if (!empty($data['kelas_list'])): ?>
                        <?php foreach ($data['kelas_list'] as $kls): ?>
                            <option value="<?= (int)$kls['id'] ?>" <?= $kelasId === (int)$kls['id'] ? 'selected' : '' ?>>
                                <?= e($kls['nama_kelas']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-2">
                <label class="form-label small fw-semibold mb-1"><i class="bi bi-funnel me-1"></i>Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="0" <?= $statusSel === '0' ? 'selected' : '' ?>>Hadir</option>
                    <option value="1" <?= $statusSel === '1' ? 'selected' : '' ?>>Sakit</option>
                    <option value="2" <?= $statusSel === '2' ? 'selected' : '' ?>>Izin</option>
                    <option value="3" <?= $statusSel === '3' ? 'selected' : '' ?>>Alpha</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <label class="form-label small fw-semibold mb-1"><i class="bi bi-search me-1"></i>Cari Siswa</label>
                <input type="text" name="keyword" class="form-control form-control-sm" placeholder="Nama atau NIS..." value="<?= $keyword ?>">
            </div>
            <div class="col-12 col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100" title="Terapkan Filter">
                    <i class="bi bi-filter"></i>
                </button>
                <a href="<?= $baseUrl ?>/admin-absensi" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table custom-table mb-0 align-middle">
            <thead>
                <tr>
                    <th width="45">No</th>
                    <th>NIS</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Tanggal</th>
                    <th>Jam</th>
                    <th>Status Kehadiran</th>
                    <th width="130" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['absensi'])): ?>
                    <tr>
                        <td colspan="8">
                            <div class="empty-state py-5 text-center">
                                <i class="bi bi-clipboard2-x fs-1 text-muted d-block mb-2"></i>
                                <h6 class="text-secondary fw-semibold">Tidak ada data absensi</h6>
                                <p class="text-muted small mb-0">Belum ada catatan presensi untuk filter tanggal atau kriteria yang dipilih.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data['absensi'] as $row): ?>
                        <?php
                            $rawSt = $row['status'] ?? 0;
                            // Konversi integer kembali ke teks string menggunakan switch-case
                            switch ((int)$rawSt) {
                                case 1:
                                    $badgeClass = 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
                                    $badgeText  = 'Sakit';
                                    $iconSt     = 'bi-heart-pulse-fill';
                                    break;
                                case 2:
                                    $badgeClass = 'bg-info-subtle text-info-emphasis border border-info-subtle';
                                    $badgeText  = 'Izin';
                                    $iconSt     = 'bi-envelope-paper-fill';
                                    break;
                                case 3:
                                    $badgeClass = 'bg-danger-subtle text-danger border border-danger-subtle';
                                    $badgeText  = 'Alpha';
                                    $iconSt     = 'bi-x-circle-fill';
                                    break;
                                case 0:
                                default:
                                    $badgeClass = 'bg-success-subtle text-success border border-success-subtle';
                                    $badgeText  = 'Hadir';
                                    $iconSt     = 'bi-check-circle-fill';
                                    break;
                            }
                        ?>
                        <tr>
                            <td class="text-muted"><?= $no++ ?></td>
                            <td><span class="font-monospace fw-semibold text-dark"><?= e($row['nis'] ?? '-') ?></span></td>
                            <td>
                                <strong class="text-dark"><?= e($row['nama'] ?? '-') ?></strong>
                            </td>
                            <td>
                                <?php if (!empty($row['nama_kelas'])): ?>
                                    <span class="badge bg-primary-subtle text-primary"><?= e($row['nama_kelas']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                            <td>
                                <span class="font-monospace text-secondary small">
                                    <i class="bi bi-clock me-1"></i><?= substr($row['jam'], 0, 5) ?> WIB
                                </span>
                            </td>
                            <td>
                                <span class="badge px-3 py-2 rounded-pill fw-semibold <?= $badgeClass ?>">
                                    <i class="bi <?= $iconSt ?> me-1"></i><?= $badgeText ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <!-- Tombol Ubah Status -->
                                <button type="button" class="btn btn-sm btn-outline-primary btn-action" title="Ubah Status Kehadiran"
                                        data-bs-toggle="modal" data-bs-target="#editModal<?= (int)$row['id'] ?>">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <!-- Tombol Hapus -->
                                <button type="button" class="btn btn-sm btn-outline-danger btn-action ms-1" title="Hapus Data"
                                        data-bs-toggle="modal" data-bs-target="#delModal<?= (int)$row['id'] ?>">
                                    <i class="bi bi-trash3"></i>
                                </button>

                                <!-- Modal Ubah Status Kehadiran -->
                                <div class="modal fade" id="editModal<?= (int)$row['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content text-start">
                                            <div class="modal-header border-bottom">
                                                <h6 class="modal-title fw-bold">
                                                    <i class="bi bi-pencil-square text-primary me-2"></i>Ubah Status Kehadiran
                                                </h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="<?= $baseUrl ?>/admin-absensi/update" method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                                    <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">

                                                    <!-- Student Info Box -->
                                                    <div class="bg-light rounded-3 p-3 mb-3 border">
                                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                                            <strong class="text-dark fs-6"><?= e($row['nama']) ?></strong>
                                                            <span class="badge bg-secondary"><?= e($row['nama_kelas'] ?? '-') ?></span>
                                                        </div>
                                                        <small class="text-muted font-monospace">NIS: <?= e($row['nis']) ?></small>
                                                    </div>

                                                    <div class="row g-2 mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label small fw-semibold">Tanggal</label>
                                                            <input type="date" name="tanggal" class="form-control form-control-sm" value="<?= e($row['tanggal']) ?>" required>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label small fw-semibold">Jam</label>
                                                            <input type="time" name="jam" class="form-control form-control-sm" value="<?= substr($row['jam'], 0, 5) ?>" required>
                                                        </div>
                                                    </div>

                                                    <!-- Pilihan Status Kehadiran Lengkap (Integer) -->
                                                    <label class="form-label small fw-semibold mb-2">Pilih Status Kehadiran: <span class="text-danger">*</span></label>
                                                    <div class="d-flex flex-column gap-2">
                                                        <div class="form-check p-2 border rounded-3 d-flex align-items-center">
                                                            <input class="form-check-input ms-1 me-2" type="radio" name="status" id="editStatusHadir<?= $row['id'] ?>" value="0" <?= (int)$rawSt === 0 ? 'checked' : '' ?>>
                                                            <label class="form-check-label w-100 fw-semibold text-success d-flex align-items-center justify-content-between" for="editStatusHadir<?= $row['id'] ?>">
                                                                <span><i class="bi bi-check-circle-fill me-2"></i>Hadir</span>
                                                                <small class="text-muted fw-normal">Siswa hadir di sekolah (0)</small>
                                                            </label>
                                                        </div>
                                                        <div class="form-check p-2 border rounded-3 d-flex align-items-center">
                                                            <input class="form-check-input ms-1 me-2" type="radio" name="status" id="editStatusSakit<?= $row['id'] ?>" value="1" <?= (int)$rawSt === 1 ? 'checked' : '' ?>>
                                                            <label class="form-check-label w-100 fw-semibold text-warning d-flex align-items-center justify-content-between" for="editStatusSakit<?= $row['id'] ?>">
                                                                <span><i class="bi bi-heart-pulse-fill me-2"></i>Sakit</span>
                                                                <small class="text-muted fw-normal">Keterangan sakit / surat dokter (1)</small>
                                                            </label>
                                                        </div>
                                                        <div class="form-check p-2 border rounded-3 d-flex align-items-center">
                                                            <input class="form-check-input ms-1 me-2" type="radio" name="status" id="editStatusIzin<?= $row['id'] ?>" value="2" <?= (int)$rawSt === 2 ? 'checked' : '' ?>>
                                                            <label class="form-check-label w-100 fw-semibold text-info d-flex align-items-center justify-content-between" for="editStatusIzin<?= $row['id'] ?>">
                                                                <span><i class="bi bi-envelope-paper-fill me-2"></i>Izin</span>
                                                                <small class="text-muted fw-normal">Izin keperluan keluarga / lainnya (2)</small>
                                                            </label>
                                                        </div>
                                                        <div class="form-check p-2 border rounded-3 d-flex align-items-center">
                                                            <input class="form-check-input ms-1 me-2" type="radio" name="status" id="editStatusAlpa<?= $row['id'] ?>" value="3" <?= (int)$rawSt === 3 ? 'checked' : '' ?>>
                                                            <label class="form-check-label w-100 fw-semibold text-danger d-flex align-items-center justify-content-between" for="editStatusAlpa<?= $row['id'] ?>">
                                                                <span><i class="bi bi-x-circle-fill me-2"></i>Alpha</span>
                                                                <small class="text-muted fw-normal">Tidak hadir tanpa keterangan (3)</small>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top bg-light">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary btn-sm px-3">
                                                        <i class="bi bi-save me-1"></i>Simpan Perubahan
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Hapus -->
                                <div class="modal fade" id="delModal<?= (int)$row['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-sm">
                                        <div class="modal-content text-center">
                                            <div class="modal-body pt-4">
                                                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center bg-danger bg-opacity-10 rounded-circle" style="width:56px;height:56px;">
                                                    <i class="bi bi-trash3-fill text-danger fs-4"></i>
                                                </div>
                                                <h6 class="fw-bold">Hapus Data Absensi?</h6>
                                                <p class="text-muted small">Catatan absensi <strong><?= e($row['nama']) ?></strong> pada tanggal <strong><?= date('d/m/Y', strtotime($row['tanggal'])) ?></strong> akan dihapus.</p>
                                            </div>
                                            <div class="modal-footer border-0 justify-content-center gap-2 pb-4">
                                                <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                                                <form action="<?= $baseUrl ?>/admin-absensi/delete/<?= (int)$row['id'] ?>" method="POST" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                                    <input type="hidden" name="tanggal" value="<?= $tanggal ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm px-3">Hapus</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($data['totalPages'] > 1): ?>
        <?php
            $queryBase = "tanggal=" . urlencode($tanggal);
            if ($kelasId > 0) $queryBase .= "&kelas_id=" . $kelasId;
            if (!empty($statusSel)) $queryBase .= "&status=" . urlencode($statusSel);
            if (!empty($keyword)) $queryBase .= "&keyword=" . urlencode($keyword);
        ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center mb-0">
                <li class="page-item <?= $data['currentPage'] <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $baseUrl ?>/admin-absensi?page=<?= $data['currentPage'] - 1 ?>&<?= $queryBase ?>">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>
                <?php for ($i = 1; $i <= $data['totalPages']; $i++): ?>
                    <li class="page-item <?= $i == $data['currentPage'] ? 'active' : '' ?>">
                        <a class="page-link" href="<?= $baseUrl ?>/admin-absensi?page=<?= $i ?>&<?= $queryBase ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $data['currentPage'] >= $data['totalPages'] ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $baseUrl ?>/admin-absensi?page=<?= $data['currentPage'] + 1 ?>&<?= $queryBase ?>">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<!-- Modal Tambah Absensi Manual -->
<div class="modal fade" id="modalTambahAbsensi" tabindex="-1" aria-labelledby="modalTambahAbsensiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h6 class="modal-title fw-bold" id="modalTambahAbsensiLabel">
                    <i class="bi bi-plus-circle-fill text-primary me-2"></i>Tambah Data Absensi Siswa Manual
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= $baseUrl ?>/admin-absensi/store" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

                    <!-- Pilih Siswa -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pilih Siswa <span class="text-danger">*</span></label>
                        <select name="siswa_id" class="form-select form-select-sm" required>
                            <option value="">-- Pilih Siswa --</option>
                            <?php if (!empty($data['siswa_list'])): ?>
                                <?php foreach ($data['siswa_list'] as $sw): ?>
                                    <option value="<?= (int)$sw['id'] ?>">
                                        <?= e($sw['nama']) ?> (NIS: <?= e($sw['nis']) ?> - <?= e($sw['nama_kelas'] ?? 'Tanpa Kelas') ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <small class="text-muted">Pilih siswa berdasarkan nama, NIS, atau kelasnya.</small>
                    </div>

                    <!-- Tanggal & Jam -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" class="form-control form-control-sm" value="<?= $tanggal ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Jam Presensi <span class="text-danger">*</span></label>
                            <input type="time" name="jam" class="form-control form-control-sm" value="<?= date('H:i') ?>" required>
                        </div>
                    </div>

                    <!-- Pilihan Status Kehadiran Lengkap (Integer) -->
                    <label class="form-label small fw-semibold mb-2">Pilih Status Kehadiran: <span class="text-danger">*</span></label>
                    <div class="d-flex flex-column gap-2">
                        <div class="form-check p-2 border rounded-3 d-flex align-items-center">
                            <input class="form-check-input ms-1 me-2" type="radio" name="status" id="addStatusHadir" value="0" checked>
                            <label class="form-check-label w-100 fw-semibold text-success d-flex align-items-center justify-content-between" for="addStatusHadir">
                                <span><i class="bi bi-check-circle-fill me-2"></i>Hadir</span>
                                <small class="text-muted fw-normal">Siswa hadir di sekolah (0)</small>
                            </label>
                        </div>
                        <div class="form-check p-2 border rounded-3 d-flex align-items-center">
                            <input class="form-check-input ms-1 me-2" type="radio" name="status" id="addStatusSakit" value="1">
                            <label class="form-check-label w-100 fw-semibold text-warning d-flex align-items-center justify-content-between" for="addStatusSakit">
                                <span><i class="bi bi-heart-pulse-fill me-2"></i>Sakit</span>
                                <small class="text-muted fw-normal">Keterangan sakit / surat izin (1)</small>
                            </label>
                        </div>
                        <div class="form-check p-2 border rounded-3 d-flex align-items-center">
                            <input class="form-check-input ms-1 me-2" type="radio" name="status" id="addStatusIzin" value="2">
                            <label class="form-check-label w-100 fw-semibold text-info d-flex align-items-center justify-content-between" for="addStatusIzin">
                                <span><i class="bi bi-envelope-paper-fill me-2"></i>Izin</span>
                                <small class="text-muted fw-normal">Izin ada keperluan (2)</small>
                            </label>
                        </div>
                        <div class="form-check p-2 border rounded-3 d-flex align-items-center">
                            <input class="form-check-input ms-1 me-2" type="radio" name="status" id="addStatusAlpa" value="3">
                            <label class="form-check-label w-100 fw-semibold text-danger d-flex align-items-center justify-content-between" for="addStatusAlpa">
                                <span><i class="bi bi-x-circle-fill me-2"></i>Alpha</span>
                                <small class="text-muted fw-normal">Tanpa keterangan / membolos (3)</small>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3">
                        <i class="bi bi-check-circle me-1"></i>Simpan Absensi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
