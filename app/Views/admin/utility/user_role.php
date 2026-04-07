<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
$roles = $roles ?? [];
$canEdit = (bool) ($can_edit ?? false);
?>
<div class="card card-outline card-primary mb-4">
    <div class="card-header d-flex align-items-center flex-wrap">
        <div>
            <h3 class="mb-0">User Role</h3>
            <p class="text-muted mb-0">Atur akses menu untuk setiap role.</p>
        </div>
        <div class="ml-auto d-flex align-items-center text-right">
            <span class="badge badge-light border mr-2">Tersedia <?= count($roles); ?> role</span>
            <?php if ($canEdit): ?>
                <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalTambahRole">
                    <i class="fas fa-plus mr-1"></i>Tambah Role
                </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <?php foreach ($roles as $role): ?>
                <div class="col-12 col-md-6 col-xl-4 mb-3 d-flex">
                    <div class="card border w-100">
                        <div class="card-body d-flex flex-column">
                            <h4 class="h5 mb-1"><?= esc((string) ($role['label'] ?? 'Role')); ?></h4>
                            <div class="text-muted mb-3">Key: <?= esc((string) ($role['role'] ?? '-')); ?></div>
                            <p class="text-muted flex-grow-1">Role ini menentukan menu yang tampil di sidebar admin.</p>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="badge badge-light border"><?= (int) ($role['menu_count'] ?? 0); ?> menu aktif</span>
                                <button type="button" class="btn btn-sm btn-primary js-open-access" data-role-id="<?= esc((string) ($role['role_id'] ?? '0')); ?>">Atur Akses</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($roles === []): ?>
            <div class="alert alert-info mb-0">Belum ada role yang dapat dikonfigurasi.</div>
        <?php endif; ?>
    </div>
</div>

<?php if ($canEdit): ?>
<div class="modal fade" id="modalTambahRole" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= site_url('admin/utility/role/tambah'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="modal-header">
                    <h5 class="modal-title mb-0">Tambah Role</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="role_label">Nama Role</label>
                        <input type="text" class="form-control" id="role_label" name="label" required>
                    </div>
                    <div class="form-group mb-0">
                        <label for="role_key">Key Role</label>
                        <input type="text" class="form-control" id="role_key" name="role_key" placeholder="contoh: verifikator" required>
                        <small class="text-muted">Huruf kecil, angka, underscore, dash.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="modal fade" id="modalRoleAccess" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0">Atur Akses Menu</h5>
                    <small class="text-muted" id="roleAccessSubtitle">Memuat data role...</small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form id="formRoleAccess">
                <?= csrf_field(); ?>
                <input type="hidden" name="role_id" id="roleIdInput" value="">
                <div class="modal-body">
                    <div class="alert alert-danger d-none" id="roleAccessError"></div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="min-width:280px;">Menu</th>
                                    <th class="text-center">Akses</th>
                                    <th class="text-center">Add</th>
                                    <th class="text-center">Edit</th>
                                    <th class="text-center">Delete</th>
                                    <th class="text-center">Approval</th>
                                    <th class="text-center">Export</th>
                                    <th class="text-center">Import</th>
                                </tr>
                            </thead>
                            <tbody id="roleAccessBody">
                                <tr><td colspan="8" class="text-center text-muted py-4">Pilih role untuk melihat daftar menu.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer d-flex align-items-center justify-content-between">
                    <div class="small text-muted">Centang akses utama untuk menampilkan menu di sidebar.</div>
                    <div class="ml-3 text-nowrap">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary" id="saveRoleAccessBtn" <?= $canEdit ? '' : 'disabled'; ?>>Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<style>
    #modalRoleAccess .modal-content {
        max-height: calc(100vh - 2rem);
    }

    #modalRoleAccess .modal-body {
        overflow-y: auto;
        max-height: calc(100vh - 220px);
    }
</style>
<script>
(() => {
    const canEdit = <?= $canEdit ? 'true' : 'false' ?>;
    const modal = $('#modalRoleAccess');
    const form = document.getElementById('formRoleAccess');
    const body = document.getElementById('roleAccessBody');
    const errorBox = document.getElementById('roleAccessError');
    const subtitle = document.getElementById('roleAccessSubtitle');
    const roleIdInput = document.getElementById('roleIdInput');
    const csrfInput = form.querySelector('input[name="<?= csrf_token() ?>"]');
    const saveButton = document.getElementById('saveRoleAccessBtn');

    const routes = {
        access: '<?= site_url('admin/utility/role/access') ?>',
        save: '<?= site_url('admin/utility/role/access/save') ?>',
    };

    const setCsrf = (hash) => { if (hash) csrfInput.value = hash; };
    const showError = (message) => {
        if (!message) {
            errorBox.classList.add('d-none');
            errorBox.textContent = '';
            return;
        }
        errorBox.textContent = message;
        errorBox.classList.remove('d-none');
    };
    const escapeHtml = (value) => String(value == null ? '' : value)
        .replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');

    const checkboxCell = (name, menuId, checked, disabled) => '<div class="custom-control custom-checkbox custom-control-inline m-0">'
        + '<input type="checkbox" class="custom-control-input js-' + name + '" id="' + name + '-' + menuId + '" name="' + name + '[' + menuId + ']" value="1" ' + (checked ? 'checked' : '') + (disabled ? ' disabled' : '') + '>'
        + '<label class="custom-control-label" for="' + name + '-' + menuId + '"></label></div>';

    const accessCell = (menuId, checked) => '<div class="custom-control custom-checkbox custom-control-inline m-0">'
        + '<input type="checkbox" class="custom-control-input js-akses" id="akses-' + menuId + '" name="akses[' + menuId + ']" value="1" ' + (checked ? 'checked' : '') + '>'
        + '<label class="custom-control-label" for="akses-' + menuId + '"></label></div>';

    const syncFeatureState = (menuId, enabled) => {
        const row = body.querySelector('tr[data-menu-id="' + menuId + '"]');
        if (!row) return;
        row.querySelectorAll('.js-fitur_add,.js-fitur_edit,.js-fitur_delete,.js-fitur_approval,.js-fitur_export,.js-fitur_import').forEach((input) => {
            input.disabled = !enabled;
        });
    };

    const setAccessState = (menuId, enabled) => {
        const accessInput = body.querySelector('#akses-' + menuId);
        if (!accessInput) return;
        accessInput.checked = enabled;
        syncFeatureState(menuId, enabled);
        const row = accessInput.closest('tr');
        if (row && enabled) {
            row.querySelectorAll('.js-fitur_add,.js-fitur_edit,.js-fitur_delete,.js-fitur_approval,.js-fitur_export,.js-fitur_import').forEach((feature) => {
                feature.checked = true;
            });
        }
        if (!enabled) {
            if (row) {
                row.querySelectorAll('.js-fitur_add,.js-fitur_edit,.js-fitur_delete,.js-fitur_approval,.js-fitur_export,.js-fitur_import').forEach((feature) => {
                    feature.checked = false;
                });
            }
        }
    };

    const syncParentAccess = (menuId) => {
        const parts = String(menuId || '').split('-');
        for (let i = 1; i < parts.length; i += 1) {
            setAccessState(parts.slice(0, i).join('-'), true);
        }
    };

    const syncChildAccess = (menuId, enabled) => {
        body.querySelectorAll('tr[data-menu-id^="' + menuId + '-"] .js-akses').forEach((input) => {
            setAccessState(input.id.replace('akses-', ''), enabled);
        });
    };

    const bindRowEvents = () => {
        body.querySelectorAll('.js-akses').forEach((input) => {
            input.addEventListener('change', () => {
                const menuId = input.id.replace('akses-', '');
                setAccessState(menuId, input.checked);
                if (input.checked) syncParentAccess(menuId);
                else syncChildAccess(menuId, false);
            });
        });
    };

    const renderRows = (rows) => {
        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">Tidak ada menu yang tersedia.</td></tr>';
            return;
        }

        body.innerHTML = rows.map((row) => {
            const level = Number(row.level || 1);
            const padding = level === 1 ? '' : 'style="padding-left:' + ((level - 1) * 24) + 'px"';
            const label = level === 1 ? '<strong>' + escapeHtml(row.label || '-') + '</strong>' : escapeHtml(row.label || '-');
            const checked = Boolean(row.akses);
            return '<tr data-menu-id="' + escapeHtml(row.menu_id || '') + '">'
                + '<td ' + padding + '>' + label + '</td>'
                + '<td class="text-center"><input type="hidden" name="menu_ids[]" value="' + escapeHtml(row.menu_id || '') + '">' + accessCell(row.menu_id, checked) + '</td>'
                + '<td class="text-center">' + checkboxCell('fitur_add', row.menu_id, Boolean(row.add), !checked) + '</td>'
                + '<td class="text-center">' + checkboxCell('fitur_edit', row.menu_id, Boolean(row.edit), !checked) + '</td>'
                + '<td class="text-center">' + checkboxCell('fitur_delete', row.menu_id, Boolean(row.delete), !checked) + '</td>'
                + '<td class="text-center">' + checkboxCell('fitur_approval', row.menu_id, Boolean(row.approval), !checked) + '</td>'
                + '<td class="text-center">' + checkboxCell('fitur_export', row.menu_id, Boolean(row.export), !checked) + '</td>'
                + '<td class="text-center">' + checkboxCell('fitur_import', row.menu_id, Boolean(row.import), !checked) + '</td>'
                + '</tr>';
        }).join('');

        bindRowEvents();
        body.querySelectorAll('.js-akses:checked').forEach((input) => syncParentAccess(input.id.replace('akses-', '')));
    };

    const loadRoleAccess = async (roleId) => {
        showError('');
        subtitle.textContent = 'Memuat data role...';
        body.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">Memuat data...</td></tr>';
        roleIdInput.value = roleId;

        const response = await fetch(routes.access + '/' + roleId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        });
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || 'Gagal memuat akses role.');
        }

        subtitle.textContent = 'Role: ' + (data.role?.label || '-') + ' | Key: ' + (data.role?.role || '-');
        renderRows(Array.isArray(data.rows) ? data.rows : []);
    };

    document.querySelectorAll('.js-open-access').forEach((button) => {
        button.addEventListener('click', () => {
            const roleId = button.dataset.roleId;
            modal.modal('show');

            if (!canEdit) {
                showError('Mode lihat saja. Hanya admin yang dapat mengubah akses menu.');
                subtitle.textContent = 'Akses hanya baca';
                body.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">Anda hanya dapat melihat daftar akses menu.</td></tr>';
                return;
            }

            if (!roleId) {
                showError('Role tidak ditemukan.');
                subtitle.textContent = 'Role tidak valid';
                body.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">Role tidak ditemukan.</td></tr>';
                return;
            }

            loadRoleAccess(roleId).catch((error) => {
                showError(error.message || 'Gagal memuat akses role.');
                subtitle.textContent = 'Gagal memuat role';
                body.innerHTML = '<tr><td colspan="8" class="text-center text-danger py-4">' + escapeHtml(error.message || 'Gagal memuat akses role.') + '</td></tr>';
            });
        });
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!canEdit) return;

        showError('');
        saveButton.disabled = true;
        saveButton.textContent = 'Menyimpan...';

        try {
            const response = await fetch(routes.save, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: new FormData(form),
            });
            const data = await response.json();
            setCsrf(data.csrfHash || '');

            if (!response.ok || data.status !== 'ok') {
                throw new Error(data.message || 'Gagal menyimpan akses role.');
            }

            window.location.reload();
        } catch (error) {
            showError(error.message || 'Gagal menyimpan akses role.');
        } finally {
            saveButton.disabled = !canEdit;
            saveButton.textContent = 'Simpan';
        }
    });
})();
</script>
<?= $this->endSection(); ?>
