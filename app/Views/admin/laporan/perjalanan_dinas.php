<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
    $reports = $reports ?? [];
    $canEdit = (bool) ($can_edit ?? false);
?>
<div class="card">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title mb-0">Laporan Perjalanan Dinas</h3>
        <div class="card-tools ml-auto">
            <a href="<?= site_url('admin/laporan/perjalanan-dinas/buat'); ?>" class="btn btn-primary btn-sm">Buat Laporan</a>
        </div>
    </div>
    <div class="card-body">
        <?php if ($reports === []): ?>
            <div class="alert alert-info mb-0" role="alert">
                Belum ada data laporan perjalanan dinas yang tersimpan.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead>
                        <tr>
                            <th style="width:60px;" class="text-center">No</th>
                            <th>Tujuan</th>
                            <th style="width:220px;">Kota Tujuan</th>
                            <th style="width:220px;">Periode</th>
                            <th>Nama Pelaksana</th>
                            <th style="width:110px;" class="text-center">Lihat Dokumen</th>
                            <th style="width:90px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $idx => $report): ?>
                            <tr>
                                <td class="text-center"><?= (int) $idx + 1; ?></td>
                                <td><?= esc((string) ($report['tujuan'] ?? '-')); ?></td>
                                <td><?= esc((string) ($report['kota_tujuan'] ?? '-')); ?></td>
                                <td><?= esc((string) ($report['periode'] ?? '-')); ?></td>
                                <td><?= esc(implode(', ', (array) ($report['pelaksana_names'] ?? [])) ?: '-'); ?></td>
                                <td class="text-center">
                                    <a href="<?= site_url('admin/laporan/perjalanan-dinas/' . (int) ($report['id'] ?? 0) . '/dokumen'); ?>" class="btn btn-sm btn-outline-danger" title="Lihat Dokumen" target="_blank" rel="noopener noreferrer">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                </td>
                                <td class="text-center">
                                    <?php if ($canEdit): ?>
                                        <a href="<?= site_url('admin/laporan/perjalanan-dinas/' . (int) ($report['id'] ?? 0) . '/ubah'); ?>" class="btn btn-sm btn-outline-primary" title="Ubah Data">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection(); ?>
