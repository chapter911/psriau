<?php
helper('custom');
helper('pdf_helper');

$records = $records ?? [];

$getSignatureJabatan = function($person): string {
    $jabatan = $person['jabatan'] ?? '';
    $nip = trim((string) ($person['nip'] ?? ''));
    if ($nip === '198002142014121002') {
        return "KEPALA SATUAN KERJA<br/>PELAKSANAAN PRASARANA STRATEGIS RIAU";
    }
    
    if (empty($jabatan)) return 'PEJABAT PENANDATANGAN';
    
    $parts = explode(',', $jabatan);
    $parts = array_map('trim', $parts);
    
    if (preg_match('/^(kepala|satker)/i', $parts[0]) && isset($parts[1]) && !empty($parts[1])) {
        $title = str_replace('Satker Mandiri', 'Satuan Kerja', $parts[0]);
        return strtoupper($title) . '<br/>' . strtoupper($parts[1]);
    }
    
    return strtoupper($parts[0]);
};
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
    @page {
        size: A4;
        margin: 1.2cm 1.5cm 1.5cm 2cm;
    }

    body {
        font-family: "Times New Roman", Times, serif;
        font-size: 11pt;
        color: #000;
        line-height: 1.4;
    }

    .center {
        text-align: center;
    }

    .bold {
        font-weight: bold;
    }

    .title-block {
        margin-top: 15px;
        margin-bottom: 20px;
    }

    .title-block h3 {
        margin: 0;
        font-size: 14pt;
        text-decoration: underline;
        letter-spacing: 1px;
    }

    .title-block p {
        margin: 2px 0 0 0;
        font-size: 11pt;
    }

    .content-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }

    .content-table td {
        vertical-align: top;
        padding: 4px 0;
    }

    .label-col {
        width: 15%;
    }

    .colon-col {
        width: 3%;
        text-align: center;
    }

    .value-col {
        width: 82%;
    }

    .memberi-tugas {
        margin: 20px 0;
        font-size: 12pt;
        letter-spacing: 2px;
    }

    .signature-container {
        margin-top: 35px;
        width: 100%;
        page-break-inside: avoid;
    }

    .signature-table {
        width: 100%;
        border-collapse: collapse;
    }

    .signature-table td {
        vertical-align: top;
        border: none;
    }

    .tembusan-block {
        margin-top: 50px;
        font-size: 9.5pt;
        page-break-inside: avoid;
    }

    .tembusan-block p {
        margin: 0 0 3px 0;
    }

    .page-break {
        page-break-after: always;
    }
    </style>
</head>
<body>

    <?php foreach ($records as $index => $data): ?>
        <?php
        $periodeMulai = tanggal_indonesia((string) ($data['periode_mulai'] ?? ''));
        $periodeSelesai = tanggal_indonesia((string) ($data['periode_selesai'] ?? ''));
        $tglTtd = tanggal_indonesia((string) ($data['tanggal_tanda_tangan'] ?? date('Y-m-d')));
        $pelaksana = $data['pelaksana'] ?? [];
        $diketahuiOleh = $data['diketahui_oleh'] ?? [];
        $dasarSpt = $data['dasar_spt'] ?? [];
        ?>

        <div class="center">
            <?= kop_surat_img_tag('', 'width:100%; max-height:120px; object-fit:contain;', 'Kop Surat', isset($data['kop_surat_id']) ? (int) $data['kop_surat_id'] : null); ?>
        </div>

        <div class="center title-block">
            <h3 class="bold">SURAT TUGAS</h3>
            <p>NOMOR : <?= esc($data['nomor_surat_tugas'] ?? '-'); ?></p>
        </div>

        <table class="content-table">
            <tr>
                <td class="label-col">Menimbang</td>
                <td class="colon-col">:</td>
                <td class="value-col">
                    Bahwa Dalam rangka melaksanakan tugas perjalanan dinas / monitoring dan evaluasi untuk <strong><?= esc($data['tujuan'] ?? ''); ?></strong>. Pelaksanaan dilakukan selama <strong><?= (int) ($data['duration_days'] ?? 0); ?> (<?= esc($data['duration_word'] ?? ''); ?>)</strong> hari, mulai dari <strong><?= esc($periodeMulai); ?></strong> sampai dengan <strong><?= esc($periodeSelesai); ?></strong>.
                </td>
            </tr>
            <tr>
                <td colspan="3" style="height: 10px;"></td>
            </tr>
            <tr>
                <td class="label-col">Dasar</td>
                <td class="colon-col">:</td>
                <td class="value-col">
                    <?php if (!empty($dasarSpt)): ?>
                        <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
                            <?php foreach ($dasarSpt as $idx => $ds): ?>
                                <tr>
                                    <td style="width: 25px; padding: 0 0 4px 0; vertical-align: top;"><?= $idx + 1; ?>.</td>
                                    <td style="padding: 0 0 4px 0; vertical-align: top; text-align: justify;"><?= esc($ds['uraian']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <div class="center memberi-tugas bold">
            MEMBERI TUGAS
        </div>

        <table class="content-table">
            <tr>
                <td class="label-col">Kepada</td>
                <td class="colon-col">:</td>
                <td class="value-col">
                    <?php if (!empty($pelaksana)): ?>
                        <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
                            <?php foreach ($pelaksana as $idx => $p): ?>
                                <tr>
                                    <td style="width: 25px; padding: 0 0 6px 0; vertical-align: top;"><?= $idx + 1; ?>.</td>
                                    <td style="padding: 0 0 6px 0; vertical-align: top;">
                                        <strong><?= esc($p['nama'] ?? '-'); ?></strong>
                                        <?php if (should_show_nip($p) && !empty($p['nip'])): ?>
                                            <br/>NIP. <?= esc($p['nip']); ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td colspan="3" style="height: 10px;"></td>
            </tr>
            <tr>
                <td class="label-col">Untuk</td>
                <td class="colon-col">:</td>
                <td class="value-col" style="text-align: justify;">
                    Pekerjaan <strong><?= esc($data['sasaran'] ?? ''); ?></strong> Kabupaten/Kota <strong><?= esc($data['kota_tujuan'] ?? ''); ?></strong>.
                </td>
            </tr>
        </table>

        <div class="signature-container">
            <table class="signature-table">
                <tr>
                    <td style="width: 45%;"></td>
                    <td style="width: 55%; text-align: left; padding-left: 20px;">
                        <div style="margin-bottom: 2px;">PEKANBARU, <?= esc(strtoupper($tglTtd)); ?></div>
                        <div class="bold" style="line-height: 1.3;">
                            <?= $getSignatureJabatan($diketahuiOleh); ?>
                        </div>
                        <div style="height: 70px;"></div>
                        <div class="bold" style="text-decoration: underline; text-transform: uppercase;">
                            <?= esc($diketahuiOleh['nama'] ?? '-'); ?>
                        </div>
                        <div>
                            <?php 
                                $nipVal = trim((string) ($diketahuiOleh['nip'] ?? ''));
                                if (should_show_nip($diketahuiOleh) && $nipVal !== '') {
                                    echo 'NIP. ' . $nipVal;
                                }
                            ?>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="tembusan-block">
            <p class="bold" style="text-decoration: underline;">Tembusan :</p>
            <p>Pejabat Pembuat Komitmen PPS Riau</p>
        </div>

        <?php if ($index < count($records) - 1): ?>
            <div class="page-break"></div>
        <?php endif; ?>

    <?php endforeach; ?>

</body>
</html>
