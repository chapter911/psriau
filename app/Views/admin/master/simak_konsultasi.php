<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<script src="<?= base_url('assets/adminlte/plugins/sortablejs/Sortable.min.js'); ?>"></script>
<style>
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

    .simak-master-actions {
        display: flex;
        gap: 6px;
        align-items: center;
        white-space: nowrap;
    }

    .empty-tree {
        border: 1px dashed #d1d5db;
        border-radius: 8px;
        padding: 18px;
        color: #6b7280;
    }

    .textarea-mini {
        min-height: 72px;
    }
</style>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0"><?= esc((string) ($pageTitle ?? 'Master SIMAK Konsultasi')); ?></h3>
        <div>
            <?php if (! empty($can_add)): ?>
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-master-simak-konsultasi" id="btn-add-item-konsultasi">Tambah Item</button>
            <?php endif; ?>
            <?php if (! empty($can_edit)): ?>
                <button type="button" class="btn btn-success btn-sm" id="btn-save-hierarchy-konsultasi">Simpan Hirarki</button>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3"><?= esc((string) ($pageSubtitle ?? 'Master pertanyaan, hirarki, dan section verifikasi SIMAK konsultasi.')); ?></p>

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

                    echo '<li class="simak-master-item" data-id="' . $id . '">';
                    echo '<div class="simak-master-row">';
                    echo '<span class="drag-handle" title="Drag untuk ubah urutan/hirarki"><i class="fas fa-grip-lines"></i></span>';
                    echo '<div class="simak-master-meta">';
                    echo '<div class="simak-master-title">' . esc($title !== '' ? $title : '-') . '</div>';
                    echo '<div class="simak-master-sub">';
                    echo 'Jenis: <strong>' . esc($rowKind) . '</strong> | Pertanyaan: <strong>' . ($hasQuestion ? 'Ya' : 'Tidak') . '</strong> | Row No: <strong>' . esc((string) ($node['row_no'] ?? '')) . '</strong>';
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="simak-master-actions">';
                    echo '<button type="button" class="btn btn-warning btn-sm btn-edit-item-konsultasi"';
                    echo ' data-id="' . $id . '"';
                    echo ' data-parent_id="' . esc((string) ($node['parent_id'] ?? ''), 'attr') . '"';
                    echo ' data-display_no="' . esc((string) ($node['display_no'] ?? ''), 'attr') . '"';
                    echo ' data-uraian="' . esc((string) ($node['uraian'] ?? ''), 'attr') . '"';
                    echo ' data-bentuk_dokumen="' . esc((string) ($node['bentuk_dokumen'] ?? ''), 'attr') . '"';
                    echo ' data-referensi="' . esc((string) ($node['referensi'] ?? ''), 'attr') . '"';
                    echo ' data-kriteria_administrasi="' . esc((string) ($node['kriteria_administrasi'] ?? ''), 'attr') . '"';
                    echo ' data-kriteria_substansi="' . esc((string) ($node['kriteria_substansi'] ?? ''), 'attr') . '"';
                    echo ' data-sumber_dokumen_hasil_integrasi="' . esc((string) ($node['sumber_dokumen_hasil_integrasi'] ?? ''), 'attr') . '"';
                    echo ' data-row_kind="' . esc($rowKind, 'attr') . '"';
                    echo ' data-has_question="' . ($hasQuestion ? '1' : '0') . '"';
                    echo '><i class="fas fa-pen"></i></button>';

                    echo '<form method="post" action="' . site_url('/admin/master/simak/konsultasi/' . $id . '/hapus') . '" class="d-inline" onsubmit="return confirm(\'Hapus item ini?\');">';
                    echo csrf_field();
                    echo '<button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>';
                    echo '</form>';
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

        <?php if (! empty($itemsTree ?? [])): ?>
            <ul class="simak-master-tree" id="simak-master-konsultasi-root">
                <?php $renderTree($itemsTree); ?>
            </ul>
        <?php else: ?>
            <div class="empty-tree">
                Master SIMAK konsultasi belum memiliki item. Tambahkan item pertama untuk mulai membangun hirarki.
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="modal-master-simak-konsultasi" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form method="post" id="form-master-simak-konsultasi" action="<?= site_url('/admin/master/simak/konsultasi/tambah'); ?>" class="modal-content">
            <?= csrf_field(); ?>
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title-master-simak-konsultasi">Tambah Item Master</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="parent_id_konsultasi">Parent</label>
                    <select class="form-control" name="parent_id" id="parent_id_konsultasi">
                        <option value="">(Root)</option>
                        <?php foreach (($parentOptions ?? []) as $opt): ?>
                            <option value="<?= (int) ($opt['id'] ?? 0); ?>"><?= esc((string) ($opt['label'] ?? '')); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="display_no_konsultasi">Nomor Tampil</label>
                        <input type="text" class="form-control" name="display_no" id="display_no_konsultasi" placeholder="Contoh: A, 1, a">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="row_kind_konsultasi">Jenis Baris</label>
                        <select class="form-control" name="row_kind" id="row_kind_konsultasi" required>
                            <option value="section">Section</option>
                            <option value="group">Group/Sub Section</option>
                            <option value="question">Pertanyaan</option>
                            <option value="text">Teks Tanpa Pertanyaan</option>
                            <option value="separator">Separator</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4 d-flex align-items-end">
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="has_question_konsultasi" name="has_question" value="1">
                            <label class="custom-control-label" for="has_question_konsultasi">Item ini memiliki pertanyaan</label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="uraian_konsultasi">Uraian</label>
                    <textarea class="form-control" name="uraian" id="uraian_konsultasi" rows="2" required></textarea>
                </div>
                <div class="form-group">
                    <label for="bentuk_dokumen_konsultasi">Bentuk Dokumen</label>
                    <textarea class="form-control textarea-mini" name="bentuk_dokumen" id="bentuk_dokumen_konsultasi"></textarea>
                </div>
                <div class="form-group">
                    <label for="referensi_konsultasi">Referensi</label>
                    <textarea class="form-control textarea-mini" name="referensi" id="referensi_konsultasi"></textarea>
                </div>
                <div class="form-group">
                    <label for="kriteria_administrasi_konsultasi">Kriteria Administrasi</label>
                    <textarea class="form-control textarea-mini" name="kriteria_administrasi" id="kriteria_administrasi_konsultasi"></textarea>
                </div>
                <div class="form-group">
                    <label for="kriteria_substansi_konsultasi">Kriteria Substansi</label>
                    <textarea class="form-control textarea-mini" name="kriteria_substansi" id="kriteria_substansi_konsultasi"></textarea>
                </div>
                <div class="form-group mb-0">
                    <label for="sumber_dokumen_hasil_integrasi_konsultasi">Sumber Dokumen Hasil Integrasi</label>
                    <textarea class="form-control textarea-mini" name="sumber_dokumen_hasil_integrasi" id="sumber_dokumen_hasil_integrasi_konsultasi"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        var root = document.getElementById('simak-master-konsultasi-root');
        var saveButton = document.getElementById('btn-save-hierarchy-konsultasi');
        var form = document.getElementById('form-master-simak-konsultasi');
        var modalTitle = document.getElementById('modal-title-master-simak-konsultasi');
        var parentSelect = document.getElementById('parent_id_konsultasi');
        var displayNoInput = document.getElementById('display_no_konsultasi');
        var uraianInput = document.getElementById('uraian_konsultasi');
        var rowKindSelect = document.getElementById('row_kind_konsultasi');
        var hasQuestionInput = document.getElementById('has_question_konsultasi');
        var bentukDokumenInput = document.getElementById('bentuk_dokumen_konsultasi');
        var referensiInput = document.getElementById('referensi_konsultasi');
        var kriteriaAdministrasiInput = document.getElementById('kriteria_administrasi_konsultasi');
        var kriteriaSubstansiInput = document.getElementById('kriteria_substansi_konsultasi');
        var sumberDokumenInput = document.getElementById('sumber_dokumen_hasil_integrasi_konsultasi');
        var csrfName = <?= json_encode(csrf_token(), JSON_UNESCAPED_UNICODE); ?>;
        var csrfValue = <?= json_encode(csrf_hash(), JSON_UNESCAPED_UNICODE); ?>;

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

        var resetFormForCreate = function () {
            form.setAttribute('action', <?= json_encode(site_url('/admin/master/simak/konsultasi/tambah'), JSON_UNESCAPED_UNICODE); ?>);
            modalTitle.textContent = 'Tambah Item Master';
            parentSelect.value = '';
            displayNoInput.value = '';
            uraianInput.value = '';
            rowKindSelect.value = 'section';
            hasQuestionInput.checked = false;
            hasQuestionInput.disabled = false;
            bentukDokumenInput.value = '';
            referensiInput.value = '';
            kriteriaAdministrasiInput.value = '';
            kriteriaSubstansiInput.value = '';
            sumberDokumenInput.value = '';
        };

        var addButton = document.getElementById('btn-add-item-konsultasi');
        if (addButton) {
            addButton.addEventListener('click', resetFormForCreate);
        }

        document.querySelectorAll('.btn-edit-item-konsultasi').forEach(function (button) {
            button.addEventListener('click', function () {
                var id = button.getAttribute('data-id') || '';
                form.setAttribute('action', <?= json_encode(site_url('/admin/master/simak/konsultasi'), JSON_UNESCAPED_UNICODE); ?> + '/' + encodeURIComponent(id) + '/ubah');
                modalTitle.textContent = 'Ubah Item Master';

                parentSelect.value = button.getAttribute('data-parent_id') || '';
                displayNoInput.value = button.getAttribute('data-display_no') || '';
                uraianInput.value = button.getAttribute('data-uraian') || '';
                rowKindSelect.value = button.getAttribute('data-row_kind') || 'question';
                hasQuestionInput.checked = (button.getAttribute('data-has_question') || '0') === '1';
                hasQuestionInput.disabled = rowKindSelect.value === 'question';
                bentukDokumenInput.value = button.getAttribute('data-bentuk_dokumen') || '';
                referensiInput.value = button.getAttribute('data-referensi') || '';
                kriteriaAdministrasiInput.value = button.getAttribute('data-kriteria_administrasi') || '';
                kriteriaSubstansiInput.value = button.getAttribute('data-kriteria_substansi') || '';
                sumberDokumenInput.value = button.getAttribute('data-sumber_dokumen_hasil_integrasi') || '';

                if (window.jQuery) {
                    window.jQuery('#modal-master-simak-konsultasi').modal('show');
                }
            });
        });

        var initSortable = function (listEl) {
            if (!listEl || typeof Sortable === 'undefined') return;
            Sortable.create(listEl, {
                group: 'simak-master-konsultasi',
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

                fetch(<?= json_encode(site_url('/admin/master/simak/konsultasi/simpan-hirarki'), JSON_UNESCAPED_UNICODE); ?>, {
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
    })();
</script>
<?= $this->endSection(); ?>