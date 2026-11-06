<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Daftar Personel Perjalanan Dinas Swakelola'); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #1e293b;
            background-color: #f8fafc;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px double #0f172a;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0 0 4px 0;
            font-size: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
        }
        .header h3 {
            margin: 0 0 4px 0;
            font-size: 13px;
            font-weight: normal;
            color: #334155;
        }
        .header p {
            margin: 0;
            font-size: 10px;
            color: #64748b;
        }
        .kpi-bar {
            display: flex;
            justify-content: space-between;
            background-color: #f1f5f9;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 11px;
            font-weight: bold;
            border: 1px solid #e2e8f0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            font-size: 10px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            vertical-align: top;
        }
        th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            font-size: 9px;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8.5px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-secondary {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }
        .signatures {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }
        .sig-block {
            text-align: center;
            width: 320px;
            font-size: 11px;
        }
        .sig-space {
            height: 60px;
        }
        .no-print {
            margin-bottom: 20px;
            text-align: right;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: bold;
            color: #ffffff;
            background-color: #2563eb;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            border: none;
        }
        .btn-secondary {
            background-color: #64748b;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .container {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            .no-print {
                display: none;
            }
            th {
                background-color: #1e3a8a !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .badge-success {
                background-color: #d1fae5 !important;
                color: #065f46 !important;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="no-print">
        <button onclick="window.print();" class="btn"><i class="fas fa-print"></i> Cetak Dokumen</button>
        <button onclick="window.close();" class="btn btn-secondary"><i class="fas fa-times"></i> Tutup</button>
    </div>

    <div class="header">
        <h2>DAFTAR PERSONEL PERJALANAN DINAS (PERJADIN) SWAKELOLA</h2>
        <h3>SATUAN KERJA PELAKSANAAN PRASARANA STRATEGIS PROVINSI RIAU</h3>
        <p>Direktorat Jenderal Prasarana Strategis — Kementerian Pekerjaan Umum | Tahun Anggaran 2025 / 2026</p>
    </div>

    <div class="kpi-bar">
        <div>Total Penugasan: <strong><?= number_format($stats['total_perjalanan'] ?? 0, 0, ',', '.'); ?></strong></div>
        <div>Personel Unik: <strong><?= number_format($stats['personel_unik'] ?? 0, 0, ',', '.'); ?> Orang</strong></div>
        <div>Cocok Master Pegawai: <strong><?= number_format($stats['matched'] ?? 0, 0, ',', '.'); ?></strong></div>
        <div>Non-Master / Eksternal: <strong><?= number_format($stats['unmatched'] ?? 0, 0, ',', '.'); ?></strong></div>
        <div>Total Realisasi Biaya: <strong>Rp <?= number_format($stats['total_biaya'] ?? 0, 0, ',', '.'); ?></strong></div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No.</th>
                <th>Nama Personel &amp; NIP</th>
                <th>Status Master</th>
                <th>Paket Kegiatan &amp; MAK</th>
                <th>Kota Tujuan</th>
                <th>Jadwal &amp; Durasi</th>
                <th>Dasar SPT / SPD</th>
                <th>No. SPM &amp; SP2D</th>
                <th style="width: 90px;">Biaya (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($records)): ?>
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px;">Tidak ada data personel perjadin.</td>
                </tr>
            <?php else: ?>
                <?php $no = 1; foreach ($records as $r):
                    $isMatched  = ((int) ($r['is_matched_master'] ?? 0) === 1);
                    $nipDisplay = ! empty($r['master_nip']) ? $r['master_nip'] : ($r['nip'] ?? '-');
                    $golDisplay = ! empty($r['master_golongan']) ? $r['master_golongan'] : ($r['golongan'] ?? '-');
                    $jabDisplay = ! empty($r['master_jabatan']) ? $r['master_jabatan'] : ($r['jabatan'] ?? '-');
                ?>
                    <tr>
                        <td class="text-center font-bold"><?= $no++; ?></td>
                        <td>
                            <strong><?= esc($r['nama_personel']); ?></strong><br>
                            <span style="color: #475569;">NIP: <?= esc($nipDisplay); ?></span><br>
                            <span style="font-size: 8.5px; color: #64748b;"><?= esc($jabDisplay); ?> (Gol. <?= esc($golDisplay); ?>)</span>
                        </td>
                        <td class="text-center">
                            <?php if ($isMatched): ?>
                                <span class="badge badge-success">Master Pegawai</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Non-Master</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong>Paket <?= (int) ($r['lha_no'] ?? 1); ?></strong> (<?= esc($r['pic_label'] ?? $r['pic_nama'] ?? '-'); ?>)<br>
                            <span style="font-size: 8.5px; color: #475569;">MAK <?= esc($r['kode_akun']); ?></span>
                        </td>
                        <td>
                            <strong><?= esc($r['kota_tujuan']); ?></strong>
                        </td>
                        <td class="text-center">
                            <?= ! empty($r['tanggal_berangkat']) ? date('d/m/Y', strtotime($r['tanggal_berangkat'])) : '-'; ?><br>
                            <span style="font-size: 8.5px; color: #64748b;">(<?= (int) ($r['lama_hari'] ?? 1); ?> Hari)</span>
                        </td>
                        <td>
                            SPT: <?= esc($r['nomor_spt'] ?? '-'); ?><br>
                            SPD: <?= esc($r['nomor_spd'] ?? '-'); ?>
                        </td>
                        <td>
                            SPM: <strong><?= esc($r['nomor_spm'] ?? '-'); ?></strong><br>
                            SP2D: <?= esc($r['nomor_sp2d'] ?? '-'); ?>
                        </td>
                        <td class="text-right font-bold">
                            <?= number_format((float) ($r['nilai_biaya'] ?? 0), 0, ',', '.'); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr style="background-color: #fef3c7; font-weight: bold;">
                <td colspan="8" class="text-right">TOTAL REALISASI BIAYA PERJALANAN DINAS:</td>
                <td class="text-right">
                    Rp <?= number_format((float) ($stats['total_biaya'] ?? 0), 0, ',', '.'); ?>
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="signatures">
        <div class="sig-block">
            Mengetahui,<br>
            <strong>Pejabat Pembuat Komitmen (PPK)</strong>
            <div class="sig-space"></div>
            <strong><u>Nurhidayat Nugroho, S.Ars.</u></strong><br>
            NIP. 19901221 201802 1 001
        </div>
        <div class="sig-block">
            Pekanbaru, <?= date('d F Y'); ?><br>
            <strong>Kepala Satuan Kerja PPS Riau</strong>
            <div class="sig-space"></div>
            <strong><u>Muhammad Yudi Prasetya, S.T.</u></strong><br>
            NIP. 19800626 200604 1 016
        </div>
    </div>
</div>

</body>
</html>
