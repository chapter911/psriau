<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Perjanjian Pinjam Pakai BMN - <?= ! empty($loan['no_surat']) ? esc($loan['no_surat']) : 'Draft'; ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5pt;
            line-height: 1.25;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .page-break {
            page-break-after: always;
        }
        .page-content {
            padding: 1.0cm 1.8cm 1.0cm 2.0cm;
            box-sizing: border-box;
        }

        /* KOP SURAT */
        .kop-wrapper {
            width: 100%;
            margin-bottom: 12px;
            text-align: center;
        }
        .kop-wrapper img {
            width: 100%;
            max-height: 105px;
            object-fit: contain;
        }
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2.5px solid #000;
            padding-bottom: 3px;
            margin-bottom: 12px;
        }
        .kop-table td {
            vertical-align: middle;
        }
        .kop-logo {
            width: 58px;
            text-align: left;
        }
        .kop-text-box {
            text-align: center;
            padding-right: 15px;
        }
        .kop-t1 {
            font-size: 10pt;
            font-weight: bold;
            letter-spacing: 0.2px;
            line-height: 1.2;
        }
        .kop-t2 {
            font-size: 9.5pt;
            font-weight: bold;
            letter-spacing: 0.2px;
            line-height: 1.2;
        }
        .kop-t3 {
            font-size: 9.5pt;
            font-weight: bold;
            letter-spacing: 0.2px;
            line-height: 1.2;
        }
        .kop-t4 {
            font-size: 6.8pt;
            margin-top: 2px;
            line-height: 1.15;
        }

        /* JUDUL & NO SURAT */
        .title-box {
            text-align: center;
            margin-bottom: 10px;
        }
        .title-text {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .title-no {
            font-size: 9pt;
            margin-top: 2px;
        }

        /* PARAGRAF & PASAL */
        .intro-text {
            text-align: justify;
            margin-bottom: 8px;
            line-height: 1.25;
            font-size: 9.5pt;
        }
        .pihak-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .pihak-table td {
            padding: 0.5px 0;
            vertical-align: top;
            font-size: 9.5pt;
            line-height: 1.25;
        }
        .pasal-block {
            margin-bottom: 6px;
        }
        .pasal-title {
            text-align: center;
            font-weight: bold;
            font-size: 9.5pt;
            margin-bottom: 2px;
        }
        .pasal-content {
            text-align: justify;
            line-height: 1.25;
            font-size: 9.5pt;
        }

        /* TANDA TANGAN HALAMAN 2 */
        .ttd-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 45px;
        }
        .ttd-table td {
            width: 50%;
            vertical-align: top;
            font-size: 9.5pt;
            line-height: 1.25;
        }

        /* HALAMAN 3 (ROTATED LANDSCAPE 270 DEG) */
        .page3-container {
            width: 210mm;
            height: 297mm;
            position: relative;
            overflow: hidden;
        }
        .page3-rotated {
            position: absolute;
            top: 297mm;
            left: 0;
            width: 297mm;
            height: 210mm;
            transform: rotate(270deg);
            transform-origin: top left;
            padding: 1.5cm 1.8cm 1.2cm 1.8cm;
            box-sizing: border-box;
        }
        .lampiran-title {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-bottom: 14px;
            text-transform: uppercase;
        }
        .lampiran-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            line-height: 1.2;
        }
        .lampiran-table th {
            border: 1px solid #000;
            padding: 4px 3px;
            text-align: center;
            font-weight: bold;
            vertical-align: middle;
        }
        .lampiran-table td {
            border: 1px solid #000;
            padding: 4px 4px;
            vertical-align: top;
        }
        .lampiran-ttd {
            width: 100%;
            border-collapse: collapse;
            margin-top: 35px;
        }
        .lampiran-ttd td {
            width: 50%;
            vertical-align: top;
            font-size: 9pt;
            line-height: 1.25;
        }
    </style>
</head>
<body>

<!-- ==================== HALAMAN 1 ==================== -->
<div class="page-content">
    <?php if (! empty($kopSuratImg)): ?>
        <div class="kop-wrapper">
            <?= $kopSuratImg; ?>
        </div>
    <?php else: ?>
        <table class="kop-table">
            <tr>
                <td class="kop-logo">
                    <?php if (! empty($logoBase64)): ?>
                        <img src="<?= $logoBase64; ?>" style="width: 54px; height: auto;" alt="Logo PU">
                    <?php endif; ?>
                </td>
                <td class="kop-text-box">
                    <div class="kop-t1">KEMENTERIAN PEKERJAAN UMUM DAN PERUMAHAN RAKYAT</div>
                    <div class="kop-t2">DIREKTORAT JENDERAL PRASARANA STRATEGIS</div>
                    <div class="kop-t3">SATKER PELAKSANAAN PRASARANA STRATEGIS RIAU</div>
                    <div class="kop-t4">Jl. Bakti Ruko Komplek Perumahan Mutiara Asri Garden, Kel. Sidomulyo Timur Kec. Marpoyan Damai</div>
                </td>
            </tr>
        </table>
    <?php endif; ?>

    <div class="title-box">
        <div class="title-text">SURAT PERJANJIAN PINJAM PAKAI</div>
        <div class="title-no">Nomor : <?= ! empty($loan['no_surat']) ? esc($loan['no_surat']) : '................................................'; ?></div>
    </div>

    <div class="intro-text"><?= esc($introText ?? ''); ?></div>

    <table class="pihak-table">
        <tr>
            <td style="width: 20px;">1.</td>
            <td style="width: 55px;">Nama</td>
            <td style="width: 10px;">:</td>
            <td><?= esc($kasatker['nama'] ?? 'Muhammad Yudi Prasetya, ST.'); ?></td>
        </tr>
        <tr>
            <td></td>
            <td>NIP</td>
            <td>:</td>
            <td><?= esc($kasatker['nip'] ?? '198002142014121002'); ?></td>
        </tr>
        <tr>
            <td></td>
            <td>Jabatan</td>
            <td>:</td>
            <td style="text-align: justify;">Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau selaku Kuasa Penguna Barang Milik Negara, yang bertindak untuk atas nama Satuan Kerja Pelaksanaan Prasarana Strategis Riau,<br>Yang selanjutnya disebut <strong>PIHAK PERTAMA</strong>.</td>
        </tr>
    </table>

    <table class="pihak-table">
        <tr>
            <td style="width: 20px;">2.</td>
            <td style="width: 55px;">Nama</td>
            <td style="width: 10px;">:</td>
            <td><?= esc($loan['nama_peminjam'] ?? ''); ?></td>
        </tr>
        <tr>
            <td></td>
            <td>NIP</td>
            <td>:</td>
            <td><?= esc($loan['nip_peminjam'] ?? '') ?: '-'; ?></td>
        </tr>
        <tr>
            <td></td>
            <td>Jabatan</td>
            <td>:</td>
            <td style="text-align: justify;"><?= esc($loan['jabatan_peminjam'] ?? '') ?: 'Pegawai'; ?> Satuan Kerja Pelaksanaan Prasarana Strategis Riau,<br>Yang selanjutnya disebut <strong>PIHAK KEDUA</strong>.</td>
        </tr>
    </table>

    <div class="intro-text" style="margin-left: 20px; margin-top: 4px;">
        Kedua belah pihak sepakat untuk membuat Perjanjian Pinjam Pakai Barang Milik Negara sebagaimana tercantum pada Lampiran Surat Perjanjian ini, dengan ketentuan sebagai berikut:
    </div>

    <div class="pasal-block">
        <div class="pasal-title">Pasal 1</div>
        <div class="pasal-content">
            PIHAK PERTAMA meminjamkan kepada PIHAK KEDUA dan PIHAK KEDUA menerima Pinjam Pakai Barang Milik Negara dari PIHAK PERTAMA untuk menunjang pelaksanaan tugas dan fungsi pada Satuan Kerja Pelaksanaan Prasarana Strategis Riau dengan rincian barang sebagaimana tercantum pada Berita Acara Serah Terima Pinjam Pakai yang merupakan bagian yang tidak terpisahkan dari Surat Perjanjian Pinjam Pakai ini.
        </div>
    </div>

    <div class="pasal-block">
        <div class="pasal-title">Pasal 2</div>
        <div class="pasal-content">
            PIHAK PERTAMA meminjamkan kepada PIHAK KEDUA Barang Milik Negara sebagaimana dimaksud pada Pasal 1 sampai berakhirnya tahun anggaran (31 Desember <?= esc($tahunPinjam ?? date('Y')); ?>)<?= ! empty($loan['tgl_kembali_rencana']) ? ' atau sampai dengan tanggal ' . date('d/m/Y', strtotime($loan['tgl_kembali_rencana'])) : ''; ?>, dan dapat diperpanjang jika diperlukan..
        </div>
    </div>

    <div class="pasal-block">
        <div class="pasal-title">Pasal 3</div>
        <div class="pasal-content">
            PIHAK PERTAMA menyerahkan Barang Milik Negara sebagaimana dimaksud Pasal 1 kepada PIHAK KEDUA dalam keadaan baik dan cukup serta siap dipergunakan oleh PIHAK KEDUA.
        </div>
    </div>

    <div class="pasal-block">
        <div class="pasal-title">Pasal 4</div>
        <div class="pasal-content">
            PIHAK KEDUA berkewajiban menjaga dengan baik terhadap Barang Milik Negara sebagaimana dimaksud pada Pasal 1, baik secara fisik maupun administrasi serta bertanggung jawab terhadap kerusakan dan kehilangan selama jangka waktu masa pinjam pakai.
        </div>
    </div>
</div>

<div class="page-break"></div>

<!-- ==================== HALAMAN 2 ==================== -->
<div class="page-content" style="padding-top: 1.5cm;">
    <div class="pasal-block">
        <div class="pasal-title">Pasal 5</div>
        <div class="pasal-content">
            PIHAK KEDUA tidak diperkenankan melakukan perubahan bentuk/dan atau Konstruksi Dasar Barang Milik Negara sebagaimana dimaksud pada Pasal 1 tanpa persetujuan PIHAK PERTAMA.
        </div>
    </div>

    <div class="pasal-block">
        <div class="pasal-title">Pasal 6</div>
        <div class="pasal-content">
            Setiap kerugian negara akibat penyalahgunaan/pelanggaran hukum atas pemakaian barang milik negara diselesaikan melalui tuntutan ganti rugi dan dapat dikenakan sanksi sesuai dengan ketentuan peraturan perundang - undangan.
        </div>
    </div>

    <div class="pasal-block">
        <div class="pasal-title">Pasal 7</div>
        <div class="pasal-content">
            Pelaksanaan Pinjam Pakai Barang Milik Negara sebagaimana dimaksud pada Pasal 1 dari PIHAK PERTAMA kepada PIHAK KEDUA tanpa dikenakan biaya apapun.
        </div>
    </div>

    <div class="pasal-block">
        <div class="pasal-title">Pasal 8</div>
        <div class="pasal-content">
            Surat Perjanjian Pinjam Pakai ini ditandatangani oleh kedua belah pihak dalam keadaan sehat jasmani dan rohani serta tanpa ada unsur paksaan dari pihak manapun.
        </div>
    </div>

    <table class="ttd-table" style="margin-top: 45px;">
        <tr>
            <td style="text-align: center;">
                <strong>PIHAK KEDUA</strong><br>
                Yang menerima,<br><br><br><br><br><br>
                <strong><?= esc($loan['nama_peminjam'] ?? ''); ?></strong><br>
                NIP. <?= esc($loan['nip_peminjam'] ?? '') ?: '-'; ?>
            </td>
            <td style="text-align: center;">
                <strong>PIHAK PERTAMA</strong><br>
                yang menyerahkan,<br>
                Kepala Satuan Kerja<br>
                Pelaksanaan Prasarana Strategis Riau<br>
                Selaku Kuasa Penguna Barang,<br><br><br><br>
                <strong><?= esc($kasatker['nama'] ?? 'Muhammad Yudi Prasetya, ST.'); ?></strong><br>
                NIP. <?= esc($kasatker['nip'] ?? '198002142014121002'); ?>
            </td>
        </tr>
    </table>
</div>

<div class="page-break"></div>

<!-- ==================== HALAMAN 3 (LAMPIRAN TABEL ROTATED) ==================== -->
<div class="page3-container">
    <div class="page3-rotated">
        <div class="lampiran-title">DAFTAR BARANG MILIK NEGARA YANG DIPINJAM PAKAI</div>
        <table class="lampiran-table">
            <thead>
                <tr>
                    <th style="width: 25px;">No</th>
                    <th style="width: 75px;">Kode Barang</th>
                    <th style="width: 90px;">Nama Barang</th>
                    <th style="width: 35px;">NUP</th>
                    <th>Jenis Barang</th>
                    <th style="width: 55px;">Tahun<br>Perolehan</th>
                    <th style="width: 45px;">Jumlah</th>
                    <th style="width: 80px;">Nilai Aset<br>per Unit<br>(Rp.)</th>
                    <th style="width: 55px;">Kondisi<br>Barang</th>
                    <th style="width: 125px;">Keterangan</th>
                </tr>
                <tr style="font-size: 7pt; background: #fdfdfd;">
                    <th>1</th><th>2</th><th>3</th><th>4</th><th>5</th><th>6</th><th>7</th><th>8</th><th>9</th><th>10</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center;">1.</td>
                    <td style="text-align: center;"><?= esc($loan['kode_barang'] ?? ''); ?></td>
                    <td><?= esc($loan['nama_barang'] ?? ''); ?></td>
                    <td style="text-align: center;"><?= esc($loan['nup'] ?? ''); ?></td>
                    <td>
                        Tipe : <?= esc($loan['merk_tipe'] ?? '-') ?: '-'; ?>
                        <?= ! empty($loan['kelengkapan']) ? ' (Kelengkapan: ' . esc($loan['kelengkapan']) . ')' : ''; ?>
                    </td>
                    <td style="text-align: center;"><?= esc($loan['tahun_perolehan'] ?? '-') ?: '-'; ?></td>
                    <td style="text-align: center;">1</td>
                    <td style="text-align: right;"><?= number_format((float) ($loan['nilai_perolehan'] ?? 0), 0, ',', '.'); ?></td>
                    <td style="text-align: center;"><?= ucwords(str_replace('_', ' ', (string) ($loan['kondisi_pinjam'] ?? 'Baik'))); ?></td>
                    <td><?= ! empty($loan['catatan']) ? esc($loan['catatan']) : 'Tercatat pada Inventarisasi Aset Satker Pelaksanaan Prasarana Strategis'; ?></td>
                </tr>
            </tbody>
        </table>

        <table class="lampiran-ttd">
            <tr>
                <td style="text-align: center;">
                    <strong>PIHAK KEDUA</strong><br>
                    Yang menerima,<br><br><br><br><br>
                    <strong><?= esc($loan['nama_peminjam'] ?? ''); ?></strong><br>
                    NIP. <?= esc($loan['nip_peminjam'] ?? '') ?: '-'; ?>
                </td>
                <td style="text-align: center;">
                    <strong>PIHAK PERTAMA</strong><br>
                    Yang menyerahkan,<br>
                    Satuan Kerja Pelaksanaan Prasarana Strategis Riau<br>
                    Selaku Kuasa Penguna Barang,<br><br><br>
                    <strong><?= esc($kasatker['nama'] ?? 'Muhammad Yudi Prasetya, ST.'); ?></strong><br>
                    NIP. <?= esc($kasatker['nip'] ?? '198002142014121002'); ?>
                </td>
            </tr>
        </table>
    </div>
</div>

</body>
</html>
