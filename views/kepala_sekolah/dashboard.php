<?php
$rekap      = $data['rekap'] ?? [];
$tren       = $data['tren'] ?? [];
$totalSiswa = 0; $totalGuru = 0;

// Hitung total dari data yang ada
$siswaHadir  = (int)($rekap['siswa']['hadir'] ?? 0);
$siswaAlpa   = (int)($rekap['siswa']['alpa'] ?? 0);
$siswaSakit  = (int)($rekap['siswa']['sakit'] ?? 0);
$siswaIzin   = (int)($rekap['siswa']['izin'] ?? 0);
$siswaTotal  = $siswaHadir + $siswaAlpa + $siswaSakit + $siswaIzin;

$guruHadir = (int)($rekap['guru']['hadir'] ?? 0);

// Chart data
$trendLabels = []; $trendHadir = [];
foreach ($tren as $t) {
    $trendLabels[] = date('d/m', strtotime($t['tanggal']));
    $trendHadir[]  = (int)$t['total_hadir'];
}
$persenSiswa = $siswaTotal > 0 ? round(($siswaHadir / $siswaTotal) * 100) : 0;
$tanggalFmt  = date('d F Y');
?>

<?php Flasher::flash(); ?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold mb-0">Dashboard Kepala Sekolah</h2>
        <p class="text-muted mb-0"><i class="bi bi-calendar3 me-1"></i><?= $tanggalFmt ?></p>
    </div>
    <button class="btn btn-outline-secondary btn-sm d-print-none" onclick="window.print()">
        <i class="bi bi-printer me-1"></i> Cetak
    </button>
</div>

<!-- Row 1: Stat Cards -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card card border-0 p-4">
            <div class="stat-icon-wrapper stat-icon-success mb-3">
                <i class="bi bi-person-check-fill"></i>
            </div>
            <div class="fw-bold fs-2"><?= $siswaHadir ?></div>
            <div class="text-muted small">Siswa Hadir</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card card border-0 p-4">
            <div class="stat-icon-wrapper stat-icon-danger mb-3">
                <i class="bi bi-person-x-fill"></i>
            </div>
            <div class="fw-bold fs-2"><?= $siswaAlpa + $siswaSakit + $siswaIzin ?></div>
            <div class="text-muted small">Siswa Tidak Hadir</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card card border-0 p-4">
            <div class="stat-icon-wrapper stat-icon-primary mb-3">
                <i class="bi bi-person-badge-fill"></i>
            </div>
            <div class="fw-bold fs-2"><?= $guruHadir ?></div>
            <div class="text-muted small">Guru Hadir</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card card border-0 p-4">
            <div class="stat-icon-wrapper stat-icon-indigo mb-3">
                <i class="bi bi-bar-chart-fill"></i>
            </div>
            <div class="fw-bold fs-2"><?= $persenSiswa ?>%</div>
            <div class="text-muted small">Kehadiran Siswa</div>
        </div>
    </div>
</div>

<!-- Row 2: Chart + Rincian -->
<div class="row g-4 mb-4">
    <!-- Tren Kehadiran -->
    <div class="col-lg-8">
        <div class="table-card h-100">
            <h6 class="fw-bold mb-4"><i class="bi bi-graph-up me-2 text-primary"></i>Tren Kehadiran Siswa (7 Hari Terakhir)</h6>
            <?php if (!empty($trendLabels)): ?>
                <canvas id="trendKS" height="220"></canvas>
            <?php else: ?>
                <div class="empty-state"><i class="bi bi-graph-down"></i><p>Belum ada data tren.</p></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Rincian Absensi Siswa Hari Ini -->
    <div class="col-lg-4">
        <div class="table-card h-100">
            <h6 class="fw-bold mb-4"><i class="bi bi-clipboard-data me-2 text-primary"></i>Rincian Absensi Hari Ini</h6>
            
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <small class="fw-semibold text-success">Hadir</small>
                    <small><?= $siswaHadir ?></small>
                </div>
                <div class="progress" style="height:8px;">
                    <div class="progress-bar bg-success" style="width:<?= $siswaTotal > 0 ? ($siswaHadir/$siswaTotal)*100 : 0 ?>%"></div>
                </div>
            </div>
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <small class="fw-semibold text-danger">Alpa</small>
                    <small><?= $siswaAlpa ?></small>
                </div>
                <div class="progress" style="height:8px;">
                    <div class="progress-bar bg-danger" style="width:<?= $siswaTotal > 0 ? ($siswaAlpa/$siswaTotal)*100 : 0 ?>%"></div>
                </div>
            </div>
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <small class="fw-semibold text-warning">Sakit</small>
                    <small><?= $siswaSakit ?></small>
                </div>
                <div class="progress" style="height:8px;">
                    <div class="progress-bar bg-warning" style="width:<?= $siswaTotal > 0 ? ($siswaSakit/$siswaTotal)*100 : 0 ?>%"></div>
                </div>
            </div>
            <div class="mb-4">
                <div class="d-flex justify-content-between mb-1">
                    <small class="fw-semibold text-info">Izin</small>
                    <small><?= $siswaIzin ?></small>
                </div>
                <div class="progress" style="height:8px;">
                    <div class="progress-bar bg-info" style="width:<?= $siswaTotal > 0 ? ($siswaIzin/$siswaTotal)*100 : 0 ?>%"></div>
                </div>
            </div>
            
            <hr>
            <div class="d-flex gap-2 mt-3">
                <a href="<?= e(BASEURL) ?>/kepala-sekolah/laporan-siswa" class="btn btn-primary btn-sm flex-fill">
                    <i class="bi bi-file-earmark-person me-1"></i> Laporan Siswa
                </a>
                <a href="<?= e(BASEURL) ?>/kepala-sekolah/laporan-guru" class="btn btn-outline-primary btn-sm flex-fill">
                    <i class="bi bi-file-earmark-medical me-1"></i> Laporan Guru
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('trendKS');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($trendLabels) ?>,
            datasets: [{
                label: 'Siswa Hadir',
                data: <?= json_encode($trendHadir) ?>,
                backgroundColor: 'rgba(59,130,246,0.7)',
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { stepSize: 1 } },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>

<style>
@media print {
    #sidebar, .top-navbar, .d-print-none { display: none !important; }
    .main-content { margin: 0 !important; }
}
</style>
