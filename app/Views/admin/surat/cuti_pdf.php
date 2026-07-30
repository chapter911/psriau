<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Formulir Permintaan dan Pemberian Cuti - <?= esc($data['nama']); ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0.6cm 1.0cm 0.6cm 1.0cm;
        }
        body {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            font-size: 8.5pt;
            line-height: 1.15;
            color: #000000;
        }
        .header-container {
            width: 100%;
            margin-bottom: 6px;
        }
        .header-right {
            float: right;
            width: 48%;
            text-align: left;
            font-size: 8.5pt;
        }
        .clear {
            clear: both;
        }
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 9.5pt;
            margin-top: 4px;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
            page-break-inside: avoid;
        }
        th, td {
            border: 1px solid #000000;
            padding: 2px 4px;
            vertical-align: top;
        }
        .section-header {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 8.5pt;
            text-transform: uppercase;
        }
        .text-center {
            text-align: center;
        }
        .check-box {
            font-family: DejaVu Sans, Symbola, sans-serif;
            font-weight: bold;
            font-size: 9.5pt;
        }
        .notes-list {
            margin-top: 4px;
            font-size: 7pt;
            line-height: 1.1;
        }
        .signature-box {
            text-align: center;
            vertical-align: top;
            padding-top: 4px;
            height: 48px;
        }
    </style>
</head>
<body>

    <?php
        $tglPengajuan = !empty($data['tanggal_pengajuan']) ? date('d F Y', strtotime($data['tanggal_pengajuan'])) : date('d F Y');
        $months = ['January'=>'Januari','February'=>'Februari','March'=>'Maret','April'=>'April','May'=>'Mei','June'=>'Juni','July'=>'Juli','August'=>'Agustus','September'=>'September','October'=>'Oktober','November'=>'November','December'=>'Desember'];
        foreach ($months as $en => $idMonth) {
            $tglPengajuan = str_replace($en, $idMonth, $tglPengajuan);
        }
        $jenisKey = strtolower(trim((string) ($data['jenis_cuti'] ?? '')));

        // Helper to format Jabatan until the first comma
        $formatJabatan = static function (?string $j): string {
            if (empty($j)) return '';
            $parts = explode(',', $j);
            return trim($parts[0]);
        };

        $jabatanClean = $formatJabatan($data['jabatan']);
    ?>

    <div class="header-container">
        <div class="header-right">
            Pekanbaru, <?= esc($tglPengajuan); ?><br><br>
            Kepada Yth.<br>
            <strong><?= esc($data['pejabat_jabatan'] ?? 'Plt. Sekretariat Direktorat Jenderal Prasarana Strategis'); ?></strong><br>
            di Jakarta
        </div>
        <div class="clear"></div>
    </div>

    <div class="title">
        FORMULIR PERMINTAAN DAN PEMBERIAN CUTI
    </div>

    <!-- I. DATA PEGAWAI -->
    <table>
        <tr class="section-header">
            <td colspan="4">I. DATA PEGAWAI</td>
        </tr>
        <tr>
            <td width="15%">Nama</td>
            <td width="35%"><?= esc($data['nama']); ?></td>
            <td width="15%">NIP</td>
            <td width="35%"><?= esc($data['nip']); ?></td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td><?= esc($jabatanClean); ?></td>
            <td>Masa Kerja</td>
            <td><?= esc($data['masa_kerja']); ?></td>
        </tr>
        <tr>
            <td>Unit Kerja</td>
            <td colspan="3"><?= esc($data['unit_kerja']); ?></td>
        </tr>
    </table>

    <!-- II. JENIS CUTI YANG DIAMBIL -->
    <table>
        <tr class="section-header">
            <td colspan="4">II. JENIS CUTI YANG DIAMBIL**</td>
        </tr>
        <tr>
            <td width="40%">1. Cuti Tahunan</td>
            <td width="10%" class="text-center"><span class="check-box"><?= $jenisKey === 'cuti tahunan' ? '&#10003;' : ''; ?></span></td>
            <td width="40%">2. Cuti Besar</td>
            <td width="10%" class="text-center"><span class="check-box"><?= $jenisKey === 'cuti besar' ? '&#10003;' : ''; ?></span></td>
        </tr>
        <tr>
            <td>3. Cuti Sakit</td>
            <td class="text-center"><span class="check-box"><?= $jenisKey === 'cuti sakit' ? '&#10003;' : ''; ?></span></td>
            <td>4. Cuti Melahirkan</td>
            <td class="text-center"><span class="check-box"><?= $jenisKey === 'cuti melahirkan' ? '&#10003;' : ''; ?></span></td>
        </tr>
        <tr>
            <td>5. Cuti Karena Alasan Penting</td>
            <td class="text-center"><span class="check-box"><?= $jenisKey === 'cuti karena alasan penting' ? '&#10003;' : ''; ?></span></td>
            <td>6. Cuti di Luar Tanggungan Negara</td>
            <td class="text-center"><span class="check-box"><?= $jenisKey === 'cuti di luar tanggungan negara' ? '&#10003;' : ''; ?></span></td>
        </tr>
    </table>

    <!-- III. ALASAN CUTI -->
    <table>
        <tr class="section-header">
            <td>III. ALASAN CUTI</td>
        </tr>
        <tr>
            <td style="min-height: 25px; vertical-align: top;">
                <?= nl2br(esc($data['alasan_cuti'])); ?>
            </td>
        </tr>
    </table>

    <!-- IV. LAMANYA CUTI -->
    <table>
        <tr class="section-header">
            <td colspan="6">IV. LAMANYA CUTI</td>
        </tr>
        <tr>
            <td width="12%">Selama</td>
            <td width="20%"><?= (int) ($data['lama_cuti_jumlah'] ?? 1); ?> <?= esc($data['lama_cuti_satuan'] ?? 'Hari'); ?></td>
            <td width="15%">Mulai tanggal</td>
            <td width="20%"><?= !empty($data['tanggal_mulai']) ? date('d/m/Y', strtotime($data['tanggal_mulai'])) : '-'; ?></td>
            <td width="8%" class="text-center">s/d</td>
            <td width="25%"><?= !empty($data['tanggal_selesai']) ? date('d/m/Y', strtotime($data['tanggal_selesai'])) : '-'; ?></td>
        </tr>
    </table>

    <!-- V. CATATAN CUTI -->
    <?php $currentYear = (int) date('Y'); ?>
    <table>
        <tr class="section-header">
            <td colspan="5">V. CATATAN CUTI***</td>
        </tr>
        <tr>
            <td colspan="3" width="50%">1. CUTI TAHUNAN</td>
            <td width="40%">2. CUTI BESAR</td>
            <td width="10%" class="text-center"></td>
        </tr>
        <tr>
            <td width="15%">Tahun</td>
            <td width="15%">Sisa</td>
            <td width="20%">Keterangan</td>
            <td>3. CUTI SAKIT</td>
            <td class="text-center"></td>
        </tr>
        <tr>
            <td>N (<?= $currentYear; ?>)</td>
            <td><?= (int) ($data['catatan_cuti_n'] ?? 0); ?> Hari</td>
            <td><?= esc($data['catatan_cuti_keterangan'] ?? ''); ?></td>
            <td>4. CUTI MELAHIRKAN</td>
            <td class="text-center"></td>
        </tr>
        <tr>
            <td colspan="3"></td>
            <td>5. CUTI KARENA ALASAN PENTING</td>
            <td class="text-center"></td>
        </tr>
        <tr>
            <td colspan="3"></td>
            <td>6. CUTI DI LUAR TANGGUNGAN NEGARA</td>
            <td class="text-center"></td>
        </tr>
    </table>

    <!-- VI. ALAMAT SELAMA MENJALANKAN CUTI -->
    <table>
        <tr class="section-header">
            <td colspan="3">VI. ALAMAT SELAMA MENJALANKAN CUTI</td>
        </tr>
        <tr>
            <td width="50%" rowspan="2" style="vertical-align: top;">
                <?= nl2br(esc($data['alamat_selama_cuti'])); ?>
            </td>
            <td width="10%">TELP</td>
            <td width="40%"><?= esc($data['telepon']); ?></td>
        </tr>
        <tr>
            <td colspan="2" class="signature-box">
                Hormat saya,<br><br><br>
                <strong><u><?= esc($data['nama']); ?></u></strong><br>
                NIP. <?= esc($data['nip']); ?>
            </td>
        </tr>
    </table>

    <!-- VII. PERTIMBANGAN ATASAN LANGSUNG -->
    <?php $pertimbangan = strtolower(trim((string) ($data['pertimbangan_atasan'] ?? ''))); ?>
    <table>
        <tr class="section-header">
            <td colspan="4">VII. PERTIMBANGAN ATASAN LANGSUNG**</td>
        </tr>
        <tr class="text-center">
            <td width="25%">DISETUJUI</td>
            <td width="25%">PERUBAHAN****</td>
            <td width="25%">DITANGGUHKAN****</td>
            <td width="25%">TIDAK DISETUJUI****</td>
        </tr>
        <tr class="text-center" style="height: 18px;">
            <td><span class="check-box"><?= ($pertimbangan === 'disetujui' || $pertimbangan === 'setuju') ? '&#10003;' : ''; ?></span></td>
            <td><span class="check-box"><?= ($pertimbangan === 'perubahan') ? '&#10003;' : ''; ?></span></td>
            <td><span class="check-box"><?= ($pertimbangan === 'ditangguhkan') ? '&#10003;' : ''; ?></span></td>
            <td><span class="check-box"><?= ($pertimbangan === 'tidak disetujui' || $pertimbangan === 'ditolak') ? '&#10003;' : ''; ?></span></td>
        </tr>
        <tr>
            <td colspan="3"></td>
            <td class="signature-box">
                <?= esc($data['atasan_jabatan'] ?? 'Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau'); ?>,<br><br><br>
                <strong><u><?= esc($data['atasan_nama'] ?? 'Muhammad Yudi Prasetya, ST'); ?></u></strong><br>
                NIP. <?= esc($data['atasan_nip'] ?? '198002142014121002'); ?>
            </td>
        </tr>
    </table>

    <!-- VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI -->
    <?php $keputusan = strtolower(trim((string) ($data['keputusan_pejabat'] ?? ''))); ?>
    <table>
        <tr class="section-header">
            <td colspan="4">VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI**</td>
        </tr>
        <tr class="text-center">
            <td width="25%">DISETUJUI</td>
            <td width="25%">PERUBAHAN****</td>
            <td width="25%">DITANGGUHKAN****</td>
            <td width="25%">TIDAK DISETUJUI****</td>
        </tr>
        <tr class="text-center" style="height: 18px;">
            <td><span class="check-box"><?= ($keputusan === 'disetujui' || $keputusan === 'setuju') ? '&#10003;' : ''; ?></span></td>
            <td><span class="check-box"><?= ($keputusan === 'perubahan') ? '&#10003;' : ''; ?></span></td>
            <td><span class="check-box"><?= ($keputusan === 'ditangguhkan') ? '&#10003;' : ''; ?></span></td>
            <td><span class="check-box"><?= ($keputusan === 'tidak disetujui' || $keputusan === 'ditolak') ? '&#10003;' : ''; ?></span></td>
        </tr>
        <tr>
            <td colspan="3"></td>
            <td class="signature-box">
                <?= esc($data['pejabat_jabatan'] ?? 'Plt. Sekretariat Direktorat Jenderal Prasarana Strategis'); ?>,<br><br><br>
                <strong><u><?= esc($data['pejabat_nama'] ?? 'Ir. Agung Hari Prabowo, M.T'); ?></u></strong><br>
                NIP. <?= esc($data['pejabat_nip'] ?? '196910301998031005'); ?>
            </td>
        </tr>
    </table>

    <div class="notes-list">
        * Coret yang tidak perlu &nbsp;&nbsp;&nbsp; ** Pilih salah satu dengan memberi tanda centang (√)<br>
        *** Diisi oleh pejabat kepegawaian sebelum PNS menjalankan cuti &nbsp;&nbsp;&nbsp; **** Diberi tanda centang (√) dan alasannya
    </div>

</body>
</html>
