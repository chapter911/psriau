<div class="card">
    <div class="card-header">
        <h2 class="card-title">Daftar Dinas</h2>
        <a href="<?= base_url() ?>C_Dinas/AddLaporan" class="btn btn-success float-right">Buat Laporan</a>
    </div>
    <div class="card-body">
        <table id="table" class="table table-bordered table-striped w-100 nowrap">
            <thead>
                <tr style="white-space: nowrap;">
                    <th class="text-center">No</th>
                    <th class="text-center">Nomor Surat Tugas</th>
                    <th class="text-center">Periode</th>
                    <th class="text-center">Kota/Kab Tujuan</th>
                    <th class="text-center">Transportasi</th>
                    <th class="text-center">Pelaksana</th>
                    <th class="text-center">Tujuan</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.table').DataTable({
        "autoWidth": false,
        "buttons": ["copy", "csv", "excel", "pdf", "print"],
        "order": [0, "asc"],
        "scrollX": true,
    }).buttons().container().appendTo('#table_wrapper .col-md-6:eq(0)');
});

function resetForm() {
    $('#id').val('0');
    $('#form')[0].reset();
}
</script>