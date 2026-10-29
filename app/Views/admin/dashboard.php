<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<link rel="stylesheet" href="<?= esc(media_url('assets/adminlte/plugins/chart.js/Chart.min.css')); ?>">
<link rel="stylesheet" href="<?= esc(media_url('assets/adminlte/plugins/fullcalendar/main.min.css')); ?>">
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

.metric-icon.school {
    background: linear-gradient(145deg, #7c3aed, #6d28d9);
}

.metric-icon.survey {
    background: linear-gradient(145deg, #059669, #047857);
}

.metric-icon.laporan {
    background: linear-gradient(145deg, #2563eb, #1d4ed8);
}

.metric-icon.wilayah {
    background: linear-gradient(145deg, #dc2626, #b91c1c);
}

.metric-icon.bmn {
    background: linear-gradient(145deg, #0284c7, #0369a1);
}

.metric-icon.pegawai {
    background: linear-gradient(145deg, #f59e0b, #d97706);
}

.metric-icon.paket {
    background: linear-gradient(145deg, #8b5cf6, #7c3aed);
}

/* Field Activity Photo Gallery Styles */
.field-photo-card {
    border: 1px solid #e7ecf3;
    border-radius: 12px;
    overflow: hidden;
    background: #fff;
    transition: all 0.25s ease;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    height: 100%;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
}

.field-photo-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
    border-color: #0d5aa7;
}

.field-photo-img-wrap {
    height: 145px;
    overflow: hidden;
    background: #0f172a;
    position: relative;
}

.field-photo-img-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.35s ease;
}

.field-photo-card:hover .field-photo-img-wrap img {
    transform: scale(1.06);
}

.field-photo-badge {
    position: absolute;
    bottom: 8px;
    left: 8px;
    background: rgba(15, 23, 42, 0.75);
    color: #fff;
    font-size: 0.68rem;
    font-weight: 600;
    padding: 2px 7px;
    border-radius: 6px;
    backdrop-filter: blur(4px);
}

/* Audit Feed Styles */
.audit-item {
    padding: 0.7rem 0;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.audit-item:last-child {
    border-bottom: none;
}

.audit-icon-wrap {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    flex-shrink: 0;
}

.audit-badge-add {
    background: #dcfce7;
    color: #15803d;
}

.audit-badge-edit {
    background: #e0f2fe;
    color: #0369a1;
}

.audit-badge-delete {
    background: #fee2e2;
    color: #b91c1c;
}

.room-badge {
    display: inline-flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.45rem 0.75rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.78rem;
    font-weight: 600;
    color: #334155;
    transition: all 0.2s ease;
}

.room-badge:hover {
    background: #eff6ff;
    border-color: #bfdbfe;
    color: #1d4ed8;
}

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
    min-height: 300px;
    height: auto;
    margin: 0 0 1rem 0;
}

.chart-container canvas {
    max-height: 400px;
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

/* FullCalendar Custom Styles */
.calendar-panel-card {
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.05);
    background: #ffffff;
    overflow: hidden;
}
.fc .fc-toolbar {
    flex-wrap: wrap;
    gap: 4px;
    margin-bottom: 0.5rem !important;
}
.fc .fc-toolbar-title {
    font-size: 0.95rem !important;
    font-weight: 700 !important;
    color: #1e293b !important;
}
.fc .fc-button {
    border-radius: 6px !important;
    font-weight: 600 !important;
    font-size: 0.75rem !important;
    padding: 0.22rem 0.5rem !important;
    transition: all 0.2s ease !important;
}
.fc .fc-button-primary {
    background-color: #0f4c81 !important;
    border-color: #0f4c81 !important;
}
.fc .fc-button-primary:hover {
    background-color: #0b3d68 !important;
    border-color: #0b3d68 !important;
}
.fc .fc-button-primary:not(:disabled).fc-button-active {
    background-color: #0b7f6d !important;
    border-color: #0b7f6d !important;
}
.fc-theme-standard td, .fc-theme-standard th {
    border-color: #e2e8f0 !important;
}
.fc-col-header-cell {
    background: #f8fafc !important;
    padding: 4px 0 !important;
    font-weight: 700 !important;
    color: #475569 !important;
}
.fc-col-header-cell-cushion {
    font-size: 0.75rem !important;
}
.fc-daygrid-day-number {
    font-size: 0.75rem !important;
    padding: 2px 4px !important;
    font-weight: 600 !important;
}
.fc-day-sun {
    background-color: #fff1f2 !important;
}
.fc-day-sat {
    background-color: #f0f9ff !important;
}
.fc-day-today {
    background-color: #fefce8 !important;
}
.fc-event {
    cursor: pointer !important;
    border-radius: 4px !important;
    font-size: 0.7rem !important;
    font-weight: 600 !important;
    padding: 1px 4px !important;
    margin-bottom: 1px !important;
    border-width: 1px !important;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06) !important;
    line-height: 1.25 !important;
    transition: transform 0.15s ease, box-shadow 0.15s ease !important;
}
.fc-event:hover {
    transform: scale(1.02) !important;
    box-shadow: 0 3px 6px rgba(0,0,0,0.12) !important;
    z-index: 5 !important;
}
.fc .fc-more-link {
    font-size: 0.68rem !important;
    font-weight: 700 !important;
    color: #0d5aa7 !important;
    padding: 1px 3px !important;
}
.filter-chip-checkbox {
    display: inline-flex;
    align-items: center;
    padding: 3px 8px;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 600;
    cursor: pointer;
    user-select: none;
    transition: all 0.2s ease;
    border: 1px solid transparent;
}
.filter-chip-checkbox:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}
</style>

<div class="admin-dashboard">
    <div class="card dashboard-hero">
        <div class="card-body">
            <h2>Selamat datang, <?= esc((string) session()->get('fullName')); ?>.</h2>
            <p class="mb-0">Dashboard real-time monitoring portal Satker PPS dengan data sekolah, laporan, dan konten
                publikasi.</p>
            <div class="hero-meta">
                <span class="badge"><i class="fas fa-user-shield mr-1"></i>
                    <?= esc((string) session()->get('role')); ?></span>
                <span class="badge"><i class="fas fa-clock mr-1"></i> <?= esc(date('d M Y H:i')); ?> WIB</span>
                <span class="badge"><i class="fas fa-database mr-1"></i> <?= esc((string) $schoolCount); ?> Sekolah
                    Terdaftar</span>
            </div>
        </div>
    </div>

    <!-- Executive KPI Summary Cards Bar (5 Cards Grid) -->
    <div class="row mt-1 mb-2">
        <!-- 1. Aset BMN Satker -->
        <div class="col-xl-2 col-lg-4 col-md-6 col-12 mb-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="metric-top">
                        <strong class="text-truncate">Aset BMN</strong>
                        <span class="metric-icon bmn"><i class="fas fa-boxes-stacked"></i></span>
                    </div>
                    <div class="metric-value text-info"><?= number_format((int) ($inventarisStats['total'] ?? 0)); ?></div>
                    <p class="metric-label mb-2">Barang Milik Negara</p>
                    <div class="metric-split flex-column" style="gap: 3px;">
                        <span class="small py-1 px-2" style="background: #e0f2fe; color: #0369a1;">
                            <i class="fas fa-check-circle text-success mr-1"></i> <?= number_format((int) ($inventarisStats['baik'] ?? 0)); ?> Baik
                        </span>
                        <span class="small py-1 px-2" style="background: #fef3c7; color: #b45309;">
                            <i class="fas fa-hand-holding text-warning mr-1"></i> <?= number_format((int) ($inventarisStats['dipinjam'] ?? 0)); ?> Dipinjam
                        </span>
                    </div>
                    <a href="<?= site_url('/admin/inventaris/barang'); ?>" class="btn btn-xs btn-outline-info btn-block mt-3 font-weight-bold" style="border-radius: 6px;">Kelola Aset</a>
                </div>
            </div>
        </div>

        <!-- 2. Pegawai & SDM Satker -->
        <div class="col-xl-2 col-lg-4 col-md-6 col-12 mb-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="metric-top">
                        <strong class="text-truncate">Pegawai Satker</strong>
                        <span class="metric-icon pegawai"><i class="fas fa-users"></i></span>
                    </div>
                    <div class="metric-value text-warning"><?= number_format((int) ($pegawaiStats['total'] ?? 0)); ?></div>
                    <p class="metric-label mb-2">Personel Aktif</p>
                    <div class="metric-split flex-column" style="gap: 3px;">
                        <span class="small py-1 px-2">
                            <i class="fas fa-id-badge text-primary mr-1"></i> <?= (int) ($pegawaiStats['pns'] ?? 0); ?> PNS | <?= (int) ($pegawaiStats['pppk'] ?? 0); ?> PPPK
                        </span>
                        <span class="small py-1 px-2">
                            <i class="fas fa-user-tie text-secondary mr-1"></i> <?= (int) ($pegawaiStats['lainnya'] ?? 0); ?> Non-ASN
                        </span>
                    </div>
                    <a href="<?= site_url('/admin/master/pegawai'); ?>" class="btn btn-xs btn-outline-warning text-dark btn-block mt-3 font-weight-bold" style="border-radius: 6px;">Data Pegawai</a>
                </div>
            </div>
        </div>

        <!-- 3. Prasarana Sekolah -->
        <div class="col-xl-3 col-lg-4 col-md-6 col-12 mb-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="metric-top">
                        <strong class="text-truncate">Prasarana Sekolah</strong>
                        <span class="metric-icon school"><i class="fas fa-school"></i></span>
                    </div>
                    <div class="metric-value text-purple" style="color: #7c3aed;"><?= number_format((int) $schoolCount); ?></div>
                    <p class="metric-label mb-2">Sekolah Terdaftar</p>
                    <div class="metric-split flex-column" style="gap: 3px;">
                        <span class="small py-1 px-2" style="background: #ede9fe; color: #6d28d9;">
                            <i class="fas fa-clipboard-check text-success mr-1"></i> <?= number_format((int) $schoolWithSurvey); ?> Telah Disurvei
                            <strong class="ml-1">(<?= $schoolCount > 0 ? round(($schoolWithSurvey / $schoolCount) * 100, 1) : 0; ?>%)</strong>
                        </span>
                    </div>
                    <a href="<?= site_url('/admin/master/sekolah'); ?>" class="btn btn-xs btn-outline-primary btn-block mt-3 font-weight-bold" style="border-radius: 6px;">Kelola Sekolah</a>
                </div>
            </div>
        </div>

        <!-- 4. Laporan Lapangan -->
        <div class="col-xl-2 col-lg-6 col-md-6 col-12 mb-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="metric-top">
                        <strong class="text-truncate">Laporan Lapangan</strong>
                        <span class="metric-icon laporan"><i class="fas fa-file-invoice"></i></span>
                    </div>
                    <div class="metric-value text-primary"><?= number_format((int) ($harianReportCount + $mingguanReportCount)); ?></div>
                    <p class="metric-label mb-2">Laporan Pekerjaan</p>
                    <div class="metric-split flex-column" style="gap: 3px;">
                        <span class="small py-1 px-2">
                            <i class="fas fa-calendar-day text-info mr-1"></i> <?= number_format((int) $harianReportCount); ?> Harian
                        </span>
                        <span class="small py-1 px-2">
                            <i class="fas fa-calendar-week text-success mr-1"></i> <?= number_format((int) $mingguanReportCount); ?> Mingguan
                        </span>
                    </div>
                    <a href="<?= site_url('/admin/laporan/harian'); ?>" class="btn btn-xs btn-outline-primary btn-block mt-3 font-weight-bold" style="border-radius: 6px;">Lihat Laporan</a>
                </div>
            </div>
        </div>

        <!-- 5. Paket & Kontrak -->
        <div class="col-xl-3 col-lg-6 col-md-12 col-12 mb-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="metric-top">
                        <strong class="text-truncate">Paket & Pekerjaan</strong>
                        <span class="metric-icon paket"><i class="fas fa-briefcase"></i></span>
                    </div>
                    <div class="metric-value" style="color: #6366f1;"><?= number_format((int) ($paketStats['total_paket'] ?? 0)); ?></div>
                    <p class="metric-label mb-2">Paket Terdaftar</p>
                    <div class="metric-split flex-column" style="gap: 3px;">
                        <span class="small py-1 px-2">
                            <i class="fas fa-award text-success mr-1"></i> <?= number_format((int) ($paketStats['total_bast'] ?? 0)); ?> BAST
                            <span class="mx-1">|</span>
                            <i class="fas fa-handshake text-info mr-1"></i> <?= number_format((int) ($paketStats['total_swakelola'] ?? 0)); ?> Swakelola
                        </span>
                        <span class="small py-1 px-2">
                            <i class="fas fa-camera text-primary mr-1"></i> <?= number_format((int) ($paketStats['total_foto'] ?? 0)); ?> Foto Lapangan
                        </span>
                    </div>
                    <a href="<?= site_url('/admin/kontrak/paket'); ?>" class="btn btn-xs btn-outline-secondary btn-block mt-3 font-weight-bold" style="border-radius: 6px;">Daftar Paket</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Row: Calendar (col-4) & SIMAK Documentation (col-8) -->
    <div class="row">
        <!-- Col 4: Kalender Terpadu & Jadwal Kegiatan -->
        <div class="col-lg-4 col-12 mb-3">
            <div class="card calendar-panel-card h-100 mb-0">
                <div class="card-header bg-white py-2 px-3 d-flex flex-wrap justify-content-between align-items-center" style="border-bottom: 1px solid #e9eef5; gap: 8px;">
                    <div>
                        <h6 class="mb-0 font-weight-bold text-dark d-flex align-items-center" style="font-size: 0.95rem;">
                            <i class="fas fa-calendar-days text-primary mr-2"></i> Kalender Kegiatan
                        </h6>
                        <small class="text-muted" style="font-size: 0.72rem;">Libur, Cuti & Perjadin</small>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-xs btn-outline-secondary dropdown-toggle font-weight-bold" type="button" data-toggle="dropdown" aria-expanded="false" style="border-radius: 6px; font-size: 0.75rem;">
                            <i class="fas fa-cog mr-1"></i> Kelola
                        </button>
                        <div class="dropdown-menu dropdown-menu-right shadow-sm border-0">
                            <a class="dropdown-item small text-danger font-weight-bold" href="<?= site_url('/admin/master/tanggal-merah'); ?>">
                                <i class="fas fa-calendar-alt mr-2 text-danger"></i> Master Tanggal Merah
                            </a>
                            <a class="dropdown-item small text-info font-weight-bold" href="<?= site_url('/admin/surat/cuti'); ?>">
                                <i class="fas fa-umbrella-beach mr-2 text-info"></i> Surat Cuti Pegawai
                            </a>
                            <a class="dropdown-item small text-success font-weight-bold" href="<?= site_url('/admin/surat/perjalanan-dinas/disposisi'); ?>">
                                <i class="fas fa-plane-departure mr-2 text-success"></i> Disposisi Perjadin
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body p-2">
                    <!-- Filter Chips -->
                    <div class="d-flex flex-wrap align-items-center mb-2" style="gap: 4px;">
                        <label class="filter-chip-checkbox" style="background-color: #fee2e2; color: #b91c1c; border-color: #fca5a5;">
                            <input type="checkbox" class="mr-1 js-cal-filter" data-type="libur_nasional" checked>
                            <i class="fas fa-flag mr-1"></i> Libur
                        </label>

                        <label class="filter-chip-checkbox" style="background-color: #fef3c7; color: #b45309; border-color: #fcd34d;">
                            <input type="checkbox" class="mr-1 js-cal-filter" data-type="cuti_bersama" checked>
                            <i class="fas fa-umbrella-beach mr-1"></i> C. Bersama
                        </label>

                        <label class="filter-chip-checkbox" style="background-color: #e0f2fe; color: #0369a1; border-color: #7dd3fc;">
                            <input type="checkbox" class="mr-1 js-cal-filter" data-type="pegawai_cuti" checked>
                            <i class="fas fa-user-clock mr-1"></i> Cuti
                        </label>

                        <label class="filter-chip-checkbox" style="background-color: #d1fae5; color: #047857; border-color: #6ee7b7;">
                            <input type="checkbox" class="mr-1 js-cal-filter" data-type="perjalanan_dinas" checked>
                            <i class="fas fa-plane-departure mr-1"></i> Perjadin
                        </label>
                    </div>

                    <!-- Mini Summary Bar -->
                    <div class="d-flex justify-content-between align-items-center bg-light px-2 py-1 rounded border mb-2" style="font-size: 0.75rem;">
                        <div>
                            <span class="text-muted">Cuti Hari Ini:</span>
                            <strong class="text-info ml-1"><?= esc((string) ($calendarStats['active_cutis_today'] ?? 0)); ?> Orang</strong>
                        </div>
                        <div class="border-left pl-2">
                            <span class="text-muted">Perjadin Hari Ini:</span>
                            <strong class="text-success ml-1"><?= esc((string) ($calendarStats['active_perjadin_today'] ?? 0)); ?> Orang</strong>
                        </div>
                    </div>

                    <!-- FullCalendar Mount Element (Compact Col-4) -->
                    <div id="dashboardFullCalendar"></div>
                </div>
            </div>
        </div>

        <!-- Col 8: SIMAK Dokumentasi Charts -->
        <div class="col-lg-8 col-12 mb-3">
            <div class="card panel-card h-100 mb-0">
                <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid #e9eef5;">
                    <h6 class="mb-0 font-weight-bold" style="color: #1f2f43; font-size: 0.95rem;">
                        <i class="fas fa-folder-open text-primary mr-2"></i> Dokumentasi SIMAK
                    </h6>
                    <span class="badge badge-light border text-muted" style="font-size: 0.72rem;">Monitoring Kelengkapan Berkas</span>
                </div>
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-md-6 col-12 mb-3 mb-md-0">
                            <div class="card h-100 border shadow-none" style="border-radius: 12px; background: #fafbfc;">
                                <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid #eef2f6;">
                                    <h6 class="card-title font-weight-bold mb-0" style="font-size: 0.88rem;">🏗️ Konstruksi</h6>
                                    <a href="<?= site_url('/admin/kontrak/simak/konstruksi'); ?>" class="btn btn-xs btn-primary font-weight-bold" style="border-radius: 6px; font-size: 0.72rem;">Detail</a>
                                </div>
                                <div class="card-body p-2">
                                    <?php if (!empty($konstruksiChartData['labels'])): ?>
                                    <div class="chart-container" style="min-height: 240px; height: 260px;">
                                        <canvas id="konstruksiChart"></canvas>
                                    </div>
                                    <?php else: ?>
                                    <div class="alert alert-info mb-0 small">
                                        <i class="fas fa-info-circle mr-1"></i> Belum ada data kelengkapan dokumen konstruksi.
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="card h-100 border shadow-none" style="border-radius: 12px; background: #fafbfc;">
                                <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid #eef2f6;">
                                    <h6 class="card-title font-weight-bold mb-0" style="font-size: 0.88rem;">📋 Konsultasi</h6>
                                    <a href="<?= site_url('/admin/kontrak/simak/konsultasi'); ?>" class="btn btn-xs btn-primary font-weight-bold" style="border-radius: 6px; font-size: 0.72rem;">Detail</a>
                                </div>
                                <div class="card-body p-2">
                                    <?php if (!empty($konsultasiChartData['labels'])): ?>
                                    <div class="chart-container" style="min-height: 240px; height: 260px;">
                                        <canvas id="konsultasiChart"></canvas>
                                    </div>
                                    <?php else: ?>
                                    <div class="alert alert-info mb-0 small">
                                        <i class="fas fa-info-circle mr-1"></i> Belum ada data kelengkapan dokumen konsultasi.
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Inventarisasi Aset BMN (Col-6) & Klasifikasi Kerusakan Sekolah (Col-6) -->
    <div class="row mt-2">
        <!-- Col 6: Ringkasan Aset BMN & Pinjam Pakai -->
        <div class="col-lg-6 col-12 mb-3">
            <div class="card panel-card h-100">
                <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid #e9eef5;">
                    <h6 class="mb-0 font-weight-bold text-dark d-flex align-items-center" style="font-size: 0.95rem;">
                        <i class="fas fa-boxes-stacked text-info mr-2"></i> Inventarisasi & Aset BMN Satker
                    </h6>
                    <a href="<?= site_url('/admin/inventaris/barang'); ?>" class="btn btn-xs btn-outline-info font-weight-bold" style="border-radius: 6px; font-size: 0.72rem;">
                        <i class="fas fa-external-link-alt mr-1"></i> Detail Aset
                    </a>
                </div>
                <div class="card-body p-3">
                    <!-- Top Mini Summary Pills -->
                    <div class="row mb-3 text-center">
                        <div class="col-4">
                            <div class="p-2 rounded border" style="background: #f0f9ff;">
                                <div class="font-weight-bold text-primary" style="font-size: 1.3rem;">
                                    <?= number_format((int) ($inventarisStats['total'] ?? 0)); ?>
                                </div>
                                <small class="text-muted font-weight-bold" style="font-size: 0.72rem;">Total Aset BMN</small>
                                <div><span class="badge badge-success font-weight-bold" style="font-size: 0.65rem;">100% Kondisi Baik</span></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 rounded border" style="background: #fefce8;">
                                <div class="font-weight-bold text-warning" style="font-size: 1.3rem;">
                                    <?= number_format((int) ($inventarisStats['dipinjam'] ?? 0)); ?>
                                </div>
                                <small class="text-muted font-weight-bold" style="font-size: 0.72rem;">Sedang Dipinjam</small>
                                <div><span class="badge badge-warning text-dark font-weight-bold" style="font-size: 0.65rem;">Pinjam Pakai Aktif</span></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 rounded border" style="background: #f0fdf4;">
                                <div class="font-weight-bold text-success" style="font-size: 1.3rem;">
                                    <?= number_format((int) ($inventarisStats['diperbaharui'] ?? 0)); ?>
                                </div>
                                <small class="text-muted font-weight-bold" style="font-size: 0.72rem;">Diperbaharui</small>
                                <div><span class="badge badge-light border text-muted font-weight-bold" style="font-size: 0.65rem;">Perpanjangan</span></div>
                            </div>
                        </div>
                    </div>

                    <!-- Kategori Aset Distribution -->
                    <h6 class="font-weight-bold text-dark mb-2" style="font-size: 0.82rem;">
                        <i class="fas fa-chart-pie text-secondary mr-1"></i> Sebaran Kategori Aset Utama
                    </h6>
                    <?php 
                        $totalBmn = (int) ($inventarisStats['total'] ?? 0);
                        if (!empty($inventarisStats['kategori'])):
                            foreach ($inventarisStats['kategori'] as $kat):
                                $pct = $totalBmn > 0 ? round((int)$kat['c'] / $totalBmn * 100, 1) : 0;
                    ?>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center small font-weight-bold mb-1">
                            <span class="text-truncate" style="max-width: 75%; font-size: 0.78rem;"><?= esc((string) $kat['kat']); ?></span>
                            <span class="text-muted" style="font-size: 0.75rem;"><?= number_format((int) $kat['c']); ?> item (<?= $pct; ?>%)</span>
                        </div>
                        <div class="progress" style="height: 6px; background: #e2e8f0; border-radius: 999px;">
                            <div class="progress-bar" role="progressbar" style="width: <?= $pct; ?>%; background: linear-gradient(90deg, #0284c7, #38bdf8); border-radius: 999px;"></div>
                        </div>
                    </div>
                    <?php endforeach; else: ?>
                    <p class="text-muted small">Data kategori belum tersedia.</p>
                    <?php endif; ?>

                    <!-- Ruangan Penempatan Aset -->
                    <h6 class="font-weight-bold text-dark mt-3 mb-2" style="font-size: 0.82rem;">
                        <i class="fas fa-door-open text-secondary mr-1"></i> Distribusi Aset per Ruangan
                    </h6>
                    <div class="d-flex flex-wrap" style="gap: 6px;">
                        <?php if (!empty($inventarisStats['ruangan'])): ?>
                            <?php foreach ($inventarisStats['ruangan'] as $ruang): ?>
                                <span class="room-badge">
                                    <span><?= esc((string) $ruang['nama_ruangan']); ?></span>
                                    <span class="badge badge-primary ml-2"><?= number_format((int) $ruang['c']); ?></span>
                                </span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="text-muted small">Data ruangan belum tersedia.</span>
                        <?php endif; ?>
                    </div>

                    <!-- Action Footer -->
                    <div class="mt-3 pt-2 border-top d-flex flex-wrap justify-content-between align-items-center" style="gap: 6px;">
                        <a href="<?= site_url('/admin/inventaris/pinjam-pakai'); ?>" class="btn btn-xs btn-outline-warning text-dark font-weight-bold" style="border-radius: 6px;">
                            <i class="fas fa-hand-holding mr-1"></i> Data Pinjam Pakai
                        </a>
                        <a href="<?= site_url('/admin/inventaris/dbr'); ?>" class="btn btn-xs btn-outline-secondary font-weight-bold" style="border-radius: 6px;">
                            <i class="fas fa-th-list mr-1"></i> Inventaris DBR Ruangan
                        </a>
                        <a href="<?= site_url('/admin/inventaris/pemeliharaan'); ?>" class="btn btn-xs btn-outline-success font-weight-bold" style="border-radius: 6px;">
                            <i class="fas fa-wrench mr-1"></i> Pemeliharaan Aset
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Col 6: Klasifikasi Kerusakan Sekolah -->
        <div class="col-lg-6 col-12 mb-3">
            <div class="card panel-card h-100">
                <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid #e9eef5;">
                    <h6 class="mb-0 font-weight-bold text-dark d-flex align-items-center" style="font-size: 0.95rem;">
                        <i class="fas fa-chart-pie text-danger mr-2"></i> Klasifikasi Kerusakan Sekolah
                    </h6>
                    <a href="<?= site_url('/admin/master/sekolah'); ?>" class="btn btn-xs btn-outline-primary font-weight-bold" style="border-radius: 6px; font-size: 0.72rem;">
                        <i class="fas fa-school mr-1"></i> Data Sekolah
                    </a>
                </div>
                <div class="card-body p-3">
                    <?php if (!empty($damageClassification)): ?>
                    <div class="chart-container" style="min-height: 200px; height: 210px; margin-bottom: 0.75rem;">
                        <canvas id="damageChart"></canvas>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0" style="font-size: 0.8rem;">
                            <thead class="bg-light">
                                <tr>
                                    <th>Klasifikasi Kerusakan</th>
                                    <th class="text-center">Jumlah</th>
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
                                    <td class="text-center font-weight-bold text-primary"><?= esc((string) $item['count']); ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 mr-2" style="height: 6px; background: #e8edf4; border-radius: 999px;">
                                                <div class="progress-bar" role="progressbar"
                                                    style="width: <?= esc((string) $percentage); ?>%; background: linear-gradient(90deg, #0d5aa7, #2563eb); border-radius: 999px;">
                                                </div>
                                            </div>
                                            <small class="font-weight-bold text-muted"><?= esc((string) $percentage); ?>%</small>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info mb-0 small">
                        <i class="fas fa-info-circle mr-2"></i> Belum ada data klasifikasi kerusakan sekolah.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 3: Galeri Dokumentasi Kegiatan Lapangan Terkini (Col-12) -->
    <div class="row mt-2">
        <div class="col-12 mb-3">
            <div class="card panel-card">
                <div class="card-header bg-white py-2 px-3 d-flex flex-wrap justify-content-between align-items-center" style="border-bottom: 1px solid #e9eef5; gap: 8px;">
                    <div>
                        <h6 class="mb-0 font-weight-bold text-dark d-flex align-items-center" style="font-size: 0.95rem;">
                            <i class="fas fa-camera text-primary mr-2"></i> Dokumentasi Kegiatan Lapangan Terkini
                        </h6>
                        <small class="text-muted" style="font-size: 0.72rem;">Dokumentasi foto kegiatan lapangan real-time dari pelaksana proyek Satker PPS</small>
                    </div>
                    <div>
                        <a href="<?= site_url('/admin/dokumentasi/kegiatan-lapangan'); ?>" class="btn btn-xs btn-primary font-weight-bold" style="border-radius: 6px; font-size: 0.75rem;">
                            <i class="fas fa-images mr-1"></i> Buka Semua Galeri
                        </a>
                    </div>
                </div>
                <div class="card-body p-3">
                    <?php if (!empty($latestFieldPhotos)): ?>
                    <div class="row">
                        <?php foreach ($latestFieldPhotos as $photo): ?>
                            <?php 
                                $photoUrl = base_url((string) ($photo['photo_path'] ?? ''));
                                $activityDate = !empty($photo['activity_date']) ? date('d M Y', strtotime($photo['activity_date'])) : '-';
                                $photoTitle = (string) ($photo['title'] ?? 'Kegiatan Lapangan');
                                $locationText = (string) ($photo['location'] ?? 'Wilayah Satker PPS Riau');
                            ?>
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 mb-3">
                                <div class="field-photo-card js-photo-preview" 
                                     data-src="<?= esc($photoUrl); ?>" 
                                     data-title="<?= esc($photoTitle); ?>" 
                                     data-meta="<?= esc($activityDate . ' — ' . $locationText); ?>">
                                    <div class="field-photo-img-wrap">
                                        <img src="<?= esc($photoUrl); ?>" alt="<?= esc($photoTitle); ?>" loading="lazy">
                                        <span class="field-photo-badge">
                                            <i class="far fa-calendar-alt mr-1"></i> <?= esc($activityDate); ?>
                                        </span>
                                    </div>
                                    <div class="p-2 flex-grow-1 d-flex flex-column justify-content-between bg-white">
                                        <h6 class="font-weight-bold text-dark text-truncate mb-1" style="font-size: 0.8rem;" title="<?= esc($photoTitle); ?>">
                                            <?= esc($photoTitle); ?>
                                        </h6>
                                        <small class="text-muted text-truncate d-block" style="font-size: 0.72rem;" title="<?= esc($locationText); ?>">
                                            <i class="fas fa-map-marker-alt text-danger mr-1"></i> <?= esc($locationText); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-light border text-center py-3 mb-0">
                        <i class="fas fa-images text-muted mr-1"></i> Belum ada foto dokumentasi lapangan yang diunggah.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 4: Log Aktivitas Sistem Terkini (Col-7) & Aksi Cepat (Col-5) -->
    <div class="row mt-2">
        <!-- Col 7: Log Aktivitas Sistem Terkini -->
        <div class="col-lg-7 col-12 mb-3">
            <div class="card panel-card h-100">
                <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid #e9eef5;">
                    <div>
                        <h6 class="mb-0 font-weight-bold text-dark d-flex align-items-center" style="font-size: 0.95rem;">
                            <i class="fas fa-shield-alt text-success mr-2"></i> Log Aktivitas Pengguna Terkini
                        </h6>
                        <small class="text-muted" style="font-size: 0.72rem;">Catatan riwayat aksi penambahan, perubahan & penghapusan data</small>
                    </div>
                    <a href="<?= site_url('/admin/history/edit'); ?>" class="btn btn-xs btn-outline-secondary font-weight-bold" style="border-radius: 6px; font-size: 0.72rem;">
                        <i class="fas fa-history mr-1"></i> Riwayat Lengkap
                    </a>
                </div>
                <div class="card-body p-3">
                    <?php if (!empty($recentAuditLogs)): ?>
                        <div class="audit-feed">
                            <?php foreach ($recentAuditLogs as $audit): ?>
                                <?php 
                                    $actionType = strtolower(trim((string) ($audit['action_type'] ?? '')));
                                    $iconClass = 'fas fa-pen';
                                    $badgeClass = 'audit-badge-edit';
                                    $badgeLabel = 'Ubah';
                                    if ($actionType === 'add' || $actionType === 'create') {
                                        $iconClass = 'fas fa-plus';
                                        $badgeClass = 'audit-badge-add';
                                        $badgeLabel = 'Tambah';
                                    } elseif ($actionType === 'delete' || $actionType === 'hapus') {
                                        $iconClass = 'fas fa-trash';
                                        $badgeClass = 'audit-badge-delete';
                                        $badgeLabel = 'Hapus';
                                    }
                                    $timeStr = !empty($audit['happened_at']) ? date('d M Y H:i', strtotime($audit['happened_at'])) : date('d M Y H:i', strtotime($audit['created_at'] ?? 'now'));
                                ?>
                                <div class="audit-item">
                                    <div class="audit-icon-wrap <?= $badgeClass; ?>">
                                        <i class="<?= $iconClass; ?>"></i>
                                    </div>
                                    <div class="flex-grow-1" style="min-width: 0;">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <div class="text-truncate mr-2">
                                                <strong class="text-dark" style="font-size: 0.82rem;"><?= esc((string) ($audit['username'] ?? 'User')); ?></strong>
                                                <span class="badge badge-light border text-muted ml-1" style="font-size: 0.66rem;"><?= esc((string) ($audit['role'] ?? '-')); ?></span>
                                            </div>
                                            <span class="text-muted small flex-shrink-0" style="font-size: 0.7rem;">
                                                <i class="far fa-clock mr-1"></i> <?= esc($timeStr); ?> WIB
                                            </span>
                                        </div>
                                        <div class="small text-muted text-truncate" style="font-size: 0.75rem;">
                                            <span class="badge <?= $badgeClass; ?> px-1 py-0 mr-1 font-weight-bold" style="font-size: 0.65rem;"><?= $badgeLabel; ?></span>
                                            <span class="text-monospace">/<?= esc((string) ($audit['module_path'] ?? '-')); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-light border text-center py-3 mb-0 small">
                            <i class="fas fa-info-circle text-muted mr-1"></i> Belum ada rekaman riwayat audit aktivitas sistem.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Col 5: Aksi Cepat & Pintasan Menu -->
        <div class="col-lg-5 col-12 mb-3">
            <div class="card panel-card h-100">
                <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid #e9eef5;">
                    <h6 class="mb-0 font-weight-bold text-dark d-flex align-items-center" style="font-size: 0.95rem;">
                        <i class="fas fa-bolt text-warning mr-2"></i> Aksi Cepat & Pintasan
                    </h6>
                    <span class="badge badge-light border text-muted" style="font-size: 0.72rem;">Operasional Satker</span>
                </div>
                <div class="card-body p-3">
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
                        <a class="quick-link" href="<?= site_url('/admin/inventaris/barang'); ?>">
                            <i class="fas fa-boxes-stacked"></i> Aset BMN
                        </a>
                        <a class="quick-link" href="<?= site_url('/admin/inventaris/pinjam-pakai'); ?>">
                            <i class="fas fa-hand-holding"></i> Pinjam Pakai
                        </a>
                        <a class="quick-link" href="<?= site_url('/admin/kontrak/simak/konstruksi'); ?>">
                            <i class="fas fa-folder-open"></i> SIMAK Berkas
                        </a>
                        <a class="quick-link" href="<?= site_url('/admin/surat/cuti'); ?>">
                            <i class="fas fa-umbrella-beach"></i> Cuti Pegawai
                        </a>
                    </div>

                    <!-- Checklist Harian Ringkas -->
                    <div class="mt-3 pt-2 border-top">
                        <h6 class="font-weight-bold text-dark mb-2" style="font-size: 0.8rem;">
                            <i class="fas fa-clipboard-check text-success mr-1"></i> Standar Operasional Harian
                        </h6>
                        <ul class="mb-0 pl-3 small text-muted" style="font-size: 0.75rem; line-height: 1.6;">
                            <li>Verifikasi status publikasi acara & postingan media sosial satker.</li>
                            <li>Pantau kelengkapan laporan harian dan data hasil survei sekolah.</li>
                            <li>Tinjau peminjaman aset BMN dan kelengkapan dokumen SIMAK.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="<?= esc(media_url('assets/adminlte/plugins/chart.js/Chart.min.js')); ?>"></script>
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
                            font: {
                                size: 12,
                                weight: '600'
                            }
                        }
                    }
                }
            }
        });
    }
    <?php endif; ?>

    <?php if (!empty($konstruksiChartData['labels'])): ?>
    // Konstruksi Bar Chart (Percentage)
    const konstruksiData = <?= json_encode([
            'labels' => $konstruksiChartData['labels'] ?? [],
            'ada' => $konstruksiChartData['ada'] ?? [],
            'tidak_ada' => $konstruksiChartData['tidak_ada'] ?? [],
        ]); ?>;

    const ctxKonstruksi = document.getElementById('konstruksiChart');
    if (ctxKonstruksi) {
        new Chart(ctxKonstruksi, {
            type: 'bar',
            data: {
                labels: konstruksiData.labels,
                datasets: [{
                        label: 'Ada',
                        data: konstruksiData.ada,
                        backgroundColor: '#22c55e',
                        borderColor: '#16a34a',
                        borderWidth: 1
                    },
                    {
                        label: 'Tidak Ada',
                        data: konstruksiData.tidak_ada,
                        backgroundColor: '#ef4444',
                        borderColor: '#dc2626',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 0,
                            font: {
                                size: 11
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            stepSize: 10,
                            font: {
                                size: 11
                            },
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        title: {
                            display: true,
                            text: 'Persentase (%)',
                            font: {
                                size: 12,
                                weight: '600'
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: {
                                size: 12,
                                weight: '600'
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y + '%';
                            }
                        }
                    }
                }
            }
        });
    }
    <?php endif; ?>

    <?php if (!empty($konsultasiChartData['labels'])): ?>
    // Konsultasi Bar Chart
    const konsultasiData = <?= json_encode([
            'labels' => $konsultasiChartData['labels'] ?? [],
            'ada' => $konsultasiChartData['ada'] ?? [],
            'tidak_ada' => $konsultasiChartData['tidak_ada'] ?? [],
        ]); ?>;

    const ctxKonsultasi = document.getElementById('konsultasiChart');
    if (ctxKonsultasi) {
        new Chart(ctxKonsultasi, {
            type: 'bar',
            data: {
                labels: konsultasiData.labels,
                datasets: [{
                        label: 'Ada',
                        data: konsultasiData.ada,
                        backgroundColor: '#22c55e',
                        borderColor: '#16a34a',
                        borderWidth: 1
                    },
                    {
                        label: 'Tidak Ada',
                        data: konsultasiData.tidak_ada,
                        backgroundColor: '#ef4444',
                        borderColor: '#dc2626',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 0,
                            font: {
                                size: 11
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            stepSize: 10,
                            font: {
                                size: 11
                            },
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        title: {
                            display: true,
                            text: 'Persentase (%)',
                            font: {
                                size: 12,
                                weight: '600'
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: {
                                size: 12,
                                weight: '600'
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y + '%';
                            }
                        }
                    }
                }
            }
        });
    }
    <?php endif; ?>

    // =========================================================
    // FULLCALENDAR INITIALIZATION (Holidays, Leaves, Perjadin)
    // =========================================================
    const calendarEl = document.getElementById('dashboardFullCalendar');
    const rawCalendarEvents = <?= json_encode($calendarEvents ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

    if (calendarEl && typeof FullCalendar !== 'undefined') {
        let activeFilters = {
            libur_nasional: true,
            cuti_bersama: true,
            pegawai_cuti: true,
            perjalanan_dinas: true
        };

        function getFilteredEvents() {
            return rawCalendarEvents.filter(ev => {
                const type = (ev.extendedProps && ev.extendedProps.eventType) ? ev.extendedProps.eventType : '';
                return activeFilters[type] !== false;
            });
        }

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,listMonth'
            },
            locale: 'id',
            buttonText: {
                today: 'Hari Ini',
                month: 'Bulan',
                list: 'Agenda'
            },
            events: getFilteredEvents(),
            dayMaxEvents: 1,
            navLinks: true,
            contentHeight: 460,
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                meridiem: false
            },
            eventClick: function(info) {
                const ev = info.event;
                const props = ev.extendedProps || {};
                const type = props.eventType || '';

                const modalHeader = document.getElementById('modalEventHeader');
                const modalCategory = document.getElementById('modalEventCategory');
                const modalBody = document.getElementById('modalEventBody');
                const modalFooter = document.getElementById('modalEventFooter');

                let headerBg = '#0f4c81';
                let icon = 'fas fa-calendar';
                let categoryLabel = props.categoryLabel || 'Agenda';
                let linkUrl = '';
                let linkText = '';

                if (type === 'libur_nasional') {
                    headerBg = '#dc3545';
                    icon = 'fas fa-flag';
                    linkUrl = '<?= site_url('/admin/master/tanggal-merah'); ?>';
                    linkText = 'Buka Master Tanggal Merah';
                } else if (type === 'cuti_bersama') {
                    headerBg = '#ff9800';
                    icon = 'fas fa-umbrella-beach';
                    linkUrl = '<?= site_url('/admin/master/tanggal-merah'); ?>';
                    linkText = 'Buka Master Tanggal Merah';
                } else if (type === 'pegawai_cuti') {
                    headerBg = '#0284c7';
                    icon = 'fas fa-user-clock';
                    linkUrl = '<?= site_url('/admin/surat/cuti'); ?>';
                    linkText = 'Buka Pengajuan Cuti';
                } else if (type === 'perjalanan_dinas') {
                    headerBg = '#10b981';
                    icon = 'fas fa-plane-departure';
                    linkUrl = '<?= site_url('/admin/surat/perjalanan-dinas/disposisi'); ?>';
                    linkText = 'Buka Disposisi Perjadin';
                }

                if (modalHeader) modalHeader.style.backgroundColor = headerBg;
                if (modalCategory) modalCategory.innerHTML = `<i class="${icon} mr-1"></i> ${categoryLabel}`;

                let bodyHtml = `
                    <div class="mb-3">
                        <span class="badge ${props.badgeColor ? 'badge-' + props.badgeColor : 'badge-primary'} px-2 py-1 mb-2 font-weight-bold">
                            ${categoryLabel}
                        </span>
                        <h5 class="font-weight-bold text-dark mb-1">${ev.title}</h5>
                    </div>
                `;

                if (type === 'libur_nasional' || type === 'cuti_bersama') {
                    bodyHtml += `
                        <div class="p-3 bg-light rounded border mb-2" style="font-size: 0.9rem;">
                            <div class="mb-1"><strong><i class="far fa-calendar text-danger mr-1"></i> Tanggal:</strong> ${props.tanggal || ev.startStr} (${props.hari || '-'})</div>
                            <div><strong><i class="fas fa-database text-primary mr-1"></i> Sumber Data:</strong> ${props.sumber || 'API'}</div>
                        </div>
                    `;
                } else if (type === 'pegawai_cuti') {
                    bodyHtml += `
                        <div class="p-3 bg-light rounded border mb-2" style="font-size: 0.9rem;">
                            <div class="mb-1"><strong><i class="fas fa-user text-info mr-1"></i> Nama Pegawai:</strong> ${props.nama || '-'}</div>
                            <div class="mb-1"><strong><i class="fas fa-id-badge text-secondary mr-1"></i> NIP:</strong> ${props.nip || '-'}</div>
                            <div class="mb-1"><strong><i class="fas fa-briefcase text-secondary mr-1"></i> Jabatan / Unit:</strong> ${props.jabatan || '-'} (${props.unit_kerja || '-'})</div>
                            <div class="mb-1"><strong><i class="fas fa-umbrella-beach text-warning mr-1"></i> Jenis Cuti:</strong> <span class="badge badge-info">${props.jenis_cuti || '-'}</span></div>
                            <div class="mb-1"><strong><i class="far fa-calendar-alt text-primary mr-1"></i> Periode:</strong> ${props.periode || '-'} (${props.lama || '-'})</div>
                            <div class="mb-1"><strong><i class="fas fa-comment-dots text-secondary mr-1"></i> Alasan Cuti:</strong> ${props.alasan || '-'}</div>
                            <div><strong><i class="fas fa-info-circle text-primary mr-1"></i> Status:</strong> <span class="badge badge-secondary">${props.status || 'Diajukan'}</span></div>
                        </div>
                    `;
                } else if (type === 'perjalanan_dinas') {
                    bodyHtml += `
                        <div class="p-3 bg-light rounded border mb-2" style="font-size: 0.9rem;">
                            <div class="mb-1"><strong><i class="fas fa-location-dot text-danger mr-1"></i> Kota Tujuan:</strong> <span class="badge badge-success font-weight-bold">${props.kota_tujuan || '-'}</span></div>
                            <div class="mb-1"><strong><i class="fas fa-users text-primary mr-1"></i> Pegawai Pelaksana:</strong> ${props.pelaksana || '-'}</div>
                            <div class="mb-1"><strong><i class="far fa-calendar-alt text-primary mr-1"></i> Periode:</strong> ${props.periode || '-'}</div>
                            <div class="mb-1"><strong><i class="fas fa-bullseye text-warning mr-1"></i> Perihal / Maksud:</strong> ${props.perihal || '-'}</div>
                            <div class="mb-1"><strong><i class="fas fa-car text-secondary mr-1"></i> Transportasi:</strong> ${props.transportasi || '-'}</div>
                            <div><strong><i class="fas fa-check-double text-success mr-1"></i> Status:</strong> <span class="badge badge-secondary">${props.status || 'Disetujui'}</span></div>
                        </div>
                    `;
                }

                if (modalBody) modalBody.innerHTML = bodyHtml;

                let footerHtml = `<button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>`;
                if (linkUrl) {
                    footerHtml += `<a href="${linkUrl}" class="btn btn-primary btn-sm font-weight-bold ml-2"><i class="fas fa-external-link-alt mr-1"></i> ${linkText}</a>`;
                }
                if (modalFooter) modalFooter.innerHTML = footerHtml;

                $('#modal-dashboard-event-detail').modal('show');
            }
        });

        calendar.render();

        // Checkbox Filter Handlers
        document.querySelectorAll('.js-cal-filter').forEach(cb => {
            cb.addEventListener('change', function() {
                const filterType = this.getAttribute('data-type');
                activeFilters[filterType] = this.checked;
                calendar.removeAllEvents();
                calendar.addEventSource(getFilteredEvents());
            });
        });
    }

    // Field Photo Preview Modal Handler
    document.querySelectorAll('.js-photo-preview').forEach(card => {
        card.addEventListener('click', function(e) {
            e.preventDefault();
            const src = this.getAttribute('data-src') || '';
            const title = this.getAttribute('data-title') || 'Dokumentasi Lapangan';
            const meta = this.getAttribute('data-meta') || '';

            const imgEl = document.getElementById('photoPreviewImg');
            const titleEl = document.getElementById('photoPreviewTitle');
            const metaEl = document.getElementById('photoPreviewMeta');

            if (imgEl) imgEl.setAttribute('src', src);
            if (titleEl) titleEl.textContent = title;
            if (metaEl) metaEl.innerHTML = `<i class="fas fa-info-circle text-primary mr-1"></i> ${meta}`;

            if (typeof $ !== 'undefined') {
                $('#modal-dashboard-photo-preview').modal('show');
            }
        });
    });
});
</script>

<!-- Modal Detail Event Kalender Dashboard -->
<div class="modal fade" id="modal-dashboard-event-detail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 14px; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
            <div class="modal-header py-2 px-3 text-white" id="modalEventHeader" style="background: #0f4c81;">
                <h6 class="modal-title font-weight-bold mb-0" id="modalEventCategory">
                    Detail Jadwal
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3" id="modalEventBody">
                <!-- Dynamically populated via JS -->
            </div>
            <div class="modal-footer bg-light py-2" id="modalEventFooter">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Preview Foto Dokumentasi Lapangan -->
<div class="modal fade" id="modal-dashboard-photo-preview" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content" style="border-radius: 14px; overflow: hidden; border: none; box-shadow: 0 15px 35px rgba(0,0,0,0.3);">
            <div class="modal-header py-2 px-3 text-white" style="background: #0f4c81;">
                <h6 class="modal-title font-weight-bold mb-0" id="photoPreviewTitle">
                    Detail Foto Kegiatan
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0 text-center bg-dark" style="min-height: 280px; display: flex; align-items: center; justify-content: center;">
                <img id="photoPreviewImg" src="" alt="Preview Dokumentasi" style="max-height: 70vh; max-width: 100%; object-fit: contain;">
            </div>
            <div class="modal-footer bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <div class="small font-weight-bold text-dark text-truncate mr-2" id="photoPreviewMeta"></div>
                <div class="d-flex align-items-center" style="gap: 6px;">
                    <a href="<?= site_url('/admin/dokumentasi/kegiatan-lapangan'); ?>" class="btn btn-primary btn-sm font-weight-bold">
                        <i class="fas fa-folder-open mr-1"></i> Buka Modul Dokumentasi
                    </a>
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= esc(media_url('assets/adminlte/plugins/fullcalendar/main.min.js')); ?>"></script>
<script src="<?= esc(media_url('assets/adminlte/plugins/fullcalendar/locales-all.min.js')); ?>"></script>
<?= $this->endSection(); ?>