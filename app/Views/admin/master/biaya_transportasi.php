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
                        <i class="fas fa-route text-primary mr-2"></i>Master Biaya Transportasi
                    </h3>
                    <?php if (! empty($can_add)): ?>
                        <div class="card-tools float-right">
                            <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm" data-toggle="modal" data-target="#modal-tambah-biaya" style="border-radius: 6px;">
                                <i class="fas fa-plus mr-1"></i> Tambah Biaya Transportasi
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
                                    <th>Asal</th>
                                    <th>Tujuan</th>
                                    <th>Besaran Tarif (Rp)</th>
                                    <?php if (! empty($can_edit) || ! empty($can_delete)): ?>
                                        <th style="width: 150px;" class="text-center">Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach (($items ?? []) as $item): ?>
                                    <tr>
                                        <td class="text-center align-middle"><?= esc((string) $i++); ?></td>
                                        <td class="align-middle"><?= esc((string) ($item['asal'] ?? '-')); ?></td>
                                        <td class="align-middle"><?= esc((string) ($item['tujuan'] ?? '-')); ?></td>
                                        <td class="align-middle font-weight-bold text-dark">
                                            Rp <?= number_format((float) ($item['besaran'] ?? 0), 0, ',', '.'); ?>
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
                                                            data-kabupaten="<?= esc((string) ($item['kode_kabupaten'] ?? ''), 'attr'); ?>"
                                                            data-asal="<?= esc((string) ($item['asal'] ?? ''), 'attr'); ?>"
                                                            data-tujuan="<?= esc((string) ($item['tujuan'] ?? ''), 'attr'); ?>"
                                                            data-besaran="<?= esc((string) ($item['besaran'] ?? 0), 'attr'); ?>"
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
                                                            data-asal="<?= esc((string) ($item['asal'] ?? ''), 'attr'); ?>"
                                                            data-tujuan="<?= esc((string) ($item['tujuan'] ?? ''), 'attr'); ?>"
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
                    <i class="fas fa-plus-circle text-primary mr-2"></i>Tambah Biaya Transportasi
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('/admin/master/biaya/transportasi/tambah'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body py-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary mb-2" style="font-size: 0.9rem;">Provinsi</label>
                        <select name="kode_provinsi" id="tambah-kode-provinsi" class="form-control select2" style="width: 100%;">
                            <option value="">-- Pilih Provinsi (Opsional) --</option>
                            <?php foreach (($provinsiOptions ?? []) as $prov): ?>
                                <option value="<?= esc((string) $prov['kode_provinsi']); ?>"><?= esc((string) $prov['nama_provinsi']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary mb-2" style="font-size: 0.9rem;">Kabupaten/Kota</label>
                        <select name="kode_kabupaten" id="tambah-kode-kabupaten" class="form-control select2" style="width: 100%;">
                            <option value="">-- Pilih Kabupaten (Opsional) --</option>
                            <?php foreach (($kabupatenOptions ?? []) as $kab): ?>
                                <option value="<?= esc((string) $kab['kode_kabupaten']); ?>" data-provinsi="<?= esc((string) $kab['kode_provinsi']); ?>"><?= esc((string) $kab['nama_kabupaten']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary mb-2" style="font-size: 0.9rem;">Asal (Kecamatan/Kota) <span class="text-danger">*</span></label>
                        <input type="text" name="asal" class="form-control" value="Pekanbaru" placeholder="Contoh: Pekanbaru" required style="border-radius: 6px; padding: 10px 12px;">
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary mb-2" style="font-size: 0.9rem;">Tujuan (Kecamatan/Kota) <span class="text-danger">*</span></label>
                        <input type="text" name="tujuan" id="tambah-tujuan" class="form-control" placeholder="Contoh: Kab. Siak" required style="border-radius: 6px; padding: 10px 12px;">
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-secondary mb-2" style="font-size: 0.9rem;">Besaran Tarif (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="besaran" class="form-control" placeholder="Contoh: 350000" required style="border-radius: 6px; padding: 10px 12px;">
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
                    <i class="fas fa-pen-fancy text-primary mr-2"></i>Ubah Biaya Transportasi
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="post" id="form-ubah-biaya">
                <?= csrf_field(); ?>
                <div class="modal-body py-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary mb-2" style="font-size: 0.9rem;">Provinsi</label>
                        <select name="kode_provinsi" id="ubah-kode-provinsi" class="form-control select2" style="width: 100%;">
                            <option value="">-- Pilih Provinsi (Opsional) --</option>
                            <?php foreach (($provinsiOptions ?? []) as $prov): ?>
                                <option value="<?= esc((string) $prov['kode_provinsi']); ?>"><?= esc((string) $prov['nama_provinsi']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary mb-2" style="font-size: 0.9rem;">Kabupaten/Kota</label>
                        <select name="kode_kabupaten" id="ubah-kode-kabupaten" class="form-control select2" style="width: 100%;">
                            <option value="">-- Pilih Kabupaten (Opsional) --</option>
                            <?php foreach (($kabupatenOptions ?? []) as $kab): ?>
                                <option value="<?= esc((string) $kab['kode_kabupaten']); ?>" data-provinsi="<?= esc((string) $kab['kode_provinsi']); ?>"><?= esc((string) $kab['nama_kabupaten']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary mb-2" style="font-size: 0.9rem;">Asal (Kecamatan/Kota) <span class="text-danger">*</span></label>
                        <input type="text" name="asal" id="ubah-asal" class="form-control" required style="border-radius: 6px; padding: 10px 12px;">
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary mb-2" style="font-size: 0.9rem;">Tujuan (Kecamatan/Kota) <span class="text-danger">*</span></label>
                        <input type="text" name="tujuan" id="ubah-tujuan" class="form-control" required style="border-radius: 6px; padding: 10px 12px;">
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-secondary mb-2" style="font-size: 0.9rem;">Besaran Tarif (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="besaran" id="ubah-besaran" class="form-control" required style="border-radius: 6px; padding: 10px 12px;">
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
                    <i class="fas fa-exclamation-triangle text-danger mr-2"></i>Hapus Biaya Transportasi
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="post" id="form-hapus-biaya">
                <?= csrf_field(); ?>
                <div class="modal-body py-4">
                    <p class="mb-0">Apakah Anda yakin ingin menghapus biaya transportasi dari <strong id="hapus-asal" class="text-dark"></strong> ke <strong id="hapus-tujuan" class="text-dark"></strong>?</p>
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

    // Cascading dropdowns (Provinsi -> Kabupaten) for modal Tambah
    $('#tambah-kode-provinsi').on('change', function() {
        var provId = $(this).val();
        var $kabSelect = $('#tambah-kode-kabupaten');
        
        $kabSelect.val('').trigger('change');
        $kabSelect.find('option').each(function() {
            var $opt = $(this);
            if ($opt.val() === '') {
                $opt.show();
                return;
            }
            if (provId === '' || $opt.data('provinsi') == provId) {
                $opt.show();
            } else {
                $opt.hide();
            }
        });
    });

    $('#tambah-kode-kabupaten').on('change', function() {
        var text = $(this).find('option:selected').text();
        if ($(this).val() !== '') {
            $('#tambah-tujuan').val(text);
        }
    });

    // Cascading dropdowns (Provinsi -> Kabupaten) for modal Ubah
    $('#ubah-kode-provinsi').on('change', function() {
        var provId = $(this).val();
        var $kabSelect = $('#ubah-kode-kabupaten');
        
        $kabSelect.find('option').each(function() {
            var $opt = $(this);
            if ($opt.val() === '') {
                $opt.show();
                return;
            }
            if (provId === '' || $opt.data('provinsi') == provId) {
                $opt.show();
            } else {
                $opt.hide();
            }
        });
    });

    $('#ubah-kode-kabupaten').on('change', function() {
        var text = $(this).find('option:selected').text();
        if ($(this).val() !== '') {
            $('#ubah-tujuan').val(text);
        }
    });

    // Populate Ubah modal
    $('#modal-ubah-biaya').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');
        var prov = button.data('provinsi');
        var kab = button.data('kabupaten');
        var asal = button.data('asal');
        var tujuan = button.data('tujuan');
        var besaran = button.data('besaran');
        
        var modal = $(this);
        modal.find('#form-ubah-biaya').attr('action', '<?= site_url('/admin/master/biaya/transportasi'); ?>/' + id + '/ubah');
        modal.find('#ubah-asal').val(asal);
        modal.find('#ubah-tujuan').val(tujuan);
        modal.find('#ubah-besaran').val(besaran);

        modal.find('#ubah-kode-provinsi').val(prov).trigger('change');
        modal.find('#ubah-kode-kabupaten').val(kab).trigger('change');
    });

    // Populate Hapus modal
    $('#modal-hapus-biaya').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');
        var asal = button.data('asal');
        var tujuan = button.data('tujuan');
        
        var modal = $(this);
        modal.find('#form-hapus-biaya').attr('action', '<?= site_url('/admin/master/biaya/transportasi'); ?>/' + id + '/hapus');
        modal.find('#hapus-asal').text(asal);
        modal.find('#hapus-tujuan').text(tujuan);
    });
});
</script>
<?= $this->endSection(); ?>
