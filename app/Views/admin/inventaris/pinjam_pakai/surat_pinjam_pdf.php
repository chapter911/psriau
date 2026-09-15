<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Perjanjian Pinjam Pakai BMN - <?= ! empty($loan['no_surat']) ? esc($loan['no_surat']) : 'Draft'; ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0.8cm 2.0cm 0.6cm 2.5cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            line-height: 1.20;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .page-break {
            page-break-before: always;
            clear: both;
        }

        /* KOP SURAT */
        .kop-wrapper {
            width: 100%;
            margin-top: 0;
            margin-bottom: 4px;
            text-align: center;
        }
        .kop-wrapper img {
            width: 100%;
            max-height: 85px;
            object-fit: contain;
        }
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #000;
            padding-bottom: 1px;
            margin-top: 0;
            margin-bottom: 4px;
        }
        .kop-table td {
            vertical-align: middle;
        }
        .kop-logo {
            width: 55px;
            text-align: left;
        }
        .kop-text-box {
            text-align: center;
            padding-right: 15px;
        }
        .kop-t1 {
            font-size: 11.5pt;
            font-weight: bold;
            letter-spacing: 0.2px;
            line-height: 1.15;
        }
        .kop-t2 {
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 0.2px;
            line-height: 1.15;
        }
        .kop-t3 {
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 0.2px;
            line-height: 1.15;
        }
        .kop-t4 {
            font-size: 7.5pt;
            margin-top: 1px;
            line-height: 1.15;
        }

        /* JUDUL & NO SURAT */
        .title-box {
            text-align: center;
            margin-bottom: 12px;
            margin-top: 12px;
        }
        .title-text {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            line-height: 1.2;
        }
        .title-no {
            font-size: 11pt;
            font-weight: bold;
            margin-top: 2px;
            line-height: 1.2;
        }

        /* PARAGRAF & PASAL */
        .intro-text {
            text-align: justify;
            font-size: 11pt;
            line-height: 1.20;
            margin-bottom: 12px;
        }
        .pihak-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 11pt;
        }
        .pihak-table td {
            padding: 0;
            vertical-align: top;
            font-size: 11pt;
            line-height: 1.20;
        }
        .pasal-block {
            margin-bottom: 12px;
        }
        .pasal-title {
            text-align: center;
            font-size: 11pt;
            margin-top: 10px;
            margin-bottom: 9pt; /* 1 baris kosong setelah Pasal */
            line-height: 1.20;
        }
        .pasal-content {
            text-align: justify;
            font-size: 11pt;
            line-height: 1.20;
        }

        /* TANDA TANGAN HALAMAN 2 */
        .page-2-content {
            margin-top: 1.7cm; /* 0.8cm + 1.7cm = 2.5cm margin from top */
        }
        .ttd-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 35px;
            font-size: 11pt;
        }
        .ttd-table td {
            width: 50%;
            vertical-align: top;
            font-size: 11pt;
            line-height: 1.20;
        }
    </style>
</head>
<body>

<!-- ==================== HALAMAN 1 ==================== -->
<div>
    <?php if (! empty($kopSuratImg)): ?>
        <div class="kop-wrapper">
            <?= $kopSuratImg; ?>
        </div>
    <?php else: ?>
        <table class="kop-table">
            <tr>
                <td class="kop-logo">
                    <?php if (! empty($logoBase64)): ?>
                        <img src="<?= $logoBase64; ?>" style="width: 58px; height: auto;" alt="Logo PU">
                    <?php endif; ?>
                </td>
                <td class="kop-text-box">
                    <div class="kop-t1">KEMENTERIAN PEKERJAAN UMUM</div>
                    <div class="kop-t2">DIREKTORAT JENDERAL PRASARANA STRATEGIS</div>
                    <div class="kop-t3">SATUAN KERJA PELAKSANAAN PRASARANA STRATEGIS</div>
                    <div class="kop-t4">Jalan Datuk Setia Maharaja No. 15 Tangkerang Labuai, Bukit Raya, Riau 28125, email : satkerppsriau@pu.go.id</div>
                </td>
            </tr>
        </table>
    <?php endif; ?>

    <div class="title-box">
        <div class="title-text">SURAT PERJANJIAN PINJAM PAKAI</div>
        <?php if (! empty($loan['no_surat'])): ?>
            <div class="title-no">NOMOR : <?= esc($loan['no_surat']); ?></div>
        <?php endif; ?>
    </div>

    <div class="intro-text"><?= $introText ?? ''; ?></div>

    <table class="pihak-table">
        <tr>
            <td style="width: 0.75cm;">1.</td>
            <td style="width: 2.5cm;">Nama</td>
            <td style="width: 0.5cm;">:</td>
            <td><?= esc($pihakPertama['nama'] ?? ($kasatker['nama'] ?? 'Muhammad Yudi Prasetya, ST.')); ?></td>
        </tr>
        <tr>
            <td></td>
            <td>NIP</td>
            <td>:</td>
            <td><?= esc($pihakPertama['nip'] ?? ($kasatker['nip'] ?? '198002142014121002')); ?></td>
        </tr>
        <tr>
            <td></td>
            <td>Jabatan</td>
            <td>:</td>
            <td style="text-align: justify;"><?= esc($pihakPertama['jabatan'] ?? 'Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau selaku Kuasa Penguna Barang Milik Negara'); ?>, yang bertindak untuk atas nama Satuan Kerja Pelaksanaan Prasarana Strategis Riau,<br>Yang selanjutnya disebut <strong>PIHAK PERTAMA</strong>.</td>
        </tr>
        <tr>
            <td colspan="4" style="height: 10px;"></td>
        </tr>
        <tr>
            <td>2.</td>
            <td>Nama</td>
            <td>:</td>
            <td><?= esc($loan['nama_peminjam'] ?? ''); ?></td>
        </tr>
        <tr>
            <td></td>
            <td>NIP</td>
            <td>:</td>
            <td><?= esc($nipPeminjamDisplay ?? (! empty($isKonsultan) ? 'Tenaga Penunjang Kegiatan' : ($loan['nip_peminjam'] ?: '-'))); ?></td>
        </tr>
        <tr>
            <td></td>
            <td>Jabatan</td>
            <td>:</td>
            <td style="text-align: justify;"><?= esc($loan['jabatan_peminjam'] ?? '') ?: 'Pegawai'; ?>, Satuan Kerja Pelaksanaan Prasarana Strategis Riau,<br>Yang selanjutnya disebut <strong>PIHAK KEDUA</strong>.</td>
        </tr>
    </table>

    <div class="intro-text" style="margin-bottom: 12px;">
        Kedua belah pihak sepakat untuk membuat Perjanjian Pinjam Pakai Barang Milik Negara sebagaimana tercantum pada Lampiran Surat Perjanjian ini, dengan ketentuan sebagai berikut:
    </div>

    <div class="pasal-block">
        <div class="pasal-title">Pasal 1</div>
        <div class="pasal-content">
            <strong>PIHAK PERTAMA</strong> meminjamkan kepada <strong>PIHAK KEDUA</strong> dan <strong>PIHAK KEDUA</strong> menerima Pinjam Pakai Barang Milik Negara dari <strong>PIHAK PERTAMA</strong> untuk menunjang pelaksanaan tugas dan fungsi pada Satuan Kerja Pelaksanaan Prasarana Strategis Riau dengan rincian barang sebagaimana tercantum pada Berita Acara Serah Terima Pinjam Pakai yang merupakan bagian yang tidak terpisahkan dari Surat Perjanjian Pinjam Pakai ini.
        </div>
    </div>

    <div class="pasal-block">
        <div class="pasal-title">Pasal 2</div>
        <div class="pasal-content">
            <strong>PIHAK PERTAMA</strong> meminjamkan kepada <strong>PIHAK KEDUA</strong> Barang Milik Negara sebagaimana dimaksud pada Pasal 1 sampai berakhirnya tahun anggaran (31 Desember <?= esc($tahunPinjam ?? date('Y')); ?>)<?= ! empty($loan['tgl_kembali_rencana']) ? ' atau sampai dengan tanggal ' . date('d/m/Y', strtotime($loan['tgl_kembali_rencana'])) : ''; ?>, dan dapat diperpanjang jika diperlukan.
        </div>
    </div>

    <div class="pasal-block">
        <div class="pasal-title">Pasal 3</div>
        <div class="pasal-content">
            <strong>PIHAK PERTAMA</strong> menyerahkan Barang Milik Negara sebagaimana dimaksud pada Pasal 1 kepada <strong>PIHAK KEDUA</strong> dalam keadaan baik dan cukup serta siap dipergunakan oleh <strong>PIHAK KEDUA</strong>.
        </div>
    </div>

    <div class="pasal-block">
        <div class="pasal-title">Pasal 4</div>
        <div class="pasal-content">
            <strong>PIHAK KEDUA</strong> berkewajiban menjaga dengan baik terhadap Barang Milik Negara sebagaimana dimaksud pada Pasal 1, baik secara fisik maupun administrasi serta bertanggung jawab terhadap kerusakan dan kehilangan selama jangka waktu masa pinjam pakai.
        </div>
    </div>
</div>

<!-- ==================== HALAMAN 2 ==================== -->
<div class="page-break"></div>
<div class="page-2-content">
    <div class="pasal-block" style="margin-top: 0;">
        <div class="pasal-title" style="margin-top: 0;">Pasal 5</div>
        <div class="pasal-content">
            <strong>PIHAK KEDUA</strong> tidak diperkenankan melakukan perubahan bentuk/dan atau Konstruksi Dasar Barang Milik Negara sebagaimana dimaksud pada Pasal 1 tanpa persetujuan <strong>PIHAK PERTAMA</strong>.
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
            Pelaksanaan Pinjam Pakai Barang Milik Negara sebagaimana dimaksud pada Pasal 1 dari <strong>PIHAK PERTAMA</strong> kepada <strong>PIHAK KEDUA</strong> tanpa dikenakan biaya apapun.
        </div>
    </div>

    <div class="pasal-block">
        <div class="pasal-title">Pasal 8</div>
        <div class="pasal-content">
            Surat Perjanjian Pinjam Pakai ini ditandatangani oleh kedua belah pihak dalam keadaan sehat jasmani dan rohani serta tanpa ada unsur paksaan dari pihak manapun.
        </div>
    </div>

    <table class="ttd-table" style="margin-top: 30px;">
        <tr>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                <strong>PIHAK KEDUA</strong><br>
                Yang menerima,
            </td>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                <strong>PIHAK PERTAMA</strong><br>
                Yang menyerahkan,<br>
                Selaku Kuasa Penguna Barang,<br>
                Kepala Satuan Kerja<br>
                Pelaksanaan Prasarana Strategis Riau
            </td>
        </tr>
        <tr>
            <td style="height: 55px;"></td>
            <td style="height: 55px;"></td>
        </tr>
        <tr>
            <td style="text-align: center; vertical-align: bottom;">
                <strong><u><?= esc($loan['nama_peminjam'] ?? ''); ?></u></strong>
            </td>
            <td style="text-align: center; vertical-align: bottom;">
                <strong><u><?= esc($pihakPertama['nama'] ?? ($kasatker['nama'] ?? 'Muhammad Yudi Prasetya, ST.')); ?></u></strong>
            </td>
        </tr>
        <tr>
            <td style="text-align: center; vertical-align: top; padding-top: 2px;">
                <?php if (! empty($isKonsultan)): ?>
                    Tenaga Penunjang Kegiatan
                <?php else: ?>
                    NIP. <?= esc($loan['nip_peminjam'] ?? '') ?: '-'; ?>
                <?php endif; ?>
            </td>
            <td style="text-align: center; vertical-align: top; padding-top: 2px;">
                NIP. <?= esc($pihakPertama['nip'] ?? ($kasatker['nip'] ?? '198002142014121002')); ?>
            </td>
        </tr>
    </table>
</div>

</body>
</html>

