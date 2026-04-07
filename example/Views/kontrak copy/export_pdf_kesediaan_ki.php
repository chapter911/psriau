<?php date_default_timezone_set('Asia/Jakarta'); ?>
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: "Times New Roman", serif;
            font-size: 12pt;
            line-height: 1.5;
            margin: 0;
        }
        @page {
            margin: 2.5cm 3cm 2.5cm 3cm; /* Margin standar surat resmi */
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
        .mb-2 {
            margin-bottom: 20px;
        }
        .mb-1 {
            margin-bottom: 10px;
        }
        .mt-2 {
            margin-top: 20px;
        }

        /* Tabel untuk biodata agar titik dua sejajar */
        table.biodata {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.biodata td {
            vertical-align: top;
            padding: 2px 0;
        }

        /* Blok tanda tangan di sebelah kanan */
        .signature-block {
            float: right;
            width: 40%;
            text-align: center; /* Biasanya tanggal rata kiri relatif terhadap blok tanda tangan, atau center */
            margin-top: 30px;
        }
    </style>
</head>
<body>

    <div class="text-center bold mb-2" style="text-decoration: underline;">
        PERNYATAAN KESEDIAAN UNTUK DITUGASKAN
    </div>

    <div class="mb-1">
        Yang bertanda tangan dibawah ini:
    </div>

    <table class="biodata">
        <tr>
            <td width="100">N a m a</td>
            <td width="10">:</td>
            <td><b><?= $data->nama; ?></b></td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td>:</td>
            <td><?= $data->alamat; ?></td>
        </tr>
    </table>

    <div class="text-justify mb-1">
        Dengan ini menyatakan bahwa saya bersedia untuk melaksanakan pekerjaan jasa <b>Konsultan Individual</b> sesuai dengan usulan jadwal penugasan saya dari bulan <?= date('F Y', strtotime($data->tanggal_awal)); ?> sampai dengan bulan <?= date('F Y', strtotime($data->tanggal_akhir)); ?> dengan posisi sebagai <b>Konsultan Individual <?= ucwords(strtolower($data->jabatan)); ?></b>.
    </div>

    <div class="text-justify">
        Demikian pernyataan ini saya buat dengan sebenar-benarnya dan penuh rasa tanggung jawab.
    </div>

    <div class="signature-block">
        <div>Pekanbaru, <?= date('d F Y', strtotime($data->tanggal_surat_penawaran)); ?></div>
        <div>Yang membuat pernyataan,</div>
        <br><br><br><br> <div class="bold" style="text-decoration: underline;"><?= $data->nama; ?></div>
    </div>

</body>
</html>