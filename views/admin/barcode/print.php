<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($data['judul']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f6f9; }
        .id-card {
            width: 8.5cm;
            height: 5.5cm;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 15px;
            margin: 10px;
            display: inline-block;
            position: relative;
            border: 1px solid #ddd;
            page-break-inside: avoid;
        }
        .id-card-header {
            text-align: center;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .id-card-header h6 {
            margin: 0;
            font-weight: bold;
            color: #0d6efd;
            font-size: 14px;
        }
        .id-card-body {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .id-card-info {
            flex-grow: 1;
            padding-right: 10px;
        }
        .id-card-info h5 {
            font-size: 16px;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .id-card-info p {
            margin: 0;
            font-size: 12px;
            color: #555;
        }
        .qr-container {
            height: 80px;
            background: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 10px;
            width: 100%;
        }
        .qr-container svg, .qr-container img {
            max-width: 100%;
            height: 100%;
        }
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .id-card {
                box-shadow: none;
                border: 1px solid #000;
            }
        }
        .download-btn {
            position: absolute;
            bottom: 5px;
            right: 5px;
            font-size: 10px;
            padding: 2px 5px;
        }
    </style>
</head>
<body>

<div class="container py-4 no-print">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><?= e($data['judul']) ?></h4>
        <div>
            <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer me-2"></i>Cetak</button>
            <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
        </div>
    </div>
</div>

<div class="container-fluid text-center">
    <?php foreach ($data['items'] as $item): ?>
        <?php if (empty($item['barcode_id'])) continue; ?>
        <div class="id-card" id="card-<?= $item['id'] ?>" style="text-align: left;">
            <div class="id-card-header d-flex justify-content-between align-items-center">
                <h6>KARTU IDENTITAS <?= strtoupper($data['type']) ?></h6>
                <span class="badge bg-primary-subtle text-primary border" style="font-size: 10px;"><?= strtoupper($data['type']) ?></span>
            </div>
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="id-card-info" style="flex: 1; padding-right: 8px;">
                    <h5 class="fw-bold mb-1 text-truncate" style="font-size: 14px; max-width: 175px;" title="<?= e($item['nama']) ?>"><?= e($item['nama']) ?></h5>
                    <?php if ($data['type'] === 'guru'): ?>
                        <p><strong>NIP:</strong> <?= e($item['nip']) ?></p>
                        <p><strong>Status:</strong> Tenaga Pendidik</p>
                    <?php else: ?>
                        <p><strong>NIS:</strong> <?= e($item['nis']) ?></p>
                        <p><strong>Kelas:</strong> <?= e($item['nama_kelas']) ?></p>
                    <?php endif; ?>
                    <p class="font-monospace text-primary fw-bold mt-1" style="font-size: 11px;"><?= e($item['barcode_id']) ?></p>
                </div>
                <div class="p-1 bg-white border rounded shadow-sm text-center" style="width: 82px; height: 82px; display:flex; align-items:center; justify-content:center;">
                    <div id="qrcode-<?= $item['id'] ?>" data-text="<?= e($item['barcode_id']) ?>" style="width:74px;height:74px;"></div>
                </div>
            </div>
            <div class="qr-container text-center pt-1 border-top" style="height: auto; margin-top: 4px;">
                <svg id="barcode-<?= $item['id'] ?>" data-text="<?= e($item['barcode_id']) ?>" style="max-width: 100%; height: 32px;"></svg>
            </div>
            <button class="btn btn-outline-secondary download-btn no-print" onclick="downloadPNG(<?= $item['id'] ?>, '<?= e($item['nama']) ?>')">
                <i class="bi bi-download"></i> PNG
            </button>
        </div>
    <?php endforeach; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script src="<?= BASEURL ?>/js/qrcode.min.js"></script>
<script>
    // Render 2D QR Codes
    document.querySelectorAll('div[id^="qrcode-"]').forEach(function(box) {
        var text = box.getAttribute('data-text');
        if (text) {
            new QRCode(box, {
                text: text,
                width: 74,
                height: 74,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.M
            });
        }
    });

    // Render 1D Barcodes
    document.querySelectorAll('svg[id^="barcode-"]').forEach(function(svg) {
        var text = svg.getAttribute('data-text');
        if (text) {
            JsBarcode(svg, text, {
                format: "CODE128",
                width: 1.4,
                height: 30,
                displayValue: false,
                margin: 0
            });
        }
    });

    // Download PNG Logic
    function downloadPNG(id, nama) {
        var card = document.getElementById('card-' + id);
        var qrImg = card.querySelector('div[id^="qrcode-"] img');
        if (qrImg && qrImg.src) {
            var link = document.createElement('a');
            link.download = 'QR_' + nama.replace(/ /g, '_') + '.png';
            link.href = qrImg.src;
            link.click();
        }
    }
</script>
</body>
</html>
