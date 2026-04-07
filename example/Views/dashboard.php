<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col">
                <label>Tipe Map</label>
                <select class="form-control" id="map_type" name="map_type" onchange="getMap()">
                    <?php foreach ($map_type as $k) { ?>
                        <option value="<?= esc($k->id); ?>" <?= session()->get('map_id') == $k->id ? 'selected' : ''; ?>><?= esc($k->map_name); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col">
                <label>NPSN</label>
                <input type="number" class="form-control" id="npsn" name="npsn">
            </div>
            <div class="col">
                <label>Nama Madrasah</label>
                <input type="text" class="form-control" id="nama" name="nama">
            </div>
            <div class="col">
                <label>Kabupaten</label>
                <select class="form-control" id="kabupaten" name="kabupaten" onchange="getMap()">
                    <option value="*">Semua Kabupaten</option>
                    <?php foreach ($kabupaten as $k) { ?>
                        <option value="<?= $k->kabupaten; ?>"><?= $k->kabupaten; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <label>Kecamatan</label>
                <select class="form-control" id="kecamatan" name="kecamatan" onchange="getMap()">
                    <option value="*">Semua Kecamatan</option>
                    <?php foreach ($kecamatan as $k) { ?>
                        <option value="<?= $k->kecamatan; ?>"><?= $k->kecamatan; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col">
                <label>Klasifikasi Kerusakan</label>
                <select class="form-control" id="klasifikasi" name="klasifikasi" onchange="getMap()">
                    <option value="*">Semua Klasifikasi</option>
                    <?php foreach ($klasifikasi as $k) { ?>
                        <option value="<?= $k->survey_klasifikasi_kerusakan; ?>"><?= $k->survey_klasifikasi_kerusakan; ?></option>
                    <?php } ?>
                    <option value="non_klasifikasi">Belum Klasifikasi</option>
                </select>
            </div>
            <div class="col">
                <label>Total Sekolah</label>
                <input type="text" class="form-control" id="total_data" style="text-align: end" readonly>
            </div>
            <div class="col">
                <label>&nbsp;</label>
                <button class="btn btn-primary btn-block" onclick="getMap()"><i class="fa fa-search"></i> Cari</button>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <label>&nbsp;</label>
                <div id="map_container" style="width: 100%; height: 55vh;"></div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        getMap();
    });

    function getMap(){
        $.ajax({
        url: "<?= base_url() ?>Dashboard/getMap",
        type: "post",
        data: {
            map_type: $('#map_type').val(),
            npsn: $('#npsn').val(),
            nama: $('#nama').val(),
            kabupaten: $('#kabupaten').val(),
            kecamatan: $('#kecamatan').val(),
            klasifikasi: $('#klasifikasi').val()
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
            $('#map_container').html(response);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            Swal.close();
            console.log(textStatus, errorThrown);
        }
    });
    }
</script>