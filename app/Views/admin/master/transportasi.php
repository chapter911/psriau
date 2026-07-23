<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm" style="border: 1px solid #e9eef5; border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white py-3" style="border-bottom: 1px solid #e9eef5;">
                    <h3 class="card-title mb-0 font-weight-bold text-dark" style="font-size: 1.15rem; line-height: 1.8;">
                        <i class="fas fa-car text-primary mr-2"></i>Master Transportasi
                    </h3>
                    <?php if (! empty($can_add)): ?>
                        <div class="card-tools float-right">
                            <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm" data-toggle="modal" data-target="#modal-tambah-transportasi" style="border-radius: 6px;">
                                <i class="fas fa-plus mr-1"></i> Tambah Transportasi
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-striped w-100 js-datatable" style="border-radius: 8px;">
                            <thead>
                                <tr>
                                    <th style="width: 60px;" class="text-center">#</th>
                                    <th>Nama Transportasi</th>
                                    <?php if (! empty($can_edit) || ! empty($can_delete)): ?>
                                        <th style="width: 150px;" class="text-center">Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach (($items ?? []) as $item): ?>
                                    <tr>
                                        <td class="text-center align-middle"><?= esc((string) $i++); ?></td>
                                        <td class="align-middle" style="white-space: normal; line-height: 1.5; font-size: 0.95rem;">
                                            <?= esc((string) ($item['nama_transportasi'] ?? '-')); ?>
                                        </td>
                                        <?php if (! empty($can_edit) || ! empty($can_delete)): ?>
                                            <td class="text-center align-middle" style="white-space: nowrap;">
                                                <div class="btn-group" role="group" style="gap: 5px;">
                                                    <?php if (! empty($can_edit)): ?>
                                                        <button
                                                            type="button"
                                                            class="btn btn-outline-primary btn-xs px-2 py-1"
                                                            data-toggle="modal"
                                                            data-target="#modal-ubah-transportasi"
                                                            data-id="<?= esc((string) ($item['id'] ?? ''), 'attr'); ?>"
                                                            data-nama="<?= esc((string) ($item['nama_transportasi'] ?? ''), 'attr'); ?>"
                                                            style="border-radius: 4px; font-weight: 500;"
                                                            title="Edit"
                                                        >
                                                            <i class="fas fa-pen mr-1"></i> Edit
                                                        </button>
                                                    <?php endif; ?>
                                                    <?php if (! empty($can_delete)): ?>
                                                        <button
                                                            type="button"
                                                            class="btn btn-outline-danger btn-xs px-2 py-1"
                                                            data-toggle="modal"
                                                            data-target="#modal-hapus-transportasi"
                                                            data-id="<?= esc((string) ($item['id'] ?? ''), 'attr'); ?>"
                                                            data-nama="<?= esc((string) ($item['nama_transportasi'] ?? ''), 'attr'); ?>"
                                                            style="border-radius: 4px; font-weight: 500;"
                                                            title="Hapus"
                                                        >
                                                            <i class="fas fa-trash-alt mr-1"></i> Hapus
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
    </div>
</div>

<!-- Modal Tambah -->
<?php if (! empty($can_add)): ?>
<div class="modal fade" id="modal-tambah-transportasi" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header bg-light py-3" style="border-bottom: 1px solid #e9eef5;">
                <h5 class="modal-title font-weight-bold text-dark" style="font-size: 1.05rem;">
                    <i class="fas fa-plus-circle text-primary mr-2"></i>Tambah Transportasi
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('/admin/master/transportasi/tambah'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body py-4">
                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-secondary mb-2" style="font-size: 0.9rem;">Nama Transportasi <span class="text-danger">*</span></label>
                        <input type="text" name="nama_transportasi" class="form-control" placeholder="Contoh: Mobil Dinas" required style="border-radius: 6px; padding: 10px 12px;">
                    </div>
                </div>
                <div class="modal-footer bg-light py-3" style="border-top: 1px solid #e9eef5;">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal" style="border-radius: 6px;">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3" style="border-radius: 6px;">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Ubah -->
<?php if (! empty($can_edit)): ?>
<div class="modal fade" id="modal-ubah-transportasi" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header bg-light py-3" style="border-bottom: 1px solid #e9eef5;">
                <h5 class="modal-title font-weight-bold text-dark" style="font-size: 1.05rem;">
                    <i class="fas fa-pen-fancy text-primary mr-2"></i>Ubah Transportasi
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="post" id="form-ubah-transportasi">
                <?= csrf_field(); ?>
                <div class="modal-body py-4">
                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-secondary mb-2" style="font-size: 0.9rem;">Nama Transportasi <span class="text-danger">*</span></label>
                        <input type="text" name="nama_transportasi" id="ubah-nama-transportasi" class="form-control" required style="border-radius: 6px; padding: 10px 12px;">
                    </div>
                </div>
                <div class="modal-footer bg-light py-3" style="border-top: 1px solid #e9eef5;">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal" style="border-radius: 6px;">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3" style="border-radius: 6px;">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Hapus -->
<?php if (! empty($can_delete)): ?>
<div class="modal fade" id="modal-hapus-transportasi" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header bg-light py-3" style="border-bottom: 1px solid #e9eef5;">
                <h5 class="modal-title font-weight-bold text-danger" style="font-size: 1.05rem;">
                    <i class="fas fa-exclamation-triangle text-danger mr-2"></i>Hapus Transportasi
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="post" id="form-hapus-transportasi">
                <?= csrf_field(); ?>
                <div class="modal-body py-4">
                    <p class="mb-0">Apakah Anda yakin ingin menghapus data transportasi <strong id="hapus-nama-transportasi" class="text-dark"></strong>?</p>
                    <small class="text-danger font-weight-bold d-block mt-2"><i class="fas fa-info-circle mr-1"></i>Tindakan ini tidak dapat dibatalkan.</small>
                </div>
                <div class="modal-footer bg-light py-3" style="border-top: 1px solid #e9eef5;">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal" style="border-radius: 6px;">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm px-3" style="border-radius: 6px;">Hapus Data</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
$(document).ready(function() {
    // Initialize standard DataTable for Master module
    $('.js-datatable').DataTable({
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

    // Populate Ubah modal
    $('#modal-ubah-transportasi').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');
        var nama = button.data('nama');
        
        var modal = $(this);
        modal.find('#form-ubah-transportasi').attr('action', '<?= site_url('/admin/master/transportasi'); ?>/' + id + '/ubah');
        modal.find('#ubah-nama-transportasi').val(nama);
    });

    // Populate Hapus modal
    $('#modal-hapus-transportasi').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');
        var nama = button.data('nama');
        
        var modal = $(this);
        modal.find('#form-hapus-transportasi').attr('action', '<?= site_url('/admin/master/transportasi'); ?>/' + id + '/hapus');
        modal.find('#hapus-nama-transportasi').text(nama);
    });
});
</script>
<?= $this->endSection(); ?>
