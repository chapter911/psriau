<?php
if (! function_exists('esc')) {
    function esc($data, string $context = 'html'): string {
        return htmlspecialchars((string) $data, ENT_QUOTES, 'UTF-8');
    }
}

$paper = strtoupper($paper ?? 'A4');
if (! in_array($paper, ['A4', 'A3', 'SRA3'], true)) {
    $paper = 'A4';
}
$isLandscape = ($orientation ?? 'portrait') === 'landscape';

/**
 * Ukuran setiap stiker dikunci tepat 10cm x 4cm (100mm x 40mm):
 * - A4 Portrait  (210mm x 297mm): 2 kolom (2 x 100mm = 200mm)
 * - A4 Landscape (297mm x 210mm): 2 kolom (3 kolom = 300mm tidak muat di 297mm)
 * - A3 / SRA3 Portrait (320mm x 450mm): 3 kolom (3 x 100mm = 300mm + jarak 2x3mm + margin 2x7mm = 320mm)
 * - A3 Landscape (420mm x 297mm): 4 kolom (4 x 100mm = 400mm)
 */
$isA3Group = in_array($paper, ['A3', 'SRA3'], true);
if ($isA3Group) {
    $perRow = $isLandscape ? 4 : 3;
} else {
    $perRow = 2;
}

$colGapMm = ($paper === 'A4' && ! $isLandscape) ? 2.5 : 3.0;
$rowGapMm = 3.0;

if ($paper === 'A4' && ! $isLandscape) {
    $pageSizeCss   = 'A4 portrait';
    $pageWidthMm   = 210.0;
    $pageHeightMm  = 297.0;
    $defaultTop    = 6.0;
    $defaultHoriz  = 3.75;
} elseif ($isA3Group && ! $isLandscape) {
    // SRA3 / A3+ Portrait (320 x 450 mm)
    $pageSizeCss   = '320mm 450mm';
    $pageWidthMm   = 320.0;
    $pageHeightMm  = 450.0;
    $defaultTop    = 0.0;
    $defaultHoriz  = 0.0;
} elseif ($isA3Group && $isLandscape) {
    // A3 Landscape (420 x 297 mm)
    $pageSizeCss   = 'A3 landscape';
    $pageWidthMm   = 420.0;
    $pageHeightMm  = 297.0;
    $defaultTop    = 0.0;
    $defaultHoriz  = 0.0;
} else {
    // A4 Landscape (297 x 210 mm)
    $pageSizeCss   = 'A4 landscape';
    $pageWidthMm   = 297.0;
    $pageHeightMm  = 210.0;
    $defaultTop    = 6.0;
    $defaultHoriz  = 8.0;
}

$marginTopMm   = isset($marginTop) && is_numeric($marginTop) ? max(0.0, (float) $marginTop) : $defaultTop;
$marginHorizMm = isset($marginHorizontal) && is_numeric($marginHorizontal) ? max(0.0, (float) $marginHorizontal) : $defaultHoriz;

// Jaga agar jarak antar kolom otomatis menyesuaikan jika margin horizontal diperbesar
$usableWidthMm = $pageWidthMm - (2.0 * $marginHorizMm);
$neededWidthMm = ($perRow * 100.0) + (($perRow - 1) * $colGapMm);
if ($perRow > 1 && $usableWidthMm < $neededWidthMm) {
    $colGapMm = max(0.0, round(($usableWidthMm - ($perRow * 100.0)) / ($perRow - 1), 2));
}

// Hitung kapasitas baris per halaman berdasarkan tinggi kertas dan margin atas/bawah
$cardHeightMm   = 40.0;
$usableHeightMm = max($cardHeightMm, $pageHeightMm - (2.0 * $marginTopMm));
$rowsPerPage    = max(1, (int) floor(($usableHeightMm + $rowGapMm) / ($cardHeightMm + $rowGapMm)));

$pageMargin = $marginTopMm . 'mm ' . $marginHorizMm . 'mm ' . $marginTopMm . 'mm ' . $marginHorizMm . 'mm';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Sticker BMN - <?= date('Y-m-d'); ?></title>
    <style>
        @page {
            size: <?= $pageSizeCss; ?>;
            margin: <?= $pageMargin; ?>;
        }
        body, div, table, tr, td, img, span {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #000000;
            background: #ffffff;
            font-size: 9.3pt;
            line-height: 1.15;
        }

        /* Grid halaman: lebar tiap sel dikunci tepat 100mm (10cm), spacer antar kolom terpisah */
        .sticker-page {
            page-break-after: always;
        }
        .sticker-page-last {
            page-break-after: auto;
        }
        .sticker-page-table {
            border-collapse: collapse;
            margin: 0 auto;
        }
        .sticker-cell {
            width: 100mm;
            vertical-align: top;
            padding: 0;
        }
        .sticker-col-gap {
            width: <?= $colGapMm; ?>mm;
            padding: 0;
        }
        .sticker-row-gap td {
            height: <?= $rowGapMm; ?>mm;
            padding: 0;
            font-size: 1pt;
            line-height: 1pt;
        }

        /* Kartu Stiker Tunggal: total border-box tepat 10cm x 4cm (100mm x 40mm) */
        .sticker-card {
            position: relative;
            width: 99.735mm;
            height: 39.735mm;
            border: 0.75pt solid #111111;
            background: #ffffff;
            page-break-inside: avoid;
            overflow: hidden;
        }

        /* Kotak Bagian Atas: 1.5cm (15mm) */
        .sticker-top {
            position: relative;
            width: 100%;
            height: 14.735mm;
            border-bottom: 0.75pt solid #111111;
        }
        .pu-logo {
            position: absolute;
            left: 2.3mm;
            top: 2.45mm;
            width: 9.5mm;
            height: 9.5mm;
            display: block;
        }
        .header-text-wrap {
            position: absolute;
            left: 0;
            right: 0;
            top: 2.25mm;
            text-align: center;
        }
        .instansi-title {
            font-size: 11.8pt;
            font-weight: normal;
            color: #000000;
            line-height: 1.1;
            white-space: nowrap;
        }
        .instansi-code {
            font-size: 9.3pt;
            font-weight: normal;
            color: #000000;
            line-height: 1.15;
            margin-top: 0.25mm;
            white-space: nowrap;
        }

        /* Kotak Bagian Bawah: 2.5cm (25mm) */
        .sticker-bottom {
            position: relative;
            width: 100%;
            height: 24.735mm;
        }
        .info-top-area {
            padding: 1.7mm 24.5mm 0 2.1mm;
        }
        .info-row-1 {
            position: relative;
            height: 3.7mm;
        }
        .kode-barang-text {
            position: absolute;
            left: 0;
            top: 0;
            font-size: 9.3pt;
            font-weight: normal;
            color: #000000;
            line-height: 1.15;
            white-space: nowrap;
        }
        .nup-text {
            position: absolute;
            left: 32.5mm;
            top: 0;
            font-size: 9.3pt;
            font-weight: normal;
            color: #000000;
            line-height: 1.15;
            white-space: nowrap;
        }
        .nama-barang-text {
            font-size: 9.3pt;
            font-weight: normal;
            color: #000000;
            line-height: 1.15;
            margin-top: 0.15mm;
            word-wrap: break-word;
            word-break: break-word;
        }
        .merk-tipe-text {
            position: absolute;
            left: 2.1mm;
            right: 24.5mm;
            bottom: 2.5mm;
            font-size: 9.3pt;
            font-weight: normal;
            color: #000000;
            line-height: 1.15;
            word-wrap: break-word;
            word-break: break-word;
        }

        /* QR Code tepat 2cm x 2cm (20mm x 20mm) di kanan */
        .qr-image {
            position: absolute;
            right: 2.3mm;
            top: 2.4mm;
            width: 20mm;
            height: 20mm;
            display: block;
        }
        .qr-placeholder {
            position: absolute;
            right: 2.3mm;
            top: 2.4mm;
            width: 20mm;
            height: 20mm;
            border: 1px dashed #cccccc;
            display: block;
        }
    </style>
</head>
<body>

    <?php
    $chunks = array_chunk($stickers ?? [], $perRow);
    $pages = array_chunk($chunks, $rowsPerPage);
    $totalPages = count($pages);
    foreach ($pages as $pageIdx => $pageRows):
        $pageRowCount = count($pageRows);
    ?>
        <div class="sticker-page<?= ($pageIdx === $totalPages - 1) ? ' sticker-page-last' : ''; ?>">
            <table class="sticker-page-table" cellpadding="0" cellspacing="0">
                <?php foreach ($pageRows as $rowIdx => $row): ?>
                    <tr>
                        <?php for ($colIdx = 0; $colIdx < $perRow; $colIdx++): ?>
                            <?php if ($colIdx > 0): ?>
                                <td class="sticker-col-gap"></td>
                            <?php endif; ?>

                            <?php if (isset($row[$colIdx])):
                                $stk = $row[$colIdx];
                                $thn = ! empty($stk['tahun_perolehan']) ? $stk['tahun_perolehan'] : date('Y');
                                $headerCode = ($kodeUakpb ?? '145060900691285000KP') . '.' . $thn;
                            ?>
                                <td class="sticker-cell">
                                    <div class="sticker-card">
                                        <!-- Kotak Atas: 1.5 cm -->
                                        <div class="sticker-top">
                                            <?php if (! empty($logoBase64)): ?>
                                                <img src="<?= $logoBase64; ?>" class="pu-logo" alt="Logo PU">
                                            <?php endif; ?>
                                            <div class="header-text-wrap">
                                                <div class="instansi-title">Kementerian Pekerjaan Umum</div>
                                                <div class="instansi-code"><?= esc($headerCode); ?></div>
                                            </div>
                                        </div>

                                        <!-- Kotak Bawah: 2.5 cm -->
                                        <div class="sticker-bottom">
                                            <div class="info-top-area">
                                                <div class="info-row-1">
                                                    <span class="kode-barang-text"><?= esc($stk['kode_barang']); ?></span>
                                                    <span class="nup-text">NUP: <?= esc($stk['nup']); ?></span>
                                                </div>
                                                <div class="nama-barang-text"><?= esc($stk['nama_barang']); ?></div>
                                            </div>

                                            <div class="merk-tipe-text"><?= esc($stk['merk_tipe']); ?></div>

                                            <?php if (! empty($stk['qr_base64'])): ?>
                                                <img src="<?= $stk['qr_base64']; ?>" class="qr-image" alt="QR Code">
                                            <?php else: ?>
                                                <div class="qr-placeholder"></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                            <?php else: ?>
                                <td class="sticker-cell"></td>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </tr>
                    <?php if ($rowIdx < $pageRowCount - 1): ?>
                        <tr class="sticker-row-gap">
                            <td colspan="<?= ($perRow * 2) - 1; ?>"></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </table>
        </div>
    <?php endforeach; ?>

</body>
</html>
