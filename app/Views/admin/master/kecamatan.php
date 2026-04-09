<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
$provinsiOptions = $provinsiOptions ?? [];
$kabupatenOptions = $kabupatenOptions ?? [];
$canAdd = (bool) ($can_add ?? false);
$canEdit = (bool) ($can_edit ?? false);
?>

<div class="card">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title mb-0">Daftar Kecamatan</h3>
        <?php if ($canAdd): ?>
            <div class="card-tools ml-auto">
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-tambah-kecamatan">Tambah Kecamatan</button>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="filter_kecamatan_provinsi">Filter Provinsi</label>
                <select id="filter_kecamatan_provinsi" class="form-control">
                    <option value="">Semua Provinsi</option>
                    <?php foreach ($provinsiOptions as $prov): ?>
                        <option value="<?= esc((string) ($prov['kode_provinsi'] ?? '')); ?>"><?= esc((string) (($prov['kode_provinsi'] ?? '') . ' - ' . ($prov['nama_provinsi'] ?? ''))); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="filter_kecamatan_kabupaten">Filter Kabupaten</label>
                <select id="filter_kecamatan_kabupaten" class="form-control">
                    <option value="">Semua Kabupaten</option>
                    <?php foreach ($kabupatenOptions as $kab): ?>
                        <option value="<?= esc((string) ($kab['kode_kabupaten'] ?? '')); ?>" data-kode-provinsi="<?= esc((string) ($kab['kode_provinsi'] ?? ''), 'attr'); ?>"><?= esc((string) (($kab['kode_kabupaten'] ?? '') . ' - ' . ($kab['nama_kabupaten'] ?? ''))); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped w-100 nowrap" id="tableKecamatan">
                <thead>
                    <tr>
                        <th class="text-center" style="width:60px;">#</th>
                        <th class="text-center">PROVINSI</th>
                        <th class="text-center">KABUPATEN</th>
                        <th class="text-center">KODE KECAMATAN</th>
                        <th class="text-center">NAMA KECAMATAN</th>
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

<?php if ($canEdit): ?>
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
        if (typeof $ === 'undefined' || ! $.fn.DataTable) {
            return;
        }

        const kabupatenOptions = <?= json_encode($kabupatenOptions, JSON_UNESCAPED_UNICODE); ?>;
        const canEdit = <?= json_encode($canEdit, JSON_UNESCAPED_UNICODE); ?>;
        const dataUrl = <?= json_encode(site_url('/admin/master/kecamatan'), JSON_UNESCAPED_UNICODE); ?>;
        const $table = $('#tableKecamatan');
        if (! $table.length || $.fn.dataTable.isDataTable($table)) {
            return;
        }

        function fillKabupatenSelect(selectEl, selectedProvinsi, selectedKabupaten) {
            if (!selectEl) return;

            const rows = kabupatenOptions.filter(function (row) {
                return selectedProvinsi === '' || String(row.kode_provinsi || '') === String(selectedProvinsi || '');
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
                    d.filter_provinsi = $('#filter_kecamatan_provinsi').val();
                    d.filter_kabupaten = $('#filter_kecamatan_kabupaten').val();
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
                { data: 'kode_kecamatan' },
                { data: 'nama_kecamatan' },
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

        const filterProvinsi = document.getElementById('filter_kecamatan_provinsi');
        const filterKabupaten = document.getElementById('filter_kecamatan_kabupaten');
        if (filterProvinsi && filterKabupaten) {
            filterProvinsi.addEventListener('change', function () {
                fillKabupatenSelect(filterKabupaten, filterProvinsi.value, '');
                filterKabupaten.value = '';
                dt.ajax.reload();
            });

            filterKabupaten.addEventListener('change', function () {
                dt.ajax.reload();
            });

            fillKabupatenSelect(filterKabupaten, filterProvinsi.value, filterKabupaten.value);
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

        const applyEditData = (trigger) => {
            if (!trigger) return;

            const oldProvinsi = trigger.getAttribute('data-kode-provinsi') || '';
            const oldKabupaten = trigger.getAttribute('data-kode-kabupaten') || '';
            const oldKecamatan = trigger.getAttribute('data-kode-kecamatan') || '';

            form.reset();
            editProvinsi.value = oldProvinsi;
            fillKabupatenSelect(editKabupaten, oldProvinsi, oldKabupaten);
            editKodeKecamatan.value = oldKecamatan;
            editNamaKecamatan.value = trigger.getAttribute('data-nama-kecamatan') || '';
            editKategoriKonflik.value = trigger.getAttribute('data-kategori-konflik') || '';

            form.action = <?= json_encode(site_url('/admin/master/kecamatan'), JSON_UNESCAPED_UNICODE); ?> + '/' + encodeURIComponent(oldProvinsi) + '/' + encodeURIComponent(oldKabupaten) + '/' + encodeURIComponent(oldKecamatan) + '/ubah';
        };

        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('button[data-target="#modal-ubah-kecamatan"]');
            if (!trigger) return;
            applyEditData(trigger);
        });

        if (editProvinsi && editKabupaten) {
            editProvinsi.addEventListener('change', function () {
                fillKabupatenSelect(editKabupaten, editProvinsi.value, '');
            });
        }

        modalEdit.addEventListener('show.bs.modal', function (event) {
            applyEditData(event.relatedTarget);
        });
    })();
</script>
<?= $this->endSection(); ?>
