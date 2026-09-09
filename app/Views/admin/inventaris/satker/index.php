<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">

    <!-- Summary Widgets -->
    <div class="row mb-3">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #007bff;">
                <span class="info-box-icon bg-primary elevation-1" style="border-radius: 8px;"><i class="fas fa-boxes-stacked"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Total Inventaris</span>
                    <span class="info-box-number text-dark" style="font-size: 1.35rem;"><?= number_format((int) ($summary['total'] ?? 0)); ?></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #28a745;">
                <span class="info-box-icon bg-success elevation-1" style="border-radius: 8px;"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Kondisi Baik</span>
                    <span class="info-box-number text-success" style="font-size: 1.35rem;"><?= number_format((int) ($summary['baik'] ?? 0)); ?></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #ffc107;">
                <span class="info-box-icon bg-warning elevation-1" style="border-radius: 8px;"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Rusak Ringan</span>
                    <span class="info-box-number text-warning" style="font-size: 1.35rem;"><?= number_format((int) ($summary['rusak_ringan'] ?? 0)); ?></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #dc3545;">
                <span class="info-box-icon bg-danger elevation-1" style="border-radius: 8px;"><i class="fas fa-times-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Rusak Berat</span>
                    <span class="info-box-number text-danger" style="font-size: 1.35rem;"><?= number_format((int) ($summary['rusak_berat'] ?? 0)); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm mb-3" style="border-radius: 10px; border: 1px solid #e9eef5;">
        <div class="card-body py-3">
            <form method="get" action="<?= site_url('admin/inventaris/satker'); ?>">
                <div class="row align-items-end">
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-tag mr-1"></i> Kategori</label>
                        <select name="kategori" class="form-control form-control-sm" style="border-radius: 6px;">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($kategoriList as $kat): ?>
                                <option value="<?= esc($kat); ?>" <?= ($filterKategori === $kat) ? 'selected' : ''; ?>><?= esc($kat); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-heartbeat mr-1"></i> Kondisi</label>
                        <select name="kondisi" class="form-control form-control-sm" style="border-radius: 6px;">
                            <option value="">Semua Kondisi</option>
                            <option value="baik" <?= ($filterKondisi === 'baik') ? 'selected' : ''; ?>>Baik</option>
                            <option value="rusak_ringan" <?= ($filterKondisi === 'rusak_ringan') ? 'selected' : ''; ?>>Rusak Ringan</option>
                            <option value="rusak_berat" <?= ($filterKondisi === 'rusak_berat') ? 'selected' : ''; ?>>Rusak Berat</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-map-marker-alt mr-1"></i> Lokasi Ruangan</label>
                        <select name="lokasi" class="form-control form-control-sm" style="border-radius: 6px;">
                            <option value="">Semua Lokasi</option>
                            <?php foreach ($lokasiList as $lok): ?>
                                <option value="<?= esc($lok); ?>" <?= ($filterLokasi === $lok) ? 'selected' : ''; ?>><?= esc($lok); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 d-flex" style="gap: 6px;">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill shadow-sm" style="border-radius: 6px;">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                        <a href="<?= site_url('admin/inventaris/satker'); ?>" class="btn btn-outline-secondary btn-sm shadow-sm" style="border-radius: 6px;" title="Reset Filter">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Card -->
    <div class="card shadow-sm" style="border: 1px solid #e9eef5; border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white py-3" style="border-bottom: 1px solid #e9eef5;">
            <div class="d-flex flex-wrap justify-content-between align-items-center w-100">
                <h3 class="card-title mb-0 font-weight-bold text-dark" style="font-size: 1.15rem; line-height: 1.8;">
                    <i class="fas fa-boxes text-primary mr-2"></i>Daftar Barang Inventaris
                </h3>
                <div class="card-tools d-flex align-items-center m-0" style="gap: 8px;">
                    <a href="<?= site_url('admin/inventaris/dbr'); ?>" class="btn btn-outline-info btn-sm px-3 shadow-sm" style="border-radius: 6px;">
                        <i class="fas fa-door-open mr-1"></i> Daftar Ruangan
                    </a>
                    <?php if (! empty($can_import)): ?>
                        <button type="button" class="btn btn-outline-success btn-sm px-3 shadow-sm" data-toggle="modal" data-target="#modal-import-siman" style="border-radius: 6px;">
                            <i class="fas fa-file-import mr-1"></i> Import SIMAN
                        </button>
                    <?php endif; ?>
                    <?php if (! empty($can_export)): ?>
                        <a href="<?= site_url('admin/inventaris/satker/export?' . http_build_query(['kategori' => $filterKategori, 'kondisi' => $filterKondisi, 'lokasi' => $filterLokasi])); ?>" class="btn btn-success btn-sm px-3 shadow-sm" style="border-radius: 6px;">
                            <i class="fas fa-file-excel mr-1"></i> Export Excel
                        </a>
                    <?php endif; ?>
                    <?php if (! empty($can_add)): ?>
                        <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm" data-toggle="modal" data-target="#modal-tambah-inventaris" style="border-radius: 6px;">
                            <i class="fas fa-plus mr-1"></i> Tambah Barang
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-striped w-100 js-datatable" data-order='[[1, "asc"], [2, "asc"]]' style="border-radius: 8px;">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 45px;" class="text-center align-middle" data-orderable="false">#</th>
                            <th class="align-middle" style="width: 125px;">Kode Barang</th>
                            <th class="text-center align-middle" style="width: 75px;">NUP</th>
                            <th class="align-middle">Nama Barang</th>
                            <th class="align-middle">Kategori</th>
                            <th class="align-middle">Merk / Tipe</th>
                            <th class="text-center align-middle" style="width: 90px;">Jumlah</th>
                            <th class="text-center align-middle" style="width: 110px;">Kondisi</th>
                            <th class="align-middle">Lokasi Ruangan</th>
                            <th class="text-center align-middle" style="width: 80px;">Tahun</th>
                            <?php if (! empty($can_edit) || ! empty($can_delete)): ?>
                                <th style="width: 100px;" class="text-center align-middle" data-orderable="false">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach (($items ?? []) as $item): ?>
                            <tr>
                                <td class="text-center align-middle"><?= $no++; ?></td>
                                <td class="align-middle">
                                    <span class="font-weight-bold text-dark" style="font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace; font-size: 0.88rem;"><?= esc($item['kode_barang']); ?></span>
                                </td>
                                <td class="text-center align-middle" data-order="<?= (int) ($item['nup'] ?? 0); ?>">
                                    <?php if (! empty($item['nup'])): ?>
                                        <span class="badge badge-light border font-weight-bold px-2 py-1" style="font-size: 0.85rem; font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace; background-color: #f1f5f9; color: #0f172a;">
                                            <?= esc((string) $item['nup']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="align-middle">
                                    <span class="font-weight-bold text-primary"><?= esc($item['nama_barang']); ?></span>
                                    <?php if (! empty($item['keterangan'])): ?>
                                        <small class="text-muted d-block" style="font-size: 0.8rem;"><?= esc($item['keterangan']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="align-middle">
                                    <span class="badge badge-info px-2 py-1"><?= esc($item['kategori']); ?></span>
                                </td>
                                <td class="align-middle"><?= esc($item['merk_tipe'] ?: '-'); ?></td>
                                <td class="text-center align-middle font-weight-bold">
                                    <?= number_format((int) ($item['jumlah'] ?? 1)); ?> <small class="text-muted"><?= esc($item['satuan'] ?? 'Unit'); ?></small>
                                </td>
                                <td class="text-center align-middle">
                                    <?php if ($item['kondisi'] === 'baik'): ?>
                                        <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Baik</span>
                                    <?php elseif ($item['kondisi'] === 'rusak_ringan'): ?>
                                        <span class="badge badge-warning px-2 py-1"><i class="fas fa-exclamation-triangle mr-1"></i> Rusak Ringan</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger px-2 py-1"><i class="fas fa-times-circle mr-1"></i> Rusak Berat</span>
                                    <?php endif; ?>
                                </td>
                                <td class="align-middle">
                                    <i class="fas fa-door-open text-muted mr-1"></i> <?= esc($item['lokasi_ruangan']); ?>
                                </td>
                                <td class="text-center align-middle"><?= esc((string) ($item['tahun_perolehan'] ?: '-')); ?></td>
                                <?php if (! empty($can_edit) || ! empty($can_delete)): ?>
                                    <td class="text-center align-middle" style="white-space: nowrap;">
                                        <div class="btn-group" role="group" style="gap: 4px;">
                                            <?php if (! empty($can_edit)): ?>
                                                <button
                                                    type="button"
                                                    class="btn btn-outline-primary btn-xs px-2 py-1 btn-edit-inventaris"
                                                    data-toggle="modal"
                                                    data-target="#modal-edit-inventaris"
                                                    data-id="<?= esc((string) $item['id'], 'attr'); ?>"
                                                    data-kode="<?= esc((string) $item['kode_barang'], 'attr'); ?>"
                                                    data-nup="<?= esc((string) ($item['nup'] ?? ''), 'attr'); ?>"
                                                    data-register="<?= esc((string) ($item['kode_register'] ?? ''), 'attr'); ?>"
                                                    data-nama="<?= esc((string) $item['nama_barang'], 'attr'); ?>"
                                                    data-kategori="<?= esc((string) $item['kategori'], 'attr'); ?>"
                                                    data-merk="<?= esc((string) ($item['merk_tipe'] ?? ''), 'attr'); ?>"
                                                    data-jumlah="<?= esc((string) ($item['jumlah'] ?? 1), 'attr'); ?>"
                                                    data-satuan="<?= esc((string) ($item['satuan'] ?? 'Unit'), 'attr'); ?>"
                                                    data-kondisi="<?= esc((string) $item['kondisi'], 'attr'); ?>"
                                                    data-lokasi="<?= esc((string) $item['lokasi_ruangan'], 'attr'); ?>"
                                                    data-tahun="<?= esc((string) ($item['tahun_perolehan'] ?? ''), 'attr'); ?>"
                                                    data-keterangan="<?= esc((string) ($item['keterangan'] ?? ''), 'attr'); ?>"
                                                    style="border-radius: 4px;"
                                                    title="Edit Barang"
                                                >
                                                    <i class="fas fa-pen"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if (! empty($can_delete)): ?>
                                                <button
                                                    type="button"
                                                    class="btn btn-outline-danger btn-xs px-2 py-1 btn-delete-inventaris"
                                                    data-toggle="modal"
                                                    data-target="#modal-delete-inventaris"
                                                    data-id="<?= esc((string) $item['id'], 'attr'); ?>"
                                                    data-nama="<?= esc((string) $item['nama_barang'], 'attr'); ?>"
                                                    style="border-radius: 4px;"
                                                    title="Hapus Barang"
                                                >
                                                    <i class="fas fa-trash-alt"></i>
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
                            <label class="font-weight-bold small text-dark">Jumlah <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah" class="form-control" value="1" min="1" required style="border-radius: 6px;">
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
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-dark">Tahun Perolehan</label>
                            <input type="number" name="tahun_perolehan" class="form-control" placeholder="Contoh: <?= date('Y'); ?>" min="1990" max="<?= date('Y') + 1; ?>" style="border-radius: 6px;">
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
                            <label class="font-weight-bold small text-dark">Jumlah <span class="text-danger">*</span></label>
                            <input type="number" id="edit-jumlah" name="jumlah" class="form-control" min="1" required style="border-radius: 6px;">
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
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-dark">Tahun Perolehan</label>
                            <input type="number" id="edit-tahun" name="tahun_perolehan" class="form-control" min="1990" max="<?= date('Y') + 1; ?>" style="border-radius: 6px;">
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
<div class="modal fade" id="modal-import-siman" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
            <div class="modal-header bg-light py-3" style="border-bottom: 1px solid #e9eef5;">
                <h5 class="modal-title font-weight-bold text-dark" style="font-size: 1.1rem;">
                    <i class="fas fa-file-excel text-success mr-2"></i>Import Data Aset BMN SIMAN
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('admin/inventaris/satker/import-siman'); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="modal-body py-4">
                    <div class="alert alert-info py-2 px-3 small mb-3" style="border-radius: 6px;">
                        <i class="fas fa-info-circle mr-1"></i>
                        Upload file ekspor Master Aset dari aplikasi <strong>SIMAN / SAKTI Kemenkeu</strong> (format <code>.xlsx</code> atau <code>.xls</code> seperti <code>daftar-aset-1.xlsx</code>). Sistem akan membaca Kode Barang, NUP, Nama Barang, Merk/Tipe, Kondisi, Nilai Perolehan, dan No PSP.
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold small text-dark">Pilih File Excel SIMAN <span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" name="file_excel" class="custom-file-input" id="file_excel" accept=".xlsx, .xls" required onchange="this.nextElementSibling.innerText = this.files[0] ? this.files[0].name : 'Pilih file .xlsx / .xls';">
                            <label class="custom-file-label" for="file_excel">Pilih file .xlsx / .xls</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-3" style="border-top: 1px solid #e9eef5;">
                    <button type="button" class="btn btn-secondary px-3" data-dismiss="modal" style="border-radius: 6px;">Batal</button>
                    <button type="submit" class="btn btn-success px-4 shadow-sm" style="border-radius: 6px;">
                        <i class="fas fa-upload mr-1"></i> Mulai Import Aset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Populate Edit Modal
    document.querySelectorAll('.btn-edit-inventaris').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            var form = document.getElementById('form-edit-inventaris');
            if (form) {
                form.action = '<?= site_url('admin/inventaris/satker'); ?>/' + id + '/ubah';
            }
            document.getElementById('edit-kode').value = this.getAttribute('data-kode') || '';
            document.getElementById('edit-nup').value = this.getAttribute('data-nup') || '';
            document.getElementById('edit-kode-register').value = this.getAttribute('data-register') || '';
            document.getElementById('edit-nama').value = this.getAttribute('data-nama') || '';
            document.getElementById('edit-kategori').value = this.getAttribute('data-kategori') || '';
            document.getElementById('edit-merk').value = this.getAttribute('data-merk') || '';
            document.getElementById('edit-jumlah').value = this.getAttribute('data-jumlah') || '1';
            document.getElementById('edit-kondisi').value = this.getAttribute('data-kondisi') || 'baik';
            document.getElementById('edit-tahun').value = this.getAttribute('data-tahun') || '';
            document.getElementById('edit-keterangan').value = this.getAttribute('data-keterangan') || '';

            // Handle Satuan Select2 (Dropdown + Manual Input)
            var satuanVal = (this.getAttribute('data-satuan') || 'Unit').trim();
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
            var lokasiVal = (this.getAttribute('data-lokasi') || '').trim();
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
    });

    if (typeof $ !== 'undefined') {
        $('#modal-tambah-inventaris').on('show.bs.modal', function () {
            $('#tambah-lokasi').val('').trigger('change');
            $('#tambah-satuan').val('Unit').trigger('change');
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

    // Populate Delete Modal
    document.querySelectorAll('.btn-delete-inventaris').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            var nama = this.getAttribute('data-nama');
            var form = document.getElementById('form-delete-inventaris');
            if (form) {
                form.action = '<?= site_url('admin/inventaris/satker'); ?>/' + id + '/hapus';
            }
            var nameEl = document.getElementById('delete-barang-name');
            if (nameEl) {
                nameEl.textContent = '"' + nama + '"';
            }
        });
    });
});
</script>
<?= $this->endSection(); ?>
