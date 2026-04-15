<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php if (empty($table_ready)): ?>
    <div class="alert alert-warning">
        Tabel pegawai belum tersedia. Jalankan migration terlebih dahulu.
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Pegawai</h3>
        <?php if (! empty($can_add) || ! empty($can_import) || ! empty($can_export)): ?>
            <div class="float-right">
                <?php if (! empty($can_export)): ?>
                    <a href="<?= site_url('/admin/master/pegawai/export'); ?>" class="btn btn-success mr-2">Export Excel</a>
                <?php endif; ?>
                <?php if (! empty($can_import)): ?>
                    <button type="button" class="btn btn-info mr-2" data-toggle="modal" data-target="#modal-import-pegawai">Import Excel</button>
                <?php endif; ?>
                <?php if (! empty($can_add)): ?>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-tambah-pegawai">Tambah Pegawai</button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped w-100 nowrap js-datatable">
            <thead>
                <tr style="white-space: nowrap;">
                    <th class="text-center">#</th>
                    <th class="text-center">FOTO</th>
                    <th class="text-center">NIP</th>
                    <th class="text-center">NAMA</th>
                    <th class="text-center">JABATAN (FUNGSIONAL/PELAKSANA)</th>
                    <th class="text-center">JABATAN (PERBENDAHARAAN)</th>
                    <th class="text-center">JENIS PEGAWAI</th>
                    <th class="text-center">ESELON</th>
                    <th class="text-center">GOLONGAN</th>
                    <th class="text-center">MASA KERJA</th>
                    <th class="text-center">STATUS</th>
                    <?php if (! empty($can_edit)): ?>
                        <th class="text-center">ACTION</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach (($items ?? []) as $item): ?>
                    <?php
                        $isActive = (int) ($item['is_active'] ?? 1) === 1;
                        $fotoPath = trim((string) ($item['foto'] ?? ''));
                        $fotoUrl = $fotoPath !== '' ? base_url($fotoPath) : '';
                    ?>
                    <tr>
                        <td><?= esc((string) $i++); ?></td>
                        <td class="text-center">
                            <?php if ($fotoUrl !== ''): ?>
                                <button
                                    type="button"
                                    class="btn p-0 border-0 bg-transparent js-open-foto-modal"
                                    data-foto-url="<?= esc($fotoUrl, 'attr'); ?>"
                                    data-nama="<?= esc((string) ($item['nama'] ?? 'Pegawai'), 'attr'); ?>"
                                    title="Lihat foto"
                                >
                                    <img src="<?= esc($fotoUrl); ?>" alt="Foto Pegawai" style="width: 56px; height: 56px; border-radius: 6px; object-fit: cover; border: 1px solid #dee2e6;">
                                </button>
                            <?php elseif (! empty($can_edit)): ?>
                                <button
                                    type="button"
                                    class="btn btn-light border d-inline-flex align-items-center justify-content-center js-open-edit-pegawai-foto"
                                    data-toggle="modal"
                                    data-target="#modal-ubah-pegawai"
                                    data-id="<?= esc((string) ($item['id'] ?? ''), 'attr'); ?>"
                                    data-nip="<?= esc((string) ($item['nip'] ?? ''), 'attr'); ?>"
                                    data-nama="<?= esc((string) ($item['nama'] ?? ''), 'attr'); ?>"
                                    data-foto-url=""
                                    data-jabatan_utama_id="<?= esc((string) ($item['jabatan_utama_id'] ?? ''), 'attr'); ?>"
                                    data-jabatan_perbendaharaan_id="<?= esc((string) ($item['jabatan_perbendaharaan_id'] ?? ''), 'attr'); ?>"
                                    data-jenis_pegawai="<?= esc((string) ($item['jenis_pegawai'] ?? 'pns'), 'attr'); ?>"
                                    data-eselon="<?= esc((string) ($item['eselon'] ?? ''), 'attr'); ?>"
                                    data-golongan="<?= esc((string) ($item['golongan'] ?? ''), 'attr'); ?>"
                                    data-masa_kerja="<?= esc((string) ($item['masa_kerja'] ?? ''), 'attr'); ?>"
                                    data-is_active="<?= esc((string) ($item['is_active'] ?? 1), 'attr'); ?>"
                                    title="Klik untuk update foto"
                                    style="width: 56px; height: 56px; border-radius: 6px;"
                                >
                                    <span class="text-muted font-weight-bold">+</span>
                                </button>
                            <?php else: ?>
                                <span class="badge badge-light border">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc((string) ($item['nip'] ?? '-')); ?></td>
                        <td><?= esc((string) ($item['nama'] ?? '-')); ?></td>
                        <td><?= esc((string) ($item['jabatan_utama_label'] ?? '-')); ?></td>
                        <td><?= esc((string) ($item['jabatan_perbendaharaan_label'] ?? '-')); ?></td>
                        <td class="text-center text-uppercase"><?= esc((string) ($item['jenis_pegawai'] ?? 'pns')); ?></td>
                        <td><?= esc((string) ($item['eselon'] ?? '-')); ?></td>
                        <td><?= esc((string) ($item['golongan'] ?? '-')); ?></td>
                        <td><?= esc((string) ($item['masa_kerja'] ?? '-')); ?></td>
                        <td class="text-center">
                            <?php if ($isActive): ?>
                                <span class="badge badge-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <?php if (! empty($can_edit)): ?>
                            <td class="text-center" style="white-space: nowrap;">
                                <button
                                    type="button"
                                    class="btn btn-warning btn-sm"
                                    data-toggle="modal"
                                    data-target="#modal-ubah-pegawai"
                                    data-id="<?= esc((string) ($item['id'] ?? ''), 'attr'); ?>"
                                    data-nip="<?= esc((string) ($item['nip'] ?? ''), 'attr'); ?>"
                                    data-nama="<?= esc((string) ($item['nama'] ?? ''), 'attr'); ?>"
                                    data-foto-url="<?= esc($fotoUrl, 'attr'); ?>"
                                    data-jabatan_utama_id="<?= esc((string) ($item['jabatan_utama_id'] ?? ''), 'attr'); ?>"
                                    data-jabatan_perbendaharaan_id="<?= esc((string) ($item['jabatan_perbendaharaan_id'] ?? ''), 'attr'); ?>"
                                    data-jenis_pegawai="<?= esc((string) ($item['jenis_pegawai'] ?? 'pns'), 'attr'); ?>"
                                    data-eselon="<?= esc((string) ($item['eselon'] ?? ''), 'attr'); ?>"
                                    data-golongan="<?= esc((string) ($item['golongan'] ?? ''), 'attr'); ?>"
                                    data-masa_kerja="<?= esc((string) ($item['masa_kerja'] ?? ''), 'attr'); ?>"
                                    data-is_active="<?= esc((string) ($item['is_active'] ?? 1), 'attr'); ?>"
                                >UBAH</button>

                                <form action="<?= site_url('/admin/master/pegawai/' . (int) ($item['id'] ?? 0) . '/status'); ?>" method="post" class="d-inline-block" onsubmit="return confirm('Yakin ingin mengubah status pegawai ini?');">
                                    <?= csrf_field(); ?>
                                    <input type="hidden" name="is_active" value="<?= $isActive ? '0' : '1'; ?>">
                                    <button type="submit" class="btn btn-sm <?= $isActive ? 'btn-secondary' : 'btn-success'; ?>">
                                        <?= $isActive ? 'NONAKTIFKAN' : 'AKTIFKAN'; ?>
                                    </button>
                                </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (! empty($can_import)): ?>
<div class="modal fade" id="modal-import-pegawai" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Pegawai (Excel)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('/admin/master/pegawai/import'); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        Kolom wajib: <strong>nip</strong>, <strong>nama</strong>, <strong>jabatan_utama</strong>, <strong>jenis_pegawai</strong> (cpns/pns/konsultan).<br>
                        Kolom opsional: <strong>jabatan_perbendaharaan</strong>, <strong>eselon</strong>, <strong>golongan</strong>, <strong>masa_kerja</strong>, <strong>status</strong>.
                    </div>
                    <div class="mb-3">
                        <a href="<?= site_url('/admin/master/pegawai/template'); ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-download mr-1"></i> Download Template Excel
                        </a>
                    </div>
                    <div class="form-group mb-0">
                        <label for="file_excel_pegawai">File Excel</label>
                        <input type="file" class="form-control" id="file_excel_pegawai" name="file_excel" accept=".xls,.xlsx,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                        <small class="text-muted">Import hanya untuk data teks. Foto tetap diupload dari form tambah/ubah.</small>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (! empty($can_add)): ?>
<div class="modal fade" id="modal-tambah-pegawai" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Pegawai</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('/admin/master/pegawai/tambah'); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>NIP</label>
                            <input type="text" name="nip" class="form-control" required maxlength="30">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Nama</label>
                            <input type="text" name="nama" class="form-control" required maxlength="150">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Foto</label>
                            <input type="file" name="foto" class="form-control" accept="image/*">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Status</label>
                            <select name="is_active" class="form-control" required>
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Jabatan (Fungsional/Pelaksana)</label>
                        <select name="jabatan_utama_id" class="form-control" required>
                            <option value="">Pilih jabatan</option>
                            <?php foreach (($jabatan_utama_options ?? []) as $option): ?>
                                <option value="<?= esc((string) ($option['id'] ?? '')); ?>"><?= esc((string) ($option['label'] ?? '')); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jabatan (Perbendaharaan/Opsional)</label>
                        <select name="jabatan_perbendaharaan_id" class="form-control">
                            <option value="">Kosongkan jika tidak ada</option>
                            <?php foreach (($jabatan_perbendaharaan_options ?? []) as $option): ?>
                                <option value="<?= esc((string) ($option['id'] ?? '')); ?>"><?= esc((string) ($option['label'] ?? '')); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jenis Pegawai</label>
                        <select name="jenis_pegawai" class="form-control" required>
                            <option value="pns">PNS</option>
                            <option value="cpns">CPNS</option>
                            <option value="konsultan">Konsultan</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Eselon</label>
                            <input type="text" name="eselon" class="form-control" maxlength="50">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Golongan</label>
                            <input type="text" name="golongan" class="form-control" maxlength="50">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Masa Kerja</label>
                            <input type="text" name="masa_kerja" class="form-control" maxlength="50">
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (! empty($can_edit)): ?>
<div class="modal fade" id="modal-ubah-pegawai" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Pegawai</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-ubah-pegawai" action="" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>NIP</label>
                            <input type="text" id="edit_nip" name="nip" class="form-control" required maxlength="30">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Nama</label>
                            <input type="text" id="edit_nama" name="nama" class="form-control" required maxlength="150">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Foto</label>
                            <input type="file" name="foto" class="form-control" accept="image/*">
                            <small class="text-muted">Kosongkan jika foto tidak diubah.</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Status</label>
                            <select id="edit_is_active" name="is_active" class="form-control" required>
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <img id="edit_foto_preview" src="" alt="Preview Foto" style="display:none;width:64px;height:64px;border-radius:50%;object-fit:cover;border:1px solid #dee2e6;">
                    </div>
                    <div class="form-group">
                        <label>Jabatan (Fungsional/Pelaksana)</label>
                        <select id="edit_jabatan_utama_id" name="jabatan_utama_id" class="form-control" required>
                            <option value="">Pilih jabatan</option>
                            <?php foreach (($jabatan_utama_options ?? []) as $option): ?>
                                <option value="<?= esc((string) ($option['id'] ?? '')); ?>"><?= esc((string) ($option['label'] ?? '')); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jabatan (Perbendaharaan/Opsional)</label>
                        <select id="edit_jabatan_perbendaharaan_id" name="jabatan_perbendaharaan_id" class="form-control">
                            <option value="">Kosongkan jika tidak ada</option>
                            <?php foreach (($jabatan_perbendaharaan_options ?? []) as $option): ?>
                                <option value="<?= esc((string) ($option['id'] ?? '')); ?>"><?= esc((string) ($option['label'] ?? '')); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jenis Pegawai</label>
                        <select id="edit_jenis_pegawai" name="jenis_pegawai" class="form-control" required>
                            <option value="pns">PNS</option>
                            <option value="cpns">CPNS</option>
                            <option value="konsultan">Konsultan</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Eselon</label>
                            <input type="text" id="edit_eselon" name="eselon" class="form-control" maxlength="50">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Golongan</label>
                            <input type="text" id="edit_golongan" name="golongan" class="form-control" maxlength="50">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Masa Kerja</label>
                            <input type="text" id="edit_masa_kerja" name="masa_kerja" class="form-control" maxlength="50">
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="modal fade" id="modal-foto-pegawai" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fotoPegawaiModalTitle">Foto Pegawai</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="fotoPegawaiModalImage" src="" alt="Foto Pegawai" style="max-width: 100%; max-height: 70vh; border-radius: 8px; border: 1px solid #dee2e6;">
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script>
    (function () {
        const modalEdit = document.getElementById('modal-ubah-pegawai');
        if (!modalEdit) return;

        const form = document.getElementById('form-ubah-pegawai');
        const fieldNip = document.getElementById('edit_nip');
        const fieldNama = document.getElementById('edit_nama');
        const fieldJabatanUtama = document.getElementById('edit_jabatan_utama_id');
        const fieldJabatanPerbend = document.getElementById('edit_jabatan_perbendaharaan_id');
        const fieldJenisPegawai = document.getElementById('edit_jenis_pegawai');
        const fieldEselon = document.getElementById('edit_eselon');
        const fieldGolongan = document.getElementById('edit_golongan');
        const fieldMasaKerja = document.getElementById('edit_masa_kerja');
        const fieldStatus = document.getElementById('edit_is_active');
        const fotoPreview = document.getElementById('edit_foto_preview');

        const applyEditData = (trigger) => {
            if (!trigger) {
                return;
            }

            const id = trigger.getAttribute('data-id') || '';
            form.action = '<?= site_url('/admin/master/pegawai'); ?>/' + encodeURIComponent(id) + '/ubah';
            fieldNip.value = trigger.getAttribute('data-nip') || '';
            fieldNama.value = trigger.getAttribute('data-nama') || '';
            fieldJabatanUtama.value = trigger.getAttribute('data-jabatan_utama_id') || '';
            fieldJabatanPerbend.value = trigger.getAttribute('data-jabatan_perbendaharaan_id') || '';
            fieldJenisPegawai.value = (trigger.getAttribute('data-jenis_pegawai') || 'pns').toLowerCase();
            fieldEselon.value = trigger.getAttribute('data-eselon') || '';
            fieldGolongan.value = trigger.getAttribute('data-golongan') || '';
            fieldMasaKerja.value = trigger.getAttribute('data-masa_kerja') || '';
            fieldStatus.value = trigger.getAttribute('data-is_active') || '1';

            const fotoUrl = trigger.getAttribute('data-foto-url') || '';
            if (fotoUrl) {
                fotoPreview.src = fotoUrl;
                fotoPreview.style.display = 'inline-block';
            } else {
                fotoPreview.src = '';
                fotoPreview.style.display = 'none';
            }
        };

        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('button[data-target="#modal-ubah-pegawai"]');
            if (!trigger) {
                return;
            }

            applyEditData(trigger);
        });

        modalEdit.addEventListener('show.bs.modal', function (event) {
            applyEditData(event.relatedTarget);
        });
    })();

    (function () {
        const fotoModal = document.getElementById('modal-foto-pegawai');
        if (!fotoModal) return;

        const fotoTitle = document.getElementById('fotoPegawaiModalTitle');
        const fotoImage = document.getElementById('fotoPegawaiModalImage');

        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('.js-open-foto-modal');
            if (!trigger) {
                return;
            }

            const fotoUrl = trigger.getAttribute('data-foto-url') || '';
            const nama = trigger.getAttribute('data-nama') || 'Pegawai';

            fotoTitle.textContent = 'Foto - ' + nama;
            fotoImage.src = fotoUrl;

            if (typeof $ !== 'undefined') {
                $('#modal-foto-pegawai').modal('show');
            }
        });

        fotoModal.addEventListener('hidden.bs.modal', function () {
            fotoImage.src = '';
        });
    })();
</script>
<?= $this->endSection(); ?>