<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>DBR Seluruh Ruangan Kantor - Satker PPS Riau</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1cm 1.2cm 1cm 1.2cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 7.5pt;
            line-height: 1.25;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }

        .doc-header {
            text-align: center;
            margin-bottom: 6px;
        }
        .doc-title {
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            color: #0f172a;
            text-transform: uppercase;
        }
        .doc-subtitle {
            font-size: 7.5pt;
            color: #475569;
            margin-top: 2px;
            font-weight: 600;
        }

        .info-strip {
            width: 100%;
            border-collapse: collapse;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-left: 3.5px solid #1e3a8a;
            margin-bottom: 12px;
        }
        .info-strip td {
            padding: 3.5px 6px;
            font-size: 7pt;
            vertical-align: middle;
        }

        /* Room Block */
        .room-block {
            margin-bottom: 14px;
        }
        .room-block.avoid-break {
            page-break-inside: avoid;
        }

        /* Room Header Banner */
        .room-banner {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-left: 3.5px solid #1e3a8a;
            border-bottom: none;
            padding: 4px 6px;
            page-break-after: avoid;
        }
        .room-banner-table {
            width: 100%;
            border-collapse: collapse;
        }
        .room-banner-table td {
            padding: 0;
            vertical-align: middle;
        }
        .room-title-text {
            font-size: 8pt;
            font-weight: bold;
            color: #0f172a;
        }
        .room-code-badge {
            background-color: #1e3a8a;
            color: #ffffff;
            font-size: 6.5pt;
            font-family: 'Courier New', Courier, monospace;
            padding: 1px 4px;
            border-radius: 2px;
            font-weight: bold;
            margin-right: 3px;
        }
        .room-floor-text {
            color: #64748b;
            font-size: 7pt;
            font-weight: normal;
        }
        .room-pj-text {
            font-size: 6.8pt;
            color: #334155;
            margin-top: 2px;
        }
        .room-unit-badge {
            display: inline-block;
            background-color: #dcfce7;
            color: #166534;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 6.8pt;
            font-weight: bold;
        }

        /* Table DBR */
        .table-dbr {
            width: 100%;
            border-collapse: collapse;
            font-size: 6.8pt;
            page-break-inside: auto;
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
            letter-spacing: 0.2px;
            padding: 3.5px 3px;
            border: 0.5px solid #334155;
            text-align: center;
        }
        .table-dbr td {
            padding: 2.5px 3.5px;
            border: 0.5px solid #cbd5e1;
            vertical-align: middle;
        }
        .table-dbr tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .row-subtotal td {
            background-color: #f8fafc;
            font-weight: bold;
            font-size: 6.5pt;
            border-top: 1px solid #94a3b8;
            padding: 3px 4px;
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

        /* Grand Total Box */
        .grand-total-box {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-left: 3.5px solid #166534;
            padding: 5px 8px;
            font-size: 7.5pt;
            font-weight: bold;
            color: #0f172a;
            margin-top: 10px;
            margin-bottom: 8px;
            page-break-inside: avoid;
        }

        /* Signatures */
        .sig-section {
            margin-top: 14px;
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

    <!-- Judul Dokumen (Tanpa Kop) -->
    <div class="doc-header">
        <div class="doc-title">DAFTAR BARANG RUANGAN (DBR) - SELURUH RUANGAN KANTOR</div>
        <div class="doc-subtitle">PENATAUSAHAAN ASET FISIK BMN SECARA INDIVIDUAL PER NOMOR URUT PENDAFTARAN (NUP)</div>
    </div>

    <?php
    // Persiapkan grouping ruangan jika belum disediakan controller
    if (empty($groupedRooms) && ! empty($items)) {
        $groupedRooms = [];
        foreach ($items as $it) {
            $roomId = (int) ($it['ruangan_id'] ?? 0);
            if (! isset($groupedRooms[$roomId])) {
                $groupedRooms[$roomId] = [
                    'ruangan_id'            => $roomId,
                    'kode_ruangan'          => (string) ($it['kode_ruangan'] ?? ''),
                    'nama_ruangan'          => (string) ($it['nama_ruangan'] ?? ''),
                    'lokasi_lantai'         => (string) ($it['lokasi_lantai'] ?? ''),
                    'penanggung_jawab_nama' => (string) ($it['penanggung_jawab_nama'] ?? ''),
                    'penanggung_jawab_nip'  => (string) ($it['penanggung_jawab_nip'] ?? ''),
                    'items'                 => [],
                ];
            }
            $groupedRooms[$roomId]['items'][] = $it;
        }
    }
    $totalRuanganCount = is_array($groupedRooms) ? count($groupedRooms) : 0;
    ?>

    <!-- Info Strip Ringkas (Tanpa Nilai Perolehan) -->
    <table class="info-strip">
        <tr>
            <td style="width: 13%; font-weight: bold; color: #334155;">UAKPB</td>
            <td style="width: 1%;">:</td>
            <td style="width: 46%; font-weight: bold; color: #0f172a;">145060900691285000KP — PELAKSANAAN PRASARANA STRATEGIS RIAU</td>
            <td style="width: 13%; font-weight: bold; color: #334155;">TGL CETAK</td>
            <td style="width: 1%;">:</td>
            <td style="width: 26%; color: #0f172a;"><?= esc($tglPenetapan); ?> (<?= esc($waktuCetak); ?>)</td>
        </tr>
        <tr>
            <td style="font-weight: bold; color: #334155;">TOTAL ASET</td>
            <td>:</td>
            <td style="font-weight: bold; color: #166534;"><?= number_format((int) ($totalUnit ?? 0)); ?> Unit Fisik Terdaftar</td>
            <td style="font-weight: bold; color: #334155;">TOTAL RUANGAN</td>
            <td>:</td>
            <td style="color: #0f172a; font-weight: bold;"><?= number_format($totalRuanganCount); ?> Ruangan Kantor Terdata</td>
        </tr>
    </table>

    <!-- Kotak Tabel Terpisah Per Ruangan -->
    <?php if (empty($groupedRooms)): ?>
        <div style="text-align: center; padding: 25px; color: #64748b; font-style: italic; border: 1px dashed #cbd5e1; border-radius: 4px;">
            Belum ada barang inventaris kantor yang dialokasikan ke ruangan.
        </div>
    <?php else: ?>
        <?php foreach ($groupedRooms as $room): ?>
            <?php
                $itemCount = count($room['items'] ?? []);
                // Jika isi barang ruangan <= 18 baris, aktifkan avoid-break agar satu kotak ruangan utuh
                // tidak terpotong canggung (tanggung) di dasar halaman, melainkan berpindah rapi ke halaman berikutnya.
                $avoidBreakClass = ($itemCount <= 18) ? 'avoid-break' : '';
            ?>
            <div class="room-block <?= $avoidBreakClass; ?>">
                <!-- Header Banner Ruangan (Judul Ruangan, Lantai & Penanggung Jawab) -->
                <div class="room-banner">
                    <table class="room-banner-table">
                        <tr>
                            <td>
                                <span class="room-code-badge"><?= esc($room['kode_ruangan']); ?></span>
                                <span class="room-title-text"><?= esc(strtoupper($room['nama_ruangan'])); ?></span>
                                <?php if (! empty($room['lokasi_lantai'])): ?>
                                    <span class="room-floor-text">&bull; <?= esc($room['lokasi_lantai']); ?></span>
                                <?php endif; ?>
                                <div class="room-pj-text">
                                    Penanggung Jawab: <strong><?= esc(! empty($room['penanggung_jawab_nama']) ? $room['penanggung_jawab_nama'] : '-'); ?></strong>
                                    <?php if (! empty($room['penanggung_jawab_nip'])): ?>
                                        <span class="font-mono text-muted">(NIP. <?= esc($room['penanggung_jawab_nip']); ?>)</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td style="text-align: right; width: 110px; vertical-align: top;">
                                <span class="room-unit-badge">
                                    <?= number_format($itemCount); ?> Unit Aset
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Tabel Barang Ruangan (7 Kolom: Tanpa Kolom Nama Ruangan/Lantai, Tanpa Kode Register, Tanpa Nilai Perolehan) -->
                <table class="table-dbr">
                    <thead>
                        <tr>
                            <th style="width: 26px;">NO</th>
                            <th style="width: 82px;">KODE BARANG</th>
                            <th style="width: 42px;">NUP</th>
                            <th>NAMA BARANG</th>
                            <th style="width: 135px;">MERK / TIPE</th>
                            <th style="width: 55px;">KONDISI</th>
                            <th style="width: 42px;">TAHUN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($room['items'])): ?>
                            <tr>
                                <td colspan="7" class="text-center" style="padding: 10px; color: #64748b; font-style: italic;">
                                    Belum ada aset fisik BMN yang dialokasikan pada ruangan ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($room['items'] as $it): ?>
                                <tr>
                                    <td class="text-center font-bold" style="color: #64748b;"><?= $no++; ?></td>
                                    <td class="text-center font-bold font-mono" style="color: #1e3a8a;"><?= esc($it['kode_barang']); ?></td>
                                    <td class="text-center font-bold font-mono" style="background-color: #f1f5f9;"><?= esc((string) $it['nup']); ?></td>
                                    <td class="font-bold" style="color: #0f172a;"><?= esc($it['nama_barang']); ?></td>
                                    <td style="color: #475569;"><?= esc(! empty($it['merk_tipe']) ? $it['merk_tipe'] : '-'); ?></td>
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
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="row-subtotal">
                            <td colspan="2" class="text-center font-bold">SUBTOTAL <?= esc($room['kode_ruangan']); ?></td>
                            <td class="text-center font-bold font-mono" style="color: #166534;"><?= number_format($itemCount); ?></td>
                            <td colspan="4" style="color: #64748b; font-size: 6.5pt;">Unit Fisik Terdata di Ruangan Ini</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Ringkasan Total Keseluruhan Dokumen -->
    <div class="grand-total-box">
        TOTAL KESELURUHAN: <?= number_format((int) ($totalUnit ?? 0)); ?> Unit Fisik Aset BMN Terdata di <?= number_format($totalRuanganCount); ?> Ruangan Kantor
    </div>

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
