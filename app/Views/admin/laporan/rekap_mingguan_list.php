<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
$reports = $reports ?? [];
$pakets = $pakets ?? [];
$filterPaketId = $filter_paket_id ?? '';
$canImport = (bool) ($can_import ?? false);
$canEdit = (bool) ($can_edit ?? false);
$canDelete = (bool) ($can_delete ?? false);
?>

<div class="card mb-3">
    <div class="card-body py-3">
        <div class="form-row align-items-end">
            <div class="col-md-4 col-sm-6">
                <label for="filter_paket_id" class="small text-muted font-weight-bold">Filter Paket</label>
                <select class="form-control form-control-sm" id="filter_paket_id" name="paket_id">
                    <option value="all">- semua paket -</option>
                    <?php foreach ($pakets as $paket): ?>
                        <option value="<?= esc((string) $paket['id']); ?>" <?= (string)$filterPaketId === (string)$paket['id'] ? 'selected' : ''; ?>>
                            <?= esc((string) $paket['nama_paket']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="card card-outline card-primary">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-file-excel mr-1"></i> Rekapitulasi Mingguan</h3>
        <div class="card-tools ml-auto">
            <?php if ($canImport): ?>
                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalImport">
                    <i class="fas fa-upload mr-1"></i> Import Excel Rekap
                </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover w-100" id="tableRekapList">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 60px;" class="text-center">No</th>
                        <th style="width: 120px;" class="text-center">Minggu Ke</th>
                        <th>Paket</th>
                        <th>Judul Laporan</th>
                        <th style="width: 120px;" class="text-center">Status</th>
                        <th>Diperbarui Pada</th>
                        <th style="width: 250px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($reports !== []): ?>
                        <?php $no = 1; foreach ($reports as $report): ?>
                            <tr>
                                <td class="text-center align-middle"><?= $no++; ?></td>
                                <td class="text-center align-middle font-weight-bold text-primary">Minggu <?= esc($report['minggu_ke']); ?></td>
                                <td class="align-middle">
                                    <?php if (!empty($report['nama_paket'])): ?>
                                        <span class="badge badge-success"><i class="fas fa-box mr-1"></i> <?= esc($report['nama_paket']); ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-warning text-dark"><i class="fas fa-exclamation-triangle mr-1"></i> Belum ada paket</span>
                                    <?php endif; ?>
                                </td>
                                <td class="align-middle"><strong><?= esc($report['judul']); ?></strong></td>
                                <td class="text-center align-middle">
                                    <span class="badge badge-success p-2">
                                        <i class="fas fa-check-circle mr-1"></i> Data Terimpor
                                    </span>
                                </td>
                                <td class="align-middle"><?= date('d F Y, H:i', strtotime($report['updated_at'] ?: $report['created_at'])); ?> WIB</td>
                                <td class="text-center align-middle text-nowrap">
                                    <a href="<?= site_url('admin/laporan/rekap-mingguan/show/' . (int)$report['id']); ?>" class="btn btn-info btn-sm mr-1">
                                        <i class="fas fa-table mr-1"></i> Buka Rekap
                                    </a>
                                    <?php if ($canEdit): ?>
                                        <button type="button" class="btn btn-warning btn-sm mr-1 btn-edit" 
                                                data-id="<?= $report['id']; ?>" 
                                                data-minggu="<?= $report['minggu_ke']; ?>" 
                                                data-paket="<?= $report['paket_id']; ?>"
                                                data-judul="<?= esc($report['judul']); ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($canDelete): ?>
                                        <button type="button" class="btn btn-danger btn-sm btn-delete" data-id="<?= $report['id']; ?>">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada data rekapitulasi mingguan. Silakan pilih "Import Excel Rekap" untuk mengimpor berkas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Edit Periode -->
<?php if ($canEdit): ?>
<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mb-0"><i class="fas fa-edit mr-1"></i> Ubah Periode Laporan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEdit" action="" method="post">
                <?= csrf_field(); ?>
                <input type="hidden" name="action" value="edit_meta">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_paket_id">Paket <span class="text-danger">*</span></label>
                        <select class="form-control" id="edit_paket_id" name="paket_id" required>
                            <option value="">-- Pilih Paket --</option>
                            <?php foreach ($pakets as $paket): ?>
                                <option value="<?= esc((string) $paket['id']); ?>"><?= esc((string) $paket['nama_paket']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_minggu_ke">Minggu Ke <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="edit_minggu_ke" name="minggu_ke" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_judul">Judul Laporan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_judul" name="judul" required>
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

<!-- Modal Import -->
<?php if ($canImport): ?>
<div class="modal fade" id="modalImport" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mb-0"><i class="fas fa-upload mr-1"></i> Import Excel Rekapitulasi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('admin/laporan/rekap-mingguan/import'); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="icon fas fa-info-circle mr-1"></i> Unggah berkas Excel (format .xlsx atau .xls). Sistem akan membaca sheet <strong>"REKAP"</strong> dan <strong>"RAB"</strong> secara otomatis untuk menghitung rekapitulasi.
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
                    <div class="form-group">
                        <label for="import_minggu_ke">Minggu Ke <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="import_minggu_ke" name="minggu_ke" min="1" placeholder="Contoh: 31" required>
                    </div>
                    <div class="form-group">
                        <label for="file_excel">File Excel <span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="file_excel" name="file_excel" accept=".xlsx,.xls" required>
                            <label class="custom-file-label" for="file_excel">Pilih file...</label>
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

<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script>
$(document).ready(function() {
    // Custom file upload label
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });

    // Client-side DataTable
    $('#tableRekapList').DataTable({
        responsive: true,
        autoWidth: false,
        language: {
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data',
            zeroRecords: 'Data tidak ditemukan',
            paginate: {
                first: 'Awal',
                last: 'Akhir',
                next: 'Berikutnya',
                previous: 'Sebelumnya',
            },
        },
    });

    // Filter Paket handler
    $('#filter_paket_id').on('change', function() {
        const val = $(this).val();
        window.location.href = '<?= site_url('admin/laporan/rekap-mingguan'); ?>' + (val !== 'all' ? '?paket_id=' + val : '');
    });

    // Edit button click handler
    $('.btn-edit').on('click', function() {
        const id = $(this).data('id');
        const minggu = $(this).data('minggu');
        const paket = $(this).data('paket');
        const judul = $(this).data('judul');

        $('#edit_paket_id').val(paket);
        $('#edit_minggu_ke').val(minggu);
        $('#edit_judul').val(judul);
        $('#formEdit').attr('action', '<?= site_url('admin/laporan/rekap-mingguan'); ?>/' + id + '/ubah');
        $('#modalEdit').modal('show');
    });

    // Delete button click handler
    $('.btn-delete').on('click', function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Seluruh data rekap dan rincian item pekerjaan untuk periode ini akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= site_url('admin/laporan/rekap-mingguan'); ?>/' + id + '/hapus',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        <?= csrf_token(); ?>: '<?= csrf_hash(); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Terhapus!', response.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Gagal!', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Gagal!', 'Terjadi kesalahan sistem.', 'error');
                    }
                });
            }
        });
    });
});
</script>
<?= $this->endSection(); ?>
