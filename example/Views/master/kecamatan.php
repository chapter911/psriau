<div class="card">
    <div class="card-header">
        <h2 class="card-title">Daftar Kecamatan</h2>
        <button class="btn btn-success float-right" data-toggle="modal" data-target="#modalAdd" onclick="resetForm()">Tambah Kecamatan</button>
    </div>
    <div class="card-body">
        <table id="table" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="text-center">Kode Provinsi</th>
                    <th class="text-center">Provinsi</th>
                    <th class="text-center">Kode Kabupaten</th>
                    <th class="text-center">Kabupaten</th>
                    <th class="text-center">Kode Kecamatan</th>
                    <th class="text-center">Kecamatan</th>
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
                    <td><?= esc($d->kode_kecamatan); ?></td>
                    <td><?= esc($d->nama_kecamatan); ?></td>
                    <td><button class="btn btn-block btn-warning" onclick="showData('<?= $d->kode_provinsi; ?>', '<?= $d->kode_kabupaten; ?>', '<?= $d->kode_kecamatan; ?>')">UPDATE</button></td>
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
                <h4 class="modal-title">Tambah Kecamatan</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="form" action="<?= base_url(); ?>C_Master/saveKecamatan" method="POST" class="form-horizontal">
                <div class="modal-body">
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Provinsi</label>
                        <div class="col-sm-8">
                            <select class="form-control select2" id="kode_provinsi" name="kode_provinsi" onchange="setKab()" required>
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
                        <label class="col-sm-4 col-form-label">Kabupaten</label>
                        <div class="col-sm-8">
                            <div id="kabupaten-container">
                                <select class="form-control select2" id="kode_kabupaten" name="kode_kabupaten" required>
                                    <option value="" selected>-- Pilih Kabupaten --</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Kode Kecamatan</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="kode_kecamatan" name="kode_kecamatan">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Nama Kecamatan</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="nama_kecamatan" name="nama_kecamatan">
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
let ajax_kabupaten = '00';
$(document).ready(function() {
    $('.table').DataTable({
        "autoWidth": false,
        "buttons": ["copy", "csv", "excel", "pdf", "print"],
        "order": [0, "asc"]
    }).buttons().container().appendTo('#table_wrapper .col-md-6:eq(0)');
});

function resetForm() {
    $('#form')[0].reset();
    $('#kode_provinsi').val('').change();
    $('#kode_kabupaten').val('').change();
    
}

function showData(kode_provinsi, kode_kabupaten, kode_kecamatan) {
    $.ajax({
        url: '<?= base_url(); ?>C_Master/getDataKecamatan',
        type: 'POST',
        data: {
            kode_provinsi: kode_provinsi,
            kode_kabupaten: kode_kabupaten,
            kode_kecamatan: kode_kecamatan
        },
        success: function(response) {
            var data = JSON.parse(response);
            ajax_kabupaten = data[0].kode_kabupaten;
            $('#kode_provinsi').val(data[0].kode_provinsi).change();
            $('#kode_kecamatan').val(data[0].kode_kecamatan);
            $('#nama_kecamatan').val(data[0].nama_kecamatan);
            $('#modalAdd').modal('show');
        }
    });
}

function setKab() {
    var kode_provinsi = $('#kode_provinsi').val();
    $.ajax({
        url: '<?= base_url(); ?>C_Master/getKabupaten',
        type: 'POST',
        data: {
            kode_provinsi: kode_provinsi
        },
        success: function(response) {
            var data = JSON.parse(response);
            $('#kabupaten-container select').empty();
            $('#kabupaten-container select').append('<option value="" selected>-- Pilih Kabupaten --</option>');
            for(let i = 0; i < data.length; i++) {
                $('#kabupaten-container select').append('<option value="' + data[i].kode_kabupaten + '">' + data[i].nama_kabupaten + '</option>');
            }
            if(ajax_kabupaten !== '00'){
                $('#kode_kabupaten').val(ajax_kabupaten).change();
            }
        }
    });
}
</script>