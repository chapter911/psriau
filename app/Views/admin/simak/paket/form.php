<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>

<?php
$paket = $paket ?? [];
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0"><?= $title; ?></h3>
    </div>
    <div class="card-body">
        <form method="post" action="<?= $mode === 'edit' ? site_url('/admin/simak/paket/ubah/' . ($paket['id'] ?? ''))) : site_url('/admin/simak/paket/simpan'); ?>" class="form-horizontal">
            <?= csrf_field(); ?>

            <div class="form-group row">
                <label for="nama_paket" class="col-md-3 col-form-label">Nama Paket <span class="text-danger">*</span></label>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="nama_paket" name="nama_paket" value="<?= esc($paket['nama_paket'] ?? old('nama_paket')); ?>" required>
                    <small class="form-text text-muted">Masukkan nama paket konstruksi</small>
                </div>
            </div>

            <div class="form-group row">
                <label for="tahun_anggaran" class="col-md-3 col-form-label">Tahun Anggaran <span class="text-danger">*</span></label>
                <div class="col-md-9">
                    <input type="number" class="form-control" id="tahun_anggaran" name="tahun_anggaran" min="2000" max="2099" value="<?= $paket['tahun_anggaran'] ?? old('tahun_anggaran'); ?>" required>
                </div>
            </div>

            <div class="form-group row">
                <label for="penyedia" class="col-md-3 col-form-label">Penyedia</label>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="penyedia" name="penyedia" value="<?= esc($paket['penyedia'] ?? old('penyedia')); ?>">
                    <small class="form-text text-muted">Nama penyedia jasa/barang</small>
                </div>
            </div>

            <div class="form-group row">
                <label for="nomor_kontrak" class="col-md-3 col-form-label">Nomor Kontrak</label>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="nomor_kontrak" name="nomor_kontrak" value="<?= esc($paket['nomor_kontrak'] ?? old('nomor_kontrak')); ?>">
                </div>
            </div>

            <div class="form-group row">
                <label for="nilai_kontrak" class="col-md-3 col-form-label">Nilai Kontrak (Rp)</label>
                <div class="col-md-9">
                    <input type="text" class="form-control currency-input" id="nilai_kontrak" name="nilai_kontrak" data-value="<?= $paket['nilai_kontrak'] ?? old('nilai_kontrak', '0'); ?>" placeholder="0">
                    <small class="form-text text-muted">Contoh: 1.000.000.000</small>
                </div>
            </div>

            <div class="form-group row">
                <label for="add_kontrak" class="col-md-3 col-form-label">Add Kontrak (apabila ada)</label>
                <div class="col-md-9">
                    <textarea class="form-control" id="add_kontrak" name="add_kontrak" rows="3"><?= esc($paket['add_kontrak'] ?? old('add_kontrak')); ?></textarea>
                    <small class="form-text text-muted">Keterangan tambahan kontrak</small>
                </div>
            </div>

            <div class="form-group row">
                <label for="tahapan_pekerjaan" class="col-md-3 col-form-label">Tahapan Pekerjaan</label>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="tahapan_pekerjaan" name="tahapan_pekerjaan" value="<?= esc($paket['tahapan_pekerjaan'] ?? old('tahapan_pekerjaan')); ?>">
                    <small class="form-text text-muted">Contoh: Tahap I, Tahap II, dll</small>
                </div>
            </div>

            <div class="form-group row">
                <label for="tanggal_pemeriksaan" class="col-md-3 col-form-label">Tanggal Pemeriksaan</label>
                <div class="col-md-9">
                    <input type="date" class="form-control" id="tanggal_pemeriksaan" name="tanggal_pemeriksaan" value="<?= $paket['tanggal_pemeriksaan'] ?? old('tanggal_pemeriksaan'); ?>">
                </div>
            </div>

            <div class="form-group row">
                <label for="satker" class="col-md-3 col-form-label">Satker</label>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="satker" name="satker" value="<?= esc($paket['satker'] ?? old('satker')); ?>">
                    <small class="form-text text-muted">Satuan Kerja</small>
                </div>
            </div>

            <div class="form-group row">
                <label for="ppk" class="col-md-3 col-form-label">PPK</label>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="ppk" name="ppk" value="<?= esc($paket['ppk'] ?? old('ppk')); ?>">
                    <small class="form-text text-muted">Pejabat Pembuat Komitmen</small>
                </div>
            </div>

            <div class="form-group row">
                <label for="nip" class="col-md-3 col-form-label">NIP</label>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="nip" name="nip" value="<?= esc($paket['nip'] ?? old('nip')); ?>">
                    <small class="form-text text-muted">Nomor Induk Pegawai PPK</small>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-md-9 offset-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                    <a href="<?= site_url('/admin/simak/paket'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times mr-1"></i> Batal
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .form-horizontal .form-group {
        margin-bottom: 1.5rem;
    }

    .form-horizontal .col-form-label {
        padding-top: calc(0.375rem + 1px);
        padding-bottom: calc(0.375rem + 1px);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const currencyInput = document.getElementById('nilai_kontrak');
    
    // Format display on load
    if (currencyInput) {
        const value = currencyInput.dataset.value || '0';
        currencyInput.value = formatCurrency(parseFloat(value) || 0);
        
        // Add comma formatting as user types
        currencyInput.addEventListener('input', function() {
            let val = this.value.replace(/[^\d]/g, '');
            if (val) {
                this.value = formatCurrency(parseFloat(val) || 0);
            }
        });
        
        // Clean value before submit
        const form = currencyInput.closest('form');
        if (form) {
            form.addEventListener('submit', function() {
                currencyInput.value = currencyInput.value.replace(/[^\d]/g, '');
            });
        }
    }
    
    function formatCurrency(num) {
        if (num === 0) return '0';
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
});
</script>
