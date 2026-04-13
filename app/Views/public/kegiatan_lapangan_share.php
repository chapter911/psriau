<?= $this->extend('layouts/public'); ?>

<?= $this->section('content'); ?>
<section class="shared-gallery-page">
    <div class="container py-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3" style="gap:12px;">
            <div>
                <h2 class="mb-1">Galeri Kegiatan Lapangan</h2>
                <p class="text-muted mb-0">
                    <?= esc((string) ($activity['title'] ?? '-')); ?>
                    <?php if (! empty($activity['activity_date'])): ?>
                        · <?= esc((string) $activity['activity_date']); ?>
                    <?php endif; ?>
                    <?php if (! empty($activity['location'])): ?>
                        · <?= esc((string) $activity['location']); ?>
                    <?php endif; ?>
                </p>
            </div>
            <div class="d-flex flex-wrap ml-auto justify-content-end gallery-toolbar" style="gap:8px;">
                <a class="btn btn-primary gallery-toolbar-btn" href="<?= site_url('/kegiatan-lapangan/share/' . $shareToken . '/download-zip'); ?>">
                    <i class="fas fa-file-archive mr-1"></i> Download Semua (ZIP)
                </a>
                <button type="button" class="btn btn-outline-secondary gallery-toolbar-btn gallery-toolbar-icon-btn" id="btnGridView" title="Tampilan kotak" aria-label="Tampilan kotak">
                    <i class="fas fa-th"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary gallery-toolbar-btn gallery-toolbar-icon-btn" id="btnListView" title="Tampilan list" aria-label="Tampilan list">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        </div>

        <?php if (! empty($expiresAt)): ?>
            <div class="alert alert-warning py-2 px-3">
                Tautan ini berlaku sampai: <strong><?= esc((string) $expiresAt); ?></strong>
            </div>
        <?php endif; ?>

        <div id="photoGallery" class="gallery gallery-grid">
            <?php foreach ($photos as $index => $photo): ?>
                <?php
                    $photoId = (int) ($photo['id'] ?? 0);
                    $photoPath = (string) ($photo['photo_path'] ?? '');
                    $photoName = (string) ($photo['photo_name'] ?? ('Foto ' . ($index + 1)));
                    $downloadUrl = site_url('/kegiatan-lapangan/share/' . $shareToken . '/download-photo/' . $photoId);
                ?>
                <article class="gallery-item">
                    <button
                        type="button"
                        class="gallery-photo-btn"
                        data-photo-src="<?= esc($photoPath); ?>"
                        data-photo-name="<?= esc($photoName); ?>"
                        data-photo-download="<?= esc($downloadUrl); ?>"
                    >
                        <img src="<?= esc($photoPath); ?>" alt="<?= esc($photoName); ?>" loading="lazy">
                    </button>
                    <div class="gallery-meta">
                        <div class="gallery-name" title="<?= esc($photoName); ?>"><?= esc($photoName); ?></div>
                        <a class="btn btn-sm btn-outline-primary" href="<?= esc($downloadUrl); ?>">
                            <i class="fas fa-download mr-1"></i> Download
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<div class="share-lightbox" id="shareLightbox" aria-hidden="true">
    <button type="button" class="lightbox-close" id="btnCloseLightbox" aria-label="Tutup">&times;</button>
    <div class="lightbox-content">
        <img id="lightboxImage" src="" alt="Foto kegiatan">
        <div class="lightbox-bar">
            <div class="lightbox-name" id="lightboxName">Foto kegiatan</div>
            <a class="btn btn-sm btn-primary" id="lightboxDownload" href="#">
                <i class="fas fa-download mr-1"></i> Download Foto Ini
            </a>
        </div>
    </div>
</div>

<style>
    .shared-gallery-page {
        background: linear-gradient(180deg, #f7fafc 0%, #eef3f8 100%);
        min-height: 70vh;
    }

    .gallery {
        display: grid;
        gap: 14px;
    }

    .gallery-toolbar-btn {
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .gallery-toolbar-icon-btn {
        width: 42px;
        padding: 0;
    }

    .gallery-grid {
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    }

    .gallery-list {
        grid-template-columns: 1fr;
    }

    .gallery-item {
        background: #fff;
        border: 1px solid #e3e8ef;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
    }

    .gallery-photo-btn {
        width: 100%;
        border: 0;
        padding: 0;
        display: block;
        background: #f8fafc;
        cursor: zoom-in;
    }

    .gallery-photo-btn img {
        width: 100%;
        height: 220px;
        object-fit: cover;
        display: block;
    }

    .gallery-list .gallery-item {
        display: grid;
        grid-template-columns: minmax(200px, 260px) 1fr;
    }

    .gallery-list .gallery-photo-btn img {
        height: 160px;
    }

    .gallery-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 12px;
    }

    .gallery-name {
        font-weight: 600;
        color: #1f2937;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .share-lightbox {
        position: fixed;
        inset: 0;
        z-index: 3000;
        background: rgba(0, 0, 0, 0.88);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 24px;
    }

    .share-lightbox.is-open {
        display: flex;
    }

    .lightbox-content {
        width: min(980px, 96vw);
    }

    .lightbox-content img {
        width: 100%;
        max-height: 75vh;
        object-fit: contain;
        display: block;
        border-radius: 10px;
    }

    .lightbox-bar {
        margin-top: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        color: #fff;
    }

    .lightbox-name {
        font-weight: 600;
    }

    .lightbox-close {
        position: absolute;
        top: 12px;
        right: 18px;
        border: 0;
        background: transparent;
        color: #fff;
        font-size: 34px;
        line-height: 1;
        cursor: pointer;
    }

    @media (max-width: 767.98px) {
        .gallery-list .gallery-item {
            grid-template-columns: 1fr;
        }

        .gallery-list .gallery-photo-btn img,
        .gallery-grid .gallery-photo-btn img {
            height: 200px;
        }

        .lightbox-bar {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const galleryEl = document.getElementById('photoGallery');
    const btnGrid = document.getElementById('btnGridView');
    const btnList = document.getElementById('btnListView');

    const lightbox = document.getElementById('shareLightbox');
    const lightboxImage = document.getElementById('lightboxImage');
    const lightboxName = document.getElementById('lightboxName');
    const lightboxDownload = document.getElementById('lightboxDownload');
    const closeLightbox = document.getElementById('btnCloseLightbox');

    const setView = function (viewType) {
        if (!galleryEl) {
            return;
        }

        galleryEl.classList.remove('gallery-grid', 'gallery-list');
        if (viewType === 'list') {
            galleryEl.classList.add('gallery-list');
            btnList && btnList.classList.add('btn-primary');
            btnList && btnList.classList.remove('btn-outline-secondary');
            btnGrid && btnGrid.classList.add('btn-outline-secondary');
            btnGrid && btnGrid.classList.remove('btn-primary');
            return;
        }

        galleryEl.classList.add('gallery-grid');
        btnGrid && btnGrid.classList.add('btn-primary');
        btnGrid && btnGrid.classList.remove('btn-outline-secondary');
        btnList && btnList.classList.add('btn-outline-secondary');
        btnList && btnList.classList.remove('btn-primary');
    };

    const openLightbox = function (src, name, downloadUrl) {
        if (!lightbox || !lightboxImage || !lightboxName || !lightboxDownload) {
            return;
        }

        lightboxImage.src = src || '';
        lightboxName.textContent = name || 'Foto kegiatan';
        lightboxDownload.href = downloadUrl || '#';
        lightbox.classList.add('is-open');
        lightbox.setAttribute('aria-hidden', 'false');
    };

    const closeBox = function () {
        if (!lightbox) {
            return;
        }

        lightbox.classList.remove('is-open');
        lightbox.setAttribute('aria-hidden', 'true');
    };

    btnGrid && btnGrid.addEventListener('click', function () {
        setView('grid');
    });

    btnList && btnList.addEventListener('click', function () {
        setView('list');
    });

    if (galleryEl) {
        galleryEl.addEventListener('click', function (event) {
            const btn = event.target.closest('.gallery-photo-btn');
            if (!btn) {
                return;
            }

            openLightbox(
                btn.getAttribute('data-photo-src') || '',
                btn.getAttribute('data-photo-name') || 'Foto kegiatan',
                btn.getAttribute('data-photo-download') || '#'
            );
        });
    }

    closeLightbox && closeLightbox.addEventListener('click', closeBox);

    if (lightbox) {
        lightbox.addEventListener('click', function (event) {
            if (event.target === lightbox) {
                closeBox();
            }
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeBox();
        }
    });

    setView('grid');
});
</script>
<?= $this->endSection(); ?>
