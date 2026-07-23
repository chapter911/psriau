<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
        $appName = trim((string) ($appSetting['app_name'] ?? 'PLN EPM-Digi'));
        $officialName = trim((string) ($globalSetting['official_name'] ?? ''));
        $preloaderSubtitle = '';
        if ($officialName !== '' && strtolower($officialName) !== strtolower($appName)) {
            $preloaderSubtitle = $officialName;
        }
        $pageDocTitle = trim((string) ($title ?? $pageTitle ?? ''));
        $docTitle = $pageDocTitle !== '' ? $pageDocTitle . ' | ' . $appName : $appName;
    ?>
    <title><?= esc($docTitle); ?></title>
    <?php if (! empty($globalSetting['logo_url'] ?? '')): ?>
        <link rel="icon" type="image/png" href="<?= esc(media_url((string) $globalSetting['logo_url'])); ?>">
        <link rel="apple-touch-icon" href="<?= esc(media_url((string) $globalSetting['logo_url'])); ?>">
    <?php endif; ?>
    <script>
        window.__appPreloaderStart = typeof performance !== 'undefined' ? performance.now() : Date.now();
    </script>
    <link rel="stylesheet" href="<?= esc(media_url('assets/adminlte/plugins/fontawesome-free/css/all.min.css')); ?>">
    <link rel="stylesheet" href="<?= esc(media_url('assets/adminlte/dist/css/adminlte.min.css')); ?>">
    <link rel="stylesheet" href="<?= esc(media_url('assets/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')); ?>">
    <link rel="stylesheet" href="<?= esc(media_url('assets/adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css')); ?>">
    <link rel="stylesheet" href="<?= esc(media_url('assets/adminlte/plugins/select2/css/select2.min.css')); ?>">
    <link rel="stylesheet" href="<?= esc(media_url('assets/adminlte/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css')); ?>">
    <style>
        :root {
            --app-primary: <?= esc($appSetting['primary_color'] ?? '#0A66C2'); ?>;
            --sidebar-bg: <?= esc($appSetting['sidebar_bg_color'] ?? '#2F3A45'); ?>;
            --sidebar-text: <?= esc($appSetting['sidebar_text_color'] ?? '#C2CBD5'); ?>;
            --sidebar-active-bg: <?= esc($appSetting['sidebar_active_bg_color'] ?? '#0A66C2'); ?>;
            --sidebar-active-text: <?= esc($appSetting['sidebar_active_text_color'] ?? '#FFFFFF'); ?>;
        }

        .brand-image-custom {
            width: 33px;
            height: 33px;
            object-fit: contain;
            border-radius: 8px;
            background: #fff;
            padding: 3px;
            margin-right: 0.5rem;
        }

        .brand-fallback {
            width: 33px;
            height: 33px;
            border-radius: 8px;
            background: #2d7f8b;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-right: 0.5rem;
            font-size: 0.8rem;
        }

        .setting-preview {
            max-height: 88px;
            width: auto;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 6px;
            background: #fff;
            margin-top: 8px;
        }

        .inline-form {
            display: inline-block;
        }

        .btn-primary,
        .bg-primary,
        .badge-primary {
            background-color: var(--app-primary) !important;
            border-color: var(--app-primary) !important;
        }

        .text-primary {
            color: var(--app-primary) !important;
        }

        .card-primary:not(.card-outline) > .card-header,
        .nav-pills .nav-link.active,
        .page-item.active .page-link {
            background-color: var(--app-primary) !important;
            border-color: var(--app-primary) !important;
        }

        .page-link {
            color: var(--app-primary);
        }

        .main-sidebar {
            background-color: var(--sidebar-bg) !important;
        }

        .main-sidebar .brand-link,
        .main-sidebar .nav-sidebar .nav-link,
        .main-sidebar .nav-sidebar .nav-link p,
        .main-sidebar .nav-sidebar .nav-link .nav-icon,
        .main-sidebar .nav-sidebar .nav-header {
            color: var(--sidebar-text) !important;
        }

        .main-sidebar .nav-sidebar > .nav-item > .nav-link.active,
        .main-sidebar .nav-sidebar .nav-treeview > .nav-item > .nav-link.active {
            background-color: var(--sidebar-active-bg) !important;
            color: var(--sidebar-active-text) !important;
        }

        .main-sidebar .nav-sidebar > .nav-item > .nav-link.active p,
        .main-sidebar .nav-sidebar > .nav-item > .nav-link.active .nav-icon,
        .main-sidebar .nav-sidebar .nav-treeview > .nav-item > .nav-link.active p,
        .main-sidebar .nav-sidebar .nav-treeview > .nav-item > .nav-link.active .nav-icon {
            color: var(--sidebar-active-text) !important;
        }

        .main-sidebar .nav-sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.08) !important;
            color: #fff !important;
        }

        /* Table header global styling */
        table thead th,
        table thead {
            background-color: var(--sidebar-bg) !important;
            color: #fff !important;
        }

        table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            border-color: var(--sidebar-bg) !important;
            font-weight: 600;
            background-clip: padding-box;
        }

        .modal table thead th {
            z-index: 1056;
        }

        .modal .table-responsive,
        .modal .table-responsive-sm,
        .modal .table-responsive-md,
        .modal .table-responsive-lg,
        .modal .table-responsive-xl {
            overflow: auto;
        }

        .app-preloader-brand {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            color: #1f2d3d;
            font-weight: 700;
            letter-spacing: 0.2px;
        }

        .app-preloader-logo {
            width: 54px;
            height: 54px;
            border-radius: 12px;
            object-fit: contain;
            background: #fff;
            border: 1px solid #d8dee4;
            padding: 6px;
            box-shadow: 0 8px 22px rgba(15, 23, 32, 0.14);
        }

        .app-preloader-fallback {
            width: 54px;
            height: 54px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(145deg, #1f6c71, #0f3f43);
            color: #fff;
            font-weight: 800;
            box-shadow: 0 8px 22px rgba(15, 23, 32, 0.2);
        }

        .app-preloader-name {
            font-size: 1.05rem;
            line-height: 1.2;
            max-width: 260px;
        }

        .app-preloader-text {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .app-preloader-subtitle {
            font-size: 0.78rem;
            color: #6b7785;
            font-weight: 600;
            margin-top: 0.15rem;
            max-width: 300px;
        }

        .app-preloader-bar {
            width: min(220px, 62vw);
            height: 3px;
            margin-top: 0.95rem;
            border-radius: 999px;
            overflow: hidden;
            background: rgba(31, 108, 113, 0.14);
        }

        .app-preloader-bar span {
            display: block;
            width: 40%;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #1f6c71, #c4471f, #1f6c71);
            background-size: 200% 100%;
            animation: preloader-bar-move 1.2s ease-in-out infinite;
        }

        @media (max-width: 480px) {
            .app-preloader-brand {
                flex-direction: column;
                text-align: center;
                gap: 0.45rem;
            }

            .app-preloader-text {
                align-items: center;
            }

            .app-preloader-name {
                font-size: 0.92rem;
                max-width: 185px;
            }

            .app-preloader-subtitle {
                max-width: 210px;
                font-size: 0.7rem;
            }

            .app-preloader-bar {
                width: min(150px, 72vw);
                margin-top: 0.7rem;
            }
        }

        @keyframes preloader-bar-move {
            0% {
                transform: translateX(-120%);
            }
            100% {
                transform: translateX(320%);
            }
        }

        /* Customize multiple Select2 selected items to wrap text, stack vertically, and be easy to delete */
        .select2-container .select2-selection--multiple .select2-selection__rendered {
            display: flex !important;
            flex-direction: column !important;
            gap: 0 !important;
            padding: 0 6px !important;
        }

        .select2-container .select2-selection--multiple .select2-selection__choice {
            display: flex !important;
            flex-direction: row-reverse !important;
            justify-content: space-between !important;
            align-items: center !important;
            float: none !important;
            width: 100% !important;
            margin: 0 !important;
            margin-top: 6px !important;
            padding: 6px 12px !important;
            white-space: normal !important;
            word-break: break-word !important;
            background-color: #f8f9fa !important;
            border: 1px solid #ced4da !important;
            border-radius: 4px !important;
            color: #495057 !important;
            box-sizing: border-box !important;
            line-height: 1.4 !important;
        }

        .select2-container .select2-selection--multiple .select2-selection__choice:last-of-type {
            margin-bottom: 6px !important;
        }

        /* Style the remove button to make it larger and easier to click on the right side */
        .select2-container .select2-selection--multiple .select2-selection__choice__remove {
            float: none !important;
            font-size: 1.25rem !important;
            font-weight: 700 !important;
            color: #dc3545 !important;
            margin-left: 12px !important;
            margin-right: 0 !important;
            cursor: pointer !important;
            padding: 0 4px !important;
            transition: color 0.15s ease-in-out !important;
        }

        .select2-container .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #bd2130 !important;
            background-color: transparent !important;
        }

        /* Ensure container expands properly and search box starts on a new line or fits nicely */
        .select2-container .select2-selection--multiple {
            height: auto !important;
            min-height: 38px !important;
        }

        .select2-container .select2-selection--multiple .select2-search--inline {
            display: block !important;
            width: 100% !important;
            float: none !important;
            margin: 0 !important;
        }

        .select2-container .select2-selection--multiple .select2-selection__choice + .select2-search--inline {
            margin-top: 6px !important;
            margin-bottom: 6px !important;
        }

        .select2-container .select2-selection--multiple .select2-search__field {
            width: 100% !important;
            margin: 0 !important;
            padding: 6px 8px !important;
            height: 36px !important;
            box-sizing: border-box !important;
            line-height: 24px !important;
        }
    </style>
    <script>
        // Prevent AdminLTE from crashing when loaded inside an iframe without data-widget="iframe"
        if (!localStorage.getItem('AdminLTE:IFrame:Options')) {
            localStorage.setItem('AdminLTE:IFrame:Options', JSON.stringify({ autoIframeMode: false }));
        }
    </script>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed" data-preloader-duration="<?= (int) ($appSetting['preloader_duration_ms'] ?? 500); ?>">
<div class="wrapper">
    <section class="content" style="padding: 15px;">
        <div class="container-fluid">
            <?php $flashSuccess = session()->getFlashdata('message') ?: session()->getFlashdata('success'); ?>
            <?php if ($flashSuccess): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle mr-1"></i> <?= esc($flashSuccess); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php elseif (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle mr-1"></i> <?= esc(session()->getFlashdata('error')); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php elseif (session()->getFlashdata('warning')): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle mr-1"></i> <?= esc(session()->getFlashdata('warning')); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php elseif (session()->getFlashdata('info')): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle mr-1"></i> <?= esc(session()->getFlashdata('info')); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            <?= $this->renderSection('content'); ?>
        </div>
    </section>
</div>
<?= $this->include('layouts/admin/scripts'); ?>
</body>
</html>
