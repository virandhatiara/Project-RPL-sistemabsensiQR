<?php $baseUrl = e(BASEURL); $csrf = e($_SESSION['csrf_token'] ?? ''); ?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/admin/dashboard" class="text-decoration-none">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/admin-user" class="text-decoration-none">Manajemen User</a></li>
        <li class="breadcrumb-item active">Tambah User & Admin</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <?php Flasher::flash(); ?>
        <div class="table-card">
            <h5 class="fw-bold mb-1"><i class="bi bi-person-plus-fill me-2 text-primary"></i>Tambah User & Admin</h5>
            <p class="text-muted small mb-4">Tambahkan akun baru beserta nama pemegang atau penanggung jawab akun.</p>

            <form action="<?= $baseUrl ?>/admin-user/store" method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                
                <div class="mb-3">
                    <label for="nama" class="form-label fw-semibold">Nama Pemegang / Penanggung Jawab <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nama" name="nama" required maxlength="100" placeholder="Contoh: Budi Santoso, S.Kom / Admin Lab">
                    <div class="form-text">Nama asli orang atau petugas yang memegang akun ini.</div>
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label fw-semibold">Username Login <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="username" name="username" required maxlength="50" placeholder="Contoh: admin_budi atau admin2" autocomplete="off">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="password" name="password" required minlength="6" placeholder="Minimal 6 karakter">
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label fw-semibold">Role / Hak Akses <span class="text-danger">*</span></label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="" disabled selected>— Pilih Role —</option>
                        <option value="admin">Admin (Administrator Sistem)</option>
                        <option value="kepala_sekolah">Kepala Sekolah</option>
                        <option value="guru">Guru</option>
                        <option value="wali_kelas">Wali Kelas</option>
                        <option value="siswa">Siswa</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="is_active" class="form-label fw-semibold">Status Akun <span class="text-danger">*</span></label>
                    <select class="form-select" id="is_active" name="is_active" required>
                        <option value="1" selected>Aktif (Dapat Login)</option>
                        <option value="0">Nonaktif (Diblokir Sementara)</option>
                    </select>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="<?= $baseUrl ?>/admin-user" class="btn btn-outline-secondary"><i class="bi bi-x-lg me-1"></i>Batal</a>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i>Simpan User</button>
                </div>
            </form>
        </div>
    </div>
</div>
