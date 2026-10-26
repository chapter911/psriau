<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<link rel="stylesheet" href="<?= esc(media_url('assets/leaflet/leaflet.css')); ?>">
<div class="row mb-3">
    <div class="col-12">
        <div class="card card-outline card-primary shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fas fa-camera text-primary mr-2"></i> Watermark Foto (TimeMark Camera Style)
                    </h3>
                    <div class="text-muted small mt-1">
                        Sematkan stempel waktu, tanggal, lokasi GPS, dan identitas instansi ke foto dokumentasi lapangan secara otomatis dengan dukungan upload massal & ZIP.
                    </div>
                </div>
                <div>
                    <span class="badge badge-primary px-3 py-2"><i class="fas fa-shield-alt mr-1"></i> Hak Akses: <?= !empty($permissions['add']) || !empty($permissions['edit']) ? 'Pemroses Aktif' : 'Hanya Lihat' ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="watermarkForm" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row">
        <!-- KOLOM KIRI: KONFIGURASI WATERMARK -->
        <div class="col-lg-6">
            <!-- 1. PILIHAN TEMPLATE TIMEMARK -->
            <div class="card card-outline card-info shadow-sm mb-3">
                <div class="card-header bg-light py-2">
                    <h5 class="card-title font-weight-bold text-dark mb-0" style="font-size: 1rem;">
                        <i class="fas fa-palette text-info mr-2"></i> 1. Pilih Format Watermark TimeMark
                    </h5>
                </div>
                <div class="card-body p-3">
                    <div class="row">
                        <!-- Template 1: Classic -->
                        <div class="col-6 mb-2">
                            <label class="template-card active w-100 p-2 border rounded text-left cursor-pointer position-relative mb-0" data-val="classic">
                                <input type="radio" name="template" value="classic" checked class="d-none">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="font-weight-bold text-primary small"><i class="fas fa-signature mr-1"></i> Klasik TimeMark</span>
                                    <span class="badge badge-primary check-badge"><i class="fas fa-check"></i></span>
                                </div>
                                <div class="template-mini-preview bg-dark text-white p-2 rounded small" style="font-size: 10px; line-height: 1.2;">
                                    <div class="text-warning font-weight-bold" style="font-size: 14px;">15:06 <span class="text-white" style="font-size: 10px;">| 26/10/2026</span></div>
                                    <div class="text-truncate text-light mt-1"><i class="fas fa-map-marker-alt text-danger"></i> Kantor Balai PPS</div>
                                </div>
                                <small class="text-muted d-block mt-1" style="font-size: 11px;">Gaya khas PU: Jam besar, garis kuning, kapsul logo.</small>
                            </label>
                        </div>

                        <!-- Template 2: Table / Grid -->
                        <div class="col-6 mb-2">
                            <label class="template-card w-100 p-2 border rounded text-left cursor-pointer position-relative mb-0" data-val="table">
                                <input type="radio" name="template" value="table" class="d-none">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="font-weight-bold text-dark small"><i class="fas fa-table mr-1"></i> Grid Inspeksi</span>
                                    <span class="badge badge-secondary check-badge d-none"><i class="fas fa-check"></i></span>
                                </div>
                                <div class="template-mini-preview bg-dark text-white p-2 rounded small" style="font-size: 10px; line-height: 1.2;">
                                    <div class="border-bottom border-secondary pb-1 font-weight-bold text-info">PU SATKER RIAU</div>
                                    <div class="d-flex justify-content-between text-warning pt-1"><span>WAKTU</span><span>15:06 WIB</span></div>
                                    <div class="d-flex justify-content-between text-light"><span>LOKASI</span><span class="text-truncate">Pekanbaru</span></div>
                                </div>
                                <small class="text-muted d-block mt-1" style="font-size: 11px;">Tabel resmi kotak inspeksi proyek konstruksi.</small>
                            </label>
                        </div>

                        <!-- Template 3: Badge / Modern Card -->
                        <div class="col-6 mb-2">
                            <label class="template-card w-100 p-2 border rounded text-left cursor-pointer position-relative mb-0" data-val="badge">
                                <input type="radio" name="template" value="badge" class="d-none">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="font-weight-bold text-dark small"><i class="fas fa-id-badge mr-1"></i> Modern Badge</span>
                                    <span class="badge badge-secondary check-badge d-none"><i class="fas fa-check"></i></span>
                                </div>
                                <div class="template-mini-preview bg-dark text-white p-2 rounded small" style="font-size: 10px; line-height: 1.2;">
                                    <div class="badge badge-warning text-dark mb-1" style="font-size: 8px;">KEMENTERIAN PU</div>
                                    <div class="font-weight-bold text-white" style="font-size: 14px;">15:06</div>
                                    <div class="text-muted" style="font-size: 9px;"><i class="fas fa-map-pin text-danger"></i> Pekanbaru, Riau</div>
                                </div>
                                <small class="text-muted d-block mt-1" style="font-size: 11px;">Kartu melayang modern di sudut foto.</small>
                            </label>
                        </div>

                        <!-- Template 4: Clean Bottom Strip -->
                        <div class="col-6 mb-2">
                            <label class="template-card w-100 p-2 border rounded text-left cursor-pointer position-relative mb-0" data-val="strip">
                                <input type="radio" name="template" value="strip" class="d-none">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="font-weight-bold text-dark small"><i class="fas fa-bars mr-1"></i> Bottom Strip</span>
                                    <span class="badge badge-secondary check-badge d-none"><i class="fas fa-check"></i></span>
                                </div>
                                <div class="template-mini-preview bg-dark text-white p-2 rounded small" style="font-size: 10px; line-height: 1.2;">
                                    <div class="border-top border-warning pt-1 d-flex justify-content-between align-items-center">
                                        <div class="font-weight-bold text-warning" style="font-size: 12px;">15:06</div>
                                        <div class="text-truncate text-light ml-1" style="font-size: 9px;">PPS Riau</div>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-1" style="font-size: 11px;">Banner hitam elegan di sepanjang tepi bawah.</small>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. UPLOAD FOTO / MULTI-FOTO / ZIP -->
            <div class="card card-outline card-primary shadow-sm mb-3">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <h5 class="card-title font-weight-bold text-dark mb-0" style="font-size: 1rem;">
                        <i class="fas fa-cloud-upload-alt text-primary mr-2"></i> 2. Unggah Foto atau Arsip ZIP
                    </h5>
                    <span class="badge badge-secondary" id="fileCountBadge">Belum ada file</span>
                </div>
                <div class="card-body p-3">
                    <div class="upload-dropzone border border-primary border-dashed rounded p-4 text-center bg-light cursor-pointer" id="dropzoneArea">
                        <i class="fas fa-images fa-3x text-primary mb-2"></i>
                        <h6 class="font-weight-bold mb-1">Pilih Foto atau Tarik (Drag & Drop) File ke Sini</h6>
                        <p class="text-muted small mb-2">Mendukung format <strong>JPG, PNG, WebP, HEIC/HEIF (iPhone)</strong>, multi-foto, atau file <strong>.ZIP</strong></p>
                        <button type="button" class="btn btn-outline-primary btn-sm px-3" onclick="$('#fileInput').click()">
                            <i class="fas fa-folder-open mr-1"></i> Jelajahi File Komputer
                        </button>
                        <input type="file" id="fileInput" name="foto_upload[]" multiple accept="image/jpeg,image/png,image/webp,image/heic,image/heif,.heic,.HEIC,.heif,.HEIF,.zip" class="d-none">
                    </div>
                    <div id="fileSelectedList" class="mt-2 text-muted small" style="display: none;"></div>
                    <small class="text-muted d-block mt-2">
                        <i class="fas fa-info-circle text-info mr-1"></i> Jika mengunggah file ZIP, seluruh foto di dalam arsip akan diekstrak dan diberi watermark secara otomatis.
                    </small>
                </div>
            </div>

            <!-- 3. DETEKSI WAKTU EXIF & PENYESUAIAN JAM (OFFSET) -->
            <div class="card card-outline card-warning shadow-sm mb-3">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <h5 class="card-title font-weight-bold text-dark mb-0" style="font-size: 1rem;">
                        <i class="fas fa-clock text-warning mr-2"></i> 3. Pengaturan Waktu & Penyesuaian (+Jam)
                    </h5>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="useExifTime" name="use_exif_time" value="1" checked>
                        <label class="custom-control-label font-weight-bold small text-dark" for="useExifTime">Ambil Waktu dari EXIF Foto</label>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="alert alert-info py-2 px-3 small mb-3" id="exifStatusAlert">
                        <i class="fas fa-magic mr-1"></i> <strong>Deteksi Otomatis:</strong> Sistem akan membaca waktu pengambilan asli langsung dari metadata EXIF tiap file foto.
                    </div>

                    <!-- OFFSET BUTTONS -->
                    <label class="font-weight-bold small text-dark mb-1">
                        Penyesuaian Jam (Offset):
                        <span class="badge badge-info ml-1" id="currentOffsetBadge">+0 Jam</span>
                    </label>
                    <div class="btn-group btn-group-toggle w-100 mb-3 flex-wrap" data-toggle="buttons">
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

                    <!-- Custom Offset Input -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="offsetHours" class="small text-muted mb-1">Offset Jam Kustom</label>
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control" id="offsetHours" name="offset_hours" value="0" step="1">
                                <div class="input-group-append"><span class="input-group-text">Jam</span></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="offsetMinutes" class="small text-muted mb-1">Offset Menit Kustom</label>
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control" id="offsetMinutes" name="offset_minutes" value="0" step="1" min="-59" max="59">
                                <div class="input-group-append"><span class="input-group-text">Menit</span></div>
                            </div>
                        </div>
                    </div>

                    <!-- Manual Date & Time Fallback -->
                    <div class="border rounded p-2 bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small font-weight-bold text-dark"><i class="fas fa-edit mr-1"></i> Waktu / Tanggal Manual (Fallback jika EXIF kosong)</span>
                            <button type="button" class="btn btn-link btn-sm p-0 text-primary small" id="btnNow">Set Sekarang</button>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label for="manualDate" class="small text-muted mb-1">Tanggal</label>
                                <input type="date" class="form-control form-control-sm" id="manualDate" name="manual_date" value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="manualTime" class="small text-muted mb-1">Jam (HH:MM)</label>
                                <input type="time" class="form-control form-control-sm" id="manualTime" name="manual_time" value="<?= date('H:i') ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. LOKASI, KOORDINAT, CATATAN & LOGO -->
            <div class="card card-outline card-secondary shadow-sm mb-3">
                <div class="card-header bg-light py-2">
                    <h5 class="card-title font-weight-bold text-dark mb-0" style="font-size: 1rem;">
                        <i class="fas fa-map-marker-alt text-danger mr-2"></i> 4. Informasi Lokasi & Keterangan Lapangan
                    </h5>
                </div>
                <div class="card-body p-3">
                    <!-- OPSI AMBIL LOKASI DARI KOORDINAT EXIF -->
                    <div class="custom-control custom-switch mb-3 bg-light p-2 rounded border">
                        <input type="checkbox" class="custom-control-input" id="useExifLocation" name="use_exif_location" value="1" checked>
                        <label class="custom-control-label font-weight-bold text-dark small" for="useExifLocation">
                            <i class="fas fa-satellite-dish text-primary mr-1"></i> Ambil Teks Lokasi Otomatis dari Koordinat EXIF Foto Saja
                        </label>
                        <small class="d-block text-muted" style="font-size: 11px;">
                            Bila foto memiliki koordinat EXIF GPS dari kamera/HP, teks lokasi pada watermark akan otomatis menggunakan koordinat GPS dari foto tersebut (koordinatnya saja). Jika foto tidak memiliki EXIF GPS, teks lokasi akan menggunakan input di bawah.
                        </small>
                    </div>

                    <div class="form-group mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="lokasi" class="small font-weight-bold text-dark mb-0">
                                Lokasi Kegiatan / Cadangan (Fallback)
                                <span class="badge badge-light border text-muted ml-1" style="font-size: 10px;">Otomatis dari EXIF</span>
                            </label>
                            <button type="button" class="btn btn-outline-primary btn-xs py-0 px-2 font-weight-bold" id="btnBukaPetaModal">
                                <i class="fas fa-map-marked-alt mr-1"></i> Pilih dari Peta (Popup)
                            </button>
                        </div>
                        <div class="input-group">
                            <input type="text" class="form-control" id="lokasi" name="lokasi" placeholder="Otomatis dari koordinat EXIF foto, atau isi nama lokasi / pilih dari peta">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-primary" id="btnBukaPetaModal2" title="Pilih Lokasi dari Peta">
                                    <i class="fas fa-map-marker-alt mr-1"></i> Peta
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="koordinat" class="small font-weight-bold text-dark mb-0">Koordinat GPS (Opsional)</label>
                                <button type="button" class="btn btn-link btn-xs p-0 text-primary small" id="btnGpsQuickFromForm" title="Ambil GPS perangkat Anda">
                                    <i class="fas fa-location-arrow"></i> GPS Saya
                                </button>
                            </div>
                            <input type="text" class="form-control form-control-sm" id="koordinat" name="koordinat" placeholder="Otomatis dari EXIF jika dikosongkan">
                            <small class="text-muted" style="font-size: 11px;">Contoh: 0.5071° N, 101.4478° E</small>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label for="catatan" class="small font-weight-bold text-dark mb-1">Catatan / Proyek (Opsional)</label>
                            <input type="text" class="form-control form-control-sm" id="catatan" name="catatan" placeholder="Contoh: Rekonstruksi & Rehabilitasi Gedung">
                        </div>
                    </div>

                    <div class="form-group mb-2">
                        <label for="logo" class="small font-weight-bold text-dark mb-1">Logo Instansi Kustom (Opsional)</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="logo" name="logo" accept="image/png,image/jpeg,image/webp">
                            <label class="custom-file-label small" for="logo">Pilih logo (PNG/JPG)...</label>
                        </div>
                        <small class="text-muted" style="font-size: 11px;">Jika dikosongkan, logo default Kementerian PU / Satker PPS akan digunakan.</small>
                    </div>

                    <!-- 5. OPSI SIMPAN KE FOLDER SERVER -->
                    <div class="border rounded p-3 mt-3 bg-light">
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="saveToFolder" name="save_to_folder" value="1" checked>
                            <label class="custom-control-label font-weight-bold text-dark" for="saveToFolder">
                                <i class="fas fa-folder-plus text-warning mr-1"></i> Simpan juga salinan foto ke folder server
                            </label>
                        </div>
                        <div id="folderInputContainer">
                            <label for="targetFolderName" class="small text-muted mb-1">Nama Folder di Server:</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-folder text-warning"></i> /uploads/watermark_hasil/</span>
                                </div>
                                <input type="text" class="form-control" id="targetFolderName" name="target_folder_name" value="Dokumentasi_<?= date('Ymd_His') ?>" placeholder="Nama folder...">
                            </div>
                            <small class="text-muted" style="font-size: 11px;">Hasil foto ber-watermark akan tersimpan rapi di direktori server dan bisa diunduh kapan pun.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TOMBOL PROSES -->
            <div class="form-group mb-4">
                <button type="submit" class="btn btn-primary btn-lg btn-block shadow font-weight-bold py-3" id="btnProses">
                    <i class="fas fa-stamp mr-2"></i> Proses Watermark TimeMark
                </button>
            </div>
        </div>

        <!-- KOLOM KANAN: PREVIEW HASIL & PENGUNDUHAN -->
        <div class="col-lg-6">
            <div class="card card-outline card-success shadow-sm sticky-top" style="top: 20px; z-index: 10;">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                    <h5 class="card-title font-weight-bold text-dark mb-0" style="font-size: 1rem;">
                        <i class="fas fa-eye text-success mr-2"></i> Hasil Pemrosesan Foto
                    </h5>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnResetAll" style="display: none;">
                        <i class="fas fa-redo mr-1"></i> Reset
                    </button>
                </div>
                <div class="card-body p-3">
                    <!-- LOADING STATE -->
                    <div id="loadingContainer" class="text-center py-5" style="display: none;">
                        <div class="spinner-border text-primary mb-3" style="width: 3.5rem; height: 3.5rem;" role="status"></div>
                        <h6 class="font-weight-bold text-dark mb-1">Sedang Memproses Foto...</h6>
                        <p class="text-muted small mb-0">Menerapkan format watermark, ekstraksi EXIF, dan menyusun hasil.</p>
                    </div>

                    <!-- EMPTY PLACEHOLDER -->
                    <div id="placeholderContainer" class="text-center py-5 text-muted">
                        <div class="my-4">
                            <i class="fas fa-images fa-4x text-black-50 mb-3"></i>
                            <h6 class="font-weight-bold text-secondary">Belum Ada Hasil Foto</h6>
                            <p class="small text-muted mb-0">Pilih format watermark, upload foto atau file ZIP di kolom kiri, lalu klik <strong>Proses Watermark TimeMark</strong>.</p>
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
                        <label class="font-weight-bold small text-dark mb-2">Daftar Foto Berhasil:</label>
                        <div class="row" id="batchGalleryGrid" style="max-height: 480px; overflow-y: auto;"></div>
                    </div>

                    <!-- HASIL SINGLE CONTAINER -->
                    <div id="singleResultContainer" style="display: none;">
                        <div class="text-center mb-3">
                            <img id="singlePreviewImage" src="" alt="Watermark Preview" class="img-fluid rounded shadow border" style="max-height: 420px; object-fit: contain;">
                        </div>

                        <div class="alert alert-info py-2 px-3 small mb-3" id="singleServerSavedAlert" style="display: none;">
                            <i class="fas fa-folder text-warning mr-1"></i> Salinan file tersimpan di server: <strong id="singleServerSavedPath"></strong>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-success font-weight-bold flex-fill mr-2" id="btnDownloadSingle">
                                <i class="fas fa-download mr-1"></i> Download Foto
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="btnResetSingle">
                                <i class="fas fa-redo mr-1"></i> Reset
                            </button>
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
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title font-weight-bold" id="modalZoomTitle">Detail Foto</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center p-2 bg-dark">
                <img id="modalZoomImg" src="" alt="Zoom" class="img-fluid rounded" style="max-height: 80vh;">
            </div>
            <div class="modal-footer py-2 d-flex justify-content-between">
                <span class="small text-muted" id="modalZoomMeta"></span>
                <a href="#" id="modalZoomDownload" class="btn btn-success btn-sm" download>
                    <i class="fas fa-download mr-1"></i> Download File Ini
                </a>
            </div>
        </div>
    </div>
</div>

<!-- MODAL MAP PICKER (POPUP PETA LEAFLET) -->
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
.template-card {
    transition: all 0.2s ease-in-out;
    background-color: #fff;
}
.template-card:hover {
    border-color: #007bff !important;
    background-color: #f8f9fa;
}
.template-card.active {
    border-color: #007bff !important;
    border-width: 2px !important;
    background-color: #f0f7ff;
}
.cursor-pointer {
    cursor: pointer;
}
.border-dashed {
    border-style: dashed !important;
    border-width: 2px !important;
}
.upload-dropzone:hover {
    background-color: #eef6ff !important;
}
</style>
<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script src="<?= esc(media_url('assets/leaflet/leaflet.js')); ?>"></script>
<script src="<?= esc(media_url('assets/heic/heic2any.min.js')); ?>"></script>
<script>
$(function() {
    'use strict';

    // State Variables
    var selectedFiles = [];
    var singleGeneratedUri = null;
    var currentZipDownloadUrl = null;
    var isProcessing = false;

    // Helper cek file HEIC / HEIF
    function isHeic(f) {
        if (!f) return false;
        var name = (f.name || '').toLowerCase();
        return name.endsWith('.heic') || name.endsWith('.heif') || f.type === 'image/heic' || f.type === 'image/heif';
    }

    // 1. Template Card Selection
    $('.template-card').on('click', function() {
        $('.template-card').removeClass('active');
        $('.template-card').find('.check-badge').addClass('d-none');
        
        $(this).addClass('active');
        $(this).find('input[type="radio"]').prop('checked', true);
        $(this).find('.check-badge').removeClass('d-none');
    });

    // 2. Dropzone & File Input Handling
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

    function handleSelectedFiles(filesList) {
        var rawFiles = Array.from(filesList);
        var heicFiles = rawFiles.filter(isHeic);

        // Jika terdapat file HEIC (iPhone) dan library heic2any tersedia, konversi client-side
        if (heicFiles.length > 0 && typeof heic2any === 'function') {
            $('#fileCountBadge').removeClass('badge-secondary badge-primary badge-success').addClass('badge-warning')
                .html('<i class="fas fa-magic fa-spin mr-1"></i> Mengonversi HEIC...');
            $('#exifStatusAlert').removeClass('alert-danger alert-info alert-success').addClass('alert-warning')
                .html('<i class="fas fa-magic fa-spin mr-1"></i> <strong>Format Foto iPhone (.HEIC) Terdeteksi:</strong> Mengonversi ' + heicFiles.length + ' file HEIC ke format JPEG...').show();

            var convertPromises = rawFiles.map(function(f) {
                if (isHeic(f)) {
                    return heic2any({ blob: f, toType: 'image/jpeg', quality: 0.94 })
                        .then(function(convertedBlob) {
                            var newName = f.name.replace(/\.(heic|heif)$/i, '.jpg');
                            return new File([convertedBlob], newName, { type: 'image/jpeg' });
                        })
                        .catch(function(err) {
                            console.warn('Konversi HEIC client dilewati, diproses server-side:', err);
                            return f; // fallback ke server converter
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

        selectedFiles.forEach(function(f) {
            if (f.name.toLowerCase().endsWith('.zip')) {
                hasZip = true;
            }
            fileNames.push(f.name);
        });

        if (total === 1 && hasZip) {
            $('#fileCountBadge').removeClass('badge-secondary badge-warning badge-primary badge-success').addClass('badge-warning').html('<i class="fas fa-file-archive mr-1"></i> 1 File ZIP');
        } else if (total === 1) {
            $('#fileCountBadge').removeClass('badge-secondary badge-warning badge-success').addClass('badge-primary').html('<i class="fas fa-image mr-1"></i> 1 Foto Dipilih');
        } else {
            $('#fileCountBadge').removeClass('badge-secondary badge-warning badge-primary').addClass('badge-success').html('<i class="fas fa-images mr-1"></i> ' + total + ' Foto Dipilih');
        }

        var previewText = fileNames.slice(0, 3).join(', ');
        if (total > 3) {
            previewText += ' dan ' + (total - 3) + ' file lainnya';
        }
        $('#fileSelectedList').html('<strong>File:</strong> ' + previewText).show();

        // Jika single image yang diupload, coba ekstrak metadata EXIF via AJAX
        if (total === 1 && !hasZip) {
            extractExifPreview(selectedFiles[0]);
        }
    }

    var lastExifCoordinates = null;

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
                    if (resp.date) {
                        $('#manualDate').val(resp.date);
                    }
                    if (resp.time) {
                        $('#manualTime').val(resp.time);
                    }
                    if (resp.coordinates) {
                        lastExifCoordinates = resp.coordinates;
                        $('#koordinat').val(resp.coordinates);
                        $('#btnGpsFromExif').show();

                        // Jika opsi useExifLocation aktif atau kolom lokasi masih kosong, isi dengan koordinat EXIF
                        if ($('#useExifLocation').is(':checked') || !$('#lokasi').val() || $('#lokasi').val().trim() === '') {
                            $('#lokasi').val(resp.coordinates);
                        }
                    }
                    if (resp.has_exif) {
                        var statusHtml = '<i class="fas fa-check-circle mr-1"></i> <strong>EXIF Terdeteksi:</strong> Waktu foto ' + resp.day + ', ' + resp.date + ' ' + resp.time;
                        if (resp.coordinates) {
                            statusHtml += ' | <i class="fas fa-satellite-dish text-success ml-1"></i> GPS: <code>' + resp.coordinates + '</code> (koordinat digunakan sebagai teks lokasi)';
                        }
                        $('#exifStatusAlert').removeClass('alert-info').addClass('alert-success').html(statusHtml);
                    }
                }
            }
        });
    }

    // Toggle useExifLocation listener
    $('#useExifLocation').on('change', function() {
        if ($(this).is(':checked') && lastExifCoordinates) {
            $('#lokasi').val(lastExifCoordinates);
        }
    });

    // 3. Offset Buttons Handling
    $('input[name="quick_offset"]').on('change', function() {
        var val = parseInt($(this).val(), 10);
        $('#offsetHours').val(val);
        $('#offsetMinutes').val(0);
        updateOffsetBadge(val, 0);
    });

    $('#offsetHours, #offsetMinutes').on('input change', function() {
        var h = parseInt($('#offsetHours').val(), 10) || 0;
        var m = parseInt($('#offsetMinutes').val(), 10) || 0;
        updateOffsetBadge(h, m);
    });

    function updateOffsetBadge(h, m) {
        var text = (h >= 0 ? '+' : '') + h + ' Jam';
        if (m !== 0) {
            text += ' ' + (m >= 0 ? '+' : '') + m + ' Menit';
        }
        $('#currentOffsetBadge').text(text);
    }

    // Set Sekarang Button
    $('#btnNow').on('click', function() {
        var now = new Date();
        var yyyy = now.getFullYear();
        var mm = String(now.getMonth() + 1).padStart(2, '0');
        var dd = String(now.getDate()).padStart(2, '0');
        var hh = String(now.getHours()).padStart(2, '0');
        var min = String(now.getMinutes()).padStart(2, '0');
        $('#manualDate').val(yyyy + '-' + mm + '-' + dd);
        $('#manualTime').val(hh + ':' + min);
    });

    // Custom File Label
    $('#logo').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName || 'Pilih logo (PNG/JPG)...');
    });

    // Toggle Save To Folder Input
    $('#saveToFolder').on('change', function() {
        if ($(this).is(':checked')) {
            $('#folderInputContainer').slideDown();
        } else {
            $('#folderInputContainer').slideUp();
        }
    });

    // 4. Form Submit & AJAX Watermark Processing
    $('#watermarkForm').on('submit', function(e) {
        e.preventDefault();

        if (isProcessing) return;

        if (selectedFiles.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'File Belum Dipilih',
                text: 'Silakan pilih foto atau file ZIP terlebih dahulu.'
            });
            return;
        }

        var formData = new FormData();
        var totalSize = 0;

        // Cek jika ada single ZIP atau multi image
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

        // UI Loading State
        isProcessing = true;
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

                    if (resp.mode === 'single' && resp.image_data) {
                        // Tampilkan Single Preview
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
                        // Tampilkan Batch Result
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

                        // Render Gallery Grid
                        var $grid = $('#batchGalleryGrid');
                        $grid.empty();

                        resp.images.forEach(function(img, idx) {
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

                    // Notifikasi Sukses Tunggal (Rule #1)
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

    // 5. Download Single Image
    $('#btnDownloadSingle').on('click', function() {
        if (!singleGeneratedUri) return;
        var a = document.createElement('a');
        a.href = singleGeneratedUri;
        a.download = 'foto-watermark-' + new Date().getTime() + '.jpg';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    });

    // 6. Reset Handlers
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
    });

    // -------------------------------------------------------------------------
    // 7. LEAFLET MAP PICKER MODAL (PILIH LOKASI DARI POPUP PETA)
    // -------------------------------------------------------------------------
    var pickerMap = null;
    var pickerMarker = null;
    var currentLat = 0.507068; // Default: Pekanbaru / Balai PPS Riau
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

    function reverseGeocode(lat, lng) {
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
                },
                error: function() {
                    currentSelectedAddress = $('#lokasi').val() || ('Lokasi Koordinat ' + formatCoordinates(lat, lng));
                    $('#selectedAddressText').text(currentSelectedAddress);
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
    $('#btnBukaPetaModal, #btnBukaPetaModal2').on('click', function(e) {
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

    // Tombol lompat ke EXIF foto di dalam popup peta
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

    // Search Trigger
    $('#btnSearchMap').on('click', function() {
        searchMapLocation($('#mapSearchInput').val());
    });
    $('#mapSearchInput').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            searchMapLocation($(this).val());
        }
    });

    // GPS Current Location Button in Modal
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
            $btn.prop('disabled', false).html('<i class="fas fa-location-arrow"></i> GPS Saya');
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
            $btn.prop('disabled', false).html('<i class="fas fa-location-arrow"></i> GPS Saya');
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
});
</script>
<?= $this->endSection(); ?>
