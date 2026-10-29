<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">

    <!-- Top Banner -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%); color: #ffffff;">
        <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <h4 class="font-weight-bold mb-1 text-white">
                    <i class="fas fa-check-double mr-2 text-warning"></i> Checklist 28 Dokumen Swakelola Tipe I
                </h4>
                <p class="text-white-50 mb-0 small">
                    <?= esc($paket['nama_kegiatan'] ?? 'Kegiatan Swakelola'); ?> — TA <?= esc($paket['tahun_anggaran'] ?? '2025'); ?>
                </p>
                <div class="mt-2 text-white-50 small">
                    <span class="mr-3"><i class="fas fa-barcode mr-1"></i> Kode: <strong><?= esc($paket['kode_paket'] ?? '-'); ?></strong></span>
                    <span class="mr-3"><i class="fas fa-money-bill-wave mr-1"></i> Pagu: <strong>Rp <?= number_format((float)($paket['pagu_anggaran'] ?? 0), 0, ',', '.'); ?></strong></span>
                    <span><i class="fas fa-check-circle mr-1 text-success"></i> Realisasi: <strong>Rp <?= number_format((float)($paket['realisasi_anggaran'] ?? 0), 0, ',', '.'); ?></strong></span>
                </div>
            </div>
            <div class="mt-3 mt-md-0 d-flex flex-column align-items-end">
                <div class="badge badge-success px-3 py-2 font-weight-bold mb-2" style="font-size: 1rem; border-radius: 8px;">
                    <i class="fas fa-clipboard-check mr-1"></i> Status: <?= $totalAda; ?> / <?= $totalCount; ?> Dokumen Lengkap (<?= $persenAda; ?>%)
                </div>
                <a href="<?= base_url('admin/swakelola/laporan/export-excel'); ?>" class="btn btn-outline-light btn-sm font-weight-bold">
                    <i class="fas fa-file-excel mr-1"></i> Export Checklist (.xlsx)
                </a>
            </div>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle mr-2"></i> <?= session()->getFlashdata('success'); ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i> <?= session()->getFlashdata('error'); ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Navigation Tabs / Sections -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list-ol mr-2"></i> Rincian 28 Dokumen Berdasarkan 5 Tahapan Swakelola (LHA No. 9)
            </h6>
            <a href="<?= base_url('admin/swakelola/paket'); ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar Paket
            </a>
        </div>
        <div class="card-body p-3">

            <?php foreach ($grouped as $code => $group): ?>
                <div class="card border mb-4 shadow-none" style="border-radius: 8px; overflow: hidden;">
                    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                        <span class="font-weight-bold text-dark" style="font-size: 0.95rem;">
                            <i class="fas fa-folder-open mr-2 text-primary"></i> <?= esc($group['label']); ?>
                        </span>
                        <span class="badge badge-info px-2 py-1 font-weight-normal">
                            <?= count($group['items']); ?> Dokumen
                        </span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="thead-light">
                                <tr style="font-size: 0.85rem;">
                                    <th class="text-center" style="width: 45px;">No</th>
                                    <th>Nama Dokumen Kelengkapan</th>
                                    <th class="text-center" style="width: 90px;">Status</th>
                                    <th>Nama Berkas Digital</th>
                                    <th>Keterangan / Uraian</th>
                                    <th class="text-center" style="width: 170px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($group['items'] as $doc): ?>
                                    <tr>
                                        <td class="text-center font-weight-bold"><?= $doc['nomor_urut']; ?></td>
                                        <td class="font-weight-bold text-dark" style="font-size: 0.9rem;">
                                            <?= esc($doc['nama_dokumen']); ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-success px-2 py-1">
                                                <i class="fas fa-check mr-1"></i> <?= esc($doc['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                                $ext = strtolower(pathinfo($doc['file_name'], PATHINFO_EXTENSION));
                                                $iconClass = ($ext === 'pdf') ? 'fa-file-pdf text-danger' : 'fa-file-word text-primary';
                                            ?>
                                            <small class="font-weight-bold">
                                                <i class="fas <?= $iconClass; ?> mr-1"></i> <?= esc($doc['file_name']); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?= esc($doc['keterangan']); ?></small>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-info btn-preview-doc" data-id="<?= $doc['id']; ?>" title="Lihat Pratinjau Dokumen">
                                                    <i class="fas fa-eye mr-1"></i> Preview
                                                </button>
                                                <a href="<?= base_url('admin/swakelola/dokumen/download/' . $doc['id']); ?>" class="btn btn-outline-primary" title="Unduh Berkas Resmi">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>
    </div>

</div>

<!-- MODAL PREVIEW DOKUMEN -->
<div class="modal fade" id="modalPreviewDokumen" tabindex="-1" role="dialog" aria-labelledby="modalPreviewTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document" style="max-width: 92vw;">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden; height: 92vh;">
            <!-- Modal Header -->
            <div class="modal-header bg-dark text-white py-3 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center overflow-hidden mr-3">
                    <span id="previewDocIcon" class="mr-2" style="font-size: 1.3rem;">
                        <i class="fas fa-file-alt text-warning"></i>
                    </span>
                    <div class="text-truncate">
                        <h6 class="modal-title font-weight-bold text-white mb-0 text-truncate" id="previewModalTitle">
                            Pratinjau Dokumen
                        </h6>
                        <small class="text-white-50" id="previewDocSubtitle">-</small>
                    </div>
                </div>
                <div class="d-flex align-items-center flex-shrink-0">
                    <a href="#" target="_blank" class="btn btn-sm btn-outline-light mr-2 font-weight-bold" id="previewBtnNewTab">
                        <i class="fas fa-external-link-alt mr-1"></i> Buka Tab Baru
                    </a>
                    <a href="#" class="btn btn-sm btn-success mr-2 font-weight-bold" id="previewBtnDownload">
                        <i class="fas fa-download mr-1"></i> Unduh
                    </a>
                    <button type="button" class="close text-white ml-2" data-dismiss="modal" aria-label="Close" style="opacity: 0.85;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-0 position-relative" style="background-color: #525659; height: calc(92vh - 65px); overflow: hidden;">
                <!-- Loading Overlay: Gunakan inline style tanpa class d-flex agar jQuery hide/fadeOut tidak tertimpa !important -->
                <div id="previewLoading" class="position-absolute w-100 h-100 flex-column justify-content-center align-items-center" style="display: none; background: rgba(30, 41, 59, 0.88); z-index: 1050; color: #fff;">
                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                    <h6 class="font-weight-bold mb-1" id="previewLoadingText">Memuat berkas dokumen...</h6>
                    <small class="text-white-50">Menyiapkan pratinjau halaman</small>
                </div>

                <!-- Iframe Viewer for PDF -->
                <iframe id="previewIframe" src="" style="width: 100%; height: 100%; border: none; display: none;"></iframe>

                <!-- Div Container for DOCX render -->
                <div id="docxPreviewWrapper" style="width: 100%; height: 100%; overflow-y: auto; padding: 25px; display: none;">
                    <div id="docxContainer" style="max-width: 850px; margin: 0 auto; background: #ffffff; padding: 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); border-radius: 4px; min-height: 100%;"></div>
                </div>

                <!-- Fallback / Error message -->
                <div id="previewError" class="p-5 text-center text-white" style="display: none;">
                    <i class="fas fa-exclamation-circle text-danger mb-3" style="font-size: 3rem;"></i>
                    <h5 class="font-weight-bold" id="previewErrorMsg">Gagal memuat pratinjau</h5>
                    <p class="text-white-50 small mb-4">Anda tetap dapat mengunduh berkas ini secara langsung ke perangkat Anda.</p>
                    <a href="#" class="btn btn-primary" id="previewErrorDownloadBtn">
                        <i class="fas fa-download mr-1"></i> Unduh Berkas Sekarang
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<!-- Local copies of JSZip and docx-preview with CDN fallback -->
<script src="<?= base_url('assets/docx/jszip.min.js'); ?>"></script>
<script>
if (typeof JSZip === 'undefined') {
    document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"><\/script>');
}
</script>

<script src="<?= base_url('assets/docx/docx-preview.min.js'); ?>"></script>
<script>
if (typeof docx === 'undefined') {
    document.write('<script src="https://unpkg.com/docx-preview@0.3.3/dist/docx-preview.min.js"><\/script>');
}
</script>

<style>
/* Word document styling emulation - preserve native docx-preview borders & justification */
#docxContainer {
    font-family: Arial, sans-serif;
    color: #000;
}
#docxContainer table {
    border-collapse: collapse;
    max-width: 100%;
}
#docxContainer img {
    max-width: 100% !important;
    height: auto !important;
}
</style>

<script>
$(document).ready(function() {
    function showLoader(text) {
        $('#previewLoadingText').text(text || 'Memuat berkas...');
        $('#previewLoading').css('display', 'flex');
    }

    function hideLoader() {
        $('#previewLoading').css('display', 'none');
    }

    $('.btn-preview-doc').on('click', function() {
        const docId = $(this).data('id');
        
        // Reset states
        showLoader('Memuat informasi dokumen...');
        $('#previewIframe').hide().attr('src', '');
        $('#docxPreviewWrapper').hide();
        $('#docxContainer').empty();
        $('#previewError').hide();
        
        $('#modalPreviewDokumen').modal('show');

        // Fetch document info via AJAX
        $.ajax({
            url: '<?= base_url('admin/swakelola/dokumen/preview'); ?>/' + docId,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status !== 'success') {
                    showError(res.message || 'Gagal memuat dokumen');
                    return;
                }

                // Set header info
                $('#previewModalTitle').text(res.nomor_urut + '. ' + res.nama_dokumen);
                $('#previewDocSubtitle').text(res.file_name);
                $('#previewBtnDownload').attr('href', res.download_url);
                $('#previewBtnNewTab').attr('href', res.inline_url);
                $('#previewErrorDownloadBtn').attr('href', res.download_url);

                if (res.ext === 'pdf') {
                    $('#previewDocIcon').html('<i class="fas fa-file-pdf text-danger"></i>');
                    showLoader('Menyiapkan tampilan PDF...');
                    
                    $('#previewIframe')
                        .off('load')
                        .on('load', function() {
                            hideLoader();
                        })
                        .attr('src', res.inline_url)
                        .show();

                    // Pengaman: hilangkan loader maksimal 400ms jika browser render cepat
                    setTimeout(function() {
                        hideLoader();
                    }, 400);
                } else if (res.ext === 'docx') {
                    $('#previewDocIcon').html('<i class="fas fa-file-word text-primary"></i>');
                    showLoader('Merender dokumen Word (.docx)...');

                    // Tampilkan wrapper kontainer segera
                    $('#docxPreviewWrapper').show();

                    // Fetch arrayBuffer and render via docx-preview
                    fetch(res.inline_url)
                        .then(function(response) {
                            if (!response.ok) throw new Error('HTTP ' + response.status);
                            return response.arrayBuffer();
                        })
                        .then(function(buffer) {
                            const container = document.getElementById('docxContainer');
                            if (typeof docx !== 'undefined' && docx.renderAsync) {
                                docx.renderAsync(buffer, container, null, {
                                    className: "docx-preview-content",
                                    inWrapper: false,
                                    ignoreWidth: true,
                                    ignoreHeight: true,
                                    ignoreFonts: true, // Super cepat tanpa unduh web font berat
                                    breakPages: true
                                }).then(function() {
                                    hideLoader();
                                }).catch(function(err) {
                                    console.warn('Docx render notice:', err);
                                    // Dokumen tetap tampil di DOM, langsung tutup loader
                                    hideLoader();
                                });

                                // Fail-safe: loader PASTI ditutup dalam 250ms setelah render dimulai
                                setTimeout(function() {
                                    hideLoader();
                                }, 250);
                            } else {
                                hideLoader();
                                showError('Library docx-preview tidak tersedia.');
                            }
                        })
                        .catch(function(err) {
                            hideLoader();
                            console.error('Fetch error:', err);
                            showError('Gagal mengunduh berkas Word untuk pratinjau: ' + err.message);
                        });
                } else {
                    hideLoader();
                    $('#previewDocIcon').html('<i class="fas fa-file text-warning"></i>');
                    showError('Format berkas ' + res.ext.toUpperCase() + ' tidak mendukung pratinjau langsung. Silakan unduh berkas.');
                }
            },
            error: function(xhr, status, error) {
                hideLoader();
                showError('Koneksi ke server gagal: ' + error);
            }
        });
    });

    function showError(msg) {
        hideLoader();
        $('#previewIframe').hide();
        $('#docxPreviewWrapper').hide();
        $('#previewErrorMsg').text(msg);
        $('#previewError').fadeIn(150);
    }

    // Clean up when modal closed
    $('#modalPreviewDokumen').on('hidden.bs.modal', function() {
        hideLoader();
        $('#previewIframe').attr('src', '');
        $('#docxContainer').empty();
    });
});
</script>
<?= $this->endSection(); ?>
