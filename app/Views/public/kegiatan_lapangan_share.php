<?= $this->extend('layouts/public'); ?>

<?= $this->section('content'); ?>
<section class="shared-gallery-page">
    <div class="container py-4">
        <div class="d-flex flex-wrap align-items-start justify-content-between mb-4" style="gap:12px;">
            <div class="gallery-page-heading">
                <div class="gallery-page-kicker">Dokumentasi Resmi</div>
                <h2 class="mb-1 gallery-page-title">Galeri Kegiatan Lapangan</h2>
                <p class="mb-0 gallery-page-subtitle">
                    <?= esc((string) ($activity['title'] ?? '-')); ?>
                    <?php if (! empty($activity['activity_date'])): ?>
                        · <?= esc((string) $activity['activity_date']); ?>
                    <?php endif; ?>
                    <?php if (! empty($activity['location'])): ?>
                        · <?= esc((string) $activity['location']); ?>
                    <?php endif; ?>
                </p>
                <div class="gallery-page-stats">
                    <span class="gallery-page-pill"><?= (int) count($photos); ?> foto</span>
                    <?php if (! empty($expiresAt)): ?>
                        <span class="gallery-page-pill gallery-page-pill--soft">Tautan aktif sampai <?= esc((string) $expiresAt); ?></span>
                    <?php endif; ?>
                </div>
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
                <i class="fas fa-chevron-left" aria-hidden="true"></i>
            </button>
            <img id="lightboxImage" src="" alt="Foto kegiatan">
            <button type="button" class="lightbox-nav-btn lightbox-nav-btn-right" id="btnLightboxNext" aria-label="Foto berikutnya">
                <i class="fas fa-chevron-right" aria-hidden="true"></i>
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
        background:
            radial-gradient(circle at top left, rgba(244, 115, 50, 0.12), transparent 30%),
            radial-gradient(circle at top right, rgba(15, 118, 110, 0.09), transparent 26%),
            linear-gradient(180deg, #f7fafc 0%, #eef3f8 100%);
        min-height: 70vh;
    }

    .gallery-page-heading {
        max-width: 980px;
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.96), rgba(255, 250, 245, 0.9));
        border: 1px solid rgba(214, 96, 34, 0.12);
        border-radius: 20px;
        padding: 16px 18px 14px;
        box-shadow: 0 16px 34px rgba(15, 23, 42, 0.08);
        backdrop-filter: blur(10px);
    }

    .gallery-page-heading::before {
        content: '';
        position: absolute;
        inset: 0 auto auto 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, #d95f23 0%, #f08a2b 55%, #f7b344 100%);
    }

    .gallery-page-heading::after {
        content: '';
        position: absolute;
        top: -34px;
        right: -34px;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(240, 138, 43, 0.16) 0%, rgba(240, 138, 43, 0) 72%);
        pointer-events: none;
    }

    .gallery-page-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        color: #c2410c;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.16em;
        text-transform: uppercase;
    }

    .gallery-page-kicker::before {
        content: '';
        width: 9px;
        height: 9px;
        border-radius: 999px;
        background: linear-gradient(180deg, #f08a2b, #d95f23);
        box-shadow: 0 0 0 4px rgba(217, 95, 35, 0.12);
    }

    .gallery-page-title {
        color: #0f172a;
        font-size: clamp(1.58rem, 2.2vw, 2.08rem);
        font-weight: 800;
        letter-spacing: -0.02em;
        text-shadow: 0 1px 0 rgba(255, 255, 255, 0.45);
    }

    .gallery-page-subtitle {
        color: #273449;
        font-size: 1.03rem;
        font-weight: 650;
        line-height: 1.5;
        opacity: 1;
    }

    .gallery-page-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }

    .gallery-page-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 999px;
        background: rgba(217, 95, 35, 0.08);
        border: 1px solid rgba(217, 95, 35, 0.12);
        color: #9a3412;
        font-size: 0.82rem;
        font-weight: 800;
        line-height: 1;
    }

    .gallery-page-pill--soft {
        background: rgba(15, 118, 110, 0.08);
        border-color: rgba(15, 118, 110, 0.12);
        color: #0f766e;
    }

    .gallery-toolbar {
        padding-top: 2px;
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
        box-shadow: 0 10px 18px rgba(220, 88, 30, 0.16);
        border-radius: 999px;
        padding-inline: 18px;
        font-weight: 700;
        letter-spacing: 0.01em;
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
        border: 1px solid rgba(148, 163, 184, 0.24);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.07);
        transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
    }

    .gallery-item:hover {
        transform: translateY(-4px);
        border-color: rgba(217, 95, 35, 0.24);
        box-shadow: 0 18px 34px rgba(15, 23, 42, 0.12);
    }

    .gallery-photo-btn {
        width: 100%;
        border: 0;
        padding: 0;
        display: block;
        background: linear-gradient(180deg, #f8fafc, #eef3f8);
        cursor: zoom-in;
        overflow: hidden;
    }

    .gallery-photo-btn img {
        width: 100%;
        height: 220px;
        object-fit: cover;
        display: block;
        transition: transform 0.28s ease, filter 0.28s ease;
    }

    .gallery-item:hover .gallery-photo-btn img {
        transform: scale(1.03);
        filter: saturate(1.06) contrast(1.03);
    }

    .gallery-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 13px 14px;
    }

    .gallery-name {
        font-weight: 700;
        color: #102033;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .gallery-meta .btn-outline-primary {
        border-color: rgba(13, 110, 253, 0.18);
        color: #0d6efd;
        background: rgba(13, 110, 253, 0.04);
        font-weight: 700;
    }

    .gallery-meta .btn-outline-primary:hover {
        background: #0d6efd;
        color: #fff;
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
        width: 44px;
        height: 44px;
        border: 0;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.8);
        color: #fff;
        font-size: 18px;
        font-weight: 600;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
        cursor: pointer;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.18);
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s ease, background 0.2s ease;
    }

    .share-lightbox.nav-visible .lightbox-nav-btn {
        opacity: 1;
        pointer-events: auto;
    }

    .lightbox-nav-btn:hover {
        background: rgba(15, 23, 42, 0.95);
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
        .gallery-toolbar {
            padding-top: 0;
        }

        .gallery-page-heading {
            padding: 12px 13px;
            border-radius: 14px;
        }

        .gallery-page-title {
            font-size: 1.28rem;
        }

        .gallery-page-subtitle {
            font-size: 0.94rem;
            line-height: 1.45;
        }

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
            opacity: 1;
            pointer-events: auto;
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
    let navHideTimer = null;

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

    const hideNavControls = function () {
        if (!lightbox) {
            return;
        }

        lightbox.classList.remove('nav-visible');
    };

    const revealNavControls = function () {
        if (!lightbox || !lightbox.classList.contains('is-open')) {
            return;
        }

        if (photoButtons.length <= 1) {
            hideNavControls();
            return;
        }

        lightbox.classList.add('nav-visible');

        if (window.matchMedia('(pointer: coarse)').matches) {
            return;
        }

        if (navHideTimer) {
            window.clearTimeout(navHideTimer);
        }

        navHideTimer = window.setTimeout(function () {
            hideNavControls();
        }, 1200);
    };

    const openLightbox = function (index) {
        if (!lightbox || !lightboxImage || !lightboxName || !lightboxDownload) {
            return;
        }

        showPhotoAt(index);
        lightbox.classList.add('is-open');
        lightbox.setAttribute('aria-hidden', 'false');
        revealNavControls();
    };

    const closeBox = function () {
        if (!lightbox) {
            return;
        }

        lightbox.classList.remove('is-open');
        lightbox.classList.remove('nav-visible');
        lightbox.setAttribute('aria-hidden', 'true');

        if (navHideTimer) {
            window.clearTimeout(navHideTimer);
            navHideTimer = null;
        }
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
        revealNavControls();
    });

    btnLightboxNext && btnLightboxNext.addEventListener('click', function () {
        showPhotoAt(currentPhotoIndex + 1);
        revealNavControls();
    });

    lightboxImage && lightboxImage.addEventListener('load', function () {
        lightboxImage.classList.remove('is-loading');
    });

    if (lightboxImageShell) {
        lightboxImageShell.addEventListener('mousemove', function () {
            revealNavControls();
        });

        lightboxImageShell.addEventListener('touchstart', function (event) {
            if (!event.touches || !event.touches.length) {
                return;
            }

            touchStartX = event.touches[0].clientX;
            touchEndX = touchStartX;
            revealNavControls();
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
