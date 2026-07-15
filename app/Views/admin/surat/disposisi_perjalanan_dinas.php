<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
    $canEdit = (bool) ($can_edit ?? false);
    $pegawaiOptions = $pegawai_options ?? [];
    $kabupatenOptions = $kabupaten_options ?? [];
    $transportasiOptions = $transportasi_options ?? [];
?>
<style>
    #tableDisposisi thead th {
        vertical-align: middle !important;
        text-align: center;
        padding: 10px !important;
        font-size: 13.5px;
    }

    #tableDisposisi tbody td {
        vertical-align: middle !important;
        padding: 10px !important;
        font-size: 13.5px;
    }

    .doc-btn-group {
        display: inline-flex;
        flex-wrap: wrap;
        gap: 5px;
        justify-content: center;
    }

    .doc-btn-group .btn {
        padding: 4px 8px;
        font-size: 12px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
</style>

<!-- Flash Messages -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle mr-1"></i> <?= session()->getFlashdata('success'); ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle mr-1"></i> <?= session()->getFlashdata('error'); ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title mb-0">Daftar Disposisi Perjalanan Dinas</h3>
        <?php if ($canEdit): ?>
            <div class="card-tools ml-auto">
                <button type="button" class="btn btn-primary btn-sm" onclick="openCreateModal()">
                    <i class="fas fa-plus mr-1"></i> Buat Disposisi
                </button>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        
        <!-- Filter Card -->
        <div class="card card-outline card-secondary mb-3">
            <div class="card-header py-2">
                <h3 class="card-title mb-0">Filter Disposisi Perjalanan Dinas</h3>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="filter_start_date" class="font-weight-bold mb-1">Tanggal Mulai</label>
                        <input type="date" class="form-control form-control-sm" id="filter_start_date" value="<?= date('Y-m-01'); ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="filter_end_date" class="font-weight-bold mb-1">Tanggal Selesai</label>
                        <input type="date" class="form-control form-control-sm" id="filter_end_date" value="<?= date('Y-m-t'); ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="filter_kota" class="font-weight-bold mb-1">Kota Tujuan</label>
                        <select class="form-control form-control-sm select2-filter" id="filter_kota" data-placeholder="Semua Kota/Kabupaten" style="width: 100%;">
                            <option value=""></option>
                            <?php foreach ($kabupatenOptions ?? [] as $kota): ?>
                                <option value="<?= esc($kota); ?>"><?= esc($kota); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="filter_pelaksana" class="font-weight-bold mb-1">Pelaksana</label>
                        <select class="form-control form-control-sm select2-filter" id="filter_pelaksana" data-placeholder="Semua Pelaksana" style="width: 100%;">
                            <option value=""></option>
                            <?php foreach ($pegawaiOptions ?? [] as $peg): ?>
                                <option value="<?= (int) ($peg['id'] ?? 0); ?>"><?= esc($peg['nama'] ?? ''); ?><?= !empty($peg['nip']) ? ' - NIP ' . esc($peg['nip']) : ''; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <small class="text-muted">Data akan diperbarui secara otomatis saat filter diubah.</small>
                    <div class="d-flex align-items-center" style="gap:10px;">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-reset-filter">Reset Filter</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="tableDisposisi" class="table table-bordered table-striped table-hover w-100">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Pelaksana SPPD</th>
                        <th style="width: 150px;">Periode</th>
                        <th>Tujuan</th>
                        <th>Transportasi</th>
                        <th>Perihal</th>
                        <th style="width: 220px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Form Disposisi -->
<div class="modal fade" id="modalDisposisi" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Tambah Disposisi Perjalanan Dinas</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <form method="POST" action="<?= site_url('admin/surat/perjalanan-dinas/disposisi/buat'); ?>" id="formDisposisi">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    
                    <!-- Pelaksana SPPD Group -->
                    <div class="card card-outline card-secondary mb-3">
                        <div class="card-header py-2">
                            <h6 class="card-title mb-0 text-bold"><i class="fas fa-users mr-1"></i> Pelaksana SPPD (Maksimal 5 Orang)</h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="form-group mb-0">
                                <label for="pelaksana_ids" class="mb-1">Pilih Pelaksana SPPD <span class="text-danger">*</span></label>
                                <select class="form-control select2-pegawai" name="pelaksana_id[]" id="pelaksana_ids" multiple="multiple" required style="width: 100%;">
                                    <?php foreach ($pegawaiOptions as $p): ?>
                                        <option value="<?= $p['id']; ?>"><?= esc($p['display_label']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Pilih satu atau beberapa pegawai pelaksana SPPD (maksimal 5 orang).</small>
                            </div>
                        </div>
                    </div>

                    <!-- Periode & Perjalanan Group -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="periode_mulai">Periode Mulai <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="periode_mulai" id="periode_mulai" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="periode_selesai">Periode Selesai <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="periode_selesai" id="periode_selesai" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="kota_tujuan_select">Kota/Kab. Tujuan Perjalanan Dinas <span class="text-danger">*</span></label>
                        <select class="form-control select2-kabupaten" name="kota_tujuan" id="kota_tujuan_select" required style="width: 100%;">
                            <option value="">-- Pilih Kota / Kabupaten --</option>
                            <?php foreach ($kabupatenOptions as $kab): ?>
                                <option value="<?= esc($kab); ?>"><?= esc($kab); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="transportasi_select">Transportasi <span class="text-danger">*</span></label>
                        <select class="form-control" name="transportasi[]" id="transportasi_select" multiple="multiple" required style="width: 100%;">
                            <?php foreach ($transportasiOptions as $t): ?>
                                <option value="<?= esc($t['nama_transportasi']); ?>"><?= esc($t['nama_transportasi']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Pilih satu atau beberapa moda transportasi.</small>
                    </div>

                    <div class="form-group">
                        <label for="tujuan_textarea">Tujuan Perjalanan Dinas <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="tujuan" id="tujuan_textarea" rows="3" placeholder="Contoh: Rapat koordinasi dinas..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="perihal">Perihal <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="perihal" id="perihal" rows="3" placeholder="Contoh: Menghadiri Rapat Koordinasi..." required></textarea>
                    </div>

                    <!-- Signatures Group -->
                    <div class="card card-outline card-info mt-3 mb-0">
                        <div class="card-header py-2">
                            <h6 class="card-title mb-0 text-bold"><i class="fas fa-file-signature mr-1"></i> Tanda Tangan Dokumen</h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="form-group mb-3">
                                <label for="menyetujui_pegawai_id" class="mb-1">Menyetujui (Pejabat Pembuat Komitmen) <span class="text-danger">*</span></label>
                                <select class="form-control select2-pegawai" name="menyetujui_pegawai_id" id="menyetujui_pegawai_id" required style="width: 100%;">
                                    <option value="">-- Pilih Pejabat PPK --</option>
                                    <?php foreach ($pegawaiOptions as $p): ?>
                                        <option value="<?= $p['id']; ?>" <?= ($p['id'] == 2) ? 'selected' : ''; ?>><?= esc($p['display_label']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group mb-0">
                                <label for="diketahui_pegawai_id" class="mb-1">Diketahui (Kepala Satuan Kerja) <span class="text-danger">*</span></label>
                                <select class="form-control select2-pegawai" name="diketahui_pegawai_id" id="diketahui_pegawai_id" required style="width: 100%;">
                                    <option value="">-- Pilih Kepala Satker --</option>
                                    <?php foreach ($pegawaiOptions as $p): ?>
                                        <option value="<?= $p['id']; ?>" <?= ($p['id'] == 1) ? 'selected' : ''; ?>><?= esc($p['display_label']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmit">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus data Disposisi Perjalanan Dinas ini?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <a href="#" class="btn btn-danger" id="btnConfirmDelete">Hapus</a>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script>
function openCreateModal() {
    // Reset Form
    document.getElementById('formDisposisi').reset();
    
    // Reset Select2 fields to default
    $('.select2-pegawai').val(null).trigger('change');
    
    // Set default PPK (ID 2) and Satker (ID 1)
    $('#menyetujui_pegawai_id').val('2').trigger('change');
    $('#diketahui_pegawai_id').val('1').trigger('change');
    
    // Set action URL
    var actionUrl = '<?= site_url('admin/surat/perjalanan-dinas/disposisi/buat'); ?>';
    document.getElementById('formDisposisi').setAttribute('action', actionUrl);
    
    // Update Modal Title
    $('#modalDisposisi .modal-title').text('Tambah Disposisi Perjalanan Dinas');
    
    // Clear kota_tujuan select2
    $('#kota_tujuan_select').val(null).trigger('change');
    $('#transportasi_select').val(null).trigger('change');
    document.getElementById('tujuan_textarea').value = '';

    // Clear min attribute for date inputs
    $('#periode_selesai').removeAttr('min');
    
    // Show Modal
    $('#modalDisposisi').modal('show');
}

function openEditModal(data) {
    // Reset Form
    document.getElementById('formDisposisi').reset();
    $('.select2-pegawai').val(null).trigger('change');
    
    // Set action URL
    var actionUrl = '<?= site_url('admin/surat/perjalanan-dinas/disposisi'); ?>/' + data.id + '/ubah';
    document.getElementById('formDisposisi').setAttribute('action', actionUrl);
    
    // Update Modal Title
    $('#modalDisposisi .modal-title').text('Ubah Disposisi Perjalanan Dinas');
    
    // Populate Fields
    document.getElementById('periode_mulai').value = data.periode_mulai;
    document.getElementById('periode_selesai').value = data.periode_selesai;
    $('#kota_tujuan_select').val(data.kota_tujuan).trigger('change');
    document.getElementById('tujuan_textarea').value = data.tujuan;
    document.getElementById('perihal').value = data.perihal;
    
    // Set min attribute based on start date
    $('#periode_selesai').attr('min', data.periode_mulai);
    
    // Populate Transportasi multiselect
    var transportModes = [];
    if (data.transportasi) {
        transportModes = data.transportasi.split(',').map(function(item) {
            return item.trim();
        });
    }
    $('#transportasi_select').val(transportModes).trigger('change');
    
    // Set Signatures
    $('#menyetujui_pegawai_id').val(data.menyetujui_id).trigger('change');
    $('#diketahui_pegawai_id').val(data.diketahui_id).trigger('change');
    
    // Set Pelaksana list
    var pelaksana = data.pelaksana_raw || [];
    var selectedIds = [];
    for (var i = 0; i < pelaksana.length; i++) {
        selectedIds.push(pelaksana[i].id);
    }
    $('#pelaksana_ids').val(selectedIds).trigger('change');
    
    // Show Modal
    $('#modalDisposisi').modal('show');
}

$(document).ready(function() {
    // Sync start and end date logic
    $('#periode_mulai').on('change', function() {
        var startVal = $(this).val();
        $('#periode_selesai').attr('min', startVal);
        
        var endVal = $('#periode_selesai').val();
        if (!endVal || endVal < startVal) {
            $('#periode_selesai').val(startVal);
        }
    });

    $('#periode_selesai').on('change', function() {
        var startVal = $('#periode_mulai').val();
        var endVal = $(this).val();
        if (startVal && endVal && endVal < startVal) {
            $(this).val(startVal);
        }
    });

    // Initialize Select2 for employee options
    $('#pelaksana_ids').select2({
        theme: 'bootstrap4',
        dropdownParent: $('#modalDisposisi'),
        placeholder: '-- Pilih Pelaksana SPPD --',
        maximumSelectionLength: 5
    });

    $('#kota_tujuan_select').select2({
        theme: 'bootstrap4',
        dropdownParent: $('#modalDisposisi'),
        placeholder: '-- Pilih Kota / Kabupaten --'
    });

    $('#transportasi_select').select2({
        theme: 'bootstrap4',
        dropdownParent: $('#modalDisposisi'),
        placeholder: '-- Pilih Transportasi --'
    });

    // Initialize select2 for signatures
    $('#menyetujui_pegawai_id, #diketahui_pegawai_id').select2({
        theme: 'bootstrap4',
        dropdownParent: $('#modalDisposisi')
    });

    var $filterStartDate = $('#filter_start_date');
    var $filterEndDate = $('#filter_end_date');
    var $filterKota = $('#filter_kota');
    var $filterPelaksana = $('#filter_pelaksana');

    $('#filter_kota').select2({
        theme: 'bootstrap4',
        placeholder: 'Semua Kota/Kabupaten',
        allowClear: true
    });

    $('#filter_pelaksana').select2({
        theme: 'bootstrap4',
        placeholder: 'Semua Pelaksana',
        allowClear: true
    });

    // Initialize DataTable
    var table = $('#tableDisposisi').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= site_url('admin/surat/perjalanan-dinas/disposisi'); ?>',
            type: 'GET',
            data: function (d) {
                d.filter_start_date = $filterStartDate.val();
                d.filter_end_date = $filterEndDate.val();
                d.filter_kota = $filterKota.val();
                d.filter_pelaksana = $filterPelaksana.val();
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
                className: 'text-center'
            },
            { data: 'pelaksana_html', sortable: false },
            { data: 'periode_html', className: 'text-center' },
            {
                data: null,
                render: function(data, type, row) {
                    var display = '';
                    if (row && row.kota_tujuan) {
                        display += '<strong>' + $('<div>').text(row.kota_tujuan).html() + '</strong>';
                    }
                    if (row && row.tujuan) {
                        display += (display ? '<br><small class="text-muted">' : '') + $('<div>').text(row.tujuan).html() + (display ? '</small>' : '');
                    }
                    return display || '-';
                }
            },
            { data: 'transportasi', className: 'text-center' },
            { data: 'perihal' },
            { data: 'action_html', className: 'text-center', sortable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
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

    // Handle Edit Button Click
    $(document).on('click', '.btn-edit', function() {
        var rowData = table.row($(this).closest('tr')).data();
        if (rowData) {
            openEditModal(rowData);
        }
    });

    // Handle Delete Button Click
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        $('#btnConfirmDelete').attr('href', '<?= site_url('admin/surat/perjalanan-dinas/disposisi'); ?>/' + id + '/hapus');
        $('#deleteModal').modal('show');
    });

    $filterStartDate.on('change', function () { table.ajax.reload(); });
    $filterEndDate.on('change', function () { table.ajax.reload(); });
    $filterKota.on('change', function () { table.ajax.reload(); });
    $filterPelaksana.on('change', function () { table.ajax.reload(); });

    $('#btn-reset-filter').on('click', function () {
        $filterStartDate.val('<?= date('Y-m-01'); ?>');
        $filterEndDate.val('<?= date('Y-m-t'); ?>');
        $filterKota.val(null).trigger('change');
        $filterPelaksana.val(null).trigger('change');
        table.ajax.reload();
    });
});
</script>
<?= $this->endSection(); ?>
