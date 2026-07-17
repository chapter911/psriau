<?= $this->extend(($is_modal ?? false) ? 'layouts/modal_iframe' : 'layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
    $input = $current_input ?? [];
    $pegawaiOptions = $pegawai_options ?? [];
    $kabupatenOptions = $kabupaten_options ?? [];
    $creatorName = (string) ($creator_name ?? 'system');
    $defaultApproverId = (int) ($default_approver_id ?? 0);
    $selectedPelaksana = array_map('intval', (array) ($input['pelaksana_id'] ?? []));
    $selectedKabupaten = trim((string) ($input['kota_tujuan'] ?? ''));
    $selectedKabupatenExists = false;
    $formAction = (string) ($form_action ?? site_url('admin/surat/perjalanan-dinas/buat'));
    $isEdit = (bool) ($is_edit ?? false);
    $submitLabelPrimary = (string) ($submit_label_primary ?? 'Simpan Final');
    $existingFotoDokumentasi = $existing_foto_dokumentasi ?? [];
    $resolvePhotoSrc = static function ($photo): string {
        // Format baru: file_path berupa URL relatif (/uploads/laporan/...)
        if (is_array($photo)) {
            foreach (['file_path', 'src', 'url', 'path'] as $key) {
                $value = trim((string) ($photo[$key] ?? ''));
                if ($value !== '') return $value;
            }
            // Fallback ke data_uri untuk data lama (base64)
            $dataUri = trim((string) ($photo['data_uri'] ?? ''));
            if ($dataUri !== '') return $dataUri;
        } elseif (is_string($photo)) {
            $value = trim($photo);
            if ($value !== '') return $value;
        }

        return '';
    };
    $formatPegawaiLabel = static function (array $pegawai): string {
        $nama = trim((string) ($pegawai['nama'] ?? $pegawai['display_name'] ?? $pegawai['display_label'] ?? 'Pegawai'));
        $nip = trim((string) ($pegawai['nip'] ?? ''));

        if ($nama === '') {
            $nama = 'Pegawai';
        }

        return $nip !== '' ? ($nama . ' | NIP ' . $nip) : $nama;
    };
?>

<style>
    .trip-form-wrap {
        max-width: 1440px;
        margin: 0 auto;
    }

    .trip-headline {
        font-weight: 700;
        letter-spacing: .2px;
    }

    .trip-card {
        border: 1px solid #e9eef5;
        border-radius: 14px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .05);
    }

    .trip-section-title {
        font-weight: 600;
        color: #1f2937;
    }

    .photo-dropzone {
        border: 1px solid #e2e8f0;
        background: #ffffff;
        border-radius: 16px;
        padding: 20px;
        transition: border-color .15s ease, box-shadow .15s ease;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
    }

    .photo-dropzone.is-dragover {
        border-color: #0A66C2;
        box-shadow: 0 0 0 3px rgba(10, 102, 194, 0.15);
    }

    .photo-dropzone__target {
        border-radius: 12px;
        border: 2px dashed #cbd5e1;
        padding: 32px 16px;
        background: #f8fafc;
        color: #64748b;
        font-weight: 500;
        cursor: pointer;
        transition: border-color .15s ease, background-color .15s ease, color .15s ease;
    }

    .photo-dropzone__target:hover,
    .photo-dropzone.is-dragover .photo-dropzone__target {
        border-color: #0A66C2;
        background: #f0f7ff;
        color: #0A66C2;
    }

    #selectedPhotoPreview,
    #existingPhotoList,
    #selectedFilePreview,
    #existingFileList {
        display: flex;
        flex-direction: column;
        gap: 8px;
        width: 100%;
    }

    .selected-photo-card {
        display: flex;
        align-items: center;
        width: 100%;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
        padding: 10px;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .selected-photo-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .selected-photo-card__preview {
        width: 70px;
        height: 70px;
        border-radius: 8px;
        overflow: hidden;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border: 1px solid #e2e8f0;
    }

    .selected-photo-card__preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .selected-photo-card__meta {
        flex: 1;
        padding: 0 16px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-width: 0;
    }

    .selected-photo-card__name {
        font-size: 13px;
        font-weight: 600;
        color: #1e293b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .selected-photo-card__size {
        font-size: 11px;
        color: #64748b;
        margin-top: 1px;
    }

    .selected-photo-card__input-wrapper {
        margin-top: 4px;
        width: 100%;
    }

    .selected-photo-card__meta input {
        border-radius: 6px !important;
        border: 1px solid #cbd5e1 !important;
        font-size: 12px !important;
        padding: 6px 10px !important;
        height: auto !important;
        width: 100% !important;
        box-sizing: border-box !important;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .selected-photo-card__meta input:focus {
        border-color: #0A66C2 !important;
        box-shadow: 0 0 0 2px rgba(10, 102, 194, 0.15) !important;
        outline: 0;
    }

    .selected-photo-card__remove-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        padding-left: 8px;
        flex-shrink: 0;
    }

    .selected-photo-card__remove {
        width: 32px;
        height: 32px;
        border: 0;
        border-radius: 50% !important;
        background: #fee2e2 !important;
        color: #ef4444 !important;
        font-size: 18px !important;
        line-height: 32px !important;
        padding: 0 !important;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.15s ease, color 0.15s ease;
    }

    .selected-photo-card__remove:hover {
        background: #fecaca !important;
        color: #dc2626 !important;
    }
    
    .nav-tabs .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        transition: all 0.2s ease;
    }
    
    .nav-tabs .nav-link:hover {
        border-color: transparent;
        color: #0A66C2 !important;
    }
    
    .nav-tabs .nav-link.active {
        color: #0A66C2 !important;
        border-bottom: 3px solid #0A66C2;
        background: transparent;
    }
    
    /* Custom vertical nav pills styles */
    .nav-pills-custom .nav-link {
        color: #475569;
        font-weight: 600;
        padding: 14px 20px;
        border-radius: 0;
        border-bottom: 1px solid #f1f5f9;
        transition: all 0.2s ease;
        text-align: left;
    }
    .nav-pills-custom .nav-link:hover {
        background-color: #f8fafc;
        color: #0A66C2;
    }
    .nav-pills-custom .nav-link.active {
        background-color: #0A66C2 !important;
        color: #ffffff !important;
        border-radius: 4px;
        border-bottom: none;
        box-shadow: 0 4px 6px -1px rgba(10, 102, 194, 0.2);
    }
    .w-20px {
        width: 24px;
        display: inline-block;
    }
    
    /* Card headers in tab content */
    .tab-pane .trip-section-title {
        font-size: 1.25rem;
        margin-bottom: 1.5rem !important;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #e2e8f0;
        color: #1e293b;
    }
    
    .trip-section-title {
        font-size: 1.1rem;
        margin-bottom: 1.5rem !important;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .card.border {
        border-color: #f1f5f9 !important;
        border-radius: 12px;
    }
    
    .form-control {
        border-radius: 8px;
        border: 1px solid #cbd5e1;
    }
    
    .form-control:focus {
        border-color: #0A66C2;
        box-shadow: 0 0 0 3px rgba(10, 102, 194, 0.1);
    }
</style>

<div class="trip-form-wrap">
    <div class="card trip-card <?= ($is_modal ?? false) ? 'border-0 shadow-none' : ''; ?>">
        <?php if (!($is_modal ?? false)): ?>
        <div class="card-header d-flex align-items-center bg-white border-bottom-0 pt-4 pb-0">
            <h3 class="card-title mb-0 trip-headline" style="font-size: 1.5rem; color: #1e293b;"><?= $isEdit ? 'Ubah' : 'Buat'; ?> Laporan Perjalanan Dinas</h3>
            <div class="card-tools ml-auto">
                <a href="<?= site_url('admin/surat/perjalanan-dinas'); ?>" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
        </div>
        <?php endif; ?>
        <div class="card-body <?= ($is_modal ?? false) ? 'p-0' : ''; ?>">
            <?php if (! empty($form_error)): ?>
                <div class="alert alert-danger"><?= esc($form_error); ?></div>
            <?php endif; ?>

            <form id="perjalananDinasForm" action="<?= esc($formAction, 'attr'); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>

                <div class="row">
                    <!-- Left Sidebar Tabs -->
                    <div class="col-md-3 mb-4">
                        <div class="nav flex-column nav-pills nav-pills-custom" id="perjalananDinasTab" role="tablist" aria-orientation="vertical" style="position: sticky; top: 20px; z-index: 10;">
                            <a class="nav-link active" id="umum-tab" data-toggle="pill" href="#umum" role="tab" aria-controls="umum" aria-selected="true">
                                <i class="fas fa-info-circle mr-2 w-20px text-center"></i> Umum
                            </a>
                            <a class="nav-link" id="dokumentasi-tab" data-toggle="pill" href="#dokumentasi" role="tab" aria-controls="dokumentasi" aria-selected="false">
                                <i class="fas fa-camera mr-2 w-20px text-center"></i> Dokumentasi Kegiatan
                            </a>
                            <a class="nav-link" id="dokumen-tab" data-toggle="pill" href="#dokumen" role="tab" aria-controls="dokumen" aria-selected="false">
                                <i class="fas fa-file-alt mr-2 w-20px text-center"></i> Dokumentasi Tiket & Pendukung
                            </a>
                        </div>
                    </div>
                    
                    <!-- Right Content Area -->
                    <div class="col-md-9">
                        <div class="tab-content" id="perjalananDinasTabContent">
                    <!-- TAB UMUM -->
                    <div class="tab-pane fade show active" id="umum" role="tabpanel" aria-labelledby="umum-tab">
                        <div class="card border shadow-none mb-3">
                    <div class="card-body">
                        <div class="trip-section-title mb-2">Penanggung Jawab dan Pelaksana</div>
                        <div class="form-row">
                            <div class="form-group col-lg-12 col-md-12">
                                <label>Nama Pelaksana (Bisa pilih lebih dari satu)</label>
                                <select id="pelaksanaSelect" name="pelaksana_id[]" class="form-control" multiple required>
                                    <?php foreach ($pegawaiOptions as $pegawai): ?>
                                        <?php $pegawaiId = (int) ($pegawai['id'] ?? 0); ?>
                                        <option value="<?= esc((string) $pegawaiId, 'attr'); ?>" <?= in_array($pegawaiId, $selectedPelaksana, true) ? 'selected' : ''; ?>>
                                            <?= esc($formatPegawaiLabel((array) $pegawai)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Gunakan Ctrl/Cmd + klik untuk memilih lebih dari satu nama.</small>
                            </div>
                            <div class="form-group col-lg-12 col-md-12">
                                <label>Diketahui Oleh</label>
                                <select name="diketahui_oleh_id" class="form-control" required>
                                    <option value="">-- Pilih Pegawai --</option>
                                    <?php foreach ($pegawaiOptions as $pegawai): ?>
                                        <?php $pegawaiId = (int) ($pegawai['id'] ?? 0); ?>
                                        <option value="<?= esc((string) $pegawaiId, 'attr'); ?>" <?= $pegawaiId === (int) ($input['diketahui_oleh_id'] ?? $defaultApproverId) ? 'selected' : ''; ?>>
                                            <?= esc((string) ($pegawai['display_label'] ?? 'Pegawai')); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Default NIP 198002142014121002.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border shadow-none mb-3">
                    <div class="card-body">
                        <div class="trip-section-title mb-2">Informasi Dasar Perjalanan</div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Periode Mulai</label>
                                <input type="date" id="periodeMulai" name="periode_mulai" class="form-control" onfocus="this.showPicker()" value="<?= esc((string) ($input['periode_mulai'] ?? '')); ?>" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Periode Selesai</label>
                                <input type="date" id="periodeSelesai" name="periode_selesai" class="form-control" onfocus="this.showPicker()" value="<?= esc((string) ($input['periode_selesai'] ?? '')); ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label>Kota/Kab. Tujuan Perjalanan Dinas</label>
                                <select id="kotaTujuanSelect" name="kota_tujuan" class="form-control" required>
                                    <option value="">Pilih Kota / Kabupaten</option>
                                    <?php foreach ($kabupatenOptions as $kabupaten): ?>
                                        <?php
                                            $kabupaten = trim((string) $kabupaten);
                                            $isSelected = $selectedKabupaten !== '' && strcasecmp($selectedKabupaten, $kabupaten) === 0;
                                            if ($isSelected) {
                                                $selectedKabupatenExists = true;
                                            }
                                        ?>
                                        <option value="<?= esc($kabupaten, 'attr'); ?>" <?= $isSelected ? 'selected' : ''; ?>><?= esc($kabupaten); ?></option>
                                    <?php endforeach; ?>
                                    <?php if ($selectedKabupaten !== '' && ! $selectedKabupatenExists): ?>
                                        <option value="<?= esc($selectedKabupaten, 'attr'); ?>" selected><?= esc($selectedKabupaten); ?></option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border shadow-none mb-3">
                    <div class="card-body">
                        <div class="trip-section-title mb-2">Isi Laporan</div>

                        <div class="form-group">
                            <label>Tujuan Perjalanan Dinas</label>
                            <textarea name="tujuan" class="form-control" rows="4" required><?= esc((string) ($input['tujuan'] ?? '')); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Sasaran Perjalanan Dinas</label>
                            <textarea name="sasaran" class="form-control" rows="4" required><?= esc((string) ($input['sasaran'] ?? '')); ?></textarea>
                        </div>

                        <div class="form-group mb-0">
                            <label>Laporan Hasil Perjalanan Dinas</label>
                            <textarea id="laporanHasil" name="laporan_hasil" class="form-control summernote" rows="6" required><?= esc((string) ($input['laporan_hasil'] ?? '')); ?></textarea>
                        </div>
                    </div>
                </div>

                    </div>
                    <!-- END TAB UMUM -->

                    <!-- TAB DOKUMENTASI KEGIATAN -->
                    <div class="tab-pane fade" id="dokumentasi" role="tabpanel" aria-labelledby="dokumentasi-tab">
                        <div class="card border shadow-none mb-3">
                    <div class="card-body">
                        <div class="trip-section-title mb-2">Foto Dokumentasi</div>
                        <input type="file" id="fotoDokumentasi" name="foto_dokumentasi[]" accept="image/*" multiple class="d-none">
                        <div class="photo-dropzone" id="photoDropzone">
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap:12px;">
                                <div>
                                    <strong>Tambah foto dokumentasi</strong>
                                    <div class="text-muted small">Klik tombol atau seret foto ke area ini.</div>
                                </div>
                                <div class="text-right">
                                    <button type="button" class="btn btn-outline-primary" id="btnPickPhotos">Pilih Foto</button>
                                    <div class="small text-muted mt-1" id="photoCounter">0 foto dipilih</div>
                                </div>
                            </div>
                            <div class="photo-dropzone__target text-center text-muted">
                                Drop foto di sini untuk upload cepat.
                            </div>
                            <div class="mt-3" id="selectedPhotoPreview"></div>

                        <?php if (!empty($existingFotoDokumentasi)): ?>
                            <div class="existing-photo-section">
                                <div class="existing-photo-title">Foto dokumentasi tersimpan (<?= count($existingFotoDokumentasi); ?> foto)</div>
                                <div id="existingPhotoList">
                                    <?php foreach ($existingFotoDokumentasi as $fIdx => $foto): ?>
                                        <?php $dataUri = $resolvePhotoSrc($foto); ?>
                                        <?php if ($dataUri !== ''): ?>
                                            <div class="selected-photo-card existing-photo-item" data-existing-index="<?= (int) $fIdx; ?>">
                                                <div class="selected-photo-card__preview">
                                                    <img src="<?= esc($dataUri); ?>" alt="Dokumentasi <?= (int) $fIdx + 1; ?>">
                                                </div>
                                                <div class="selected-photo-card__meta">
                                                    <div class="selected-photo-card__name">Foto Dokumentasi <?= (int) $fIdx + 1; ?></div>
                                                    <div class="selected-photo-card__input-wrapper">
                                                        <input type="text" name="existing_foto_keterangan[<?= (int) $fIdx; ?>]" class="form-control form-control-sm" placeholder="Keterangan foto..." value="<?= esc($foto['keterangan'] ?? ''); ?>">
                                                    </div>
                                                </div>
                                                <div class="selected-photo-card__remove-wrapper">
                                                    <button type="button" class="btn-remove-existing selected-photo-card__remove" title="Hapus foto ini">&times;</button>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        </div>
                        <small class="text-muted d-block mt-2">Format gambar umum didukung, bisa pilih banyak foto sekaligus.</small>
                    </div>
                </div>

                    </div>
                    <!-- END TAB DOKUMENTASI KEGIATAN -->

                    <!-- TAB DOKUMEN PENDUKUNG -->
                    <div class="tab-pane fade" id="dokumen" role="tabpanel" aria-labelledby="dokumen-tab">
                        <div class="card border shadow-none mb-3">
                    <div class="card-body">
                        <div class="trip-section-title mb-2">Dokumen Pendukung</div>
                        <input type="file" id="dokumenPendukung" name="dokumen_pendukung[]" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar" multiple class="d-none">
                        <div class="photo-dropzone" id="fileDropzone">
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap:12px;">
                                <div>
                                    <strong>Tambah dokumen pendukung</strong>
                                    <div class="text-muted small">Klik tombol atau seret file (tiket pesawat, nota, struk bensin, dll) ke area ini.</div>
                                </div>
                                <div class="text-right">
                                    <button type="button" class="btn btn-outline-primary" id="btnPickFiles">Pilih File</button>
                                    <div class="small text-muted mt-1" id="fileCounter">0 file dipilih</div>
                                </div>
                            </div>
                            <div class="photo-dropzone__target text-center text-muted">
                                Drop file di sini untuk upload cepat.
                            </div>
                            <div class="mt-3" id="selectedFilePreview"></div>

                        <?php $existingDocs = $existing_dokumen_pendukung ?? []; ?>
                        <?php if (!empty($existingDocs)): ?>
                            <div class="existing-photo-section">
                                <div class="existing-photo-title">Dokumen pendukung tersimpan (<?= count($existingDocs); ?> file)</div>
                                <div id="existingFileList">
                                    <?php foreach ($existingDocs as $fIdx => $doc): ?>
                                        <?php 
                                            $filePath = trim((string) ($doc['file_path'] ?? ''));
                                            $fileName = trim((string) ($doc['name'] ?? 'File'));
                                            $docUrl = $filePath !== '' ? media_url($filePath) : '';
                                        ?>
                                        <?php if ($docUrl !== ''): ?>
                                            <div class="selected-photo-card existing-photo-item" data-existing-index="<?= (int) $fIdx; ?>">
                                                <div class="selected-photo-card__preview">
                                                    <?php 
                                                        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                                                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)): 
                                                    ?>
                                                        <img src="<?= esc($docUrl); ?>" alt="<?= esc($fileName); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                                    <?php else: ?>
                                                        <?php 
                                                            $icon = 'fa-file';
                                                            $color = 'text-secondary';
                                                            if ($ext === 'pdf') { $icon = 'fa-file-pdf'; $color = 'text-danger'; }
                                                            elseif (in_array($ext, ['doc', 'docx'], true)) { $icon = 'fa-file-word'; $color = 'text-primary'; }
                                                            elseif (in_array($ext, ['xls', 'xlsx'], true)) { $icon = 'fa-file-excel'; $color = 'text-success'; }
                                                        ?>
                                                        <i class="fas <?= $icon; ?> <?= $color; ?>" style="font-size: 2.5rem;"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="selected-photo-card__meta">
                                                    <div class="selected-photo-card__name" title="<?= esc($fileName); ?>"><?= esc($fileName); ?></div>
                                                    <div class="selected-photo-card__size"><?= esc(strtoupper($ext)); ?> File</div>
                                                    <div class="selected-photo-card__input-wrapper">
                                                        <select name="existing_dokumen_transportasi[<?= (int) $fIdx; ?>]" class="form-control form-control-sm mb-1">
                                                            <option value="">-- Pilih Transportasi (Opsional) --</option>
                                                            <?php foreach (['Pesawat', 'Kereta Api', 'Kapal Laut', 'Bus / Travel', 'Lainnya'] as $tOption): ?>
                                                                <option value="<?= $tOption; ?>" <?= ($doc['transportasi'] ?? '') === $tOption ? 'selected' : ''; ?>><?= $tOption; ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0; padding-left: 8px;">
                                                    <a href="<?= esc($docUrl); ?>" target="_blank" class="btn btn-xs btn-outline-primary px-2 font-weight-bold">Unduh</a>
                                                    <button type="button" class="btn-remove-existing selected-photo-card__remove" title="Hapus file ini">&times;</button>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        </div>
                        <small class="text-muted d-block mt-2">Format dokumen didukung: PDF, gambar (JPG, PNG), Word (DOC, DOCX), Excel (XLS, XLSX), TXT, ZIP, RAR.</small>
                        </div>
                    </div>
                    <!-- END TAB DOKUMEN PENDUKUNG -->
                </div>
                </div> <!-- End row for tabs -->

                <div class="form-group">
                    <label>Dibuat Oleh</label>
                    <input type="text" class="form-control" value="<?= esc($creatorName); ?>" readonly>
                    <?php if (! empty($creator_pegawai)): ?>
                        <small class="text-muted">Tersambung ke pegawai: <?= esc((string) ($creator_pegawai['nama'] ?? '-')); ?> / NIP <?= esc((string) ($creator_pegawai['nip'] ?? '-')); ?> / <?= esc((string) ($creator_pegawai['jabatan'] ?? '-')); ?></small>
                    <?php endif; ?>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Simpan data laporan pelaksanaan perjalanan dinas.
                    </div>
                    <div>
                        <button type="submit" name="save_mode" value="final" class="btn btn-primary"><?= esc($submitLabelPrimary); ?></button>
                    </div>
                </div>
                <input type="hidden" name="removed_foto_indices" id="removedFotoIndices" value="">
                <input type="hidden" name="removed_file_indices" id="removedFileIndices" value="">
            </form>
        </div>
    </div>
</div>
    <!-- CDN includes for jQuery and Summernote (loaded here for compatibility on shared hosting) -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" rel="stylesheet">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>

        <script>
(function () {
    var periodeMulai = document.getElementById('periodeMulai');
    var periodeSelesai = document.getElementById('periodeSelesai');
    var fileInput = document.getElementById('fotoDokumentasi');
    var pickButton = document.getElementById('btnPickPhotos');
    var dropzone = document.getElementById('photoDropzone');
    var previewContainer = document.getElementById('selectedPhotoPreview');
    var counter = document.getElementById('photoCounter');
    var selectedItems = [];
    var removedExistingIndices = [];
    var existingList = document.getElementById('existingPhotoList');

    // Hapus foto existing saat diklik
    if (existingList) {
        existingList.addEventListener('click', function (e) {
            var btn = e.target.closest('.btn-remove-existing');
            if (!btn) return;
            var item = btn.closest('.existing-photo-item');
            if (!item) return;
            var idx = parseInt(item.getAttribute('data-existing-index'), 10);
            if (!removedExistingIndices.includes(idx)) {
                removedExistingIndices.push(idx);
            }
            item.remove();
            updateRemovedIndices();
        });
    }

    function updateRemovedIndices() {
        var field = document.getElementById('removedFotoIndices');
        if (field) field.value = JSON.stringify(removedExistingIndices);
    }

    function syncPeriodeRules() {
        if (!periodeMulai || !periodeSelesai) {
            return;
        }

        var startValue = periodeMulai.value || '';
        if (startValue !== '') {
            periodeSelesai.min = startValue;
        } else {
            periodeSelesai.removeAttribute('min');
        }

        if (startValue !== '' && (periodeSelesai.value === '' || periodeSelesai.value < startValue)) {
            periodeSelesai.value = startValue;
        }
    }

    function showNotice(message, type) {
        var notice = document.getElementById('photoUploadNotice');
        if (!notice) {
            notice = document.createElement('div');
            notice.id = 'photoUploadNotice';
            notice.className = 'alert alert-info mt-3 mb-0';
            dropzone.appendChild(notice);
        }

        notice.className = type === 'danger' ? 'alert alert-danger mt-3 mb-0' : 'alert alert-info mt-3 mb-0';
        notice.textContent = message;
    }

    function updateCounter() {
        if (counter) {
            counter.textContent = selectedItems.length + ' foto dipilih';
        }
    }

    function syncFileInput() {
        if (typeof DataTransfer === 'undefined' || !fileInput) {
            return;
        }

        var dataTransfer = new DataTransfer();
        selectedItems.forEach(function (item) {
            dataTransfer.items.add(item.file);
        });
        fileInput.files = dataTransfer.files;
    }

    // Expose for use before form submit so files are attached to input
    window.syncPhotoFileInput = syncFileInput;

    function renderPreview() {
        if (!previewContainer) {
            return;
        }

        previewContainer.innerHTML = '';

        selectedItems.forEach(function (item, index) {
            var card = document.createElement('div');
            card.className = 'selected-photo-card';
            card.innerHTML = '' +
                '<div class="selected-photo-card__preview">' +
                    '<img src="' + item.previewUrl + '" alt="' + item.name + '">' +
                '</div>' +
                '<div class="selected-photo-card__meta">' +
                    '<div class="selected-photo-card__name">' + item.name + '</div>' +
                    '<div class="selected-photo-card__size">' + item.sizeLabel + '</div>' +
                    '<div class="selected-photo-card__input-wrapper">' +
                        '<input type="text" name="foto_keterangan[]" class="form-control form-control-sm selected-photo-desc" placeholder="Keterangan foto..." value="' + (item.keterangan || '') + '">' +
                    '</div>' +
                '</div>' +
                '<div class="selected-photo-card__remove-wrapper">' +
                    '<button type="button" class="selected-photo-card__remove" aria-label="Hapus foto">&times;</button>' +
                '</div>';

            var removeButton = card.querySelector('.selected-photo-card__remove');
            if (removeButton) {
                removeButton.addEventListener('click', function () {
                    URL.revokeObjectURL(item.previewUrl);
                    selectedItems.splice(index, 1);
                    syncFileInput();
                    renderPreview();
                    updateCounter();
                });
            }

            var descInput = card.querySelector('.selected-photo-desc');
            if (descInput) {
                descInput.addEventListener('input', function () {
                    item.keterangan = this.value;
                });
            }

            previewContainer.appendChild(card);
        });
    }

    function addFiles(fileList) {
        var incomingFiles = Array.prototype.slice.call(fileList || []).filter(function (file) {
            return file && file.type && file.type.indexOf('image/') === 0;
        });

        if (incomingFiles.length === 0) {
            showNotice('Silakan pilih file gambar yang valid.', 'danger');
            return;
        }

        incomingFiles.forEach(function (file) {
            selectedItems.push({
                file: file,
                previewUrl: URL.createObjectURL(file),
                name: file.name,
                sizeLabel: Math.max(1, Math.round(file.size / 1024)) + ' KB',
            });
        });

        syncFileInput();
        renderPreview();
        updateCounter();
        showNotice('Foto siap diunggah.', 'info');
    }

    if (periodeMulai && periodeSelesai) {
        periodeMulai.addEventListener('change', syncPeriodeRules);
        periodeSelesai.addEventListener('change', syncPeriodeRules);
        syncPeriodeRules();
    }

    if (pickButton && fileInput) {
        pickButton.addEventListener('click', function () {
            fileInput.click();
        });
    }

    if (fileInput) {
        fileInput.addEventListener('change', function () {
            addFiles(fileInput.files);
            fileInput.value = '';
        });
    }

    if (dropzone) {
        ['dragenter', 'dragover'].forEach(function (eventName) {
            dropzone.addEventListener(eventName, function (event) {
                event.preventDefault();
                dropzone.classList.add('is-dragover');
            });
        });

        ['dragleave', 'drop'].forEach(function (eventName) {
            dropzone.addEventListener(eventName, function (event) {
                event.preventDefault();
                dropzone.classList.remove('is-dragover');
            });
        });

        dropzone.addEventListener('drop', function (event) {
            addFiles(event.dataTransfer ? event.dataTransfer.files : []);
        });
    }

    updateCounter();
})();

// Javascript for Dokumen Pendukung dropzone, selection, previews, and deletion
(function () {
    var fileInput = document.getElementById('dokumenPendukung');
    var pickButton = document.getElementById('btnPickFiles');
    var dropzone = document.getElementById('fileDropzone');
    var previewContainer = document.getElementById('selectedFilePreview');
    var counter = document.getElementById('fileCounter');
    var selectedItems = [];
    var removedExistingIndices = [];
    var existingList = document.getElementById('existingFileList');

    if (existingList) {
        existingList.addEventListener('click', function (e) {
            var btn = e.target.closest('.btn-remove-existing');
            if (!btn) return;
            var item = btn.closest('.existing-photo-item');
            if (!item) return;
            var idx = parseInt(item.getAttribute('data-existing-index'), 10);
            if (!removedExistingIndices.includes(idx)) {
                removedExistingIndices.push(idx);
            }
            item.remove();
            updateRemovedIndices();
        });
    }

    function updateRemovedIndices() {
        var field = document.getElementById('removedFileIndices');
        if (field) field.value = JSON.stringify(removedExistingIndices);
    }

    function showNotice(message, type) {
        var notice = document.getElementById('fileUploadNotice');
        if (!notice) {
            notice = document.createElement('div');
            notice.id = 'fileUploadNotice';
            notice.className = 'alert alert-info mt-3 mb-0';
            dropzone.appendChild(notice);
        }
        notice.className = type === 'danger' ? 'alert alert-danger mt-3 mb-0' : 'alert alert-info mt-3 mb-0';
        notice.textContent = message;
    }

    function updateCounter() {
        if (counter) {
            counter.textContent = selectedItems.length + ' file dipilih';
        }
    }

    function syncFileInput() {
        if (typeof DataTransfer === 'undefined' || !fileInput) {
            return;
        }
        var dataTransfer = new DataTransfer();
        selectedItems.forEach(function (item) {
            dataTransfer.items.add(item.file);
        });
        fileInput.files = dataTransfer.files;
    }

    window.syncDocFileInput = syncFileInput;

    function renderPreview() {
        if (!previewContainer) {
            return;
        }
        previewContainer.innerHTML = '';

        selectedItems.forEach(function (item, index) {
            var card = document.createElement('div');
            card.className = 'selected-photo-card';

            var isImage = item.file && item.file.type && item.file.type.indexOf('image/') === 0;
            var previewContent = '';
            if (isImage) {
                previewContent = '<img src="' + item.previewUrl + '" alt="' + item.name + '" style="width: 100%; height: 100%; object-fit: cover;">';
            } else {
                var icon = 'fa-file';
                var color = 'text-secondary';
                if (item.name.endsWith('.pdf')) { icon = 'fa-file-pdf'; color = 'text-danger'; }
                else if (item.name.endsWith('.doc') || item.name.endsWith('.docx')) { icon = 'fa-file-word'; color = 'text-primary'; }
                else if (item.name.endsWith('.xls') || item.name.endsWith('.xlsx')) { icon = 'fa-file-excel'; color = 'text-success'; }
                previewContent = '<i class="fas ' + icon + ' ' + color + '" style="font-size: 2.5rem;"></i>';
            }

            card.innerHTML = '' +
                '<div class="selected-photo-card__preview">' +
                    previewContent +
                '</div>' +
                '<div class="selected-photo-card__meta">' +
                    '<div class="selected-photo-card__name" title="' + item.name + '">' + item.name + '</div>' +
                    '<div class="selected-photo-card__size">' + item.sizeLabel + '</div>' +
                    '<div class="selected-photo-card__input-wrapper">' +
                        '<select name="dokumen_transportasi[]" class="form-control form-control-sm mb-1 selected-doc-transport">' +
                            '<option value="">-- Pilih Transportasi (Opsional) --</option>' +
                            '<option value="Pesawat" ' + (item.transportasi === "Pesawat" ? "selected" : "") + '>Pesawat</option>' +
                            '<option value="Kereta Api" ' + (item.transportasi === "Kereta Api" ? "selected" : "") + '>Kereta Api</option>' +
                            '<option value="Kapal Laut" ' + (item.transportasi === "Kapal Laut" ? "selected" : "") + '>Kapal Laut</option>' +
                            '<option value="Bus / Travel" ' + (item.transportasi === "Bus / Travel" ? "selected" : "") + '>Bus / Travel</option>' +
                            '<option value="Lainnya" ' + (item.transportasi === "Lainnya" ? "selected" : "") + '>Lainnya</option>' +
                        '</select>' +
                    '</div>' +
                '</div>' +
                '<div class="selected-photo-card__remove-wrapper">' +
                    '<button type="button" class="selected-photo-card__remove" aria-label="Hapus file">&times;</button>' +
                '</div>';

            var removeButton = card.querySelector('.selected-photo-card__remove');
            if (removeButton) {
                removeButton.addEventListener('click', function () {
                    if (item.previewUrl) URL.revokeObjectURL(item.previewUrl);
                    selectedItems.splice(index, 1);
                    syncFileInput();
                    renderPreview();
                    updateCounter();
                });
            }



            var transInput = card.querySelector('.selected-doc-transport');
            if (transInput) {
                transInput.addEventListener('change', function () {
                    item.transportasi = this.value;
                });
            }

            previewContainer.appendChild(card);
        });
     }

     function addFiles(fileList) {
         var incomingFiles = Array.prototype.slice.call(fileList || []).filter(function (file) {
             return file;
         });

         if (incomingFiles.length === 0) {
             showNotice('Silakan pilih file yang valid.', 'danger');
             return;
         }

         incomingFiles.forEach(function (file) {
             var pUrl = '';
             if (file.type && file.type.indexOf('image/') === 0) {
                 pUrl = URL.createObjectURL(file);
             }
             selectedItems.push({
                 file: file,
                 previewUrl: pUrl,
                 name: file.name,
                 sizeLabel: Math.max(1, Math.round(file.size / 1024)) + ' KB',
                 keterangan: '',
             });
         });

        syncFileInput();
        renderPreview();
        updateCounter();
        showNotice('Dokumen siap diunggah.', 'info');
    }

    if (pickButton && fileInput) {
        pickButton.addEventListener('click', function () {
            fileInput.click();
        });
    }

    if (fileInput) {
        fileInput.addEventListener('change', function () {
            addFiles(fileInput.files);
            fileInput.value = '';
        });
    }

    if (dropzone) {
        ['dragenter', 'dragover'].forEach(function (eventName) {
            dropzone.addEventListener(eventName, function (event) {
                event.preventDefault();
                dropzone.classList.add('is-dragover');
            });
        });

        ['dragleave', 'drop'].forEach(function (eventName) {
            dropzone.addEventListener(eventName, function (event) {
                event.preventDefault();
                dropzone.classList.remove('is-dragover');
            });
        });

        dropzone.addEventListener('drop', function (event) {
            addFiles(event.dataTransfer ? event.dataTransfer.files : []);
        });
    }

    updateCounter();
})();
</script>
<script>
// Initialize Summernote and ensure summernote content is submitted
(function () {
    var form = document.getElementById('perjalananDinasForm');

    if (window.jQuery && typeof $.fn.summernote === 'function') {
        try {
            $('#laporanHasil').summernote({ height: 260 });
        } catch (e) {
            console.warn('Summernote init failed', e);
        }
    }

    if (form) {
        form.addEventListener('submit', function (ev) {
            // Ensure file input contains the selected items before submit
            try {
                if (typeof window.syncPhotoFileInput === 'function') {
                    window.syncPhotoFileInput();
                }
            } catch (e) {
                console.error('syncPhotoFileInput error:', e);
            }

            // Ensure document input contains selected documents before submit
            try {
                if (typeof window.syncDocFileInput === 'function') {
                    window.syncDocFileInput();
                }
            } catch (e) {
                console.error('syncDocFileInput error:', e);
            }

            // Sync summernote content to hidden textarea before submit
            try {
                if (window.jQuery && typeof $.fn.summernote === 'function') {
                    var code = $('#laporanHasil').summernote('code');
                    var ta = form.querySelector('textarea[name="laporan_hasil"]');
                    if (ta) ta.value = code;
                }
            } catch (e) {
                // ignore
            }
        });
    }
})();
</script>
<?= $this->endSection(); ?>