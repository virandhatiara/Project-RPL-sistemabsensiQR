<?php $baseUrl = e(BASEURL); $csrf = e($_SESSION['csrf_token'] ?? ''); $k = $data['kelas']; ?>

<nav aria-label="breadcrumb" class="mb-3"><ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/admin/dashboard" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/admin-kelas" class="text-decoration-none">Data Kelas</a></li>
    <li class="breadcrumb-item active">Edit Kelas</li>
</ol></nav>

<div class="row justify-content-center"><div class="col-lg-7">
    <?php Flasher::flash(); ?>
    <div class="table-card">
        <h5 class="fw-bold mb-4"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Data Kelas</h5>
        <form action="<?= $baseUrl ?>/admin-kelas/update" method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="id" value="<?= (int)$k['id'] ?>">
            <div class="mb-4">
                <label for="nama_kelas" class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nama_kelas" name="nama_kelas" required maxlength="50" value="<?= e($k['nama_kelas']) ?>">
            </div>
            <div class="mb-4">
                <label for="wali_kelas_id" class="form-label">Wali Kelas</label>
                <select class="form-select" id="wali_kelas_id" name="wali_kelas_id">
                    <option value="">— Belum ada wali kelas —</option>
                    <?php foreach ($data['guru'] as $g): ?>
                        <option value="<?= (int)$g['id'] ?>" <?= $g['id'] == $k['wali_kelas_id'] ? 'selected' : '' ?>>
                            <?= e($g['nama']) ?> (<?= e($g['nip']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="<?= $baseUrl ?>/admin-kelas" class="btn btn-outline-secondary"><i class="bi bi-x-lg me-1"></i>Batal</a>
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i>Update</button>
            </div>
        </form>
    </div>
</div></div>
