<?= $this->extend('layouts/admin'); ?>

<?php
use App\Libraries\AtkExcelExporter;
?>

<?= $this->section('content'); ?>
<div class="row mb-3">
    <div class="col-md-4">
        <div class="small-box bg-dark">
            <div class="inner">
                <h3 style="font-size: 1.65rem;">Rp <?= number_format((float) $totalAtk, 0, ',', '.'); ?></h3>
                <p>Total Realisasi ATK (<?= (int) $tahun; ?>)</p>
            </div>
            <div class="icon"><i class="fas fa-pen-nib"></i></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="small-box bg-info">
            <div class="inner">
                <h3 style="font-size: 1.65rem;">Rp <?= number_format((float) $totalSupplies, 0, ',', '.'); ?></h3>
                <p>Total Realisasi Supplies Komputer (<?= (int) $tahun; ?>)</p>
            </div>
            <div class="icon"><i class="fas fa-print"></i></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="small-box bg-success">
            <div class="inner">
                <h3 style="font-size: 1.65rem;">Rp <?= number_format((float) $grandTotal, 0, ',', '.'); ?></h3>
                <p>Grand Total ATK &amp; Supplies (<?= (int) $tahun; ?>)</p>
            </div>
            <div class="icon"><i class="fas fa-coins"></i></div>
        </div>
    </div>
</div>

<div class="card card-outline card-primary">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
        <h3 class="card-title mb-2 mb-md-0"><i class="fas fa-chart-bar mr-1"></i> Rekapitulasi Bulanan ATK &amp; Supplies Tahun <?= (int) $tahun; ?></h3>
        <div class="d-flex flex-wrap" style="gap: 6px;">
            <form method="get" action="<?= site_url('/admin/atk/laporan'); ?>" class="form-inline mr-2">
                <select name="tahun" class="form-control form-control-sm mr-1" onchange="this.form.submit()">
                    <?php for ($y = date('Y') + 1; $y >= 2024; $y--): ?>
                        <option value="<?= $y; ?>" <?= (int) $tahun === $y ? 'selected' : ''; ?>>Tahun <?= $y; ?></option>
                    <?php endfor; ?>
                </select>
            </form>
            <?php if (! empty($permissions['export'])): ?>
                <a href="<?= site_url('/admin/atk/laporan/export-excel?tahun=' . (int) $tahun); ?>" class="btn btn-sm btn-success">
                    <i class="fas fa-file-excel mr-1"></i> Export Excel
                </a>
                <a href="<?= site_url('/admin/atk/laporan/export-pdf?tahun=' . (int) $tahun); ?>" target="_blank" class="btn btn-sm btn-danger">
                    <i class="fas fa-file-pdf mr-1"></i> Cetak PDF
                </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-sm">
            <thead class="thead-light">
                <tr>
                    <th>Bulan</th>
                    <th class="text-right">ATK</th>
                    <th class="text-right">Supplies Komputer</th>
                    <th class="text-right">Total Bulan</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <?php $d = $perBulan[$m]; ?>
                    <tr>
                        <td><strong><?= esc(AtkExcelExporter::bulanNama($m)); ?></strong></td>
                        <td class="text-right">Rp <?= number_format((float) $d['atk'], 0, ',', '.'); ?></td>
                        <td class="text-right">Rp <?= number_format((float) $d['supplies'], 0, ',', '.'); ?></td>
                        <td class="text-right font-weight-bold">Rp <?= number_format((float) $d['total'], 0, ',', '.'); ?></td>
                    </tr>
                <?php endfor; ?>
            </tbody>
            <tfoot>
                <tr class="bg-light">
                    <th>TOTAL TAHUN <?= (int) $tahun; ?></th>
                    <th class="text-right">Rp <?= number_format((float) $totalAtk, 0, ',', '.'); ?></th>
                    <th class="text-right">Rp <?= number_format((float) $totalSupplies, 0, ',', '.'); ?></th>
                    <th class="text-right">Rp <?= number_format((float) $grandTotal, 0, ',', '.'); ?></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<?= $this->endSection(); ?>

