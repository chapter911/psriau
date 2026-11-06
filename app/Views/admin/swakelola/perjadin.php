<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
$activePaketId = (int) ($filters['paket_id'] ?? 0);
$selectedMatch = (string) ($filters['status_match'] ?? '');
$selectedPersonel = (string) ($filters['nama_personel'] ?? '');
$selectedKota = (string) ($filters['kota_tujuan'] ?? '');
$selectedSpm = (string) ($filters['spm'] ?? '');
$selectedAkun = (string) ($filters['kode_akun'] ?? '');
?>

<div class="container-fluid">

    <!-- Top Banner -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 14px; background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%); color: #ffffff;">
        <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center">
            <div class="mb-3 mb-lg-0">
                <div class="d-flex align-items-center mb-1">
                    <span class="badge badge-warning text-dark font-weight-bold px-2 py-1 mr-2" style="font-size: 0.8rem;">
                        <i class="fas fa-plane-departure mr-1"></i> MODUL SWAKELOLA
                    </span>
                    <span class="badge badge-outline-light text-white font-weight-light" style="border: 1px solid rgba(255,255,255,0.3); font-size: 0.8rem;">
                        Audit LHA No. 09 &amp; POK Revisi 16
                    </span>
                </div>
                <h3 class="font-weight-bold mb-1 text-white">
                    <i class="fas fa-users-cog mr-2 text-warning"></i> Daftar Personel Perjalanan Dinas (Perjadin)
                </h3>
                <p class="text-white-50 mb-0 small" style="max-width: 780px;">
                    Inventarisasi dan monitoring menyeluruh nama-nama personel yang ditugaskan dalam perjalanan dinas biasa (524111), dalam kota (524113), dan rapat paket meeting (524114) pada 4 paket kegiatan swakelola, disinkronkan otomatis dengan database <strong>Master Pegawai</strong>.
                </p>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <?php if (! empty($permissions['edit']) || ! empty($permissions['add'])): ?>
                    <form action="<?= base_url('admin/swakelola/perjadin/sync-master'); ?>" method="post" class="d-inline mr-2 mb-2 mb-md-0" onsubmit="return confirm('Jalankan pencocokan ulang seluruh personel dengan database Master Pegawai?');">
                        <?= csrf_field(); ?>
                        <button type="submit" class="btn btn-warning font-weight-bold shadow-sm px-3 py-2" style="border-radius: 8px;">
                            <i class="fas fa-sync-alt mr-1"></i> Sinkronisasi Master Pegawai
                        </button>
                    </form>
                <?php endif; ?>

                <?php if (! empty($permissions['export'])): ?>
                    <?php
                    $exportQuery = http_build_query(array_filter([
                        'paket_id'      => $activePaketId ?: null,
                        'nama_personel' => $selectedPersonel ?: null,
                        'kota_tujuan'   => $selectedKota ?: null,
                        'status_match'  => $selectedMatch ?: null,
                        'spm'           => $selectedSpm ?: null,
                        'kode_akun'     => $selectedAkun ?: null,
                    ]));
                    $exportUrl = base_url('admin/swakelola/perjadin/export-excel') . ($exportQuery ? '?' . $exportQuery : '');
                    $printUrl  = base_url('admin/swakelola/perjadin/export-pdf') . ($exportQuery ? '?' . $exportQuery : '');
                    ?>
                    <a href="<?= $exportUrl; ?>" class="btn btn-success font-weight-bold shadow-sm px-3 py-2 mr-2 mb-2 mb-md-0" style="border-radius: 8px;">
                        <i class="fas fa-file-excel mr-1"></i> Export Excel (.xlsx)
                    </a>
                    <a href="<?= $printUrl; ?>" target="_blank" class="btn btn-outline-light font-weight-bold shadow-sm px-3 py-2 mb-2 mb-md-0" style="border-radius: 8px;">
                        <i class="fas fa-print mr-1"></i> Cetak / PDF
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 5 KPI Metric Cards -->
    <div class="row mb-4">
        <div class="col-xl col-md-4 col-sm-6 mb-3">
            <div class="card h-100 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #3b82f6 !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small text-uppercase font-weight-bold">Total Perjalanan</span>
                            <h3 class="font-weight-bold mb-0 text-primary mt-1"><?= number_format($stats['total_perjalanan'] ?? 0, 0, ',', '.'); ?></h3>
                            <span class="text-muted" style="font-size: 0.75rem;">Penugasan Terdata</span>
                        </div>
                        <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; background-color: #dbeafe;">
                            <i class="fas fa-plane-departure fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-4 col-sm-6 mb-3">
            <div class="card h-100 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #06b6d4 !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small text-uppercase font-weight-bold">Personel Unik</span>
                            <h3 class="font-weight-bold mb-0 text-info mt-1"><?= number_format($stats['personel_unik'] ?? 0, 0, ',', '.'); ?></h3>
                            <span class="text-muted" style="font-size: 0.75rem;">Individu Berbeda</span>
                        </div>
                        <div class="text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; background-color: #cffafe;">
                            <i class="fas fa-users fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-4 col-sm-6 mb-3">
            <div class="card h-100 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #10b981 !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small text-uppercase font-weight-bold">Master Pegawai</span>
                            <h3 class="font-weight-bold mb-0 text-success mt-1"><?= number_format($stats['matched'] ?? 0, 0, ',', '.'); ?></h3>
                            <span class="text-success font-weight-bold" style="font-size: 0.75rem;">
                                <i class="fas fa-check-circle mr-1"></i> Cocok Database
                            </span>
                        </div>
                        <div class="text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; background-color: #d1fae5;">
                            <i class="fas fa-user-check fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-6 col-sm-6 mb-3">
            <div class="card h-100 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #8b5cf6 !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small text-uppercase font-weight-bold">Non-Master / Eksternal</span>
                            <h3 class="font-weight-bold mb-0 text-purple mt-1" style="color: #8b5cf6;"><?= number_format($stats['unmatched'] ?? 0, 0, ',', '.'); ?></h3>
                            <span class="text-muted" style="font-size: 0.75rem;">
                                <i class="fas fa-id-badge mr-1"></i> Kontrak/Kemenag/TP4D
                            </span>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; background-color: #ede9fe; color: #8b5cf6;">
                            <i class="fas fa-user-tag fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-6 col-sm-12 mb-3">
            <div class="card h-100 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #f59e0b !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small text-uppercase font-weight-bold">Total Biaya Perjadin</span>
                            <h4 class="font-weight-bold mb-0 text-warning mt-1" style="color: #d97706 !important;">
                                Rp <?= number_format($stats['total_biaya'] ?? 0, 0, ',', '.'); ?>
                            </h4>
                            <span class="text-muted" style="font-size: 0.75rem;">Realisasi Definitif SP2D</span>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; background-color: #fef3c7; color: #d97706;">
                            <i class="fas fa-money-bill-wave fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-filter text-primary mr-2"></i> Filter &amp; Pencarian Personel Perjadin
            </h6>
            <a href="<?= base_url('admin/swakelola/perjadin'); ?>" class="btn btn-sm btn-outline-secondary" style="border-radius: 6px;">
                <i class="fas fa-undo mr-1"></i> Reset Filter
            </a>
        </div>
        <div class="card-body">
            <form action="<?= base_url('admin/swakelola/perjadin'); ?>" method="get">
                <div class="row">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="form-label small font-weight-bold text-dark">Paket Swakelola LHA No. 9</label>
                        <select name="paket_id" class="form-control form-control-sm" style="border-radius: 8px;">
                            <option value="">-- Semua 4 Paket Swakelola --</option>
                            <?php foreach ($paketList as $p): ?>
                                <option value="<?= $p['id']; ?>" <?= ((int) $p['id'] === $activePaketId) ? 'selected' : ''; ?>>
                                    Paket <?= (int) ($p['lha_no'] ?? 1); ?> — <?= esc($p['pic_label'] ?? $p['pic_nama']); ?> (<?= esc($p['nama_kegiatan']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="form-label small font-weight-bold text-dark">Nama Personel</label>
                        <input type="text" name="nama_personel" class="form-control form-control-sm" placeholder="Ketik nama personel..." value="<?= esc($selectedPersonel); ?>" style="border-radius: 8px;">
                    </div>

                    <div class="col-lg-2 col-md-4 mb-3">
                        <label class="form-label small font-weight-bold text-dark">Kota / Tujuan</label>
                        <select name="kota_tujuan" class="form-control form-control-sm" style="border-radius: 8px;">
                            <option value="">-- Semua Kota --</option>
                            <?php foreach ($uniqueDestinations as $dest): ?>
                                <option value="<?= esc($dest); ?>" <?= ($dest === $selectedKota) ? 'selected' : ''; ?>>
                                    <?= esc($dest); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-4 mb-3">
                        <label class="form-label small font-weight-bold text-dark">Status Master Pegawai</label>
                        <select name="status_match" class="form-control form-control-sm" style="border-radius: 8px;">
                            <option value="">-- Semua Status --</option>
                            <option value="matched" <?= ($selectedMatch === 'matched') ? 'selected' : ''; ?>>Cocok Master Pegawai</option>
                            <option value="unmatched" <?= ($selectedMatch === 'unmatched') ? 'selected' : ''; ?>>Non-Master / Eksternal</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-4 mb-3">
                        <label class="form-label small font-weight-bold text-dark">Akun MAK</label>
                        <select name="kode_akun" class="form-control form-control-sm" style="border-radius: 8px;">
                            <option value="">-- Semua Akun --</option>
                            <option value="524111" <?= ($selectedAkun === '524111') ? 'selected' : ''; ?>>524111 - Perjadin Biasa</option>
                            <option value="524113" <?= ($selectedAkun === '524113') ? 'selected' : ''; ?>>524113 - Perjadin Dalam Kota</option>
                            <option value="524114" <?= ($selectedAkun === '524114') ? 'selected' : ''; ?>>524114 - Paket Meeting</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-3 col-md-6 mb-2">
                        <label class="form-label small font-weight-bold text-dark">Nomor SPM</label>
                        <input type="text" name="spm" class="form-control form-control-sm" placeholder="Contoh: 00450A, 00445A..." value="<?= esc($selectedSpm); ?>" style="border-radius: 8px;">
                    </div>
                    <div class="col-lg-9 col-md-6 d-flex align-items-end justify-content-end mb-2">
                        <button type="submit" class="btn btn-primary btn-sm px-4 font-weight-bold" style="border-radius: 8px;">
                            <i class="fas fa-search mr-1"></i> Terapkan Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Data Table Card -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 14px;">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center">
            <h5 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-table text-primary mr-2"></i> Data Personel Perjadin Swakelola
            </h5>
            <div class="small text-muted">
                Menampilkan <strong><?= count($records); ?></strong> dari <strong><?= (int) ($stats['total_perjalanan'] ?? 0); ?></strong> penugasan
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0" id="perjadinTable" style="font-size: 0.875rem;">
                    <thead style="background-color: #f1f5f9; color: #334155; font-size: 0.8rem; text-transform: uppercase;">
                        <tr>
                            <th class="text-center py-3" style="width: 45px;">No.</th>
                            <th class="py-3">Personel &amp; Status Master</th>
                            <th class="py-3">Paket Swakelola &amp; Akun</th>
                            <th class="py-3">Tujuan &amp; Jadwal</th>
                            <th class="py-3">Dasar Penugasan (SPT/SPD)</th>
                            <th class="py-3">SPM / SP2D</th>
                            <th class="text-right py-3">Biaya Realisasi</th>
                            <th class="py-3" style="min-width: 220px;">Sumber Berkas &amp; Bukti PDF</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($records)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-info-circle fa-2x mb-3 text-secondary d-block"></i>
                                    Tidak ada data personel perjalanan dinas yang sesuai dengan kriteria filter yang dipilih.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($records as $row):
                                $isMatched   = ((int) ($row['is_matched_master'] ?? 0) === 1);
                                $paketSlug   = (string) ($row['paket_slug'] ?? 'sppg');
                                $fileBukti   = (string) ($row['file_bukti'] ?? '');
                                $fileUrl     = $fileBukti !== '' ? base_url('uploads/swakelola/' . $paketSlug . '/' . rawurlencode($fileBukti)) : '';
                                $nipDisplay  = ! empty($row['master_nip']) ? $row['master_nip'] : ($row['nip'] ?? '-');
                                $golDisplay  = ! empty($row['master_golongan']) ? $row['master_golongan'] : ($row['golongan'] ?? '-');
                                $jabDisplay  = ! empty($row['master_jabatan']) ? $row['master_jabatan'] : ($row['jabatan'] ?? '-');
                            ?>
                                <tr>
                                    <td class="text-center font-weight-bold text-muted"><?= $no++; ?></td>

                                    <!-- Personel & Master Pegawai Status -->
                                    <td>
                                        <div class="font-weight-bold text-dark" style="font-size: 0.95rem;">
                                            <?= esc($row['nama_personel']); ?>
                                        </div>
                                        <div class="mt-1 d-flex flex-wrap align-items-center gap-1">
                                            <?php if ($isMatched): ?>
                                                <span class="badge badge-success px-2 py-1 mr-1" style="font-size: 0.75rem;" title="Data berhasil dicocokkan dengan Master Pegawai">
                                                    <i class="fas fa-check-circle mr-1"></i> Master Pegawai
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary px-2 py-1 mr-1" style="font-size: 0.75rem; background-color: #64748b;" title="Personel luar/kontrak tidak tercantum dalam Master Pegawai internal">
                                                    <i class="fas fa-user-tag mr-1"></i> Non-Master / Eksternal
                                                </span>
                                            <?php endif; ?>

                                            <?php if ($golDisplay !== '-'): ?>
                                                <span class="badge badge-light border text-muted mr-1" style="font-size: 0.72rem;">
                                                    Gol. <?= esc($golDisplay); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-muted small mt-1">
                                            <i class="fas fa-id-card mr-1"></i> NIP: <code><?= esc($nipDisplay); ?></code>
                                        </div>
                                        <div class="text-muted small">
                                            <i class="fas fa-briefcase mr-1"></i> <?= esc($jabDisplay); ?>
                                            <?php if (! empty($row['instansi']) && $row['instansi'] !== 'Satker Pelaksanaan Prasarana Strategis Riau'): ?>
                                                <br><span class="text-primary"><i class="fas fa-building mr-1"></i> <?= esc($row['instansi']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <!-- Paket Swakelola & Akun -->
                                    <td>
                                        <span class="badge badge-primary px-2 py-1 mb-1">
                                            Paket <?= (int) ($row['lha_no'] ?? 1); ?> — <?= esc($row['pic_label'] ?? $row['pic_nama'] ?? 'Swakelola'); ?>
                                        </span>
                                        <div class="small font-weight-bold text-dark" style="max-width: 220px;">
                                            <?= esc($row['nama_paket']); ?>
                                        </div>
                                        <div class="mt-1">
                                            <span class="badge badge-info" style="font-size: 0.75rem;">
                                                <i class="fas fa-tag mr-1"></i> MAK <?= esc($row['kode_akun']); ?>
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Tujuan & Jadwal -->
                                    <td>
                                        <div class="font-weight-bold text-dark">
                                            <i class="fas fa-map-marker-alt text-danger mr-1"></i> <?= esc($row['kota_tujuan']); ?>
                                        </div>
                                        <div class="small text-muted mt-1">
                                            <i class="fas fa-calendar-alt mr-1"></i>
                                            <?= ! empty($row['tanggal_berangkat']) ? date('d/m/Y', strtotime($row['tanggal_berangkat'])) : '-'; ?>
                                            s/d
                                            <?= ! empty($row['tanggal_kembali']) ? date('d/m/Y', strtotime($row['tanggal_kembali'])) : '-'; ?>
                                        </div>
                                        <div class="small text-info font-weight-bold">
                                            <i class="fas fa-clock mr-1"></i> Durasi: <?= (int) ($row['lama_hari'] ?? 1); ?> Hari
                                        </div>
                                    </td>

                                    <!-- Dasar Penugasan (SPT/SPD) -->
                                    <td>
                                        <div class="small">
                                            <strong>SPT:</strong>
                                            <?php if (! empty($row['nomor_spt'])): ?>
                                                <code><?= esc($row['nomor_spt']); ?></code>
                                                <?php if (! empty($row['tanggal_spt'])): ?>
                                                    <span class="text-muted">(<?= date('d/m/Y', strtotime($row['tanggal_spt'])); ?>)</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="small mt-1">
                                            <strong>SPD:</strong>
                                            <?php if (! empty($row['nomor_spd'])): ?>
                                                <code><?= esc($row['nomor_spd']); ?></code>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (! empty($row['maksud_tugas'])): ?>
                                            <div class="small text-muted mt-1" style="max-width: 240px; font-style: italic;">
                                                "<?= esc($row['maksud_tugas']); ?>"
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- SPM / SP2D -->
                                    <td>
                                        <div class="small">
                                            <strong>SPM:</strong>
                                            <span class="badge badge-warning text-dark font-weight-bold">
                                                <?= esc($row['nomor_spm'] ?? '-'); ?>
                                            </span>
                                            <?php if (! empty($row['tanggal_spm'])): ?>
                                                <span class="text-muted small">(<?= date('d/m/Y', strtotime($row['tanggal_spm'])); ?>)</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="small mt-1">
                                            <strong>SP2D:</strong>
                                            <code><?= esc($row['nomor_sp2d'] ?? '-'); ?></code>
                                        </div>
                                    </td>

                                    <!-- Biaya Realisasi -->
                                    <td class="text-right font-weight-bold text-dark" style="font-size: 0.95rem;">
                                        Rp <?= number_format((float) ($row['nilai_biaya'] ?? 0), 0, ',', '.'); ?>
                                    </td>

                                    <!-- Sumber Berkas & Bukti PDF -->
                                    <td>
                                        <?php if ($fileBukti !== ''): ?>
                                            <div class="small font-weight-bold text-dark text-break">
                                                <i class="fas fa-file-pdf text-danger mr-1"></i> <?= esc($fileBukti); ?>
                                            </div>
                                            <div class="text-muted" style="font-size: 0.74rem;">
                                                <i class="fas fa-folder-open text-warning mr-1"></i> <code>uploads/swakelola/<?= esc($paketSlug); ?>/</code>
                                            </div>
                                            <div class="mt-1">
                                                <a href="<?= $fileUrl; ?>" target="_blank" class="btn btn-xs btn-outline-primary shadow-sm px-2 py-0" style="border-radius: 6px;" title="Buka berkas <?= esc($fileBukti); ?>">
                                                    <i class="fas fa-external-link-alt mr-1"></i> Lihat Berkas
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <?php if (! empty($records)): ?>
                        <tfoot style="background-color: #fef3c7; color: #78350f; font-weight: bold;">
                            <tr>
                                <td colspan="6" class="text-right py-3 pr-3">
                                    TOTAL REALISASI BIAYA PERJALANAN DINAS (<?= count($records); ?> PENUGASAN):
                                </td>
                                <td class="text-right py-3 font-weight-bold" style="font-size: 1.05rem; color: #92400e;">
                                    Rp <?= number_format((float) ($stats['total_biaya'] ?? 0), 0, ',', '.'); ?>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

</div>

<?= $this->endSection(); ?>
