<?php
$csrf    = e($_SESSION['csrf_token'] ?? '');
$keyword = e($data['keyword'] ?? '');
$baseUrl = e(BASEURL);
$no      = (($data['currentPage'] - 1) * 10) + 1;
?>

<?php Flasher::flash(); ?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/admin/dashboard" class="text-decoration-none">Dashboard</a></li>
        <li class="breadcrumb-item active">Data Guru</li>
    </ol>
</nav>

<div class="table-card">
    <div class="table-toolbar">
        <h5 class="fw-bold mb-0"><i class="bi bi-person-badge-fill me-2 text-primary"></i>Data Guru</h5>
        <a href="<?= $baseUrl ?>/admin-guru/create" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Tambah Guru
        </a>
    </div>

    <form action="<?= $baseUrl ?>/admin-guru" method="GET" class="mb-4">
        <div class="input-group" style="max-width:380px;">
            <input type="text" name="keyword" class="form-control" placeholder="Cari NIP atau Nama Guru…" value="<?= $keyword ?>">
            <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
            <?php if (!empty($data['keyword'])): ?>
                <a href="<?= $baseUrl ?>/admin-guru" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-x-lg"></i></a>
            <?php endif; ?>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table custom-table mb-0">
            <thead>
                <tr>
                    <th width="50">No</th>
                    <th>NIP</th>
                    <th>Nama Guru</th>
                    <th>No. WhatsApp</th>
                    <th>Barcode ID</th>
                    <th>Akun</th>
                    <th width="120">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['guru'])): ?>
                    <tr><td colspan="7">
                        <div class="empty-state">
                            <i class="bi bi-person-badge"></i>
                            <p><?= empty($data['keyword']) ? 'Belum ada data guru.' : 'Tidak ada hasil untuk "<strong>' . $keyword . '</strong>".' ?></p>
                        </div>
                    </td></tr>
                <?php else: ?>
                    <?php foreach ($data['guru'] as $g): ?>
                        <?php 
                            $phoneGuru  = !empty($g['no_wa']) ? $g['no_wa'] : (!empty($g['no_hp']) ? $g['no_hp'] : '');
                            $cleanPhone = preg_replace('/[^0-9]/', '', $phoneGuru);
                            if (!empty($cleanPhone) && strpos($cleanPhone, '0') === 0) {
                                $waLink = '62' . substr($cleanPhone, 1);
                            } else {
                                $waLink = $cleanPhone;
                            }
                        ?>
                        <tr>
                            <td class="text-muted"><?= $no++ ?></td>
                            <td><span class="fw-semibold"><?= e($g['nip']) ?></span></td>
                            <td><?= e($g['nama']) ?></td>
                            <td>
                                <?php if (!empty($phoneGuru)): ?>
                                    <a href="https://wa.me/<?= e($waLink) ?>" target="_blank" rel="noopener noreferrer" class="badge bg-success-subtle text-success text-decoration-none px-2 py-1 d-inline-flex align-items-center gap-1" title="Kirim Pesan WhatsApp">
                                        <i class="bi bi-whatsapp"></i>
                                        <span><?= e($phoneGuru) ?></span>
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border">Belum diisi</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($g['barcode_id']): ?>
                                    <code class="text-primary"><?= e($g['barcode_id']) ?></code>
                                <?php else: ?>
                                    <span class="text-muted">Belum terdaftar</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($g['username']): ?>
                                    <span class="badge badge-hadir"><?= e($g['username']) ?></span>
                                <?php else: ?>
                                    <span class="badge badge-alpa">Tidak ada akun</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= $baseUrl ?>/admin-guru/edit/<?= (int)$g['id'] ?>" class="btn btn-sm btn-outline-primary btn-action" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger btn-action ms-1" title="Hapus"
                                    data-bs-toggle="modal" data-bs-target="#delGuru<?= (int)$g['id'] ?>">
                                    <i class="bi bi-trash3"></i>
                                </button>

                                <div class="modal fade" id="delGuru<?= (int)$g['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-sm">
                                        <div class="modal-content text-center">
                                            <div class="modal-body pt-4">
                                                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center bg-danger bg-opacity-10 rounded-circle" style="width:56px;height:56px;">
                                                    <i class="bi bi-trash3-fill text-danger fs-4"></i>
                                                </div>
                                                <h6 class="fw-bold">Hapus Guru?</h6>
                                                <p class="text-muted small">Data <strong><?= e($g['nama']) ?></strong> dan akun terkait akan dihapus permanen.</p>
                                            </div>
                                            <div class="modal-footer border-0 justify-content-center gap-2 pb-4">
                                                <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                                                <form action="<?= $baseUrl ?>/admin-guru/delete/<?= (int)$g['id'] ?>" method="POST" class="d-inline">
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

    <?php if ($data['totalPages'] > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center mb-0">
                <li class="page-item <?= $data['currentPage'] <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $baseUrl ?>/admin-guru?page=<?= $data['currentPage']-1 ?><?= !empty($data['keyword']) ? '&keyword='.urlencode($data['keyword']) : '' ?>"><i class="bi bi-chevron-left"></i></a>
                </li>
                <?php for ($i = 1; $i <= $data['totalPages']; $i++): ?>
                    <li class="page-item <?= $i == $data['currentPage'] ? 'active' : '' ?>">
                        <a class="page-link" href="<?= $baseUrl ?>/admin-guru?page=<?= $i ?><?= !empty($data['keyword']) ? '&keyword='.urlencode($data['keyword']) : '' ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $data['currentPage'] >= $data['totalPages'] ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $baseUrl ?>/admin-guru?page=<?= $data['currentPage']+1 ?><?= !empty($data['keyword']) ? '&keyword='.urlencode($data['keyword']) : '' ?>"><i class="bi bi-chevron-right"></i></a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>
