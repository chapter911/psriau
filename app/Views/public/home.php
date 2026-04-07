<?= $this->extend('layouts/public'); ?>

<?= $this->section('content'); ?>
<section id="beranda" class="hero">
    <?php if (! empty($slides)): ?>
        <?php foreach ($slides as $index => $slide): ?>
            <div class="hero-slide <?= $index === 0 ? 'active' : ''; ?>" style="background-image: url('<?= esc($slide['image_url']); ?>')"></div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="hero-slide active" style="background-image: linear-gradient(130deg, #1f6c71, #c4471f)"></div>
    <?php endif; ?>
    <div class="hero-overlay"></div>

    <?php if (! empty($slides) && count($slides) > 1): ?>
        <button class="hero-nav hero-nav-prev" aria-label="Slide sebelumnya">❮</button>
        <button class="hero-nav hero-nav-next" aria-label="Slide berikutnya">❯</button>
        <div class="hero-indicators">
            <?php foreach ($slides as $index => $slide): ?>
                <button class="hero-indicator <?= $index === 0 ? 'active' : ''; ?>" data-slide="<?= $index; ?>" aria-label="Slide <?= $index + 1; ?>"></button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="container hero-content hero-grid">
        <div>
            <span class="hero-kicker">Portal Informasi Resmi</span>
            <h1><?= esc($setting['hero_title']); ?></h1>
            <p><?= esc($setting['hero_subtitle']); ?></p>
            <div class="hero-cta">
                <a class="btn-primary" href="#tentang">Kenal Lebih Dekat</a>
                <a class="btn-ghost" href="#acara">Lihat Agenda</a>
            </div>
        </div>
        <aside class="hero-panel">
            <h3>Ringkasan Cepat</h3>
            <div class="hero-stats">
                <div>
                    <span><?= count($events); ?></span>
                    <small>Acara Terbaru</small>
                </div>
                <div>
                    <span><?= count($instagramPostUrls ?? []); ?></span>
                    <small>Post Instagram</small>
                </div>
            </div>
            <p>Informasi kegiatan dan pembaruan Instagram perencanaan prasarana strategis tersedia dalam satu portal.</p>
        </aside>
    </div>
</section>

<section id="tentang" class="section section-about">
    <div class="container">
        <div class="section-head">
            <span class="section-kicker">Profil</span>
            <h2>Tentang Kami</h2>
        </div>
        <div class="content-wrap about-wrap">
            <p><?= nl2br(esc($setting['about_intro'])); ?></p>
            <a class="text-link" href="#beranda">Kembali ke atas</a>
        </div>
    </div>
</section>

<section id="acara" class="section">
    <div class="container">
        <div class="section-head section-head-inline">
            <div>
                <span class="section-kicker">Agenda</span>
                <h2>Acara Terbaru</h2>
            </div>
            <a class="text-link" href="#kontak">Hubungi kami</a>
        </div>
        <div class="cards">
            <?php foreach ($events as $event): ?>
                <article class="card">
                    <?php $eventImage = $event['image_url'] ?: ($setting['default_event_image'] ?? ($globalSetting['default_event_image'] ?? '')); ?>
                    <?php if (! empty($eventImage)): ?>
                        <img src="<?= esc($eventImage); ?>" alt="<?= esc($event['title']); ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <h3><?= esc($event['title']); ?></h3>
                        <p class="meta"><?= esc($event['event_date'] ?? '-'); ?> • <?= esc($event['location'] ?? 'Lokasi belum diisi'); ?></p>
                        <p><?= esc($event['summary']); ?></p>
                        <a class="btn-secondary" href="<?= site_url('/acara/' . $event['slug']); ?>">Lihat Detail</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="instagram" class="section">
    <div class="container">
        <div class="section-head section-head-inline">
            <div>
                <span class="section-kicker">Publikasi</span>
                <h2>Instagram Terbaru</h2>
            </div>
            <?php $instagramProfileUrl = (string) ($setting['instagram_profile_url'] ?? ($globalSetting['instagram_profile_url'] ?? '')); ?>
            <?php if (! empty($instagramProfileUrl)): ?>
                <a class="text-link" href="<?= esc($instagramProfileUrl); ?>" target="_blank" rel="noopener">Buka Profil</a>
            <?php endif; ?>
        </div>
        <div class="content-wrap" style="display: grid; grid-template-columns: 1fr; gap: 1rem; align-items: start;">
            <?php $instagramPostUrls = $instagramPostUrls ?? []; ?>

            <?php if (! empty($instagramPostUrls)): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem;">
                    <?php foreach ($instagramPostUrls as $instagramPostUrl): ?>
                        <div class="card" style="padding: .75rem; background: linear-gradient(180deg, #ffffff 0%, #f6f7fb 100%); border: 1px solid rgba(0,0,0,.08);">
                            <blockquote class="instagram-media" data-instgrm-permalink="<?= esc($instagramPostUrl); ?>" data-instgrm-version="14" style="background:#fff;border:0;border-radius:12px;box-shadow:0 1px 10px rgba(0,0,0,.08);margin:0;min-width:100%;padding:0;width:100%;"></blockquote>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card" style="padding: 1rem; background: linear-gradient(180deg, #ffffff 0%, #f6f7fb 100%); border: 1px dashed rgba(0,0,0,.18);">
                    <p style="margin: 0 0 .75rem;">Belum ada post Instagram yang dipublikasikan dari menu Admin Berita.</p>
                    <?php if (! empty($instagramProfileUrl)): ?>
                        <a class="btn-secondary" href="<?= esc($instagramProfileUrl); ?>" target="_blank" rel="noopener">Buka Profil Instagram</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php if (! empty($instagramPostUrls)): ?>
    <script async defer src="https://www.instagram.com/embed.js"></script>
<?php endif; ?>

<script>
    (() => {
        const slides = document.querySelectorAll('.hero-slide');
        if (slides.length < 2) return;

        let current = 0;
        let touchStartX = 0;
        let touchEndX = 0;
        let autoPlayInterval;

        const showSlide = (index) => {
            slides[current].classList.remove('active');
            current = (index + slides.length) % slides.length;
            slides[current].classList.add('active');
        };

        const nextSlide = () => {
            showSlide((current + 1) % slides.length);
        };

        const prevSlide = () => {
            showSlide((current - 1 + slides.length) % slides.length);
        };

        const handleSwipe = () => {
            if (touchEndX < touchStartX - 50) {
                nextSlide();
                restartAutoPlay();
            } else if (touchEndX > touchStartX + 50) {
                prevSlide();
                restartAutoPlay();
            }
        };

        const startAutoPlay = () => {
            autoPlayInterval = setInterval(nextSlide, 4500);
        };

        const stopAutoPlay = () => {
            clearInterval(autoPlayInterval);
        };

        const restartAutoPlay = () => {
            stopAutoPlay();
            startAutoPlay();
        };

        const updateIndicators = () => {
            document.querySelectorAll('.hero-indicator').forEach((indicator, index) => {
                indicator.classList.toggle('active', index === current);
            });
        };

        const heroElement = document.querySelector('.hero');
        if (heroElement) {
            heroElement.addEventListener('touchstart', (e) => {
                touchStartX = e.changedTouches[0].screenX;
                stopAutoPlay();
            }, false);

            heroElement.addEventListener('touchend', (e) => {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe();
            }, false);

            heroElement.addEventListener('mouseenter', stopAutoPlay);
            heroElement.addEventListener('mouseleave', restartAutoPlay);
        }

        // Tombol navigasi
        document.querySelector('.hero-nav-prev')?.addEventListener('click', () => {
            prevSlide();
            updateIndicators();
            restartAutoPlay();
        });

        document.querySelector('.hero-nav-next')?.addEventListener('click', () => {
            nextSlide();
            updateIndicators();
            restartAutoPlay();
        });

        // Indikator dots
        document.querySelectorAll('.hero-indicator').forEach(indicator => {
            indicator.addEventListener('click', () => {
                showSlide(parseInt(indicator.dataset.slide));
                updateIndicators();
                restartAutoPlay();
            });
        });

        updateIndicators();
        startAutoPlay();
    })();
</script>
<?= $this->endSection(); ?>
