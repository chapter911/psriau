<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Stock Opname <?= esc($audit['kode_audit']); ?></title>
    <style>
        @page {
            margin: 1.2cm 1.2cm 1.5cm 1.2cm;
            size: A4 portrait;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            line-height: 1.3;
            color: #111827;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #1e3a8a;
            margin-bottom: 15px;
            padding-bottom: 8px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .logo-img {
            max-height: 55px;
            max-width: 110px;
        }
        .header-text {
            text-align: center;
        }
        .header-title-1 {
            font-size: 11pt;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            margin: 0;
        }
        .header-title-2 {
            font-size: 9.5pt;
            font-weight: bold;
            color: #111827;
            margin: 2px 0;
        }
        .header-subtitle {
            font-size: 7.5pt;
            color: #4b5563;
            margin: 0;
        }
        .doc-title {
            text-align: center;
            margin-top: 5px;
            margin-bottom: 12px;
        }
        .doc-title h2 {
            font-size: 11pt;
            font-weight: bold;
            margin: 0 0 3px 0;
            text-transform: uppercase;
            text-decoration: underline;
        }
        .doc-title p {
            font-size: 8.5pt;
            color: #4b5563;
            margin: 0;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .meta-table td {
            font-size: 8pt;
            padding: 2px 4px;
            vertical-align: top;
        }
        .summary-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .summary-box td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            font-size: 8pt;
            text-align: center;
            background-color: #f8fafc;
        }
        .summary-box td strong {
            display: block;
            font-size: 10pt;
            color: #1e3a8a;
            margin-top: 2px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table th, .data-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 5px;
            font-size: 7.5pt;
            vertical-align: middle;
        }
        .data-table th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 6.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-success { background-color: #dcfce7; color: #15803d; }
        .badge-warning { background-color: #fef3c7; color: #b45309; }
        .badge-danger  { background-color: #fee2e2; color: #b91c1c; }
        .badge-info    { background-color: #e0f2fe; color: #0369a1; }
        .badge-gray    { background-color: #f1f5f9; color: #475569; }

        .room-block {
            margin-bottom: 15px;
        }
        .room-banner {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-left: 4px solid #1e3a8a;
            padding: 5px 8px;
            margin-top: 10px;
            margin-bottom: 4px;
        }
        .room-unit-badge {
            display: inline-block;
            background-color: #e2e8f0;
            color: #1e293b;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 6.8pt;
            font-weight: bold;
        }
        .row-subtotal td {
            background-color: #f8fafc;
            font-weight: bold;
            font-size: 7pt;
            border-top: 1px solid #94a3b8;
            padding: 3px 5px;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .signature-table td {
            vertical-align: top;
            font-size: 8pt;
        }
    </style>
</head>
<body>

    <!-- Header Satker -->
    <table class="header-table">
        <tr>
            <td style="width: 15%; text-align: left;">
                <?php if (! empty($logoPuBase64)): ?>
                    <img src="<?= $logoPuBase64; ?>" class="logo-img" alt="Logo PU">
                <?php endif; ?>
            </td>
            <td class="header-text" style="width: 70%;">
                <div class="header-title-1">KEMENTERIAN PEKERJAAN UMUM DAN PERUMAHAN RAKYAT</div>
                <div class="header-title-2">DIREKTORAT JENDERAL CIPTA KARYA</div>
                <div class="header-title-2">BALAI PRASARANA PERMUKIMAN WILAYAH RIAU</div>
                <div class="header-subtitle">SATKER PELAKSANAAN PRASARANA PERMUKIMAN STRATEGIS PROVINSI RIAU</div>
                <div class="header-subtitle">Jl. Arifin Achmad / Komplek Perkantoran PU Pekanbaru - Riau</div>
            </td>
            <td style="width: 15%; text-align: right;"></td>
        </tr>
    </table>

    <!-- Judul Dokumen -->
    <div class="doc-title">
        <h2>BERITA ACARA PEMERIKSAAN FISIK (STOCK OPNAME) ASET BMN</h2>
        <p>Nomor: <?= esc($audit['kode_audit']); ?> / BA-SO / BMN / <?= date('Y', strtotime($audit['tanggal_audit'])); ?></p>
    </div>

    <!-- Metadata Informasi Audit -->
    <table class="meta-table">
        <tr>
            <td style="width: 18%; font-weight: bold;">Judul Kegiatan</td>
            <td style="width: 2%;">:</td>
            <td style="width: 45%;"><?= esc($audit['judul_audit']); ?></td>
            <td style="width: 15%; font-weight: bold;">Tanggal Audit</td>
            <td style="width: 2%;">:</td>
            <td style="width: 18%;"><?= date('d F Y', strtotime($audit['tanggal_audit'])); ?></td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Sasaran / Lingkup</td>
            <td>:</td>
            <td>
                <?php if ($audit['lingkup_audit'] === 'kantor_ruangan'): ?>
                    Ruangan: <strong><?= esc($audit['ruangan_nama'] ?: 'Ruangan Terpilih'); ?></strong>
                <?php elseif ($audit['lingkup_audit'] === 'kantor_seluruh'): ?>
                    Seluruh Aset Kantor Satker
                <?php else: ?>
                    Aset Sarpras / Mobiler Sekolah Binaan
                <?php endif; ?>
            </td>
            <td style="font-weight: bold;">Status Sesi</td>
            <td>:</td>
            <td><strong><?= strtoupper($audit['status']); ?></strong></td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Petugas Pemeriksa</td>
            <td>:</td>
            <td><?= esc($audit['auditor_nama']); ?><?= ! empty($audit['auditor_nip']) ? ' (NIP: ' . esc($audit['auditor_nip']) . ')' : ''; ?></td>
            <td style="font-weight: bold;">Total Sasaran</td>
            <td>:</td>
            <td><strong><?= (int) $audit['total_item']; ?> Unit Aset</strong></td>
        </tr>
    </table>

    <!-- Ringkasan Hasil Pemeriksaan -->
    <table class="summary-box">
        <tr>
            <td style="width: 20%;">
                Sesuai & Ada
                <strong style="color: #15803d;"><?= (int) $audit['total_sesuai']; ?> unit</strong>
            </td>
            <td style="width: 20%;">
                Sedang Dipinjam
                <strong style="color: #b45309;"><?= (int) $audit['total_dipinjam']; ?> unit</strong>
            </td>
            <td style="width: 20%;">
                Kondisi Berubah
                <strong style="color: #0369a1;"><?= (int) $audit['total_berubah']; ?> unit</strong>
            </td>
            <td style="width: 20%;">
                Tidak Ditemukan
                <strong style="color: #b91c1c;"><?= (int) $audit['total_selisih']; ?> unit</strong>
            </td>
            <td style="width: 20%;">
                Belum Diperiksa
                <strong style="color: #64748b;"><?= (int) $audit['total_belum']; ?> unit</strong>
            </td>
        </tr>
    </table>

    <!-- Tabel Rincian Barang per Ruangan -->
    <?php if (! empty($roomStats)): ?>
        <?php foreach ($roomStats as $rs): ?>
            <?php
                $rKey = $rs['ruangan_key'];
                $roomItems = $groupedItems[$rKey] ?? [];
                if (empty($roomItems)) continue;
            ?>
            <div class="room-block">
                <div class="room-banner">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 60%; vertical-align: middle;">
                                <strong style="font-size: 8.5pt; color: #1e3a8a;">RUANGAN: <?= esc(strtoupper($rs['ruangan_nama'])); ?></strong>
                                <?php if (! empty($rs['lokasi_lantai'])): ?>
                                    <span style="font-size: 7.2pt; color: #64748b;">(<?= esc($rs['lokasi_lantai']); ?>)</span>
                                <?php endif; ?>
                                <div style="font-size: 7pt; color: #334155; margin-top: 1px;">
                                    Penanggung Jawab: <strong><?= esc($rs['penanggung_jawab_nama'] ?: 'Petugas Ruangan'); ?></strong>
                                    <?= ! empty($rs['penanggung_jawab_nip']) ? ' (NIP: ' . esc($rs['penanggung_jawab_nip']) . ')' : ''; ?>
                                </div>
                            </td>
                            <td style="width: 40%; text-align: right; vertical-align: middle;">
                                <span class="room-unit-badge">
                                    Target: <?= (int) $rs['total_item']; ?> Unit | Sesuai: <?= (int) $rs['total_sesuai']; ?> | Dipinjam: <?= (int) $rs['total_dipinjam']; ?>
                                    <?php if ($rs['total_selisih'] > 0): ?>
                                        | Selisih: <strong style="color: #b91c1c;"><?= (int) $rs['total_selisih']; ?></strong>
                                    <?php endif; ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 25px;">No</th>
                            <th style="width: 80px;">Kode Barang</th>
                            <th style="width: 40px;">NUP</th>
                            <th>Nama Barang & Spesifikasi</th>
                            <th style="width: 60px;">Kondisi Buku</th>
                            <th style="width: 65px;">Status Pinjam</th>
                            <th style="width: 95px;">Hasil Fisik Lapangan</th>
                            <th style="width: 90px;">Keterangan Temuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($roomItems as $item): ?>
                            <tr>
                                <td style="text-align: center;"><?= $no++; ?></td>
                                <td style="text-align: center; font-family: monospace;"><?= esc($item['kode_barang']); ?></td>
                                <td style="text-align: center; font-weight: bold;"><?= esc($item['nup'] ?: '-'); ?></td>
                                <td>
                                    <strong><?= esc($item['nama_barang']); ?></strong>
                                    <?php if (! empty($item['merk_tipe'])): ?>
                                        <br><span style="color: #4b5563; font-size: 7pt;"><?= esc($item['merk_tipe']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;"><?= esc($item['kondisi_sistem']); ?></td>
                                <td style="text-align: center;">
                                    <?php if ($item['status_pinjam_sistem'] === 'dipinjam'): ?>
                                        <span class="badge badge-warning">Dipinjam</span>
                                    <?php else: ?>
                                        <span class="badge badge-gray">Di Kantor</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($item['status_audit'] === 'sesuai'): ?>
                                        <span class="badge badge-success">Sesuai (Ada)</span>
                                    <?php elseif ($item['status_audit'] === 'terkonfirmasi_dipinjam'): ?>
                                        <span class="badge badge-warning">Dipinjam Sah</span>
                                        <?php if (! empty($item['peminjam_nama'])): ?>
                                            <br><span style="font-size: 6.5pt; color: #b45309;"><?= esc($item['peminjam_nama']); ?></span>
                                        <?php endif; ?>
                                    <?php elseif ($item['status_audit'] === 'kondisi_berubah'): ?>
                                        <span class="badge badge-info">Fisik Berubah</span>
                                        <br><strong style="font-size: 6.5pt; color: #b91c1c;"><?= esc($item['kondisi_fisik']); ?></strong>
                                    <?php elseif ($item['status_audit'] === 'salah_lokasi'): ?>
                                        <span class="badge badge-gray">Pindah Ruang</span>
                                        <br><span style="font-size: 6.5pt; color: #0284c7;"><?= esc($item['ruangan_fisik_nama'] ?: 'Ruang Lain'); ?></span>
                                    <?php elseif ($item['status_audit'] === 'tidak_ditemukan'): ?>
                                        <span class="badge badge-danger">Hilang / Selisih</span>
                                    <?php else: ?>
                                        <span class="badge badge-gray">Belum Dicek</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (! empty($item['catatan_pemeriksaan'])): ?>
                                        <?= esc($item['catatan_pemeriksaan']); ?>
                                    <?php elseif ($item['status_pinjam_sistem'] === 'dipinjam'): ?>
                                        Surat: <?= esc($item['no_surat_pinjam'] ?: '-'); ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="row-subtotal">
                            <td colspan="4" style="text-align: right;">Subtotal Ruangan <?= esc($rs['ruangan_nama']); ?>:</td>
                            <td colspan="4">
                                <strong><?= count($roomItems); ?> Unit Aset</strong>
                                (Sesuai: <?= (int) $rs['total_sesuai']; ?>, Dipinjam: <?= (int) $rs['total_dipinjam']; ?>, Berubah: <?= (int) $rs['total_berubah']; ?>, Selisih: <?= (int) $rs['total_selisih']; ?>, Belum: <?= (int) $rs['total_belum']; ?>)
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25px;">No</th>
                    <th style="width: 80px;">Kode Barang</th>
                    <th style="width: 40px;">NUP</th>
                    <th>Nama Barang & Spesifikasi</th>
                    <th style="width: 80px;">Lokasi Tercatat</th>
                    <th style="width: 60px;">Kondisi Buku</th>
                    <th style="width: 95px;">Hasil Fisik Lapangan</th>
                    <th style="width: 90px;">Keterangan Temuan</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($items as $item): ?>
                    <tr>
                        <td style="text-align: center;"><?= $no++; ?></td>
                        <td style="text-align: center; font-family: monospace;"><?= esc($item['kode_barang']); ?></td>
                        <td style="text-align: center; font-weight: bold;"><?= esc($item['nup'] ?: '-'); ?></td>
                        <td>
                            <strong><?= esc($item['nama_barang']); ?></strong>
                            <?php if (! empty($item['merk_tipe'])): ?>
                                <br><span style="color: #4b5563; font-size: 7pt;"><?= esc($item['merk_tipe']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($item['ruangan_sistem_nama'] ?: ($item['peruntukan'] === 'mobiler' ? 'Sekolah' : 'Kantor')); ?></td>
                        <td style="text-align: center;"><?= esc($item['kondisi_sistem']); ?></td>
                        <td style="text-align: center;">
                            <?php if ($item['status_audit'] === 'sesuai'): ?>
                                <span class="badge badge-success">Sesuai (Ada)</span>
                            <?php elseif ($item['status_audit'] === 'terkonfirmasi_dipinjam'): ?>
                                <span class="badge badge-warning">Dipinjam Sah</span>
                                <?php if (! empty($item['peminjam_nama'])): ?>
                                    <br><span style="font-size: 6.5pt; color: #b45309;"><?= esc($item['peminjam_nama']); ?></span>
                                <?php endif; ?>
                            <?php elseif ($item['status_audit'] === 'kondisi_berubah'): ?>
                                <span class="badge badge-info">Fisik Berubah</span>
                                <br><strong style="font-size: 6.5pt; color: #b91c1c;"><?= esc($item['kondisi_fisik']); ?></strong>
                            <?php elseif ($item['status_audit'] === 'salah_lokasi'): ?>
                                <span class="badge badge-gray">Pindah Ruang</span>
                                <br><span style="font-size: 6.5pt; color: #0284c7;"><?= esc($item['ruangan_fisik_nama'] ?: 'Ruang Lain'); ?></span>
                            <?php elseif ($item['status_audit'] === 'tidak_ditemukan'): ?>
                                <span class="badge badge-danger">Hilang / Selisih</span>
                            <?php else: ?>
                                <span class="badge badge-gray">Belum Dicek</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (! empty($item['catatan_pemeriksaan'])): ?>
                                <?= esc($item['catatan_pemeriksaan']); ?>
                            <?php elseif ($item['status_pinjam_sistem'] === 'dipinjam'): ?>
                                Surat: <?= esc($item['no_surat_pinjam'] ?: '-'); ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Tanda Tangan -->
    <table class="signature-table">
        <tr>
            <td style="width: 50%; text-align: left; padding-left: 20px;">
                Mengetahui,<br>
                <strong>Penanggung Jawab BMN / Ruangan</strong>
                <div style="height: 50px;"></div>
                <strong>( ____________________________ )</strong><br>
                NIP. .....................................................
            </td>
            <td style="width: 50%; text-align: right; padding-right: 20px;">
                Pekanbaru, <?= date('d F Y', strtotime($audit['tanggal_audit'])); ?><br>
                <strong>Petugas Pemeriksa (Auditor)</strong>
                <div style="height: 50px;"></div>
                <strong><u><?= esc($audit['auditor_nama']); ?></u></strong><br>
                <?= ! empty($audit['auditor_nip']) ? 'NIP. ' . esc($audit['auditor_nip']) : 'Petugas Aset Satker'; ?>
            </td>
        </tr>
    </table>

</body>
</html>
