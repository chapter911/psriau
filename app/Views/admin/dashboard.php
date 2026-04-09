<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
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
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: .65rem;
    }

    .quick-link {
        border: 1px solid #e4eaf2;
        border-radius: 12px;
        background: #fff;
        color: #1f2f43;
        padding: .8rem .9rem;
        display: flex;
        align-items: center;
        gap: .55rem;
        font-weight: 600;
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
    }

    .table-sm td,
    .table-sm th {
        padding: .6rem;
        vertical-align: middle;
    }

    @media (max-width: 768px) {
        .dashboard-hero h2 {
            font-size: 1.2rem;
        }
    }
</style>

<div class="admin-dashboard">
    <div class="card dashboard-hero">
        <div class="card-body">
            <h2>Selamat datang, <?= esc((string) session()->get('fullName')); ?>.</h2>
            <p class="mb-0">Dashboard ini merangkum performa konten, status publikasi, dan akses cepat pengelolaan portal.</p>
            <div class="hero-meta">
                <span class="badge"><i class="fas fa-user-shield mr-1"></i> <?= esc((string) session()->get('role')); ?></span>
                <span class="badge"><i class="fas fa-clock mr-1"></i> <?= esc(date('d M Y H:i')); ?> WIB</span>
                <span class="badge"><i class="fas fa-globe mr-1"></i> Monitoring Konten Publik</span>
            </div>
        </div>
    </div>

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
                        <span><i class="fas fa-circle text-success mr-1"></i> Publikasi: <?= esc((string) $eventPublishedCount); ?></span>
                        <span><i class="fas fa-circle text-secondary mr-1"></i> Draft: <?= esc((string) $eventDraftCount); ?></span>
                    </div>
                    <a href="<?= site_url('/admin/acara'); ?>" class="btn btn-sm btn-outline-primary mt-3">Kelola Acara</a>
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
                    <p class="metric-label">Post Instagram yang tersimpan.</p>
                    <div class="metric-split">
                        <span><i class="fas fa-circle text-success mr-1"></i> Publikasi: <?= esc((string) $articlePublishedCount); ?></span>
                        <span><i class="fas fa-circle text-secondary mr-1"></i> Draft: <?= esc((string) $articleDraftCount); ?></span>
                    </div>
                    <a href="<?= site_url('/admin/berita'); ?>" class="btn btn-sm btn-outline-primary mt-3">Kelola Feed</a>
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
                    <p class="metric-label">Jumlah slide hero homepage.</p>
                    <div class="metric-split">
                        <span><i class="fas fa-circle text-success mr-1"></i> Aktif: <?= esc((string) $slideActiveCount); ?></span>
                        <span><i class="fas fa-circle text-secondary mr-1"></i> Nonaktif: <?= esc((string) max(0, $slideCount - $slideActiveCount)); ?></span>
                    </div>
                    <a href="<?= site_url('/admin/pengaturan-home'); ?>" class="btn btn-sm btn-outline-primary mt-3">Kelola Home</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7 col-12 mb-3">
            <div class="card panel-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Aktivitas Konten Terbaru</h3>
                    <span class="badge badge-light">Live Snapshot</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                            <tr>
                                <th>Jenis</th>
                                <th>Judul</th>
                                <th>Status</th>
                                <th>Waktu</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($latestEvents as $event): ?>
                                <tr>
                                    <td><span class="badge badge-info">Acara</span></td>
                                    <td><?= esc($event['title'] ?? '-'); ?></td>
                                    <td>
                                        <?php if ((int) ($event['is_published'] ?? 0) === 1): ?>
                                            <span class="badge badge-success">Publikasi</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Draft</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc((string) ($event['event_date'] ?? '-')); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php foreach ($latestInstagramPosts as $post): ?>
                                <tr>
                                    <td><span class="badge badge-danger">Instagram</span></td>
                                    <td><?= esc($post['title'] ?? '-'); ?></td>
                                    <td>
                                        <?php if ((int) ($post['is_published'] ?? 0) === 1): ?>
                                            <span class="badge badge-success">Publikasi</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Draft</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc((string) ($post['published_at'] ?? '-')); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($latestEvents) && empty($latestInstagramPosts)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Belum ada aktivitas konten terbaru.</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5 col-12 mb-3">
            <div class="card panel-card mb-3">
                <div class="card-header">
                    <h3 class="card-title mb-0">Aksi Cepat</h3>
                </div>
                <div class="card-body">
                    <div class="quick-grid">
                        <a class="quick-link" href="<?= site_url('/admin/acara/tambah'); ?>">
                            <i class="fas fa-plus"></i> Tambah Acara
                        </a>
                        <a class="quick-link" href="<?= site_url('/admin/berita/tambah'); ?>">
                            <i class="fab fa-instagram"></i> Tambah Feed
                        </a>
                        <a class="quick-link" href="<?= site_url('/admin/pengaturan-home'); ?>">
                            <i class="fas fa-sliders-h"></i> Atur Homepage
                        </a>
                        <a class="quick-link" href="<?= site_url('/admin/master/kop-surat'); ?>">
                            <i class="fas fa-file-signature"></i> Master Kop Surat
                        </a>
                    </div>
                </div>
            </div>

            <div class="card panel-card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Checklist Harian Admin</h3>
                </div>
                <div class="card-body">
                    <ul class="mb-0 pl-3">
                        <li class="mb-2">Verifikasi minimal 1 post Instagram sudah status publikasi.</li>
                        <li class="mb-2">Cek jadwal acara mendatang agar tanggal dan lokasi lengkap.</li>
                        <li class="mb-2">Pastikan slider homepage menampilkan konten terbaru.</li>
                        <li>Review menu dan akses pengguna sebelum akhir hari kerja.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>
<?= $this->endSection(); ?>
