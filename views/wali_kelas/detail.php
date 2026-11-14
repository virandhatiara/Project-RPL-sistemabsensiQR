<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col">
            <a href="<?= e(BASEURL); ?>/wali-kelas/dashboard" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Kembali</a>
            <h1 class="h3 text-gray-800"><?= e($data['judul']); ?></h1>
        </div>
    </div>

    <div class="row">
        <!-- Profil Siswa -->
        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold"><i class="bi bi-person-badge"></i> Identitas Siswa</h6>
                </div>
                <div class="card-body text-center pt-4">
                    <div class="bg-light rounded-circle d-inline-flex justify-content-center align-items-center mb-3" style="width: 100px; height: 100px;">
                        <i class="bi bi-person text-secondary" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="font-weight-bold mb-1"><?= e($data['siswa']['nama']); ?></h5>
                    <p class="text-muted mb-3">NIS: <?= e($data['siswa']['nis']); ?></p>
                    
                    <ul class="list-group list-group-flush text-start mt-4">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Kelas
                            <span class="fw-bold"><?= e($data['kelas']['nama_kelas']); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Barcode Terdaftar
                            <span class="badge <?= e($data['siswa']['barcode_id'] ? 'bg-success' : 'bg-danger'); ?>">
                                <?= e($data['siswa']['barcode_id'] ? 'Ya' : 'Tidak'); ?>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Rekap Absensi -->
            <div class="card shadow mt-4">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-pie-chart"></i> Rekapitulasi Absensi</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center g-2">
                        <div class="col-6">
                            <div class="border rounded p-2 border-success border-opacity-50 bg-success bg-opacity-10">
                                <h3 class="mb-0 text-success fw-bold"><?= e($data['rekap']['total_hadir']); ?></h3>
                                <small class="text-muted">Hadir</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2 border-info border-opacity-50 bg-info bg-opacity-10">
                                <h3 class="mb-0 text-info fw-bold"><?= e($data['rekap']['total_sakit']); ?></h3>
                                <small class="text-muted">Sakit</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2 border-warning border-opacity-50 bg-warning bg-opacity-10">
                                <h3 class="mb-0 text-warning fw-bold text-dark"><?= e($data['rekap']['total_izin']); ?></h3>
                                <small class="text-muted">Izin</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2 border-danger border-opacity-50 bg-danger bg-opacity-10">
                                <h3 class="mb-0 text-danger fw-bold"><?= e($data['rekap']['total_alpa']); ?></h3>
                                <small class="text-muted">Alpa</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Riwayat Absensi Terakhir -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-clock-history"></i> 30 Riwayat Absensi Terakhir</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Jam Scan</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($data['riwayat'])) : ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">Belum ada riwayat absensi.</td>
                                    </tr>
                                <?php else : ?>
                                    <?php $no = 1; foreach ($data['riwayat'] as $r) : ?>
                                        <tr>
                                            <td><?= e($no++); ?></td>
                                            <td><?= e(date('l, d M Y', strtotime($r['tanggal']))); ?></td>
                                            <td><?= e($r['jam']); ?></td>
                                            <td>
                                                <?php 
                                                    $badgeClass = 'bg-primary';
                                                    if ($r['status'] == 'hadir') $badgeClass = 'bg-success';
                                                    elseif ($r['status'] == 'terlambat') $badgeClass = 'bg-warning text-dark';
                                                    elseif ($r['status'] == 'sakit') $badgeClass = 'bg-info text-dark';
                                                    elseif ($r['status'] == 'izin') $badgeClass = 'bg-warning text-dark';
                                                    elseif ($r['status'] == 'alpa') $badgeClass = 'bg-danger';
                                                    
                                                    echo '<span class="badge ' . $badgeClass . '">' . ucfirst($r['status']) . '</span>';
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
