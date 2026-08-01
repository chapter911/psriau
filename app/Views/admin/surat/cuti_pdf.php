<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Formulir Permintaan dan Pemberian Cuti - <?= esc($data['nama']); ?></title>
    <style>
        @page {
            size: A4 portrait;
            /* DOCX declared: top=1cm, LR=2cm, bottom=0.5cm
               BUT Word actually renders bottom margin as ~3.4pt (not 14pt)
               We match that behavior to fit content on 1 page. */
            margin: 1.0cm 2.0cm 0.12cm 2.0cm;
        }
        body {
            /* Calibri exact font from DOCX reference */
            font-family: Calibri, Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.0;
            color: #000;
            margin: 0;
            padding: 0;
        }

        /* ── base table style ── */
        table.t {
            width: 100%;
            border-collapse: collapse;
            font-size: 11pt;
            line-height: 1.0;
            margin-bottom: 0;
        }
        table.t td {
            /* DOCX cell margin: 108 twips L/R ≈ 1.9mm ≈ 3.8pt
               Padding top/bottom reduced to 0 to save space */
            border: 1px solid #000;
            padding: 0 4pt;
            vertical-align: top;
        }

        /* Gap between table sections. */
        .gap {
            height: 2pt;
            display: block;
            font-size: 1pt;
            line-height: 0;
        }

        /* section header (I. DATA PEGAWAI style) */
        td.hdr { font-size: 11pt; font-weight: normal; }

        .tc  { text-align: center; }
        .chk { font-family: "DejaVu Sans", sans-serif; font-size: 13pt; font-weight: bold; }

        /* signature: 3 blank lines at 11pt ≈ 33pt + text ~11pt = ~44pt total
           but reference shows ~40pt gap before name so 42pt height is good */
        .sig {
            text-align: center;
            vertical-align: top;
            padding-top: 1pt;
            padding-bottom: 1pt;
            height: 20pt;
        }

        /* check row (DISETUJUI / PERUBAHAN …) */
        .ckr { text-align: center; vertical-align: middle; height: 11pt; }

        /* footnote text: 7pt as measured from reference PDF */
        .fn { font-size: 7pt; line-height: 1.15; }
    </style>
</head>
<body>
<?php
    $months = ['January'=>'Januari','February'=>'Februari','March'=>'Maret',
               'April'=>'April','May'=>'Mei','June'=>'Juni','July'=>'Juli',
               'August'=>'Agustus','September'=>'September','October'=>'Oktober',
               'November'=>'November','December'=>'Desember'];

    $toInd = static function(?string $d) use($months): string {
        if(empty($d)) return '-';
        $t = strtotime($d);
        return $t ? str_replace(array_keys($months), array_values($months), date('j F Y', $t)) : '-';
    };

    $tglPengajuan = $toInd($data['tanggal_pengajuan'] ?? date('Y-m-d'));
    $jenisKey     = strtolower(trim((string)($data['jenis_cuti'] ?? '')));
    $jabatan      = trim(explode(',', (string)($data['jabatan'] ?? ''))[0]);

    $ck = static function(bool $v): string { return $v ? '&#10003;' : ''; };

    $pertimbangan = strtolower(trim((string)($data['pertimbangan_atasan'] ?? '')));
    $keputusan    = strtolower(trim((string)($data['keputusan_pejabat']   ?? '')));
    $currentYear  = (int)date('Y');
?>

<!-- ═══════════════════════════════════════
     ADDRESS BLOCK (right-aligned)
     DOCX indent: w:left="5954" of 9628 = 61.8%
     Line spacing: 240/20=12pt single
     ═══════════════════════════════════════ -->
<table style="width:100%;border-collapse:collapse;font-size:11pt;line-height:0.85;margin-bottom:2pt;">
    <tr>
        <td style="width:61.8%;border:none;"></td>
        <td style="width:38.2%;border:none;vertical-align:top;">
            Pekanbaru, <?= esc($tglPengajuan); ?><br>
            Kepada Yth.<br>
            <?= esc($data['pejabat_jabatan'] ?? 'Plt. Sekretariat Direktorat Jenderal Prasarana Strategis'); ?><br>
            di &nbsp; Jakarta
        </td>
    </tr>
</table>

<!-- ═══════════════════════════════════════
     TITLE  (14pt, centered, no bold)
     ═══════════════════════════════════════ -->
<div style="text-align:center;font-size:14pt;font-weight:normal;margin-bottom:4pt;line-height:1.2;">
    FORMULIR PERMINTAAN DAN PEMBERIAN CUTI
</div>

<!-- ═══════════════════════════════════════
     I. DATA PEGAWAI
     12.8% | 49.1% | 13.1% | 25.0%
     ═══════════════════════════════════════ -->
<table class="t">
    <tr><td colspan="4" class="hdr">I.&nbsp;&nbsp;DATA PEGAWAI</td></tr>
    <tr>
        <td width="12.8%">Nama</td>
        <td width="49.1%"><?= esc($data['nama']); ?></td>
        <td width="13.1%">NIP</td>
        <td width="25.0%"><?= esc($data['nip']); ?></td>
    </tr>
    <tr>
        <td>Jabatan</td>
        <td><?= esc($jabatan); ?></td>
        <td>Masa Kerja</td>
        <td><?= esc($data['masa_kerja']); ?></td>
    </tr>
    <tr>
        <td>Unit Kerja</td>
        <td colspan="3"><?= esc($data['unit_kerja']); ?></td>
    </tr>
</table>
<div class="gap"></div>

<!-- ═══════════════════════════════════════
     II. JENIS CUTI YANG DIAMBIL**
     37.5% | 13.0% | 37.9% | 11.6%
     Items: numbered list (1. … 6. …)
     ═══════════════════════════════════════ -->
<table class="t">
    <tr><td colspan="4" class="hdr">II.&nbsp;&nbsp;JENIS CUTI YANG DIAMBIL**</td></tr>
    <tr>
        <td width="37.5%">1.&nbsp; Cuti Tahunan</td>
        <td width="13.0%" class="tc"><span class="chk"><?= $ck($jenisKey==='cuti tahunan'); ?></span></td>
        <td width="37.9%">2.&nbsp; Cuti Besar</td>
        <td width="11.6%" class="tc"><span class="chk"><?= $ck($jenisKey==='cuti besar'); ?></span></td>
    </tr>
    <tr>
        <td>3.&nbsp; Cuti sakit</td>
        <td class="tc"><span class="chk"><?= $ck($jenisKey==='cuti sakit'); ?></span></td>
        <td>4.&nbsp; Cuti Melahirkan</td>
        <td class="tc"><span class="chk"><?= $ck($jenisKey==='cuti melahirkan'); ?></span></td>
    </tr>
    <tr>
        <td>5.&nbsp; Cuti Karena Alasan Penting</td>
        <td class="tc"><span class="chk"><?= $ck($jenisKey==='cuti karena alasan penting'); ?></span></td>
        <td>6.&nbsp; Cuti di Luar Tanggungan Negara</td>
        <td class="tc"><span class="chk"><?= $ck($jenisKey==='cuti di luar tanggungan negara'); ?></span></td>
    </tr>
</table>
<div class="gap"></div>

<!-- ═══════════════════════════════════════
     III. ALASAN CUTI (full width, 2 rows)
     Height: header row + content row = ~47.5pt total (=3×row_height+gap)
     ═══════════════════════════════════════ -->
<table class="t">
    <tr><td class="hdr">III.&nbsp;&nbsp;ALASAN CUTI</td></tr>
    <tr>
        <td style="height:28pt;vertical-align:top;padding:1pt 4pt;"><?= nl2br(esc($data['alasan_cuti'])); ?></td>
    </tr>
</table>
<div class="gap"></div>

<!-- ═══════════════════════════════════════
     IV. LAMANYA CUTI
     9.0% | 25.6% | 16.1% | 20.3% | 5.9% | 23.2%
     ═══════════════════════════════════════ -->
<table class="t">
    <tr><td colspan="6" class="hdr">IV.&nbsp;&nbsp;LAMANYA CUTI</td></tr>
    <tr>
        <td width="9.0%">Selama</td>
        <td width="25.6%" class="tc"><?= (int)($data['lama_cuti_jumlah']??1); ?> <?= esc($data['lama_cuti_satuan']??'Hari'); ?></td>
        <td width="16.1%">Mulai tanggal</td>
        <td width="20.3%" class="tc"><?= esc($toInd($data['tanggal_mulai'])); ?></td>
        <td width="5.9%"  class="tc">s/d</td>
        <td width="23.2%" class="tc"><?= esc($toInd($data['tanggal_selesai'])); ?></td>
    </tr>
</table>
<div class="gap"></div>

<!-- ═══════════════════════════════════════
     V. CATATAN CUTI***
     14.2% | 8.8% | 30.5% | 40.7% | 5.8%
     Last item: "6.  CUTI  DI LUAR TANGGUNGAN NEGARA"
     ═══════════════════════════════════════ -->
<table class="t">
    <tr><td colspan="5" class="hdr">V.&nbsp;&nbsp;CATATAN CUTI***</td></tr>
    <tr>
        <td colspan="3" width="53.5%">1. CUTI TAHUNAN</td>
        <td width="40.7%">2. CUTI BESAR</td>
        <td width="5.8%" class="tc"></td>
    </tr>
    <tr>
        <td width="14.2%">Tahun</td>
        <td width="8.8%">Sisa</td>
        <td width="30.5%">Keterangan</td>
        <td>3. CUTI SAKIT</td>
        <td class="tc"></td>
    </tr>
    <tr>
        <td><?= $currentYear; ?></td>
        <td class="tc"><?= (int)($data['catatan_cuti_n']??0); ?> Hari</td>
        <td><?= esc($data['catatan_cuti_keterangan']??''); ?></td>
        <td>4. CUTI MELAHIRKAN</td>
        <td class="tc"></td>
    </tr>
    <tr>
        <td></td><td></td><td></td>
        <td>5. CUTI KARENA ALASAN PENTING</td>
        <td class="tc"></td>
    </tr>
    <tr>
        <td></td><td></td><td></td>
        <td>6.&nbsp;CUTI &nbsp;DI LUAR TANGGUNGAN NEGARA</td>
        <td class="tc"></td>
    </tr>
</table>
<div class="gap"></div>

<!-- ═══════════════════════════════════════
     VI. ALAMAT SELAMA MENJALANKAN CUTI
     58.3% | 6.6% | 35.1%
     ═══════════════════════════════════════ -->
<table class="t">
    <tr><td colspan="3" class="hdr">VI.&nbsp;&nbsp;ALAMAT SELAMA MENJALANKAN CUTI</td></tr>
    <tr>
        <td width="58.3%" rowspan="2" style="vertical-align:top;padding:1pt 4pt;">
            <?= nl2br(esc($data['alamat_selama_cuti'])); ?>
        </td>
        <td width="6.6%">TELP</td>
        <td width="35.1%"><?= esc($data['telepon']); ?></td>
    </tr>
    <tr>
        <td colspan="2" class="sig" style="text-align:center;vertical-align:top;padding-top:1pt;">
            Hormat saya,
            <div style="height:25pt;"></div>
            <strong><u><?= esc($data['nama']); ?></u></strong><br>
            NIP. <?= esc($data['nip']); ?>
        </td>
    </tr>
</table>
<div class="gap"></div>

<!-- ═══════════════════════════════════════
     VII. PERTIMBANGAN ATASAN LANGSUNG**
     15.8% | 25.3% | 23.6% | 35.3%
     ═══════════════════════════════════════ -->
<table class="t">
    <tr><td colspan="4" class="hdr">VII.&nbsp;&nbsp;PERTIMBANGAN ATASAN LANGSUNG**</td></tr>
    <tr class="tc">
        <td width="15.8%">DISETUJUI</td>
        <td width="25.3%">PERUBAHAN****</td>
        <td width="23.6%">DITANGGUHKAN****</td>
        <td width="35.3%">TIDAK DISETUJUI****</td>
    </tr>
    <tr>
        <td class="ckr"><span class="chk"><?= $ck($pertimbangan==='disetujui'||$pertimbangan==='setuju'); ?></span></td>
        <td class="ckr"><span class="chk"><?= $ck($pertimbangan==='perubahan'); ?></span></td>
        <td class="ckr"><span class="chk"><?= $ck($pertimbangan==='ditangguhkan'); ?></span></td>
        <td class="ckr"><span class="chk"><?= $ck($pertimbangan==='tidak disetujui'||$pertimbangan==='ditolak'); ?></span></td>
    </tr>
    <tr>
        <td colspan="3" style="border-left:none; border-bottom:none;"></td>
        <td class="sig" style="text-align:center;vertical-align:top;padding-top:1pt;">
            <?= esc($data['atasan_jabatan']??'Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau'); ?>,
            <div style="height:25pt;"></div>
            <strong><u><?= esc($data['atasan_nama']??'Muhammad Yudi Prasetya, ST'); ?></u></strong><br>
            NIP. <?= esc($data['atasan_nip']??'198002142014121002'); ?>
        </td>
    </tr>
</table>
<div class="gap"></div>

<!-- ═══════════════════════════════════════
     VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI**
     15.3% | 26.3% | 23.1% | 35.3%
     ═══════════════════════════════════════ -->
<table class="t">
    <tr><td colspan="4" class="hdr">VIII.&nbsp;&nbsp;KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI**</td></tr>
    <tr class="tc">
        <td width="15.3%">DISETUJUI</td>
        <td width="26.3%">PERUBAHAN****</td>
        <td width="23.1%">DITANGGUHKAN****</td>
        <td width="35.3%">TIDAK DISETUJUI****</td>
    </tr>
    <tr>
        <td class="ckr"><span class="chk"><?= $ck($keputusan==='disetujui'||$keputusan==='setuju'); ?></span></td>
        <td class="ckr"><span class="chk"><?= $ck($keputusan==='perubahan'); ?></span></td>
        <td class="ckr"><span class="chk"><?= $ck($keputusan==='ditangguhkan'); ?></span></td>
        <td class="ckr"><span class="chk"><?= $ck($keputusan==='tidak disetujui'||$keputusan==='ditolak'); ?></span></td>
    </tr>
    <tr>
        <td colspan="3" style="vertical-align:top;padding:2pt 4pt; border-left:none; border-bottom:none;">
            <div class="fn">
                Catatan:<br>
                <table style="width:100%;border:none;margin:0;font-size:7pt;line-height:1.15;border-collapse:collapse;">
                    <tr><td style="width:9%;border:none;padding:0;vertical-align:top;">*</td>   <td style="border:none;padding:0;">Coret yang tidak perlu</td></tr>
                    <tr><td style="border:none;padding:0;vertical-align:top;">**</td>  <td style="border:none;padding:0;">Pilih salah satu dengan memberi tanda centang (&radic;)</td></tr>
                    <tr><td style="border:none;padding:0;vertical-align:top;">***</td> <td style="border:none;padding:0;">Diisi oleh pejabat yang menangani bidang kepegawaian sebelum PNS menjalankan cuti</td></tr>
                    <tr><td style="border:none;padding:0;vertical-align:top;">****</td><td style="border:none;padding:0;">Diberi tanda centang (&radic;) dan alasannya</td></tr>
                    <tr><td style="border:none;padding:0;vertical-align:top;">N</td>   <td style="border:none;padding:0;">Cuti Tahunan</td></tr>
                    <tr><td style="border:none;padding:0;vertical-align:top;">N-1</td> <td style="border:none;padding:0;">Sisa cuti 1 tahun sebelumnya</td></tr>
                    <tr><td style="border:none;padding:0;vertical-align:top;">N-2</td> <td style="border:none;padding:0;">Sisa cuti 2 tahun sebelumnya</td></tr>
                </table>
            </div>
        </td>
        <td class="sig" style="text-align:center;vertical-align:top;padding-top:1pt;">
            <?= esc($data['pejabat_jabatan']??'Plt. Sekretariat Direktorat Jenderal Prasarana Strategis'); ?>,
            <div style="height:20pt;"></div>
            <strong><u><?= esc($data['pejabat_nama']??'Ir. Agung Hari Prabowo, M.T'); ?></u></strong><br>
            NIP. <?= esc($data['pejabat_nip']??'196910301998031005'); ?>
        </td>
    </tr>
</table>

</body>
</html>
