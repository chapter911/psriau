<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>

<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap justify-content-between align-items-end" style="gap:12px;">
            <div>
                <h3 class="card-title mb-0">Daftar Kegiatan</h3>
                <small class="text-muted">Gunakan filter untuk mempersempit data kegiatan berdasarkan kategori, tanggal, atau lokasi.</small>
            </div>
            <div class="search-filter-box w-100">
                <form method="get" action="<?= site_url('/admin/dokumentasi/kegiatan-lapangan'); ?>" class="mb-0">
                <div class="form-row align-items-end mb-2">
                    <div class="form-group col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label for="serverFilterTitle" class="small text-muted mb-1">Judul Kegiatan</label>
                        <input type="text" class="form-control form-control-sm" id="serverFilterTitle" name="title" value="<?= esc((string) ($filters['title'] ?? '')); ?>" placeholder="Cari judul kegiatan...">
                    </div>
                    <div class="form-group col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label for="serverFilterKategori" class="small text-muted mb-1">Kategori</label>
                        <select class="form-control form-control-sm" id="serverFilterKategori" name="kategori">
                            <option value="">-- Semua Kategori --</option>
                            <?php foreach (($categories ?? []) as $cat): ?>
                                <option value="<?= esc($cat); ?>" <?= ($filters['kategori'] ?? '') === $cat ? 'selected' : ''; ?>>
                                    <?= esc($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label for="serverFilterDateFrom" class="small text-muted mb-1">Tanggal Dari</label>
                        <input type="date" class="form-control form-control-sm" id="serverFilterDateFrom" name="date_from" value="<?= esc((string) ($filters['date_from'] ?? '')); ?>">
                    </div>
                    <div class="form-group col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label for="serverFilterDateTo" class="small text-muted mb-1">Tanggal Sampai</label>
                        <input type="date" class="form-control form-control-sm" id="serverFilterDateTo" name="date_to" value="<?= esc((string) ($filters['date_to'] ?? '')); ?>">
                    </div>
                </div>
                <div class="form-row align-items-end mb-0">
                    <div class="form-group col-md-6 col-sm-12 mb-2 mb-md-0">
                        <label for="serverFilterLocation" class="small text-muted mb-1">Lokasi Pelaksanaan</label>
                        <input type="text" class="form-control form-control-sm" id="serverFilterLocation" name="location" value="<?= esc((string) ($filters['location'] ?? '')); ?>" placeholder="Cari lokasi kegiatan...">
                    </div>
                    <div class="form-group col-md-6 col-sm-12 mb-2 mb-md-0">
                        <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap:8px;">
                            <div class="d-flex align-items-center" style="gap:8px;">
                                <button type="submit" class="btn btn-sm btn-primary" id="applyServerFilters">
                                    <i class="fas fa-filter mr-1"></i> Terapkan
                                </button>
                                <a class="btn btn-sm btn-outline-secondary" id="resetServerFilters" href="<?= site_url('/admin/dokumentasi/kegiatan-lapangan'); ?>">
                                    <i class="fas fa-undo mr-1"></i> Reset
                                </a>
                            </div>
                            <?php if (! empty($permissions['add'])): ?>
                                <button type="button" class="btn btn-sm btn-success" id="btnOpenCreateModal">
                                    <i class="fas fa-plus mr-1"></i> Tambah Kegiatan
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover js-kegiatan-lapangan-table w-100" data-order='[[2,"desc"]]'>
                <thead>
                <tr>
                    <th>Judul Kegiatan</th>
                    <th>Kategori</th>
                    <th>Tanggal Kegiatan</th>
                    <th>Lokasi Kegiatan</th>
                    <th class="text-center">Foto Kegiatan</th>
                    <th>Dibuat Oleh</th>
                    <th class="text-center" style="width: 130px; min-width: 130px;">Bagikan &amp; Unduh</th>
                    <th class="text-center" style="width: 95px; min-width: 95px;">Aksi</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL INPUT / EDIT KEGIATAN -->
<div class="modal fade" id="modalKegiatanForm" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title font-weight-bold" id="modalKegiatanFormTitle">
                    <i class="fas fa-calendar-alt mr-2"></i>Tambah Kegiatan
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formKegiatanAjax" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <?= csrf_field(); ?>
                    <input type="hidden" id="modalActivityId" value="">

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="modalTitle" class="font-weight-bold">Judul Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" id="modalTitle" name="title" class="form-control" placeholder="Masukkan judul kegiatan..." required>
                        </div>
                        <div class="form-group col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="modalKategori" class="font-weight-bold mb-0">Kategori <span class="text-danger">*</span></label>
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="modalBtnToggleCategory" title="Beralih ke mode teks manual atau pilihan">
                                    <i class="fas fa-keyboard mr-1"></i><span id="modalBtnToggleCategoryText">Input Manual</span>
                                </button>
                            </div>
                            <div id="modalCategorySelectWrapper">
                                <select id="modalKategori" name="kategori" class="form-control select2" data-tags="true" data-placeholder="-- Pilih atau Ketik Kategori Baru --" required style="width:100%;">
                                    <option value="">-- Pilih atau Ketik Kategori Baru --</option>
                                    <?php foreach (($categories ?? ['Kegiatan Lapangan', 'Rapat Internal', 'Rapat External']) as $cat): ?>
                                        <option value="<?= esc($cat); ?>"><?= esc($cat); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div id="modalCategoryInputWrapper" class="d-none">
                                <input type="text" id="modalKategoriManual" class="form-control" placeholder="Ketik nama kategori baru secara langsung...">
                            </div>
                            <small class="text-muted d-block mt-1">Pilih kategori atau ketik baru lalu tekan Enter.</small>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="modalActivityDate" class="font-weight-bold">Tanggal Kegiatan <span class="text-danger">*</span></label>
                            <input type="date" id="modalActivityDate" name="activity_date" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="modalLocation" class="font-weight-bold">Lokasi Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" id="modalLocation" name="location" class="form-control" placeholder="Masukkan lokasi pelaksanaan..." required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="modalCompressionPercent" class="font-weight-bold">Persentase Kompresi Foto</label>
                            <select id="modalCompressionPercent" class="form-control">
                                <?php foreach ([30, 40, 50, 60, 70, 80, 90, 100] as $percent): ?>
                                    <option value="<?= $percent; ?>" <?= $percent === 30 ? 'selected' : ''; ?>><?= $percent; ?>% <?= $percent === 30 ? '(Rekomendasi - Cepat)' : ''; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Kompresi otomatis di browser sebelum upload agar super cepat dan hemat kuota.</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="modalCreatedBy" class="font-weight-bold">Dibuat Oleh</label>
                            <input type="text" id="modalCreatedBy" class="form-control bg-light" value="<?= esc((string) (session()->get('fullName') ?: session()->get('username') ?: 'system')); ?>" readonly>
                        </div>
                    </div>

                    <!-- AREA FOTO YANG SUDAH TERSIMPAN (KHUSUS MODE UBAH) -->
                    <div id="modalExistingPhotosSection" class="form-group d-none mb-3">
                        <label class="font-weight-bold d-block text-secondary mb-1">
                            <i class="fas fa-images mr-1"></i> Foto Tersimpan Saat Ini (<span id="modalExistingPhotosCount">0</span> foto)
                        </label>
                        <small class="text-muted d-block mb-2">Klik tombol silang merah (<i class="fas fa-times text-danger"></i>) pada foto jika ingin menghapusnya.</small>
                        <div class="existing-photo-grid" id="modalExistingPhotosGrid"></div>
                    </div>

                    <!-- AREA UPLOAD FOTO BARU -->
                    <div class="form-group mb-0">
                        <label class="font-weight-bold d-block mb-1">
                            <i class="fas fa-cloud-upload-alt mr-1 text-primary"></i> <span id="modalUploadPhotosLabel">Upload Foto Kegiatan</span>
                        </label>
                        <input type="file" id="modalFileInput" accept="image/*" multiple class="d-none">
                        <div class="photo-dropzone" id="modalPhotoDropzone">
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2" style="gap:10px;">
                                <div>
                                    <strong class="text-dark">Pilih atau seret foto ke sini</strong>
                                    <div class="text-muted small">Mendukung format JPG, PNG, WebP.</div>
                                </div>
                                <div class="text-right">
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="modalBtnPickPhotos">
                                        <i class="fas fa-plus-circle mr-1"></i> Pilih Foto
                                    </button>
                                    <div class="small text-muted mt-1" id="modalPhotoCounter">0/200 foto</div>
                                </div>
                            </div>
                            <div class="photo-dropzone__target text-center text-muted" id="modalDropzonePrompt">
                                <i class="far fa-images fa-2x mb-1 d-block text-secondary"></i>
                                Seret beberapa foto kegiatan ke kotak ini untuk upload cepat.
                            </div>
                            <div class="mt-2 d-flex flex-wrap" id="modalSelectedPhotoPreview"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary" id="modalBtnSubmit">
                        <i class="fas fa-save mr-1"></i> Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL BAGIKAN FOTO (PERMANEN) -->
<div class="modal fade" id="modalShareKegiatan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white py-3">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-share-alt mr-2"></i>Bagikan Galeri Foto Kegiatan
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <h6 class="font-weight-bold text-dark mb-1" id="shareKegiatanTitle">-</h6>
                    <div class="small text-muted" id="shareKegiatanMeta">-</div>
                </div>

                <div class="alert alert-info py-2 px-3 small mb-3">
                    <i class="fas fa-infinity mr-1"></i> <strong>Tautan Berbagi Permanen:</strong> Tautan galeri ini bersifat permanen tanpa batas waktu kedaluwarsa. Siapa saja yang memiliki tautan dapat membuka dan mengunduh dokumentasi foto ini.
                </div>

                <!-- Bagian ketika tautan SUDAH AKTIF -->
                <div id="sectionShareActive" class="d-none">
                    <div class="form-group mb-3">
                        <label for="inputShareUrl" class="small text-muted font-weight-bold mb-1">Tautan Galeri Publik</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="inputShareUrl" readonly>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-primary" id="btnCopyShareDirectLink" title="Salin Tautan">
                                    <i class="fas fa-copy mr-1"></i> Salin
                                </button>
                                <a href="#" target="_blank" rel="noopener noreferrer" class="btn btn-outline-info" id="btnOpenShareExternal" title="Buka di Tab Baru">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="textareaShareWaFormat" class="small text-muted font-weight-bold mb-1">Format Pesan WhatsApp Resmi</label>
                        <textarea class="form-control small bg-light" id="textareaShareWaFormat" rows="7" readonly style="resize:none; font-family:monospace;"></textarea>
                        <button type="button" class="btn btn-success btn-sm btn-block mt-2" id="btnCopyShareWaText">
                            <i class="fab fa-whatsapp mr-1"></i> Salin Format WhatsApp
                        </button>
                    </div>
                </div>

                <!-- Bagian ketika tautan BELUM DIBUAT / NONAKTIF -->
                <div id="sectionShareInactive" class="d-none text-center py-3">
                    <p class="text-muted mb-3">Tautan berbagi publik untuk kegiatan ini belum aktif.</p>
                    <button type="button" class="btn btn-success font-weight-bold px-4" id="btnCreateSharePermanent">
                        <i class="fas fa-link mr-1"></i> Aktifkan Tautan Permanen
                    </button>
                </div>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between">
                <button type="button" class="btn btn-outline-danger btn-sm d-none" id="btnDeactivateSharePermanent">
                    <i class="fas fa-ban mr-1"></i> Nonaktifkan Link
                </button>
                <button type="button" class="btn btn-secondary btn-sm ml-auto" data-dismiss="modal">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL VIEWER GALERI FOTO -->
<div class="modal fade" id="activityPhotoModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content photo-modal-content">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title mb-1" id="activityPhotoModalTitle">Foto Kegiatan</h5>
                    <small class="text-muted" id="activityPhotoModalMeta">-</small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pt-2">
                <div class="photo-viewer-shell">
                    <button type="button" class="photo-nav-btn photo-nav-btn--left" id="photoPrevBtn" aria-label="Foto sebelumnya">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div class="photo-viewer-main">
                        <img id="activityPhotoModalImage" src="" alt="Foto kegiatan">
                    </div>
                    <button type="button" class="photo-nav-btn photo-nav-btn--right" id="photoNextBtn" aria-label="Foto berikutnya">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                <div class="photo-caption mt-3" id="activityPhotoModalCaption"></div>
                <div class="photo-thumbnails mt-3" id="activityPhotoThumbnails"></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.DataTable) {
        return;
    }

    const $ = window.jQuery;
    const tableEl = document.querySelector('.js-kegiatan-lapangan-table');
    const filterTitle = document.getElementById('serverFilterTitle');
    const filterKategori = document.getElementById('serverFilterKategori');
    const filterDateFrom = document.getElementById('serverFilterDateFrom');
    const filterDateTo = document.getElementById('serverFilterDateTo');
    const filterLocation = document.getElementById('serverFilterLocation');
    const applyButton = document.getElementById('applyServerFilters');
    const resetButton = document.getElementById('resetServerFilters');

    // Share Modal Elements
    const shareModalEl = document.getElementById('modalShareKegiatan');
    const shareKegiatanTitle = document.getElementById('shareKegiatanTitle');
    const shareKegiatanMeta = document.getElementById('shareKegiatanMeta');
    const sectionShareActive = document.getElementById('sectionShareActive');
    const sectionShareInactive = document.getElementById('sectionShareInactive');
    const inputShareUrl = document.getElementById('inputShareUrl');
    const textareaShareWaFormat = document.getElementById('textareaShareWaFormat');
    const btnCopyShareDirectLink = document.getElementById('btnCopyShareDirectLink');
    const btnCopyShareWaText = document.getElementById('btnCopyShareWaText');
    const btnOpenShareExternal = document.getElementById('btnOpenShareExternal');
    const btnCreateSharePermanent = document.getElementById('btnCreateSharePermanent');
    const btnDeactivateSharePermanent = document.getElementById('btnDeactivateSharePermanent');

    // Photo Viewer Modal Elements
    const galleryModalEl = document.getElementById('activityPhotoModal');
    const galleryModalTitle = document.getElementById('activityPhotoModalTitle');
    const galleryModalMeta = document.getElementById('activityPhotoModalMeta');
    const galleryModalImage = document.getElementById('activityPhotoModalImage');
    const galleryModalCaption = document.getElementById('activityPhotoModalCaption');
    const galleryThumbnailsEl = document.getElementById('activityPhotoThumbnails');
    const galleryPrevBtn = document.getElementById('photoPrevBtn');
    const galleryNextBtn = document.getElementById('photoNextBtn');

    // Create/Edit Modal Elements
    const formModalEl = document.getElementById('modalKegiatanForm');
    const formModalTitle = document.getElementById('modalKegiatanFormTitle');
    const formAjax = document.getElementById('formKegiatanAjax');
    const modalActivityId = document.getElementById('modalActivityId');
    const modalTitle = document.getElementById('modalTitle');
    const modalKategori = document.getElementById('modalKategori');
    const modalKategoriManual = document.getElementById('modalKategoriManual');
    const modalBtnToggleCategory = document.getElementById('modalBtnToggleCategory');
    const modalBtnToggleCategoryText = document.getElementById('modalBtnToggleCategoryText');
    const modalCategorySelectWrapper = document.getElementById('modalCategorySelectWrapper');
    const modalCategoryInputWrapper = document.getElementById('modalCategoryInputWrapper');
    const modalActivityDate = document.getElementById('modalActivityDate');
    const modalLocation = document.getElementById('modalLocation');
    const modalCompressionPercent = document.getElementById('modalCompressionPercent');
    const modalExistingPhotosSection = document.getElementById('modalExistingPhotosSection');
    const modalExistingPhotosCount = document.getElementById('modalExistingPhotosCount');
    const modalExistingPhotosGrid = document.getElementById('modalExistingPhotosGrid');
    const modalFileInput = document.getElementById('modalFileInput');
    const modalPhotoDropzone = document.getElementById('modalPhotoDropzone');
    const modalBtnPickPhotos = document.getElementById('modalBtnPickPhotos');
    const modalPhotoCounter = document.getElementById('modalPhotoCounter');
    const modalSelectedPhotoPreview = document.getElementById('modalSelectedPhotoPreview');
    const modalBtnSubmit = document.getElementById('modalBtnSubmit');
    const btnOpenCreateModal = document.getElementById('btnOpenCreateModal');

    const csrfTokenName = <?= json_encode(csrf_token(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    let csrfTokenValue = <?= json_encode(csrf_hash(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    const dataUrl = <?= json_encode(site_url('/admin/dokumentasi/kegiatan-lapangan/data'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    const createUrl = <?= json_encode(site_url('/admin/dokumentasi/kegiatan-lapangan/tambah'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;

    const maxPhotos = 200;
    let currentSelectedPhotos = [];
    let currentExistingPhotoCount = 0;
    let currentGalleryPhotos = [];
    let currentGalleryIndex = 0;
    let isFilterLoading = false;
    let isCategoryManualMode = false;

    let activeShareConfig = {
        id: 0,
        title: '',
        activityDate: '',
        shareUrl: '',
        deactivateUrl: '',
        currentShareUrl: '',
        isActive: false
    };

    const escapeHtml = function (value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    };

    const buildShareCopyText = function (title, activityDate, url) {
        const safeActivityDate = String(activityDate || '').trim();
        return [
            'Izin mengirimkan dokumentasi',
            '',
            'acara : ' + (title || '-'),
            'tanggal : ' + safeActivityDate,
            'link : ' + (url || '-'),
            '',
            'Terima kasih.'
        ].join('\n');
    };

    // Inisialisasi Select2 di dalam Modal
    if ($.fn.select2 && modalKategori) {
        $(modalKategori).select2({
            theme: 'bootstrap4',
            dropdownParent: $(formModalEl),
            width: '100%',
            tags: true,
            placeholder: '-- Pilih atau Ketik Kategori Baru --',
            createTag: function (params) {
                const term = $.trim(params.term);
                if (term === '') {
                    return null;
                }
                return {
                    id: term,
                    text: term,
                    newTag: true
                };
            }
        });
    }

    // Toggle Input Manual Kategori di Modal
    if (modalBtnToggleCategory) {
        modalBtnToggleCategory.addEventListener('click', function () {
            isCategoryManualMode = !isCategoryManualMode;
            if (isCategoryManualMode) {
                modalCategorySelectWrapper.classList.add('d-none');
                modalCategoryInputWrapper.classList.remove('d-none');
                modalKategoriManual.value = $(modalKategori).val() || '';
                modalKategori.removeAttribute('name');
                modalKategori.removeAttribute('required');
                modalKategoriManual.setAttribute('name', 'kategori');
                modalKategoriManual.setAttribute('required', 'required');
                modalKategoriManual.focus();
                modalBtnToggleCategoryText.textContent = 'Pilih dari Dropdown';
                modalBtnToggleCategory.classList.remove('btn-outline-secondary');
                modalBtnToggleCategory.classList.add('btn-outline-primary');
            } else {
                const manualVal = (modalKategoriManual.value || '').trim();
                if (manualVal !== '') {
                    let exists = false;
                    $(modalKategori).find('option').each(function () {
                        if ($(this).val().toLowerCase() === manualVal.toLowerCase()) {
                            exists = true;
                            $(modalKategori).val($(this).val()).trigger('change');
                        }
                    });
                    if (!exists) {
                        const newOption = new Option(manualVal, manualVal, true, true);
                        $(modalKategori).append(newOption).trigger('change');
                    }
                }
                modalCategoryInputWrapper.classList.add('d-none');
                modalCategorySelectWrapper.classList.remove('d-none');
                modalKategoriManual.removeAttribute('name');
                modalKategoriManual.removeAttribute('required');
                modalKategori.setAttribute('name', 'kategori');
                modalKategori.setAttribute('required', 'required');
                modalBtnToggleCategoryText.textContent = 'Input Manual';
                modalBtnToggleCategory.classList.remove('btn-outline-primary');
                modalBtnToggleCategory.classList.add('btn-outline-secondary');
            }
        });
    }

    const resetCategoryToSelectMode = function (selectedVal) {
        isCategoryManualMode = false;
        modalCategoryInputWrapper.classList.add('d-none');
        modalCategorySelectWrapper.classList.remove('d-none');
        modalKategoriManual.removeAttribute('name');
        modalKategoriManual.removeAttribute('required');
        modalKategoriManual.value = '';
        modalKategori.setAttribute('name', 'kategori');
        modalKategori.setAttribute('required', 'required');
        modalBtnToggleCategoryText.textContent = 'Input Manual';
        modalBtnToggleCategory.classList.remove('btn-outline-primary');
        modalBtnToggleCategory.classList.add('btn-outline-secondary');

        if (selectedVal) {
            let exists = false;
            $(modalKategori).find('option').each(function () {
                if ($(this).val().toLowerCase() === selectedVal.toLowerCase()) {
                    exists = true;
                    $(modalKategori).val($(this).val()).trigger('change');
                }
            });
            if (!exists) {
                const newOpt = new Option(selectedVal, selectedVal, true, true);
                $(modalKategori).append(newOpt).trigger('change');
            }
        } else {
            $(modalKategori).val('Kegiatan Lapangan').trigger('change');
        }
    };

    const updateModalCounter = function () {
        if (modalPhotoCounter) {
            const total = currentExistingPhotoCount + currentSelectedPhotos.length;
            modalPhotoCounter.textContent = `${total}/${maxPhotos} foto`;
        }
    };

    const renderModalSelectedPreviews = function () {
        modalSelectedPhotoPreview.innerHTML = '';
        currentSelectedPhotos.forEach(function (item, index) {
            const card = document.createElement('div');
            card.className = 'selected-photo-card';
            card.innerHTML = `
                <div class="selected-photo-card__preview">
                    <img src="${item.previewUrl}" alt="${escapeHtml(item.name)}">
                    <button type="button" class="selected-photo-card__remove" title="Hapus foto">&times;</button>
                </div>
                <div class="selected-photo-card__meta">
                    <div class="selected-photo-card__name">${escapeHtml(item.name)}</div>
                    <div>${item.sizeLabel}</div>
                </div>
            `;

            card.querySelector('.selected-photo-card__remove').addEventListener('click', function () {
                URL.revokeObjectURL(item.previewUrl);
                currentSelectedPhotos.splice(index, 1);
                renderModalSelectedPreviews();
                updateModalCounter();
            });

            modalSelectedPhotoPreview.appendChild(card);
        });
    };

    const addFilesToSelection = function (fileList) {
        const incoming = Array.from(fileList || []).filter(f => f && f.type && f.type.startsWith('image/'));
        if (incoming.length === 0) {
            return;
        }

        const remainingSlots = maxPhotos - currentExistingPhotoCount - currentSelectedPhotos.length;
        if (remainingSlots <= 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Batas Maksimum Tercapai',
                    text: `Maksimal ${maxPhotos} foto untuk satu kegiatan telah tercapai.`
                });
            }
            return;
        }

        if (incoming.length > remainingSlots) {
            incoming.length = remainingSlots;
        }

        for (const file of incoming) {
            const previewUrl = URL.createObjectURL(file);
            currentSelectedPhotos.push({
                file: file,
                previewUrl: previewUrl,
                name: file.name,
                sizeLabel: `${Math.max(1, Math.round(file.size / 1024))} KB (kompresi saat simpan)`
            });
        }

        renderModalSelectedPreviews();
        updateModalCounter();
    };

    if (modalBtnPickPhotos && modalFileInput) {
        modalBtnPickPhotos.addEventListener('click', function () {
            modalFileInput.click();
        });

        modalFileInput.addEventListener('change', function () {
            addFilesToSelection(modalFileInput.files);
            modalFileInput.value = '';
        });
    }

    if (modalPhotoDropzone) {
        ['dragenter', 'dragover'].forEach(eventName => {
            modalPhotoDropzone.addEventListener(eventName, function (e) {
                e.preventDefault();
                modalPhotoDropzone.classList.add('is-dragover');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            modalPhotoDropzone.addEventListener(eventName, function (e) {
                e.preventDefault();
                modalPhotoDropzone.classList.remove('is-dragover');
            });
        });

        modalPhotoDropzone.addEventListener('drop', function (e) {
            addFilesToSelection(e.dataTransfer ? e.dataTransfer.files : []);
        });
    }

    const resizeFile = function (file, scaleFactor) {
        return new Promise(function (resolve, reject) {
            const image = new Image();
            const objectUrl = URL.createObjectURL(file);

            image.onload = function () {
                const width = Math.max(1, Math.round(image.width * scaleFactor));
                const height = Math.max(1, Math.round(image.height * scaleFactor));
                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;

                const context = canvas.getContext('2d');
                if (!context) {
                    URL.revokeObjectURL(objectUrl);
                    reject(new Error('Canvas context unavailable'));
                    return;
                }

                context.imageSmoothingEnabled = true;
                context.imageSmoothingQuality = 'high';
                context.drawImage(image, 0, 0, width, height);

                const outputType = file.type === 'image/png' ? 'image/png' : 'image/jpeg';
                canvas.toBlob(function (blob) {
                    URL.revokeObjectURL(objectUrl);
                    if (!blob) {
                        reject(new Error('Resize failed'));
                        return;
                    }

                    const extension = outputType === 'image/png' ? 'png' : 'jpg';
                    const baseName = (file.name || 'foto').replace(/\.[^.]+$/, '');
                    const newName = `${baseName}-sm.${extension}`;
                    resolve(new File([blob], newName, { type: outputType, lastModified: Date.now() }));
                }, outputType, outputType === 'image/jpeg' ? 0.86 : undefined);
            };

            image.onerror = function () {
                URL.revokeObjectURL(objectUrl);
                reject(new Error('Cannot read image'));
            };

            image.src = objectUrl;
        });
    };

    const compressSelectedFiles = async function (scaleFactor, setProgress) {
        const compressed = [];
        const total = currentSelectedPhotos.length;

        for (let index = 0; index < total; index++) {
            const item = currentSelectedPhotos[index];
            if (typeof setProgress === 'function') {
                setProgress(5 + ((index + 1) / Math.max(total, 1)) * 35, `Memproses kompresi foto ${index + 1}/${total}...`);
            }
            const resizedFile = await resizeFile(item.file, scaleFactor);
            compressed.push({
                file: resizedFile,
                name: resizedFile.name || item.name
            });
        }

        return compressed;
    };

    // Buka Modal Tambah Kegiatan
    if (btnOpenCreateModal) {
        btnOpenCreateModal.addEventListener('click', function () {
            formModalTitle.innerHTML = '<i class="fas fa-plus-circle mr-2"></i>Tambah Kegiatan';
            formAjax.action = createUrl;
            modalActivityId.value = '';
            modalTitle.value = '';
            modalActivityDate.value = new Date().toISOString().split('T')[0];
            modalLocation.value = '';
            modalCompressionPercent.value = '30';
            resetCategoryToSelectMode('Kegiatan Lapangan');

            // Reset foto
            currentSelectedPhotos = [];
            currentExistingPhotoCount = 0;
            renderModalSelectedPreviews();
            modalExistingPhotosSection.classList.add('d-none');
            modalExistingPhotosGrid.innerHTML = '';
            updateModalCounter();

            $(formModalEl).modal('show');
        });
    }

    // Buka Modal Ubah Kegiatan (via AJAX)
    $(tableEl).on('click', '.js-edit-kegiatan', function () {
        const id = this.getAttribute('data-id');
        if (!id) return;

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Memuat data...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
        }

        $.ajax({
            url: <?= json_encode(site_url('/admin/dokumentasi/kegiatan-lapangan'), JSON_UNESCAPED_SLASHES); ?> + '/' + id + '/ubah?format=json',
            method: 'GET',
            dataType: 'json'
        }).done(function (res) {
            if (typeof Swal !== 'undefined') {
                Swal.close();
            }

            if (!res || res.status !== 'ok' || !res.activity) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: 'Data kegiatan tidak ditemukan.' });
                }
                return;
            }

            const act = res.activity;
            formModalTitle.innerHTML = '<i class="fas fa-edit mr-2"></i>Ubah Data Kegiatan';
            formAjax.action = res.actionUrl || (<?= json_encode(site_url('/admin/dokumentasi/kegiatan-lapangan'), JSON_UNESCAPED_SLASHES); ?> + '/' + id + '/ubah');
            modalActivityId.value = act.id || id;
            modalTitle.value = act.title || '';
            modalActivityDate.value = act.activity_date ? act.activity_date.split(' ')[0] : '';
            modalLocation.value = act.location || '';
            modalCompressionPercent.value = '30';
            resetCategoryToSelectMode(act.kategori || 'Kegiatan Lapangan');

            // Existing Photos render
            const photos = Array.isArray(res.photos) ? res.photos : [];
            currentExistingPhotoCount = photos.length;
            modalExistingPhotosCount.textContent = currentExistingPhotoCount;

            modalExistingPhotosGrid.innerHTML = '';
            if (photos.length > 0) {
                modalExistingPhotosSection.classList.remove('d-none');
                photos.forEach(function (p) {
                    const fig = document.createElement('figure');
                    fig.className = 'existing-photo-card';
                    fig.id = 'existingPhotoCard_' + p.id;
                    fig.innerHTML = `
                        <div class="existing-photo-card__wrapper">
                            <img src="${escapeHtml(p.photo_path)}" alt="Foto">
                            <button type="button" class="existing-photo-card__del js-delete-existing-photo" data-photo-id="${p.id}" title="Hapus foto ini">&times;</button>
                        </div>
                        <figcaption>${escapeHtml(p.photo_name || 'Foto')}</figcaption>
                    `;
                    modalExistingPhotosGrid.appendChild(fig);
                });
            } else {
                modalExistingPhotosSection.classList.add('d-none');
            }

            // Reset foto baru
            currentSelectedPhotos = [];
            renderModalSelectedPreviews();
            updateModalCounter();

            $(formModalEl).modal('show');
        }).fail(function (xhr) {
            if (typeof Swal !== 'undefined') {
                const msg = xhr && xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal memuat data kegiatan.';
                Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
            }
        });
    });

    // Hapus foto lama di dalam modal Edit
    $(modalExistingPhotosGrid).on('click', '.js-delete-existing-photo', function () {
        const photoId = this.getAttribute('data-photo-id');
        if (!photoId) return;

        if (currentExistingPhotoCount + currentSelectedPhotos.length <= 1) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tidak Dapat Dihapus',
                    text: 'Kegiatan harus menyisakan minimal 1 foto dokumentasi.'
                });
            }
            return;
        }

        const doDelete = function () {
            $.ajax({
                url: <?= json_encode(site_url('/admin/dokumentasi/kegiatan-lapangan/photo'), JSON_UNESCAPED_SLASHES); ?> + '/' + photoId + '/hapus',
                method: 'POST',
                dataType: 'json',
                data: {
                    [csrfTokenName]: csrfTokenValue
                }
            }).done(function (res) {
                if (res && res.csrf_hash) {
                    csrfTokenValue = res.csrf_hash;
                }

                if (res && res.status === 'ok') {
                    const card = document.getElementById('existingPhotoCard_' + photoId);
                    if (card) card.remove();

                    currentExistingPhotoCount = Math.max(0, currentExistingPhotoCount - 1);
                    modalExistingPhotosCount.textContent = currentExistingPhotoCount;
                    if (currentExistingPhotoCount === 0) {
                        modalExistingPhotosSection.classList.add('d-none');
                    }
                    updateModalCounter();
                    dt.ajax.reload(null, false);

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Foto Terhapus',
                            timer: 1000,
                            showConfirmButton: false
                        });
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: (res && res.message) ? res.message : 'Gagal menghapus foto.' });
                    }
                }
            }).fail(function (xhr) {
                const msg = xhr && xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Koneksi bermasalah saat menghapus foto.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                }
            });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Hapus foto ini?',
                text: 'Foto ini akan langsung dihapus dari penyimpanan server.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    doDelete();
                }
            });
        } else if (confirm('Hapus foto ini dari server?')) {
            doDelete();
        }
    });

    // Submit Form AJAX dengan Kompresi Klien
    formAjax.addEventListener('submit', async function (e) {
        e.preventDefault();

        if (currentExistingPhotoCount + currentSelectedPhotos.length === 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Foto Belum Dipilih',
                    text: 'Minimal 1 foto kegiatan harus dipilih.'
                });
            }
            return;
        }

        const formData = new FormData(formAjax);
        formData.set(csrfTokenName, csrfTokenValue);

        const progressHtml = `
            <div class="text-left">
                <div class="mb-2" id="uploadStatusText">Sedang menyiapkan foto...</div>
                <div class="progress" style="height: 12px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" id="modalUploadProgressBar" role="progressbar" style="width: 0%">0%</div>
                </div>
            </div>
        `;

        const setProgress = function (percent, label) {
            const bar = document.getElementById('modalUploadProgressBar');
            const text = document.getElementById('uploadStatusText');
            const safePercent = Math.max(0, Math.min(100, Math.round(percent)));
            if (bar) {
                bar.style.width = safePercent + '%';
                bar.textContent = safePercent + '%';
            }
            if (text && label) {
                text.textContent = label;
            }
        };

        if (modalBtnSubmit) modalBtnSubmit.disabled = true;

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Menyimpan Kegiatan',
                html: progressHtml,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading()
            });
        }

        try {
            const compressionPercent = Math.max(30, Math.min(100, Number(modalCompressionPercent.value || 30)));
            const scaleFactor = compressionPercent / 100;
            const compressedFiles = await compressSelectedFiles(scaleFactor, setProgress);

            compressedFiles.forEach(function (item) {
                formData.append('activity_photos[]', item.file, item.file.name || 'photo.jpg');
            });
        } catch (err) {
            if (modalBtnSubmit) modalBtnSubmit.disabled = false;
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal memproses kompresi foto.' });
            }
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open('POST', formAjax.action, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.upload.addEventListener('progress', function (event) {
            if (event.lengthComputable) {
                const uploadPercent = (event.loaded / event.total) * 60;
                const totalPercent = 40 + uploadPercent;
                setProgress(totalPercent, `Mengunggah foto (${Math.round(totalPercent)}%)...`);
            }
        });

        xhr.addEventListener('load', function () {
            if (modalBtnSubmit) modalBtnSubmit.disabled = false;

            let payload = null;
            try {
                payload = JSON.parse(xhr.responseText || '{}');
            } catch (e) {
                payload = null;
            }

            if (payload && payload.csrf_hash) {
                csrfTokenValue = payload.csrf_hash;
            }

            if (xhr.status >= 200 && xhr.status < 300 && payload && payload.status === 'ok') {
                $(formModalEl).modal('hide');
                dt.ajax.reload(null, false);

                // User Rule #1: Pastikan Notifikasinya tidak double (hanya 1 notifikasi)
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: payload.message || 'Data kegiatan berhasil disimpan.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
                return;
            }

            const message = payload && payload.message ? payload.message : 'Gagal menyimpan kegiatan.';
            const errorDetails = payload && payload.errors && typeof payload.errors === 'object'
                ? Object.values(payload.errors).filter(Boolean).join('<br>')
                : '';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    html: errorDetails ? `<div>${message}</div><div class="mt-2 text-left small text-danger">${errorDetails}</div>` : message
                });
            }
        });

        xhr.addEventListener('error', function () {
            if (modalBtnSubmit) modalBtnSubmit.disabled = false;
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Gagal', text: 'Koneksi bermasalah saat mengirim data.' });
            }
        });

        xhr.send(formData);
    });

    // Setup DataTable
    const dt = $(tableEl).DataTable({
        processing: true,
        serverSide: true,
        responsive: false,
        autoWidth: false,
        scrollX: true,
        scrollCollapse: true,
        searching: false,
        order: [[2, 'desc']],
        ajax: {
            url: dataUrl,
            type: 'GET',
            data: function (d) {
                d.title = filterTitle ? filterTitle.value : '';
                d.kategori = filterKategori ? filterKategori.value : '';
                d.date_from = filterDateFrom ? filterDateFrom.value : '';
                d.date_to = filterDateTo ? filterDateTo.value : '';
                d.location = filterLocation ? filterLocation.value : '';
            }
        },
        columns: [
            {
                data: 'title',
                defaultContent: '-',
                render: function (data) {
                    return escapeHtml(data || '-');
                }
            },
            {
                data: 'kategori',
                defaultContent: 'Kegiatan Lapangan',
                render: function (data) {
                    const cat = escapeHtml(data || 'Kegiatan Lapangan');
                    let badgeClass = 'badge badge-secondary';
                    if (cat === 'Kegiatan Lapangan') {
                        badgeClass = 'badge badge-success';
                    } else if (cat === 'Rapat Internal') {
                        badgeClass = 'badge badge-info';
                    } else if (cat === 'Rapat External') {
                        badgeClass = 'badge badge-primary';
                    }
                    return '<span class="' + badgeClass + '" style="font-size:0.8rem; font-weight:500;">' + cat + '</span>';
                }
            },
            {
                data: 'activity_date',
                defaultContent: '-',
                render: function (data) {
                    return escapeHtml(data || '-');
                }
            },
            {
                data: 'location',
                defaultContent: '-',
                render: function (data) {
                    return escapeHtml(data || '-');
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function (row) {
                    const photos = Array.isArray(row.photos) ? row.photos : [];
                    const coverPhoto = row.cover_photo || '';
                    const title = row.title || '-';
                    const activityDate = row.activity_date || '-';
                    const photosJson = encodeURIComponent(JSON.stringify(photos));
                    const photoCount = Number(row.photo_count || 0);

                    let thumbHtml = '';
                    if (coverPhoto !== '') {
                        thumbHtml = '<img src="' + escapeHtml(coverPhoto) + '" alt="Foto kegiatan" class="activity-table-thumb">';
                    } else {
                        thumbHtml = '<div class="activity-table-thumb-empty"><i class="far fa-image fa-lg"></i></div>';
                    }

                    return ''
                        + '<button type="button" class="btn btn-link p-0 text-decoration-none js-open-gallery activity-photo-click-btn"'
                        + ' data-gallery="' + photosJson + '"'
                        + ' data-title="' + encodeURIComponent(title) + '"'
                        + ' data-date="' + encodeURIComponent(activityDate) + '"'
                        + ' data-toggle="tooltip" data-placement="top" title="Klik foto untuk melihat galeri">'
                        + '<div class="d-inline-flex flex-column align-items-center">'
                        + thumbHtml
                        + '<span class="activity-photo-count-badge mt-1"><i class="fas fa-camera mr-1"></i>' + photoCount + ' foto</span>'
                        + '</div>'
                        + '</button>';
                }
            },
            {
                data: 'created_by',
                defaultContent: '-',
                render: function (data) {
                    return escapeHtml(data || '-');
                }
            },
            {
                // Kolom Pemisahan: Bagikan & Unduh (Publikasi Dokumentasi)
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function (row) {
                    const id = row.id;
                    const shareCreateUrl = row.share_create_url || '#';
                    const shareDeactivateUrl = row.share_deactivate_url || '#';
                    const title = row.title || '-';
                    const activityDate = row.activity_date || '-';
                    const sharePublicUrl = row.share_public_url || '';
                    const shareIsActive = Boolean(row.share_is_active);
                    const hasPhotos = Number(row.photo_count || 0) > 0;

                    let buttons = '<div class="d-inline-flex align-items-center justify-content-center" style="gap:5px;">';

                    // 1. Tombol Bagikan Foto (Icon Button)
                    const shareBtnClass = shareIsActive ? 'btn btn-sm btn-info text-white shadow-sm' : 'btn btn-sm btn-outline-info';
                    const shareTooltip = shareIsActive ? 'Tautan Berbagi Aktif (Permanen)' : 'Bagikan Galeri (Permanen)';
                    buttons += '<button type="button" class="' + shareBtnClass + ' js-share-photo"'
                        + ' data-id="' + id + '"'
                        + ' data-share-url="' + escapeHtml(shareCreateUrl) + '"'
                        + ' data-share-deactivate-url="' + escapeHtml(shareDeactivateUrl) + '"'
                        + ' data-share-title="' + escapeHtml(encodeURIComponent(title)) + '"'
                        + ' data-share-activity-date="' + escapeHtml(encodeURIComponent(activityDate)) + '"'
                        + ' data-share-public-url="' + escapeHtml(sharePublicUrl) + '"'
                        + ' data-share-active="' + (shareIsActive ? '1' : '0') + '"'
                        + ' data-toggle="tooltip" data-placement="top" title="' + escapeHtml(shareTooltip) + '">'
                        + '<i class="fas fa-share-alt"></i>'
                        + '</button>';

                    // 2. Tombol Buka Link Publik (jika sudah aktif)
                    if (shareIsActive && sharePublicUrl) {
                        buttons += '<a class="btn btn-sm btn-outline-success shadow-sm" href="' + escapeHtml(sharePublicUrl) + '" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="top" title="Buka Galeri Publik">'
                            + '<i class="fas fa-external-link-alt"></i>'
                            + '</a>';
                    }

                    // 3. Tombol Download ZIP (Icon Button)
                    if (row.can_export && hasPhotos) {
                        buttons += '<a class="btn btn-sm btn-outline-primary shadow-sm" href="' + escapeHtml(row.download_zip_url || '#') + '" data-toggle="tooltip" data-placement="top" title="Download Semua Foto (.ZIP)">'
                            + '<i class="fas fa-file-archive"></i>'
                            + '</a>';
                    } else {
                        buttons += '<button type="button" class="btn btn-sm btn-outline-secondary" disabled data-toggle="tooltip" data-placement="top" title="Tidak ada foto / Terkunci">'
                            + '<i class="fas fa-file-archive"></i>'
                            + '</button>';
                    }

                    buttons += '</div>';
                    return buttons;
                }
            },
            {
                // Kolom Aksi Khusus Pengelolaan Rekaman (Ubah & Hapus)
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function (row) {
                    const id = row.id;
                    let buttons = '<div class="d-inline-flex align-items-center justify-content-center" style="gap:5px;">';

                    // 1. Tombol Ubah (Icon Button) - Solid Warning dengan warna teks gelap & border kontras tinggi agar sangat jelas terlihat
                    if (row.can_edit) {
                        buttons += '<button type="button" class="btn btn-sm btn-warning text-dark font-weight-bold shadow-sm js-edit-kegiatan" data-id="' + id + '" data-toggle="tooltip" data-placement="top" title="Ubah Data Kegiatan" style="border: 1px solid #d39e00;">'
                            + '<i class="fas fa-edit"></i>'
                            + '</button>';
                    }

                    // 2. Tombol Hapus (Icon Button) - Solid Danger agar tegas dan terlihat jelas
                    if (row.can_delete) {
                        buttons += '<form class="inline-form d-inline" method="post" action="' + escapeHtml(row.delete_url || '#') + '"'
                            + ' data-confirm-title="Hapus Kegiatan"'
                            + ' data-confirm-text="Hapus kegiatan ini beserta seluruh fotonya?"'
                            + ' data-confirm-button="Ya, hapus">'
                            + '<input type="hidden" name="' + escapeHtml(csrfTokenName) + '" value="' + escapeHtml(csrfTokenValue) + '">'
                            + '<button type="submit" class="btn btn-sm btn-danger shadow-sm" data-toggle="tooltip" data-placement="top" title="Hapus Kegiatan">'
                            + '<i class="fas fa-trash-alt"></i>'
                            + '</button>'
                            + '</form>';
                    }

                    buttons += '</div>';
                    return buttons || '<span class="text-muted small">-</span>';
                }
            }
        ],
        drawCallback: function () {
            // Aktifkan bootstrap tooltips setiap kali tabel dirender ulang
            $('[data-toggle="tooltip"]').tooltip({ boundary: 'window' });
        },
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

    $(dt.table().container()).find('.dataTables_filter').hide();

    // Buka Modal Bagikan Foto Permanen
    $(tableEl).on('click', '.js-share-photo', function () {
        const id = this.getAttribute('data-id');
        const title = decodeURIComponent(this.getAttribute('data-share-title') || '');
        const date = decodeURIComponent(this.getAttribute('data-share-activity-date') || '');
        const shareUrl = this.getAttribute('data-share-url') || '';
        const deactivateUrl = this.getAttribute('data-share-deactivate-url') || '';
        const publicUrl = this.getAttribute('data-share-public-url') || '';
        const isActive = this.getAttribute('data-share-active') === '1';

        activeShareConfig = {
            id: id,
            title: title,
            activityDate: date,
            shareUrl: shareUrl,
            deactivateUrl: deactivateUrl,
            currentShareUrl: publicUrl,
            isActive: isActive
        };

        shareKegiatanTitle.textContent = title || '-';
        shareKegiatanMeta.textContent = (date ? 'Tanggal Kegiatan: ' + date : '');

        if (isActive && publicUrl) {
            sectionShareActive.classList.remove('d-none');
            sectionShareInactive.classList.add('d-none');
            btnDeactivateSharePermanent.classList.remove('d-none');

            inputShareUrl.value = publicUrl;
            btnOpenShareExternal.href = publicUrl;
            textareaShareWaFormat.value = buildShareCopyText(title, date, publicUrl);
        } else {
            sectionShareActive.classList.add('d-none');
            sectionShareInactive.classList.remove('d-none');
            btnDeactivateSharePermanent.classList.add('d-none');

            inputShareUrl.value = '';
            textareaShareWaFormat.value = '';
        }

        $(shareModalEl).modal('show');
    });

    // Buat Tautan Permanen
    if (btnCreateSharePermanent) {
        btnCreateSharePermanent.addEventListener('click', function () {
            if (!activeShareConfig.shareUrl) return;

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Membuat tautan...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
            }

            $.ajax({
                url: activeShareConfig.shareUrl,
                method: 'POST',
                dataType: 'json',
                data: {
                    [csrfTokenName]: csrfTokenValue
                }
            }).done(function (res) {
                if (res && res.csrf_hash) {
                    csrfTokenValue = res.csrf_hash;
                }

                if (res && res.status === 'ok') {
                    activeShareConfig.isActive = true;
                    activeShareConfig.currentShareUrl = res.share_url || '';

                    sectionShareActive.classList.remove('d-none');
                    sectionShareInactive.classList.add('d-none');
                    btnDeactivateSharePermanent.classList.remove('d-none');

                    inputShareUrl.value = activeShareConfig.currentShareUrl;
                    btnOpenShareExternal.href = activeShareConfig.currentShareUrl;
                    textareaShareWaFormat.value = buildShareCopyText(activeShareConfig.title, activeShareConfig.activityDate, activeShareConfig.currentShareUrl);

                    dt.ajax.reload(null, false);

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Tautan Berhasil Dibuat',
                            text: 'Tautan berbagi permanen kini aktif dan siap dibagikan.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: (res && res.message) ? res.message : 'Gagal membuat tautan.' });
                    }
                }
            }).fail(function (xhr) {
                const msg = xhr && xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Koneksi bermasalah.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                }
            });
        });
    }

    // Salin Tautan Langsung
    if (btnCopyShareDirectLink) {
        btnCopyShareDirectLink.addEventListener('click', function () {
            const url = inputShareUrl.value;
            if (!url) return;

            navigator.clipboard.writeText(url).then(function () {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'success', title: 'Tautan Tersalin', timer: 1000, showConfirmButton: false });
                }
            }).catch(function () {
                inputShareUrl.select();
                document.execCommand('copy');
            });
        });
    }

    // Salin Pesan Format WhatsApp
    if (btnCopyShareWaText) {
        btnCopyShareWaText.addEventListener('click', function () {
            const text = textareaShareWaFormat.value;
            if (!text) return;

            navigator.clipboard.writeText(text).then(function () {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'success', title: 'Pesan WA Tersalin', text: 'Format pesan siap ditempel (paste) ke WhatsApp.', timer: 1200, showConfirmButton: false });
                }
            }).catch(function () {
                textareaShareWaFormat.select();
                document.execCommand('copy');
            });
        });
    }

    // Nonaktifkan Tautan Permanen
    if (btnDeactivateSharePermanent) {
        btnDeactivateSharePermanent.addEventListener('click', function () {
            if (!activeShareConfig.deactivateUrl) return;

            const doDeactivate = function () {
                $.ajax({
                    url: activeShareConfig.deactivateUrl,
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        [csrfTokenName]: csrfTokenValue
                    }
                }).done(function (res) {
                    if (res && res.csrf_hash) {
                        csrfTokenValue = res.csrf_hash;
                    }

                    if (res && res.status === 'ok') {
                        $(shareModalEl).modal('hide');
                        dt.ajax.reload(null, false);

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Tautan Dinonaktifkan',
                                text: 'Tautan publik tidak dapat lagi diakses oleh pengguna luar.',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: (res && res.message) ? res.message : 'Gagal menonaktifkan link.' });
                        }
                    }
                }).fail(function (xhr) {
                    const msg = xhr && xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Koneksi bermasalah.';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                    }
                });
            };

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Nonaktifkan Tautan Publik?',
                    text: 'Pengguna luar yang memiliki tautan ini tidak akan bisa melihat galeri foto lagi.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Nonaktifkan',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then(function (result) {
                    if (result.isConfirmed) {
                        doDeactivate();
                    }
                });
            } else if (confirm('Nonaktifkan tautan publik ini?')) {
                doDeactivate();
            }
        });
    }

    // Konfirmasi Hapus Baris Kegiatan
    $(tableEl).on('submit', 'form.inline-form', function (e) {
        if (this.dataset.confirmed === '1') {
            return;
        }

        e.preventDefault();
        const formEl = this;
        const title = formEl.getAttribute('data-confirm-title') || 'Konfirmasi';
        const text = formEl.getAttribute('data-confirm-text') || 'Yakin ingin melanjutkan?';
        const confirmButtonText = formEl.getAttribute('data-confirm-button') || 'Ya, lanjutkan';

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'question',
                title: title,
                text: text,
                showCancelButton: true,
                confirmButtonText: confirmButtonText,
                cancelButtonText: 'Batal',
                reverseButtons: true,
                focusCancel: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    formEl.dataset.confirmed = '1';
                    formEl.submit();
                }
            });
        } else if (confirm(text)) {
            formEl.dataset.confirmed = '1';
            formEl.submit();
        }
    });

    // Galeri Viewer Modal
    const showPhoto = function (index) {
        if (!currentGalleryPhotos.length) return;
        currentGalleryIndex = (index + currentGalleryPhotos.length) % currentGalleryPhotos.length;
        const photo = currentGalleryPhotos[currentGalleryIndex] || {};

        if (galleryModalImage) {
            galleryModalImage.src = photo.path || '';
            galleryModalImage.alt = photo.name || 'Foto kegiatan';
        }

        if (galleryModalCaption) {
            galleryModalCaption.textContent = photo.name || 'Foto kegiatan';
        }

        if (galleryThumbnailsEl) {
            galleryThumbnailsEl.innerHTML = '';
            currentGalleryPhotos.forEach(function (item, thumbIndex) {
                const thumbButton = document.createElement('button');
                thumbButton.type = 'button';
                thumbButton.className = 'gallery-thumb' + (thumbIndex === currentGalleryIndex ? ' active' : '');
                thumbButton.innerHTML = '<img src="' + escapeHtml(item.path || '') + '" alt="' + escapeHtml(item.name || 'Foto') + '">';
                thumbButton.addEventListener('click', function () {
                    showPhoto(thumbIndex);
                });
                galleryThumbnailsEl.appendChild(thumbButton);
            });
        }
    };

    $(tableEl).on('click', '.js-open-gallery', function () {
        let photos = [];
        try {
            photos = JSON.parse(decodeURIComponent(this.getAttribute('data-gallery') || '[]'));
        } catch (e) {
            photos = [];
        }

        currentGalleryPhotos = Array.isArray(photos) ? photos : [];
        currentGalleryIndex = 0;
        if (!currentGalleryPhotos.length) return;

        const title = decodeURIComponent(this.getAttribute('data-title') || '');
        const date = decodeURIComponent(this.getAttribute('data-date') || '');

        galleryModalTitle.textContent = title || 'Foto Kegiatan';
        galleryModalMeta.textContent = (date || '-') + ' · ' + currentGalleryPhotos.length + ' foto';

        showPhoto(0);
        $(galleryModalEl).modal('show');
    });

    if (galleryPrevBtn) {
        galleryPrevBtn.addEventListener('click', function () {
            showPhoto(currentGalleryIndex - 1);
        });
    }

    if (galleryNextBtn) {
        galleryNextBtn.addEventListener('click', function () {
            showPhoto(currentGalleryIndex + 1);
        });
    }

    // Filter Buttons
    if (applyButton) {
        applyButton.addEventListener('click', function (e) {
            e.preventDefault();
            dt.ajax.reload();
        });
    }

    if (resetButton) {
        resetButton.addEventListener('click', function (e) {
            e.preventDefault();
            if (filterTitle) filterTitle.value = '';
            if (filterKategori) filterKategori.value = '';
            if (filterDateFrom) filterDateFrom.value = '';
            if (filterDateTo) filterDateTo.value = '';
            if (filterLocation) filterLocation.value = '';
            dt.ajax.reload();
        });
    }

    if (filterKategori) {
        filterKategori.addEventListener('change', function () {
            dt.ajax.reload();
        });
    }
});
</script>

<style>
    .dataTables_wrapper .dataTables_filter {
        display: none !important;
    }

    /* Thumbnail Foto Kegiatan di Tabel */
    .activity-photo-click-btn {
        cursor: pointer;
        outline: none !important;
        background: transparent;
        border: none;
    }

    .activity-photo-click-btn:focus {
        outline: none !important;
        box-shadow: none !important;
    }

    .activity-table-thumb {
        width: 54px;
        height: 54px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #ced4da;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
    }

    .activity-photo-click-btn:hover .activity-table-thumb {
        transform: scale(1.08);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.18);
        border-color: #007bff;
    }

    .activity-table-thumb-empty {
        width: 54px;
        height: 54px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f8fafc;
        color: #94a3b8;
        border-radius: 8px;
        border: 1px dashed #cbd5e1;
        transition: transform 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }

    .activity-photo-click-btn:hover .activity-table-thumb-empty {
        transform: scale(1.08);
        border-color: #007bff;
        color: #007bff;
    }

    .activity-photo-count-badge {
        font-size: 0.72rem;
        font-weight: 600;
        color: #475569;
        background-color: #f1f5f9;
        border: 1px solid #e2e8f0;
        padding: 2px 8px;
        border-radius: 12px;
        display: inline-block;
        white-space: nowrap;
        transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
    }

    .activity-photo-click-btn:hover .activity-photo-count-badge {
        background-color: #e0f2fe;
        color: #0284c7;
        border-color: #bae6fd;
    }

    .photo-modal-content {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
    }

    .search-filter-box {
        min-width: min(760px, 100%);
    }

    .photo-viewer-shell {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 14px;
    }

    .photo-viewer-main {
        width: 100%;
        max-width: 100%;
        min-height: 360px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(180deg, #f8fafc 0%, #eef4fb 100%);
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid #e5ebf2;
    }

    .photo-viewer-main img {
        max-width: 100%;
        max-height: 70vh;
        object-fit: contain;
        display: block;
    }

    .photo-nav-btn {
        width: 44px;
        height: 44px;
        border: 0;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.8);
        color: #fff;
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.18);
    }

    .photo-nav-btn:hover {
        background: rgba(15, 23, 42, 0.95);
    }

    .photo-caption {
        font-weight: 600;
        color: #1f2937;
    }

    .photo-thumbnails {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding-bottom: 4px;
        justify-content: center;
    }

    .gallery-thumb {
        border: 2px solid transparent;
        background: #fff;
        padding: 0;
        border-radius: 10px;
        overflow: hidden;
        width: 74px;
        height: 74px;
        flex: 0 0 auto;
        opacity: 0.78;
    }

    .gallery-thumb.active {
        border-color: var(--app-primary);
        opacity: 1;
    }

    .gallery-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    /* Modal Dropzone & Cards */
    .photo-dropzone {
        border: 2px dashed #cfd7e3;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        border-radius: 14px;
        padding: 14px;
        transition: border-color .15s ease, background-color .15s ease;
    }

    .photo-dropzone.is-dragover {
        border-color: var(--app-primary);
        background: #eef6ff;
    }

    .photo-dropzone__target {
        border-radius: 10px;
        border: 1px solid #e6ecf3;
        padding: 20px 14px;
        background: #fff;
    }

    #modalSelectedPhotoPreview,
    .existing-photo-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .selected-photo-card {
        width: 110px;
        border: 1px solid #dbe3ee;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
    }

    .selected-photo-card__preview {
        position: relative;
        width: 100%;
        height: 90px;
        background: #f8fafc;
    }

    .selected-photo-card__preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .selected-photo-card__remove {
        position: absolute;
        top: 4px;
        right: 4px;
        width: 22px;
        height: 22px;
        border: 0;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.85);
        color: #fff;
        font-size: 14px;
        line-height: 20px;
        padding: 0;
        cursor: pointer;
    }

    .selected-photo-card__meta {
        padding: 6px 8px;
        font-size: 10px;
        line-height: 1.3;
        color: #475569;
        word-break: break-word;
    }

    .selected-photo-card__name {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .existing-photo-card {
        width: 110px;
        border: 1px solid #dbe3ee;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
        margin: 0;
    }

    .existing-photo-card__wrapper {
        position: relative;
        width: 100%;
        height: 90px;
        background: #f8fafc;
    }

    .existing-photo-card__wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .existing-photo-card__del {
        position: absolute;
        top: 4px;
        right: 4px;
        width: 22px;
        height: 22px;
        border: 0;
        border-radius: 999px;
        background: rgba(220, 53, 69, 0.9);
        color: #fff;
        font-size: 14px;
        line-height: 20px;
        padding: 0;
        cursor: pointer;
    }

    .existing-photo-card figcaption {
        padding: 6px 8px;
        font-size: 10px;
        color: #475569;
        word-break: break-word;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    @media (max-width: 767.98px) {
        .photo-viewer-shell {
            gap: 8px;
        }

        .photo-viewer-main {
            min-height: 240px;
        }

        .photo-nav-btn {
            width: 38px;
            height: 38px;
        }
    }
</style>
<?= $this->endSection(); ?>
