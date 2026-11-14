<?php
$kelas      = $data['kelas'];
$ringkasan  = $data['ringkasan'];
$siswaHari  = $data['siswa_hari_ini'];
$tanggalFmt = date('d F Y');
?>

<?php Flasher::flash(); ?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold mb-0">Dashboard Wali Kelas</h2>
        <p class="text-muted mb-0">
            <i class="bi bi-building me-1"></i>Kelas Binaan: 
            <strong class="text-primary"><?= e($kelas['nama_kelas']) ?></strong>
            &bull; <?= $tanggalFmt ?>
        </p>
    </div>
    <a href="<?= e(BASEURL) ?>/wali-kelas/absensi" class="btn btn-outline-primary btn-sm d-print-none">
        <i class="bi bi-clock-history me-1"></i> Seluruh Riwayat
    </a>
</div>

<!-- Stat Cards -->
<div class="row g-4 mb-4">
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card card border-0 p-3 text-center">
            <div class="stat-icon-wrapper stat-icon-primary mx-auto mb-3">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="fw-bold fs-3"><?= (int)$ringkasan['total'] ?></div>
            <div class="text-muted small">Total Siswa</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card card border-0 p-3 text-center">
            <div class="stat-icon-wrapper stat-icon-success mx-auto mb-3">
                <i class="bi bi-person-check-fill"></i>
            </div>
            <div class="fw-bold fs-3"><?= (int)$ringkasan['hadir'] ?></div>
            <div class="text-muted small">Hadir</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card card border-0 p-3 text-center">
            <div class="stat-icon-wrapper stat-icon-warning mx-auto mb-3">
                <i class="bi bi-thermometer-half"></i>
            </div>
            <div class="fw-bold fs-3"><?= (int)$ringkasan['sakit'] ?></div>
            <div class="text-muted small">Sakit</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card card border-0 p-3 text-center">
            <div class="stat-icon-wrapper stat-icon-indigo mx-auto mb-3">
                <i class="bi bi-envelope-fill"></i>
            </div>
            <div class="fw-bold fs-3"><?= (int)$ringkasan['izin'] ?></div>
            <div class="text-muted small">Izin</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card card border-0 p-3 text-center">
            <div class="stat-icon-wrapper stat-icon-danger mx-auto mb-3">
                <i class="bi bi-person-x-fill"></i>
            </div>
            <div class="fw-bold fs-3"><?= (int)$ringkasan['alpa'] ?></div>
            <div class="text-muted small">Alpa</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card card border-0 p-3 text-center">
            <div class="stat-icon-wrapper mx-auto mb-3" style="background:#f1f5f9;color:#94a3b8;">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="fw-bold fs-3"><?= (int)$ringkasan['belum_absen'] ?></div>
            <div class="text-muted small">Belum Absen</div>
        </div>
    </div>
</div>

<!-- Daftar Siswa Hari Ini -->
<div class="table-card">
    <div class="table-toolbar">
        <h6 class="fw-bold mb-0"><i class="bi bi-list-check me-2 text-primary"></i>Status Absensi Hari Ini</h6>
    </div>

    <?php if (!empty($siswaHari)): ?>
        <div class="table-responsive">
            <table class="table custom-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>Jam Scan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($siswaHari as $s): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td class="text-muted"><?= e($s['nis']) ?></td>
                            <td class="fw-semibold"><?= e($s['nama']) ?></td>
                            <td><?= $s['jam'] ? substr($s['jam'], 0, 5) : '<span class="text-muted">–</span>' ?></td>
                            <td>
                                <?php
                                $st  = $s['status'];
                                $cls = 'bg-secondary text-white';
                                $lbl = 'Belum Absen';
                                if ($st === 'hadir')     { $cls = 'badge-hadir';    $lbl = 'Hadir'; }
                                if ($st === 'terlambat') { $cls = 'badge-terlambat'; $lbl = 'Terlambat'; }
                                if ($st === 'sakit')     { $cls = 'badge-sakit';    $lbl = 'Sakit'; }
                                if ($st === 'izin')      { $cls = 'badge-izin';     $lbl = 'Izin'; }
                                if ($st === 'alpa')      { $cls = 'badge-alpa';     $lbl = 'Alpa'; }
                                ?>
                                <span class="badge <?= $cls ?>"><?= $lbl ?></span>
                            </td>
                            <td>
                                <a href="<?= e(BASEURL) ?>/wali-kelas/detail/<?= e($s['id']) ?>" class="btn btn-sm btn-outline-primary btn-action" title="Lihat Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if (!$s['status']): ?>
                                    <button class="btn btn-sm btn-outline-secondary btn-action ms-1" title="Input Manual"
                                        data-bs-toggle="modal" data-bs-target="#modalInput<?= (int)$s['id'] ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <?php if (!$s['status']): ?>
                        <div class="modal fade" id="modalInput<?= (int)$s['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="<?= e(BASEURL) ?>/wali-kelas/inputManual" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                        <input type="hidden" name="siswa_id" value="<?= (int)$s['id'] ?>">
                                        <input type="hidden" name="tanggal" value="<?= date('Y-m-d') ?>">
                                        <div class="modal-header">
                                            <h6 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Input Absensi Manual</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Set status kehadiran untuk <strong><?= e($s['nama']) ?></strong> hari ini.</p>
                                            <div class="mb-3">
                                                <label class="form-label">Status Kehadiran</label>
                                                <select class="form-select" name="status" required>
                                                    <option value="" disabled selected>Pilih status...</option>
                                                    <option value="sakit">Sakit</option>
                                                    <option value="izin">Izin</option>
                                                    <option value="alpa">Alpa</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="bi bi-people"></i>
            <p>Belum ada data siswa terdaftar di kelas ini.</p>
        </div>
    <?php endif; ?>
</div>
