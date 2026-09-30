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
                            <th style="width: 22%;">Tenaga Ahli</th>
                            <th style="width: 20%;">Badan Usaha</th>
                            <th style="width: 24%;">Paket Pekerjaan</th>
                            <th style="width: 18%;">Kontrak &amp; Penugasan</th>
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

                                            <?php if ($permissions['add'] ?? false): ?>
                                                <button type="button" 
                                                        class="btn btn-outline-success btn-copy-surket" 
                                                        data-id="<?= $item['id']; ?>" 
                                                        title="Duplikat / Salin Data Sebagai Dokumen Baru"
                                                        data-toggle="tooltip">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            <?php endif; ?>

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
                                        <label for="kop_surat_id" class="font-weight-bold">
                                            Kop Surat Resmi <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control font-weight-bold" id="kop_surat_id" name="kop_surat_id">
                                            <?php foreach ($kopSuratList as $kop): ?>
                                                <?php $isDef = ($defaultKop && $defaultKop['id'] == $kop['id']); ?>
                                                <option value="<?= $kop['id']; ?>" <?= $isDef ? 'selected' : ''; ?>>
                                                    <?= esc($kop['title']); ?> <?= ! empty($kop['is_active']) ? '(Aktif)' : ''; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="form-text text-muted">Secara default menggunakan kop surat aktif dari Master Kop Surat.</small>
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
                                            <select class="form-control font-weight-bold" id="lingkup_jasa" name="lingkup_jasa" required>
                                                <option value="Manajemen Konstruksi" selected>Manajemen Konstruksi</option>
                                                <option value="Fisik">Fisik</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 form-group">
                                            <label for="lokasi_pekerjaan" class="font-weight-bold">
                                                Lokasi Pekerjaan <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="lokasi_pekerjaan" name="lokasi_pekerjaan" 
                                                   placeholder="Contoh: Kota Pekanbaru" required>
                                        </div>

                                        <div class="col-md-6 form-group">
                                            <label for="nomor_kontrak" class="font-weight-bold">
                                                Nomor Kontrak <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="nomor_kontrak" name="nomor_kontrak" 
                                                   placeholder="Contoh: HK.02.01/SP/PPS-RIAU/01/2026" required>
                                        </div>

                                        <div class="col-md-6 form-group">
                                            <label for="tanggal_kontrak" class="font-weight-bold">
                                                Tanggal Kontrak <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control" id="tanggal_kontrak" name="tanggal_kontrak" 
                                                   value="<?= date('Y-m-d'); ?>" required>
                                        </div>

                                        <div class="col-md-6 form-group">
                                            <label for="nilai_kontrak" class="font-weight-bold">
                                                Nilai Kontrak (termasuk addendum bila ada) <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text font-weight-bold bg-light">Rp</span>
                                                </div>
                                                <input type="text" class="form-control font-weight-bold" id="nilai_kontrak" name="nilai_kontrak" 
                                                       placeholder="Contoh: 1.450.000.000" required>
                                            </div>
                                            <small class="form-text text-muted">Input angka saja (format Rupiah otomatis terbentuk).</small>
                                        </div>

                                        <div class="col-md-6 form-group">
                                            <label for="sumber_dana" class="font-weight-bold">
                                                Sumber Dana <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="sumber_dana" name="sumber_dana" 
                                                   value="APBN DIPA Satker Pelaksanaan Prasarana Strategis Riau" required>
                                        </div>

                                        <div class="col-md-6 form-group">
                                            <label for="status_persen" class="font-weight-bold">
                                                Status Pekerjaan <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="status_persen" name="status_persen" 
                                                       min="0" max="100" value="100" placeholder="100" required>
                                                <div class="input-group-append">
                                                    <span class="input-group-text bg-light font-weight-bold">%</span>
                                                </div>
                                            </div>
                                            <small class="form-text text-muted">
                                                Teks tercetak: <span class="badge badge-success font-weight-bold" id="badge_status_preview">Selesai</span> <span class="text-secondary">(100% tercetak "Selesai")</span>
                                            </small>
                                        </div>

                                        <!-- Masa Penugasan Tenaga Ahli (Dari Tanggal s.d. Tanggal & Total Hari) -->
                                        <div class="col-12 form-group mb-0 border-top pt-3">
                                            <label class="font-weight-bold d-block text-dark mb-2">
                                                <i class="far fa-calendar-alt text-primary mr-1"></i>Masa Penugasan Tenaga Ahli <span class="text-danger">*</span>
                                            </label>
                                            <div class="row">
                                                <div class="col-md-4 form-group mb-2">
                                                    <label for="tanggal_mulai_penugasan" class="small font-weight-bold text-muted mb-1">
                                                        Dari Tanggal (Mulai) <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="date" class="form-control" id="tanggal_mulai_penugasan" name="tanggal_mulai_penugasan" required>
                                                </div>
                                                <div class="col-md-4 form-group mb-2">
                                                    <label for="tanggal_selesai_penugasan" class="small font-weight-bold text-muted mb-1">
                                                        Hingga Tanggal (Selesai) <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="date" class="form-control" id="tanggal_selesai_penugasan" name="tanggal_selesai_penugasan" required>
                                                </div>
                                                <div class="col-md-4 form-group mb-2">
                                                    <label for="masa_penugasan_hari" class="small font-weight-bold text-muted mb-1">
                                                        Total Masa Penugasan <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" id="masa_penugasan_hari" name="masa_penugasan_hari" 
                                                               min="1" placeholder="Contoh: 180" required>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text bg-light font-weight-bold">Hari</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="alert alert-light border py-2 px-3 mb-0 small text-muted">
                                                        <i class="fas fa-eye text-info mr-1"></i>Teks tercetak pada dokumen: 
                                                        <strong class="text-dark ml-1" id="preview_masa_penugasan_live">-</strong>
                                                        <span class="text-secondary ml-1" id="keterangan_hari_penugasan"></span>
                                                    </div>
                                                </div>
                                            </div>
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

                    <p class="small mb-1 font-weight-bold">Yang bertanda tangan di bawah ini:</p>
                    <table class="table table-sm table-borderless small mb-2" style="margin-left: 25px; width: calc(100% - 25px);">
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

                    <p class="small mb-1 font-weight-bold">Dengan ini menerangkan bahwa:</p>
                    <table class="table table-sm table-borderless small mb-2" style="margin-left: 25px; width: calc(100% - 25px);">
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

                    <p class="small mb-1 font-weight-bold" id="preview_teks_pengantar">telah melaksanakan pekerjaan jasa konsultansi dengan data sebagai berikut:</p>
                    <table class="table table-sm table-borderless small mb-3" style="margin-left: 25px; width: calc(100% - 25px);">
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

                    <div class="small text-muted mb-4" id="preview_teks_penutup" style="line-height: 1.5;">
                        Demikian surat keterangan ini dibuat dengan sebenarnya untuk dipergunakan sebagaimana mestinya, antara lain sebagai bukti pengalaman dalam proses pengadaan jasa konsultansi.
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
                    <?php if ($permissions['add'] ?? false): ?>
                        <button type="button" id="previewBtnCopy" class="btn btn-outline-success font-weight-bold mr-2" data-id="">
                            <i class="fas fa-copy mr-1"></i>Duplikat Data
                        </button>
                    <?php endif; ?>
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
            { orderable: false, targets: [5] }
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

    // Helper Format Rupiah Input
    function formatRupiahDisplay(angka) {
        let number_string = (angka || '').toString().replace(/[^0-9]/g, '');
        let split = number_string.split(',');
        let sisa = split[0].length % 3;
        let rupiah = split[0].substr(0, sisa);
        let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        return rupiah;
    }

    // Helper Badge Status Pekerjaan
    function updateStatusBadge(val) {
        let p = parseInt(val, 10);
        if (isNaN(p) || p < 0) p = 0;
        if (p > 100) p = 100;
        if (p >= 100) {
            $('#badge_status_preview').removeClass('badge-info badge-secondary badge-warning').addClass('badge-success').text('Selesai');
        } else {
            $('#badge_status_preview').removeClass('badge-success badge-secondary').addClass('badge-info').text(p + '%');
        }
    }

    $('#nilai_kontrak').on('input keyup', function () {
        $(this).val(formatRupiahDisplay($(this).val()));
    });

    $('#status_persen').on('input change keyup', function () {
        updateStatusBadge($(this).val());
    });

    // Helper Format Tanggal Indonesia
    const NAMA_BULAN_ID = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    function formatTanggalIndo(dateStr) {
        if (!dateStr) return '';
        const parts = dateStr.split('-');
        if (parts.length === 3) {
            const y = parts[0];
            const m = parseInt(parts[1], 10) - 1;
            const d = parts[2];
            if (m >= 0 && m < 12) {
                return `${d} ${NAMA_BULAN_ID[m]} ${y}`;
            }
        }
        return dateStr;
    }

    // Helper Preview Masa Penugasan
    function updatePreviewMasaPenugasan() {
        const tglMulai = $('#tanggal_mulai_penugasan').val();
        const tglSelesai = $('#tanggal_selesai_penugasan').val();
        const hari = $('#masa_penugasan_hari').val();

        if (tglMulai && tglSelesai) {
            const strMulai = formatTanggalIndo(tglMulai);
            const strSelesai = formatTanggalIndo(tglSelesai);
            const strHari = hari ? ` (${hari} Hari)` : '';
            $('#preview_masa_penugasan_live').text(`${strMulai} s.d. ${strSelesai}${strHari}`);
        } else if (hari) {
            $('#preview_masa_penugasan_live').text(`${hari} Hari`);
        } else {
            $('#preview_masa_penugasan_live').text('-');
        }
    }

    $('#tanggal_mulai_penugasan, #tanggal_selesai_penugasan').on('change input', function() {
        const tglMulai = $('#tanggal_mulai_penugasan').val();
        const tglSelesai = $('#tanggal_selesai_penugasan').val();

        if (tglMulai && tglSelesai) {
            const d1 = new Date(tglMulai + 'T00:00:00');
            const d2 = new Date(tglSelesai + 'T00:00:00');
            if (!isNaN(d1.getTime()) && !isNaN(d2.getTime())) {
                if (d2 >= d1) {
                    const diffTime = d2.getTime() - d1.getTime();
                    const diffDays = Math.round(diffTime / (1000 * 60 * 60 * 24)) + 1;
                    $('#masa_penugasan_hari').val(diffDays);
                    $('#keterangan_hari_penugasan').text(`(Otomatis dihitung: ${diffDays} hari kalender inklusif)`);
                } else {
                    $('#keterangan_hari_penugasan').text('(Perhatian: tanggal selesai mendahului tanggal mulai)');
                }
            }
        }
        updatePreviewMasaPenugasan();
    });

    $('#masa_penugasan_hari').on('input change keyup', function() {
        updatePreviewMasaPenugasan();
    });

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
        $('#kop_surat_id').val('<?= $defaultKop['id'] ?? ''; ?>');
        $('#pilih_paket_preset').val('');
        $('#paket_id').val('');
        $('#lingkup_jasa').val('Manajemen Konstruksi');
        $('#tanggal_kontrak').val('<?= date('Y-m-d'); ?>');
        $('#tanggal_surat').val('<?= date('Y-m-d'); ?>');
        $('#tanggal_mulai_penugasan').val('');
        $('#tanggal_selesai_penugasan').val('');
        $('#masa_penugasan_hari').val('');
        $('#preview_masa_penugasan_live').text('-');
        $('#keterangan_hari_penugasan').text('');
        $('#status_persen').val(100);
        updateStatusBadge(100);
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

    // 6. Helper Muat Form Surket (Ubah atau Duplikat)
    function loadSurketForm(id, isDuplicate) {
        const detailUrl = '<?= site_url('admin/kontrak/surket-kinerja-ta'); ?>/' + id + '/detail';

        $.ajax({
            url: detailUrl,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success && res.data) {
                    const d = res.data;
                    $('#formSurket')[0].reset();
                    $('#surket_id').val(isDuplicate ? '' : d.id);
                    $('#kop_surat_id').val(d.kop_surat_id || '<?= $defaultKop['id'] ?? ''; ?>');

                    if (isDuplicate) {
                        $('#modalFormSurketLabel').html('<i class="fas fa-copy mr-2"></i>Duplikat Surket Kinerja TA (Data Baru)');
                        $('#btnSubmitForm').html('<i class="fas fa-save mr-2"></i>Simpan Sebagai Data Baru');
                        $('#formAlertContainer').removeClass('d-none').html(
                            '<div class="alert alert-info py-2 px-3 mb-0 small"><i class="fas fa-info-circle mr-1"></i>Menyalin data dari <strong>' + 
                            $('<div>').text(d.nama_tenaga_ahli || '').html() + '</strong>. Silakan ubah data yang diperlukan, lalu klik <strong>Simpan Sebagai Data Baru</strong>.</div>'
                        );
                    } else {
                        $('#modalFormSurketLabel').html('<i class="fas fa-edit mr-2"></i>Ubah Data Surket Kinerja TA');
                        $('#btnSubmitForm').html('<i class="fas fa-save mr-2"></i>Simpan Perubahan');
                        $('#formAlertContainer').addClass('d-none').html('');
                    }

                    $('#nama_tenaga_ahli').val(d.nama_tenaga_ahli);
                    $('#jabatan_pekerjaan').val(d.jabatan_pekerjaan);
                    $('#nama_badan_usaha').val(d.nama_badan_usaha);
                    $('#alamat_badan_usaha').val(d.alamat_badan_usaha);
                    $('#nomor_surat').val(d.nomor_surat);
                    $('#tanggal_surat').val(isDuplicate ? '<?= date('Y-m-d'); ?>' : (d.tanggal_surat || '<?= date('Y-m-d'); ?>'));
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
                    $('#lingkup_jasa').val(d.lingkup_jasa || 'Manajemen Konstruksi');
                    $('#lokasi_pekerjaan').val(d.lokasi_pekerjaan);

                    // Nomor & Tanggal Kontrak Terpisah
                    let nomorK = d.nomor_kontrak || '';
                    let tglK = d.tanggal_kontrak || '';
                    if (!nomorK && d.nomor_tanggal_kontrak) {
                        if (d.nomor_tanggal_kontrak.indexOf(' tanggal ') !== -1) {
                            nomorK = d.nomor_tanggal_kontrak.split(' tanggal ')[0];
                        } else {
                            nomorK = d.nomor_tanggal_kontrak;
                        }
                    }
                    $('#nomor_kontrak').val(nomorK);
                    $('#tanggal_kontrak').val(tglK || '<?= date('Y-m-d'); ?>');

                    // Nilai Kontrak (format Rupiah)
                    let rawNilai = (d.nilai_kontrak || '').replace(/[^0-9]/g, '');
                    $('#nilai_kontrak').val(formatRupiahDisplay(rawNilai));

                    $('#sumber_dana').val(d.sumber_dana);

                    // Masa Penugasan (Dari Tanggal s.d. Tanggal & Total Hari)
                    $('#tanggal_mulai_penugasan').val(d.tanggal_mulai_penugasan || '');
                    $('#tanggal_selesai_penugasan').val(d.tanggal_selesai_penugasan || '');

                    let hariVal = d.masa_penugasan_hari;
                    if (!hariVal && d.masa_penugasan) {
                        let match = d.masa_penugasan.match(/\d+/);
                        hariVal = match ? match[0] : '';
                    }
                    $('#masa_penugasan_hari').val(hariVal || '');
                    $('#keterangan_hari_penugasan').text('');
                    updatePreviewMasaPenugasan();

                    // Status Pekerjaan (% dan preview Selesai jika 100%)
                    let persenVal = (d.status_persen !== null && d.status_persen !== undefined && d.status_persen !== '')
                        ? d.status_persen
                        : (d.status_pekerjaan === 'Selesai' ? 100 : (parseInt(d.status_pekerjaan, 10) || 100));
                    $('#status_persen').val(persenVal);
                    updateStatusBadge(persenVal);

                    $('#modalFormSurket').modal('show');
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Gagal mengambil data.' });
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Koneksi ke server bermasalah.' });
            }
        });
    }

    // Tombol Ubah / Edit
    $(document).on('click', '.btn-edit-surket', function() {
        loadSurketForm($(this).data('id'), false);
    });

    // Tombol Duplikat / Copy dari Tabel
    $(document).on('click', '.btn-copy-surket', function() {
        loadSurketForm($(this).data('id'), true);
    });

    // Tombol Duplikat dari Modal Pratinjau
    $('#previewBtnCopy').on('click', function() {
        const id = $(this).data('id');
        if (id) {
            $('#modalPreviewSurket').modal('hide');
            loadSurketForm(id, true);
        }
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
                    $('#previewBtnCopy').data('id', d.id);
                    $('#preview_nomor_surat').text('Nomor: ' + (d.nomor_surat || '..........................................'));
                    $('#preview_ppk_nama').text(d.ppk_nama || '-');
                    $('#preview_ppk_jabatan').text(d.ppk_jabatan || '-');
                    $('#preview_ppk_satker').text(d.ppk_satker || '-');
                    $('#preview_ppk_alamat').text(d.ppk_alamat || '-');

                    $('#preview_nama_tenaga_ahli').text(d.nama_tenaga_ahli || '-');
                    $('#preview_jabatan_pekerjaan').text(d.jabatan_pekerjaan || '-');
                    $('#preview_nama_badan_usaha').text(d.nama_badan_usaha || '-');
                    $('#preview_alamat_badan_usaha').text(d.alamat_badan_usaha || '-');

                    $('#preview_teks_pengantar').text(d.teks_pengantar || 'telah melaksanakan pekerjaan jasa konsultansi dengan data sebagai berikut:');
                    $('#preview_nama_paket').text(d.nama_paket || '-');
                    $('#preview_lingkup_jasa').text(d.lingkup_jasa || '-');
                    $('#preview_lokasi_pekerjaan').text(d.lokasi_pekerjaan || '-');
                    $('#preview_nomor_tanggal_kontrak').text(d.nomor_tanggal_kontrak || '-');
                    $('#preview_nilai_kontrak').text(d.nilai_kontrak || '-');
                    $('#preview_sumber_dana').text(d.sumber_dana || '-');
                    $('#preview_masa_penugasan').text(d.masa_penugasan || '-');
                    $('#preview_status_pekerjaan').text(d.status_pekerjaan || '-');
                    $('#preview_teks_penutup').text(d.teks_penutup || 'Demikian surat keterangan ini dibuat dengan sebenarnya untuk dipergunakan sebagaimana mestinya, antara lain sebagai bukti pengalaman dalam proses pengadaan jasa konsultansi.');

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
