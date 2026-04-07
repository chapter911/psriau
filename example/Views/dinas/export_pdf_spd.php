<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Tugas & Rincian Biaya</title>
    <style>
        @page {
            size: A4;
            margin: 1.5cm 2cm 1.5cm 2cm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            line-height: 1.3;
            color: #000;
        }

        /* === UTILITIES === */
        .text-center { text-align: center; }
        .text-justify { text-align: justify; }
        .text-bold { font-weight: bold; }
        .text-right { text-align: right; }
        .uppercase { text-transform: uppercase; }
        .underline { text-decoration: underline; }
        
        .mb-5 { margin-bottom: 5px; }
        .mb-10 { margin-bottom: 10px; }
        .mb-20 { margin-bottom: 20px; }
        .mt-10 { margin-top: 10px; }
        .mt-20 { margin-top: 20px; }
        
        .page-break { page-break-before: always; }

        /* === TABLES === */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* Tabel Bordered Standard */
        .table-bordered {
            width: 100%;
            border: 2px solid #000; /* Border luar lebih tebal */
            font-size: 9pt;
        }

        .table-bordered th, .table-bordered td {
            border: 1px solid #000;
            padding: 4px;
            vertical-align: top;
        }

        /* Pola Titik-titik untuk Header (Simulasi) */
        .pattern-bg {
            background-color: #f0f0f0; /* Fallback color */
            background-image: radial-gradient(#000 15%, transparent 16%);
            background-size: 3px 3px;
            font-weight: bold;
            text-align: center;
            vertical-align: middle !important;
        }

        /* Tabel Info (Tanpa Border) */
        .table-info td {
            padding: 2px;
            vertical-align: top;
        }

        /* === HALAMAN 1 SPECIFIC === */
        .header-title {
            font-size: 12pt;
            text-decoration: underline;
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .col-no { width: 4%; text-align: center; font-weight: bold; }
        .col-pelaksana { width: 35%; }
        .col-unit { width: 10%; text-align: center; }
        .col-periode { width: 18%; text-align: center; }
        .col-tujuan { width: 10%; text-align: center; }
        .col-transport { width: 13%; text-align: center; }
        .col-anggaran { width: 10%; text-align: center; }

        /* Signature Section Page 1 */
        .signature-box {
            float: right;
            width: 50%;
            text-align: center; /* Center align items inside the box */
            margin-top: 20px;
        }
        
        /* Table inside signature box for alignment */
        .sig-meta-table {
            width: 100%;
            font-size: 9pt;
            font-weight: bold;
            margin-bottom: 10px;
        }

        /* === HALAMAN 2 SPECIFIC === */
        .h2-title {
            font-size: 11pt;
            font-weight: bold;
            text-align: center;
        }
        
        .table-rincian th { text-align: center; }
        .col-h2-no { width: 5%; text-align: center; }
        .col-h2-uraian { width: 55%; }
        .col-h2-jumlah { width: 20%; text-align: right; }
        .col-h2-ket { width: 20%; text-align: center; }

        .total-row {
            font-weight: bold;
        }

        .signatures-grid {
            width: 100%;
            margin-top: 20px;
            font-size: 9pt;
        }
        .signatures-grid td {
            vertical-align: top;
            padding: 5px;
        }

        .spd-rampung-title {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            letter-spacing: 2px;
            margin-bottom: 10px;
            margin-top: 10px;
        }

        .border-top-thick {
            border-top: 2px solid #000;
            padding-top: 10px;
        }
        
        /* Helper for nested calculations in table */
        .calc-table {
            width: 100%;
            border: none;
        }
        .calc-table td {
            border: none;
            padding: 0;
        }

    </style>
</head>
<body>

    <div class="text-center">
        <div class="header-title">SURAT TUGAS</div>
        <div class="text-bold">NOMOR : 275 /SPT/SATKER/PPS-RIAU/Gs7/2025</div>
    </div>

    <br>

    <table class="table-info mb-10">
        <tr>
            <td style="width: 15%;">Dalam rangka</td>
            <td style="width: 2%;">:</td>
            <td style="font-style: italic;">
                Koordinasi kelengkapan Readinas Criteria (RC) Pembangunan Sekolah Rakyat Provinsi Riau Lokasi Kabupaten Kuantan Singingi
            </td>
        </tr>
    </table>

    <div class="text-justify mb-10">
        Dengan ini menugaskan pejabat/staf sebagaimana tercantum di bawah ini untuk melaksanakan tugas/ perjalanan dinas dimaksud tersebut dengan rincian sebagai berikut :
    </div>

    <table class="table-bordered">
        <thead>
            <tr>
                <th class="pattern-bg col-no">NO.</th>
                <th class="pattern-bg col-pelaksana">PELAKSANA SPPD</th>
                <th class="pattern-bg col-unit">UNIT KERJA</th>
                <th class="pattern-bg col-periode">PERIODE PERJALANAN DINAS</th>
                <th class="pattern-bg col-tujuan">TUJUAN</th>
                <th class="pattern-bg col-transport">TRANSPORTASI</th>
                <th class="pattern-bg col-anggaran">PEMBEBANAN ANGGARAN</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center text-bold">1</td>
                <td>
                    <strong>JEFRI GUSRA, S.S.T.</strong><br>
                    <span style="font-size: 8pt;">
                    NIP. 19960812 202506 1 009<br>
                    Penata Muda (III/a)<br>
                    Penata Kelola Bangunan Gedung dan Kawasan Permukiman Ahli Pertama
                    </span>
                </td>
                <td class="text-center">SATKER<br>PPS RIAU</td>
                <td class="text-center">
                    2 (dua) hari<br>
                    20 November 2025 s/d<br>
                    21 November 2025
                </td>
                <td class="text-center">Kab.<br>Kuantan Singingi</td>
                <td class="text-center">Sewa Kendaraan</td>
                <td class="text-center">SATKER PPS RIAU</td>
            </tr>
            <tr>
                <td class="text-center text-bold">2</td>
                <td>
                    <strong>SYALAM HARYADI, S.T.</strong><br>
                    <span style="font-size: 8pt;">
                    NIPPPPK. 19910416 202521 1 050<br>
                    GOL. IX<br>
                    Penata Kelola Bangunan Gedung dan Kawasan Permukiman Ahli Pertama
                    </span>
                </td>
                <td class="text-center">SATKER<br>PPS RIAU</td>
                <td class="text-center">
                    2 (dua) hari<br>
                    20 November 2025 s/d<br>
                    21 November 2025
                </td>
                <td class="text-center">Kab.<br>Kuantan Singingi</td>
                <td class="text-center">Sewa Kendaraan</td>
                <td class="text-center">SATKER PPS RIAU</td>
            </tr>
            <tr>
                <td class="text-center text-bold">2</td>
                <td>
                    <strong>NASRULLAH, S.T.</strong><br>
                    <span style="font-size: 8pt;">
                    Tenaga Penunjang Kegiatan
                    <br><br>
                    </span>
                </td>
                <td class="text-center">SATKER<br>PPS RIAU</td>
                <td class="text-center">
                    2 (dua) hari<br>
                    20 November 2025 s/d<br>
                    21 November 2025
                </td>
                <td class="text-center">Kab.<br>Kuantan Singingi</td>
                <td class="text-center">Sewa Kendaraan</td>
                <td class="text-center">SATKER PPS RIAU</td>
            </tr>
            <tr style="height: 15px;">
                <td colspan="7" class="pattern-bg" style="height: 15px; padding: 0;"></td>
            </tr>
        </tbody>
    </table>

    <div class="text-justify mt-10" style="font-size: 9pt;">
        Kepada pegawai tersebut diatas agar melaksanakan perjalanan dinas sesuai dengan Surat Tugas dan menyampaikan laporan pelaksanaan perjalanan dinas kepada Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau , selambat-lambatnya 5 (lima) hari kerja setelah perjalanan dinas.
    </div>

    <div class="mt-10" style="font-size: 9pt;">
        Demikian Surat Tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.
    </div>

    <div class="signature-box">
        <table class="sig-meta-table">
            <tr>
                <td style="text-align: left; width: 45%;">DIKELUARKAN DI</td>
                <td style="text-align: center; width: 5%;">:</td>
                <td style="text-align: left;">P E K A N B A R U</td>
            </tr>
            <tr>
                <td style="text-align: left;">PADA TANGGAL</td>
                <td style="text-align: center;">:</td>
                <td style="text-align: left;"> &nbsp;&nbsp;&nbsp;NOVEMBER 2025</td>
            </tr>
        </table>
        <div style="border-top: 1px solid #000; margin-bottom: 10px;"></div>
        
        <div style="font-weight: bold; font-size: 9pt;">
            KEPALA SATUAN KERJA<br>
            PELAKSANAAN PRASARANA STRATEGIS RIAU
        </div>
        
        <div style="height: 50px; margin-top: 5px; margin-bottom: 5px; position: relative;">
             <div style="color: blue; font-family: 'Brush Script MT', cursive; font-size: 28pt; transform: rotate(-10deg); position: absolute; left: 30%; top: 0;">fustji</div> 
        </div>

        <div style="font-weight: bold; text-decoration: underline;">MUHAMMAD YUDI PRASETYA, S.T.</div>
        <div style="font-weight: bold;">NIP. 19800214 201412 1 002</div>
    </div>
    
    <div style="clear: both;"></div>

    <div class="page-break"></div>

    <div class="h2-title">RINCIAN BIAYA PERJALANAN DINAS</div>
    <div class="text-center" style="font-size: 9pt; margin-bottom: 2px;">LAMPIRAN SPD NOMOR : 680/SPD/SATKER/PPS-RIAU/2025</div>
    <div class="text-center" style="font-size: 9pt;">TANGGAL : 25 NOVEMBER 2025</div>
    <br>

    <table class="table-bordered table-rincian">
        <thead>
            <tr>
                <th class="pattern-bg col-h2-no">No</th>
                <th class="pattern-bg col-h2-uraian">RINCIAN BIAYA</th>
                <th class="pattern-bg col-h2-jumlah">JUMLAH</th>
                <th class="pattern-bg col-h2-ket">KETERANGAN</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>
                    <strong>BIAYA TRANSPORT :</strong><br>
                    Sewa Kendaraan :<br>
                    <table class="calc-table">
                        <tr>
                            <td style="width: 10%;">1</td>
                            <td style="width: 15%;">hari</td>
                            <td style="width: 5%;">x</td>
                            <td style="width: 10%;">Rp.</td>
                            <td style="width: 25%; text-align: right;">600,000</td>
                            <td style="width: 10%; text-align: center;">Rp.</td>
                            <td style="width: 25%; text-align: right;">600,000</td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table class="calc-table">
                        <tr>
                            <td>Rp.</td>
                            <td style="text-align: right;">600,000</td>
                        </tr>
                    </table>
                </td>
                <td></td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>
                    <strong>UANG HARIAN</strong><br>
                    Uang Makan, Uang Transport Lokal, Uang Saku selama :<br>
                    <table class="calc-table">
                        <tr>
                            <td style="width: 10%;">1</td>
                            <td style="width: 15%;">hari</td>
                            <td style="width: 5%;">x</td>
                            <td style="width: 10%;">Rp.</td>
                            <td style="width: 25%; text-align: right;">370,000</td>
                            <td style="width: 10%; text-align: center;">Rp.</td>
                            <td style="width: 25%; text-align: right;">370,000</td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table class="calc-table">
                        <tr>
                            <td>Rp.</td>
                            <td style="text-align: right;">370,000</td>
                        </tr>
                    </table>
                </td>
                <td></td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td>
                    <strong>UANG PENGINAPAN</strong><br>
                    Uang penginapan selama :<br>
                    <br>
                </td>
                <td style="vertical-align: bottom;"></td>
                <td></td>
            </tr>
            <tr class="total-row">
                <td colspan="2" style="text-align: left; padding-left: 5px;">JUMLAH :</td>
                <td>
                     <table class="calc-table" style="font-weight: bold;">
                        <tr>
                            <td>Rp.</td>
                            <td style="text-align: right;">970,000</td>
                        </tr>
                    </table>
                </td>
                <td class="pattern-bg"></td>
            </tr>
            <tr>
                <td class="text-bold pattern-bg" style="text-align: left; padding-left: 5px;">TERBILANG :</td>
                <td colspan="3" class="text-bold pattern-bg" style="text-align: left;">Sembilan Ratus Tujuh Puluh Ribu Rupiah,-</td>
            </tr>
        </tbody>
    </table>

    <table class="signatures-grid">
        <tr>
            <td style="width: 50%;">
                Telah dibayar uang sebesar<br><br>
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 10%;">Rp.</td>
                        <td style="width: 90%;">970,000</td>
                    </tr>
                </table>
                <br>
                Pekanbaru, &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Desember 2025<br>
                Bendahara Pengeluaran,<br>
                <br><br><br>
                <span class="text-bold underline">KH. SRI HANDAYANI, S.Si., M.T.</span><br>
                NIP. 19820402 201412 2 002
            </td>
            <td style="width: 50%; text-align: right;">
                Pekanbaru, &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Desember 2025<br>
                <br>
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 60%;"></td>
                        <td style="width: 10%;">Rp.</td>
                        <td style="width: 30%;">970,000</td>
                    </tr>
                </table>
                <br>
                Yang Menerima :<br>
                <br><br><br>
                <span class="text-bold underline">YOANDA ADIMAS PUTRA, A.Md.</span><br>
                NIPPPPK. 19901125 202421 1 008
            </td>
        </tr>
    </table>

    <div class="border-top-thick mt-10">
        <div class="spd-rampung-title">PERHITUNGAN SPD RAMPUNG</div>
        
        <table style="width: 100%; font-size: 9pt;">
            <tr>
                <td style="width: 50%;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="width: 60%;">Ditetapkan Sejumlah..................................................</td>
                            <td style="width: 10%;">Rp</td>
                            <td style="width: 30%; text-align: right;">970,000</td>
                        </tr>
                        <tr>
                            <td>Yang dibayar semula .................................................</td>
                            <td>Rp</td>
                            <td style="text-align: right;">-</td>
                        </tr>
                        <tr>
                            <td style="border-bottom: 1px solid #000;">Sisa kurang / Lebih .......................................................</td>
                            <td style="border-bottom: 1px solid #000;">Rp</td>
                            <td style="text-align: right; border-bottom: 1px solid #000;">-</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 50%;">
                    <div style="text-align: center; margin-left: 20px;">
                        <br>
                        Pejabat Pembuat Komitmen<br>
                        Pelaksanaan Prasarana Strategis<br>
                        <br><br><br><br>
                        <span class="text-bold underline">NURHIDAYAT NUGROHO, S.Ars.</span><br>
                        NIP. 19901221 201802 1 001
                    </div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>