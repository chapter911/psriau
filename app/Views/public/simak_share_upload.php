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
    <style>
        body {
            background: linear-gradient(180deg, #eef3f8 0%, #f8fbff 100%);
            color: #1f2a37;
        }

        .share-wrapper {
            max-width: 1200px;
            margin: 24px auto;
            padding: 0 12px;
        }

        .share-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.08);
            border: 1px solid #e5eaf1;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .table-share-simak thead th {
            text-align: center;
            vertical-align: middle;
            position: sticky;
            top: 0;
            z-index: 5;
            background-color: #343a40;
            border-color: #454d55;
        }

        .table-share-simak .cell-center {
            text-align: center;
        }

        .table-share-simak .row-group {
            background-color: #f2f4f7;
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

        .doc-meta {
            font-size: 12px;
            color: #6b7280;
        }

        .badge-kel-ada {
            background: #198754;
            color: #fff;
        }

        .badge-kel-tidak {
            background: #dc3545;
            color: #fff;
        }

        .cell-belum-sesuai {
            background-color: #fff3cd;
        }

        .share-upload-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .share-upload-row .share-upload-item {
            margin: 0;
        }

        .table-share-wrap {
            max-height: 72vh;
            overflow: auto;
        }

        .share-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .share-brand-logo {
            width: 48px;
            height: 48px;
            object-fit: contain;
            border-radius: 8px;
            background: #fff;
            border: 1px solid #d8dee4;
            padding: 5px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
        }

        .share-brand-text {
            display: flex;
            flex-direction: column;
        }

        .share-brand-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1f2a37;
            margin: 0;
        }

        .share-brand-sub {
            font-size: 0.85rem;
            color: #6b7280;
            margin: 0;
        }

        .share-kelengkapan-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }

        .share-kelengkapan-card {
            background: #fff;
            border-radius: 8px;
            padding: 14px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .share-kelengkapan-card.lengkap {
            border-left: 4px solid #198754;
        }

        .share-kelengkapan-card.belum-sesuai {
            border-left: 4px solid #ffc107;
        }

        .share-kelengkapan-card.menunggu-verifikasi {
            border-left: 4px solid #0dcaf0;
        }

        .share-kelengkapan-card.belum-ada {
            border-left: 4px solid #dc3545;
        }

        .share-kelengkapan-label {
            font-size: 0.8rem;
            color: #6b7280;
            font-weight: 600;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .share-kelengkapan-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
        }
            .share-kelengkapan-count {
                font-size: 0.75rem;
                color: #9ca3af;
                font-weight: 500;
            }

            .google-auth-card {
                background: #fff;
                border: 1px solid #dbe4ef;
                border-radius: 12px;
                padding: 16px;
                margin-bottom: 16px;
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
    </style>
</head>
<body>
<div class="share-wrapper">
    <div class="share-brand">
        <?php if (! empty($globalSetting['logo_url'] ?? '')): ?>
            <img src="<?= esc($globalSetting['logo_url']); ?>" alt="Logo" class="share-brand-logo">
        <?php endif; ?>
        <div class="share-brand-text">
            <p class="share-brand-name"><?= esc((string) ($globalSetting['official_name'] ?? 'Satker PPS Kementerian PU')); ?></p>
            <p class="share-brand-sub">Upload Dokumen SIMAK</p>
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

    <div class="share-card p-3 p-md-4 mb-3">
        <h5 class="mb-1"><?= esc((string) ($item['nama_paket'] ?? '-')); ?></h5>
        <div class="text-muted mb-0">
            Nomor Kontrak: <?= esc((string) ($item['nomor_kontrak'] ?? '-')); ?>
            · Tahun Anggaran: <?= esc((string) ($item['tahun_anggaran'] ?? '-')); ?>
        </div>
    </div>

    <div class="google-auth-card">
        <div class="d-flex align-items-start justify-content-between flex-wrap" style="gap: 12px;">
            <div>
                <h5 class="mb-1">Login Google terlebih dahulu</h5>
                <p class="text-muted mb-0">Login dibuka sebagai popup dan identitas Google akan dipakai untuk mencatat siapa yang mengupload dokumen.</p>
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

        <div class="tab-content bg-white border border-top-0 p-3">
            <?php $tabIndex = 0; foreach ($sections as $sectionKey => $section): ?>
                <div class="tab-pane fade <?= $tabIndex === 0 ? 'show active' : ''; ?>" id="share-panel-<?= esc($sectionKey); ?>" role="tabpanel">
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
                                                    $dokumenActionLabel = $isDriveLink ? 'Buka Link' : 'Lihat Dokumen';
                                                ?>
                                                <div>
                                                    <a href="<?= site_url('simak/share/' . (string) ($token ?? '') . '/download-dokumen/' . (int) ($latestDokumen['id'] ?? 0)); ?>" class="btn btn-success btn-sm">
                                                        <i class="fas <?= $isDriveLink ? 'fa-external-link-alt' : 'fa-download'; ?>"></i> <?= esc($dokumenActionLabel); ?>
                                                    </a>
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
                        Login Google terlebih dahulu untuk mengaktifkan upload.
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
                        <small class="text-muted">Format: PDF, JPG, PNG, DOC, DOCX, XLS, XLSX, ZIP. Maksimal 10MB. File akan diupload langsung ke folder Google Drive dari env. File gambar/PDF di atas 5MB akan dicoba dikompres otomatis (target sekitar 3MB) di browser sebelum upload.</small>
                        <div id="upload_file_status" class="small mt-2 text-muted">Status file: belum dipilih.</div>
                    </div>

                    <div class="form-group mb-0 d-none" id="uploadDriveGroup">
                        <label for="google_drive_link">Link Google Drive (alternatif)</label>
                        <input type="url" id="google_drive_link" name="google_drive_link" class="form-control" placeholder="https://drive.google.com/... atau https://docs.google.com/...">
                        <small class="text-muted">Gunakan link dari drive.google.com atau docs.google.com.</small>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://accounts.google.com/gsi/client" async defer></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
    'use strict';

    var googleClientId = <?= json_encode((string) ($googleClientId ?? ''), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    var googleDriveUploadFolderId = <?= json_encode((string) ($googleDriveUploadFolderId ?? ''), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    var googleDriveUploadFolderUrl = <?= json_encode((string) ($googleDriveUploadFolderUrl ?? ''), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    var openButtons = document.querySelectorAll('.js-open-upload-modal');
    var uploadForm = document.getElementById('form-upload-share-simak');
    var rowNoEl = document.getElementById('upload_row_no_modal');
    var rowLabelEl = document.getElementById('upload_row_label_modal');
    var rowUraianEl = document.getElementById('upload_row_uraian_modal');
    var uploadMethodEl = document.getElementById('upload_method');
    var uploadFileGroupEl = document.getElementById('uploadFileGroup');
    var uploadDriveGroupEl = document.getElementById('uploadDriveGroup');
    var dokumenFileEl = document.getElementById('dokumen_file_modal');
    var uploadFileStatusEl = document.getElementById('upload_file_status');
    var googleDriveLinkEl = document.getElementById('google_drive_link');
    var googleAccessTokenEl = document.getElementById('google_access_token');
    var uploaderNameEl = document.getElementById('uploader_name');
    var uploaderEmailEl = document.getElementById('uploader_email');
    var uploaderSubEl = document.getElementById('uploader_sub');
    var googlePopupLoginBtn = document.getElementById('googlePopupLoginBtn');
    var googleSignOutButton = document.getElementById('googleSignOutButton');
    var googleAuthUser = document.getElementById('googleAuthUser');
    var googleAuthHint = document.getElementById('googleAuthHint');
    var uploadGoogleLock = document.getElementById('uploadGoogleLock');
    var uploadModalEl = document.getElementById('modal-upload-share-simak');
    var uploadSubmitButton = uploadForm ? uploadForm.querySelector('button[type="submit"]') : null;
    var googleProfile = null;
    var googleTokenClient = null;
    var googleStorageKey = 'simak_share_google_credential_' + (window.location.pathname || 'share');
    var pendingUploadContext = null;
    var maxUploadBytes = 10 * 1024 * 1024;
    var autoCompressThresholdBytes = 5 * 1024 * 1024;
    var compressTargetBytes = 3 * 1024 * 1024;
    var aggressiveImageTargetBytes = 1200 * 1024;
    var isSubmitting = false;

    function getFileExtension(fileName) {
        var name = String(fileName || '').trim();
        var dotIndex = name.lastIndexOf('.');
        if (dotIndex < 0) {
            return '';
        }

        return name.substring(dotIndex + 1).toLowerCase();
    }

    function canAutoCompress(ext) {
        return ['jpg', 'jpeg', 'png', 'pdf'].indexOf(ext) !== -1;
    }

    function setFileStatus(message, tone) {
        if (!uploadFileStatusEl) {
            return;
        }

        var classByTone = {
            neutral: 'text-muted',
            info: 'text-info',
            success: 'text-success',
            warning: 'text-warning',
            danger: 'text-danger',
        };

        uploadFileStatusEl.textContent = String(message || 'Status file: belum dipilih.');
        uploadFileStatusEl.className = 'small mt-2 ' + (classByTone[tone] || classByTone.neutral);
    }

    function refreshFileStatus() {
        var selectedMethod = String(uploadMethodEl && uploadMethodEl.value ? uploadMethodEl.value : 'file').toLowerCase();
        if (selectedMethod === 'drive') {
            setFileStatus('Mode Link Google Drive aktif.', 'info');
            return;
        }

        var currentFile = dokumenFileEl && dokumenFileEl.files && dokumenFileEl.files[0] ? dokumenFileEl.files[0] : null;
        if (!currentFile) {
            setFileStatus('Status file: belum dipilih.', 'neutral');
            return;
        }

        var extension = getFileExtension(currentFile.name);
        var summary = 'File: ' + currentFile.name + ' (' + formatBytes(currentFile.size) + ')';

        if (currentFile.size > maxUploadBytes) {
            setFileStatus(summary + ' • melebihi 10MB.', 'danger');
            return;
        }

        if (extension === 'pdf') {
            setFileStatus(summary + ' • PDF akan dicoba dioptimasi di browser sebelum upload Google Drive.', 'info');
            return;
        }

        setFileStatus(summary + ' • siap upload.', 'success');
    }

    function formatBytes(bytes) {
        var value = Number(bytes || 0);
        if (value <= 0) {
            return '0 KB';
        }

        if (value < 1024 * 1024) {
            return Math.max(1, Math.round(value / 1024)) + ' KB';
        }

        return (value / (1024 * 1024)).toFixed(2) + ' MB';
    }

    function formatCompressionSaving(originalBytes, finalBytes) {
        var original = Number(originalBytes || 0);
        var finalValue = Number(finalBytes || 0);
        if (original <= 0 || finalValue < 0 || finalValue >= original) {
            return '0%';
        }

        var percent = ((original - finalValue) / original) * 100;
        return percent.toFixed(1) + '%';
    }

    function formatEtaSeconds(totalSeconds) {
        var value = Math.max(0, Math.round(Number(totalSeconds) || 0));
        var minutes = Math.floor(value / 60);
        var seconds = value % 60;
        if (minutes <= 0) {
            return seconds + ' detik';
        }

        return minutes + ' menit ' + seconds + ' detik';
    }

    function replaceExtension(fileName, nextExt) {
        var name = String(fileName || '').trim();
        var ext = String(nextExt || '').replace(/^\./, '').toLowerCase();
        if (!name) {
            return 'file.' + (ext || 'bin');
        }

        var dotIndex = name.lastIndexOf('.');
        if (dotIndex < 0) {
            return name + '.' + (ext || 'bin');
        }

        return name.substring(0, dotIndex) + '.' + (ext || 'bin');
    }

    function switchToDriveMode() {
        if (!uploadMethodEl) {
            return;
        }

        uploadMethodEl.value = 'drive';
        syncUploadMethodUI();
        refreshFileStatus();
        if (googleDriveLinkEl && typeof googleDriveLinkEl.focus === 'function') {
            googleDriveLinkEl.focus();
        }
    }

    function showCompressionProgress(stage, detail) {
        if (!window.Swal || typeof window.Swal.update !== 'function') {
            return;
        }

        var safeStage = String(stage || 'Sedang memproses...');
        var safeDetail = String(detail || 'Mohon tunggu sebentar.');
        var percent = arguments.length > 2 ? Number(arguments[2]) : null;
        var hasPercent = Number.isFinite(percent);
        var safePercent = hasPercent ? Math.max(0, Math.min(100, Math.round(percent))) : 0;
        var progressHtml = hasPercent
            ? '<div class="progress mt-2" style="height: 12px;"><div class="progress-bar" role="progressbar" style="width: ' + safePercent + '%;" aria-valuenow="' + safePercent + '" aria-valuemin="0" aria-valuemax="100">' + safePercent + '%</div></div>'
            : '';
        window.Swal.update({
            title: 'Mengompres file...',
            html: '<div class="text-left"><div class="font-weight-semibold">' + safeStage + '</div><div class="small text-muted mt-1">' + safeDetail + '</div>' + progressHtml + '</div>',
        });
    }

    async function compressImageFile(file) {
        if (!window.imageCompression || typeof window.imageCompression !== 'function') {
            return file;
        }

        var originalExt = getFileExtension(file.name);
        var originalType = String(file.type || '').toLowerCase();
        var isPng = originalExt === 'png' || originalType === 'image/png';
        var forceJpeg = isPng && file.size > (8 * 1024 * 1024);
        var preferredType = forceJpeg ? 'image/jpeg' : (file.type || undefined);
        var targetBytes = file.size > maxUploadBytes ? aggressiveImageTargetBytes : compressTargetBytes;

        var passes = [
            { maxSizeMB: Math.max(0.6, (targetBytes * 1.7) / (1024 * 1024)), maxWidthOrHeight: 2200, initialQuality: 0.76 },
            { maxSizeMB: Math.max(0.5, (targetBytes * 1.25) / (1024 * 1024)), maxWidthOrHeight: 1700, initialQuality: 0.62 },
            { maxSizeMB: Math.max(0.4, (targetBytes * 1.0) / (1024 * 1024)), maxWidthOrHeight: 1400, initialQuality: 0.50 },
            { maxSizeMB: Math.max(0.35, (targetBytes * 0.8) / (1024 * 1024)), maxWidthOrHeight: 1100, initialQuality: 0.40 },
        ];

        var current = file;
        var currentName = forceJpeg ? replaceExtension(file.name, 'jpg') : file.name;
        var processStartedAt = Date.now();
        for (let passIndex = 0; passIndex < passes.length; passIndex += 1) {
            var pass = passes[passIndex];

            try {
                var compressed = await window.imageCompression(current, {
                    maxSizeMB: pass.maxSizeMB,
                    maxWidthOrHeight: pass.maxWidthOrHeight,
                    useWebWorker: true,
                    initialQuality: pass.initialQuality,
                    fileType: preferredType,
                    onProgress: function (percent) {
                        var safePercent = Math.max(0, Math.min(100, Math.round(Number(percent) || 0)));
                        var overallPercent = ((passIndex + (safePercent / 100)) / passes.length) * 100;
                        var elapsedSeconds = (Date.now() - processStartedAt) / 1000;
                        var etaSeconds = overallPercent > 0 ? (elapsedSeconds * (100 - overallPercent)) / overallPercent : 0;
                        var detailText = 'Proses kompresi gambar sedang berjalan.';
                        if (overallPercent > 2 && Number.isFinite(etaSeconds)) {
                            detailText += ' Estimasi sisa: ' + formatEtaSeconds(etaSeconds) + '.';
                        }

                        showCompressionProgress(
                            'Kompresi gambar tahap ' + (passIndex + 1) + '/' + passes.length,
                            detailText,
                            overallPercent
                        );
                    },
                });

                if (compressed && compressed.size && compressed.size < current.size) {
                    current = new File([compressed], currentName, {
                        type: compressed.type || current.type || 'application/octet-stream',
                        lastModified: Date.now(),
                    });
                }

                if (current.size <= targetBytes) {
                    break;
                }
            } catch (error) {
                break;
            }
        }

        return current.size < file.size ? current : file;
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
            uploadFileGroupEl.classList.toggle('d-none', useDrive);
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
        var signedIn = !!(googleProfile && googleAccessTokenEl && googleAccessTokenEl.value);

        openButtons.forEach(function (button) {
            button.disabled = !signedIn;
        });

        if (uploadSubmitButton) {
            uploadSubmitButton.disabled = !signedIn;
        }

        if (uploadGoogleLock) {
            uploadGoogleLock.classList.toggle('d-none', signedIn);
        }

        if (googleAuthUser) {
            googleAuthUser.classList.toggle('d-none', !signedIn);
            if (signedIn) {
                googleAuthUser.textContent = (googleProfile.name || 'Google User') + ' · ' + (googleProfile.email || '-');
            }
        }

        if (googleSignOutButton) {
            googleSignOutButton.classList.toggle('d-none', !signedIn);
        }

        if (googleAuthHint) {
            googleAuthHint.textContent = signedIn
                ? 'Anda sudah login dengan Google dan siap mengupload dokumen.'
                : 'Silakan login dengan akun Google sebelum membuka form upload.';
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

    function initGoogleSignIn() {
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

            googleTokenClient.requestAccessToken({ prompt: 'consent' });
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

    openButtons.forEach(function (button) {
        button.addEventListener('click', function (event) {
            var context = getUploadContextFromButton(this);

            if (!googleAccessTokenEl || !googleAccessTokenEl.value) {
                pendingUploadContext = context;

                if (event && typeof event.preventDefault === 'function') {
                    event.preventDefault();
                    event.stopPropagation();
                }

                if (googleTokenClient) {
                    googleTokenClient.requestAccessToken({ prompt: 'consent' });
                }

                if (window.Swal) {
                    window.Swal.fire({
                        icon: 'warning',
                        title: 'Login Google diperlukan',
                        text: 'Silakan pilih akun Google. Setelah login berhasil, form upload akan terbuka otomatis.',
                    });
                }
                return;
            }

            setUploadContext(context);
        });
    });

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
            dokumenFileEl.addEventListener('change', refreshFileStatus);
        }

        uploadForm.addEventListener('submit', async function (event) {
            if (isSubmitting) {
                return;
            }

            var selectedMethod = String(uploadMethodEl && uploadMethodEl.value ? uploadMethodEl.value : 'file').toLowerCase();

            if (!googleAccessTokenEl || !googleAccessTokenEl.value) {
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

            if (selectedMethod !== 'drive' && !hasFile) {
                event.preventDefault();
                if (window.Swal) {
                    window.Swal.fire({
                        icon: 'warning',
                        title: 'Dokumen belum diisi',
                        text: 'Silakan pilih file dokumen terlebih dahulu.',
                    });
                }
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
                        if (window.Swal) {
                            var oversizeResult = await window.Swal.fire({
                                icon: 'warning',
                                title: 'Ukuran file melebihi batas',
                                html: '<div class="text-left">Setelah kompresi, ukuran file masih di atas 10MB.<br><span class="small text-muted">Ukuran saat ini: ' + formatBytes(finalFile.size) + '</span></div>',
                                showCancelButton: true,
                                confirmButtonText: 'Gunakan Link Google Drive',
                                cancelButtonText: 'Tutup',
                            });

                            if (oversizeResult && oversizeResult.isConfirmed) {
                                switchToDriveMode();
                            }
                        }
                        setFileStatus('Hasil kompres masih >10MB. Gunakan Link Google Drive.', 'warning');
                        return;
                    }

                    if (window.Swal) {
                        if (compressionResult.compressed) {
                            var savingPercent = formatCompressionSaving(sourceFile.size, finalFile.size);
                            var savingPercentValue = sourceFile.size > 0 ? ((sourceFile.size - finalFile.size) / sourceFile.size) * 100 : 0;
                            var isPdfFile = sourceExt === 'pdf';
                            var pdfHintHtml = '';
                            if (isPdfFile && savingPercentValue < 10) {
                                pdfHintHtml = '<br><span class="small text-warning">Catatan: PDF sering sulit dikompres signifikan di browser. Jika file masih besar, lebih efektif pakai Link Google Drive.</span>';
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
                    setFileStatus('Ukuran file >10MB dan tidak bisa dikompres otomatis.', 'danger');
                    if (window.Swal) {
                        var noCompressOversizeResult = await window.Swal.fire({
                            icon: 'warning',
                            title: 'Ukuran file melebihi batas',
                            html: '<div class="text-left">File ini tidak bisa dikompres otomatis dan ukurannya di atas 10MB.<br><span class="small text-muted">Gunakan link Google Drive atau kecilkan file terlebih dahulu.</span></div>',
                            showCancelButton: true,
                            confirmButtonText: 'Gunakan Link Google Drive',
                            cancelButtonText: 'Tutup',
                        });
                        if (noCompressOversizeResult && noCompressOversizeResult.isConfirmed) {
                            switchToDriveMode();
                        }
                    }
                    return;
                }

                var uploadFolderId = resolveDriveUploadFolderId();
                if (!uploadFolderId) {
                    if (window.Swal) {
                        await window.Swal.fire({
                            icon: 'error',
                            title: 'Folder Google Drive belum dikonfigurasi',
                            text: 'Isi GOOGLE_DRIVE_UPLOAD_FOLDER_URL atau GOOGLE_DRIVE_UPLOAD_FOLDER_ID di env.',
                        });
                    }
                    setFileStatus('Folder Google Drive belum dikonfigurasi.', 'danger');
                    return;
                }

                if (window.Swal && typeof window.Swal.fire === 'function') {
                    window.Swal.fire({
                        title: 'Mengunggah ke Google Drive...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: function () {
                            window.Swal.showLoading();
                        }
                    });
                }

                try {
                    var uploadResult = await uploadFileDirectToGoogleDrive(finalFile, googleAccessTokenEl.value, uploadFolderId);
                    if (googleDriveLinkEl) {
                        googleDriveLinkEl.value = uploadResult.link;
                    }
                    if (uploadMethodEl) {
                        uploadMethodEl.value = 'drive';
                        syncUploadMethodUI();
                    }
                    if (dokumenFileEl) {
                        dokumenFileEl.value = '';
                    }

                    setFileStatus('Upload ke Google Drive berhasil. Link siap dikirim.', 'success');
                    if (window.Swal) {
                        await window.Swal.fire({
                            icon: 'success',
                            title: 'Upload ke Google Drive berhasil',
                            timer: 1400,
                            showConfirmButton: false,
                        });
                    }

                    isSubmitting = true;
                    uploadForm.submit();
                    return;
                } catch (driveUploadError) {
                    if (window.Swal) {
                        await window.Swal.fire({
                            icon: 'error',
                            title: 'Upload Google Drive gagal',
                            text: driveUploadError && driveUploadError.message ? driveUploadError.message : 'Terjadi kesalahan saat upload ke Google Drive.',
                        });
                    }
                    setFileStatus('Upload ke Google Drive gagal.', 'danger');
                    return;
                }
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
