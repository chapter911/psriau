<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<style>
    .font-mono {
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace !important;
    }
    .inv-hero-card {
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 18px -4px rgba(0, 0, 0, 0.05);
        background: #ffffff;
    }
    .inv-kpi-tile {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px 16px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none !important;
        display: block;
        position: relative;
        overflow: hidden;
    }
    .inv-kpi-tile:hover {
        transform: translateY(-2px);
        background: #ffffff;
        box-shadow: 0 8px 18px -4px rgba(0, 0, 0, 0.08);
        border-color: #cbd5e1;
    }
    .inv-kpi-tile.active-all {
        background: #f0f9ff;
        border-color: #0284c7;
        box-shadow: 0 4px 12px rgba(2, 132, 199, 0.15);
    }
    .inv-kpi-tile.active-all::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3.5px;
        background: #0284c7;
    }
    .inv-kpi-tile.active-kantor {
        background: #eff6ff;
        border-color: #2563eb;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
    }
    .inv-kpi-tile.active-kantor::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3.5px;
        background: #2563eb;
    }
    .inv-kpi-tile.active-mobiler {
        background: #fffbeb;
        border-color: #d97706;
        box-shadow: 0 4px 12px rgba(217, 119, 6, 0.15);
    }
    .inv-kpi-tile.active-mobiler::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3.5px;
        background: #d97706;
    }
    .inv-kpi-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
</style>

<div class="container-fluid">

    <!-- Hero Card: Header, Consolidated Actions & Interactive KPI Filter Tiles -->
    <div class="card inv-hero-card mb-3">
        <div class="card-body p-3 p-md-4">
            
            <!-- Header Top: Context, Title & Action Buttons -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap: 12px;">
                <div>
                    <div class="d-flex align-items-center mb-1 text-muted small">
                        <span><i class="fas fa-boxes-stacked mr-1"></i> Modul Inventarisasi</span>
                        <span class="mx-2">/</span>
                        <span class="text-primary font-weight-bold">Daftar Barang</span>
                    </div>
                    <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                        <h4 class="font-weight-bold text-dark mb-0" style="font-size: 1.35rem; letter-spacing: -0.3px;">
                            Daftar Barang Inventarisasi
                        </h4>
                        <span class="badge badge-light border px-2.5 py-1 text-muted font-weight-bold" style="font-size: 0.82rem; border-radius: 6px;">
                            <?= number_format((int) ($summary['total'] ?? 0)); ?> Unit Terdaftar
                        </span>
                    </div>
                    <p class="text-muted small mb-0 mt-1">
                        Penatausahaan aset BMN, klasifikasi peruntukan operasional kantor satker vs mobiler sekolah, serta distribusi ke DBR.
                    </p>
                </div>

                <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                    <?php if (! empty($can_edit)): ?>
                        <button type="button" class="btn btn-warning btn-sm px-3 shadow-sm font-weight-bold text-dark" data-toggle="modal" data-target="#modal-update-peruntukan-massal" style="border-radius: 8px;">
                            <i class="fas fa-sliders-h mr-1.5"></i> Update Peruntukan Massal (NUP)
                        </button>
                    <?php endif; ?>
                    <a href="<?= site_url('admin/inventaris/dbr'); ?>" class="btn btn-outline-info btn-sm px-3 shadow-sm font-weight-bold" style="border-radius: 8px;">
                        <i class="fas fa-door-open mr-1.5"></i> Ruangan (DBR)
                    </a>
                    <?php if (! empty($can_import)): ?>
                        <button type="button" class="btn btn-outline-success btn-sm px-3 shadow-sm font-weight-bold" data-toggle="modal" data-target="#modal-import-siman" style="border-radius: 8px;">
                            <i class="fas fa-file-import mr-1.5"></i> Import SIMAN
                        </button>
                    <?php endif; ?>
                    <?php if (! empty($can_export)): ?>
                        <a href="<?= site_url('admin/inventaris/barang/export?' . http_build_query(['peruntukan' => $filterPeruntukan, 'kategori' => $filterKategori, 'kondisi' => $filterKondisi, 'lokasi' => $filterLokasi])); ?>" class="btn btn-outline-secondary btn-sm px-3 shadow-sm font-weight-bold" style="border-radius: 8px;" title="Export Excel">
                            <i class="fas fa-file-excel text-success mr-1.5"></i> Export Excel
                        </a>
                    <?php endif; ?>
                    <?php if (! empty($can_add)): ?>
                        <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm font-weight-bold" data-toggle="modal" data-target="#modal-tambah-inventaris" style="border-radius: 8px;">
                            <i class="fas fa-plus mr-1.5"></i> Tambah Barang
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Row 2: 4 Interactive KPI Tiles (Click to Quick Filter Peruntukan) -->
            <div class="row" style="margin-left: -6px; margin-right: -6px;">
                <!-- Tile 1: Semua Barang -->
                <div class="col-12 col-sm-6 col-lg-3 px-1 mb-2 mb-lg-0">
                    <a href="<?= site_url('admin/inventaris/barang?' . http_build_query(array_merge($_GET, ['peruntukan' => '']))); ?>" class="inv-kpi-tile <?= empty($filterPeruntukan) ? 'active-all' : ''; ?>" title="Klik untuk menampilkan seluruh barang">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="small font-weight-bold text-muted text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">Semua Barang</span>
                            <div class="inv-kpi-icon bg-light text-primary" style="border: 1px solid #e2e8f0;">
                                <i class="fas fa-boxes-stacked"></i>
                            </div>
                        </div>
                        <div class="h4 font-weight-bold text-dark mb-0" style="letter-spacing: -0.5px;">
                            <?= number_format((int) ($summary['total'] ?? 0)); ?> <small class="text-muted" style="font-size: 0.82rem; font-weight: normal;">unit</small>
                        </div>
                        <div class="small <?= empty($filterPeruntukan) ? 'text-primary font-weight-bold' : 'text-muted'; ?> mt-1">
                            <?= empty($filterPeruntukan) ? '● Sedang Menampilkan Semua' : 'Klik untuk tampilkan semua'; ?>
                        </div>
                    </a>
                </div>

                <!-- Tile 2: Kantor / Satker -->
                <div class="col-12 col-sm-6 col-lg-3 px-1 mb-2 mb-lg-0">
                    <a href="<?= site_url('admin/inventaris/barang?' . http_build_query(array_merge($_GET, ['peruntukan' => 'kantor']))); ?>" class="inv-kpi-tile <?= ($filterPeruntukan === 'kantor') ? 'active-kantor' : ''; ?>" title="Klik untuk menyaring khusus aset Kantor Satker">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="small font-weight-bold text-primary text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">Kantor / Satker</span>
                            <div class="inv-kpi-icon" style="background-color: #dbeafe; color: #1d4ed8;">
                                <i class="fas fa-building"></i>
                            </div>
                        </div>
                        <div class="h4 font-weight-bold text-dark mb-0" style="letter-spacing: -0.5px;">
                            <?= number_format((int) ($summary['total_kantor'] ?? $summary['kantor'] ?? 0)); ?> <small class="text-muted" style="font-size: 0.82rem; font-weight: normal;">unit</small>
                        </div>
                        <div class="small <?= ($filterPeruntukan === 'kantor') ? 'text-primary font-weight-bold' : 'text-muted'; ?> mt-1">
                            <?= ($filterPeruntukan === 'kantor') ? '● Filter Aktif: Aset Kantor (DBR)' : 'Aset Operasional & Ruangan'; ?>
                        </div>
                    </a>
                </div>

                <!-- Tile 3: Mobiler / Sekolah -->
                <div class="col-12 col-sm-6 col-lg-3 px-1 mb-2 mb-lg-0">
                    <a href="<?= site_url('admin/inventaris/barang?' . http_build_query(array_merge($_GET, ['peruntukan' => 'mobiler']))); ?>" class="inv-kpi-tile <?= ($filterPeruntukan === 'mobiler') ? 'active-mobiler' : ''; ?>" title="Klik untuk menyaring khusus aset Mobiler Sekolah">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="small font-weight-bold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem; color: #b45309 !important;">Mobiler / Sekolah</span>
                            <div class="inv-kpi-icon" style="background-color: #fef3c7; color: #d97706;">
                                <i class="fas fa-school"></i>
                            </div>
                        </div>
                        <div class="h4 font-weight-bold text-dark mb-0" style="letter-spacing: -0.5px;">
                            <?= number_format((int) ($summary['total_mobiler'] ?? $summary['mobiler'] ?? 0)); ?> <small class="text-muted" style="font-size: 0.82rem; font-weight: normal;">unit</small>
                        </div>
                        <div class="small <?= ($filterPeruntukan === 'mobiler') ? 'font-weight-bold' : 'text-muted'; ?> mt-1" style="<?= ($filterPeruntukan === 'mobiler') ? 'color: #b45309 !important;' : ''; ?>">
                            <?= ($filterPeruntukan === 'mobiler') ? '● Filter Aktif: Mobiler Sekolah' : 'Bantuan Sarpras Sekolah'; ?>
                        </div>
                    </a>
                </div>

                <!-- Tile 4: Kondisi Fisik -->
                <div class="col-12 col-sm-6 col-lg-3 px-1">
                    <div class="inv-kpi-tile" style="cursor: default;">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="small font-weight-bold text-muted text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">Kondisi Fisik</span>
                            <div class="inv-kpi-icon" style="background-color: #dcfce7; color: #15803d;">
                                <i class="fas fa-heartbeat"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center flex-wrap mt-1" style="gap: 5px;">
                            <span class="badge badge-success font-weight-bold px-2 py-1">
                                <?= number_format((int) ($summary['baik'] ?? 0)); ?> Baik
                            </span>
                            <span class="badge badge-warning text-dark font-weight-bold px-2 py-1">
                                <?= number_format((int) ($summary['rusak_ringan'] ?? 0)); ?> R.Ringan
                            </span>
                            <span class="badge badge-danger font-weight-bold px-2 py-1">
                                <?= number_format((int) ($summary['rusak_berat'] ?? 0)); ?> R.Berat
                            </span>
                        </div>
                        <div class="small text-muted mt-1">
                            Status Kelayakan Aset
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Main Data Card with Integrated Filter Strip -->
    <div class="card shadow-sm">
        
        <!-- Card Header: Title & Filter Status -->
        <div class="card-header">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-boxes text-primary mr-1"></i> Data Inventarisasi Barang
                <?php if ($filterPeruntukan === 'kantor'): ?>
                    <span class="badge badge-primary ml-2 px-2 py-1">
                        <i class="fas fa-building mr-1"></i> Difilter: Kantor / Satker
                    </span>
                <?php elseif ($filterPeruntukan === 'mobiler'): ?>
                    <span class="badge badge-warning text-dark ml-2 px-2 py-1">
                        <i class="fas fa-school mr-1"></i> Difilter: Mobiler / Sekolah
                    </span>
                <?php else: ?>
                    <span class="badge badge-secondary ml-2 px-2 py-1">
                        <i class="fas fa-layer-group mr-1"></i> Semua Peruntukan
                    </span>
                <?php endif; ?>

                <?php if (! empty($filterKategori) || ! empty($filterKondisi) || ! empty($filterLokasi)): ?>
                    <span class="badge badge-danger ml-1 px-2 py-1">
                        <i class="fas fa-filter mr-1"></i> Filter Detail Aktif
                    </span>
                <?php endif; ?>
            </h3>

            <div class="card-tools d-flex align-items-center" style="gap: 8px;">
                <button class="btn btn-sm btn-outline-secondary px-3" type="button" data-toggle="collapse" data-target="#collapseFilterBar" aria-expanded="true" aria-controls="collapseFilterBar">
                    <i class="fas fa-sliders-h mr-1"></i> Filter Detail <i class="fas fa-chevron-down ml-1" style="font-size: 0.72rem;"></i>
                </button>
                <?php if (! empty($filterKategori) || ! empty($filterKondisi) || ! empty($filterLokasi) || ! empty($filterPeruntukan)): ?>
                    <a href="<?= site_url('admin/inventaris/barang'); ?>" class="btn btn-sm btn-light border text-muted px-2.5" title="Reset Semua Filter">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Integrated Filter Strip (Collapsible) -->
        <div class="collapse show" id="collapseFilterBar">
            <div class="px-3 py-3" style="background-color: #f8fafc; border-bottom: 1px solid #dee2e6;">
                <form method="get" action="<?= site_url('admin/inventaris/barang'); ?>" id="form-filter-inventaris">
                    <?php if (! empty($filterPeruntukan)): ?>
                        <input type="hidden" name="peruntukan" value="<?= esc($filterPeruntukan); ?>">
                    <?php endif; ?>
                    <div class="row align-items-end" style="margin-left: -6px; margin-right: -6px;">
                        <div class="col-12 col-sm-6 col-md-3 px-1 mb-2 mb-md-0">
                            <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-tag mr-1 text-primary"></i> Kategori Aset</label>
                            <select name="kategori" class="form-control form-control-sm">
                                <option value="">Semua Kategori</option>
                                <?php foreach ($kategoriList as $kat): ?>
                                    <option value="<?= esc($kat); ?>" <?= ($filterKategori === $kat) ? 'selected' : ''; ?>><?= esc($kat); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3 px-1 mb-2 mb-md-0">
                            <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-heartbeat mr-1 text-success"></i> Kondisi Fisik</label>
                            <select name="kondisi" class="form-control form-control-sm">
                                <option value="">Semua Kondisi</option>
                                <option value="baik" <?= ($filterKondisi === 'baik') ? 'selected' : ''; ?>>Baik</option>
                                <option value="rusak_ringan" <?= ($filterKondisi === 'rusak_ringan') ? 'selected' : ''; ?>>Rusak Ringan</option>
                                <option value="rusak_berat" <?= ($filterKondisi === 'rusak_berat') ? 'selected' : ''; ?>>Rusak Berat</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3 px-1 mb-2 mb-md-0">
                            <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-map-marker-alt mr-1 text-danger"></i> Lokasi Ruangan</label>
                            <select name="lokasi" class="form-control form-control-sm">
                                <option value="">Semua Lokasi</option>
                                <?php foreach ($lokasiList as $lok): ?>
                                    <option value="<?= esc($lok); ?>" <?= ($filterLokasi === $lok) ? 'selected' : ''; ?>><?= esc($lok); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3 px-1 d-flex" style="gap: 6px;">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill shadow-sm font-weight-bold">
                                <i class="fas fa-search mr-1"></i> Terapkan Filter
                            </button>
                            <a href="<?= site_url('admin/inventaris/barang' . (! empty($filterPeruntukan) ? '?peruntukan=' . esc($filterPeruntukan) : '')); ?>" class="btn btn-outline-secondary btn-sm shadow-sm" title="Reset Filter Kategori/Kondisi/Lokasi">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table Body -->
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover w-100 js-datatable" data-order='[[1, "asc"], [2, "asc"]]'>
                    <thead class="thead-light">
                        <tr style="white-space: nowrap;">
                            <th style="width: 40px;" class="text-center align-middle" data-orderable="false">#</th>
                            <th class="align-middle" style="width: 120px;">Kode Barang</th>
                            <th class="text-center align-middle" style="width: 65px;">NUP</th>
                            <th class="text-center align-middle" style="width: 100px;">Peruntukan</th>
                            <th class="align-middle">Nama Barang</th>
                            <th class="align-middle" style="width: 130px;">Kategori</th>
                            <th class="align-middle" style="width: 140px;">Merk / Tipe</th>
                            <th class="text-center align-middle" style="width: 85px;">Jumlah</th>
                            <th class="text-center align-middle" style="width: 110px;">Kondisi</th>
                            <th class="align-middle" style="width: 140px;">Lokasi Ruangan</th>
                            <th class="text-center align-middle" style="width: 70px;">Tahun</th>
                            <?php if (! empty($can_edit) || ! empty($can_delete)): ?>
                                <th style="width: 90px;" class="text-center align-middle" data-orderable="false">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach (($items ?? []) as $item): ?>
                            <tr>
                                <td class="text-center align-middle"><?= $no++; ?></td>
                                <td class="align-middle font-mono font-weight-bold text-dark">
                                    <?= esc($item['kode_barang']); ?>
                                </td>
                                <td class="text-center align-middle" data-order="<?= (int) ($item['nup'] ?? 0); ?>">
                                    <?php if (! empty($item['nup'])): ?>
                                        <span class="badge badge-light border font-mono font-weight-bold px-2 py-1">
                                            <?= esc((string) $item['nup']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center align-middle" data-order="<?= ($item['peruntukan'] ?? 'kantor') === 'mobiler' ? 2 : 1; ?>">
                                    <?php if (($item['peruntukan'] ?? 'kantor') === 'mobiler'): ?>
                                        <span class="badge badge-warning text-dark font-weight-bold px-2 py-1" title="Diperuntukkan untuk Mobiler Sekolah">
                                            <i class="fas fa-school mr-1"></i> Mobiler
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-primary font-weight-bold px-2 py-1" title="Diperuntukkan untuk Kantor Satker">
                                            <i class="fas fa-building mr-1"></i> Kantor
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="align-middle">
                                    <div class="font-weight-bold text-primary"><?= esc($item['nama_barang']); ?></div>
                                    <?php if (! empty($item['keterangan'])): ?>
                                        <small class="text-muted d-block mt-0.5"><i class="fas fa-info-circle mr-1"></i><?= esc($item['keterangan']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="align-middle">
                                    <span class="badge badge-light border px-2 py-1"><?= esc($item['kategori']); ?></span>
                                </td>
                                <td class="align-middle"><?= esc($item['merk_tipe'] ?: '-'); ?></td>
                                <td class="text-center align-middle font-weight-bold">
                                    <?= number_format((int) ($item['jumlah'] ?? 1)); ?> <small class="text-muted font-weight-normal"><?= esc($item['satuan'] ?? 'Unit'); ?></small>
                                </td>
                                <td class="text-center align-middle">
                                    <?php if ($item['kondisi'] === 'baik'): ?>
                                        <span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i> Baik</span>
                                    <?php elseif ($item['kondisi'] === 'rusak_ringan'): ?>
                                        <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-exclamation-triangle mr-1"></i> Rusak Ringan</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger px-2 py-1"><i class="fas fa-times mr-1"></i> Rusak Berat</span>
                                    <?php endif; ?>
                                </td>
                                <td class="align-middle">
                                    <i class="fas fa-door-open text-muted mr-1"></i> <?= esc($item['lokasi_ruangan']); ?>
                                </td>
                                <td class="text-center align-middle"><?= esc((string) ($item['tahun_perolehan'] ?: '-')); ?></td>
                                <?php if (! empty($can_edit) || ! empty($can_delete)): ?>
                                    <td class="text-center align-middle" style="white-space: nowrap;">
                                        <div class="btn-group" role="group">
                                            <?php if (! empty($can_edit)): ?>
                                                <button
                                                    type="button"
                                                    class="btn btn-warning btn-sm btn-edit-inventaris"
                                                    data-toggle="modal"
                                                    data-target="#modal-edit-inventaris"
                                                    data-id="<?= esc((string) $item['id'], 'attr'); ?>"
                                                    data-kode="<?= esc((string) $item['kode_barang'], 'attr'); ?>"
                                                    data-nup="<?= esc((string) ($item['nup'] ?? ''), 'attr'); ?>"
                                                    data-register="<?= esc((string) ($item['kode_register'] ?? ''), 'attr'); ?>"
                                                    data-peruntukan="<?= esc((string) ($item['peruntukan'] ?? 'kantor'), 'attr'); ?>"
                                                    data-nama="<?= esc((string) $item['nama_barang'], 'attr'); ?>"
                                                    data-kategori="<?= esc((string) $item['kategori'], 'attr'); ?>"
                                                    data-merk="<?= esc((string) ($item['merk_tipe'] ?? ''), 'attr'); ?>"
                                                    data-jumlah="<?= esc((string) ($item['jumlah'] ?? 1), 'attr'); ?>"
                                                    data-satuan="<?= esc((string) ($item['satuan'] ?? 'Unit'), 'attr'); ?>"
                                                    data-kondisi="<?= esc((string) $item['kondisi'], 'attr'); ?>"
                                                    data-lokasi="<?= esc((string) $item['lokasi_ruangan'], 'attr'); ?>"
                                                    data-tahun="<?= esc((string) ($item['tahun_perolehan'] ?? ''), 'attr'); ?>"
                                                    data-keterangan="<?= esc((string) ($item['keterangan'] ?? ''), 'attr'); ?>"
                                                    title="Edit Barang"
                                                >
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if (! empty($can_delete)): ?>
                                                <button
                                                    type="button"
                                                    class="btn btn-danger btn-sm btn-delete-inventaris"
                                                    data-toggle="modal"
                                                    data-target="#modal-delete-inventaris"
                                                    data-id="<?= esc((string) $item['id'], 'attr'); ?>"
                                                    data-nama="<?= esc((string) $item['nama_barang'], 'attr'); ?>"
                                                    title="Hapus Barang"
                                                >
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
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

<!-- Modal Tambah Barang -->
<?php if (! empty($can_add)): ?>
<div class="modal fade" id="modal-tambah-inventaris" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.12);">
            <div class="modal-header bg-light py-3" style="border-bottom: 1px solid #e9eef5;">
                <h5 class="modal-title font-weight-bold text-dark" style="font-size: 1.1rem;">
                    <i class="fas fa-plus-circle text-primary mr-2"></i>Tambah Inventaris Barang
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('admin/inventaris/satker/tambah'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body py-4">
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold small text-dark">Kode Barang <span class="text-danger">*</span></label>
                            <input type="text" name="kode_barang" class="form-control font-weight-bold" placeholder="Contoh: 3050104001" required style="border-radius: 6px; font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;">
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="font-weight-bold small text-dark">NUP (No Urut)</label>
                            <input type="text" name="nup" class="form-control" placeholder="Contoh: 1" style="border-radius: 6px; font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;">
                        </div>
                        <div class="col-md-5 form-group">
                            <label class="font-weight-bold small text-dark">Kode Register (SIMAN BMN / QR)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white" style="border-radius: 6px 0 0 6px;"><i class="fas fa-qrcode text-muted"></i></span>
                                </div>
                                <input type="text" name="kode_register" class="form-control" placeholder="32-hex SIMAN (opsional)" style="border-radius: 0 6px 6px 0; font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace; font-size: 0.85rem;">
                            </div>
                            <small class="text-muted" style="font-size: 0.72rem;">Kode fisik 32-karakter dari aplikasi SIMAN / stiker label QR</small>
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="font-weight-bold small text-dark">Nama Barang <span class="text-danger">*</span></label>
                            <input type="text" name="nama_barang" class="form-control" placeholder="Nama lengkap barang / aset" required style="border-radius: 6px;">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-dark">Kategori <span class="text-danger">*</span></label>
                            <input type="text" name="kategori" class="form-control" list="list-kategori" placeholder="Peralatan Kantor, Komputer, Furniture..." required style="border-radius: 6px;">
                            <datalist id="list-kategori">
                                <option value="Peralatan Kantor">
                                <option value="Komputer & Elektronik">
                                <option value="Mebel & Furniture">
                                <option value="Kendaraan Dinas">
                                <option value="Peralatan Teknik Lapangan">
                                <option value="Pendingin Ruangan (AC)">
                            </datalist>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-dark">Merk / Tipe / Spesifikasi</label>
                            <input type="text" name="merk_tipe" class="form-control" placeholder="Contoh: Asus Zenbook, Toyota Hilux, dll." style="border-radius: 6px;">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold small text-dark">Jumlah (Unit BMN) <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah" class="form-control" value="1" min="1" max="1" readonly style="border-radius: 6px; background-color: #f8fafc;">
                            <small class="text-muted" style="font-size: 0.72rem;"><i class="fas fa-shield-alt mr-1 text-primary"></i>1 Kode & NUP = Tepat 1 Unit Fisik</small>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold small text-dark">Satuan <span class="text-danger">*</span></label>
                            <select name="satuan" id="tambah-satuan" class="form-control select2" data-tags="true" data-placeholder="-- Pilih atau Ketik Satuan --" required style="width: 100%;">
                                <?php foreach (($satuanOptions ?? ['Unit', 'Buah', 'Set', 'Pcs', 'Lembar', 'Paket', 'Kotak']) as $optSatuan): ?>
                                    <option value="<?= esc($optSatuan); ?>" <?= ($optSatuan === 'Unit') ? 'selected' : ''; ?>><?= esc($optSatuan); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted" style="font-size: 0.72rem;">Pilih dari daftar atau ketik satuan baru</small>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold small text-dark">Kondisi <span class="text-danger">*</span></label>
                            <select name="kondisi" class="form-control" required style="border-radius: 6px;">
                                <option value="baik">Baik</option>
                                <option value="rusak_ringan">Rusak Ringan</option>
                                <option value="rusak_berat">Rusak Berat</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-dark">Lokasi Ruangan <span class="text-danger">*</span></label>
                            <select name="lokasi_ruangan" id="tambah-lokasi" class="form-control select2" data-tags="true" data-placeholder="-- Pilih atau Ketik Ruangan --" required style="width: 100%;">
                                <option value=""></option>
                                <?php foreach (($ruanganOptions ?? []) as $optRuangan): ?>
                                    <option value="<?= esc($optRuangan); ?>"><?= esc($optRuangan); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted" style="font-size: 0.72rem;">Pilih dari daftar ruangan atau ketik langsung nama ruangan baru</small>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="font-weight-bold small text-dark">Tahun Perolehan</label>
                            <input type="number" name="tahun_perolehan" class="form-control" placeholder="Contoh: <?= date('Y'); ?>" min="1990" max="<?= date('Y') + 1; ?>" style="border-radius: 6px;">
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="font-weight-bold small text-dark">Peruntukan <span class="text-danger">*</span></label>
                            <div class="d-flex align-items-center mt-2" style="gap: 15px;">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="tambah_peruntukan_kantor" name="peruntukan" value="kantor" class="custom-control-input" checked>
                                    <label class="custom-control-label font-weight-normal text-primary" for="tambah_peruntukan_kantor">
                                        <i class="fas fa-building mr-1"></i> Kantor
                                    </label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="tambah_peruntukan_mobiler" name="peruntukan" value="mobiler" class="custom-control-input">
                                    <label class="custom-control-label font-weight-normal text-warning" for="tambah_peruntukan_mobiler">
                                        <i class="fas fa-school mr-1"></i> <strong class="text-dark">Mobiler</strong>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 form-group mb-0">
                            <label class="font-weight-bold small text-dark">Keterangan Tambahan</label>
                            <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan kondisi atau kelengkapan barang..." style="border-radius: 6px;"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-3" style="border-top: 1px solid #e9eef5;">
                    <button type="button" class="btn btn-secondary px-3" data-dismiss="modal" style="border-radius: 6px;">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm" style="border-radius: 6px;">
                        <i class="fas fa-save mr-1"></i> Simpan Barang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Edit Barang -->
<?php if (! empty($can_edit)): ?>
<div class="modal fade" id="modal-edit-inventaris" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.12);">
            <div class="modal-header bg-light py-3" style="border-bottom: 1px solid #e9eef5;">
                <h5 class="modal-title font-weight-bold text-dark" style="font-size: 1.1rem;">
                    <i class="fas fa-edit text-primary mr-2"></i>Ubah Data Inventaris Barang
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-edit-inventaris" action="" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body py-4">
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold small text-dark">Kode Barang <span class="text-danger">*</span></label>
                            <input type="text" id="edit-kode" name="kode_barang" class="form-control font-weight-bold" required style="border-radius: 6px; font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;">
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="font-weight-bold small text-dark">NUP</label>
                            <input type="text" id="edit-nup" name="nup" class="form-control" style="border-radius: 6px; font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;">
                        </div>
                        <div class="col-md-5 form-group">
                            <label class="font-weight-bold small text-dark">Kode Register (SIMAN BMN / QR)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white" style="border-radius: 6px 0 0 6px;"><i class="fas fa-qrcode text-muted"></i></span>
                                </div>
                                <input type="text" id="edit-kode-register" name="kode_register" class="form-control" placeholder="32-hex SIMAN (opsional)" style="font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace; font-size: 0.85rem;">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" id="btn-copy-edit-reg" title="Salin Kode Register" style="border-radius: 0 6px 6px 0;">
                                        <i class="far fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted" style="font-size: 0.72rem;">Kode fisik 32-karakter dari aplikasi SIMAN / stiker label QR</small>
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="font-weight-bold small text-dark">Nama Barang <span class="text-danger">*</span></label>
                            <input type="text" id="edit-nama" name="nama_barang" class="form-control" required style="border-radius: 6px;">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-dark">Kategori <span class="text-danger">*</span></label>
                            <input type="text" id="edit-kategori" name="kategori" class="form-control" required style="border-radius: 6px;">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-dark">Merk / Tipe / Spesifikasi</label>
                            <input type="text" id="edit-merk" name="merk_tipe" class="form-control" style="border-radius: 6px;">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold small text-dark">Jumlah (Unit BMN) <span class="text-danger">*</span></label>
                            <input type="number" id="edit-jumlah" name="jumlah" class="form-control" value="1" min="1" max="1" readonly style="border-radius: 6px; background-color: #f8fafc;">
                            <small class="text-muted" style="font-size: 0.72rem;"><i class="fas fa-shield-alt mr-1 text-primary"></i>1 Kode & NUP = Tepat 1 Unit Fisik</small>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold small text-dark">Satuan <span class="text-danger">*</span></label>
                            <select id="edit-satuan" name="satuan" class="form-control select2" data-tags="true" data-placeholder="-- Pilih atau Ketik Satuan --" required style="width: 100%;">
                                <?php foreach (($satuanOptions ?? ['Unit', 'Buah', 'Set', 'Pcs', 'Lembar', 'Paket', 'Kotak']) as $optSatuan): ?>
                                    <option value="<?= esc($optSatuan); ?>"><?= esc($optSatuan); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted" style="font-size: 0.72rem;">Pilih dari daftar atau ketik satuan baru</small>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold small text-dark">Kondisi <span class="text-danger">*</span></label>
                            <select id="edit-kondisi" name="kondisi" class="form-control" required style="border-radius: 6px;">
                                <option value="baik">Baik</option>
                                <option value="rusak_ringan">Rusak Ringan</option>
                                <option value="rusak_berat">Rusak Berat</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-dark">Lokasi Ruangan <span class="text-danger">*</span></label>
                            <select id="edit-lokasi" name="lokasi_ruangan" class="form-control select2" data-tags="true" data-placeholder="-- Pilih atau Ketik Ruangan --" required style="width: 100%;">
                                <option value=""></option>
                                <?php foreach (($ruanganOptions ?? []) as $optRuangan): ?>
                                    <option value="<?= esc($optRuangan); ?>"><?= esc($optRuangan); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted" style="font-size: 0.72rem;">Pilih dari daftar ruangan atau ketik langsung nama ruangan baru</small>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="font-weight-bold small text-dark">Tahun Perolehan</label>
                            <input type="number" id="edit-tahun" name="tahun_perolehan" class="form-control" min="1990" max="<?= date('Y') + 1; ?>" style="border-radius: 6px;">
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="font-weight-bold small text-dark">Peruntukan <span class="text-danger">*</span></label>
                            <div class="d-flex align-items-center mt-2" style="gap: 15px;">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="edit_peruntukan_kantor" name="peruntukan" value="kantor" class="custom-control-input" checked>
                                    <label class="custom-control-label font-weight-normal text-primary" for="edit_peruntukan_kantor">
                                        <i class="fas fa-building mr-1"></i> Kantor
                                    </label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="edit_peruntukan_mobiler" name="peruntukan" value="mobiler" class="custom-control-input">
                                    <label class="custom-control-label font-weight-normal text-warning" for="edit_peruntukan_mobiler">
                                        <i class="fas fa-school mr-1"></i> <strong class="text-dark">Mobiler</strong>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 form-group mb-0">
                            <label class="font-weight-bold small text-dark">Keterangan Tambahan</label>
                            <textarea id="edit-keterangan" name="keterangan" class="form-control" rows="2" style="border-radius: 6px;"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-3" style="border-top: 1px solid #e9eef5;">
                    <button type="button" class="btn btn-secondary px-3" data-dismiss="modal" style="border-radius: 6px;">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm" style="border-radius: 6px;">
                        <i class="fas fa-save mr-1"></i> Perbarui Barang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Hapus Barang -->
<?php if (! empty($can_delete)): ?>
<div class="modal fade" id="modal-delete-inventaris" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.12);">
            <div class="modal-header bg-danger text-white py-3">
                <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Konfirmasi Hapus Barang
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-delete-inventaris" action="" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body py-4 text-center">
                    <i class="fas fa-trash-alt text-danger mb-3" style="font-size: 3rem;"></i>
                    <p class="mb-1 text-dark font-weight-bold">Apakah Anda yakin ingin menghapus data barang ini?</p>
                    <p id="delete-barang-name" class="text-primary font-weight-bold mb-2"></p>
                    <small class="text-muted">Data yang dihapus tidak dapat dipulihkan kembali.</small>
                </div>
                <div class="modal-footer bg-light py-3" style="border-top: 1px solid #e9eef5;">
                    <button type="button" class="btn btn-secondary px-3" data-dismiss="modal" style="border-radius: 6px;">Batal</button>
                    <button type="submit" class="btn btn-danger px-4 shadow-sm" style="border-radius: 6px;">
                        <i class="fas fa-trash-alt mr-1"></i> Ya, Hapus Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Import SIMAN -->
<?php if (! empty($can_import)): ?>
<div class="modal fade" id="modal-import-siman" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 14px; border: none; box-shadow: 0 12px 36px rgba(0,0,0,0.18); overflow: hidden;">
            <div class="modal-header py-3" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff;">
                <h5 class="modal-title font-weight-bold mb-0" style="font-size: 1.05rem;">
                    <i class="fas fa-file-excel text-success mr-2"></i>Import Data Aset BMN SIMAN
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" id="btn-close-import-x" style="opacity: 0.85;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <!-- SECTION 1: Form Input (Initial View) -->
            <form id="form-import-siman" action="<?= site_url('admin/inventaris/barang/import-siman'); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="modal-body py-4" id="import-form-view">
                    <div class="alert alert-info py-2 px-3 small mb-3 border-0 shadow-sm" style="border-radius: 8px; background-color: #f0f9ff; color: #0369a1;">
                        <i class="fas fa-info-circle mr-1 text-info"></i>
                        Upload file ekspor Master Aset dari aplikasi <strong>SIMAN / SAKTI Kemenkeu</strong> (format <code>.xlsx</code> atau <code>.xls</code> seperti <code>daftar-aset-1.xlsx</code>). Sistem secara otomatis mencocokkan <strong>Kode Barang &amp; NUP</strong> (update data yang sudah ada, insert data baru) serta membaca Kode Register BMN.
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold small text-dark">Pilih File Excel SIMAN <span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" name="file_excel" class="custom-file-input" id="file_excel" accept=".xlsx, .xls" required>
                            <label class="custom-file-label font-mono text-truncate" for="file_excel" id="file_excel_label" style="border-radius: 8px;">Pilih file .xlsx / .xls</label>
                        </div>
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-check text-success mr-1"></i> Dilengkapi loading progress real-time, estimasi waktu, dan status tahapan impor.
                        </small>
                    </div>
                </div>
                <div class="modal-footer bg-light py-3" id="import-form-footer" style="border-top: 1px solid #e9eef5;">
                    <button type="button" class="btn btn-secondary px-3" data-dismiss="modal" style="border-radius: 6px;">Batal</button>
                    <button type="submit" class="btn btn-success px-4 shadow-sm font-weight-bold" id="btn-submit-import" style="border-radius: 6px;">
                        <i class="fas fa-upload mr-1"></i> Mulai Import Aset
                    </button>
                </div>
            </form>

            <!-- SECTION 2: Progress Screen (Active during upload & processing) -->
            <div class="modal-body py-4" id="import-progress-view" style="display: none;">
                <div class="text-center">
                    <div id="progress-spinner-wrapper" class="mb-3">
                        <div class="spinner-border text-success" style="width: 3.5rem; height: 3.5rem; border-width: 0.3em;" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                    <div id="progress-success-icon" style="display: none;" class="mb-3">
                        <i class="fas fa-check-circle text-success" style="font-size: 3.5rem;"></i>
                    </div>
                    <div id="progress-error-icon" style="display: none;" class="mb-3">
                        <i class="fas fa-times-circle text-danger" style="font-size: 3.5rem;"></i>
                    </div>

                    <h5 class="font-weight-bold text-dark mb-1" id="progress-main-title">Mengimpor Data Aset SIMAN...</h5>
                    <p class="text-muted small mb-3" id="progress-sub-title">File: <strong id="progress-filename">-</strong> (<span id="progress-filesize">-</span>)</p>

                    <!-- Progress Bar Container -->
                    <div class="px-2 mb-2">
                        <div class="progress" style="height: 22px; border-radius: 12px; background-color: #e2e8f0; overflow: hidden; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);">
                            <div id="import-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success font-weight-bold" role="progressbar" style="width: 0%; font-size: 0.8rem; transition: width 0.3s ease;">0%</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between px-2 text-muted small mb-3">
                        <span id="progress-status-desc" class="font-weight-bold text-info"><i class="fas fa-arrow-up mr-1"></i> Memulai upload file...</span>
                        <span id="progress-timer" class="font-mono"><i class="fas fa-clock mr-1"></i> 00:00</span>
                    </div>

                    <!-- Step Tracker -->
                    <div class="px-2 text-left mb-3">
                        <div class="bg-light p-3 rounded border small shadow-xs" style="font-size: 0.82rem;">
                            <div class="d-flex align-items-center mb-2" id="step-upload">
                                <i class="fas fa-spinner fa-spin mr-2 text-primary" id="step-upload-icon"></i>
                                <span id="step-upload-text" class="font-weight-bold text-dark">1. Mengunggah file Excel ke server</span>
                            </div>
                            <div class="d-flex align-items-center mb-2 text-muted" id="step-parse">
                                <i class="far fa-circle mr-2" id="step-parse-icon"></i>
                                <span id="step-parse-text">2. Membaca lembar data SIMAN BMN &amp; memetakan kolom</span>
                            </div>
                            <div class="d-flex align-items-center mb-2 text-muted" id="step-match">
                                <i class="far fa-circle mr-2" id="step-match-icon"></i>
                                <span id="step-match-text">3. Validasi &amp; pencocokan Kode Barang &amp; NUP</span>
                            </div>
                            <div class="d-flex align-items-center mb-2 text-muted" id="step-save">
                                <i class="far fa-circle mr-2" id="step-save-icon"></i>
                                <span id="step-save-text">4. Menjalankan proses Upsert (Insert baru &amp; Update aset)</span>
                            </div>
                            <div class="d-flex align-items-center text-muted" id="step-finish">
                                <i class="far fa-circle mr-2" id="step-finish-icon"></i>
                                <span id="step-finish-text">5. Finalisasi data &amp; penyegaran tabel</span>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning py-2 px-3 small text-left mb-0 border-0" id="progress-warning-note" style="border-radius: 8px; font-size: 0.78rem;">
                        <i class="fas fa-exclamation-triangle mr-1 text-warning"></i>
                        Mohon <strong>jangan menutup atau me-refresh tab browser</strong> sampai seluruh proses impor dan penyimpanan data selesai.
                    </div>

                    <!-- Result Summary Box (Shown upon completion) -->
                    <div id="progress-result-box" style="display: none;" class="px-2 mt-3 text-left">
                        <div class="card border-0 shadow-sm" style="border-radius: 10px; background: #f0fdf4; border: 1px solid #bbf7d0 !important;">
                            <div class="card-body p-3">
                                <div class="font-weight-bold text-success small mb-2">
                                    <i class="fas fa-check-double mr-1"></i> Impor Berhasil Diproses!
                                </div>
                                <div class="row text-center">
                                    <div class="col-4 border-right">
                                        <div class="text-muted small" style="font-size: 0.75rem;">Total Diproses</div>
                                        <h5 class="font-weight-bold text-dark mb-0" id="res-total">0</h5>
                                    </div>
                                    <div class="col-4 border-right">
                                        <div class="text-muted small" style="font-size: 0.75rem;">Aset Baru</div>
                                        <h5 class="font-weight-bold text-success mb-0" id="res-imported">0</h5>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-muted small" style="font-size: 0.75rem;">Diperbarui</div>
                                        <h5 class="font-weight-bold text-info mb-0" id="res-updated">0</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-success btn-block mt-3 py-2 font-weight-bold shadow-sm" id="btn-import-finish-reload" style="border-radius: 8px;">
                            <i class="fas fa-check mr-1"></i> Selesai &amp; Tampilkan Data
                        </button>
                    </div>

                    <!-- Error Box (Shown if failed) -->
                    <div id="progress-error-box" style="display: none;" class="px-2 mt-3 text-left">
                        <div class="alert alert-danger border-0 shadow-sm py-2 px-3 small mb-3" id="progress-error-msg" style="border-radius: 8px;">
                            Terjadi kesalahan saat mengimpor data.
                        </div>
                        <button type="button" class="btn btn-secondary btn-block py-2 font-weight-bold" id="btn-import-retry" style="border-radius: 8px;">
                            <i class="fas fa-undo mr-1"></i> Kembali &amp; Coba Lagi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Update Peruntukan Massal -->
<?php if (! empty($can_edit)): ?>
<div class="modal fade" id="modal-update-peruntukan-massal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
            <div class="modal-header bg-warning text-dark py-3" style="border-bottom: 1px solid #ffeeba;">
                <h5 class="modal-title font-weight-bold" style="font-size: 1.1rem;">
                    <i class="fas fa-sliders-h mr-2"></i>Update Peruntukan Barang Massal
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('admin/inventaris/barang/update-peruntukan-massal'); ?>" method="post" id="form-mass-update">
                <?= csrf_field(); ?>
                <div class="modal-body py-4">
                    <div class="alert alert-light border py-2 px-3 small mb-3" style="border-radius: 8px; background-color: #fcfbf6;">
                        <i class="fas fa-info-circle text-info mr-1"></i>
                        Gunakan fitur ini untuk mengubah status peruntukan (<strong>Kantor Satker</strong> atau <strong>Mobiler Sekolah</strong>) sekaligus untuk unit barang berdasarkan <strong>Kode Barang</strong> dan <strong>Rentang Nomor Urut Pendaftaran (NUP)</strong>.
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-dark">Pilih Kode Barang / Nama Barang / Merk Tipe <span class="text-danger">*</span></label>
                        <select id="mass-kode-barang-select" class="form-control select2" required style="width: 100%;">
                            <option value="">-- Pilih Barang --</option>
                            <?php foreach (($uniqueKodeBarangList ?? []) as $kb): ?>
                                <?php
                                    $itemText = $kb['kode_barang'] . ' — ' . $kb['nama_barang'];
                                    if (! empty($kb['merk_tipe']) && stripos($kb['nama_barang'], $kb['merk_tipe']) === false) {
                                        $itemText .= ' (' . $kb['merk_tipe'] . ')';
                                    }
                                    $itemText .= ' (' . number_format((int) $kb['total_unit']) . ' unit)';
                                ?>
                                <option value="<?= esc($kb['kode_barang'] . ':::' . $kb['nama_barang'] . ':::' . ($kb['merk_tipe'] ?? '')); ?>"
                                        data-kode="<?= esc($kb['kode_barang']); ?>"
                                        data-nama="<?= esc($kb['nama_barang']); ?>"
                                        data-merk="<?= esc($kb['merk_tipe'] ?? ''); ?>">
                                    <?= esc($itemText); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="kode_barang" id="mass-kode-barang">
                        <input type="hidden" name="nama_barang" id="mass-hidden-nama">
                        <input type="hidden" name="merk_tipe" id="mass-hidden-merk">
                    </div>

                    <!-- Box Detail Info Aset Terpilih (AJAX Loaded) -->
                    <div id="mass-info-box" class="p-3 mb-3 border rounded shadow-none" style="background-color: #f8fafc; border-color: #e2e8f0; display: none;">
                        <div class="row text-center">
                            <div class="col-4 border-right">
                                <small class="text-muted d-block font-weight-bold">Total Unit Terdata</small>
                                <span class="h5 font-weight-bold text-dark mb-0" id="mass-info-total">0</span>
                            </div>
                            <div class="col-4 border-right">
                                <small class="text-muted d-block font-weight-bold">Rentang NUP Terdaftar</small>
                                <span class="h5 font-weight-bold text-primary mb-0" id="mass-info-nup-range">-</span>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block font-weight-bold">Peruntukan Saat Ini</small>
                                <div class="mt-1">
                                    <span class="badge badge-primary px-2" id="mass-info-kantor">0 Kantor</span>
                                    <span class="badge badge-warning text-dark px-2" id="mass-info-mobiler">0 Mobiler</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pilihan Lingkup NUP (Seluruh vs Sebagian) -->
                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-dark">Lingkup Pembaruan NUP <span class="text-danger">*</span></label>
                        <div class="card p-3 mb-1" style="background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px;">
                            <div class="row">
                                <div class="col-md-6 mb-2 mb-md-0">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="mass_lingkup_semua" name="lingkup_nup" value="semua" class="custom-control-input" checked>
                                        <label class="custom-control-label font-weight-bold text-dark" for="mass_lingkup_semua" style="cursor: pointer;">
                                            <i class="fas fa-check-double text-success mr-1"></i> Seluruh NUP (Semua Unit)
                                        </label>
                                        <small class="text-muted d-block" style="font-size: 0.74rem;">Otomatis mengubah seluruh unit yang terdaftar untuk kode ini</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="mass_lingkup_sebagian" name="lingkup_nup" value="sebagian" class="custom-control-input">
                                        <label class="custom-control-label font-weight-bold text-dark" for="mass_lingkup_sebagian" style="cursor: pointer;">
                                            <i class="fas fa-filter text-primary mr-1"></i> Sebagian NUP (Rentang Tertentu)
                                        </label>
                                        <small class="text-muted d-block" style="font-size: 0.74rem;">Tentukan nomor urut NUP awal hingga akhir secara spesifik</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="mass-rentang-wrapper">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-dark">Dari NUP Awal <span class="text-danger" id="req-star-awal" style="display: none;">*</span></label>
                            <input type="number" name="nup_awal" id="mass-nup-awal" class="form-control font-weight-bold" placeholder="Contoh: 1" min="1" readonly style="border-radius: 6px; background-color: #f1f5f9;">
                            <small class="text-muted" id="note-nup-awal" style="font-size: 0.72rem;">Nomor urut pendaftaran awal yang akan diubah</small>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-dark">Hingga NUP Akhir <span class="text-danger" id="req-star-akhir" style="display: none;">*</span></label>
                            <input type="number" name="nup_akhir" id="mass-nup-akhir" class="form-control font-weight-bold" placeholder="Contoh: 50" min="1" readonly style="border-radius: 6px; background-color: #f1f5f9;">
                            <small class="text-muted" id="note-nup-akhir" style="font-size: 0.72rem;">Nomor urut pendaftaran akhir yang akan diubah</small>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold small text-dark">Ubah Peruntukan Menjadi <span class="text-danger">*</span></label>
                        <div class="card p-3" style="background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px;">
                            <div class="row">
                                <div class="col-md-6 mb-2 mb-md-0">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="mass_peruntukan_kantor" name="peruntukan" value="kantor" class="custom-control-input" checked>
                                        <label class="custom-control-label font-weight-bold text-primary" for="mass_peruntukan_kantor">
                                            <i class="fas fa-building mr-1"></i> Kantor / Satker
                                        </label>
                                        <small class="text-muted d-block" style="font-size: 0.75rem;">Aset operasional kantor satker (dapat dialokasikan ke Ruangan / DBR)</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="mass_peruntukan_mobiler" name="peruntukan" value="mobiler" class="custom-control-input">
                                        <label class="custom-control-label font-weight-bold text-dark" for="mass_peruntukan_mobiler">
                                            <i class="fas fa-school text-warning mr-1"></i> Mobiler / Sekolah
                                        </label>
                                        <small class="text-muted d-block" style="font-size: 0.75rem;">Aset bantuan pendidikan yang didistribusikan ke sekolah-sekolah</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-3" style="border-top: 1px solid #e9eef5;">
                    <button type="button" class="btn btn-secondary px-3" data-dismiss="modal" style="border-radius: 6px;">Batal</button>
                    <button type="submit" class="btn btn-warning px-4 shadow-sm font-weight-bold text-dark" style="border-radius: 6px;">
                        <i class="fas fa-check-circle mr-1"></i> Simpan Perubahan Massal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Populate Edit Modal via delegated click
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-edit-inventaris');
        if (!btn) return;
        var id = btn.getAttribute('data-id');
        var form = document.getElementById('form-edit-inventaris');
        if (form) {
            form.action = '<?= site_url('admin/inventaris/barang'); ?>/' + id + '/ubah';
        }
        document.getElementById('edit-kode').value = btn.getAttribute('data-kode') || '';
        document.getElementById('edit-nup').value = btn.getAttribute('data-nup') || '';
        document.getElementById('edit-kode-register').value = btn.getAttribute('data-register') || '';
        document.getElementById('edit-nama').value = btn.getAttribute('data-nama') || '';
        document.getElementById('edit-kategori').value = btn.getAttribute('data-kategori') || '';
        document.getElementById('edit-merk').value = btn.getAttribute('data-merk') || '';
        document.getElementById('edit-jumlah').value = '1';
        document.getElementById('edit-kondisi').value = btn.getAttribute('data-kondisi') || 'baik';
        document.getElementById('edit-tahun').value = btn.getAttribute('data-tahun') || '';
        document.getElementById('edit-keterangan').value = btn.getAttribute('data-keterangan') || '';

        // Handle Peruntukan Radio
        var peruntukanVal = (btn.getAttribute('data-peruntukan') || 'kantor').toLowerCase();
        if (peruntukanVal === 'mobiler') {
            var radioMobiler = document.getElementById('edit_peruntukan_mobiler');
            if (radioMobiler) radioMobiler.checked = true;
        } else {
            var radioKantor = document.getElementById('edit_peruntukan_kantor');
            if (radioKantor) radioKantor.checked = true;
        }

        // Handle Satuan Select2 (Dropdown + Manual Input)
        var satuanVal = (btn.getAttribute('data-satuan') || 'Unit').trim();
        if (typeof $ !== 'undefined') {
            var $editSatuan = $('#edit-satuan');
            if ($editSatuan.length) {
                if (satuanVal !== '') {
                    var optSatuanExists = false;
                    $editSatuan.find('option').each(function() {
                        if ($(this).val().toLowerCase() === satuanVal.toLowerCase()) {
                            optSatuanExists = true;
                            satuanVal = $(this).val();
                            return false;
                        }
                    });
                    if (!optSatuanExists) {
                        var newSatuanOption = new Option(satuanVal, satuanVal, true, true);
                        $editSatuan.append(newSatuanOption);
                    }
                    $editSatuan.val(satuanVal).trigger('change');
                } else {
                    $editSatuan.val('Unit').trigger('change');
                }
            }
        } else {
            var elSatuan = document.getElementById('edit-satuan');
            if (elSatuan) elSatuan.value = satuanVal;
        }

        // Handle Lokasi Ruangan Select2 (Dropdown + Manual Input)
        var lokasiVal = (btn.getAttribute('data-lokasi') || '').trim();
        if (typeof $ !== 'undefined') {
            var $editLokasi = $('#edit-lokasi');
            if ($editLokasi.length) {
                if (lokasiVal !== '') {
                    var optionExists = false;
                    $editLokasi.find('option').each(function() {
                        if ($(this).val() === lokasiVal) {
                            optionExists = true;
                            return false;
                        }
                    });
                    if (!optionExists) {
                        var newOption = new Option(lokasiVal, lokasiVal, true, true);
                        $editLokasi.append(newOption);
                    }
                    $editLokasi.val(lokasiVal).trigger('change');
                } else {
                    $editLokasi.val('').trigger('change');
                }
            }
        } else {
            var el = document.getElementById('edit-lokasi');
            if (el) el.value = lokasiVal;
        }
    });

    if (typeof $ !== 'undefined') {
        $('#modal-tambah-inventaris').on('show.bs.modal', function () {
            $('#tambah-lokasi').val('').trigger('change');
            $('#tambah-satuan').val('Unit').trigger('change');
            var radioKantor = document.getElementById('tambah_peruntukan_kantor');
            if (radioKantor) radioKantor.checked = true;
        });

        // AJAX range fetch for Batch Update Modal (Specific to Kode, Nama, and Merk/Tipe)
        $('#mass-kode-barang-select').on('change', function() {
            var $selected = $(this).find(':selected');
            var kode = $selected.data('kode') || '';
            var nama = $selected.data('nama') || '';
            var merk = $selected.data('merk') || '';

            $('#mass-kode-barang').val(kode);
            $('#mass-hidden-nama').val(nama);
            $('#mass-hidden-merk').val(merk);

            if (!kode) {
                $('#mass-info-box').slideUp();
                $('#mass-nup-awal').val('');
                $('#mass-nup-akhir').val('');
                return;
            }

            $.getJSON('<?= site_url('admin/inventaris/barang/nup-range-by-kode'); ?>', { 
                kode_barang: kode,
                nama_barang: nama,
                merk_tipe: merk
            }, function(res) {
                if (res && (res.success || res.status === 'success') && res.data) {
                    var d = res.data;
                    $('#mass-info-total').text((d.total_unit || 0) + ' Unit');
                    var minNup = d.min_nup !== null ? d.min_nup : 0;
                    var maxNup = d.max_nup !== null ? d.max_nup : 0;
                    $('#mass-info-nup-range').text(minNup + ' s/d ' + maxNup);
                    $('#mass-info-kantor').text((d.total_kantor || 0) + ' Kantor');
                    $('#mass-info-mobiler').text((d.total_mobiler || 0) + ' Mobiler');
                    
                    if (minNup > 0) $('#mass-nup-awal').val(minNup);
                    if (maxNup > 0) $('#mass-nup-akhir').val(maxNup);
                    updateLingkupNupUI();
                    
                    $('#mass-info-box').slideDown();
                }
            });
        });

        function updateLingkupNupUI() {
            var isSemua = $('#mass_lingkup_semua').is(':checked');
            if (isSemua) {
                $('#mass-nup-awal, #mass-nup-akhir').prop('readonly', true).css('background-color', '#f1f5f9');
                $('#req-star-awal, #req-star-akhir').hide();
                $('#note-nup-awal').html('<span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i>Otomatis mencakup NUP terendah</span>');
                $('#note-nup-akhir').html('<span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i>Otomatis mencakup NUP tertinggi</span>');
            } else {
                $('#mass-nup-awal, #mass-nup-akhir').prop('readonly', false).css('background-color', '#ffffff');
                $('#req-star-awal, #req-star-akhir').show();
                $('#note-nup-awal').text('Nomor urut pendaftaran awal yang akan diubah');
                $('#note-nup-akhir').text('Nomor urut pendaftaran akhir yang akan diubah');
            }
        }

        $('input[name="lingkup_nup"]').on('change', function() {
            updateLingkupNupUI();
            if ($('#mass_lingkup_sebagian').is(':checked')) {
                $('#mass-nup-awal').focus();
            }
        });

        $('#modal-update-peruntukan-massal').on('show.bs.modal', function () {
            $('#mass_lingkup_semua').prop('checked', true);
            updateLingkupNupUI();
        });

        $('#form-mass-update').on('submit', function(e) {
            var $selected = $('#mass-kode-barang-select').find(':selected');
            var kode = $selected.data('kode') || '';
            var nama = $selected.data('nama') || '';
            var merk = $selected.data('merk') || '';

            $('#mass-kode-barang').val(kode);
            $('#mass-hidden-nama').val(nama);
            $('#mass-hidden-merk').val(merk);

            if (!kode) {
                e.preventDefault();
                alert('Silakan pilih barang terlebih dahulu.');
                return false;
            }
        });

        $('#modal-update-peruntukan-massal').on('shown.bs.modal', function () {
            if ($.fn.select2) {
                $('#mass-kode-barang-select').select2({
                    dropdownParent: $('#modal-update-peruntukan-massal')
                });
            }
        });
    }

    // Copy to clipboard for Kode Register inside Edit Modal
    var btnCopyEditReg = document.getElementById('btn-copy-edit-reg');
    if (btnCopyEditReg) {
        btnCopyEditReg.addEventListener('click', function(e) {
            e.preventDefault();
            var input = document.getElementById('edit-kode-register');
            var val = input ? input.value.trim() : '';
            if (val && navigator.clipboard) {
                navigator.clipboard.writeText(val).then(function() {
                    if (typeof Swal !== 'undefined') {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });
                        Toast.fire({
                            icon: 'success',
                            title: 'Kode Register disalin ke clipboard!'
                        });
                    }
                });
            }
        });
    }

    // Populate Delete Modal via delegated click
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-delete-inventaris');
        if (!btn) return;
        var id = btn.getAttribute('data-id');
        var nama = btn.getAttribute('data-nama');
        var form = document.getElementById('form-delete-inventaris');
        if (form) {
            form.action = '<?= site_url('admin/inventaris/barang'); ?>/' + id + '/hapus';
        }
        var nameEl = document.getElementById('delete-barang-name');
        if (nameEl) {
            nameEl.textContent = '"' + nama + '"';
        }
    });

    // ========================================================
    // Real-Time Loading Progress for Import SIMAN Excel
    // ========================================================
    var formImport = document.getElementById('form-import-siman');
    var fileInputImport = document.getElementById('file_excel');
    var fileLabelImport = document.getElementById('file_excel_label');

    if (fileInputImport && fileLabelImport) {
        fileInputImport.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                fileLabelImport.textContent = this.files[0].name;
            } else {
                fileLabelImport.textContent = 'Pilih file .xlsx / .xls';
            }
        });
    }

    if (formImport) {
        var timerInterval = null;
        var processingTicker = null;
        var isImportRunning = false;
        var isImportDone = false;

        function formatBytes(bytes, decimals) {
            if (!bytes || bytes === 0) return '0 Bytes';
            var k = 1024;
            var dm = decimals < 0 ? 0 : (decimals || 1);
            var sizes = ['Bytes', 'KB', 'MB', 'GB'];
            var i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }

        function setProgressBar(percent, text, customClass) {
            // If import has already completed successfully, prevent any intermediate ticker from downgrading it
            if (isImportDone && percent < 100) {
                return;
            }
            var bar = document.getElementById('import-progress-bar');
            if (!bar) return;
            bar.style.width = percent + '%';
            bar.textContent = text !== undefined ? text : (percent + '%');
            if (customClass) {
                bar.className = 'progress-bar progress-bar-striped progress-bar-animated font-weight-bold ' + customClass;
            }
        }

        function setStepActive(stepId, text) {
            var el = document.getElementById(stepId);
            if (!el) return;
            el.className = 'd-flex align-items-center mb-2 font-weight-bold text-primary';
            var icon = document.getElementById(stepId + '-icon');
            if (icon) icon.className = 'fas fa-spinner fa-spin mr-2 text-primary';
            var txt = document.getElementById(stepId + '-text');
            if (txt && text) txt.textContent = text;
        }

        function markStepDone(stepId) {
            var el = document.getElementById(stepId);
            if (!el) return;
            el.className = 'd-flex align-items-center mb-2 text-success font-weight-bold';
            var icon = document.getElementById(stepId + '-icon');
            if (icon) icon.className = 'fas fa-check-circle mr-2 text-success';
        }

        function resetImportModal() {
            isImportRunning = false;
            isImportDone = false;
            if (timerInterval) { clearInterval(timerInterval); timerInterval = null; }
            if (processingTicker) { clearInterval(processingTicker); processingTicker = null; }

            var btnSubmit = document.getElementById('btn-submit-import');
            if (btnSubmit) btnSubmit.disabled = false;

            document.getElementById('import-form-view').style.display = 'block';
            document.getElementById('import-form-footer').style.display = 'flex';
            document.getElementById('import-progress-view').style.display = 'none';
            document.getElementById('progress-spinner-wrapper').style.display = 'block';
            document.getElementById('progress-success-icon').style.display = 'none';
            document.getElementById('progress-error-icon').style.display = 'none';
            document.getElementById('progress-result-box').style.display = 'none';
            document.getElementById('progress-error-box').style.display = 'none';
            document.getElementById('progress-warning-note').style.display = 'block';
            document.getElementById('btn-close-import-x').style.display = 'block';

            var steps = ['step-upload', 'step-parse', 'step-match', 'step-save', 'step-finish'];
            steps.forEach(function(s) {
                var el = document.getElementById(s);
                if (el) el.className = 'd-flex align-items-center mb-2 text-muted';
                var icon = document.getElementById(s + '-icon');
                if (icon) icon.className = 'far fa-circle mr-2';
            });

            if (fileInputImport) fileInputImport.value = '';
            if (fileLabelImport) fileLabelImport.textContent = 'Pilih file .xlsx / .xls';
        }

        function showImportError(msg) {
            isImportRunning = false;
            isImportDone = false;
            if (timerInterval) { clearInterval(timerInterval); timerInterval = null; }
            if (processingTicker) { clearInterval(processingTicker); processingTicker = null; }

            var btnSubmit = document.getElementById('btn-submit-import');
            if (btnSubmit) btnSubmit.disabled = false;

            setProgressBar(100, 'Error', 'bg-danger');
            document.getElementById('progress-spinner-wrapper').style.display = 'none';
            document.getElementById('progress-error-icon').style.display = 'block';
            document.getElementById('progress-main-title').textContent = 'Proses Impor Gagal';
            document.getElementById('progress-status-desc').innerHTML = '<i class="fas fa-exclamation-circle mr-1 text-danger"></i> Terjadi kesalahan';
            document.getElementById('progress-warning-note').style.display = 'none';
            document.getElementById('progress-error-msg').textContent = msg || 'Gagal memproses file Excel SIMAN.';
            document.getElementById('progress-error-box').style.display = 'block';
            document.getElementById('btn-close-import-x').style.display = 'block';
        }

        var btnRetry = document.getElementById('btn-import-retry');
        if (btnRetry) {
            btnRetry.addEventListener('click', function() {
                resetImportModal();
            });
        }

        // Prevent accidental closing of window during active import
        window.addEventListener('beforeunload', function(e) {
            if (isImportRunning) {
                e.preventDefault();
                e.returnValue = 'Proses impor data sedang berlangsung. Apakah Anda yakin ingin meninggalkan halaman?';
                return e.returnValue;
            }
        });

        formImport.addEventListener('submit', function(e) {
            e.preventDefault();

            if (isImportRunning) {
                return;
            }

            if (!fileInputImport.files || fileInputImport.files.length === 0) {
                alert('Silakan pilih file Excel SIMAN terlebih dahulu.');
                return;
            }

            var file = fileInputImport.files[0];
            var ext = file.name.split('.').pop().toLowerCase();
            if (ext !== 'xlsx' && ext !== 'xls') {
                alert('Format file harus berupa .xlsx atau .xls');
                return;
            }

            isImportRunning = true;
            isImportDone = false;

            var btnSubmit = document.getElementById('btn-submit-import');
            if (btnSubmit) btnSubmit.disabled = true;

            // Setup UI info
            document.getElementById('progress-filename').textContent = file.name;
            document.getElementById('progress-filesize').textContent = formatBytes(file.size);

            // Switch to progress screen
            document.getElementById('import-form-view').style.display = 'none';
            document.getElementById('import-form-footer').style.display = 'none';
            document.getElementById('import-progress-view').style.display = 'block';
            document.getElementById('btn-close-import-x').style.display = 'none';

            setProgressBar(0, '0%', 'bg-success');
            setStepActive('step-upload', '1. Mengunggah file Excel ke server...');

            // Start timer
            var secondsElapsed = 0;
            var timerEl = document.getElementById('progress-timer');
            timerInterval = setInterval(function() {
                secondsElapsed++;
                var m = Math.floor(secondsElapsed / 60);
                var s = secondsElapsed % 60;
                if (timerEl) {
                    timerEl.innerHTML = '<i class="fas fa-clock mr-1"></i> ' + (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
                }
            }, 1000);

            var formData = new FormData(formImport);
            var xhr = new XMLHttpRequest();
            xhr.open('POST', formImport.action, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            // Track upload bytes
            xhr.upload.onprogress = function(pe) {
                if (pe.lengthComputable) {
                    var percent = Math.round((pe.loaded / pe.total) * 35); // 0-35%
                    setProgressBar(percent, percent + '%');
                    document.getElementById('progress-status-desc').innerHTML = '<i class="fas fa-arrow-up mr-1 text-info"></i> Mengunggah (' + formatBytes(pe.loaded) + ' / ' + formatBytes(pe.total) + ')...';
                }
            };

            // Upload complete, server processing begins
            xhr.upload.onload = function() {
                if (!isImportRunning || isImportDone) return;

                markStepDone('step-upload');
                setStepActive('step-parse', '2. Membaca lembar data SIMAN BMN & memetakan kolom...');
                setProgressBar(45, '45%');
                document.getElementById('progress-status-desc').innerHTML = '<i class="fas fa-cog fa-spin mr-1 text-primary"></i> Membaca file di server...';

                var currentPct = 45;
                var stepParseDone = false;
                var stepMatchDone = false;

                if (processingTicker) {
                    clearInterval(processingTicker);
                    processingTicker = null;
                }

                processingTicker = setInterval(function() {
                    if (!isImportRunning || isImportDone) {
                        clearInterval(processingTicker);
                        processingTicker = null;
                        return;
                    }

                    currentPct = Math.min(currentPct + 4, 90);
                    setProgressBar(currentPct, currentPct + '%');

                    if (currentPct >= 58 && !stepParseDone) {
                        markStepDone('step-parse');
                        setStepActive('step-match', '3. Validasi & pencocokan Kode Barang & NUP di database...');
                        document.getElementById('progress-status-desc').innerHTML = '<i class="fas fa-database fa-spin mr-1 text-info"></i> Mencocokkan data aset di database...';
                        stepParseDone = true;
                    } else if (currentPct >= 74 && !stepMatchDone) {
                        markStepDone('step-match');
                        setStepActive('step-save', '4. Menjalankan proses Upsert (Insert baru & Update aset)...');
                        document.getElementById('progress-status-desc').innerHTML = '<i class="fas fa-sync fa-spin mr-1 text-success"></i> Menyimpan aset ke database...';
                        stepMatchDone = true;
                    }
                }, 1000);
            };

            // Response received
            xhr.onload = function() {
                isImportRunning = false;
                if (timerInterval) { clearInterval(timerInterval); timerInterval = null; }
                if (processingTicker) { clearInterval(processingTicker); processingTicker = null; }

                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        var res = JSON.parse(xhr.responseText);
                        if (res.status === 'success') {
                            isImportDone = true;

                            // Force progress bar to 100%
                            setProgressBar(100, '100%', 'bg-success');
                            var bar = document.getElementById('import-progress-bar');
                            if (bar) {
                                bar.style.width = '100%';
                                bar.textContent = '100%';
                                bar.className = 'progress-bar progress-bar-striped bg-success font-weight-bold';
                            }

                            markStepDone('step-upload');
                            markStepDone('step-parse');
                            markStepDone('step-match');
                            markStepDone('step-save');
                            markStepDone('step-finish');

                            document.getElementById('progress-spinner-wrapper').style.display = 'none';
                            document.getElementById('progress-success-icon').style.display = 'block';
                            document.getElementById('progress-main-title').textContent = 'Impor Data Berhasil!';
                            document.getElementById('progress-status-desc').innerHTML = '<i class="fas fa-check-circle mr-1 text-success"></i> ' + (res.message || 'Selesai.');
                            document.getElementById('progress-warning-note').style.display = 'none';

                            document.getElementById('res-total').textContent = (res.total_proses || 0).toLocaleString();
                            document.getElementById('res-imported').textContent = (res.imported_count || 0).toLocaleString();
                            document.getElementById('res-updated').textContent = (res.updated_count || 0).toLocaleString();
                            document.getElementById('progress-result-box').style.display = 'block';
                            document.getElementById('btn-close-import-x').style.display = 'block';

                            var btnFinish = document.getElementById('btn-import-finish-reload');
                            if (btnFinish) {
                                btnFinish.addEventListener('click', function() {
                                    window.location.reload();
                                });
                            }
                            return;
                        } else {
                            showImportError(res.message || 'Gagal memproses file Excel.');
                        }
                    } catch (err) {
                        showImportError('Respons server tidak valid.');
                    }
                } else {
                    var errMsg = 'Gagal menghubungi server (HTTP ' + xhr.status + ').';
                    try {
                        var errObj = JSON.parse(xhr.responseText);
                        if (errObj.message) errMsg = errObj.message;
                    } catch (e) {}
                    showImportError(errMsg);
                }
            };

            xhr.onerror = function() {
                showImportError('Koneksi jaringan terputus saat memproses impor.');
            };

            xhr.send(formData);
        });
    }
});
</script>
<?= $this->endSection(); ?>
