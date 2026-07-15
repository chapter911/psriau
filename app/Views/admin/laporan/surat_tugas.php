<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
    $canEdit = (bool) ($can_edit ?? false);
    $canVerify = (bool) ($can_verify ?? false);
?>
<style>
    .text-tujuan {
        white-space: normal;
        word-wrap: break-word;
        max-width: 280px;
    }
</style>

<!-- Flash Messages -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle mr-1"></i> <?= session()->getFlashdata('success'); ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle mr-1"></i> <?= session()->getFlashdata('error'); ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>

<div class="card card-outline card-primary">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title mb-0 font-weight-bold">Daftar Surat Tugas (SPT)</h3>
    </div>
    <div class="card-body">
        
        <!-- Filter Card -->
        <div class="card card-outline card-secondary mb-4 shadow-sm">
            <div class="card-header py-2 bg-light">
                <h3 class="card-title mb-0 font-weight-bold text-secondary" style="font-size:0.95rem;"><i class="fas fa-filter mr-1"></i> Filter Data</h3>
            </div>
            <div class="card-body py-3">
                <div class="form-row">
                    <div class="form-group col-md-3 mb-2 mb-md-0">
                        <label for="filter_start_date" class="font-weight-bold mb-1" style="font-size:0.85rem;">Tanggal Mulai</label>
                        <input type="date" class="form-control form-control-sm" id="filter_start_date" value="<?= date('Y-m-01'); ?>">
                    </div>
                    <div class="form-group col-md-3 mb-2 mb-md-0">
                        <label for="filter_end_date" class="font-weight-bold mb-1" style="font-size:0.85rem;">Tanggal Selesai</label>
                        <input type="date" class="form-control form-control-sm" id="filter_end_date" value="<?= date('Y-m-t'); ?>">
                    </div>
                    <div class="form-group col-md-3 mb-2 mb-md-0">
                        <label for="filter_kota" class="font-weight-bold mb-1" style="font-size:0.85rem;">Kota Tujuan</label>
                        <select class="form-control form-control-sm select2-filter" id="filter_kota" data-placeholder="Semua Kota/Kabupaten" style="width: 100%;">
                            <option value=""></option>
                            <?php foreach ($kabupaten_options ?? [] as $kota): ?>
                                <option value="<?= esc($kota); ?>"><?= esc($kota); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3 mb-2 mb-md-0">
                        <label for="filter_pelaksana" class="font-weight-bold mb-1" style="font-size:0.85rem;">Pelaksana</label>
                        <select class="form-control form-control-sm select2-filter" id="filter_pelaksana" data-placeholder="Semua Pelaksana" style="width: 100%;">
                            <option value=""></option>
                            <?php foreach ($pegawai_options ?? [] as $peg): ?>
                                <option value="<?= (int) ($peg['id'] ?? 0); ?>"><?= esc($peg['nama'] ?? ''); ?><?= !empty($peg['nip']) ? ' - NIP ' . esc($peg['nip']) : ''; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3 pt-2" style="border-top:1px solid #f0f2f5;">
                    <small class="text-muted"><i class="fas fa-info-circle mr-1"></i> Data diperbarui secara otomatis saat filter diubah.</small>
                    <div class="d-flex align-items-center" style="gap:10px;">
                        <button type="button" class="btn btn-danger btn-sm" id="btn-cetak-periode"><i class="fas fa-file-pdf mr-1"></i> Cetak Surat Tugas (Periode)</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-reset-filter">Reset Filter</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped w-100" id="tableSuratTugas">
                <thead>
                    <tr>
                        <th style="width:60px;" class="text-center">No</th>
                        <th style="width:280px;">Tujuan</th>
                        <th style="width:180px;">Kota Tujuan</th>
                        <th style="width:180px;">Periode</th>
                        <th>Nama Pelaksana</th>
                        <th style="width:150px;" class="text-center">Status Verifikasi</th>
                        <th style="width:130px;" class="text-center">File SPT</th>
                        <th style="width:110px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
</div>

<?php if ($can_verify ?? false): ?>
<!-- Modal Verifikasi SPT -->
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
                        <label class="font-weight-bold mb-1">Dasar SPT (Legal Basis) <span class="text-danger">*</span></label>
                        <div id="dasar-spt-container">
                            <!-- Dynamic inputs will be inserted here -->
                        </div>
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-outline-success font-weight-bold" id="btn-add-dasar">
                                <i class="fas fa-plus mr-1"></i> Tambah Dasar SPT
                            </button>
                        </div>
                        <small class="text-muted mt-1 d-block">Masukkan dasar hukum/dasar tugas SPT secara manual. Gunakan tombol + untuk menambah.</small>
                    </div>
                    <div class="form-group">
                        <label for="verify_kop_surat" class="font-weight-bold mb-1">Kop Surat <span class="text-danger">*</span></label>
                        <select class="form-control" id="verify_kop_surat" name="kop_surat_id" required>
                            <option value="">-- Pilih Kop Surat --</option>
                            <?php foreach ($kop_surat_list ?? [] as $ks): ?>
                                <option value="<?= (int) $ks['id']; ?>" <?= (int) ($ks['is_active'] ?? 0) === 1 ? 'data-default="1"' : ''; ?>>
                                    <?= esc($ks['title']); ?> <?= (int) ($ks['is_active'] ?? 0) === 1 ? '(Aktif)' : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted mt-1 d-block">Pilih kop surat yang akan digunakan untuk SPT ini.</small>
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
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
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

        const $table = $('#tableSuratTugas');
        if (! $table.length || $.fn.dataTable.isDataTable($table)) {
            return;
        }

        const canEdit = <?= json_encode($canEdit, JSON_UNESCAPED_UNICODE); ?>;
        const canVerify = <?= json_encode($canVerify, JSON_UNESCAPED_UNICODE); ?>;
        const dataUrl = <?= json_encode(site_url('admin/surat/perjalanan-dinas/surat-tugas'), JSON_UNESCAPED_UNICODE); ?>;

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
                render: function (data, type, row) {
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
                data: 'status_verifikasi_html',
                orderable: false,
                searchable: false,
                className: 'text-center'
            },
            {
                data: 'file_spt_html',
                orderable: false,
                searchable: false,
                className: 'text-center'
            },
            {
                data: 'aksi_spt_html',
                orderable: false,
                searchable: false,
                className: 'text-center'
            }
        ];

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

            function addDasarInputRow(value = '') {
                const container = $('#dasar-spt-container');
                const rowHtml = `
                    <div class="input-group mb-2 dasar-spt-row">
                        <input type="text" class="form-control" name="dasar_spt[]" required value="${$('<div>').text(value).html()}" placeholder="Contoh: Undang-Undang Nomor 17 Tahun 2003...">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-danger btn-remove-dasar" title="Hapus"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                `;
                container.append(rowHtml);
            }

            // Add Dasar row
            $('#btn-add-dasar').off('click').on('click', function() {
                addDasarInputRow('');
            });

            // Remove Dasar row
            $('#dasar-spt-container').off('click', '.btn-remove-dasar').on('click', '.btn-remove-dasar', function() {
                const rowsCount = $('#dasar-spt-container .dasar-spt-row').length;
                if (rowsCount > 1) {
                    $(this).closest('.dasar-spt-row').remove();
                } else {
                    $('#dasar-spt-container .dasar-spt-row input').val('');
                }
            });

            $table.on('click', '.btn-verify-spt', function () {
                const $btn = $(this);
                const id = $btn.data('id');
                const nomor = $btn.data('nomor') || '';
                const dasarStr = $btn.attr('data-dasar') || '[]';
                const tgl = $btn.data('tgl') || '';
                const kopSuratId = String($btn.attr('data-kop-surat-id') || '0');

                $formVerify.attr('action', '<?= site_url("admin/surat/perjalanan-dinas"); ?>/' + id + '/verify');
                $('#verify_nomor_surat').val(nomor);
                $('#verify_tanggal_ttd').val(tgl !== '' ? tgl : new Date().toISOString().split('T')[0]);

                if (kopSuratId !== '0') {
                    $('#verify_kop_surat').val(kopSuratId);
                } else {
                    const defaultOpt = $('#verify_kop_surat option[data-default="1"]');
                    if (defaultOpt.length) {
                        $('#verify_kop_surat').val(defaultOpt.val());
                    } else {
                        $('#verify_kop_surat').val('');
                    }
                }

                let dasarTexts = [];
                try {
                    dasarTexts = JSON.parse(dasarStr);
                } catch (e) {
                    dasarTexts = [];
                }

                const container = $('#dasar-spt-container');
                container.empty();

                if (dasarTexts.length === 0) {
                    addDasarInputRow('');
                } else {
                    dasarTexts.forEach(function(text) {
                        addDasarInputRow(text);
                    });
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
