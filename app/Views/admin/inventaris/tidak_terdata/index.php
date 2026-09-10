<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<style>
/* Styling Modal & Select2 Enhancement */
.modal-content {
    border: none !important;
    border-radius: 12px !important;
    box-shadow: 0 15px 35px rgba(0,0,0,0.18) !important;
}
.modal-header {
    border-top-left-radius: 12px !important;
    border-top-right-radius: 12px !important;
}
.modal-footer {
    border-bottom-left-radius: 12px !important;
    border-bottom-right-radius: 12px !important;
}

.modal .select2-container--bootstrap4 .select2-selection {
    border: 1px solid #ced4da !important;
    border-radius: 6px !important;
    min-height: 40px !important;
    padding: 5px 8px !important;
    background-color: #ffffff !important;
}
.modal .select2-container--bootstrap4.select2-container--focus .select2-selection,
.modal .select2-container--bootstrap4.select2-container--open .select2-selection {
    border-color: #007bff !important;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
}
.modal .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
    line-height: 28px !important;
    padding-left: 4px !important;
    color: #1e293b !important;
    font-size: 0.9rem !important;
}

.select2-container--open .select2-dropdown {
    border: 1px solid #cbd5e1 !important;
    border-radius: 8px !important;
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.18) !important;
    z-index: 9999 !important;
}

.badge-pu {
    background-color: #1E3A8A;
    color: #ffffff;
}

.lokasi-pill {
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    color: #0f172a;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
</style>

<div class="container-fluid">
    <?php if (session()->getFlashdata('success')) : ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle mr-1"></i> <?= esc(session()->getFlashdata('success')); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')) : ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-circle mr-1"></i> <?= esc(session()->getFlashdata('error')); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Summary Widgets -->
    <div class="row mb-3">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #1E3A8A;">
                <span class="info-box-icon bg-navy elevation-1" style="border-radius: 8px;"><i class="fas fa-boxes"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Jenis Barang</span>
                    <span class="info-box-number text-dark" style="font-size: 1.35rem;"><?= number_format($summary['total_item'] ?? 0); ?> Item</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #0284c7;">
                <span class="info-box-icon bg-info elevation-1" style="border-radius: 8px;"><i class="fas fa-cubes"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Total Kuantitas</span>
                    <span class="info-box-number text-info" style="font-size: 1.35rem;"><?= number_format($summary['total_buah'] ?? 0); ?> Buah</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #16a34a;">
                <span class="info-box-icon bg-success elevation-1" style="border-radius: 8px;"><i class="fas fa-check-double"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Kondisi Baik</span>
                    <span class="info-box-number text-success" style="font-size: 1.35rem;"><?= number_format($summary['total_baik'] ?? 0); ?> Buah</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #dc2626;">
                <span class="info-box-icon bg-danger elevation-1" style="border-radius: 8px;"><i class="fas fa-tools"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Perlu Perbaikan / Rusak</span>
                    <span class="info-box-number text-danger" style="font-size: 1.35rem;"><?= number_format($summary['total_rusak'] ?? 0); ?> Buah</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card card-outline card-primary shadow-sm" style="border-radius: 10px;">
        <div class="card-header bg-white py-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h5 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fas fa-clipboard-list text-primary mr-2"></i>Daftar Aset Tidak Terdata
                    </h5>
                    <div class="text-muted small">Pencatatan inventaris non-BMN / aset operasional kantor yang belum terinput di aplikasi SIMAN</div>
                </div>
                <div class="card-tools mt-2 mt-sm-0">
                    <?php if (! empty($menuPermissions['add'])) : ?>
                        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm mr-1" data-toggle="modal" data-target="#modalTambah">
                            <i class="fas fa-plus-circle mr-1"></i> Tambah Aset
                        </button>
                    <?php endif; ?>

                    <?php if (! empty($menuPermissions['export'])) : ?>
                        <a href="<?= base_url('admin/inventaris/tidak-terdata/cetak-pdf?' . http_build_query($filters)); ?>" target="_blank" class="btn btn-outline-danger btn-sm rounded-pill px-3 mr-1 shadow-sm" title="Pratinjau Cetak PDF di Tab Baru">
                            <i class="fas fa-print mr-1"></i> Cetak PDF
                        </a>
                        <a href="<?= base_url('admin/inventaris/tidak-terdata/export-pdf?' . http_build_query($filters)); ?>" class="btn btn-danger btn-sm rounded-pill px-3 mr-1 shadow-sm" title="Unduh Langsung File PDF (.pdf)">
                            <i class="fas fa-file-pdf mr-1"></i> Ekspor PDF
                        </a>
                        <a href="<?= base_url('admin/inventaris/tidak-terdata/export-excel?' . http_build_query($filters)); ?>" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-sm" title="Unduh Spreadsheet Excel (.xlsx)">
                            <i class="fas fa-file-excel mr-1"></i> Ekspor Excel
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Filter Box -->
        <div class="card-body border-bottom bg-light py-2">
            <form action="<?= base_url('admin/inventaris/tidak-terdata'); ?>" method="get" class="form-row align-items-center">
                <div class="col-md-4 col-sm-6 my-1">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white"><i class="fas fa-map-marker-alt text-danger"></i></span>
                        </div>
                        <select name="ruangan_id" class="form-control form-control-sm select2-filter">
                            <option value="">-- Semua Ruangan / Lokasi --</option>
                            <?php foreach ($ruanganList as $r) : ?>
                                <option value="<?= $r['id']; ?>" <?= ($filters['ruangan_id'] ?? '') == $r['id'] ? 'selected' : ''; ?>>
                                    <?= esc($r['nama_ruangan']); ?> (<?= esc($r['kode_ruangan']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 my-1">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white"><i class="fas fa-heartbeat text-info"></i></span>
                        </div>
                        <select name="kondisi" class="form-control form-control-sm">
                            <option value="">-- Semua Kondisi --</option>
                            <option value="baik" <?= ($filters['kondisi'] ?? '') === 'baik' ? 'selected' : ''; ?>>Baik</option>
                            <option value="rusak_ringan" <?= ($filters['kondisi'] ?? '') === 'rusak_ringan' ? 'selected' : ''; ?>>Rusak Ringan</option>
                            <option value="rusak_berat" <?= ($filters['kondisi'] ?? '') === 'rusak_berat' ? 'selected' : ''; ?>>Rusak Berat</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-4 col-sm-8 my-1">
                    <div class="input-group input-group-sm">
                        <input type="text" name="q" value="<?= esc($filters['q'] ?? ''); ?>" class="form-control form-control-sm" placeholder="Cari nama barang, merk/tipe, lokasi...">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                            <?php if (! empty($filters['ruangan_id']) || ! empty($filters['kondisi']) || ! empty($filters['q'])) : ?>
                                <a href="<?= base_url('admin/inventaris/tidak-terdata'); ?>" class="btn btn-outline-secondary" title="Reset Filter"><i class="fas fa-sync-alt"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table View -->
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped text-nowrap align-middle" id="tblTidakTerdata" style="width: 100%;">
                <thead class="thead-light">
                    <tr>
                        <th class="text-center" style="width: 45px;">No</th>
                        <th>Nama Barang</th>
                        <th class="text-center">Kuantitas</th>
                        <th>Merk / Type</th>
                        <th class="text-center">Tahun</th>
                        <th><i class="fas fa-map-marker-alt text-danger mr-1"></i> Lokasi Aset</th>
                        <th class="text-center">Kondisi</th>
                        <th>Keterangan Perolehan</th>
                        <th class="text-center" style="width: 90px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)) : ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fas fa-box-open fa-3x mb-3 text-secondary d-block"></i>
                                Belum ada data aset tidak terdata. Silakan tambah data baru.
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php $no = 1; foreach ($items as $item) : ?>
                            <tr>
                                <td class="text-center font-weight-bold text-muted"><?= $no++; ?></td>
                                <td class="font-weight-bold text-dark">
                                    <?= esc($item['nama_barang']); ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-pill badge-primary px-3 py-1 font-weight-bold" style="font-size: 0.88rem;">
                                        <?= (int) $item['jumlah']; ?> <?= esc($item['satuan'] ?: 'Buah'); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (! empty($item['merk_tipe'])) : ?>
                                        <span class="text-dark"><i class="fas fa-tag text-secondary mr-1"></i><?= esc($item['merk_tipe']); ?></span>
                                    <?php else : ?>
                                        <span class="text-muted font-italic">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (! empty($item['tahun_perolehan'])) : ?>
                                        <span class="badge badge-light border font-weight-bold"><?= esc($item['tahun_perolehan']); ?></span>
                                    <?php else : ?>
                                        <span class="text-muted font-italic">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $lokasiLabel = $item['nama_ruangan'] ?? $item['lokasi_penempatan'] ?? '-';
                                    ?>
                                    <div class="lokasi-pill">
                                        <i class="fas fa-door-open text-primary"></i>
                                        <span><?= esc($lokasiLabel); ?></span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php if ($item['kondisi'] === 'baik') : ?>
                                        <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Baik</span>
                                    <?php elseif ($item['kondisi'] === 'rusak_ringan') : ?>
                                        <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-exclamation-triangle mr-1"></i> Rusak Ringan</span>
                                    <?php else : ?>
                                        <span class="badge badge-danger px-2 py-1"><i class="fas fa-times-circle mr-1"></i> Rusak Berat</span>
                                    <?php endif; ?>
                                </td>
                                <td style="max-width: 280px; white-space: normal;">
                                    <?php if (! empty($item['keterangan'])) : ?>
                                        <span class="text-muted small"><?= esc($item['keterangan']); ?></span>
                                    <?php else : ?>
                                        <span class="text-muted font-italic">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <?php if (! empty($menuPermissions['edit'])) : ?>
                                            <button type="button" 
                                                class="btn btn-warning text-white btn-edit" 
                                                data-toggle="modal" 
                                                data-target="#modalEdit"
                                                data-id="<?= (int) $item['id']; ?>"
                                                data-nama="<?= esc($item['nama_barang'], 'attr'); ?>"
                                                data-jumlah="<?= (int) $item['jumlah']; ?>"
                                                data-satuan="<?= esc($item['satuan'] ?: 'Buah', 'attr'); ?>"
                                                data-merk="<?= esc($item['merk_tipe'] ?? '', 'attr'); ?>"
                                                data-tahun="<?= esc($item['tahun_perolehan'] ?? '', 'attr'); ?>"
                                                data-ruangan-id="<?= esc((string) ($item['ruangan_id'] ?? ''), 'attr'); ?>"
                                                data-lokasi="<?= esc($item['lokasi_penempatan'] ?? '', 'attr'); ?>"
                                                data-kondisi="<?= esc($item['kondisi'] ?? 'baik', 'attr'); ?>"
                                                data-petugas-nama="<?= esc($item['petugas_nama'] ?? 'Hendrick Bastiar', 'attr'); ?>"
                                                data-petugas-nip="<?= esc($item['petugas_nip'] ?? '197810162025211023', 'attr'); ?>"
                                                data-keterangan="<?= esc($item['keterangan'] ?? '', 'attr'); ?>"
                                                title="Ubah Data">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        <?php endif; ?>

                                        <?php if (! empty($menuPermissions['delete'])) : ?>
                                            <button type="button" 
                                                class="btn btn-danger btn-delete" 
                                                data-toggle="modal" 
                                                data-target="#modalDelete"
                                                data-id="<?= (int) $item['id']; ?>" 
                                                data-nama="<?= esc($item['nama_barang'], 'attr'); ?>" 
                                                title="Hapus Data">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (! empty($items)) : ?>
                    <tfoot class="bg-light font-weight-bold">
                        <tr>
                            <td colspan="2" class="text-right">TOTAL KUANTITAS:</td>
                            <td class="text-center">
                                <span class="badge badge-pill badge-dark px-3 py-1 font-weight-bold" style="font-size: 0.9rem;">
                                    <?= number_format($summary['total_buah'] ?? 0); ?> Buah
                                </span>
                            </td>
                            <td colspan="6" class="text-muted font-italic">
                                (Terdiri dari <?= number_format($summary['total_item'] ?? 0); ?> jenis barang inventaris)
                            </td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Data -->
<div class="modal fade" id="modalTambah" tabindex="-1" role="dialog" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="<?= base_url('admin/inventaris/tidak-terdata/create'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold" id="modalTambahLabel">
                        <i class="fas fa-plus-circle mr-2"></i>Tambah Aset Tidak Terdata
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-row">
                        <div class="form-group col-md-8">
                            <label class="font-weight-bold">Nama Barang <span class="text-danger">*</span></label>
                            <input type="text" name="nama_barang" class="form-control" placeholder="Contoh: P.C Unit, Printer, Lemari Es" required>
                        </div>
                        <div class="form-group col-md-2">
                            <label class="font-weight-bold">Kuantitas <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah" class="form-control" value="1" min="1" required>
                        </div>
                        <div class="form-group col-md-2">
                            <label class="font-weight-bold">Satuan</label>
                            <input type="text" name="satuan" class="form-control" value="Buah">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-8">
                            <label class="font-weight-bold">Merk / Spesifikasi / Type</label>
                            <input type="text" name="merk_tipe" class="form-control" placeholder="Contoh: PC Desktop Intel Core i5 8400, HP Smart Tank 210">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold">Tahun Perolehan</label>
                            <input type="text" name="tahun_perolehan" class="form-control" placeholder="Contoh: 2023">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold"><i class="fas fa-map-marker-alt text-danger mr-1"></i> Lokasi Ruangan</label>
                            <select name="ruangan_id" id="tambahRuanganId" class="form-control select2-modal">
                                <option value="">-- Pilih dari Master Ruangan (Opsional) --</option>
                                <?php foreach ($ruanganList as $r) : ?>
                                    <option value="<?= $r['id']; ?>" data-nama="<?= esc($r['nama_ruangan']); ?>">
                                        <?= esc($r['nama_ruangan']); ?> (<?= esc($r['kode_ruangan']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Pilih ruangan resmi atau isi lokasi penempatan manual di samping.</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Lokasi Penempatan / Detail Ruang</label>
                            <input type="text" name="lokasi_penempatan" id="tambahLokasiPenempatan" class="form-control" placeholder="Contoh: Ruang Tata Usaha, Ruang Staf, Pantry">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Kondisi Barang <span class="text-danger">*</span></label>
                            <select name="kondisi" class="form-control" required>
                                <option value="baik" selected>Baik</option>
                                <option value="rusak_ringan">Rusak Ringan</option>
                                <option value="rusak_berat">Rusak Berat</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Petugas Aset Tetap</label>
                            <input type="text" name="petugas_nama" class="form-control" value="Hendrick Bastiar">
                            <input type="hidden" name="petugas_nip" value="197810162025211023">
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Keterangan / Riwayat Perolehan</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Contoh: Diperoleh Pada Saat Masih PPK PS di BPPW Dirjen Cipta Karya"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="fas fa-save mr-1"></i> Simpan Aset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ubah Data -->
<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="formEdit" action="" method="post">
                <?= csrf_field(); ?>
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title font-weight-bold" id="modalEditLabel">
                        <i class="fas fa-edit mr-2"></i>Ubah Aset Tidak Terdata
                    </h5>
                    <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-row">
                        <div class="form-group col-md-8">
                            <label class="font-weight-bold">Nama Barang <span class="text-danger">*</span></label>
                            <input type="text" name="nama_barang" id="editNamaBarang" class="form-control" required>
                        </div>
                        <div class="form-group col-md-2">
                            <label class="font-weight-bold">Kuantitas <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah" id="editJumlah" class="form-control" min="1" required>
                        </div>
                        <div class="form-group col-md-2">
                            <label class="font-weight-bold">Satuan</label>
                            <input type="text" name="satuan" id="editSatuan" class="form-control">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-8">
                            <label class="font-weight-bold">Merk / Spesifikasi / Type</label>
                            <input type="text" name="merk_tipe" id="editMerkTipe" class="form-control">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold">Tahun Perolehan</label>
                            <input type="text" name="tahun_perolehan" id="editTahunPerolehan" class="form-control">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold"><i class="fas fa-map-marker-alt text-danger mr-1"></i> Lokasi Ruangan</label>
                            <select name="ruangan_id" id="editRuanganId" class="form-control select2-modal">
                                <option value="">-- Pilih dari Master Ruangan (Opsional) --</option>
                                <?php foreach ($ruanganList as $r) : ?>
                                    <option value="<?= $r['id']; ?>" data-nama="<?= esc($r['nama_ruangan']); ?>">
                                        <?= esc($r['nama_ruangan']); ?> (<?= esc($r['kode_ruangan']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Lokasi Penempatan / Detail Ruang</label>
                            <input type="text" name="lokasi_penempatan" id="editLokasiPenempatan" class="form-control">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Kondisi Barang <span class="text-danger">*</span></label>
                            <select name="kondisi" id="editKondisi" class="form-control" required>
                                <option value="baik">Baik</option>
                                <option value="rusak_ringan">Rusak Ringan</option>
                                <option value="rusak_berat">Rusak Berat</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Petugas Aset Tetap</label>
                            <input type="text" name="petugas_nama" id="editPetugasNama" class="form-control">
                            <input type="hidden" name="petugas_nip" id="editPetugasNip" value="197810162025211023">
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Keterangan / Riwayat Perolehan</label>
                        <textarea name="keterangan" id="editKeterangan" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 shadow-sm font-weight-bold text-dark">
                        <i class="fas fa-save mr-1"></i> Perbarui Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Hapus Data -->
<div class="modal fade" id="modalDelete" tabindex="-1" role="dialog" aria-labelledby="modalDeleteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="formDelete" action="" method="post">
                <?= csrf_field(); ?>
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title font-weight-bold" id="modalDeleteLabel">
                        <i class="fas fa-trash-alt mr-2"></i>Konfirmasi Hapus
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 text-center">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <p class="mb-1 font-weight-bold text-dark" style="font-size: 1.1rem;">Apakah Anda yakin ingin menghapus data aset ini?</p>
                    <p class="text-danger font-weight-bold" id="deleteNamaBarang"></p>
                    <small class="text-muted">Data yang dihapus tidak dapat dipulihkan kembali.</small>
                </div>
                <div class="modal-footer bg-light justify-content-center">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm">
                        <i class="fas fa-trash-alt mr-1"></i> Ya, Hapus Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script>
$(document).ready(function() {
    // Inisialisasi DataTable
    if ($.fn.DataTable) {
        $('#tblTidakTerdata').DataTable({
            responsive: true,
            pageLength: 25,
            language: {
                search: "Cari data:",
                lengthMenu: "Tampilkan _MENU_ baris",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "Lanjut",
                    previous: "Kembali"
                },
                emptyTable: "Tidak ada data yang tersedia"
            }
        });
    }

    // Inisialisasi Select2 Filter
    if ($.fn.select2) {
        $('.select2-filter').select2({
            theme: 'bootstrap4',
            width: 'resolve'
        });
    }

    // Handle Select2 Ruangan di Modal Tambah
    $('#modalTambah').on('shown.bs.modal', function () {
        $('#tambahRuanganId').select2({
            dropdownParent: $('#modalTambah'),
            theme: 'bootstrap4',
            width: '100%'
        });
    });

    $('#tambahRuanganId').on('select2:select', function() {
        var selectedName = $(this).find(':selected').data('nama');
        if (selectedName && !$('#tambahLokasiPenempatan').val()) {
            $('#tambahLokasiPenempatan').val(selectedName);
        }
    });

    // Handle Select2 Ruangan di Modal Edit
    $('#modalEdit').on('shown.bs.modal', function () {
        $('#editRuanganId').select2({
            dropdownParent: $('#modalEdit'),
            theme: 'bootstrap4',
            width: '100%'
        });
    });

    $('#editRuanganId').on('select2:select', function() {
        var selectedName = $(this).find(':selected').data('nama');
        if (selectedName) {
            $('#editLokasiPenempatan').val(selectedName);
        }
    });

    // Fungsi Pengisian Data Modal Edit yang Andal
    function populateEditModal(el) {
        var $btn = $(el).closest('.btn-edit');
        if ($btn.length === 0) return;

        var id = $btn.data('id') || $btn.attr('data-id');
        if (!id) return;

        var nama = $btn.data('nama') || $btn.attr('data-nama') || '';
        var jumlah = $btn.data('jumlah') || $btn.attr('data-jumlah') || 1;
        var satuan = $btn.data('satuan') || $btn.attr('data-satuan') || 'Buah';
        var merk = $btn.data('merk') || $btn.attr('data-merk') || '';
        var tahun = $btn.data('tahun') || $btn.attr('data-tahun') || '';
        var ruanganId = $btn.data('ruangan-id') || $btn.attr('data-ruangan-id') || '';
        var lokasi = $btn.data('lokasi') || $btn.attr('data-lokasi') || '';
        var kondisi = $btn.data('kondisi') || $btn.attr('data-kondisi') || 'baik';
        var petugasNama = $btn.data('petugas-nama') || $btn.attr('data-petugas-nama') || 'Hendrick Bastiar';
        var petugasNip = $btn.data('petugas-nip') || $btn.attr('data-petugas-nip') || '197810162025211023';
        var keterangan = $btn.data('keterangan') || $btn.attr('data-keterangan') || '';

        $('#formEdit').attr('action', '<?= base_url("admin/inventaris/tidak-terdata"); ?>/' + id + '/edit');
        $('#editNamaBarang').val(nama);
        $('#editJumlah').val(jumlah);
        $('#editSatuan').val(satuan);
        $('#editMerkTipe').val(merk);
        $('#editTahunPerolehan').val(tahun);
        $('#editRuanganId').val(ruanganId ? String(ruanganId) : '').trigger('change');
        $('#editLokasiPenempatan').val(lokasi);
        $('#editKondisi').val(kondisi);
        $('#editPetugasNama').val(petugasNama);
        $('#editPetugasNip').val(petugasNip);
        $('#editKeterangan').val(keterangan);
    }

    // Fungsi Pengisian Data Modal Hapus
    function populateDeleteModal(el) {
        var $btn = $(el).closest('.btn-delete');
        if ($btn.length === 0) return;

        var id = $btn.data('id') || $btn.attr('data-id');
        var nama = $btn.data('nama') || $btn.attr('data-nama') || '';

        $('#formDelete').attr('action', '<?= base_url("admin/inventaris/tidak-terdata"); ?>/' + id + '/delete');
        $('#deleteNamaBarang').text(nama);
    }

    // Event Listener Klik Tombol Edit
    $(document).on('click', '.btn-edit', function(e) {
        populateEditModal(this);
        $('#modalEdit').modal('show');
    });

    // Event Listener Modal Edit saat ditampilkan via Bootstrap data-toggle
    $('#modalEdit').on('show.bs.modal', function(e) {
        if (e.relatedTarget) {
            populateEditModal(e.relatedTarget);
        }
    });

    // Event Listener Klik Tombol Hapus
    $(document).on('click', '.btn-delete', function(e) {
        populateDeleteModal(this);
        $('#modalDelete').modal('show');
    });

    // Event Listener Modal Hapus saat ditampilkan via Bootstrap data-toggle
    $('#modalDelete').on('show.bs.modal', function(e) {
        if (e.relatedTarget) {
            populateDeleteModal(e.relatedTarget);
        }
    });
});
</script>
<?= $this->endSection(); ?>
