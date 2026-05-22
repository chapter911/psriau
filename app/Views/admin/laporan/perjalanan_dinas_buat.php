<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
    $input = $current_input ?? [];
    $pegawaiOptions = $pegawai_options ?? [];
    $creatorName = (string) ($creator_name ?? 'system');
    $defaultApproverId = (int) ($default_approver_id ?? 0);
    $selectedPelaksana = array_map('intval', (array) ($input['pelaksana_id'] ?? []));
    if ($selectedPelaksana === []) {
        $selectedPelaksana = [0];
    }
?>

<style>
    .pegawai-row { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 12px; }
</style>

<div class="card">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title mb-0">Buat Laporan Perjalanan Dinas</h3>
        <div class="card-tools ml-auto">
            <a href="<?= site_url('/admin/laporan/perjalanan-dinas'); ?>" class="btn btn-secondary btn-sm">Kembali</a>
        </div>
    </div>
    <div class="card-body">
        <?php if (! empty($form_error)): ?>
            <div class="alert alert-danger"><?= esc($form_error); ?></div>
        <?php endif; ?>

        <form action="<?= site_url('/admin/laporan/perjalanan-dinas/buat'); ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field(); ?>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Nomor Surat Tugas</label>
                    <input type="text" name="nomor_surat_tugas" class="form-control" value="<?= esc((string) ($input['nomor_surat_tugas'] ?? '')); ?>" required>
                </div>
                <div class="form-group col-md-3">
                    <label>Periode Mulai</label>
                    <input type="date" name="periode_mulai" class="form-control" value="<?= esc((string) ($input['periode_mulai'] ?? '')); ?>" required>
                </div>
                <div class="form-group col-md-3">
                    <label>Periode Selesai</label>
                    <input type="date" name="periode_selesai" class="form-control" value="<?= esc((string) ($input['periode_selesai'] ?? '')); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Kota/Kab. Tujuan Perjalanan Dinas</label>
                    <input type="text" name="kota_tujuan" class="form-control" value="<?= esc((string) ($input['kota_tujuan'] ?? '')); ?>" required>
                </div>
                <div class="form-group col-md-6">
                    <label>Diketahui Oleh</label>
                    <select name="diketahui_oleh_id" class="form-control" required>
                        <option value="">-- Pilih Pegawai --</option>
                        <?php foreach ($pegawaiOptions as $pegawai): ?>
                            <?php $pegawaiId = (int) ($pegawai['id'] ?? 0); ?>
                            <option value="<?= esc((string) $pegawaiId, 'attr'); ?>" <?= $pegawaiId === (int) ($input['diketahui_oleh_id'] ?? $defaultApproverId) ? 'selected' : ''; ?>>
                                <?= esc((string) ($pegawai['display_label'] ?? 'Pegawai')); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Default NIP 198002142014121002.</small>
                </div>
            </div>

            <div class="form-group">
                <label>Tujuan Perjalanan Dinas</label>
                <textarea name="tujuan" class="form-control" rows="4" required><?= esc((string) ($input['tujuan'] ?? '')); ?></textarea>
            </div>

            <div class="form-group">
                <label>Sasaran Perjalanan Dinas</label>
                <textarea name="sasaran" class="form-control" rows="4" required><?= esc((string) ($input['sasaran'] ?? '')); ?></textarea>
            </div>

            <div class="form-group">
                <label>Laporan Hasil Perjalanan Dinas</label>
                <textarea name="laporan_hasil" class="form-control" rows="6" required><?= esc((string) ($input['laporan_hasil'] ?? '')); ?></textarea>
            </div>

            <div class="card border shadow-none mb-3">
                <div class="card-header d-flex align-items-center">
                    <strong>Data Pelaksana</strong>
                    <button type="button" class="btn btn-success btn-sm ml-auto" id="btn-add-pelaksana">Tambah Pelaksana</button>
                </div>
                <div class="card-body" id="pelaksana-container">
                    <?php foreach ($selectedPelaksana as $selectedId): ?>
                        <div class="pegawai-row mb-3 js-pelaksana-row">
                            <div class="form-row align-items-end">
                                <div class="form-group col-md-10">
                                    <label>Nama Pelaksana</label>
                                    <select name="pelaksana_id[]" class="form-control js-pegawai-select" required>
                                        <option value="">-- Pilih Pegawai --</option>
                                        <?php foreach ($pegawaiOptions as $pegawai): ?>
                                            <?php $pegawaiId = (int) ($pegawai['id'] ?? 0); ?>
                                            <option value="<?= esc((string) $pegawaiId, 'attr'); ?>" <?= $pegawaiId === $selectedId ? 'selected' : ''; ?>>
                                                <?= esc((string) ($pegawai['display_label'] ?? 'Pegawai')); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-2 text-right">
                                    <button type="button" class="btn btn-outline-danger btn-block js-remove-pelaksana">Hapus</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label>Foto Dokumentasi</label>
                <input type="file" name="foto_dokumentasi[]" class="form-control" accept="image/*" multiple>
                <small class="text-muted">Bisa pilih lebih dari 1 foto.</small>
            </div>

            <div class="form-group">
                <label>Dibuat Oleh</label>
                <input type="text" class="form-control" value="<?= esc($creatorName); ?>" readonly>
                <?php if (! empty($creator_pegawai)): ?>
                    <small class="text-muted">Tersambung ke pegawai: <?= esc((string) ($creator_pegawai['nama'] ?? '-')); ?> / NIP <?= esc((string) ($creator_pegawai['nip'] ?? '-')); ?> / <?= esc((string) ($creator_pegawai['jabatan'] ?? '-')); ?></small>
                <?php endif; ?>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">Hasil akan dibuka sebagai PDF.</div>
                <button type="submit" class="btn btn-primary">Buat PDF</button>
            </div>
        </form>
    </div>
</div>

<template id="template-pelaksana-row">
    <div class="pegawai-row mb-3 js-pelaksana-row">
        <div class="form-row align-items-end">
            <div class="form-group col-md-10">
                <label>Nama Pelaksana</label>
                <select name="pelaksana_id[]" class="form-control js-pegawai-select" required>
                    <option value="">-- Pilih Pegawai --</option>
                    <?php foreach ($pegawaiOptions as $pegawai): ?>
                        <option value="<?= esc((string) (int) ($pegawai['id'] ?? 0), 'attr'); ?>"><?= esc((string) ($pegawai['display_label'] ?? 'Pegawai')); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-md-2 text-right">
                <button type="button" class="btn btn-outline-danger btn-block js-remove-pelaksana">Hapus</button>
            </div>
        </div>
    </div>
</template>

<script>
(function () {
    var container = document.getElementById('pelaksana-container');
    var template = document.getElementById('template-pelaksana-row');
    var addButton = document.getElementById('btn-add-pelaksana');

    function bindRow(row) {
        var removeButton = row.querySelector('.js-remove-pelaksana');
        if (removeButton) {
            removeButton.addEventListener('click', function () {
                if (container.querySelectorAll('.js-pelaksana-row').length <= 1) {
                    return;
                }
                row.remove();
            });
        }
    }

    container.querySelectorAll('.js-pelaksana-row').forEach(bindRow);

    addButton.addEventListener('click', function () {
        var fragment = template.content.cloneNode(true);
        var row = fragment.querySelector('.js-pelaksana-row');
        container.appendChild(fragment);
        bindRow(row);
    });
})();
</script>
<?= $this->endSection(); ?>