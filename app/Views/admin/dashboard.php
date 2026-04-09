<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<link rel="stylesheet" href="<?= base_url('assets/adminlte/plugins/chart.js/Chart.min.css'); ?>">
<style>
    .admin-dashboard {
        display: grid;
        gap: 1rem;
    }

    .dashboard-hero {
        border: 0;
        border-radius: 16px;
        color: #fff;
        background: linear-gradient(125deg, #0f4c81 0%, #0b7f6d 58%, #db6e2a 100%);
        overflow: hidden;
    }

    .dashboard-hero .card-body {
        padding: 1.25rem;
    }

    .dashboard-hero h2 {
        font-size: 1.5rem;
        margin-bottom: .4rem;
    }

    .hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: .6rem;
        margin-top: .9rem;
    }

    .hero-meta .badge {
        font-size: .8rem;
        padding: .45rem .65rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, .18);
        color: #fff;
        font-weight: 600;
    }

    .metric-card {
        border: 1px solid #e7ecf3;
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(20, 28, 41, .06);
        transition: all 0.3s ease;
    }

    .metric-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(20, 28, 41, .12);
    }

    .metric-card .card-body {
        padding: 1rem;
    }

    .metric-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: .55rem;
    }

    .metric-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1rem;
    }

    .metric-icon.events { background: linear-gradient(145deg, #1689a5, #0f6f87); }
    .metric-icon.instagram { background: linear-gradient(145deg, #e74366, #c82476); }
    .metric-icon.slide { background: linear-gradient(145deg, #f59f00, #ea7300); }
    .metric-icon.school { background: linear-gradient(145deg, #7c3aed, #6d28d9); }
    .metric-icon.survey { background: linear-gradient(145deg, #059669, #047857); }
    .metric-icon.laporan { background: linear-gradient(145deg, #2563eb, #1d4ed8); }
    .metric-icon.wilayah { background: linear-gradient(145deg, #dc2626, #b91c1c); }

    .metric-value {
        font-size: 1.7rem;
        font-weight: 700;
        line-height: 1.1;
        margin-bottom: .2rem;
    }

    .metric-label {
        margin-bottom: .65rem;
        color: #5c697c;
        font-weight: 600;
        font-size: .9rem;
    }

    .metric-split {
        display: flex;
        gap: .5rem;
        font-size: .85rem;
    }

    .metric-split span {
        padding: .28rem .55rem;
        border-radius: 999px;
        background: #f3f5f9;
        color: #38455a;
        font-weight: 600;
    }

    .panel-card {
        border: 1px solid #e8edf4;
        border-radius: 14px;
        box-shadow: 0 10px 24px rgba(15, 22, 35, .05);
    }

    .panel-card .card-header {
        border-bottom: 1px solid #e8edf4;
        background: #fff;
        padding: .8rem 1rem;
    }

    .panel-card .card-body {
        padding: 1rem;
    }

    .quick-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: .65rem;
    }

    .quick-link {
        border: 1px solid #e4eaf2;
        border-radius: 12px;
        background: #fff;
        color: #1f2f43;
        padding: .8rem .9rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: .4rem;
        font-weight: 600;
        font-size: .85rem;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .quick-link:hover {
        border-color: #0d5aa7;
        background: #f0f7ff;
        color: #0d5aa7;
    }

    .quick-link i {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #eef3fa;
        color: #0d5aa7;
        font-size: 0.9rem;
    }

    .activity-item {
        padding: .75rem 0;
        border-bottom: 1px solid #f0f3f8;
        display: flex;
        align-items: flex-start;
        gap: .8rem;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #0d5aa7;
        margin-top: 0.4rem;
        flex-shrink: 0;
    }

    .activity-content {
        flex: 1;
    }

    .activity-action {
        font-weight: 600;
        color: #1f2f43;
        font-size: .9rem;
        margin-bottom: .2rem;
    }

    .activity-time {
        font-size: .75rem;
        color: #8295a5;
    }

    .chart-container {
        position: relative;
        height: 280px;
        margin: 0 0 1rem 0;
    }

    .table-sm td,
    .table-sm th {
        padding: .6rem;
        vertical-align: middle;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 0.8rem;
    }

    .stat-box {
        background: linear-gradient(135deg, #f5f7fa 0%, #f9fafc 100%);
        border: 1px solid #e8edf4;
        border-radius: 10px;
        padding: 1rem;
        text-align: center;
    }

    .stat-box .stat-value {
        font-size: 1.8rem;
        font-weight: 700;
        color: #0d5aa7;
        margin: 0;
    }

    .stat-box .stat-label {
        font-size: .8rem;
        color: #6b7280;
        margin-top: .3rem;
    }

    @media (max-width: 768px) {
        .dashboard-hero h2 {
            font-size: 1.2rem;
        }
        
        .quick-grid {
            grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
        }
    }
</style>

<div class="admin-dashboard">
    <div class="card dashboard-hero">
        <div class="card-body">
            <h2>Selamat datang, <?= esc((string) session()->get('fullName')); ?>.</h2>
            <p class="mb-0">Dashboard real-time monitoring portal Satker PPS dengan data sekolah, laporan, dan konten publikasi.</p>
            <div class="hero-meta">
                <span class="badge"><i class="fas fa-user-shield mr-1"></i> <?= esc((string) session()->get('role')); ?></span>
                <span class="badge"><i class="fas fa-clock mr-1"></i> <?= esc(date('d M Y H:i')); ?> WIB</span>
                <span class="badge"><i class="fas fa-database mr-1"></i> <?= esc((string) $schoolCount); ?> Sekolah Terdaftar</span>
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <h5 class="mt-3" style="color: #1f2f43; font-weight: 700;">📰 Konten & Media</h5>
    <div class="row">
        <div class="col-lg-4 col-md-6 col-12 mb-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="metric-top">
                        <strong>Total Acara</strong>
                        <span class="metric-icon events"><i class="fas fa-calendar-days"></i></span>
                    </div>
                    <div class="metric-value"><?= esc((string) $eventCount); ?></div>
                    <p class="metric-label">Konten acara yang dikelola.</p>
                    <div class="metric-split">
                        <span><i class="fas fa-circle text-success mr-1"></i> <?= esc((string) $eventPublishedCount); ?></span>
                        <span><i class="fas fa-circle text-secondary mr-1"></i> <?= esc((string) $eventDraftCount); ?></span>
                    </div>
                    <a href="<?= site_url('/admin/acara'); ?>" class="btn btn-sm btn-outline-primary mt-3">Lihat Acara</a>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 col-12 mb-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="metric-top">
                        <strong>Feed Instagram</strong>
                        <span class="metric-icon instagram"><i class="fab fa-instagram"></i></span>
                    </div>
                    <div class="metric-value"><?= esc((string) $articleCount); ?></div>
                    <p class="metric-label">Post Instagram tersimpan.</p>
                    <div class="metric-split">
                        <span><i class="fas fa-circle text-success mr-1"></i> <?= esc((string) $articlePublishedCount); ?></span>
                        <span><i class="fas fa-circle text-secondary mr-1"></i> <?= esc((string) $articleDraftCount); ?></span>
                    </div>
                    <a href="<?= site_url('/admin/berita'); ?>" class="btn btn-sm btn-outline-primary mt-3">Lihat Feed</a>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-12 col-12 mb-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="metric-top">
                        <strong>Slide Homepage</strong>
                        <span class="metric-icon slide"><i class="fas fa-images"></i></span>
                    </div>
                    <div class="metric-value"><?= esc((string) $slideCount); ?></div>
                    <p class="metric-label">Jumlah slide hero.</p>
                    <div class="metric-split">
                        <span><i class="fas fa-circle text-success mr-1"></i> <?= esc((string) $slideActiveCount); ?></span>
                        <span><i class="fas fa-circle text-secondary mr-1"></i> <?= esc((string) max(0, $slideCount - $slideActiveCount)); ?></span>
                    </div>
                    <a href="<?= site_url('/admin/pengaturan-home'); ?>" class="btn btn-sm btn-outline-primary mt-3">Atur Home</a>
                </div>
            </div>
        </div>
    </div>

    <!-- School & Survey Section -->
    <h5 class="mt-3" style="color: #1f2f43; font-weight: 700;">🏫 Data Sekolah & Survei</h5>
    <div class="row">
        <div class="col-lg-4 col-md-6 col-12 mb-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="metric-top">
                        <strong>Total Sekolah</strong>
                        <span class="metric-icon school"><i class="fas fa-school"></i></span>
                    </div>
                    <div class="metric-value"><?= esc((string) $schoolCount); ?></div>
                    <p class="metric-label">Sekolah terdaftar dalam sistem.</p>
                    <div class="metric-split">
                        <span><i class="fas fa-check-circle mr-1" style="color: #059669;"></i> Tersurvei: <?= esc((string) $schoolWithSurvey); ?></span>
                    </div>
                    <a href="<?= site_url('/admin/master/sekolah'); ?>" class="btn btn-sm btn-outline-primary mt-3">Kelola Sekolah</a>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 col-12 mb-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="metric-top">
                        <strong>Laporan Harian</strong>
                        <span class="metric-icon laporan"><i class="fas fa-calendar-check"></i></span>
                    </div>
                    <div class="metric-value"><?= esc((string) $harianReportCount); ?></div>
                    <p class="metric-label">Laporan harian yang terbuat.</p>
                    <a href="<?= site_url('/admin/laporan/harian'); ?>" class="btn btn-sm btn-outline-primary mt-3">Lihat Laporan</a>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-12 col-12 mb-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="metric-top">
                        <strong>Laporan Mingguan</strong>
                        <span class="metric-icon survey"><i class="fas fa-chart-line"></i></span>
                    </div>
                    <div class="metric-value"><?= esc((string) $mingguanReportCount); ?></div>
                    <p class="metric-label">Laporan mingguan ringkasan.</p>
                    <a href="<?= site_url('/admin/laporan/mingguan'); ?>" class="btn btn-sm btn-outline-primary mt-3">Lihat Laporan</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Wilayah Section -->
    <h5 class="mt-3" style="color: #1f2f43; font-weight: 700;">🗺️ Data Wilayah</h5>
    <div class="row">
        <div class="col-lg-6 col-12 mb-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="metric-top">
                        <strong>Total Kabupaten</strong>
                        <span class="metric-icon wilayah"><i class="fas fa-map"></i></span>
                    </div>
                    <div class="metric-value"><?= esc((string) $kabupatenCount); ?></div>
                    <p class="metric-label">Kabupaten dalam sistem.</p>
                    <a href="<?= site_url('/admin/master/kabupaten'); ?>" class="btn btn-sm btn-outline-primary mt-3">Kelola Kabupaten</a>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-12 mb-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="metric-top">
                        <strong>Total Kecamatan</strong>
                        <span class="metric-icon wilayah"><i class="fas fa-compass"></i></span>
                    </div>
                    <div class="metric-value"><?= esc((string) $kecamatanCount); ?></div>
                    <p class="metric-label">Kecamatan dalam sistem.</p>
                    <a href="<?= site_url('/admin/master/kecamatan'); ?>" class="btn btn-sm btn-outline-primary mt-3">Kelola Kecamatan</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts & Activities Row -->
    <div class="row mt-3">
        <div class="col-lg-7 col-12 mb-3">
            <div class="card panel-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Klasifikasi Kerusakan Sekolah</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($damageClassification)): ?>
                        <div class="chart-container">
                            <canvas id="damageChart"></canvas>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                <tr>
                                    <th>Klasifikasi</th>
                                    <th>Jumlah</th>
                                    <th>Persentase</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php 
                                    $totalDamage = array_sum(array_column($damageClassification, 'count'));
                                    foreach ($damageClassification as $item): 
                                        $percentage = $totalDamage > 0 ? round((int)$item['count'] / $totalDamage * 100, 1) : 0;
                                ?>
                                    <tr>
                                        <td><strong><?= esc((string) ($item['survey_klasifikasi_kerusakan'] ?? '-')); ?></strong></td>
                                        <td><?= esc((string) $item['count']); ?></td>
                                        <td>
                                            <div class="progress" style="height: 6px; background: #e8edf4;">
                                                <div class="progress-bar" role="progressbar" style="width: <?= esc((string) $percentage); ?>%; background: linear-gradient(90deg, #0d5aa7, #2563eb);"></div>
                                            </div>
                                            <small><?= esc((string) $percentage); ?>%</small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle mr-2"></i> Belum ada data klasifikasi kerusakan sekolah.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-5 col-12 mb-3">
            <div class="card panel-card h-100">
                <div class="card-header">
                    <h3 class="card-title mb-0">Aktivitas Terbaru</h3>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <?php if (!empty($latestAudit)): ?>
                        <?php foreach ($latestAudit as $audit): ?>
                            <div class="activity-item">
                                <div class="activity-dot"></div>
                                <div class="activity-content">
                                    <div class="activity-action"><?= esc((string) ($audit['action'] ?? '-')); ?></div>
                                    <small class="activity-time"><?= esc((string) ($audit['created_at'] ?? '-')); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-secondary mb-0">
                            <i class="fas fa-inbox mr-2"></i> Belum ada aktivitas tercatat.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Checklist -->
    <div class="row mt-3">
        <div class="col-lg-7 col-12 mb-3">
            <div class="card panel-card">
                <div class="card-header">
                    <h3 class="card-title mb-0">⚡ Aksi Cepat</h3>
                </div>
                <div class="card-body">
                    <div class="quick-grid">
                        <a class="quick-link" href="<?= site_url('/admin/acara/tambah'); ?>">
                            <i class="fas fa-plus"></i> Tambah Acara
                        </a>
                        <a class="quick-link" href="<?= site_url('/admin/berita/tambah'); ?>">
                            <i class="fab fa-instagram"></i> Tambah Feed
                        </a>
                        <a class="quick-link" href="<?= site_url('/admin/master/sekolah'); ?>">
                            <i class="fas fa-school"></i> Data Sekolah
                        </a>
                        <a class="quick-link" href="<?= site_url('/admin/laporan/harian'); ?>">
                            <i class="fas fa-file-alt"></i> Laporan Harian
                        </a>
                        <a class="quick-link" href="<?= site_url('/admin/pengaturan-home'); ?>">
                            <i class="fas fa-sliders-h"></i> Atur Homepage
                        </a>
                        <a class="quick-link" href="<?= site_url('/admin/pengaturan/menus'); ?>">
                            <i class="fas fa-bars"></i> Atur Menu
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5 col-12 mb-3">
            <div class="card panel-card">
                <div class="card-header">
                    <h3 class="card-title mb-0">✓ Checklist Harian Admin</h3>
                </div>
                <div class="card-body">
                    <ul class="mb-0 pl-3">
                        <li class="mb-2">Verifikasi konten acara dan post Instagram statu publikasi.</li>
                        <li class="mb-2">Cek data survei dan laporan harian terbaru.</li>
                        <li class="mb-2">Verifikasi slider homepage menampilkan konten terbaru.</li>
                        <li class="mb-2">Review akses pengguna dan izin menu jika ada perubahan.</li>
                        <li>Arsipkan atau buat laporan mingguan ringkasan.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="<?= base_url('assets/adminlte/plugins/chart.js/Chart.min.js'); ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($damageClassification)): ?>
        const damageData = <?= json_encode(array_combine(
            array_map(fn($d) => (string)($d['survey_klasifikasi_kerusakan'] ?? ''), $damageClassification),
            array_map(fn($d) => (int)$d['count'], $damageClassification)
        )); ?>;
        
        const ctx = document.getElementById('damageChart');
        if (ctx) {
            const labels = Object.keys(damageData);
            const data = Object.values(damageData);
            const colors = ['#dc2626', '#f97316', '#eab308', '#84cc16', '#22c55e'];
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: colors.slice(0, labels.length),
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 15,
                                font: { size: 12, weight: '600' }
                            }
                        }
                    }
                }
            });
        }
    <?php endif; ?>
});
</script>

<?= $this->endSection(); ?>
