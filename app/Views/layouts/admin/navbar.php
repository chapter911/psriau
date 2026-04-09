<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <?php
        $role = strtolower(trim((string) session()->get('role')));
        $isSuperAdministrator = in_array($role, ['super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
        $canUseProductionUtilities = defined('ENVIRONMENT') && ENVIRONMENT === 'production' && $isSuperAdministrator;
        $commandResult = session()->getFlashdata('command_result');
    ?>
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <?php if ($canUseProductionUtilities): ?>
            <li class="nav-item d-flex align-items-center flex-wrap py-1">
                <span class="nav-link text-dark font-weight-bold pr-2 mb-0" style="cursor: default;">
                    <i class="fas fa-tools mr-1"></i> Ops Tools
                </span>
                <form action="<?= site_url('/admin/pengaturan/application/git-pull'); ?>" method="post" class="mr-1 mb-0 js-ops-tool-form" data-loading-text="Menjalankan Git Pull..." data-skip-confirm="1">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="redirect_to" value="<?= esc((string) current_url(true)); ?>">
                    <button type="submit" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-code-branch mr-1"></i> Git Pull
                    </button>
                </form>
                <form action="<?= site_url('/admin/pengaturan/application/merge-database'); ?>" method="post" class="mr-1 mb-0 js-ops-tool-form" data-loading-text="Menjalankan Merge Database..." data-skip-confirm="1">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="redirect_to" value="<?= esc((string) current_url(true)); ?>">
                    <button type="submit" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-database mr-1"></i> Merge Database
                    </button>
                </form>
                <button type="button" class="btn btn-sm btn-outline-warning mb-0" data-toggle="modal" data-target="#errorLogModalNavbar">
                    <i class="fas fa-triangle-exclamation mr-1"></i> Lihat Log Error
                </button>
            </li>
        <?php endif; ?>
    </ul>

    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle text-dark" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-user-circle mr-1"></i>
                <?= esc((string) session()->get('fullName')); ?> <span class="text-muted" style="font-size:0.9em;">(<?= esc((string) session()->get('role')); ?>)</span>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#modalUpdatePassword" role="button">
                    <i class="fas fa-key mr-1"></i> Update Password
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-danger" href="<?= site_url('/keluar'); ?>">
                    <i class="fas fa-sign-out-alt mr-1"></i> Keluar
                </a>
            </div>
        </li>
    </ul>
</nav>

<div class="modal fade" id="modalUpdatePassword" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mb-0">Update Password</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="navbarPasswordForm" action="<?= site_url('/admin/password/update'); ?>" method="post" autocomplete="off" data-skip-confirm="1">
                <div class="modal-body">
                    <?= csrf_field(); ?>
                    <div class="alert alert-danger d-none" id="navbarPasswordError"></div>
                    <div class="alert alert-success d-none" id="navbarPasswordSuccess"></div>
                    <div class="form-group">
                        <label for="navbar_current_password">Password Lama</label>
                        <input type="password" name="current_password" id="navbar_current_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="navbar_new_password">Password Baru</label>
                        <input type="password" name="new_password" id="navbar_new_password" class="form-control" minlength="6" required>
                    </div>
                    <div class="form-group mb-0">
                        <label for="navbar_confirm_password">Konfirmasi Password Baru</label>
                        <input type="password" name="confirm_password" id="navbar_confirm_password" class="form-control" minlength="6" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary" id="navbarPasswordSubmitBtn">
                        <i class="fas fa-key mr-1"></i> Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($canUseProductionUtilities): ?>
<div class="modal fade" id="errorLogModalNavbar" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mb-0">Log Error Aplikasi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="navbarErrorLogDate">Pilih File Log Error</label>
                    <select class="form-control" id="navbarErrorLogDate">
                        <option value="">- pilih file log -</option>
                    </select>
                    <small class="text-muted">Menampilkan 3 file log terbaru. Pilih salah satu untuk melihat isi file.</small>
                </div>
                <div id="navbarErrorLogResult" class="border rounded p-3" style="max-height: 50vh; overflow:auto;">
                    <div class="text-muted">Belum ada data ditampilkan.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?php if (is_array($commandResult)): ?>
<div class="modal fade" id="navbarCommandResultModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mb-0"><?= esc((string) ($commandResult['title'] ?? 'Hasil Eksekusi')); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert <?= ! empty($commandResult['success']) ? 'alert-success' : 'alert-danger'; ?> mb-3">
                    <?= ! empty($commandResult['success']) ? 'Perintah berhasil dijalankan.' : 'Perintah gagal dijalankan.'; ?>
                </div>
                <pre style="white-space: pre-wrap; max-height: 50vh; background:#0b1220; color:#d6e1ff; padding:14px; border-radius:8px;"><?= esc((string) ($commandResult['output'] ?? 'Tidak ada output.')); ?></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<script>
(() => {
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('navbarPasswordForm');
        if (!form) {
            return;
        }

        const blurActiveElement = (modalElement) => {
            const active = document.activeElement;
            if (!active || typeof active.blur !== 'function') {
                return;
            }

            if (!modalElement || modalElement.contains(active)) {
                active.blur();
            }
        };

        const modal = window.jQuery ? window.jQuery('#modalUpdatePassword') : null;
        const modalElement = document.getElementById('modalUpdatePassword');
        const errorBox = document.getElementById('navbarPasswordError');
        const successBox = document.getElementById('navbarPasswordSuccess');
        const submitButton = document.getElementById('navbarPasswordSubmitBtn');
        const csrfInput = form.querySelector('input[name="<?= csrf_token() ?>"]');

        const setAlert = (element, message) => {
            if (!element) {
                return;
            }

            if (!message) {
                element.classList.add('d-none');
                element.textContent = '';
                return;
            }

            element.textContent = message;
            element.classList.remove('d-none');
        };

        const resetFormState = () => {
            form.reset();
            setAlert(errorBox, '');
            setAlert(successBox, '');
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-key mr-1"></i> Update Password';
        };

        if (modal && typeof modal.on === 'function') {
            modal.on('hide.bs.modal', () => {
                blurActiveElement(modalElement);
            });
            modal.on('hidden.bs.modal', resetFormState);
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            setAlert(errorBox, '');
            setAlert(successBox, '');

            submitButton.disabled = true;
            submitButton.textContent = 'Menyimpan...';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: new FormData(form),
                });

                const payload = await response.json();

                if (payload.csrfHash && csrfInput) {
                    csrfInput.value = payload.csrfHash;
                }

                if (!response.ok || payload.status !== 'ok') {
                    throw new Error(payload.message || 'Gagal memperbarui password.');
                }

                setAlert(successBox, payload.message || 'Password berhasil diubah.');
                if (modal && typeof modal.modal === 'function') {
                    window.setTimeout(() => {
                        modal.modal('hide');
                    }, 850);
                } else {
                    resetFormState();
                }
            } catch (error) {
                setAlert(errorBox, error.message || 'Gagal memperbarui password.');
            } finally {
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="fas fa-key mr-1"></i> Update Password';
            }
        });
    });
})();

<?php if ($canUseProductionUtilities): ?>
(() => {
    document.addEventListener('DOMContentLoaded', () => {
        const dateSelect = document.getElementById('navbarErrorLogDate');
        const resultBox = document.getElementById('navbarErrorLogResult');

        const opsForms = document.querySelectorAll('form.js-ops-tool-form');
        if (opsForms.length > 0) {
            opsForms.forEach((form) => {
                form.addEventListener('submit', () => {
                    if (form.dataset.submitting === '1') {
                        return;
                    }

                    form.dataset.submitting = '1';
                    const submitButton = form.querySelector('button[type="submit"]');
                    if (submitButton) {
                        submitButton.disabled = true;
                    }

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Mohon Tunggu',
                            text: form.getAttribute('data-loading-text') || 'Memproses perintah...',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            didOpen: () => Swal.showLoading(),
                        });
                    }
                });
            });
        }

        if (!dateSelect || !resultBox) {
            return;
        }

        const escapeHtml = (value) => String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');

        const renderLogContent = (payload) => {
            const fileName = payload && payload.file ? String(payload.file) : '-';
            const content = payload && payload.content ? String(payload.content) : '';
            const isTruncated = Boolean(payload && payload.isTruncated);
            const totalLines = Number(payload && payload.totalLines ? payload.totalLines : 0);
            const displayedLines = Number(payload && payload.displayedLines ? payload.displayedLines : 0);

            if (!content.trim()) {
                resultBox.innerHTML = `<div class="text-muted">File ${escapeHtml(fileName)} kosong.</div>`;
                return;
            }

            const meta = isTruncated
                ? `<div class="alert alert-warning py-2 px-3 mb-2">Menampilkan ${escapeHtml(String(displayedLines))} dari ${escapeHtml(String(totalLines))} baris terakhir.</div>`
                : '';

            resultBox.innerHTML = `
                <div class="mb-2"><strong>File:</strong> ${escapeHtml(fileName)}</div>
                ${meta}
                <pre class="mb-0" style="white-space: pre-wrap; background:#0b1220; color:#d6e1ff; padding:12px; border-radius:8px;">${escapeHtml(content)}</pre>
            `;
        };

        const loadDates = async () => {
            dateSelect.innerHTML = '<option value="">Memuat file log...</option>';
            try {
                const response = await fetch('<?= site_url('/admin/pengaturan/application/error-log-dates'); ?>', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                const payload = await response.json();
                if (!response.ok || payload.status !== 'ok') {
                    throw new Error(payload.message || 'Gagal memuat daftar file log.');
                }

                const dates = Array.isArray(payload.data) ? payload.data : [];
                if (dates.length === 0) {
                    dateSelect.innerHTML = '<option value="">- tidak ada file log -</option>';
                    return;
                }

                dateSelect.innerHTML = '<option value="">- pilih file log -</option>' + dates
                    .map((date) => `<option value="${escapeHtml(date)}">${escapeHtml(date)}</option>`)
                    .join('');
            } catch (error) {
                dateSelect.innerHTML = '<option value="">- gagal memuat file log -</option>';
                resultBox.innerHTML = `<div class="text-danger">${escapeHtml(error.message || 'Gagal memuat file log.')}</div>`;
            }
        };

        const fetchLogsByDate = async (date) => {
            if (!date) {
                resultBox.innerHTML = '<div class="text-muted">Pilih file log untuk melihat isinya.</div>';
                return;
            }

            resultBox.innerHTML = '<div class="text-muted">Memuat isi file log...</div>';
            try {
                const response = await fetch(`<?= site_url('/admin/pengaturan/application/error-logs'); ?>?file=${encodeURIComponent(date)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                const payload = await response.json();
                if (!response.ok || payload.status !== 'ok') {
                    throw new Error(payload.message || 'Gagal memuat isi file log.');
                }

                renderLogContent(payload.data || {});
            } catch (error) {
                resultBox.innerHTML = `<div class="text-danger">${escapeHtml(error.message || 'Gagal memuat isi file log.')}</div>`;
            }
        };

        dateSelect.addEventListener('change', (event) => {
            fetchLogsByDate(event.target.value || '');
        });

        if (window.jQuery) {
            const blurActiveElementInModal = (modalId) => {
                const modalEl = document.getElementById(modalId);
                const active = document.activeElement;
                if (!active || typeof active.blur !== 'function') {
                    return;
                }

                if (!modalEl || modalEl.contains(active)) {
                    active.blur();
                }
            };

            window.jQuery('#errorLogModalNavbar').on('show.bs.modal', () => {
                resultBox.innerHTML = '<div class="text-muted">Pilih file log untuk melihat isinya.</div>';
                loadDates();
            });

            window.jQuery('#errorLogModalNavbar').on('hide.bs.modal', () => {
                blurActiveElementInModal('errorLogModalNavbar');
            });

            window.jQuery('#errorLogModalNavbar').on('hidden.bs.modal', () => {
                dateSelect.innerHTML = '<option value="">- pilih file log -</option>';
                resultBox.innerHTML = '<div class="text-muted">Belum ada data ditampilkan.</div>';
            });

            <?php if (is_array($commandResult)): ?>
            window.jQuery('#navbarCommandResultModal').on('hide.bs.modal', () => {
                blurActiveElementInModal('navbarCommandResultModal');
            });

            window.jQuery('#navbarCommandResultModal').modal('show');
            <?php endif; ?>
        }
    });
})();
<?php endif; ?>
</script>
