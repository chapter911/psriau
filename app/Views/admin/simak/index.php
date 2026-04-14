<?= $this->extend('layouts/app'); ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h4><?= $title ?></h4>
        </div>
        <div class="col-md-6 text-end">
            <?php if ($canCreate) : ?>
                <a href="<?= site_url('admin/paket/simak/create') ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Tambah SIMAK
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')) : ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- FILTER -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="get" class="row g-2">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama paket/ nomor kontrak..." value="<?= $search ?>">
                </div>
                <div class="col-md-4">
                    <input type="text" name="tahun" class="form-control form-control-sm" placeholder="Tahun anggaran..." value="<?= $tahun ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary btn-sm w-100">
                        <i class="fas fa-search"></i> Cari
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- TABLE -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Nama Paket</th>
                        <th>Satker</th>
                        <th>Tahun Anggaran</th>
                        <th>Nomor Kontrak</th>
                        <th>Status</th>
                        <th>Dibuat Oleh</th>
                        <th>Tanggal Buat</th>
                        <th style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)) : ?>
                        <tr>
                            <td colspan="9" class="text-center py-3">Tidak ada data SIMAK</td>
                        </tr>
                    <?php else : ?>
                        <?php $no = 1; foreach ($items as $item) : ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <a href="<?= site_url('admin/paket/simak/' . $item['id']) ?>" class="text-decoration-none">
                                        <?= esc($item['nama_paket']) ?>
                                    </a>
                                </td>
                                <td><?= esc($item['satker']) ?></td>
                                <td><?= esc($item['tahun_anggaran']) ?></td>
                                <td><?= esc($item['nomor_kontrak']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $item['status'] === 'draft' ? 'secondary' : 'primary' ?>">
                                        <?= ucfirst($item['status']) ?>
                                    </span>
                                </td>
                                <td><?= esc($item['created_by']) ?></td>
                                <td><?= $item['created_at'] ? date('d-m-Y H:i:s', strtotime($item['created_at'])) : '-' ?></td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="<?= site_url('admin/paket/simak/' . $item['id']) ?>">
                                                    <i class="fas fa-eye"></i> Lihat
                                                </a>
                                            </li>
                                            <?php if ($canEdit) : ?>
                                                <li>
                                                    <a class="dropdown-item" href="<?= site_url('admin/paket/simak/' . $item['id'] . '/edit') ?>">
                                                        <i class="fas fa-edit"></i> Ubah
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- PAGINATION -->
    <?php if (!empty($pager)) : ?>
        <div class="mt-3">
            <?= $pager->links('default', 'bootstrap') ?>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
