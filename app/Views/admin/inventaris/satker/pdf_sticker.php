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
            margin: 7mm 6mm 7mm 6mm;
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

        /* Tabel Halaman Utama: 2 Kolom Per Baris Fixed Layout */
        .sticker-page-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 3.5mm 3.5mm;
        }
        .sticker-cell {
            width: 50%;
            max-width: 95.5mm;
            vertical-align: top;
            padding: 0;
        }
        .sticker-cell.empty-cell {
            border: none;
            background: transparent;
        }

        /* Kartu Stiker Tunggal */
        .sticker-card {
            width: 100%;
            height: 36.5mm;
            max-height: 36.5mm;
            border: 1px solid #111111;
            background: #ffffff;
            page-break-inside: avoid;
            overflow: hidden;
            box-sizing: border-box;
        }

        /* Bagian Atas (Identitas Instansi) */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1px solid #111111;
            height: 11.5mm;
        }
        .header-logo-td {
            width: 11mm;
            vertical-align: middle;
            text-align: left;
            padding-left: 2.2mm;
        }
        .pu-logo {
            width: 8.5mm;
            height: 8.5mm;
            display: block;
        }
        .header-text-td {
            vertical-align: middle;
            text-align: center;
            padding: 1.2mm 0;
        }
        .header-spacer-td {
            width: 11mm;
            padding: 0;
        }
        .instansi-title {
            font-size: 8pt;
            font-weight: normal;
            color: #000000;
            line-height: 1.15;
            text-align: center;
            white-space: nowrap;
        }
        .instansi-code {
            font-size: 7.2pt;
            font-weight: normal;
            color: #000000;
            line-height: 1.15;
            margin-top: 0.5mm;
            text-align: center;
            white-space: nowrap;
        }

        /* Bagian Bawah (Data Spesifikasi Aset & Verifikasi) */
        .body-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            height: 24.8mm;
        }
        .body-info-td {
            width: 72%;
            vertical-align: top;
            padding: 1.8mm 1.5mm 1mm 2.2mm;
            overflow: hidden;
        }
        .info-top-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-bottom: 0.8mm;
        }
        .cell-kode-barang {
            width: 55%;
            font-size: 8pt;
            font-weight: normal;
            color: #000000;
            vertical-align: top;
            white-space: nowrap;
        }
        .cell-nup {
            width: 45%;
            font-size: 8pt;
            font-weight: normal;
            color: #000000;
            vertical-align: top;
            text-align: left;
            white-space: nowrap;
        }
        .cell-nama-barang {
            font-size: 7.8pt;
            font-weight: normal;
            color: #000000;
            line-height: 1.15;
            word-wrap: break-word;
            word-break: break-word;
            margin-bottom: 1.8mm;
        }
        .cell-merk-tipe {
            font-size: 7.2pt;
            font-weight: normal;
            color: #000000;
            line-height: 1.15;
            word-wrap: break-word;
            word-break: break-word;
        }

        /* Area Kanan (Kode Digital: QR Code) */
        .body-qr-td {
            width: 28%;
            vertical-align: middle;
            text-align: right;
            padding: 1.5mm 2.2mm 1mm 0;
        }
        .qr-image {
            width: 19.5mm;
            height: 19.5mm;
            display: block;
            margin-left: auto;
        }
        .qr-placeholder {
            width: 19.5mm;
            height: 19.5mm;
            border: 1px dashed #cccccc;
            display: block;
            margin-left: auto;
        }
    </style>
</head>
<body>

    <table class="sticker-page-table" cellpadding="0" cellspacing="0">
        <colgroup>
            <col style="width: 50%;">
            <col style="width: 50%;">
        </colgroup>
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
                                <colgroup>
                                    <col style="width: 11mm;">
                                    <col style="width: 73mm;">
                                    <col style="width: 11mm;">
                                </colgroup>
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
                                    <td class="header-spacer-td"></td>
                                </tr>
                            </table>

                            <!-- Bagian Bawah (Data Spesifikasi Aset & Verifikasi) -->
                            <table class="body-table" cellpadding="0" cellspacing="0">
                                <colgroup>
                                    <col style="width: 72%;">
                                    <col style="width: 28%;">
                                </colgroup>
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

                                        <!-- Baris Tengah: Nama Baku Barang Berdasarkan Kodifikasi Resmi BMN -->
                                        <div class="cell-nama-barang" title="<?= esc($stk['nama_barang'], 'attr'); ?>"><?= esc($stk['nama_barang']); ?></div>

                                        <!-- Baris Bawah: Deskripsi Spesifik, Tipe, atau Merek Fisik Aset -->
                                        <div class="cell-merk-tipe" title="<?= esc($stk['merk_tipe'], 'attr'); ?>"><?= esc($stk['merk_tipe']); ?></div>
                                    </td>

                                    <!-- Area Kanan (Kode Digital: QR Code) -->
                                    <td class="body-qr-td">
                                        <?php if (! empty($stk['qr_base64'])): ?>
                                            <img src="<?= $stk['qr_base64']; ?>" class="qr-image" alt="QR Code">
                                        <?php else: ?>
                                            <div class="qr-placeholder"></div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                <?php endforeach; ?>

                <?php if (count($row) === 1): ?>
                    <!-- Sel kosong penyeimbang jika baris ganjil -->
                    <td class="sticker-cell empty-cell"></td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </table>

</body>
</html>
