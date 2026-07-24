<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Perjalanan Dinas (SPD)</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11.5px;
            margin: 0;
            padding: 0;
            line-height: 1.3;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-weight-bold { font-weight: bold; }
        
        .header-table {
            width: 100%;
            margin-bottom: 10px;
        }
        .header-table td {
            vertical-align: top;
        }
        .title {
            text-align: center;
            font-size: 14px;
            text-decoration: underline;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .main-table th, .main-table td {
            border: 1px solid #000;
            padding: 4px;
            vertical-align: top;
        }
        .col-no { width: 4%; text-align: center; }
        .col-label { width: 38%; }
        .col-value { width: 58%; }

        .pengikut-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        .pengikut-table th, .pengikut-table td {
            border: none;
            padding: 4px;
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
        }
        .clear { clear: both; }

        .page-break {
            page-break-after: always;
        }
        
        /* Kop Surat */
        .kop-surat-img {
            width: 100%;
            max-height: 120px;
            object-fit: contain;
            margin-bottom: 10px;
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
    $tujuanMaksud = esc($row['tujuan'] ?? '-');
    $tanggalTtd = $formatDate($row['tanggal_tanda_tangan'] ?? date('Y-m-d'));
    
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
        // Jabatan & Pangkat
        $jabatanStr = trim((string)($utama['jabatan'] ?? ''));
        $golongan = '';
        $pangkat = '';
        
        if (preg_match('/^([^\(]+)\((.*?)\)/', $jabatanStr, $matches)) {
            $golongan = trim($matches[1]);
            $pangkat = trim($matches[2]);
        } else {
            $golongan = $jabatanStr;
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
                            <td></td>
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
                <td class="col-value">Pejabat Pembuat Komitmen<br>Pelaksanaan Prasarana Strategis</td>
            </tr>
            <tr>
                <td class="col-no">2.</td>
                <td class="col-label">Nama/NIP Pegawai yang melaksanakan perjalanan dinas</td>
                <td class="col-value"><?= esc($utama['nama']); ?><br>NIP. <?= esc($utama['nip']); ?></td>
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
                        <tr><td style="border:none; padding:0; width:5%;">a.</td><td style="border:none; padding:0;"><?= esc($pangkat); ?> (<?= esc($golongan); ?>)</td></tr>
                        <tr><td style="border:none; padding:0;">b.</td><td style="border:none; padding:0;">Pejabat Pembuat Komitmen Pelaksanaan Prasarana Strategis Riau</td></tr>
                        <tr><td style="border:none; padding:0;">c.</td><td style="border:none; padding:0;"><?= $tingkatBiaya; ?></td></tr>
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
                <td class="col-value">Kendaraan Operasional</td>
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
            
            <div style="margin-top: 10px;">
                Pejabat Berwenang yang Memberi Perintah<br>
                Pejabat Pembuat Komitmen<br>
                Pelaksanaan Prasarana Strategis
                
                <div style="height: 50px;"></div>
                
                <span class="font-weight-bold" style="text-decoration: underline;">NURHIDAYAT NUGROHO, S.Ars.</span><br>
                NIP. 19901221 201802 1 001
            </div>
        </div>
        <div class="clear"></div>
        
        <?php if ($index < $totalPelaksana - 1): ?>
            <div class="page-break"></div>
        <?php endif; ?>
    <?php endforeach; ?>

</body>
</html>
