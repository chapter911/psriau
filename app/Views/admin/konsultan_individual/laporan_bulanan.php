<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">

    <!-- Top Profile / Info Header -->
    <?php if ($is_konsultan): ?>
        <div class="card shadow-sm border-0 mb-3" style="border-radius: 12px; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #ffffff;">
            <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center mr-3 shadow" style="width: 52px; height: 52px; font-size: 1.4rem;">
                        <i class="fas fa-calendar-check"></i>
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
                    <button type="button" class="btn btn-primary font-weight-bold shadow-sm px-3 py-2 mt-3 mt-md-0" data-toggle="modal" data-target="#modalUploadLaporan" style="border-radius: 8px;">
                        <i class="fas fa-cloud-upload-alt mr-2"></i> Unggah Laporan Bulanan
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="callout callout-info shadow-sm bg-white mb-3" style="border-radius: 10px; border-left: 4px solid #17a2b8;">
            <div class="d-flex align-items-center">
                <i class="fas fa-info-circle text-info fa-2x mr-3"></i>
                <div>
                    <h6 class="font-weight-bold text-dark mb-1">Informasi Akses Administrator / Pengawas</h6>
                    <p class="mb-0 text-muted small">
                        Pengunggahan laporan bulanan dilakukan secara mandiri oleh masing-masing Pegawai berjenis <strong>Konsultan Individual</strong> melalui akun login mereka.
                        Administrator dan pengawas memiliki hak akses untuk memantau ketepatan waktu pengumpulan laporan, melihat dokumen, serta mengunduh berkas laporan bulanan seluruh konsultan.
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
                        <i class="fas fa-calendar-check text-info mr-2"></i>Arsip Laporan Bulanan Konsultan Individual
                    </h3>
                    <small class="text-muted d-block mt-1">Daftar laporan berkala per bulan yang diunggah oleh pegawai konsultan individual</small>
                </div>

                <!-- Filters -->
                <div class="d-flex flex-wrap align-items-center gap-2 mt-2 mt-md-0">
                    <!-- Filter Tahun -->
                    <div class="mr-2">
                        <select id="filter_tahun" class="form-control form-control-sm" style="width: 110px; border-radius: 6px;">
                            <option value="">Semua Tahun</option>
                            <?php foreach ($years as $y): ?>
                                <option value="<?= $y; ?>" <?= $y === $current_year ? 'selected' : ''; ?>><?= $y; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Filter Bulan -->
                    <div class="mr-2">
                        <select id="filter_bulan" class="form-control form-control-sm" style="width: 140px; border-radius: 6px;">
                            <option value="">Semua Bulan</option>
                            <?php foreach ($months as $num => $namaBulan): ?>
                                <option value="<?= $num; ?>"><?= $namaBulan; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Filter Pegawai for Admin -->
                    <?php if (! $is_konsultan && ! empty($konsultan_list)): ?>
                        <div>
                            <select id="filter_pegawai" class="form-control form-control-sm" style="width: 220px; border-radius: 6px;">
                                <option value="">Semua Konsultan</option>
                                <?php foreach ($konsultan_list as $k): ?>
                                    <option value="<?= (int) $k['id']; ?>"><?= esc($k['nama']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-hover table-striped w-100" id="tableLaporan">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th>Pegawai Konsultan</th>
                            <th>NIP</th>
                            <th>Periode Laporan</th>
                            <th class="text-center">Berkas Laporan (PDF)</th>
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

<!-- Modal Upload Laporan Bulanan (Tanpa Judul sesuai kebutuhan) -->
<?php if ($is_konsultan && ! empty($permissions['add'])): ?>
<div class="modal fade" id="modalUploadLaporan" tabindex="-1" role="dialog" aria-labelledby="modalUploadLaporanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-info text-white py-3">
                <h5 class="modal-title font-weight-bold" id="modalUploadLaporanLabel">
                    <i class="fas fa-calendar-check mr-2"></i> Unggah Laporan Bulanan
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formUploadLaporan" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="modal-body p-4">
                    <!-- Info Pegawai -->
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark small mb-1">Nama Konsultan Individual</label>
                        <input type="text" class="form-control bg-light" value="<?= esc($current_konsultan['nama'] ?? ''); ?> (NIP: <?= esc($current_konsultan['nip'] ?? ''); ?>)" readonly>
                    </div>

                    <!-- Periode: Bulan & Tahun -->
                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group mb-3">
                                <label for="bulan" class="font-weight-bold text-dark small mb-1">
                                    Bulan Laporan <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" id="bulan" name="bulan" required>
                                    <?php foreach ($months as $num => $namaBulan): ?>
                                        <option value="<?= $num; ?>" <?= $num === $current_month ? 'selected' : ''; ?>>
                                            <?= sprintf('%02d', $num); ?> - <?= $namaBulan; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-danger d-none" data-error="bulan"></small>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group mb-3">
                                <label for="tahun" class="font-weight-bold text-dark small mb-1">
                                    Tahun <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" id="tahun" name="tahun" required>
                                    <?php foreach ($years as $y): ?>
                                        <option value="<?= $y; ?>" <?= $y === $current_year ? 'selected' : ''; ?>>
                                            <?= $y; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-danger d-none" data-error="tahun"></small>
                            </div>
                        </div>
                    </div>

                    <!-- Upload PDF File -->
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark small mb-1">
                            Berkas Laporan (PDF) <span class="text-danger">*</span>
                        </label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="file_pdf_laporan" name="file_pdf" accept=".pdf,application/pdf" required>
                            <label class="custom-file-label" for="file_pdf_laporan" id="file_pdf_laporan_label">Pilih file PDF...</label>
                        </div>
                        <small class="form-text text-muted">Hanya menerima format <strong>PDF</strong>. Ukuran maksimum: <strong>20 MB</strong>.</small>
                        <small class="text-danger d-none" data-error="file_pdf"></small>
                    </div>

                    <!-- Keterangan (Opsional) - Catatan: Tanpa Judul -->
                    <div class="form-group mb-0">
                        <label for="keterangan_laporan" class="font-weight-bold text-dark small mb-1">Keterangan / Catatan (Opsional)</label>
                        <textarea class="form-control" id="keterangan_laporan" name="keterangan" rows="2" placeholder="Catatan singkat terkait laporan kegiatan bulanan..."></textarea>
                        <small class="text-danger d-none" data-error="keterangan"></small>
                    </div>

                    <!-- Upload Progress -->
                    <div class="progress mt-3 d-none" id="uploadProgressLaporan" style="height: 10px; border-radius: 5px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info btn-sm px-3" id="btnSubmitLaporan">
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
                    <i class="fas fa-file-pdf text-danger mr-2"></i> Pratinjau Laporan Bulanan
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
    const table = $('#tableLaporan').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        ajax: {
            url: '<?= site_url('admin/konsultan-individual/laporan-bulanan/data'); ?>',
            type: 'GET',
            data: function(d) {
                const filterTahun = $('#filter_tahun').val();
                if (filterTahun) {
                    d.tahun = filterTahun;
                }
                const filterBulan = $('#filter_bulan').val();
                if (filterBulan) {
                    d.bulan = filterBulan;
                }
                const filterPegawai = $('#filter_pegawai').val();
                if (filterPegawai) {
                    d.pegawai_id = filterPegawai;
                }
            }
        },
        columns: [
            { data: 'no', className: 'text-center align-middle font-weight-bold' },
            { data: 'nama_pegawai', className: 'align-middle' },
            { data: 'nip_pegawai', className: 'align-middle small text-muted' },
            { data: 'periode', className: 'align-middle' },
            { data: 'file_badge', className: 'text-center align-middle' },
            { data: 'keterangan', className: 'align-middle small' },
            { data: 'created_at', className: 'align-middle small text-muted' },
            { data: 'action', className: 'text-center align-middle', orderable: false, searchable: false }
        ],
        order: [[3, 'desc']],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            zeroRecords: "Tidak ada data laporan bulanan yang ditemukan",
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

    // Filter changes
    $('#filter_tahun, #filter_bulan, #filter_pegawai').on('change', function() {
        table.ajax.reload();
    });

    // Custom file input label update
    $('#file_pdf_laporan').on('change', function() {
        const file = this.files[0];
        if (file) {
            if (file.size > 20 * 1024 * 1024) {
                Swal.fire({
                    icon: 'error',
                    title: 'Ukuran Berkas Terlalu Besar',
                    text: 'Ukuran berkas melebihi batas maksimal 20 MB.'
                });
                $(this).val('');
                $('#file_pdf_laporan_label').text('Pilih file PDF...');
                return;
            }
            $('#file_pdf_laporan_label').text(file.name);
        } else {
            $('#file_pdf_laporan_label').text('Pilih file PDF...');
        }
    });

    // Handle AJAX Form Upload
    $('#formUploadLaporan').on('submit', function(e) {
        e.preventDefault();

        $('[data-error]').addClass('d-none').text('');

        const formData = new FormData(this);
        const $btn = $('#btnSubmitLaporan');
        const $progress = $('#uploadProgressLaporan');
        const $bar = $progress.find('.progress-bar');

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengunggah...');
        $progress.removeClass('d-none');
        $bar.css('width', '0%').text('0%');

        $.ajax({
            url: '<?= site_url('admin/konsultan-individual/laporan-bulanan/tambah'); ?>',
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
                $('#modalUploadLaporan').modal('hide');
                $('#formUploadLaporan')[0].reset();
                $('#file_pdf_laporan_label').text('Pilih file PDF...');
                $progress.addClass('d-none');
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan & Unggah');

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: res.message || 'Laporan bulanan berhasil diunggah.'
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

    // Handle Delete Report
    $(document).on('click', '.btn-delete-laporan', function(e) {
        e.preventDefault();
        const deleteUrl = $(this).data('url');

        Swal.fire({
            title: 'Hapus Laporan Bulanan?',
            text: 'Dokumen laporan bulanan dan berkas PDF fisik akan dihapus secara permanen.',
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
                            text: res.message || 'Laporan bulanan berhasil dihapus.'
                        });
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        const res = xhr.responseJSON;
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: (res && res.message) ? res.message : 'Gagal menghapus laporan bulanan.'
                        });
                    }
                });
            }
        });
    });

    // Handle Preview Modal
    $(document).on('click', 'a[title="Preview PDF"], a[title="Lihat Laporan"]', function(e) {
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
