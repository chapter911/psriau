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
                            <span class="step-label">Logo &amp; Proses</span>
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

                        <div id="fileSelectedList" class="alert alert-light border py-2 px-3 small text-muted mb-2" style="display: none;"></div>
                        <div id="largeFileAlert" class="alert alert-warning py-2 px-3 small text-dark mb-3 shadow-xs" style="display: none;"></div>

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
                                        <i class="fas fa-undo mr-1"></i> Pulihkan Waktu EXIF (Semua)
                                    </button>
                                </div>
                            </div>

                            <!-- Baris Input Tanggal & Jam dengan Opsi Independen -->
                            <div class="row mb-2">
                                <!-- Kolom Pengaturan Tanggal -->
                                <div class="col-6">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label for="manualDate" class="small font-weight-bold text-dark mb-0" style="font-size: 11px;">
                                            <i class="far fa-calendar-alt text-primary mr-1"></i> Tanggal Foto:
                                        </label>
                                        <button type="button" class="btn btn-link btn-xs p-0 text-info d-none font-weight-bold" id="btnRestoreDateExif" title="Kembalikan tanggal asli EXIF" style="font-size: 10px;">
                                            <i class="fas fa-undo mr-1"></i>EXIF
                                        </button>
                                    </div>
                                    <input type="date" class="form-control form-control-sm" id="manualDate" name="manual_date" value="<?= date('Y-m-d') ?>">
                                    <div class="custom-control custom-switch mt-1 pt-1 bg-white px-2 py-1 rounded border">
                                        <input type="checkbox" class="custom-control-input" id="useExifDate" name="use_exif_date" value="1" checked>
                                        <label class="custom-control-label small font-weight-bold text-muted" for="useExifDate" id="labelUseExifDate" style="font-size: 10.5px; cursor: pointer;">
                                            Gunakan Tanggal EXIF
                                        </label>
                                    </div>
                                </div>

                                <!-- Kolom Pengaturan Jam -->
                                <div class="col-6">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label for="manualTime" class="small font-weight-bold text-dark mb-0" style="font-size: 11px;">
                                            <i class="far fa-clock text-warning mr-1"></i> Jam Foto:
                                        </label>
                                        <div class="d-flex align-items-center">
                                            <button type="button" class="btn btn-link btn-xs p-0 text-info mr-2 d-none font-weight-bold" id="btnRestoreTimeExif" title="Kembalikan jam asli EXIF" style="font-size: 10px;">
                                                <i class="fas fa-undo mr-1"></i>EXIF
                                            </button>
                                            <button type="button" class="btn btn-link btn-xs p-0 text-primary font-weight-bold" id="btnNow" title="Set ke jam sekarang" style="font-size: 10px;">
                                                <i class="fas fa-clock mr-1"></i>Sekarang
                                            </button>
                                        </div>
                                    </div>
                                    <input type="time" class="form-control form-control-sm" id="manualTime" name="manual_time" value="<?= date('H:i') ?>">
                                    <div class="custom-control custom-switch mt-1 pt-1 bg-white px-2 py-1 rounded border">
                                        <input type="checkbox" class="custom-control-input" id="useExifTime" name="use_exif_time" value="1" checked>
                                        <label class="custom-control-label small font-weight-bold text-muted" for="useExifTime" id="labelUseExifTime" style="font-size: 10.5px; cursor: pointer;">
                                            Gunakan Jam EXIF
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Banner Status Mode Waktu yang Dipilih -->
                            <div class="alert alert-light border py-1 px-2 small mb-2 text-muted" id="dateTimeModeStatus" style="font-size: 11px;">
                                <i class="fas fa-info-circle text-info mr-1"></i>
                                <span id="dateTimeModeStatusText">Tanggal &amp; Jam disinkronkan dari EXIF asli foto masing-masing.</span>
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
                    <!-- LANGKAH 3: LOGO KUSTOM & PROSES WATERMARK               -->
                    <!-- ======================================================= -->
                    <div class="wizard-step-pane d-none" id="wizardStepPane3">
                        <!-- 3.1 Logo Kustom Instansi (Opsional) -->
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

                        <!-- 3.2 Ringkasan Konfigurasi Sebelum Proses -->
                        <div class="card border rounded-lg p-3 mb-3 shadow-none bg-white">
                            <h6 class="font-weight-bold text-dark small mb-2"><i class="fas fa-clipboard-check text-success mr-1"></i> Ringkasan Pengaturan Watermark:</h6>
                            <div class="row small text-muted">
                                <div class="col-6 mb-1">Format: <strong class="text-dark" id="summaryTemplate">Klasik PU</strong></div>
                                <div class="col-6 mb-1">File Terpilih: <strong class="text-dark" id="summaryFiles">0 Berkas</strong></div>
                                <div class="col-12 mb-1">Mode Waktu: <strong class="text-dark" id="summaryWaktuMode">EXIF Kamera Asli</strong></div>
                                <div class="col-12 mb-1">Penyesuaian Jam: <strong class="text-dark" id="summaryOffset">+0 Jam</strong></div>
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
                        <!-- LOADING STATE & PROGRESS BAR -->
                        <div id="loadingContainer" class="text-center py-5" style="display: none;">
                            <div class="spinner-border text-primary mb-3" style="width: 3.5rem; height: 3.5rem;" role="status"></div>
                            <h6 class="font-weight-bold text-dark mb-1" id="loadingTitleText">Sedang Memproses Watermark Foto...</h6>
                            <p class="text-muted small mb-2" id="uploadProgressText">Menerapkan format watermark, ekstraksi EXIF, dan mengompilasi berkas.</p>
                            <div class="progress mx-auto mb-2" style="max-width: 380px; height: 18px; border-radius: 9px; background-color: #e9ecef;">
                                <div id="uploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary font-weight-bold" role="progressbar" style="width: 0%; font-size: 11px;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                            </div>
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
                                    <h6 class="font-weight-bold mb-0"><i class="fas fa-check-circle mr-1"></i> <span id="batchCountText">0</span> Foto Berhasil Diproses!</h6>
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
<script src="<?= esc(media_url('assets/adminlte/plugins/jszip/jszip.min.js')); ?>"></script>
<script>
$(function() {
    'use strict';

    // State Variables
    var selectedFiles = [];
    var singleGeneratedUri = null;
    var singleProcessedFileName = null;
    var currentZipDownloadUrl = null;
    var isProcessing = false;
    var currentStep = 1;

    // Base Date & Time state for Live Simulator
    var baseDate = '<?= date('Y-m-d') ?>';
    var baseTime = '<?= date('H:i') ?>';
    var lastExifDate = null;
    var lastExifTime = null;
    var lastExifCoordinates = null;

    // Raw original selected file before any client-side canvas conversion
    var rawOriginalFirstFile = null;

    // Helper cek file HEIC / HEIF
    function isHeic(f) {
        if (!f) return false;
        var name = (f.name || '').toLowerCase();
        return name.endsWith('.heic') || name.endsWith('.heif') || f.type === 'image/heic' || f.type === 'image/heif';
    }

    // Helper format ukuran file (Bytes -> KB / MB / GB)
    function formatFileSize(bytes) {
        if (!bytes || bytes <= 0) return '0 B';
        var units = ['B', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(1024));
        return (bytes / Math.pow(1024, i)).toFixed(i >= 2 ? 1 : 0) + ' ' + units[i];
    }

    // Patch exifr HEIC parser agar mengenali box ftyp iPhone modern (panjang box > 50 & brand compatible)
    if (typeof exifr !== 'undefined' && exifr.fileParsers && exifr.fileParsers.has('heic')) {
        try {
            var heicParser = exifr.fileParsers.get('heic');
            var origHeicCanHandle = heicParser.canHandle;
            heicParser.canHandle = function(buffer, header) {
                if (header === 0) {
                    try {
                        var box = buffer.getString(4, 4);
                        if (box === 'ftyp') {
                            var major = buffer.getString(8, 4);
                            var brands = ['heic', 'heix', 'hevc', 'heim', 'heis', 'hevm', 'hevs', 'mif1', 'msf1', 'miaf'];
                            if (brands.indexOf(major) !== -1) return true;
                            var len = buffer.getUint32 ? buffer.getUint32(0) : buffer.getUint16(2);
                            var maxCheck = Math.min(len || 256, 256);
                            for (var off = 16; off < maxCheck; off += 4) {
                                var brand = buffer.getString(off, 4);
                                if (brands.indexOf(brand) !== -1) return true;
                            }
                        }
                    } catch (e) {}
                }
                return origHeicCanHandle.call(this, buffer, header);
            };
        } catch (e) {
            console.warn('Gagal patching exifr HEIC parser:', e);
        }
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
        if (!file) return;

        // Jika library exifr belum termuat atau file bukan format biasa, langsung alihkan ke server
        if (typeof exifr === 'undefined') {
            extractExifPreview(file);
            return;
        }

        exifr.parse(file, {
            gps: true,
            tiff: true,
            exif: true
        }).then(function(exif) {
            var hasExif = false;
            if (exif) {
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
                        $('#useExifDate').prop('checked', true);
                        $('#useExifTime').prop('checked', true);
                        $('#btnRestoreDateExif').removeClass('d-none').show();
                        $('#btnRestoreTimeExif').removeClass('d-none').show();
                        $('#btnTimeFromExif').removeClass('d-none').show();
                        updateDateTimeStatusText();
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
            }

            if (hasExif) {
                var alertHtml = '<i class="fas fa-check-circle text-success mr-1"></i> <strong>EXIF Foto Terdeteksi:</strong> ';
                if (lastExifDate) alertHtml += 'Waktu: ' + lastExifDate + ' ' + (lastExifTime || '') + ' ';
                if (lastExifCoordinates) alertHtml += '| GPS: <code>' + lastExifCoordinates + '</code> ';
                alertHtml += '<span class="text-success font-weight-bold ml-1">(Lokasi &amp; waktu otomatis diisi)</span>';
                $('#exifStatusAlert').removeClass('alert-info alert-warning alert-danger').addClass('alert-success').html(alertHtml).show();
                updateLiveSimulator();
                updateExifSummaryDisplay();
            } else {
                // Client-side tidak menemukan tag GPS/Tanggal, delegasikan ke ekstraksi server yang lebih mendalam
                extractExifPreview(file);
            }
        }).catch(function(err) {
            console.info('Client-side EXIF inspection dilewati (dialihkan ke server fallback):', err && err.message ? err.message : err);
            // Otomatis lakukan server-side inspection menggunakan file asli
            extractExifPreview(file);
        });
    }

    function handleSelectedFiles(filesList) {
        var rawFiles = Array.from(filesList);
        var heicFiles = rawFiles.filter(isHeic);

        // Simpan file foto mentah asli pertama sebelum konversi kanvas browser
        rawOriginalFirstFile = rawFiles.find(function(f) {
            return !f.name.toLowerCase().endsWith('.zip');
        }) || null;

        // Ekstraksi EXIF langsung dari file mentah pertama
        if (rawOriginalFirstFile) {
            extractExifClientSide(rawOriginalFirstFile);
        } else {
            // Jika file yang dipilih adalah berkas arsip .ZIP, ekstrak sampel foto pertama via JSZip untuk EXIF & preview simulator
            var zipFile = rawFiles.find(function(f) {
                return f.name.toLowerCase().endsWith('.zip');
            });

            if (zipFile && typeof JSZip !== 'undefined') {
                if (zipFile.size > 40 * 1024 * 1024) {
                    console.info('Berkas ZIP berukuran besar (' + formatFileSize(zipFile.size) + '). Pembacaan sampel di browser dilewati untuk performa.');
                    $('#simBackgroundInfo').text('Arsip ZIP (' + formatFileSize(zipFile.size) + ') - Diproses di Server');
                } else {
                    JSZip.loadAsync(zipFile).then(function(zip) {
                    var firstImageEntry = null;
                    zip.forEach(function(relativePath, zipEntry) {
                        if (firstImageEntry) return;
                        if (zipEntry.dir) return;
                        if (relativePath.indexOf('__MACOSX') !== -1 || zipEntry.name.indexOf('._') === 0) return;
                        if (/\.(jpe?g|png|webp|heic|heif)$/i.test(zipEntry.name)) {
                            firstImageEntry = zipEntry;
                        }
                    });

                    if (firstImageEntry) {
                        firstImageEntry.async('blob').then(function(blob) {
                            var sampleFile = new File([blob], firstImageEntry.name, {
                                type: firstImageEntry.name.toLowerCase().endsWith('.png') ? 'image/png' : 'image/jpeg'
                            });
                            rawOriginalFirstFile = sampleFile;
                            extractExifClientSide(sampleFile);

                            // Pasang sampel foto dari ZIP ke Simulator Background
                            var sampleUrl = URL.createObjectURL(blob);
                            $('#simulatorCanvasBg').css('background-image', 'url(' + sampleUrl + ')');
                            $('#simBackgroundInfo').text('Sampel ZIP (' + firstImageEntry.name + ')');
                        }).catch(function(err) {
                            console.info('Gagal membaca sampel gambar dari ZIP:', err);
                        });
                    }
                }).catch(function(err) {
                    console.info('Gagal membaca struktur ZIP client-side:', err);
                });
            }
        }
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

        var totalSizeBytes = 0;
        selectedFiles.forEach(function(f) {
            totalSizeBytes += (f.size || 0);
            if (f.name.toLowerCase().endsWith('.zip')) {
                hasZip = true;
            }
            if (isHeic(f)) {
                hasRawHeic = true;
            }
            fileNames.push(f.name);
        });

        var sizeFormatted = formatFileSize(totalSizeBytes);

        if (total === 1 && hasZip) {
            $('#fileCountBadge').removeClass('badge-secondary badge-warning badge-primary badge-success').addClass('badge-warning').html('<i class="fas fa-file-archive mr-1"></i> 1 Arsip ZIP (' + sizeFormatted + ')');
            $('#summaryFiles').text('1 File ZIP (' + sizeFormatted + ')');
        } else if (total === 1 && hasRawHeic) {
            $('#fileCountBadge').removeClass('badge-secondary badge-warning badge-success').addClass('badge-info').html('<i class="fas fa-apple-alt mr-1"></i> 1 Foto HEIC (' + sizeFormatted + ')');
            $('#summaryFiles').text('1 Foto HEIC (' + sizeFormatted + ')');
        } else if (total === 1) {
            $('#fileCountBadge').removeClass('badge-secondary badge-warning badge-success').addClass('badge-primary').html('<i class="fas fa-image mr-1"></i> 1 Foto (' + sizeFormatted + ')');
            $('#summaryFiles').text('1 Foto (' + sizeFormatted + ')');
        } else {
            $('#fileCountBadge').removeClass('badge-secondary badge-warning badge-primary').addClass('badge-success').html('<i class="fas fa-images mr-1"></i> ' + total + ' Foto (' + sizeFormatted + ')');
            $('#summaryFiles').text(total + ' Foto (' + sizeFormatted + ')');
        }

        var previewText = fileNames.slice(0, 3).join(', ');
        if (total > 3) {
            previewText += ' dan ' + (total - 3) + ' file lainnya';
        }
        previewText += ' <span class="badge badge-secondary ml-1">' + sizeFormatted + '</span>';
        $('#fileSelectedList').html('<strong><i class="fas fa-check-circle text-success mr-1"></i> File Siap:</strong> ' + previewText).fadeIn();

        // Peringatan jika total ukuran berkas di atas 100 MB
        if (totalSizeBytes > 100 * 1024 * 1024) {
            $('#largeFileAlert').html(
                '<div class="d-flex align-items-start">' +
                '<i class="fas fa-exclamation-triangle text-warning fa-lg mr-2 mt-1"></i>' +
                '<div>' +
                '<strong>Perhatian Ukuran Berkas Sangat Besar (' + sizeFormatted + '):</strong> ' +
                'Server hosting umumnya membatasi ukuran unggahan per request (100 MB – 250 MB). ' +
                'Mengunggah berkas sebesar <strong>' + sizeFormatted + '</strong> rentan memicu pemutusan koneksi (<em>ERR_HTTP2_PROTOCOL_ERROR</em> / Timeout).<br>' +
                '<strong>Saran Praktis:</strong> Disarankan membagi foto menjadi beberapa file ZIP / batch lebih kecil (misalnya 20–30 foto per batch, sekitar 50–100 MB) agar pemrosesan berjalan cepat dan lancar.' +
                '</div>' +
                '</div>'
            ).fadeIn();
        } else {
            $('#largeFileAlert').hide();
        }

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

        // Jika EXIF belum terbaca dan ada file mentah asli, panggil fallback server
        if (rawOriginalFirstFile && !lastExifDate && !lastExifCoordinates) {
            extractExifPreview(rawOriginalFirstFile);
        }

        updateSummaryCard();
        updateExifSummaryDisplay();
    }

    function extractExifPreview(file) {
        if (!file) return;
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
                        $('#simBackgroundInfo').text('Foto (' + file.name + ')');
                    }
                    if (resp.date) {
                        lastExifDate = resp.date;
                        baseDate = resp.date;
                        $('#manualDate').val(resp.date);
                        $('#useExifDate').prop('checked', true);
                        $('#btnRestoreDateExif').removeClass('d-none').show();
                        $('#btnTimeFromExif').removeClass('d-none').show();
                    }
                    if (resp.time) {
                        lastExifTime = resp.time;
                        baseTime = resp.time;
                        $('#manualTime').val(resp.time);
                        $('#useExifTime').prop('checked', true);
                        $('#btnRestoreTimeExif').removeClass('d-none').show();
                        $('#btnTimeFromExif').removeClass('d-none').show();
                    }
                    updateDateTimeStatusText();
                    if (resp.lat != null && resp.lng != null) {
                        var lat = parseFloat(resp.lat);
                        var lng = parseFloat(resp.lng);
                        var coordsStr = resp.coordinates || (lat.toFixed(6) + ', ' + lng.toFixed(6));
                        lastExifCoordinates = coordsStr;
                        $('#koordinat').val(coordsStr);
                        $('#btnGpsFromExif').removeClass('d-none').show();
                        setMarkerLocation(lat, lng, true);
                        reverseGeocode(lat, lng, true);
                    } else if (resp.coordinates) {
                        lastExifCoordinates = resp.coordinates;
                        $('#koordinat').val(resp.coordinates);
                        $('#btnGpsFromExif').removeClass('d-none').show();
                        var parts = resp.coordinates.split(',');
                        if (parts.length === 2) {
                            var pLat = parseFloat(parts[0]);
                            var pLng = parseFloat(parts[1]);
                            if (!isNaN(pLat) && !isNaN(pLng)) {
                                setMarkerLocation(pLat, pLng, true);
                                reverseGeocode(pLat, pLng, true);
                            }
                        }
                    }
                    if (resp.description && (! $('#catatan').val() || $('#catatan').val().trim() === '')) {
                        $('#catatan').val(resp.description);
                    }

                    if (resp.has_exif) {
                        var statusHtml = '<i class="fas fa-check-circle text-success mr-1"></i> <strong>EXIF Foto Terdeteksi (Server):</strong> ';
                        if (lastExifDate) statusHtml += 'Waktu: ' + lastExifDate + ' ' + (lastExifTime || '') + ' ';
                        if (lastExifCoordinates) statusHtml += '| GPS: <code>' + lastExifCoordinates + '</code> ';
                        statusHtml += '<span class="text-success font-weight-bold ml-1">(Lokasi &amp; waktu otomatis diisi)</span>';
                        $('#exifStatusAlert').removeClass('alert-info alert-warning alert-danger').addClass('alert-success').html(statusHtml).show();
                    } else if (!lastExifDate && !lastExifCoordinates) {
                        $('#exifStatusAlert').removeClass('alert-success alert-danger').addClass('alert-info').html('<i class="fas fa-info-circle mr-1"></i> Foto tidak memiliki metadata EXIF tanggal/GPS. Waktu otomatis diset saat ini, dan Anda dapat memilih lokasi pada peta di bawah.').show();
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
        var hh = String(now.getHours()).padStart(2, '0');
        var min = String(now.getMinutes()).padStart(2, '0');
        baseTime = hh + ':' + min;
        $('#manualTime').val(baseTime);
        $('#useExifTime').prop('checked', false);
        updateDateTimeStatusText();
        updateLiveSimulator();
        updateExifSummaryDisplay();
        updateSummaryCard();

        const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1800 });
        Toast.fire({ icon: 'info', title: 'Jam disetel ke waktu sekarang (Ganti Jam Saja)!' });
    });

    $('#btnRestoreDateExif').on('click', function() {
        if (lastExifDate) {
            baseDate = lastExifDate;
            $('#manualDate').val(lastExifDate);
            $('#useExifDate').prop('checked', true);
            updateDateTimeStatusText();
            updateLiveSimulator();
            updateExifSummaryDisplay();
            updateSummaryCard();

            const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1800 });
            Toast.fire({ icon: 'info', title: 'Tanggal dipulihkan ke tanggal asli EXIF foto!' });
        }
    });

    $('#btnRestoreTimeExif').on('click', function() {
        if (lastExifTime) {
            baseTime = lastExifTime;
            $('#manualTime').val(lastExifTime);
            $('#useExifTime').prop('checked', true);
            updateDateTimeStatusText();
            updateLiveSimulator();
            updateExifSummaryDisplay();
            updateSummaryCard();

            const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1800 });
            Toast.fire({ icon: 'info', title: 'Jam dipulihkan ke jam asli EXIF foto!' });
        }
    });

    $('#btnTimeFromExif').on('click', function() {
        if (lastExifDate) {
            baseDate = lastExifDate;
            $('#manualDate').val(lastExifDate);
            $('#useExifDate').prop('checked', true);
        }
        if (lastExifTime) {
            baseTime = lastExifTime;
            $('#manualTime').val(lastExifTime);
            $('#useExifTime').prop('checked', true);
        }
        $('#offsetHours').val(0);
        $('#offsetMinutes').val(0);
        updateOffsetBadge(0, 0);
        $('input[name="quick_offset"][value="0"]').prop('checked', true).parent().addClass('active').siblings().removeClass('active');
        updateDateTimeStatusText();
        updateLiveSimulator();
        updateExifSummaryDisplay();
        updateSummaryCard();

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

    // Perubahan input tanggal foto mandiri (ganti tanggal saja)
    $('#manualDate').on('input change', function() {
        $('#useExifDate').prop('checked', false);
        baseDate = $(this).val() || '<?= date('Y-m-d') ?>';
        updateDateTimeStatusText();
        updateLiveSimulator();
        updateExifSummaryDisplay();
        updateSummaryCard();
    });

    // Perubahan input jam foto mandiri (ganti jam saja)
    $('#manualTime').on('input change', function() {
        $('#useExifTime').prop('checked', false);
        baseTime = $(this).val() || '<?= date('H:i') ?>';
        updateDateTimeStatusText();
        updateLiveSimulator();
        updateExifSummaryDisplay();
        updateSummaryCard();
    });

    // Saklar independen Tanggal & Jam EXIF
    $('#useExifDate, #useExifTime').on('change', function() {
        if ($('#useExifDate').is(':checked') && lastExifDate) {
            $('#manualDate').val(lastExifDate);
            baseDate = lastExifDate;
        }
        if ($('#useExifTime').is(':checked') && lastExifTime) {
            $('#manualTime').val(lastExifTime);
            baseTime = lastExifTime;
        }
        updateDateTimeStatusText();
        updateLiveSimulator();
        updateExifSummaryDisplay();
        updateSummaryCard();
    });

    function updateDateTimeStatusText() {
        var isDateExif = $('#useExifDate').is(':checked');
        var isTimeExif = $('#useExifTime').is(':checked');
        var dVal = $('#manualDate').val() || '';
        var tVal = $('#manualTime').val() || '';

        var $box = $('#dateTimeModeStatus');
        var $txt = $('#dateTimeModeStatusText');

        if (isDateExif && isTimeExif) {
            $box.removeClass('alert-warning alert-primary alert-danger').addClass('alert-light');
            $txt.html('<i class="fas fa-magic text-success mr-1"></i> <strong>EXIF Asli:</strong> Tanggal &amp; jam mengikuti data kamera masing-masing foto (+ offset jika diatur).');
            $('#labelUseExifDate').text('Gunakan Tanggal EXIF').removeClass('text-primary text-danger').addClass('text-muted');
            $('#labelUseExifTime').text('Gunakan Jam EXIF').removeClass('text-warning text-danger').addClass('text-muted');
        } else if (!isDateExif && isTimeExif) {
            $box.removeClass('alert-light alert-warning alert-danger').addClass('alert-primary');
            $txt.html('<i class="fas fa-calendar-day text-primary mr-1"></i> <strong>Ganti Tanggal Saja:</strong> Semua foto diubah ke tanggal <strong>' + (dVal || 'manual') + '</strong>, jam tetap mempertahankan jam asli kamera per foto.');
            $('#labelUseExifDate').text('Tanggal Manual').removeClass('text-muted text-danger').addClass('text-primary font-weight-bold');
            $('#labelUseExifTime').text('Gunakan Jam EXIF').removeClass('text-warning text-danger').addClass('text-muted');
        } else if (isDateExif && !isTimeExif) {
            $box.removeClass('alert-light alert-primary alert-danger').addClass('alert-warning');
            $txt.html('<i class="fas fa-clock text-warning mr-1"></i> <strong>Ganti Jam Saja:</strong> Semua foto diubah ke jam <strong>' + (tVal || 'manual') + ' WIB</strong>, tanggal tetap mempertahankan tanggal asli kamera per foto.');
            $('#labelUseExifDate').text('Gunakan Tanggal EXIF').removeClass('text-primary text-danger').addClass('text-muted');
            $('#labelUseExifTime').text('Jam Manual').removeClass('text-muted text-danger').addClass('text-warning font-weight-bold');
        } else {
            $box.removeClass('alert-light alert-primary alert-warning').addClass('alert-danger');
            $txt.html('<i class="fas fa-edit text-danger mr-1"></i> <strong>Ganti Tanggal &amp; Jam:</strong> Semua foto disetel manual ke tanggal <strong>' + (dVal || 'manual') + '</strong> jam <strong>' + (tVal || 'manual') + '</strong>.');
            $('#labelUseExifDate').text('Tanggal Manual').removeClass('text-muted text-primary').addClass('text-danger font-weight-bold');
            $('#labelUseExifTime').text('Jam Manual').removeClass('text-muted text-warning').addClass('text-danger font-weight-bold');
        }
    }

    $('#lokasi, #koordinat, #catatan, #useExifLocation, #updateExif').on('input change', function() {
        updateLiveSimulator();
        updateExifSummaryDisplay();
        updateSummaryCard();
    });

    var daysIndo = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    var monthsIndo = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    function updateLiveSimulator() {
        var isDateExif = $('#useExifDate').is(':checked') && lastExifDate;
        var isTimeExif = $('#useExifTime').is(':checked') && lastExifTime;

        // Hitung effective date & time
        var curDate = (!isDateExif) ? ($('#manualDate').val() || baseDate) : lastExifDate;
        var curTime = (!isTimeExif) ? ($('#manualTime').val() || baseTime) : lastExifTime;
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
        var isDateExif = $('#useExifDate').is(':checked');
        var isTimeExif = $('#useExifTime').is(':checked');
        var summaryWaktu = 'EXIF Kamera Asli';
        if (!isDateExif && isTimeExif) {
            summaryWaktu = 'Ganti Tanggal Saja (' + ($('#manualDate').val() || '') + ')';
        } else if (isDateExif && !isTimeExif) {
            summaryWaktu = 'Ganti Jam Saja (' + ($('#manualTime').val() || '') + ')';
        } else if (!isDateExif && !isTimeExif) {
            summaryWaktu = 'Tanggal & Jam Manual';
        }
        $('#summaryWaktuMode').text(summaryWaktu);
        updateOffsetBadge(h, m);
    }

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
        selectedFiles.forEach(function(f) {
            totalSize += (f.size || 0);
        });

        if (selectedFiles.length === 1 && selectedFiles[0].name.toLowerCase().endsWith('.zip')) {
            formData.append('zip_file', selectedFiles[0]);
        } else if (selectedFiles.length === 1) {
            formData.append('foto', selectedFiles[0]);
        } else {
            for (var i = 0; i < selectedFiles.length; i++) {
                formData.append('fotos[]', selectedFiles[i]);
            }
        }

        // Form Fields
        formData.append('template', $('input[name="template"]:checked').val() || 'classic');
        formData.append('use_exif_date', $('#useExifDate').is(':checked') ? '1' : '0');
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


        // Logo
        var logoInput = document.getElementById('logo');
        if (logoInput && logoInput.files && logoInput.files.length > 0) {
            formData.append('logo', logoInput.files[0]);
        }

        formData.append('<?= csrf_token() ?>', $('input[name="<?= csrf_token() ?>"]').val());

        // Beralih ke View Hasil & Tampilkan Loading
        // Konfirmasi peringatan jika ukuran berkas sangat besar (> 250 MB)
        if (totalSize > 250 * 1024 * 1024) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian: File Sangat Besar',
                html: '<div class="text-left">' +
                      '<p>Total ukuran berkas yang akan diunggah adalah <strong class="text-danger">' + formatFileSize(totalSize) + '</strong>.</p>' +
                      '<p class="small text-muted mb-2">Mengunggah berkas sebesar ini dalam satu permintaan HTTP sangat rentan terputus oleh web server hosting (<code>ERR_HTTP2_PROTOCOL_ERROR</code> / Timeout) jika koneksi internet mengalami fluktuasi atau melampaui batas server.</p>' +
                      '<div class="alert alert-info py-2 px-3 small mb-0">' +
                      '<strong>Saran Terbaik:</strong> Bagi foto menjadi 2 atau 3 file ZIP yang lebih kecil (misal per 20–30 foto). Atau lanjutkan jika server hosting Anda telah dikonfigurasi <code>upload_max_filesize 512M</code>.' +
                      '</div>' +
                      '</div>',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-upload mr-1"></i> Tetap Coba Unggah (' + formatFileSize(totalSize) + ')',
                cancelButtonText: '<i class="fas fa-times mr-1"></i> Batal &amp; Bagi Berkas',
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d'
            }).then(function(result) {
                if (result.isConfirmed) {
                    executeUploadProses(formData, totalSize);
                }
            });
            return;
        }

        executeUploadProses(formData, totalSize);
    });

    function executeUploadProses(formData, totalSize) {
        // Beralih ke View Hasil & Tampilkan Loading
        isProcessing = true;
        switchToResultView();
        var $btn = $('#btnProses');
        var originalBtnHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Sedang Memproses Watermark...');

        $('#placeholderContainer').hide();
        $('#singleResultContainer').hide();
        $('#batchResultContainer').hide();
        $('#loadingTitleText').text('Sedang Mengunggah & Memproses Watermark...');
        $('#uploadProgressBar').css('width', '0%').text('0%').removeClass('bg-success').addClass('bg-primary');
        $('#uploadProgressText').text('Menyiapkan koneksi unggahan...');
        $('#loadingContainer').fadeIn(150);

        $.ajax({
            url: '<?= site_url('/admin/dokumentasi/watermark-foto/proses') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                if (xhr.upload) {
                    xhr.upload.addEventListener('progress', function(evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = Math.min(100, Math.round((evt.loaded / evt.total) * 100));
                            var loadedFormatted = formatFileSize(evt.loaded);
                            var totalFormatted = formatFileSize(evt.total);
                            $('#uploadProgressBar').css('width', percentComplete + '%').text(percentComplete + '%');

                            if (percentComplete < 100) {
                                $('#loadingTitleText').text('Mengunggah Berkas ke Server (' + percentComplete + '%)...');
                                $('#uploadProgressText').html('<i class="fas fa-arrow-up text-primary mr-1"></i> Mengunggah: <strong>' + percentComplete + '%</strong> (' + loadedFormatted + ' dari ' + totalFormatted + ')');
                            } else {
                                $('#loadingTitleText').text('Sedang Memproses Watermark & EXIF di Server...');
                                $('#uploadProgressBar').removeClass('bg-primary').addClass('bg-success');
                                $('#uploadProgressText').html('<i class="fas fa-cog fa-spin text-success mr-1"></i> Berkas 100% selesai diunggah. Server sedang mengekstrak, merender watermark, dan menyematkan metadata EXIF...');
                            }
                        }
                    }, false);
                }
                return xhr;
            },
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
                        if (resp.images && resp.images[0] && resp.images[0].file_name) {
                            singleProcessedFileName = resp.images[0].file_name;
                        } else if (selectedFiles && selectedFiles[0]) {
                            singleProcessedFileName = selectedFiles[0].name.replace(/\.(heic|heif)$/i, '.jpg');
                        }
                        if (singleProcessedFileName) {
                            singleProcessedFileName = singleProcessedFileName.replace(/^watermark[_\-\s]*/i, '');
                        }
                        $('#singlePreviewImage').attr('src', resp.image_data);
                        $('#singleResultContainer').fadeIn();
                    } else if (resp.mode === 'batch' || resp.images) {
                        $('#batchCountText').text(resp.total_processed);

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
                            var thumbSrc = img.data_uri || '';
                            var col = $('<div>').addClass('col-4 col-sm-3 mb-3');
                            var card = $('<div>').addClass('border rounded overflow-hidden shadow-sm bg-light text-center p-1 position-relative');

                            if (thumbSrc) {
                                var imageEl = $('<img>').attr('src', thumbSrc)
                                    .attr('alt', img.orig_name || img.file_name)
                                    .attr('loading', 'lazy')
                                    .addClass('img-fluid rounded cursor-pointer')
                                    .css({ 'height': '110px', 'width': '100%', 'object-fit': 'cover', 'display': 'block' })
                                    .on('click', function() {
                                        var cleanTitle = (img.orig_name || img.file_name).replace(/^watermark[_\-\s]*/i, '');
                                        $('#modalZoomImg').attr('src', thumbSrc);
                                        $('#modalZoomTitle').text(cleanTitle);
                                        var metaText = 'Waktu: ' + (img.day || '') + ' ' + (img.date || '') + ' ' + (img.time || '');
                                        if (img.sub_dir) {
                                            metaText += ' | Folder: ' + img.sub_dir;
                                        }
                                        $('#modalZoomMeta').text(metaText);
                                        var downloadUrl = img.download_url || thumbSrc;
                                        $('#modalZoomDownload').attr('href', downloadUrl).attr('download', cleanTitle);
                                        if (img.view_url) {
                                            var hiRes = new Image();
                                            hiRes.onload = function() {
                                                $('#modalZoomImg').attr('src', hiRes.src);
                                            };
                                            hiRes.src = img.view_url;
                                        }
                                        $('#modalZoomPhoto').modal('show');
                                    });
                                card.append(imageEl);
                            } else {
                                card.append('<div class="py-4 text-muted"><i class="fas fa-file-image fa-2x"></i></div>');
                            }

                            if (img.sub_dir) {
                                var folderBadge = $('<div>').addClass('badge badge-light border text-truncate text-secondary w-100 mt-1')
                                    .css({ 'font-size': '9.5px', 'max-width': '100%', 'padding': '2px 4px' })
                                    .attr('title', 'Folder: ' + img.sub_dir)
                                    .html('<i class="fas fa-folder text-warning mr-1"></i>' + img.sub_dir);
                                card.append(folderBadge);
                            }

                            var cleanDisplayName = (img.orig_name || img.file_name).replace(/^watermark[_\-\s]*/i, '');
                            var caption = $('<div>').addClass('text-truncate font-weight-bold mt-1 text-dark')
                                .css('font-size', '11px')
                                .attr('title', cleanDisplayName)
                                .text(cleanDisplayName);
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
            error: function(xhr, textStatus) {
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

                var serverMsg = null;
                try {
                    var parsed = JSON.parse(xhr.responseText);
                    if (parsed && parsed.message) serverMsg = parsed.message;
                    if (parsed && parsed.csrf_hash) $('input[name="<?= csrf_token() ?>"]').val(parsed.csrf_hash);
                } catch (ex) {}

                // 1. Jika ada balasan JSON eksplisit dari server (misal validasi 422, limit 413, disk space 507, atau exception 500)
                if (serverMsg) {
                    Swal.fire({
                        icon: 'error',
                        title: (xhr.status === 413 || xhr.status === 507) ? 'Peringatan Server' : 'Gagal Memproses',
                        text: serverMsg
                    });
                    return;
                }

                // 2. Penanganan Khusus untuk Kasus Unggahan Terputus (xhr.status === 0 / connection reset / timeout)
                if (xhr.status === 0 || xhr.status === 413 || textStatus === 'timeout') {
                    var sizeInfo = formatFileSize(totalSize);
                    Swal.fire({
                        icon: 'error',
                        title: 'Koneksi Upload Terputus',
                        html: '<div class="text-left">' +
                              '<p class="mb-2">Proses unggah berkas (<strong class="text-danger">' + sizeInfo + '</strong>) terputus oleh web server (<em>ERR_HTTP2_PROTOCOL_ERROR</em> / Connection Reset / Timeout).</p>' +
                              '<div class="alert alert-warning py-2 px-3 small mb-2">' +
                              '<strong>Kemungkinan Penyebab:</strong><br>' +
                              '1. <strong>Kapasitas Hard Disk Server/Lokal Penuh:</strong> Jika ruang disk lokal atau server hampir habis (&lt; 1 GB tersisa), sistem operasi otomatis memutus koneksi saat mengekstrak berkas besar.<br>' +
                              '2. <strong>Batas Ukuran Upload Web Server:</strong> Ukuran file melebihi batas request server hosting (standar PHP / LiteSpeed adalah 100 MB – 250 MB).<br>' +
                              '3. <strong>Koneksi Timeout:</strong> Waktu transfer melebihi batas timeout server.' +
                              '</div>' +
                              '<div class="alert alert-info py-2 px-3 small mb-0">' +
                              '<strong>Solusi yang Sangat Disarankan:</strong><br>' +
                              '1. <strong>Bagi foto menjadi batch kecil:</strong> Pecah berkas ZIP atau pilih foto dalam kelompok 20–30 foto per unggahan (sekitar 50–100 MB).<br>' +
                              '2. <strong>Periksa &amp; Kosongkan Hard Disk:</strong> Kosongkan file sampah (Trash) atau berkas sementara pada komputer Anda agar tersisa minimal 3–5 GB.<br>' +
                              '3. <strong>Konfigurasi cPanel:</strong> Jika di hosting cPanel, buka <em>MultiPHP INI Editor</em> lalu naikkan <code>post_max_size</code> dan <code>upload_max_filesize</code> menjadi 512M.' +
                              '</div>' +
                              '</div>',
                        confirmButtonText: '<i class="fas fa-check mr-1"></i> Mengerti'
                    });
                    return;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Terjadi kesalahan sistem saat memproses foto (Status HTTP: ' + xhr.status + ').'
                });
            },
            complete: function() {
                isProcessing = false;
                $btn.prop('disabled', false).html(originalBtnHtml);
            }
        });
    }

    // -------------------------------------------------------------------------
    // 7. DOWNLOAD SINGLE & RESET
    // -------------------------------------------------------------------------
    $('#btnDownloadSingle').on('click', function() {
        if (!singleGeneratedUri) return;
        var a = document.createElement('a');
        a.href = singleGeneratedUri;
        var downloadName = singleProcessedFileName || ((selectedFiles && selectedFiles.length === 1) ? selectedFiles[0].name.replace(/\.(heic|heif)$/i, '.jpg') : ('foto_' + new Date().getTime() + '.jpg'));
        downloadName = downloadName.replace(/^watermark[_\-\s]*/i, '');
        a.download = downloadName;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    });

    $('#singlePreviewImage').on('click', function() {
        if (!singleGeneratedUri) return;
        var downloadName = singleProcessedFileName || ((selectedFiles && selectedFiles.length === 1) ? selectedFiles[0].name.replace(/\.(heic|heif)$/i, '.jpg') : 'foto.jpg');
        downloadName = downloadName.replace(/^watermark[_\-\s]*/i, '');
        $('#modalZoomImg').attr('src', singleGeneratedUri);
        $('#modalZoomTitle').text(downloadName);
        $('#modalZoomMeta').text('Resolusi kamera penuh');
        $('#modalZoomDownload').attr('href', singleGeneratedUri).attr('download', downloadName);
        $('#modalZoomPhoto').modal('show');
    });

    $('#modalZoomPhoto').on('hidden.bs.modal', function () {
        $('#modalZoomImg').attr('src', '');
    });

    $('#btnResetAll, #btnResetSingle').on('click', function() {
        selectedFiles = [];
        singleGeneratedUri = null;
        singleProcessedFileName = null;
        currentZipDownloadUrl = null;
        $('#useExifDate').prop('checked', true);
        $('#useExifTime').prop('checked', true);
        $('#btnRestoreDateExif').addClass('d-none').hide();
        $('#btnRestoreTimeExif').addClass('d-none').hide();
        $('#btnTimeFromExif').addClass('d-none').hide();
        updateDateTimeStatusText();
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
