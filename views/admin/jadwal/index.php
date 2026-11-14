<?php $baseUrl = e(BASEURL); $csrf = e($_SESSION['csrf_token'] ?? '');
$keyword = e($data['keyword'] ?? ''); $no = (($data['currentPage'] - 1) * 10) + 1;
$hariColors = ['Senin'=>'bg-primary','Selasa'=>'bg-indigo','Rabu'=>'bg-success','Kamis'=>'bg-warning text-dark','Jumat'=>'bg-info text-dark','Sabtu'=>'bg-secondary'];
?>
<?php Flasher::flash(); ?>
<nav aria-label="breadcrumb" class="mb-3"><ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/admin/dashboard" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item active">Data Jadwal</li>
</ol></nav>

<div class="table-card">
    <div class="table-toolbar">
        <h5 class="fw-bold mb-0"><i class="bi bi-calendar-event-fill me-2 text-primary"></i>Data Jadwal</h5>
        <a href="<?= $baseUrl ?>/admin-jadwal/create" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Tambah Jadwal</a>
    </div>
    <form action="<?= $baseUrl ?>/admin-jadwal" method="GET" class="mb-4">
        <div class="input-group" style="max-width:400px;">
            <input type="text" name="keyword" class="form-control" placeholder="Cari mata pelajaran, kelas, guru, hari…" value="<?= $keyword ?>">
            <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
            <?php if (!empty($data['keyword'])): ?><a href="<?= $baseUrl ?>/admin-jadwal" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a><?php endif; ?>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table custom-table mb-0 align-middle">
            <thead>
                <tr>
                    <th width="45">No</th>
                    <th>Hari</th>
                    <th>Jam</th>
                    <th>Mata Pelajaran</th>
                    <th>Kelas</th>
                    <th>Guru Pengajar</th>
                    <th width="120" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['jadwal'])): ?>
                    <tr><td colspan="7"><div class="empty-state"><i class="bi bi-calendar-x"></i><p><?= empty($data['keyword']) ? 'Belum ada data jadwal.' : 'Tidak ada hasil untuk "<strong>'.$keyword.'</strong>".' ?></p></div></td></tr>
                <?php else: foreach ($data['jadwal'] as $j): ?>
                    <tr>
                        <td class="text-muted"><?= $no++ ?></td>
                        <td><span class="badge <?= $hariColors[$j['hari']] ?? 'bg-secondary' ?>"><?= e($j['hari']) ?></span></td>
                        <td><code><?= substr($j['jam_mulai'], 0, 5) ?> – <?= substr($j['jam_selesai'], 0, 5) ?></code></td>
                        <td><span class="fw-bold text-dark"><i class="bi bi-book-half text-primary me-1"></i><?= e($j['mata_pelajaran'] ?? '-') ?></span></td>
                        <td><span class="badge bg-primary-subtle text-primary"><?= e($j['nama_kelas']) ?></span></td>
                        <td class="fw-semibold text-secondary"><?= e($j['nama_guru']) ?></td>
                        <td class="text-center">
                            <a href="<?= $baseUrl ?>/admin-jadwal/edit/<?= (int)$j['id'] ?>" class="btn btn-sm btn-outline-primary btn-action" title="Edit"><i class="bi bi-pencil"></i></a>
                            <button class="btn btn-sm btn-outline-danger btn-action ms-1" data-bs-toggle="modal" data-bs-target="#delJadwal<?= (int)$j['id'] ?>"><i class="bi bi-trash3"></i></button>
                            <div class="modal fade" id="delJadwal<?= (int)$j['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-sm"><div class="modal-content text-center">
                                    <div class="modal-body pt-4">
                                        <div class="mx-auto mb-3 d-flex align-items-center justify-content-center bg-danger bg-opacity-10 rounded-circle" style="width:56px;height:56px;"><i class="bi bi-trash3-fill text-danger fs-4"></i></div>
                                        <h6 class="fw-bold">Hapus Jadwal?</h6>
                                        <p class="text-muted small">Jadwal <strong><?= e($j['mata_pelajaran'] ?? 'Pelajaran') ?></strong> (<?= e($j['hari']) ?> – <?= e($j['nama_kelas']) ?>) akan dihapus permanen.</p>
                                    </div>
                                    <div class="modal-footer border-0 justify-content-center gap-2 pb-4">
                                        <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                                        <form action="<?= $baseUrl ?>/admin-jadwal/delete/<?= (int)$j['id'] ?>" method="POST" class="d-inline">
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
        <li class="page-item <?= $data['currentPage'] <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="<?= $baseUrl ?>/admin-jadwal?page=<?= $data['currentPage']-1 ?><?= !empty($data['keyword']) ? '&keyword='.urlencode($data['keyword']) : '' ?>"><i class="bi bi-chevron-left"></i></a></li>
        <?php for ($i = 1; $i <= $data['totalPages']; $i++): ?><li class="page-item <?= $i == $data['currentPage'] ? 'active' : '' ?>"><a class="page-link" href="<?= $baseUrl ?>/admin-jadwal?page=<?= $i ?><?= !empty($data['keyword']) ? '&keyword='.urlencode($data['keyword']) : '' ?>"><?= $i ?></a></li><?php endfor; ?>
        <li class="page-item <?= $data['currentPage'] >= $data['totalPages'] ? 'disabled' : '' ?>"><a class="page-link" href="<?= $baseUrl ?>/admin-jadwal?page=<?= $data['currentPage']+1 ?><?= !empty($data['keyword']) ? '&keyword='.urlencode($data['keyword']) : '' ?>"><i class="bi bi-chevron-right"></i></a></li>
    </ul></nav><?php endif; ?>
</div>
