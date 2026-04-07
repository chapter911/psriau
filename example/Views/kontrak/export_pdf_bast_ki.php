<?php
// Load the custom helper file
helper('custom');
date_default_timezone_set('Asia/Jakarta');

$nama_kedua = $data->nama ?? '-';
$jabatan_kedua = $data->jabatan ?? '-';
$alamat_kedua = $data->alamat ?? '-';
$nomor_spk = $data->nomor_kontrak ?? '-';
$tanggal_spk = tanggal_indonesia($data->tanggal_kontrak ?? $data->tanggal_surat_penawaran ?? date('Y-m-d'));
$nomor_baphp = $data->nomor_baphp ?? 'BAPHP/ADM-PTSR/' . date('m', strtotime($data->tanggal_surat_penawaran ?? $data->tanggal_kontrak ?? date('Y-m-d'))) . '/PPK/PPS-RIAU/XI/' . date('Y', strtotime($data->tanggal_surat_penawaran ?? $data->tanggal_kontrak ?? date('Y-m-d')));
$tanggal_baphp = tanggal_indonesia($data->tanggal_surat_penawaran ?? $data->tanggal_kontrak ?? date('Y-m-d'));
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
        <img src="<?= base_url() ?>public/img/logo_kop_jl_bakti.png" alt="Kop Surat" style="width: 100%; max-height: 120px; object-fit: contain;">
    </div>

    <div class="text-center bold" style="font-size: 12pt; margin-bottom: 6px;">BERITA ACARA SERAH TERIMA PERTAMA HASIL PEKERJAAN (BAST)</div>

    <div class="text-center mb-2">Nomor: <?= htmlspecialchars($data->nomor_bast, ENT_QUOTES, 'UTF-8'); ?></div>

    <div class="text-justify mb-2">
        Pada hari ini, tanggal <?= tanggal_indonesia($data->tanggal_surat_penawaran ?? $data->tanggal_kontrak ?? date('Y-m-d')); ?>, yang bertandatangan di bawah ini:
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
            <td><?= htmlspecialchars($jabatan_kedua, ENT_QUOTES, 'UTF-8'); ?></td>
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
        <li>Berita Acara Pemeriksaan Hasil Pekerjaan Nomor: <?= htmlspecialchars($data->nomor_baphp, ENT_QUOTES, 'UTF-8'); ?> tanggal <?= $tanggal_baphp; ?></li>
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
