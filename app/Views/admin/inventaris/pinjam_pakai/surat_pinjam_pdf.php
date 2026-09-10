<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Izin Pinjam Pakai BMN - <?= esc($loan['no_surat'] ?? '') ?: 'Draft'; ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0.8cm 1.3cm 0.8cm 1.3cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8pt;
            line-height: 1.3;
            color: #0f172a;
            margin: 0;
            padding: 0;
        }

        /* Kop Surat */
        .kop-wrapper {
            text-align: center;
            margin-bottom: 6px;
            width: 100%;
        }
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2.5px solid #0f172a;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }
        .kop-table td {
            vertical-align: middle;
            padding: 0;
        }
        .kop-text-1 {
            font-size: 10.5pt;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            line-height: 1.15;
        }
        .kop-text-2 {
            font-size: 9pt;
            font-weight: bold;
            color: #1e293b;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            line-height: 1.15;
        }
        .kop-text-3 {
            font-size: 9pt;
            font-weight: bold;
            color: #1e3a8a;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            line-height: 1.15;
        }
        .kop-subtext {
            font-size: 7.5pt;
            color: #475569;
            margin-top: 2px;
            line-height: 1.15;
        }

        /* Judul Dokumen */
        .doc-title-box {
            text-align: center;
            margin-bottom: 8px;
        }
        .doc-title {
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-decoration: underline;
            margin-bottom: 2px;
        }
        .doc-no {
            font-size: 8.5pt;
            color: #334155;
            font-weight: bold;
        }

        /* Identitas Para Pihak */
        .pihak-box {
            margin-bottom: 4px;
        }
        .pihak-intro {
            text-align: justify;
            margin-bottom: 4px;
            line-height: 1.25;
            font-size: 8pt;
        }
        .pihak-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
            margin-left: 10px;
        }
        .pihak-table td {
            padding: 1px 0;
            vertical-align: top;
            font-size: 8pt;
        }
        .pihak-lbl {
            width: 120px;
            color: #334155;
        }
        .pihak-sep {
            width: 12px;
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
            font-size: 7.5pt;
            margin: 6px 0 6px 0;
        }
        .table-aset th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7pt;
            padding: 4px 5px;
            border: 1px solid #1e3a8a;
            text-align: center;
        }
        .table-aset td {
            padding: 3.5px 5px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }
        .table-aset tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        /* Ketentuan / Pasal */
        .pasal-title {
            font-weight: bold;
            font-size: 8pt;
            color: #0f172a;
            margin-top: 4px;
            margin-bottom: 1px;
        }
        .pasal-content {
            text-align: justify;
            margin-bottom: 3px;
            line-height: 1.25;
            font-size: 8pt;
        }
        .pasal-list {
            margin: 2px 0 4px 16px;
            padding: 0;
            text-align: justify;
            font-size: 7.5pt;
        }
        .pasal-list li {
            margin-bottom: 1.5px;
            line-height: 1.2;
        }

        /* Blok Tanda Tangan */
        .ttd-wrapper {
            margin-top: 8px;
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
            padding: 0 8px;
        }
        .ttd-jabatan {
            font-size: 8pt;
            font-weight: bold;
            color: #0f172a;
            line-height: 1.2;
            margin-bottom: 36px;
        }
        .ttd-nama {
            font-size: 8pt;
            font-weight: bold;
            color: #0f172a;
            text-decoration: underline;
            line-height: 1.15;
        }
        .ttd-nip {
            font-size: 7.5pt;
            color: #334155;
            margin-top: 1px;
        }
    </style>
</head>
<body>

    <!-- Header / Kop Surat -->
    <div class="kop-wrapper">
        <?php if (! empty($kopSuratImg)): ?>
            <?= $kopSuratImg; ?>
        <?php elseif (function_exists('kop_surat_img_tag') && kop_surat_img_tag('', '', 'Kop Surat') !== ''): ?>
            <?= kop_surat_img_tag('', 'width: 100%; max-height: 110px; object-fit: contain;', 'Kop Surat'); ?>
        <?php else: ?>
            <table class="kop-table">
                <tr>
                    <?php if (! empty($logoBase64)): ?>
                        <td style="width: 60px; text-align: left;">
                            <img src="<?= $logoBase64; ?>" style="height: 52px; width: auto;" alt="Logo PU">
                        </td>
                    <?php endif; ?>
                    <td style="text-align: center;">
                        <div class="kop-text-1">KEMENTERIAN PEKERJAAN UMUM</div>
                        <div class="kop-text-2">DIREKTORAT JENDERAL PRASARANA STRATEGIS</div>
                        <div class="kop-text-3">SATUAN KERJA PELAKSANAAN PRASARANA STRATEGIS PROVINSI RIAU</div>
                        <div class="kop-subtext">Kode UAKPB: <?= esc($kodeUakpb ?? '145060900691285000KP'); ?> &bull; Jl. HR. Soebrantas, Pekanbaru - Riau</div>
                    </td>
                </tr>
            </table>
        <?php endif; ?>
    </div>

    <!-- Judul Dokumen -->
    <div class="doc-title-box">
        <div class="doc-title">SURAT IZIN PINJAM PAKAI BARANG MILIK NEGARA (BMN)</div>
        <div class="doc-no">Nomor: <?= ! empty($loan['no_surat']) ? esc($loan['no_surat']) : '................................................'; ?></div>
    </div>

    <!-- Pembukaan -->
    <div class="pihak-intro">
        Pada hari ini, tanggal <strong><?= esc($tglPinjamIndo ?? ''); ?></strong>, bertempat di Kantor Satuan Kerja Pelaksanaan Prasarana Strategis Provinsi Riau, yang bertanda tangan di bawah ini:
    </div>

    <!-- Pihak Pertama -->
    <div class="pihak-box">
        <table class="pihak-table">
            <tr>
                <td style="width: 18px;">1.</td>
                <td class="pihak-lbl">Nama</td>
                <td class="pihak-sep">:</td>
                <td class="pihak-val"><?= esc($kasatker['nama'] ?? ''); ?></td>
            </tr>
            <tr>
                <td></td>
                <td class="pihak-lbl">NIP</td>
                <td class="pihak-sep">:</td>
                <td class="pihak-val"><?= esc($kasatker['nip'] ?? ''); ?></td>
            </tr>
            <tr>
                <td></td>
                <td class="pihak-lbl">Jabatan</td>
                <td class="pihak-sep">:</td>
                <td class="pihak-val"><?= esc($kasatker['jabatan'] ?? ''); ?></td>
            </tr>
            <tr>
                <td></td>
                <td class="pihak-lbl">Unit Kerja</td>
                <td class="pihak-sep">:</td>
                <td class="pihak-val">Satker Pelaksanaan Prasarana Strategis Provinsi Riau</td>
            </tr>
        </table>
        <div style="font-size: 7.5pt; margin-left: 18px; color: #475569; margin-bottom: 4px;">
            Bertindak untuk dan atas nama Kuasa Pengguna Barang (KPB) Satker PPS Riau, selanjutnya disebut sebagai <strong>PIHAK PERTAMA</strong>.
        </div>
    </div>

    <!-- Pihak Kedua -->
    <div class="pihak-box">
        <table class="pihak-table">
            <tr>
                <td style="width: 18px;">2.</td>
                <td class="pihak-lbl">Nama Lengkap</td>
                <td class="pihak-sep">:</td>
                <td class="pihak-val"><?= esc($loan['nama_peminjam'] ?? ''); ?></td>
            </tr>
            <tr>
                <td></td>
                <td class="pihak-lbl">NIP</td>
                <td class="pihak-sep">:</td>
                <td class="pihak-val"><?= esc($loan['nip_peminjam'] ?? '') ?: '-'; ?></td>
            </tr>
            <tr>
                <td></td>
                <td class="pihak-lbl">Jabatan</td>
                <td class="pihak-sep">:</td>
                <td class="pihak-val"><?= esc($loan['jabatan_peminjam'] ?? '') ?: '-'; ?></td>
            </tr>
            <tr>
                <td></td>
                <td class="pihak-lbl">No. Kontak / HP</td>
                <td class="pihak-sep">:</td>
                <td class="pihak-val"><?= esc($loan['kontak_peminjam'] ?? '') ?: '-'; ?></td>
            </tr>
        </table>
        <div style="font-size: 7.5pt; margin-left: 18px; color: #475569; margin-bottom: 4px;">
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
                <th style="width: 25px;">No</th>
                <th>Nama Barang / Aset</th>
                <th style="width: 90px;">Kode Barang</th>
                <th style="width: 40px;">NUP</th>
                <th style="width: 120px;">Merk / Spesifikasi</th>
                <th style="width: 45px;">Tahun</th>
                <th style="width: 70px;">Kondisi</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center;">1</td>
                <td><strong><?= esc($loan['nama_barang'] ?? ''); ?></strong></td>
                <td style="text-align: center; font-family: monospace;"><?= esc($loan['kode_barang'] ?? ''); ?></td>
                <td style="text-align: center; font-weight: bold;"><?= esc($loan['nup'] ?? ''); ?></td>
                <td><?= esc($loan['merk_tipe'] ?? '') ?: '-'; ?></td>
                <td style="text-align: center;"><?= esc($loan['tahun_perolehan'] ?? '') ?: '-'; ?></td>
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
        Barang Milik Negara sebagaimana dimaksud di atas dipinjam pakai oleh <strong>PIHAK KEDUA</strong> semata-mata untuk menunjang kelancaran pelaksanaan tugas dan fungsi kedinasan, yaitu: <em>"<?= esc($loan['keperluan'] ?? ''); ?>"</em>.
    </div>

    <div class="pasal-title">Pasal 2 &mdash; Jangka Waktu Pinjam Pakai</div>
    <div class="pasal-content">
        Peminjaman berlaku terhitung sejak tanggal <strong><?= esc($tglPinjamIndo ?? ''); ?></strong> sampai dengan 
        <strong><?= ! empty($loan['tgl_kembali_rencana']) ? date('d-m-Y', strtotime($loan['tgl_kembali_rencana'])) : 'selesainya penugasan dinas'; ?></strong>. Apabila sewaktu-waktu barang tersebut dibutuhkan oleh Satker, <strong>PIHAK KEDUA</strong> bersedia menyerahkannya kembali.
    </div>

    <div class="pasal-title">Pasal 3 &mdash; Hak dan Kewajiban Peminjam</div>
    <ol class="pasal-list">
        <li><strong>PIHAK KEDUA</strong> berkewajiban merawat, menjaga keamanan fisik, dan memelihara keutuhan barang yang dipinjam pakai secara cermat dan bertanggung jawab.</li>
        <li><strong>PIHAK KEDUA</strong> tidak dibenarkan memindahtangankan, meminjamkan kembali, menyewakan, atau mengalihkan penguasaan barang kepada pihak ketiga tanpa persetujuan tertulis <strong>PIHAK PERTAMA</strong>.</li>
        <li>Apabila terjadi kehilangan atau kerusakan akibat kelalaian peminjam, maka <strong>PIHAK KEDUA</strong> bersedia bertanggung jawab penuh memperbaiki atau mengganti barang sesuai ketentuan Peraturan BMN.</li>
        <li>Setelah masa pinjam pakai selesai, <strong>PIHAK KEDUA</strong> wajib mengembalikan barang kepada Petugas Pengurus Barang Pengguna dalam kondisi baik dan lengkap.</li>
    </ol>

    <!-- Penutup & Tanda Tangan -->
    <div class="pihak-intro" style="margin-top: 4px;">
        Demikian Surat Izin Pinjam Pakai ini dibuat dalam rangkap 2 (dua) untuk dipergunakan sebagaimana mestinya.
    </div>

    <div class="ttd-wrapper">
        <div style="text-align: right; margin-bottom: 4px; font-size: 8pt;">
            Pekanbaru, <?= esc($tglCetak ?? ''); ?>
        </div>
        <table class="ttd-table">
            <tr>
                <td>
                    <div class="ttd-jabatan">
                        Yang Menerima Pinjam Pakai<br>
                        <strong>PIHAK KEDUA</strong>
                    </div>
                    <div class="ttd-nama"><?= esc($loan['nama_peminjam'] ?? ''); ?></div>
                    <div class="ttd-nip">NIP. <?= esc($loan['nip_peminjam'] ?? '') ?: '-'; ?></div>
                </td>
                <td>
                    <div class="ttd-jabatan">
                        Kuasa Pengguna Barang<br>
                        <strong>PIHAK PERTAMA</strong>
                    </div>
                    <div class="ttd-nama"><?= esc($kasatker['nama'] ?? ''); ?></div>
                    <div class="ttd-nip">NIP. <?= esc($kasatker['nip'] ?? ''); ?></div>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding-top: 10px;">
                    <div class="ttd-jabatan" style="margin-bottom: 35px;">
                        Mengetahui / Memeriksa Penatausahaan BMN:<br>
                        <strong>Pengurus Barang Pengguna</strong>
                    </div>
                    <div class="ttd-nama"><?= esc($pengurusBarang['nama'] ?? ''); ?></div>
                    <div class="ttd-nip">NIP. <?= esc($pengurusBarang['nip'] ?? ''); ?></div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
