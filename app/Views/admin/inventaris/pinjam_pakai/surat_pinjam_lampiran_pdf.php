<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lampiran BMN Pinjam Pakai - <?= ! empty($loan['no_surat']) ? esc($loan['no_surat']) : 'Draft'; ?></title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1.5cm 1.8cm 1.5cm 1.8cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            line-height: 1.25;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .lampiran-title {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            text-transform: uppercase;
        }
        .lampiran-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            line-height: 1.25;
        }
        .lampiran-table th {
            border: 1px solid #000;
            padding: 5px 3px;
            text-align: center;
            font-weight: bold;
            vertical-align: middle;
            background-color: #ffffff;
        }
        .lampiran-table th.sub-num {
            background-color: #f2f2f2;
            font-size: 7.5pt;
            font-weight: normal;
            padding: 2px 0;
        }
        .lampiran-table td {
            border: 1px solid #000;
            padding: 5px 4px;
            vertical-align: top;
        }
        .lampiran-ttd {
            width: 100%;
            border-collapse: collapse;
            margin-top: 35px;
        }
        .lampiran-ttd td {
            width: 50%;
            vertical-align: top;
            font-size: 9pt;
            line-height: 1.25;
        }
    </style>
</head>
<body>

    <div class="lampiran-title">DAFTAR BARANG MILIK NEGARA YANG DIPINJAM PAKAI</div>

    <table class="lampiran-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 80px;">Kode Barang</th>
                <th style="width: 100px;">Nama Barang</th>
                <th style="width: 35px;">NUP</th>
                <th>Jenis Barang</th>
                <th style="width: 55px;">Tahun<br>Perolehan</th>
                <th style="width: 45px;">Jumlah</th>
                <th style="width: 85px;">Nilai Aset<br>per Unit<br>(Rp.)</th>
                <th style="width: 55px;">Kondisi<br>Barang</th>
                <th style="width: 130px;">Keterangan</th>
            </tr>
            <tr>
                <th class="sub-num">1</th>
                <th class="sub-num">2</th>
                <th class="sub-num">3</th>
                <th class="sub-num"></th>
                <th class="sub-num">4</th>
                <th class="sub-num">5</th>
                <th class="sub-num">6</th>
                <th class="sub-num">7</th>
                <th class="sub-num">8</th>
                <th class="sub-num">9</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center;">1.</td>
                <td style="text-align: center;"><?= esc($loan['kode_barang'] ?? ''); ?></td>
                <td><?= esc($loan['nama_barang'] ?? ''); ?></td>
                <td style="text-align: center;"><?= esc($loan['nup'] ?? ''); ?></td>
                <td>
                    Tipe : <?= esc($loan['merk_tipe'] ?? '-') ?: '-'; ?>
                    <?= ! empty($loan['kelengkapan']) ? ' (Kelengkapan: ' . esc($loan['kelengkapan']) . ')' : ''; ?>
                </td>
                <td style="text-align: center;"><?= esc($loan['tahun_perolehan'] ?? '-') ?: '-'; ?></td>
                <td style="text-align: center;">1</td>
                <td style="text-align: right;"><?= number_format((float) ($loan['nilai_perolehan'] ?? 0), 0, ',', '.'); ?></td>
                <td style="text-align: center;"><?= ucwords(str_replace('_', ' ', (string) ($loan['kondisi_pinjam'] ?? 'Baik'))); ?></td>
                <td><?= ! empty($loan['catatan']) ? esc($loan['catatan']) : 'Tercatat pada Inventarisasi Aset Satker Pelaksanaan Prasarana Strategis'; ?></td>
            </tr>
        </tbody>
    </table>

    <table class="lampiran-ttd" style="margin-top: 30px;">
        <tr>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                <strong>PIHAK KEDUA</strong><br>
                Yang menerima,
            </td>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                <strong>PIHAK PERTAMA</strong><br>
                Yang menyerahkan,<br>
                Satuan Kerja Pelaksanaan Prasarana Strategis Riau<br>
                Selaku Kuasa Penguna Barang,
            </td>
        </tr>
        <tr>
            <td style="height: 50px;"></td>
            <td style="height: 50px;"></td>
        </tr>
        <tr>
            <td style="text-align: center; vertical-align: bottom;">
                <strong><u><?= esc($loan['nama_peminjam'] ?? ''); ?></u></strong>
            </td>
            <td style="text-align: center; vertical-align: bottom;">
                <strong><u><?= esc($kasatker['nama'] ?? 'Muhammad Yudi Prasetya, ST.'); ?></u></strong>
            </td>
        </tr>
        <tr>
            <td style="text-align: center; vertical-align: top; padding-top: 3px;">
                <?php if (! empty($isKonsultan)): ?>
                    Tenaga Penunjang Kegiatan
                <?php else: ?>
                    NIP. <?= esc($loan['nip_peminjam'] ?? '') ?: '-'; ?>
                <?php endif; ?>
            </td>
            <td style="text-align: center; vertical-align: top; padding-top: 3px;">
                NIP. <?= esc($kasatker['nip'] ?? '198002142014121002'); ?>
            </td>
        </tr>
    </table>

</body>
</html>
