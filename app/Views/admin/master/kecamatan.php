<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Kecamatan</h3>
        <?php if (! empty($can_add)): ?>
            <div class="float-right">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-tambah-kecamatan">Tambah Kecamatan</button>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped w-100 nowrap js-datatable">
            <thead>
                <tr>
                    <th class="text-center">#</th>
                    <th class="text-center">PROVINSI</th>
                    <th class="text-center">KABUPATEN</th>
                    <th class="text-center">KODE KECAMATAN</th>
                    <th class="text-center">NAMA KECAMATAN</th>
                    <th class="text-center">KATEGORI KONFLIK</th>
                    <?php if (! empty($can_edit)): ?>
                        <th class="text-center">ACTION</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach (($items ?? []) as $item): ?>
                    <tr>
                        <td><?= esc((string) $i++); ?></td>
                        <td><?= esc((string) (($item['kode_provinsi'] ?? '-') . ' - ' . ($item['nama_provinsi'] ?? '-'))); ?></td>
                        <td><?= esc((string) (($item['kode_kabupaten'] ?? '-') . ' - ' . ($item['nama_kabupaten'] ?? '-'))); ?></td>
                        <td><?= esc((string) ($item['kode_kecamatan'] ?? '-')); ?></td>
                        <td><?= esc((string) ($item['nama_kecamatan'] ?? '-')); ?></td>
                        <td><?= esc((string) ($item['kategori_konflik'] ?? '-')); ?></td>
                        <?php if (! empty($can_edit)): ?>
                            <td class="text-center">
                                <button
                                    type="button"
                                    class="btn btn-warning btn-sm"
                                    data-toggle="modal"
                                    data-target="#modal-ubah-kecamatan"
                                    data-kode-provinsi="<?= esc((string) ($item['kode_provinsi'] ?? ''), 'attr'); ?>"
                                    data-kode-kabupaten="<?= esc((string) ($item['kode_kabupaten'] ?? ''), 'attr'); ?>"
                                    data-kode-kecamatan="<?= esc((string) ($item['kode_kecamatan'] ?? ''), 'attr'); ?>"
                                    data-nama-kecamatan="<?= esc((string) ($item['nama_kecamatan'] ?? ''), 'attr'); ?>"
                                    data-kategori-konflik="<?= esc((string) ($item['kategori_konflik'] ?? ''), 'attr'); ?>"
                                >UBAH</button>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (! empty($can_add)): ?>
<div class="modal fade" id="modal-tambah-kecamatan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Kecamatan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('/admin/master/kecamatan/tambah'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Provinsi</label>
                        <select id="add_kec_provinsi" name="kode_provinsi" class="form-control" required>
                            <option value="">Pilih Provinsi</option>
                            <?php foreach (($provinsiOptions ?? []) as $prov): ?>
                                <option value="<?= esc((string) ($prov['kode_provinsi'] ?? '')); ?>"><?= esc((string) (($prov['kode_provinsi'] ?? '') . ' - ' . ($prov['nama_provinsi'] ?? ''))); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kabupaten</label>
                        <select id="add_kec_kabupaten" name="kode_kabupaten" class="form-control" required>
                            <option value="">Pilih Kabupaten</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kode Kecamatan</label>
                        <input type="text" name="kode_kecamatan" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Kecamatan</label>
                        <input type="text" name="nama_kecamatan" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Kategori Konflik</label>
                        <input type="text" name="kategori_konflik" class="form-control">
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
<div class="modal fade" id="modal-ubah-kecamatan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Kecamatan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-ubah-kecamatan" action="" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Provinsi</label>
                        <select id="edit_kec_provinsi" name="kode_provinsi" class="form-control" required>
                            <option value="">Pilih Provinsi</option>
                            <?php foreach (($provinsiOptions ?? []) as $prov): ?>
                                <option value="<?= esc((string) ($prov['kode_provinsi'] ?? '')); ?>"><?= esc((string) (($prov['kode_provinsi'] ?? '') . ' - ' . ($prov['nama_provinsi'] ?? ''))); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kabupaten</label>
                        <select id="edit_kec_kabupaten" name="kode_kabupaten" class="form-control" required>
                            <option value="">Pilih Kabupaten</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kode Kecamatan</label>
                        <input type="text" id="edit_kode_kecamatan" name="kode_kecamatan" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Kecamatan</label>
                        <input type="text" id="edit_nama_kecamatan" name="nama_kecamatan" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Kategori Konflik</label>
                        <input type="text" id="edit_kategori_konflik" name="kategori_konflik" class="form-control">
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
<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script>
    (function () {
        const kabupatenOptions = <?= json_encode($kabupatenOptions ?? [], JSON_UNESCAPED_UNICODE); ?>;

        function fillKabupatenSelect(selectEl, selectedProvinsi, selectedKabupaten) {
            if (!selectEl) return;

            const rows = kabupatenOptions.filter(function (row) {
                return String(row.kode_provinsi || '') === String(selectedProvinsi || '');
            });

            selectEl.innerHTML = '<option value="">Pilih Kabupaten</option>';
            rows.forEach(function (row) {
                const opt = document.createElement('option');
                opt.value = String(row.kode_kabupaten || '');
                opt.textContent = String((row.kode_kabupaten || '') + ' - ' + (row.nama_kabupaten || ''));
                if (String(row.kode_kabupaten || '') === String(selectedKabupaten || '')) {
                    opt.selected = true;
                }
                selectEl.appendChild(opt);
            });
        }

        const addProvinsi = document.getElementById('add_kec_provinsi');
        const addKabupaten = document.getElementById('add_kec_kabupaten');
        if (addProvinsi && addKabupaten) {
            addProvinsi.addEventListener('change', function () {
                fillKabupatenSelect(addKabupaten, addProvinsi.value, '');
            });
        }

        const modalEdit = document.getElementById('modal-ubah-kecamatan');
        if (!modalEdit) return;

        const form = document.getElementById('form-ubah-kecamatan');
        const editProvinsi = document.getElementById('edit_kec_provinsi');
        const editKabupaten = document.getElementById('edit_kec_kabupaten');
        const editKodeKecamatan = document.getElementById('edit_kode_kecamatan');
        const editNamaKecamatan = document.getElementById('edit_nama_kecamatan');
        const editKategoriKonflik = document.getElementById('edit_kategori_konflik');

        if (editProvinsi && editKabupaten) {
            editProvinsi.addEventListener('change', function () {
                fillKabupatenSelect(editKabupaten, editProvinsi.value, '');
            });
        }

        modalEdit.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            if (!trigger) return;

            const oldProvinsi = trigger.getAttribute('data-kode-provinsi') || '';
            const oldKabupaten = trigger.getAttribute('data-kode-kabupaten') || '';
            const oldKecamatan = trigger.getAttribute('data-kode-kecamatan') || '';

            editProvinsi.value = oldProvinsi;
            fillKabupatenSelect(editKabupaten, oldProvinsi, oldKabupaten);
            editKodeKecamatan.value = oldKecamatan;
            editNamaKecamatan.value = trigger.getAttribute('data-nama-kecamatan') || '';
            editKategoriKonflik.value = trigger.getAttribute('data-kategori-konflik') || '';

            form.action = '<?= site_url('/admin/master/kecamatan'); ?>/' + encodeURIComponent(oldProvinsi) + '/' + encodeURIComponent(oldKabupaten) + '/' + encodeURIComponent(oldKecamatan) + '/ubah';
        });
    })();
</script>
<?= $this->endSection(); ?>
