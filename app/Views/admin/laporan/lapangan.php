<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">
    <!-- Quick Statistics Row -->
    <div class="row mb-3">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info shadow-sm">
                <div class="inner">
                    <h3><?= count($projects); ?></h3>
                    <p>Total Proyek Sekolah</p>
                </div>
                <div class="icon">
                    <i class="fas fa-school"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success shadow-sm">
                <div class="inner">
                    <?php
                    $completedCount = 0;
                    foreach ($projects as $proj) {
                        if ($proj['progress_persen'] >= 100) {
                            $completedCount++;
                        }
                    }
                    ?>
                    <h3><?= $completedCount; ?></h3>
                    <p>Proyek Selesai (100%)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-double"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline shadow-sm">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-chart-line mr-2 text-primary"></i>
                        Progress Laporan Lapangan per Proyek Sekolah
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover w-100" id="tableLaporanLapangan">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 50px;" class="text-center">No</th>
                                    <th>Sekolah</th>
                                    <th>Paket Pekerjaan</th>
                                    <th style="width: 180px;" class="text-center">Rincian Progress Pekerjaan</th>
                                    <th style="width: 250px;">Progress Proyek</th>
                                    <th style="width: 120px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($projects)): ?>
                                    <?php $no = 1; foreach ($projects as $project): ?>
                                        <tr>
                                            <td class="text-center align-middle"><?= esc((string) $no++); ?></td>
                                            <td class="align-middle">
                                                <strong><?= esc((string) ($project['nama_sekolah'] ?? '-')); ?></strong>
                                                <div class="small text-muted">NPSN: <?= esc((string) ($project['sekolah_npsn'] ?? '-')); ?></div>
                                            </td>
                                            <td class="align-middle">
                                                <span class="badge badge-secondary"><?= esc((string) ($project['nama_paket'] ?? '-')); ?></span>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="badge badge-info py-2 px-3">
                                                    <?= esc((string) $project['reported_count']); ?> / <?= esc((string) $project['total_pekerjaan']); ?> Item Dilaporkan
                                                </span>
                                            </td>
                                            <td class="align-middle">
                                                <div class="d-flex align-items-center">
                                                    <div class="progress progress-xs w-100 mr-2" style="height: 10px; border-radius: 5px;">
                                                        <div class="progress-bar bg-success progress-bar-striped" role="progressbar" 
                                                             aria-valuenow="<?= esc($project['progress_persen']); ?>" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100" 
                                                             style="width: <?= esc($project['progress_persen']); ?>%; border-radius: 5px;">
                                                        </div>
                                                    </div>
                                                    <span class="font-weight-bold text-success" style="min-width: 45px;"><?= esc($project['progress_persen']); ?>%</span>
                                                </div>
                                            </td>
                                            <td class="text-center align-middle">
                                                <a href="<?= site_url('admin/laporan/lapangan/detail/' . esc($project['sekolah_npsn']) . '/' . (int) $project['paket_id']); ?>" class="btn btn-primary btn-sm btn-flat shadow-sm">
                                                    <i class="fas fa-eye mr-1"></i> Detail Progress
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">Belum ada data proyek sekolah dengan paket pekerjaan terdaftar.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script>
(() => {
    if (typeof $ === 'undefined' || ! $.fn.DataTable) {
        return;
    }

    const $table = $('#tableLaporanLapangan');
    if ($table.length && ! $.fn.dataTable.isDataTable($table)) {
        $table.DataTable({
            responsive: false,
            autoWidth: false,
            scrollX: true,
            pageLength: 10,
            order: [[4, 'desc']], // Order by progress descending
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Semua']],
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data',
                zeroRecords: 'Data tidak ditemukan',
                paginate: {
                    first: 'Awal',
                    last: 'Akhir',
                    next: 'Berikutnya',
                    previous: 'Sebelumnya',
                },
            },
        });
    }
})();
</script>
<?= $this->endSection(); ?>
