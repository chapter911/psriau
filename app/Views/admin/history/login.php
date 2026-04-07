<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
$rows = $rows ?? [];
$stats = $stats ?? ['total' => 0, 'success' => 0, 'failed' => 0, 'today' => 0];
$tableReady = (bool) ($table_ready ?? false);
$filters = is_array($filters ?? null) ? $filters : [
    'status' => '',
    'username' => '',
    'date_from' => '',
    'date_to' => '',
];

$reasonLabel = static function (?string $reason): string {
    $normalized = strtolower(trim((string) $reason));
    return match ($normalized) {
        'validation_failed' => 'Input login tidak lengkap',
        'invalid_credentials' => 'Kredensial tidak valid',
        'inactive_account' => 'Akun nonaktif',
        '', '-' => '-',
        default => ucwords(str_replace(['_', '-'], ' ', $normalized)),
    };
};
?>
<div class="row mb-3">
    <div class="col-12 col-md-6 col-xl-3 mb-2">
        <div class="small-box bg-info mb-0">
            <div class="inner">
                <h3><?= esc((string) ($stats['total'] ?? 0)); ?></h3>
                <p>Total Attempt</p>
            </div>
            <div class="icon"><i class="fas fa-fingerprint"></i></div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3 mb-2">
        <div class="small-box bg-success mb-0">
            <div class="inner">
                <h3><?= esc((string) ($stats['success'] ?? 0)); ?></h3>
                <p>Login Berhasil</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3 mb-2">
        <div class="small-box bg-danger mb-0">
            <div class="inner">
                <h3><?= esc((string) ($stats['failed'] ?? 0)); ?></h3>
                <p>Login Gagal</p>
            </div>
            <div class="icon"><i class="fas fa-times-circle"></i></div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3 mb-2">
        <div class="small-box bg-primary mb-0">
            <div class="inner">
                <h3><?= esc((string) ($stats['today'] ?? 0)); ?></h3>
                <p>Hari Ini</p>
            </div>
            <div class="icon"><i class="fas fa-calendar-day"></i></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="card-title mb-0">History Login</h3>
        <small class="text-muted">Mencatat detail login sukses dan gagal.</small>
    </div>
    <div class="card-body">
        <?php if (! $tableReady): ?>
            <div class="alert alert-warning mb-0">Tabel login history belum tersedia. Jalankan migration terlebih dahulu.</div>
        <?php else: ?>
            <form method="get" action="<?= site_url('admin/history/login'); ?>" class="mb-3">
                <div class="form-row align-items-end">
                    <div class="form-group col-12 col-md-2">
                        <label for="filter_status">Status</label>
                        <select class="form-control" id="filter_status" name="status">
                            <option value="" <?= ($filters['status'] ?? '') === '' ? 'selected' : ''; ?>>Semua</option>
                            <option value="success" <?= ($filters['status'] ?? '') === 'success' ? 'selected' : ''; ?>>Sukses</option>
                            <option value="failed" <?= ($filters['status'] ?? '') === 'failed' ? 'selected' : ''; ?>>Gagal</option>
                        </select>
                    </div>
                    <div class="form-group col-12 col-md-4">
                        <label for="filter_username">Username/Nama</label>
                        <input type="text" class="form-control" id="filter_username" name="username" value="<?= esc((string) ($filters['username'] ?? '')); ?>" placeholder="Cari username atau nama">
                    </div>
                    <div class="form-group col-12 col-md-2">
                        <label for="filter_date_from">Dari Tanggal</label>
                        <input type="date" class="form-control" id="filter_date_from" name="date_from" value="<?= esc((string) ($filters['date_from'] ?? '')); ?>">
                    </div>
                    <div class="form-group col-12 col-md-2">
                        <label for="filter_date_to">Sampai Tanggal</label>
                        <input type="date" class="form-control" id="filter_date_to" name="date_to" value="<?= esc((string) ($filters['date_to'] ?? '')); ?>">
                    </div>
                    <div class="form-group col-12 col-md-2 d-flex">
                        <button type="submit" class="btn btn-primary mr-2 flex-fill">Filter</button>
                        <a href="<?= site_url('admin/history/login'); ?>" class="btn btn-outline-secondary flex-fill">Reset</a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped w-100 js-datatable" id="tableLoginHistory">
                    <thead>
                        <tr>
                            <th style="width:170px;">Waktu</th>
                            <th style="width:100px;" class="text-center">Status</th>
                            <th style="width:140px;">Username Input</th>
                            <th style="width:140px;">Akun Terhubung</th>
                            <th style="width:130px;">Role</th>
                            <th style="width:140px;">IP Address</th>
                            <th style="width:170px;">Alasan Gagal</th>
                            <th style="width:90px;" class="text-center">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <?php
                            $isSuccess = (int) ($row['is_success'] ?? 0) === 1;
                            $badgeClass = $isSuccess ? 'badge badge-success' : 'badge badge-danger';
                            $badgeText = $isSuccess ? 'Sukses' : 'Gagal';

                            $detail = [
                                'attempted_at' => (string) ($row['attempted_at'] ?? ''),
                                'username_input' => (string) ($row['username_input'] ?? ''),
                                'user_id' => (string) ($row['user_id'] ?? ''),
                                'full_name' => (string) ($row['full_name'] ?? ''),
                                'role' => (string) ($row['role'] ?? ''),
                                'account_active' => (string) ($row['account_active'] ?? ''),
                                'ip_address' => (string) ($row['ip_address'] ?? ''),
                                'user_agent' => (string) ($row['user_agent'] ?? ''),
                                'http_method' => (string) ($row['http_method'] ?? ''),
                                'request_path' => (string) ($row['request_path'] ?? ''),
                                'referer' => (string) ($row['referer'] ?? ''),
                                'session_id' => (string) ($row['session_id'] ?? ''),
                                'failure_reason' => (string) ($row['failure_reason'] ?? ''),
                                'request_payload' => json_decode((string) ($row['request_payload_json'] ?? ''), true),
                                'server_context' => json_decode((string) ($row['server_context_json'] ?? ''), true),
                            ];
                            ?>
                            <tr>
                                <td><?= esc((string) ($row['attempted_at'] ?? '-')); ?></td>
                                <td class="text-center"><span class="<?= $badgeClass; ?>"><?= esc($badgeText); ?></span></td>
                                <td><?= esc((string) ($row['username_input'] ?? '-')); ?></td>
                                <td><?= esc((string) ($row['full_name'] ?? '-')); ?></td>
                                <td><?= esc((string) ($row['role'] ?? '-')); ?></td>
                                <td><?= esc((string) ($row['ip_address'] ?? '-')); ?></td>
                                <td><?= esc($reasonLabel((string) ($row['failure_reason'] ?? ''))); ?></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-info js-open-login-detail" data-detail="<?= esc(json_encode($detail, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'attr'); ?>">Lihat</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($tableReady): ?>
<div class="modal fade" id="modalLoginDetail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Login</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <pre id="loginDetailBody" class="mb-0" style="white-space: pre-wrap;"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<?php if ($tableReady): ?>
<script>
(() => {
    document.addEventListener('click', (event) => {
        const button = event.target.closest('.js-open-login-detail');
        if (!button) {
            return;
        }

        let detail = {};
        try {
            detail = JSON.parse(button.getAttribute('data-detail') || '{}');
        } catch (error) {
            detail = {};
        }

        const pretty = JSON.stringify(detail, null, 2) || '-';
        const target = document.getElementById('loginDetailBody');
        if (target) {
            target.textContent = pretty;
        }

        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.modal) {
            window.jQuery('#modalLoginDetail').modal('show');
        }
    });
})();
</script>
<?php endif; ?>
<?= $this->endSection(); ?>
