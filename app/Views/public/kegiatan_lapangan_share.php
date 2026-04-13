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
                        data-photo-index="<?= (int) $index; ?>"
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
        <div class="lightbox-image-shell">
            <button type="button" class="lightbox-nav-btn lightbox-nav-btn-left" id="btnLightboxPrev" aria-label="Foto sebelumnya">
                <i class="fas fa-chevron-left"></i>
            </button>
            <img id="lightboxImage" src="" alt="Foto kegiatan">
            <button type="button" class="lightbox-nav-btn lightbox-nav-btn-right" id="btnLightboxNext" aria-label="Foto berikutnya">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        <div class="lightbox-thumbnails-wrap">
            <div class="lightbox-thumbnails" id="lightboxThumbnails"></div>
        </div>
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

    .lightbox-image-shell {
        position: relative;
        border-radius: 10px;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.04);
    }

    .lightbox-content img {
        width: 100%;
        max-height: 75vh;
        object-fit: contain;
        display: block;
        border-radius: 10px;
        opacity: 1;
        transition: opacity 0.22s ease;
    }

    .lightbox-content img.is-loading {
        opacity: 0.15;
    }

    .lightbox-nav-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 42px;
        height: 42px;
        border: 0;
        border-radius: 999px;
        background: rgba(0, 0, 0, 0.68);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
        cursor: pointer;
    }

    .lightbox-nav-btn:hover {
        background: rgba(0, 0, 0, 0.88);
    }

    .lightbox-nav-btn-left {
        left: 10px;
    }

    .lightbox-nav-btn-right {
        right: 10px;
    }

    .lightbox-thumbnails-wrap {
        margin-top: 10px;
        overflow-x: auto;
        overflow-y: hidden;
        display: flex;
        justify-content: center;
        padding-bottom: 4px;
        scrollbar-width: thin;
    }

    .lightbox-thumbnails {
        display: flex;
        gap: 8px;
        width: max-content;
        margin: 0 auto;
    }

    .lightbox-thumb {
        width: 64px;
        height: 64px;
        border: 2px solid transparent;
        border-radius: 8px;
        padding: 0;
        overflow: hidden;
        background: #fff;
        opacity: 0.8;
        cursor: pointer;
        flex: 0 0 auto;
    }

    .lightbox-thumb.is-active {
        border-color: #0d6efd;
        opacity: 1;
    }

    .lightbox-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
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
        .gallery-grid .gallery-photo-btn img {
            height: 200px;
        }

        .lightbox-bar {
            flex-direction: column;
            align-items: flex-start;
        }

        .lightbox-nav-btn {
            width: 36px;
            height: 36px;
        }

        .lightbox-thumb {
            width: 50px;
            height: 50px;
        }

        .lightbox-thumbnails {
            gap: 6px;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const galleryEl = document.getElementById('photoGallery');

    const lightbox = document.getElementById('shareLightbox');
    const lightboxImage = document.getElementById('lightboxImage');
    const lightboxName = document.getElementById('lightboxName');
    const lightboxDownload = document.getElementById('lightboxDownload');
    const lightboxThumbnails = document.getElementById('lightboxThumbnails');
    const lightboxImageShell = lightbox ? lightbox.querySelector('.lightbox-image-shell') : null;
    const btnLightboxPrev = document.getElementById('btnLightboxPrev');
    const btnLightboxNext = document.getElementById('btnLightboxNext');
    const closeLightbox = document.getElementById('btnCloseLightbox');
    const photoButtons = galleryEl ? Array.from(galleryEl.querySelectorAll('.gallery-photo-btn')) : [];
    let currentPhotoIndex = 0;
    let touchStartX = 0;
    let touchEndX = 0;
    const minSwipeDistance = 45;

    const setView = function () {
        if (!galleryEl) {
            return;
        }

        galleryEl.classList.remove('gallery-list');
        galleryEl.classList.add('gallery-grid');
    };

    const renderLightboxThumbnails = function () {
        if (!lightboxThumbnails) {
            return;
        }

        lightboxThumbnails.innerHTML = '';

        photoButtons.forEach(function (button, index) {
            const thumb = document.createElement('button');
            thumb.type = 'button';
            thumb.className = 'lightbox-thumb' + (index === currentPhotoIndex ? ' is-active' : '');
            thumb.innerHTML = '<img src="' + (button.getAttribute('data-photo-src') || '') + '" alt="Thumbnail">';
            thumb.addEventListener('click', function () {
                showPhotoAt(index);
            });
            lightboxThumbnails.appendChild(thumb);
        });

        const activeThumb = lightboxThumbnails.querySelector('.lightbox-thumb.is-active');
        if (activeThumb && typeof activeThumb.scrollIntoView === 'function') {
            activeThumb.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
        }
    };

    const showPhotoAt = function (index) {
        if (!photoButtons.length) {
            return;
        }

        if (index < 0) {
            currentPhotoIndex = photoButtons.length - 1;
        } else if (index >= photoButtons.length) {
            currentPhotoIndex = 0;
        } else {
            currentPhotoIndex = index;
        }

        const activeButton = photoButtons[currentPhotoIndex];
        if (!activeButton || !lightboxImage || !lightboxName || !lightboxDownload) {
            return;
        }

        lightboxImage.classList.add('is-loading');
        lightboxImage.src = activeButton.getAttribute('data-photo-src') || '';
        lightboxName.textContent = activeButton.getAttribute('data-photo-name') || 'Foto kegiatan';
        lightboxDownload.href = activeButton.getAttribute('data-photo-download') || '#';

        if (btnLightboxPrev) {
            btnLightboxPrev.style.display = photoButtons.length > 1 ? 'inline-flex' : 'none';
        }

        if (btnLightboxNext) {
            btnLightboxNext.style.display = photoButtons.length > 1 ? 'inline-flex' : 'none';
        }

        renderLightboxThumbnails();
    };

    const openLightbox = function (index) {
        if (!lightbox || !lightboxImage || !lightboxName || !lightboxDownload) {
            return;
        }

        showPhotoAt(index);
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

    if (galleryEl) {
        galleryEl.addEventListener('click', function (event) {
            const btn = event.target.closest('.gallery-photo-btn');
            if (!btn) {
                return;
            }

            const index = parseInt(btn.getAttribute('data-photo-index') || '0', 10);
            openLightbox(Number.isNaN(index) ? 0 : index);
        });
    }

    btnLightboxPrev && btnLightboxPrev.addEventListener('click', function () {
        showPhotoAt(currentPhotoIndex - 1);
    });

    btnLightboxNext && btnLightboxNext.addEventListener('click', function () {
        showPhotoAt(currentPhotoIndex + 1);
    });

    lightboxImage && lightboxImage.addEventListener('load', function () {
        lightboxImage.classList.remove('is-loading');
    });

    if (lightboxImageShell) {
        lightboxImageShell.addEventListener('touchstart', function (event) {
            if (!event.touches || !event.touches.length) {
                return;
            }

            touchStartX = event.touches[0].clientX;
            touchEndX = touchStartX;
        }, { passive: true });

        lightboxImageShell.addEventListener('touchmove', function (event) {
            if (!event.touches || !event.touches.length) {
                return;
            }

            touchEndX = event.touches[0].clientX;
        }, { passive: true });

        lightboxImageShell.addEventListener('touchend', function () {
            const deltaX = touchEndX - touchStartX;
            if (Math.abs(deltaX) < minSwipeDistance) {
                return;
            }

            if (deltaX < 0) {
                showPhotoAt(currentPhotoIndex + 1);
                return;
            }

            showPhotoAt(currentPhotoIndex - 1);
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
        if (lightbox && lightbox.classList.contains('is-open')) {
            if (event.key === 'ArrowLeft') {
                showPhotoAt(currentPhotoIndex - 1);
                return;
            }

            if (event.key === 'ArrowRight') {
                showPhotoAt(currentPhotoIndex + 1);
                return;
            }
        }

        if (event.key === 'Escape') {
            closeBox();
        }
    });

    setView();
});
</script>
<?= $this->endSection(); ?>
