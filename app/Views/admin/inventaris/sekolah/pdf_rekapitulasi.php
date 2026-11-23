<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hasil Penelitian BMN Madrasah PHTC Riau</title>
    <style>
        @page {
            margin: 1.2cm 1.2cm 1.2cm 1.2cm;
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
            margin-bottom: 3px;
            text-transform: uppercase;
        }
        .header-subtitle {
            text-align: center;
            font-size: 9pt;
            margin-bottom: 12px;
            font-weight: bold;
            color: #333;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 10px;
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
            margin-bottom: 12px;
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
        .page-break { page-break-before: always; }
        .signature-table {
            width: 100%;
            margin-top: 20px;
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

    <div class="header-title">RINCIAN HASIL PENELITIAN BARANG MILIK NEGARA (BMN)</div>
    <div class="header-subtitle">PEKERJAAN REHABILITASI DAN RENOVASI MADRASAH (PHTC) PROVINSI RIAU</div>

    <table class="meta-table">
        <tr>
            <td style="width: 18%; font-weight: bold;">Satker Pelaksana</td>
            <td style="width: 42%;">: Satker Pelaksanaan Prasarana Strategis Provinsi Riau</td>
            <td style="width: 18%; font-weight: bold;">Total Kontrak Fisik (ATR)</td>
            <td style="width: 22%;">: Rp <?= number_format($stats['total_fisik'] ?? 0, 0, ',', '.'); ?>,-</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Tahun Anggaran</td>
            <td>: 2025 - 2026</td>
            <td style="font-weight: bold;">Total Meubelair (ASP)</td>
            <td>: Rp <?= number_format($stats['total_asp'] ?? 0, 0, ',', '.'); ?>,- (<?= number_format($stats['total_asp_unit'] ?? 0); ?> Unit)</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Ruang Lingkup</td>
            <td>: 9 Paket Kegiatan (<?= $stats['total_madrasah'] ?? 0; ?> Madrasah: <?= $stats['count_negeri'] ?? 0; ?> Negeri, <?= $stats['count_swasta'] ?? 0; ?> Swasta)</td>
            <td style="font-weight: bold;">Total Nilai Perolehan</td>
            <td>: <strong>Rp <?= number_format($stats['total_gabungan'] ?? 0, 0, ',', '.'); ?>,-</strong></td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th>Uraian Paket Kegiatan</th>
                <th style="width: 55px;">Tahun</th>
                <th style="width: 65px;">Kode BMN</th>
                <th>Jenis BMN</th>
                <th>Wilayah / Lokasi</th>
                <th style="width: 45px;">Madrasah</th>
                <th style="width: 85px;">Kontrak Fisik ATR (Rp)</th>
                <th style="width: 75px;">MK / Supervisi (Rp)</th>
                <th style="width: 85px;">Peralatan Mesin ASP (Rp)</th>
                <th style="width: 90px;">Total ATR + PM (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                $sumFisik = 0; $sumMk = 0; $sumAsp = 0; $sumGab = 0; $sumMadrasah = 0;
            ?>
            <?php foreach ($paketList as $p): ?>
                <?php 
                    $sumFisik    += (float) $p['nilai_kontrak_fisik'];
                    $sumMk       += (float) $p['nilai_mk_supervisi'];
                    $sumAsp      += (float) $p['total_asp_nilai'];
                    $sumGab      += (float) $p['total_gabungan'];
                    $sumMadrasah += (int) $p['jumlah_madrasah'];
                ?>
                <tr>
                    <td class="text-center font-bold"><?= $p['no_paket']; ?></td>
                    <td class="font-bold"><?= esc($p['nama_paket']); ?></td>
                    <td class="text-center"><?= esc($p['tahun_anggaran']); ?></td>
                    <td class="text-center"><?= esc($p['kode_bmn']); ?></td>
                    <td><?= esc($p['jenis_bmn']); ?></td>
                    <td><?= esc($p['wilayah']); ?></td>
                    <td class="text-center"><?= $p['jumlah_madrasah']; ?> Unit</td>
                    <td class="text-right"><?= number_format($p['nilai_kontrak_fisik'], 0, ',', '.'); ?></td>
                    <td class="text-right"><?= number_format($p['nilai_mk_supervisi'], 0, ',', '.'); ?></td>
                    <td class="text-right font-bold"><?= number_format($p['total_asp_nilai'], 0, ',', '.'); ?></td>
                    <td class="text-right font-bold" style="background-color: #f7fafc;"><?= number_format($p['total_gabungan'], 0, ',', '.'); ?></td>
                </tr>
            <?php endforeach; ?>
            <tr style="background-color: #D9E1F2; font-weight: bold;">
                <td colspan="6" class="text-right">TOTAL KESELURUHAN (PAKET 1 - 9):</td>
                <td class="text-center"><?= $sumMadrasah; ?> Unit</td>
                <td class="text-right">Rp <?= number_format($sumFisik, 0, ',', '.'); ?></td>
                <td class="text-right">Rp <?= number_format($sumMk, 0, ',', '.'); ?></td>
                <td class="text-right">Rp <?= number_format($sumAsp, 0, ',', '.'); ?></td>
                <td class="text-right">Rp <?= number_format($sumGab, 0, ',', '.'); ?></td>
            </tr>
        </tbody>
    </table>

    <!-- RINGKASAN WILAYAH -->
    <div style="font-weight: bold; font-size: 8.5pt; margin-bottom: 4px; margin-top: 8px;">
        REKAPITULASI HASIL PENELITIAN BMN PER LOKASI / KABUPATEN:
    </div>
    <table class="data-table" style="width: 75%;">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th>Lokasi / Kabupaten</th>
                <th style="width: 110px;">Jumlah ATR (Rp)</th>
                <th style="width: 110px;">Jumlah ASP (Rp)</th>
                <th style="width: 120px;">Total ATR + PM (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($paketList as $p): ?>
                <tr>
                    <td class="text-center"><?= $p['no_paket']; ?></td>
                    <td><?= esc($p['wilayah']); ?></td>
                    <td class="text-right"><?= number_format($p['nilai_kontrak_fisik'], 0, ',', '.'); ?></td>
                    <td class="text-right"><?= number_format($p['total_asp_nilai'], 0, ',', '.'); ?></td>
                    <td class="text-right font-bold"><?= number_format($p['total_gabungan'], 0, ',', '.'); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <table class="signature-table">
        <tr>
            <td>
                Mengetahui,<br>
                <strong>Pejabat Pembuat Komitmen (PPK)<br>Prasarana Strategis Provinsi Riau</strong>
                <br><br><br><br><br>
                <strong><u>( .................................................... )</u></strong><br>
                NIP. ....................................................
            </td>
            <td>
                Pekanbaru, <?= date('d F Y'); ?><br>
                <strong>Pengurus Barang Pengguna / Tim BMN<br>Satker Pelaksanaan Prasarana Strategis Riau</strong>
                <br><br><br><br><br>
                <strong><u>( .................................................... )</u></strong><br>
                NIP. ....................................................
            </td>
        </tr>
    </table>

</body>
</html>
