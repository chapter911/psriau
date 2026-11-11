<?= $this->extend('layouts/admin'); ?>

<?php
$bulanMap = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
];
$statusBadges = [
    'draft'        => ['badge-secondary', 'Draft'],
    'menunggu_ttd' => ['badge-warning text-dark', 'Menunggu TTD / Verifikasi'],
    'disetujui'    => ['badge-success', 'Disetujui (TTD Basah)'],
    'dibayar'      => ['badge-primary', 'Lunas / Dibayar'],
    'ditolak'      => ['badge-danger', 'Ditolak / Revisi'],
];
$jenisDokLabels = [
    'semua_gabungan' => 'Bundel Gabungan (Semua Dokumen)',
    'usulan_ppk'     => '1. Usulan Kebutuhan PPK',
    'spb'            => '2. Surat Pesanan Barang (SPB)',
    'bast'           => '3. BAST & Pemeriksaan Barang',
    'lamp_bastp'     => '4. Lampiran BASTP Persediaan',
    'faktur'         => '5. Faktur Penagihan',
    'kuitansi_ls'    => '6. Kuitansi / Bukti Pembayaran (LS)',
];
$p   = $pengadaan;
$pid = (int) $p['id'];
$st  = (string) ($p['status'] ?? 'draft');
$stMeta = $statusBadges[$st] ?? ['badge-secondary', ucfirst($st)];
?>

<?= $this->section('content'); ?>
<div class="mb-3 d-flex flex-wrap justify-content-between align-items-center" style="gap: 8px;">
    <div>
        <a href="<?= site_url('/admin/atk/pengadaan'); ?>" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
        </a>
        <span class="badge <?= $stMeta[0]; ?> ml-2 p-2" style="font-size: 0.9rem;">
            Status: <?= esc($stMeta[1]); ?>
        </span>
    </div>

    <div class="d-flex flex-wrap" style="gap: 6px;">
        <?php if (! empty($permissions['export'])): ?>
            <div class="btn-group btn-group-sm">
                <a href="<?= site_url('/admin/atk/pengadaan/' . $pid . '/cetak-pdf/all'); ?>" target="_blank" class="btn btn-danger">
                    <i class="fas fa-file-pdf mr-1"></i> Cetak Semua (6 Dokumen PDF)
                </a>
                <button type="button" class="btn btn-danger dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="sr-only">Pilih Dokumen</span>
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" target="_blank" href="<?= site_url('/admin/atk/pengadaan/' . $pid . '/cetak-pdf/usulan_ppk'); ?>">1. Usulan Kebutuhan PPK</a>
                    <a class="dropdown-item" target="_blank" href="<?= site_url('/admin/atk/pengadaan/' . $pid . '/cetak-pdf/spb'); ?>">2. Surat Pesanan Barang</a>
                    <a class="dropdown-item" target="_blank" href="<?= site_url('/admin/atk/pengadaan/' . $pid . '/cetak-pdf/bast'); ?>">3. BAST &amp; Pemeriksaan</a>
                    <a class="dropdown-item" target="_blank" href="<?= site_url('/admin/atk/pengadaan/' . $pid . '/cetak-pdf/lamp_bastp'); ?>">4. Lampiran BASTP</a>
                    <a class="dropdown-item" target="_blank" href="<?= site_url('/admin/atk/pengadaan/' . $pid . '/cetak-pdf/faktur'); ?>">5. Faktur Penagihan</a>
                    <a class="dropdown-item" target="_blank" href="<?= site_url('/admin/atk/pengadaan/' . $pid . '/cetak-pdf/kuitansi_ls'); ?>">6. Kuitansi LS</a>
                </div>
            </div>

            <a href="<?= site_url('/admin/atk/pengadaan/' . $pid . '/export-excel'); ?>" class="btn btn-sm btn-success">
                <i class="fas fa-file-excel mr-1"></i> Export Excel (6 Sheet)
            </a>
        <?php endif; ?>

        <?php if (! empty($permissions['edit'])): ?>
            <button type="button" class="btn btn-sm btn-warning text-dark" data-toggle="modal" data-target="#modalEditPengadaan">
                <i class="fas fa-edit mr-1"></i> Ubah Data &amp; Item
            </button>
        <?php endif; ?>

        <?php if (! empty($permissions['edit']) || ! empty($permissions['add']) || ! empty($permissions['approval'])): ?>
            <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalUploadTtd">
                <i class="fas fa-file-upload mr-1"></i> Upload Dokumen TTD Basah
            </button>
        <?php endif; ?>

        <?php if (! empty($permissions['approval'])): ?>
            <?php if ($st !== 'disetujui' && $st !== 'dibayar'): ?>
                <form method="post" action="<?= site_url('/admin/atk/pengadaan/' . $pid . '/setujui'); ?>" class="d-inline" data-confirm-title="Setujui Pengadaan?" data-confirm-text="Status pengadaan akan diubah menjadi Disetujui.">
                    <?= csrf_field(); ?>
                    <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check mr-1"></i> Setujui</button>
                </form>
            <?php endif; ?>
            <?php if ($st === 'disetujui'): ?>
                <form method="post" action="<?= site_url('/admin/atk/pengadaan/' . $pid . '/setujui'); ?>" class="d-inline" data-confirm-title="Tandai Lunas / Dibayar?" data-confirm-text="Pengadaan akan ditandai sebagai Lunas / Dibayar.">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="mark_paid" value="1">
                    <button type="submit" class="btn btn-sm btn-info"><i class="fas fa-check-double mr-1"></i> Tandai Dibayar</button>
                </form>
            <?php endif; ?>
            <button type="button" class="btn btn-sm btn-outline-danger" data-toggle="modal" data-target="#modalTolakPengadaan">
                <i class="fas fa-times-circle mr-1"></i> Tolak / Revisi
            </button>
        <?php endif; ?>
    </div>
</div>

<?php if ($st === 'ditolak' && ! empty($p['rejection_reason'])): ?>
    <div class="alert alert-danger">
        <h6 class="font-weight-bold mb-1"><i class="fas fa-ban mr-1"></i> Pengadaan / Dokumen Ditolak untuk Direvisi</h6>
        <p class="mb-0">Alasan: <?= esc($p['rejection_reason']); ?></p>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-5">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-info-circle mr-1"></i> Informasi Dokumen SPJ</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped mb-0">
                    <tbody>
                        <tr><th style="width: 42%;">Periode</th><td><?= esc($bulanMap[(int) $p['bulan']] ?? '-'); ?> <?= (int) $p['tahun_anggaran']; ?></td></tr>
                        <tr><th>Kategori</th><td><span class="badge <?= $p['kategori'] === 'Supplies Komputer' ? 'badge-info' : 'badge-dark'; ?>"><?= esc($p['kategori']); ?></span></td></tr>
                        <tr><th>Kegiatan</th><td><?= esc($p['kegiatan_label']); ?></td></tr>
                        <tr><th>Mata Anggaran</th><td><code><?= esc($p['kode_mata_anggaran']); ?></code></td></tr>
                        <tr><th>Penyedia</th><td><strong><?= esc($penyedia['nama_perusahaan'] ?? '-'); ?></strong><br><small class="text-muted"><?= esc($penyedia['nama_direktur'] ?? ''); ?></small></td></tr>
                        <tr><th>No. / Tgl Usulan</th><td><?= esc($p['no_usulan'] ?: '-'); ?> / <?= esc($p['tgl_usulan'] ?: '-'); ?></td></tr>
                        <tr><th>No. / Tgl SPB</th><td><?= esc($p['no_pesanan'] ?: '-'); ?> / <?= esc($p['tgl_pesanan'] ?: '-'); ?> (Batas: <?= esc($p['tgl_batas_terima'] ?: '-'); ?>)</td></tr>
                        <tr><th>No. / Tgl BASTP</th><td><?= esc($p['no_bastp'] ?: '-'); ?> / <?= esc($p['tgl_bastp'] ?: '-'); ?></td></tr>
                        <tr><th>No. / Tgl Faktur</th><td><?= esc($p['no_faktur'] ?: '-'); ?> / <?= esc($p['tgl_faktur'] ?: '-'); ?></td></tr>
                        <tr><th>No. Bukti / Tgl Kuitansi</th><td><?= esc($p['no_bukti_kuitansi'] ?: '-'); ?> / <?= esc($p['tgl_kuitansi'] ?: '-'); ?></td></tr>
                        <tr><th>Tgl Lunas Bayar</th><td><?= esc($p['tgl_lunas_bayar'] ?: '-'); ?></td></tr>
                        <tr><th>PPK</th><td><?= esc($p['ppk_nama']); ?><br><small class="text-muted">NIP <?= esc($p['ppk_nip']); ?></small></td></tr>
                        <tr><th>Bendahara</th><td><?= esc($p['bendahara_nama']); ?><br><small class="text-muted">NIP <?= esc($p['bendahara_nip']); ?></small></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card card-outline card-primary">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold"><i class="fas fa-shopping-cart mr-1"></i> Rincian Barang Pengadaan</h3>
                <span class="badge badge-success p-2">Total: Rp <?= number_format((float) $p['total_nilai'], 0, ',', '.'); ?></span>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center" style="width: 40px;">No</th>
                            <th>Nama Barang</th>
                            <th class="text-center">Vol</th>
                            <th>Satuan</th>
                            <th class="text-right">Harga Satuan</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $idx => $it): ?>
                            <tr>
                                <td class="text-center"><?= $idx + 1; ?></td>
                                <td><?= esc($it['nama_barang']); ?></td>
                                <td class="text-center"><?= number_format((float) $it['volume'], 0, ',', '.'); ?></td>
                                <td><?= esc($it['satuan']); ?></td>
                                <td class="text-right">Rp <?= number_format((float) $it['harga_satuan'], 0, ',', '.'); ?></td>
                                <td class="text-right font-weight-bold">Rp <?= number_format((float) $it['subtotal'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-light">
                            <th colspan="5" class="text-right">JUMLAH TOTAL</th>
                            <th class="text-right">Rp <?= number_format((float) $p['total_nilai'], 0, ',', '.'); ?></th>
                        </tr>
                        <tr>
                            <td colspan="6" class="font-italic"><strong>Terbilang:</strong> <?= esc($p['terbilang']); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="card card-outline card-success">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold"><i class="fas fa-file-signature mr-1"></i> Arsip Dokumen Tanda Tangan Basah</h3>
                <?php if (! empty($permissions['edit']) || ! empty($permissions['add']) || ! empty($permissions['approval'])): ?>
                    <button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#modalUploadTtd">
                        <i class="fas fa-upload mr-1"></i> Upload Scan TTD
                    </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($dokumenTtdList)): ?>
                    <p class="text-muted mb-0"><i class="fas fa-info-circle mr-1"></i> Belum ada dokumen bertanda tangan basah yang diunggah. Cetak dokumen PDF terlebih dahulu, tandatangani secara basah, lalu unggah hasil pindainya di sini.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Jenis Dokumen</th>
                                    <th>Nama File</th>
                                    <th class="text-center">Status Verifikasi</th>
                                    <th>Catatan / Alasan</th>
                                    <th class="text-center" style="width: 110px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dokumenTtdList as $doc): ?>
                                    <?php
                                    $dv = (string) ($doc['status_verifikasi'] ?? 'menunggu');
                                    $dvBadge = match ($dv) {
                                        'disetujui' => ['badge-success', 'Disetujui'],
                                        'ditolak'   => ['badge-danger', 'Ditolak'],
                                        default     => ['badge-warning text-dark', 'Menunggu'],
                                    };
                                    ?>
                                    <tr>
                                        <td class="small"><?= esc($jenisDokLabels[$doc['jenis_dokumen']] ?? $doc['jenis_dokumen']); ?></td>
                                        <td class="small">
                                            <a href="<?= site_url('/admin/atk/pengadaan/dokumen/' . (int) $doc['id'] . '/view'); ?>" target="_blank">
                                                <i class="fas fa-external-link-alt mr-1"></i><?= esc($doc['file_name']); ?>
                                            </a>
                                            <div class="text-muted"><?= esc($doc['created_at']); ?></div>
                                        </td>
                                        <td class="text-center"><span class="badge <?= $dvBadge[0]; ?>"><?= esc($dvBadge[1]); ?></span></td>
                                        <td class="small">
                                            <?= esc($doc['catatan'] ?: '-'); ?>
                                            <?php if ($dv === 'ditolak' && ! empty($doc['rejection_reason'])): ?>
                                                <div class="text-danger"><strong>Alasan tolak:</strong> <?= esc($doc['rejection_reason']); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= site_url('/admin/atk/pengadaan/dokumen/' . (int) $doc['id'] . '/view'); ?>" target="_blank" class="btn btn-xs btn-info" title="Lihat File">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if (! empty($permissions['delete']) || ! empty($permissions['edit'])): ?>
                                                <form method="post" action="<?= site_url('/admin/atk/pengadaan/dokumen/' . (int) $doc['id'] . '/hapus'); ?>" class="d-inline" data-confirm-title="Hapus Dokumen?" data-confirm-text="File dokumen tanda tangan basah ini akan dihapus.">
                                                    <?= csrf_field(); ?>
                                                    <button type="submit" class="btn btn-xs btn-outline-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload Dokumen TTD Basah -->
<div class="modal fade" id="modalUploadTtd" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="<?= site_url('/admin/atk/pengadaan/' . $pid . '/upload-ttd'); ?>" enctype="multipart/form-data" data-skip-confirm="1">
                <?= csrf_field(); ?>
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-file-signature mr-1"></i> Upload Dokumen Tanda Tangan Basah</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="small font-weight-bold">Jenis Dokumen yang Diunggah</label>
                        <select name="jenis_dokumen" class="form-control form-control-sm">
                            <?php foreach ($jenisDokLabels as $k => $lbl): ?>
                                <option value="<?= esc($k); ?>"><?= esc($lbl); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">File Hasil Scan / Foto (PDF / JPG / PNG)</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="fileDokumenTtd" name="file_dokumen" accept=".pdf,.jpg,.jpeg,.png" required>
                            <label class="custom-file-label" for="fileDokumenTtd">Pilih file...</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Catatan (Opsional)</label>
                        <textarea name="catatan" rows="2" class="form-control form-control-sm" placeholder="Contoh: Sudah ditandatangani basah oleh PPK, Bendahara, dan CV. Sinar Cahaya Lestari"></textarea>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="chkAutoApprove" name="auto_approve" value="1" checked>
                        <label class="custom-control-label small" for="chkAutoApprove">
                            <strong>Langsung anggap Disetujui</strong> (karena dokumen sudah bertanda tangan basah; tetap bisa ditolak/direvisi bila perlu)
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-upload mr-1"></i> Unggah Dokumen</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if (! empty($permissions['approval'])): ?>
<!-- Modal Tolak / Revisi -->
<div class="modal fade" id="modalTolakPengadaan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="<?= site_url('/admin/atk/pengadaan/' . $pid . '/tolak'); ?>" data-skip-confirm="1">
                <?= csrf_field(); ?>
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-times-circle mr-1"></i> Tolak / Minta Revisi Dokumen</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="small font-weight-bold">Alasan Penolakan / Catatan Revisi <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" rows="3" class="form-control form-control-sm" required placeholder="Contoh: Tanda tangan penyedia belum lengkap / Nomor BASTP belum terisi / Scan buram."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-ban mr-1"></i> Tolak Dokumen</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (! empty($permissions['edit'])): ?>
<!-- Modal Edit Pengadaan -->
<div class="modal fade" id="modalEditPengadaan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <form method="post" action="<?= site_url('/admin/atk/pengadaan/' . $pid . '/ubah'); ?>" data-skip-confirm="1">
                <?= csrf_field(); ?>
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-edit mr-1"></i> Ubah Data &amp; Rincian Pengadaan</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-2 mb-2">
                            <label class="small font-weight-bold">Tahun</label>
                            <input type="number" name="tahun_anggaran" class="form-control form-control-sm" value="<?= (int) $p['tahun_anggaran']; ?>" required>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="small font-weight-bold">Bulan</label>
                            <select name="bulan" class="form-control form-control-sm" required>
                                <?php foreach ($bulanMap as $num => $nama): ?>
                                    <option value="<?= $num; ?>" <?= (int) $p['bulan'] === $num ? 'selected' : ''; ?>><?= $nama; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">Kategori</label>
                            <select name="kategori" class="form-control form-control-sm" required>
                                <option value="ATK" <?= $p['kategori'] === 'ATK' ? 'selected' : ''; ?>>ATK</option>
                                <option value="Supplies Komputer" <?= $p['kategori'] === 'Supplies Komputer' ? 'selected' : ''; ?>>Supplies Komputer</option>
                            </select>
                        </div>
                        <div class="col-md-5 mb-2">
                            <label class="small font-weight-bold">Penyedia</label>
                            <select name="penyedia_id" class="form-control form-control-sm">
                                <?php foreach ($penyediaList as $pen): ?>
                                    <option value="<?= (int) $pen['id']; ?>" <?= (int) $p['penyedia_id'] === (int) $pen['id'] ? 'selected' : ''; ?>><?= esc($pen['nama_perusahaan']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Kode Mata Anggaran</label>
                            <input type="text" name="kode_mata_anggaran" class="form-control form-control-sm" value="<?= esc($p['kode_mata_anggaran']); ?>">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Kegiatan</label>
                            <input type="text" name="kegiatan_label" class="form-control form-control-sm" value="<?= esc($p['kegiatan_label']); ?>">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Kop Surat</label>
                            <select name="kop_surat_id" class="form-control form-control-sm">
                                <option value="">-- Kop Surat Aktif Default --</option>
                                <?php foreach ($kopSuratList as $k): ?>
                                    <option value="<?= (int) $k['id']; ?>" <?= (int) ($p['kop_surat_id'] ?? 0) === (int) $k['id'] ? 'selected' : ''; ?>><?= esc($k['title']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">No. Usulan PPK</label>
                            <input type="text" name="no_usulan" class="form-control form-control-sm" value="<?= esc($p['no_usulan']); ?>">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">Tgl. Usulan</label>
                            <input type="date" name="tgl_usulan" class="form-control form-control-sm" value="<?= esc($p['tgl_usulan']); ?>">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">No. Surat Pesanan (SPB)</label>
                            <input type="text" name="no_pesanan" class="form-control form-control-sm" value="<?= esc($p['no_pesanan']); ?>">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">Tgl. Surat Pesanan</label>
                            <input type="date" name="tgl_pesanan" class="form-control form-control-sm" value="<?= esc($p['tgl_pesanan']); ?>">
                        </div>

                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">Tgl. Batas Terima</label>
                            <input type="date" name="tgl_batas_terima" class="form-control form-control-sm" value="<?= esc($p['tgl_batas_terima']); ?>">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">No. BASTP</label>
                            <input type="text" name="no_bastp" class="form-control form-control-sm" value="<?= esc($p['no_bastp']); ?>">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">Tgl. BASTP</label>
                            <input type="date" name="tgl_bastp" class="form-control form-control-sm" value="<?= esc($p['tgl_bastp']); ?>">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">No. Faktur</label>
                            <input type="text" name="no_faktur" class="form-control form-control-sm" value="<?= esc($p['no_faktur']); ?>">
                        </div>

                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">Tgl. Faktur</label>
                            <input type="date" name="tgl_faktur" class="form-control form-control-sm" value="<?= esc($p['tgl_faktur']); ?>">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">No. Bukti Kuitansi</label>
                            <input type="text" name="no_bukti_kuitansi" class="form-control form-control-sm" value="<?= esc($p['no_bukti_kuitansi']); ?>">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">Tgl. Kuitansi</label>
                            <input type="date" name="tgl_kuitansi" class="form-control form-control-sm" value="<?= esc($p['tgl_kuitansi']); ?>">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">Tgl. Lunas Bayar</label>
                            <input type="date" name="tgl_lunas_bayar" class="form-control form-control-sm" value="<?= esc($p['tgl_lunas_bayar']); ?>">
                        </div>

                        <div class="col-md-6 mb-2">
                            <label class="small font-weight-bold">Nama &amp; NIP PPK</label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="ppk_nama" class="form-control" value="<?= esc($p['ppk_nama']); ?>" placeholder="Nama PPK">
                                <input type="text" name="ppk_nip" class="form-control" value="<?= esc($p['ppk_nip']); ?>" placeholder="NIP PPK">
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="small font-weight-bold">Nama &amp; NIP Bendahara</label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="bendahara_nama" class="form-control" value="<?= esc($p['bendahara_nama']); ?>" placeholder="Nama Bendahara">
                                <input type="text" name="bendahara_nip" class="form-control" value="<?= esc($p['bendahara_nip']); ?>" placeholder="NIP Bendahara">
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="font-weight-bold mb-0"><i class="fas fa-list mr-1"></i> Rincian Barang</h6>
                        <button type="button" class="btn btn-xs btn-outline-primary" id="btnEditAddRow">
                            <i class="fas fa-plus mr-1"></i> Tambah Baris
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" id="tblEditItems">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 28%;">Katalog</th>
                                    <th style="width: 26%;">Nama Barang</th>
                                    <th style="width: 11%;">Satuan</th>
                                    <th style="width: 9%;">Vol</th>
                                    <th style="width: 13%;">Harga</th>
                                    <th style="width: 10%;">Subtotal</th>
                                    <th style="width: 36px;"></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-right">TOTAL (Rp)</th>
                                    <th class="text-right" id="editGrandTotal">0</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning btn-sm font-weight-bold"><i class="fas fa-save mr-1"></i> Simpan Perubahan</button>
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
    const catalog = <?= json_encode($barangList ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const existingItems = <?= json_encode($items ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const tbody = document.querySelector('#tblEditItems tbody');
    const grandTotalEl = document.getElementById('editGrandTotal');
    const btnAddRow = document.getElementById('btnEditAddRow');
    if (!tbody || !btnAddRow) return;

    const formatRp = (num) => Math.round(num || 0).toLocaleString('id-ID');
    const parseDigits = (val) => parseInt(String(val || '').replace(/\D/g, ''), 10) || 0;
    const formatInputRibuan = (inp) => {
        const num = parseDigits(inp.value);
        inp.value = inp.value === '' ? '' : formatRp(num);
    };
    const catalogById = {};
    catalog.forEach((b) => {
        catalogById[String(b.id)] = b;
    });

    const recalc = () => {
        let total = 0;
        tbody.querySelectorAll('tr').forEach((tr) => {
            const vol = parseFloat(tr.querySelector('.inp-vol')?.value || '0') || 0;
            const harga = parseDigits(tr.querySelector('.inp-harga')?.value || '0');
            const sub = vol * harga;
            total += sub;
            tr.querySelector('.txt-sub').textContent = formatRp(sub);
        });
        if (grandTotalEl) grandTotalEl.textContent = formatRp(total);
    };

    const applyCatalogSelection = (selEl) => {
        const tr = selEl.closest('tr');
        if (!tr) return;
        const item = catalogById[String(selEl.value || '')];
        if (item) {
            tr.querySelector('.inp-nama').value = item.nama_barang || '';
            tr.querySelector('.inp-satuan').value = item.satuan || 'Buah';
            tr.querySelector('.inp-harga').value = formatRp(parseFloat(item.harga_satuan || '0'));
        }
        recalc();
    };

    const buildCatalogOptions = (selectedId) => {
        let html = '<option value="">-- Katalog / Manual --</option>';
        catalog.forEach((b) => {
            const sel = String(b.id) === String(selectedId || '') ? 'selected' : '';
            html += `<option value="${b.id}" ${sel}>[${b.kategori}] ${b.nama_barang} - Rp ${formatRp(b.harga_satuan)}</option>`;
        });
        return html;
    };

    const addRow = (prefill = null) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><select name="item_barang_id[]" class="form-control form-control-sm sel-katalog">${buildCatalogOptions(prefill?.barang_id)}</select></td>
            <td><input type="text" name="item_nama_barang[]" class="form-control form-control-sm inp-nama" value="${prefill ? String(prefill.nama_barang).replace(/"/g, '&quot;') : ''}" required></td>
            <td><input type="text" name="item_satuan[]" class="form-control form-control-sm inp-satuan" value="${prefill ? String(prefill.satuan).replace(/"/g, '&quot;') : 'Buah'}" required></td>
            <td><input type="number" step="1" min="1" name="item_volume[]" class="form-control form-control-sm inp-vol text-center" value="${prefill ? Math.round(parseFloat(prefill.volume || 1)) : 1}" required></td>
            <td><input type="text" inputmode="numeric" name="item_harga_satuan[]" class="form-control form-control-sm inp-harga text-right" value="${prefill ? formatRp(parseFloat(prefill.harga_satuan || 0)) : '0'}" required></td>
            <td class="text-right align-middle font-weight-bold txt-sub">0</td>
            <td class="text-center align-middle"><button type="button" class="btn btn-xs btn-outline-danger btn-del-row"><i class="fas fa-times"></i></button></td>
        `;
        tbody.appendChild(tr);

        const sel = tr.querySelector('.sel-katalog');
        sel.addEventListener('change', () => applyCatalogSelection(sel));
        if (typeof window.jQuery !== 'undefined') {
            window.jQuery(sel).on('change select2:select', function () {
                applyCatalogSelection(this);
            }).on('select2:clear', function () {
                tr.querySelector('.inp-nama').value = '';
                tr.querySelector('.inp-satuan').value = 'Buah';
                tr.querySelector('.inp-harga').value = '0';
                recalc();
            });
        }
        tr.querySelector('.inp-vol').addEventListener('input', recalc);
        tr.querySelector('.inp-harga').addEventListener('input', (e) => {
            formatInputRibuan(e.target);
            recalc();
        });
        tr.querySelector('.btn-del-row').addEventListener('click', () => {
            tr.remove();
            recalc();
        });
    };

    if (existingItems.length > 0) {
        existingItems.forEach((it) => addRow(it));
    } else {
        addRow();
    }
    recalc();
    btnAddRow.addEventListener('click', () => addRow());
})();
</script>
<?= $this->endSection(); ?>

