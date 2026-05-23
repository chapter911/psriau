<?= $this->extend('layouts/admin'); ?>

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
        border: 1px dashed #cfd7e3;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        border-radius: 16px;
        padding: 16px;
        transition: border-color .15s ease, background-color .15s ease, transform .15s ease;
    }

    .photo-dropzone.is-dragover {
        border-color: #2563eb;
        background: #eef6ff;
        transform: translateY(-1px);
    }

    .photo-dropzone__target {
        border-radius: 12px;
        border: 1px solid #e6ecf3;
        padding: 24px 16px;
        background: #fff;
    }

    #selectedPhotoPreview {
        gap: 10px;
    }

    .selected-photo-card {
        width: 126px;
        border: 1px solid #dbe3ee;
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
    }

    .selected-photo-card__preview {
        position: relative;
        width: 100%;
        height: 126px;
        background: #f8fafc;
    }

    .selected-photo-card__preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .selected-photo-card__remove {
        position: absolute;
        top: 6px;
        right: 6px;
        width: 26px;
        height: 26px;
        border: 0;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.8);
        color: #fff;
        font-size: 16px;
        line-height: 26px;
        padding: 0;
        cursor: pointer;
    }

    .selected-photo-card__meta {
        padding: 8px 10px 10px;
        font-size: 11px;
        line-height: 1.35;
        color: #475569;
        word-break: break-word;
    }

    .selected-photo-card__name {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 4px;
    }
</style>

<div class="trip-form-wrap">
    <div class="card trip-card">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title mb-0 trip-headline">Buat Laporan Perjalanan Dinas</h3>
            <div class="card-tools ml-auto">
                <a href="<?= site_url('admin/laporan/perjalanan-dinas'); ?>" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
        </div>
        <div class="card-body">
            <?php if (! empty($form_error)): ?>
                <div class="alert alert-danger"><?= esc($form_error); ?></div>
            <?php endif; ?>

            <form id="perjalananDinasForm" action="<?= site_url('admin/laporan/perjalanan-dinas/buat'); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>

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
                            <div class="form-group col-12">
                                <label>Nomor Surat Tugas</label>
                                <input type="text" name="nomor_surat_tugas" class="form-control" value="<?= esc((string) ($input['nomor_surat_tugas'] ?? '')); ?>" required>
                            </div>
                        </div>

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
                            <div class="mt-3 d-flex flex-wrap" id="selectedPhotoPreview"></div>
                        </div>
                        <small class="text-muted d-block mt-2">Format gambar umum didukung, bisa pilih banyak foto sekaligus.</small>
                    </div>
                </div>

                <div class="form-group">
                    <label>Dibuat Oleh</label>
                    <input type="text" class="form-control" value="<?= esc($creatorName); ?>" readonly>
                    <?php if (! empty($creator_pegawai)): ?>
                        <small class="text-muted">Tersambung ke pegawai: <?= esc((string) ($creator_pegawai['nama'] ?? '-')); ?> / NIP <?= esc((string) ($creator_pegawai['nip'] ?? '-')); ?> / <?= esc((string) ($creator_pegawai['jabatan'] ?? '-')); ?></small>
                    <?php endif; ?>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">Pilih simpan: <strong>Draft</strong> menyimpan sementara, <strong>Final</strong> akan menghasilkan PDF.</div>
                    <div>
                        <button type="submit" name="save_mode" value="draft" class="btn btn-secondary">Simpan Draft</button>
                        <button type="submit" name="save_mode" value="final" class="btn btn-primary">Simpan Final</button>
                    </div>
                </div>
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
                    '<button type="button" class="selected-photo-card__remove" aria-label="Hapus foto">&times;</button>' +
                '</div>' +
                '<div class="selected-photo-card__meta">' +
                    '<div class="selected-photo-card__name">' + item.name + '</div>' +
                    '<div>' + item.sizeLabel + '</div>' +
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