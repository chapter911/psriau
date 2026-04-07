<div class="card">
    <div class="card-header">
        <h2 class="card-title">Daftar Group User</h2>
        <button class="btn btn-success float-right" data-toggle="modal" data-target="#modalAdd" onclick="resetForm()">Tambah Group</button>
    </div>
    <div class="card-body">
        <table id="table" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="text-center">Group Name</th>
                    <th class="text-center">Remark</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Created By</th>
                    <th class="text-center">Created Date</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $d) { ?>
                <tr>
                    <td><?= strtoupper($d->group_name); ?></td>
                    <td><?= $d->remark; ?></td>
                    <td class="text-center">
                        <?= $d->is_active == 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Disabled</span>'; ?>
                    </td>
                    <td class="text-center"><?= $d->created_by; ?></td>
                    <td class="text-center"><?= date('Y-m-d', strtotime($d->created_date)); ?></td>
                    <td class="text-center">
                        <button class="btn btn-success" onclick="showData('<?= $d->group_id; ?>')">UPDATE</button>
                        <button class="btn btn-warning" onclick="showAccess('<?= $d->group_id; ?>')">ACCESS EDIT</button>
                    </td>
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
                <h4 class="modal-title">Tambah Group</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="<?= base_url() ?>C_Utility/SaveGroup" id="form" class="form-horizontal" method="post">
                <div class="modal-body">
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Group Name</label>
                        <div class="col-sm-10">
                            <input type="hidden" class="form-control" id="group_id" name="group_id" value="0">
                            <input type="text" class="form-control" id="group_name" name="group_name" placeholder="Group Name" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Remark</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="remark" name="remark" placeholder="Remark" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Is Active</label>
                        <div class="col-sm-10">
                            <input type="checkbox" name="is_active" checked data-bootstrap-switch>
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

<div class="modal fade" id="modalGroupAccess">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Group Access</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="<?= base_url() ?>C_Utility/UpdateGroupAccess" id="form" class="form-horizontal" method="post">
                <div class="modal-body">
                    <div id="group_access_container"></div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.table').DataTable({
        "autoWidth": false
    });
});

function resetForm() {
    $('#group_id').val(0);
    $('#form')[0].reset();
}

function showData(group_id) {
    $.ajax({
        url: '<?= base_url('C_Utility/getUserGroup'); ?>',
        type: 'POST',
        data: { group_id: group_id },
        dataType: 'json',
        success: function(data) {
            if (data.length > 0) {
                $('#group_id').val(data[0].group_id);
                $('#group_name').val(data[0].group_name);
                $('#remark').val(data[0].remark);
                $('#modalAdd').modal('show');
            } else {
                alert('Data not found');
            }
        },
        error: function() {
            alert('Error retrieving data');
        }
    });
}

function showAccess(group_id){
    $.ajax({
        url: '<?= base_url('C_Utility/getGroupAccess'); ?>',
        type: 'POST',
        data: { group_id: group_id },
        success: function(data) {
            $('#group_access_container').html(data);
            $('#modalGroupAccess').modal('show');
        },
        error: function() {
            alert('Error retrieving data');
        }
    });
}
</script>