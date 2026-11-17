<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Kertas Kerja BMN Sekolah Rakyat</title>
    <style>
        @page {
            margin: 1.5cm 1.2cm 1.5cm 1.2cm;
            size: A4 landscape;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8pt;
            color: #222;
            line-height: 1.3;
        }
        .header-title {
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 4px;
            text-transform: uppercase;
        }
        .header-subtitle {
            text-align: center;
            font-size: 9pt;
            margin-bottom: 15px;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 12px;
            font-size: 8pt;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 2px 4px;
            vertical-align: top;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 7pt;
        }
        .data-table th, .data-table td {
            border: 0.5px solid #444;
            padding: 3px 4px;
            vertical-align: middle;
        }
        .data-table th {
            background-color: #2B579A;
            color: #fff;
            text-align: center;
            font-weight: bold;
        }
        .section-header {
            background-color: #D9E1F2;
            font-weight: bold;
            padding: 4px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .signature-table {
            width: 100%;
            margin-top: 25px;
            page-break-inside: avoid;
            font-size: 8pt;
        }
        .signature-table td {
            text-align: center;
            vertical-align: top;
            width: 50%;
        }
    </style>
</head>
<body>

    <div class="header-title">KERTAS KERJA RINCIAN PENDETAILAN BMN HASIL PEKERJAAN KONSTRUKSI DAN PENGADAAN MEBEL/PERALATAN</div>
    <div class="header-subtitle">SATUAN KERJA PELAKSANAAN PRASARANA STRATEGIS PROVINSI RIAU</div>

    <table class="meta-table">
        <tr>
            <td style="width: 15%; font-weight: bold;">Pekerjaan</td>
            <td style="width: 45%;">: Pembangunan Sekolah Rakyat Provinsi Riau</td>
            <td style="width: 15%; font-weight: bold;">Nilai Kontrak</td>
            <td style="width: 25%;">: Rp <?= number_format($stats['total_nilai'] ?? 0, 0, ',', '.'); ?>,- (Addendum V)</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Lokasi</td>
            <td>: Komplek Sport Center, Kec. Kuantan Tengah, Kab. Kuantan Singingi</td>
            <td style="font-weight: bold;">Penyedia Jasa</td>
            <td>: PT. Duta Rama – PT. Gala Karya, KSO</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Nomor Kontrak</td>
            <td>: HK.02.01/PPS-RIAU/FSK-SR/2025/01 tanggal 24 Desember 2025</td>
            <td style="font-weight: bold;">Status Aset</td>
            <td>: <?= number_format($stats['total_items'] ?? 0); ?> Item Rincian Terdata</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 90px;">Kode BMN</th>
                <th>Jenis BMN</th>
                <th>Merk / Type / Spesifikasi Material Teknis</th>
                <th style="width: 40px;">Jml</th>
                <th style="width: 40px;">Sat</th>
                <th style="width: 85px;">Nilai Perolehan (Rp)</th>
                <th style="width: 95px;">Total Nilai (Rp)</th>
                <th style="width: 50px;">Kondisi</th>
                <th style="width: 110px;">Lokasi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                $currentGroup = '';
                foreach ($items as $it): 
                    if ($it['kelompok_bmn'] !== $currentGroup):
                        $currentGroup = $it['kelompok_bmn'];
            ?>
                <tr>
                    <td colspan="10" class="section-header"><?= esc($currentGroup); ?></td>
                </tr>
            <?php endif; ?>
                <tr>
                    <td class="text-center"><?= $it['no_urut']; ?></td>
                    <td class="text-center font-bold"><?= esc($it['kode_bmn']); ?></td>
                    <td class="font-bold"><?= esc($it['jenis_bmn']); ?></td>
                    <td><?= esc($it['spesifikasi']); ?></td>
                    <td class="text-center"><?= rtrim(rtrim(number_format((float) $it['jumlah'], 2, ',', '.'), '0'), ','); ?></td>
                    <td class="text-center"><?= esc($it['satuan']); ?></td>
                    <td class="text-right"><?= number_format((float) $it['nilai_perolehan'], 0, ',', '.'); ?></td>
                    <td class="text-right font-bold"><?= number_format((float) $it['total_nilai_perolehan'], 0, ',', '.'); ?></td>
                    <td class="text-center"><?= esc($it['kondisi']); ?></td>
                    <td><?= esc($it['lokasi']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background-color: #EAEAEA; font-weight: bold;">
                <td colspan="7" class="text-center">TOTAL KESELURUHAN NILAI PEROLEHAN BMN</td>
                <td class="text-right">Rp <?= number_format($stats['total_nilai'] ?? 0, 0, ',', '.'); ?></td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <table class="signature-table">
        <tr>
            <td>
                Mengetahui,<br>
                <strong>Pejabat Pembuat Komitmen (PPK)</strong><br>
                Prasarana Strategis Riau<br><br><br><br><br>
                ( ............................................................ )<br>
                NIP. ........................................................
            </td>
            <td>
                Pekanbaru, <?= date('d F Y'); ?><br>
                <strong>Petugas Pengelola / Verifikator BMN</strong><br>
                Satker Pelaksanaan Prasarana Strategis Riau<br><br><br><br><br>
                ( ............................................................ )<br>
                NIP. ........................................................
            </td>
        </tr>
    </table>

</body>
</html>
