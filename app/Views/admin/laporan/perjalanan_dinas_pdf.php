<?php
helper('custom');
helper('pdf_helper');

$data = $data ?? [];
$periodeMulai = tanggal_indonesia((string) ($data['periode_mulai'] ?? ''));
$periodeSelesai = tanggal_indonesia((string) ($data['periode_selesai'] ?? ''));
$renderedDate = tanggal_indonesia((string) ($data['periode_mulai'] ?? date('Y-m-d')));
$pelaksana = $data['pelaksana'] ?? [];
$photos = $data['foto_dokumentasi'] ?? [];
$creatorPegawai = $data['creator_pegawai'] ?? [];
$diketahuiOleh = $data['diketahui_oleh'] ?? [];

// ========== DIKETAHUI OLEH SECTION ==========
$diketahuiList = [];
if (!empty($diketahuiOleh)) {
    if (is_array($diketahuiOleh) && isset($diketahuiOleh[0])) {
        $diketahuiList = $diketahuiOleh;
    } elseif (is_array($diketahuiOleh) && isset($diketahuiOleh['nama'])) {
        $diketahuiList = [$diketahuiOleh];
    }
}

$diketahuiCount = count($diketahuiList);
$diketahuiPerRow = $diketahuiCount >= 4 ? 2 : $diketahuiCount;
$diketahuiRows = $diketahuiCount >= 4 ? 2 : 1;

$getJabatan = function($jabatan): string {
    if (empty($jabatan)) return '';
    $parts = explode(',', $jabatan, 2);
    return trim($parts[0]);
};

$getSignatureJabatan = function($person): string {
    $jabatan = $person['jabatan'] ?? '';
    if (empty($jabatan)) return '';
    
    $nip = trim((string) ($person['nip'] ?? ''));
    if ($nip === '198002142014121002') {
        return "Kepala Satuan Kerja,<br/>Pelaksanaan Prasarana Strategis Riau";
    }
    
    $parts = explode(',', $jabatan);
    $parts = array_map('trim', $parts);
    
    if (preg_match('/^(kepala|satker)/i', $parts[0]) && isset($parts[1]) && !empty($parts[1])) {
        $title = str_replace('Satker Mandiri', 'Satuan Kerja', $parts[0]);
        return $title . ',<br/>' . $parts[1];
    }
    
    return $parts[0] . ',';
};

$executorsSignList = [];
if (!empty($pelaksana)) {
    $executorsSignList = $pelaksana;
} elseif (!empty($creatorPegawai)) {
    if (is_array($creatorPegawai) && isset($creatorPegawai['nama']) && !empty(trim((string) $creatorPegawai['nama']))) {
        $executorsSignList = [$creatorPegawai];
    }
}

$resolvePhotoSrc = static function ($photo): string {
    $candidates = [];
    if (is_array($photo)) {
        // Format baru: file_path berupa URL relatif
        $filePath = trim((string) ($photo['file_path'] ?? ''));
        if ($filePath !== '') {
            // Convert URL path ke file path absolut untuk dibaca sebagai base64
            $safePath = ltrim($filePath, '/');
            $absPath = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $safePath);
            if (is_file($absPath)) {
                $bin = @file_get_contents($absPath);
                if ($bin !== false && $bin !== '') {
                    $mime = function_exists('mime_content_type') ? (@mime_content_type($absPath) ?: 'image/jpeg') : 'image/jpeg';
                    return 'data:' . $mime . ';base64,' . base64_encode($bin);
                }
            }
        }
        // Fallback ke data_uri untuk data lama
        foreach (['data_uri', 'src', 'url', 'path'] as $key) {
            $value = trim((string) ($photo[$key] ?? ''));
            if ($value !== '') $candidates[] = $value;
        }
    } elseif (is_string($photo)) {
        $value = trim($photo);
        if ($value !== '') $candidates[] = $value;
    }

    foreach ($candidates as $candidate) {
        if (preg_match('#^(data:|https?://|//)#i', $candidate)) return $candidate;
        $trimmed = ltrim($candidate, '/\\');
        $paths = [$candidate, $trimmed];
        foreach (['FCPATH','WRITEPATH','ROOTPATH'] as $c) {
            if (defined($c)) $paths[] = rtrim(constant($c), '/\\') . DIRECTORY_SEPARATOR . $trimmed;
        }
        foreach (array_unique($paths) as $p) {
            if (!is_file($p)) continue;
            $bin = @file_get_contents($p);
            if ($bin === false) continue;
            $mime = function_exists('mime_content_type') ? @mime_content_type($p) : '';
            if ($mime === '' && function_exists('finfo_open')) {
                $f = @finfo_open(FILEINFO_MIME_TYPE);
                if ($f !== false) {
                    $mime = (string) @finfo_file($f, $p);
                    @finfo_close($f);
                }
            }
            if ($mime === '') $mime = 'image/jpeg';
            return 'data:' . $mime . ';base64,' . base64_encode($bin);
        }
    }
    return '';
};
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <style>
    @page {
        size: A4;
        margin: 1.3cm 1.5cm 1.5cm 1.5cm;
    }

    body {
        font-family: "Times New Roman", Times, serif;
        font-size: 11pt;
        color: #000;
    }

    body:before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        border: 1px solid #000;
        z-index: 9999;
        pointer-events: none;
    }

    .center {
        text-align: center;
    }

    .bold {
        font-weight: 700;
    }

    .main-table {
        width: 100%;
        border-collapse: collapse;
    }

    .main-table td {
        border: 1px solid #000;
        padding: 6px;
        vertical-align: top;
    }

    .label {
        width: 40%;
    }

    .colon {
        width: 3%;
        text-align: center;
    }

    .value {
        width: 57%;
    }

    .pelaksana-block {
        margin-bottom: 4px;
    }

    .pelaksana-line {
        display: flex;
    }

    .pelaksana-no {
        display: inline-block;
        width: 18px;
    }

    .pelaksana-key {
        display: inline-block;
        width: 60px;
        flex-shrink: 0;
    }

    /* KOTAK BESAR: tanpa border internal */
    .report-wrapper {
        margin-top: 4px;
    }

    .report-title {
        padding: 6px 8px 4px;
        font-weight: 700;
    }

    .report-content {
        padding: 6px 8px;
    }

    .report-title+.report-content {
        padding-top: 8px;
    }

    /* reduce block margins inside content */
    .report-content p,
    .report-content h1,
    .report-content h2,
    .report-content h3,
    .report-content h4,
    .report-content ul,
    .report-content ol {
        margin: 0 0 5px 0;
        padding: 0;
    }

    .report-content p:last-child,
    .report-content ul:last-child,
    .report-content ol:last-child {
        margin-bottom: 0;
    }

    .page-break {
        page-break-before: always;
    }

    .avoid-break {
        page-break-inside: avoid;
        break-inside: avoid;
    }


    .ttd-grid-3 {
        width: 100%;
        border-collapse: collapse;
        margin-top: 12px;
    }

    .ttd-grid-3 td {
        width: 33.33%;
        text-align: center;
        vertical-align: top;
        padding: 0 6px;
    }

    .known-judul {
        font-size: 14pt;
        font-weight: 700;
        margin-bottom: 12px;
    }

    .foto-grid {
        width: 100%;
        border-collapse: collapse;
        margin-top: 6px;
    }

    .foto-grid td {
        width: 50%;
        height: 250px;
        border: 1px solid #000;
        padding: 6px;
        vertical-align: middle;
        text-align: center;
    }

    .foto-grid img {
        max-width: 100%;
        max-height: 180px;
        display: block;
        margin: 0 auto;
    }
    </style>
</head>

<body>
    <div class="center">
        <?= kop_surat_img_tag('', 'width:100%; max-height:120px; object-fit:contain;', 'Kop Surat'); ?>
        <h2 class="bold">LAPORAN PELAKSANAAN PERJALANAN DINAS</h2>
    </div>

    <table class="main-table">
        <tr>
            <td class="label">Nomor Surat Tugas</td>
            <td class="colon">:</td>
            <td class="value"><?= esc((string) ($data['nomor_surat_tugas'] ?? '-')); ?></td>
        </tr>
        <tr>
            <td class="label">Periode Perjalanan Dinas</td>
            <td class="colon">:</td>
            <td class="value"><?= esc($periodeMulai . ' s.d ' . $periodeSelesai); ?></td>
        </tr>
        <tr>
            <td class="label">Kota/Kab. Tujuan Perjalanan Dinas</td>
            <td class="colon">:</td>
            <td class="value"><?= esc((string) ($data['kota_tujuan'] ?? '-')); ?></td>
        </tr>
        <tr>
            <td class="label">Pelaksana Perjalanan Dinas</td>
            <td class="colon">:</td>
            <td class="value">
                <?php if (!empty($pelaksana)): ?>
                <table style="width:100%; border-collapse:collapse; border:none; margin:0; padding:0;">
                    <?php foreach ($pelaksana as $idx => $p): ?>
                    <tr style="border:none;">
                        <td style="width:18px; border:none; padding:2px 0; vertical-align:top; font-size:11pt;"><?= (int) $idx + 1; ?>.</td>
                        <td style="width:50px; border:none; padding:2px 0; vertical-align:top; font-size:11pt;">Nama</td>
                        <td style="width:10px; border:none; padding:2px 0; vertical-align:top; font-size:11pt;">:</td>
                        <td style="border:none; padding:2px 0; vertical-align:top; font-size:11pt;"><strong><?= esc((string) ($p['nama'] ?? '-')); ?></strong></td>
                    </tr>
                    <tr style="border:none;">
                        <td style="border:none; padding:2px 0; vertical-align:top; font-size:11pt;"></td>
                        <td style="border:none; padding:2px 0; vertical-align:top; font-size:11pt;">Jabatan</td>
                        <td style="border:none; padding:2px 0; vertical-align:top; font-size:11pt;">:</td>
                        <td style="border:none; padding:2px 0; vertical-align:top; font-size:11pt;"><?= esc($getJabatan($p['jabatan'] ?? '')); ?></td>
                    </tr>
                    <?php if ($idx < count($pelaksana) - 1): ?>
                    <tr style="border:none;"><td colspan="4" style="height:6px; border:none; padding:0;"></td></tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </table>
                <?php else: ?>-
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td class="label">Tujuan Perjalanan Dinas</td>
            <td class="colon">:</td>
            <td class="value"><?= nl2br(esc((string) ($data['tujuan'] ?? '-'))); ?></td>
        </tr>
        <tr>
            <td class="label">Sasaran Perjalanan Dinas</td>
            <td class="colon">:</td>
            <td class="value"><?= nl2br(esc((string) ($data['sasaran'] ?? '-'))); ?></td>
        </tr>
    </table>

    <?php
    $laporan = trim((string) ($data['laporan_hasil'] ?? ''));
    // sanitize possible page-break styles and office markup
    $laporan = preg_replace('#<!--(.*?)-->#s', '', $laporan);
    $laporan = preg_replace('#<(?:meta|link|style)[^>]*>#i', '', $laporan);
    $laporan = preg_replace('#(<[^>]+)style=["\"][^"\"]*page-break-[^;:\\"]*:[^;"\"]*;?[^"\"]*["\"]#i', '$1', $laporan);
    $blocks = pdf_split_blocks($laporan);
  ?>

    <div class="report-wrapper">
        <div class="report-title">Laporan Hasil Perjalanan Dinas</div>
        <?php if (!empty($blocks)): ?>
        <?php foreach ($blocks as $b): ?>
        <div class="report-content"><?= $b; ?></div>
        <?php endforeach; ?>
        <?php else: ?>
        <div class="report-content">&nbsp;</div>
        <?php endif; ?>
    </div>    <div class="avoid-break" style="margin-top:16px;">
        <div style="text-align:center;">Pekanbaru, <?= esc($renderedDate); ?><br/>Dibuat Oleh :</div>

        <?php if (!empty($executorsSignList)): ?>
        <?php
        $executorsCount = count($executorsSignList);
        $colWidth = 100 / $executorsCount;
        ?>
        <table style="width:100%; border-collapse:collapse; margin-top:12px;">
            <tr>
                <?php foreach ($executorsSignList as $person): ?>
                <td style="width:<?= $colWidth ?>%; text-align:center; vertical-align:top; padding:0 6px; border:none;">
                    <div class="bold"><?= $getSignatureJabatan($person); ?></div>
                </td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <?php foreach ($executorsSignList as $person): ?>
                <td style="width:<?= $colWidth ?>%; text-align:center; vertical-align:top; padding:0 6px; border:none;">
                    <div style="height:60px;"></div>
                </td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <?php foreach ($executorsSignList as $person): ?>
                <?php 
                    $nipVal = trim((string) ($person['nip'] ?? ''));
                    $nipLabel = 'NIP. ' . $nipVal;
                    if ($nipVal !== '') {
                        if (preg_match('/^(nip|nipppk|ni\s*pppk)/i', $nipVal)) {
                            $nipLabel = $nipVal;
                        }
                    } else {
                        $nipLabel = 'NIP. -';
                    }
                ?>
                <td style="width:<?= $colWidth ?>%; text-align:center; vertical-align:top; padding:0 6px; border:none;">
                    <div class="bold" style="text-decoration:underline;"><?= esc((string) ($person['nama'] ?? '-')); ?></div>
                    <div><?= esc($nipLabel); ?></div>
                </td>
                <?php endforeach; ?>
            </tr>
        </table>
        <?php endif; ?>

        <!-- DIKETAHUI OLEH SECTION -->
        <?php if (!empty($diketahuiList)): ?>
        <div style="margin-top:20px;">
            <div class="center bold known-judul">Diketahui Oleh :</div>
            <?php
            $idx = 0;
            for ($row = 0; $row < $diketahuiRows; $row++):
                $itemsInThisRow = ($row === 0) ? $diketahuiPerRow : ($diketahuiCount - $diketahuiPerRow);
                $colWidth = $itemsInThisRow > 0 ? (100 / $itemsInThisRow) : 100;
                $rowPersons = array_slice($diketahuiList, $idx, $itemsInThisRow);
                $idx += $itemsInThisRow;
            ?>
            <table style="width:100%; border-collapse:collapse; margin-top:14px;">
                <tr>
                    <?php foreach ($rowPersons as $person): ?>
                    <td style="width:<?= $colWidth ?>%; text-align:center; vertical-align:top; padding:0 6px; border:none;">
                        <div class="bold"><?= $getSignatureJabatan($person); ?></div>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <?php foreach ($rowPersons as $person): ?>
                    <td style="width:<?= $colWidth ?>%; text-align:center; vertical-align:top; padding:0 6px; border:none;">
                        <div style="height:60px;"></div>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <?php foreach ($rowPersons as $person): ?>
                    <?php 
                        $nipVal = trim((string) ($person['nip'] ?? ''));
                        $nipLabel = 'NIP. ' . $nipVal;
                        if ($nipVal !== '') {
                            if (preg_match('/^(nip|nipppk|ni\s*pppk)/i', $nipVal)) {
                                $nipLabel = $nipVal;
                            }
                        } else {
                            $nipLabel = 'NIP. -';
                        }
                    ?>
                    <td style="width:<?= $colWidth ?>%; text-align:center; vertical-align:top; padding:0 6px; border:none;">
                        <div class="bold" style="text-decoration:underline;"><?= esc((string) ($person['nama'] ?? '-')); ?></div>
                        <div><?= esc($nipLabel); ?></div>
                    </td>
                    <?php endforeach; ?>
                </tr>
            </table>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="page-break"></div>

    <div style="text-align:center; font-weight:700; margin-bottom:8px;">LAMPIRAN DOKUMENTASI PELAKSANAAN PERJALANAN
        DINAS</div>

    <?php if (!empty($photos)): ?>
    <table class="foto-grid">
        <?php foreach (array_chunk($photos, 2) as $row): ?>
        <tr>
            <?php foreach ($row as $photo): ?>
            <td>
                <?php $src = $resolvePhotoSrc($photo); ?>
                <?php if ($src !== ''): ?>
                <img src="<?= esc($src); ?>" alt="Dokumentasi">
                <?php else: ?>
                <div style="height:180px"></div>
                <?php endif; ?>
                <?php 
                    $keterangan = '';
                    if (is_array($photo) && isset($photo['keterangan'])) {
                        $keterangan = trim((string) $photo['keterangan']);
                    }
                ?>
                <?php if ($keterangan !== ''): ?>
                <div style="margin-top: 6px; font-size: 10pt; font-weight: bold;"><?= esc($keterangan); ?></div>
                <?php endif; ?>
            </td>
            <?php endforeach; ?>
            <?php if (count($row) === 1): ?><td></td><?php endif; ?>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php else: ?>
    <table class="foto-grid">
        <tr>
            <td style="height:240px"></td>
            <td></td>
        </tr>
    </table>
    <?php endif; ?>

</body>

</html>