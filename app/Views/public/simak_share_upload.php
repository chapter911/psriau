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
                <p class="text-muted mb-0">Identitas Google akan dipakai untuk mencatat siapa yang mengupload dokumen.</p>
            </div>
            <div class="google-auth-actions">
                <div id="googleSignInButton"></div>
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

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc((string) session()->getFlashdata('error')); ?></div>
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
                                    $isGroup = in_array($rowType, ['section_header', 'subsection_header'], true) || $hasChildren;
                                    $rowClass = $isGroup ? 'row-group' : 'row-leaf';
                                    $isInputRow = $isLeaf && ! in_array($rowType, ['section_header', 'subsection_header'], true);
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
                                                <div>
                                                    <a href="<?= site_url('simak/share/' . (string) ($token ?? '') . '/download-dokumen/' . (int) ($latestDokumen['id'] ?? 0)); ?>" class="btn btn-success btn-sm">
                                                        <i class="fas fa-download"></i> Lihat Dokumen
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
                <input type="hidden" name="google_credential" id="google_credential" value="">
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

                    <div class="form-group mb-0">
                        <label for="dokumen_file_modal">File Dokumen</label>
                        <input type="file" id="dokumen_file_modal" name="dokumen_file" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" required>
                        <small class="text-muted">Format: PDF, JPG, PNG, DOC, DOCX, XLS, XLSX.</small>
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
    var openButtons = document.querySelectorAll('.js-open-upload-modal');
    var uploadForm = document.getElementById('form-upload-share-simak');
    var rowNoEl = document.getElementById('upload_row_no_modal');
    var rowLabelEl = document.getElementById('upload_row_label_modal');
    var rowUraianEl = document.getElementById('upload_row_uraian_modal');
    var googleCredentialEl = document.getElementById('google_credential');
    var uploaderNameEl = document.getElementById('uploader_name');
    var uploaderEmailEl = document.getElementById('uploader_email');
    var uploaderSubEl = document.getElementById('uploader_sub');
    var googleSignInButton = document.getElementById('googleSignInButton');
    var googleSignOutButton = document.getElementById('googleSignOutButton');
    var googleAuthUser = document.getElementById('googleAuthUser');
    var googleAuthHint = document.getElementById('googleAuthHint');
    var uploadGoogleLock = document.getElementById('uploadGoogleLock');
    var uploadSubmitButton = uploadForm ? uploadForm.querySelector('button[type="submit"]') : null;
    var googleProfile = null;

    function updateUploadLockState() {
        var signedIn = !!(googleProfile && googleCredentialEl && googleCredentialEl.value);

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
        if (googleCredentialEl) googleCredentialEl.value = '';
        if (uploaderNameEl) uploaderNameEl.value = '';
        if (uploaderEmailEl) uploaderEmailEl.value = '';
        if (uploaderSubEl) uploaderSubEl.value = '';
        updateUploadLockState();
    }

    function decodeJwtPayload(token) {
        try {
            var part = String(token || '').split('.')[1] || '';
            var padded = part.replace(/-/g, '+').replace(/_/g, '/');
            while (padded.length % 4) {
                padded += '=';
            }
            return JSON.parse(atob(padded));
        } catch (error) {
            return null;
        }
    }

    function handleGoogleCredential(response) {
        var credential = String(response && response.credential ? response.credential : '').trim();
        if (!credential) {
            clearGoogleProfile();
            return;
        }

        var payload = decodeJwtPayload(credential);
        if (!payload) {
            if (window.Swal) {
                window.Swal.fire({ icon: 'error', title: 'Login Google gagal', text: 'Credential Google tidak valid.' });
            }
            clearGoogleProfile();
            return;
        }

        googleProfile = {
            name: String(payload.name || payload.given_name || 'Google User'),
            email: String(payload.email || ''),
            sub: String(payload.sub || ''),
        };

        if (googleCredentialEl) googleCredentialEl.value = credential;
        if (uploaderNameEl) uploaderNameEl.value = googleProfile.name;
        if (uploaderEmailEl) uploaderEmailEl.value = googleProfile.email;
        if (uploaderSubEl) uploaderSubEl.value = googleProfile.sub;

        updateUploadLockState();
    }

    function initGoogleSignIn() {
        if (!googleClientId || !window.google || !window.google.accounts || !window.google.accounts.id) {
            if (googleSignInButton) {
                googleSignInButton.innerHTML = '<div class="text-danger small">Google Sign-In belum dikonfigurasi.</div>';
            }
            return;
        }

        window.google.accounts.id.initialize({
            client_id: googleClientId,
            callback: handleGoogleCredential,
        });

        if (googleSignInButton) {
            window.google.accounts.id.renderButton(googleSignInButton, {
                theme: 'outline',
                size: 'large',
                text: 'signin_with',
                shape: 'pill',
                width: 260,
            });
        }
    }

    openButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            if (!googleCredentialEl || !googleCredentialEl.value) {
                if (window.Swal) {
                    window.Swal.fire({
                        icon: 'warning',
                        title: 'Login Google diperlukan',
                        text: 'Silakan login dengan Google terlebih dahulu untuk mengunggah dokumen.',
                    });
                }
                return;
            }

            if (rowNoEl) {
                rowNoEl.value = String(this.getAttribute('data-row-no') || '').trim();
            }
            if (rowLabelEl) {
                rowLabelEl.textContent = String(this.getAttribute('data-row-label') || '-').trim();
            }
            if (rowUraianEl) {
                rowUraianEl.textContent = String(this.getAttribute('data-uraian') || '-').trim();
            }
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
        uploadForm.addEventListener('submit', function (event) {
            if (!googleCredentialEl || !googleCredentialEl.value) {
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
        });

        updateUploadLockState();
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
