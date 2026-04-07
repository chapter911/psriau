<div class="card">
    <div class="card-header">
        <h2 class="card-title">Daftar Kontrak KI</h2>
    </div>
    <div class="card-body">
        <table id="table" class="table table-bordered table-striped w-100 nowrap">
            <thead>
                <tr style="white-space: nowrap;">
                    <th class="text-center">#</th>
                    <th class="text-center">NOMOR KONTRAK</th>
                    <th class="text-center">TANGGAL KONTRAK</th>
                    <th class="text-center">PAKET</th>
                    <th class="text-center">KODE PERSONIL</th>
                    <th class="text-center">NAMA</th>
                    <th class="text-center">ALAMAT</th>
                    <th class="text-center">NIK</th>
                    <th class="text-center">NPWP</th>
                    <th class="text-center">JABATAN</th>
                    <th class="text-center">DURASI PELAKSANAAN</th>
                    <th class="text-center">NOMOR DIPA</th>
                    <th class="text-center">TANGGAL DIPA</th>
                    <th class="text-center">MATA ANGGARAN</th>
                    <th class="text-center">NOMOR SURAT UNDANGAN PENGADAAN</th>
                    <th class="text-center">TANGGAL SURAT UNDANGAN PENGADAAN</th>
                    <th class="text-center">NOMOR SURAT BERITA ACARA PENGADAAN</th>
                    <th class="text-center">TANGGAL SURAT BERITA ACARA PENGADAAN</th>
                    <th class="text-center">NOMOR SURAT PENAWARAN</th>
                    <th class="text-center">TANGGAL SURAT PENAWARAN</th>
                    <th class="text-center">NOMOR UNDANGAN</th>
                    <th class="text-center">TOTAL PENAWARAN</th>
                    <th class="text-center">TANGGAL AWAL</th>
                    <th class="text-center">TANGGAL AKHIR</th>
                    <th class="text-center">TAHUN ANGGARAN</th>
                    <th class="text-center">NO SPPBJ</th>
                    <th class="text-center">TANGGAL SPPBJ</th>
                    <th class="text-center">PEJABAT PPK</th>
                    <th class="text-center">NIP PEJABAT PPK</th>
                    <th class="text-center">KEDUDUKAN PEJABAT PPK</th>
                    <th class="text-center">NOMOR SURAT KEPUTUSAN MENTERI</th>
                    <th class="text-center">TANGGAL SURAT KEPUTUSAN MENTERI</th>
                    <th class="text-center">NOMOR PERUBAHAN KEPUTUSAN MENTERI</th>
                    <th class="text-center">BANK NOMOR REKENING</th>
                    <th class="text-center">BANK NAMA</th>
                    <th class="text-center">BANK ATAS NAMA</th>
                    <th class="text-center">BANK PEMBAYARAN</th>
                    <th class="text-center">KATEGORI</th>
                    <th class="text-center">ACTION</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($data as $d) { ?>
                    <tr>
                        <td><?= $i++; ?></td>
                        <td><?= $d->nomor_kontrak; ?></td>
                        <td><?= $d->tanggal_kontrak; ?></td>
                        <td><?= $d->nama_paket; ?></td>
                        <td><?= $d->kode_personil; ?></td>
                        <td><?= $d->nama; ?></td>
                        <td><?= $d->alamat; ?></td>
                        <td><?= $d->nik; ?></td>
                        <td><?= $d->npwp; ?></td>
                        <td><?= $d->jabatan; ?></td>
                        <td><?= $d->durasi_pelaksanaan; ?></td>
                        <td><?= $d->nomor_dipa; ?></td>
                        <td><?= $d->tanggal_dipa; ?></td>
                        <td><?= $d->mata_anggaran; ?></td>
                        <td><?= $d->nomor_surat_undangan_pengadaan; ?></td>
                        <td><?= $d->tanggal_surat_undangan_pengadaan; ?></td>
                        <td><?= $d->nomor_surat_berita_acara_pengadaan; ?></td>
                        <td><?= $d->tanggal_surat_berita_acara_pengadaan; ?></td>
                        <td><?= $d->nomor_surat_penawaran; ?></td>
                        <td><?= $d->tanggal_surat_penawaran; ?></td>
                        <td><?= $d->nomor_undangan; ?></td>
                        <td><?= number_format($d->total_penawaran, 0, '.', '.'); ?></td>
                        <td><?= $d->tanggal_awal; ?></td>
                        <td><?= $d->tanggal_akhir; ?></td>
                        <td><?= $d->tahun_anggaran; ?></td>
                        <td><?= $d->no_sppbj; ?></td>
                        <td><?= $d->tanggal_sppbj; ?></td>
                        <td><?= $d->pejabat_ppk; ?></td>
                        <td><?= $d->nip_pejabat_ppk; ?></td>
                        <td><?= $d->kedudukan_pejabat_ppk; ?></td>
                        <td><?= $d->nomor_surat_keputusan_menteri; ?></td>
                        <td><?= $d->tanggal_surat_keputusan_menteri; ?></td>
                        <td><?= $d->nomor_perubahan_keputusan_menteri; ?></td>
                        <td><?= $d->bank_nomor_rekening; ?></td>
                        <td><?= $d->bank_nama; ?></td>
                        <td><?= $d->bank_atas_nama; ?></td>
                        <td><?= $d->bank_pembayaran; ?></td>
                        <td><?= $d->kategori; ?></td>
                        <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn-success" data-toggle="dropdown">Export</button>
                                <button type="button" class="btn btn-success dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                    <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu" role="menu">
                                    <a href="<?= base_url() ?>C_Kontrak/Export/Penawaran/<?= $d->id;?>" class="dropdown-item" target="_blank">PENAWARAN</a>
                                    <a href="<?= base_url() ?>C_Kontrak/Export/PaktaIntegritas/<?= $d->id;?>" class="dropdown-item" target="_blank">PAKTA INTEGRITAS</a>
                                    <a href="<?= base_url() ?>C_Kontrak/Export/FormulirKualifikasi/<?= $d->id;?>" class="dropdown-item" target="_blank">KUALIFIKASI</a>
                                    <a href="<?= base_url() ?>C_Kontrak/Export/BOQ/<?= $d->id;?>" class="dropdown-item" target="_blank">BOQ</a>
                                    <a href="<?= base_url() ?>C_Kontrak/Export/Kesediaan/<?= $d->id;?>" class="dropdown-item" target="_blank">KESEDIAAN</a>
                                    <a href="<?= base_url() ?>C_Kontrak/Export/SPBBJ/<?= $d->id;?>" class="dropdown-item" target="_blank">SPBBJ</a>
                                    <a href="<?= base_url() ?>C_Kontrak/Export/Evaluasi/<?= $d->id;?>" class="dropdown-item" target="_blank">Evaluasi</a>
                                    <a href="<?= base_url() ?>C_Kontrak/Export/BAPHP/<?= $d->id;?>" class="dropdown-item" target="_blank">BAPHP</a>
                                    <a href="<?= base_url() ?>C_Kontrak/Export/BAST/<?= $d->id;?>" class="dropdown-item" target="_blank">BAST</a>
                                    <div class="dropdown-divider"></div>
                                    <a href="<?= base_url() ?>C_Kontrak/Export/SPMK/<?= $d->id;?>" class="dropdown-item" target="_blank">SPMK</a>
                                    <a href="<?= base_url() ?>C_Kontrak/Export/SPK/<?= $d->id;?>" class="dropdown-item" target="_blank">SPK</a>
                                </div>
                            </div>
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
        "autoWidth": false,
        "buttons": ["copy", "csv", "excel", "pdf", "print"],
        "order": [0, "asc"],
        "scrollX": true,
    }).buttons().container().appendTo('#table_wrapper .col-md-6:eq(0)');
});
</script>