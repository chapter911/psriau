<script src="<?= base_url('assets/adminlte/plugins/jquery/jquery.min.js'); ?>"></script>
<script src="<?= base_url('assets/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
<script src="<?= base_url('assets/adminlte/plugins/datatables/jquery.dataTables.min.js'); ?>"></script>
<script src="<?= base_url('assets/adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js'); ?>"></script>
<script src="<?= base_url('assets/adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js'); ?>"></script>
<script src="<?= base_url('assets/adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js'); ?>"></script>
<script src="<?= base_url('assets/adminlte/plugins/select2/js/select2.full.min.js'); ?>"></script>
<script src="<?= base_url('assets/adminlte/dist/js/adminlte.min.js'); ?>"></script>
<script src="<?= base_url('assets/adminlte/plugins/sweetalert2/sweetalert2.all.min.js'); ?>"></script>
<script>
    (() => {
        const preloaderShownAt = typeof window.__appPreloaderStart === 'number'
            ? window.__appPreloaderStart
            : (typeof performance !== 'undefined' ? performance.now() : Date.now());
        const minimumVisibleMs = Number(document.body?.dataset.preloaderDuration || <?= (int) ($appSetting['preloader_duration_ms'] ?? 500); ?>);

        window.addEventListener('load', () => {
            const preloader = document.querySelector('.preloader');
            if (!preloader) {
                return;
            }

            const now = typeof performance !== 'undefined' ? performance.now() : Date.now();
            const remaining = Math.max(0, minimumVisibleMs - (now - preloaderShownAt));

            window.setTimeout(() => {
                preloader.style.transition = 'opacity .28s ease';
                preloader.style.opacity = '0';
                window.setTimeout(() => {
                    preloader.style.display = 'none';
                }, 300);
            }, remaining);
        });
    })();

    (() => {
        if (typeof $ === 'undefined' || ! $.fn || ! $.fn.select2) {
            return;
        }

        const initSelect2 = (root) => {
            const $root = root ? $(root) : $(document);
            const $targets = $root.find('select').add($root.filter('select'));

            const buildPlaceholder = ($select) => {
                const explicitPlaceholder = ($select.data('placeholder') || '').toString().trim();
                if (explicitPlaceholder !== '') {
                    return explicitPlaceholder;
                }

                const firstOption = $select.find('option').first();
                if (firstOption.length === 0) {
                    return '';
                }

                const firstValue = (firstOption.attr('value') || '').toString().trim();
                const firstText = (firstOption.text() || '').toString().trim();
                if (firstValue === '' && firstText !== '') {
                    return firstText;
                }

                return '';
            };

            $targets.each(function () {
                const $select = $(this);

                if ($select.closest('.swal2-container, .swal2-popup').length > 0 || $select.hasClass('swal2-select')) {
                    return;
                }

                if ($select.closest('.dataTables_length').length > 0 || ($select.attr('name') || '').endsWith('_length')) {
                    return;
                }

                if ($select.hasClass('no-select2') || $select.data('select2')) {
                    return;
                }

                const inModal = $select.closest('.modal').length > 0;
                const options = {
                    theme: 'bootstrap4',
                    width: '100%',
                    minimumResultsForSearch: 0,
                    closeOnSelect: false,
                    selectOnClose: false,
                };

                const placeholder = buildPlaceholder($select);
                if (placeholder !== '') {
                    options.placeholder = placeholder;
                }

                const isRequired = $select.prop('required') === true;
                const isMultiple = $select.prop('multiple') === true;
                if (!isRequired && !isMultiple) {
                    options.allowClear = true;
                }

                if (inModal) {
                    options.dropdownParent = $select.closest('.modal');
                }

                $select.select2(options);
            });
        };

        initSelect2(document);

        $(document).on('shown.bs.modal', '.modal', function () {
            initSelect2(this);
        });

        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) {
                        initSelect2(node);
                    }
                });
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });
    })();

    (() => {
        if (typeof $ === 'undefined' || ! $.fn.DataTable) return;

        $('.js-datatable').each(function () {
            const tableOrder = $(this).data('order') || [[0, 'asc']];

            $(this).DataTable({
                responsive: false,
                autoWidth: false,
                scrollX: true,
                scrollCollapse: true,
                order: tableOrder,
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    zeroRecords: 'Data tidak ditemukan',
                    paginate: {
                        first: 'Awal',
                        last: 'Akhir',
                        next: 'Berikutnya',
                        previous: 'Sebelumnya'
                    }
                }
            });
        });
    })();

    (() => {
        const buildConfirmConfig = (form, submitter) => {
            const title = (submitter && submitter.dataset.confirmTitle) || form.dataset.confirmTitle || 'Konfirmasi';
            const text = (submitter && submitter.dataset.confirmText) || form.dataset.confirmText || 'Yakin ingin melanjutkan?';
            const confirmButtonText = (submitter && submitter.dataset.confirmButton) || form.dataset.confirmButton || 'Ya, lanjutkan';

            return {
                icon: 'question',
                title,
                text,
                showCancelButton: true,
                confirmButtonText,
                cancelButtonText: 'Batal',
                reverseButtons: true,
                focusCancel: true,
            };
        };

        document.querySelectorAll('form').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (form.dataset.confirmed === '1' || form.dataset.skipConfirm === '1') {
                    return;
                }

                const method = (form.getAttribute('method') || 'get').toLowerCase();
                if (method !== 'post') {
                    return;
                }

                event.preventDefault();

                const submitter = event.submitter || null;
                const config = buildConfirmConfig(form, submitter);
                const fallbackConfirm = () => {
                    const ok = window.confirm(config.text || 'Yakin ingin melanjutkan?');
                    if (ok) {
                        form.dataset.confirmed = '1';
                        if (submitter && typeof form.requestSubmit === 'function') {
                            form.requestSubmit(submitter);
                            return;
                        }

                        form.submit();
                    }
                };

                if (typeof Swal === 'undefined') {
                    fallbackConfirm();
                    return;
                }

                Swal.fire(config).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    form.dataset.confirmed = '1';
                    if (submitter && typeof form.requestSubmit === 'function') {
                        form.requestSubmit(submitter);
                        return;
                    }

                    form.submit();
                });
            });
        });
    })();

    (() => {
        const autoLogoutMinutes = <?= (int) ($appSetting['auto_logout_minutes'] ?? 60); ?>;
        if (autoLogoutMinutes <= 0) {
            return;
        }

        let timerId = null;
        const logoutUrl = '<?= site_url('/keluar'); ?>';
        const resetTimer = () => {
            if (timerId) {
                window.clearTimeout(timerId);
            }

            timerId = window.setTimeout(() => {
                window.location.href = logoutUrl;
            }, autoLogoutMinutes * 60 * 1000);
        };

        ['click', 'keydown', 'mousemove', 'scroll', 'touchstart'].forEach((eventName) => {
            window.addEventListener(eventName, resetTimer, { passive: true });
        });

        resetTimer();
    })();

    (() => {
        const perms = <?= json_encode($currentMenuPermissions ?? [
            'add' => false,
            'edit' => false,
            'delete' => false,
            'export' => false,
            'import' => false,
            'approval' => false,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;

        if (!perms || typeof perms !== 'object') {
            return;
        }

        const hideBySelector = (selector) => {
            document.querySelectorAll(selector).forEach((el) => {
                el.style.display = 'none';
            });
        };

        if (!perms.add) {
            hideBySelector('a[href*="/tambah"], form[action*="/tambah"], button[data-target*="tambah"], button[id*="tambah"], button[id*="Tambah"], button[class*="tambah"], button[class*="Tambah"]');
        }

        if (!perms.edit) {
            hideBySelector('a[href*="/ubah"], form[action*="/ubah"], form[action*="/update"], form[action*="/status"], form[action*="/save"], button[id*="ubah"], button[id*="Ubah"], button[class*="edit"], button[class*="Edit"]');
        }

        if (!perms.delete) {
            hideBySelector('a[href*="/hapus"], form[action*="/hapus"], form[action*="/delete"], button[id*="hapus"], button[id*="Hapus"], button[class*="delete"], button[class*="Delete"]');
        }

        if (!perms.export) {
            hideBySelector('a[href*="/export"], form[action*="/export"], button[id*="export"], button[id*="Export"], button[class*="export"], button[class*="Export"]');
        }

        if (!perms.import) {
            hideBySelector('a[href*="/import"], form[action*="/import"], button[id*="import"], button[id*="Import"], button[class*="import"], button[class*="Import"]');
        }

        if (!perms.approval) {
            hideBySelector('a[href*="/approval"], a[href*="/approve"], form[action*="/approval"], form[action*="/approve"], button[id*="approval"], button[id*="Approval"], button[class*="approval"], button[class*="Approval"]');
        }
    })();
</script>
<?= $this->renderSection('pageScripts'); ?>
