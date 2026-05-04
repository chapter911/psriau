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

    .simak-status-yellow {
        background-color: #fff3cd;
    }

    .simak-status-red-soft {
        background-color: #f8d7da;
    }

    .js-simak-toggle-columns.is-hidden {
        display: none !important;
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
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="card-title mb-0">Kelengkapan Dokumen dan Verifikasi Dit. KI</h2>
        <div class="ml-auto">
            <button type="button" class="btn btn-outline-secondary btn-sm js-toggle-simak-columns" data-state="collapsed">
                Tampilkan Kolom Detail
            </button>
        </div>
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
                            <table class="table table-bordered table-sm simak-verifikasi-table" style="min-width: 1900px;">
                                <thead class="text-center">
                                    <tr>
                                        <th style="width: 70px; position: sticky; top: 0; z-index: 6; background: #2F3A45; color: #fff;">No</th>
                                        <th style="width: 320px; position: sticky; top: 0; z-index: 6; background: #2F3A45; color: #fff;">Tahapan</th>
                                        <th style="width: 280px; position: sticky; top: 0; z-index: 6; background: #2F3A45; color: #fff;">Bentuk Dokumen</th>
                                        <th style="width: 260px; position: sticky; top: 0; z-index: 6; background: #2F3A45; color: #fff;">Referensi</th>
                                        <th class="js-simak-toggle-columns is-hidden" style="width: 320px; position: sticky; top: 0; z-index: 6; background: #2F3A45; color: #fff;">Kriteria Administrasi</th>
                                        <th class="js-simak-toggle-columns is-hidden" style="width: 320px; position: sticky; top: 0; z-index: 6; background: #2F3A45; color: #fff;">Kriteria Substansi</th>
                                        <th class="js-simak-toggle-columns is-hidden" style="width: 320px; position: sticky; top: 0; z-index: 6; background: #2F3A45; color: #fff;">Sumber Dokumen Hasil Integrasi</th>
                                        <th style="width: 170px; position: sticky; top: 0; z-index: 6; background: #2F3A45; color: #fff;">Kelengkapan Dokumen</th>
                                        <th style="width: 170px; position: sticky; top: 0; z-index: 6; background: #2F3A45; color: #fff;">Verifikasi Dit. KI</th>
                                        <th style="width: 280px; position: sticky; top: 0; z-index: 6; background: #2F3A45; color: #fff;">Keterangan</th>
                                        <th style="width: 170px; position: sticky; top: 0; z-index: 6; background: #2F3A45; color: #fff;">PIC</th>
                                        <th style="width: 280px; position: sticky; top: 0; z-index: 6; background: #2F3A45; color: #fff;">Dokumen</th>
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
                                            $indentLevel = (int) ($row['indent_level'] ?? 0);
                                            $rowType = (string) ($row['row_type'] ?? 'detail');
                                            $hasChildren = (bool) ($row['has_children'] ?? false);
                                            $isLeaf = (bool) ($row['is_leaf'] ?? false);
                                            $isPromotedSubsectionInput = $isLeaf
                                                && $rowType === 'subsection_header'
                                                && preg_match('/^\d+$/', $displayNo) === 1;
                                            $isInputRow = $isLeaf
                                                && (! in_array($rowType, ['section_header', 'subsection_header'], true) || $isPromotedSubsectionInput);
                                            $existing = $verifikasiByRow[$rowNo] ?? [];
                                            $kelengkapan = (string) ($existing['kelengkapan_dokumen'] ?? '');
                                            $verifikasi = (string) ($existing['verifikasi_ki'] ?? '');
                                            $keterangan = (string) ($existing['keterangan'] ?? '');
                                            $pic = (string) ($existing['pic'] ?? '');
                                            $uraian = (string) ($row['uraian'] ?? '');
                                            $bentukDokumen = trim((string) ($row['bentuk_dokumen'] ?? ''));
                                            $referensi = trim((string) ($row['referensi'] ?? ''));
                                            $kriteriaAdministrasi = trim((string) ($row['kriteria_administrasi'] ?? ''));
                                            $kriteriaSubstansi = trim((string) ($row['kriteria_substansi'] ?? ''));
                                            $sumberDokumenIntegrasi = trim((string) ($row['sumber_dokumen_hasil_integrasi'] ?? ''));
                                            $dokumenRows = $dokumenByRow[$rowNo] ?? [];
                                            $dokumenCount = count($dokumenRows);
                                            $latestDokumen = $dokumenRows[0] ?? null;
                                            $latestPath = is_array($latestDokumen) ? trim((string) ($latestDokumen['file_relative_path'] ?? '')) : '';
                                            $latestHost = strtolower((string) parse_url($latestPath, PHP_URL_HOST));
                                            $isDriveLink = in_array($latestHost, ['drive.google.com', 'docs.google.com'], true);
                                            $dokumenActionLabel = $isDriveLink ? 'Buka Link' : 'Lihat Dokumen';
                                            $indentPadding = max(0, $indentLevel) * 18;
                                            $isGroup = ($hasChildren || in_array($rowType, ['section_header', 'subsection_header'], true))
                                                && ! $isPromotedSubsectionInput;
                                            $fontWeight = $isGroup ? 'font-weight: 700;' : ($indentLevel > 1 ? 'font-weight: 500;' : 'font-weight: 600;');
                                            $bgStyle = $isGroup ? 'background-color: #f2f4f7;' : '';
                                            $noText = $displayNo !== '' ? $displayNo . '.' : '';
                                            $statusCellClass = '';

                                            if ($isInputRow) {
                                                if ($kelengkapan === 'ada' && $verifikasi === 'tidak_sesuai') {
                                                    $statusCellClass = 'simak-status-yellow';
                                                } elseif ($kelengkapan !== 'ada') {
                                                    $statusCellClass = 'simak-status-red-soft';
                                                }
                                            }
                                        ?>
                                        <tr style="<?= esc($bgStyle); ?>">
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
                                            <td style="text-align: left; vertical-align: top; white-space: pre-line;"><?= $bentukDokumen !== '' ? esc($bentukDokumen) : '<span class="text-muted">-</span>'; ?></td>
                                            <td style="text-align: left; vertical-align: top; white-space: pre-line;"><?= $referensi !== '' ? esc($referensi) : '<span class="text-muted">-</span>'; ?></td>
                                            <td class="js-simak-toggle-columns is-hidden" style="text-align: left; vertical-align: top; white-space: pre-line;"><?= $kriteriaAdministrasi !== '' ? esc($kriteriaAdministrasi) : '<span class="text-muted">-</span>'; ?></td>
                                            <td class="js-simak-toggle-columns is-hidden" style="text-align: left; vertical-align: top; white-space: pre-line;"><?= $kriteriaSubstansi !== '' ? esc($kriteriaSubstansi) : '<span class="text-muted">-</span>'; ?></td>
                                            <td class="js-simak-toggle-columns is-hidden" style="text-align: left; vertical-align: top; white-space: pre-line;"><?= $sumberDokumenIntegrasi !== '' ? esc($sumberDokumenIntegrasi) : '<span class="text-muted">-</span>'; ?></td>
                                            <?php if ($isInputRow): ?>
                                                <td class="<?= esc($statusCellClass); ?>">
                                                    <?php if ($kelengkapan === 'ada'): ?>
                                                        <span class="badge badge-success">Ada</span>
                                                    <?php elseif ($kelengkapan === 'tidak'): ?>
                                                        <span class="badge badge-danger">Tidak</span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="<?= esc($statusCellClass); ?>">
                                                    <?php if ($verifikasi === 'sesuai'): ?>
                                                        <span class="badge badge-success">Sesuai</span>
                                                    <?php elseif ($verifikasi === 'tidak_sesuai'): ?>
                                                        <span class="badge badge-warning">Tidak Sesuai</span>
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
                                                    <div class="d-flex align-items-center" style="gap: 6px; flex-wrap: wrap;">
                                                        <?php if (is_array($latestDokumen)): ?>
                                                            <a
                                                                href="<?= site_url('admin/kontrak/simak/konsultasi/verifikasi-dokumen/' . (int) ($latestDokumen['id'] ?? 0)); ?>"
                                                                target="_blank"
                                                                rel="noopener"
                                                                class="btn btn-info btn-sm"
                                                                title="<?= esc($dokumenActionLabel); ?> terbaru: <?= esc((string) ($latestDokumen['file_original_name'] ?? 'Dokumen')); ?>"
                                                                aria-label="<?= esc($dokumenActionLabel); ?> terbaru <?= esc((string) ($latestDokumen['file_original_name'] ?? 'Dokumen')); ?>"
                                                            ><i class="fas <?= $isDriveLink ? 'fa-external-link-alt' : 'fa-eye'; ?>"></i> <?= esc($dokumenActionLabel); ?></a>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </div>
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
                                                    <button
                                                        type="button"
                                                        class="btn btn-success btn-sm js-open-admin-upload-modal"
                                                        data-row-no="<?= esc((string) $rowNo); ?>"
                                                        data-row-label="<?= esc($noText); ?>"
                                                        data-uraian="<?= esc($uraian); ?>"
                                                    >Upload</button>
                                                </td>
                                                <td>
                                                    <?php if (is_array($latestDokumen)): ?>
                                                        <button
                                                            type="button"
                                                            class="btn btn-warning btn-sm js-open-upload-modal"
                                                            data-row-no="<?= esc((string) $rowNo); ?>"
                                                            data-row-label="<?= esc($noText); ?>"
                                                            data-uraian="<?= esc($uraian); ?>"
                                                            data-kelengkapan="<?= esc($kelengkapan); ?>"
                                                            data-verifikasi="<?= esc($verifikasi); ?>"
                                                            data-keterangan="<?= esc($keterangan); ?>"
                                                            data-pic="<?= esc($pic); ?>"
                                                            data-created-by="<?= esc((string) ($latestDokumen['created_by'] ?? '')); ?>"
                                                        >Verifikasi</button>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            <?php else: ?>
                                                <td colspan="8"></td>
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
            <form method="post" action="<?= site_url('admin/kontrak/simak/konsultasi/' . (int) ($item['id'] ?? 0) . '/admin-upload-dokumen'); ?>" enctype="multipart/form-data" id="form-admin-upload-dokumen" novalidate>
                <?= csrf_field(); ?>
                <input type="hidden" name="row_no" id="admin_upload_row_no" value="">
                <div class="modal-body">
                    <div class="alert alert-light border">
                        <div><strong>No:</strong> <span id="admin_upload_row_label">-</span></div>
                        <div><strong>Uraian:</strong> <span id="admin_upload_row_uraian">-</span></div>
                    </div>
                    <div class="alert alert-info">
                        <strong>Info:</strong> Dokumen yang diupload akan otomatis terverifikasi sebagai <strong>Lengkap</strong>.
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
            <form method="post" action="<?= site_url('admin/kontrak/simak/konsultasi/' . (int) ($item['id'] ?? 0) . '/verifikasi/upload'); ?>" enctype="multipart/form-data" id="form-upload-verifikasi" novalidate>
                <?= csrf_field(); ?>
                <input type="hidden" name="row_no" id="upload_row_no" value="">
                <div class="modal-body">
                    <div class="alert alert-light border">
                        <div><strong>No:</strong> <span id="upload_row_label">-</span></div>
                        <div><strong>Uraian:</strong> <span id="upload_row_uraian">-</span></div>
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

                    <div class="form-group">
                        <label for="notification_email">Email Penerima Notifikasi</label>
                        <input type="email" class="form-control" id="notification_email" name="notification_email" placeholder="contoh@email.com">
                        <small class="text-muted">Jika diisi, notifikasi verifikasi akan dikirim ke email ini</small>
                    </div>


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
    var toggleColumnsButton = document.querySelector('.js-toggle-simak-columns');
    var toggleColumnsCells = document.querySelectorAll('.js-simak-toggle-columns');

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

            return '<tr>' +
                '<td><div class="font-weight-bold">' + fileName + '</div><small class="text-muted">' + label + '</small></td>' +
                '<td>' + createdAt + '</td>' +
                '<td>' + createdBy + '</td>' +
                '<td>' + size + '</td>' +
                '<td class="text-center"><a href="<?= site_url('admin/kontrak/simak/konsultasi/verifikasi-dokumen/'); ?>' + docId + '" target="_blank" rel="noopener" class="btn btn-info btn-sm"><i class="fas ' + actionIcon + '"></i> ' + actionLabel + '</a></td>' +
            '</tr>';
        }).join('');

        historyTbodyEl.innerHTML = html;
    };

    var updateVerifikasiLogic = function () {
        if (!verifikasiEl || !keteranganEl) return;
        
        var selectedValue = verifikasiEl.value;
        
        // Always clear errors first
        if (keteranganErrorEl) {
            keteranganErrorEl.style.display = 'none';
        }
        if (verifikasiErrorEl) {
            verifikasiErrorEl.style.display = 'none';
        }
        
        if (selectedValue === 'sesuai') {
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

            // Auto-fill notification email from uploader info if available
            var createdBy = this.getAttribute('data-created-by') || '';
            var extractedEmail = '';
            if (createdBy) {
                var angleMatch = createdBy.match(/<([^>]+)>/);
                if (angleMatch && angleMatch[1]) {
                    extractedEmail = angleMatch[1].trim();
                } else {
                    var emailMatch = createdBy.match(/([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,})/i);
                    if (emailMatch && emailMatch[1]) {
                        extractedEmail = emailMatch[1].trim();
                    }
                }
            }

            var notificationEmailInput = document.getElementById('notification_email');
            if (notificationEmailInput) {
                notificationEmailInput.value = extractedEmail;
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

    if (formEl) {
        var isForcedSubmit = false;


    if (toggleColumnsButton) {
        toggleColumnsButton.addEventListener('click', function () {
            var isExpanded = this.getAttribute('data-state') !== 'collapsed';
            toggleColumnsCells.forEach(function (el) {
                if (isExpanded) {
                    el.classList.add('is-hidden');
                } else {
                    el.classList.remove('is-hidden');
                }
            });

            this.setAttribute('data-state', isExpanded ? 'collapsed' : 'expanded');
            this.textContent = isExpanded ? 'Tampilkan Kolom Detail' : 'Sembunyikan Kolom Detail';
        });
    }
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
            var verifikasiValue = verifikasiEl ? verifikasiEl.value : '';
            var keteranganValue = keteranganEl ? keteranganEl.value.trim() : '';

            if (isForcedSubmit) {
                return;
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

            if (verifikasiValue === 'tidak_sesuai' && !keteranganValue) {
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
})();
</script>
<?= $this->endSection(); ?>
