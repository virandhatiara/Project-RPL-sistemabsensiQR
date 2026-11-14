<?php $baseUrl = e(BASEURL); $csrf = e($_SESSION['csrf_token'] ?? ''); $g = $data['guru']; ?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/admin/dashboard" class="text-decoration-none">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/admin-guru" class="text-decoration-none">Data Guru</a></li>
        <li class="breadcrumb-item active">Edit Guru</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <?php Flasher::flash(); ?>
        <div class="table-card">
            <h5 class="fw-bold mb-4"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Data Guru</h5>
            <form action="<?= $baseUrl ?>/admin-guru/update" method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="id" value="<?= (int)$g['id'] ?>">

                <div class="mb-4">
                    <label for="nip" class="form-label">NIP <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nip" name="nip" required maxlength="20" value="<?= e($g['nip']) ?>">
                </div>

                <div class="mb-4">
                    <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nama" name="nama" required maxlength="100" value="<?= e($g['nama']) ?>" placeholder="Contoh: Soekarno, S.Pd (tanpa sebutan Bapak/Ibu)">
                    <div class="form-text"><i class="bi bi-info-circle me-1"></i>Gunakan nama lengkap langsung tanpa sebutan "Bapak" atau "Ibu".</div>
                </div>

                <div class="card bg-light border-0 p-3 mb-4 rounded-3">
                    <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-shield-lock-fill me-1"></i>Akun Login Guru</h6>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username" name="username" required maxlength="50" value="<?= e($g['username'] ?? $g['nip']) ?>" placeholder="Username login akun guru">
                    </div>

                    <div class="mb-0">
                        <label for="password" class="form-label">Password Baru</label>
                        <input type="password" class="form-control" id="password" name="password" minlength="4" placeholder="Kosongkan jika tidak ingin mengubah password">
                        <div class="form-text"><i class="bi bi-info-circle me-1"></i>Biarkan kosong jika tetap menggunakan password saat ini.</div>
                    </div>
                </div>

                <div class="card bg-light border-0 p-3 mb-4 rounded-3">
                    <h6 class="fw-bold mb-3 text-success"><i class="bi bi-whatsapp me-1"></i>Kontak & WhatsApp Notifikasi</h6>
                    <div class="mb-0">
                        <label for="no_wa" class="form-label">Nomor HP / WhatsApp Guru</label>
                        <?php $noWaVal = !empty($g['no_wa']) ? $g['no_wa'] : (!empty($g['no_hp']) ? $g['no_hp'] : ''); ?>
                        <input type="tel" class="form-control" id="no_wa" name="no_wa" maxlength="20" value="<?= e($noWaVal) ?>" placeholder="Contoh: 081234567890">
                        <div class="form-text text-success"><i class="bi bi-check2-circle me-1"></i>Notifikasi kehadiran otomatis via WhatsApp (Fonnte) akan dikirim langsung ke nomor ini saat guru scan absen Masuk & Pulang.</div>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="barcode_id" class="form-label">Barcode ID</label>
                    <input type="text" class="form-control" id="barcode_id" name="barcode_id" maxlength="50" value="<?= e($g['barcode_id'] ?? '') ?>" placeholder="Kosongkan jika belum ada">
                    <div class="form-text"><i class="bi bi-upc-scan me-1"></i>Barcode digunakan untuk scan absensi guru.</div>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="<?= $baseUrl ?>/admin-guru" class="btn btn-outline-secondary"><i class="bi bi-x-lg me-1"></i>Batal</a>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i>Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
