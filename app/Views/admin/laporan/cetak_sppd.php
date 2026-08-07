<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Perjalanan Dinas (SPD)</title>
    <style>
        @page {
            margin: 12mm 15mm 12mm 15mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
            line-height: 1.18;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-weight-bold { font-weight: bold; }
        
        .header-table {
            width: 100%;
            margin-bottom: 4px;
        }
        .header-table td {
            vertical-align: top;
            padding: 1px 0;
        }
        .title {
            text-align: center;
            font-size: 12px;
            text-decoration: underline;
            font-weight: bold;
            margin-bottom: 6px;
        }
        
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .main-table th, .main-table td {
            border: 1px solid #000;
            padding: 2.5px 3.5px;
            vertical-align: top;
        }
        .col-no { width: 4%; text-align: center; }
        .col-label { width: 38%; }
        .col-value { width: 58%; }

        .pengikut-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
        }
        .pengikut-table th, .pengikut-table td {
            border: none;
            padding: 2px;
        }

        .ttd-box {
            float: right;
            width: 45%;
        }
        .ttd-box table {
            width: 100%;
        }
        .ttd-box td {
            vertical-align: top;
            padding: 1px 0;
        }
        .clear { clear: both; }

        .page-break {
            page-break-after: always;
        }
        
        /* Kop Surat */
        .kop-surat-img {
            width: 100%;
            max-height: 90px;
            object-fit: contain;
            margin-bottom: 4px;
        }
    </style>
</head>
<body>

    <?php 
    // Function to format date safely
    $formatDate = function($dateStr) {
        if (empty($dateStr)) return '-';
        $ts = strtotime($dateStr);
        if (!$ts) return $dateStr;
        
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        return date('j', $ts) . ' ' . $months[(int)date('n', $ts)] . ' ' . date('Y', $ts);
    };
    
    $tglBerangkat = $formatDate($row['periode_mulai'] ?? '');
    $tglKembali = $formatDate($row['periode_selesai'] ?? '');
    
    // Calculate Lama Perjalanan
    $lamaPerjalanan = '-';
    if (!empty($row['periode_mulai']) && !empty($row['periode_selesai'])) {
        $start = new \DateTime($row['periode_mulai']);
        $end = new \DateTime($row['periode_selesai']);
        $diff = $start->diff($end);
        $days = $diff->days + 1;
        
        $angkaTeks = [
            1 => 'satu', 2 => 'dua', 3 => 'tiga', 4 => 'empat', 5 => 'lima',
            6 => 'enam', 7 => 'tujuh', 8 => 'delapan', 9 => 'sembilan', 10 => 'sepuluh',
            11 => 'sebelas', 12 => 'dua belas', 13 => 'tiga belas', 14 => 'empat belas', 
            15 => 'lima belas', 16 => 'enam belas', 17 => 'tujuh belas', 18 => 'delapan belas', 
            19 => 'sembilan belas', 20 => 'dua puluh'
        ];
        
        $teks = $angkaTeks[$days] ?? $days;
        $lamaPerjalanan = $days . ' (' . $teks . ') hari';
    }
    
    $nomorSurat = esc($row['nomor_surat_tugas'] ?? '-');
    $kotaTujuan = esc($row['kota_tujuan'] ?? '-');
    $rawMaksud = !empty($row['perihal_disposisi']) ? $row['perihal_disposisi'] : (!empty($row['perihal']) ? $row['perihal'] : ($row['tujuan'] ?? '-'));
    $tujuanMaksud = esc($rawMaksud);
    $tanggalTtd = $formatDate($row['tanggal_tanda_tangan'] ?? date('Y-m-d'));
    
    $alatAngkutan = '';
    if (!empty($row['transportasi'])) {
        $alatAngkutan = $row['transportasi'];
    } else {
        $rincianArr = json_decode((string)($row['rincian_biaya_json'] ?? '{}'), true) ?: [];
        if (!empty($rincianArr['transport']) && is_array($rincianArr['transport'])) {
            $transList = [];
            foreach ($rincianArr['transport'] as $tItem) {
                if (is_array($tItem) && !empty($tItem['nama_transportasi'])) {
                    $transList[] = $tItem['nama_transportasi'];
                }
            }
            if (!empty($transList)) {
                $alatAngkutan = implode(', ', array_unique($transList));
            }
        }
    }
    if (empty($alatAngkutan)) {
        $alatAngkutan = 'Kendaraan Operasional';
    }
    $alatAngkutan = esc($alatAngkutan);
    
    $kopSuratImgUrl = '';
    if (!empty($kop_surat['image_url'])) {
        $path = FCPATH . ltrim($kop_surat['image_url'], '/');
        if (file_exists($path)) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $data = file_get_contents($path);
            $kopSuratImgUrl = 'data:image/' . $ext . ';base64,' . base64_encode($data);
        }
    }
    
    $totalPelaksana = count($pelaksana);
    if ($totalPelaksana === 0) {
        $pelaksana[] = ['nama' => '-', 'nip' => '-', 'jabatan' => '-'];
        $totalPelaksana = 1;
    }
    ?>

    <?php foreach ($pelaksana as $index => $utama): ?>
        <?php
        // Jabatan & Golongan
        $jabatanStr = trim((string)($utama['jabatan'] ?? ''));
        $golonganCode = trim((string)($utama['golongan'] ?? ''));
        
        if ($golonganCode === '') {
            if (preg_match('/^([^\(]+)\((.*?)\)/', $jabatanStr, $matches)) {
                $golonganCode = trim($matches[1]);
            }
        }
        
        if ($golonganCode !== '' && $golonganCode !== '-') {
            $golonganCodeClean = trim(preg_replace('/\s*\(.*?\)/', '', $golonganCode));
            if (preg_match('/(I{1,3}|IV)\/[a-e]/i', $golonganCodeClean, $gMatch)) {
                $golonganDisplay = strtoupper($gMatch[0]);
            } else {
                $golonganDisplay = $golonganCodeClean !== '' ? $golonganCodeClean : '-';
            }
        } else {
            $golonganDisplay = '-';
        }

        $jenisPeg = strtolower(trim((string)($utama['jenis_pegawai'] ?? '')));
        if ($jenisPeg === '' && !empty($utama['id']) && is_numeric($utama['id'])) {
            $dbPeg = \Config\Database::connect();
            if ($dbPeg->tableExists('mst_pegawai')) {
                $pegRow = $dbPeg->table('mst_pegawai')->select('jenis_pegawai')->where('id', (int)$utama['id'])->get()->getRowArray();
                if (!empty($pegRow['jenis_pegawai'])) {
                    $jenisPeg = strtolower(trim((string)$pegRow['jenis_pegawai']));
                }
            }
        }
        $isPPPK = (strpos($jenisPeg, 'pppk') !== false);

        $jabatanFull = $jabatanStr !== '' ? $jabatanStr : 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau';
        if (strpos($jabatanFull, ',') !== false) {
            $jabatanDisplay = trim(explode(',', $jabatanFull)[0]);
        } else {
            $jabatanDisplay = $jabatanFull;
        }

        $tingkatBiaya = 'c'; // Default to c for non-eselon
        $jabUpper = strtoupper($jabatanStr);
        if (strpos($jabUpper, 'ESELON I') !== false) {
            $tingkatBiaya = 'a';
        } elseif (strpos($jabUpper, 'ESELON II') !== false) {
            $tingkatBiaya = 'b';
        } elseif (strpos($jabUpper, 'ESELON III') !== false) {
            $tingkatBiaya = 'b';
        }
        $baseKodeStr = trim((string)($row['kode_nomor'] ?? '1'));
        if (preg_match('/^(\d+)(.*)$/', $baseKodeStr, $m)) {
            $numVal = (int) $m[1] + $index;
            $padLen = max(3, strlen($m[1]));
            $displayKodeNomor = str_pad((string) $numVal, $padLen, '0', STR_PAD_LEFT) . $m[2];
        } else {
            $displayKodeNomor = $baseKodeStr;
        }
        ?>

        <?php if ($kopSuratImgUrl): ?>
            <img src="<?= $kopSuratImgUrl; ?>" class="kop-surat-img" alt="Kop Surat">
        <?php endif; ?>

        <table class="header-table">
            <tr>
                <td style="width: 55%;"></td>
                <td style="width: 45%;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="width: 30%;">Lembar Ke</td>
                            <td style="width: 5%;">:</td>
                            <td style="width: 65%;">1</td>
                        </tr>
                        <tr>
                            <td>Kode Nomor</td>
                            <td>:</td>
                            <td><?= esc($displayKodeNomor); ?></td>
                        </tr>
                        <tr>
                            <td>Nomor</td>
                            <td>:</td>
                            <td><?= str_replace('SPT', 'SPD', $nomorSurat); ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="title">SURAT PERJALANAN DINAS (SPD)</div>

        <table class="main-table">
            <tr>
                <td class="col-no">1.</td>
                <td class="col-label">Pejabat yang Berwenang Memberi Perintah</td>
                <td class="col-value">Pejabat Pembuat Komitmen Pelaksanaan Prasarana Strategis</td>
            </tr>
            <tr>
                <td class="col-no">2.</td>
                <td class="col-label">Nama/NIP Pegawai yang melaksanakan perjalanan dinas</td>
                <td class="col-value"><?= esc($utama['nama']); ?></td>
            </tr>
            <tr>
                <td class="col-no">3.</td>
                <td class="col-label">
                    a. Pangkat dan Golongan<br>
                    b. Jabatan/Instansi<br>
                    c. Tingkat Biaya Perjalanan Dinas
                </td>
                <td class="col-value">
                    <table style="width:100%; border:none; margin:0; padding:0;">
                        <tr><td style="border:none; padding:0; width:5%;">a.</td><td style="border:none; padding:0;"><?= ($isPPPK && $golonganDisplay !== '-') ? 'Golongan ' : ''; ?><?= esc($golonganDisplay); ?></td></tr>
                        <tr><td style="border:none; padding:0;">b.</td><td style="border:none; padding:0;"><?= esc($jabatanDisplay); ?></td></tr>
                        <tr><td style="border:none; padding:0;">c.</td><td style="border:none; padding:0;"><?= esc($tingkatBiaya); ?></td></tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="col-no">4.</td>
                <td class="col-label">Maksud Perjalanan Dinas</td>
                <td class="col-value"><?= $tujuanMaksud; ?></td>
            </tr>
            <tr>
                <td class="col-no">5.</td>
                <td class="col-label">Alat angkutan yang dipergunakan</td>
                <td class="col-value"><?= $alatAngkutan; ?></td>
            </tr>
            <tr>
                <td class="col-no">6.</td>
                <td class="col-label">
                    a. Tempat berangkat<br>
                    b. Tempat tujuan
                </td>
                <td class="col-value">
                    <table style="width:100%; border:none; margin:0; padding:0;">
                        <tr><td style="border:none; padding:0; width:5%;">a.</td><td style="border:none; padding:0;">Pekanbaru</td></tr>
                        <tr><td style="border:none; padding:0;">b.</td><td style="border:none; padding:0;"><?= $kotaTujuan; ?></td></tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="col-no">7.</td>
                <td class="col-label">
                    a. Lamanya Perjalanan Dinas<br>
                    b. Tanggal berangkat<br>
                    c. Tanggal harus kembali/tiba ditempat baru
                </td>
                <td class="col-value">
                    <table style="width:100%; border:none; margin:0; padding:0;">
                        <tr><td style="border:none; padding:0; width:5%;">a.</td><td style="border:none; padding:0;"><?= $lamaPerjalanan; ?></td></tr>
                        <tr><td style="border:none; padding:0;">b.</td><td style="border:none; padding:0;"><?= $tglBerangkat; ?></td></tr>
                        <tr><td style="border:none; padding:0;">c.</td><td style="border:none; padding:0;"><?= $tglKembali; ?></td></tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="col-no">8.</td>
                <td colspan="2" style="padding: 0;">
                    <div style="padding: 6px;">Pengikut :</div>
                    <table class="pengikut-table">
                        <thead>
                            <tr>
                                <th style="width: 5%;">No</th>
                                <th style="width: 45%;">Nama</th>
                                <th style="width: 25%;">Tanggal Lahir</th>
                                <th style="width: 25%;">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center">1.</td><td></td><td></td><td></td>
                            </tr>
                            <tr>
                                <td class="text-center">2.</td><td></td><td></td><td></td>
                            </tr>
                            <tr>
                                <td class="text-center">3.</td><td></td><td></td><td></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="col-no">9.</td>
                <td class="col-label">
                    Pembebanan Anggaran<br>
                    a. Instansi<br>
                    b. Akun
                </td>
                <td class="col-value">
                    <br>
                    <table style="width:100%; border:none; margin:0; padding:0;">
                        <tr><td style="border:none; padding:0; width:5%;">a.</td><td style="border:none; padding:0;">DIPA Satker Pelaksanaan Prasarana Strategis Riau</td></tr>
                        <tr><td style="border:none; padding:0;">b.</td><td style="border:none; padding:0;"><?= esc($mata_anggaran ?? ''); ?></td></tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="col-no">10.</td>
                <td class="col-label">Keterangan lain-lain</td>
                <td class="col-value">Surat Tugas Nomor : <?= $nomorSurat; ?></td>
            </tr>
        </table>

        <div class="ttd-box">
            <table>
                <tr>
                    <td style="width: 30%;">Dikeluarkan di</td>
                    <td style="width: 5%;">:</td>
                    <td style="width: 65%;">Pekanbaru</td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td>:</td>
                    <td><?= $tanggalTtd; ?></td>
                </tr>
            </table>
            
            <div style="margin-top: 10px; text-align: center;">
                Pejabat Berwenang yang Memberi Perintah<br>
                Pejabat Pembuat Komitmen<br>
                Pelaksanaan Prasarana Strategis
                
                <div style="height: 50px;"></div>
                
                <span class="font-weight-bold" style="text-decoration: underline;">NURHIDAYAT NUGROHO, S.Ars.</span>
                <?php if (should_show_nip(['nip' => '19901221 201802 1 001', 'jenis_pegawai' => 'PNS'])): ?>
                    <br>NIP. 19901221 201802 1 001
                <?php endif; ?>
            </div>
        </div>
        <div class="clear"></div>
        
        <?php if ($index < $totalPelaksana - 1): ?>
            <div class="page-break"></div>
        <?php endif; ?>
    <?php endforeach; ?>

</body>
</html>
