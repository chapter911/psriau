<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
$menuPermissions = is_array($currentMenuPermissions ?? null) ? $currentMenuPermissions : [];
$canEditBase = (bool) ($can_edit ?? false);
$canAddFeature = (bool) ($menuPermissions['add'] ?? false);
$canEditFeature = (bool) ($menuPermissions['edit'] ?? false);
$canAdd = $canEditBase && $canAddFeature;
$canEditAction = $canEditBase && $canEditFeature;
?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Daftar Kop Surat</h2>
        <?php if ($canAdd): ?>
            <div class="float-right">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-tambah-kop-surat">Tambah Kop Surat</button>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped w-100 nowrap js-datatable">
            <thead>
                <tr style="white-space: nowrap;">
                    <th class="text-center">#</th>
                    <th class="text-center">JUDUL</th>
                    <th class="text-center">GAMBAR</th>
                    <th class="text-center">STATUS</th>
                    <?php if ($canEditAction): ?>
                        <th class="text-center">ACTION</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach (($items ?? []) as $item): ?>
                    <tr>
                        <td><?= esc((string) $i++); ?></td>
                        <td>
                            <div class="font-weight-bold"><?= esc((string) ($item['title'] ?? '-')); ?></div>
                        </td>
                        <td class="text-center">
                            <?php if (! empty($item['image_url'] ?? '')): ?>
                                <button
                                    type="button"
                                    class="btn btn-info btn-sm js-preview-kop-surat"
                                    title="Lihat"
                                    data-image-url="<?= esc(base_url((string) ($item['image_url'] ?? '')), 'attr'); ?>"
                                    data-title="<?= esc((string) ($item['title'] ?? 'Kop Surat'), 'attr'); ?>"
                                ><i class="fas fa-eye"></i></button>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ((int) ($item['is_active'] ?? 0) === 1): ?>
                                <span class="badge badge-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <?php if ($canEditAction): ?>
                            <td class="text-center" style="white-space: nowrap;">
                                <button
                                    type="button"
                                    class="btn btn-warning btn-sm"
                                    data-toggle="modal"
                                    data-target="#modal-ubah-kop-surat"
                                    data-id="<?= esc((string) ((int) ($item['id'] ?? 0))); ?>"
                                    data-title="<?= esc((string) ($item['title'] ?? ''), 'attr'); ?>"
                                    data-description="<?= esc((string) ($item['description'] ?? ''), 'attr'); ?>"
                                    data-image-url="<?= esc(base_url((string) ($item['image_url'] ?? ''))); ?>"
                                    data-is-active="<?= esc((string) ((int) ($item['is_active'] ?? 0))); ?>"
                                >UBAH</button>
                                <?php if ((int) ($item['is_active'] ?? 0) === 1): ?>
                                    <form
                                        action="<?= site_url('/admin/master/kop-surat/' . (int) ($item['id'] ?? 0) . '/status'); ?>"
                                        method="post"
                                        class="inline-form"
                                        data-confirm-title="Nonaktifkan Kop Surat"
                                        data-confirm-text="Yakin ingin menonaktifkan kop surat ini?"
                                        data-confirm-button="Ya, nonaktifkan"
                                    >
                                        <?= csrf_field(); ?>
                                        <input type="hidden" name="is_active" value="0">
                                        <button type="submit" class="btn btn-secondary btn-sm">TIDAK AKTIF</button>
                                    </form>
                                <?php else: ?>
                                    <form
                                        action="<?= site_url('/admin/master/kop-surat/' . (int) ($item['id'] ?? 0) . '/status'); ?>"
                                        method="post"
                                        class="inline-form"
                                        data-confirm-title="Aktifkan Kop Surat"
                                        data-confirm-text="Yakin ingin mengaktifkan kop surat ini? Kop surat aktif lainnya akan dinonaktifkan."
                                        data-confirm-button="Ya, aktifkan"
                                    >
                                        <?= csrf_field(); ?>
                                        <input type="hidden" name="is_active" value="1">
                                        <button type="submit" class="btn btn-success btn-sm">AKTIF</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modal-lihat-kop-surat" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-lihat-kop-surat-title">Preview Kop Surat</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img
                    id="modal-lihat-kop-surat-image"
                    src=""
                    alt="Preview Kop Surat"
                    class="img-fluid"
                    style="max-height: 75vh;"
                >
            </div>
        </div>
    </div>
</div>

<?php if ($canAdd): ?>
<div class="modal fade" id="modal-tambah-kop-surat" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Kop Surat</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('/admin/master/kop-surat/tambah'); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="title">Judul</label>
                        <input type="text" id="title" name="title" class="form-control" required>
                        <small class="form-text text-muted">Contoh: Kop Surat Kantor Pusat, Kop Surat Cabang, dan lainnya.</small>
                    </div>

                    <div class="form-group">
                        <label for="description">Keterangan</label>
                        <textarea id="description" name="description" rows="2" class="form-control" placeholder="Opsional"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="image_file">File Gambar Kop Surat <span class="text-danger">*</span></label>
                        <input type="file" id="image_file" name="image_file" class="form-control" accept="image/*" required>
                        <small class="form-text text-muted">Format: JPG, PNG, WebP, SVG (maksimal 4 MB).</small>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input" checked>
                        <label class="form-check-label" for="is_active">Aktifkan kop surat ini</label>
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

<?php if ($canEditAction): ?>
<div class="modal fade" id="modal-ubah-kop-surat" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Kop Surat</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-ubah-kop-surat" action="" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_title">Judul</label>
                        <input type="text" id="edit_title" name="title" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_description">Keterangan</label>
                        <textarea id="edit_description" name="description" rows="2" class="form-control" placeholder="Opsional"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="edit_image_file">File Gambar Kop Surat</label>
                        <input type="file" id="edit_image_file" name="image_file" class="form-control" accept="image/*">
                        <small class="form-text text-muted">Kosongkan jika tidak ingin mengganti gambar.</small>
                    </div>

                    <div class="mb-3" id="edit_current_image_wrap" style="display: none;">
                        <small class="d-block text-muted mb-1">Gambar saat ini</small>
                        <img id="edit_current_image" src="" alt="Gambar saat ini" class="img-fluid border rounded" style="max-height: 140px;">
                    </div>

                    <div class="form-check">
                        <input type="checkbox" id="edit_is_active" name="is_active" value="1" class="form-check-input">
                        <label class="form-check-label" for="edit_is_active">Aktifkan kop surat ini</label>
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
        const modalTitle = document.getElementById('modal-lihat-kop-surat-title');
        const modalImage = document.getElementById('modal-lihat-kop-surat-image');
        const modalElement = document.getElementById('modal-lihat-kop-surat');

        if (modalTitle && modalImage && modalElement) {
            document.addEventListener('click', function (event) {
                const trigger = event.target.closest('.js-preview-kop-surat');
                if (!trigger) return;

                const imageUrl = trigger.getAttribute('data-image-url') || '';
                const title = trigger.getAttribute('data-title') || 'Preview Kop Surat';

                modalTitle.textContent = title;
                modalImage.src = imageUrl;
                modalImage.alt = 'Preview ' + title;

                if (window.jQuery && window.jQuery.fn && window.jQuery.fn.modal) {
                    window.jQuery('#modal-lihat-kop-surat').modal('show');
                }
            });
        }

        const modalEdit = document.getElementById('modal-ubah-kop-surat');
        if (!modalEdit) return;

        const editForm = document.getElementById('form-ubah-kop-surat');
        const editTitle = document.getElementById('edit_title');
        const editDescription = document.getElementById('edit_description');
        const editIsActive = document.getElementById('edit_is_active');
        const editCurrentImageWrap = document.getElementById('edit_current_image_wrap');
        const editCurrentImage = document.getElementById('edit_current_image');
        const editImageFile = document.getElementById('edit_image_file');

        modalEdit.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            if (!trigger) return;

            const id = trigger.getAttribute('data-id') || '';
            const title = trigger.getAttribute('data-title') || '';
            const description = trigger.getAttribute('data-description') || '';
            const imageUrl = trigger.getAttribute('data-image-url') || '';
            const isActive = trigger.getAttribute('data-is-active') === '1';

            editForm.setAttribute('action', '<?= site_url('/admin/master/kop-surat'); ?>/' + id + '/ubah');
            editTitle.value = title;
            editDescription.value = description;
            editIsActive.checked = isActive;

            if (imageUrl) {
                editCurrentImage.src = imageUrl;
                editCurrentImageWrap.style.display = 'block';
            } else {
                editCurrentImage.src = '';
                editCurrentImageWrap.style.display = 'none';
            }

            editImageFile.value = '';
        });
    })();
</script>
<?= $this->endSection(); ?>
