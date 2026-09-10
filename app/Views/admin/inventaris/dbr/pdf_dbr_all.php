<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>DBR Seluruh Ruangan Kantor - Satker PPS Riau</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0.9cm 1.2cm 1cm 1.2cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 7.5pt;
            line-height: 1.25;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }

        .header-instansi {
            text-align: center;
            margin-bottom: 2px;
            width: 100%;
        }
        .instansi-text-1 {
            font-size: 9.5pt;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            line-height: 1.2;
        }
        .instansi-text-2 {
            font-size: 9pt;
            font-weight: bold;
            color: #1e293b;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            line-height: 1.2;
        }
        .instansi-text-3 {
            font-size: 8.5pt;
            font-weight: bold;
            color: #1e3a8a;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            line-height: 1.2;
        }
        .header-divider {
            border-top: 1.5px solid #1e3a8a;
            margin: 4px 0 6px 0;
            width: 100%;
        }

        .doc-header {
            text-align: center;
            margin-bottom: 6px;
        }
        .doc-title {
            font-size: 10.5pt;
            font-weight: bold;
            letter-spacing: 0.8px;
            color: #0f172a;
            text-transform: uppercase;
        }
        .doc-subtitle {
            font-size: 7.5pt;
            color: #475569;
            margin-top: 1px;
            font-weight: 600;
        }

        .info-strip {
            width: 100%;
            border-collapse: collapse;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-left: 3.5px solid #1e3a8a;
            margin-bottom: 8px;
        }
        .info-strip td {
            padding: 3px 6px;
            font-size: 7pt;
            vertical-align: middle;
        }

        .table-dbr {
            width: 100%;
            border-collapse: collapse;
            font-size: 7pt;
            margin-bottom: 6px;
        }
        .table-dbr thead {
            display: table-header-group;
        }
        .table-dbr tr {
            page-break-inside: avoid;
        }
        .table-dbr th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 6.5pt;
            letter-spacing: 0.3px;
            padding: 4.5px 3px;
            border: 0.5px solid #334155;
            text-align: center;
        }
        .table-dbr td {
            padding: 3px 4px;
            border: 0.5px solid #cbd5e1;
            vertical-align: middle;
        }
        .table-dbr tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .badge-kondisi-b {
            font-weight: bold;
            color: #166534;
            background-color: #dcfce7;
            padding: 1px 3px;
            border-radius: 2px;
            font-size: 6pt;
            display: inline-block;
        }
        .badge-kondisi-rr {
            font-weight: bold;
            color: #854d0e;
            background-color: #fef9c3;
            padding: 1px 3px;
            border-radius: 2px;
            font-size: 6pt;
            display: inline-block;
        }
        .badge-kondisi-rb {
            font-weight: bold;
            color: #991b1b;
            background-color: #fee2e2;
            padding: 1px 3px;
            border-radius: 2px;
            font-size: 6pt;
            display: inline-block;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }

        .row-total td {
            background-color: #f1f5f9;
            font-weight: bold;
            border-top: 1.5px solid #1e3a8a;
            color: #0f172a;
            padding: 4px;
        }

        .sig-section {
            margin-top: 12px;
            page-break-inside: avoid;
            width: 100%;
        }
        .sig-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
        }
        .sig-table td {
            vertical-align: top;
            padding: 2px 8px;
        }
    </style>
</head>
<body>

    <!-- Header Instansi -->
    <?php if (! empty($kopSuratImg)): ?>
        <div style="text-align: center; margin-bottom: 4px;">
            <?= $kopSuratImg; ?>
        </div>
    <?php else: ?>
        <table class="header-instansi" style="border-collapse: collapse;">
            <tr>
                <?php if (! empty($logoBase64)): ?>
                    <td style="width: 48px; vertical-align: middle; text-align: center; padding-right: 6px;">
                        <img src="<?= $logoBase64; ?>" style="height: 42px; width: auto;" alt="Logo PU">
                    </td>
                <?php endif; ?>
                <td style="text-align: center; vertical-align: middle;">
                    <div class="instansi-text-1">KEMENTERIAN PEKERJAAN UMUM</div>
                    <div class="instansi-text-2">DIREKTORAT JENDERAL PRASARANA STRATEGIS</div>
                    <div class="instansi-text-3">SATUAN KERJA PELAKSANAAN PRASARANA STRATEGIS RIAU</div>
                </td>
            </tr>
        </table>
        <div class="header-divider"></div>
    <?php endif; ?>

    <!-- Judul Dokumen -->
    <div class="doc-header">
        <div class="doc-title">DAFTAR BARANG RUANGAN (DBR) - SELURUH RUANGAN KANTOR</div>
        <div class="doc-subtitle">PENATAUSAHAAN ASET FISIK BMN SECARA INDIVIDUAL PER NOMOR URUT PENDAFTARAN (NUP)</div>
    </div>

    <!-- Info Strip Ringkas -->
    <table class="info-strip">
        <tr>
            <td style="width: 12%; font-weight: bold; color: #334155;">UAKPB</td>
            <td style="width: 1%;">:</td>
            <td style="width: 37%; font-weight: bold; color: #0f172a;">145060900691285000KP — PELAKSANAAN PRASARANA STRATEGIS RIAU</td>
            <td style="width: 12%; font-weight: bold; color: #334155;">TGL CETAK</td>
            <td style="width: 1%;">:</td>
            <td style="width: 37%; color: #0f172a;"><?= esc($tglPenetapan); ?> (<?= esc($waktuCetak); ?>)</td>
        </tr>
        <tr>
            <td style="font-weight: bold; color: #334155;">TOTAL ASET</td>
            <td>:</td>
            <td style="font-weight: bold; color: #166534;"><?= number_format((int) ($totalUnit ?? 0)); ?> Unit Fisik Terdaftar</td>
            <td style="font-weight: bold; color: #334155;">NILAI PEROLEHAN</td>
            <td>:</td>
            <td style="font-weight: bold; color: #0f172a;">Rp <?= number_format((float) ($totalNilai ?? 0), 0, ',', '.'); ?></td>
        </tr>
    </table>

    <!-- Tabel Data Barang BMN -->
    <table class="table-dbr">
        <thead>
            <tr>
                <th style="width: 25px;">NO</th>
                <th style="width: 65px;">KODE RUANGAN</th>
                <th style="width: 110px;">NAMA RUANGAN & LANTAI</th>
                <th style="width: 105px;">PENANGGUNG JAWAB</th>
                <th style="width: 75px;">KODE BARANG</th>
                <th style="width: 40px;">NUP</th>
                <th style="width: 95px;">KODE REGISTER (SIMAN)</th>
                <th>NAMA BARANG</th>
                <th style="width: 100px;">MERK / TIPE</th>
                <th style="width: 55px;">KONDISI</th>
                <th style="width: 40px;">TAHUN</th>
                <th style="width: 80px;">NILAI PEROLEHAN</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items)): ?>
                <tr>
                    <td colspan="12" class="text-center" style="padding: 18px; color: #64748b;">
                        Belum ada barang inventaris kantor yang dialokasikan ke ruangan.
                    </td>
                </tr>
            <?php else: ?>
                <?php $no = 1; foreach ($items as $it): ?>
                    <tr>
                        <td class="text-center font-bold" style="color: #64748b;"><?= $no++; ?></td>
                        <td class="text-center font-bold font-mono" style="color: #1e3a8a;"><?= esc($it['kode_ruangan']); ?></td>
                        <td>
                            <span class="font-bold" style="color: #0f172a;"><?= esc($it['nama_ruangan']); ?></span>
                            <?php if (! empty($it['lokasi_lantai'])): ?>
                                <br><small style="color: #64748b;"><?= esc($it['lokasi_lantai']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= esc($it['penanggung_jawab_nama'] ?: '-'); ?>
                            <?php if (! empty($it['penanggung_jawab_nip'])): ?>
                                <br><small class="font-mono" style="color: #64748b;">NIP. <?= esc($it['penanggung_jawab_nip']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center font-bold font-mono"><?= esc($it['kode_barang']); ?></td>
                        <td class="text-center font-bold font-mono" style="background-color: #f1f5f9;"><?= esc((string) $it['nup']); ?></td>
                        <td class="text-center font-mono" style="font-size: 6pt; color: #475569;">
                            <?= esc($it['kode_register'] ?: '-'); ?>
                        </td>
                        <td class="font-bold" style="color: #0f172a;"><?= esc($it['nama_barang']); ?></td>
                        <td style="color: #475569;"><?= esc($it['merk_tipe'] ?: '-'); ?></td>
                        <td class="text-center">
                            <?php if ($it['kondisi'] === 'baik'): ?>
                                <span class="badge-kondisi-b">Baik</span>
                            <?php elseif ($it['kondisi'] === 'rusak_ringan'): ?>
                                <span class="badge-kondisi-rr">R. Ringan</span>
                            <?php else: ?>
                                <span class="badge-kondisi-rb">R. Berat</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center font-mono"><?= esc((string) ($it['tahun_perolehan'] ?: '-')); ?></td>
                        <td class="text-right font-bold font-mono">
                            Rp <?= number_format((float) ($it['nilai_perolehan'] ?? 0), 0, ',', '.'); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="row-total">
                <td colspan="5" class="text-center font-bold">TOTAL KESELURUHAN</td>
                <td class="text-center font-bold font-mono" style="color: #166534;"><?= number_format((int) ($totalUnit ?? 0)); ?></td>
                <td colspan="5" class="text-center font-bold" style="color: #64748b;">Unit Fisik Aset Terdata</td>
                <td class="text-right font-bold font-mono" style="color: #166534;">
                    Rp <?= number_format((float) ($totalNilai ?? 0), 0, ',', '.'); ?>
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Blok Tanda Tangan Resmi Kedinasan -->
    <div class="sig-section">
        <table class="sig-table">
            <tr>
                <td style="width: 50%; text-align: center;">
                    Mengetahui,<br>
                    <strong>Kepala Satuan Kerja / Kuasa Pengguna Barang</strong>
                    <div style="height: 48px;"></div>
                    <strong style="text-decoration: underline;"><?= esc($kasatker['nama'] ?? 'Muhammad Yudi Prasetya, S.T.'); ?></strong><br>
                    <span>NIP. <?= esc($kasatker['nip'] ?? '198002142014121002'); ?></span>
                </td>
                <td style="width: 50%; text-align: center;">
                    Pekanbaru, <?= esc($tglPenetapan); ?><br>
                    <strong>Petugas Penatausahaan BMN Satker</strong>
                    <div style="height: 48px;"></div>
                    <strong style="text-decoration: underline;">( .................................................... )</strong><br>
                    <span>NIP. ....................................................</span>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
