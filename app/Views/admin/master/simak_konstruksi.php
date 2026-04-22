<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<script src="<?= base_url('assets/adminlte/plugins/sortablejs/Sortable.min.js'); ?>"></script>
<style>
    .simak-split-layout {
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        gap: 16px;
    }

    @media (max-width: 991.98px) {
        .simak-split-layout {
            grid-template-columns: 1fr;
        }
    }

    .simak-tree-panel,
    .simak-detail-panel {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #fff;
    }

    .simak-detail-panel {
        position: sticky;
        top: 72px;
        align-self: start;
    }

    @media (max-width: 991.98px) {
        .simak-detail-panel {
            position: static;
            top: auto;
        }
    }

    .simak-panel-head {
        padding: 12px 14px;
        border-bottom: 1px solid #eef2f7;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .simak-panel-head .form-control-sm {
        max-width: 180px;
    }

    .simak-panel-meta {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
        margin-top: 4px;
    }

    .simak-panel-meta .badge {
        font-size: 11px;
        font-weight: 600;
    }

    .simak-panel-body {
        padding: 14px;
    }

    .simak-master-tree,
    .simak-master-tree ul {
        list-style: none;
        margin: 0;
        padding-left: 0;
    }

    .simak-master-tree ul {
        margin-left: 24px;
        border-left: 1px dashed #d0d7de;
        padding-left: 12px;
    }

    .simak-master-item {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        margin-bottom: 10px;
        background: #fff;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .simak-master-item.is-inactive {
        opacity: 0.68;
        background: #f8fafc;
    }

    .simak-master-item.is-selected {
        border-color: #2563eb;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12);
    }

    .simak-master-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
    }

    .drag-handle {
        cursor: grab;
        color: #6b7280;
    }

    .simak-master-meta {
        flex: 1;
        min-width: 0;
        cursor: pointer;
    }

    .simak-master-title {
        font-weight: 600;
        line-height: 1.3;
    }

    .simak-master-sub {
        color: #6b7280;
        font-size: 12px;
        margin-top: 2px;
    }

    .simak-status-badge {
        display: inline-block;
        margin-left: 6px;
        font-size: 11px;
        vertical-align: middle;
    }

    .empty-tree {
        border: 1px dashed #d1d5db;
        border-radius: 8px;
        padding: 18px;
        color: #6b7280;
    }

    .simak-form-hint {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 12px;
    }

    .simak-notice-stack {
        position: fixed;
        top: 76px;
        right: 20px;
        z-index: 1090;
        display: flex;
        flex-direction: column;
        gap: 10px;
        pointer-events: none;
        width: min(360px, calc(100vw - 40px));
    }

    .simak-toast {
        pointer-events: auto;
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 10px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.15);
        overflow: hidden;
        background: #fff;
        animation: simak-toast-in 0.18s ease-out;
    }

    .simak-toast.is-hiding {
        animation: simak-toast-out 0.18s ease-in forwards;
    }

    .simak-toast-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 12px;
        font-weight: 600;
        font-size: 13px;
    }

    .simak-toast-body {
        padding: 10px 12px 12px;
        font-size: 13px;
        line-height: 1.4;
        color: #1f2937;
    }

    .simak-toast-success .simak-toast-header {
        background: #ecfdf3;
        color: #166534;
    }

    .simak-toast-danger .simak-toast-header {
        background: #fef2f2;
        color: #991b1b;
    }

    .simak-toast-warning .simak-toast-header {
        background: #fffbeb;
        color: #92400e;
    }

    .simak-toast-info .simak-toast-header {
        background: #eff6ff;
        color: #1d4ed8;
    }

    @keyframes simak-toast-in {
        from {
            opacity: 0;
            transform: translateY(-8px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes simak-toast-out {
        from {
            opacity: 1;
            transform: translateY(0);
        }

        to {
            opacity: 0;
            transform: translateY(-8px);
        }
    }
</style>

<div class="card">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title mb-0"><?= esc((string) ($pageTitle ?? 'Master SIMAK Konstruksi')); ?></h3>
        <?php if (! empty($can_edit)): ?>
            <button type="button" class="btn btn-success btn-sm ml-auto" id="btn-save-hierarchy">Simpan Hirarki</button>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3"><?= esc((string) ($pageSubtitle ?? 'Master pertanyaan, hirarki, dan section verifikasi SIMAK konstruksi.')); ?></p>
        <div id="simak-notice-stack" class="simak-notice-stack" aria-live="polite" aria-atomic="true"></div>

        <?php
            $renderTree = static function (array $nodes) use (&$renderTree): void {
                echo '<ul class="simak-master-tree-list">';
                foreach ($nodes as $node) {
                    $id = (int) ($node['id'] ?? 0);
                    $displayNo = trim((string) ($node['display_no'] ?? ''));
                    $uraian = trim((string) ($node['uraian'] ?? ''));
                    $title = trim($displayNo . ' ' . $uraian);
                    $rowKind = (string) ($node['row_kind'] ?? 'question');
                    $hasQuestion = (int) ($node['has_question'] ?? 0) === 1;
                    $isActive = (int) ($node['is_active'] ?? 1) === 1;
                    $children = is_array($node['children'] ?? null) ? $node['children'] : [];

                    echo '<li class="simak-master-item' . (! $isActive ? ' is-inactive' : '') . '" data-id="' . $id . '"';
                    echo ' data-parent_id="' . esc((string) ($node['parent_id'] ?? ''), 'attr') . '"';
                    echo ' data-display_no="' . esc((string) ($node['display_no'] ?? ''), 'attr') . '"';
                    echo ' data-uraian="' . esc((string) ($node['uraian'] ?? ''), 'attr') . '"';
                    echo ' data-row_kind="' . esc($rowKind, 'attr') . '"';
                    echo ' data-has_question="' . ($hasQuestion ? '1' : '0') . '"';
                    echo ' data-is_active="' . ($isActive ? '1' : '0') . '"';
                    echo '>';
                    echo '<div class="simak-master-row">';
                    echo '<span class="drag-handle" title="Drag untuk ubah urutan/hirarki"><i class="fas fa-grip-lines"></i></span>';
                    echo '<div class="simak-master-meta">';
                    echo '<div class="simak-master-title">' . esc($title !== '' ? $title : '-') . ' <span class="badge ' . ($isActive ? 'badge-success' : 'badge-secondary') . ' simak-status-badge">' . ($isActive ? 'Aktif' : 'Nonaktif') . '</span></div>';
                    echo '<div class="simak-master-sub">';
                    echo 'Jenis: <strong>' . esc($rowKind) . '</strong> | Pertanyaan: <strong>' . ($hasQuestion ? 'Ya' : 'Tidak') . '</strong> | Row No: <strong>' . esc((string) ($node['row_no'] ?? '')) . '</strong>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';

                    if ($children !== []) {
                        $renderTree($children);
                    }

                    echo '</li>';
                }
                echo '</ul>';
            };
        ?>

        <div class="simak-split-layout">
            <div class="simak-tree-panel">
                <div class="simak-panel-head">
                    <div>
                        <strong>Hirarki Pertanyaan</strong>
                        <div class="simak-panel-meta">
                            <span class="badge badge-success" id="simak-count-active">Aktif: 0</span>
                            <span class="badge badge-secondary" id="simak-count-inactive">Nonaktif: 0</span>
                        </div>
                    </div>
                    <small class="text-muted d-none d-md-inline">Klik item untuk edit di panel kanan</small>
                </div>
                <div class="simak-panel-body">
                    <?php if (! empty($itemsTree ?? [])): ?>
                        <ul class="simak-master-tree" id="simak-master-root">
                            <?php $renderTree($itemsTree); ?>
                        </ul>
                    <?php else: ?>
                        <div class="empty-tree">
                            Master SIMAK konstruksi belum memiliki item. Gunakan tombol "Tambah Root" di panel kanan.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="simak-detail-panel">
                <div class="simak-panel-head">
                    <strong id="form-mode-label">Tambah Item Master</strong>
                    <div>
                        <?php if (! empty($can_add)): ?>
                            <button type="button" class="btn btn-primary btn-sm" id="btn-add-root">Tambah Root</button>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-child">Tambah Child</button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="simak-panel-body">
                    <div class="simak-form-hint">Pilih item di kiri untuk mode ubah. Untuk tambah baru, gunakan tombol Tambah Root atau Tambah Child.</div>

                    <form method="post" id="form-master-simak" action="<?= site_url('/admin/master/simak/konstruksi/tambah'); ?>">
                        <?= csrf_field(); ?>
                        <input type="hidden" id="selected_id" value="">

                        <div class="form-group">
                            <label for="parent_id">Parent</label>
                            <select class="form-control" name="parent_id" id="parent_id" <?= empty($can_edit) && empty($can_add) ? 'disabled' : ''; ?>>
                                <option value="">(Root)</option>
                                <?php foreach (($parentOptions ?? []) as $opt): ?>
                                    <option value="<?= (int) ($opt['id'] ?? 0); ?>"><?= esc((string) ($opt['label'] ?? '')); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="display_no">Nomor Tampil</label>
                            <input type="text" class="form-control" name="display_no" id="display_no" placeholder="Contoh: A, 1, a" <?= empty($can_edit) && empty($can_add) ? 'disabled' : ''; ?>>
                        </div>
                        <div class="form-group">
                            <label for="uraian">Uraian</label>
                            <textarea class="form-control" name="uraian" id="uraian" rows="3" required <?= empty($can_edit) && empty($can_add) ? 'disabled' : ''; ?>></textarea>
                        </div>
                        <div class="form-group">
                            <label for="row_kind">Jenis Baris</label>
                            <select class="form-control" name="row_kind" id="row_kind" required <?= empty($can_edit) && empty($can_add) ? 'disabled' : ''; ?>>
                                <option value="section">Section</option>
                                <option value="group">Group/Sub Section</option>
                                <option value="question">Pertanyaan</option>
                                <option value="text">Teks Tanpa Pertanyaan</option>
                                <option value="separator">Separator</option>
                            </select>
                        </div>
                        <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" class="custom-control-input" id="has_question" name="has_question" value="1" <?= empty($can_edit) && empty($can_add) ? 'disabled' : ''; ?>>
                            <label class="custom-control-label" for="has_question">Item ini memiliki pertanyaan</label>
                        </div>

                        <div class="d-flex flex-wrap" style="gap:8px;">
                            <?php if (! empty($can_add) || ! empty($can_edit)): ?>
                                <button type="submit" class="btn btn-primary" id="btn-submit-form">Simpan</button>
                                <button type="button" class="btn btn-outline-secondary" id="btn-reset-selection">Reset</button>
                            <?php endif; ?>
                            <?php if (! empty($can_edit)): ?>
                                <button type="button" class="btn btn-outline-secondary" id="btn-toggle-status" disabled>Aktifkan/Nonaktifkan Item</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var root = document.getElementById('simak-master-root');
        var saveButton = document.getElementById('btn-save-hierarchy');
        var form = document.getElementById('form-master-simak');
        var formModeLabel = document.getElementById('form-mode-label');
        var selectedIdInput = document.getElementById('selected_id');
        var parentSelect = document.getElementById('parent_id');
        var displayNoInput = document.getElementById('display_no');
        var uraianInput = document.getElementById('uraian');
        var rowKindSelect = document.getElementById('row_kind');
        var hasQuestionInput = document.getElementById('has_question');
        var addRootButton = document.getElementById('btn-add-root');
        var addChildButton = document.getElementById('btn-add-child');
        var resetButton = document.getElementById('btn-reset-selection');
        var toggleStatusButton = null;
        var countActiveBadge = document.getElementById('simak-count-active');
        var countInactiveBadge = document.getElementById('simak-count-inactive');
        var treePanelBody = document.querySelector('.simak-tree-panel .simak-panel-body');
        var noticeStack = document.getElementById('simak-notice-stack');
        var csrfName = <?= json_encode(csrf_token(), JSON_UNESCAPED_UNICODE); ?>;
        var csrfValue = <?= json_encode(csrf_hash(), JSON_UNESCAPED_UNICODE); ?>;
        var addUrl = <?= json_encode(site_url('/admin/master/simak/konstruksi/tambah'), JSON_UNESCAPED_UNICODE); ?>;
        var baseUrl = <?= json_encode(site_url('/admin/master/simak/konstruksi'), JSON_UNESCAPED_UNICODE); ?>;

        var showNotice = function (message, type) {
            if (!message || !noticeStack) return;

            var level = ['success', 'danger', 'warning', 'info'].indexOf(type) >= 0 ? type : 'info';
            var titles = {
                success: 'Berhasil',
                danger: 'Gagal',
                warning: 'Perhatian',
                info: 'Info'
            };
            var note = document.createElement('div');
            note.className = 'simak-toast simak-toast-' + level;
            note.setAttribute('role', 'status');
            note.setAttribute('aria-live', 'polite');
            note.innerHTML = '' +
                '<div class="simak-toast-header">' +
                '  <span>' + titles[level] + '</span>' +
                '  <button type="button" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                '</div>' +
                '<div class="simak-toast-body"></div>';
            note.querySelector('.simak-toast-body').textContent = message;

            noticeStack.prepend(note);

            var closeButton = note.querySelector('button.close');
            var removeToast = function () {
                if (!note.parentNode) return;
                note.classList.add('is-hiding');
                window.setTimeout(function () {
                    if (note.parentNode) {
                        note.parentNode.removeChild(note);
                    }
                }, 180);
            };

            if (closeButton) {
                closeButton.addEventListener('click', removeToast);
            }

            window.setTimeout(function () {
                removeToast();
            }, 3500);
        };

        var syncCsrfFromJson = function (json) {
            if (!json || !json.csrf) return;
            if (typeof json.csrf.name === 'string' && json.csrf.name !== '') {
                csrfName = json.csrf.name;
            }
            if (typeof json.csrf.hash === 'string' && json.csrf.hash !== '') {
                csrfValue = json.csrf.hash;
            }
        };

        if (rowKindSelect) {
            rowKindSelect.addEventListener('change', function () {
                if (!hasQuestionInput) return;
                if (rowKindSelect.value === 'question') {
                    hasQuestionInput.checked = true;
                    hasQuestionInput.disabled = true;
                } else {
                    hasQuestionInput.disabled = false;
                }
            });
        }

        var clearSelection = function () {
            if (!root) return;
            root.querySelectorAll('.simak-master-item.is-selected').forEach(function (el) {
                el.classList.remove('is-selected');
            });
        };

        var updateToggleButton = function (isActive) {
            if (!toggleStatusButton) return;

            toggleStatusButton.disabled = false;
            if (isActive) {
                toggleStatusButton.textContent = 'Nonaktifkan Item';
                toggleStatusButton.className = 'btn btn-warning btn-sm';
            } else {
                toggleStatusButton.textContent = 'Aktifkan Item';
                toggleStatusButton.className = 'btn btn-success btn-sm';
            }
        };

        var setCreateMode = function (parentId, label) {
            if (!form) return;
            form.setAttribute('action', addUrl);
            if (formModeLabel) formModeLabel.textContent = label || 'Tambah Item Master';
            if (selectedIdInput) selectedIdInput.value = '';
            if (parentSelect) parentSelect.value = parentId || '';
            if (displayNoInput) displayNoInput.value = '';
            if (uraianInput) uraianInput.value = '';
            if (rowKindSelect) rowKindSelect.value = 'section';
            if (hasQuestionInput) {
                hasQuestionInput.checked = false;
                hasQuestionInput.disabled = false;
            }
            if (toggleStatusButton) {
                toggleStatusButton.disabled = true;
                toggleStatusButton.textContent = 'Aktifkan/Nonaktifkan Item';
                toggleStatusButton.className = 'btn btn-outline-secondary btn-sm';
            }
        };

        var setEditModeFromItem = function (itemEl) {
            if (!itemEl || !form) return;
            var id = itemEl.getAttribute('data-id') || '';
            clearSelection();
            itemEl.classList.add('is-selected');

            form.setAttribute('action', baseUrl + '/' + encodeURIComponent(id) + '/ubah');
            if (formModeLabel) formModeLabel.textContent = 'Ubah Item #' + id;
            if (selectedIdInput) selectedIdInput.value = id;
            if (parentSelect) parentSelect.value = itemEl.getAttribute('data-parent_id') || '';
            if (displayNoInput) displayNoInput.value = itemEl.getAttribute('data-display_no') || '';
            if (uraianInput) uraianInput.value = itemEl.getAttribute('data-uraian') || '';
            if (rowKindSelect) rowKindSelect.value = itemEl.getAttribute('data-row_kind') || 'question';
            if (hasQuestionInput) {
                hasQuestionInput.checked = (itemEl.getAttribute('data-has_question') || '0') === '1';
                hasQuestionInput.disabled = rowKindSelect && rowKindSelect.value === 'question';
            }
            if (toggleStatusButton) {
                updateToggleButton((itemEl.getAttribute('data-is_active') || '1') === '1');
            }
        };

        if (addRootButton) {
            addRootButton.addEventListener('click', function () {
                clearSelection();
                setCreateMode('', 'Tambah Item Root');
            });
        }

        if (addChildButton) {
            addChildButton.addEventListener('click', function () {
                var selectedId = selectedIdInput ? selectedIdInput.value : '';
                if (!selectedId) {
                    showNotice('Pilih item parent di panel kiri terlebih dahulu.', 'warning');
                    return;
                }
                setCreateMode(selectedId, 'Tambah Child dari Item #' + selectedId);
            });
        }

        if (resetButton) {
            resetButton.addEventListener('click', function () {
                clearSelection();
                setCreateMode('', 'Tambah Item Master');
            });
        }

        toggleStatusButton = document.getElementById('btn-toggle-status');

        var bindTreeItemClicks = function () {
            if (!root) return;
            root.querySelectorAll('.simak-master-item .simak-master-meta').forEach(function (metaEl) {
                metaEl.addEventListener('click', function () {
                    var itemEl = metaEl.closest('.simak-master-item');
                    setEditModeFromItem(itemEl);
                });
            });
        };

        var updateStatusSummary = function () {
            var activeCount = 0;
            var inactiveCount = 0;

            if (!root) {
                if (countActiveBadge) countActiveBadge.textContent = 'Aktif: 0';
                if (countInactiveBadge) countInactiveBadge.textContent = 'Nonaktif: 0';
                return;
            }

            root.querySelectorAll('.simak-master-item').forEach(function (itemEl) {
                var isActive = (itemEl.getAttribute('data-is_active') || '1') === '1';
                if (isActive) {
                    activeCount++;
                } else {
                    inactiveCount++;
                }
            });

            if (countActiveBadge) countActiveBadge.textContent = 'Aktif: ' + activeCount;
            if (countInactiveBadge) countInactiveBadge.textContent = 'Nonaktif: ' + inactiveCount;
        };

        bindTreeItemClicks();

        var refreshPanels = function (selectedId, formLabel) {
            return fetch(window.location.href, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Gagal menyegarkan data');
                    }
                    return response.text();
                })
                .then(function (html) {
                    var parser = new DOMParser();
                    var doc = parser.parseFromString(html, 'text/html');
                    var freshTreePanelBody = doc.querySelector('.simak-tree-panel .simak-panel-body');
                    var freshParentSelect = doc.getElementById('parent_id');

                    if (treePanelBody && freshTreePanelBody) {
                        treePanelBody.innerHTML = freshTreePanelBody.innerHTML;
                    }

                    if (parentSelect && freshParentSelect) {
                        parentSelect.innerHTML = freshParentSelect.innerHTML;
                    }

                    root = document.getElementById('simak-master-root');
                    bindTreeItemClicks();
                    updateStatusSummary();

                    if (root) {
                        root.querySelectorAll('ul.simak-master-tree-list').forEach(initSortable);
                    }

                    if (selectedId) {
                        var selectedEl = document.querySelector('.simak-master-item[data-id="' + selectedId + '"]');
                        if (selectedEl) {
                            setEditModeFromItem(selectedEl);
                            return;
                        }
                    }

                    setCreateMode('', formLabel || 'Tambah Item Master');
                });
        };

        var toggleStatusAjax = function () {
            if (!toggleStatusButton || !selectedIdInput || !selectedIdInput.value) {
                return;
            }

            var selectedId = selectedIdInput.value;
            var currentItem = document.querySelector('.simak-master-item[data-id="' + selectedId + '"]');
            var isActive = currentItem ? (currentItem.getAttribute('data-is_active') || '1') === '1' : true;
            var nextStatus = isActive ? 0 : 1;

            toggleStatusButton.disabled = true;

            var formData = new FormData();
            formData.append('is_active', String(nextStatus));
            formData.append(csrfName, csrfValue);

            fetch(baseUrl + '/' + encodeURIComponent(selectedId) + '/status', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(function (response) {
                    return response.json().then(function (json) {
                        return { ok: response.ok, json: json };
                    });
                })
                .then(function (result) {
                    var json = result.json || {};
                    syncCsrfFromJson(json);
                    if (!result.ok || json.status !== 'ok') {
                        showNotice(json.message || 'Gagal mengubah status item.', 'danger');
                        return;
                    }

                    refreshPanels(selectedId, formModeLabel ? formModeLabel.textContent : 'Tambah Item Master').then(function () {
                        showNotice(json.message || 'Status item berhasil diubah.', 'success');
                    });
                })
                .catch(function () {
                    showNotice('Gagal mengubah status item.', 'danger');
                })
                .finally(function () {
                    if (toggleStatusButton) {
                        toggleStatusButton.disabled = false;
                    }
                });
        };

        var submitMasterFormAjax = function () {
            if (!form) return;

            form.addEventListener('submit', function (event) {
                event.preventDefault();

                var submitButton = document.getElementById('btn-submit-form');
                if (submitButton) submitButton.disabled = true;

                var formData = new FormData(form);
                formData.set(csrfName, csrfValue);

                fetch(form.getAttribute('action'), {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                    .then(function (response) {
                        return response.json().then(function (json) {
                            return { ok: response.ok, json: json };
                        });
                    })
                    .then(function (result) {
                        var json = result.json || {};
                        syncCsrfFromJson(json);
                        if (!result.ok || json.status !== 'ok') {
                            showNotice(json.message || 'Gagal menyimpan data master.', 'danger');
                            return;
                        }

                        var id = json.id ? String(json.id) : '';
                        return refreshPanels(id, 'Tambah Item Master').then(function () {
                            showNotice(json.message || 'Berhasil menyimpan data master.', 'success');
                        });
                    })
                    .catch(function () {
                        showNotice('Gagal menyimpan data master.', 'danger');
                    })
                    .finally(function () {
                        if (submitButton) submitButton.disabled = false;
                    });
            });
        };

        submitMasterFormAjax();

        var initSortable = function (listEl) {
            if (!listEl || typeof Sortable === 'undefined') return;
            Sortable.create(listEl, {
                group: 'simak-master',
                animation: 150,
                handle: '.drag-handle',
                draggable: '> li.simak-master-item',
                fallbackOnBody: true,
                swapThreshold: 0.65
            });
        };

        if (root) {
            root.querySelectorAll('ul.simak-master-tree-list').forEach(initSortable);
        }

        var serializeTree = function (listEl) {
            var result = [];
            var children = listEl ? Array.prototype.slice.call(listEl.children) : [];

            children.forEach(function (child) {
                if (!child.classList.contains('simak-master-item')) return;
                var id = parseInt(child.getAttribute('data-id') || '0', 10);
                if (!id) return;

                var nested = child.querySelector(':scope > ul.simak-master-tree-list');
                result.push({
                    id: id,
                    children: nested ? serializeTree(nested) : []
                });
            });

            return result;
        };

        if (saveButton) {
            saveButton.addEventListener('click', function () {
                if (!root) return;

                var topLevel = root.querySelector(':scope > ul.simak-master-tree-list');
                var treePayload = serializeTree(topLevel);

                var formData = new FormData();
                formData.append('tree', JSON.stringify(treePayload));
                formData.append(csrfName, csrfValue);

                fetch(<?= json_encode(site_url('/admin/master/simak/konstruksi/simpan-hirarki'), JSON_UNESCAPED_UNICODE); ?>, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                    .then(function (response) { return response.json(); })
                    .then(function (json) {
                        syncCsrfFromJson(json);
                        if (!json || json.status !== 'ok') {
                            showNotice((json && json.message) ? json.message : 'Gagal menyimpan hirarki', 'danger');
                            return;
                        }
                        refreshPanels(selectedIdInput ? selectedIdInput.value : '', formModeLabel ? formModeLabel.textContent : 'Tambah Item Master')
                            .then(function () {
                                showNotice(json.message || 'Hirarki berhasil disimpan.', 'success');
                            });
                    })
                    .catch(function () {
                        showNotice('Gagal menyimpan hirarki', 'danger');
                    });
            });
        }

        if (toggleStatusButton) {
            toggleStatusButton.addEventListener('click', function () {
                toggleStatusAjax();
            });
        }

        updateStatusSummary();
        setCreateMode('', 'Tambah Item Master');
    })();
</script>
<?= $this->endSection(); ?>
