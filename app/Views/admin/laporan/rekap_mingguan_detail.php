<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
$rekap = $rekap ?? [];
$sekolahName = $sekolahName ?? '';
?>

<div class="d-flex align-items-center mb-3">
    <a href="<?= site_url('admin/laporan/rekap-mingguan/show/' . (int)$rekap['id']); ?>" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Rekapitulasi
    </a>
</div>

<div class="card card-outline card-primary mb-4">
    <div class="card-header py-2">
        <h3 class="card-title mb-0 font-weight-bold text-primary">
            <i class="fas fa-school mr-1"></i> <?= esc($sekolahName); ?>
        </h3>
    </div>
    <div class="card-body py-2">
        <div class="row">
            <div class="col-sm-6">
                <span class="text-muted small d-block">Periode Laporan</span>
                <span class="font-weight-bold text-dark"><?= esc($rekap['judul']); ?></span>
            </div>
            <div class="col-sm-6">
                <span class="text-muted small d-block">Paket</span>
                <span class="font-weight-bold text-dark"><?= esc($rekap['nama_paket'] ?? 'Tanpa Paket'); ?></span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-light">
        <h3 class="card-title mb-0 font-weight-bold"><i class="fas fa-list-alt mr-1"></i> Rincian Item Pekerjaan RAB</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover table-sm w-100" id="tableWeeklyRabDetail" style="font-size: 0.8rem;">
                <thead class="thead-light text-center">
                    <tr>
                        <th rowspan="2" class="align-middle" style="width: 40px;">No</th>
                        <th rowspan="2" class="align-middle" style="width: 50px;">No Urut</th>
                        <th rowspan="2" class="align-middle" style="min-width: 250px;">Uraian Pekerjaan</th>
                        <th rowspan="2" class="align-middle" style="width: 60px;">Volume</th>
                        <th rowspan="2" class="align-middle" style="width: 50px;">Satuan</th>
                        <th rowspan="2" class="align-middle" style="width: 110px;">Harga Satuan</th>
                        <th rowspan="2" class="align-middle" style="width: 120px;">Jumlah Harga</th>
                        <th rowspan="2" class="align-middle" style="width: 80px;">Bobot (%)</th>
                        <th colspan="2" class="py-1">Progres Minggu Lalu</th>
                        <th colspan="2" class="py-1">Progres Minggu Ini</th>
                        <th colspan="2" class="py-1">Progres s/d Minggu Ini</th>
                        <th rowspan="2" class="align-middle" style="width: 70px;">Progres Pekerjaan %</th>
                        <th rowspan="2" class="align-middle" style="width: 70px;">Deviasi Progres</th>
                        <th rowspan="2" class="align-middle" style="width: 70px;">Sisa Progres</th>
                    </tr>
                    <tr>
                        <th class="py-1">Vol</th>
                        <th class="py-1">Bobot %</th>
                        <th class="py-1">Vol</th>
                        <th class="py-1">Bobot %</th>
                        <th class="py-1">Vol</th>
                        <th class="py-1">Bobot %</th>
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
$(document).ready(function() {
    $('#tableWeeklyRabDetail').DataTable({
        processing: true,
        serverSide: true,
        scrollX: true,
        ajax: {
            url: '<?= site_url('admin/laporan/rekap-mingguan/data-detail/' . $rekap['id']); ?>',
            type: 'GET',
            data: function(d) {
                d.sekolah = '<?= esc($sekolahName, 'js'); ?>';
            }
        },
        columns: [
            {
                data: null,
                sortable: false,
                searchable: false,
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                },
                className: 'text-center align-middle'
            },
            { data: 'no_urut', className: 'text-center align-middle font-weight-bold' },
            { 
                data: 'uraian_escaped', 
                className: 'align-middle',
                render: function(data, type, row) {
                    // Check if it's a category/header (no volume/price)
                    if ((row.volume === null || row.volume === '') && (row.harga_satuan === null || row.harga_satuan === '')) {
                        return '<strong style="color: #2F3A45;">' + data + '</strong>';
                    }
                    return '<span style="padding-left: 10px;">' + data + '</span>';
                }
            },
            { data: 'volume_formatted', className: 'text-right align-middle' },
            { data: 'satuan', className: 'text-center align-middle' },
            { data: 'harga_satuan_formatted', className: 'text-right align-middle' },
            { data: 'jumlah_harga_formatted', className: 'text-right align-middle font-weight-bold' },
            { data: 'bobot_formatted', className: 'text-right align-middle' },
            
            // Minggu Lalu
            { data: 'progres_minggu_lalu_vol_formatted', className: 'text-right align-middle' },
            { data: 'progres_minggu_lalu_bobot_formatted', className: 'text-right align-middle' },
            
            // Minggu Ini
            { data: 'progres_minggu_ini_vol_formatted', className: 'text-right align-middle' },
            { data: 'progres_minggu_ini_bobot_formatted', className: 'text-right align-middle' },
            
            // S/d Minggu Ini
            { data: 'progres_sampai_minggu_ini_vol_formatted', className: 'text-right align-middle' },
            { data: 'progres_sampai_minggu_ini_bobot_formatted', className: 'text-right align-middle font-weight-bold text-success' },
            
            { data: 'progres_pekerjaan_persen_formatted', className: 'text-right align-middle' },
            { data: 'deviasi_progres_formatted', className: 'text-right align-middle text-danger' },
            { data: 'sisa_progres_formatted', className: 'text-right align-middle' }
        ],
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            processing: 'Memproses...',
            zeroRecords: 'Tidak ada data',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty: 'Menampilkan 0 - 0 dari 0 data',
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            paginate: {
                first: 'Pertama',
                last: 'Terakhir',
                next: 'Berikutnya',
                previous: 'Sebelumnya'
            }
        }
    });
});
</script>
<?= $this->endSection(); ?>
