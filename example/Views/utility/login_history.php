<div class="card">
    <div class="card-header">
        <h2 class="card-title">Login History</h2>
    </div>
    <div class="card-body">
        <table id="table" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="text-center">Login Date</th>
                    <th class="text-center">Username</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $d) { ?>
                <tr>
                    <td class="text-center"><?= $d->created_date; ?></td>
                    <td><?= strtoupper($d->username); ?></td>
                    <td class="text-center">
                        <?= $d->is_logged_in == 1 ? '<span class="badge badge-success">SUCCESS</span>' : '<span class="badge badge-danger">FAILED</span>'; ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.table').DataTable({
        "autoWidth": true,
        "ordering": false,
    });
});
</script>