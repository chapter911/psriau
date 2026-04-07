<?php
// Load the custom helper file
helper('custom');
date_default_timezone_set('Asia/Jakarta');

$jabatan = ucwords(strtolower($data->jabatan));
$nomor_baphp = $data->nomor_baphp ?? 'BAPHP/TL-PTSR/01/PPK/PPS-RIAU/' . date('m', strtotime($data->tanggal_surat_penawaran ?? $data->tanggal_kontrak ?? date('Y-m-d'))) . '/' . date('Y', strtotime($data->tanggal_surat_penawaran ?? $data->tanggal_kontrak ?? date('Y-m-d')));
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

$tanggal_pemeriksaan_raw = date('Y-m-d', strtotime(($data->tanggal_akhir ?? $data->tanggal_surat_penawaran ?? $data->tanggal_kontrak ?? date('Y-m-d')) . ' -1 day'));
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
            font-size: 11pt;
            line-height: 1.2;
            margin: 0;
        }

        @page {
            margin: 1cm 2cm 2cm 2cm;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-justify { text-align: justify; }
        .bold { font-weight: bold; }
        .underline { text-decoration: underline; }
        .mb-1 { margin-bottom: 5px; }
        .mb-2 { margin-bottom: 15px; }
        .ml-2 { margin-left: 20px; }
        .box-highlight {
            background: #fff58d;
            padding: 6px 10px;
            border: 1px solid #000;
            margin-bottom: 10px;
            display: inline-block;
        }
        .header-table, .content-table, .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td, .content-table td, .content-table th {
            vertical-align: top;
            padding: 2px 4px;
        }
        .content-table th, .content-table td {
            border: 1px solid #000;
        }
        .signature-table td {
            vertical-align: top;
            padding: 4px;
        }
        .signature-name { margin-top: 60px; }
    </style>
</head>
<body>
    <div class="header-img-container" style="text-align: center; margin-bottom: 10px;">
        <?= kop_surat_img_tag('header-img', 'width: 100%; max-height: 120px; object-fit: contain;', 'Kop Surat', isset($data->kop_surat_id) ? (int) $data->kop_surat_id : null); ?>
    </div>

    <div class="text-center bold" style="font-size: 12pt; margin-bottom: 8px;">BERITA ACARA PEMERIKSAAN HASIL PEKERJAAN (BAPHP)</div>

    <div class="text-center mb-2">
        Nomor: <?= htmlspecialchars($nomor_baphp, ENT_QUOTES, 'UTF-8'); ?>
    </div>

    <div class="text-justify mb-2">
        Pada hari ini <?= $tanggal_pemeriksaan; ?>, telah dilaksanakan pemeriksaan hasil pekerjaan, dengan ini telah mengadakan penelitian dan pemeriksaan antara konsultan perencanaan dan pejabat penandatangan kontrak, dan tim pendukung atas Pekerjaan <b><?= htmlspecialchars($data->nama_pekerjaan, ENT_QUOTES, 'UTF-8'); ?></b>.
    </div>

    <div class="mb-1">Berdasarkan:</div>
    <ol class="mb-2 text-justify" style="margin-top: 0; margin-bottom: 15px; padding-left: 22px;">
        <li>Surat Perintah Kerja (SPK)/Kontrak nomor <?= htmlspecialchars($nomor_spk, ENT_QUOTES, 'UTF-8'); ?>, tanggal <?= $tanggal_spk; ?></li>
        <li>Surat yang bersangkutan nomor <?= htmlspecialchars($data->nomor_surat_permohonan, ENT_QUOTES, 'UTF-8'); ?>, tanggal <?= tanggal_indonesia($data->tanggal_surat_permohonan); ?></li>
    </ol>

    <div class="mb-2">
        <span>Dengan Hasil Pemeriksaan Sebagai Berikut :</span>
    </div>

    <table class="content-table" style="margin-bottom: 15px;">
        <thead style="background-color: #f0f0f0;">
            <tr>
                <th width="5%" rowspan="2" style=" vertical-align: middle; text-align: center;">No</th>
                <th width="40%" rowspan="2" style=" vertical-align: middle; text-align: center;">Uraian Pekerjaan</th>
                <th width="20%" colspan="2" style=" vertical-align: middle; text-align: center;">Hasil Pemeriksaan</th>
                <th width="35%" rowspan="2" style=" vertical-align: middle; text-align: center;">Keterangan</th>
            </tr>
            <tr>
                <th style=" vertical-align: middle; text-align: center;">Sesuai</th>
                <th style=" vertical-align: middle; text-align: center;">Tidak Sesuai</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pekerjaan_baphp as $d) { ?>
                <tr>
                    <td class="text-center">1</td>
                    <td><?= $d->pekerjaan ?></td>
                    <td class="text-center check-mark">&#10003;</td>
                    <td></td>
                    <td></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <div class="mb-1">Menyatakan Bahwa:</div>
    <ol class="mb-2 text-justify" style="margin-top: 0; margin-bottom: 15px; padding-left: 22px;">
        <li>Pekerjaan <b><?= htmlspecialchars($data->nama_pekerjaan, ENT_QUOTES, 'UTF-8'); ?></b>, telah melaksanakan pekerjaan sesuai kontrak yang telah disepakati bersama dengan sistem pembayaran yaitu <?= htmlspecialchars($data->jenis_pembayaran, ENT_QUOTES, 'UTF-8'); ?>.</li>
    </ol>

    <div class="text-justify mb-2">
        Demikian Berita Acara Pemeriksaan Hasil Pekerjaan ini dibuat dan apabila dikemudian hari terdapat kekeliruan di dalamnya maka akan ditinjau dan diperbaiki sebagaimana mestinya.
    </div>

    <table class="content-table" style="margin-bottom: 15px;">
        <thead style="background-color: #f0f0f0;">
            <tr>
                <th width="5%" style=" vertical-align: middle; text-align: center;">No</th>
                <th width="30%" style=" vertical-align: middle; text-align: center;">Nama</th>
                <th width="35%" style=" vertical-align: middle; text-align: center;">Jabatan</th>
                <th width="25%" style=" vertical-align: middle; text-align: center;">Tanda Tangan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td><?= htmlspecialchars($data->pejabat_ppk, ENT_QUOTES, 'UTF-8'); ?></td>
                <td>Pejabat Penandatangan Kontrak</td>
                <td></td>
            </tr>
            <tr>
                <td>2</td>
                <td></td>
                <td>Tim Teknis PPK</td>
                <td></td>
            </tr>
            <tr>
                <td>3</td>
                <td></td>
                <td>Tim Teknis PPK</td>
                <td></td>
            </tr>
            <tr>
                <td>4</td>
                <td></td>
                <td>Tim Teknis PPK</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
