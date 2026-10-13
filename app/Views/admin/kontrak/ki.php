<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h2 class="card-title mb-0">Daftar Kontrak KI - <?= esc((string) ($paket['nama_paket'] ?? '-')); ?></h2>
        <div class="ml-auto d-flex align-items-center flex-wrap" style="gap: 8px;">
            <div class="btn-group btn-group-sm" role="group" aria-label="Tampilan kolom">
                <button type="button" class="btn btn-outline-secondary active" id="btnKolomUtama">Tampilan Utama</button>
                <button type="button" class="btn btn-outline-secondary" id="btnKolomSemua">Show All</button>
            </div>

            <?php if (($can_export ?? true) === true): ?>
                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalExportBatchKi"><i class="fas fa-file-archive mr-1"></i> Export Batch</button>
            <?php endif; ?>
            <?php if (($can_import ?? $can_edit ?? false) === true): ?>
                <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalImportKi"><i class="fas fa-file-excel mr-1"></i> Import Excel</button>
            <?php endif; ?>
            <?php if (($can_add ?? $can_edit ?? false) === true): ?>
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalTambahKi"><i class="fas fa-plus mr-1"></i> Tambah Data</button>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">

        <table class="table table-bordered table-striped w-100 nowrap" id="tableKontrakKi">
            <thead>
                <tr style="white-space: nowrap;">
                    <th class="text-center">#</th>
                    <th class="text-center">ACTION</th>
                    <th class="text-center">NOMOR KONTRAK</th>
                    <th class="text-center">TANGGAL KONTRAK</th>
                    <th class="text-center">KODE PERSONIL</th>
                    <th class="text-center">NAMA</th>
                    <th class="text-center">ALAMAT</th>
                    <th class="text-center">NIK</th>
                    <th class="text-center">NPWP</th>
                    <th class="text-center">JABATAN</th>
                    <th class="text-center">DURASI PELAKSANAAN</th>
                    <th class="text-center">NOMOR DIPA</th>
                    <th class="text-center">TANGGAL DIPA</th>
                    <th class="text-center">MATA ANGGARAN</th>
                    <th class="text-center">NOMOR SURAT UNDANGAN PENGADAAN</th>
                    <th class="text-center">TANGGAL SURAT UNDANGAN PENGADAAN</th>
                    <th class="text-center">NOMOR SURAT BERITA ACARA PENGADAAN</th>
                    <th class="text-center">TANGGAL SURAT BERITA ACARA PENGADAAN</th>
                    <th class="text-center">NOMOR SURAT PENAWARAN</th>
                    <th class="text-center">TANGGAL SURAT PENAWARAN</th>
                    <th class="text-center">NOMOR UNDANGAN</th>
                    <th class="text-center">TOTAL PENAWARAN</th>
                    <th class="text-center">TANGGAL AWAL</th>
                    <th class="text-center">TANGGAL AKHIR</th>
                    <th class="text-center">TAHUN ANGGARAN</th>
                    <th class="text-center">NO SPPBJ</th>
                    <th class="text-center">TANGGAL SPPBJ</th>
                    <th class="text-center">PEJABAT PPK</th>
                    <th class="text-center">NIP PEJABAT PPK</th>
                    <th class="text-center">KEDUDUKAN PEJABAT PPK</th>
                    <th class="text-center">NOMOR SURAT KEPUTUSAN MENTERI</th>
                    <th class="text-center">TANGGAL SURAT KEPUTUSAN MENTERI</th>
                    <th class="text-center">NOMOR PERUBAHAN KEPUTUSAN MENTERI</th>
                    <th class="text-center">BANK NOMOR REKENING</th>
                    <th class="text-center">BANK NAMA</th>
                    <th class="text-center">BANK ATAS NAMA</th>
                    <th class="text-center">BANK PEMBAYARAN</th>
                    <th class="text-center">KATEGORI</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach (($data ?? []) as $d): ?>
                    <tr>
                        <td class="text-center"><?= esc((string) $i++); ?></td>
                        <td class="text-center" style="white-space: nowrap;">
                            <div class="d-inline-flex align-items-center" style="gap: 5px;">
                                <?php if (($can_edit ?? false) === true): ?>
                                    <button type="button" class="btn btn-warning btn-sm edit-ki-btn" 
                                            title="Edit Data KI"
                                            data-id="<?= esc((string) ($d['id'] ?? '')); ?>"
                                            data-nomor-kontrak="<?= esc((string) ($d['nomor_kontrak'] ?? '')); ?>"
                                            data-tanggal-kontrak="<?= esc((string) ($d['tanggal_kontrak'] ?? '')); ?>"
                                            data-kode-personil="<?= esc((string) ($d['kode_personil'] ?? '')); ?>"
                                            data-nama="<?= esc((string) ($d['nama'] ?? '')); ?>"
                                            data-alamat="<?= esc((string) ($d['alamat'] ?? '')); ?>"
                                            data-nik="<?= esc((string) ($d['nik'] ?? '')); ?>"
                                            data-npwp="<?= esc((string) ($d['npwp'] ?? '')); ?>"
                                            data-jabatan="<?= esc((string) ($d['jabatan'] ?? '')); ?>"
                                            data-durasi-pelaksanaan="<?= esc((string) ($d['durasi_pelaksanaan'] ?? '')); ?>"
                                            data-tanggal-awal="<?= esc((string) ($d['tanggal_awal'] ?? '')); ?>"
                                            data-tanggal-akhir="<?= esc((string) ($d['tanggal_akhir'] ?? '')); ?>"
                                            data-nama-pekerjaan="<?= esc((string) ($d['nama_pekerjaan'] ?? '')); ?>"
                                            data-nomor-dipa="<?= esc((string) ($d['nomor_dipa'] ?? '')); ?>"
                                            data-tanggal-dipa="<?= esc((string) ($d['tanggal_dipa'] ?? '')); ?>"
                                            data-mata-anggaran="<?= esc((string) ($d['mata_anggaran'] ?? '')); ?>"
                                            data-nomor-surat-undangan-pengadaan="<?= esc((string) ($d['nomor_surat_undangan_pengadaan'] ?? '')); ?>"
                                            data-tanggal-surat-undangan-pengadaan="<?= esc((string) ($d['tanggal_surat_undangan_pengadaan'] ?? '')); ?>"
                                            data-nomor-surat-berita-acara-pengadaan="<?= esc((string) ($d['nomor_surat_berita_acara_pengadaan'] ?? '')); ?>"
                                            data-tanggal-surat-berita-acara-pengadaan="<?= esc((string) ($d['tanggal_surat_berita_acara_pengadaan'] ?? '')); ?>"
                                            data-nomor-surat-penawaran="<?= esc((string) ($d['nomor_surat_penawaran'] ?? '')); ?>"
                                            data-tanggal-surat-penawaran="<?= esc((string) ($d['tanggal_surat_penawaran'] ?? '')); ?>"
                                            data-nomor-undangan="<?= esc((string) ($d['nomor_undangan'] ?? '')); ?>"
                                            data-total-penawaran="<?= esc((string) ($d['total_penawaran'] ?? '')); ?>"
                                            data-tahun-anggaran="<?= esc((string) ($d['tahun_anggaran'] ?? '')); ?>"
                                            data-no-sppbj="<?= esc((string) ($d['no_sppbj'] ?? '')); ?>"
                                            data-tanggal-sppbj="<?= esc((string) ($d['tanggal_sppbj'] ?? '')); ?>"
                                            data-pejabat-ppk="<?= esc((string) ($d['pejabat_ppk'] ?? '')); ?>"
                                            data-nip-pejabat-ppk="<?= esc((string) ($d['nip_pejabat_ppk'] ?? '')); ?>"
                                            data-kedudukan-pejabat-ppk="<?= esc((string) ($d['kedudukan_pejabat_ppk'] ?? '')); ?>"
                                            data-nomor-surat-keputusan-menteri="<?= esc((string) ($d['nomor_surat_keputusan_menteri'] ?? '')); ?>"
                                            data-tanggal-surat-keputusan-menteri="<?= esc((string) ($d['tanggal_surat_keputusan_menteri'] ?? '')); ?>"
                                            data-nomor-perubahan-keputusan-menteri="<?= esc((string) ($d['nomor_perubahan_keputusan_menteri'] ?? '')); ?>"
                                            data-bank-nomor-rekening="<?= esc((string) ($d['bank_nomor_rekening'] ?? '')); ?>"
                                            data-bank-nama="<?= esc((string) ($d['bank_nama'] ?? '')); ?>"
                                            data-bank-atas-nama="<?= esc((string) ($d['bank_atas_nama'] ?? '')); ?>"
                                            data-bank-pembayaran="<?= esc((string) ($d['bank_pembayaran'] ?? '')); ?>"
                                            data-kategori="<?= esc((string) ($d['kategori'] ?? '')); ?>"
                                            data-nomor-telefon-ki="<?= esc((string) ($d['nomor_telefon_ki'] ?? '')); ?>"
                                            data-email-ki="<?= esc((string) ($d['email_ki'] ?? '')); ?>"
                                            data-nominal-kontrak="<?= esc((string) ($d['nominal_kontrak'] ?? '')); ?>"
                                            data-nominal-hps="<?= esc((string) ($d['nominal_hps'] ?? '')); ?>"
                                            data-nomor-spmk="<?= esc((string) ($d['nomor_spmk'] ?? '')); ?>"
                                            data-tanggal-spmk="<?= esc((string) ($d['tanggal_spmk'] ?? '')); ?>"
                                            data-nomor-baphp="<?= esc((string) ($d['nomor_baphp'] ?? '')); ?>"
                                            data-nomor-surat-permohonan="<?= esc((string) ($d['nomor_surat_permohonan'] ?? '')); ?>"
                                            data-tanggal-surat-permohonan="<?= esc((string) ($d['tanggal_surat_permohonan'] ?? '')); ?>"
                                            data-nama-pekerjaan="<?= esc((string) ($d['nama_pekerjaan'] ?? '')); ?>"
                                            data-jenis-pembayaran="<?= esc((string) ($d['jenis_pembayaran'] ?? '')); ?>"
                                            data-nomor-bast="<?= esc((string) ($d['nomor_bast'] ?? '')); ?>"
                                            data-pendidikan="<?= esc((string) ($d['pendidikan'] ?? '')); ?>"
                                            data-sertifikat="<?= esc((string) ($d['sertifikat'] ?? '')); ?>"
>
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </button>
                                <?php endif; ?>

                                <?php if (($can_export ?? true) === true): ?>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-success btn-sm" data-toggle="dropdown"><i class="fas fa-file-export mr-1"></i> Export</button>
                                        <button type="button" class="btn btn-success btn-sm dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                            <span class="sr-only">Toggle Dropdown</span>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right" role="menu" style="max-height: 440px; overflow-y: auto; min-width: 270px; box-shadow: 0 4px 16px rgba(0,0,0,0.15);">
                                            <!-- PDF SECTION -->
                                            <div class="px-3 py-1 font-weight-bold text-danger small bg-light d-flex align-items-center justify-content-between">
                                                <span><i class="fas fa-file-pdf mr-1"></i> EXPORT PDF</span>
                                                <span class="badge badge-danger">PDF</span>
                                            </div>
                                            <a href="<?= site_url('admin/kontrak/export/penawaran/' . (int) ($d['id'] ?? 0)); ?>" class="dropdown-item py-1" target="_blank">PENAWARAN</a>
                                            <a href="<?= site_url('admin/kontrak/export/pakta-integritas/' . (int) ($d['id'] ?? 0)); ?>" class="dropdown-item py-1" target="_blank">PAKTA INTEGRITAS</a>
                                            <a href="<?= site_url('admin/kontrak/export/kualifikasi/' . (int) ($d['id'] ?? 0)); ?>" class="dropdown-item py-1" target="_blank">KUALIFIKASI</a>
                                            <a href="<?= site_url('admin/kontrak/export/kesediaan/' . (int) ($d['id'] ?? 0)); ?>" class="dropdown-item py-1" target="_blank">KESEDIAAN</a>
                                            <a href="<?= site_url('admin/kontrak/export/spbbj/' . (int) ($d['id'] ?? 0)); ?>" class="dropdown-item py-1" target="_blank">SPBBJ</a>
                                            <a href="<?= site_url('admin/kontrak/export/baphp/' . (int) ($d['id'] ?? 0)); ?>" class="dropdown-item py-1" target="_blank">BAPHP</a>
                                            <a href="<?= site_url('admin/kontrak/export/bast/' . (int) ($d['id'] ?? 0)); ?>" class="dropdown-item py-1" target="_blank">BAST</a>
                                            <a href="<?= site_url('admin/kontrak/export/spmk/' . (int) ($d['id'] ?? 0)); ?>" class="dropdown-item py-1" target="_blank">SPMK</a>
                                            <a href="<?= site_url('admin/kontrak/export/spk/' . (int) ($d['id'] ?? 0)); ?>" class="dropdown-item py-1" target="_blank">SPK</a>

                                            <div class="dropdown-divider my-1"></div>

                                            <!-- WORD SECTION -->
                                            <div class="px-3 py-1 font-weight-bold text-primary small bg-light d-flex align-items-center justify-content-between">
                                                <span><i class="fas fa-file-word mr-1"></i> EXPORT WORD</span>
                                                <span class="badge badge-primary">DOC</span>
                                            </div>
                                            <a href="<?= site_url('admin/kontrak/export/word/penawaran/' . (int) ($d['id'] ?? 0)); ?>?format=word" class="dropdown-item py-1" target="_blank">PENAWARAN <small class="text-muted">(.doc)</small></a>
                                            <a href="<?= site_url('admin/kontrak/export/word/pakta-integritas/' . (int) ($d['id'] ?? 0)); ?>?format=word" class="dropdown-item py-1" target="_blank">PAKTA INTEGRITAS <small class="text-muted">(.doc)</small></a>
                                            <a href="<?= site_url('admin/kontrak/export/word/kualifikasi/' . (int) ($d['id'] ?? 0)); ?>?format=word" class="dropdown-item py-1" target="_blank">KUALIFIKASI <small class="text-muted">(.doc)</small></a>
                                            <a href="<?= site_url('admin/kontrak/export/word/kesediaan/' . (int) ($d['id'] ?? 0)); ?>?format=word" class="dropdown-item py-1" target="_blank">KESEDIAAN <small class="text-muted">(.doc)</small></a>
                                            <a href="<?= site_url('admin/kontrak/export/word/spbbj/' . (int) ($d['id'] ?? 0)); ?>?format=word" class="dropdown-item py-1" target="_blank">SPBBJ <small class="text-muted">(.doc)</small></a>
                                            <a href="<?= site_url('admin/kontrak/export/word/baphp/' . (int) ($d['id'] ?? 0)); ?>?format=word" class="dropdown-item py-1" target="_blank">BAPHP <small class="text-muted">(.doc)</small></a>
                                            <a href="<?= site_url('admin/kontrak/export/word/bast/' . (int) ($d['id'] ?? 0)); ?>?format=word" class="dropdown-item py-1" target="_blank">BAST <small class="text-muted">(.doc)</small></a>
                                            <a href="<?= site_url('admin/kontrak/export/word/spmk/' . (int) ($d['id'] ?? 0)); ?>?format=word" class="dropdown-item py-1" target="_blank">SPMK <small class="text-muted">(.doc)</small></a>
                                            <a href="<?= site_url('admin/kontrak/export/word/spk/' . (int) ($d['id'] ?? 0)); ?>?format=word" class="dropdown-item py-1" target="_blank">SPK <small class="text-muted">(.doc)</small></a>

                                            <div class="dropdown-divider my-1"></div>

                                            <!-- EXCEL SECTION -->
                                            <div class="px-3 py-1 font-weight-bold text-success small bg-light d-flex align-items-center justify-content-between">
                                                <span><i class="fas fa-file-excel mr-1"></i> EXPORT EXCEL</span>
                                                <span class="badge badge-success">XLSX</span>
                                            </div>
                                            <a href="<?= site_url('admin/kontrak/export/boq/' . (int) ($d['id'] ?? 0)); ?>" class="dropdown-item py-1" target="_blank">BOQ (Excel)</a>
                                            <a href="<?= site_url('admin/kontrak/export/evaluasi/' . (int) ($d['id'] ?? 0)); ?>" class="dropdown-item py-1" target="_blank">Evaluasi (Excel)</a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="text-center font-weight-bold">
                            <?= esc((string) ($d['nomor_kontrak'] ?? '')); ?>
                        </td>
                        <td class="text-center"><?= esc((string) ($d['tanggal_kontrak'] ?? '')); ?></td>
                        <td class="text-center"><?= esc((string) ($d['kode_personil'] ?? '')); ?></td>
                        <td class="text-left"><?= esc((string) ($d['nama'] ?? '')); ?></td>
                        <td class="text-left"><?= esc((string) ($d['alamat'] ?? '')); ?></td>
                        <td class="text-right"><?= esc((string) ($d['nik'] ?? '')); ?></td>
                        <td class="text-right"><?= esc((string) ($d['npwp'] ?? '')); ?></td>
                        <td class="text-left"><?= esc((string) ($d['jabatan'] ?? '')); ?></td>
                        <td class="text-center"><?= esc((string) ($d['durasi_pelaksanaan'] ?? '')); ?></td>
                        <td class="text-center"><?= esc((string) ($d['nomor_dipa'] ?? '')); ?></td>
                        <td class="text-center"><?= esc((string) ($d['tanggal_dipa'] ?? '')); ?></td>
                        <td class="text-left"><?= esc((string) ($d['mata_anggaran'] ?? '')); ?></td>
                        <td class="text-center"><?= esc((string) ($d['nomor_surat_undangan_pengadaan'] ?? '')); ?></td>
                        <td class="text-center"><?= esc((string) ($d['tanggal_surat_undangan_pengadaan'] ?? '')); ?></td>
                        <td class="text-center"><?= esc((string) ($d['nomor_surat_berita_acara_pengadaan'] ?? '')); ?></td>
                        <td class="text-center"><?= esc((string) ($d['tanggal_surat_berita_acara_pengadaan'] ?? '')); ?></td>
                        <td class="text-center"><?= esc((string) ($d['nomor_surat_penawaran'] ?? '')); ?></td>
                        <td class="text-center"><?= esc((string) ($d['tanggal_surat_penawaran'] ?? '')); ?></td>
                        <td class="text-center"><?= esc((string) ($d['nomor_undangan'] ?? '')); ?></td>
                        <td class="text-right"><?= number_format((float) ($d['total_penawaran'] ?? 0), 0, ',', '.'); ?></td>
                        <td class="text-center"><?= esc((string) ($d['tanggal_awal'] ?? '')); ?></td>
                        <td class="text-center"><?= esc((string) ($d['tanggal_akhir'] ?? '')); ?></td>
                        <td class="text-center"><?= esc((string) ($d['tahun_anggaran'] ?? '')); ?></td>
                        <td class="text-center"><?= esc((string) ($d['no_sppbj'] ?? '')); ?></td>
                        <td class="text-center"><?= esc((string) ($d['tanggal_sppbj'] ?? '')); ?></td>
                        <td class="text-left"><?= esc((string) ($d['pejabat_ppk'] ?? '')); ?></td>
                        <td class="text-right"><?= esc((string) ($d['nip_pejabat_ppk'] ?? '')); ?></td>
                        <td class="text-left"><?= esc((string) ($d['kedudukan_pejabat_ppk'] ?? '')); ?></td>
                        <td class="text-center"><?= esc((string) ($d['nomor_surat_keputusan_menteri'] ?? '')); ?></td>
                        <td class="text-center"><?= esc((string) ($d['tanggal_surat_keputusan_menteri'] ?? '')); ?></td>
                        <td class="text-center"><?= esc((string) ($d['nomor_perubahan_keputusan_menteri'] ?? '')); ?></td>
                        <td class="text-right"><?= esc((string) ($d['bank_nomor_rekening'] ?? '')); ?></td>
                        <td class="text-left"><?= esc((string) ($d['bank_nama'] ?? '')); ?></td>
                        <td class="text-left"><?= esc((string) ($d['bank_atas_nama'] ?? '')); ?></td>
                        <td class="text-left"><?= esc((string) ($d['bank_pembayaran'] ?? '')); ?></td>
                        <td class="text-center"><?= esc((string) ($d['kategori'] ?? '')); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (($can_export ?? true) === true): ?>
<div class="modal fade" id="modalExportBatchKi" tabindex="-1" role="dialog" aria-labelledby="modalExportBatchKiLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title font-weight-bold" id="modalExportBatchKiLabel">
                    <i class="fas fa-file-archive mr-2"></i>Export Batch Dokumen Kontrak KI
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formExportBatchKi" action="<?= site_url('admin/kontrak/ki/' . (int) ($paket['id'] ?? 0) . '/export-batch'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="alert alert-light border mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-folder-open text-warning fa-2x mr-3"></i>
                            <div>
                                <div class="text-muted small">Paket Pekerjaan:</div>
                                <div class="font-weight-bold text-dark"><?= esc((string) ($paket['nama_paket'] ?? '-')); ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- PILIH DOKUMEN -->
                    <div class="card mb-3 border">
                        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold text-dark"><i class="fas fa-file-alt mr-1 text-primary"></i> 1. Pilih Dokumen yang Ingin Diexport</span>
                            <div>
                                <button type="button" class="btn btn-xs btn-outline-primary" id="btnSelectAllBatchDocs">Pilih Semua</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="btnUnselectAllBatchDocs">Kosongkan</button>
                            </div>
                        </div>
                        <div class="card-body py-2">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input batch-doc-item" id="batchDocPenawaran" name="documents[]" value="penawaran" checked>
                                        <label class="custom-control-label font-weight-normal" for="batchDocPenawaran">
                                            <strong>Penawaran</strong> <span class="badge badge-danger">PDF</span>
                                        </label>
                                    </div>
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input batch-doc-item" id="batchDocPakta" name="documents[]" value="pakta-integritas" checked>
                                        <label class="custom-control-label font-weight-normal" for="batchDocPakta">
                                            <strong>Pakta Integritas</strong> <span class="badge badge-danger">PDF</span>
                                        </label>
                                    </div>
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input batch-doc-item" id="batchDocKualifikasi" name="documents[]" value="kualifikasi" checked>
                                        <label class="custom-control-label font-weight-normal" for="batchDocKualifikasi">
                                            <strong>Kualifikasi</strong> <span class="badge badge-danger">PDF</span>
                                        </label>
                                    </div>
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input batch-doc-item" id="batchDocKesediaan" name="documents[]" value="kesediaan" checked>
                                        <label class="custom-control-label font-weight-normal" for="batchDocKesediaan">
                                            <strong>Kesediaan</strong> <span class="badge badge-danger">PDF</span>
                                        </label>
                                    </div>
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input batch-doc-item" id="batchDocSpbbj" name="documents[]" value="spbbj" checked>
                                        <label class="custom-control-label font-weight-normal" for="batchDocSpbbj">
                                            <strong>SPBBJ</strong> <span class="badge badge-danger">PDF</span>
                                        </label>
                                    </div>
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input batch-doc-item" id="batchDocBaphp" name="documents[]" value="baphp" checked>
                                        <label class="custom-control-label font-weight-normal" for="batchDocBaphp">
                                            <strong>BAPHP</strong> <span class="badge badge-danger">PDF</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input batch-doc-item" id="batchDocBast" name="documents[]" value="bast" checked>
                                        <label class="custom-control-label font-weight-normal" for="batchDocBast">
                                            <strong>BAST</strong> <span class="badge badge-danger">PDF</span>
                                        </label>
                                    </div>
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input batch-doc-item" id="batchDocSpmk" name="documents[]" value="spmk" checked>
                                        <label class="custom-control-label font-weight-normal" for="batchDocSpmk">
                                            <strong>SPMK</strong> <span class="badge badge-danger">PDF</span>
                                        </label>
                                    </div>
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input batch-doc-item" id="batchDocSpk" name="documents[]" value="spk" checked>
                                        <label class="custom-control-label font-weight-normal" for="batchDocSpk">
                                            <strong>SPK</strong> <span class="badge badge-danger">PDF</span>
                                        </label>
                                    </div>
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input batch-doc-item" id="batchDocBoq" name="documents[]" value="boq" checked>
                                        <label class="custom-control-label font-weight-normal" for="batchDocBoq">
                                            <strong>BOQ</strong> <span class="badge badge-success">Excel</span>
                                        </label>
                                    </div>
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input batch-doc-item" id="batchDocEvaluasi" name="documents[]" value="evaluasi" checked>
                                        <label class="custom-control-label font-weight-normal" for="batchDocEvaluasi">
                                            <strong>Evaluasi</strong> <span class="badge badge-success">Excel</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PILIH ANGGOTA -->
                    <div class="card mb-3 border">
                        <div class="card-header bg-light py-2">
                            <span class="font-weight-bold text-dark"><i class="fas fa-users mr-1 text-info"></i> 2. Pilih Anggota / Personil</span>
                        </div>
                        <div class="card-body py-2">
                            <div class="mb-2">
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="batchMemberAll" name="target_members" value="all" class="custom-control-input" checked>
                                    <label class="custom-control-label font-weight-bold" for="batchMemberAll">
                                        Semua Anggota (<?= count($data ?? []); ?> Personil)
                                    </label>
                                </div>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="batchMemberSelected" name="target_members" value="selected" class="custom-control-input">
                                    <label class="custom-control-label font-weight-bold" for="batchMemberSelected">
                                        Pilih Anggota Tertentu
                                    </label>
                                </div>
                            </div>

                            <div id="batchMemberListWrapper" class="mt-2" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="text-muted">Centang anggota yang ingin diexport:</small>
                                    <div>
                                        <button type="button" class="btn btn-xs btn-outline-primary" id="btnSelectAllBatchMembers">Pilih Semua</button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary" id="btnUnselectAllBatchMembers">Kosongkan</button>
                                    </div>
                                </div>
                                <div class="border rounded p-2" style="max-height: 180px; overflow-y: auto; background-color: #fcfcfc;">
                                    <?php if (! empty($data)): ?>
                                        <?php foreach ($data as $m): ?>
                                            <div class="custom-control custom-checkbox mb-1">
                                                <input type="checkbox" class="custom-control-input batch-member-item" id="batchMember_<?= (int) ($m['id'] ?? 0); ?>" name="ki_ids[]" value="<?= (int) ($m['id'] ?? 0); ?>" checked>
                                                <label class="custom-control-label font-weight-normal small" for="batchMember_<?= (int) ($m['id'] ?? 0); ?>">
                                                    <strong><?= esc((string) ($m['nama'] ?? '-')); ?></strong> 
                                                    <span class="text-muted">(<?= esc((string) ($m['nomor_kontrak'] ?? '-')); ?> - <?= esc((string) ($m['jabatan'] ?? '-')); ?>)</span>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-muted small">Belum ada data anggota KI.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PILIH FORMAT DOKUMEN -->
                    <div class="card mb-3 border">
                        <div class="card-header bg-light py-2">
                            <span class="font-weight-bold text-dark"><i class="fas fa-file-invoice mr-1 text-secondary"></i> 3. Format Dokumen</span>
                        </div>
                        <div class="card-body py-2">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="formatPdf" name="export_format" value="pdf" class="custom-control-input" checked>
                                        <label class="custom-control-label font-weight-bold" for="formatPdf">
                                            <i class="fas fa-file-pdf text-danger mr-1"></i> PDF (.pdf)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="formatWord" name="export_format" value="word" class="custom-control-input">
                                        <label class="custom-control-label font-weight-bold" for="formatWord">
                                            <i class="fas fa-file-word text-primary mr-1"></i> Word (.doc)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="formatBoth" name="export_format" value="both" class="custom-control-input">
                                        <label class="custom-control-label font-weight-bold" for="formatBoth">
                                            <i class="fas fa-copy text-info mr-1"></i> Keduanya (PDF & Word)
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-1">*Catatan: Berkas BOQ dan Evaluasi tetap diekspor dalam format spreadsheet Excel (.xlsx).</small>
                        </div>
                    </div>

                    <!-- PENGATURAN HALAMAN DOKUMEN -->
                    <div class="card mb-3 border">
                        <div class="card-header bg-light py-2">
                            <span class="font-weight-bold text-dark"><i class="fas fa-file-alt mr-1 text-primary"></i> 4. Pilihan Halaman Dokumen</span>
                        </div>
                        <div class="card-body py-2">
                            <div class="mb-2">
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="batchPagesAll" name="page_mode" value="all" class="custom-control-input" checked>
                                    <label class="custom-control-label font-weight-bold" for="batchPagesAll">
                                        <i class="fas fa-layer-group text-secondary mr-1"></i> Semua Halaman (Lengkap)
                                    </label>
                                </div>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="batchPagesCustom" name="page_mode" value="custom" class="custom-control-input">
                                    <label class="custom-control-label font-weight-bold" for="batchPagesCustom">
                                        <i class="fas fa-filter text-success mr-1"></i> Halaman Tertentu Saja (Misal: Halaman 3 SPK)
                                    </label>
                                </div>
                            </div>

                            <div id="batchPagesDetailWrapper" class="mt-2 pt-2 border-top" style="display: none;">
                                <div class="form-group mb-2">
                                    <label class="font-weight-bold small mb-1">Target Dokumen:</label>
                                    <div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="pageTargetSpk" name="page_target" value="spk_only" class="custom-control-input" checked>
                                            <label class="custom-control-label small" for="pageTargetSpk">
                                                <strong>Khusus Dokumen SPK</strong> <span class="text-muted">(Rekomendasi - dokumen multi-halaman inti & tanda tangan)</span>
                                            </label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="pageTargetAll" name="page_target" value="all_docs" class="custom-control-input">
                                            <label class="custom-control-label small" for="pageTargetAll">
                                                <strong>Semua Berkas Terpilih</strong> <span class="text-muted">(Jika berkas memiliki halaman tersebut)</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-2">
                                    <label class="font-weight-bold small mb-1">Pilihan Cepat (Preset Halaman SPK):</label>
                                    <div class="btn-group-toggle d-flex flex-wrap">
                                        <button type="button" class="btn btn-xs btn-success mr-1 mb-1 btn-page-preset" data-range="3">
                                            <i class="fas fa-signature mr-1"></i> Halaman 3 Saja (Lembar TTD SPK)
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary mr-1 mb-1 btn-page-preset" data-range="1-3">
                                            <i class="fas fa-file-contract mr-1"></i> Halaman 1-3 (SPK Inti: Cover s/d TTD)
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary mr-1 mb-1 btn-page-preset" data-range="1">
                                            <i class="fas fa-book-open mr-1"></i> Halaman 1 (Cover Saja)
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary mb-1 btn-page-preset" data-range="4-11">
                                            <i class="fas fa-list-ol mr-1"></i> Halaman 4-Selesai (Syarat Umum)
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group mb-1">
                                    <label for="batchPageRange" class="font-weight-bold small mb-1">Nomor / Rentang Halaman yang Diexport:</label>
                                    <div class="input-group input-group-sm" style="max-width: 320px;">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                        </div>
                                        <input type="text" class="form-control form-control-sm font-weight-bold text-primary" id="batchPageRange" name="page_range" value="3" placeholder="Contoh: 3 atau 1-3 atau 1,3">
                                    </div>
                                    <small class="text-muted mt-1 d-block">
                                        Format: Masukkan angka halaman tunggal (contoh: <code>3</code>) atau rentang halaman (contoh: <code>1-3</code> atau <code>1,3</code>).
                                    </small>
                                </div>

                                <div class="alert alert-light border small text-muted py-1 px-2 mb-0 mt-2">
                                    <i class="fas fa-info-circle text-info mr-1"></i>
                                    <strong>Panduan Halaman Dokumen SPK:</strong>
                                    <ul class="mb-0 pl-3">
                                        <li><strong>Hal 1:</strong> Cover Judul SPK</li>
                                        <li><strong>Hal 2:</strong> Data Surat Perjanjian Kerja (Pihak Pertama & Kedua)</li>
                                        <li><strong>Hal 3:</strong> <u>Sumber Dana, Nilai Kontrak, Pembayaran, dan Lembar Tanda Tangan PPK & Penyedia</u></li>
                                        <li><strong>Hal 4 dst:</strong> Syarat-Syarat Umum Kontrak (SSUK)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PENGATURAN BENTUK HASIL BERKAS -->
                    <div class="card mb-0 border">
                        <div class="card-header bg-light py-2">
                            <span class="font-weight-bold text-dark"><i class="fas fa-box-open mr-1 text-primary"></i> 5. Bentuk Hasil Berkas</span>
                        </div>
                        <div class="card-body py-2">
                            <div class="mb-2">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="outputModeZip" name="output_mode" value="zip" class="custom-control-input" checked>
                                    <label class="custom-control-label font-weight-bold" for="outputModeZip">
                                        <i class="fas fa-file-archive text-warning mr-1"></i> Arsip ZIP (File Terpisah per Dokumen / Personil)
                                    </label>
                                    <div id="zipStructureWrapper" class="ml-4 mt-1">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="folderPerPerson" name="folder_per_person" value="1" checked>
                                            <label class="custom-control-label font-weight-normal small" for="folderPerPerson">
                                                Kelompokkan berkas ke dalam folder per personil di dalam file ZIP
                                                <span class="text-muted">(Contoh: <code>[Nama]/PENAWARAN_[Nama].pdf</code>)</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="border-top pt-2">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="outputModeMerged" name="output_mode" value="merged" class="custom-control-input">
                                    <label class="custom-control-label font-weight-bold text-success" for="outputModeMerged">
                                        <i class="fas fa-file-pdf text-danger mr-1"></i> Simpan / Gabung Menjadi 1 File Saja Siap Cetak (Single File)
                                    </label>
                                    <small class="text-muted d-block ml-4 mt-1">
                                        Seluruh berkas dokumen dari seluruh anggota yang dipilih akan digabungkan secara berurutan menjadi <strong>1 file dokumen tunggal</strong> (format <code>.pdf</code> atau <code>.doc</code>). Anda dapat langsung mencetak seluruhnya sekaligus dalam 1 kali perintah print (sangat praktis, contoh: mencetak lembar Halaman 3 SPK semua personil).
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success font-weight-bold" id="btnSubmitBatchExport">
                        <i class="fas fa-file-archive mr-1"></i> Download ZIP
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (($can_import ?? $can_edit ?? false) === true): ?>
<div class="modal fade" id="modalImportKi" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-excel mr-1 text-success"></i> Import Data KI</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formImportKi" action="<?= site_url('admin/kontrak/ki/' . (int) ($paket['id'] ?? 0) . '/import'); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        Download format terlebih dahulu, ubah datanya, lalu upload kembali file <strong>.xls/.xlsx</strong>.
                    </div>

                    <div class="mb-3">
                        <a href="<?= site_url('admin/kontrak/ki/' . (int) ($paket['id'] ?? 0) . '/export'); ?>" class="btn btn-success btn-sm" target="_blank">
                            <i class="fas fa-download mr-1"></i> Download Format (XLSX)
                        </a>
                    </div>

                    <div class="form-group mb-0">
                        <label for="file_excel">File Excel</label>
                        <input type="file" class="form-control" id="file_excel" name="file_excel" accept=".xls,.xlsx,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                        <small class="text-muted">Jika nomor kontrak sudah ada, datanya akan di-replace. Jika belum ada, data baru akan ditambahkan.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (($can_add ?? false) === true || ($can_edit ?? false) === true): ?>
<div class="modal fade" id="modalTambahKi" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahKiTitle">Tambah Data KI</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formTambahKi" action="<?= site_url('admin/kontrak/ki/' . (int) ($paket['id'] ?? 0) . '/tambah'); ?>" method="post" novalidate>
                <?= csrf_field(); ?>
                <input type="hidden" id="kiEditId" name="ki_id" value="">
                <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                    <ul class="nav nav-tabs" id="tambahKiTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-utama" data-toggle="tab" href="#pane-utama" role="tab"><i class="fas fa-id-card mr-1"></i> Utama & Personil</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-anggaran" data-toggle="tab" href="#pane-anggaran" role="tab"><i class="fas fa-money-bill-wave mr-1"></i> DIPA & Anggaran</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-dokumen" data-toggle="tab" href="#pane-dokumen" role="tab"><i class="fas fa-file-alt mr-1"></i> Dokumen Pengadaan & Kontrak</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-pejabat" data-toggle="tab" href="#pane-pejabat" role="tab"><i class="fas fa-user-shield mr-1"></i> Pejabat PPK & SK</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-bank" data-toggle="tab" href="#pane-bank" role="tab"><i class="fas fa-address-book mr-1"></i> Data Diri, Bank & Kontak</a>
                        </li>
                    </ul>

                    <div class="tab-content pt-3" id="tambahKiTabContent">
                        <div class="tab-pane fade show active" id="pane-utama" role="tabpanel">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="nomor_kontrak">Nomor Kontrak <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nomor_kontrak" name="nomor_kontrak" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="tanggal_kontrak">Tanggal Kontrak</label>
                                    <input type="date" class="form-control" id="tanggal_kontrak" name="tanggal_kontrak">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="kode_personil">Kode Personil</label>
                                    <input type="text" class="form-control" id="kode_personil" name="kode_personil">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="nama">Nama Personil <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nama" name="nama" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="jabatan">Jabatan</label>
                                    <input type="text" class="form-control" id="jabatan" name="jabatan">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="durasi_pelaksanaan">Durasi Pelaksanaan</label>
                                    <input type="text" class="form-control" id="durasi_pelaksanaan" name="durasi_pelaksanaan">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="tanggal_awal">Tanggal Awal</label>
                                    <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="tanggal_akhir">Tanggal Akhir</label>
                                    <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir">
                                </div>
                                <div class="form-group col-md-8">
                                    <label for="nama_pekerjaan">Nama Pekerjaan</label>
                                    <input type="text" class="form-control" id="nama_pekerjaan" name="nama_pekerjaan">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="kategori">Kategori</label>
                                    <input type="text" class="form-control" id="kategori" name="kategori">
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="pane-anggaran" role="tabpanel">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="nomor_dipa">Nomor DIPA</label>
                                    <input type="text" class="form-control" id="nomor_dipa" name="nomor_dipa">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="tanggal_dipa">Tanggal DIPA</label>
                                    <input type="date" class="form-control" id="tanggal_dipa" name="tanggal_dipa">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="mata_anggaran">Mata Anggaran</label>
                                    <input type="text" class="form-control" id="mata_anggaran" name="mata_anggaran">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="tahun_anggaran">Tahun Anggaran</label>
                                    <input type="text" class="form-control" id="tahun_anggaran" name="tahun_anggaran">
                                </div>
                            </div>
                            <hr>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="total_penawaran">Total Penawaran</label>
                                    <input type="text" class="form-control" id="total_penawaran" name="total_penawaran" placeholder="0">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="nominal_kontrak">Nominal Kontrak</label>
                                    <input type="text" class="form-control" id="nominal_kontrak" name="nominal_kontrak" placeholder="0">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="nominal_hps">Nominal HPS</label>
                                    <input type="text" class="form-control" id="nominal_hps" name="nominal_hps" placeholder="0">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="jenis_pembayaran">Jenis Pembayaran</label>
                                    <input type="text" class="form-control" id="jenis_pembayaran" name="jenis_pembayaran">
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="pane-dokumen" role="tabpanel">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="nomor_surat_undangan_pengadaan">Nomor Surat Undangan Pengadaan</label>
                                    <input type="text" class="form-control" id="nomor_surat_undangan_pengadaan" name="nomor_surat_undangan_pengadaan">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="tanggal_surat_undangan_pengadaan">Tanggal Surat Undangan Pengadaan</label>
                                    <input type="date" class="form-control" id="tanggal_surat_undangan_pengadaan" name="tanggal_surat_undangan_pengadaan">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="nomor_surat_berita_acara_pengadaan">Nomor Surat Berita Acara Pengadaan</label>
                                    <input type="text" class="form-control" id="nomor_surat_berita_acara_pengadaan" name="nomor_surat_berita_acara_pengadaan">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="tanggal_surat_berita_acara_pengadaan">Tanggal Surat Berita Acara Pengadaan</label>
                                    <input type="date" class="form-control" id="tanggal_surat_berita_acara_pengadaan" name="tanggal_surat_berita_acara_pengadaan">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="nomor_surat_penawaran">Nomor Surat Penawaran</label>
                                    <input type="text" class="form-control" id="nomor_surat_penawaran" name="nomor_surat_penawaran">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="tanggal_surat_penawaran">Tanggal Surat Penawaran</label>
                                    <input type="date" class="form-control" id="tanggal_surat_penawaran" name="tanggal_surat_penawaran">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="nomor_undangan">Nomor Undangan</label>
                                    <input type="text" class="form-control" id="nomor_undangan" name="nomor_undangan">
                                </div>
                            </div>
                            <hr>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="no_sppbj">No SPPBJ</label>
                                    <input type="text" class="form-control" id="no_sppbj" name="no_sppbj">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="tanggal_sppbj">Tanggal SPPBJ</label>
                                    <input type="date" class="form-control" id="tanggal_sppbj" name="tanggal_sppbj">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="nomor_spmk">Nomor SPMK</label>
                                    <input type="text" class="form-control" id="nomor_spmk" name="nomor_spmk">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="tanggal_spmk">Tanggal SPMK</label>
                                    <input type="date" class="form-control" id="tanggal_spmk" name="tanggal_spmk">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="nomor_baphp">Nomor BAPHP</label>
                                    <input type="text" class="form-control" id="nomor_baphp" name="nomor_baphp">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="nomor_bast">Nomor BAST</label>
                                    <input type="text" class="form-control" id="nomor_bast" name="nomor_bast">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="nomor_surat_permohonan">Nomor Surat Permohonan</label>
                                    <input type="text" class="form-control" id="nomor_surat_permohonan" name="nomor_surat_permohonan">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="tanggal_surat_permohonan">Tanggal Surat Permohonan</label>
                                    <input type="date" class="form-control" id="tanggal_surat_permohonan" name="tanggal_surat_permohonan">
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="pane-pejabat" role="tabpanel">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="pejabat_ppk">Pejabat PPK</label>
                                    <input type="text" class="form-control" id="pejabat_ppk" name="pejabat_ppk">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="nip_pejabat_ppk">NIP Pejabat PPK</label>
                                    <input type="text" class="form-control" id="nip_pejabat_ppk" name="nip_pejabat_ppk">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="kedudukan_pejabat_ppk">Kedudukan Pejabat PPK</label>
                                    <input type="text" class="form-control" id="kedudukan_pejabat_ppk" name="kedudukan_pejabat_ppk">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="nomor_surat_keputusan_menteri">Nomor Surat Keputusan Menteri</label>
                                    <input type="text" class="form-control" id="nomor_surat_keputusan_menteri" name="nomor_surat_keputusan_menteri">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="tanggal_surat_keputusan_menteri">Tanggal Surat Keputusan Menteri</label>
                                    <input type="date" class="form-control" id="tanggal_surat_keputusan_menteri" name="tanggal_surat_keputusan_menteri">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="nomor_perubahan_keputusan_menteri">Nomor Perubahan Keputusan Menteri</label>
                                    <input type="text" class="form-control" id="nomor_perubahan_keputusan_menteri" name="nomor_perubahan_keputusan_menteri">
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="pane-bank" role="tabpanel">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="nik">NIK</label>
                                    <input type="text" class="form-control" id="nik" name="nik">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="npwp">NPWP</label>
                                    <input type="text" class="form-control" id="npwp" name="npwp">
                                </div>
                                <div class="form-group col-md-12">
                                    <label for="alamat">Alamat</label>
                                    <textarea class="form-control" id="alamat" name="alamat" rows="2"></textarea>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="pendidikan">Pendidikan</label>
                                    <input type="text" class="form-control" id="pendidikan" name="pendidikan">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="sertifikat">Sertifikat</label>
                                    <input type="text" class="form-control" id="sertifikat" name="sertifikat">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="bank_nama">Bank Nama</label>
                                    <input type="text" class="form-control" id="bank_nama" name="bank_nama">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="bank_nomor_rekening">Bank Nomor Rekening</label>
                                    <input type="text" class="form-control" id="bank_nomor_rekening" name="bank_nomor_rekening">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="bank_atas_nama">Bank Atas Nama</label>
                                    <input type="text" class="form-control" id="bank_atas_nama" name="bank_atas_nama">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="bank_pembayaran">Bank Pembayaran</label>
                                    <input type="text" class="form-control" id="bank_pembayaran" name="bank_pembayaran">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="nomor_telefon_ki">Nomor Telepon KI</label>
                                    <input type="text" class="form-control" id="nomor_telefon_ki" name="nomor_telefon_ki">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="email_ki">Email KI</label>
                                    <input type="text" class="form-control" id="email_ki" name="email_ki" placeholder="contoh@email.com">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitKi">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script>
    (() => {
        if (typeof $ === 'undefined' || ! $.fn.DataTable) {
            return;
        }

        const $table = $('#tableKontrakKi');
        if (! $table.length || $.fn.dataTable.isDataTable($table)) {
            return;
        }

        const dt = $table.DataTable({
            responsive: false,
            autoWidth: false,
            scrollX: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Semua']],
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

        // Kolom utama disamakan dengan bagian "Utama" pada modal tambah data.
        // ACTION kini ada di kolom pertama (index 0).
        const mainColumns = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
        const allColumns = Array.from({ length: dt.columns().count() }, (_, i) => i);

        const applyColumnView = (showAll) => {
            allColumns.forEach((idx) => {
                const visible = showAll || mainColumns.includes(idx);
                dt.column(idx).visible(visible, false);
            });
            dt.columns.adjust().draw(false);

            $('#btnKolomUtama').toggleClass('active', !showAll);
            $('#btnKolomSemua').toggleClass('active', showAll);
        };

        $('#btnKolomUtama').on('click', function () {
            applyColumnView(false);
        });

        $('#btnKolomSemua').on('click', function () {
            applyColumnView(true);
        });

        // Default: tampilkan kolom utama terlebih dahulu.
        applyColumnView(false);
    })();

    // Edit KI: populate & show modal
    (() => {
        const paketId = <?= (int) ($paket['id'] ?? 0); ?>;
        const $modal  = $('#modalTambahKi');
        const $form   = $('#formTambahKi');
        const $title  = $('#modalTambahKiTitle');
        const $btnSubmit = $('#btnSubmitKi');
        const $kiEditId  = $('#kiEditId');

        const fieldMapping = {
            'data-nomor-kontrak': 'nomor_kontrak',
            'data-tanggal-kontrak': 'tanggal_kontrak',
            'data-kode-personil': 'kode_personil',
            'data-nama': 'nama',
            'data-alamat': 'alamat',
            'data-nik': 'nik',
            'data-npwp': 'npwp',
            'data-jabatan': 'jabatan',
            'data-durasi-pelaksanaan': 'durasi_pelaksanaan',
            'data-tanggal-awal': 'tanggal_awal',
            'data-tanggal-akhir': 'tanggal_akhir',
            'data-nama-pekerjaan': 'nama_pekerjaan',
            'data-nomor-dipa': 'nomor_dipa',
            'data-tanggal-dipa': 'tanggal_dipa',
            'data-mata-anggaran': 'mata_anggaran',
            'data-nomor-surat-undangan-pengadaan': 'nomor_surat_undangan_pengadaan',
            'data-tanggal-surat-undangan-pengadaan': 'tanggal_surat_undangan_pengadaan',
            'data-nomor-surat-berita-acara-pengadaan': 'nomor_surat_berita_acara_pengadaan',
            'data-tanggal-surat-berita-acara-pengadaan': 'tanggal_surat_berita_acara_pengadaan',
            'data-nomor-surat-penawaran': 'nomor_surat_penawaran',
            'data-tanggal-surat-penawaran': 'tanggal_surat_penawaran',
            'data-nomor-undangan': 'nomor_undangan',
            'data-total-penawaran': 'total_penawaran',
            'data-tahun-anggaran': 'tahun_anggaran',
            'data-no-sppbj': 'no_sppbj',
            'data-tanggal-sppbj': 'tanggal_sppbj',
            'data-pejabat-ppk': 'pejabat_ppk',
            'data-nip-pejabat-ppk': 'nip_pejabat_ppk',
            'data-kedudukan-pejabat-ppk': 'kedudukan_pejabat_ppk',
            'data-nomor-surat-keputusan-menteri': 'nomor_surat_keputusan_menteri',
            'data-tanggal-surat-keputusan-menteri': 'tanggal_surat_keputusan_menteri',
            'data-nomor-perubahan-keputusan-menteri': 'nomor_perubahan_keputusan_menteri',
            'data-bank-nomor-rekening': 'bank_nomor_rekening',
            'data-bank-nama': 'bank_nama',
            'data-bank-atas-nama': 'bank_atas_nama',
            'data-bank-pembayaran': 'bank_pembayaran',
            'data-kategori': 'kategori',
            'data-nomor-telefon-ki': 'nomor_telefon_ki',
            'data-email-ki': 'email_ki',
            'data-nominal-kontrak': 'nominal_kontrak',
            'data-nominal-hps': 'nominal_hps',
            'data-nomor-spmk': 'nomor_spmk',
            'data-tanggal-spmk': 'tanggal_spmk',
            'data-nomor-baphp': 'nomor_baphp',
            'data-nomor-surat-permohonan': 'nomor_surat_permohonan',
            'data-tanggal-surat-permohonan': 'tanggal_surat_permohonan',
            'data-jenis-pembayaran': 'jenis_pembayaran',
            'data-nomor-bast': 'nomor_bast',
            'data-pendidikan': 'pendidikan',
            'data-sertifikat': 'sertifikat'
        };

        // Klik tombol Edit: isi form lalu buka modal secara programatik
        $(document).on('click', '.edit-ki-btn', function () {
            const $btn         = $(this);
            const kiId         = $btn.data('id');
            const nomorKontrak = $btn.attr('data-nomor-kontrak') || '';

            // Reset form & tab
            $form[0].reset();
            $kiEditId.val('');
            $('#tambahKiTab a[href="#pane-utama"]').tab('show');

            // Isi semua field dari data-attributes
            Object.entries(fieldMapping).forEach(([attr, fieldName]) => {
                const val    = $btn.attr(attr) || '';
                const $field = $form.find(`[name="${fieldName}"]`);
                if ($field.length) {
                    $field.val(val);
                }
            });

            // Set ID & action
            $kiEditId.val(kiId);
            $form.attr('action', `<?= rtrim(site_url('admin/kontrak/ki'), '/'); ?>/${paketId}/${kiId}/ubah`);

            // Judul & label tombol
            $title.html(`<i class="fas fa-edit mr-1 text-warning"></i> Edit Data KI <span class="badge badge-secondary ml-1">${nomorKontrak}</span>`);
            $btnSubmit.text('Simpan Perubahan').prop('disabled', false);

            // Buka modal
            $modal.modal('show');
        });

        // Reset saat modal ditutup
        $modal.on('hidden.bs.modal', function () {
            $form[0].reset();
            $kiEditId.val('');
            $form.attr('action', `<?= rtrim(site_url('admin/kontrak/ki'), '/'); ?>/${paketId}/tambah`);
            $title.text('Tambah Data KI');
            $btnSubmit.text('Simpan').prop('disabled', false);
            $('#tambahKiTab a[href="#pane-utama"]').tab('show');
        });

        // ---------------------------------------------------------------
        // Submit handler: validasi manual (nomor_kontrak & nama wajib),
        // tampilkan Swal loading, lalu submit via native HTMLFormElement
        // agar tidak terhambat oleh disabled-button atau HTML5 validation.
        // ---------------------------------------------------------------
        $form.on('submit', function (e) {
            e.preventDefault(); // selalu cegah submit default

            const nomorKontrak = $form.find('[name="nomor_kontrak"]').val();
            const nama         = $form.find('[name="nama"]').val();

            if (!nomorKontrak || !nomorKontrak.trim()) {
                // Pindah ke tab Utama & fokus ke field
                $('#tambahKiTab a[href="#pane-utama"]').tab('show');
                $form.find('#nomor_kontrak').focus();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Nomor Kontrak wajib diisi.', confirmButtonText: 'OK' });
                } else {
                    alert('Nomor Kontrak wajib diisi.');
                }
                return;
            }

            if (!nama || !nama.trim()) {
                $('#tambahKiTab a[href="#pane-utama"]').tab('show');
                $form.find('#nama').focus();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Nama Personil wajib diisi.', confirmButtonText: 'OK' });
                } else {
                    alert('Nama Personil wajib diisi.');
                }
                return;
            }

            // Tampilkan loading
            $btnSubmit.prop('disabled', true);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Memproses Data',
                    text: 'Mohon tunggu...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading()
                });
            }

            // Submit via native method (melewati disabled-button blocking)
            const formEl = $form[0];
            setTimeout(() => {
                HTMLFormElement.prototype.submit.call(formEl);
            }, 50);
        });
    })();

    // SweetAlert loading untuk form Import (tetap gunakan submit event biasa)
    (() => {
        if (typeof $ === 'undefined') return;

        const $formImport = $('#formImportKi');
        if (! $formImport.length) return;

        $formImport.on('submit', function () {
            const $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Memproses Import',
                    text: 'Mohon tunggu...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading()
                });
            }
        });
    })();
    // Handler Modal Export Batch KI
    (() => {
        if (typeof $ === 'undefined') return;

        // Toggle Member List
        $('input[name="target_members"]').on('change', function () {
            if ($(this).val() === 'selected') {
                $('#batchMemberListWrapper').slideDown(200);
            } else {
                $('#batchMemberListWrapper').slideUp(200);
            }
        });

        // Quick buttons Documents
        $('#btnSelectAllBatchDocs').on('click', function () {
            $('.batch-doc-item').prop('checked', true);
        });
        $('#btnUnselectAllBatchDocs').on('click', function () {
            $('.batch-doc-item').prop('checked', false);
        });

        // Quick buttons Members
        $('#btnSelectAllBatchMembers').on('click', function () {
            $('.batch-member-item').prop('checked', true);
        });
        $('#btnUnselectAllBatchMembers').on('click', function () {
            $('.batch-member-item').prop('checked', false);
        });

        // Toggle Pilihan Halaman Dokumen
        $('input[name="page_mode"]').on('change', function () {
            if ($(this).val() === 'custom') {
                $('#batchPagesDetailWrapper').slideDown(200);
            } else {
                $('#batchPagesDetailWrapper').slideUp(200);
            }
        });

        // Presets Halaman
        $('.btn-page-preset').on('click', function () {
            const range = $(this).data('range');
            $('#batchPageRange').val(range);
            $('.btn-page-preset').removeClass('btn-success btn-primary btn-info btn-secondary').addClass('btn-outline-secondary');
            $(this).removeClass('btn-outline-secondary').addClass('btn-success');
        });

        // Toggle Bentuk Hasil Berkas (ZIP vs 1 File Gabungan)
        $('input[name="output_mode"]').on('change', function () {
            if ($(this).val() === 'merged') {
                $('#zipStructureWrapper').slideUp(150);
                $('#btnSubmitBatchExport').html('<i class="fas fa-file-download mr-1"></i> Download 1 File Gabungan');
            } else {
                $('#zipStructureWrapper').slideDown(150);
                $('#btnSubmitBatchExport').html('<i class="fas fa-file-archive mr-1"></i> Download ZIP');
            }
        });

        // Validation & Submit handling
        $('#formExportBatchKi').on('submit', function (e) {
            const checkedDocs = $('.batch-doc-item:checked').length;
            if (checkedDocs === 0) {
                e.preventDefault();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Pilih minimal satu jenis dokumen yang ingin diexport.', confirmButtonText: 'OK' });
                } else {
                    alert('Pilih minimal satu jenis dokumen yang ingin diexport.');
                }
                return false;
            }

            const targetMembers = $('input[name="target_members"]:checked').val();
            if (targetMembers === 'selected') {
                const checkedMembers = $('.batch-member-item:checked').length;
                if (checkedMembers === 0) {
                    e.preventDefault();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Pilih minimal satu personil/anggota yang ingin diexport.', confirmButtonText: 'OK' });
                    } else {
                        alert('Pilih minimal satu personil/anggota yang ingin diexport.');
                    }
                    return false;
                }
            }

            const isMerged = $('input[name="output_mode"]:checked').val() === 'merged';
            $btn.prop('disabled', true).html(isMerged ? '<i class="fas fa-spinner fa-spin mr-1"></i> Sedang Menggabungkan Berkas...' : '<i class="fas fa-spinner fa-spin mr-1"></i> Sedang Menyiapkan ZIP...');

            // Karena proses download berkas via attachment tidak reload halaman, re-enable button setelah 3.5 detik
            setTimeout(() => {
                $btn.prop('disabled', false).html(originalHtml);
                $('#modalExportBatchKi').modal('hide');
            }, 3500);
        });
    })();
</script>
<?= $this->endSection(); ?>
