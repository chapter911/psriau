<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="card card-outline card-primary mb-4">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title mb-0">Manajemen User</h3>
        <button type="button" class="btn btn-primary btn-sm ml-auto" id="btnOpenCreate" <?= empty($can_edit ?? false) ? 'disabled' : '' ?>>
            <i class="fas fa-user-plus"></i> Tambah User
        </button>
    </div>
    <div class="card-body">
        <input type="hidden" id="csrf_token" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">

        <?php if (empty($can_edit ?? false)): ?>
            <div class="alert alert-warning">Anda tidak memiliki akses untuk mengubah data user.</div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped table-bordered mb-0" id="userTable">
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Username / NIP</th>
                        <th>Nama Lengkap</th>
                        <th style="width: 140px;">Status</th>
                        <th style="width: 140px;">Role</th>
                        <th style="width: 170px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    <?php foreach (($users ?? []) as $idx => $row): ?>
                        <tr data-id="<?= esc((string) ($row['id'] ?? '')); ?>">
                            <td><?= esc((string) ($idx + 1)); ?></td>
                            <td><?= esc((string) ($row['username'] ?? '-')); ?></td>
                            <td><?= esc((string) ($row['full_name'] ?? '-')); ?></td>
                            <td>
                                <?php if ((int) ($row['is_active'] ?? 1) === 1): ?>
                                    <span class="badge badge-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Non Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge badge-secondary"><?= esc((string) ($row['role'] ?? '-')); ?></span></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary btn-edit" <?= empty($can_edit ?? false) ? 'disabled' : '' ?>>
                                    <i class="fas fa-pen"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="userForm" class="modal-content" novalidate>
            <div class="modal-header">
                <h5 class="modal-title" id="userModalTitle">Tambah User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="userId">
                <div class="form-group">
                    <label for="sourceType">Sumber User</label>
                    <select class="form-control no-select2" id="sourceType">
                        <option value="manual">Input Manual</option>
                        <option value="pegawai">Pilih dari Pegawai (mst_pegawai)</option>
                    </select>
                    <small class="text-muted d-block">Untuk sumber pegawai, username otomatis menggunakan NIP.</small>
                </div>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" id="username" required>
                    <select class="form-control d-none no-select2" id="usernamePegawai">
                        <option value="">Pilih NIP pegawai</option>
                    </select>
                    <small class="text-muted d-block" id="usernameHint">Username tidak dapat diubah setelah user dibuat.</small>
                    <small class="text-danger d-none" data-error="username"></small>
                    <small class="text-danger d-none" data-error="pegawai_id"></small>
                </div>
                <div class="form-group">
                    <label for="fullName">Nama Lengkap</label>
                    <input type="text" class="form-control" id="fullName" required>
                    <small class="text-danger d-none" data-error="full_name"></small>
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select class="form-control no-select2" id="role" required>
                        <option value="">Pilih Role</option>
                        <option value="editor">Editor</option>
                        <option value="admin">Admin</option>
                        <option value="super_administrator">Super Administrator</option>
                    </select>
                    <small class="text-danger d-none" data-error="role"></small>
                </div>
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="isActive" checked>
                        <label class="custom-control-label" for="isActive">Status aktif</label>
                    </div>
                    <small class="text-muted d-block mt-1">Jika non aktif, user tidak dapat login ke aplikasi.</small>
                </div>
                <div class="form-group">
                    <label for="password">Password <span class="text-muted" id="passwordHint">(wajib diisi)</span></label>
                    <input type="password" class="form-control" id="password" autocomplete="new-password">
                    <small class="text-danger d-none" data-error="password"></small>
                    <small class="text-muted d-block" id="passwordAutoHint"></small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" id="userSubmitBtn">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const pegawaiOptions = <?= json_encode($pegawai_options ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const canEdit = <?= empty($can_edit ?? false) ? 'false' : 'true' ?>;
    const csrfInput = document.getElementById('csrf_token');
    const userTableBody = document.getElementById('userTableBody');
    const userModalEl = document.getElementById('userModal');
    const userForm = document.getElementById('userForm');
    const btnOpenCreate = document.getElementById('btnOpenCreate');
    const userModalTitle = document.getElementById('userModalTitle');
    const userSubmitBtn = document.getElementById('userSubmitBtn');
    const userId = document.getElementById('userId');
    const sourceType = document.getElementById('sourceType');
    const username = document.getElementById('username');
    const usernamePegawai = document.getElementById('usernamePegawai');
    const fullName = document.getElementById('fullName');
    const role = document.getElementById('role');
    const isActive = document.getElementById('isActive');
    const password = document.getElementById('password');
    const passwordHint = document.getElementById('passwordHint');
    const passwordAutoHint = document.getElementById('passwordAutoHint');
    const usernameHint = document.getElementById('usernameHint');
    let userDataTable = null;

    function populatePegawaiOptions() {
        if (!usernamePegawai) {
            return;
        }

        usernamePegawai.innerHTML = '<option value="">Pilih NIP pegawai</option>';
        (pegawaiOptions || []).forEach(function (item) {
            const option = document.createElement('option');
            option.value = String(item.id || '');
            option.textContent = String(item.nip || '-') + ' - ' + String(item.nama || '-');
            option.dataset.nip = String(item.nip || '');
            option.dataset.nama = String(item.nama || '');
            usernamePegawai.appendChild(option);
        });
    }

    function updateSourceState() {
        const isCreate = !userId.value;
        const usingPegawai = isCreate && sourceType && sourceType.value === 'pegawai';

        if (sourceType) {
            sourceType.disabled = !isCreate;
        }

        if (usingPegawai) {
            username.classList.add('d-none');
            if (usernamePegawai) {
                usernamePegawai.classList.remove('d-none');
            }
            fullName.readOnly = true;
            password.value = '';
            password.required = false;
            password.disabled = true;
            if (passwordHint) {
                passwordHint.textContent = '(otomatis)';
            }
            if (passwordAutoHint) {
                passwordAutoHint.textContent = 'Password default akan otomatis menggunakan NIP pegawai.';
            }
            if (usernameHint) {
                usernameHint.textContent = 'Username otomatis diisi dari NIP pegawai.';
            }
        } else {
            username.classList.remove('d-none');
            if (usernamePegawai) {
                usernamePegawai.classList.add('d-none');
            }
            username.readOnly = !isCreate;
            fullName.readOnly = false;
            password.disabled = false;
            if (isCreate) {
                password.required = true;
                if (passwordHint) {
                    passwordHint.textContent = '(wajib diisi)';
                }
            } else {
                password.required = false;
                if (passwordHint) {
                    passwordHint.textContent = '(opsional, isi jika ingin ganti)';
                }
            }
            if (passwordAutoHint) {
                passwordAutoHint.textContent = '';
            }
            if (usernameHint) {
                usernameHint.textContent = isCreate
                    ? 'Username digunakan untuk login dan tidak bisa diubah setelah dibuat.'
                    : 'Username dikunci dan tidak dapat diubah.';
            }
        }
    }

    function applySelectedPegawai() {
        if (!usernamePegawai) {
            return;
        }

        const selected = usernamePegawai.options[usernamePegawai.selectedIndex];
        if (!selected || !selected.value) {
            username.value = '';
            fullName.value = '';
            return;
        }

        username.value = String(selected.dataset.nip || '').trim();
        fullName.value = String(selected.dataset.nama || '').trim();
    }

    function getModalController(el) {
        if (!el) {
            return { show: function () {}, hide: function () {} };
        }

        if (window.bootstrap && window.bootstrap.Modal) {
            const instance = (typeof window.bootstrap.Modal.getOrCreateInstance === 'function')
                ? window.bootstrap.Modal.getOrCreateInstance(el)
                : new window.bootstrap.Modal(el);

            return {
                show: function () { instance.show(); },
                hide: function () { instance.hide(); },
            };
        }

        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.modal) {
            return {
                show: function () { window.jQuery(el).modal('show'); },
                hide: function () { window.jQuery(el).modal('hide'); },
            };
        }

        return {
            show: function () { el.style.display = 'block'; },
            hide: function () { el.style.display = 'none'; },
        };
    }

    const userModal = getModalController(userModalEl);

    function clearErrors() {
        document.querySelectorAll('[data-error]').forEach(function (el) {
            el.classList.add('d-none');
            el.textContent = '';
        });
    }

    function showErrors(errors) {
        Object.keys(errors || {}).forEach(function (key) {
            const target = document.querySelector('[data-error="' + key + '"]');
            if (target) {
                target.textContent = String(errors[key] || '');
                target.classList.remove('d-none');
            }
        });
    }

    function updateCsrf(hash) {
        if (csrfInput && hash) {
            csrfInput.value = hash;
        }
    }

    function notifySuccess(message) {
        if (window.Swal) {
            window.Swal.fire({ icon: 'success', title: 'Berhasil', text: message });
            return;
        }

        window.alert(message);
    }

    function notifyError(message) {
        if (window.Swal) {
            window.Swal.fire({ icon: 'error', title: 'Gagal', text: message });
            return;
        }

        window.alert(message);
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderRows(rows) {
        const safeRows = Array.isArray(rows) ? rows : [];

        if (userDataTable) {
            const tableRows = safeRows.map(function (row, index) {
                const statusBadge = (parseInt(row.is_active || '1', 10) === 1)
                    ? '<span class="badge badge-success">Aktif</span>'
                    : '<span class="badge badge-secondary">Non Aktif</span>';

                return [
                    index + 1,
                    escapeHtml(String(row.username || '-')),
                    escapeHtml(String(row.full_name || '-')),
                    statusBadge,
                    '<span class="badge badge-secondary">' + escapeHtml(String(row.role || '-')) + '</span>',
                    '<button type="button" class="btn btn-sm btn-outline-primary btn-edit" ' + (canEdit ? '' : 'disabled') + '><i class="fas fa-pen"></i></button>',
                ];
            });

            userDataTable.clear();
            userDataTable.rows.add(tableRows).draw();

            window.jQuery('#userTable tbody tr').each(function (index) {
                const row = safeRows[index];
                if (!row) {
                    return;
                }

                this.setAttribute('data-id', String(row.id || ''));
                this.dataset.username = String(row.username || '');
                this.dataset.fullName = String(row.full_name || '');
                this.dataset.role = String(row.role || 'editor');
                this.dataset.isActive = String(parseInt(row.is_active || '1', 10) === 1 ? '1' : '0');
            });

            return;
        }

        userTableBody.innerHTML = '';

        if (safeRows.length === 0) {
            userTableBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Belum ada data user.</td></tr>';
            return;
        }

        safeRows.forEach(function (row, index) {
            const tr = document.createElement('tr');
            tr.setAttribute('data-id', String(row.id));
            const statusBadge = (parseInt(row.is_active || '1', 10) === 1)
                ? '<span class="badge badge-success">Aktif</span>'
                : '<span class="badge badge-secondary">Non Aktif</span>';
            tr.innerHTML = '' +
                '<td>' + (index + 1) + '</td>' +
                '<td>' + escapeHtml(String(row.username || '-')) + '</td>' +
                '<td>' + escapeHtml(String(row.full_name || '-')) + '</td>' +
                '<td>' + statusBadge + '</td>' +
                '<td><span class="badge badge-secondary">' + escapeHtml(String(row.role || '-')) + '</span></td>' +
                '<td>' +
                    '<button type="button" class="btn btn-sm btn-outline-primary btn-edit" ' + (canEdit ? '' : 'disabled') + '><i class="fas fa-pen"></i></button> ' +
                '</td>';

            tr.dataset.username = String(row.username || '');
            tr.dataset.fullName = String(row.full_name || '');
            tr.dataset.role = String(row.role || 'editor');
            tr.dataset.isActive = String(parseInt(row.is_active || '1', 10) === 1 ? '1' : '0');
            userTableBody.appendChild(tr);
        });
    }

    function initUserDataTable() {
        if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.DataTable) {
            return;
        }

        userDataTable = window.jQuery('#userTable').DataTable({
            responsive: false,
            autoWidth: false,
            scrollX: true,
            scrollCollapse: true,
            order: [[2, 'asc']],
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data',
                zeroRecords: 'Data tidak ditemukan',
                emptyTable: 'Belum ada data user.',
                paginate: {
                    first: 'Awal',
                    last: 'Akhir',
                    next: 'Berikutnya',
                    previous: 'Sebelumnya'
                }
            },
            columnDefs: [
                { targets: [3, 4, 5], orderable: false },
            ]
        });
    }

    function fetchUsers() {
        fetch('<?= site_url('admin/utility/user/list') ?>', {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, data: data };
                });
            })
            .then(function (result) {
                updateCsrf(result.data.csrfHash);
                if (!result.ok) {
                    throw new Error(result.data.message || 'Gagal memuat data user.');
                }

                renderRows(result.data.data || []);
            })
            .catch(function (error) {
                notifyError(error.message || 'Gagal memuat data user.');
            });
    }

    function openCreateModal() {
        clearErrors();
        userModalTitle.textContent = 'Tambah User';
        userSubmitBtn.textContent = 'Simpan';
        userId.value = '';
        if (sourceType) {
            sourceType.value = 'manual';
        }
        if (usernamePegawai) {
            usernamePegawai.value = '';
        }
        username.value = '';
        fullName.value = '';
        role.value = 'editor';
        isActive.checked = true;
        password.value = '';
        password.required = true;
        password.disabled = false;
        passwordHint.textContent = '(wajib diisi)';
        if (passwordAutoHint) {
            passwordAutoHint.textContent = '';
        }
        updateSourceState();
        userModal.show();
    }

    function openEditModal(row) {
        clearErrors();
        userModalTitle.textContent = 'Ubah User';
        userSubmitBtn.textContent = 'Perbarui';
        userId.value = row.getAttribute('data-id') || '';
        if (sourceType) {
            sourceType.value = 'manual';
        }
        if (usernamePegawai) {
            usernamePegawai.value = '';
        }
        username.value = row.dataset.username || '';
        fullName.value = row.dataset.fullName || '';
        role.value = row.dataset.role || 'editor';
        isActive.checked = String(row.dataset.isActive || '1') === '1';
        password.value = '';
        password.required = false;
        password.disabled = false;
        passwordHint.textContent = '(opsional, isi jika ingin ganti)';
        if (passwordAutoHint) {
            passwordAutoHint.textContent = '';
        }
        updateSourceState();
        userModal.show();
    }

    function request(url, payload) {
        const postData = new URLSearchParams();
        Object.keys(payload).forEach(function (key) {
            postData.append(key, payload[key]);
        });

        if (csrfInput && csrfInput.name) {
            postData.append(csrfInput.name, csrfInput.value);
        }

        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: postData.toString(),
        }).then(function (response) {
            return response.json().then(function (data) {
                return { ok: response.ok, data: data };
            });
        });
    }

    if (btnOpenCreate && canEdit) {
        btnOpenCreate.addEventListener('click', function () {
            openCreateModal();
        });
    }

    if (sourceType) {
        sourceType.addEventListener('change', function () {
            updateSourceState();
            if (sourceType.value === 'manual') {
                username.value = '';
                fullName.value = '';
            } else {
                applySelectedPegawai();
            }
        });
    }

    if (usernamePegawai) {
        usernamePegawai.addEventListener('change', function () {
            applySelectedPegawai();
        });
    }

    if (userTableBody) {
        userTableBody.addEventListener('click', function (event) {
            const editBtn = event.target.closest('.btn-edit');

            if (!canEdit) {
                return;
            }

            if (editBtn) {
                const row = editBtn.closest('tr');
                if (row) {
                    openEditModal(row);
                }
                return;
            }
        });
    }

    if (userForm) {
        userForm.addEventListener('submit', function (event) {
            event.preventDefault();
            clearErrors();

            const payload = {
                full_name: fullName.value.trim(),
                role: role.value,
                is_active: isActive && isActive.checked ? '1' : '0',
                password: password.value,
            };

            const currentId = userId.value;
            const url = currentId
                ? '<?= site_url('admin/utility/user') ?>/' + encodeURIComponent(currentId) + '/ubah'
                : '<?= site_url('admin/utility/user/tambah') ?>';

            if (!currentId) {
                const selectedSource = sourceType ? sourceType.value : 'manual';
                payload.source_type = selectedSource;
                if (selectedSource === 'pegawai') {
                    payload.pegawai_id = usernamePegawai ? usernamePegawai.value : '';
                    payload.username = username.value.trim();
                } else {
                    payload.pegawai_id = '';
                    payload.username = username.value.trim();
                }
            }

            request(url, payload)
                .then(function (result) {
                    updateCsrf(result.data.csrfHash);

                    if (!result.ok) {
                        if (result.data && result.data.errors) {
                            showErrors(result.data.errors);
                            return;
                        }

                        throw new Error(result.data.message || 'Gagal menyimpan user.');
                    }

                    userModal.hide();
                    notifySuccess(result.data.message || 'User berhasil disimpan.');
                    fetchUsers();
                })
                .catch(function (error) {
                    notifyError(error.message || 'Gagal menyimpan user.');
                });
        });
    }

    populatePegawaiOptions();
    updateSourceState();
    initUserDataTable();
    fetchUsers();
});
</script>
<?= $this->endSection(); ?>
