<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
$groups = $groups ?? [];
$canEdit = (bool) ($can_edit ?? false);
?>
<style>
    .user-group-header-title {
        margin: 0;
        font-size: 1.9rem;
        font-weight: 700;
        line-height: 1.2;
        color: #212529;
    }

    .user-group-header-subtitle {
        margin: 0.45rem 0 0;
        max-width: 560px;
        color: #6c757d;
        font-size: 1.05rem;
        line-height: 1.45;
    }

    .user-group-card {
        border-radius: 0.55rem;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
    }

    .user-group-card .card-body {
        padding: 1.5rem;
    }

    .user-group-role {
        font-size: 0.95rem;
    }

    .user-group-summary {
        font-size: 1.05rem;
        line-height: 1.6;
    }

    .user-group-header-badge {
        margin-left: auto;
        text-align: right;
    }

    @media (max-width: 767.98px) {
        .user-group-header-title {
            font-size: 1.5rem;
        }

        .user-group-header-subtitle {
            font-size: 0.95rem;
        }

        .user-group-card .card-body {
            padding: 1.1rem;
        }

        .user-group-header-badge {
            width: 100%;
            margin-top: 0.75rem;
            text-align: right;
        }
    }
</style>
<div class="card card-outline card-primary mb-4">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
        <div>
            <h3 class="user-group-header-title">User Group</h3>
            <p class="user-group-header-subtitle">Atur menu yang dapat diakses oleh setiap role user.</p>
        </div>
        <div class="text-right user-group-header-badge">
            <span class="badge badge-light border">Tersedia <?= count($groups); ?> group</span>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <?php foreach ($groups as $group): ?>
                <div class="col-12 col-md-6 col-xl-4 mb-3 d-flex">
                    <div class="card user-group-card h-100 border w-100">
                        <div class="card-body d-flex flex-column">
                            <div class="mb-3">
                                <div>
                                    <h4 class="h5 mb-1"><?= esc((string) ($group['label'] ?? 'Group')); ?></h4>
                                    <div class="text-muted user-group-role">Role: <?= esc((string) ($group['role'] ?? '-')); ?></div>
                                </div>
                            </div>
                            <p class="text-muted user-group-summary flex-grow-1 mb-3">
                                Group ini mengacu ke tabel akses menu dan akan menentukan menu yang tampil di sidebar.
                            </p>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="badge badge-light border"><?= (int) ($group['menu_count'] ?? 0); ?> menu aktif</span>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-primary js-open-access"
                                    data-group-id="<?= esc((string) ($group['group_id'] ?? '0')); ?>"
                                >
                                    Atur Akses
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($groups === []): ?>
            <div class="alert alert-info mb-0">Belum ada group yang dapat dikonfigurasi.</div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="modalGroupAccess" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0">Atur Akses Menu</h5>
                    <small class="text-muted" id="groupAccessSubtitle">Memuat data group...</small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formGroupAccess">
                <?= csrf_field(); ?>
                <input type="hidden" name="group_id" id="groupIdInput" value="">
                <div class="modal-body">
                    <div class="alert alert-danger d-none" id="groupAccessError"></div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="min-width: 280px;">Menu</th>
                                    <th class="text-center">Akses</th>
                                    <th class="text-center">Add</th>
                                    <th class="text-center">Edit</th>
                                    <th class="text-center">Delete</th>
                                    <th class="text-center">Approval</th>
                                    <th class="text-center">Export</th>
                                    <th class="text-center">Import</th>
                                </tr>
                            </thead>
                            <tbody id="groupAccessBody">
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Pilih group untuk melihat daftar menu.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer justify-content-between flex-wrap">
                    <div class="small text-muted w-100 mb-2">Centang akses utama untuk menampilkan menu di sidebar.</div>
                    <div>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary" id="saveGroupAccessBtn" <?= $canEdit ? '' : 'disabled'; ?>>Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script>
(() => {
    console.log('=== User Group Script Initialized ===');
    
    const canEdit = <?= $canEdit ? 'true' : 'false' ?>;
    console.log('canEdit:', canEdit);
    
    const modal = $('#modalGroupAccess');
    console.log('Modal element found:', modal.length > 0);
    
    const form = document.getElementById('formGroupAccess');
    console.log('Form element found:', form !== null);
    
    const body = document.getElementById('groupAccessBody');
    console.log('Body element found:', body !== null);
    const errorBox = document.getElementById('groupAccessError');
    const subtitle = document.getElementById('groupAccessSubtitle');
    const groupIdInput = document.getElementById('groupIdInput');
    const csrfInput = form.querySelector('input[name="<?= csrf_token() ?>"]');
    const saveButton = document.getElementById('saveGroupAccessBtn');
    let lastTrigger = null;

    const routes = {
        access: '<?= site_url('admin/utility/user-group/access') ?>',
        save: '<?= site_url('admin/utility/user-group/access/save') ?>',
    };

    function setCsrf(hash) {
        if (!hash) {
            return;
        }

        csrfInput.value = hash;
    }

    function showError(message) {
        if (!message) {
            errorBox.classList.add('d-none');
            errorBox.textContent = '';
            return;
        }

        errorBox.textContent = message;
        errorBox.classList.remove('d-none');
    }

    function escapeHtml(value) {
        const text = value == null ? '' : String(value);
        return text
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function checkboxCell(name, menuId, checked, disabled) {
        return '<div class="custom-control custom-checkbox custom-control-inline m-0">' +
            '<input type="checkbox" class="custom-control-input js-' + name + '" id="' + name + '-' + menuId + '" name="' + name + '[' + menuId + ']" value="1" ' + (checked ? 'checked' : '') + (disabled ? ' disabled' : '') + '>' +
            '<label class="custom-control-label" for="' + name + '-' + menuId + '"></label>' +
            '</div>';
    }

    function accessCell(menuId, checked) {
        return '<div class="custom-control custom-checkbox custom-control-inline m-0">' +
            '<input type="checkbox" class="custom-control-input js-akses" id="akses-' + menuId + '" name="akses[' + menuId + ']" value="1" ' + (checked ? 'checked' : '') + '>' +
            '<label class="custom-control-label" for="akses-' + menuId + '"></label>' +
            '</div>';
    }

    function renderRows(rows) {
        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">Tidak ada menu yang tersedia.</td></tr>';
            return;
        }

        body.innerHTML = rows.map((row) => {
            const level = Number(row.level || 1);
            const padding = level === 1 ? '' : 'style="padding-left:' + ((level - 1) * 24) + 'px"';
            const label = level === 1 ? '<strong>' + escapeHtml(row.label || '-') + '</strong>' : escapeHtml(row.label || '-');
            const accessChecked = Boolean(row.akses);

            return '<tr data-menu-id="' + escapeHtml(row.menu_id || '') + '">' +
                '<td ' + padding + '>' + label + '</td>' +
                '<td class="text-center">' + '<input type="hidden" name="menu_ids[]" value="' + escapeHtml(row.menu_id || '') + '">' + accessCell(row.menu_id, accessChecked) + '</td>' +
                '<td class="text-center">' + checkboxCell('fitur_add', row.menu_id, Boolean(row.add), !accessChecked) + '</td>' +
                '<td class="text-center">' + checkboxCell('fitur_edit', row.menu_id, Boolean(row.edit), !accessChecked) + '</td>' +
                '<td class="text-center">' + checkboxCell('fitur_delete', row.menu_id, Boolean(row.delete), !accessChecked) + '</td>' +
                '<td class="text-center">' + checkboxCell('fitur_approval', row.menu_id, Boolean(row.approval), !accessChecked) + '</td>' +
                '<td class="text-center">' + checkboxCell('fitur_export', row.menu_id, Boolean(row.export), !accessChecked) + '</td>' +
                '<td class="text-center">' + checkboxCell('fitur_import', row.menu_id, Boolean(row.import), !accessChecked) + '</td>' +
            '</tr>';
        }).join('');

        bindRowEvents();
        body.querySelectorAll('.js-akses:checked').forEach((input) => {
            syncParentAccess(input.id.replace('akses-', ''));
        });
    }

    function syncFeatureState(menuId, enabled) {
        const row = body.querySelector('tr[data-menu-id="' + menuId + '"]');
        if (!row) {
            return;
        }

        row.querySelectorAll('.js-fitur_add, .js-fitur_edit, .js-fitur_delete, .js-fitur_approval, .js-fitur_export, .js-fitur_import').forEach((input) => {
            input.disabled = !enabled;
        });
    }

    function setAccessState(menuId, enabled) {
        const accessInput = body.querySelector('#akses-' + menuId);
        if (!accessInput) {
            return;
        }

        accessInput.checked = enabled;
        syncFeatureState(menuId, enabled);

        const row = accessInput.closest('tr');
        if (!enabled && row) {
            row.querySelectorAll('.js-fitur_add, .js-fitur_edit, .js-fitur_delete, .js-fitur_approval, .js-fitur_export, .js-fitur_import').forEach((feature) => {
                feature.checked = false;
            });
        }
    }

    function syncParentAccess(menuId) {
        const parts = String(menuId || '').split('-');
        if (parts.length <= 1) {
            return;
        }

        for (let index = 1; index < parts.length; index += 1) {
            const parentId = parts.slice(0, index).join('-');
            setAccessState(parentId, true);
        }
    }

    function syncChildAccess(menuId, enabled) {
        const selector = 'tr[data-menu-id^="' + menuId + '-"] .js-akses';
        body.querySelectorAll(selector).forEach((input) => {
            setAccessState(input.id.replace('akses-', ''), enabled);
        });
    }

    function bindRowEvents() {
        body.querySelectorAll('.js-akses').forEach((input) => {
            input.addEventListener('change', () => {
                const menuId = input.id.replace('akses-', '');
                setAccessState(menuId, input.checked);

                if (input.checked) {
                    syncParentAccess(menuId);
                } else {
                    syncChildAccess(menuId, false);
                }
            });
        });
    }

    async function loadGroupAccess(groupId) {
        showError('');
        subtitle.textContent = 'Memuat data group...';
        body.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">Memuat data...</td></tr>';
        groupIdInput.value = groupId;

        try {
            const url = routes.access + '/' + groupId;
            console.log('Fetching access data from:', url);
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });

            const data = await response.json();
            console.log('Response data:', data);

            if (!response.ok) {
                throw new Error(data.error || 'Gagal memuat akses group.');
            }

            if (!data.rows) {
                throw new Error('Data menu tidak diterima.');
            }

            subtitle.textContent = 'Group: ' + (data.group?.label || '-') + ' | Role: ' + (data.group?.role || '-');
            renderRows(Array.isArray(data.rows) ? data.rows : []);
            console.log('Access data loaded successfully:', data.rows.length, 'menus');
        } catch (error) {
            console.error('Error loading access data:', error);
            throw error;
        }
    }

    modal.on('hide.bs.modal', () => {
        const activeElement = document.activeElement;
        if (activeElement && modal[0].contains(activeElement)) {
            activeElement.blur();
        }
    });

    modal.on('hidden.bs.modal', () => {
        if (lastTrigger && typeof lastTrigger.focus === 'function') {
            lastTrigger.focus();
        }
    });

    const openAccessButtons = document.querySelectorAll('.js-open-access');
    console.log('Found .js-open-access buttons:', openAccessButtons.length);
    
    openAccessButtons.forEach((button) => {
        console.log('Attaching listener to button with groupId:', button.dataset.groupId);
        button.addEventListener('click', () => {
            const groupId = button.dataset.groupId;
            console.log('Atur Akses button clicked for groupId:', groupId);
            console.log('canEdit:', canEdit);
            
            lastTrigger = button;
            modal.modal('show');

            if (!canEdit) {
                console.log('Not in edit mode, showing read-only view');
                showError('Mode lihat saja. Hanya admin yang dapat mengubah akses menu.');
                subtitle.textContent = 'Akses hanya baca';
                body.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">Anda hanya dapat melihat daftar akses menu.</td></tr>';
                return;
            }

            if (!groupId) {
                console.error('Group ID not found');
                showError('Group tidak ditemukan.');
                subtitle.textContent = 'Group tidak valid';
                body.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">Group tidak ditemukan.</td></tr>';
                return;
            }

            console.log('Loading access data for group:', groupId);
            loadGroupAccess(groupId).catch((error) => {
                console.error('Error:', error);
                showError(error.message || 'Gagal memuat akses group.');
                subtitle.textContent = 'Gagal memuat group';
                body.innerHTML = '<tr><td colspan="8" class="text-center text-danger py-4">' + escapeHtml(error.message || 'Gagal memuat akses group.') + '</td></tr>';
            });
        });
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!canEdit) {
            return;
        }

        showError('');
        saveButton.disabled = true;
        saveButton.textContent = 'Menyimpan...';

        try {
            const response = await fetch(routes.save, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: new FormData(form),
            });
            const data = await response.json();
            setCsrf(data.csrfHash || '');

            if (!response.ok || data.status !== 'ok') {
                throw new Error(data.message || 'Gagal menyimpan akses group.');
            }

            window.location.reload();
        } catch (error) {
            showError(error.message || 'Gagal menyimpan akses group.');
        } finally {
            saveButton.disabled = !canEdit;
            saveButton.textContent = 'Simpan';
        }
    });

})();
</script>
<?= $this->endSection(); ?>
