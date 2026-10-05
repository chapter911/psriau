<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= esc($row['nomor_bast'] ?: 'BAST'); ?></title>
    <style>
        @page {
            margin: 1.5cm 2cm 2cm 2cm;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            line-height: 1.35;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .text-center { text-align: center; }
        .text-justify { text-align: justify; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .kop-wrapper {
            text-align: center;
            margin-bottom: 12px;
            width: 100%;
        }
        .kop-img {
            width: 100%;
            max-height: 115px;
            object-fit: contain;
        }
        .kop-divider {
            border-bottom: 2.5px solid #000;
            margin-top: 5px;
            margin-bottom: 15px;
        }
        .title-block {
            text-align: center;
            margin-bottom: 15px;
        }
        .title-block .doc-title {
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 2px;
        }
        .title-block .doc-subtitle {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .title-block .doc-paket {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .title-block .doc-nomor {
            font-size: 11pt;
        }
        .section-title {
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 4px;
        }
        .table-pihak {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        .table-pihak td {
            vertical-align: top;
            padding: 1.5px 0;
            font-size: 11pt;
        }
        .table-rincian {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 10px;
        }
        .table-rincian th, .table-rincian td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 10.5pt;
        }
        .table-rincian th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .table-ttd {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .table-ttd td {
            width: 50%;
            vertical-align: top;
            text-align: center;
            font-size: 11pt;
            line-height: 1.3;
        }
        .space-ttd {
            height: 65px;
        }
        p {
            margin: 4px 0;
            text-align: justify;
        }
        .pasal-heading {
            text-align: center;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 2px;
        }
        ol.dasar-list {
            margin: 4px 0 8px 0;
            padding-left: 20px;
        }
        ol.dasar-list li {
            margin-bottom: 3px;
            text-align: justify;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT -->
    <?php if (! empty($kopBase64)): ?>
        <div class="kop-wrapper">
            <img src="<?= $kopBase64; ?>" class="kop-img" alt="Kop Surat">
        </div>
    <?php endif; ?>

    <!-- JUDUL DOKUMEN -->
    <div class="title-block">
        <div class="doc-title"><?= esc($row['judul_bast'] ?: 'BERITA ACARA SERAH TERIMA I'); ?></div>
        <div class="doc-subtitle"><?= esc($row['jenis_pekerjaan'] ?: 'PEKERJAAN JASA KONSULTANSI KONSTRUKSI'); ?></div>
        <div class="doc-paket"><?= esc($row['nama_paket']); ?></div>
        <div class="doc-nomor">Nomor: <?= esc($row['nomor_bast'] ?: '-'); ?></div>
    </div>

    <!-- PEMBUKA -->
    <p>
        Pada hari ini <?= esc($hariTanggalTerbilang); ?>, bertempat di <?= esc($row['kota_bast'] ?: 'Pekanbaru'); ?>, yang bertanda tangan di bawah ini:
    </p>

    <!-- PIHAK PERTAMA -->
    <div class="section-title">1. &nbsp;PIHAK PERTAMA</div>
    <table class="table-pihak" style="margin-left: 18px; width: 96%;">
        <tr>
            <td style="width: 14%;">Nama</td>
            <td style="width: 2%;">:</td>
            <td style="width: 84%;" class="font-bold"><?= esc($row['ppk_nama']); ?></td>
        </tr>
        <tr>
            <td>NIP</td>
            <td>:</td>
            <td><?= esc($row['ppk_nip']); ?></td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>:</td>
            <td><?= esc($row['ppk_jabatan']); ?></td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td>:</td>
            <td><?= esc($row['ppk_alamat'] ?: 'Jl. Datuk Setia Maharaja No. 15 Pekanbaru, Riau'); ?></td>
        </tr>
    </table>
    <p style="margin-left: 18px;">
        Dalam hal ini bertindak untuk dan atas nama <?= esc($row['ppk_jabatan']); ?>, selanjutnya disebut <strong>PIHAK PERTAMA</strong>.
    </p>

    <!-- PIHAK KEDUA -->
    <div class="section-title" style="margin-top: 8px;">2. &nbsp;PIHAK KEDUA</div>
    <table class="table-pihak" style="margin-left: 18px; width: 96%;">
        <tr>
            <td style="width: 14%;">Nama</td>
            <td style="width: 2%;">:</td>
            <td style="width: 84%;" class="font-bold"><?= esc($row['penyedia_wakil']); ?></td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>:</td>
            <td><?= esc($row['penyedia_jabatan'] ?: 'Direktur Utama'); ?></td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td>:</td>
            <td><?= esc($row['penyedia_alamat'] ?: '-'); ?></td>
        </tr>
    </table>
    <p style="margin-left: 18px;">
        Dalam hal ini bertindak untuk dan atas nama <?= esc($row['penyedia_nama']); ?>, selanjutnya disebut <strong>PIHAK KEDUA</strong>.
    </p>

    <p style="margin-top: 8px;">
        <strong>PIHAK PERTAMA</strong> dan <strong>PIHAK KEDUA</strong> secara bersama-sama disebut <strong>PARA PIHAK</strong>.
    </p>

    <!-- DASAR PELAKSANAAN -->
    <p class="font-bold" style="margin-top: 8px; margin-bottom: 2px;">Dasar pelaksanaan:</p>
    <?php if (! empty($dasarArr)): ?>
        <ol class="dasar-list">
            <?php foreach ($dasarArr as $d): ?>
                <li><?= esc($d); ?></li>
            <?php endforeach; ?>
        </ol>
    <?php else: ?>
        <p style="margin-left: 20px;">-</p>
    <?php endif; ?>

    <p style="margin-top: 8px;">
        PARA PIHAK menyatakan sepakat sebagai berikut:
    </p>

    <!-- PASAL 1 -->
    <div class="pasal-heading">Pasal 1</div>
    <p>
        PIHAK KEDUA menyerahkan kepada PIHAK PERTAMA hasil pekerjaan <?= $isFisik ? 'konstruksi' : 'jasa konsultansi'; ?> Pekerjaan <?= esc($row['nama_paket']); ?>, berupa:
    </p>

    <table class="table-rincian">
        <thead>
            <tr>
                <th style="width: 6%;">No</th>
                <th style="width: 50%;">Uraian Hasil Pekerjaan</th>
                <th style="width: 20%;">Jumlah</th>
                <th style="width: 24%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php if (! empty($rincianArr)): ?>
                <?php foreach ($rincianArr as $idx => $item): ?>
                    <tr>
                        <td class="text-center"><?= esc($item['no'] ?? ($idx + 1)); ?></td>
                        <td><?= esc($item['uraian'] ?? '-'); ?></td>
                        <td class="text-center"><?= esc($item['jumlah'] ?? '-'); ?></td>
                        <td><?= esc($item['keterangan'] ?? '-'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td class="text-center">1</td>
                    <td>Hasil Pekerjaan Sesuai Kontrak</td>
                    <td class="text-center">1 Berkas</td>
                    <td>Hardcopy &amp; softcopy</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- PASAL 2 -->
    <div class="pasal-heading">Pasal 2</div>
    <p>
        PIHAK PERTAMA menyatakan telah menerima hasil pekerjaan sebagaimana dimaksud dalam Pasal 1 dan telah melakukan pemeriksaan, dengan hasil bahwa pekerjaan <strong><?= esc($row['kesesuaian_pekerjaan'] ?: 'telah sesuai'); ?></strong> dengan ketentuan dalam kontrak.
    </p>

    <!-- PASAL 3 -->
    <div class="pasal-heading">Pasal 3</div>
    <p>
        Dengan ditandatanganinya berita acara ini, PIHAK PERTAMA berhak memproses pembayaran sebesar <strong><?= esc($row['persentase_pembayaran'] ?: '100%'); ?></strong> dari nilai kontrak/Addendum sesuai ketentuan dalam kontrak/Addendum.
    </p>

    <!-- PASAL 4 -->
    <div class="pasal-heading">Pasal 4</div>
    <p>
        Serah terima ini tidak menghapuskan kewajiban PIHAK KEDUA atas tahap pekerjaan berikutnya sampai seluruh pekerjaan diselesaikan sesuai kontrak/Addendum.
    </p>

    <!-- PASAL 5 -->
    <div class="pasal-heading">Pasal 5</div>
    <p>
        Berita acara ini dibuat rangkap 2 (dua) bermeterai cukup, masing-masing mempunyai kekuatan hukum yang sama, satu untuk PIHAK PERTAMA dan satu untuk PIHAK KEDUA.
    </p>

    <!-- PENUTUP -->
    <p style="margin-top: 8px;">
        Demikian <?= esc($row['judul_bast'] ?: 'Berita Acara Serah Terima I'); ?> ini dibuat dengan sebenarnya untuk dipergunakan sebagaimana mestinya.
    </p>

    <!-- TANDA TANGAN -->
    <?php
        $ppkJabatan = trim((string) ($row['ppk_jabatan'] ?? ''));
        $ppkTtd1 = 'Pejabat Penandatangan Kontrak';
        $ppkTtd2 = 'Pelaksanaan Prasarana Strategis';
        if (! empty($ppkJabatan) && stripos($ppkJabatan, ',') !== false) {
            $parts = explode(',', $ppkJabatan, 2);
            $ppkTtd1 = trim($parts[0]);
            $ppkTtd2 = trim($parts[1]);
        } elseif (! empty($ppkJabatan)) {
            $ppkTtd1 = $ppkJabatan;
            $ppkTtd2 = trim((string) ($row['ppk_satker'] ?? 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau'));
        }
    ?>
    <table class="table-ttd">
        <tr>
            <td>
                <div class="font-bold">PIHAK KEDUA</div>
                <div class="font-bold" style="min-height: 32px;"><?= esc($row['penyedia_nama']); ?></div>
                <div class="space-ttd"></div>
                <div class="font-bold" style="text-decoration: underline;"><?= esc($row['penyedia_wakil']); ?></div>
                <div><?= esc($row['penyedia_jabatan'] ?: 'Direktur Utama'); ?></div>
            </td>
            <td>
                <div class="font-bold">PIHAK PERTAMA</div>
                <div class="font-bold"><?= esc($ppkTtd1); ?></div>
                <div><?= esc($ppkTtd2); ?></div>
                <div class="space-ttd"></div>
                <div class="font-bold" style="text-decoration: underline;"><?= esc($row['ppk_nama']); ?></div>
                <div>NIP. <?= esc($row['ppk_nip']); ?></div>
            </td>
        </tr>
    </table>

</body>
</html>
