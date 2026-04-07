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
        .signature-cell {
            text-align: center;
            width: 45%;
            float: right;
        }
        
        .underline { text-decoration: underline; }
        .bold { font-weight: bold; }

    </style>
</head>
<body>

    <div class="header-img-container">
        <img src="<?= base_url() ?>public/img/logo_kop_jl_bakti.png" class="header-img" alt="Kop Surat">
    </div>

    <div class="header-title">SURAT TUGAS</div>
    <div class="header-number" style="white-space: pre;">NOMOR : <?= $data[0]->spt_no; ?></div>

    <table class="table-info">
        <tr>
            <td style="width: 18%;">Dalam rangka</td>
            <td style="width: 2%;">:</td>
            <td style="font-style: italic;">
                <?= $data[0]->rangka; ?>
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
            <?php $no = 1; foreach ($data as $d) { ?>
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
                            <td style="text-align: left; font-weight: bold;"><?= $data[0]->spt_di_keluarkan; ?></td>
                        </tr>
                        <tr>
                            <td style="text-align: left;">PADA TANGGAL</td>
                            <td style="text-align: center;">:</td>
                            <td style="text-align: left; font-weight: bold;"><?= tanggal_indonesia($data[0]->spt_tanggal); ?></td>
                        </tr>
                    </table>
                    <br>
                    <div class="bold" style="margin-bottom: 60px;">
                        KEPALA SATUAN KERJA<br>
                        PELAKSANAAN PRASARANA STRATEGIS RIAU
                    </div>
                    <br>
                    <div class="bold underline"><?= $data[0]->approval_by; ?></div>
                    <div class="bold">NIP. <?= $data[0]->nip_approval; ?></div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>