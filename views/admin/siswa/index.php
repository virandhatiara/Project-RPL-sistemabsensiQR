<?php
$csrf    = e($_SESSION['csrf_token'] ?? '');
$keyword = e($data['keyword'] ?? '');
$baseUrl = e(BASEURL);
$no      = (($data['currentPage'] - 1) * 10) + 1;
?>

<?php Flasher::flash(); ?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/admin/dashboard" class="text-decoration-none">Dashboard</a></li>
        <li class="breadcrumb-item active">Data Siswa</li>
    </ol>
</nav>

<div class="table-card">
    <!-- Toolbar -->
    <div class="table-toolbar">
        <h5 class="fw-bold mb-0"><i class="bi bi-people-fill me-2 text-primary"></i>Data Siswa</h5>
        <a href="<?= $baseUrl ?>/admin-siswa/create" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Tambah Siswa
        </a>
    </div>

    <!-- Search -->
    <form action="<?= $baseUrl ?>/admin-siswa" method="GET" class="mb-4">
        <div class="input-group" style="max-width:380px;">
            <input type="text" name="keyword" class="form-control" placeholder="Cari NIS, Nama, atau Kelas…" value="<?= $keyword ?>">
            <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
            <?php if (!empty($data['keyword'])): ?>
                <a href="<?= $baseUrl ?>/admin-siswa" class="btn btn-outline-secondary" title="Reset pencarian"><i class="bi bi-x-lg"></i></a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table custom-table mb-0">
            <thead>
                <tr>
                    <th width="50">No</th>
                    <th>NIS</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Orang Tua & WhatsApp</th>
                    <th>Barcode ID</th>
                    <th>Akun</th>
                    <th width="120">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['siswa'])): ?>
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="bi bi-people"></i>
                                <p><?= empty($data['keyword']) ? 'Belum ada data siswa.' : 'Tidak ada hasil untuk pencarian "<strong>' . $keyword . '</strong>".' ?></p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data['siswa'] as $s): ?>
                        <?php 
                            $phoneOrtu  = !empty($s['no_hp_ortu']) ? $s['no_hp_ortu'] : (!empty($s['no_wa']) ? $s['no_wa'] : '');
                            $cleanPhone = preg_replace('/[^0-9]/', '', $phoneOrtu);
                            if (!empty($cleanPhone) && strpos($cleanPhone, '0') === 0) {
                                $waLink = '62' . substr($cleanPhone, 1);
                            } else {
                                $waLink = $cleanPhone;
                            }
                        ?>
                        <tr>
                            <td class="text-muted"><?= $no++ ?></td>
                            <td><span class="fw-semibold"><?= e($s['nis']) ?></span></td>
                            <td><?= e($s['nama']) ?></td>
                            <td>
                                <?php if ($s['nama_kelas']): ?>
                                    <span class="badge bg-primary-subtle text-primary"><?= e($s['nama_kelas']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">–</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($s['nama_ortu']) || !empty($phoneOrtu)): ?>
                                    <div class="d-flex flex-column gap-1">
                                        <?php if (!empty($s['nama_ortu'])): ?>
                                            <span class="small fw-semibold text-dark"><i class="bi bi-person-fill text-muted me-1"></i><?= e($s['nama_ortu']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($phoneOrtu)): ?>
                                            <a href="https://wa.me/<?= e($waLink) ?>" target="_blank" class="btn btn-sm btn-outline-success py-0 px-2 rounded-pill d-inline-flex align-items-center gap-1 shadow-sm" style="font-size: 0.75rem; width: fit-content;" title="Klik untuk Chat WhatsApp Orang Tua">
                                                <i class="bi bi-whatsapp"></i> <?= e($phoneOrtu) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small" style="font-size: 0.75rem;">— Belum ada No. WA</span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small">— Belum diatur</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($s['barcode_id']): ?>
                                    <code class="text-primary"><?= e($s['barcode_id']) ?></code>
                                <?php else: ?>
                                    <span class="text-muted">Belum terdaftar</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($s['username'])): ?>
                                    <span class="badge badge-hadir"><?= e($s['username']) ?></span>
                                <?php else: ?>
                                    <span class="badge badge-alpa">Tidak ada akun</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= $baseUrl ?>/admin-siswa/edit/<?= (int)$s['id'] ?>" class="btn btn-sm btn-outline-primary btn-action" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger btn-action ms-1" title="Hapus"
                                    data-bs-toggle="modal" data-bs-target="#delModal<?= (int)$s['id'] ?>">
                                    <i class="bi bi-trash3"></i>
                                </button>

                                <!-- Delete Modal -->
                                <div class="modal fade" id="delModal<?= (int)$s['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-sm">
                                        <div class="modal-content text-center">
                                            <div class="modal-body pt-4">
                                                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center bg-danger bg-opacity-10 rounded-circle" style="width:56px;height:56px;">
                                                    <i class="bi bi-trash3-fill text-danger fs-4"></i>
                                                </div>
                                                <h6 class="fw-bold">Hapus Siswa?</h6>
                                                <p class="text-muted small">Data <strong><?= e($s['nama']) ?></strong> beserta akun login-nya akan dihapus permanen.</p>
                                            </div>
                                            <div class="modal-footer border-0 justify-content-center gap-2 pb-4">
                                                <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                                                <form action="<?= $baseUrl ?>/admin-siswa/delete/<?= (int)$s['id'] ?>" method="POST" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
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
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center mb-0">
                <li class="page-item <?= $data['currentPage'] <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $baseUrl ?>/admin-siswa?page=<?= $data['currentPage'] - 1 ?><?= !empty($data['keyword']) ? '&keyword=' . urlencode($data['keyword']) : '' ?>">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>
                <?php for ($i = 1; $i <= $data['totalPages']; $i++): ?>
                    <li class="page-item <?= $i == $data['currentPage'] ? 'active' : '' ?>">
                        <a class="page-link" href="<?= $baseUrl ?>/admin-siswa?page=<?= $i ?><?= !empty($data['keyword']) ? '&keyword=' . urlencode($data['keyword']) : '' ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $data['currentPage'] >= $data['totalPages'] ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $baseUrl ?>/admin-siswa?page=<?= $data['currentPage'] + 1 ?><?= !empty($data['keyword']) ? '&keyword=' . urlencode($data['keyword']) : '' ?>">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>
