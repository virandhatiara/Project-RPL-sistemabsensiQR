<?php
// Pastikan token CSRF sudah diinisialisasi
$csrfToken = Csrf::getToken();
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-9 col-xl-8">

        <!-- Header Card -->
        <div class="card border-0 shadow-sm rounded-4 mb-3 overflow-hidden">
            <div class="card-body p-4 text-center bg-white">
                <div class="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success rounded-circle p-3 mb-2" style="width: 64px; height: 64px;">
                    <i class="bi bi-person-badge-fill fs-2"></i>
                </div>
                <h3 class="fw-bold mb-1 text-dark">Presensi Guru & Tenaga Pendidik</h3>
                <p class="text-muted small mb-0">Arahkan kartu pengenal guru (QR Code atau Barcode Batang) ke kamera perangkat untuk mencatat Jam Masuk / Jam Pulang.</p>
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

                    <!-- Detail Data Pengguna (Guru / Siswa) -->
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
                            <span class="badge bg-white text-dark border px-3 py-2 fw-semibold" id="boxKetRole">GURU</span>
                        </div>
                        <div class="row g-2 text-start small">
                            <div class="col-6 col-md-3">
                                <span class="text-muted d-block" id="labelResNip">NIP</span>
                                <strong id="resNip" class="text-dark font-monospace fs-6">-</strong>
                            </div>
                            <div class="col-6 col-md-5">
                                <span class="text-muted d-block">Nama Lengkap</span>
                                <strong id="resNama" class="text-dark fs-6">-</strong>
                            </div>
                            <div class="col-6 col-md-2">
                                <span class="text-muted d-block" id="labelResTipe">Tipe / Info</span>
                                <strong id="resTipe" class="text-dark fs-6">-</strong>
                            </div>
                            <div class="col-6 col-md-2">
                                <span class="text-muted d-block">Waktu</span>
                                <strong id="resWaktu" class="text-dark fs-6">-</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Cooldown Bar -->
                    <div class="d-flex align-items-center justify-content-between text-muted small">
                        <span><i class="bi bi-arrow-repeat me-1"></i> Siap memindai guru berikutnya dalam: <strong id="countdownText">3s</strong></span>
                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" id="btnResumeManual">Scan Sekarang</button>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div id="cooldownProgress" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 100%;"></div>
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
                                <i class="bi bi-camera-video text-success"></i>
                            </span>
                            <select id="cameraSelect" class="form-select bg-light border-start-0" aria-label="Pilih Kamera">
                                <option value="">Mencari kamera...</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 text-md-end d-flex gap-2 justify-content-md-end">
                        <button type="button" id="btnToggleCamera" class="btn btn-sm btn-success d-inline-flex align-items-center gap-1">
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
                        <h6 class="text-white fw-bold mb-0">Memverifikasi Presensi Guru...</h6>
                        <small id="processingCode" class="text-white-50 font-monospace"></small>
                    </div>

                    <!-- Camera Placeholder when stopped -->
                    <div id="cameraStoppedNotice" class="camera-placeholder d-flex flex-column align-items-center justify-content-center p-4">
                        <i class="bi bi-camera-video-off display-3 text-secondary mb-2"></i>
                        <h6 class="fw-semibold text-secondary mb-1">Kamera Belum Aktif</h6>
                        <p class="text-muted small mb-3">Klik tombol "Mulai Kamera" di atas untuk mengaktifkan pemindaian otomatis via webcam/kamera HP.</p>
                        <button type="button" class="btn btn-success btn-sm px-3" onclick="document.getElementById('btnToggleCamera').click()">
                            <i class="bi bi-play-fill me-1"></i> Aktifkan Kamera
                        </button>
                    </div>
                </div>

                <!-- Status Badge -->
                <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                    <span id="scanStatusBadge" class="badge bg-secondary-subtle text-secondary border px-3 py-2 rounded-pill">
                        <i class="bi bi-circle-fill me-1 small"></i> Kamera Nonaktif
                    </span>
                    <span class="text-muted small">&bull;</span>
                    <span class="text-muted small">Mendukung QR Code & Barcode Batang (Code 128 / Code 39)</span>
                </div>

                <!-- Fallback / Physical USB Scanner Input -->
                <div class="card bg-light border-0 rounded-3 p-3">
                    <form id="formScanManual" action="<?= e(BASEURL); ?>/absensi/scanGuru" method="POST">
                        <input type="hidden" name="csrf_token" id="csrf_token" value="<?= e($csrfToken); ?>">
                        <input type="hidden" name="is_ajax" value="1">

                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <i class="bi bi-upc-scan"></i>
                            </span>
                            <input type="text" 
                                   class="form-control border-start-0 font-monospace text-center bg-white" 
                                   id="barcode_id" 
                                   name="barcode_id" 
                                   placeholder="Atau arahkan barcode gun USB / ketik NIP manual di sini lalu Enter..." 
                                   autocomplete="off">
                            <button class="btn btn-success px-3" type="submit" id="btnSubmitManual">
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
    border-color: #10b981;
    border-style: solid;
}
.corner.top-left { top: -2px; left: -2px; border-width: 4px 0 0 4px; border-top-left-radius: 12px; }
.corner.top-right { top: -2px; right: -2px; border-width: 4px 4px 0 0; border-top-right-radius: 12px; }
.corner.bottom-left { bottom: -2px; left: -2px; border-width: 0 0 4px 4px; border-bottom-left-radius: 12px; }
.corner.bottom-right { bottom: -2px; right: -2px; border-width: 0 4px 4px 0; border-bottom-right-radius: 12px; }

/* Animated Green/Emerald Laser Beam */
.laser-beam {
    position: absolute;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, transparent, #10b981, #34d399, #10b981, transparent);
    box-shadow: 0 0 12px 3px rgba(16, 185, 129, 0.8);
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
    const resNip               = document.getElementById('resNip');
    const resNama              = document.getElementById('resNama');
    const resTipe              = document.getElementById('resTipe');
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
    let currentFacingMode = "environment";
    let isTorchOn         = false;
    let cooldownTimer     = null;
    let cooldownInterval  = null;

    // Web Audio API Beep Synthesizer
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
                osc.frequency.setValueAtTime(880, ctx.currentTime);
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

                let backCam = devices.find(d => d.label.toLowerCase().includes('back') || d.label.toLowerCase().includes('belakang') || d.label.toLowerCase().includes('environment'));
                currentCameraId = backCam ? backCam.id : devices[0].id;
                cameraSelect.value = currentCameraId;

                startCamera(currentCameraId);
            } else {
                cameraSelect.innerHTML = '<option value="">Tidak ada kamera ditemukan</option>';
            }
        }).catch(err => {
            console.warn('Gagal mendapatkan daftar kamera:', err);
            cameraSelect.innerHTML = '<option value="">Izinkan akses kamera di browser</option>';
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
            btnToggleCamera.className = "btn btn-sm btn-success d-inline-flex align-items-center gap-1";

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

    // Flip Kamera
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

        isProcessing = true;
        isPaused = true;

        if (html5QrCode && isCameraRunning) {
            try {
                html5QrCode.pause(true);
            } catch (e) {
                console.log("Pause error:", e);
            }
        }

        processingCode.textContent = decodedText;
        processingOverlay.classList.remove('d-none');

        scanStatusBadge.className = "badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2 rounded-pill";
        scanStatusBadge.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Memproses: ' + decodedText;

        sendGuruAttendanceRequest(decodedText);
    }

    function onScanFailure(error) {
        // Ignored
    }

    // -------------------------------------------------------------
    // AJAX Request ke Backend (Guru Scan)
    // -------------------------------------------------------------
    function sendGuruAttendanceRequest(code) {
        const csrfToken = csrfTokenInput ? csrfTokenInput.value : '';
        const formData = new FormData();
        formData.append('barcode_id', code);
        formData.append('csrf_token', csrfToken);
        formData.append('is_ajax', '1');

        fetch('<?= e(BASEURL); ?>/absensi/scanGuru', {
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
            handleGuruScanResponse(data, code);
        })
        .catch(err => {
            processingOverlay.classList.add('d-none');
            handleGuruScanResponse({
                status: 'error',
                message: 'Kendala server: ' + err.message
            }, code);
        });
    }

    // -------------------------------------------------------------
    // Tampilkan Feedback UI Hasil Scan Guru
    // -------------------------------------------------------------
    function handleGuruScanResponse(res, rawCode) {
        scanResultContainer.classList.remove('d-none');

        clearInterval(cooldownInterval);
        clearTimeout(cooldownTimer);

        const boxKeterangan = document.getElementById('boxKeterangan');
        const boxKetIcon    = document.getElementById('boxKetIcon');
        const boxKetText    = document.getElementById('boxKetText');
        const boxKetRole    = document.getElementById('boxKetRole');
        const labelResNip   = document.getElementById('labelResNip');
        const labelResTipe  = document.getElementById('labelResTipe');

        if (res.status === 'success') {
            playBeep('success');

            scanResultCard.className = "card border-0 shadow-lg rounded-4 overflow-hidden border-start border-success border-4";
            resultIconWrapper.className = "rounded-circle d-flex align-items-center justify-content-center bg-success text-white";
            resultIcon.className = "bi bi-check-lg fs-3";

            resultTitle.innerHTML = '<span class="text-success fw-bold"><i class="bi bi-patch-check-fill me-1"></i> Keterangan: SUDAH ABSEN</span>';
            resultSubtitle.textContent = res.message || "Data presensi berhasil disimpan ke sistem.";

            const tipe = (res.data && res.data.tipe) ? res.data.tipe.toUpperCase() : 'HADIR';
            resultBadge.className = "badge bg-success text-white px-3 py-2 fs-6 rounded-pill shadow-sm";
            resultBadge.textContent = "SUDAH ABSEN";

            if (boxKeterangan) {
                boxKeterangan.className = "alert alert-success d-flex align-items-center justify-content-between py-2 px-3 mb-3 border-0 rounded-3 shadow-sm";
                boxKetIcon.className = "bi bi-patch-check-fill fs-4 me-2 text-success";
                boxKetText.textContent = "SUDAH ABSEN (" + tipe + ")";
            }

            if (res.data) {
                const isSiswa = (res.data.role === 'Siswa');
                if (boxKetRole) boxKetRole.textContent = isSiswa ? 'SISWA' : 'GURU';
                if (labelResNip) labelResNip.textContent = isSiswa ? 'NIS' : 'NIP';
                if (labelResTipe) labelResTipe.textContent = isSiswa ? 'Kelas' : 'Tipe Presensi';

                resNip.textContent   = isSiswa ? (res.data.nis || '-') : (res.data.nip || '-');
                resNama.textContent  = res.data.nama || '-';
                resTipe.textContent  = isSiswa ? (res.data.kelas || '-') : (res.data.tipe || 'Masuk');
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
                const isSiswa = (res.data.role === 'Siswa');
                if (boxKetRole) boxKetRole.textContent = isSiswa ? 'SISWA' : 'GURU';
                if (labelResNip) labelResNip.textContent = isSiswa ? 'NIS' : 'NIP';
                if (labelResTipe) labelResTipe.textContent = isSiswa ? 'Kelas' : 'Tipe Presensi';

                resNip.textContent   = isSiswa ? (res.data.nis || '-') : (res.data.nip || '-');
                resNama.textContent  = res.data.nama || '-';
                resTipe.textContent  = isSiswa ? (res.data.kelas || '-') : (res.data.jam_pulang ? 'Lengkap' : 'Masuk');
                resWaktu.textContent = (res.data.jam_pulang || res.data.jam_masuk || res.data.jam || '-') + ' WIB';
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

            resNip.textContent   = rawCode;
            resNama.textContent  = "Tidak Dikenal";
            resTipe.textContent  = "-";
            resWaktu.textContent = "-";
        }

        scanResultContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        startCooldownTimer(2.8);
    }

    // -------------------------------------------------------------
    // Cooldown Timer & Reset Kamera
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

        if (html5QrCode && isCameraRunning) {
            try {
                html5QrCode.resume();
            } catch (e) {
                console.log('Resume error:', e);
            }
        }

        scanStatusBadge.className = "badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill";
        scanStatusBadge.innerHTML = '<i class="bi bi-broadcast me-1"></i> Kamera Aktif &bull; Memindai...';

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

    // Mulai inisialisasi kamera
    initCameraList();
});
</script>
