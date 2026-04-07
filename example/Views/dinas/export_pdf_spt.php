<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Tugas</title>
    <style>
        @page {
            size: A4;
            margin: 1cm 1.5cm 1.5cm 1.5cm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            line-height: 1;
            color: #000;
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
            width: 100%;      /* Lebar mengikuti kertas */
            height: auto;     /* Tinggi proporsional */
            max-height: 150px; /* Batasi tinggi agar tidak terlalu besar */
            object-fit: contain;
        }

        /* === HEADER SURAT === */
        .header-title {
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            text-decoration: underline;
            margin-bottom: 5px;
        }

        .header-number {
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 20px;
        }

        /* === BAGIAN 'DALAM RANGKA' === */
        .table-info {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-bottom: 15px;
        }
        .table-info td {
            vertical-align: top;
            padding: 2px;
        }

        /* === TABEL UTAMA === */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000; /* Border luar lebih tebal sesuai gambar */
            font-size: 8pt;
            margin-bottom: 15px;
        }

        .main-table th {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            vertical-align: middle;
            font-weight: bold;
            /* Simulasi pattern titik-titik dengan background abu-abu terang */
            background-color: #e0e0e0;
            background-image: radial-gradient(#000 15%, transparent 16%);
            background-size: 3px 3px;
        }

        .main-table td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: middle;
        }

        .col-no { width: 5%; text-align: center; font-weight: bold; }
        .col-pelaksana { width: 30%; }
        .col-unit { width: 10%; text-align: center; }
        .col-periode { width: 15%; text-align: center; }
        .col-tujuan { width: 13%; text-align: center; }
        .col-transport { width: 13%; text-align: center; }
        .col-anggaran { width: 14%; text-align: center; }

        .name-text { font-weight: bold; text-decoration: underline; }
        .detail-text { font-size: 8.5pt; }

        /* === TEXT PARAGRAF === */
        .text-justify {
            text-align: justify;
            margin-bottom: 10px;
            line-height: 1.4;
        }

        /* === TANDA TANGAN === */
        .signature-table {
            width: 100%;
            margin-top: 30px;
            border: none;
        }

        .signature-table th, .signature-table td {
            padding: 0px;
            text-align: center;
            vertical-align: middle;
            font-weight: normal;
            border: none;
        }

        .kuitansi-table td {
            padding: 5px;
            vertical-align: top;
        }

        .kuitansi-table-header {
            width: 50%;
            margin-bottom: 20px;
            float: right;
            border: 1px solid;
            border-collapse: collapse;
            padding: 5px;
        }

        .kuitansi-table-header td{
            width: 50%;
            margin-bottom: 20px;
            float: right;
            border: 1px solid;
            border-collapse: collapse;
            padding: 5px;
        }

        .kuitansi-table-header {
            width: 50%;
            margin-bottom: 20px;
            float: right;
            border: 1px solid;
            border-collapse: collapse;
            padding: 5px;
        }

        .spd-table-header {
            width: 60%;
            float: right;
            border: none;
        }

        .spd-table-header td{
            width: 60%;
            float: right;
            border: none;
            padding: 5px;
        }

        .spd-below-table {
            width: 100%;
            margin-bottom: 20px;
            border: 1px solid;
            border-collapse: collapse;
        }

        .spd-below-table td {
            width: 100%;
            margin-bottom: 20px;
            border: 1px solid;
            border-collapse: collapse;
            padding: 3px;
        }

        .underline { text-decoration: underline; }
        .bold { font-weight: bold; }

        .border-top-thick {
            border-top: 2px solid #000;
            padding-top: 10px;
        }

    </style>
</head>
<body>
    <div class="header-img-container">
        <img src="<?= base_url() ?>public/img/logo_kop_jl_bakti.png" class="header-img" alt="Kop Surat">
    </div>

    <div class="header-title">SURAT TUGAS</div>
    <div class="header-number" style="white-space: pre;">NOMOR : <?= $spt[0]->spt_no; ?></div>

    <table class="table-info">
        <tr>
            <td style="width: 18%;">Dalam rangka</td>
            <td style="width: 2%;">:</td>
            <td style="font-style: italic;">
                <?= $spt[0]->rangka; ?>
            </td>
        </tr>
    </table>

    <div class="text-justify">
        Dengan ini menugaskan pejabat/staf sebagaimana tercantum di bawah ini untuk melaksanakan tugas/ perjalanan dinas dimaksud tersebut dengan rincian sebagai berikut :
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th>NO.</th>
                <th>PELAKSANA SPPD</th>
                <th>UNIT KERJA</th>
                <th>PERIODE PERJALANAN DINAS</th>
                <th>TUJUAN</th>
                <th>TRANSPORTASI</th>
                <th>PEMBEBANAN ANGGARAN</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($spt as $d) { ?>
                <tr>
                    <td class="col-no"><?= $no++; ?></td>
                    <td class="col-pelaksana">
                        <span class="name-text"><?= $d->pelaksana; ?></span><br>
                        <span class="detail-text">
                        <?= !empty($d->nip_pelaksana) ? $d->nip_pelaksana . "<br>" : ''; ?>
                        <?= !empty($d->golongan) ? $d->golongan . "<br>" : ''; ?>
                        <?= $d->jabatan; ?>
                        </span>
                    </td>
                    <td class="col-unit">SATKER PPS RIAU</td>
                    <td class="col-periode">
                        <?php
                            $start = new DateTime($d->tanggal_awal);
                            $end = new DateTime($d->tanggal_akhir);
                            $days = (int) $start->diff($end)->days + 1;
                        ?>
                        <?= $days; ?> (<?= trim(terbilang($days)); ?>) hari<br>
                        <?= tanggal_indonesia($d->tanggal_awal); ?> s/d<br>
                        <?= tanggal_indonesia($d->tanggal_akhir); ?>
                    </td>
                    <td class="col-tujuan"><?= $d->tujuan; ?></td>
                    <td class="col-transport"><?= $d->transportasi; ?></td>
                    <td class="col-anggaran">SATKER PPS RIAU</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <div style="page-break-inside: avoid;">
        <div class="text-justify">
            Kepada pegawai tersebut diatas agar melaksanakan perjalanan dinas sesuai dengan Surat Tugas dan menyampaikan laporan pelaksanaan perjalanan dinas kepada Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau, selambat-lambatnya 5 (lima) hari kerja setelah perjalanan dinas.
        </div>

        <div class="text-justify">
            Demikian Surat Tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.
        </div>

        <table class="signature-table">
            <tr>
                <td style="width: 50%;"></td>
                <td class="signature-cell">
                    <table style="width: 100%; border: none;">
                        <tr>
                            <td style="text-align: left; width: 40%;">DIKELUARKAN DI</td>
                            <td style="text-align: center; width: 5%;">:</td>
                            <td style="text-align: left; font-weight: bold;"><?= $spt[0]->spt_di_keluarkan; ?></td>
                        </tr>
                        <tr>
                            <td style="text-align: left;">PADA TANGGAL</td>
                            <td style="text-align: center;">:</td>
                            <td style="text-align: left; font-weight: bold;"><?= tanggal_indonesia($spt[0]->spt_tanggal); ?></td>
                        </tr>
                    </table>
                    <br>
                    <div class="bold" style="margin-bottom: 60px;">
                        KEPALA SATUAN KERJA<br>
                        PELAKSANAAN PRASARANA STRATEGIS RIAU
                    </div>
                    <br>
                    <div class="bold underline"><?= $spt[0]->approval_by; ?></div>
                    <div class="bold">NIP. <?= $spt[0]->nip_approval; ?></div>
                </td>
            </tr>
        </table>
    </div>

    <div style="page-break-before: always;">
        <div class="header-title">RINCIAN BIAYA PERJALANAN DINAS</div>
        <div style="text-align: center; white-space: pre;">LAMPIRAN SPD NOMOR : <?= $spd_header[0]->no_spd; ?></div>
        <div style="text-align: center; margin-bottom: 10px;">TANGGAL : <?= strtoupper(tanggal_indonesia($spd_header[0]->tanggal_spd)); ?></div>

        <table class="main-table">
            <thead>
                <tr>
                    <th style="width: 10%;">NO.</th>
                    <th style="width: 50%;">RINCIAN BIAYA</th>
                    <th style="width: 20%;">JUMLAH</th>
                    <th style="width: 20%;">KETERANGAN</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="col-no">1</td>
                    <td>
                        <b>BIAYA TRANSPORT</b><br>
                        <?php
                            $total_biaya = 0;
                            $total_transport = 0;
                            $keterangan = "";
                            foreach ($spd_detail as $d) {
                            if($d->jenis_biaya == "Transportasi") {
                                $total_biaya += $d->durasi * $d->jumlah_satuan;
                                $total_transport += $d->durasi * $d->jumlah_satuan;
                                $keterangan .= $d->keterangan; ?>
                                <?= $d->nama_biaya; ?> : <br/>
                                <?= $d->durasi; ?> Hari x Rp. <?= number_format($d->jumlah_satuan,0,',','.'); ?><br/>
                        <?php }
                        } ?>
                    </td>
                    <td style="text-align: right;">Rp. <?= number_format($total_transport,0,',','.'); ?></td>
                    <td style="text-align: center;"><?= $keterangan; ?></td>
                </tr>
                <tr>
                    <td class="col-no">2</td>
                    <td>
                        <b>UANG HARIAN</b></br>
                        Uang Makan, Uang Transport Lokal, Uang Saku Selama :<br>
                        <?php
                            $total_harian = 0;
                            $keterangan = "";
                            foreach ($spd_detail as $d) {
                            if($d->jenis_biaya == "Harian") {
                                $total_biaya += $d->durasi * $d->jumlah_satuan;
                                $total_harian += $d->durasi * $d->jumlah_satuan;
                                $keterangan .= $d->keterangan; ?>
                                <?= $d->nama_biaya; ?> : <br/>
                                <?= $d->durasi; ?> Hari x Rp. <?= number_format($d->jumlah_satuan,0,',','.'); ?><br/>
                        <?php }
                        } ?>
                    </td>
                    <td style="text-align: right;">Rp. <?= number_format($total_harian,0,',','.'); ?></td>
                    <td style="text-align: center;"><?= $keterangan; ?></td>
                </tr>
                <tr>
                    <td class="col-no">3</td>
                    <td><b>UANG PENGINAPAN</b></br>
                        Uang Penginapan Selama :<br>
                        <?php
                            $total_penginapan = 0;
                            $keterangan = "";
                            foreach ($spd_detail as $d) {
                            if($d->jenis_biaya == "Penginapan") {
                                $total_biaya += $d->durasi * $d->jumlah_satuan;
                                $total_penginapan += $d->durasi * $d->jumlah_satuan;
                                $keterangan .= $d->keterangan; ?>
                                <?= $d->nama_biaya; ?> : <br/>
                                <?= $d->durasi; ?> Hari x Rp. <?= number_format($d->jumlah_satuan,0,',','.'); ?><br/>
                        <?php }
                        } ?>
                    </td>
                    <td style="text-align: right;">Rp. <?= number_format($total_penginapan,0,',','.'); ?></td>
                    <td style="text-align: center;"><?= $keterangan; ?></td>
                </tr>
                <tr>
                    <td colspan="2"><b>JUMLAH</b></td>
                    <td colspan="2" style="text-align: right;">Rp. <?= number_format($total_biaya,0,',','.'); ?></td>
                </tr>
                <tr>
                    <td colspan="4"><b>TERBILANG : <?= terbilang_rupiah($total_biaya); ?></b></td>
                </tr>
            </tbody>
        </table>

        <table class="signature-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Telah dibayar uang sebesar</th>
                    <th style="width: 50%;">Pekanbaru, &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= substr(tanggal_indonesia($spd_header[0]->tanggal_ttd_bendahara), 3); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Rp. <?= number_format($total_biaya,0,',','.'); ?></td>
                    <td></td>
                </tr>
                <tr>
                    <td>Pekanbaru, &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= substr(tanggal_indonesia($spd_header[0]->tanggal_ttd_bendahara), 3); ?></td>
                    <td></td>
                </tr>
                <tr>
                    <td style="height: 100px; vertical-align: top">Bendahara Pengeluaran,</td>
                    <td style="height: 100px; vertical-align: top">Yang Menerima :</td>
                </tr>
                <tr>
                    <td><b><u><?= $spd_header[0]->nama_bendahara; ?></u></b></td>
                    <td><b><u><?= $spd_header[0]->penerima; ?></u></b></td>
                </tr>
                <tr>
                    <td><?= $spd_header[0]->nip_bendahara; ?></td>
                    <td><?= $spd_header[0]->nip_penerima; ?></td>
                </tr>
            </tbody>
        </table>

        <div class="border-top-thick" style="margin-top: 30px; margin-bottom: 30px;">
            <div style="text-align: center"><b><u>PERHITUNGAN SPD RAMPUNG</u></b></div>

            <table style="width: 100%; margin-top: 30px;">
                <tbody>
                    <tr>
                        <td style="width: 70%; border-bottom: 1px dotted;">Ditetapkan Sejumlah</td>
                        <td style="width: 5%;">Rp.</td>
                        <td style="width: 25%; text-align: right;"><?= number_format($total_biaya,0,',','.'); ?></td>
                    </tr>
                    <tr>
                        <td style="width: 70%; border-bottom: 1px dotted;">Yang dibayar semula</td>
                        <td>Rp.</td>
                        <td style="text-align: right; border-bottom: 1px solid;">-</td>
                    </tr>
                    <tr>
                        <td style="width: 70%; border-bottom: 1px dotted;">Sisa kurang/lebih</td>
                        <td>Rp.</td>
                        <td style="text-align: right;">-</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <table class="signature-table">
            <tr>
                <td style="width: 50%;"></td>
                <td class="signature-cell">
                    <div style="margin-bottom: 60px;">
                        Pejabat Pembuat Komitmen<br>
                        Pelaksanaan Prasarana Strategis
                    </div>
                    <br>
                    <div><b><u><?= $spd_header[0]->pejabat_ppk; ?></u></b></div>
                    <div>NIP. <?= $spd_header[0]->nip_ppk; ?></div>
                </td>
            </tr>
        </table>
    </div>

    <div style="page-break-before: always;">
        <div class="header-img-container">
            <img src="<?= base_url() ?>public/img/logo_kop_jl_bakti.png" class="header-img" alt="Kop Surat">
        </div>

        <div style="border: 1px solid #000; padding: 15px;">
            <div>
                <table class="kuitansi-table-header">
                    <tbody>
                        <tr>
                            <td>Tahun Anggaran</td>
                            <td style="text-align: right;"><?= $spd_header[0]->tahun_anggaran; ?></td>
                        </tr>
                        <tr>
                            <td>Nomor Bukti</td>
                            <td style="text-align: right;"><?= $spd_header[0]->nomor_bukti; ?></td>
                        </tr>
                        <tr>
                            <td>Mata Anggaran</td>
                            <td style="text-align: right;"><b><?= $spd_header[0]->mata_anggaran; ?></b></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div style="text-align: center; clear: both;"><h1><u>KUITANSI</u></h1></div>

            <table class="kuitansi-table" style="font-size: 10pt; width: 100%;">
                <tr>
                    <td style="width: 27%;">Sudah di terima dari</td>
                    <td style="width: 2%;">:</td>
                    <td style="width: 71%;" class="bold">Pejabat Pembuat Komitmen Pelaksanaan Prasarana Strategis</td>
                </tr>
                <tr>
                    <td>Jumlah Uang</td>
                    <td>:</td>
                    <td class="bold">Rp. <?= number_format($total_biaya,0,',','.'); ?></td>
                </tr>
                <tr>
                    <td>Terbilang</td>
                    <td>:</td>
                    <td class="bold"><?= terbilang_rupiah($total_biaya); ?>,-</td>
                </tr>
                <tr>
                    <td>Untuk Pembayaran</td>
                    <td>:</td>
                    <td style="text-align: justify">Perjalanan Dinas a.n <?= $spd_header[0]->penerima; ?>. dalam rangka <?= $spt[0]->rangka; ?>, sesuai dengan Peraturan Menteri Keuangan RI Nomor 119 Tahun 2023 Tanggal 15 November 2023, sebagaimana daftar perincian terlampir.</td>
                </tr>
                <tr>
                    <td>Berdasarkan SPD</td>
                </tr>
                <tr>
                    <td>Nomor</td>
                    <td>:</td>
                    <td style=" white-space: pre;"><?= $spd_header[0]->no_spd; ?></td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td>:</td>
                    <td><?= tanggal_indonesia($spd_header[0]->tanggal_spd); ?></td>
                </tr>
                <tr>
                    <td>Untuk Perjalanan dinas dari</td>
                    <td>:</td>
                    <td><?= $spt[0]->asal . ' - ' . $spt[0]->tujuan; ?></td>
                </tr>
                <tr>
                    <td>Berangkat dari tanggal</td>
                    <td>:</td>
                    <td><?= tanggal_indonesia($spt[0]->tanggal_awal) . ' s/d ' . tanggal_indonesia($spt[0]->tanggal_akhir); ?></td>
                </tr>
            </table>

            <table class="signature-table">
                <thead>
                    <tr>
                        <th style="width: 50%;">An. Kuasa Pengguna Anggaran</th>
                        <th style="width: 50%;">Pekanbaru, &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= substr(tanggal_indonesia($spd_header[0]->tanggal_ttd_bendahara), 3); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="height: 100px; vertical-align: top">Pejabat Pembuat Komitmen<br/>Pelaksanaan Prasarana Strategis</td>
                        <td style="height: 100px; vertical-align: top"><?= $spd_header[0]->jabatan_penerima; ?></td>
                    </tr>
                    <tr>
                        <td><b><u><?= $spd_header[0]->pejabat_ppk; ?></u></b></td>
                        <td><b><u><?= strtoupper($spd_header[0]->penerima); ?></u></b></td>
                    </tr>
                    <tr>
                        <td>NIP : <?= $spd_header[0]->nip_ppk; ?></td>
                        <td><?= $spd_header[0]->nip_penerima; ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <?php foreach ($spt as $s) { ?>

    <div style="page-break-before: always;">
        <div class="header-img-container">
            <img src="<?= base_url() ?>public/img/logo_kop_jl_bakti.png" class="header-img" alt="Kop Surat">
        </div>

        <div>
            <table class="spd-table-header">
                <tbody>
                    <tr>
                        <td style="width: 8%;">Lembar Ke</td>
                        <td style="width: 2%;">:</td>
                        <td style="width: 90%; text-align: right;">1</td>
                    </tr>
                    <tr>
                        <td>Kode Nomor</td>
                        <td style="width: 2%;">:</td>
                        <td style="text-align: right;"></td>
                    </tr>
                    <tr>
                        <td>Nomor</td>
                        <td style="width: 2%;">:</td>
                        <td style="text-align: right;"><?= $spd_header[0]->no_spd; ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div style="text-align: center; clear: both;"><h3>SURAT PERJALANAN DINAS (SPD)</h3></div>

        <table class="spd-below-table" style="font-size: 9pt; width: 100%;">
            <tr>
                <td style="width: 2%;">1</td>
                <td style="width: 38%;">Pejabat yang Berwenang Memberi Perintah</td>
                <td style="width: 60%;" colspan="2">Pejabat Pembuat Komitmen Pelaksanaan Prasarana Strategis</td>
            </tr>
            <tr>
                <td style="width: 2%;">2</td>
                <td style="width: 38%;">Nama/NIP Pegawai yang melaksanakan perjalanan dinas</td>
                <td style="width: 60%;" colspan="2"><?= $s->pelaksana; ?> / <?= $s->nip_pelaksana; ?></td>
            </tr>
            <tr>
                <td style="width: 2%;" rowspan="3">3</td>
                <td style="width: 38%;">a. Pangkat dan Golongan </td>
                <td style="width: 60%;" colspan="2">Golongan <?= $s->golongan; ?></td>
            </tr>
            <tr>
                <td style="width: 38%;">b. Jabatan/Instansi</td>
                <td style="width: 60%;" colspan="2"><?= $s->jabatan; ?></td>
            </tr>
            <tr>
                <td style="width: 38%;">c. Tingkat Biaya Perjalanan Dinas</td>
                <td style="width: 60%;" colspan="2">C</td>
            </tr>
            <tr>
                <td style="width: 2%;">4</td>
                <td style="width: 38%;">Maksud Perjalanan Dinas</td>
                <td style="width: 60%;" colspan="2"><?= $spt[0]->rangka; ?></td>
            </tr>
            <tr>
                <td style="width: 2%;">5</td>
                <td style="width: 38%;">Alat angkutan yang dipergunakan</td>
                <td style="width: 60%;" colspan="2">
                    <?php
                        $is_start = true;
                        foreach ($spd_detail as $d) {
                            if($d->jenis_biaya == "Transportasi") {
                                if (!$is_start) {
                                    echo ", ";
                                } else {
                                    $is_start = false;
                                }
                                echo $d->nama_biaya;
                            }
                    } ?>
                </td>
            </tr>
            <tr>
                <td style="width: 2%;" rowspan="2">6</td>
                <td style="width: 38%;">a. Tempat berangkat</td>
                <td style="width: 60%;" colspan="2"><?= $spt[0]->asal; ?></td>
            </tr>
            <tr>
                <td style="width: 38%;">b. Tempat tujuan</td>
                <td style="width: 60%;" colspan="2"><?= $spt[0]->tujuan; ?></td>
            </tr>
            <tr>
                <td style="width: 2%;" rowspan="3">7</td>
                <td style="width: 38%;">a. Lamanya Perjalanan Dinas</td>
                <td style="width: 60%;" colspan="2">
                    <?php
                        $start = new DateTime($spt[0]->tanggal_awal);
                        $end = new DateTime($spt[0]->tanggal_akhir);
                        $days = (int) $start->diff($end)->days + 1;
                    ?>
                    <?= $days; ?> (<?= trim(terbilang($days)); ?>) hari
                </td>
            </tr>
            <tr>
                <td style="width: 38%;">b. Tanggal berangkat</td>
                <td style="width: 60%;" colspan="2"><?= tanggal_indonesia($spt[0]->tanggal_awal); ?></td>
            </tr>
            <tr>
                <td style="width: 38%;">c. Tanggal harus kembali/tiba ditempat baru</td>
                <td style="width: 60%;" colspan="2"><?= tanggal_indonesia($spt[0]->tanggal_akhir); ?></td>
            </tr>
            <tr>
                <td style="width: 2%;" rowspan="2">8</td>
                <td style="width: 38%;">Pengikut</td>
                <td style="width: 30%; text-align: center">Tanggal Lahir</td>
                <td style="width: 30%; text-align: center">Keterangan</td>
            </tr>
            <tr>
                <td style="width: 38%;">
                    <?php
                        for ($no = 1; $no <= 3; $no++) {
                            echo $no . ".<br/>";
                        }
                    ?>
                </td>
                <td style="width: 30%;"></td>
                <td style="width: 30%;"></td>
            </tr>
            <tr>
                <td style="width: 2%;" rowspan="3">9</td>
                <td style="width: 38%;" colspan="3">Pembebanan Anggaran</td>
            </tr>
            <tr>
                <td style="width: 38%;">a. Instansi</td>
                <td style="width: 60%;" colspan="2">DIPA Satker Pelaksanaan Prasarana Strategis Riau</td>
            </tr>
            <tr>
                <td style="width: 38%;">b. Akun</td>
                <td style="width: 60%;" colspan="2"><?= $spd_header[0]->mata_anggaran; ?></td>
            </tr>
            <tr>
                <td style="width: 2%;">10</td>
                <td style="width: 38%;">Keterangan lain-lain</td>
                <td style="width: 60%; white-space: pre;" colspan="2">Surat Tugas Nomor : <?= $spt[0]->spt_no ?></td>
            </tr>
        </table>

        <div style="page-break-inside: avoid;">
            <table class="signature-table">
                <tr>
                    <td style="width: 50%;"></td>
                    <td class="signature-cell">
                        <table style="width: 100%; border: none;">
                            <tr>
                                <td style="text-align: left; width: 40%;">Dikeluarkan di</td>
                                <td style="text-align: center; width: 5%;">:</td>
                                <td style="text-align: left; font-weight: bold;">Pekanbaru</td>
                            </tr>
                            <tr>
                                <td style="text-align: left;">Tanggal</td>
                                <td style="text-align: center;">:</td>
                                <td style="text-align: left; font-weight: bold;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= substr(tanggal_indonesia($spd_header[0]->tanggal_ttd_bendahara), 3); ?></td>
                            </tr>
                        </table>
                        <br>
                        <div class="bold" style="margin-bottom: 60px;">
                            Pejabat Berwenang yang Memberi Perintah<br>
                            Pejabat Pembuat Komitmen<br>Pelaksanaan Prasarana Strategis
                        </div>
                        <br>
                        <div class="bold underline"><?= $spd_header[0]->pejabat_ppk; ?></div>
                        <div>NIP. <?= $spd_header[0]->nip_ppk; ?></div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <?php } ?>
</body>
</html>