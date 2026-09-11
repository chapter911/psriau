<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<style>
    .font-mono {
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace !important;
    }
    .dbr-hero-card {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.04);
        background: #ffffff;
    }
    .dbr-kpi-tile {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px 14px;
        transition: all 0.2s ease;
    }
    .dbr-kpi-tile:hover {
        background: #ffffff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        transform: translateY(-1px);
    }
    .dbr-nav-pills .nav-link {
        border-radius: 8px;
        color: #475569;
        padding: 8px 16px;
        transition: all 0.2s ease;
        border: 1px solid transparent;
        font-size: 0.92rem;
    }
    .dbr-nav-pills .nav-link:hover {
        color: #0284c7;
        background-color: #f1f5f9;
    }
    .dbr-nav-pills .nav-link.active,
    .dbr-nav-pills .nav-link.active:hover {
        color: #ffffff !important;
        background: #007bff !important;
        box-shadow: 0 2px 6px rgba(0, 123, 255, 0.25) !important;
    }
    .dbr-nav-pills .nav-link.active i,
    .dbr-nav-pills .nav-link.active span {
        color: #ffffff !important;
    }
    .dbr-nav-pills .nav-link.active .badge {
        background-color: rgba(255, 255, 255, 0.25) !important;
        color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.4) !important;
    }
    .table-dbr,
    .table-modal-dbr {
        width: 100% !important;
    }
    .table-dbr thead th,
    .table-modal-dbr thead th {
        font-size: 0.78rem !important;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        padding: 12px 14px !important;
        padding-right: 28px !important;
        white-space: nowrap !important;
        vertical-align: middle !important;
        line-height: 1.4 !important;
    }
    .table-dbr tbody td,
    .table-modal-dbr tbody td {
        padding: 10px 14px !important;
        vertical-align: middle !important;
    }
    /* Pastikan header DataTables selalu 100% lebar container */
    .dataTables_wrapper,
    .dataTables_scroll,
    .dataTables_scrollHead,
    .dataTables_scrollHeadInner,
    .dataTables_scrollHeadInner table,
    .dataTables_scrollBody,
    .dataTables_scrollBody table {
        width: 100% !important;
    }
</style>

<div class="container-fluid">

    <!-- Room Header Banner -->
    <div class="card dbr-hero-card mb-4">
        <div class="card-body p-3 p-md-4">
            <!-- Row 1: Context, Room Title & Action Buttons -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                <div class="mb-2 mb-md-0">
                    <div class="d-flex align-items-center mb-1 text-muted small">
                        <a href="<?= site_url('admin/inventaris/dbr'); ?>" class="text-muted"><i class="fas fa-arrow-left mr-1"></i> Daftar Ruangan</a>
                        <span class="mx-2 text-muted">/</span>
                        <span class="text-primary font-weight-bold"><?= esc($room['kode_ruangan']); ?></span>
                    </div>
                    <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                        <span class="badge px-3 py-1.5 font-weight-bold text-monospace text-white" style="font-size: 0.95rem; border-radius: 8px; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); box-shadow: 0 2px 6px rgba(2, 132, 199, 0.25);">
                            <i class="fas fa-door-open mr-1 text-white"></i> <?= esc($room['kode_ruangan']); ?>
                        </span>
                        <h2 class="mb-0 font-weight-bold text-dark" style="font-size: 1.4rem; letter-spacing: -0.3px;">
                            <?= esc($room['nama_ruangan']); ?>
                        </h2>
                    </div>
                </div>
                <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                    <a href="<?= site_url('admin/inventaris/dbr'); ?>" class="btn btn-outline-secondary btn-sm px-3 shadow-sm font-weight-bold" style="border-radius: 6px;">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                    <a href="<?= site_url('admin/inventaris/audit/mulai-ruangan/' . $room['id']); ?>" class="btn btn-warning btn-sm px-3 shadow-sm font-weight-bold text-dark" style="border-radius: 6px;" title="Mulai Sesi Audit & Stock Opname Fisik Ruangan Ini">
                        <i class="fas fa-clipboard-check mr-1"></i> Audit Ruangan Ini
                    </a>
                    <?php if (! empty($can_export)): ?>
                        <div class="btn-group shadow-sm">
                            <a href="<?= site_url('admin/inventaris/dbr/' . $room['id'] . '/cetak-pdf'); ?>" target="_blank" class="btn btn-danger btn-sm px-3 font-weight-bold" style="border-radius: 6px 0 0 6px;">
                                <i class="fas fa-file-pdf mr-1"></i> Cetak PDF DBR
                            </a>
                            <a href="<?= site_url('admin/inventaris/dbr/' . $room['id'] . '/export-excel'); ?>" class="btn btn-success btn-sm px-3 font-weight-bold" style="border-radius: 0 6px 6px 0;">
                                <i class="fas fa-file-excel mr-1"></i> Export Excel
                            </a>
                        </div>
                    <?php endif; ?>
                    <?php if (! empty($can_add)): ?>
                        <button type="button" class="btn btn-info btn-sm px-3 shadow-sm font-weight-bold text-white d-inline-flex align-items-center" data-toggle="modal" data-target="#modalScanCamera" style="border-radius: 6px; background: #0284c7; border-color: #0284c7; gap: 6px;">
                            <i class="fas fa-camera"></i>
                            <span>Scan Kamera HP</span>
                        </button>
                        <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm font-weight-bold" data-toggle="modal" data-target="#modal-alokasi-barang" style="border-radius: 6px; background: #2563eb; border-color: #2563eb;">
                            <i class="fas fa-plus mr-1"></i> Alokasikan Manual
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Divider -->
            <div class="my-3" style="height: 1px; background: #f1f5f9;"></div>

            <!-- Row 2: 4 KPI Summary Tiles -->
            <div class="row">
                <!-- Penanggung Jawab -->
                <div class="col-12 col-sm-6 col-lg-3 mb-2 mb-lg-0">
                    <div class="dbr-kpi-tile h-100 d-flex align-items-center">
                        <div class="d-flex align-items-center justify-content-center rounded-circle mr-3" style="width: 44px; height: 44px; background: #e0f2fe; color: #0284c7; flex-shrink: 0;">
                            <i class="fas fa-user-shield fa-lg"></i>
                        </div>
                        <div class="overflow-hidden">
                            <span class="text-uppercase text-muted font-weight-bold d-block" style="font-size: 0.68rem; letter-spacing: 0.5px;">Penanggung Jawab</span>
                            <span class="text-dark font-weight-bold text-truncate d-block" style="font-size: 0.95rem;" title="<?= esc($room['penanggung_jawab_nama'] ?: 'Belum ditentukan'); ?>">
                                <?= esc($room['penanggung_jawab_nama'] ?: 'Belum ditentukan'); ?>
                            </span>
                            <small class="text-muted text-truncate d-block" style="font-size: 0.75rem;">NIP. <?= esc($room['penanggung_jawab_nip'] ?: '-'); ?></small>
                        </div>
                    </div>
                </div>

                <!-- Lokasi Lantai -->
                <div class="col-12 col-sm-6 col-lg-3 mb-2 mb-lg-0">
                    <div class="dbr-kpi-tile h-100 d-flex align-items-center">
                        <div class="d-flex align-items-center justify-content-center rounded-circle mr-3" style="width: 44px; height: 44px; background: #f1f5f9; color: #475569; flex-shrink: 0;">
                            <i class="fas fa-map-marked-alt fa-lg"></i>
                        </div>
                        <div class="overflow-hidden">
                            <span class="text-uppercase text-muted font-weight-bold d-block" style="font-size: 0.68rem; letter-spacing: 0.5px;">Lokasi Ruangan</span>
                            <span class="text-dark font-weight-bold text-truncate d-block" style="font-size: 0.95rem;">
                                <?= esc($room['lokasi_lantai'] ?: 'Gedung Kantor Satker'); ?>
                            </span>
                            <small class="text-muted text-truncate d-block" style="font-size: 0.75rem;">Satker PPS Provinsi Riau</small>
                        </div>
                    </div>
                </div>

                <!-- Total Fisik Barang -->
                <div class="col-12 col-sm-6 col-lg-3 mb-2 mb-lg-0">
                    <div class="dbr-kpi-tile h-100 d-flex align-items-center">
                        <div class="d-flex align-items-center justify-content-center rounded-circle mr-3" style="width: 44px; height: 44px; background: #ecfdf5; color: #059669; flex-shrink: 0;">
                            <i class="fas fa-boxes-stacked fa-lg"></i>
                        </div>
                        <div class="overflow-hidden">
                            <span class="text-uppercase text-muted font-weight-bold d-block" style="font-size: 0.68rem; letter-spacing: 0.5px;">Total Fisik Barang</span>
                            <span class="text-success font-weight-bold d-block" style="font-size: 1.05rem;">
                                <?= number_format((int) ($totalUnit ?? 0)); ?> Unit
                            </span>
                            <small class="text-muted text-truncate d-block" style="font-size: 0.75rem;"><?= count($dbrItems ?? []); ?> Jenis (<?= count($individualItems ?? []); ?> Aset NUP)</small>
                        </div>
                    </div>
                </div>

                <!-- Total Nilai Aset -->
                <div class="col-12 col-sm-6 col-lg-3 mb-2 mb-lg-0">
                    <div class="dbr-kpi-tile h-100 d-flex align-items-center">
                        <div class="d-flex align-items-center justify-content-center rounded-circle mr-3" style="width: 44px; height: 44px; background: #fffbeb; color: #d97706; flex-shrink: 0;">
                            <i class="fas fa-coins fa-lg"></i>
                        </div>
                        <div class="overflow-hidden">
                            <span class="text-uppercase text-muted font-weight-bold d-block" style="font-size: 0.68rem; letter-spacing: 0.5px;">Nilai Perolehan Total</span>
                            <span class="text-dark font-weight-bold d-block text-truncate font-mono" style="font-size: 1.05rem;">
                                Rp <?= number_format((float) ($totalNilaiRuangan ?? 0), 0, ',', '.'); ?>
                            </span>
                            <small class="text-success font-weight-bold text-truncate d-block" style="font-size: 0.75rem;"><i class="fas fa-check-circle mr-1"></i>Tercatat SIMAN BMN</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Container -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; border: 1px solid #e2e8f0 !important; overflow: hidden;">
        <div class="card-header bg-white border-bottom p-2 px-3">
            <ul class="nav nav-pills dbr-nav-pills" id="dbrTabs" role="tablist">
                <li class="nav-item mr-2">
                    <a class="nav-link active font-weight-bold" id="tab-rekap-tab" data-toggle="pill" href="#tab-rekap" role="tab" aria-controls="tab-rekap" aria-selected="true" style="color: #ffffff !important;">
                        <i class="fas fa-file-invoice mr-1.5" style="color: #ffffff !important;"></i> <span class="tab-label-text" style="color: #ffffff !important;">Rekapitulasi DBR (Format Resmi)</span>
                        <span class="badge badge-pill-counter ml-1.5 px-2 py-0.5" style="border-radius: 10px; background: rgba(255, 255, 255, 0.25); color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.4);"><?= count($dbrItems ?? []); ?> Jenis</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link font-weight-bold" id="tab-detail-tab" data-toggle="pill" href="#tab-detail" role="tab" aria-controls="tab-detail" aria-selected="false">
                        <i class="fas fa-cubes-stacked mr-1.5 text-info"></i> <span class="tab-label-text">Daftar Detail Fisik Barang</span>
                        <span class="badge badge-pill-counter badge-secondary ml-1.5 px-2 py-0.5" style="border-radius: 10px;"><?= count($individualItems ?? []); ?> Unit Aset</span>
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body p-3 p-md-4">
            <div class="tab-content" id="dbrTabsContent">

                <!-- TAB 1: REKAPITULASI DBR RESMI -->
                <div class="tab-pane fade show active" id="tab-rekap" role="tabpanel" aria-labelledby="tab-rekap-tab">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-striped w-100 js-datatable table-dbr" data-scroll-x="false" style="border-radius: 8px;">
                            <thead style="background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%); color: #f8fafc;">
                                <tr>
                                    <th style="width: 45px;" class="text-center align-middle">NO</th>
                                    <th style="width: 140px;" class="text-center align-middle">KODE BARANG</th>
                                    <th class="align-middle">NAMA BARANG</th>
                                    <th class="align-middle">MERK / TYPE</th>
                                    <th class="text-center align-middle" style="width: 90px;">JUMLAH</th>
                                    <th class="text-center align-middle" style="width: 80px;">SATUAN</th>
                                    <th class="text-right align-middle" style="width: 150px;">NILAI PEROLEHAN</th>
                                    <th class="align-middle text-center" style="width: 150px;">LEGALITAS (SK PSP)</th>
                                    <th class="align-middle" style="width: 160px;">DAFTAR NUP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($dbrItems)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">
                                            <i class="fas fa-box-open fa-2x mb-2 d-block"></i>
                                            Belum ada barang yang dialokasikan pada ruangan ini.<br>
                                            <?php if (! empty($can_add)): ?>
                                                <button type="button" class="btn btn-primary btn-sm mt-2 shadow-sm" data-toggle="modal" data-target="#modal-alokasi-barang">
                                                    <i class="fas fa-plus mr-1"></i> Alokasikan Barang Sekarang
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($dbrItems as $item): ?>
                                        <tr>
                                            <td class="text-center align-middle text-muted font-weight-bold"><?= $no++; ?></td>
                                            <td class="text-center align-middle font-weight-bold text-dark font-mono">
                                                <?= esc($item['kode_barang']); ?>
                                            </td>
                                            <td class="align-middle font-weight-bold" style="color: #0f172a; font-size: 0.93rem;">
                                                <?= esc($item['nama_barang']); ?>
                                            </td>
                                            <td class="align-middle text-secondary" style="font-size: 0.86rem;">
                                                <?= esc($item['merk_tipe'] ?: '-'); ?>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="badge px-2.5 py-1.5 font-weight-bold" style="font-size: 0.95rem; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; border-radius: 6px;">
                                                    <?= number_format((int) ($item['total_jumlah'] ?? 1)); ?>
                                                </span>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="badge badge-light border text-muted px-2 py-1 font-weight-normal"><?= esc($item['satuan'] ?: 'Buah'); ?></span>
                                            </td>
                                            <td class="text-right align-middle font-weight-bold text-dark font-mono text-nowrap" style="font-size: 0.92rem;">
                                                Rp <?= number_format((float) ($item['total_nilai'] ?? 0), 0, ',', '.'); ?>
                                            </td>
                                            <td class="align-middle text-center small">
                                                <?php if (! empty($item['no_psp'])): ?>
                                                    <span class="badge px-2.5 py-1 text-truncate font-weight-normal" style="background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; font-size: 0.74rem; max-width: 140px; display: inline-block;" title="<?= esc($item['no_psp']); ?>">
                                                        <i class="fas fa-stamp mr-1"></i><?= esc($item['no_psp']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-light border text-muted px-2 py-1 font-weight-normal" style="font-size: 0.74rem;">Belum PSP</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="align-middle small">
                                                <span class="badge badge-light border text-secondary px-2.5 py-1 text-monospace" style="font-size: 0.76rem; border-radius: 6px; font-weight: 500;">NUP: <?= esc($item['nup_list'] ?: '-'); ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <?php if (! empty($dbrItems)): ?>
                                <tfoot>
                                    <tr class="font-weight-bold" style="background: #f8fafc; border-top: 2px solid #cbd5e1;">
                                        <td colspan="4" class="text-center align-middle font-weight-bold text-dark" style="font-size: 0.92rem; letter-spacing: 0.5px;">TOTAL KESELURUHAN</td>
                                        <td class="text-center align-middle text-success font-weight-bold" style="font-size: 1.1rem;"><?= number_format((int) ($totalUnit ?? 0)); ?></td>
                                        <td class="text-center align-middle text-muted">Unit</td>
                                        <td class="text-right align-middle text-success font-weight-bold font-mono text-nowrap" style="font-size: 1.05rem;">Rp <?= number_format((float) ($totalNilaiRuangan ?? 0), 0, ',', '.'); ?></td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <!-- TAB 2: DETAIL INDIVIDUAL ASET -->
                <div class="tab-pane fade" id="tab-detail" role="tabpanel" aria-labelledby="tab-detail-tab">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-striped w-100 js-datatable table-dbr" data-scroll-x="false" data-order='[[1, "asc"], [2, "asc"]]' style="border-radius: 8px;">
                            <thead style="background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%); color: #f8fafc;">
                                <tr>
                                    <th style="width: 45px;" class="text-center align-middle" data-orderable="false">#</th>
                                    <th class="align-middle" style="width: 150px;">KODE BARANG</th>
                                    <th class="text-center align-middle" style="width: 80px;" data-type="num">NUP</th>
                                    <th class="align-middle">NAMA BARANG</th>
                                    <th class="align-middle">MERK / TIPE</th>
                                    <th class="text-center align-middle" style="width: 110px;">KONDISI</th>
                                    <th class="text-center align-middle" style="width: 80px;">TAHUN</th>
                                    <?php if (! empty($can_delete)): ?>
                                        <th style="width: 110px;" class="text-center align-middle" data-orderable="false">AKSI</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $idx = 1; foreach (($individualItems ?? []) as $ind): ?>
                                    <tr>
                                        <td class="text-center align-middle text-muted font-weight-bold"><?= $idx++; ?></td>
                                        <td class="align-middle" data-order="<?= esc($ind['kode_barang']); ?>">
                                            <span class="font-weight-bold text-dark font-mono"><?= esc($ind['kode_barang']); ?></span>
                                            <?php if (! empty($ind['kode_register'])): ?>
                                                <small class="text-muted d-block font-mono" style="font-size: 0.73rem;" title="Kode Register SIMAN: <?= esc($ind['kode_register']); ?>">
                                                    <i class="fas fa-barcode mr-1"></i><?= esc(substr($ind['kode_register'], 0, 10)); ?>...
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center align-middle" data-order="<?= (int) ($ind['nup'] ?? 0); ?>">
                                            <span class="badge badge-light border font-weight-bold px-2 py-1 font-mono"><?= esc((string) $ind['nup']); ?></span>
                                        </td>
                                        <td class="align-middle font-weight-bold" style="color: #0f172a; font-size: 0.92rem;"><?= esc($ind['nama_barang']); ?></td>
                                        <td class="align-middle text-secondary" style="font-size: 0.86rem;"><?= esc($ind['merk_tipe'] ?: '-'); ?></td>
                                        <td class="text-center align-middle">
                                            <?php if ($ind['kondisi'] === 'baik'): ?>
                                                <span class="badge px-2.5 py-1 font-weight-medium" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; border-radius: 6px; font-size: 0.78rem;">Baik</span>
                                            <?php elseif ($ind['kondisi'] === 'rusak_ringan'): ?>
                                                <span class="badge px-2.5 py-1 font-weight-medium" style="background: #fefce8; color: #854d0e; border: 1px solid #fef08a; border-radius: 6px; font-size: 0.78rem;">Rusak Ringan</span>
                                            <?php else: ?>
                                                <span class="badge px-2.5 py-1 font-weight-medium" style="background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; border-radius: 6px; font-size: 0.78rem;">Rusak Berat</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center align-middle text-secondary font-mono"><?= esc((string) ($ind['tahun_perolehan'] ?: '-')); ?></td>
                                        <?php if (! empty($can_delete)): ?>
                                            <td class="text-center align-middle">
                                                <form action="<?= site_url('admin/inventaris/dbr/' . $room['id'] . '/keluarkan-barang'); ?>" method="post" onsubmit="return confirm('Keluarkan barang ini dari ruangan?');">
                                                    <?= csrf_field(); ?>
                                                    <input type="hidden" name="asset_id" value="<?= esc((string) $ind['id'], 'attr'); ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-xs px-2.5 py-1 shadow-sm" style="border-radius: 5px;" title="Keluarkan dari Ruangan">
                                                        <i class="fas fa-sign-out-alt mr-1"></i> Keluarkan
                                                    </button>
                                                </form>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal Alokasi Barang ke Ruangan -->
<?php if (! empty($can_add)): ?>
<div class="modal fade" id="modal-alokasi-barang" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document" style="max-width: 1140px;">
        <div class="modal-content" style="border-radius: 14px; border: none; box-shadow: 0 14px 40px rgba(0,0,0,0.18); overflow: hidden;">
            <div class="modal-header py-3" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff;">
                <div>
                    <h5 class="modal-title font-weight-bold mb-0 text-white" style="font-size: 1.15rem;">
                        <i class="fas fa-box-open text-primary mr-2"></i>Alokasikan Barang ke Ruangan
                    </h5>
                    <small style="color: #94a3b8;">Ruangan Tujuan: <strong class="text-white"><?= esc($room['nama_ruangan']); ?> (<?= esc($room['kode_ruangan']); ?>)</strong></small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-alokasi-barang" action="<?= site_url('admin/inventaris/dbr/' . $room['id'] . '/alokasi-barang'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body py-3 bg-light">
                    <!-- Search & Filter Card -->
                    <div class="card border-0 shadow-sm mb-3 bg-white" style="border-radius: 10px;">
                        <div class="card-body p-3">
                            <div class="row align-items-center">
                                <div class="col-12 col-lg-6 mb-2 mb-lg-0">
                                    <label class="small font-weight-bold text-dark mb-1">
                                        <i class="fas fa-search text-primary mr-1"></i> Cari Aset BMN:
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light border-right-0 text-muted" style="border-radius: 8px 0 0 8px;">
                                                <i class="fas fa-search"></i>
                                            </span>
                                        </div>
                                        <input type="text" id="inputSearchAlokasi" class="form-control border-left-0 font-weight-medium" placeholder="Ketik Nama Barang, Kode Barang, NUP, Merk/Tipe, atau Kode Register SIMAN..." autocomplete="off" style="border-radius: 0 8px 8px 0;">
                                        <div class="input-group-append" id="wrapperClearSearch" style="display: none;">
                                            <button class="btn btn-outline-secondary border-left-0" type="button" id="btnClearSearchAlokasi" title="Bersihkan Pencarian" style="border-radius: 0 8px 8px 0;">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-lg-3 mb-2 mb-lg-0">
                                    <label class="small font-weight-bold text-dark mb-1">
                                        <i class="fas fa-map-marker-alt text-info mr-1"></i> Asal / Sumber Aset:
                                    </label>
                                    <select id="filterSumberAlokasi" class="form-control custom-select" style="border-radius: 8px; font-size: 0.88rem;">
                                        <option value="">Semua Sumber Aset</option>
                                        <option value="belum_berlokasi">Aset Belum Berlokasi Saja</option>
                                        <option value="ruangan_lain">Dari Ruangan Lain (Pindah Ruangan)</option>
                                    </select>
                                </div>
                                <div class="col-6 col-lg-3 mb-2 mb-lg-0">
                                    <label class="small font-weight-bold text-dark mb-1">
                                        <i class="fas fa-check-circle text-success mr-1"></i> Kondisi Fisik:
                                    </label>
                                    <select id="filterKondisiAlokasi" class="form-control custom-select" style="border-radius: 8px; font-size: 0.88rem;">
                                        <option value="">Semua Kondisi</option>
                                        <option value="baik">Baik</option>
                                        <option value="rusak_ringan">Rusak Ringan</option>
                                        <option value="rusak_berat">Rusak Berat</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Selection & Counter Toolbar -->
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 px-1" style="gap: 8px;">
                        <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                            <span class="badge badge-light border px-3 py-2 font-weight-bold text-dark shadow-sm" id="badgeSelectedCount" style="font-size: 0.85rem; border-radius: 6px;">
                                <i class="fas fa-check-square text-primary mr-1"></i> <span id="countSelectedText">0</span> Barang Dipilih
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-danger shadow-sm font-weight-bold" id="btnResetSelection" style="display: none; border-radius: 6px; padding: 4px 10px; font-size: 0.8rem;">
                                <i class="fas fa-times mr-1"></i> Batalkan Semua Pilihan
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary shadow-sm font-weight-bold" id="btnToggleOnlySelected" style="border-radius: 6px; padding: 4px 10px; font-size: 0.8rem;">
                                <i class="fas fa-filter mr-1"></i> <span id="textToggleOnlySelected">Tampilkan Hanya yang Dipilih</span>
                            </button>
                        </div>
                        <div class="small text-muted font-weight-medium">
                            <span id="labelTotalAvailable">Menampilkan <?= count($unallocatedAssets); ?> aset tersedia</span>
                        </div>
                    </div>

                    <?php if (empty($unallocatedAssets)): ?>
                        <div class="alert alert-info mb-0" style="border-radius: 8px;">
                            <i class="fas fa-info-circle mr-1"></i> Tidak ada data aset inventaris satker yang tersedia untuk dialokasikan. Semua aset sudah berada di ruangan ini atau silakan tambahkan data aset pada menu <strong>Inventaris Satker</strong>.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive bg-white rounded border shadow-sm" style="max-height: 440px; overflow-y: auto;">
                            <table id="tableAlokasiBarang" class="table table-hover table-bordered table-sm w-100 mb-0" style="font-size: 0.88rem;">
                                <thead class="thead-light sticky-top bg-light" style="z-index: 5;">
                                    <tr>
                                        <th style="width: 42px;" class="text-center align-middle" data-orderable="false">
                                            <input type="checkbox" id="checkAllAlokasi" title="Pilih Semua di Hasil Ini">
                                        </th>
                                        <th style="width: 170px;" class="align-middle">Kode Barang & Register</th>
                                        <th style="width: 75px;" class="text-center align-middle" data-type="num">NUP</th>
                                        <th class="align-middle">Nama Barang</th>
                                        <th class="align-middle">Merk / Tipe</th>
                                        <th style="width: 140px;" class="text-center align-middle">Asal / Status</th>
                                        <th style="width: 95px;" class="text-center align-middle">Kondisi</th>
                                        <th style="width: 80px;" class="text-center align-middle">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($unallocatedAssets as $u): ?>
                                        <?php
                                            $isUnassigned = empty($u['ruangan_id']) || $u['ruangan_id'] == 0;
                                            $sumberType = $isUnassigned ? 'belum_berlokasi' : 'ruangan_lain';
                                        ?>
                                        <tr data-id="<?= (int) $u['id']; ?>" data-sumber="<?= $sumberType; ?>" data-kondisi="<?= esc($u['kondisi']); ?>">
                                            <td class="text-center align-middle">
                                                <input type="checkbox" class="asset-checkbox-alokasi" value="<?= (int) $u['id']; ?>" data-id="<?= (int) $u['id']; ?>" data-kode="<?= esc($u['kode_barang'], 'attr'); ?>" data-nup="<?= esc((string) $u['nup'], 'attr'); ?>" data-nama="<?= esc($u['nama_barang'], 'attr'); ?>">
                                            </td>
                                            <td class="align-middle" data-order="<?= esc($u['kode_barang']); ?>">
                                                <span class="font-weight-bold text-dark font-mono"><?= esc($u['kode_barang']); ?></span>
                                                <?php if (! empty($u['kode_register'])): ?>
                                                    <div class="text-muted font-mono text-truncate" style="max-width: 180px; font-size: 0.74rem;" title="Kode Register SIMAN: <?= esc($u['kode_register']); ?>">
                                                        <i class="fas fa-barcode text-secondary mr-1"></i><?= esc($u['kode_register']); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center align-middle" data-order="<?= (int) ($u['nup'] ?? 0); ?>">
                                                <span class="badge badge-light border font-mono"><?= esc((string) $u['nup']); ?></span>
                                            </td>
                                            <td class="align-middle font-weight-bold text-primary"><?= esc($u['nama_barang']); ?></td>
                                            <td class="align-middle"><?= esc($u['merk_tipe'] ?: '-'); ?></td>
                                            <td class="text-center align-middle">
                                                <?php if ($isUnassigned): ?>
                                                    <span class="badge badge-secondary px-2 py-1" style="font-size: 0.78rem;">
                                                        <i class="fas fa-box-open mr-1"></i>Belum Berlokasi
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-info px-2 py-1 text-truncate" style="max-width: 130px; font-size: 0.78rem;" title="Saat ini di: <?= esc($u['lokasi_ruangan']); ?>">
                                                        <i class="fas fa-exchange-alt mr-1"></i><?= esc($u['lokasi_ruangan']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="badge badge-<?= $u['kondisi'] === 'baik' ? 'success' : ($u['kondisi'] === 'rusak_ringan' ? 'warning' : 'danger'); ?> px-2 py-1" style="font-size: 0.78rem;">
                                                    <?= ucfirst(str_replace('_', ' ', $u['kondisi'])); ?>
                                                </span>
                                            </td>
                                            <td class="text-center align-middle"><?= number_format((int) ($u['jumlah'] ?? 1)); ?> <?= esc($u['satuan'] ?? 'Unit'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer bg-light py-3" style="border-top: 1px solid #e9eef5;">
                    <button type="button" class="btn btn-secondary px-3" data-dismiss="modal" style="border-radius: 6px;">Batal</button>
                    <?php if (! empty($unallocatedAssets)): ?>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm font-weight-bold" id="btnSubmitAlokasi" style="border-radius: 6px; background: #2563eb; border-color: #2563eb;">
                            <i class="fas fa-check-circle mr-1"></i> Alokasikan <span id="btnSubmitCountBadge" class="badge badge-light text-primary ml-1" style="display: none;">0</span> ke Ruangan
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Scan QR Code / Register Aset -->
<?php if (! empty($can_add)): ?>
<style>
    #qr-reader-container {
        position: relative;
        background: #090d16;
        border-radius: 12px;
        overflow: hidden;
        min-height: 280px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    }
    #qr-reader {
        width: 100% !important;
        border: none !important;
    }
    #qr-reader video {
        width: 100% !important;
        height: auto !important;
        max-height: 48vh !important;
        object-fit: cover !important;
        border-radius: 10px;
    }
    .scan-guide-overlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 220px;
        height: 220px;
        border: 2px dashed rgba(56, 189, 248, 0.85);
        border-radius: 16px;
        box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.45);
        pointer-events: none;
        z-index: 10;
    }
    .scan-laser-line {
        position: absolute;
        width: 100%;
        height: 2px;
        background: linear-gradient(90deg, transparent, #38bdf8, transparent);
        top: 0;
        animation: laserScan 2s infinite ease-in-out;
    }
    @keyframes laserScan {
        0% { top: 0%; opacity: 0.8; }
        50% { top: 100%; opacity: 1; }
        100% { top: 0%; opacity: 0.8; }
    }
    @media (max-width: 767.98px) {
        #modalScanCamera .modal-dialog {
            margin: 0.5rem auto;
            max-width: calc(100% - 1rem);
        }
        #qr-reader-container {
            min-height: 260px !important;
        }
    }
</style>

<div class="modal fade" id="modalScanCamera" tabindex="-1" role="dialog" aria-labelledby="modalScanCameraLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content" style="border-radius: 14px; border: none; box-shadow: 0 12px 36px rgba(0,0,0,0.25); overflow: hidden;">
            <div class="modal-header py-3" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff;">
                <div class="d-flex align-items-center">
                    <div class="bg-primary text-white rounded-circle p-2 mr-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="fas fa-camera"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold mb-0" id="modalScanCameraLabel" style="font-size: 1.05rem;">
                            Scan QR / Barcode Aset BMN (Kamera HP)
                        </h5>
                        <small style="color: #94a3b8;">Ruangan Tujuan: <strong class="text-white"><?= esc($room['nama_ruangan']); ?> (<?= esc($room['kode_ruangan']); ?>)</strong></small>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3 bg-light">
                <!-- Status & Control Toolbar -->
                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap" style="gap: 6px;">
                    <div class="small font-weight-bold text-muted d-flex align-items-center" id="cam-status">
                        <i class="fas fa-circle text-warning mr-1"></i> Kamera siap dinyalakan
                    </div>
                    <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                        <button type="button" class="btn btn-warning btn-sm px-2 text-dark font-weight-bold shadow-sm" id="btn-toggle-torch" style="border-radius: 6px; display: none;" title="Nyalakan/Matikan Senter HP">
                            <i class="fas fa-lightbulb mr-1"></i> <span id="torch-text">Senter</span>
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm px-2 shadow-sm font-weight-bold" id="btn-flip-cam" style="border-radius: 6px;" title="Ganti Kamera Belakang / Depan">
                            <i class="fas fa-sync-alt mr-1"></i> Ganti Kamera
                        </button>
                        <button type="button" class="btn btn-primary btn-sm px-3 font-weight-bold shadow-sm" id="btn-toggle-cam" style="border-radius: 6px;">
                            <i class="fas fa-video mr-1"></i> <span id="btn-cam-text">Nyalakan</span>
                        </button>
                    </div>
                </div>

                <!-- Camera Stream Viewport -->
                <div id="qr-reader-container" class="mb-3">
                    <div id="qr-reader"></div>
                    <div class="scan-guide-overlay" id="scanGuideOverlay" style="display: none;">
                        <div class="scan-laser-line"></div>
                    </div>
                </div>

                <!-- Hasil Scan Terakhir -->
                <div id="camScanResultBox" style="display: none;">
                    <div class="alert shadow-sm mb-2 p-2.5" id="camScanAlert" style="border-radius: 10px; border-left: 4px solid #16a34a; background: #f0fdf4; border-color: #bbf7d0;">
                        <div class="d-flex align-items-center">
                            <div class="mr-2 text-success" id="camResultIcon" style="font-size: 1.5rem;"><i class="fas fa-check-circle"></i></div>
                            <div class="flex-grow-1">
                                <div class="font-weight-bold text-dark" id="camResultTitle" style="font-size: 0.92rem;">-</div>
                                <div class="small text-muted" id="camResultMeta">-</div>
                            </div>
                            <span class="badge badge-success px-2.5 py-1 font-weight-bold" id="camResultBadge" style="font-size: 0.8rem;">BERHASIL DIALOKASIKAN</span>
                        </div>
                    </div>
                </div>

                <!-- Input Cepat Alternatif (USB Barcode / Input Manual) -->
                <div class="input-group input-group-sm mt-2">
                    <input type="text" class="form-control" id="inputScanManual" placeholder="Scan barcode USB / ketik NUP / Kode Register di sini..." style="border-radius: 6px 0 0 6px;">
                    <div class="input-group-append">
                        <button class="btn btn-outline-primary font-weight-bold" type="button" id="btnSubmitScanManual" style="border-radius: 0 6px 6px 0;">
                            <i class="fas fa-search mr-1"></i> Alokasikan
                        </button>
                    </div>
                </div>

                <div class="text-muted small text-center mt-2">
                    <i class="fas fa-info-circle mr-1 text-primary"></i> Kamera mendeteksi URL QR SIMAN, Kode Register 32-hex, Kode Barang & NUP secara otomatis dan langsung menempatkan ke ruangan ini.
                </div>
            </div>
            <div class="modal-footer py-2 bg-white d-flex justify-content-between">
                <span class="small text-muted font-weight-bold" id="camScanCounterText">Belum ada barang dialokasikan sesi ini</span>
                <button type="button" class="btn btn-secondary btn-sm px-3 font-weight-bold" data-dismiss="modal">
                    Tutup Scanner
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Script HTML5 QR Code Library & Handler -->
<script>
// Global iOS WebKit Video PlaysInline Safeguard
(function() {
    var origCreate = document.createElement;
    document.createElement = function(tag, opt) {
        var el = origCreate.call(document, tag, opt);
        if (tag && typeof tag === 'string' && tag.toLowerCase() === 'video') {
            el.setAttribute('playsinline', 'true');
            el.setAttribute('webkit-playsinline', 'true');
            el.setAttribute('autoplay', 'true');
            el.setAttribute('muted', 'true');
            el.playsInline = true;
            el.muted = true;
            el.autoplay = true;
            el.style.background = 'transparent';
        }
        return el;
    };
})();
</script>
<script src="<?= base_url('assets/adminlte/plugins/html5-qrcode/html5-qrcode.min.js'); ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Check all assets in manual allocation modal
    var checkAll = document.getElementById('checkAllAssets');
    if (checkAll) {
        checkAll.addEventListener('change', function() {
            var checkboxes = document.querySelectorAll('.asset-checkbox');
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = this.checked;
            }
        });
    }

    // 2. High-Performance Continuous QR Code Scanner (Identik dengan Modul Audit)
    var html5QrCode = null;
    var isCameraRunning = false;
    var lastScannedCode = '';
    var lastScannedTime = 0;
    var scanCooldown = 1800; // jeda 1.8 detik antar scan
    var sessionAllocatedCount = 0;
    var currentFacingMode = "environment"; // Kamera Belakang Smartphone

    var csrfTokenName = '<?= csrf_token(); ?>';
    var csrfHash = '<?= csrf_hash(); ?>';
    var scanProcessUrl = '<?= site_url("admin/inventaris/dbr/{$room['id']}/scan-process"); ?>';

    function updateCsrf(newHash) {
        if (newHash) {
            csrfHash = newHash;
        }
    }

    // Audio Feedback (Web Audio API)
    function playBeep(isSuccess) {
        try {
            var AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            var audioCtx = new AudioContext();
            var osc = audioCtx.createOscillator();
            var gain = audioCtx.createGain();
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.type = 'sine';
            osc.frequency.setValueAtTime(isSuccess ? 880 : 380, audioCtx.currentTime);
            gain.gain.setValueAtTime(0.18, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + (isSuccess ? 0.15 : 0.25));
            osc.start(audioCtx.currentTime);
            osc.stop(audioCtx.currentTime + (isSuccess ? 0.15 : 0.25));
        } catch (e) {}
    }

    $('#modalScanCamera').on('shown.bs.modal', function() {
        startCameraScanner();
        $('#inputScanManual').focus();
    });

    $('#modalScanCamera').on('hidden.bs.modal', function() {
        stopCameraScanner();
        if (sessionAllocatedCount > 0) {
            window.location.reload();
        }
    });

    function startCameraScanner() {
        if (!window.Html5Qrcode) {
            $('#cam-status').html('<i class="fas fa-times-circle text-danger mr-1"></i> Library scanner belum tersedia.');
            return;
        }

        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("qr-reader");
        }

        var config = {
            fps: 15,
            qrbox: function(viewfinderWidth, viewfinderHeight) {
                var minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                var qrboxSize = Math.floor(minEdge * 0.72);
                return {
                    width: Math.max(qrboxSize, 220),
                    height: Math.max(qrboxSize, 220)
                };
            },
            aspectRatio: 1.0
        };

        html5QrCode.start(
            { facingMode: currentFacingMode },
            config,
            onQrCodeScannedSuccess,
            onQrCodeScanError
        ).then(function() {
            isCameraRunning = true;
            $('#scanGuideOverlay').show();
            $('#cam-status').html('<i class="fas fa-circle text-success mr-1"></i> Kamera Aktif - Arahkan ke QR BMN');
            $('#btn-cam-text').text('Matikan');
            $('#btn-toggle-cam').removeClass('btn-primary').addClass('btn-danger');

            checkTorchCapability();
        }).catch(function(err) {
            isCameraRunning = false;
            $('#scanGuideOverlay').hide();
            $('#cam-status').html('<i class="fas fa-exclamation-triangle text-danger mr-1"></i> Izin kamera ditolak atau kamera tidak dapat diakses.');
            console.error('Camera start error:', err);
        });
    }

    function stopCameraScanner() {
        if (html5QrCode && isCameraRunning) {
            html5QrCode.stop().then(function() {
                isCameraRunning = false;
                $('#scanGuideOverlay').hide();
                $('#cam-status').html('<i class="fas fa-circle text-secondary mr-1"></i> Kamera dimatikan');
                $('#btn-cam-text').text('Nyalakan');
                $('#btn-toggle-cam').removeClass('btn-danger').addClass('btn-primary');
                $('#btn-toggle-torch').hide();
            }).catch(function(err) {
                console.error('Camera stop error:', err);
            });
        }
    }

    $('#btn-toggle-cam').on('click', function() {
        if (isCameraRunning) {
            stopCameraScanner();
        } else {
            startCameraScanner();
        }
    });

    $('#btn-flip-cam').on('click', function() {
        currentFacingMode = (currentFacingMode === "environment") ? "user" : "environment";
        if (isCameraRunning) {
            stopCameraScanner();
            setTimeout(function() {
                startCameraScanner();
            }, 400);
        }
    });

    var isTorchOn = false;
    function checkTorchCapability() {
        try {
            var track = html5QrCode.getRunningTrackCapabilities();
            if (track && track.torch) {
                $('#btn-toggle-torch').show();
            } else {
                $('#btn-toggle-torch').hide();
            }
        } catch (e) {
            $('#btn-toggle-torch').hide();
        }
    }

    $('#btn-toggle-torch').on('click', function() {
        try {
            isTorchOn = !isTorchOn;
            html5QrCode.applyVideoConstraints({
                advanced: [{ torch: isTorchOn }]
            }).then(function() {
                $('#torch-text').text(isTorchOn ? 'Senter ON' : 'Senter OFF');
                if (isTorchOn) {
                    $('#btn-toggle-torch').removeClass('btn-warning text-dark').addClass('btn-light text-warning');
                } else {
                    $('#btn-toggle-torch').removeClass('btn-light text-warning').addClass('btn-warning text-dark');
                }
            });
        } catch (e) {
            console.error('Torch error:', e);
        }
    });

    function onQrCodeScannedSuccess(decodedText) {
        var now = Date.now();
        if (decodedText === lastScannedCode && (now - lastScannedTime) < scanCooldown) {
            return;
        }

        lastScannedCode = decodedText;
        lastScannedTime = now;

        if (navigator.vibrate) {
            try { navigator.vibrate(80); } catch(e) {}
        }

        processScannedCode(decodedText);
    }

    function onQrCodeScanError(errorMessage) {
        // abaikan frame scanning biasa
    }

    function processScannedCode(code) {
        if (!code || !code.trim()) return;
        code = code.trim();

        $('#cam-status').html('<i class="fas fa-spinner fa-spin text-primary mr-1"></i> Memproses: ' + code.substring(0, 22) + '...');

        var postData = {
            scan_keyword: code
        };
        postData[csrfTokenName] = csrfHash;

        $.ajax({
            url: scanProcessUrl,
            type: 'POST',
            data: postData,
            dataType: 'json',
            success: function(res) {
                if (res.csrf_hash) updateCsrf(res.csrf_hash);

                var alertBox = $('#camScanAlert');
                var iconBox  = $('#camResultIcon');
                var badgeBox = $('#camResultBadge');

                if (res.success) {
                    playBeep(true);
                    var it = res.item;

                    if (res.already) {
                        alertBox.css({
                            'border-left': '4px solid #0284c7',
                            'background': '#f0f9ff',
                            'border-color': '#bae6fd'
                        });
                        iconBox.removeClass().addClass('mr-2 text-info').html('<i class="fas fa-info-circle"></i>');
                        badgeBox.removeClass().addClass('badge badge-info px-2.5 py-1 font-weight-bold').text('SUDAH DI RUANGAN INI');
                        $('#cam-status').html('<i class="fas fa-info-circle text-info mr-1"></i> ' + it.nama_barang + ' sudah berada di ruangan ini.');
                    } else {
                        sessionAllocatedCount++;
                        $('#camScanCounterText').text(sessionAllocatedCount + ' barang berhasil dialokasikan pada sesi ini');

                        alertBox.css({
                            'border-left': '4px solid #16a34a',
                            'background': '#f0fdf4',
                            'border-color': '#bbf7d0'
                        });
                        iconBox.removeClass().addClass('mr-2 text-success').html('<i class="fas fa-check-circle"></i>');
                        badgeBox.removeClass().addClass('badge badge-success px-2.5 py-1 font-weight-bold').text('BERHASIL DIALOKASIKAN');
                        $('#cam-status').html('<i class="fas fa-check-circle text-success mr-1"></i> ' + it.nama_barang + ' Berhasil Masuk DBR!');
                    }

                    $('#camResultTitle').text(it.nama_barang + ' (NUP: ' + (it.nup || '-') + ')');
                    $('#camResultMeta').text('Kode: ' + it.kode_barang + ' • Merk/Tipe: ' + (it.merk_tipe || '-') + ' • Kondisi: ' + it.kondisi);
                    $('#camScanResultBox').slideDown();

                    $('#inputScanManual').val('').focus();
                } else {
                    playBeep(false);
                    alertBox.css({
                        'border-left': '4px solid #ef4444',
                        'background': '#fef2f2',
                        'border-color': '#fecaca'
                    });
                    iconBox.removeClass().addClass('mr-2 text-danger').html('<i class="fas fa-times-circle"></i>');
                    badgeBox.removeClass().addClass('badge badge-danger px-2.5 py-1 font-weight-bold').text('TIDAK DITEMUKAN');

                    $('#camResultTitle').text('Item Tidak Ditemukan');
                    $('#camResultMeta').text(res.message);
                    $('#camScanResultBox').slideDown();
                    $('#cam-status').html('<i class="fas fa-times-circle text-danger mr-1"></i> Barang tidak ditemukan');

                    $('#inputScanManual').select();
                }
            },
            error: function() {
                playBeep(false);
                $('#cam-status').html('<i class="fas fa-times-circle text-danger mr-1"></i> Gagal menghubungi server.');
            }
        });
    }

    // Input manual / USB Barcode Scanner Handler
    $('#inputScanManual').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            processScannedCode($(this).val());
        }
    });

    $('#btnSubmitScanManual').on('click', function() {
        processScannedCode($('#inputScanManual').val());
    });

    // Sync tab pill active text color to white
    $('#dbrTabs a[data-toggle="pill"]').on('shown.bs.tab', function(e) {
        $('#dbrTabs .nav-link').each(function() {
            var isActive = $(this).hasClass('active');
            var $badge = $(this).find('.badge-pill-counter');
            if (isActive) {
                $(this).css('color', '#ffffff');
                $(this).find('i').css('color', '#ffffff');
                $(this).find('.tab-label-text').css('color', '#ffffff');
                $badge.css({
                    'background-color': 'rgba(255, 255, 255, 0.25)',
                    'color': '#ffffff',
                    'border': '1px solid rgba(255, 255, 255, 0.4)'
                }).removeClass('badge-secondary');
            } else {
                $(this).css('color', '');
                $(this).find('i').css('color', '');
                $(this).find('.tab-label-text').css('color', '');
                $badge.css({
                    'background-color': '',
                    'color': '',
                    'border': ''
                }).addClass('badge-secondary');
            }
        });

        if ($.fn.dataTable) {
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        }
    });

    /* ==========================================================================
       MODAL ALOKASI BARANG: LIVE SEARCH, FILTER & MULTI-SELECTION RETENTION
       ========================================================================== */
    var selectedAssetMap = new Map();
    var isFilterOnlySelected = false;
    var alokasiDataTable = null;

    function updateAlokasiSelectionUI() {
        var count = selectedAssetMap.size;
        $('#countSelectedText').text(count);
        $('#btnSubmitCountBadge').text(count);

        if (count > 0) {
            $('#badgeSelectedCount')
                .removeClass('badge-light text-dark')
                .addClass('badge-primary text-white');
            $('#btnResetSelection').show();
            $('#btnSubmitCountBadge').show();
        } else {
            $('#badgeSelectedCount')
                .removeClass('badge-primary text-white')
                .addClass('badge-light text-dark');
            $('#btnResetSelection').hide();
            $('#btnSubmitCountBadge').hide();
        }

        if (alokasiDataTable) {
            var totalVisible = 0;
            var totalVisibleChecked = 0;
            alokasiDataTable.rows({ filter: 'applied' }).every(function() {
                totalVisible++;
                var rowNode = this.node();
                var chk = $(rowNode).find('.asset-checkbox-alokasi');
                if (chk.length && selectedAssetMap.has(String(chk.val()))) {
                    totalVisibleChecked++;
                }
            });
            $('#checkAllAlokasi').prop('checked', totalVisible > 0 && totalVisible === totalVisibleChecked);
            $('#labelTotalAvailable').text('Menampilkan ' + totalVisible + ' aset dari <?= count($unallocatedAssets); ?> aset');
        }
    }

    // Inisialisasi DataTable untuk Alokasi Barang saat modal ditampilkan
    $('#modal-alokasi-barang').on('shown.bs.modal', function () {
        if (!alokasiDataTable && $.fn.DataTable) {
            // Register custom filter for sumber & kondisi & onlySelected
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                if (settings.nTable.id !== 'tableAlokasiBarang') {
                    return true;
                }

                var $row = $(settings.aoData[dataIndex].nTr);
                var rowId = String($row.attr('data-id') || '');
                var rowSumber = $row.attr('data-sumber') || '';
                var rowKondisi = $row.attr('data-kondisi') || '';

                // 1. Filter Hanya yang Dipilih
                if (isFilterOnlySelected && !selectedAssetMap.has(rowId)) {
                    return false;
                }

                // 2. Filter Sumber
                var selectedSumber = $('#filterSumberAlokasi').val();
                if (selectedSumber && rowSumber !== selectedSumber) {
                    return false;
                }

                // 3. Filter Kondisi
                var selectedKondisi = $('#filterKondisiAlokasi').val();
                if (selectedKondisi && rowKondisi !== selectedKondisi) {
                    return false;
                }

                return true;
            });

            alokasiDataTable = $('#tableAlokasiBarang').DataTable({
                responsive: false,
                autoWidth: false,
                order: [[1, 'asc'], [2, 'asc']],
                columnDefs: [
                    { targets: 0, orderable: false },
                    { targets: 1, orderData: [1, 2] },
                    { targets: 2, orderData: [2, 1], type: 'num' },
                    { targets: 3, orderData: [3, 2] },
                    { targets: 4, orderData: [4, 2] }
                ],
                dom: 't<"d-flex flex-wrap justify-content-between align-items-center mt-2 px-2"ip>',
                pageLength: 25,
                language: {
                    info: 'Menampilkan _START_ s/d _END_ dari _TOTAL_ aset',
                    infoEmpty: 'Tidak ada aset yang cocok',
                    zeroRecords: '<div class="text-center py-4 text-muted"><i class="fas fa-search fa-2x mb-2 d-block text-secondary"></i>Tidak ditemukan aset dengan kriteria pencarian tersebut</div>',
                    paginate: {
                        first: 'Awal',
                        last: 'Akhir',
                        next: '&rsaquo;',
                        previous: '&lsaquo;'
                    }
                },
                drawCallback: function() {
                    // Sync checkboxes on current page with selectedAssetMap
                    $('#tableAlokasiBarang .asset-checkbox-alokasi').each(function() {
                        var id = String($(this).val());
                        $(this).prop('checked', selectedAssetMap.has(id));
                    });
                    updateAlokasiSelectionUI();
                }
            });
        } else if (alokasiDataTable) {
            alokasiDataTable.columns.adjust();
        }

        // Focus search input
        setTimeout(function() {
            $('#inputSearchAlokasi').focus();
        }, 150);
    });

    // Real-time search handler with debounce
    var searchDebounceTimer = null;
    $('#inputSearchAlokasi').on('input keyup', function() {
        var val = $(this).val();
        if (val.trim() !== '') {
            $('#wrapperClearSearch').show();
        } else {
            $('#wrapperClearSearch').hide();
        }

        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(function() {
            if (alokasiDataTable) {
                alokasiDataTable.search(val).draw();
            }
        }, 200);
    });

    // Clear search button
    $('#btnClearSearchAlokasi').on('click', function() {
        $('#inputSearchAlokasi').val('').trigger('input').focus();
    });

    // Change filter sumber & kondisi
    $('#filterSumberAlokasi, #filterKondisiAlokasi').on('change', function() {
        if (alokasiDataTable) {
            alokasiDataTable.draw();
        }
    });

    // Individual Checkbox Click
    $(document).on('change', '#tableAlokasiBarang .asset-checkbox-alokasi', function() {
        var $chk = $(this);
        var id = String($chk.val());
        if ($chk.is(':checked')) {
            selectedAssetMap.set(id, {
                id: id,
                kode: $chk.data('kode'),
                nup: $chk.data('nup'),
                nama: $chk.data('nama')
            });
        } else {
            selectedAssetMap.delete(id);
            if (isFilterOnlySelected) {
                if (alokasiDataTable) alokasiDataTable.draw();
            }
        }
        updateAlokasiSelectionUI();
    });

    // Select All in filtered results
    $('#checkAllAlokasi').on('change', function() {
        var isChecked = $(this).is(':checked');
        if (!alokasiDataTable) return;

        alokasiDataTable.rows({ filter: 'applied' }).every(function() {
            var rowNode = this.node();
            var chk = $(rowNode).find('.asset-checkbox-alokasi');
            if (chk.length) {
                var id = String(chk.val());
                chk.prop('checked', isChecked);
                if (isChecked) {
                    selectedAssetMap.set(id, {
                        id: id,
                        kode: chk.data('kode'),
                        nup: chk.data('nup'),
                        nama: chk.data('nama')
                    });
                } else {
                    selectedAssetMap.delete(id);
                }
            }
        });
        updateAlokasiSelectionUI();
    });

    // Reset All Selection
    $('#btnResetSelection').on('click', function() {
        selectedAssetMap.clear();
        $('#tableAlokasiBarang .asset-checkbox-alokasi').prop('checked', false);
        $('#checkAllAlokasi').prop('checked', false);
        if (isFilterOnlySelected) {
            isFilterOnlySelected = false;
            $('#btnToggleOnlySelected').removeClass('btn-primary text-white').addClass('btn-outline-primary');
            $('#textToggleOnlySelected').text('Tampilkan Hanya yang Dipilih');
            if (alokasiDataTable) alokasiDataTable.draw();
        }
        updateAlokasiSelectionUI();
    });

    // Toggle "Tampilkan Hanya yang Dipilih"
    $('#btnToggleOnlySelected').on('click', function() {
        isFilterOnlySelected = !isFilterOnlySelected;
        if (isFilterOnlySelected) {
            $(this).removeClass('btn-outline-primary').addClass('btn-primary text-white');
            $('#textToggleOnlySelected').text('Tampilkan Semua Aset');
        } else {
            $(this).removeClass('btn-primary text-white').addClass('btn-outline-primary');
            $('#textToggleOnlySelected').text('Tampilkan Hanya yang Dipilih');
        }
        if (alokasiDataTable) {
            alokasiDataTable.draw();
        }
    });

    // Form Submit Handler: Inject all selected IDs from Map into hidden inputs
    $('#form-alokasi-barang').on('submit', function(e) {
        if (selectedAssetMap.size === 0) {
            e.preventDefault();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih Aset Terlebih Dahulu',
                    text: 'Silakan centang minimal 1 barang inventaris yang ingin dialokasikan ke ruangan ini.',
                    confirmButtonText: 'Mengerti'
                });
            } else {
                alert('Silakan pilih minimal satu barang untuk dialokasikan.');
            }
            return false;
        }

        // Hapus input checkbox eksisting di form agar tidak bentrok
        $(this).find('.injected-asset-id').remove();

        // Inject all selected IDs
        selectedAssetMap.forEach(function(val, id) {
            $('<input>').attr({
                type: 'hidden',
                name: 'asset_ids[]',
                value: id,
                class: 'injected-asset-id'
            }).appendTo('#form-alokasi-barang');
        });

        return true;
    });

    // Auto-focus manual input when switching tab
    $('a[data-toggle="pill"]').on('shown.bs.tab', function(e) {
        if (e.target.id === 'pills-manual-tab') {
            stopCamera();
            var input = document.getElementById('input-scan-code');
            if (input) input.focus();
        } else if (e.target.id === 'pills-camera-tab') {
            var selectCam = document.getElementById('camera-select');
            startCamera(selectCam && selectCam.value ? selectCam.value : null);
        }
    });
});
</script>
<?= $this->endSection(); ?>

