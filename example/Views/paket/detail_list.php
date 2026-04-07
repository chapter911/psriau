<div class="row">
    <div class="col-5 col-sm-2">
        <div class="nav flex-column nav-tabs h-100" id="vert-tabs-tab" role="tablist" aria-orientation="vertical">
            <a class="nav-link active" id="vert-tabs-home-tab" data-toggle="pill" href="#vert-tabs-home" role="tab"
                aria-controls="vert-tabs-home" aria-selected="true" onclick="setTab('vert-tabs-home')">DETAIL</a>
            <a class="nav-link" id="vert-tabs-add-on-tab" data-toggle="pill" href="#vert-tabs-add-on" role="tab"
                aria-controls="vert-tabs-add-on" aria-selected="false" onclick="setTab('vert-tabs-add-on')">ADD ON</a>
            <?php foreach ($detail_head as $d) {?>
            <a class="nav-link" id="vert-tabs-<?php echo $d->id;?>-tab" data-toggle="pill"
                href="#vert-tabs-<?php echo $d->id;?>" role="tab" aria-controls="vert-tabs-profile"
                aria-selected="false"
                onclick="setTab('vert-tabs-<?php echo $d->id;?>-tab')"><?php echo $d->tahapan;?></a>
            <?php }?>
        </div>
    </div>
    <div class="col-7 col-sm-10">
        <div class="tab-content" id="vert-tabs-tabContent">
            <div class="tab-pane text-left fade show active" id="vert-tabs-home" role="tabpanel"
                aria-labelledby="vert-tabs-home-tab">
                <form id="form" action="<?php echo base_url();?>C_Paket/savePaketHDR" method="POST"
                    class="form-horizontal">
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Nomor Kontrak</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="nomor_kontrak" placeholder="Nomor Kontrak"
                                value="<?php echo $header->nomor_kontrak;?>" readonly required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Nilai Kontrak</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="nilai_kontrak" palceholder="Nilai Kontrak"
                                value="Rp. <?php echo number_format($header->nilai_kontrak, 0, ",", ".");?>"
                                oninput="formatRupiah(this)" required>
                            <script>
                            function formatRupiah(input) {
                                let value = input.value.replace(/[^,\d]/g, '').toString();
                                let split = value.split(',');
                                let sisa = split[0].length % 3;
                                let rupiah = split[0].substr(0, sisa);
                                let ribuan = split[0].substr(sisa).match(/\d{3}/g);

                                if (ribuan) {
                                    rupiah += (sisa ? '.' : '') + ribuan.join('.');
                                }

                                rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
                                input.value = rupiah ? 'Rp. ' + rupiah : '';
                            }
                            </script>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Nama Paket</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="nama_paket"
                                value="<?php echo $header->nama_paket;?>" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">PPK</label>
                        <div class="col-sm-8">
                            <select class="form-control" name="nip_ppk" required>
                                <option value="">-- Pilih Petugas PPK --</option>
                                <?php foreach ($ppk as $p) {?>
                                <option value='<?php echo $p->nip?>'
                                    <?php echo $header->nip_ppk == $p->nip ? "selected" : "";?>>
                                    <?php echo $p->nip . ' - ' . strtoupper($p->nama);?></option>
                                <?php }?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Masa Pelaksanaan</label>
                        <div class="col-sm">
                            <input type="date" class="form-control" name="masa_pelaksanaan_awal"
                                value="<?php echo $header->masa_pelaksanaan_awal;?>" required>
                        </div>
                        <div class="col-sm-1 text-center">s/d</div>
                        <div class="col-sm">
                            <input type="date" class="form-control" name="masa_pelaksanaan_akhir"
                                min="<?php echo $header->masa_pelaksanaan_awal;?>"
                                value="<?php echo $header->masa_pelaksanaan_akhir;?>" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Tahun Anggaran</label>
                        <div class="col-sm-8">
                            <select class="form-control" name="tahun_anggaran" required>
                                <option value="">-- Pilih Tahun Anggaran --</option>
                                <?php
                            for ($year = date('Y'); $year >= 2000; $year--) {?>
                                <option value='<?php echo $year?>'
                                    <?php echo $header->tahun_anggaran == $year ? "selected" : "";?>><?php echo $year?>
                                </option>
                                <?php }?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Penyedia</label>
                        <div class="col-sm-8">
                            <select class="form-control" name="penyedia_id" required>
                                <option value="">-- Pilih Penyedia --</option>
                                <?php foreach ($penyedia as $d) {?>
                                <option value="<?php echo esc($d->id);?>"
                                    <?php echo $header->penyedia_id == $d->id ? "selected" : "";?>>
                                    <?php echo esc($d->penyedia);?></option>
                                <?php }?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Metode Pemilihan</label>
                        <div class="col-sm-8">
                            <select class="form-control" name="metode_pemilihan_id" required>
                                <option value="">-- Pilih Metode --</option>
                                <?php foreach ($pemilihan as $d) {?>
                                <option value="<?php echo esc($d->id);?>"
                                    <?php echo $header->metode_pemilihan_id == $d->id ? "selected" : "";?>>
                                    <?php echo esc($d->pemilihan);?></option>
                                <?php }?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
            <div class="tab-pane fade" id="vert-tabs-add-on" role="tabpanel"
                aria-labelledby="vert-tabs-add-on-tab">
                <form id="form" action="<?php echo base_url();?>C_Paket/saveAddOn" method="POST"
                    class="form-horizontal">
                    <input type="hidden" class="form-control" name="nomor_kontrak" value="<?php echo $header->nomor_kontrak;?>" readonly required>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Add On 1</label>
                        <div class="col-sm-3">
                            <input type="text" class="form-control" name="add_on_1" <?= count($add_on) == 0 ? "" : "value='Rp. " . number_format($add_on[0]->add_on, 0, ",", ".") . "'" ?> oninput="formatRupiah(this)" placeholder="Add On 1">
                        </div>
                        <div class="col-sm-3">
                            <input type="date" class="form-control" name="tanggal_add_on_1" <?= count($add_on) == 0 ? "" : "value='" . $add_on[0]->tanggal . "'" ?> placeholder="Tanggal Add On 1">
                        </div>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="keterangan_add_on_1" <?= count($add_on) == 0 ? "" : "value='" . $add_on[0]->keterangan . "'" ?> placeholder="Keterangan Add On 1">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Add On 2</label>
                        <div class="col-sm-3">
                            <input type="text" class="form-control" name="add_on_2" <?= count($add_on) == 0 ? "" : "value='Rp. " . number_format($add_on[1]->add_on, 0, ",", ".") . "'" ?> oninput="formatRupiah(this)" placeholder="Add On 2">
                        </div>
                        <div class="col-sm-3">
                            <input type="date" class="form-control" name="tanggal_add_on_2" <?= count($add_on) == 0 ? "" : "value='" . $add_on[1]->tanggal . "'" ?> placeholder="Tanggal Add On 2">
                        </div>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="keterangan_add_on_2" <?= count($add_on) == 0 ? "" : "value='" . $add_on[1]->keterangan . "'" ?> placeholder="Keterangan Add On 2">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Add On 3</label>
                        <div class="col-sm-3">
                            <input type="text" class="form-control" name="add_on_3" <?= count($add_on) == 0 ? "" : "value='Rp. " . number_format($add_on[2]->add_on, 0, ",", ".") . "'" ?> oninput="formatRupiah(this)" placeholder="Add On 3">
                        </div>
                        <div class="col-sm-3">
                            <input type="date" class="form-control" name="tanggal_add_on_3" <?= count($add_on) == 0 ? "" : "value='" . $add_on[2]->tanggal . "'" ?> placeholder="Tanggal Add On 3">
                        </div>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="keterangan_add_on_3" <?= count($add_on) == 0 ? "" : "value='" . $add_on[2]->keterangan . "'" ?> placeholder="Keterangan Add On 3">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Add On 4</label>
                        <div class="col-sm-3">
                            <input type="text" class="form-control" name="add_on_4" <?= count($add_on) == 0 ? "" : "value='Rp. " . number_format($add_on[3]->add_on, 0, ",", ".") . "'" ?> oninput="formatRupiah(this)" placeholder="Add On 4">
                        </div>
                        <div class="col-sm-3">
                            <input type="date" class="form-control" name="tanggal_add_on_4" <?= count($add_on) == 0 ? "" : "value='" . $add_on[3]->tanggal . "'" ?> placeholder="Tanggal Add On 4">
                        </div>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="keterangan_add_on_4" <?= count($add_on) == 0 ? "" : "value='" . $add_on[3]->keterangan . "'" ?> placeholder="Keterangan Add On 4">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Add On 5</label>
                        <div class="col-sm-3">
                            <input type="text" class="form-control" name="add_on_5" <?= count($add_on) == 0 ? "" : "value='Rp. " . number_format($add_on[4]->add_on, 0, ",", ".") . "'" ?> oninput="formatRupiah(this)" placeholder="Add On 5">
                        </div>
                        <div class="col-sm-3">
                            <input type="date" class="form-control" name="tanggal_add_on_5" <?= count($add_on) == 0 ? "" : "value='" . $add_on[4]->tanggal . "'" ?> placeholder="Tanggal Add On 5">
                        </div>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="keterangan_add_on_5" <?= count($add_on) == 0 ? "" : "value='" . $add_on[4]->keterangan . "'" ?> placeholder="Keterangan Add On 5">
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
            <?php foreach ($detail_head as $d) {?>
            <div class="tab-pane fade" id="vert-tabs-<?php echo $d->id;?>" role="tabpanel"
                aria-labelledby="vert-tabs-<?php echo $d->id;?>-tab">
                <form id="form" action="<?php echo base_url();?>C_Paket/savePaketDTL" method="POST"
                    class="form-horizontal">
                    <input type="hidden" name="nomor_kontrak" value="<?php echo $header->nomor_kontrak;?>" />
                    <table id="table-detail" class="table-detail table-bordered table-striped w-100 nowrap">
                        <thead class="sticky-top bg-light">
                            <tr>
                                <th style="width: 4%;">#</th>
                                <th style="width: 20%;">Tahapan</th>
                                <th style="width: 20%;">Bentuk Dokumen</th>
                                <th style="width: 22%;">Referensi</th>
                                <th style="width: 10%;">Kelengkapan<br />Dokumen</th>
                                <th style="width: 10%;">Verifikasi<br />Dit KI</th>
                                <th style="width: 14%;">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 0;foreach ($section as $s) {
                                if ($d->section == $s->section) {?>
                            <input type="hidden" name="section[]" value="<?php echo $s->section;?>" />
                            <input type="hidden" name="no[]" value="<?php echo $s->no;?>" />
                            <input type="hidden" name="sub_lv1[]" value="<?php echo $s->sub_lv1;?>" />
                            <input type="hidden" name="sub_lv2[]" value="<?php echo $s->sub_lv2;?>" />
                            <input type="hidden" name="sub_lv3[]" value="<?php echo $s->sub_lv3;?>" />
                            <input type="hidden" name="is_checkbox[]" value="<?php echo $s->is_checkbox;?>" />
                            <tr 
                                <?php echo $s->is_checkbox == 0 || $s->level == 2 || $s->level == 3 ? "style='background-color: #d4d4d4; color: black; font-weight: bold;'" : ""?>>
                                <td>
                                    <?php if (! empty($s->sub_lv3)) {?>
                                    &nbsp;&nbsp;&nbsp;&nbsp; <?php echo $s->sub_lv3;?>
                                    <?php } else if (! empty($s->sub_lv2)) {?>
                                    &nbsp;&nbsp;&nbsp; <?php echo $s->sub_lv2;?>
                                    <?php } else if (! empty($s->sub_lv1)) {?>
                                    &nbsp;&nbsp; <?php echo $s->sub_lv1;?>
                                    <?php } else if (! empty($s->no)) {?>
                                    &nbsp; <?php echo $s->no;?>
                                    <?php } else if (! empty($s->section)) {?>
                                    <b><?php echo $s->section;?></b>
                                    <?php }?>
                                </td>
                                <?php if ($s->is_checkbox == 0) {?>
                                <td <?php echo $s->is_checkbox == 0 ? "colspan='3'" : ""?>><?php echo $s->tahapan;?>
                                </td>
                                <?php } else { ?>
                                <td><?php echo $s->tahapan;?></td>
                                <td style="white-space: pre-wrap; font-family: inherit;"><?php echo $s->dokumen;?></td>
                                <td><?php echo $s->referensi;?></td>
                                <?php } ?>
                                <td class="text-center">
                                    <?php if ($s->is_checkbox == 1) { ?>
                                    <select class='form-control' name='kelengkapan_<?php echo $i;?>[]'>
                                        <option value='-' <?php echo $s->kelengkapan == '-' ? "selected" : ""?>>-
                                        </option>
                                        <option value='Ada' <?php echo $s->kelengkapan == 'Ada' ? "selected" : ""?>>Ada
                                        </option>
                                        <option value='Tidak' <?php echo $s->kelengkapan == 'Tidak' ? "selected" : ""?>>
                                            Tidak</option>
                                    </select>
                                    <?php }?>
                                </td>
                                <td class="text-center">
                                    <?php if ($s->is_checkbox == 1) { ?>
                                    <select class='form-control' name='referensi_ki_<?php echo $i;?>[]'>
                                        <option value='-' <?php echo $s->referensi_ki == '-' ? "selected" : ""?>>-
                                        </option>
                                        <option value='Sesuai'
                                            <?php echo $s->referensi_ki == 'Sesuai' ? "selected" : ""?>>Sesuai</option>
                                        <option value='Tidak Sesuai'
                                            <?php echo $s->referensi_ki == 'Tidak Sesuai' ? "selected" : ""?>>Tidak
                                            Sesuai</option>
                                    </select>
                                    <?php }?>
                                </td>
                                <td class="text-center">
                                    <?php if ($s->is_checkbox == 1) {?>
                                    <textarea class='form-control' name='keterangan_<?php echo $i;?>[]'
                                        rows='2'><?php echo $s->keterangan;?></textarea>
                                    <?php }?>
                                </td>
                            </tr>
                            <?php }
                                if($d->section == $s->section){
                                    $i++;
                                } else {
                                    $i = 0;
                                }
                            }?>
                        </tbody>
                    </table>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
            <?php }?>
        </div>
    </div>
</div>

<script>
let selected_tab = 'vert-tabs-home-tab';

function setTab(tab_id) {
    selected_tab = tab_id;
}

$('form').submit(function(e) {
    e.preventDefault();
    var form = $(this);
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "simpan data ini?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, simpan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: form.attr('method'),
                url: form.attr('action'),
                data: form.serialize(),
                success: function(response) {
                    Swal.fire(
                        'Tersimpan!',
                        'Data Anda telah disimpan.',
                        'success'
                    ).then(() => {
                        var nomor_kontrak = form.find(
                            'input[name="nomor_kontrak"]').val();
                        showDetail(nomor_kontrak);
                        $('#' + selected_tab).tab('show');
                    });
                },
                error: function(xhr, status, error) {
                    Swal.fire(
                        'Gagal!',
                        'Terjadi kesalahan saat menyimpan data.',
                        'error'
                    );
                }
            });
        }
    })
});
</script>