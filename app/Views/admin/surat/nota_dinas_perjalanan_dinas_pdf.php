<?php
helper('custom');
helper('pdf');

$pelaksana = json_decode((string) ($data['pelaksana_json'] ?? '[]'), true);
if (! is_array($pelaksana)) {
    $pelaksana = [];
}

// Format NIP helper (19740704 200701 1 002)
$formatNip = static function (string $nip): string {
    $nip = preg_replace('/\s+/', '', trim($nip));
    if (strlen($nip) === 18) {
        return substr($nip, 0, 8) . ' ' . substr($nip, 8, 6) . ' ' . substr($nip, 14, 1) . ' ' . substr($nip, 15);
    }
    return $nip;
};

// Format Golongan helper: "III/a (Penata Muda)" -> "Penata Muda (III/a)"
$formatPangkatGolongan = static function (string $golongan): string {
    $golongan = trim($golongan);
    if ($golongan === '' || $golongan === '-') {
        return '';
    }
    if (preg_match('/^([IVX]+\/[a-e])\s*\((.+)\)$/i', $golongan, $m)) {
        return trim($m[2]) . ' (' . trim($m[1]) . ')';
    }
    return $golongan;
};

// Format Periode Perjalanan helper per row
$formatPeriode = static function (string $mulaiRaw, string $selesaiRaw): string {
    $mulaiRaw   = trim($mulaiRaw);
    $selesaiRaw = trim($selesaiRaw);
    if ($mulaiRaw !== '' && $selesaiRaw !== '') {
        if ($mulaiRaw === $selesaiRaw) {
            return function_exists('tanggal_indonesia') ? tanggal_indonesia($mulaiRaw) : date('d-m-Y', strtotime($mulaiRaw));
        }
        $m = function_exists('tanggal_indonesia') ? tanggal_indonesia($mulaiRaw) : date('d-m-Y', strtotime($mulaiRaw));
        $s = function_exists('tanggal_indonesia') ? tanggal_indonesia($selesaiRaw) : date('d-m-Y', strtotime($selesaiRaw));
        return $m . ' s/d ' . $s;
    }
    if ($mulaiRaw !== '') {
        return function_exists('tanggal_indonesia') ? tanggal_indonesia($mulaiRaw) : date('d-m-Y', strtotime($mulaiRaw));
    }
    return '';
};

$fallbackMulai   = trim((string) ($data['periode_mulai'] ?? ''));
$fallbackSelesai = trim((string) ($data['periode_selesai'] ?? ''));
$fallbackTujuan  = trim((string) ($data['tujuan'] ?? ''));

// Format Tanggal Nota Dinas
$tglNotaRaw  = trim((string) ($data['tanggal_nota_dinas'] ?? ''));
$tglNotaText = '';
if ($tglNotaRaw !== '') {
    $tglNotaText = function_exists('tanggal_indonesia') ? tanggal_indonesia($tglNotaRaw) : date('d-m-Y', strtotime($tglNotaRaw));
}

$nomorNota = trim((string) ($data['nomor_nota_dinas'] ?? ''));
$kepadaYth = trim((string) ($data['kepada_yth'] ?? '')) ?: 'Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau';
$dari      = trim((string) ($data['dari'] ?? '')) ?: 'Pejabat Pembuat Komitmen Pelaksanaan Prasarana Strategis Riau';
$hal       = trim((string) ($data['hal'] ?? '')) ?: 'Permohonan Penerbitan Surat Tugas Pelaksanaan Kegiatan';
$isiNota   = trim((string) ($data['isi_nota_dinas'] ?? ''));
$ppkNama   = trim((string) ($ppk['nama'] ?? '')) ?: 'Nurhidayat Nugroho, S.Ars.';
$ppkNip    = $formatNip((string) ($ppk['nip'] ?? ''));
$showPpkNip = function_exists('should_show_nip') ? should_show_nip($ppk) : ($ppkNip !== '' && $ppkNip !== '-');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nota Dinas Perjalanan Dinas</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.2cm 1.8cm 1.5cm 1.8cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            line-height: 1.45;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .kop-container {
            text-align: center;
            margin-bottom: 14px;
        }
        .title-block {
            text-align: center;
            margin-bottom: 16px;
        }
        .title-block .title {
            font-size: 12pt;
            font-weight: bold;
            margin: 0;
            padding: 0;
        }
        .title-block .nomor {
            font-size: 12pt;
            font-weight: normal;
            margin: 2px 0 0 0;
            padding: 0;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 11pt;
        }
        .meta-table td {
            padding: 2px 0;
            vertical-align: top;
        }
        .meta-table td.label-col {
            width: 14%;
            font-size: 11.5pt;
        }
        .meta-table td.colon-col {
            width: 3%;
            text-align: center;
        }
        .meta-table td.val-col {
            width: 83%;
        }
        .meta-divider td {
            border-bottom: 1px solid #000;
            padding-top: 6px;
        }
        .paragraph-justify {
            text-align: justify;
            margin: 0 0 14px 0;
            font-size: 11pt;
            line-height: 1.5;
        }
        .pegawai-table-wrapper {
            width: 100%;
            margin: 12px 0 18px 0;
        }
        .pegawai-table {
            width: 86%;
            margin-left: 14%;
            border-collapse: collapse;
            font-size: 11pt;
        }
        .pegawai-table th,
        .pegawai-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: middle;
        }
        .pegawai-table th {
            font-weight: normal;
            text-align: center;
        }
        .signature-table {
            width: 100%;
            margin-top: 26px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }
        .signature-table td {
            vertical-align: top;
            font-size: 11.5pt;
        }
    </style>
</head>
<body>

    <!-- Kop Surat -->
    <div class="kop-container">
        <?php if (function_exists('kop_surat_img_tag') && kop_surat_img_tag('', '', 'Kop Surat') !== ''): ?>
            <?= kop_surat_img_tag('', 'width: 100%; max-height: 115px; object-fit: contain;', 'Kop Surat'); ?>
        <?php else: ?>
            <div style="font-size: 14pt; font-weight: bold; text-transform: uppercase;">
                KEMENTERIAN PEKERJAAN UMUM
            </div>
            <div style="font-size: 12pt; font-weight: bold;">
                Satuan Kerja Pelaksanaan Prasarana Strategis Riau
            </div>
            <div style="border-bottom: 3px double #000; margin: 5px 0 15px;"></div>
        <?php endif; ?>
    </div>

    <!-- Judul & Nomor -->
    <div class="title-block">
        <div class="title">NOTA DINAS</div>
        <div class="nomor">
            <?php if ($nomorNota !== ''): ?>
                NOMOR : <?= esc($nomorNota); ?>
            <?php else: ?>
                NOMOR : <span style="color: #ffffff;">272/SPT/Gs7/20262222222</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Metadata Nota Dinas -->
    <table class="meta-table">
        <tr>
            <td class="label-col">Yth.</td>
            <td class="colon-col">:</td>
            <td class="val-col"><?= esc($kepadaYth); ?></td>
        </tr>
        <tr>
            <td class="label-col">Dari</td>
            <td class="colon-col">:</td>
            <td class="val-col"><?= esc($dari); ?></td>
        </tr>
        <tr>
            <td class="label-col">Hal</td>
            <td class="colon-col">:</td>
            <td class="val-col"><?= esc($hal); ?></td>
        </tr>
        <tr>
            <td class="label-col">Tanggal</td>
            <td class="colon-col">:</td>
            <td class="val-col"><?= esc($tglNotaText); ?></td>
        </tr>
        <tr class="meta-divider">
            <td colspan="3"></td>
        </tr>
    </table>

    <!-- Paragraf Pembuka (Manual) -->
    <p class="paragraph-justify">
        <?= nl2br(esc($isiNota)); ?>
    </p>

    <!-- Tabel Pegawai Diusulkan -->
    <div class="pegawai-table-wrapper">
        <table class="pegawai-table">
            <thead>
                <tr>
                    <th style="width: 44%;">Nama</th>
                    <th style="width: 32%;">Periode Perjalanan</th>
                    <th style="width: 24%;">Tujuan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalRows = count($pelaksana);
                if ($totalRows > 0):
                    foreach ($pelaksana as $p):
                        $pNama    = trim((string) ($p['nama'] ?? ''));
                        $pNip     = trim((string) ($p['nip'] ?? ''));
                        $pGol     = $formatPangkatGolongan((string) ($p['golongan'] ?? ''));
                        $showNip  = function_exists('should_show_nip') ? should_show_nip($p) : ($pNip !== '' && $pNip !== '-');
                        $pMulai   = ! empty($p['periode_mulai']) ? (string) $p['periode_mulai'] : $fallbackMulai;
                        $pSelesai = ! empty($p['periode_selesai']) ? (string) $p['periode_selesai'] : $fallbackSelesai;
                        $pPeriode = $formatPeriode($pMulai, $pSelesai);
                        $pTujuan  = isset($p['tujuan']) && trim((string) $p['tujuan']) !== '' ? trim((string) $p['tujuan']) : $fallbackTujuan;
                ?>
                    <tr>
                        <td style="text-align: left; vertical-align: top; line-height: 1.35;">
                            <div><?= esc($pNama); ?></div>
                            <?php if ($showNip && $pNip !== ''): ?>
                                <div>NIP. <?= esc($formatNip($pNip)); ?></div>
                            <?php endif; ?>
                            <?php if ($pGol !== ''): ?>
                                <div><?= esc($pGol); ?></div>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            <?= esc($pPeriode); ?>
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            <?= nl2br(esc($pTujuan)); ?>
                        </td>
                    </tr>
                <?php
                    endforeach;
                else:
                ?>
                    <tr>
                        <td style="height: 45px;">&nbsp;</td>
                        <td style="text-align: center; vertical-align: middle;"><?= esc($formatPeriode($fallbackMulai, $fallbackSelesai)); ?></td>
                        <td style="text-align: center; vertical-align: middle;"><?= nl2br(esc($fallbackTujuan)); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Paragraf Penutup -->
    <p class="paragraph-justify">
        Sehubungan dengan hal tersebut di atas, mohon kiranya Bapak berkenan untuk menerbitkan Surat Tugas (dan SPPD) bagi pegawai yang bersangkutan.
    </p>

    <p class="paragraph-justify">
        Demikian permohonan ini kami sampaikan, atas perhatian dan perkenan Bapak/Ibu diucapkan terima kasih.
    </p>

    <!-- Tanda Tangan -->
    <table class="signature-table">
        <tr>
            <td style="width: 45%;"></td>
            <td style="width: 55%; text-align: center;">
                <div>Pejabat Pembuat Komitmen</div>
                <div>Pelaksanaan Prasarana Strategis Riau</div>
                <div style="height: 68px;"></div>
                <div><?= esc($ppkNama); ?></div>
                <?php if ($showPpkNip && $ppkNip !== ''): ?>
                    <div>NIP. <?= esc($ppkNip); ?></div>
                <?php endif; ?>
            </td>
        </tr>
    </table>

</body>
</html>
