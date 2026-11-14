<div id="sidebar">
    <div class="sidebar-brand d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center overflow-hidden me-1">
            <i class="bi bi-shield-check text-primary me-2 fs-4 flex-shrink-0"></i>
            <div class="text-truncate">
                <h6 class="mb-0 fw-bold text-dark text-truncate" title="SMK NU WARGABINANGUN">SMK NU WARGABINANGUN</h6>
                <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.65rem; letter-spacing: 0.5px;">Sistem Absensi</small>
            </div>
        </div>
        <button class="btn btn-sm btn-light border text-muted rounded-2 p-1 px-2" type="button" id="sidebarCollapseBtn" title="Sembunyikan Sidebar ke samping">
            <i class="bi bi-chevron-left"></i>
        </button>
    </div>
    
    <div class="p-3">
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <?php 
                    $role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
                    $roleUrl = str_replace('_', '-', $role);
                    $isActive = (strpos($_SERVER['REQUEST_URI'], '/' . $roleUrl . '/dashboard') !== false) ? 'active' : '';
                ?>
                <a href="<?= e(BASEURL); ?>/<?= e($roleUrl); ?>/dashboard" class="nav-link <?= e($isActive); ?>">
                    <i class="bi bi-grid-1x2-fill"></i> Dashboard
                </a>
            </li>
            
            <?php if($role === 'admin'): ?>
                <li class="mt-4 mb-2">
                    <div class="sidebar-heading">Presensi & Scanner</div>
                </li>
                <li>
                    <a href="<?= e(BASEURL); ?>/admin-absensi" class="nav-link <?= e((strpos($_SERVER['REQUEST_URI'], '/admin-absensi') !== false) ? 'active' : ''); ?>">
                        <i class="bi bi-calendar-check-fill text-primary"></i> Data Absensi Siswa
                    </a>
                </li>
                <li>
                    <a href="<?= e(BASEURL); ?>/absensi/siswa" class="nav-link <?= e((strpos($_SERVER['REQUEST_URI'], '/absensi/siswa') !== false) ? 'active' : ''); ?>" target="_blank">
                        <i class="bi bi-camera-video-fill text-success"></i> Scan Absen Siswa
                    </a>
                </li>
                <li>
                    <a href="<?= e(BASEURL); ?>/absensi/guru" class="nav-link <?= e((strpos($_SERVER['REQUEST_URI'], '/absensi/guru') !== false) ? 'active' : ''); ?>" target="_blank">
                        <i class="bi bi-camera-video-fill text-success"></i> Scan Absen Guru
                    </a>
                </li>

                <li class="mt-4 mb-2">
                    <div class="sidebar-heading">Data Master</div>
                </li>
                <li>
                    <a href="<?= e(BASEURL); ?>/admin-siswa" class="nav-link <?= e((strpos($_SERVER['REQUEST_URI'], '/admin-siswa') !== false) ? 'active' : ''); ?>">
                        <i class="bi bi-people-fill"></i> Data Siswa
                    </a>
                </li>
                <li>
                    <a href="<?= e(BASEURL); ?>/admin-guru" class="nav-link <?= e((strpos($_SERVER['REQUEST_URI'], '/admin-guru') !== false) ? 'active' : ''); ?>">
                        <i class="bi bi-person-badge-fill"></i> Data Guru
                    </a>
                </li>
                <li>
                    <a href="<?= e(BASEURL); ?>/admin-kelas" class="nav-link <?= e((strpos($_SERVER['REQUEST_URI'], '/admin-kelas') !== false) ? 'active' : ''); ?>">
                        <i class="bi bi-building-fill"></i> Data Kelas
                    </a>
                </li>
                <li>
                    <a href="<?= e(BASEURL); ?>/admin-jadwal" class="nav-link <?= e((strpos($_SERVER['REQUEST_URI'], '/admin-jadwal') !== false) ? 'active' : ''); ?>">
                        <i class="bi bi-calendar-event-fill"></i> Data Jadwal
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= e(BASEURL); ?>/admin-barcode" class="nav-link <?= e((strpos($_SERVER['REQUEST_URI'], '/admin-barcode') !== false) ? 'active' : ''); ?>">
                        <i class="bi bi-upc-scan"></i> Data Barcode
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= e(BASEURL); ?>/admin-user" class="nav-link <?= e((strpos($_SERVER['REQUEST_URI'], '/admin-user') !== false) ? 'active' : ''); ?>">
                        <i class="bi bi-person-gear"></i> Manajemen User & Admin
                    </a>
                </li>
                <li class="mt-4 mb-2">
                    <div class="sidebar-heading">Konfigurasi</div>
                </li>
                <li class="nav-item">
                    <a href="<?= e(BASEURL); ?>/admin/dashboard#ai-setting-card" class="nav-link">
                        <i class="bi bi-robot text-primary"></i> Setting API AI Chatbot
                    </a>
                </li>
            <?php endif; ?>

            <?php if($role === 'guru'): ?>
                <li class="mt-4 mb-2">
                    <div class="sidebar-heading">Menu Guru</div>
                </li>
                <li>
                    <a href="<?= e(BASEURL); ?>/guru/dashboard#jadwal-mengajar" class="nav-link">
                        <i class="bi bi-calendar-week-fill text-primary"></i> Jadwal Mengajar
                    </a>
                </li>
                <li>
                    <a href="<?= e(BASEURL); ?>/absensi/guru" class="nav-link <?= e((strpos($_SERVER['REQUEST_URI'], '/absensi/guru') !== false) ? 'active' : ''); ?>">
                        <i class="bi bi-camera-video-fill text-success"></i> Scan Barcode Anda
                    </a>
                </li>
            <?php endif; ?>

            <?php if($role === 'wali_kelas'): ?>
                <li class="mt-4 mb-2">
                    <div class="sidebar-heading">Menu Kelas</div>
                </li>
                <li>
                    <a href="<?= e(BASEURL); ?>/wali-kelas/absensi" class="nav-link <?= e((strpos($_SERVER['REQUEST_URI'], '/wali-kelas/absensi') !== false || strpos($_SERVER['REQUEST_URI'], '/wali-kelas/detail') !== false) ? 'active' : ''); ?>">
                        <i class="bi bi-journal-check"></i> Riwayat Absensi
                    </a>
                </li>
            <?php endif; ?>

            <?php if($role === 'siswa'): ?>
                <li class="mt-4 mb-2">
                    <div class="sidebar-heading">Menu Saya</div>
                </li>
                <li>
                    <a href="<?= e(BASEURL); ?>/siswa/dashboard#jadwal-belajar" class="nav-link">
                        <i class="bi bi-calendar-week-fill text-primary"></i> Jadwal Belajar
                    </a>
                </li>
                <li>
                    <a href="<?= e(BASEURL); ?>/absensi/siswa" class="nav-link <?= e((strpos($_SERVER['REQUEST_URI'], '/absensi/siswa') !== false) ? 'active' : ''); ?>">
                        <i class="bi bi-camera-video-fill text-success"></i> Scan Barcode Anda
                    </a>
                </li>
                <li>
                    <a href="<?= e(BASEURL); ?>/siswa/dashboard" class="nav-link <?= e((strpos($_SERVER['REQUEST_URI'], '/siswa/dashboard') !== false) ? 'active' : ''); ?>">
                        <i class="bi bi-calendar-check-fill"></i> Riwayat Kehadiran
                    </a>
                </li>
            <?php endif; ?>

            <?php if($role === 'kepala_sekolah'): ?>
                <li class="mt-4 mb-2">
                    <div class="sidebar-heading">Laporan Utama</div>
                </li>
                <li>
                    <a href="<?= e(BASEURL); ?>/kepala-sekolah/laporan-siswa" class="nav-link <?= e((strpos($_SERVER['REQUEST_URI'], '/kepala-sekolah/laporan-siswa') !== false) ? 'active' : ''); ?>">
                        <i class="bi bi-file-earmark-person-fill"></i> Rekap Absen Siswa
                    </a>
                </li>
                <li>
                    <a href="<?= e(BASEURL); ?>/kepala-sekolah/laporan-guru" class="nav-link <?= e((strpos($_SERVER['REQUEST_URI'], '/kepala-sekolah/laporan-guru') !== false) ? 'active' : ''); ?>">
                        <i class="bi bi-file-earmark-medical-fill"></i> Rekap Absen Guru
                    </a>
                </li>
            <?php endif; ?>
        </ul>
        
        <div class="mt-5">
            <hr class="text-secondary opacity-25">
            <a href="<?= e(BASEURL); ?>/auth/logout" class="d-flex align-items-center text-danger text-decoration-none nav-link px-3 py-2 fw-semibold rounded-3 hover-bg-light transition">
                <i class="bi bi-box-arrow-left me-2 fs-5"></i>
                Keluar
            </a>
        </div>
    </div>
</div>
