<?php
// Load the custom helper file
helper('custom');

date_default_timezone_set('Asia/Jakarta');

// Pre-calculate values to avoid redundant function calls in the template
$total_penawaran_terbilang = terbilang_rupiah($data->total_penawaran);
$total_nominal_terbilang = terbilang_rupiah($data->nominal_kontrak);
$durasi_pelaksanaan_terbilang = terbilang_angka($data->durasi_pelaksanaan);
$jabatan = ucwords(strtolower($data->jabatan));
$tanggal_surat = tanggal_indonesia($data->tanggal_surat_penawaran);

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Surat Perintah Kerja (SPK)</title>
    <style>
        /* === PENGATURAN KERTAS === */
        @page {
            size: A4;
            /* Margin bawah besar (3cm) untuk menampung Footer Paraf tanpa tertimpa */
            margin: 1.5cm 1.2cm 3cm 2cm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            line-height: 1.3;
            color: #000;
        }

        /* === FOOTER PARAF (Fixed Position) === */
        /* Ini akan muncul otomatis di posisi yang sama pada setiap halaman */
        .footer-paraf {
            position: fixed;
            bottom: -2.5cm;
            right: 2.5cm;
            width: 150px;
            z-index: 9999;
        }

        .table-paraf {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            font-size: 9pt;
            background-color: #fff;
        }

        .table-paraf th {
            border: 1px solid #000;
            background-color: #f2f2f2;
            /* Abu-abu header */
            font-weight: bold;
            text-align: center;
            padding: 4px;
            color: #000;
        }

        .table-paraf td {
            border: 1px solid #000;
            height: 35px;
            /* Tinggi kotak kosong paraf */
        }

        /* === TABLE STYLING === */
        table.main-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            margin-bottom: 0;
        }

        table.main-table td,
        table.main-table th {
            border: 1px solid #000;
            padding: 5px 8px;
            vertical-align: top;
        }

        /* --- HEADER (GAMBAR KOP SURAT) --- */
        .header-img-container {
            width: 100%;
            text-align: center;
            margin-bottom: 20px;
            /* PENTING: Jangan gunakan position: fixed.
                Biarkan statis agar hanya muncul di awal dokumen (Halaman 1).
            */
        }

        .header-img {
            width: 100%;
            /* Lebar mengikuti kertas */
            height: auto;
            /* Tinggi proporsional */
            max-height: 150px;
            /* Batasi tinggi agar tidak terlalu besar */
            object-fit: contain;
        }

        /* Kolom Kiri (Header Section) */
        .col-header {
            width: 25%;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
        }

        /* Kolom Kanan (Content Section) */
        .col-content {
            width: 75%;
        }

        /* Nested Table (Untuk isian titik dua agar rapi) */
        table.inner-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        table.inner-table td {
            border: none;
            padding: 1px;
            vertical-align: top;
        }

        .label-inner {
            width: 22%;
        }

        .sep-inner {
            width: 2%;
            text-align: center;
        }

        /* Typography Helpers */
        .text-center {
            text-align: center;
        }

        .text-bold {
            font-weight: bold;
        }

        .text-justify {
            text-align: justify;
        }

        .text-underline {
            text-decoration: underline;
        }

        /* Page Break Helper */
        .page-break {
            page-break-before: always;
        }

        /* === LIST STYLING (SYARAT UMUM) === */
        ol.syarat-list {
            list-style-type: none;
            padding-left: 0;
            margin: 0;
        }

        ol.syarat-list>li {
            margin-bottom: 10px;
            position: relative;
            padding-left: 20px;
            text-align: justify;
        }

        /* Menggunakan CSS counter agar nomor urut konsisten jika pindah halaman */
        ol.syarat-list {
            counter-reset: item;
        }

        ol.syarat-list>li:before {
            content: counter(item) ".";
            counter-increment: item;
            position: absolute;
            left: 0;
            font-weight: bold;
        }

        /* Untuk halaman 3, kita set start counter manual */
        ol.syarat-list.continue {
            counter-reset: item 5;
        }

        ol.syarat-list.continue2 {
            counter-reset: item 11;
        }

        ol.syarat-list.continue3 {
            counter-reset: item 14;
        }

        ol.syarat-list.continue4 {
            counter-reset: item 16;
        }

        ol.syarat-list.continue5 {
            counter-reset: item 19;
        }

        ol.syarat-list.continue6 {
            counter-reset: item 22;
        }

        ol.syarat-list.continue7 {
            counter-reset: item 23;
        }

        /* Sub-list (a, b, c) */
        ol.sub-list {
            list-style-type: lower-alpha;
            padding-left: 20px;
            margin-top: 2px;
            margin-bottom: 0;
        }

        /* Sub-sub-list (i, ii, iii) */
        .sub-sub-list {
            list-style-type: lower-roman;
            padding-left: 25px;
            margin-top: 2px;
            margin-bottom: 0;
        }

        /* === BORDER HALAMAN (SYARAT UMUM) === */
        /* Trik ini menggunakan elemen fixed-position untuk membuat border
           yang muncul di setiap halaman SETELAH elemen ini didefinisikan. */

        /* Cover untuk menutupi border pada halaman pertama */
        .first-page-cover {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #fff;
            z-index: -5;
            /* Di atas border (-10) tapi di bawah konten (auto/0) */
        }

        .page-border {
            position: fixed;
            top: 0cm;
            /* Sesuaikan dengan margin halaman */
            left: 0cm;
            /* Sesuaikan dengan margin halaman */
            right: 0cm;
            /* Sesuaikan dengan margin halaman */
            bottom: 0cm;
            /* Sesuaikan dengan margin halaman */
            border: 1px solid #000;
            z-index: -10;
            /* Pastikan border di belakang konten */
        }
    </style>
</head>

<body>

    <div class="first-page-cover"></div>

    <div class="header-img-container">
        <img src="<?= base_url() ?>public/img/logo_kop_jl_bakti.png" class="header-img" alt="Kop Surat">
    </div>

    <div class="footer-paraf">
        <table class="table-paraf">
            <thead>
                <tr>
                    <th style="width: 3cm">Penyedia</th>
                    <th style="width: 3cm">PPK</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>

    <table class="main-table">
        <tr>
            <td class="col-header" rowspan="2">SURAT PERINTAH<br>KERJA (SPK)</td>
            <td class="col-content text-center">
                <strong>Satuan Kerja :</strong><br>Pelaksanaan Prasarana Strategis Riau
            </td>
        </tr>
        <tr>
            <td class="col-content">
                <div class="text-center text-bold" style="margin-bottom: 5px;">Nomor dan Tanggal SPK</div>
                <table class="inner-table">
                    <tr>
                        <td class="label-inner">Nomor</td>
                        <td class="sep-inner">:</td>
                        <td class="text-bold"><?= $data->nomor_kontrak; ?></td>
                    </tr>
                    <tr>
                        <td class="label-inner">Tanggal</td>
                        <td class="sep-inner">:</td>
                        <td class="text-bold"><?= tanggal_indonesia($data->tanggal_kontrak); ?></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="col-header">NAMA PEJABAT<br>PENANDATANGAN<br>KONTRAK</td>
            <td class="col-content">
                <table class="inner-table">
                    <tr>
                        <td class="label-inner">Nama</td>
                        <td class="sep-inner">:</td>
                        <td><?= $data->pejabat_ppk; ?></td>
                    </tr>
                    <tr>
                        <td class="label-inner">NIP</td>
                        <td class="sep-inner">:</td>
                        <td><?= $data->nip_pejabat_ppk; ?></td>
                    </tr>
                    <tr>
                        <td class="label-inner">Jabatan</td>
                        <td class="sep-inner">:</td>
                        <td>Pejabat Pembuat Komitmen Prasarana Strategis Riau</td>
                    </tr>
                    <tr>
                        <td class="label-inner">Berkedudukan di</td>
                        <td class="sep-inner">:</td>
                        <td><?= $data->kedudukan_pejabat_ppk; ?></td>
                    </tr>
                </table>
                <div class="text-justify" style="margin-top: 5px; font-size: 9pt;">
                    yang bertindak untuk dan atas nama*) Pemerintah Indonesia c.q. Kementerian Pekerjaan Umum c.q
                    Direktorat Jenderal Prasarana Strategis c.q. Satuan Kerja Pelaksanaan Prasarana Strategis Riau
                    berdasarkan Surat Keputusan Menteri Pekerjaan Umum Nomor:
                    <?= $data->nomor_surat_keputusan_menteri; ?> Tanggal
                    <?= tanggal_indonesia($data->tanggal_surat_keputusan_menteri); ?> Tentang Perubahan Atas Keputusan
                    Menteri Pekerjaaan Umum Nomor: <?= $data->nomor_perubahan_keputusan_menteri; ?> Tentang Pengangkatan
                    Atasan/Atasan Langsung/Pembantu Atasan Langsung Kuasa Pengguna Anggaran/Barang dan Pejabat
                    Perbendaharaan Satuan Kerja di Lingkungan Direktorat Jenderal Prasarana Strategis, Kementerian
                    Pekerjaan Umum selanjutnya disebut <strong>“Pejabat Penandatanganan Kontrak”</strong>, dengan:
                </div>
            </td>
        </tr>
        <tr>
            <td class="col-header">NAMA PENYEDIA</td>
            <td class="col-content">
                <table class="inner-table">
                    <tr>
                        <td class="label-inner">Nama</td>
                        <td class="sep-inner">:</td>
                        <td><?= $data->nama; ?></td>
                    </tr>
                    <tr>
                        <td class="label-inner">Jabatan</td>
                        <td class="sep-inner">:</td>
                        <td><?= ucwords(strtolower($data->jabatan)); ?></td>
                    </tr>
                    <tr>
                        <td class="label-inner">Berdasarkan NIK</td>
                        <td class="sep-inner">:</td>
                        <td><?= $data->nik; ?></td>
                    </tr>
                </table>
                <div class="text-justify" style="margin-top: 5px;">
                    Yang bertindak untuk dan atas nama Konsultan Perorangan selanjutnya disebut
                    <strong>“Penyedia”</strong>
                </div>
            </td>
        </tr>
        <tr>
            <td class="col-header">WAKIL SAH PEJABAT<br>PENANDATANGANAN<br>KONTRAK</td>
            <td class="col-content">
                Wakil Sah Pejabat Penandatanganan Kontrak<br>Untuk Pejabat Penandatanganan Kontrak
                <table class="inner-table">
                    <tr>
                        <td class="label-inner">Nama</td>
                        <td class="sep-inner">:</td>
                        <td>-</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="col-header">PAKET PENGADAAN:<br><span style="font-weight: normal;">Konsultan Individual Team
                    Leader</span></td>
            <td class="col-content">
                <div class="text-bold">NOMOR DAN TANGGAL SURAT UNDANGAN PENGADAAN LANGSUNG :</div>
                <table class="inner-table" style="margin-bottom: 5px;">
                    <tr>
                        <td class="label-inner">Nomor</td>
                        <td class="sep-inner">:</td>
                        <td><?= $data->nomor_surat_undangan_pengadaan; ?></td>
                    </tr>
                    <tr>
                        <td class="label-inner">Tanggal</td>
                        <td class="sep-inner">:</td>
                        <td><?= tanggal_indonesia($data->tanggal_surat_undangan_pengadaan); ?></td>
                    </tr>
                </table>
                <div class="text-bold">NOMOR DAN TANGGAL SURAT BERITA ACARA PENGADAAN LANGSUNG :</div>
                <table class="inner-table">
                    <tr>
                        <td class="label-inner">Nomor</td>
                        <td class="sep-inner">:</td>
                        <td><?= $data->nomor_surat_berita_acara_pengadaan; ?></td>
                    </tr>
                    <tr>
                        <td class="label-inner">Tanggal</td>
                        <td class="sep-inner">:</td>
                        <td><?= tanggal_indonesia($data->tanggal_surat_berita_acara_pengadaan); ?></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="col-content text-justify">
                <strong>SUMBER DANA:</strong> APBN DIPA Satuan Kerja Pelaksanaan Prasarana Strategis Riau Nomor:
                <strong><?= $data->nomor_dipa; ?> Tanggal <?= tanggal_indonesia($data->tanggal_dipa); ?></strong> Tahun
                Anggaran <?= $data->tahun_anggaran; ?> untuk Mata Anggaran Kegiatan <?= $data->mata_anggaran; ?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <span class="text-bold">Harga Kontrak</span> Adalah Sebesar <span class="text-bold">Rp
                    <?= number_format($data->nominal_kontrak, 0, ",", "."); ?>,-
                    (<?= htmlspecialchars($total_nominal_terbilang, ENT_QUOTES, 'UTF-8'); ?>)</span>
            </td>
        </tr>
    </table>

    <div class="page-break"></div>

    <table class="main-table">
        <tr>
            <td colspan="2">
                <div class="text-bold">SISTEM PEMBAYARAN</div>
                <ol style="margin-top: 0; padding-left: 20px; margin-bottom: 5px;">
                    <li>Pembayaran untuk Kontrak ini Dilakukan ke <strong><?= $data->bank_nama; ?></strong> atas nama
                        <strong><?= $data->bank_atas_nama; ?></strong> Rekening Nomor:
                        <?= $data->bank_nomor_rekening; ?> ; dan
                    </li>
                    <li>Pembayaran Dilakukan dengan <?= $data->bank_pembayaran; ?></li>
                </ol>
                <strong>Jenis Kontrak</strong> : Waktu Penugasan
            </td>
        </tr>

        <tr>
            <td colspan="2">
                <strong>Waktu Pelaksanaan Pekerjaan : <?= (int) $data->durasi_pelaksanaan; ?>
                    (<?= htmlspecialchars($durasi_pelaksanaan_terbilang, ENT_QUOTES, 'UTF-8'); ?>)</strong> Hari
                Kalender
            </td>
        </tr>

        <tr>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                <strong>Untuk dan Atas Nama<br>Pejabat Penandatanganan Kontrak<br>Prasarana Strategis<br>Satuan Kerja
                    Pelaksanaan<br>Prasarana Strategis Riau</strong>
                <br><br><br><br><br>
                <span class="text-bold text-underline"><?= $data->pejabat_ppk; ?></span><br>
                NIP. <?= $data->nip_pejabat_ppk; ?>
            </td>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                <strong>Untuk dan Atas Nama Penyedia Jasa<br>Konsultan
                    Individual<br><?= ucwords(strtolower($data->jabatan)); ?></strong>
                <br><br><br><br><br><br><br>
                <span class="text-bold text-underline"><?= $data->nama; ?></span><br>
                <i><?= ucwords(strtolower($data->jabatan)); ?></i>
            </td>
        </tr>
    </table>

    <br>

    <div class="page-border"></div>

    <div>
        <div style="border: 1px solid black;">
            <div class="text-center">
                <span class="text-bold text-underline">SYARAT UMUM</span><br>
                <span class="text-bold">SURAT PERINTAH KERJA (SPK)</span>
            </div>
        </div>
        <div style="padding: 0.2cm">
            <ol class="syarat-list">
                <li>
                    <strong>LINGKUP PEKERJAAN</strong><br>
                    Penyedia yang ditunjuk berkewajiban untuk menyelesaikan pekerjaan dalam jangka waktu yang
                    ditentukan, dengan mutu sesuai Kerangka Kerja dan harga Sesuai SPK.
                </li>
                <li>
                    <strong>HUKUM YANG BERLAKU</strong><br>
                    Keabsahan, interprestasi dan pelaksanaan SPK ini didasarkan kepada hukum Republik Indonesia.
                </li>
                <li>
                    <strong>PENYEDIA JASA KONSULTANSI MANDIRI</strong><br>
                    Penyedia berdasarkan SPK ini bertanggung jawab penuh terhadap personel serta pekerjaan yang
                    dilakukan
                </li>
                <li>
                    <strong>BIAYA SPK</strong>
                    <ol class="sub-list">
                        <li>Biaya SPK telah memperhitungkan keuntungan, beban pajak dan biaya <i>overhead</i> serta
                            biaya asuransi (apabila dipersyaratkan);</li>
                        <li>Rincian biaya SPK sesuai dengan rincian yang tercantum dalam rekapitulasi penawaran biaya.
                        </li>
                    </ol>
                </li>
                <li>
                    <strong>HAK KEPEMILIKAN</strong>
                    <ol class="sub-list">
                        <li>Pejabat Penandatanganan Kontrak berhak atas kepemilikan semua barang/bahan yang terkait
                            langsung atau disediakan sehubungan dengan jasa yang diberikan oleh Penyedia Jasa kepada
                            Pejabat Penandatanganan Kontrak. Jika diminta oleh Pejabat Penandatanganan Kontrak maka
                            Penyedia Jasa berkewajiban untuk membantu secara optimal pengalihan hak kepemilikan tersebut
                            kepada Pejabat Penandatanganan Kontrak dengan hukum yang berlaku; dan</li>
                        <li>Hak kepemilikan atas peralatan dan barang/bahan yang disediakan oleh Pejabat Penandatanganan
                            Kontrak tetap pada Pejabat Penandatanganan Kontrak dan semua peralatan tersebut harus
                            dikembalikan kepada Pejabat Penandatanganan Kontrak pada saat SPK berakhir atau jika tidak
                            diperlukan lagi oleh Penyedia Jasa. Semua peralatan tersebut harus dikembalikan dalam
                            kondisi yang sama pada saat diberikan kepada Penyedia Jasa dengan pengecualian keausan
                            akibat pemakaian yang wajar.</li>
                    </ol>
                </li>
            </ol>
            <div class="page-break"></div>
            <ol class="syarat-list continue">
                <li>
                    <strong>HAK KEPEMILIKAN</strong>
                    <ol class="sub-list">
                        <li>Setiap tindak yang diisyaratkan atau diperbolehkan untuk dilakukan dan setiap dokumen yang
                            diisyaratkan atau diperbolehkan untuk dibuat berdasarkan Kontrak ini oleh Pejabat
                            Penandatanganan Kontrak hanya dapat dilakukan atau dibuat oleh Wakil Sah Pejabat
                            Penandatanganan Kontrak yang disebutkan dalam SPK; dan</li>
                        <li>Wakil Sah Pejabat Penandatanganan Kontrak Adalah Ketua Tim Pelaksanaan yang kewenangannya
                            diatur dalam Surat Keputusan dari Pejbat Penandatanganan Kontrak dan harus disampaikan
                            kepada Penyedia Jasa.</li>
                    </ol>
                </li>
                <li>
                    <strong>PERPAJAKAN</strong>
                    <br>Penyedia Jasa Berkewajiban untuk membayar semua pajak, bea, retribusi dan pungutan lain yang sah
                    yang dibebankan oleh hukum yang berlaku atas pelaksanaan SPK. Semua pengeluaran perpajakan ini
                    dianggap telah termasuk dalam biaya SPK.
                </li>
                <li>
                    <strong>PENGALIHAN DAN/ATAU SUBKONTRAK</strong>
                    <br>Penyedia Jasa dilarang untuk mengalihkan dan/atau mensubkontrakkan Sebagian atau seluruh
                    pekerjaan. Pengalihan seluruh pekerjaan hanya diperbolehkan dalam hal pergantian nama Penyedia Jasa,
                    baik sebagai akibat peleburan (merger) atau akibat lainnya.
                </li>
                <li>
                    <strong>JADWAL</strong>
                    <ol class="sub-list">
                        <li>SPK ini berlaku efektif pada tanggal penandatanganan oleh para pihak atau pada tanggal yang
                            ditetapkan dalam Surat Perintah Mulai Kerja (SPMK);</li>
                        <li>Waktu Pelaksanaan SPK adadlah sejak tanggal mulai kerja yang tercantum dalam SPMK;</li>
                        <li>Penyedi Jasa harus menyelesaikan pekerjaan sesuai jadwal yang ditentukan; dan</li>
                        <li>Apabila Penyedia jasa tidak dapat menyelesaikan pekerjaan sesuai jadwal karena keadaan di
                            luar pengendaliannya dan Penyedia Jasa telah melaporkan kejadian tersebut kepada Pejabat
                            Penandatanganan Kontrak, maka Pejabat Penandatanganan Kontrak dapat melakukan penjadwalan
                            Kembali pelaksanaan tugas Penyedia dengan Adendum SPK.</li>
                    </ol>
                </li>
                <li>
                    <strong>PEMBERIAN KESEMPATAN</strong>
                    <ol class="sub-list">
                        <li>Dalam hal diperkirakan Penyedia gagal menyelesaikan pekerjaan sampai Waktu Penyelesaian
                            Pekerjaan berakhir, namun Pejabat Penandatanganan Kontrak menilai bahwa Penyedia jasa mampu
                            menyelesaikan pekerjaan, Pejabat Penandatanganan Kontrak dapat memberukan kesempatan kepada
                            Penyedia Jasa untuk menyelesaikan pekerjaan;</li>
                        <li>
                            Pemberian kesempatan kepada Penyedia Jasa untuk menyelesaikan pekerjaan dimuat dalam Adendum
                            SPK yang di dalamnya mengatur:
                            <ol class="sub-sub-list">
                                <li>Waktu pemberian kesempatan penyelesaian pekerjaan; dan</li>
                                <li>Pengenaan sanksi denda keterlambatan kepada Penyedia Jasa.</li>
                            </ol>
                        </li>
                        <li>Pemberian Kesempatan kepada Penyedia Jasa menyelesaikan pekerjaan dengan diikuti pengenaan
                            denda keterlambatan;</li>
                        <li>
                            Pemberian Kesempatan kepada Penyedia Jasa untuk menyelesaikan pekerjaan dilaksanakan dengan
                            ketentuan:
                            <ol class="sub-sub-list">
                                <li>Tidak dapat melampui Tahun Anggaran; dan</li>
                                <li>Paling lama sama dengan Waktu Penyelesaian Awal.</li>
                            </ol>
                        </li>
                    </ol>
                </li>
                <li>
                    <strong>ASURANSI</strong>
                    <ol class="sub-list">
                        <li>
                            Apabila dipersyaratkan, Penyedia Jasa menyediakan asuransi sejak SPMK dengan tanggal
                            selesainya pemeliharaan untuk:
                            <ol class="sub-sub-list">
                                <li>Semua barang dan peralatan yang mempunyai risiko tinggi terjadinya kecelakaan,
                                    pelaksanaan pekerjaan, serta pekerja untuk pelaksanaan pekerjaan, atas segala risiko
                                    terhadap kecelakaan, kerusakan, kehilangan serta risiko lain yang tidak dapat
                                    diduga; dan terhadap kecelakaan, kerusakan, kehilangan serta risiko lain yang tidak
                                    dapat diduga; dan</li>
                                <li>Pihak ketiga sebagai akibat kecelakaan di tempat kerjanya.</li>
                            </ol>
                        </li>
                        <li>Besarnya asuransi sudah diperhitungkan dalam penawaran dan termasuk dalam biaya SPK.</li>
                    </ol>
                </li>
            </ol>
            <ol class="syarat-list continue2">
                <li>
                    <strong>PENANGGUNGAN DAN RISIKO</strong>
                    <ol class="sub-list">
                        <li>
                            Penyedia Jasa berkewajiban untuk melindungi, membebaskan dan menanggung tanpa batas Pejabat
                            Penandatanganan Kontrak beserta instansinya terhadap semua bentuk tuntutan, tanggung jawab,
                            kewajiban, kehilangan, kerugian, denda, gugatan atau tuntutan hukum, proses pemeriksaan
                            hukum dan biaya yang dikenakan terhadap Pejabat Penandatanganan Kontrak beserta instansinya
                            (kecuali kerugian yang mendasari tuntutan tersebut disebabkan kesalahan atau kelalaian berat
                            Pejabat Penandatanganan Kontrak) sehubungan dengan klaim yang timbul dari hal-hal berikut
                            terhitung sejak tanggal mulai kerja sampai dengan tanggal penandatanganan berita acara
                            penyerahan akhir.
                            <ol class="sub-sub-list">
                                <li>Kehilangan atau kerusakan peralatan dan harta benda Penyedi Jasa;</li>
                                <li>Cedera tubuh, sakit atau kematian Penyedia Jasa; dan/atau</li>
                                <li>Kehilangan atau kerusakan harta benda, cedera tubuh, sakit atau kematian pihak lain
                                </li>
                            </ol>
                        </li>
                        <li>Terhitung sejak tanggal mulai kerja sampai dengan tanggal penandatanganan berita acara serah
                            terima, semua risiko kehilangan atau kerusakan hasil pekerjaan ini merupakan risiko Penyedia
                            Jasa, kecuali kerugian atau kerusakan tersebut diakibatkan oleh kesalahan atau kelalaian
                            Pejabat Penandatanganan Kontrak; dan</li>
                        <li>Pertanggungan asuransi yang dimiliki oleh Penyedia Jasa dibatasi sebesar nilai pekerjaan
                            yang dilaksanakan oleh Penyedia Jasa. Pejabat Penandatanganan Kontrak dapat memerintahkan
                            kepada pihak lain untuk melakukan pengawasan dan pemeriksaan atas nama pelaksanaan pekerjaan
                            yang dilaksanakan oleh Penyedia Jasa.</li>
                    </ol>
                </li>
                <li>
                    <strong>PENGAWASAN DAN PEMERIKSAAN</strong><br>
                    Pejabat Penandatanganan Kontrak dan Tim Pelaksana berwenang melakukan pengawasan dan pemeriksaan
                    terhadap pelaksanaan pekerjaan yang dilaksanakan oleh Penyedia Jasa. Pejabat Penandatanganan Kontrak
                    dapat memerintahkan kepada pihak lain untuk melakukan pengawasan dan pemeriksaan atas semua
                    pelaksanaan pekerjaan yang dilaksanakan oleh Penyedia Jasa.
                </li>
                <li>
                    <strong>LAPORAN HASIL PEKERJAAN</strong><br>
                    <?= $syarat_umum->laporan ?? ''; ?>
                </li>
            </ol>
            <ol class="syarat-list continue3">
                <li>
                    <strong>PRODUK HASIL PEKERJAAN (OUTPUT)</strong><br />
                    <?= $syarat_umum->hasil ?? ''; ?>
                </li>
                <li>
                    <strong>WAKTU PENYELESAIAN PEKERJAAN</strong>
                    <ol class="sub-list">
                        <li>Kecuali SPK diputuskan lebih awal, Penyedia Jasa berkewajiban untuk memulai pelaksanaan
                            pekerjaan pada tanggal mulai kerja, serta menyelesaikan Pekerjaan selambat-lambatnya pada
                            tanggal penyelesaian yang ditetapkan dalam SPMK;</li>
                        <li>Jika Pekerjaan tidak selesai pada tanggal penyelesaian disebabkan karena kesalahan atau
                            kelalaian Penyedia Jasa maka Penyedia Jasa dikenakan sanksi berupa denda keterlambatan;</li>
                        <li>Jika Keterlambatan tersebut disebabkan oleh peristiwa kompensasi maka Pejabata
                            Penandatanganan Kontrak memberikan tambahan perpanjangan waktu penyelesaian pekerjaan; dan
                        </li>
                        <li>Tanggal penyelesaian yang dimaksud dalam ketentuan ini adalah tanggal penyelesaian semua
                            pekerjaan.</li>
                    </ol>
                </li>
            </ol>
            <ol class="syarat-list continue4">
                <li>
                    <strong>TUGAS DAN TANGGUNG JAWAB PENYEDIA JASA</strong><br>
                    <?= $syarat_umum->tugas_tanggung_jawab ?? ''; ?>
                </li>
                <li>
                    <strong>SERAH TERIMA PEKERJAAN</strong>
                    <ol class="sub-list">
                        <li>Setelah pekerjaan selesai, Penyedia Jasa mengajukan permintaan secara tertulis kepada
                            Pejabat Penandatanganan Kontrak untuk penyerahan Pekerjaan;</li>
                        <li>Sebelum dilakukan serah terima pekerjaan, Pejabat Penandatanganan Kontrak dan Tim Pelaksana
                            melakukan pemeriksaan terhadap hasil pekerjaan;</li>
                        <li>Pejabat Penandatanganan Kontrak dalam melakukan pemeriksaan hasil pekerjaan dapat dibantu
                            oleh tim pelaksana;</li>
                        <li>Apabila terdapat kekurangan-kekurangan dan/atau cacat hasil pekerjaan, Penyedia Jasa wajib
                            memperbaiki/menyelesaikannya, atas perintah Pejabat Penandatanganan Kontrak dan Tim
                            Pelaksana;</li>
                        <li>Pejabat Penandatanganan Kontrak dan Tim Pelaksana menerima hasil pekerjaan setelah seluruh
                            hasil pekerjaan dilaksanakan sesuai dengan SPK; dan</li>
                        <li>Pembayaran dilakukan sebesar 100% (seratus persen) dari biaya SPK setelah pekerjaan selesai.
                        </li>
                    </ol>
                </li>
                <li>
                    <strong>PERUBAHAN SPK SERAH</strong>
                    <ol class="sub-list">
                        <li>SPK hanya dapat diubah melalui Adendum SPK;</li>
                        <li>
                            Perubahan SPK dapat dilaksanakan dalam hal terdapat perbedaan antara kondisi lapangan pada
                            saat pelaksanaan dengan SPK dan disetujui oleh para pihak, meliputi:
                            <ol class="sub-sub-list">
                                <li>Menambah atau mengurangi volume yang tercantum dalam SPK;</li>
                                <li>Menambah dan/atau mengurangi jenis kegiatan;</li>
                                <li>Mengubah Kerangka Acuan Kerja sesuai dengan kondisi lapangan; dan/atau</li>
                                <li>Mengubah jadwal pelaksanaan pekerjaan.</li>
                            </ol>
                        </li>
                        <li>Untuk kepentingan perubahan SPK, Pejabat Penandatanganan Kontrak dapat meminta pertimbangan
                            dari Tim Pelaksana.</li>
                    </ol>
                </li>
            </ol>
            <ol class="syarat-list continue5">
                <li>
                    <strong>KEADAAN KAHar</strong>
                    <ol class="sub-list">
                        <li>
                            Dalam hal terjadi keadaan kahar, Pejabat Penandatanganan Kontrak atau Penyedia Jasa
                            memberitahukan tentang terjadinya Keadaan Kahar kepada salah satu pihak secara tertulis
                            dengan ketentuan:
                            <ol class="sub-sub-list">
                                <li>Dalam waktu paling lambat 14 (empat belas) hari kalender sejak menyadari atau
                                    seharusnya menyadari atas kejadian atau terjadinya Keadaan Kahar;</li>
                                <li>Menyertakan bukti Keadaan Kahar; dan</li>
                                <li>Menyerahkan hasil identifikasi kewajiban dan kinerja pelaksanaan yang terhambat
                                    dan/atau akan terlambat akibat Keadaan Kahar tersebut.</li>
                            </ol>
                        </li>
                        <li>Dalam Keadaan Kahar, kegagalan salah satu Pihak untuk memenuhi kewajibannya yang ditentukan
                            dalam SPK bukan merupakan cedera janji atau wanprestasi apabila telah dilakukan sesuai pada
                            poin a. kewajiban yang dimaksud adalah hanya kewajiban dan kinerja pelaksanaan terhadap
                            pekerjaan/bagian pekerjaan yang terdampak dan/atau akan terdampak akibat dari Keadaan Kahar.
                        </li>
                    </ol>
                </li>
                <li>
                    <strong>PERISTIWA KOMPENSASI</strong>
                    <ol class="sub-list">
                        <li>
                            Peristiwa Kompensasi dapat diberikan kepada penyedia dalam hal sebagai berikut:
                            <ol class="sub-sub-list">
                                <li>Pejabat Penandatanganan Kontrak mengubah jadwal yang dapat memengaruhi pelaksanaan
                                    pekerjaan;</li>
                                <li>Keterlamatan pembayaran kepada Penyedia Jasa;</li>
                                <li>Pejabat Penandatanganan Kontrak tidak memberikan gambaran-gambaran, Kerangka Acuan
                                    Kerja dan/atau instruksi sesuai jadwal yang dibutuhkan;</li>
                                <li>Penyedia Jasa belum bisa masuk ke lokasi sesuai jadwal;</li>
                                <li>Pejabat Penandatanganan Kontrak memerintahkan penundaan pelaksanaan pekerjaan;</li>
                                <li>Pejabat Penandatanganan Kontrak memerintahkan mengatasi kondisi tertentu yang tidak
                                    dapat diduga sebelumnya dan disebabkan oleh Pejabat Penandatanganan Kontrak;</li>
                            </ol>
                        </li>
                        <li>Jika Peristiwa Kompensasi mengakibatkan pengeluaran tambahan dan/atau keterlambatan
                            penyelesaian pekerjaan maka Pejabat Penandatanganan Kontrak berkewajiban untuk membayar
                            Ganti rugi dan/atau memberikan perpanjangan waktu penyelesaian pekerjaan;</li>
                        <li>Ganti rugi hanya dapat dibayarkan jika berdasarkan data penunjang dan perhitungan kompensasi
                            yang diajukan penyedia kepada Pejabat Penandatanganan Kontrak, dapat dibuktikan kerugian
                            nyata akibat dari Peristiwa Kompensasi;</li>
                        <li>Perpanjangan waktu penyelesaian pekerjaan hanya dapat diberikan jika berdasarkan data
                            penunjang dan perhitungan kompensasi yang diajukan oleh Penyedia Jasa kepada Pejabat
                            Penandatanganan Kontrak, dapat dibuktikan perlunya tambahan waktu akibat Peristiwa
                            Kompensasi; dan</li>
                        <li>Penyedia Jasa tidak berhak atas Ganti rugi dan/atau perpanjangan waktu penyelesaian
                            pekerjaan jika Penyedia Jasa gagal atau lalai untuk memberikan peringatan dini dalam
                            mengantisipasi atau mengatasi dampak Peristiwa Kompensasi.</li>
                    </ol>
                </li>
                <li>
                    <strong>PERPANJANGAN WAKTU PERPANJANGAN WAKTU</strong>
                    <ol class="sub-list">
                        <li>Jika terjadi Peristiwa Kompensasi sehingga penyelesaian pekerjaan akan melampui tanggal
                            penyelesaian maka Penyedia Jasa berhak untuk meminta perpanjangan tanggal penyelesaian
                            berdasarkan data penunjang. Pejabat Penandatanganan Kontrak berdasarkan pertimbangan Tim
                            Pelaksana memperpanjang tanggal penyelesaian pekerjaan secara tertulis perpanjangan tanggal
                            penyelesaian harus dilakukan melalui Adendum SPK;</li>
                        <li>Pejabat Penandatanganan Kontrak dapat menyetujui perpanjangan waktu pelaksanaan setelah
                            melakukan penelitian terhadap usulan tertulis yang diajukan oleh Penyedia Jasa.</li>
                    </ol>
                </li>
            </ol>
            <ol class="syarat-list continue6">
                <li>
                    <strong>PENGHENTIAN DAN PEMUTUSAN SPK</strong>
                    <ol class="sub-list">
                        <li>Penghentian SPK dapat dilakukan karena terjadi Keadaan Kahar;</li>
                        <li>
                            Dalam hal SPK dihentikan, Pejabat Penandatanganan Kontrak wajib membayar kepada Penyedia
                            Jasa dengan prestasi pekerjaan yang telah dicapai, termasuk:
                            <ol class="sub-sub-list">
                                <li>Biaya langsung pengadaan bahan dan perlengkapan untuk pekerjaan ini, bahan dan
                                    perlengkapan ini harus diserahkan oleh Penyedia Jasa kepada Pejabat Penandatanganan
                                    Kontrak dan selanjutnya menjadi hak milik Pejabat Penandatanganan Kontrak; dan</li>
                                <li>Biaya langsung demobilisasi Penyedia Jasa.</li>
                            </ol>
                        </li>
                        <li>Pemutusan SPK dapat dilakukan oleh Pejabat Penandatanganan Kontrak atau pihak Penyedia Jasa;
                        </li>
                        <li>
                            Mengesampingkan Pasal 1266 dan Pasal 1267 Kitab Undang-undang Hukum Perdata, Pejabat
                            Penandatanganan Kontrak atau Penyedia Jasa melalui pemberitahuan tertulis dapat melakukan
                            pemutusan SPK apabila:
                            <ol class="sub-sub-list">
                                <li>Penyedia terbukti melakukan korupsi, kolusi dan/atau nepotisme (KKN), kecurangan
                                    dan/atau pemalsuan dalam proses pengadaan yang diputuskan oleh instansi yang
                                    berwenang;</li>
                                <li>Pengaduan tentang penyimpangan prosedur, dugaan KKN dan/atau pelanggaran persaingan
                                    sehat dalam pelaksanaan pengadaan dinyatakan benar oleh instansi berwenang;</li>
                                <li>Penyedia Jasa lalai/cedera janji dalam melaksanakan kewajibannya dan tidak
                                    memperbaiki kelalainnya dalam jangka waktu yang telah ditetapkan;</li>
                                <li>Penyedia tanpa persetujuan Pejabat Penandatanganan Kontrak, tidak memulai
                                    pelaksanaan pekerjaan;</li>
                                <li>Penyedia menghentikan pekerjaan dan penghentian ini tidak tercantum dalam program
                                    mutu serta tanpa persetujuan Pejabat Penandatanganan Kontrak;</li>
                                <li>Penyedia Jasa berada dalam keadaan pailit;</li>
                                <li>Penyedia Jasa gagal memperbaiki kinerja setelah mendapat Surat Peringatan sebanyak 3
                                    (tiga) kali;</li>
                                <li>Penyedia Jasa selama Masa SPK gagal memperbaiki Cacat Mutu dalam jangka waktu
                                    ditetapkan oleh Pejabat Penandatanganan Kontrak;</li>
                                <li>Pejabat Penandatanganan Kontrak memerintahkan Penyedia Jasa untuk menunda
                                    pelaksanaan atau kelanjutan pekerjaan dan perintah tersebut tidak ditarik selama 28
                                    (dua puluh delapan) hari kalender; dan/atau</li>
                                <li>Pejabat Penandatanganan Kontrak tidak menerbitkan surat perintah pembayaran untuk
                                    pembayaran tagihan angsuran sesuai dengan yang disepakti sebagaiman tercatum dalam
                                    SPK.</li>
                            </ol>
                        </li>
                        <li>
                            Dalam hal pemutusan SPK dilakukan karena kesalahan Penyedia Jasa:
                            <ol class="sub-sub-list">
                                <li>Penyedia Jasa membayar denda keterlambatan (apabila ada); dan</li>
                                <li>Penyedia dikenakan Sanksi Daftar Hitam.</li>
                            </ol>
                        </li>
                        <li>Dalam hal pemutusan SPK dilakukan karena Pejabat Penandatanganan Kontrak terlibat
                            penyimpangan prosedur, melakukan praktik KKN dan/atau pelanggaran persaingan sehat dalam
                            pelaksanaan pengadaan, maka Pejabat Penandatanganan Kontrak dikenakan sanksi berdasarkan
                            peraturan dan perundang-undangan yang berlaku.</li>
                    </ol>
                </li>
            </ol>
            <ol class="syarat-list continue7">
                <li>
                    <strong>PEMBAYARAN</strong>
                    <ol class="sub-list">
                        <li>
                            Pembayaran prestasi hasil pekerjaan yang disepakati dilakukan oleh Pejabat Penandatanganan
                            Kontrak, dengan ketentuan:’
                            <ol class="sub-sub-list">
                                <li>Penyedia Jasa telah mengajukan tagihan disertai laporan kemajuan hasil pekerjaan;
                                </li>
                                <li>Pembayaran dilakukan dengan <?= strtolower($data->bank_pembayaran); ?></li>
                                <li>Pembayaran harus dipotong denda (apabila ada), dan pajak.</li>
                            </ol>
                        </li>
                        <li>Pembayaran terakhir hanya dilakukan setelah pekerjaan selesai dan Berita Acara Serah Terima
                            ditandatangani;</li>
                        <li>Pejabat Penandatanganan Kontrak dalam kurun waktu 7 (tujuh) hari kerja setelah pengajuan
                            permintaan pembayaran dari Penyedia Jasa harus mengajukan Surat Permintaan Pembayaran kepada
                            Pejabat Penandatanganan Surat Perintah Membayar (PPSPM);</li>
                        <li>Jika terdapat ketidaksesuaian dalam perhitungan angsuran, tidak akan menjadi alasan untuk
                            menunda pembayaran. Pejabat Penandatanganan Kontrak dapat meminta Penyedia Jasa untuk
                            menyampaikan perhitungan prestasi sementara dengan mengesampingkan hal-hal yang sedang
                            menjadi perselisihan; dan</li>
                        <li>Penyedia Jasa berhak menerima pengganti uang harian, transportasi dan penginapan dari
                            Pejabat Penandatanganan Kontrak selama Penyedia Jasa melaksanakan tugas dari Pejabat
                            Penandatanganan Kontrak.</li>
                    </ol>
                </li>
                <li>
                    <strong>DENDA</strong>
                    <ol class="sub-list">
                        <li>Jika Pekerjaan tidak dapat diselesaikan dalam jangka waktu pelaksanaan pekerjaan karena
                            kesalahan atau kelalaian Penyedia Jasa maka Penyedia Jasa berkewajiban untuk membayar denda
                            kepada Pejabat Penandatanganan Kontrak sebesar 1/1000 (satu permil) dari nilai SPK (tidak
                            termasuk PPN) untuk setiap hari keterlambatan; dan</li>
                        <li>Pejabat Penandatanganan Kontrak mengenakan Denda dengan memotong pembyaran prestasi
                            pekerjaan Penyedia Jasa. Pembayaran Denda tidak mengurangi tanggung jawab kontraktual
                            Penyedia Jasa.</li>
                    </ol>
                </li>
                <li>
                    <strong>PENYELESAIAN PERSELISIHAN</strong><br>
                    Pejabat Penandatanganan Kontrak dan Penyedia Jasa berkewajiban untuk berupaya bersungguh-sungguh
                    menyelesaikan secara damai semua perselisihan yang timbul dari atau berhubungan dengan SPK ini atau
                    interprestasinya selama atau setelah pelaksanaan pekerjaan Jika perselisihan tidak dapat
                    diselesaikan secara musyawarah maka perselisihan akan diselesaikan melalui Mediasi, Konsiliasi atau
                    arbitrase.
                </li>
                <li>
                    <strong>LARANGAN PEMBERIAN KOMISI</strong><br>
                    Penyedia Jasa menjamin bahwa tidak ada satu pun personel Pejabat Penandatanganan Kontrak telah atau
                    akan menerima komisi atau keuntungan tidak sah lainnya baik langsung maupun tidak langsung dari SPK
                    ini. Penyedia Jasa menyetujui bahwa pelanggaran syarat ini merupakan pelanggaran yang mendasar
                    terhadap SPK ini.
                </li>
            </ol>
        </div>
    </div>

</body>

</html>