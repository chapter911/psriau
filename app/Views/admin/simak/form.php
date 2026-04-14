<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h4><?= $title ?></h4>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?= site_url('admin/paket/simak') ?>" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <?php if (session()->getFlashdata('validation')) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Validasi Error:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach (session()->getFlashdata('validation')->getErrors() as $error) : ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Informasi SIMAK</h6>
                </div>
                <div class="card-body">
                    <form action="<?= site_url('admin/paket/simak/store') ?>" method="post">
                        <?= csrf_field() ?>
                        
                        <?php if ($simak) : ?>
                            <input type="hidden" name="id" value="<?= $simak['id'] ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama Paket *</label>
                                    <input type="text" name="nama_paket" class="form-control" required 
                                        value="<?= old('nama_paket', $simak['nama_paket'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tahun Anggaran *</label>
                                    <input type="text" name="tahun_anggaran" class="form-control" required 
                                        value="<?= old('tahun_anggaran', $simak['tahun_anggaran'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Satker</label>
                                    <input type="text" name="satker" class="form-control" 
                                        value="<?= old('satker', $simak['satker'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">PPK</label>
                                    <input type="text" name="ppk" class="form-control" 
                                        value="<?= old('ppk', $simak['ppk'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">NIP</label>
                                    <input type="text" name="nip" class="form-control" 
                                        value="<?= old('nip', $simak['nip'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Penyedia</label>
                                    <input type="text" name="penyedia" class="form-control" 
                                        value="<?= old('penyedia', $simak['penyedia'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nomor Kontrak</label>
                                    <input type="text" name="nomor_kontrak" class="form-control" 
                                        value="<?= old('nomor_kontrak', $simak['nomor_kontrak'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Kontrak</label>
                                    <input type="date" name="tanggal_kontrak" class="form-control" 
                                        value="<?= old('tanggal_kontrak', $simak['tanggal_kontrak'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nilai Kontrak (Rp)</label>
                                    <input type="number" name="nilai_kontrak" class="form-control" 
                                        value="<?= old('nilai_kontrak', $simak['nilai_kontrak'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nilai Add Kontrak (Rp)</label>
                                    <input type="number" name="nilai_add_kontrak" class="form-control" 
                                        value="<?= old('nilai_add_kontrak', $simak['nilai_add_kontrak'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea name="catatan" class="form-control" rows="3"><?= old('catatan', $simak['catatan'] ?? '') ?></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                            <a href="<?= site_url('admin/paket/simak') ?>" class="btn btn-secondary btn-sm">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
