<?php 
$baseUrl = e(BASEURL); 
$csrf = e($_SESSION['csrf_token'] ?? ''); 
$item = $data['item']; 
$type = $data['type']; 
$idNumber = $type === 'guru' ? $item['nip'] : $item['nis'];
$extraInfo = $type === 'siswa' && isset($item['nama_kelas']) ? ' &bull; Kelas: '.e($item['nama_kelas']) : '';
?>

<nav aria-label="breadcrumb" class="mb-3"><ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/admin/dashboard" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/admin-barcode?type=<?= $type ?>" class="text-decoration-none">Data Barcode</a></li>
    <li class="breadcrumb-item active"><?= $item['barcode_id'] ? 'Ubah Barcode' : 'Daftarkan Barcode' ?></li>
</ol></nav>

<div class="row justify-content-center"><div class="col-lg-6">
    <?php Flasher::flash(); ?>
    <div class="table-card">
        <h5 class="fw-bold mb-4"><i class="bi bi-upc-scan me-2 text-primary"></i><?= $item['barcode_id'] ? 'Ubah Barcode '.ucfirst($type) : 'Daftarkan Barcode '.ucfirst($type) ?></h5>

        <div class="p-3 rounded-3 mb-4" style="background:#f0f7ff;border:1px solid #bfdbfe;">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar-circle" style="width:44px;height:44px;font-size:1.2rem;"><?= strtoupper(substr($item['nama'], 0, 1)) ?></div>
                <div>
                    <div class="fw-bold"><?= e($item['nama']) ?></div>
                    <div class="text-muted small"><?= strtoupper($type === 'guru' ? 'NIP' : 'NIS') ?>: <?= e($idNumber) ?><?= $extraInfo ?></div>
                    <?php if ($item['barcode_id']): ?>
                        <div class="mt-1"><code class="text-primary"><?= e($item['barcode_id']) ?></code> <small class="text-muted">— Barcode saat ini</small></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <form action="<?= $baseUrl ?>/admin-barcode/store" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
            <input type="hidden" name="type" value="<?= $type ?>">
            <div class="mb-4">
                <label for="barcode_id" class="form-label fw-semibold">Barcode / QR ID Baru</label>
                <input type="text" class="form-control form-control-lg text-center font-monospace"
                    id="barcode_id" name="barcode_id"
                    value="<?= e($item['barcode_id'] ?? '') ?>"
                    placeholder="Scan Barcode/QR Code…"
                    autofocus autocomplete="off">
                <div class="form-text text-center mt-2">
                    <i class="bi bi-info-circle me-1"></i>
                    Klik kotak di atas, lalu gunakan scanner untuk membaca Barcode/QR Code.
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="<?= $baseUrl ?>/admin-barcode?type=<?= $type ?>" class="btn btn-outline-secondary"><i class="bi bi-x-lg me-1"></i>Batal</a>
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i>Simpan Barcode</button>
            </div>
        </form>
    </div>
</div></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const barcodeInput = document.getElementById('barcode_id');
    let timer;
    barcodeInput.addEventListener('input', function() {
        clearTimeout(timer);
        timer = setTimeout(function() {
            // Auto-submit setelah 800ms tanpa input (opsional)
        }, 800);
    });
});
</script>
