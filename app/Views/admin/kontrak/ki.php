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
                                        <div class="dropdown-menu" role="menu">
                                            <a href="<?= site_url('admin/kontrak/export/penawaran/' . (int) ($d['id'] ?? 0)); ?>" class="dropdown-item" target="_blank">PENAWARAN</a>
                                            <a href="<?= site_url('admin/kontrak/export/pakta-integritas/' . (int) ($d['id'] ?? 0)); ?>" class="dropdown-item" target="_blank">PAKTA INTEGRITAS</a>
                                            <a href="<?= site_url('admin/kontrak/export/kualifikasi/' . (int) ($d['id'] ?? 0)); ?>" class="dropdown-item" target="_blank">KUALIFIKASI</a>
                                            <a href="<?= site_url('admin/kontrak/export/boq/' . (int) ($d['id'] ?? 0)); ?>" class="dropdown-item" target="_blank">BOQ</a>
                                            <a href="<?= site_url('admin/kontrak/export/kesediaan/' . (int) ($d['id'] ?? 0)); ?>" class="dropdown-item" target="_blank">KESEDIAAN</a>
                                            <a href="<?= site_url('admin/kontrak/export/spbbj/' . (int) ($d['id'] ?? 0)); ?>" class="dropdown-item" target="_blank">SPBBJ</a>
                                            <a href="<?= site_url('admin/kontrak/export/evaluasi/' . (int) ($d['id'] ?? 0)); ?>" class="dropdown-item" target="_blank">Evaluasi</a>
                                            <a href="<?= site_url('admin/kontrak/export/baphp/' . (int) ($d['id'] ?? 0)); ?>" class="dropdown-item" target="_blank">BAPHP</a>
                                            <a href="<?= site_url('admin/kontrak/export/bast/' . (int) ($d['id'] ?? 0)); ?>" class="dropdown-item" target="_blank">BAST</a>
                                            <div class="dropdown-divider"></div>
                                            <a href="<?= site_url('admin/kontrak/export/spmk/' . (int) ($d['id'] ?? 0)); ?>" class="dropdown-item" target="_blank">SPMK</a>
                                            <a href="<?= site_url('admin/kontrak/export/spk/' . (int) ($d['id'] ?? 0)); ?>" class="dropdown-item" target="_blank">SPK</a>
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
</script>
<?= $this->endSection(); ?>
