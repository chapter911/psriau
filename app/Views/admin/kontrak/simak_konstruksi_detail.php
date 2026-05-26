<?= $this->extend('layouts/admin'); ?>
<?php helper('custom'); ?>

<?php
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
?>

<?= $this->section('content'); ?>
<style>
    .simak-verifikasi-table th,
    .simak-verifikasi-table td,
    .simak-history-table th,
    .simak-history-table td {
        text-align: center;
        vertical-align: middle;
    }

    .simak-verifikasi-table td .d-flex,
    .simak-history-table td .d-flex {
        justify-content: center;
    }

    .simak-upload-actions {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: center;
        gap: 8px;
        max-width: 150px;
        margin: 0 auto;
    }

    .simak-upload-actions .btn {
        width: auto;
        white-space: normal;
    }

    .simak-status-yellow {
        background-color: #fff3cd;
    }

    .simak-status-red-soft {
        background-color: #f8d7da;
    }

    .kelengkapan-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .kelengkapan-card {
        background: #fff;
        border-radius: 8px;
        padding: 16px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .kelengkapan-card.lengkap {
        border-left: 4px solid #198754;
    }

    .kelengkapan-card.belum-sesuai {
        border-left: 4px solid #ffc107;
    }

    .kelengkapan-card.menunggu-verifikasi {
        border-left: 4px solid #0dcaf0;
    }

    .kelengkapan-card.belum-ada {
        border-left: 4px solid #dc3545;
    }

    .kelengkapan-label {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 600;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .kelengkapan-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1f2937;
    }

    .kelengkapan-count {
        font-size: 0.75rem;
        color: #9ca3af;
        font-weight: 500;
        margin-top: 4px;
    }
</style>
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc((string) session()->getFlashdata('success')); ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc((string) session()->getFlashdata('error')); ?></div>
<?php endif; ?>

<?php if (! empty($error ?? '')): ?>
    <div class="alert alert-danger"><?= esc((string) $error); ?></div>
<?php endif; ?>

<!-- Kelengkapan Dokumen Administrasi Summary -->
<div class="kelengkapan-summary">
    <div class="kelengkapan-card lengkap">
        <div class="kelengkapan-label">Lengkap</div>
        <div class="kelengkapan-value"><?= number_format((float) ($kelengkapanPercentage['lengkap_persen'] ?? 0), 2, ',', '.'); ?>%</div>
        <div class="kelengkapan-count"><?= $lengkapCount; ?> dari <?= $leafRowsCount; ?></div>
    </div>
    <div class="kelengkapan-card belum-sesuai">
        <div class="kelengkapan-label">Belum Sesuai</div>
        <div class="kelengkapan-value"><?= number_format((float) ($kelengkapanPercentage['belum_sesuai_persen'] ?? 0), 2, ',', '.'); ?>%</div>
        <div class="kelengkapan-count"><?= $belumSesuaiCount; ?> dari <?= $leafRowsCount; ?></div>
    </div>
    <div class="kelengkapan-card menunggu-verifikasi">
        <div class="kelengkapan-label">Menunggu Verifikasi</div>
        <div class="kelengkapan-value"><?= number_format((float) ($kelengkapanPercentage['belum_verifikasi_persen'] ?? 0), 2, ',', '.'); ?>%</div>
        <div class="kelengkapan-count"><?= $menungguVerifikasiCount; ?> dari <?= $leafRowsCount; ?></div>
    </div>
    <div class="kelengkapan-card belum-ada">
        <div class="kelengkapan-label">Belum Ada</div>
        <div class="kelengkapan-value"><?= number_format((float) ($kelengkapanPercentage['belum_ada_persen'] ?? 0), 2, ',', '.'); ?>%</div>
        <div class="kelengkapan-count"><?= $belumAdaCount; ?> dari <?= $leafRowsCount; ?></div>
    </div>
</div>

<!-- Filter Toolbar -->
<div class="mb-3 d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-2">
        <label class="mb-0 font-weight-bold">Filter:</label>
        <select class="form-control form-control-sm" id="filter-status" style="width: 200px;">
            <option value="all">Semua</option>
            <option value="menunggu">Menunggu Verifikasi</option>
            <option value="sesuai">Sesuai</option>
            <option value="tidak_sesuai">Tidak Sesuai</option>
            <option value="belum_ada">Belum Ada</option>
        </select>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-reset-filter">Reset</button>
    </div>
    <div class="text-muted small" id="filter-info">Menampilkan semua item</div>
</div>

<?php if (! empty($templateItems ?? [])): ?>
<?php
    $sections = [];
    $currentSectionKey = '';

    foreach (($templateItems ?? []) as $row) {
        if ((bool) ($row['is_header'] ?? false) === true) {
            $sectionKey = trim((string) ($row['section_key'] ?? ($row['display_no'] ?? '')));
            if ($sectionKey === '') {
                continue;
            }

            $sectionNo = trim((string) ($row['display_no'] ?? ''));
            if ($sectionNo !== '' && $sectionNo !== '-') {
                $sectionNo = preg_replace('/\.+$/', '.', $sectionNo);
            }

            $sections[$sectionKey] = [
                'label' => $sectionNo . ' ' . trim((string) ($row['section_title'] ?? $row['uraian'] ?? '')),
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
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="card-title mb-0">Kelengkapan Dokumen dan Verifikasi Dit. KI</h2>
    </div>
    <div class="card-body">
            <ul class="nav nav-tabs" id="simakSectionTabs" role="tablist">
                <?php $tabIndex = 0; foreach ($sections as $sectionKey => $section): ?>
                    <li class="nav-item" role="presentation">
                        <a
                            class="nav-link <?= $tabIndex === 0 ? 'active' : ''; ?>"
                            id="simak-tab-<?= esc($sectionKey); ?>"
                            data-toggle="tab"
                            href="#simak-panel-<?= esc($sectionKey); ?>"
                            role="tab"
                            aria-controls="simak-panel-<?= esc($sectionKey); ?>"
                            aria-selected="<?= $tabIndex === 0 ? 'true' : 'false'; ?>"
                        >
                            <?= esc((string) ($section['label'] ?? $sectionKey)); ?>
                        </a>
                    </li>
                    <?php $tabIndex++; ?>
                <?php endforeach; ?>
            </ul>

            <div class="tab-content pt-3">
                <?php $tabIndex = 0; foreach ($sections as $sectionKey => $section): ?>
                    <div class="tab-pane fade <?= $tabIndex === 0 ? 'show active' : ''; ?>" id="simak-panel-<?= esc($sectionKey); ?>" role="tabpanel" aria-labelledby="simak-tab-<?= esc($sectionKey); ?>">
                        <div class="table-responsive" style="max-height: 75vh; overflow-y: auto;">
                            <table class="table table-bordered table-sm simak-verifikasi-table" style="min-width: 1450px;">
                                <thead class="text-center">
                                    <tr>
                                        <th style="width: 70px; position: sticky; top: 0; z-index: 6; background: #2F3A45; color: #fff;">No</th>
                                        <th style="width: 520px; position: sticky; top: 0; z-index: 6; background: #2F3A45; color: #fff;">Uraian</th>
                                        <th style="width: 170px; position: sticky; top: 0; z-index: 6; background: #2F3A45; color: #fff;">Kelengkapan Dokumen</th>
                                        <th style="width: 170px; position: sticky; top: 0; z-index: 6; background: #2F3A45; color: #fff;">Verifikasi Draft</th>
                                        <th style="width: 170px; position: sticky; top: 0; z-index: 6; background: #2F3A45; color: #fff;">Verifikasi Final</th>
                                        <th style="width: 270px; position: sticky; top: 0; z-index: 6; background: #2F3A45; color: #fff;">Keterangan</th>
                                        <th style="width: 170px; position: sticky; top: 0; z-index: 6; background: #2F3A45; color: #fff;">PIC</th>
                                        <th style="width: 280px; position: sticky; top: 0; z-index: 6; background: #2F3A45; color: #fff;">Dokumen Draft</th>
                                        <th style="width: 280px; position: sticky; top: 0; z-index: 6; background: #2F3A45; color: #fff;">Dokumen Final</th>
                                        <th style="width: 170px; position: sticky; top: 0; z-index: 6; background: #2F3A45; color: #fff;">History Dokumen</th>
                                        <th style="width: 130px; position: sticky; top: 0; z-index: 6; background: #2F3A45; color: #fff;">Upload Dokumen</th>
                                        <th style="width: 130px; position: sticky; top: 0; z-index: 6; background: #2F3A45; color: #fff;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (($section['rows'] ?? []) as $row): ?>
                                        <?php
                                            $rowNo = (int) ($row['row_no'] ?? 0);
                                            $displayNo = trim((string) ($row['display_no'] ?? ''));
                                            if ($displayNo !== '' && $displayNo !== '-') {
                                                $displayNo = preg_replace('/\.+$/', '.', $displayNo);
                                            }
                                            $indentLevel = (int) ($row['indent_level'] ?? 0);
                                            $rowType = (string) ($row['row_type'] ?? 'detail');
                                            $hasChildren = (bool) ($row['has_children'] ?? false);
                                            $isLeaf = (bool) ($row['is_leaf'] ?? false);
                                            $isPromotedSubsectionInput = $isLeaf
                                                && $rowType === 'subsection_header'
                                                && preg_match('/^\d+$/', $displayNo) === 1;
                                            $isInputRow = $isLeaf
                                                && (! in_array($rowType, ['section_header', 'subsection_header'], true) || $isPromotedSubsectionInput);
                                            $hasDraft = (bool) ($row['has_draft'] ?? false);
                                            $existing = $verifikasiByRow[$rowNo] ?? [];
                                            $kelengkapan = (string) ($existing['kelengkapan_dokumen'] ?? '');
                                            $verifikasi = (string) ($existing['verifikasi_ki'] ?? '');
                                            $keterangan = (string) ($existing['keterangan'] ?? '');
                                            $pic = (string) ($existing['pic'] ?? '');
                                            $uraian = (string) ($row['uraian'] ?? '');
                                            $dokumenRows = $dokumenByRow[$rowNo] ?? [];
                                            $dokumenCount = count($dokumenRows);
                                            $draftDokumen = null;
                                            $finalDokumen = null;
                                            foreach ($dokumenRows as $docRow) {
                                                $docType = strtolower(trim((string) ($docRow['tipe_dokumen'] ?? 'final')));
                                                if ($docType === 'draft' && $draftDokumen === null) {
                                                    $draftDokumen = $docRow;
                                                } elseif ($docType !== 'draft' && $finalDokumen === null) {
                                                    $finalDokumen = $docRow;
                                                }

                                                if ($draftDokumen !== null && $finalDokumen !== null) {
                                                    break;
                                                }
                                            }
                                            $latestDokumen = $finalDokumen ?? $draftDokumen;
                                            $draftVerifikasi = is_array($draftDokumen) ? strtolower(trim((string) ($draftDokumen['verifikasi_ki'] ?? ''))) : '';
                                            $finalVerifikasi = is_array($finalDokumen) ? strtolower(trim((string) ($finalDokumen['verifikasi_ki'] ?? ''))) : '';
                                            $draftApproved = $draftVerifikasi === 'sesuai';
                                            $finalApproved = $finalVerifikasi === 'sesuai';
                                            $draftHasFile = is_array($draftDokumen) && trim((string) ($draftDokumen['file_relative_path'] ?? '')) !== '';
                                            $finalHasFile = is_array($finalDokumen) && trim((string) ($finalDokumen['file_relative_path'] ?? '')) !== '';
                                            // Check untuk placeholder "Tidak Ada" tanpa file
                                            $finalNoFilePlaceholder = $hasDraft === false
                                                && is_array($finalDokumen)
                                                && trim((string) ($finalDokumen['file_relative_path'] ?? '')) === ''
                                                && trim((string) ($finalDokumen['file_stored_name'] ?? '')) === '';
                                            // canVerifyDraft hanya untuk items dengan has_draft=1
                                            $canVerifyDraft = $hasDraft && ($draftDokumen !== null || $kelengkapan === 'tidak') && ! $draftApproved && ! $finalApproved;
                                            // canVerifyFinal untuk items dengan atau tanpa draft
                                            $canVerifyFinal = ($finalDokumen !== null || $finalNoFilePlaceholder) && ! $finalApproved;
                                            $draftActionKelengkapan = strtolower(trim((string) ($draftDokumen['kelengkapan_dokumen'] ?? ($kelengkapan !== '' ? $kelengkapan : 'tidak'))));
                                            $draftActionVerifikasi = strtolower(trim((string) ($draftDokumen['verifikasi_ki'] ?? $verifikasi)));
                                            $draftActionKeterangan = (string) ($draftDokumen['keterangan'] ?? $keterangan);
                                            $draftActionPic = (string) ($draftDokumen['pic'] ?? $pic);
                                            // Untuk final-only items, gunakan kelengkapan dari placeholder
                                            $finalActionKelengkapan = strtolower(trim((string) ($finalDokumen['kelengkapan_dokumen'] ?? ($finalNoFilePlaceholder ? 'tidak' : ($kelengkapan !== '' ? $kelengkapan : 'tidak')))));
                                            $finalActionVerifikasi = strtolower(trim((string) ($finalDokumen['verifikasi_ki'] ?? $verifikasi)));
                                            $finalActionKeterangan = (string) ($finalDokumen['keterangan'] ?? $keterangan);
                                            $finalActionPic = (string) ($finalDokumen['pic'] ?? $pic);
                                            // canUploadFinal: untuk items dengan draft, perlu verifikasi='sesuai'; untuk final-only, tampilkan jika ada placeholder
                                            $canUploadFinal = ($hasDraft && $verifikasi === 'sesuai') || $finalNoFilePlaceholder;
                                            $canUploadDraft = $hasDraft && ! $draftApproved && ! $finalApproved;
                                            $latestPath = is_array($latestDokumen) ? trim((string) ($latestDokumen['file_relative_path'] ?? '')) : '';
                                            $latestHost = strtolower((string) parse_url($latestPath, PHP_URL_HOST));
                                            $isDriveLink = in_array($latestHost, ['drive.google.com', 'docs.google.com'], true);
                                            $dokumenActionLabel = $isDriveLink ? 'Buka Link' : 'Lihat Dokumen';
                                            $indentPadding = max(0, $indentLevel) * 18;
                                            $isGroup = ($hasChildren || in_array($rowType, ['section_header', 'subsection_header'], true))
                                                && ! $isPromotedSubsectionInput;
                                            $fontWeight = $isGroup ? 'font-weight: 700;' : ($indentLevel > 1 ? 'font-weight: 500;' : 'font-weight: 600;');
                                            $bgStyle = $isGroup ? 'background-color: #f2f4f7;' : '';
                                            $noText = $displayNo;
                                            $statusCellClass = '';
                                            // Check jika menunggu verifikasi (record ada tapi verifikasi belum sesuai/null)
                                            $hasPendingDraft = $hasDraft && ($draftDokumen !== null || $kelengkapan === 'tidak') && $draftVerifikasi !== 'sesuai' && $draftVerifikasi !== 'tidak_sesuai';
                                            $hasPendingFinal = ($finalDokumen !== null || $finalNoFilePlaceholder) && $finalVerifikasi !== 'sesuai' && $finalVerifikasi !== 'tidak_sesuai';
                                            $isPendingVerification = $verifikasi === 'belum_verifikasi'
                                                || $draftVerifikasi === 'belum_verifikasi'
                                                || $finalVerifikasi === 'belum_verifikasi'
                                                || $hasPendingDraft
                                                || $hasPendingFinal;

                                            if ($isInputRow) {
                                                if ($kelengkapan === 'ada' && $verifikasi === 'tidak_sesuai') {
                                                    $statusCellClass = 'simak-status-yellow';
                                                    $rowDataStatus = 'tidak_sesuai';
                                                } elseif ($kelengkapan === 'tidak' && $verifikasi === 'sesuai') {
                                                    $statusCellClass = 'simak-status-green';
                                                    $rowDataStatus = 'sesuai';
                                                } elseif ($isPendingVerification) {
                                                    $statusCellClass = 'simak-status-yellow';
                                                    $rowDataStatus = 'menunggu';
                                                } elseif ($kelengkapan === 'tidak') {
                                                    $statusCellClass = 'simak-status-red-soft';
                                                    $rowDataStatus = 'belum_ada';
                                                } else {
                                                    $rowDataStatus = 'all';
                                                }
                                            } else {
                                                $rowDataStatus = 'all';
                                            }
                                        }
                                        ?>
                                        <tr data-status="<?= esc($rowDataStatus); ?>" style="<?= esc($bgStyle . ($isPendingVerification && $isInputRow ? ' background-color: #fff3cd;' : '')); ?>">
                                            <td>
                                                <div style="padding-left: <?= (int) $indentPadding; ?>px; white-space: nowrap; <?= esc($fontWeight); ?>">
                                                    <?= esc($noText); ?>
                                                </div>
                                            </td>
                                            <td style="padding-left: <?= (int) ($indentPadding + 8); ?>px; vertical-align: top;">
                                                <div style="<?= esc($fontWeight); ?>">
                                                    <?= esc($uraian); ?>
                                                </div>
                                            </td>
                                            <?php if ($isInputRow): ?>
                                                <td class="<?= esc($statusCellClass); ?>">
                                                    <?php if ($kelengkapan === 'ada'): ?>
                                                        <span class="badge badge-success">Ada</span>
                                                    <?php elseif ($kelengkapan === 'tidak'): ?>
                                                        <span class="badge badge-danger">Tidak Ada</span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($hasDraft): ?>
                                                        <?php if ($draftVerifikasi === 'sesuai'): ?>
                                                            <span class="badge badge-success">Sesuai</span>
                                                        <?php elseif ($draftVerifikasi === 'belum_verifikasi' || ($draftDokumen !== null && $draftVerifikasi === '')): ?>
                                                            <span class="badge badge-warning">Menunggu Verifikasi</span>
                                                        <?php elseif ($draftVerifikasi === 'tidak_sesuai'): ?>
                                                            <span class="badge badge-warning">Tidak Sesuai</span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($finalVerifikasi === 'sesuai'): ?>
                                                        <span class="badge badge-success">Sesuai</span>
                                                    <?php elseif ($finalVerifikasi === 'belum_verifikasi' || ($finalDokumen !== null && $finalVerifikasi === '')): ?>
                                                        <span class="badge badge-warning">Menunggu Verifikasi</span>
                                                    <?php elseif ($finalVerifikasi === 'tidak_sesuai'): ?>
                                                        <span class="badge badge-warning">Tidak Sesuai</span>
                                                    <?php elseif ($finalNoFilePlaceholder): ?>
                                                        <span class="badge badge-warning">Menunggu Verifikasi</span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?= $keterangan !== '' ? esc($keterangan) : '<span class="text-muted">-</span>'; ?>
                                                </td>
                                                <td>
                                                    <?= $pic !== '' ? esc($pic) : '<span class="text-muted">-</span>'; ?>
                                                </td>
                                                <td>
                                                    <?php if ($draftHasFile): ?>
                                                        <a
                                                            href="<?= site_url('admin/kontrak/simak/konstruksi/verifikasi-dokumen/' . (int) ($draftDokumen['id'] ?? 0)); ?>"
                                                            target="_blank"
                                                            rel="noopener"
                                                            class="btn btn-outline-secondary btn-sm"
                                                            title="Lihat dokumen draft: <?= esc((string) ($draftDokumen['file_original_name'] ?? 'Dokumen')); ?>"
                                                        ><i class="fas fa-eye"></i> Lihat Draft</a>
                                                    <?php elseif (is_array($draftDokumen)): ?>
                                                        <span class="badge badge-danger">Tidak Ada</span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($finalHasFile): ?>
                                                        <a
                                                            href="<?= site_url('admin/kontrak/simak/konstruksi/verifikasi-dokumen/' . (int) ($finalDokumen['id'] ?? 0)); ?>"
                                                            target="_blank"
                                                            rel="noopener"
                                                            class="btn btn-info btn-sm"
                                                            title="Lihat dokumen final: <?= esc((string) ($finalDokumen['file_original_name'] ?? 'Dokumen')); ?>"
                                                        ><i class="fas fa-eye"></i> Lihat Final</a>
                                                    <?php elseif (is_array($finalDokumen)): ?>
                                                        <span class="badge badge-danger">Tidak Ada</span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($dokumenCount >= 1): ?>
                                                        <button
                                                            type="button"
                                                            class="btn btn-secondary btn-sm js-open-history-modal"
                                                            data-row-no="<?= esc((string) $rowNo); ?>"
                                                            data-row-label="<?= esc($noText); ?>"
                                                            data-uraian="<?= esc($uraian); ?>"
                                                        >History (<?= esc((string) $dokumenCount); ?>)</button>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="simak-upload-actions">
                                                        <?php if ($canUploadFinal): ?>
                                                            <button
                                                                type="button"
                                                                class="btn btn-success btn-sm js-open-admin-upload-modal"
                                                                data-row-no="<?= esc((string) $rowNo); ?>"
                                                                data-row-label="<?= esc($noText); ?>"
                                                                data-uraian="<?= esc($uraian); ?>"
                                                                data-tipe-dokumen="final"
                                                            >Final</button>
                                                        <?php endif; ?>
                                                        <?php if ($canUploadDraft): ?>
                                                            <button
                                                                type="button"
                                                                class="btn btn-outline-secondary btn-sm js-open-admin-upload-modal"
                                                                data-row-no="<?= esc((string) $rowNo); ?>"
                                                                data-row-label="<?= esc($noText); ?>"
                                                                data-uraian="<?= esc($uraian); ?>"
                                                                data-tipe-dokumen="draft"
                                                            >Draft</button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="simak-upload-actions">
                                                        <?php if ($canVerifyDraft): ?>
                                                            <button
                                                                type="button"
                                                                class="btn btn-outline-secondary btn-sm js-open-upload-modal"
                                                                data-row-no="<?= esc((string) $rowNo); ?>"
                                                                data-row-label="<?= esc($noText); ?>"
                                                                data-uraian="<?= esc($uraian); ?>"
                                                                data-tipe-dokumen="draft"
                                                                data-kelengkapan="<?= esc($draftActionKelengkapan); ?>"
                                                                data-verifikasi="<?= esc($draftActionVerifikasi); ?>"
                                                                data-keterangan="<?= esc($draftActionKeterangan); ?>"
                                                                data-pic="<?= esc($draftActionPic); ?>"
                                                            >Verif Draft</button>
                                                        <?php endif; ?>
                                                        <?php if ($canVerifyFinal): ?>
                                                            <button
                                                                type="button"
                                                                class="btn btn-warning btn-sm js-open-upload-modal"
                                                                data-row-no="<?= esc((string) $rowNo); ?>"
                                                                data-row-label="<?= esc($noText); ?>"
                                                                data-uraian="<?= esc($uraian); ?>"
                                                                data-tipe-dokumen="final"
                                                                data-kelengkapan="<?= esc($finalActionKelengkapan); ?>"
                                                                data-verifikasi="<?= esc($finalActionVerifikasi); ?>"
                                                                data-keterangan="<?= esc($finalActionKeterangan); ?>"
                                                                data-pic="<?= esc($finalActionPic); ?>"
                                                            >Verif Final</button>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if (! $canVerifyDraft && ! $canVerifyFinal): ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            <?php else: ?>
                                                <td colspan="10"></td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php $tabIndex++; ?>
                <?php endforeach; ?>
            </div>
    </div>
</div>

<div class="modal fade" id="modal-admin-upload-dokumen" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Dokumen</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="<?= site_url('admin/kontrak/simak/konstruksi/' . (int) ($item['id'] ?? 0) . '/admin-upload-dokumen'); ?>" enctype="multipart/form-data" id="form-admin-upload-dokumen" novalidate>
                <?= csrf_field(); ?>
                <input type="hidden" name="row_no" id="admin_upload_row_no" value="">
                <input type="hidden" name="tipe_dokumen" id="admin_upload_tipe_dokumen" value="final">
                <div class="modal-body">
                    <div class="alert alert-light border">
                        <div><strong>No:</strong> <span id="admin_upload_row_label">-</span></div>
                        <div><strong>Uraian:</strong> <span id="admin_upload_row_uraian">-</span></div>
                        <div><strong>Tipe:</strong> <span id="admin_upload_tipe_label">Final</span></div>
                    </div>
                    <div class="alert alert-info">
                        <strong>Info:</strong> Dokumen yang diupload akan otomatis terverifikasi sebagai <strong>Lengkap</strong>.
                    </div>
                    <div class="form-group">
                        <label for="upload_kelengkapan_dokumen">Kelengkapan Dokumen <span class="text-danger">*</span></label>
                        <select class="form-control select2-modal" id="upload_kelengkapan_dokumen" name="kelengkapan_dokumen" required>
                            <option value="">-- Pilih --</option>
                            <option value="ada">Dokumen Ada</option>
                            <option value="tidak">Dokumen Tidak Ada</option>
                        </select>
                        <small class="text-muted">Jika memilih Dokumen Memang Tidak Ada, keterangan wajib diisi.</small>
                        <div class="invalid-feedback d-block" id="kelengkapan_error" style="display: none; color: #dc3545;">Kelengkapan dokumen wajib dipilih</div>
                    </div>
                    <div class="form-group">
                        <label for="admin_upload_file">File Dokumen <span class="text-danger">*</span></label>
                        <input
                            type="file"
                            class="form-control"
                            id="admin_upload_file"
                            name="dokumen_file"
                            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                            required
                        >
                        <small class="text-muted">Format: PDF, JPG, PNG, DOC, DOCX, XLS, XLSX (Max 50MB)</small>
                        <div class="invalid-feedback d-block" id="admin_upload_file_error" style="display: none; color: #dc3545;">File wajib dipilih</div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Upload dan Verifikasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-upload-verifikasi" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hasil Verifikasi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="<?= site_url('admin/kontrak/simak/konstruksi/' . (int) ($item['id'] ?? 0) . '/verifikasi/upload'); ?>" enctype="multipart/form-data" id="form-upload-verifikasi" novalidate>
                <?= csrf_field(); ?>
                <input type="hidden" name="row_no" id="upload_row_no" value="">
                <input type="hidden" name="tipe_dokumen" id="upload_tipe_dokumen" value="final">
                <div class="modal-body">
                    <div class="alert alert-light border">
                        <div><strong>No:</strong> <span id="upload_row_label">-</span></div>
                        <div><strong>Uraian:</strong> <span id="upload_row_uraian">-</span></div>
                        <div><strong>Tipe:</strong> <span id="upload_tipe_label">Final</span></div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="upload_verifikasi">Verifikasi Dit. KI <span class="text-danger">*</span></label>
                            <select class="form-control select2-modal" id="upload_verifikasi" name="verifikasi_ki" required>
                                <option value="">-- Pilih --</option>
                                <option value="sesuai">Sesuai</option>
                                <option value="tidak_sesuai">Tidak Sesuai</option>
                            </select>
                            <div class="invalid-feedback d-block" id="verifikasi_error" style="display: none; color: #dc3545;">Verifikasi Dit. KI wajib dipilih</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="upload_keterangan">Keterangan <span class="text-danger" id="keterangan_required_indicator" style="display: none;">*</span></label>
                        <textarea class="form-control" id="upload_keterangan" name="keterangan" rows="3" maxlength="500"></textarea>
                        <div class="invalid-feedback d-block" id="keterangan_error" style="display: none; color: #dc3545;">Keterangan wajib diisi</div>
                    </div>

                    <div class="form-group">
                        <label for="upload_pic">PIC</label>
                        <input type="text" class="form-control" id="upload_pic" name="pic" readonly>
                    </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Verifikasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-history-dokumen" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">History Dokumen Verifikasi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-light border mb-3">
                    <div><strong>No:</strong> <span id="history_row_label">-</span></div>
                    <div><strong>Uraian:</strong> <span id="history_row_uraian">-</span></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0 simak-history-table">
                        <thead class="text-center">
                            <tr>
                                <th>File</th>
                                <th>Tanggal Upload</th>
                                <th>Uploader</th>
                                <th>Ukuran</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="history_dokumen_tbody">
                            <tr>
                                <td colspan="5" class="text-center text-muted">Pilih baris history untuk melihat data.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script>
(function () {
    'use strict';

    var dokumenHistoryByRow = <?= json_encode($dokumenByRow ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    var buttons = document.querySelectorAll('.js-open-upload-modal');
    var historyButtons = document.querySelectorAll('.js-open-history-modal');
    var rowNoInput = document.getElementById('upload_row_no');
    var rowLabelEl = document.getElementById('upload_row_label');
    var rowUraianEl = document.getElementById('upload_row_uraian');
    var tipeDokumenInput = document.getElementById('upload_tipe_dokumen');
    var tipeDokumenLabelEl = document.getElementById('upload_tipe_label');
    var kelengkapanEl = document.getElementById('upload_kelengkapan_dokumen');
    var verifikasiEl = document.getElementById('upload_verifikasi');
    var keteranganEl = document.getElementById('upload_keterangan');
    var picEl = document.getElementById('upload_pic');
    var formEl = document.getElementById('form-upload-verifikasi');
    var historyRowLabelEl = document.getElementById('history_row_label');
    var historyRowUraianEl = document.getElementById('history_row_uraian');
    var historyTbodyEl = document.getElementById('history_dokumen_tbody');
    var keteranganRequiredIndicator = document.getElementById('keterangan_required_indicator');
    var keteranganErrorEl = document.getElementById('keterangan_error');
    var verifikasiErrorEl = document.getElementById('verifikasi_error');
    var kelengkapanErrorEl = document.getElementById('kelengkapan_error');

    var currentUsername = '<?= esc((string) session()->get('username')); ?>';

    var isDriveLinkPath = function (path) {
        var value = String(path || '').trim();
        if (!value) {
            return false;
        }

        try {
            var parsed = new URL(value, window.location.origin);
            var host = String(parsed.hostname || '').toLowerCase();
            return host === 'drive.google.com' || host === 'docs.google.com';
        } catch (error) {
            return false;
        }
    };

    var formatFileSize = function (bytes) {
        var size = Number(bytes || 0);
        if (!Number.isFinite(size) || size <= 0) {
            return '-';
        }

        var units = ['B', 'KB', 'MB', 'GB'];
        var index = 0;
        while (size >= 1024 && index < units.length - 1) {
            size = size / 1024;
            index++;
        }

        return size.toFixed(index === 0 ? 0 : 2) + ' ' + units[index];
    };

    var renderHistoryRows = function (rows) {
        if (!historyTbodyEl) {
            return;
        }

        if (!Array.isArray(rows) || rows.length === 0) {
            historyTbodyEl.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Tidak ada riwayat dokumen.</td></tr>';
            return;
        }

        var html = rows.map(function (doc, index) {
            var fileName = doc && doc.file_original_name ? String(doc.file_original_name) : 'Dokumen';
            var createdAt = doc && doc.created_at ? String(doc.created_at) : '-';
            var createdBy = doc && doc.created_by ? String(doc.created_by) : '-';
            var size = formatFileSize(doc && doc.file_size ? doc.file_size : 0);
            var docId = doc && doc.id ? String(doc.id) : '0';
            var label = index === 0 ? 'Terbaru' : 'Riwayat ' + (index + 1);
            var path = doc && doc.file_relative_path ? String(doc.file_relative_path) : '';
            var isDrive = isDriveLinkPath(path);
            var actionLabel = isDrive ? 'Buka Link' : 'Lihat Dokumen';
            var actionIcon = isDrive ? 'fa-external-link-alt' : 'fa-eye';
            var hasFile = path.trim() !== '';

            return '<tr>' +
                '<td><div class="font-weight-bold">' + fileName + '</div><small class="text-muted">' + label + '</small></td>' +
                '<td>' + createdAt + '</td>' +
                '<td>' + createdBy + '</td>' +
                '<td>' + size + '</td>' +
                '<td class="text-center">' + (hasFile ? '<a href="<?= site_url('admin/kontrak/simak/konstruksi/verifikasi-dokumen/'); ?>' + docId + '" target="_blank" rel="noopener" class="btn btn-info btn-sm"><i class="fas ' + actionIcon + '"></i> ' + actionLabel + '</a>' : '<span class="badge badge-danger">Tidak Ada</span>') + '</td>' +
            '</tr>';
        }).join('');

        historyTbodyEl.innerHTML = html;
    };

    var updateVerifikasiLogic = function () {
        if (!verifikasiEl || !keteranganEl) return;

        var selectedKelengkapan = kelengkapanEl ? kelengkapanEl.value : '';
        var selectedValue = verifikasiEl.value;

        // Always clear errors first
        if (keteranganErrorEl) {
            keteranganErrorEl.style.display = 'none';
        }
        if (verifikasiErrorEl) {
            verifikasiErrorEl.style.display = 'none';
        }
        if (kelengkapanErrorEl) {
            kelengkapanErrorEl.style.display = 'none';
        }

        if (selectedKelengkapan === 'tidak') {
            keteranganEl.setAttribute('required', 'required');
            if (keteranganRequiredIndicator) {
                keteranganRequiredIndicator.style.display = 'inline';
            }
        } else if (selectedValue === 'sesuai') {
            keteranganEl.value = 'Verifikasi Sesuai';
            keteranganEl.removeAttribute('required');
            if (keteranganRequiredIndicator) {
                keteranganRequiredIndicator.style.display = 'none';
            }
        } else if (selectedValue === 'tidak_sesuai') {
            keteranganEl.value = '';
            keteranganEl.setAttribute('required', 'required');
            if (keteranganRequiredIndicator) {
                keteranganRequiredIndicator.style.display = 'inline';
            }
        } else {
            keteranganEl.value = '';
            keteranganEl.removeAttribute('required');
            if (keteranganRequiredIndicator) {
                keteranganRequiredIndicator.style.display = 'none';
            }
        }
    };

    buttons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (rowNoInput) {
                rowNoInput.value = this.getAttribute('data-row-no') || '';
            }
            if (rowLabelEl) {
                rowLabelEl.textContent = this.getAttribute('data-row-label') || '-';
            }
            if (rowUraianEl) {
                rowUraianEl.textContent = this.getAttribute('data-uraian') || '-';
            }
            if (tipeDokumenInput) {
                var tipeDokumen = String(this.getAttribute('data-tipe-dokumen') || 'final').toLowerCase();
                tipeDokumenInput.value = tipeDokumen === 'draft' ? 'draft' : 'final';
                if (tipeDokumenLabelEl) {
                    tipeDokumenLabelEl.textContent = tipeDokumenInput.value === 'draft' ? 'Draft' : 'Final';
                }
            }
            if (kelengkapanEl) {
                kelengkapanEl.value = this.getAttribute('data-kelengkapan') || 'ada';
                if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                    try {
                        window.jQuery(kelengkapanEl).trigger('change');
                    } catch (e) {}
                }
            }
            if (verifikasiEl) {
                verifikasiEl.value = this.getAttribute('data-verifikasi') || '';
                if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                    try {
                        window.jQuery(verifikasiEl).select2('close');
                        window.jQuery(verifikasiEl).trigger('change');
                    } catch (e) {}
                }
            }
            if (keteranganEl) {
                keteranganEl.value = this.getAttribute('data-keterangan') || '';
            }
            if (picEl) {
                picEl.value = currentUsername;
            }

            updateVerifikasiLogic();

            if (window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.modal === 'function') {
                window.jQuery('#modal-upload-verifikasi').modal('show');
            }
        });
    });

    if (verifikasiEl) {
        verifikasiEl.addEventListener('change', function () {
            updateVerifikasiLogic();
            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                try {
                    window.jQuery(this).select2('close');
                } catch (e) {}
            }
        });
        
        // Handle select2 change event if select2 is initialized
        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
            try {
                window.jQuery(verifikasiEl).on('select2:select', function () {
                    updateVerifikasiLogic();
                    window.jQuery(this).select2('close');
                });
            } catch (e) {}
        }
    }

    if (kelengkapanEl) {
        kelengkapanEl.addEventListener('change', function () {
            updateVerifikasiLogic();
            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                try {
                    window.jQuery(this).select2('close');
                } catch (e) {}
            }
        });
    }

    if (formEl) {
        var isForcedSubmit = false;

        var showLoadingAndSubmit = function () {
            isForcedSubmit = true;

            if (window.Swal) {
                window.Swal.fire({
                    title: 'Menyimpan...',
                    text: 'Mohon tunggu, data sedang disimpan',
                    icon: 'info',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: function () {
                        window.Swal.showLoading();
                    }
                });
            }

            setTimeout(function () {
                formEl.submit();
            }, 50);
        };

        formEl.addEventListener('submit', function (e) {
            var kelengkapanValue = kelengkapanEl ? kelengkapanEl.value : '';
            var verifikasiValue = verifikasiEl ? verifikasiEl.value : '';
            var keteranganValue = keteranganEl ? keteranganEl.value.trim() : '';

            if (isForcedSubmit) {
                return;
            }

            if (!kelengkapanValue) {
                if (kelengkapanErrorEl) {
                    kelengkapanErrorEl.style.display = 'none';
                }

                e.preventDefault();
                e.stopPropagation();

                if (window.Swal) {
                    window.Swal.fire({
                        title: 'Peringatan',
                        text: 'Wajib pilih kelengkapan dokumen.',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    }).then(function () {
                        if (kelengkapanEl) {
                            kelengkapanEl.focus();
                        }
                    });
                } else {
                    alert('Wajib pilih kelengkapan dokumen.');
                    if (kelengkapanEl) {
                        kelengkapanEl.focus();
                    }
                }

                return;
            } else if (kelengkapanErrorEl) {
                kelengkapanErrorEl.style.display = 'none';
            }

            if (!verifikasiValue) {
                if (verifikasiErrorEl) {
                    verifikasiErrorEl.style.display = 'none';
                }

                e.preventDefault();
                e.stopPropagation();

                if (window.Swal) {
                    window.Swal.fire({
                        title: 'Peringatan',
                        text: 'Wajib pilih verifikasi.',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    }).then(function () {
                        if (verifikasiEl) {
                            verifikasiEl.focus();
                        }
                    });
                } else {
                    alert('Wajib pilih verifikasi.');
                    if (verifikasiEl) {
                        verifikasiEl.focus();
                    }
                }

                return;
            } else {
                if (verifikasiErrorEl) {
                    verifikasiErrorEl.style.display = 'none';
                }
            }

            if ((kelengkapanValue === 'tidak' || verifikasiValue === 'tidak_sesuai') && !keteranganValue) {
                if (keteranganErrorEl) {
                    keteranganErrorEl.style.display = 'none';
                }

                e.preventDefault();
                e.stopPropagation();

                if (window.Swal) {
                    window.Swal.fire({
                        title: 'Peringatan',
                        text: 'Wajib isi keterangan.',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    }).then(function () {
                        if (keteranganEl) {
                            keteranganEl.focus();
                        }
                    });
                } else {
                    alert('Wajib isi keterangan.');
                    if (keteranganEl) {
                        keteranganEl.focus();
                    }
                }

                return;
            } else {
                if (keteranganErrorEl) {
                    keteranganErrorEl.style.display = 'none';
                }
            }

            e.preventDefault();
            e.stopPropagation();

            if (kelengkapanValue === 'tidak') {
                if (window.Swal) {
                    window.Swal.fire({
                        title: 'Konfirmasi Status Dokumen',
                        text: 'Simpan status bahwa dokumen memang tidak ada tanpa upload file?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Simpan',
                        cancelButtonText: 'Batal'
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            showLoadingAndSubmit();
                        }
                    });
                } else if (confirm('Simpan status bahwa dokumen memang tidak ada?')) {
                    showLoadingAndSubmit();
                }
                return;
            }

            if (verifikasiValue === 'sesuai') {
                if (window.Swal) {
                    window.Swal.fire({
                        title: 'Konfirmasi Verifikasi',
                        text: 'Pastikan data sudah benar. Lanjutkan verifikasi sesuai?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Lanjutkan',
                        cancelButtonText: 'Batal',
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            showLoadingAndSubmit();
                        }
                    });
                } else {
                    var okSesuai = window.confirm('Pastikan data sudah benar. Lanjutkan verifikasi sesuai?');
                    if (okSesuai) {
                        showLoadingAndSubmit();
                    }
                }
                return;
            }

            if (verifikasiValue === 'tidak_sesuai') {
                if (window.Swal) {
                    window.Swal.fire({
                        title: 'Konfirmasi Verifikasi',
                        text: 'Lanjutkan simpan verifikasi tidak sesuai?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Lanjutkan',
                        cancelButtonText: 'Batal',
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            showLoadingAndSubmit();
                        }
                    });
                } else {
                    var okTidakSesuai = window.confirm('Lanjutkan simpan verifikasi tidak sesuai?');
                    if (okTidakSesuai) {
                        showLoadingAndSubmit();
                    }
                }
                return;
            }
        });
    }

    // Close select2 when modal is hidden and shown
    if (window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.modal === 'function') {
        try {
            var modalEl = window.jQuery('#modal-upload-verifikasi');
            
            modalEl.on('show.bs.modal', function () {
                // Reset form when modal is shown
                if (formEl) {
                    isForcedSubmit = false;
                    if (keteranganErrorEl) {
                        keteranganErrorEl.style.display = 'none';
                    }
                    if (verifikasiErrorEl) {
                        verifikasiErrorEl.style.display = 'none';
                    }
                }
            });
            
            modalEl.on('hide.bs.modal', function () {
                if (verifikasiEl && window.jQuery.fn.select2) {
                    try {
                        window.jQuery(verifikasiEl).select2('close');
                    } catch (e) {}
                }
            });
        } catch (e) {}
    }

    historyButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var rowNo = parseInt(this.getAttribute('data-row-no') || '0', 10);
            var label = this.getAttribute('data-row-label') || '-';
            var uraian = this.getAttribute('data-uraian') || '-';
            var rows = dokumenHistoryByRow[rowNo] || [];

            console.log('History button clicked:', { rowNo: rowNo, rows: rows });

            if (historyRowLabelEl) {
                historyRowLabelEl.textContent = label;
            }
            if (historyRowUraianEl) {
                historyRowUraianEl.textContent = uraian;
            }

            renderHistoryRows(rows);

            // Use jQuery modal exactly like other modals in this file
            console.log('About to call modal show');
            try {
                // Ensure modal is appended to <body> to avoid ancestor stacking/transform hiding it
                window.jQuery('#modal-history-dokumen').appendTo('body').modal('show');
                console.log('Modal show called successfully (appended to body)');
            } catch (error) {
                console.error('Error showing modal:', error);
            }
        });
    });

    // Admin Upload Dokumen
    var adminUploadButtons = document.querySelectorAll('.js-open-admin-upload-modal');
    var adminUploadRowNoInput = document.getElementById('admin_upload_row_no');
    var adminUploadRowLabelEl = document.getElementById('admin_upload_row_label');
    var adminUploadRowUraianEl = document.getElementById('admin_upload_row_uraian');
    var adminUploadTypeInput = document.getElementById('admin_upload_tipe_dokumen');
    var adminUploadTypeLabelEl = document.getElementById('admin_upload_tipe_label');
    var adminUploadFileInput = document.getElementById('admin_upload_file');
    var adminUploadForm = document.getElementById('form-admin-upload-dokumen');

    adminUploadButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (adminUploadRowNoInput) {
                adminUploadRowNoInput.value = this.getAttribute('data-row-no') || '';
            }
            if (adminUploadRowLabelEl) {
                adminUploadRowLabelEl.textContent = this.getAttribute('data-row-label') || '-';
            }
            if (adminUploadRowUraianEl) {
                adminUploadRowUraianEl.textContent = this.getAttribute('data-uraian') || '-';
            }
            if (adminUploadTypeInput) {
                var uploadType = String(this.getAttribute('data-tipe-dokumen') || 'final').toLowerCase();
                adminUploadTypeInput.value = uploadType === 'draft' ? 'draft' : 'final';
                if (adminUploadTypeLabelEl) {
                    adminUploadTypeLabelEl.textContent = adminUploadTypeInput.value === 'draft' ? 'Draft' : 'Final';
                }
            }
            if (adminUploadFileInput) {
                adminUploadFileInput.value = '';
            }
            window.jQuery('#modal-admin-upload-dokumen').modal('show');
        });
    });

    if (adminUploadForm) {
        adminUploadForm.addEventListener('submit', function (e) {
            if (adminUploadFileInput && (!adminUploadFileInput.value || adminUploadFileInput.value === '')) {
                e.preventDefault();
                var fileError = document.getElementById('admin_upload_file_error');
                if (fileError) {
                    fileError.style.display = 'block';
                }
                return false;
            }
        });
    }

    // Filter functionality
    var filterSelect = document.getElementById('filter-status');
    var filterInfo = document.getElementById('filter-info');
    var resetBtn = document.getElementById('btn-reset-filter');
    var sectionTabs = document.querySelectorAll('[data-toggle="tab"]');
    var allTableRows = [];

    // Collect all rows by section
    function initFilterRows() {
        allTableRows = [];
        document.querySelectorAll('.simak-verifikasi-table tbody tr').forEach(function(row) {
            allTableRows.push(row);
        });
    }

    function getRowStatus(row) {
        // Check data attributes first
        var dataStatus = row.getAttribute('data-status');
        if (dataStatus) return dataStatus;

        // Fallback: check cell contents
        var kelengkapanCell = row.querySelector('td:nth-child(3)');
        var draftCell = row.querySelector('td:nth-child(4)');
        var finalCell = row.querySelector('td:nth-child(5)');
        var verifikasiCell = row.querySelector('td:nth-child(3)'); // Status cell

        var kelengkapan = kelengkapanCell ? kelengkapanCell.textContent.trim() : '';
        var hasDraft = row.querySelector('[data-tipe-dokumen="draft"]') !== null;

        // Check verification status
        var draftBadge = draftCell ? draftCell.querySelector('.badge') : null;
        var finalBadge = finalCell ? finalCell.querySelector('.badge') : null;

        var hasSesuai = (draftBadge && draftBadge.textContent.includes('Sesuai')) ||
                       (finalBadge && finalBadge.textContent.includes('Sesuai'));
        var hasMenunggu = (draftBadge && draftBadge.textContent.includes('Menunggu')) ||
                         (finalBadge && finalBadge.textContent.includes('Menunggu'));
        var hasTidakSesuai = (draftBadge && draftBadge.textContent.includes('Tidak Sesuai')) ||
                            (finalBadge && finalBadge.textContent.includes('Tidak Sesuai'));
        var hasTidakAda = kelengkapan.includes('Tidak');

        if (hasTidakAda && !hasSesuai && !hasMenunggu) {
            return 'belum_ada';
        }
        if (hasSesuai) return 'sesuai';
        if (hasTidakSesuai) return 'tidak_sesuai';
        if (hasMenunggu) return 'menunggu';

        return 'all';
    }

    function applyFilter(filterValue) {
        var visibleCount = 0;
        var totalCount = 0;

        document.querySelectorAll('.simak-verifikasi-table tbody tr').forEach(function(row) {
            // Skip section header rows
            if (row.querySelector('td[colspan]')) {
                row.style.display = '';
                return;
            }

            totalCount++;
            var status = getRowStatus(row);

            if (filterValue === 'all' || status === filterValue) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Update filter info
        if (filterInfo) {
            if (filterValue === 'all') {
                filterInfo.textContent = 'Menampilkan semua item';
            } else {
                var filterLabels = {
                    'menunggu': 'Menunggu Verifikasi',
                    'sesuai': 'Sesuai',
                    'tidak_sesuai': 'Tidak Sesuai',
                    'belum_ada': 'Belum Ada'
                };
                filterInfo.textContent = 'Menampilkan ' + visibleCount + ' dari ' + totalCount + ' item (' + (filterLabels[filterValue] || filterValue) + ')';
            }
        }
    }

    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            applyFilter(this.value);
        });
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            if (filterSelect) {
                filterSelect.value = 'all';
            }
            applyFilter('all');
        });
    }

    // Initialize filter
    initFilterRows();

    // Re-initialize filter when tab changes
    sectionTabs.forEach(function(tab) {
        tab.addEventListener('shown.bs.tab', function() {
            initFilterRows();
            if (filterSelect) {
                applyFilter(filterSelect.value);
            }
        });
    });
})();
</script>
<?= $this->endSection(); ?>
