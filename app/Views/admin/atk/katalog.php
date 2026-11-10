<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="card card-outline card-primary">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
        <h3 class="card-title mb-2 mb-md-0"><i class="fas fa-tags mr-1"></i> Katalog Barang &amp; Harga ATK / Supplies</h3>
        <div class="card-tools d-flex flex-wrap" style="gap: 6px;">
            <?php if (! empty($permissions['export'])): ?>
                <a href="<?= site_url('/admin/atk/katalog/export'); ?>" class="btn btn-sm btn-success">
                    <i class="fas fa-file-excel mr-1"></i> Export Excel
                </a>
            <?php endif; ?>
            <?php if (! empty($permissions['import'])): ?>
                <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#modalImportKatalog">
                    <i class="fas fa-file-import mr-1"></i> Import Excel
                </button>
            <?php endif; ?>
            <?php if (! empty($permissions['add'])): ?>
                <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalTambahBarang">
                    <i class="fas fa-plus mr-1"></i> Tambah Barang
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="card-body">
        <form method="get" action="<?= site_url('/admin/atk/katalog'); ?>" class="form-row mb-3 align-items-end">
            <div class="col-md-3 mb-2">
                <label class="small font-weight-bold mb-1">Kategori</label>
                <select name="kategori" class="form-control form-control-sm">
                    <option value="">Semua Kategori</option>
                    <option value="ATK" <?= $filterKategori === 'ATK' ? 'selected' : ''; ?>>ATK</option>
                    <option value="Supplies Komputer" <?= $filterKategori === 'Supplies Komputer' ? 'selected' : ''; ?>>Supplies Komputer</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="small font-weight-bold mb-1">Tahun</label>
                <select name="tahun" class="form-control form-control-sm">
                    <option value="0">Semua Tahun</option>
                    <?php for ($y = date('Y') + 1; $y >= 2024; $y--): ?>
                        <option value="<?= $y; ?>" <?= (int) $filterTahun === $y ? 'selected' : ''; ?>><?= $y; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <button type="submit" class="btn btn-sm btn-info"><i class="fas fa-filter mr-1"></i> Filter</button>
                <a href="<?= site_url('/admin/atk/katalog'); ?>" class="btn btn-sm btn-default"><i class="fas fa-sync-alt"></i></a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm js-datatable" data-order='[[0, "asc"]]'>
                <thead class="thead-light">
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th>Kategori</th>
                        <th>Nama Barang</th>
                        <th>Satuan</th>
                        <th class="text-right">Harga Satuan</th>
                        <th>Tahun</th>
                        <th>Penyedia</th>
                        <th class="text-center">Status</th>
                        <th style="width: 110px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($barangList as $i => $b): ?>
                        <tr>
                            <td class="text-center"><?= $i + 1; ?></td>
                            <td>
                                <span class="badge <?= $b['kategori'] === 'Supplies Komputer' ? 'badge-info' : 'badge-dark'; ?>">
                                    <?= esc($b['kategori']); ?>
                                </span>
                            </td>
                            <td>
                                <strong><?= esc($b['nama_barang']); ?></strong>
                                <?php if (! empty($b['keterangan'])): ?>
                                    <div class="small text-muted"><?= esc($b['keterangan']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= esc($b['satuan']); ?></td>
                            <td class="text-right font-weight-bold">Rp <?= number_format((float) $b['harga_satuan'], 0, ',', '.'); ?></td>
                            <td class="text-center"><?= (int) $b['tahun_anggaran']; ?></td>
                            <td class="small"><?= esc($b['nama_perusahaan'] ?: '-'); ?></td>
                            <td class="text-center">
                                <?= ! empty($b['is_active']) ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-secondary">Nonaktif</span>'; ?>
                            </td>
                            <td class="text-center">
                                <?php if (! empty($permissions['edit'])): ?>
                                    <button type="button" class="btn btn-xs btn-warning btn-edit-barang"
                                        data-id="<?= (int) $b['id']; ?>"
                                        data-penyedia="<?= (int) ($b['penyedia_id'] ?? 0); ?>"
                                        data-tahun="<?= (int) $b['tahun_anggaran']; ?>"
                                        data-kategori="<?= esc($b['kategori']); ?>"
                                        data-nama="<?= esc($b['nama_barang']); ?>"
                                        data-satuan="<?= esc($b['satuan']); ?>"
                                        data-harga="<?= (int) round((float) $b['harga_satuan']); ?>"
                                        data-ket="<?= esc($b['keterangan'] ?? ''); ?>"
                                        data-active="<?= (int) $b['is_active']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                <?php endif; ?>
                                <?php if (! empty($permissions['delete'])): ?>
                                    <form method="post" action="<?= site_url('/admin/atk/katalog/' . (int) $b['id'] . '/hapus'); ?>" class="d-inline" data-confirm-title="Hapus Barang?" data-confirm-text="Item katalog akan dihapus.">
                                        <?= csrf_field(); ?>
                                        <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (! empty($permissions['add'])): ?>
<div class="modal fade" id="modalTambahBarang" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="<?= site_url('/admin/atk/katalog/tambah'); ?>" data-skip-confirm="1">
                <?= csrf_field(); ?>
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Tambah Barang Katalog</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="small font-weight-bold">Penyedia</label>
                        <select name="penyedia_id" class="form-control form-control-sm">
                            <?php foreach ($penyediaList as $pen): ?>
                                <option value="<?= (int) $pen['id']; ?>"><?= esc($pen['nama_perusahaan']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="col-6 form-group">
                            <label class="small font-weight-bold">Kategori</label>
                            <select name="kategori" class="form-control form-control-sm">
                                <option value="ATK">ATK</option>
                                <option value="Supplies Komputer">Supplies Komputer</option>
                            </select>
                        </div>
                        <div class="col-6 form-group">
                            <label class="small font-weight-bold">Tahun</label>
                            <input type="number" name="tahun_anggaran" class="form-control form-control-sm" value="<?= (int) date('Y'); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Nama Barang</label>
                        <input type="text" name="nama_barang" class="form-control form-control-sm" required>
                    </div>
                    <div class="form-row">
                        <div class="col-6 form-group">
                            <label class="small font-weight-bold">Satuan</label>
                            <input type="text" name="satuan" class="form-control form-control-sm" value="Buah" required>
                        </div>
                        <div class="col-6 form-group">
                            <label class="small font-weight-bold">Harga Satuan (Rp)</label>
                            <input type="number" name="harga_satuan" class="form-control form-control-sm" required min="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Keterangan</label>
                        <input type="text" name="keterangan" class="form-control form-control-sm" value="Harga termasuk pajak">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (! empty($permissions['edit'])): ?>
<div class="modal fade" id="modalEditBarang" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" id="formEditBarang" action="" data-skip-confirm="1">
                <?= csrf_field(); ?>
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Ubah Barang Katalog</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="small font-weight-bold">Penyedia</label>
                        <select name="penyedia_id" id="ebPenyedia" class="form-control form-control-sm">
                            <?php foreach ($penyediaList as $pen): ?>
                                <option value="<?= (int) $pen['id']; ?>"><?= esc($pen['nama_perusahaan']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="col-6 form-group">
                            <label class="small font-weight-bold">Kategori</label>
                            <select name="kategori" id="ebKategori" class="form-control form-control-sm">
                                <option value="ATK">ATK</option>
                                <option value="Supplies Komputer">Supplies Komputer</option>
                            </select>
                        </div>
                        <div class="col-6 form-group">
                            <label class="small font-weight-bold">Tahun</label>
                            <input type="number" name="tahun_anggaran" id="ebTahun" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Nama Barang</label>
                        <input type="text" name="nama_barang" id="ebNama" class="form-control form-control-sm" required>
                    </div>
                    <div class="form-row">
                        <div class="col-6 form-group">
                            <label class="small font-weight-bold">Satuan</label>
                            <input type="text" name="satuan" id="ebSatuan" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-6 form-group">
                            <label class="small font-weight-bold">Harga Satuan (Rp)</label>
                            <input type="number" name="harga_satuan" id="ebHarga" class="form-control form-control-sm" required min="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Keterangan</label>
                        <input type="text" name="keterangan" id="ebKet" class="form-control form-control-sm">
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Status</label>
                        <select name="is_active" id="ebActive" class="form-control form-control-sm">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning btn-sm font-weight-bold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (! empty($permissions['import'])): ?>
<div class="modal fade" id="modalImportKatalog" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="<?= site_url('/admin/atk/katalog/import'); ?>" enctype="multipart/form-data" data-skip-confirm="1">
                <?= csrf_field(); ?>
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Import Katalog Barang dari Excel</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="small">Unduh <a href="<?= site_url('/admin/atk/katalog/template'); ?>"><strong>Template Excel</strong></a> terlebih dahulu untuk format kolom yang sesuai.</p>
                    <div class="form-group">
                        <label class="small font-weight-bold">Penyedia</label>
                        <select name="penyedia_id" class="form-control form-control-sm">
                            <?php foreach ($penyediaList as $pen): ?>
                                <option value="<?= (int) $pen['id']; ?>"><?= esc($pen['nama_perusahaan']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">File Excel (.xlsx / .xls)</label>
                        <input type="file" name="file_excel" class="form-control-file" accept=".xlsx,.xls" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info btn-sm">Import Sekarang</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script>
document.querySelectorAll('.btn-edit-barang').forEach((btn) => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        document.getElementById('formEditBarang').action = '<?= site_url('/admin/atk/katalog'); ?>/' + id + '/ubah';
        document.getElementById('ebPenyedia').value = btn.dataset.penyedia || '';
        document.getElementById('ebKategori').value = btn.dataset.kategori || 'ATK';
        document.getElementById('ebTahun').value = btn.dataset.tahun || '2026';
        document.getElementById('ebNama').value = btn.dataset.nama || '';
        document.getElementById('ebSatuan').value = btn.dataset.satuan || 'Buah';
        document.getElementById('ebHarga').value = btn.dataset.harga || '0';
        document.getElementById('ebKet').value = btn.dataset.ket || '';
        document.getElementById('ebActive').value = btn.dataset.active || '1';
        $('#modalEditBarang').modal('show');
    });
});
</script>
<?= $this->endSection(); ?>

