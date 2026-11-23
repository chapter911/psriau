<?php
// Load the custom helper file
helper('custom');
date_default_timezone_set('Asia/Jakarta');

$jabatan = ucwords(strtolower($data->jabatan));
$nomor_spk = $data->nomor_kontrak ?? '-';
$tanggal_spk = tanggal_indonesia($data->tanggal_kontrak ?? $data->tanggal_surat_penawaran ?? date('Y-m-d'));
$nomor_surat = $data->nomor_surat_penawaran ?? '-';
$tanggal_surat = tanggal_indonesia($data->tanggal_surat_penawaran ?? date('Y-m-d'));

if (!function_exists('hari_indonesia')) {
    function hari_indonesia($tanggal)
    {
        $map = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        return $map[intval(date('N', strtotime($tanggal)))] ?? '';
    }
}

if (!function_exists('bulan_indonesia')) {
    function bulan_indonesia($tanggal)
    {
        $map = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
        return $map[intval(date('n', strtotime($tanggal)))] ?? '';
    }
}

$tanggal_pemeriksaan_base = (!empty($data->tanggal_akhir) && $data->tanggal_akhir !== '0000-00-00')
    ? $data->tanggal_akhir
    : ((!empty($data->tanggal_surat_penawaran) && $data->tanggal_surat_penawaran !== '0000-00-00')
        ? $data->tanggal_surat_penawaran
        : ((!empty($data->tanggal_kontrak) && $data->tanggal_kontrak !== '0000-00-00') ? $data->tanggal_kontrak : date('Y-m-d')));
$tanggal_pemeriksaan_raw = date('Y-m-d', strtotime($tanggal_pemeriksaan_base));
$nomor_baphp = !empty(trim((string) ($data->nomor_baphp ?? '')))
    ? trim((string) $data->nomor_baphp)
    : 'BAPHP/TL-PTSR/01/PPK/PPS-RIAU/' . date('m', strtotime($tanggal_pemeriksaan_raw)) . '/' . date('Y', strtotime($tanggal_pemeriksaan_raw));
$tanggal_pemeriksaan = sprintf(
    '%s, tanggal %s bulan %s tahun %s (%s)',
    hari_indonesia($tanggal_pemeriksaan_raw),
    ucwords(strtolower(terbilang_angka(intval(date('d', strtotime($tanggal_pemeriksaan_raw)))))),
    bulan_indonesia($tanggal_pemeriksaan_raw),
    ucwords(strtolower(terbilang_angka(intval(date('Y', strtotime($tanggal_pemeriksaan_raw)))))),
    date('d-m-Y', strtotime($tanggal_pemeriksaan_raw))
);
$total_nominal_terbilang = terbilang_rupiah($data->nominal_kontrak);
$pekerjaan_baphp = $pekerjaan_baphp ?? [];

$jabatanVal = trim((string) ($data->jabatan ?? ''));
$paketVal = trim((string) ($data->nama_paket ?? ''));
if ($paketVal === '') {
    $paketVal = trim((string) ($data->nama_pekerjaan ?? ''));
}
if (stripos($paketVal, 'pekerjaan ') === 0) {
    $paketVal = trim(substr($paketVal, 10));
}

if ($jabatanVal !== '' && $paketVal !== '') {
    $nama_pekerjaan_lengkap = 'Konsultan Individual ' . $jabatanVal . ', Pekerjaan ' . $paketVal;
} elseif ($jabatanVal !== '') {
    $nama_pekerjaan_lengkap = 'Konsultan Individual ' . $jabatanVal;
} elseif ($paketVal !== '') {
    $nama_pekerjaan_lengkap = 'Pekerjaan ' . $paketVal;
} else {
    $nama_pekerjaan_lengkap = 'Konsultan Individual';
}

$jenis_pembayaran = 'Lumsum';

$defaultJabatanTimTeknis = 'Tim Pengawas Swakelola';
$timTeknisList = [];
if (!empty($tim_teknis_ppk) && is_array($tim_teknis_ppk)) {
    foreach ($tim_teknis_ppk as $item) {
        if (is_array($item)) {
            $namaTrim = trim((string) ($item['nama'] ?? ''));
            $jabatanTrim = trim((string) ($item['jabatan'] ?? ''));
        } else {
            $namaTrim = trim((string) $item);
            $jabatanTrim = '';
        }
        if ($namaTrim !== '') {
            $timTeknisList[] = [
                'nama' => $namaTrim,
                'jabatan' => $jabatanTrim !== '' ? $jabatanTrim : $defaultJabatanTimTeknis,
            ];
        }
    }
}
if (empty($timTeknisList)) {
    $timTeknisList = [
        ['nama' => '', 'jabatan' => $defaultJabatanTimTeknis],
    ];
}

$totalPekerjaan = max(1, count($pekerjaan_baphp));
$totalSigners = 1 + count($timTeknisList); // 1 PPK + Tim Teknis

// Tata letak adaptif agar dokumen mengisi halaman penuh sampai bawah dan tetap 1 halaman
if ($totalSigners >= 8) {
    $divHeight = 32;
    $mb2 = 6;
    $mb1 = 3;
    $tablePadding = '2px 4px';
    $lineHeight = 1.15;
    $fontSize = '10pt';
} elseif ($totalSigners === 7) {
    if ($totalPekerjaan > 4) {
        $divHeight = 38;
        $mb2 = 7;
        $mb1 = 3;
        $tablePadding = '2px 4px';
        $lineHeight = 1.15;
        $fontSize = '10pt';
    } else {
        $divHeight = 47;
        $mb2 = 8;
        $mb1 = 4;
        $tablePadding = '2px 4px';
        $lineHeight = 1.16;
        $fontSize = '10.5pt';
    }
} elseif ($totalSigners === 6) {
    $divHeight = 56;
    $mb2 = 9;
    $mb1 = 4;
    $tablePadding = '3px 4px';
    $lineHeight = 1.18;
    $fontSize = '10.5pt';
} elseif ($totalSigners === 5) {
    $divHeight = 68;
    $mb2 = 11;
    $mb1 = 5;
    $tablePadding = '3px 5px';
    $lineHeight = 1.20;
    $fontSize = '10.5pt';
} elseif ($totalSigners === 4) {
    $divHeight = 82;
    $mb2 = 13;
    $mb1 = 6;
    $tablePadding = '3px 5px';
    $lineHeight = 1.22;
    $fontSize = '10.5pt';
} elseif ($totalSigners === 3) {
    $divHeight = 98;
    $mb2 = 15;
    $mb1 = 6;
    $tablePadding = '4px 5px';
    $lineHeight = 1.24;
    $fontSize = '11pt';
} else {
    // 1 atau 2 penandatangan
    $divHeight = 115;
    $mb2 = 18;
    $mb1 = 7;
    $tablePadding = '4px 5px';
    $lineHeight = 1.25;
    $fontSize = '11pt';
}
$ttdHeight = $divHeight + 4;
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        body, table, td, th, div, span, p, b, u {
            font-family: "Times New Roman", serif;
        }

        .check-mark {
            font-family: "DejaVu Sans", "Arial Unicode MS", sans-serif;
        }

        body {
            font-size: <?= $fontSize; ?>;
            line-height: <?= $lineHeight; ?>;
            margin: 0;
        }

        @page {
            margin: 0.8cm 1.8cm 1cm 1.8cm;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-justify { text-align: justify; }
        .bold { font-weight: bold; }
        .underline { text-decoration: underline; }
        .mb-1 { margin-bottom: <?= $mb1; ?>px; }
        .mb-2 { margin-bottom: <?= $mb2; ?>px; }
        .ml-2 { margin-left: 20px; }
        .box-highlight {
            background: #fff58d;
            padding: 6px 10px;
            border: 1px solid #000;
            margin-bottom: 8px;
            display: inline-block;
        }
        .header-table, .content-table, .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td, .content-table td, .content-table th {
            vertical-align: top;
            padding: <?= $tablePadding; ?>;
        }
        .content-table th, .content-table td {
            border: 1px solid #000;
        }
        .signature-table td {
            vertical-align: top;
            padding: 4px;
        }
        .signature-name { margin-top: 40px; }
        .ttd-table {
            page-break-inside: avoid;
            margin-bottom: 0;
        }
        .ttd-table th, .ttd-table td {
            vertical-align: middle;
            padding: 2px 4px;
            font-size: 10pt;
        }
        .ttd-cell {
            height: <?= $ttdHeight; ?>px;
        }
    </style>
</head>
<body>
    <div class="header-img-container" style="text-align: center; margin-bottom: 6px;">
        <?= kop_surat_img_tag('header-img', 'width: 100%; max-height: 100px; object-fit: contain;', 'Kop Surat', isset($data->kop_surat_id) ? (int) $data->kop_surat_id : null); ?>
    </div>

    <div class="text-center bold" style="font-size: 11.5pt; margin-bottom: 4px;">BERITA ACARA PEMERIKSAAN HASIL PEKERJAAN (BAPHP)</div>

    <div class="text-center mb-2" style="font-size: 10.5pt;">
        Nomor: <?= htmlspecialchars($nomor_baphp, ENT_QUOTES, 'UTF-8'); ?>
    </div>

    <div class="text-justify mb-2">
        Pada hari ini <?= $tanggal_pemeriksaan; ?>, telah dilaksanakan penelitian dan pemeriksaan bersama antara Pejabat Penandatangan Kontrak, Tim Pengawas/Teknis, dan Penyedia Jasa terhadap hasil pelaksanaan <b><?= htmlspecialchars($nama_pekerjaan_lengkap, ENT_QUOTES, 'UTF-8'); ?></b>.
    </div>

    <div class="mb-1">Berdasarkan:</div>
    <ol class="mb-2 text-justify" style="margin-top: 0; margin-bottom: <?= $mb2; ?>px; padding-left: 20px;">
        <li>Surat Perintah Kerja (SPK)/Kontrak Nomor: <?= htmlspecialchars($nomor_spk, ENT_QUOTES, 'UTF-8'); ?>, tanggal <?= $tanggal_spk; ?></li>
    </ol>

    <div class="mb-1">
        <span>Dengan hasil pemeriksaan sebagai berikut:</span>
    </div>

    <table class="content-table" style="margin-bottom: <?= $mb2; ?>px; font-size: 10pt;">
        <thead style="background-color: #f0f0f0;">
            <tr>
                <th width="5%" rowspan="2" style=" vertical-align: middle; text-align: center;">No</th>
                <th width="45%" rowspan="2" style=" vertical-align: middle; text-align: center;">Uraian Pekerjaan</th>
                <th width="20%" colspan="2" style=" vertical-align: middle; text-align: center;">Hasil Pemeriksaan</th>
                <th width="30%" rowspan="2" style=" vertical-align: middle; text-align: center;">Keterangan</th>
            </tr>
            <tr>
                <th style=" vertical-align: middle; text-align: center;">Sesuai</th>
                <th style=" vertical-align: middle; text-align: center;">Tidak Sesuai</th>
            </tr>
        </thead>
        <tbody>
            <?php $noPekerjaan = 1; foreach ($pekerjaan_baphp as $d) { ?>
                <tr>
                    <td class="text-center"><?= $noPekerjaan++; ?></td>
                    <td><?= htmlspecialchars((string) ($d->pekerjaan ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td class="text-center check-mark">&#10003;</td>
                    <td></td>
                    <td></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <div class="mb-1">Menyatakan Bahwa:</div>
    <ol class="mb-2 text-justify" style="margin-top: 0; margin-bottom: <?= $mb2; ?>px; padding-left: 20px;">
        <li><b><?= htmlspecialchars($nama_pekerjaan_lengkap, ENT_QUOTES, 'UTF-8'); ?></b>, telah selesai dilaksanakan sesuai ketentuan kontrak yang disepakati bersama, dengan sistem pembayaran <b><?= htmlspecialchars($jenis_pembayaran, ENT_QUOTES, 'UTF-8'); ?></b>.</li>
    </ol>

    <div class="text-justify mb-2">
        Demikian Berita Acara Pemeriksaan Hasil Pekerjaan ini dibuat, dan apabila di kemudian hari terdapat kekeliruan di dalamnya, maka akan ditinjau dan diperbaiki sebagaimana mestinya.
    </div>

    <table class="content-table ttd-table">
        <thead style="background-color: #f0f0f0;">
            <tr>
                <th width="5%" style="vertical-align: middle; text-align: center;">No</th>
                <th width="38%" style="vertical-align: middle; text-align: center;">Nama</th>
                <th width="37%" style="vertical-align: middle; text-align: center;">Jabatan</th>
                <th width="20%" style="vertical-align: middle; text-align: center;">Tanda Tangan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td><?= htmlspecialchars((string) ($data->pejabat_ppk ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                <td>Pejabat Penandatangan Kontrak</td>
                <td class="ttd-cell" height="<?= $ttdHeight; ?>"><div style="height: <?= $divHeight; ?>px; line-height: <?= $divHeight; ?>px;">&nbsp;</div></td>
            </tr>
            <?php
            $noTtd = 2;
            foreach ($timTeknisList as $rowTimTeknis):
            ?>
                <tr>
                    <td class="text-center"><?= $noTtd++; ?></td>
                    <td><?= htmlspecialchars($rowTimTeknis['nama'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars($rowTimTeknis['jabatan'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td class="ttd-cell" height="<?= $ttdHeight; ?>"><div style="height: <?= $divHeight; ?>px; line-height: <?= $divHeight; ?>px;">&nbsp;</div></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
