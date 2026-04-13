<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="mb-3 d-flex flex-wrap justify-content-between align-items-center" style="gap:12px;">
    <a class="btn btn-primary" href="<?= site_url('/admin/dokumentasi/kegiatan-lapangan/tambah'); ?>">
        <i class="fas fa-plus mr-1"></i> Tambah Kegiatan
    </a>
</div>

<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap justify-content-between align-items-end" style="gap:12px;">
            <div>
                <h3 class="card-title mb-0">Daftar Kegiatan Lapangan</h3>
                <small class="text-muted">Gunakan filter untuk mempersempit data berdasarkan kolom.</small>
            </div>
            <div class="search-filter-box w-100">
                <form method="get" action="<?= site_url('/admin/dokumentasi/kegiatan-lapangan'); ?>" class="mb-0">
                <div class="form-row align-items-end mb-0">
                    <div class="form-group col-md-4 mb-2 mb-md-0">
                        <label for="serverFilterTitle" class="small text-muted mb-1">Judul</label>
                        <input type="text" class="form-control form-control-sm" id="serverFilterTitle" name="title" value="<?= esc((string) ($filters['title'] ?? '')); ?>" placeholder="Cari judul kegiatan">
                    </div>
                    <div class="form-group col-md-3 mb-2 mb-md-0">
                        <label for="serverFilterDate" class="small text-muted mb-1">Tanggal</label>
                        <input type="date" class="form-control form-control-sm" id="serverFilterDate" name="date" value="<?= esc((string) ($filters['date'] ?? '')); ?>">
                    </div>
                    <div class="form-group col-md-3 mb-2 mb-md-0">
                        <label for="serverFilterLocation" class="small text-muted mb-1">Lokasi</label>
                        <input type="text" class="form-control form-control-sm" id="serverFilterLocation" name="location" value="<?= esc((string) ($filters['location'] ?? '')); ?>" placeholder="Cari lokasi">
                    </div>
                    <div class="form-group col-md-2 mb-2 mb-md-0">
                        <label class="small text-muted mb-1 d-block invisible">Aksi</label>
                        <div class="d-flex align-items-end" style="gap:8px;">
                            <button type="submit" class="btn btn-sm btn-primary w-100" id="applyServerFilters">
                                Terapkan
                            </button>
                            <a class="btn btn-sm btn-outline-secondary w-100" id="resetServerFilters" href="<?= site_url('/admin/dokumentasi/kegiatan-lapangan'); ?>">
                                Reset
                            </a>
                        </div>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover js-kegiatan-lapangan-table w-100" data-order='[[1,"desc"]]'>
                <thead>
                <tr>
                    <th>Judul Kegiatan</th>
                    <th>Tanggal Kegiatan</th>
                    <th>Lokasi Kegiatan</th>
                    <th>Foto Kegiatan</th>
                    <th>Dibuat Oleh</th>
                    <th class="text-center">Download Foto</th>
                    <th class="text-center">Bagikan Foto</th>
                    <th class="text-right">Aksi</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="shareDurationModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bagikan Foto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Berapa lama durasi foto ini akan dibagikan?</p>
                <p class="small text-muted mb-3" id="shareActivityName">-</p>

                <div class="custom-control custom-radio mb-2">
                    <input type="radio" id="shareDuration1Day" name="shareDuration" class="custom-control-input" value="1day">
                    <label class="custom-control-label" for="shareDuration1Day">1 hari</label>
                </div>
                <div class="custom-control custom-radio mb-2">
                    <input type="radio" id="shareDuration1Week" name="shareDuration" class="custom-control-input" value="1week" checked>
                    <label class="custom-control-label" for="shareDuration1Week">1 minggu</label>
                </div>
                <div class="custom-control custom-radio mb-2">
                    <input type="radio" id="shareDuration1Month" name="shareDuration" class="custom-control-input" value="1month">
                    <label class="custom-control-label" for="shareDuration1Month">1 bulan</label>
                </div>
                <div class="custom-control custom-radio">
                    <input type="radio" id="shareDurationPermanent" name="shareDuration" class="custom-control-input" value="permanent">
                    <label class="custom-control-label" for="shareDurationPermanent">Permanen</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnGenerateShareLink">
                    Buat Link Bagikan
                </button>
            </div>
        </div>
    </div>
</div>

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
    const filterDate = document.getElementById('serverFilterDate');
    const filterLocation = document.getElementById('serverFilterLocation');
    const applyButton = document.getElementById('applyServerFilters');
    const resetButton = document.getElementById('resetServerFilters');
    const shareModalEl = document.getElementById('shareDurationModal');
    const shareActivityName = document.getElementById('shareActivityName');
    const btnGenerateShareLink = document.getElementById('btnGenerateShareLink');

    const modalEl = document.getElementById('activityPhotoModal');
    const modalTitle = document.getElementById('activityPhotoModalTitle');
    const modalMeta = document.getElementById('activityPhotoModalMeta');
    const modalImage = document.getElementById('activityPhotoModalImage');
    const modalCaption = document.getElementById('activityPhotoModalCaption');
    const thumbnailsEl = document.getElementById('activityPhotoThumbnails');
    const prevBtn = document.getElementById('photoPrevBtn');
    const nextBtn = document.getElementById('photoNextBtn');

    let currentPhotos = [];
    let currentIndex = 0;
    let isFilterLoading = false;

    const csrfTokenName = <?= json_encode(csrf_token(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    let csrfTokenValue = <?= json_encode(csrf_hash(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    const dataUrl = <?= json_encode(site_url('/admin/dokumentasi/kegiatan-lapangan/data'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    let selectedShareConfig = {
        title: '',
        shareUrl: '',
    };

    const escapeHtml = function (value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    };

    const showFilterLoading = function () {
        if (typeof Swal === 'undefined') {
            return;
        }

        Swal.fire({
            title: 'Memuat data...',
            html: 'Menerapkan filter',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: function () {
                Swal.showLoading();
            }
        });
    };

    const hideFilterLoading = function () {
        if (typeof Swal === 'undefined') {
            return;
        }

        if (Swal.isVisible()) {
            Swal.close();
        }
    };

    const reloadWithFilterLoading = function () {
        isFilterLoading = true;
        showFilterLoading();
        dt.ajax.reload();
    };

    const showPhoto = function (index) {
        if (!currentPhotos.length) {
            return;
        }

        currentIndex = (index + currentPhotos.length) % currentPhotos.length;
        const photo = currentPhotos[currentIndex] || {};

        if (modalImage) {
            modalImage.src = photo.path || '';
            modalImage.alt = photo.name || 'Foto kegiatan';
        }

        if (modalCaption) {
            modalCaption.textContent = photo.name || 'Foto kegiatan';
        }

        if (thumbnailsEl) {
            thumbnailsEl.innerHTML = '';
            currentPhotos.forEach(function (item, thumbIndex) {
                const thumbButton = document.createElement('button');
                thumbButton.type = 'button';
                thumbButton.className = 'gallery-thumb' + (thumbIndex === currentIndex ? ' active' : '');
                thumbButton.innerHTML = '<img src="' + escapeHtml(item.path || '') + '" alt="' + escapeHtml(item.name || 'Foto kegiatan') + '">';
                thumbButton.addEventListener('click', function () {
                    showPhoto(thumbIndex);
                });
                thumbnailsEl.appendChild(thumbButton);
            });
        }
    };

    const openGallery = function (activityTitle, activityDate, photos) {
        currentPhotos = Array.isArray(photos) ? photos : [];
        currentIndex = 0;

        if (!currentPhotos.length) {
            return;
        }

        if (modalTitle) {
            modalTitle.textContent = activityTitle || 'Foto Kegiatan';
        }

        if (modalMeta) {
            modalMeta.textContent = (activityDate || '-') + ' · ' + currentPhotos.length + ' foto';
        }

        showPhoto(0);

        if (typeof $.fn.modal === 'function') {
            $(modalEl).modal('show');
        }
    };

    if (prevBtn) {
        prevBtn.addEventListener('click', function () {
            showPhoto(currentIndex - 1);
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function () {
            showPhoto(currentIndex + 1);
        });
    }

    if (!tableEl) {
        return;
    }

    const dt = $(tableEl).DataTable({
        processing: true,
        serverSide: true,
        responsive: false,
        autoWidth: false,
        scrollX: true,
        scrollCollapse: true,
        searching: false,
        order: [[1, 'desc']],
        ajax: {
            url: dataUrl,
            type: 'GET',
            data: function (d) {
                d.title = filterTitle ? filterTitle.value : '';
                d.date = filterDate ? filterDate.value : '';
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
                render: function (row) {
                    const photos = Array.isArray(row.photos) ? row.photos : [];
                    const coverPhoto = row.cover_photo || '';
                    const title = row.title || '-';
                    const activityDate = row.activity_date || '-';
                    const photosJson = encodeURIComponent(JSON.stringify(photos));

                    let thumbHtml = '';
                    if (coverPhoto !== '') {
                        thumbHtml = '<img src="' + escapeHtml(coverPhoto) + '" alt="Foto kegiatan" style="width:54px;height:54px;object-fit:cover;border-radius:10px;border:1px solid #dee2e6;">';
                    } else {
                        thumbHtml = '<div class="d-flex align-items-center justify-content-center bg-light text-muted" style="width:54px;height:54px;border-radius:10px;border:1px solid #dee2e6;"><i class="far fa-image"></i></div>';
                    }

                    return ''
                        + '<button type="button" class="btn btn-link p-0 text-left js-open-gallery"'
                        + ' data-gallery="' + photosJson + '"'
                        + ' data-title="' + encodeURIComponent(title) + '"'
                        + ' data-date="' + encodeURIComponent(activityDate) + '">'
                        + '<div class="d-flex align-items-center" style="gap:10px;">'
                        + thumbHtml
                        + '<div>'
                        + '<div class="font-weight-bold">' + Number(row.photo_count || 0) + ' foto</div>'
                        + '</div>'
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
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function (row) {
                    const zipUrl = row.download_zip_url || '#';
                    const hasPhotos = Number(row.photo_count || 0) > 0;

                    if (!hasPhotos) {
                        return '<button type="button" class="btn btn-sm btn-secondary" disabled>Tidak ada foto</button>';
                    }

                    return ''
                        + '<a class="btn btn-sm btn-info" href="' + escapeHtml(zipUrl) + '">'
                        + '<i class="fas fa-file-archive mr-1"></i> ZIP'
                        + '</a>';
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function (row) {
                    const shareCreateUrl = row.share_create_url || '#';
                    const title = row.title || '-';

                    return ''
                        + '<button type="button" class="btn btn-sm btn-success js-share-photo"'
                        + ' data-share-url="' + escapeHtml(shareCreateUrl) + '"'
                        + ' data-share-title="' + escapeHtml(encodeURIComponent(title)) + '">'
                        + '<i class="fas fa-share-alt mr-1"></i>'
                        + '</button>';
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-right',
                render: function (row) {
                    const editUrl = row.edit_url || '#';
                    const deleteUrl = row.delete_url || '#';

                    return ''
                        + '<a class="btn btn-sm btn-warning" href="' + escapeHtml(editUrl) + '">Ubah</a> '
                        + '<form class="inline-form" method="post" action="' + escapeHtml(deleteUrl) + '"'
                        + ' data-confirm-title="Hapus Kegiatan"'
                        + ' data-confirm-text="Hapus kegiatan lapangan ini beserta seluruh fotonya?"'
                        + ' data-confirm-button="Ya, hapus">'
                        + '<input type="hidden" name="' + escapeHtml(csrfTokenName) + '" value="' + escapeHtml(csrfTokenValue) + '">'
                        + '<button type="submit" class="btn btn-sm btn-danger">Hapus</button>'
                        + '</form>';
                }
            }
        ],
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

    $(tableEl).on('draw.dt', function () {
        if (!isFilterLoading) {
            return;
        }

        isFilterLoading = false;
        hideFilterLoading();
    });

    $(tableEl).on('click', '.js-open-gallery', function () {
        let photos = [];
        try {
            photos = JSON.parse(decodeURIComponent(this.getAttribute('data-gallery') || '[]'));
        } catch (error) {
            photos = [];
        }

        openGallery(
            decodeURIComponent(this.getAttribute('data-title') || ''),
            decodeURIComponent(this.getAttribute('data-date') || ''),
            photos
        );
    });

    $(tableEl).on('click', '.js-share-photo', function () {
        selectedShareConfig = {
            title: decodeURIComponent(this.getAttribute('data-share-title') || ''),
            shareUrl: this.getAttribute('data-share-url') || '',
        };

        if (shareActivityName) {
            shareActivityName.textContent = selectedShareConfig.title || '-';
        }

        if (typeof $.fn.modal === 'function') {
            $(shareModalEl).modal('show');
        }
    });

    if (btnGenerateShareLink) {
        btnGenerateShareLink.addEventListener('click', function () {
            if (!selectedShareConfig.shareUrl) {
                return;
            }

            const selectedDurationInput = document.querySelector('input[name="shareDuration"]:checked');
            const duration = selectedDurationInput ? selectedDurationInput.value : '1week';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Membuat link...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: function () {
                        Swal.showLoading();
                    }
                });
            }

            $.ajax({
                url: selectedShareConfig.shareUrl,
                method: 'POST',
                dataType: 'json',
                data: {
                    duration: duration,
                    [csrfTokenName]: csrfTokenValue,
                },
            }).done(function (response) {
                if (response && response.csrf_hash) {
                    csrfTokenValue = response.csrf_hash;
                }

                if (typeof $.fn.modal === 'function') {
                    $(shareModalEl).modal('hide');
                }

                const shareUrl = response && response.share_url ? response.share_url : '';

                if (typeof Swal === 'undefined') {
                    window.prompt('Salin link berikut:', shareUrl);
                    return;
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Link Berhasil Dibuat',
                    html: '<div class="text-left"><small class="text-muted">Bagikan tautan berikut:</small><input id="shareLinkInput" class="form-control mt-2" value="' + escapeHtml(shareUrl) + '" readonly></div>',
                    showCancelButton: true,
                    confirmButtonText: 'Salin Link',
                    cancelButtonText: 'Tutup',
                    preConfirm: function () {
                        const input = document.getElementById('shareLinkInput');
                        if (!input) {
                            return;
                        }

                        input.select();
                        input.setSelectionRange(0, 99999);

                        if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
                            return navigator.clipboard.writeText(input.value);
                        }

                        document.execCommand('copy');
                    }
                });
            }).fail(function (xhr) {
                const message = xhr && xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Gagal membuat link berbagi.';

                if (typeof Swal === 'undefined') {
                    window.alert(message);
                    return;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: message,
                });
            });
        });
    }

    $(tableEl).on('submit', 'form.inline-form', function (event) {
        if (this.dataset.confirmed === '1') {
            return;
        }

        event.preventDefault();

        const formEl = this;
        const title = formEl.getAttribute('data-confirm-title') || 'Konfirmasi';
        const text = formEl.getAttribute('data-confirm-text') || 'Yakin ingin melanjutkan?';
        const confirmButtonText = formEl.getAttribute('data-confirm-button') || 'Ya, lanjutkan';

        if (typeof Swal === 'undefined') {
            if (window.confirm(text)) {
                formEl.dataset.confirmed = '1';
                formEl.submit();
            }
            return;
        }

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
            if (!result.isConfirmed) {
                return;
            }

            formEl.dataset.confirmed = '1';
            formEl.submit();
        });
    });

    if (applyButton) {
        applyButton.addEventListener('click', function (event) {
            event.preventDefault();
            reloadWithFilterLoading();
        });
    }

    if (resetButton) {
        resetButton.addEventListener('click', function (event) {
            event.preventDefault();
            if (filterTitle) {
                filterTitle.value = '';
            }
            if (filterDate) {
                filterDate.value = '';
            }
            if (filterLocation) {
                filterLocation.value = '';
            }
            reloadWithFilterLoading();
        });
    }

    if (filterTitle) {
        filterTitle.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                reloadWithFilterLoading();
            }
        });
    }

    if (filterLocation) {
        filterLocation.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                reloadWithFilterLoading();
            }
        });
    }

    if (filterDate) {
        filterDate.addEventListener('change', function () {
            reloadWithFilterLoading();
        });
    }
});
</script>

<style>
    .dataTables_wrapper .dataTables_filter {
        display: none !important;
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