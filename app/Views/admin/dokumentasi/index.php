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
                <div class="form-row align-items-end mb-0">
                    <div class="form-group col-md-4 mb-2 mb-md-0">
                        <label for="filterTitle" class="small text-muted mb-1">Judul</label>
                        <input type="text" class="form-control form-control-sm" id="filterTitle" placeholder="Cari judul kegiatan">
                    </div>
                    <div class="form-group col-md-3 mb-2 mb-md-0">
                        <label for="filterDate" class="small text-muted mb-1">Tanggal</label>
                        <input type="date" class="form-control form-control-sm" id="filterDate">
                    </div>
                    <div class="form-group col-md-3 mb-2 mb-md-0">
                        <label for="filterLocation" class="small text-muted mb-1">Lokasi</label>
                        <input type="text" class="form-control form-control-sm" id="filterLocation" placeholder="Cari lokasi">
                    </div>
                    <div class="form-group col-md-2 mb-0 text-md-right">
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="resetFilters">
                            Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover js-datatable w-100" data-order='[[1,"desc"]]'>
                <thead>
                <tr>
                    <th>Judul Kegiatan</th>
                    <th>Tanggal Kegiatan</th>
                    <th>Lokasi Kegiatan</th>
                    <th>Foto Kegiatan</th>
                    <th>Dibuat Oleh</th>
                    <th class="text-right">Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($activities as $activity): ?>
                    <?php $coverPhoto = (string) ($activity['cover_photo'] ?? ''); ?>
                    <?php $galleryPhotos = array_map(static function (array $photo): array {
                        return [
                            'path' => (string) ($photo['photo_path'] ?? ''),
                            'name' => (string) ($photo['photo_name'] ?? 'Foto kegiatan'),
                        ];
                    }, $activity['photos'] ?? []); ?>
                    <tr>
                        <td><?= esc((string) ($activity['title'] ?? '-')); ?></td>
                        <td><?= esc((string) ($activity['activity_date'] ?? '-')); ?></td>
                        <td><?= esc((string) ($activity['location'] ?? '-')); ?></td>
                        <td>
                            <button
                                type="button"
                                class="btn btn-link p-0 text-left"
                                data-photo-gallery='<?= esc(json_encode($galleryPhotos, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)); ?>'
                                data-activity-title="<?= esc((string) ($activity['title'] ?? '-')); ?>"
                                data-activity-date="<?= esc((string) ($activity['activity_date'] ?? '-')); ?>"
                            >
                                <div class="d-flex align-items-center" style="gap:10px;">
                                    <?php if ($coverPhoto !== ''): ?>
                                        <img src="<?= esc($coverPhoto); ?>" alt="Foto kegiatan" style="width:54px;height:54px;object-fit:cover;border-radius:10px;border:1px solid #dee2e6;">
                                    <?php else: ?>
                                        <div class="d-flex align-items-center justify-content-center bg-light text-muted" style="width:54px;height:54px;border-radius:10px;border:1px solid #dee2e6;">
                                            <i class="far fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="font-weight-bold"><?= (int) ($activity['photo_count'] ?? 0); ?> foto</div>
                                        <small class="text-muted">Klik untuk lihat galeri</small>
                                    </div>
                                </div>
                            </button>
                        </td>
                        <td><?= esc((string) ($activity['created_by'] ?? '-')); ?></td>
                        <td class="text-right">
                            <a class="btn btn-sm btn-warning" href="<?= site_url('/admin/dokumentasi/kegiatan-lapangan/' . $activity['id'] . '/ubah'); ?>">Ubah</a>
                            <form
                                class="inline-form"
                                method="post"
                                action="<?= site_url('/admin/dokumentasi/kegiatan-lapangan/' . $activity['id'] . '/hapus'); ?>"
                                data-confirm-title="Hapus Kegiatan"
                                data-confirm-text="Hapus kegiatan lapangan ini beserta seluruh fotonya?"
                                data-confirm-button="Ya, hapus"
                            >
                                <?= csrf_field(); ?>
                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
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
document.addEventListener('DOMContentLoaded', () => {
    const modalEl = document.getElementById('activityPhotoModal');
    const modalTitle = document.getElementById('activityPhotoModalTitle');
    const modalMeta = document.getElementById('activityPhotoModalMeta');
    const modalImage = document.getElementById('activityPhotoModalImage');
    const modalCaption = document.getElementById('activityPhotoModalCaption');
    const thumbnailsEl = document.getElementById('activityPhotoThumbnails');
    const prevBtn = document.getElementById('photoPrevBtn');
    const nextBtn = document.getElementById('photoNextBtn');
    const filterTitle = document.getElementById('filterTitle');
    const filterDate = document.getElementById('filterDate');
    const filterLocation = document.getElementById('filterLocation');
    const resetFiltersBtn = document.getElementById('resetFilters');
    const photoCards = document.querySelectorAll('[data-photo-gallery]');
    let currentPhotos = [];
    let currentIndex = 0;
    const tableEl = document.querySelector('.js-datatable');
    const dataTable = window.jQuery && tableEl && window.jQuery.fn.DataTable && window.jQuery.fn.DataTable.isDataTable(tableEl)
        ? window.jQuery(tableEl).DataTable()
        : null;

    const showPhoto = (index) => {
        if (!currentPhotos.length) {
            return;
        }

        currentIndex = (index + currentPhotos.length) % currentPhotos.length;
        const photo = currentPhotos[currentIndex];

        if (modalImage) {
            modalImage.src = photo.path;
            modalImage.alt = photo.name || 'Foto kegiatan';
        }

        if (modalCaption) {
            modalCaption.textContent = photo.name || 'Foto kegiatan';
        }

        if (thumbnailsEl) {
            thumbnailsEl.innerHTML = '';
            currentPhotos.forEach((item, thumbIndex) => {
                const thumbButton = document.createElement('button');
                thumbButton.type = 'button';
                thumbButton.className = 'gallery-thumb' + (thumbIndex === currentIndex ? ' active' : '');
                thumbButton.innerHTML = `<img src="${item.path}" alt="${item.name || 'Foto kegiatan'}">`;
                thumbButton.addEventListener('click', () => showPhoto(thumbIndex));
                thumbnailsEl.appendChild(thumbButton);
            });
        }
    };

    const openGallery = (activityTitle, activityDate, photos) => {
        currentPhotos = Array.isArray(photos) ? photos : [];
        currentIndex = 0;

        if (!currentPhotos.length) {
            return;
        }

        if (modalTitle) {
            modalTitle.textContent = activityTitle || 'Foto Kegiatan';
        }

        if (modalMeta) {
            const dateLabel = activityDate || '-';
            modalMeta.textContent = `${dateLabel} · ${currentPhotos.length} foto`;
        }

        showPhoto(0);

        if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
            window.jQuery(modalEl).modal('show');
        }
    };

    prevBtn?.addEventListener('click', () => showPhoto(currentIndex - 1));
    nextBtn?.addEventListener('click', () => showPhoto(currentIndex + 1));

    photoCards.forEach((card) => {
        card.addEventListener('click', () => {
            const photos = JSON.parse(card.getAttribute('data-photo-gallery') || '[]');
            openGallery(card.getAttribute('data-activity-title') || '', card.getAttribute('data-activity-date') || '', photos);
        });
    });

    const applyFilters = () => {
        if (dataTable) {
            dataTable.column(0).search(filterTitle?.value || '');
            dataTable.column(1).search(filterDate?.value || '');
            dataTable.column(2).search(filterLocation?.value || '');
            dataTable.draw();
        }
    };

    filterTitle?.addEventListener('input', applyFilters);
    filterDate?.addEventListener('change', applyFilters);
    filterLocation?.addEventListener('input', applyFilters);

    resetFiltersBtn?.addEventListener('click', () => {
        if (filterTitle) {
            filterTitle.value = '';
        }
        if (filterDate) {
            filterDate.value = '';
        }
        if (filterLocation) {
            filterLocation.value = '';
        }
        applyFilters();
    });
});
</script>

<style>
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