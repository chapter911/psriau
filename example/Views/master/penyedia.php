<div class="card">
    <div class="card-header">
        <h2 class="card-title">Daftar Penyedia</h2>
        <button class="btn btn-success float-right" data-toggle="modal" data-target="#modalAdd" onclick="resetForm()">Tambah Penyedia</button>
    </div>
    <div class="card-body">
        <table id="table" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="text-center">Nama Penyedia</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $d) { ?>
                <tr>
                    <td><?= esc($d->penyedia); ?></td>
                    <td><button class="btn btn-block btn-warning" onclick="showData('<?= $d->id; ?>', '<?= $d->penyedia; ?>')">UPDATE</button></td>
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
                <h4 class="modal-title">Tambah Penyedia</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="form" action="<?= base_url(); ?>C_Master/savePenyedia" method="POST" class="form-horizontal">
                <input type="hidden" class="form-control" id="id" name="id" value="0" required>
                <div class="modal-body">
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Penyedia</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="penyedia" name="penyedia" required>
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
    $('#id').val('0');
    $('#form')[0].reset();
}
function showData(id, penyedia) {
    $('#id').val(id);
    $('#penyedia').val(penyedia);
    $('#modalAdd').modal('show');
}
</script>