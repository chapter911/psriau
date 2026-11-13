<?php
if (! function_exists('esc')) {
    function esc($data, string $context = 'html'): string {
        return htmlspecialchars((string) $data, ENT_QUOTES, 'UTF-8');
    }
}

$paper = strtoupper($paper ?? 'A4');
if (! in_array($paper, ['8X8', 'A4', 'A3', 'SRA3'], true)) {
    $paper = 'A4';
}
$isLandscape = ($orientation ?? 'portrait') === 'landscape';

$format = strtolower($format ?? '10x4');
if (! in_array($format, ['10x4', '8x8'], true)) {
    $format = '10x4';
}
$is8x8 = ($format === '8x8');
$is8x8Paper = ($paper === '8X8');

$isA3Group = in_array($paper, ['A3', 'SRA3'], true);

/**
 * Pilihan Format Stiker:
 * 1. 10cm x 4cm (100mm x 40mm) - Standar Resmi Kementerian Pekerjaan Umum
 *    - A4 Portrait  (210 x 297 mm): 2 kolom (12 stiker/hal)
 *    - A4 Landscape (297 x 210 mm): 2 kolom (8 stiker/hal)
 *    - SRA3 Portrait (320 x 450 mm): 3 kolom (30 stiker/hal)
 *    - A3 Landscape  (420 x 297 mm): 4 kolom (24 stiker/hal)
 *
 * 2. 8cm x 8cm (80mm x 80mm) - Format Kotak / Persegi Presisi
 *    - Kertas 8x8cm (80 x 80 mm): 1 lembar = 1 stiker pas (Printer Stiker / Satuan)
 *    - A4 Portrait  (210 x 297 mm): 2 kolom x 3 baris (6 stiker/hal)
 *    - A4 Landscape (297 x 210 mm): 3 kolom x 2 baris (6 stiker/hal)
 *    - SRA3 Portrait (320 x 450 mm): 3 kolom x 5 baris (15 stiker/hal)
 *    - A3 Landscape  (420 x 297 mm): 5 kolom x 3 baris (15 stiker/hal)
 */
if ($is8x8Paper) {
    $cardWidthMm  = 80.0;
    $cardHeightMm = 80.0;
    $rowGapMm     = 0.0;
    $perRow       = 1;
    $colGapMm     = 0.0;
    $pageSizeCss  = '80mm 80mm';
    $pageWidthMm  = 80.0;
    $pageHeightMm = 80.0;
    $defaultTop   = 0.0;
    $defaultHoriz = 0.0;
} elseif ($is8x8) {
    $cardWidthMm  = 80.0;
    $cardHeightMm = 80.0;
    $rowGapMm     = 4.0;
    if ($isA3Group) {
        $perRow   = $isLandscape ? 5 : 3;
        $colGapMm = $isLandscape ? 3.0 : 5.0;
    } else {
        $perRow   = $isLandscape ? 3 : 2;
        $colGapMm = 4.0;
    }
} else {
    $cardWidthMm  = 100.0;
    $cardHeightMm = 40.0;
    $rowGapMm     = 3.0;
    if ($isA3Group) {
        $perRow   = $isLandscape ? 4 : 3;
    } else {
        $perRow   = 2;
    }
    $colGapMm = ($paper === 'A4' && ! $isLandscape) ? 2.5 : 3.0;
}

if (! $is8x8Paper) {
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
}

$marginTopMm   = isset($marginTop) && is_numeric($marginTop) ? max(0.0, (float) $marginTop) : $defaultTop;
$marginHorizMm = isset($marginHorizontal) && is_numeric($marginHorizontal) ? max(0.0, (float) $marginHorizontal) : $defaultHoriz;

if ($is8x8Paper) {
    $rowsPerPage = 1;
    $pageMargin  = '0mm';
} else {
    // Jaga agar jarak antar kolom otomatis menyesuaikan jika margin horizontal diperbesar
    $usableWidthMm = $pageWidthMm - (2.0 * $marginHorizMm);
    $neededWidthMm = ($perRow * $cardWidthMm) + (($perRow - 1) * $colGapMm);
    if ($perRow > 1 && $usableWidthMm < $neededWidthMm) {
        if ($is8x8 && $perRow === 5 && $usableWidthMm < 400.0) {
            $perRow = 4;
            $neededWidthMm = ($perRow * $cardWidthMm) + (($perRow - 1) * $colGapMm);
        }
        if ($usableWidthMm >= ($perRow * $cardWidthMm)) {
            $colGapMm = max(0.0, round(($usableWidthMm - ($perRow * $cardWidthMm)) / ($perRow - 1), 2));
        }
    }

    $usableHeightMm = max($cardHeightMm, $pageHeightMm - (2.0 * $marginTopMm));
    $rowsPerPage    = max(1, (int) floor(($usableHeightMm + $rowGapMm) / ($cardHeightMm + $rowGapMm)));
    $pageMargin     = $marginTopMm . 'mm ' . $marginHorizMm . 'mm ' . $marginTopMm . 'mm ' . $marginHorizMm . 'mm';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Sticker BMN (<?= $format; ?>) - <?= date('Y-m-d'); ?></title>
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

        /* Halaman Kertas 8x8 cm (Pas 1 Lembar 80x80mm) */
        .page-8x8 {
            page-break-after: always;
            page-break-inside: avoid;
            width: 80mm;
            padding: 0;
            margin: 0;
        }
        .page-8x8-last {
            page-break-after: auto;
            page-break-inside: avoid;
            width: 80mm;
            padding: 0;
            margin: 0;
        }

        /* Grid Halaman (A4 & A3) */
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
            width: <?= $cardWidthMm; ?>mm;
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

        /* ==========================================================================
           FORMAT 1: 10cm x 4cm (Total border-box tepat 100mm x 40mm)
           ========================================================================== */
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

        /* ==========================================================================
           FORMAT 2: 8cm x 8cm (Presisi Anti-Split Table Card)
           ========================================================================== */
        table.card-table-8x8 {
            width: 218pt;
            border-collapse: collapse;
            border: 1pt solid #111111;
            background: #ffffff;
            table-layout: fixed;
            margin: 2.5pt auto 0 auto;
            page-break-inside: avoid;
        }
        td.card-header-8x8 {
            height: 34pt;
            border-bottom: 1pt solid #111111;
            vertical-align: middle;
            padding: 2pt 4pt;
        }
        .instansi-title-8x8 {
            font-size: 10pt;
            font-weight: normal;
            color: #000000;
            line-height: 1.1;
            white-space: nowrap;
        }
        .instansi-code-8x8 {
            font-size: 8.5pt;
            font-weight: normal;
            color: #000000;
            line-height: 1.15;
            margin-top: 1pt;
            white-space: nowrap;
        }
        td.card-body-8x8 {
            height: 168pt;
            vertical-align: top;
            padding: 4pt 6pt 2pt 6pt;
        }
        table.table-info-row-8x8 {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2pt;
        }
        td.kode-barang-text-8x8 {
            font-size: 9pt;
            font-weight: normal;
            color: #000000;
            text-align: left;
            vertical-align: middle;
            white-space: nowrap;
        }
        td.nup-text-8x8 {
            font-size: 9pt;
            font-weight: normal;
            color: #000000;
            text-align: right;
            vertical-align: middle;
            white-space: nowrap;
        }
        .nama-barang-text-8x8 {
            font-size: 9pt;
            font-weight: normal;
            color: #000000;
            line-height: 1.15;
            word-wrap: break-word;
            word-break: break-word;
            max-height: 20pt;
            overflow: hidden;
        }
        .merk-tipe-text-8x8 {
            font-size: 8.5pt;
            font-weight: normal;
            color: #000000;
            line-height: 1.1;
            margin-top: 1.5pt;
            word-wrap: break-word;
            word-break: break-word;
            max-height: 18pt;
            overflow: hidden;
        }
        .qr-container-8x8 {
            text-align: center;
            margin-top: 4pt;
        }
        .qr-box-8x8 {
            display: inline-block;
            width: 95pt;
            height: 95pt;
        }
        .qr-box-placeholder {
            border: 1pt dashed #cccccc;
        }
    </style>
</head>
<body>

    <?php if ($is8x8Paper): ?>
        <?php foreach (($stickers ?? []) as $stkIdx => $stk):
            $thn = ! empty($stk['tahun_perolehan']) ? $stk['tahun_perolehan'] : date('Y');
            $headerCode = ($kodeUakpb ?? '145060900691285000KP') . '.' . $thn;
            $isLast = ($stkIdx === count($stickers) - 1);
        ?>
            <div class="<?= $isLast ? 'page-8x8-last' : 'page-8x8'; ?>">
                <table class="card-table-8x8" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="card-header-8x8">
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <?php if (! empty($logoBase64)): ?>
                                        <td style="width: 28pt; vertical-align: middle; text-align: left; padding-left: 4pt;">
                                            <img src="<?= $logoBase64; ?>" style="width: 24pt; height: 24pt; vertical-align: middle; display: block;" alt="Logo PU">
                                        </td>
                                    <?php endif; ?>
                                    <td style="vertical-align: middle; text-align: center; padding: <?= ! empty($logoBase64) ? '0 28pt 0 0' : '0'; ?>;">
                                        <div class="instansi-title-8x8">Kementerian Pekerjaan Umum</div>
                                        <div class="instansi-code-8x8"><?= esc($headerCode); ?></div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="card-body-8x8">
                            <table class="table-info-row-8x8" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="kode-barang-text-8x8"><?= esc($stk['kode_barang']); ?></td>
                                    <td class="nup-text-8x8">NUP: <?= esc($stk['nup']); ?></td>
                                </tr>
                            </table>
                            <div class="nama-barang-text-8x8"><?= esc($stk['nama_barang']); ?></div>
                            <div class="merk-tipe-text-8x8"><?= esc($stk['merk_tipe']); ?></div>
                            <div class="qr-container-8x8">
                                <?php if (! empty($stk['qr_base64'])): ?>
                                    <img src="<?= $stk['qr_base64']; ?>" class="qr-box-8x8" alt="QR Code">
                                <?php else: ?>
                                    <div class="qr-box-8x8 qr-box-placeholder"></div>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
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
                                        <?php if ($is8x8): ?>
                                            <table class="card-table-8x8" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td class="card-header-8x8">
                                                        <table style="width: 100%; border-collapse: collapse;">
                                                            <tr>
                                                                <?php if (! empty($logoBase64)): ?>
                                                                    <td style="width: 28pt; vertical-align: middle; text-align: left; padding-left: 4pt;">
                                                                        <img src="<?= $logoBase64; ?>" style="width: 24pt; height: 24pt; vertical-align: middle; display: block;" alt="Logo PU">
                                                                    </td>
                                                                <?php endif; ?>
                                                                <td style="vertical-align: middle; text-align: center; padding: <?= ! empty($logoBase64) ? '0 28pt 0 0' : '0'; ?>;">
                                                                    <div class="instansi-title-8x8">Kementerian Pekerjaan Umum</div>
                                                                    <div class="instansi-code-8x8"><?= esc($headerCode); ?></div>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="card-body-8x8">
                                                        <table class="table-info-row-8x8" cellpadding="0" cellspacing="0">
                                                            <tr>
                                                                <td class="kode-barang-text-8x8"><?= esc($stk['kode_barang']); ?></td>
                                                                <td class="nup-text-8x8">NUP: <?= esc($stk['nup']); ?></td>
                                                            </tr>
                                                        </table>
                                                        <div class="nama-barang-text-8x8"><?= esc($stk['nama_barang']); ?></div>
                                                        <div class="merk-tipe-text-8x8"><?= esc($stk['merk_tipe']); ?></div>
                                                        <div class="qr-container-8x8">
                                                            <?php if (! empty($stk['qr_base64'])): ?>
                                                                <img src="<?= $stk['qr_base64']; ?>" class="qr-box-8x8" alt="QR Code">
                                                            <?php else: ?>
                                                                <div class="qr-box-8x8 qr-box-placeholder"></div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        <?php else: ?>
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
                                        <?php endif; ?>
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
    <?php endif; ?>

</body>
</html>
