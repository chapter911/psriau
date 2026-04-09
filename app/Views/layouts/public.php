<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <?php
        $appName = trim((string) ($appSetting['app_name'] ?? 'PLN EPM-Digi'));
        $pageDocTitle = trim((string) ($title ?? ''));
        $isLandingPage = trim(uri_string(), '/') === '';

        if ($isLandingPage) {
            $docTitle = $pageDocTitle !== '' ? $pageDocTitle : (string) ($globalSetting['official_name'] ?? 'Satker PPS Kementerian PU');
        } else {
            $docTitle = $pageDocTitle !== '' ? $pageDocTitle . ' | ' . $appName : $appName;
        }
    ?>
    <title><?= esc($docTitle); ?></title>
    <?php if (! empty($globalSetting['logo_url'] ?? '')): ?>
        <link rel="icon" type="image/png" href="<?= esc($globalSetting['logo_url']); ?>">
        <link rel="apple-touch-icon" href="<?= esc($globalSetting['logo_url']); ?>">
    <?php endif; ?>
    <script>
        window.__appPreloaderStart = typeof performance !== 'undefined' ? performance.now() : Date.now();
    </script>
    <link rel="stylesheet" href="<?= base_url('assets/css/site.css'); ?>">
</head>
<body data-preloader-duration="<?= (int) ($appSetting['preloader_duration_ms'] ?? 500); ?>">
<?php
    $preloaderName = trim((string) ($appSetting['app_name'] ?? $globalSetting['official_name'] ?? 'Aplikasi'));
    $preloaderLogo = trim((string) ($globalSetting['logo_url'] ?? ''));
    $officialName = trim((string) ($globalSetting['official_name'] ?? ''));
    $preloaderSubtitle = '';
    if ($officialName !== '' && strtolower($officialName) !== strtolower($preloaderName)) {
        $preloaderSubtitle = $officialName;
    }
?>
<div class="app-preloader" id="appPreloader" aria-hidden="true">
    <div class="app-preloader-brand">
        <?php if ($preloaderLogo !== ''): ?>
            <img src="<?= esc($preloaderLogo); ?>" alt="Logo <?= esc($preloaderName); ?>" class="app-preloader-logo">
        <?php else: ?>
            <span class="app-preloader-fallback"><?= esc(strtoupper(substr($preloaderName, 0, 2))); ?></span>
        <?php endif; ?>
        <div class="app-preloader-text">
            <span class="app-preloader-name"><?= esc($preloaderName); ?></span>
            <?php if ($preloaderSubtitle !== ''): ?>
                <small class="app-preloader-subtitle"><?= esc($preloaderSubtitle); ?></small>
            <?php endif; ?>
        </div>
    </div>
    <div class="app-preloader-bar" aria-hidden="true"><span></span></div>
</div>

<header class="site-header">
    <div class="container nav-wrap">
        <?php $appLogo = $globalSetting['logo_url'] ?? ''; ?>
        <a href="<?= site_url('/#beranda'); ?>" class="brand brand-with-logo">
            <?php if (! empty($appLogo)): ?>
                <img src="<?= esc($appLogo); ?>" alt="Logo <?= esc($globalSetting['official_name'] ?? 'Satker PPS Kementerian PU'); ?>" class="brand-logo">
            <?php else: ?>
                <span class="brand-mark">SP</span>
            <?php endif; ?>
            <span class="brand-text"><?= esc($globalSetting['official_name'] ?? 'Satker PPS Kementerian PU'); ?></span>
        </a>
        <nav id="navMenu">
            <a href="<?= site_url('/#beranda'); ?>">Beranda</a>
            <a href="<?= site_url('/#tentang'); ?>">Tentang Kami</a>
            <a href="<?= site_url('/#acara'); ?>">Acara</a>
            <a href="<?= site_url('/#instagram'); ?>">Instagram</a>
            <a href="<?= site_url('/#kontak'); ?>">Kontak</a>
            <a href="<?= site_url('/masuk'); ?>" class="nav-login-link">Masuk</a>
        </nav>
        <a href="<?= site_url('/masuk'); ?>" class="btn-login">Masuk</a>
        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation" aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const navToggle = document.getElementById('navToggle');
        const navMenu = document.getElementById('navMenu');

        if (navToggle && navMenu) {
            navToggle.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                navMenu.classList.toggle('active');
                const isActive = navMenu.classList.contains('active');
                navToggle.setAttribute('aria-expanded', isActive);
            });

            // Close menu when clicking on a link
            navMenu.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    navMenu.classList.remove('active');
                    navToggle.setAttribute('aria-expanded', 'false');
                });
            });

            // Close menu when clicking outside
            document.addEventListener('click', (e) => {
                if (!navToggle.contains(e.target) && !navMenu.contains(e.target)) {
                    navMenu.classList.remove('active');
                    navToggle.setAttribute('aria-expanded', 'false');
                }
            });
        }
    });
</script>

<main>
    <?php $message = session()->getFlashdata('message'); ?>
    <?php $error = session()->getFlashdata('error'); ?>
    <?php if ($message || $error): ?>
        <div class="container flash-wrap">
            <?php if ($message): ?>
                <div class="flash success"><?= esc($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="flash error"><?= esc($error); ?></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?= $this->renderSection('content'); ?>
</main>

<footer id="kontak" class="site-footer">
    <div class="container footer-grid">
        <div>
            <h4><?= esc($globalSetting['official_name'] ?? 'Satker PPS Kementerian PU'); ?></h4>
            <p>Kementerian Pekerjaan Umum</p>
        </div>
        <div>
            <h4>Layanan Publik</h4>
            <p>Informasi perencanaan, kegiatan, dan pembaruan resmi Instagram.</p>
        </div>
        <div>
            <h4>Kontak</h4>
            <p>Email: <?= esc($globalSetting['contact_email'] ?? '-'); ?></p>
            <p>Telepon: <?= esc($globalSetting['contact_phone'] ?? '-'); ?></p>
            <p>Alamat: <?= esc($globalSetting['contact_address'] ?? '-'); ?></p>
            <?php if (! empty($globalSetting['contact_map_url'])): ?>
                <p><a href="<?= esc($globalSetting['contact_map_url']); ?>" target="_blank" rel="noopener">Lihat peta</a></p>
            <?php endif; ?>
        </div>
    </div>
</footer>
<script src="<?= base_url('assets/adminlte/plugins/sweetalert2/sweetalert2.all.min.js'); ?>"></script>
<script>
    (() => {
        const preloaderShownAt = typeof window.__appPreloaderStart === 'number'
            ? window.__appPreloaderStart
            : (typeof performance !== 'undefined' ? performance.now() : Date.now());
        const minimumVisibleMs = Number(document.body?.dataset.preloaderDuration || <?= (int) ($appSetting['preloader_duration_ms'] ?? 500); ?>);

        window.addEventListener('load', () => {
            const preloader = document.getElementById('appPreloader');
            if (!preloader) {
                return;
            }

            const now = typeof performance !== 'undefined' ? performance.now() : Date.now();
            const remaining = Math.max(0, minimumVisibleMs - (now - preloaderShownAt));

            window.setTimeout(() => {
                preloader.classList.add('is-hidden');
                window.setTimeout(() => {
                    preloader.remove();
                }, 320);
            }, remaining);
        });
    })();

    (() => {
        const buildConfirmConfig = (form, submitter) => {
            const title = (submitter && submitter.dataset.confirmTitle) || form.dataset.confirmTitle || 'Konfirmasi';
            const text = (submitter && submitter.dataset.confirmText) || form.dataset.confirmText || 'Yakin ingin melanjutkan?';
            const confirmButtonText = (submitter && submitter.dataset.confirmButton) || form.dataset.confirmButton || 'Ya, lanjutkan';

            return {
                icon: 'question',
                title,
                text,
                showCancelButton: true,
                confirmButtonText,
                cancelButtonText: 'Batal',
                reverseButtons: true,
                focusCancel: true,
            };
        };

        document.querySelectorAll('form').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (form.dataset.confirmed === '1' || form.dataset.skipConfirm === '1') {
                    return;
                }

                const method = (form.getAttribute('method') || 'get').toLowerCase();
                if (method !== 'post') {
                    return;
                }

                event.preventDefault();

                const submitter = event.submitter || null;
                const config = buildConfirmConfig(form, submitter);
                const fallbackConfirm = () => {
                    const ok = window.confirm(config.text || 'Yakin ingin melanjutkan?');
                    if (ok) {
                        form.dataset.confirmed = '1';
                        if (submitter && typeof form.requestSubmit === 'function') {
                            form.requestSubmit(submitter);
                            return;
                        }

                        form.submit();
                    }
                };

                if (typeof Swal === 'undefined') {
                    fallbackConfirm();
                    return;
                }

                Swal.fire(config).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    form.dataset.confirmed = '1';
                    if (submitter && typeof form.requestSubmit === 'function') {
                        form.requestSubmit(submitter);
                        return;
                    }

                    form.submit();
                });
            });
        });
    })();
</script>
</body>
</html>
