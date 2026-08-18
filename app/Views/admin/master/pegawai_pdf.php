<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Pegawai - Satker PPS Riau</title>
    <style>
        @page {
            margin: 12mm 10mm 12mm 10mm;
            size: A4 landscape;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            color: #1a202c;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }

        .header-wrapper {
            width: 100%;
            margin-bottom: 12px;
            text-align: center;
        }

        .kop-container {
            width: 100%;
            text-align: center;
            margin-bottom: 8px;
        }

        .kop-container img {
            max-width: 100%;
            max-height: 85px;
            object-fit: contain;
        }

        .doc-header-title {
            text-align: center;
            margin-bottom: 10px;
        }

        .doc-title {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 3px 0;
            color: #0f172a;
        }

        .doc-subtitle {
            font-size: 9.5pt;
            font-weight: 600;
            text-transform: uppercase;
            margin: 0 0 2px 0;
            color: #334155;
        }

        .doc-agency {
            font-size: 8.5pt;
            color: #64748b;
            margin: 0;
        }

        .header-divider {
            border: none;
            border-top: 2px solid #0f172a;
            border-bottom: 0.5px solid #0f172a;
            height: 3px;
            margin: 8px 0 10px 0;
        }

        .meta-bar {
            width: 100%;
            margin-bottom: 10px;
            font-size: 8pt;
            color: #475569;
        }

        .meta-bar table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-bar td {
            padding: 0;
            vertical-align: middle;
        }

        .filter-badge-list {
            display: inline-block;
            font-weight: bold;
            color: #1e40af;
        }

        .table-pegawai {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }

        .table-pegawai thead th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            border: 1px solid #1e3a8a;
            padding: 6px 4px;
            text-transform: uppercase;
            font-size: 7.8pt;
        }

        .table-pegawai thead tr.numbering-row th {
            background-color: #3b82f6;
            color: #eff6ff;
            font-size: 7pt;
            padding: 2px 4px;
            font-weight: normal;
            border: 1px solid #2563eb;
        }

        .table-pegawai tbody td {
            border: 1px solid #cbd5e1;
            padding: 5px 4px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .table-pegawai tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        .photo-box {
            width: 42px;
            height: 52px;
            margin: 0 auto;
            border-radius: 3px;
            overflow: hidden;
            border: 1px solid #94a3b8;
            background-color: #f1f5f9;
            text-align: center;
        }

        .photo-box img {
            width: 42px;
            height: 52px;
            object-fit: cover;
            display: block;
        }

        .photo-placeholder {
            width: 42px;
            height: 52px;
            line-height: 52px;
            font-size: 6.5pt;
            color: #94a3b8;
            text-align: center;
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            margin: 0 auto;
            border-radius: 3px;
        }

        .badge-status-active {
            font-weight: bold;
            color: #166534;
        }

        .badge-status-inactive {
            font-weight: bold;
            color: #991b1b;
        }

        .badge-jp {
            font-weight: 600;
            color: #0f172a;
        }

        .footer-note {
            margin-top: 10px;
            width: 100%;
            font-size: 7.5pt;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
        }

        .footer-note table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-note td {
            padding: 0;
        }
    </style>
</head>
<body>

    <div class="header-wrapper">
        <?php if (! empty($kop_surat_src)): ?>
            <div class="kop-container">
                <img src="<?= esc($kop_surat_src); ?>" alt="Kop Surat">
            </div>
        <?php else: ?>
            <div class="doc-header-title">
                <div class="doc-title">DAFTAR PEGAWAI</div>
                <div class="doc-subtitle">SATUAN KERJA PELAKSANAAN PRASARANA STRATEGIS RIAU</div>
                <div class="doc-agency">DIREKTORAT JENDERAL PRASARANA STRATEGIS &bull; KEMENTERIAN PEKERJAAN UMUM</div>
            </div>
            <hr class="header-divider">
        <?php endif; ?>
    </div>

    <?php if (! empty($kop_surat_src)): ?>
        <div class="doc-header-title" style="margin-top: 4px; margin-bottom: 8px;">
            <div class="doc-title" style="font-size: 11pt;">DAFTAR PEGAWAI</div>
        </div>
    <?php endif; ?>

    <div class="meta-bar">
        <table>
            <tr>
                <td class="text-left">
                    <strong>Total Data:</strong> <?= count($items ?? []); ?> Pegawai
                    <?php if (! empty($filter_summary)): ?>
                        &nbsp;|&nbsp; <strong>Filter:</strong> <span class="filter-badge-list"><?= esc($filter_summary); ?></span>
                    <?php endif; ?>
                </td>
                <td class="text-right">
                    <strong>Dicetak pada:</strong> <?= date('d/m/Y H:i'); ?> WIB
                </td>
            </tr>
        </table>
    </div>

    <table class="table-pegawai">
        <thead>
            <tr>
                <th style="width: 24px;">NO</th>
                <th style="width: 50px;">FOTO</th>
                <th style="width: 110px;">NIP</th>
                <th style="width: 140px;">NAMA / EMAIL</th>
                <th style="width: 150px;">JABATAN (FUNGSIONAL / PELAKSANA)</th>
                <th style="width: 130px;">JABATAN (PERBENDAHARAAN)</th>
                <th style="width: 75px;">JENIS</th>
                <th style="width: 50px;">ESELON</th>
                <th style="width: 45px;">GOL</th>
                <th style="width: 55px;">MASA KERJA</th>
                <th style="width: 45px;">STATUS</th>
            </tr>
            <tr class="numbering-row">
                <th>1</th>
                <th>2</th>
                <th>3</th>
                <th>4</th>
                <th>5</th>
                <th>6</th>
                <th>7</th>
                <th>8</th>
                <th>9</th>
                <th>10</th>
                <th>11</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items)): ?>
                <tr>
                    <td colspan="11" class="text-center" style="padding: 20px; color: #64748b;">
                        Tidak ada data pegawai yang sesuai.
                    </td>
                </tr>
            <?php else: ?>
                <?php $no = 1; foreach ($items as $item): ?>
                    <?php
                        $isActive = (int) ($item['is_active'] ?? 1) === 1;
                        $jpRaw = strtolower(trim((string) ($item['jenis_pegawai'] ?? 'pns')));
                        $isAsn = in_array($jpRaw, ['pns', 'cpns', 'pppk'], true);
                        $displayNip = $isAsn ? trim((string) ($item['nip'] ?? '')) : '';
                        $jpLabel = match ($jpRaw) {
                            'konsultan' => 'Konsultan Individual',
                            'security' => 'Security',
                            'cleaning_service' => 'Cleaning Service',
                            'ppnpn' => 'PPNPN',
                            'pppk' => 'PPPK',
                            'cpns' => 'CPNS',
                            'lainnya' => 'Lainnya',
                            default => strtoupper($jpRaw),
                        };
                        $fotoBase64 = $item['foto_base64'] ?? null;
                    ?>
                    <tr>
                        <td class="text-center font-bold"><?= $no++; ?></td>
                        <td class="text-center">
                            <?php if (! empty($fotoBase64)): ?>
                                <div class="photo-box">
                                    <img src="<?= esc($fotoBase64); ?>" alt="Foto">
                                </div>
                            <?php else: ?>
                                <div class="photo-placeholder">No Foto</div>
                            <?php endif; ?>
                        </td>
                        <td class="text-center font-bold" style="font-size: 7.5pt; letter-spacing: 0.2px;">
                            <?= esc($displayNip !== '' ? $displayNip : '-'); ?>
                        </td>
                        <td>
                            <div class="font-bold" style="color: #0f172a; font-size: 8.2pt;"><?= esc((string) ($item['nama'] ?? '-')); ?></div>
                            <?php if (! empty($item['email'])): ?>
                                <div style="font-size: 7pt; color: #64748b; margin-top: 2px; word-break: break-all;"><?= esc((string) $item['email']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= esc((string) ($item['jabatan_utama_label'] ?? '-')); ?></td>
                        <td><?= esc((string) ($item['jabatan_perbendaharaan_label'] ?? '-')); ?></td>
                        <td class="text-center badge-jp"><?= esc($jpLabel); ?></td>
                        <td class="text-center"><?= esc((string) ($item['eselon'] ?? '-')); ?></td>
                        <td class="text-center"><?= esc((string) ($item['golongan'] ?? '-')); ?></td>
                        <td class="text-center"><?= esc((string) ($item['masa_kerja'] ?? '-')); ?></td>
                        <td class="text-center">
                            <?php if ($isActive): ?>
                                <span class="badge-status-active">Aktif</span>
                            <?php else: ?>
                                <span class="badge-status-inactive">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer-note">
        <table>
            <tr>
                <td class="text-left">
                    Sistem Informasi Manajemen Satuan Kerja Pelaksanaan Prasarana Strategis Riau
                </td>
                <td class="text-right">
                    Dokumen ini digenerate secara otomatis oleh sistem.
                </td>
            </tr>
        </table>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
            $size = 7.5;
            $text = "Halaman {PAGE_NUM} dari {PAGE_COUNT}";
            $pdf->page_text(745, 570, $text, $font, $size, array(0.4, 0.4, 0.4));
        }
    </script>
</body>
</html>
