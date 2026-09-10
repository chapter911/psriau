<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Aset Tidak Terdata</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.2cm 1.4cm 1.2cm 1.4cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8.5pt;
            line-height: 1.35;
            color: #111827;
            margin: 0;
            padding: 0;
        }

        /* Judul Dokumen (Tanpa Kop Surat) */
        .doc-title-box {
            text-align: center;
            margin-top: 5px;
            margin-bottom: 16px;
        }
        .doc-title {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #0f172a;
            text-decoration: underline;
            margin-bottom: 4px;
        }
        .doc-subtitle {
            font-size: 9pt;
            font-weight: 600;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Tabel Data */
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .table-data th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: bold;
            font-size: 8pt;
            text-transform: uppercase;
            border: 1px solid #000000;
            padding: 6px 5px;
            text-align: center;
        }
        .table-data td {
            border: 1px solid #000000;
            padding: 5.5px 6px;
            font-size: 8pt;
            vertical-align: top;
        }
        .table-data .text-center {
            text-align: center;
        }
        .table-data .text-right {
            text-align: right;
        }
        .table-data .total-row td {
            background-color: #f8fafc;
            font-weight: bold;
            font-size: 8.5pt;
        }

        /* Tanda Tangan */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .signature-table td {
            padding: 0;
            vertical-align: top;
        }
        .sign-box {
            text-align: center;
            width: 250px;
            float: right;
        }
        .sign-date {
            font-size: 8.5pt;
            margin-bottom: 4px;
        }
        .sign-title {
            font-size: 8.5pt;
            font-weight: bold;
            line-height: 1.35;
        }
        .sign-space {
            height: 65px;
        }
        .sign-name {
            font-size: 9pt;
            font-weight: bold;
            text-decoration: underline;
            color: #0f172a;
        }
        .sign-nip {
            font-size: 8pt;
            color: #334155;
            margin-top: 2px;
        }
    </style>
</head>
<body>

    <!-- Judul Dokumen Langsung (Tanpa Kop Sesuai Permintaan) -->
    <div class="doc-title-box">
        <div class="doc-title">DAFTAR ASET TIDAK TERDATA</div>
        <div class="doc-subtitle">SATUAN KERJA PELAKSANAAN PRASARANA PERMUKIMAN STRATEGIS PROVINSI RIAU</div>
    </div>

    <!-- Tabel Data Sesuai Format Asli Lampiran -->
    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 145px;">Nama Barang</th>
                <th style="width: 50px;">Buah</th>
                <th>Merk / Type</th>
                <th style="width: 55px;">Tahun Perolehan</th>
                <th style="width: 100px;">Lokasi Aset</th>
                <th style="width: 140px;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1; 
            $totalBuah = 0;
            foreach ($items as $item) : 
                $totalBuah += (int) $item['jumlah'];
                $lokasi = $item['nama_ruangan'] ?? $item['lokasi_penempatan'] ?? '-';
            ?>
                <tr>
                    <td class="text-center"><?= $no++; ?></td>
                    <td style="font-weight: bold;"><?= esc($item['nama_barang']); ?></td>
                    <td class="text-center font-weight-bold"><?= (int) $item['jumlah']; ?></td>
                    <td><?= esc($item['merk_tipe'] ?: '-'); ?></td>
                    <td class="text-center"><?= esc($item['tahun_perolehan'] ?: '-'); ?></td>
                    <td><?= esc($lokasi); ?></td>
                    <td><?= esc($item['keterangan'] ?: '-'); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2" class="text-center" style="font-weight: bold;">JUMLAH</td>
                <td class="text-center" style="font-weight: bold;"><?= $totalBuah; ?></td>
                <td colspan="4" style="font-style: italic; color: #475569;">
                    Total Kuantitas: <?= $totalBuah; ?> Buah (<?= count($items); ?> Item Barang)
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Tanda Tangan Hendrick Bastiar -->
    <table class="signature-table">
        <tr>
            <td style="width: 55%;"></td>
            <td style="width: 45%;">
                <div class="sign-box">
                    <div class="sign-date">Pekanbaru, <?= $tanggalCetak; ?></div>
                    <div class="sign-title">
                        Petugas Aset Tetap<br>
                        Satker Pelaksanaan Prasarana Strategis Riau
                    </div>
                    <div class="sign-space"></div>
                    <div class="sign-name"><?= esc($items[0]['petugas_nama'] ?? 'Hendrick Bastiar'); ?></div>
                    <div class="sign-nip">NIP. <?= esc($items[0]['petugas_nip'] ?? '197810162025211023'); ?></div>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
