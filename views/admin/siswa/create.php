<?php $baseUrl = e(BASEURL); $csrf = e($_SESSION['csrf_token'] ?? ''); ?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/admin/dashboard" class="text-decoration-none">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/admin-siswa" class="text-decoration-none">Data Siswa</a></li>
        <li class="breadcrumb-item active">Tambah Siswa</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <?php Flasher::flash(); ?>
        <div class="table-card">
            <h5 class="fw-bold mb-4"><i class="bi bi-person-plus-fill me-2 text-primary"></i>Tambah Data Siswa</h5>
            <form action="<?= $baseUrl ?>/admin-siswa/store" method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

                <div class="mb-4">
                    <label for="nis" class="form-label">NIS <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nis" name="nis" required maxlength="20"
                        placeholder="Nomor Induk Siswa">
                </div>

                <div class="mb-4">
                    <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nama" name="nama" required maxlength="100"
                        placeholder="Nama lengkap siswa">
                </div>

                <div class="mb-4">
                    <label for="kelas_id" class="form-label">Kelas <span class="text-danger">*</span></label>
                    <select class="form-select" id="kelas_id" name="kelas_id" required>
                        <option value="" disabled selected>— Pilih Kelas —</option>
                        <?php foreach ($data['kelas'] as $k): ?>
                            <option value="<?= (int)$k['id'] ?>"><?= e($k['nama_kelas']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="card bg-white border border-light-subtle shadow-sm p-3 mb-4 rounded-3">
                    <h6 class="fw-bold mb-3 text-success d-flex align-items-center gap-2">
                        <i class="bi bi-whatsapp fs-5"></i>
                        <span>Data Kontak Orang Tua / Wali (Notifikasi WA)</span>
                    </h6>
                    <div class="mb-3">
                        <label for="nama_ortu" class="form-label fw-semibold">Nama Orang Tua / Wali</label>
                        <input type="text" class="form-control" id="nama_ortu" name="nama_ortu" maxlength="100"
                            placeholder="Contoh: Bapak Hendra / Ibu Ratna">
                        <div class="form-text"><i class="bi bi-info-circle me-1"></i>Nama ayah, ibu, atau wali murid siswa bersangkutan.</div>
                    </div>

                    <div class="mb-0">
                        <label for="no_hp_ortu" class="form-label fw-semibold">Nomor WhatsApp Orang Tua</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-telephone-fill"></i></span>
                            <input type="text" class="form-control font-monospace" id="no_hp_ortu" name="no_hp_ortu" maxlength="20"
                                placeholder="Contoh: 0895391621414 atau 62895391621414">
                        </div>
                        <div class="form-text text-success"><i class="bi bi-check2-circle me-1"></i>Notifikasi kehadiran otomatis via WhatsApp (Fonnte) akan dikirim langsung ke nomor ini saat siswa scan absen.</div>
                    </div>
                </div>

                <div class="card bg-light border-0 p-3 mb-4 rounded-3">
                    <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-shield-lock-fill me-1"></i>Akun Login Siswa</h6>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username" name="username" required maxlength="50"
                            placeholder="Username untuk login akun siswa">
                        <div class="form-text"><i class="bi bi-info-circle me-1"></i>Default otomatis mengikuti NIS jika tidak diubah.</div>
                    </div>

                    <div class="mb-0">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="4"
                            placeholder="Password untuk login akun siswa">
                        <div class="form-text"><i class="bi bi-key me-1"></i>Password yang digunakan siswa untuk login.</div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="<?= $baseUrl ?>/admin-siswa" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i>Batal
                    </a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-lg me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nisInput = document.getElementById('nis');
    const usernameInput = document.getElementById('username');
    let userEditedUsername = false;

    usernameInput.addEventListener('input', function() {
        userEditedUsername = this.value.trim() !== '';
    });

    nisInput.addEventListener('input', function() {
        if (!userEditedUsername) {
            usernameInput.value = this.value.trim();
        }
    });
});
</script>
