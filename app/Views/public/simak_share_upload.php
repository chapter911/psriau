<?php
    helper('custom');

    $leafRowsCount = 0;
    foreach (($templateItems ?? []) as $row) {
        if ((bool) ($row['is_leaf'] ?? false) === true) {
            $leafRowsCount++;
        }
    }

    $lengkapCount = $leafRowsCount > 0 ? (int) round(((float) ($kelengkapanPercentage['lengkap_persen'] ?? 0) / 100) * $leafRowsCount) : 0;
    $belumSesuaiCount = $leafRowsCount > 0 ? (int) round(((float) ($kelengkapanPercentage['belum_sesuai_persen'] ?? 0) / 100) * $leafRowsCount) : 0;
    $menungguVerifikasiCount = $leafRowsCount > 0 ? (int) round(((float) ($kelengkapanPercentage['belum_verifikasi_persen'] ?? 0) / 100) * $leafRowsCount) : 0;
    $belumAdaCount = $leafRowsCount > 0 ? (int) round(((float) ($kelengkapanPercentage['belum_ada_persen'] ?? 0) / 100) * $leafRowsCount) : 0;

    $sections = [];
    $currentSectionKey = '';

    foreach (($templateItems ?? []) as $row) {
        if ((bool) ($row['is_header'] ?? false) === true) {
            $sectionKey = trim((string) ($row['section_key'] ?? ($row['display_no'] ?? '')));
            if ($sectionKey === '') {
                continue;
            }

            $sections[$sectionKey] = [
                'label' => trim((string) ($row['display_no'] ?? '')) . '. ' . trim((string) ($row['section_title'] ?? $row['uraian'] ?? '')),
                'rows' => [],
            ];
            $currentSectionKey = $sectionKey;
            continue;
        }

        $sectionKey = trim((string) ($row['section_key'] ?? $currentSectionKey));
        if ($sectionKey === '') {
            continue;
        }

        if (! isset($sections[$sectionKey])) {
            $sections[$sectionKey] = [
                'label' => $sectionKey,
                'rows' => [],
            ];
        }

        $sections[$sectionKey]['rows'][] = $row;
    }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc((string) ($title ?? 'Upload Dokumen SIMAK')); ?></title>
    <?php if (! empty($globalSetting['logo_url'] ?? '')): ?>
        <link rel="icon" type="image/png" href="<?= esc($globalSetting['logo_url']); ?>">
        <link rel="apple-touch-icon" href="<?= esc($globalSetting['logo_url']); ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('assets/adminlte/plugins/sweetalert2/sweetalert2.all.min.js'); ?>"></script>
    <style>
        body {
            background:
                radial-gradient(circle at top left, rgba(56, 189, 248, 0.18), transparent 34%),
                radial-gradient(circle at top right, rgba(59, 130, 246, 0.16), transparent 30%),
                linear-gradient(180deg, #eaf2fb 0%, #f7fbff 55%, #ffffff 100%);
            color: #1f2937;
            min-height: 100vh;
        }

        .share-wrapper {
            max-width: 1520px;
            margin: 28px auto 40px;
            padding: 0 18px;
        }

        .share-hero {
            position: relative;
            overflow: hidden;
            border-radius: 24px;
            padding: 24px;
            margin-bottom: 18px;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 52%, #0ea5e9 100%);
            color: #fff;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.22);
        }

        .share-hero::before,
        .share-hero::after {
            content: '';
            position: absolute;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            pointer-events: none;
        }

        .share-hero::before {
            width: 180px;
            height: 180px;
            top: -60px;
            right: -20px;
        }

        .share-hero::after {
            width: 120px;
            height: 120px;
            bottom: -40px;
            left: 18%;
            background: rgba(255, 255, 255, 0.08);
        }

        .share-hero-inner {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: minmax(0, 1.5fr) minmax(260px, 0.9fr);
            gap: 18px;
            align-items: center;
        }

        .share-hero-title {
            font-size: clamp(1.5rem, 2.5vw, 2.25rem);
            font-weight: 800;
            line-height: 1.1;
            margin: 0 0 10px;
            letter-spacing: -0.03em;
        }

        .share-hero-subtitle {
            max-width: 58ch;
            margin: 0;
            color: rgba(255, 255, 255, 0.84);
            font-size: 0.98rem;
        }

        .share-meta-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 16px;
        }

        .share-meta-item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
            font-size: 0.88rem;
            border: 1px solid rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(8px);
        }

        .share-brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .share-brand-logo {
            width: 62px;
            height: 62px;
            object-fit: contain;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.14);
            padding: 8px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.16);
        }

        .share-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            border: 1px solid rgba(148, 163, 184, 0.18);
        }

        .share-surface {
            background: rgba(255, 255, 255, 0.78);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(148, 163, 184, 0.22);
            box-shadow: 0 16px 36px rgba(15, 23, 42, 0.10);
        }

        .share-intro-card {
            margin-bottom: 18px;
            padding: 18px 20px;
        }

        .share-intro-label {
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

        .share-intro-title {
            margin: 12px 0 6px;
            font-size: 1.15rem;
            font-weight: 800;
            color: #0f172a;
        }

        .share-intro-text {
            margin: 0;
            color: #475569;
            line-height: 1.7;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .share-tabs-wrap {
            margin-top: 8px;
        }

        .nav-tabs {
            border-bottom: 0;
            gap: 8px;
        }

        .nav-tabs .nav-link {
            border: 1px solid rgba(148, 163, 184, 0.28);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.8);
            color: #334155;
            font-weight: 700;
            padding: 10px 16px;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
        }

        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 100%);
            color: #fff;
            border-color: transparent;
        }

        .share-table-shell {
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid rgba(148, 163, 184, 0.2);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
        }

        .table-share-simak thead th {
            text-align: center;
            vertical-align: middle;
            position: sticky;
            top: 0;
            z-index: 5;
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            color: #fff;
            border-color: rgba(255, 255, 255, 0.08);
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .table-share-simak .cell-center {
            text-align: center;
        }

        .table-share-simak .row-group {
            background: linear-gradient(180deg, #f8fafc 0%, #edf4fb 100%);
        }

        .table-share-simak .cell-hierarchy-no,
        .table-share-simak .cell-hierarchy-uraian {
            text-align: left;
        }

        .table-share-simak .row-group .cell-hierarchy-no,
        .table-share-simak .row-group .cell-hierarchy-uraian {
            font-weight: 700;
        }

        .table-share-simak .row-leaf .cell-hierarchy-uraian {
            font-weight: 500;
        }

        .table-share-simak tbody tr {
            transition: background-color 0.15s ease, transform 0.15s ease;
        }

        .table-share-simak tbody tr:hover {
            background: #f8fbff;
        }

        .table-share-simak tbody td {
            border-color: #e5edf6;
        }

        .table-share-simak .badge {
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 0.74rem;
            letter-spacing: 0.02em;
        }

        .badge-kel-ada,
        .badge-success {
            background: #dcfce7;
            color: #166534;
        }

        .badge-kel-tidak,
        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .share-kelengkapan-card {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(248, 250, 252, 0.96));
            border-radius: 18px;
            padding: 16px;
            border: 1px solid rgba(148, 163, 184, 0.18);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
            position: relative;
            overflow: hidden;
        }

        .share-kelengkapan-card::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.3), transparent 30%);
            pointer-events: none;
        }

        .share-brand-name {
            font-size: 1.2rem;
            font-weight: 800;
            color: #fff;
            margin: 0;
        }

        .share-brand-sub {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.78);
            margin: 0;
        }

        .share-kelengkapan-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .share-kelengkapan-card.lengkap {
            border-top: 4px solid #16a34a;
        }

        .share-kelengkapan-card.belum-sesuai {
            border-top: 4px solid #f59e0b;
        }

        .share-kelengkapan-card.menunggu-verifikasi {
            border-top: 4px solid #06b6d4;
        }

        .share-kelengkapan-card.belum-ada {
            border-top: 4px solid #ef4444;
        }

        .share-kelengkapan-label {
            font-size: 0.8rem;
            color: #64748b;
            font-weight: 800;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .share-kelengkapan-value {
            font-size: 1.5rem;
            font-weight: 900;
            color: #0f172a;
        }

        .share-kelengkapan-count {
            font-size: 0.76rem;
            color: #64748b;
            font-weight: 600;
        }

        .google-auth-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
            border: 1px solid #dbe4ef;
            border-radius: 20px;
            padding: 18px;
            margin-bottom: 16px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
        }

        .google-auth-status {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .google-auth-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border-radius: 999px;
            background: #eef6ff;
            color: #1d4ed8;
            font-size: 0.88rem;
            font-weight: 600;
        }

        .google-auth-pill strong {
            color: #0f172a;
        }

        .google-auth-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .google-login-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #d1d5db;
            background: #fff;
            color: #1f2937;
            border-radius: 999px;
            padding: 10px 16px;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
            transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
        }

        .google-login-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.12);
            border-color: #94a3b8;
        }

        .google-login-btn svg {
            width: 18px;
            height: 18px;
            display: block;
        }

        .upload-lock-overlay {
            border: 1px dashed #f59e0b;
            background: #fffaf0;
            color: #92400e;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 12px;
        }

        .alert {
            border-radius: 14px;
            border-width: 1px;
        }

        .tab-content.bg-white {
            background: rgba(255, 255, 255, 0.9) !important;
        }

        .table-share-wrap {
            border-radius: 18px;
            overflow: hidden;
        }

        .table-share-simak tbody tr.row-leaf:nth-child(even) {
            background-color: #fbfdff;
        }

        .table-share-simak tbody tr.row-group td {
            background: #f1f5f9;
        }

        .btn {
            border-radius: 10px;
        }

        .modal-content {
            border: 0;
            border-radius: 18px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
        }

        .modal-header,
        .modal-footer {
            border-color: rgba(148, 163, 184, 0.15);
        }

        @media (max-width: 991.98px) {
            .share-hero-inner {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 575.98px) {
            .share-wrapper {
                padding: 0 10px;
            }

            .share-hero {
                padding: 18px;
                border-radius: 20px;
            }

            .share-card,
            .google-auth-card,
            .share-intro-card {
                border-radius: 16px;
            }
        }
    </style>
</head>
<body>
<div class="share-wrapper">
    <div class="share-hero">
        <div class="share-hero-inner">
            <div>
                <div class="share-brand">
                    <?php if (! empty($globalSetting['logo_url'] ?? '')): ?>
                        <img src="<?= esc($globalSetting['logo_url']); ?>" alt="Logo" class="share-brand-logo">
                    <?php endif; ?>
                    <div class="share-brand-text">
                        <p class="share-brand-name"><?= esc((string) ($globalSetting['official_name'] ?? 'Satker PPS Kementerian PU')); ?></p>
                        <p class="share-brand-sub">Upload Dokumen SIMAK</p>
                    </div>
                </div>
                <h1 class="share-hero-title">Portal upload dokumen yang cepat, jelas, dan aman.</h1>
                <p class="share-hero-subtitle">Gunakan halaman ini untuk mengunggah dokumen verifikasi SIMAK. Dokumen yang bisa dilihat langsung akan tampil sebagai preview, sementara file besar diarahkan ke Google Drive agar proses tetap stabil.</p>
                <div class="share-meta-list">
                    <span class="share-meta-item"><i class="fas fa-lock"></i> Akses publik via tautan</span>
                    <span class="share-meta-item"><i class="fas fa-file-alt"></i> Preview dokumen otomatis</span>
                    <span class="share-meta-item"><i class="fas fa-cloud-upload-alt"></i> File besar pakai Google Drive</span>
                </div>
            </div>
            <div class="share-card share-surface share-intro-card">
                <span class="share-intro-label">Ringkas</span>
                <div class="share-intro-title"><?= esc((string) ($item['nama_paket'] ?? '-')); ?></div>
                <p class="share-intro-text mb-2">Nomor kontrak: <?= esc((string) ($item['nomor_kontrak'] ?? '-')); ?></p>
                <p class="share-intro-text">Tahun anggaran: <?= esc((string) ($item['tahun_anggaran'] ?? '-')); ?></p>
            </div>
        </div>
    </div>

    <div class="share-kelengkapan-summary">
        <div class="share-kelengkapan-card lengkap">
            <div class="share-kelengkapan-label">Lengkap</div>
            <div class="share-kelengkapan-value"><?= number_format((float) ($kelengkapanPercentage['lengkap_persen'] ?? 0), 2, ',', '.'); ?>%</div>
            <div class="share-kelengkapan-count"><?= $lengkapCount; ?> dari <?= $leafRowsCount; ?></div>
        </div>
        <div class="share-kelengkapan-card belum-sesuai">
            <div class="share-kelengkapan-label">Belum Sesuai</div>
            <div class="share-kelengkapan-value"><?= number_format((float) ($kelengkapanPercentage['belum_sesuai_persen'] ?? 0), 2, ',', '.'); ?>%</div>
            <div class="share-kelengkapan-count"><?= $belumSesuaiCount; ?> dari <?= $leafRowsCount; ?></div>
        </div>
        <div class="share-kelengkapan-card menunggu-verifikasi">
            <div class="share-kelengkapan-label">Menunggu Verifikasi</div>
            <div class="share-kelengkapan-value"><?= number_format((float) ($kelengkapanPercentage['belum_verifikasi_persen'] ?? 0), 2, ',', '.'); ?>%</div>
            <div class="share-kelengkapan-count"><?= $menungguVerifikasiCount; ?> dari <?= $leafRowsCount; ?></div>
        </div>
        <div class="share-kelengkapan-card belum-ada">
            <div class="share-kelengkapan-label">Belum Ada</div>
            <div class="share-kelengkapan-value"><?= number_format((float) ($kelengkapanPercentage['belum_ada_persen'] ?? 0), 2, ',', '.'); ?>%</div>
            <div class="share-kelengkapan-count"><?= $belumAdaCount; ?> dari <?= $leafRowsCount; ?></div>
        </div>
    </div>

    <div class="google-auth-card">
        <div class="d-flex align-items-start justify-content-between flex-wrap" style="gap: 12px;">
            <div>
                <h5 class="mb-1">Login Google terlebih dahulu</h5>
                <p class="text-muted mb-0">Login dibuka sebagai popup dan identitas Google akan dipakai untuk mencatat siapa yang mengupload dokumen.<?php if (! empty($ciEnvironment) && strtolower((string) $ciEnvironment) === 'development'): ?><br><span class="badge badge-warning mt-2" style="display: inline-block;">DEVELOPMENT MODE - Login dilewati</span><?php endif; ?></p>
            </div>
            <div class="google-auth-actions">
                <button type="button" class="google-login-btn" id="googlePopupLoginBtn">
                    <svg viewBox="0 0 48 48" aria-hidden="true" focusable="false"><path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303C33.626 32.91 29.304 36 24 36c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.153 7.962 3.037l5.657-5.657C34.007 6.053 29.311 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.652-.389-3.917z"/><path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.153 7.962 3.037l5.657-5.657C34.007 6.053 29.311 4 24 4c-7.159 0-13.315 4.034-16.694 9.691z"/><path fill="#4CAF50" d="M24 44c5.237 0 9.834-1.99 13.364-5.237l-6.16-5.194C29.151 35.091 26.715 36 24 36c-5.282 0-9.593-3.073-11.288-7.283l-6.52 5.025C9.534 39.556 16.227 44 24 44z"/><path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-1.017 2.877-2.92 5.247-5.099 6.569.002-.001.004-.002.006-.003l6.16 5.194C35.96 38.713 40 34 40 24c0-1.341-.138-2.652-.389-3.917z"/></svg>
                    <span>Login dengan Google</span>
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm d-none" id="googleSignOutButton">Logout Google</button>
            </div>
        </div>
        <div class="google-auth-status">
            <span class="google-auth-pill d-none" id="googleAuthUser">Belum login</span>
            <span class="text-muted small" id="googleAuthHint">Silakan login dengan akun Google sebelum membuka form upload.</span>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc((string) session()->getFlashdata('success')); ?></div>
    <?php endif; ?>

    <?php $querySuccess = trim((string) service('request')->getGet('success')); ?>
    <?php if ($querySuccess !== ''): ?>
        <div class="alert alert-success"><?= esc($querySuccess); ?></div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc((string) session()->getFlashdata('error')); ?></div>
    <?php endif; ?>

    <?php $queryError = trim((string) service('request')->getGet('error')); ?>
    <?php if ($queryError !== ''): ?>
        <div class="alert alert-danger"><?= esc($queryError); ?></div>
    <?php endif; ?>

    <div class="alert alert-info">
        Halaman ini hanya untuk upload dokumen oleh kontraktor. Setelah file berhasil diupload, status kelengkapan dokumen otomatis berubah menjadi <strong>Ada</strong>.
    </div>

    <?php if ($sections === []): ?>
        <div class="alert alert-warning">Template SIMAK belum tersedia.</div>
    <?php else: ?>
        <ul class="nav nav-tabs" id="shareTabs" role="tablist">
            <?php $tabIndex = 0; foreach ($sections as $sectionKey => $section): ?>
                <li class="nav-item" role="presentation">
                    <a
                        class="nav-link <?= $tabIndex === 0 ? 'active' : ''; ?>"
                        id="share-tab-<?= esc($sectionKey); ?>"
                        data-toggle="tab"
                        href="#share-panel-<?= esc($sectionKey); ?>"
                        role="tab"
                    ><?= esc((string) ($section['label'] ?? $sectionKey)); ?></a>
                </li>
            <?php $tabIndex++; endforeach; ?>
        </ul>

        <div class="tab-content bg-white border border-top-0 p-3 share-tabs-wrap">
            <?php $tabIndex = 0; foreach ($sections as $sectionKey => $section): ?>
                <div class="tab-pane fade <?= $tabIndex === 0 ? 'show active' : ''; ?>" id="share-panel-<?= esc($sectionKey); ?>" role="tabpanel">
                    <div class="share-table-shell">
                    <div class="table-responsive table-share-wrap">
                        <table class="table table-bordered table-sm mb-0 table-share-simak">
                            <thead class="thead-dark">
                                <tr>
                                    <th style="width: 90px;">No</th>
                                    <th>Uraian</th>
                                    <th style="width: 140px;">Status Dokumen</th>
                                    <th style="width: 160px;">Verifikasi Dit. KI</th>
                                    <th style="width: 260px;">Keterangan</th>
                                    <th style="width: 250px;">Dokumen Terakhir</th>
                                    <th style="width: 380px;">Upload</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach (($section['rows'] ?? []) as $row): ?>
                                <?php
                                    $rowNo = (int) ($row['row_no'] ?? 0);
                                    $displayNo = trim((string) ($row['display_no'] ?? ''));
                                    $rowType = (string) ($row['row_type'] ?? 'detail');
                                    $isLeaf = (bool) ($row['is_leaf'] ?? false);
                                    $hasChildren = (bool) ($row['has_children'] ?? false);
                                    $indentLevel = (int) ($row['indent_level'] ?? 0);
                                    $indentPadding = max(0, $indentLevel) * 16;
                                    $isGroup = $hasChildren
                                        || $rowType === 'section_header'
                                        || ($rowType === 'subsection_header' && ! $isLeaf);
                                    $rowClass = $isGroup ? 'row-group' : 'row-leaf';
                                    $isInputRow = $isLeaf;
                                    $existing = $verifikasiByRow[$rowNo] ?? [];
                                    $kelengkapan = strtolower(trim((string) ($existing['kelengkapan_dokumen'] ?? '')));
                                    $verifikasi = strtolower(trim((string) ($existing['verifikasi_ki'] ?? '')));
                                    $keterangan = trim((string) ($existing['keterangan'] ?? ''));
                                    $isBelumSesuai = $verifikasi === 'tidak_sesuai';
                                    $dokumenRows = $dokumenByRow[$rowNo] ?? [];
                                    $latestDokumen = $dokumenRows[0] ?? null;
                                    $isLockedUpload = $verifikasi === 'sesuai' && is_array($latestDokumen);
                                ?>
                                <tr class="<?= esc($rowClass); ?>">
                                    <td class="cell-hierarchy-no" style="padding-left: <?= (int) $indentPadding; ?>px;"><?= esc($displayNo !== '' ? $displayNo . '.' : '-'); ?></td>
                                    <td class="cell-hierarchy-uraian" style="padding-left: <?= (int) ($indentPadding + 6); ?>px;"><?= esc((string) ($row['uraian'] ?? '-')); ?></td>
                                    <?php if ($isInputRow): ?>
                                        <td class="cell-center">
                                            <?php if ($kelengkapan === 'ada'): ?>
                                                <span class="badge badge-kel-ada">Ada</span>
                                            <?php elseif ($kelengkapan === 'tidak'): ?>
                                                <span class="badge badge-kel-tidak">Tidak</span>
                                            <?php else: ?>
                                                <span class="text-muted">Belum ada</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="cell-center <?= $isBelumSesuai ? 'cell-belum-sesuai' : ''; ?>">
                                            <?php if ($verifikasi === 'sesuai'): ?>
                                                <span class="badge badge-success">Sesuai</span>
                                            <?php elseif ($verifikasi === 'tidak_sesuai'): ?>
                                                <span class="badge badge-warning">Belum Sesuai</span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="cell-center <?= $isBelumSesuai ? 'cell-belum-sesuai' : ''; ?>">
                                            <?= $keterangan !== '' ? esc($keterangan) : '<span class="text-muted">-</span>'; ?>
                                        </td>
                                        <td class="cell-center">
                                            <?php if (is_array($latestDokumen)): ?>
                                                <?php $uploadDate = date('d-m-Y', strtotime((string) ($latestDokumen['created_at'] ?? ''))); ?>
                                                <?php
                                                    $latestPath = trim((string) ($latestDokumen['file_relative_path'] ?? ''));
                                                    $latestHost = strtolower((string) parse_url($latestPath, PHP_URL_HOST));
                                                    $isDriveLink = in_array($latestHost, ['drive.google.com', 'docs.google.com'], true);
                                                        $latestMime = strtolower(trim((string) ($latestDokumen['file_mime'] ?? '')));
                                                        $latestName = (string) ($latestDokumen['file_original_name'] ?? 'dokumen');
                                                        $latestExt = strtolower((string) pathinfo($latestName, PATHINFO_EXTENSION));
                                                        $isPreviewableDokumen = ! $isDriveLink && (
                                                            ($latestMime !== '' && (str_starts_with($latestMime, 'image/') || $latestMime === 'application/pdf'))
                                                            || in_array($latestExt, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'pdf'], true)
                                                        );
                                                        $dokumenActionLabel = $isDriveLink ? 'Buka Link' : ($isPreviewableDokumen ? 'Lihat Dokumen' : 'Download Dokumen');
                                                ?>
                                                <div>
                                                        <?php if ($isDriveLink): ?>
                                                            <a href="<?= esc($latestPath); ?>" class="btn btn-success btn-sm" target="_blank" rel="noopener noreferrer">
                                                                <i class="fas fa-external-link-alt"></i> <?= esc($dokumenActionLabel); ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <a
                                                                href="<?= site_url('simak/share/' . (string) ($token ?? '') . '/download-dokumen/' . (int) ($latestDokumen['id'] ?? 0)); ?>"
                                                                class="btn btn-success btn-sm js-open-dokumen"
                                                                data-dokumen-url="<?= site_url('simak/share/' . (string) ($token ?? '') . '/download-dokumen/' . (int) ($latestDokumen['id'] ?? 0)); ?>"
                                                                data-dokumen-name="<?= esc($latestName); ?>"
                                                                data-dokumen-mime="<?= esc($latestMime); ?>"
                                                                data-dokumen-previewable="<?= $isPreviewableDokumen ? '1' : '0'; ?>"
                                                            >
                                                                <i class="fas <?= $isPreviewableDokumen ? 'fa-eye' : 'fa-download'; ?>"></i> <?= esc($dokumenActionLabel); ?>
                                                            </a>
                                                        <?php endif; ?>
                                                </div>
                                                <small class="text-muted" style="display: block; margin-top: 4px;">Di Upload Tanggal<br/><?= esc($uploadDate); ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="cell-center">
                                            <?php if ($isLockedUpload): ?>
                                                <span class="badge badge-success">Sudah Sesuai</span>
                                                <small class="d-block text-muted mt-1">Upload tidak diperlukan</small>
                                            <?php else: ?>
                                                <button
                                                    type="button"
                                                    class="btn btn-primary btn-sm js-open-upload-modal"
                                                    data-toggle="modal"
                                                    data-target="#modal-upload-share-simak"
                                                    data-row-no="<?= esc((string) $rowNo); ?>"
                                                    data-row-label="<?= esc($displayNo !== '' ? $displayNo . '.' : '-'); ?>"
                                                    data-uraian="<?= esc((string) ($row['uraian'] ?? '-')); ?>"
                                                >Upload</button>
                                            <?php endif; ?>
                                        </td>
                                    <?php else: ?>
                                        <td colspan="5" class="text-muted cell-center">Baris grup (tidak perlu upload)</td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    </div>
                </div>
            <?php $tabIndex++; endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="modal-upload-share-simak" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Dokumen</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-upload-share-simak" action="<?= site_url('simak/share/' . (string) ($token ?? '') . '/upload'); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <input type="hidden" name="row_no" id="upload_row_no_modal" value="">
                <input type="hidden" name="google_access_token" id="google_access_token" value="">
                <input type="hidden" name="uploader_name" id="uploader_name" value="">
                <input type="hidden" name="uploader_email" id="uploader_email" value="">
                <input type="hidden" name="uploader_sub" id="uploader_sub" value="">
                <div class="modal-body">
                    <div class="upload-lock-overlay" id="uploadGoogleLock">
                        Login Google terlebih dahulu untuk mengaktifkan upload file lokal atau link Google Drive.
                    </div>
                    <div class="alert alert-light border py-2">
                        <div><strong>No:</strong> <span id="upload_row_label_modal">-</span></div>
                        <div><strong>Uraian:</strong> <span id="upload_row_uraian_modal">-</span></div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="upload_method">Metode Upload</label>
                        <select id="upload_method" name="upload_method" class="form-control">
                            <option value="file">Upload File</option>
                            <option value="drive">Link Google Drive</option>
                        </select>
                    </div>

                    <div class="form-group mb-3" id="uploadFileGroup">
                        <label for="dokumen_file_modal">File Dokumen</label>
                        <input type="file" id="dokumen_file_modal" name="dokumen_file" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.zip">
                        <small class="text-muted">Format: PDF, JPG, PNG, DOC, DOCX, XLS, XLSX, ZIP. Maksimal 5MB. Untuk file di atas 5MB, gunakan link Google Drive.</small>
                        <div id="upload_file_status" class="small mt-2 text-muted">Status file: belum dipilih.</div>
                    </div>

                    <div class="form-group mb-3 d-none" id="uploadDriveGroup">
                        <label for="google_drive_link_modal">Link Google Drive</label>
                        <input type="url" id="google_drive_link_modal" name="google_drive_link" class="form-control" placeholder="https://drive.google.com/...">
                        <small class="text-muted">Gunakan link file dari Google Drive atau Google Docs.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="dokumenPreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0" id="dokumenPreviewTitle">Preview Dokumen</h5>
                    <small class="text-muted" id="dokumenPreviewMeta">-</small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body bg-light">
                <div id="dokumenPreviewBody" class="text-center"></div>
            </div>
            <div class="modal-footer justify-content-between">
                <small class="text-muted">Gunakan tombol Download jika Anda ingin menyimpan file ke perangkat.</small>
                <div>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <a href="#" class="btn btn-primary" id="dokumenPreviewDownloadBtn" target="_blank" rel="noopener noreferrer">Download</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {

    var googleClientId = <?= json_encode((string) ($googleClientId ?? ''), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    var googleDriveUploadFolderId = <?= json_encode((string) ($googleDriveUploadFolderId ?? ''), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    var googleDriveUploadFolderUrl = <?= json_encode((string) ($googleDriveUploadFolderUrl ?? ''), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    var ciEnvironment = <?= json_encode((string) ($ciEnvironment ?? ''), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    var isDevMode = String(ciEnvironment || '').toLowerCase() === 'development';
    var googleProfile = null;
    var googleStorageKey = 'simak-share-google-credential';
    var googleTokenClient = null;
    var pendingUploadContext = null;
    var isSubmitting = false;
    var autoCompressThresholdBytes = 5 * 1024 * 1024;
    var maxUploadBytes = 100 * 1024 * 1024;

    function formatBytes(bytes) {
        var size = Number(bytes || 0);
        if (!Number.isFinite(size) || size <= 0) {
            return '0 B';
        }

        var units = ['B', 'KB', 'MB', 'GB'];
        var unitIndex = 0;
        while (size >= 1024 && unitIndex < units.length - 1) {
            size = size / 1024;
            unitIndex++;
        }

        var precision = unitIndex === 0 ? 0 : 1;
        return size.toFixed(precision) + ' ' + units[unitIndex];
    }

    function getFileExtension(fileName) {
        var value = String(fileName || '').trim().toLowerCase();
        if (!value || value.indexOf('.') === -1) {
            return '';
        }

        return value.split('.').pop();
    }

    function canAutoCompress(ext) {
        return ['jpg', 'jpeg', 'png', 'pdf'].indexOf(String(ext || '').toLowerCase()) !== -1;
    }

    function showCompressionProgress(title, detail, percent) {
        if (!uploadFileStatusEl) {
            return;
        }

        var message = String(title || '').trim();
        if (detail) {
            message += ' - ' + String(detail || '').trim();
        }

        if (Number.isFinite(Number(percent))) {
            message += ' (' + Math.max(0, Math.min(100, Math.round(Number(percent)))) + '%)';
        }

        setFileStatus(message, 'info');
    }

    async function optimizePdfFile(file) {
        if (!window.PDFLib || !window.PDFLib.PDFDocument) {
            return file;
        }

        try {
            showCompressionProgress('Mengoptimasi PDF', 'Menganalisis struktur dokumen...', 15);
            var arrayBuffer = await file.arrayBuffer();
            var sourceDoc = await window.PDFLib.PDFDocument.load(arrayBuffer, { updateMetadata: false });

            showCompressionProgress('Mengoptimasi PDF', 'Membangun ulang halaman PDF...', 45);
            var rebuiltDoc = await window.PDFLib.PDFDocument.create();

            try {
                if (sourceDoc.getForm) {
                    var form = sourceDoc.getForm();
                    if (form) {
                        form.flatten();
                    }
                }
            } catch (formError) {
                // Abaikan jika PDF tidak memiliki form atau flatten gagal.
            }

            var pageIndices = sourceDoc.getPageIndices();
            var copiedPages = await rebuiltDoc.copyPages(sourceDoc, pageIndices);
            copiedPages.forEach(function (page) {
                rebuiltDoc.addPage(page);
            });

            showCompressionProgress('Mengoptimasi PDF', 'Menyimpan ulang PDF dengan struktur yang lebih efisien...', 80);
            var optimizedBytes = await rebuiltDoc.save({
                useObjectStreams: true,
                addDefaultPage: false,
                updateFieldAppearances: false,
            });
            showCompressionProgress('Mengoptimasi PDF', 'Finalisasi hasil kompresi PDF...', 100);

            if (!optimizedBytes || optimizedBytes.length >= file.size) {
                return file;
            }

            return new File([optimizedBytes], file.name, {
                type: 'application/pdf',
                lastModified: Date.now(),
            });
        } catch (error) {
            return file;
        }
    }

    async function maybeCompressUploadFile(file) {
        if (!file || !file.size || file.size <= autoCompressThresholdBytes) {
            return { file: file, compressed: false, skipped: true, ext: '' };
        }

        var ext = getFileExtension(file.name);
        if (!canAutoCompress(ext)) {
            return { file: file, compressed: false, skipped: true, ext: ext };
        }

        var resultFile = file;
        if (ext === 'pdf') {
            resultFile = await optimizePdfFile(file);
        } else {
            resultFile = await compressImageFile(file);
        }

        var compressed = !!(resultFile && resultFile.size && resultFile.size < file.size);
        return {
            file: resultFile || file,
            compressed: compressed,
            skipped: false,
            ext: ext,
        };
    }

    function replaceSelectedFile(file) {
        if (!dokumenFileEl || !file || typeof DataTransfer === 'undefined') {
            return false;
        }

        var dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        dokumenFileEl.files = dataTransfer.files;
        return true;
    }

    function extractDriveFolderIdFromUrl(url) {
        var value = String(url || '').trim();
        if (!value) {
            return '';
        }

        var folderMatch = value.match(/\/folders\/([a-zA-Z0-9_-]+)/);
        if (folderMatch && folderMatch[1]) {
            return String(folderMatch[1]).trim();
        }

        try {
            var parsed = new URL(value);
            var queryId = String(parsed.searchParams.get('id') || '').trim();
            if (/^[a-zA-Z0-9_-]+$/.test(queryId)) {
                return queryId;
            }
        } catch (error) {
            return '';
        }

        return '';
    }

    function resolveDriveUploadFolderId() {
        var directId = String(googleDriveUploadFolderId || '').trim();
        if (directId) {
            return directId;
        }

        return extractDriveFolderIdFromUrl(googleDriveUploadFolderUrl);
    }

    async function uploadFileDirectToGoogleDrive(file, accessToken, folderId) {
        if (isDevMode) {
            var devFileName = String(file && file.name ? file.name : 'dokumen-' + Date.now());
            var devLink = 'https://drive.google.com/file/d/dev-mode-dummy-' + Date.now() + '/view';
            return {
                id: 'dev-mode-dummy-' + Date.now(),
                link: devLink,
                name: devFileName,
            };
        }

        var metadata = {
            name: String(file && file.name ? file.name : 'dokumen'),
            parents: [String(folderId || '').trim()],
        };

        var formData = new FormData();
        formData.append('metadata', new Blob([JSON.stringify(metadata)], { type: 'application/json' }));
        formData.append('file', file);

        var endpoint = 'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&supportsAllDrives=true&fields=id,name,webViewLink,webContentLink';
        var response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                Authorization: 'Bearer ' + accessToken,
            },
            body: formData,
        });

        var payload = null;
        try {
            payload = await response.json();
        } catch (error) {
            payload = null;
        }

        if (!response.ok) {
            var rawMessage = payload && payload.error && payload.error.message ? String(payload.error.message) : 'Upload ke Google Drive gagal.';
            throw new Error(rawMessage);
        }

        var fileId = payload && payload.id ? String(payload.id).trim() : '';
        var webViewLink = payload && payload.webViewLink ? String(payload.webViewLink).trim() : '';
        var finalLink = webViewLink || (fileId ? ('https://drive.google.com/file/d/' + fileId + '/view') : '');

        if (!finalLink) {
            throw new Error('Google Drive tidak mengembalikan link file.');
        }

        return {
            id: fileId,
            link: finalLink,
            name: payload && payload.name ? String(payload.name) : '',
        };
    }

    function isAllowedGoogleDriveUrl(url) {
        var value = String(url || '').trim();
        if (!value) {
            return false;
        }

        try {
            var parsed = new URL(value);
            var host = String(parsed.hostname || '').toLowerCase();
            return host === 'drive.google.com' || host === 'docs.google.com';
        } catch (error) {
            return false;
        }
    }

    function setUploadContext(context) {
        context = context || {};

        if (rowNoEl) {
            rowNoEl.value = String(context.rowNo || '').trim();
        }
        if (rowLabelEl) {
            rowLabelEl.textContent = String(context.rowLabel || '-').trim() || '-';
        }
        if (rowUraianEl) {
            rowUraianEl.textContent = String(context.rowUraian || '-').trim() || '-';
        }
        if (dokumenFileEl) {
            dokumenFileEl.value = '';
        }
        if (googleDriveLinkEl) {
            googleDriveLinkEl.value = '';
        }
        if (uploadMethodEl) {
            uploadMethodEl.value = 'file';
        }
        syncUploadMethodUI();
        refreshFileStatus();
    }

    function syncUploadMethodUI() {
        var method = String(uploadMethodEl && uploadMethodEl.value ? uploadMethodEl.value : 'file').toLowerCase();
        var useDrive = method === 'drive';

        if (uploadFileGroupEl) {
            uploadFileGroupEl.classList.toggle('d-none', useDrive ? true : false);
        }
        if (uploadDriveGroupEl) {
            uploadDriveGroupEl.classList.toggle('d-none', !useDrive);
        }

        if (useDrive && dokumenFileEl) {
            dokumenFileEl.value = '';
        }
        if (!useDrive && googleDriveLinkEl) {
            googleDriveLinkEl.value = '';
        }

        refreshFileStatus();
    }

    function handleDokumenFileChange() {
        if (!dokumenFileEl || !dokumenFileEl.files || !dokumenFileEl.files.length) {
            refreshFileStatus();
            return;
        }

        var selectedFile = dokumenFileEl.files[0];
        if (enforceGoogleDriveOnlyForLargeFile(selectedFile)) {
            refreshFileStatus();
            return;
        }

        refreshFileStatus();
    }

    function getUploadContextFromButton(button) {
        return {
            rowNo: String(button && button.getAttribute('data-row-no') || '').trim(),
            rowLabel: String(button && button.getAttribute('data-row-label') || '-').trim(),
            rowUraian: String(button && button.getAttribute('data-uraian') || '-').trim(),
        };
    }

    function openUploadModal() {
        if (!uploadModalEl) {
            return;
        }

        if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
            window.jQuery(uploadModalEl).modal('show');
        }
    }

    function flushPendingUploadContext() {
        if (!pendingUploadContext || !googleAccessTokenEl || !googleAccessTokenEl.value) {
            return;
        }

        setUploadContext(pendingUploadContext);
        pendingUploadContext = null;
        openUploadModal();
    }

    function saveGoogleCredential(credential) {
        try {
            if (!credential) {
                window.localStorage.removeItem(googleStorageKey);
                return;
            }

            window.localStorage.setItem(googleStorageKey, credential);
        } catch (error) {
            // Ignore storage errors.
        }
    }

    function loadGoogleCredential() {
        try {
            return window.localStorage.getItem(googleStorageKey) || '';
        } catch (error) {
            return '';
        }
    }

    function updateUploadLockState() {
        var googleSignedIn = !!(googleProfile && googleAccessTokenEl && googleAccessTokenEl.value);
        var signedIn = googleSignedIn || isDevMode;

        openButtons.forEach(function (button) {
            button.disabled = false;
        });

        if (uploadSubmitButton) {
            uploadSubmitButton.disabled = !signedIn;
        }

        if (uploadGoogleLock) {
            uploadGoogleLock.classList.toggle('d-none', signedIn);
        }

        if (googleAuthUser) {
            googleAuthUser.classList.toggle('d-none', !googleSignedIn);
            if (googleSignedIn) {
                googleAuthUser.textContent = (googleProfile.name || 'Google User') + ' · ' + (googleProfile.email || '-');
            }
        }

        if (googleSignOutButton) {
            googleSignOutButton.classList.toggle('d-none', !googleSignedIn);
        }

        if (googleAuthHint) {
            googleAuthHint.textContent = googleSignedIn
                ? 'Anda sudah login dengan Google dan siap mengupload dokumen lokal atau memakai link Google Drive.'
                : 'Login Google dipakai untuk upload file lokal dan link Google Drive.';
        }

    }

    function clearGoogleProfile() {
        googleProfile = null;
        if (googleAccessTokenEl) googleAccessTokenEl.value = '';
        if (uploaderNameEl) uploaderNameEl.value = '';
        if (uploaderEmailEl) uploaderEmailEl.value = '';
        if (uploaderSubEl) uploaderSubEl.value = '';
        saveGoogleCredential('');
        updateUploadLockState();
    }

    function applyGoogleProfile(profile, accessToken, persist) {
        profile = profile || {};
        accessToken = String(accessToken || '').trim();

        if (!accessToken || !profile.email) {
            clearGoogleProfile();
            return;
        }

        googleProfile = {
            name: String(profile.name || profile.given_name || 'Google User'),
            email: String(profile.email || ''),
            sub: String(profile.sub || profile.id || ''),
        };

        if (googleAccessTokenEl) googleAccessTokenEl.value = accessToken;
        if (uploaderNameEl) uploaderNameEl.value = googleProfile.name;
        if (uploaderEmailEl) uploaderEmailEl.value = googleProfile.email;
        if (uploaderSubEl) uploaderSubEl.value = googleProfile.sub;

        if (persist !== false) {
            saveGoogleCredential(accessToken);
        }

        updateUploadLockState();
        flushPendingUploadContext();

        if (window.Swal && persist !== false) {
            window.Swal.fire({
                toast: true,
                icon: 'success',
                title: 'Login Google berhasil',
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
            });
        }
    }

    function loadGoogleUserInfo(accessToken) {
        return fetch('https://www.googleapis.com/oauth2/v3/userinfo', {
            headers: {
                Authorization: 'Bearer ' + accessToken,
            },
        }).then(function (response) {
            if (!response.ok) {
                throw new Error('Gagal mengambil profil Google.');
            }

            return response.json();
        });
    }

    function handleGoogleTokenResponse(response) {
        var accessToken = String(response && response.access_token ? response.access_token : '').trim();
        if (!accessToken) {
            clearGoogleProfile();
            return;
        }

        loadGoogleUserInfo(accessToken)
            .then(function (profile) {
                applyGoogleProfile(profile, accessToken, true);
            })
            .catch(function (error) {
                if (window.Swal) {
                    window.Swal.fire({ icon: 'error', title: 'Login Google gagal', text: error.message || 'Credential Google tidak valid.' });
                }
                clearGoogleProfile();
            });
    }

    function requestGoogleAccessTokenWithPrompt(promptMode) {
        return new Promise(function (resolve, reject) {
            if (!googleTokenClient) {
                reject(new Error('Google login client belum siap.'));
                return;
            }

            var settled = false;
            googleTokenClient.callback = function (response) {
                if (settled) {
                    return;
                }
                settled = true;

                var responseError = String(response && response.error ? response.error : '').trim();
                if (responseError !== '') {
                    googleTokenClient.callback = handleGoogleTokenResponse;
                    reject(new Error(String(response && response.error_description ? response.error_description : responseError)));
                    return;
                }

                var accessToken = String(response && response.access_token ? response.access_token : '').trim();
                if (!accessToken) {
                    googleTokenClient.callback = handleGoogleTokenResponse;
                    reject(new Error('Google tidak mengembalikan access token.'));
                    return;
                }

                loadGoogleUserInfo(accessToken)
                    .then(function (profile) {
                        applyGoogleProfile(profile, accessToken, true);
                        googleTokenClient.callback = handleGoogleTokenResponse;
                        resolve(accessToken);
                    })
                    .catch(function (error) {
                        clearGoogleProfile();
                        googleTokenClient.callback = handleGoogleTokenResponse;
                        reject(error instanceof Error ? error : new Error('Gagal memuat profil Google.'));
                    });
            };

            googleTokenClient.requestAccessToken({ prompt: promptMode || '' });
        });
    }

    async function ensureGoogleDriveAccessToken() {
        if (isDevMode) {
            var currentDevToken = String(googleAccessTokenEl && googleAccessTokenEl.value ? googleAccessTokenEl.value : '').trim();
            if (currentDevToken !== '') {
                return currentDevToken;
            }
            applyDevModeDummyProfile();
            return String(googleAccessTokenEl && googleAccessTokenEl.value ? googleAccessTokenEl.value : 'dev-mode-dummy-token');
        }

        var currentToken = String(googleAccessTokenEl && googleAccessTokenEl.value ? googleAccessTokenEl.value : '').trim();
        if (!currentToken) {
            return await requestGoogleAccessTokenWithPrompt('consent');
        }

        try {
            return await requestGoogleAccessTokenWithPrompt('');
        } catch (silentError) {
            return await requestGoogleAccessTokenWithPrompt('consent');
        }
    }

    function applyDevModeDummyProfile() {
        if (!isDevMode) {
            return false;
        }

        var dummyProfile = {
            name: 'Development User',
            email: 'dev@localhost',
            sub: 'dev-mode-dummy-sub',
        };

        var dummyToken = 'dev-mode-dummy-token-' + Date.now();

        googleProfile = dummyProfile;
        if (googleAccessTokenEl) googleAccessTokenEl.value = dummyToken;
        if (uploaderNameEl) uploaderNameEl.value = dummyProfile.name;
        if (uploaderEmailEl) uploaderEmailEl.value = dummyProfile.email;
        if (uploaderSubEl) uploaderSubEl.value = dummyProfile.sub;

        saveGoogleCredential(dummyToken);
        updateUploadLockState();
        flushPendingUploadContext();

        if (window.Swal) {
            window.Swal.fire({
                toast: true,
                icon: 'info',
                title: 'Mode Development',
                text: 'Google login dilewati untuk testing.',
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
            });
        }

        return true;
    }

    function initGoogleSignIn() {
        if (isDevMode) {
            applyDevModeDummyProfile();
            return;
        }

        if (!googlePopupLoginBtn || !googleClientId || !window.google || !window.google.accounts || !window.google.accounts.oauth2) {
            if (googlePopupLoginBtn && !googleClientId) {
                googlePopupLoginBtn.disabled = true;
                googlePopupLoginBtn.classList.add('disabled');
                googlePopupLoginBtn.title = 'GOOGLE_CLIENT_ID belum dikonfigurasi';
            }
            return;
        }

        googleTokenClient = window.google.accounts.oauth2.initTokenClient({
            client_id: googleClientId,
            scope: 'openid email profile https://www.googleapis.com/auth/drive.file',
            callback: handleGoogleTokenResponse,
        });

        googlePopupLoginBtn.disabled = false;
        googlePopupLoginBtn.classList.remove('disabled');
        googlePopupLoginBtn.title = '';

        googlePopupLoginBtn.addEventListener('click', function () {
            if (!googleTokenClient) {
                return;
            }

            requestGoogleAccessTokenWithPrompt('consent').catch(function (error) {
                if (window.Swal) {
                    window.Swal.fire({
                        icon: 'error',
                        title: 'Login Google gagal',
                        text: error && error.message ? error.message : 'Autorisasi Google gagal.',
                    });
                }
            });
        });

        if (googleClientId) {
            var storedCredential = loadGoogleCredential();
            if (storedCredential) {
                loadGoogleUserInfo(storedCredential)
                    .then(function (profile) {
                        applyGoogleProfile(profile, storedCredential, false);
                    })
                    .catch(function () {
                        clearGoogleProfile();
                    });
            }
        }
    }

    // Ensure DOM elements are referenced before usage (fix: openButtons undefined)
    var openButtons = document.querySelectorAll('.js-open-upload-modal');
    var uploadModalEl = document.getElementById('modal-upload-share-simak');
    var uploadForm = document.getElementById('form-upload-share-simak');
    var rowNoEl = document.getElementById('upload_row_no_modal');
    var rowLabelEl = document.getElementById('upload_row_label_modal');
    var rowUraianEl = document.getElementById('upload_row_uraian_modal');
    var dokumenFileEl = document.getElementById('dokumen_file_modal');
    var uploadMethodEl = document.getElementById('upload_method');
    var uploadFileGroupEl = document.getElementById('uploadFileGroup');
    var uploadDriveGroupEl = document.getElementById('uploadDriveGroup');
    var googleDriveLinkEl = document.getElementById('google_drive_link_modal');
    var googleAccessTokenEl = document.getElementById('google_access_token');
    var uploaderNameEl = document.getElementById('uploader_name');
    var uploaderEmailEl = document.getElementById('uploader_email');
    var uploaderSubEl = document.getElementById('uploader_sub');
    var uploadFileStatusEl = document.getElementById('upload_file_status');
    var uploadSubmitButton = document.querySelector('#form-upload-share-simak [type="submit"]');
    var uploadGoogleLock = document.getElementById('uploadGoogleLock');
    var googlePopupLoginBtn = document.getElementById('googlePopupLoginBtn');
    var googleSignOutButton = document.getElementById('googleSignOutButton');
    var googleAuthUser = document.getElementById('googleAuthUser');
    var googleAuthHint = document.getElementById('googleAuthHint');
    var dokumenPreviewModalEl = document.getElementById('dokumenPreviewModal');
    var dokumenPreviewTitleEl = document.getElementById('dokumenPreviewTitle');
    var dokumenPreviewMetaEl = document.getElementById('dokumenPreviewMeta');
    var dokumenPreviewBodyEl = document.getElementById('dokumenPreviewBody');
    var dokumenPreviewDownloadBtn = document.getElementById('dokumenPreviewDownloadBtn');
    var currentDokumenPreviewUrl = '';

    function buildDownloadUrl(url, forceDownload) {
        var value = String(url || '').trim();
        if (!value) {
            return '';
        }

        if (!forceDownload) {
            return value;
        }

        return value + (value.indexOf('?') === -1 ? '?' : '&') + 'download=1';
    }

    function clearDokumenPreview() {
        currentDokumenPreviewUrl = '';

        if (dokumenPreviewBodyEl) {
            dokumenPreviewBodyEl.innerHTML = '';
        }

        if (dokumenPreviewMetaEl) {
            dokumenPreviewMetaEl.textContent = '-';
        }

        if (dokumenPreviewDownloadBtn) {
            dokumenPreviewDownloadBtn.href = '#';
        }
    }

    function showGoogleDriveOnlyNotice(fileName, fileSize) {
        var title = 'Gunakan link Google Drive';
        var text = 'File ' + String(fileName || 'dokumen') + ' berukuran ' + formatBytes(fileSize || 0) + '. Untuk file di atas 5MB, gunakan link Google Drive saja. Modal sudah direset.';

        if (window.Swal && typeof window.Swal.fire === 'function') {
            window.Swal.fire({
                icon: 'warning',
                title: title,
                text: text,
            });
        }
    }

    function enforceGoogleDriveOnlyForLargeFile(file) {
        if (!file || !file.size || file.size <= autoCompressThresholdBytes) {
            return false;
        }

        if (uploadMethodEl) {
            uploadMethodEl.value = 'drive';
        }

        if (dokumenFileEl) {
            dokumenFileEl.value = '';
        }

        if (googleDriveLinkEl) {
            googleDriveLinkEl.value = '';
        }

        syncUploadMethodUI();
        showGoogleDriveOnlyNotice(file.name, file.size);

        if (googleDriveLinkEl) {
            googleDriveLinkEl.focus();
        }

        return true;
    }

    function setFileStatus(message, tone) {
        if (!uploadFileStatusEl) {
            return;
        }

        var statusText = String(message || '').trim();
        uploadFileStatusEl.textContent = statusText || 'Status file: belum dipilih.';
        uploadFileStatusEl.className = 'small mt-2 text-muted';

        if (tone === 'success') {
            uploadFileStatusEl.classList.remove('text-muted');
            uploadFileStatusEl.classList.add('text-success');
        } else if (tone === 'warning') {
            uploadFileStatusEl.classList.remove('text-muted');
            uploadFileStatusEl.classList.add('text-warning');
        } else if (tone === 'info') {
            uploadFileStatusEl.classList.remove('text-muted');
            uploadFileStatusEl.classList.add('text-info');
        }
    }

    function refreshFileStatus() {
        if (!dokumenFileEl || !dokumenFileEl.files || !dokumenFileEl.files.length) {
            setFileStatus('Status file: belum dipilih.');
            return;
        }

        var selectedFile = dokumenFileEl.files[0];
        var fileName = String(selectedFile && selectedFile.name ? selectedFile.name : 'dokumen');
        var fileSize = selectedFile && selectedFile.size ? selectedFile.size : 0;

        if (fileSize > autoCompressThresholdBytes) {
            setFileStatus(fileName + ' (' + formatBytes(fileSize) + ') - gunakan link Google Drive.', 'warning');
            return;
        }

        setFileStatus(fileName + ' (' + formatBytes(fileSize) + ')', 'info');
    }

    function openDokumenPreview(url, title, mimeType) {
        if (!dokumenPreviewModalEl || !dokumenPreviewBodyEl) {
            return;
        }

        var finalUrl = buildDownloadUrl(url, false);
        currentDokumenPreviewUrl = finalUrl;

        var normalizedMime = String(mimeType || '').toLowerCase();
        var isPdf = normalizedMime === 'application/pdf' || finalUrl.toLowerCase().indexOf('.pdf') !== -1;
        var isImage = normalizedMime.indexOf('image/') === 0 || /\.(png|jpe?g|gif|webp|bmp)$/i.test(finalUrl);

        if (dokumenPreviewTitleEl) {
            dokumenPreviewTitleEl.textContent = String(title || 'Preview Dokumen');
        }

        if (dokumenPreviewMetaEl) {
            dokumenPreviewMetaEl.textContent = String(mimeType || 'Dokumen');
        }

        if (dokumenPreviewDownloadBtn) {
            dokumenPreviewDownloadBtn.href = buildDownloadUrl(url, true);
        }

        if (isImage) {
            dokumenPreviewBodyEl.innerHTML = '<img src="' + finalUrl.replace(/"/g, '&quot;') + '" alt="Preview dokumen" class="img-fluid rounded" style="max-height:75vh; object-fit:contain;">';
        } else if (isPdf) {
            dokumenPreviewBodyEl.innerHTML = '<iframe src="' + finalUrl.replace(/"/g, '&quot;') + '" title="Preview dokumen" style="width:100%; height:75vh; border:0; background:#fff;"></iframe>';
        } else {
            dokumenPreviewBodyEl.innerHTML = '<div class="alert alert-warning mb-0">File ini tidak memiliki preview bawaan. Gunakan tombol Download.</div>';
        }

        if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
            window.jQuery(dokumenPreviewModalEl).modal('show');
        }
    }

    function isPreviewableDokumen(mimeType, fileName) {
        var normalizedMime = String(mimeType || '').toLowerCase();
        var normalizedName = String(fileName || '').toLowerCase();

        if (normalizedMime.indexOf('image/') === 0 || normalizedMime === 'application/pdf') {
            return true;
        }

        return /\.(png|jpe?g|gif|webp|bmp|pdf)$/i.test(normalizedName);
    }

    openButtons.forEach(function (button) {
        button.addEventListener('click', function (event) {
            setUploadContext(getUploadContextFromButton(this));
        });
    });

    document.querySelectorAll('.js-open-dokumen').forEach(function (button) {
        button.addEventListener('click', function (event) {
            var url = String(this.getAttribute('data-dokumen-url') || this.getAttribute('href') || '').trim();
            var title = String(this.getAttribute('data-dokumen-name') || 'Preview Dokumen').trim();
            var mimeType = String(this.getAttribute('data-dokumen-mime') || '').trim();
            var previewableAttr = String(this.getAttribute('data-dokumen-previewable') || '0').trim();
            var previewable = previewableAttr === '1' || isPreviewableDokumen(mimeType, title);

            if (!url) {
                return;
            }

            event.preventDefault();

            if (previewable) {
                openDokumenPreview(url, title, mimeType);
                return;
            }

            var downloadUrl = buildDownloadUrl(url, true);
            var shouldDownload = window.confirm('File ini tidak dapat dipreview. Apakah Anda ingin mendownload file ini?');
            if (shouldDownload && downloadUrl) {
                window.location.href = downloadUrl;
            }
        });
    });

    if (dokumenPreviewModalEl) {
        dokumenPreviewModalEl.addEventListener('hidden.bs.modal', clearDokumenPreview);
    }

    if (googleSignOutButton) {
        googleSignOutButton.addEventListener('click', function () {
            clearGoogleProfile();
            if (window.google && window.google.accounts && window.google.accounts.id) {
                window.google.accounts.id.prompt();
            }
        });
    }

    if (uploadForm) {
        if (uploadMethodEl) {
            uploadMethodEl.addEventListener('change', syncUploadMethodUI);
            syncUploadMethodUI();
        }

        if (dokumenFileEl) {
            dokumenFileEl.addEventListener('change', handleDokumenFileChange);
        }

        uploadForm.addEventListener('submit', async function (event) {
            if (isSubmitting) {
                return;
            }

            var selectedMethod = String(uploadMethodEl && uploadMethodEl.value ? uploadMethodEl.value : 'file').toLowerCase();
            var hasGoogleToken = !!(googleAccessTokenEl && googleAccessTokenEl.value);

            if (!hasGoogleToken) {
                if (isDevMode) {
                    applyDevModeDummyProfile();
                    hasGoogleToken = true;
                } else {
                    event.preventDefault();
                    if (window.Swal) {
                        window.Swal.fire({
                            icon: 'warning',
                            title: 'Login Google diperlukan',
                            text: 'Anda harus login dengan Google sebelum upload dokumen.',
                        });
                    }
                    return;
                }
            }

            var hasFile = !!(dokumenFileEl && dokumenFileEl.files && dokumenFileEl.files.length > 0);
            var driveLink = String(googleDriveLinkEl && googleDriveLinkEl.value ? googleDriveLinkEl.value : '').trim();
            var hasDriveLink = driveLink !== '';
            var rowNoValue = parseInt(String(rowNoEl && rowNoEl.value ? rowNoEl.value : '0').trim(), 10) || 0;

            if (rowNoValue <= 0) {
                event.preventDefault();
                if (window.Swal) {
                    window.Swal.fire({
                        icon: 'warning',
                        title: 'Baris belum dipilih',
                        text: 'Silakan tutup dialog, lalu klik tombol Upload pada baris dokumen yang ingin dikirim.',
                    });
                }
                return;
            }

            if (selectedMethod === 'drive' && !hasDriveLink) {
                event.preventDefault();
                if (window.Swal) {
                    window.Swal.fire({
                        icon: 'warning',
                        title: 'Dokumen belum diisi',
                        text: 'Silakan isi link Google Drive.',
                    });
                }
                return;
            }

            if (selectedMethod !== 'drive' && hasFile && dokumenFileEl && dokumenFileEl.files[0] && dokumenFileEl.files[0].size > autoCompressThresholdBytes) {
                event.preventDefault();
                enforceGoogleDriveOnlyForLargeFile(dokumenFileEl.files[0]);
                return;
            }

            if (selectedMethod !== 'drive' && hasFile && dokumenFileEl && dokumenFileEl.files[0]) {
                event.preventDefault();
                var sourceFile = dokumenFileEl.files[0];
                var sourceExt = getFileExtension(sourceFile.name);
                var shouldCompress = sourceFile.size > autoCompressThresholdBytes && canAutoCompress(sourceExt);
                var finalFile = sourceFile;

                if (shouldCompress) {
                    setFileStatus('Sedang kompresi di browser...', 'info');
                    if (window.Swal && typeof window.Swal.fire === 'function') {
                        window.Swal.fire({
                            title: 'Mengompres file...',
                            html: '<div class="text-left"><div class="font-weight-semibold">Menyiapkan kompresi...</div><div class="small text-muted mt-1">File lebih dari 5MB, sistem sedang mencoba mengecilkan ukuran sebelum upload.</div><div class="progress mt-2" style="height: 12px;"><div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div></div></div>',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: function () {
                                window.Swal.showLoading();
                            }
                        });
                    }

                    var compressionResult = await maybeCompressUploadFile(sourceFile);
                    finalFile = compressionResult.file || sourceFile;

                    if (compressionResult.compressed) {
                        replaceSelectedFile(finalFile);
                        setFileStatus('Kompresi selesai: ' + formatBytes(sourceFile.size) + ' → ' + formatBytes(finalFile.size), 'success');
                    }

                    if (finalFile.size > maxUploadBytes) {
                        setFileStatus('File besar: ' + formatBytes(finalFile.size) + ' - akan diupload ke server.', 'info');
                    }

                    if (window.Swal) {
                        if (compressionResult.compressed) {
                            var savingPercent = formatCompressionSaving(sourceFile.size, finalFile.size);
                            var savingPercentValue = sourceFile.size > 0 ? ((sourceFile.size - finalFile.size) / sourceFile.size) * 100 : 0;
                            var isPdfFile = sourceExt === 'pdf';
                            var pdfHintHtml = '';
                            if (isPdfFile && savingPercentValue < 10) {
                                pdfHintHtml = '<br><span class="small text-warning">Catatan: PDF sering sulit dikompres signifikan di browser.</span>';
                            }
                            await window.Swal.fire({
                                icon: 'success',
                                title: 'Kompresi berhasil',
                                html: '<div class="text-left">Ukuran file: ' + formatBytes(sourceFile.size) + ' → ' + formatBytes(finalFile.size) + '<br><span class="small text-muted">Hemat ukuran: ' + savingPercent + '</span>' + pdfHintHtml + '</div>',
                                timer: 1800,
                                showConfirmButton: false,
                            });
                        } else {
                            setFileStatus('Kompresi tidak mengubah ukuran file.', 'warning');
                            await window.Swal.fire({
                                icon: 'info',
                                title: 'Kompresi tidak mengubah ukuran',
                                text: 'File tetap diupload dengan ukuran asli: ' + formatBytes(sourceFile.size),
                                timer: 1700,
                                showConfirmButton: false,
                            });
                        }
                    }
                }

                if (!shouldCompress && sourceFile.size > maxUploadBytes) {
                    setFileStatus('Ukuran file >100MB.', 'warning');
                }

                if (window.Swal && typeof window.Swal.fire === 'function') {
                    window.Swal.fire({
                        title: 'Mengunggah dokumen...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: function () {
                            window.Swal.showLoading();
                        }
                    });
                }

                isSubmitting = true;
                uploadForm.submit();
                return;
            }

            if (selectedMethod === 'drive' && !isAllowedGoogleDriveUrl(driveLink)) {
                event.preventDefault();
                if (window.Swal) {
                    window.Swal.fire({
                        icon: 'warning',
                        title: 'Link tidak valid',
                        text: 'Gunakan link dari drive.google.com atau docs.google.com.',
                    });
                }
                return;
            }

            if (window.Swal && typeof window.Swal.fire === 'function') {
                window.Swal.fire({
                    title: 'Mengunggah dokumen...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: function () {
                        window.Swal.showLoading();
                    }
                });
            }

            isSubmitting = true;
        });

        updateUploadLockState();
        refreshFileStatus();
        if (googleClientId) {
            if (window.google && window.google.accounts && window.google.accounts.id) {
                initGoogleSignIn();
            } else {
                window.addEventListener('load', initGoogleSignIn, { once: true });
            }
        }
    }
})();
</script>
</body>
</html>
