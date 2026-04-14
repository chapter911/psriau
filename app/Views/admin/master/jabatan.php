<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Jabatan</h3>
        <?php if (! empty($can_add) || ! empty($can_import)): ?>
            <div class="float-right">
                <?php if (! empty($can_import)): ?>
                    <button type="button" class="btn btn-info mr-2" data-toggle="modal" data-target="#modal-import-jabatan">Import Excel</button>
                <?php endif; ?>
                <?php if (! empty($can_add)): ?>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-tambah-jabatan">Tambah Jabatan</button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped w-100 nowrap js-datatable">
            <thead>
                <tr style="white-space: nowrap;">
                    <th class="text-center">#</th>
                    <th class="text-center">JABATAN</th>
                    <th class="text-center">JENIS JABATAN</th>
                    <th class="text-center">DESKRIPSI JABATAN</th>
                    <th class="text-center">STATUS</th>
                    <?php if (! empty($can_edit)): ?>
                        <th class="text-center">ACTION</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach (($items ?? []) as $item): ?>
                    <?php $isActive = (int) ($item['is_active'] ?? 1) === 1; ?>
                    <tr>
                        <td><?= esc((string) $i++); ?></td>
                        <td><?= esc((string) ($item['jabatan'] ?? '-')); ?></td>
                        <td><?= esc((string) ($item['jenis_jabatan'] ?? '-')); ?></td>
                        <td><?= esc((string) ($item['deskripsi_jabatan'] ?? '-')); ?></td>
                        <td class="text-center">
                            <?php if ($isActive): ?>
                                <span class="badge badge-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <?php if (! empty($can_edit)): ?>
                            <td class="text-center" style="white-space: nowrap;">
                                <button
                                    type="button"
                                    class="btn btn-warning btn-sm"
                                    data-toggle="modal"
                                    data-target="#modal-ubah-jabatan"
                                    data-id="<?= esc((string) ($item['id'] ?? ''), 'attr'); ?>"
                                    data-jabatan="<?= esc((string) ($item['jabatan'] ?? ''), 'attr'); ?>"
                                    data-jenis_jabatan="<?= esc((string) ($item['jenis_jabatan'] ?? ''), 'attr'); ?>"
                                    data-deskripsi_jabatan="<?= esc((string) ($item['deskripsi_jabatan'] ?? ''), 'attr'); ?>"
                                >UBAH</button>

                                <form action="<?= site_url('/admin/master/jabatan/' . (int) ($item['id'] ?? 0) . '/status'); ?>" method="post" class="d-inline-block" onsubmit="return confirm('Yakin ingin mengubah status jabatan ini?');">
                                    <?= csrf_field(); ?>
                                    <input type="hidden" name="is_active" value="<?= $isActive ? '0' : '1'; ?>">
                                    <button type="submit" class="btn btn-sm <?= $isActive ? 'btn-secondary' : 'btn-success'; ?>">
                                        <?= $isActive ? 'NONAKTIFKAN' : 'AKTIFKAN'; ?>
                                    </button>
                                </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (! empty($can_import)): ?>
<div class="modal fade" id="modal-import-jabatan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Jabatan (Excel)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('/admin/master/jabatan/import'); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        Kolom wajib: <strong>jabatan</strong>, <strong>jenis_jabatan</strong>.<br>
                        Kolom opsional: <strong>deskripsi_jabatan</strong>, <strong>status</strong> (aktif/nonaktif atau 1/0).
                    </div>
                    <div class="mb-3">
                        <a href="<?= site_url('/admin/master/jabatan/template'); ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-download mr-1"></i> Download Template Excel
                        </a>
                    </div>
                    <div class="form-group mb-0">
                        <label for="file_excel_jabatan">File Excel</label>
                        <input type="file" class="form-control" id="file_excel_jabatan" name="file_excel" accept=".xls,.xlsx,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                        <small class="text-muted">Jika nama jabatan sudah ada, data akan diperbarui. Jika belum ada, data baru akan ditambahkan.</small>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (! empty($can_add)): ?>
<div class="modal fade" id="modal-tambah-jabatan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Jabatan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('/admin/master/jabatan/tambah'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Jabatan</label>
                        <input type="text" name="jabatan" class="form-control" required maxlength="150">
                    </div>
                    <div class="form-group">
                        <label>Jenis Jabatan</label>
                        <select name="jenis_jabatan" class="form-control" required>
                            <option value="">Pilih jenis jabatan</option>
                            <option value="Fungsional">Fungsional</option>
                            <option value="Perbendaharaan">Perbendaharaan</option>
                            <option value="Pelaksana">Pelaksana</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label>Deskripsi Jabatan</label>
                        <textarea name="deskripsi_jabatan" class="form-control" rows="3" maxlength="1000"></textarea>
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
<div class="modal fade" id="modal-ubah-jabatan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Jabatan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-ubah-jabatan" action="" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Jabatan</label>
                        <input type="text" id="edit_jabatan" name="jabatan" class="form-control" required maxlength="150">
                    </div>
                    <div class="form-group">
                        <label>Jenis Jabatan</label>
                        <select id="edit_jenis_jabatan" name="jenis_jabatan" class="form-control" required>
                            <option value="Fungsional">Fungsional</option>
                            <option value="Perbendaharaan">Perbendaharaan</option>
                            <option value="Pelaksana">Pelaksana</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label>Deskripsi Jabatan</label>
                        <textarea id="edit_deskripsi_jabatan" name="deskripsi_jabatan" class="form-control" rows="3" maxlength="1000"></textarea>
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
        const modalEdit = document.getElementById('modal-ubah-jabatan');
        if (!modalEdit) return;

        const form = document.getElementById('form-ubah-jabatan');
        const fieldJabatan = document.getElementById('edit_jabatan');
        const fieldJenisJabatan = document.getElementById('edit_jenis_jabatan');
        const fieldDeskripsiJabatan = document.getElementById('edit_deskripsi_jabatan');

        const applyEditData = (trigger) => {
            if (!trigger) {
                return;
            }

            const id = trigger.getAttribute('data-id') || '';
            form.action = '<?= site_url('/admin/master/jabatan'); ?>/' + encodeURIComponent(id) + '/ubah';
            fieldJabatan.value = trigger.getAttribute('data-jabatan') || '';
            fieldJenisJabatan.value = trigger.getAttribute('data-jenis_jabatan') || 'Pelaksana';
            fieldDeskripsiJabatan.value = trigger.getAttribute('data-deskripsi_jabatan') || '';
        };

        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('button[data-target="#modal-ubah-jabatan"]');
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