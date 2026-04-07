<div class="card">
    <div class="card-header">
        <h2 class="card-title">Daftar Madrasah</h2>
    </div>
    <div class="card-body">
        <table id="table" class="table table-bordered table-striped" style="white-space: nowrap">
            <thead>
                <tr>
                    <th class="text-center">NPSN</th>
                    <th class="text-center">Nama</th>
                    <th class="text-center">MAP</th>
                    <th class="text-center">Lokasi</th>
                    <th class="text-center">Periode</th>
                    <th class="text-center">Jumlah Siswa</th>
                    <th class="text-center">Survey Tingkat Kerusakan</th>
                    <th class="text-center">Survey Klasifikasi Kerusakan</th>
                    <th class="text-center">Status Lahan</th>
                    <th class="text-center">Status Penangan</th>
                    <th class="text-center">Ekspos Tingkat Kerusakan</th>
                    <th class="text-center">Ekspos Klasifikasi Kerusakan</th>
                    <th class="text-center">Ekspos Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $d) { ?>
                <tr>
                    <td><a href="<?= base_url() ?>C_Survey/DetailMadrasah/<?= esc($d->npsn); ?>" class="btn btn-block btn-warning"><?= esc($d->npsn); ?></a></td>
                    <td><?= esc($d->nama); ?></td>
                    <td><button class="btn btn-block btn-info" onclick="openMap('<?= esc($d->latitude); ?>', '<?= esc($d->longitude); ?>')"><i class="fas fa-map-marked-alt"></i></button></td>
                    <td><?= esc($d->kabupaten) . ' ' . esc($d->kecamatan); ?></td>
                    <td><?= esc($d->periode); ?></td>
                    <td><?= esc($d->emis_jumlah_siswa); ?></td>
                    <td><?= esc($d->survey_tingat_kerusakan); ?></td>
                    <td><?= esc($d->survey_klasifikasi_kerusakan); ?></td>
                    <td><?= esc($d->status_lahan); ?></td>
                    <td><?= esc($d->status_penanganan); ?></td>
                    <td><?= esc($d->ekspos_tingkat_kerusakan); ?></td>
                    <td><?= esc($d->ekspos_klasifikasi_kerusakan); ?></td>
                    <td><?= esc($d->ekspos_status); ?></td>
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
                <h4 class="modal-title">Tambah Sekolah</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="<?= base_url(); ?>C_Utility/saveUser?>" method="POST" class="form-horizontal">
                <div class="modal-body">
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Username</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
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
                            <input type="text" class="form-control" id="email" name="email" placeholder="Email" required>
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

<div class="modal fade" id="modalMap">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 id="modal-title-sekolah" class="modal-title">Map Lokasi</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="map" style="height: 300px;"></div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                <a href="#" target="_blank" id="openGoogleMap" class="btn btn-success">Buka Google Map</a>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#table').DataTable({
        "autoWidth": false,
        "buttons": ["copy", "csv", "excel", "pdf", "print"],
        "order": [4, "asc"],
        "scrollX": true
    }).buttons().container().appendTo('#table_wrapper .col-md-6:eq(0)');
});

var map = L.map('map').setView([0.5154399, 101.4441507], 8);

var googleHybrid = L.tileLayer('http://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {
    maxZoom: 20,
    subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
}).addTo(map);

function resetForm() {
    $('#form')[0].reset();
}

function openMap(lat, lng){
    $('#modalMap').modal('show');
    setInterval(function() {
        map.invalidateSize();
    }, 400);
    map.setView([lat, lng], 15);
    
    map.eachLayer((layer) => {
        if (layer instanceof L.Marker) {
            map.removeLayer(layer);
        }
    });
    
    L.marker([lat, lng]).addTo(map);
    $('#openGoogleMap').attr('href', 'https://www.google.com/maps/search/?api=1&query=' + lat + ',' + lng);
}
</script>