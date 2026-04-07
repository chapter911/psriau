<?php
// Load the custom helper file
helper('custom');

date_default_timezone_set('Asia/Jakarta');

// Pre-calculate values to avoid redundant function calls in the template
$total_penawaran_terbilang = terbilang_rupiah($data->total_penawaran);
$total_nominal_terbilang = terbilang_rupiah($data->nominal_kontrak);
$jabatan = ucwords(strtolower($data->jabatan));
$tanggal_surat = tanggal_indonesia($data->tanggal_surat_penawaran);

?>

<!DOCTYPE html>
<html>
<head>
    <style>
        /* Pengaturan Font Global untuk seluruh dokumen */
        body, table, td, th, div, span, li, p, b, i, u {
            font-family: "Times New Roman", serif;
        }

        body {
            font-size: 11pt;
            line-height: 1;
            margin: 0;
        }

        @page {
            margin: 1cm 2cm 2cm 2cm;
        }

        .text-center {
            text-align: center;
        }
        .text-justify {
            text-align: justify;
        }
        .bold {
            font-weight: bold;
        }
        .underline {
            text-decoration: underline;
        }

        /* Layout Helpers */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .header-table td {
            vertical-align: top;
            padding: 0;
        }

        .content-div {
            margin-bottom: 10px;
            text-indent: 50px; /* Indentasi awal paragraf */
        }

        .mb-1 { margin-bottom: 5px; }
        .mb-2 { margin-bottom: 20px; }
        .ml-2 { margin-left: 20px; }

        /* Blok Tanda Tangan */
        .signature-block {
            float: left;
            width: 60%;
            text-align: left;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        /* Blok Tembusan */
        .cc-block {
            clear: both;
            font-size: 12pt;
            margin-top: 20px;
        }
        ol {
            margin-top: 0;
            padding-left: 20px;
        }
        li {
            padding-left: 5px;
        }

        /* --- HEADER GAMBAR (Hanya Halaman 1) --- */
        .header-img-container {
            width: 100%;
            text-align: center;
            margin-bottom: 10px;
        }
        .header-img {
            width: 100%;
            height: auto;
            max-height: 120px;
            object-fit: contain;
        }
    </style>
</head>
<body>

    <div class="header-img-container">
        <img src="<?= base_url() ?>public/img/logo_kop_jl_bakti.png" class="header-img" alt="Kop Surat">
    </div>

    <div class="text-center bold underline mb-2" style="font-size: 12pt;">
        SURAT PENUNJUKAN PENYEDIA BARANG/JASA (SPPBJ)
    </div>

    <table class="header-table">
        <tr>
            <td width="80">Nomor</td>
            <td width="10">:</td>
            <td><?= htmlspecialchars($data->no_sppbj, ENT_QUOTES, 'UTF-8'); ?></td>
            <td align="right" width="150">Pekanbaru, <?= tanggal_indonesia($data->tanggal_sppbj); ?></td>
        </tr>
        <tr>
            <td>Lampiran</td>
            <td>:</td>
            <td colspan="2">-</td>
        </tr>
    </table>

    <div class="mb-1">
        Kepada Yth.<br>
        <b><?= htmlspecialchars($data->nama, ENT_QUOTES, 'UTF-8'); ?></b><br>
        Di -<br>
        <div class="ml-2">
            Tempat
        </div>
    </div>

    <div class="mb-2">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td width="60" style="vertical-align: top;">Perihal:</td>
                <td class="text-justify">
                    <b>Penunjukan Penyedia untuk Pelaksanaan Pekerjaan</b><br>
                    <b>Konsultan Individual <?= htmlspecialchars($jabatan, ENT_QUOTES, 'UTF-8'); ?><br/><?= htmlspecialchars($data->nama_paket, ENT_QUOTES, 'UTF-8'); ?></b>
                </td>
            </tr>
        </table>
    </div>

    <div class="text-justify content-div">
        Dengan ini kami beritahukan bahwa penawaran Saudara nomor: <?= htmlspecialchars($data->nomor_surat_penawaran, ENT_QUOTES, 'UTF-8'); ?> tanggal <?= tanggal_indonesia($data->tanggal_surat_penawaran); ?> perihal Penawaran Pekerjaan Konsultan Individual <?= htmlspecialchars($jabatan, ENT_QUOTES, 'UTF-8'); ?> <?= htmlspecialchars($data->nama_paket, ENT_QUOTES, 'UTF-8'); ?> dengan nilai penawaran setelah dilakukan klarifikasi dan negosiasi teknis dan biaya oleh Pejabat Pengadaan Barang / Jasa Pemerintah pada Satuan Kerja Pelaksanaan Prasarana Strategis Riau Tahun Anggaran <?= $data->tahun_anggaran; ?> sebesar <b>Rp. <?= number_format($data->nominal_kontrak, 0, ",", "."); ?>,- (<?= htmlspecialchars($total_nominal_terbilang, ENT_QUOTES, 'UTF-8'); ?>)</b> termasuk PPH pasal 21, telah ditetapkan sebagai pemenang oleh Pejabat Pengadaan Barang / Jasa Pemerintah pada Satuan Kerja Pelaksanaan Prasarana strategis Riau Tahun Anggaran <?= $data->tahun_anggaran; ?>.
    </div>

    <div class="text-justify content-div">
        Selanjutnya kami menunjuk Saudara untuk melaksanakan pekerjaan <b>Konsultan Individual <?= htmlspecialchars($jabatan, ENT_QUOTES, 'UTF-8'); ?> <?= htmlspecialchars($data->nama_paket, ENT_QUOTES, 'UTF-8'); ?></b>, dan meminta Saudara untuk menandatangani Surat Perjanjian setelah dikeluarkannya SPPBJ ini sesuai dengan ketentuan dalam Dokumen Pemilihan.
    </div>

    <div class="text-justify content-div">
        Kegagalan Saudara untuk menerima penunjukan ini yang disusun berdasarkan evaluasi terhadap penawaran Saudara akan dikenakan sanksi sesuai dengan ketentuan yang tercantum dalam Dokumen Pemilihan.
    </div>

    <div class="signature-block">
        <div class="bold">Pejabat Pembuat Komitmen</div>
        <div class="bold">Pelaksanaan Prasarana Strategis Riau</div>
        <div class="bold">Tahun Anggaran <?= $data->tahun_anggaran; ?></div>
        <br><br><br><br><br><br>
        <div class="bold underline"><?= $data->pejabat_ppk; ?></div>
        <div>NIP. <?= $data->nip_pejabat_ppk; ?></div>
    </div>

    <div class="cc-block">
        Tembusan, disampaikan kepada Yth :
        <ol>
            <li>Menteri Pekerjaan Umum RI di Jakarta;</li>
            <li>Inspektur Jenderal Kementerian Pekerjaan Umum RI di Jakarta;</li>
            <li>Direktur Jenderal Prasarana Strategis Kementerian Pekerjaan Umum RI di Jakarta;</li>
            <li>Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau</li>
            <li>Pejabat Pengadaan Barang / Jasa Pemerintah di Lingkungan Satuan Kerja Pelaksanaan Prasarana Strategis Riau dan;</li>
            <li>Pertinggal.</li>
        </ol>
    </div>

</body>
</html>