<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">

    <!-- Header Banner -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; background: linear-gradient(135deg, #0d3b66 0%, #001e3d 100%); color: #ffffff;">
        <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <h4 class="font-weight-bold mb-1 text-white">
                    <i class="fas fa-clipboard-check mr-2 text-warning"></i> Administrasi Swakelola Satker PPS Riau
                </h4>
                <p class="text-white-50 mb-0 small">
                    Pengelolaan, monitoring checklist 28 dokumen, dan akuntabilitas realisasi anggaran kegiatan Swakelola Tipe I.
                </p>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="<?= base_url('admin/swakelola/laporan/export-excel'); ?>" class="btn btn-success font-weight-bold shadow-sm px-3 py-2" style="border-radius: 8px;">
                    <i class="fas fa-file-excel mr-1"></i> Download Checklist Lengkap (.xlsx)
                </a>
            </div>
        </div>
    </div>

    <!-- Alert / Notifikasi -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle mr-2"></i> <?= session()->getFlashdata('success'); ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i> <?= session()->getFlashdata('error'); ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Table Paket Swakelola -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-tasks mr-2"></i> Daftar Paket Kegiatan Swakelola
            </h6>
            <span class="badge badge-primary px-3 py-2" style="border-radius: 20px;">
                Total: <?= count($paketList); ?> Paket Terdata
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th>Nama Kegiatan & Kode POK</th>
                            <th>TA & Tipe</th>
                            <th class="text-right">Pagu DIPA</th>
                            <th class="text-right">Realisasi (SPM)</th>
                            <th class="text-center">Serapan</th>
                            <th>Pejabat (PPK / Kasatker)</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" style="width: 180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($paketList)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">Belum ada paket swakelola terdaftar.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($paketList as $idx => $p): 
                                $pagu = (float) $p['pagu_anggaran'];
                                $real = (float) $p['realisasi_anggaran'];
                                $pct = $pagu > 0 ? round(($real / $pagu) * 100, 1) : 0;
                            ?>
                                <tr>
                                    <td class="text-center font-weight-bold"><?= $idx + 1; ?></td>
                                    <td>
                                        <div class="font-weight-bold text-dark"><?= esc($p['nama_kegiatan']); ?></div>
                                        <small class="text-muted"><i class="fas fa-barcode mr-1"></i> <?= esc($p['kode_paket']); ?></small><br>
                                        <small class="text-info"><i class="fas fa-map-marker-alt mr-1"></i> <?= esc($p['lokasi']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary"><?= esc($p['tahun_anggaran']); ?></span><br>
                                        <small class="font-weight-bold text-primary"><?= esc($p['tipe_swakelola']); ?></small>
                                    </td>
                                    <td class="text-right font-weight-bold">
                                        Rp <?= number_format($pagu, 0, ',', '.'); ?>
                                    </td>
                                    <td class="text-right font-weight-bold text-success">
                                        Rp <?= number_format($real, 0, ',', '.'); ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="font-weight-bold text-dark"><?= $pct; ?>%</div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-success" style="width: <?= min(100, $pct); ?>%"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="d-block"><strong>PPK:</strong> <?= esc($p['ppk_nama']); ?></small>
                                        <small class="text-muted d-block"><strong>Kasatker:</strong> <?= esc($p['kasatker_nama']); ?></small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-success px-2 py-1 font-weight-normal"><?= esc($p['status']); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= base_url('admin/swakelola/dokumen?paket_id=' . $p['id']); ?>" class="btn btn-outline-primary" title="Lihat 28 Dokumen Checklist">
                                                <i class="fas fa-check-double mr-1"></i> Dokumen
                                            </a>
                                            <a href="<?= base_url('admin/swakelola/realisasi?paket_id=' . $p['id']); ?>" class="btn btn-outline-success" title="Realisasi SPM & Kuitansi">
                                                <i class="fas fa-receipt mr-1"></i> Realisasi
                                            </a>
                                            <a href="<?= base_url('admin/swakelola/laporan?paket_id=' . $p['id']); ?>" class="btn btn-outline-info" title="Rekapitulasi RAB vs Realisasi">
                                                <i class="fas fa-chart-pie"></i>
                                            </a>
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

</div>
<?= $this->endSection(); ?>

