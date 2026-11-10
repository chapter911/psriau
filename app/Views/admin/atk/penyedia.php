<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="card card-outline card-primary">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title"><i class="fas fa-store mr-1"></i> Data Penyedia (Vendor) ATK &amp; Supplies</h3>
        <?php if (! empty($permissions['add'])): ?>
            <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalTambahPenyedia">
                <i class="fas fa-plus mr-1"></i> Tambah Penyedia
            </button>
        <?php endif; ?>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-hover table-sm js-datatable">
            <thead class="thead-light">
                <tr>
                    <th style="width: 40px;">No</th>
                    <th>Nama Perusahaan</th>
                    <th>Alamat</th>
                    <th>Kontak</th>
                    <th>Pimpinan / Direktur</th>
                    <th class="text-center">Status</th>
                    <th style="width: 110px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($penyediaList as $i => $p): ?>
                    <tr>
                        <td class="text-center"><?= $i + 1; ?></td>
                        <td>
                            <strong><?= esc($p['nama_perusahaan']); ?></strong>
                            <?php if (! empty($p['npwp'])): ?>
                                <div class="small text-muted">NPWP: <?= esc($p['npwp']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="small"><?= esc($p['alamat']); ?>, <?= esc($p['kota']); ?></td>
                        <td class="small">
                            <div><?= esc($p['telepon'] ?: '-'); ?></div>
                            <div class="text-muted"><?= esc($p['email'] ?: '-'); ?></div>
                        </td>
                        <td class="small">
                            <strong><?= esc($p['nama_direktur']); ?></strong>
                            <div class="text-muted"><?= esc($p['jabatan_pimpinan']); ?></div>
                        </td>
                        <td class="text-center">
                            <?= ! empty($p['is_active']) ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-secondary">Nonaktif</span>'; ?>
                        </td>
                        <td class="text-center">
                            <?php if (! empty($permissions['edit'])): ?>
                                <button type="button" class="btn btn-xs btn-warning btn-edit-penyedia"
                                    data-id="<?= (int) $p['id']; ?>"
                                    data-nama="<?= esc($p['nama_perusahaan']); ?>"
                                    data-alamat="<?= esc($p['alamat']); ?>"
                                    data-kota="<?= esc($p['kota']); ?>"
                                    data-telepon="<?= esc($p['telepon']); ?>"
                                    data-email="<?= esc($p['email']); ?>"
                                    data-direktur="<?= esc($p['nama_direktur']); ?>"
                                    data-jabatan="<?= esc($p['jabatan_pimpinan']); ?>"
                                    data-npwp="<?= esc($p['npwp']); ?>"
                                    data-active="<?= (int) $p['is_active']; ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                            <?php endif; ?>
                            <?php if (! empty($permissions['delete'])): ?>
                                <form method="post" action="<?= site_url('/admin/atk/penyedia/' . (int) $p['id'] . '/hapus'); ?>" class="d-inline" data-confirm-title="Hapus Penyedia?" data-confirm-text="Data penyedia akan dihapus.">
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

<?php if (! empty($permissions['add'])): ?>
<div class="modal fade" id="modalTambahPenyedia" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="<?= site_url('/admin/atk/penyedia/tambah'); ?>" data-skip-confirm="1">
                <?= csrf_field(); ?>
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Tambah Penyedia</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group"><label class="small font-weight-bold">Nama Perusahaan</label><input type="text" name="nama_perusahaan" class="form-control form-control-sm" required></div>
                    <div class="form-group"><label class="small font-weight-bold">Alamat</label><textarea name="alamat" rows="2" class="form-control form-control-sm"></textarea></div>
                    <div class="form-row">
                        <div class="col-6 form-group"><label class="small font-weight-bold">Kota</label><input type="text" name="kota" class="form-control form-control-sm" value="Pekanbaru"></div>
                        <div class="col-6 form-group"><label class="small font-weight-bold">NPWP</label><input type="text" name="npwp" class="form-control form-control-sm"></div>
                    </div>
                    <div class="form-row">
                        <div class="col-6 form-group"><label class="small font-weight-bold">Telepon / WA</label><input type="text" name="telepon" class="form-control form-control-sm"></div>
                        <div class="col-6 form-group"><label class="small font-weight-bold">Email</label><input type="email" name="email" class="form-control form-control-sm"></div>
                    </div>
                    <div class="form-row">
                        <div class="col-7 form-group"><label class="small font-weight-bold">Nama Pimpinan / Direktur</label><input type="text" name="nama_direktur" class="form-control form-control-sm"></div>
                        <div class="col-5 form-group"><label class="small font-weight-bold">Jabatan</label><input type="text" name="jabatan_pimpinan" class="form-control form-control-sm" value="Direktur"></div>
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
<div class="modal fade" id="modalEditPenyedia" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" id="formEditPenyedia" action="" data-skip-confirm="1">
                <?= csrf_field(); ?>
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Ubah Data Penyedia</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group"><label class="small font-weight-bold">Nama Perusahaan</label><input type="text" name="nama_perusahaan" id="epNama" class="form-control form-control-sm" required></div>
                    <div class="form-group"><label class="small font-weight-bold">Alamat</label><textarea name="alamat" id="epAlamat" rows="2" class="form-control form-control-sm"></textarea></div>
                    <div class="form-row">
                        <div class="col-6 form-group"><label class="small font-weight-bold">Kota</label><input type="text" name="kota" id="epKota" class="form-control form-control-sm"></div>
                        <div class="col-6 form-group"><label class="small font-weight-bold">NPWP</label><input type="text" name="npwp" id="epNpwp" class="form-control form-control-sm"></div>
                    </div>
                    <div class="form-row">
                        <div class="col-6 form-group"><label class="small font-weight-bold">Telepon / WA</label><input type="text" name="telepon" id="epTel" class="form-control form-control-sm"></div>
                        <div class="col-6 form-group"><label class="small font-weight-bold">Email</label><input type="email" name="email" id="epEmail" class="form-control form-control-sm"></div>
                    </div>
                    <div class="form-row">
                        <div class="col-7 form-group"><label class="small font-weight-bold">Nama Pimpinan / Direktur</label><input type="text" name="nama_direktur" id="epDir" class="form-control form-control-sm"></div>
                        <div class="col-5 form-group"><label class="small font-weight-bold">Jabatan</label><input type="text" name="jabatan_pimpinan" id="epJab" class="form-control form-control-sm"></div>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Status</label>
                        <select name="is_active" id="epActive" class="form-control form-control-sm">
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
<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script>
document.querySelectorAll('.btn-edit-penyedia').forEach((btn) => {
    btn.addEventListener('click', () => {
        document.getElementById('formEditPenyedia').action = '<?= site_url('/admin/atk/penyedia'); ?>/' + btn.dataset.id + '/ubah';
        document.getElementById('epNama').value = btn.dataset.nama || '';
        document.getElementById('epAlamat').value = btn.dataset.alamat || '';
        document.getElementById('epKota').value = btn.dataset.kota || '';
        document.getElementById('epTel').value = btn.dataset.telepon || '';
        document.getElementById('epEmail').value = btn.dataset.email || '';
        document.getElementById('epDir').value = btn.dataset.direktur || '';
        document.getElementById('epJab').value = btn.dataset.jabatan || '';
        document.getElementById('epNpwp').value = btn.dataset.npwp || '';
        document.getElementById('epActive').value = btn.dataset.active || '1';
        $('#modalEditPenyedia').modal('show');
    });
});
</script>
<?= $this->endSection(); ?>

