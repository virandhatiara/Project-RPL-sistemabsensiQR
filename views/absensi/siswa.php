<?php
// Pastikan token CSRF sudah diinisialisasi
$csrfToken = Csrf::getToken();
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-9 col-xl-8">

        <!-- Header Card -->
        <div class="card border-0 shadow-sm rounded-4 mb-3 overflow-hidden">
            <div class="card-body p-4 text-center bg-white">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle p-3 mb-2" style="width: 64px; height: 64px;">
                    <i class="bi bi-qr-code-scan fs-2"></i>
                </div>
                <h3 class="fw-bold mb-1 text-dark">Presensi Siswa — Scanner QR & Barcode</h3>
                <p class="text-muted small mb-0">Arahkan kartu pelajar (QR Code atau Barcode Batang) ke kamera perangkat (laptop / HP) atau gunakan scanner fisik USB.</p>
            </div>
        </div>

        <!-- Status Kehadiran Selector Card -->
        <div class="card border-0 shadow-sm rounded-4 mb-3 overflow-hidden">
            <div class="card-body p-3 p-md-4 bg-white">
                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-3">
                    <div>
                        <h6 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-person-check-fill text-primary"></i>
                            <span>Pilih Status Kehadiran Siswa</span>
                        </h6>
                        <small class="text-muted">Pilih status kehadiran sebelum melakukan scan QR Code / Barcode:</small>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-semibold" id="activeStatusBadge">
                        <i class="bi bi-check-circle-fill me-1"></i> Status: HADIR
                    </span>
                </div>

                <div class="row g-2" role="radiogroup" aria-label="Status Kehadiran Siswa">
                    <div class="col-4">
                        <input type="radio" class="btn-check" name="status_kehadiran" id="statusHadir" value="0" checked autocomplete="off">
                        <label class="btn btn-outline-success w-100 py-2 py-md-3 rounded-3 d-flex flex-column flex-md-row align-items-center justify-content-center gap-1 gap-md-2 fw-semibold shadow-sm" for="statusHadir">
                            <i class="bi bi-check-circle-fill fs-5"></i>
                            <span>Hadir</span>
                        </label>
                    </div>
                    <div class="col-4">
                        <input type="radio" class="btn-check" name="status_kehadiran" id="statusSakit" value="1" autocomplete="off">
                        <label class="btn btn-outline-warning w-100 py-2 py-md-3 rounded-3 d-flex flex-column flex-md-row align-items-center justify-content-center gap-1 gap-md-2 fw-semibold shadow-sm" for="statusSakit">
                            <i class="bi bi-heart-pulse-fill fs-5"></i>
                            <span>Sakit</span>
                        </label>
                    </div>
                    <div class="col-4">
                        <input type="radio" class="btn-check" name="status_kehadiran" id="statusIzin" value="2" autocomplete="off">
                        <label class="btn btn-outline-info w-100 py-2 py-md-3 rounded-3 d-flex flex-column flex-md-row align-items-center justify-content-center gap-1 gap-md-2 fw-semibold shadow-sm" for="statusIzin">
                            <i class="bi bi-envelope-paper-fill fs-5"></i>
                            <span>Izin</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Result / Feedback Notification Card -->
        <div id="scanResultContainer" class="d-none mb-3">
            <div id="scanResultCard" class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div id="resultIconWrapper" class="rounded-circle d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                                <i id="resultIcon" class="bi fs-3"></i>
                            </div>
                            <div>
                                <h5 id="resultTitle" class="fw-bold mb-0"></h5>
                                <small id="resultSubtitle" class="text-muted"></small>
                            </div>
                        </div>
                        <span id="resultBadge" class="badge px-3 py-2 fs-6 rounded-pill"></span>
                    </div>

                    <!-- Detail Data Pengguna (Siswa / Guru) -->
                    <div id="resultDetailBox" class="bg-light rounded-3 p-3 mb-3 border">
                        <!-- Banner Keterangan SUDAH ABSEN -->
                        <div class="alert alert-success d-flex align-items-center justify-content-between py-2 px-3 mb-3 border-0 rounded-3 shadow-sm" id="boxKeterangan">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-patch-check-fill fs-4 me-2 text-success" id="boxKetIcon"></i>
                                <div>
                                    <span class="text-muted small d-block" style="font-size:11px;">Keterangan Status</span>
                                    <strong class="fs-5" id="boxKetText">SUDAH ABSEN</strong>
                                </div>
                            </div>
                            <span class="badge bg-white text-dark border px-3 py-2 fw-semibold" id="boxKetRole">SISWA</span>
                        </div>
                        <div class="row g-2 text-start small">
                            <div class="col-6 col-md-3">
                                <span class="text-muted d-block" id="labelResNis">NIS</span>
                                <strong id="resNis" class="text-dark font-monospace fs-6">-</strong>
                            </div>
                            <div class="col-6 col-md-4">
                                <span class="text-muted d-block">Nama Lengkap</span>
                                <strong id="resNama" class="text-dark fs-6">-</strong>
                            </div>
                            <div class="col-6 col-md-2">
                                <span class="text-muted d-block" id="labelResKelas">Kelas</span>
                                <strong id="resKelas" class="text-dark fs-6">-</strong>
                            </div>
                            <div class="col-6 col-md-3">
                                <span class="text-muted d-block">Waktu Presensi</span>
                                <strong id="resWaktu" class="text-dark fs-6">-</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Cooldown Bar -->
                    <div class="d-flex align-items-center justify-content-between text-muted small">
                        <span><i class="bi bi-arrow-repeat me-1"></i> Siap memindai siswa berikutnya dalam: <strong id="countdownText">3s</strong></span>
                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" id="btnResumeManual">Scan Sekarang</button>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div id="cooldownProgress" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 100%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Scanner Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <!-- Camera Toolbar -->
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-camera-video text-primary"></i>
                            </span>
                            <select id="cameraSelect" class="form-select bg-light border-start-0" aria-label="Pilih Kamera">
                                <option value="">Mencari kamera...</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 text-md-end d-flex gap-2 justify-content-md-end">
                        <button type="button" id="btnToggleCamera" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1">
                            <i id="btnCameraIcon" class="bi bi-camera-video-fill"></i>
                            <span id="btnCameraText">Mulai Kamera</span>
                        </button>
                        <button type="button" id="btnFlipCamera" class="btn btn-sm btn-outline-secondary" title="Ganti Kamera Depan/Belakang">
                            <i class="bi bi-arrow-left-right"></i>
                        </button>
                        <button type="button" id="btnTorch" class="btn btn-sm btn-outline-secondary d-none" title="Nyalakan Lampu / Flash">
                            <i class="bi bi-lightning-charge"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body p-3 p-md-4 text-center">
                
                <!-- Live Camera Viewport Container -->
                <div class="scanner-viewport-wrapper position-relative mx-auto mb-3">
                    <div id="reader" class="scanner-viewport rounded-3 overflow-hidden"></div>

                    <!-- Overlay Viewfinder & Laser Animation -->
                    <div id="scannerOverlay" class="scanner-overlay d-none">
                        <div class="viewfinder-box">
                            <div class="corner top-left"></div>
                            <div class="corner top-right"></div>
                            <div class="corner bottom-left"></div>
                            <div class="corner bottom-right"></div>
                            <div class="laser-beam"></div>
                        </div>
                    </div>

                    <!-- Processing Overlay -->
                    <div id="processingOverlay" class="processing-overlay d-none">
                        <div class="spinner-border text-light mb-2" role="status"></div>
                        <h6 class="text-white fw-bold mb-0">Memverifikasi Presensi...</h6>
                        <small id="processingCode" class="text-white-50 font-monospace"></small>
                    </div>

                    <!-- Camera Placeholder when stopped -->
                    <div id="cameraStoppedNotice" class="camera-placeholder d-flex flex-column align-items-center justify-content-center p-4">
                        <i class="bi bi-camera-video-off display-3 text-secondary mb-2"></i>
                        <h6 class="fw-semibold text-secondary mb-1">Kamera Belum Aktif</h6>
                        <p class="text-muted small mb-3">Klik tombol "Mulai Kamera" di atas untuk mengaktifkan pemindaian otomatis via webcam/kamera HP.</p>
                        <button type="button" class="btn btn-primary btn-sm px-3" onclick="document.getElementById('btnToggleCamera').click()">
                            <i class="bi bi-play-fill me-1"></i> Aktifkan Kamera
                        </button>
                    </div>
                </div>

                <!-- Status Badge -->
                <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                    <span id="scanStatusBadge" class="badge bg-secondary-subtle text-secondary border px-3 py-2 rounded-pill">
                        <i class="bi bi-circle-fill me-1 small"></i> Kamera Mati
                    </span>
                    <span class="text-muted small">&bull;</span>
                    <span class="text-muted small">Mendukung QR Code & Barcode Batang (Code 128 / Code 39)</span>
                </div>

                <!-- Fallback / Physical USB Scanner Input -->
                <div class="card bg-light border-0 rounded-3 p-3">
                    <form id="formScanManual" action="<?= e(BASEURL); ?>/absensi/scanSiswa" method="POST">
                        <input type="hidden" name="csrf_token" id="csrf_token" value="<?= e($csrfToken); ?>">
                        <input type="hidden" name="is_ajax" value="1">
                        <input type="hidden" name="status" id="manual_status_input" value="0">

                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <i class="bi bi-upc-scan"></i>
                            </span>
                            <input type="text" 
                                   class="form-control border-start-0 font-monospace text-center bg-white" 
                                   id="barcode_id" 
                                   name="barcode_id" 
                                   placeholder="Atau arahkan barcode gun USB / ketik NIS manual di sini lalu Enter..." 
                                   autocomplete="off">
                            <button class="btn btn-primary px-3" type="submit" id="btnSubmitManual">
                                Presensi
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>

    </div>
</div>

<!-- Load Library html5-qrcode (Offline Local fallback dengan CDN backup) -->
<script src="<?= e(BASEURL); ?>/js/html5-qrcode.min.js"></script>
<script>
    if (typeof Html5Qrcode === 'undefined') {
        document.write('<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"><\/script>');
    }
</script>

<style>
/* Styling Scanner Viewport & Animations */
.scanner-viewport-wrapper {
    max-width: 480px;
    width: 100%;
    min-height: 320px;
    background: #0f172a;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2), 0 8px 10px -6px rgba(0, 0, 0, 0.2);
}

.scanner-viewport {
    width: 100%;
    min-height: 320px;
    background: transparent;
}

.scanner-viewport video {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    border-radius: 0.8rem;
}

/* Scanner Viewfinder Box */
.scanner-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}

.viewfinder-box {
    position: relative;
    width: 250px;
    height: 250px;
    border: 2px solid rgba(255, 255, 255, 0.25);
    border-radius: 16px;
    overflow: hidden;
}

/* Corner highlights */
.corner {
    position: absolute;
    width: 22px;
    height: 22px;
    border-color: #38bdf8;
    border-style: solid;
}
.corner.top-left { top: -2px; left: -2px; border-width: 4px 0 0 4px; border-top-left-radius: 12px; }
.corner.top-right { top: -2px; right: -2px; border-width: 4px 4px 0 0; border-top-right-radius: 12px; }
.corner.bottom-left { bottom: -2px; left: -2px; border-width: 0 0 4px 4px; border-bottom-left-radius: 12px; }
.corner.bottom-right { bottom: -2px; right: -2px; border-width: 0 4px 4px 0; border-bottom-right-radius: 12px; }

/* Animated Red/Cyan Laser Beam */
.laser-beam {
    position: absolute;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, transparent, #ef4444, #f43f5e, #ef4444, transparent);
    box-shadow: 0 0 12px 3px rgba(239, 68, 68, 0.8);
    animation: scanLaser 2.2s ease-in-out infinite alternate;
}

@keyframes scanLaser {
    0% { top: 5%; opacity: 0.8; }
    50% { opacity: 1; }
    100% { top: 92%; opacity: 0.8; }
}

/* Processing Overlay */
.processing-overlay {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(15, 23, 42, 0.85);
    backdrop-filter: blur(4px);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 20;
}

/* Camera Placeholder */
.camera-placeholder {
    min-height: 320px;
    background: #f8fafc;
    border: 2px dashed #cbd5e1;
    border-radius: 0.8rem;
}

/* Alert Card transition */
#scanResultCard {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // -------------------------------------------------------------
    // Inisialisasi Elemen DOM
    // -------------------------------------------------------------
    const cameraSelect         = document.getElementById('cameraSelect');
    const btnToggleCamera      = document.getElementById('btnToggleCamera');
    const btnCameraIcon        = document.getElementById('btnCameraIcon');
    const btnCameraText        = document.getElementById('btnCameraText');
    const btnFlipCamera        = document.getElementById('btnFlipCamera');
    const btnTorch             = document.getElementById('btnTorch');
    const scannerOverlay       = document.getElementById('scannerOverlay');
    const processingOverlay    = document.getElementById('processingOverlay');
    const processingCode       = document.getElementById('processingCode');
    const cameraStoppedNotice  = document.getElementById('cameraStoppedNotice');
    const scanStatusBadge      = document.getElementById('scanStatusBadge');
    
    // Result Notification elements
    const scanResultContainer  = document.getElementById('scanResultContainer');
    const scanResultCard       = document.getElementById('scanResultCard');
    const resultIconWrapper    = document.getElementById('resultIconWrapper');
    const resultIcon           = document.getElementById('resultIcon');
    const resultTitle          = document.getElementById('resultTitle');
    const resultSubtitle       = document.getElementById('resultSubtitle');
    const resultBadge          = document.getElementById('resultBadge');
    const resNis               = document.getElementById('resNis');
    const resNama              = document.getElementById('resNama');
    const resKelas             = document.getElementById('resKelas');
    const resWaktu             = document.getElementById('resWaktu');
    const countdownText        = document.getElementById('countdownText');
    const cooldownProgress     = document.getElementById('cooldownProgress');
    const btnResumeManual      = document.getElementById('btnResumeManual');

    // Input USB Manual elements
    const formScanManual       = document.getElementById('formScanManual');
    const barcodeInput         = document.getElementById('barcode_id');
    const csrfTokenInput       = document.getElementById('csrf_token');

    // -------------------------------------------------------------
    // State Scanner & Audio Beep
    // -------------------------------------------------------------
    let html5QrCode       = null;
    let isCameraRunning   = false;
    let isPaused          = false;
    let isProcessing      = false;
    let availableCameras  = [];
    let currentCameraId   = null;
    let currentFacingMode = "environment"; // default kamera belakang HP / laptop
    let isTorchOn         = false;
    let cooldownTimer     = null;
    let cooldownInterval  = null;

    // Web Audio API Beep Synthesizer (Tanpa perlu file audio external)
    function playBeep(type = 'success') {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            const ctx = new AudioContext();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.connect(gain);
            gain.connect(ctx.destination);

            if (type === 'success') {
                osc.type = 'sine';
                osc.frequency.setValueAtTime(880, ctx.currentTime); // A5 tone
                gain.gain.setValueAtTime(0.25, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.15);
                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + 0.15);
            } else if (type === 'warning') {
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(520, ctx.currentTime);
                gain.gain.setValueAtTime(0.2, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.25);
                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + 0.25);
            } else {
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(260, ctx.currentTime);
                gain.gain.setValueAtTime(0.3, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + 0.3);
            }
        } catch (e) {
            console.log("Audio not allowed or supported:", e);
        }
    }

    // -------------------------------------------------------------
    // Enumerasi Kamera Perangkat
    // -------------------------------------------------------------
    function initCameraList() {
        if (typeof Html5Qrcode === 'undefined') {
            console.error('Html5Qrcode library not loaded.');
            return;
        }

        Html5Qrcode.getCameras().then(devices => {
            availableCameras = devices;
            cameraSelect.innerHTML = '';

            if (devices && devices.length > 0) {
                devices.forEach((device, index) => {
                    const opt = document.createElement('option');
                    opt.value = device.id;
                    opt.textContent = device.label || `Kamera ${index + 1}`;
                    cameraSelect.appendChild(opt);
                });

                // Pilih kamera belakang jika ada (environment), atau kamera pertama
                let backCam = devices.find(d => d.label.toLowerCase().includes('back') || d.label.toLowerCase().includes('belakang') || d.label.toLowerCase().includes('environment'));
                currentCameraId = backCam ? backCam.id : devices[0].id;
                cameraSelect.value = currentCameraId;

                // Otomatis nyalakan kamera pertama kali
                startCamera(currentCameraId);
            } else {
                cameraSelect.innerHTML = '<option value="">Tidak ada kamera ditemukan</option>';
            }
        }).catch(err => {
            console.warn('Gagal mendapatkan daftar kamera:', err);
            cameraSelect.innerHTML = '<option value="">Izinkan akses kamera di browser</option>';
            // Fallback coba nyalakan dengan facingMode
            startCameraWithFacingMode("environment");
        });
    }

    // -------------------------------------------------------------
    // Start & Stop Camera
    // -------------------------------------------------------------
    function startCamera(cameraId) {
        if (isCameraRunning) {
            stopCamera().then(() => startCamera(cameraId));
            return;
        }

        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("reader");
        }

        const config = {
            fps: 15,
            qrbox: { width: 230, height: 230 },
            aspectRatio: 1.0,
            formatsToSupport: [
                Html5QrcodeSupportedFormats.QR_CODE,
                Html5QrcodeSupportedFormats.CODE_128,
                Html5QrcodeSupportedFormats.CODE_39,
                Html5QrcodeSupportedFormats.EAN_13,
                Html5QrcodeSupportedFormats.UPC_A
            ]
        };

        const cameraSource = cameraId ? { deviceId: { exact: cameraId } } : { facingMode: currentFacingMode };

        html5QrCode.start(
            cameraSource,
            config,
            onScanSuccess,
            onScanFailure
        ).then(() => {
            isCameraRunning = true;
            isPaused = false;
            cameraStoppedNotice.classList.add('d-none');
            scannerOverlay.classList.remove('d-none');
            processingOverlay.classList.add('d-none');

            btnCameraText.textContent = "Hentikan Kamera";
            btnCameraIcon.className = "bi bi-stop-fill";
            btnToggleCamera.className = "btn btn-sm btn-danger d-inline-flex align-items-center gap-1";

            scanStatusBadge.className = "badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill";
            scanStatusBadge.innerHTML = '<i class="bi bi-broadcast me-1"></i> Kamera Aktif &bull; Memindai...';

            // Cek kemampuan Torch (Lampu Flash)
            try {
                const capabilities = html5QrCode.getRunningTrackCapabilities();
                if (capabilities && capabilities.torch) {
                    btnTorch.classList.remove('d-none');
                } else {
                    btnTorch.classList.add('d-none');
                }
            } catch (e) {
                btnTorch.classList.add('d-none');
            }
        }).catch(err => {
            console.error('Gagal membuka kamera:', err);
            isCameraRunning = false;
            cameraStoppedNotice.classList.remove('d-none');
            scannerOverlay.classList.add('d-none');

            scanStatusBadge.className = "badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill";
            scanStatusBadge.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Gagal Membuka Kamera: ' + (err.message || err);
        });
    }

    function startCameraWithFacingMode(facingMode) {
        currentFacingMode = facingMode;
        startCamera(null);
    }

    function stopCamera() {
        if (!html5QrCode || !isCameraRunning) {
            return Promise.resolve();
        }

        return html5QrCode.stop().then(() => {
            isCameraRunning = false;
            isPaused = false;
            cameraStoppedNotice.classList.remove('d-none');
            scannerOverlay.classList.add('d-none');
            processingOverlay.classList.add('d-none');

            btnCameraText.textContent = "Mulai Kamera";
            btnCameraIcon.className = "bi bi-camera-video-fill";
            btnToggleCamera.className = "btn btn-sm btn-primary d-inline-flex align-items-center gap-1";

            scanStatusBadge.className = "badge bg-secondary-subtle text-secondary border px-3 py-2 rounded-pill";
            scanStatusBadge.innerHTML = '<i class="bi bi-circle-fill me-1 small"></i> Kamera Nonaktif';

            btnTorch.classList.add('d-none');
            isTorchOn = false;
        }).catch(err => {
            console.warn('Error saat stop kamera:', err);
        });
    }

    // Toggle Camera Start / Stop
    btnToggleCamera.addEventListener('click', function () {
        if (isCameraRunning) {
            stopCamera();
        } else {
            const selectedId = cameraSelect.value;
            startCamera(selectedId);
        }
    });

    // Pilih kamera dari Dropdown
    cameraSelect.addEventListener('change', function () {
        currentCameraId = this.value;
        if (isCameraRunning) {
            startCamera(currentCameraId);
        }
    });

    // Flip Kamera (Depan / Belakang)
    btnFlipCamera.addEventListener('click', function () {
        if (availableCameras.length > 1) {
            let currentIndex = availableCameras.findIndex(c => c.id === currentCameraId);
            let nextIndex = (currentIndex + 1) % availableCameras.length;
            currentCameraId = availableCameras[nextIndex].id;
            cameraSelect.value = currentCameraId;
            startCamera(currentCameraId);
        } else {
            currentFacingMode = (currentFacingMode === "user") ? "environment" : "user";
            startCameraWithFacingMode(currentFacingMode);
        }
    });

    // Toggle Torch (Flash)
    btnTorch.addEventListener('click', function () {
        if (!html5QrCode || !isCameraRunning) return;
        isTorchOn = !isTorchOn;
        html5QrCode.applyVideoConstraints({
            advanced: [{ torch: isTorchOn }]
        }).then(() => {
            btnTorch.className = isTorchOn ? "btn btn-sm btn-warning text-dark" : "btn btn-sm btn-outline-secondary";
        }).catch(err => console.log('Torch error:', err));
    });

    // -------------------------------------------------------------
    // Callback Scan Barcode / QR Code
    // -------------------------------------------------------------
    function onScanSuccess(decodedText, decodedResult) {
        if (isProcessing || isPaused) return;

        // 1. Kunci agar tidak men-scan berulang kali
        isProcessing = true;
        isPaused = true;

        // 2. Pause scanner kamera
        if (html5QrCode && isCameraRunning) {
            try {
                html5QrCode.pause(true);
            } catch (e) {
                console.log("Pause error:", e);
            }
        }

        // Tampilkan overlay processing
        processingCode.textContent = decodedText;
        processingOverlay.classList.remove('d-none');

        scanStatusBadge.className = "badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2 rounded-pill";
        scanStatusBadge.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Memproses: ' + decodedText;

        // 3. Kirim ke Backend via AJAX Fetch
        sendAttendanceRequest(decodedText);
    }

    function onScanFailure(error) {
        // Ignored: failure callback fires on every frame that doesn't contain a code
    }

    // -------------------------------------------------------------
    // Status Kehadiran Helper
    // -------------------------------------------------------------
    function getSelectedStatus() {
        const checked = document.querySelector('input[name="status_kehadiran"]:checked');
        return checked ? checked.value : '0';
    }

    function updateActiveStatusBadge(status) {
        const badge = document.getElementById('activeStatusBadge');
        if (!badge) return;
        const sVal = String(status).toLowerCase();
        if (sVal === '1' || sVal === 'sakit') {
            badge.className = 'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2 rounded-pill fw-semibold';
            badge.innerHTML = '<i class="bi bi-heart-pulse-fill me-1"></i> Status: SAKIT';
        } else if (sVal === '2' || sVal === 'izin') {
            badge.className = 'badge bg-info-subtle text-info-emphasis border border-info-subtle px-3 py-2 rounded-pill fw-semibold';
            badge.innerHTML = '<i class="bi bi-envelope-paper-fill me-1"></i> Status: IZIN';
        } else if (sVal === '3' || sVal === 'alpa' || sVal === 'alpha') {
            badge.className = 'badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill fw-semibold';
            badge.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i> Status: ALPHA';
        } else {
            badge.className = 'badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-semibold';
            badge.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Status: HADIR';
        }
    }

    document.querySelectorAll('input[name="status_kehadiran"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const manualInput = document.getElementById('manual_status_input');
            if (manualInput) manualInput.value = this.value;
            updateActiveStatusBadge(this.value);
        });
    });

    // -------------------------------------------------------------
    // AJAX Request ke Backend (Siswa Scan)
    // -------------------------------------------------------------
    function sendAttendanceRequest(code) {
        const selectedStatus = getSelectedStatus();
        const csrfToken = csrfTokenInput ? csrfTokenInput.value : '';
        const formData = new FormData();
        formData.append('barcode_id', code);
        formData.append('status', selectedStatus);
        formData.append('csrf_token', csrfToken);
        formData.append('is_ajax', '1');

        fetch('<?= e(BASEURL); ?>/absensi/scanSiswa', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok && response.status === 403) {
                throw new Error("Sesi token kedaluwarsa. Silakan refresh halaman.");
            }
            return response.json();
        })
        .then(data => {
            processingOverlay.classList.add('d-none');
            handleScanResponse(data, code);
        })
        .catch(err => {
            processingOverlay.classList.add('d-none');
            // Coba fallback ke proses_absen.php jika controller rute mengalami kendala
            fallbackToProsesAbsen(code, err.message);
        });
    }

    // Fallback jika /absensi/scanSiswa tidak merespons
    function fallbackToProsesAbsen(code, prevError) {
        const selectedStatus = getSelectedStatus();
        const formData = new FormData();
        formData.append('barcode_id', code);
        formData.append('status', selectedStatus);

        fetch('<?= e(BASEURL); ?>/proses_absen.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            handleScanResponse(data, code);
        })
        .catch(err => {
            handleScanResponse({
                status: 'error',
                message: 'Kendala server: ' + prevError
            }, code);
        });
    }

    // -------------------------------------------------------------
    // Tampilkan Feedback UI Hasil Scan
    // -------------------------------------------------------------
    function handleScanResponse(res, rawCode) {
        scanResultContainer.classList.remove('d-none');

        // Bersihkan timer countdown sebelumnya jika ada
        clearInterval(cooldownInterval);
        clearTimeout(cooldownTimer);

        const boxKeterangan = document.getElementById('boxKeterangan');
        const boxKetIcon    = document.getElementById('boxKetIcon');
        const boxKetText    = document.getElementById('boxKetText');
        const boxKetRole    = document.getElementById('boxKetRole');
        const labelResNis   = document.getElementById('labelResNis');
        const labelResKelas = document.getElementById('labelResKelas');

        if (res.status === 'success') {
            playBeep('success');

            const rawItemStatus = (res.data && res.data.status !== undefined) ? String(res.data.status).toLowerCase() : '0';
            let statusLabel = 'HADIR';
            let cardBorder = 'border-success';
            let iconBg = 'bg-success';
            let badgeClass = 'bg-success text-white';
            let alertClass = 'alert-success';
            let iconClass = 'bi-patch-check-fill text-success';

            if (rawItemStatus === '1' || rawItemStatus === 'sakit') {
                statusLabel = 'SAKIT';
                cardBorder = 'border-warning';
                iconBg = 'bg-warning text-dark';
                badgeClass = 'bg-warning text-dark';
                alertClass = 'alert-warning';
                iconClass = 'bi-heart-pulse-fill text-warning';
            } else if (rawItemStatus === '2' || rawItemStatus === 'izin') {
                statusLabel = 'IZIN';
                cardBorder = 'border-info';
                iconBg = 'bg-info text-white';
                badgeClass = 'bg-info text-white';
                alertClass = 'alert-info';
                iconClass = 'bi-envelope-paper-fill text-info';
            } else if (rawItemStatus === '3' || rawItemStatus === 'alpa' || rawItemStatus === 'alpha') {
                statusLabel = 'ALPHA';
                cardBorder = 'border-danger';
                iconBg = 'bg-danger text-white';
                badgeClass = 'bg-danger text-white';
                alertClass = 'alert-danger';
                iconClass = 'bi-x-circle-fill text-danger';
            } else if (rawItemStatus === 'terlambat') {
                statusLabel = 'TERLAMBAT';
                cardBorder = 'border-warning';
                iconBg = 'bg-warning text-dark';
                badgeClass = 'bg-warning text-dark';
                alertClass = 'alert-warning';
                iconClass = 'bi-clock-history text-warning';
            }

            scanResultCard.className = "card border-0 shadow-lg rounded-4 overflow-hidden border-start " + cardBorder + " border-4";
            resultIconWrapper.className = "rounded-circle d-flex align-items-center justify-content-center " + iconBg;
            resultIcon.className = "bi bi-check-lg fs-3";

            resultTitle.innerHTML = '<span class="fw-bold"><i class="bi ' + iconClass + ' me-1"></i> Presensi Tercatat: ' + statusLabel + '</span>';
            resultSubtitle.textContent = res.message || "Presensi berhasil dicatat dan diverifikasi.";

            resultBadge.className = "badge px-3 py-2 fs-6 rounded-pill shadow-sm " + badgeClass;
            resultBadge.textContent = "STATUS: " + statusLabel;

            if (boxKeterangan) {
                boxKeterangan.className = "alert d-flex align-items-center justify-content-between py-2 px-3 mb-3 border-0 rounded-3 shadow-sm " + alertClass;
                boxKetIcon.className = "bi fs-4 me-2 " + iconClass;
                boxKetText.textContent = "SUDAH ABSEN (" + statusLabel + ")";
            }

            if (res.data) {
                const isGuru = (res.data.role === 'Guru');
                if (boxKetRole) boxKetRole.textContent = isGuru ? 'GURU' : 'SISWA';
                if (labelResNis) labelResNis.textContent = isGuru ? 'NIP' : 'NIS';
                if (labelResKelas) labelResKelas.textContent = isGuru ? 'Tipe' : 'Kelas';

                resNis.textContent   = isGuru ? (res.data.nip || '-') : (res.data.nis || '-');
                resNama.textContent  = res.data.nama || '-';
                resKelas.textContent = isGuru ? (res.data.tipe || 'Guru') : (res.data.kelas || '-');
                resWaktu.textContent = (res.data.jam || '-') + ' WIB';
            }
        } 
        else if (res.status === 'warning') {
            playBeep('warning');

            scanResultCard.className = "card border-0 shadow-lg rounded-4 overflow-hidden border-start border-warning border-4";
            resultIconWrapper.className = "rounded-circle d-flex align-items-center justify-content-center bg-warning text-dark";
            resultIcon.className = "bi bi-exclamation-triangle-fill fs-3";

            resultTitle.innerHTML = '<span class="text-warning-emphasis fw-bold"><i class="bi bi-info-circle-fill me-1"></i> Keterangan: SUDAH ABSEN</span>';
            resultSubtitle.textContent = res.message || "Pengguna ini sudah melakukan presensi hari ini.";

            resultBadge.className = "badge bg-warning text-dark px-3 py-2 fs-6 rounded-pill shadow-sm";
            resultBadge.textContent = "SUDAH ABSEN";

            if (boxKeterangan) {
                boxKeterangan.className = "alert alert-warning d-flex align-items-center justify-content-between py-2 px-3 mb-3 border-0 rounded-3 shadow-sm";
                boxKetIcon.className = "bi bi-info-circle-fill fs-4 me-2 text-warning";
                boxKetText.textContent = "SUDAH ABSEN HARI INI";
            }

            if (res.data) {
                const isGuru = (res.data.role === 'Guru');
                if (boxKetRole) boxKetRole.textContent = isGuru ? 'GURU' : 'SISWA';
                if (labelResNis) labelResNis.textContent = isGuru ? 'NIP' : 'NIS';
                if (labelResKelas) labelResKelas.textContent = isGuru ? 'Tipe' : 'Kelas';

                resNis.textContent   = isGuru ? (res.data.nip || '-') : (res.data.nis || '-');
                resNama.textContent  = res.data.nama || '-';
                resKelas.textContent = isGuru ? (res.data.tipe || 'Guru') : (res.data.kelas || '-');
                resWaktu.textContent = (res.data.jam || res.data.waktu || '-') + ' WIB';
            }
        } 
        else {
            playBeep('error');

            scanResultCard.className = "card border-0 shadow-lg rounded-4 overflow-hidden border-start border-danger border-4";
            resultIconWrapper.className = "rounded-circle d-flex align-items-center justify-content-center bg-danger text-white";
            resultIcon.className = "bi bi-x-lg fs-3";

            resultTitle.innerHTML = '<span class="text-danger fw-bold"><i class="bi bi-x-circle-fill me-1"></i> Presensi Gagal</span>';
            resultSubtitle.textContent = res.message || "Kode QR / Barcode tidak dikenali.";

            resultBadge.className = "badge bg-danger text-white px-3 py-2 fs-6 rounded-pill";
            resultBadge.textContent = "BELUM TERDAFTAR";

            if (boxKeterangan) {
                boxKeterangan.className = "alert alert-danger d-flex align-items-center justify-content-between py-2 px-3 mb-3 border-0 rounded-3 shadow-sm";
                boxKetIcon.className = "bi bi-x-circle-fill fs-4 me-2 text-danger";
                boxKetText.textContent = "BELUM ABSEN";
                if (boxKetRole) boxKetRole.textContent = "TIDAK DIKENAL";
            }

            resNis.textContent   = rawCode;
            resNama.textContent  = "Tidak Dikenal";
            resKelas.textContent = "-";
            resWaktu.textContent = "-";
        }

        // Scroll hasil presensi ke pandangan jika perlu
        scanResultContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        // Mulai Countdown untuk reset kamera
        startCooldownTimer(2.8);
    }

    // -------------------------------------------------------------
    // Cooldown Timer & Reset Kamera untuk Siswa Berikutnya
    // -------------------------------------------------------------
    function startCooldownTimer(seconds) {
        let remaining = seconds;
        const totalMs = seconds * 1000;
        const startTime = Date.now();

        countdownText.textContent = Math.ceil(remaining) + 's';
        cooldownProgress.style.width = '100%';

        cooldownInterval = setInterval(() => {
            const elapsed = Date.now() - startTime;
            const pct = Math.max(0, 100 - (elapsed / totalMs * 100));
            cooldownProgress.style.width = pct + '%';

            const secsLeft = Math.max(0, Math.ceil((totalMs - elapsed) / 1000));
            countdownText.textContent = secsLeft + 's';

            if (elapsed >= totalMs) {
                clearInterval(cooldownInterval);
                resumeScanning();
            }
        }, 100);
    }

    function resumeScanning() {
        clearInterval(cooldownInterval);
        clearTimeout(cooldownTimer);
        cooldownProgress.style.width = '0%';

        isProcessing = false;
        isPaused = false;

        // Resume camera frame stream
        if (html5QrCode && isCameraRunning) {
            try {
                html5QrCode.resume();
            } catch (e) {
                console.log('Resume error:', e);
            }
        }

        scanStatusBadge.className = "badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill";
        scanStatusBadge.innerHTML = '<i class="bi bi-broadcast me-1"></i> Kamera Aktif &bull; Memindai...';

        // Kembalikan fokus ke input USB
        if (barcodeInput) {
            barcodeInput.value = '';
            barcodeInput.focus();
        }
    }

    btnResumeManual.addEventListener('click', function () {
        resumeScanning();
    });

    // -------------------------------------------------------------
    // Handle Input USB Scanner / Manual Form
    // -------------------------------------------------------------
    if (formScanManual) {
        formScanManual.addEventListener('submit', function (e) {
            e.preventDefault();
            const code = barcodeInput.value.trim();
            if (code && !isProcessing) {
                onScanSuccess(code, null);
                barcodeInput.value = '';
            }
        });
    }

    // Deteksi cepat dari USB barcode gun yang mengirim teks diakhiri Enter
    let usbTimer;
    if (barcodeInput) {
        barcodeInput.focus();
        barcodeInput.addEventListener('input', function () {
            clearTimeout(usbTimer);
            usbTimer = setTimeout(() => {
                const val = barcodeInput.value.trim();
                if (val.length >= 4 && !isProcessing && !isPaused) {
                    onScanSuccess(val, null);
                    barcodeInput.value = '';
                }
            }, 250);
        });
    }

    // -------------------------------------------------------------
    // Mulai proses inisialisasi kamera
    // -------------------------------------------------------------
    initCameraList();
});
</script>
