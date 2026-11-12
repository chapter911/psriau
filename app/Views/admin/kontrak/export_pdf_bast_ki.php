<?php
// Load the custom helper file
helper('custom');
date_default_timezone_set('Asia/Jakarta');

$nama_kedua = $data->nama ?? '-';
$jabatan_kedua = trim((string) ($data->jabatan ?? '-'));
if ($jabatan_kedua === '') {
    $jabatan_kedua = '-';
}
$nama_paket_str = trim((string) ($data->nama_paket ?? ''));
$jabatan_kedua_lengkap = $jabatan_kedua;
if ($jabatan_kedua !== '-' && $nama_paket_str !== '' && stripos($jabatan_kedua, $nama_paket_str) === false) {
    $jabatan_kedua_lengkap = $jabatan_kedua . ' ' . $nama_paket_str;
}
$alamat_kedua = $data->alamat ?? '-';
$nomor_spk = $data->nomor_kontrak ?? '-';
$tanggal_spk = tanggal_indonesia($data->tanggal_kontrak ?? $data->tanggal_surat_penawaran ?? date('Y-m-d'));

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

$tanggal_bast_base = (!empty($data->tanggal_akhir) && $data->tanggal_akhir !== '0000-00-00')
    ? $data->tanggal_akhir
    : ((!empty($data->tanggal_surat_penawaran) && $data->tanggal_surat_penawaran !== '0000-00-00')
        ? $data->tanggal_surat_penawaran
        : ((!empty($data->tanggal_kontrak) && $data->tanggal_kontrak !== '0000-00-00') ? $data->tanggal_kontrak : date('Y-m-d')));
$tanggal_bast_raw = date('Y-m-d', strtotime($tanggal_bast_base));
$tanggal_bast_terbilang = sprintf(
    '%s, tanggal %s bulan %s tahun %s (%s)',
    hari_indonesia($tanggal_bast_raw),
    ucwords(strtolower(terbilang_angka(intval(date('d', strtotime($tanggal_bast_raw)))))),
    bulan_indonesia($tanggal_bast_raw),
    ucwords(strtolower(terbilang_angka(intval(date('Y', strtotime($tanggal_bast_raw)))))),
    date('d-m-Y', strtotime($tanggal_bast_raw))
);

$tahun_bast = !empty($data->tahun_anggaran) ? (string) $data->tahun_anggaran : date('Y', strtotime($tanggal_bast_raw));
$no_urut_bast = str_pad((string) max(1, (int) ($bast_no_urut ?? 1)), 2, '0', STR_PAD_LEFT);
$kode_paket_bast = trim((string) ($bast_kode_paket ?? 'KI'));
if ($kode_paket_bast === '') {
    $kode_paket_bast = 'KI';
}
$nomor_bast = !empty(trim((string) ($data->nomor_bast ?? '')))
    ? trim((string) $data->nomor_bast)
    : "{$no_urut_bast}/BAST/{$kode_paket_bast}/PPK/PPS-RIAU/{$tahun_bast}";

$nomor_baphp = !empty(trim((string) ($data->nomor_baphp ?? '')))
    ? trim((string) $data->nomor_baphp)
    : 'BAPHP/TL-PTSR/01/PPK/PPS-RIAU/' . date('m', strtotime($tanggal_bast_raw)) . '/' . date('Y', strtotime($tanggal_bast_raw));
$tanggal_baphp = tanggal_indonesia($tanggal_bast_raw);
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        body, table, td, th, div, span, p, b, u {
            font-family: "Times New Roman", serif;
        }

        body {
            font-size: 11pt;
            line-height: 1.3;
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
        .mb-2 { margin-bottom: 12px; }
        .ml-2 { margin-left: 20px; }
        .box-highlight {
            background: #fff58d;
            padding: 6px 10px;
            border: 1px solid #000;
            display: inline-block;
        }
        .data-table, .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table td {
            vertical-align: top;
            padding: 2px 4px;
        }
        .signature-table td {
            vertical-align: top;
            padding: 4px;
            border: 1px solid #000;
        }
        .signature-table th {
            text-align: left;
            padding: 4px;
            border: 1px solid #000;
        }
        .no-border {
            border: none !important;
        }
        ol {
            margin-top: 0;
            padding-left: 18px;
        }
    </style>
</head>
<body>
    <div class="text-center" style="margin-bottom: 10px;">
        <?= kop_surat_img_tag('', 'width: 100%; max-height: 120px; object-fit: contain;', 'Kop Surat', isset($data->kop_surat_id) ? (int) $data->kop_surat_id : null); ?>
    </div>

    <div class="text-center bold" style="font-size: 12pt; margin-bottom: 6px;">BERITA ACARA SERAH TERIMA PERTAMA HASIL PEKERJAAN (BAST)</div>

    <div class="text-center mb-2">Nomor: <?= htmlspecialchars($nomor_bast, ENT_QUOTES, 'UTF-8'); ?></div>

    <div class="text-justify mb-2">
        Pada hari ini <?= $tanggal_bast_terbilang; ?>, yang bertandatangan di bawah ini:
    </div>

    <table class="data-table mb-2">
        <tr>
            <td width="20%">Nama</td>
            <td width="3%">:</td>
            <td><?= htmlspecialchars($data->pejabat_ppk, ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
        <tr>
            <td>NIP</td>
            <td>:</td>
            <td><?= htmlspecialchars($data->nip_pejabat_ppk, ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>:</td>
            <td>Pejabat Penandatangan Kontrak Pelaksanaan Prasarana Strategis, Satuan Kerja Pelaksanaan Prasarana Strategis Riau</td>
        </tr>
        <tr>
            <td>Berkedudukan di</td>
            <td>:</td>
            <td>Pekanbaru</td>
        </tr>
    </table>

    <div class="text-justify mb-2">
        Selanjutnya disebut PIHAK PERTAMA
    </div>

    <table class="data-table mb-2">
        <tr>
            <td width="20%">Nama</td>
            <td width="3%">:</td>
            <td><?= htmlspecialchars($nama_kedua, ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>:</td>
            <td><?= htmlspecialchars($jabatan_kedua_lengkap, ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
        <tr>
            <td>Berkedudukan di</td>
            <td>:</td>
            <td><?= htmlspecialchars($alamat_kedua, ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
    </table>

    <div class="text-justify mb-2">
        Selanjutnya disebut PIHAK KEDUA
    </div>

    <div class="bold mb-1">Berdasarkan:</div>
    <ol class="mb-2 text-justify" style="margin-top: 0; margin-bottom: 15px;">
        <li>Surat Perintah Kerja (SPK)/Kontrak Nomor <?= htmlspecialchars($nomor_spk, ENT_QUOTES, 'UTF-8'); ?> tanggal <?= $tanggal_spk; ?></li>
        <li>Berita Acara Pemeriksaan Hasil Pekerjaan Nomor: <?= htmlspecialchars($nomor_baphp, ENT_QUOTES, 'UTF-8'); ?> tanggal <?= $tanggal_baphp; ?></li>
    </ol>

    <div class="text-justify mb-2">
        Dengan ini kedua belah pihak setuju dan sepakat untuk melakukan serah terima Pekerjaan <b><?= htmlspecialchars($data->nama_pekerjaan, ENT_QUOTES, 'UTF-8'); ?></b> dengan ketentuan sebagai berikut:
    </div>

    <ol class="mb-2" style="margin-top: 0; margin-bottom: 15px; padding-left: 18px;">
        <li>PIHAK KEDUA melakukan penyerahan seluruh hasil pekerjaan paket Pekerjaan Jasa Konsultan Individual/Perorangan <b><?= htmlspecialchars($data->nama_pekerjaan, ENT_QUOTES, 'UTF-8'); ?></b> kepada PIHAK PERTAMA.</li>
        <li>PIHAK PERTAMA menerima penyerahan hasil pekerjaan paket Pekerjaan Jasa Konsultan Perorangan <b><?= htmlspecialchars($data->nama_pekerjaan, ENT_QUOTES, 'UTF-8'); ?></b> dari PIHAK KEDUA.</li>
        <li>Penyerahan sebagaimana dimaksud pada angka 1 (satu) berupa hasil pekerjaan <b><?= htmlspecialchars($data->nama_pekerjaan, ENT_QUOTES, 'UTF-8'); ?></b> pada Satuan Kerja Pelaksanaan Prasarana Strategis Riau T.A 2025.</li>
    </ol>

    <table class="data-table" style="margin-bottom: 20px;">
        <tr>
            <td class="no-border text-center" style="width: 50%;">
                Pelaksana Pekerjaan<br>
                KONSULTAN INDIVIDUAL/ PERORANGAN
            </td>
            <td class="no-border text-center" style="width: 50%;">
                PPK Pelaksanaan Prasarana Strategis,<br>
                Satuan Kerja Pelaksanaan Prasarana Strategis Riau
            </td>
        </tr>
    </table>

    <table class="signature-table">
        <tr>
            <td class="no-border" style="width: 50%;">&nbsp;</td>
            <td class="no-border" style="width: 50%;">&nbsp;</td>
        </tr>
        <tr>
            <td class="no-border text-center" style="padding-top: 40px;">
                <b><?= htmlspecialchars($nama_kedua, ENT_QUOTES, 'UTF-8'); ?></b><br>
                <?= htmlspecialchars($jabatan_kedua, ENT_QUOTES, 'UTF-8'); ?>
            </td>
            <td class="no-border text-center" style="padding-top: 40px;">
                <b><?= htmlspecialchars($data->pejabat_ppk, ENT_QUOTES, 'UTF-8'); ?></b><br>
                NIP. <?= htmlspecialchars($data->nip_pejabat_ppk, ENT_QUOTES, 'UTF-8'); ?>
            </td>
        </tr>
    </table>
</body>
</html>
