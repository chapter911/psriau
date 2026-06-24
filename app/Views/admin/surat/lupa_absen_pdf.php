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

        .number-list {
            margin-left: 40px;
            margin-bottom: 15px;
        }

        .number-list li {
            margin-bottom: 8px;
        }

        table.entries {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table.entries th,
        table.entries td {
            border: 1px solid #000;
            padding: 6px 10px;
            font-size: 11pt;
        }

        table.entries th {
            background-color: #f0f0f0;
            text-align: center;
            font-weight: bold;
        }

        table.entries td {
            text-align: center;
        }

        table.signature {
            width: 100%;
            margin-top: 40px;
        }

        table.signature td {
            width: 50%;
            vertical-align: top;
            padding: 10px;
        }

        .signature-label {
            text-align: center;
            margin-bottom: 60px;
        }

        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 5px;
        }

        .signature-nip {
            font-size: 10pt;
        }

        .footer-note {
            font-size: 10pt;
            margin-top: 20px;
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

        <table style="margin-left: 40px; margin-bottom: 15px;">
            <tr>
                <td style="width: 150px;">Nama</td>
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
            Dengan ini menyatakan bahwa saya terlambat / tidak melakukan presensi absensi pada tanggal-tanggal sebagaimana tercantum dalam tabel di bawah ini:
        </p>

        <!-- Table Entries -->
        <table class="entries">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th style="width: 120px;">Tanggal</th>
                    <th style="width: 100px;">Hari</th>
                    <th style="width: 80px;">Jam</th>
                    <th>Jenis Absen</th>
                    <th style="width: 200px;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entries as $idx => $entry): ?>
                <tr>
                    <td><?= $idx + 1; ?></td>
                    <td><?= formatTanggalIndonesia($entry['tanggal'] ?? ''); ?></td>
                    <td><?= esc($entry['hari'] ?? ''); ?></td>
                    <td><?= formatJam($entry['jam'] ?? ''); ?></td>
                    <td><?= esc($entry['jenis'] ?? ''); ?></td>
                    <td><?= esc($entry['keterangan'] ?? '-'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p class="paragraph">
            <strong>Alasan/Tidak dapat melakukan presensi:</strong><br>
            <?= esc($alasan_detail ?? ''); ?>
        </p>

        <p class="paragraph">
            Sehubungan dengan hal tersebut di atas, saya mohon agar diberikan izin tidak masuk kerja / hadir terlambat sebagaimana tercantum dalam tabel di atas dan agar tidak diberikan sanksi administratif sebagaimana diatur dalam Peraturan Pemerintah.
        </p>

        <p class="paragraph">
            Demikian Surat Pernyataan ini saya buat dengan sebenarnya, untuk dapat dipergunakan sebagaimana mestinya.
        </p>

        <!-- Signature -->
        <table class="signature">
            <tr>
                <td></td>
                <td>
                    <div class="signature-label">
                        Pekanbaru, <?= formatTanggalIndonesia($tanggal_surat ?? date('Y-m-d')); ?><br>
                        Yang Membuat Pernyataan,
                    </div>
                    <div class="signature-name"><?= esc($nama ?? ''); ?></div>
                    <div class="signature-nip">NIP. <?= esc($nip ?? ''); ?></div>
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
// Helper functions for the PDF
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

function formatJam($time) {
    if (empty($time)) {
        return '-';
    }
    return date('H:i', strtotime($time)) . ' WIB';
}
?>
