<div class="login-card">
    <div class="login-image">
        <i class="bi bi-shield-lock"></i>
        <h2 class="fw-bold mb-2">SMK NU WARGABINANGUN</h2>
        <p class="text-white-50">Sistem Absensi & Presensi Digital Terintegrasi</p>
    </div>
    
    <div class="login-form-wrapper">
        <div class="mb-4">
            <h4 class="fw-bold text-dark">Selamat Datang 👋</h4>
            <p class="text-muted small">Silakan masuk menggunakan kredensial Anda.</p>
        </div>

        <?php Flasher::flash(); ?>

        <form action="<?= e(BASEURL); ?>/auth/login" method="POST">
            <input type="hidden" name="csrf_token" value="<?= e(Csrf::getToken()); ?>">

            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" required autocomplete="off">
                <label for="username">Username / NIS / NIP</label>
            </div>
            
            <div class="form-floating mb-4">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                <label for="password">Password</label>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-sm">
                Masuk ke Sistem <i class="bi bi-box-arrow-in-right ms-2"></i>
            </button>
        </form>
    </div>
</div>
