<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<link rel="stylesheet" href="<?= esc(media_url('assets/adminlte/plugins/chart.js/Chart.min.css')); ?>">

<div class="container-fluid">
    <!-- Back Button -->
    <div class="mb-3">
        <a href="<?= site_url('admin/laporan/lapangan'); ?>" class="btn btn-secondary btn-flat btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar Proyek
        </a>
    </div>

    <!-- General Info Cards -->
    <div class="row">
        <!-- Project Info -->
        <div class="col-md-5 col-12">
            <div class="card card-outline card-primary shadow-sm h-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-2 text-primary"></i>
                        Informasi Umum Proyek
                    </h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <tr>
                            <th style="width: 35%;">Nama Sekolah</th>
                            <td>
                                <strong><?= esc((string) ($sekolah['nama'] ?? '-')); ?></strong>
                                <div class="small text-muted">NPSN: <?= esc((string) ($sekolah['npsn'] ?? '-')); ?></div>
                            </td>
                        </tr>
                        <tr>
                            <th>Kabupaten / Kota</th>
                            <td><?= esc((string) ($sekolah['kabupaten'] ?? '-')); ?></td>
                        </tr>
                        <tr>
                            <th>Kecamatan</th>
                            <td><?= esc((string) ($sekolah['kecamatan'] ?? '-')); ?></td>
                        </tr>
                        <tr>
                            <th>Paket Pekerjaan</th>
                            <td>
                                <span class="badge badge-primary py-1 px-2 font-weight-bold">
                                    <?= esc((string) ($paket['nama_paket'] ?? '-')); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Total Progress Proyek</th>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress progress-xs w-100 mr-2" style="height: 12px; border-radius: 6px;">
                                        <div class="progress-bar bg-success progress-bar-striped" role="progressbar" 
                                             aria-valuenow="<?= esc($overallProgress); ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100" 
                                             style="width: <?= esc($overallProgress); ?>%; border-radius: 6px;">
                                        </div>
                                    </div>
                                    <span class="font-weight-bold text-success text-lg"><?= esc($overallProgress); ?>%</span>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Progress Chart -->
        <div class="col-md-7 col-12">
            <div class="card card-outline card-success shadow-sm h-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line mr-2 text-success"></i>
                        Grafik Progress Akumulatif Proyek
                    </h3>
                </div>
                <div class="card-body d-flex flex-column justify-content-center" style="position: relative; min-height: 250px;">
                    <?php if (!empty($chartLabels)): ?>
                        <div style="height: 220px; width: 100%;">
                            <canvas id="progressChart"></canvas>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted my-5">
                            <i class="fas fa-chart-area fa-3x mb-3 text-muted/30"></i>
                            <p>Belum ada data laporan harian untuk memetakan grafik progress.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Pekerjaan / Progress Details Card -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card card-outline card-warning shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tasks mr-2 text-warning"></i>
                        Daftar Progress Item Pekerjaan
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover w-100 mb-0" id="tableItems">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 50px;" class="text-center">No</th>
                                    <th>Pekerjaan Utama</th>
                                    <th>Kategori</th>
                                    <th>Sub Kategori</th>
                                    <th>Uraian Detail</th>
                                    <th style="width: 80px;" class="text-center">Bobot</th>
                                    <th style="width: 100px;" class="text-center">Progres</th>
                                    <th style="width: 110px;" class="text-center">Status</th>
                                    <th style="width: 150px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($jobs)): ?>
                                    <?php $no = 1; foreach ($jobs as $job): ?>
                                        <tr data-id="<?= esc($job['id']); ?>">
                                            <td class="text-center align-middle"><?= esc((string) $no++); ?></td>
                                            <td class="align-middle"><strong><?= esc((string) ($job['pekerjaan_utama'] ?: 'Default')); ?></strong></td>
                                            <td class="align-middle"><?= esc((string) ($job['kategori_1'] ?? '-')); ?></td>
                                            <td class="align-middle"><?= esc((string) ($job['kategori_2'] ?? '-')); ?></td>
                                            <td class="align-middle">
                                                <span><?= esc((string) ($job['uraian'] ?? '-')); ?></span>
                                                <div class="small text-muted mt-1">ID: <?= esc((string) $job['id']); ?></div>
                                            </td>
                                            <td class="text-center align-middle font-weight-bold text-muted"><?= esc((string) ($job['bobot_persen'] ?? '0')); ?>%</td>
                                            <td class="text-center align-middle font-weight-bold text-primary"><?= esc((string) $job['latest_progress']); ?>%</td>
                                            <td class="text-center align-middle">
                                                <?php if ((int)$job['status_selesai'] === 1): ?>
                                                    <span class="badge badge-success px-2 py-1">Selesai</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning px-2 py-1">Belum Selesai</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center align-middle">
                                                <button type="button" class="btn btn-info btn-sm btn-flat btn-show-history shadow-sm" 
                                                        data-job-id="<?= esc($job['id']); ?>">
                                                    <i class="fas fa-history mr-1"></i> Cek Laporan (<?= count($job['history']); ?>)
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal History Viewer -->
<div class="modal fade" id="modalHistoryViewer" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-history mr-2"></i> Riwayat Laporan Harian</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0" id="historyViewerBody">
                <!-- Content will be injected dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-flat" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Templates for Daily Report History -->
<div id="historyTemplates" class="d-none">
    <?php foreach ($jobs as $job): ?>
        <div id="history-content-<?= esc($job['id']); ?>">
            <div class="p-3">
                <div class="alert alert-light border mb-3">
                    <div class="row">
                        <div class="col-sm-6">
                            <strong>Item Pekerjaan:</strong> <span><?= esc($job['uraian']); ?></span>
                        </div>
                        <div class="col-sm-6 text-sm-right">
                            <strong>Pekerjaan Utama:</strong> <span><?= esc($job['pekerjaan_utama'] ?: '-'); ?></span>
                        </div>
                    </div>
                </div>
                <?php if (!empty($job['history'])): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover bg-white mb-0">
                            <thead class="bg-gray-light text-muted small">
                                <tr>
                                    <th style="width: 120px;" class="text-center">Tanggal</th>
                                    <th style="width: 150px;" class="text-center">Jam Kerja</th>
                                    <th style="width: 120px;" class="text-center">Progres Hari Itu</th>
                                    <th style="width: 120px;" class="text-center">Status</th>
                                    <th>Keterangan & Kendala</th>
                                    <th>Pelapor</th>
                                    <th style="width: 120px;" class="text-center">Foto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($job['history'] as $hist): ?>
                                    <tr>
                                        <td class="align-middle text-center">
                                            <strong><?= esc(date('d-m-Y', strtotime($hist['tanggal']))); ?></strong>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="badge badge-light">
                                                <?= esc((string) $hist['jam_mulai']); ?> - <?= esc((string) $hist['jam_selesai']); ?>
                                            </span>
                                        </td>
                                        <td class="text-center align-middle font-weight-bold text-primary">
                                            <?= esc((string) $hist['progres_persen']); ?>%
                                        </td>
                                        <td class="text-center align-middle">
                                            <?php if ((int)$hist['status_selesai'] === 1): ?>
                                                <span class="badge badge-success">Selesai</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Belum Selesai</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="align-middle">
                                            <?php if (!empty($hist['keterangan_progres'])): ?>
                                                <div><strong>Keterangan:</strong> <?= esc((string) $hist['keterangan_progres']); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($hist['kendala'])): ?>
                                                <div class="text-danger mt-1"><strong>Kendala:</strong> <?= esc((string) $hist['kendala']); ?></div>
                                            <?php endif; ?>
                                            <?php if (empty($hist['keterangan_progres']) && empty($hist['kendala'])): ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="align-middle"><?= esc((string) ($hist['nama_pelapor'] ?? '-')); ?></td>
                                        <td class="text-center align-middle">
                                            <?php
                                            $photos = json_decode($hist['foto_paths_json'] ?? '[]', true) ?: [];
                                            if (!empty($photos)):
                                            ?>
                                                <button type="button" class="btn btn-info btn-xs btn-flat btn-show-photos" data-photos='<?= esc(json_encode($photos)); ?>'>
                                                    <i class="fas fa-images mr-1"></i> <?= count($photos); ?> Foto
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted small">Tidak ada foto</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-3 text-center text-muted small bg-white border rounded">
                        <i class="fas fa-info-circle mr-1"></i> Belum ada laporan harian untuk item pekerjaan ini.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Modal Photo Viewer -->
<div class="modal fade" id="modalPhotoViewer" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1600;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-info">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-images mr-2"></i> Foto Dokumentasi</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body bg-light">
                <div id="photoViewerContainer" class="d-flex flex-wrap justify-content-center">
                    <!-- Images will be inserted here dynamically -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-flat" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script src="<?= esc(media_url('assets/adminlte/plugins/chart.js/Chart.min.js')); ?>"></script>
<script>
(() => {
    // 1. Chart rendering
    <?php if (!empty($chartLabels)): ?>
    const ctx = document.getElementById('progressChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($chartLabels); ?>,
            datasets: [{
                label: 'Progress Akumulatif (%)',
                data: <?= json_encode($chartData); ?>,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                borderWidth: 3,
                pointBackgroundColor: '#28a745',
                pointRadius: 4,
                tension: 0.15,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        max: 100,
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }]
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        return 'Progress: ' + tooltipItem.yLabel + '%';
                    }
                }
            }
        }
    });
    <?php endif; ?>

    // 2. DataTables integration
    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        const $table = $('#tableItems');
        if ($table.length && ! $.fn.dataTable.isDataTable($table)) {
            const table = $table.DataTable({
                responsive: false,
                autoWidth: false,
                scrollX: true,
                pageLength: 10,
                order: [[5, 'desc']], // Sort by weight (bobot) descending
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Semua']],
                columnDefs: [
                    { orderable: false, targets: [8] } // Make Action column non-sortable
                ],
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
    }

    // 3. Show History Modal Action
    $(document).on('click', '.btn-show-history', function() {
        const jobId = $(this).data('job-id');
        const contentHtml = $('#history-content-' + jobId).html();
        $('#historyViewerBody').html(contentHtml);
        $('#modalHistoryViewer').modal('show');
    });

    // 4. Photo viewer action
    $(document).on('click', '.btn-show-photos', function() {
        const photos = $(this).data('photos') || [];
        const $container = $('#photoViewerContainer');
        $container.empty();

        if (photos.length > 0) {
            const baseUrl = '<?= base_url(); ?>';
            photos.forEach(photo => {
                const photoUrl = photo.startsWith('http') ? photo : baseUrl + '/' + photo.replace(/^\//, '');
                $container.append(`
                    <div class="m-2 text-center" style="max-width: 100%;">
                        <a href="${photoUrl}" target="_blank">
                            <img src="${photoUrl}" class="img-thumbnail shadow-sm" style="max-height: 400px; max-width: 100%; object-fit: contain;">
                        </a>
                    </div>
                `);
            });
            $('#modalPhotoViewer').modal('show');
        }
    });
})();
</script>
<?= $this->endSection(); ?>
