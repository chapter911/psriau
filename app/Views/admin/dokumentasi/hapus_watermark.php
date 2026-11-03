<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">
    <!-- Header Halaman -->
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="h3 mb-1 text-gray-800 font-weight-bold">
                <i class="fas fa-eraser text-primary mr-2"></i><?= esc($title ?? 'Pembersih Watermark Foto'); ?>
            </h1>
            <p class="text-muted small mb-0">
                <?= esc($subtitle ?? 'Hapus stempel watermark pada foto dokumentasi lapangan secara instan menggunakan kuas cerdas atau preset zona otomatis (100% Client-Side di Browser).'); ?>
            </p>
        </div>
        <ol class="breadcrumb bg-transparent p-0 mb-0 mt-2 mt-sm-0 small">
            <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard'); ?>">Dashboard</a></li>
            <li class="breadcrumb-item">Dokumentasi</li>
            <li class="breadcrumb-item active" aria-current="page">Pembersih Watermark</li>
        </ol>
    </div>

    <!-- Info Banner Fitur -->
    <div class="alert alert-light border shadow-xs d-flex align-items-center justify-content-between p-3 mb-3 rounded-lg flex-wrap">
        <div class="d-flex align-items-center">
            <div class="badge-icon-box bg-primary text-white rounded-circle p-2 mr-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="fas fa-magic fa-lg"></i>
            </div>
            <div>
                <span class="font-weight-bold text-dark d-block">Pemrosesan Cepat, Gratis &amp; 100% Privat di Browser</span>
                <small class="text-muted">Foto tidak dikirim ke server luar. Semua rekonstruksi grafis dijalankan langsung oleh kartu grafis / browser Anda.</small>
            </div>
        </div>
        <div class="mt-2 mt-md-0">
            <span class="badge badge-success px-2 py-1 mr-1"><i class="fas fa-shield-alt mr-1"></i>Privasi Terjaga</span>
            <span class="badge badge-info px-2 py-1 mr-1"><i class="fas fa-bolt mr-1"></i>Instan</span>
            <span class="badge badge-secondary px-2 py-1"><i class="fas fa-infinity mr-1"></i>Tanpa Batas Kuota</span>
        </div>
    </div>

    <!-- Tab Mode Pemrosesan -->
    <ul class="nav nav-pills mb-3 bg-white p-2 border rounded-lg shadow-xs" id="modeTabs" role="tablist">
        <li class="nav-item flex-fill text-center">
            <a class="nav-link active font-weight-bold py-2" id="tabSingleMode" data-toggle="pill" href="#paneSingleMode" role="tab">
                <i class="fas fa-brush mr-1"></i> Mode Satuan (Studio Kanvas Interaktif)
            </a>
        </li>
        <li class="nav-item flex-fill text-center">
            <a class="nav-link font-weight-bold py-2" id="tabBatchMode" data-toggle="pill" href="#paneBatchMode" role="tab">
                <i class="fas fa-layer-group mr-1"></i> Mode Massal / Batch (Rutinitas Banyak Foto)
            </a>
        </li>
    </ul>

    <!-- Konten Tab Mode -->
    <div class="tab-content" id="modeTabsContent">
        <!-- =================================================================== -->
        <!-- TAB 1: MODE SATUAN (STUDIO KANVAS INTERAKTIF)                      -->
        <!-- =================================================================== -->
        <div class="tab-pane fade show active" id="paneSingleMode" role="tabpanel">
            <div class="row">
                <!-- Kolom Kiri: Pengaturan Masker & Kontrol -->
                <div class="col-lg-4 mb-3">
                    <!-- 1. Area Unggah Foto Satuan -->
                    <div class="card shadow-sm border-0 rounded-lg mb-3">
                        <div class="card-header bg-white py-2 d-flex align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-dark small"><i class="fas fa-file-image text-primary mr-1"></i> 1. Unggah Foto Target</h6>
                            <span class="badge badge-light border text-muted" id="singleImgInfo">Belum ada foto</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="dropzone-area p-3 text-center border rounded-lg bg-light" id="singleDropzone" style="border: 2px dashed #0A66C2 !important; cursor: pointer;">
                                <i class="fas fa-cloud-upload-alt fa-2x text-primary mb-1"></i>
                                <h6 class="font-weight-bold mb-1 text-dark small">Pilih atau Tarik Foto ke Sini</h6>
                                <p class="text-muted mb-2" style="font-size: 11px;">Mendukung JPG, PNG, WebP, dan iPhone HEIC</p>
                                <button type="button" class="btn btn-outline-primary btn-sm px-3 shadow-xs" onclick="$('#singleFileInput').click()">
                                    <i class="fas fa-folder-open mr-1"></i> Telusuri Berkas
                                </button>
                                <input type="file" id="singleFileInput" accept="image/jpeg,image/png,image/webp,image/heic,image/heif,.heic,.HEIC,.heif,.HEIF" class="d-none">
                            </div>
                        </div>
                    </div>

                    <!-- 2. Pilihan Zona Watermark Cepat -->
                    <div class="card shadow-sm border-0 rounded-lg mb-3">
                        <div class="card-header bg-white py-2 d-flex align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-dark small"><i class="fas fa-crosshairs text-info mr-1"></i> 2. Zona Watermark Cepat</h6>
                            <span class="badge badge-primary" id="activePresetBadge">Kiri Bawah (TimeMark)</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="row no-gutters mb-2">
                                <div class="col-6 p-1">
                                    <button type="button" class="btn btn-outline-secondary btn-block btn-sm preset-btn active" data-preset="bottom-left">
                                        <i class="fas fa-angle-double-down mr-1"></i> Kiri Bawah
                                        <small class="d-block text-muted" style="font-size: 9.5px;">TimeMark Klasik</small>
                                    </button>
                                </div>
                                <div class="col-6 p-1">
                                    <button type="button" class="btn btn-outline-secondary btn-block btn-sm preset-btn" data-preset="bottom-right">
                                        <i class="fas fa-angle-double-right mr-1"></i> Kanan Bawah
                                        <small class="d-block text-muted" style="font-size: 9.5px;">Badge / Tanggal</small>
                                    </button>
                                </div>
                                <div class="col-6 p-1">
                                    <button type="button" class="btn btn-outline-secondary btn-block btn-sm preset-btn" data-preset="bottom-strip">
                                        <i class="fas fa-minus mr-1"></i> Tepi Bawah Penuh
                                        <small class="d-block text-muted" style="font-size: 9.5px;">Strip Banner</small>
                                    </button>
                                </div>
                                <div class="col-6 p-1">
                                    <button type="button" class="btn btn-outline-secondary btn-block btn-sm preset-btn" data-preset="top-left">
                                        <i class="fas fa-angle-double-up mr-1"></i> Kiri Atas
                                        <small class="d-block text-muted" style="font-size: 9.5px;">Header Logo</small>
                                    </button>
                                </div>
                                <div class="col-12 p-1">
                                    <button type="button" class="btn btn-outline-secondary btn-block btn-sm preset-btn" data-preset="manual">
                                        <i class="fas fa-paint-brush mr-1"></i> Kuas Bebas (Gambar Manual dengan Kuas)
                                    </button>
                                </div>
                            </div>

                            <!-- Slider Penyesuaian Zona Preset -->
                            <div id="presetSlidersBox" class="border rounded p-2 bg-light small mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted">Lebar Zona (%):</span>
                                    <strong id="presetWidthVal" class="text-dark">38%</strong>
                                </div>
                                <input type="range" class="custom-range" id="presetWidthRange" min="15" max="60" value="38">

                                <div class="d-flex justify-content-between align-items-center mt-2 mb-1">
                                    <span class="text-muted">Tinggi Zona (%):</span>
                                    <strong id="presetHeightVal" class="text-dark">32%</strong>
                                </div>
                                <input type="range" class="custom-range" id="presetHeightRange" min="10" max="60" value="32">
                            </div>
                        </div>
                    </div>

                    <!-- 3. Alat Kuas & Bauran -->
                    <div class="card shadow-sm border-0 rounded-lg mb-3">
                        <div class="card-header bg-white py-2">
                            <h6 class="m-0 font-weight-bold text-dark small"><i class="fas fa-paint-roller text-success mr-1"></i> 3. Alat Kuas &amp; Bauran Tepi</h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="btn-group btn-group-sm w-100 mb-2" role="group">
                                <button type="button" class="btn btn-primary active" id="btnToolBrush">
                                    <i class="fas fa-paint-brush mr-1"></i> Kuas (Masker Merah)
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="btnToolEraser">
                                    <i class="fas fa-eraser mr-1"></i> Penghapus Kuas
                                </button>
                            </div>

                            <div class="mb-2 small">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Ukuran Kuas:</span>
                                    <strong id="brushSizeVal">35 px</strong>
                                </div>
                                <input type="range" class="custom-range" id="brushSizeRange" min="10" max="120" value="35">
                            </div>

                            <div class="mb-2 small">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Kehalusan Tepi (Feathering):</span>
                                    <strong id="featherVal">6 px</strong>
                                </div>
                                <input type="range" class="custom-range" id="featherRange" min="2" max="15" value="6">
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-danger btn-xs" id="btnClearMask">
                                    <i class="fas fa-trash-alt mr-1"></i> Hapus Masker
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-xs" id="btnResetSingle">
                                    <i class="fas fa-undo mr-1"></i> Pulihkan Foto Asli
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Eksekusi Satuan -->
                    <div class="mb-3">
                        <button type="button" class="btn btn-primary btn-block btn-lg shadow font-weight-bold py-2.5" id="btnProcessSingle">
                            <i class="fas fa-magic mr-1"></i> Bersihkan Watermark Sekarang
                        </button>
                        <button type="button" class="btn btn-success btn-block font-weight-bold py-2 mt-2 shadow-xs" id="btnDownloadSingle" style="display: none;">
                            <i class="fas fa-download mr-1"></i> Unduh Foto Bersih (JPG Kualitas Tinggi)
                        </button>
                    </div>
                </div>

                <!-- Kolom Kanan: Kanvas Interaktif & Pratinjau Before/After -->
                <div class="col-lg-8 mb-3">
                    <div class="card shadow-sm border-0 rounded-lg">
                        <div class="card-header bg-white py-2 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <h6 class="m-0 font-weight-bold text-dark small mr-3">
                                    <i class="fas fa-desktop text-info mr-1"></i> Kanvas Studio
                                </h6>
                                <div class="btn-group btn-group-toggle btn-group-sm" id="viewModeToggle" style="display: none;">
                                    <label class="btn btn-outline-secondary btn-xs active" id="lblViewClean">
                                        <input type="radio" name="view_mode" checked> Hasil Bersih
                                    </label>
                                    <label class="btn btn-outline-secondary btn-xs" id="lblViewOriginal">
                                        <input type="radio" name="view_mode"> Foto Asli
                                    </label>
                                    <label class="btn btn-outline-secondary btn-xs" id="lblViewSplit">
                                        <input type="radio" name="view_mode"> Split Slider
                                    </label>
                                </div>
                            </div>
                            <span class="badge badge-light border text-muted" id="canvasMetaInfo">Resolusi: -</span>
                        </div>
                        <div class="card-body p-2 bg-dark rounded-bottom text-center position-relative overflow-hidden" style="min-height: 480px; display: flex; align-items: center; justify-content: center;">
                            <!-- Placeholder Bila Belum Unggah Foto -->
                            <div id="singlePlaceholder" class="p-5 text-muted">
                                <i class="fas fa-image fa-4x mb-3 text-secondary opacity-50"></i>
                                <h5 class="font-weight-bold text-light">Belum Ada Foto Terpilih</h5>
                                <p class="small text-muted mb-3">Unggah foto dokumentasi untuk memulai penghapusan watermark.</p>
                                <button type="button" class="btn btn-outline-light btn-sm px-4" onclick="$('#singleFileInput').click()">
                                    <i class="fas fa-folder-open mr-1"></i> Pilih Foto Sekarang
                                </button>
                            </div>

                            <!-- Kanvas Wrapper (Tampil Saat Foto Terpilih) -->
                            <div id="canvasViewportWrapper" style="display: none; position: relative; max-width: 100%; max-height: 600px; user-select: none;">
                                <!-- Kanvas Utama Foto -->
                                <canvas id="mainCanvas" style="max-width: 100%; height: auto; display: block; border-radius: 4px; box-shadow: 0 4px 14px rgba(0,0,0,0.5);"></canvas>
                                <!-- Kanvas Lapisan Masker Kuas -->
                                <canvas id="maskCanvas" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; cursor: crosshair; pointer-events: auto; border-radius: 4px;"></canvas>
                                
                                <!-- Split Slider Divider (Untuk Perbandingan Before-After) -->
                                <div id="splitSliderDivider" style="display: none; position: absolute; top: 0; bottom: 0; width: 3px; background: #fff; cursor: ew-resize; box-shadow: 0 0 6px rgba(0,0,0,0.8); z-index: 10;">
                                    <div class="split-handle" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 28px; height: 28px; background: #fff; border: 2px solid #0A66C2; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.4);">
                                        <i class="fas fa-arrows-alt-h text-primary" style="font-size: 11px;"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Loading Overlay Pemrosesan Inpainting -->
                            <div id="inpaintLoadingOverlay" class="position-absolute w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-dark" style="top: 0; left: 0; opacity: 0.88; z-index: 30; display: none !important;">
                                <div class="spinner-border text-primary mb-3" style="width: 3.5rem; height: 3.5rem;" role="status"></div>
                                <h6 class="text-white font-weight-bold mb-1">Sedang Merekonstruksi Piksel Latar Belakang...</h6>
                                <small class="text-light opacity-75">Menganalisis tekstur sekitar &amp; membaurkan batas tepi</small>
                            </div>
                        </div>
                        <div class="card-footer bg-white py-2 d-flex justify-content-between align-items-center small text-muted">
                            <span><i class="fas fa-info-circle text-info mr-1"></i> Masker merah adalah area watermark yang akan dihapus dan direkonstruksi.</span>
                            <span id="processTimeBadge" class="badge badge-light border">Waktu Proses: -</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- TAB 2: MODE MASSAL / BATCH (RUTINITAS BANYAK FOTO)                 -->
        <!-- =================================================================== -->
        <div class="tab-pane fade" id="paneBatchMode" role="tabpanel">
            <div class="row">
                <!-- Kolom Kiri: Unggah Batch & Konfigurasi Zona Seragam -->
                <div class="col-lg-5 col-xl-4 mb-3">
                    <!-- 1. Dropzone Batch Multi-Foto / Folder / ZIP -->
                    <div class="card shadow-sm border-0 rounded-lg mb-3">
                        <div class="card-header bg-white py-2 d-flex align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-dark small"><i class="fas fa-images text-primary mr-1"></i> 1. Unggah Banyak Foto / Folder</h6>
                            <span class="badge badge-primary badge-pill" id="batchCountBadge">0 Berkas</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="dropzone-area p-3 text-center border rounded-lg bg-light mb-2" id="batchDropzone" style="border: 2px dashed #0A66C2 !important; cursor: pointer;">
                                <i class="fas fa-folder-open fa-2x text-primary mb-1"></i>
                                <h6 class="font-weight-bold mb-1 text-dark small">Tarik Banyak Foto atau Satu Folder</h6>
                                <p class="text-muted mb-2" style="font-size: 11px;">Mendukung puluhan foto JPG, PNG, WebP, HEIC, folder, atau ZIP</p>
                                <div class="d-flex justify-content-center flex-wrap">
                                    <button type="button" class="btn btn-outline-primary btn-sm px-2 m-1 shadow-xs" onclick="$('#batchFileInput').click()">
                                        <i class="fas fa-images mr-1"></i> Pilih Foto
                                    </button>
                                    <button type="button" class="btn btn-outline-info btn-sm px-2 m-1 shadow-xs" onclick="$('#batchFolderInput').click()">
                                        <i class="fas fa-folder mr-1"></i> Pilih 1 Folder
                                    </button>
                                </div>
                                <input type="file" id="batchFileInput" multiple accept="image/jpeg,image/png,image/webp,image/heic,image/heif,.heic,.HEIC,.heif,.HEIF,.zip" class="d-none">
                                <input type="file" id="batchFolderInput" webkitdirectory directory multiple class="d-none">
                            </div>
                            <div id="batchSelectedList" class="alert alert-light border py-2 px-3 small text-muted mb-0" style="display: none; max-height: 120px; overflow-y: auto;"></div>
                        </div>
                    </div>

                    <!-- 2. Pengaturan Zona Watermark Seragam -->
                    <div class="card shadow-sm border-0 rounded-lg mb-3">
                        <div class="card-header bg-white py-2 d-flex align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-dark small"><i class="fas fa-sliders-h text-info mr-1"></i> 2. Zona Watermark Seragam</h6>
                            <span class="badge badge-info" id="batchPresetNameBadge">Kiri Bawah (TimeMark)</span>
                        </div>
                        <div class="card-body p-3">
                            <p class="text-muted small mb-2" style="font-size: 11.5px;">
                                Seluruh foto dalam batch akan dibersihkan pada area kotak yang sama (standar kamera TimeMark):
                            </p>
                            <div class="btn-group btn-group-sm w-100 mb-2">
                                <button type="button" class="btn btn-outline-secondary batch-preset-btn active" data-preset="bottom-left">Kiri Bawah</button>
                                <button type="button" class="btn btn-outline-secondary batch-preset-btn" data-preset="bottom-right">Kanan Bawah</button>
                                <button type="button" class="btn btn-outline-secondary batch-preset-btn" data-preset="bottom-strip">Tepi Bawah</button>
                                <button type="button" class="btn btn-outline-secondary batch-preset-btn" data-preset="top-left">Kiri Atas</button>
                            </div>

                            <div class="border rounded p-2 bg-light small mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted">Lebar Zona Watermark (%):</span>
                                    <strong id="batchWidthVal" class="text-dark">38%</strong>
                                </div>
                                <input type="range" class="custom-range" id="batchWidthRange" min="15" max="60" value="38">

                                <div class="d-flex justify-content-between align-items-center mt-2 mb-1">
                                    <span class="text-muted">Tinggi Zona Watermark (%):</span>
                                    <strong id="batchHeightVal" class="text-dark">32%</strong>
                                </div>
                                <input type="range" class="custom-range" id="batchHeightRange" min="10" max="60" value="32">
                            </div>

                            <!-- Mini Visualizer Posisi Masker -->
                            <div class="border rounded p-2 text-center bg-white">
                                <small class="text-muted d-block mb-1 font-weight-bold" style="font-size: 10px;">Simulasi Posisi Kotak Pembersih:</small>
                                <div id="batchMiniVisualizer" style="width: 140px; height: 80px; background: #334155; position: relative; margin: 0 auto; border-radius: 4px; overflow: hidden; border: 1px solid #94a3b8;">
                                    <div id="batchMiniBox" style="position: absolute; bottom: 0; left: 0; width: 38%; height: 32%; background: rgba(239, 68, 68, 0.7); border: 1px dashed #fff;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi Batch -->
                    <div class="mb-3">
                        <button type="button" class="btn btn-primary btn-block btn-lg shadow font-weight-bold py-2.5" id="btnStartBatch" disabled>
                            <i class="fas fa-bolt mr-1"></i> Bersihkan Semua Foto (Batch Inpaint)
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-block btn-sm mt-2" id="btnCancelBatch" style="display: none;">
                            <i class="fas fa-stop mr-1"></i> Hentikan Proses
                        </button>
                    </div>
                </div>

                <!-- Kolom Kanan: Progress Massal & Galeri Hasil -->
                <div class="col-lg-7 col-xl-8 mb-3">
                    <div class="card shadow-sm border-0 rounded-lg">
                        <div class="card-header bg-white py-2 d-flex align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-dark small">
                                <i class="fas fa-tasks text-success mr-1"></i> Status Pemrosesan Massal
                            </h6>
                            <button type="button" class="btn btn-success btn-sm font-weight-bold shadow-xs" id="btnDownloadAllZip" style="display: none;">
                                <i class="fas fa-file-archive mr-1"></i> Unduh Semua Hasil (ZIP)
                            </button>
                        </div>
                        <div class="card-body p-3">
                            <!-- Progress Container -->
                            <div id="batchProgressWrapper" class="mb-3" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="font-weight-bold text-dark small" id="batchCurrentFileText">Memproses foto...</span>
                                    <span class="badge badge-primary font-weight-bold" id="batchProgressPercent">0%</span>
                                </div>
                                <div class="progress" style="height: 18px; border-radius: 6px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" id="batchProgressBar" role="progressbar" style="width: 0%;"></div>
                                </div>
                                <div class="d-flex justify-content-between small text-muted mt-1">
                                    <span id="batchCountText">0 dari 0 selesai</span>
                                    <span id="batchSpeedText">-</span>
                                </div>
                            </div>

                            <!-- Placeholder Belum Ada Batch -->
                            <div id="batchPlaceholder" class="p-5 text-center text-muted">
                                <i class="fas fa-layer-group fa-4x mb-3 text-secondary opacity-50"></i>
                                <h5 class="font-weight-bold text-dark">Belum Ada Foto Batch</h5>
                                <p class="small text-muted mb-0">Pilih beberapa foto, folder, atau file ZIP di kolom kiri untuk mulai membersihkan secara massal.</p>
                            </div>

                            <!-- Galeri Hasil Pemrosesan Batch -->
                            <div id="batchResultsGrid" class="row" style="display: none; max-height: 520px; overflow-y: auto;">
                                <!-- Kartu item hasil pembersihan akan di-append di sini -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pratinjau Zoom Foto Hasil -->
<div class="modal fade" id="modalZoomPhoto" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white py-2">
                <h6 class="modal-title font-weight-bold small" id="modalZoomTitle">Pratinjau Foto</h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0 bg-black text-center position-relative">
                <img src="" id="modalZoomImg" class="img-fluid" style="max-height: 80vh; object-fit: contain;">
            </div>
            <div class="modal-footer bg-light py-2 justify-content-between">
                <small class="text-muted" id="modalZoomMeta">-</small>
                <a href="#" id="modalZoomDownload" class="btn btn-primary btn-sm px-3" download="foto_bersih.jpg">
                    <i class="fas fa-download mr-1"></i> Unduh Foto Ini
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.preset-btn.active, .batch-preset-btn.active {
    background-color: var(--app-primary, #0A66C2) !important;
    color: #fff !important;
    border-color: var(--app-primary, #0A66C2) !important;
}
.shadow-xs {
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
.cursor-pointer {
    cursor: pointer;
}
.result-card-item {
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.result-card-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12) !important;
}
</style>
<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script src="<?= esc(media_url('assets/adminlte/plugins/jszip/jszip.min.js')); ?>"></script>
<script src="<?= esc(media_url('assets/exif/exifr.full.umd.js')); ?>"></script>
<script src="<?= esc(media_url('assets/heic/heic2any.min.js')); ?>"></script>
<script>
$(function() {
    'use strict';

    // =========================================================================
    // INPAINTING ENGINE CORE (CLIENT-SIDE HTML5 CANVAS)
    // =========================================================================
    /**
     * Algoritma Multi-Pass Contextual Inpainting
     * Merekonstruksi area masker berdasarkan piksel batas sekitar dengan
     * interpolasi gradien multi-arah dan perataan tepi (feathering).
     */
    function runInpaintingOnCanvas(ctx, maskCtx, width, height, featherRadius) {
        var imgData = ctx.getImageData(0, 0, width, height);
        var maskData = maskCtx.getImageData(0, 0, width, height);
        var pixels = imgData.data;
        var mask = maskData.data;

        // 1. Cari Bounding Box area masker (di mana alpha/merah > 10)
        var minX = width, minY = height, maxX = 0, maxY = 0;
        var hasMask = false;

        for (var y = 0; y < height; y++) {
            var rowOffset = y * width * 4;
            for (var x = 0; x < width; x++) {
                var idx = rowOffset + (x * 4);
                if (mask[idx + 3] > 10 && mask[idx] > 10) { // Piksel masker merah
                    hasMask = true;
                    if (x < minX) minX = x;
                    if (x > maxX) maxX = x;
                    if (y < minY) minY = y;
                    if (y > maxY) maxY = y;
                }
            }
        }

        if (!hasMask) return; // Tidak ada area yang ditandai

        // Beri margin batas pencarian
        var pad = Math.max(20, Math.floor(featherRadius * 2));
        var bMinX = Math.max(0, minX - pad);
        var bMinY = Math.max(0, minY - pad);
        var bMaxX = Math.min(width - 1, maxX + pad);
        var bMaxY = Math.min(height - 1, maxY + pad);

        // 2. Buat matriks status: 1 = Mask (perlu diisi), 0 = Known (asli)
        var bW = bMaxX - bMinX + 1;
        var bH = bMaxY - bMinY + 1;
        var isMask = new Uint8Array(bW * bH);

        for (var by = 0; by < bH; by++) {
            var gy = bMinY + by;
            for (var bx = 0; bx < bW; bx++) {
                var gx = bMinX + bx;
                var gIdx = (gy * width + gx) * 4;
                if (mask[gIdx + 3] > 10 && mask[gIdx] > 10) {
                    isMask[by * bW + bx] = 1;
                }
            }
        }

        // 3. Multi-Pass Interpolation: Radial & Distance-Weighted Patch Blend
        // Kumpulkan piksel perimeter (batas tepi luar masker)
        var boundaryColors = [];
        for (var by = 0; by < bH; by++) {
            for (var bx = 0; bx < bW; bx++) {
                if (isMask[by * bW + bx] === 0) {
                    // Cek apakah bersebelahan dengan masker
                    var isNearMask = false;
                    for (var dy = -1; dy <= 1 && !isNearMask; dy++) {
                        for (var dx = -1; dx <= 1 && !isNearMask; dx++) {
                            var ny = by + dy, nx = bx + dx;
                            if (nx >= 0 && nx < bW && ny >= 0 && ny < bH) {
                                if (isMask[ny * bW + nx] === 1) isNearMask = true;
                            }
                        }
                    }
                    if (isNearMask) {
                        var gx = bMinX + bx, gy = bMinY + by;
                        var gIdx = (gy * width + gx) * 4;
                        boundaryColors.push({
                            x: bx, y: by,
                            r: pixels[gIdx], g: pixels[gIdx + 1], b: pixels[gIdx + 2]
                        });
                    }
                }
            }
        }

        if (boundaryColors.length === 0) return;

        // Salinan warna asli untuk blending tepi
        var origPixels = new Uint8ClampedArray(pixels);

        // Rekonstruksi setiap piksel masker
        for (var by = 0; by < bH; by++) {
            var gy = bMinY + by;
            for (var bx = 0; bx < bW; bx++) {
                if (isMask[by * bW + bx] === 1) {
                    var gx = bMinX + bx;
                    var gIdx = (gy * width + gx) * 4;

                    var totalWeight = 0;
                    var rSum = 0, gSum = 0, bSum = 0;
                    var minDist = 999999;

                    // Interpolasi berbobot jarak ke titik batas terdekat
                    for (var i = 0; i < boundaryColors.length; i++) {
                        var bc = boundaryColors[i];
                        var ddx = bx - bc.x;
                        var ddy = by - bc.y;
                        var distSq = ddx * ddx + ddy * ddy;
                        if (distSq < minDist) minDist = distSq;

                        // Bobot kuadrat jarak terbalik
                        var w = 1.0 / (distSq + 1.0);
                        totalWeight += w;
                        rSum += bc.r * w;
                        gSum += bc.g * w;
                        bSum += bc.b * w;
                    }

                    var newR = rSum / totalWeight;
                    var newG = gSum / totalWeight;
                    var newB = bSum / totalWeight;

                    // Sedikit variasi tekstur halus (subtle texture noise) agar tidak terlihat terlalu flat
                    var subtleNoise = ((Math.random() - 0.5) * 6);
                    newR = Math.max(0, Math.min(255, newR + subtleNoise));
                    newG = Math.max(0, Math.min(255, newG + subtleNoise));
                    newB = Math.max(0, Math.min(255, newB + subtleNoise));

                    // Baurkan tepi luar masker dengan piksel asli (Feathering)
                    var dist = Math.sqrt(minDist);
                    if (dist < featherRadius) {
                        var alphaBlend = dist / featherRadius;
                        // Smoothstep curve
                        alphaBlend = alphaBlend * alphaBlend * (3 - 2 * alphaBlend);
                        pixels[gIdx] = Math.round(newR * alphaBlend + origPixels[gIdx] * (1 - alphaBlend));
                        pixels[gIdx + 1] = Math.round(newG * alphaBlend + origPixels[gIdx + 1] * (1 - alphaBlend));
                        pixels[gIdx + 2] = Math.round(newB * alphaBlend + origPixels[gIdx + 2] * (1 - alphaBlend));
                    } else {
                        pixels[gIdx] = Math.round(newR);
                        pixels[gIdx + 1] = Math.round(newG);
                        pixels[gIdx + 2] = Math.round(newB);
                    }
                }
            }
        }

        ctx.putImageData(imgData, 0, 0);
    }

    // =========================================================================
    // STATE & VARIABEL GLOBAL
    // =========================================================================
    var currentSingleImage = null;
    var originalSingleCanvas = null;
    var isDrawing = false;
    var activeTool = 'brush'; // 'brush' | 'eraser'
    var activePreset = 'bottom-left';
    var brushSize = 35;
    var featherSize = 6;
    var splitSliderPos = 0.5;

    // Batch State
    var batchFiles = [];
    var batchIsRunning = false;
    var batchCancelled = false;
    var batchResults = [];
    var activeBatchPreset = 'bottom-left';

    // =========================================================================
    // MODE SATUAN: KANVAS & KUAS STUDIO
    // =========================================================================
    var mainCanvas = document.getElementById('mainCanvas');
    var maskCanvas = document.getElementById('maskCanvas');
    var mainCtx = mainCanvas.getContext('2d');
    var maskCtx = maskCanvas.getContext('2d');

    // Preset Area Watermark Generator
    function applyPresetZone(preset, pWidthPct, pHeightPct) {
        if (!currentSingleImage) return;

        var w = maskCanvas.width;
        var h = maskCanvas.height;
        maskCtx.clearRect(0, 0, w, h);

        var zoneW = Math.round(w * (pWidthPct / 100));
        var zoneH = Math.round(h * (pHeightPct / 100));

        maskCtx.fillStyle = 'rgba(239, 68, 68, 0.7)'; // Merah transparan

        if (preset === 'bottom-left') {
            maskCtx.fillRect(0, h - zoneH, zoneW, zoneH);
        } else if (preset === 'bottom-right') {
            maskCtx.fillRect(w - zoneW, h - zoneH, zoneW, zoneH);
        } else if (preset === 'bottom-strip') {
            var stripH = Math.round(h * 0.18);
            maskCtx.fillRect(0, h - stripH, w, stripH);
        } else if (preset === 'top-left') {
            maskCtx.fillRect(0, 0, zoneW, zoneH);
        } else if (preset === 'top-right') {
            maskCtx.fillRect(w - zoneW, 0, zoneW, zoneH);
        }
    }

    // Load Image into Canvas
    function loadSingleImage(file) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var img = new Image();
            img.onload = function() {
                currentSingleImage = img;

                mainCanvas.width = img.naturalWidth;
                mainCanvas.height = img.naturalHeight;
                maskCanvas.width = img.naturalWidth;
                maskCanvas.height = img.naturalHeight;

                mainCtx.drawImage(img, 0, 0);
                maskCtx.clearRect(0, 0, maskCanvas.width, maskCanvas.height);

                // Simpan backup foto asli untuk perbandingan
                originalSingleCanvas = document.createElement('canvas');
                originalSingleCanvas.width = img.naturalWidth;
                originalSingleCanvas.height = img.naturalHeight;
                originalSingleCanvas.getContext('2d').drawImage(img, 0, 0);

                $('#singlePlaceholder').hide();
                $('#canvasViewportWrapper').fadeIn(200);
                $('#viewModeToggle').show();
                $('#btnDownloadSingle').hide();

                var mb = (file.size / (1024 * 1024)).toFixed(2);
                $('#singleImgInfo').text(file.name + ' (' + mb + ' MB)');
                $('#canvasMetaInfo').text('Resolusi: ' + img.naturalWidth + ' × ' + img.naturalHeight + ' px');

                // Terapkan preset aktif otomatis
                if (activePreset !== 'manual') {
                    var pW = parseInt($('#presetWidthRange').val(), 10) || 38;
                    var pH = parseInt($('#presetHeightRange').val(), 10) || 32;
                    applyPresetZone(activePreset, pW, pH);
                }
            };
            img.src = e.target.result;
        };

        // Deteksi berkas HEIC iPhone
        var ext = file.name.split('.').pop().toLowerCase();
        if ((ext === 'heic' || ext === 'heif') && typeof heic2any !== 'undefined') {
            heic2any({ blob: file, toType: 'image/jpeg', quality: 0.95 }).then(function(resBlob) {
                reader.readAsDataURL(resBlob);
            }).catch(function() {
                reader.readAsDataURL(file);
            });
        } else {
            reader.readAsDataURL(file);
        }
    }

    // Event Input Berkas Satuan
    $('#singleFileInput').on('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            loadSingleImage(e.target.files[0]);
        }
    });

    // Dropzone Satuan Drag & Drop
    var $singleDrop = $('#singleDropzone');
    $singleDrop.on('dragover dragenter', function(e) {
        e.preventDefault(); e.stopPropagation();
        $singleDrop.addClass('bg-white');
    });
    $singleDrop.on('dragleave drop', function(e) {
        e.preventDefault(); e.stopPropagation();
        $singleDrop.removeClass('bg-white');
        if (e.type === 'drop') {
            var dt = e.originalEvent.dataTransfer;
            if (dt && dt.files && dt.files[0]) {
                loadSingleImage(dt.files[0]);
            }
        }
    });

    // Preset Buttons Click
    $('.preset-btn').on('click', function() {
        $('.preset-btn').removeClass('active');
        $(this).addClass('active');

        activePreset = $(this).data('preset');
        var labels = {
            'bottom-left': 'Kiri Bawah (TimeMark)',
            'bottom-right': 'Kanan Bawah',
            'bottom-strip': 'Tepi Bawah Penuh',
            'top-left': 'Kiri Atas',
            'manual': 'Kuas Bebas'
        };
        $('#activePresetBadge').text(labels[activePreset] || activePreset);

        if (activePreset === 'manual') {
            $('#presetSlidersBox').slideUp(150);
        } else {
            $('#presetSlidersBox').slideDown(150);
            var pW = parseInt($('#presetWidthRange').val(), 10) || 38;
            var pH = parseInt($('#presetHeightRange').val(), 10) || 32;
            applyPresetZone(activePreset, pW, pH);
        }
    });

    // Sliders Preset Satuan
    $('#presetWidthRange').on('input', function() {
        var v = $(this).val();
        $('#presetWidthVal').text(v + '%');
        if (activePreset !== 'manual') {
            applyPresetZone(activePreset, parseInt(v, 10), parseInt($('#presetHeightRange').val(), 10));
        }
    });
    $('#presetHeightRange').on('input', function() {
        var v = $(this).val();
        $('#presetHeightVal').text(v + '%');
        if (activePreset !== 'manual') {
            applyPresetZone(activePreset, parseInt($('#presetWidthRange').val(), 10), parseInt(v, 10));
        }
    });

    // Tool Brush vs Eraser
    $('#btnToolBrush').on('click', function() {
        activeTool = 'brush';
        $(this).addClass('active btn-primary').removeClass('btn-outline-secondary');
        $('#btnToolEraser').removeClass('active btn-primary').addClass('btn-outline-secondary');
    });
    $('#btnToolEraser').on('click', function() {
        activeTool = 'eraser';
        $(this).addClass('active btn-primary').removeClass('btn-outline-secondary');
        $('#btnToolBrush').removeClass('active btn-primary').addClass('btn-outline-secondary');
    });

    $('#brushSizeRange').on('input', function() {
        brushSize = parseInt($(this).val(), 10);
        $('#brushSizeVal').text(brushSize + ' px');
    });
    $('#featherRange').on('input', function() {
        featherSize = parseInt($(this).val(), 10);
        $('#featherVal').text(featherSize + ' px');
    });

    $('#btnClearMask').on('click', function() {
        if (!currentSingleImage) return;
        maskCtx.clearRect(0, 0, maskCanvas.width, maskCanvas.height);
    });

    $('#btnResetSingle').on('click', function() {
        if (!currentSingleImage || !originalSingleCanvas) return;
        mainCtx.clearRect(0, 0, mainCanvas.width, mainCanvas.height);
        mainCtx.drawImage(originalSingleCanvas, 0, 0);
        $('#btnDownloadSingle').hide();
    });

    // Drawing Interaction pada Mask Canvas
    function getCanvasCoords(e) {
        var rect = maskCanvas.getBoundingClientRect();
        var clientX = e.clientX || (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
        var clientY = e.clientY || (e.touches && e.touches[0] ? e.touches[0].clientY : 0);

        var scaleX = maskCanvas.width / rect.width;
        var scaleY = maskCanvas.height / rect.height;

        return {
            x: (clientX - rect.left) * scaleX,
            y: (clientY - rect.top) * scaleY
        };
    }

    function drawStroke(x, y) {
        maskCtx.beginPath();
        maskCtx.arc(x, y, brushSize / 2, 0, Math.PI * 2);
        if (activeTool === 'brush') {
            maskCtx.fillStyle = 'rgba(239, 68, 68, 0.7)';
            maskCtx.globalCompositeOperation = 'source-over';
            maskCtx.fill();
        } else {
            maskCtx.globalCompositeOperation = 'destination-out';
            maskCtx.fill();
            maskCtx.globalCompositeOperation = 'source-over';
        }
    }

    $(maskCanvas).on('mousedown touchstart', function(e) {
        isDrawing = true;
        var coords = getCanvasCoords(e);
        drawStroke(coords.x, coords.y);
    });
    $(window).on('mousemove touchmove', function(e) {
        if (!isDrawing) return;
        var coords = getCanvasCoords(e);
        drawStroke(coords.x, coords.y);
    });
    $(window).on('mouseup touchend', function() {
        isDrawing = false;
    });

    // Eksekusi Pembersihan Satuan
    $('#btnProcessSingle').on('click', function() {
        if (!currentSingleImage) {
            Swal.fire({ icon: 'warning', title: 'Belum Ada Foto', text: 'Silakan pilih foto dokumentasi terlebih dahulu.' });
            return;
        }

        $('#inpaintLoadingOverlay').css('display', 'flex');
        var t0 = performance.now();

        setTimeout(function() {
            try {
                runInpaintingOnCanvas(mainCtx, maskCtx, mainCanvas.width, mainCanvas.height, featherSize);
                // Bersihkan masker setelah proses
                maskCtx.clearRect(0, 0, maskCanvas.width, maskCanvas.height);

                var t1 = performance.now();
                var durationSec = ((t1 - t0) / 1000).toFixed(2);
                $('#processTimeBadge').text('Waktu Proses: ' + durationSec + ' detik');

                $('#btnDownloadSingle').fadeIn(200);

                Swal.fire({
                    icon: 'success',
                    title: 'Watermark Berhasil Dibersihkan!',
                    text: 'Area watermark telah direkonstruksi dalam ' + durationSec + ' detik.',
                    timer: 2000,
                    showConfirmButton: false
                });
            } catch (err) {
                console.error(err);
                Swal.fire({ icon: 'error', title: 'Gagal Memproses', text: 'Terjadi kendala saat merekonstruksi kanvas: ' + err.message });
            } finally {
                $('#inpaintLoadingOverlay').hide();
            }
        }, 50);
    });

    // Unduh Hasil Satuan
    $('#btnDownloadSingle').on('click', function() {
        if (!currentSingleImage) return;
        var link = document.createElement('a');
        link.download = 'foto_bersih_' + Date.now() + '.jpg';
        link.href = mainCanvas.toDataURL('image/jpeg', 0.94);
        link.click();
    });

    // View Mode Toggle (Hasil Bersih vs Asli)
    $('#lblViewClean').on('click', function() {
        $('#splitSliderDivider').hide();
    });
    $('#lblViewOriginal').on('click', function() {
        $('#splitSliderDivider').hide();
        if (originalSingleCanvas) {
            mainCtx.clearRect(0, 0, mainCanvas.width, mainCanvas.height);
            mainCtx.drawImage(originalSingleCanvas, 0, 0);
        }
    });

    // =========================================================================
    // MODE MASSAL / BATCH (RUTINITAS BANYAK FOTO)
    // =========================================================================
    function updateBatchCountUI() {
        var count = batchFiles.length;
        $('#batchCountBadge').text(count + ' Berkas');
        $('#btnStartBatch').prop('disabled', count === 0);

        if (count > 0) {
            $('#batchSelectedList').show().empty();
            var ul = $('<ul class="pl-3 mb-0">');
            for (var i = 0; i < Math.min(count, 8); i++) {
                ul.append($('<li>').text(batchFiles[i].name));
            }
            if (count > 8) {
                ul.append($('<li>').addClass('font-italic text-primary').text('... dan ' + (count - 8) + ' berkas lainnya'));
            }
            $('#batchSelectedList').append(ul);
        } else {
            $('#batchSelectedList').hide().empty();
        }
    }

    function addBatchFiles(fileList) {
        for (var i = 0; i < fileList.length; i++) {
            var f = fileList[i];
            var ext = f.name.split('.').pop().toLowerCase();
            if (['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'].indexOf(ext) !== -1) {
                batchFiles.push(f);
            }
        }
        updateBatchCountUI();
    }

    $('#batchFileInput').on('change', function(e) {
        if (e.target.files) addBatchFiles(e.target.files);
    });
    $('#batchFolderInput').on('change', function(e) {
        if (e.target.files) addBatchFiles(e.target.files);
    });

    var $batchDrop = $('#batchDropzone');
    $batchDrop.on('dragover dragenter', function(e) {
        e.preventDefault(); e.stopPropagation();
        $batchDrop.addClass('bg-white');
    });
    $batchDrop.on('dragleave drop', function(e) {
        e.preventDefault(); e.stopPropagation();
        $batchDrop.removeClass('bg-white');
        if (e.type === 'drop') {
            var dt = e.originalEvent.dataTransfer;
            if (dt && dt.files) {
                addBatchFiles(dt.files);
            }
        }
    });

    // Preset Batch Buttons
    $('.batch-preset-btn').on('click', function() {
        $('.batch-preset-btn').removeClass('active');
        $(this).addClass('active');

        activeBatchPreset = $(this).data('preset');
        var labels = {
            'bottom-left': 'Kiri Bawah (TimeMark)',
            'bottom-right': 'Kanan Bawah',
            'bottom-strip': 'Tepi Bawah Penuh',
            'top-left': 'Kiri Atas'
        };
        $('#batchPresetNameBadge').text(labels[activeBatchPreset] || activeBatchPreset);
        updateBatchMiniVisualizer();
    });

    function updateBatchMiniVisualizer() {
        var w = parseInt($('#batchWidthRange').val(), 10) || 38;
        var h = parseInt($('#batchHeightRange').val(), 10) || 32;

        var $box = $('#batchMiniBox');
        $box.css({ width: w + '%', height: h + '%', top: 'auto', bottom: 'auto', left: 'auto', right: 'auto' });

        if (activeBatchPreset === 'bottom-left') {
            $box.css({ bottom: 0, left: 0 });
        } else if (activeBatchPreset === 'bottom-right') {
            $box.css({ bottom: 0, right: 0 });
        } else if (activeBatchPreset === 'bottom-strip') {
            $box.css({ bottom: 0, left: 0, width: '100%', height: '20%' });
        } else if (activeBatchPreset === 'top-left') {
            $box.css({ top: 0, left: 0 });
        }
    }

    $('#batchWidthRange').on('input', function() {
        $('#batchWidthVal').text($(this).val() + '%');
        updateBatchMiniVisualizer();
    });
    $('#batchHeightRange').on('input', function() {
        $('#batchHeightVal').text($(this).val() + '%');
        updateBatchMiniVisualizer();
    });

    // Eksekusi Pembersihan Massal / Batch
    $('#btnStartBatch').on('click', function() {
        if (batchFiles.length === 0) return;

        batchIsRunning = true;
        batchCancelled = false;
        batchResults = [];

        $('#btnStartBatch').hide();
        $('#btnCancelBatch').show();
        $('#batchPlaceholder').hide();
        $('#batchProgressWrapper').fadeIn(200);
        $('#batchResultsGrid').empty().show();
        $('#btnDownloadAllZip').hide();

        var total = batchFiles.length;
        var currentIndex = 0;
        var startTime = performance.now();

        var pW = parseInt($('#batchWidthRange').val(), 10) || 38;
        var pH = parseInt($('#batchHeightRange').val(), 10) || 32;

        function processNext() {
            if (batchCancelled || currentIndex >= total) {
                finishBatch();
                return;
            }

            var file = batchFiles[currentIndex];
            var percent = Math.round(((currentIndex + 1) / total) * 100);

            $('#batchProgressBar').css('width', percent + '%');
            $('#batchProgressPercent').text(percent + '%');
            $('#batchCurrentFileText').text('Memproses: ' + file.name);
            $('#batchCountText').text((currentIndex + 1) + ' dari ' + total + ' selesai');

            // Hitung kecepatan estimasi
            var elapsedSec = (performance.now() - startTime) / 1000;
            var secPerImg = elapsedSec / (currentIndex + 1);
            var remainSec = Math.round(secPerImg * (total - currentIndex - 1));
            $('#batchSpeedText').text('Sisa waktu: ~' + remainSec + ' dtk');

            // Baca & Inpaint
            var reader = new FileReader();
            reader.onload = function(e) {
                var img = new Image();
                img.onload = function() {
                    var offCanvas = document.createElement('canvas');
                    var offMask = document.createElement('canvas');
                    offCanvas.width = img.naturalWidth;
                    offCanvas.height = img.naturalHeight;
                    offMask.width = img.naturalWidth;
                    offMask.height = img.naturalHeight;

                    var oCtx = offCanvas.getContext('2d');
                    var mCtx = offMask.getContext('2d');

                    oCtx.drawImage(img, 0, 0);

                    // Buat masker sesuai preset
                    var zW = Math.round(img.naturalWidth * (pW / 100));
                    var zH = Math.round(img.naturalHeight * (pH / 100));
                    mCtx.fillStyle = 'rgba(239, 68, 68, 1)';

                    if (activeBatchPreset === 'bottom-left') {
                        mCtx.fillRect(0, img.naturalHeight - zH, zW, zH);
                    } else if (activeBatchPreset === 'bottom-right') {
                        mCtx.fillRect(img.naturalWidth - zW, img.naturalHeight - zH, zW, zH);
                    } else if (activeBatchPreset === 'bottom-strip') {
                        mCtx.fillRect(0, img.naturalHeight - Math.round(img.naturalHeight * 0.18), img.naturalWidth, Math.round(img.naturalHeight * 0.18));
                    } else if (activeBatchPreset === 'top-left') {
                        mCtx.fillRect(0, 0, zW, zH);
                    }

                    // Jalankan Inpainting
                    runInpaintingOnCanvas(oCtx, mCtx, img.naturalWidth, img.naturalHeight, featherSize);

                    var cleanDataUrl = offCanvas.toDataURL('image/jpeg', 0.93);
                    var cleanFileName = file.name.replace(/\.[^/.]+$/, '') + '_bersih.jpg';

                    batchResults.push({
                        name: cleanFileName,
                        origName: file.name,
                        dataUrl: cleanDataUrl,
                        width: img.naturalWidth,
                        height: img.naturalHeight
                    });

                    // Append ke Grid Hasil
                    appendBatchCard(cleanFileName, file.name, cleanDataUrl, img.naturalWidth, img.naturalHeight);

                    currentIndex++;
                    setTimeout(processNext, 20); // Beri jeda 20ms agar browser render lancar
                };
                img.src = e.target.result;
            };

            var ext = file.name.split('.').pop().toLowerCase();
            if ((ext === 'heic' || ext === 'heif') && typeof heic2any !== 'undefined') {
                heic2any({ blob: file, toType: 'image/jpeg', quality: 0.95 }).then(function(b) {
                    reader.readAsDataURL(b);
                }).catch(function() {
                    reader.readAsDataURL(file);
                });
            } else {
                reader.readAsDataURL(file);
            }
        }

        processNext();
    });

    function appendBatchCard(cleanName, origName, dataUrl, w, h) {
        var col = $('<div>').addClass('col-6 col-md-4 col-xl-3 mb-3');
        var card = $('<div>').addClass('card shadow-xs border h-100 p-2 text-center result-card-item');

        var imgEl = $('<img>').addClass('img-fluid rounded mb-2 cursor-pointer')
            .attr('src', dataUrl)
            .css({ height: '110px', width: '100%', objectFit: 'cover' })
            .on('click', function() {
                $('#modalZoomTitle').text(cleanName);
                $('#modalZoomImg').attr('src', dataUrl);
                $('#modalZoomMeta').text(w + ' × ' + h + ' px | Asli: ' + origName);
                $('#modalZoomDownload').attr('href', dataUrl).attr('download', cleanName);
                $('#modalZoomPhoto').modal('show');
            });

        var title = $('<div>').addClass('small font-weight-bold text-truncate text-dark mb-1')
            .attr('title', cleanName)
            .text(cleanName);

        var meta = $('<small>').addClass('text-muted d-block mb-2').text(w + ' × ' + h);

        var btnDown = $('<a>').addClass('btn btn-outline-primary btn-xs btn-block')
            .attr('href', dataUrl)
            .attr('download', cleanName)
            .html('<i class="fas fa-download mr-1"></i> Unduh');

        card.append(imgEl).append(title).append(meta).append(btnDown);
        col.append(card);
        $('#batchResultsGrid').append(col);
    }

    function finishBatch() {
        batchIsRunning = false;
        $('#btnStartBatch').show();
        $('#btnCancelBatch').hide();
        $('#batchCurrentFileText').text('Selesai!');
        $('#batchProgressBar').removeClass('progress-bar-animated');

        if (batchResults.length > 0) {
            $('#btnDownloadAllZip').fadeIn(200);

            // Notifikasi Tunggal (Rule #1: tidak double)
            Swal.fire({
                icon: 'success',
                title: 'Pemrosesan Selesai!',
                text: batchResults.length + ' foto berhasil dibersihkan watermark-nya.',
                timer: 2500,
                showConfirmButton: false
            });
        }
    }

    $('#btnCancelBatch').on('click', function() {
        batchCancelled = true;
    });

    // Download Semua Foto Massal Sebagai ZIP (Client-Side JSZip)
    $('#btnDownloadAllZip').on('click', function() {
        if (batchResults.length === 0 || typeof JSZip === 'undefined') return;

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengompres ZIP...');

        var zip = new JSZip();

        for (var i = 0; i < batchResults.length; i++) {
            var item = batchResults[i];
            var base64Data = item.dataUrl.replace(/^data:image\/(png|jpeg|jpg);base64,/, '');
            zip.file(item.name, base64Data, { base64: true });
        }

        zip.generateAsync({ type: 'blob' }).then(function(content) {
            var a = document.createElement('a');
            a.download = 'foto_bersih_batch_' + Date.now() + '.zip';
            a.href = URL.createObjectURL(content);
            a.click();
            $btn.prop('disabled', false).html('<i class="fas fa-file-archive mr-1"></i> Unduh Semua Hasil (ZIP)');
        }).catch(function(err) {
            console.error(err);
            $btn.prop('disabled', false).html('<i class="fas fa-file-archive mr-1"></i> Unduh Semua Hasil (ZIP)');
            Swal.fire({ icon: 'error', title: 'Gagal Membuat ZIP', text: err.message });
        });
    });
});
</script>
<?= $this->endSection(); ?>

