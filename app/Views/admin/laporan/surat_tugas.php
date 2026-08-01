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
    .btn-table-action {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 105px !important;
        min-width: 105px !important;
        max-width: 105px !important;
        height: 38px !important;
        min-height: 38px !important;
        max-height: 38px !important;
        font-size: 0.78rem !important;
        font-weight: 700 !important;
        line-height: 1.2 !important;
        text-align: center !important;
        padding: 2px 4px !important;
        box-sizing: border-box !important;
        white-space: normal !important;
        word-break: break-word !important;
    }
</style>


<div class="card card-outline card-primary shadow-sm" style="border-radius: 10px; overflow: hidden;">
    <div class="card-header bg-white py-3">
        <h3 class="card-title mb-0 font-weight-bold text-dark" style="font-size: 1.15rem; line-height: 1.8;">
            <i class="fas fa-file-signature text-primary mr-2"></i>Daftar Surat Tugas (SPT)
        </h3>
        <?php if ($can_verify ?? false): ?>
            <div class="card-tools float-right">
                <button type="button" class="btn btn-dark btn-sm px-3 shadow-sm font-weight-bold" data-toggle="modal" data-target="#modal-last-number" style="border-radius: 6px;">
                    <i class="fas fa-list-ol mr-1"></i> Setting Nomor Terakhir SPPD/Kwitansi
                </button>
            </div>
        <?php endif; ?>
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
                        <th style="width:250px;">Tujuan</th>
                        <th style="width:150px;">Kota Tujuan</th>
                        <th style="width:150px;">Periode</th>
                        <th>Nama Pelaksana</th>
                        <th style="width:130px;" class="text-center">Status Laporan</th>
                        <th style="width:110px;" class="text-center">Cetak SPT</th>
                        <th style="width:140px;" class="text-center">Upload SPT (TTD)</th>
                        <th style="width:130px;" class="text-center">Daftar Nominatif</th>
                        <th style="width:100px;" class="text-center">SPPD</th>
                        <th style="width:100px;" class="text-center">Kwitansi</th>
                        <th style="width:80px;" class="text-center">Aksi</th>
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
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
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
                <div class="modal-body py-3">
                    <!-- Info Banner -->
                    <div class="bg-light border rounded p-3 mb-3 shadow-sm" style="font-size: 0.95rem; border-left: 4px solid #17a2b8 !important;">
                        <div class="mb-2"><strong class="text-info"><i class="fas fa-map-marker-alt mr-2"></i> Tujuan:</strong> <span id="info-verify-tujuan" class="text-dark font-weight-bold"></span> <span class="text-muted">(<span id="info-verify-kota"></span>)</span></div>
                        <div class="mb-2"><strong class="text-info"><i class="far fa-calendar-alt mr-2"></i> Periode:</strong> <span id="info-verify-periode" class="text-dark font-weight-bold"></span></div>
                        <div class="mb-0"><strong class="text-info"><i class="fas fa-users mr-2"></i> Pelaksana:</strong> <span id="info-verify-pelaksana" class="text-dark font-weight-bold"></span></div>
                    </div>

                    <ul class="nav nav-tabs font-weight-bold" id="verifyTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="verifikasi-tab" data-toggle="tab" href="#tab-verifikasi" role="tab" aria-controls="tab-verifikasi" aria-selected="true">
                                <i class="fas fa-file-alt mr-1"></i> Data Verifikasi & SPT
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-primary" id="biaya-tab" data-toggle="tab" href="#tab-biaya" role="tab" aria-controls="tab-biaya" aria-selected="false">
                                <i class="fas fa-calculator mr-1"></i> Rincian Biaya (Transport & Penginapan)
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content pt-3" id="verifyTabContent">
                        <!-- TAB 1: DATA VERIFIKASI & SPT -->
                        <div class="tab-pane fade show active" id="tab-verifikasi" role="tabpanel" aria-labelledby="verifikasi-tab">
                            <div class="form-group">
                                <label for="verify_nomor_surat" class="font-weight-bold mb-1">Nomor Surat Tugas <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="verify_nomor_surat" name="nomor_surat_tugas" required placeholder="Contoh: 132/SPT/Gs7/2026">
                            </div>
                            <div class="form-group">
                                <label for="verify_kode_nomor" class="font-weight-bold mb-1">Kode Nomor (SPPD) & Nomor Bukti (Kwitansi)</label>
                                <input type="text" class="form-control" id="verify_kode_nomor" name="kode_nomor" placeholder="Otomatis (atau isi manual jika kustom, contoh: 016)">
                                <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle mr-1 text-info"></i> Kosongkan untuk meng-generate nomor auto-increment secara otomatis.</small>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold mb-1">Dasar SPT (Legal Basis) <span class="text-danger">*</span></label>
                                <div id="dasar-spt-container">
                                    <!-- Dynamic inputs inserted here -->
                                </div>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-success font-weight-bold" id="btn-add-dasar">
                                        <i class="fas fa-plus mr-1"></i> Tambah Dasar SPT
                                    </button>
                                </div>
                                <small class="text-muted mt-1 d-block">Masukkan dasar hukum/dasar tugas SPT secara manual.</small>
                            </div>
                            <div class="form-group">
                                <label for="verify_kop_surat" class="font-weight-bold mb-1">Kop Surat <span class="text-danger">*</span></label>
                                <select class="form-control" id="verify_kop_surat" name="kop_surat_id" required>
                                    <option value="">-- Pilih Kop Surat --</option>
                                    <?php foreach ($kop_surat_list ?? [] as $ks): ?>
                                        <option value="<?= (int) $ks['id']; ?>" <?= (int) ($ks['is_active'] ?? 0) === 1 ? 'selected data-default="1"' : ''; ?>>
                                            <?= esc($ks['title']); ?> <?= (int) ($ks['is_active'] ?? 0) === 1 ? '(Aktif)' : ''; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted mt-1 d-block">Pilih kop surat yang akan digunakan untuk SPT ini.</small>
                            </div>
                            <div class="form-group">
                                <label for="verify_mata_anggaran" class="font-weight-bold mb-1">Mata Anggaran (MAK) <span class="text-danger">*</span></label>
                                <select class="form-control" id="verify_mata_anggaran" name="mata_anggaran_id" required style="width: 100%;">
                                    <option value="">-- PILIH --</option>
                                    <?php foreach ($mata_anggaran_list ?? [] as $ma): ?>
                                        <option value="<?= (int) $ma['id']; ?>">
                                            <?= esc($ma['mata_anggaran']); ?> <?= strtolower((string) ($ma['status'] ?? '')) === 'aktif' ? '(Aktif)' : ''; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted mt-1 d-block">Pilih mata anggaran (MAK) yang digunakan untuk perjalanan dinas ini.</small>
                            </div>
                            <div class="form-group mb-0">
                                <label for="verify_tanggal_ttd" class="font-weight-bold mb-1">Tanggal Tanda Tangan <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="verify_tanggal_ttd" name="tanggal_tanda_tangan" required onfocus="this.showPicker()">
                            </div>
                        </div>

                        <!-- TAB 2: RINCIAN BIAYA (TRANSPORT & PENGINAPAN) -->
                        <div class="tab-pane fade" id="tab-biaya" role="tabpanel" aria-labelledby="biaya-tab">
                            <!-- CARD UANG HARIAN -->
                            <div class="card card-outline card-success mb-3 shadow-sm" style="border-top-color: #28a745;">
                                <div class="card-header py-2 bg-light">
                                    <h6 class="card-title mb-0 font-weight-bold text-success"><i class="fas fa-money-bill-wave mr-1"></i> Uang Harian</h6>
                                </div>
                                <div class="card-body py-3">
                                    <div id="uang-harian-container">
                                        <!-- Dynamic uang harian rows will be added here -->
                                    </div>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-success font-weight-bold" id="btn-add-uang-harian">
                                            <i class="fas fa-plus mr-1"></i> Tambah Uang Harian
                                        </button>
                                    </div>
                                    <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle mr-1"></i> Dalam provinsi: Rp 370.000 atau Rp 222.000. Luar provinsi sesuai SBM. Bisa diinput manual.</small>
                                </div>
                            </div>

                            <!-- CARD BIAYA TRANSPORT -->
                            <div class="card card-outline card-info mb-3 shadow-sm">
                                <div class="card-header py-2 bg-light">
                                    <h6 class="card-title mb-0 font-weight-bold text-info"><i class="fas fa-car mr-1"></i> Biaya Transport / Sewa Kendaraan</h6>
                                </div>
                                <div class="card-body py-3">
                                    <div id="transport-container">
                                        <!-- Dynamic transport rows will be added here -->
                                    </div>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-success font-weight-bold" id="btn-add-transport">
                                            <i class="fas fa-plus mr-1"></i> Tambah Biaya Transport
                                        </button>
                                    </div>
                                    <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle mr-1"></i> Tambahkan baris baru jika terdapat perbedaan tanggal/tarif transport.</small>
                                </div>
                            </div>

                            <!-- CARD BIAYA PENGINAPAN -->
                            <div class="card card-outline card-purple mb-2 shadow-sm" style="border-top-color: #6f42c1;">
                                <div class="card-header py-2 bg-light">
                                    <h6 class="card-title mb-0 font-weight-bold" style="color: #6f42c1;"><i class="fas fa-hotel mr-1"></i> Biaya Penginapan</h6>
                                </div>
                                <div class="card-body py-3">
                                    <div id="penginapan-container">
                                        <!-- Dynamic penginapan rows will be added here -->
                                    </div>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-success font-weight-bold" id="btn-add-penginapan">
                                            <i class="fas fa-plus mr-1"></i> Tambah Biaya Penginapan
                                        </button>
                                    </div>
                                    <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle mr-1"></i> Tambahkan baris baru jika terdapat perbedaan tanggal/tarif hotel. Kosongkan jika menggunakan tarif master.</small>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-top text-right">
                                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                                <button type="button" class="btn btn-primary btn-sm font-weight-bold btn-save-tab" data-tab="tab2"><i class="fas fa-save mr-1"></i> Simpan Rincian Biaya</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- modal footer removed since buttons are inside tabs -->
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Upload SPT TTD (PDF) -->
<div class="modal fade" id="modal-upload-spt-pdf" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title font-weight-bold" id="modalUploadSptTitle">
                    <i class="fas fa-file-upload mr-2"></i>Upload SPT Sudah TTD (PDF)
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <form id="form-upload-spt-pdf" method="post" action="" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="modal-body py-4">
                    <p class="text-secondary mb-3">Upload file SPT yang telah ditandatangani untuk <strong id="upload-spt-nomor">-</strong>:</p>
                    <div class="form-group mb-0">
                        <label for="verified_spt" class="font-weight-bold">File SPT TTD <span class="text-danger">* (Wajib Format PDF)</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="verified_spt" name="verified_spt" accept="application/pdf,.pdf" required>
                            <label class="custom-file-label" for="verified_spt">Pilih file PDF...</label>
                        </div>
                        <small class="text-muted mt-2 d-block"><i class="fas fa-info-circle mr-1"></i> Ukuran maksimal 10MB. File <strong>wajib berkestensi .pdf</strong>.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm font-weight-bold"><i class="fas fa-upload mr-1"></i> Upload PDF</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($can_verify ?? false): ?>
<!-- Modal Setting Nomor Terakhir SPPD & Kwitansi -->
<div class="modal fade" id="modal-last-number" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-list-ol mr-2"></i>Setting Nomor Terakhir SPPD / Kwitansi
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <form method="post" action="<?= site_url('admin/surat/perjalanan-dinas/set-last-number'); ?>">
                <?= csrf_field(); ?>
                <div class="modal-body py-4">
                    <div class="form-group mb-3">
                        <label for="last_number_input" class="font-weight-bold">Nomor Terakhir Terpakai</label>
                        <input type="number" class="form-control form-control-lg text-center font-weight-bold" id="last_number_input" name="last_number" value="<?= (int) ($last_kode_nomor ?? 0); ?>" required min="0" placeholder="Contoh: 15">
                        <small class="text-muted mt-2 d-block">
                            <i class="fas fa-info-circle mr-1 text-info"></i> Masukkan nomor terakhir yang sudah pernah dipakai. Nomor berikutnya yang akan ter-generate otomatis adalah <strong>[Nomor Terakhir + 1]</strong> (misal: jika diisi <code>15</code>, maka nomor berikutnya adalah <code>016</code>).
                        </small>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm font-weight-bold"><i class="fas fa-save mr-1"></i> Simpan Nomor Terakhir</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection(); ?>

    <!-- Datalist for Transportasi -->
    <datalist id="transportasi-master-list">
        <?php foreach ($transportasi_list ?? [] as $t): ?>
            <option value="<?= esc($t['nama_transportasi']) ?>"></option>
        <?php endforeach; ?>
    </datalist>

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

        // Initialize select2 for mata anggaran with tags enabled
        if ($.fn.select2) {
            $('#verify_mata_anggaran').select2({
                tags: true,
                dropdownParent: $('#modal-verify-spt'),
                placeholder: '-- PILIH --'
            });
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
                data: 'pelaksana_names_html',
                render: function (data) {
                    return data ? data : '-';
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
                data: 'upload_spt_ttd_html',
                orderable: false,
                searchable: false,
                className: 'text-center'
            },
            {
                data: 'daftar_nominatif_html',
                orderable: false,
                searchable: false,
                className: 'text-center'
            },
            {
                data: 'sppd_html',
                orderable: false,
                searchable: false,
                className: 'text-center'
            },
            {
                data: 'kwitansi_html',
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

        // Upload SPT TTD (PDF) button handler
        $table.on('click', '.btn-upload-spt-pdf', function (e) {
            e.preventDefault();
            const url = $(this).data('url');
            const nomor = $(this).data('nomor') || '-';

            $('#form-upload-spt-pdf').attr('action', url);
            $('#upload-spt-nomor').text(nomor);
            $('#verified_spt').val('');
            $('#modal-upload-spt-pdf .custom-file-label').text('Pilih file PDF...');
            $('#modal-upload-spt-pdf').modal('show');
        });

        // Update custom file input label on file selection
        $('#verified_spt').on('change', function () {
            const fileName = $(this).val().split('\\').pop() || 'Pilih file PDF...';
            $(this).next('.custom-file-label').text(fileName);
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

            function formatRibuan(val) {
                if (val === undefined || val === null || val === '') return '';
                const raw = String(val).replace(/\D/g, '');
                if (raw === '') return '';
                return new Intl.NumberFormat('id-ID').format(raw);
            }

            $(document).on('input', '.input-currency', function () {
                this.value = formatRibuan(this.value);
            });

            // Uang Harian dynamic rows
            function addUangHarianInputRow(data) {
                data = data || {};
                const uStart = data.tgl_mulai || '';
                const uEnd = data.tgl_selesai || '';
                const uNom = data.nominal !== undefined && data.nominal !== null ? data.nominal : '';
                const uKet = data.keterangan || '';

                const rowHtml = `
                    <div class="uang-harian-row p-2 mb-2 bg-light border rounded">
                        <div class="form-row align-items-center">
                            <div class="col-md-3 mb-1 mb-md-0">
                                <label class="font-weight-bold mb-0 text-muted" style="font-size:0.75rem;">Mulai Tgl</label>
                                <input type="date" class="form-control form-control-sm" name="uang_harian_start_date[]" value="${$('<div/>').text(uStart).html()}" min="${globalTglMulai}" max="${globalTglSelesai}" onfocus="this.showPicker()">
                            </div>
                            <div class="col-md-3 mb-1 mb-md-0">
                                <label class="font-weight-bold mb-0 text-muted" style="font-size:0.75rem;">Selesai Tgl</label>
                                <input type="date" class="form-control form-control-sm" name="uang_harian_end_date[]" value="${$('<div/>').text(uEnd).html()}" min="${globalTglMulai}" max="${globalTglSelesai}" onfocus="this.showPicker()">
                            </div>
                            <div class="col-md-2 mb-1 mb-md-0">
                                <label class="font-weight-bold mb-0 text-muted" style="font-size:0.75rem;">Tarif (Rp)</label>
                                <input type="text" class="form-control form-control-sm input-currency" name="uang_harian_nominal[]" placeholder="Rp" value="${$('<div/>').text(formatRibuan(uNom)).html()}">
                            </div>
                            <div class="col-md-3 mb-1 mb-md-0">
                                <label class="font-weight-bold mb-0 text-muted" style="font-size:0.75rem;">Keterangan</label>
                                <input type="text" class="form-control form-control-sm" name="uang_harian_ket[]" placeholder="Keterangan..." value="${$('<div/>').text(uKet).html()}">
                            </div>
                            <div class="col-md-1 mb-0 text-center pt-3">
                                <button type="button" class="btn btn-xs btn-outline-danger btn-remove-uang-harian" title="Hapus Baris"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                `;
                $('#uang-harian-container').append(rowHtml);
            }

            // Transport dynamic rows
            function addTransportInputRow(data) {
                data = data || {};
                const tStart = data.tgl_mulai || '';
                const tEnd = data.tgl_selesai || '';
                const tNom = data.nominal !== undefined && data.nominal !== null ? data.nominal : '';
                const tJenis = data.jenis || '';
                // Handle legacy data where transport_ket held the type, but if it has a '-', it's likely a route
                let tKet = data.keterangan || '';
                let defaultJenis = tJenis;
                if (!tJenis && tKet && tKet.indexOf('-') === -1 && tKet.toLowerCase().indexOf('pp') === -1) {
                    // It might be a legacy transport type in keterangan
                    defaultJenis = tKet;
                    tKet = '';
                }

                const tIsLumpsum = data.is_lumpsum ? 'checked' : '';
                const tIsLumpsumVal = data.is_lumpsum ? '1' : '0';
                const rowId = 'lumpsum_' + Math.random().toString(36).substr(2, 9);

                const rowHtml = `
                    <div class="transport-row p-2 mb-2 bg-light border rounded">
                        <div class="form-row align-items-center">
                            <div class="col-md-2 mb-1 mb-md-0" style="padding-right: 5px;">
                                <label class="font-weight-bold mb-0 text-muted" style="font-size:0.75rem;">Mulai</label>
                                <input type="date" class="form-control form-control-sm" name="transport_start_date[]" value="${$('<div/>').text(tStart).html()}" min="${globalTglMulai}" max="${globalTglSelesai}" onfocus="this.showPicker()">
                            </div>
                            <div class="col-md-2 mb-1 mb-md-0" style="padding-right: 5px; padding-left: 5px;">
                                <label class="font-weight-bold mb-0 text-muted" style="font-size:0.75rem;">Selesai</label>
                                <input type="date" class="form-control form-control-sm" name="transport_end_date[]" value="${$('<div/>').text(tEnd).html()}" min="${globalTglMulai}" max="${globalTglSelesai}" onfocus="this.showPicker()">
                            </div>
                            <div class="col-md-2 mb-1 mb-md-0" style="padding-right: 5px; padding-left: 5px;">
                                <label class="font-weight-bold mb-0 text-muted" style="font-size:0.75rem;">Jenis Transp.</label>
                                <input type="text" list="transportasi-master-list" class="form-control form-control-sm" name="transport_jenis[]" placeholder="Cth: Pesawat" value="${$('<div/>').text(defaultJenis).html()}">
                            </div>
                            <div class="col-md-2 mb-1 mb-md-0" style="padding-right: 5px; padding-left: 5px;">
                                <label class="font-weight-bold mb-0 text-muted" style="font-size:0.75rem;">Rute</label>
                                <input type="text" class="form-control form-control-sm" name="transport_ket[]" placeholder="Cth: Jkt-Pku (PP)" value="${$('<div/>').text(tKet).html()}">
                            </div>
                            <div class="col-md-2 mb-1 mb-md-0" style="padding-right: 5px; padding-left: 5px;">
                                <label class="font-weight-bold mb-0 text-muted" style="font-size:0.75rem;">Tarif (Rp)</label>
                                <input type="text" class="form-control form-control-sm input-currency" name="transport_nominal[]" placeholder="Rp" value="${$('<div/>').text(formatRibuan(tNom)).html()}">
                            </div>
                            <div class="col-md-1 mb-1 mb-md-0 text-center" style="padding-right: 5px; padding-left: 5px;">
                                <label class="font-weight-bold mb-0 text-muted d-block" style="font-size:0.70rem;" for="${rowId}" title="Ceklis jika tarif ini untuk 1 kali bayar (Pulang-Pergi / Lumpsum) dan BUKAN tarif per hari.">Lumpsum/PP</label>
                                <input type="hidden" name="transport_is_lumpsum[]" value="${tIsLumpsumVal}" class="hidden-lumpsum">
                                <input type="checkbox" id="${rowId}" ${tIsLumpsum} style="transform: scale(1.2); margin-top: 5px; cursor:pointer;" title="Ceklis jika tarif ini untuk 1 kali bayar (Pulang-Pergi / Lumpsum) dan BUKAN tarif per hari." onchange="$(this).siblings('.hidden-lumpsum').val(this.checked ? '1' : '0')">
                            </div>
                            <div class="col-md-1 mb-0 text-center pt-3">
                                <button type="button" class="btn btn-xs btn-outline-danger btn-remove-transport" title="Hapus Baris"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                `;
                $('#transport-container').append(rowHtml);
            }

            // Penginapan dynamic rows
            function addPenginapanInputRow(data) {
                data = data || {};
                const pStart = data.tgl_mulai || '';
                const pEnd = data.tgl_selesai || '';
                const pNom = data.nominal !== undefined && data.nominal !== null ? data.nominal : '';
                const pKet = data.keterangan || '';

                const rowHtml = `
                    <div class="penginapan-row p-2 mb-2 bg-light border rounded">
                        <div class="form-row align-items-center">
                            <div class="col-md-3 mb-1 mb-md-0">
                                <label class="font-weight-bold mb-0 text-muted" style="font-size:0.75rem;">Mulai Tgl</label>
                                <input type="date" class="form-control form-control-sm" name="penginapan_start_date[]" value="${$('<div/>').text(pStart).html()}" min="${globalTglMulai}" max="${globalTglSelesai}" onfocus="this.showPicker()">
                            </div>
                            <div class="col-md-3 mb-1 mb-md-0">
                                <label class="font-weight-bold mb-0 text-muted" style="font-size:0.75rem;">Selesai Tgl</label>
                                <input type="date" class="form-control form-control-sm" name="penginapan_end_date[]" value="${$('<div/>').text(pEnd).html()}" min="${globalTglMulai}" max="${globalTglSelesai}" onfocus="this.showPicker()">
                            </div>
                            <div class="col-md-2 mb-1 mb-md-0">
                                <label class="font-weight-bold mb-0 text-muted" style="font-size:0.75rem;">Tarif (Rp)</label>
                                <input type="text" class="form-control form-control-sm input-currency" name="penginapan_nominal[]" placeholder="Kosongkan..." value="${$('<div/>').text(formatRibuan(pNom)).html()}">
                            </div>
                            <div class="col-md-3 mb-1 mb-md-0">
                                <label class="font-weight-bold mb-0 text-muted" style="font-size:0.75rem;">Keterangan</label>
                                <input type="text" class="form-control form-control-sm" name="penginapan_ket[]" placeholder="Keterangan..." value="${$('<div/>').text(pKet).html()}">
                            </div>
                            <div class="col-md-1 mb-0 text-center pt-3">
                                <button type="button" class="btn btn-xs btn-outline-danger btn-remove-penginapan" title="Hapus Baris"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                `;
                $('#penginapan-container').append(rowHtml);
            }

            // Uang Harian Add & Remove handlers
            $('#btn-add-uang-harian').off('click').on('click', function() {
                addUangHarianInputRow({});
            });
            $('#uang-harian-container').off('click', '.btn-remove-uang-harian').on('click', '.btn-remove-uang-harian', function() {
                const rowsCount = $('#uang-harian-container .uang-harian-row').length;
                if (rowsCount > 1) {
                    $(this).closest('.uang-harian-row').remove();
                } else {
                    $('#uang-harian-container .uang-harian-row input').val('');
                }
            });

            // Transport Add & Remove handlers
            $('#btn-add-transport').off('click').on('click', function() {
                addTransportInputRow({});
            });
            $('#transport-container').off('click', '.btn-remove-transport').on('click', '.btn-remove-transport', function() {
                const rowsCount = $('#transport-container .transport-row').length;
                if (rowsCount > 1) {
                    $(this).closest('.transport-row').remove();
                } else {
                    $('#transport-container .transport-row input').val('');
                }
            });

            // Penginapan Add & Remove handlers
            $('#btn-add-penginapan').off('click').on('click', function() {
                addPenginapanInputRow({});
            });
            $('#penginapan-container').off('click', '.btn-remove-penginapan').on('click', '.btn-remove-penginapan', function() {
                const rowsCount = $('#penginapan-container .penginapan-row').length;
                if (rowsCount > 1) {
                    $(this).closest('.penginapan-row').remove();
                } else {
                    $('#penginapan-container .penginapan-row input').val('');
                }
            });

            $table.on('click', '.btn-verify-spt', function () {
                const $btn = $(this);
                const id = $btn.data('id');
                const nomor = $btn.data('nomor') || '';
                const kodeNomor = $btn.attr('data-kode-nomor') || '';
                const dasarStr = $btn.attr('data-dasar') || '[]';
                const tgl = $btn.data('tgl') || '';
                const kopSuratId = String($btn.attr('data-kop-surat-id') || '0');
                const mataAnggaranId = String($btn.attr('data-mata-anggaran-id') || '0');
                const rincianBiayaStr = $btn.attr('data-rincian-biaya') || '{}';
                
                const kotaTujuan = $btn.attr('data-kota') || '-';
                const tujuan = $btn.attr('data-tujuan') || '-';
                const periode = $btn.attr('data-periode') || '-';
                const pelaksana = $btn.attr('data-pelaksana') || '-';
                const tglMulai = $btn.attr('data-tgl-mulai') || '';
                const tglSelesai = $btn.attr('data-tgl-selesai') || '';
                globalTglMulai = tglMulai;
                globalTglSelesai = tglSelesai;
                const defHarian = $btn.attr('data-def-harian') || '';
                const defPenginapan = $btn.attr('data-def-penginapan') || '';
                
                let rincian = {};
                try {
                    rincian = JSON.parse(rincianBiayaStr);
                } catch (e) {
                    rincian = {};
                }

                $formVerify.attr('action', '<?= site_url("admin/surat/perjalanan-dinas"); ?>/' + id + '/verify');
                
                // Populate Info Banner
                $('#info-verify-tujuan').text(tujuan);
                $('#info-verify-kota').text(kotaTujuan);
                $('#info-verify-periode').text(periode);
                $('#info-verify-pelaksana').text(pelaksana);

                $('#verify_nomor_surat').val(nomor);
                $('#verify_kode_nomor').val(kodeNomor);
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

                if (mataAnggaranId !== '0') {
                    // Check if value exists, if not, it means it's a new tag, append it
                    if ($('#verify_mata_anggaran').find("option[value='" + mataAnggaranId + "']").length) {
                        $('#verify_mata_anggaran').val(mataAnggaranId).trigger('change');
                    } else {
                        const newOption = new Option(mataAnggaranId, mataAnggaranId, true, true);
                        $('#verify_mata_anggaran').append(newOption).trigger('change');
                    }
                } else {
                    $('#verify_mata_anggaran').val('').trigger('change');
                }

                // Populate Dynamic Uang Harian Rows
                const uangHarianContainer = $('#uang-harian-container');
                uangHarianContainer.empty();
                let uangHarianList = rincian.uang_harian || [];
                if (!Array.isArray(uangHarianList) && rincian.uang_harian_start_date) {
                    uangHarianList = [{
                        tgl_mulai: rincian.uang_harian_start_date,
                        tgl_selesai: rincian.uang_harian_end_date,
                        nominal: rincian.uang_harian_nominal,
                        keterangan: ''
                    }];
                }
                if (uangHarianList.length === 0) {
                    addUangHarianInputRow({ tgl_mulai: tglMulai, tgl_selesai: tglSelesai, nominal: defHarian });
                } else {
                    uangHarianList.forEach(function(uItem) {
                        addUangHarianInputRow(uItem);
                    });
                }

                // Populate Dynamic Transport Rows
                const transportContainer = $('#transport-container');
                transportContainer.empty();
                let transportList = rincian.transport || [];
                if (!Array.isArray(transportList) && rincian.transport_start_date) {
                    transportList = [{
                        tgl_mulai: rincian.transport_start_date,
                        tgl_selesai: rincian.transport_end_date,
                        nominal: rincian.transport_nominal,
                        keterangan: ''
                    }];
                }
                if (transportList.length === 0) {
                    addTransportInputRow({ tgl_mulai: tglMulai, tgl_selesai: tglSelesai });
                } else {
                    transportList.forEach(function(tItem) {
                        addTransportInputRow(tItem);
                    });
                }

                // Populate Dynamic Penginapan Rows
                const penginapanContainer = $('#penginapan-container');
                penginapanContainer.empty();
                let penginapanList = rincian.penginapan || [];
                if (!Array.isArray(penginapanList) && rincian.penginapan_start_date) {
                    penginapanList = [{
                        tgl_mulai: rincian.penginapan_start_date,
                        tgl_selesai: rincian.penginapan_end_date,
                        nominal: rincian.penginapan_nominal,
                        keterangan: ''
                    }];
                }
                if (penginapanList.length === 0) {
                    addPenginapanInputRow({ tgl_mulai: tglMulai, tgl_selesai: tglSelesai, nominal: defPenginapan });
                } else {
                    penginapanList.forEach(function(pItem) {
                        addPenginapanInputRow(pItem);
                    });
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

                // Reset active tab to Tab 1
                $('#verifikasi-tab').tab('show');

                $modalVerify.modal('show');
            });
        }

        
        // Handle AJAX submit per tab
        $('.btn-save-tab').on('click', function(e) {
            e.preventDefault();
            const tabAction = $(this).data('tab');
            const $form = $('#form-verify-spt');
            const url = $form.attr('action');
            
            // Create a FormData object
            const formData = new FormData($form[0]);
            formData.append('tab_action', tabAction);

            const $btn = $(this);
            const originalText = $btn.html();
            $btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...').prop('disabled', true);

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $btn.html(originalText).prop('disabled', false);
                    if (response.status === 'success') {
                        Swal.fire('Berhasil', response.message, 'success');
                        dt.ajax.reload(null, false);
                    } else {
                        Swal.fire('Gagal', response.message || 'Terjadi kesalahan', 'error');
                    }
                },
                error: function() {
                    $btn.html(originalText).prop('disabled', false);
                    Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan data', 'error');
                }
            });
        });

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
