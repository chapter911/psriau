<?php
/**
 * Template PDF Perjalanan Dinas - Mirip contoh_perjadin.docx
 *
 * Struktur:
 * - Halaman 1: Kop Surat + Form Header + Laporan Hasil (1 kotak besar)
 * - Halaman 2: Tanda Tangan
 * - Halaman 3: Lampiran Dokumentasi Foto
 */
helper('custom');

$data = $data ?? [];
$periodeMulai = tanggal_indonesia((string) ($data['periode_mulai'] ?? ''));
$periodeSelesai = tanggal_indonesia((string) ($data['periode_selesai'] ?? ''));
$renderedDate = tanggal_indonesia((string) ($data['periode_mulai'] ?? date('Y-m-d')));
$pelaksana = $data['pelaksana'] ?? [];
$photos = $data['foto_dokumentasi'] ?? [];
$creatorPegawai = $data['creator_pegawai'] ?? [];
$diketahuiOleh = $data['diketahui_oleh'] ?? [];

$pelaksanaSignerLeft  = $pelaksana[0] ?? [];
$pelaksanaSignerRight = $pelaksana[1] ?? [];
$pelaksanaSignerExtra = $pelaksana[2] ?? [];

if ($pelaksanaSignerLeft === [] && is_array($creatorPegawai)) {
    $pelaksanaSignerLeft = $creatorPegawai;
}

// Tentukan layout tanda tangan:
// 3 executor → 3 kolom tanda tangan
// <3 executor → 2 kolom (kiri + kanan kosong), Diketahui di bawah
$isThreeExe = !empty($pelaksanaSignerExtra) && !empty(trim((string) ($pelaksanaSignerExtra['nama'] ?? '')));

$resolvePhotoSrc = static function ($photo): string {
    $candidates = [];

    if (is_array($photo)) {
        foreach (['data_uri', 'src', 'url', 'path', 'file_path'] as $key) {
            $value = trim((string) ($photo[$key] ?? ''));
            if ($value !== '') {
                $candidates[] = $value;
            }
        }
    } elseif (is_string($photo)) {
        $value = trim($photo);
        if ($value !== '') {
            $candidates[] = $value;
        }
    }

    foreach ($candidates as $candidate) {
        if (preg_match('#^(data:|https?://|//)#i', $candidate)) {
            return $candidate;
        }

        $pathCandidates = [$candidate];
        $trimmedCandidate = ltrim($candidate, '/\\');
        $pathCandidates[] = $trimmedCandidate;

        foreach (['FCPATH', 'WRITEPATH', 'ROOTPATH'] as $rootConstant) {
            if (defined($rootConstant)) {
                $pathCandidates[] = rtrim(constant($rootConstant), '/\\') . DIRECTORY_SEPARATOR . $trimmedCandidate;
            }
        }

        foreach (array_values(array_unique($pathCandidates)) as $pathCandidate) {
            if (! is_file($pathCandidate)) {
                continue;
            }

            $binary = @file_get_contents($pathCandidate);
            if ($binary === false) {
                continue;
            }

            $mimeType = function_exists('mime_content_type') ? (string) @mime_content_type($pathCandidate) : '';
            if ($mimeType === '' && function_exists('finfo_open')) {
                $finfo = @finfo_open(FILEINFO_MIME_TYPE);
                if ($finfo !== false) {
                    $mimeType = (string) @finfo_file($finfo, $pathCandidate);
                    @finfo_close($finfo);
                }
            }
            if ($mimeType === '') {
                $mimeType = 'image/jpeg';
            }

            return 'data:' . $mimeType . ';base64,' . base64_encode($binary);
        }
    }

    return '';
};
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
@page { size: A4; margin: 1.3cm 1.5cm 1.5cm 1.5cm; }

body {
    font-family: "Times New Roman", Times, serif;
    font-size: 11pt;
    line-height: 1.5;
    color: #000;
    margin: 0;
    padding: 0;
}

/* ── UTILITY ── */
.text-center { text-align: center; }
.bold { font-weight: bold; }

/* ── KOP SURAT ── */
.kop-surat { width: 100%; }

/* ── TITLE ── */
.judul {
    font-size: 13pt;
    font-weight: bold;
    text-align: center;
    margin: 10px 0 12px 0;
}

/* ── FORM TABLE ── */
.form-table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #000;
}
.form-table td {
    border: 1px solid #000;
    padding: 5px 6px;
    vertical-align: top;
    line-height: 1.45;
}
.form-table .col-label { width: 42%; }
.form-table .col-colon { width: 3%; text-align: center; }
.form-table .col-value { width: 55%; }

/* ── PELAKSANA ITEMS ── */
.pelaksana-block { margin-bottom: 5px; }
.pelaksana-block:last-child { margin-bottom: 0; }
.pelaksana-no { display: inline-block; width: 16px; }
.pelaksana-line { display: flex; }
.pelaksana-key { display: inline-block; width: 52px; flex-shrink: 0; }

/* ── LAPORAN HASIL: 1 KOTAK BESAR (NO ROW DIVIDERS) ── */
.lh-box {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #000;
    margin-top: -1px;
    page-break-inside: auto;
}
.lh-box td {
    padding: 3px 5px;
    vertical-align: top;
}
.lh-title {
    font-weight: bold;
    margin-bottom: 0;
    line-height: 1.2;
}
.lh-full { padding: 0; margin-top: 2px; }
.lh-full > :first-child { margin-top: 0; }
.lh-full > :last-child { margin-bottom: 0; }
.lh-full p { margin: 0 0 4px 0; }
.lh-full ol, .lh-full ul { margin: 0 0 4px 0; padding-left: 20px; }
.lh-full li { margin: 0 0 3px 0; }
.lh-full { overflow-wrap: anywhere; word-break: break-word; }

/* ── PAGE BREAK ── */
.page-break { page-break-before: always; }

/* ── SIGNATURE PAGE ── */
.tgl-cetak { text-align: center; font-size: 14pt; margin-bottom: 18px; }
.judul-ttd { text-align: center; font-size: 17pt; font-weight: bold; margin-bottom: 16px; }

/* 3-kolom: executor 1 | executor 2 | executor 3 */
.ttd-grid-3 { width: 100%; border-collapse: collapse; margin-top: 8px; }
.ttd-grid-3 td { width: 33.33%; vertical-align: top; text-align: center; padding: 0 6px; }

.ttd-grid-3 .ttd-known-cell {
    padding-top: 28px;
    text-align: left;
}

/* 2-kolom: executor 1 | executor 2 (extra spacer) */
.ttd-grid-2 { width: 100%; border-collapse: collapse; margin-top: 8px; }
.ttd-grid-2 td { width: 50%; vertical-align: top; text-align: center; padding: 0 10px; }
.ttd-grid-2 .ttd-placeholder { padding: 0; }

/* 2-kolom outer (untuk ketahui + placeholder) */
.ttd-known-wrap { width: 100%; border-collapse: collapse; margin-top: 12px; }
.ttd-known-wrap td { width: 50%; vertical-align: top; text-align: center; padding: 0 10px; }
.ttd-known-wrap .known-cell { width: 50%; }

/* Signature signer blocks */
.ttd-jabatan { font-size: 11pt; font-weight: bold; min-height: 55px; margin-bottom: 28px; line-height: 1.3; }
.ttd-nama { font-size: 12pt; font-weight: bold; text-decoration: underline; margin-bottom: 3px; }
.ttd-nip { font-size: 11pt; }

/* Diketahui block */
.known-judul { font-size: 17pt; font-weight: bold; margin-bottom: 26px; }
.known-jabatan { font-size: 11pt; font-weight: bold; min-height: 50px; margin-bottom: 26px; line-height: 1.3; }
.known-nama { font-size: 12pt; font-weight: bold; text-decoration: underline; margin-bottom: 3px; }
.known-nip { font-size: 11pt; }

/* ── LAMPIRAN FOTO ── */
.lampiran-title { text-align: center; font-size: 13pt; font-weight: bold; margin-bottom: 10px; }
.foto-grid { width: 100%; border-collapse: collapse; }
.foto-grid td { width: 50%; border: 1px solid #000; padding: 6px; vertical-align: top; }
.foto-grid img { width: 100%; height: 230px; object-fit: cover; display: block; }
</style>
</head>
<body>

<!-- ═══════════════════ HALAMAN 1: KOP + FORM + LAPORAN ═══════════════════ -->

<!-- Kop Surat -->
<div class="text-center">
    <?= kop_surat_img_tag('', 'width:100%; max-height:110px; object-fit:contain;', 'Kop Surat'); ?>
</div>

<!-- Judul -->
<div class="judul">LAPORAN PELAKSANAAN PERJALANAN DINAS</div>

<!-- Form Header Table -->
<table class="form-table">
    <tr>
        <td class="col-label">Nomor Surat Tugas</td>
        <td class="col-colon">:</td>
        <td class="col-value"><?= esc((string) ($data['nomor_surat_tugas'] ?? '-')); ?></td>
    </tr>
    <tr>
        <td class="col-label">Periode Perjalanan Dinas</td>
        <td class="col-colon">:</td>
        <td class="col-value"><?= esc(trim($periodeMulai . ' s.d ' . $periodeSelesai)); ?></td>
    </tr>
    <tr>
        <td class="col-label">Kota/Kab. Tujuan Perjalanan Dinas</td>
        <td class="col-colon">:</td>
        <td class="col-value"><?= esc((string) ($data['kota_tujuan'] ?? '-')); ?></td>
    </tr>
    <tr>
        <td class="col-label">Pelaksana Perjalanan Dinas</td>
        <td class="col-colon">:</td>
        <td class="col-value">
            <?php if (!empty($pelaksana)): ?>
                <?php foreach ($pelaksana as $idx => $p): ?>
                    <div class="pelaksana-block">
                        <div class="pelaksana-line">
                            <span class="pelaksana-no"><?= (int) $idx + 1; ?>.</span>
                            <span class="pelaksana-key">Nama</span>: <strong><?= esc((string) ($p['nama'] ?? '-')); ?></strong>
                        </div>
                        <div class="pelaksana-line">
                            <span class="pelaksana-no"></span>
                            <span class="pelaksana-key">Jabatan</span>: <?= esc((string) ($p['jabatan'] ?? '-')); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                -
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <td class="col-label">Tujuan Perjalanan Dinas</td>
        <td class="col-colon">:</td>
        <td class="col-value"><?= nl2br(esc((string) ($data['tujuan'] ?? '-'))); ?></td>
    </tr>
    <tr>
        <td class="col-label">Sasaran Perjalanan Dinas</td>
        <td class="col-colon">:</td>
        <td class="col-value"><?= nl2br(esc((string) ($data['sasaran'] ?? '-'))); ?></td>
    </tr>
</table>

<!-- Laporan Hasil Perjalanan Dinas — 1 KOTAK BESAR -->
<?php
    $laporanHasilRaw = trim((string) ($data['laporan_hasil'] ?? ''));
    $laporanHasilRaw = preg_replace('/^(?:\s*<p>(?:<br\s*\/?|&nbsp;)<\/p>)+/i', '', $laporanHasilRaw) ?? $laporanHasilRaw;
    $laporanHasilRaw = preg_replace('/(?:\s*<p>(?:<br\s*\/?|&nbsp;)<\/p>)+\s*$/i', '', $laporanHasilRaw) ?? $laporanHasilRaw;
?>
<table class="lh-box">
    <tr>
        <td>
            <div class="lh-title">Laporan Hasil Perjalanan Dinas</div>
            <?php if ($laporanHasilRaw !== ''): ?>
                <div class="lh-full"><?= $laporanHasilRaw; ?></div>
            <?php else: ?>
                <div class="lh-full">&nbsp;</div>
            <?php endif; ?>
        </td>
    </tr>
</table>

<!-- ═══════════════════ HALAMAN 2: TANDA TANGAN ═══════════════════ -->
<div class="page-break"></div>

<div class="tgl-cetak">Pekanbaru, <?= esc($renderedDate); ?></div>
<div class="judul-ttd">Dibuat Oleh :</div>

<?php if ($isThreeExe): ?>
    <!-- 3 executor → 3 kolom simetris -->
    <table class="ttd-grid-3">
        <tr>
            <td>
                <div class="ttd-jabatan"><?= nl2br(esc((string) ($pelaksanaSignerLeft['jabatan'] ?? '-'))); ?></div>
                <div class="ttd-nama"><?= esc((string) ($pelaksanaSignerLeft['nama'] ?? '-')); ?></div>
                <div class="ttd-nip">NIP. <?= esc((string) ($pelaksanaSignerLeft['nip'] ?? '-')); ?></div>
            </td>
            <td>
                <div class="ttd-jabatan"><?= nl2br(esc((string) ($pelaksanaSignerRight['jabatan'] ?? '-'))); ?></div>
                <div class="ttd-nama"><?= esc((string) ($pelaksanaSignerRight['nama'] ?? '-')); ?></div>
                <div class="ttd-nip">NIP. <?= esc((string) ($pelaksanaSignerRight['nip'] ?? '-')); ?></div>
            </td>
            <td>
                <div class="ttd-jabatan"><?= nl2br(esc((string) ($pelaksanaSignerExtra['jabatan'] ?? '-'))); ?></div>
                <div class="ttd-nama"><?= esc((string) ($pelaksanaSignerExtra['nama'] ?? '-')); ?></div>
                <div class="ttd-nip">NIP. <?= esc((string) ($pelaksanaSignerExtra['nip'] ?? '-')); ?></div>
            </td>
        </tr>
        <tr>
            <td colspan="3" class="ttd-known-cell">
                <div class="known-judul">Diketahui Oleh :</div>
                <div style="max-width: 320px;">
                    <div class="known-jabatan"><?= nl2br(esc((string) ($diketahuiOleh['jabatan'] ?? '-'))); ?></div>
                    <div class="known-nama"><?= esc((string) ($diketahuiOleh['nama'] ?? '-')); ?></div>
                    <div class="known-nip">NIP. <?= esc((string) ($diketahuiOleh['nip'] ?? '-')); ?></div>
                </div>
            </td>
        </tr>
    </table>
<?php else: ?>
    <!-- ≤2 executor → executor 1 | executor 2, Diketahui di bawah kiri -->
    <table class="ttd-known-wrap">
        <tr>
            <td>
                <div class="ttd-jabatan"><?= nl2br(esc((string) ($pelaksanaSignerLeft['jabatan'] ?? '-'))); ?></div>
                <div class="ttd-nama"><?= esc((string) ($pelaksanaSignerLeft['nama'] ?? '-')); ?></div>
                <div class="ttd-nip">NIP. <?= esc((string) ($pelaksanaSignerLeft['nip'] ?? '-')); ?></div>
            </td>
            <td class="ttd-placeholder">
                <div class="ttd-jabatan"><?= nl2br(esc((string) ($pelaksanaSignerRight['jabatan'] ?? '-'))); ?></div>
                <div class="ttd-nama"><?= esc((string) ($pelaksanaSignerRight['nama'] ?? '-')); ?></div>
                <div class="ttd-nip">NIP. <?= esc((string) ($pelaksanaSignerRight['nip'] ?? '-')); ?></div>
            </td>
        </tr>
    </table>

    <!-- Diketahui Oleh di bawah kiri -->
    <div style="margin-top: 28px;">
        <div class="known-judul">Diketahui Oleh :</div>
        <div style="text-align: left; max-width: 300px;">
            <div class="known-jabatan"><?= nl2br(esc((string) ($diketahuiOleh['jabatan'] ?? '-'))); ?></div>
            <div class="known-nama"><?= esc((string) ($diketahuiOleh['nama'] ?? '-')); ?></div>
            <div class="known-nip">NIP. <?= esc((string) ($diketahuiOleh['nip'] ?? '-')); ?></div>
        </div>
    </div>
<?php endif; ?>

<!-- ═══════════════════ HALAMAN 3: LAMPIRAN FOTO ═══════════════════ -->
<div class="page-break"></div>

<div class="lampiran-title">LAMPIRAN DOKUMENTASI PELAKSANAAN PERJALANAN DINAS</div>

<?php if (!empty($photos)): ?>
    <table class="foto-grid">
        <?php foreach (array_chunk($photos, 2) as $photoRow): ?>
            <tr>
                <?php foreach ($photoRow as $photo): ?>
                    <td>
                        <?php $photoSrc = $resolvePhotoSrc($photo); ?>
                        <?php if ($photoSrc !== ''): ?>
                            <img src="<?= esc($photoSrc); ?>" alt="Dokumentasi">
                        <?php else: ?>
                            <div style="height: 230px;"></div>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
                <?php if (count($photoRow) === 1): ?>
                    <td></td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <table class="foto-grid">
        <tr>
            <td style="height: 240px;"></td>
            <td></td>
        </tr>
    </table>
<?php endif; ?>

</body>
</html>
