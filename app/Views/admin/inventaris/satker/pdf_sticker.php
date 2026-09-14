<?php
if (! function_exists('esc')) {
    function esc($data, string $context = 'html'): string {
        return htmlspecialchars((string) $data, ENT_QUOTES, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Sticker BMN - <?= date('Y-m-d'); ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 7mm 8mm 7mm;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #000000;
            background: #ffffff;
            font-size: 8pt;
            line-height: 1.2;
        }
        .sticker-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 4mm 4mm;
        }
        .sticker-cell {
            width: 50%;
            vertical-align: top;
            padding: 0;
        }
        .sticker-card {
            width: 100%;
            height: 36.5mm;
            max-height: 36.5mm;
            border: 1px solid #111111;
            background: #ffffff;
            page-break-inside: avoid;
            overflow: hidden;
            position: relative;
        }
        /* Header Bagian Atas */
        .sticker-header {
            width: 100%;
            height: 11mm;
            border-bottom: 1px solid #111111;
            display: table;
            padding: 1mm 2mm 1mm 2mm;
        }
        .header-logo-col {
            display: table-cell;
            width: 9mm;
            vertical-align: middle;
            text-align: left;
        }
        .header-logo-col img {
            width: 8.8mm;
            height: 8.8mm;
            display: block;
        }
        .header-text-col {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            padding-left: 1.5mm;
        }
        .instansi-title {
            font-size: 7.8pt;
            font-weight: bold;
            line-height: 1.15;
            color: #000000;
            letter-spacing: 0.1px;
        }
        .instansi-code {
            font-size: 7.2pt;
            font-weight: normal;
            line-height: 1.15;
            color: #000000;
            margin-top: 0.4mm;
            font-family: Arial, Helvetica, sans-serif;
            letter-spacing: 0.2px;
        }
        /* Body Bagian Bawah */
        .sticker-body {
            width: 100%;
            height: 25.5mm;
            display: table;
            padding: 1.5mm 2mm 1.5mm 2.2mm;
        }
        .body-info-col {
            display: table-cell;
            vertical-align: top;
            width: 73%;
            padding-right: 1.5mm;
        }
        .info-code-nup-table {
            width: 100%;
            display: table;
            margin-bottom: 0.8mm;
        }
        .col-kode-barang {
            display: table-cell;
            font-size: 7.8pt;
            font-weight: bold;
            color: #000000;
            width: 55%;
            vertical-align: middle;
            font-family: Arial, Helvetica, sans-serif;
        }
        .col-nup {
            display: table-cell;
            font-size: 7.8pt;
            font-weight: bold;
            color: #000000;
            text-align: left;
            width: 45%;
            vertical-align: middle;
        }
        .item-nama {
            font-size: 7.8pt;
            font-weight: bold;
            color: #000000;
            line-height: 1.15;
            max-height: 8mm;
            overflow: hidden;
            margin-bottom: 2mm;
        }
        .item-merk {
            font-size: 7pt;
            line-height: 1.15;
            color: #111111;
            max-height: 7.5mm;
            overflow: hidden;
        }
        .body-qr-col {
            display: table-cell;
            width: 27%;
            vertical-align: middle;
            text-align: right;
        }
        .body-qr-col img {
            width: 20mm;
            height: 20mm;
            display: block;
            margin-left: auto;
        }
    </style>
</head>
<body>

    <table class="sticker-table">
        <?php
        $chunks = array_chunk($stickers ?? [], 2);
        foreach ($chunks as $row):
        ?>
            <tr>
                <?php foreach ($row as $stk):
                    $thn = ! empty($stk['tahun_perolehan']) ? $stk['tahun_perolehan'] : '2025';
                    $headerCode = ($kodeUakpb ?? '145060900691285000KP') . '.' . $thn;
                ?>
                    <td class="sticker-cell">
                        <div class="sticker-card">
                            <!-- Header Instansi & Kode UAKPB -->
                            <div class="sticker-header">
                                <div class="header-logo-col">
                                    <?php if (! empty($logoBase64)): ?>
                                        <img src="<?= $logoBase64; ?>" alt="Logo PU">
                                    <?php endif; ?>
                                </div>
                                <div class="header-text-col">
                                    <div class="instansi-title">Kementerian Pekerjaan Umum</div>
                                    <div class="instansi-code"><?= esc($headerCode); ?></div>
                                </div>
                            </div>

                            <!-- Body: Data Aset & QR Code -->
                            <div class="sticker-body">
                                <div class="body-info-col">
                                    <div class="info-code-nup-table">
                                        <div class="col-kode-barang"><?= esc($stk['kode_barang']); ?></div>
                                        <div class="col-nup">NUP: <?= esc($stk['nup']); ?></div>
                                    </div>
                                    <div class="item-nama"><?= esc($stk['nama_barang']); ?></div>
                                    <div class="item-merk"><?= esc($stk['merk_tipe']); ?></div>
                                </div>
                                <div class="body-qr-col">
                                    <?php if (! empty($stk['qr_base64'])): ?>
                                        <img src="<?= $stk['qr_base64']; ?>" alt="QR Code">
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </td>
                <?php endforeach; ?>

                <?php if (count($row) === 1): ?>
                    <!-- Sel kosong penyeimbang jika jumlah ganjil -->
                    <td class="sticker-cell" style="border: none;"></td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </table>

</body>
</html>
