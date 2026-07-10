<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Permohonan Izin / Pemberitahuan Lupa Absen</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #000;
            padding: 10px 40px;
        }

        .header {
            text-align: center;
            margin-bottom: 5px;
        }

        .divider {
            border-bottom: 3px double #000;
            margin: 5px 0 15px;
        }

        .title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            margin-top: 10px;
            text-transform: uppercase;
        }

        .nomor {
            text-align: center;
            font-size: 11pt;
            margin-bottom: 25px;
        }

        .introduction {
            margin-bottom: 15px;
        }

        table.biodata {
            width: 100%;
            margin-left: 20px;
            margin-bottom: 25px;
            border-collapse: collapse;
        }

        table.biodata td {
            padding: 4px 0;
            vertical-align: top;
        }

        table.biodata td.label {
            width: 150px;
        }

        table.biodata td.colon {
            width: 20px;
            text-align: center;
        }

        .content-paragraph {
            text-align: justify;
            text-indent: 0px;
            margin-bottom: 15px;
            line-height: 1.8;
        }

        .strike {
            text-decoration: line-through;
        }

        .reason-section {
            margin-top: 15px;
            margin-bottom: 35px;
        }

        .reason-line {
            border-bottom: 1px dotted #000;
            min-height: 28px;
            padding-left: 10px;
            line-height: 28px;
            font-style: italic;
        }

        .signature-section {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }

        .signature-section td {
            width: 50%;
            vertical-align: top;
            text-align: center;
            padding: 5px;
        }

        .signature-date {
            margin-bottom: 5px;
            text-align: center;
        }

        .signature-title {
            margin-bottom: 65px;
            text-align: center;
        }

        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            display: inline-block;
        }

        .catatan-section {
            margin-top: 50px;
            page-break-inside: avoid;
        }

        .catatan-title {
            font-weight: bold;
            margin-bottom: 8px;
        }

        .footer-note {
            margin-top: 40px;
            font-size: 10pt;
        }

        @page {
            size: A4;
            margin: 20mm 18mm;
        }
    </style>
</head>
<body>

    <!-- Header / Kop Surat -->
    <div class="header">
        <?php if (function_exists('kop_surat_img_tag') && kop_surat_img_tag('', '', 'Kop Surat', $kop_surat_id ?? null) !== ''): ?>
            <?= kop_surat_img_tag('', 'width: 100%; max-height: 110px; object-fit: contain;', 'Kop Surat', $kop_surat_id ?? null); ?>
        <?php else: ?>
            <div style="font-size: 14pt; font-weight: bold; text-transform: uppercase;">
                <?= esc($app_name ?? 'KEMENTERIAN PEKERJAAN UMUM'); ?>
            </div>
            <div style="font-size: 12pt; font-weight: bold;">
                <?= esc($official_name ?? 'Satuan Kerja Prasarana Strategis Riau'); ?>
            </div>
            <div class="divider"></div>
        <?php endif; ?>
    </div>

    <!-- Title & Nomor -->
    <div class="title">
        SURAT PERMOHONAN IZIN/PEMBERITAHUAN
    </div>
    <div class="nomor">
        Nomor : <?= esc($nomor_surat ?? '............................................'); ?>
    </div>

    <!-- Content -->
    <div class="introduction">
        Yang bertandatangan dibawah ini:
    </div>

    <table class="biodata">
        <tr>
            <td class="label">Nama</td>
            <td class="colon">:</td>
            <td><strong><?= esc($nama ?? ''); ?></strong></td>
        </tr>
        <tr>
            <td class="label">NIP</td>
            <td class="colon">:</td>
            <td><?= esc($nip ?? ''); ?></td>
        </tr>
        <tr>
            <td class="label">Pangkat/Gol</td>
            <td class="colon">:</td>
            <td><?= esc(getPangkatGolongan($golongan ?? '')); ?></td>
        </tr>
        <tr>
            <td class="label">Jabatan</td>
            <td class="colon">:</td>
            <td><?= esc($jabatan ?? ''); ?></td>
        </tr>
    </table>

    <?php
    // Classify reasons for strikethrough check
    $isTerlambatMasuk = false;
    $isPulangAwal = false;
    $isLupaMasuk = false;
    $isLupaPulang = false;

    $jenisLower = strtolower($jenis_absen ?? '');
    $alasanLower = strtolower($alasan_detail ?? '');

    if ($jenisLower === 'masuk') {
        if (strpos($alasanLower, 'terlambat') !== false) {
            $isTerlambatMasuk = true;
        } else {
            $isLupaMasuk = true;
        }
    } else if ($jenisLower === 'pulang') {
        if (strpos($alasanLower, 'terlambat') !== false || strpos($alasanLower, 'pulang sebelum') !== false || strpos($alasanLower, 'cepat') !== false || strpos($alasanLower, 'awal') !== false) {
            $isPulangAwal = true;
        } else {
            $isLupaPulang = true;
        }
    }
    ?>

    <div class="content-paragraph">
        Dengan ini menerangkan bahwa pada hari <strong><?= getHariIndonesia($tanggal_absen ?? ''); ?></strong> tanggal <strong><?= formatTanggalIndonesia($tanggal_absen ?? ''); ?></strong>, saya 
        <span class="<?= $isTerlambatMasuk ? '' : 'strike'; ?>">terlambat masuk kerja</span> / 
        <span class="<?= $isPulangAwal ? '' : 'strike'; ?>">pulang sebelum waktunya</span> / 
        <span class="<?= $isLupaMasuk ? '' : 'strike'; ?>">tidak mengisi daftar hadir kedatangan</span> / 
        <span class="<?= $isLupaPulang ? '' : 'strike'; ?>">tidak mengisi daftar hadir kepulangan</span>*) karena:
    </div>

    <!-- Detail Alasan dengan Garis Dotted -->
    <div class="reason-section">
        <div class="reason-line"><?= esc($alasan_detail ?? ''); ?></div>
        <div class="reason-line">&nbsp;</div>
        <div class="reason-line">&nbsp;</div>
    </div>

    <!-- Tanda Tangan -->
    <table class="signature-section">
        <tr>
            <td style="vertical-align: top;">
                <div class="signature-date" style="visibility: hidden;">&nbsp;</div>
                <div class="signature-title" style="margin-bottom: 0;">Pegawai yang bersangkutan,</div>
            </td>
            <td style="vertical-align: top;">
                <div class="signature-date">
                    Pekanbaru, <?= formatTanggalIndonesia($tanggal_surat ?? date('Y-m-d')); ?>
                </div>
                <div class="signature-title" style="margin-bottom: 0;">
                    Disetujui/tidak disetujui *) oleh<br>
                    Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau
                </div>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 70px; vertical-align: bottom;">
                <div class="signature-name"><?= esc($nama ?? ''); ?></div>
                <div>NIP. <?= esc($nip ?? '........................'); ?></div>
            </td>
            <td style="padding-top: 70px; vertical-align: bottom;">
                <div class="signature-name">Muhammad Yudi Prasetya, ST</div>
                <div>NIP. 198002142014121002</div>
            </td>
        </tr>
    </table>

    <!-- Catatan Alasan Tidak Disetujui -->
    <div class="catatan-section">
        <div class="catatan-title">Catatan Alasan Tidak Disetujui :</div>
        <div class="reason-line">&nbsp;</div>
        <div class="reason-line">&nbsp;</div>
        <div class="reason-line">&nbsp;</div>
    </div>

    <div class="footer-note">
        *) Coret yang tidak perlu
    </div>

</body>
</html>

<?php
// Helper Functions

function formatTanggalIndonesia($date) {
    if (empty($date) || $date === '0000-00-00') {
        return '-';
    }
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $parts = explode('-', $date);
    if (count($parts) !== 3) {
        return $date;
    }
    $day = (int) $parts[2];
    $month = (int) $parts[1];
    $year = (int) $parts[0];
    return $day . ' ' . ($bulan[$month] ?? $month) . ' ' . $year;
}

function getHariIndonesia($date) {
    if (empty($date) || $date === '0000-00-00') {
        return '....';
    }
    $dayOfWeek = date('w', strtotime($date));
    $hari = [
        0 => 'Minggu',
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu'
    ];
    return $hari[$dayOfWeek] ?? '....';
}

function getPangkatGolongan($golongan) {
    if (empty($golongan)) {
        return '-';
    }
    $map = [
        'I/a' => 'Juru Muda',
        'I/b' => 'Juru Muda Tingkat I',
        'I/c' => 'Juru',
        'I/d' => 'Juru Tingkat I',
        'II/a' => 'Pengatur Muda',
        'II/b' => 'Pengatur Muda Tingkat I',
        'II/c' => 'Pengatur',
        'II/d' => 'Pengatur Tingkat I',
        'III/a' => 'Penata Muda',
        'III/b' => 'Penata Muda Tingkat I',
        'III/c' => 'Penata',
        'III/d' => 'Penata Tingkat I',
        'IV/a' => 'Pembina',
        'IV/b' => 'Pembina Tingkat I',
        'IV/c' => 'Pembina Utama Muda',
        'IV/d' => 'Pembina Utama Madya',
        'IV/e' => 'Pembina Utama',
    ];
    
    $cleanGol = trim($golongan);
    if (preg_match('#^([IVXivx]+)/([a-eA-E])#', $cleanGol, $matches)) {
        $cleanGol = strtoupper($matches[1]) . '/' . strtolower($matches[2]);
    }
    
    $pangkat = $map[$cleanGol] ?? null;
    
    if ($pangkat === null) {
        return $golongan;
    }
    
    return $pangkat . ' / ' . $cleanGol;
}
?>
