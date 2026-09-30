<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan Kinerja Tenaga Ahli - <?= esc($row['nama_tenaga_ahli'] ?? ''); ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.5cm 2cm 1.8cm 2cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            line-height: 1.35;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .header-kop {
            text-align: center;
            margin-bottom: 8px;
            padding-bottom: 2px;
        }
        .header-kop img {
            width: 100%;
            max-height: 95px;
            display: block;
            margin: 0 auto;
        }
        .title-block {
            text-align: center;
            margin-top: 10px;
            margin-bottom: 12px;
        }
        .title-block .doc-title {
            font-size: 11pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
            padding: 0;
            letter-spacing: 0.5px;
        }
        .title-block .doc-subtitle {
            font-size: 10.5pt;
            font-weight: bold;
            margin: 1px 0;
            padding: 0;
            letter-spacing: 0.5px;
        }
        .title-block .doc-number {
            font-size: 10pt;
            margin-top: 4px;
        }
        .section-intro {
            margin-top: 8px;
            margin-bottom: 4px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        table.data-table td {
            vertical-align: top;
            padding: 2px 0;
            font-size: 10pt;
        }
        table.data-table td.col-label {
            width: 33%;
            color: #111;
        }
        table.data-table td.col-colon {
            width: 3%;
            text-align: center;
        }
        table.data-table td.col-val {
            width: 64%;
        }
        .penilaian-box {
            margin-top: 8px;
            margin-bottom: 8px;
            font-size: 10pt;
        }
        .penilaian-box strong {
            font-weight: bold;
        }
        .closing-text {
            text-align: justify;
            margin-top: 8px;
            margin-bottom: 15px;
            line-height: 1.4;
        }
        .signature-table {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
        }
        .signature-table td {
            vertical-align: top;
            padding: 0;
        }
        .signature-box {
            text-align: left;
            width: 55%;
            margin-left: auto;
        }
        .signature-space {
            height: 60px;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="header-kop">
        <?php if (! empty($kopBase64)): ?>
            <img src="<?= $kopBase64; ?>" alt="Kop Surat Resmi Satker PPS">
        <?php endif; ?>
    </div>

    <div class="title-block">
        <div class="doc-title">SURAT KETERANGAN / REFERENSI KINERJA</div>
        <div class="doc-subtitle">TENAGA AHLI DAN PENDUKUNG</div>
        <div class="doc-subtitle">KONSULTANSI KONSTRUKSI</div>
        <div class="doc-number">Nomor: <?= esc($row['nomor_surat'] ?: '..........................................'); ?></div>
    </div>

    <div class="section-intro">Yang bertanda tangan di bawah ini:</div>
    <table class="data-table">
        <tr>
            <td class="col-label">Nama</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= esc($row['ppk_nama'] ?: '-'); ?></td>
        </tr>
        <tr>
            <td class="col-label">Jabatan</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= esc($row['ppk_jabatan'] ?: 'Pejabat Pembuat Komitmen Pelaksanaan Prasarana Strategis'); ?></td>
        </tr>
        <tr>
            <td class="col-label">Satker</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= esc($row['ppk_satker'] ?: 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau'); ?></td>
        </tr>
        <tr>
            <td class="col-label">Alamat</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= nl2br(esc($row['ppk_alamat'] ?: '-')); ?></td>
        </tr>
    </table>

    <div class="section-intro">Dengan ini menerangkan bahwa:</div>
    <table class="data-table">
        <tr>
            <td class="col-label">Nama Tenaga Ahli</td>
            <td class="col-colon">:</td>
            <td class="col-val"><strong><?= esc($row['nama_tenaga_ahli'] ?: '-'); ?></strong></td>
        </tr>
        <tr>
            <td class="col-label">Jabatan dalam Pekerjaan</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= esc($row['jabatan_pekerjaan'] ?: '-'); ?></td>
        </tr>
        <tr>
            <td class="col-label">Nama Badan Usaha</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= esc($row['nama_badan_usaha'] ?: '-'); ?></td>
        </tr>
        <tr>
            <td class="col-label">Alamat Badan Usaha</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= nl2br(esc($row['alamat_badan_usaha'] ?: '-')); ?></td>
        </tr>
    </table>

    <div class="section-intro">telah melaksanakan pekerjaan jasa konsultansi konstruksi dengan data sebagai berikut:</div>
    <table class="data-table">
        <tr>
            <td class="col-label">Nama Paket Pekerjaan</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= esc($row['nama_paket'] ?: '-'); ?></td>
        </tr>
        <tr>
            <td class="col-label">Lingkup Jasa</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= esc($row['lingkup_jasa'] ?: 'Manajemen Konstruksi'); ?></td>
        </tr>
        <tr>
            <td class="col-label">Lokasi Pekerjaan</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= esc($row['lokasi_pekerjaan'] ?: '-'); ?></td>
        </tr>
        <tr>
            <td class="col-label">Nomor &amp; Tanggal Kontrak</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= esc($row['nomor_tanggal_kontrak'] ?: '-'); ?></td>
        </tr>
        <tr>
            <td class="col-label">Nilai Kontrak (termasuk addendum bila ada)</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= esc($row['nilai_kontrak'] ?: '-'); ?></td>
        </tr>
        <tr>
            <td class="col-label">Sumber Dana</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= esc($row['sumber_dana'] ?: 'APBN DIPA Satker'); ?></td>
        </tr>
        <tr>
            <td class="col-label">Masa Penugasan Tenaga Ahli</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= esc($row['masa_penugasan'] ?: '-'); ?></td>
        </tr>
        <tr>
            <td class="col-label">Status Pekerjaan</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= esc($row['status_pekerjaan'] ?: 'selesai 100%'); ?></td>
        </tr>
    </table>

    <div class="penilaian-box">
        Penilaian keseluruhan: <strong><?= esc($row['penilaian_keseluruhan'] ?: 'Sangat Baik'); ?></strong>
    </div>

    <div class="closing-text">
        Demikian surat keterangan ini dibuat dengan sebenarnya untuk dipergunakan sebagaimana mestinya, antara lain sebagai bukti pengalaman dalam proses pengadaan jasa konsultansi konstruksi.
    </div>

    <?php
        $kota = ! empty($row['kota_surat']) ? $row['kota_surat'] : 'Pekanbaru';
        $tglStr = ! empty($row['tanggal_surat']) ? tanggal_indonesia($row['tanggal_surat']) : tanggal_indonesia(date('Y-m-d'));
    ?>
    <table class="signature-table">
        <tr>
            <td style="width: 45%;"></td>
            <td style="width: 55%;">
                <div class="signature-box">
                    <div><?= esc($kota); ?>, <?= esc($tglStr); ?></div>
                    <div><?= esc($row['ppk_jabatan'] ?: 'PPK Pelaksanaan Prasarana Strategis'); ?>,</div>
                    <div><?= esc($row['ppk_satker'] ?: 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau'); ?></div>
                    <div class="signature-space"></div>
                    <div class="signature-name"><?= esc($row['ppk_nama'] ?: 'Nurhidayat Nugroho, S. Ars'); ?></div>
                    <div>NIP. <?= esc($row['ppk_nip'] ?: '199012212018021001'); ?></div>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
