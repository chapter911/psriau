<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<style>
    .cuti-card-header {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        color: #ffffff;
        border-radius: 10px 10px 0 0;
        padding: 16px 20px;
    }
    .form-section-title {
        font-weight: 700;
        font-size: 0.95rem;
        color: #0A66C2;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 6px;
        margin-top: 15px;
        margin-bottom: 15px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .jenis-cuti-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 10px;
    }
    .jenis-cuti-item {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 10px 14px;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #f8fafc;
    }
    .jenis-cuti-item:hover {
        border-color: #0A66C2;
        background: #f0f7ff;
    }
    .jenis-cuti-item input[type="radio"]:checked + span {
        font-weight: 700;
        color: #0A66C2;
    }
    .jenis-cuti-item.selected {
        border-color: #0A66C2;
        background: #e0f2fe;
        box-shadow: 0 0 0 2px rgba(10, 102, 194, 0.2);
    }
    #modalCuti .modal-dialog {
        max-height: calc(100vh - 40px);
        margin-top: 20px;
        margin-bottom: 20px;
    }
    #modalCuti form.modal-content {
        max-height: calc(100vh - 40px);
        display: flex;
        flex-direction: column;
    }
    #modalCuti .modal-body {
        overflow-y: auto !important;
        max-height: calc(100vh - 180px) !important;
    }

    /* Matrix Preview Styles */
    .table-matrix {
        border-collapse: separate;
        border-spacing: 0;
        width: max-content;
        font-size: 11.5px;
    }
    .table-matrix th, .table-matrix td {
        border-right: 1px solid #e2e8f0;
        border-bottom: 1px solid #e2e8f0;
        padding: 5px 6px;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
    }
    .table-matrix thead tr.row-month th {
        position: sticky;
        top: 0;
        z-index: 20;
        background: #0f172a;
        color: #fff;
        font-weight: 700;
    }
    .table-matrix thead tr.row-day th {
        position: sticky;
        top: 27px;
        z-index: 20;
        font-size: 10px;
        font-weight: 700;
        color: #fff;
    }
    .table-matrix thead tr.row-date th {
        position: sticky;
        top: 47px;
        z-index: 20;
        font-size: 11px;
        font-weight: 700;
        color: #fff;
    }
    .table-matrix thead th.sticky-fixed {
        position: sticky;
        z-index: 30;
        background: #1e293b !important;
        color: #fff !important;
    }
    .table-matrix tbody td.sticky-fixed {
        position: sticky;
        z-index: 10;
        background: #ffffff;
    }
    .table-matrix tbody tr:hover td.sticky-fixed {
        background: #f1f5f9;
    }
    .table-matrix tbody tr:hover td {
        filter: brightness(0.97);
    }
    .table-matrix .col-no { left: 0; min-width: 42px; max-width: 42px; }
    .table-matrix .col-nip { left: 42px; min-width: 175px; max-width: 175px; text-align: left !important; }
    .table-matrix .col-nama { left: 217px; min-width: 220px; max-width: 220px; text-align: left !important; }
    .table-matrix .col-total {
        left: 437px;
        min-width: 75px;
        max-width: 75px;
        box-shadow: 3px 0 6px rgba(0,0,0,0.06);
        border-right: 2px solid #cbd5e1 !important;
        font-weight: 700;
    }
    .table-matrix tfoot tr td {
        position: sticky;
        bottom: 0;
        z-index: 20;
        background: #f1f5f9;
        font-weight: 700;
        border-top: 2px solid #cbd5e1;
    }
    .table-matrix tfoot td.sticky-fixed {
        z-index: 25;
        background: #e2e8f0;
    }
    .cell-cuti {
        background-color: #dc2626 !important;
        color: #ffffff !important;
        font-weight: 700;
        cursor: pointer;
    }
    .cell-leave {
        background-color: #fde047 !important;
        color: #854d0e !important;
        font-weight: 700;
        cursor: pointer;
    }
    .cell-holiday {
        background-color: #fca5a5 !important;
        color: #991b1b !important;
        font-weight: 700;
        cursor: pointer;
    }
    .cell-weekend {
        background-color: #e2e8f0 !important;
        color: #94a3b8 !important;
    }
    .cell-normal {
        background-color: #ffffff;
    }
</style>

<div class="container-fluid">
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 10px; overflow: hidden;">
        <div class="cuti-card-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="font-weight-bold mb-0"><i class="far fa-calendar-alt mr-2 text-warning"></i> Daftar Pengajuan Cuti</h4>
                <small class="text-light" style="opacity: 0.85;">Kelola pengajuan cuti pegawai Satuan Kerja Pelaksanaan Prasarana Strategis Riau.</small>
            </div>
            <div class="d-flex align-items-center" style="gap: 8px;">
                <?php if ($can_export ?? false): ?>
                    <button type="button" class="btn btn-success font-weight-bold shadow-sm" id="btnOpenModalExportExcel" data-toggle="modal" data-target="#modalExportExcelCuti" title="Export Rekapitulasi Matriks Cuti Pegawai (.xlsx)">
                        <i class="fas fa-file-excel mr-1"></i> Rekap Cuti (.xlsx)
                    </button>
                    <a href="<?= site_url('admin/surat/cuti/export-word-kosong'); ?>" class="btn btn-outline-light font-weight-bold shadow-sm" id="btnExportWordKosong" title="Unduh Formulir Permintaan dan Pemberian Cuti Kosong (.docx)">
                        <i class="fas fa-file-word mr-1 text-info"></i> Form Cuti Kosong (.docx)
                    </a>
                <?php endif; ?>
                <?php if (($can_add ?? false) || ($can_edit ?? false)): ?>
                    <button type="button" class="btn btn-warning font-weight-bold text-dark shadow-sm" id="btnOpenModalBuat">
                        <i class="fas fa-plus-circle mr-1"></i> Ajukan Cuti
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tableCuti" class="table table-bordered table-striped table-hover w-100">
                    <thead class="bg-light">
                        <tr>
                            <th width="4%" class="text-center">No</th>
                            <th width="12%">Tgl Pengajuan</th>
                            <th width="20%">Nama / NIP</th>
                            <th width="15%">Jenis Cuti</th>
                            <th width="18%">Periode Cuti</th>
                            <th width="15%">Alasan</th>
                            <th width="10%" class="text-center">Status</th>
                            <th width="12%" class="text-center">Dokumen</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form Pengajuan Cuti -->
<div class="modal fade" id="modalCuti" tabindex="-1" role="dialog" aria-labelledby="modalCutiLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <form id="formCuti" action="<?= site_url('admin/surat/cuti/buat'); ?>" method="post" class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <?= csrf_field(); ?>
            <input type="hidden" id="cuti_id" name="id" value="">
            <input type="hidden" id="pegawai_id" name="pegawai_id" value="<?= esc($current_pegawai['pegawai_id'] ?? ''); ?>">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="modalCutiLabel">
                    <i class="far fa-paper-plane mr-2"></i> Formulir Permintaan dan Pemberian Cuti
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-4">

                    <!-- Section: Tanggal Pengajuan (READONLY / LOCKED) -->
                    <div class="alert alert-secondary d-flex align-items-center mb-3">
                        <i class="fas fa-lock text-muted mr-3 fa-2x"></i>
                        <div>
                            <strong class="d-block text-dark">Tanggal Pengajuan Surat:</strong>
                            <span class="badge badge-info font-weight-bold" style="font-size: 0.95rem;">
                                <?= date('d F Y'); ?>
                            </span>
                            <small class="text-muted d-block mt-1">(Tanggal pengajuan terkunci secara otomatis sesuai tanggal hari ini)</small>
                        </div>
                    </div>
                    <input type="hidden" name="tanggal_pengajuan" value="<?= date('Y-m-d'); ?>">

                    <!-- Section I: DATA PEGAWAI -->
                    <div class="form-section-title">
                        <i class="fas fa-user-id-card mr-1"></i> I. Data Pegawai
                    </div>

                    <?php if (!empty($pegawai_list) && count($pegawai_list) > 1): ?>
                    <div class="form-group">
                        <label class="font-weight-bold">Pilih Pegawai (Opsional untuk Admin)</label>
                        <select class="form-control select2" id="selectPegawai" style="width: 100%;">
                            <option value="">-- Gunakan Data Pegawai Aktif Login --</option>
                            <?php foreach ($pegawai_list as $p): ?>
                                <option value="<?= $p['id']; ?>" 
                                        data-nama="<?= esc($p['nama']); ?>" 
                                        data-nip="<?= esc($p['nip']); ?>" 
                                        data-jabatan="<?= esc($p['jabatan_label'] ?? ''); ?>"
                                        data-masakerja="<?= esc($p['masa_kerja'] ?? ''); ?>">
                                    <?= esc($p['nama']); ?> (NIP: <?= esc($p['nip']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Nama Pegawai <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama" name="nama" value="<?= esc($current_pegawai['nama'] ?? ''); ?>" required placeholder="Masukkan Nama Lengkap">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">N I P <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nip" name="nip" value="<?= esc($current_pegawai['nip'] ?? ''); ?>" required placeholder="Masukkan NIP">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Jabatan</label>
                            <input type="text" class="form-control" id="jabatan" name="jabatan" value="<?= esc($current_pegawai['jabatan'] ?? ''); ?>" placeholder="Masukkan Jabatan">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Masa Kerja</label>
                            <input type="text" class="form-control" id="masa_kerja" name="masa_kerja" value="<?= esc($current_pegawai['masa_kerja'] ?? ''); ?>" placeholder="Contoh: 5 Tahun 2 Bulan">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Unit Kerja</label>
                        <input type="text" class="form-control" id="unit_kerja" name="unit_kerja" value="<?= esc($current_pegawai['unit_kerja'] ?? 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau'); ?>" placeholder="Unit Kerja">
                    </div>

                    <!-- Section II: JENIS CUTI YANG DIAMBIL -->
                    <div class="form-section-title">
                        <i class="fas fa-list-ul mr-1"></i> II. Jenis Cuti Yang Diambil <span class="text-danger">*</span>
                    </div>

                    <div class="jenis-cuti-grid mb-3">
                        <label class="jenis-cuti-item mb-0 selected">
                            <input type="radio" name="jenis_cuti" value="Cuti Tahunan" checked class="mr-2">
                            <span>Cuti Tahunan</span>
                        </label>
                        <label class="jenis-cuti-item mb-0">
                            <input type="radio" name="jenis_cuti" value="Cuti Besar" class="mr-2">
                            <span>Cuti Besar</span>
                        </label>
                        <label class="jenis-cuti-item mb-0">
                            <input type="radio" name="jenis_cuti" value="Cuti Sakit" class="mr-2">
                            <span>Cuti Sakit</span>
                        </label>
                        <label class="jenis-cuti-item mb-0">
                            <input type="radio" name="jenis_cuti" value="Cuti Melahirkan" class="mr-2">
                            <span>Cuti Melahirkan</span>
                        </label>
                        <label class="jenis-cuti-item mb-0">
                            <input type="radio" name="jenis_cuti" value="Cuti Karena Alasan Penting" class="mr-2">
                            <span>Cuti Karena Alasan Penting</span>
                        </label>
                        <label class="jenis-cuti-item mb-0">
                            <input type="radio" name="jenis_cuti" value="Cuti di Luar Tanggungan Negara" class="mr-2">
                            <span>Cuti di Luar Tanggungan Negara</span>
                        </label>
                    </div>

                    <!-- Section III: ALASAN CUTI -->
                    <div class="form-section-title">
                        <i class="fas fa-comment-alt mr-1"></i> III. Alasan Cuti <span class="text-danger">*</span>
                    </div>

                    <div class="form-group">
                        <textarea class="form-control" id="alasan_cuti" name="alasan_cuti" rows="3" required placeholder="Tuliskan alasan pengajuan cuti secara rinci..."></textarea>
                    </div>

                    <!-- Section IV: LAMANYA CUTI -->
                    <div class="form-section-title">
                        <i class="fas fa-business-time mr-1"></i> IV. Lamanya Cuti <span class="text-danger">*</span>
                    </div>

                    <div class="row">
                        <div class="col-md-3 form-group">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="font-weight-bold mb-0">Jumlah <span class="text-danger">*</span></label>
                                <span id="badgeOtomatis" class="badge badge-success px-2 py-1" style="font-size: 0.7rem; border-radius: 4px;" title="Total hari kerja dihitung otomatis tanpa akhir pekan & hari libur">
                                    <i class="fas fa-magic mr-1"></i>Otomatis
                                </span>
                            </div>
                            <div class="input-group">
                                <input type="number" class="form-control font-weight-bold text-primary" id="lama_cuti_jumlah" name="lama_cuti_jumlah" min="1" value="1" required readonly style="background-color: #f8fafc; font-size: 1.05rem;">
                                <div class="input-group-append" id="wrapperBtnRecalc">
                                    <button class="btn btn-outline-secondary" type="button" id="btnRecalculateCuti" title="Hitung Ulang Hari Kerja" style="border-color: #ced4da;">
                                        <i class="fas fa-sync-alt text-muted" id="iconRecalc"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-1" id="hintLamaCuti" style="font-size: 0.78rem;">
                                <i class="fas fa-info-circle mr-1 text-info"></i>Dihitung otomatis (tanpa Sabtu, Minggu & Libur)
                            </small>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="font-weight-bold">Satuan</label>
                            <select class="form-control" id="lama_cuti_satuan" name="lama_cuti_satuan">
                                <option value="Hari" selected>Hari</option>
                                <option value="Bulan">Bulan</option>
                                <option value="Tahun">Tahun</option>
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="font-weight-bold">Mulai Tanggal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="font-weight-bold">s/d Tanggal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" required>
                        </div>
                    </div>

                    <!-- Container Info Kalkulasi Otomatis Hari Kerja Cuti -->
                    <div id="cutiKalkulasiBox" class="mb-3" style="display: none;"></div>

                    <!-- Section V: ALAMAT SELAMA MENJALANKAN CUTI -->
                    <div class="form-section-title">
                        <i class="fas fa-map-marker-alt mr-1"></i> V. Alamat & Kontak Selama Menjalankan Cuti
                    </div>

                    <div class="row">
                        <div class="col-md-8 form-group">
                            <label class="font-weight-bold">Alamat Selama Cuti</label>
                            <textarea class="form-control" id="alamat_selama_cuti" name="alamat_selama_cuti" rows="2" placeholder="Masukkan alamat lengkap lokasi selama cuti"></textarea>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold">No. Telepon / HP</label>
                            <input type="text" class="form-control" id="telepon" name="telepon" placeholder="Contoh: 08123456789">
                        </div>
                    </div>

                    <!-- Section VI: CATATAN CUTI -->
                    <div class="form-section-title">
                        <i class="fas fa-clipboard-list mr-1"></i> VI. Catatan Cuti (Sisa Cuti)
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Jumlah Sisa Cuti (Hari)</label>
                            <input type="number" class="form-control" id="catatan_cuti_n" name="catatan_cuti_n" min="0" value="0" placeholder="Masukkan jumlah sisa cuti">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Keterangan Catatan</label>
                            <input type="text" class="form-control" id="catatan_cuti_keterangan" name="catatan_cuti_keterangan" placeholder="Keterangan tambahan (opsional)">
                        </div>
                    </div>

                    <!-- Section VII: PENANDATANGAN -->
                    <div class="form-section-title">
                        <i class="fas fa-file-signature mr-1"></i> VII. Penandatangan & Atasan
                    </div>

                    <div class="row">
                        <div class="col-md-6 bg-light p-3 rounded mb-2">
                            <strong class="text-primary d-block mb-2"><i class="fas fa-user-tie mr-1"></i> Atasan Langsung</strong>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Nama Atasan</label>
                                <input type="text" class="form-control form-control-sm" id="atasan_nama" name="atasan_nama" value="Muhammad Yudi Prasetya, ST">
                            </div>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">NIP Atasan</label>
                                <input type="text" class="form-control form-control-sm" id="atasan_nip" name="atasan_nip" value="198002142014121002">
                            </div>
                            <div class="form-group mb-0">
                                <label class="small font-weight-bold">Jabatan Atasan</label>
                                <input type="text" class="form-control form-control-sm" id="atasan_jabatan" name="atasan_jabatan" value="Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau">
                            </div>
                        </div>

                        <div class="col-md-6 bg-light p-3 rounded mb-2">
                            <strong class="text-primary d-block mb-2"><i class="fas fa-user-shield mr-1"></i> Pejabat Berwenang</strong>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Nama Pejabat</label>
                                <input type="text" class="form-control form-control-sm" id="pejabat_nama" name="pejabat_nama" value="Ir. Agung Hari Prabowo, M.T">
                            </div>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">NIP Pejabat</label>
                                <input type="text" class="form-control form-control-sm" id="pejabat_nip" name="pejabat_nip" value="196910301998031005">
                            </div>
                            <div class="form-group mb-0">
                                <label class="small font-weight-bold">Jabatan Pejabat</label>
                                <input type="text" class="form-control form-control-sm" id="pejabat_jabatan" name="pejabat_jabatan" value="Plt. Sekretariat Direktorat Jenderal Prasarana Strategis">
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Batal</button>
                    <button type="submit" class="btn btn-primary font-weight-bold"><i class="fas fa-save mr-1"></i> Simpan Pengajuan Cuti</button>
                </div>
            </form>
        </div>
    </div>

<?php if ($can_export ?? false): ?>
<!-- Modal Export Excel Rekap Cuti -->
<div class="modal fade" id="modalExportExcelCuti" tabindex="-1" role="dialog" aria-labelledby="modalExportExcelCutiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 580px;">
        <form action="<?= site_url('admin/surat/cuti/export-excel'); ?>" method="get" target="_blank" class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
                <h5 class="modal-title font-weight-bold" id="modalExportExcelCutiLabel">
                    <i class="fas fa-file-excel mr-2"></i> Export Rekapitulasi Cuti Pegawai (.xlsx)
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-3">
                    Laporan diekspor dalam <strong>format matriks horizontal memanjang ke samping</strong>. Baris atas memuat tanggal kalender, sisi kiri daftar nama pegawai, dengan penanda warna untuk tanggal cuti yang diambil, cuti bersama SKB 3 Menteri, tanggal merah/libur nasional, dan akhir pekan.
                </p>

                <!-- Quick Presets -->
                <div class="mb-3">
                    <label class="font-weight-bold text-dark small text-uppercase mb-2 d-block">Pilihan Cepat Periode:</label>
                    <div class="btn-group btn-group-sm w-100 flex-wrap" role="group">
                        <button type="button" class="btn btn-secondary text-white btn-quick-period" data-period="this_month">Bulan Ini</button>
                        <button type="button" class="btn btn-outline-secondary btn-quick-period" data-period="last_month">Bulan Lalu</button>
                        <button type="button" class="btn btn-outline-secondary btn-quick-period" data-period="next_month">Bulan Depan</button>
                        <button type="button" class="btn btn-outline-secondary btn-quick-period" data-period="this_year">Tahun Berjalan (1 Thn)</button>
                    </div>
                </div>

                <!-- Date Range Inputs -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-dark small">Dari Tanggal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="tanggal_mulai" id="excel_tanggal_mulai" value="<?= date('Y-m-01'); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-dark small">Sampai Tanggal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="tanggal_selesai" id="excel_tanggal_selesai" value="<?= date('Y-m-t'); ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark small">Filter Status Pengajuan</label>
                    <select name="status" class="form-control" id="excel_status">
                        <option value="disetujui" selected>Hanya Pengajuan yang Disetujui (Rekomendasi)</option>
                        <option value="semua">Semua Pengajuan Aktif (Disetujui &amp; Menunggu Persetujuan)</option>
                    </select>
                </div>

                <!-- Filter Pegawai -->
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark small">Daftar Pegawai yang Ditampilkan</label>
                    <select name="filter_pegawai" class="form-control" id="excel_filter_pegawai">
                        <option value="cuti_saja" selected>Hanya Pegawai yang Mengambil Cuti Saja (Rekomendasi)</option>
                        <option value="semua">Semua Pegawai Aktif Satker</option>
                    </select>
                    <small class="text-muted">Pilih "Hanya Pegawai yang Mengambil Cuti Saja" agar tabel Excel hanya memuat baris nama pegawai yang mengajukan cuti pada periode ini.</small>
                </div>

                <!-- Visual Legend Preview Box -->
                <div class="card bg-light border-0 p-3 mt-3">
                    <div class="font-weight-bold text-dark small mb-2"><i class="fas fa-palette mr-1 text-primary"></i> Penandaan Warna pada Excel:</div>
                    <div class="d-flex flex-wrap" style="gap: 8px;">
                        <span class="badge" style="background-color: #DC2626; color: #fff; font-size: 11px; padding: 5px 8px;">
                            <i class="fas fa-square mr-1"></i> Cuti Diambil (Merah)
                        </span>
                        <span class="badge" style="background-color: #FDE047; color: #854D0E; font-size: 11px; padding: 5px 8px;">
                            <i class="fas fa-square mr-1"></i> Cuti Bersama (Kuning)
                        </span>
                        <span class="badge" style="background-color: #FCA5A5; color: #991B1B; font-size: 11px; padding: 5px 8px;">
                            <i class="fas fa-square mr-1"></i> Libur Nasional / Tanggal Merah
                        </span>
                        <span class="badge" style="background-color: #E2E8F0; color: #64748B; font-size: 11px; padding: 5px 8px;">
                            <i class="fas fa-square mr-1"></i> Akhir Pekan (Sabtu/Minggu)
                        </span>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        <i class="fas fa-thumbtack mr-1 text-info"></i> <strong>Freeze Panes:</strong> Kolom nama pegawai &amp; baris tanggal otomatis terkunci saat Anda menggulir tabel ke kanan atau bawah.
                    </small>
                </div>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Batal
                </button>
                <div class="d-flex" style="gap: 8px;">
                    <button type="button" class="btn btn-info font-weight-bold shadow-sm" id="btnTriggerPreview">
                        <i class="fas fa-eye mr-1"></i> Pratinjau (Preview) Matriks
                    </button>
                    <button type="submit" class="btn btn-success font-weight-bold shadow-sm" id="btnSubmitExportExcel">
                        <i class="fas fa-download mr-1"></i> Unduh File Excel (.xlsx)
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Preview Matriks Rekap Cuti -->
<div class="modal fade" id="modalPreviewRekapCuti" tabindex="-1" role="dialog" aria-labelledby="modalPreviewRekapCutiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 96vw; width: 96vw; margin: 15px auto;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; height: calc(100vh - 30px); display: flex; flex-direction: column;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); flex-shrink: 0; padding: 12px 20px;">
                <div>
                    <h5 class="modal-title font-weight-bold mb-1" id="modalPreviewRekapCutiLabel" style="font-size: 1.1rem;">
                        <i class="far fa-calendar-alt text-warning mr-2"></i> Pratinjau Matriks Rekapitulasi Cuti Pegawai
                    </h5>
                    <div id="previewHeaderBadges" class="d-flex flex-wrap align-items-center" style="gap: 6px;">
                        <span class="badge badge-light" id="previewBadgePeriod">-</span>
                        <span class="badge badge-info" id="previewBadgeStatus">-</span>
                        <span class="badge badge-primary" id="previewBadgePegawai">-</span>
                    </div>
                </div>
                <div class="d-flex align-items-center" style="gap: 8px;">
                    <button type="button" class="btn btn-sm btn-outline-light" id="btnBackToFilter">
                        <i class="fas fa-sliders-h mr-1"></i> Ubah Filter
                    </button>
                    <button type="button" class="btn btn-sm btn-success font-weight-bold" id="btnDownloadFromPreview">
                        <i class="fas fa-download mr-1"></i> Unduh Excel (.xlsx)
                    </button>
                    <button type="button" class="close text-white ml-2" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>

            <div class="modal-body p-3" style="flex: 1 1 auto; overflow: hidden; display: flex; flex-direction: column; background: #f8fafc;">
                <!-- Loading State -->
                <div id="previewLoading" class="text-center py-5 my-auto">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="sr-only">Memuat pratinjau...</span>
                    </div>
                    <h6 class="font-weight-bold text-dark mt-3">Menyusun Matriks Kalender Rekapitulasi Cuti...</h6>
                    <small class="text-muted">Menganalisis pengajuan cuti, kalender hari libur SKB 3 menteri &amp; hari kerja efektif.</small>
                </div>

                <!-- Error State -->
                <div id="previewError" class="alert alert-danger d-none my-auto text-center p-4">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2 text-danger d-block"></i>
                    <strong id="previewErrorMsg" class="d-block mb-2 font-weight-bold">Terjadi kesalahan.</strong>
                    <button type="button" class="btn btn-outline-danger btn-sm mt-2" id="btnRetryFilter">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Pengaturan Filter
                    </button>
                </div>

                <!-- Main Content Container -->
                <div id="previewContainer" class="d-none" style="height: 100%; display: flex; flex-direction: column;">
                    <!-- Top Stats & Legend Bar -->
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2" style="gap: 10px; flex-shrink: 0;">
                        <!-- Summary Stats Cards -->
                        <div class="d-flex flex-wrap" style="gap: 6px;">
                            <span class="badge badge-pill badge-primary px-3 py-2 font-weight-bold" style="font-size: 11.5px;">
                                <i class="fas fa-users mr-1"></i> Pegawai: <span id="statPegawaiCount">0</span>
                            </span>
                            <span class="badge badge-pill badge-danger px-3 py-2 font-weight-bold" style="font-size: 11.5px;">
                                <i class="fas fa-calendar-check mr-1"></i> Total Hari Cuti: <span id="statHariCutiCount">0</span> Hari
                            </span>
                            <span class="badge badge-pill badge-warning text-dark px-3 py-2 font-weight-bold" style="font-size: 11.5px;">
                                <i class="fas fa-sun mr-1"></i> Libur &amp; Cuti Bersama: <span id="statLiburCount">0</span> Hari
                            </span>
                            <span class="badge badge-pill badge-secondary px-3 py-2 font-weight-bold" style="font-size: 11.5px;">
                                <i class="fas fa-calendar mr-1"></i> Kalender: <span id="statKalenderCount">0</span> Hari
                            </span>
                        </div>

                        <!-- Legend -->
                        <div class="d-flex flex-wrap align-items-center" style="gap: 6px;">
                            <span class="badge" style="background-color: #DC2626; color: #fff; font-size: 11px; padding: 4px 7px;">
                                <i class="fas fa-square mr-1"></i> Cuti Diambil (C)
                            </span>
                            <span class="badge" style="background-color: #FDE047; color: #854D0E; font-size: 11px; padding: 4px 7px;">
                                <i class="fas fa-square mr-1"></i> Cuti Bersama (CB)
                            </span>
                            <span class="badge" style="background-color: #FCA5A5; color: #991B1B; font-size: 11px; padding: 4px 7px;">
                                <i class="fas fa-square mr-1"></i> Libur Nasional (LN)
                            </span>
                            <span class="badge" style="background-color: #E2E8F0; color: #64748B; font-size: 11px; padding: 4px 7px;">
                                <i class="fas fa-square mr-1"></i> Akhir Pekan (-)
                            </span>
                            <span class="badge bg-white border text-dark" style="font-size: 11px; padding: 4px 7px;">
                                Normal
                            </span>
                        </div>
                    </div>

                    <!-- Scrollable Table Matrix Container with Sticky Freeze Panes -->
                    <div class="matrix-wrapper" style="flex: 1 1 auto; overflow: auto; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px;">
                        <table class="table-matrix" id="tableMatrixPreview">
                            <thead id="matrixThead"></thead>
                            <tbody id="matrixTbody"></tbody>
                            <tfoot id="matrixTfoot"></tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light py-2 px-3 d-flex justify-content-between align-items-center" style="flex-shrink: 0;">
                <div class="small text-muted">
                    <i class="fas fa-info-circle mr-1 text-primary"></i> <strong>Arahkan kursor (hover)</strong> pada sel tanggal cuti/libur untuk melihat rincian alasan dan keterangan lengkap.
                </div>
                <div class="d-flex" style="gap: 8px;">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Tutup
                    </button>
                    <button type="button" class="btn btn-success btn-sm font-weight-bold" id="btnDownloadFromPreviewBottom">
                        <i class="fas fa-download mr-1"></i> Unduh File Excel (.xlsx)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Radio button UI highlight handler
    document.querySelectorAll('.jenis-cuti-item input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', function () {
            document.querySelectorAll('.jenis-cuti-item').forEach(item => item.classList.remove('selected'));
            if (this.checked) {
                this.closest('.jenis-cuti-item').classList.add('selected');
            }
        });
    });

    const computeMasaKerjaFromNip = (nip) => {
        const digits = (nip || '').replace(/\D+/g, '');
        if (digits.length < 14) {
            return '';
        }

        const year = Number(digits.slice(8, 12));
        const month = Number(digits.slice(12, 14));
        if (!year || !month || month < 1 || month > 12 || year < 1950) {
            return '';
        }

        const currentYear = new Date().getFullYear();
        if (year > currentYear) {
            return '';
        }

        const tmtDate = new Date(year, month - 1, 1);
        if (Number.isNaN(tmtDate.getTime())) {
            return '';
        }

        const today = new Date();
        if (tmtDate > today) {
            return '';
        }

        let years = today.getFullYear() - tmtDate.getFullYear();
        let months = today.getMonth() - tmtDate.getMonth();
        if (today.getDate() < tmtDate.getDate()) {
            months -= 1;
        }
        if (months < 0) {
            years -= 1;
            months += 12;
        }

        const parts = [];
        if (years > 0) {
            parts.push(years + ' Tahun');
        }
        if (months > 0) {
            parts.push(months + ' Bulan');
        }

        return parts.length > 0 ? parts.join(' ') : '0 Bulan';
    };

    $('#nip').on('input', function () {
        const computed = computeMasaKerjaFromNip($(this).val());
        if (computed) {
            $('#masa_kerja').val(computed);
        }
    });

    // Select2 integration for employee list selection if present
    if (typeof $.fn.select2 !== 'undefined' && $('#selectPegawai').length > 0) {
        $('#selectPegawai').select2({
            dropdownParent: $('#modalCuti'),
            placeholder: '-- Pilih Pegawai --'
        }).on('change', function () {
            const selectedOpt = $(this).find(':selected');
            if (selectedOpt.val()) {
                $('#nama').val(selectedOpt.data('nama') || '');
                $('#nip').val(selectedOpt.data('nip') || '');
                $('#jabatan').val(selectedOpt.data('jabatan') || '');
                const computed = computeMasaKerjaFromNip(selectedOpt.data('nip') || '');
                $('#masa_kerja').val(computed || selectedOpt.data('masakerja') || '');
                $('#pegawai_id').val(selectedOpt.val());
            }
        });
    }

    // DataTable initialization
    const tableCuti = $('#tableCuti').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "<?= site_url('admin/surat/cuti'); ?>",
            type: "GET"
        },
        columns: [
            {
                data: null,
                className: "text-center",
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { data: "tanggal_pengajuan_formatted" },
            { 
                data: null,
                render: function (data) {
                    return '<strong>' + (data.nama || '-') + '</strong><br><small class="text-muted">NIP: ' + (data.nip || '-') + '</small>';
                }
            },
            { data: "jenis_cuti" },
            { data: "periode_formatted" },
            { data: "alasan_cuti" },
            { data: "status_badge", className: "text-center" },
            { data: "dokumen_html", className: "text-center" },
            { data: "action_html", className: "text-center" }
        ],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
        }
    });

    // -------------------------------------------------------------
    // Kalkulasi Otomatis Total Hari Cuti (Excluding Weekend & Holidays)
    // -------------------------------------------------------------
    let isCalculating = false;

    function formatTanggalIndo(tglStr) {
        if (!tglStr) return '-';
        const parts = tglStr.split('-');
        if (parts.length !== 3) return tglStr;
        const bulanNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        return parts[2] + ' ' + (bulanNames[parseInt(parts[1], 10)] || parts[1]) + ' ' + parts[0];
    }

    function syncSatuanCutiUI() {
        const satuan = $('#lama_cuti_satuan').val();
        if (satuan === 'Hari') {
            $('#lama_cuti_jumlah').prop('readonly', true).css('background-color', '#f8fafc');
            $('#badgeOtomatis').show();
            $('#wrapperBtnRecalc').show();
            $('#hintLamaCuti').html('<i class="fas fa-info-circle mr-1 text-info"></i>Dihitung otomatis (tanpa Sabtu, Minggu & Libur)');
        } else {
            $('#lama_cuti_jumlah').prop('readonly', false).css('background-color', '#ffffff');
            $('#badgeOtomatis').hide();
            $('#wrapperBtnRecalc').hide();
            $('#hintLamaCuti').html('<i class="fas fa-pen mr-1 text-secondary"></i>Input manual jumlah ' + (satuan ? satuan.toLowerCase() : 'hari'));
            $('#cutiKalkulasiBox').hide().empty();
        }
    }

    function kalkulasiHariCuti() {
        const satuan = $('#lama_cuti_satuan').val();
        if (satuan !== 'Hari') {
            syncSatuanCutiUI();
            return;
        }

        const mulai = $('#tanggal_mulai').val();
        const selesai = $('#tanggal_selesai').val();

        if (!mulai || !selesai) {
            $('#cutiKalkulasiBox').hide().empty();
            return;
        }

        if (selesai < mulai) {
            $('#lama_cuti_jumlah').val(0);
            $('#cutiKalkulasiBox').html(`
                <div class="alert alert-danger py-2 px-3 mb-2 small shadow-sm d-flex align-items-center" style="border-radius: 6px;">
                    <i class="fas fa-times-circle fa-lg mr-2 text-danger"></i>
                    <div>
                        <strong>Tanggal Tidak Valid:</strong> Tanggal selesai (${formatTanggalIndo(selesai)}) tidak boleh lebih awal dari tanggal mulai (${formatTanggalIndo(mulai)}).
                    </div>
                </div>
            `).show();
            return;
        }

        if (isCalculating) return;
        isCalculating = true;

        $('#iconRecalc').addClass('fa-spin');
        $('#cutiKalkulasiBox').html(`
            <div class="alert alert-light py-2 px-3 mb-2 small border shadow-sm text-muted d-flex align-items-center" style="border-radius: 6px;">
                <span class="spinner-border spinner-border-sm text-primary mr-2" role="status" aria-hidden="true"></span>
                <span>Menghitung hari kerja aktif & mengecek kalender hari libur nasional...</span>
            </div>
        `).show();

        $.ajax({
            url: "<?= site_url('admin/surat/cuti/hitung-hari'); ?>",
            type: "GET",
            data: { mulai: mulai, selesai: selesai },
            dataType: "json",
            success: function (res) {
                $('#iconRecalc').removeClass('fa-spin');
                isCalculating = false;

                if (!res || !res.success) {
                    $('#cutiKalkulasiBox').html(`
                        <div class="alert alert-warning py-2 px-3 mb-2 small shadow-sm" style="border-radius: 6px;">
                            <i class="fas fa-exclamation-triangle mr-1"></i> ${res.message || 'Gagal menghitung hari kerja.'}
                        </div>
                    `).show();
                    return;
                }

                $('#lama_cuti_jumlah').val(res.total_hari);

                if (res.total_hari <= 0) {
                    let rincianHtml = '';
                    if (res.excluded_list && res.excluded_list.length > 0) {
                        rincianHtml = '<ul class="mb-0 mt-2 pl-3 small">';
                        res.excluded_list.forEach(item => {
                            const badgeColor = item.kategori === 'weekend' ? 'badge-secondary' : (item.kategori === 'leave' ? 'badge-warning text-dark' : 'badge-danger');
                            rincianHtml += `<li><strong>${item.hari}, ${formatTanggalIndo(item.tanggal)}</strong>: <span class="badge ${badgeColor}">${item.keterangan}</span></li>`;
                        });
                        rincianHtml += '</ul>';
                    }

                    $('#cutiKalkulasiBox').html(`
                        <div class="alert alert-warning border-0 shadow-sm py-2 px-3 mb-2" style="background: #fffbeb; border-left: 4px solid #f59e0b !important; border-radius: 6px;">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-exclamation-triangle text-warning fa-lg mr-2 mt-1"></i>
                                <div class="w-100">
                                    <strong class="text-dark">Tidak Ada Hari Kerja Aktif (0 Hari)</strong>
                                    <div class="small text-muted mt-1">
                                        Seluruh tanggal pada rentang yang dipilih (${formatTanggalIndo(mulai)} s/d ${formatTanggalIndo(selesai)}) bertepatan dengan akhir pekan, libur nasional, atau cuti bersama. Silakan periksa kembali rentang tanggal cuti.
                                    </div>
                                    ${rincianHtml}
                                </div>
                            </div>
                        </div>
                    `).show();
                } else {
                    let rincianHtml = '';
                    if (res.excluded_list && res.excluded_list.length > 0) {
                        rincianHtml = `
                            <div class="mt-2 pt-2 border-top" id="boxRincianLibur" style="display: none;">
                                <div class="font-weight-bold small text-dark mb-1"><i class="fas fa-calendar-times mr-1 text-danger"></i> Daftar Hari yang Dikecualikan (Tidak Dihitung Cuti):</div>
                                <div class="d-flex flex-wrap" style="gap: 6px;">
                        `;
                        res.excluded_list.forEach(item => {
                            const badgeClass = item.kategori === 'weekend' ? 'badge-light text-secondary border' : (item.kategori === 'leave' ? 'badge-warning text-dark' : 'badge-danger');
                            rincianHtml += `<span class="badge ${badgeClass} p-1" style="font-size: 0.78rem;" title="${item.keterangan}"><i class="far fa-calendar mr-1"></i>${item.hari}, ${formatTanggalIndo(item.tanggal)}: ${item.keterangan}</span>`;
                        });
                        rincianHtml += `
                                </div>
                            </div>
                        `;
                    }

                    const excludedBtn = res.total_dikecualikan > 0
                        ? `<button type="button" class="btn btn-xs btn-outline-info font-weight-bold ml-2" id="btnToggleRincianLibur" style="font-size: 0.75rem; padding: 2px 8px; border-radius: 4px;">
                            <i class="fas fa-list-ul mr-1"></i> Rincian ${res.total_dikecualikan} Hari Dikecualikan
                           </button>`
                        : '';

                    $('#cutiKalkulasiBox').html(`
                        <div class="alert alert-success border-0 shadow-sm py-2 px-3 mb-2" style="background: #f0fdf4; border-left: 4px solid #10b981 !important; border-radius: 6px;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-success font-weight-bold" style="font-size: 0.95rem;">
                                        <i class="fas fa-check-circle mr-1"></i> Total Cuti: <span class="badge badge-success px-2 py-1" style="font-size: 0.9rem;">${res.total_hari} Hari Kerja Efektif</span>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        Rentang kalender: <strong>${res.total_kalender} hari</strong> | Dikecualikan: <strong>${res.total_dikecualikan} hari</strong> (${res.keterangan_dikecualikan}).
                                    </div>
                                </div>
                                <div>
                                    ${excludedBtn}
                                </div>
                            </div>
                            ${rincianHtml}
                        </div>
                    `).show();

                    $('#btnToggleRincianLibur').off('click').on('click', function(e) {
                        e.preventDefault();
                        $('#boxRincianLibur').slideToggle(200);
                    });
                }
            },
            error: function () {
                $('#iconRecalc').removeClass('fa-spin');
                isCalculating = false;
                $('#cutiKalkulasiBox').html(`
                    <div class="alert alert-warning py-2 px-3 mb-2 small shadow-sm" style="border-radius: 6px;">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Tidak dapat terhubung ke server untuk menghitung hari libur secara live.
                    </div>
                `).show();
            }
        });
    }

    $('#tanggal_mulai, #tanggal_selesai').on('change input', function () {
        kalkulasiHariCuti();
    });

    $('#lama_cuti_satuan').on('change', function () {
        syncSatuanCutiUI();
        kalkulasiHariCuti();
    });

    $('#btnRecalculateCuti').on('click', function () {
        kalkulasiHariCuti();
    });

    $('#formCuti').on('submit', function (e) {
        const satuan = $('#lama_cuti_satuan').val();
        const jumlah = parseInt($('#lama_cuti_jumlah').val(), 10);
        if (satuan === 'Hari' && (isNaN(jumlah) || jumlah <= 0)) {
            e.preventDefault();
            alert('Pengajuan cuti tidak valid: Periode cuti yang dipilih tidak memiliki hari kerja aktif (0 hari). Silakan periksa kembali tanggal mulai dan tanggal selesai.');
            return false;
        }
    });

    // Open Modal Create
    $('#btnOpenModalBuat').on('click', function () {
        $('#formCuti')[0].reset();
        $('#cuti_id').val('');
        $('#formCuti').attr('action', "<?= site_url('admin/surat/cuti/buat'); ?>");
        $('#modalCutiLabel').html('<i class="far fa-paper-plane mr-2"></i> Formulir Permintaan dan Pemberian Cuti');
        $('.jenis-cuti-item').removeClass('selected');
        $('.jenis-cuti-item input[value="Cuti Tahunan"]').prop('checked', true).closest('.jenis-cuti-item').addClass('selected');
        $('#cutiKalkulasiBox').hide().empty();
        syncSatuanCutiUI();
        $('#modalCuti').modal('show');
    });

    // Check URL parameters for direct leave request from Master Tanggal Merah
    const urlParams = new URLSearchParams(window.location.search);
    const paramMulai = urlParams.get('mulai');
    const paramSelesai = urlParams.get('selesai');
    if (paramMulai && paramSelesai) {
        if ($('#btnOpenModalBuat').length > 0) {
            $('#btnOpenModalBuat').trigger('click');
            $('#tanggal_mulai').val(paramMulai);
            $('#tanggal_selesai').val(paramSelesai);
            kalkulasiHariCuti();
        }
    }

    // Edit button click handler
    $('#tableCuti').on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        $.ajax({
            url: "<?= site_url('admin/surat/cuti'); ?>/" + id + "/detail",
            type: "GET",
            dataType: "json",
            success: function (res) {
                if (res.status === 'success' && res.data) {
                    const data = res.data;
                    $('#cuti_id').val(data.id);
                    $('#nama').val(data.nama);
                    $('#nip').val(data.nip);
                    $('#jabatan').val(data.jabatan);
                    $('#masa_kerja').val(data.masa_kerja);
                    $('#unit_kerja').val(data.unit_kerja);
                    $('#alasan_cuti').val(data.alasan_cuti);
                    $('#lama_cuti_jumlah').val(data.lama_cuti_jumlah);
                    $('#lama_cuti_satuan').val(data.lama_cuti_satuan);
                    $('#tanggal_mulai').val(data.tanggal_mulai);
                    $('#tanggal_selesai').val(data.tanggal_selesai);
                    $('#alamat_selama_cuti').val(data.alamat_selama_cuti);
                    $('#telepon').val(data.telepon);
                    $('#catatan_cuti_n').val(data.catatan_cuti_n);
                    $('#catatan_cuti_keterangan').val(data.catatan_cuti_keterangan);
                    $('#atasan_nama').val(data.atasan_nama);
                    $('#atasan_nip').val(data.atasan_nip);
                    $('#atasan_jabatan').val(data.atasan_jabatan);
                    $('#pejabat_nama').val(data.pejabat_nama);
                    $('#pejabat_nip').val(data.pejabat_nip);
                    $('#pejabat_jabatan').val(data.pejabat_jabatan);

                    $('.jenis-cuti-item').removeClass('selected');
                    $('input[name="jenis_cuti"][value="' + data.jenis_cuti + '"]').prop('checked', true).closest('.jenis-cuti-item').addClass('selected');

                    $('#formCuti').attr('action', "<?= site_url('admin/surat/cuti'); ?>/" + id + "/ubah");
                    $('#modalCutiLabel').html('<i class="fas fa-edit mr-2"></i> Edit Pengajuan Cuti');
                    syncSatuanCutiUI();
                    if (data.tanggal_mulai && data.tanggal_selesai && (data.lama_cuti_satuan || 'Hari') === 'Hari') {
                        kalkulasiHariCuti();
                    } else {
                        $('#cutiKalkulasiBox').hide().empty();
                    }
                    $('#modalCuti').modal('show');
                }
            }
        });
    });

    // Delete confirmation handler
    $('#tableCuti').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        if (confirm('Apakah Anda yakin ingin menghapus pengajuan cuti ini?')) {
            const form = $('<form action="<?= site_url('admin/surat/cuti'); ?>/' + id + '/hapus" method="post">' +
                '<?= csrf_field(); ?>' +
                '</form>');
            $('body').append(form);
            form.submit();
        }
    });

    // Approve confirmation handler
    $('#tableCuti').on('click', '.btn-approve', function () {
        const id = $(this).data('id');
        if (confirm('Apakah Anda yakin ingin menyetujui pengajuan cuti ini?')) {
            const form = $('<form action="<?= site_url('admin/surat/cuti'); ?>/' + id + '/setujui" method="post">' +
                '<?= csrf_field(); ?>' +
                '</form>');
            $('body').append(form);
            form.submit();
        }
    });

    // Reject confirmation handler
    $('#tableCuti').on('click', '.btn-reject', function () {
        const id = $(this).data('id');
        if (confirm('Apakah Anda yakin ingin menolak pengajuan cuti ini?')) {
            const form = $('<form action="<?= site_url('admin/surat/cuti'); ?>/' + id + '/tolak" method="post">' +
                '<?= csrf_field(); ?>' +
                '</form>');
            $('body').append(form);
            form.submit();
        }
    });

    // Quick period buttons in Export Excel modal
    $(document).on('click', '.btn-quick-period', function () {
        const period = $(this).data('period');
        const now = new Date();
        let start, end;

        if (period === 'this_month') {
            start = new Date(now.getFullYear(), now.getMonth(), 1);
            end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
        } else if (period === 'last_month') {
            start = new Date(now.getFullYear(), now.getMonth() - 1, 1);
            end = new Date(now.getFullYear(), now.getMonth(), 0);
        } else if (period === 'next_month') {
            start = new Date(now.getFullYear(), now.getMonth() + 1, 1);
            end = new Date(now.getFullYear(), now.getMonth() + 2, 0);
        } else if (period === 'this_year') {
            start = new Date(now.getFullYear(), 0, 1);
            end = new Date(now.getFullYear(), 11, 31);
        }

        const formatYmd = function (d) {
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return y + '-' + m + '-' + day;
        };

        if (start && end) {
            $('#excel_tanggal_mulai').val(formatYmd(start));
            $('#excel_tanggal_selesai').val(formatYmd(end));
            $('.btn-quick-period').removeClass('btn-secondary text-white').addClass('btn-outline-secondary');
            $(this).removeClass('btn-outline-secondary').addClass('btn-secondary text-white');
        }
    });

    // Trigger preview button
    $('#btnTriggerPreview').on('click', function () {
        const mulai = $('#excel_tanggal_mulai').val();
        const selesai = $('#excel_tanggal_selesai').val();
        const status = $('#excel_status').val();
        const filterPegawai = $('#excel_filter_pegawai').val();

        if (!mulai || !selesai) {
            alert('Harap tentukan tanggal mulai dan tanggal selesai.');
            return;
        }

        $('#modalExportExcelCuti').modal('hide');
        $('#modalPreviewRekapCuti').modal('show');

        loadMatrixPreview(mulai, selesai, status, filterPegawai);
    });

    // Back to filter modal button
    $('#btnBackToFilter, #btnRetryFilter').on('click', function () {
        $('#modalPreviewRekapCuti').modal('hide');
        setTimeout(function () {
            $('#modalExportExcelCuti').modal('show');
        }, 300);
    });

    // Download from preview button
    $('#btnDownloadFromPreview, #btnDownloadFromPreviewBottom').on('click', function () {
        const mulai = $('#excel_tanggal_mulai').val();
        const selesai = $('#excel_tanggal_selesai').val();
        const status = $('#excel_status').val();
        const filterPegawai = $('#excel_filter_pegawai').val();

        const url = "<?= site_url('admin/surat/cuti/export-excel'); ?>?" + $.param({
            tanggal_mulai: mulai,
            tanggal_selesai: selesai,
            status: status,
            filter_pegawai: filterPegawai
        });

        window.open(url, '_blank');
    });

    function loadMatrixPreview(mulai, selesai, status, filterPegawai) {
        $('#previewLoading').removeClass('d-none');
        $('#previewError').addClass('d-none');
        $('#previewContainer').addClass('d-none');

        $.ajax({
            url: "<?= site_url('admin/surat/cuti/preview-rekap'); ?>",
            type: "GET",
            data: {
                tanggal_mulai: mulai,
                tanggal_selesai: selesai,
                status: status,
                filter_pegawai: filterPegawai
            },
            dataType: "json",
            success: function (res) {
                $('#previewLoading').addClass('d-none');
                if (!res.success) {
                    $('#previewErrorMsg').text(res.message || 'Terjadi kesalahan saat memuat pratinjau.');
                    $('#previewError').removeClass('d-none');
                    return;
                }

                renderMatrixTable(res);
                $('#previewContainer').removeClass('d-none');
            },
            error: function (xhr) {
                $('#previewLoading').addClass('d-none');
                $('#previewErrorMsg').text('Gagal memuat pratinjau dari server (' + xhr.status + ': ' + xhr.statusText + ').');
                $('#previewError').removeClass('d-none');
            }
        });
    }

    function renderMatrixTable(data) {
        $('#previewBadgePeriod').text('Periode: ' + data.period_label);
        $('#previewBadgeStatus').text(data.status_label);
        $('#previewBadgePegawai').text(data.pegawai_label);

        $('#statPegawaiCount').text(data.stats.total_pegawai || 0);
        $('#statHariCutiCount').text(data.stats.total_hari_cuti || 0);
        $('#statLiburCount').text((data.stats.total_libur_nasional || 0) + (data.stats.total_cuti_bersama || 0));
        $('#statKalenderCount').text(data.stats.total_kalender || 0);

        // 1. Build Thead
        let theadHtml = '';
        theadHtml += '<tr class="row-month">';
        theadHtml += '<th rowspan="3" class="sticky-fixed col-no">No</th>';
        theadHtml += '<th rowspan="3" class="sticky-fixed col-nip">NIP</th>';
        theadHtml += '<th rowspan="3" class="sticky-fixed col-nama">Nama Pegawai</th>';
        theadHtml += '<th rowspan="3" class="sticky-fixed col-total">Total Cuti</th>';

        data.month_groups.forEach(function (mg) {
            theadHtml += '<th colspan="' + mg.col_count + '" style="background:#0f172a; color:#fff; font-size:11px; padding:6px; text-transform:uppercase; letter-spacing:0.5px;">' + mg.label + '</th>';
        });
        theadHtml += '</tr>';

        // Row Day Name
        theadHtml += '<tr class="row-day">';
        data.days.forEach(function (d) {
            let bg = '#1e293b';
            if (d.is_holiday) bg = '#b91c1c';
            else if (d.is_leave) bg = '#d97706';
            else if (d.is_weekend) bg = '#475569';

            theadHtml += '<th style="background:' + bg + ';" title="' + (d.holiday_name ? (d.is_leave ? 'Cuti Bersama: ' : 'Libur: ') + d.holiday_name : d.day_name) + '">' + d.day_name + '</th>';
        });
        theadHtml += '</tr>';

        // Row Date Number
        theadHtml += '<tr class="row-date">';
        data.days.forEach(function (d) {
            let bg = '#1e293b';
            if (d.is_holiday) bg = '#b91c1c';
            else if (d.is_leave) bg = '#d97706';
            else if (d.is_weekend) bg = '#475569';

            theadHtml += '<th style="background:' + bg + ';" title="' + (d.holiday_name ? (d.is_leave ? 'Cuti Bersama: ' : 'Libur: ') + d.holiday_name : d.day_num) + '">' + d.day_num + '</th>';
        });
        theadHtml += '</tr>';
        $('#matrixThead').html(theadHtml);

        // 2. Build Tbody
        let tbodyHtml = '';
        data.employees.forEach(function (emp) {
            tbodyHtml += '<tr>';
            tbodyHtml += '<td class="sticky-fixed col-no font-weight-bold text-muted">' + emp.no + '</td>';
            tbodyHtml += '<td class="sticky-fixed col-nip"><small class="text-muted">' + emp.nip + '</small></td>';
            tbodyHtml += '<td class="sticky-fixed col-nama font-weight-bold text-dark"><span class="d-block text-truncate" style="max-width:210px;" title="' + emp.nama + '">' + emp.nama + '</span></td>';
            tbodyHtml += '<td class="sticky-fixed col-total ' + (emp.total_cuti > 0 ? 'text-danger font-weight-bold' : 'text-muted') + '">' + emp.total_cuti + '</td>';

            emp.cells.forEach(function (c) {
                let cellClass = 'cell-normal';
                if (c.type === 'cuti') cellClass = 'cell-cuti';
                else if (c.type === 'leave') cellClass = 'cell-leave';
                else if (c.type === 'holiday') cellClass = 'cell-holiday';
                else if (c.type === 'weekend') cellClass = 'cell-weekend';

                const safeTitle = c.tooltip ? $('<div>').text(c.tooltip).html().replace(/"/g, '&quot;') : '';
                const tooltipAttr = safeTitle ? ' data-toggle="tooltip" data-placement="top" title="' + safeTitle + '"' : '';
                tbodyHtml += '<td class="' + cellClass + '"' + tooltipAttr + '>' + (c.code || '') + '</td>';
            });
            tbodyHtml += '</tr>';
        });
        $('#matrixTbody').html(tbodyHtml);

        // 3. Build Tfoot
        let tfootHtml = '<tr>';
        tfootHtml += '<td colspan="3" class="sticky-fixed text-right font-weight-bold px-3">TOTAL PEGAWAI CUTI PER HARI</td>';
        tfootHtml += '<td class="sticky-fixed col-total font-weight-bold text-primary">' + data.total_akumulasi_cuti + '</td>';

        data.summary_per_day.forEach(function (cnt) {
            const highlight = cnt > 0 ? 'background-color: #fee2e2; color: #dc2626; font-weight:700;' : '';
            tfootHtml += '<td style="' + highlight + '">' + cnt + '</td>';
        });
        tfootHtml += '</tr>';
        $('#matrixTfoot').html(tfootHtml);

        // Tooltips
        $('#tableMatrixPreview [data-toggle="tooltip"]').tooltip({
            container: 'body',
            html: true
        });
    }
});
</script>
<?= $this->endSection(); ?>
