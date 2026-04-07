<div class="card">
    <div class="card-header">
        <h2 class="card-title">Potensi Konflik</h2>
    </div>
    <div class="card-body">
        <table id="table" class="table table-bordered table-striped" style="white-space: nowrap">
            <thead>
                <tr>
                    <th class="text-center">Kabupaten</th>
                    <th class="text-center">Potensi Konflik</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $d) { ?>
                <tr>
                    <td><?= esc($d->nama_kabupaten); ?></td>
                    <td class="text-center"><?php if($d->tingkat_konflik == "TINGGI"){
                        echo '<span class="badge badge-danger">TINGGI</span>';
                    } else if($d->tingkat_konflik == "SEDANG"){
                        echo '<span class="badge badge-warning">SEDANG</span>';
                    } else if($d->tingkat_konflik == "RENDAH"){
                        echo '<span class="badge badge-success">RENDAH</span>';
                    } else {
                        echo '<span class="badge badge-secondary">-</span>';
                    } ?></td>
                    <td>
                        <button class="btn btn-block btn-warning" onclick="update('<?= esc($d->kode_provinsi); ?>', '<?= esc($d->kode_kabupaten); ?>', '<?= esc($d->nama_kabupaten); ?>', '<?= esc($d->tingkat_konflik); ?>')">
                            UPDATE
                        </button>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modal-update">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Update Potensi Konflik</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="<?= base_url(); ?>C_Survey/updatePotensiKonflik" method="POST" class="form-horizontal">
                <div class="modal-body">
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Kabupaten</label>
                        <div class="col-sm-8">
                            <input type="hidden" class="form-control" id="kode_provinsi" name="kode_provinsi" readonly>
                            <input type="hidden" class="form-control" id="kode_kabupaten" name="kode_kabupaten" readonly>
                            <input type="text" class="form-control" id="nama_kabupaten" name="nama_kabupaten" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Potensi Konflik</label>
                        <div class="col-sm-8">
                            <select class="form-control" id="tingkat_konflik" name="tingkat_konflik" required>
                                <option value="">-- Pilih Potensi Konflik --</option>
                                <option value="RENDAH">RENDAH</option>
                                <option value="SEDANG">SEDANG</option>
                                <option value="TINGGI">TINGGI</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
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

function update(kode_provinsi, kode_kabupaten, nama_kabupaten, tingkat_konflik) {
    $('#modal-update #kode_provinsi').val(kode_provinsi);
    $('#modal-update #kode_kabupaten').val(kode_kabupaten);
    $('#modal-update #nama_kabupaten').val(nama_kabupaten);
    $('#modal-update #tingkat_konflik').val(tingkat_konflik);
    $('#modal-update').modal('show');
}
</script>