<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lampiran BMN Pinjam Pakai - <?= ! empty($loan['no_surat']) ? esc($loan['no_surat']) : 'Draft'; ?></title>
    <style>
        @page {
            size: A4 landscape;
            margin: 2.5cm 2.5cm 2.0cm 2.5cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            line-height: 1.18;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .lampiran-title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            letter-spacing: 0.3px;
            margin-bottom: 14px;
            text-transform: uppercase;
            line-height: 1.2;
        }
        .lampiran-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5pt;
            line-height: 1.15;
        }
        .lampiran-table th {
            border: 1px solid #000;
            padding: 5px 3px;
            text-align: center;
            font-weight: bold;
            vertical-align: middle;
            background-color: #ffffff;
            font-size: 10.5pt;
        }
        .lampiran-table td {
            border: 1px solid #000;
            padding: 4px 4px;
            vertical-align: top;
            font-size: 10.5pt;
        }
        .lampiran-ttd {
            width: 100%;
            border-collapse: collapse;
            margin-top: 35px;
            font-size: 12pt;
        }
        .lampiran-ttd td {
            width: 50%;
            vertical-align: top;
            font-size: 12pt;
            line-height: 1.2;
        }
    </style>
</head>
<body>

    <div class="lampiran-title">DAFTAR BARANG MILIK NEGARA YANG DIPINJAM PAKAI</div>

    <table class="lampiran-table">
        <thead>
            <tr>
                <th style="width: 3.8%;">No</th>
                <th style="width: 10.5%;">Kode Barang</th>
                <th style="width: 14.5%;">Nama Barang</th>
                <th style="width: 4.8%;">NUP</th>
                <th style="width: 20%;">Merk / Tipe</th>
                <th style="width: 9.5%;">Tahun<br>Perolehan</th>
                <th style="width: 6.5%;">Jumlah</th>
                <th style="width: 11.5%;">Nilai Aset<br>(Rp)</th>
                <th style="width: 7.5%;">Kondisi<br>Barang</th>
                <th style="width: 11.4%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $itemList = ! empty($loan['items']) ? $loan['items'] : [$loan];
            $totalQty = 0;
            $totalNilai = 0.0;
            $noUrut = 1;
            foreach ($itemList as $itm): 
                $qty = 1;
                $totalQty += $qty;
                $nilaiItem = (float) ($itm['nilai_perolehan'] ?? 0);
                $totalNilai += $nilaiItem;
                $kondisiItem = $itm['kondisi_pinjam'] ?? ($loan['kondisi_pinjam'] ?? 'Baik');
                $kelengkapanItem = $itm['kelengkapan'] ?? ($loan['kelengkapan'] ?? '');
                $catatanItem = ! empty($itm['catatan']) ? $itm['catatan'] : (! empty($loan['catatan']) ? $loan['catatan'] : 'Tercatat pada Inventarisasi Aset Satker Pelaksanaan Prasarana Strategis');
                $cleanNama = clean_inventaris_text($itm['nama_barang'] ?? '');
                $cleanMerk = clean_inventaris_text($itm['merk_tipe'] ?? '');
                $displayMerk = ($cleanMerk !== '' && strcasecmp($cleanMerk, $cleanNama) !== 0) ? $cleanMerk : '-';
            ?>
            <tr>
                <td style="text-align: center;"><?= $noUrut++; ?>.</td>
                <td style="text-align: center;"><?= esc($itm['kode_barang'] ?? ''); ?></td>
                <td><?= esc($cleanNama); ?></td>
                <td style="text-align: center;"><?= esc($itm['nup'] ?? ''); ?></td>
                <td>
                    <?= (strncasecmp($cleanMerk, 'Tipe', 4) === 0 ? '' : 'Tipe : ') . esc($displayMerk); ?>
                    <?= ! empty($kelengkapanItem) ? ' (' . esc($kelengkapanItem) . ')' : ''; ?>
                </td>
                <td style="text-align: center;"><?= esc($itm['tahun_perolehan'] ?? '-') ?: '-'; ?></td>
                <td style="text-align: center;"><?= $qty; ?></td>
                <td style="text-align: right;"><?= number_format($nilaiItem, 0, ',', '.'); ?></td>
                <td style="text-align: center;"><?= ucwords(str_replace('_', ' ', (string) $kondisiItem)); ?></td>
                <td><?= esc($catatanItem); ?></td>
            </tr>
            <?php endforeach; ?>
            <tr style="font-weight: bold;">
                <td colspan="6" style="text-align: center; font-weight: bold; padding: 4px;">JUMLAH / TOTAL</td>
                <td style="text-align: center; font-weight: bold;"><?= $totalQty; ?></td>
                <td style="text-align: right; font-weight: bold;"><?= number_format($totalNilai, 0, ',', '.'); ?></td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>

    <table class="lampiran-ttd" style="margin-top: 30px;">
        <tr>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                <strong>PIHAK KEDUA</strong><br>
                Yang menerima,
                <?php if (! empty($isDelegasi)): ?>
                    <br><?= esc($loan['jabatan_peminjam'] ?? 'Kepala Satuan Kerja'); ?>
                <?php endif; ?>
            </td>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                <strong>PIHAK PERTAMA</strong><br>
                Yang menyerahkan,<br>
                <?php if (! empty($isDelegasi)): ?>
                    a.n. Kuasa Pengguna Barang<br>
                    <?= esc($pihakPertama['jabatan_singkat'] ?? 'Pengurus Barang Pengguna'); ?>,
                <?php else: ?>
                    Selaku Kuasa Penguna Barang,<br>
                    Kepala Satuan Kerja<br>
                    Pelaksanaan Prasarana Strategis Riau
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td style="height: 55px;"></td>
            <td style="height: 55px;"></td>
        </tr>
        <tr>
            <td style="text-align: center; vertical-align: bottom;">
                <strong><u><?= esc($loan['nama_peminjam'] ?? ''); ?></u></strong>
            </td>
            <td style="text-align: center; vertical-align: bottom;">
                <strong><u><?= esc($pihakPertama['nama'] ?? ($kasatker['nama'] ?? 'Muhammad Yudi Prasetya, ST.')); ?></u></strong>
            </td>
        </tr>
        <tr>
            <td style="text-align: center; vertical-align: top; padding-top: 2px;">
                <?php if (! empty($isKonsultan)): ?>
                    Tenaga Penunjang Kegiatan
                <?php else: ?>
                    NIP. <?= esc($loan['nip_peminjam'] ?? '') ?: '-'; ?>
                <?php endif; ?>
            </td>
            <td style="text-align: center; vertical-align: top; padding-top: 2px;">
                NIP. <?= esc($pihakPertama['nip'] ?? ($kasatker['nip'] ?? '198002142014121002')); ?>
            </td>
        </tr>
    </table>

</body>
</html>

