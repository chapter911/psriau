<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">

    <!-- Top Profile / Info Header -->
    <?php if ($is_konsultan): ?>
        <div class="card shadow-sm border-0 mb-3" style="border-radius: 12px; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #ffffff;">
            <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-3 shadow" style="width: 52px; height: 52px; font-size: 1.4rem;">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div>
                        <h5 class="font-weight-bold mb-1 text-white"><?= esc($current_konsultan['nama'] ?? 'Konsultan Individual'); ?></h5>
                        <div class="text-white-50 small">
                            <span><i class="fas fa-id-card mr-1"></i> NIP: <strong><?= esc($current_konsultan['nip'] ?? '-'); ?></strong></span>
                            <span class="mx-2">•</span>
                            <span><i class="fas fa-briefcase mr-1"></i> <?= esc($current_konsultan['jabatan_label'] ?? 'Konsultan Individual'); ?></span>
                            <span class="badge badge-success ml-2 font-weight-normal px-2 py-1">Konsultan Individual</span>
                        </div>
                    </div>
                </div>
                <?php if (!empty($permissions['add'])): ?>
                    <button type="button" class="btn btn-primary font-weight-bold shadow-sm px-3 py-2 mt-3 mt-md-0" data-toggle="modal" data-target="#modalUploadKontrak" style="border-radius: 8px;">
                        <i class="fas fa-cloud-upload-alt mr-2"></i> Unggah Kontrak Baru
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="callout callout-info shadow-sm bg-white mb-3" style="border-radius: 10px; border-left: 4px solid #007bff;">
            <div class="d-flex align-items-center">
                <i class="fas fa-info-circle text-primary fa-2x mr-3"></i>
                <div>
                    <h6 class="font-weight-bold text-dark mb-1">Informasi Akses Administrator / Pengawas</h6>
                    <p class="mb-0 text-muted small">
                        Pengunggahan dokumen kontrak kerja dilakukan secara mandiri oleh masing-masing Pegawai berjenis <strong>Konsultan Individual</strong> melalui akun login mereka.
                        Administrator dan pengawas memiliki hak akses untuk memantau kelengkapan kontrak, memeriksa masa berlaku, serta melihat dan mengunduh berkas PDF.
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Data Card -->
    <div class="card shadow-sm" style="border: 1px solid #e9eef5; border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white py-3" style="border-bottom: 1px solid #e9eef5;">
            <div class="d-flex flex-wrap justify-content-between align-items-center w-100">
                <div>
                    <h3 class="card-title mb-0 font-weight-bold text-dark" style="font-size: 1.15rem; line-height: 1.8;">
                        <i class="fas fa-file-contract text-primary mr-2"></i>Dokumen Kontrak Kerja Konsultan Individual
                    </h3>
                    <small class="text-muted d-block mt-1">Daftar arsip dokumen kontrak kerja berdasarkan periode tanggal mulai dan tanggal selesai</small>
                </div>

                <!-- Filter for Admin -->
                <?php if (! $is_konsultan && ! empty($konsultan_list)): ?>
                    <div class="d-flex align-items-center mt-2 mt-md-0">
                        <label for="filter_pegawai" class="small text-muted font-weight-bold mr-2 mb-0">Filter Konsultan:</label>
                        <select id="filter_pegawai" class="form-control form-control-sm" style="width: 250px; border-radius: 6px;">
                            <option value="">-- Semua Konsultan Individual --</option>
                            <?php foreach ($konsultan_list as $k): ?>
                                <option value="<?= (int) $k['id']; ?>"><?= esc($k['nama']); ?> (<?= esc($k['nip']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-hover table-striped w-100" id="tableKontrak">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th>Pegawai Konsultan</th>
                            <th>Periode Kontrak</th>
                            <th class="text-center">Durasi</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Berkas PDF</th>
                            <th>Keterangan</th>
                            <th>Tanggal Unggah</th>
                            <th class="text-center" style="width: 90px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Modal Upload Kontrak -->
<?php if ($is_konsultan && ! empty($permissions['add'])): ?>
<div class="modal fade" id="modalUploadKontrak" tabindex="-1" role="dialog" aria-labelledby="modalUploadKontrakLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title font-weight-bold" id="modalUploadKontrakLabel">
                    <i class="fas fa-file-contract mr-2"></i> Unggah Dokumen Kontrak
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formUploadKontrak" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="modal-body p-4">
                    <!-- Info Pegawai -->
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark small mb-1">Nama Konsultan Individual</label>
                        <input type="text" class="form-control bg-light" value="<?= esc($current_konsultan['nama'] ?? ''); ?> (NIP: <?= esc($current_konsultan['nip'] ?? ''); ?>)" readonly>
                    </div>

                    <!-- Periode Tanggal -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="tanggal_mulai" class="font-weight-bold text-dark small mb-1">
                                    Tanggal Mulai Kontrak <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" required>
                                <small class="text-danger d-none" data-error="tanggal_mulai"></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="tanggal_selesai" class="font-weight-bold text-dark small mb-1">
                                    Tanggal Selesai Kontrak <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" required>
                                <small class="text-danger d-none" data-error="tanggal_selesai"></small>
                            </div>
                        </div>
                    </div>

                    <!-- Upload PDF File -->
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark small mb-1">
                            Berkas Kontrak (PDF) <span class="text-danger">*</span>
                        </label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="file_pdf" name="file_pdf" accept=".pdf,application/pdf" required>
                            <label class="custom-file-label" for="file_pdf" id="file_pdf_label">Pilih file PDF...</label>
                        </div>
                        <small class="form-text text-muted">Hanya menerima format <strong>PDF</strong>. Ukuran maksimum: <strong>20 MB</strong>.</small>
                        <small class="text-danger d-none" data-error="file_pdf"></small>
                    </div>

                    <!-- Keterangan -->
                    <div class="form-group mb-0">
                        <label for="keterangan" class="font-weight-bold text-dark small mb-1">Keterangan / Catatan Tambahan (Opsional)</label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="2" placeholder="Contoh: Kontrak Kerja Perubahan Addendum I..."></textarea>
                        <small class="text-danger d-none" data-error="keterangan"></small>
                    </div>

                    <!-- Upload Progress -->
                    <div class="progress mt-3 d-none" id="uploadProgress" style="height: 10px; border-radius: 5px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3" id="btnSubmitKontrak">
                        <i class="fas fa-save mr-1"></i> Simpan & Unggah
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Preview PDF -->
<div class="modal fade" id="modalPreviewPdf" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 90vw;" role="document">
        <div class="modal-content border-0 shadow-lg" style="height: 88vh; border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-dark text-white py-2 px-3">
                <h6 class="modal-title font-weight-bold mb-0">
                    <i class="fas fa-file-pdf text-danger mr-2"></i> Pratinjau Dokumen Kontrak
                </h6>
                <div class="d-flex align-items-center">
                    <a href="#" id="btnDownloadPreview" class="btn btn-outline-light btn-sm mr-2" download>
                        <i class="fas fa-download mr-1"></i> Unduh
                    </a>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
            <div class="modal-body p-0" style="height: calc(100% - 48px); background-color: #525659;">
                <iframe id="iframePdfPreview" src="" style="width: 100%; height: 100%; border: none;"></iframe>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
$(document).ready(function() {
    // 1. Initialize DataTable
    const table = $('#tableKontrak').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        ajax: {
            url: '<?= site_url('admin/konsultan-individual/kontrak/data'); ?>',
            type: 'GET',
            data: function(d) {
                const filterPegawai = $('#filter_pegawai').val();
                if (filterPegawai) {
                    d.pegawai_id = filterPegawai;
                }
            }
        },
        columns: [
            { data: 'no', className: 'text-center align-middle font-weight-bold' },
            { data: 'nama_pegawai', className: 'align-middle' },
            { data: 'periode_kontrak', className: 'align-middle' },
            { data: 'durasi', className: 'text-center align-middle' },
            { data: 'status_badge', className: 'text-center align-middle' },
            { data: 'file_badge', className: 'text-center align-middle' },
            { data: 'keterangan', className: 'align-middle small' },
            { data: 'created_at', className: 'align-middle small text-muted' },
            { data: 'action', className: 'text-center align-middle', orderable: false, searchable: false }
        ],
        order: [[2, 'desc']],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            zeroRecords: "Tidak ada data kontrak yang ditemukan",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(disaring dari _MAX_ total data)",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Berikutnya",
                previous: "Sebelumnya"
            }
        }
    });

    // Filter change
    $('#filter_pegawai').on('change', function() {
        table.ajax.reload();
    });

    // Custom file input label update
    $('#file_pdf').on('change', function() {
        const file = this.files[0];
        if (file) {
            // Check size max 20MB
            if (file.size > 20 * 1024 * 1024) {
                Swal.fire({
                    icon: 'error',
                    title: 'Ukuran Berkas Terlalu Besar',
                    text: 'Ukuran berkas melebihi batas maksimal 20 MB.'
                });
                $(this).val('');
                $('#file_pdf_label').text('Pilih file PDF...');
                return;
            }
            $('#file_pdf_label').text(file.name);
        } else {
            $('#file_pdf_label').text('Pilih file PDF...');
        }
    });

    // Handle AJAX Form Upload
    $('#formUploadKontrak').on('submit', function(e) {
        e.preventDefault();

        // Clear errors
        $('[data-error]').addClass('d-none').text('');

        const formData = new FormData(this);
        const $btn = $('#btnSubmitKontrak');
        const $progress = $('#uploadProgress');
        const $bar = $progress.find('.progress-bar');

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengunggah...');
        $progress.removeClass('d-none');
        $bar.css('width', '0%').text('0%');

        $.ajax({
            url: '<?= site_url('admin/konsultan-individual/kontrak/tambah'); ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                const xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(evt) {
                    if (evt.lengthComputable) {
                        const percentComplete = Math.round((evt.loaded / evt.total) * 100);
                        $bar.css('width', percentComplete + '%').text(percentComplete + '%');
                    }
                }, false);
                return xhr;
            },
            success: function(res) {
                $('#modalUploadKontrak').modal('hide');
                $('#formUploadKontrak')[0].reset();
                $('#file_pdf_label').text('Pilih file PDF...');
                $progress.addClass('d-none');
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan & Unggah');

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: res.message || 'Berkas kontrak berhasil diunggah.'
                });

                table.ajax.reload(null, false);
            },
            error: function(xhr) {
                $progress.addClass('d-none');
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan & Unggah');

                const res = xhr.responseJSON;
                if (xhr.status === 422 && res && res.errors) {
                    $.each(res.errors, function(field, msg) {
                        const $err = $('[data-error="' + field + '"]');
                        if ($err.length) {
                            $err.removeClass('d-none').text(msg);
                        }
                    });
                    Swal.fire({
                        icon: 'warning',
                        title: 'Periksa Isian Form',
                        text: 'Terdapat isian yang belum sesuai. Silakan periksa kembali.'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: (res && res.message) ? res.message : 'Terjadi kesalahan sistem saat mengunggah.'
                    });
                }
            }
        });
    });

    // Handle Delete Contract
    $(document).on('click', '.btn-delete-kontrak', function(e) {
        e.preventDefault();
        const deleteUrl = $(this).data('url');

        Swal.fire({
            title: 'Hapus Dokumen Kontrak?',
            text: 'Dokumen kontrak dan berkas PDF fisik akan dihapus secara permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: deleteUrl,
                    type: 'POST',
                    data: {
                        '<?= csrf_token(); ?>': '<?= csrf_hash(); ?>'
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Dihapus',
                            text: res.message || 'Dokumen kontrak berhasil dihapus.'
                        });
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        const res = xhr.responseJSON;
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: (res && res.message) ? res.message : 'Gagal menghapus dokumen kontrak.'
                        });
                    }
                });
            }
        });
    });

    // Handle Preview Modal
    $(document).on('click', 'a[title="Preview PDF"], a[title="Lihat Dokumen"]', function(e) {
        // If screen width is small (mobile), let it open in new tab
        if ($(window).width() < 768) {
            return;
        }

        e.preventDefault();
        const previewUrl = $(this).attr('href');
        const downloadUrl = previewUrl.replace('/preview', '/download');

        $('#iframePdfPreview').attr('src', previewUrl);
        $('#btnDownloadPreview').attr('href', downloadUrl);
        $('#modalPreviewPdf').modal('show');
    });

    $('#modalPreviewPdf').on('hidden.bs.modal', function() {
        $('#iframePdfPreview').attr('src', '');
    });
});
</script>
<?= $this->endSection(); ?>
