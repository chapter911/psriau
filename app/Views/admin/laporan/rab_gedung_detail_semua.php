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
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
        background-color: #007bff;
        border-color: #0069d9;
        color: #fff;
        font-size: 0.78rem;
        padding: 2px 6px;
    }
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove {
        color: #fff;
        margin-right: 4px;
    }
    .filter-quick-action {
        font-size: 0.72rem;
        cursor: pointer;
        user-select: none;
    }
</style>
<?php
$sekolahs    = $sekolahs ?? [];
$gedungs     = $gedungs ?? [];
$kategori_1s = $kategori_1s ?? [];
$kategori_2s = $kategori_2s ?? [];
$pakets      = $pakets ?? [];

$totalKontrak = $total_kontrak ?? 0;
$totalMcNol   = $total_mcnol ?? 0;
$totalTambah  = $total_tambah ?? 0;
$totalKurang  = $total_kurang ?? 0;
?>

<!-- Header & Back Button -->
<div class="d-flex align-items-center mb-3">
    <a href="<?= site_url('admin/laporan/rab-gedung'); ?>" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar Sekolah
    </a>
</div>

<!-- Main Title & Filter Card -->
<div class="card card-outline card-primary mb-4">
    <div class="card-header d-flex align-items-center py-2">
        <h3 class="card-title mb-0 font-weight-bold text-primary">
            <i class="fas fa-list-alt mr-1"></i> Detail Seluruh Data RAB Gedung
        </h3>
        <button type="button" class="btn btn-outline-secondary btn-sm ml-auto" id="btnResetAllFilters" title="Reset Semua Filter">
            <i class="fas fa-undo mr-1"></i> Reset Semua Filter
        </button>
    </div>
    <div class="card-body py-3">
        <!-- Multi-Select Checklist Filters Row -->
        <div class="row">
            <!-- Filter Sekolah -->
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="filterSekolah" class="mb-0 text-muted small font-weight-bold">
                        <i class="fas fa-school mr-1"></i>Sekolah (Multi-Select)
                    </label>
                    <span class="small">
                        <a href="#" class="btn-select-all filter-quick-action text-primary mr-1" data-target="#filterSekolah">[Pilih Semua]</a>
                        <a href="#" class="btn-reset-filter filter-quick-action text-danger" data-target="#filterSekolah">[Reset]</a>
                    </span>
                </div>
                <select id="filterSekolah" class="form-control select2-filter" multiple="multiple" data-placeholder="Semua Sekolah...">
                    <?php foreach ($sekolahs as $sch): ?>
                        <option value="<?= esc((string) $sch['npsn']); ?>"><?= esc((string) $sch['nama']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Paket -->
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="filterPaket" class="mb-0 text-muted small font-weight-bold">
                        <i class="fas fa-box mr-1"></i>Paket (Multi-Select)
                    </label>
                    <span class="small">
                        <a href="#" class="btn-select-all filter-quick-action text-primary mr-1" data-target="#filterPaket">[Pilih Semua]</a>
                        <a href="#" class="btn-reset-filter filter-quick-action text-danger" data-target="#filterPaket">[Reset]</a>
                    </span>
                </div>
                <select id="filterPaket" class="form-control select2-filter" multiple="multiple" data-placeholder="Semua Paket...">
                    <?php foreach ($pakets as $pkt): ?>
                        <option value="<?= esc((string) $pkt['id']); ?>"><?= esc((string) $pkt['nama_paket']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Pekerjaan -->
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="filterGedung" class="mb-0 text-muted small font-weight-bold">
                        <i class="fas fa-building mr-1"></i>Pekerjaan (Multi-Select)
                    </label>
                    <span class="small">
                        <a href="#" class="btn-select-all filter-quick-action text-primary mr-1" data-target="#filterGedung">[Pilih Semua]</a>
                        <a href="#" class="btn-reset-filter filter-quick-action text-danger" data-target="#filterGedung">[Reset]</a>
                    </span>
                </div>
                <select id="filterGedung" class="form-control select2-filter" multiple="multiple" data-placeholder="Semua Pekerjaan...">
                    <?php foreach ($gedungs as $gedung): ?>
                        <option value="<?= esc($gedung); ?>"><?= esc($gedung); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Kategori -->
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="filterKategori" class="mb-0 text-muted small font-weight-bold">
                        <i class="fas fa-folder mr-1"></i>Kategori (Multi-Select)
                    </label>
                    <span class="small">
                        <a href="#" class="btn-select-all filter-quick-action text-primary mr-1" data-target="#filterKategori">[Pilih Semua]</a>
                        <a href="#" class="btn-reset-filter filter-quick-action text-danger" data-target="#filterKategori">[Reset]</a>
                    </span>
                </div>
                <select id="filterKategori" class="form-control select2-filter" multiple="multiple" data-placeholder="Semua Kategori...">
                    <?php foreach ($kategori_1s as $kat): ?>
                        <option value="<?= esc($kat); ?>"><?= esc($kat); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Sub-Kategori -->
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="filterSubKategori" class="mb-0 text-muted small font-weight-bold">
                        <i class="fas fa-folder-open mr-1"></i>Sub-Kategori (Multi-Select)
                    </label>
                    <span class="small">
                        <a href="#" class="btn-select-all filter-quick-action text-primary mr-1" data-target="#filterSubKategori">[Pilih Semua]</a>
                        <a href="#" class="btn-reset-filter filter-quick-action text-danger" data-target="#filterSubKategori">[Reset]</a>
                    </span>
                </div>
                <select id="filterSubKategori" class="form-control select2-filter" multiple="multiple" data-placeholder="Semua Sub-Kategori...">
                    <?php foreach ($kategori_2s as $sub): ?>
                        <option value="<?= esc($sub); ?>"><?= esc($sub); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Uraian Pekerjaan -->
            <div class="col-lg-4 col-md-12 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="filterUraian" class="mb-0 text-muted small font-weight-bold">
                        <i class="fas fa-tasks mr-1"></i>Uraian Pekerjaan (Multi-Select)
                    </label>
                    <span class="small">
                        <a href="#" class="btn-select-all filter-quick-action text-primary mr-1" data-target="#filterUraian">[Pilih Semua]</a>
                        <a href="#" class="btn-reset-filter filter-quick-action text-danger" data-target="#filterUraian">[Reset]</a>
                    </span>
                </div>
                <select id="filterUraian" class="form-control select2-filter" multiple="multiple" data-placeholder="Semua Uraian Pekerjaan...">
                    <?php foreach ($uraians as $ur): ?>
                        <option value="<?= esc($ur); ?>"><?= esc($ur); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Summary Dashboard Cards -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h4 class="font-weight-bold text-truncate" id="cardTotalKontrak" style="font-size: 1.25rem;">Rp <?= number_format($totalKontrak, 0, ',', '.'); ?></h4>
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
                <h4 class="font-weight-bold text-truncate" id="cardTotalMcNol" style="font-size: 1.25rem;">Rp <?= number_format($totalMcNol, 0, ',', '.'); ?></h4>
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
                <h4 class="font-weight-bold text-truncate" id="cardTotalTambah" style="font-size: 1.25rem;">Rp <?= number_format($totalTambah, 0, ',', '.'); ?></h4>
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
                <h4 class="font-weight-bold text-truncate text-dark" id="cardTotalKurang" style="font-size: 1.25rem;">Rp <?= number_format($totalKurang, 0, ',', '.'); ?></h4>
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
        <h3 class="card-title mb-0 font-weight-bold"><i class="fas fa-table mr-1"></i> Rincian Item Pekerjaan Seluruh Data</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive table-sticky-container">
            <table class="table table-bordered table-striped table-hover w-100 table-sm" id="tableRabDetailSemua" style="font-size: 0.83rem;">
                <thead class="thead-light">
                    <tr>
                        <th rowspan="2" class="align-middle text-center" style="width: 30px;">No</th>
                        <th rowspan="2" class="align-middle" style="min-width: 180px;">Nama Sekolah</th>
                        <th rowspan="2" class="align-middle" style="min-width: 130px;">NPSN / NSM</th>
                        <th rowspan="2" class="align-middle" style="min-width: 130px;">Kecamatan</th>
                        <th rowspan="2" class="align-middle" style="min-width: 130px;">Kabupaten</th>
                        <th rowspan="2" class="align-middle text-center" style="min-width: 120px;">Paket</th>
                        <th rowspan="2" class="align-middle" style="min-width: 120px;">Pekerjaan</th>
                        <th rowspan="2" class="align-middle" style="min-width: 150px;">Kategori</th>
                        <th rowspan="2" class="align-middle" style="min-width: 150px;">Sub-Kategori</th>
                        <th rowspan="2" class="align-middle" style="min-width: 200px;">Uraian Pekerjaan</th>
                        <th rowspan="2" class="align-middle text-center" style="width: 40px;">Sat</th>
                        <th colspan="3" class="text-center py-1">KONTRAK</th>
                        <th colspan="2" class="text-center py-1">MC NOL (100%)</th>
                        <th colspan="2" class="text-center py-1">TAMBAH</th>
                        <th colspan="2" class="text-center py-1">KURANG</th>
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
                        <td colspan="11" class="text-right align-middle">TOTAL:</td>
                        <td class="text-right align-middle" id="sumTotalKontrakVol">-</td>
                        <td class="text-right align-middle">-</td>
                        <td class="text-right align-middle" id="sumTotalKontrak">Rp 0</td>
                        <td class="text-right align-middle" id="sumTotalMcNolVol">-</td>
                        <td class="text-right align-middle" id="sumTotalMcNol">Rp 0</td>
                        <td class="text-right align-middle" id="sumTotalTambahVol">-</td>
                        <td class="text-right align-middle" id="sumTotalTambah">Rp 0</td>
                        <td class="text-right align-middle" id="sumTotalKurangVol">-</td>
                        <td class="text-right align-middle" id="sumTotalKurang">Rp 0</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script>
$(document).ready(function() {
    function formatRupiah(num) {
        return 'Rp ' + Number(num).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    function formatVolume(num) {
        return Number(num).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Initialize Select2 Multi-select for filters
    if ($.fn.select2) {
        $('.select2-filter').select2({
            theme: 'bootstrap4',
            width: '100%',
            allowClear: true,
            closeOnSelect: false
        });
    }

    // Quick action: Select All options for a target filter
    $('.btn-select-all').on('click', function(e) {
        e.preventDefault();
        var target = $(this).data('target');
        $(target + ' option').prop('selected', true);
        $(target).trigger('change');
    });

    // Quick action: Reset/Clear target filter
    $('.btn-reset-filter').on('click', function(e) {
        e.preventDefault();
        var target = $(this).data('target');
        $(target).val(null).trigger('change');
    });

    // Reset All Filters
    $('#btnResetAllFilters').on('click', function() {
        $('.select2-filter').val(null).trigger('change');
    });

    var table = $('#tableRabDetailSemua').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= site_url('admin/laporan/rab-gedung/data'); ?>',
            type: 'GET',
            data: function(d) {
                d.sekolah_npsn = $('#filterSekolah').val();
                d.paket_id     = $('#filterPaket').val();
                d.gedung        = $('#filterGedung').val();
                d.kategori_1    = $('#filterKategori').val();
                d.kategori_2    = $('#filterSubKategori').val();
                d.uraian        = $('#filterUraian').val();
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
            { data: 'nama_sekolah_escaped', className: 'align-middle font-weight-bold' },
            { data: 'npsn_nsm_escaped', className: 'align-middle text-nowrap' },
            { data: 'kecamatan_escaped', className: 'align-middle' },
            { data: 'kabupaten_escaped', className: 'align-middle' },
            { 
                data: 'nama_paket_escaped', 
                className: 'align-middle text-center',
                render: function(data) {
                    if (data) {
                        return '<span class="badge badge-success"><i class="fas fa-box mr-1"></i>' + data + '</span>';
                    }
                    return '<span class="badge badge-warning text-dark"><i class="fas fa-exclamation-triangle mr-1"></i> -</span>';
                }
            },
            { data: 'gedung_escaped', className: 'align-middle' },
            { data: 'kategori_1_escaped', className: 'align-middle' },
            { data: 'kategori_2_escaped', className: 'align-middle' },
            { 
                data: 'uraian_escaped',
                className: 'align-middle',
                render: function(data) {
                    return '<strong>' + data + '</strong>';
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
            { data: 'kurang_jumlah_harga_formatted', className: 'text-right align-middle font-weight-bold' }
        ],
        order: [[1, 'asc'], [6, 'asc'], [7, 'asc'], [8, 'asc']],
        lengthMenu: [[10, 25, 50, 100, 250], [10, 25, 50, 100, 250]],
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

                // Update summary card badges
                $('#cardTotalKontrak').html(formatRupiah(json.sums.kontrak_jumlah_harga));
                $('#cardTotalMcNol').html(formatRupiah(json.sums.mc_nol_jumlah_harga));
                $('#cardTotalTambah').html(formatRupiah(json.sums.tambah_jumlah_harga));
                $('#cardTotalKurang').html(formatRupiah(json.sums.kurang_jumlah_harga));
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
    $('#filterSekolah, #filterPaket, #filterGedung, #filterKategori, #filterSubKategori, #filterUraian').on('change', function() {
        table.ajax.reload();
    });
});
</script>
<?= $this->endSection(); ?>
