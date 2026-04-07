<div class="card">
    <div class="card-header">
        <h2 class="card-title">Daftar Sekolah</h2>
        <button class="btn btn-success float-right" data-toggle="modal" data-target="#modalAdd" onclick="resetForm()">Tambah Sekolah</button>
    </div>
    <div class="card-body">
        <table id="table" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="text-center">NPSN</th>
                    <th class="text-center">Nama</th>
                    <th class="text-center">Jenis</th>
                    <th class="text-center">NSM</th>
                    <th class="text-center">Kabupaten</th>
                    <th class="text-center">Kecamatan</th>
                    <th class="text-center">Koordinat</th>
                    <th class="text-center">MAP</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $d) { ?>
                <tr>
                    <td><?= esc($d->npsn); ?></td>
                    <td><?= esc($d->nama); ?></td>
                    <td><?= esc($d->jenis); ?></td>
                    <td><?= esc($d->nsm); ?></td>
                    <td><?= esc($d->kabupaten); ?></td>
                    <td><?= esc($d->kecamatan); ?></td>
                    <td><?= esc($d->latitude) . ', ' . esc($d->longitude); ?></td>
                    <td><button class="btn btn-block btn-info" onclick="openMap('<?= esc($d->latitude); ?>', '<?= esc($d->longitude); ?>')"><i class="fas fa-map-marked-alt"></i></button></td>
                    <td><button class="btn btn-block btn-warning" onclick="showData('<?= $d->npsn; ?>')">UPDATE</button></td>
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

            <form id="form-sekolah" action="<?= base_url(); ?>C_Master/saveSekolah" method="POST" class="form-horizontal">
                <div class="modal-body">
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">NPSN</label>
                        <div class="col-sm-8">
                            <input type="hidden" class="form-control" id="is_update" name="is_update" value="0">
                            <input type="number" class="form-control" id="npsn" name="npsn" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">NAMA</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="nama" name="nama">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">JENIS</label>
                        <div class="col-sm-8">
                            <select class="form-control" id="jenis" name="jenis">
                                <option value="">- PILIH JENIS -</option>
                                <option value="Madrasah">Madrasah</option>
                                <option value="Sekolah Rakyat">Sekolah Rakyat</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">NSM</label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control" id="nsm" name="nsm">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">KABUPATEN</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="kabupaten" name="kabupaten">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">KECAMATAN</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="kecamatan" name="kecamatan">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">LATITUDE</label>
                        <div class="col-sm-8">
                            <input type="number" step="any" class="form-control" id="latitude" name="latitude">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">LONGITUDE</label>
                        <div class="col-sm-8">
                            <input type="number" step="any" class="form-control" id="longitude" name="longitude">
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
    $('.table').DataTable({
        "autoWidth": false,
        "buttons": ["copy", "csv", "excel", "pdf", "print"],
        "order": [4, "asc"]
    }).buttons().container().appendTo('#table_wrapper .col-md-6:eq(0)');
});

function resetForm() {
    $("#npsn").attr('readonly', false);
    $("#is_update").val("0");
    $('#form-sekolah')[0].reset();
}

function showData(npsn) {
    $.ajax({
        url: '<?= base_url('C_Master/getDataSekolah'); ?>',
        type: 'POST',
        data: { npsn: npsn },
        dataType: 'json',
        success: function(response) {
            $("#npsn").val(response[0]['npsn']).attr('readonly', true);
            $("#nama").val(response[0]['nama']);
            $("#jenis").val(response[0]['jenis']);
            $("#nsm").val(response[0]['nsm']);
            $("#kabupaten").val(response[0]['kabupaten']);
            $("#kecamatan").val(response[0]['kecamatan']);
            $("#latitude").val(response[0]['latitude']);
            $("#longitude").val(response[0]['longitude']);

            $("#is_update").val("1");
            $('#modalAdd').modal('show');
        },
        error: function() {
            alert('Error retrieving data');
        }
    });
}

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