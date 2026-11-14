<?php
$baseUrl = e(BASEURL);
$filters = $data['filters'];
$kelas   = $data['kelas'];
$no      = (($data['currentPage'] - 1) * 15) + 1;
?>

<?php Flasher::flash(); ?>

<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold mb-0">Riwayat Absensi Kelas</h2>
        <p class="text-muted small mb-0"><i class="bi bi-building me-1"></i>Kelas <strong class="text-primary"><?= e($kelas['nama_kelas']) ?></strong></p>
    </div>
    <a href="<?= $baseUrl ?>/wali-kelas/dashboard" class="btn btn-outline-secondary btn-sm d-print-none">
        <i class="bi bi-arrow-left me-1"></i>Dashboard
    </a>
</div>

<!-- Filter -->
<div class="table-card mb-4 d-print-none">
    <h6 class="fw-bold mb-3"><i class="bi bi-funnel me-2 text-primary"></i>Filter</h6>
    <form action="<?= $baseUrl ?>/wali-kelas/absensi" method="GET" class="row g-3">
        <div class="col-md-3">
            <label class="form-label">Tanggal</label>
            <input type="date" name="tanggal" class="form-control" value="<?= e($filters['tanggal'] ?? '') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Nama Siswa</label>
            <input type="text" name="nama" class="form-control" placeholder="Cari nama siswa…" value="<?= e($filters['nama'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">— Semua Status —</option>
                <?php foreach (['hadir','terlambat','sakit','izin','alpa'] as $st): ?>
                    <option value="<?= $st ?>" <?= ($filters['status'] ?? '') === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search me-1"></i>Filter</button>
            <?php if (!empty($filters['tanggal']) || !empty($filters['nama']) || !empty($filters['status'])): ?>
                <a href="<?= $baseUrl ?>/wali-kelas/absensi" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Table -->
<div class="table-card">
    <div class="table-responsive">
        <table class="table custom-table mb-0">
            <thead>
                <tr><th width="50">No</th><th>NIS</th><th>Nama Siswa</th><th>Tanggal</th><th>Jam</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php if (empty($data['absensi'])): ?>
                    <tr><td colspan="6">
                        <div class="empty-state">
                            <i class="bi bi-clipboard-x"></i>
                            <p>Data absensi tidak ditemukan berdasarkan filter yang dipilih.</p>
                        </div>
                    </td></tr>
                <?php else: foreach ($data['absensi'] as $a): ?>
                    <tr>
                        <td class="text-muted"><?= $no++ ?></td>
                        <td><?= e($a['nis']) ?></td>
                        <td>
                            <a href="<?= $baseUrl ?>/wali-kelas/detail/<?= (int)$a['siswa_id'] ?>" class="fw-semibold text-decoration-none">
                                <?= e($a['nama']) ?>
                            </a>
                        </td>
                        <td><?= date('d/m/Y', strtotime($a['tanggal'])) ?></td>
                        <td><?= $a['jam'] ? substr($a['jam'],0,5) : '<span class="text-muted">–</span>' ?></td>
                        <td>
                            <?php
                            $st = $a['status'];
                            $cls = 'bg-secondary text-white';
                            if ($st==='hadir') $cls='badge-hadir';
                            elseif ($st==='terlambat') $cls='badge-terlambat';
                            elseif ($st==='sakit') $cls='badge-sakit';
                            elseif ($st==='izin') $cls='badge-izin';
                            elseif ($st==='alpa') $cls='badge-alpa';
                            ?>
                            <span class="badge <?= $cls ?>"><?= ucfirst($st) ?></span>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($data['totalPages'] > 1): ?>
    <nav class="mt-4"><ul class="pagination justify-content-center mb-0">
        <li class="page-item <?= $data['currentPage'] <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= $baseUrl ?>/wali-kelas/absensi?page=<?= $data['currentPage']-1 ?>&<?= e(http_build_query($filters)) ?>"><i class="bi bi-chevron-left"></i></a>
        </li>
        <?php for ($i = 1; $i <= $data['totalPages']; $i++): ?>
            <li class="page-item <?= $i == $data['currentPage'] ? 'active' : '' ?>">
                <a class="page-link" href="<?= $baseUrl ?>/wali-kelas/absensi?page=<?= $i ?>&<?= e(http_build_query($filters)) ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= $data['currentPage'] >= $data['totalPages'] ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= $baseUrl ?>/wali-kelas/absensi?page=<?= $data['currentPage']+1 ?>&<?= e(http_build_query($filters)) ?>"><i class="bi bi-chevron-right"></i></a>
        </li>
    </ul></nav>
    <?php endif; ?>
</div>
