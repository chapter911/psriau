<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>DAFTAR NOMINATIF RINCIAN PERJALANAN DINAS</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1cm 1.5cm 1.5cm 1.5cm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            line-height: 1;
            color: #000;
        }

    </style>
</head>
<body>
    <h3 style="text-align: center">DAFTAR NOMINATIF RINCIAN PERJALANAN DINAS</h3>

    <table>
        <tbody>
            <tr>
                <td><b>SATKER</b></td>
                <td><b>:</b></td>
                <td><b>PELAKSANAAN PRASARANA STRATEGIS RIAU</b></td>
            </tr>
            <tr>
                <td><b>KODE SATKER</b></td>
                <td><b>:</b></td>
                <td><b>691285</b></td>
            </tr>
            <tr>
                <td><b>MAK</b></td>
                <td><b>:</b></td>
                <td><b><?= $spd_header[0]->mata_anggaran; ?></b></td>
            </tr>
            <tr>
                <td><b>URAIAN</b></td>
                <td><b>:</b></td>
                <td><b><?= $spt[0]->rangka; ?></b></td>
            </tr>
        </tbody>
    </table>
</body>
</html>