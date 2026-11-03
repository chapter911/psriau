<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
$rows = $rows ?? [];
$stats = $stats ?? ['total' => 0, 'create' => 0, 'edit' => 0, 'delete' => 0, 'view' => 0, 'today' => 0];
$tableReady = (bool) ($table_ready ?? false);
$filters = is_array($filters ?? null) ? $filters : [
    'type'      => '',
    'username'  => '',
    'module'    => '',
    'date_from' => '',
    'date_to'   => '',
];

$formatDateTime = static function (?string $value): array {
    $trimmed = trim((string) $value);
    if ($trimmed === '' || $trimmed === '-') {
        return ['full' => '-', 'date' => '-', 'time' => '-'];
    }

    try {
        $dt = new DateTimeImmutable($trimmed);
        return [
            'full' => $dt->format('d/m/Y H:i:s'),
            'date' => $dt->format('d/m/Y'),
            'time' => $dt->format('H:i:s') . ' WIB',
        ];
    } catch (Throwable $e) {
        return ['full' => $trimmed, 'date' => $trimmed, 'time' => ''];
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
?>

<style>
.stat-box-purple { background-color: #6f42c1 !important; color: #fff; }
.badge-action-create { background-color: #28a745; color: #fff; }
.badge-action-edit { background-color: #fd7e14; color: #fff; }
.badge-action-delete { background-color: #dc3545; color: #fff; }
.badge-action-view { background-color: #6f42c1; color: #fff; }
.badge-action-login { background-color: #17a2b8; color: #fff; }
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

<!-- Stat Cards -->
<div class="row mb-3">
    <div class="col-6 col-md-4 col-xl-2 mb-2">
        <div class="small-box bg-info mb-0 h-100">
            <div class="inner py-2 px-3">
                <h4 class="font-weight-bold mb-1"><?= number_format((int) ($stats['total'] ?? 0)); ?></h4>
                <p class="mb-0 text-white-50" style="font-size: 0.8rem;">Total Aktivitas</p>
            </div>
            <div class="icon"><i class="fas fa-list-check"></i></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2 mb-2">
        <div class="small-box bg-success mb-0 h-100">
            <div class="inner py-2 px-3">
                <h4 class="font-weight-bold mb-1"><?= number_format((int) ($stats['create'] ?? 0)); ?></h4>
                <p class="mb-0 text-white-50" style="font-size: 0.8rem;">Tambah Data</p>
            </div>
            <div class="icon"><i class="fas fa-plus-circle"></i></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2 mb-2">
        <div class="small-box bg-warning mb-0 h-100">
            <div class="inner py-2 px-3">
                <h4 class="font-weight-bold mb-1 text-white"><?= number_format((int) ($stats['edit'] ?? 0)); ?></h4>
                <p class="mb-0 text-white" style="font-size: 0.8rem;">Ubah Data</p>
            </div>
            <div class="icon"><i class="fas fa-edit"></i></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2 mb-2">
        <div class="small-box bg-danger mb-0 h-100">
            <div class="inner py-2 px-3">
                <h4 class="font-weight-bold mb-1"><?= number_format((int) ($stats['delete'] ?? 0)); ?></h4>
                <p class="mb-0 text-white-50" style="font-size: 0.8rem;">Hapus Data</p>
            </div>
            <div class="icon"><i class="fas fa-trash-alt"></i></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2 mb-2">
        <div class="small-box stat-box-purple mb-0 h-100">
            <div class="inner py-2 px-3">
                <h4 class="font-weight-bold mb-1"><?= number_format((int) ($stats['view'] ?? 0)); ?></h4>
                <p class="mb-0 text-white-50" style="font-size: 0.8rem;">Akses Halaman</p>
            </div>
            <div class="icon"><i class="fas fa-eye"></i></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2 mb-2">
        <div class="small-box bg-primary mb-0 h-100">
            <div class="inner py-2 px-3">
                <h4 class="font-weight-bold mb-1"><?= number_format((int) ($stats['today'] ?? 0)); ?></h4>
                <p class="mb-0 text-white-50" style="font-size: 0.8rem;">Hari Ini</p>
            </div>
            <div class="icon"><i class="fas fa-calendar-day"></i></div>
        </div>
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
                Catatan kronologis perubahan data (Tambah, Ubah, Hapus) dan kunjungan halaman oleh seluruh pengguna sistem secara real-time.
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
                <div class="form-row align-items-end">
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
                        <label for="filter_username" class="small font-weight-bold text-muted mb-1">Pengguna / Role</label>
                        <input type="text" class="form-control form-control-sm" id="filter_username" name="username" value="<?= esc((string) ($filters['username'] ?? '')); ?>" placeholder="Nama / Username">
                    </div>
                    <div class="form-group col-12 col-md-2 mb-2 mb-md-0">
                        <label for="filter_module" class="small font-weight-bold text-muted mb-1">Modul / Path</label>
                        <input type="text" class="form-control form-control-sm" id="filter_module" name="module" value="<?= esc((string) ($filters['module'] ?? '')); ?>" placeholder="Cari modul / path">
                    </div>
                    <div class="form-group col-6 col-md-2 mb-2 mb-md-0">
                        <label for="filter_date_from" class="small font-weight-bold text-muted mb-1">Dari Tanggal</label>
                        <input type="date" class="form-control form-control-sm" id="filter_date_from" name="date_from" value="<?= esc((string) ($filters['date_from'] ?? '')); ?>">
                    </div>
                    <div class="form-group col-6 col-md-2 mb-2 mb-md-0">
                        <label for="filter_date_to" class="small font-weight-bold text-muted mb-1">Sampai Tanggal</label>
                        <input type="date" class="form-control form-control-sm" id="filter_date_to" name="date_to" value="<?= esc((string) ($filters['date_to'] ?? '')); ?>">
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
            </form>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover w-100 js-datatable" id="tableActivityHistory" data-order='[[0,"desc"]]'>
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 140px;">Waktu</th>
                            <th style="width: 160px;">Pengguna</th>
                            <th style="width: 90px;" class="text-center">Aksi</th>
                            <th style="min-width: 200px;">Modul / Halaman</th>
                            <th style="width: 140px;">Target Data</th>
                            <th style="width: 120px;">IP Address</th>
                            <th style="width: 110px;" class="text-center">Rincian</th>
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
                                <td>
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

