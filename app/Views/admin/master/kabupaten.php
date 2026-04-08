<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Kabupaten/Kota</h3>
        <?php if (! empty($can_add)): ?>
            <div class="float-right">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-tambah-kabupaten">Tambah Kabupaten</button>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped w-100 nowrap js-datatable">
            <thead>
                <tr>
                    <th class="text-center">#</th>
                    <th class="text-center">PROVINSI</th>
                    <th class="text-center">KODE KABUPATEN</th>
                    <th class="text-center">NAMA KABUPATEN</th>
                    <?php if (! empty($can_edit)): ?>
                        <th class="text-center">ACTION</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach (($items ?? []) as $item): ?>
                    <tr>
                        <td><?= esc((string) $i++); ?></td>
                        <td><?= esc((string) (($item['kode_provinsi'] ?? '-') . ' - ' . ($item['nama_provinsi'] ?? '-'))); ?></td>
                        <td><?= esc((string) ($item['kode_kabupaten'] ?? '-')); ?></td>
                        <td><?= esc((string) ($item['nama_kabupaten'] ?? '-')); ?></td>
                        <?php if (! empty($can_edit)): ?>
                            <td class="text-center">
                                <button
                                    type="button"
                                    class="btn btn-warning btn-sm"
                                    data-toggle="modal"
                                    data-target="#modal-ubah-kabupaten"
                                    data-kode-provinsi="<?= esc((string) ($item['kode_provinsi'] ?? ''), 'attr'); ?>"
                                    data-kode-kabupaten="<?= esc((string) ($item['kode_kabupaten'] ?? ''), 'attr'); ?>"
                                    data-nama-kabupaten="<?= esc((string) ($item['nama_kabupaten'] ?? ''), 'attr'); ?>"
                                >UBAH</button>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (! empty($can_add)): ?>
<div class="modal fade" id="modal-tambah-kabupaten" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Kabupaten/Kota</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('/admin/master/kabupaten/tambah'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Provinsi</label>
                        <select name="kode_provinsi" class="form-control" required>
                            <option value="">Pilih Provinsi</option>
                            <?php foreach (($provinsiOptions ?? []) as $prov): ?>
                                <option value="<?= esc((string) ($prov['kode_provinsi'] ?? '')); ?>"><?= esc((string) (($prov['kode_provinsi'] ?? '') . ' - ' . ($prov['nama_provinsi'] ?? ''))); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kode Kabupaten</label>
                        <input type="text" name="kode_kabupaten" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Kabupaten/Kota</label>
                        <input type="text" name="nama_kabupaten" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (! empty($can_edit)): ?>
<div class="modal fade" id="modal-ubah-kabupaten" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Kabupaten/Kota</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-ubah-kabupaten" action="" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Provinsi</label>
                        <select id="edit_kode_provinsi" name="kode_provinsi" class="form-control" required>
                            <option value="">Pilih Provinsi</option>
                            <?php foreach (($provinsiOptions ?? []) as $prov): ?>
                                <option value="<?= esc((string) ($prov['kode_provinsi'] ?? '')); ?>"><?= esc((string) (($prov['kode_provinsi'] ?? '') . ' - ' . ($prov['nama_provinsi'] ?? ''))); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kode Kabupaten</label>
                        <input type="text" id="edit_kode_kabupaten" name="kode_kabupaten" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Kabupaten/Kota</label>
                        <input type="text" id="edit_nama_kabupaten" name="nama_kabupaten" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script>
    (function () {
        const modalEdit = document.getElementById('modal-ubah-kabupaten');
        if (!modalEdit) return;

        const form = document.getElementById('form-ubah-kabupaten');
        const editProvinsi = document.getElementById('edit_kode_provinsi');
        const editKabupaten = document.getElementById('edit_kode_kabupaten');
        const editNama = document.getElementById('edit_nama_kabupaten');

        modalEdit.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            if (!trigger) return;

            const oldProvinsi = trigger.getAttribute('data-kode-provinsi') || '';
            const oldKabupaten = trigger.getAttribute('data-kode-kabupaten') || '';
            editProvinsi.value = oldProvinsi;
            editKabupaten.value = oldKabupaten;
            editNama.value = trigger.getAttribute('data-nama-kabupaten') || '';
            form.action = '<?= site_url('/admin/master/kabupaten'); ?>/' + encodeURIComponent(oldProvinsi) + '/' + encodeURIComponent(oldKabupaten) + '/ubah';
        });
    })();
</script>
<?= $this->endSection(); ?>
