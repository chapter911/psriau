<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h4><?= $title ?></h4>
        </div>
        <div class="col-md-6 text-end">
            <?php if ($canEdit) : ?>
                <a href="<?= site_url('admin/paket/simak/' . $simak['id'] . '/edit') ?>" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Ubah
                </a>
            <?php endif; ?>
            <?php if ($canDelete) : ?>
                <form action="<?= site_url('admin/paket/simak/' . $simak['id'] . '/delete') ?>" method="post" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </form>
            <?php endif; ?>
            <a href="<?= site_url('admin/paket/simak') ?>" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')) : ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <!-- INFO CARD -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Informasi SIMAK</h6>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">Nama Paket</dt>
                        <dd class="col-sm-9"><?= esc($simak['nama_paket']) ?></dd>

                        <dt class="col-sm-3">Tahun Anggaran</dt>
                        <dd class="col-sm-9"><?= esc($simak['tahun_anggaran']) ?></dd>

                        <dt class="col-sm-3">Satker</dt>
                        <dd class="col-sm-9"><?= esc($simak['satker']) ?: '-' ?></dd>

                        <dt class="col-sm-3">PPK</dt>
                        <dd class="col-sm-9"><?= esc($simak['ppk']) ?: '-' ?></dd>

                        <dt class="col-sm-3">NIP</dt>
                        <dd class="col-sm-9"><?= esc($simak['nip']) ?: '-' ?></dd>

                        <dt class="col-sm-3">Penyedia</dt>
                        <dd class="col-sm-9"><?= esc($simak['penyedia']) ?: '-' ?></dd>

                        <dt class="col-sm-3">Nomor Kontrak</dt>
                        <dd class="col-sm-9"><?= esc($simak['nomor_kontrak']) ?: '-' ?></dd>

                        <dt class="col-sm-3">Tanggal Kontrak</dt>
                        <dd class="col-sm-9"><?= $simak['tanggal_kontrak'] ? date('d-m-Y', strtotime($simak['tanggal_kontrak'])) : '-' ?></dd>

                        <dt class="col-sm-3">Nilai Kontrak</dt>
                        <dd class="col-sm-9"><?= $simak['nilai_kontrak'] ? 'Rp ' . number_format($simak['nilai_kontrak'], 0, ',', '.') : '-' ?></dd>

                        <dt class="col-sm-3">Nilai Add Kontrak</dt>
                        <dd class="col-sm-9"><?= $simak['nilai_add_kontrak'] ? 'Rp ' . number_format($simak['nilai_add_kontrak'], 0, ',', '.') : '-' ?></dd>

                        <dt class="col-sm-3">Status</dt>
                        <dd class="col-sm-9">
                            <span class="badge bg-<?= $simak['status'] === 'draft' ? 'secondary' : 'primary' ?>">
                                <?= ucfirst($simak['status']) ?>
                            </span>
                        </dd>

                        <dt class="col-sm-3">Catatan</dt>
                        <dd class="col-sm-9"><?= esc($simak['catatan']) ?: '-' ?></dd>

                        <dt class="col-sm-3">Dibuat Oleh</dt>
                        <dd class="col-sm-9"><?= esc($simak['created_by']) ?> (<?= $simak['created_at'] ? date('d-m-Y H:i:s', strtotime($simak['created_at'])) : '-' ?>)</dd>

                        <?php if ($simak['updated_at']) : ?>
                            <dt class="col-sm-3">Diubah Oleh</dt>
                            <dd class="col-sm-9"><?= esc($simak['updated_by']) ?> (<?= date('d-m-Y H:i:s', strtotime($simak['updated_at'])) ?>)</dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

            <!-- FILE HISTORY CARD -->
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Upload File (Historical)</h6>
                    <?php if ($canEdit) : ?>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                            <i class="fas fa-upload"></i> Upload File
                        </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (empty($simak['files'])) : ?>
                        <p class="text-muted mb-0">Belum ada file yang diupload.</p>
                    <?php else : ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama File</th>
                                        <th style="width: 100px;">Ukuran</th>
                                        <th>Diupload Oleh</th>
                                        <th>Tanggal Upload</th>
                                        <th style="width: 100px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($simak['files'] as $file) : ?>
                                        <tr>
                                            <td><?= esc($file['file_name']) ?></td>
                                            <td><?= formatBytes($file['file_size']) ?></td>
                                            <td><?= esc($file['uploaded_by']) ?></td>
                                            <td><?= date('d-m-Y H:i:s', strtotime($file['uploaded_date'])) ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?= site_url('admin/paket/simak/download/' . $file['id']) ?>" class="btn btn-sm btn-info" title="Download">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    <?php if ($canEdit) : ?>
                                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteFile(<?= $file['id'] ?>)" title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
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
</div>

<!-- UPLOAD FILE MODAL -->
<?php if ($canEdit) : ?>
    <div class="modal fade" id="uploadFileModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload File SIMAK</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Upload file Excel (.xls, .xlsx) yang berisi data SIMAK untuk menyimpan history perubahan data.</p>
                    <input type="file" id="fileinput" class="form-control" accept=".xls,.xlsx" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="uploadFile()">
                        <i class="fas fa-upload"></i> Upload
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function uploadFile() {
            const fileInput = document.getElementById('fileinput');
            const file = fileInput.files[0];

            if (!file) {
                alert('Pilih file terlebih dahulu!');
                return;
            }

            const formData = new FormData();
            formData.append('file', file);

            fetch('<?= site_url('admin/paket/simak/' . $simak['id'] . '/upload-file') ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('File berhasil diupload!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat upload file');
            });
        }

        function deleteFile(fileId) {
            if (!confirm('Yakin ingin menghapus file ini?')) {
                return;
            }

            fetch('<?= site_url('admin/paket/simak/delete-file') ?>/' + fileId, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('File berhasil dihapus!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus file');
            });
        }
    </script>
<?php endif; ?>

<?= $this->endSection() ?>
