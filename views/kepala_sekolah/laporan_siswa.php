<?php
$baseUrl = e(BASEURL);
$filters = $data['filters'];
$qs = http_build_query($filters);
?>

<?php Flasher::flash(); ?>

<!-- Header -->
<div class="d-flex align-items-start justify-content-between mb-4 d-print-none flex-wrap gap-3">
    <div>
        <h2 class="fw-bold mb-0">Laporan Absensi Siswa</h2>
        <p class="text-muted small mb-0"><i class="bi bi-person-lines-fill me-1"></i>Rekapitulasi kehadiran seluruh siswa</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $baseUrl ?>/kepala-sekolah/export-siswa-csv?<?= e($qs) ?>" class="btn btn-success btn-sm">
            <i class="bi bi-file-earmark-excel me-1"></i>Export CSV
        </a>
        <button onclick="window.print()" class="btn btn-danger btn-sm">
            <i class="bi bi-printer me-1"></i>Print / PDF
        </button>
    </div>
</div>

<!-- Print Title -->
<div class="d-none d-print-block text-center mb-4">
    <h3 class="fw-bold">Laporan Rekapitulasi Absensi Siswa</h3>
    <p>Periode: <strong><?= date('d M Y', strtotime($filters['tgl_awal'])) ?></strong> s/d <strong><?= date('d M Y', strtotime($filters['tgl_akhir'])) ?></strong></p>
</div>

<!-- Filter -->
<div class="table-card mb-4 d-print-none">
    <h6 class="fw-bold mb-3"><i class="bi bi-funnel me-2 text-primary"></i>Filter Laporan</h6>
    <form action="<?= $baseUrl ?>/kepala-sekolah/laporan-siswa" method="GET" class="row g-3">
        <div class="col-md-2">
            <label class="form-label">Tanggal Awal</label>
            <input type="date" name="tgl_awal" class="form-control" value="<?= e($filters['tgl_awal']) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label">Tanggal Akhir</label>
            <input type="date" name="tgl_akhir" class="form-control" value="<?= e($filters['tgl_akhir']) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Kelas</label>
            <select name="kelas_id" class="form-select">
                <option value="">— Semua Kelas —</option>
                <?php foreach ($data['daftar_kelas'] as $k): ?>
                    <option value="<?= (int)$k['id'] ?>" <?= $filters['kelas_id'] == $k['id'] ? 'selected' : '' ?>><?= e($k['nama_kelas']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">— Semua —</option>
                <?php foreach (['hadir','terlambat','sakit','izin','alpa'] as $st): ?>
                    <option value="<?= $st ?>" <?= $filters['status'] === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search me-1"></i>Terapkan</button>
            <a href="<?= $baseUrl ?>/kepala-sekolah/laporan-siswa" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i></a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="table-card print-card-flat">
    <div class="table-responsive">
        <table class="table custom-table mb-0">
            <thead>
                <tr><th>No</th><th>NIS</th><th>Nama Siswa</th><th>Kelas</th><th>Tanggal</th><th>Jam</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php if (empty($data['laporan'])): ?>
                    <tr><td colspan="7">
                        <div class="empty-state">
                            <i class="bi bi-clipboard-x"></i>
                            <p>Data tidak ditemukan pada periode / filter yang dipilih.</p>
                        </div>
                    </td></tr>
                <?php else: $no = 1; foreach ($data['laporan'] as $a): ?>
                    <tr>
                        <td class="text-muted"><?= $no++ ?></td>
                        <td class="fw-semibold"><?= e($a['nis']) ?></td>
                        <td><?= e($a['nama']) ?></td>
                        <td><span class="badge bg-primary-subtle text-primary"><?= e($a['nama_kelas']) ?></span></td>
                        <td><?= date('d/m/Y', strtotime($a['tanggal'])) ?></td>
                        <td><?= $a['jam'] ? substr($a['jam'], 0, 5) : '–' ?></td>
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

    <!-- Signature print block -->
    <div class="d-none d-print-block mt-5 pt-4 text-end">
        <p>Mengetahui,</p>
        <br><br><br>
        <p><strong>Kepala Sekolah</strong></p>
    </div>
</div>

<style>
@media print {
    @page { size: landscape; margin: 1cm; }
    #sidebar, .top-navbar, .d-print-none { display: none !important; }
    .main-content { margin: 0 !important; padding: 0 !important; }
    .print-card-flat { border: none !important; box-shadow: none !important; border-radius: 0 !important; }
}
</style>
