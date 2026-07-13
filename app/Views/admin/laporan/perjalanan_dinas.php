<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
    $reports = $reports ?? [];
    $canEdit = (bool) ($can_edit ?? false);
?>
<style>
    /* Table styling improvements */
    #tablePerjalananDinas thead th {
        vertical-align: middle !important;
        text-align: center;
        padding: 8px 10px !important;
        font-size: 13.5px;
        line-height: 1.3;
        font-weight: 600;
    }
    
    #tablePerjalananDinas tbody td {
        vertical-align: middle !important;
        padding: 8px 10px !important;
        font-size: 13.5px;
    }
    
    /* Make document action buttons larger and cleaner (icon only) */
    .doc-btn-group {
        display: inline-flex;
        flex-wrap: nowrap;
        gap: 6px;
        justify-content: center;
        align-items: center;
        white-space: nowrap;
    }
    
    .doc-btn-group .btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        font-size: 14px;
        transition: all 0.2s ease-in-out;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .doc-btn-group .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.15);
    }
    
    /* Align custom table sorting icons in AdminLTE/Bootstrap4 DataTables */
    table.dataTable thead .sorting::before, 
    table.dataTable thead .sorting_asc::before, 
    table.dataTable thead .sorting_desc::before, 
    table.dataTable thead .sorting_asc_disabled::before, 
    table.dataTable thead .sorting_desc_disabled::before,
    table.dataTable thead .sorting::after, 
    table.dataTable thead .sorting_asc::after, 
    table.dataTable thead .sorting_desc::after, 
    table.dataTable thead .sorting_asc_disabled::after, 
    table.dataTable thead .sorting_desc_disabled::after {
        bottom: 50% !important;
        transform: translateY(50%) !important;
    }
    
    /* Text styling for Tujuan column */
    .text-tujuan {
        max-width: 280px;
        font-size: 12.5px;
        line-height: 1.4;
        white-space: normal;
        word-break: break-word;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>

<div class="card">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title mb-0">Laporan Perjalanan Dinas</h3>
        <div class="card-tools ml-auto">
            <a href="<?= site_url('admin/surat/perjalanan-dinas/buat'); ?>" class="btn btn-primary btn-sm">Buat Laporan</a>
        </div>
    </div>
    <div class="card-body">

        <div class="card card-outline card-secondary mb-3">
            <div class="card-header py-2">
                <h3 class="card-title mb-0">Filter Laporan Perjalanan Dinas</h3>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="filter_start_date" class="font-weight-bold mb-1">Tanggal Mulai</label>
                        <input type="date" class="form-control form-control-sm" id="filter_start_date" value="<?= date('Y-m-01'); ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="filter_end_date" class="font-weight-bold mb-1">Tanggal Selesai</label>
                        <input type="date" class="form-control form-control-sm" id="filter_end_date" value="<?= date('Y-m-t'); ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="filter_kota" class="font-weight-bold mb-1">Kota Tujuan</label>
                        <select class="form-control form-control-sm" id="filter_kota" data-placeholder="Semua Kota/Kabupaten">
                            <option value=""></option>
                            <?php foreach ($kabupaten_options ?? [] as $kota): ?>
                                <option value="<?= esc($kota); ?>"><?= esc($kota); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="filter_pelaksana" class="font-weight-bold mb-1">Pelaksana</label>
                        <select class="form-control form-control-sm" id="filter_pelaksana" data-placeholder="Semua Pelaksana">
                            <option value=""></option>
                            <?php foreach ($pegawai_options ?? [] as $peg): ?>
                                <option value="<?= (int) ($peg['id'] ?? 0); ?>"><?= esc($peg['nama'] ?? ''); ?><?= !empty($peg['nip']) ? ' - NIP ' . esc($peg['nip']) : ''; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <small class="text-muted">Data akan diperbarui secara otomatis saat filter diubah.</small>
                    <div class="d-flex align-items-center" style="gap:10px;">
                        <button type="button" class="btn btn-danger btn-sm" id="btn-cetak-periode"><i class="fas fa-file-pdf mr-1"></i> Cetak Surat Tugas (Periode)</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-reset-filter">Reset Filter</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped w-100" id="tablePerjalananDinas">
                <thead>
                    <tr>
                        <th style="width:60px;" class="text-center">No</th>
                        <th style="width:280px;">Tujuan</th>
                        <th style="width:220px;">Kota Tujuan</th>
                        <th style="width:220px;">Periode</th>
                        <th>Nama Pelaksana</th>
                        <th style="width:110px;" class="text-center">Lihat Dokumen</th>
                        <?php if ($can_upload_verified ?? false): ?>
                            <th style="width:130px;" class="text-center">Upload Verified</th>
                        <?php endif; ?>
                        <th style="width:150px;" class="text-center">Verifikasi SPT</th>
                        <th style="width:90px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
</div>

<?php if ($can_upload_verified ?? false): ?>
<div class="modal fade" id="modal-upload-verified" tabindex="-1" role="dialog" aria-labelledby="modalUploadVerifiedTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUploadVerifiedTitle">Upload Verified Perjadin & SPT</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-upload-verified" action="" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold mb-1">Nomor Surat Tugas</label>
                        <p id="upload_nomor_label" class="form-control-plaintext text-secondary font-weight-bold py-0">-</p>
                    </div>
                    <div class="form-group">
                        <label for="verified_spt_file" class="font-weight-bold mb-1">File Laporan & SPT Terverifikasi <span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="verified_spt_file" name="verified_spt" accept=".pdf,.jpg,.jpeg,.png" required>
                            <label class="custom-file-label" for="verified_spt_file">Pilih file (PDF, JPG, JPEG, PNG)</label>
                        </div>
                        <small class="form-text text-muted mt-2">Maksimal ukuran file: 10MB. Mengupload file baru akan menimpa file terverifikasi sebelumnya jika ada.</small>
                    </div>
                    <div id="existing_verified_file_container" class="alert alert-info py-2 px-3 mt-3 d-none">
                        <i class="fas fa-info-circle mr-1"></i> File terverifikasi saat ini: 
                        <a href="" id="existing_verified_file_link" target="_blank" class="font-weight-bold text-white text-underline" style="text-decoration: underline;">Lihat File</a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Upload & Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($can_verify ?? false): ?>
<div class="modal fade" id="modal-verify-spt" role="dialog" aria-labelledby="modalVerifyTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
            <div class="modal-header bg-light py-3" style="border-bottom: 1px solid #e9eef5;">
                <h5 class="modal-title font-weight-bold text-dark" id="modalVerifyTitle">
                    <i class="fas fa-check-double text-success mr-2"></i>Verifikasi Laporan Perjadin & SPT
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-verify-spt" method="post" action="">
                <?= csrf_field(); ?>
                <div class="modal-body py-4">
                    <div class="form-group">
                        <label for="verify_nomor_surat" class="font-weight-bold mb-1">Nomor Surat Tugas <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="verify_nomor_surat" name="nomor_surat_tugas" required placeholder="Contoh: 132/SPT/Gs7/2026">
                    </div>
                    <div class="form-group">
                        <label for="verify_dasar_spt" class="font-weight-bold mb-1">Dasar SPT (Legal Basis) <span class="text-danger">*</span></label>
                        <select class="form-control select2" id="verify_dasar_spt" name="dasar_spt_ids[]" multiple="multiple" data-placeholder="Pilih Dasar SPT" style="width: 100%;" required>
                            <?php foreach ($dasar_spt_options ?? [] as $ds): ?>
                                <option value="<?= (int) ($ds['id'] ?? 0); ?>"><?= esc(strip_tags((string) ($ds['uraian'] ?? ''))); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted mt-1 d-block">Pilih satu atau lebih legal basis yang relevan.</small>
                    </div>
                    <div class="form-group mb-0">
                        <label for="verify_tanggal_ttd" class="font-weight-bold mb-1">Tanggal Tanda Tangan <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="verify_tanggal_ttd" name="tanggal_tanda_tangan" required onfocus="this.showPicker()">
                    </div>
                </div>
                <div class="modal-footer bg-light py-2" style="border-top: 1px solid #e9eef5;">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm font-weight-bold" id="btn-save-verify">Simpan Verifikasi</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus data perjalanan dinas ini?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                <a id="btnConfirmDelete" href="#" class="btn btn-danger btn-sm">Hapus</a>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script>
    (function () {
        if (typeof $ === 'undefined' || ! $.fn.DataTable) {
            return;
        }

        const $table = $('#tablePerjalananDinas');
        if (! $table.length || $.fn.dataTable.isDataTable($table)) {
            return;
        }

        const canEdit = <?= json_encode($canEdit, JSON_UNESCAPED_UNICODE); ?>;
        const canUploadVerified = <?= json_encode($can_upload_verified ?? false, JSON_UNESCAPED_UNICODE); ?>;
        const canVerify = <?= json_encode($can_verify ?? false, JSON_UNESCAPED_UNICODE); ?>;
        const dataUrl = <?= json_encode(site_url('admin/surat/perjalanan-dinas'), JSON_UNESCAPED_UNICODE); ?>;

        const $filterStartDate = $('#filter_start_date');
        const $filterEndDate = $('#filter_end_date');
        const $filterKota = $('#filter_kota');
        const $filterPelaksana = $('#filter_pelaksana');

        const columns = [
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { 
                data: 'tujuan',
                render: function (data) {
                    if (!data) return '-';
                    const escaped = $('<div/>').text(data).html();
                    return '<div class="text-tujuan" title="' + escaped + '">' + escaped + '</div>';
                }
            },
            { 
                data: 'kota_tujuan',
                render: function (data) {
                    return data ? $('<div/>').text(data).html() : '-';
                }
            },
            { 
                data: 'periode',
                render: function (data) {
                    return data ? $('<div/>').text(data).html() : '-';
                }
            },
            { 
                data: 'pelaksana_names_label',
                render: function (data) {
                    return data ? $('<div/>').text(data).html() : '-';
                }
            },
            {
                data: 'dokumen_html',
                orderable: false,
                searchable: false,
                className: 'text-center'
            }
        ];

        if (canUploadVerified) {
            columns.push({
                data: 'upload_verified_html',
                orderable: false,
                searchable: false,
                className: 'text-center'
            });
        }

        columns.push({
            data: 'verification_status_html',
            orderable: false,
            searchable: false,
            className: 'text-center'
        });

        columns.push({
            data: 'action_html',
            orderable: false,
            searchable: false,
            className: 'text-center'
        });

        const dt = $table.DataTable({
            processing: true,
            serverSide: true,
            responsive: false,
            autoWidth: false,
            scrollX: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50], [10, 25, 50]],
            order: [[0, 'desc']],
            ajax: {
                url: dataUrl,
                type: 'GET',
                data: function (d) {
                    d.filter_start_date = $filterStartDate.val();
                    d.filter_end_date = $filterEndDate.val();
                    d.filter_kota = $filterKota.val();
                    d.filter_pelaksana = $filterPelaksana.val();
                }
            },
            columns: columns,
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data',
                zeroRecords: 'Data tidak ditemukan',
                paginate: {
                    first: 'Awal',
                    last: 'Akhir',
                    next: 'Berikutnya',
                    previous: 'Sebelumnya'
                }
            }
        });

        // Trigger reload on filter changes
        $filterStartDate.on('change', function () { dt.ajax.reload(); });
        $filterEndDate.on('change', function () { dt.ajax.reload(); });
        $filterKota.on('change', function () { dt.ajax.reload(); });
        $filterPelaksana.on('change', function () { dt.ajax.reload(); });

        // Reset button
        $('#btn-reset-filter').on('click', function () {
            $filterStartDate.val('<?= date('Y-m-01'); ?>');
            $filterEndDate.val('<?= date('Y-m-t'); ?>');
            
            // Turn off listeners temporarily to avoid multiple updates
            $filterKota.off('change');
            $filterPelaksana.off('change');
            
            $filterKota.val('').trigger('change');
            $filterPelaksana.val('').trigger('change');
            
            // Re-bind listeners
            $filterKota.on('change', function () { dt.ajax.reload(); });
            $filterPelaksana.on('change', function () { dt.ajax.reload(); });
            
            dt.ajax.reload();
        });

        if (canUploadVerified) {
            // Handle click on upload verified button (using delegation)
            $table.on('click', '.btn-upload-verified', function () {
                const $btn = $(this);
                const id = $btn.data('id');
                const nomor = $btn.data('nomor');
                const existing = $btn.data('existing');

                const $modal = $('#modal-upload-verified');
                const $form = $('#form-upload-verified');
                
                // Set action dynamically
                $form.attr('action', '<?= site_url("admin/surat/perjalanan-dinas"); ?>/' + id + '/upload-verified');
                
                // Set label nomor
                $('#upload_nomor_label').text(nomor);
                
                // Reset file input label
                $modal.find('.custom-file-input').val('');
                $modal.find('.custom-file-label').html('Pilih file (PDF, JPG, JPEG, PNG)');

                // Handle existing file preview
                const $container = $('#existing_verified_file_container');
                const $link = $('#existing_verified_file_link');
                if (existing && existing !== '') {
                    $link.attr('href', '<?= media_url(""); ?>' + '/' + existing);
                    $container.removeClass('d-none');
                } else {
                    $link.attr('href', '#');
                    $container.addClass('d-none');
                }

                $modal.modal('show');
            });

            // Update file label on file selection
            $(document).on('change', '.custom-file-input', function (e) {
                let fileName = e.target.files[0] ? e.target.files[0].name : 'Pilih file (PDF, JPG, JPEG, PNG)';
                $(this).next('.custom-file-label').html(fileName);
            });
        }

        // Delete button handler
        $table.on('click', '.btn-delete', function () {
            const id = $(this).data('id');
            $('#btnConfirmDelete').attr('href', '<?= site_url("admin/surat/perjalanan-dinas"); ?>/' + id + '/hapus');
            $('#deleteModal').modal('show');
        });

        // Verification button handler (delegated)
        if (canVerify) {
            const $modalVerify = $('#modal-verify-spt');
            const $formVerify = $('#form-verify-spt');
            const $selectVerify = $('#verify_dasar_spt');

            // Initialize select2 if available
            if ($.fn.select2) {
                $selectVerify.select2({
                    theme: 'bootstrap4',
                    placeholder: 'Pilih Dasar SPT',
                    dropdownParent: $modalVerify,
                    closeOnSelect: true
                });
            }

            $table.on('click', '.btn-verify-spt', function () {
                const $btn = $(this);
                const id = $btn.data('id');
                const nomor = $btn.data('nomor') || '';
                const dasarStr = $btn.attr('data-dasar') || '[]';
                const tgl = $btn.data('tgl') || '';

                $formVerify.attr('action', '<?= site_url("admin/surat/perjalanan-dinas"); ?>/' + id + '/verify');
                $('#verify_nomor_surat').val(nomor);
                $('#verify_tanggal_ttd').val(tgl !== '' ? tgl : new Date().toISOString().split('T')[0]);

                let dasarIds = [];
                try {
                    dasarIds = JSON.parse(dasarStr);
                } catch (e) {
                    dasarIds = [];
                }

                if ($.fn.select2) {
                    $selectVerify.val(dasarIds).trigger('change');
                } else {
                    $selectVerify.val(dasarIds);
                }

                $modalVerify.modal('show');
            });
        }

        // Cetak Berdasarkan Periode handler
        $('#btn-cetak-periode').on('click', function () {
            const start = $filterStartDate.val();
            const end = $filterEndDate.val();

            if (!start || !end) {
                alert('Silakan tentukan Tanggal Mulai dan Tanggal Selesai terlebih dahulu.');
                return;
            }

            const url = '<?= site_url("admin/surat/perjalanan-dinas/cetak-periode"); ?>?start_date=' + encodeURIComponent(start) + '&end_date=' + encodeURIComponent(end);
            window.open(url, '_blank');
        });
    })();
</script>
<?= $this->endSection(); ?>
