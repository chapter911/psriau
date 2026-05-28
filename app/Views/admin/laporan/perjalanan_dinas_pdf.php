<?php
/**
 * Template PDF Perjalanan Dinas - Mirip contoh_perjadin.docx
 *
 * Struktur:
 * - Halaman 1: Kop Surat + Form Header + Laporan Hasil (bagian 1)
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

if ($pelaksanaSignerLeft === [] && is_array($creatorPegawai)) {
    $pelaksanaSignerLeft = $creatorPegawai;
}
?>
<!DOCTYPE html>
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
    line-height: 1.5;
    color: #000;
    margin: 0;
    padding: 0;
}

/* ── UTILITY ── */
.text-center { text-align: center; }
.bold { font-weight: bold; }
.mt-1 { margin-top: 6px; }
.mb-2 { margin-bottom: 10px; }
.mb-1 { margin-bottom: 6px; }
.pt-1 { padding-top: 6px; }
.pb-1 { padding-bottom: 6px; }
.pt-05 { padding-top: 3px; }
.pb-05 { padding-bottom: 3px; }

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

/* ── LAPORAN HASIL WRAPPER ── */
.laporan-wrapper {
    border: 1px solid #000;
    border-top: none;
    margin-top: -1px; /* collapse top border with table */
}
.laporan-title-row {
    border-top: 1px solid #000;
    padding: 5px 6px;
    font-weight: bold;
}
.laporan-row {
    border-top: 1px solid #000;
    padding: 5px 6px;
    min-height: 24px;
}
.laporan-html p { margin: 0 0 3px 0; }
.laporan-html ol, .laporan-html ul { margin: 0 0 3px 0; padding-left: 20px; }
.laporan-html li { margin: 0 0 2px 0; }
.laporan-html { overflow-wrap: anywhere; word-break: break-word; }

/* ── PAGE BREAK ── */
.page-break { page-break-before: always; }

/* ── SIGNATURE PAGE ── */
.tgl-cetak { text-align: center; font-size: 14pt; margin-bottom: 18px; }
.judul-ttd { text-align: center; font-size: 17pt; font-weight: bold; margin-bottom: 16px; }

.ttd-grid {
    width: 100%;
    border-collapse: collapse;
    margin-top: 8px;
}
.ttd-grid td {
    width: 50%;
    vertical-align: top;
    text-align: center;
    padding: 0 10px;
}
.ttd-jabatan {
    font-size: 11pt;
    font-weight: bold;
    min-height: 60px;
    margin-bottom: 30px;
    line-height: 1.3;
}
.ttd-nama {
    font-size: 12pt;
    font-weight: bold;
    text-decoration: underline;
    margin-bottom: 3px;
    line-height: 1.3;
}
.ttd-nip { font-size: 11pt; }

.ttd-known { text-align: center; margin-top: 28px; }
.known-judul { font-size: 17pt; font-weight: bold; margin-bottom: 28px; }
.known-jabatan { font-size: 11pt; font-weight: bold; min-height: 55px; margin-bottom: 30px; line-height: 1.3; }
.known-nama { font-size: 12pt; font-weight: bold; text-decoration: underline; margin-bottom: 3px; }
.known-nip { font-size: 11pt; }

/* ── LAMPIRAN FOTO ── */
.lampiran-title {
    text-align: center;
    font-size: 13pt;
    font-weight: bold;
    margin-bottom: 10px;
}
.foto-grid {
    width: 100%;
    border-collapse: collapse;
}
.foto-grid td {
    width: 50%;
    border: 1px solid #000;
    padding: 6px;
    vertical-align: top;
}
.foto-grid img {
    width: 100%;
    height: 230px;
    object-fit: cover;
    display: block;
}
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

<!-- Laporan Hasil Perjalanan Dinas -->
<?php
$laporanHasilRaw = trim((string) ($data['laporan_hasil'] ?? ''));

function split_laporan_blocks($html) {
    $html = trim((string) $html);
    if ($html === '') return [];

    if (class_exists('DOMDocument')) {
        libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $wrapped = '<?xml encoding="utf-8" ?><div>' . $html . '</div>';
        $doc->loadHTML($wrapped);
        libxml_clear_errors();
        $container = $doc->getElementsByTagName('div')->item(0);
        if (!$container) return [];

        $chunks = [];
        foreach ($container->childNodes as $child) {
            $outer = trim((string) $doc->saveHTML($child));
            if ($outer !== '') $chunks[] = $outer;
        }
        if (!empty($chunks)) return $chunks;
    }

    // Fallback: split by <p> or <br>
    $parts = preg_split('/<\/?p>|<br\s*\/?\s*>/i', $html);
    $out = [];
    foreach ($parts as $p) {
        $p = trim($p);
        if ($p !== '') $out[] = '<p>' . $p . '</p>';
    }
    return $out;
}

$chunks = split_laporan_blocks($laporanHasilRaw);
$chunks = array_values($chunks);
$minRows = 16;
$maxRows = 36;
$usedRows = count($chunks);
$totalRows = max($minRows, min($maxRows, $usedRows + 6));
?>
<div class="laporan-wrapper">
    <div class="laporan-title-row">Laporan Hasil Perjalanan Dinas</div>
    <?php foreach ($chunks as $chunk): ?>
        <div class="laporan-row">
            <div class="laporan-html"><?= $chunk; ?></div>
        </div>
    <?php endforeach; ?>
    <?php for ($i = 0; $i < ($totalRows - $usedRows); $i++): ?>
        <div class="laporan-row">&nbsp;</div>
    <?php endfor; ?>
</div>

<!-- ═══════════════════ HALAMAN 2: TANDA TANGAN ═══════════════════ -->
<div class="page-break"></div>

<!-- Tanda Tangan -->
<div class="tgl-cetak">Pekanbaru, <?= esc($renderedDate); ?></div>
<div class="judul-ttd">Dibuat Oleh :</div>

<table class="ttd-grid">
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
    </tr>
</table>

<div class="ttd-known">
    <div class="known-judul">Diketahui Oleh :</div>
    <div class="known-jabatan"><?= nl2br(esc((string) ($diketahuiOleh['jabatan'] ?? '-'))); ?></div>
    <div class="known-nama"><?= esc((string) ($diketahuiOleh['nama'] ?? '-')); ?></div>
    <div class="known-nip">NIP. <?= esc((string) ($diketahuiOleh['nip'] ?? '-')); ?></div>
</div>

<!-- ═══════════════════ HALAMAN 3: LAMPIRAN FOTO ═══════════════════ -->
<div class="page-break"></div>

<div class="lampiran-title">LAMPIRAN DOKUMENTASI PELAKSANAAN PERJALANAN DINAS</div>

<?php if (!empty($photos)): ?>
    <table class="foto-grid">
        <?php foreach (array_chunk($photos, 2) as $photoRow): ?>
            <tr>
                <?php foreach ($photoRow as $photo): ?>
                    <td>
                        <img src="<?= esc((string) ($photo['data_uri'] ?? '')); ?>" alt="Dokumentasi">
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