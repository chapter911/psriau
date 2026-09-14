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
            line-height: 1.15;
        }
        .sticker-page-table {
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
            height: 35.5mm;
            max-height: 35.5mm;
            border: 1px solid #111111;
            background: #ffffff;
            page-break-inside: avoid;
            overflow: hidden;
        }

        /* Bagian Atas (Identitas Instansi) */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1px solid #111111;
            padding: 1.2mm 2.2mm 1.2mm 2.2mm;
        }
        .header-logo-td {
            width: 9.5mm;
            vertical-align: middle;
            text-align: left;
            padding: 0;
        }
        .pu-logo {
            width: 8.5mm;
            height: 8.5mm;
            display: block;
        }
        .header-text-td {
            vertical-align: middle;
            text-align: center;
            padding: 0 0 0 1.5mm;
        }
        .instansi-title {
            font-size: 8.5pt;
            font-weight: normal;
            color: #000000;
            line-height: 1.15;
            letter-spacing: 0.1px;
        }
        .instansi-code {
            font-size: 7.8pt;
            font-weight: normal;
            color: #000000;
            line-height: 1.15;
            margin-top: 0.4mm;
            letter-spacing: 0.2px;
        }

        /* Bagian Bawah (Data Spesifikasi Aset & Verifikasi) */
        .body-table {
            width: 100%;
            border-collapse: collapse;
            padding: 1.5mm 2.2mm 1.5mm 2.2mm;
        }
        .body-info-td {
            width: 74%;
            vertical-align: top;
            padding-right: 1.5mm;
        }
        .info-top-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0.4mm;
        }
        .cell-kode-barang {
            width: 52%;
            font-size: 8.2pt;
            font-weight: normal;
            color: #000000;
            vertical-align: top;
        }
        .cell-nup {
            width: 48%;
            font-size: 8.2pt;
            font-weight: normal;
            color: #000000;
            vertical-align: top;
            text-align: left;
        }
        .cell-nama-barang {
            font-size: 8.2pt;
            font-weight: normal;
            color: #000000;
            line-height: 1.15;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .cell-spacer {
            height: 4.5mm;
        }
        .cell-merk-tipe {
            font-size: 7.8pt;
            font-weight: normal;
            color: #000000;
            line-height: 1.15;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Area Kanan (Kode Digital: QR Code) */
        .body-qr-td {
            width: 26%;
            vertical-align: middle;
            text-align: right;
            padding: 0;
        }
        .qr-image {
            width: 19mm;
            height: 19mm;
            display: block;
            margin-left: auto;
        }
    </style>
</head>
<body>

    <table class="sticker-page-table" cellpadding="0" cellspacing="0">
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
                            <!-- Bagian Atas (Identitas Instansi) -->
                            <table class="header-table" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="header-logo-td">
                                        <?php if (! empty($logoBase64)): ?>
                                            <img src="<?= $logoBase64; ?>" class="pu-logo" alt="Logo PU">
                                        <?php endif; ?>
                                    </td>
                                    <td class="header-text-td">
                                        <div class="instansi-title">Kementerian Pekerjaan Umum</div>
                                        <div class="instansi-code"><?= esc($headerCode); ?></div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Bagian Bawah (Data Spesifikasi Aset & Verifikasi) -->
                            <table class="body-table" cellpadding="0" cellspacing="0">
                                <tr>
                                    <!-- Area Kiri (Informasi Barang) -->
                                    <td class="body-info-td">
                                        <!-- Baris Atas: Kode Barang & NUP -->
                                        <table class="info-top-table" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td class="cell-kode-barang"><?= esc($stk['kode_barang']); ?></td>
                                                <td class="cell-nup">NUP: <?= esc($stk['nup']); ?></td>
                                            </tr>
                                        </table>
                                        <!-- Baris Tengah: Nama Baku Barang -->
                                        <div class="cell-nama-barang"><?= esc($stk['nama_barang']); ?></div>

                                        <!-- Spacer agar deskripsi spesifik berada di baris bawah -->
                                        <div class="cell-spacer"></div>

                                        <!-- Baris Bawah: Deskripsi Spesifik / Merk / Tipe Fisik -->
                                        <div class="cell-merk-tipe"><?= esc($stk['merk_tipe']); ?></div>
                                    </td>

                                    <!-- Area Kanan (Kode Digital: QR Code) -->
                                    <td class="body-qr-td">
                                        <?php if (! empty($stk['qr_base64'])): ?>
                                            <img src="<?= $stk['qr_base64']; ?>" class="qr-image" alt="QR Code">
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
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
