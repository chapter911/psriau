<?php
// Load the custom helper file
helper('custom');

date_default_timezone_set('Asia/Jakarta');

// Pre-calculate values to avoid redundant function calls in the template
$total_penawaran_terbilang = terbilang_rupiah($data->total_penawaran);
$durasi_pelaksanaan_terbilang = terbilang_angka($data->durasi_pelaksanaan);
$jabatan = ucwords(strtolower($data->jabatan));
$tanggal_surat = tanggal_indonesia($data->tanggal_surat_penawaran);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Penawaran</title>
    <style>
        /* Pengaturan Umum Halaman A4 */
        @page {
            margin: 2cm; /* Margin lebih kecil untuk memaksimalkan ruang halaman */
            size: A4 portrait;
        }

        html {
            margin: 0;
            padding: 0;
            height: 100%;
        }

        body {
            margin: 0;
            padding: 2cm; /* Inner padding untuk menjaga jarak isi dari tepi halaman */
            font-family: "Times New Roman", serif;
            font-size: 12pt;
            line-height: 1.05; /* Sedikit dikurangi untuk menghemat ruang */
            color: #000;
        }

        /* Judul Surat */
        .header-title {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        /* Tanggal di kanan */
        .date-right {
            text-align: right;
            margin-bottom: 10px;
        }

        /* Layout Header (Nomor, Lampiran, dll) */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .meta-table td {
            vertical-align: top;
            padding: 1px 0;
        }
        .col-label {
            width: 100px;
        }
        .col-sep {
            width: 15px;
            text-align: center;
        }

        /* Bagian Kepada Yth */
        .recipient-block {
            margin-bottom: 10px;
        }

        /* Paragraf Isi */
        p {
            text-align: justify;
            margin: 0 0 8px 0;
        }

        /* Penekanan Teks (Biaya) */
        .amount {
            font-weight: bold;
            font-style: italic;
        }

        /* Daftar */
        ol {
            margin: 0 0 8px 0;
            padding-left: 18px;
        }
        li {
            margin-bottom: 4px;
            text-align: justify;
        }
        ol.alpha-list {
            list-style-type: lower-alpha;
            padding-left: 22px;
        }
        ol.sub-list {
            list-style-type: none;
            counter-reset: sublist;
            padding-left: 15px;
        }
        ol.sub-list > li {
            counter-increment: sublist;
        }
        ol.sub-list > li::before {
            content: counter(sublist) ") ";
        }

        /* Tanda Tangan */
        .signature-block {
            margin-top: 10px;
            width: 55%;
            float: right;
            text-align: center;
            page-break-inside: avoid;
        }
        .signer-name {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 55px;
        }

        /* Helper untuk membersihkan float */
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>

    <div class="header-title">SURAT PENAWARAN</div>

    <div class="date-right">Pekanbaru, <?= $tanggal_surat; ?></div>

    <table class="meta-table">
        <tr>
            <td class="col-label">Nomor</td>
            <td class="col-sep">:</td>
            <td><?= htmlspecialchars($data->nomor_surat_penawaran, ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
        <tr>
            <td class="col-label">Lampiran</td>
            <td class="col-sep">:</td>
            <td><u>1 (satu) Berkas</u></td>
        </tr>
    </table>

    <div class="recipient-block">
        Kepada Yth.:<br>
        Pejabat Pengadaan Barang/Jasa<br>
        Pada Satuan Kerja Pelaksanaan Prasarana Strategis Riau<br>
        di<br>
        &nbsp;&nbsp;&nbsp;&nbsp;Pekanbaru
    </div>

    <table class="meta-table">
        <tr>
            <td class="col-label">Perihal</td>
            <td class="col-sep">:</td>
            <td>Penawaran Pekerjaan <b><u>Konsultan Individual <?= htmlspecialchars($jabatan, ENT_QUOTES, 'UTF-8'); ?></u><br/><?= htmlspecialchars($data->nama_paket, ENT_QUOTES, 'UTF-8'); ?></b></td>
        </tr>
    </table>

    <p>
        Sehubungan dengan Undangan Pejabat Pengadaan dengan Pasca kualifikasi dan Dokumen Pengadaan Nomor : <b><?= htmlspecialchars($data->nomor_undangan, ENT_QUOTES, 'UTF-8'); ?></b> tanggal <b><?= $tanggal_surat; ?></b> dan setelah kami pelajari dengan seksama Dokumen Pengadaan, Berita Acara Pemberian Penjelasan <i>[dan Adendum Dokumen Pengadaan]</i>, dengan ini kami mengajukan penawaran Administrasi untuk pekerjaan <strong><u>Konsultan Individual <?= htmlspecialchars($jabatan, ENT_QUOTES, 'UTF-8'); ?></u></strong> dengan Total Penawaran Biaya sebesar Rp. <?= number_format($data->total_penawaran, 0, ",", "."); ?>,- (<?= htmlspecialchars($total_penawaran_terbilang, ENT_QUOTES, 'UTF-8'); ?>).
    </p>

    <p>
        Penawaran Administrasi ini sudah memperhatikan ketentuan dan persyaratan yang tercantum dalam Dokumen Pengadaan untuk melaksanakan pekerjaan tersebut.
    </p>

    <p>
        Penawaran ini berlaku selama <strong><?= (int)$data->durasi_pelaksanaan; ?> (<?= htmlspecialchars($durasi_pelaksanaan_terbilang, ENT_QUOTES, 'UTF-8'); ?>)</strong> hari kalender sejak batas akhir pemasukan Dokumen Penawaran.
    </p>

    <p>Sesuai dengan persyaratan Dokumen Pengadaan, bersama Penawaran Administrasi ini kami lampirkan:</p>

    <ol>
        <li>Dokumen usulan teknis, terdiri dari:
            <ol class="alpha-list">
                <li>Kualifikasi Tenaga Ahli, terdiri dari:
                    <ol class="sub-list">
                        <li>Daftar Riwayat Hidup;</li>
                        <li>Surat pernyataan kesediaan untuk ditugaskan;</li>
                    </ol>
                </li>
            </ol>
        </li>

        <li>Dokumen penawaran biaya, yang terdiri dari:
            <ol class="alpha-list">
                <li>Rekapitulasi Penawaran Biaya;</li>
                <li>Rincian Biaya Langsung Personil (<em>remuneration</em>);</li>
                <li>Rincian Biaya Langsung Non-Personil (<em>direct reimburseable cost</em>);</li>
            </ol>
        </li>

        <li>Data Kualifikasi; dan</li>
        <li><i>[Dokumen lain yang dipersyaratkan].</i></li>
    </ol>

    <p>
        Dengan disampaikannya Penawaran Administrasi ini, maka kami menyatakan sanggup dan akan tunduk pada semua ketentuan yang tercantum dalam Dokumen Pengadaan.
    </p>

    <div class="clearfix">
        <div class="signature-block">
            Konsultan Individual
            <div class="signer-name"><?= htmlspecialchars($data->nama, ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
    </div>

</body>
</html>