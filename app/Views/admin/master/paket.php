<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Paket</h3>
        <?php if (! empty($can_add)): ?>
            <div class="float-right">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-tambah-paket">Tambah Paket</button>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped w-100 nowrap js-datatable">
            <thead>
                <tr style="white-space: nowrap;">
                    <th class="text-center">#</th>
                    <th class="text-center">NAMA PAKET</th>
                    <th class="text-center">SINGKATAN PAKET</th>
                    <?php if (! empty($can_edit)): ?>
                        <th class="text-center">AKSI</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach (($items ?? []) as $item): ?>
                    <tr>
                        <td><?= esc((string) $i++); ?></td>
                        <td><?= esc((string) ($item['nama_paket'] ?? '-')); ?></td>
                        <td><?= esc((string) ($item['singkatan_paket'] ?? '-')); ?></td>
                        <?php if (! empty($can_edit)): ?>
                            <td class="text-center" style="white-space: nowrap;">
                                <button
                                    type="button"
                                    class="btn btn-warning btn-sm"
                                    data-toggle="modal"
                                    data-target="#modal-ubah-paket"
                                    data-id="<?= esc((string) ($item['id'] ?? ''), 'attr'); ?>"
                                    data-nama_paket="<?= esc((string) ($item['nama_paket'] ?? ''), 'attr'); ?>"
                                    data-singkatan_paket="<?= esc((string) ($item['singkatan_paket'] ?? ''), 'attr'); ?>"
                                >EDIT</button>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (! empty($can_add)): ?>
<div class="modal fade" id="modal-tambah-paket" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Paket</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('/admin/master/paket/tambah'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Paket</label>
                        <input type="text" name="nama_paket" class="form-control" required maxlength="255" placeholder="Masukkan nama paket">
                    </div>
                    <div class="form-group mb-0">
                        <label>Singkatan Paket</label>
                        <input type="text" name="singkatan_paket" class="form-control" required maxlength="50" placeholder="Masukkan singkatan paket">
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
<div class="modal fade" id="modal-ubah-paket" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Paket</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-ubah-paket" action="" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Paket</label>
                        <input type="text" id="edit_nama_paket" name="nama_paket" class="form-control" required maxlength="255">
                    </div>
                    <div class="form-group mb-0">
                        <label>Singkatan Paket</label>
                        <input type="text" id="edit_singkatan_paket" name="singkatan_paket" class="form-control" required maxlength="50">
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
        const modalEdit = document.getElementById('modal-ubah-paket');
        if (!modalEdit) return;

        const form = document.getElementById('form-ubah-paket');
        const fieldNamaPaket = document.getElementById('edit_nama_paket');
        const fieldSingkatanPaket = document.getElementById('edit_singkatan_paket');

        const applyEditData = (trigger) => {
            if (!trigger) {
                return;
            }

            const id = trigger.getAttribute('data-id') || '';
            form.action = '<?= site_url('/admin/master/paket'); ?>/' + encodeURIComponent(id) + '/ubah';
            fieldNamaPaket.value = trigger.getAttribute('data-nama_paket') || '';
            fieldSingkatanPaket.value = trigger.getAttribute('data-singkatan_paket') || '';
        };

        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('button[data-target="#modal-ubah-paket"]');
            if (!trigger) {
                return;
            }

            applyEditData(trigger);
        });

        modalEdit.addEventListener('show.bs.modal', function (event) {
            applyEditData(event.relatedTarget);
        });
    })();
</script>
<?= $this->endSection(); ?>
