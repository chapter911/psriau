<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
$provinsiOptions = $provinsiOptions ?? [];
$kabupatenOptions = $kabupatenOptions ?? [];
$kecamatanOptions = $kecamatanOptions ?? [];
$canAdd = (bool) ($can_add ?? false);
$canEdit = (bool) ($can_edit ?? false);
?>

<div class="card">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title mb-0">Daftar Kelurahan</h3>
        <?php if ($canAdd): ?>
            <div class="card-tools ml-auto">
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-tambah-kelurahan">Tambah Kelurahan</button>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <label for="filter_kelurahan_provinsi">Filter Provinsi</label>
                <select id="filter_kelurahan_provinsi" class="form-control">
                    <option value="">Semua Provinsi</option>
                    <?php foreach ($provinsiOptions as $prov): ?>
                        <option value="<?= esc((string) ($prov['kode_provinsi'] ?? '')); ?>"><?= esc((string) (($prov['kode_provinsi'] ?? '') . ' - ' . ($prov['nama_provinsi'] ?? ''))); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter_kelurahan_kabupaten">Filter Kabupaten</label>
                <select id="filter_kelurahan_kabupaten" class="form-control">
                    <option value="">Semua Kabupaten</option>
                    <?php foreach ($kabupatenOptions as $kab): ?>
                        <option value="<?= esc((string) ($kab['kode_kabupaten'] ?? '')); ?>" data-kode-provinsi="<?= esc((string) ($kab['kode_provinsi'] ?? ''), 'attr'); ?>"><?= esc((string) (($kab['kode_kabupaten'] ?? '') . ' - ' . ($kab['nama_kabupaten'] ?? ''))); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter_kelurahan_kecamatan">Filter Kecamatan</label>
                <select id="filter_kelurahan_kecamatan" class="form-control">
                    <option value="">Semua Kecamatan</option>
                    <?php foreach ($kecamatanOptions as $kec): ?>
                        <option value="<?= esc((string) ($kec['kode_kecamatan'] ?? '')); ?>" data-kode-provinsi="<?= esc((string) ($kec['kode_provinsi'] ?? ''), 'attr'); ?>" data-kode-kabupaten="<?= esc((string) ($kec['kode_kabupaten'] ?? ''), 'attr'); ?>"><?= esc((string) (($kec['kode_kecamatan'] ?? '') . ' - ' . ($kec['nama_kecamatan'] ?? ''))); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped w-100 nowrap" id="tableKelurahan">
                <thead>
                    <tr>
                        <th class="text-center" style="width:60px;">#</th>
                        <th class="text-center">PROVINSI</th>
                        <th class="text-center">KABUPATEN</th>
                        <th class="text-center">KECAMATAN</th>
                        <th class="text-center">KODE KELURAHAN</th>
                        <th class="text-center">NAMA KELURAHAN</th>
                        <th class="text-center">KATEGORI KONFLIK</th>
                        <?php if ($canEdit): ?>
                            <th class="text-center" style="width:120px;">ACTION</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($canAdd): ?>
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

<?php if ($canEdit): ?>
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
        if (typeof $ === 'undefined' || ! $.fn.DataTable) {
            return;
        }

        const kabupatenOptions = <?= json_encode($kabupatenOptions, JSON_UNESCAPED_UNICODE); ?>;
        const kecamatanOptions = <?= json_encode($kecamatanOptions, JSON_UNESCAPED_UNICODE); ?>;
        const canEdit = <?= json_encode($canEdit, JSON_UNESCAPED_UNICODE); ?>;
        const dataUrl = <?= json_encode(site_url('/admin/master/kelurahan'), JSON_UNESCAPED_UNICODE); ?>;

        function fillKabupatenSelect(selectEl, provinsiValue, selectedKabupaten) {
            if (!selectEl) return;
            const rows = kabupatenOptions.filter(function (row) {
                return provinsiValue === '' || String(row.kode_provinsi || '') === String(provinsiValue || '');
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

        function fillKecamatanSelect(selectEl, provinsiValue, kabupatenValue, selectedKecamatan) {
            if (!selectEl) return;
            const rows = kecamatanOptions.filter(function (row) {
                const provinsiMatch = provinsiValue === '' || String(row.kode_provinsi || '') === String(provinsiValue || '');
                const kabupatenMatch = kabupatenValue === '' || String(row.kode_kabupaten || '') === String(kabupatenValue || '');
                return provinsiMatch && kabupatenMatch;
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

        const $table = $('#tableKelurahan');
        if (! $table.length || $.fn.dataTable.isDataTable($table)) {
            return;
        }

        const dt = $table.DataTable({
            processing: true,
            serverSide: true,
            responsive: false,
            autoWidth: false,
            scrollX: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50], [10, 25, 50]],
            ajax: {
                url: dataUrl,
                type: 'GET',
                data: function (d) {
                    d.filter_provinsi = $('#filter_kelurahan_provinsi').val();
                    d.filter_kabupaten = $('#filter_kelurahan_kabupaten').val();
                    d.filter_kecamatan = $('#filter_kelurahan_kecamatan').val();
                }
            },
            columns: [
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: null,
                    render: function (data, type, row) {
                        return (row.kode_provinsi || '-') + ' - ' + (row.nama_provinsi || '-');
                    }
                },
                {
                    data: null,
                    render: function (data, type, row) {
                        return (row.kode_kabupaten || '-') + ' - ' + (row.nama_kabupaten || '-');
                    }
                },
                {
                    data: null,
                    render: function (data, type, row) {
                        return (row.kode_kecamatan || '-') + ' - ' + (row.nama_kecamatan || '-');
                    }
                },
                { data: 'kode_kelurahan' },
                { data: 'nama_kelurahan' },
                { data: 'kategori_konflik' }
                <?= $canEdit ? ",\n                {\n                    data: 'action_html',\n                    orderable: false,\n                    searchable: false,\n                    className: 'text-center'\n                }" : ''; ?>
            ],
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data',
                zeroRecords: 'Data tidak ditemukan',
                paginate: {
                    first: 'Awal',
                    last: 'Akhir',
                    next: 'Berikutnya',
                    previous: 'Sebelumnya'
                }
            }
        });

        const filterProvinsi = document.getElementById('filter_kelurahan_provinsi');
        const filterKabupaten = document.getElementById('filter_kelurahan_kabupaten');
        const filterKecamatan = document.getElementById('filter_kelurahan_kecamatan');

        if (filterProvinsi && filterKabupaten && filterKecamatan) {
            filterProvinsi.addEventListener('change', function () {
                fillKabupatenSelect(filterKabupaten, filterProvinsi.value, '');
                fillKecamatanSelect(filterKecamatan, filterProvinsi.value, '', '');
                filterKabupaten.value = '';
                filterKecamatan.value = '';
                dt.ajax.reload();
            });

            filterKabupaten.addEventListener('change', function () {
                fillKecamatanSelect(filterKecamatan, filterProvinsi.value, filterKabupaten.value, '');
                filterKecamatan.value = '';
                dt.ajax.reload();
            });

            filterKecamatan.addEventListener('change', function () {
                dt.ajax.reload();
            });

            fillKabupatenSelect(filterKabupaten, filterProvinsi.value, filterKabupaten.value);
            fillKecamatanSelect(filterKecamatan, filterProvinsi.value, filterKabupaten.value, filterKecamatan.value);
        }

        const addProvinsi = document.getElementById('add_kel_provinsi');
        const addKabupaten = document.getElementById('add_kel_kabupaten');
        const addKecamatan = document.getElementById('add_kel_kecamatan');

        if (addProvinsi && addKabupaten && addKecamatan) {
            addProvinsi.addEventListener('change', function () {
                fillKabupatenSelect(addKabupaten, addProvinsi.value, '');
                fillKecamatanSelect(addKecamatan, addProvinsi.value, '', '');
            });

            addKabupaten.addEventListener('change', function () {
                fillKecamatanSelect(addKecamatan, addProvinsi.value, addKabupaten.value, '');
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
                fillKabupatenSelect(editKabupaten, editProvinsi.value, '');
                fillKecamatanSelect(editKecamatan, editProvinsi.value, '', '');
            });

            editKabupaten.addEventListener('change', function () {
                fillKecamatanSelect(editKecamatan, editProvinsi.value, editKabupaten.value, '');
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
            fillKabupatenSelect(editKabupaten, oldProvinsi, oldKabupaten);
            fillKecamatanSelect(editKecamatan, oldProvinsi, oldKabupaten, oldKecamatan);

            editKodeKelurahan.value = oldKelurahan;
            editNamaKelurahan.value = trigger.getAttribute('data-nama-kelurahan') || '';
            editKategoriKonflik.value = trigger.getAttribute('data-kategori-konflik') || '';

            form.action = <?= json_encode(site_url('/admin/master/kelurahan'), JSON_UNESCAPED_UNICODE); ?> + '/' + encodeURIComponent(oldProvinsi)
                + '/' + encodeURIComponent(oldKabupaten)
                + '/' + encodeURIComponent(oldKecamatan)
                + '/' + encodeURIComponent(oldKelurahan)
                + '/ubah';
        });
    })();
</script>
<?= $this->endSection(); ?>
