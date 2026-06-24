<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
    $isEdit = (bool) ($is_edit ?? false);
    $title = $title ?? ($isEdit ? 'Ubah Lupa Absen' : 'Ajukan Lupa Absen');
    $currentInput = $current_input ?? [];
    $formError = $form_error ?? null;
    $formId = $id ?? null;
?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0"><?= esc($title); ?></h3>
        <div class="card-tools">
            <a href="<?= site_url('admin/surat/lupa-absen'); ?>" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
    <div class="card-body">
        <?php if (! empty($formError)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?= esc($formError); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= site_url('admin/surat/lupa-absen' . ($isEdit ? '/' . $formId . '/ubah' : '/buat')); ?>" autocomplete="off">
            <?= csrf_field(); ?>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="nip">NIP <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control"
                               id="nip"
                               name="nip"
                               value="<?= esc((string) ($currentInput['nip'] ?? '')); ?>"
                               required
                               placeholder="Masukkan NIP">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="nama">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control"
                               id="nama"
                               name="nama"
                               value="<?= esc((string) ($currentInput['nama'] ?? '')); ?>"
                               required
                               placeholder="Masukkan nama lengkap">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="tanggal_absen">Tanggal Absen <span class="text-danger">*</span></label>
                        <input type="date"
                               class="form-control"
                               id="tanggal_absen"
                               name="tanggal_absen"
                               value="<?= esc((string) ($currentInput['tanggal_absen'] ?? '')); ?>"
                               required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="jenis_absen">Jenis Absen <span class="text-danger">*</span></label>
                        <select class="form-control" id="jenis_absen" name="jenis_absen" required>
                            <option value="">-- Pilih --</option>
                            <option value="masuk" <?= (($currentInput['jenis_absen'] ?? '') === 'masuk') ? 'selected' : ''; ?>>Absen Masuk</option>
                            <option value="pulang" <?= (($currentInput['jenis_absen'] ?? '') === 'pulang') ? 'selected' : ''; ?>>Absen Pulang</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="jam_absen">Jam Absen <span class="text-danger">*</span></label>
                        <input type="time"
                               class="form-control"
                               id="jam_absen"
                               name="jam_absen"
                               value="<?= esc((string) ($currentInput['jam_absen'] ?? '')); ?>"
                               required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="keterangan">Keterangan</label>
                <textarea class="form-control"
                          id="keterangan"
                          name="keterangan"
                          rows="4"
                          placeholder="Jelaskan mengapa lupa absen (opsional)"><?= esc((string) ($currentInput['keterangan'] ?? '')); ?></textarea>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Ajukan'; ?>
                </button>
                <a href="<?= site_url('admin/surat/lupa-absen'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection(); ?>
