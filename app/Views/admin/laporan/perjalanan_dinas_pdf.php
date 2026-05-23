<?php helper('custom'); ?>
<?php
    $data = $data ?? [];
    $periodeMulai = tanggal_indonesia((string) ($data['periode_mulai'] ?? ''));
    $periodeSelesai = tanggal_indonesia((string) ($data['periode_selesai'] ?? ''));
    $pelaksana = $data['pelaksana'] ?? [];
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        @page { margin: 1.3cm 1.5cm 1.5cm 1.5cm; }
        body { font-family: "Times New Roman", serif; font-size: 10.8pt; line-height: 1.35; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .small { font-size: 10pt; }
        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        .mb-3 { margin-bottom: 14px; }
        .table { width: 100%; border-collapse: collapse; }
        .table td, .table th { padding: 4px 6px; vertical-align: top; }
        .bordered td, .bordered th { border: 1px solid #000; }
        .title { font-size: 12.8pt; }
        .main-sheet td { border: 1px solid #000; }
        .main-sheet .label-col { width: 41%; }
        .main-sheet .colon-col { width: 3%; text-align: center; }
        .main-sheet .value-col { width: 56%; }
        .pelaksana-item { margin-bottom: 7px; }
        .pelaksana-item:last-child { margin-bottom: 0; }
        .pelaksana-no { display: inline-block; width: 18px; }
        .pelaksana-label { display: inline-block; width: 66px; }
        .laporan-html p { margin: 0 0 4px 0; }
        .laporan-html ol, .laporan-html ul { margin: 0 0 4px 20px; }
        .laporan-html li { margin: 0 0 3px 0; }
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
                            <div><span class="pelaksana-no"><?= (int) $index + 1; ?>.</span><span class="pelaksana-label">Nama</span>: <strong><?= esc((string) ($row['nama'] ?? '-')); ?></strong></div>
                            <div><span class="pelaksana-no"></span><span class="pelaksana-label">Jabatan</span>: <?= esc((string) ($row['jabatan'] ?? '-')); ?></div>
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
        <tr>
            <td class="label-col">Laporan Hasil Perjalanan Dinas</td>
            <td class="colon-col">:</td>
            <td class="value-col">
                <div class="laporan-html">
                    <?php $laporanHasilRaw = trim((string) ($data['laporan_hasil'] ?? '')); ?>
                    <?php if ($laporanHasilRaw === ''): ?>
                        -
                    <?php else: ?>
                        <?= $laporanHasilRaw; ?>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>