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
        <tr>
            <td>b.</td>
            <td>Nomor Bukti Laporan Pajak Tahun terakhir</td>
            <td></td>
        </tr>
    </table>

    <div class="bold">E. Data Personalia (Tenaga Ahli/Teknis)</div>
    <table class="bordered-table mb-2">
        <thead>
            <tr>
                <th width="30">No</th>
                <th>Nama</th>
                <th>Tingkat Pendidikan</th>
                <th>Jabatan dalam pekerjaan yang diusulkan</th>
                <th>Pengalaman Kerja</th>
                <th>Sertifikat Keahlian/Keterampilan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td><?= $data->nama; ?></td>
                <td><?= $data->pendidikan ?? '-'; ?></td>
                <td><?= ucwords(strtolower($data->jabatan)); ?></td>
                <td>
                    <?php if(!empty($pengalaman)){
                        foreach($pengalaman as $v){
                            $namaPerusahaan = $v->nama_perusahaan ?? '-';
                            $rentangTanggal = $v->rentang_tanggal ?? '-';
                            echo '- '. $namaPerusahaan .' ('. $rentangTanggal .')<br>';
                        }
                    } ?>
                </td>
                <td><?= $data->sertifikat ?? '-'; ?></td>
            </tr>
        </tbody>
    </table>

    <div class="mb-1">Demikian pernyataan ini kami buat dengan sebenarnya dan penuh rasa tanggung jawab.</div>

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
