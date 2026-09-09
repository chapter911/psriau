<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>DBR - <?= esc($room['kode_ruangan']); ?> - <?= esc($room['nama_ruangan']); ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0.8cm 1.2cm 0.8cm 1.2cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8pt;
            line-height: 1.3;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }

        /* Header Instansi Biasa (Bukan Kop Surat) */
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
            line-height: 1.25;
        }
        .instansi-text-2 {
            font-size: 9pt;
            font-weight: bold;
            color: #1e293b;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            line-height: 1.25;
        }
        .instansi-text-3 {
            font-size: 8.5pt;
            font-weight: bold;
            color: #1e3a8a;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            line-height: 1.25;
        }
        .header-divider {
            border-top: 1.5px solid #1e3a8a;
            margin: 4px 0 6px 0;
            width: 100%;
        }

        /* Judul Dokumen */
        .doc-header {
            text-align: center;
            margin-bottom: 6px;
        }
        .doc-title {
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 1px;
            color: #0f172a;
            text-transform: uppercase;
        }

        /* Info Box Ruangan */
        .info-box {
            width: 100%;
            border-collapse: collapse;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-left: 3.5px solid #1e3a8a;
            margin-bottom: 8px;
        }
        .info-box td {
            padding: 3px 6px;
            font-size: 7.5pt;
            vertical-align: middle;
        }
        .info-label {
            width: 16%;
            font-weight: bold;
            color: #334155;
            text-transform: uppercase;
            font-size: 7pt;
        }
        .info-sep {
            width: 2%;
            text-align: center;
            color: #64748b;
        }
        .info-val {
            width: 32%;
            color: #0f172a;
        }

        /* Tabel Data Barang BMN */
        .table-dbr {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
            margin-bottom: 0px;
        }
        .table-dbr th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7pt;
            letter-spacing: 0.3px;
            padding: 4.5px 4px;
            border: 1px solid #1e3a8a;
            text-align: center;
        }
        .table-dbr td {
            padding: 3.5px 5px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }
        .table-dbr tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        /* Badge & Utility Tags */
        .badge-psp {
            display: inline-block;
            background-color: #f8fafc;
            color: #334155;
            font-size: 6pt;
            font-weight: bold;
            padding: 1px 4px;
            border: 0.5px solid #cbd5e1;
            border-radius: 2px;
            margin-top: 1px;
        }
        .badge-kondisi-b {
            font-weight: bold;
            color: #166534;
            background-color: #dcfce7;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 6.5pt;
            display: inline-block;
        }
        .badge-kondisi-rr {
            font-weight: bold;
            color: #854d0e;
            background-color: #fef9c3;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 6.5pt;
            display: inline-block;
        }
        .badge-kondisi-rb {
            font-weight: bold;
            color: #991b1b;
            background-color: #fee2e2;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 6.5pt;
            display: inline-block;
        }

        /* Alignment Utilities */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }

        /* Total Row */
        .row-total td {
            background-color: #f1f5f9;
            font-weight: bold;
            border-top: 1.5px solid #1e3a8a;
            color: #0f172a;
            padding: 4px 5px;
        }

        /* Catatan Regulasi Kedinasan BMN */
        .catatan-card {
            border: 1px solid #e2e8f0;
            border-left: 3px solid #d97706;
            background-color: #fffbeb;
            padding: 4px 8px;
            margin-top: 6px;
            font-size: 6.5pt;
            color: #78350f;
            line-height: 1.25;
        }
        .catatan-card strong {
            color: #92400e;
        }

        /* Blok Tanda Tangan */
        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
            font-size: 8pt;
            page-break-inside: avoid;
        }
        .sig-table td {
            vertical-align: top;
            width: 50%;
        }
        .sig-box {
            padding: 0 10px;
        }
        .sig-role {
            font-size: 7.5pt;
            color: #475569;
            margin-bottom: 1px;
        }
        .sig-title {
            font-weight: bold;
            color: #0f172a;
            font-size: 8pt;
        }
        .sig-space {
            height: 42px;
        }
        .sig-name {
            font-weight: bold;
            color: #0f172a;
            font-size: 8pt;
            text-decoration: underline;
        }
        .sig-nip {
            font-size: 7.5pt;
            color: #334155;
            margin-top: 1px;
        }

        /* Footer Halaman */
        .footer-note {
            margin-top: 12px;
            border-top: 0.5px solid #cbd5e1;
            padding-top: 3px;
            font-size: 6pt;
            color: #64748b;
            width: 100%;
        }
    </style>
</head>
<body>

    <!-- Header Instansi Biasa (Bukan Seperti Surat) -->
    <div class="header-instansi">
        <div class="instansi-text-1">KEMENTERIAN PEKERJAAN UMUM</div>
        <div class="instansi-text-2">DIREKTORAT JENDERAL PRASARANA STRATEGIS</div>
        <div class="instansi-text-3">SATUAN KERJA PELAKSANAAN PRASARANA STRATEGIS PROVINSI RIAU</div>
    </div>
    <div class="header-divider"></div>

    <!-- Judul Dokumen (Tanpa Nomor & Tahun Anggaran) -->
    <div class="doc-header">
        <div class="doc-title">DAFTAR BARANG RUANGAN (DBR)</div>
    </div>

    <!-- Kotak Metadata Ruangan & UAKPB (Tanpa Penanggung Jawab di Atas) -->
    <table class="info-box">
        <tr>
            <td class="info-label">Unit Akuntansi (UAKPB)</td>
            <td class="info-sep">:</td>
            <td class="info-val font-bold"><?= esc($namaUakpb ?? 'PELAKSANAAN PRASARANA STRATEGIS PROVINSI RIAU'); ?></td>
            
            <td class="info-label">Nama Ruangan</td>
            <td class="info-sep">:</td>
            <td class="info-val font-bold" style="color: #1e3a8a;"><?= esc(strtoupper($room['nama_ruangan'] ?? '-')); ?></td>
        </tr>
        <tr>
            <td class="info-label">Kode UAKPB</td>
            <td class="info-sep">:</td>
            <td class="info-val font-mono"><?= esc($kodeUakpb ?? '145060900691285000KP'); ?></td>
            
            <td class="info-label">Kode Ruangan</td>
            <td class="info-sep">:</td>
            <td class="info-val font-mono font-bold"><?= esc(strtoupper($room['kode_ruangan'] ?? '-')); ?></td>
        </tr>
        <?php if (! empty($room['lokasi_gedung']) || ! empty($room['lantai']) || ! empty($room['lokasi_ruangan'])): ?>
        <tr>
            <td class="info-label">Lokasi Fisik</td>
            <td class="info-sep">:</td>
            <td class="info-val" colspan="4">
                <?= esc(! empty($room['lokasi_gedung']) ? $room['lokasi_gedung'] : (! empty($room['lokasi_ruangan']) ? $room['lokasi_ruangan'] : 'Gedung Kantor')); ?> 
                <?= ! empty($room['lantai']) ? (' &bull; Lantai ' . esc($room['lantai'])) : ''; ?>
            </td>
        </tr>
        <?php endif; ?>
    </table>

    <!-- Tabel Daftar Barang Ruangan (Compact, Tanpa NUP & Nilai Perolehan) -->
    <table class="table-dbr">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 18%;">Kode Barang</th>
                <th style="width: 32%;">Nama Barang</th>
                <th style="width: 26%;">Merk / Type</th>
                <th style="width: 6%;">Tahun</th>
                <th style="width: 8%;">Kondisi</th>
                <th style="width: 6%;">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items)): ?>
                <tr>
                    <td colspan="7" class="text-center" style="padding: 16px; color: #64748b; font-style: italic;">
                        - Belum ada Barang Milik Negara (BMN) yang dialokasikan pada ruangan ini -
                    </td>
                </tr>
            <?php else: ?>
                <?php $no = 1; foreach ($items as $item): ?>
                    <?php 
                        $kondisiRaw = strtolower(trim((string) ($item['kondisi'] ?? 'baik')));
                        $kondisiBadgeClass = 'badge-kondisi-b';
                        $kondisiText = 'Baik';
                        if (str_contains($kondisiRaw, 'berat')) {
                            $kondisiBadgeClass = 'badge-kondisi-rb';
                            $kondisiText = 'Rusak Berat';
                        } elseif (str_contains($kondisiRaw, 'ringan')) {
                            $kondisiBadgeClass = 'badge-kondisi-rr';
                            $kondisiText = 'Rusak Ringan';
                        }

                        $noPsp = trim((string) ($item['no_psp'] ?? ''));
                        $satuan = ! empty($item['satuan']) ? $item['satuan'] : 'Buah';
                    ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td class="text-center font-mono font-bold" style="color: #1e293b;"><?= esc($item['kode_barang']); ?></td>
                        <td>
                            <div class="font-bold" style="color: #0f172a;"><?= esc($item['nama_barang']); ?></div>
                            <?php if ($noPsp !== ''): ?>
                                <div class="badge-psp">PSP: <?= esc($noPsp); ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= esc(! empty($item['merk_tipe']) ? $item['merk_tipe'] : '-'); ?></td>
                        <td class="text-center"><?= esc(! empty($item['tahun_perolehan']) ? $item['tahun_perolehan'] : '-'); ?></td>
                        <td class="text-center">
                            <span class="<?= $kondisiBadgeClass; ?>"><?= $kondisiText; ?></span>
                        </td>
                        <td class="text-center font-bold" style="color: #1e3a8a;">
                            <?= number_format((int) ($item['total_jumlah'] ?? 1)); ?> <?= esc($satuan); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr class="row-total">
                    <td colspan="6" class="text-center">TOTAL KESELURUHAN BARANG RUANGAN</td>
                    <td class="text-center font-bold" style="font-size: 8.5pt; color: #1e3a8a;"><?= number_format((int) ($totalUnit ?? 0)); ?> Unit</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Catatan Regulasi Pengelolaan BMN -->
    <div class="catatan-card">
        <strong>KETENTUAN PENGELOLAAN BARANG MILIK NEGARA (BMN):</strong><br>
        Barang Milik Negara yang tercantum dalam Daftar Barang Ruangan (DBR) ini dilarang dipindahkan, dialihkan, atau dipinjamkan tanpa persetujuan tertulis dari Pejabat Penatausahaan Pengguna Barang / UAKPB dan Penanggung Jawab Ruangan. Pemeliharaan dan keutuhan fisik barang sepenuhnya berada di bawah pengawasan penanggung jawab ruangan.
    </div>

    <!-- Blok Pengesahan / Tanda Tangan -->
    <table class="sig-table">
        <tr>
            <td style="padding-left: 20px;">
                <div class="sig-box">
                    <div class="sig-role">Mengetahui,</div>
                    <div class="sig-title">Penanggung Jawab UAKPB</div>
                    <div class="sig-role"><?= esc($kasatker['jabatan'] ?? 'Kepala Kuasa Pengguna Barang'); ?></div>
                    <div class="sig-space"></div>
                    <div class="sig-name"><?= esc($kasatker['nama'] ?? 'Muhammad Yudi Prasetya, S.T.'); ?></div>
                    <div class="sig-nip">NIP. <?= esc($kasatker['nip'] ?? '198002142014121002'); ?></div>
                </div>
            </td>
            <td style="padding-left: 40px;">
                <div class="sig-box">
                    <div class="sig-role">Pekanbaru, <?= esc($tglPenetapan ?? date('d F Y')); ?></div>
                    <div class="sig-title">Penanggung Jawab Ruangan</div>
                    <div class="sig-role"><?= esc($room['nama_ruangan'] ?? 'Ruangan'); ?></div>
                    <div class="sig-space"></div>
                    <?php if (! empty($room['penanggung_jawab_nama'])): ?>
                        <div class="sig-name"><?= esc($room['penanggung_jawab_nama']); ?></div>
                        <div class="sig-nip"><?= ! empty($room['penanggung_jawab_nip']) ? ('NIP. ' . esc($room['penanggung_jawab_nip'])) : '-'; ?></div>
                    <?php else: ?>
                        <div class="sig-name">( .................................................... )</div>
                        <div class="sig-nip">NIP. .............................................</div>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
    </table>

    <!-- Footer Catatan Sistem & Keabsahan Dokumen -->
    <table class="footer-note">
        <tr>
            <td style="text-align: left; width: 65%;">
                Sistem Informasi Inventaris & BMN &bull; Satuan Kerja Pelaksanaan Prasarana Strategis Provinsi Riau &bull; Dicetak pada: <?= esc($waktuCetak ?? (date('d/m/Y H:i') . ' WIB')); ?>
            </td>
            <td style="text-align: right; width: 35%;">
                Dokumen Resmi Kedinasan PU
            </td>
        </tr>
    </table>

    <!-- Script Canvas Dompdf: Kotak Paraf Kecil di Pojok Bawah Halaman jika Dokumen Terdiri dari Beberapa Halaman -->
    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) {
                // Hanya aktif jika dokumen terdiri dari beberapa halaman (> 1 halaman)
                if ($pageCount > 1) {
                    $pageW = $canvas->get_width();
                    $pageH = $canvas->get_height();

                    // 1. Teks nomor halaman dinamis di pojok kanan footer
                    $fontNormal = $fontMetrics->get_font("Helvetica", "normal");
                    $pageText = "Halaman " . $pageNumber . " dari " . $pageCount;
                    $canvas->text($pageW - 95, $pageH - 20, $pageText, $fontNormal, 6.5, array(0.4, 0.45, 0.5));

                    // 2. Kotak Paraf Kecil Penanggung Jawab Ruangan di Pojok Bawah
                    // Tampil pada setiap halaman sebelum halaman tanda tangan terakhir
                    if ($pageNumber < $pageCount) {
                        $boxW = 115;
                        $boxH = 36;
                        $headerH = 11;
                        $x = $pageW - 34 - $boxW;
                        $y = $pageH - 25 - $boxH;

                        // Background header kotak paraf
                        $canvas->filled_rectangle($x, $y, $boxW, $headerH, array(0.95, 0.96, 0.98));
                        // Background area paraf putih
                        $canvas->filled_rectangle($x, $y + $headerH, $boxW, $boxH - $headerH, array(1.0, 1.0, 1.0));

                        // Garis tepi & pemisah header
                        $canvas->rectangle($x, $y, $boxW, $boxH, array(0.45, 0.52, 0.6), 0.7);
                        $canvas->line($x, $y + $headerH, $x + $boxW, $y + $headerH, array(0.45, 0.52, 0.6), 0.7);

                        // Teks label header kotak paraf
                        $fontBold = $fontMetrics->get_font("Helvetica", "bold");
                        $canvas->text($x + 10, $y + 2.5, "PARAF PENANGGUNG JAWAB", $fontBold, 5.8, array(0.12, 0.18, 0.26));
                    }
                }
            });
        }
    </script>
</body>
</html>


