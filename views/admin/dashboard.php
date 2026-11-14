<?php
// Prepare chart data (JSON-safe)
$trendLabels = [];
$trendHadir  = [];
$trendAlpa   = [];
foreach ($data['tren'] as $t) {
    $trendLabels[] = $t['label'];
    $trendHadir[]  = (int)$t['hadir'];
    $trendAlpa[]   = (int)$t['alpa'];
}
$ringkasan     = $data['ringkasan'];
$absSiswa      = $data['absensi_siswa'];
$absGuru       = $data['absensi_guru'];
$tidakHadir    = $data['tidak_hadir'];
$tanggalFmt    = date('d F Y', strtotime($data['tanggal']));
?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold mb-0">Dashboard Admin</h2>
        <p class="text-muted mb-0"><i class="bi bi-calendar3 me-1"></i><?= $tanggalFmt ?></p>
    </div>
</div>

<?php Flasher::flash(); ?>

<!-- Row 1: Ringkasan Utama -->
<div class="row g-4 mb-4">
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card card border-0 p-3">
            <div class="stat-icon-wrapper stat-icon-primary mb-3">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="fw-bold fs-3"><?= (int)$ringkasan['total_siswa'] ?></div>
            <div class="text-muted small">Total Siswa</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card card border-0 p-3">
            <div class="stat-icon-wrapper stat-icon-indigo mb-3">
                <i class="bi bi-person-badge-fill"></i>
            </div>
            <div class="fw-bold fs-3"><?= (int)$ringkasan['total_guru'] ?></div>
            <div class="text-muted small">Total Guru</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card card border-0 p-3">
            <div class="stat-icon-wrapper stat-icon-purple mb-3">
                <i class="bi bi-building-fill"></i>
            </div>
            <div class="fw-bold fs-3"><?= (int)$ringkasan['total_kelas'] ?></div>
            <div class="text-muted small">Total Kelas</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card card border-0 p-3">
            <div class="stat-icon-wrapper stat-icon-success mb-3">
                <i class="bi bi-person-check-fill"></i>
            </div>
            <div class="fw-bold fs-3"><?= (int)($absSiswa['hadir'] + $absSiswa['terlambat']) ?></div>
            <div class="text-muted small">Siswa Hadir</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card card border-0 p-3">
            <div class="stat-icon-wrapper stat-icon-warning mb-3">
                <i class="bi bi-person-x-fill"></i>
            </div>
            <div class="fw-bold fs-3"><?= (int)($absSiswa['alpa'] + $absSiswa['sakit'] + $absSiswa['izin']) ?></div>
            <div class="text-muted small">Siswa Absen</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card card border-0 p-3">
            <div class="stat-icon-wrapper stat-icon-success mb-3">
                <i class="bi bi-person-check-fill"></i>
            </div>
            <div class="fw-bold fs-3"><?= (int)$absGuru['hadir'] ?></div>
            <div class="text-muted small">Guru Hadir</div>
        </div>
    </div>
</div>

<!-- Row 2: Grafik + Absensi Siswa Hari Ini -->
<div class="row g-4 mb-4">
    <!-- Grafik Tren -->
    <div class="col-lg-7">
        <div class="table-card h-100">
            <h6 class="fw-bold mb-4"><i class="bi bi-graph-up me-2 text-primary"></i>Tren Kehadiran Siswa (7 Hari Terakhir)</h6>
            <canvas id="trendChart" height="200"></canvas>
        </div>
    </div>

    <!-- Absensi Siswa Hari Ini (Donut) -->
    <div class="col-lg-5">
        <div class="table-card h-100">
            <h6 class="fw-bold mb-4"><i class="bi bi-pie-chart-fill me-2 text-primary"></i>Absensi Siswa Hari Ini</h6>
            <?php if ($absSiswa['total'] > 0): ?>
                <canvas id="donutChart" height="180"></canvas>
                <div class="row text-center mt-3 g-2">
                    <div class="col-6"><span class="badge badge-hadir w-100 py-2">Hadir: <?= (int)($absSiswa['hadir'] + $absSiswa['terlambat']) ?></span></div>
                    <div class="col-6"><span class="badge badge-alpa w-100 py-2">Alpa: <?= (int)$absSiswa['alpa'] ?></span></div>
                    <div class="col-6"><span class="badge badge-sakit w-100 py-2">Sakit: <?= (int)$absSiswa['sakit'] ?></span></div>
                    <div class="col-6"><span class="badge badge-izin w-100 py-2">Izin: <?= (int)$absSiswa['izin'] ?></span></div>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-clipboard-x"></i>
                    <p>Belum ada data absensi hari ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Row 3: Absensi Guru + Siswa Tidak Hadir -->
<div class="row g-4">
    <!-- Absensi Guru -->
    <div class="col-lg-4">
        <div class="table-card h-100">
            <h6 class="fw-bold mb-4"><i class="bi bi-person-badge me-2 text-primary"></i>Status Guru Hari Ini</h6>
            <div class="d-flex align-items-center mb-3">
                <div class="stat-icon-wrapper stat-icon-success me-3">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div>
                    <div class="fw-semibold"><?= (int)$absGuru['hadir'] ?> Guru</div>
                    <div class="text-muted small">Sudah absen</div>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <div class="stat-icon-wrapper stat-icon-danger me-3">
                    <i class="bi bi-x-circle-fill"></i>
                </div>
                <div>
                    <div class="fw-semibold"><?= (int)$absGuru['tidak_hadir'] ?> Guru</div>
                    <div class="text-muted small">Belum absen</div>
                </div>
            </div>
            <?php if ($absGuru['total'] > 0): ?>
                <div class="progress mt-4" style="height:8px;">
                    <div class="progress-bar bg-success" style="width:<?= round(($absGuru['hadir'] / $absGuru['total']) * 100) ?>%"></div>
                </div>
                <small class="text-muted"><?= round(($absGuru['hadir'] / $absGuru['total']) * 100) ?>% dari <?= (int)$absGuru['total'] ?> guru</small>
            <?php endif; ?>
        </div>
    </div>

    <!-- Siswa Tidak Hadir -->
    <div class="col-lg-8">
        <div class="table-card h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-person-x me-2 text-primary"></i>Siswa Tidak/Belum Hadir (10 Teratas)</h6>
            <?php if (!empty($tidakHadir)): ?>
                <div class="table-responsive">
                    <table class="table custom-table mb-0">
                        <thead><tr><th>Nama</th><th>Kelas</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach ($tidakHadir as $s): ?>
                            <tr>
                                <td class="fw-semibold"><?= e($s['nama']) ?></td>
                                <td><?= e($s['nama_kelas'] ?? '-') ?></td>
                                <td>
                                    <?php 
                                    $st = (string)$s['status']; 
                                    $isAlpa = ($st === '3' || $st === 'alpa' || $st === 'alpha');
                                    ?>
                                    <span class="badge <?= $isAlpa ? 'badge-alpa' : 'bg-secondary text-white' ?>">
                                        <?= $isAlpa ? 'Alpha' : 'Belum Absen' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state py-3">
                    <i class="bi bi-check-circle" style="font-size:2.5rem;"></i>
                    <p class="mt-2">Semua siswa sudah hadir hari ini! 🎉</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$aiSetting = $data['ai_setting'] ?? [
    'provider' => 'nvidia',
    'api_key' => '',
    'api_url' => 'https://integrate.api.nvidia.com/v1/chat/completions',
    'model' => 'meta/llama-3.2-11b-vision-instruct',
    'temperature' => 0.7,
    'max_tokens' => 1024,
];
$aiPresets = $data['ai_presets'] ?? [];
?>

<!-- Row 4: Konfigurasi AI Chatbot -->
<div class="row g-4 mb-4" id="ai-setting-card">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-4" style="background: linear-gradient(135deg, #ffffff 0%, #f8faff 100%); border: 1px solid rgba(59, 130, 246, 0.2) !important;">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 p-3 d-flex align-items-center justify-content-center text-white" style="background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); width: 56px; height: 56px; box-shadow: 0 8px 16px rgba(59,130,246,0.25);">
                        <i class="bi bi-robot fs-2"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="fw-bold mb-0">Konfigurasi AI Chatbot Helpdesk</h5>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill small">
                                <i class="bi bi-check-circle-fill me-1"></i> Aktif & Terhubung MySQL
                            </span>
                        </div>
                        <p class="text-muted small mb-0 mt-1">
                            Kelola API Key, ganti provider (NVIDIA NIM, Groq, OpenAI), dan pilih model AI yang aktif untuk chatbot sekolah.
                        </p>
                    </div>
                </div>
                <div>
                    <button type="button" class="btn btn-primary px-4 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalAiSetting">
                        <i class="bi bi-sliders2-vertical"></i>
                        <span>Ubah API Key & Model AI</span>
                    </button>
                </div>
            </div>

            <hr class="my-3 opacity-10">

            <div class="row g-3">
                <div class="col-sm-6 col-lg-3">
                    <div class="p-3 bg-white rounded-3 border">
                        <span class="text-muted small d-block mb-1"><i class="bi bi-cpu me-1 text-primary"></i> Provider AI</span>
                        <span class="fw-bold fs-6 text-dark text-capitalize" id="activeProviderDisplay"><?= e($aiSetting['provider']) ?></span>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="p-3 bg-white rounded-3 border">
                        <span class="text-muted small d-block mb-1"><i class="bi bi-box-seam me-1 text-success"></i> Model Terpilih</span>
                        <span class="fw-bold fs-6 text-dark font-monospace text-truncate d-block" id="activeModelDisplay" title="<?= e($aiSetting['model']) ?>"><?= e($aiSetting['model']) ?></span>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="p-3 bg-white rounded-3 border">
                        <span class="text-muted small d-block mb-1"><i class="bi bi-key-fill me-1 text-warning"></i> API Key Aktif</span>
                        <?php
                            $maskedKey = 'Belum dikonfigurasi';
                            if (!empty($aiSetting['api_key'])) {
                                $len = strlen($aiSetting['api_key']);
                                if ($len > 14) {
                                    $maskedKey = substr($aiSetting['api_key'], 0, 8) . '••••••••' . substr($aiSetting['api_key'], -4);
                                } else {
                                    $maskedKey = '••••••••••••';
                                }
                            }
                        ?>
                        <span class="fw-bold fs-6 text-muted font-monospace"><?= e($maskedKey) ?></span>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="p-3 bg-white rounded-3 border">
                        <span class="text-muted small d-block mb-1"><i class="bi bi-database-check me-1 text-info"></i> Sumber Data</span>
                        <span class="fw-bold fs-6 text-dark">Real-Time MySQL DB</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfigurasi AI -->
<div class="modal fade" id="modalAiSetting" tabindex="-1" aria-labelledby="modalAiSettingLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white p-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-robot fs-3"></i>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalAiSettingLabel">Konfigurasi AI Key & Model Chatbot</h5>
                        <small class="text-white-50">Sesuaikan API Key dan pilih model AI yang didukung</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="<?= e(BASEURL); ?>/admin/saveAiSetting" method="POST" id="formAiSetting">
                <input type="hidden" name="csrf_token" value="<?= Csrf::getToken(); ?>">

                <div class="modal-body p-4">
                    <!-- Alert Status Ajax Fetch -->
                    <div id="aiFetchAlert" class="alert d-none mb-4" role="alert"></div>

                    <!-- 1. Pilihan Provider -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Pilih Provider AI</label>
                        <div class="row g-2" id="providerSelector">
                            <div class="col-6 col-md-3">
                                <input type="radio" class="btn-check provider-radio" name="provider" id="providerNvidia" value="nvidia" <?= ($aiSetting['provider'] === 'nvidia') ? 'checked' : '' ?>>
                                <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center justify-content-center h-100 rounded-3" for="providerNvidia">
                                    <i class="bi bi-gpu-card fs-4 mb-1"></i>
                                    <span class="fw-semibold small">NVIDIA NIM</span>
                                </label>
                            </div>
                            <div class="col-6 col-md-3">
                                <input type="radio" class="btn-check provider-radio" name="provider" id="providerGroq" value="groq" <?= ($aiSetting['provider'] === 'groq') ? 'checked' : '' ?>>
                                <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center justify-content-center h-100 rounded-3" for="providerGroq">
                                    <i class="bi bi-lightning-charge-fill fs-4 mb-1"></i>
                                    <span class="fw-semibold small">Groq Cloud</span>
                                </label>
                            </div>
                            <div class="col-6 col-md-3">
                                <input type="radio" class="btn-check provider-radio" name="provider" id="providerOpenai" value="openai" <?= ($aiSetting['provider'] === 'openai') ? 'checked' : '' ?>>
                                <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center justify-content-center h-100 rounded-3" for="providerOpenai">
                                    <i class="bi bi-chat-square-dots-fill fs-4 mb-1"></i>
                                    <span class="fw-semibold small">OpenAI</span>
                                </label>
                            </div>
                            <div class="col-6 col-md-3">
                                <input type="radio" class="btn-check provider-radio" name="provider" id="providerCustom" value="custom" <?= ($aiSetting['provider'] === 'custom' || $aiSetting['provider'] === 'openrouter') ? 'checked' : '' ?>>
                                <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center justify-content-center h-100 rounded-3" for="providerCustom">
                                    <i class="bi bi-gear-wide-connected fs-4 mb-1"></i>
                                    <span class="fw-semibold small">OpenRouter / Custom</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- 2. API Endpoint URL -->
                    <div class="mb-3">
                        <label for="aiApiUrl" class="form-label fw-bold">API Endpoint URL (Chat Completions)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-link-45deg"></i></span>
                            <input type="text" class="form-control font-monospace" id="aiApiUrl" name="api_url" value="<?= e($aiSetting['api_url']) ?>" required>
                        </div>
                        <div class="form-text">Endpoint API standar OpenAI-compatible untuk chat completions.</div>
                    </div>

                    <!-- 3. API Key -->
                    <div class="mb-3">
                        <label for="aiApiKey" class="form-label fw-bold">API Key</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-key-fill"></i></span>
                            <input type="password" class="form-control font-monospace" id="aiApiKey" name="api_key" value="<?= e($aiSetting['api_key']) ?>" placeholder="Masukkan API Key (nvapi-..., gsk_..., sk-...)" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleApiKeyVisibility" title="Lihat / Sembunyikan Key">
                                <i class="bi bi-eye" id="toggleApiKeyIcon"></i>
                            </button>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2">
                            <div class="form-text mb-0">API Key disimpan terenkripsi di database MySQL sekolah.</div>
                            <button type="button" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1 shadow-sm px-3 py-1" id="btnFetchModels">
                                <span class="spinner-border spinner-border-sm d-none" id="fetchSpinner" role="status" aria-hidden="true"></span>
                                <i class="bi bi-arrow-repeat" id="fetchIcon"></i>
                                <span id="fetchBtnText">⚡ Tarik Model dari API Key Ini</span>
                            </button>
                        </div>
                    </div>

                    <!-- 4. Pilihan Model AI -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="aiModelSelect" class="form-label fw-bold mb-0">Pilih Model AI (Modeling)</label>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle small" id="modelCountBadge">
                                Model Rekomendasi
                            </span>
                        </div>
                        <select class="form-select font-monospace" id="aiModelSelect" name="model" required>
                            <!-- Populated dynamically via JS -->
                        </select>
                        <div class="form-text">Pilih salah satu model yang didukung oleh API key Anda, atau gunakan opsi input kustom.</div>
                    </div>

                    <!-- Input Manual Model (jika user pilih manual) -->
                    <div class="mb-3 d-none" id="customModelWrapper">
                        <label for="aiCustomModelInput" class="form-label fw-bold text-primary">Ketik Nama Model Kustom</label>
                        <input type="text" class="form-control font-monospace" id="aiCustomModelInput" placeholder="Contoh: meta/llama-3.3-70b-instruct">
                    </div>

                    <!-- Pengaturan Lanjutan (Collapse) -->
                    <div class="mt-4 pt-3 border-top">
                        <a class="text-decoration-none d-flex align-items-center justify-content-between text-muted small fw-semibold" data-bs-toggle="collapse" href="#advancedSettings" role="button" aria-expanded="false">
                            <span><i class="bi bi-sliders me-1"></i> Pengaturan Lanjutan (Temperature & Max Tokens)</span>
                            <i class="bi bi-chevron-down"></i>
                        </a>
                        <div class="collapse mt-3" id="advancedSettings">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="aiTemperature" class="form-label small fw-bold">Temperature (Kreativitas Jawaban)</label>
                                    <input type="number" step="0.1" min="0" max="1" class="form-control form-control-sm" id="aiTemperature" name="temperature" value="<?= e($aiSetting['temperature'] ?? 0.7) ?>">
                                    <div class="form-text small">Rekomendasi: 0.5 - 0.7 untuk balasan yang akurat dan luwes.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="aiMaxTokens" class="form-label small fw-bold">Max Tokens (Panjang Maksimal Balasan)</label>
                                    <input type="number" step="128" min="256" max="4096" class="form-control form-control-sm" id="aiMaxTokens" name="max_tokens" value="<?= e($aiSetting['max_tokens'] ?? 1024) ?>">
                                    <div class="form-text small">Maksimum jumlah token yang dihasilkan per jawaban (default: 1024).</div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer p-3 bg-light">
                    <button type="button" class="btn btn-outline-secondary px-4 rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 rounded-3 d-flex align-items-center gap-2">
                        <i class="bi bi-check-lg"></i>
                        <span>Simpan Konfigurasi AI</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Chart.js & AI Configuration Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Trend Chart
    const trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($trendLabels) ?>,
                datasets: [
                    {
                        label: 'Hadir',
                        data: <?= json_encode($trendHadir) ?>,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59,130,246,0.1)',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#3b82f6',
                        pointRadius: 4,
                    },
                    {
                        label: 'Alpa',
                        data: <?= json_encode($trendAlpa) ?>,
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239,68,68,0.1)',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#ef4444',
                        pointRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'top' } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // Donut Chart
    const donutCtx = document.getElementById('donutChart');
    if (donutCtx) {
        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: ['Hadir', 'Alpa', 'Sakit', 'Izin'],
                datasets: [{
                    data: [
                        <?= (int)($absSiswa['hadir'] + $absSiswa['terlambat']) ?>,
                        <?= (int)$absSiswa['alpa'] ?>,
                        <?= (int)$absSiswa['sakit'] ?>,
                        <?= (int)$absSiswa['izin'] ?>
                    ],
                    backgroundColor: ['#10b981','#ef4444','#f59e0b','#6366f1'],
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                cutout: '65%',
                plugins: { legend: { display: false } }
            }
        });
    }

    // -------------------------------------------------------------------------
    // AI Configuration Script
    // -------------------------------------------------------------------------
    const aiPresets = <?= json_encode($aiPresets) ?>;
    const currentModel = <?= json_encode($aiSetting['model'] ?? '') ?>;
    const providerUrls = {
        'nvidia': 'https://integrate.api.nvidia.com/v1/chat/completions',
        'groq': 'https://api.groq.com/openai/v1/chat/completions',
        'openai': 'https://api.openai.com/v1/chat/completions',
        'custom': 'https://openrouter.ai/api/v1/chat/completions'
    };

    const providerRadios = document.querySelectorAll('.provider-radio');
    const apiUrlInput = document.getElementById('aiApiUrl');
    const apiKeyInput = document.getElementById('aiApiKey');
    const modelSelect = document.getElementById('aiModelSelect');
    const customModelWrapper = document.getElementById('customModelWrapper');
    const customModelInput = document.getElementById('aiCustomModelInput');
    const modelCountBadge = document.getElementById('modelCountBadge');
    const btnFetch = document.getElementById('btnFetchModels');
    const fetchSpinner = document.getElementById('fetchSpinner');
    const fetchIcon = document.getElementById('fetchIcon');
    const fetchBtnText = document.getElementById('fetchBtnText');
    const fetchAlert = document.getElementById('aiFetchAlert');
    const toggleKeyBtn = document.getElementById('toggleApiKeyVisibility');
    const toggleKeyIcon = document.getElementById('toggleApiKeyIcon');

    // Toggle Eye Visibility for API Key
    if (toggleKeyBtn && apiKeyInput) {
        toggleKeyBtn.addEventListener('click', function() {
            if (apiKeyInput.type === 'password') {
                apiKeyInput.type = 'text';
                toggleKeyIcon.classList.remove('bi-eye');
                toggleKeyIcon.classList.add('bi-eye-slash');
            } else {
                apiKeyInput.type = 'password';
                toggleKeyIcon.classList.remove('bi-eye-slash');
                toggleKeyIcon.classList.add('bi-eye');
            }
        });
    }

    // Get currently selected provider
    function getSelectedProvider() {
        const checked = document.querySelector('.provider-radio:checked');
        return checked ? checked.value : 'nvidia';
    }

    // Populate model select with array of strings
    function populateModelOptions(modelList, selectedVal) {
        modelSelect.innerHTML = '';
        let found = false;

        modelList.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m;
            opt.textContent = m;
            if (m === selectedVal) {
                opt.selected = true;
                found = true;
            }
            modelSelect.appendChild(opt);
        });

        // Add custom option
        const customOpt = document.createElement('option');
        customOpt.value = '__custom__';
        customOpt.textContent = '✏️ Input Manual Model Kustom Lainnya...';
        if (!found && selectedVal) {
            customOpt.selected = true;
            customModelWrapper.classList.remove('d-none');
            customModelInput.value = selectedVal;
        }
        modelSelect.appendChild(customOpt);
    }

    // Load preset models for provider
    function loadPresetForProvider(provider) {
        const list = aiPresets[provider] || [];
        modelCountBadge.textContent = 'Rekomendasi (' + list.length + ' model)';
        modelCountBadge.className = 'badge bg-primary-subtle text-primary border border-primary-subtle small';
        populateModelOptions(list, currentModel);
    }

    // Provider radio change event
    providerRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            const provider = this.value;
            if (providerUrls[provider]) {
                apiUrlInput.value = providerUrls[provider];
            }
            loadPresetForProvider(provider);
        });
    });

    // Model select change event
    modelSelect.addEventListener('change', function() {
        if (this.value === '__custom__') {
            customModelWrapper.classList.remove('d-none');
            customModelInput.focus();
        } else {
            customModelWrapper.classList.add('d-none');
        }
    });

    // Custom model input sync
    customModelInput.addEventListener('input', function() {
        if (modelSelect.value === '__custom__') {
            // When form submits, we will handle this in submit handler
        }
    });

    // Form submit handler: if __custom__, set custom value
    const formAi = document.getElementById('formAiSetting');
    if (formAi) {
        formAi.addEventListener('submit', function(e) {
            if (modelSelect.value === '__custom__') {
                const customVal = customModelInput.value.trim();
                if (!customVal) {
                    e.preventDefault();
                    alert('Silakan ketik nama model kustom Anda.');
                    customModelInput.focus();
                    return;
                }
                // Temporarily create option with the custom value
                const opt = document.createElement('option');
                opt.value = customVal;
                opt.selected = true;
                modelSelect.appendChild(opt);
            }
        });
    }

    // Fetch models via AJAX from API Key
    if (btnFetch) {
        btnFetch.addEventListener('click', function() {
            const apiKey = apiKeyInput.value.trim();
            const apiUrl = apiUrlInput.value.trim();
            const provider = getSelectedProvider();

            if (!apiKey) {
                showAlert('danger', 'Silakan masukkan API Key terlebih dahulu untuk menarik daftar model.');
                apiKeyInput.focus();
                return;
            }

            // Show loading
            btnFetch.disabled = true;
            fetchSpinner.classList.remove('d-none');
            fetchIcon.classList.add('d-none');
            fetchBtnText.textContent = 'Menghubungi API...';
            hideAlert();

            const formData = new FormData();
            formData.append('api_key', apiKey);
            formData.append('api_url', apiUrl);
            formData.append('provider', provider);
            formData.append('csrf_token', '<?= Csrf::getToken() ?>');

            fetch('<?= BASEURL ?>/admin/fetchAiModels', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btnFetch.disabled = false;
                fetchSpinner.classList.add('d-none');
                fetchIcon.classList.remove('d-none');
                fetchBtnText.textContent = '⚡ Tarik Model dari API Key Ini';

                if (data.success && data.models && data.models.length > 0) {
                    showAlert('success', '✅ <strong>Berhasil Terhubung!</strong> Ditemukan <strong>' + data.count + ' model</strong> yang tersedia pada API Key ini.');
                    modelCountBadge.textContent = data.count + ' Model Tersedia dari API';
                    modelCountBadge.className = 'badge bg-success text-white small';
                    
                    const activeNow = modelSelect.value !== '__custom__' ? modelSelect.value : currentModel;
                    populateModelOptions(data.models, activeNow);
                } else {
                    showAlert('danger', '❌ <strong>Gagal:</strong> ' + (data.error || 'Tidak dapat menarik daftar model dari API Key ini.'));
                }
            })
            .catch(err => {
                btnFetch.disabled = false;
                fetchSpinner.classList.add('d-none');
                fetchIcon.classList.remove('d-none');
                fetchBtnText.textContent = '⚡ Tarik Model dari API Key Ini';
                showAlert('danger', '❌ Terjadi kendala jaringan saat menghubungi server: ' + err.message);
            });
        });
    }

    function showAlert(type, msg) {
        fetchAlert.className = 'alert alert-' + type + ' alert-custom py-2 px-3 small mb-3';
        fetchAlert.innerHTML = msg;
        fetchAlert.classList.remove('d-none');
    }

    function hideAlert() {
        fetchAlert.classList.add('d-none');
    }

    // Initial load
    const initProvider = getSelectedProvider();
    loadPresetForProvider(initProvider);

    // Scroll if hash #ai-setting-card
    if (window.location.hash === '#ai-setting-card') {
        const card = document.getElementById('ai-setting-card');
        if (card) {
            setTimeout(() => {
                card.scrollIntoView({ behavior: 'smooth' });
                card.classList.add('border-primary');
            }, 300);
        }
    }
});
</script>
