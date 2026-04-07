<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kwitansi Perjalanan Dinas</title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
        }
        .header p {
            margin: 0;
            font-size: 14px;
        }
        .content {
            width: 100%;
        }
        .content table {
            width: 100%;
            border-collapse: collapse;
        }
        .content th, .content td {
            border: 1px solid black;
            padding: 5px;
            font-size: 12px;
        }
        .footer {
            margin-top: 50px;
            width: 100%;
        }
        .footer table {
            width: 100%;
        }
        .footer td {
            width: 50%;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>KWITANSI (TANDA BUKTI PEMBAYARAN)</h1>
            <p>Perjalanan Dinas Jabatan</p>
        </div>
        <div class="content">
            <table>
                <tr>
                    <td colspan="2">T.A</td>
                    <td>:</td>
                    <td colspan="3"></td>
                </tr>
                <tr>
                    <td colspan="2">No. Bukti</td>
                    <td>:</td>
                    <td colspan="3"></td>
                </tr>
                <tr>
                    <td colspan="2">MAK</td>
                    <td>:</td>
                    <td colspan="3"></td>
                </tr>
                <tr>
                    <td colspan="5" style="text-align: center;"><b>PENERIMAAN</b></td>
                </tr>
                <tr>
                    <td colspan="2">Diterima dari</td>
                    <td>:</td>
                    <td colspan="3">Pejabat Pembuat Komitmen</td>
                </tr>
                <tr>
                    <td colspan="2">Jumlah</td>
                    <td>:</td>
                    <td colspan="3">Rp. </td>
                </tr>
                <tr>
                    <td colspan="2">Terbilang</td>
                    <td>:</td>
                    <td colspan="3"></td>
                </tr>
                <tr>
                    <td colspan="2">Untuk Pembayaran</td>
                    <td>:</td>
                    <td colspan="3">Biaya Perjalanan Dinas an. <?= $header['penerima'] ?? '' ?> dalam rangka <?= $header['rangka'] ?? '' ?> selama <?= $header['lama_perjalanan'] ?? '' ?> hari dari tanggal <?= $header['tanggal_awal'] ?? '' ?> s.d <?= $header['tanggal_akhir'] ?? '' ?></td>
                </tr>
                <tr>
                    <td colspan="5" style="text-align: center;"><b>RINCIAN PERHITUNGAN</b></td>
                </tr>
                <tr>
                    <th>No.</th>
                    <th>Uraian</th>
                    <th>Jumlah</th>
                    <th>Satuan</th>
                    <th>Keterangan</th>
                </tr>
                <?php if (!empty($detail)): ?>
                    <?php $no = 1; foreach ($detail as $d): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= $d['nama_biaya'] ?></td>
                            <td><?= number_format($d['jumlah_satuan'], 0, ',', '.') ?></td>
                            <td></td>
                            <td><?= $d['keterangan'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </table>
        </div>
        <div class="footer">
            <table>
                <tr>
                    <td>
                        <p>Telah dibayar lunas sejumlah,</p>
                        <p>Bendahara Pengeluaran</p>
                        <br><br><br>
                        <p><b><u><?= $header['nama_bendahara'] ?? '' ?></u></b></p>
                        <p>NIP. <?= $header['nip_bendahara'] ?? '' ?></p>
                    </td>
                    <td>
                        <p>Yang Menerima,</p>
                        <br><br><br><br>
                        <p><b><u><?= $header['penerima'] ?? '' ?></u></b></p>
                        <p>NIP. <?= $header['nip_penerima'] ?? '' ?></p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
