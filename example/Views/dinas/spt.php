<div class="card">
    <div class="card-header">
        <h2 class="card-title">Daftar SPT</h2>
        <div class="float-right">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-buat-spt">Buat SPT</button>
            <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modal-lg">Import Excel</button>
            <a href="<?= base_url('C_Dinas/import_spt') ?>" class="btn btn-warning">Refresh</a>
        </div>
    </div>
    <div class="card-body">
        <table id="table" class="table table-bordered table-striped w-100 nowrap">
            <thead>
                <tr style="white-space: nowrap;">
                    <th class="text-center">No</th>
                    <th class="text-center">Nomor Surat Tugas</th>
                    <th class="text-center">Rangka</th>
                    <th class="text-center">Pelaksana</th>
                    <th class="text-center">Periode</th>
                    <th class="text-center">Tujuan</th>
                    <th class="text-center">Transportasi</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($data as $d) { ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= $d->spt_no; ?></td>
                        <td><?= $d->rangka; ?></td>
                        <td><?= $d->jumlah_pelaksana; ?> Orang</td>
                        <td><?= $d->tanggal_awal . ' s/d ' . $d->tanggal_akhir; ?></td>
                        <td><?= $d->tujuan; ?></td>
                        <td><?= $d->transportasi; ?></td>
                        <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn-success" data-toggle="dropdown">Export</button>
                                <button type="button" class="btn btn-success dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                    <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu" role="menu">
                                    <a href="<?= base_url() ?>C_Dinas/Export/SPT/<?= $d->spt_id ; ?>" class="dropdown-item" target="_blank">SPT / SPD</a>
                                    <a href="<?= base_url() ?>C_Dinas/Export/Nomina/<?= $d->spt_id ; ?>" class="dropdown-item" target="_blank">NOMINATIF</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modal-lg">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Import Data SPT</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= base_url('C_Dinas/import_spt') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="excel_file">Pilih File Excel</label>
                        <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xls, .xlsx" required>
                        <small class="form-text text-muted">Unduh template excel <a href="https://docs.google.com/spreadsheets/d/108I0SXwsyTmCYr6ddblurizhssSFnRBc1yrwUIP3vKk/edit?usp=sharing" target="_blank">disini</a></small>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.table').DataTable({
        "autoWidth": false,
        "buttons": ["copy", "csv", "excel", "pdf", "print"],
        "order": [0, "asc"],
        "scrollX": true,
    }).buttons().container().appendTo('#table_wrapper .col-md-6:eq(0)');
});

function resetForm() {
    $('#id').val('0');
    $('#form')[0].reset();
}
</script>