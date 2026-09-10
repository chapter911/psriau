<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">

    <!-- Summary Widgets -->
    <div class="row mb-3">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #ffc107;">
                <span class="info-box-icon bg-warning elevation-1 text-white" style="border-radius: 8px;"><i class="fas fa-hand-holding"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Sedang Dipinjam</span>
                    <span class="info-box-number text-dark" style="font-size: 1.35rem;"><?= number_format((int) ($stats['total_dipinjam'] ?? 0)); ?> Aset</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #28a745;">
                <span class="info-box-icon bg-success elevation-1" style="border-radius: 8px;"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Telah Dikembalikan</span>
                    <span class="info-box-number text-success" style="font-size: 1.35rem;"><?= number_format((int) ($stats['total_dikembalikan'] ?? 0)); ?> Selesai</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #17a2b8;">
                <span class="info-box-icon bg-info elevation-1" style="border-radius: 8px;"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Pegawai Peminjam</span>
                    <span class="info-box-number text-info" style="font-size: 1.35rem;"><?= number_format((int) ($stats['total_peminjam'] ?? 0)); ?> Orang</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #6c757d;">
                <span class="info-box-icon bg-secondary elevation-1" style="border-radius: 8px;"><i class="fas fa-coins"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Nilai Aset Dipinjam</span>
                    <span class="info-box-number text-dark" style="font-size: 1.15rem;">Rp <?= number_format((float) ($stats['total_nilai_dipinjam'] ?? 0), 0, ',', '.'); ?></span>
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
                                        <?= esc($p['no_surat']); ?>
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
<div class="modal fade" id="modal-tambah-pinjam" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.12);">
            <div class="modal-header bg-light py-3" style="border-bottom: 1px solid #e9eef5;">
                <h5 class="modal-title font-weight-bold text-dark" style="font-size: 1.1rem;">
                    <i class="fas fa-plus-circle text-primary mr-2"></i>Peminjaman Aset BMN Baru
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('admin/inventaris/pinjam-pakai/create'); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="modal-body py-4">
                    
                    <!-- Section Aset BMN -->
                    <h6 class="font-weight-bold text-primary mb-3 border-bottom pb-2">
                        <i class="fas fa-box-open mr-1"></i> 1. Informasi Aset yang Dipinjamkan
                    </h6>
                    <div class="form-group">
                        <label class="font-weight-bold small text-dark">Pilih Aset BMN <span class="text-danger">*</span></label>
                        <select name="inventaris_id" class="form-control select2" required style="width: 100%; border-radius: 6px;">
                            <option value="">-- Pilih Aset BMN yang Tersedia --</option>
                            <?php foreach (($availableAssets ?? []) as $ast): ?>
                                <option value="<?= esc($ast['id']); ?>">
                                    [NUP <?= esc($ast['nup']); ?>] <?= esc($ast['nama_barang']); ?> <?= ! empty($ast['merk_tipe']) ? ' - ' . esc($ast['merk_tipe']) : ''; ?> (<?= esc($ast['kode_barang']); ?>) - Kondisi: <?= esc(ucwords(str_replace('_', ' ', $ast['kondisi'] ?? 'baik'))); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Aset yang sedang dalam status dipinjam aktif tidak ditampilkan pada daftar ini.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-dark">Kondisi Fisik Saat Dipinjam <span class="text-danger">*</span></label>
                            <select name="kondisi_pinjam" class="form-control" required style="border-radius: 6px;">
                                <option value="baik" selected>Baik</option>
                                <option value="rusak_ringan">Rusak Ringan</option>
                                <option value="rusak_berat">Rusak Berat</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-dark">Kelengkapan / Aksesoris</label>
                            <input type="text" name="kelengkapan" class="form-control" placeholder="Contoh: Charger original, Tas laptop, Mouse wireless" style="border-radius: 6px;">
                        </div>
                    </div>

                    <!-- Section Pegawai Peminjam -->
                    <h6 class="font-weight-bold text-primary mb-3 mt-4 border-bottom pb-2">
                        <i class="fas fa-user mr-1"></i> 2. Identitas Peminjam
                    </h6>
                    <div class="form-group">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="font-weight-bold small text-dark mb-0">Pilih dari Master Pegawai</label>
                            <button type="button" class="btn btn-link btn-xs text-danger p-0" id="btn-clear-tambah-pegawai" style="font-size: 0.75rem; text-decoration: none;">
                                <i class="fas fa-times-circle mr-1"></i> Input Manual / Reset
                            </button>
                        </div>
                        <select name="pegawai_id" id="tambah-pegawai-select" class="form-control select2" style="width: 100%; border-radius: 6px;">
                            <option value="">-- Pilih Pegawai (Otomatis Isi Data) --</option>
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
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-dark">Nama Lengkap Peminjam <span class="text-danger">*</span></label>
                            <input type="text" name="nama_peminjam" id="tambah-nama-peminjam" class="form-control" placeholder="Nama lengkap & gelar" required style="border-radius: 6px;">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-dark">NIP Peminjam</label>
                            <input type="text" name="nip_peminjam" id="tambah-nip-peminjam" class="form-control" placeholder="NIP 18 digit" style="border-radius: 6px;">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-dark">Jabatan Peminjam</label>
                            <input type="text" name="jabatan_peminjam" id="tambah-jabatan-peminjam" class="form-control" placeholder="Jabatan kedinasan" style="border-radius: 6px;">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-dark">No. Handphone / WhatsApp</label>
                            <input type="text" name="kontak_peminjam" id="tambah-kontak-peminjam" class="form-control" placeholder="Contoh: 081234567890" style="border-radius: 6px;">
                        </div>
                    </div>

                    <!-- Section Administrasi Surat -->
                    <h6 class="font-weight-bold text-primary mb-3 mt-4 border-bottom pb-2">
                        <i class="fas fa-file-contract mr-1"></i> 3. Administrasi & Masa Pinjam
                    </h6>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-dark">Nomor Surat Izin Pinjam Pakai <span class="text-danger">*</span></label>
                            <input type="text" name="no_surat" class="form-control" placeholder="Contoh: 01/SPP/BMN/PPS-RIAU/2026" value="SPP/BMN/<?= date('Y/m'); ?>/" required style="border-radius: 6px;">
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="font-weight-bold small text-dark">Tanggal Pinjam <span class="text-danger">*</span></label>
                            <input type="date" name="tgl_pinjam" class="form-control" value="<?= date('Y-m-d'); ?>" required style="border-radius: 6px;">
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="font-weight-bold small text-dark">Rencana Pengembalian</label>
                            <input type="date" name="tgl_kembali_rencana" class="form-control" style="border-radius: 6px;">
                            <small class="text-muted">Kosongkan bila tentatif</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold small text-dark">Keperluan Peminjaman <span class="text-danger">*</span></label>
                        <textarea name="keperluan" class="form-control" rows="2" placeholder="Contoh: Digunakan untuk tugas kedinasan pengawasan lapangan dan operasional harian Satker PPS Riau..." required style="border-radius: 6px;"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-dark">Upload Scan Dokumen Pinjam (PDF)</label>
                            <input type="file" name="file_surat" class="form-control-file border p-1 w-100" accept=".pdf" style="border-radius: 6px;">
                            <small class="text-muted">Format .PDF maks 10MB (opsional, bisa diupload menyusul)</small>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-dark">Catatan Tambahan</label>
                            <textarea name="catatan" class="form-control" rows="2" placeholder="Catatan atau perjanjian khusus..." style="border-radius: 6px;"></textarea>
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-light py-3" style="border-top: 1px solid #e9eef5;">
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
<div class="modal fade" id="modal-edit-pinjam" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.12);">
            <div class="modal-header bg-light py-3" style="border-bottom: 1px solid #e9eef5;">
                <h5 class="modal-title font-weight-bold text-dark" style="font-size: 1.1rem;">
                    <i class="fas fa-edit text-primary mr-2"></i>Ubah Data Pinjam Pakai
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-edit-pinjam" action="" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="modal-body py-4">

                    <h6 class="font-weight-bold text-primary mb-3 border-bottom pb-2">
                        <i class="fas fa-user mr-1"></i> 1. Identitas Peminjam
                    </h6>
                    <div class="form-group">
                        <label class="font-weight-bold small text-dark mb-1">Pilih dari Master Pegawai</label>
                        <select name="pegawai_id" id="edit-pegawai-select" class="form-control" style="border-radius: 6px;">
                            <option value="">-- Pilih Pegawai --</option>
                            <?php foreach (($pegawaiList ?? []) as $peg): ?>
                                <option value="<?= esc($peg['id']); ?>" 
                                    data-nama="<?= esc($peg['nama']); ?>" 
                                    data-nip="<?= esc($peg['nip']); ?>" 
                                    data-jabatan="<?= esc($peg['jabatan'] ?? ''); ?>" 
                                    data-kontak="<?= esc($peg['no_hp'] ?? ''); ?>">
                                    <?= esc($peg['nama']); ?> (NIP. <?= esc($peg['nip'] ?: '-'); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-dark">Nama Lengkap Peminjam <span class="text-danger">*</span></label>
                            <input type="text" name="nama_peminjam" id="edit-nama-peminjam" class="form-control" required style="border-radius: 6px;">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-dark">NIP Peminjam</label>
                            <input type="text" name="nip_peminjam" id="edit-nip-peminjam" class="form-control" style="border-radius: 6px;">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-dark">Jabatan Peminjam</label>
                            <input type="text" name="jabatan_peminjam" id="edit-jabatan-peminjam" class="form-control" style="border-radius: 6px;">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-dark">No. Handphone / WhatsApp</label>
                            <input type="text" name="kontak_peminjam" id="edit-kontak-peminjam" class="form-control" style="border-radius: 6px;">
                        </div>
                    </div>

                    <h6 class="font-weight-bold text-primary mb-3 mt-4 border-bottom pb-2">
                        <i class="fas fa-file-contract mr-1"></i> 2. Dokumen & Kondisi
                    </h6>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-dark">Nomor Surat <span class="text-danger">*</span></label>
                            <input type="text" name="no_surat" id="edit-no-surat" class="form-control" required style="border-radius: 6px;">
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="font-weight-bold small text-dark">Tanggal Pinjam <span class="text-danger">*</span></label>
                            <input type="date" name="tgl_pinjam" id="edit-tgl-pinjam" class="form-control" required style="border-radius: 6px;">
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="font-weight-bold small text-dark">Rencana Kembali</label>
                            <input type="date" name="tgl_kembali_rencana" id="edit-tgl-kembali-rencana" class="form-control" style="border-radius: 6px;">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-dark">Kondisi Fisik Saat Pinjam</label>
                            <select name="kondisi_pinjam" id="edit-kondisi-pinjam" class="form-control" style="border-radius: 6px;">
                                <option value="baik">Baik</option>
                                <option value="rusak_ringan">Rusak Ringan</option>
                                <option value="rusak_berat">Rusak Berat</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-dark">Kelengkapan / Aksesoris</label>
                            <input type="text" name="kelengkapan" id="edit-kelengkapan" class="form-control" style="border-radius: 6px;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold small text-dark">Keperluan Peminjaman <span class="text-danger">*</span></label>
                        <textarea name="keperluan" id="edit-keperluan" class="form-control" rows="2" required style="border-radius: 6px;"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-dark">Ganti File Scan Dokumen (PDF)</label>
                            <input type="file" name="file_surat" class="form-control-file border p-1 w-100" accept=".pdf" style="border-radius: 6px;">
                            <small class="text-muted">Biarkan kosong jika tidak mengubah scan PDF</small>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-dark">Catatan Tambahan</label>
                            <textarea name="catatan" id="edit-catatan" class="form-control" rows="2" style="border-radius: 6px;"></textarea>
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-light py-3" style="border-top: 1px solid #e9eef5;">
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
<div class="modal fade" id="modal-kembalikan-pinjam" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.12);">
            <div class="modal-header bg-success text-white py-3">
                <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
                    <i class="fas fa-undo-alt mr-2"></i>Konfirmasi Pengembalian Aset BMN
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-kembalikan-pinjam" action="" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body py-4">
                    <div class="alert alert-light border mb-3">
                        <div class="small text-muted">Aset BMN:</div>
                        <div class="font-weight-bold text-dark" id="kembali-barang-nama">-</div>
                        <div class="small text-muted mt-1">Peminjam: <strong class="text-primary" id="kembali-peminjam-nama">-</strong></div>
                        <div class="small text-muted">No. Surat: <span id="kembali-surat-no">-</span></div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold small text-dark">Tanggal Realisasi Pengembalian <span class="text-danger">*</span></label>
                        <input type="date" name="tgl_kembali_realisasi" class="form-control" value="<?= date('Y-m-d'); ?>" required style="border-radius: 6px;">
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold small text-dark">Kondisi Fisik Saat Dikembalikan <span class="text-danger">*</span></label>
                        <select name="kondisi_kembali" id="kembali-kondisi-select" class="form-control" required style="border-radius: 6px;">
                            <option value="baik">Baik</option>
                            <option value="rusak_ringan">Rusak Ringan</option>
                            <option value="rusak_berat">Rusak Berat</option>
                        </select>
                        <small class="text-muted">Kondisi ini akan memperbarui status fisik barang pada data inventaris induk.</small>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold small text-dark">Catatan Pengembalian</label>
                        <textarea name="catatan_kembali" class="form-control" rows="2" placeholder="Kondisi kelengkapan barang yang dikembalikan..." style="border-radius: 6px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-3" style="border-top: 1px solid #e9eef5;">
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
<div class="modal fade" id="modal-delete-pinjam" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.12);">
            <div class="modal-header bg-danger text-white py-3">
                <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Konfirmasi Hapus Pinjam Pakai
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-delete-pinjam" action="" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body py-4 text-center">
                    <i class="fas fa-trash-alt text-danger mb-3" style="font-size: 3rem;"></i>
                    <p class="mb-1 text-dark font-weight-bold">Apakah Anda yakin ingin menghapus catatan transaksi peminjaman ini?</p>
                    <p id="delete-pinjam-surat" class="text-primary font-weight-bold mb-2"></p>
                    <small class="text-muted d-block" id="delete-pinjam-warning">Jika aset masih berstatus dipinjam, status aset akan otomatis dikembalikan ke 'Belum berlokasi'.</small>
                </div>
                <div class="modal-footer bg-light py-3" style="border-top: 1px solid #e9eef5;">
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
    // Inisialisasi Select2 jika tersedia
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('.select2').select2({
            theme: 'bootstrap4',
            dropdownParent: $('body')
        });
    }

    // Auto-fill Data Peminjam dari Master Pegawai (Modal Tambah)
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

    // Auto-fill Data Peminjam dari Master Pegawai (Modal Edit)
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

    // Populate Edit Modal
    function populateEditModal(btn) {
        if (!btn) return;
        var id = btn.getAttribute('data-id') || '';
        var form = document.getElementById('form-edit-pinjam');
        if (form && id) {
            form.action = '<?= site_url('admin/inventaris/pinjam-pakai'); ?>/' + id + '/edit';
        }

        var pegSelect = document.getElementById('edit-pegawai-select');
        var pegId = btn.getAttribute('data-pegawai') || '';
        if (pegSelect) {
            pegSelect.value = pegId;
            if (typeof $ !== 'undefined') {
                $(pegSelect).trigger('change.select2');
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
        document.getElementById('edit-kelengkapan').value = btn.getAttribute('data-kelengkapan') || '';
        document.getElementById('edit-catatan').value = btn.getAttribute('data-catatan') || '';
    }

    // Populate Kembalikan Modal
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
        if (elSurat) elSurat.textContent = surat;

        var elKondisi = document.getElementById('kembali-kondisi-select');
        if (elKondisi) elKondisi.value = kondisi;
    }

    // Populate Delete Modal
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
            suratEl.textContent = 'Surat: ' + surat + ' (' + barang + ')';
        }
    }

    // Delegated click listeners (berfungsi optimal meskipun DataTables melakukan sorting, filter, paging)
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

    // Bootstrap modal show event hooks
    if (typeof $ !== 'undefined') {
        $('#modal-edit-pinjam').on('show.bs.modal', function(e) {
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
