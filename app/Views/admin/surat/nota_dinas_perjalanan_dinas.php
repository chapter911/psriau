<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
    $canAdd         = (bool) ($can_add ?? false);
    $canEdit        = (bool) ($can_edit ?? false);
    $canDelete      = (bool) ($can_delete ?? false);
    $canExport      = (bool) ($can_export ?? false);
    $pegawaiOptions = $pegawai_options ?? [];
    $defaultIsi     = 'Menunjuk Surat dari PT. Toleransi Aceh, KSO PT. Bripona Jaya Abadi Nomor: 35/PT.TA-PT.BJA.KSO/MDRS-R4/X/2026 tanggal 14 Oktober 2026 perihal Permohonan Serah Terima Pertama (PHO), bersama ini dengan hormat kami usulkan pegawai di bawah ini untuk mengikuti kegiatan tersebut:';
?>
<style>
    .tooltip {
        pointer-events: none !important;
    }

    #tableNotaDinas th,
    #tableNotaDinas td,
    .table th,
    .table td {
        white-space: nowrap !important;
        vertical-align: middle !important;
        padding: 8px 10px !important;
        font-size: 13.5px;
    }

    .text-tujuan, .text-isi, .pelaksana-single-line {
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        max-width: 240px !important;
        display: inline-block !important;
        vertical-align: middle !important;
        cursor: pointer;
    }

    .doc-btn-group {
        display: inline-flex !important;
        flex-wrap: nowrap !important;
        white-space: nowrap !important;
        gap: 4px !important;
        justify-content: center !important;
        align-items: center !important;
    }

    .doc-btn-group .btn {
        padding: 4px 8px !important;
        font-size: 12px !important;
        border-radius: 4px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 4px !important;
        white-space: nowrap !important;
        flex-shrink: 0 !important;
        margin: 0 !important;
    }
</style>

<div class="card">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-file-alt text-primary mr-2"></i>Daftar Nota Dinas Perjalanan Dinas</h3>
        <?php if ($canAdd): ?>
            <div class="card-tools ml-auto">
                <button type="button" class="btn btn-primary btn-sm" onclick="openCreateModal()">
                    <i class="fas fa-plus mr-1"></i> Buat Nota Dinas
                </button>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <!-- Filter Card -->
        <div class="card card-outline card-secondary mb-3">
            <div class="card-header py-2">
                <h3 class="card-title mb-0">Filter Nota Dinas Perjalanan Dinas</h3>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="filter_start_date" class="font-weight-bold mb-1">Periode Mulai</label>
                        <input type="date" class="form-control form-control-sm" id="filter_start_date" value="<?= date('Y-01-01'); ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="filter_end_date" class="font-weight-bold mb-1">Periode Selesai</label>
                        <input type="date" class="form-control form-control-sm" id="filter_end_date" value="<?= date('Y-m-t'); ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="filter_pelaksana" class="font-weight-bold mb-1">Pegawai Diusulkan</label>
                        <select class="form-control form-control-sm select2-filter" id="filter_pelaksana" data-placeholder="Semua Pegawai" style="width: 100%;">
                            <option value=""></option>
                            <?php foreach ($pegawaiOptions as $peg): ?>
                                <option value="<?= (int) ($peg['id'] ?? 0); ?>"><?= esc($peg['nama'] ?? ''); ?><?= ! empty($peg['nip']) ? ' - NIP ' . esc($peg['nip']) : ''; ?></option>
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
            <table id="tableNotaDinas" class="table table-bordered table-striped table-hover w-100">
                <thead>
                    <tr>
                        <th style="width: 50px;" class="text-center">No</th>
                        <th style="width: 160px;">Nomor Nota Dinas</th>
                        <th style="width: 110px;" class="text-center">Tgl. Nota Dinas</th>
                        <th>Pegawai Diusulkan</th>
                        <th style="width: 160px;" class="text-center">Periode Perjalanan</th>
                        <th>Tujuan</th>
                        <th>Isi / Dasar Surat</th>
                        <th style="width: 160px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Form Nota Dinas -->
<div class="modal fade" id="modalNotaDinas" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Tambah Nota Dinas Perjalanan Dinas</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <form method="POST" action="<?= site_url('admin/surat/perjalanan-dinas/nota-dinas/buat'); ?>" id="formNotaDinas">
                <?= csrf_field(); ?>
                <div class="modal-body">

                    <!-- Informasi Header Nota Dinas -->
                    <div class="card card-outline card-secondary mb-3">
                        <div class="card-header py-2">
                            <h6 class="card-title mb-0 text-bold"><i class="fas fa-heading mr-1"></i> Header Nota Dinas</h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="row">
                                <div class="col-md-7">
                                    <div class="form-group mb-2">
                                        <label for="nomor_nota_dinas" class="mb-1">Nomor Nota Dinas <small class="text-muted">(Opsional)</small></label>
                                        <input type="text" class="form-control" name="nomor_nota_dinas" id="nomor_nota_dinas" placeholder="Kosongkan jika diisi manual saat cetak (Contoh: 272/SPT/Gs7/2026)">
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group mb-2">
                                        <label for="tanggal_nota_dinas" class="mb-1">Tanggal Nota Dinas <small class="text-muted">(Opsional)</small></label>
                                        <input type="date" class="form-control" name="tanggal_nota_dinas" id="tanggal_nota_dinas">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-2">
                                <label for="kepada_yth" class="mb-1">Yth. <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="kepada_yth" id="kepada_yth" value="Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau" required>
                            </div>

                            <div class="form-group mb-2">
                                <label for="dari" class="mb-1">Dari <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="dari" id="dari" value="Pejabat Pembuat Komitmen Pelaksanaan Prasarana Strategis Riau" required>
                            </div>

                            <div class="form-group mb-0">
                                <label for="hal" class="mb-1">Hal <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="hal" id="hal" value="Permohonan Penerbitan Surat Tugas Pelaksanaan Kegiatan" required>
                            </div>
                        </div>
                    </div>

                    <!-- Isi Manual Nota Dinas -->
                    <div class="card card-outline card-primary mb-3">
                        <div class="card-header py-2">
                            <h6 class="card-title mb-0 text-bold"><i class="fas fa-align-left mr-1"></i> Isi Pembuka Nota Dinas</h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="form-group mb-0">
                                <label for="isi_nota_dinas" class="mb-1">Paragraf Pembuka (Menunjuk Surat...) <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="isi_nota_dinas" id="isi_nota_dinas" rows="4" required><?= esc($defaultIsi); ?></textarea>
                                <small class="text-muted">Ketik atau sesuaikan isi surat rujukan sebelum tabel daftar pegawai yang diusulkan.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Pegawai, Periode & Tujuan -->
                    <div class="card card-outline card-success mb-3">
                        <div class="card-header py-2">
                            <h6 class="card-title mb-0 text-bold"><i class="fas fa-users mr-1"></i> Usulan Pegawai, Periode & Tujuan Perjalanan</h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="form-group">
                                <label for="pelaksana_ids" class="mb-1">Pilih Pegawai (Master Pegawai) <span class="text-danger">*</span></label>
                                <select class="form-control select2-pegawai" name="pelaksana_id[]" id="pelaksana_ids" multiple="multiple" required style="width: 100%;">
                                    <?php foreach ($pegawaiOptions as $p): ?>
                                        <option value="<?= $p['id']; ?>"><?= esc($p['display_label']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Nama, NIP, dan Pangkat/Golongan akan diambil otomatis dari Master Pegawai.</small>
                            </div>

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

                            <div class="form-group mb-0">
                                <label for="tujuan_textarea">Tujuan Perjalanan <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="tujuan" id="tujuan_textarea" rows="2" placeholder="Contoh: Kab. Kampar / Lokasi Kegiatan..." required></textarea>
                                <small class="text-muted">Kolom Periode Perjalanan dan Tujuan akan digabung (merge vertikal) pada tabel cetakan PDF.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Penandatangan -->
                    <div class="card card-outline card-info mb-0">
                        <div class="card-header py-2">
                            <h6 class="card-title mb-0 text-bold"><i class="fas fa-file-signature mr-1"></i> Penandatangan Nota Dinas</h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="form-group mb-0">
                                <label for="ppk_pegawai_id" class="mb-1">Pejabat Pembuat Komitmen <span class="text-danger">*</span></label>
                                <select class="form-control select2-pegawai" name="ppk_pegawai_id" id="ppk_pegawai_id" required style="width: 100%;">
                                    <option value="">-- Pilih Pejabat PPK --</option>
                                    <?php foreach ($pegawaiOptions as $p): ?>
                                        <option value="<?= $p['id']; ?>" <?= ($p['id'] == 2) ? 'selected' : ''; ?>><?= esc($p['display_label']); ?></option>
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
                Apakah Anda yakin ingin menghapus data Nota Dinas Perjalanan Dinas ini?
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
var defaultIsiNotaDinas = <?= json_encode($defaultIsi, JSON_UNESCAPED_UNICODE); ?>;

function openCreateModal() {
    document.getElementById('formNotaDinas').reset();
    $('.select2-pegawai').val(null).trigger('change');

    document.getElementById('nomor_nota_dinas').value = '';
    document.getElementById('tanggal_nota_dinas').value = '';
    document.getElementById('kepada_yth').value = 'Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau';
    document.getElementById('dari').value = 'Pejabat Pembuat Komitmen Pelaksanaan Prasarana Strategis Riau';
    document.getElementById('hal').value = 'Permohonan Penerbitan Surat Tugas Pelaksanaan Kegiatan';
    document.getElementById('isi_nota_dinas').value = defaultIsiNotaDinas;
    document.getElementById('tujuan_textarea').value = '';

    $('#ppk_pegawai_id').val('2').trigger('change');

    var actionUrl = '<?= site_url('admin/surat/perjalanan-dinas/nota-dinas/buat'); ?>';
    document.getElementById('formNotaDinas').setAttribute('action', actionUrl);

    $('#modalNotaDinas .modal-title').text('Tambah Nota Dinas Perjalanan Dinas');
    $('#periode_selesai').removeAttr('min');

    $('#modalNotaDinas').modal('show');
}

function openEditModal(data) {
    document.getElementById('formNotaDinas').reset();
    $('.select2-pegawai').val(null).trigger('change');

    var actionUrl = '<?= site_url('admin/surat/perjalanan-dinas/nota-dinas'); ?>/' + data.id + '/ubah';
    document.getElementById('formNotaDinas').setAttribute('action', actionUrl);

    $('#modalNotaDinas .modal-title').text('Ubah Nota Dinas Perjalanan Dinas');

    document.getElementById('nomor_nota_dinas').value = data.nomor_nota_dinas || '';
    document.getElementById('tanggal_nota_dinas').value = data.tanggal_nota_dinas || '';
    document.getElementById('kepada_yth').value = data.kepada_yth || 'Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau';
    document.getElementById('dari').value = data.dari || 'Pejabat Pembuat Komitmen Pelaksanaan Prasarana Strategis Riau';
    document.getElementById('hal').value = data.hal || 'Permohonan Penerbitan Surat Tugas Pelaksanaan Kegiatan';
    document.getElementById('isi_nota_dinas').value = data.isi_nota_dinas || defaultIsiNotaDinas;
    document.getElementById('periode_mulai').value = data.periode_mulai || '';
    document.getElementById('periode_selesai').value = data.periode_selesai || '';
    document.getElementById('tujuan_textarea').value = data.tujuan || '';

    if (data.periode_mulai) {
        $('#periode_selesai').attr('min', data.periode_mulai);
    }

    $('#ppk_pegawai_id').val(data.ppk_pegawai_id || '2').trigger('change');

    var pelaksana = data.pelaksana_raw || [];
    var selectedIds = [];
    for (var i = 0; i < pelaksana.length; i++) {
        selectedIds.push(pelaksana[i].id);
    }
    $('#pelaksana_ids').val(selectedIds).trigger('change');

    $('#modalNotaDinas').modal('show');
}

$(document).ready(function() {
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

    $('#pelaksana_ids').select2({
        theme: 'bootstrap4',
        dropdownParent: $('#modalNotaDinas'),
        placeholder: '-- Pilih Pegawai dari Master Pegawai --'
    });

    $('#ppk_pegawai_id').select2({
        theme: 'bootstrap4',
        dropdownParent: $('#modalNotaDinas')
    });

    var $filterStartDate = $('#filter_start_date');
    var $filterEndDate   = $('#filter_end_date');
    var $filterPelaksana = $('#filter_pelaksana');

    $('#filter_pelaksana').select2({
        theme: 'bootstrap4',
        placeholder: 'Semua Pegawai',
        allowClear: true
    });

    var table = $('#tableNotaDinas').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= site_url('admin/surat/perjalanan-dinas/nota-dinas'); ?>',
            type: 'GET',
            data: function (d) {
                d.filter_start_date = $filterStartDate.val();
                d.filter_end_date   = $filterEndDate.val();
                d.filter_pelaksana  = $filterPelaksana.val();
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
            {
                data: 'nomor_nota_dinas',
                render: function(data) {
                    if (!data) return '<span class="text-muted font-italic">(Kosong / Manual)</span>';
                    return '<strong>' + $('<div>').text(data).html() + '</strong>';
                }
            },
            { data: 'tanggal_nota_html', className: 'text-center' },
            { data: 'pelaksana_html', sortable: false },
            { data: 'periode_html', className: 'text-center' },
            {
                data: 'tujuan',
                render: function(data) {
                    if (!data) return '-';
                    var escaped = $('<div>').text(data).html();
                    var shortText = data.length > 35 ? data.substring(0, 35) + '...' : data;
                    var escapedShort = $('<div>').text(shortText).html();
                    return '<div class="text-tujuan" data-toggle="tooltip" data-placement="top" title="' + escaped + '">' + escapedShort + '</div>';
                }
            },
            {
                data: 'isi_nota_dinas',
                render: function(data) {
                    if (!data) return '-';
                    var escaped = $('<div>').text(data).html();
                    var shortText = data.length > 45 ? data.substring(0, 45) + '...' : data;
                    var escapedShort = $('<div>').text(shortText).html();
                    return '<div class="text-isi" data-toggle="tooltip" data-placement="top" title="' + escaped + '">' + escapedShort + '</div>';
                }
            },
            { data: 'action_html', className: 'text-center', sortable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            processing: 'Memproses...',
            zeroRecords: 'Tidak ada data Nota Dinas',
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
        },
        drawCallback: function () {
            $('[data-toggle="tooltip"]').tooltip('dispose').tooltip({
                container: 'body',
                trigger: 'hover',
                boundary: 'window'
            });
        }
    });

    $(document).on('click', '.btn-edit', function() {
        var rowData = table.row($(this).closest('tr')).data();
        if (rowData) {
            openEditModal(rowData);
        }
    });

    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var deleteUrl = '<?= site_url('admin/surat/perjalanan-dinas/nota-dinas'); ?>/' + id + '/hapus';

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Hapus Nota Dinas?',
                text: 'Data Nota Dinas ini akan dihapus secara permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'btn btn-danger font-weight-bold px-3 py-2 mr-2',
                    cancelButton: 'btn btn-secondary px-3 py-2'
                },
                buttonsStyling: false
            }).then(function(result) {
                if (result.isConfirmed) {
                    window.location.href = deleteUrl;
                }
            });
        } else {
            $('#btnConfirmDelete').attr('href', deleteUrl);
            $('#deleteModal').modal('show');
        }
    });

    $filterStartDate.on('change', function () { table.ajax.reload(); });
    $filterEndDate.on('change', function () { table.ajax.reload(); });
    $filterPelaksana.on('change', function () { table.ajax.reload(); });

    $('#btn-reset-filter').on('click', function () {
        $filterStartDate.val('<?= date('Y-01-01'); ?>');
        $filterEndDate.val('<?= date('Y-m-t'); ?>');
        $filterPelaksana.val(null).trigger('change');
        table.ajax.reload();
    });
});
</script>
<?= $this->endSection(); ?>

