<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
$provinsiOptions = $provinsiOptions ?? [];
$canAdd = (bool) ($can_add ?? false);
$canEdit = (bool) ($can_edit ?? false);
?>

<div class="card">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title mb-0">Daftar Kabupaten/Kota</h3>
        <?php if ($canAdd): ?>
            <div class="card-tools ml-auto">
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-tambah-kabupaten">Tambah Kabupaten</button>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="filter_kabupaten_provinsi">Filter Provinsi</label>
                <select id="filter_kabupaten_provinsi" class="form-control">
                    <option value="">Semua Provinsi</option>
                    <?php foreach ($provinsiOptions as $prov): ?>
                        <option value="<?= esc((string) ($prov['kode_provinsi'] ?? '')); ?>"><?= esc((string) (($prov['kode_provinsi'] ?? '') . ' - ' . ($prov['nama_provinsi'] ?? ''))); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped w-100 nowrap" id="tableKabupaten">
                <thead>
                    <tr>
                        <th class="text-center" style="width:60px;">#</th>
                        <th class="text-center">PROVINSI</th>
                        <th class="text-center">KODE KABUPATEN</th>
                        <th class="text-center">NAMA KABUPATEN</th>
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
<div class="modal fade" id="modal-tambah-kabupaten" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Kabupaten/Kota</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('/admin/master/kabupaten/tambah'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Provinsi</label>
                        <select name="kode_provinsi" class="form-control" required>
                            <option value="">Pilih Provinsi</option>
                            <?php foreach (($provinsiOptions ?? []) as $prov): ?>
                                <option value="<?= esc((string) ($prov['kode_provinsi'] ?? '')); ?>"><?= esc((string) (($prov['kode_provinsi'] ?? '') . ' - ' . ($prov['nama_provinsi'] ?? ''))); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kode Kabupaten</label>
                        <input type="text" name="kode_kabupaten" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Kabupaten/Kota</label>
                        <input type="text" name="nama_kabupaten" class="form-control" required>
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
<div class="modal fade" id="modal-ubah-kabupaten" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Kabupaten/Kota</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-ubah-kabupaten" action="" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Provinsi</label>
                        <select id="edit_kode_provinsi" name="kode_provinsi" class="form-control" required>
                            <option value="">Pilih Provinsi</option>
                            <?php foreach (($provinsiOptions ?? []) as $prov): ?>
                                <option value="<?= esc((string) ($prov['kode_provinsi'] ?? '')); ?>"><?= esc((string) (($prov['kode_provinsi'] ?? '') . ' - ' . ($prov['nama_provinsi'] ?? ''))); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kode Kabupaten</label>
                        <input type="text" id="edit_kode_kabupaten" name="kode_kabupaten" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Kabupaten/Kota</label>
                        <input type="text" id="edit_nama_kabupaten" name="nama_kabupaten" class="form-control" required>
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

        const $table = $('#tableKabupaten');
        if (! $table.length || $.fn.dataTable.isDataTable($table)) {
            return;
        }

        const canEdit = <?= json_encode($canEdit, JSON_UNESCAPED_UNICODE); ?>;
        const dataUrl = <?= json_encode(site_url('/admin/master/kabupaten'), JSON_UNESCAPED_UNICODE); ?>;
        const $filterProvinsi = $('#filter_kabupaten_provinsi');

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
                    d.filter_provinsi = $filterProvinsi.val();
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
                { data: 'kode_kabupaten' },
                { data: 'nama_kabupaten' }
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

        $filterProvinsi.on('change', function () {
            dt.ajax.reload();
        });

        const modalEdit = document.getElementById('modal-ubah-kabupaten');
        if (!modalEdit) {
            return;
        }

        const form = document.getElementById('form-ubah-kabupaten');
        const editProvinsi = document.getElementById('edit_kode_provinsi');
        const editKabupaten = document.getElementById('edit_kode_kabupaten');
        const editNama = document.getElementById('edit_nama_kabupaten');

        const applyEditData = (trigger) => {
            if (!trigger) return;

            const oldProvinsi = trigger.getAttribute('data-kode-provinsi') || '';
            const oldKabupaten = trigger.getAttribute('data-kode-kabupaten') || '';
            form.reset();
            editProvinsi.value = oldProvinsi;
            editKabupaten.value = oldKabupaten;
            editNama.value = trigger.getAttribute('data-nama-kabupaten') || '';
            form.action = <?= json_encode(site_url('/admin/master/kabupaten'), JSON_UNESCAPED_UNICODE); ?> + '/' + encodeURIComponent(oldProvinsi) + '/' + encodeURIComponent(oldKabupaten) + '/ubah';
        };

        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('button[data-target="#modal-ubah-kabupaten"]');
            if (!trigger) return;
            applyEditData(trigger);
        });

        modalEdit.addEventListener('show.bs.modal', function (event) {
            applyEditData(event.relatedTarget);
        });
    })();
</script>
<?= $this->endSection(); ?>
