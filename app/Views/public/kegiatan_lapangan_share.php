<?= $this->extend('layouts/public'); ?>

<?= $this->section('content'); ?>
<section class="shared-gallery-page">
    <div class="container gallery-shell py-4">
        <div class="gallery-hero">
            <div class="gallery-hero-inner">
                <div class="gallery-hero-copy">
                    <div class="gallery-hero-kicker">Dokumentasi Resmi</div>
                    <h1 class="gallery-hero-title">Galeri Kegiatan Lapangan</h1>
                    <p class="gallery-hero-subtitle">
                        <?= esc((string) ($activity['title'] ?? '-')); ?>
                        <?php if (! empty($activity['activity_date'])): ?>
                            · <?= esc((string) $activity['activity_date']); ?>
                        <?php endif; ?>
                        <?php if (! empty($activity['location'])): ?>
                            · <?= esc((string) $activity['location']); ?>
                        <?php endif; ?>
                    </p>
                    <div class="gallery-meta-list">
                        <span class="gallery-meta-item"><i class="fas fa-images"></i> <?= (int) count($photos); ?> foto</span>
                        <span class="gallery-meta-item"><i class="fas fa-link"></i> Akses publik via tautan</span>
                        <?php if (! empty($expiresAt)): ?>
                            <span class="gallery-meta-item"><i class="fas fa-clock"></i> Aktif sampai <?= esc((string) $expiresAt); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="gallery-hero-aside">
                    <div class="gallery-hero-summary-card">
                        <div class="gallery-hero-summary-label">Ringkasan</div>
                        <div class="gallery-hero-summary-value"><?= (int) count($photos); ?> foto</div>
                        <div class="gallery-hero-summary-text">Klik foto untuk memperbesar, atau unduh per item jika diperlukan.</div>
                        <a class="btn btn-light gallery-toolbar-btn gallery-toolbar-btn--light" href="<?= site_url('/kegiatan-lapangan/share/' . $shareToken . '/download-zip'); ?>">
                            <i class="fas fa-file-archive mr-1"></i> Download Semua (ZIP)
                        </a>
                    </div>
                </div>
            </div>
        </div>

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
            radial-gradient(circle at top left, rgba(56, 189, 248, 0.18), transparent 34%),
            radial-gradient(circle at top right, rgba(59, 130, 246, 0.14), transparent 30%),
            linear-gradient(180deg, #eaf2fb 0%, #f7fbff 56%, #ffffff 100%);
        min-height: 70vh;
    }

    .gallery-shell {
        max-width: 1520px;
        margin: 0 auto 40px;
    }

    /* Small inner top padding so hero doesn't touch the header when needed */
    .shared-gallery-page > .container {
        padding-top: 12px;
    }

    .gallery-hero {
        position: relative;
        overflow: hidden;
        border-radius: 24px;
        padding: 26px 26px 24px;
        margin-bottom: 18px;
        background: linear-gradient(135deg, #1d4ed8 0%, #1e3a8a 52%, #0ea5e9 100%);
        color: #fff;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.22);
    }

    .gallery-hero::before,
    .gallery-hero::after {
        content: '';
        position: absolute;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        pointer-events: none;
    }

    .gallery-hero::before {
        width: 180px;
        height: 180px;
        top: -60px;
        right: -20px;
    }

    .gallery-hero::after {
        width: 120px;
        height: 120px;
        bottom: -40px;
        left: 18%;
        background: rgba(255, 255, 255, 0.08);
    }

    .gallery-hero-inner {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: minmax(0, 1.5fr) minmax(280px, 0.85fr);
        gap: 22px;
        align-items: center;
    }

    .gallery-hero-copy {
        max-width: 64ch;
    }

    .gallery-hero-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        color: rgba(255, 255, 255, 0.84);
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.16em;
        text-transform: uppercase;
    }

    .gallery-hero-kicker::before {
        content: '';
        width: 9px;
        height: 9px;
        border-radius: 999px;
        background: linear-gradient(180deg, #7dd3fc, #38bdf8);
        box-shadow: 0 0 0 4px rgba(125, 211, 252, 0.14);
    }

    .gallery-hero-title {
        margin: 0 0 12px;
        color: #fff;
        font-size: clamp(1.5rem, 2.5vw, 2.25rem);
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -0.03em;
    }

    .gallery-hero-subtitle {
        max-width: 58ch;
        margin: 0;
        color: rgba(255, 255, 255, 0.84);
        font-size: 0.98rem;
    }

    .gallery-meta-list {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 18px;
    }

    .gallery-meta-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.12);
        font-size: 0.82rem;
        font-weight: 700;
        line-height: 1;
        backdrop-filter: blur(8px);
    }

    .gallery-hero-aside {
        display: flex;
        justify-content: flex-end;
    }

    .gallery-hero-summary-card {
        position: relative;
        width: 100%;
        max-width: 340px;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.16);
        border-radius: 20px;
        padding: 18px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.16);
        backdrop-filter: blur(10px);
    }

    .gallery-hero-summary-card::before {
        content: '';
        position: absolute;
        left: 18px;
        right: 18px;
        top: 12px;
        height: 1px;
        background: rgba(255, 255, 255, 0.16);
    }

    .gallery-hero-summary-label {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: rgba(255, 255, 255, 0.75);
        font-weight: 700;
        margin-bottom: 6px;
    }

    .gallery-hero-summary-value {
        font-size: 2rem;
        font-weight: 900;
        line-height: 1;
        color: #fff;
        margin-bottom: 6px;
    }

    .gallery-hero-summary-text {
        color: rgba(255, 255, 255, 0.82);
        line-height: 1.6;
        margin-bottom: 14px;
    }

    .gallery-intro-card {
        margin-bottom: 18px;
        padding: 18px 20px 17px;
    }

    .gallery-expiry-card {
        margin-bottom: 18px;
        padding: 14px 16px 13px;
        border-top: 3px solid #334155;
    }

    .gallery-expiry-label {
        font-size: 0.74rem;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: #64748b;
        font-weight: 800;
        margin-bottom: 4px;
    }

    .gallery-expiry-value {
        font-size: 0.98rem;
        font-weight: 700;
        color: #0f172a;
    }

    .gallery-intro-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-radius: 999px;
        background: #e0f2fe;
        color: #0369a1;
        padding: 6px 12px;
        font-size: 0.82rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .gallery-intro-title {
        margin: 12px 0 6px;
        font-size: 1.15rem;
        font-weight: 800;
        color: #0f172a;
    }

    .gallery-intro-text {
        margin: 0;
        color: #475569;
        line-height: 1.7;
    }

    .gallery-toolbar {
        padding-top: 2px;
    }

    .gallery {
        display: grid;
        gap: 16px;
    }

    .gallery-toolbar-btn {
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 18px rgba(15, 23, 42, 0.18);
        border-radius: 999px;
        padding-inline: 18px;
        font-weight: 700;
        letter-spacing: 0.01em;
    }

    .gallery-toolbar-btn--light {
        background: rgba(255, 255, 255, 0.96);
        color: #0f172a;
        border-color: rgba(255, 255, 255, 0.2);
    }

    .gallery-toolbar-btn--light:hover {
        background: #fff;
        color: #0f172a;
    }

    .gallery-toolbar-btn:focus,
    .gallery-toolbar-btn:focus-visible {
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.18), 0 10px 18px rgba(30, 41, 59, 0.14);
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
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
        transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
    }

    .gallery-item:hover {
        transform: translateY(-4px);
        border-color: rgba(59, 130, 246, 0.24);
        box-shadow: 0 18px 34px rgba(15, 23, 42, 0.12);
    }

    .gallery-item:focus-within {
        border-color: rgba(59, 130, 246, 0.38);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.14), 0 16px 30px rgba(15, 23, 42, 0.1);
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

    .gallery-photo-btn:focus,
    .gallery-photo-btn:focus-visible {
        outline: none;
    }

    .gallery-photo-btn:focus-visible img {
        filter: saturate(1.05) contrast(1.05);
        box-shadow: inset 0 0 0 3px rgba(59, 130, 246, 0.32);
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
        border-top: 1px solid rgba(226, 232, 240, 0.9);
        background: linear-gradient(180deg, #fff, #fbfdff);
    }

    .gallery-name {
        font-weight: 700;
        color: #102033;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .gallery-meta .btn-outline-primary {
        border-color: rgba(59, 130, 246, 0.2);
        color: #0d6efd;
        background: rgba(59, 130, 246, 0.03);
        font-weight: 700;
    }

    .gallery-meta .btn-outline-primary:hover {
        background: #0d6efd;
        color: #fff;
    }

    .gallery-meta .btn-outline-primary:focus,
    .gallery-meta .btn-outline-primary:focus-visible {
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.16);
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

    @media (max-width: 991.98px) {
        .gallery-hero-inner {
            grid-template-columns: 1fr;
        }

        .gallery-hero-aside {
            justify-content: flex-start;
        }

        .gallery-hero-summary-card {
            max-width: none;
        }
    }

    @media (max-width: 767.98px) {
        .gallery-shell {
            margin-top: 18px;
            margin-bottom: 28px;
        }

        .gallery-hero {
            padding: 18px 18px 16px;
            border-radius: 20px;
        }

        .gallery-intro-card {
            padding: 16px 16px 15px;
        }

        .gallery-hero-title {
            font-size: 1.4rem;
        }

        .gallery-hero-subtitle {
            font-size: 0.92rem;
            line-height: 1.5;
        }

        .gallery-hero-summary-card {
            padding: 16px 16px 15px;
        }

        .gallery-hero-summary-value {
            font-size: 1.7rem;
        }

        .gallery-grid .gallery-photo-btn img {
            height: 160px;
        }

        /* Force two columns on small screens for a denser mobile grid */
        .gallery-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
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
