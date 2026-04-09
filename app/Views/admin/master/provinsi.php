<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Provinsi</h3>
        <?php if (! empty($can_add)): ?>
            <div class="float-right">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-tambah-provinsi">Tambah Provinsi</button>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped w-100 nowrap js-datatable">
            <thead>
                <tr>
                    <th class="text-center">#</th>
                    <th class="text-center">KODE</th>
                    <th class="text-center">NAMA PROVINSI</th>
                    <?php if (! empty($can_edit)): ?>
                        <th class="text-center">ACTION</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach (($items ?? []) as $item): ?>
                    <tr>
                        <td><?= esc((string) $i++); ?></td>
                        <td><?= esc((string) ($item['kode_provinsi'] ?? '-')); ?></td>
                        <td><?= esc((string) ($item['nama_provinsi'] ?? '-')); ?></td>
                        <?php if (! empty($can_edit)): ?>
                            <td class="text-center">
                                <button
                                    type="button"
                                    class="btn btn-warning btn-sm"
                                    data-toggle="modal"
                                    data-target="#modal-ubah-provinsi"
                                    data-kode="<?= esc((string) ($item['kode_provinsi'] ?? ''), 'attr'); ?>"
                                    data-nama="<?= esc((string) ($item['nama_provinsi'] ?? ''), 'attr'); ?>"
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
<div class="modal fade" id="modal-tambah-provinsi" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Provinsi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('/admin/master/provinsi/tambah'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Kode Provinsi</label>
                        <input type="text" name="kode_provinsi" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Provinsi</label>
                        <input type="text" name="nama_provinsi" class="form-control" required>
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
<div class="modal fade" id="modal-ubah-provinsi" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Provinsi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-ubah-provinsi" action="" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Kode Provinsi</label>
                        <input type="text" id="edit_kode_provinsi" name="kode_provinsi" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Provinsi</label>
                        <input type="text" id="edit_nama_provinsi" name="nama_provinsi" class="form-control" required>
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
        const modalEdit = document.getElementById('modal-ubah-provinsi');
        if (!modalEdit) return;

        const form = document.getElementById('form-ubah-provinsi');
        const editKode = document.getElementById('edit_kode_provinsi');
        const editNama = document.getElementById('edit_nama_provinsi');

        modalEdit.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            if (!trigger) return;

            const kode = trigger.getAttribute('data-kode') || '';
            form.reset();
            editKode.value = kode;
            editNama.value = trigger.getAttribute('data-nama') || '';
            form.action = '<?= site_url('/admin/master/provinsi'); ?>/' + encodeURIComponent(kode) + '/ubah';
        });
    })();
</script>
<?= $this->endSection(); ?>
