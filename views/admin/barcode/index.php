<?php
$csrf = e($_SESSION['csrf_token'] ?? ''); $keyword = e($data['keyword'] ?? '');
$baseUrl = e(BASEURL); $no = (($data['currentPage'] - 1) * 10) + 1;
$type = $data['type'];
?>
<?php Flasher::flash(); ?>
<nav aria-label="breadcrumb" class="mb-3"><ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/admin/dashboard" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item active">Data Barcode / QR</li>
</ol></nav>

<div class="table-card">
    <div class="table-toolbar">
        <div>
            <h5 class="fw-bold mb-0"><i class="bi bi-upc-scan me-2 text-primary"></i>Kelola Barcode/QR Code</h5>
            <p class="text-muted small mb-0">Kelola ID Barcode/QR untuk kebutuhan scan absensi Siswa dan Guru.</p>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-pills mb-4">
        <li class="nav-item">
            <a class="nav-link <?= $type === 'siswa' ? 'active' : '' ?>" href="<?= $baseUrl ?>/admin-barcode?type=siswa">Siswa</a>
        </li>
        <li class="nav-item ms-2">
            <a class="nav-link <?= $type === 'guru' ? 'active' : '' ?>" href="<?= $baseUrl ?>/admin-barcode?type=guru">Guru</a>
        </li>
    </ul>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <form action="<?= $baseUrl ?>/admin-barcode" method="GET" class="mb-0 flex-grow-1">
            <input type="hidden" name="type" value="<?= $type ?>">
            <div class="input-group" style="max-width:380px;">
                <input type="text" name="keyword" class="form-control" placeholder="Cari Nama atau Barcode ID…" value="<?= $keyword ?>">
                <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                <?php if (!empty($data['keyword'])): ?><a href="<?= $baseUrl ?>/admin-barcode?type=<?= $type ?>" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a><?php endif; ?>
            </div>
        </form>
        <a href="<?= $baseUrl ?>/admin-barcode/printMassal/<?= $type ?><?= !empty($data['keyword']) ? '?keyword='.urlencode($data['keyword']) : '' ?>" class="btn btn-primary" target="_blank">
            <i class="bi bi-printer me-2"></i>Cetak Massal
        </a>
    </div>

    <div class="table-responsive">
        <table class="table custom-table mb-0">
            <thead><tr>
                <th width="50">No</th>
                <th><?= $type === 'guru' ? 'NIP' : 'NIS' ?></th>
                <th>Nama <?= ucfirst($type) ?></th>
                <th>Barcode/QR ID</th>
                <th>Status</th>
                <th width="260">Aksi</th>
            </tr></thead>
            <tbody>
                <?php if (empty($data['items'])): ?>
                    <tr><td colspan="6"><div class="empty-state"><i class="bi bi-upc"></i><p><?= empty($data['keyword']) ? 'Belum ada data.' : 'Tidak ada hasil untuk "<strong>'.$keyword.'</strong>".' ?></p></div></td></tr>
                <?php else: foreach ($data['items'] as $item): ?>
                    <tr>
                        <td class="text-muted"><?= $no++ ?></td>
                        <td class="fw-semibold"><?= e($type === 'guru' ? $item['nip'] : $item['nis']) ?></td>
                        <td><?= e($item['nama']) ?></td>
                        <td><?= $item['barcode_id'] ? '<code class="text-primary">'.e($item['barcode_id']).'</code>' : '<span class="text-muted">–</span>' ?></td>
                        <td>
                            <span class="badge <?= $item['barcode_id'] ? 'badge-hadir' : 'badge-terlambat' ?>">
                                <i class="bi <?= $item['barcode_id'] ? 'bi-check-circle' : 'bi-exclamation-circle' ?> me-1"></i>
                                <?= $item['barcode_id'] ? 'Terdaftar' : 'Belum Terdaftar' ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <button type="button" 
                                        class="btn btn-sm btn-primary btn-action px-2" 
                                        onclick="previewQrModal('<?= e(addslashes($item['nama'])) ?>', '<?= e($type === 'guru' ? $item['nip'] : $item['nis']) ?>', '<?= e($item['barcode_id'] ?: ($type === 'guru' ? $item['nip'] : $item['nis'])) ?>', '<?= ucfirst($type) ?>')" 
                                        title="Lihat & Unduh QR Code">
                                    <i class="bi bi-qr-code"></i>
                                </button>
                                <?php if ($item['barcode_id']): ?>
                                    <a href="<?= $baseUrl ?>/admin-barcode/print/<?= $type ?>/<?= (int)$item['id'] ?>" class="btn btn-sm btn-info text-white btn-action px-2" target="_blank" title="Cetak Kartu">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="<?= $baseUrl ?>/admin-barcode/assign/<?= $type ?>/<?= (int)$item['id'] ?>" class="btn btn-sm btn-outline-primary btn-action px-2" title="Manual Assign">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="<?= $baseUrl ?>/admin-barcode/generate/<?= $type ?>/<?= (int)$item['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('<?= $item['barcode_id'] ? 'Yakin ingin me-reset (generate ulang) Barcode ini? Barcode lama tidak akan berlaku lagi.' : 'Generate otomatis Barcode?' ?>')">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                    <button type="submit" class="btn btn-sm <?= $item['barcode_id'] ? 'btn-outline-warning' : 'btn-primary' ?> btn-action px-2" title="Generate Otomatis">
                                        <i class="bi bi-magic me-1"></i> <?= $item['barcode_id'] ? 'Reset' : 'Generate' ?>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($data['totalPages'] > 1): ?><nav class="mt-4"><ul class="pagination justify-content-center mb-0">
        <li class="page-item <?= $data['currentPage'] <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="<?= $baseUrl ?>/admin-barcode?type=<?= $type ?>&page=<?= $data['currentPage']-1 ?><?= !empty($data['keyword']) ? '&keyword='.urlencode($data['keyword']) : '' ?>"><i class="bi bi-chevron-left"></i></a></li>
        <?php for ($i = 1; $i <= $data['totalPages']; $i++): ?><li class="page-item <?= $i == $data['currentPage'] ? 'active' : '' ?>"><a class="page-link" href="<?= $baseUrl ?>/admin-barcode?type=<?= $type ?>&page=<?= $i ?><?= !empty($data['keyword']) ? '&keyword='.urlencode($data['keyword']) : '' ?>"><?= $i ?></a></li><?php endfor; ?>
        <li class="page-item <?= $data['currentPage'] >= $data['totalPages'] ? 'disabled' : '' ?>"><a class="page-link" href="<?= $baseUrl ?>/admin-barcode?type=<?= $type ?>&page=<?= $data['currentPage']+1 ?><?= !empty($data['keyword']) ? '&keyword='.urlencode($data['keyword']) : '' ?>"><i class="bi bi-chevron-right"></i></a></li>
    </ul></nav><?php endif; ?>
</div>

<!-- Modal Quick QR Preview Admin -->
<div class="modal fade" id="modalAdminQr" tabindex="-1" aria-labelledby="modalAdminQrLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalAdminQrLabel">
                    <i class="bi bi-qr-code-scan text-primary me-2"></i>Preview Kode QR
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="d-inline-flex p-3 bg-white rounded-4 shadow border mb-3">
                    <div id="admin-qr-container" style="width:200px;height:200px;display:flex;align-items:center;justify-content:center;"></div>
                </div>
                <h5 class="fw-bold mb-1" id="adminQrNama">-</h5>
                <p class="text-muted mb-2"><span id="adminQrRoleLabel">NIS</span>: <strong class="text-dark font-monospace" id="adminQrNoInduk">-</strong></p>
                <div class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill font-monospace fs-6 mb-3" id="adminQrCodeId">
                    -
                </div>
                <p class="text-muted small mb-0">Kode QR ini dapat di-scan di kamera absensi mana pun oleh pengguna untuk mencatat presensi.</p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary px-4" id="btnAdminDownloadQr">
                    <i class="bi bi-download me-1"></i> Unduh QR PNG
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= $baseUrl ?>/js/qrcode.min.js"></script>
<script>
let currentAdminQrObj = null;
let currentAdminQrName = '';

function previewQrModal(nama, noInduk, codeVal, role) {
    document.getElementById('adminQrNama').textContent = nama;
    document.getElementById('adminQrNoInduk').textContent = noInduk;
    document.getElementById('adminQrRoleLabel').textContent = (role === 'Guru') ? 'NIP' : 'NIS';
    document.getElementById('adminQrCodeId').textContent = 'ID: ' + codeVal;
    currentAdminQrName = nama;

    const container = document.getElementById('admin-qr-container');
    container.innerHTML = '';
    currentAdminQrObj = new QRCode(container, {
        text: codeVal,
        width: 200,
        height: 200,
        colorDark: "#0f172a",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });

    const modal = new bootstrap.Modal(document.getElementById('modalAdminQr'));
    modal.show();
}

document.getElementById('btnAdminDownloadQr').addEventListener('click', function() {
    const img = document.querySelector('#admin-qr-container img');
    if (img && img.src) {
        const a = document.createElement('a');
        a.href = img.src;
        a.download = 'QR_' + currentAdminQrName.replace(/[^a-zA-Z0-9_-]/g, '_') + '.png';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }
});
</script>
