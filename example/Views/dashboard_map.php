<div id="map" style="height: 100%;"></div>

<div class="modal fade" id="modalDetail">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Keterangan</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tabs-madrasah" data-toggle="pill" href="#custom-tabs-madrasah" role="tab" aria-controls="custom-tabs-madrasah" aria-selected="true">Madrasah</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tabs-survey" data-toggle="pill" href="#custom-tabs-survey" role="tab" aria-controls="custom-tabs-survey" aria-selected="true">Survey</a>
                    </li>
                </ul>
                <br/>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="custom-tabs-madrasah" role="tabpanel" aria-labelledby="custom-tabs-madrasah">
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">NPSN</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="dtl_npsn" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">NAMA</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="dtl_nama" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">JENIS</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="dtl_jenis" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">NSM</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="dtl_nsm" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">KABUPATEN</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="dtl_kabupaten" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">KECAMATAN</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="dtl_kecamatan" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">LATITUDE</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="dtl_latitude" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">LONGITUDE</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="dtl_longitude" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="custom-tabs-survey" role="tabpanel" aria-labelledby="custom-tabs-survey-tab">
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">PERIODE</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="dtl_periode" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">EMIS JUMLAH SISWA</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="dtl_emis_jumlah_siswa" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">SURVEY JUMLAH SISWA</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="dtl_survey_jumlah_siswa" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">SURVEY TINGAT KERUSAKAN</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="dtl_survey_tingat_kerusakan" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">SURVEY KLASIFIKASI KERUSAKAN</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="dtl_survey_klasifikasi_kerusakan" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">STATUS LAHAN</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="dtl_status_lahan" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">STATUS PENANGANAN</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="dtl_status_penanganan" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">EKSPOS TINGKAT KERUSAKAN</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="dtl_ekspos_tingkat_kerusakan" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">EKSPOS KLASIFIKASI KERUSAKAN</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="dtl_ekspos_klasifikasi_kerusakan" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">EKSPOS STATUS</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="dtl_ekspos_status" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>

$(document).ready(function() {
    $("#total_data").val("<?= count($data); ?>");
});

var savedLat = localStorage.getItem('leafletMapCenterLat');
var savedLng = localStorage.getItem('leafletMapCenterLng');
var savedZoom = localStorage.getItem('leafletMapZoom');

var initialLat = savedLat ? parseFloat(savedLat) : 0.5154399;
var initialLng = savedLng ? parseFloat(savedLng) : 101.4441507;
var initialZoom = savedZoom ? parseInt(savedZoom) : 8;

var map = L.map('map').setView([initialLat, initialLng], initialZoom);

var iconBlue = L.icon({
    iconUrl: '<?= base_url('public/img/marker-icon-2x-blue.png') ?>',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
});

var iconRed = L.icon({
    iconUrl: '<?= base_url('public/img/marker-icon-2x-red.png') ?>',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
});

var iconYellow = L.icon({
    iconUrl: '<?= base_url('public/img/marker-icon-2x-yellow.png') ?>',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
});

var iconGreen = L.icon({
    iconUrl: '<?= base_url('public/img/marker-icon-2x-green.png') ?>',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
});

<?php foreach ($data as $d) { ?>
    var kerusakan = "<?= $d->survey_klasifikasi_kerusakan; ?>";
    if(kerusakan == "Rusak Berat") {
        var icon = iconRed;
    } else if(kerusakan == "Rusak Sedang") {
        var icon = iconYellow;
    } else if(kerusakan == "Rusak Ringan") {
        var icon = iconGreen;
    } else {
        var icon = iconBlue;
    }
    L.marker([<?= $d->latitude; ?>, <?= $d->longitude; ?>], {icon: icon}).addTo(map).on('click', function(e) {
        showData(<?= $d->npsn; ?>);
    });
<?php } ?>

function showData(npsn) {
    $.ajax({
        url: "<?= base_url() ?>Dashboard/getDetail",
        type: "post",
        data: {
            npsn: npsn,
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
            $("#dtl_npsn").val(data['nspn'][0]['npsn']);
            $("#dtl_nama").val(data['nspn'][0]['nama']);
            $("#dtl_jenis").val(data['nspn'][0]['jenis']);
            $("#dtl_nsm").val(data['nspn'][0]['nsm']);
            $("#dtl_kabupaten").val(data['nspn'][0]['kabupaten']);
            $("#dtl_kecamatan").val(data['nspn'][0]['kecamatan']);
            $("#dtl_latitude").val(data['nspn'][0]['latitude']);
            $("#dtl_longitude").val(data['nspn'][0]['longitude']);
            $("#dtl_periode").val(data['survey'][0]['periode']);
            $("#dtl_emis_jumlah_siswa").val(data['survey'][0]['emis_jumlah_siswa']);
            $("#dtl_survey_jumlah_siswa").val(data['survey'][0]['survey_jumlah_siswa']);
            $("#dtl_survey_tingat_kerusakan").val(data['survey'][0]['survey_tingat_kerusakan']);
            $("#dtl_survey_klasifikasi_kerusakan").val(data['survey'][0]['survey_klasifikasi_kerusakan']);
            $("#dtl_status_lahan").val(data['survey'][0]['status_lahan']);
            $("#dtl_status_penanganan").val(data['survey'][0]['status_penanganan']);
            $("#dtl_ekspos_tingkat_kerusakan").val(data['survey'][0]['ekspos_tingkat_kerusakan']);
            $("#dtl_ekspos_klasifikasi_kerusakan").val(data['survey'][0]['ekspos_klasifikasi_kerusakan']);
            $("#dtl_ekspos_status").val(data['survey'][0]['ekspos_status']);

            $('#modalDetail').modal('show');
        },
        error: function(jqXHR, textStatus, errorThrown) {
            Swal.close();
            console.log(textStatus, errorThrown);
        }
    });
}

<?= session()->get("map_script"); ?>

var geojsonFeature = <?php
    $geojson_data = file_get_contents('https://ps.agungj.com/public/geojson/provinsi_riau.json');
    echo $geojson_data;
?>;

L.geoJSON(geojsonFeature, {
    onEachFeature: function (feature, layer) {
        layer.bindTooltip(feature.properties.WADMKK);

        <?php foreach($tingkat_konflik as $tk){ ?>
            if (feature.properties.KDPKAB == "<?= $tk->kode_provinsi . '.' . $tk->kode_kabupaten; ?>") {
                <?php if($tk->tingkat_konflik == "TINGGI"){ ?>
                    layer.setStyle({fillColor :'#ff0000'}); 
                <?php } else if($tk->tingkat_konflik == "SEDANG"){ ?>
                    layer.setStyle({fillColor :'#ffff00'}); 
                <?php } else if($tk->tingkat_konflik == "RENDAH"){ ?>
                    layer.setStyle({fillColor :'#00ff00'}); 
                <?php } ?>
            }
        <?php } ?>

        // layer.on('click', function (e) {
        //     map.fitBounds(e.target.getBounds());
        // });
    }
}).addTo(map);

map.on('moveend', function() {
    var center = map.getCenter();
    var zoom = map.getZoom();
    localStorage.setItem('leafletMapCenterLat', center.lat);
    localStorage.setItem('leafletMapCenterLng', center.lng);
    localStorage.setItem('leafletMapZoom', zoom);
});

</script>