<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="content-header pb-2">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-7">
                <h1 class="m-0 text-dark font-weight-bold" style="font-size: 1.45rem;">
                    <i class="fas fa-camera text-info mr-2"></i>Kelola Lampiran Foto Pemeliharaan Aset
                </h1>
                <p class="text-muted small mb-0 mt-1">
                    Input bertahap foto fisik 4 sudut (Depan, Atas, Bawah, Belakang) untuk Surat No: <strong><?= esc($pemeliharaan['no_surat']); ?></strong>
                </p>
            </div>
            <div class="col-sm-5 text-sm-right mt-2 mt-sm-0">
                <a href="<?= site_url('admin/inventaris/pemeliharaan'); ?>" class="btn btn-outline-secondary btn-sm shadow-sm mr-2" style="border-radius: 6px;">
                    <i class="fas fa-arrow-left mr-1"></i>Kembali ke Arsip
                </a>
                <?php if (! empty($permissions['can_export'])): ?>
                    <a href="<?= site_url('admin/inventaris/pemeliharaan/' . $pemeliharaan['id'] . '/cetak-pdf'); ?>" target="_blank" class="btn btn-danger btn-sm shadow-sm font-weight-bold" style="border-radius: 6px;" title="Pratinjau Cetak PDF Lengkap">
                        <i class="fas fa-file-pdf mr-1"></i>Lihat PDF
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- KARTU RINGKASAN SURAT & PROGRESS KELENGKAPAN FOTO -->
        <div class="card shadow-sm border-0 mb-3" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
                    <div>
                        <span class="badge badge-primary px-2.5 py-1 mb-1" style="font-size: 0.75rem;">
                            <i class="fas fa-tag mr-1"></i><?= esc($pemeliharaan['jenis_pemeliharaan']); ?>
                        </span>
                        <h5 class="font-weight-bold text-dark mb-0">
                            <?= esc($pemeliharaan['judul_surat']); ?>
                        </h5>
                        <div class="small text-muted mt-0.5">
                            Nomor: <strong class="text-dark"><?= esc($pemeliharaan['no_surat']); ?></strong> &bull; 
                            Tanggal: <strong class="text-dark"><?= date('d F Y', strtotime($pemeliharaan['tanggal_surat'])); ?></strong> &bull; 
                            Kasatker KPB: <strong class="text-dark"><?= esc($pemeliharaan['nama_kasatker']); ?></strong>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="badge badge-<?= $stats['status_color']; ?> px-3 py-2 font-weight-bold shadow-sm" id="badgeOverallStatus" style="font-size: 0.85rem; border-radius: 8px;">
                            <i class="fas fa-camera mr-1.5"></i><span id="labelOverallStatus"><?= esc($stats['status_label']); ?></span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body p-3 bg-light">
                <div class="row align-items-center">
                    <div class="col-md-3 col-6 text-center border-right mb-2 mb-md-0">
                        <div class="text-muted small">Kelompok Barang</div>
                        <div class="h5 font-weight-bold text-dark mb-0 mt-0.5">
                            <?= number_format($stats['total_groups']); ?> <small class="text-muted font-weight-normal">Kelompok</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 text-center border-right mb-2 mb-md-0">
                        <div class="text-muted small">Total Foto Dibutuhkan</div>
                        <div class="h5 font-weight-bold text-dark mb-0 mt-0.5" id="statTotalSlots">
                            <?= number_format($stats['total_slots']); ?> <small class="text-muted font-weight-normal">Slot Foto</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 text-center border-right">
                        <div class="text-muted small">Foto Sudah Terisi</div>
                        <div class="h5 font-weight-bold text-success mb-0 mt-0.5" id="statFilledSlots">
                            <?= number_format($stats['filled_slots']); ?> <small class="text-success font-weight-normal">Foto</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 text-center">
                        <div class="text-muted small">Foto Belum Terisi</div>
                        <div class="h5 font-weight-bold text-danger mb-0 mt-0.5" id="statEmptySlots">
                            <?= number_format($stats['empty_slots']); ?> <small class="text-danger font-weight-normal">Foto</small>
                        </div>
                    </div>
                </div>

                <div class="mt-3 pt-2 border-top">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="font-weight-bold text-muted">Progress Kelengkapan Foto Fisik:</small>
                        <small class="font-weight-bold text-primary" id="progressPercentageText"><?= $stats['percentage']; ?>% Selesai</small>
                    </div>
                    <div class="progress" style="height: 10px; border-radius: 6px; background-color: #e2e8f0;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-<?= $stats['status_color'] === 'success' ? 'success' : 'primary'; ?>" id="mainProgressBar" role="progressbar" style="width: <?= $stats['percentage']; ?>%;" aria-valuenow="<?= $stats['percentage']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PANDUAN WARNA & INDIKATOR TOMBOL ICON -->
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap: 10px;">
            <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                <span class="small font-weight-bold text-dark mr-1">Petunjuk Status:</span>
                <span class="badge badge-success px-2.5 py-1.5 shadow-sm font-weight-bold" style="font-size: 0.76rem;">
                    <i class="fas fa-check-circle mr-1"></i>Hijau = Foto Tersedia (Klik untuk Pratinjau &amp; Update)
                </span>
                <span class="badge badge-danger px-2.5 py-1.5 shadow-sm font-weight-bold" style="font-size: 0.76rem;">
                    <i class="fas fa-camera mr-1"></i>Merah = Belum Ada Foto (Klik untuk Ambil / Upload Foto)
                </span>
            </div>
            <div class="small text-muted">
                <i class="fas fa-info-circle mr-1 text-info"></i>Setiap foto tersimpan otomatis secara instan.
            </div>
        </div>

        <!-- TABEL KELOMPOK BARANG & 4 TOMBOL ICON SUDUT FOTO -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0" id="tableFotoPemeliharaan" style="font-size: 0.88rem;">
                        <thead class="thead-light text-center">
                            <tr>
                                <th style="width: 40px;" class="align-middle">No</th>
                                <th class="align-middle text-left">Nama Barang</th>
                                <th style="width: 140px;" class="align-middle">Kategori</th>
                                <th style="width: 160px;" class="align-middle text-left">Merek / Tipe / Spesifikasi</th>
                                <th style="width: 110px;" class="align-middle">Rentang NUP</th>
                                <th style="width: 68px;" class="align-middle" title="Tampak Depan">Depan</th>
                                <th style="width: 68px;" class="align-middle" title="Tampak Atas">Atas</th>
                                <th style="width: 68px;" class="align-middle" title="Tampak Bawah">Bawah</th>
                                <th style="width: 68px;" class="align-middle" title="Tampak Belakang">Belakang</th>
                                <th style="width: 135px;" class="align-middle">Kelengkapan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($fotos)): ?>
                                <tr>
                                    <td colspan="10" class="text-center py-5 text-muted">
                                        <i class="fas fa-boxes fa-3x mb-2 text-secondary" style="opacity: 0.3;"></i>
                                        <div class="font-weight-bold">Tidak Ada Barang pada Surat Ini</div>
                                        <small>Pastikan surat sudah memiliki daftar barang di langkah input surat.</small>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php
                                    $angleLabels = [
                                        'depan'    => 'Tampak Depan',
                                        'atas'     => 'Tampak Atas',
                                        'bawah'    => 'Tampak Bawah',
                                        'belakang' => 'Tampak Belakang',
                                    ];
                                ?>
                                <?php foreach ($fotos as $idx => $f): ?>
                                    <?php
                                        $fId = (int) $f['id'];
                                        $kKey = $f['kelompok_key'];
                                        $gFilled = (int) ($f['group_filled'] ?? 0);
                                        $gStatus = $f['group_status'] ?? 'danger';
                                    ?>
                                    <tr id="rowGroup_<?= $fId; ?>">
                                        <!-- 1. No -->
                                        <td class="text-center text-muted font-weight-bold align-middle">
                                            <?= $idx + 1; ?>
                                        </td>

                                        <!-- 2. Nama Barang -->
                                        <td class="align-middle">
                                            <div class="font-weight-bold text-dark">
                                                <?= esc($f['nama_barang']); ?>
                                            </div>
                                            <?php if (! empty($f['kode_barang'])): ?>
                                                <small class="text-muted font-weight-bold">
                                                    <code><?= esc($f['kode_barang']); ?></code>
                                                </small>
                                            <?php endif; ?>
                                        </td>

                                        <!-- 3. Kategori -->
                                        <td class="text-center align-middle">
                                            <span class="badge badge-light border text-dark font-weight-bold px-2 py-1" style="font-size: 0.74rem;">
                                                <?= esc($f['kategori'] ?? 'MESIN PERALATAN NON TIK'); ?>
                                            </span>
                                        </td>

                                        <!-- 4. Merek / Tipe / Spesifikasi -->
                                        <td class="align-middle text-secondary small">
                                            <?= esc($f['merk_tipe'] ?: '-'); ?>
                                        </td>

                                        <!-- 5. Rentang NUP -->
                                        <td class="text-center align-middle">
                                            <span class="badge badge-light border text-primary font-weight-bold px-2 py-1" style="font-size: 0.76rem;">
                                                <?= esc($f['nup_gabungan'] ?: '-'); ?>
                                            </span>
                                        </td>

                                        <!-- 6. Tampak Depan -->
                                        <?php
                                            $pathDepan = $f['foto_depan'] ?? null;
                                            $hasDepan = ! empty($pathDepan) && is_file(FCPATH . ltrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $pathDepan), DIRECTORY_SEPARATOR));
                                        ?>
                                        <td class="text-center align-middle" id="cellSlot_<?= $fId; ?>_depan">
                                            <?php if ($hasDepan): ?>
                                                <button type="button" class="btn btn-success btn-sm btn-slot-photo shadow-sm"
                                                    id="btnSlot_<?= $fId; ?>_depan"
                                                    data-foto-id="<?= $fId; ?>"
                                                    data-pos="depan"
                                                    data-status="filled"
                                                    data-src="<?= base_url($pathDepan); ?>"
                                                    data-nama="<?= esc($f['nama_barang']); ?>"
                                                    data-nup="<?= esc($f['nup_gabungan']); ?>"
                                                    data-label="Tampak Depan"
                                                    title="Tampak Depan: Foto Tersedia (Klik untuk Pratinjau & Update)"
                                                    style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px;">
                                                    <i class="fas fa-check-circle" style="font-size: 1.1rem;"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-outline-danger btn-sm btn-slot-photo shadow-sm"
                                                    id="btnSlot_<?= $fId; ?>_depan"
                                                    data-foto-id="<?= $fId; ?>"
                                                    data-pos="depan"
                                                    data-status="empty"
                                                    data-src=""
                                                    data-nama="<?= esc($f['nama_barang']); ?>"
                                                    data-nup="<?= esc($f['nup_gabungan']); ?>"
                                                    data-label="Tampak Depan"
                                                    title="Tampak Depan: Belum Ada Foto (Klik untuk Ambil / Upload Foto)"
                                                    style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px;">
                                                    <i class="fas fa-camera text-danger" style="font-size: 1.05rem;"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>

                                        <!-- 7. Tampak Atas -->
                                        <?php
                                            $pathAtas = $f['foto_atas'] ?? null;
                                            $hasAtas = ! empty($pathAtas) && is_file(FCPATH . ltrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $pathAtas), DIRECTORY_SEPARATOR));
                                        ?>
                                        <td class="text-center align-middle" id="cellSlot_<?= $fId; ?>_atas">
                                            <?php if ($hasAtas): ?>
                                                <button type="button" class="btn btn-success btn-sm btn-slot-photo shadow-sm"
                                                    id="btnSlot_<?= $fId; ?>_atas"
                                                    data-foto-id="<?= $fId; ?>"
                                                    data-pos="atas"
                                                    data-status="filled"
                                                    data-src="<?= base_url($pathAtas); ?>"
                                                    data-nama="<?= esc($f['nama_barang']); ?>"
                                                    data-nup="<?= esc($f['nup_gabungan']); ?>"
                                                    data-label="Tampak Atas"
                                                    title="Tampak Atas: Foto Tersedia (Klik untuk Pratinjau & Update)"
                                                    style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px;">
                                                    <i class="fas fa-check-circle" style="font-size: 1.1rem;"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-outline-danger btn-sm btn-slot-photo shadow-sm"
                                                    id="btnSlot_<?= $fId; ?>_atas"
                                                    data-foto-id="<?= $fId; ?>"
                                                    data-pos="atas"
                                                    data-status="empty"
                                                    data-src=""
                                                    data-nama="<?= esc($f['nama_barang']); ?>"
                                                    data-nup="<?= esc($f['nup_gabungan']); ?>"
                                                    data-label="Tampak Atas"
                                                    title="Tampak Atas: Belum Ada Foto (Klik untuk Ambil / Upload Foto)"
                                                    style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px;">
                                                    <i class="fas fa-camera text-danger" style="font-size: 1.05rem;"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>

                                        <!-- 8. Tampak Bawah -->
                                        <?php
                                            $pathBawah = $f['foto_bawah'] ?? null;
                                            $hasBawah = ! empty($pathBawah) && is_file(FCPATH . ltrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $pathBawah), DIRECTORY_SEPARATOR));
                                        ?>
                                        <td class="text-center align-middle" id="cellSlot_<?= $fId; ?>_bawah">
                                            <?php if ($hasBawah): ?>
                                                <button type="button" class="btn btn-success btn-sm btn-slot-photo shadow-sm"
                                                    id="btnSlot_<?= $fId; ?>_bawah"
                                                    data-foto-id="<?= $fId; ?>"
                                                    data-pos="bawah"
                                                    data-status="filled"
                                                    data-src="<?= base_url($pathBawah); ?>"
                                                    data-nama="<?= esc($f['nama_barang']); ?>"
                                                    data-nup="<?= esc($f['nup_gabungan']); ?>"
                                                    data-label="Tampak Bawah"
                                                    title="Tampak Bawah: Foto Tersedia (Klik untuk Pratinjau & Update)"
                                                    style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px;">
                                                    <i class="fas fa-check-circle" style="font-size: 1.1rem;"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-outline-danger btn-sm btn-slot-photo shadow-sm"
                                                    id="btnSlot_<?= $fId; ?>_bawah"
                                                    data-foto-id="<?= $fId; ?>"
                                                    data-pos="bawah"
                                                    data-status="empty"
                                                    data-src=""
                                                    data-nama="<?= esc($f['nama_barang']); ?>"
                                                    data-nup="<?= esc($f['nup_gabungan']); ?>"
                                                    data-label="Tampak Bawah"
                                                    title="Tampak Bawah: Belum Ada Foto (Klik untuk Ambil / Upload Foto)"
                                                    style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px;">
                                                    <i class="fas fa-camera text-danger" style="font-size: 1.05rem;"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>

                                        <!-- 9. Tampak Belakang -->
                                        <?php
                                            $pathBelakang = $f['foto_belakang'] ?? null;
                                            $hasBelakang = ! empty($pathBelakang) && is_file(FCPATH . ltrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $pathBelakang), DIRECTORY_SEPARATOR));
                                        ?>
                                        <td class="text-center align-middle" id="cellSlot_<?= $fId; ?>_belakang">
                                            <?php if ($hasBelakang): ?>
                                                <button type="button" class="btn btn-success btn-sm btn-slot-photo shadow-sm"
                                                    id="btnSlot_<?= $fId; ?>_belakang"
                                                    data-foto-id="<?= $fId; ?>"
                                                    data-pos="belakang"
                                                    data-status="filled"
                                                    data-src="<?= base_url($pathBelakang); ?>"
                                                    data-nama="<?= esc($f['nama_barang']); ?>"
                                                    data-nup="<?= esc($f['nup_gabungan']); ?>"
                                                    data-label="Tampak Belakang"
                                                    title="Tampak Belakang: Foto Tersedia (Klik untuk Pratinjau & Update)"
                                                    style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px;">
                                                    <i class="fas fa-check-circle" style="font-size: 1.1rem;"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-outline-danger btn-sm btn-slot-photo shadow-sm"
                                                    id="btnSlot_<?= $fId; ?>_belakang"
                                                    data-foto-id="<?= $fId; ?>"
                                                    data-pos="belakang"
                                                    data-status="empty"
                                                    data-src=""
                                                    data-nama="<?= esc($f['nama_barang']); ?>"
                                                    data-nup="<?= esc($f['nup_gabungan']); ?>"
                                                    data-label="Tampak Belakang"
                                                    title="Tampak Belakang: Belum Ada Foto (Klik untuk Ambil / Upload Foto)"
                                                    style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px;">
                                                    <i class="fas fa-camera text-danger" style="font-size: 1.05rem;"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>

                                        <!-- 10. Kelengkapan & Tombol Wizard 4 Sudut -->
                                        <td class="text-center align-middle">
                                            <div class="d-flex flex-column align-items-center" style="gap: 4px;">
                                                <span class="badge badge-<?= $gStatus; ?> px-2 py-1 font-weight-bold group-status-badge" id="groupBadge_<?= $fId; ?>" style="font-size: 0.74rem;">
                                                    <?= $gFilled; ?> / 4 Terisi
                                                </span>
                                                <button type="button" class="btn btn-outline-primary btn-xs font-weight-bold rounded-pill px-2 py-0.5 btn-start-group-wizard shadow-sm"
                                                    data-foto-id="<?= $fId; ?>"
                                                    data-key="<?= esc($kKey); ?>"
                                                    data-nama="<?= esc($f['nama_barang']); ?>"
                                                    data-nup="<?= esc($f['nup_gabungan']); ?>"
                                                    title="Jepret 4 Sudut Berurutan (Wizard)">
                                                    <i class="fas fa-camera mr-0.5"></i>4 Sudut
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-5 text-center p-3 bg-white" style="border-radius: 12px;">
            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
                <div class="text-left">
                    <div class="font-weight-bold text-dark">Seluruh foto otomatis tersimpan real-time</div>
                    <small class="text-muted">Anda dapat meninggalkan halaman ini dan melanjutkan pengambilan foto di lain waktu.</small>
                </div>
                <div>
                    <a href="<?= site_url('admin/inventaris/pemeliharaan'); ?>" class="btn btn-outline-secondary btn-sm px-4 rounded-pill mr-2">
                        <i class="fas fa-check mr-1"></i>Selesai &amp; Kembali ke Arsip
                    </a>
                    <?php if (! empty($permissions['can_export'])): ?>
                        <a href="<?= site_url('admin/inventaris/pemeliharaan/' . $pemeliharaan['id'] . '/cetak-pdf'); ?>" target="_blank" class="btn btn-danger btn-sm px-4 rounded-pill font-weight-bold shadow-sm">
                            <i class="fas fa-file-pdf mr-1"></i>Cetak Dokumen PDF
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- MODAL 1: PRATINJAU FOTO & TOMBOL UPDATE (UNTUK TOMBOL HIJAU) -->
<div class="modal fade" id="modalPhotoPreview" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header bg-success text-white py-2.5 px-3 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="modal-title font-weight-bold mb-0" id="previewModalTitle">Pratinjau Foto</h6>
                    <small class="text-white opacity-9" id="previewModalSubtitle">-</small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3 text-center bg-dark position-relative">
                <div class="position-relative d-inline-block" style="max-height: 400px; width: 100%;">
                    <img id="previewModalImg" src="" style="max-height: 380px; max-width: 100%; object-fit: contain; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);" alt="Foto Lampiran">
                    <span class="badge badge-warning text-dark font-weight-bold shadow" style="position: absolute; bottom: 8px; right: 8px; font-size: 0.72rem;">
                        <i class="fas fa-certificate mr-1"></i>TimeMark Resmi PU
                    </span>
                </div>
            </div>
            <div class="modal-footer bg-light py-2.5 px-3 d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">
                <button type="button" class="btn btn-outline-danger btn-sm" id="btnPreviewDelete" title="Hapus foto ini">
                    <i class="fas fa-trash-alt mr-1"></i>Hapus Foto
                </button>
                <div class="d-flex align-items-center" style="gap: 6px;">
                    <button type="button" class="btn btn-outline-primary btn-sm font-weight-bold" id="btnPreviewRetakeCamera">
                        <i class="fas fa-camera mr-1"></i>Foto Ulang Kamera
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm font-weight-bold" id="btnPreviewUploadNew">
                        <i class="fas fa-upload mr-1"></i>Unggah File Baru
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 2: PILIHAN INPUT FOTO (UNTUK TOMBOL MERAH) -->
<div class="modal fade" id="modalEmptyAction" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header bg-primary text-white py-2.5 px-3">
                <div>
                    <h6 class="modal-title font-weight-bold mb-0" id="emptyActionTitle">Ambil / Unggah Foto</h6>
                    <small class="text-white opacity-9" id="emptyActionSubtitle">-</small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                <p class="small text-muted mb-3 text-center">Pilih metode pengambilan foto fisik untuk sudut ini:</p>
                <button type="button" class="btn btn-primary btn-block py-2 mb-2 font-weight-bold shadow-sm d-flex align-items-center justify-content-center" id="btnEmptyChooseCamera" style="border-radius: 8px;">
                    <i class="fas fa-camera fa-lg mr-2"></i>Foto Langsung Kamera
                </button>
                <button type="button" class="btn btn-outline-info btn-block py-2 font-weight-bold shadow-sm d-flex align-items-center justify-content-center" id="btnEmptyChooseUpload" style="border-radius: 8px;">
                    <i class="fas fa-upload fa-lg mr-2"></i>Unggah File Gambar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL KAMERA BERURUTAN TERPANDU (SEQUENTIAL CAMERA WIZARD & SINGLE SNAP) -->
<div class="modal fade" id="modalCameraWizard" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header bg-dark text-white py-2.5 px-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <i class="fas fa-camera text-warning mr-2"></i>
                    <div>
                        <h6 class="modal-title font-weight-bold mb-0" id="cameraWizardTitle">Pemotretan Terpandu 4 Sudut</h6>
                        <small class="text-light opacity-8" id="cameraWizardSubtitle">Target Aset: -</small>
                    </div>
                </div>
                <button type="button" class="close text-white opacity-8" id="btnCloseCameraWizard" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0 bg-black text-center position-relative" style="background: #000; min-height: 380px;">
                
                <!-- Indikator Langkah 4 Sudut -->
                <div class="wizard-steps-bar p-2 bg-dark d-flex justify-content-center align-items-center flex-wrap" style="gap: 8px; border-bottom: 1px solid #333;">
                    <span class="badge badge-secondary px-2.5 py-1.5 step-badge" id="stepBadge_depan"><i class="fas fa-circle mr-1"></i>1. Depan</span>
                    <i class="fas fa-chevron-right text-muted small"></i>
                    <span class="badge badge-secondary px-2.5 py-1.5 step-badge" id="stepBadge_atas"><i class="fas fa-circle mr-1"></i>2. Atas</span>
                    <i class="fas fa-chevron-right text-muted small"></i>
                    <span class="badge badge-secondary px-2.5 py-1.5 step-badge" id="stepBadge_bawah"><i class="fas fa-circle mr-1"></i>3. Bawah</span>
                    <i class="fas fa-chevron-right text-muted small"></i>
                    <span class="badge badge-secondary px-2.5 py-1.5 step-badge" id="stepBadge_belakang"><i class="fas fa-circle mr-1"></i>4. Belakang</span>
                </div>

                <!-- Video Viewfinder Kamera Langsung -->
                <div class="position-relative d-inline-block w-100" style="max-height: 440px; background: #000; overflow: hidden;">
                    <video id="cameraVideo" autoplay playsinline style="width: 100%; max-height: 440px; object-fit: contain; background: #000;"></video>
                    
                    <!-- Reticle Frame Pemandu Objek -->
                    <div id="cameraReticle" style="position: absolute; top: 10%; left: 10%; width: 80%; height: 80%; border: 2px dashed rgba(255, 255, 255, 0.45); pointer-events: none; border-radius: 8px;">
                        <span class="badge badge-dark opacity-8 p-1 small" style="position: absolute; top: 6px; left: 6px;" id="lblReticleAngle">
                            Arahkan kamera ke bagian DEPAN
                        </span>
                    </div>

                    <!-- Canvas Jepret Gambar Sementara -->
                    <canvas id="cameraCanvas" style="display: none;"></canvas>

                    <!-- Preview Hasil Foto dengan Watermark -->
                    <div id="cameraSnapPreviewWrapper" style="display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: #000;">
                        <img id="cameraSnapPreviewImg" src="" style="width: 100%; height: 100%; object-fit: contain;">
                        <div class="watermark-processing-indicator" id="wmProcessingSpinner" style="display: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(0,0,0,0.75); color: #fff; padding: 12px 20px; border-radius: 8px;">
                            <i class="fas fa-spinner fa-spin mr-2 text-warning"></i>Menyematkan Watermark TimeMark PU...
                        </div>
                    </div>
                </div>

                <!-- Petunjuk Sudut Aktif -->
                <div class="py-2 px-3 bg-dark text-white d-flex justify-content-between align-items-center">
                    <span class="small font-weight-bold" id="lblCurrentAngleInstruction">
                        <i class="fas fa-info-circle text-info mr-1"></i>Ambil foto dari <strong>Tampak Depan</strong>
                    </span>
                    <button type="button" class="btn btn-outline-light btn-xs" id="btnSwitchCamera" title="Ganti Kamera Depan/Belakang">
                        <i class="fas fa-sync-alt mr-1"></i>Ganti Kamera
                    </button>
                </div>
            </div>
            <div class="modal-footer bg-dark py-2 px-3 d-flex justify-content-between">
                <div>
                    <button type="button" class="btn btn-outline-secondary btn-sm text-light" id="btnCancelCameraWizard">
                        Batal
                    </button>
                </div>
                <div id="cameraControlsCapture">
                    <button type="button" class="btn btn-warning btn-sm rounded-pill px-4 font-weight-bold shadow" id="btnSnapPhoto">
                        <i class="fas fa-camera mr-1.5"></i>Jepret Foto
                    </button>
                </div>
                <div id="cameraControlsReview" style="display: none;">
                    <button type="button" class="btn btn-outline-light btn-sm mr-2" id="btnRetakeCurrentAngle">
                        <i class="fas fa-redo mr-1"></i>Ulangi
                    </button>
                    <button type="button" class="btn btn-success btn-sm rounded-pill px-4 font-weight-bold" id="btnAcceptCurrentAngle">
                        <i class="fas fa-check mr-1"></i>Gunakan &amp; Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Global Hidden File Input untuk Unggah Cepat -->
<input type="file" id="globalSlotFileInput" class="d-none" accept="image/*,.heic,.heif">

<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script>
$(document).ready(function() {
    var pemeliharaanId = <?= (int) $pemeliharaan['id']; ?>;
    var baseUrl = '<?= rtrim(site_url(), "/"); ?>';

    // State Aktif
    var activeFotoId = null;
    var activePos = '';
    var activeLabel = '';
    var activeItemNama = '';
    var activeItemNup = '';
    var activeSrc = '';
    var singleModeAngle = null;
    var currentAngleIndex = 0;
    var angleOrder = ['depan', 'atas', 'bawah', 'belakang'];
    var currentAngle = 'depan';
    var videoStream = null;
    var currentFacingMode = 'environment';
    var userGpsCoordinates = '';
    var capturedSessionImages = {};

    var angleLabels = {
        'depan': 'Tampak Depan',
        'atas': 'Tampak Atas',
        'bawah': 'Tampak Bawah',
        'belakang': 'Tampak Belakang'
    };

    // Dapatkan Koordinat GPS Real-time untuk Watermark
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(pos) {
            var lat = pos.coords.latitude.toFixed(5);
            var lng = pos.coords.longitude.toFixed(5);
            var latDir = lat >= 0 ? '° N' : '° S';
            var lngDir = lng >= 0 ? '° E' : '° W';
            userGpsCoordinates = Math.abs(lat) + latDir + ', ' + Math.abs(lng) + lngDir;
        }, function(err) {
            userGpsCoordinates = '0.4851° N, 101.4485° E'; // Fallback Pekanbaru
        });
    }

    // Klik tombol slot foto di tabel (Bisa Hijau atau Merah)
    $(document).on('click', '.btn-slot-photo', function() {
        var btn = $(this);
        activeFotoId = btn.data('foto-id');
        activePos = btn.data('pos');
        activeLabel = btn.data('label') || angleLabels[activePos];
        activeItemNama = btn.data('nama');
        activeItemNup = btn.data('nup');
        activeSrc = btn.data('src');
        var status = btn.data('status');

        if (status === 'filled') {
            // FOTO SUDAH ADA (HIJAU): Buka Modal Pratinjau & Tombol Update
            $('#previewModalTitle').text(activeItemNama + ' - ' + activeLabel);
            $('#previewModalSubtitle').text('NUP: ' + (activeItemNup || '-'));
            $('#previewModalImg').attr('src', activeSrc);
            $('#modalPhotoPreview').modal('show');
        } else {
            // BELUM ADA FOTO (MERAH): Buka Modal Pilihan Ambil Kamera / Unggah
            $('#emptyActionTitle').text(activeItemNama + ' - ' + activeLabel);
            $('#emptyActionSubtitle').text('NUP: ' + (activeItemNup || '-'));
            $('#modalEmptyAction').modal('show');
        }
    });

    // Pilihan dari Modal Merah: Ambil Kamera
    $('#btnEmptyChooseCamera').on('click', function() {
        $('#modalEmptyAction').modal('hide');
        startSingleCameraSnap(activeFotoId, activePos, activeLabel, activeItemNama, activeItemNup);
    });

    // Pilihan dari Modal Merah: Unggah File
    $('#btnEmptyChooseUpload').on('click', function() {
        $('#modalEmptyAction').modal('hide');
        $('#globalSlotFileInput').click();
    });

    // Pilihan dari Modal Hijau: Foto Ulang Kamera
    $('#btnPreviewRetakeCamera').on('click', function() {
        $('#modalPhotoPreview').modal('hide');
        startSingleCameraSnap(activeFotoId, activePos, activeLabel, activeItemNama, activeItemNup);
    });

    // Pilihan dari Modal Hijau: Ganti File Unggah
    $('#btnPreviewUploadNew').on('click', function() {
        $('#modalPhotoPreview').modal('hide');
        $('#globalSlotFileInput').click();
    });

    // Pilihan dari Modal Hijau: Hapus Foto
    $('#btnPreviewDelete').on('click', function() {
        if (!confirm('Apakah Anda yakin ingin menghapus foto ' + activeLabel + ' ini?')) {
            return;
        }

        var btn = $('#btnSlot_' + activeFotoId + '_' + activePos);
        var oldBtnHtml = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: baseUrl + '/admin/inventaris/pemeliharaan/' + pemeliharaanId + '/foto/ajax-delete-single',
            type: 'POST',
            data: { foto_id: activeFotoId, posisi: activePos },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $('#modalPhotoPreview').modal('hide');
                    setSlotToEmpty(activeFotoId, activePos, activeLabel, activeItemNama, activeItemNup);
                    updateStatsUI(res);
                } else {
                    alert('Gagal menghapus foto: ' + (res.error || 'Terjadi kesalahan'));
                    btn.html(oldBtnHtml);
                }
            },
            error: function() {
                alert('Gagal menghapus foto dari server.');
                btn.html(oldBtnHtml);
            }
        });
    });

    // Handle Input File Global (Unggah Langsung)
    $('#globalSlotFileInput').on('change', function(e) {
        var file = this.files[0];
        if (!file || !activeFotoId || !activePos) return;

        var formData = new FormData();
        formData.append('foto_id', activeFotoId);
        formData.append('posisi', activePos);
        formData.append('image_file', file);

        var btn = $('#btnSlot_' + activeFotoId + '_' + activePos);
        var oldBtnHtml = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: baseUrl + '/admin/inventaris/pemeliharaan/' + pemeliharaanId + '/foto/ajax-save-single',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    setSlotToFilled(activeFotoId, activePos, activeLabel, activeItemNama, activeItemNup, res.photo_url);
                    updateStatsUI(res);
                } else {
                    alert('Gagal mengunggah foto: ' + (res.error || 'Terjadi kesalahan'));
                    btn.html(oldBtnHtml);
                }
            },
            error: function() {
                alert('Gagal mengunggah foto ke server.');
                btn.html(oldBtnHtml);
            },
            complete: function() {
                $('#globalSlotFileInput').val('');
            }
        });
    });

    // Mulai Kamera Single Angle
    function startSingleCameraSnap(fotoId, pos, label, nama, nup) {
        activeFotoId = fotoId;
        activePos = pos;
        activeLabel = label;
        activeItemNama = nama;
        activeItemNup = nup;
        singleModeAngle = pos;

        $('#cameraWizardTitle').text('Foto ' + label);
        $('#cameraWizardSubtitle').text(nama + ' (NUP: ' + (nup || '-') + ')');
        $('.wizard-steps-bar').hide();

        capturedSessionImages = {};
        $('#modalCameraWizard').modal('show');
        switchCameraStep(pos);
        startCameraStream();
    }

    // Start Sequential Wizard 4 Sudut dari tombol di kolom tabel
    $(document).on('click', '.btn-start-group-wizard', function() {
        activeFotoId = $(this).data('foto-id');
        activeItemNama = $(this).data('nama');
        activeItemNup = $(this).data('nup');
        singleModeAngle = null;

        $('#cameraWizardTitle').text('Pemotretan Terpandu 4 Sudut');
        $('#cameraWizardSubtitle').text(activeItemNama + ' (NUP: ' + (activeItemNup || '-') + ')');
        $('.wizard-steps-bar').show();

        capturedSessionImages = {};
        $('#modalCameraWizard').modal('show');
        switchCameraStep('depan');
        startCameraStream();
    });

    // Beralih Sudut Kamera
    function switchCameraStep(angle) {
        currentAngle = angle;
        currentAngleIndex = angleOrder.indexOf(angle);

        $('.step-badge').removeClass('badge-warning text-dark font-weight-bold').addClass('badge-secondary');
        $('#stepBadge_' + angle).removeClass('badge-secondary').addClass('badge-warning text-dark font-weight-bold');

        $('#lblReticleAngle').text('Arahkan kamera ke bagian ' + angle.toUpperCase());
        $('#lblCurrentAngleInstruction').html('<i class="fas fa-camera text-info mr-1"></i>Ambil foto dari <strong>' + angleLabels[angle] + '</strong>');

        $('#cameraSnapPreviewWrapper').hide();
        $('#cameraControlsCapture').show();
        $('#cameraControlsReview').hide();
    }

    // Mulai Stream Kamera WebRTC
    function startCameraStream() {
        stopCameraStream();

        var constraints = {
            video: {
                facingMode: currentFacingMode,
                width: { ideal: 1920 },
                height: { ideal: 1080 }
            },
            audio: false
        };

        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia(constraints).then(function(stream) {
                videoStream = stream;
                var videoElem = document.getElementById('cameraVideo');
                videoElem.srcObject = stream;
                videoElem.play();
            }).catch(function(err) {
                console.warn('Gagal akses kamera resolusi tinggi, coba default:', err);
                navigator.mediaDevices.getUserMedia({ video: true, audio: false }).then(function(stream) {
                    videoStream = stream;
                    var videoElem = document.getElementById('cameraVideo');
                    videoElem.srcObject = stream;
                    videoElem.play();
                }).catch(function(fallbackErr) {
                    alert('Tidak dapat mengakses kamera perangkat: ' + fallbackErr.message);
                });
            });
        } else {
            alert('Perangkat Anda tidak mendukung akses WebRTC Camera.');
        }
    }

    // Hentikan Stream Kamera
    function stopCameraStream() {
        if (videoStream) {
            videoStream.getTracks().forEach(function(track) { track.stop(); });
            videoStream = null;
        }
    }

    // Ganti Kamera Depan/Belakang
    $('#btnSwitchCamera').on('click', function() {
        currentFacingMode = (currentFacingMode === 'environment') ? 'user' : 'environment';
        startCameraStream();
    });

    // Tutup / Batal Modal Wizard
    $('#btnCloseCameraWizard, #btnCancelCameraWizard').on('click', function() {
        stopCameraStream();
        $('#modalCameraWizard').modal('hide');
    });

    // Jepret Foto Saat Ini
    $('#btnSnapPhoto').on('click', function() {
        var video = document.getElementById('cameraVideo');
        var canvas = document.getElementById('cameraCanvas');
        if (!video || !video.videoWidth) return;

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        var ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        var rawBase64 = canvas.toDataURL('image/jpeg', 0.92);

        $('#cameraSnapPreviewImg').attr('src', rawBase64);
        $('#cameraSnapPreviewWrapper').show();
        $('#wmProcessingSpinner').show();
        $('#cameraControlsCapture').hide();
        $('#cameraControlsReview').hide();

        // Kirim ke server untuk Watermark TimeMark Resmi PU
        $.ajax({
            url: baseUrl + '/admin/inventaris/pemeliharaan/apply-watermark',
            type: 'POST',
            data: {
                image_data: rawBase64,
                nama_barang: activeItemNama,
                merk_tipe: '',
                nup: activeItemNup,
                sudut: angleLabels[currentAngle],
                coordinates: userGpsCoordinates
            },
            dataType: 'json',
            success: function(res) {
                $('#wmProcessingSpinner').hide();
                if (res.success && res.data_uri) {
                    $('#cameraSnapPreviewImg').attr('src', res.data_uri);
                    capturedSessionImages[currentAngle] = res.data_uri;
                    $('#cameraControlsReview').show();
                } else {
                    alert('Gagal menyematkan watermark: ' + (res.error || 'Unknown error'));
                    capturedSessionImages[currentAngle] = rawBase64;
                    $('#cameraControlsReview').show();
                }
            },
            error: function() {
                $('#wmProcessingSpinner').hide();
                capturedSessionImages[currentAngle] = rawBase64;
                $('#cameraControlsReview').show();
            }
        });
    });

    // Ulangi Jepret Sudut Saat Ini
    $('#btnRetakeCurrentAngle').on('click', function() {
        delete capturedSessionImages[currentAngle];
        $('#cameraSnapPreviewWrapper').hide();
        $('#cameraControlsCapture').show();
        $('#cameraControlsReview').hide();
    });

    // Terima Hasil Foto Sudut Saat Ini
    $('#btnAcceptCurrentAngle').on('click', function() {
        var finalDataUri = capturedSessionImages[currentAngle];
        if (!finalDataUri) return;

        var targetAngle = currentAngle;
        var targetFotoId = activeFotoId;

        // Auto-Save instan ke backend untuk sudut ini
        saveCapturedImageToSlot(targetFotoId, targetAngle, finalDataUri);

        if (singleModeAngle !== null) {
            // Selesai mode single
            stopCameraStream();
            $('#modalCameraWizard').modal('hide');
        } else {
            // Lanjut ke sudut berikutnya jika ada
            if (currentAngleIndex < angleOrder.length - 1) {
                var nextAngle = angleOrder[currentAngleIndex + 1];
                switchCameraStep(nextAngle);
            } else {
                // Selesai seluruh 4 sudut
                stopCameraStream();
                $('#modalCameraWizard').modal('hide');
            }
        }
    });

    // Kirim AJAX Save Single Foto ke Server dari Kamera
    function saveCapturedImageToSlot(fotoId, pos, dataUri) {
        var btn = $('#btnSlot_' + fotoId + '_' + pos);
        btn.html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: baseUrl + '/admin/inventaris/pemeliharaan/' + pemeliharaanId + '/foto/ajax-save-single',
            type: 'POST',
            data: {
                foto_id: fotoId,
                posisi: pos,
                image_base64: dataUri
            },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    setSlotToFilled(fotoId, pos, angleLabels[pos], activeItemNama, activeItemNup, res.photo_url || dataUri);
                    updateStatsUI(res);
                } else {
                    alert('Gagal menyimpan foto: ' + (res.error || 'Terjadi kesalahan'));
                    setSlotToEmpty(fotoId, pos, angleLabels[pos], activeItemNama, activeItemNup);
                }
            },
            error: function() {
                setSlotToFilled(fotoId, pos, angleLabels[pos], activeItemNama, activeItemNup, dataUri);
            }
        });
    }

    // Ubah status tombol slot di tabel menjadi HIJAU (Foto Tersedia)
    function setSlotToFilled(fotoId, pos, label, nama, nup, photoUrl) {
        var cell = $('#cellSlot_' + fotoId + '_' + pos);
        cell.html(
            '<button type="button" class="btn btn-success btn-sm btn-slot-photo shadow-sm"' +
                ' id="btnSlot_' + fotoId + '_' + pos + '"' +
                ' data-foto-id="' + fotoId + '"' +
                ' data-pos="' + pos + '"' +
                ' data-status="filled"' +
                ' data-src="' + photoUrl + '"' +
                ' data-nama="' + escapeHtml(nama) + '"' +
                ' data-nup="' + escapeHtml(nup) + '"' +
                ' data-label="' + escapeHtml(label) + '"' +
                ' title="' + escapeHtml(label) + ': Foto Tersedia (Klik untuk Pratinjau & Update)"' +
                ' style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px;">' +
                '<i class="fas fa-check-circle" style="font-size: 1.1rem;"></i>' +
            '</button>'
        );
        recalculateRowBadge(fotoId);
    }

    // Ubah status tombol slot di tabel menjadi MERAH (Belum Ada Foto)
    function setSlotToEmpty(fotoId, pos, label, nama, nup) {
        var cell = $('#cellSlot_' + fotoId + '_' + pos);
        cell.html(
            '<button type="button" class="btn btn-outline-danger btn-sm btn-slot-photo shadow-sm"' +
                ' id="btnSlot_' + fotoId + '_' + pos + '"' +
                ' data-foto-id="' + fotoId + '"' +
                ' data-pos="' + pos + '"' +
                ' data-status="empty"' +
                ' data-src=""' +
                ' data-nama="' + escapeHtml(nama) + '"' +
                ' data-nup="' + escapeHtml(nup) + '"' +
                ' data-label="' + escapeHtml(label) + '"' +
                ' title="' + escapeHtml(label) + ': Belum Ada Foto (Klik untuk Ambil / Upload Foto)"' +
                ' style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px;">' +
                '<i class="fas fa-camera text-danger" style="font-size: 1.05rem;"></i>' +
            '</button>'
        );
        recalculateRowBadge(fotoId);
    }

    // Rekalkulasi badge kelengkapan baris tabel
    function recalculateRowBadge(fId) {
        var filledCount = $('#rowGroup_' + fId + ' .btn-success').length;
        var gStatus = (filledCount === 4) ? 'success' : ((filledCount > 0) ? 'warning' : 'danger');

        var badge = $('#groupBadge_' + fId);
        badge.removeClass('badge-success badge-warning badge-danger').addClass('badge-' + gStatus);
        badge.text(filledCount + ' / 4 Terisi');
    }

    // Update Metrik Statistik & Progress Bar Real-Time
    function updateStatsUI(res) {
        if (!res) return;

        var filled = res.filled_slots || 0;
        var total = res.total_slots || 0;
        var percentage = res.percentage || 0;
        var empty = Math.max(0, total - filled);

        $('#statFilledSlots').html(filled + ' <small class="text-success font-weight-normal">Foto</small>');
        $('#statEmptySlots').html(empty + ' <small class="text-danger font-weight-normal">Foto</small>');
        $('#progressPercentageText').text(percentage + '% Selesai');
        $('#mainProgressBar').css('width', percentage + '%').attr('aria-valuenow', percentage);

        if (res.status_color === 'success') {
            $('#mainProgressBar').removeClass('bg-primary').addClass('bg-success');
        } else {
            $('#mainProgressBar').removeClass('bg-success').addClass('bg-primary');
        }

        var overallBadge = $('#badgeOverallStatus');
        overallBadge.removeClass('badge-success badge-warning badge-danger').addClass('badge-' + res.status_color);
        $('#labelOverallStatus').text(res.status_label || (percentage + '%'));
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
});
</script>
<?= $this->endSection(); ?>
