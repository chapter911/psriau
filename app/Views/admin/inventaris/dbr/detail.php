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
                        <button type="button" class="btn btn-info btn-sm px-3 shadow-sm font-weight-bold text-white" data-toggle="modal" data-target="#modal-scan-qr" style="border-radius: 6px; background: #0284c7; border-color: #0284c7;">
                            <i class="fas fa-qrcode mr-1"></i> Scan QR / Register
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
                                    <th class="text-center align-middle" style="width: 80px;">NUP</th>
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
                                        <td class="align-middle">
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
                                        <th style="width: 75px;" class="text-center align-middle">NUP</th>
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
                                            <td class="align-middle">
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
    .scan-laser {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: linear-gradient(to right, transparent, #38bdf8, #818cf8, transparent);
        box-shadow: 0 0 14px #38bdf8, 0 0 24px rgba(56, 189, 248, 0.6);
        animation: scanLaserAnim 2s ease-in-out infinite alternate;
        z-index: 12;
        pointer-events: none;
    }
    @keyframes scanLaserAnim {
        0% { top: 4%; }
        100% { top: 96%; }
    }
    #qr-reader {
        width: 100% !important;
        border: none !important;
        padding: 0 !important;
    }
    #qr-reader video {
        width: 100% !important;
        height: 100% !important;
        min-height: 360px !important;
        max-height: 520px !important;
        object-fit: cover !important;
        border-radius: 12px !important;
    }
    #qr-reader-container {
        position: relative;
        width: 100%;
        max-width: 640px;
        margin: 0 auto;
        background: #020617;
        border-radius: 12px;
        overflow: hidden;
        min-height: 380px;
        height: 50vh;
        max-height: 520px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 30px rgba(0,0,0,0.35);
    }
    .scanner-reticle-box {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 78%;
        max-width: 320px;
        height: 78%;
        max-height: 320px;
        pointer-events: none;
        z-index: 11;
        border-radius: 12px;
    }
    .reticle-corner {
        position: absolute;
        width: 28px;
        height: 28px;
        border-color: #38bdf8;
        border-style: solid;
        border-width: 0;
    }
    .reticle-corner.top-left {
        top: 0;
        left: 0;
        border-top-width: 3.5px;
        border-left-width: 3.5px;
        border-top-left-radius: 10px;
    }
    .reticle-corner.top-right {
        top: 0;
        right: 0;
        border-top-width: 3.5px;
        border-right-width: 3.5px;
        border-top-right-radius: 10px;
    }
    .reticle-corner.bottom-left {
        bottom: 0;
        left: 0;
        border-bottom-width: 3.5px;
        border-left-width: 3.5px;
        border-bottom-left-radius: 10px;
    }
    .reticle-corner.bottom-right {
        bottom: 0;
        right: 0;
        border-bottom-width: 3.5px;
        border-right-width: 3.5px;
        border-bottom-right-radius: 10px;
    }
    .reticle-badge {
        position: absolute;
        bottom: 12px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(15, 23, 42, 0.85);
        color: #e2e8f0;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 3px 12px;
        border-radius: 20px;
        border: 1px solid rgba(56, 189, 248, 0.35);
        white-space: nowrap;
        z-index: 11;
        pointer-events: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.4);
    }
    @media (max-width: 767.98px) {
        #modal-scan-qr .modal-dialog {
            margin: 0.35rem auto;
            max-width: calc(100% - 0.7rem);
        }
        #modal-scan-qr .modal-body {
            padding: 0.65rem !important;
        }
        #modal-scan-qr .modal-header {
            padding: 0.75rem 1rem !important;
        }
        #qr-reader-container {
            min-height: 360px !important;
            height: 52vh !important;
            max-height: 520px !important;
        }
        #qr-reader video {
            min-height: 350px !important;
            height: 52vh !important;
        }
        #modal-scan-qr .nav-pills .nav-link {
            font-size: 0.8rem;
            padding: 0.45rem 0.5rem;
        }
    }
</style>

<div class="modal fade" id="modal-scan-qr" tabindex="-1" role="dialog" aria-labelledby="modalScanQrLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 14px; border: none; box-shadow: 0 12px 36px rgba(0,0,0,0.2); overflow: hidden;">
            <div class="modal-header py-3" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff;">
                <div>
                    <h5 class="modal-title font-weight-bold mb-0" id="modalScanQrLabel">
                        <i class="fas fa-qrcode text-info mr-2"></i> Scan QR Code / Kode Register Aset BMN
                    </h5>
                    <small style="color: #94a3b8;">Ruangan Tujuan: <strong class="text-white"><?= esc($room['nama_ruangan']); ?> (<?= esc($room['kode_ruangan']); ?>)</strong></small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" id="btn-close-modal-x" style="opacity: 0.85;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3 p-md-4 bg-light">
                <!-- Nav Tabs Scanner Mode -->
                <ul class="nav nav-pills nav-justified mb-3 bg-white p-1 rounded border shadow-sm" id="pills-scan-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active font-weight-bold py-2" id="pills-camera-tab" data-toggle="pill" href="#pills-camera" role="tab">
                            <i class="fas fa-camera mr-1"></i> <span class="d-none d-sm-inline">Kamera </span>Scanner Live (HP)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold py-2" id="pills-manual-tab" data-toggle="pill" href="#pills-manual" role="tab">
                            <i class="fas fa-barcode mr-1"></i> <span class="d-none d-sm-inline">Scanner </span>Fisik USB / Manual
                        </a>
                    </li>
                </ul>

                <div class="tab-content" id="pills-scan-tabContent">
                    <!-- TAB A: KAMERA SCANNER -->
                    <div class="tab-pane fade show active" id="pills-camera" role="tabpanel">
                        <div class="card border-0 shadow-sm mb-3" style="border-radius: 10px;">
                            <div class="card-body p-2 p-md-3 text-center">
                                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap" style="gap: 6px;">
                                    <div class="small font-weight-bold text-muted d-flex align-items-center" id="cam-status">
                                        <i class="fas fa-circle text-warning mr-1"></i> Kamera siap dinyalakan
                                    </div>
                                    <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                                        <button type="button" class="btn btn-warning btn-sm px-2 text-dark font-weight-bold shadow-sm" id="btn-toggle-torch" style="border-radius: 6px; display: none;" title="Nyalakan/Matikan Senter HP">
                                            <i class="fas fa-lightbulb mr-1"></i> <span id="torch-text">Flash</span>
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-sm px-2 shadow-sm font-weight-bold" id="btn-flip-cam" style="border-radius: 6px;" title="Ganti Kamera Belakang / Depan">
                                            <i class="fas fa-sync-alt mr-1"></i> <span class="d-none d-sm-inline">Ganti </span>Kamera
                                        </button>
                                        <select id="camera-select" class="form-control form-control-sm custom-select" style="max-width: 170px; border-radius: 6px; font-size: 0.8rem; display: none;"></select>
                                        <button type="button" class="btn btn-primary btn-sm px-3 font-weight-bold shadow-sm" id="btn-toggle-cam" style="border-radius: 6px;">
                                            <i class="fas fa-video mr-1"></i> <span id="btn-cam-text">Nyalakan</span>
                                        </button>
                                    </div>
                                </div>
                                <div id="qr-reader-container">
                                    <div id="scan-laser-line" class="scan-laser" style="display: none;"></div>
                                    
                                    <!-- Reticle targeting overlay -->
                                    <div id="scanner-reticle" class="scanner-reticle-box" style="display: none;">
                                        <div class="reticle-corner top-left"></div>
                                        <div class="reticle-corner top-right"></div>
                                        <div class="reticle-corner bottom-left"></div>
                                        <div class="reticle-corner bottom-right"></div>
                                        <div class="reticle-badge">
                                            <i class="fas fa-crosshairs text-info mr-1"></i> Bidik QR Code BMN
                                        </div>
                                    </div>

                                    <div id="qr-reader"></div>
                                    <div id="camera-placeholder" class="text-white text-center p-4">
                                        <i class="fas fa-camera fa-3x mb-3 text-info" style="opacity: 0.75;"></i>
                                        <h6 class="font-weight-bold text-white mb-1">Kamera Belakang Siap Digunakan</h6>
                                        <p class="small text-muted mb-0" style="max-width: 320px; margin: 0 auto;">Arahkan kamera smartphone ke stiker label QR Code SIMAN BMN pada fisik barang.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB B: MANUAL INPUT / SCANNER FISIK -->
                    <div class="tab-pane fade" id="pills-manual" role="tabpanel">
                        <div class="card border-0 shadow-sm mb-3" style="border-radius: 10px;">
                            <div class="card-body p-3">
                                <label class="small font-weight-bold text-dark mb-1">
                                    <i class="fas fa-barcode mr-1 text-primary"></i> Scan dengan Barcode Scanner Fisik atau Ketik Kode Register / Kode Barang:
                                </label>
                                <div class="input-group">
                                    <input type="text" id="input-scan-code" class="form-control form-control-lg font-mono" placeholder="Scan barcode atau paste Kode Register (32 karakter hex)..." style="font-size: 0.95rem; border-radius: 8px 0 0 8px;">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-primary px-4 font-weight-bold" id="btn-lookup-manual" style="border-radius: 0 8px 8px 0;">
                                            <i class="fas fa-search mr-1"></i> Cari
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-info-circle mr-1"></i> Scanner barcode/QR USB otomatis mengirimkan tombol Enter yang langsung memicu pencarian dan alokasi.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fast Auto-Add Option -->
                <div class="d-flex justify-content-between align-items-center bg-white p-2 px-3 rounded border mb-3 shadow-sm">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="chk-auto-allocate" checked>
                        <label class="custom-control-label small font-weight-bold text-dark" for="chk-auto-allocate" style="cursor: pointer;">
                            <i class="fas fa-bolt text-warning mr-1"></i> Mode Cepat: Otomatis Alokasikan Langsung saat QR Terdeteksi
                        </label>
                    </div>
                    <span class="badge badge-success px-3 py-2 font-weight-bold" id="session-count-badge" style="font-size: 0.85rem;">0 Dialokasikan</span>
                </div>

                <!-- Asset Result Card -->
                <div id="scan-result-card" class="card border-0 shadow-sm mb-3" style="display: none; border-radius: 10px; overflow: hidden;">
                    <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center border-bottom">
                        <span class="font-weight-bold small text-dark"><i class="fas fa-box-open mr-1 text-primary"></i> Data Aset Terdeteksi</span>
                        <span class="badge" id="res-status-badge">Menunggu</span>
                    </div>
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="font-weight-bold text-dark mb-1" id="res-nama-barang">-</h5>
                                <div class="text-muted small mb-2" id="res-merk-tipe">-</div>
                                <div class="d-flex flex-wrap" style="gap: 6px;">
                                    <span class="badge badge-light border">Kode: <strong id="res-kode-barang" class="font-mono">-</strong></span>
                                    <span class="badge badge-light border">NUP: <strong id="res-nup" class="font-mono">-</strong></span>
                                    <span class="badge badge-light border">Kondisi: <strong id="res-kondisi">-</strong></span>
                                    <span class="badge badge-light border">Tahun: <strong id="res-tahun">-</strong></span>
                                </div>
                                <div class="mt-2 small text-muted">
                                    Kode Register: <code class="font-weight-bold text-dark" id="res-kode-register" style="font-size: 0.85rem;">-</code>
                                </div>
                                <div class="mt-1 small">
                                    Lokasi Saat Ini: <strong id="res-lokasi-ruangan" class="text-info">-</strong>
                                </div>
                            </div>
                            <div class="col-md-4 text-center text-md-right mt-3 mt-md-0">
                                <button type="button" class="btn btn-success btn-block py-2 font-weight-bold shadow-sm" id="btn-do-allocate">
                                    <i class="fas fa-check-circle mr-1"></i> Alokasikan ke Ruangan Ini
                                </button>
                                <div id="res-already-msg" class="text-success small font-weight-bold mt-2" style="display: none;">
                                    <i class="fas fa-check-double mr-1"></i> Aset ini sudah berada di ruangan ini
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Session Scanned History -->
                <div id="session-history-card" class="card border-0 shadow-sm" style="display: none; border-radius: 10px;">
                    <div class="card-header bg-white py-2">
                        <span class="small font-weight-bold text-muted"><i class="fas fa-history mr-1"></i> Riwayat Alokasi Sesi Ini:</span>
                    </div>
                    <div class="card-body p-2" style="max-height: 140px; overflow-y: auto;">
                        <ul class="list-group list-group-flush small" id="session-history-list"></ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white py-3 justify-content-between">
                <small class="text-muted"><i class="fas fa-info-circle mr-1 text-info"></i> Selesai scan? Tutup modal untuk memperbarui daftar barang ruangan.</small>
                <button type="button" class="btn btn-dark px-4" data-dismiss="modal" id="btn-close-scan-modal" style="border-radius: 6px;">
                    <i class="fas fa-times mr-1"></i> Selesai & Tutup
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Script HTML5 QR Code Library & Handler -->
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

    // 2. QR Code Scanner & Register Asset Logic
    var html5QrCode = null;
    var isCameraRunning = false;
    var currentAsset = null;
    var sessionAllocatedCount = 0;
    var lastScannedCode = '';
    var lastScannedTime = 0;
    var scanCooldown = 1800; // ms

    var csrfTokenName = '<?= csrf_token(); ?>';
    var csrfHash = '<?= csrf_hash(); ?>';
    var lookupUrl = '<?= site_url("admin/inventaris/dbr/{$room['id']}/scan-lookup"); ?>';
    var allocateUrl = '<?= site_url("admin/inventaris/dbr/{$room['id']}/scan-alokasi"); ?>';

    // Play pleasant audio beep on successful detection
    function playBeep(isSuccess) {
        try {
            var audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            var osc = audioCtx.createOscillator();
            var gain = audioCtx.createGain();
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.type = 'sine';
            osc.frequency.setValueAtTime(isSuccess ? 880 : 440, audioCtx.currentTime);
            gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + 0.15);
            osc.start(audioCtx.currentTime);
            osc.stop(audioCtx.currentTime + 0.15);
        } catch (e) {
            // ignore audio fail
        }
    }

    // Lookup asset via Ajax
    function lookupAsset(code) {
        if (!code || !code.trim()) return;
        code = code.trim();

        var statusEl = document.getElementById('cam-status');
        if (statusEl) {
            statusEl.innerHTML = '<span class="spinner-border spinner-border-sm text-info mr-1"></span> Mencari data aset...';
        }

        var formData = new FormData();
        formData.append('code', code);
        formData.append(csrfTokenName, csrfHash);

        fetch(lookupUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(res) { return res.json(); })
        .then(function(res) {
            if (res.csrfHash) csrfHash = res.csrfHash;

            if (res.status === 'success' && res.data) {
                playBeep(true);
                currentAsset = res.data;
                displayAsset(res.data);

                var autoAllocate = document.getElementById('chk-auto-allocate');
                if (autoAllocate && autoAllocate.checked && !res.data.is_already_in_this_room) {
                    // Auto-allocate directly!
                    allocateAsset(res.data.id);
                }
            } else {
                playBeep(false);
                currentAsset = null;
                alert(res.message || 'Barang tidak ditemukan.');
                if (statusEl) {
                    statusEl.innerHTML = '<i class="fas fa-exclamation-circle text-danger mr-1"></i> Aset tidak ditemukan';
                }
            }
        })
        .catch(function(err) {
            console.error('Scan error:', err);
            if (statusEl) {
                statusEl.innerHTML = '<i class="fas fa-exclamation-triangle text-danger mr-1"></i> Gagal menghubungi server';
            }
        });
    }

    // Display asset card
    function displayAsset(asset) {
        var card = document.getElementById('scan-result-card');
        var namaEl = document.getElementById('res-nama-barang');
        var merkEl = document.getElementById('res-merk-tipe');
        var kodeEl = document.getElementById('res-kode-barang');
        var nupEl = document.getElementById('res-nup');
        var kondisiEl = document.getElementById('res-kondisi');
        var tahunEl = document.getElementById('res-tahun');
        var regEl = document.getElementById('res-kode-register');
        var lokEl = document.getElementById('res-lokasi-ruangan');
        var badgeEl = document.getElementById('res-status-badge');
        var btnAlloc = document.getElementById('btn-do-allocate');
        var alreadyMsg = document.getElementById('res-already-msg');

        if (card) card.style.display = 'block';
        if (namaEl) namaEl.textContent = asset.nama_barang;
        if (merkEl) merkEl.textContent = (asset.merk_tipe && asset.merk_tipe !== '-') ? asset.merk_tipe : asset.kategori;
        if (kodeEl) kodeEl.textContent = asset.kode_barang;
        if (nupEl) nupEl.textContent = asset.nup || '-';
        if (kondisiEl) kondisiEl.textContent = (asset.kondisi === 'baik' ? 'Baik' : (asset.kondisi === 'rusak_ringan' ? 'Rusak Ringan' : 'Rusak Berat'));
        if (tahunEl) tahunEl.textContent = asset.tahun_perolehan;
        if (regEl) regEl.textContent = asset.kode_register || '-';
        if (lokEl) lokEl.textContent = asset.lokasi_ruangan;

        if (asset.is_already_in_this_room) {
            if (badgeEl) {
                badgeEl.className = 'badge badge-success';
                badgeEl.textContent = 'Sudah di Ruangan Ini';
            }
            if (btnAlloc) btnAlloc.style.display = 'none';
            if (alreadyMsg) alreadyMsg.style.display = 'block';
        } else {
            if (badgeEl) {
                badgeEl.className = 'badge badge-primary';
                badgeEl.textContent = 'Siap Dialokasikan';
            }
            if (btnAlloc) {
                btnAlloc.style.display = 'block';
                btnAlloc.disabled = false;
                btnAlloc.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Alokasikan ke Ruangan Ini';
            }
            if (alreadyMsg) alreadyMsg.style.display = 'none';
        }
    }

    // Allocate asset
    function allocateAsset(assetId) {
        var btnAlloc = document.getElementById('btn-do-allocate');
        if (btnAlloc) {
            btnAlloc.disabled = true;
            btnAlloc.innerHTML = '<span class="spinner-border spinner-border-sm mr-1"></span> Menyimpan...';
        }

        var formData = new FormData();
        formData.append('asset_id', assetId);
        formData.append(csrfTokenName, csrfHash);

        fetch(allocateUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(res) { return res.json(); })
        .then(function(res) {
            if (res.csrfHash) csrfHash = res.csrfHash;

            if (res.status === 'success') {
                sessionAllocatedCount++;
                var countBadge = document.getElementById('session-count-badge');
                if (countBadge) {
                    countBadge.textContent = sessionAllocatedCount + ' Dialokasikan';
                }

                // Append to history
                var histCard = document.getElementById('session-history-card');
                var histList = document.getElementById('session-history-list');
                if (histCard) histCard.style.display = 'block';
                if (histList && res.asset) {
                    var li = document.createElement('li');
                    li.className = 'list-group-item px-2 py-1 d-flex justify-content-between align-items-center bg-white border-0';
                    li.innerHTML = '<span><i class="fas fa-check text-success mr-1"></i> [' + res.asset.kode_barang + '] <strong>' + res.asset.nama_barang + '</strong> (NUP: ' + (res.asset.nup || '-') + ')</span><span class="badge badge-success">Sukses</span>';
                    histList.insertBefore(li, histList.firstChild);
                }

                if (currentAsset) {
                    currentAsset.is_already_in_this_room = true;
                    currentAsset.lokasi_ruangan = '<?= esc($room['nama_ruangan']); ?>';
                    displayAsset(currentAsset);
                }

                var statusEl = document.getElementById('cam-status');
                if (statusEl) {
                    statusEl.innerHTML = '<i class="fas fa-check-circle text-success mr-1"></i> Berhasil dialokasikan! Siap scan berikutnya.';
                }
            } else {
                alert(res.message || 'Gagal mengalokasikan barang.');
                if (btnAlloc) {
                    btnAlloc.disabled = false;
                    btnAlloc.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Alokasikan ke Ruangan Ini';
                }
            }
        })
        .catch(function(err) {
            console.error('Allocate error:', err);
            if (btnAlloc) {
                btnAlloc.disabled = false;
                btnAlloc.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Alokasikan ke Ruangan Ini';
            }
        });
    }

    // Button click allocate
    var btnDoAlloc = document.getElementById('btn-do-allocate');
    if (btnDoAlloc) {
        btnDoAlloc.addEventListener('click', function() {
            if (currentAsset && currentAsset.id) {
                allocateAsset(currentAsset.id);
            }
        });
    }

    // Manual input search
    var btnManual = document.getElementById('btn-lookup-manual');
    var inputCode = document.getElementById('input-scan-code');
    if (btnManual && inputCode) {
        btnManual.addEventListener('click', function() {
            lookupAsset(inputCode.value);
            inputCode.value = '';
            inputCode.focus();
        });

        inputCode.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                lookupAsset(inputCode.value);
                inputCode.value = '';
                inputCode.focus();
            }
        });
    }

    // Camera scanner variables & state
    var availableCameras = [];
    var currentCameraIndex = 0;
    var isTorchOn = false;

    function isRearCamera(camera) {
        if (!camera) return false;
        var lbl = (camera.label || '').toLowerCase();
        return lbl.includes('back') || lbl.includes('rear') || lbl.includes('belakang') || lbl.includes('environment');
    }

    // Camera scanner start/stop
    function startCamera(cameraId) {
        if (!window.Html5Qrcode) {
            alert('Pustaka scanner belum dimuat.');
            return;
        }

        // Optimized scan configuration for mobile speed & responsiveness
        var config = {
            fps: 24, // Rapid scan framerate
            qrbox: function(viewfinderWidth, viewfinderHeight) {
                var minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                var boxEdge = Math.max(260, Math.floor(minEdge * 0.82));
                return { width: boxEdge, height: boxEdge };
            },
            aspectRatio: 1.0,
            experimentalFeatures: {
                useBarCodeDetectorIfSupported: true // Native hardware-accelerated scanning on mobile browsers
            }
        };

        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("qr-reader");
        }

        var placeholder = document.getElementById('camera-placeholder');
        var laser = document.getElementById('scan-laser-line');
        var reticle = document.getElementById('scanner-reticle');
        var statusEl = document.getElementById('cam-status');
        var btnText = document.getElementById('btn-cam-text');

        // Priority: if specific cameraId provided, use deviceId; else ALWAYS default to back camera (facingMode: "environment")
        var camConfig = cameraId ? { deviceId: { exact: cameraId } } : { facingMode: "environment" };

        if (statusEl) {
            statusEl.innerHTML = '<span class="spinner-border spinner-border-sm text-info mr-1"></span> Menghubungkan kamera belakang...';
        }

        function onScanSuccess(decodedText) {
            var now = Date.now();
            if (decodedText === lastScannedCode && (now - lastScannedTime) < scanCooldown) {
                return; // skip duplicate in cooldown
            }
            lastScannedCode = decodedText;
            lastScannedTime = now;

            // Haptic vibration feedback on smartphone
            if (navigator.vibrate) {
                try { navigator.vibrate(80); } catch(e) {}
            }

            lookupAsset(decodedText);
        }

        function onScanFailure(errorMessage) {
            // scanning frame ignored
        }

        function onCameraStarted() {
            isCameraRunning = true;
            if (placeholder) placeholder.style.display = 'none';
            if (laser) laser.style.display = 'block';
            if (reticle) reticle.style.display = 'block';
            if (statusEl) statusEl.innerHTML = '<i class="fas fa-circle text-success mr-1"></i> Kamera Belakang Aktif';
            if (btnText) btnText.textContent = 'Matikan';

            // Sync dropdown and enumerate cameras
            checkAndSyncCameras();
            // Check torch / flash support
            checkTorchSupport();
        }

        function onCameraError(err) {
            console.error('Camera error:', err);
            isCameraRunning = false;
            if (statusEl) statusEl.innerHTML = '<i class="fas fa-exclamation-triangle text-danger mr-1"></i> Kamera gagal diakses (' + err + ')';
            if (btnText) btnText.textContent = 'Nyalakan';
        }

        html5QrCode.start(
            camConfig,
            config,
            onScanSuccess,
            onScanFailure
        )
        .then(function() {
            onCameraStarted();
        })
        .catch(function(err) {
            console.warn('Camera start with config error, attempting fallback:', err);
            // If deviceId exact constraint failed, fallback to environment facingMode
            if (camConfig && camConfig.deviceId) {
                html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess, onScanFailure)
                .then(function() {
                    onCameraStarted();
                })
                .catch(function(err2) {
                    onCameraError(err2);
                });
            } else {
                onCameraError(err);
            }
        });
    }

    function stopCamera(callback) {
        if (html5QrCode && isCameraRunning) {
            html5QrCode.stop().then(function() {
                isCameraRunning = false;
                isTorchOn = false;
                var btnTorch = document.getElementById('btn-toggle-torch');
                if (btnTorch) btnTorch.style.display = 'none';
                var placeholder = document.getElementById('camera-placeholder');
                var laser = document.getElementById('scan-laser-line');
                var reticle = document.getElementById('scanner-reticle');
                var statusEl = document.getElementById('cam-status');
                var btnText = document.getElementById('btn-cam-text');
                if (placeholder) placeholder.style.display = 'block';
                if (laser) laser.style.display = 'none';
                if (reticle) reticle.style.display = 'none';
                if (statusEl) statusEl.innerHTML = '<i class="fas fa-circle text-warning mr-1"></i> Kamera dimatikan';
                if (btnText) btnText.textContent = 'Nyalakan';
                if (typeof callback === 'function') callback();
            }).catch(function(err) {
                console.error('Stop camera error:', err);
                if (typeof callback === 'function') callback();
            });
        } else {
            if (typeof callback === 'function') callback();
        }
    }

    // Check torch / flash support
    function checkTorchSupport() {
        var btnTorch = document.getElementById('btn-toggle-torch');
        if (!btnTorch) return;
        try {
            var videoEl = document.querySelector('#qr-reader video');
            if (videoEl && videoEl.srcObject) {
                var track = videoEl.srcObject.getVideoTracks()[0];
                if (track && track.getCapabilities) {
                    var capabilities = track.getCapabilities();
                    if (capabilities.torch) {
                        btnTorch.style.display = 'inline-block';
                        btnTorch.className = isTorchOn ? 'btn btn-warning btn-sm px-2 text-dark font-weight-bold shadow-sm' : 'btn btn-outline-warning btn-sm px-2 font-weight-bold shadow-sm';
                        return;
                    }
                }
            }
        } catch (e) {}
        btnTorch.style.display = 'none';
    }

    // Toggle torch / flash
    var btnTorch = document.getElementById('btn-toggle-torch');
    if (btnTorch) {
        btnTorch.addEventListener('click', function() {
            try {
                var videoEl = document.querySelector('#qr-reader video');
                if (videoEl && videoEl.srcObject) {
                    var track = videoEl.srcObject.getVideoTracks()[0];
                    if (track && track.applyConstraints) {
                        isTorchOn = !isTorchOn;
                        track.applyConstraints({
                            advanced: [{ torch: isTorchOn }]
                        }).then(function() {
                            var torchText = document.getElementById('torch-text');
                            if (torchText) torchText.textContent = isTorchOn ? 'Flash On' : 'Flash';
                            btnTorch.className = isTorchOn ? 'btn btn-warning btn-sm px-2 text-dark font-weight-bold shadow-sm' : 'btn btn-outline-warning btn-sm px-2 font-weight-bold shadow-sm';
                        }).catch(function(e) {
                            console.log('Torch error:', e);
                        });
                    }
                }
            } catch (e) {}
        });
    }

    // Check and populate cameras in dropdown
    function checkAndSyncCameras() {
        if (!window.Html5Qrcode || !Html5Qrcode.getCameras) return;

        Html5Qrcode.getCameras().then(function(cameras) {
            if (!cameras || cameras.length === 0) return;
            availableCameras = cameras;

            var selectCam = document.getElementById('camera-select');

            if (selectCam) {
                var currentSelected = selectCam.value;
                selectCam.innerHTML = '';

                var rearIdx = -1;
                cameras.forEach(function(c, i) {
                    var isRear = isRearCamera(c);
                    if (isRear && rearIdx === -1) rearIdx = i;

                    var opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = (isRear ? '📷 Belakang: ' : '📱 Depan: ') + (c.label || ('Kamera ' + (i + 1)));
                    selectCam.appendChild(opt);
                });

                if (currentSelected) {
                    selectCam.value = currentSelected;
                } else if (rearIdx !== -1) {
                    selectCam.value = cameras[rearIdx].id;
                    currentCameraIndex = rearIdx;
                }

                if (cameras.length > 1) {
                    selectCam.style.display = 'inline-block';
                }
            }
        }).catch(function(err) {
            console.log('getCameras error:', err);
        });
    }

    // Camera select change listener
    var selectCamEl = document.getElementById('camera-select');
    if (selectCamEl) {
        selectCamEl.addEventListener('change', function() {
            var chosenId = this.value;
            if (isCameraRunning) {
                stopCamera(function() {
                    startCamera(chosenId);
                });
            } else {
                startCamera(chosenId);
            }
        });
    }

    // Flip camera button (cycle cameras or toggle back/front)
    var btnFlipCam = document.getElementById('btn-flip-cam');
    if (btnFlipCam) {
        btnFlipCam.addEventListener('click', function() {
            if (availableCameras && availableCameras.length > 1) {
                currentCameraIndex = (currentCameraIndex + 1) % availableCameras.length;
                var nextCamera = availableCameras[currentCameraIndex];
                if (selectCamEl) selectCamEl.value = nextCamera.id;
                if (isCameraRunning) {
                    stopCamera(function() {
                        startCamera(nextCamera.id);
                    });
                } else {
                    startCamera(nextCamera.id);
                }
            } else {
                // If only 1 camera enumerated, restart with environment
                if (isCameraRunning) {
                    stopCamera(function() {
                        startCamera(null);
                    });
                } else {
                    startCamera(null);
                }
            }
        });
    }

    // Toggle camera button
    var btnToggleCam = document.getElementById('btn-toggle-cam');
    if (btnToggleCam) {
        btnToggleCam.addEventListener('click', function() {
            if (isCameraRunning) {
                stopCamera();
            } else {
                var chosenId = (selectCamEl && selectCamEl.value) ? selectCamEl.value : null;
                startCamera(chosenId);
            }
        });
    }

    // Modal lifecycle
    $('#modal-scan-qr').on('shown.bs.modal', function() {
        // Automatically start rear camera by default
        if (window.Html5Qrcode && Html5Qrcode.getCameras) {
            Html5Qrcode.getCameras().then(function(cameras) {
                if (cameras && cameras.length > 0) {
                    availableCameras = cameras;
                    var rearCam = cameras.find(isRearCamera);
                    if (rearCam) {
                        currentCameraIndex = cameras.indexOf(rearCam);
                        startCamera(rearCam.id);
                    } else {
                        // Pass null to let startCamera request facingMode: "environment"
                        startCamera(null);
                    }
                } else {
                    startCamera(null);
                }
            }).catch(function() {
                startCamera(null);
            });
        } else {
            startCamera(null);
        }
    });

    $('#modal-scan-qr').on('hidden.bs.modal', function() {
        stopCamera();
        if (sessionAllocatedCount > 0) {
            // Reload to reflect newly allocated items in table
            window.location.reload();
        }
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

