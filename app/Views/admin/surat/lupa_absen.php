<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
    $canEdit = (bool) ($can_edit ?? false);
    $canApprove = (bool) ($can_approve ?? false);
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

    table.dataTable thead .sorting::before,
    table.dataTable thead .sorting_asc::before,
    table.dataTable thead .sorting_desc::before,
    table.dataTable thead .sorting_asc_disabled::before,
    table.dataTable thead .sorting_desc_disabled::before,
    table.dataTable thead .sorting::after,
    table.dataTable thead .sorting_asc::after,
    table.dataTable thead .sorting_desc::after,
    table.dataTable thead .sorting_asc_disabled::after,
    table.dataTable thead .sorting_desc_disabled::after {
        bottom: 50% !important;
        transform: translateY(50%) !important;
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
            <a href="<?= site_url('admin/surat/lupa-absen/buat'); ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Ajukan Lupa Absen
            </a>
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
                        <th>Tanggal Pengajuan</th>
                        <th>Jumlah Entri</th>
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus data pengajuan lupa absen ini?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <a id="btnConfirmDelete" href="#" class="btn btn-danger">Hapus</a>
            </div>
        </div>
    </div>
</div>

<script>
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
            { data: 'tanggal_surat_formatted', className: 'text-center' },
            { data: 'jumlah_entri', className: 'text-center' },
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
