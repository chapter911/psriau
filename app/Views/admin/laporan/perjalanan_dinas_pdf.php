<?php helper('custom'); ?>
<?php
    $data = $data ?? [];
    $periodeMulai = tanggal_indonesia((string) ($data['periode_mulai'] ?? ''));
    $periodeSelesai = tanggal_indonesia((string) ($data['periode_selesai'] ?? ''));
    $renderedDate = tanggal_indonesia((string) ($data['periode_mulai'] ?? date('Y-m-d')));
    $pelaksana = $data['pelaksana'] ?? [];
    $photos = $data['foto_dokumentasi'] ?? [];
    $creatorPegawai = $data['creator_pegawai'] ?? [];
    $diketahuiOleh = $data['diketahui_oleh'] ?? [];

    $pelaksanaSignerLeft = $pelaksana[0] ?? [];
    $pelaksanaSignerRight = $pelaksana[1] ?? [];

    if ($pelaksanaSignerLeft === [] && is_array($creatorPegawai)) {
        $pelaksanaSignerLeft = $creatorPegawai;
    }
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        @page { margin: 1.3cm 1.5cm 1.5cm 1.5cm; }
        body { font-family: "Times New Roman", serif; font-size: 11pt; line-height: 1.25; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .table { width: 100%; border-collapse: collapse; }
        .table td, .table th { padding: 4px 6px; vertical-align: top; }
        .bordered td, .bordered th { border: 1px solid #000; }
        .title { font-size: 12.8pt; }
        .main-sheet { table-layout: fixed; page-break-inside: auto; }
        .main-sheet tr { page-break-inside: auto; page-break-after: auto; }
        .main-sheet td { border: 1px solid #000; page-break-inside: auto; }
        .main-sheet .label-col { width: 41%; }
        .main-sheet .colon-col { width: 3%; text-align: center; }
        .main-sheet .value-col { width: 56%; }
        .report-cell { padding: 0; }
        .report-title { padding: 4px 6px 0 6px; font-size: 11pt; }
        .report-content { padding: 2px 6px 4px 6px; }
        .pelaksana-item { margin-bottom: 5px; }
        .pelaksana-item:last-child { margin-bottom: 0; }
        .pelaksana-no { display: inline-block; width: 18px; }
        .pelaksana-meta-label { display: inline-block; width: 60px; }
        .laporan-section { margin-top: 4px; }
        .laporan-section-title { font-size: 11pt; margin-bottom: 2px; }
        .laporan-html p { margin: 0 0 4px 0; }
        .laporan-html ol, .laporan-html ul { margin: 0 0 4px 20px; }
        .laporan-html li { margin: 0 0 3px 0; }
        .laporan-html { overflow-wrap: anywhere; word-break: break-word; }
        .page-break { page-break-before: always; }

        .signature-sheet { width: 100%; margin-top: 6px; }
        .sign-date { text-align: center; font-size: 14pt; margin-bottom: 20px; }
        .sign-title { text-align: center; font-size: 17pt; margin-bottom: 18px; }
        .sign-grid { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .sign-grid td { width: 50%; vertical-align: top; text-align: center; padding: 0 12px; }
        .sign-jabatan { font-size: 18pt; font-weight: 700; line-height: 1.2; min-height: 92px; margin-bottom: 36px; }
        .sign-name { font-size: 21pt; font-weight: 700; text-decoration: underline; line-height: 1.2; }
        .sign-nip { font-size: 16pt; line-height: 1.2; }
        .sign-known { text-align: center; margin-top: 34px; }
        .known-title { font-size: 17pt; margin-bottom: 30px; }
        .known-jabatan { font-size: 18pt; font-weight: 700; line-height: 1.2; min-height: 120px; }
        .known-name { font-size: 21pt; font-weight: 700; text-decoration: underline; line-height: 1.2; }
        .known-nip { font-size: 16pt; line-height: 1.2; }

        .lampiran-title { text-align: center; font-size: 13pt; font-weight: 700; margin-bottom: 12px; }
        .photo-grid { width: 100%; border-collapse: collapse; }
        .photo-grid td { width: 50%; border: 1px solid #000; padding: 8px; vertical-align: top; }
        .photo-grid img { width: 100%; height: 220px; object-fit: cover; display: block; }
    </style>
</head>
<body>
    <div class="text-center mb-2">
        <?= kop_surat_img_tag('', 'width: 100%; max-height: 110px; object-fit: contain;', 'Kop Surat'); ?>
    </div>
    <div class="text-center bold title mb-2">LAPORAN PELAKSANAAN PERJALANAN DINAS</div>

    <table class="table bordered main-sheet">
        <tr>
            <td class="label-col">Nomor Surat Tugas</td>
            <td class="colon-col">:</td>
            <td class="value-col"><?= esc((string) ($data['nomor_surat_tugas'] ?? '-')); ?></td>
        </tr>
        <tr>
            <td class="label-col">Periode Perjalanan Dinas</td>
            <td class="colon-col">:</td>
            <td class="value-col"><?= esc(trim($periodeMulai . ' s.d ' . $periodeSelesai)); ?></td>
        </tr>
        <tr>
            <td class="label-col">Kota/Kab. Tujuan Perjalanan Dinas</td>
            <td class="colon-col">:</td>
            <td class="value-col"><?= esc((string) ($data['kota_tujuan'] ?? '-')); ?></td>
        </tr>
        <tr>
            <td class="label-col">Pelaksana Perjalanan Dinas</td>
            <td class="colon-col">:</td>
            <td class="value-col">
                <?php if ($pelaksana !== []): ?>
                    <?php foreach ($pelaksana as $index => $row): ?>
                        <div class="pelaksana-item">
                            <div><span class="pelaksana-no"><?= (int) $index + 1; ?>.</span><span class="pelaksana-meta-label">Nama</span>: <strong><?= esc((string) ($row['nama'] ?? '-')); ?></strong></div>
                            <div><span class="pelaksana-no"></span><span class="pelaksana-meta-label">Jabatan</span>: <?= esc((string) ($row['jabatan'] ?? '-')); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td class="label-col">Tujuan Perjalanan Dinas</td>
            <td class="colon-col">:</td>
            <td class="value-col"><?= nl2br(esc((string) ($data['tujuan'] ?? '-'))); ?></td>
        </tr>
        <tr>
            <td class="label-col">Sasaran Perjalanan Dinas</td>
            <td class="colon-col">:</td>
            <td class="value-col"><?= nl2br(esc((string) ($data['sasaran'] ?? '-'))); ?></td>
        </tr>
        <?php
            $laporanHasilRaw = trim((string) ($data['laporan_hasil'] ?? ''));

            function split_html_into_blocks($html)
            {
                $html = trim((string) $html);
                if ($html === '') {
                    return [];
                }

                if (class_exists('DOMDocument')) {
                    libxml_use_internal_errors(true);
                    $doc = new DOMDocument();
                    // wrap to ensure a single container
                    $wrapped = '<?xml encoding="utf-8" ?><div>' . $html . '</div>';
                    $doc->loadHTML($wrapped);
                    libxml_clear_errors();
                    $container = $doc->getElementsByTagName('div')->item(0);
                    $chunks = [];
                    if ($container) {
                        foreach ($container->childNodes as $child) {
                            $outer = $doc->saveHTML($child);
                            $outer = trim((string) $outer);
                            if ($outer !== '') {
                                $chunks[] = $outer;
                            }
                        }
                    }
                    if (!empty($chunks)) {
                        return $chunks;
                    }
                }

                // Fallback: split by closing paragraphs or line breaks
                $parts = preg_split('/<\/?p>|<br\s*\/?\s*>/i', $html);
                $out = [];
                foreach ($parts as $p) {
                    $p = trim($p);
                    if ($p !== '') {
                        $out[] = '<p>' . $p . '</p>';
                    }
                }
                return $out;
            }

            $chunks = split_html_into_blocks($laporanHasilRaw);
            if (empty($chunks)) :
        ?>
            <tr>
                <td class="report-cell" colspan="3">
                    <div class="report-title bold">Laporan Hasil Perjalanan Dinas</div>
                    <div class="report-content">-</div>
                </td>
            </tr>
        <?php else: ?>
            <tr>
                <td class="report-cell" colspan="3"><div class="report-title bold">Laporan Hasil Perjalanan Dinas</div></td>
            </tr>
            <?php foreach ($chunks as $chunk): ?>
                <tr>
                    <td class="report-cell" colspan="3">
                        <div class="report-content laporan-html"><?= $chunk; ?></div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>

    <div class="page-break"></div>

    <div class="signature-sheet">
        <div class="sign-date"><?= esc('Pekanbaru, ' . $renderedDate); ?></div>
        <div class="sign-title">Dibuat Oleh :</div>

        <table class="sign-grid">
            <tr>
                <td>
                    <div class="sign-jabatan"><?= nl2br(esc((string) ($pelaksanaSignerLeft['jabatan'] ?? '-'))); ?></div>
                    <div class="sign-name"><?= esc((string) ($pelaksanaSignerLeft['nama'] ?? '-')); ?></div>
                    <div class="sign-nip">NIP. <?= esc((string) ($pelaksanaSignerLeft['nip'] ?? '-')); ?></div>
                </td>
                <td>
                    <div class="sign-jabatan"><?= nl2br(esc((string) ($pelaksanaSignerRight['jabatan'] ?? '-'))); ?></div>
                    <div class="sign-name"><?= esc((string) ($pelaksanaSignerRight['nama'] ?? '-')); ?></div>
                    <div class="sign-nip">NIP. <?= esc((string) ($pelaksanaSignerRight['nip'] ?? '-')); ?></div>
                </td>
            </tr>
        </table>

        <div class="sign-known">
            <div class="known-title">Diketahui Oleh :</div>
            <div class="known-jabatan"><?= nl2br(esc((string) ($diketahuiOleh['jabatan'] ?? '-'))); ?></div>
            <div class="known-name"><?= esc((string) ($diketahuiOleh['nama'] ?? '-')); ?></div>
            <div class="known-nip">NIP. <?= esc((string) ($diketahuiOleh['nip'] ?? '-')); ?></div>
        </div>
    </div>

    <div class="page-break"></div>

    <div class="lampiran-title">LAMPIRAN DOKUMENTASI PELAKSANAAN PERJALANAN DINAS</div>
    <?php if ($photos !== []): ?>
        <table class="photo-grid">
            <?php foreach (array_chunk($photos, 2) as $photoRow): ?>
                <tr>
                    <?php foreach ($photoRow as $photo): ?>
                        <td>
                            <img src="<?= esc((string) ($photo['data_uri'] ?? '')); ?>" alt="Foto Dokumentasi">
                        </td>
                    <?php endforeach; ?>
                    <?php if (count($photoRow) === 1): ?>
                        <td></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <table class="photo-grid">
            <tr>
                <td style="height:220px;"></td>
                <td></td>
            </tr>
        </table>
    <?php endif; ?>
</body>
</html>