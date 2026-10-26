<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= esc($p['judul_surat'] ?? 'Surat Pemeliharaan Aset'); ?> - <?= esc($p['no_surat'] ?? ''); ?></title>
    <style>
        @page {
            margin: 1.8cm 1.8cm 1.8cm 2cm;
            font-family: Arial, Helvetica, sans-serif;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            line-height: 1.35;
            color: #000000;
            margin: 0;
            padding: 0;
        }

        /* Kop Surat */
        .kop-wrapper {
            width: 100%;
            margin-bottom: 12px;
            text-align: center;
        }
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-bottom: 4px;
        }
        .kop-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        .kop-line-thick {
            border-top: 2.5px solid #000000;
            margin-top: 4px;
            margin-bottom: 1px;
        }
        .kop-line-thin {
            border-top: 1px solid #000000;
            margin-bottom: 16px;
        }

        /* Judul Surat */
        .judul-surat {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 2px;
            text-transform: uppercase;
        }
        .nomor-surat {
            text-align: center;
            font-size: 10.5pt;
            font-weight: bold;
            margin-bottom: 18px;
        }

        /* Identitas Pejabat */
        .identitas-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 10pt;
        }
        .identitas-table td {
            border: none;
            padding: 2px 0;
            vertical-align: top;
        }

        /* Tabel Rekapitulasi Barang */
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 12px;
            font-size: 8.5pt;
        }
        .table-data th, .table-data td {
            border: 1px solid #000000;
            padding: 4px 5px;
        }
        .table-data th {
            text-align: center;
            font-weight: bold;
            background-color: #ffffff;
            vertical-align: middle;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        /* Pernyataan & Konsiderans */
        .teks-pernyataan {
            text-align: justify;
            margin-top: 10px;
            margin-bottom: 8px;
            font-size: 10pt;
        }
        .alasan-list {
            margin: 4px 0 10px 0;
            padding-left: 20px;
            font-size: 10pt;
            text-align: justify;
        }
        .alasan-list li {
            margin-bottom: 4px;
            line-height: 1.35;
        }

        /* Tanda Tangan */
        .ttd-wrapper {
            width: 100%;
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .ttd-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }
        .ttd-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }

        /* Halaman Lampiran */
        .page-break {
            page-break-before: always;
        }
        .page-header-lampiran {
            text-align: center;
            font-size: 10pt;
            margin-bottom: 16px;
        }
        .lampiran-title {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 14px;
            letter-spacing: 0.5px;
        }

        .table-lampiran {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
        }
        .table-lampiran th, .table-lampiran td {
            border: 1px solid #000000;
            padding: 4px;
            vertical-align: middle;
        }
        .table-lampiran th {
            text-align: center;
            font-weight: bold;
            background-color: #ffffff;
        }

        .img-lampiran {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border: 1px solid #d1d5db;
            display: block;
            margin: 0 auto;
        }
        .no-photo-box {
            width: 90px;
            height: 70px;
            line-height: 70px;
            text-align: center;
            color: #9ca3af;
            font-size: 7.5pt;
            background: #f9fafb;
            border: 1px dashed #d1d5db;
            margin: 0 auto;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT RESMI -->
    <div class="kop-wrapper">
        <?php if (! empty($kopSuratImg)): ?>
            <?= $kopSuratImg; ?>
        <?php else: ?>
            <table class="kop-table">
                <tr>
                    <td style="width: 80px; text-align: left;">
                        <img src="<?= FCPATH . 'assets/img/logo_pupr.png'; ?>" style="width: 70px; height: auto;" alt="Logo PU">
                    </td>
                    <td style="text-align: center;">
                        <div style="font-size: 13pt; font-weight: bold; letter-spacing: 0.5px;">KEMENTERIAN PEKERJAAN UMUM</div>
                        <div style="font-size: 11pt; font-weight: bold;">DIREKTORAT JENDERAL PRASARANA STRATEGIS</div>
                        <div style="font-size: 11pt; font-weight: bold;">SATUAN KERJA PELAKSANAAN PRASARANA STRATEGIS</div>
                        <div style="font-size: 8pt; margin-top: 3px;">
                            Jalan Datuk Setia Maharaja No. 15 Tangkerang Labuai, Bukit Raya, Riau 28125, email: satkerppsriau@pu.go.id
                        </div>
                    </td>
                </tr>
            </table>
            <div class="kop-line-thick"></div>
            <div class="kop-line-thin"></div>
        <?php endif; ?>
    </div>

    <!-- JUDUL SURAT -->
    <div class="judul-surat">
        <?= esc($p['judul_surat']); ?>
    </div>
    <div class="nomor-surat">
        NOMOR : <?= esc($p['no_surat']); ?>
    </div>

    <!-- IDENTITAS PEJABAT -->
    <div style="margin-bottom: 6px;">Yang bertanda tangan di bawah ini:</div>
    <table class="identitas-table">
        <tr>
            <td style="width: 110px;">Nama</td>
            <td style="width: 15px;">:</td>
            <td class="font-bold"><?= esc($p['nama_kasatker']); ?></td>
        </tr>
        <tr>
            <td>NIP</td>
            <td>:</td>
            <td><?= esc($p['nip_kasatker']); ?></td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>:</td>
            <td><?= esc($p['jabatan_kasatker']); ?></td>
        </tr>
        <tr>
            <td>Unit Kerja</td>
            <td>:</td>
            <td><?= esc($p['unit_kerja']); ?></td>
        </tr>
    </table>

    <div style="margin-bottom: 6px;">
        dengan ini menyatakan bahwa <?= strtolower(esc($p['judul_surat'])); ?> dengan data sebagai berikut:
    </div>

    <!-- TABEL REKAPITULASI BARANG -->
    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 15%;">Jenis Barang</th>
                <th style="width: 13%;">Kode Barang</th>
                <th style="width: 18%;">Nama Barang</th>
                <th style="width: 25%;">Merk</th>
                <th style="width: 9%;">NUP</th>
                <th style="width: 8%;">Kondisi</th>
                <th style="width: 8%;">Tahun Perolehan</th>
            </tr>
        </thead>
        <tbody>
            <?php
                // Pastikan item dikelompokkan berdasarkan 4 kriteria:
                // 1. Jenis Barang
                // 2. Kode Barang
                // 3. Nama Barang
                // 4. Merk
                $tableItems = [];
                $tableGroups = [];

                foreach ($items ?? [] as $it) {
                    $jenis = clean_inventaris_text((string) ($it['jenis_barang'] ?? 'MESIN PERALATAN NON TIK'));
                    if ($jenis === '') {
                        $jenis = 'MESIN PERALATAN NON TIK';
                    }
                    $kode = trim((string) ($it['kode_barang'] ?? ''));
                    $nama = clean_inventaris_text((string) ($it['nama_barang'] ?? ''));
                    $merk = clean_inventaris_text((string) ($it['merk_tipe'] ?? ''));

                    $gKey = mb_strtolower($jenis) . '::' . mb_strtolower($kode) . '::' . mb_strtolower($nama) . '::' . mb_strtolower($merk);

                    if (! isset($tableGroups[$gKey])) {
                        $tableGroups[$gKey] = [
                            'jenis_barang'    => $jenis,
                            'kode_barang'     => $kode,
                            'nama_barang'     => $nama,
                            'merk_tipe'       => $merk ?: '-',
                            'nups'            => [],
                            'kondisi'         => [],
                            'tahun_perolehan' => [],
                        ];
                    }

                    if (! empty($it['nup'])) {
                        $tableGroups[$gKey]['nups'][] = $it['nup'];
                    }
                    if (! empty($it['kondisi'])) {
                        $tableGroups[$gKey]['kondisi'][] = trim((string) $it['kondisi']);
                    }
                    if (! empty($it['tahun_perolehan'])) {
                        $tableGroups[$gKey]['tahun_perolehan'][] = trim((string) $it['tahun_perolehan']);
                    }
                }

                foreach ($tableGroups as $tg) {
                    $nupStr = format_nup_range_summary($tg['nups'], '-');
                    $nupStr = preg_replace('/\s*[-–—]\s*/', '-', $nupStr);

                    $kondisiUniq = array_values(array_filter(array_unique($tg['kondisi'])));
                    $kondisiStr  = ! empty($kondisiUniq) ? implode(', ', $kondisiUniq) : 'Baik';

                    $tahunUniq = array_values(array_filter(array_unique($tg['tahun_perolehan'])));
                    $tahunStr  = ! empty($tahunUniq) ? implode(', ', $tahunUniq) : '-';

                    $tableItems[] = [
                        'jenis_barang'    => $tg['jenis_barang'],
                        'kode_barang'     => $tg['kode_barang'],
                        'nama_barang'     => $tg['nama_barang'],
                        'merk_tipe'       => $tg['merk_tipe'],
                        'nup'             => $nupStr ?: '-',
                        'kondisi'         => $kondisiStr,
                        'tahun_perolehan' => $tahunStr,
                    ];
                }

                // Urutkan data tabel rekapitulasi surat resmi berdasarkan: 1. Nama Barang ASC, 2. NUP ASC (numerik)
                usort($tableItems, function ($a, $b) {
                    $cmpNama = strcasecmp(trim((string) ($a['nama_barang'] ?? '')), trim((string) ($b['nama_barang'] ?? '')));
                    if ($cmpNama !== 0) {
                        return $cmpNama;
                    }

                    $numA = preg_match('/\d+/', (string) ($a['nup'] ?? ''), $mA) ? (int) $mA[0] : 0;
                    $numB = preg_match('/\d+/', (string) ($b['nup'] ?? ''), $mB) ? (int) $mB[0] : 0;

                    if ($numA !== $numB) {
                        return $numA <=> $numB;
                    }

                    $cmpNup = strnatcasecmp((string) ($a['nup'] ?? ''), (string) ($b['nup'] ?? ''));
                    if ($cmpNup !== 0) {
                        return $cmpNup;
                    }

                    $cmpMerk = strcasecmp(trim((string) ($a['merk_tipe'] ?? '')), trim((string) ($b['merk_tipe'] ?? '')));
                    if ($cmpMerk !== 0) {
                        return $cmpMerk;
                    }

                    return strcasecmp(trim((string) ($a['kode_barang'] ?? '')), trim((string) ($b['kode_barang'] ?? '')));
                });
            ?>
            <?php if (empty($tableItems)): ?>
                <tr>
                    <td colspan="8" class="text-center" style="padding: 15px;">Tidak ada data barang.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($tableItems as $idx => $it): ?>
                    <tr>
                        <td class="text-center"><?= $idx + 1; ?></td>
                        <td class="text-center" style="font-size: 8pt;"><?= esc($it['jenis_barang'] ?: 'MESIN PERALATAN NON TIK'); ?></td>
                        <td class="text-center font-bold"><?= esc($it['kode_barang']); ?></td>
                        <td><?= esc($it['nama_barang']); ?></td>
                        <td><?= esc($it['merk_tipe'] ?: '-'); ?></td>
                        <td class="text-center font-bold"><?= esc($it['nup']); ?></td>
                        <td class="text-center"><?= esc($it['kondisi'] ?: 'Baik'); ?></td>
                        <td class="text-center"><?= esc($it['tahun_perolehan'] ?: '-'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- KONSIDERANS & PERNYATAAN BMN -->
    <div class="teks-pernyataan">
        merupakan Barang Milik Negara (BMN) yang tercatat pada unit kerja kami.
    </div>

    <div class="teks-pernyataan" style="margin-top: 6px;">
        Sehubungan dengan peralatan & mesin tersebut, dengan ini kami menyatakan bahwa:
    </div>

    <div style="margin-left: 5px; font-size: 10pt; line-height: 1.35;">
        <div style="margin-bottom: 4px;">
            a. <?= esc($p['alasan_poin_a'] ?: 'BMN masih dalam kondisi baik dan masih layak untuk digunakan;'); ?>
        </div>
        <div style="margin-bottom: 4px;">
            b. BMN masih perlu dipertahankan dan dipelihara, dengan alasan teknis sebagai berikut:
        </div>
        <?php
            $alasanRaw = (string) ($p['alasan_poin_b'] ?? '');
            $lines = array_filter(array_map('trim', explode("\n", $alasanRaw)));
        ?>
        <ol class="alasan-list">
            <?php if (! empty($lines)): ?>
                <?php foreach ($lines as $ln): ?>
                    <?php $cleanLn = preg_replace('/^\d+[\.\)]\s*/', '', $ln); ?>
                    <li><?= esc($cleanLn); ?></li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>Memiliki spesifikasi dan fungsi yang masih sesuai dengan kebutuhan operasional unit kerja/masih digunakan secara rutin dan memiliki peran penting dalam mendukung kelancaran kegiatan operasional unit kerja.</li>
                <li>Pemeliharaan rutin memastikan aset tetap dalam kondisi prima (siap pakai) untuk mendukung kelancaran pelayanan publik dan tugas fungsi pemerintah tanpa kendala teknis.</li>
                <li>Secara teknis, perawatan berkala jauh lebih murah dan efisien dibanding mendanai perbaikan berat atau penggantian total akibat kerusakan fatal (kerusakan struktural/sistem).</li>
            <?php endif; ?>
        </ol>
    </div>

    <div class="teks-pernyataan">
        Bahwa peralatan & mesin tersebut masih digunakan untuk mendukung kelancaran pelaksanaan tugas dan fungsi unit kerja sehingga diperlukan pemeliharaan terhadap BMN dimaksud.
    </div>
    <div class="teks-pernyataan" style="margin-bottom: 20px;">
        Demikian Surat Pernyataan ini dibuat dengan sebenar-benarnya untuk dapat dipergunakan sebagaimana mestinya.
    </div>

    <!-- TANDA TANGAN KASATKER -->
    <div class="ttd-wrapper">
        <table class="ttd-table">
            <tr>
                <td style="width: 52%;"></td>
                <td style="width: 48%; text-align: left;">
                    <div><?= esc($p['tempat_surat'] ?: 'Pekanbaru'); ?>, <?= esc($tglIndo); ?></div>
                    <div>Kepala Satuan Kerja Pelaksanaan</div>
                    <div>Prasarana Strategis Riau</div>
                    <div style="height: 65px; margin-top: 10px; margin-bottom: 10px;">
                        <!-- Ruang tanda tangan / meterai / stempel -->
                    </div>
                    <div class="font-bold" style="text-decoration: underline; font-size: 10.5pt;">
                        <?= esc($p['nama_kasatker']); ?>
                    </div>
                    <div>NIP. <?= esc($p['nip_kasatker']); ?></div>
                </td>
            </tr>
        </table>
    </div>

    <!-- HALAMAN LAMPIRAN FOTO 4 SUDUT -->
    <div class="page-break"></div>

    <div class="page-header-lampiran">
        -3-
    </div>

    <div class="lampiran-title">
        LAMPIRAN SURAT
    </div>

    <table class="table-lampiran">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 18%;">Nama Barang</th>
                <th style="width: 12%;">NUP</th>
                <th style="width: 16.5%;">Foto Tampak Depan</th>
                <th style="width: 16.5%;">Foto Tampak Atas</th>
                <th style="width: 16.5%;">Foto Tampak Bawah</th>
                <th style="width: 16.5%;">Foto Tampak Belakang</th>
            </tr>
        </thead>
        <tbody>
            <?php
                if (! empty($fotos)) {
                    // Urutkan lampiran foto berdasarkan: 1. Nama Barang ASC, 2. NUP ASC (numerik)
                    usort($fotos, function ($a, $b) {
                        $cmpNama = strcasecmp(trim((string) ($a['nama_barang'] ?? '')), trim((string) ($b['nama_barang'] ?? '')));
                        if ($cmpNama !== 0) {
                            return $cmpNama;
                        }

                        $numA = preg_match('/\d+/', (string) ($a['nup_gabungan'] ?? ''), $mA) ? (int) $mA[0] : 0;
                        $numB = preg_match('/\d+/', (string) ($b['nup_gabungan'] ?? ''), $mB) ? (int) $mB[0] : 0;

                        if ($numA !== $numB) {
                            return $numA <=> $numB;
                        }

                        return strnatcasecmp((string) ($a['nup_gabungan'] ?? ''), (string) ($b['nup_gabungan'] ?? ''));
                    });
                }
            ?>
            <?php if (empty($fotos)): ?>
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px;">Belum ada lampiran foto yang diunggah.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($fotos as $fIdx => $f): ?>
                    <tr>
                        <td class="text-center"><?= $fIdx + 1; ?></td>
                        <td>
                            <div class="font-bold"><?= esc($f['nama_barang']); ?></div>
                        </td>
                        <td class="text-center font-bold">
                            <?= esc(preg_replace('/\s*[-–—]\s*/', '-', (string) ($f['nup_gabungan'] ?: '-'))); ?>
                        </td>
                        <!-- Foto Depan -->
                        <td class="text-center">
                            <?php if (! empty($f['foto_depan_base64'])): ?>
                                <img src="<?= $f['foto_depan_base64']; ?>" class="img-lampiran" alt="Tampak Depan">
                            <?php else: ?>
                                <div class="no-photo-box">Tidak Ada Foto</div>
                            <?php endif; ?>
                        </td>
                        <!-- Foto Atas -->
                        <td class="text-center">
                            <?php if (! empty($f['foto_atas_base64'])): ?>
                                <img src="<?= $f['foto_atas_base64']; ?>" class="img-lampiran" alt="Tampak Atas">
                            <?php else: ?>
                                <div class="no-photo-box">Tidak Ada Foto</div>
                            <?php endif; ?>
                        </td>
                        <!-- Foto Bawah -->
                        <td class="text-center">
                            <?php if (! empty($f['foto_bawah_base64'])): ?>
                                <img src="<?= $f['foto_bawah_base64']; ?>" class="img-lampiran" alt="Tampak Bawah">
                            <?php else: ?>
                                <div class="no-photo-box">Tidak Ada Foto</div>
                            <?php endif; ?>
                        </td>
                        <!-- Foto Belakang -->
                        <td class="text-center">
                            <?php if (! empty($f['foto_belakang_base64'])): ?>
                                <img src="<?= $f['foto_belakang_base64']; ?>" class="img-lampiran" alt="Tampak Belakang">
                            <?php else: ?>
                                <div class="no-photo-box">Tidak Ada Foto</div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>
