<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<style>
    .kompu-card {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 18px -4px rgba(0, 0, 0, 0.05);
        background: #ffffff;
    }
    .badge-status-sukses {
        background-color: #dcfce7;
        color: #15803d;
        border: 1px solid #86efac;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 20px;
    }
    .badge-status-gagal {
        background-color: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fca5a5;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 20px;
    }
    .font-mono {
        font-family: 'SFMono-Regular', Consolas, Menlo, monospace;
    }
    .log-kpi-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 14px 18px;
        transition: all 0.2s ease;
    }
</style>

<!-- HEADER ROW -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card kompu-card border-0 mb-3">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="font-weight-bold text-dark mb-1">
                            <i class="fas fa-envelope-open-text text-primary mr-2"></i> Log &amp; Audit Trail Pengiriman Kredensial
                        </h4>
                        <p class="text-muted small mb-md-0">
                            Pencatatan riwayat otomatis setiap permintaan pengiriman password akun resmi ke email pegawai terdaftar.
                        </p>
                    </div>
                    <div class="col-md-4 text-md-right">
                        <a href="<?= site_url('admin/kompu/sosmed'); ?>" class="btn btn-outline-primary btn-sm shadow-sm mr-1">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Akun Sosmed
                        </a>
                        <?php if ($menuPermissions['export']): ?>
                            <a href="<?= site_url('admin/kompu/log-kredensial/export'); ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
                                <i class="fas fa-file-csv mr-1"></i> Export Log
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- STATS CARDS -->
<?php
    $totalLogs = count($logs);
    $totalSukses = 0;
    $totalGagal = 0;
    foreach ($logs as $l) {
        if ($l['status'] === 'sukses') $totalSukses++;
        else $totalGagal++;
    }
?>
<div class="row mb-3">
    <div class="col-md-4 mb-2 mb-md-0">
        <div class="log-kpi-card d-flex align-items-center justify-content-between">
            <div>
                <small class="text-muted font-weight-bold text-uppercase">Total Permintaan</small>
                <h3 class="font-weight-bold mb-0 text-dark"><?= number_format($totalLogs); ?></h3>
            </div>
            <div class="text-primary" style="font-size: 2rem;">
                <i class="fas fa-history"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-2 mb-md-0">
        <div class="log-kpi-card d-flex align-items-center justify-content-between" style="background: #f0fdf4; border-color: #bbf7d0;">
            <div>
                <small class="text-success font-weight-bold text-uppercase">Pengiriman Sukses</small>
                <h3 class="font-weight-bold mb-0 text-success"><?= number_format($totalSukses); ?></h3>
            </div>
            <div class="text-success" style="font-size: 2rem;">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="log-kpi-card d-flex align-items-center justify-content-between" style="background: #fef2f2; border-color: #fecaca;">
            <div>
                <small class="text-danger font-weight-bold text-uppercase">Pengiriman Gagal / Ditolak</small>
                <h3 class="font-weight-bold mb-0 text-danger"><?= number_format($totalGagal); ?></h3>
            </div>
            <div class="text-danger" style="font-size: 2rem;">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
    </div>
</div>

<!-- FILTER BAR -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card kompu-card">
            <div class="card-body p-3">
                <form method="get" action="<?= site_url('admin/kompu/log-kredensial'); ?>" class="row align-items-center">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                            </div>
                            <input type="text" name="q" value="<?= esc($search ?? ''); ?>" class="form-control" placeholder="Cari nama platform, pegawai, NIP, email...">
                        </div>
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <select name="status" class="form-control form-control-sm">
                            <option value="">-- Semua Status --</option>
                            <option value="sukses" <?= ($statusFilter === 'sukses') ? 'selected' : ''; ?>>Hanya Sukses</option>
                            <option value="gagal" <?= ($statusFilter === 'gagal') ? 'selected' : ''; ?>>Hanya Gagal</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary btn-sm mr-1">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                        <a href="<?= site_url('admin/kompu/log-kredensial'); ?>" class="btn btn-outline-secondary btn-sm">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- TABLE LOG -->
<div class="row">
    <div class="col-12">
        <div class="card kompu-card">
            <div class="card-header bg-white border-bottom-0 py-3">
                <h5 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-list mr-2 text-primary"></i> Rekaman Riwayat Permintaan (<?= count($logs); ?> Data Terakhir)
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted text-uppercase" style="font-size: 0.78rem; letter-spacing: 0.5px;">
                            <tr>
                                <th style="width: 50px;" class="text-center">No.</th>
                                <th>Waktu Permintaan</th>
                                <th>Akun Media Sosial</th>
                                <th>Pegawai Peminta</th>
                                <th>Email Tujuan</th>
                                <th>Status</th>
                                <th>Keterangan / Debug</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 text-secondary d-block"></i>
                                        Belum ada rekaman riwayat pengiriman kredensial.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($logs as $log): ?>
                                    <tr>
                                        <td class="text-center font-weight-bold text-muted"><?= $no++; ?>.</td>
                                        <td style="white-space: nowrap;">
                                            <div class="font-weight-bold text-dark font-mono" style="font-size: 0.85rem;">
                                                <?= date('d/m/Y H:i:s', strtotime($log['created_at'])); ?>
                                            </div>
                                            <small class="text-muted">WIB</small>
                                        </td>
                                        <td>
                                            <strong class="text-primary"><?= esc($log['nama_sosmed']); ?></strong>
                                        </td>
                                        <td>
                                            <div class="font-weight-bold text-dark"><?= esc($log['user_name']); ?></div>
                                            <?php if (! empty($log['user_nip'])): ?>
                                                <small class="text-muted font-mono">NIP: <?= esc($log['user_nip']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="font-mono text-dark" style="font-size: 0.85rem;"><?= esc($log['email_tujuan']); ?></span>
                                        </td>
                                        <td>
                                            <?php if ($log['status'] === 'sukses'): ?>
                                                <span class="badge-status-sukses">
                                                    <i class="fas fa-check mr-1"></i> Sukses
                                                </span>
                                            <?php else: ?>
                                                <span class="badge-status-gagal">
                                                    <i class="fas fa-times mr-1"></i> Gagal
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small class="text-muted d-block" style="max-width: 280px; word-break: break-word;">
                                                <?= esc($log['pesan_status'] ?: '-'); ?>
                                            </small>
                                        </td>
                                        <td class="font-mono text-muted" style="font-size: 0.8rem;">
                                            <?= esc($log['ip_address'] ?: '-'); ?>
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
<?= $this->endSection(); ?>
