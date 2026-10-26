<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<style>
.photo-dropzone {
    border: 2px dashed #cbd5e1;
    border-radius: 8px;
    background-color: #f8fafc;
    text-align: center;
    padding: 12px 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    min-height: 145px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    position: relative;
    overflow: hidden;
}
.photo-dropzone:hover, .photo-dropzone.dragover {
    border-color: #007bff;
    background-color: #eff6ff;
}
.photo-preview-img {
    max-width: 100%;
    max-height: 110px;
    object-fit: contain;
    border-radius: 6px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.12);
}
.group-card {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #ffffff;
    transition: all 0.2s ease;
}
.group-card:hover {
    box-shadow: 0 6px 16px rgba(0,0,0,0.06);
}
.group-card.status-lengkap {
    border-left: 5px solid #28a745 !important;
}
.group-card.status-sebagian {
    border-left: 5px solid #ffc107 !important;
}
.group-card.status-kosong {
    border-left: 5px solid #dc3545 !important;
}
.badge-angle {
    font-size: 0.74rem;
    font-weight: 700;
    letter-spacing: 0.3px;
    padding: 3px 8px;
    border-radius: 4px;
}
.camera-viewfinder-wrapper {
    position: relative;
    width: 100%;
    background: #000;
    border-radius: 10px;
    overflow: hidden;
    min-height: 320px;
    display: flex;
    justify-content: center;
    align-items: center;
}
.camera-viewfinder {
    width: 100%;
    max-height: 420px;
    object-fit: cover;
}
.camera-reticle {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 78%;
    height: 72%;
    border: 2px dashed rgba(253, 184, 19, 0.85); /* PU Yellow */
    border-radius: 8px;
    pointer-events: none;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 10px;
}
.camera-guide-pill {
    position: absolute;
    top: 14px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.75);
    color: #fff;
    padding: 5px 16px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    z-index: 5;
    text-transform: uppercase;
}
.camera-gps-pill {
    position: absolute;
    bottom: 12px;
    left: 12px;
    background: rgba(0, 0, 0, 0.65);
    color: #f1f5f9;
    padding: 3px 10px;
    border-radius: 14px;
    font-size: 0.72rem;
    z-index: 5;
}
.step-nav-item {
    flex: 1;
    text-align: center;
    padding: 8px 4px;
    border-bottom: 3px solid #e2e8f0;
    color: #64748b;
    font-weight: 600;
    font-size: 0.82rem;
    cursor: pointer;
    transition: all 0.2s;
}
.step-nav-item.active {
    border-bottom-color: #007bff;
    color: #007bff;
    font-weight: 700;
    background: #f0f7ff;
}
.step-nav-item.completed {
    border-bottom-color: #28a745;
    color: #28a745;
}
.shutter-btn {
    width: 66px;
    height: 66px;
    border-radius: 50%;
    background: #dc3545;
    color: #ffffff;
    border: 4px solid #ffffff;
    box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
    font-size: 1.45rem;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    transition: transform 0.1s ease;
}
.shutter-btn:active {
    transform: scale(0.92);
}
</style>

<div class="content-header pb-2">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-8">
                <h1 class="m-0 text-dark font-weight-bold" style="font-size: 1.45rem;">
                    <i class="fas fa-file-signature text-primary mr-2"></i><?= esc($title); ?>
                </h1>
                <p class="text-muted small mb-0 mt-1">
                    Lengkapi data surat, pilih barang BMN, dan lengkapi foto fisik 4 sudut (bisa upload file atau foto langsung ber-watermark)
                </p>
            </div>
            <div class="col-sm-4 text-sm-right mt-2 mt-sm-0">
                <a href="<?= site_url('admin/inventaris/pemeliharaan'); ?>" class="btn btn-outline-secondary btn-sm shadow-sm" style="border-radius: 6px;">
                    <i class="fas fa-arrow-left mr-1"></i>Kembali ke Daftar Arsip
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <form id="formPemeliharaan" method="post" enctype="multipart/form-data" action="<?= $isEdit ? site_url('admin/inventaris/pemeliharaan/' . $pemeliharaan['id'] . '/ubah') : site_url('admin/inventaris/pemeliharaan/tambah'); ?>">
            <?= csrf_field(); ?>
            <input type="hidden" name="items_json" id="itemsJsonInput" value="">

            <!-- BAGIAN 1: DATA POKOK SURAT -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 10px;">
                <div class="card-header bg-white border-bottom py-2.5 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-info-circle text-primary mr-2"></i>1. Informasi Surat Keterangan
                    </h6>
                    <span class="badge badge-primary px-2.5 py-1">Langkah 1 dari 3</span>
                </div>
                <div class="card-body p-3">
                    <div class="form-row">
                        <div class="col-md-4 mb-3">
                            <label class="small font-weight-bold text-dark">Nomor Surat <span class="text-danger">*</span></label>
                            <input type="text" name="no_surat" id="no_surat" value="<?= esc(old('no_surat', $pemeliharaan['no_surat'] ?? '')); ?>" class="form-control form-control-sm font-weight-bold text-dark" placeholder="Contoh: 19/PS-0601/SKET/Gs7/2026" required style="border-radius: 6px;">
                            <small class="text-muted">Nomor surat resmi diinput manual sesuai penomoran dinas.</small>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small font-weight-bold text-dark">Tanggal Surat <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_surat" id="tanggal_surat" value="<?= esc(old('tanggal_surat', $pemeliharaan['tanggal_surat'] ?? date('Y-m-d'))); ?>" class="form-control form-control-sm" required style="border-radius: 6px;">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="small font-weight-bold text-dark">Tempat Surat</label>
                            <input type="text" name="tempat_surat" value="<?= esc(old('tempat_surat', $pemeliharaan['tempat_surat'] ?? 'Pekanbaru')); ?>" class="form-control form-control-sm" placeholder="Pekanbaru" style="border-radius: 6px;">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small font-weight-bold text-dark">Jenis Pemeliharaan</label>
                            <select name="jenis_pemeliharaan" id="jenis_pemeliharaan" class="form-control form-control-sm" style="border-radius: 6px;">
                                <option value="TIK" <?= (old('jenis_pemeliharaan', $pemeliharaan['jenis_pemeliharaan'] ?? 'TIK') === 'TIK') ? 'selected' : ''; ?>>Peralatan & Mesin TIK</option>
                                <option value="NON TIK" <?= (old('jenis_pemeliharaan', $pemeliharaan['jenis_pemeliharaan'] ?? '') === 'NON TIK') ? 'selected' : ''; ?>>Peralatan & Mesin Non-TIK</option>
                                <option value="UMUM" <?= (old('jenis_pemeliharaan', $pemeliharaan['jenis_pemeliharaan'] ?? '') === 'UMUM') ? 'selected' : ''; ?>>Umum / Gabungan</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-7 mb-3">
                            <label class="small font-weight-bold text-dark">Judul / Perihal Dokumen <span class="text-danger">*</span></label>
                            <input type="text" name="judul_surat" id="judul_surat" value="<?= esc(old('judul_surat', $pemeliharaan['judul_surat'] ?? 'SURAT KETERANGAN PEMELIHARAAN PERALATAN & MESIN TIK')); ?>" class="form-control form-control-sm font-weight-bold" placeholder="SURAT KETERANGAN PEMELIHARAAN PERALATAN & MESIN TIK" required style="border-radius: 6px;">
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="small font-weight-bold text-dark">Kop Surat Dinas</label>
                            <select name="kop_surat_id" class="form-control form-control-sm" style="border-radius: 6px;">
                                <option value="">-- Gunakan Kop Surat Aktif Otomatis --</option>
                                <?php foreach ($kopSuratList as $kop): ?>
                                    <option value="<?= esc($kop['id']); ?>" <?= ((string)old('kop_surat_id', $pemeliharaan['kop_surat_id'] ?? '') === (string)$kop['id']) ? 'selected' : ''; ?>>
                                        <?= esc($kop['nama_kop'] ?? ('Kop #' . $kop['id'])); ?> <?= ! empty($kop['is_active']) ? '(Aktif)' : ''; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold text-dark">Nama Penandatangan (KPB)</label>
                            <input type="text" name="nama_kasatker" value="<?= esc(old('nama_kasatker', $pemeliharaan['nama_kasatker'] ?? '')); ?>" class="form-control form-control-sm" placeholder="Nama Kasatker" style="border-radius: 6px;">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold text-dark">NIP Penandatangan</label>
                            <input type="text" name="nip_kasatker" value="<?= esc(old('nip_kasatker', $pemeliharaan['nip_kasatker'] ?? '')); ?>" class="form-control form-control-sm" placeholder="NIP Kasatker" style="border-radius: 6px;">
                        </div>
                        <div class="col-md-5 mb-2">
                            <label class="small font-weight-bold text-dark">Jabatan Penandatangan</label>
                            <input type="text" name="jabatan_kasatker" value="<?= esc(old('jabatan_kasatker', $pemeliharaan['jabatan_kasatker'] ?? '')); ?>" class="form-control form-control-sm" placeholder="Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau" style="border-radius: 6px;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- BAGIAN 2: DAFTAR BARANG YANG DIPELIHARA -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 10px;">
                <div class="card-header bg-white border-bottom py-2.5 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="m-0 font-weight-bold text-dark">
                            <i class="fas fa-boxes text-success mr-2"></i>2. Daftar Barang yang Dipelihara
                        </h6>
                        <small class="text-muted">Pilih aset BMN dari master inventaris kantor atau tambahkan baris secara fleksibel.</small>
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline-success btn-sm shadow-sm mr-1 font-weight-bold" id="btnOpenPicker" style="border-radius: 6px;">
                            <i class="fas fa-search-plus mr-1"></i>Pilih dari Inventaris Satker
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm shadow-sm" id="btnAddManualRow" style="border-radius: 6px;" title="Tambah Baris Barang Manual">
                            <i class="fas fa-plus mr-1"></i>Tambah Manual
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0" id="tableSelectedItems" style="font-size: 0.85rem;">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th style="width: 40px;">No</th>
                                    <th style="width: 170px;">Jenis Barang</th>
                                    <th style="width: 120px;">Kode Barang</th>
                                    <th>Nama Barang <span class="text-danger">*</span></th>
                                    <th>Merk / Tipe / Spesifikasi</th>
                                    <th style="width: 90px;">NUP <span class="text-danger">*</span></th>
                                    <th style="width: 100px;">Kondisi</th>
                                    <th style="width: 90px;">Tahun</th>
                                    <th style="width: 50px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbodySelectedItems">
                                <!-- Baris item ter-render secara dinamis via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top py-2 d-flex justify-content-between align-items-center">
                    <span class="small text-muted" id="selectedItemsCounter">
                        Total: <strong>0</strong> barang dipilih
                    </span>
                    <button type="button" class="btn btn-outline-danger btn-xs" id="btnClearAllItems" title="Kosongkan Semua Barang">
                        <i class="fas fa-trash-alt mr-1"></i>Hapus Semua Barang
                    </button>
                </div>
            </div>

            <!-- BAGIAN 3: LAMPIRAN FOTO FISIK (OPSIONAL / BISA DIISI BERTAHAP) -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 10px;">
                <div class="card-header bg-white border-bottom py-2.5 d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h6 class="m-0 font-weight-bold text-dark">
                            <i class="fas fa-camera text-info mr-2"></i>3. Lampiran Foto Fisik (Opsional / Bisa Diinput Bertahap Belakangan)
                        </h6>
                        <small class="text-muted">
                            Tersedia opsi <strong>Unggah File</strong> dan <strong>Foto Langsung Kamera</strong> dengan watermark resmi instansi (PU TimeMark).
                        </small>
                    </div>
                    <div class="mt-2 mt-sm-0">
                        <span class="badge badge-light border text-muted px-2 py-1 mr-2" style="font-size: 0.75rem;">
                            Indikator Status: 
                            <span class="text-success font-weight-bold"><i class="fas fa-circle mr-0.5"></i> Hijau (Lengkap)</span> &bull; 
                            <span class="text-warning font-weight-bold"><i class="fas fa-circle mr-0.5"></i> Kuning (Sebagian)</span> &bull; 
                            <span class="text-danger font-weight-bold"><i class="fas fa-circle mr-0.5"></i> Merah (Kosong)</span>
                        </span>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="alert alert-light border border-info shadow-sm d-flex align-items-center mb-3" style="border-radius: 8px;">
                        <i class="fas fa-info-circle fa-2x mr-3 text-info"></i>
                        <div>
                            <div class="font-weight-bold text-dark">Foto Bersifat Fleksibel &amp; Dapat Diinput Belakangan</div>
                            <small class="text-muted">
                                Tidak perlu khawatir jika foto belum lengkap saat ini. Anda dapat menyimpan surat terlebih dahulu, dan menginput / mencicil foto 4 sudut secara bertahap kapan saja melalui tombol <strong class="text-info"><i class="fas fa-camera mr-1"></i>Foto</strong> di daftar arsip surat.
                            </small>
                        </div>
                    </div>
                    <div id="containerPhotoGroups">
                        <!-- Kartu upload 4 foto per kelompok barang di-generate otomatis via JS -->
                        <div class="text-center py-4 text-muted" id="emptyPhotoNotice">
                            <i class="fas fa-images fa-2x mb-2 text-secondary" style="opacity: 0.4;"></i>
                            <div class="font-weight-bold">Belum Ada Barang yang Dipilih</div>
                            <small>Silakan tambahkan barang di langkah 2 terlebih dahulu. Formulir foto 4 sudut akan muncul otomatis sesuai kelompok barang.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BAGIAN 4: TEKS KONSIDERANS & CATATAN (OPSIONAL) -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 10px;">
                <div class="card-header bg-white border-bottom py-2.5" data-toggle="collapse" data-target="#collapseKonsiderans" style="cursor: pointer;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-dark">
                            <i class="fas fa-pen-alt text-secondary mr-2"></i>Teks Konsiderans Alasan Teknis & Catatan (Opsional)
                        </h6>
                        <i class="fas fa-chevron-down text-muted"></i>
                    </div>
                </div>
                <div class="collapse show" id="collapseKonsiderans">
                    <div class="card-body p-3">
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-dark">Pernyataan Poin a (Kondisi & Kelayakan)</label>
                            <input type="text" name="alasan_poin_a" value="<?= esc(old('alasan_poin_a', $pemeliharaan['alasan_poin_a'] ?? 'BMN masih dalam kondisi baik dan masih layak untuk digunakan;')); ?>" class="form-control form-control-sm" style="border-radius: 6px;">
                        </div>
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-dark">Pernyataan Poin b (Alasan Teknis Pemeliharaan 1, 2, 3)</label>
                            <textarea name="alasan_poin_b" rows="4" class="form-control form-control-sm" style="border-radius: 6px; font-family: monospace; font-size: 0.82rem;"><?= esc(old('alasan_poin_b', $pemeliharaan['alasan_poin_b'] ?? "1. Memiliki spesifikasi dan fungsi yang masih sesuai dengan kebutuhan operasional unit kerja/masih digunakan secara rutin dan memiliki peran penting dalam mendukung kelancaran kegiatan operasional unit kerja.\n2. Pemeliharaan rutin memastikan aset tetap dalam kondisi prima (siap pakai) untuk mendukung kelancaran pelayanan publik dan tugas fungsi pemerintah tanpa kendala teknis.\n3. Secara teknis, perawatan berkala jauh lebih murah dan efisien dibanding mendanai perbaikan berat atau penggantian total akibat kerusakan fatal (kerusakan struktural/sistem).")); ?></textarea>
                        </div>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold text-dark">Catatan / Keterangan Tambahan Internal</label>
                            <textarea name="keterangan" rows="2" class="form-control form-control-sm" placeholder="Catatan internal arsip surat (opsional)" style="border-radius: 6px;"><?= esc(old('keterangan', $pemeliharaan['keterangan'] ?? '')); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TOMBOL SIMPAN -->
            <div class="card shadow-sm border-0 mb-5" style="border-radius: 10px;">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <a href="<?= site_url('admin/inventaris/pemeliharaan'); ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-4">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-5 font-weight-bold shadow-sm" id="btnSubmitSurat">
                        <i class="fas fa-save mr-1.5"></i><?= $isEdit ? 'Simpan Perubahan Surat' : 'Simpan & Terbitkan Surat Pemeliharaan'; ?>
                    </button>
                </div>
            </div>

        </form>

    </div>
</section>

<!-- MODAL PICKER: PILIH BARANG DARI INVENTARIS SATKER -->
<div class="modal fade" id="modalBarangPicker" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px;">
            <div class="modal-header bg-primary text-white py-2.5 px-3">
                <h6 class="modal-title font-weight-bold">
                    <i class="fas fa-boxes mr-1.5"></i>Pilih Barang Inventaris Satker
                </h6>
                <button type="button" class="close text-white opacity-8" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                <!-- PILIHAN PERUNTUKAN ASET: KANTOR (DBR) VS MOBILER SEKOLAH VS LAINNYA VS SEMUA -->
                <div class="p-2 mb-3 bg-light rounded border d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
                    <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                        <span class="small font-weight-bold text-dark"><i class="fas fa-layer-group text-primary mr-1"></i>Pilih Peruntukan:</span>
                        <div class="btn-group btn-group-sm btn-group-toggle shadow-sm" data-toggle="buttons" id="pickerPeruntukanToggle">
                            <label class="btn btn-outline-primary active font-weight-bold" id="lblPeruntukanKantor" style="cursor: pointer;">
                                <input type="radio" name="picker_peruntukan" value="kantor" checked autocomplete="off">
                                <i class="fas fa-building mr-1"></i>Aset Kantor (DBR)
                            </label>
                            <label class="btn btn-outline-warning text-dark font-weight-bold" id="lblPeruntukanMobiler" style="cursor: pointer;">
                                <input type="radio" name="picker_peruntukan" value="mobiler" autocomplete="off">
                                <i class="fas fa-school mr-1 text-warning"></i>Mobiler Sekolah
                            </label>
                            <label class="btn btn-outline-secondary font-weight-bold" id="lblPeruntukanLainnya" style="cursor: pointer;">
                                <input type="radio" name="picker_peruntukan" value="lainnya" autocomplete="off">
                                <i class="fas fa-archive mr-1"></i>Aset Lainnya
                            </label>
                            <label class="btn btn-outline-dark font-weight-bold" id="lblPeruntukanAll" style="cursor: pointer;">
                                <input type="radio" name="picker_peruntukan" value="all" autocomplete="off">
                                <i class="fas fa-globe mr-1"></i>Semua Barang
                            </label>
                        </div>
                    </div>
                    <div>
                        <span class="badge badge-info px-2.5 py-1.5 font-weight-bold shadow-sm" id="badgeTotalLoaded">
                            <i class="fas fa-spinner fa-spin mr-1"></i>Memuat...
                        </span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6 mb-2 mb-md-0">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                            </div>
                            <input type="text" id="pickerSearchInput" class="form-control" placeholder="Cari nama barang, merk, kode barang, atau NUP...">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" id="pickerBtnClearSearch" title="Hapus kata kunci">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-2 mb-md-0">
                        <select id="pickerKategoriSelect" class="form-control form-control-sm">
                            <option value="">Semua Kategori</option>
                            <option value="MESIN PERALATAN NON TIK">Mesin Peralatan Non TIK</option>
                            <option value="Peralatan & Mesin">Peralatan & Mesin</option>
                            <option value="Gedung & Bangunan">Gedung & Bangunan</option>
                            <option value="Jaringan">Jaringan</option>
                            <option value="Aset Tetap Lainnya">Aset Tetap Lainnya</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-6 text-right">
                        <button type="button" id="pickerBtnSearch" class="btn btn-primary btn-sm shadow-sm font-weight-bold">
                            <i class="fas fa-search mr-1"></i>Cari
                        </button>
                        <button type="button" id="pickerBtnSelectAll" class="btn btn-outline-secondary btn-sm shadow-sm ml-1">
                            Pilih Semua
                        </button>
                    </div>
                </div>

                <div class="table-responsive" style="max-height: 440px;">
                    <table class="table table-sm table-hover table-bordered mb-0" style="font-size: 0.82rem;">
                        <thead class="thead-light text-center sticky-top">
                            <tr>
                                <th style="width: 35px;"><input type="checkbox" id="checkAllPicker" title="Pilih / Batalkan Semua Kelompok"></th>
                                <th style="width: 100px;">Peruntukan</th>
                                <th style="width: 110px;">Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Merk / Tipe / Spesifikasi</th>
                                <th style="width: 140px;">Rentang NUP</th>
                                <th style="width: 95px;">Jumlah Barang</th>
                                <th style="width: 85px;">Rincian</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyPickerResults">
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fas fa-spinner fa-spin mr-1"></i>Memuat kelompok barang inventaris satker...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-3 d-flex justify-content-between">
                <span class="small text-muted" id="pickerSelectedCount">0 barang terpilih</span>
                <div>
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-dismiss="modal">
                        Batal
                    </button>
                    <button type="button" class="btn btn-success btn-sm rounded-pill px-4 font-weight-bold shadow-sm" id="btnInsertSelectedToForm">
                        <i class="fas fa-check-circle mr-1"></i>Tambahkan ke Surat
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL KAMERA BERURUTAN TERPANDU (GUIDED SEQUENTIAL CAMERA WIZARD) -->
<div class="modal fade" id="modalCameraWizard" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header bg-dark text-white py-2.5 px-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <i class="fas fa-camera text-warning mr-2"></i>
                    <div>
                        <h6 class="modal-title font-weight-bold mb-0" id="wizardItemTitle">Pemotretan Langsung Aset</h6>
                        <small class="text-warning font-weight-bold" id="wizardItemSub">Watermark TimeMark Otomatis</small>
                    </div>
                </div>
                <button type="button" class="close text-white opacity-8" id="btnCloseCameraWizard" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- STEP INDICATOR: DEPAN -> ATAS -> BAWAH -> BELAKANG -->
            <div class="d-flex bg-light border-bottom">
                <div class="step-nav-item active" id="stepPill_depan" onclick="switchCameraStep('depan');">
                    <i class="fas fa-cube mr-1"></i> 1. Tampak Depan
                </div>
                <div class="step-nav-item" id="stepPill_atas" onclick="switchCameraStep('atas');">
                    <i class="fas fa-arrow-up mr-1"></i> 2. Tampak Atas
                </div>
                <div class="step-nav-item" id="stepPill_bawah" onclick="switchCameraStep('bawah');">
                    <i class="fas fa-arrow-down mr-1"></i> 3. Tampak Bawah
                </div>
                <div class="step-nav-item" id="stepPill_belakang" onclick="switchCameraStep('belakang');">
                    <i class="fas fa-undo mr-1"></i> 4. Tampak Belakang
                </div>
            </div>

            <div class="modal-body p-3 bg-dark">
                <!-- VIEW 1: LIVE VIEWFINDER -->
                <div id="viewLiveCamera">
                    <div class="camera-viewfinder-wrapper">
                        <video id="cameraVideo" class="camera-viewfinder" autoplay playsinline muted></video>
                        <div class="camera-guide-pill" id="cameraGuideLabel">
                            ARAHKAN KE TAMPAK DEPAN
                        </div>
                        <div class="camera-reticle">
                            <div class="text-right">
                                <span class="badge badge-warning text-dark font-weight-bold px-2 py-1 shadow-sm" style="font-size: 0.72rem;">
                                    <i class="fas fa-compress-arrows-alt mr-1"></i>Frame Foto
                                </span>
                            </div>
                            <div class="text-center text-warning font-weight-bold small" style="text-shadow: 0 1px 3px rgba(0,0,0,0.9);">
                                Posisikan aset di dalam bingkai
                            </div>
                        </div>
                        <div class="camera-gps-pill">
                            <i class="fas fa-map-marker-alt text-warning mr-1"></i><span id="cameraGpsText">Mendeteksi GPS Pekanbaru...</span>
                        </div>
                    </div>

                    <!-- CONTROL BAR KAMERA -->
                    <div class="text-center mt-3 d-flex justify-content-center align-items-center" style="gap: 20px;">
                        <button type="button" class="btn btn-outline-light btn-sm rounded-circle shadow-sm" id="btnToggleCameraFacing" style="width: 44px; height: 44px;" title="Ganti Kamera Depan / Belakang">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <button type="button" class="shutter-btn shadow-lg" id="btnCaptureCurrentAngle" title="Jepret Foto Sudut Ini">
                            <i class="fas fa-camera"></i>
                        </button>
                        <label class="btn btn-outline-light btn-sm rounded-circle shadow-sm mb-0 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; cursor: pointer;" title="Unggah / Buka Kamera Bawaan HP">
                            <i class="fas fa-folder-open"></i>
                            <input type="file" id="fallbackNativeCameraInput" accept="image/*" capture="environment" class="d-none">
                        </label>
                    </div>
                </div>

                <!-- VIEW 2: SNAPSHOT PREVIEW WITH WATERMARK -->
                <div id="viewPreviewWatermark" style="display: none;">
                    <div class="text-center p-2">
                        <div class="position-relative d-inline-block">
                            <img id="imgWatermarkPreview" src="" class="img-fluid rounded shadow" style="max-height: 380px; object-fit: contain; border: 2px solid #28a745;">
                            <span class="badge badge-success px-2.5 py-1.5 font-weight-bold shadow" style="position: absolute; top: 12px; right: 12px; font-size: 0.8rem;">
                                <i class="fas fa-certificate mr-1"></i>Watermark TimeMark Berhasil Diterapkan
                            </span>
                        </div>
                    </div>
                    <div class="text-center mt-3 d-flex justify-content-center" style="gap: 12px;">
                        <button type="button" class="btn btn-outline-light btn-sm rounded-pill px-3" id="btnRetakeCurrentAngle">
                            <i class="fas fa-redo mr-1"></i>Foto Ulang
                        </button>
                        <button type="button" class="btn btn-success btn-sm rounded-pill px-4 font-weight-bold shadow" id="btnAcceptCurrentAngle">
                            <span id="btnAcceptAngleText">Lanjut ke Tampak Atas <i class="fas fa-arrow-right ml-1"></i></span>
                        </button>
                    </div>
                </div>

                <!-- LOADING SPINNER -->
                <div id="cameraLoadingSpinner" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-warning mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                    <h6 class="text-white font-weight-bold" id="cameraLoadingText">Menerapkan Watermark TimeMark Resmi...</h6>
                    <small class="text-muted">Menyematkan stempel waktu, lokasi GPS, identitas aset, dan logo Satker PU</small>
                </div>
            </div>

            <div class="modal-footer bg-dark border-top py-2 px-3 d-flex justify-content-between">
                <span class="small text-muted" id="cameraFooterStatus">Sudut 1 dari 4 (Tampak Depan)</span>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 font-weight-bold" id="btnFinishCameraWizard">
                    <i class="fas fa-check mr-1"></i>Selesai &amp; Terapkan Foto
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script>
// Data items dan fotos awal dari controller PHP
var selectedItems = <?= json_encode($items ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?> || [];
var existingFotos = <?= json_encode($fotos ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?> || [];
var baseUrl = '<?= rtrim(site_url(), "/"); ?>';

// State Kamera Wizard
var activeCameraGroupKey = null;
var activeCameraGroupData = null;
var currentAngleStep = 'depan'; // 'depan', 'atas', 'bawah', 'belakang'
var cameraStream = null;
var cameraFacingMode = 'environment'; // 'environment' (kamera belakang) atau 'user'
var userCoordinates = '0.4851° N, 101.4485° E'; // Default koordinat Pekanbaru
var capturedSessionImages = {}; // key -> { depan: dataUri, atas: dataUri, bawah: dataUri, belakang: dataUri }

var angleOrder = ['depan', 'atas', 'bawah', 'belakang'];
var angleLabels = {
    'depan': 'Tampak Depan',
    'atas': 'Tampak Atas',
    'bawah': 'Tampak Bawah',
    'belakang': 'Tampak Belakang'
};

document.addEventListener('DOMContentLoaded', function() {
    renderItemsTable();
    syncPhotoGroups();

    // Deteksi Geolocation Browser untuk Watermark Real-time
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(pos) {
            var lat = pos.coords.latitude.toFixed(4);
            var lng = pos.coords.longitude.toFixed(4);
            var latDir = lat >= 0 ? '° N' : '° S';
            var lngDir = lng >= 0 ? '° E' : '° W';
            userCoordinates = Math.abs(lat) + latDir + ', ' + Math.abs(lng) + lngDir;
            $('#cameraGpsText').text(userCoordinates + ' (Satker PPS Riau)');
        }, function(err) {
            $('#cameraGpsText').text('0.4851° N, 101.4485° E (Satker PPS Riau)');
        });
    }

    // Otomatis sesuaikan judul jika jenis pemeliharaan berubah
    $('#jenis_pemeliharaan').on('change', function() {
        var val = $(this).val();
        var judulInput = $('#judul_surat');
        if (val === 'TIK') {
            judulInput.val('SURAT KETERANGAN PEMELIHARAAN PERALATAN & MESIN TIK');
        } else if (val === 'NON TIK') {
            judulInput.val('SURAT KETERANGAN PEMELIHARAAN PERALATAN & MESIN NON TIK');
        } else {
            judulInput.val('SURAT KETERANGAN PEMELIHARAAN PERALATAN & MESIN');
        }
    });

    // Buka Modal Picker Barang
    $('#btnOpenPicker').on('click', function() {
        $('#modalBarangPicker').modal('show');
        loadPickerData();
    });

    $('#pickerBtnSearch').on('click', function() {
        loadPickerData();
    });
    $('#pickerSearchInput').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            loadPickerData();
        }
    });

    // Tambah Baris Manual
    $('#btnAddManualRow').on('click', function() {
        selectedItems.push({
            inventaris_id: null,
            jenis_barang: 'MESIN PERALATAN NON TIK',
            kode_barang: '',
            nama_barang: '',
            merk_tipe: '',
            nup: '',
            kondisi: 'Baik',
            tahun_perolehan: new Date().getFullYear().toString()
        });
        renderItemsTable();
        syncPhotoGroups();
    });

    // Hapus Semua Barang
    $('#btnClearAllItems').on('click', function() {
        if (selectedItems.length > 0 && confirm('Kosongkan seluruh barang yang dipilih?')) {
            selectedItems = [];
            renderItemsTable();
            syncPhotoGroups();
        }
    });

    // Submit Validation
    $('#formPemeliharaan').on('submit', function(e) {
        if (selectedItems.length === 0) {
            e.preventDefault();
            alert('Pilih minimal satu barang inventaris untuk dimasukkan ke dalam surat pemeliharaan.');
            return false;
        }

        for (var i = 0; i < selectedItems.length; i++) {
            if (!selectedItems[i].nama_barang || !selectedItems[i].nup) {
                e.preventDefault();
                alert('Nama barang dan NUP pada baris #' + (i + 1) + ' tidak boleh kosong.');
                return false;
            }
        }

        $('#itemsJsonInput').val(JSON.stringify(selectedItems));
        $('#btnSubmitSurat').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...');
        return true;
    });

    // Kamera Wizard Handlers
    $('#btnCloseCameraWizard').on('click', function() {
        stopCameraStream();
        $('#modalCameraWizard').modal('hide');
    });

    $('#btnToggleCameraFacing').on('click', function() {
        cameraFacingMode = (cameraFacingMode === 'environment') ? 'user' : 'environment';
        startCameraStream();
    });

    $('#btnCaptureCurrentAngle').on('click', function() {
        captureFrameAndApplyWatermark();
    });

    $('#btnRetakeCurrentAngle').on('click', function() {
        $('#viewPreviewWatermark').hide();
        $('#viewLiveCamera').show();
        startCameraStream();
    });

    $('#btnAcceptCurrentAngle').on('click', function() {
        advanceToNextAngle();
    });

    $('#btnFinishCameraWizard').on('click', function() {
        stopCameraStream();
        $('#modalCameraWizard').modal('hide');
        applyCapturedSessionToForm(activeCameraGroupKey);
    });

    // Fallback Native Camera Input (untuk HP yang prefer kamera sistem)
    $('#fallbackNativeCameraInput').on('change', function(e) {
        var file = this.files[0];
        if (!file) return;

        var reader = new FileReader();
        reader.onload = function(evt) {
            processImageWatermarkAjax(evt.target.result);
        };
        reader.readAsDataURL(file);
    });
});

// Render Tabel Item Terpilih
function renderItemsTable() {
    var tbody = $('#tbodySelectedItems');
    tbody.empty();

    if (selectedItems.length === 0) {
        tbody.append('<tr><td colspan="9" class="text-center py-4 text-muted"><i class="fas fa-boxes mr-1"></i>Belum ada barang dipilih. Klik tombol <strong>Pilih dari Inventaris Satker</strong> di atas.</td></tr>');
        $('#selectedItemsCounter').html('Total: <strong>0</strong> barang dipilih');
        return;
    }

    selectedItems.forEach(function(it, idx) {
        var tr = $('<tr></tr>');
        tr.append('<td class="text-center text-muted font-weight-bold">' + (idx + 1) + '</td>');
        tr.append('<td><input type="text" class="form-control form-control-sm item-field" data-idx="' + idx + '" data-field="jenis_barang" value="' + escapeHtml(it.jenis_barang || 'MESIN PERALATAN NON TIK') + '"></td>');
        tr.append('<td><input type="text" class="form-control form-control-sm item-field" data-idx="' + idx + '" data-field="kode_barang" value="' + escapeHtml(it.kode_barang || '') + '" placeholder="Kode"></td>');
        tr.append('<td><input type="text" class="form-control form-control-sm font-weight-bold text-dark item-field" data-idx="' + idx + '" data-field="nama_barang" value="' + escapeHtml(it.nama_barang || '') + '" placeholder="Nama Barang" required></td>');
        tr.append('<td><input type="text" class="form-control form-control-sm item-field" data-idx="' + idx + '" data-field="merk_tipe" value="' + escapeHtml(it.merk_tipe || '') + '" placeholder="Merk / Tipe"></td>');
        tr.append('<td><input type="text" class="form-control form-control-sm text-center item-field" data-idx="' + idx + '" data-field="nup" value="' + escapeHtml(it.nup || '') + '" placeholder="NUP" required></td>');

        var kondisiOpt = '<select class="form-control form-control-sm item-field" data-idx="' + idx + '" data-field="kondisi">' +
            '<option value="Baik"' + (it.kondisi === 'Baik' ? ' selected' : '') + '>Baik</option>' +
            '<option value="Rusak Ringan"' + (it.kondisi === 'Rusak Ringan' ? ' selected' : '') + '>Rusak Ringan</option>' +
            '<option value="Rusak Berat"' + (it.kondisi === 'Rusak Berat' ? ' selected' : '') + '>Rusak Berat</option>' +
            '</select>';
        tr.append('<td>' + kondisiOpt + '</td>');
        tr.append('<td><input type="text" class="form-control form-control-sm text-center item-field" data-idx="' + idx + '" data-field="tahun_perolehan" value="' + escapeHtml(it.tahun_perolehan || '') + '" placeholder="Tahun"></td>');
        tr.append('<td class="text-center"><button type="button" class="btn btn-outline-danger btn-xs btn-remove-item" data-idx="' + idx + '" title="Hapus baris ini"><i class="fas fa-times"></i></button></td>');

        tbody.append(tr);
    });

    $('#selectedItemsCounter').html('Total: <strong>' + selectedItems.length + '</strong> barang dipilih');

    $('.item-field').off('change keyup').on('change keyup', function() {
        var idx = $(this).data('idx');
        var field = $(this).data('field');
        selectedItems[idx][field] = $(this).val();

        if (field === 'nama_barang' || field === 'merk_tipe' || field === 'nup' || field === 'kode_barang') {
            syncPhotoGroups();
        }
    });

    $('.btn-remove-item').off('click').on('click', function() {
        var idx = $(this).data('idx');
        selectedItems.splice(idx, 1);
        renderItemsTable();
        syncPhotoGroups();
    });
}

// Sinkronisasi Kelompok Foto Lampiran & Indikator Warna Status (Hijau, Kuning, Merah)
function syncPhotoGroups() {
    var container = $('#containerPhotoGroups');

    if (selectedItems.length === 0) {
        container.html('<div class="text-center py-4 text-muted"><i class="fas fa-images fa-2x mb-2 text-secondary" style="opacity: 0.4;"></i><div class="font-weight-bold">Belum Ada Barang yang Dipilih</div><small>Silakan tambahkan barang di langkah 2 terlebih dahulu.</small></div>');
        return;
    }

    var groups = {};
    selectedItems.forEach(function(it) {
        var n = (it.nama_barang || '').trim();
        var m = (it.merk_tipe || '').trim();
        var k = (it.kode_barang || '').trim();
        if (!n) return;

        var key = md5(k + '::' + n + '::' + m);
        if (!groups[key]) {
            groups[key] = {
                key: key,
                kode_barang: k,
                nama_barang: n,
                merk_tipe: m,
                nups: []
            };
        }
        if (it.nup) {
            groups[key].nups.push(it.nup);
        }
    });

    var oldFotosByKey = {};
    existingFotos.forEach(function(f) {
        if (f.kelompok_key) {
            oldFotosByKey[f.kelompok_key] = f;
        }
    });

    container.empty();

    var groupKeys = Object.keys(groups);
    if (groupKeys.length === 0) {
        container.html('<div class="text-center py-4 text-muted"><small>Harap isi nama barang pada tabel di atas untuk memunculkan zona upload foto.</small></div>');
        return;
    }

    groupKeys.forEach(function(key, gIdx) {
        var g = groups[key];
        var nupSummary = formatNupRange(g.nups);
        var oldF = oldFotosByKey[key] || {};
        var sessionF = capturedSessionImages[key] || {};

        // Hitung kelengkapan foto saat ini (4 posisi: depan, atas, bawah, belakang)
        var filledCount = 0;
        ['depan', 'atas', 'bawah', 'belakang'].forEach(function(pos) {
            if (sessionF[pos] || oldF['foto_' + pos]) {
                filledCount++;
            }
        });

        // Tentukan status dan warna indikator
        var statusClass = 'status-kosong';
        var badgeColor = 'danger'; // Merah
        var iconColor = 'text-danger';
        var statusLabel = 'Belum Ada Foto (0/4)';

        if (filledCount === 4) {
            statusClass = 'status-lengkap';
            badgeColor = 'success'; // Hijau
            iconColor = 'text-success';
            statusLabel = 'Foto Lengkap (4/4)';
        } else if (filledCount > 0) {
            statusClass = 'status-sebagian';
            badgeColor = 'warning text-dark'; // Kuning
            iconColor = 'text-warning';
            statusLabel = 'Belum Lengkap (' + filledCount + '/4)';
        }

        var cardHtml = '<div class="group-card mb-4 p-3 shadow-sm ' + statusClass + '" id="cardGroup_' + key + '">' +
            '<div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom flex-wrap" style="gap: 8px;">' +
                '<div class="d-flex align-items-center flex-wrap" style="gap: 8px;">' +
                    '<span class="badge badge-primary" style="font-size: 0.85rem;">#' + (gIdx + 1) + '</span>' +
                    '<i class="fas fa-camera ' + iconColor + '" id="cameraIcon_' + key + '" style="font-size: 1.25rem;"></i>' +
                    '<strong class="text-dark" style="font-size: 0.98rem;">' + escapeHtml(g.nama_barang) + '</strong>' +
                    (g.merk_tipe ? ' <span class="text-muted small">(' + escapeHtml(g.merk_tipe) + ')</span>' : '') +
                    '<span class="badge badge-light border text-dark font-weight-bold px-2 py-1 ml-1" style="font-size: 0.78rem;">' +
                        'NUP: <strong>' + escapeHtml(nupSummary || '-') + '</strong>' +
                    '</span>' +
                '</div>' +
                '<div class="d-flex align-items-center flex-wrap" style="gap: 8px;">' +
                    '<span class="badge badge-' + badgeColor + ' px-2.5 py-1.5 font-weight-bold" id="statusBadge_' + key + '" style="font-size: 0.8rem;">' +
                        '<i class="fas fa-circle mr-1" style="font-size: 0.6rem;"></i>' + statusLabel +
                    '</span>' +
                    '<button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-sm btn-open-camera-wizard" data-key="' + key + '" data-nama="' + escapeHtml(g.nama_barang) + '" data-merk="' + escapeHtml(g.merk_tipe) + '" data-nup="' + escapeHtml(nupSummary) + '" style="border-radius: 6px;">' +
                        '<i class="fas fa-camera mr-1 text-warning"></i> Foto Langsung Berurutan' +
                    '</button>' +
                '</div>' +
            '</div>' +
            '<div class="row">' +
                renderPhotoBox(key, 'depan', 'Tampak Depan', oldF.foto_depan, sessionF.depan) +
                renderPhotoBox(key, 'atas', 'Tampak Atas', oldF.foto_atas, sessionF.atas) +
                renderPhotoBox(key, 'bawah', 'Tampak Bawah', oldF.foto_bawah, sessionF.bawah) +
                renderPhotoBox(key, 'belakang', 'Tampak Belakang', oldF.foto_belakang, sessionF.belakang) +
            '</div>' +
        '</div>';

        container.append(cardHtml);
    });

    attachPhotoUploadEvents();
}

// Render Satu Kotak Slot Foto
function renderPhotoBox(key, pos, label, existingUrl, base64Url) {
    var inputName = 'foto_' + key + '_' + pos;
    var base64InputName = 'foto_base64_' + key + '_' + pos;
    var previewId = 'preview_' + key + '_' + pos;
    var dropzoneId = 'dropzone_' + key + '_' + pos;
    var hasPhoto = (base64Url || existingUrl) ? true : false;
    var currentImgSrc = base64Url ? base64Url : (existingUrl ? (baseUrl + '/' + existingUrl.replace(/^\//, '')) : '');
    var isWatermarked = base64Url ? true : false;

    var html = '<div class="col-lg-3 col-sm-6 mb-3">' +
        '<div class="d-flex justify-content-between align-items-center mb-1.5">' +
            '<span class="badge badge-secondary badge-angle">' + label + '</span>' +
            '<div class="d-flex align-items-center" style="gap: 6px;">' +
                '<button type="button" class="btn btn-outline-info btn-xs btn-snap-single-angle" data-key="' + key + '" data-pos="' + pos + '" title="Jepret foto langsung untuk sudut ini">' +
                    '<i class="fas fa-camera"></i>' +
                '</button>' +
                (hasPhoto ? '<label class="small text-danger mb-0 font-weight-bold" style="cursor: pointer;"><input type="checkbox" name="hapus_foto_' + pos + '_' + key + '" value="1" onchange="handleDeletePhotoBox(\'' + key + '\', \'' + pos + '\')"> Hapus</label>' : '') +
            '</div>' +
        '</div>' +
        '<div class="photo-dropzone ' + (hasPhoto ? 'border-success' : '') + '" id="' + dropzoneId + '">' +
            '<input type="file" id="' + inputName + '" name="' + inputName + '" accept="image/jpeg,image/png,image/webp,image/heic,image/heif,.heic,.HEIC" class="d-none photo-file-input" data-key="' + key + '" data-pos="' + pos + '">' +
            '<input type="hidden" id="' + base64InputName + '" name="' + base64InputName + '" value="' + (base64Url ? escapeHtml(base64Url) : '') + '">' +
            '<div id="' + previewId + '" class="w-100">' +
                (hasPhoto ?
                    '<div class="position-relative">' +
                        '<img src="' + currentImgSrc + '" class="photo-preview-img mb-1">' +
                        (isWatermarked ? '<span class="badge badge-warning text-dark font-weight-bold shadow-sm" style="position: absolute; bottom: 4px; right: 4px; font-size: 0.65rem;"><i class="fas fa-certificate mr-0.5"></i>TimeMark</span>' : '') +
                    '</div>' +
                    '<div class="text-success small font-weight-bold"><i class="fas fa-check-circle mr-1"></i>Foto Tersedia</div>' +
                    '<div class="mt-1">' +
                        '<button type="button" class="btn btn-outline-primary btn-xs mr-1" onclick="document.getElementById(\'' + inputName + '\').click();"><i class="fas fa-folder-open mr-0.5"></i>Ganti File</button>' +
                    '</div>'
                    :
                    '<div onclick="document.getElementById(\'' + inputName + '\').click();" style="cursor: pointer;">' +
                        '<i class="fas fa-cloud-upload-alt fa-2x text-muted mb-1"></i>' +
                        '<div class="small font-weight-bold text-dark">Unggah File</div>' +
                        '<span class="text-muted" style="font-size: 0.72rem;">Drag file atau klik di sini</span>' +
                    '</div>'
                ) +
            '</div>' +
        '</div>' +
    '</div>';

    return html;
}

// Update status kelengkapan foto kartu secara dinamis
function refreshGroupCardStatus(key) {
    var sessionF = capturedSessionImages[key] || {};
    var card = $('#cardGroup_' + key);
    var icon = $('#cameraIcon_' + key);
    var badge = $('#statusBadge_' + key);

    var filled = 0;
    ['depan', 'atas', 'bawah', 'belakang'].forEach(function(pos) {
        var base64Val = $('#foto_base64_' + key + '_' + pos).val();
        var fileInput = document.getElementById('foto_' + key + '_' + pos);
        var hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
        var hasBase64 = base64Val && base64Val.startsWith('data:image');
        var previewImg = $('#preview_' + key + '_' + pos).find('img').length > 0;

        if (hasFile || hasBase64 || previewImg) {
            filled++;
        }
    });

    card.removeClass('status-lengkap status-sebagian status-kosong');
    icon.removeClass('text-success text-warning text-danger');
    badge.removeClass('badge-success badge-warning badge-danger text-dark');

    if (filled === 4) {
        card.addClass('status-lengkap');
        icon.addClass('text-success');
        badge.addClass('badge-success').html('<i class="fas fa-check-circle mr-1"></i>Foto Lengkap (4/4)');
    } else if (filled > 0) {
        card.addClass('status-sebagian');
        icon.addClass('text-warning');
        badge.addClass('badge-warning text-dark').html('<i class="fas fa-exclamation-circle mr-1"></i>Belum Lengkap (' + filled + '/4)');
    } else {
        card.addClass('status-kosong');
        icon.addClass('text-danger');
        badge.addClass('badge-danger').html('<i class="fas fa-times-circle mr-1"></i>Belum Ada Foto (0/4)');
    }
}

function handleDeletePhotoBox(key, pos) {
    if (!capturedSessionImages[key]) capturedSessionImages[key] = {};
    capturedSessionImages[key][pos] = null;
    $('#foto_base64_' + key + '_' + pos).val('');
    $('#preview_' + key + '_' + pos).html(
        '<div onclick="document.getElementById(\'foto_' + key + '_' + pos + '\').click();" style="cursor: pointer;">' +
            '<i class="fas fa-cloud-upload-alt fa-2x text-muted mb-1"></i>' +
            '<div class="small font-weight-bold text-dark">Unggah File</div>' +
            '<span class="text-muted" style="font-size: 0.72rem;">Drag file atau klik di sini</span>' +
        '</div>'
    );
    refreshGroupCardStatus(key);
}

// Event handler Drag-and-drop & File Selection Preview
function attachPhotoUploadEvents() {
    $('.photo-file-input').off('change').on('change', function(e) {
        var file = this.files[0];
        if (!file) return;

        var key = $(this).data('key');
        var pos = $(this).data('pos');
        var previewContainer = $('#preview_' + key + '_' + pos);

        var reader = new FileReader();
        reader.onload = function(evt) {
            previewContainer.html(
                '<img src="' + evt.target.result + '" class="photo-preview-img mb-1">' +
                '<div class="text-primary small font-weight-bold"><i class="fas fa-image mr-1"></i>' + escapeHtml(file.name.substring(0, 16)) + '</div>' +
                '<small class="text-muted" style="font-size: 0.72rem;">Klik untuk mengubah</small>'
            );
            refreshGroupCardStatus(key);
        };
        reader.readAsDataURL(file);
    });

    // Tombol Buka Kamera Wizard Berurutan (Depan -> Atas -> Bawah -> Belakang)
    $('.btn-open-camera-wizard').off('click').on('click', function() {
        var key = $(this).data('key');
        var nama = $(this).data('nama');
        var merk = $(this).data('merk');
        var nup = $(this).data('nup');

        activeCameraGroupKey = key;
        activeCameraGroupData = { key: key, nama: nama, merk: merk, nup: nup };

        $('#wizardItemTitle').text(nama + (merk ? ' (' + merk + ')' : ''));
        $('#wizardItemSub').text('NUP: ' + (nup || '-') + ' | TimeMark Watermark Otomatis');

        if (!capturedSessionImages[key]) {
            capturedSessionImages[key] = {};
        }

        switchCameraStep('depan');
        $('#modalCameraWizard').modal('show');
        startCameraStream();
    });

    // Tombol Foto Langsung Khusus 1 Sudut
    $('.btn-snap-single-angle').off('click').on('click', function(e) {
        e.stopPropagation();
        var key = $(this).data('key');
        var pos = $(this).data('pos');

        // Cari data barang grup
        var item = selectedItems.find(function(it) {
            var k = (it.kode_barang || '').trim();
            var n = (it.nama_barang || '').trim();
            var m = (it.merk_tipe || '').trim();
            return md5(k + '::' + n + '::' + m) === key;
        });

        activeCameraGroupKey = key;
        activeCameraGroupData = {
            key: key,
            nama: item ? item.nama_barang : '',
            merk: item ? item.merk_tipe : '',
            nup: item ? item.nup : ''
        };

        if (!capturedSessionImages[key]) {
            capturedSessionImages[key] = {};
        }

        switchCameraStep(pos);
        $('#modalCameraWizard').modal('show');
        startCameraStream();
    });
}

// Beralih Sudut Kamera (depan, atas, bawah, belakang)
function switchCameraStep(step) {
    currentAngleStep = step;

    $('.step-nav-item').removeClass('active');
    $('#stepPill_' + step).addClass('active');

    var stepIdx = angleOrder.indexOf(step) + 1;
    $('#cameraGuideLabel').text('ARAHKAN KE ' + angleLabels[step].toUpperCase());
    $('#cameraFooterStatus').text('Sudut ' + stepIdx + ' dari 4 (' + angleLabels[step] + ')');

    // Cek jika sudut ini sudah ada foto hasil jepretan
    if (capturedSessionImages[activeCameraGroupKey] && capturedSessionImages[activeCameraGroupKey][step]) {
        showWatermarkPreview(capturedSessionImages[activeCameraGroupKey][step]);
    } else {
        $('#viewPreviewWatermark').hide();
        $('#viewLiveCamera').show();
        startCameraStream();
    }
}

// Mulai Video Stream Kamera Browser
function startCameraStream() {
    stopCameraStream();

    var video = document.getElementById('cameraVideo');
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        $('#cameraGuideLabel').text('Kamera Browser Tidak Didukung. Gunakan Tombol Kamera HP di Bawah.');
        return;
    }

    navigator.mediaDevices.getUserMedia({
        video: {
            facingMode: cameraFacingMode,
            width: { ideal: 1280 },
            height: { ideal: 720 }
        },
        audio: false
    }).then(function(stream) {
        cameraStream = stream;
        video.srcObject = stream;
        video.play();
    }).catch(function(err) {
        console.warn('Gagal membuka kamera:', err);
        $('#cameraGuideLabel').text('Kamera Tidak Dapat Dibuka. Gunakan Kamera Bawaan HP.');
    });
}

function stopCameraStream() {
    if (cameraStream) {
        cameraStream.getTracks().forEach(function(track) {
            track.stop();
        });
        cameraStream = null;
    }
}

// Jepret Frame Video & Kirim ke Server untuk Watermark TimeMark
function captureFrameAndApplyWatermark() {
    var video = document.getElementById('cameraVideo');
    if (!video || !video.videoWidth) {
        alert('Kamera belum siap. Pastikan kamera telah aktif.');
        return;
    }

    var canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    var ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

    var rawBase64 = canvas.toDataURL('image/jpeg', 0.92);
    stopCameraStream();
    processImageWatermarkAjax(rawBase64);
}

// Kirim ke Endpoint Watermark Resmi TimeMark
function processImageWatermarkAjax(rawBase64) {
    $('#viewLiveCamera').hide();
    $('#viewPreviewWatermark').hide();
    $('#cameraLoadingSpinner').show();
    $('#cameraLoadingText').text('Menerapkan Watermark TimeMark untuk ' + angleLabels[currentAngleStep] + '...');

    var formData = new FormData();
    formData.append('image_data', rawBase64);
    formData.append('nama_barang', activeCameraGroupData.nama || '');
    formData.append('merk_tipe', activeCameraGroupData.merk || '');
    formData.append('nup', activeCameraGroupData.nup || '');
    formData.append('sudut', angleLabels[currentAngleStep]);
    formData.append('coordinates', userCoordinates);

    $.ajax({
        url: baseUrl + '/admin/inventaris/pemeliharaan/apply-watermark',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(res) {
            $('#cameraLoadingSpinner').hide();
            if (res.success && res.data_uri) {
                // Simpan ke sesi lokal
                if (!capturedSessionImages[activeCameraGroupKey]) {
                    capturedSessionImages[activeCameraGroupKey] = {};
                }
                capturedSessionImages[activeCameraGroupKey][currentAngleStep] = res.data_uri;

                // Tandai step completed
                $('#stepPill_' + currentAngleStep).addClass('completed');

                showWatermarkPreview(res.data_uri);
            } else {
                alert('Gagal memproses watermark: ' + (res.error || 'Terjadi kesalahan sistem.'));
                $('#viewLiveCamera').show();
                startCameraStream();
            }
        },
        error: function(xhr) {
            $('#cameraLoadingSpinner').hide();
            alert('Koneksi ke server gagal saat memproses watermark.');
            $('#viewLiveCamera').show();
            startCameraStream();
        }
    });
}

function showWatermarkPreview(dataUri) {
    $('#viewLiveCamera').hide();
    $('#viewPreviewWatermark').show();
    $('#imgWatermarkPreview').attr('src', dataUri);

    var currentIdx = angleOrder.indexOf(currentAngleStep);
    if (currentIdx < angleOrder.length - 1) {
        var nextAngle = angleOrder[currentIdx + 1];
        $('#btnAcceptAngleText').html('Lanjut ke ' + angleLabels[nextAngle] + ' <i class="fas fa-arrow-right ml-1"></i>');
    } else {
        $('#btnAcceptAngleText').html('<i class="fas fa-check-circle mr-1"></i> Selesai Seluruh Sudut');
    }
}

// Lanjut ke Sudut Pemotretan Berikutnya (Depan -> Atas -> Bawah -> Belakang)
function advanceToNextAngle() {
    var currentIdx = angleOrder.indexOf(currentAngleStep);
    if (currentIdx < angleOrder.length - 1) {
        var nextAngle = angleOrder[currentIdx + 1];
        switchCameraStep(nextAngle);
    } else {
        // Sudah selesai sudut ke-4
        stopCameraStream();
        $('#modalCameraWizard').modal('hide');
        applyCapturedSessionToForm(activeCameraGroupKey);
    }
}

// Terapkan hasil foto jepretan kamera ke form utama
function applyCapturedSessionToForm(key) {
    var sessionF = capturedSessionImages[key];
    if (!sessionF) return;

    ['depan', 'atas', 'bawah', 'belakang'].forEach(function(pos) {
        if (sessionF[pos]) {
            var dataUri = sessionF[pos];
            $('#foto_base64_' + key + '_' + pos).val(dataUri);

            var previewContainer = $('#preview_' + key + '_' + pos);
            previewContainer.html(
                '<div class="position-relative">' +
                    '<img src="' + dataUri + '" class="photo-preview-img mb-1">' +
                    '<span class="badge badge-warning text-dark font-weight-bold shadow-sm" style="position: absolute; bottom: 4px; right: 4px; font-size: 0.65rem;"><i class="fas fa-certificate mr-0.5"></i>TimeMark</span>' +
                '</div>' +
                '<div class="text-success small font-weight-bold"><i class="fas fa-check-circle mr-1"></i>Foto Tersedia</div>' +
                '<div class="mt-1">' +
                    '<button type="button" class="btn btn-outline-info btn-xs btn-snap-single-angle" data-key="' + key + '" data-pos="' + pos + '"><i class="fas fa-camera mr-0.5"></i>Foto Ulang</button>' +
                '</div>'
            );
        }
    });

    refreshGroupCardStatus(key);
}

// Variable global untuk menampung grup barang yang sedang dimuat di picker
var pickerLoadedGroups = [];

// Load data picker dari server via AJAX (Terkelompok Cerdas)
function loadPickerData() {
    var q = $('#pickerSearchInput').val();
    var kat = $('#pickerKategoriSelect').val();
    var peruntukan = $('input[name="picker_peruntukan"]:checked').val() || 'kantor';
    var tbody = $('#tbodyPickerResults');
    var badge = $('#badgeTotalLoaded');

    tbody.html('<tr><td colspan="8" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin mr-1"></i>Memuat kelompok barang inventaris satker...</td></tr>');
    badge.html('<i class="fas fa-spinner fa-spin mr-1"></i>Memuat...');

    $.ajax({
        url: baseUrl + '/admin/inventaris/pemeliharaan/ajax-search-barang',
        type: 'GET',
        data: { q: q, kategori: kat, peruntukan: peruntukan, limit: 2500 },
        dataType: 'json',
        success: function(res) {
            tbody.empty();
            pickerLoadedGroups = (res && res.data) ? res.data : [];

            if (!res.success || !pickerLoadedGroups || pickerLoadedGroups.length === 0) {
                tbody.html('<tr><td colspan="8" class="text-center py-4 text-muted"><i class="fas fa-info-circle mr-1"></i>Tidak ada kelompok barang yang cocok dengan filter atau kata kunci ini.</td></tr>');
                badge.removeClass('badge-info badge-danger').addClass('badge-secondary').text('0 Kelompok Ditemukan');
                updatePickerCounter();
                return;
            }

            var totalGroups = pickerLoadedGroups.length;
            var totalItems = res.total_items || 0;
            var peruntukanLabel = peruntukan === 'kantor' ? 'Aset Kantor' : (peruntukan === 'mobiler' ? 'Mobiler Sekolah' : (peruntukan === 'lainnya' ? 'Aset Lainnya' : 'Semua'));
            badge.removeClass('badge-secondary badge-danger').addClass('badge-info')
                 .html('<i class="fas fa-check-circle mr-1"></i>' + totalItems + ' Unit (' + totalGroups + ' Kelompok - ' + peruntukanLabel + ')');

            pickerLoadedGroups.forEach(function(g) {
                // Periksa apakah item-item dalam kelompok ini sudah ada di form
                var preSelectedCount = (g.items || []).filter(function(it) {
                    return selectedItems.some(function(s) {
                        return s.inventaris_id && s.inventaris_id === it.id;
                    });
                }).length;
                var totalGItems = (g.items || []).length;
                var isAllPreSelected = totalGItems > 0 && preSelectedCount === totalGItems;
                var isSomePreSelected = preSelectedCount > 0 && preSelectedCount < totalGItems;

                // Badge peruntukan
                var pBadge = '<span class="badge badge-primary px-1.5 py-0.5" style="font-size: 0.72rem;"><i class="fas fa-building mr-0.5"></i>Kantor</span>';
                if (g.peruntukan === 'mobiler') {
                    pBadge = '<span class="badge badge-warning text-dark px-1.5 py-0.5 font-weight-bold" style="font-size: 0.72rem;"><i class="fas fa-school mr-0.5"></i>Mobiler</span>';
                } else if (g.peruntukan === 'lainnya') {
                    pBadge = '<span class="badge badge-secondary px-1.5 py-0.5" style="font-size: 0.72rem;"><i class="fas fa-archive mr-0.5"></i>Lainnya</span>';
                }

                // Baris Utama Kelompok (6 kriteria pengelompokan yang diminta user)
                var tr = $('<tr class="picker-group-row" data-group-key="' + g.group_key + '" style="cursor: pointer;"></tr>');
                
                // 1. Checkbox Kelompok
                tr.append('<td class="text-center align-middle" style="width: 35px;"><input type="checkbox" class="picker-group-checkbox" data-group-key="' + g.group_key + '"' + (isAllPreSelected ? ' checked' : '') + '></td>');
                // 2. Peruntukan
                tr.append('<td class="text-center align-middle" style="width: 100px;">' + pBadge + '</td>');
                // 3. Kode Barang
                tr.append('<td class="align-middle font-weight-bold text-muted small" style="width: 110px;">' + escapeHtml(g.kode_barang || '-') + '</td>');
                // 4. Nama Barang
                tr.append('<td class="align-middle font-weight-bold text-dark">' + escapeHtml(g.nama_barang) + '</td>');
                // 5. Merk / Tipe / Spesifikasi
                tr.append('<td class="align-middle text-secondary small">' + escapeHtml(g.merk_tipe || '-') + '</td>');
                // 6. Rentang NUP
                tr.append('<td class="align-middle text-center" style="width: 140px;"><span class="badge badge-light border text-primary font-weight-bold px-2 py-1" style="font-size: 0.78rem;">' + escapeHtml(g.rentang_nup || '-') + '</span></td>');
                // 7. Jumlah Barang
                tr.append('<td class="align-middle text-center" style="width: 95px;"><span class="badge badge-info px-2 py-1 font-weight-bold shadow-sm" style="font-size: 0.78rem;">' + g.jumlah_barang + ' Unit</span></td>');
                // 8. Rincian / Detail NUP Button
                tr.append('<td class="align-middle text-center" style="width: 85px;"><button type="button" class="btn btn-outline-info btn-xs rounded-pill btn-toggle-detail px-2" data-group-key="' + g.group_key + '" title="Lihat Rincian NUP"><i class="fas fa-list-ol mr-1"></i>NUP <i class="fas fa-chevron-down arrow-icon ml-0.5" style="font-size: 0.65rem;"></i></button></td>');

                tbody.append(tr);

                // Baris Sub-Detail Rincian NUP (Accordion)
                var detailTr = $('<tr id="detailRow_' + g.group_key + '" class="group-detail-row bg-light" style="display: none;"></tr>');
                var detailTd = $('<td colspan="8" class="p-2.5 border-top-0"></td>');

                var detailWrapper = $('<div class="card card-body p-2 border shadow-sm mb-0 bg-white" style="border-radius: 8px;"></div>');
                var detailHeader = $(
                    '<div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">' +
                        '<div class="small font-weight-bold text-muted">' +
                            '<i class="fas fa-barcode text-primary mr-1"></i>Rincian NUP: <span class="text-dark font-weight-bold">' + escapeHtml(g.nama_barang) + '</span>' +
                            ' <span class="text-secondary">(' + g.jumlah_barang + ' Unit)</span>' +
                        '</div>' +
                        '<div class="btn-group btn-group-xs">' +
                            '<button type="button" class="btn btn-outline-primary btn-xs py-0 px-2 btn-check-all-group-nups" data-group-key="' + g.group_key + '"><i class="fas fa-check-square mr-1"></i>Pilih Semua NUP</button>' +
                            '<button type="button" class="btn btn-outline-secondary btn-xs py-0 px-2 btn-uncheck-all-group-nups" data-group-key="' + g.group_key + '"><i class="fas fa-square mr-1"></i>Batal Pilih</button>' +
                        '</div>' +
                    '</div>'
                );
                detailWrapper.append(detailHeader);

                var chipsContainer = $('<div class="d-flex flex-wrap" style="gap: 6px; max-height: 180px; overflow-y: auto;"></div>');
                (g.items || []).forEach(function(it) {
                    var isItemChecked = isAllPreSelected || selectedItems.some(function(s) {
                        return s.inventaris_id && s.inventaris_id === it.id;
                    });

                    var chip = $(
                        '<label class="btn btn-sm btn-outline-secondary mb-0 py-0.5 px-2 d-inline-flex align-items-center picker-nup-chip' + (isItemChecked ? ' active text-primary font-weight-bold' : '') + '" style="font-size: 0.74rem; cursor: pointer; border-radius: 6px;">' +
                            '<input type="checkbox" class="picker-item-checkbox mr-1" id="cb_item_' + it.id + '" data-group-key="' + g.group_key + '" data-item-id="' + it.id + '"' + (isItemChecked ? ' checked' : '') + '>' +
                            '<span>NUP <strong>' + escapeHtml(it.nup || '-') + '</strong> <small class="text-muted ml-0.5">(' + escapeHtml(it.kondisi || 'Baik') + ')</small></span>' +
                        '</label>'
                    );
                    chipsContainer.append(chip);
                });

                detailWrapper.append(chipsContainer);
                detailTd.append(detailWrapper);
                detailTr.append(detailTd);
                tbody.append(detailTr);

                // Atur status indeterminate jika sebagian item sudah terpilih
                if (isSomePreSelected) {
                    tr.find('.picker-group-checkbox').prop('indeterminate', true);
                }
            });

            updatePickerCounter();
        },
        error: function(xhr) {
            var errMsg = 'Gagal memuat data dari server.';
            if (xhr && xhr.responseJSON && xhr.responseJSON.error) {
                errMsg += ' (' + xhr.responseJSON.error + ')';
            }
            tbody.html('<tr><td colspan="8" class="text-center py-4 text-danger"><i class="fas fa-exclamation-circle mr-1"></i>' + escapeHtml(errMsg) + '</td></tr>');
            badge.removeClass('badge-info').addClass('badge-danger').text('Gagal memuat');
        }
    });
}

// Update counter di footer modal
function updatePickerCounter() {
    var checkedItems = $('.picker-item-checkbox:checked').length;
    var checkedGroupKeys = new Set();
    $('.picker-item-checkbox:checked').each(function() {
        checkedGroupKeys.add($(this).data('group-key'));
    });
    var groupCount = checkedGroupKeys.size;

    $('#pickerSelectedCount').html('<strong>' + checkedItems + '</strong> barang (' + groupCount + ' kelompok) terpilih');
}

// Toggle accordion rincian NUP
$(document).on('click', '.btn-toggle-detail', function(e) {
    e.stopPropagation();
    var gKey = $(this).data('group-key');
    var targetRow = $('#detailRow_' + gKey);
    var icon = $(this).find('.arrow-icon');

    targetRow.toggle();
    if (targetRow.is(':visible')) {
        icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
    } else {
        icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
    }
});

// Klik baris kelompok (kecuali kolom checkbox & tombol rincian) untuk buka/tutup rincian
$(document).on('click', '.picker-group-row td', function(e) {
    if ($(this).find('.picker-group-checkbox').length > 0 || $(this).find('.btn-toggle-detail').length > 0 || e.target.tagName === 'INPUT' || e.target.tagName === 'BUTTON') {
        return;
    }
    var gKey = $(this).closest('.picker-group-row').data('group-key');
    $(this).closest('.picker-group-row').find('.btn-toggle-detail').trigger('click');
});

// Checkbox Kelompok: Centang/Lepas semua NUP di dalam kelompok tersebut
$(document).on('change', '.picker-group-checkbox', function(e) {
    e.stopPropagation();
    var gKey = $(this).data('group-key');
    var isChecked = $(this).is(':checked');
    $(this).prop('indeterminate', false);

    var itemCbs = $('.picker-item-checkbox[data-group-key="' + gKey + '"]');
    itemCbs.prop('checked', isChecked);
    itemCbs.closest('.picker-nup-chip').toggleClass('active text-primary font-weight-bold', isChecked);

    updatePickerCounter();
});

// Checkbox Item NUP: Perbarui status checkbox kelompok induknya
$(document).on('change', '.picker-item-checkbox', function() {
    var gKey = $(this).data('group-key');
    var isChecked = $(this).is(':checked');
    $(this).closest('.picker-nup-chip').toggleClass('active text-primary font-weight-bold', isChecked);

    var allInGroup = $('.picker-item-checkbox[data-group-key="' + gKey + '"]');
    var checkedInGroup = allInGroup.filter(':checked');
    var groupCb = $('.picker-group-checkbox[data-group-key="' + gKey + '"]');

    if (checkedInGroup.length === allInGroup.length && allInGroup.length > 0) {
        groupCb.prop({ checked: true, indeterminate: false });
    } else if (checkedInGroup.length === 0) {
        groupCb.prop({ checked: false, indeterminate: false });
    } else {
        groupCb.prop({ checked: false, indeterminate: true });
    }

    updatePickerCounter();
});

// Tombol Pilih Semua NUP di dalam satu kelompok
$(document).on('click', '.btn-check-all-group-nups', function() {
    var gKey = $(this).data('group-key');
    var itemCbs = $('.picker-item-checkbox[data-group-key="' + gKey + '"]');
    itemCbs.prop('checked', true);
    itemCbs.closest('.picker-nup-chip').addClass('active text-primary font-weight-bold');
    $('.picker-group-checkbox[data-group-key="' + gKey + '"]').prop({ checked: true, indeterminate: false });
    updatePickerCounter();
});

// Tombol Batal Pilih NUP di dalam satu kelompok
$(document).on('click', '.btn-uncheck-all-group-nups', function() {
    var gKey = $(this).data('group-key');
    var itemCbs = $('.picker-item-checkbox[data-group-key="' + gKey + '"]');
    itemCbs.prop('checked', false);
    itemCbs.closest('.picker-nup-chip').removeClass('active text-primary font-weight-bold');
    $('.picker-group-checkbox[data-group-key="' + gKey + '"]').prop({ checked: false, indeterminate: false });
    updatePickerCounter();
});

// Event listener ganti peruntukan & filter
$('input[name="picker_peruntukan"]').on('change', function() {
    loadPickerData();
});
$('#pickerKategoriSelect').on('change', function() {
    loadPickerData();
});
$('#pickerBtnClearSearch').on('click', function() {
    $('#pickerSearchInput').val('');
    loadPickerData();
});

// Check all kelompok dan semua unit di modal
$('#checkAllPicker').on('change', function() {
    var checked = $(this).is(':checked');
    $('.picker-group-checkbox, .picker-item-checkbox').prop('checked', checked).prop('indeterminate', false);
    $('.picker-nup-chip').toggleClass('active text-primary font-weight-bold', checked);
    updatePickerCounter();
});

// Tombol "Pilih Semua" di modal
$('#pickerBtnSelectAll').on('click', function() {
    $('.picker-group-checkbox, .picker-item-checkbox').prop('checked', true).prop('indeterminate', false);
    $('.picker-nup-chip').addClass('active text-primary font-weight-bold');
    $('#checkAllPicker').prop('checked', true);
    updatePickerCounter();
});

// Tombol "Tambahkan ke Surat": masukkan semua item yang dicentang
$('#btnInsertSelectedToForm').on('click', function() {
    var checkedItemCbs = $('.picker-item-checkbox:checked');
    if (checkedItemCbs.length === 0) {
        alert('Pilih minimal satu barang atau kelompok barang dengan mencentang kotak di tabel.');
        return;
    }

    var checkedIdSet = new Set();
    checkedItemCbs.each(function() {
        var id = $(this).data('item-id');
        if (id) checkedIdSet.add(parseInt(id, 10));
    });

    var addedCount = 0;
    pickerLoadedGroups.forEach(function(g) {
        (g.items || []).forEach(function(it) {
            if (checkedIdSet.has(parseInt(it.id, 10))) {
                var already = selectedItems.some(function(sel) {
                    return sel.inventaris_id && sel.inventaris_id === it.id;
                });

                if (!already) {
                    selectedItems.push({
                        inventaris_id: it.id,
                        jenis_barang: it.jenis_barang || 'MESIN PERALATAN NON TIK',
                        kode_barang: it.kode_barang || '',
                        nama_barang: it.nama_barang || '',
                        merk_tipe: it.merk_tipe || '',
                        nup: it.nup || '',
                        kondisi: it.kondisi || 'Baik',
                        tahun_perolehan: it.tahun_perolehan || new Date().getFullYear().toString()
                    });
                    addedCount++;
                }
            }
        });
    });

    $('#modalBarangPicker').modal('hide');
    renderItemsTable();
    syncPhotoGroups();
});

// Helper NUP Range Formatter di Client-Side JS
function formatNupRange(nups) {
    if (!nups || nups.length === 0) return '';
    var nums = [];
    nups.forEach(function(n) {
        var str = String(n).trim();
        if (/^\d+$/.test(str)) {
            nums.push(parseInt(str, 10));
        } else if (/^(\d+)\s*[-–—]\s*(\d+)$/.test(str)) {
            var m = str.match(/^(\d+)\s*[-–—]\s*(\d+)$/);
            var a = parseInt(m[1], 10);
            var b = parseInt(m[2], 10);
            for (var i = Math.min(a, b); i <= Math.max(a, b); i++) {
                nums.push(i);
            }
        }
    });

    if (nums.length === 0) return nups.join(', ');

    nums = Array.from(new Set(nums)).sort(function(a, b) { return a - b; });

    var ranges = [];
    var start = nums[0];
    var prev = nums[0];

    for (var i = 1; i < nums.length; i++) {
        if (nums[i] === prev + 1) {
            prev = nums[i];
        } else {
            ranges.push(start === prev ? String(start) : (start + ' - ' + prev));
            start = nums[i];
            prev = nums[i];
        }
    }
    ranges.push(start === prev ? String(start) : (start + ' - ' + prev));

    if (ranges.length <= 1) return ranges[0];
    if (ranges.length === 2) return ranges[0] + ' & ' + ranges[1];
    return ranges.slice(0, -1).join(', ') + ' & ' + ranges[ranges.length - 1];
}

// Simple MD5 Hash untuk key kelompok
function md5(string) {
    function RotateLeft(lValue, iShiftBits) {
        return (lValue << iShiftBits) | (lValue >>> (32 - iShiftBits));
    }
    function AddUnsigned(lX, lY) {
        var lX4, lY4, lX8, lY8, lResult;
        lX8 = (lX & 0x80000000);
        lY8 = (lY & 0x80000000);
        lX4 = (lX & 0x40000000);
        lY4 = (lY & 0x40000000);
        lResult = (lX & 0x3FFFFFFF) + (lY & 0x3FFFFFFF);
        if (lX4 & lY4) return (lResult ^ 0x80000000 ^ lX8 ^ lY8);
        if (lX4 | lY4) {
            if (lResult & 0x40000000) return (lResult ^ 0xC0000000 ^ lX8 ^ lY8);
            else return (lResult ^ 0x40000000 ^ lX8 ^ lY8);
        } else {
            return (lResult ^ lX8 ^ lY8);
        }
    }
    function F(x, y, z) { return (x & y) | ((~x) & z); }
    function G(x, y, z) { return (x & z) | (y & (~z)); }
    function H(x, y, z) { return (x ^ y ^ z); }
    function I(x, y, z) { return (y ^ (x | (~z))); }
    function FF(a, b, c, d, x, s, ac) {
        a = AddUnsigned(a, AddUnsigned(AddUnsigned(F(b, c, d), x), ac));
        return AddUnsigned(RotateLeft(a, s), b);
    }
    function GG(a, b, c, d, x, s, ac) {
        a = AddUnsigned(a, AddUnsigned(AddUnsigned(G(b, c, d), x), ac));
        return AddUnsigned(RotateLeft(a, s), b);
    }
    function HH(a, b, c, d, x, s, ac) {
        a = AddUnsigned(a, AddUnsigned(AddUnsigned(H(b, c, d), x), ac));
        return AddUnsigned(RotateLeft(a, s), b);
    }
    function II(a, b, c, d, x, s, ac) {
        a = AddUnsigned(a, AddUnsigned(AddUnsigned(I(b, c, d), x), ac));
        return AddUnsigned(RotateLeft(a, s), b);
    }
    function ConvertToWordArray(string) {
        var lWordCount;
        var lMessageLength = string.length;
        var lNumberOfWords_temp1 = lMessageLength + 8;
        var lNumberOfWords_temp2 = (lNumberOfWords_temp1 - (lNumberOfWords_temp1 % 64)) / 64;
        var lNumberOfWords = (lNumberOfWords_temp2 + 1) * 16;
        var lWordArray = Array(lNumberOfWords - 1);
        var lBytePosition = 0;
        var lByteCount = 0;
        while (lByteCount < lMessageLength) {
            lWordCount = (lByteCount - (lByteCount % 4)) / 4;
            lBytePosition = (lByteCount % 4) * 8;
            lWordArray[lWordCount] = (lWordArray[lWordCount] | (string.charCodeAt(lByteCount) << lBytePosition));
            lByteCount++;
        }
        lWordCount = (lByteCount - (lByteCount % 4)) / 4;
        lBytePosition = (lByteCount % 4) * 8;
        lWordArray[lWordCount] = lWordArray[lWordCount] | (0x80 << lBytePosition);
        lWordArray[lNumberOfWords - 2] = lMessageLength << 3;
        lWordArray[lNumberOfWords - 1] = lMessageLength >>> 29;
        return lWordArray;
    }
    function WordToHex(lValue) {
        var WordToHexValue = "", WordToHexValue_temp = "", lByte, lCount;
        for (lCount = 0; lCount <= 3; lCount++) {
            lByte = (lValue >>> (lCount * 8)) & 255;
            WordToHexValue_temp = "0" + lByte.toString(16);
            WordToHexValue = WordToHexValue + WordToHexValue_temp.substr(WordToHexValue_temp.length - 2, 2);
        }
        return WordToHexValue;
    }
    var x = Array();
    var k, AA, BB, CC, DD, a, b, c, d;
    var S11 = 7, S12 = 12, S13 = 17, S14 = 22;
    var S21 = 5, S22 = 9, S23 = 14, S24 = 20;
    var S31 = 4, S32 = 11, S33 = 16, S34 = 23;
    var S41 = 6, S42 = 10, S43 = 15, S44 = 21;
    x = ConvertToWordArray(string);
    a = 0x67452301; b = 0xEFCDAB89; c = 0x98BADCFE; d = 0x10325476;
    for (k = 0; k < x.length; k += 16) {
        AA = a; BB = b; CC = c; DD = d;
        a = FF(a, b, c, d, x[k + 0], S11, 0xD76AA478);
        d = FF(d, a, b, c, x[k + 1], S12, 0xE8C7B756);
        c = FF(c, d, a, b, x[k + 2], S13, 0x242070DB);
        b = FF(b, c, d, a, x[k + 3], S14, 0xC1BDCEEE);
        a = FF(a, b, c, d, x[k + 4], S11, 0xF57C0FAF);
        d = FF(d, a, b, c, x[k + 5], S12, 0x4787C62A);
        c = FF(c, d, a, b, x[k + 6], S13, 0xA8304613);
        b = FF(b, c, d, a, x[k + 7], S14, 0xFD469501);
        a = FF(a, b, c, d, x[k + 8], S11, 0x698098D8);
        d = FF(d, a, b, c, x[k + 9], S12, 0x8B44F7AF);
        c = FF(c, d, a, b, x[k + 10], S13, 0xFFFF5BB1);
        b = FF(b, c, d, a, x[k + 11], S14, 0x895CD7BE);
        a = FF(a, b, c, d, x[k + 12], S11, 0x6B901122);
        d = FF(d, a, b, c, x[k + 13], S12, 0xFD987193);
        c = FF(c, d, a, b, x[k + 14], S13, 0xA679438E);
        b = FF(b, c, d, a, x[k + 15], S14, 0x49B40821);
        a = GG(a, b, c, d, x[k + 1], S21, 0xF61E2562);
        d = GG(d, a, b, c, x[k + 6], S22, 0xC040B340);
        c = GG(c, d, a, b, x[k + 11], S23, 0x265E5A51);
        b = GG(b, c, d, a, x[k + 0], S24, 0xE9B6C7AA);
        a = GG(a, b, c, d, x[k + 5], S21, 0xD62F105D);
        d = GG(d, a, b, c, x[k + 10], S22, 0x02441453);
        c = GG(c, d, a, b, x[k + 15], S23, 0xD8A1E681);
        b = GG(b, c, d, a, x[k + 4], S24, 0xE7D3FBC8);
        a = GG(a, b, c, d, x[k + 9], S21, 0x21E1CDE6);
        d = GG(d, a, b, c, x[k + 14], S22, 0xC33707D6);
        c = GG(c, d, a, b, x[k + 3], S23, 0xF4D50D87);
        b = GG(b, c, d, a, x[k + 8], S24, 0x455A14ED);
        a = GG(a, b, c, d, x[k + 13], S21, 0xA9E3E905);
        d = GG(d, a, b, c, x[k + 2], S22, 0xFCEFA3F8);
        c = GG(c, d, a, b, x[k + 7], S23, 0x676F02D9);
        b = GG(b, c, d, a, x[k + 12], S24, 0x8D2A4C8A);
        a = HH(a, b, c, d, x[k + 5], S31, 0xFFFA3942);
        d = HH(d, a, b, c, x[k + 8], S32, 0x8771F681);
        c = HH(c, d, a, b, x[k + 11], S33, 0x6D9D6122);
        b = HH(b, c, d, a, x[k + 14], S34, 0xFDE5380C);
        a = HH(a, b, c, d, x[k + 1], S31, 0xA4BEEA44);
        d = HH(d, a, b, c, x[k + 4], S32, 0x4BDECFA9);
        c = HH(c, d, a, b, x[k + 7], S33, 0xF6BB4B60);
        b = HH(b, c, d, a, x[k + 10], S34, 0xBEBFBC70);
        a = HH(a, b, c, d, x[k + 13], S31, 0x289B7EC6);
        d = HH(d, a, b, c, x[k + 0], S32, 0xEAA127FA);
        c = HH(c, d, a, b, x[k + 3], S33, 0xD4EF3085);
        b = HH(b, c, d, a, x[k + 6], S34, 0x04881D05);
        a = HH(a, b, c, d, x[k + 9], S31, 0xD9D4D039);
        d = HH(d, a, b, c, x[k + 12], S32, 0xE6DB99E5);
        c = HH(c, d, a, b, x[k + 15], S33, 0x1FA27CF8);
        b = HH(b, c, d, a, x[k + 2], S34, 0xC4AC5665);
        a = II(a, b, c, d, x[k + 0], S41, 0xF4292244);
        d = II(d, a, b, c, x[k + 7], S42, 0x432AFF97);
        c = II(c, d, a, b, x[k + 14], S43, 0xAB9423A7);
        b = II(b, c, d, a, x[k + 5], S44, 0xFC93A039);
        a = II(a, b, c, d, x[k + 12], S41, 0x655B59C3);
        d = II(d, a, b, c, x[k + 3], S42, 0x8F0CCC92);
        c = II(c, d, a, b, x[k + 10], S43, 0xFFEFF47D);
        b = II(b, c, d, a, x[k + 1], S44, 0x85845DD1);
        a = II(a, b, c, d, x[k + 8], S41, 0x6FA87E4F);
        d = II(d, a, b, c, x[k + 15], S42, 0xFE2CE6E0);
        c = II(c, d, a, b, x[k + 6], S43, 0xA3014314);
        b = II(b, c, d, a, x[k + 13], S44, 0x4E0811A1);
        a = II(a, b, c, d, x[k + 4], S41, 0xF7537E82);
        d = II(d, a, b, c, x[k + 11], S42, 0xBD3AF235);
        c = II(c, d, a, b, x[k + 2], S43, 0x2AD7D2BB);
        b = II(b, c, d, a, x[k + 9], S44, 0xEB86D391);
        a = AddUnsigned(a, AA);
        b = AddUnsigned(b, BB);
        c = AddUnsigned(c, CC);
        d = AddUnsigned(d, DD);
    }
    var temp = WordToHex(a) + WordToHex(b) + WordToHex(c) + WordToHex(d);
    return temp.toLowerCase();
}

function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
</script>
<?= $this->endSection(); ?>
