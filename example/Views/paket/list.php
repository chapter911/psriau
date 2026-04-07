<div class="card">
    <div class="card-header">
        <h2 class="card-title">Daftar Paket</h2>
        <button class="btn btn-success float-right" data-toggle="modal" data-target="#modalAdd" onclick="resetForm()">Buat Paket</button>
    </div>
    <div class="card-body">
        <table id="table" class="table table-bordered table-striped w-100 nowrap">
            <thead>
                <tr style="white-space: nowrap;">
                    <th class="text-center">No</th>
                    <th class="text-center">Nomor Kontrak</th>
                    <th class="text-center">Nilai Kontrak </th>
                    <th class="text-center">Nama Paket</th>
                    <th class="text-center">PPK</th>
                    <th class="text-center">Tahap Pekerjaan</th>
                    <th class="text-center">Tanggal Pemeriksaan</th>
                    <th class="text-center">Kelengkapan (%)</th>
                    <th class="text-center">Masa Pelaksanaan</th>
                    <th class="text-center">Tahun Anggaran</th>
                    <th class="text-center">Penyedia</th>
                    <th class="text-center">Metode Pemilihan</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $i=1; foreach ($data as $d) { ?>
                    <tr>
                        <td><?= $i++; ?></td>
                        <td><button class="btn btn-block btn-warning" onclick="showDetail('<?= $d->nomor_kontrak; ?>');"><?= $d->nomor_kontrak; ?></button></td>
                        <td class="text-right">Rp. <?= number_format($d->nilai_kontrak, 0, ",", "."); ?></td>
                        <td><?= $d->nama_paket; ?></td>
                        <td><?= $d->nip_ppk . '<br/>' .$d->nama_ppk; ?></td>
                        <td>-</td>
                        <td></td>
                        <td>0</td>
                        <td><?= $d->masa_pelaksanaan_awal . ' s/d ' . $d->masa_pelaksanaan_akhir; ?></td>
                        <td><?= $d->tahun_anggaran; ?></td>
                        <td><?= $d->penyedia; ?></td>
                        <td><?= $d->pemilihan; ?></td>
                        <td>
                            <button class="btn btn-success" onclick="exportPaket('<?= $d->nomor_kontrak; ?>')"><i class="fas fa-file-excel"></i></button>
                            <button class="btn btn-danger" onclick="hapusPaket('<?= $d->nomor_kontrak; ?>')"><i class="fas fa-trash-alt"></i></button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalAdd">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Buat Paket</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="form" action="<?= base_url(); ?>C_Paket/savePaketHDR" method="POST" class="form-horizontal">
                <div class="modal-body">
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Nomor Kontrak</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="nomor_kontrak" name="nomor_kontrak" placeholder="Nomor Kontrak" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Nilai Kontrak</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="nilai_kontrak" name="nilai_kontrak" palceholder="Nilai Kontrak" required oninput="formatRupiah(this)">
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
                            <input type="text" class="form-control" id="nama_paket" name="nama_paket" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">PPK</label>
                        <div class="col-sm-8">
                            <select class="form-control" id="nip_ppk" name="nip_ppk" required>
                                <option value="">-- Pilih Petugas PPK --</option>
                                <?php foreach ($ppk as $p) { ?>
                                    <option value='<?= $p->nip ?>'><?= $p->nip . ' - ' . strtoupper($p->nama); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Masa Pelaksanaan</label>
                        <div class="col-sm">
                            <input type="date" class="form-control" id="masa_pelaksanaan_awal" name="masa_pelaksanaan_awal" required>
                        </div>
                        <div class="col-sm-1 text-center">s/d</div>
                        <div class="col-sm">
                            <input type="date" class="form-control" id="masa_pelaksanaan_akhir" name="masa_pelaksanaan_akhir" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Tahun Anggaran</label>
                        <div class="col-sm-8">
                            <select class="form-control" id="tahun_anggaran" name="tahun_anggaran" required>
                                <option value="">-- Pilih Tahun Anggaran --</option>
                                <?php
                                    for ($year = date('Y'); $year >= 2000; $year--) {?>
                                        <option value='<?= $year ?>'><?= $year ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Penyedia</label>
                        <div class="col-sm-8">
                            <select class="form-control" id="penyedia_id" name="penyedia_id" required>
                                <option value="">-- Pilih Penyedia --</option>
                                <?php foreach ($penyedia as $d) { ?>
                                    <option value="<?= esc($d->id); ?>"><?= esc($d->penyedia); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Metode Pemilihan</label>
                        <div class="col-sm-8">
                            <select class="form-control" id="metode_pemilihan_id" name="metode_pemilihan_id" required>
                                <option value="">-- Pilih Metode --</option>
                                <?php foreach ($pemilihan as $d) { ?>
                                    <option value="<?= esc($d->id); ?>"><?= esc($d->pemilihan); ?></option>
                                <?php } ?>
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

<div class="modal fade" id="modalDetailPaket">
    <div class="modal-dialog modal-xl" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Detail Paket</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="detailPaket"></div>
            </div>
        </div>
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
function showDetail(nomor_kontrak) {
    $.ajax({
        type: "POST",
        url: "<?= base_url(); ?>C_Paket/ListDetail",
        data: {
            nomor_kontrak: nomor_kontrak
        },
        beforeSend: function() {
            Swal.fire({
                title: 'Mengambil data...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });
        },
        success: function(response) {
            Swal.close();
            $('#detailPaket').html(response);
            $('#modalDetailPaket').modal('show');
        }
    });
}

function hapusPaket(nomor_kontrak) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data paket ini akan dihapus secara permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "POST",
                url: "<?= base_url(); ?>C_Paket/hapusPaket",
                data: {
                    nomor_kontrak: nomor_kontrak
                },
                success: function(response) {
                    Swal.fire(
                        'Dihapus!',
                        'Data paket telah dihapus.',
                        'success'
                    ).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr, status, error) {
                    Swal.fire(
                        'Gagal!',
                        'Terjadi kesalahan saat menghapus data.',
                        'error'
                    );
                }
            });
        }
    });
}

function exportPaket(nomor_kontrak) {
    // Create a form dynamically
    var form = document.createElement("form");
    form.method = "POST";
    form.action = "<?= base_url(); ?>C_Paket/exportPaket";

    // Create an input field for nomor_kontrak
    var input = document.createElement("input");
    input.type = "hidden";
    input.name = "nomor_kontrak";
    input.value = nomor_kontrak;

    // Append the input to the form
    form.appendChild(input);

    // Append the form to the body and submit it
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>