<?php 
$baseUrl = e(BASEURL); 
$csrf = e($_SESSION['csrf_token'] ?? ''); 
$u = $data['user']; 
$isSelf = ($u['id'] == $_SESSION['user_id']); 
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/admin/dashboard" class="text-decoration-none">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/admin-user" class="text-decoration-none">Manajemen User</a></li>
        <li class="breadcrumb-item active">Edit User & Admin</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <?php Flasher::flash(); ?>
        <div class="table-card">
            <h5 class="fw-bold mb-1"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Data User & Admin</h5>
            <p class="text-muted small mb-4">Ubah nama pemegang akun, username, password, atau role akses.</p>

            <?php if ($isSelf): ?>
                <div class="alert alert-info d-flex align-items-center gap-2 mb-4 py-2 px-3">
                    <i class="bi bi-info-circle-fill flex-shrink-0 fs-5"></i>
                    <span class="small">Anda sedang mengedit akun Anda sendiri. Role dan status aktif terkunci untuk keamanan akun.</span>
                </div>
            <?php endif; ?>

            <form action="<?= $baseUrl ?>/admin-user/update" method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                <?php if ($isSelf): ?>
                    <input type="hidden" name="role" value="<?= e($u['role']) ?>">
                    <input type="hidden" name="is_active" value="<?= (int)$u['is_active'] ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="nama" class="form-label fw-semibold">Nama Pemegang / Penanggung Jawab <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nama" name="nama" required maxlength="100" value="<?= e($u['nama'] ?? '') ?>" placeholder="Nama lengkap orang yang memegang akun ini">
                    <div class="form-text">Nama orang atau staf yang memegang / mengoperasikan akun ini.</div>
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label fw-semibold">Username Login <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="username" name="username" required maxlength="50" value="<?= e($u['username']) ?>">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">Password Baru
                        <span class="text-muted fw-normal small">(Kosongkan jika tidak ingin mengubah password)</span>
                    </label>
                    <input type="password" class="form-control" id="password" name="password" minlength="6" placeholder="Masukkan password baru jika ingin diganti">
                </div>

                <?php if (!$isSelf): ?>
                    <div class="mb-3">
                        <label for="role" class="form-label fw-semibold">Role / Hak Akses <span class="text-danger">*</span></label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="admin" <?= $u['role']=='admin' ? 'selected' : '' ?>>Admin (Administrator Sistem)</option>
                            <option value="kepala_sekolah" <?= $u['role']=='kepala_sekolah' ? 'selected' : '' ?>>Kepala Sekolah</option>
                            <option value="guru" <?= $u['role']=='guru' ? 'selected' : '' ?>>Guru</option>
                            <option value="wali_kelas" <?= $u['role']=='wali_kelas' ? 'selected' : '' ?>>Wali Kelas</option>
                            <option value="siswa" <?= $u['role']=='siswa' ? 'selected' : '' ?>>Siswa</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="is_active" class="form-label fw-semibold">Status Akun <span class="text-danger">*</span></label>
                        <select class="form-select" id="is_active" name="is_active" required>
                            <option value="1" <?= $u['is_active']==1 ? 'selected' : '' ?>>Aktif (Dapat Login)</option>
                            <option value="0" <?= $u['is_active']==0 ? 'selected' : '' ?>>Nonaktif (Diblokir Sementara)</option>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="<?= $baseUrl ?>/admin-user" class="btn btn-outline-secondary"><i class="bi bi-x-lg me-1"></i>Batal</a>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i>Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>
