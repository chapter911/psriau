<div class="card">
    <div class="card-header">
        <h2 class="card-title">Daftar User</h2>
        <button class="btn btn-success float-right" data-toggle="modal" data-target="#modalAdd"
            onclick="resetForm()">Tambah User</button>
    </div>
    <div class="card-body">
        <table id="table" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="text-center">Username</th>
                    <th class="text-center">Name</th>
                    <th class="text-center">Email</th>
                    <th class="text-center">Group</th>
                    <th class="text-center">Jabatan</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Web Access</th>
                    <th class="text-center">Android Access</th>
                    <th class="text-center">Created By</th>
                    <th class="text-center">Created Date</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $d) { ?>
                    <tr>
                        <td><?= strtoupper($d->username); ?></td>
                        <td><?= strtoupper($d->nama); ?></td>
                        <td><?= $d->email; ?></td>
                        <td class="text-center"><?= $d->group_name; ?></td>
                        <td class="text-center"><?= $d->jabatan; ?></td>
                        <td class="text-center">
                            <?= $d->is_active == 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Disabled</span>'; ?>
                        </td>
                        <td class="text-center">
                            <?= $d->web_access == 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Disabled</span>'; ?>
                        </td>
                        <td class="text-center">
                            <?= $d->android_access == 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Disabled</span>'; ?>
                        </td>
                        <td class="text-center"><?= $d->created_by; ?></td>
                        <td class="text-center"><?= date('Y-m-d', strtotime($d->created_date)); ?></td>
                        <td><button class="btn btn-block btn-warning"
                                onclick="showData('<?= $d->username; ?>')">EDIT</button></td>
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
                <h4 class="modal-title">Tambah User</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="<?= base_url(); ?>C_Utility/saveUser" method="POST" class="form-horizontal" id="form">
                <div class="modal-body">
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Username</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="username" name="username" placeholder="Username"
                                required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Password</label>
                        <div class="col-sm-8">
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Password" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Nama</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="nama" name="nama" placeholder="Nama" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Email</label>
                        <div class="col-sm-8">
                            <input type="email" class="form-control" id="email" name="email" placeholder="Email"
                                required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Jabatan</label>
                        <div class="col-sm-8">
                            <select class="form-control" id="jabatan_id" name="jabatan_id" required>
                                <option value="">-- Pilih Jabatan --</option>
                                <?php if (isset($jabatan) && !empty($jabatan)) {
                                    echo "<!-- ";
                                    print_r($jabatan[0]);
                                    echo " -->";
                                    foreach ($jabatan as $j) { ?>
                                        <option value="<?= $j->id; ?>"><?= $j->jabatan; ?></option>
                                    <?php }
                                } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Group User</label>
                        <div class="col-sm-8">
                            <select class="form-control" id="group_id" name="group_id" required>
                                <option value="">-- Pilih Group --</option>
                                <?php foreach ($group as $g) { ?>
                                    <option value="<?= $g->group_id; ?>"><?= $g->group_name; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Status</label>
                        <div class="col-sm-8">
                            <select class="form-control" id="is_active" name="is_active" required>
                                <option value="1">Active</option>
                                <option value="0">Disabled</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Web Access</label>
                        <div class="col-sm-8">
                            <select class="form-control" id="web_access" name="web_access" required>
                                <option value="1">Active</option>
                                <option value="0">Disabled</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Android Access</label>
                        <div class="col-sm-8">
                            <select class="form-control" id="android_access" name="android_access" required>
                                <option value="1">Active</option>
                                <option value="0">Disabled</option>
                            </select>
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
    $(document).ready(function () {
        $('.table').DataTable({
            "autoWidth": false,
            "buttons": ["copy", "csv", "excel", "pdf", "print"]
        }).buttons().container().appendTo('#table_wrapper .col-md-6:eq(0)');
    });

    function resetForm() {
        $('#form')[0].reset();
    }

    function showData(username) {
        $.ajax({
            url: '<?= base_url('C_Utility/getUser'); ?>',
            type: 'POST',
            data: { username: username },
            dataType: 'json',
            success: function (data) {
                if (data.length > 0) {
                    // Assuming data is an array of user objects
                    var user = data[0];
                    $('#username').val(user.username);
                    $('#nama').val(user.nama);
                    $('#email').val(user.email);
                    $('#group_id').val(user.group_id);
                    // Set other fields as necessary
                    $('#modalAdd').modal('show');
                } else {
                    alert('Data not found');
                }
            },
            error: function () {
                alert('Error retrieving data');
            }
        });
    }
</script>