<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">
    <!-- Messages -->
    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-1"></i> <?= session()->getFlashdata('message'); ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-1"></i> <?= session()->getFlashdata('error'); ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm" style="border: 1px solid #e9eef5; border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white py-3" style="border-bottom: 1px solid #e9eef5;">
                    <h3 class="card-title mb-0 font-weight-bold text-dark" style="font-size: 1.15rem; line-height: 1.8;">
                        <i class="fas fa-hotel text-primary mr-2"></i>Master Biaya Penginapan
                    </h3>
                    <?php if (! empty($can_add)): ?>
                        <div class="card-tools float-right">
                            <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm" data-toggle="modal" data-target="#modal-tambah-biaya" style="border-radius: 6px;">
                                <i class="fas fa-plus mr-1"></i> Tambah Biaya Penginapan
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
                                    <th>Provinsi</th>
                                    <th>Tingkat / Level Pejabat</th>
                                    <th>Tarif Hotel / Malam (Rp)</th>
                                    <?php if (! empty($can_edit) || ! empty($can_delete)): ?>
                                        <th style="width: 150px;" class="text-center">Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach (($items ?? []) as $item): ?>
                                    <tr>
                                        <td class="text-center align-middle"><?= esc((string) $i++); ?></td>
                                        <td class="align-middle"><?= esc((string) ($item['nama_provinsi'] ?? '-')); ?></td>
                                        <td class="align-middle"><?= esc((string) ($item['level_pejabat'] ?? '-')); ?></td>
                                        <td class="align-middle font-weight-bold text-dark">
                                            Rp <?= number_format((float) ($item['tarif'] ?? 0), 0, ',', '.'); ?>
                                        </td>
                                        <?php if (! empty($can_edit) || ! empty($can_delete)): ?>
                                            <td class="text-center align-middle" style="white-space: nowrap;">
                                                <div class="btn-group" role="group" style="gap: 5px;">
                                                    <?php if (! empty($can_edit)): ?>
                                                        <button
                                                            type="button"
                                                            class="btn btn-outline-primary btn-xs px-2 py-1"
                                                            data-toggle="modal"
                                                            data-target="#modal-ubah-biaya"
                                                            data-id="<?= esc((string) ($item['id'] ?? ''), 'attr'); ?>"
                                                            data-provinsi="<?= esc((string) ($item['kode_provinsi'] ?? ''), 'attr'); ?>"
                                                            data-namaprovinsi="<?= esc((string) ($item['nama_provinsi'] ?? ''), 'attr'); ?>"
                                                            data-level="<?= esc((string) ($item['level_pejabat'] ?? ''), 'attr'); ?>"
                                                            data-tarif="<?= esc((string) ($item['tarif'] ?? 0), 'attr'); ?>"
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
                                                            data-target="#modal-hapus-biaya"
                                                            data-id="<?= esc((string) ($item['id'] ?? ''), 'attr'); ?>"
                                                            data-provinsi="<?= esc((string) ($item['nama_provinsi'] ?? ''), 'attr'); ?>"
                                                            data-level="<?= esc((string) ($item['level_pejabat'] ?? ''), 'attr'); ?>"
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
<div class="modal fade" id="modal-tambah-biaya" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header bg-light py-3" style="border-bottom: 1px solid #e9eef5;">
                <h5 class="modal-title font-weight-bold text-dark" style="font-size: 1.05rem;">
                    <i class="fas fa-plus-circle text-primary mr-2"></i>Tambah Biaya Penginapan
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('/admin/master/biaya/penginapan/tambah'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body py-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary mb-2" style="font-size: 0.9rem;">Provinsi <span class="text-danger">*</span></label>
                        <select name="kode_provinsi" id="tambah-kode-provinsi" class="form-control select2" style="width: 100%;" required>
                            <option value="">-- Pilih Provinsi --</option>
                            <?php foreach (($provinsiOptions ?? []) as $prov): ?>
                                <option value="<?= esc((string) $prov['kode_provinsi']); ?>"><?= esc((string) $prov['nama_provinsi']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="nama_provinsi" id="tambah-nama-provinsi">
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary mb-2" style="font-size: 0.9rem;">Tingkat / Level Pejabat <span class="text-danger">*</span></label>
                        <select name="level_pejabat" class="form-control select2" style="width: 100%;" required>
                            <option value="">-- Pilih Level Pejabat --</option>
                            <option value="Pejabat Negara/Wakil Menteri/Pejabat Eselon I">Pejabat Negara/Wakil Menteri/Pejabat Eselon I</option>
                            <option value="Pejabat Negara Lainnya/Pejabat Eselon II">Pejabat Negara Lainnya/Pejabat Eselon II</option>
                            <option value="Pejabat Eselon III/Golongan IV">Pejabat Eselon III/Golongan IV</option>
                            <option value="Pejabat Eselon IV/Golongan III/II/I">Pejabat Eselon IV/Golongan III/II/I</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-secondary mb-2" style="font-size: 0.9rem;">Tarif Hotel / Malam (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="tarif" class="form-control" placeholder="Contoh: 1650000" required style="border-radius: 6px; padding: 10px 12px;">
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
<div class="modal fade" id="modal-ubah-biaya" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header bg-light py-3" style="border-bottom: 1px solid #e9eef5;">
                <h5 class="modal-title font-weight-bold text-dark" style="font-size: 1.05rem;">
                    <i class="fas fa-pen-fancy text-primary mr-2"></i>Ubah Biaya Penginapan
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="post" id="form-ubah-biaya">
                <?= csrf_field(); ?>
                <div class="modal-body py-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary mb-2" style="font-size: 0.9rem;">Provinsi <span class="text-danger">*</span></label>
                        <select name="kode_provinsi" id="ubah-kode-provinsi" class="form-control select2" style="width: 100%;" required>
                            <option value="">-- Pilih Provinsi --</option>
                            <?php foreach (($provinsiOptions ?? []) as $prov): ?>
                                <option value="<?= esc((string) $prov['kode_provinsi']); ?>"><?= esc((string) $prov['nama_provinsi']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="nama_provinsi" id="ubah-nama-provinsi">
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary mb-2" style="font-size: 0.9rem;">Tingkat / Level Pejabat <span class="text-danger">*</span></label>
                        <select name="level_pejabat" id="ubah-level" class="form-control select2" style="width: 100%;" required>
                            <option value="">-- Pilih Level Pejabat --</option>
                            <option value="Pejabat Negara/Wakil Menteri/Pejabat Eselon I">Pejabat Negara/Wakil Menteri/Pejabat Eselon I</option>
                            <option value="Pejabat Negara Lainnya/Pejabat Eselon II">Pejabat Negara Lainnya/Pejabat Eselon II</option>
                            <option value="Pejabat Eselon III/Golongan IV">Pejabat Eselon III/Golongan IV</option>
                            <option value="Pejabat Eselon IV/Golongan III/II/I">Pejabat Eselon IV/Golongan III/II/I</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-secondary mb-2" style="font-size: 0.9rem;">Tarif Hotel / Malam (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="tarif" id="ubah-tarif" class="form-control" required style="border-radius: 6px; padding: 10px 12px;">
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
<div class="modal fade" id="modal-hapus-biaya" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header bg-light py-3" style="border-bottom: 1px solid #e9eef5;">
                <h5 class="modal-title font-weight-bold text-danger" style="font-size: 1.05rem;">
                    <i class="fas fa-exclamation-triangle text-danger mr-2"></i>Hapus Biaya Penginapan
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="post" id="form-hapus-biaya">
                <?= csrf_field(); ?>
                <div class="modal-body py-4">
                    <p class="mb-0">Apakah Anda yakin ingin menghapus biaya penginapan untuk Provinsi <strong id="hapus-provinsi" class="text-dark"></strong> dengan Level <strong id="hapus-level" class="text-dark"></strong>?</p>
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
    // Initialize DataTable
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

    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4'
    });

    // Handle Provinsi text synchronization
    $('#tambah-kode-provinsi').on('change', function() {
        var text = $(this).find('option:selected').text();
        if ($(this).val() !== '') {
            $('#tambah-nama-provinsi').val(text);
        } else {
            $('#tambah-nama-provinsi').val('');
        }
    });

    $('#ubah-kode-provinsi').on('change', function() {
        var text = $(this).find('option:selected').text();
        if ($(this).val() !== '') {
            $('#ubah-nama-provinsi').val(text);
        } else {
            $('#ubah-nama-provinsi').val('');
        }
    });

    // Populate Ubah modal
    $('#modal-ubah-biaya').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');
        var prov = button.data('provinsi');
        var namaProv = button.data('namaprovinsi');
        var level = button.data('level');
        var tarif = button.data('tarif');
        
        var modal = $(this);
        modal.find('#form-ubah-biaya').attr('action', '<?= site_url('/admin/master/biaya/penginapan'); ?>/' + id + '/ubah');
        modal.find('#ubah-tarif').val(tarif);

        modal.find('#ubah-kode-provinsi').val(prov).trigger('change');
        modal.find('#ubah-nama-provinsi').val(namaProv);
        modal.find('#ubah-level').val(level).trigger('change');
    });

    // Populate Hapus modal
    $('#modal-hapus-biaya').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');
        var prov = button.data('provinsi');
        var level = button.data('level');
        
        var modal = $(this);
        modal.find('#form-hapus-biaya').attr('action', '<?= site_url('/admin/master/biaya/penginapan'); ?>/' + id + '/hapus');
        modal.find('#hapus-provinsi').text(prov);
        modal.find('#hapus-level').text(level);
    });
});
</script>
<?= $this->endSection(); ?>
