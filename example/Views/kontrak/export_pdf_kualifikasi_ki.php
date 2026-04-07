<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: "Times New Roman", serif;
            font-size: 11pt;
            line-height: 1.3;
            margin: 0; /* Margin diatur oleh @page */
        }
        @page {
            margin: 2.5cm 2.5cm 2.5cm 2.5cm;
        }
        .text-center { text-align: center; }
        .text-justify { text-align: justify; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        
        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .bio-table td {
            vertical-align: top;
            padding: 2px 5px;
        }
        .bordered-table {
            width: 100%;
            border: 1px solid black;
        }
        .bordered-table th, .bordered-table td {
            border: 1px solid black;
            padding: 5px;
            vertical-align: top;
        }
        .no-border {
            border: none !important;
        }
        
        /* Helpers */
        .mb-1 { margin-bottom: 10px; }
        .mb-2 { margin-bottom: 20px; }
        .mt-1 { margin-top: 10px; }
        .mt-2 { margin-top: 20px; }
        .indent { padding-left: 20px; }
        
        .page-break { page-break-before: always; }
        
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
        dalam rangka pengadaan pekerjaan <b>Konsultan Individual  <?= ucwords(strtolower($data->jabatan)); ?></b> pada <b>Pejabat Pengadaan Barang/Jasa Pemerintah Pada Satuan Kerja Pelaksaan Prasarana Strategis Riau</b> di Pekanbaru dengan ini menyatakan bahwa:
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

    <div class="page-break"></div>

    <div class="text-center bold uppercase mb-2" style="font-size: 12pt; text-decoration: underline;">
        FORMULIR ISIAN KUALIFIKASI
    </div>

    <div class="mb-1">Saya yang bertanda tangan di bawah ini:</div>

    <table class="bio-table">
        <tr>
            <td width="180">Nama</td>
            <td width="10">:</td>
            <td><b><?= $data->nama; ?></td>
        </tr>
        <tr>
            <td>No. Identitas</td>
            <td>:</td>
            <td><b><?= $data->nik; ?></td>
        </tr>
        <tr>
            <td>Pekerjaan</td>
            <td>:</td>
            <td>Wiraswasta / Konsultan</td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td>:</td>
            <td><?= $data->alamat; ?></td>
        </tr>
        <?php if(!empty($data->nomor_telefon_ki)){ ?>
        <tr>
            <td>Telepon/fax</td>
            <td>:</td>
            <td><?= $data->nomor_telefon_ki; ?></td>
        </tr>
        <?php } ?>
        <?php if(!empty($data->email_ki)){ ?>
        <tr>
            <td>E-mail</td>
            <td>:</td>
            <td><?= $data->email_ki; ?></td>
        </tr>
        <?php } ?>
    </table>

    <div class="mb-1">Menyatakan dengan sesungguhnya bahwa:</div>
    <ol style="margin-top: 0; padding-left: 20px;" class="text-justify">
        <li>Saya secara hukum mempunyai kapasitas menandatangani kontrak;</li>
        <li>Saya bukan sebagai pegawai;</li>
        <li>Saya tidak sedang menjalani sanksi pidana;</li>
        <li>Saya tidak sedang dan tidak akan terlibat pertentangan kepentingan dengan Para pihak yang terkait, langsung maupun tidak langsung dalam proses Pengadaan ini;</li>
        <li>Saya tidak masuk dalam daftar hitam, tidak dalam pengawasan pengadilan, Tidak pailit, dan kegiatan usaha saya tidak sedang dihentikan;</li>
    </ol>

    <div class="mb-1">Data-data saya adalah sebagai berikut:</div>

    <div class="bold">A. Data Administrasi</div>
    <table class="bordered-table mb-2">
        <tr>
            <td width="30">1.</td>
            <td width="200">Nama</td>
            <td><?= $data->nama; ?></td>
        </tr>
        <tr>
            <td>2.</td>
            <td>Pekerjaan</td>
            <td>Wiraswasta / Konsultan</td>
        </tr>
        <tr>
            <td>3.</td>
            <td>Alamat Rumah</td>
            <td><?= $data->alamat; ?></td>
        </tr>
        <?php if(!empty($data->nomor_telefon_ki)){ ?>
        <tr>
            <td></td>
            <td>No. Telepon</td>
            <td>-<?= $data->nomor_telefon_ki; ?>/td>
        </tr>
        <?php } ?>
        <?php if(!empty($data->email_ki)){ ?>
        <tr>
            <td></td>
            <td>E-mail</td>
            <td><?= $data->email_ki; ?></td>
        </tr>
        <?php } ?>
        <tr>
            <td>4.</td>
            <td>Nomor Identitas (KTP/SIM/Paspor)</td>
            <td><?= $data->nik; ?></td>
        </tr>
    </table>

    <div class="bold">B. Surat Izin Usaha/melakukan kegiatan</div>
    <table class="bordered-table mb-2">
        <tr>
            <td width="30">1.</td>
            <td width="200">No. Surat Izin Usaha</td>
            <td>-</td>
        </tr>
        <tr>
            <td></td>
            <td>Tanggal</td>
            <td></td>
        </tr>
        <tr>
            <td>2.</td>
            <td>Masa berlaku izin usaha</td>
            <td>-</td>
        </tr>
        <tr>
            <td>3.</td>
            <td>Instansi pemberi izin usaha</td>
            <td>-</td>
        </tr>
    </table>

    <div class="bold">C. Izin Lainnya</div>
    <table class="bordered-table mb-2">
        <tr>
            <td width="30">1.</td>
            <td width="200">No. Surat Izin Usaha</td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td>Tanggal</td>
            <td>-</td>
        </tr>
        <tr>
            <td>2.</td>
            <td>Masa berlaku izin usaha</td>
            <td>-</td>
        </tr>
        <tr>
            <td>3.</td>
            <td>Instansi pemberi izin usaha</td>
            <td>-</td>
        </tr>
    </table>

    <div class="page-break"></div>

    <div class="bold">D. Data Keuangan Pajak</div>
    <table class="bordered-table mb-2">
        <tr>
            <td width="30">a.</td>
            <td width="200">Nomor Pokok Wajib Pajak</td>
            <td><?= $data->npwp; ?></td>
        </tr>
    </table>

    <?php if(!empty($pengalaman)){ ?>
        <div class="bold">E. Data Pengalaman</div>
        <table class="bordered-table mb-2">
            <thead>
                <tr class="text-center bold">
                    <td>No</td>
                    <td>Pengalaman</td>
                    <td>Awal</td>
                    <td>Akhir</td>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach($pengalaman as $p) { ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td><?= $p->pengalaman; ?></td>
                        <td><?= $p->tanggal_awal; ?></td>
                        <td><?= $p->tanggal_akhir; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } ?>

    <div class="page-break"></div>

    <div class="bold">F. Data Fasilitas/Peralatan/Perlengkapan yang mendukung</div>
    <table class="bordered-table mb-2">
        <thead>
            <tr class="text-center bold">
                <td>No</td>
                <td>Jenis Fasilitas/ Peralatan/ Perlengkapan</td>
                <td>Jumlah</td>
                <td>Kapasitas</td>
                <td>Merk dan Tipe</td>
                <td>Tahun pembuatan</td>
                <td>Kondisi (%)</td>
                <td>Lokasi sekarang</td>
                <td>Bukti milik</td>
            </tr>
            <tr class="text-center" style="font-size: 8pt;">
                <td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td><td>9</td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="height: 20px;">1</td>
                <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
            </tr>
        </tbody>
    </table>

    <div class="bold">G. Data Pekerjaan yang sedang dilaksanakan</div>
    <table class="bordered-table mb-2">
        <thead>
            <tr class="text-center bold">
                <td rowspan="2">No</td>
                <td rowspan="2">Nama Pekerjaan</td>
                <td rowspan="2">Bidang/ Sub Bidang Pekerjaan</td>
                <td rowspan="2">Lokasi</td>
                <td rowspan="2">Pemberi Tugas/ Pejabat Pembuat Komitmen</td>
                <td colspan="2">Kontrak</td>
                <td colspan="2">Progress Terakhir</td>
            </tr>
            <tr class="text-center bold">
                <td>No / Tanggal</td>
                <td>Nilai</td>
                <td>Kontrak (Rencana)%</td>
                <td>Prestasi Kerja(%)</td>
            </tr>
            <tr class="text-center" style="font-size: 8pt;">
                <td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td><td>9</td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="9" class="text-center bold" style="padding: 10px;">N I H I L</td>
            </tr>
        </tbody>
    </table>

    <div class="text-justify mb-2">
        Demikian pernyataan ini saya buat dengan sebenarnya dan penuh rasa tanggung jawab. Jika dikemudian hari ditemui bahwa data/dokumen yang saya sampaikan tidak benar dan ada pemalsuan, maka saya bersedia dikenakan sanksi berupa sanksi administratif, sanksi pencantuman dalam Daftar Hitam, gugatan secara perdata, dan/atau pelaporan secara pidana kepada pihak berwenang sesuai dengan ketentuan perundang-undangan.
    </div>

    <div class="signature-block">
        Pekanbaru, <?= tanggal_indonesia($data->tanggal_surat_penawaran); ?><br>
        Konsultan Individual<br>
        <?= ucwords(strtolower($data->jabatan)); ?><br>
        <br><br><br>
        <b><?= $data->nama; ?></b>
    </div>
    <div class="clear"></div>

</body>
</html>