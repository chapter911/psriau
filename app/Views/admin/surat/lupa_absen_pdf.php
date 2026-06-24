<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Pernyataan Lupa Absen</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
            padding: 40px 50px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: middle;
        }

        .logo-cell {
            width: 80px;
            text-align: center;
        }

        .logo {
            width: 70px;
            height: auto;
        }

        .institution-name {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
        }

        .document-number {
            font-size: 11pt;
            margin-top: 5px;
        }

        .title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 25px 0 20px;
        }

        .content {
            text-align: justify;
        }

        .paragraph {
            margin-bottom: 15px;
            text-indent: 40px;
        }

        table.biodata {
            margin-left: 40px;
            margin-bottom: 15px;
        }

        table.biodata td {
            padding: 2px 0;
        }

        table.biodata td:first-child {
            width: 150px;
        }

        .info-box {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #000;
        }

        .info-box table {
            width: 100%;
        }

        .info-box td {
            padding: 5px 10px;
        }

        .signature {
            margin-top: 50px;
            width: 100%;
        }

        .signature td {
            width: 50%;
            vertical-align: top;
            padding: 10px;
        }

        .signature-right {
            text-align: center;
        }

        .signature-label {
            margin-bottom: 60px;
        }

        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }

        .footer-note {
            font-size: 10pt;
            margin-top: 30px;
            font-style: italic;
        }

        @page {
            size: A4;
            margin: 20mm;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    <?php if (! empty($kop_surat_logo)): ?>
                        <img src="<?= $kop_surat_logo; ?>" alt="Logo" class="logo">
                    <?php else: ?>
                        <div style="width: 70px; height: 70px; border: 2px solid #000; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 24pt;">PU</div>
                    <?php endif; ?>
                </td>
                <td class="institution-name">
                    <?= esc($app_name ?? 'KEMENTERIAN PEKERJAAN UMUM'); ?><br>
                    <?= esc($official_name ?? 'Satuan Kerja Prasarana Strategis Riau'); ?>
                </td>
            </tr>
        </table>
        <div class="document-number"><?= esc($nomor_surat ?? ''); ?></div>
    </div>

    <!-- Title -->
    <div class="title">
        SURAT PERNYATAAN LUPA ABSEN
    </div>

    <!-- Content -->
    <div class="content">
        <p class="paragraph">
            Yang bertanda tangan di bawah ini, saya:
        </p>

        <table class="biodata">
            <tr>
                <td>Nama</td>
                <td>: <?= esc($nama ?? ''); ?></td>
            </tr>
            <tr>
                <td>NIP</td>
                <td>: <?= esc($nip ?? ''); ?></td>
            </tr>
            <tr>
                <td>Jabatan</td>
                <td>: <?= esc($jabatan ?? ''); ?></td>
            </tr>
            <tr>
                <td>Unit Kerja</td>
                <td>: <?= esc($unit_kerja ?? ''); ?></td>
            </tr>
        </table>

        <p class="paragraph">
            Dengan ini menyatakan bahwa saya <?= strtolower($jenis_absen ?? '') === 'masuk' ? 'terlambat' : 'pulang lebih awal'; ?> dan tidak melakukan presensi <?= strtolower($jenis_absen ?? '') === 'masuk' ? 'absensi masuk' : 'absensi pulang'; ?> pada:
        </p>

        <!-- Info Box -->
        <div class="info-box">
            <table>
                <tr>
                    <td style="width: 30%;"><strong>Tanggal</strong></td>
                    <td>: <?= formatTanggalIndonesia($tanggal_absen ?? ''); ?></td>
                </tr>
                <tr>
                    <td><strong>Jenis Absen</strong></td>
                    <td>: <?= esc($jenis_absen ?? ''); ?></td>
                </tr>
                <tr>
                    <td><strong>Alasan</strong></td>
                    <td>: <?= esc($alasan_detail ?? ''); ?></td>
                </tr>
            </table>
        </div>

        <p class="paragraph">
            Sehubungan dengan hal tersebut di atas, saya mohon agar diberikan izin <?= strtolower($jenis_absen ?? '') === 'masuk' ? 'terlambat masuk kerja' : 'pulang lebih awal'; ?> dan agar tidak diberikan sanksi administratif sebagaimana diatur dalam Peraturan Pemerintah.
        </p>

        <p class="paragraph">
            Demikian Surat Pernyataan ini saya buat dengan sebenarnya, untuk dapat dipergunakan sebagaimana mestinya.
        </p>

        <!-- Signature -->
        <table class="signature">
            <tr>
                <td></td>
                <td class="signature-right">
                    <div class="signature-label">
                        Pekanbaru, <?= formatTanggalIndonesia($tanggal_surat ?? date('Y-m-d')); ?><br>
                        Yang Membuat Pernyataan,
                    </div>
                    <div class="signature-name"><?= esc($nama ?? ''); ?></div>
                    <div>NIP. <?= esc($nip ?? ''); ?></div>
                </td>
            </tr>
        </table>

        <div class="footer-note">
            * Surat pernyataan ini dibuat dengan sebenar-benarnya dan penuh tanggung jawab.
        </div>
    </div>
</body>
</html>
<?php
// Helper function
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
?>
