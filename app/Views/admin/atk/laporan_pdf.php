<?php
use App\Libraries\AtkExcelExporter;

$grandTotal = 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Rekapitulasi ATK &amp; Supplies <?= (int) $tahun; ?></title>
<style>
    @page { margin: 14mm 15mm; }
    body { font-family: "DejaVu Sans", Arial, sans-serif; font-size: 9pt; color: #111; }
    .kop { border-bottom: 2px solid #000; padding-bottom: 6px; margin-bottom: 10px; text-align: center; }
    .kop img { width: 100%; max-height: 85px; object-fit: contain; }
    h3 { text-align: center; margin: 6px 0 12px 0; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #000; padding: 5px 6px; }
    th { background: #efefef; text-align: center; }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
</style>
</head>
<body>
    <div class="kop">
        <?php if ($kopDataUri !== ''): ?>
            <img src="<?= $kopDataUri; ?>" alt="Kop Surat">
        <?php else: ?>
            <strong>SATUAN KERJA PELAKSANAAN PRASARANA STRATEGIS RIAU</strong>
        <?php endif; ?>
    </div>

    <h3>REKAPITULASI PENGADAAN ATK &amp; SUPPLIES KOMPUTER TAHUN <?= (int) $tahun; ?></h3>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Bulan</th>
                <th>Kategori</th>
                <th>No. Pesanan</th>
                <th>No. BASTP</th>
                <th>Penyedia</th>
                <th>Mata Anggaran</th>
                <th>Status</th>
                <th>Total Nilai (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $i => $r): ?>
                <?php $grandTotal += (float) $r['total_nilai']; ?>
                <tr>
                    <td class="text-center"><?= $i + 1; ?></td>
                    <td><?= esc(AtkExcelExporter::bulanNama((int) $r['bulan'])); ?></td>
                    <td><?= esc($r['kategori']); ?></td>
                    <td><?= esc($r['no_pesanan'] ?: '-'); ?></td>
                    <td><?= esc($r['no_bastp'] ?: '-'); ?></td>
                    <td><?= esc($r['nama_perusahaan'] ?: '-'); ?></td>
                    <td><?= esc($r['kode_mata_anggaran'] ?: '-'); ?></td>
                    <td class="text-center"><?= esc(strtoupper((string) $r['status'])); ?></td>
                    <td class="text-right"><?= number_format((float) $r['total_nilai'], 0, ',', '.'); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="8" class="text-right">TOTAL KESELURUHAN</th>
                <th class="text-right">Rp <?= number_format($grandTotal, 0, ',', '.'); ?></th>
            </tr>
        </tfoot>
    </table>
</body>
</html>

