<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">

    <!-- Top Banner -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; background: linear-gradient(135deg, #065f46 0%, #022c22 100%); color: #ffffff;">
        <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <h4 class="font-weight-bold mb-1 text-white">
                    <i class="fas fa-receipt mr-2 text-warning"></i> Realisasi Anggaran Belanja Swakelola
                </h4>
                <p class="text-white-50 mb-0 small">
                    <?= esc($paket['nama_kegiatan'] ?? 'Kegiatan Swakelola'); ?> — TA <?= esc($paket['tahun_anggaran'] ?? '2025'); ?>
                </p>
                <div class="mt-2 text-white-50 small">
                    <span class="mr-3"><i class="fas fa-barcode mr-1"></i> Kode POK: <strong><?= esc($paket['kode_paket'] ?? '-'); ?></strong></span>
                    <span><i class="fas fa-wallet mr-1"></i> Total Realisasi SP2D Definitif: <strong>Rp <?= number_format($totalRealisasi, 0, ',', '.'); ?></strong></span>
                </div>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="<?= base_url('admin/swakelola/laporan'); ?>" class="btn btn-warning text-dark font-weight-bold shadow-sm px-3 py-2" style="border-radius: 8px;">
                    <i class="fas fa-chart-pie mr-1"></i> Lihat Rekapitulasi RAB vs Realisasi
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
                        <small class="text-muted font-weight-bold d-block mb-1">Akun <?= $kode; ?> - <?= esc($info['nama']); ?></small>
                        <h4 class="font-weight-bold text-dark mb-1">Rp <?= number_format($info['total'], 0, ',', '.'); ?></h4>
                        <small class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> <?= $info['count']; ?> Transaksi SPM/SP2D</small>
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
                            <th>Nama Berkas Bukti (Folder Bahan)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($realisasi)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Belum ada data realisasi belanja.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($realisasi as $idx => $r): ?>
                                <tr>
                                    <td class="text-center font-weight-bold"><?= $idx + 1; ?></td>
                                    <td>
                                        <span class="badge badge-secondary"><?= esc($r['kode_akun']); ?></span>
                                    </td>
                                    <td class="font-weight-bold text-dark">
                                        <?= esc($r['uraian_belanja']); ?>
                                    </td>
                                    <td class="text-center font-weight-bold text-primary">
                                        <?= esc($r['nomor_spm']); ?>
                                    </td>
                                    <td class="text-center text-muted">
                                        <?= date('d/m/Y', strtotime($r['tanggal_spm'])); ?>
                                    </td>
                                    <td class="text-center">
                                        <small class="font-weight-bold text-dark"><?= esc($r['nomor_sp2d']); ?></small>
                                    </td>
                                    <td class="text-right font-weight-bold text-success">
                                        Rp <?= number_format((float)$r['jumlah_bayar'], 0, ',', '.'); ?>
                                    </td>
                                    <td>
                                        <small class="text-muted"><i class="fas fa-file-pdf text-danger mr-1"></i> <?= esc($r['file_bukti']); ?></small>
                                    </td>
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
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

</div>
<?= $this->endSection(); ?>

