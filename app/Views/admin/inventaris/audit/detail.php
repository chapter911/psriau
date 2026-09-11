<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">

    <!-- Header Audit Information -->
    <div class="card shadow-sm mb-3" style="border-radius: 12px; border: 1px solid #e2e8f0;">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start" style="gap: 12px;">
                <div>
                    <div class="d-flex align-items-center flex-wrap mb-1" style="gap: 8px;">
                        <a href="<?= site_url('admin/inventaris/audit'); ?>" class="btn btn-outline-secondary btn-xs px-2 py-1 shadow-sm" style="border-radius: 4px;" title="Kembali ke Daftar Audit">
                            <i class="fas fa-arrow-left mr-1"></i> Daftar Audit
                        </a>
                        <span class="badge badge-primary font-weight-bold px-2.5 py-1" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                            <?= esc($audit['kode_audit']); ?>
                        </span>
                        <?php if ($audit['status'] === 'selesai'): ?>
                            <span class="badge badge-success px-2.5 py-1" id="badge-audit-status" style="font-size: 0.85rem;">
                                <i class="fas fa-lock mr-1"></i> Selesai & Terkunci
                            </span>
                        <?php elseif ($audit['status'] === 'berjalan'): ?>
                            <span class="badge badge-warning text-dark px-2.5 py-1" id="badge-audit-status" style="font-size: 0.85rem;">
                                <i class="fas fa-spinner fa-spin mr-1"></i> Sedang Berjalan
                            </span>
                        <?php else: ?>
                            <span class="badge badge-secondary px-2.5 py-1" id="badge-audit-status" style="font-size: 0.85rem;">Dibatalkan</span>
                        <?php endif; ?>
                    </div>
                    <h2 class="font-weight-bold text-dark mb-1" style="font-size: 1.35rem;">
                        <?= esc($audit['judul_audit']); ?>
                    </h2>
                    <div class="text-muted small d-flex flex-wrap align-items-center" style="gap: 14px;">
                        <span><i class="far fa-calendar-alt mr-1 text-primary"></i> <strong>Tanggal:</strong> <?= date('d F Y', strtotime($audit['tanggal_audit'])); ?></span>
                        <span><i class="fas fa-user-check mr-1 text-success"></i> <strong>Auditor:</strong> <?= esc($audit['auditor_nama']); ?><?= ! empty($audit['auditor_nip']) ? ' (NIP: ' . esc($audit['auditor_nip']) . ')' : ''; ?></span>
                        <span><i class="fas fa-layer-group mr-1 text-info"></i> <strong>Sasaran:</strong> 
                            <?php if ($audit['lingkup_audit'] === 'kantor_ruangan'): ?>
                                Ruangan: <strong><?= esc($audit['ruangan_nama'] ?: 'Ruangan Terpilih'); ?></strong>
                            <?php elseif ($audit['lingkup_audit'] === 'kantor_seluruh'): ?>
                                <strong>Seluruh Aset Kantor Satker</strong>
                            <?php else: ?>
                                <strong>Aset Sekolah / Mobiler</strong>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>

                <!-- Action Toolbar -->
                <div class="d-flex flex-wrap align-items-center" style="gap: 6px;">
                    <?php if ($audit['status'] === 'berjalan'): ?>
                        <button type="button" class="btn btn-outline-success btn-sm font-weight-bold shadow-sm" onclick="confirmMarkRemainingSesuai()" style="border-radius: 6px;">
                            <i class="fas fa-check-double mr-1"></i> Verifikasi Sisa Sesuai
                        </button>
                        <button type="button" class="btn btn-success btn-sm font-weight-bold shadow-sm" onclick="confirmSelesaikanAudit()" style="border-radius: 6px;">
                            <i class="fas fa-lock mr-1"></i> Selesaikan Audit
                        </button>
                    <?php else: ?>
                        <button type="button" class="btn btn-outline-warning btn-sm font-weight-bold shadow-sm text-dark" onclick="confirmBukaKembaliAudit()" style="border-radius: 6px;">
                            <i class="fas fa-unlock mr-1"></i> Buka Kembali Sesi
                        </button>
                    <?php endif; ?>

                    <?php if ($menuPermissions['export'] ?? false): ?>
                        <a href="<?= site_url('admin/inventaris/audit/' . $audit['id'] . '/cetak-pdf'); ?>" target="_blank" class="btn btn-danger btn-sm font-weight-bold shadow-sm" style="border-radius: 6px;" title="Cetak Berita Acara Pemeriksaan Fisik (PDF)">
                            <i class="fas fa-file-pdf mr-1"></i> Cetak BAP (PDF)
                        </a>
                        <a href="<?= site_url('admin/inventaris/audit/' . $audit['id'] . '/export-excel'); ?>" class="btn btn-success btn-sm font-weight-bold shadow-sm" style="border-radius: 6px;" title="Export Data Lengkap ke Excel (.xlsx)">
                            <i class="fas fa-file-excel mr-1"></i> Export Excel
                        </a>
                    <?php endif; ?>

                    <?php if ($menuPermissions['delete'] ?? false): ?>
                        <button type="button" class="btn btn-outline-danger btn-sm font-weight-bold shadow-sm btn-delete-audit" data-id="<?= $audit['id']; ?>" data-kode="<?= esc($audit['kode_audit']); ?>" data-judul="<?= esc($audit['judul_audit']); ?>" style="border-radius: 6px;" title="Hapus Sesi Audit">
                            <i class="fas fa-trash-alt mr-1"></i> Hapus Sesi
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Statistics Cards -->
    <div class="row mb-3">
        <div class="col-6 col-md-2 mb-2 mb-md-0">
            <div class="card shadow-sm text-center py-2 px-1 mb-0" style="border-radius: 10px; border-top: 3px solid #64748b;">
                <span class="text-muted small font-weight-bold text-uppercase" style="font-size: 0.68rem;">Target Total</span>
                <h4 class="font-weight-bold text-dark mb-0" id="stat-total-item"><?= (int) $audit['total_item']; ?></h4>
                <small class="text-muted" style="font-size: 0.7rem;">unit aset</small>
            </div>
        </div>
        <div class="col-6 col-md-2 mb-2 mb-md-0">
            <div class="card shadow-sm text-center py-2 px-1 mb-0" style="border-radius: 10px; border-top: 3px solid #10b981;">
                <span class="text-success small font-weight-bold text-uppercase" style="font-size: 0.68rem;"><i class="fas fa-check-circle mr-1"></i> Sesuai (Ada)</span>
                <h4 class="font-weight-bold text-success mb-0" id="stat-total-sesuai"><?= (int) $audit['total_sesuai']; ?></h4>
                <small class="text-muted" style="font-size: 0.7rem;">kondisi normal</small>
            </div>
        </div>
        <div class="col-6 col-md-2 mb-2 mb-md-0">
            <div class="card shadow-sm text-center py-2 px-1 mb-0" style="border-radius: 10px; border-top: 3px solid #f59e0b;">
                <span class="text-warning small font-weight-bold text-uppercase" style="font-size: 0.68rem;"><i class="fas fa-file-signature mr-1"></i> Dipinjam</span>
                <h4 class="font-weight-bold text-warning mb-0" id="stat-total-dipinjam"><?= (int) $audit['total_dipinjam']; ?></h4>
                <small class="text-muted" style="font-size: 0.7rem;">izin dinas</small>
            </div>
        </div>
        <div class="col-6 col-md-2 mb-2 mb-md-0">
            <div class="card shadow-sm text-center py-2 px-1 mb-0" style="border-radius: 10px; border-top: 3px solid #0284c7;">
                <span class="text-info small font-weight-bold text-uppercase" style="font-size: 0.68rem;"><i class="fas fa-exchange-alt mr-1"></i> Berubah/Pindah</span>
                <h4 class="font-weight-bold text-info mb-0" id="stat-total-berubah"><?= (int) $audit['total_berubah']; ?></h4>
                <small class="text-muted" style="font-size: 0.7rem;">kondisi / ruangan</small>
            </div>
        </div>
        <div class="col-6 col-md-2 mb-2 mb-md-0">
            <div class="card shadow-sm text-center py-2 px-1 mb-0" style="border-radius: 10px; border-top: 3px solid #ef4444;">
                <span class="text-danger small font-weight-bold text-uppercase" style="font-size: 0.68rem;"><i class="fas fa-times-circle mr-1"></i> Hilang/Selisih</span>
                <h4 class="font-weight-bold text-danger mb-0" id="stat-total-selisih"><?= (int) $audit['total_selisih']; ?></h4>
                <small class="text-muted" style="font-size: 0.7rem;">tidak ditemukan</small>
            </div>
        </div>
        <div class="col-6 col-md-2 mb-2 mb-md-0">
            <div class="card shadow-sm text-center py-2 px-1 mb-0" style="border-radius: 10px; border-top: 3px solid #94a3b8;">
                <span class="text-secondary small font-weight-bold text-uppercase" style="font-size: 0.68rem;"><i class="fas fa-hourglass mr-1"></i> Belum Dicek</span>
                <h4 class="font-weight-bold text-secondary mb-0" id="stat-total-belum"><?= (int) $audit['total_belum']; ?></h4>
                <small class="text-muted" style="font-size: 0.7rem;">menunggu audit</small>
            </div>
        </div>
    </div>

    <!-- Room Navigation Tabs / Pills (Divided per Ruangan) -->
    <?php if (! empty($roomStats)): ?>
        <div class="card shadow-sm mb-3" style="border-radius: 12px; border: 1px solid #e2e8f0;">
            <div class="card-header bg-white py-2 px-3 border-bottom d-flex flex-wrap justify-content-between align-items-center" style="gap: 8px;">
                <div class="d-flex align-items-center">
                    <span class="font-weight-bold text-dark mr-2" style="font-size: 0.92rem;">
                        <i class="fas fa-door-open text-primary mr-1"></i> Partisi Ruangan Audit
                    </span>
                    <span class="badge badge-light border text-muted small"><?= count($roomStats); ?> Ruangan Terlibat</span>
                </div>
                <div class="small text-muted">
                    Klik salah satu ruangan di bawah untuk fokus memeriksa aset di ruangan tersebut:
                </div>
            </div>
            <div class="card-body p-2.5">
                <div class="d-flex flex-wrap" style="gap: 8px;">
                    <!-- Pill: Semua Ruangan -->
                    <?php
                        $isAllActive = ($activeRuangan === 'all');
                        $totalAuditItems = (int) $audit['total_item'];
                        $totalAuditChecked = $totalAuditItems - (int) $audit['total_belum'];
                        $allPercent = $totalAuditItems > 0 ? round(($totalAuditChecked / $totalAuditItems) * 100) : 0;
                    ?>
                    <a href="<?= site_url('admin/inventaris/audit/' . $audit['id'] . '?ruangan=all' . ($filterStatusAudit !== 'semua' ? '&status_audit=' . esc($filterStatusAudit) : '') . (! empty($keyword) ? '&q=' . esc($keyword) : '')); ?>"
                       class="btn btn-sm <?= $isAllActive ? 'btn-primary shadow-sm' : 'btn-outline-secondary'; ?> d-flex align-items-center"
                       style="border-radius: 8px; padding: 6px 12px; gap: 8px;">
                        <div>
                            <div class="font-weight-bold" style="font-size: 0.84rem;"><i class="fas fa-building mr-1"></i> Semua Ruangan</div>
                            <small class="<?= $isAllActive ? 'text-white-50' : 'text-muted'; ?>" style="font-size: 0.7rem;">
                                <?= $totalAuditChecked; ?> / <?= $totalAuditItems; ?> unit (<?= $allPercent; ?>%)
                            </small>
                        </div>
                        <?php if ($allPercent === 100): ?>
                            <span class="badge badge-success px-1.5 py-0.5"><i class="fas fa-check"></i></span>
                        <?php endif; ?>
                    </a>

                    <!-- Pills per Room -->
                    <?php foreach ($roomStats as $rs): ?>
                        <?php
                            $isRoomActive = ($activeRuangan === $rs['ruangan_key']);
                            $isRoomDone = ($rs['total_item'] > 0 && $rs['total_belum'] === 0);
                            $checkedCount = $rs['total_item'] - $rs['total_belum'];
                        ?>
                        <a href="<?= site_url('admin/inventaris/audit/' . $audit['id'] . '?ruangan=' . esc($rs['ruangan_key']) . ($filterStatusAudit !== 'semua' ? '&status_audit=' . esc($filterStatusAudit) : '') . (! empty($keyword) ? '&q=' . esc($keyword) : '')); ?>"
                           class="btn btn-sm <?= $isRoomActive ? 'btn-primary shadow-sm' : ($isRoomDone ? 'btn-light border-success text-dark' : 'btn-light border text-dark'); ?> d-flex align-items-center position-relative"
                           style="border-radius: 8px; padding: 6px 12px; gap: 8px; text-align: left;">
                            <div>
                                <div class="font-weight-bold" style="font-size: 0.84rem;">
                                    <i class="fas fa-door-closed mr-1 <?= $isRoomActive ? 'text-white' : ($isRoomDone ? 'text-success' : 'text-secondary'); ?>"></i>
                                    <?= esc($rs['ruangan_nama'] ?? $rs['nama_ruangan'] ?? 'Ruangan'); ?>
                                </div>
                                <div class="d-flex align-items-center" style="gap: 6px;">
                                    <small class="<?= $isRoomActive ? 'text-white-50' : 'text-muted'; ?>" style="font-size: 0.7rem;">
                                        <?= $checkedCount; ?> / <?= $rs['total_item']; ?> unit (<?= $rs['persen']; ?>%)
                                    </small>
                                    <div class="progress" style="width: 45px; height: 5px; border-radius: 3px; background-color: <?= $isRoomActive ? 'rgba(255,255,255,0.3)' : '#e2e8f0'; ?>;">
                                        <div class="progress-bar <?= $isRoomDone ? 'bg-success' : ($isRoomActive ? 'bg-white' : 'bg-primary'); ?>" style="width: <?= $rs['persen']; ?>%;"></div>
                                    </div>
                                </div>
                            </div>
                            <?php if ($isRoomDone): ?>
                                <span class="badge <?= $isRoomActive ? 'badge-light text-primary' : 'badge-success'; ?> px-1.5 py-0.5" title="Ruangan selesai diperiksa">
                                    <i class="fas fa-check"></i>
                                </span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Selected Room Context Card (when focusing on a specific room) -->
    <?php if ($selectedRoomInfo): ?>
        <div class="card shadow-sm mb-3" style="border-radius: 12px; border-left: 5px solid #0A66C2; border-top: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
            <div class="card-body py-3 px-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 12px;">
                    <div>
                        <div class="d-flex align-items-center flex-wrap mb-1" style="gap: 8px;">
                            <span class="badge badge-primary px-2 py-1 font-weight-bold" style="font-size: 0.8rem;">
                                <i class="fas fa-door-open mr-1"></i> FOKUS RUANGAN
                            </span>
                            <h4 class="font-weight-bold text-dark mb-0" style="font-size: 1.15rem;">
                                <?= esc($selectedRoomInfo['ruangan_nama'] ?? $selectedRoomInfo['nama_ruangan'] ?? 'Ruangan'); ?>
                            </h4>
                        </div>
                        <div class="text-muted small d-flex flex-wrap align-items-center" style="gap: 12px;">
                            <span><i class="fas fa-user-tie text-secondary mr-1"></i> <strong>Penanggung Jawab:</strong> <?= esc($selectedRoomInfo['penanggung_jawab_nama'] ?: 'Belum ditetapkan'); ?><?= ! empty($selectedRoomInfo['penanggung_jawab_nip']) ? ' (NIP: ' . esc($selectedRoomInfo['penanggung_jawab_nip']) . ')' : ''; ?></span>
                            <?php if (! empty($selectedRoomInfo['lokasi_lantai'])): ?>
                                <span><i class="fas fa-layer-group text-info mr-1"></i> <?= esc($selectedRoomInfo['lokasi_lantai']); ?></span>
                            <?php endif; ?>
                            <span><i class="fas fa-boxes text-primary mr-1"></i> Target Ruangan: <strong><?= $selectedRoomInfo['total_item']; ?> Unit</strong></span>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
                        <span class="badge badge-light border text-dark font-weight-bold px-3 py-2" style="font-size: 0.85rem;">
                            Selesai: <?= $selectedRoomInfo['total_item'] - $selectedRoomInfo['total_belum']; ?> / <?= $selectedRoomInfo['total_item']; ?> (<?= $selectedRoomInfo['persen']; ?>%)
                        </span>
                        <?php if ($audit['status'] === 'berjalan' && $selectedRoomInfo['total_belum'] > 0 && ($menuPermissions['edit'] ?? false)): ?>
                            <button type="button" class="btn btn-outline-success btn-sm font-weight-bold shadow-sm" onclick="confirmMarkRuanganSesuai('<?= esc($selectedRoomInfo['ruangan_key']); ?>', '<?= esc($selectedRoomInfo['ruangan_nama'] ?? $selectedRoomInfo['nama_ruangan'] ?? 'Ruangan'); ?>')" style="border-radius: 6px;">
                                <i class="fas fa-check-double mr-1"></i> Tandai Sisa Ruangan Ini Selesai
                            </button>
                        <?php endif; ?>
                        <a href="<?= site_url('admin/inventaris/audit/' . $audit['id'] . '?ruangan=all' . ($filterStatusAudit !== 'semua' ? '&status_audit=' . esc($filterStatusAudit) : '') . (! empty($keyword) ? '&q=' . esc($keyword) : '')); ?>" class="btn btn-light border btn-sm text-muted font-weight-bold" style="border-radius: 6px;" title="Kembali ke Semua Ruangan">
                            <i class="fas fa-times mr-1"></i> Reset Fokus
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Scanner & Quick-Search Box -->
    <div class="card shadow-sm mb-3" style="border-radius: 12px; background: linear-gradient(135deg, #0A66C2 0%, #004182 100%); color: white;">
        <div class="card-body py-3 px-4">
            <div class="row align-items-center">
                <div class="col-md-5 mb-2 mb-md-0">
                    <h5 class="mb-1 font-weight-bold text-white"><i class="fas fa-qrcode mr-2"></i>Mode Scan Cepat (Barcode / QR Code / NUP)</h5>
                    <p class="small text-white-50 mb-0">Arahkan scanner USB/Kamera ke barcode aset atau ketik NUP/Kode Barang lalu tekan <strong>Enter</strong> untuk menandai fisik aset secara instan.</p>
                </div>
                <div class="col-md-7">
                    <div class="input-group shadow-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-0 text-primary font-weight-bold">
                                <i class="fas fa-barcode fa-lg"></i>
                            </span>
                        </div>
                        <input type="text" id="inputFastScan" class="form-control form-control-lg border-0" placeholder="Scan Barcode atau masukkan Kode Barang / NUP di sini..." autocomplete="off" <?= $audit['status'] === 'selesai' ? 'disabled' : ''; ?>>
                        <div class="input-group-append">
                            <button type="button" id="btnSubmitScan" class="btn btn-warning font-weight-bold px-4 text-dark" <?= $audit['status'] === 'selesai' ? 'disabled' : ''; ?>>
                                <i class="fas fa-check mr-1"></i> Periksa
                            </button>
                        </div>
                    </div>
                    <div id="scanFeedback" class="small mt-1 text-white font-weight-bold" style="display: none; min-height: 18px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table & Filter Tabs -->
    <div class="card shadow-sm" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white py-2 px-3" style="border-bottom: 1px solid #e2e8f0;">
            <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 8px;">
                <!-- Filter Tabs -->
                <ul class="nav nav-pills small" style="gap: 4px;">
                    <?php
                        $activeTab = $filterStatusAudit;
                        $tabLinks = [
                            'semua'                  => 'Semua (' . count($items) . ')',
                            'belum_diperiksa'        => 'Belum Diperiksa',
                            'sesuai'                 => 'Sesuai',
                            'terkonfirmasi_dipinjam' => 'Dipinjam',
                            'kondisi_berubah'        => 'Kondisi Berubah',
                            'salah_lokasi'           => 'Salah Lokasi',
                            'tidak_ditemukan'        => 'Tidak Ditemukan',
                        ];
                    ?>
                    <?php foreach ($tabLinks as $key => $label): ?>
                        <li class="nav-item">
                            <a class="nav-link py-1 px-2.5 <?= ($activeTab === $key) ? 'active' : 'text-dark bg-light'; ?>" style="border-radius: 6px;" href="<?= site_url('admin/inventaris/audit/' . $audit['id'] . '?ruangan=' . esc($activeRuangan) . '&status_audit=' . $key . (! empty($keyword) ? '&q=' . esc($keyword) : '')); ?>">
                                <?= $label; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <!-- Text Search -->
                <form method="get" action="<?= site_url('admin/inventaris/audit/' . $audit['id']); ?>" class="form-inline m-0">
                    <input type="hidden" name="ruangan" value="<?= esc($activeRuangan); ?>">
                    <input type="hidden" name="status_audit" value="<?= esc($filterStatusAudit); ?>">
                    <div class="input-group input-group-sm">
                        <input type="text" name="q" class="form-control" placeholder="Cari nama / kode / NUP..." value="<?= esc($keyword ?? ''); ?>" style="border-radius: 6px 0 0 6px;">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary" style="border-radius: 0 6px 6px 0;"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle" id="tableAuditItems">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 40px;" class="text-center">#</th>
                            <th style="width: 130px;">Kode & NUP</th>
                            <th>Nama Barang & Spesifikasi</th>
                            <th style="width: 140px;">Lokasi Sistem</th>
                            <th style="width: 100px;" class="text-center">Kondisi Sistem</th>
                            <th style="width: 160px;" class="text-center">Status Pinjam</th>
                            <th style="width: 170px;" class="text-center">Hasil Audit Fisik</th>
                            <th style="width: 130px;" class="text-center">Aksi Cepat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-box-open fa-3x mb-3 text-secondary d-block" style="opacity: 0.3;"></i>
                                    <p class="mb-0 font-weight-bold">Tidak ada item aset yang sesuai dengan filter.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($items as $item): ?>
                                <tr id="row-item-<?= $item['id']; ?>" class="item-row <?= ($item['status_audit'] !== 'belum_diperiksa') ? 'table-row-checked' : ''; ?>">
                                    <td class="text-center align-middle text-muted small"><?= $no++; ?></td>
                                    <td class="align-middle">
                                        <span class="font-weight-bold text-dark d-block"><?= esc($item['kode_barang']); ?></span>
                                        <span class="badge badge-light border text-primary font-weight-bold px-1.5 py-0.5">NUP: <?= esc($item['nup'] ?: '-'); ?></span>
                                    </td>
                                    <td class="align-middle">
                                        <span class="font-weight-bold text-dark d-block item-nama"><?= esc($item['nama_barang']); ?></span>
                                        <?php if (! empty($item['merk_tipe'])): ?>
                                            <small class="text-muted d-block"><?= esc($item['merk_tipe']); ?></small>
                                        <?php endif; ?>
                                        <small class="text-muted" style="font-size: 0.72rem;"><?= esc($item['satuan'] ?: 'Unit'); ?> &bull; Peruntukan: <?= ucfirst($item['peruntukan'] ?: 'kantor'); ?></small>
                                    </td>
                                    <td class="align-middle small">
                                        <?php if (! empty($item['ruangan_sistem_nama'])): ?>
                                            <a href="<?= site_url('admin/inventaris/audit/' . $audit['id'] . '?ruangan=' . esc($item['ruangan_sistem_id'] ?: 'non_ruangan')); ?>" class="text-dark font-weight-bold" title="Klik untuk fokus ke ruangan ini">
                                                <i class="fas fa-door-open text-primary mr-1"></i><?= esc($item['ruangan_sistem_nama']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted"><i class="fas fa-map-marker-alt text-secondary mr-1"></i><?= esc($item['peruntukan'] === 'mobiler' ? 'Sekolah / Mobiler' : 'Belum Ditentukan'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <?php
                                            $kBadge = 'badge-success';
                                            if (strtolower($item['kondisi_sistem']) === 'rusak ringan') $kBadge = 'badge-warning text-dark';
                                            elseif (strtolower($item['kondisi_sistem']) === 'rusak berat') $kBadge = 'badge-danger';
                                        ?>
                                        <span class="badge <?= $kBadge; ?> px-2 py-1"><?= esc($item['kondisi_sistem']); ?></span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <?php if ($item['status_pinjam_sistem'] === 'dipinjam'): ?>
                                            <span class="badge badge-warning text-dark px-2 py-1 font-weight-bold" title="Surat: <?= esc($item['no_surat_pinjam'] ?: '-'); ?>">
                                                <i class="fas fa-user-clock mr-1"></i> Dipinjam
                                            </span>
                                            <small class="text-dark d-block mt-0.5 font-weight-bold"><?= esc($item['peminjam_nama']); ?></small>
                                            <?php if (! empty($item['no_surat_pinjam'])): ?>
                                                <small class="text-muted d-block" style="font-size: 0.68rem;"><?= esc($item['no_surat_pinjam']); ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge badge-light border text-muted px-2 py-1">
                                                <i class="fas fa-building mr-1"></i> Di Kantor
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center align-middle" id="cell-status-<?= $item['id']; ?>">
                                        <?php if ($item['status_audit'] === 'sesuai'): ?>
                                            <span class="badge badge-success px-2 py-1 font-weight-bold"><i class="fas fa-check mr-1"></i> Sesuai</span>
                                            <small class="text-muted d-block mt-1">Fisik: <?= esc($item['kondisi_fisik'] ?: $item['kondisi_sistem']); ?></small>
                                        <?php elseif ($item['status_audit'] === 'terkonfirmasi_dipinjam'): ?>
                                            <span class="badge badge-warning text-dark px-2 py-1 font-weight-bold"><i class="fas fa-file-signature mr-1"></i> Terkonfirmasi Dipinjam</span>
                                        <?php elseif ($item['status_audit'] === 'kondisi_berubah'): ?>
                                            <span class="badge badge-info px-2 py-1 font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i> Kondisi Berubah</span>
                                            <small class="text-danger font-weight-bold d-block mt-1">Fisik: <?= esc($item['kondisi_fisik']); ?></small>
                                        <?php elseif ($item['status_audit'] === 'salah_lokasi'): ?>
                                            <span class="badge badge-secondary px-2 py-1 font-weight-bold"><i class="fas fa-exchange-alt mr-1"></i> Pindah Ruangan</span>
                                            <small class="text-primary font-weight-bold d-block mt-1"><?= esc($item['ruangan_fisik_nama'] ?: 'Ruang Lain'); ?></small>
                                        <?php elseif ($item['status_audit'] === 'tidak_ditemukan'): ?>
                                            <span class="badge badge-danger px-2 py-1 font-weight-bold"><i class="fas fa-times mr-1"></i> Tidak Ditemukan</span>
                                        <?php else: ?>
                                            <span class="badge badge-light border text-secondary px-2 py-1">Belum Diperiksa</span>
                                        <?php endif; ?>

                                        <?php if (! empty($item['catatan_pemeriksaan'])): ?>
                                            <small class="text-muted d-block font-italic" style="font-size: 0.7rem;">"<?= esc($item['catatan_pemeriksaan']); ?>"</small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <?php if ($audit['status'] === 'berjalan'): ?>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-success btn-xs px-2 py-1 shadow-sm" onclick="markItemSesuaiQuick(<?= $item['id']; ?>)" title="Tandai Sesuai & Ada">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-primary btn-xs px-2 py-1 shadow-sm" onclick="openModalEditItem(<?= htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8'); ?>)" title="Ubah Kondisi / Lokasi / Catatan Temuan">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge badge-light text-muted small"><i class="fas fa-lock"></i> Terkunci</span>
                                        <?php endif; ?>
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

<!-- Modal Update Temuan Item -->
<div class="modal fade" id="modalEditItemAudit" tabindex="-1" role="dialog" aria-labelledby="modalEditItemAuditLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none;">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title font-weight-bold" id="modalEditItemAuditLabel">
                    <i class="fas fa-clipboard-check mr-2"></i>Hasil Pemeriksaan Fisik Aset
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formUpdateItemAudit" onsubmit="submitFormUpdateItem(event)">
                <input type="hidden" name="item_id" id="edit_item_id">
                <div class="modal-body p-4">
                    <div class="alert alert-light border mb-3 py-2 px-3" style="border-radius: 8px;">
                        <span class="font-weight-bold text-dark d-block" id="info_item_nama">Nama Barang</span>
                        <small class="text-muted d-block" id="info_item_kode_nup">Kode | NUP</small>
                        <small class="text-muted d-block" id="info_item_pinjam"></small>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark">Status Verifikasi Fisik <span class="text-danger">*</span></label>
                        <select name="status_audit" id="edit_status_audit" class="form-control" required onchange="onStatusAuditChange(this.value)">
                            <option value="sesuai">✅ Sesuai & Ada di Lokasi</option>
                            <option value="terkonfirmasi_dipinjam">📋 Terkonfirmasi Sedang Dipinjam Pegawai (Sah)</option>
                            <option value="kondisi_berubah">⚠️ Fisik Ada, Namun Kondisi Berubah</option>
                            <option value="salah_lokasi">🔄 Salah Lokasi / Fisik Berada di Ruangan Lain</option>
                            <option value="tidak_ditemukan">❌ Tidak Ditemukan / Hilang (Selisih Kurang)</option>
                        </select>
                    </div>

                    <div class="form-group mb-3" id="wrapper_kondisi_fisik">
                        <label class="font-weight-bold text-dark">Kondisi Fisik Saat Ini <span class="text-danger">*</span></label>
                        <select name="kondisi_fisik" id="edit_kondisi_fisik" class="form-control">
                            <option value="Baik">Baik</option>
                            <option value="Rusak Ringan">Rusak Ringan</option>
                            <option value="Rusak Berat">Rusak Berat</option>
                        </select>
                    </div>

                    <div class="form-group mb-3" id="wrapper_ruangan_fisik" style="display: none;">
                        <label class="font-weight-bold text-dark">Fisik Ditemukan di Ruangan Mana?</label>
                        <select name="ruangan_fisik_id" id="edit_ruangan_fisik_id" class="form-control">
                            <option value="">-- Pilih Ruangan Temuan --</option>
                            <?php foreach ($ruanganList as $r): ?>
                                <option value="<?= esc($r['id']); ?>"><?= esc($r['kode_ruangan']); ?> - <?= esc($r['nama_ruangan']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-dark">Catatan Pemeriksaan Temuan (Opsional)</label>
                        <textarea name="catatan_pemeriksaan" id="edit_catatan_pemeriksaan" class="form-control" rows="2" placeholder="Tuliskan catatan kondisi, letak fisik, nomor seri, dll..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 font-weight-bold shadow-sm">
                        <i class="fas fa-save mr-1"></i> Simpan Hasil Audit
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
            <form id="formDeleteAudit" action="<?= site_url('admin/inventaris/audit/' . $audit['id'] . '/delete'); ?>" method="post">
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
                    <p class="text-danger font-weight-bold mb-1" style="font-size: 1.15rem;"><?= esc($audit['kode_audit']); ?></p>
                    <p class="text-muted small mb-3"><?= esc($audit['judul_audit']); ?></p>
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

<!-- Forms for actions -->
<form id="formActionPost" action="" method="post" style="display: none;">
    <?= csrf_field(); ?>
</form>

<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script>
var ajaxUrl = '<?= site_url('admin/inventaris/audit/' . $audit['id'] . '/update-item'); ?>';

// Inisialisasi focus ke input scanner
$(document).ready(function() {
    $('#inputFastScan').focus();

    $('#inputFastScan').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            executeFastScan();
        }
    });

    $('#btnSubmitScan').on('click', function() {
        executeFastScan();
    });
});

function executeFastScan() {
    var val = $('#inputFastScan').val().trim();
    if (!val) return;

    $('#scanFeedback').show().html('<i class="fas fa-spinner fa-spin mr-1"></i> Memeriksa ' + val + '...');

    $.ajax({
        url: ajaxUrl,
        type: 'POST',
        data: {
            scan_keyword: val,
            '<?= csrf_token(); ?>': '<?= csrf_hash(); ?>'
        },
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                $('#scanFeedback').html('<i class="fas fa-check-circle mr-1 text-light"></i> ' + res.message);
                $('#inputFastScan').val('').focus();
                updateRowUI(res.item);
                updateStatsUI(res.stats);
            } else {
                $('#scanFeedback').html('<i class="fas fa-exclamation-circle mr-1 text-warning"></i> ' + res.message);
                $('#inputFastScan').select();
            }
        },
        error: function() {
            $('#scanFeedback').html('<i class="fas fa-times-circle mr-1 text-danger"></i> Gagal menghubungi server.');
        }
    });
}

function markItemSesuaiQuick(itemId) {
    $.ajax({
        url: ajaxUrl,
        type: 'POST',
        data: {
            item_id: itemId,
            status_audit: 'sesuai',
            '<?= csrf_token(); ?>': '<?= csrf_hash(); ?>'
        },
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                updateRowUI(res.item);
                updateStatsUI(res.stats);
            } else {
                alert(res.message);
            }
        },
        error: function() {
            alert('Gagal memperbarui status item.');
        }
    });
}

function openModalEditItem(item) {
    $('#edit_item_id').val(item.id);
    $('#info_item_nama').text(item.nama_barang);
    $('#info_item_kode_nup').text('Kode: ' + item.kode_barang + ' | NUP: ' + (item.nup || '-'));
    
    if (item.status_pinjam_sistem === 'dipinjam') {
        $('#info_item_pinjam').html('<span class="badge badge-warning text-dark"><i class="fas fa-file-signature mr-1"></i> Sedang Dipinjam oleh: ' + (item.peminjam_nama || '-') + '</span>');
    } else {
        $('#info_item_pinjam').empty();
    }

    var st = (item.status_audit && item.status_audit !== 'belum_diperiksa') ? item.status_audit : (item.status_pinjam_sistem === 'dipinjam' ? 'terkonfirmasi_dipinjam' : 'sesuai');
    $('#edit_status_audit').val(st);
    $('#edit_kondisi_fisik').val(item.kondisi_fisik || item.kondisi_sistem || 'Baik');
    $('#edit_ruangan_fisik_id').val(item.ruangan_fisik_id || '');
    $('#edit_catatan_pemeriksaan').val(item.catatan_pemeriksaan || '');

    onStatusAuditChange(st);
    $('#modalEditItemAudit').modal('show');
}

function onStatusAuditChange(val) {
    if (val === 'salah_lokasi') {
        $('#wrapper_ruangan_fisik').slideDown();
    } else {
        $('#wrapper_ruangan_fisik').slideUp();
    }
}

function submitFormUpdateItem(e) {
    e.preventDefault();
    var formData = $('#formUpdateItemAudit').serializeArray();
    formData.push({name: '<?= csrf_token(); ?>', value: '<?= csrf_hash(); ?>'});

    $.ajax({
        url: ajaxUrl,
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                $('#modalEditItemAudit').modal('hide');
                updateRowUI(res.item);
                updateStatsUI(res.stats);
            } else {
                alert(res.message);
            }
        },
        error: function() {
            alert('Gagal menyimpan hasil audit.');
        }
    });
}

function updateRowUI(item) {
    var row = $('#row-item-<?= '' ?>' + item.id);
    if (!row.length) {
        location.reload();
        return;
    }

    row.addClass('table-row-checked');

    var cellStatus = $('#cell-status-' + item.id);
    var badgeHtml = '';

    if (item.status_audit === 'sesuai') {
        badgeHtml = '<span class="badge badge-success px-2 py-1 font-weight-bold"><i class="fas fa-check mr-1"></i> Sesuai</span>' +
                    '<small class="text-muted d-block mt-1">Fisik: ' + (item.kondisi_fisik || item.kondisi_sistem) + '</small>';
    } else if (item.status_audit === 'terkonfirmasi_dipinjam') {
        badgeHtml = '<span class="badge badge-warning text-dark px-2 py-1 font-weight-bold"><i class="fas fa-file-signature mr-1"></i> Terkonfirmasi Dipinjam</span>';
    } else if (item.status_audit === 'kondisi_berubah') {
        badgeHtml = '<span class="badge badge-info px-2 py-1 font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i> Kondisi Berubah</span>' +
                    '<small class="text-danger font-weight-bold d-block mt-1">Fisik: ' + item.kondisi_fisik + '</small>';
    } else if (item.status_audit === 'salah_lokasi') {
        badgeHtml = '<span class="badge badge-secondary px-2 py-1 font-weight-bold"><i class="fas fa-exchange-alt mr-1"></i> Pindah Ruangan</span>' +
                    '<small class="text-primary font-weight-bold d-block mt-1">' + (item.ruangan_fisik_nama || 'Ruang Lain') + '</small>';
    } else if (item.status_audit === 'tidak_ditemukan') {
        badgeHtml = '<span class="badge badge-danger px-2 py-1 font-weight-bold"><i class="fas fa-times mr-1"></i> Tidak Ditemukan</span>';
    }

    if (item.catatan_pemeriksaan) {
        badgeHtml += '<small class="text-muted d-block font-italic" style="font-size: 0.7rem;">"' + item.catatan_pemeriksaan + '"</small>';
    }

    cellStatus.html(badgeHtml);

    // Animasi flash baris
    row.css('background-color', '#ecfdf5');
    setTimeout(function() {
        row.css('background-color', '');
    }, 1500);
}

function updateStatsUI(stats) {
    if (!stats) return;
    $('#stat-total-item').text(stats.total_item);
    $('#stat-total-sesuai').text(stats.total_sesuai);
    $('#stat-total-dipinjam').text(stats.total_dipinjam);
    $('#stat-total-berubah').text(stats.total_berubah);
    $('#stat-total-selisih').text(stats.total_selisih);
    $('#stat-total-belum').text(stats.total_belum);
}

function confirmMarkRemainingSesuai() {
    if (confirm('Verifikasi dan tandai seluruh sisa barang yang BELUM diperiksa menjadi SESUAI? Aset yang sedang dipinjam pakai akan otomatis terkonfirmasi dipinjam.')) {
        var f = document.getElementById('formActionPost');
        f.action = '<?= site_url('admin/inventaris/audit/' . $audit['id'] . '/mark-all-sesuai'); ?>';
        f.submit();
    }
}

function confirmMarkRuanganSesuai(ruanganKey, roomName) {
    if (confirm('Tandai seluruh sisa aset di ruangan "' + roomName + '" yang BELUM diperiksa menjadi SESUAI?\n\nAset yang sedang berstatus dipinjam pakai akan otomatis terkonfirmasi dipinjam.')) {
        var f = document.getElementById('formActionPost');
        f.action = '<?= site_url('admin/inventaris/audit/' . $audit['id'] . '/mark-ruangan-sesuai/'); ?>' + encodeURIComponent(ruanganKey);
        f.submit();
    }
}

function confirmSelesaikanAudit() {
    if (confirm('Selesaikan sesi audit ini? Setelah diselesaikan, data audit akan terkunci dan dapat dicetak sebagai Berita Acara resmi.')) {
        var f = document.getElementById('formActionPost');
        f.action = '<?= site_url('admin/inventaris/audit/' . $audit['id'] . '/selesai'); ?>';
        f.submit();
    }
}

function confirmBukaKembaliAudit() {
    if (confirm('Buka kembali sesi audit ini untuk melanjutkan atau merevisi pemeriksaan?')) {
        var f = document.getElementById('formActionPost');
        f.action = '<?= site_url('admin/inventaris/audit/' . $audit['id'] . '/buka-kembali'); ?>';
        f.submit();
    }
}

$(document).on('click', '.btn-delete-audit', function(e) {
    e.preventDefault();
    $('#modalDeleteAudit').modal('show');
});
</script>
<?= $this->endSection(); ?>
