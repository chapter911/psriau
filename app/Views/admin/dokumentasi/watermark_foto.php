<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<link rel="stylesheet" href="<?= esc(media_url('assets/leaflet/leaflet.css')); ?>">

<!-- HEADER SECTION -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card shadow-sm border-0 header-banner-card">
            <div class="card-body p-3 p-md-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                <div class="mb-2 mb-md-0">
                    <div class="d-flex align-items-center">
                        <div class="header-icon-box mr-3">
                            <i class="fas fa-camera-retro"></i>
                        </div>
                        <div>
                            <h3 class="font-weight-bold text-dark mb-1 h5 h4-md">
                                Watermark Foto Lapangan <span class="badge badge-primary font-weight-normal ml-2">TimeMark Studio</span>
                            </h3>
                            <p class="text-muted small mb-0">
                                Sematkan stempel waktu, tanggal, lokasi GPS, dan identitas instansi secara profesional dengan dukungan batch upload &amp; ZIP.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <span class="badge badge-light border text-dark px-3 py-2 shadow-xs mr-2">
                        <i class="fas fa-shield-alt text-primary mr-1"></i> Hak Akses: <?= !empty($permissions['add']) || !empty($permissions['edit']) ? 'Pemroses Aktif' : 'Hanya Lihat' ?>
                    </span>
                    <a href="<?= site_url('/admin/tutorial'); ?>" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-question-circle mr-1"></i> Tutorial
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="watermarkForm" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row">
        <!-- =================================================================== -->
        <!-- KOLOM KIRI: WIZARD STEP CONTROL PANEL (OPSI 1)                     -->
        <!-- =================================================================== -->
        <div class="col-lg-6 col-xl-6 mb-4">
            <div class="card shadow-sm border-0 studio-control-card">
                <!-- WIZARD STEP NAVIGATION TABS -->
                <div class="card-header bg-white border-bottom p-2 p-md-3">
                    <div class="wizard-steps-nav d-flex" role="tablist">
                        <button type="button" class="wizard-step-btn flex-fill btn btn-sm active" data-step="1" id="tabBtnStep1">
                            <span class="step-num mr-1">1</span>
                            <span class="step-label">Foto &amp; Format</span>
                        </button>
                        <button type="button" class="wizard-step-btn flex-fill btn btn-sm text-muted" data-step="2" id="tabBtnStep2">
                            <span class="step-num mr-1">2</span>
                            <span class="step-label">Waktu &amp; Lokasi</span>
                        </button>
                        <button type="button" class="wizard-step-btn flex-fill btn btn-sm text-muted" data-step="3" id="tabBtnStep3">
                            <span class="step-num mr-1">3</span>
                            <span class="step-label">Simpan &amp; Proses</span>
                        </button>
                    </div>
                </div>

                <div class="card-body p-3 p-md-4">
                    <!-- ======================================================= -->
                    <!-- LANGKAH 1: PILIH FORMAT & UNGGAH FOTO                   -->
                    <!-- ======================================================= -->
                    <div class="wizard-step-pane active" id="wizardStepPane1">
                        <!-- 1.1 Format Template TimeMark -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="font-weight-bold text-dark small mb-0">
                                <i class="fas fa-palette text-primary mr-1"></i> Pilih Format Watermark:
                            </label>
                            <span class="badge badge-light border text-muted" id="activeTemplateBadge">Klasik TimeMark</span>
                        </div>

                        <div class="row mb-3">
                            <!-- Template 1: Classic -->
                            <div class="col-6 mb-2">
                                <label class="template-card active w-100 p-2 border rounded text-left cursor-pointer position-relative mb-0 shadow-xs" data-val="classic">
                                    <input type="radio" name="template" value="classic" checked class="d-none">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="font-weight-bold text-primary small"><i class="fas fa-signature mr-1"></i> Klasik PU</span>
                                        <span class="badge badge-primary check-badge"><i class="fas fa-check"></i></span>
                                    </div>
                                    <div class="template-mini-preview bg-dark text-white p-2 rounded small">
                                        <div class="text-warning font-weight-bold" style="font-size: 13px;">15:06 <span class="text-white small" style="font-size: 9px;">| 27/10/2026</span></div>
                                        <div class="text-truncate text-light mt-1" style="font-size: 10px;"><i class="fas fa-map-marker-alt text-danger"></i> Kantor Balai PPS</div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <span class="badge badge-warning text-dark font-weight-bold" style="font-size: 8px;">POPULER</span>
                                        <small class="text-muted" style="font-size: 10px;">Jam besar khas PU</small>
                                    </div>
                                </label>
                            </div>

                            <!-- Template 2: Table / Grid -->
                            <div class="col-6 mb-2">
                                <label class="template-card w-100 p-2 border rounded text-left cursor-pointer position-relative mb-0 shadow-xs" data-val="table">
                                    <input type="radio" name="template" value="table" class="d-none">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="font-weight-bold text-dark small"><i class="fas fa-table mr-1"></i> Grid Inspeksi</span>
                                        <span class="badge badge-secondary check-badge d-none"><i class="fas fa-check"></i></span>
                                    </div>
                                    <div class="template-mini-preview bg-dark text-white p-2 rounded small">
                                        <div class="border-bottom border-secondary pb-1 font-weight-bold text-info" style="font-size: 10px;">PU SATKER RIAU</div>
                                        <div class="d-flex justify-content-between text-warning pt-1" style="font-size: 9px;"><span>WAKTU</span><span>15:06 WIB</span></div>
                                        <div class="d-flex justify-content-between text-light" style="font-size: 9px;"><span>LOKASI</span><span class="text-truncate">Pekanbaru</span></div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <span class="badge badge-info text-white" style="font-size: 8px;">RESMI</span>
                                        <small class="text-muted" style="font-size: 10px;">Kotak proyek teknis</small>
                                    </div>
                                </label>
                            </div>

                            <!-- Template 3: Badge / Modern Card -->
                            <div class="col-6 mb-2">
                                <label class="template-card w-100 p-2 border rounded text-left cursor-pointer position-relative mb-0 shadow-xs" data-val="badge">
                                    <input type="radio" name="template" value="badge" class="d-none">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="font-weight-bold text-dark small"><i class="fas fa-id-badge mr-1"></i> Modern Badge</span>
                                        <span class="badge badge-secondary check-badge d-none"><i class="fas fa-check"></i></span>
                                    </div>
                                    <div class="template-mini-preview bg-dark text-white p-2 rounded small">
                                        <div class="badge badge-warning text-dark mb-1" style="font-size: 7px;">KEMENTERIAN PU</div>
                                        <div class="font-weight-bold text-white" style="font-size: 13px;">15:06</div>
                                        <div class="text-muted text-truncate" style="font-size: 9px;"><i class="fas fa-map-pin text-danger"></i> Pekanbaru, Riau</div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <span class="badge badge-success text-white" style="font-size: 8px;">MODERN</span>
                                        <small class="text-muted" style="font-size: 10px;">Kartu melayang</small>
                                    </div>
                                </label>
                            </div>

                            <!-- Template 4: Clean Bottom Strip -->
                            <div class="col-6 mb-2">
                                <label class="template-card w-100 p-2 border rounded text-left cursor-pointer position-relative mb-0 shadow-xs" data-val="strip">
                                    <input type="radio" name="template" value="strip" class="d-none">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="font-weight-bold text-dark small"><i class="fas fa-bars mr-1"></i> Bottom Strip</span>
                                        <span class="badge badge-secondary check-badge d-none"><i class="fas fa-check"></i></span>
                                    </div>
                                    <div class="template-mini-preview bg-dark text-white p-2 rounded small">
                                        <div class="border-top border-warning pt-1 d-flex justify-content-between align-items-center">
                                            <div class="font-weight-bold text-warning" style="font-size: 12px;">15:06</div>
                                            <div class="text-truncate text-light ml-1" style="font-size: 9px;">Satker PPS Riau</div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <span class="badge badge-secondary text-white" style="font-size: 8px;">MINIMALIS</span>
                                        <small class="text-muted" style="font-size: 10px;">Banner tepi bawah</small>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- 1.2 Area Unggah Foto / File ZIP -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="font-weight-bold text-dark small mb-0">
                                <i class="fas fa-cloud-upload-alt text-primary mr-1"></i> Unggah Foto Dokumentasi:
                            </label>
                            <span class="badge badge-secondary" id="fileCountBadge">Belum ada file</span>
                        </div>

                        <div class="upload-dropzone border border-dashed rounded p-3 text-center cursor-pointer mb-2" id="dropzoneArea">
                            <div class="dropzone-icon mb-2">
                                <i class="fas fa-images fa-2x text-primary"></i>
                            </div>
                            <h6 class="font-weight-bold mb-1 text-dark" style="font-size: 0.95rem;">Tarik &amp; Lepas Foto ke Sini</h6>
                            <p class="text-muted small mb-2" style="font-size: 11px;">
                                Format didukung: <strong>JPG, PNG, WebP, iPhone (HEIC/HEIF)</strong>, multi-foto, atau arsip <strong>.ZIP</strong>
                            </p>
                            <button type="button" class="btn btn-outline-primary btn-sm px-3 shadow-xs" onclick="$('#fileInput').click()">
                                <i class="fas fa-folder-open mr-1"></i> Pilih Berkas Foto
                            </button>
                            <input type="file" id="fileInput" name="foto_upload[]" multiple accept="image/jpeg,image/png,image/webp,image/heic,image/heif,.heic,.HEIC,.heif,.HEIF,.zip" class="d-none">
                        </div>

                        <div id="fileSelectedList" class="alert alert-light border py-2 px-3 small text-muted mb-3" style="display: none;"></div>

                        <!-- Navigasi Langkah 1 -->
                        <div class="d-flex justify-content-end mt-3 pt-2 border-top">
                            <button type="button" class="btn btn-primary px-4 font-weight-bold btn-next-step" data-target="2">
                                Lanjut: Waktu &amp; Lokasi <i class="fas fa-arrow-right ml-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- ======================================================= -->
                    <!-- LANGKAH 2: PENGATURAN WAKTU, LOKASI & METADATA EXIF    -->
                    <!-- ======================================================= -->
                    <div class="wizard-step-pane d-none" id="wizardStepPane2">
                        <!-- Alert Status EXIF Auto-Detect -->
                        <div class="alert alert-info py-2 px-3 small mb-3 border-0 shadow-xs" id="exifStatusAlert">
                            <i class="fas fa-magic mr-1"></i> <strong>Deteksi Otomatis:</strong> Sistem membaca tanggal, jam, dan lokasi GPS dari EXIF file foto. Anda dapat menyesuaikan atau mengubah tanggal, jam, alamat, atau koordinat di bawah ini.
                        </div>

                        <!-- 2.1 Pengaturan Tanggal & Waktu Foto -->
                        <div class="card bg-light border-0 rounded-lg p-3 mb-3 shadow-xs">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="font-weight-bold text-dark small">
                                    <i class="fas fa-calendar-alt text-warning mr-1"></i> Tanggal &amp; Waktu Foto
                                </span>
                                <div class="d-flex align-items-center">
                                    <button type="button" class="btn btn-outline-info btn-xs mr-2 font-weight-bold d-none" id="btnTimeFromExif" title="Kembalikan tanggal & jam sesuai EXIF foto">
                                        <i class="fas fa-undo mr-1"></i> Pulihkan Waktu EXIF
                                    </button>
                                    <button type="button" class="btn btn-link btn-xs p-0 text-primary small font-weight-bold" id="btnNow">
                                        <i class="fas fa-clock mr-1"></i> Set Sekarang
                                    </button>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-6">
                                    <label for="manualDate" class="small text-muted mb-1" style="font-size: 11px;">Tanggal Foto:</label>
                                    <input type="date" class="form-control form-control-sm" id="manualDate" name="manual_date" value="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="col-6">
                                    <label for="manualTime" class="small text-muted mb-1" style="font-size: 11px;">Jam Foto:</label>
                                    <input type="time" class="form-control form-control-sm" id="manualTime" name="manual_time" value="<?= date('H:i') ?>">
                                </div>
                            </div>

                            <!-- Opsi Khusus Multi-Foto: Gunakan Waktu EXIF Masing-Masing File -->
                            <div class="custom-control custom-switch mb-2 bg-white p-2 rounded border">
                                <input type="checkbox" class="custom-control-input" id="useExifTime" name="use_exif_time" value="1" checked>
                                <label class="custom-control-label font-weight-bold text-dark small" for="useExifTime">
                                    Pertahankan Waktu EXIF Asli per Foto (Batch Multi-Foto)
                                </label>
                                <small class="d-block text-muted" style="font-size: 10px;">
                                    Jika aktif pada banyak foto sekaligus, setiap foto akan memakai waktu pemotretannya masing-masing ditambah offset jam.
                                </small>
                            </div>

                            <!-- Tombol Cepat Offset Jam -->
                            <label class="font-weight-bold small text-dark mb-1">
                                Penyesuaian Offset Jam:
                                <span class="badge badge-info ml-1" id="currentOffsetBadge">+0 Jam</span>
                            </label>
                            <div class="btn-group btn-group-toggle w-100 mb-2 flex-wrap shadow-xs" data-toggle="buttons">
                                <label class="btn btn-outline-secondary btn-sm active flex-fill">
                                    <input type="radio" name="quick_offset" value="0" checked> Asli (+0j)
                                </label>
                                <label class="btn btn-outline-secondary btn-sm flex-fill">
                                    <input type="radio" name="quick_offset" value="1"> +1 Jam
                                </label>
                                <label class="btn btn-outline-secondary btn-sm flex-fill">
                                    <input type="radio" name="quick_offset" value="2"> +2 Jam
                                </label>
                                <label class="btn btn-outline-primary btn-sm flex-fill font-weight-bold">
                                    <input type="radio" name="quick_offset" value="3"> +3 Jam
                                </label>
                                <label class="btn btn-outline-primary btn-sm flex-fill font-weight-bold">
                                    <input type="radio" name="quick_offset" value="5"> +5 Jam
                                </label>
                                <label class="btn btn-outline-secondary btn-sm flex-fill">
                                    <input type="radio" name="quick_offset" value="-1"> -1 Jam
                                </label>
                            </div>

                            <!-- Custom Offset Inputs -->
                            <div class="row">
                                <div class="col-6 mb-1">
                                    <label for="offsetHours" class="small text-muted mb-0" style="font-size: 11px;">Offset Jam Manual</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control" id="offsetHours" name="offset_hours" value="0" step="1">
                                        <div class="input-group-append"><span class="input-group-text">Jam</span></div>
                                    </div>
                                </div>
                                <div class="col-6 mb-1">
                                    <label for="offsetMinutes" class="small text-muted mb-0" style="font-size: 11px;">Offset Menit</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control" id="offsetMinutes" name="offset_minutes" value="0" step="1" min="-59" max="59">
                                        <div class="input-group-append"><span class="input-group-text">Mnt</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2.2 Lokasi Kegiatan, Koordinat & Catatan -->
                        <div class="card bg-light border-0 rounded-lg p-3 mb-3 shadow-xs">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="font-weight-bold text-dark small">
                                    <i class="fas fa-map-marker-alt text-danger mr-1"></i> Lokasi Kegiatan &amp; Koordinat GPS
                                </span>
                                <div>
                                    <button type="button" class="btn btn-outline-info btn-xs font-weight-bold d-none" id="btnGpsFromExif" title="Kembalikan koordinat sesuai EXIF foto">
                                        <i class="fas fa-satellite-dish mr-1"></i> Pulihkan GPS EXIF
                                    </button>
                                </div>
                            </div>

                            <!-- Opsi Ambil Lokasi dari EXIF -->
                            <div class="custom-control custom-switch mb-2 bg-white p-2 rounded border">
                                <input type="checkbox" class="custom-control-input" id="useExifLocation" name="use_exif_location" value="1" checked>
                                <label class="custom-control-label font-weight-bold text-dark small" for="useExifLocation">
                                    <i class="fas fa-satellite-dish text-primary mr-1"></i> Utamakan Koordinat GPS dari EXIF Foto
                                </label>
                                <small class="d-block text-muted" style="font-size: 10px;">
                                    Jika aktif dan file foto memiliki koordinat GPS, stempel lokasi otomatis mengikuti GPS asli foto.
                                </small>
                            </div>

                            <!-- Input Lokasi dengan Tombol Buka Peta & GPS Saya -->
                            <div class="form-group mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label for="lokasi" class="small font-weight-bold text-dark mb-0">Nama Lokasi / Alamat:</label>
                                    <button type="button" class="btn btn-link btn-xs p-0 text-success font-weight-bold" id="btnGpsQuickFromForm" title="Ambil GPS perangkat Anda saat ini">
                                        <i class="fas fa-location-arrow mr-1"></i> GPS Saya
                                    </button>
                                </div>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" id="lokasi" name="lokasi" placeholder="Otomatis dari EXIF, atau ketik nama lokasi / pilih peta">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-primary" id="btnBukaPetaModal" title="Pilih Lokasi dari Peta">
                                            <i class="fas fa-map-marked-alt mr-1"></i> Buka Peta
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group mb-2">
                                    <label for="koordinat" class="small font-weight-bold text-dark mb-1">Koordinat GPS (Lat, Lng):</label>
                                    <input type="text" class="form-control form-control-sm" id="koordinat" name="koordinat" placeholder="Contoh: 0.5071, 101.4478">
                                    <small class="text-muted" style="font-size: 10px;">Format desimal atau derajat (bisa diedit bebas)</small>
                                </div>
                                <div class="col-md-6 form-group mb-2">
                                    <label for="catatan" class="small font-weight-bold text-dark mb-1">Catatan Kegiatan / Proyek:</label>
                                    <input type="text" class="form-control form-control-sm" id="catatan" name="catatan" placeholder="Contoh: Rehabilitasi Gedung Balai">
                                    <small class="text-muted" style="font-size: 10px;">Disematkan pada watermark dan metadata foto</small>
                                </div>
                            </div>
                        </div>

                        <!-- 2.3 Penulisan & Sinkronisasi Metadata EXIF Berkas Foto -->
                        <div class="card border border-primary-light bg-white rounded-lg p-3 mb-3 shadow-xs">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="font-weight-bold text-dark small">
                                    <i class="fas fa-tags text-primary mr-1"></i> Tulis &amp; Sinkronkan Metadata EXIF ke Berkas Foto
                                </span>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="updateExif" name="update_exif" value="1" checked>
                                    <label class="custom-control-label font-weight-bold small text-primary" for="updateExif">Tulis ke File</label>
                                </div>
                            </div>
                            <p class="small text-muted mb-2" style="font-size: 11px;">
                                Setiap perubahan tanggal, jam, koordinat GPS, dan catatan kegiatan di atas akan <strong>otomatis ditulis langsung ke metadata internal file JPEG</strong> (tag EXIF DateTimeOriginal, GPSLatitude, GPSLongitude, ImageDescription) saat foto disimpan atau diunduh.
                            </p>
                            <div class="p-2 bg-light rounded border small" style="font-size: 11px;" id="exifLiveSummaryBox">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted font-weight-bold"><i class="fas fa-info-circle mr-1 text-primary"></i> Pratinjau Metadata yang Akan Disimpan:</span>
                                    <span class="badge badge-success" id="metaExifStatusBadge"><i class="fas fa-check-circle mr-1"></i> Sinkronisasi EXIF Aktif</span>
                                </div>
                                <div class="text-truncate mb-1"><i class="fas fa-calendar-alt text-secondary mr-1"></i> <strong>EXIF Waktu:</strong> <span id="metaExifTimeSummary" class="text-dark font-weight-bold">-</span></div>
                                <div class="text-truncate mb-1"><i class="fas fa-map-marker-alt text-danger mr-1"></i> <strong>EXIF GPS:</strong> <span id="metaExifGpsSummary" class="text-dark font-weight-bold">-</span></div>
                                <div class="text-truncate"><i class="fas fa-file-alt text-info mr-1"></i> <strong>EXIF Deskripsi:</strong> <span id="metaExifDescSummary" class="text-dark font-weight-bold">-</span></div>
                            </div>
                        </div>

                        <!-- Navigasi Langkah 2 -->
                        <div class="d-flex justify-content-between mt-3 pt-2 border-top">
                            <button type="button" class="btn btn-outline-secondary px-3 btn-prev-step" data-target="1">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali
                            </button>
                            <button type="button" class="btn btn-primary px-4 font-weight-bold btn-next-step" data-target="3">
                                Lanjut: Simpan &amp; Proses <i class="fas fa-arrow-right ml-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- ======================================================= -->
                    <!-- LANGKAH 3: SIMPAN SERVER & PROSES WATERMARK             -->
                    <!-- ======================================================= -->
                    <div class="wizard-step-pane d-none" id="wizardStepPane3">
                        <!-- 3.1 Simpan ke Folder Server -->
                        <div class="card bg-light border-0 rounded-lg p-3 mb-3">
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" id="saveToFolder" name="save_to_folder" value="1" checked>
                                <label class="custom-control-label font-weight-bold text-dark small" for="saveToFolder">
                                    <i class="fas fa-folder-plus text-warning mr-1"></i> Simpan Salinan Foto ke Direktori Server
                                </label>
                            </div>
                            <div id="folderInputContainer">
                                <label for="targetFolderName" class="small text-muted mb-1">Nama Folder Penyimpanan:</label>
                                <div class="input-group input-group-sm mb-1">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-folder text-warning"></i> /uploads/watermark_hasil/</span>
                                    </div>
                                    <input type="text" class="form-control" id="targetFolderName" name="target_folder_name" value="Dokumentasi_<?= date('Ymd_His') ?>" placeholder="Nama folder...">
                                </div>
                                <small class="text-muted" style="font-size: 11px;">
                                    Foto yang diproses akan tersimpan aman di server dan dapat diakses kembali sewaktu-waktu.
                                </small>
                            </div>
                        </div>

                        <!-- 3.2 Logo Kustom Instansi (Opsional) -->
                        <div class="card bg-light border-0 rounded-lg p-3 mb-3">
                            <label for="logo" class="small font-weight-bold text-dark mb-1">
                                <i class="fas fa-shield-alt text-info mr-1"></i> Logo Instansi Kustom (Opsional):
                            </label>
                            <div class="custom-file mb-1">
                                <input type="file" class="custom-file-input" id="logo" name="logo" accept="image/png,image/jpeg,image/webp">
                                <label class="custom-file-label small" for="logo">Pilih logo instansi (PNG/JPG)...</label>
                            </div>
                            <small class="text-muted" style="font-size: 11px;">
                                Jika dikosongkan, logo default resmi <strong>Kementerian PU / Satker PPS</strong> akan digunakan secara otomatis.
                            </small>
                        </div>

                        <!-- 3.3 Ringkasan Konfigurasi Sebelum Proses -->
                        <div class="card border rounded-lg p-3 mb-3 shadow-none bg-white">
                            <h6 class="font-weight-bold text-dark small mb-2"><i class="fas fa-clipboard-check text-success mr-1"></i> Ringkasan Pengaturan Watermark:</h6>
                            <div class="row small text-muted">
                                <div class="col-6 mb-1">Format: <strong class="text-dark" id="summaryTemplate">Klasik PU</strong></div>
                                <div class="col-6 mb-1">File Terpilih: <strong class="text-dark" id="summaryFiles">0 Berkas</strong></div>
                                <div class="col-6 mb-1">Penyesuaian Jam: <strong class="text-dark" id="summaryOffset">+0 Jam</strong></div>
                                <div class="col-6 mb-1">Simpan Server: <strong class="text-dark" id="summarySave">Ya</strong></div>
                                <div class="col-12 mb-1">Tulis EXIF Berkas: <strong class="text-success" id="summaryExif">Ya (Tanggal, GPS &amp; Catatan)</strong></div>
                            </div>
                        </div>

                        <!-- Tombol Submit Utama -->
                        <div class="form-group mb-2">
                            <button type="submit" class="btn btn-primary btn-lg btn-block shadow font-weight-bold py-3" id="btnProses">
                                <i class="fas fa-stamp mr-2"></i> Proses Watermark Sekarang
                            </button>
                        </div>

                        <!-- Navigasi Langkah 3 -->
                        <div class="d-flex justify-content-start mt-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm px-3 btn-prev-step" data-target="2">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Waktu &amp; Lokasi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- KOLOM KANAN: LIVE SIMULATOR & HASIL AKHIR (OPSI 2: YA)             -->
        <!-- =================================================================== -->
        <div class="col-lg-6 col-xl-6 mb-4">
            <div class="card shadow-sm border-0 sticky-top studio-preview-card" style="top: 20px; z-index: 10;">
                <!-- HEADER TAB KANAN: DUAL VIEW (SIMULASI LIVE vs HASIL PEMROSESAN) -->
                <div class="card-header bg-white border-bottom p-2 p-md-3 d-flex justify-content-between align-items-center">
                    <div class="btn-group btn-group-sm p-1 bg-light rounded-pill" role="group">
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 font-weight-bold" id="btnTabSimulator">
                            <i class="fas fa-eye mr-1"></i> Simulasi Live <span class="badge badge-light text-primary ml-1" style="font-size: 9px;">Real-time</span>
                        </button>
                        <button type="button" class="btn btn-sm btn-light rounded-pill px-3 font-weight-bold text-muted" id="btnTabResult">
                            <i class="fas fa-check-circle mr-1"></i> Hasil Akhir <span class="badge badge-secondary ml-1 d-none" id="resultCountBadge">0</span>
                        </button>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-xs py-1 px-2" id="btnResetAll" style="display: none;">
                        <i class="fas fa-redo mr-1"></i> Reset
                    </button>
                </div>

                <div class="card-body p-3">
                    <!-- VIEW 1: LIVE INTERACTIVE WATERMARK SIMULATOR -->
                    <div id="liveSimulatorSection">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small font-weight-bold text-dark">
                                <i class="fas fa-satellite-dish text-success mr-1"></i> Kanvas Simulasi Pratinjau:
                            </span>
                            <span class="badge badge-success px-2 py-1" style="font-size: 10px;">
                                <i class="fas fa-bolt mr-1"></i> Sinkronisasi Otomatis
                            </span>
                        </div>

                        <!-- LIVE CANVAS CONTAINER -->
                        <div class="simulator-viewport rounded shadow-sm position-relative overflow-hidden mb-2" id="simulatorViewport">
                            <!-- Background Image (Sample or Uploaded) -->
                            <div class="simulator-canvas-bg" id="simulatorCanvasBg"></div>

                            <!-- Live Mockup Overlay -->
                            <div class="simulator-overlay-wrapper" id="simulatorOverlayWrapper">
                                <!-- TEMPLATE OVERLAY: CLASSIC -->
                                <div class="sim-template-box sim-classic" id="simClassicOverlay">
                                    <div class="sim-classic-top d-flex align-items-baseline">
                                        <div class="sim-clock-big" id="simClassicClock">15:06</div>
                                        <div class="sim-divider-line mx-2"></div>
                                        <div class="sim-date-block">
                                            <div class="sim-day-name font-weight-bold" id="simClassicDay">Selasa</div>
                                            <div class="sim-date-text" id="simClassicDate">27 Okt 2026 | WIB</div>
                                        </div>
                                    </div>
                                    <div class="sim-classic-bottom mt-2 d-flex align-items-center">
                                        <div class="sim-accent-bar mr-2"></div>
                                        <div class="sim-info-content">
                                            <div class="sim-logo-tag badge badge-warning text-dark font-weight-bold mb-1" id="simClassicLogoTag">
                                                <i class="fas fa-building mr-1"></i> PU SATKER RIAU
                                            </div>
                                            <div class="sim-location-text font-weight-bold text-white text-truncate" id="simClassicLocation">
                                                0.507068° N, 101.447779° E
                                            </div>
                                            <div class="sim-coords-text small text-warning" id="simClassicCoords">
                                                Pekanbaru, Riau
                                            </div>
                                            <div class="sim-notes-text small text-light text-truncate" id="simClassicNotes">
                                                Dokumentasi Lapangan
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- TEMPLATE OVERLAY: TABLE / GRID -->
                                <div class="sim-template-box sim-table d-none" id="simTableOverlay">
                                    <div class="sim-table-inner border border-secondary rounded p-2">
                                        <div class="sim-table-header text-info font-weight-bold border-bottom border-secondary pb-1 mb-1 d-flex justify-content-between">
                                            <span><i class="fas fa-hard-hat mr-1"></i> INSPEKSI PU RIAU</span>
                                            <span class="badge badge-warning text-dark" style="font-size: 8px;">KEMENTERIAN PU</span>
                                        </div>
                                        <div class="sim-table-row d-flex justify-content-between text-warning small py-0">
                                            <span class="text-muted">WAKTU</span>
                                            <span class="font-weight-bold" id="simTableTime">15:06 WIB</span>
                                        </div>
                                        <div class="sim-table-row d-flex justify-content-between text-light small py-0">
                                            <span class="text-muted">TANGGAL</span>
                                            <span id="simTableDate">27 Okt 2026</span>
                                        </div>
                                        <div class="sim-table-row d-flex justify-content-between text-light small py-0">
                                            <span class="text-muted">LOKASI</span>
                                            <span class="text-truncate text-right ml-2" id="simTableLocation">Pekanbaru</span>
                                        </div>
                                        <div class="sim-table-row d-flex justify-content-between text-light small py-0">
                                            <span class="text-muted">GPS</span>
                                            <span id="simTableCoords">0.507068° N, 101.447779° E</span>
                                        </div>
                                        <div class="sim-table-row d-flex justify-content-between text-light small py-0" id="simTableNotesRow">
                                            <span class="text-muted">PROYEK</span>
                                            <span class="text-truncate text-right ml-2" id="simTableNotes">Kegiatan Lapangan</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- TEMPLATE OVERLAY: BADGE / FLOATING CARD -->
                                <div class="sim-template-box sim-badge d-none" id="simBadgeOverlay">
                                    <div class="sim-badge-card rounded p-2 border border-secondary">
                                        <div class="badge badge-warning text-dark font-weight-bold mb-1" style="font-size: 8px;">
                                            <i class="fas fa-shield-alt mr-1"></i> KEMENTERIAN PU
                                        </div>
                                        <div class="sim-clock-badge font-weight-bold text-white" id="simBadgeClock">15:06</div>
                                        <div class="text-warning small" id="simBadgeDate">Selasa, 27 Okt 2026</div>
                                        <div class="text-white small text-truncate mt-1" id="simBadgeLocation">
                                            <i class="fas fa-map-marker-alt text-danger mr-1"></i> <span id="simBadgeLocationText">Pekanbaru, Riau</span>
                                        </div>
                                        <div class="text-muted small" id="simBadgeCoords" style="font-size: 9px;">0.507068° N, 101.447779° E</div>
                                    </div>
                                </div>

                                <!-- TEMPLATE OVERLAY: BOTTOM STRIP -->
                                <div class="sim-template-box sim-strip d-none" id="simStripOverlay">
                                    <div class="sim-strip-banner p-2 border-top border-warning d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="text-warning font-weight-bold h6 mb-0" id="simStripClock">15:06 WIB</div>
                                            <small class="text-light" id="simStripDate">27 Okt 2026 | PPS Riau</small>
                                        </div>
                                        <div class="text-right text-truncate ml-2" style="max-width: 60%;">
                                            <div class="text-white small font-weight-bold text-truncate" id="simStripLocation">Pekanbaru, Riau</div>
                                            <small class="text-warning text-truncate d-block" id="simStripCoords">0.507068° N, 101.447779° E</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted" style="font-size: 11px;">
                                <i class="fas fa-info-circle text-info mr-1"></i> Simulasi render live. Saat diproses, server akan merender font resolusi tinggi asli kamera.
                            </small>
                            <span class="badge badge-light border text-muted" id="simBackgroundInfo">Foto Sampel</span>
                        </div>
                    </div>

                    <!-- VIEW 2: HASIL PEMROSESAN (HIDDEN BY DEFAULT UNTIL PROCESSED) -->
                    <div id="resultSection" style="display: none;">
                        <!-- LOADING STATE -->
                        <div id="loadingContainer" class="text-center py-5" style="display: none;">
                            <div class="spinner-border text-primary mb-3" style="width: 3.5rem; height: 3.5rem;" role="status"></div>
                            <h6 class="font-weight-bold text-dark mb-1">Sedang Memproses Watermark Foto...</h6>
                            <p class="text-muted small mb-0">Menerapkan format watermark, ekstraksi EXIF, dan mengompilasi berkas.</p>
                        </div>

                        <!-- EMPTY PLACEHOLDER -->
                        <div id="placeholderContainer" class="text-center py-5 text-muted">
                            <div class="my-4">
                                <i class="fas fa-images fa-4x text-black-50 mb-3"></i>
                                <h6 class="font-weight-bold text-secondary">Belum Ada Foto Diproses</h6>
                                <p class="small text-muted mb-0">Atur form di kolom kiri lalu klik tombol <strong>Proses Watermark Sekarang</strong>.</p>
                            </div>
                        </div>

                        <!-- HASIL BATCH / ZIP CONTAINER -->
                        <div id="batchResultContainer" style="display: none;">
                            <div class="alert alert-success d-flex align-items-center justify-content-between p-3 mb-3">
                                <div>
                                    <h6 class="font-weight-bold mb-1"><i class="fas fa-check-circle mr-1"></i> <span id="batchCountText">0</span> Foto Berhasil Diproses!</h6>
                                    <small class="text-dark" id="batchFolderInfo" style="display: none;">
                                        <i class="fas fa-folder-check mr-1 text-primary"></i> Tersimpan di server: <code id="batchFolderText" class="font-weight-bold"></code>
                                    </small>
                                </div>
                            </div>

                            <!-- TOMBOL DOWNLOAD BATCH ZIP -->
                            <div class="mb-3" id="batchZipDownloadAction" style="display: none;">
                                <a href="#" id="linkDownloadBatchZip" class="btn btn-success btn-block btn-lg font-weight-bold shadow-sm">
                                    <i class="fas fa-file-archive mr-2"></i> Download Semua Foto (File ZIP)
                                </a>
                            </div>

                            <!-- GALLERY GRID MINI PREVIEWS -->
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="font-weight-bold small text-dark mb-0">Galeri Foto Hasil:</label>
                                <span class="badge badge-light border text-muted">Klik foto untuk perbesar</span>
                            </div>
                            <div class="row" id="batchGalleryGrid" style="max-height: 480px; overflow-y: auto;"></div>
                        </div>

                        <!-- HASIL SINGLE CONTAINER -->
                        <div id="singleResultContainer" style="display: none;">
                            <div class="text-center mb-3">
                                <img id="singlePreviewImage" src="" alt="Watermark Preview" class="img-fluid rounded shadow border cursor-pointer" style="max-height: 400px; object-fit: contain;" title="Klik untuk perbesar">
                            </div>

                            <div class="alert alert-info py-2 px-3 small mb-3" id="singleServerSavedAlert" style="display: none;">
                                <i class="fas fa-folder text-warning mr-1"></i> Salinan file tersimpan di server: <strong id="singleServerSavedPath"></strong>
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-success font-weight-bold flex-fill mr-2 py-2" id="btnDownloadSingle">
                                    <i class="fas fa-download mr-1"></i> Download Foto
                                </button>
                                <button type="button" class="btn btn-outline-secondary py-2" id="btnResetSingle">
                                    <i class="fas fa-redo mr-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- MODAL IMAGE ZOOM VIEWER -->
<div class="modal fade" id="modalZoomPhoto" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header py-2 bg-dark text-white">
                <h6 class="modal-title font-weight-bold" id="modalZoomTitle">Detail Foto Watermark</h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center p-2 bg-dark">
                <img id="modalZoomImg" src="" alt="Zoom" class="img-fluid rounded" style="max-height: 80vh;">
            </div>
            <div class="modal-footer py-2 bg-dark text-white d-flex justify-content-between border-top border-secondary">
                <span class="small text-muted" id="modalZoomMeta"></span>
                <a href="#" id="modalZoomDownload" class="btn btn-success btn-sm font-weight-bold" download>
                    <i class="fas fa-download mr-1"></i> Download File Ini
                </a>
            </div>
        </div>
    </div>
</div>

<!-- MODAL MAP PICKER (POPUP PETA LEAFLET DENGAN DESAIN BERSIH) -->
<div class="modal fade" id="modalPilihPeta" tabindex="-1" role="dialog" aria-labelledby="modalPilihPetaTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document" style="max-width: 880px;">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title font-weight-bold" id="modalPilihPetaTitle" style="font-size: 1.05rem;">
                    <i class="fas fa-map-marked-alt mr-2"></i> Pilih Lokasi &amp; Koordinat dari Peta Interaktif
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                <!-- Search & GPS Row -->
                <div class="row mb-2">
                    <div class="col-md-8 mb-2 mb-md-0">
                        <div class="input-group input-group-sm">
                            <input type="text" id="mapSearchInput" class="form-control" placeholder="Ketik nama jalan, kantor, atau tempat di Riau/Indonesia...">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-primary" id="btnSearchMap">
                                    <i class="fas fa-search mr-1"></i> Cari Tempat
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-right">
                        <div class="btn-group btn-group-sm w-100" role="group">
                            <button type="button" class="btn btn-outline-info" id="btnGpsFromExif" style="display:none;" title="Arahkan peta ke koordinat EXIF foto">
                                <i class="fas fa-camera mr-1"></i> EXIF Foto
                            </button>
                            <button type="button" class="btn btn-outline-success" id="btnGpsCurrentLocation" title="Deteksi posisi GPS perangkat">
                                <i class="fas fa-location-arrow mr-1"></i> Lokasi Saya
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Leaflet Map Container -->
                <div id="mapPickerContainer" style="height: 380px; width: 100%; border-radius: 8px; border: 1px solid #ced4da; position: relative;"></div>

                <!-- Info Box Terpilih -->
                <div class="card bg-light border mt-2 mb-0 p-2 shadow-none">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <small class="text-muted d-block" style="font-size: 11px;">Alamat / Tempat Terpilih:</small>
                            <span class="font-weight-bold text-dark small text-truncate d-block" id="selectedAddressText">Klik atau geser penanda pin pada peta</span>
                        </div>
                        <div class="col-md-5 text-md-right mt-1 mt-md-0">
                            <small class="text-muted d-block" style="font-size: 11px;">Koordinat GPS:</small>
                            <code class="font-weight-bold text-primary" id="selectedCoordsText">0.507068° N, 101.447779° E</code>
                        </div>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                        <span class="small font-weight-bold text-dark"><i class="fas fa-pen-fancy mr-1 text-secondary"></i> Format Teks ke Formulir:</span>
                        <div class="d-flex align-items-center mt-1 mt-sm-0">
                            <div class="custom-control custom-radio mr-3">
                                <input type="radio" id="mapFormatCoords" name="map_text_format" class="custom-control-input" value="coords" checked>
                                <label class="custom-control-label small font-weight-bold text-primary" for="mapFormatCoords">
                                    <i class="fas fa-crosshairs mr-1"></i> Koordinat Saja
                                </label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="mapFormatAddress" name="map_text_format" class="custom-control-input" value="address">
                                <label class="custom-control-label small text-dark" for="mapFormatAddress">
                                    <i class="fas fa-map-marker-alt mr-1"></i> Nama Alamat Lengkap
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2 bg-light d-flex justify-content-between">
                <span class="small text-muted">
                    <i class="fas fa-info-circle mr-1 text-info"></i> Geser pin penanda atau klik langsung pada peta.
                </span>
                <div>
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary btn-sm font-weight-bold px-3" id="btnTerapkanLokasiPeta">
                        <i class="fas fa-check mr-1"></i> Gunakan Lokasi Ini
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Header Card */
.header-banner-card {
    border-radius: 12px;
    background: #ffffff;
    border-top: 4px solid var(--app-primary, #0A66C2) !important;
}
.header-icon-box {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: rgba(10, 102, 194, 0.1);
    color: var(--app-primary, #0A66C2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
}

/* Wizard Step Navigation */
.studio-control-card, .studio-preview-card {
    border-radius: 12px;
}
.wizard-steps-nav {
    background: #f1f5f9;
    padding: 4px;
    border-radius: 10px;
    gap: 4px;
}
.wizard-step-btn {
    border-radius: 8px;
    font-weight: 600;
    font-size: 12px;
    padding: 6px 10px;
    transition: all 0.2s ease;
    border: none;
    color: #64748b;
    background: transparent;
}
.wizard-step-btn.active {
    background: #ffffff !important;
    color: var(--app-primary, #0A66C2) !important;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}
.wizard-step-btn .step-num {
    display: inline-block;
    width: 18px;
    height: 18px;
    line-height: 18px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #475569;
    font-size: 11px;
    text-align: center;
}
.wizard-step-btn.active .step-num {
    background: var(--app-primary, #0A66C2);
    color: #ffffff;
}

/* Template Cards */
.template-card {
    transition: all 0.2s ease-in-out;
    background-color: #fff;
    border-color: #e2e8f0 !important;
}
.template-card:hover {
    border-color: var(--app-primary, #0A66C2) !important;
    transform: translateY(-2px);
}
.template-card.active {
    border-color: var(--app-primary, #0A66C2) !important;
    border-width: 2px !important;
    background-color: #f0f7ff;
    box-shadow: 0 4px 10px rgba(10, 102, 194, 0.12);
}
.cursor-pointer {
    cursor: pointer;
}
.border-dashed {
    border-style: dashed !important;
    border-width: 2px !important;
}
.upload-dropzone {
    background-color: #f8fafc;
    border-color: #cbd5e1;
    transition: all 0.2s ease;
}
.upload-dropzone:hover, .upload-dropzone.bg-white {
    background-color: #f0f7ff !important;
    border-color: var(--app-primary, #0A66C2) !important;
}

/* Live Simulator Canvas */
.simulator-viewport {
    width: 100%;
    height: 380px;
    background-color: #1e293b;
    position: relative;
    border: 1px solid #334155;
}
.simulator-canvas-bg {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: url('https://images.unsplash.com/photo-1541888946425-d0fbb186156f?q=80&w=1200&auto=format&fit=crop');
    background-size: cover;
    background-position: center;
    filter: brightness(0.92);
}
.simulator-overlay-wrapper {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 16px;
}
.sim-template-box {
    animation: fadeInOverlay 0.3s ease;
}
@keyframes fadeInOverlay {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Template 1: Classic */
.sim-classic {
    background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.85) 100%);
    padding: 12px 14px;
    border-radius: 8px;
    max-width: 90%;
    backdrop-filter: blur(2px);
}
.sim-clock-big {
    font-size: 28px;
    font-weight: 800;
    color: #facc15;
    line-height: 1;
    letter-spacing: 0.5px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.6);
}
.sim-divider-line {
    width: 2px;
    height: 24px;
    background: rgba(255,255,255,0.4);
}
.sim-day-name {
    font-size: 11px;
    color: #ffffff;
    line-height: 1.1;
}
.sim-date-text {
    font-size: 10px;
    color: #cbd5e1;
}
.sim-accent-bar {
    width: 3px;
    height: 38px;
    background: #facc15;
    border-radius: 2px;
}
.sim-location-text {
    font-size: 11px;
    line-height: 1.2;
}

/* Template 2: Table */
.sim-table {
    max-width: 95%;
}
.sim-table-inner {
    background: rgba(15, 23, 42, 0.88);
    backdrop-filter: blur(4px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.4);
}

/* Template 3: Badge */
.sim-badge {
    max-width: 80%;
}
.sim-badge-card {
    background: rgba(15, 23, 42, 0.85);
    backdrop-filter: blur(6px);
    box-shadow: 0 4px 14px rgba(0,0,0,0.35);
}
.sim-clock-badge {
    font-size: 22px;
    line-height: 1;
    letter-spacing: 0.5px;
}

/* Template 4: Strip */
.sim-strip {
    margin: -16px;
}
.sim-strip-banner {
    background: rgba(15, 23, 42, 0.92);
    backdrop-filter: blur(4px);
}

.shadow-xs {
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
</style>
<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script src="<?= esc(media_url('assets/leaflet/leaflet.js')); ?>"></script>
<script src="<?= esc(media_url('assets/exif/exifr.full.umd.js')); ?>"></script>
<script src="<?= esc(media_url('assets/heic/heic-to.min.js')); ?>"></script>
<script src="<?= esc(media_url('assets/heic/heic2any.min.js')); ?>"></script>
<script>
$(function() {
    'use strict';

    // State Variables
    var selectedFiles = [];
    var singleGeneratedUri = null;
    var currentZipDownloadUrl = null;
    var isProcessing = false;
    var currentStep = 1;

    // Base Date & Time state for Live Simulator
    var baseDate = '<?= date('Y-m-d') ?>';
    var baseTime = '<?= date('H:i') ?>';
    var lastExifDate = null;
    var lastExifTime = null;
    var lastExifCoordinates = null;

    // Helper cek file HEIC / HEIF
    function isHeic(f) {
        if (!f) return false;
        var name = (f.name || '').toLowerCase();
        return name.endsWith('.heic') || name.endsWith('.heif') || f.type === 'image/heic' || f.type === 'image/heif';
    }

    // -------------------------------------------------------------------------
    // 1. WIZARD STEP NAVIGATION (OPSI 1)
    // -------------------------------------------------------------------------
    function goToStep(step) {
        currentStep = parseInt(step, 10);
        $('.wizard-step-btn').removeClass('active').addClass('text-muted');
        $('#tabBtnStep' + currentStep).addClass('active').removeClass('text-muted');

        $('.wizard-step-pane').addClass('d-none').removeClass('active');
        $('#wizardStepPane' + currentStep).removeClass('d-none').addClass('active');

        // Scroll smooth to top of form on mobile
        if ($(window).width() < 768) {
            $('html, body').animate({ scrollTop: $('#watermarkForm').offset().top - 20 }, 250);
        }
        updateSummaryCard();
    }

    $('.wizard-step-btn').on('click', function() {
        var targetStep = $(this).data('step');
        goToStep(targetStep);
    });

    $('.btn-next-step').on('click', function() {
        var target = $(this).data('target');
        goToStep(target);
    });

    $('.btn-prev-step').on('click', function() {
        var target = $(this).data('target');
        goToStep(target);
    });

    // -------------------------------------------------------------------------
    // 2. TEMPLATE CARD SELECTION
    // -------------------------------------------------------------------------
    var templateLabels = {
        'classic': 'Klasik PU',
        'table': 'Grid Inspeksi',
        'badge': 'Modern Badge',
        'strip': 'Bottom Strip'
    };

    $('.template-card').on('click', function() {
        $('.template-card').removeClass('active');
        $('.template-card').find('.check-badge').addClass('d-none');
        
        $(this).addClass('active');
        $(this).find('input[type="radio"]').prop('checked', true);
        $(this).find('.check-badge').removeClass('d-none');

        var val = $(this).data('val');
        $('#activeTemplateBadge').text(templateLabels[val] || val);
        $('#summaryTemplate').text(templateLabels[val] || val);

        updateLiveSimulator();
    });

    // -------------------------------------------------------------------------
    // 3. DROPZONE & FILE INPUT HANDLING
    // -------------------------------------------------------------------------
    var $dropzone = $('#dropzoneArea');
    var $fileInput = $('#fileInput');

    $dropzone.on('dragover dragenter', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $dropzone.addClass('bg-white');
    });

    $dropzone.on('dragleave drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $dropzone.removeClass('bg-white');
    });

    $dropzone.on('drop', function(e) {
        var dt = e.originalEvent.dataTransfer;
        if (dt && dt.files && dt.files.length > 0) {
            handleSelectedFiles(dt.files);
        }
    });

    $fileInput.on('change', function() {
        if (this.files && this.files.length > 0) {
            handleSelectedFiles(this.files);
        }
    });

    function convertSingleHeic(file) {
        if (typeof HeicTo === 'function') {
            return HeicTo({ blob: file, type: 'image/jpeg', quality: 0.92 })
                .then(function(blob) {
                    var newName = file.name.replace(/\.(heic|heif)$/i, '.jpg');
                    return new File([blob], newName, { type: 'image/jpeg' });
                });
        }
        if (typeof heic2any === 'function') {
            return heic2any({ blob: file, toType: 'image/jpeg', quality: 0.92, multiple: true })
                .then(function(result) {
                    var singleBlob = Array.isArray(result) ? result[0] : result;
                    var newName = file.name.replace(/\.(heic|heif)$/i, '.jpg');
                    return new File([singleBlob], newName, { type: 'image/jpeg' });
                });
        }
        return Promise.reject(new Error('Decoder HEIC tidak tersedia di peramban.'));
    }

    // -------------------------------------------------------------------------
    // 3.1 CLIENT-SIDE EXIF EXTRACTION (INSTANT & PRESERVES RAW EXIF)
    // -------------------------------------------------------------------------
    function extractExifClientSide(file) {
        if (typeof exifr === 'undefined' || !file) return;

        exifr.parse(file, {
            gps: true,
            tiff: true,
            exif: true
        }).then(function(exif) {
            if (!exif) return;
            var hasExif = false;

            // 1. Tanggal & Jam Asli Foto dari EXIF
            var dtOrig = exif.DateTimeOriginal || exif.CreateDate || exif.ModifyDate;
            if (dtOrig) {
                var dt = (dtOrig instanceof Date) ? dtOrig : new Date(dtOrig);
                if (!isNaN(dt.getTime())) {
                    var yyyy = dt.getFullYear();
                    var mm = String(dt.getMonth() + 1).padStart(2, '0');
                    var dd = String(dt.getDate()).padStart(2, '0');
                    var hh = String(dt.getHours()).padStart(2, '0');
                    var ii = String(dt.getMinutes()).padStart(2, '0');

                    lastExifDate = yyyy + '-' + mm + '-' + dd;
                    lastExifTime = hh + ':' + ii;
                    baseDate = lastExifDate;
                    baseTime = lastExifTime;

                    $('#manualDate').val(lastExifDate);
                    $('#manualTime').val(lastExifTime);
                    $('#btnTimeFromExif').removeClass('d-none').show();
                    hasExif = true;
                }
            }

            // 2. Koordinat GPS dari EXIF
            if (typeof exif.latitude === 'number' && typeof exif.longitude === 'number') {
                var lat = exif.latitude;
                var lng = exif.longitude;
                var coordsStr = lat.toFixed(6) + ', ' + lng.toFixed(6);
                lastExifCoordinates = coordsStr;

                $('#koordinat').val(coordsStr);
                $('#btnGpsFromExif').removeClass('d-none').show();
                setMarkerLocation(lat, lng, true);

                // Otomatis lakukan reverse geocoding untuk mengisi nama alamat/lokasi
                reverseGeocode(lat, lng, true);
                hasExif = true;
            }

            // 3. Deskripsi jika tersedia
            if (exif.ImageDescription && (! $('#catatan').val() || $('#catatan').val().trim() === '')) {
                $('#catatan').val(exif.ImageDescription);
            }

            if (hasExif) {
                var alertHtml = '<i class="fas fa-check-circle text-success mr-1"></i> <strong>EXIF Foto Terdeteksi:</strong> ';
                if (lastExifDate) alertHtml += 'Waktu: ' + lastExifDate + ' ' + lastExifTime + ' ';
                if (lastExifCoordinates) alertHtml += '| GPS: <code>' + lastExifCoordinates + '</code> ';
                alertHtml += '<span class="text-success font-weight-bold ml-1">(Lokasi &amp; waktu otomatis diisi)</span>';
                $('#exifStatusAlert').removeClass('alert-info alert-warning alert-danger').addClass('alert-success').html(alertHtml).show();
            }

            updateLiveSimulator();
            updateExifSummaryDisplay();
        }).catch(function(err) {
            console.warn('Gagal membaca EXIF foto di browser:', err);
        });
    }

    function handleSelectedFiles(filesList) {
        var rawFiles = Array.from(filesList);
        var heicFiles = rawFiles.filter(isHeic);

        // Ekstraksi EXIF langsung dari file foto pertama secara client-side sebelum konversi kanvas
        var firstImage = rawFiles.find(function(f) {
            return !f.name.toLowerCase().endsWith('.zip');
        });
        if (firstImage) {
            extractExifClientSide(firstImage);
        }

        // Jika terdapat file HEIC (iPhone) dan library decoder tersedia, konversi client-side ke JPEG untuk preview
        if (heicFiles.length > 0 && (typeof HeicTo === 'function' || typeof heic2any === 'function')) {
            $('#fileCountBadge').removeClass('badge-secondary badge-primary badge-success').addClass('badge-warning')
                .html('<i class="fas fa-magic fa-spin mr-1"></i> Mengonversi Format iPhone...');

            var convertPromises = rawFiles.map(function(f) {
                if (isHeic(f)) {
                    return convertSingleHeic(f)
                        .catch(function(err) {
                            console.info('Konversi HEIC client dilewati (dialihkan ke server):', err && err.message ? err.message : err);
                            return f;
                        });
                }
                return Promise.resolve(f);
            });

            Promise.all(convertPromises).then(function(processedList) {
                applyFilesList(processedList);
            });
            return;
        }

        applyFilesList(rawFiles);
    }

    function applyFilesList(files) {
        selectedFiles = files;
        var total = selectedFiles.length;
        var hasZip = false;
        var fileNames = [];
        var hasRawHeic = false;

        selectedFiles.forEach(function(f) {
            if (f.name.toLowerCase().endsWith('.zip')) {
                hasZip = true;
            }
            if (isHeic(f)) {
                hasRawHeic = true;
            }
            fileNames.push(f.name);
        });

        if (total === 1 && hasZip) {
            $('#fileCountBadge').removeClass('badge-secondary badge-warning badge-primary badge-success').addClass('badge-warning').html('<i class="fas fa-file-archive mr-1"></i> 1 Arsip ZIP');
            $('#summaryFiles').text('1 File ZIP');
        } else if (total === 1 && hasRawHeic) {
            $('#fileCountBadge').removeClass('badge-secondary badge-warning badge-success').addClass('badge-info').html('<i class="fas fa-apple-alt mr-1"></i> 1 Foto HEIC');
            $('#summaryFiles').text('1 Foto (HEIC iPhone)');
        } else if (total === 1) {
            $('#fileCountBadge').removeClass('badge-secondary badge-warning badge-success').addClass('badge-primary').html('<i class="fas fa-image mr-1"></i> 1 Foto');
            $('#summaryFiles').text('1 Foto');
        } else {
            $('#fileCountBadge').removeClass('badge-secondary badge-warning badge-primary').addClass('badge-success').html('<i class="fas fa-images mr-1"></i> ' + total + ' Foto');
            $('#summaryFiles').text(total + ' Foto');
        }

        var previewText = fileNames.slice(0, 3).join(', ');
        if (total > 3) {
            previewText += ' dan ' + (total - 3) + ' file lainnya';
        }
        $('#fileSelectedList').html('<strong><i class="fas fa-check-circle text-success mr-1"></i> File Siap:</strong> ' + previewText).fadeIn();

        // Update background of Live Simulator jika single image standar (bukan HEIC)
        if (total >= 1 && !hasZip) {
            var firstImg = selectedFiles[0];
            if (!isHeic(firstImg) && firstImg.type && firstImg.type.startsWith('image/')) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#simulatorCanvasBg').css('background-image', 'url(' + e.target.result + ')');
                    $('#simBackgroundInfo').text('Foto Anda (' + firstImg.name + ')');
                };
                reader.readAsDataURL(firstImg);
            } else if (isHeic(firstImg)) {
                $('#simBackgroundInfo').text('Foto iPhone HEIC (' + firstImg.name + ')');
            }
        }

        // Ekstrak metadata EXIF server-side jika single image (fallback & thumbnail preview)
        if (total === 1 && !hasZip) {
            extractExifPreview(selectedFiles[0]);
        }
        updateSummaryCard();
        updateExifSummaryDisplay();
    }

    function extractExifPreview(file) {
        var formData = new FormData();
        formData.append('foto', file);
        formData.append('<?= csrf_token() ?>', $('input[name="<?= csrf_token() ?>"]').val());

        $.ajax({
            url: '<?= site_url('/admin/dokumentasi/watermark-foto/extract-meta') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(resp) {
                if (resp.csrf_hash) {
                    $('input[name="<?= csrf_token() ?>"]').val(resp.csrf_hash);
                }
                if (resp.status === 'ok') {
                    // Jika server menghasilkan thumbnail JPEG (misal untuk HEIC yang didecode server-side)
                    if (resp.preview_data_uri) {
                        $('#simulatorCanvasBg').css('background-image', 'url(' + resp.preview_data_uri + ')');
                        $('#simBackgroundInfo').text('Foto iPhone (' + file.name + ')');
                    }
                    if (resp.date && !lastExifDate) {
                        lastExifDate = resp.date;
                        baseDate = resp.date;
                        $('#manualDate').val(resp.date);
                        $('#btnTimeFromExif').removeClass('d-none').show();
                    }
                    if (resp.time && !lastExifTime) {
                        lastExifTime = resp.time;
                        baseTime = resp.time;
                        $('#manualTime').val(resp.time);
                    }
                    if (resp.coordinates && !$('#koordinat').val()) {
                        lastExifCoordinates = resp.coordinates;
                        $('#koordinat').val(resp.coordinates);
                        $('#btnGpsFromExif').removeClass('d-none').show();

                        if ($('#useExifLocation').is(':checked') || !$('#lokasi').val() || $('#lokasi').val().trim() === '') {
                            $('#lokasi').val(resp.coordinates);
                            var parts = resp.coordinates.split(',');
                            if (parts.length === 2) {
                                reverseGeocode(parseFloat(parts[0]), parseFloat(parts[1]), true);
                            }
                        }
                    }
                    var statusHtml = '';
                    if (resp.has_exif) {
                        statusHtml = '<i class="fas fa-check-circle mr-1"></i> <strong>EXIF Terdeteksi:</strong> Waktu foto ' + (resp.day || '') + ', ' + (resp.date || '') + ' ' + (resp.time || '');
                        if (resp.coordinates) {
                            statusHtml += ' | <i class="fas fa-satellite-dish text-success ml-1"></i> GPS: <code>' + resp.coordinates + '</code>';
                        }
                        $('#exifStatusAlert').removeClass('alert-info alert-warning').addClass('alert-success').html(statusHtml);
                    }
                    updateLiveSimulator();
                    updateExifSummaryDisplay();
                }
            },
            error: function() {}
        });
    }

    // -------------------------------------------------------------------------
    // 4. OFFSET WAKTU, METADATA EDITOR & LIVE SIMULATOR UPDATER
    // -------------------------------------------------------------------------
    $('input[name="quick_offset"]').on('change', function() {
        var val = parseInt($(this).val(), 10);
        $('#offsetHours').val(val);
        $('#offsetMinutes').val(0);
        updateOffsetBadge(val, 0);
        updateLiveSimulator();
        updateExifSummaryDisplay();
    });

    $('#offsetHours, #offsetMinutes').on('input change', function() {
        var h = parseInt($('#offsetHours').val(), 10) || 0;
        var m = parseInt($('#offsetMinutes').val(), 10) || 0;
        updateOffsetBadge(h, m);
        updateLiveSimulator();
        updateExifSummaryDisplay();
    });

    function updateOffsetBadge(h, m) {
        var text = (h >= 0 ? '+' : '') + h + ' Jam';
        if (m !== 0) {
            text += ' ' + (m >= 0 ? '+' : '') + m + ' Mnt';
        }
        $('#currentOffsetBadge').text(text);
        $('#summaryOffset').text(text);
    }

    $('#btnNow').on('click', function() {
        var now = new Date();
        var yyyy = now.getFullYear();
        var mm = String(now.getMonth() + 1).padStart(2, '0');
        var dd = String(now.getDate()).padStart(2, '0');
        var hh = String(now.getHours()).padStart(2, '0');
        var min = String(now.getMinutes()).padStart(2, '0');
        baseDate = yyyy + '-' + mm + '-' + dd;
        baseTime = hh + ':' + min;
        $('#manualDate').val(baseDate);
        $('#manualTime').val(baseTime);
        updateLiveSimulator();
        updateExifSummaryDisplay();

        const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1800 });
        Toast.fire({ icon: 'info', title: 'Tanggal & jam disetel ke waktu sekarang!' });
    });

    $('#btnTimeFromExif').on('click', function() {
        if (lastExifDate) {
            baseDate = lastExifDate;
            $('#manualDate').val(lastExifDate);
        }
        if (lastExifTime) {
            baseTime = lastExifTime;
            $('#manualTime').val(lastExifTime);
        }
        $('#offsetHours').val(0);
        $('#offsetMinutes').val(0);
        updateOffsetBadge(0, 0);
        $('input[name="quick_offset"][value="0"]').prop('checked', true).parent().addClass('active').siblings().removeClass('active');
        updateLiveSimulator();
        updateExifSummaryDisplay();

        const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
        Toast.fire({ icon: 'info', title: 'Tanggal & jam dipulihkan ke waktu asli EXIF foto!' });
    });

    $('#btnGpsFromExif').on('click', function() {
        if (lastExifCoordinates) {
            $('#koordinat').val(lastExifCoordinates);
            var parsed = parseExistingCoords(lastExifCoordinates);
            if (parsed) {
                setMarkerLocation(parsed.lat, parsed.lng, true);
                reverseGeocode(parsed.lat, parsed.lng, true);
            }
            updateLiveSimulator();
            updateExifSummaryDisplay();

            const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
            Toast.fire({ icon: 'info', title: 'Koordinat GPS dipulihkan ke GPS asli EXIF foto!' });
        }
    });

    $('#manualDate, #manualTime').on('input change', function() {
        baseDate = $('#manualDate').val() || '<?= date('Y-m-d') ?>';
        baseTime = $('#manualTime').val() || '<?= date('H:i') ?>';
        updateLiveSimulator();
        updateExifSummaryDisplay();
    });

    $('#lokasi, #koordinat, #catatan, #useExifLocation, #useExifTime, #updateExif').on('input change', function() {
        updateLiveSimulator();
        updateExifSummaryDisplay();
        updateSummaryCard();
    });

    var daysIndo = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    var monthsIndo = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    function updateLiveSimulator() {
        var h = parseInt($('#offsetHours').val(), 10) || 0;
        var m = parseInt($('#offsetMinutes').val(), 10) || 0;

        // Hitung effective date & time
        var curDate = $('#manualDate').val() || baseDate;
        var curTime = $('#manualTime').val() || baseTime;
        var calcDate = new Date(curDate + 'T' + (curTime.length === 5 ? curTime + ':00' : curTime));
        if (isNaN(calcDate.getTime())) {
            calcDate = new Date();
        }
        calcDate.setHours(calcDate.getHours() + h);
        calcDate.setMinutes(calcDate.getMinutes() + m);

        var hh = String(calcDate.getHours()).padStart(2, '0');
        var mm = String(calcDate.getMinutes()).padStart(2, '0');
        var timeStr = hh + ':' + mm;
        var dayName = daysIndo[calcDate.getDay()] || 'Senin';
        var dateFormatted = calcDate.getDate() + ' ' + (monthsIndo[calcDate.getMonth()] || '') + ' ' + calcDate.getFullYear();
        var fullDateStr = dayName + ', ' + dateFormatted;

        var locInput = $('#lokasi').val() || '';
        var coordsInput = $('#koordinat').val() || '';
        var notesInput = $('#catatan').val() || 'Dokumentasi Lapangan';

        var effectiveLoc = locInput.trim() !== '' ? locInput : (coordsInput ? coordsInput : 'Kantor Balai PPS Riau');
        var effectiveCoords = coordsInput.trim() !== '' ? coordsInput : (lastExifCoordinates ? lastExifCoordinates : '0.507068° N, 101.447779° E');

        // 1. Classic Template
        $('#simClassicClock').text(timeStr);
        $('#simClassicDay').text(dayName);
        $('#simClassicDate').text(dateFormatted + ' | WIB');
        $('#simClassicLocation').text(effectiveLoc);
        $('#simClassicCoords').text(effectiveCoords);
        $('#simClassicNotes').text(notesInput);

        // 2. Table Template
        $('#simTableTime').text(timeStr + ' WIB');
        $('#simTableDate').text(dateFormatted);
        $('#simTableLocation').text(effectiveLoc);
        $('#simTableCoords').text(effectiveCoords);
        $('#simTableNotes').text(notesInput);

        // 3. Badge Template
        $('#simBadgeClock').text(timeStr);
        $('#simBadgeDate').text(fullDateStr);
        $('#simBadgeLocationText').text(effectiveLoc);
        $('#simBadgeCoords').text(effectiveCoords);

        // 4. Strip Template
        $('#simStripClock').text(timeStr + ' WIB');
        $('#simStripDate').text(dateFormatted + ' | PPS Riau');
        $('#simStripLocation').text(effectiveLoc);
        $('#simStripCoords').text(effectiveCoords);

        // Ganti overlay aktif sesuai radio template
        var selectedTemplate = $('input[name="template"]:checked').val() || 'classic';
        $('.sim-template-box').addClass('d-none');
        if (selectedTemplate === 'classic') $('#simClassicOverlay').removeClass('d-none');
        else if (selectedTemplate === 'table') $('#simTableOverlay').removeClass('d-none');
        else if (selectedTemplate === 'badge') $('#simBadgeOverlay').removeClass('d-none');
        else if (selectedTemplate === 'strip') $('#simStripOverlay').removeClass('d-none');

        updateExifSummaryDisplay();
    }

    function updateExifSummaryDisplay() {
        var isExifEnabled = $('#updateExif').is(':checked');
        if (!isExifEnabled) {
            $('#metaExifStatusBadge').removeClass('badge-success').addClass('badge-secondary').html('<i class="fas fa-ban mr-1"></i> Penulisan EXIF Dinonaktifkan');
            $('#metaExifTimeSummary').html('<span class="text-muted font-italic">Tidak disinkronkan ke berkas</span>');
            $('#metaExifGpsSummary').html('<span class="text-muted font-italic">Tidak disinkronkan ke berkas</span>');
            $('#metaExifDescSummary').html('<span class="text-muted font-italic">Tidak disinkronkan ke berkas</span>');
            $('#summaryExif').removeClass('text-success').addClass('text-muted').text('Tidak (Dinonaktifkan)');
            return;
        }

        $('#metaExifStatusBadge').removeClass('badge-secondary').addClass('badge-success').html('<i class="fas fa-check-circle mr-1"></i> Sinkronisasi EXIF Aktif');
        $('#summaryExif').removeClass('text-muted').addClass('text-success').text('Ya (Tanggal, GPS & Catatan)');

        // Tanggal & Jam yang akan disematkan
        var h = parseInt($('#offsetHours').val(), 10) || 0;
        var m = parseInt($('#offsetMinutes').val(), 10) || 0;
        var curDate = $('#manualDate').val() || baseDate;
        var curTime = $('#manualTime').val() || baseTime;
        var calcDate = new Date(curDate + 'T' + (curTime.length === 5 ? curTime + ':00' : curTime));
        if (isNaN(calcDate.getTime())) calcDate = new Date();
        calcDate.setHours(calcDate.getHours() + h);
        calcDate.setMinutes(calcDate.getMinutes() + m);

        var yyyy = calcDate.getFullYear();
        var mm = String(calcDate.getMonth() + 1).padStart(2, '0');
        var dd = String(calcDate.getDate()).padStart(2, '0');
        var hh = String(calcDate.getHours()).padStart(2, '0');
        var ii = String(calcDate.getMinutes()).padStart(2, '0');
        var exifDtStr = yyyy + ':' + mm + ':' + dd + ' ' + hh + ':' + ii + ':00';
        $('#metaExifTimeSummary').html('<code>' + exifDtStr + '</code> <small class="text-muted">(' + dd + '/' + mm + '/' + yyyy + ' ' + hh + ':' + ii + ' WIB)</small>');

        // GPS
        var coordVal = ($('#koordinat').val() || '').trim();
        if (coordVal) {
            $('#metaExifGpsSummary').html('<code>' + coordVal + '</code>');
        } else if (lastExifCoordinates) {
            $('#metaExifGpsSummary').html('<code>' + lastExifCoordinates + '</code> <small class="text-success">(dari EXIF foto)</small>');
        } else {
            $('#metaExifGpsSummary').html('<span class="text-muted font-italic">0.507068, 101.447779 (Default Kantor)</span>');
        }

        // Deskripsi
        var locVal = ($('#lokasi').val() || '').trim();
        var catVal = ($('#catatan').val() || '').trim();
        var desc = [];
        if (locVal) desc.push('Lokasi: ' + locVal);
        if (catVal) desc.push('Catatan: ' + catVal);
        if (coordVal) desc.push('GPS: ' + coordVal);
        var descStr = desc.length > 0 ? desc.join(' | ') : 'Dokumentasi Lapangan Satker PPS Riau';
        $('#metaExifDescSummary').text(descStr);
    }

    function updateSummaryCard() {
        var tpl = $('input[name="template"]:checked').val() || 'classic';
        $('#summaryTemplate').text(templateLabels[tpl] || tpl);
        var total = selectedFiles.length;
        $('#summaryFiles').text(total > 0 ? (total + ' Berkas') : 'Belum dipilih');
        var h = parseInt($('#offsetHours').val(), 10) || 0;
        var m = parseInt($('#offsetMinutes').val(), 10) || 0;
        updateOffsetBadge(h, m);
        $('#summarySave').text($('#saveToFolder').is(':checked') ? 'Ya (/uploads/watermark_hasil/)' : 'Tidak (Unduh Langsung)');
    }

    // Toggle save to folder container
    $('#saveToFolder').on('change', function() {
        if ($(this).is(':checked')) {
            $('#folderInputContainer').slideDown();
        } else {
            $('#folderInputContainer').slideUp();
        }
        updateSummaryCard();
    });

    // Logo label update
    $('#logo').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName || 'Pilih logo instansi (PNG/JPG)...');
    });

    // -------------------------------------------------------------------------
    // 5. DUAL VIEW SWITCHER: SIMULATOR vs HASIL
    // -------------------------------------------------------------------------
    $('#btnTabSimulator').on('click', function() {
        $('#btnTabSimulator').addClass('btn-primary').removeClass('btn-light text-muted');
        $('#btnTabResult').addClass('btn-light text-muted').removeClass('btn-primary');
        $('#resultSection').hide();
        $('#liveSimulatorSection').fadeIn(150);
    });

    $('#btnTabResult').on('click', function() {
        $('#btnTabResult').addClass('btn-primary').removeClass('btn-light text-muted');
        $('#btnTabSimulator').addClass('btn-light text-muted').removeClass('btn-primary');
        $('#liveSimulatorSection').hide();
        $('#resultSection').fadeIn(150);
    });

    function switchToResultView() {
        $('#btnTabResult').click();
    }

    // -------------------------------------------------------------------------
    // 6. FORM SUBMIT & AJAX PROSES WATERMARK
    // -------------------------------------------------------------------------
    $('#watermarkForm').on('submit', function(e) {
        e.preventDefault();

        if (isProcessing) return;

        if (selectedFiles.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'File Belum Dipilih',
                text: 'Silakan pilih foto atau arsip ZIP pada Langkah 1 terlebih dahulu.'
            });
            goToStep(1);
            return;
        }

        var formData = new FormData();
        var totalSize = 0;

        if (selectedFiles.length === 1 && selectedFiles[0].name.toLowerCase().endsWith('.zip')) {
            formData.append('zip_file', selectedFiles[0]);
        } else if (selectedFiles.length === 1) {
            formData.append('foto', selectedFiles[0]);
        } else {
            for (var i = 0; i < selectedFiles.length; i++) {
                formData.append('fotos[]', selectedFiles[i]);
                totalSize += selectedFiles[i].size;
            }
        }

        // Form Fields
        formData.append('template', $('input[name="template"]:checked').val() || 'classic');
        formData.append('use_exif_time', $('#useExifTime').is(':checked') ? '1' : '0');
        formData.append('use_exif_location', $('#useExifLocation').is(':checked') ? '1' : '0');
        formData.append('update_exif', $('#updateExif').is(':checked') ? '1' : '0');
        formData.append('offset_hours', $('#offsetHours').val() || '0');
        formData.append('offset_minutes', $('#offsetMinutes').val() || '0');
        formData.append('manual_date', $('#manualDate').val() || '');
        formData.append('manual_time', $('#manualTime').val() || '');
        formData.append('lokasi', $('#lokasi').val() || '');
        formData.append('koordinat', $('#koordinat').val() || '');
        formData.append('catatan', $('#catatan').val() || '');
        formData.append('save_to_folder', $('#saveToFolder').is(':checked') ? '1' : '0');
        formData.append('target_folder_name', $('#targetFolderName').val() || '');

        // Logo
        var logoInput = document.getElementById('logo');
        if (logoInput && logoInput.files && logoInput.files.length > 0) {
            formData.append('logo', logoInput.files[0]);
        }

        formData.append('<?= csrf_token() ?>', $('input[name="<?= csrf_token() ?>"]').val());

        // Beralih ke View Hasil & Tampilkan Loading
        isProcessing = true;
        switchToResultView();
        var $btn = $('#btnProses');
        var originalBtnHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Sedang Memproses Watermark...');

        $('#placeholderContainer').hide();
        $('#singleResultContainer').hide();
        $('#batchResultContainer').hide();
        $('#loadingContainer').fadeIn(150);

        $.ajax({
            url: '<?= site_url('/admin/dokumentasi/watermark-foto/proses') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(resp) {
                if (resp.csrf_hash) {
                    $('input[name="<?= csrf_token() ?>"]').val(resp.csrf_hash);
                }

                if (resp.status === 'ok') {
                    $('#loadingContainer').hide();
                    $('#btnResetAll').show();

                    var countTotal = resp.total_processed || 1;
                    $('#resultCountBadge').text(countTotal).removeClass('d-none');

                    if (resp.mode === 'single' && resp.image_data) {
                        singleGeneratedUri = resp.image_data;
                        $('#singlePreviewImage').attr('src', resp.image_data);
                        if (resp.server_folder) {
                            $('#singleServerSavedPath').text(resp.server_folder);
                            $('#singleServerSavedAlert').show();
                        } else {
                            $('#singleServerSavedAlert').hide();
                        }
                        $('#singleResultContainer').fadeIn();
                    } else if (resp.mode === 'batch' || resp.images) {
                        $('#batchCountText').text(resp.total_processed);
                        if (resp.server_folder) {
                            $('#batchFolderText').text(resp.server_folder);
                            $('#batchFolderInfo').show();
                        } else {
                            $('#batchFolderInfo').hide();
                        }

                        if (resp.download_zip_url) {
                            currentZipDownloadUrl = resp.download_zip_url;
                            $('#linkDownloadBatchZip').attr('href', resp.download_zip_url);
                            $('#batchZipDownloadAction').show();
                        } else {
                            $('#batchZipDownloadAction').hide();
                        }

                        var $grid = $('#batchGalleryGrid');
                        $grid.empty();

                        resp.images.forEach(function(img) {
                            var thumbSrc = img.data_uri ? img.data_uri : (img.server_file ? img.server_file : '');
                            var col = $('<div>').addClass('col-4 col-sm-3 mb-3');
                            var card = $('<div>').addClass('border rounded overflow-hidden shadow-sm bg-light text-center p-1 position-relative');

                            if (thumbSrc) {
                                var imageEl = $('<img>').attr('src', thumbSrc)
                                    .addClass('img-fluid rounded cursor-pointer')
                                    .css({ 'height': '100px', 'width': '100%', 'object-fit': 'cover' })
                                    .on('click', function() {
                                        $('#modalZoomImg').attr('src', thumbSrc);
                                        $('#modalZoomTitle').text(img.file_name);
                                        $('#modalZoomMeta').text('Waktu: ' + (img.day || '') + ' ' + (img.date || '') + ' ' + (img.time || ''));
                                        $('#modalZoomDownload').attr('href', thumbSrc).attr('download', img.file_name);
                                        $('#modalZoomPhoto').modal('show');
                                    });
                                card.append(imageEl);
                            } else {
                                card.append('<div class="py-4 text-muted"><i class="fas fa-file-image fa-2x"></i></div>');
                            }

                            var caption = $('<div>').addClass('text-truncate font-weight-bold mt-1 text-dark')
                                .css('font-size', '11px')
                                .text(img.orig_name || img.file_name);
                            card.append(caption);

                            var timeText = (img.date && img.time) ? (img.date + ' ' + img.time) : '';
                            if (timeText) {
                                card.append($('<small>').addClass('text-muted d-block').css('font-size', '10px').text(timeText));
                            }

                            col.append(card);
                            $grid.append(col);
                        });

                        $('#batchResultContainer').fadeIn();
                    }

                    // Notifikasi Tunggal (Rule #1: tidak double)
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: resp.message || 'Watermark foto berhasil diproses.',
                        timer: 2500,
                        showConfirmButton: false
                    });
                } else {
                    $('#loadingContainer').hide();
                    $('#placeholderContainer').show();
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Memproses',
                        text: resp.message || 'Terjadi kendala saat memproses watermark.'
                    });
                }
            },
            error: function(xhr) {
                $('#loadingContainer').hide();
                $('#placeholderContainer').show();

                if (xhr.status === 403) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Akses Ditolak / Sesi Habis',
                        text: 'Token sesi keamanan tidak valid atau izin ditolak. Halaman akan dimuat ulang.'
                    }).then(function() {
                        window.location.reload();
                    });
                    return;
                }

                var errMsg = 'Terjadi kesalahan sistem saat memproses foto.';
                try {
                    var parsed = JSON.parse(xhr.responseText);
                    if (parsed.message) errMsg = parsed.message;
                    if (parsed.csrf_hash) $('input[name="<?= csrf_token() ?>"]').val(parsed.csrf_hash);
                } catch (ex) {}

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: errMsg
                });
            },
            complete: function() {
                isProcessing = false;
                $btn.prop('disabled', false).html(originalBtnHtml);
            }
        });
    });

    // -------------------------------------------------------------------------
    // 7. DOWNLOAD SINGLE & RESET
    // -------------------------------------------------------------------------
    $('#btnDownloadSingle').on('click', function() {
        if (!singleGeneratedUri) return;
        var a = document.createElement('a');
        a.href = singleGeneratedUri;
        a.download = 'foto-watermark-' + new Date().getTime() + '.jpg';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    });

    $('#singlePreviewImage').on('click', function() {
        if (!singleGeneratedUri) return;
        $('#modalZoomImg').attr('src', singleGeneratedUri);
        $('#modalZoomTitle').text('Foto Hasil Watermark');
        $('#modalZoomMeta').text('Resolusi kamera penuh');
        $('#modalZoomDownload').attr('href', singleGeneratedUri).attr('download', 'foto-watermark.jpg');
        $('#modalZoomPhoto').modal('show');
    });

    $('#btnResetAll, #btnResetSingle').on('click', function() {
        selectedFiles = [];
        singleGeneratedUri = null;
        currentZipDownloadUrl = null;
        $('#fileInput').val('');
        $('#fileCountBadge').removeClass('badge-primary badge-success badge-warning').addClass('badge-secondary').text('Belum ada file');
        $('#fileSelectedList').empty().hide();
        $('#singleResultContainer').hide();
        $('#batchResultContainer').hide();
        $('#loadingContainer').hide();
        $('#placeholderContainer').fadeIn();
        $('#btnResetAll').hide();
        $('#resultCountBadge').addClass('d-none').text('0');

        // Kembalikan background live simulator ke default
        $('#simulatorCanvasBg').css('background-image', "url('https://images.unsplash.com/photo-1541888946425-d0fbb186156f?q=80&w=1200&auto=format&fit=crop')");
        $('#simBackgroundInfo').text('Foto Sampel');

        $('#btnTabSimulator').click();
        goToStep(1);
    });

    // -------------------------------------------------------------------------
    // 8. LEAFLET MAP PICKER MODAL (POPUP PETA INTERAKTIF)
    // -------------------------------------------------------------------------
    var pickerMap = null;
    var pickerMarker = null;
    var currentLat = 0.507068;
    var currentLng = 101.447779;
    var currentSelectedAddress = 'Balai Pelaksanaan Prasarana Permukiman Wilayah Riau';
    var geocodeTimeout = null;

    var customPinIcon = L.divIcon({
        className: 'custom-map-pin',
        html: '<div style="transform: translate(-50%, -100%); line-height: 1;"><i class="fas fa-map-marker-alt text-danger" style="font-size: 34px; filter: drop-shadow(0 3px 5px rgba(0,0,0,0.45));"></i></div>',
        iconSize: [34, 34],
        iconAnchor: [0, 0]
    });

    function formatCoordinates(lat, lng) {
        var latRef = lat >= 0 ? 'N' : 'S';
        var lngRef = lng >= 0 ? 'E' : 'W';
        return Math.abs(lat).toFixed(6) + '° ' + latRef + ', ' + Math.abs(lng).toFixed(6) + '° ' + lngRef;
    }

    function parseExistingCoords(str) {
        if (!str || typeof str !== 'string') return null;
        var dmsMatch = str.match(/([\d\.]+)\s*°?\s*([NS])\s*,\s*([\d\.]+)\s*°?\s*([EW])/i);
        if (dmsMatch) {
            var lat = parseFloat(dmsMatch[1]) * (dmsMatch[2].toUpperCase() === 'S' ? -1 : 1);
            var lng = parseFloat(dmsMatch[3]) * (dmsMatch[4].toUpperCase() === 'W' ? -1 : 1);
            if (!isNaN(lat) && !isNaN(lng)) return { lat: lat, lng: lng };
        }
        var decMatch = str.match(/([-\d\.]+)\s*,\s*([-\d\.]+)/);
        if (decMatch) {
            var lat2 = parseFloat(decMatch[1]);
            var lng2 = parseFloat(decMatch[2]);
            if (!isNaN(lat2) && !isNaN(lng2) && Math.abs(lat2) <= 90 && Math.abs(lng2) <= 180) {
                return { lat: lat2, lng: lng2 };
            }
        }
        return null;
    }

    function initPickerMap() {
        if (pickerMap) return;

        pickerMap = L.map('mapPickerContainer', {
            zoomControl: true,
            preferCanvas: true
        }).setView([currentLat, currentLng], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(pickerMap);

        pickerMarker = L.marker([currentLat, currentLng], {
            icon: customPinIcon,
            draggable: true
        }).addTo(pickerMap);

        pickerMarker.on('dragend', function() {
            var pos = pickerMarker.getLatLng();
            currentLat = pos.lat;
            currentLng = pos.lng;
            updateCoordsDisplay(currentLat, currentLng);
            reverseGeocode(currentLat, currentLng);
        });

        pickerMap.on('click', function(e) {
            currentLat = e.latlng.lat;
            currentLng = e.latlng.lng;
            pickerMarker.setLatLng([currentLat, currentLng]);
            updateCoordsDisplay(currentLat, currentLng);
            reverseGeocode(currentLat, currentLng);
        });
    }

    function updateCoordsDisplay(lat, lng) {
        $('#selectedCoordsText').text(formatCoordinates(lat, lng));
    }

    function setMarkerLocation(lat, lng, panTo) {
        currentLat = lat;
        currentLng = lng;
        updateCoordsDisplay(lat, lng);
        if (pickerMarker) {
            pickerMarker.setLatLng([lat, lng]);
        }
        if (pickerMap && panTo) {
            pickerMap.setView([lat, lng], Math.max(pickerMap.getZoom(), 15));
        }
    }

    function reverseGeocode(lat, lng, updateInput) {
        if (geocodeTimeout) clearTimeout(geocodeTimeout);
        $('#selectedAddressText').html('<i class="fas fa-spinner fa-spin text-primary"></i> Mengambil nama tempat...');

        geocodeTimeout = setTimeout(function() {
            var url = 'https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng + '&zoom=18&addressdetails=1';
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                timeout: 5000,
                success: function(data) {
                    if (data && data.address) {
                        var a = data.address;
                        var parts = [
                            a.building || a.amenity || a.office || a.shop || a.leisure || a.tourism || '',
                            a.road || a.street || '',
                            a.suburb || a.village || a.neighbourhood || '',
                            a.city || a.town || a.county || a.state_district || '',
                            a.state || ''
                        ].filter(Boolean);

                        currentSelectedAddress = parts.length > 0 ? parts.join(', ') : (data.display_name || '');
                    } else if (data && data.display_name) {
                        currentSelectedAddress = data.display_name;
                    } else {
                        currentSelectedAddress = 'Lokasi Lapangan (' + formatCoordinates(lat, lng) + ')';
                    }
                    $('#selectedAddressText').text(currentSelectedAddress);
                    if (updateInput) {
                        $('#lokasi').val(currentSelectedAddress);
                        updateLiveSimulator();
                        updateExifSummaryDisplay();
                    }
                },
                error: function() {
                    currentSelectedAddress = $('#lokasi').val() || ('Lokasi Koordinat ' + formatCoordinates(lat, lng));
                    $('#selectedAddressText').text(currentSelectedAddress);
                    if (updateInput && (! $('#lokasi').val() || $('#lokasi').val().trim() === '')) {
                        $('#lokasi').val(currentSelectedAddress);
                        updateLiveSimulator();
                        updateExifSummaryDisplay();
                    }
                }
            });
        }, 350);
    }

    function searchMapLocation(query) {
        if (!query || query.trim() === '') return;
        var $btn = $('#btnSearchMap');
        var originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        var url = 'https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(query) + '&limit=1';
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            timeout: 6000,
            success: function(data) {
                if (data && data.length > 0) {
                    var lat = parseFloat(data[0].lat);
                    var lon = parseFloat(data[0].lon);
                    setMarkerLocation(lat, lon, true);
                    currentSelectedAddress = data[0].display_name;
                    $('#selectedAddressText').text(currentSelectedAddress);
                } else {
                    Swal.fire({
                        icon: 'info',
                        title: 'Tidak Ditemukan',
                        text: 'Tempat "' + query + '" tidak ditemukan. Coba gunakan nama jalan atau kota.',
                        timer: 2500,
                        showConfirmButton: false
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pencarian Gagal',
                    text: 'Gagal terhubung ke server peta online.',
                    timer: 2000,
                    showConfirmButton: false
                });
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalHtml);
            }
        });
    }

    // Modal Trigger
    $('#btnBukaPetaModal').on('click', function(e) {
        e.preventDefault();
        $('#modalPilihPeta').modal('show');
    });

    $('#modalPilihPeta').on('shown.bs.modal', function() {
        if (lastExifCoordinates) {
            $('#btnGpsFromExif').show();
        } else {
            $('#btnGpsFromExif').hide();
        }

        var existingCoords = parseExistingCoords($('#koordinat').val() || lastExifCoordinates);
        if (existingCoords) {
            currentLat = existingCoords.lat;
            currentLng = existingCoords.lng;
        }

        initPickerMap();

        if (pickerMap) {
            pickerMap.invalidateSize();
            setMarkerLocation(currentLat, currentLng, true);
        }

        if ($('#lokasi').val() && $('#lokasi').val().trim() !== '') {
            currentSelectedAddress = $('#lokasi').val().trim();
            $('#selectedAddressText').text(currentSelectedAddress);
        } else {
            reverseGeocode(currentLat, currentLng);
        }
    });

    $('#btnGpsFromExif').on('click', function() {
        if (!lastExifCoordinates) return;
        var parsed = parseExistingCoords(lastExifCoordinates);
        if (parsed) {
            setMarkerLocation(parsed.lat, parsed.lng, true);
            reverseGeocode(parsed.lat, parsed.lng);
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1800,
                timerProgressBar: true
            });
            Toast.fire({
                icon: 'info',
                title: 'Peta diarahkan ke koordinat EXIF foto'
            });
        }
    });

    $('#btnSearchMap').on('click', function() {
        searchMapLocation($('#mapSearchInput').val());
    });
    $('#mapSearchInput').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            searchMapLocation($(this).val());
        }
    });

    $('#btnGpsCurrentLocation').on('click', function() {
        if (!navigator.geolocation) {
            Swal.fire({ icon: 'warning', title: 'GPS Tidak Didukung', text: 'Perangkat atau browser Anda tidak mendukung fitur lokasi GPS.', timer: 2500, showConfirmButton: false });
            return;
        }
        var $btn = $(this);
        var origHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Mencari GPS...');

        navigator.geolocation.getCurrentPosition(function(pos) {
            $btn.prop('disabled', false).html(origHtml);
            setMarkerLocation(pos.coords.latitude, pos.coords.longitude, true);
            reverseGeocode(pos.coords.latitude, pos.coords.longitude);
        }, function() {
            $btn.prop('disabled', false).html(origHtml);
            Swal.fire({
                icon: 'info',
                title: 'Akses GPS Diperlukan',
                text: 'Silakan izinkan akses lokasi pada peramban untuk mendeteksi posisi Anda saat ini.',
                timer: 3000,
                showConfirmButton: false
            });
        }, { timeout: 8000, enableHighAccuracy: true });
    });

    // Quick GPS Button directly from Form
    $('#btnGpsQuickFromForm').on('click', function() {
        if (!navigator.geolocation) {
            Swal.fire({ icon: 'warning', title: 'GPS Tidak Didukung', text: 'Perangkat atau browser Anda tidak mendukung fitur lokasi GPS.', timer: 2500, showConfirmButton: false });
            return;
        }
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        navigator.geolocation.getCurrentPosition(function(pos) {
            $btn.prop('disabled', false).html('<i class="fas fa-location-arrow mr-1"></i> GPS Saya');
            var coordsStr = formatCoordinates(pos.coords.latitude, pos.coords.longitude);
            $('#koordinat').val(coordsStr);

            var url = 'https://nominatim.openstreetmap.org/reverse?format=json&lat=' + pos.coords.latitude + '&lon=' + pos.coords.longitude + '&zoom=18&addressdetails=1';
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                timeout: 5000,
                success: function(data) {
                    if (data && data.address) {
                        var a = data.address;
                        var parts = [
                            a.building || a.amenity || a.office || '',
                            a.road || a.street || '',
                            a.suburb || a.village || '',
                            a.city || a.town || a.county || '',
                            a.state || ''
                        ].filter(Boolean);
                        if (parts.length > 0) {
                            $('#lokasi').val(parts.join(', '));
                        }
                    }
                    updateLiveSimulator();
                },
                error: function() {
                    updateLiveSimulator();
                }
            });

            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });
            Toast.fire({
                icon: 'success',
                title: 'Koordinat GPS perangkat berhasil diambil!'
            });
        }, function() {
            $btn.prop('disabled', false).html('<i class="fas fa-location-arrow mr-1"></i> GPS Saya');
            Swal.fire({
                icon: 'info',
                title: 'Akses Lokasi Ditolak',
                text: 'Izinkan akses lokasi pada browser untuk mengambil koordinat.',
                timer: 2500,
                showConfirmButton: false
            });
        }, { timeout: 8000, enableHighAccuracy: true });
    });

    // Terapkan Lokasi Terpilih dari Peta
    $('#btnTerapkanLokasiPeta').on('click', function() {
        var useOnlyCoords = $('#mapFormatCoords').is(':checked');
        var formattedCoords = (currentLat !== null && currentLng !== null) ? formatCoordinates(currentLat, currentLng) : '';

        if (useOnlyCoords && formattedCoords) {
            $('#lokasi').val(formattedCoords);
        } else if (currentSelectedAddress) {
            $('#lokasi').val(currentSelectedAddress);
        } else if (formattedCoords) {
            $('#lokasi').val(formattedCoords);
        }

        if (formattedCoords) {
            $('#koordinat').val(formattedCoords);
        }

        $('#modalPilihPeta').modal('hide');
        updateLiveSimulator();

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
        Toast.fire({
            icon: 'success',
            title: useOnlyCoords ? 'Koordinat GPS diterapkan ke teks lokasi!' : 'Lokasi & koordinat peta berhasil diterapkan!'
        });
    });

    // Initial trigger to render live simulator with defaults
    updateLiveSimulator();
    updateSummaryCard();
    updateExifSummaryDisplay();
});
</script>
<?= $this->endSection(); ?>
