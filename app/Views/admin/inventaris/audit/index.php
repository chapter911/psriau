<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<style>
.audit-scope-card {
    border: 1.5px solid #e2e8f0 !important;
    background-color: #ffffff;
    border-radius: 10px !important;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    margin-bottom: 0;
}
.audit-scope-card:hover {
    border-color: #007bff !important;
    background-color: #f8fbff;
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.08) !important;
}
.audit-scope-card.active-scope {
    border-color: #007bff !important;
    background-color: #f0f7ff !important;
    box-shadow: 0 0 0 1px #007bff, 0 4px 12px rgba(0, 123, 255, 0.12) !important;
}
.audit-scope-card .custom-control {
    padding-left: 1.6rem;
}
.audit-scope-card .custom-control-label {
    cursor: pointer;
    font-size: 0.95rem;
}
.audit-scope-card .custom-control-label::before,
.audit-scope-card .custom-control-label::after {
    top: 0.18rem;
    left: -1.6rem;
}
</style>
<div class="container-fluid">

    <!-- KPI Summary Widgets -->
    <div class="row mb-3">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #0A66C2;">
                <span class="info-box-icon elevation-1" style="border-radius: 8px; background-color: #0A66C2; color: #fff;"><i class="fas fa-clipboard-list"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Total Sesi Audit</span>
                    <span class="info-box-number text-dark" style="font-size: 1.35rem;"><?= number_format((int) ($summaryKPI['total_audit'] ?? 0)); ?></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #f59e0b;">
                <span class="info-box-icon elevation-1" style="border-radius: 8px; background-color: #f59e0b; color: #fff;"><i class="fas fa-hourglass-half"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Sedang Berjalan</span>
                    <span class="info-box-number text-warning" style="font-size: 1.35rem;"><?= number_format((int) ($summaryKPI['total_berjalan'] ?? 0)); ?></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #10b981;">
                <span class="info-box-icon elevation-1" style="border-radius: 8px; background-color: #10b981; color: #fff;"><i class="fas fa-check-double"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Selesai & Terkunci</span>
                    <span class="info-box-number text-success" style="font-size: 1.35rem;"><?= number_format((int) ($summaryKPI['total_selesai'] ?? 0)); ?></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #6366f1;">
                <span class="info-box-icon elevation-1" style="border-radius: 8px; background-color: #6366f1; color: #fff;"><i class="fas fa-boxes"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Total Aset Diperiksa</span>
                    <span class="info-box-number text-primary" style="font-size: 1.35rem;"><?= number_format((int) ($summaryKPI['total_aset'] ?? 0)); ?> unit</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm mb-3" style="border-radius: 10px; border: 1px solid #e9eef5;">
        <div class="card-body py-3">
            <form method="get" action="<?= site_url('admin/inventaris/audit'); ?>">
                <div class="row align-items-end">
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-flag mr-1"></i> Status Sesi</label>
                        <select name="status" class="form-control form-control-sm" style="border-radius: 6px;">
                            <option value="">Semua Status</option>
                            <option value="berjalan" <?= ($filterStatus === 'berjalan') ? 'selected' : ''; ?>>Sedang Berjalan</option>
                            <option value="selesai" <?= ($filterStatus === 'selesai') ? 'selected' : ''; ?>>Selesai</option>
                            <option value="dibatalkan" <?= ($filterStatus === 'dibatalkan') ? 'selected' : ''; ?>>Dibatalkan</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-layer-group mr-1"></i> Lingkup Audit</label>
                        <select name="lingkup" class="form-control form-control-sm" style="border-radius: 6px;">
                            <option value="">Semua Lingkup</option>
                            <option value="kantor_ruangan" <?= ($filterLingkup === 'kantor_ruangan') ? 'selected' : ''; ?>>Kantor - Per Ruangan</option>
                            <option value="kantor_seluruh" <?= ($filterLingkup === 'kantor_seluruh') ? 'selected' : ''; ?>>Kantor - Seluruh Aset</option>
                            <option value="sekolah" <?= ($filterLingkup === 'sekolah') ? 'selected' : ''; ?>>Sekolah (Mobiler / Sarpras)</option>
                        </select>
                    </div>
                    <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-search mr-1"></i> Kata Kunci</label>
                        <input type="text" name="q" class="form-control form-control-sm" placeholder="Cari kode audit, judul, nama auditor..." value="<?= esc($searchKeyword ?? ''); ?>" style="border-radius: 6px;">
                    </div>
                    <div class="col-md-2 col-sm-6 d-flex" style="gap: 6px;">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill shadow-sm" style="border-radius: 6px;">
                            <i class="fas fa-filter mr-1"></i> Saring
                        </button>
                        <a href="<?= site_url('admin/inventaris/audit'); ?>" class="btn btn-outline-secondary btn-sm shadow-sm" style="border-radius: 6px;" title="Reset Filter">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="card shadow-sm" style="border: 1px solid #e9eef5; border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white py-3" style="border-bottom: 1px solid #e9eef5;">
            <div class="d-flex flex-wrap justify-content-between align-items-center w-100">
                <div>
                    <h3 class="card-title mb-0 font-weight-bold text-dark" style="font-size: 1.15rem; line-height: 1.8;">
                        <i class="fas fa-clipboard-check text-primary mr-2"></i>Daftar Sesi Audit & Stock Opname Aset
                    </h3>
                    <small class="text-muted d-block mt-1">Pemeriksaan fisik aset BMN kantor (per ruangan / seluruhnya), pemantauan aset dipinjam pakai, dan aset binaan sekolah</small>
                </div>
                <div class="card-tools m-0 d-flex align-items-center" style="gap: 8px;">
                    <?php if ($menuPermissions['add'] ?? false): ?>
                        <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm font-weight-bold" data-toggle="modal" data-target="#modalTambahAudit" style="border-radius: 6px;">
                            <i class="fas fa-plus-circle mr-1"></i> Mulai Sesi Audit Baru
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 45px;" class="text-center">#</th>
                            <th style="width: 140px;">Kode & Tanggal</th>
                            <th>Judul Sesi Audit</th>
                            <th style="width: 170px;">Lingkup & Sasaran</th>
                            <th style="width: 150px;">Auditor</th>
                            <th style="width: 110px;" class="text-center">Status</th>
                            <th style="width: 220px;">Progress & Temuan</th>
                            <th style="width: 130px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($audits)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-clipboard-list fa-3x mb-3 text-secondary d-block" style="opacity: 0.3;"></i>
                                    <p class="mb-1 font-weight-bold">Belum ada sesi audit yang tercatat.</p>
                                    <small class="text-muted">Klik tombol <strong>"Mulai Sesi Audit Baru"</strong> di atas untuk membuat pemeriksaan fisik aset pertama Anda.</small>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($audits as $a): ?>
                                <?php
                                    $tot = (int) ($a['total_item'] ?? 0);
                                    $ses = (int) ($a['total_sesuai'] ?? 0);
                                    $ber = (int) ($a['total_berubah'] ?? 0);
                                    $pjm = (int) ($a['total_dipinjam'] ?? 0);
                                    $sel = (int) ($a['total_selisih'] ?? 0);
                                    $blm = (int) ($a['total_belum'] ?? 0);
                                    $checked = $tot - $blm;
                                    $pct = $tot > 0 ? round(($checked / $tot) * 100) : 0;
                                ?>
                                <tr>
                                    <td class="text-center align-middle"><?= $no++; ?></td>
                                    <td class="align-middle">
                                        <span class="badge badge-light border text-primary font-weight-bold px-2 py-1"><?= esc($a['kode_audit']); ?></span>
                                        <small class="text-muted d-block mt-1"><i class="far fa-calendar-alt mr-1"></i><?= date('d/m/Y', strtotime($a['tanggal_audit'])); ?></small>
                                    </td>
                                    <td class="align-middle">
                                        <a href="<?= site_url('admin/inventaris/audit/' . $a['id']); ?>" class="font-weight-bold text-dark text-decoration-none" title="Buka Workspace Audit">
                                            <?= esc($a['judul_audit']); ?>
                                        </a>
                                        <?php if (! empty($a['catatan'])): ?>
                                            <small class="text-muted d-block text-truncate" style="max-width: 320px;"><?= esc($a['catatan']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <?php if ($a['lingkup_audit'] === 'kantor_ruangan'): ?>
                                            <span class="badge badge-info px-2 py-1"><i class="fas fa-door-open mr-1"></i> Ruangan</span>
                                            <small class="text-dark font-weight-bold d-block mt-1"><?= esc($a['ruangan_nama'] ?: 'Ruangan Terpilih'); ?></small>
                                        <?php elseif ($a['lingkup_audit'] === 'kantor_seluruh'): ?>
                                            <span class="badge badge-primary px-2 py-1"><i class="fas fa-building mr-1"></i> Kantor Seluruh</span>
                                            <small class="text-muted d-block mt-1">Semua Aset Kantor Satker</small>
                                        <?php else: ?>
                                            <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-school mr-1"></i> Sekolah / Mobiler</span>
                                            <small class="text-dark font-weight-bold d-block mt-1"><?= esc($a['sekolah_nama'] ?: 'Seluruh Mobiler Sekolah'); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <span class="text-dark font-weight-bold"><i class="fas fa-user-check text-muted mr-1"></i><?= esc($a['auditor_nama']); ?></span>
                                        <?php if (! empty($a['auditor_nip'])): ?>
                                            <small class="text-muted d-block">NIP: <?= esc($a['auditor_nip']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <?php if ($a['status'] === 'selesai'): ?>
                                            <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Selesai</span>
                                        <?php elseif ($a['status'] === 'berjalan'): ?>
                                            <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-spinner fa-spin mr-1"></i> Berjalan</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary px-2 py-1">Dibatalkan</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="small font-weight-bold"><?= $checked; ?> / <?= $tot; ?> Aset (<?= $pct; ?>%)</span>
                                            <?php if ($blm > 0): ?>
                                                <span class="badge badge-light border text-muted" style="font-size: 0.7rem;"><?= $blm; ?> belum</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="progress" style="height: 6px; border-radius: 4px;">
                                            <div class="progress-bar <?= $pct == 100 ? 'bg-success' : 'bg-primary'; ?>" role="progressbar" style="width: <?= $pct; ?>%;" aria-valuenow="<?= $pct; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <div class="d-flex flex-wrap mt-1" style="gap: 4px; font-size: 0.72rem;">
                                            <span class="badge badge-success px-1.5" title="Sesuai & Ada"><i class="fas fa-check mr-1"></i><?= $ses; ?> Sesuai</span>
                                            <?php if ($pjm > 0): ?>
                                                <span class="badge badge-warning text-dark px-1.5" title="Sedang Dipinjam Pakai Pegawai"><i class="fas fa-file-signature mr-1"></i><?= $pjm; ?> Dipinjam</span>
                                            <?php endif; ?>
                                            <?php if ($ber > 0): ?>
                                                <span class="badge badge-info px-1.5" title="Kondisi Berubah / Pindah Ruangan"><i class="fas fa-exchange-alt mr-1"></i><?= $ber; ?> Berubah</span>
                                            <?php endif; ?>
                                            <?php if ($sel > 0): ?>
                                                <span class="badge badge-danger px-1.5" title="Tidak Ditemukan / Hilang"><i class="fas fa-times mr-1"></i><?= $sel; ?> Hilang</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= site_url('admin/inventaris/audit/' . $a['id']); ?>" class="btn btn-info btn-xs px-2 py-1 shadow-sm" title="Buka Workspace Pemeriksaan Fisik">
                                                <i class="fas fa-tasks"></i>
                                            </a>
                                            <?php if ($menuPermissions['export'] ?? false): ?>
                                                <a href="<?= site_url('admin/inventaris/audit/' . $a['id'] . '/cetak-pdf'); ?>" target="_blank" class="btn btn-danger btn-xs px-2 py-1 shadow-sm" title="Cetak Berita Acara PDF">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                                <a href="<?= site_url('admin/inventaris/audit/' . $a['id'] . '/export-excel'); ?>" class="btn btn-success btn-xs px-2 py-1 shadow-sm" title="Export Spreadsheet Excel">
                                                    <i class="fas fa-file-excel"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($menuPermissions['delete'] ?? false): ?>
                                                <button type="button" 
                                                        class="btn btn-outline-danger btn-xs px-2 py-1 shadow-sm btn-delete-audit" 
                                                        data-id="<?= $a['id']; ?>" 
                                                        data-kode="<?= esc($a['kode_audit']); ?>" 
                                                        data-judul="<?= esc($a['judul_audit']); ?>" 
                                                        title="Hapus Sesi Audit">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
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

<!-- Modal Tambah Sesi Audit -->
<div class="modal fade" id="modalTambahAudit" tabindex="-1" role="dialog" aria-labelledby="modalTambahAuditLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none;">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title font-weight-bold" id="modalTambahAuditLabel">
                    <i class="fas fa-plus-circle mr-2"></i>Mulai Sesi Audit / Stock Opname Baru
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('admin/inventaris/audit/create'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="font-weight-bold text-dark">Judul Sesi Audit <span class="text-danger">*</span></label>
                            <input type="text" name="judul_audit" class="form-control" placeholder="Contoh: Stock Opname Semester I 2026 / Pemeriksaan Ruang TU" required value="<?= 'Audit Fisik Aset ' . date('d F Y'); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold text-dark">Tanggal Pelaksanaan <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_audit" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark d-block mb-2">Pilih Lingkup Audit <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label class="audit-scope-card d-flex flex-column h-100 p-3 border rounded shadow-sm" for="lingkup_ruangan">
                                    <div class="custom-control custom-radio mb-1">
                                        <input type="radio" id="lingkup_ruangan" name="lingkup_audit" value="kantor_ruangan" class="custom-control-input" checked>
                                        <span class="custom-control-label font-weight-bold text-dark">
                                            <i class="fas fa-door-open text-info mr-1"></i> Kantor - Per Ruangan
                                        </span>
                                    </div>
                                    <small class="text-muted d-block" style="padding-left: 1.6rem; font-size: 0.8rem; line-height: 1.35;">
                                        Audit aset yang berlokasi pada ruangan DBR tertentu.
                                    </small>
                                </label>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="audit-scope-card d-flex flex-column h-100 p-3 border rounded shadow-sm" for="lingkup_seluruh">
                                    <div class="custom-control custom-radio mb-1">
                                        <input type="radio" id="lingkup_seluruh" name="lingkup_audit" value="kantor_seluruh" class="custom-control-input">
                                        <span class="custom-control-label font-weight-bold text-dark">
                                            <i class="fas fa-building text-primary mr-1"></i> Kantor - Seluruh Aset
                                        </span>
                                    </div>
                                    <small class="text-muted d-block" style="padding-left: 1.6rem; font-size: 0.8rem; line-height: 1.35;">
                                        Semua aset kantor (termasuk yang dipinjam & non-ruangan).
                                    </small>
                                </label>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="audit-scope-card d-flex flex-column h-100 p-3 border rounded shadow-sm" for="lingkup_sekolah">
                                    <div class="custom-control custom-radio mb-1">
                                        <input type="radio" id="lingkup_sekolah" name="lingkup_audit" value="sekolah" class="custom-control-input">
                                        <span class="custom-control-label font-weight-bold text-dark">
                                            <i class="fas fa-school text-warning mr-1"></i> Sekolah / Mobiler
                                        </span>
                                    </div>
                                    <small class="text-muted d-block" style="padding-left: 1.6rem; font-size: 0.8rem; line-height: 1.35;">
                                        Seluruh aset mobiler binaan sekolah.
                                    </small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Target Selection: Ruangan -->
                    <div id="target_ruangan_wrapper" class="form-group mb-3">
                        <label class="font-weight-bold text-dark">Pilih Ruangan Kantor Target <span class="text-danger">*</span></label>
                        <select name="ruangan_id" id="select_ruangan_id" class="form-control" style="border-radius: 6px;">
                            <option value="">-- Pilih Ruangan DBR --</option>
                            <?php foreach ($ruanganList as $r): ?>
                                <option value="<?= esc($r['id']); ?>"><?= esc($r['kode_ruangan']); ?> - <?= esc($r['nama_ruangan']); ?> <?= ! empty($r['lokasi_lantai']) ? '(' . esc($r['lokasi_lantai']) . ')' : ''; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Target Selection: Sekolah (Opsional / Seluruh Sekolah) -->
                    <div id="target_sekolah_wrapper" class="form-group mb-3" style="display: none;">
                        <label class="font-weight-bold text-dark">Pilih Sekolah Binaan (Opsional)</label>
                        <select name="sekolah_id" id="select_sekolah_id" class="form-control" style="border-radius: 6px;">
                            <option value="">-- Seluruh Sekolah Binaan (Semua Mobiler) --</option>
                            <?php foreach ($sekolahList as $s): ?>
                                <option value="<?= esc($s['id']); ?>"><?= esc($s['nama']); ?> <?= ! empty($s['kabupaten_kota']) ? '(' . esc($s['kabupaten_kota']) . ')' : ''; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Pilih sekolah tertentu atau biarkan opsi default untuk mengaudit seluruh sekolah.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-dark">Nama Auditor / Pemeriksa <span class="text-danger">*</span></label>
                            <input type="text" name="auditor_nama" class="form-control" placeholder="Nama petugas pemeriksa..." value="<?= esc(session()->get('name') ?: (session()->get('username') ?: '')); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-dark">NIP Auditor (Opsional)</label>
                            <input type="text" name="auditor_nip" class="form-control" placeholder="Nomor Induk Pegawai...">
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-dark">Catatan Sesi Audit (Opsional)</label>
                        <textarea name="catatan" class="form-control" rows="2" placeholder="Catatan khusus terkait target pemeriksaan, surat perintah tugas, dll..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 font-weight-bold shadow-sm">
                        <i class="fas fa-play mr-1"></i> Mulai & Buka Workspace
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus Sesi Audit -->
<div class="modal fade" id="modalDeleteAudit" tabindex="-1" role="dialog" aria-labelledby="modalDeleteAuditLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none;">
            <form id="formDeleteAudit" action="" method="post">
                <?= csrf_field(); ?>
                <div class="modal-header bg-danger text-white py-3">
                    <h5 class="modal-title font-weight-bold" id="modalDeleteAuditLabel">
                        <i class="fas fa-trash-alt mr-2"></i>Konfirmasi Hapus Sesi Audit
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 text-center">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h5 class="font-weight-bold text-dark mb-1">Hapus Sesi Audit Ini?</h5>
                    <p class="text-danger font-weight-bold mb-1" id="delKodeAudit" style="font-size: 1.15rem;"></p>
                    <p class="text-muted small mb-3" id="delJudulAudit"></p>
                    <div class="alert alert-warning text-left small mb-0 py-2">
                        <i class="fas fa-info-circle mr-1"></i> <strong>Perhatian:</strong> Seluruh data checklist fisik, temuan aset, dan catatan pemeriksaan pada sesi audit ini akan dihapus secara permanen.
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 justify-content-center">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm font-weight-bold">
                        <i class="fas fa-trash-alt mr-1"></i> Ya, Hapus Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script>
function toggleLingkupInput(val) {
    var rWrapper = document.getElementById('target_ruangan_wrapper');
    var rSelect  = document.getElementById('select_ruangan_id');
    var sWrapper = document.getElementById('target_sekolah_wrapper');

    if (val === 'kantor_ruangan') {
        if (rWrapper) rWrapper.style.display = 'block';
        if (rSelect) rSelect.setAttribute('required', 'required');
        if (sWrapper) sWrapper.style.display = 'none';
    } else if (val === 'sekolah') {
        if (rWrapper) rWrapper.style.display = 'none';
        if (rSelect) rSelect.removeAttribute('required');
        if (sWrapper) sWrapper.style.display = 'block';
    } else {
        if (rWrapper) rWrapper.style.display = 'none';
        if (rSelect) rSelect.removeAttribute('required');
        if (sWrapper) sWrapper.style.display = 'none';
    }
}

$(document).ready(function() {
    function syncScopeCards() {
        var checkedRadio = $('input[name="lingkup_audit"]:checked');
        var checkedVal = checkedRadio.val();
        $('.audit-scope-card').removeClass('active-scope');
        checkedRadio.closest('.audit-scope-card').addClass('active-scope');
        toggleLingkupInput(checkedVal);
    }

    $('input[name="lingkup_audit"]').on('change', function() {
        syncScopeCards();
    });
    syncScopeCards();

    // Event listener modal delete
    $(document).on('click', '.btn-delete-audit', function(e) {
        e.preventDefault();
        var id    = $(this).data('id');
        var kode  = $(this).data('kode');
        var judul = $(this).data('judul');

        $('#delKodeAudit').text(kode);
        $('#delJudulAudit').text(judul || '');
        $('#formDeleteAudit').attr('action', '<?= site_url('admin/inventaris/audit'); ?>/' + id + '/delete');
        $('#modalDeleteAudit').modal('show');
    });
});
</script>
<?= $this->endSection(); ?>
