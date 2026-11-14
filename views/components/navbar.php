<nav class="navbar navbar-expand-lg top-navbar d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center">
        <!-- Toggle button for desktop and mobile -->
        <button class="btn btn-light me-3 shadow-sm border rounded-3 btn-sidebar-toggle" type="button" id="sidebarToggle" title="Buka/Tutup Sidebar">
            <i class="bi bi-list fs-5 text-primary"></i>
        </button>
        <h1 class="page-title"><?= e(isset($data['judul']) ? $data['judul'] : 'Dashboard'); ?></h1>
    </div>
    
    <div class="d-flex align-items-center">
        <div class="dropdown">
            <a href="#" class="text-decoration-none dropdown-toggle user-dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="avatar-circle">
                    <?php 
                        $displayName = !empty($_SESSION['nama']) ? $_SESSION['nama'] : ($_SESSION['username'] ?? 'User');
                        echo strtoupper(substr($displayName, 0, 1)); 
                    ?>
                </div>
                <span class="d-none d-sm-inline fw-semibold"><?= e($displayName); ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end text-small shadow-lg border-0 mt-2 rounded-3" aria-labelledby="dropdownUser" style="min-width: 220px;">
                <li class="px-3 py-2">
                    <div class="fw-bold text-dark text-truncate" style="max-width: 200px;"><?= e($displayName); ?></div>
                    <small class="text-muted d-block font-monospace">@<?= e($_SESSION['username'] ?? ''); ?></small>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle mt-1 text-uppercase fw-semibold" style="font-size: 0.68rem;">
                        <?= e(str_replace('_', ' ', $_SESSION['role'] ?? 'User')); ?>
                    </span>
                </li>
                <?php if(($_SESSION['role'] ?? '') === 'admin'): ?>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <a class="dropdown-item d-flex align-items-center py-2" href="<?= e(BASEURL); ?>/admin-user">
                        <i class="bi bi-person-gear me-2 text-primary"></i> Manajemen User & Admin
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center py-2" href="<?= e(BASEURL); ?>/admin-user/edit/<?= (int)($_SESSION['user_id'] ?? 1); ?>">
                        <i class="bi bi-person-badge me-2 text-success"></i> Edit Profil Nama Saya
                    </a>
                </li>
                <?php endif; ?>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <a class="dropdown-item text-danger d-flex align-items-center py-2" href="<?= e(BASEURL); ?>/auth/logout">
                        <i class="bi bi-box-arrow-right me-2"></i> Sign out
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
