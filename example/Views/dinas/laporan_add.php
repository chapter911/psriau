<div class="card">
    <div class="card-header">
        <h2 class="card-title">Buat Laporan Dinas</h2>
    </div>
    <div class="card-body">
        <form id="form">
            <div class="form-group">
                <label for="nomor_surat">Nomor Surat Tugas</label>
                <input type="text" class="form-control" id="nomor_surat" name="nomor_surat" required>
                <div class="form-row">
                    <div class="col">
                        <label for="tanggal_mulai">Periode Perjalanan Dinas - Mulai</label>
                        <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" required>
                    </div>
                    <div class="col">
                        <label for="tanggal_selesai">Periode Perjalanan Dinas - Selesai</label>
                        <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" required>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <label for="tujuan_kota">Kota/Kab. Tujuan Perjalanan Dinas</label>
                    <div class="row">
                        <div class="col-md-6">
                            <select class="form-control select2" style="width: 100%;" id="provinsi" name="provinsi" data-placeholder="Pilih Provinsi" required>
                                <option value="" disabled selected>Pilih Provinsi</option>
                                <?php foreach ($provinsi as $p): ?>
                                    <option value="<?= $p->kode_provinsi; ?>"><?= $p->nama_provinsi; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="tujuan_kabupaten" name="tujuan_kabupaten" placeholder="Contoh: Bandung" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="sarana_transportasi">Sarana Transportasi</label>
                    <select class="form-control select2 w-100" id="sarana_transportasi" name="sarana_transportasi[]" multiple="multiple" data-placeholder="Pilih Sarana Transportasi">
                        <option value="Darat">Darat (Mobil)</option>
                        <option value="Kereta">Kereta</option>
                        <option value="Laut">Laut (Kapal)</option>
                        <option value="Udara">Udara (Pesawat)</option>
                        <option value="Kendaraan Dinas">Kendaraan Dinas</option>
                        <option value="Kendaraan Pribadi">Kendaraan Pribadi</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="pelaksana">Pelaksana Perjalanan Dinas</label>
                    <select class="form-control select2" id="nip" name="nip[]" multiple="multiple" data-placeholder="Pilih Pelaksana" required>
                        <?php foreach ($user as $u): ?>
                            <option value="<?= $u->nip; ?>"><?= $u->nama; ?> - <?= $u->jabatan; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="pelaksana">Diketahui Oleh</label>
                    <select class="form-control select2" id="diketahui" name="diketahui[]" multiple="multiple" data-placeholder="Pilih DiKetahui Oleh" required>
                        <?php foreach ($user as $u): ?>
                            <option value="<?= $u->nip; ?>"><?= $u->nama; ?> - <?= $u->jabatan; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="tujuan_perjalanan">Tujuan Perjalanan Dinas</label>
                    <textarea class="form-control" id="tujuan_perjalanan" name="tujuan_perjalanan" rows="3" placeholder="Jelaskan tujuan perjalanan" required></textarea>
                </div>

                <div class="form-group">
                    <label for="sasaran_perjalanan">Sasaran Perjalanan Dinas</label>
                    <textarea class="form-control" id="sasaran_perjalanan" name="sasaran_perjalanan" rows="3" placeholder="Sasaran yang ingin dicapai" required></textarea>
                </div>

                <h3>Laporan Hasil Perjalanan Dinas</h3>

                <div class="form-group">
                    <label for="pelaksanaan_kegiatan">1. Pelaksanaan Kegiatan</label>
                    <textarea id="pelaksanaan_kegiatan" name="pelaksanaan_kegiatan"></textarea>
                    
                </div>

                <div class="form-group">
                    <label for="hasil_pelaksanaan">2. Hasil Pelaksanaan</label>
                    <textarea id="hasil_pelaksanaan" name="hasil_pelaksanaan"></textarea>
                </div>

                <div class="form-group">
                    <label for="tindak_lanjut_kegiatan">3. Tindak Lanjut Kegiatan</label>
                    <textarea id="tindak_lanjut_kegiatan" name="tindak_lanjut_kegiatan"></textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </div>
</div>

<script>
    $(function () {
        $('#pelaksanaan_kegiatan').summernote();
        $('#hasil_pelaksanaan').summernote();
        $('#tindak_lanjut_kegiatan').summernote();
    });
</script>