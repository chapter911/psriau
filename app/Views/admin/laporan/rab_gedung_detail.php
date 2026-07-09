<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<style>
    .table-sticky-container {
        max-height: 70vh;
        overflow: auto;
    }
    .table-sticky-container thead th {
        border: 1px solid #c2c7d0 !important;
        background-color: #f8f9fa !important;
        color: #343a40 !important;
        text-align: center;
        vertical-align: middle !important;
        font-weight: 600;
    }
    .table-sticky-container thead tr:nth-child(1) th {
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .table-sticky-container thead tr:nth-child(2) th {
        position: sticky;
        top: 31px; /* Height of first header row */
        z-index: 10;
    }
    .table-sticky-container tfoot td {
        border: 1px solid #c2c7d0 !important;
        background-color: #f8f9fa !important;
        color: #343a40 !important;
    }
</style>
<?php
$sekolah = $sekolah ?? [];
$sekolahs = $sekolahs ?? [];
$gedungs = $gedungs ?? [];
$canAdd = (bool) ($can_add ?? false);
$canEdit = (bool) ($can_edit ?? false);
$canDelete = (bool) ($can_delete ?? false);
$canImport = (bool) ($can_import ?? false);

$totalKontrak = $total_kontrak ?? 0;
$totalMcNol = $total_mcnol ?? 0;
$totalTambah = $total_tambah ?? 0;
$totalKurang = $total_kurang ?? 0;
?>

<!-- Alert Flash Messages -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="icon fas fa-check"></i> <?= session()->getFlashdata('success'); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="icon fas fa-ban"></i> <?= session()->getFlashdata('error'); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Header & Back Button -->
<div class="d-flex align-items-center mb-3">
    <a href="<?= site_url('admin/laporan/rab-gedung'); ?>" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar Sekolah
    </a>
</div>

<!-- School Info Card & Filter -->
<div class="card card-outline card-primary mb-4">
    <div class="card-header d-flex align-items-center py-2">
        <h3 class="card-title mb-0 font-weight-bold text-primary">
            <i class="fas fa-school mr-1"></i> <?= esc($sekolah['nama']); ?>
        </h3>
        <div class="card-tools ml-auto">
            <?php if ($canImport): ?>
                <button type="button" class="btn btn-outline-info btn-sm mr-2" data-toggle="modal" data-target="#modalImport">
                    <i class="fas fa-file-import mr-1"></i> Import Excel
                </button>
            <?php endif; ?>
            <?php if ($canAdd): ?>
                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalAdd">
                    <i class="fas fa-plus mr-1"></i> Tambah Item
                </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body py-3">
        <div class="row align-items-center">
            <div class="col-md-2 border-right">
                <span class="text-muted d-block small">NPSN / NSM</span>
                <span class="font-weight-bold text-dark"><?= esc($sekolah['npsn']); ?> / <?= esc($sekolah['nsm'] ?: '-'); ?></span>
            </div>
            <div class="col-md-2 border-right">
                <span class="text-muted d-block small">Kecamatan</span>
                <span class="font-weight-bold text-dark"><?= esc($sekolah['kecamatan'] ?: '-'); ?></span>
            </div>
            <div class="col-md-2 border-right">
                <span class="text-muted d-block small">Kabupaten</span>
                <span class="font-weight-bold text-dark"><?= esc($sekolah['kabupaten'] ?: '-'); ?></span>
            </div>
            <div class="col-md-3 border-right">
                <div class="form-group mb-0">
                    <label for="filterGedung" class="mb-0 text-muted small font-weight-normal">Filter Pekerjaan</label>
                    <select id="filterGedung" class="form-control form-control-sm">
                        <option value="">-- Semua Pekerjaan --</option>
                        <?php foreach ($gedungs as $gedung): ?>
                            <option value="<?= esc($gedung); ?>"><?= esc($gedung); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group mb-0">
                    <label for="filterPaket" class="mb-0 text-muted small font-weight-normal">Filter Paket</label>
                    <select id="filterPaket" class="form-control form-control-sm">
                        <option value="">-- Semua Paket --</option>
                        <?php foreach ($pakets as $paket): ?>
                            <option value="<?= esc((string) $paket['id']); ?>"><?= esc((string) $paket['nama_paket']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Summary Dashboard Cards -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h4 class="font-weight-bold text-truncate" style="font-size: 1.25rem;">Rp <?= number_format($totalKontrak, 0, ',', '.'); ?></h4>
                <p class="mb-0">Nilai Kontrak Awal</p>
            </div>
            <div class="icon">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h4 class="font-weight-bold text-truncate" style="font-size: 1.25rem;">Rp <?= number_format($totalMcNol, 0, ',', '.'); ?></h4>
                <p class="mb-0">Nilai Realisasi MC Nol</p>
            </div>
            <div class="icon">
                <i class="fas fa-calculator"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h4 class="font-weight-bold text-truncate" style="font-size: 1.25rem;">Rp <?= number_format($totalTambah, 0, ',', '.'); ?></h4>
                <p class="mb-0">Total Pekerjaan Tambah</p>
            </div>
            <div class="icon">
                <i class="fas fa-plus-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h4 class="font-weight-bold text-truncate text-dark" style="font-size: 1.25rem;">Rp <?= number_format($totalKurang, 0, ',', '.'); ?></h4>
                <p class="mb-0 text-dark">Total Pekerjaan Kurang</p>
            </div>
            <div class="icon">
                <i class="fas fa-minus-circle"></i>
            </div>
        </div>
    </div>
</div>

<!-- Details Table Card -->
<div class="card">
    <div class="card-header bg-light">
        <h3 class="card-title mb-0 font-weight-bold"><i class="fas fa-list-alt mr-1"></i> Rincian Item Pekerjaan</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive table-sticky-container">
            <table class="table table-bordered table-striped table-hover w-100 table-sm" id="tableRabDetail" style="font-size: 0.85rem;">
                <thead class="thead-light">
                    <tr>
                        <th rowspan="2" class="align-middle text-center" style="width: 30px;">No</th>
                        <th rowspan="2" class="align-middle" style="min-width: 150px;">Pekerjaan</th>
                        <th rowspan="2" class="align-middle" style="min-width: 200px;">Uraian Pekerjaan</th>
                        <th rowspan="2" class="align-middle text-center" style="width: 40px;">Sat</th>
                        <th colspan="3" class="text-center py-1">KONTRAK</th>
                        <th colspan="2" class="text-center py-1">MC NOL (100%)</th>
                        <th colspan="2" class="text-center py-1">TAMBAH</th>
                        <th colspan="2" class="text-center py-1">KURANG</th>
                        <th rowspan="2" class="align-middle text-center" style="width: 80px;">Aksi</th>
                    </tr>
                    <tr>
                        <!-- Kontrak -->
                        <th class="text-center py-1">Vol</th>
                        <th class="text-center py-1">Harga Satuan</th>
                        <th class="text-center py-1">Total Harga</th>
                        <!-- MC Nol -->
                        <th class="text-center py-1">Vol</th>
                        <th class="text-center py-1">Total Harga</th>
                        <!-- Tambah -->
                        <th class="text-center py-1">Vol</th>
                        <th class="text-center py-1">Total Harga</th>
                        <!-- Kurang -->
                        <th class="text-center py-1">Vol</th>
                        <th class="text-center py-1">Total Harga</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr class="bg-light font-weight-bold" style="font-size: 0.85rem;">
                        <td colspan="4" class="text-right align-middle">TOTAL:</td>
                        <td class="text-right align-middle" id="sumTotalKontrakVol">-</td>
                        <td class="text-right align-middle">-</td>
                        <td class="text-right align-middle" id="sumTotalKontrak">Rp 0</td>
                        <td class="text-right align-middle" id="sumTotalMcNolVol">-</td>
                        <td class="text-right align-middle" id="sumTotalMcNol">Rp 0</td>
                        <td class="text-right align-middle" id="sumTotalTambahVol">-</td>
                        <td class="text-right align-middle" id="sumTotalTambah">Rp 0</td>
                        <td class="text-right align-middle" id="sumTotalKurangVol">-</td>
                        <td class="text-right align-middle" id="sumTotalKurang">Rp 0</td>
                        <td class="text-center align-middle"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Modal Import -->
<?php if ($canImport): ?>
<div class="modal fade" id="modalImport" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mb-0"><i class="fas fa-file-import mr-1"></i> Import Excel RAB</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('admin/laporan/rab-gedung/import'); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="icon fas fa-info-circle mr-1"></i> Unggah file adendum Excel (format .xlsx atau .xls). Pastikan file memiliki sheet bernama <strong>"RAB PER GEDUNG"</strong> dengan struktur kolom yang sesuai.
                    </div>
                    <div class="form-group">
                        <label for="file_excel">File Excel</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="file_excel" name="file_excel" accept=".xlsx,.xls" required>
                            <label class="custom-file-label" for="file_excel">Pilih file...</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="import_paket_id">Paket <span class="text-danger">*</span></label>
                        <select class="form-control" id="import_paket_id" name="paket_id" required>
                            <option value="">-- Pilih Paket --</option>
                            <?php foreach ($pakets as $paket): ?>
                                <option value="<?= esc((string) $paket['id']); ?>"><?= esc((string) $paket['nama_paket']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="clear_data" name="clear_data" value="1" checked>
                            <label class="custom-control-label" for="clear_data">Kosongkan data sebelum import (Trunkasi tabel)</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-upload mr-1"></i> Mulai Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Add -->
<?php if ($canAdd): ?>
<div class="modal fade" id="modalAdd" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mb-0"><i class="fas fa-plus mr-1"></i> Tambah Item RAB - <?= esc($sekolah['nama']); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('admin/laporan/rab-gedung/tambah'); ?>" method="post">
                <?= csrf_field(); ?>
                <input type="hidden" name="sekolah_npsn" value="<?= esc($sekolah['npsn']); ?>">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="addPaketId">Paket <span class="text-danger">*</span></label>
                                <select class="form-control" id="addPaketId" name="paket_id" required>
                                    <option value="">-- Pilih Paket --</option>
                                    <?php foreach ($pakets as $paket): ?>
                                        <option value="<?= esc((string) $paket['id']); ?>"><?= esc((string) $paket['nama_paket']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="addPekerjaanUtama">Pekerjaan Utama</label>
                                <input type="text" class="form-control" id="addPekerjaanUtama" name="pekerjaan_utama" placeholder="Contoh: Rehabilitasi dan Renovasi...">
                            </div>
                            <div class="form-group">
                                <label for="addGedung">Pekerjaan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="addGedung" name="gedung" placeholder="Contoh: REHABILITASI BANGUNAN A" required>
                            </div>
                            <div class="form-group">
                                <label for="addKategori1">Kategori 1 (Romawi)</label>
                                <input type="text" class="form-control" id="addKategori1" name="kategori_1" placeholder="Contoh: PEKERJAAN STRUKTUR">
                            </div>
                            <div class="form-group">
                                <label for="addKategori2">Kategori 2 (Sub-Kelompok)</label>
                                <input type="text" class="form-control" id="addKategori2" name="kategori_2" placeholder="Contoh: Pek. Rangka Baja Ringan">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="addSatuan">Satuan</label>
                                <input type="text" class="form-control" id="addSatuan" name="satuan" placeholder="Contoh: M2, M3, Psg">
                            </div>
                            <div class="form-group">
                                <label for="addUraian">Uraian Pekerjaan <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="addUraian" name="uraian" rows="2" placeholder="Uraian pekerjaan..." required></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="addKontrakVolume">Volume (Kontrak)</label>
                                        <input type="number" step="any" class="form-control calc-trigger" id="addKontrakVolume" name="kontrak_volume" value="0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="addKontrakHargaSatuan">Harga Satuan (Kontrak)</label>
                                        <input type="number" step="any" class="form-control calc-trigger" id="addKontrakHargaSatuan" name="kontrak_harga_satuan" value="0">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="addMcNolVolume">Volume (MC Nol)</label>
                                        <input type="number" step="any" class="form-control calc-trigger" id="addMcNolVolume" name="mc_nol_volume" value="0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="addBobot">Bobot (%)</label>
                                        <input type="number" step="any" class="form-control" id="addBobot" name="bobot_persen" value="0">
                                    </div>
                                </div>
                            </div>
                            <div class="row pt-2 border-top">
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label class="small text-muted mb-1">Jumlah Harga (Kontrak)</label>
                                        <input type="text" class="form-control bg-light font-weight-bold" id="addKontrakJumlahHarga" readonly value="Rp 0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label class="small text-muted mb-1">Jumlah Harga (MC Nol)</label>
                                        <input type="text" class="form-control bg-light font-weight-bold" id="addMcNolJumlahHarga" readonly value="Rp 0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Edit -->
<?php if ($canEdit): ?>
<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mb-0"><i class="fas fa-edit mr-1"></i> Ubah Item RAB</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEdit" action="" method="post">
                <?= csrf_field(); ?>
                <input type="hidden" id="editSekolahNpsn" name="sekolah_npsn" value="<?= esc($sekolah['npsn']); ?>">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editPaketId">Paket <span class="text-danger">*</span></label>
                                <select class="form-control" id="editPaketId" name="paket_id" required>
                                    <option value="">-- Pilih Paket --</option>
                                    <?php foreach ($pakets as $paket): ?>
                                        <option value="<?= esc((string) $paket['id']); ?>"><?= esc((string) $paket['nama_paket']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="editPekerjaanUtama">Pekerjaan Utama</label>
                                <input type="text" class="form-control" id="editPekerjaanUtama" name="pekerjaan_utama">
                            </div>
                            <div class="form-group">
                                <label for="editGedung">Pekerjaan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editGedung" name="gedung" required>
                            </div>
                            <div class="form-group">
                                <label for="editKategori1">Kategori 1 (Romawi)</label>
                                <input type="text" class="form-control" id="editKategori1" name="kategori_1">
                            </div>
                            <div class="form-group">
                                <label for="editKategori2">Kategori 2 (Sub-Kelompok)</label>
                                <input type="text" class="form-control" id="editKategori2" name="kategori_2">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editSatuan">Satuan</label>
                                <input type="text" class="form-control" id="editSatuan" name="satuan">
                            </div>
                            <div class="form-group">
                                <label for="editUraian">Uraian Pekerjaan <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="editUraian" name="uraian" rows="2" required></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="editKontrakVolume">Volume (Kontrak)</label>
                                        <input type="number" step="any" class="form-control calc-trigger" id="editKontrakVolume" name="kontrak_volume">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="editKontrakHargaSatuan">Harga Satuan (Kontrak)</label>
                                        <input type="number" step="any" class="form-control calc-trigger" id="editKontrakHargaSatuan" name="kontrak_harga_satuan">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="editMcNolVolume">Volume (MC Nol)</label>
                                        <input type="number" step="any" class="form-control calc-trigger" id="editMcNolVolume" name="mc_nol_volume">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="editBobot">Bobot (%)</label>
                                        <input type="number" step="any" class="form-control" id="editBobot" name="bobot_persen">
                                    </div>
                                </div>
                            </div>
                            <div class="row pt-2 border-top">
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label class="small text-muted mb-1">Jumlah Harga (Kontrak)</label>
                                        <input type="text" class="form-control bg-light font-weight-bold" id="editKontrakJumlahHarga" readonly value="Rp 0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label class="small text-muted mb-1">Jumlah Harga (MC Nol)</label>
                                        <input type="text" class="form-control bg-light font-weight-bold" id="editMcNolJumlahHarga" readonly value="Rp 0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script>
$(document).ready(function() {
    // File upload label update
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });

    // Form auto-calculator helper
    function formatRupiah(num) {
        return 'Rp ' + Number(num).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    function formatVolume(num) {
        return Number(num).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    $('.calc-trigger').on('input change', function() {
        const modal = $(this).closest('.modal');
        const isEdit = modal.attr('id') === 'modalEdit';
        const prefix = isEdit ? 'edit' : 'add';

        const volume = parseFloat(modal.find(`#${prefix}KontrakVolume`).val()) || 0;
        const price = parseFloat(modal.find(`#${prefix}KontrakHargaSatuan`).val()) || 0;
        const mcVol = parseFloat(modal.find(`#${prefix}McNolVolume`).val()) || 0;

        const totalKontrak = volume * price;
        const totalMc = mcVol * price;

        modal.find(`#${prefix}KontrakJumlahHarga`).val(formatRupiah(totalKontrak));
        modal.find(`#${prefix}McNolJumlahHarga`).val(formatRupiah(totalMc));
    });

    // DataTable init
    var table = $('#tableRabDetail').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= site_url('admin/laporan/rab-gedung/data'); ?>',
            type: 'GET',
            data: function(d) {
                d.sekolah_npsn = '<?= $sekolah['npsn']; ?>';
                d.gedung = $('#filterGedung').val();
                d.paket_id = $('#filterPaket').val();
            }
        },
        columns: [
            {
                data: null,
                sortable: false,
                searchable: false,
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                },
                className: 'text-center align-middle'
            },
            { data: 'gedung_escaped', className: 'align-middle' },
            { 
                data: 'uraian_escaped',
                className: 'align-middle',
                render: function(data, type, row) {
                    let html = '<div><strong>' + data + '</strong></div>';
                    if (row.nama_paket_escaped) {
                        html += ' <span class="badge badge-success"><i class="fas fa-box mr-1"></i>' + row.nama_paket_escaped + '</span>';
                    }
                    if (row.kategori_1_escaped) {
                        html += ' <span class="badge badge-info">' + row.kategori_1_escaped + '</span>';
                    }
                    if (row.kategori_2_escaped) {
                        html += ' <span class="badge badge-secondary">' + row.kategori_2_escaped + '</span>';
                    }
                    return html;
                }
            },
            { data: 'satuan', className: 'text-center align-middle' },
            // Kontrak
            { data: 'kontrak_volume_formatted', className: 'text-right align-middle' },
            { data: 'kontrak_harga_satuan_formatted', className: 'text-right align-middle' },
            { data: 'kontrak_jumlah_harga_formatted', className: 'text-right align-middle font-weight-bold' },
            // MC Nol
            { data: 'mc_nol_volume_formatted', className: 'text-right align-middle' },
            { data: 'mc_nol_jumlah_harga_formatted', className: 'text-right align-middle font-weight-bold' },
            // Tambah
            { data: 'tambah_volume_formatted', className: 'text-right align-middle' },
            { data: 'tambah_jumlah_harga_formatted', className: 'text-right align-middle font-weight-bold' },
            // Kurang
            { data: 'kurang_volume_formatted', className: 'text-right align-middle' },
            { data: 'kurang_jumlah_harga_formatted', className: 'text-right align-middle font-weight-bold' },
            // Aksi
            {
                data: null,
                sortable: false,
                searchable: false,
                className: 'text-center align-middle',
                render: function(data, type, row) {
                    let buttons = '';
                    
                    <?php if ($canEdit): ?>
                    buttons += '<button class="btn btn-warning btn-xs btn-edit mr-1" data-id="' + row.id + '" title="Edit"><i class="fas fa-edit"></i></button>';
                    <?php endif; ?>

                    <?php if ($canDelete): ?>
                    buttons += '<button class="btn btn-danger btn-xs btn-delete" data-id="' + row.id + '" title="Hapus"><i class="fas fa-trash"></i></button>';
                    <?php endif; ?>
                    
                    return buttons || '-';
                }
            }
        ],
        order: [[1, 'asc'], [2, 'asc']],
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        drawCallback: function(settings) {
            var api = this.api();
            var json = api.ajax.json();
            
            if (json && json.sums) {
                $('#sumTotalKontrakVol').html(formatVolume(json.sums.kontrak_volume));
                $('#sumTotalKontrak').html(formatRupiah(json.sums.kontrak_jumlah_harga));
                $('#sumTotalMcNolVol').html(formatVolume(json.sums.mc_nol_volume));
                $('#sumTotalMcNol').html(formatRupiah(json.sums.mc_nol_jumlah_harga));
                $('#sumTotalTambahVol').html(formatVolume(json.sums.tambah_volume));
                $('#sumTotalTambah').html(formatRupiah(json.sums.tambah_jumlah_harga));
                $('#sumTotalKurangVol').html(formatVolume(json.sums.kurang_volume));
                $('#sumTotalKurang').html(formatRupiah(json.sums.kurang_jumlah_harga));
            }
        },
        language: {
            processing: 'Memproses...',
            zeroRecords: 'Tidak ada data',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty: 'Menampilkan 0 - 0 dari 0 data',
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            paginate: {
                first: 'Pertama',
                last: 'Terakhir',
                next: 'Berikutnya',
                previous: 'Sebelumnya'
            }
        }
    });

    // Filter triggers reload
    $('#filterGedung, #filterPaket').on('change', function() {
        table.ajax.reload();
    });

    // Populate Edit Modal
    $('#tableRabDetail').on('click', '.btn-edit', function() {
        const id = $(this).data('id');
        const rowData = table.row($(this).closest('tr')).data();

        $('#formEdit').attr('action', '<?= site_url('admin/laporan/rab-gedung/'); ?>' + id + '/ubah');
        
        $('#editSekolahNpsn').val(rowData.sekolah_npsn);
        $('#editPaketId').val(rowData.paket_id);
        $('#editPekerjaanUtama').val(rowData.pekerjaan_utama);
        $('#editGedung').val(rowData.gedung);
        $('#editKategori1').val(rowData.kategori_1);
        $('#editKategori2').val(rowData.kategori_2);
        $('#editSatuan').val(rowData.satuan);
        $('#editUraian').val(rowData.uraian);
        
        $('#editKontrakVolume').val(rowData.kontrak_volume);
        $('#editKontrakHargaSatuan').val(rowData.kontrak_harga_satuan);
        $('#editMcNolVolume').val(rowData.mc_nol_volume);
        $('#editBobot').val(rowData.bobot_persen);
        $('#editPrestasi').val(rowData.prestasi_persen);

        // Trigger dynamic total calculation inside the edit modal
        $('#modalEdit .calc-trigger').first().trigger('change');

        $('#modalEdit').modal('show');
    });

    // Delete confirmation
    $('#tableRabDetail').on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        
        if (window.Swal) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data item RAB ini akan dihapus permanen dari sekolah ini!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    performDelete(id);
                }
            });
        } else {
            if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                performDelete(id);
            }
        }
    });

    function performDelete(id) {
        $.ajax({
            url: '<?= site_url('admin/laporan/rab-gedung/'); ?>' + id + '/hapus',
            type: 'POST',
            dataType: 'json',
            data: {
                <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            },
            success: function(response) {
                if (response.success) {
                    if (window.Swal) {
                        Swal.fire('Terhapus!', response.message, 'success');
                    } else {
                        alert(response.message);
                    }
                    table.ajax.reload();
                    // Optional reload page to recalculate dashboard sums
                    setTimeout(function() {
                        location.reload();
                    }, 1200);
                } else {
                    if (window.Swal) {
                        Swal.fire('Gagal!', response.message, 'error');
                    } else {
                        alert(response.message);
                    }
                }
            },
            error: function() {
                if (window.Swal) {
                    Swal.fire('Error!', 'Terjadi kesalahan sistem saat menghapus data.', 'error');
                } else {
                    alert('Terjadi kesalahan sistem saat menghapus data.');
                }
            }
        });
    }
});
</script>
<?= $this->endSection(); ?>
