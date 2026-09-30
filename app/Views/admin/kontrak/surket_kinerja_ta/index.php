<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid px-0">
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap align-items-center justify-content-between">
            <div>
                <h4 class="font-weight-bold text-dark mb-1">
                    <i class="fas fa-file-signature text-primary mr-2"></i>Surat Keterangan Kinerja Tenaga Ahli (SURKET KINERJA TA)
                </h4>
                <small class="text-muted">
                    Pengelolaan dan penerbitan berkas resmi Surat Keterangan / Referensi Kinerja Tenaga Ahli Konsultansi Konstruksi
                </small>
            </div>
            <div class="mt-2 mt-md-0">
                <?php if ($permissions['add'] ?? false): ?>
                    <button type="button" class="btn btn-primary font-weight-bold shadow-sm px-3 py-2" id="btnTambahSurket" style="border-radius: 8px;">
                        <i class="fas fa-plus-circle mr-2"></i>Buat Surket Kinerja TA Baru
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover table-striped w-100 align-middle" id="tableSurket" style="font-size: 0.92rem;">
                    <thead class="bg-light text-dark font-weight-bold">
                        <tr>
                            <th style="width: 4%; text-align: center;">No</th>
                            <th style="width: 20%;">Tenaga Ahli</th>
                            <th style="width: 18%;">Badan Usaha</th>
                            <th style="width: 22%;">Paket Pekerjaan</th>
                            <th style="width: 16%;">Kontrak &amp; Penugasan</th>
                            <th style="width: 8%; text-align: center;">Penilaian</th>
                            <th style="width: 12%; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (! empty($surketList)): ?>
                            <?php $no = 1; foreach ($surketList as $item): ?>
                                <tr>
                                    <td class="text-center font-weight-bold text-muted"><?= $no++; ?></td>
                                    <td>
                                        <div class="font-weight-bold text-dark"><?= esc($item['nama_tenaga_ahli']); ?></div>
                                        <div class="badge badge-light border text-primary font-weight-normal mt-1">
                                            <i class="fas fa-user-tag mr-1"></i><?= esc($item['jabatan_pekerjaan']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold text-secondary"><?= esc($item['nama_badan_usaha']); ?></div>
                                        <small class="text-muted d-block text-truncate" style="max-width: 220px;" title="<?= esc($item['alamat_badan_usaha']); ?>">
                                            <i class="fas fa-map-marker-alt text-danger mr-1"></i><?= esc($item['alamat_badan_usaha']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold text-dark"><?= esc($item['nama_paket']); ?></div>
                                        <small class="text-muted d-block">
                                            <span class="badge badge-info font-weight-normal mr-1"><?= esc($item['lingkup_jasa']); ?></span>
                                            <span><i class="fas fa-map-pin text-secondary mr-1"></i><?= esc($item['lokasi_pekerjaan']); ?></span>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="small font-weight-bold text-dark text-truncate" style="max-width: 200px;" title="<?= esc($item['nomor_tanggal_kontrak']); ?>">
                                            <?= esc($item['nomor_tanggal_kontrak']); ?>
                                        </div>
                                        <div class="small text-success font-weight-bold mt-1">
                                            <?= esc($item['nilai_kontrak']); ?>
                                        </div>
                                        <small class="text-muted d-block mt-1">
                                            <i class="far fa-calendar-alt mr-1"></i><?= esc($item['masa_penugasan']); ?>
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                            $nilai = trim((string) ($item['penilaian_keseluruhan'] ?? ''));
                                            $badgeClass = match ($nilai) {
                                                'Sangat Baik' => 'badge-success',
                                                'Baik'        => 'badge-primary',
                                                'Cukup'       => 'badge-warning',
                                                'Kurang'      => 'badge-danger',
                                                default       => 'badge-secondary',
                                            };
                                        ?>
                                        <span class="badge <?= $badgeClass; ?> px-2 py-1 shadow-xs font-weight-normal" style="font-size: 0.85rem;">
                                            <?= esc($nilai ?: 'Sangat Baik'); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <?php if ($permissions['export'] ?? false): ?>
                                                <a href="<?= site_url('admin/kontrak/surket-kinerja-ta/' . $item['id'] . '/unduh-docx'); ?>" 
                                                   class="btn btn-outline-primary" 
                                                   title="Unduh Dokumen Word (.docx)"
                                                   data-toggle="tooltip">
                                                    <i class="fas fa-file-word"></i>
                                                </a>
                                                <a href="<?= site_url('admin/kontrak/surket-kinerja-ta/' . $item['id'] . '/cetak-pdf'); ?>" 
                                                   target="_blank" 
                                                   class="btn btn-outline-danger" 
                                                   title="Cetak / Pratinjau PDF"
                                                   data-toggle="tooltip">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                            <?php endif; ?>

                                            <button type="button" 
                                                    class="btn btn-outline-info btn-preview-surket" 
                                                    data-id="<?= $item['id']; ?>" 
                                                    title="Pratinjau Data Lengkap"
                                                    data-toggle="tooltip">
                                                <i class="fas fa-eye"></i>
                                            </button>

                                            <?php if ($permissions['edit'] ?? false): ?>
                                                <button type="button" 
                                                        class="btn btn-outline-warning btn-edit-surket" 
                                                        data-id="<?= $item['id']; ?>" 
                                                        title="Ubah Data"
                                                        data-toggle="tooltip">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            <?php endif; ?>

                                            <?php if ($permissions['delete'] ?? false): ?>
                                                <button type="button" 
                                                        class="btn btn-outline-secondary btn-delete-surket text-danger" 
                                                        data-id="<?= $item['id']; ?>" 
                                                        data-nama="<?= esc($item['nama_tenaga_ahli']); ?>" 
                                                        title="Hapus Data"
                                                        data-toggle="tooltip">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL FORM (TAMBAH / UBAH SURKET KINERJA TA) -->
<!-- ============================================================ -->
<div class="modal fade" id="modalFormSurket" tabindex="-1" role="dialog" aria-labelledby="modalFormSurketLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <form id="formSurket" autocomplete="off" class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <input type="hidden" name="id" id="surket_id" value="">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title font-weight-bold" id="modalFormSurketLabel">
                    <i class="fas fa-file-signature mr-2"></i>Form Surat Keterangan Kinerja Tenaga Ahli
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" style="background-color: #f8f9fa; max-height: calc(100vh - 200px); overflow-y: auto;">
                    
                    <!-- ALERT VALIDATION JS -->
                    <div id="formAlertContainer" class="d-none"></div>

                    <!-- ACCORDION / KELOMPOK FORM -->
                    <div class="row">
                        <!-- KOLOM KIRI: TENAGA AHLI & BADAN USAHA -->
                        <div class="col-lg-6 mb-3">
                            <div class="card h-100 border-0 shadow-sm" style="border-radius: 10px;">
                                <div class="card-header bg-white border-bottom py-2 font-weight-bold text-primary">
                                    <i class="fas fa-user-tie mr-2"></i>1. Data Tenaga Ahli &amp; Badan Usaha
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="nama_tenaga_ahli" class="font-weight-bold">
                                            Nama Tenaga Ahli <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="nama_tenaga_ahli" name="nama_tenaga_ahli" 
                                               placeholder="Contoh: Ir. Budi Santoso, S.T., M.T." required>
                                    </div>

                                    <div class="form-group">
                                        <label for="jabatan_pekerjaan" class="font-weight-bold">
                                            Jabatan dalam Pekerjaan <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="jabatan_pekerjaan" name="jabatan_pekerjaan" 
                                               placeholder="Contoh: Ahli Struktur / Team Leader" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="nama_badan_usaha" class="font-weight-bold">
                                            Nama Badan Usaha <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="nama_badan_usaha" name="nama_badan_usaha" 
                                               placeholder="Contoh: PT. Citra Reka Konsultan" required>
                                    </div>

                                    <div class="form-group mb-0">
                                        <label for="alamat_badan_usaha" class="font-weight-bold">
                                            Alamat Badan Usaha <span class="text-danger">*</span>
                                        </label>
                                        <textarea class="form-control" id="alamat_badan_usaha" name="alamat_badan_usaha" rows="3" 
                                                  placeholder="Contoh: Jl. Jenderal Sudirman No. 123, Pekanbaru" required></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- KOLOM KANAN: NOMOR SURAT & PENANDATANGAN (PPK) -->
                        <div class="col-lg-6 mb-3">
                            <div class="card h-100 border-0 shadow-sm" style="border-radius: 10px;">
                                <div class="card-header bg-white border-bottom py-2 font-weight-bold text-primary">
                                    <i class="fas fa-stamp mr-2"></i>2. Nomor Surat &amp; Pejabat Penandatangan
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-sm-6 form-group">
                                            <label for="nomor_surat" class="font-weight-bold">Nomor Surat</label>
                                            <input type="text" class="form-control" id="nomor_surat" name="nomor_surat" 
                                                   placeholder="Contoh: PW.01.02/Cb19.5/01/2026">
                                        </div>
                                        <div class="col-sm-6 form-group">
                                            <label for="tanggal_surat" class="font-weight-bold">Tanggal Surat</label>
                                            <input type="date" class="form-control" id="tanggal_surat" name="tanggal_surat" 
                                                   value="<?= date('Y-m-d'); ?>">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="pilih_ppk_preset" class="font-weight-bold">
                                            Pilih Pejabat Penandatangan (PPK)
                                        </label>
                                        <select class="form-control" id="pilih_ppk_preset">
                                            <option value="">-- Pilih dari Master Pegawai --</option>
                                            <?php foreach ($pegawaiList as $p): ?>
                                                <?php $isDef = ($defaultPpk && $defaultPpk['id'] == $p['id']); ?>
                                                <option value="<?= $p['id']; ?>" 
                                                        data-nama="<?= esc($p['nama']); ?>" 
                                                        data-nip="<?= esc($p['nip']); ?>"
                                                        <?= $isDef ? 'selected' : ''; ?>>
                                                    <?= esc($p['nama']); ?> (NIP. <?= esc($p['nip']); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <input type="hidden" name="ppk_pegawai_id" id="ppk_pegawai_id" value="<?= esc($defaultPpk['id'] ?? ''); ?>">

                                    <div class="row">
                                        <div class="col-sm-6 form-group">
                                            <label for="ppk_nama" class="font-weight-bold">Nama PPK <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="ppk_nama" name="ppk_nama" 
                                                   value="<?= esc($defaultPpk['nama'] ?? 'Nurhidayat Nugroho, S.Ars'); ?>" required>
                                        </div>
                                        <div class="col-sm-6 form-group">
                                            <label for="ppk_nip" class="font-weight-bold">NIP PPK <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="ppk_nip" name="ppk_nip" 
                                                   value="<?= esc($defaultPpk['nip'] ?? '199012212018021001'); ?>" required>
                                        </div>
                                    </div>

                                    <div class="form-group mb-0">
                                        <label for="ppk_jabatan" class="font-weight-bold">Jabatan PPK <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="ppk_jabatan" name="ppk_jabatan" 
                                               value="PPK Pelaksanaan Prasarana Strategis" required>
                                    </div>
                                    <input type="hidden" name="ppk_satker" id="ppk_satker" value="Satuan Kerja Pelaksanaan Prasarana Strategis Riau">
                                    <input type="hidden" name="ppk_alamat" id="ppk_alamat" value="Jl. Datuk Setia Maharaja No. 1 Pekanbaru">
                                    <input type="hidden" name="kota_surat" id="kota_surat" value="Pekanbaru">
                                </div>
                            </div>
                        </div>

                        <!-- KOLOM BAWAH PENUH: DATA PAKET PEKERJAAN & KONTRAK -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm" style="border-radius: 10px;">
                                <div class="card-header bg-white border-bottom py-2 font-weight-bold text-primary">
                                    <i class="fas fa-briefcase mr-2"></i>3. Data Paket Pekerjaan &amp; Pelaksanaan Kontrak
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label for="pilih_paket_preset" class="font-weight-bold">
                                                Pilih dari Master Paket (Opsional)
                                            </label>
                                            <select class="form-control" id="pilih_paket_preset">
                                                <option value="">-- Ketik Nama Paket Bebas / Manual --</option>
                                                <?php foreach ($paketList as $pkt): ?>
                                                    <option value="<?= $pkt['id']; ?>" data-nama="<?= esc($pkt['nama_paket']); ?>">
                                                        <?= esc($pkt['nama_paket']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="hidden" name="paket_id" id="paket_id" value="">
                                        </div>

                                        <div class="col-md-6 form-group">
                                            <label for="nama_paket" class="font-weight-bold">
                                                Nama Paket Pekerjaan <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="nama_paket" name="nama_paket" 
                                                   placeholder="Contoh: Pembangunan Sarana & Prasarana Madrasah Terpadu Pekanbaru" required>
                                        </div>

                                        <div class="col-md-6 form-group">
                                            <label for="lingkup_jasa" class="font-weight-bold">
                                                Lingkup Jasa <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="lingkup_jasa" name="lingkup_jasa" 
                                                   value="Manajemen Konstruksi" placeholder="Contoh: Manajemen Konstruksi" required>
                                        </div>

                                        <div class="col-md-6 form-group">
                                            <label for="lokasi_pekerjaan" class="font-weight-bold">
                                                Lokasi Pekerjaan <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="lokasi_pekerjaan" name="lokasi_pekerjaan" 
                                                   placeholder="Contoh: Kota Pekanbaru" required>
                                        </div>

                                        <div class="col-md-6 form-group">
                                            <label for="nomor_tanggal_kontrak" class="font-weight-bold">
                                                Nomor &amp; Tanggal Kontrak <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="nomor_tanggal_kontrak" name="nomor_tanggal_kontrak" 
                                                   placeholder="Contoh: HK.02.01/SP/PPS-RIAU/01/2026 tanggal 15 Januari 2026" required>
                                        </div>

                                        <div class="col-md-6 form-group">
                                            <label for="nilai_kontrak" class="font-weight-bold">
                                                Nilai Kontrak (termasuk addendum bila ada) <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="nilai_kontrak" name="nilai_kontrak" 
                                                   placeholder="Contoh: Rp 1.450.000.000,-" required>
                                        </div>

                                        <div class="col-md-6 form-group">
                                            <label for="sumber_dana" class="font-weight-bold">
                                                Sumber Dana <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="sumber_dana" name="sumber_dana" 
                                                   value="APBN DIPA Satker Pelaksanaan Prasarana Strategis Riau" required>
                                        </div>

                                        <div class="col-md-6 form-group">
                                            <label for="masa_penugasan" class="font-weight-bold">
                                                Masa Penugasan Tenaga Ahli <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="masa_penugasan" name="masa_penugasan" 
                                                   placeholder="Contoh: 01 Februari 2026 s.d. 31 Juli 2026 (6 Bulan)" required>
                                        </div>

                                        <div class="col-md-6 form-group mb-md-0">
                                            <label for="status_pekerjaan" class="font-weight-bold">
                                                Status Pekerjaan <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="status_pekerjaan" name="status_pekerjaan" 
                                                   value="selesai 100%" required>
                                        </div>

                                        <div class="col-md-6 form-group mb-0">
                                            <label for="penilaian_keseluruhan" class="font-weight-bold">
                                                Penilaian Keseluruhan <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-control font-weight-bold" id="penilaian_keseluruhan" name="penilaian_keseluruhan" required>
                                                <option value="Sangat Baik" selected>Sangat Baik</option>
                                                <option value="Baik">Baik</option>
                                                <option value="Cukup">Cukup</option>
                                                <option value="Kurang">Kurang</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-white border-top py-3">
                    <button type="button" class="btn btn-secondary font-weight-bold px-4" data-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary font-weight-bold px-4" id="btnSubmitForm">
                        <i class="fas fa-save mr-2"></i>Simpan Dokumen
                    </button>
                </div>
        </form>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL PREVIEW DETAIL SURKET KINERJA TA -->
<!-- ============================================================ -->
<div class="modal fade" id="modalPreviewSurket" tabindex="-1" role="dialog" aria-labelledby="modalPreviewSurketLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title font-weight-bold" id="modalPreviewSurketLabel">
                    <i class="fas fa-eye mr-2"></i>Pratinjau Surat Keterangan Kinerja TA
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4 bg-light" style="max-height: calc(100vh - 200px); overflow-y: auto;">
                <div class="card border-0 shadow-sm p-4 bg-white" style="border-radius: 8px; font-family: Arial, sans-serif;">
                    <div class="text-center pb-2 mb-3 border-bottom">
                        <h6 class="font-weight-bold text-uppercase mb-0" style="letter-spacing: 0.5px;">SURAT KETERANGAN / REFERENSI KINERJA</h6>
                        <h6 class="font-weight-bold text-uppercase mb-0">TENAGA AHLI DAN PENDUKUNG</h6>
                        <h6 class="font-weight-bold text-uppercase mb-1">KONSULTANSI KONSTRUKSI</h6>
                        <div class="small" id="preview_nomor_surat">Nomor: -</div>
                    </div>

                    <p class="small mb-1">Yang bertanda tangan di bawah ini:</p>
                    <table class="table table-sm table-borderless small mb-2">
                        <tr>
                            <td style="width: 32%;">Nama</td>
                            <td style="width: 3%;">:</td>
                            <td id="preview_ppk_nama" class="font-weight-bold">-</td>
                        </tr>
                        <tr>
                            <td>Jabatan</td>
                            <td>:</td>
                            <td id="preview_ppk_jabatan">-</td>
                        </tr>
                        <tr>
                            <td>Satker</td>
                            <td>:</td>
                            <td id="preview_ppk_satker">-</td>
                        </tr>
                        <tr>
                            <td>Alamat</td>
                            <td>:</td>
                            <td id="preview_ppk_alamat">-</td>
                        </tr>
                    </table>

                    <p class="small mb-1">Dengan ini menerangkan bahwa:</p>
                    <table class="table table-sm table-borderless small mb-2">
                        <tr>
                            <td style="width: 32%;">Nama Tenaga Ahli</td>
                            <td style="width: 3%;">:</td>
                            <td id="preview_nama_tenaga_ahli" class="font-weight-bold text-primary">-</td>
                        </tr>
                        <tr>
                            <td>Jabatan dalam Pekerjaan</td>
                            <td>:</td>
                            <td id="preview_jabatan_pekerjaan">-</td>
                        </tr>
                        <tr>
                            <td>Nama Badan Usaha</td>
                            <td>:</td>
                            <td id="preview_nama_badan_usaha">-</td>
                        </tr>
                        <tr>
                            <td>Alamat Badan Usaha</td>
                            <td>:</td>
                            <td id="preview_alamat_badan_usaha">-</td>
                        </tr>
                    </table>

                    <p class="small mb-1">telah melaksanakan pekerjaan jasa konsultansi konstruksi dengan data sebagai berikut:</p>
                    <table class="table table-sm table-borderless small mb-3">
                        <tr>
                            <td style="width: 32%;">Nama Paket Pekerjaan</td>
                            <td style="width: 3%;">:</td>
                            <td id="preview_nama_paket" class="font-weight-bold">-</td>
                        </tr>
                        <tr>
                            <td>Lingkup Jasa</td>
                            <td>:</td>
                            <td id="preview_lingkup_jasa">-</td>
                        </tr>
                        <tr>
                            <td>Lokasi Pekerjaan</td>
                            <td>:</td>
                            <td id="preview_lokasi_pekerjaan">-</td>
                        </tr>
                        <tr>
                            <td>Nomor &amp; Tanggal Kontrak</td>
                            <td>:</td>
                            <td id="preview_nomor_tanggal_kontrak">-</td>
                        </tr>
                        <tr>
                            <td>Nilai Kontrak</td>
                            <td>:</td>
                            <td id="preview_nilai_kontrak">-</td>
                        </tr>
                        <tr>
                            <td>Sumber Dana</td>
                            <td>:</td>
                            <td id="preview_sumber_dana">-</td>
                        </tr>
                        <tr>
                            <td>Masa Penugasan Tenaga Ahli</td>
                            <td>:</td>
                            <td id="preview_masa_penugasan">-</td>
                        </tr>
                        <tr>
                            <td>Status Pekerjaan</td>
                            <td>:</td>
                            <td id="preview_status_pekerjaan">-</td>
                        </tr>
                    </table>

                    <div class="small font-weight-bold mb-3">
                        Penilaian keseluruhan: <span id="preview_penilaian_keseluruhan" class="badge badge-success px-2 py-1">-</span>
                    </div>

                    <div class="small text-muted mb-4" style="line-height: 1.5;">
                        Demikian surat keterangan ini dibuat dengan sebenarnya untuk dipergunakan sebagaimana mestinya, antara lain sebagai bukti pengalaman dalam proses pengadaan jasa konsultansi konstruksi.
                    </div>

                    <div class="row small">
                        <div class="col-6"></div>
                        <div class="col-6 text-left">
                            <div id="preview_kota_tanggal">Pekanbaru, -</div>
                            <div id="preview_ppk_jabatan_ttd">PPK Pelaksanaan Prasarana Strategis,</div>
                            <div id="preview_ppk_satker_ttd">Satuan Kerja Pelaksanaan Prasarana Strategis Riau</div>
                            <div style="height: 50px;"></div>
                            <div id="preview_ppk_nama_ttd" class="font-weight-bold text-decoration-underline">-</div>
                            <div id="preview_ppk_nip_ttd">NIP. -</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white border-top py-3 d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <div>
                    <?php if ($permissions['export'] ?? false): ?>
                        <a href="#" id="previewBtnWord" class="btn btn-primary font-weight-bold mr-2">
                            <i class="fas fa-file-word mr-1"></i>Unduh Word (.docx)
                        </a>
                        <a href="#" id="previewBtnPdf" target="_blank" class="btn btn-danger font-weight-bold">
                            <i class="fas fa-file-pdf mr-1"></i>Cetak PDF
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
$(document).ready(function () {
    // 1. Inisialisasi DataTable
    const tableSurket = $('#tableSurket').DataTable({
        responsive: true,
        ordering: true,
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [6] }
        ],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            zeroRecords: "Tidak ada data yang cocok dengan pencarian",
            emptyTable: "Belum ada data Surat Keterangan Kinerja Tenaga Ahli",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(disaring dari _MAX_ total data)",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Berikutnya",
                previous: "Sebelumnya"
            }
        }
    });

    $('[data-toggle="tooltip"]').tooltip();

    // 2. Preset Paket dropdown listener
    $('#pilih_paket_preset').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const val = $(this).val();
        const nama = selectedOption.data('nama') || '';

        if (val) {
            $('#paket_id').val(val);
            $('#nama_paket').val(nama);
        } else {
            $('#paket_id').val('');
        }
    });

    // 3. Preset PPK dropdown listener
    $('#pilih_ppk_preset').on('change', function() {
        const opt = $(this).find('option:selected');
        const val = $(this).val();
        if (val) {
            $('#ppk_pegawai_id').val(val);
            $('#ppk_nama').val(opt.data('nama') || '');
            $('#ppk_nip').val(opt.data('nip') || '');
        } else {
            $('#ppk_pegawai_id').val('');
        }
    });

    // 4. Tombol Tambah Surket Baru
    $('#btnTambahSurket').on('click', function() {
        $('#formSurket')[0].reset();
        $('#surket_id').val('');
        $('#pilih_paket_preset').val('');
        $('#paket_id').val('');
        $('#modalFormSurketLabel').html('<i class="fas fa-plus-circle mr-2"></i>Buat Surket Kinerja TA Baru');
        $('#btnSubmitForm').html('<i class="fas fa-save mr-2"></i>Simpan Dokumen');
        $('#formAlertContainer').addClass('d-none').html('');
        
        // Reset default PPK
        const defaultPpkOpt = $('#pilih_ppk_preset option[selected]');
        if (defaultPpkOpt.length) {
            $('#pilih_ppk_preset').val(defaultPpkOpt.val()).trigger('change');
        }

        $('#modalFormSurket').modal('show');
    });

    // 5. Submit Form (Tambah / Ubah)
    $('#formSurket').on('submit', function(e) {
        e.preventDefault();

        const surketId = $('#surket_id').val();
        const isEdit = (surketId && surketId !== '');
        const submitUrl = isEdit 
            ? '<?= site_url('admin/kontrak/surket-kinerja-ta'); ?>/' + surketId + '/ubah'
            : '<?= site_url('admin/kontrak/surket-kinerja-ta/simpan'); ?>';

        const $btn = $('#btnSubmitForm');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...');

        $.ajax({
            url: submitUrl,
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                $btn.prop('disabled', false).html(originalText);
                if (res.success) {
                    $('#modalFormSurket').modal('hide');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message || 'Data berhasil disimpan.',
                            timer: 1600,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        window.location.reload();
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: res.message || 'Terjadi kesalahan saat menyimpan data.'
                    });
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html(originalText);
                let errorMsg = 'Terjadi kesalahan pada server.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan',
                        text: errorMsg
                    });
                } else {
                    alert(errorMsg);
                }
            }
        });
    });

    // 6. Tombol Ubah / Edit
    $(document).on('click', '.btn-edit-surket', function() {
        const id = $(this).data('id');
        const detailUrl = '<?= site_url('admin/kontrak/surket-kinerja-ta'); ?>/' + id + '/detail';

        $.ajax({
            url: detailUrl,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success && res.data) {
                    const d = res.data;
                    $('#formSurket')[0].reset();
                    $('#surket_id').val(d.id);
                    $('#modalFormSurketLabel').html('<i class="fas fa-edit mr-2"></i>Ubah Data Surket Kinerja TA');
                    $('#btnSubmitForm').html('<i class="fas fa-save mr-2"></i>Simpan Perubahan');

                    $('#nama_tenaga_ahli').val(d.nama_tenaga_ahli);
                    $('#jabatan_pekerjaan').val(d.jabatan_pekerjaan);
                    $('#nama_badan_usaha').val(d.nama_badan_usaha);
                    $('#alamat_badan_usaha').val(d.alamat_badan_usaha);
                    $('#nomor_surat').val(d.nomor_surat);
                    $('#tanggal_surat').val(d.tanggal_surat);
                    $('#ppk_pegawai_id').val(d.ppk_pegawai_id || '');
                    $('#pilih_ppk_preset').val(d.ppk_pegawai_id || '');
                    $('#ppk_nama').val(d.ppk_nama);
                    $('#ppk_nip').val(d.ppk_nip);
                    $('#ppk_jabatan').val(d.ppk_jabatan);
                    $('#ppk_satker').val(d.ppk_satker);
                    $('#ppk_alamat').val(d.ppk_alamat);
                    $('#paket_id').val(d.paket_id || '');
                    $('#pilih_paket_preset').val(d.paket_id || '');
                    $('#nama_paket').val(d.nama_paket);
                    $('#lingkup_jasa').val(d.lingkup_jasa);
                    $('#lokasi_pekerjaan').val(d.lokasi_pekerjaan);
                    $('#nomor_tanggal_kontrak').val(d.nomor_tanggal_kontrak);
                    $('#nilai_kontrak').val(d.nilai_kontrak);
                    $('#sumber_dana').val(d.sumber_dana);
                    $('#masa_penugasan').val(d.masa_penugasan);
                    $('#status_pekerjaan').val(d.status_pekerjaan);
                    $('#penilaian_keseluruhan').val(d.penilaian_keseluruhan);

                    $('#modalFormSurket').modal('show');
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Gagal mengambil data.' });
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Koneksi ke server bermasalah.' });
            }
        });
    });

    // 7. Tombol Pratinjau Detail
    $(document).on('click', '.btn-preview-surket', function() {
        const id = $(this).data('id');
        const detailUrl = '<?= site_url('admin/kontrak/surket-kinerja-ta'); ?>/' + id + '/detail';

        $.ajax({
            url: detailUrl,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success && res.data) {
                    const d = res.data;
                    $('#preview_nomor_surat').text('Nomor: ' + (d.nomor_surat || '..........................................'));
                    $('#preview_ppk_nama').text(d.ppk_nama || '-');
                    $('#preview_ppk_jabatan').text(d.ppk_jabatan || '-');
                    $('#preview_ppk_satker').text(d.ppk_satker || '-');
                    $('#preview_ppk_alamat').text(d.ppk_alamat || '-');

                    $('#preview_nama_tenaga_ahli').text(d.nama_tenaga_ahli || '-');
                    $('#preview_jabatan_pekerjaan').text(d.jabatan_pekerjaan || '-');
                    $('#preview_nama_badan_usaha').text(d.nama_badan_usaha || '-');
                    $('#preview_alamat_badan_usaha').text(d.alamat_badan_usaha || '-');

                    $('#preview_nama_paket').text(d.nama_paket || '-');
                    $('#preview_lingkup_jasa').text(d.lingkup_jasa || '-');
                    $('#preview_lokasi_pekerjaan').text(d.lokasi_pekerjaan || '-');
                    $('#preview_nomor_tanggal_kontrak').text(d.nomor_tanggal_kontrak || '-');
                    $('#preview_nilai_kontrak').text(d.nilai_kontrak || '-');
                    $('#preview_sumber_dana').text(d.sumber_dana || '-');
                    $('#preview_masa_penugasan').text(d.masa_penugasan || '-');
                    $('#preview_status_pekerjaan').text(d.status_pekerjaan || '-');
                    $('#preview_penilaian_keseluruhan').text(d.penilaian_keseluruhan || '-');

                    $('#preview_kota_tanggal').text((d.kota_surat || 'Pekanbaru') + ', ' + (d.tanggal_surat || ''));
                    $('#preview_ppk_jabatan_ttd').text((d.ppk_jabatan || 'PPK Pelaksanaan Prasarana Strategis') + ',');
                    $('#preview_ppk_satker_ttd').text(d.ppk_satker || 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau');
                    $('#preview_ppk_nama_ttd').text(d.ppk_nama || '-');
                    $('#preview_ppk_nip_ttd').text('NIP. ' + (d.ppk_nip || '-'));

                    $('#previewBtnWord').attr('href', '<?= site_url('admin/kontrak/surket-kinerja-ta'); ?>/' + d.id + '/unduh-docx');
                    $('#previewBtnPdf').attr('href', '<?= site_url('admin/kontrak/surket-kinerja-ta'); ?>/' + d.id + '/cetak-pdf');

                    $('#modalPreviewSurket').modal('show');
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Gagal memuat pratinjau.' });
                }
            }
        });
    });

    // 8. Tombol Hapus Data
    $(document).on('click', '.btn-delete-surket', function() {
        const id = $(this).data('id');
        const nama = $(this).data('nama');
        const deleteUrl = '<?= site_url('admin/kontrak/surket-kinerja-ta'); ?>/' + id + '/hapus';

        Swal.fire({
            title: 'Hapus Surat Keterangan?',
            html: 'Apakah Anda yakin ingin menghapus data Surket Kinerja TA untuk <strong>' + $('<div>').text(nama).html() + '</strong>?<br><small class="text-danger">Tindakan ini tidak dapat dibatalkan.</small>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus Data',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: deleteUrl,
                    type: 'POST',
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus',
                                text: res.message || 'Data berhasil dihapus.',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || 'Gagal menghapus data.' });
                        }
                    },
                    error: function() {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan jaringan.' });
                    }
                });
            }
        });
    });
});
</script>
<?= $this->endSection(); ?>
