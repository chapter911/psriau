<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<link rel="stylesheet" href="<?= esc(media_url('assets/leaflet/leaflet.css')); ?>">
<div class="container-fluid">
    <!-- Header Page -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 font-weight-bold">
                <i class="fas fa-layer-group text-primary mr-2"></i><?= esc($title ?? 'Organisir & Pengelompokan Foto GPS'); ?>
            </h1>
            <p class="text-muted small mb-0">
                <?= esc($subtitle ?? 'Kelompokkan foto kegiatan lapangan secara otomatis ke dalam sub-folder lokasi berdasarkan GPS kamera asli (100% Lossless, Tanpa Watermark).'); ?>
            </p>
        </div>
        <ol class="breadcrumb bg-transparent p-0 mb-0 mt-2 mt-sm-0 small">
            <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard'); ?>">Dashboard</a></li>
            <li class="breadcrumb-item">Dokumentasi</li>
            <li class="breadcrumb-item active" aria-current="page">Organisir Foto GPS</li>
        </ol>
    </div>

    <!-- Main Row -->
    <div class="row">
        <!-- Kolom Kiri: Formulir Unggah & Pengaturan Radius -->
        <div class="col-lg-5 col-xl-4 mb-4">
            <div class="card shadow-sm border-0 rounded-lg">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom">
                    <h6 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-folder-tree text-info mr-2"></i>Unggah &amp; Konfigurasi
                    </h6>
                    <span class="badge badge-primary badge-pill" id="fileCountBadge">Belum ada file</span>
                </div>
                <div class="card-body">
                    <form id="organisirForm" enctype="multipart/form-data">
                        <?= csrf_field(); ?>

                        <!-- Dropzone Area -->
                        <div class="dropzone-area p-4 text-center border rounded-lg bg-light mb-3 position-relative" id="dropzoneBox" style="border: 2px dashed #0A66C2 !important; cursor: pointer; transition: all 0.2s ease;">
                            <div class="dropzone-icon mb-2">
                                <i class="fas fa-cloud-upload-alt fa-3x text-primary"></i>
                            </div>
                            <h6 class="font-weight-bold mb-1 text-dark" style="font-size: 0.95rem;">Tarik &amp; Lepas Foto atau Folder ke Sini</h6>
                            <p class="text-muted small mb-3" style="font-size: 11.5px;">
                                Format: <strong>JPG, PNG, WebP, HEIC/HEIF</strong>, multi-foto, atau <strong>pilih satu folder langsung</strong>
                            </p>
                            <div class="d-flex justify-content-center align-items-center flex-wrap">
                                <button type="button" class="btn btn-outline-primary btn-sm px-3 shadow-xs m-1" onclick="$('#fileInput').click()">
                                    <i class="fas fa-images mr-1"></i> Pilih Berkas Foto
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm px-3 shadow-xs m-1" onclick="$('#folderInput').click()">
                                    <i class="fas fa-folder-open mr-1"></i> Pilih Satu Folder
                                </button>
                            </div>
                            <input type="file" id="fileInput" multiple accept="image/jpeg,image/png,image/webp,image/heic,image/heif,.heic,.HEIC,.heif,.HEIF,.zip" class="d-none">
                            <input type="file" id="folderInput" webkitdirectory directory multiple class="d-none">
                        </div>

                        <!-- Daftar Berkas Terpilih Ringkasan -->
                        <div id="fileSelectedList" class="alert alert-light border py-2 px-3 small text-muted mb-3" style="display: none; max-height: 140px; overflow-y: auto;"></div>

                        <!-- Format Hierarki Pengelompokan Folder -->
                        <div class="card bg-light border p-3 rounded-lg mb-3 shadow-none">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="groupingMode" class="small font-weight-bold text-dark mb-0">
                                    <i class="fas fa-sitemap text-primary mr-1"></i> Format Hierarki Pengelompokan Folder:
                                </label>
                                <span class="badge badge-primary px-2 py-1 font-weight-bold" id="groupingModeBadge">Standar (2 Tingkat)</span>
                            </div>
                            <p class="text-muted small mb-2" style="font-size: 11px;">
                                Pilih struktur susunan subfolder lokasi untuk penyimpanan foto di galeri dan berkas ZIP unduhan.
                            </p>
                            <select class="custom-select custom-select-sm font-weight-bold text-dark" id="groupingMode" name="grouping_mode">
                                <option value="kab_kec" selected>Kabupaten &rsaquo; Kecamatan (Standar Rekomendasi)</option>
                                <option value="kab_kec_kel">Kabupaten &rsaquo; Kecamatan &rsaquo; Kelurahan / Desa (3 Tingkat - Sangat Detail)</option>
                                <option value="kab_kec_spot">Kabupaten &rsaquo; Kecamatan &rsaquo; Spot Radius GPS (3 Tingkat - Berdasarkan STA / Titik)</option>
                                <option value="kab">Kabupaten / Kota Saja (1 Tingkat - Ringkas)</option>
                            </select>
                            <div id="groupingModeHelp" class="mt-2 text-primary small" style="font-size: 10.5px;">
                                <i class="fas fa-info-circle mr-1"></i> Contoh susunan: <code>Kabupaten_Kampar/Kecamatan_Tambang/foto.jpg</code>
                            </div>
                        </div>

                        <!-- Pengaturan Toleransi Radius GPS -->
                        <div class="card bg-light border p-3 rounded-lg mb-3 shadow-none">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="clusterRadius" class="small font-weight-bold text-dark mb-0">
                                    <i class="fas fa-ruler-combined text-primary mr-1"></i> Radius Toleransi Lokasi:
                                </label>
                                <span class="badge badge-info px-2 py-1 font-weight-bold" id="clusterRadiusValue">100 Meter</span>
                            </div>
                            <p class="text-muted small mb-2" style="font-size: 11px;">
                                Foto dalam radius ini akan disatukan ke dalam subfolder yang sama (misal: <em>Lokasi_01_Pekanbaru/</em>).
                            </p>
                            <input type="range" class="custom-range" id="clusterRadius" name="cluster_radius" min="25" max="1000" step="25" value="100">
                            <div class="d-flex justify-content-between text-muted" style="font-size: 10px;">
                                <span>25 m</span>
                                <span>100 m (Standar)</span>
                                <span>500 m</span>
                                <span>1 km</span>
                            </div>
                        </div>

                        <!-- Penjelasan Lossless -->
                        <div class="alert alert-success border-0 py-2 px-3 small mb-3" style="font-size: 11.5px; background-color: #e8f5e9; color: #1b5e20;">
                            <i class="fas fa-shield-alt mr-1"></i> <strong>100% Lossless &amp; Murni:</strong>
                            Berkas foto dipindahkan/dikelompokkan tanpa mengubah piksel, tanpa mengompres kualitas, dan tanpa visual watermark.
                        </div>

                        <!-- Action Button -->
                        <?php if (! empty($permissions['add']) || ! empty($permissions['edit'])): ?>
                            <button type="submit" class="btn btn-primary btn-block font-weight-bold shadow-xs py-2" id="btnProses">
                                <i class="fas fa-layer-group mr-2"></i> Mulai Organisir Foto GPS
                            </button>
                        <?php else: ?>
                            <div class="alert alert-warning small mb-0 py-2 text-center">
                                <i class="fas fa-lock mr-1"></i> Role Anda hanya memiliki akses baca (Rule #4).
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Tampilan Peta Titik Lokasi & Galeri Folder Hasil -->
        <div class="col-lg-7 col-xl-8 mb-4">
            <div class="card shadow-sm border-0 rounded-lg">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom">
                    <h6 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-map-marked-alt text-primary mr-2"></i>Hasil Pengelompokan Foto
                    </h6>
                    <div class="d-flex align-items-center">
                        <button type="button" class="btn btn-sm btn-outline-secondary mr-2" id="btnReset" style="display: none;">
                            <i class="fas fa-redo-alt mr-1"></i> Reset
                        </button>
                        <a href="#" class="btn btn-sm btn-success shadow-xs font-weight-bold" id="btnDownloadZip" style="display: none;">
                            <i class="fas fa-download mr-1"></i> Unduh ZIP Semua Folder
                        </a>
                    </div>
                </div>
                <div class="card-body p-3">
                    <!-- Placeholder State (Sebelum Upload) -->
                    <div id="placeholderState" class="text-center py-5">
                        <div class="mb-3 text-muted">
                            <i class="fas fa-folder-open fa-4x" style="opacity: 0.35;"></i>
                        </div>
                        <h6 class="font-weight-bold text-dark mb-1">Belum Ada Foto yang Dikelompokkan</h6>
                        <p class="text-muted small mb-0" style="max-width: 480px; margin: 0 auto;">
                            Pilih berkas foto kegiatan atau satu folder survei di panel sebelah kiri, kemudian klik tombol <strong>Mulai Organisir Foto GPS</strong>. Sistem akan mengekstrak EXIF GPS dan membaginya ke sub-folder lokasi secara otomatis.
                        </p>
                    </div>

                    <!-- Loading / Progress State (Detail Real-Time Monitor) -->
                    <div id="loadingState" style="display: none;" class="py-3">
                        <!-- Header Status & Stopwatch -->
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                            <div>
                                <h6 class="font-weight-bold text-dark mb-0" id="loadingTitleText">
                                    <i class="fas fa-spinner fa-spin text-primary mr-2"></i>Sedang Mengorganisir Foto GPS...
                                </h6>
                                <span class="badge badge-info badge-pill mt-1" id="loadingStageBadge">Tahap 1 dari 1 Batch</span>
                            </div>
                            <div class="text-right">
                                <span class="badge badge-light border px-2 py-1 font-monospace text-muted" id="timerElapsedText">
                                    <i class="fas fa-stopwatch mr-1"></i>00:00
                                </span>
                            </div>
                        </div>

                        <!-- 4 Stat Metrics Cards -->
                        <div class="row mb-3">
                            <div class="col-6 col-md-3 mb-2 mb-md-0">
                                <div class="bg-light border rounded p-2 text-center shadow-xs">
                                    <div class="text-muted" style="font-size: 10px; text-transform: uppercase;">Total Foto</div>
                                    <div class="font-weight-bold text-dark h6 mb-0" id="statTotalFoto">0</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 mb-2 mb-md-0">
                                <div class="bg-light border rounded p-2 text-center shadow-xs" style="border-left: 3px solid #28a745 !important;">
                                    <div class="text-muted" style="font-size: 10px; text-transform: uppercase;">Terproses</div>
                                    <div class="font-weight-bold text-success h6 mb-0" id="statProcessedFoto">0</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="bg-light border rounded p-2 text-center shadow-xs" style="border-left: 3px solid #fd7e14 !important;">
                                    <div class="text-muted" style="font-size: 10px; text-transform: uppercase;">Sisa Antrean</div>
                                    <div class="font-weight-bold text-warning h6 mb-0" id="statRemainingFoto">0</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="bg-light border rounded p-2 text-center shadow-xs" style="border-left: 3px solid #0284c7 !important;">
                                    <div class="text-muted" style="font-size: 10px; text-transform: uppercase;">Wilayah Terdeteksi</div>
                                    <div class="font-weight-bold text-primary h6 mb-0" id="statWilayahDitemukan">0</div>
                                </div>
                            </div>
                        </div>

                        <!-- Live Geocoding / Location Recognition Progress Banner -->
                        <div class="alert alert-light border d-flex align-items-center py-2 px-3 mb-3 shadow-xs" style="background-color: #f0fdf4; border-left: 4px solid #16a34a !important;">
                            <div class="mr-3 text-success">
                                <i class="fas fa-map-marked-alt fa-2x fa-pulse"></i>
                            </div>
                            <div class="text-truncate flex-grow-1">
                                <div class="small font-weight-bold text-dark mb-0">
                                    <span class="badge badge-success mr-1">Progress Lokasi GPS</span> Identifikasi Nama Wilayah:
                                </div>
                                <div class="small text-primary text-truncate font-monospace mt-1" id="liveGeocodeStatusText">
                                    Menunggu pembacaan koordinat GPS berkas...
                                </div>
                            </div>
                            <div class="ml-2 text-right d-none d-sm-block">
                                <span class="badge badge-primary font-weight-bold" id="badgeTotalWilayah">0 Wilayah</span>
                            </div>
                        </div>

                        <!-- Progress Bar & Percentage -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small font-weight-bold text-dark" id="loadingProgressText">Menyiapkan pengunggahan berkas...</span>
                                <span class="small font-weight-bold text-primary" id="progressPercentText">0%</span>
                            </div>
                            <div class="progress" style="height: 18px; border-radius: 9px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" id="progressBar" role="progressbar" style="width: 0%; font-size: 11px; font-weight: bold;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                            </div>
                        </div>

                        <!-- Live Activity Log Console -->
                        <div class="card border mb-2 shadow-none">
                            <div class="card-header py-1 px-3 bg-light d-flex justify-content-between align-items-center">
                                <span class="small font-weight-bold text-dark" style="font-size: 11.5px;">
                                    <i class="fas fa-terminal text-secondary mr-1"></i> Log Aktivitas Pemrosesan:
                                </span>
                                <span class="badge badge-secondary badge-pill" style="font-size: 9.5px;" id="logItemCount">0 item</span>
                            </div>
                            <div class="card-body p-2" id="processingLogBox" style="background-color: #0f172a; color: #38bdf8; height: 160px; overflow-y: auto; font-family: monospace; font-size: 11px; line-height: 1.5; border-radius: 0 0 4px 4px;">
                                <div class="text-muted">[00:00] Menyiapkan antrean pemrosesan foto...</div>
                            </div>
                        </div>

                        <div class="text-center">
                            <small class="text-muted" style="font-size: 11px;">
                                <i class="fas fa-info-circle mr-1"></i> Pemrosesan otomatis dipecah bertahap (5 foto/batch) agar aman dan bebas batas memori/timeout hosting.
                            </small>
                        </div>
                    </div>

                    <!-- Result Container -->
                    <div id="resultState" style="display: none;">
                        <!-- Peta Ringkasan Cluster Lokasi -->
                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="font-weight-bold small text-dark">
                                    <i class="fas fa-map mr-1 text-primary"></i> Peta Sebaran Titik Lokasi:
                                </span>
                                <span class="badge badge-info" id="clusterCountBadge">0 Titik Lokasi</span>
                            </div>
                            <div id="clusterMap" style="height: 280px; width: 100%; border-radius: 8px; border: 1px solid #ced4da;"></div>
                        </div>

                        <hr class="my-3">

                        <!-- Galeri Berdasarkan Folder Lokasi -->
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="font-weight-bold text-dark mb-0 small" style="font-size: 13px;">
                                <i class="fas fa-images text-secondary mr-1"></i> Rincian Sub-Folder Lokasi &amp; Foto:
                            </h6>
                            <span class="small text-muted" id="totalProcessedText">0 Foto</span>
                        </div>
                        <div id="foldersContainer" class="row"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Zoom Pratinjau Foto -->
<div class="modal fade" id="modalZoomPhoto" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-2 bg-light">
                <div class="text-truncate mr-2">
                    <h6 class="modal-title font-weight-bold text-dark small" id="modalZoomTitle">Pratinjau Foto</h6>
                    <small class="text-muted font-monospace" id="modalZoomMeta"></small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0 text-center bg-dark">
                <img id="modalZoomImg" src="" alt="Zoom Foto" style="max-height: 75vh; max-width: 100%; object-fit: contain;">
            </div>
            <div class="modal-footer py-2 bg-light d-flex justify-content-between">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                <a href="#" class="btn btn-primary btn-sm" id="modalZoomDownload" download>
                    <i class="fas fa-download mr-1"></i> Unduh Foto Asli
                </a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<!-- Leaflet JS -->
<script src="<?= esc(media_url('assets/leaflet/leaflet.js')); ?>"></script>

<script>
$(function() {
    var selectedFiles = [];
    var isProcessing = false;
    var clusterMapInstance = null;
    var clusterMarkersLayer = null;

    // Helper escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 B';
        var k = 1024, dm = 2, sizes = ['B', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }

    // Radius Slider Event
    $('#clusterRadius').on('input change', function() {
        $('#clusterRadiusValue').text($(this).val() + ' Meter');
    });

    // Grouping Mode Change Event
    $('#groupingMode').on('change', function() {
        var mode = $(this).val();
        var $badge = $('#groupingModeBadge');
        var $help = $('#groupingModeHelp');

        if (mode === 'kab_kec_kel') {
            $badge.removeClass().addClass('badge badge-success px-2 py-1 font-weight-bold').text('Detail (3 Tingkat)');
            $help.html('<i class="fas fa-info-circle mr-1"></i> Contoh susunan: <code>Kabupaten_Meranti/Kecamatan_Tasik_Putri_Puyu/Desa_Mengkirau/foto.jpg</code> (Otomatis fallback ke wilayah sekitar jika nama desa tidak terdaftar)');
        } else if (mode === 'kab_kec_spot') {
            $badge.removeClass().addClass('badge badge-warning text-dark px-2 py-1 font-weight-bold').text('Spot Radius (3 Tingkat)');
            $help.html('<i class="fas fa-info-circle mr-1"></i> Contoh susunan: <code>Kabupaten_Siak/Kecamatan_Siak/Spot_01_0.9780_101.9961/foto.jpg</code> (Foto dalam radius meter akan disatukan per spot)');
        } else if (mode === 'kab') {
            $badge.removeClass().addClass('badge badge-secondary px-2 py-1 font-weight-bold').text('Ringkas (1 Tingkat)');
            $help.html('<i class="fas fa-info-circle mr-1"></i> Contoh susunan: <code>Kabupaten_Bengkalis/foto.jpg</code>');
        } else {
            $badge.removeClass().addClass('badge badge-primary px-2 py-1 font-weight-bold').text('Standar (2 Tingkat)');
            $help.html('<i class="fas fa-info-circle mr-1"></i> Contoh susunan: <code>Kabupaten_Kampar/Kecamatan_Tambang/foto.jpg</code>');
        }
    });

    // Dropzone Events
    var $dropzone = $('#dropzoneBox');
    $dropzone.on('dragover dragenter', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $dropzone.addClass('bg-white border-primary shadow-sm');
    });
    $dropzone.on('dragleave drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $dropzone.removeClass('bg-white border-primary shadow-sm');
    });

    $dropzone.on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var dt = e.originalEvent.dataTransfer;
        if (!dt) return;

        // Cek webkitGetAsEntry untuk folder drag & drop
        if (dt.items && dt.items.length > 0 && typeof dt.items[0].webkitGetAsEntry === 'function') {
            var entries = [];
            for (var i = 0; i < dt.items.length; i++) {
                var entry = dt.items[i].webkitGetAsEntry();
                if (entry) entries.push(entry);
            }
            if (entries.length > 0) {
                readAllDirectoryEntries(entries, function(files) {
                    if (files && files.length > 0) {
                        handleNewFiles(files);
                    }
                });
                return;
            }
        }

        if (dt.files && dt.files.length > 0) {
            handleNewFiles(Array.from(dt.files));
        }
    });

    // File input change
    $('#fileInput').on('change', function() {
        if (this.files && this.files.length > 0) {
            handleNewFiles(Array.from(this.files));
        }
    });

    // Folder input change
    $('#folderInput').on('change', function() {
        if (this.files && this.files.length > 0) {
            handleNewFiles(Array.from(this.files));
        }
    });

    // Recursive directory reader for folder drag-and-drop
    function readAllDirectoryEntries(entries, callback) {
        var files = [];
        var pendingCount = 0;

        function traverseEntry(entry, pathPrefix) {
            pathPrefix = pathPrefix || '';
            if (entry.isFile) {
                pendingCount++;
                entry.file(function(file) {
                    file.customRelativePath = pathPrefix + file.name;
                    var ext = file.name.split('.').pop().toLowerCase();
                    if (['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'].indexOf(ext) !== -1) {
                        files.push(file);
                    }
                    pendingCount--;
                    if (pendingCount === 0) callback(files);
                }, function() {
                    pendingCount--;
                    if (pendingCount === 0) callback(files);
                });
            } else if (entry.isDirectory) {
                var dirReader = entry.createReader();
                pendingCount++;
                var readEntries = function() {
                    dirReader.readEntries(function(subEntries) {
                        if (subEntries.length > 0) {
                            for (var s = 0; s < subEntries.length; s++) {
                                traverseEntry(subEntries[s], pathPrefix + entry.name + '/');
                            }
                            readEntries();
                        } else {
                            pendingCount--;
                            if (pendingCount === 0) callback(files);
                        }
                    }, function() {
                        pendingCount--;
                        if (pendingCount === 0) callback(files);
                    });
                };
                readEntries();
            }
        }

        for (var i = 0; i < entries.length; i++) {
            traverseEntry(entries[i], '');
        }
    }

    function handleNewFiles(files) {
        var validFiles = files.filter(function(f) {
            var ext = f.name.split('.').pop().toLowerCase();
            return ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'].indexOf(ext) !== -1;
        });

        if (validFiles.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Tidak Ada Foto Valid',
                text: 'Harap pilih berkas gambar bertipe JPG, PNG, WebP, atau iPhone HEIC.'
            });
            return;
        }

        selectedFiles = validFiles;
        updateFileListUI();
    }

    function updateFileListUI() {
        var count = selectedFiles.length;
        if (count === 0) {
            $('#fileCountBadge').removeClass('badge-primary badge-success').addClass('badge-secondary').text('Belum ada file');
            $('#fileSelectedList').empty().hide();
            return;
        }

        var totalBytes = selectedFiles.reduce(function(acc, f) { return acc + (f.size || 0); }, 0);
        $('#fileCountBadge').removeClass('badge-secondary').addClass('badge-primary').text(count + ' Foto (' + formatFileSize(totalBytes) + ')');

        var $list = $('#fileSelectedList');
        $list.empty().show();
        var html = '<div class="font-weight-bold text-dark mb-1"><i class="fas fa-check-circle text-success mr-1"></i> ' + count + ' Berkas Foto Terpilih:</div><ul class="mb-0 pl-3">';
        var maxShow = Math.min(5, count);
        for (var i = 0; i < maxShow; i++) {
            var rel = selectedFiles[i].customRelativePath || selectedFiles[i].webkitRelativePath || selectedFiles[i].name;
            html += '<li class="text-truncate">' + escapeHtml(rel) + ' <span class="text-muted">(' + formatFileSize(selectedFiles[i].size || 0) + ')</span></li>';
        }
        if (count > 5) {
            html += '<li class="text-muted font-italic">... dan ' + (count - 5) + ' berkas lainnya.</li>';
        }
        html += '</ul>';
        $list.html(html);
    }

    // Submit Form
    $('#organisirForm').on('submit', function(e) {
        e.preventDefault();
        if (isProcessing) return;

        if (selectedFiles.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Foto Belum Dipilih',
                text: 'Silakan pilih atau tarik foto kegiatan ke area upload terlebih dahulu.'
            });
            return;
        }

        executeChunkedOrganize(selectedFiles, 10);
    });

    var stopwatchInterval = null;
    var elapsedSeconds = 0;
    var logItemCount = 0;

    function startProgressStopwatch() {
        elapsedSeconds = 0;
        clearInterval(stopwatchInterval);
        $('#timerElapsedText').html('<i class="fas fa-stopwatch mr-1"></i>00:00');
        stopwatchInterval = setInterval(function() {
            elapsedSeconds++;
            var mm = String(Math.floor(elapsedSeconds / 60)).padStart(2, '0');
            var ss = String(elapsedSeconds % 60).padStart(2, '0');
            $('#timerElapsedText').html('<i class="fas fa-stopwatch mr-1"></i>' + mm + ':' + ss);
        }, 1000);
    }

    function stopProgressStopwatch() {
        clearInterval(stopwatchInterval);
    }

    function addLogEntry(text, colorClass) {
        logItemCount++;
        var mm = String(Math.floor(elapsedSeconds / 60)).padStart(2, '0');
        var ss = String(elapsedSeconds % 60).padStart(2, '0');
        var timeBadge = '<span class="text-secondary">[' + mm + ':' + ss + ']</span> ';
        var cls = colorClass || 'text-info';
        var line = $('<div>').addClass(cls).html(timeBadge + text);
        var $box = $('#processingLogBox');
        $box.append(line);
        $box.scrollTop($box.prop('scrollHeight'));
        $('#logItemCount').text(logItemCount + ' item');
    }

    /**
     * Chunked upload processor: slices files into 10 photos per stage.
     */
    function executeChunkedOrganize(files, chunkSize) {
        chunkSize = chunkSize || 10;
        var totalFiles = files.length;
        var totalChunks = Math.ceil(totalFiles / chunkSize);
        var batchId = 'org_' + Date.now() + '_' + Math.random().toString(36).substr(2, 6);
        var radiusMeters = $('#clusterRadius').val() || '100';

        isProcessing = true;
        var $btn = $('#btnProses');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Memproses Parsial (0/' + totalFiles + ')...');

        $('#placeholderState').hide();
        $('#resultState').hide();
        $('#loadingState').fadeIn(150);

        // Reset Metric Cards & Console
        logItemCount = 0;
        var detectedWilayahSet = {};
        $('#processingLogBox').empty();
        $('#statTotalFoto').text(totalFiles);
        $('#statProcessedFoto').text(0);
        $('#statRemainingFoto').text(totalFiles);
        $('#statWilayahDitemukan').text(0);
        $('#badgeTotalWilayah').text('0 Wilayah');
        $('#progressPercentText').text('0%');
        $('#loadingStageBadge').text('Tahap 1 dari ' + totalChunks + ' Batch');
        $('#loadingTitleText').html('<i class="fas fa-spinner fa-spin text-primary mr-2"></i>Sedang Mengorganisir Foto GPS...');
        $('#progressBar').css('width', '2%').text('0%').removeClass('bg-success').addClass('bg-primary');
        $('#loadingProgressText').text('Memulai pemrosesan tahap 1 dari ' + totalChunks + '...');
        $('#liveGeocodeStatusText').html('<i class="fas fa-search-location fa-spin text-primary mr-1"></i> Menyiapkan antrean & koneksi identifikasi wilayah GPS...');

        startProgressStopwatch();
        addLogEntry('Menginisialisasi antrean ' + totalFiles + ' foto dalam ' + totalChunks + ' batch (5 foto/batch)...', 'text-warning');

        var currentChunk = 0;
        var processedCount = 0;

        function processNextChunk() {
            if (currentChunk >= totalChunks) return;

            var startIdx = currentChunk * chunkSize;
            var endIdx = Math.min(startIdx + chunkSize, totalFiles);
            var chunkSlice = files.slice(startIdx, endIdx);
            var isLast = (currentChunk === totalChunks - 1);

            var chunkFormData = new FormData();
            chunkFormData.append('batch_id', batchId);
            chunkFormData.append('chunk_index', currentChunk);
            chunkFormData.append('total_chunks', totalChunks);
            chunkFormData.append('is_last_chunk', isLast ? '1' : '0');
            chunkFormData.append('cluster_radius', radiusMeters);
            chunkFormData.append('grouping_mode', $('#groupingMode').val() || 'kab_kec');

            for (var i = 0; i < chunkSlice.length; i++) {
                chunkFormData.append('fotos[]', chunkSlice[i]);
                var rel = chunkSlice[i].customRelativePath || chunkSlice[i].webkitRelativePath || chunkSlice[i].name;
                chunkFormData.append('foto_paths[]', rel);
            }

            chunkFormData.append('<?= csrf_token() ?>', $('input[name="<?= csrf_token() ?>"]').val());

            var progressPercent = Math.round(((currentChunk) / totalChunks) * 100);
            $('#progressBar').css('width', Math.max(5, progressPercent) + '%').text(progressPercent + '%');
            $('#progressPercentText').text(progressPercent + '%');
            $('#loadingStageBadge').text('Tahap ' + (currentChunk + 1) + ' dari ' + totalChunks + ' Batch');
            $('#loadingProgressText').text('Tahap ' + (currentChunk + 1) + ' dari ' + totalChunks + ': Mengunggah & membaca GPS foto ' + (startIdx + 1) + '–' + endIdx + ' dari ' + totalFiles + ' foto...');
            $('#liveGeocodeStatusText').html('<i class="fas fa-search-location text-info mr-1"></i> Tahap ' + (currentChunk + 1) + ': Mengidentifikasi nama lokasi & wilayah administratif...');
            $btn.html('<i class="fas fa-spinner fa-spin mr-2"></i> Tahap ' + (currentChunk + 1) + '/' + totalChunks + ' (' + endIdx + '/' + totalFiles + ' Foto)...');

            addLogEntry('⟳ Mengunggah Batch ' + (currentChunk + 1) + '/' + totalChunks + ' (' + chunkSlice.length + ' foto)...', 'text-info');

            $.ajax({
                url: '<?= site_url('/admin/dokumentasi/organisir-foto-gps/proses-chunk') ?>',
                type: 'POST',
                data: chunkFormData,
                processData: false,
                contentType: false,
                dataType: 'json',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function(resp) {
                    if (resp.csrf_hash) {
                        $('input[name="<?= csrf_token() ?>"]').val(resp.csrf_hash);
                    }

                    if (resp.status !== 'ok') {
                        isProcessing = false;
                        stopProgressStopwatch();
                        $('#loadingState').hide();
                        $('#placeholderState').show();
                        $btn.prop('disabled', false).html('<i class="fas fa-layer-group mr-2"></i> Mulai Organisir Foto GPS');
                        addLogEntry('✗ Gagal di Tahap ' + (currentChunk + 1) + ': ' + (resp.message || 'Kesalahan server'), 'text-danger');
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal di Tahap ' + (currentChunk + 1),
                            text: resp.message || 'Terjadi kendala saat memproses batch foto.'
                        });
                        return;
                    }

                    // Perbarui hitungan statistik
                    var doneNow = resp.processed_count || (startIdx + chunkSlice.length);
                    processedCount = doneNow;
                    $('#statProcessedFoto').text(doneNow);
                    $('#statRemainingFoto').text(Math.max(0, totalFiles - doneNow));

                    // Log setiap berkas yang selesai di batch ini dengan nama lokasi teridentifikasi
                    if (resp.processed_files && resp.processed_files.length > 0) {
                        resp.processed_files.forEach(function(item) {
                            var fname = typeof item === 'object' ? item.file_name : item;
                            var hasGps = typeof item === 'object' ? item.has_gps : false;
                            var kab = (typeof item === 'object' && item.kabupaten) ? item.kabupaten : null;
                            var kec = (typeof item === 'object' && item.kecamatan) ? item.kecamatan : null;
                            var kel = (typeof item === 'object' && item.kelurahan && item.kelurahan !== '-') ? item.kelurahan : null;
                            var coords = (typeof item === 'object' && item.coordinates) ? item.coordinates : null;

                            if (hasGps && kab && kab !== 'Tanpa Koordinat GPS') {
                                var wKey = kab + '__' + (kec || '') + '__' + (kel || '');
                                detectedWilayahSet[wKey] = true;
                                var countW = Object.keys(detectedWilayahSet).length;
                                $('#statWilayahDitemukan').text(countW);
                                $('#badgeTotalWilayah').text(countW + ' Wilayah');

                                var locChain = escapeHtml(kab);
                                if (kec && kec !== '-') locChain += ' &rsaquo; ' + escapeHtml(kec);
                                if (kel && kel !== '-') locChain += ' &rsaquo; ' + escapeHtml(kel);

                                addLogEntry('✓ <span class="text-white font-weight-bold">' + escapeHtml(fname) + '</span><br>' +
                                    '<span class="text-success ml-3"><i class="fas fa-map-marker-alt text-danger mr-1"></i> <strong>' + locChain + '</strong></span>' +
                                    (coords ? ' <span class="text-muted small font-monospace">(' + escapeHtml(coords) + ')</span>' : ''), 'text-light');

                                $('#liveGeocodeStatusText').html('<i class="fas fa-check-circle text-success mr-1"></i> Terdeteksi: <strong>' + locChain + '</strong> <span class="text-muted">(' + escapeHtml(fname) + ')</span>');
                            } else {
                                addLogEntry('⚠️ <span class="text-white font-weight-bold">' + escapeHtml(fname) + '</span><br>' +
                                    '<span class="text-warning ml-3"><i class="fas fa-map-marker-slash mr-1"></i> Tidak memiliki EXIF GPS &rarr; Folder: <code>Tanpa_Koordinat_GPS/</code></span>', 'text-light');

                                $('#liveGeocodeStatusText').html('<i class="fas fa-info-circle text-warning mr-1"></i> ' + escapeHtml(fname) + ' tanpa koordinat GPS &rarr; disimpan ke Tanpa_Koordinat_GPS');
                            }
                        });
                    } else {
                        chunkSlice.forEach(function(f) {
                            addLogEntry('✓ ' + escapeHtml(f.name) + ' — GPS terbaca & diarsipkan', 'text-light');
                        });
                    }

                    if (!resp.is_finished) {
                        currentChunk++;
                        var nextPercent = Math.round(((currentChunk) / totalChunks) * 100);
                        $('#progressBar').css('width', nextPercent + '%').text(nextPercent + '%');
                        $('#progressPercentText').text(nextPercent + '%');
                        processNextChunk();
                    } else {
                        // Selesai seluruh tahapan!
                        isProcessing = false;
                        stopProgressStopwatch();
                        $btn.prop('disabled', false).html('<i class="fas fa-layer-group mr-2"></i> Mulai Organisir Foto GPS');
                        $('#statProcessedFoto').text(totalFiles);
                        $('#statRemainingFoto').text(0);
                        $('#progressBar').css('width', '100%').text('100%').removeClass('bg-primary').addClass('bg-success');
                        $('#progressPercentText').text('100%');
                        $('#loadingProgressText').text('Selesai! Menyusun folder Kabupaten / Kecamatan...');

                        addLogEntry('🎉 Seluruh foto selesai diorganisir ke subfolder Kabupaten & Kecamatan!', 'text-success font-weight-bold');

                        setTimeout(function() {
                            $('#loadingState').hide();
                            renderOrganizedResults(resp);
                        }, 500);
                    }
                },
                error: function(xhr) {
                    isProcessing = false;
                    stopProgressStopwatch();
                    $('#loadingState').hide();
                    $('#placeholderState').show();
                    $btn.prop('disabled', false).html('<i class="fas fa-layer-group mr-2"></i> Mulai Organisir Foto GPS');
                    var errMsg = 'Terjadi gangguan jaringan atau server saat memproses tahap ' + (currentChunk + 1) + '.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errMsg = xhr.responseJSON.message;
                    }
                    addLogEntry('✗ Putus koneksi pada Batch ' + (currentChunk + 1) + ': ' + errMsg, 'text-danger');
                    Swal.fire({
                        icon: 'error',
                        title: 'Koneksi Terputus di Tahap ' + (currentChunk + 1),
                        html: errMsg
                    });
                }
            });
        }

        processNextChunk();
    }

    /**
     * Render hasil clustering GPS: Peta titik dan galeri subfolder.
     */
    function renderOrganizedResults(resp) {
        $('#btnReset').show();
        if (resp.download_zip_url) {
            $('#btnDownloadZip').attr('href', resp.download_zip_url).html('<i class="fas fa-file-archive mr-1"></i> Unduh ZIP (Terkelompok Kabupaten / Kecamatan)').show();
        } else {
            $('#btnDownloadZip').hide();
        }

        $('#totalProcessedText').text(resp.total_processed + ' Foto');
        var clusters = resp.clusters || [];
        $('#clusterCountBadge').text(clusters.length + ' Sub-Folder Lokasi');

        var $foldersBox = $('#foldersContainer');
        $foldersBox.empty();

        // 1. Inisialisasi Peta Leaflet jika belum ada
        if (!clusterMapInstance) {
            clusterMapInstance = L.map('clusterMap').setView([0.507068, 101.447779], 8);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(clusterMapInstance);
            clusterMarkersLayer = L.featureGroup().addTo(clusterMapInstance);
        } else {
            clusterMarkersLayer.clearLayers();
        }

        var mapBounds = [];

        // 2. Render Tiap Folder Cluster (Terkelompok Hierarkis Kabupaten & Kecamatan)
        clusters.forEach(function(cluster) {
            var isNoGps = (cluster.id === 'no_gps');
            var clusterImgs = resp.images.filter(function(im) {
                return String(im.cluster_id) === String(cluster.id);
            });
            if (clusterImgs.length === 0) return;

            var kabName = isNoGps ? 'Tanpa Koordinat GPS' : (cluster.kabupaten || 'Kabupaten Riau');
            var kecName = isNoGps ? '-' : (cluster.kecamatan || 'Kecamatan');
            var kelName = isNoGps ? '-' : (cluster.kelurahan || '-');

            // Tambah Pin ke Peta jika memiliki koordinat
            if (!isNoGps && cluster.lat !== null && cluster.lng !== null) {
                var lat = parseFloat(cluster.lat);
                var lng = parseFloat(cluster.lng);
                mapBounds.push([lat, lng]);

                var marker = L.marker([lat, lng]);
                var popupHtml = '<div style="font-family: sans-serif; font-size: 12px; min-width: 190px;">' +
                    '<div class="font-weight-bold text-primary mb-1 border-bottom pb-1"><i class="fas fa-map-marker-alt text-danger mr-1"></i> ' + escapeHtml(kabName) + '</div>' +
                    (kecName !== '-' ? '<div class="mb-1 text-dark" style="font-size: 11.5px;"><strong>Kecamatan:</strong> ' + escapeHtml(kecName) + '</div>' : '') +
                    (kelName !== '-' ? '<div class="mb-1 text-success" style="font-size: 11.5px;"><strong>Kelurahan / Desa:</strong> ' + escapeHtml(kelName) + '</div>' : '') +
                    '<div class="text-muted small mb-1"><strong>Folder ZIP:</strong> <code class="text-primary">' + escapeHtml(cluster.folder_name) + '</code></div>' +
                    '<div class="badge badge-info">' + clusterImgs.length + ' Foto</div>' +
                    '</div>';
                marker.bindPopup(popupHtml);
                clusterMarkersLayer.addLayer(marker);
            }

            // Header Subfolder: Tampilkan Jelas Kabupaten, Kecamatan & Kelurahan
            var headerCol = $('<div>').addClass('col-12 mt-3 mb-2');
            var headerCard = $('<div>').addClass('d-flex flex-column flex-md-row align-items-md-center justify-content-between p-2 rounded border shadow-xs')
                .css({
                    'border-left': '4px solid ' + (isNoGps ? '#6c757d' : '#0A66C2') + ' !important',
                    'background-color': isNoGps ? '#f8f9fa' : '#f0f7ff'
                });

            var leftBox = $('<div>').addClass('mr-2 mb-2 mb-md-0');
            var icon = isNoGps ? 'fas fa-map-marker-slash text-secondary' : 'fas fa-map-marked-alt text-danger';

            var locParts = '<span class="text-primary">' + escapeHtml(kabName) + '</span>';
            if (kecName !== '-') {
                locParts += ' &rsaquo; <span>' + escapeHtml(kecName) + '</span>';
            }
            if (kelName !== '-') {
                locParts += ' &rsaquo; <span class="text-success font-weight-bold">' + escapeHtml(kelName) + '</span>';
            }

            var titleDiv = $('<div>').addClass('font-weight-bold text-dark small').html(
                '<i class="' + icon + ' mr-1"></i> ' + locParts
            );
            leftBox.append(titleDiv);

            var metaDiv = $('<div>').addClass('mt-1 d-flex align-items-center flex-wrap');
            metaDiv.append($('<span>').addClass('badge ' + (isNoGps ? 'badge-secondary' : 'badge-warning text-dark') + ' mr-2').html('<i class="fas fa-folder mr-1"></i>' + escapeHtml(cluster.folder_name)));
            if (cluster.coords_text && cluster.coords_text !== '-') {
                metaDiv.append($('<small>').addClass('text-muted font-monospace').text('(' + cluster.coords_text + ')'));
            }
            leftBox.append(metaDiv);

            var rightBox = $('<div>').addClass('d-flex align-items-center');
            rightBox.append($('<span>').addClass('badge badge-primary badge-pill px-3 py-1 font-weight-bold').text(clusterImgs.length + ' Foto'));

            headerCard.append(leftBox).append(rightBox);
            headerCol.append(headerCard);
            $foldersBox.append(headerCol);

            // Thumbnail Cards
            clusterImgs.forEach(function(img) {
                var col = $('<div>').addClass('col-6 col-sm-4 col-md-3 mb-3');
                var card = $('<div>').addClass('border rounded overflow-hidden shadow-sm bg-light text-center p-1 position-relative');

                var thumbSrc = img.data_uri || '';
                if (thumbSrc) {
                    var imageEl = $('<img>').attr('src', thumbSrc)
                        .attr('alt', img.orig_name || img.file_name)
                        .attr('loading', 'lazy')
                        .addClass('img-fluid rounded cursor-pointer')
                        .css({ 'height': '110px', 'width': '100%', 'object-fit': 'cover', 'display': 'block' })
                        .on('click', function() {
                            $('#modalZoomImg').attr('src', thumbSrc);
                            $('#modalZoomTitle').text(img.orig_name || img.file_name);
                            $('#modalZoomMeta').text('Lokasi: ' + kabName + (kecName !== '-' ? ' > ' + kecName : '') + (kelName !== '-' ? ' > ' + kelName : '') + ' (' + img.sub_dir + ')');
                            $('#modalZoomDownload').attr('href', img.download_url || thumbSrc).attr('download', img.orig_name || img.file_name);
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

                var title = $('<div>').addClass('text-truncate font-weight-bold mt-1 text-dark')
                    .css('font-size', '11px')
                    .attr('title', img.orig_name || img.file_name)
                    .text(img.orig_name || img.file_name);
                card.append(title);

                var badge = $('<div>').addClass('badge badge-light border text-truncate text-secondary w-100 mt-1')
                    .css({ 'font-size': '9.5px', 'max-width': '100%', 'padding': '2px 4px' })
                    .html('<i class="fas fa-folder text-warning mr-1"></i>' + escapeHtml(img.sub_dir));
                card.append(badge);

                col.append(card);
                $foldersBox.append(col);
            });
        });

        $('#resultState').fadeIn(200, function() {
            if (clusterMapInstance) {
                clusterMapInstance.invalidateSize();
                if (mapBounds.length > 0) {
                    clusterMapInstance.fitBounds(mapBounds, { padding: [30, 30], maxZoom: 15 });
                }
            }
        });

        // Notifikasi Tunggal (Rule #1: tidak double)
        Swal.fire({
            icon: 'success',
            title: 'Berhasil Dikelompokkan!',
            text: resp.message || 'Foto berhasil dikelompokkan ke dalam subfolder lokasi.',
            timer: 2500,
            showConfirmButton: false
        });
    }

    // Reset button
    $('#btnReset').on('click', function() {
        selectedFiles = [];
        $('#fileInput').val('');
        $('#folderInput').val('');
        updateFileListUI();
        $('#resultState').hide();
        $('#btnReset').hide();
        $('#btnDownloadZip').hide();
        $('#placeholderState').fadeIn();
    });
});
</script>
<?= $this->endSection(); ?>

