<?php
$csrf = e($_SESSION['csrf_token'] ?? ''); $keyword = e($data['keyword'] ?? '');
$baseUrl = e(BASEURL); $no = (($data['currentPage'] - 1) * 10) + 1;
?>
<?php Flasher::flash(); ?>
<nav aria-label="breadcrumb" class="mb-3"><ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/admin/dashboard" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item active">Data Kelas</li>
</ol></nav>

<div class="table-card">
    <div class="table-toolbar">
        <h5 class="fw-bold mb-0"><i class="bi bi-building-fill me-2 text-primary"></i>Data Kelas</h5>
        <a href="<?= $baseUrl ?>/admin-kelas/create" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Tambah Kelas</a>
    </div>
    <form action="<?= $baseUrl ?>/admin-kelas" method="GET" class="mb-4">
        <div class="input-group" style="max-width:360px;">
            <input type="text" name="keyword" class="form-control" placeholder="Cari nama kelas…" value="<?= $keyword ?>">
            <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
            <?php if (!empty($data['keyword'])): ?><a href="<?= $baseUrl ?>/admin-kelas" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a><?php endif; ?>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table custom-table mb-0">
            <thead><tr><th width="50">No</th><th>Nama Kelas</th><th>Wali Kelas</th><th width="120">Aksi</th></tr></thead>
            <tbody>
                <?php if (empty($data['kelas'])): ?>
                    <tr><td colspan="4"><div class="empty-state"><i class="bi bi-building"></i><p><?= empty($data['keyword']) ? 'Belum ada data kelas.' : 'Tidak ada hasil untuk "<strong>'.$keyword.'</strong>".' ?></p></div></td></tr>
                <?php else: foreach ($data['kelas'] as $k): ?>
                    <tr>
                        <td class="text-muted"><?= $no++ ?></td>
                        <td class="fw-semibold"><?= e($k['nama_kelas']) ?></td>
                        <td>
                            <?php if ($k['nama_wali']): ?>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-circle" style="width:28px;height:28px;font-size:0.75rem;"><?= strtoupper(substr($k['nama_wali'], 0, 1)) ?></div>
                                    <?= e($k['nama_wali']) ?>
                                </div>
                            <?php else: ?><span class="text-muted">Belum ada wali kelas</span><?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= $baseUrl ?>/admin-kelas/edit/<?= (int)$k['id'] ?>" class="btn btn-sm btn-outline-primary btn-action" title="Edit"><i class="bi bi-pencil"></i></a>
                            <button class="btn btn-sm btn-outline-danger btn-action ms-1" data-bs-toggle="modal" data-bs-target="#delKelas<?= (int)$k['id'] ?>"><i class="bi bi-trash3"></i></button>
                            <div class="modal fade" id="delKelas<?= (int)$k['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-sm"><div class="modal-content text-center">
                                    <div class="modal-body pt-4">
                                        <div class="mx-auto mb-3 d-flex align-items-center justify-content-center bg-danger bg-opacity-10 rounded-circle" style="width:56px;height:56px;"><i class="bi bi-trash3-fill text-danger fs-4"></i></div>
                                        <h6 class="fw-bold">Hapus Kelas?</h6>
                                        <p class="text-muted small">Kelas <strong><?= e($k['nama_kelas']) ?></strong> akan dihapus permanen.</p>
                                    </div>
                                    <div class="modal-footer border-0 justify-content-center gap-2 pb-4">
                                        <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                                        <form action="<?= $baseUrl ?>/admin-kelas/delete/<?= (int)$k['id'] ?>" method="POST" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                            <button type="submit" class="btn btn-danger btn-sm px-3">Hapus</button>
                                        </form>
                                    </div>
                                </div></div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($data['totalPages'] > 1): ?><nav class="mt-4"><ul class="pagination justify-content-center mb-0">
        <li class="page-item <?= $data['currentPage'] <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="<?= $baseUrl ?>/admin-kelas?page=<?= $data['currentPage']-1 ?><?= !empty($data['keyword']) ? '&keyword='.urlencode($data['keyword']) : '' ?>"><i class="bi bi-chevron-left"></i></a></li>
        <?php for ($i = 1; $i <= $data['totalPages']; $i++): ?><li class="page-item <?= $i == $data['currentPage'] ? 'active' : '' ?>"><a class="page-link" href="<?= $baseUrl ?>/admin-kelas?page=<?= $i ?><?= !empty($data['keyword']) ? '&keyword='.urlencode($data['keyword']) : '' ?>"><?= $i ?></a></li><?php endfor; ?>
        <li class="page-item <?= $data['currentPage'] >= $data['totalPages'] ? 'disabled' : '' ?>"><a class="page-link" href="<?= $baseUrl ?>/admin-kelas?page=<?= $data['currentPage']+1 ?><?= !empty($data['keyword']) ? '&keyword='.urlencode($data['keyword']) : '' ?>"><i class="bi bi-chevron-right"></i></a></li>
    </ul></nav><?php endif; ?>
</div>
