<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">

    <!-- Summary Widgets -->
    <div class="row mb-3">
        <div class="col-12 col-sm-6 col-md-4">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #17a2b8;">
                <span class="info-box-icon bg-info elevation-1" style="border-radius: 8px;"><i class="fas fa-school"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Total Sekolah Binaan</span>
                    <span class="info-box-number text-dark" style="font-size: 1.35rem;"><?= number_format((int) ($totalSekolah ?? 0)); ?></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #6f42c1;">
                <span class="info-box-icon bg-purple elevation-1" style="border-radius: 8px; background-color: #6f42c1; color: white;"><i class="fas fa-cubes"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Paket Pembangunan</span>
                    <span class="info-box-number text-dark" style="font-size: 1.35rem;"><?= number_format((int) ($totalPaket ?? 0)); ?></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <div class="info-box shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #28a745;">
                <span class="info-box-icon bg-success elevation-1" style="border-radius: 8px;"><i class="fas fa-map-marker-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">Wilayah Kabupaten</span>
                    <span class="info-box-number text-success" style="font-size: 1.35rem;"><?= count($kabupatens ?? []); ?> Kab/Kota</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm mb-3" style="border-radius: 10px; border: 1px solid #e9eef5;">
        <div class="card-body py-3">
            <form method="get" action="<?= site_url('admin/inventaris/sekolah'); ?>">
                <div class="row align-items-end">
                    <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-cube mr-1"></i> Paket Proyek</label>
                        <select name="paket_id" class="form-control form-control-sm" style="border-radius: 6px;">
                            <option value="">Semua Paket</option>
                            <?php foreach ($pakets as $p): ?>
                                <option value="<?= esc($p['id']); ?>" <?= ($filterPaketId == $p['id']) ? 'selected' : ''; ?>>
                                    <?= esc($p['nama_paket']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-map mr-1"></i> Kabupaten</label>
                        <select name="kabupaten" class="form-control form-control-sm" style="border-radius: 6px;">
                            <option value="">Semua Kabupaten</option>
                            <?php foreach ($kabupatens as $kab): ?>
                                <option value="<?= esc($kab); ?>" <?= ($filterKabupaten === $kab) ? 'selected' : ''; ?>><?= esc($kab); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-search mr-1"></i> Kata Kunci</label>
                        <input type="text" name="keyword" class="form-control form-control-sm" placeholder="Nama sekolah / NPSN..." value="<?= esc($keyword ?? ''); ?>" style="border-radius: 6px;">
                    </div>
                    <div class="col-md-2 col-sm-6 d-flex" style="gap: 6px;">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill shadow-sm" style="border-radius: 6px;">
                            <i class="fas fa-filter mr-1"></i> Cari
                        </button>
                        <a href="<?= site_url('admin/inventaris/sekolah'); ?>" class="btn btn-outline-secondary btn-sm shadow-sm" style="border-radius: 6px;" title="Reset Filter">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Card -->
    <div class="card shadow-sm" style="border: 1px solid #e9eef5; border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white py-3" style="border-bottom: 1px solid #e9eef5;">
            <div class="d-flex flex-wrap justify-content-between align-items-center w-100">
                <div>
                    <h3 class="card-title mb-0 font-weight-bold text-dark" style="font-size: 1.15rem; line-height: 1.8;">
                        <i class="fas fa-school text-info mr-2"></i>Daftar Inventaris Sarana & Prasarana Sekolah
                    </h3>
                    <small class="text-muted d-block mt-1">Pendataan sarana dan prasarana hasil rehabilitasi / renovasi sekolah binaan Satker PPS Riau</small>
                </div>
                <div class="card-tools m-0">
                    <span class="badge badge-info px-3 py-2" style="font-size: 0.85rem; border-radius: 6px;">
                        <i class="fas fa-database mr-1"></i> <?= count($items ?? []); ?> Sekolah Terdata
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-striped w-100 js-datatable" style="border-radius: 8px;">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 45px;" class="text-center align-middle">#</th>
                            <th class="align-middle">NPSN / NSM</th>
                            <th class="align-middle">Nama Sekolah</th>
                            <th class="align-middle">Jenis</th>
                            <th class="align-middle">Paket Pembangunan</th>
                            <th class="align-middle">Kabupaten</th>
                            <th class="align-middle">Kecamatan</th>
                            <th class="text-center align-middle" style="width: 140px;">Status Sarpras</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach (($items ?? []) as $item): ?>
                            <tr>
                                <td class="text-center align-middle"><?= $no++; ?></td>
                                <td class="align-middle">
                                    <span class="font-weight-bold text-dark"><?= esc($item['npsn'] ?: '-'); ?></span>
                                    <?php if (! empty($item['nsm'])): ?>
                                        <small class="text-muted d-block">NSM: <?= esc($item['nsm']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="align-middle">
                                    <span class="font-weight-bold text-primary"><?= esc($item['nama']); ?></span>
                                </td>
                                <td class="align-middle">
                                    <span class="badge badge-light border"><?= esc($item['jenis'] ?: 'Sekolah'); ?></span>
                                </td>
                                <td class="align-middle">
                                    <?php if (! empty($item['nama_paket'])): ?>
                                        <span class="badge badge-secondary px-2 py-1"><?= esc($item['nama_paket']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted small">- Belum Ditautkan -</span>
                                    <?php endif; ?>
                                </td>
                                <td class="align-middle"><?= esc($item['kabupaten'] ?: '-'); ?></td>
                                <td class="align-middle"><?= esc($item['kecamatan'] ?: '-'); ?></td>
                                <td class="text-center align-middle">
                                    <span class="badge badge-success px-2 py-1">
                                        <i class="fas fa-check-circle mr-1"></i> Terinventarisir
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
