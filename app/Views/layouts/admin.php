<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
        $appName = trim((string) ($appSetting['app_name'] ?? 'PLN EPM-Digi'));
        $pageDocTitle = trim((string) ($title ?? $pageTitle ?? ''));
        $docTitle = $pageDocTitle !== '' ? $pageDocTitle . ' | ' . $appName : $appName;
    ?>
    <title><?= esc($docTitle); ?></title>
    <?php if (! empty($globalSetting['logo_url'] ?? '')): ?>
        <link rel="icon" type="image/png" href="<?= esc($globalSetting['logo_url']); ?>">
        <link rel="apple-touch-icon" href="<?= esc($globalSetting['logo_url']); ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= base_url('assets/adminlte/plugins/fontawesome-free/css/all.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/adminlte/dist/css/adminlte.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css'); ?>">
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
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
<div class="wrapper">
    <?= $this->include('layouts/admin/navbar'); ?>
    <?= $this->include('layouts/admin/sidebar'); ?>
    <?= $this->include('layouts/admin/content'); ?>
    <?= $this->include('layouts/admin/footer'); ?>
</div>
<?= $this->include('layouts/admin/scripts'); ?>
</body>
</html>
