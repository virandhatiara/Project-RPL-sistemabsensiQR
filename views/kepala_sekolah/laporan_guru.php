<?php
$baseUrl = e(BASEURL);
$filters = $data['filters'];
$qs = http_build_query($filters);
?>

<?php Flasher::flash(); ?>

<!-- Header -->
<div class="d-flex align-items-start justify-content-between mb-4 d-print-none flex-wrap gap-3">
    <div>
        <h2 class="fw-bold mb-0">Laporan Absensi Guru</h2>
        <p class="text-muted small mb-0"><i class="bi bi-person-badge-fill me-1"></i>Rekapitulasi kehadiran staf pengajar</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $baseUrl ?>/kepala-sekolah/export-guru-csv?<?= e($qs) ?>" class="btn btn-success btn-sm">
            <i class="bi bi-file-earmark-excel me-1"></i>Export CSV
        </a>
        <button onclick="window.print()" class="btn btn-danger btn-sm">
            <i class="bi bi-printer me-1"></i>Print / PDF
        </button>
    </div>
</div>

<!-- Print Title -->
<div class="d-none d-print-block text-center mb-4">
    <h3 class="fw-bold">Laporan Rekapitulasi Absensi Guru</h3>
    <p>Periode: <strong><?= date('d M Y', strtotime($filters['tgl_awal'])) ?></strong> s/d <strong><?= date('d M Y', strtotime($filters['tgl_akhir'])) ?></strong></p>
</div>

<!-- Filter -->
<div class="table-card mb-4 d-print-none">
    <h6 class="fw-bold mb-3"><i class="bi bi-funnel me-2 text-primary"></i>Filter Laporan</h6>
    <form action="<?= $baseUrl ?>/kepala-sekolah/laporan-guru" method="GET" class="row g-3">
        <div class="col-md-3">
            <label class="form-label">Tanggal Awal</label>
            <input type="date" name="tgl_awal" class="form-control" value="<?= e($filters['tgl_awal']) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Tanggal Akhir</label>
            <input type="date" name="tgl_akhir" class="form-control" value="<?= e($filters['tgl_akhir']) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Nama Guru</label>
            <input type="text" name="nama" class="form-control" placeholder="Cari nama guru…" value="<?= e($filters['nama'] ?? '') ?>">
        </div>
        <div class="col-md-3 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search me-1"></i>Terapkan</button>
            <a href="<?= $baseUrl ?>/kepala-sekolah/laporan-guru" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i></a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="table-card print-card-flat">
    <div class="table-responsive">
        <table class="table custom-table mb-0">
            <thead>
                <tr><th>No</th><th>NIP</th><th>Nama Guru</th><th>Tanggal</th><th>Jam Masuk</th><th>Jam Pulang</th></tr>
            </thead>
            <tbody>
                <?php if (empty($data['laporan'])): ?>
                    <tr><td colspan="6">
                        <div class="empty-state">
                            <i class="bi bi-clipboard-x"></i>
                            <p>Data tidak ditemukan pada periode / filter yang dipilih.</p>
                        </div>
                    </td></tr>
                <?php else: $no = 1; foreach ($data['laporan'] as $a): ?>
                    <tr>
                        <td class="text-muted"><?= $no++ ?></td>
                        <td class="fw-semibold"><?= e($a['nip']) ?></td>
                        <td><?= e($a['nama']) ?></td>
                        <td><?= date('d/m/Y', strtotime($a['tanggal'])) ?></td>
                        <td>
                            <?php if ($a['status'] === 'alpa'): ?>
                                <span class="badge badge-alpa"><i class="bi bi-x-circle me-1"></i>Alpha</span>
                            <?php elseif ($a['jam_masuk']): ?>
                                <span class="badge badge-hadir"><i class="bi bi-box-arrow-in-right me-1"></i><?= substr($a['jam_masuk'],0,5) ?></span>
                            <?php else: ?><span class="text-muted">–</span><?php endif; ?>
                        </td>
                        <td>
                            <?php if ($a['status'] === 'alpa'): ?>
                                <span class="text-muted">–</span>
                            <?php elseif ($a['jam_pulang']): ?>
                                <span class="badge badge-terlambat"><i class="bi bi-box-arrow-left me-1"></i><?= substr($a['jam_pulang'],0,5) ?></span>
                            <?php else: ?><span class="text-muted">–</span><?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <div class="d-none d-print-block mt-5 pt-4 text-end">
        <p>Mengetahui,</p><br><br><br>
        <p><strong>Kepala Sekolah</strong></p>
    </div>
</div>

<style>
@media print {
    @page { size: portrait; margin: 1cm; }
    #sidebar, .top-navbar, .d-print-none { display: none !important; }
    .main-content { margin: 0 !important; padding: 0 !important; }
    .print-card-flat { border: none !important; box-shadow: none !important; border-radius: 0 !important; }
}
</style>
