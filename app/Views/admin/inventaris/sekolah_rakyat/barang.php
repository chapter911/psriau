<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">

    <!-- KPI Widgets -->
    <div class="row mb-3">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #17a2b8;">
                <span class="info-box-icon bg-info elevation-1" style="border-radius: 8px;"><i class="fas fa-file-invoice-dollar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Total Nilai Kontrak</span>
                    <span class="info-box-number text-dark" style="font-size: 1.15rem;">Rp <?= number_format($stats['total_nilai'] ?? 0, 0, ',', '.'); ?></span>
                    <small class="text-muted">Addendum V (Termasuk PPN)</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #007bff;">
                <span class="info-box-icon bg-primary elevation-1" style="border-radius: 8px;"><i class="fas fa-boxes"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Total Item BMN</span>
                    <span class="info-box-number text-dark" style="font-size: 1.35rem;"><?= number_format($stats['total_items'] ?? 0); ?> <span style="font-size: 0.85rem; font-weight: normal;">Rincian</span></span>
                    <small class="text-muted"><?= $stats['count_peralatan']; ?> Mesin | <?= $stats['count_gedung']; ?> Gedung | <?= $stats['count_jaringan']; ?> Jaringan</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #28a745;">
                <span class="info-box-icon bg-success elevation-1" style="border-radius: 8px;"><i class="fas fa-camera"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Dokumentasi Foto Fisik</span>
                    <span class="info-box-number text-success" style="font-size: 1.35rem;"><?= $stats['total_sudah_foto']; ?> / <?= $stats['total_items']; ?> <small>(<?= $stats['persen_foto']; ?>%)</small></span>
                    <small class="text-muted"><?= $stats['total_foto_file']; ?> foto tersimpan</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #ffc107;">
                <span class="info-box-icon bg-warning elevation-1 text-white" style="border-radius: 8px;"><i class="fas fa-map-marked-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Kawasan & Gedung</span>
                    <span class="info-box-number text-dark" style="font-size: 1.35rem;"><?= count($lokasiList); ?> <span style="font-size: 0.85rem; font-weight: normal;">Zona/Gedung</span></span>
                    <small class="text-muted">Komplek Sport Center Kuansing</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm mb-3" style="border-radius: 10px; border: 1px solid #e9eef5;">
        <div class="card-body py-3">
            <form method="get" action="<?= site_url('admin/inventaris/sekolah-rakyat/barang'); ?>">
                <div class="row align-items-end">
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-layer-group mr-1"></i> Kelompok BMN</label>
                        <select name="kelompok" class="form-control form-control-sm select2">
                            <option value="">-- Semua Kelompok --</option>
                            <?php foreach ($kelompokList as $k): ?>
                                <option value="<?= esc($k); ?>" <?= ($filterKelompok === $k) ? 'selected' : ''; ?>><?= esc($k); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-building mr-1"></i> Lokasi Gedung / Zona</label>
                        <select name="lokasi" class="form-control form-control-sm select2">
                            <option value="">-- Semua Lokasi --</option>
                            <?php foreach ($lokasiList as $lok): ?>
                                <option value="<?= esc($lok); ?>" <?= ($filterLokasi === $lok) ? 'selected' : ''; ?>><?= esc($lok); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-camera mr-1"></i> Status Foto</label>
                        <select name="status_foto" class="form-control form-control-sm">
                            <option value="">Semua Status</option>
                            <option value="sudah" <?= ($filterStatusFoto === 'sudah') ? 'selected' : ''; ?>>Sudah Difoto</option>
                            <option value="belum" <?= ($filterStatusFoto === 'belum') ? 'selected' : ''; ?>>Belum Difoto</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-search mr-1"></i> Kata Kunci</label>
                        <input type="text" name="keyword" class="form-control form-control-sm" placeholder="Kode / Nama / Spek..." value="<?= esc($keyword ?? ''); ?>">
                    </div>
                    <div class="col-md-2 col-sm-12 d-flex" style="gap: 6px;">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill shadow-sm">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                        <a href="<?= site_url('admin/inventaris/sekolah-rakyat/barang'); ?>" class="btn btn-outline-secondary btn-sm shadow-sm" title="Reset Filter">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if (! empty($filterLokasi) && $filterLokasi !== '*'): ?>
        <div class="alert alert-info py-2 px-3 mb-3 d-flex justify-content-between align-items-center shadow-sm" style="border-radius: 8px;">
            <div>
                <i class="fas fa-building text-primary mr-1"></i>
                Filter Lokasi Aktif: <strong class="text-dark"><?= esc($filterLokasi); ?></strong> (<?= count($items); ?> Item)
            </div>
            <a href="<?= site_url('admin/inventaris/sekolah-rakyat/barang'); ?>" class="btn btn-xs btn-light font-weight-bold shadow-sm border">
                <i class="fas fa-globe mr-1"></i> Tampilkan Seluruh Lokasi
            </a>
        </div>
    <?php endif; ?>

    <!-- Main Data Table Card -->
    <div class="card shadow-sm" style="border: 1px solid #e9eef5; border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white py-3" style="border-bottom: 1px solid #e9eef5;">
            <div class="d-flex flex-wrap justify-content-between align-items-center w-100">
                <div>
                    <h3 class="card-title mb-0 font-weight-bold text-dark" style="font-size: 1.15rem; line-height: 1.8;">
                        <i class="fas fa-university text-primary mr-2"></i>Kertas Kerja Rincian BMN Sekolah Rakyat
                    </h3>
                    <small class="text-muted d-block mt-1">Pembangunan Sekolah Rakyat Provinsi Riau (Komplek Sport Center Kuansing - Kontrak Addendum V)</small>
                </div>
                <div class="card-tools d-flex align-items-center mt-2 mt-md-0" style="gap: 8px;">
                    <a href="<?= site_url('admin/inventaris/sekolah-rakyat/foto'); ?>" class="btn btn-sm btn-outline-success shadow-sm">
                        <i class="fas fa-camera mr-1"></i> Mode Foto Lapangan
                    </a>
                    <a href="<?= site_url('admin/inventaris/sekolah-rakyat/rekapitulasi'); ?>" class="btn btn-sm btn-outline-info shadow-sm">
                        <i class="fas fa-chart-pie mr-1"></i> Rekapitulasi & Nilai
                    </a>
                    <?php if ($canExport): ?>
                        <a href="<?= site_url('admin/inventaris/sekolah-rakyat/barang/export-excel'); ?>" class="btn btn-sm btn-success shadow-sm">
                            <i class="fas fa-file-excel mr-1"></i> Unduh Excel
                        </a>
                    <?php endif; ?>
                    <?php if ($canAdd): ?>
                        <button type="button" class="btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#modalTambahBarang">
                            <i class="fas fa-plus mr-1"></i> Tambah Item
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card-body p-3">
            <div class="table-responsive">
                <table id="tbl_bmn_sr" class="table table-bordered table-striped table-hover align-middle w-100" style="font-size: 0.88rem;">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width: 40px;" class="text-center align-middle">No</th>
                            <th style="width: 140px;" class="align-middle">Kode BMN</th>
                            <th class="align-middle">Jenis BMN & Spesifikasi Material Teknis</th>
                            <th style="width: 90px;" class="text-center align-middle">Volume</th>
                            <th style="width: 130px;" class="text-right align-middle">Harga Satuan</th>
                            <th style="width: 140px;" class="text-right align-middle">Total Nilai</th>
                            <th style="width: 140px;" class="align-middle">Lokasi Gedung / Zona</th>
                            <th style="width: 80px;" class="text-center align-middle">Kondisi</th>
                            <th style="width: 100px;" class="text-center align-middle">Foto Fisik</th>
                            <th style="width: 90px;" class="text-center align-middle">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $idx => $it): ?>
                            <tr>
                                <td class="text-center align-middle font-weight-bold text-muted"><?= $it['no_urut']; ?></td>
                                <td class="align-middle font-weight-bold text-nowrap">
                                    <span class="badge badge-light border text-dark font-weight-bold p-1"><?= esc($it['kode_bmn']); ?></span>
                                    <div class="small text-muted mt-1" style="font-size: 0.75rem;">
                                        <?php if (str_starts_with($it['kelompok_bmn'], 'I.')): ?>
                                            <span class="badge badge-info px-1 py-0.5">Peralatan</span>
                                        <?php elseif (str_starts_with($it['kelompok_bmn'], 'II.')): ?>
                                            <span class="badge badge-primary px-1 py-0.5">Gedung</span>
                                        <?php else: ?>
                                            <span class="badge badge-success px-1 py-0.5">Jaringan</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="align-middle">
                                    <div class="font-weight-bold text-primary mb-1"><?= esc($it['jenis_bmn']); ?></div>
                                    <small class="text-muted d-block" style="line-height: 1.35;"><?= esc($it['spesifikasi']); ?></small>
                                </td>
                                <td class="text-center align-middle font-weight-bold">
                                    <?= rtrim(rtrim(number_format((float) $it['jumlah'], 2, ',', '.'), '0'), ','); ?>
                                    <span class="badge badge-light border d-block mt-1"><?= esc($it['satuan']); ?></span>
                                </td>
                                <td class="text-right align-middle">
                                    Rp <?= number_format((float) $it['nilai_perolehan'], 0, ',', '.'); ?>
                                </td>
                                <td class="text-right align-middle font-weight-bold text-dark">
                                    Rp <?= number_format((float) $it['total_nilai_perolehan'], 0, ',', '.'); ?>
                                </td>
                                <td class="align-middle">
                                    <span class="badge badge-info px-2 py-1 font-weight-normal text-wrap" style="max-width: 140px; font-size: 0.8rem;">
                                        <i class="fas fa-map-marker-alt mr-1"></i><?= esc($it['lokasi']); ?>
                                    </span>
                                </td>
                                <td class="text-center align-middle">
                                    <span class="badge badge-light border text-success px-2 py-1"><?= esc($it['kondisi']); ?></span>
                                </td>
                                <td class="text-center align-middle">
                                    <?php $fCount = (int) ($it['foto_count'] ?? 0); ?>
                                    <?php if ($fCount > 0): ?>
                                        <a href="<?= site_url('admin/inventaris/sekolah-rakyat/foto?lokasi=' . urlencode($it['lokasi'])); ?>" class="badge badge-success px-2 py-1.5" title="Lihat di Galeri">
                                            <i class="fas fa-check-circle mr-1"></i> <?= $fCount; ?> Foto
                                        </a>
                                        <?php if (! empty($it['sample_foto_wm'])): ?>
                                            <div class="mt-1">
                                                <img src="<?= base_url($it['sample_foto_wm']); ?>" alt="Thumbnail" class="img-thumbnail" style="width: 48px; height: 36px; object-fit: cover; border-radius: 4px; cursor: pointer;" onclick="previewImageModal('<?= base_url($it['sample_foto_wm']); ?>', '<?= esc($it['jenis_bmn']); ?>')">
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="<?= site_url('admin/inventaris/sekolah-rakyat/foto?lokasi=' . urlencode($it['lokasi'])); ?>" class="badge badge-warning px-2 py-1.5 text-dark" title="Ambil Foto">
                                            <i class="fas fa-camera mr-1"></i> Belum
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center align-middle">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-info btn-xs btn-detail-item" data-id="<?= $it['id']; ?>" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($canEdit): ?>
                                            <button type="button" class="btn btn-outline-primary btn-xs btn-edit-item" data-id="<?= $it['id']; ?>" data-json='<?= json_encode($it, JSON_HEX_APOS | JSON_HEX_QUOT); ?>' title="Ubah Item">
                                                <i class="fas fa-pencil-alt"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($canDelete): ?>
                                            <button type="button" class="btn btn-outline-danger btn-xs btn-hapus-item" data-id="<?= $it['id']; ?>" data-name="<?= esc($it['jenis_bmn']); ?>" title="Hapus Item">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Barang -->
<div class="modal fade" id="modalTambahBarang" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form method="post" action="<?= site_url('admin/inventaris/sekolah-rakyat/barang/tambah'); ?>">
            <?= csrf_field(); ?>
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle mr-2"></i>Tambah Item BMN Sekolah Rakyat</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="small font-weight-bold text-muted">Kelompok BMN <span class="text-danger">*</span></label>
                            <select name="kelompok_bmn" class="form-control form-control-sm" required>
                                <option value="I. Peralatan dan Mesin">I. Peralatan dan Mesin</option>
                                <option value="II. Gedung dan Bangunan">II. Gedung dan Bangunan</option>
                                <option value="III. Jalan, Irigasi dan Jaringan">III. Jalan, Irigasi dan Jaringan</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small font-weight-bold text-muted">Kode BMN <span class="text-danger">*</span></label>
                            <input type="text" name="kode_bmn" class="form-control form-control-sm" placeholder="Contoh: 3.05.02.01.001" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="small font-weight-bold text-muted">Jenis BMN <span class="text-danger">*</span></label>
                            <input type="text" name="jenis_bmn" class="form-control form-control-sm" placeholder="Nama / Jenis Barang BMN" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="small font-weight-bold text-muted">Merk / Tipe / Spesifikasi Teknis</label>
                            <textarea name="spesifikasi" rows="2" class="form-control form-control-sm" placeholder="Uraian spesifikasi material teknis..."></textarea>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small font-weight-bold text-muted">Volume Jumlah <span class="text-danger">*</span></label>
                            <input type="number" step="any" name="jumlah" id="tambah_jumlah" class="form-control form-control-sm" value="1" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small font-weight-bold text-muted">Satuan <span class="text-danger">*</span></label>
                            <input type="text" name="satuan" class="form-control form-control-sm" value="Buah" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small font-weight-bold text-muted">Kondisi</label>
                            <select name="kondisi" class="form-control form-control-sm">
                                <option value="Baik" selected>Baik</option>
                                <option value="Rusak Ringan">Rusak Ringan</option>
                                <option value="Rusak Berat">Rusak Berat</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small font-weight-bold text-muted">Harga Satuan Perolehan (Rp) <span class="text-danger">*</span></label>
                            <input type="text" name="nilai_perolehan" id="tambah_nilai" class="form-control form-control-sm" placeholder="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small font-weight-bold text-muted">Lokasi Gedung / Zona <span class="text-danger">*</span></label>
                            <input type="text" name="lokasi" list="list_lokasi" class="form-control form-control-sm" placeholder="Pilih atau ketik lokasi..." required>
                            <datalist id="list_lokasi">
                                <?php foreach ($lokasiList as $lok): ?>
                                    <option value="<?= esc($lok); ?>"></option>
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="col-12 mb-2">
                            <label class="small font-weight-bold text-muted">Keterangan</label>
                            <textarea name="keterangan" rows="2" class="form-control form-control-sm" placeholder="Catatan tambahan..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save mr-1"></i> Simpan Data</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Ubah Barang -->
<div class="modal fade" id="modalUbahBarang" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form method="post" id="formUbahBarang" action="">
            <?= csrf_field(); ?>
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-pencil-alt mr-2"></i>Ubah Item BMN Sekolah Rakyat</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="small font-weight-bold text-muted">Kelompok BMN</label>
                            <select name="kelompok_bmn" id="edit_kelompok" class="form-control form-control-sm" required>
                                <option value="I. Peralatan dan Mesin">I. Peralatan dan Mesin</option>
                                <option value="II. Gedung dan Bangunan">II. Gedung dan Bangunan</option>
                                <option value="III. Jalan, Irigasi dan Jaringan">III. Jalan, Irigasi dan Jaringan</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small font-weight-bold text-muted">Kode BMN</label>
                            <input type="text" name="kode_bmn" id="edit_kode_bmn" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="small font-weight-bold text-muted">Jenis BMN</label>
                            <input type="text" name="jenis_bmn" id="edit_jenis_bmn" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="small font-weight-bold text-muted">Spesifikasi Material Teknis</label>
                            <textarea name="spesifikasi" id="edit_spesifikasi" rows="2" class="form-control form-control-sm"></textarea>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small font-weight-bold text-muted">Volume Jumlah</label>
                            <input type="number" step="any" name="jumlah" id="edit_jumlah" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small font-weight-bold text-muted">Satuan</label>
                            <input type="text" name="satuan" id="edit_satuan" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small font-weight-bold text-muted">Kondisi</label>
                            <select name="kondisi" id="edit_kondisi" class="form-control form-control-sm">
                                <option value="Baik">Baik</option>
                                <option value="Rusak Ringan">Rusak Ringan</option>
                                <option value="Rusak Berat">Rusak Berat</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small font-weight-bold text-muted">Harga Satuan (Rp)</label>
                            <input type="text" name="nilai_perolehan" id="edit_nilai_perolehan" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small font-weight-bold text-muted">Lokasi Gedung / Zona</label>
                            <input type="text" name="lokasi" id="edit_lokasi" list="list_lokasi" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-12 mb-2">
                            <label class="small font-weight-bold text-muted">Keterangan</label>
                            <textarea name="keterangan" id="edit_keterangan" rows="2" class="form-control form-control-sm"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-info"><i class="fas fa-check-circle mr-1"></i> Perbarui</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Detail Item & Galeri -->
<div class="modal fade" id="modalDetailItem" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white py-2">
                <h6 class="modal-title font-weight-bold" id="detailModalTitle"><i class="fas fa-info-circle mr-1"></i>Detail Barang BMN</h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-7">
                        <table class="table table-sm table-bordered">
                            <tbody>
                                <tr><th style="width: 35%;" class="bg-light">Kelompok BMN</th><td id="dtl_kelompok"></td></tr>
                                <tr><th class="bg-light">Kode BMN</th><td id="dtl_kode"></td></tr>
                                <tr><th class="bg-light">Jenis BMN</th><td id="dtl_jenis" class="font-weight-bold text-primary"></td></tr>
                                <tr><th class="bg-light">Spesifikasi</th><td id="dtl_spek"></td></tr>
                                <tr><th class="bg-light">Volume / Satuan</th><td id="dtl_volume"></td></tr>
                                <tr><th class="bg-light">Harga Satuan</th><td id="dtl_harga"></td></tr>
                                <tr><th class="bg-light">Total Nilai</th><td id="dtl_total" class="font-weight-bold text-success"></td></tr>
                                <tr><th class="bg-light">Lokasi Gedung</th><td id="dtl_lokasi"></td></tr>
                                <tr><th class="bg-light">Kondisi</th><td id="dtl_kondisi"></td></tr>
                                <tr><th class="bg-light">Keterangan</th><td id="dtl_ket"></td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-5">
                        <h6 class="font-weight-bold text-muted mb-2"><i class="fas fa-camera mr-1"></i>Dokumentasi Foto Lapangan</h6>
                        <div id="detailPhotoContainer" class="d-flex flex-wrap" style="gap: 8px;">
                            <!-- Dinamis diisi AJAX -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pratinjau Foto Penuh -->
<div class="modal fade" id="modalPreviewImage" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header py-2 bg-dark text-white">
                <h6 class="modal-title font-weight-bold" id="previewImageTitle">Foto Watermark</h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center p-2 bg-light">
                <img id="previewImageSrc" src="" alt="Pratinjau" class="img-fluid rounded shadow-sm" style="max-height: 80vh;">
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
$(document).ready(function() {
    // Inisialisasi DataTable
    $('#tbl_bmn_sr').DataTable({
        "pageLength": 25,
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        "language": {
            "search": "Cari Cepat:",
            "lengthMenu": "Tampilkan _MENU_ data",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ item",
            "infoEmpty": "Data kosong",
            "paginate": {
                "first": "Awal",
                "last": "Akhir",
                "next": "Lanjut",
                "previous": "Sebelum"
            }
        },
        "order": []
    });

    // Detail Item AJAX
    $('.btn-detail-item').on('click', function() {
        const id = $(this).data('id');
        $.ajax({
            url: '<?= site_url("admin/inventaris/sekolah-rakyat/barang"); ?>/' + id + '/detail',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    const it = res.item;
                    $('#detailModalTitle').html('<i class="fas fa-boxes mr-1"></i> ' + (it.jenis_bmn || 'Detail'));
                    $('#dtl_kelompok').text(it.kelompok_bmn || '-');
                    $('#dtl_kode').text(it.kode_bmn || '-');
                    $('#dtl_jenis').text(it.jenis_bmn || '-');
                    $('#dtl_spek').text(it.spesifikasi || '-');
                    $('#dtl_volume').text((it.jumlah || 1) + ' ' + (it.satuan || ''));
                    $('#dtl_harga').text('Rp ' + Number(it.nilai_perolehan || 0).toLocaleString('id-ID'));
                    $('#dtl_total').text('Rp ' + Number(it.total_nilai_perolehan || 0).toLocaleString('id-ID'));
                    $('#dtl_lokasi').text(it.lokasi || '-');
                    $('#dtl_kondisi').text(it.kondisi || 'Baik');
                    $('#dtl_ket').text(it.keterangan || '-');

                    let photoHtml = '';
                    if (res.fotos && res.fotos.length > 0) {
                        res.fotos.forEach(function(f) {
                            const imgSrc = '<?= base_url(); ?>/' + (f.foto_watermark || f.foto_asli);
                            photoHtml += `
                                <div class="position-relative border rounded p-1 mb-2" style="width: 48%;">
                                    <img src="${imgSrc}" class="img-fluid rounded" style="height: 110px; width: 100%; object-fit: cover; cursor: pointer;" onclick="previewImageModal('${imgSrc}', '${f.judul_foto || 'Foto'}')">
                                    <small class="d-block text-truncate font-weight-bold mt-1">${f.judul_foto || 'Dokumentasi'}</small>
                                    <small class="text-muted" style="font-size: 0.7rem;">${f.taken_at || ''}</small>
                                </div>
                            `;
                        });
                    } else {
                        photoHtml = '<div class="alert alert-light text-muted w-100 p-3 text-center"><i class="fas fa-camera mr-1"></i> Belum ada foto dokumentasi.<br><a href="<?= site_url("admin/inventaris/sekolah-rakyat/foto"); ?>?lokasi=' + encodeURIComponent(it.lokasi) + '" class="btn btn-xs btn-outline-primary mt-2">Buka Kamera Lapangan</a></div>';
                    }
                    $('#detailPhotoContainer').html(photoHtml);

                    $('#modalDetailItem').modal('show');
                } else {
                    alert(res.error || 'Gagal memuat detail.');
                }
            }
        });
    });

    // Ubah Item Modal
    $('.btn-edit-item').on('click', function() {
        const it = $(this).data('json');
        $('#formUbahBarang').attr('action', '<?= site_url("admin/inventaris/sekolah-rakyat/barang"); ?>/' + it.id + '/ubah');
        $('#edit_kelompok').val(it.kelompok_bmn);
        $('#edit_kode_bmn').val(it.kode_bmn);
        $('#edit_jenis_bmn').val(it.jenis_bmn);
        $('#edit_spesifikasi').val(it.spesifikasi);
        $('#edit_jumlah').val(it.jumlah);
        $('#edit_satuan').val(it.satuan);
        $('#edit_kondisi').val(it.kondisi);
        $('#edit_nilai_perolehan').val(Number(it.nilai_perolehan).toLocaleString('id-ID'));
        $('#edit_lokasi').val(it.lokasi);
        $('#edit_keterangan').val(it.keterangan);
        $('#modalUbahBarang').modal('show');
    });

    // Hapus Item
    $('.btn-hapus-item').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');

        if (confirm('Apakah Anda yakin ingin menghapus item "' + name + '" beserta seluruh arsip fotonya?')) {
            $.ajax({
                url: '<?= site_url("admin/inventaris/sekolah-rakyat/barang"); ?>/' + id + '/hapus',
                type: 'POST',
                data: {
                    <?= csrf_token(); ?>: '<?= csrf_hash(); ?>'
                },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        location.reload();
                    } else {
                        alert(res.error || 'Gagal menghapus.');
                    }
                },
                error: function() {
                    alert('Terjadi kesalahan jaringan.');
                }
            });
        }
    });
});

function previewImageModal(src, title) {
    $('#previewImageSrc').attr('src', src);
    $('#previewImageTitle').text(title || 'Foto Watermark');
    $('#modalPreviewImage').modal('show');
}
</script>
<?= $this->endSection(); ?>
