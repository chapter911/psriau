<div class="card">
    <div class="card-header">
        <h2 class="card-title">Daftar Kabupaten</h2>
        <button class="btn btn-success float-right" data-toggle="modal" data-target="#modalAdd" onclick="resetForm()">Tambah Kabupaten</button>
    </div>
    <div class="card-body">
        <table id="table" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="text-center">Kode Provinsi</th>
                    <th class="text-center">Provinsi</th>
                    <th class="text-center">Kode Kabupaten</th>
                    <th class="text-center">Kabupaten</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $d) { ?>
                <tr>
                    <td><?= esc($d->kode_provinsi); ?></td>
                    <td><?= esc($d->nama_provinsi); ?></td>
                    <td><?= esc($d->kode_kabupaten); ?></td>
                    <td><?= esc($d->nama_kabupaten); ?></td>
                    <td><button class="btn btn-block btn-warning" onclick="showData('<?= $d->kode_provinsi; ?>', '<?= $d->kode_kabupaten; ?>')">UPDATE</button></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalAdd">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah Kabupaten</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="form" action="<?= base_url(); ?>C_Master/saveKabupaten" method="POST" class="form-horizontal">
                <div class="modal-body">
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Provinsi</label>
                        <div class="col-sm-8">
                            <select class="form-control select2" id="kode_provinsi" name="kode_provinsi" required>
                                <option value="" selected>-- Pilih Provinsi --</option>
                                <?php
                                foreach ($provinsi as $prov) {
                                    echo '<option value="' . $prov->kode_provinsi . '">' . $prov->nama_provinsi . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Kode Kabupaten</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="kode_kabupaten" name="kode_kabupaten">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Nama Kabupaten</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="nama_kabupaten" name="nama_kabupaten">
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

<script>
$(document).ready(function() {
    $('.table').DataTable({
        "autoWidth": false,
        "buttons": ["copy", "csv", "excel", "pdf", "print"],
        "order": [0, "asc"]
    }).buttons().container().appendTo('#table_wrapper .col-md-6:eq(0)');
});

function resetForm() {
    $('#form')[0].reset();
}

function showData(kode_provinsi, kode_kabupaten) {
    $.ajax({
        url: '<?= base_url(); ?>C_Master/getDataKabupaten',
        type: 'POST',
        data: {
            kode_provinsi: kode_provinsi,
            kode_kabupaten: kode_kabupaten
        },
        success: function(response) {
            var data = JSON.parse(response);
            $('#kode_provinsi').val(data[0].kode_provinsi).change();
            $('#kode_kabupaten').val(data[0].kode_kabupaten);
            $('#nama_kabupaten').val(data[0].nama_kabupaten);
            $('#modalAdd').modal('show');
        }
    });
}
</script>