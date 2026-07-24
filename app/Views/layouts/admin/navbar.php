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
            <li class="nav-item d-none d-md-flex align-items-center flex-wrap py-1">
                <span class="nav-link text-dark font-weight-bold pr-2 mb-0" style="cursor: default;">
                    <i class="fas fa-tools mr-1"></i>
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
                <button type="button" class="btn btn-sm btn-outline-primary mr-1 mb-0" data-toggle="modal" data-target="#extractDatabaseModalNavbar">
                    <i class="fas fa-file-export mr-1"></i> Ekstrak Database
                </button>
                <button type="button" class="btn btn-sm btn-outline-warning mb-0" data-toggle="modal" data-target="#errorLogModalNavbar">
                    <i class="fas fa-triangle-exclamation mr-1"></i> Lihat Log Error
                </button>
            </li>

            <li class="nav-item dropdown d-md-none">
                <a class="nav-link dropdown-toggle text-dark font-weight-bold" href="#" id="opsToolsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-tools mr-1"></i>
                </a>
                <div class="dropdown-menu" aria-labelledby="opsToolsDropdown">
                    <form action="<?= site_url('/admin/pengaturan/application/git-pull'); ?>" method="post" class="js-ops-tool-form mb-0" data-loading-text="Menjalankan Git Pull..." data-skip-confirm="1">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="redirect_to" value="<?= esc((string) current_url(true)); ?>">
                        <button type="submit" class="dropdown-item">
                            <i class="fas fa-code-branch mr-2 text-primary"></i> Git Pull
                        </button>
                    </form>
                    <form action="<?= site_url('/admin/pengaturan/application/merge-database'); ?>" method="post" class="js-ops-tool-form mb-0" data-loading-text="Menjalankan Merge Database..." data-skip-confirm="1">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="redirect_to" value="<?= esc((string) current_url(true)); ?>">
                        <button type="submit" class="dropdown-item">
                            <i class="fas fa-database mr-2 text-primary"></i> Merge Database
                        </button>
                    </form>
                    <button type="button" class="dropdown-item text-primary" data-toggle="modal" data-target="#extractDatabaseModalNavbar">
                        <i class="fas fa-file-export mr-2 text-primary"></i> Ekstrak Database
                    </button>
                    <button type="button" class="dropdown-item text-warning" data-toggle="modal" data-target="#errorLogModalNavbar">
                        <i class="fas fa-triangle-exclamation mr-2"></i> Lihat Log Error
                    </button>
                </div>
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
<div class="modal fade" id="extractDatabaseModalNavbar" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title mb-0 font-weight-bold"><i class="fas fa-file-export mr-2"></i> Ekstrak Database / Tabel</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('/admin/pengaturan/application/extract-database'); ?>" method="post" id="navbarExtractDbForm" data-skip-confirm="1">
                <?= csrf_field(); ?>
                <input type="hidden" name="redirect_to" value="<?= esc((string) current_url(true)); ?>">
                <div class="modal-body">
                    <div class="row align-items-center mb-3">
                        <div class="col-md-5 mb-2 mb-md-0">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                                <input type="text" id="navbarExtractSearchInput" class="form-control" placeholder="Cari nama tabel...">
                            </div>
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-sort-amount-down"></i></span>
                                </div>
                                <select id="navbarExtractSortSelect" class="form-control">
                                    <option value="name_asc">Nama (A-Z)</option>
                                    <option value="size_desc">Ukuran (Terbesar)</option>
                                    <option value="size_asc">Ukuran (Terkecil)</option>
                                    <option value="rows_desc">Baris (Terbanyak)</option>
                                    <option value="rows_asc">Baris (Tersedikit)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 text-md-right">
                            <button type="button" class="btn btn-xs btn-outline-secondary mr-1" id="navbarExtractSelectAllBtn" title="Pilih Semua">
                                <i class="fas fa-check-double mr-1"></i> Semua
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-secondary" id="navbarExtractDeselectAllBtn" title="Hapus Pilihan">
                                <i class="fas fa-times mr-1"></i> Reset
                            </button>
                        </div>
                    </div>

                    <div id="navbarExtractTableContainer" class="border rounded p-2" style="max-height: 45vh; overflow-y: auto; background: #fdfdfd;">
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-spinner fa-spin mr-1"></i> Memuat daftar tabel database...
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center">
                    <span class="text-muted small font-weight-bold" id="navbarExtractCountBadge">0 tabel dipilih</span>
                    <div>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary" id="navbarExtractSubmitBtn" disabled>
                            <i class="fas fa-download mr-1"></i> Ekstrak Tabel Terpilih
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

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
        const shouldOpenPasswordModal = <?= session()->getFlashdata('open_password_modal') ? 'true' : 'false'; ?>;
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

            if (shouldOpenPasswordModal) {
                modal.modal('show');
            }
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

        const renderLogContent = (data) => {
            if (!data || typeof data !== 'object') {
                resultBox.innerHTML = `<div class="alert alert-warning p-3 mb-0"><i class="fas fa-exclamation-triangle mr-1"></i> Data log tidak valid atau tidak ditemukan.</div>`;
                return;
            }

            const fileName = data.file ? String(data.file) : '-';
            const content = data.content ? String(data.content) : '';
            const isTruncated = Boolean(data.isTruncated);
            const totalLines = Number(data.totalLines ? data.totalLines : 0);
            const displayedLines = Number(data.displayedLines ? data.displayedLines : 0);

            if (!content.trim()) {
                resultBox.innerHTML = `<div class="alert alert-info p-3 mb-0"><i class="fas fa-info-circle mr-1"></i> File ${escapeHtml(fileName)} kosong atau tidak memiliki isi.</div>`;
                return;
            }

            const meta = isTruncated
                ? `<div class="alert alert-warning py-2 px-3 mb-2"><i class="fas fa-exclamation-circle mr-1"></i> Menampilkan ${escapeHtml(String(displayedLines))} dari ${escapeHtml(String(totalLines))} baris terakhir.</div>`
                : '';

            resultBox.innerHTML = `
                <div class="mb-2"><strong><i class="fas fa-file-alt mr-1"></i> File:</strong> ${escapeHtml(fileName)}</div>
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

                let payload;
                try {
                    payload = await response.json();
                } catch (parseError) {
                    const text = await response.text().catch(() => 'Tidak dapat membaca response');
                    throw new Error(`Response tidak valid (status: ${response.status}). Server mengembalikan: ${text.substring(0, 200)}`);
                }

                if (!response.ok || payload.status !== 'ok') {
                    throw new Error(payload && payload.message ? payload.message : `Gagal memuat daftar file log (status: ${response.status}).`);
                }

                const dates = Array.isArray(payload.data) ? payload.data : [];
                if (dates.length === 0) {
                    dateSelect.innerHTML = '<option value="">- tidak ada file log -</option>';
                    resultBox.innerHTML = '<div class="alert alert-info mb-0"><i class="fas fa-info-circle mr-1"></i> Tidak ada file log error yang ditemukan di direktori.</div>';
                    return;
                }

                dateSelect.innerHTML = '<option value="">- pilih file log -</option>' + dates
                    .map((date) => `<option value="${escapeHtml(date)}">${escapeHtml(date)}</option>`)
                    .join('');
            } catch (error) {
                console.error('Error loading dates:', error);
                dateSelect.innerHTML = '<option value="">- gagal memuat file log -</option>';
                resultBox.innerHTML = `<div class="alert alert-danger p-3 mb-0"><i class="fas fa-exclamation-triangle mr-1"></i> ${escapeHtml(error.message || 'Gagal memuat file log.')}</div>`;
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

                let payload;
                try {
                    payload = await response.json();
                } catch (parseError) {
                    // Jika response bukan JSON, baca sebagai text untuk debugging
                    const text = await response.text().catch(() => 'Tidak dapat membaca response');
                    throw new Error(`Response tidak valid (status: ${response.status}). Server mengembalikan: ${text.substring(0, 200)}`);
                }

                if (!response.ok || payload.status !== 'ok') {
                    throw new Error(payload && payload.message ? payload.message : `Gagal memuat isi file log (status: ${response.status}).`);
                }

                renderLogContent(payload.data || {});
            } catch (error) {
                console.error('Error fetching logs:', error);
                resultBox.innerHTML = `<div class="alert alert-danger p-3 mb-0"><i class="fas fa-exclamation-triangle mr-1"></i> ${escapeHtml(error.message || 'Gagal memuat isi file log.')}</div>`;
            }
        };

        dateSelect.addEventListener('change', (event) => {
            const selectedValue = event.target.value || '';
            console.log('Date selected:', selectedValue);
            if (!selectedValue) {
                resultBox.innerHTML = '<div class="alert alert-info p-3 mb-0"><i class="fas fa-info-circle mr-1"></i> Silakan pilih file log dari dropdown di atas.</div>';
                return;
            }
            fetchLogsByDate(selectedValue);
        });

        // Fallback: juga listen dengan jQuery jika native event tidak berfungsi
        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.jquery) {
            window.jQuery('#navbarErrorLogDate').on('change', function() {
                const selectedValue = window.jQuery(this).val() || '';
                console.log('jQuery change event, value:', selectedValue);
                if (!selectedValue) {
                    resultBox.innerHTML = '<div class="alert alert-info p-3 mb-0"><i class="fas fa-info-circle mr-1"></i> Silakan pilih file log dari dropdown di atas.</div>';
                    return;
                }
                fetchLogsByDate(selectedValue);
            });
        }

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

            // Extract Database Modal Logic
            const extractTableContainer = document.getElementById('navbarExtractTableContainer');
            const extractSearchInput = document.getElementById('navbarExtractSearchInput');
            const extractSortSelect = document.getElementById('navbarExtractSortSelect');
            const extractSelectAllBtn = document.getElementById('navbarExtractSelectAllBtn');
            const extractDeselectAllBtn = document.getElementById('navbarExtractDeselectAllBtn');
            const extractCountBadge = document.getElementById('navbarExtractCountBadge');
            const extractSubmitBtn = document.getElementById('navbarExtractSubmitBtn');
            let rawExtractTablesData = [];

            const updateExtractCounter = () => {
                if (!extractTableContainer || !extractCountBadge || !extractSubmitBtn) {
                    return;
                }

                const checkedBoxes = extractTableContainer.querySelectorAll('.navbar-table-checkbox:checked');
                const totalTables = extractTableContainer.querySelectorAll('.navbar-table-checkbox').length;
                const count = checkedBoxes.length;

                extractCountBadge.textContent = `${count} dari ${totalTables} tabel dipilih`;
                extractSubmitBtn.disabled = count === 0;

                if (count > 0) {
                    extractSubmitBtn.innerHTML = count === totalTables
                        ? '<i class="fas fa-download mr-1"></i> Ekstrak Seluruh Database'
                        : `<i class="fas fa-download mr-1"></i> Ekstrak (${count}) Tabel Terpilih`;
                } else {
                    extractSubmitBtn.innerHTML = '<i class="fas fa-download mr-1"></i> Ekstrak Tabel Terpilih';
                }
            };

            const sortExtractTablesData = (tables, sortKey) => {
                const sorted = [...tables];
                switch (sortKey) {
                    case 'size_desc':
                        sorted.sort((a, b) => (Number(b.bytes || 0) - Number(a.bytes || 0)));
                        break;
                    case 'size_asc':
                        sorted.sort((a, b) => (Number(a.bytes || 0) - Number(b.bytes || 0)));
                        break;
                    case 'rows_desc':
                        sorted.sort((a, b) => (Number(b.rows || 0) - Number(a.rows || 0)));
                        break;
                    case 'rows_asc':
                        sorted.sort((a, b) => (Number(a.rows || 0) - Number(b.rows || 0)));
                        break;
                    case 'name_asc':
                    default:
                        sorted.sort((a, b) => String(a.name || '').localeCompare(String(b.name || '')));
                        break;
                }
                return sorted;
            };

            const renderExtractTables = (tables) => {
                if (!extractTableContainer) {
                    return;
                }

                if (!Array.isArray(tables) || tables.length === 0) {
                    extractTableContainer.innerHTML = '<div class="alert alert-warning p-3 mb-0"><i class="fas fa-exclamation-triangle mr-1"></i> Tidak ada tabel ditemukan.</div>';
                    updateExtractCounter();
                    return;
                }

                // Preserve checked states across sorts
                const checkedSet = new Set();
                extractTableContainer.querySelectorAll('.navbar-table-checkbox:checked').forEach((cb) => {
                    checkedSet.add(cb.value);
                });

                const sortKey = extractSortSelect ? extractSortSelect.value : 'name_asc';
                const sortedTables = sortExtractTablesData(tables, sortKey);

                extractTableContainer.innerHTML = sortedTables.map((t, idx) => {
                    const name = escapeHtml(t.name);
                    const rows = Number(t.rows || 0);
                    const size = escapeHtml(t.size_formatted || '0 B');
                    const isChecked = checkedSet.has(t.name) ? 'checked' : '';
                    return `
                        <div class="d-flex align-items-center justify-content-between py-2 px-3 border-bottom navbar-table-item" data-table-name="${name.toLowerCase()}">
                            <div class="d-flex align-items-center" style="gap: 10px;">
                                <input type="checkbox" class="navbar-table-checkbox" id="chk_tbl_${idx}" name="tables[]" value="${name}" ${isChecked} style="width: 18px; height: 18px; cursor: pointer; accent-color: #007bff; flex-shrink: 0;">
                                <label for="chk_tbl_${idx}" class="mb-0 text-dark font-weight-bold" style="cursor: pointer; user-select: none;">
                                    <i class="fas fa-table text-secondary mr-1"></i> ${name}
                                </label>
                            </div>
                            <div class="d-flex align-items-center" style="gap: 6px;">
                                <span class="badge badge-light border text-muted px-2 py-1"><i class="fas fa-hdd text-info mr-1"></i>${size}</span>
                                <span class="badge badge-light border text-muted px-2 py-1">~${rows.toLocaleString('id-ID')} baris</span>
                            </div>
                        </div>
                    `;
                }).join('');

                const checkboxes = extractTableContainer.querySelectorAll('.navbar-table-checkbox');
                checkboxes.forEach((cb) => cb.addEventListener('change', updateExtractCounter));

                // Re-apply search query filter if any
                if (extractSearchInput && extractSearchInput.value.trim()) {
                    const query = extractSearchInput.value.trim().toLowerCase();
                    const items = extractTableContainer.querySelectorAll('.navbar-table-item');
                    items.forEach((item) => {
                        const tableName = item.getAttribute('data-table-name') || '';
                        if (tableName.includes(query)) {
                            item.classList.remove('d-none');
                        } else {
                            item.classList.add('d-none');
                        }
                    });
                }

                updateExtractCounter();
            };

            const loadExtractTables = async () => {
                if (!extractTableContainer) {
                    return;
                }
                extractTableContainer.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat daftar tabel database...</div>';

                try {
                    const response = await fetch('<?= site_url('/admin/pengaturan/application/database-tables'); ?>', {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    const payload = await response.json();
                    if (!response.ok || payload.status !== 'ok') {
                        throw new Error(payload.message || 'Gagal memuat tabel database.');
                    }
                    rawExtractTablesData = payload.data || [];
                    renderExtractTables(rawExtractTablesData);
                } catch (error) {
                    console.error('Error loading extract tables:', error);
                    extractTableContainer.innerHTML = `<div class="alert alert-danger p-3 mb-0"><i class="fas fa-exclamation-triangle mr-1"></i> ${escapeHtml(error.message || 'Gagal memuat daftar tabel.')}</div>`;
                    updateExtractCounter();
                }
            };

            if (extractSearchInput) {
                extractSearchInput.addEventListener('input', (e) => {
                    const query = String(e.target.value || '').trim().toLowerCase();
                    const items = extractTableContainer ? extractTableContainer.querySelectorAll('.navbar-table-item') : [];
                    items.forEach((item) => {
                        const tableName = item.getAttribute('data-table-name') || '';
                        if (!query || tableName.includes(query)) {
                            item.classList.remove('d-none');
                        } else {
                            item.classList.add('d-none');
                        }
                    });
                });
            }

            if (extractSortSelect) {
                extractSortSelect.addEventListener('change', () => {
                    renderExtractTables(rawExtractTablesData);
                });
            }

            if (extractSelectAllBtn) {
                extractSelectAllBtn.addEventListener('click', () => {
                    if (!extractTableContainer) return;
                    const items = extractTableContainer.querySelectorAll('.navbar-table-item');
                    items.forEach((item) => {
                        if (!item.classList.contains('d-none')) {
                            const cb = item.querySelector('.navbar-table-checkbox');
                            if (cb) cb.checked = true;
                        }
                    });
                    updateExtractCounter();
                });
            }

            if (extractDeselectAllBtn) {
                extractDeselectAllBtn.addEventListener('click', () => {
                    if (!extractTableContainer) return;
                    const checkboxes = extractTableContainer.querySelectorAll('.navbar-table-checkbox');
                    checkboxes.forEach((cb) => cb.checked = false);
                    updateExtractCounter();
                });
            }

            window.jQuery('#extractDatabaseModalNavbar').on('show.bs.modal', () => {
                if (extractSearchInput) extractSearchInput.value = '';
                if (extractSortSelect) extractSortSelect.value = 'name_asc';
                loadExtractTables();
            });

            window.jQuery('#extractDatabaseModalNavbar').on('hide.bs.modal', () => {
                blurActiveElementInModal('extractDatabaseModalNavbar');
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
