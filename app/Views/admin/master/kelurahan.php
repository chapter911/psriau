<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Kelurahan</h3>
        <?php if (! empty($can_add)): ?>
            <div class="float-right">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-tambah-kelurahan">Tambah Kelurahan</button>
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
                    <th class="text-center">KECAMATAN</th>
                    <th class="text-center">KODE KELURAHAN</th>
                    <th class="text-center">NAMA KELURAHAN</th>
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
                        <td><?= esc((string) (($item['kode_kecamatan'] ?? '-') . ' - ' . ($item['nama_kecamatan'] ?? '-'))); ?></td>
                        <td><?= esc((string) ($item['kode_kelurahan'] ?? '-')); ?></td>
                        <td><?= esc((string) ($item['nama_kelurahan'] ?? '-')); ?></td>
                        <td><?= esc((string) ($item['kategori_konflik'] ?? '-')); ?></td>
                        <?php if (! empty($can_edit)): ?>
                            <td class="text-center">
                                <button
                                    type="button"
                                    class="btn btn-warning btn-sm"
                                    data-toggle="modal"
                                    data-target="#modal-ubah-kelurahan"
                                    data-kode-provinsi="<?= esc((string) ($item['kode_provinsi'] ?? ''), 'attr'); ?>"
                                    data-kode-kabupaten="<?= esc((string) ($item['kode_kabupaten'] ?? ''), 'attr'); ?>"
                                    data-kode-kecamatan="<?= esc((string) ($item['kode_kecamatan'] ?? ''), 'attr'); ?>"
                                    data-kode-kelurahan="<?= esc((string) ($item['kode_kelurahan'] ?? ''), 'attr'); ?>"
                                    data-nama-kelurahan="<?= esc((string) ($item['nama_kelurahan'] ?? ''), 'attr'); ?>"
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
<div class="modal fade" id="modal-tambah-kelurahan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Kelurahan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('/admin/master/kelurahan/tambah'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Provinsi</label>
                        <select id="add_kel_provinsi" name="kode_provinsi" class="form-control" required>
                            <option value="">Pilih Provinsi</option>
                            <?php foreach (($provinsiOptions ?? []) as $prov): ?>
                                <option value="<?= esc((string) ($prov['kode_provinsi'] ?? '')); ?>"><?= esc((string) (($prov['kode_provinsi'] ?? '') . ' - ' . ($prov['nama_provinsi'] ?? ''))); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kabupaten</label>
                        <select id="add_kel_kabupaten" name="kode_kabupaten" class="form-control" required>
                            <option value="">Pilih Kabupaten</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kecamatan</label>
                        <select id="add_kel_kecamatan" name="kode_kecamatan" class="form-control" required>
                            <option value="">Pilih Kecamatan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kode Kelurahan</label>
                        <input type="text" name="kode_kelurahan" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Kelurahan</label>
                        <input type="text" name="nama_kelurahan" class="form-control" required>
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
<div class="modal fade" id="modal-ubah-kelurahan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Kelurahan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-ubah-kelurahan" action="" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Provinsi</label>
                        <select id="edit_kel_provinsi" name="kode_provinsi" class="form-control" required>
                            <option value="">Pilih Provinsi</option>
                            <?php foreach (($provinsiOptions ?? []) as $prov): ?>
                                <option value="<?= esc((string) ($prov['kode_provinsi'] ?? '')); ?>"><?= esc((string) (($prov['kode_provinsi'] ?? '') . ' - ' . ($prov['nama_provinsi'] ?? ''))); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kabupaten</label>
                        <select id="edit_kel_kabupaten" name="kode_kabupaten" class="form-control" required>
                            <option value="">Pilih Kabupaten</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kecamatan</label>
                        <select id="edit_kel_kecamatan" name="kode_kecamatan" class="form-control" required>
                            <option value="">Pilih Kecamatan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kode Kelurahan</label>
                        <input type="text" id="edit_kode_kelurahan" name="kode_kelurahan" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Kelurahan</label>
                        <input type="text" id="edit_nama_kelurahan" name="nama_kelurahan" class="form-control" required>
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
        const kecamatanOptions = <?= json_encode($kecamatanOptions ?? [], JSON_UNESCAPED_UNICODE); ?>;

        function fillKabupaten(selectEl, provinsiValue, selectedKabupaten) {
            if (!selectEl) return;
            const rows = kabupatenOptions.filter(function (row) {
                return String(row.kode_provinsi || '') === String(provinsiValue || '');
            });

            selectEl.innerHTML = '<option value="">Pilih Kabupaten</option>';
            rows.forEach(function (row) {
                const opt = document.createElement('option');
                opt.value = String(row.kode_kabupaten || '');
                opt.textContent = String((row.kode_kabupaten || '') + ' - ' + (row.nama_kabupaten || ''));
                if (String(opt.value) === String(selectedKabupaten || '')) {
                    opt.selected = true;
                }
                selectEl.appendChild(opt);
            });
        }

        function fillKecamatan(selectEl, provinsiValue, kabupatenValue, selectedKecamatan) {
            if (!selectEl) return;
            const rows = kecamatanOptions.filter(function (row) {
                return String(row.kode_provinsi || '') === String(provinsiValue || '')
                    && String(row.kode_kabupaten || '') === String(kabupatenValue || '');
            });

            selectEl.innerHTML = '<option value="">Pilih Kecamatan</option>';
            rows.forEach(function (row) {
                const opt = document.createElement('option');
                opt.value = String(row.kode_kecamatan || '');
                opt.textContent = String((row.kode_kecamatan || '') + ' - ' + (row.nama_kecamatan || ''));
                if (String(opt.value) === String(selectedKecamatan || '')) {
                    opt.selected = true;
                }
                selectEl.appendChild(opt);
            });
        }

        const addProvinsi = document.getElementById('add_kel_provinsi');
        const addKabupaten = document.getElementById('add_kel_kabupaten');
        const addKecamatan = document.getElementById('add_kel_kecamatan');

        if (addProvinsi && addKabupaten && addKecamatan) {
            addProvinsi.addEventListener('change', function () {
                fillKabupaten(addKabupaten, addProvinsi.value, '');
                fillKecamatan(addKecamatan, addProvinsi.value, '', '');
            });

            addKabupaten.addEventListener('change', function () {
                fillKecamatan(addKecamatan, addProvinsi.value, addKabupaten.value, '');
            });
        }

        const modalEdit = document.getElementById('modal-ubah-kelurahan');
        if (!modalEdit) return;

        const form = document.getElementById('form-ubah-kelurahan');
        const editProvinsi = document.getElementById('edit_kel_provinsi');
        const editKabupaten = document.getElementById('edit_kel_kabupaten');
        const editKecamatan = document.getElementById('edit_kel_kecamatan');
        const editKodeKelurahan = document.getElementById('edit_kode_kelurahan');
        const editNamaKelurahan = document.getElementById('edit_nama_kelurahan');
        const editKategoriKonflik = document.getElementById('edit_kategori_konflik');

        if (editProvinsi && editKabupaten && editKecamatan) {
            editProvinsi.addEventListener('change', function () {
                fillKabupaten(editKabupaten, editProvinsi.value, '');
                fillKecamatan(editKecamatan, editProvinsi.value, '', '');
            });

            editKabupaten.addEventListener('change', function () {
                fillKecamatan(editKecamatan, editProvinsi.value, editKabupaten.value, '');
            });
        }

        modalEdit.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            if (!trigger) return;

            const oldProvinsi = trigger.getAttribute('data-kode-provinsi') || '';
            const oldKabupaten = trigger.getAttribute('data-kode-kabupaten') || '';
            const oldKecamatan = trigger.getAttribute('data-kode-kecamatan') || '';
            const oldKelurahan = trigger.getAttribute('data-kode-kelurahan') || '';

            editProvinsi.value = oldProvinsi;
            fillKabupaten(editKabupaten, oldProvinsi, oldKabupaten);
            fillKecamatan(editKecamatan, oldProvinsi, oldKabupaten, oldKecamatan);

            editKodeKelurahan.value = oldKelurahan;
            editNamaKelurahan.value = trigger.getAttribute('data-nama-kelurahan') || '';
            editKategoriKonflik.value = trigger.getAttribute('data-kategori-konflik') || '';

            form.action = '<?= site_url('/admin/master/kelurahan'); ?>/' + encodeURIComponent(oldProvinsi)
                + '/' + encodeURIComponent(oldKabupaten)
                + '/' + encodeURIComponent(oldKecamatan)
                + '/' + encodeURIComponent(oldKelurahan)
                + '/ubah';
        });
    })();
</script>
<?= $this->endSection(); ?>
