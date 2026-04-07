<div class="card">
    <div class="card-header">
        <h2 class="card-title">Daftar Kelurahan</h2>
        <button class="btn btn-success float-right" data-toggle="modal" data-target="#modalAdd" onclick="resetForm()">Tambah Kelurahan</button>
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
                    <th class="text-center">Kode Kelurahan</th>
                    <th class="text-center">Kelurahan</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalAdd">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah Kelurahan</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="form" action="<?= base_url(); ?>C_Master/saveKelurahan" method="POST" class="form-horizontal">
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
                                <select class="form-control select2" id="kode_kabupaten" name="kode_kabupaten" onchange="setKec()" required>
                                    <option value="" selected>-- Pilih Kabupaten --</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Kecamatan</label>
                        <div class="col-sm-8">
                            <div id="kecamatan-container">
                                <select class="form-control select2" id="kode_kecamatan" name="kode_kecamatan" required>
                                    <option value="" selected>-- Pilih Kecamatan --</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Kode Kelurahan</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="kode_kelurahan" name="kode_kelurahan">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Nama Kelurahan</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="nama_kelurahan" name="nama_kelurahan">
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
let ajax_kecamatan = '00';
$(document).ready(function() {
    $('.table').DataTable({
        "autoWidth": false,
        "order": [0, "asc"],
        "processing": true,
        "serverSide": true,
        "paging": true,
        "searching": false,
        "scrollX": true,
        "ajax": {
            "url": "<?= base_url() ?>C_Master/ajaxKelurahan",
            "type": "post",
            "beforeSend": function() {
                Swal.fire({
                    title: 'Mohon Tunggu',
                    html: 'Mengambil Data',
                    allowOutsideClick: false,
                    showCancelButton: false,
                    showConfirmButton: false,
                });
                Swal.showLoading();
            },
            "data": function(data) {
            },
            "complete": function(response) {
                Swal.close();
            },
            "error": function(jqXHR, textStatus, errorThrown) {
                Swal.close();
            }
        },
    });
});

function resetForm() {
    $('#form')[0].reset();
    $('#kode_provinsi').val('').change();
    $('#kode_kabupaten').val('').change();
    $('#kode_kecamatan').val('').change();
}

function showData(kode_provinsi, kode_kabupaten, kode_kecamatan, kode_kelurahan) {
    $.ajax({
        url: '<?= base_url(); ?>C_Master/getDataKecamatan',
        type: 'POST',
        data: {
            kode_provinsi: kode_provinsi,
            kode_kabupaten: kode_kabupaten,
            kode_kecamatan: kode_kecamatan,
            kode_kelurahan: kode_kelurahan
        },
        success: function(response) {
            var data = JSON.parse(response);
            ajax_kabupaten = data[0].kode_kabupaten;
            ajax_kecamatan = data[0].kode_kecamatan;
            $('#kode_provinsi').val(data[0].kode_provinsi).change();
            $('#kode_kelurahan').val(data[0].kode_kelurahan);
            $('#nama_kelurahan').val(data[0].nama_kelurahan);
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

function setKec() {
    var kode_provinsi = $('#kode_provinsi').val();
    var kode_kabupaten = $('#kode_kabupaten').val();
    $.ajax({
        url: '<?= base_url(); ?>C_Master/getKecamatan',
        type: 'POST',
        data: {
            kode_provinsi: kode_provinsi,
            kode_kabupaten: kode_kabupaten
        },
        success: function(response) {
            var data = JSON.parse(response);
            $('#kecamatan-container select').empty();
            $('#kecamatan-container select').append('<option value="" selected>-- Pilih Kecamatan --</option>');
            for(let i = 0; i < data.length; i++) {
                $('#kecamatan-container select').append('<option value="' + data[i].kode_kecamatan + '">' + data[i].nama_kecamatan + '</option>');
            }
            if(ajax_kecamatan !== '00'){
                $('#kode_kecamatan').val(ajax_kecamatan).change();
            }
        }
    });
}
</script>