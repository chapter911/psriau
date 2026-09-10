<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Izin Pinjam Pakai BMN - <?= esc($loan['no_surat']); ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.2cm 1.5cm 1.2cm 1.5cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8.5pt;
            line-height: 1.35;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }

        /* Kop Surat */
        .kop-wrapper {
            text-align: center;
            margin-bottom: 8px;
            width: 100%;
        }
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2.5px solid #0f172a;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }
        .kop-table td {
            vertical-align: middle;
            padding: 0;
        }
        .kop-text-1 {
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            line-height: 1.2;
        }
        .kop-text-2 {
            font-size: 9.5pt;
            font-weight: bold;
            color: #1e293b;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            line-height: 1.2;
        }
        .kop-text-3 {
            font-size: 9.5pt;
            font-weight: bold;
            color: #1e3a8a;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            line-height: 1.2;
        }
        .kop-subtext {
            font-size: 7.5pt;
            color: #475569;
            margin-top: 2px;
            line-height: 1.2;
        }

        /* Judul Dokumen */
        .doc-title-box {
            text-align: center;
            margin-bottom: 12px;
        }
        .doc-title {
            font-size: 11.5pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            text-decoration: underline;
            margin-bottom: 2px;
        }
        .doc-no {
            font-size: 9pt;
            color: #334155;
            font-weight: bold;
        }

        /* Identitas Para Pihak */
        .pihak-box {
            margin-bottom: 8px;
        }
        .pihak-intro {
            text-align: justify;
            margin-bottom: 6px;
            line-height: 1.35;
        }
        .pihak-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            margin-left: 10px;
        }
        .pihak-table td {
            padding: 1.5px 0;
            vertical-align: top;
            font-size: 8.5pt;
        }
        .pihak-lbl {
            width: 130px;
            color: #334155;
        }
        .pihak-sep {
            width: 15px;
            text-align: center;
        }
        .pihak-val {
            font-weight: bold;
            color: #0f172a;
        }

        /* Tabel Spesifikasi Aset */
        .table-aset {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin: 8px 0 10px 0;
        }
        .table-aset th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7.5pt;
            padding: 5px 6px;
            border: 1px solid #1e3a8a;
            text-align: center;
        }
        .table-aset td {
            padding: 4.5px 6px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }
        .table-aset tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        /* Ketentuan / Pasal */
        .pasal-title {
            font-weight: bold;
            font-size: 8.5pt;
            color: #0f172a;
            margin-top: 6px;
            margin-bottom: 2px;
        }
        .pasal-content {
            text-align: justify;
            margin-bottom: 4px;
            line-height: 1.35;
        }
        .pasal-list {
            margin: 2px 0 6px 18px;
            padding: 0;
            text-align: justify;
        }
        .pasal-list li {
            margin-bottom: 2px;
            line-height: 1.3;
        }

        /* Blok Tanda Tangan */
        .ttd-wrapper {
            margin-top: 14px;
            width: 100%;
            page-break-inside: avoid;
        }
        .ttd-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
        }
        .ttd-table td {
            vertical-align: top;
            width: 50%;
            padding: 0 10px;
        }
        .ttd-jabatan {
            font-size: 8.5pt;
            font-weight: bold;
            color: #0f172a;
            line-height: 1.25;
            margin-bottom: 50px;
        }
        .ttd-nama {
            font-size: 8.5pt;
            font-weight: bold;
            color: #0f172a;
            text-decoration: underline;
            line-height: 1.2;
        }
        .ttd-nip {
            font-size: 8pt;
            color: #334155;
            margin-top: 1px;
        }
    </style>
</head>
<body>

    <!-- Header / Kop Surat -->
    <div class="kop-wrapper">
        <?php if (function_exists('kop_surat_img_tag') && kop_surat_img_tag('', '', 'Kop Surat') !== ''): ?>
            <?= kop_surat_img_tag('', 'width: 100%; max-height: 110px; object-fit: contain;', 'Kop Surat'); ?>
        <?php else: ?>
            <table class="kop-table">
                <tr>
                    <?php if (! empty($logoBase64)): ?>
                        <td style="width: 70px; text-align: left;">
                            <img src="<?= $logoBase64; ?>" style="height: 60px; width: auto;" alt="Logo PU">
                        </td>
                    <?php endif; ?>
                    <td style="text-align: center;">
                        <div class="kop-text-1">KEMENTERIAN PEKERJAAN UMUM</div>
                        <div class="kop-text-2">DIREKTORAT JENDERAL PRASARANA STRATEGIS</div>
                        <div class="kop-text-3">SATUAN KERJA PELAKSANAAN PRASARANA STRATEGIS PROVINSI RIAU</div>
                        <div class="kop-subtext">Kode UAKPB: <?= esc($kodeUakpb); ?> &bull; Jl. HR. Soebrantas, Pekanbaru - Riau</div>
                    </td>
                </tr>
            </table>
        <?php endif; ?>
    </div>

    <!-- Judul Dokumen -->
    <div class="doc-title-box">
        <div class="doc-title">SURAT IZIN PINJAM PAKAI BARANG MILIK NEGARA (BMN)</div>
        <div class="doc-no">Nomor: <?= esc($loan['no_surat']); ?></div>
    </div>

    <!-- Pembukaan -->
    <div class="pihak-intro">
        Pada hari ini, tanggal <strong><?= esc($tglPinjamIndo); ?></strong>, bertempat di Kantor Satuan Kerja Pelaksanaan Prasarana Strategis Provinsi Riau, yang bertanda tangan di bawah ini:
    </div>

    <!-- Pihak Pertama -->
    <div class="pihak-box">
        <table class="pihak-table">
            <tr>
                <td style="width: 20px;">1.</td>
                <td class="pihak-lbl">Nama</td>
                <td class="pihak-sep">:</td>
                <td class="pihak-val"><?= esc($kasatker['nama']); ?></td>
            </tr>
            <tr>
                <td></td>
                <td class="pihak-lbl">NIP</td>
                <td class="pihak-sep">:</td>
                <td class="pihak-val"><?= esc($kasatker['nip']); ?></td>
            </tr>
            <tr>
                <td></td>
                <td class="pihak-lbl">Jabatan</td>
                <td class="pihak-sep">:</td>
                <td class="pihak-val"><?= esc($kasatker['jabatan']); ?></td>
            </tr>
            <tr>
                <td></td>
                <td class="pihak-lbl">Unit Kerja</td>
                <td class="pihak-sep">:</td>
                <td class="pihak-val">Satker Pelaksanaan Prasarana Strategis Provinsi Riau</td>
            </tr>
        </table>
        <div style="font-size: 8pt; margin-left: 20px; color: #475569; margin-bottom: 6px;">
            Bertindak untuk dan atas nama Kuasa Pengguna Barang (KPB) Satker PPS Riau, selanjutnya disebut sebagai <strong>PIHAK PERTAMA</strong>.
        </div>
    </div>

    <!-- Pihak Kedua -->
    <div class="pihak-box">
        <table class="pihak-table">
            <tr>
                <td style="width: 20px;">2.</td>
                <td class="pihak-lbl">Nama Lengkap</td>
                <td class="pihak-sep">:</td>
                <td class="pihak-val"><?= esc($loan['nama_peminjam']); ?></td>
            </tr>
            <tr>
                <td></td>
                <td class="pihak-lbl">NIP</td>
                <td class="pihak-sep">:</td>
                <td class="pihak-val"><?= esc($loan['nip_peminjam'] ?: '-'); ?></td>
            </tr>
            <tr>
                <td></td>
                <td class="pihak-lbl">Jabatan</td>
                <td class="pihak-sep">:</td>
                <td class="pihak-val"><?= esc($loan['jabatan_peminjam'] ?: '-'); ?></td>
            </tr>
            <tr>
                <td></td>
                <td class="pihak-lbl">No. Kontak / HP</td>
                <td class="pihak-sep">:</td>
                <td class="pihak-val"><?= esc($loan['kontak_peminjam'] ?: '-'); ?></td>
            </tr>
        </table>
        <div style="font-size: 8pt; margin-left: 20px; color: #475569; margin-bottom: 6px;">
            Bertindak selaku Pegawai Pengguna / Peminjam, selanjutnya disebut sebagai <strong>PIHAK KEDUA</strong>.
        </div>
    </div>

    <div class="pihak-intro">
        Kedua belah pihak telah bersepakat untuk mengadakan perjanjian Pinjam Pakai Barang Milik Negara (BMN) berupa aset inventaris kedinasan dengan rincian data sebagai berikut:
    </div>

    <!-- Rincian Aset BMN -->
    <table class="table-aset">
        <thead>
            <tr>
                <th style="width: 28px;">No</th>
                <th>Nama Barang / Aset</th>
                <th style="width: 100px;">Kode Barang</th>
                <th style="width: 45px;">NUP</th>
                <th style="width: 110px;">Merk / Spesifikasi</th>
                <th style="width: 50px;">Tahun</th>
                <th style="width: 75px;">Kondisi</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center;">1</td>
                <td><strong><?= esc($loan['nama_barang']); ?></strong></td>
                <td style="text-align: center; font-family: monospace;"><?= esc($loan['kode_barang']); ?></td>
                <td style="text-align: center; font-weight: bold;"><?= esc($loan['nup']); ?></td>
                <td><?= esc($loan['merk_tipe'] ?: '-'); ?></td>
                <td style="text-align: center;"><?= esc($loan['tahun_perolehan'] ?: '-'); ?></td>
                <td style="text-align: center; font-weight: bold;"><?= esc(ucwords(str_replace('_', ' ', $loan['kondisi_pinjam'] ?? 'baik'))); ?></td>
            </tr>
            <?php if (! empty($loan['kelengkapan'])): ?>
                <tr>
                    <td colspan="7" style="background-color: #f1f5f9; font-size: 7.5pt;">
                        <strong>Kelengkapan / Aksesoris yang Diserahkan:</strong> <?= esc($loan['kelengkapan']); ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Syarat dan Ketentuan -->
    <div class="pasal-title">Pasal 1 &mdash; Keperluan Peminjaman</div>
    <div class="pasal-content">
        Barang Milik Negara sebagaimana dimaksud di atas dipinjam pakai oleh <strong>PIHAK KEDUA</strong> semata-mata untuk menunjang kelancaran pelaksanaan tugas dan fungsi kedinasan, yaitu: <em>"<?= esc($loan['keperluan']); ?>"</em>.
    </div>

    <div class="pasal-title">Pasal 2 &mdash; Jangka Waktu Pinjam Pakai</div>
    <div class="pasal-content">
        Peminjaman berlaku terhitung sejak tanggal <strong><?= esc($tglPinjamIndo); ?></strong> sampai dengan 
        <strong><?= ! empty($loan['tgl_kembali_rencana']) ? date('d-m-Y', strtotime($loan['tgl_kembali_rencana'])) : 'selesainya kebutuhan penugasan dinas'; ?></strong>. Apabila sewaktu-waktu barang tersebut dibutuhkan oleh Satker, <strong>PIHAK KEDUA</strong> bersedia menyerahkannya kembali kepada <strong>PIHAK PERTAMA</strong>.
    </div>

    <div class="pasal-title">Pasal 3 &mdash; Hak dan Kewajiban Peminjam</div>
    <ol class="pasal-list">
        <li><strong>PIHAK KEDUA</strong> berkewajiban merawat, menjaga keamanan fisik, dan memelihara keutuhan barang yang dipinjam pakai secara cermat dan bertanggung jawab.</li>
        <li><strong>PIHAK KEDUA</strong> tidak dibenarkan memindahtangankan, meminjamkan kembali, menyewakan, atau mengalihkan penguasaan barang tersebut kepada pihak ketiga mana pun tanpa persetujuan tertulis dari <strong>PIHAK PERTAMA</strong>.</li>
        <li>Apabila terjadi kehilangan atau kerusakan pada aset yang diakibatkan oleh kelalaian peminjam, maka <strong>PIHAK KEDUA</strong> bersedia bertanggung jawab penuh untuk memperbaiki atau mengganti barang tersebut sesuai dengan ketentuan Peraturan Perundang-undangan Pengelolaan BMN yang berlaku.</li>
        <li>Setelah jangka waktu pinjam pakai selesai, <strong>PIHAK KEDUA</strong> wajib mengembalikan barang tersebut kepada Petugas Pengurus Barang Pengguna dalam kondisi baik dan lengkap.</li>
    </ol>

    <!-- Penutup & Tanda Tangan -->
    <div class="pihak-intro" style="margin-top: 6px;">
        Demikian Surat Izin Pinjam Pakai ini dibuat dengan sebenarnya dalam rangkap 2 (dua) untuk dipergunakan sebagaimana mestinya.
    </div>

    <div class="ttd-wrapper">
        <div style="text-align: right; margin-bottom: 8px; font-size: 8.5pt;">
            Pekanbaru, <?= esc($tglCetak); ?>
        </div>
        <table class="ttd-table">
            <tr>
                <td>
                    <div class="ttd-jabatan">
                        Yang Menerima Pinjam Pakai<br>
                        <strong>PIHAK KEDUA</strong>
                    </div>
                    <div class="ttd-nama"><?= esc($loan['nama_peminjam']); ?></div>
                    <div class="ttd-nip">NIP. <?= esc($loan['nip_peminjam'] ?: '-'); ?></div>
                </td>
                <td>
                    <div class="ttd-jabatan">
                        Kuasa Pengguna Barang<br>
                        <strong>PIHAK PERTAMA</strong>
                    </div>
                    <div class="ttd-nama"><?= esc($kasatker['nama']); ?></div>
                    <div class="ttd-nip">NIP. <?= esc($kasatker['nip']); ?></div>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding-top: 15px;">
                    <div class="ttd-jabatan" style="margin-bottom: 45px;">
                        Mengetahui / Memeriksa Penatausahaan BMN:<br>
                        <strong>Pengurus Barang Pengguna</strong>
                    </div>
                    <div class="ttd-nama"><?= esc($pengurusBarang['nama']); ?></div>
                    <div class="ttd-nip">NIP. <?= esc($pengurusBarang['nip']); ?></div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
