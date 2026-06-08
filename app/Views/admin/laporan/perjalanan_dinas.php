<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
    $reports = $reports ?? [];
    $canEdit = (bool) ($can_edit ?? false);
?>
<div class="card">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title mb-0">Laporan Perjalanan Dinas</h3>
        <div class="card-tools ml-auto">
            <a href="<?= site_url('admin/laporan/perjalanan-dinas/buat'); ?>" class="btn btn-primary btn-sm">Buat Laporan</a>
        </div>
    </div>
    <div class="card-body">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= esc((string) session()->getFlashdata('success')); ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= esc((string) session()->getFlashdata('error')); ?></div>
        <?php endif; ?>

        <div class="card card-outline card-secondary mb-3">
            <div class="card-header py-2">
                <h3 class="card-title mb-0">Filter Laporan Perjalanan Dinas</h3>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="filter_start_date" class="font-weight-bold mb-1">Tanggal Mulai</label>
                        <input type="date" class="form-control form-control-sm" id="filter_start_date">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="filter_end_date" class="font-weight-bold mb-1">Tanggal Selesai</label>
                        <input type="date" class="form-control form-control-sm" id="filter_end_date">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="filter_kota" class="font-weight-bold mb-1">Kota Tujuan</label>
                        <select class="form-control form-control-sm" id="filter_kota" data-placeholder="Semua Kota/Kabupaten">
                            <option value=""></option>
                            <?php foreach ($kabupaten_options ?? [] as $kota): ?>
                                <option value="<?= esc($kota); ?>"><?= esc($kota); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="filter_pelaksana" class="font-weight-bold mb-1">Pelaksana</label>
                        <select class="form-control form-control-sm" id="filter_pelaksana" data-placeholder="Semua Pelaksana">
                            <option value=""></option>
                            <?php foreach ($pegawai_options ?? [] as $peg): ?>
                                <option value="<?= (int) ($peg['id'] ?? 0); ?>"><?= esc($peg['nama'] ?? ''); ?><?= !empty($peg['nip']) ? ' - NIP ' . esc($peg['nip']) : ''; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <small class="text-muted">Data akan diperbarui secara otomatis saat filter diubah.</small>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-reset-filter">Reset Filter</button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped w-100" id="tablePerjalananDinas">
                <thead>
                    <tr>
                        <th style="width:60px;" class="text-center">No</th>
                        <th>Tujuan</th>
                        <th style="width:220px;">Kota Tujuan</th>
                        <th style="width:220px;">Periode</th>
                        <th>Nama Pelaksana</th>
                        <th style="width:110px;" class="text-center">Lihat Dokumen</th>
                        <th style="width:90px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script>
    (function () {
        if (typeof $ === 'undefined' || ! $.fn.DataTable) {
            return;
        }

        const $table = $('#tablePerjalananDinas');
        if (! $table.length || $.fn.dataTable.isDataTable($table)) {
            return;
        }

        const canEdit = <?= json_encode($canEdit, JSON_UNESCAPED_UNICODE); ?>;
        const dataUrl = <?= json_encode(site_url('admin/laporan/perjalanan-dinas'), JSON_UNESCAPED_UNICODE); ?>;

        const $filterStartDate = $('#filter_start_date');
        const $filterEndDate = $('#filter_end_date');
        const $filterKota = $('#filter_kota');
        const $filterPelaksana = $('#filter_pelaksana');

        const dt = $table.DataTable({
            processing: true,
            serverSide: true,
            responsive: false,
            autoWidth: false,
            scrollX: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50], [10, 25, 50]],
            order: [[0, 'desc']],
            ajax: {
                url: dataUrl,
                type: 'GET',
                data: function (d) {
                    d.filter_start_date = $filterStartDate.val();
                    d.filter_end_date = $filterEndDate.val();
                    d.filter_kota = $filterKota.val();
                    d.filter_pelaksana = $filterPelaksana.val();
                }
            },
            columns: [
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { 
                    data: 'tujuan',
                    render: function (data) {
                        return data ? $('<div/>').text(data).html() : '-';
                    }
                },
                { 
                    data: 'kota_tujuan',
                    render: function (data) {
                        return data ? $('<div/>').text(data).html() : '-';
                    }
                },
                { 
                    data: 'periode',
                    render: function (data) {
                        return data ? $('<div/>').text(data).html() : '-';
                    }
                },
                { 
                    data: 'pelaksana_names_label',
                    render: function (data) {
                        return data ? $('<div/>').text(data).html() : '-';
                    }
                },
                {
                    data: 'dokumen_html',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data: 'action_html',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }
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

        // Trigger reload on filter changes
        $filterStartDate.on('change', function () { dt.ajax.reload(); });
        $filterEndDate.on('change', function () { dt.ajax.reload(); });
        $filterKota.on('change', function () { dt.ajax.reload(); });
        $filterPelaksana.on('change', function () { dt.ajax.reload(); });

        // Reset button
        $('#btn-reset-filter').on('click', function () {
            $filterStartDate.val('');
            $filterEndDate.val('');
            
            // Turn off listeners temporarily to avoid multiple updates
            $filterKota.off('change');
            $filterPelaksana.off('change');
            
            $filterKota.val('').trigger('change');
            $filterPelaksana.val('').trigger('change');
            
            // Re-bind listeners
            $filterKota.on('change', function () { dt.ajax.reload(); });
            $filterPelaksana.on('change', function () { dt.ajax.reload(); });
            
            dt.ajax.reload();
        });
    })();
</script>
<?= $this->endSection(); ?>
