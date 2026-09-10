<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<style>
/* Styling Khusus Modal Pinjam Pakai & Select2 Enhancement */
.modal-content {
    border: none !important;
    border-radius: 12px !important;
    box-shadow: 0 15px 35px rgba(0,0,0,0.18) !important;
}
.modal-header {
    border-top-left-radius: 12px !important;
    border-top-right-radius: 12px !important;
}
.modal-footer {
    border-bottom-left-radius: 12px !important;
    border-bottom-right-radius: 12px !important;
}

/* Select2 Bootstrap 4 Styling Khusus di Dalam Modal */
.modal .select2-container--bootstrap4 .select2-selection {
    border: 1px solid #ced4da !important;
    border-radius: 6px !important;
    min-height: 40px !important;
    padding: 5px 8px !important;
    background-color: #ffffff !important;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}
.modal .select2-container--bootstrap4.select2-container--focus .select2-selection,
.modal .select2-container--bootstrap4.select2-container--open .select2-selection {
    border-color: #007bff !important;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
}
.modal .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
    line-height: 28px !important;
    padding-left: 4px !important;
    color: #1e293b !important;
    font-size: 0.9rem !important;
}
.modal .select2-container--bootstrap4 .select2-selection--single .select2-selection__placeholder {
    color: #94a3b8 !important;
    font-size: 0.88rem !important;
}
.modal .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
    height: 100% !important;
    top: 0 !important;
    right: 10px !important;
}

/* Select2 Dropdown Layer & Search Field */
.select2-container--open .select2-dropdown {
    border: 1px solid #cbd5e1 !important;
    border-radius: 8px !important;
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.18) !important;
    z-index: 9999 !important;
    overflow: hidden !important;
}
.select2-search--dropdown {
    padding: 8px 10px !important;
    background: #f8fafc !important;
    border-bottom: 1px solid #e2e8f0 !important;
}
.select2-search--dropdown .select2-search__field {
    border: 1px solid #cbd5e1 !important;
    border-radius: 6px !important;
    padding: 7px 12px !important;
    font-size: 0.88rem !important;
    color: #1e293b !important;
    outline: none !important;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
}
.select2-search--dropdown .select2-search__field:focus {
    border-color: #007bff !important;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.15) !important;
}
.select2-results__options {
    max-height: 260px !important;
    padding: 4px 0 !important;
}
.select2-results__option {
    padding: 8px 12px !important;
    font-size: 0.87rem !important;
    line-height: 1.4 !important;
    border-bottom: 1px solid #f8fafc !important;
}
.select2-results__option--highlighted {
    background-color: #007bff !important;
    color: #ffffff !important;
}
.select2-results__option--highlighted .text-dark,
.select2-results__option--highlighted .text-muted,
.select2-results__option--highlighted i {
    color: #ffffff !important;
}
.select2-results__option--highlighted .badge {
    background-color: #ffffff !important;
    color: #007bff !important;
}
</style>
<div class="container-fluid">
<?php
$stats = $stats ?? $summary ?? [];
$cntDipinjam = (int) ($stats['total_dipinjam'] ?? $summary['total_dipinjam'] ?? 0);
$cntDikembalikan = (int) ($stats['total_dikembalikan'] ?? $summary['total_dikembalikan'] ?? 0);
$cntPeminjam = (int) ($stats['total_peminjam'] ?? $stats['total_peminjam_unik'] ?? $summary['total_peminjam'] ?? $summary['total_peminjam_unik'] ?? 0);
$valDipinjam = (float) ($stats['total_nilai_dipinjam'] ?? $summary['total_nilai_dipinjam'] ?? 0);
?>

    <!-- Summary Widgets -->
    <div class="row mb-3">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #ffc107;">
                <span class="info-box-icon bg-warning elevation-1 text-white" style="border-radius: 8px;"><i class="fas fa-hand-holding"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Sedang Dipinjam</span>
                    <span class="info-box-number text-dark" style="font-size: 1.35rem;"><?= number_format($cntDipinjam); ?> Aset</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #28a745;">
                <span class="info-box-icon bg-success elevation-1" style="border-radius: 8px;"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Telah Dikembalikan</span>
                    <span class="info-box-number text-success" style="font-size: 1.35rem;"><?= number_format($cntDikembalikan); ?> Selesai</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #17a2b8;">
                <span class="info-box-icon bg-info elevation-1" style="border-radius: 8px;"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Pegawai Peminjam</span>
                    <span class="info-box-number text-info" style="font-size: 1.35rem;"><?= number_format($cntPeminjam); ?> Orang</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #6c757d;">
                <span class="info-box-icon bg-secondary elevation-1" style="border-radius: 8px;"><i class="fas fa-coins"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Nilai Aset Dipinjam</span>
                    <span class="info-box-number text-dark" style="font-size: 1.15rem;">Rp <?= number_format($valDipinjam, 0, ',', '.'); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Card -->
    <div class="card shadow-sm" style="border: 1px solid #e9eef5; border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white py-3" style="border-bottom: 1px solid #e9eef5;">
            <div class="d-flex flex-wrap justify-content-between align-items-center w-100">
                <div>
                    <h3 class="card-title mb-0 font-weight-bold text-dark" style="font-size: 1.15rem; line-height: 1.8;">
                        <i class="fas fa-hand-holding text-primary mr-2"></i>Daftar Pinjam Pakai Aset BMN
                    </h3>
                    <small class="text-muted d-block mt-1">Pencatatan peminjaman aset BMN untuk dinas pegawai, Surat Izin Pinjam Pakai, dan monitoring pengembalian</small>
                </div>
                <div class="card-tools d-flex align-items-center m-0" style="gap: 8px;">
                    <a href="<?= site_url('admin/inventaris/dbr'); ?>" class="btn btn-outline-secondary btn-sm px-3 shadow-sm" style="border-radius: 6px;">
                        <i class="fas fa-door-open mr-1"></i> Ruangan DBR
                    </a>
                    <a href="<?= site_url('admin/inventaris/satker'); ?>" class="btn btn-outline-secondary btn-sm px-3 shadow-sm" style="border-radius: 6px;">
                        <i class="fas fa-boxes mr-1"></i> Daftar Barang
                    </a>
                    <?php if (! empty($can_export)): ?>
                        <a href="<?= site_url('admin/inventaris/pinjam-pakai/export-excel'); ?>" class="btn btn-success btn-sm px-3 shadow-sm font-weight-bold" style="border-radius: 6px;" title="Export Rekap Pinjam Pakai ke Excel">
                            <i class="fas fa-file-excel mr-1"></i> Export Excel
                        </a>
                    <?php endif; ?>
                    <?php if (! empty($can_add)): ?>
                        <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm font-weight-bold" data-toggle="modal" data-target="#modal-tambah-pinjam" style="border-radius: 6px;">
                            <i class="fas fa-plus mr-1"></i> Pinjamkan Aset
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-striped w-100 js-datatable" style="border-radius: 8px;">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 40px;" class="text-center align-middle">#</th>
                            <th style="width: 170px;" class="align-middle">Surat & Status</th>
                            <th class="align-middle" style="min-width: 220px;">Aset BMN</th>
                            <th class="align-middle" style="min-width: 180px;">Pegawai Peminjam</th>
                            <th class="align-middle" style="min-width: 180px;">Masa Pinjam & Keperluan</th>
                            <th style="width: 180px;" class="text-center align-middle">Aksi & Dokumen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach (($pinjamList ?? []) as $p): ?>
                            <tr>
                                <td class="text-center align-middle"><?= $no++; ?></td>
                                <td class="align-middle">
                                    <span class="font-weight-bold text-dark d-block" style="font-size: 0.95rem;">
                                        <?= ! empty($p['no_surat']) ? esc($p['no_surat']) : '<span class="text-muted font-italic font-weight-normal">(Tanpa No. Surat)</span>'; ?>
                                    </span>
                                    <small class="text-muted d-block mb-1">
                                        <i class="far fa-calendar-alt mr-1"></i> <?= date('d/m/Y', strtotime($p['tgl_pinjam'])); ?>
                                    </small>
                                    <div>
                                        <?php if ($p['status'] === 'dipinjam'): ?>
                                            <span class="badge badge-warning px-2 py-1 font-weight-bold shadow-sm" style="font-size: 0.78rem;">
                                                <i class="fas fa-clock mr-1"></i> Sedang Dipinjam
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-success px-2 py-1 font-weight-bold shadow-sm" style="font-size: 0.78rem;">
                                                <i class="fas fa-check-circle mr-1"></i> Telah Dikembalikan
                                            </span>
                                        <?php endif; ?>

                                        <?php if (! empty($p['file_surat'])): ?>
                                            <a href="<?= base_url(esc($p['file_surat'])); ?>" target="_blank" class="badge badge-danger px-2 py-1 ml-1" title="Lihat Scan Dokumen PDF">
                                                <i class="fas fa-file-pdf mr-1"></i> Scan PDF
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="align-middle">
                                    <span class="font-weight-bold text-primary d-block" style="font-size: 0.95rem;">
                                        <?= esc($p['nama_barang'] ?? '-'); ?>
                                    </span>
                                    <div class="small text-muted mt-1">
                                        <span><strong>NUP:</strong> <?= esc($p['nup'] ?? '-'); ?></span> | 
                                        <span><strong>Kode:</strong> <?= esc($p['kode_barang'] ?? '-'); ?></span>
                                    </div>
                                    <?php if (! empty($p['merk_tipe'])): ?>
                                        <small class="text-muted d-block"><i class="fas fa-tag mr-1"></i> <?= esc($p['merk_tipe']); ?></small>
                                    <?php endif; ?>
                                    <div class="mt-1">
                                        <span class="badge badge-light border px-2 py-1" style="font-size: 0.75rem;">
                                            Kondisi Pinjam: <strong><?= esc(ucwords(str_replace('_', ' ', $p['kondisi_pinjam'] ?? 'baik'))); ?></strong>
                                        </span>
                                        <?php if ($p['status'] === 'dikembalikan' && ! empty($p['kondisi_kembali'])): ?>
                                            <span class="badge badge-light border px-2 py-1 ml-1 text-success" style="font-size: 0.75rem;">
                                                Kembali: <strong><?= esc(ucwords(str_replace('_', ' ', $p['kondisi_kembali']))); ?></strong>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="align-middle">
                                    <span class="font-weight-bold text-dark d-block" style="font-size: 0.95rem;">
                                        <?= esc($p['nama_peminjam']); ?>
                                    </span>
                                    <?php if (! empty($p['nip_peminjam'])): ?>
                                        <small class="text-muted d-block">NIP. <?= esc($p['nip_peminjam']); ?></small>
                                    <?php endif; ?>
                                    <?php if (! empty($p['jabatan_peminjam'])): ?>
                                        <small class="text-muted d-block"><i class="fas fa-briefcase mr-1"></i> <?= esc($p['jabatan_peminjam']); ?></small>
                                    <?php endif; ?>
                                    <?php if (! empty($p['kontak_peminjam'])): ?>
                                        <small class="text-muted d-block"><i class="fas fa-phone mr-1"></i> <?= esc($p['kontak_peminjam']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="align-middle">
                                    <div class="small">
                                        <strong>Rencana Kembali:</strong> 
                                        <?= ! empty($p['tgl_kembali_rencana']) ? date('d/m/Y', strtotime($p['tgl_kembali_rencana'])) : '<span class="text-muted font-italic">Sesuai Kebutuhan</span>'; ?>
                                    </div>
                                    <?php if ($p['status'] === 'dikembalikan' && ! empty($p['tgl_kembali_realisasi'])): ?>
                                        <div class="small text-success font-weight-bold mt-1">
                                            <i class="fas fa-calendar-check mr-1"></i> Kembali: <?= date('d/m/Y', strtotime($p['tgl_kembali_realisasi'])); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="small text-muted mt-1" style="max-width: 250px; white-space: normal;">
                                        <strong>Keperluan:</strong> <?= esc($p['keperluan']); ?>
                                    </div>
                                    <?php if (! empty($p['kelengkapan'])): ?>
                                        <small class="text-secondary d-block font-italic mt-1"><i class="fas fa-paperclip mr-1"></i> <?= esc($p['kelengkapan']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center align-middle" style="white-space: nowrap;">
                                    <div class="btn-group" role="group" style="gap: 4px;">
                                        <!-- Cetak Surat Pinjam PDF -->
                                        <a href="<?= site_url('admin/inventaris/pinjam-pakai/' . $p['id'] . '/cetak-pdf'); ?>" target="_blank" class="btn btn-danger btn-xs px-2 py-1 shadow-sm" style="border-radius: 4px;" title="Cetak Surat Izin Pinjam Pakai BMN (PDF)">
                                            <i class="fas fa-file-pdf mr-1"></i> Cetak PDF
                                        </a>

                                        <!-- Tombol Kembalikan Aset (Hanya jika sedang dipinjam) -->
                                        <?php if ($p['status'] === 'dipinjam' && ! empty($can_edit)): ?>
                                            <button
                                                type="button"
                                                class="btn btn-success btn-xs px-2 py-1 btn-kembalikan-pinjam"
                                                data-toggle="modal"
                                                data-target="#modal-kembalikan-pinjam"
                                                data-id="<?= esc((string) $p['id'], 'attr'); ?>"
                                                data-surat="<?= esc((string) $p['no_surat'], 'attr'); ?>"
                                                data-barang="<?= esc((string) ($p['nama_barang'] ?? ''), 'attr'); ?>"
                                                data-nup="<?= esc((string) ($p['nup'] ?? ''), 'attr'); ?>"
                                                data-peminjam="<?= esc((string) $p['nama_peminjam'], 'attr'); ?>"
                                                data-kondisi="<?= esc((string) ($p['kondisi_pinjam'] ?? 'baik'), 'attr'); ?>"
                                                style="border-radius: 4px;"
                                                title="Proses Pengembalian Aset"
                                            >
                                                <i class="fas fa-undo-alt mr-1"></i> Kembalikan
                                            </button>
                                        <?php endif; ?>

                                        <!-- Edit Pinjam Pakai -->
                                        <?php if (! empty($can_edit)): ?>
                                            <button
                                                type="button"
                                                class="btn btn-outline-primary btn-xs px-2 py-1 btn-edit-pinjam"
                                                data-toggle="modal"
                                                data-target="#modal-edit-pinjam"
                                                data-id="<?= esc((string) $p['id'], 'attr'); ?>"
                                                data-inventaris="<?= esc((string) $p['inventaris_id'], 'attr'); ?>"
                                                data-pegawai="<?= esc((string) ($p['pegawai_id'] ?? ''), 'attr'); ?>"
                                                data-nama="<?= esc((string) $p['nama_peminjam'], 'attr'); ?>"
                                                data-nip="<?= esc((string) ($p['nip_peminjam'] ?? ''), 'attr'); ?>"
                                                data-jabatan="<?= esc((string) ($p['jabatan_peminjam'] ?? ''), 'attr'); ?>"
                                                data-kontak="<?= esc((string) ($p['kontak_peminjam'] ?? ''), 'attr'); ?>"
                                                data-surat="<?= esc((string) $p['no_surat'], 'attr'); ?>"
                                                data-barang="<?= esc((string) ($p['nama_barang'] ?? ''), 'attr'); ?>"
                                                data-nup="<?= esc((string) ($p['nup'] ?? ''), 'attr'); ?>"
                                                data-kode="<?= esc((string) ($p['kode_barang'] ?? ''), 'attr'); ?>"
                                                data-status="<?= esc((string) $p['status'], 'attr'); ?>"
                                                data-tgl-pinjam="<?= esc((string) $p['tgl_pinjam'], 'attr'); ?>"
                                                data-tgl-kembali="<?= esc((string) ($p['tgl_kembali_rencana'] ?? ''), 'attr'); ?>"
                                                data-keperluan="<?= esc((string) $p['keperluan'], 'attr'); ?>"
                                                data-kondisi="<?= esc((string) ($p['kondisi_pinjam'] ?? 'baik'), 'attr'); ?>"
                                                data-kelengkapan="<?= esc((string) ($p['kelengkapan'] ?? ''), 'attr'); ?>"
                                                data-catatan="<?= esc((string) ($p['catatan'] ?? ''), 'attr'); ?>"
                                                style="border-radius: 4px;"
                                                title="Edit Data Pinjam Pakai"
                                            >
                                                <i class="fas fa-pen"></i>
                                            </button>
                                        <?php endif; ?>

                                        <!-- Hapus Pinjam Pakai -->
                                        <?php if (! empty($can_delete)): ?>
                                            <button
                                                type="button"
                                                class="btn btn-outline-danger btn-xs px-2 py-1 btn-delete-pinjam"
                                                data-toggle="modal"
                                                data-target="#modal-delete-pinjam"
                                                data-id="<?= esc((string) $p['id'], 'attr'); ?>"
                                                data-surat="<?= esc((string) $p['no_surat'], 'attr'); ?>"
                                                data-barang="<?= esc((string) ($p['nama_barang'] ?? ''), 'attr'); ?>"
                                                data-nup="<?= esc((string) ($p['nup'] ?? ''), 'attr'); ?>"
                                                data-status="<?= esc((string) $p['status'], 'attr'); ?>"
                                                style="border-radius: 4px;"
                                                title="Hapus Transaksi Pinjam Pakai"
                                            >
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Pinjam Pakai -->
<?php if (! empty($can_add)): ?>
<div class="modal fade" id="modal-tambah-pinjam" role="dialog" aria-labelledby="modalTambahPinjamTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header py-3 px-4" style="background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%); border-bottom: 1px solid #e2e8f0;">
                <div class="d-flex align-items-center">
                    <div class="mr-3 d-flex align-items-center justify-content-center bg-primary text-white rounded-circle shadow-sm" style="width: 42px; height: 42px; font-size: 1.15rem;">
                        <i class="fas fa-hand-holding"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold text-dark mb-0" id="modalTambahPinjamTitle" style="font-size: 1.15rem;">
                            Peminjaman Aset BMN Baru
                        </h5>
                        <small class="text-muted">Pencatatan peminjaman aset dinas yang digunakan pribadi oleh pegawai</small>
                    </div>
                </div>
                <button type="button" class="close text-secondary" data-dismiss="modal" aria-label="Close" style="font-size: 1.5rem; opacity: 0.7;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('admin/inventaris/pinjam-pakai/create'); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="modal-body py-4 px-4">
                    
                    <!-- Section 1: Aset BMN -->
                    <div class="card border mb-3 shadow-none" style="border-radius: 8px; border-color: #e2e8f0; background: #fafbfc;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-3">
                                <span class="badge badge-primary rounded-circle mr-2 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 0.75rem;">1</span>
                                <h6 class="font-weight-bold text-dark mb-0" style="font-size: 0.95rem;">Informasi Aset BMN yang Dipinjamkan</h6>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label class="font-weight-bold small text-dark mb-1">Pilih Aset BMN <span class="text-danger">*</span></label>
                                <select name="inventaris_id" id="tambah-inventaris-select" class="form-control" required style="width: 100%;">
                                    <option value="">-- Pilih atau Cari Aset BMN yang Tersedia --</option>
                                    <?php foreach (($availableAssets ?? []) as $ast): ?>
                                        <option value="<?= esc($ast['id']); ?>">
                                            [NUP <?= esc($ast['nup']); ?>] <?= esc($ast['nama_barang']); ?> <?= ! empty($ast['merk_tipe']) ? ' - ' . esc($ast['merk_tipe']) : ''; ?> (<?= esc($ast['kode_barang']); ?>) - Kondisi: <?= esc(ucwords(str_replace('_', ' ', $ast['kondisi'] ?? 'baik'))); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle mr-1 text-primary"></i>Hanya menampilkan aset aktif yang sedang tidak dipinjam. Cari berdasarkan nama, merk, kode, atau NUP.</small>
                            </div>

                            <div class="form-group mb-0">
                                <label class="font-weight-bold small text-dark mb-1">Kondisi Fisik Saat Dipinjam <span class="text-danger">*</span></label>
                                <select name="kondisi_pinjam" class="form-control" required style="border-radius: 6px; font-size: 0.9rem;">
                                    <option value="baik" selected>Baik</option>
                                    <option value="rusak_ringan">Rusak Ringan</option>
                                    <option value="rusak_berat">Rusak Berat</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Pegawai Peminjam -->
                    <div class="card border mb-3 shadow-none" style="border-radius: 8px; border-color: #e2e8f0; background: #fafbfc;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-primary rounded-circle mr-2 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 0.75rem;">2</span>
                                    <h6 class="font-weight-bold text-dark mb-0" style="font-size: 0.95rem;">Identitas Pegawai Peminjam</h6>
                                </div>
                                <button type="button" class="btn btn-link btn-xs text-danger p-0 font-weight-bold" id="btn-clear-tambah-pegawai" style="font-size: 0.75rem; text-decoration: none;">
                                    <i class="fas fa-undo mr-1"></i> Reset / Input Manual
                                </button>
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold small text-dark mb-1">Pilih dari Master Pegawai <small class="text-muted font-weight-normal">(Otomatis isi nama, NIP, jabatan)</small></label>
                                <select name="pegawai_id" id="tambah-pegawai-select" class="form-control" style="width: 100%;">
                                    <option value="">-- Pilih atau Cari Nama Pegawai --</option>
                                    <?php foreach (($pegawaiList ?? []) as $peg): ?>
                                        <option value="<?= esc($peg['id']); ?>" 
                                            data-nama="<?= esc($peg['nama']); ?>" 
                                            data-nip="<?= esc($peg['nip']); ?>" 
                                            data-jabatan="<?= esc($peg['jabatan'] ?? ''); ?>" 
                                            data-kontak="<?= esc($peg['no_hp'] ?? ''); ?>">
                                            <?= esc($peg['nama']); ?> (NIP. <?= esc($peg['nip'] ?: '-'); ?>) <?= ! empty($peg['jabatan']) ? ' - ' . esc($peg['jabatan']) : ''; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group mb-2">
                                    <label class="font-weight-bold small text-dark mb-1">Nama Lengkap Peminjam <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_peminjam" id="tambah-nama-peminjam" class="form-control" placeholder="Nama lengkap & gelar peminjam" required style="border-radius: 6px; font-size: 0.9rem;">
                                </div>
                                <div class="col-md-6 form-group mb-2">
                                    <label class="font-weight-bold small text-dark mb-1">NIP Peminjam</label>
                                    <input type="text" name="nip_peminjam" id="tambah-nip-peminjam" class="form-control" placeholder="NIP 18 digit (jika PNS/PPPK)" style="border-radius: 6px; font-size: 0.9rem;">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group mb-0">
                                    <label class="font-weight-bold small text-dark mb-1">Jabatan Peminjam</label>
                                    <input type="text" name="jabatan_peminjam" id="tambah-jabatan-peminjam" class="form-control" placeholder="Jabatan kedinasan" style="border-radius: 6px; font-size: 0.9rem;">
                                </div>
                                <div class="col-md-6 form-group mb-0">
                                    <label class="font-weight-bold small text-dark mb-1">No. Handphone / WhatsApp</label>
                                    <input type="text" name="kontak_peminjam" id="tambah-kontak-peminjam" class="form-control" placeholder="Contoh: 081234567890" style="border-radius: 6px; font-size: 0.9rem;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Administrasi Surat & Masa Pinjam -->
                    <div class="card border mb-0 shadow-none" style="border-radius: 8px; border-color: #e2e8f0; background: #fafbfc;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-3">
                                <span class="badge badge-primary rounded-circle mr-2 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 0.75rem;">3</span>
                                <h6 class="font-weight-bold text-dark mb-0" style="font-size: 0.95rem;">Administrasi &amp; Masa Pinjam</h6>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group mb-2">
                                    <label class="font-weight-bold small text-dark mb-1">Nomor Surat Izin Pinjam Pakai <span class="text-muted font-weight-normal">(Opsional)</span></label>
                                    <input type="text" name="no_surat" class="form-control" placeholder="Contoh: 01/SPP/BMN/PPS-RIAU/2026 (Opsional)" style="border-radius: 6px; font-size: 0.9rem;">
                                </div>
                                <div class="col-md-3 form-group mb-2">
                                    <label class="font-weight-bold small text-dark mb-1">Tanggal Pinjam <span class="text-danger">*</span></label>
                                    <input type="date" name="tgl_pinjam" class="form-control" value="<?= date('Y-m-d'); ?>" required style="border-radius: 6px; font-size: 0.9rem;">
                                </div>
                                <div class="col-md-3 form-group mb-2">
                                    <label class="font-weight-bold small text-dark mb-1">Rencana Kembali</label>
                                    <input type="date" name="tgl_kembali_rencana" class="form-control" style="border-radius: 6px; font-size: 0.9rem;">
                                </div>
                            </div>

                            <div class="form-group mb-2">
                                <label class="font-weight-bold small text-dark mb-1">Keperluan Peminjaman <span class="text-danger">*</span></label>
                                <textarea name="keperluan" class="form-control" rows="2" placeholder="Uraikan keperluan kedinasan penggunaan aset BMN ini..." required style="border-radius: 6px; font-size: 0.9rem;"></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group mb-0">
                                    <label class="font-weight-bold small text-dark mb-1">Upload Scan Dokumen Pinjam (PDF)</label>
                                    <input type="file" name="file_surat" class="form-control-file border p-2 w-100 bg-white" accept=".pdf" style="border-radius: 6px; font-size: 0.85rem;">
                                    <small class="text-muted">Format .PDF maks 10MB (opsional, bisa diupload menyusul)</small>
                                </div>
                                <div class="col-md-6 form-group mb-0">
                                    <label class="font-weight-bold small text-dark mb-1">Catatan Tambahan</label>
                                    <textarea name="catatan" class="form-control" rows="2" placeholder="Catatan atau perjanjian khusus..." style="border-radius: 6px; font-size: 0.9rem;"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer py-3 px-4" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
                    <button type="button" class="btn btn-secondary px-3" data-dismiss="modal" style="border-radius: 6px;">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm font-weight-bold" style="border-radius: 6px;">
                        <i class="fas fa-save mr-1"></i> Simpan Peminjaman Aset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Edit Pinjam Pakai -->
<?php if (! empty($can_edit)): ?>
<div class="modal fade" id="modal-edit-pinjam" role="dialog" aria-labelledby="modalEditPinjamTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header py-3 px-4" style="background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%); border-bottom: 1px solid #e2e8f0;">
                <div class="d-flex align-items-center">
                    <div class="mr-3 d-flex align-items-center justify-content-center bg-info text-white rounded-circle shadow-sm" style="width: 42px; height: 42px; font-size: 1.15rem;">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold text-dark mb-0" id="modalEditPinjamTitle" style="font-size: 1.15rem;">
                            Ubah Data Pinjam Pakai
                        </h5>
                        <small class="text-muted">Perbarui data peminjam, nomor surat, tanggal, atau ganti file dokumen scan</small>
                    </div>
                </div>
                <button type="button" class="close text-secondary" data-dismiss="modal" aria-label="Close" style="font-size: 1.5rem; opacity: 0.7;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-edit-pinjam" action="" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="modal-body py-4 px-4">

                    <!-- Info Banner Aset Terkait (Read Only) -->
                    <div class="alert alert-light border d-flex align-items-center mb-3 py-2 px-3 shadow-none" style="border-radius: 8px; background: #f0f7ff; border-left: 4px solid #007bff !important;">
                        <div class="mr-3 text-primary" style="font-size: 1.6rem;">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="small text-muted font-weight-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Aset BMN yang Sedang Dipinjam:</div>
                            <div class="font-weight-bold text-dark" id="edit-aset-nama" style="font-size: 0.98rem;">-</div>
                            <div class="small text-muted mt-1">
                                <span>Kode: <strong id="edit-aset-kode">-</strong></span> | 
                                <span>NUP: <span class="badge badge-primary px-2 py-0" id="edit-aset-nup">-</span></span> | 
                                <span>Status: <span class="badge badge-warning px-2 py-0" id="edit-aset-status">-</span></span>
                            </div>
                        </div>
                    </div>

                    <!-- Section 1: Identitas Pegawai Peminjam -->
                    <div class="card border mb-3 shadow-none" style="border-radius: 8px; border-color: #e2e8f0; background: #fafbfc;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-info rounded-circle mr-2 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 0.75rem;">1</span>
                                    <h6 class="font-weight-bold text-dark mb-0" style="font-size: 0.95rem;">Identitas Pegawai Peminjam</h6>
                                </div>
                                <button type="button" class="btn btn-link btn-xs text-danger p-0 font-weight-bold" id="btn-clear-edit-pegawai" style="font-size: 0.75rem; text-decoration: none;">
                                    <i class="fas fa-undo mr-1"></i> Reset / Input Manual
                                </button>
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold small text-dark mb-1">Pilih dari Master Pegawai <small class="text-muted font-weight-normal">(Otomatis isi nama, NIP, jabatan)</small></label>
                                <select name="pegawai_id" id="edit-pegawai-select" class="form-control" style="width: 100%;">
                                    <option value="">-- Pilih atau Cari Nama Pegawai --</option>
                                    <?php foreach (($pegawaiList ?? []) as $peg): ?>
                                        <option value="<?= esc($peg['id']); ?>" 
                                            data-nama="<?= esc($peg['nama']); ?>" 
                                            data-nip="<?= esc($peg['nip']); ?>" 
                                            data-jabatan="<?= esc($peg['jabatan'] ?? ''); ?>" 
                                            data-kontak="<?= esc($peg['no_hp'] ?? ''); ?>">
                                            <?= esc($peg['nama']); ?> (NIP. <?= esc($peg['nip'] ?: '-'); ?>) <?= ! empty($peg['jabatan']) ? ' - ' . esc($peg['jabatan']) : ''; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group mb-2">
                                    <label class="font-weight-bold small text-dark mb-1">Nama Lengkap Peminjam <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_peminjam" id="edit-nama-peminjam" class="form-control" required style="border-radius: 6px; font-size: 0.9rem;">
                                </div>
                                <div class="col-md-6 form-group mb-2">
                                    <label class="font-weight-bold small text-dark mb-1">NIP Peminjam</label>
                                    <input type="text" name="nip_peminjam" id="edit-nip-peminjam" class="form-control" style="border-radius: 6px; font-size: 0.9rem;">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group mb-0">
                                    <label class="font-weight-bold small text-dark mb-1">Jabatan Peminjam</label>
                                    <input type="text" name="jabatan_peminjam" id="edit-jabatan-peminjam" class="form-control" style="border-radius: 6px; font-size: 0.9rem;">
                                </div>
                                <div class="col-md-6 form-group mb-0">
                                    <label class="font-weight-bold small text-dark mb-1">No. Handphone / WhatsApp</label>
                                    <input type="text" name="kontak_peminjam" id="edit-kontak-peminjam" class="form-control" style="border-radius: 6px; font-size: 0.9rem;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Administrasi Surat & Kondisi -->
                    <div class="card border mb-0 shadow-none" style="border-radius: 8px; border-color: #e2e8f0; background: #fafbfc;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-3">
                                <span class="badge badge-info rounded-circle mr-2 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 0.75rem;">2</span>
                                <h6 class="font-weight-bold text-dark mb-0" style="font-size: 0.95rem;">Dokumen &amp; Kondisi</h6>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group mb-2">
                                    <label class="font-weight-bold small text-dark mb-1">Nomor Surat <span class="text-muted font-weight-normal">(Opsional)</span></label>
                                    <input type="text" name="no_surat" id="edit-no-surat" class="form-control" placeholder="Opsional (boleh dikosongkan)" style="border-radius: 6px; font-size: 0.9rem;">
                                </div>
                                <div class="col-md-3 form-group mb-2">
                                    <label class="font-weight-bold small text-dark mb-1">Tanggal Pinjam <span class="text-danger">*</span></label>
                                    <input type="date" name="tgl_pinjam" id="edit-tgl-pinjam" class="form-control" required style="border-radius: 6px; font-size: 0.9rem;">
                                </div>
                                <div class="col-md-3 form-group mb-2">
                                    <label class="font-weight-bold small text-dark mb-1">Rencana Kembali</label>
                                    <input type="date" name="tgl_kembali_rencana" id="edit-tgl-kembali-rencana" class="form-control" style="border-radius: 6px; font-size: 0.9rem;">
                                </div>
                            </div>

                            <div class="form-group mb-2">
                                <label class="font-weight-bold small text-dark mb-1">Kondisi Fisik Saat Pinjam</label>
                                <select name="kondisi_pinjam" id="edit-kondisi-pinjam" class="form-control" style="border-radius: 6px; font-size: 0.9rem;">
                                    <option value="baik">Baik</option>
                                    <option value="rusak_ringan">Rusak Ringan</option>
                                    <option value="rusak_berat">Rusak Berat</option>
                                </select>
                            </div>

                            <div class="form-group mb-2">
                                <label class="font-weight-bold small text-dark mb-1">Keperluan Peminjaman <span class="text-danger">*</span></label>
                                <textarea name="keperluan" id="edit-keperluan" class="form-control" rows="2" required style="border-radius: 6px; font-size: 0.9rem;"></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group mb-0">
                                    <label class="font-weight-bold small text-dark mb-1">Ganti File Scan Dokumen (PDF)</label>
                                    <input type="file" name="file_surat" class="form-control-file border p-2 w-100 bg-white" accept=".pdf" style="border-radius: 6px; font-size: 0.85rem;">
                                    <small class="text-muted">Biarkan kosong jika tidak mengubah scan PDF</small>
                                </div>
                                <div class="col-md-6 form-group mb-0">
                                    <label class="font-weight-bold small text-dark mb-1">Catatan Tambahan</label>
                                    <textarea name="catatan" id="edit-catatan" class="form-control" rows="2" style="border-radius: 6px; font-size: 0.9rem;"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer py-3 px-4" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
                    <button type="button" class="btn btn-secondary px-3" data-dismiss="modal" style="border-radius: 6px;">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm font-weight-bold" style="border-radius: 6px;">
                        <i class="fas fa-save mr-1"></i> Perbarui Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Kembalikan Aset -->
<?php if (! empty($can_edit)): ?>
<div class="modal fade" id="modal-kembalikan-pinjam" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white py-3 px-4">
                <div class="d-flex align-items-center">
                    <div class="mr-3 d-flex align-items-center justify-content-center bg-white text-success rounded-circle shadow-sm" style="width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="fas fa-undo-alt"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold text-white mb-0" style="font-size: 1.05rem;">
                            Konfirmasi Pengembalian Aset BMN
                        </h5>
                        <small class="text-white-50">Pengembalian aset dinas ke gudang / inventaris induk</small>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-kembalikan-pinjam" action="" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body py-4 px-4">
                    <div class="alert alert-light border mb-3 py-3 px-3 shadow-none" style="border-radius: 8px; background: #f8fafc; border-left: 4px solid #28a745 !important;">
                        <div class="small text-muted font-weight-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Aset yang Dikembalikan:</div>
                        <div class="font-weight-bold text-dark" id="kembali-barang-nama" style="font-size: 0.98rem;">-</div>
                        <div class="small text-muted mt-1">Peminjam: <strong class="text-primary" id="kembali-peminjam-nama">-</strong></div>
                        <div class="small text-muted">No. Surat: <span id="kembali-surat-no">-</span></div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold small text-dark mb-1">Tanggal Realisasi Pengembalian <span class="text-danger">*</span></label>
                        <input type="date" name="tgl_kembali_realisasi" class="form-control" value="<?= date('Y-m-d'); ?>" required style="border-radius: 6px;">
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold small text-dark mb-1">Kondisi Fisik Saat Dikembalikan <span class="text-danger">*</span></label>
                        <select name="kondisi_kembali" id="kembali-kondisi-select" class="form-control" required style="border-radius: 6px;">
                            <option value="baik">Baik</option>
                            <option value="rusak_ringan">Rusak Ringan</option>
                            <option value="rusak_berat">Rusak Berat</option>
                        </select>
                        <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle mr-1 text-success"></i>Kondisi ini otomatis memperbarui fisik barang di database inventaris.</small>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold small text-dark mb-1">Catatan Pengembalian</label>
                        <textarea name="catatan_kembali" class="form-control" rows="2" placeholder="Catatan fisik atau kelengkapan barang saat diserahkan kembali..." style="border-radius: 6px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer py-3 px-4" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
                    <button type="button" class="btn btn-secondary px-3" data-dismiss="modal" style="border-radius: 6px;">Batal</button>
                    <button type="submit" class="btn btn-success px-4 shadow-sm font-weight-bold" style="border-radius: 6px;">
                        <i class="fas fa-check-circle mr-1"></i> Simpan Pengembalian Aset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Hapus Pinjam Pakai -->
<?php if (! empty($can_delete)): ?>
<div class="modal fade" id="modal-delete-pinjam" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white py-3 px-4">
                <div class="d-flex align-items-center">
                    <div class="mr-3 d-flex align-items-center justify-content-center bg-white text-danger rounded-circle shadow-sm" style="width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h5 class="modal-title font-weight-bold text-white mb-0" style="font-size: 1.05rem;">
                        Konfirmasi Hapus Pinjam Pakai
                    </h5>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-delete-pinjam" action="" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body py-4 px-4 text-center">
                    <i class="fas fa-trash-alt text-danger mb-3" style="font-size: 2.8rem;"></i>
                    <p class="mb-1 text-dark font-weight-bold" style="font-size: 1rem;">Apakah Anda yakin ingin menghapus data transaksi peminjaman ini?</p>
                    <p id="delete-pinjam-surat" class="text-primary font-weight-bold mb-2"></p>
                    <div class="alert alert-warning py-2 px-3 small text-left mb-0" style="border-radius: 6px;">
                        <i class="fas fa-info-circle mr-1"></i> Jika status aset masih <strong>dipinjam</strong>, status aset di inventaris induk akan dipulihkan ke <em>'Belum berlokasi'</em> secara otomatis.
                    </div>
                </div>
                <div class="modal-footer py-3 px-4" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
                    <button type="button" class="btn btn-secondary px-3" data-dismiss="modal" style="border-radius: 6px;">Batal</button>
                    <button type="submit" class="btn btn-danger px-4 shadow-sm font-weight-bold" style="border-radius: 6px;">
                        <i class="fas fa-trash-alt mr-1"></i> Ya, Hapus Catatan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. NONAKTIFKAN BOOTSTRAP MODAL ENFORCE FOCUS
    // Supaya search input Select2 di dalam modal selalu dapat diketik secara bebas tanpa terblokir focus trap
    if (typeof $ !== 'undefined' && $.fn.modal && $.fn.modal.Constructor) {
        $.fn.modal.Constructor.prototype._enforceFocus = function() {};
    }

    // 2. TEMPLATE FORMATTER SELECT2
    function formatAssetOption(state) {
        if (!state.id) return state.text;
        var text = state.text;
        var nupMatch = text.match(/\[NUP\s*([^\]]+)\]/i);
        var nup = nupMatch ? nupMatch[1] : '';
        var cleanText = text.replace(/\[NUP\s*[^\]]+\]/i, '').trim();

        return $(
            '<div class="d-flex justify-content-between align-items-center py-1">' +
                '<div class="text-truncate pr-2" style="font-size: 0.88rem; color: #1e293b; font-weight: 600;">' + 
                    $('<div>').text(cleanText).html() + 
                '</div>' +
                (nup ? '<span class="badge badge-primary px-2 py-1 font-weight-bold" style="font-size: 0.74rem; white-space: nowrap;">NUP ' + $('<div>').text(nup).html() + '</span>' : '') +
            '</div>'
        );
    }

    function formatPegawaiOption(state) {
        if (!state.id) return state.text;
        var el = state.element;
        if (!el) return state.text;
        var nama = el.getAttribute('data-nama') || state.text;
        var nip = el.getAttribute('data-nip') || '';
        var jabatan = el.getAttribute('data-jabatan') || '';

        return $(
            '<div class="py-1">' +
                '<div class="font-weight-bold" style="font-size: 0.88rem; color: #1e293b;"><i class="fas fa-user-circle text-primary mr-1"></i> ' + $('<div>').text(nama).html() + '</div>' +
                '<div class="small text-muted mt-0">' +
                    (nip ? '<span>NIP. ' + $('<div>').text(nip).html() + '</span>' : '<span class="font-italic">Tanpa NIP</span>') +
                    (jabatan ? ' &bull; <span>' + $('<div>').text(jabatan).html() + '</span>' : '') +
                '</div>' +
            '</div>'
        );
    }

    // 3. INITIALIZER SELECT2 UNTUK MODAL TAMBAH & EDIT
    function initSelect2ForModals() {
        if (typeof $ === 'undefined' || ! $.fn.select2) return;

        // Modal Tambah: dropdownParent = #modal-tambah-pinjam
        var $modalTambah = $('#modal-tambah-pinjam');
        if ($modalTambah.length) {
            var $selAsset = $('#tambah-inventaris-select');
            if ($selAsset.length && ! $selAsset.data('select2')) {
                $selAsset.select2({
                    theme: 'bootstrap4',
                    dropdownParent: $modalTambah,
                    width: '100%',
                    placeholder: '-- Pilih atau Cari Aset BMN yang Tersedia --',
                    allowClear: true,
                    templateResult: formatAssetOption
                });
            }

            var $selPegTambah = $('#tambah-pegawai-select');
            if ($selPegTambah.length && ! $selPegTambah.data('select2')) {
                $selPegTambah.select2({
                    theme: 'bootstrap4',
                    dropdownParent: $modalTambah,
                    width: '100%',
                    placeholder: '-- Pilih atau Cari Nama Pegawai --',
                    allowClear: true,
                    templateResult: formatPegawaiOption
                });
            }
        }

        // Modal Edit: dropdownParent = #modal-edit-pinjam
        var $modalEdit = $('#modal-edit-pinjam');
        if ($modalEdit.length) {
            var $selPegEdit = $('#edit-pegawai-select');
            if ($selPegEdit.length && ! $selPegEdit.data('select2')) {
                $selPegEdit.select2({
                    theme: 'bootstrap4',
                    dropdownParent: $modalEdit,
                    width: '100%',
                    placeholder: '-- Pilih atau Cari Nama Pegawai --',
                    allowClear: true,
                    templateResult: formatPegawaiOption
                });
            }
        }
    }

    // Inisialisasi awal saat halaman siap
    initSelect2ForModals();

    // Auto-focus search field ketika dropdown Select2 dibuka
    $(document).on('select2:open', function() {
        setTimeout(function() {
            var sf = document.querySelector('.select2-container--open .select2-search__field');
            if (sf) {
                sf.focus();
            }
        }, 50);
    });

    // 4. AUTO-FILL DATA PEMINJAM DARI MASTER PEGAWAI (MODAL TAMBAH)
    var selectTambahPeg = document.getElementById('tambah-pegawai-select');
    if (selectTambahPeg) {
        var handlerTambah = function() {
            var val = selectTambahPeg.value;
            if (val === '') {
                document.getElementById('tambah-nama-peminjam').value = '';
                document.getElementById('tambah-nip-peminjam').value = '';
                document.getElementById('tambah-jabatan-peminjam').value = '';
                document.getElementById('tambah-kontak-peminjam').value = '';
            } else {
                var opt = selectTambahPeg.options[selectTambahPeg.selectedIndex];
                if (opt) {
                    document.getElementById('tambah-nama-peminjam').value = opt.getAttribute('data-nama') || '';
                    document.getElementById('tambah-nip-peminjam').value = opt.getAttribute('data-nip') || '';
                    document.getElementById('tambah-jabatan-peminjam').value = opt.getAttribute('data-jabatan') || '';
                    document.getElementById('tambah-kontak-peminjam').value = opt.getAttribute('data-kontak') || '';
                }
            }
        };

        selectTambahPeg.addEventListener('change', handlerTambah);
        if (typeof $ !== 'undefined') {
            $('#tambah-pegawai-select').on('change', handlerTambah);
        }
    }

    var btnClearTambahPeg = document.getElementById('btn-clear-tambah-pegawai');
    if (btnClearTambahPeg) {
        btnClearTambahPeg.addEventListener('click', function() {
            if (selectTambahPeg) {
                selectTambahPeg.value = '';
                if (typeof $ !== 'undefined') {
                    $('#tambah-pegawai-select').val('').trigger('change');
                }
            }
            document.getElementById('tambah-nama-peminjam').value = '';
            document.getElementById('tambah-nip-peminjam').value = '';
            document.getElementById('tambah-jabatan-peminjam').value = '';
            document.getElementById('tambah-kontak-peminjam').value = '';
        });
    }

    // 5. AUTO-FILL DATA PEMINJAM DARI MASTER PEGAWAI (MODAL EDIT)
    var selectEditPeg = document.getElementById('edit-pegawai-select');
    if (selectEditPeg) {
        var handlerEdit = function() {
            var val = selectEditPeg.value;
            if (val !== '') {
                var opt = selectEditPeg.options[selectEditPeg.selectedIndex];
                if (opt) {
                    document.getElementById('edit-nama-peminjam').value = opt.getAttribute('data-nama') || '';
                    document.getElementById('edit-nip-peminjam').value = opt.getAttribute('data-nip') || '';
                    document.getElementById('edit-jabatan-peminjam').value = opt.getAttribute('data-jabatan') || '';
                    document.getElementById('edit-kontak-peminjam').value = opt.getAttribute('data-kontak') || '';
                }
            }
        };

        selectEditPeg.addEventListener('change', handlerEdit);
        if (typeof $ !== 'undefined') {
            $('#edit-pegawai-select').on('change', handlerEdit);
        }
    }

    var btnClearEditPeg = document.getElementById('btn-clear-edit-pegawai');
    if (btnClearEditPeg) {
        btnClearEditPeg.addEventListener('click', function() {
            if (selectEditPeg) {
                selectEditPeg.value = '';
                if (typeof $ !== 'undefined') {
                    $('#edit-pegawai-select').val('').trigger('change.select2');
                }
            }
            document.getElementById('edit-nama-peminjam').value = '';
            document.getElementById('edit-nip-peminjam').value = '';
            document.getElementById('edit-jabatan-peminjam').value = '';
            document.getElementById('edit-kontak-peminjam').value = '';
        });
    }

    // 6. POPULATE EDIT MODAL
    function populateEditModal(btn) {
        if (!btn) return;
        var id = btn.getAttribute('data-id') || '';
        var form = document.getElementById('form-edit-pinjam');
        if (form && id) {
            form.action = '<?= site_url('admin/inventaris/pinjam-pakai'); ?>/' + id + '/edit';
        }

        // Info Banner Aset Terkait
        var barang = btn.getAttribute('data-barang') || '-';
        var nup = btn.getAttribute('data-nup') || '-';
        var kode = btn.getAttribute('data-kode') || '-';
        var status = btn.getAttribute('data-status') || 'dipinjam';

        var elNama = document.getElementById('edit-aset-nama');
        if (elNama) elNama.textContent = barang;

        var elNup = document.getElementById('edit-aset-nup');
        if (elNup) elNup.textContent = 'NUP ' + nup;

        var elKode = document.getElementById('edit-aset-kode');
        if (elKode) elKode.textContent = kode;

        var elStatus = document.getElementById('edit-aset-status');
        if (elStatus) elStatus.textContent = status === 'dipinjam' ? 'Sedang Dipinjam' : 'Dikembalikan';

        var pegSelect = document.getElementById('edit-pegawai-select');
        var pegId = btn.getAttribute('data-pegawai') || '';
        if (pegSelect) {
            pegSelect.value = pegId;
            if (typeof $ !== 'undefined') {
                $(pegSelect).val(pegId).trigger('change.select2');
            }
        }

        document.getElementById('edit-nama-peminjam').value = btn.getAttribute('data-nama') || '';
        document.getElementById('edit-nip-peminjam').value = btn.getAttribute('data-nip') || '';
        document.getElementById('edit-jabatan-peminjam').value = btn.getAttribute('data-jabatan') || '';
        document.getElementById('edit-kontak-peminjam').value = btn.getAttribute('data-kontak') || '';
        document.getElementById('edit-no-surat').value = btn.getAttribute('data-surat') || '';
        document.getElementById('edit-tgl-pinjam').value = btn.getAttribute('data-tgl-pinjam') || '';
        document.getElementById('edit-tgl-kembali-rencana').value = btn.getAttribute('data-tgl-kembali') || '';
        document.getElementById('edit-keperluan').value = btn.getAttribute('data-keperluan') || '';
        document.getElementById('edit-kondisi-pinjam').value = btn.getAttribute('data-kondisi') || 'baik';
        document.getElementById('edit-catatan').value = btn.getAttribute('data-catatan') || '';
    }

    // 7. POPULATE KEMBALIKAN MODAL
    function populateKembalikanModal(btn) {
        if (!btn) return;
        var id = btn.getAttribute('data-id') || '';
        var form = document.getElementById('form-kembalikan-pinjam');
        if (form && id) {
            form.action = '<?= site_url('admin/inventaris/pinjam-pakai'); ?>/' + id + '/kembalikan';
        }

        var barang = btn.getAttribute('data-barang') || '';
        var nup = btn.getAttribute('data-nup') || '';
        var peminjam = btn.getAttribute('data-peminjam') || '';
        var surat = btn.getAttribute('data-surat') || '';
        var kondisi = btn.getAttribute('data-kondisi') || 'baik';

        var elBarang = document.getElementById('kembali-barang-nama');
        if (elBarang) elBarang.textContent = barang + ' (NUP: ' + nup + ')';

        var elPeminjam = document.getElementById('kembali-peminjam-nama');
        if (elPeminjam) elPeminjam.textContent = peminjam;

        var elSurat = document.getElementById('kembali-surat-no');
        if (elSurat) elSurat.textContent = surat ? surat : '(Tanpa No. Surat)';

        var elKondisi = document.getElementById('kembali-kondisi-select');
        if (elKondisi) elKondisi.value = kondisi;
    }

    // 8. POPULATE DELETE MODAL
    function populateDeleteModal(btn) {
        if (!btn) return;
        var id = btn.getAttribute('data-id') || '';
        var surat = btn.getAttribute('data-surat') || '';
        var barang = btn.getAttribute('data-barang') || '';
        var form = document.getElementById('form-delete-pinjam');
        if (form && id) {
            form.action = '<?= site_url('admin/inventaris/pinjam-pakai'); ?>/' + id + '/delete';
        }

        var suratEl = document.getElementById('delete-pinjam-surat');
        if (suratEl) {
            suratEl.textContent = (surat ? ('Surat: ' + surat) : '(Tanpa No. Surat)') + ' (' + barang + ')';
        }
    }

    // 9. EVENT LISTENERS (DELEGASI UNTUK DATATABLES SORT & PAGING)
    document.addEventListener('click', function(e) {
        var editBtn = e.target.closest('.btn-edit-pinjam');
        if (editBtn) {
            populateEditModal(editBtn);
        }

        var kembaliBtn = e.target.closest('.btn-kembalikan-pinjam');
        if (kembaliBtn) {
            populateKembalikanModal(kembaliBtn);
        }

        var deleteBtn = e.target.closest('.btn-delete-pinjam');
        if (deleteBtn) {
            populateDeleteModal(deleteBtn);
        }
    });

    // 10. MODAL SHOWN EVENT (PASTIKAN SELECT2 TERINISIALISASI SEMPURNA DENGAN 100% WIDTH)
    if (typeof $ !== 'undefined') {
        $('#modal-tambah-pinjam').on('shown.bs.modal', function() {
            initSelect2ForModals();
        });

        $('#modal-edit-pinjam').on('shown.bs.modal', function(e) {
            initSelect2ForModals();
            var btn = e.relatedTarget;
            if (btn) populateEditModal(btn);
        });

        $('#modal-kembalikan-pinjam').on('show.bs.modal', function(e) {
            var btn = e.relatedTarget;
            if (btn) populateKembalikanModal(btn);
        });

        $('#modal-delete-pinjam').on('show.bs.modal', function(e) {
            var btn = e.relatedTarget;
            if (btn) populateDeleteModal(btn);
        });
    }
});
</script>
<?= $this->endSection(); ?>
