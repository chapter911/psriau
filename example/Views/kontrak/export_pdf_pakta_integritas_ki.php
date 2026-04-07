<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: "Times New Roman", serif;
            font-size: 11pt;
            line-height: 1.3;
            margin: 0;
        }
        @page {
            margin: 2.5cm 2.5cm 2.5cm 2.5cm;
        }
        .text-center { text-align: center; }
        .text-justify { text-align: justify; }
        .bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .bio-table td {
            vertical-align: top;
            padding: 2px 5px;
        }
        .mb-1 { margin-bottom: 10px; }
        .mb-2 { margin-bottom: 20px; }
        .signature-block {
            float: right;
            width: 40%;
            text-align: center;
            margin-top: 20px;
        }
        .clear { clear: both; }
    </style>
</head>
<body>

    <div class="text-center bold uppercase mb-2" style="font-size: 12pt; text-decoration: underline;">
        PAKTA INTEGRITAS
    </div>

    <div class="mb-1">Saya yang bertanda tangan di bawah ini :</div>

    <table class="bio-table">
        <tr>
            <td width="180">Nama</td>
            <td width="10">:</td>
            <td><b><?= $data->nama; ?></b></td>
        </tr>
        <tr>
            <td>No. Identitas</td>
            <td>:</td>
            <td><?= $data->nik; ?></td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>:</td>
            <td>Konsultan Individual  <?= ucwords(strtolower($data->jabatan)); ?></td>
        </tr>
        <tr>
            <td>Bertindak untuk dan atas nama</td>
            <td>:</td>
            <td>Diri sendiri</td>
        </tr>
    </table>

    <div class="text-justify mb-1">
        dalam rangka pengadaan pekerjaan <b>Konsultan Individual  <?= ucwords(strtolower($data->jabatan)); ?></b> pada <b>Pengadaan Barang/Jasa Pemerintah Pada Satuan Kerja Pelaksaan Prasarana Strategis Riau</b> di Pekanbaru dengan ini menyatakan bahwa:
    </div>

    <ol style="margin-top: 0; padding-left: 20px;" class="text-justify">
        <li class="mb-1">tidak akan melakukan praktek Korupsi, Kolusi dan Nepotisme (KKN);</li>
        <li class="mb-1">akan melaporkan kepada pihak yang berwajib/berwenang apabila mengetahui ada indikasi KKN didalam proses pengadaan ini;</li>
        <li class="mb-1">akan mengikuti proses pengadaan secara bersih, transparan, dan profesional untuk memberikan hasil kerja terbaik sesuai ketentuan peraturan perundang-undangan;</li>
        <li class="mb-1">apabila melanggar hal-hal yang dinyatakan dalam PAKTA INTEGRITAS ini, bersedia menerima sanksi administratif, menerima sanksi pencantuman dalam Daftar Hitam, digugat secara perdata dan/atau dilaporkan secara pidana.</li>
    </ol>

    <div class="signature-block">
        Pekanbaru, <?= tanggal_indonesia($data->tanggal_surat_penawaran); ?><br>
        Konsultan Individual<br>
        <?= ucwords(strtolower($data->jabatan)); ?><br>
        <br><br><br>
        <b><b><?= $data->nama; ?></b>
    </div>
    <div class="clear"></div>

</body>
</html>
