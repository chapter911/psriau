<div class="card">
    <div class="card-header">
        <h2 class="card-title">Daftar Konsultan</h2>
        <button class="btn btn-success float-right" data-toggle="modal" data-target="#modalAdd" onclick="resetForm()">Tambah Konsultan</button>
    </div>
    <div class="card-body">
        <table id="table" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="text-center">Nama Konsultan</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $d) { ?>
                <tr>
                    <td><?= esc($d->konsultan); ?></td>
                    <td><button class="btn btn-block btn-warning" onclick="showData('<?= $d->id; ?>', '<?= $d->konsultan; ?>')">UPDATE</button></td>
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
                <h4 class="modal-title">Tambah Konsultan</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="form" action="<?= base_url(); ?>C_Master/saveKonsultan" method="POST" class="form-horizontal">
                <input type="hidden" class="form-control" id="id" name="id" value="0" required>
                <div class="modal-body">
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Konsultan</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="konsultan" name="konsultan" required>
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
function showData(id, konsultan) {
    $('#id').val(id);
    $('#konsultan').val(konsultan);
    $('#modalAdd').modal('show');
}
</script>