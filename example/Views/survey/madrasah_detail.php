<div class="card">
    <div class="card-header">
        <h2 class="card-title">Detail Madrasah</h2>
        <button class="btn btn-success float-right" data-toggle="modal" data-target="#modalAdd" onclick="resetForm()">Input Survey</button>
    </div>
    <div class="card-body">
        <table id="table" class="table table-bordered table-striped" style="white-space: nowrap">
            <thead>
                <tr>
                    <th class="text-center">Periode</th>
                    <th class="text-center">Jumlah Siswa</th>
                    <th class="text-center">Survey Tingkat Kerusakan</th>
                    <th class="text-center">Survey Klasifikasi Kerusakan</th>
                    <th class="text-center">Status Lahan</th>
                    <th class="text-center">Status Penangan</th>
                    <th class="text-center">Ekspos Tingkat Kerusakan</th>
                    <th class="text-center">Ekspos Klasifikasi Kerusakan</th>
                    <th class="text-center">Ekspos Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detail as $d) { ?>
                <tr>
                    <td><?= esc($d->periode); ?></td>
                    <td><?= esc($d->emis_jumlah_siswa); ?></td>
                    <td><?= esc($d->survey_tingat_kerusakan); ?></td>
                    <td><?= esc($d->survey_klasifikasi_kerusakan); ?></td>
                    <td><?= esc($d->status_lahan); ?></td>
                    <td><?= esc($d->status_penanganan); ?></td>
                    <td><?= esc($d->ekspos_tingkat_kerusakan); ?></td>
                    <td><?= esc($d->ekspos_klasifikasi_kerusakan); ?></td>
                    <td><?= esc($d->ekspos_status); ?></td>
                    <td><button class="btn btn-block btn-warning" onclick="getDetail('<?= $d->id; ?>')">UPDATE</button> </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalAdd">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 id="modal-title" class="modal-title">Input Survey</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="form-input" action="<?= base_url(); ?>C_Survey/saveDetail" method="POST" class="form-horizontal">
                <div class="modal-body">
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">PERIODE</label>
                        <div class="col-sm-8">
                            <input type="hidden" class="form-control" id="id" name="id" value="0">
                            <input type="hidden" class="form-control" id="npsn" name="npsn" value="<?= $sekolah[0]->npsn; ?>">
                            <input type="date" class="form-control" id="periode" name="periode" onclick="this.showPicker()" value="<?= date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">EMIS JUMLAH SISWA</label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control" id="emis_jumlah_siswa" name="emis_jumlah_siswa">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">SURVEY JUMLAH SISWA</label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control" id="survey_jumlah_siswa" name="survey_jumlah_siswa">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">SURVEY TINGAT KERUSAKAN (%)</label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control" id="survey_tingat_kerusakan" name="survey_tingat_kerusakan">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">SURVEY KLASIFIKASI KERUSAKAN</label>
                        <div class="col-sm-8">
                            <select class="form-control" id="survey_klasifikasi_kerusakan" name="survey_klasifikasi_kerusakan">
                                <option value="">- Belum Klasifikasi -</option>
                                <?php foreach ($klasifikasi as $k) { ?>
                                    <option value="<?= $k->survey_klasifikasi_kerusakan; ?>"><?= $k->survey_klasifikasi_kerusakan; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">STATUS LAHAN</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="status_lahan" name="status_lahan">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">STATUS PENANGANAN</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="status_penanganan" name="status_penanganan">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">EKSPOS TINGKAT KERUSAKAN</label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control" id="ekspos_tingkat_kerusakan" name="ekspos_tingkat_kerusakan">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">EKSPOS KLASIFIKASI KERUSAKAN</label>
                        <div class="col-sm-8">
                            <select class="form-control" id="ekspos_klasifikasi_kerusakan" name="ekspos_klasifikasi_kerusakan">
                                <option value="">- Belum Klasifikasi -</option>
                                <?php foreach ($klasifikasi as $k) { ?>
                                    <option value="<?= $k->survey_klasifikasi_kerusakan; ?>"><?= $k->survey_klasifikasi_kerusakan; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">EKSPOS STATUS</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="ekspos_status" name="ekspos_status">
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
    $('#table').DataTable({
        "autoWidth": false,
        "buttons": ["copy", "csv", "excel", "pdf", "print"],
        "scrollX": true
    }).buttons().container().appendTo('#table_wrapper .col-md-6:eq(0)');
});

function resetForm() {
    $('#modal-title').html("Input Survey");
    $("#id").val("0");
    $('#form-input')[0].reset();
}

function getDetail(id){
    $.ajax({
        url: "<?= base_url() ?>C_Survey/getDetail",
        type: "post",
        data: {
            id: id,
        },
        beforeSend: function() {
            Swal.fire({
                title: 'Mohon Tunggu',
                html: 'Mengambil Data',
                allowOutsideClick: false,
                showCancelButton: false,
                showConfirmButton: false,
            });
            Swal.showLoading();
        },
        success: function(response) {
            Swal.close();
            var data = JSON.parse(response);
            $("#id").val(data['survey'][0]['id']);
            $("#periode").val(data['survey'][0]['periode']);
            $("#emis_jumlah_siswa").val(data['survey'][0]['emis_jumlah_siswa']);
            $("#survey_jumlah_siswa").val(data['survey'][0]['survey_jumlah_siswa']);
            $("#survey_tingat_kerusakan").val(data['survey'][0]['survey_tingat_kerusakan']);
            $("#survey_klasifikasi_kerusakan").val(data['survey'][0]['survey_klasifikasi_kerusakan']);
            $("#status_lahan").val(data['survey'][0]['status_lahan']);
            $("#status_penanganan").val(data['survey'][0]['status_penanganan']);
            $("#ekspos_tingkat_kerusakan").val(data['survey'][0]['ekspos_tingkat_kerusakan']);
            $("#ekspos_klasifikasi_kerusakan").val(data['survey'][0]['ekspos_klasifikasi_kerusakan']);
            $("#ekspos_status").val(data['survey'][0]['ekspos_status']);

            $('#modal-title').html("Update Survey");
            $('#modalAdd').modal('show');
        },
        error: function(jqXHR, textStatus, errorThrown) {
            Swal.close();
            console.log(textStatus, errorThrown);
        }
    });
}
</script>