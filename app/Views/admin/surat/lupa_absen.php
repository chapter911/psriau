<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
    $canEdit = (bool) ($can_edit ?? false);
    $canApprove = (bool) ($can_approve ?? false);
    $currentPegawai = $current_pegawai ?? [];
?>
<style>
    #tableLupaAbsen thead th {
        vertical-align: middle !important;
        text-align: center;
        padding: 8px 10px !important;
        font-size: 13.5px;
    }

    #tableLupaAbsen tbody td {
        vertical-align: middle !important;
        padding: 8px 10px !important;
        font-size: 13.5px;
    }

    .doc-btn-group {
        display: inline-flex;
        flex-wrap: wrap;
        gap: 4px;
        justify-content: center;
    }

    .doc-btn-group .btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        font-size: 14px;
    }
</style>

<div class="card">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title mb-0">Daftar Pengajuan Lupa Absen</h3>
        <div class="card-tools ml-auto">
            <button type="button" class="btn btn-primary btn-sm" onclick="openCreateModal()">
                <i class="fas fa-plus"></i> Ajukan Lupa Absen
            </button>
        </div>
    </div>
    <div class="card-body">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?= esc((string) session()->getFlashdata('success')); ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?= esc((string) session()->getFlashdata('error')); ?>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table id="tableLupaAbsen" class="table table-bordered table-striped table-hover w-100">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>NIP</th>
                        <th>Nama</th>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Alasan</th>
                        <th>Status</th>
                        <th>Dokumen</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Buat/Edit Lupa Absen -->
<div class="modal fade" id="modalLupaAbsen" tabindex="-1" role="dialog" aria-labelledby="modalLupaAbsenTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalLupaAbsenTitle">Ajukan Lupa Absen</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="<?= site_url('admin/surat/lupa-absen/buat'); ?>" id="formLupaAbsen">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <!-- Data Pegawai (Read-only) -->
                    <div class="alert alert-info mb-3">
                        <strong>Data Pegawai:</strong><br>
                        Nama: <?= esc($currentPegawai['nama'] ?? '-'); ?><br>
                        NIP: <?= esc($currentPegawai['nip'] ?? '-'); ?><br>
                        Jabatan: <?= esc($currentPegawai['jabatan'] ?? '-'); ?>
                    </div>

                    <input type="hidden" name="nama" value="<?= esc($currentPegawai['nama'] ?? ''); ?>">
                    <input type="hidden" name="nip" value="<?= esc($currentPegawai['nip'] ?? ''); ?>">
                    <input type="hidden" name="jabatan_id" value="<?= (int) ($currentPegawai['jabatan_id'] ?? 0); ?>">
                    <input type="hidden" name="jabatan" value="<?= esc($currentPegawai['jabatan'] ?? ''); ?>">
                    <input type="hidden" name="unit_kerja" value="<?= esc($currentPegawai['unit_kerja'] ?? ''); ?>">

                    <div class="form-group">
                        <label for="tanggal_absen">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="tanggal_absen" name="tanggal_absen" required>
                    </div>

                    <div class="form-group">
                        <label for="jenis_absen">Jenis Absen <span class="text-danger">*</span></label>
                        <select class="form-control" id="jenis_absen" name="jenis_absen" required onchange="updateAlasanTemplate()">
                            <option value="">-- Pilih --</option>
                            <option value="Masuk">Absen Masuk</option>
                            <option value="Pulang">Absen Pulang</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="alasan">Alasan <span class="text-danger">*</span></label>
                        <select class="form-control" id="alasan_template" onchange="applyTemplate()">
                            <option value="">-- Pilih Template --</option>
                            <option value="Lupa Absen Masuk">Lupa Absen Masuk</option>
                            <option value="Lupa Absen Pulang">Lupa Absen Pulang</option>
                            <option value="Terlambat Masuk">Terlambat Masuk</option>
                            <option value="Terlambat Pulang">Terlambat Pulang</option>
                            <option value="custom">Ketik Manual...</option>
                        </select>
                        <input type="text" class="form-control mt-2" id="alasan_custom" name="alasan_detail" placeholder="Ketik alasan di sini..." style="display: none;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Ajukan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus pengajuan lupa absen ini?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <a id="btnConfirmDelete" href="#" class="btn btn-danger">Hapus</a>
            </div>
        </div>
    </div>
</div>

<script>
function openCreateModal() {
    // Reset form
    $('#formLupaAbsen')[0].reset();
    $('#alasan_template').val('').trigger('change');
    $('#alasan_custom').hide();

    // Set default date to today
    var today = new Date().toISOString().split('T')[0];
    $('#tanggal_absen').val(today);

    // Show modal
    $('#modalLupaAbsen').modal('show');
}

function updateAlasanTemplate() {
    var jenis = $('#jenis_absen').val();

    if (jenis === 'Masuk') {
        $('#alasan_template').val('Lupa Absen Masuk');
    } else if (jenis === 'Pulang') {
        $('#alasan_template').val('Lupa Absen Pulang');
    }

    applyTemplate();
}

function applyTemplate() {
    var template = $('#alasan_template').val();

    if (template === 'custom') {
        $('#alasan_custom').show().focus();
        $('#alasan_custom').attr('name', 'alasan_detail');
    } else if (template) {
        $('#alasan_custom').hide();
        // Create or update hidden input
        if ($('#hidden_alasan').length) {
            $('#hidden_alasan').val(template);
        } else {
            $('#formLupaAbsen').append('<input type="hidden" id="hidden_alasan" name="alasan_detail" value="">');
            $('#hidden_alasan').val(template);
        }
    }
}

$(document).ready(function() {
    var table = $('#tableLupaAbsen').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= site_url('admin/surat/lupa-absen'); ?>',
            type: 'GET',
            error: function(xhr, error, thrown) {
                console.error('DataTable error:', thrown);
                alert('Gagal memuat data. Silakan refresh halaman.');
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
            { data: 'nip', className: 'text-center' },
            { data: 'nama' },
            { data: 'tanggal_formatted', className: 'text-center' },
            { data: 'jenis_formatted', className: 'text-center' },
            { data: 'alasan_detail', className: 'text-left' },
            { data: 'status_badge', className: 'text-center', sortable: false, searchable: false },
            { data: 'dokumen_html', className: 'text-center', sortable: false, searchable: false },
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

    // Delete button handler
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        $('#btnConfirmDelete').attr('href', '<?= site_url('admin/surat/lupa-absen'); ?>/' + id + '/hapus');
        $('#deleteModal').modal('show');
    });

    // Approve button handler
    $(document).on('click', '.btn-approve', function() {
        var id = $(this).data('id');
        if (confirm('Apakah Anda yakin ingin MENYETUJUI pengajuan ini?')) {
            window.location.href = '<?= site_url('admin/surat/lupa-absen'); ?>/' + id + '/approve';
        }
    });

    // Reject button handler
    $(document).on('click', '.btn-reject', function() {
        var id = $(this).data('id');
        if (confirm('Apakah Anda yakin ingin MENOLAK pengajuan ini?')) {
            window.location.href = '<?= site_url('admin/surat/lupa-absen'); ?>/' + id + '/reject';
        }
    });
});
</script>

<?= $this->endSection(); ?>
