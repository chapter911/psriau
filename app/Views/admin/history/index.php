<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
$rows = $rows ?? [];
$userList = $user_list ?? [];
$stats = $stats ?? ['total' => 0, 'create' => 0, 'edit' => 0, 'delete' => 0, 'view' => 0, 'today' => 0];
$tableReady = (bool) ($table_ready ?? false);
$filters = is_array($filters ?? null) ? $filters : [
    'type'               => '',
    'username'           => '',
    'exclude_user'       => '',
    'include_superadmin' => false,
    'module'             => '',
    'date_from'          => '',
    'date_to'            => '',
];

$includeSuperadmin = (bool) ($filters['include_superadmin'] ?? false);
$currentType = (string) ($filters['type'] ?? '');
$isTodayFilter = ($filters['date_from'] ?? '') === date('Y-m-d') && ($filters['date_to'] ?? '') === date('Y-m-d');

$formatDateTime = static function (?string $value): array {
    $trimmed = trim((string) $value);
    if ($trimmed === '' || $trimmed === '-') {
        return ['full' => '-', 'date' => '-', 'time' => '-', 'raw' => ''];
    }

    try {
        $dt = new DateTimeImmutable($trimmed);
        return [
            'full' => $dt->format('d/m/Y H:i:s'),
            'date' => $dt->format('d/m/Y'),
            'time' => $dt->format('H:i:s') . ' WIB',
            'raw'  => $dt->format('Y-m-d H:i:s'),
        ];
    } catch (Throwable $e) {
        return ['full' => $trimmed, 'date' => $trimmed, 'time' => '', 'raw' => $trimmed];
    }
};

$resolveModuleLabel = static function (?string $path, ?string $table): string {
    $p = trim(strtolower((string) $path), '/');
    if (strpos($p, 'admin/surat/perjalanan-dinas/disposisi') === 0) return 'Surat - Disposisi';
    if (strpos($p, 'admin/surat/perjalanan-dinas/nota-dinas') === 0) return 'Surat - Nota Dinas';
    if (strpos($p, 'admin/surat/perjalanan-dinas') === 0) return 'Surat - Perjalanan Dinas';
    if (strpos($p, 'admin/surat/cuti') === 0) return 'Surat - Cuti Pegawai';
    if (strpos($p, 'admin/surat/lupa-absen') === 0) return 'Surat - Lupa Absen';
    if (strpos($p, 'admin/inventaris/satker') === 0) return 'Inventaris - Aset SIMAN';
    if (strpos($p, 'admin/inventaris/dbr') === 0) return 'Inventaris - Ruangan DBR';
    if (strpos($p, 'admin/inventaris/pinjam-pakai') === 0) return 'Inventaris - Pinjam Pakai';
    if (strpos($p, 'admin/inventaris/audit') === 0) return 'Inventaris - Audit Aset';
    if (strpos($p, 'admin/inventaris/pemeliharaan') === 0) return 'Inventaris - Pemeliharaan';
    if (strpos($p, 'admin/inventaris/tidak-terdata') === 0) return 'Inventaris - Tidak Terdata';
    if (strpos($p, 'admin/kontrak/paket') === 0) return 'Kontrak - Paket Pekerjaan';
    if (strpos($p, 'admin/kontrak/ki') === 0) return 'Kontrak - Konsultan Individual';
    if (strpos($p, 'admin/kontrak/bast') === 0) return 'Kontrak - BAST';
    if (strpos($p, 'admin/kontrak/simak') === 0) return 'Kontrak - SIMAK';
    if (strpos($p, 'admin/laporan/harian') === 0) return 'Laporan - Harian';
    if (strpos($p, 'admin/laporan/mingguan') === 0) return 'Laporan - Mingguan';
    if (strpos($p, 'admin/swakelola') === 0) return 'Swakelola';
    if (strpos($p, 'admin/kompu') === 0) return 'KOMPU';
    if (strpos($p, 'admin/master/sekolah') === 0) return 'Master - Sekolah';
    if (strpos($p, 'admin/master/pegawai') === 0) return 'Master - Pegawai';
    if (strpos($p, 'admin/master/jabatan') === 0) return 'Master - Jabatan';
    if (strpos($p, 'admin/master/mata-anggaran') === 0) return 'Master - Mata Anggaran';
    if (strpos($p, 'admin/master/tanggal-merah') === 0) return 'Master - Tanggal Libur';
    if (strpos($p, 'admin/master/biaya') === 0) return 'Master - Standar Biaya';
    if (strpos($p, 'admin/master/transportasi') === 0) return 'Master - Transportasi';
    if (strpos($p, 'admin/master/kop-surat') === 0) return 'Master - Kop Surat';
    if (strpos($p, 'admin/utility/user') === 0) return 'Utility - Manajemen User';
    if (strpos($p, 'admin/utility/role') === 0 || strpos($p, 'admin/utility/user-group') === 0) return 'Utility - Hak Akses Role';
    if (strpos($p, 'admin/pengaturan') === 0) return 'Pengaturan Sistem';
    if (strpos($p, 'admin/history/login') === 0) return 'History - Login';
    if (strpos($p, 'admin/history') === 0) return 'History - Activity Log';
    if (strpos($p, 'admin/tutorial') === 0) return 'Tutorial & Flowchart';
    if (strpos($p, 'admin/dokumentasi') === 0) return 'Dokumentasi Lapangan';
    if ($p === 'admin') return 'Dashboard';

    if (! empty($table)) {
        return ucwords(str_replace(['trn_', 'mst_', 'cfg_', '_'], ' ', $table));
    }

    return '/' . $p;
};

// Helper URL builder for clickable stat cards
$buildCardUrl = static function (array $overrides) use ($filters): string {
    $params = [
        'type'               => $filters['type'] ?? '',
        'username'           => $filters['username'] ?? '',
        'exclude_user'       => $filters['exclude_user'] ?? '',
        'module'             => $filters['module'] ?? '',
        'date_from'          => $filters['date_from'] ?? '',
        'date_to'            => $filters['date_to'] ?? '',
        'include_superadmin' => ($filters['include_superadmin'] ?? false) ? '1' : '',
    ];

    $merged = array_merge($params, $overrides);
    $clean = [];
    foreach ($merged as $k => $v) {
        if ($v !== '' && $v !== null) {
            $clean[$k] = $v;
        }
    }

    return site_url('admin/history' . (! empty($clean) ? '?' . http_build_query($clean) : ''));
};
?>

<style>
.stat-box-purple { background-color: #6f42c1 !important; color: #fff; }
.badge-action-create { background-color: #28a745; color: #fff; }
.badge-action-edit { background-color: #fd7e14; color: #fff; }
.badge-action-delete { background-color: #dc3545; color: #fff; }
.badge-action-view { background-color: #6f42c1; color: #fff; }
.badge-action-login { background-color: #17a2b8; color: #fff; }

/* Clickable card styles */
.card-filter-link {
    text-decoration: none !important;
    display: block;
    height: 100%;
    color: inherit;
}
.card-filter-link .small-box {
    transition: transform 0.18s ease, box-shadow 0.18s ease;
    cursor: pointer;
    border-radius: 8px;
    position: relative;
    overflow: hidden;
}
.card-filter-link:hover .small-box {
    transform: translateY(-4px);
    box-shadow: 0 8px 18px rgba(0, 0, 0, 0.22) !important;
    filter: brightness(1.04);
}
.card-filter-link.is-active .small-box {
    box-shadow: 0 0 0 3px #17a2b8, 0 8px 18px rgba(0, 0, 0, 0.3) !important;
    transform: scale(1.02);
}
.card-filter-link.is-active .small-box::after {
    content: "✓ Aktif";
    position: absolute;
    top: 6px;
    right: 8px;
    background: rgba(0, 0, 0, 0.45);
    color: #fff;
    font-size: 0.65rem;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 10px;
    letter-spacing: 0.5px;
}
.json-viewer-box {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 12px;
    font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    font-size: 0.82rem;
    max-height: 420px;
    overflow-y: auto;
    white-space: pre-wrap;
    word-break: break-all;
}
</style>

<!-- Clickable Stat Cards -->
<div class="row mb-3">
    <!-- Card 1: Total Aktivitas -->
    <div class="col-6 col-md-4 col-xl-2 mb-2">
        <a href="<?= $buildCardUrl(['type' => '', 'date_from' => '', 'date_to' => '']); ?>" 
           class="card-filter-link <?= ($currentType === '' && ! $isTodayFilter) ? 'is-active' : ''; ?>"
           title="Klik untuk melihat semua aktivitas">
            <div class="small-box bg-info mb-0 h-100">
                <div class="inner py-2 px-3">
                    <h4 class="font-weight-bold mb-1"><?= number_format((int) ($stats['total'] ?? 0)); ?></h4>
                    <p class="mb-0 text-white-50" style="font-size: 0.8rem;">Total Aktivitas</p>
                </div>
                <div class="icon"><i class="fas fa-list-check"></i></div>
            </div>
        </a>
    </div>

    <!-- Card 2: Tambah Data -->
    <div class="col-6 col-md-4 col-xl-2 mb-2">
        <a href="<?= $buildCardUrl(['type' => 'create']); ?>" 
           class="card-filter-link <?= ($currentType === 'create') ? 'is-active' : ''; ?>"
           title="Klik untuk memfilter Tambah Data (Create)">
            <div class="small-box bg-success mb-0 h-100">
                <div class="inner py-2 px-3">
                    <h4 class="font-weight-bold mb-1"><?= number_format((int) ($stats['create'] ?? 0)); ?></h4>
                    <p class="mb-0 text-white-50" style="font-size: 0.8rem;">Tambah Data</p>
                </div>
                <div class="icon"><i class="fas fa-plus-circle"></i></div>
            </div>
        </a>
    </div>

    <!-- Card 3: Ubah Data -->
    <div class="col-6 col-md-4 col-xl-2 mb-2">
        <a href="<?= $buildCardUrl(['type' => 'edit']); ?>" 
           class="card-filter-link <?= ($currentType === 'edit') ? 'is-active' : ''; ?>"
           title="Klik untuk memfilter Ubah Data (Edit)">
            <div class="small-box bg-warning mb-0 h-100">
                <div class="inner py-2 px-3">
                    <h4 class="font-weight-bold mb-1 text-white"><?= number_format((int) ($stats['edit'] ?? 0)); ?></h4>
                    <p class="mb-0 text-white" style="font-size: 0.8rem;">Ubah Data</p>
                </div>
                <div class="icon"><i class="fas fa-edit"></i></div>
            </div>
        </a>
    </div>

    <!-- Card 4: Hapus Data -->
    <div class="col-6 col-md-4 col-xl-2 mb-2">
        <a href="<?= $buildCardUrl(['type' => 'delete']); ?>" 
           class="card-filter-link <?= ($currentType === 'delete') ? 'is-active' : ''; ?>"
           title="Klik untuk memfilter Hapus Data (Delete)">
            <div class="small-box bg-danger mb-0 h-100">
                <div class="inner py-2 px-3">
                    <h4 class="font-weight-bold mb-1"><?= number_format((int) ($stats['delete'] ?? 0)); ?></h4>
                    <p class="mb-0 text-white-50" style="font-size: 0.8rem;">Hapus Data</p>
                </div>
                <div class="icon"><i class="fas fa-trash-alt"></i></div>
            </div>
        </a>
    </div>

    <!-- Card 5: Kunjungan Halaman -->
    <div class="col-6 col-md-4 col-xl-2 mb-2">
        <a href="<?= $buildCardUrl(['type' => 'view']); ?>" 
           class="card-filter-link <?= ($currentType === 'view') ? 'is-active' : ''; ?>"
           title="Klik untuk memfilter Kunjungan Halaman (View)">
            <div class="small-box stat-box-purple mb-0 h-100">
                <div class="inner py-2 px-3">
                    <h4 class="font-weight-bold mb-1"><?= number_format((int) ($stats['view'] ?? 0)); ?></h4>
                    <p class="mb-0 text-white-50" style="font-size: 0.8rem;">Akses Halaman</p>
                </div>
                <div class="icon"><i class="fas fa-eye"></i></div>
            </div>
        </a>
    </div>

    <!-- Card 6: Hari Ini -->
    <div class="col-6 col-md-4 col-xl-2 mb-2">
        <a href="<?= $buildCardUrl(['date_from' => date('Y-m-d'), 'date_to' => date('Y-m-d')]); ?>" 
           class="card-filter-link <?= ($isTodayFilter) ? 'is-active' : ''; ?>"
           title="Klik untuk melihat aktivitas hari ini">
            <div class="small-box bg-primary mb-0 h-100">
                <div class="inner py-2 px-3">
                    <h4 class="font-weight-bold mb-1"><?= number_format((int) ($stats['today'] ?? 0)); ?></h4>
                    <p class="mb-0 text-white-50" style="font-size: 0.8rem;">Hari Ini</p>
                </div>
                <div class="icon"><i class="fas fa-calendar-day"></i></div>
            </div>
        </a>
    </div>
</div>

<!-- Main Card -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between">
        <div>
            <h5 class="card-title font-weight-bold mb-0 text-dark">
                <i class="fas fa-history text-primary mr-2"></i> Activity History (Log Aktivitas Pengguna)
            </h5>
            <small class="text-muted d-block mt-1">
                Catatan kronologis perubahan data (Tambah, Ubah, Hapus) dan kunjungan halaman oleh pengguna sistem secara real-time.
            </small>
        </div>
        <div class="mt-2 mt-md-0">
            <a href="<?= site_url('admin/history/login'); ?>" class="btn btn-sm btn-outline-info">
                <i class="fas fa-user-shield mr-1"></i> Lihat History Login
            </a>
        </div>
    </div>

    <div class="card-body">
        <?php if (! $tableReady): ?>
            <div class="alert alert-warning mb-0">
                <i class="fas fa-exclamation-triangle mr-2"></i> Tabel audit history belum tersedia di database.
            </div>
        <?php else: ?>
            <!-- Filter Form -->
            <form method="get" action="<?= site_url('admin/history'); ?>" class="p-3 mb-4 bg-light rounded border">
                <!-- Baris 1: Filter Utama -->
                <div class="form-row align-items-end mb-2">
                    <div class="form-group col-12 col-md-3 mb-2 mb-md-0">
                        <label for="filter_type" class="small font-weight-bold text-muted mb-1">Tipe Aktivitas</label>
                        <select class="form-control form-control-sm" id="filter_type" name="type">
                            <option value="" <?= ($filters['type'] ?? '') === '' ? 'selected' : ''; ?>>Semua Aktivitas</option>
                            <option value="data_changes" <?= ($filters['type'] ?? '') === 'data_changes' ? 'selected' : ''; ?>>Perubahan Data Saja (Tambah/Ubah/Hapus)</option>
                            <option value="create" <?= ($filters['type'] ?? '') === 'create' ? 'selected' : ''; ?>>Tambah Data (Create)</option>
                            <option value="edit" <?= ($filters['type'] ?? '') === 'edit' ? 'selected' : ''; ?>>Ubah Data (Edit)</option>
                            <option value="delete" <?= ($filters['type'] ?? '') === 'delete' ? 'selected' : ''; ?>>Hapus Data (Delete)</option>
                            <option value="view" <?= ($filters['type'] ?? '') === 'view' ? 'selected' : ''; ?>>Kunjungan Halaman (View)</option>
                        </select>
                    </div>
                    <div class="form-group col-12 col-md-2 mb-2 mb-md-0">
                        <label for="filter_username" class="small font-weight-bold text-muted mb-1">Cari Pengguna</label>
                        <input type="text" class="form-control form-control-sm" id="filter_username" name="username" value="<?= esc((string) ($filters['username'] ?? '')); ?>" list="userListSearchOptions" placeholder="Nama / Username">
                        <datalist id="userListSearchOptions">
                            <?php foreach ($userList as $u): ?>
                                <option value="<?= esc($u['full_name']); ?>"><?= esc($u['username']); ?> (<?= esc($u['role']); ?>)</option>
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    <div class="form-group col-12 col-md-2 mb-2 mb-md-0">
                        <label for="filter_exclude_user" class="small font-weight-bold text-danger mb-1">
                            <i class="fas fa-user-slash mr-1"></i> Kecualikan User
                        </label>
                        <input type="text" class="form-control form-control-sm border-danger" id="filter_exclude_user" name="exclude_user" value="<?= esc((string) ($filters['exclude_user'] ?? '')); ?>" list="userListExcludeOptions" placeholder="Sembunyikan user ini">
                        <datalist id="userListExcludeOptions">
                            <?php foreach ($userList as $u): ?>
                                <option value="<?= esc($u['full_name']); ?>"><?= esc($u['username']); ?></option>
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    <div class="form-group col-12 col-md-2 mb-2 mb-md-0">
                        <label for="filter_module" class="small font-weight-bold text-muted mb-1">Modul / Path</label>
                        <input type="text" class="form-control form-control-sm" id="filter_module" name="module" value="<?= esc((string) ($filters['module'] ?? '')); ?>" placeholder="Cari modul / path">
                    </div>
                    <div class="form-group col-6 col-md-1 mb-2 mb-md-0">
                        <label for="filter_date_from" class="small font-weight-bold text-muted mb-1">Dari Tgl</label>
                        <input type="date" class="form-control form-control-sm px-1" id="filter_date_from" name="date_from" value="<?= esc((string) ($filters['date_from'] ?? '')); ?>">
                    </div>
                    <div class="form-group col-6 col-md-1 mb-2 mb-md-0">
                        <label for="filter_date_to" class="small font-weight-bold text-muted mb-1">Sampai Tgl</label>
                        <input type="date" class="form-control form-control-sm px-1" id="filter_date_to" name="date_to" value="<?= esc((string) ($filters['date_to'] ?? '')); ?>">
                    </div>
                    <div class="form-group col-12 col-md-1 mb-0 d-flex">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill mr-1" title="Terapkan Filter">
                            <i class="fas fa-filter"></i>
                        </button>
                        <a href="<?= site_url('admin/history'); ?>" class="btn btn-outline-secondary btn-sm flex-fill" title="Reset Filter">
                            <i class="fas fa-sync-alt"></i>
                        </a>
                    </div>
                </div>

                <!-- Baris 2: Opsi Tambahan (Toggle Super Administrator) -->
                <div class="d-flex flex-wrap align-items-center justify-content-between pt-2 border-top">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="include_superadmin" name="include_superadmin" value="1" <?= $includeSuperadmin ? 'checked' : ''; ?> onchange="this.form.submit()">
                        <label class="custom-control-label small font-weight-bold text-secondary" for="include_superadmin" style="cursor: pointer;">
                            <i class="fas fa-user-shield text-primary mr-1"></i> Sertakan Log Super Administrator
                        </label>
                        <small class="text-muted ml-2 d-none d-md-inline">(Secara default dinonaktifkan agar log fokus pada staf/operator).</small>
                    </div>

                    <?php if (! empty($filters['exclude_user'])): ?>
                        <div class="badge badge-warning text-dark py-1 px-2 mt-1 mt-md-0">
                            <i class="fas fa-filter mr-1"></i> User Dikecualikan: <strong><?= esc($filters['exclude_user']); ?></strong>
                        </div>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover w-100 js-datatable" id="tableActivityHistory" data-order='[[0,"desc"]]'>
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 150px;">Waktu</th>
                            <th style="width: 170px;">Pengguna</th>
                            <th style="width: 95px;" class="text-center">Aksi</th>
                            <th style="min-width: 200px;">Modul / Halaman</th>
                            <th style="width: 140px;">Target Data</th>
                            <th style="width: 120px;">IP Address</th>
                            <th style="width: 100px;" class="text-center">Rincian</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <?php
                            $actionType = strtolower(trim((string) ($row['action_type'] ?? '')));
                            $dt = $formatDateTime($row['happened_at'] ?? null);

                            $badgeClass = match ($actionType) {
                                'create', 'add' => 'badge-action-create',
                                'edit', 'update' => 'badge-action-edit',
                                'delete', 'hapus' => 'badge-action-delete',
                                'view' => 'badge-action-view',
                                'login' => 'badge-action-login',
                                default => 'badge-secondary',
                            };

                            $actionLabel = match ($actionType) {
                                'create', 'add' => '<i class="fas fa-plus mr-1"></i> Tambah',
                                'edit', 'update' => '<i class="fas fa-edit mr-1"></i> Ubah',
                                'delete', 'hapus' => '<i class="fas fa-trash mr-1"></i> Hapus',
                                'view' => '<i class="fas fa-eye mr-1"></i> Kunjungan',
                                'login' => '<i class="fas fa-sign-in-alt mr-1"></i> Login',
                                default => esc(ucfirst($actionType)),
                            };

                            $reqJson = (string) ($row['request_data_json'] ?? '');
                            $beforeJson = (string) ($row['before_data_json'] ?? '');
                            $afterJson = (string) ($row['after_data_json'] ?? '');

                            $hasRequest = ! in_array(trim($reqJson), ['', 'null', '[]', '{}'], true);
                            $hasBefore = ! in_array(trim($beforeJson), ['', 'null', '[]', '{}'], true);
                            $hasAfter = ! in_array(trim($afterJson), ['', 'null', '[]', '{}'], true);
                            $hasDetails = $hasRequest || $hasBefore || $hasAfter;

                            $moduleLabel = $resolveModuleLabel($row['module_path'] ?? null, $row['table_name'] ?? null);
                            ?>
                            <tr>
                                <!-- Kolom 0: Menggunakan data-order & data-sort ISO agar pengurutan tanggal kronologis sempurna -->
                                <td data-order="<?= esc($dt['raw']); ?>" data-sort="<?= esc($dt['raw']); ?>">
                                    <div class="font-weight-bold text-dark" style="font-size: 0.82rem;"><?= esc($dt['date']); ?></div>
                                    <small class="text-muted"><i class="far fa-clock mr-1"></i><?= esc($dt['time']); ?></small>
                                </td>
                                <td>
                                    <div class="font-weight-bold text-dark" style="font-size: 0.84rem;"><?= esc((string) ($row['username'] ?? '-')); ?></div>
                                    <span class="badge badge-light border text-muted px-1 py-0" style="font-size: 0.68rem;"><?= esc((string) ($row['role'] ?? '-')); ?></span>
                                </td>
                                <td class="text-center align-middle">
                                    <span class="badge <?= $badgeClass; ?> px-2 py-1 font-weight-normal" style="font-size: 0.75rem;">
                                        <?= $actionLabel; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="font-weight-bold text-dark" style="font-size: 0.84rem;"><?= esc($moduleLabel); ?></div>
                                    <small class="text-muted text-monospace">/<?= esc((string) ($row['module_path'] ?? '-')); ?></small>
                                </td>
                                <td>
                                    <?php if (! empty($row['table_name'])): ?>
                                        <div class="small font-weight-bold text-secondary"><?= esc((string) $row['table_name']); ?></div>
                                        <?php if (! empty($row['record_id'])): ?>
                                            <small class="text-muted">ID: <span class="badge badge-light border font-weight-bold"><?= esc((string) $row['record_id']); ?></span></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="font-weight-bold text-secondary d-block"><?= esc((string) ($row['ip_address'] ?? '-')); ?></small>
                                    <?php if (! empty($row['user_agent'])): ?>
                                        <small class="text-muted text-truncate d-inline-block" style="max-width: 140px;" title="<?= esc((string) $row['user_agent'], 'attr'); ?>">
                                            <i class="fas fa-desktop mr-1"></i><?= esc(substr((string) $row['user_agent'], 0, 25)); ?>...
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center align-middle">
                                    <?php if ($hasDetails): ?>
                                        <button type="button" class="btn btn-xs btn-outline-primary js-open-audit-modal"
                                            data-id="<?= esc((string) $row['id']); ?>"
                                            data-user="<?= esc((string) $row['username']); ?>"
                                            data-action="<?= esc($actionType); ?>"
                                            data-module="<?= esc($moduleLabel); ?>"
                                            data-time="<?= esc($dt['full']); ?>"
                                            data-request="<?= esc($reqJson, 'attr'); ?>"
                                            data-before="<?= esc($beforeJson, 'attr'); ?>"
                                            data-after="<?= esc($afterJson, 'attr'); ?>">
                                            <i class="fas fa-file-code mr-1"></i> Detail
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
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

<!-- Modal Detail JSON Payload -->
<div class="modal fade" id="modalAuditDetail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <div>
                    <h5 class="modal-title font-weight-bold text-dark" id="auditModalTitle">
                        <i class="fas fa-shield-alt text-primary mr-1"></i> Rincian Aktivitas
                    </h5>
                    <small class="text-muted" id="auditModalSubtitle"></small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                <ul class="nav nav-pills mb-3" id="auditTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active font-weight-bold" id="tab-request-link" data-toggle="pill" href="#tab-request" role="tab">
                            <i class="fas fa-paper-plane mr-1"></i> Request Payload
                        </a>
                    </li>
                    <li class="nav-item" id="tab-before-item">
                        <a class="nav-link font-weight-bold" id="tab-before-link" data-toggle="pill" href="#tab-before" role="tab">
                            <i class="fas fa-history mr-1"></i> Data Sebelum (Before)
                        </a>
                    </li>
                    <li class="nav-item" id="tab-after-item">
                        <a class="nav-link font-weight-bold" id="tab-after-link" data-toggle="pill" href="#tab-after" role="tab">
                            <i class="fas fa-check-double mr-1"></i> Data Sesudah (After)
                        </a>
                    </li>
                </ul>
                <div class="tab-content" id="auditTabsContent">
                    <div class="tab-pane fade show active" id="tab-request" role="tabpanel">
                        <pre class="json-viewer-box" id="jsonRequestViewer">-</pre>
                    </div>
                    <div class="tab-pane fade" id="tab-before" role="tabpanel">
                        <pre class="json-viewer-box" id="jsonBeforeViewer">-</pre>
                    </div>
                    <div class="tab-pane fade" id="tab-after" role="tabpanel">
                        <pre class="json-viewer-box" id="jsonAfterViewer">-</pre>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script>
(() => {
    const modalEl = document.getElementById('modalAuditDetail');
    const titleEl = document.getElementById('auditModalTitle');
    const subtitleEl = document.getElementById('auditModalSubtitle');
    const requestBox = document.getElementById('jsonRequestViewer');
    const beforeBox = document.getElementById('jsonBeforeViewer');
    const afterBox = document.getElementById('jsonAfterViewer');
    const tabBeforeItem = document.getElementById('tab-before-item');
    const tabAfterItem = document.getElementById('tab-after-item');

    if (!modalEl) return;

    const formatJson = (content) => {
        if (!content || inEmpty(content)) return 'Tidak ada data.';
        try {
            const parsed = JSON.parse(content);
            return JSON.stringify(parsed, null, 4);
        } catch (e) {
            return content;
        }
    };

    const inEmpty = (str) => {
        const trimmed = (str || '').trim();
        return trimmed === '' || trimmed === 'null' || trimmed === '[]' || trimmed === '{}';
    };

    document.addEventListener('click', (event) => {
        const btn = event.target.closest('.js-open-audit-modal');
        if (!btn) return;

        const user = btn.getAttribute('data-user') || '-';
        const action = btn.getAttribute('data-action') || '-';
        const module = btn.getAttribute('data-module') || '-';
        const time = btn.getAttribute('data-time') || '-';
        const reqData = btn.getAttribute('data-request') || '';
        const beforeData = btn.getAttribute('data-before') || '';
        const afterData = btn.getAttribute('data-after') || '';

        subtitleEl.textContent = `${user} • Aksi: ${action.toUpperCase()} • ${module} (${time})`;

        requestBox.textContent = formatJson(reqData);
        beforeBox.textContent = formatJson(beforeData);
        afterBox.textContent = formatJson(afterData);

        // Toggle tabs availability
        if (inEmpty(beforeData)) {
            tabBeforeItem.style.display = 'none';
        } else {
            tabBeforeItem.style.display = 'block';
        }

        if (inEmpty(afterData)) {
            tabAfterItem.style.display = 'none';
        } else {
            tabAfterItem.style.display = 'block';
        }

        // Activate first tab
        if (window.jQuery) {
            window.jQuery('#tab-request-link').tab('show');
            window.jQuery('#modalAuditDetail').modal('show');
        }
    });
})();
</script>
<?= $this->endSection(); ?>
