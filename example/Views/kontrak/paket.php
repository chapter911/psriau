<div class="card">
    <div class="card-header">
        <h2 class="card-title">Daftar Paket</h2>
        <div class="float-right">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-buat-kontrak">Buat Kontrak</button>
            <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modal-lg">Import Excel</button>
        </div>
    </div>
    <div class="card-body">
        <table id="table" class="table table-bordered table-striped w-100 nowrap">
            <thead>
                <tr style="white-space: nowrap;">
                    <th class="text-center">#</th>
                    <th class="text-center">PAKET</th>
                    <th class="text-center">SYARAT UMUM</th>
                    <th class="text-center">ACTION</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($data as $d) { ?>
                    <tr>
                        <td><?= $i++; ?></td>
                        <td><?= $d->nama_paket; ?></td>
                        <td><button type="button" class="btn btn-warning btn-block" onclick="getJabatan(<?= $d->id;?>)">UPDATE</button></td>
                        <td><a href="<?= base_url() ?>C_Kontrak/KI/<?= $d->id ; ?>" class="btn btn-success btn-block">DETAIL</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modal-buat-kontrak">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Buat Kontrak KI Baru</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= base_url('C_Kontrak/simpan') ?>" method="post">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nomor_kontrak">Nomor Kontrak</label>
                                <input type="text" class="form-control" id="nomor_kontrak" name="nomor_kontrak" required>
                            </div>
                            <div class="form-group">
                                <label for="tanggal_kontrak">Tanggal Kontrak</label>
                                <input type="date" class="form-control" id="tanggal_kontrak" name="tanggal_kontrak" required>
                            </div>
                            <div class="form-group">
                                <label for="paket">Paket</label>
                                <input type="text" class="form-control" id="paket" name="paket" required>
                            </div>
                            <div class="form-group">
                                <label for="kode_personil">Kode Personil</label>
                                <input type="text" class="form-control" id="kode_personil" name="kode_personil" required>
                            </div>
                            <div class="form-group">
                                <label for="nama">Nama</label>
                                <input type="text" class="form-control" id="nama" name="nama" required>
                            </div>
                            <div class="form-group">
                                <label for="nik">NIK</label>
                                <input type="text" class="form-control" id="nik" name="nik" required>
                            </div>
                            <div class="form-group">
                                <label for="npwp">NPWP</label>
                                <input type="text" class="form-control" id="npwp" name="npwp">
                            </div>
                            <div class="form-group">
                                <label for="jabatan">Jabatan</label>
                                <input type="text" class="form-control" id="jabatan" name="jabatan" required>
                            </div>
                            <div class="form-group">
                                <label for="harga_kontrak">Harga Kontrak</label>
                                <input type="number" class="form-control" id="harga_kontrak" name="harga_kontrak" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nilai_kontrak">Nilai Kontrak</label>
                                <input type="number" class="form-control" id="nilai_kontrak" name="nilai_kontrak" required>
                            </div>
                            <div class="form-group">
                                <label for="waktu_pelaksanaan">Waktu Pelaksanaan</label>
                                <input type="text" class="form-control" id="waktu_pelaksanaan" name="waktu_pelaksanaan" required>
                            </div>
                            <div class="form-group">
                                <label for="nomor_dipa">Nomor DIPA</label>
                                <input type="text" class="form-control" id="nomor_dipa" name="nomor_dipa">
                            </div>
                            <div class="form-group">
                                <label for="tanggal_dipa">Tanggal DIPA</label>
                                <input type="date" class="form-control" id="tanggal_dipa" name="tanggal_dipa">
                            </div>
                            <div class="form-group">
                                <label for="mata_anggaran">Mata Anggaran</label>
                                <input type="text" class="form-control" id="mata_anggaran" name="mata_anggaran">
                            </div>
                            <div class="form-group">
                                <label for="nomor_surat_undangan_pengadaan">Nomor Surat Undangan Pengadaan</label>
                                <input type="text" class="form-control" id="nomor_surat_undangan_pengadaan" name="nomor_surat_undangan_pengadaan">
                            </div>
                            <div class="form-group">
                                <label for="tanggal_surat_undangan_pengadaan">Tanggal Surat Undangan Pengadaan</label>
                                <input type="date" class="form-control" id="tanggal_surat_undangan_pengadaan" name="tanggal_surat_undangan_pengadaan">
                            </div>
                            <div class="form-group">
                                <label for="nomor_surat_berita_acara_pengadaan">Nomor Berita Acara Pengadaan</label>
                                <input type="text" class="form-control" id="nomor_surat_berita_acara_pengadaan" name="nomor_surat_berita_acara_pengadaan">
                            </div>
                            <div class="form-group">
                                <label for="tanggal_surat_berita_acara_pengadaan">Tanggal Berita Acara Pengadaan</label>
                                <input type="date" class="form-control" id="tanggal_surat_berita_acara_pengadaan" name="tanggal_surat_berita_acara_pengadaan">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-lg">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Import Data Kontrak KI dari Excel</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= base_url('C_Kontrak/import_excel_ki') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="excel_file">Pilih File Excel</label>
                        <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xls, .xlsx" required>
                        <small class="form-text text-muted">Unduh template excel <a href="https://docs.google.com/spreadsheets/d/11XzVqa5C8ONLPV9sHgRUJ_pVo7ba-x5Do9ZnAmkzcpo/edit?usp=sharing" target="_blank">disini</a></small>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-syarat-umum">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Update Syarat Umum</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-syarat-umum" action="<?= base_url('C_Kontrak/update_syarat_paket') ?>" method="post">
                <div class="modal-body">
                    <input type="hidden" id="paket_id" name="paket_id">
                    <div class="form-group">
                        <label for="jabatan_filter">Jabatan</label>
                        <select class="form-control" id="jabatan_filter" name="jabatan_filter" onchange="updateSyarat()" required>
                            <option value="">-- Pilih Jabatan --</option>
                        </select>
                    </div>

                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" id="syaratUmumTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="laporan-tab" data-toggle="tab" href="#laporan" role="tab" aria-controls="laporan" aria-selected="true">Laporan Hasil Pekerjaan</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="hasil-tab" data-toggle="tab" href="#hasil" role="tab" aria-controls="hasil" aria-selected="false">Produk Hasil Pekerjaan</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tugas-tab" data-toggle="tab" href="#tugas" role="tab" aria-controls="tugas" aria-selected="false">Tugas dan Tanggung Jawab</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="contoh-tab" data-toggle="tab" href="#contoh" role="tab" aria-controls="contoh" aria-selected="false">Contoh Format</a>
                        </li>
                    </ul>
                    <!-- Tab panes -->
                    <div class="tab-content" id="syaratUmumTabContent">
                        <div class="tab-pane fade show active" id="laporan" role="tabpanel" aria-labelledby="laporan-tab">
                            <div class="form-group">
                                <textarea id="laporan-editor" class="form-control mt-3" name="laporan" rows="10" placeholder="Laporan Hasil Pekerjaan"></textarea>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="hasil" role="tabpanel" aria-labelledby="hasil-tab">
                            <div class="form-group">
                                <textarea id="hasil-editor" class="form-control mt-3" name="hasil" rows="10" placeholder="Produk Hasil Pekerjaan"></textarea>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tugas" role="tabpanel" aria-labelledby="tugas-tab">
                            <div class="form-group">
                                <textarea id="tugas-editor" class="form-control mt-3" name="tugas_tanggung_jawab" rows="10" placeholder="Tugas dan Tanggung Jawab Penyedia Jasa"></textarea>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="contoh" role="tabpanel" aria-labelledby="contoh-tab">
                            <div class="form-group">
                                <textarea id="contoh-editor" class="form-control mt-3" rows="10" placeholder="Contoh Format Penulisan" readonly>
                                    Harap mengcopy text dibawah ini dan sesuaikan formatnya<br><br>
                                    <strong>contoh judul</strong><br>
                                    sub judul
                                    <ol class="sub-list">
                                        <li>Item 1</li>
                                        <li>
                                            Item 2
                                            <ol class="sub-sub-list">
                                                <li>Sub-item 1</li>
                                                <li>Sub-item 2</li>
                                            </ol>
                                        </li>
                                        <li>Item 3</li>
                                    </ol><br><br>
                                    contoh judul 2<br>
                                    sub judul
                                    <ol class="sub-list">
                                        <li>Item 1</li>
                                        <li>
                                            Item 2
                                            <ol class="sub-sub-list">
                                                <li>Sub-item 1</li>
                                                <li>Sub-item 2</li>
                                            </ol>
                                        </li>
                                        <li>Item 3</li>
                                    </ol>
                                </textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
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

    $('#laporan-editor, #hasil-editor, #tugas-editor, #contoh-editor').summernote({
        height: 200,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link']],
            ['view', ['fullscreen', 'codeview']]
        ]
    });
});

function resetForm() {
    $('#id').val('0');
    $('#form')[0].reset();
}

function getJabatan($id) {
    $('#paket_id').val($id);
    $.ajax({
        url: '<?= base_url('C_Kontrak/get_jabatan_syarat_umum'); ?>' ,
        type: 'GET',
        dataType: 'JSON',
        before: function(){
            Swal.fire({
                title: 'Loading...',
                text: 'Mengambil data syarat',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        },
        success: function(data) {
            if (data) {
                $('#jabatan_filter').empty();
                $('#jabatan_filter').append('<option value="">-- Pilih Jabatan --</option>');
                $.each(data.jabatan, function(key, value) {
                    $('#jabatan_filter').append('<option value="' + value.jabatan + '">' + value.jabatan + '</option>');
                });

            } 
            Swal.close();
            $('#modal-syarat-umum').modal('show');
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log('Error getting data: ' + textStatus, errorThrown);
        }
    });
}

function updateSyarat() {
    $id = $('#paket_id').val();
    $jabatan = $('#jabatan_filter').val();
    
    $.ajax({
        url: '<?= base_url('C_Kontrak/get_syarat_umum_by_paket_id'); ?>',
        type: 'POST',
        data: {
            id: $id,
            jabatan: $jabatan
        },
        dataType: 'JSON',
        before: function(){
            Swal.fire({
                title: 'Loading...',
                text: 'Mengambil data syarat',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        },
        success: function(data) {
            if (data.paket != null) {
                $('#laporan-editor').summernote('code', data.paket.laporan);
                $('#hasil-editor').summernote('code', data.paket.hasil);
                $('#tugas-editor').summernote('code', data.paket.tugas_tanggung_jawab);
            } else {
                $('#laporan-editor').summernote('code', '');
                $('#hasil-editor').summernote('code', '');
                $('#tugas-editor').summernote('code', '');
            }
            Swal.close();
            $('#syaratUmumTab a[href="#laporan"]').tab('show');
            $('#modal-syarat-umum').modal('show');
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log('Error getting data: ' + textStatus, errorThrown);
            $('#laporan-editor').summernote('code', '');
            $('#hasil-editor').summernote('code', '');
            $('#tugas-editor').summernote('code', '');
        }
    });
}
</script>