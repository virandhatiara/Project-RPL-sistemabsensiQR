<?php $baseUrl = e(BASEURL); $csrf = e($_SESSION['csrf_token'] ?? ''); ?>

<nav aria-label="breadcrumb" class="mb-3"><ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/admin/dashboard" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/admin-jadwal" class="text-decoration-none">Data Jadwal</a></li>
    <li class="breadcrumb-item active">Tambah Jadwal</li>
</ol></nav>

<div class="row justify-content-center"><div class="col-lg-7">
    <?php Flasher::flash(); ?>
    <div class="table-card">
        <h5 class="fw-bold mb-4"><i class="bi bi-calendar-plus me-2 text-primary"></i>Tambah Data Jadwal</h5>
        <form action="<?= $baseUrl ?>/admin-jadwal/store" method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <div class="mb-4">
                <label for="kelas_id" class="form-label">Kelas <span class="text-danger">*</span></label>
                <select class="form-select" id="kelas_id" name="kelas_id" required>
                    <option value="" disabled selected>— Pilih Kelas —</option>
                    <?php foreach ($data['kelas'] as $k): ?>
                        <option value="<?= (int)$k['id'] ?>"><?= e($k['nama_kelas']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="mata_pelajaran" class="form-label">Mata Pelajaran <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="mata_pelajaran" name="mata_pelajaran" placeholder="Contoh: Pemrograman Web, Matematika, Basis Data..." required>
                <div class="form-text">Masukkan nama mata pelajaran atau kompetensi keahlian yang diajarkan.</div>
            </div>
            <div class="mb-4">
                <label for="guru_id" class="form-label">Guru Pengajar <span class="text-danger">*</span></label>
                <select class="form-select" id="guru_id" name="guru_id" required>
                    <option value="" disabled selected>— Pilih Guru —</option>
                    <?php foreach ($data['guru'] as $g): ?>
                        <option value="<?= (int)$g['id'] ?>"><?= e($g['nama']) ?> (<?= e($g['nip']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="hari" class="form-label">Hari <span class="text-danger">*</span></label>
                <select class="form-select" id="hari" name="hari" required>
                    <option value="" disabled selected>— Pilih Hari —</option>
                    <?php foreach (['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $h): ?>
                        <option value="<?= $h ?>"><?= $h ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <label for="jam_mulai" class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                    <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" required>
                </div>
                <div class="col-6">
                    <label for="jam_selesai" class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                    <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" required>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="<?= $baseUrl ?>/admin-jadwal" class="btn btn-outline-secondary"><i class="bi bi-x-lg me-1"></i>Batal</a>
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i>Simpan</button>
            </div>
        </form>
    </div>
</div></div>
