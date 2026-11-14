<?php 
$baseUrl = e(BASEURL); 
$csrf = e($_SESSION['csrf_token'] ?? '');
$keyword = e($data['keyword'] ?? ''); 
$no = (($data['currentPage'] - 1) * 10) + 1;
$roleBadge = [
    'admin'          => 'bg-danger',
    'kepala_sekolah' => 'bg-warning text-dark',
    'guru'           => 'bg-primary',
    'wali_kelas'     => 'bg-info text-dark',
    'siswa'          => 'badge-hadir'
];
$roleLabel = [
    'admin'          => 'Admin',
    'kepala_sekolah' => 'Kepala Sekolah',
    'guru'           => 'Guru',
    'wali_kelas'     => 'Wali Kelas',
    'siswa'          => 'Siswa'
];
?>
<?php Flasher::flash(); ?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/admin/dashboard" class="text-decoration-none">Dashboard</a></li>
        <li class="breadcrumb-item active">Manajemen User & Admin</li>
    </ol>
</nav>

<div class="table-card">
    <div class="table-toolbar">
        <div>
            <h5 class="fw-bold mb-1"><i class="bi bi-people-fill me-2 text-primary"></i>Manajemen User & Admin</h5>
            <small class="text-muted">Kelola akun dan nama pemegang akses sistem (Administrator, Guru, Kepala Sekolah, Siswa)</small>
        </div>
        <a href="<?= $baseUrl ?>/admin-user/create" class="btn btn-primary btn-sm">
            <i class="bi bi-person-plus-fill me-1"></i> Tambah User Baru
        </a>
    </div>

    <form action="<?= $baseUrl ?>/admin-user" method="GET" class="mb-4">
        <div class="input-group" style="max-width:420px;">
            <input type="text" name="keyword" class="form-control" placeholder="Cari nama pemegang, username, atau role…" value="<?= $keyword ?>">
            <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
            <?php if (!empty($data['keyword'])): ?>
                <a href="<?= $baseUrl ?>/admin-user" class="btn btn-outline-secondary" title="Reset filter"><i class="bi bi-x-lg"></i></a>
            <?php endif; ?>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table custom-table mb-0 align-middle">
            <thead>
                <tr>
                    <th width="50">No</th>
                    <th>Nama Pemegang Akun</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th width="120" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['users'])): ?>
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <i class="bi bi-person-x"></i>
                                <p><?= empty($data['keyword']) ? 'Belum ada data user.' : 'Tidak ada hasil untuk "<strong>'.$keyword.'</strong>".' ?></p>
                            </div>
                        </td>
                    </tr>
                <?php else: foreach ($data['users'] as $u): ?>
                    <?php 
                        $namaDisplay = !empty($u['nama']) ? $u['nama'] : '— Belum ada nama —';
                        $initial = strtoupper(substr(!empty($u['nama']) ? $u['nama'] : $u['username'], 0, 1));
                    ?>
                    <tr>
                        <td class="text-muted"><?= $no++ ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-circle" style="width:34px; height:34px; font-size:0.85rem; font-weight:600;">
                                    <?= $initial ?>
                                </div>
                                <div>
                                    <span class="fw-bold text-dark"><?= e($u['nama'] ?: $u['username']) ?></span>
                                    <?php if ($u['id'] == $_SESSION['user_id']): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1" style="font-size: 0.7rem;">Akun Anda</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="font-monospace text-muted small bg-light px-2 py-1 rounded border">@<?= e($u['username']) ?></span>
                        </td>
                        <td>
                            <span class="badge <?= $roleBadge[$u['role']] ?? 'bg-secondary' ?> py-2 px-2.5">
                                <?= $roleLabel[$u['role']] ?? e(ucwords(str_replace('_', ' ', $u['role']))) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= $u['is_active'] ? 'badge-hadir' : 'badge-alpa' ?>">
                                <?= $u['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="<?= $baseUrl ?>/admin-user/edit/<?= (int)$u['id'] ?>" class="btn btn-sm btn-outline-primary btn-action" title="Edit Akun & Nama">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                <button class="btn btn-sm btn-outline-danger btn-action ms-1" data-bs-toggle="modal" data-bs-target="#delUser<?= (int)$u['id'] ?>" title="Hapus User">
                                    <i class="bi bi-trash3"></i>
                                </button>
                                <div class="modal fade" id="delUser<?= (int)$u['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-sm">
                                        <div class="modal-content text-center">
                                            <div class="modal-body pt-4">
                                                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center bg-danger bg-opacity-10 rounded-circle" style="width:56px;height:56px;">
                                                    <i class="bi bi-trash3-fill text-danger fs-4"></i>
                                                </div>
                                                <h6 class="fw-bold">Hapus User?</h6>
                                                <p class="text-muted small">Akun <strong><?= e($u['nama'] ?: $u['username']) ?></strong> (@<?= e($u['username']) ?>) akan dihapus secara permanen.</p>
                                            </div>
                                            <div class="modal-footer border-0 justify-content-center gap-2 pb-4">
                                                <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                                                <form action="<?= $baseUrl ?>/admin-user/delete/<?= (int)$u['id'] ?>" method="POST" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm px-3">Hapus</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <span class="text-muted small ms-1" title="Tidak dapat menghapus diri sendiri">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($data['totalPages'] > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center mb-0">
                <li class="page-item <?= $data['currentPage'] <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $baseUrl ?>/admin-user?page=<?= $data['currentPage']-1 ?><?= !empty($data['keyword']) ? '&keyword='.urlencode($data['keyword']) : '' ?>">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>
                <?php for ($i = 1; $i <= $data['totalPages']; $i++): ?>
                    <li class="page-item <?= $i == $data['currentPage'] ? 'active' : '' ?>">
                        <a class="page-link" href="<?= $baseUrl ?>/admin-user?page=<?= $i ?><?= !empty($data['keyword']) ? '&keyword='.urlencode($data['keyword']) : '' ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $data['currentPage'] >= $data['totalPages'] ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $baseUrl ?>/admin-user?page=<?= $data['currentPage']+1 ?><?= !empty($data['keyword']) ? '&keyword='.urlencode($data['keyword']) : '' ?>">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>
