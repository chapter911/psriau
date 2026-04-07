<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SPMK - Surat Perintah Mulai Kerja</title>
    <style>
        /* --- PENGATURAN HALAMAN --- */
        @page {
            /* Margin Atas: 2.5cm (Cukup untuk halaman 2 dst).
               Pada Halaman 1, gambar Header akan otomatis mengisi area
               di bawah margin ini.
            */
            margin: 1cm 2.5cm 3cm 2.5cm; 
            size: A4;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            line-height: 1.3;
            color: #000;
        }

        /* --- HEADER (GAMBAR KOP SURAT) --- */
        .header-img-container {
            width: 100%;
            text-align: center;
            margin-bottom: 20px;
            /* PENTING: Jangan gunakan position: fixed.
               Biarkan statis agar hanya muncul di awal dokumen (Halaman 1).
            */
        }

        .header-img {
            width: 100%;      /* Lebar mengikuti kertas */
            height: auto;     /* Tinggi proporsional */
            max-height: 150px; /* Batasi tinggi agar tidak terlalu besar */
            object-fit: contain;
        }

        /* --- JUDUL DOKUMEN --- */
        .doc-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
            margin-top: 10px; 
        }

        .doc-title h2 {
            margin: 0;
            font-size: 12pt;
            text-decoration: none;
            text-transform: uppercase;
        }

        .doc-number {
            text-align: center;
            margin-bottom: 20px;
        }

        /* --- ISI KONTEN --- */
        .content {
            text-align: justify;
        }

        /* Tabel Biodata */
        .info-table {
            width: 100%;
            margin-top: 5px;
            margin-bottom: 10px;
        }

        .info-table td {
            vertical-align: top;
            padding-bottom: 3px;
        }

        .label-col {
            width: 140px;
        }

        .sep-col {
            width: 20px;
            text-align: center;
        }

        /* Teks Hukum */
        .legal-text {
            text-align: justify;
            margin-bottom: 10px;
            line-height: 1.4;
        }

        /* Tabel Poin 1-5 */
        .list-table {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
        }
        
        .list-table td {
            vertical-align: top;
            padding-bottom: 8px;
        }

        .number-col {
            width: 25px;
        }

        /* --- TANDA TANGAN UTAMA (Halaman Terakhir) --- */
        .signature-section {
            margin-top: 40px;
            width: 100%;
            page-break-inside: avoid; 
        }

        .signature-table {
            width: 100%;
            text-align: center;
            border: none;
        }

        .signature-table td {
            vertical-align: top;
            padding: 0 5px;
            width: 50%;
        }

        .sig-title {
            font-weight: normal;
            margin-bottom: 70px;
            line-height: 1.2;
        }

        .sig-name {
            font-weight: bold;
            text-decoration: underline;
        }

        .sig-nip {
            margin-top: 2px;
        }

        /* --- FOOTER PARAF (KOTAK KECIL - REPEATING) --- */
        #footer-paraf {
            position: fixed;
            bottom: -2.5cm; /* Posisi di margin bawah */
            right: 0px;
            width: 180px;
            z-index: 1;
        }

        .table-paraf {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            font-family: Arial, Helvetica, sans-serif;
        }

        .table-paraf th {
            background-color: #f5f5f5;
            color: #666;
            font-weight: bold;
            border: 1px solid #999;
            padding: 5px;
            text-align: center;
        }

        .table-paraf td {
            border: 1px solid #999;
            height: 40px;
            background-color: #fff;
        }

        /* --- MASKING (PENUTUP) HALAMAN TERAKHIR --- */
        .last-page-mask {
            position: absolute;
            bottom: -3cm;
            left: 0;
            width: 100%;
            height: 4cm;
            background-color: #ffffff;
            z-index: 10;
        }

        /* Helper */
        .bold { font-weight: bold; }
    </style>
</head>
<body>

    <div class="header-img-container">
        <img src="<?= base_url() ?>public/img/logo_kop_jl_bakti.png" class="header-img" alt="Kop Surat">
    </div>

    <div id="footer-paraf">
        <table class="table-paraf">
            <tr>
                <th>Penyedia</th>
                <th>PPK</th>
            </tr>
            <tr>
                <td></td>
                <td></td>
            </tr>
        </table>
    </div>

    <div class="doc-title">
        <h2>SURAT PERINTAH MULAI KERJA</h2>
        <h2>(SPMK)</h2>
    </div>

    <div class="doc-number">
        Untuk Melaksanakan<br>
        Paket Pekerjaan Konsultan Individual <?= ucwords(strtolower($data->jabatan));?><br>
        Nomor: <?= $data->nomor_kontrak; ?>
    </div>

    <div class="content">
        <p>Yang bertanda tangan di bawah ini:</p>

        <table class="info-table">
            <tr>
                <td class="label-col">Nama</td>
                <td class="sep-col">:</td>
                <td><?= $data->pejabat_ppk; ?></td>
            </tr>
            <tr>
                <td class="label-col">NIP</td>
                <td class="sep-col">:</td>
                <td><?= $data->nip_pejabat_ppk; ?></td>
            </tr>
            <tr>
                <td class="label-col">Jabatan</td>
                <td class="sep-col">:</td>
                <td>Pejabat Pembuat Komitmen Prasarana Strategis</td>
            </tr>
            <tr>
                <td class="label-col">Berkedudukan di</td>
                <td class="sep-col">:</td>
                <td><?= $data->kedudukan_pejabat_ppk; ?></td>
            </tr>
        </table>

        <div class="legal-text">
            Yang bertindak untuk dan atas nama*) Pemerintah Indonesia c.q. Kementerian Pekerjaan Umum c.q. Direktorat Jenderal Prasarana Strategis c.q. Satuan Kerja Pelaksanaan Prasarana Strategis Riau berdasarkan Surat Keputusan Menteri Pekerjaan Umum Nomor: <?= $data->nomor_surat_keputusan_menteri; ?> Tanggal <?= tanggal_indonesia($data->tanggal_surat_keputusan_menteri); ?> Tentang Perubahan Atas Keputusan Menteri Pekerjaaan Umum Nomor: <?= $data->nomor_perubahan_keputusan_menteri; ?> Tentang Pengangkatan Atasan/Atasan Langsung/Pembantu Atasan Langsung Kuasa Pengguna Anggaran/Barang dan Pejabat Perbendaharaan Satuan Kerja di Lingkungan Direktorat Jenderal Prasarana Strategis, Kementerian Pekerjaan Umum<br>
            selanjutnya disebut <span class="bold">Pejabat Penandatanganan Kontrak;</span>
        </div>

        <div class="legal-text">
            Berdasarkan SPK Nomor: <?= $data->nomor_kontrak; ?> Tanggal <?= tanggal_indonesia($data->tanggal_kontrak); ?>, Bersama ini memerintahkan:
        </div>

        <table class="info-table">
            <tr>
                <td class="label-col">Nama</td>
                <td class="sep-col">:</td>
                <td><?= $data->nama; ?></td>
            </tr>
            <tr>
                <td class="label-col">Jabatan</td>
                <td class="sep-col">:</td>
                <td><i><?= ucwords(strtolower($data->jabatan)); ?></i></td>
            </tr>
            <tr>
                <td class="label-col">Berkedudukan di</td>
                <td class="sep-col">:</td>
                <td>Pekanbaru</td>
            </tr>
        </table>

        <p>
            Yang dalam hal ini diwakili oleh: -<br>
            Selanjutnya disebut dengan <span class="bold">Penyedia Jasa</span>
        </p>

        <p>Untuk segera memulai pelaksanaan pekerjaan dengan memperhatikan ketentuan-ketentuan sebagai berikut:</p>

        <table class="list-table">
            <tr>
                <td class="number-col">1.</td>
                <td class="label-col" style="width: 130px;">Paket Pengadaan</td>
                <td class="sep-col">:</td>
                <td>Konsultan Individual <?= ucwords(strtolower($data->jabatan)); ?></td>
            </tr>
            <tr>
                <td class="number-col">2.</td>
                <td class="label-col">Tanggal Mulai Kerja</td>
                <td class="sep-col">:</td>
                <td><?= tanggal_indonesia($data->tanggal_awal); ?></td>
            </tr>
            <tr>
                <td class="number-col">3.</td>
                <td colspan="3">Syarat-syarat Pekerjaan sesuai dengan persyaratan dan ketentuan SPK;</td>
            </tr>
            <tr>
                <td class="number-col">4.</td>
                <td colspan="3">Waktu Penyelesaian selama (<?= $data->durasi_pelaksanaan; ?>) hari kalender dan pekerjaan harus sudah selesai pada tanggal <?= tanggal_indonesia($data->tanggal_akhir); ?>;</td>
            </tr>
            <tr>
                <td class="number-col">5.</td>
                <td class="label-col">Denda</td>
                <td class="sep-col">:</td>
                <td style="text-align: justify;">
                    Terhadap setiap hari keterlambatan pelaksanaan/penyelesaian pekerjaan Penyedia Jasa akan dikenakan Denda Keterlambatan sebesar 1/1000 (satu permil) dari nilai SPK atau dari nilai bagian SPK (tidak termasuk PPN) sesuai ketentuan dalam SPK
                </td>
            </tr>
        </table>
    </div>

    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td>
                    <div class="sig-title">
                        Untuk dan Atas Nama<br>
                        Pejabat Penandatanganan Kontrak<br>
                        Prasarana Strategis<br>
                        Satuan Kerja Pelaksanaan<br>
                        Prasarana Strategis Riau
                    </div>
                    <div class="sig-name"><?= $data->pejabat_ppk; ?></div>
                    <div class="sig-nip">NIP. <?= $data->nip_pejabat_ppk; ?></div>
                </td>
                <td>
                    <div class="sig-title">
                        Untuk dan Atas Nama Penyedia Jasa<br>
                        Konsultan Individual<br>
                        <?= ucwords(strtolower($data->jabatan));?><br>
                        <br><br>
                    </div>
                    <div class="sig-name"><?= $data->nama; ?></div>
                    <div class="sig-nip"><?= ucwords(strtolower($data->jabatan));?></div>
                </td>
            </tr>
        </table>
    </div>

    <div class="last-page-mask"></div>

</body>
</html>