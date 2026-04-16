<?php
    helper('custom');

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
        </div>
        <div class="share-kelengkapan-card belum-sesuai">
            <div class="share-kelengkapan-label">Belum Sesuai</div>
            <div class="share-kelengkapan-value"><?= number_format((float) ($kelengkapanPercentage['belum_sesuai_persen'] ?? 0), 2, ',', '.'); ?>%</div>
        </div>
        <div class="share-kelengkapan-card menunggu-verifikasi">
            <div class="share-kelengkapan-label">Menunggu Verifikasi</div>
            <div class="share-kelengkapan-value"><?= number_format((float) ($kelengkapanPercentage['belum_verifikasi_persen'] ?? 0), 2, ',', '.'); ?>%</div>
        </div>
        <div class="share-kelengkapan-card belum-ada">
            <div class="share-kelengkapan-label">Belum Ada</div>
            <div class="share-kelengkapan-value"><?= number_format((float) ($kelengkapanPercentage['belum_ada_persen'] ?? 0), 2, ',', '.'); ?>%</div>
        </div>
    </div>

    <div class="share-card p-3 p-md-4 mb-3">
        <h5 class="mb-1"><?= esc((string) ($item['nama_paket'] ?? '-')); ?></h5>
        <div class="text-muted mb-0">
            Nomor Kontrak: <?= esc((string) ($item['nomor_kontrak'] ?? '-')); ?>
            · Tahun Anggaran: <?= esc((string) ($item['tahun_anggaran'] ?? '-')); ?>
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
                <div class="modal-body">
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
    'use strict';

    var openButtons = document.querySelectorAll('.js-open-upload-modal');
    var uploadForm = document.getElementById('form-upload-share-simak');
    var rowNoEl = document.getElementById('upload_row_no_modal');
    var rowLabelEl = document.getElementById('upload_row_label_modal');
    var rowUraianEl = document.getElementById('upload_row_uraian_modal');

    openButtons.forEach(function (button) {
        button.addEventListener('click', function () {
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

    if (uploadForm) {
        uploadForm.addEventListener('submit', function () {
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
    }
})();
</script>
</body>
</html>
