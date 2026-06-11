<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<script src="<?= esc(media_url('assets/adminlte/plugins/sortablejs/Sortable.min.js')); ?>"></script>
<style>
    .simak-split-layout {
        display: grid;
        grid-template-columns: 1.4fr 1fr;
        gap: 16px;
    }

    @media (max-width: 991.98px) {
        .simak-split-layout {
            grid-template-columns: 1fr;
        }
    }

    .simak-tree-panel,
    .simak-detail-panel {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        overflow: hidden;
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
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        background: #f8fafc;
    }

    .simak-panel-head strong {
        font-size: 15px;
        color: #1e293b;
        font-weight: 700;
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
        padding: 3px 8px;
        border-radius: 6px;
    }

    .simak-panel-body {
        padding: 20px;
    }

    /* Tree Grid Table Styling */
    .simak-table-grid-wrapper {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        position: relative;
        max-height: calc(100vh - 240px);
        overflow: auto;
    }

    .simak-table-header {
        display: flex;
        align-items: center;
        padding: 10px 14px;
        background: #f1f5f9;
        border-bottom: 2px solid #cbd5e1;
        font-weight: 700;
        font-size: 11.5px;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        position: sticky;
        top: 0;
        z-index: 10;
        min-width: 780px;
    }

    .simak-col-handle { width: 24px; flex-shrink: 0; display: flex; justify-content: center; }
    .simak-col-toggle { width: 24px; flex-shrink: 0; display: flex; justify-content: center; }
    .simak-col-no { width: 60px; flex-shrink: 0; display: flex; justify-content: center; }
    .simak-col-uraian { flex: 1; min-width: 150px; }
    .simak-col-jenis { width: 85px; flex-shrink: 0; text-align: center; }
    .simak-col-question { width: 50px; flex-shrink: 0; text-align: center; }
    .simak-col-draft { width: 50px; flex-shrink: 0; text-align: center; }
    .simak-col-status { width: 75px; flex-shrink: 0; text-align: center; }
    .simak-col-share { width: 75px; flex-shrink: 0; text-align: center; }
    .simak-col-aksi { width: 140px; flex-shrink: 0; display: flex; justify-content: flex-end; gap: 3px; align-items: center; }

    /* Action button styles */
    .simak-col-aksi .btn-xs {
        padding: 4px 7px;
        font-size: 11px;
        min-width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .simak-col-aksi .btn-inline-delete {
        color: #dc2626 !important;
        background: transparent;
        border: 1px solid #fca5a5;
    }

    .simak-col-aksi .btn-inline-delete:hover {
        color: #fff !important;
        background: #dc2626;
        border-color: #dc2626;
    }

    .simak-master-tree {
        list-style: none;
        margin: 0;
        padding-left: 0;
        min-width: 780px;
    }

    .simak-master-tree ul {
        list-style: none;
        margin: 0;
        padding-left: 0;
        margin-top: 0;
    }

    .simak-master-item {
        background: #fff;
        border-bottom: 1px solid #e2e8f0;
        transition: background 0.15s ease, border-color 0.15s ease;
    }

    .simak-master-item:last-child {
        border-bottom: none;
    }

    .simak-master-item:hover {
        background: #f8fafc;
    }

    .simak-master-row {
        display: flex;
        align-items: center;
        padding: 8px 14px;
        min-height: 48px;
    }

    /* Node Kind Coding */
    .simak-master-item.row-kind-section {
        background: #f8fafc;
    }
    .simak-master-item.row-kind-section > .simak-master-row {
        background: rgba(15, 23, 42, 0.03);
    }
    .simak-master-item.row-kind-section .simak-master-title {
        font-weight: 700;
        color: #0f172a;
    }

    .simak-master-item.row-kind-group {
        background: #fff;
    }
    .simak-master-item.row-kind-group .simak-master-title {
        font-weight: 600;
        color: #1e293b;
    }

    .simak-master-item.row-kind-question .simak-master-title {
        font-weight: 400;
        color: #334155;
    }

    .simak-master-item.is-inactive {
        opacity: 0.6;
        background: #f1f5f9;
    }

    .simak-master-item.is-selected {
        background: rgba(10, 102, 194, 0.04) !important;
        outline: 2px solid var(--app-primary);
        outline-offset: -2px;
    }

    .drag-handle {
        cursor: grab;
        color: #94a3b8;
        padding: 4px;
        display: inline-flex;
    }

    .drag-handle:hover {
        color: var(--app-primary);
    }

    .node-toggle-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        cursor: pointer;
        color: #64748b;
        transition: all 0.2s ease;
    }

    .node-toggle-btn:hover {
        color: var(--app-primary);
        background: rgba(15, 23, 42, 0.05);
        border-radius: 4px;
    }

    .node-toggle-btn.is-empty {
        cursor: default;
        opacity: 0.25;
    }

    .node-toggle-btn i {
        transition: transform 0.2s ease;
    }

    .simak-master-item.is-collapsed > .simak-master-row .node-toggle-btn i {
        transform: rotate(-90deg);
    }

    .simak-master-item.is-collapsed > ul.simak-master-tree-list {
        display: none !important;
    }

    .simak-master-meta {
        cursor: pointer;
        min-width: 0;
    }

    .simak-master-title {
        font-size: 13px;
        line-height: 1.4;
    }

    .simak-inline-actions {
        display: flex;
        gap: 4px;
        align-items: center;
    }

    .simak-inline-actions .btn {
        width: 24px;
        height: 24px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        background: #fff;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    .simak-status-badge {
        font-size: 10px;
        font-weight: 600;
        padding: 3px 6px;
        border-radius: 4px;
    }

    .empty-tree {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 32px;
        text-align: center;
        color: #64748b;
        background: #f8fafc;
    }

    .simak-form-hint {
        font-size: 12px;
        color: #64748b;
        margin-bottom: 16px;
        padding: 10px 12px;
        background: #f0fdf4;
        border-left: 3px solid #10b981;
        border-radius: 6px;
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
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: 12px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        background: #fff;
        animation: simak-toast-in 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .simak-toast.is-hiding {
        animation: simak-toast-out 0.2s ease-in forwards;
    }

    .simak-toast-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 14px;
        font-weight: 700;
        font-size: 13.5px;
    }

    .simak-toast-body {
        padding: 12px 14px 14px;
        font-size: 13px;
        line-height: 1.5;
        color: #334155;
    }

    .simak-toast-success .simak-toast-header {
        background: #f0fdf4;
        color: #166534;
        border-bottom: 1px solid #bbf7d0;
    }

    .simak-toast-danger .simak-toast-header {
        background: #fef2f2;
        color: #991b1b;
        border-bottom: 1px solid #fecaca;
    }

    .simak-toast-warning .simak-toast-header {
        background: #fffbeb;
        color: #92400e;
        border-bottom: 1px solid #fef3c7;
    }

    .simak-toast-info .simak-toast-header {
        background: #eff6ff;
        color: #1d4ed8;
        border-bottom: 1px solid #bfdbfe;
    }

    /* Search highlight styling */
    .search-hidden {
        display: none !important;
    }
    
    .search-match {
        background: #fef08a !important;
    }

    /* Dragging ghost styling */
    .simak-master-tree-list .sortable-ghost {
        opacity: 0.4;
        background-color: rgba(10, 102, 194, 0.05);
        border: 2px dashed var(--app-primary) !important;
    }

    @keyframes simak-toast-in {
        from {
            opacity: 0;
            transform: translateY(-12px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @keyframes simak-toast-out {
        from {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
        to {
            opacity: 0;
            transform: translateY(-12px) scale(0.95);
        }
    }
</style>

<div class="card">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title mb-0"><?= esc((string) ($pageTitle ?? 'Master SIMAK Konstruksi')); ?></h3>
        <?php if (! empty($can_edit)): ?>
            <div class="btn-group ml-auto" role="group">
                <button type="button" class="btn btn-success btn-sm" id="btn-save-hierarchy">Simpan Susunan</button>
                <div class="btn-group" role="group">
                    <button id="exportDropdown" type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Export</button>
                    <div class="dropdown-menu" aria-labelledby="exportDropdown">
                        <a class="dropdown-item" href="<?= site_url('/admin/master/simak/konstruksi/export?format=csv'); ?>">CSV</a>
                        <a class="dropdown-item" href="<?= site_url('/admin/master/simak/konstruksi/export?format=xlsx'); ?>">XLSX</a>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm" id="btn-open-import">Import</button>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3"><?= esc((string) ($pageSubtitle ?? 'Daftar item, susunan, dan pengaturan verifikasi SIMAK konstruksi.')); ?></p>
        <div id="simak-notice-stack" class="simak-notice-stack" aria-live="polite" aria-atomic="true"></div>

        <?php
            $renderTree = static function (array $nodes, int $depth = 0) use (&$renderTree, $shareVisibilityAvailable): void {
                echo '<ul class="simak-master-tree-list">';
                foreach ($nodes as $node) {
                    $id = (int) ($node['id'] ?? 0);
                    $displayNo = trim((string) ($node['display_no_auto'] ?? $node['display_no'] ?? ''));
                    $uraian = trim((string) ($node['uraian'] ?? ''));
                    $rowKind = (string) ($node['row_kind'] ?? 'question');
                    $hasQuestion = (int) ($node['has_question'] ?? 0) === 1;
                    $hasDraft = (int) ($node['has_draft'] ?? 0) === 1;
                    $isActive = (int) ($node['is_active'] ?? 1) === 1;
                    $isHiddenShare = (int) ($node['is_hidden_share'] ?? 0) === 1;
                    $children = is_array($node['children'] ?? null) ? $node['children'] : [];

                    echo '<li class="simak-master-item row-kind-' . esc($rowKind) . (! $isActive ? ' is-inactive' : '') . ($isHiddenShare ? ' is-share-hidden' : '') . '" data-id="' . $id . '"';
                    echo ' data-parent_id="' . esc((string) ($node['parent_id'] ?? ''), 'attr') . '"';
                    echo ' data-uraian="' . esc((string) ($node['uraian'] ?? ''), 'attr') . '"';
                    echo ' data-row_kind="' . esc($rowKind, 'attr') . '"';
                    echo ' data-has_question="' . ($hasQuestion ? '1' : '0') . '"';
                    echo ' data-has_draft="' . ($hasDraft ? '1' : '0') . '"';
                    echo ' data-is_active="' . ($isActive ? '1' : '0') . '"';
                    echo ' data-is_hidden_share="' . ($isHiddenShare ? '1' : '0') . '"';
                    echo '>';
                    echo '<div class="simak-master-row">';
                    
                    // Column 1: Drag handle
                    echo '<div class="simak-col-handle">';
                    echo '<span class="drag-handle" title="Seret untuk mengubah urutan/susunan"><i class="fas fa-grip-lines"></i></span>';
                    echo '</div>';

                    // Column 2: Spacer for indentation depth
                    $indentWidth = $depth * 20;
                    echo '<div class="simak-col-indent" style="width: ' . $indentWidth . 'px; flex-shrink: 0;"></div>';
                    
                    // Column 3: Chevron Toggle (for sections and groups)
                    echo '<div class="simak-col-toggle">';
                    $hasChildren = ($children !== []);
                    if ($rowKind === 'section' || $rowKind === 'group') {
                        echo '<span class="node-toggle-btn' . ($hasChildren ? '' : ' is-empty') . '" title="Klik untuk melipat"><i class="fas ' . ($hasChildren ? 'fa-chevron-down' : 'fa-minus') . '"></i></span>';
                    } else {
                        echo '<span class="node-toggle-btn is-empty"><i class="fas fa-circle" style="font-size: 5px; opacity: 0.35;"></i></span>';
                    }
                    echo '</div>';

                    // Column 4: Display No
                    echo '<div class="simak-col-no">';
                    echo '<span class="badge badge-dark">' . esc($displayNo !== '' ? $displayNo : '-') . '</span>';
                    echo '</div>';
                    
                    // Column 5: Uraian Title
                    echo '<div class="simak-col-uraian simak-master-meta">';
                    echo '<div class="simak-master-title">' . esc($uraian !== '' ? $uraian : '-') . '</div>';
                    echo '</div>';

                    // Column 6: Jenis Row Kind
                    echo '<div class="simak-col-jenis">';
                    echo '<span class="badge badge-light text-capitalize">' . esc($rowKind) . '</span>';
                    echo '</div>';

                    // Column 7: Pertanyaan status
                    echo '<div class="simak-col-question">';
                    echo $hasQuestion ? '<i class="fas fa-check text-success" title="Punya Pertanyaan"></i>' : '<i class="fas fa-times text-muted" title="Tidak Ada Pertanyaan"></i>';
                    echo '</div>';

                    // Column 8: Draft status
                    echo '<div class="simak-col-draft">';
                    echo $hasDraft ? '<i class="fas fa-check text-success" title="Punya Draft"></i>' : '<i class="fas fa-times text-muted" title="Tidak Ada Draft"></i>';
                    echo '</div>';

                    // Column 9: Status (Active/Inactive)
                    echo '<div class="simak-col-status">';
                    echo '<span class="badge ' . ($isActive ? 'badge-success' : 'badge-secondary') . ' simak-status-badge">' . ($isActive ? 'Aktif' : 'Nonaktif') . '</span>';
                    echo '</div>';

                    // Column 10: Share Status
                    echo '<div class="simak-col-share">';
                    echo $isHiddenShare ? '<span class="badge badge-warning simak-status-badge">Sembunyi</span>' : '<span class="badge badge-info simak-status-badge">Tampil</span>';
                    echo '</div>';
                    
                    // Column 11: Action Buttons
                    echo '<div class="simak-col-aksi">';
                    echo '<button type="button" class="btn btn-xs btn-outline-primary btn-inline-edit" title="Ubah Item"><i class="fas fa-edit"></i></button>';
                    if ($rowKind !== 'question' && $rowKind !== 'text') {
                        echo '<button type="button" class="btn btn-xs btn-outline-success btn-inline-add-child" title="Tambah Subitem"><i class="fas fa-plus"></i></button>';
                    }
                    echo '<button type="button" class="btn btn-xs btn-outline-secondary btn-inline-toggle-status" title="' . ($isActive ? 'Nonaktifkan' : 'Aktifkan') . '"><i class="fas ' . ($isActive ? 'fa-toggle-on text-success' : 'fa-toggle-off') . '"></i></button>';
                    if (! empty($shareVisibilityAvailable)) {
                        echo '<button type="button" class="btn btn-xs btn-outline-info btn-inline-toggle-share" title="' . ($isHiddenShare ? 'Tampilkan di Share' : 'Sembunyikan dari Share') . '"><i class="fas ' . ($isHiddenShare ? 'fa-eye-slash text-warning' : 'fa-eye') . '"></i></button>';
                    }
                    if (! empty($can_delete)) {
                        echo '<button type="button" class="btn btn-xs btn-outline-danger btn-inline-delete" title="Hapus Item"><i class="fas fa-trash"></i></button>';
                    }
                    echo '</div>';
                    
                    echo '</div>'; // end row

                    if ($children !== []) {
                        $renderTree($children, $depth + 1);
                    }

                    echo '</li>';
                }
                echo '</ul>';
            };
        ?>

        <div class="simak-tree-panel">
            <div class="simak-panel-head">
                <div>
                    <strong>Susunan Pertanyaan</strong>
                    <div class="simak-panel-meta">
                        <span class="badge badge-success" id="simak-count-active">Aktif: 0</span>
                        <span class="badge badge-secondary" id="simak-count-inactive">Nonaktif: 0</span>
                    </div>
                </div>
                <div>
                    <?php if (! empty($can_add)): ?>
                        <button type="button" class="btn btn-primary btn-sm" id="btn-add-root">Tambah Item Utama</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-child" disabled>Tambah Subitem</button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="simak-search-filter-bar px-3 py-2 border-bottom d-flex align-items-center justify-content-between bg-light" style="gap:10px;">
                <div class="input-group input-group-sm" style="max-width: 250px;">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                    </div>
                    <input type="text" id="simak-search-input" class="form-control border-left-0" placeholder="Cari pertanyaan...">
                </div>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary" id="btn-expand-all" title="Buka Semua"><i class="fas fa-angle-double-down"></i> Buka</button>
                    <button type="button" class="btn btn-outline-secondary" id="btn-collapse-all" title="Tutup Semua"><i class="fas fa-angle-double-up"></i> Tutup</button>
                </div>
            </div>
            <div class="simak-panel-body">
                <?php if (! empty($itemsTree ?? [])): ?>
                    <div class="simak-table-grid-wrapper">
                        <!-- Table Header -->
                        <div class="simak-table-header d-none d-md-flex">
                            <div class="simak-col-handle"></div>
                            <div class="simak-col-toggle"></div>
                            <div class="simak-col-no">No</div>
                            <div class="simak-col-uraian">Uraian</div>
                            <div class="simak-col-jenis text-center">Jenis</div>
                            <div class="simak-col-question text-center">Tanya</div>
                            <div class="simak-col-draft text-center">Draft</div>
                            <div class="simak-col-status text-center">Status</div>
                            <div class="simak-col-share text-center">Share</div>
                            <div class="simak-col-aksi text-right">Aksi</div>
                        </div>
                        <ul class="simak-master-tree" id="simak-master-root">
                            <?php $renderTree($itemsTree); ?>
                        </ul>
                    </div>
                <?php else: ?>
                    <div class="empty-tree">
                        Master SIMAK konstruksi belum memiliki item. Gunakan tombol "Tambah Item Utama" di atas.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Master Form Modal -->
<div class="modal fade" id="modal-master-form" tabindex="-1" role="dialog" aria-labelledby="modalMasterFormLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="form-mode-label">Tambah Item Master</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" id="form-master-simak" action="<?= site_url('/admin/master/simak/konstruksi/tambah'); ?>">
                <?= csrf_field(); ?>
                <input type="hidden" id="selected_id" value="">
                <div class="modal-body">
                    <div class="simak-form-hint">Nomor tampil dibuat otomatis, sedangkan urutan diubah lewat tarik dan lepas di tabel grid utama.</div>

                    <div class="form-group">
                        <label for="parent_id">Item Induk</label>
                        <select class="form-control" name="parent_id" id="parent_id" <?= empty($can_edit) && empty($can_add) ? 'disabled' : ''; ?>>
                            <option value="">(Tanpa Induk)</option>
                            <?php foreach (($parentOptions ?? []) as $opt): ?>
                                <option value="<?= (int) ($opt['id'] ?? 0); ?>"><?= esc((string) ($opt['label'] ?? '')); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="uraian">Uraian</label>
                        <textarea class="form-control" name="uraian" id="uraian" rows="3" required <?= empty($can_edit) && empty($can_add) ? 'disabled' : ''; ?>></textarea>
                    </div>
                    <div class="form-group">
                        <label for="row_kind">Jenis Item</label>
                        <select class="form-control" name="row_kind" id="row_kind" required <?= empty($can_edit) && empty($can_add) ? 'disabled' : ''; ?>>
                            <option value="section">Bagian</option>
                            <option value="group">Subbagian</option>
                            <option value="question">Pertanyaan</option>
                            <option value="text">Teks</option>
                            <option value="separator">Pemisah</option>
                        </select>
                    </div>
                    <div class="custom-control custom-checkbox mb-3">
                        <input type="checkbox" class="custom-control-input" id="has_question" name="has_question" value="1" <?= empty($can_edit) && empty($can_add) ? 'disabled' : ''; ?>>
                        <label class="custom-control-label" for="has_question">Item ini punya pertanyaan</label>
                    </div>

                    <div class="custom-control custom-checkbox mb-3">
                        <input type="checkbox" class="custom-control-input" id="has_draft" name="has_draft" value="1" <?= empty($can_edit) && empty($can_add) ? 'disabled' : ''; ?>>
                        <label class="custom-control-label" for="has_draft">Item ini punya draft</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <?php if (! empty($can_add) || ! empty($can_edit)): ?>
                        <button type="submit" class="btn btn-primary" id="btn-submit-form">Simpan</button>
                    <?php endif; ?>
                    <?php if (! empty($can_edit)): ?>
                        <button type="button" class="btn btn-outline-warning btn-sm" id="btn-toggle-status" disabled>Aktifkan/Nonaktifkan</button>
                        <?php if (! empty($shareVisibilityAvailable)): ?>
                            <button type="button" class="btn btn-outline-info btn-sm" id="btn-toggle-share-visibility" disabled>Sembunyikan Share</button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="modal-delete-confirm" tabindex="-1" role="dialog" aria-labelledby="modalDeleteConfirmLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalDeleteConfirmLabel"><i class="fas fa-exclamation-triangle"></i> Konfirmasi Hapus</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus item ini?</p>
                <p class="text-danger"><strong id="delete-item-name"></strong></p>
                <p class="text-muted small mb-0" id="delete-item-info"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="btn-confirm-delete"><i class="fas fa-trash"></i> Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="modal-import" tabindex="-1" role="dialog" aria-labelledby="modalImportLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalImportLabel">Import Master SIMAK Konstruksi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="<?= site_url('/admin/master/simak/konstruksi/import'); ?>" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="import_file">Pilih file CSV atau XLSX</label>
                        <input type="file" class="form-control-file" name="import_file" id="import_file" accept=".csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel" required>
                    </div>
                    <p class="text-muted small">File harus memiliki header: id,parent_id,display_no,uraian,row_kind,has_question,ordering,is_active,is_hidden_share,external_id</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Preview</button>
                </div>
            </form>
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
        var uraianInput = document.getElementById('uraian');
        var rowKindSelect = document.getElementById('row_kind');
        var hasQuestionInput = document.getElementById('has_question');
        var hasDraftInput = document.getElementById('has_draft');
        var addRootButton = document.getElementById('btn-add-root');
        var addChildButton = document.getElementById('btn-add-child');
        var resetButton = document.getElementById('btn-reset-selection');
        var toggleStatusButton = null;
        var toggleShareVisibilityButton = null;
        var countActiveBadge = document.getElementById('simak-count-active');
        var countInactiveBadge = document.getElementById('simak-count-inactive');
        var treePanelBody = document.querySelector('.simak-tree-panel .simak-panel-body');
        var noticeStack = document.getElementById('simak-notice-stack');
        var csrfName = <?= json_encode(csrf_token(), JSON_UNESCAPED_UNICODE); ?>;
        var csrfValue = <?= json_encode(csrf_hash(), JSON_UNESCAPED_UNICODE); ?>;
        var addUrl = <?= json_encode(site_url('/admin/master/simak/konstruksi/tambah'), JSON_UNESCAPED_UNICODE); ?>;
        var baseUrl = <?= json_encode(site_url('/admin/master/simak/konstruksi'), JSON_UNESCAPED_UNICODE); ?>;

        var showFormModal = function () {
            var modalEl = document.getElementById('modal-master-form');
            if (!modalEl) return;
            if (window.jQuery && typeof window.jQuery === 'function') {
                window.jQuery(modalEl).modal('show');
                return;
            }
            if (window.bootstrap && window.bootstrap.Modal) {
                var m = new window.bootstrap.Modal(modalEl);
                m.show();
                return;
            }
            modalEl.style.display = 'block';
        };

        var hideFormModal = function () {
            var modalEl = document.getElementById('modal-master-form');
            if (!modalEl) return;
            if (window.jQuery && typeof window.jQuery === 'function') {
                window.jQuery(modalEl).modal('hide');
                return;
            }
            var closeBtn = modalEl.querySelector('[data-dismiss="modal"]');
            if (closeBtn) {
                closeBtn.click();
            } else {
                modalEl.style.display = 'none';
            }
        };

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

        var updateShareVisibilityButton = function (isHidden) {
            if (!toggleShareVisibilityButton) return;

            toggleShareVisibilityButton.disabled = false;
            if (isHidden) {
                toggleShareVisibilityButton.textContent = 'Tampilkan di Share';
                toggleShareVisibilityButton.className = 'btn btn-outline-info btn-sm';
            } else {
                toggleShareVisibilityButton.textContent = 'Sembunyikan dari Share';
                toggleShareVisibilityButton.className = 'btn btn-outline-warning btn-sm';
            }
        };

        var setCreateMode = function (parentId, label) {
            if (!form) return;
            form.setAttribute('action', addUrl);
            if (formModeLabel) formModeLabel.textContent = label || 'Tambah Item Baru';
            if (selectedIdInput) selectedIdInput.value = '';
            if (parentSelect) parentSelect.value = parentId || '';
            if (uraianInput) uraianInput.value = '';
            if (rowKindSelect) rowKindSelect.value = 'section';
            if (hasQuestionInput) {
                hasQuestionInput.checked = false;
                hasQuestionInput.disabled = false;
            }
            if (hasDraftInput) {
                hasDraftInput.checked = false;
            }
            if (toggleStatusButton) {
                toggleStatusButton.disabled = true;
                toggleStatusButton.textContent = 'Aktifkan/Nonaktifkan';
                toggleStatusButton.className = 'btn btn-outline-secondary btn-sm';
            }
            if (toggleShareVisibilityButton) {
                toggleShareVisibilityButton.disabled = true;
                toggleShareVisibilityButton.textContent = 'Sembunyikan Share';
                toggleShareVisibilityButton.className = 'btn btn-outline-secondary btn-sm';
            }
            if (addChildButton) {
                addChildButton.disabled = true;
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
            if (uraianInput) uraianInput.value = itemEl.getAttribute('data-uraian') || '';
            if (rowKindSelect) rowKindSelect.value = itemEl.getAttribute('data-row_kind') || 'question';
            if (hasQuestionInput) {
                hasQuestionInput.checked = (itemEl.getAttribute('data-has_question') || '0') === '1';
                hasQuestionInput.disabled = rowKindSelect && rowKindSelect.value === 'question';
            }
            if (hasDraftInput) {
                hasDraftInput.checked = (itemEl.getAttribute('data-has_draft') || '0') === '1';
            }
            if (toggleStatusButton) {
                updateToggleButton((itemEl.getAttribute('data-is_active') || '1') === '1');
            }
            if (toggleShareVisibilityButton) {
                updateShareVisibilityButton((itemEl.getAttribute('data-is_hidden_share') || '0') === '1');
            }
            if (addChildButton) {
                addChildButton.disabled = false;
            }
        };

        if (addRootButton) {
            addRootButton.addEventListener('click', function () {
                clearSelection();
                setCreateMode('', 'Tambah Item Utama');
                showFormModal();
            });
        }

        if (addChildButton) {
            addChildButton.addEventListener('click', function () {
                var selectedId = selectedIdInput ? selectedIdInput.value : '';
                if (!selectedId) {
                    showNotice('Pilih item induk terlebih dahulu.', 'warning');
                    return;
                }
                setCreateMode(selectedId, 'Tambah Subitem dari Item #' + selectedId);
                showFormModal();
            });
        }

        if (resetButton) {
            resetButton.addEventListener('click', function () {
                clearSelection();
                setCreateMode('', 'Tambah Item Baru');
            });
        }

        toggleStatusButton = document.getElementById('btn-toggle-status');
        toggleShareVisibilityButton = document.getElementById('btn-toggle-share-visibility');

        var bindTreeItemClicks = function () {
            if (!root) return;
            root.querySelectorAll('.simak-master-item .simak-master-meta').forEach(function (metaEl) {
                metaEl.addEventListener('click', function () {
                    var itemEl = metaEl.closest('.simak-master-item');
                    setEditModeFromItem(itemEl);
                    showFormModal();
                });
            });

            // Bind edit button clicks
            root.querySelectorAll('.simak-master-item .btn-inline-edit').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    var itemEl = btn.closest('.simak-master-item');
                    setEditModeFromItem(itemEl);
                    showFormModal();
                });
            });

            // Bind add child button clicks
            root.querySelectorAll('.simak-master-item .btn-inline-add-child').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    var itemEl = btn.closest('.simak-master-item');
                    var itemId = itemEl.getAttribute('data-id') || '';
                    setCreateMode(itemId, 'Tambah Subitem dari Item #' + itemId);
                    showFormModal();
                });
            });

            // Bind toggle status button clicks
            root.querySelectorAll('.simak-master-item .btn-inline-toggle-status').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    var itemEl = btn.closest('.simak-master-item');
                    var itemId = itemEl.getAttribute('data-id') || '';
                    clearSelection();
                    itemEl.classList.add('is-selected');
                    if (selectedIdInput) selectedIdInput.value = itemId;
                    toggleStatusAjax();
                });
            });

            // Bind toggle share visibility button clicks
            root.querySelectorAll('.simak-master-item .btn-inline-toggle-share').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    var itemEl = btn.closest('.simak-master-item');
                    var itemId = itemEl.getAttribute('data-id') || '';
                    clearSelection();
                    itemEl.classList.add('is-selected');
                    if (selectedIdInput) selectedIdInput.value = itemId;
                    toggleShareVisibilityAjax();
                });
            });

            // Bind delete button clicks
            root.querySelectorAll('.simak-master-item .btn-inline-delete').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    var itemEl = btn.closest('.simak-master-item');
                    var itemId = itemEl.getAttribute('data-id') || '';
                    var itemUraian = itemEl.getAttribute('data-uraian') || '';
                    var itemRowKind = itemEl.getAttribute('data-row_kind') || '';
                    var hasChildren = itemEl.querySelector(':scope > ul.simak-master-tree-list');

                    // Show confirmation modal
                    var deleteModal = document.getElementById('modal-delete-confirm');
                    var deleteItemName = document.getElementById('delete-item-name');
                    var deleteItemInfo = document.getElementById('delete-item-info');

                    if (deleteItemName) deleteItemName.textContent = itemUraian;
                    if (deleteItemInfo) {
                        var infoText = 'Jenis: ' + itemRowKind;
                        if (hasChildren) {
                            var childCount = itemEl.querySelectorAll(':scope ul li.simak-master-item').length;
                            infoText += ' | Memiliki ' + childCount + ' subitem (akan ikut dihapus)';
                        }
                        deleteItemInfo.textContent = infoText;
                    }

                    // Store the item ID for deletion
                    deleteModal.setAttribute('data-delete-id', itemId);

                    // Show modal
                    if (window.jQuery && typeof window.jQuery === 'function') {
                        window.jQuery(deleteModal).modal('show');
                    } else if (window.bootstrap && window.bootstrap.Modal) {
                        var m = new window.bootstrap.Modal(deleteModal);
                        m.show();
                    } else {
                        deleteModal.style.display = 'block';
                    }
                });
            });
        };

        // Delete confirmation button handler
        var confirmDeleteBtn = document.getElementById('btn-confirm-delete');
        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', function () {
                var deleteModal = document.getElementById('modal-delete-confirm');
                var deleteId = deleteModal.getAttribute('data-delete-id');

                if (!deleteId) {
                    showNotice('ID item tidak ditemukan.', 'danger');
                    return;
                }

                confirmDeleteBtn.disabled = true;

                var formData = new FormData();
                formData.append(csrfName, csrfValue);

                fetch(baseUrl + '/' + encodeURIComponent(deleteId) + '/hapus', {
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

                        // Hide confirmation modal
                        if (window.jQuery && typeof window.jQuery === 'function') {
                            window.jQuery(deleteModal).modal('hide');
                        } else {
                            var closeBtn = deleteModal.querySelector('[data-dismiss="modal"]');
                            if (closeBtn) closeBtn.click();
                        }

                        if (!result.ok || json.status !== 'ok') {
                            showNotice(json.message || 'Gagal menghapus item.', 'danger');
                            return;
                        }

                        // Refresh panels and show success notice
                        var refreshPromise = refreshPanelsWithoutFlash('', 'Tambah Item Baru');
                        refreshPromise.then(function () {
                            showNotice(json.message || 'Item berhasil dihapus.', 'success');
                        });
                    })
                    .catch(function () {
                        showNotice('Gagal menghapus item.', 'danger');
                        // Hide modal on error too
                        if (window.jQuery && typeof window.jQuery === 'function') {
                            window.jQuery(deleteModal).modal('hide');
                        }
                    })
                    .finally(function () {
                        confirmDeleteBtn.disabled = false;
                    });
            });
        }

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

                    // Re-trigger search text query if search term was active
                    var searchInput = document.getElementById('simak-search-input');
                    if (searchInput && searchInput.value !== '') {
                        searchInput.dispatchEvent(new Event('input'));
                    }

                    if (selectedId) {
                        var selectedEl = document.querySelector('.simak-master-item[data-id="' + selectedId + '"]');
                        if (selectedEl) {
                            setEditModeFromItem(selectedEl);
                            return;
                        }
                    }

                    setCreateMode('', formLabel || 'Tambah Item Baru');
                });
        };

        // Refresh panels without triggering server-side flash messages (for post-save scenarios)
        var refreshPanelsWithoutFlash = function (selectedId, formLabel) {
            return fetch(window.location.href + (window.location.href.indexOf('?') > -1 ? '&' : '?') + '_silent=1', {
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

                    // Re-trigger search text query if search term was active
                    var searchInput = document.getElementById('simak-search-input');
                    if (searchInput && searchInput.value !== '') {
                        searchInput.dispatchEvent(new Event('input'));
                    }

                    if (selectedId) {
                        var selectedEl = document.querySelector('.simak-master-item[data-id="' + selectedId + '"]');
                        if (selectedEl) {
                            setEditModeFromItem(selectedEl);
                            return;
                        }
                    }

                    setCreateMode('', formLabel || 'Tambah Item Baru');
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

                    refreshPanels(selectedId, formModeLabel ? formModeLabel.textContent : 'Tambah Item Baru').then(function () {
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

        var toggleShareVisibilityAjax = function () {
            if (!toggleShareVisibilityButton || !selectedIdInput || !selectedIdInput.value) {
                return;
            }

            var selectedId = selectedIdInput.value;
            var currentItem = document.querySelector('.simak-master-item[data-id="' + selectedId + '"]');
            var isHidden = currentItem ? (currentItem.getAttribute('data-is_hidden_share') || '0') === '1' : false;
            var nextVisibility = isHidden ? 0 : 1;

            toggleShareVisibilityButton.disabled = true;

            var formData = new FormData();
            formData.append('is_hidden_share', String(nextVisibility));
            formData.append(csrfName, csrfValue);

            fetch(baseUrl + '/' + encodeURIComponent(selectedId) + '/share-visibility', {
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
                        showNotice(json.message || 'Gagal mengubah visibilitas share item.', 'danger');
                        return;
                    }

                    refreshPanels(selectedId, formModeLabel ? formModeLabel.textContent : 'Tambah Item Baru').then(function () {
                        showNotice(json.message || 'Visibilitas share item berhasil diubah.', 'success');
                    });
                })
                .catch(function () {
                    showNotice('Gagal mengubah visibilitas share item.', 'danger');
                })
                .finally(function () {
                    if (toggleShareVisibilityButton) {
                        toggleShareVisibilityButton.disabled = false;
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

                        // First hide modal immediately
                        hideFormModal();

                        // Then refresh panels (without triggering server-side flash)
                        var refreshPromise = refreshPanelsWithoutFlash(id, 'Tambah Item Baru');

                        // Show success notice after refresh completes
                        refreshPromise.then(function () {
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
                            showNotice((json && json.message) ? json.message : 'Gagal menyimpan susunan', 'danger');
                            return;
                        }
                        refreshPanels(selectedIdInput ? selectedIdInput.value : '', formModeLabel ? formModeLabel.textContent : 'Tambah Item Baru')
                            .then(function () {
                                showNotice(json.message || 'Susunan berhasil disimpan.', 'success');
                            });
                    })
                    .catch(function () {
                        showNotice('Gagal menyimpan susunan', 'danger');
                    });
            });
        }

        if (toggleStatusButton) {
            toggleStatusButton.addEventListener('click', function () {
                toggleStatusAjax();
            });
        }

        if (toggleShareVisibilityButton) {
            toggleShareVisibilityButton.addEventListener('click', function () {
                toggleShareVisibilityAjax();
            });
        }

        updateStatusSummary();
        setCreateMode('', 'Tambah Item Baru');

        var importBtn = document.getElementById('btn-open-import');
        if (importBtn) {
            importBtn.addEventListener('click', function () {
                var modalEl = document.getElementById('modal-import');
                if (window.jQuery && typeof window.jQuery === 'function') {
                    window.jQuery(modalEl).modal('show');
                    return;
                }
                if (window.bootstrap && window.bootstrap.Modal) {
                    var m = new window.bootstrap.Modal(modalEl);
                    m.show();
                    return;
                }
                // fallback: make modal visible
                modalEl.style.display = 'block';
            });
        }

        // --- Real-time Search & Filter ---
        var setupSearchInput = function() {
            var searchInput = document.getElementById('simak-search-input');
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    var query = searchInput.value.toLowerCase().trim();
                    var items = root ? root.querySelectorAll('.simak-master-item') : [];
                    
                    // Reset
                    items.forEach(function (item) {
                        item.classList.remove('search-hidden', 'search-match');
                        var titleEl = item.querySelector('.simak-master-title');
                        if (titleEl && titleEl.dataset.originalHtml) {
                            titleEl.innerHTML = titleEl.dataset.originalHtml;
                        }
                    });

                    if (query === '') {
                        return;
                    }

                    var matches = [];
                    items.forEach(function (item) {
                        var titleEl = item.querySelector('.simak-master-title');
                        if (!titleEl) return;
                        
                        if (!titleEl.dataset.originalHtml) {
                            titleEl.dataset.originalHtml = titleEl.innerHTML;
                        }

                        var text = titleEl.textContent.toLowerCase();
                        if (text.indexOf(query) !== -1) {
                            item.classList.add('search-match');
                            matches.push(item);

                            var rawHTML = titleEl.dataset.originalHtml;
                            var reg = new RegExp('(' + query.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&') + ')', 'gi');
                            titleEl.innerHTML = rawHTML.replace(reg, '<mark class="bg-warning p-0">$1</mark>');
                        }
                    });

                    // Hide non-matches
                    items.forEach(function (item) {
                        if (!item.classList.contains('search-match')) {
                            item.classList.add('search-hidden');
                        }
                    });

                    // Restore match ancestors and auto-expand them
                    matches.forEach(function (item) {
                        item.classList.remove('search-hidden');
                        var parent = item.parentElement.closest('.simak-master-item');
                        while (parent) {
                            parent.classList.remove('search-hidden');
                            parent.classList.remove('is-collapsed');
                            parent = parent.parentElement.closest('.simak-master-item');
                        }
                    });
                });
            }
        };

        // --- Expand/Collapse All ---
        var setupExpandCollapseAll = function() {
            var expandAllBtn = document.getElementById('btn-expand-all');
            if (expandAllBtn) {
                expandAllBtn.addEventListener('click', function () {
                    if (!root) return;
                    root.querySelectorAll('.simak-master-item').forEach(function (item) {
                        item.classList.remove('is-collapsed');
                    });
                });
            }

            var collapseAllBtn = document.getElementById('btn-collapse-all');
            if (collapseAllBtn) {
                collapseAllBtn.addEventListener('click', function () {
                    if (!root) return;
                    root.querySelectorAll('.simak-master-item').forEach(function (item) {
                        if (item.querySelector('ul.simak-master-tree-list')) {
                            item.classList.add('is-collapsed');
                        }
                    });
                });
            }
        };

        // --- Event Delegation on Tree Root (Toggle & Inline Actions) ---
        var setupTreeEventDelegation = function() {
            if (root) {
                root.addEventListener('click', function (e) {
                    // 1. Chevron toggle click
                    var toggleBtn = e.target.closest('.node-toggle-btn');
                    if (toggleBtn && !toggleBtn.classList.contains('is-empty')) {
                        e.preventDefault();
                        e.stopPropagation();
                        var itemEl = toggleBtn.closest('.simak-master-item');
                        if (itemEl) {
                            itemEl.classList.toggle('is-collapsed');
                        }
                        return;
                    }

                    // 2. Inline Edit action
                    var inlineEdit = e.target.closest('.btn-inline-edit');
                    if (inlineEdit) {
                        e.preventDefault();
                        e.stopPropagation();
                        var itemEl = inlineEdit.closest('.simak-master-item');
                        setEditModeFromItem(itemEl);
                        showFormModal();
                        return;
                    }

                    // 3. Inline Add Child action
                    var inlineAdd = e.target.closest('.btn-inline-add-child');
                    if (inlineAdd) {
                        e.preventDefault();
                        e.stopPropagation();
                        var itemEl = inlineAdd.closest('.simak-master-item');
                        var id = itemEl.getAttribute('data-id') || '';
                        setCreateMode(id, 'Tambah Subitem dari Item #' + id);
                        showFormModal();
                        if (uraianInput) {
                            uraianInput.focus();
                        }
                        return;
                    }

                    // 4. Inline Toggle Active status action
                    var inlineStatus = e.target.closest('.btn-inline-toggle-status');
                    if (inlineStatus) {
                        e.preventDefault();
                        e.stopPropagation();
                        var itemEl = inlineStatus.closest('.simak-master-item');
                        if (itemEl) {
                            var id = itemEl.getAttribute('data-id');
                            var isActive = (itemEl.getAttribute('data-is_active') || '1') === '1';
                            var nextStatus = isActive ? 0 : 1;
                            runToggleStatusInline(id, nextStatus, inlineStatus);
                        }
                        return;
                    }

                    // 5. Inline Toggle Share visibility action
                    var inlineShare = e.target.closest('.btn-inline-toggle-share');
                    if (inlineShare) {
                        e.preventDefault();
                        e.stopPropagation();
                        var itemEl = inlineShare.closest('.simak-master-item');
                        if (itemEl) {
                            var id = itemEl.getAttribute('data-id');
                            var isHidden = (itemEl.getAttribute('data-is_hidden_share') || '0') === '1';
                            var nextVisibility = isHidden ? 0 : 1;
                            runToggleShareInline(id, nextVisibility, inlineShare);
                        }
                        return;
                    }
                });
            }
        };

        var runToggleStatusInline = function (id, nextStatus, buttonEl) {
            if (!id) return;
            var originalHtml = buttonEl.innerHTML;
            buttonEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            buttonEl.disabled = true;

            var formData = new FormData();
            formData.append('is_active', String(nextStatus));
            formData.append(csrfName, csrfValue);

            fetch(baseUrl + '/' + encodeURIComponent(id) + '/status', {
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
                        buttonEl.innerHTML = originalHtml;
                        buttonEl.disabled = false;
                        return;
                    }

                    var currentSelectedId = selectedIdInput ? selectedIdInput.value : '';
                    refreshPanels(currentSelectedId, formModeLabel ? formModeLabel.textContent : 'Tambah Item Baru').then(function () {
                        showNotice(json.message || 'Status item berhasil diubah.', 'success');
                    });
                })
                .catch(function () {
                    showNotice('Gagal mengubah status item.', 'danger');
                    buttonEl.innerHTML = originalHtml;
                    buttonEl.disabled = false;
                });
        };

        var runToggleShareInline = function (id, nextVisibility, buttonEl) {
            if (!id) return;
            var originalHtml = buttonEl.innerHTML;
            buttonEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            buttonEl.disabled = true;

            var formData = new FormData();
            formData.append('is_hidden_share', String(nextVisibility));
            formData.append(csrfName, csrfValue);

            fetch(baseUrl + '/' + encodeURIComponent(id) + '/share-visibility', {
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
                        showNotice(json.message || 'Gagal mengubah visibilitas share item.', 'danger');
                        buttonEl.innerHTML = originalHtml;
                        buttonEl.disabled = false;
                        return;
                    }

                    var currentSelectedId = selectedIdInput ? selectedIdInput.value : '';
                    refreshPanels(currentSelectedId, formModeLabel ? formModeLabel.textContent : 'Tambah Item Baru').then(function () {
                        showNotice(json.message || 'Visibilitas share item berhasil diubah.', 'success');
                    });
                })
                .catch(function () {
                    showNotice('Gagal mengubah visibilitas share item.', 'danger');
                    buttonEl.innerHTML = originalHtml;
                    buttonEl.disabled = false;
                });
        };

        // Initialize features
        setupSearchInput();
        setupExpandCollapseAll();
        setupTreeEventDelegation();
    })();
</script>
<?= $this->endSection(); ?>
