<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
$canAdd    = ! empty($permissions['add']);
$canEdit   = ! empty($permissions['edit']);
$canDelete = ! empty($permissions['delete']);
$activePaketId = (int) ($paket['id'] ?? 0);
$activeSlug    = (string) ($paket['slug'] ?? 'sppg');
?>
<div class="container-fluid">

    <!-- Paket Switcher Bar (Paket 1 s/d Paket 4 LHA No. 9) -->
    <div class="card shadow-sm border-0 mb-3" style="border-radius: 12px;">
        <div class="card-body py-3 px-4 d-flex flex-wrap justify-content-between align-items-center">
            <div class="d-flex align-items-center mb-2 mb-lg-0">
                <span class="font-weight-bold text-dark mr-3">
                    <i class="fas fa-layer-group text-success mr-1"></i> Pilih Paket LHA No. 9:
                </span>
                <div class="nav nav-pills flex-wrap">
                    <?php foreach ($paketList as $pItem):
                        $isAct = ((int) $pItem['id'] === $activePaketId);
                        $lhaNo = (int) ($pItem['lha_no'] ?? 1);
                    ?>
                        <a href="<?= base_url('admin/swakelola/realisasi?paket_id=' . $pItem['id']); ?>"
                           class="btn btn-sm <?= $isAct ? 'btn-success font-weight-bold shadow-sm' : 'btn-outline-secondary'; ?> mr-2 mb-1"
                           style="border-radius: 20px;">
                            Paket <?= $lhaNo; ?> (<?= esc($pItem['pic_label'] ?? $pItem['pic_nama']); ?>)
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <select class="form-control form-control-sm" style="min-width: 260px; border-radius: 8px;" onchange="if(this.value) window.location.href=this.value;">
                    <?php foreach ($paketList as $pItem):
                        $isAct = ((int) $pItem['id'] === $activePaketId);
                        $lhaNo = (int) ($pItem['lha_no'] ?? 1);
                    ?>
                        <option value="<?= base_url('admin/swakelola/realisasi?paket_id=' . $pItem['id']); ?>" <?= $isAct ? 'selected' : ''; ?>>
                            Paket <?= $lhaNo; ?> — <?= esc($pItem['nama_kegiatan']); ?> (PIC: <?= esc($pItem['pic_label'] ?? '-'); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Top Banner -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; background: linear-gradient(135deg, #065f46 0%, #022c22 100%); color: #ffffff;">
        <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <div class="mb-1">
                    <span class="badge badge-warning text-dark font-weight-bold px-2 py-1 mr-2">
                        PAKET <?= (int) ($paket['lha_no'] ?? 1); ?> — PIC: <?= esc($paket['pic_nama'] ?? '-'); ?>
                    </span>
                </div>
                <h4 class="font-weight-bold mb-1 text-white">
                    <i class="fas fa-receipt mr-2 text-warning"></i> Realisasi Anggaran Belanja Swakelola
                </h4>
                <p class="text-white-50 mb-0 small">
                    <?= esc($paket['nama_kegiatan'] ?? 'Kegiatan Swakelola'); ?> — TA <?= esc($paket['tahun_anggaran'] ?? '2025'); ?>
                </p>
                <div class="mt-2 text-white-50 small">
                    <span class="mr-3"><i class="fas fa-barcode mr-1"></i> Kode POK: <strong><?= esc($paket['kode_paket'] ?? '-'); ?></strong></span>
                    <span class="mr-3"><i class="fas fa-money-bill-wave mr-1"></i> Pagu POK Rev 16: <strong>Rp <?= number_format((float)($paket['pagu_anggaran'] ?? 0), 0, ',', '.'); ?></strong></span>
                    <span><i class="fas fa-wallet mr-1 text-warning"></i> Total Realisasi SP2D Definitif: <strong>Rp <?= number_format($totalRealisasi, 0, ',', '.'); ?></strong></span>
                </div>
            </div>
            <div class="mt-3 mt-md-0 d-flex flex-wrap">
                <?php if ($canAdd): ?>
                    <button type="button" class="btn btn-light text-success font-weight-bold shadow-sm px-3 py-2 mr-2" style="border-radius: 8px;" id="btnAddRealisasi">
                        <i class="fas fa-plus-circle mr-1"></i> Tambah Realisasi SPM
                    </button>
                <?php endif; ?>
                <a href="<?= base_url('admin/swakelola/laporan?paket_id=' . $activePaketId); ?>" class="btn btn-warning text-dark font-weight-bold shadow-sm px-3 py-2" style="border-radius: 8px;">
                    <i class="fas fa-chart-pie mr-1"></i> Rekapitulasi RAB vs Realisasi
                </a>
            </div>
        </div>
    </div>

    <!-- Summary KPI Cards Per Akun -->
    <div class="row mb-4">
        <?php foreach ($perAkun as $kode => $info): ?>
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm border-0 h-100" style="border-radius: 10px; border-left: 5px solid #059669 !important;">
                    <div class="card-body py-3">
                        <small class="text-muted font-weight-bold d-block mb-1">Akun <?= esc($kode); ?> - <?= esc($info['nama']); ?></small>
                        <h4 class="font-weight-bold text-dark mb-1">Rp <?= number_format((float) $info['total'], 0, ',', '.'); ?></h4>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-success font-weight-bold">
                                <i class="fas fa-check-circle mr-1"></i> <?= (int) $info['count']; ?> Transaksi SPM/SP2D
                            </small>
                            <?php if (! empty($info['pagu'])): ?>
                                <small class="text-muted">Pagu: Rp <?= number_format((float) $info['pagu'], 0, ',', '.'); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Tabel Rincian Voucher SPM & SP2D -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table mr-2"></i> Rincian Pembayaran Surat Perintah Membayar (SPM) dan SP2D
            </h6>
            <a href="<?= base_url('admin/swakelola/paket'); ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar Paket
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-light">
                        <tr style="font-size: 0.85rem;">
                            <th class="text-center" style="width: 45px;">No</th>
                            <th>Akun</th>
                            <th>Uraian Pengeluaran Belanja</th>
                            <th class="text-center">Nomor SPM</th>
                            <th class="text-center">Tanggal SPM</th>
                            <th class="text-center">Nomor SP2D</th>
                            <th class="text-right">Jumlah Bayar (Rp)</th>
                            <th>Nama Berkas Bukti (PDF)</th>
                            <?php if ($canEdit || $canDelete): ?>
                                <th class="text-center" style="width: 110px;">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($realisasi)): ?>
                            <tr>
                                <td colspan="<?= ($canEdit || $canDelete) ? 9 : 8; ?>" class="text-center py-4 text-muted">
                                    Belum ada data realisasi belanja SPM/SP2D untuk paket ini.
                                    <?php if ($canAdd): ?>
                                        Silakan klik tombol <strong>Tambah Realisasi SPM</strong> di atas.
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($realisasi as $idx => $r): ?>
                                <tr>
                                    <td class="text-center font-weight-bold align-middle"><?= $idx + 1; ?></td>
                                    <td class="align-middle">
                                        <span class="badge badge-secondary"><?= esc($r['kode_akun']); ?></span>
                                    </td>
                                    <td class="font-weight-bold text-dark align-middle">
                                        <?= esc($r['uraian_belanja']); ?>
                                    </td>
                                    <td class="text-center font-weight-bold text-primary align-middle">
                                        <?= esc($r['nomor_spm']); ?>
                                    </td>
                                    <td class="text-center text-muted align-middle">
                                        <?= ! empty($r['tanggal_spm']) ? date('d/m/Y', strtotime($r['tanggal_spm'])) : '-'; ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <small class="font-weight-bold text-dark"><?= esc($r['nomor_sp2d']); ?></small>
                                    </td>
                                    <td class="text-right font-weight-bold text-success align-middle">
                                        Rp <?= number_format((float)$r['jumlah_bayar'], 0, ',', '.'); ?>
                                    </td>
                                    <td class="align-middle">
                                        <?php if (! empty($r['file_bukti'])): ?>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-dark font-weight-bold mr-2">
                                                    <i class="fas fa-file-pdf text-danger mr-1"></i> <?= esc($r['file_bukti']); ?>
                                                </small>
                                                <div class="btn-group btn-group-sm flex-shrink-0">
                                                    <a href="<?= base_url('uploads/swakelola/' . $activeSlug . '/' . rawurlencode($r['file_bukti'])); ?>" target="_blank" class="btn btn-xs btn-info py-0 px-2" title="Buka Pratinjau PDF">
                                                        <i class="fas fa-eye mr-1"></i> Lihat
                                                    </a>
                                                    <a href="<?= base_url('admin/swakelola/dokumen/download/0') . '?file=' . rawurlencode($r['file_bukti']); ?>" class="btn btn-xs btn-outline-primary py-0 px-2" title="Unduh Bukti SPM">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <small class="text-muted font-italic">Belum ada berkas bukti</small>
                                        <?php endif; ?>
                                    </td>
                                    <?php if ($canEdit || $canDelete): ?>
                                        <td class="text-center align-middle">
                                            <div class="btn-group btn-group-sm">
                                                <?php if ($canEdit): ?>
                                                    <button type="button"
                                                        class="btn btn-outline-primary btn-edit-realisasi"
                                                        data-id="<?= $r['id']; ?>"
                                                        data-akun="<?= esc($r['kode_akun'], 'attr'); ?>"
                                                        data-uraian="<?= esc($r['uraian_belanja'], 'attr'); ?>"
                                                        data-spm="<?= esc($r['nomor_spm'] ?? '', 'attr'); ?>"
                                                        data-tgl="<?= esc($r['tanggal_spm'] ?? '', 'attr'); ?>"
                                                        data-sp2d="<?= esc($r['nomor_sp2d'] ?? '', 'attr'); ?>"
                                                        data-jumlah="<?= (float) $r['jumlah_bayar']; ?>"
                                                        data-bukti="<?= esc($r['file_bukti'] ?? '', 'attr'); ?>"
                                                        title="Ubah Data Realisasi">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <?php if ($canDelete): ?>
                                                    <form method="post" action="<?= base_url('admin/swakelola/realisasi/' . $r['id'] . '/hapus'); ?>" class="d-inline" onsubmit="return confirm('Hapus data realisasi SPM ini?');">
                                                        <?= csrf_field(); ?>
                                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus Realisasi">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="bg-light font-weight-bold">
                        <tr>
                            <td colspan="6" class="text-right text-uppercase py-3">Total Realisasi Anggaran (SP2D Definitif):</td>
                            <td class="text-right text-success py-3" style="font-size: 1.1rem;">
                                Rp <?= number_format($totalRealisasi, 0, ',', '.'); ?>
                            </td>
                            <td colspan="<?= ($canEdit || $canDelete) ? 2 : 1; ?>"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- MODAL TAMBAH / EDIT REALISASI SPM -->
<?php if ($canAdd || $canEdit): ?>
<div class="modal fade" id="modalRealisasiSpm" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow border-0" style="border-radius: 12px;">
            <form method="post" action="<?= base_url('admin/swakelola/realisasi/simpan'); ?>" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <input type="hidden" name="id" id="realId" value="0">
                <input type="hidden" name="paket_id" value="<?= $activePaketId; ?>">
                <div class="modal-header bg-success text-white py-3">
                    <h6 class="modal-title font-weight-bold" id="modalRealisasiTitle">
                        <i class="fas fa-receipt mr-2"></i> Tambah Realisasi SPM &amp; SP2D
                    </h6>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="font-weight-bold small">Kode Akun Belanja (POK Rev 16)</label>
                        <select name="kode_akun" id="realKodeAkun" class="form-control form-control-sm" required>
                            <?php if (! empty($rabAccounts)): ?>
                                <?php foreach ($rabAccounts as $kAkun => $accInfo): ?>
                                    <option value="<?= esc($kAkun); ?>"><?= esc($kAkun); ?> — <?= esc($accInfo['nama']); ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <option value="521211">521211 — Belanja Bahan</option>
                            <option value="521213">521213 — Belanja Honor Output Kegiatan</option>
                            <option value="521811">521811 — Belanja Barang Persediaan Barang Konsumsi</option>
                            <option value="522191">522191 — Belanja Jasa Lainnya</option>
                            <option value="524111">524111 — Belanja Perjalanan Dinas Biasa</option>
                            <option value="524113">524113 — Belanja Perjalanan Dinas Dalam Kota</option>
                            <option value="524114">524114 — Belanja Perjalanan Dinas Paket Meeting Dalam Kota</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold small">Uraian Pengeluaran Belanja</label>
                        <textarea name="uraian_belanja" id="realUraian" rows="2" class="form-control form-control-sm" required placeholder="Contoh: Belanja Bahan ATK Kegiatan ..."></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold small">Nomor SPM</label>
                            <input type="text" name="nomor_spm" id="realNomorSpm" class="form-control form-control-sm" placeholder="Contoh: 00415A">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold small">Tanggal SPM</label>
                            <input type="date" name="tanggal_spm" id="realTanggalSpm" class="form-control form-control-sm" value="<?= date('Y-m-d'); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold small">Nomor SP2D</label>
                            <input type="text" name="nomor_sp2d" id="realNomorSp2d" class="form-control form-control-sm" placeholder="Contoh: 259991311235904">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold small">Jumlah Bayar (Rp)</label>
                            <input type="number" step="1" name="jumlah_bayar" id="realJumlahBayar" class="form-control form-control-sm" required placeholder="0">
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold small">Unggah Berkas Bukti SPM / SP2D / Kuitansi (.pdf)</label>
                        <input type="file" name="file_bukti" class="form-control-file border p-2 rounded" accept=".pdf,.docx,.xlsx">
                        <small class="text-muted d-block mt-1" id="realCurrentBukti">Opsional: Pilih berkas PDF bukti pengeluaran.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-success font-weight-bold">
                        <i class="fas fa-save mr-1"></i> Simpan Realisasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
$(document).ready(function() {
    $('#btnAddRealisasi').on('click', function() {
        $('#modalRealisasiTitle').html('<i class="fas fa-plus-circle mr-2"></i> Tambah Realisasi SPM &amp; SP2D');
        $('#realId').val('0');
        $('#realUraian').val('');
        $('#realNomorSpm').val('');
        $('#realTanggalSpm').val('<?= date('Y-m-d'); ?>');
        $('#realNomorSp2d').val('');
        $('#realJumlahBayar').val('');
        $('#realCurrentBukti').text('Opsional: Pilih berkas PDF bukti pengeluaran.');
        $('#modalRealisasiSpm').modal('show');
    });

    $(document).on('click', '.btn-edit-realisasi', function() {
        $('#modalRealisasiTitle').html('<i class="fas fa-edit mr-2"></i> Ubah Realisasi SPM &amp; SP2D');
        $('#realId').val($(this).data('id'));
        $('#realKodeAkun').val($(this).data('akun'));
        $('#realUraian').val($(this).data('uraian'));
        $('#realNomorSpm').val($(this).data('spm'));
        $('#realTanggalSpm').val($(this).data('tgl'));
        $('#realNomorSp2d').val($(this).data('sp2d'));
        $('#realJumlahBayar').val($(this).data('jumlah'));
        const bukti = $(this).data('bukti') || '';
        $('#realCurrentBukti').text(bukti ? ('Berkas saat ini: ' + bukti + ' (Pilih file baru untuk mengganti)') : 'Belum ada berkas bukti terunggah.');
        $('#modalRealisasiSpm').modal('show');
    });
});
</script>
<?= $this->endSection(); ?>
