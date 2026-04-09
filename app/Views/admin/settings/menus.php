<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="menu-setting">
    <input type="hidden" id="csrf_token" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">

    <?php if (empty($menu_access['edit'])): ?>
        <div class="alert alert-warning">Anda tidak memiliki akses untuk mengubah menu.</div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-body menu-header">
            <div>
                <h3 class="menu-title">Atur Urutan Menu</h3>
                <p class="menu-subtitle">Drag dan drop untuk mengubah urutan menu, submenu, dan sub-submenu.</p>
            </div>
            <div class="menu-actions">
                <button type="button" class="btn btn-outline-secondary" id="btnAddMenu" <?= empty($menu_access['edit']) ? 'disabled' : '' ?>>
                    <i class="fas fa-plus"></i> Tambah Menu
                </button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <ul class="menu-tree" id="menuLv1">
                <?php foreach (($lv1 ?? []) as $lv1Item): ?>
                    <li class="menu-item" data-id="<?= esc($lv1Item['id']) ?>" data-level="1">
                        <div class="menu-row">
                            <span class="drag-handle"><i class="fas fa-grip-lines"></i></span>
                            <span class="menu-label">
                                <i class="fas fa-folder"></i>
                                <?= esc($lv1Item['label'] ?? '-') ?>
                            </span>
                            <span class="menu-link"><?= esc($lv1Item['link'] ?? '#') ?></span>
                            <span class="menu-tools">
                                <button type="button" class="btn btn-sm btn-outline-primary btn-edit-menu"
                                    data-level="1"
                                    data-id="<?= esc($lv1Item['id']) ?>"
                                    data-label="<?= esc($lv1Item['label'] ?? '') ?>"
                                    data-link="<?= esc($lv1Item['link'] ?? '') ?>"
                                    data-icon="<?= esc($lv1Item['icon'] ?? '') ?>"
                                    data-ordering="<?= esc((string) ($lv1Item['ordering'] ?? '')) ?>"
                                    data-header=""
                                    <?= empty($menu_access['edit']) ? 'disabled' : '' ?>>
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info btn-change-icon"
                                    data-level="1"
                                    data-id="<?= esc($lv1Item['id']) ?>"
                                    data-label="<?= esc($lv1Item['label'] ?? '') ?>"
                                    data-icon="<?= esc($lv1Item['icon'] ?? '') ?>"
                                    <?= empty($menu_access['edit']) ? 'disabled' : '' ?>>
                                    <i class="fas fa-icons"></i>
                                </button>
                            </span>
                        </div>
                        <ul class="menu-tree menu-lv2-list">
                            <?php foreach (($lv2_by_parent[$lv1Item['id']] ?? []) as $lv2Item): ?>
                                <li class="menu-item" data-id="<?= esc($lv2Item['id']) ?>" data-level="2">
                                    <div class="menu-row">
                                        <span class="drag-handle"><i class="fas fa-grip-lines"></i></span>
                                        <span class="menu-label">
                                            <i class="far fa-circle"></i>
                                            <?= esc($lv2Item['label'] ?? '-') ?>
                                        </span>
                                        <span class="menu-link"><?= esc($lv2Item['link'] ?? '#') ?></span>
                                        <span class="menu-tools">
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-edit-menu"
                                                data-level="2"
                                                data-id="<?= esc($lv2Item['id']) ?>"
                                                data-label="<?= esc($lv2Item['label'] ?? '') ?>"
                                                data-link="<?= esc($lv2Item['link'] ?? '') ?>"
                                                data-icon="<?= esc($lv2Item['icon'] ?? '') ?>"
                                                data-ordering="<?= esc((string) ($lv2Item['ordering'] ?? '')) ?>"
                                                data-header="<?= esc((string) ($lv2Item['header'] ?? '')) ?>"
                                                <?= empty($menu_access['edit']) ? 'disabled' : '' ?>>
                                                <i class="fas fa-pen"></i>
                                            </button>
                                        </span>
                                    </div>
                                    <ul class="menu-tree menu-lv3-list">
                                        <?php foreach (($lv3_by_parent[$lv2Item['id']] ?? []) as $lv3Item): ?>
                                            <li class="menu-item" data-id="<?= esc($lv3Item['id']) ?>" data-level="3">
                                                <div class="menu-row">
                                                    <span class="drag-handle"><i class="fas fa-grip-lines"></i></span>
                                                    <span class="menu-label">
                                                        <i class="far fa-dot-circle"></i>
                                                        <?= esc($lv3Item['label'] ?? '-') ?>
                                                    </span>
                                                    <span class="menu-link"><?= esc($lv3Item['link'] ?? '#') ?></span>
                                                    <span class="menu-tools">
                                                        <button type="button" class="btn btn-sm btn-outline-primary btn-edit-menu"
                                                            data-level="3"
                                                            data-id="<?= esc($lv3Item['id']) ?>"
                                                            data-label="<?= esc($lv3Item['label'] ?? '') ?>"
                                                            data-link="<?= esc($lv3Item['link'] ?? '') ?>"
                                                            data-icon="<?= esc($lv3Item['icon'] ?? '') ?>"
                                                            data-ordering="<?= esc((string) ($lv3Item['ordering'] ?? '')) ?>"
                                                            data-header="<?= esc((string) ($lv3Item['header'] ?? '')) ?>"
                                                            <?= empty($menu_access['edit']) ? 'disabled' : '' ?>>
                                                            <i class="fas fa-pen"></i>
                                                        </button>
                                                    </span>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<div class="floating-save-wrap">
    <button type="button" class="btn btn-primary btn-lg floating-save-btn" id="btnSaveMenu" <?= empty($menu_access['edit']) ? 'disabled' : '' ?>>
        <i class="fas fa-save"></i> Simpan Urutan
    </button>
</div>

<div class="modal fade" id="iconModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form method="post" id="iconForm" class="modal-content" action="#">
            <?= csrf_field(); ?>
            <input type="hidden" name="level" id="iconMenuLevel">
            <input type="hidden" name="icon" id="iconSelectedValue">
            <div class="modal-header">
                <h5 class="modal-title">Ganti Icon Menu</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Menu: <strong id="iconMenuLabel">-</strong></p>
                <div class="icon-preview-box mb-3">
                    <span>Preview:</span>
                    <i id="iconPreview" class="fas fa-folder"></i>
                    <code id="iconPreviewText">fas fa-folder</code>
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" id="iconSearch" placeholder="Cari icon... contoh: user, chart, cog">
                </div>
                <div class="icon-grid" id="iconPickerGrid"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-info">Simpan Icon</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="menuModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="post" id="menuForm" class="modal-content" action="<?= site_url('admin/pengaturan/menus/tambah') ?>">
            <?= csrf_field(); ?>
            <div class="modal-header">
                <h5 class="modal-title" id="menuModalTitle">Tambah Menu</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="menuFormMode" value="create">
                <input type="hidden" id="menuId" name="id">
                <div class="form-group">
                    <label for="menuIdDisplay">ID Menu</label>
                    <input type="text" class="form-control" id="menuIdDisplay" readonly>
                    <small class="text-muted">ID dibuat otomatis mengikuti pola hirarki menu.</small>
                </div>
                <div class="form-group" id="menuLevelGroup">
                    <label for="menuLevel">Level</label>
                    <select class="form-control" id="menuLevel" name="level" required>
                        <option value="1">Level 1</option>
                        <option value="2">Level 2</option>
                        <option value="3">Level 3</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="menuLabel">Label</label>
                    <input type="text" class="form-control" id="menuLabel" name="label" required>
                </div>
                <div class="form-group" id="menuIconGroup">
                    <label for="menuIcon">Icon</label>
                    <select class="form-control" id="menuIcon" name="icon" required>
                        <option value="fas fa-folder">Folder</option>
                        <option value="fas fa-folder-open">Folder Open</option>
                        <option value="fas fa-file-alt">File</option>
                        <option value="fas fa-cog">Settings</option>
                        <option value="fas fa-users">Users</option>
                        <option value="fas fa-home">Home</option>
                        <option value="fas fa-chart-bar">Chart</option>
                        <option value="fas fa-briefcase">Briefcase</option>
                        <option value="fas fa-tools">Tools</option>
                    </select>
                </div>
                <div class="form-group" id="menuHeaderGroup" style="display: none;">
                    <label for="menuHeader">Parent</label>
                    <select class="form-control" id="menuHeader" name="header"></select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" id="menuSubmitBtn">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url('assets/adminlte/plugins/sortablejs/Sortable.min.js'); ?>"></script>
<style>
    @font-face {
        font-family: 'Space Grotesk';
        font-style: normal;
        font-weight: 300 700;
        font-display: swap;
        src: url("<?= base_url('assets/fonts/SpaceGrotesk-Variable.ttf'); ?>") format('truetype');
    }

    .menu-setting {
        font-family: 'Space Grotesk', sans-serif;
    }

    .menu-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .menu-title {
        font-size: 22px;
        margin: 0 0 4px;
    }

    .menu-actions {
        display: flex;
        gap: 8px;
        margin-left: auto;
    }

    .menu-subtitle {
        margin: 0;
        color: #516173;
    }

    .floating-save-wrap {
        position: fixed;
        right: 24px;
        bottom: 24px;
        z-index: 1050;
    }

    .floating-save-btn {
        border-radius: 999px;
        box-shadow: 0 10px 24px rgba(17, 24, 39, 0.22);
        padding: 10px 18px;
        font-weight: 600;
    }

    .menu-tree {
        list-style: none;
        padding-left: 0;
        margin: 0;
    }

    .menu-tree .menu-tree {
        margin-left: 18px;
        padding-left: 12px;
        border-left: 2px dashed #e1e6ee;
    }

    .menu-item {
        margin-bottom: 10px;
    }

    .menu-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid #e3e8ef;
    }

    .menu-row .menu-label {
        font-weight: 600;
        color: #1f2a37;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .menu-row .menu-link {
        margin-left: auto;
        font-size: 12px;
        color: #6b7280;
        background: #eef2f7;
        padding: 4px 8px;
        border-radius: 999px;
    }

    .menu-tools {
        display: inline-flex;
        gap: 6px;
    }

    .icon-preview-box {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f8fafc;
        border: 1px solid #e3e8ef;
        border-radius: 10px;
        padding: 10px 12px;
    }

    .icon-preview-box i {
        font-size: 20px;
        width: 26px;
        text-align: center;
    }

    .icon-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
        gap: 10px;
    }

    .icon-choice {
        border: 1px solid #dbe3ee;
        border-radius: 10px;
        background: #fff;
        padding: 10px 8px;
        text-align: center;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .icon-choice:hover {
        border-color: #60a5fa;
        background: #f0f7ff;
    }

    .icon-choice.active {
        border-color: #2563eb;
        background: #e8f0ff;
    }

    .icon-choice i {
        font-size: 18px;
        margin-bottom: 6px;
    }

    .icon-choice span {
        display: block;
        font-size: 11px;
        line-height: 1.2;
        color: #334155;
        word-break: break-word;
    }

    .drag-handle {
        cursor: grab;
        color: #94a3b8;
    }

    .sortable-ghost {
        opacity: 0.6;
    }

    @media (max-width: 767.98px) {
        .floating-save-wrap {
            right: 14px;
            left: 14px;
            bottom: 14px;
        }

        .floating-save-btn {
            width: 100%;
            border-radius: 12px;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const canEdit = <?= empty($menu_access['edit']) ? 'false' : 'true' ?>;
    const lv1Data = <?= json_encode(array_map(static function ($item) {
        return ['id' => (string) $item['id'], 'label' => (string) ($item['label'] ?? '')];
    }, $lv1 ?? []), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const lv2Data = <?= json_encode(array_map(static function ($item) {
        return ['id' => (string) $item['id'], 'label' => (string) ($item['label'] ?? ''), 'header' => (string) ($item['header'] ?? '')];
    }, $lv2 ?? []), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const lv3Data = <?= json_encode(array_map(static function ($item) {
        return ['id' => (string) $item['id'], 'label' => (string) ($item['label'] ?? ''), 'header' => (string) ($item['header'] ?? '')];
    }, $lv3 ?? []), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    const lv1List = document.getElementById('menuLv1');
    if (!lv1List) {
        return;
    }

    const csrfInput = document.getElementById('csrf_token');
    const menuModalEl = document.getElementById('menuModal');
    const iconModalEl = document.getElementById('iconModal');

    function getModalController(el) {
        if (!el) {
            return { show: function () {}, hide: function () {} };
        }

        if (window.bootstrap && window.bootstrap.Modal) {
            let instance = null;

            if (typeof window.bootstrap.Modal.getOrCreateInstance === 'function') {
                instance = window.bootstrap.Modal.getOrCreateInstance(el);
            } else if (typeof window.bootstrap.Modal.getInstance === 'function') {
                instance = window.bootstrap.Modal.getInstance(el);
                if (!instance) {
                    instance = new window.bootstrap.Modal(el);
                }
            } else {
                instance = new window.bootstrap.Modal(el);
            }

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
            show: function () {
                el.style.display = 'block';
                el.classList.add('show');
                document.body.classList.add('modal-open');
            },
            hide: function () {
                el.style.display = 'none';
                el.classList.remove('show');
                document.body.classList.remove('modal-open');
            },
        };
    }

    const menuModal = getModalController(menuModalEl);
    const iconModal = getModalController(iconModalEl);

    const iconOptions = [
        'fas fa-folder',
        'fas fa-folder-open',
        'fas fa-file-alt',
        'fas fa-cog',
        'fas fa-users',
        'fas fa-home',
        'fas fa-chart-bar',
        'fas fa-briefcase',
        'fas fa-tools',
        'fas fa-calendar-check',
        'fas fa-database',
        'fas fa-list',
            'fas fa-user',
            'fas fa-user-cog',
            'fas fa-user-shield',
            'fas fa-user-friends',
            'fas fa-building',
            'fas fa-city',
            'fas fa-school',
            'fas fa-book',
            'fas fa-book-open',
            'fas fa-edit',
            'fas fa-pen',
            'fas fa-table',
            'fas fa-chart-line',
            'fas fa-chart-pie',
            'fas fa-file-export',
            'fas fa-file-import',
            'fas fa-upload',
            'fas fa-download',
            'fas fa-inbox',
            'fas fa-bell',
            'fas fa-envelope',
            'fas fa-map-marker-alt',
            'fas fa-globe',
            'fas fa-wrench',
            'fas fa-sliders-h',
            'fas fa-shield-alt',
            'fas fa-lock',
            'fas fa-key',
            'fas fa-cubes',
            'fas fa-layer-group',
            'fas fa-network-wired',
            'fas fa-clipboard-list',
            'fas fa-tasks',
            'fas fa-check-circle',
            'fas fa-info-circle',
            'fas fa-exclamation-triangle',
            'fas fa-search',
            'fas fa-print',
            'fas fa-history',
            'fas fa-clock',
            'fas fa-archive',
    ];

        const iconLabels = {
            'fas fa-folder': 'Folder',
            'fas fa-folder-open': 'Folder Open',
            'fas fa-file-alt': 'File',
            'fas fa-cog': 'Settings',
            'fas fa-users': 'Users',
            'fas fa-home': 'Home',
            'fas fa-chart-bar': 'Chart Bar',
            'fas fa-briefcase': 'Briefcase',
            'fas fa-tools': 'Tools',
        };

    new Sortable(lv1List, {
        handle: '.drag-handle',
        animation: 150,
        disabled: !canEdit,
    });

    document.querySelectorAll('.menu-lv2-list').forEach(function (list) {
        new Sortable(list, {
            handle: '.drag-handle',
            animation: 150,
            group: 'lv2',
            disabled: !canEdit,
        });
    });

    document.querySelectorAll('.menu-lv3-list').forEach(function (list) {
        new Sortable(list, {
            handle: '.drag-handle',
            animation: 150,
            group: 'lv3',
            disabled: !canEdit,
        });
    });

    function populateHeaderOptions(level, selectedValue) {
        const headerGroup = document.getElementById('menuHeaderGroup');
        const header = document.getElementById('menuHeader');
        if (!headerGroup || !header) {
            return;
        }

        header.innerHTML = '';

        if (level === 1) {
            headerGroup.style.display = 'none';
            return;
        }

        const source = level === 2 ? lv1Data : lv2Data;
        source.forEach(function (item) {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.id + ' - ' + item.label;
            header.appendChild(option);
        });

        if (selectedValue) {
            header.value = selectedValue;
        } else if (source.length > 0) {
            header.value = source[0].id;
        }

        headerGroup.style.display = '';
    }

    function getNextMenuId(level, header) {
        if (level === 1) {
            let maxSequence = 0;
            lv1Data.forEach(function (item) {
                const match = String(item.id || '').match(/^(\d+)$/);
                if (match) {
                    maxSequence = Math.max(maxSequence, parseInt(match[1], 10));
                }
            });

            return String(maxSequence + 1).padStart(2, '0');
        }

        if (!header) {
            return '';
        }

        const source = level === 2 ? lv2Data : lv3Data;
        let maxSequence = 0;

        source.forEach(function (item) {
            if (String(item.header || '') !== String(header)) {
                return;
            }

            const prefix = String(header) + '-';
            const id = String(item.id || '');
            if (!id.startsWith(prefix)) {
                return;
            }

            const suffix = id.slice(prefix.length);
            const match = suffix.match(/^(\d+)$/);
            if (match) {
                maxSequence = Math.max(maxSequence, parseInt(match[1], 10));
            }
        });

        return String(header) + '-' + String(maxSequence + 1).padStart(2, '0');
    }

    function syncMenuIdPreview() {
        const menuFormMode = document.getElementById('menuFormMode');
        const menuId = document.getElementById('menuId');
        const menuIdDisplay = document.getElementById('menuIdDisplay');
        const menuLevel = document.getElementById('menuLevel');
        const menuHeader = document.getElementById('menuHeader');

        if (!menuFormMode || !menuId || !menuIdDisplay || !menuLevel) {
            return;
        }

        if (menuFormMode.value === 'edit') {
            menuIdDisplay.value = menuId.value || '';
            return;
        }

        const level = parseInt(menuLevel.value || '1', 10);
        const header = menuHeader && menuHeader.value ? menuHeader.value : '';
        const generatedId = getNextMenuId(level, header);
        menuId.value = generatedId;
        menuIdDisplay.value = generatedId;
    }

        function syncIconSelectOptions(selectedIcon) {
            const select = document.getElementById('menuIcon');
            if (!select) {
                return;
            }

            const currentValue = selectedIcon || select.value || 'fas fa-folder';
            select.innerHTML = '';

            iconOptions.forEach(function (iconClass) {
                const option = document.createElement('option');
                option.value = iconClass;
                option.textContent = iconLabels[iconClass] ? iconLabels[iconClass] + ' (' + iconClass + ')' : iconClass;
                if (iconClass === currentValue) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        }

        syncIconSelectOptions('fas fa-folder');

    const menuLevel = document.getElementById('menuLevel');
    if (menuLevel) {
        menuLevel.addEventListener('change', function () {
            populateHeaderOptions(parseInt(menuLevel.value, 10), null);
            syncMenuIdPreview();
        });
    }

    const menuHeader = document.getElementById('menuHeader');
    if (menuHeader) {
        menuHeader.addEventListener('change', function () {
            syncMenuIdPreview();
        });
    }

    const btnAddMenu = document.getElementById('btnAddMenu');
    if (btnAddMenu) {
        btnAddMenu.addEventListener('click', function () {
            document.getElementById('menuModalTitle').textContent = 'Tambah Menu';
            document.getElementById('menuSubmitBtn').textContent = 'Simpan';
            document.getElementById('menuForm').setAttribute('action', '<?= site_url('admin/pengaturan/menus/tambah') ?>');
            document.getElementById('menuFormMode').value = 'create';

            const menuLevelGroup = document.getElementById('menuLevelGroup');
            const menuLevel = document.getElementById('menuLevel');
            if (menuLevel) {
                menuLevel.disabled = false;
                menuLevel.value = '1';
            }
            if (menuLevelGroup) {
                menuLevelGroup.style.display = '';
            }

            const menuId = document.getElementById('menuId');
            const menuIdDisplay = document.getElementById('menuIdDisplay');
            if (menuId) {
                menuId.value = '';
            }
            if (menuIdDisplay) {
                menuIdDisplay.value = '';
            }

            const menuLabel = document.getElementById('menuLabel');
            if (menuLabel) {
                menuLabel.value = '';
            }

            const menuIconGroup = document.getElementById('menuIconGroup');
            const menuIcon = document.getElementById('menuIcon');
            if (menuIconGroup) {
                menuIconGroup.style.display = '';
            }
            if (menuIcon) {
                menuIcon.disabled = false;
            }
            syncIconSelectOptions('fas fa-folder');

            populateHeaderOptions(1, null);
            syncMenuIdPreview();
            menuModal.show();
        });
    }

    document.querySelectorAll('.btn-edit-menu').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const level = parseInt(btn.dataset.level || '1', 10);
            const id = String(btn.dataset.id || '');

            document.getElementById('menuModalTitle').textContent = 'Ubah Menu';
            document.getElementById('menuSubmitBtn').textContent = 'Perbarui';
            document.getElementById('menuForm').setAttribute('action', '<?= site_url('admin/pengaturan/menus') ?>/' + encodeURIComponent(id) + '/ubah');
            document.getElementById('menuFormMode').value = 'edit';
            const menuLevelGroup = document.getElementById('menuLevelGroup');
            const menuLevel = document.getElementById('menuLevel');
            if (menuLevel) {
                menuLevel.value = String(level);
                menuLevel.disabled = true;
            }
            if (menuLevelGroup) {
                menuLevelGroup.style.display = 'none';
            }

            const menuId = document.getElementById('menuId');
            menuId.value = id;
            menuId.readOnly = true;

            const menuIdDisplay = document.getElementById('menuIdDisplay');
            if (menuIdDisplay) {
                menuIdDisplay.value = id;
            }

            document.getElementById('menuLabel').value = String(btn.dataset.label || '');
                syncIconSelectOptions(String(btn.dataset.icon || 'fas fa-folder'));
            const menuIconGroup = document.getElementById('menuIconGroup');
            const menuIcon = document.getElementById('menuIcon');
            if (menuIconGroup) {
                menuIconGroup.style.display = 'none';
            }
            if (menuIcon) {
                menuIcon.disabled = true;
            }

            populateHeaderOptions(level, String(btn.dataset.header || ''));
            syncMenuIdPreview();
            menuModal.show();
        });
    });

    const iconForm = document.getElementById('iconForm');
    const iconPickerGrid = document.getElementById('iconPickerGrid');
    const iconPreview = document.getElementById('iconPreview');
    const iconPreviewText = document.getElementById('iconPreviewText');
    const iconSelectedValue = document.getElementById('iconSelectedValue');
    const iconMenuLevel = document.getElementById('iconMenuLevel');
    const iconMenuLabel = document.getElementById('iconMenuLabel');
        const iconSearch = document.getElementById('iconSearch');

    function setIconPreview(iconClass) {
        if (iconPreview) {
            iconPreview.className = iconClass;
        }
        if (iconPreviewText) {
            iconPreviewText.textContent = iconClass;
        }
        if (iconSelectedValue) {
            iconSelectedValue.value = iconClass;
        }
    }

    function renderIconChoices(selectedIcon, keyword) {
        if (!iconPickerGrid) {
            return;
        }

        iconPickerGrid.innerHTML = '';
        const normalizedKeyword = (keyword || '').trim().toLowerCase();

        iconOptions
            .filter(function (iconClass) {
                if (normalizedKeyword === '') {
                    return true;
                }

                const label = (iconLabels[iconClass] || '').toLowerCase();
                return iconClass.toLowerCase().indexOf(normalizedKeyword) !== -1 || label.indexOf(normalizedKeyword) !== -1;
            })
            .forEach(function (iconClass) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'icon-choice' + (iconClass === selectedIcon ? ' active' : '');
            btn.setAttribute('data-icon', iconClass);
            const caption = iconLabels[iconClass] ? iconLabels[iconClass] : iconClass;
            btn.innerHTML = '<i class="' + iconClass + '"></i><span>' + caption + '</span>';
            btn.addEventListener('click', function () {
                iconPickerGrid.querySelectorAll('.icon-choice').forEach(function (item) {
                    item.classList.remove('active');
                });
                btn.classList.add('active');
                setIconPreview(iconClass);
            });
            iconPickerGrid.appendChild(btn);
        });
    }

    document.querySelectorAll('.btn-change-icon').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const level = String(btn.dataset.level || '');
            const id = String(btn.dataset.id || '');
            const label = String(btn.dataset.label || id);
            const selectedIcon = String(btn.dataset.icon || 'fas fa-folder');

            if (iconForm) {
                iconForm.setAttribute('action', '<?= site_url('admin/pengaturan/menus') ?>/' + encodeURIComponent(id) + '/icon');
            }
            if (iconMenuLevel) {
                iconMenuLevel.value = level;
            }
            if (iconMenuLabel) {
                iconMenuLabel.textContent = label;
            }

            setIconPreview(selectedIcon);
                if (iconSearch) {
                    iconSearch.value = '';
                }
                renderIconChoices(selectedIcon, '');
            iconModal.show();
        });
    });

        if (iconSearch) {
            iconSearch.addEventListener('input', function () {
                renderIconChoices(iconSelectedValue ? iconSelectedValue.value : 'fas fa-folder', iconSearch.value);
            });
        }

    document.querySelectorAll('[data-dismiss="modal"], [data-bs-dismiss="modal"]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            menuModal.hide();
            iconModal.hide();
        });
    });

    const btnSaveMenu = document.getElementById('btnSaveMenu');
    if (btnSaveMenu) {
        btnSaveMenu.addEventListener('click', function () {
            const payload = buildPayload();
            const postData = new URLSearchParams();
            postData.append('payload', JSON.stringify(payload));

            if (csrfInput && csrfInput.name) {
                postData.append(csrfInput.name, csrfInput.value);
            }

            Swal.fire({
                title: 'Simpan urutan menu?',
                text: 'Perubahan akan diterapkan ke seluruh pengguna.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, simpan',
                cancelButtonText: 'Batal',
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }

                fetch('<?= site_url('admin/pengaturan/menus/save') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: postData.toString(),
                })
                    .then(function (response) {
                        return response.json().then(function (data) {
                            return { ok: response.ok, data: data };
                        });
                    })
                    .then(function (resultData) {
                        if (resultData.data && resultData.data.csrfHash && csrfInput) {
                            csrfInput.value = resultData.data.csrfHash;
                        }

                        if (!resultData.ok) {
                            throw new Error((resultData.data && resultData.data.message) || 'Gagal menyimpan urutan.');
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: (resultData.data && resultData.data.message) || 'Urutan menu tersimpan.',
                        });
                    })
                    .catch(function (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: error.message || 'Gagal menyimpan urutan.',
                        });
                    });
            });
        });
    }

    function buildPayload() {
        const lv1 = [];
        const lv2 = [];
        const lv3 = [];

        document.querySelectorAll('#menuLv1 > li.menu-item').forEach(function (lv1Item, index) {
            const id = lv1Item.getAttribute('data-id') || '';
            lv1.push({ id: id, ordering: index + 1 });

            lv1Item.querySelectorAll(':scope > ul.menu-lv2-list > li.menu-item').forEach(function (lv2Item, idx) {
                const lv2Id = lv2Item.getAttribute('data-id') || '';
                lv2.push({ id: lv2Id, ordering: idx + 1, header: id });

                lv2Item.querySelectorAll(':scope > ul.menu-lv3-list > li.menu-item').forEach(function (lv3Item, subIdx) {
                    const lv3Id = lv3Item.getAttribute('data-id') || '';
                    lv3.push({ id: lv3Id, ordering: subIdx + 1, header: lv2Id });
                });
            });
        });

        return { lv1: lv1, lv2: lv2, lv3: lv3 };
    }
});
</script>
<?= $this->endSection(); ?>
