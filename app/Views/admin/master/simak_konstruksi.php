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

    .simak-panel-head {
        padding: 12px 14px;
        border-bottom: 1px solid #eef2f7;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
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
</style>

<div class="card">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title mb-0\"><?= esc((string) ($pageTitle ?? 'Master SIMAK Konstruksi')); ?></h3>
        <?php if (! empty($can_edit)): ?>
            <button type="button" class="btn btn-success btn-sm ml-auto" id="btn-save-hierarchy">Simpan Hirarki</button>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3"><?= esc((string) ($pageSubtitle ?? 'Master pertanyaan, hirarki, dan section verifikasi SIMAK konstruksi.')); ?></p>

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
                    $children = is_array($node['children'] ?? null) ? $node['children'] : [];

                    echo '<li class="simak-master-item" data-id="' . $id . '"';
                    echo ' data-parent_id="' . esc((string) ($node['parent_id'] ?? ''), 'attr') . '"';
                    echo ' data-display_no="' . esc((string) ($node['display_no'] ?? ''), 'attr') . '"';
                    echo ' data-uraian="' . esc((string) ($node['uraian'] ?? ''), 'attr') . '"';
                    echo ' data-row_kind="' . esc($rowKind, 'attr') . '"';
                    echo ' data-has_question="' . ($hasQuestion ? '1' : '0') . '"';
                    echo '>';
                    echo '<div class="simak-master-row">';
                    echo '<span class="drag-handle" title="Drag untuk ubah urutan/hirarki"><i class="fas fa-grip-lines"></i></span>';
                    echo '<div class="simak-master-meta">';
                    echo '<div class="simak-master-title">' . esc($title !== '' ? $title : '-') . '</div>';
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
                    <strong>Hirarki Pertanyaan</strong>
                .simak-detail-panel {
                    position: static;
                    top: auto;
                    <small class="text-muted">Klik item untuk edit di panel kanan</small>
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
                            <?php if (! empty($can_delete)): ?>
                                <button type="button" class="btn btn-danger" id="btn-delete-selected" disabled>Hapus Item Terpilih</button>
                            <?php endif; ?>
                        </div>
                    </form>

                    <?php if (! empty($can_delete)): ?>
                        <form method="post" id="form-delete-selected" action="" class="d-none">
                            <?= csrf_field(); ?>
                        </form>
                    <?php endif; ?>
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
        var deleteSelectedButton = document.getElementById('btn-delete-selected');
        var deleteForm = document.getElementById('form-delete-selected');
        var csrfName = <?= json_encode(csrf_token(), JSON_UNESCAPED_UNICODE); ?>;
        var csrfValue = <?= json_encode(csrf_hash(), JSON_UNESCAPED_UNICODE); ?>;
        var addUrl = <?= json_encode(site_url('/admin/master/simak/konstruksi/tambah'), JSON_UNESCAPED_UNICODE); ?>;
        var baseUrl = <?= json_encode(site_url('/admin/master/simak/konstruksi'), JSON_UNESCAPED_UNICODE); ?>;

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
            if (deleteSelectedButton) deleteSelectedButton.disabled = true;
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
            if (deleteSelectedButton) deleteSelectedButton.disabled = false;
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
                    alert('Pilih item parent di panel kiri terlebih dahulu.');
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

        if (root) {
            root.querySelectorAll('.simak-master-item .simak-master-meta').forEach(function (metaEl) {
                metaEl.addEventListener('click', function () {
                    var itemEl = metaEl.closest('.simak-master-item');
                    setEditModeFromItem(itemEl);
                });
            });
        }

        if (deleteSelectedButton && deleteForm) {
            deleteSelectedButton.addEventListener('click', function () {
                var selectedId = selectedIdInput ? selectedIdInput.value : '';
                if (!selectedId) {
                    return;
                }
                if (!window.confirm('Hapus item terpilih?')) {
                    return;
                }

                deleteForm.setAttribute('action', baseUrl + '/' + encodeURIComponent(selectedId) + '/hapus');
                deleteForm.submit();
            });
        }

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
                        if (!json || json.status !== 'ok') {
                            alert((json && json.message) ? json.message : 'Gagal menyimpan hirarki');
                            return;
                        }
                        window.location.reload();
                    })
                    .catch(function () {
                        alert('Gagal menyimpan hirarki');
                    });
            });
        }

        setCreateMode('', 'Tambah Item Master');
    })();
</script>
<?= $this->endSection(); ?>
