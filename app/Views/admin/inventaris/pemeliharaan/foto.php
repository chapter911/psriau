<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="content-header pb-2">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-7">
                <h1 class="m-0 text-dark font-weight-bold" style="font-size: 1.45rem;">
                    <i class="fas fa-camera text-info mr-2"></i>Kelola Lampiran Foto Pemeliharaan Aset
                </h1>
                <p class="text-muted small mb-0 mt-1">
                    Input bertahap foto fisik 4 sudut (Depan, Atas, Bawah, Belakang) ber-watermark resmi TimeMark PU untuk Surat No: <strong><?= esc($pemeliharaan['no_surat']); ?></strong>
                </p>
            </div>
            <div class="col-sm-5 text-sm-right mt-2 mt-sm-0">
                <a href="<?= site_url('admin/inventaris/pemeliharaan'); ?>" class="btn btn-outline-secondary btn-sm shadow-sm mr-2" style="border-radius: 6px;">
                    <i class="fas fa-arrow-left mr-1"></i>Kembali ke Arsip
                </a>
                <?php if (! empty($permissions['can_export'])): ?>
                    <a href="<?= site_url('admin/inventaris/pemeliharaan/' . $pemeliharaan['id'] . '/cetak-pdf'); ?>" target="_blank" class="btn btn-danger btn-sm shadow-sm font-weight-bold" style="border-radius: 6px;" title="Pratinjau Cetak PDF Lengkap">
                        <i class="fas fa-file-pdf mr-1"></i>Lihat PDF
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- KARTU RINGKASAN SURAT & PROGRESS KELENGKAPAN FOTO -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
                    <div>
                        <span class="badge badge-primary px-2.5 py-1 mb-1" style="font-size: 0.75rem;">
                            <i class="fas fa-tag mr-1"></i><?= esc($pemeliharaan['jenis_pemeliharaan']); ?>
                        </span>
                        <h5 class="font-weight-bold text-dark mb-0">
                            <?= esc($pemeliharaan['judul_surat']); ?>
                        </h5>
                        <div class="small text-muted mt-0.5">
                            Nomor: <strong class="text-dark"><?= esc($pemeliharaan['no_surat']); ?></strong> &bull; 
                            Tanggal: <strong class="text-dark"><?= date('d F Y', strtotime($pemeliharaan['tanggal_surat'])); ?></strong> &bull; 
                            Kasatker KPB: <strong class="text-dark"><?= esc($pemeliharaan['nama_kasatker']); ?></strong>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="badge badge-<?= $stats['status_color']; ?> px-3 py-2 font-weight-bold shadow-sm" id="badgeOverallStatus" style="font-size: 0.85rem; border-radius: 8px;">
                            <i class="fas fa-camera mr-1.5"></i><span id="labelOverallStatus"><?= esc($stats['status_label']); ?></span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body p-3 bg-light">
                <div class="row align-items-center">
                    <div class="col-md-3 col-6 text-center border-right mb-2 mb-md-0">
                        <div class="text-muted small">Kelompok Barang</div>
                        <div class="h5 font-weight-bold text-dark mb-0 mt-0.5">
                            <?= number_format($stats['total_groups']); ?> <small class="text-muted font-weight-normal">Kelompok</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 text-center border-right mb-2 mb-md-0">
                        <div class="text-muted small">Total Foto Dibutuhkan</div>
                        <div class="h5 font-weight-bold text-dark mb-0 mt-0.5" id="statTotalSlots">
                            <?= number_format($stats['total_slots']); ?> <small class="text-muted font-weight-normal">Slot Foto</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 text-center border-right">
                        <div class="text-muted small">Foto Sudah Terisi</div>
                        <div class="h5 font-weight-bold text-success mb-0 mt-0.5" id="statFilledSlots">
                            <?= number_format($stats['filled_slots']); ?> <small class="text-success font-weight-normal">Foto</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 text-center">
                        <div class="text-muted small">Foto Belum Terisi</div>
                        <div class="h5 font-weight-bold text-danger mb-0 mt-0.5" id="statEmptySlots">
                            <?= number_format($stats['empty_slots']); ?> <small class="text-danger font-weight-normal">Foto</small>
                        </div>
                    </div>
                </div>

                <div class="mt-3 pt-2 border-top">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="font-weight-bold text-muted">Progress Kelengkapan Foto Fisik:</small>
                        <small class="font-weight-bold text-primary" id="progressPercentageText"><?= $stats['percentage']; ?>% Selesai</small>
                    </div>
                    <div class="progress" style="height: 10px; border-radius: 6px; background-color: #e2e8f0;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-<?= $stats['status_color'] === 'success' ? 'success' : 'primary'; ?>" id="mainProgressBar" role="progressbar" style="width: <?= $stats['percentage']; ?>%;" aria-valuenow="<?= $stats['percentage']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PANDUAN PENGISIAN BERTAHAP -->
        <div class="alert alert-light border border-info shadow-sm d-flex align-items-center mb-4" style="border-radius: 10px;">
            <i class="fas fa-info-circle fa-2x mr-3 text-info"></i>
            <div>
                <div class="font-weight-bold text-dark">Alur Pengambilan Foto Fisik Bertahap</div>
                <small class="text-muted">
                    Setiap barang/kelompok membutuhkan 4 foto fisik (<strong>Tampak Depan, Tampak Atas, Tampak Bawah, Tampak Belakang</strong>). 
                    Anda dapat mencicil foto kapan saja saat melakukan pengecekan di lapangan. Setiap foto langsung tersimpan otomatis ke server dan disematkan watermark resmi TimeMark instansi.
                </small>
            </div>
        </div>

        <!-- DAFTAR KELOMPOK BARANG & FORMULIR 4 FOTO -->
        <?php if (empty($fotos)): ?>
            <div class="card shadow-sm border-0 py-5 text-center text-muted" style="border-radius: 10px;">
                <i class="fas fa-images fa-3x mb-2 text-secondary" style="opacity: 0.3;"></i>
                <h6 class="font-weight-bold">Tidak Ada Kelompok Barang pada Surat Ini</h6>
                <small>Pastikan surat sudah memiliki daftar barang di langkah input surat.</small>
            </div>
        <?php else: ?>
            <?php foreach ($fotos as $idx => $f): ?>
                <?php
                    $fId = (int) $f['id'];
                    $kKey = $f['kelompok_key'];
                    $gFilled = (int) ($f['group_filled'] ?? 0);
                    $gStatus = $f['group_status'] ?? 'danger';
                    $gStatusClass = ($gStatus === 'success') ? 'border-success' : (($gStatus === 'warning') ? 'border-warning' : 'border-danger');
                ?>
                <div class="card shadow-sm border mb-4 photo-group-card <?= $gStatusClass; ?>" id="groupCard_<?= $fId; ?>" style="border-radius: 12px; border-width: 1.5px !important; overflow: hidden;">
                    <div class="card-header bg-white py-2.5 px-3 d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
                        <div>
                            <span class="badge badge-dark px-2 py-0.5 mr-1" style="font-size: 0.75rem;">
                                Kelompok #<?= $f['urutan']; ?>
                            </span>
                            <span class="font-weight-bold text-dark" style="font-size: 1rem;">
                                <?= esc($f['nama_barang']); ?>
                            </span>
                            <span class="badge badge-light border text-primary font-weight-bold px-2 py-0.5 ml-1" style="font-size: 0.78rem;">
                                NUP: <?= esc($f['nup_gabungan'] ?: '-'); ?>
                            </span>
                        </div>
                        <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                            <span class="badge badge-<?= $gStatus; ?> px-2.5 py-1.5 font-weight-bold group-status-badge" id="groupBadge_<?= $fId; ?>" style="font-size: 0.78rem;">
                                <?= $gFilled; ?> / 4 Foto Terisi
                            </span>
                            <button type="button" class="btn btn-primary btn-sm rounded-pill shadow-sm font-weight-bold px-3 btn-start-group-wizard" data-foto-id="<?= $fId; ?>" data-key="<?= esc($kKey); ?>" data-nama="<?= esc($f['nama_barang']); ?>" data-nup="<?= esc($f['nup_gabungan']); ?>">
                                <i class="fas fa-camera mr-1"></i>Foto Kamera 4 Sudut (Wizard)
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="row">
                            <?php
                                $angles = [
                                    'depan'    => ['label' => '1. Tampak Depan', 'col' => 'foto_depan'],
                                    'atas'     => ['label' => '2. Tampak Atas', 'col' => 'foto_atas'],
                                    'bawah'    => ['label' => '3. Tampak Bawah', 'col' => 'foto_bawah'],
                                    'belakang' => ['label' => '4. Tampak Belakang', 'col' => 'foto_belakang'],
                                ];
                            ?>
                            <?php foreach ($angles as $pos => $cfg): ?>
                                <?php
                                    $colName = $cfg['col'];
                                    $filePath = $f[$colName] ?? null;
                                    $hasPhoto = ! empty($filePath) && is_file(FCPATH . ltrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $filePath), DIRECTORY_SEPARATOR));
                                    $fullUrl = $hasPhoto ? base_url($filePath) : '';
                                ?>
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="border rounded p-2 text-center bg-light h-100 d-flex flex-column justify-content-between slot-container" id="slotContainer_<?= $fId; ?>_<?= $pos; ?>" style="border-radius: 8px;">
                                        <div class="small font-weight-bold text-dark mb-1.5 pb-1 border-bottom d-flex justify-content-between align-items-center">
                                            <span><?= $cfg['label']; ?></span>
                                            <span class="slot-status-icon" id="slotStatusIcon_<?= $fId; ?>_<?= $pos; ?>">
                                                <?php if ($hasPhoto): ?>
                                                    <i class="fas fa-check-circle text-success" title="Foto Tersedia"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle text-danger" title="Belum Ada Foto"></i>
                                                <?php endif; ?>
                                            </span>
                                        </div>

                                        <div class="preview-box my-1" id="previewBox_<?= $fId; ?>_<?= $pos; ?>">
                                            <?php if ($hasPhoto): ?>
                                                <div class="position-relative d-inline-block">
                                                    <img src="<?= $fullUrl; ?>" class="img-thumbnail shadow-sm cursor-pointer img-preview-clickable" data-src="<?= $fullUrl; ?>" data-title="<?= esc($f['nama_barang']) . ' - ' . $cfg['label']; ?>" style="max-height: 120px; max-width: 100%; object-fit: contain; border-radius: 6px;" alt="<?= $cfg['label']; ?>">
                                                    <span class="badge badge-warning text-dark font-weight-bold shadow-sm" style="position: absolute; bottom: 4px; right: 4px; font-size: 0.62rem;">
                                                        <i class="fas fa-certificate mr-0.5"></i>TimeMark
                                                    </span>
                                                </div>
                                            <?php else: ?>
                                                <div class="py-4 border border-dashed rounded bg-white text-muted" style="border-style: dashed !important; border-color: #cbd5e1 !important;">
                                                    <i class="fas fa-camera fa-2x mb-1 text-secondary" style="opacity: 0.35;"></i>
                                                    <div class="small font-weight-bold text-muted">Belum Ada Foto</div>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="mt-2 pt-1 border-top action-btn-group">
                                            <div class="btn-group btn-group-sm w-100">
                                                <button type="button" class="btn btn-outline-primary btn-xs py-1 btn-snap-single" data-foto-id="<?= $fId; ?>" data-pos="<?= $pos; ?>" data-label="<?= $cfg['label']; ?>" data-nama="<?= esc($f['nama_barang']); ?>" data-nup="<?= esc($f['nup_gabungan']); ?>" title="Foto Langsung Kamera">
                                                    <i class="fas fa-camera mr-0.5"></i>Foto
                                                </button>
                                                <button type="button" class="btn btn-outline-info btn-xs py-1 btn-trigger-upload" data-foto-id="<?= $fId; ?>" data-pos="<?= $pos; ?>" title="Unggah File Gambar">
                                                    <i class="fas fa-upload mr-0.5"></i>Unggah
                                                </button>
                                                <?php if ($hasPhoto): ?>
                                                    <button type="button" class="btn btn-outline-danger btn-xs py-1 btn-delete-single" data-foto-id="<?= $fId; ?>" data-pos="<?= $pos; ?>" data-label="<?= $cfg['label']; ?>" title="Hapus Foto">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                            <!-- Hidden File Input untuk slot ini -->
                                            <input type="file" class="d-none slot-file-input" id="fileInput_<?= $fId; ?>_<?= $pos; ?>" data-foto-id="<?= $fId; ?>" data-pos="<?= $pos; ?>" accept="image/*,.heic,.heif">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="card shadow-sm border-0 mb-5 text-center p-3 bg-white" style="border-radius: 12px;">
            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
                <div class="text-left">
                    <div class="font-weight-bold text-dark">Seluruh foto otomatis tersimpan real-time</div>
                    <small class="text-muted">Anda dapat meninggalkan halaman ini dan melanjutkan pengambilan foto di lain waktu.</small>
                </div>
                <div>
                    <a href="<?= site_url('admin/inventaris/pemeliharaan'); ?>" class="btn btn-outline-secondary btn-sm px-4 rounded-pill mr-2">
                        <i class="fas fa-check mr-1"></i>Selesai & Kembali ke Arsip
                    </a>
                    <?php if (! empty($permissions['can_export'])): ?>
                        <a href="<?= site_url('admin/inventaris/pemeliharaan/' . $pemeliharaan['id'] . '/cetak-pdf'); ?>" target="_blank" class="btn btn-danger btn-sm px-4 rounded-pill font-weight-bold shadow-sm">
                            <i class="fas fa-file-pdf mr-1"></i>Cetak Dokumen PDF
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- MODAL KAMERA BERURUTAN TERPANDU (SEQUENTIAL CAMERA WIZARD) -->
<div class="modal fade" id="modalCameraWizard" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header bg-dark text-white py-2.5 px-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <i class="fas fa-camera text-warning mr-2"></i>
                    <div>
                        <h6 class="modal-title font-weight-bold mb-0" id="cameraWizardTitle">Pemotretan Terpandu 4 Sudut</h6>
                        <small class="text-light opacity-8" id="cameraWizardSubtitle">Target Aset: -</small>
                    </div>
                </div>
                <button type="button" class="close text-white opacity-8" id="btnCloseCameraWizard" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0 bg-black text-center position-relative" style="background: #000; min-height: 380px;">
                
                <!-- Indikator Langkah 4 Sudut -->
                <div class="wizard-steps-bar p-2 bg-dark d-flex justify-content-center align-items-center flex-wrap" style="gap: 8px; border-bottom: 1px solid #333;">
                    <span class="badge badge-secondary px-2.5 py-1.5 step-badge" id="stepBadge_depan"><i class="fas fa-circle mr-1"></i>1. Depan</span>
                    <i class="fas fa-chevron-right text-muted small"></i>
                    <span class="badge badge-secondary px-2.5 py-1.5 step-badge" id="stepBadge_atas"><i class="fas fa-circle mr-1"></i>2. Atas</span>
                    <i class="fas fa-chevron-right text-muted small"></i>
                    <span class="badge badge-secondary px-2.5 py-1.5 step-badge" id="stepBadge_bawah"><i class="fas fa-circle mr-1"></i>3. Bawah</span>
                    <i class="fas fa-chevron-right text-muted small"></i>
                    <span class="badge badge-secondary px-2.5 py-1.5 step-badge" id="stepBadge_belakang"><i class="fas fa-circle mr-1"></i>4. Belakang</span>
                </div>

                <!-- Video Viewfinder Kamera Langsung -->
                <div class="position-relative d-inline-block w-100" style="max-height: 440px; background: #000; overflow: hidden;">
                    <video id="cameraVideo" autoplay playsinline style="width: 100%; max-height: 440px; object-fit: contain; background: #000;"></video>
                    
                    <!-- Reticle Frame Pemandu Objek -->
                    <div id="cameraReticle" style="position: absolute; top: 10%; left: 10%; width: 80%; height: 80%; border: 2px dashed rgba(255, 255, 255, 0.45); pointer-events: none; border-radius: 8px;">
                        <span class="badge badge-dark opacity-8 p-1 small" style="position: absolute; top: 6px; left: 6px;" id="lblReticleAngle">
                            Arahkan kamera ke bagian DEPAN
                        </span>
                    </div>

                    <!-- Canvas Jepret Gambar Sementara -->
                    <canvas id="cameraCanvas" style="display: none;"></canvas>

                    <!-- Preview Hasil Foto dengan Watermark -->
                    <div id="cameraSnapPreviewWrapper" style="display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: #000;">
                        <img id="cameraSnapPreviewImg" src="" style="width: 100%; height: 100%; object-fit: contain;">
                        <div class="watermark-processing-indicator" id="wmProcessingSpinner" style="display: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(0,0,0,0.75); color: #fff; padding: 12px 20px; border-radius: 8px;">
                            <i class="fas fa-spinner fa-spin mr-2 text-warning"></i>Menyematkan Watermark TimeMark PU...
                        </div>
                    </div>
                </div>

                <!-- Petunjuk Sudut Aktif -->
                <div class="py-2 px-3 bg-dark text-white d-flex justify-content-between align-items-center">
                    <span class="small font-weight-bold" id="lblCurrentAngleInstruction">
                        <i class="fas fa-info-circle text-info mr-1"></i>Ambil foto dari <strong>Tampak Depan</strong>
                    </span>
                    <button type="button" class="btn btn-outline-light btn-xs" id="btnSwitchCamera" title="Ganti Kamera Depan/Belakang">
                        <i class="fas fa-sync-alt mr-1"></i>Ganti Kamera
                    </button>
                </div>
            </div>
            <div class="modal-footer bg-dark py-2 px-3 d-flex justify-content-between">
                <div>
                    <button type="button" class="btn btn-outline-secondary btn-sm text-light" id="btnCancelCameraWizard">
                        Batal
                    </button>
                </div>
                <div id="cameraControlsCapture">
                    <button type="button" class="btn btn-warning btn-sm rounded-pill px-4 font-weight-bold shadow" id="btnSnapPhoto">
                        <i class="fas fa-camera mr-1.5"></i>Jepret Foto
                    </button>
                </div>
                <div id="cameraControlsReview" style="display: none;">
                    <button type="button" class="btn btn-outline-light btn-sm mr-2" id="btnRetakeCurrentAngle">
                        <i class="fas fa-redo mr-1"></i>Ulangi
                    </button>
                    <button type="button" class="btn btn-success btn-sm rounded-pill px-4 font-weight-bold" id="btnAcceptCurrentAngle">
                        <i class="fas fa-check mr-1"></i>Gunakan &amp; Lanjut
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PERBESAR FOTO -->
<div class="modal fade" id="modalEnlargePhoto" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0 bg-dark text-white" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header py-2 px-3 border-secondary">
                <h6 class="modal-title font-weight-bold" id="enlargePhotoTitle">Pratinjau Foto</h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0 text-center bg-black">
                <img id="enlargePhotoImg" src="" style="width: 100%; max-height: 520px; object-fit: contain;" alt="Pratinjau">
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script>
$(document).ready(function() {
    var pemeliharaanId = <?= (int) $pemeliharaan['id']; ?>;
    var baseUrl = '<?= rtrim(site_url(), "/"); ?>';

    // State Kamera Wizard
    var activeFotoId = null;
    var activeGroupKey = '';
    var activeItemNama = '';
    var activeItemNup = '';
    var singleModeAngle = null; // Jika non-null, hanya memotret 1 sudut spesifik
    var currentAngleIndex = 0;
    var angleOrder = ['depan', 'atas', 'bawah', 'belakang'];
    var currentAngle = 'depan';
    var videoStream = null;
    var currentFacingMode = 'environment';
    var userGpsCoordinates = '';
    var capturedSessionImages = {}; // Format: { depan: dataUri, atas: ..., ... }

    // Dapatkan Koordinat GPS Real-time untuk Watermark
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(pos) {
            var lat = pos.coords.latitude.toFixed(5);
            var lng = pos.coords.longitude.toFixed(5);
            var latDir = lat >= 0 ? '° N' : '° S';
            var lngDir = lng >= 0 ? '° E' : '° W';
            userGpsCoordinates = Math.abs(lat) + latDir + ', ' + Math.abs(lng) + lngDir;
        }, function(err) {
            userGpsCoordinates = '0.4851° N, 101.4485° E'; // Fallback Pekanbaru
        });
    }

    // Modal Perbesar Foto
    $(document).on('click', '.img-preview-clickable', function() {
        var src = $(this).data('src');
        var title = $(this).data('title');
        $('#enlargePhotoImg').attr('src', src);
        $('#enlargePhotoTitle').text(title || 'Pratinjau Foto');
        $('#modalEnlargePhoto').modal('show');
    });

    // Trigger Upload File Spesifik
    $(document).on('click', '.btn-trigger-upload', function() {
        var fotoId = $(this).data('foto-id');
        var pos = $(this).data('pos');
        $('#fileInput_' + fotoId + '_' + pos).click();
    });

    // Handle Upload File Gambar per Sudut
    $(document).on('change', '.slot-file-input', function(e) {
        var file = this.files[0];
        if (!file) return;

        var fotoId = $(this).data('foto-id');
        var pos = $(this).data('pos');
        var inputElem = $(this);

        var formData = new FormData();
        formData.append('foto_id', fotoId);
        formData.append('posisi', pos);
        formData.append('image_file', file);

        var previewBox = $('#previewBox_' + fotoId + '_' + pos);
        var oldHtml = previewBox.html();
        previewBox.html('<div class="py-4 text-center text-info"><i class="fas fa-spinner fa-spin fa-2x mb-1"></i><div class="small font-weight-bold">Mengunggah foto...</div></div>');

        $.ajax({
            url: baseUrl + '/admin/inventaris/pemeliharaan/' + pemeliharaanId + '/foto/ajax-save-single',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    renderSlotPhotoAvailable(fotoId, pos, res.photo_url);
                    updateStatsUI(res);
                } else {
                    alert('Gagal mengunggah foto: ' + (res.error || 'Terjadi kesalahan'));
                    previewBox.html(oldHtml);
                }
            },
            error: function(xhr) {
                alert('Gagal mengunggah foto ke server.');
                previewBox.html(oldHtml);
            },
            complete: function() {
                inputElem.val('');
            }
        });
    });

    // Handle Hapus Foto per Sudut
    $(document).on('click', '.btn-delete-single', function() {
        var fotoId = $(this).data('foto-id');
        var pos = $(this).data('pos');
        var label = $(this).data('label');

        if (!confirm('Apakah Anda yakin ingin menghapus foto ' + label + '?')) {
            return;
        }

        var previewBox = $('#previewBox_' + fotoId + '_' + pos);
        var oldHtml = previewBox.html();
        previewBox.html('<div class="py-4 text-center text-danger"><i class="fas fa-spinner fa-spin fa-2x mb-1"></i><div class="small font-weight-bold">Menghapus foto...</div></div>');

        $.ajax({
            url: baseUrl + '/admin/inventaris/pemeliharaan/' + pemeliharaanId + '/foto/ajax-delete-single',
            type: 'POST',
            data: { foto_id: fotoId, posisi: pos },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    renderSlotPhotoEmpty(fotoId, pos, label);
                    updateStatsUI(res);
                } else {
                    alert('Gagal menghapus foto: ' + (res.error || 'Terjadi kesalahan'));
                    previewBox.html(oldHtml);
                }
            },
            error: function(xhr) {
                alert('Gagal menghapus foto dari server.');
                previewBox.html(oldHtml);
            }
        });
    });

    // Start Sequential Wizard 4 Sudut
    $(document).on('click', '.btn-start-group-wizard', function() {
        activeFotoId = $(this).data('foto-id');
        activeGroupKey = $(this).data('key');
        activeItemNama = $(this).data('nama');
        activeItemNup = $(this).data('nup');
        singleModeAngle = null;

        $('#cameraWizardTitle').text('Pemotretan Terpandu 4 Sudut');
        $('#cameraWizardSubtitle').text(activeItemNama + ' (NUP: ' + activeItemNup + ')');
        $('.wizard-steps-bar').show();

        capturedSessionImages = {};
        $('#modalCameraWizard').modal('show');
        switchCameraStep('depan');
        startCameraStream();
    });

    // Start Single Angle Camera Snap
    $(document).on('click', '.btn-snap-single', function() {
        activeFotoId = $(this).data('foto-id');
        activeGroupKey = '';
        activeItemNama = $(this).data('nama');
        activeItemNup = $(this).data('nup');
        var pos = $(this).data('pos');
        var label = $(this).data('label');
        singleModeAngle = pos;

        $('#cameraWizardTitle').text('Foto ' + label);
        $('#cameraWizardSubtitle').text(activeItemNama + ' (NUP: ' + activeItemNup + ')');
        $('.wizard-steps-bar').hide();

        capturedSessionImages = {};
        $('#modalCameraWizard').modal('show');
        switchCameraStep(pos);
        startCameraStream();
    });

    // Beralih Sudut Kamera
    function switchCameraStep(angle) {
        currentAngle = angle;
        currentAngleIndex = angleOrder.indexOf(angle);

        var angleLabels = {
            'depan': 'Tampak DEPAN',
            'atas': 'Tampak ATAS',
            'bawah': 'Tampak BAWAH',
            'belakang': 'Tampak BELAKANG'
        };

        $('.step-badge').removeClass('badge-warning text-dark font-weight-bold').addClass('badge-secondary');
        $('#stepBadge_' + angle).removeClass('badge-secondary').addClass('badge-warning text-dark font-weight-bold');

        $('#lblReticleAngle').text('Arahkan kamera ke bagian ' + angle.toUpperCase());
        $('#lblCurrentAngleInstruction').html('<i class="fas fa-camera text-info mr-1"></i>Ambil foto dari <strong>' + angleLabels[angle] + '</strong>');

        $('#cameraSnapPreviewWrapper').hide();
        $('#cameraControlsCapture').show();
        $('#cameraControlsReview').hide();
    }

    // Mulai Stream Kamera
    function startCameraStream() {
        stopCameraStream();

        var constraints = {
            video: {
                facingMode: currentFacingMode,
                width: { ideal: 1920 },
                height: { ideal: 1080 }
            },
            audio: false
        };

        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia(constraints).then(function(stream) {
                videoStream = stream;
                var videoElem = document.getElementById('cameraVideo');
                videoElem.srcObject = stream;
                videoElem.play();
            }).catch(function(err) {
                console.warn('Gagal akses kamera resolusi tinggi, coba default:', err);
                navigator.mediaDevices.getUserMedia({ video: true, audio: false }).then(function(stream) {
                    videoStream = stream;
                    var videoElem = document.getElementById('cameraVideo');
                    videoElem.srcObject = stream;
                    videoElem.play();
                }).catch(function(fallbackErr) {
                    alert('Tidak dapat mengakses kamera perangkat: ' + fallbackErr.message);
                });
            });
        } else {
            alert('Perangkat Anda tidak mendukung akses WebRTC Camera.');
        }
    }

    // Hentikan Stream Kamera
    function stopCameraStream() {
        if (videoStream) {
            videoStream.getTracks().forEach(function(track) { track.stop(); });
            videoStream = null;
        }
    }

    // Ganti Kamera Depan/Belakang
    $('#btnSwitchCamera').on('click', function() {
        currentFacingMode = (currentFacingMode === 'environment') ? 'user' : 'environment';
        startCameraStream();
    });

    // Tutup / Batal Modal Wizard
    $('#btnCloseCameraWizard, #btnCancelCameraWizard').on('click', function() {
        stopCameraStream();
        $('#modalCameraWizard').modal('hide');
    });

    // Jepret Foto Saat Ini
    $('#btnSnapPhoto').on('click', function() {
        var video = document.getElementById('cameraVideo');
        var canvas = document.getElementById('cameraCanvas');
        if (!video || !video.videoWidth) return;

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        var ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        var rawBase64 = canvas.toDataURL('image/jpeg', 0.92);

        $('#cameraSnapPreviewImg').attr('src', rawBase64);
        $('#cameraSnapPreviewWrapper').show();
        $('#wmProcessingSpinner').show();
        $('#cameraControlsCapture').hide();
        $('#cameraControlsReview').hide();

        var angleLabels = {
            'depan': 'Tampak Depan',
            'atas': 'Tampak Atas',
            'bawah': 'Tampak Bawah',
            'belakang': 'Tampak Belakang'
        };

        // Kirim ke server untuk Watermark TimeMark Resmi PU
        $.ajax({
            url: baseUrl + '/admin/inventaris/pemeliharaan/apply-watermark',
            type: 'POST',
            data: {
                image_data: rawBase64,
                nama_barang: activeItemNama,
                merk_tipe: '',
                nup: activeItemNup,
                sudut: angleLabels[currentAngle],
                coordinates: userGpsCoordinates
            },
            dataType: 'json',
            success: function(res) {
                $('#wmProcessingSpinner').hide();
                if (res.success && res.data_uri) {
                    $('#cameraSnapPreviewImg').attr('src', res.data_uri);
                    capturedSessionImages[currentAngle] = res.data_uri;
                    $('#cameraControlsReview').show();
                } else {
                    alert('Gagal menyematkan watermark: ' + (res.error || 'Unknown error'));
                    capturedSessionImages[currentAngle] = rawBase64;
                    $('#cameraControlsReview').show();
                }
            },
            error: function() {
                $('#wmProcessingSpinner').hide();
                capturedSessionImages[currentAngle] = rawBase64;
                $('#cameraControlsReview').show();
            }
        });
    });

    // Ulangi Jepret Sudut Saat Ini
    $('#btnRetakeCurrentAngle').on('click', function() {
        delete capturedSessionImages[currentAngle];
        $('#cameraSnapPreviewWrapper').hide();
        $('#cameraControlsCapture').show();
        $('#cameraControlsReview').hide();
    });

    // Terima Hasil Foto Sudut Saat Ini
    $('#btnAcceptCurrentAngle').on('click', function() {
        var finalDataUri = capturedSessionImages[currentAngle];
        if (!finalDataUri) return;

        // Auto-Save instan ke backend untuk sudut ini
        saveCapturedImageToSlot(activeFotoId, currentAngle, finalDataUri);

        if (singleModeAngle !== null) {
            // Selesai mode single
            stopCameraStream();
            $('#modalCameraWizard').modal('hide');
        } else {
            // Lanjut ke sudut berikutnya jika ada
            if (currentAngleIndex < angleOrder.length - 1) {
                var nextAngle = angleOrder[currentAngleIndex + 1];
                switchCameraStep(nextAngle);
            } else {
                // Selesai seluruh 4 sudut
                stopCameraStream();
                $('#modalCameraWizard').modal('hide');
            }
        }
    });

    // Kirim AJAX Save Single Foto ke Server
    function saveCapturedImageToSlot(fotoId, pos, dataUri) {
        var previewBox = $('#previewBox_' + fotoId + '_' + pos);
        previewBox.html('<div class="py-4 text-center text-info"><i class="fas fa-spinner fa-spin fa-2x mb-1"></i><div class="small font-weight-bold">Menyimpan foto...</div></div>');

        $.ajax({
            url: baseUrl + '/admin/inventaris/pemeliharaan/' + pemeliharaanId + '/foto/ajax-save-single',
            type: 'POST',
            data: {
                foto_id: fotoId,
                posisi: pos,
                image_base64: dataUri
            },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    renderSlotPhotoAvailable(fotoId, pos, res.photo_url || dataUri);
                    updateStatsUI(res);
                } else {
                    alert('Gagal menyimpan foto: ' + (res.error || 'Terjadi kesalahan'));
                    renderSlotPhotoEmpty(fotoId, pos, pos);
                }
            },
            error: function() {
                renderSlotPhotoAvailable(fotoId, pos, dataUri);
            }
        });
    }

    // Render Tampilan Slot yang Memiliki Foto
    function renderSlotPhotoAvailable(fotoId, pos, photoUrl) {
        var container = $('#slotContainer_' + fotoId + '_' + pos);
        var previewBox = $('#previewBox_' + fotoId + '_' + pos);
        var statusIcon = $('#slotStatusIcon_' + fotoId + '_' + pos);

        statusIcon.html('<i class="fas fa-check-circle text-success" title="Foto Tersedia"></i>');

        previewBox.html(
            '<div class="position-relative d-inline-block">' +
                '<img src="' + photoUrl + '" class="img-thumbnail shadow-sm cursor-pointer img-preview-clickable" data-src="' + photoUrl + '" style="max-height: 120px; max-width: 100%; object-fit: contain; border-radius: 6px;">' +
                '<span class="badge badge-warning text-dark font-weight-bold shadow-sm" style="position: absolute; bottom: 4px; right: 4px; font-size: 0.62rem;">' +
                    '<i class="fas fa-certificate mr-0.5"></i>TimeMark' +
                '</span>' +
            '</div>'
        );

        var actionBtns = container.find('.action-btn-group .btn-group');
        if (actionBtns.find('.btn-delete-single').length === 0) {
            actionBtns.append(
                '<button type="button" class="btn btn-outline-danger btn-xs py-1 btn-delete-single" data-foto-id="' + fotoId + '" data-pos="' + pos + '" title="Hapus Foto">' +
                    '<i class="fas fa-trash-alt"></i>' +
                '</button>'
            );
        }
    }

    // Render Tampilan Slot yang Kosong
    function renderSlotPhotoEmpty(fotoId, pos, label) {
        var container = $('#slotContainer_' + fotoId + '_' + pos);
        var previewBox = $('#previewBox_' + fotoId + '_' + pos);
        var statusIcon = $('#slotStatusIcon_' + fotoId + '_' + pos);

        statusIcon.html('<i class="fas fa-times-circle text-danger" title="Belum Ada Foto"></i>');

        previewBox.html(
            '<div class="py-4 border border-dashed rounded bg-white text-muted" style="border-style: dashed !important; border-color: #cbd5e1 !important;">' +
                '<i class="fas fa-camera fa-2x mb-1 text-secondary" style="opacity: 0.35;"></i>' +
                '<div class="small font-weight-bold text-muted">Belum Ada Foto</div>' +
            '</div>'
        );

        container.find('.action-btn-group .btn-delete-single').remove();
    }

    // Update Metrik Statistik & Progress Bar Real-Time
    function updateStatsUI(res) {
        if (!res) return;

        var filled = res.filled_slots || 0;
        var total = res.total_slots || 0;
        var percentage = res.percentage || 0;
        var empty = Math.max(0, total - filled);

        $('#statFilledSlots').html(filled + ' <small class="text-success font-weight-normal">Foto</small>');
        $('#statEmptySlots').html(empty + ' <small class="text-danger font-weight-normal">Foto</small>');
        $('#progressPercentageText').text(percentage + '% Selesai');
        $('#mainProgressBar').css('width', percentage + '%').attr('aria-valuenow', percentage);

        if (res.status_color === 'success') {
            $('#mainProgressBar').removeClass('bg-primary').addClass('bg-success');
        } else {
            $('#mainProgressBar').removeClass('bg-success').addClass('bg-primary');
        }

        var overallBadge = $('#badgeOverallStatus');
        overallBadge.removeClass('badge-success badge-warning badge-danger').addClass('badge-' + res.status_color);
        $('#labelOverallStatus').text(res.status_label || (percentage + '%'));

        // Update badge kelompok yang bersangkutan
        if (activeFotoId) {
            recalculateGroupBadge(activeFotoId);
        }
    }

    // Rekalkulasi badge kelompok tertentu
    function recalculateGroupBadge(fId) {
        var totalInGroup = 4;
        var filledInGroup = $('#groupCard_' + fId + ' .slot-status-icon .fa-check-circle').length;
        var gStatus = (filledInGroup === 4) ? 'success' : ((filledInGroup > 0) ? 'warning' : 'danger');

        var badge = $('#groupBadge_' + fId);
        badge.removeClass('badge-success badge-warning badge-danger').addClass('badge-' + gStatus);
        badge.text(filledInGroup + ' / 4 Foto Terisi');

        var card = $('#groupCard_' + fId);
        card.removeClass('border-success border-warning border-danger').addClass('border-' + gStatus);
    }
});
</script>
<?= $this->endSection(); ?>
