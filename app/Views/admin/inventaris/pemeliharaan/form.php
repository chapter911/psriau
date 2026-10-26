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

            <!-- BAGIAN 2: DAFTAR BARANG YANG DIPELIHARA & STATUS FOTO 4 SUDUT -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white border-bottom py-2.5 d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
                    <div>
                        <h6 class="m-0 font-weight-bold text-dark">
                            <i class="fas fa-boxes text-success mr-2"></i>2. Daftar Barang yang Dipelihara
                        </h6>
                        <small class="text-muted">Pilih aset BMN dari master inventaris satker atau tambah manual. Foto 4 sudut dapat diinput sekarang atau dicicil belakangan.</small>
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline-success btn-sm shadow-sm mr-1 font-weight-bold" id="btnOpenPicker" style="border-radius: 6px;">
                            <i class="fas fa-search-plus mr-1"></i>Pilih dari Inventaris Satker
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm shadow-sm font-weight-bold" id="btnAddManualRow" style="border-radius: 6px;" title="Tambah Baris Barang Manual">
                            <i class="fas fa-plus mr-1"></i>Tambah Manual
                        </button>
                    </div>
                </div>

                <!-- PANDUAN WARNA & INDIKATOR TOMBOL ICON (SAMA PERSIS DENGAN MENU FOTO) -->
                <div class="px-3 pt-2.5 pb-2 bg-light border-bottom d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">
                    <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                        <span class="small font-weight-bold text-dark mr-1">Petunjuk Status:</span>
                        <span class="badge badge-success px-2.5 py-1.5 shadow-sm font-weight-bold" style="font-size: 0.76rem;">
                            <i class="fas fa-check-circle mr-1"></i>Hijau = Foto Tersedia (Klik untuk Pratinjau &amp; Update)
                        </span>
                        <span class="badge badge-danger px-2.5 py-1.5 shadow-sm font-weight-bold" style="font-size: 0.76rem;">
                            <i class="fas fa-camera mr-1"></i>Merah = Belum Ada Foto (Klik untuk Ambil / Upload Foto)
                        </span>
                    </div>
                    <div class="small text-muted">
                        <i class="fas fa-info-circle mr-1 text-info"></i>Foto bersifat fleksibel &amp; dapat dilengkapi bertahap belakangan.
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0" id="tableFotoPemeliharaan" style="font-size: 0.88rem;">
                            <thead class="thead-light text-center">
                                <tr>
                                    <th style="width: 40px;" class="align-middle">No</th>
                                    <th class="align-middle text-left">Nama Barang</th>
                                    <th style="width: 140px;" class="align-middle">Kategori</th>
                                    <th style="width: 160px;" class="align-middle text-left">Merek / Tipe / Spesifikasi</th>
                                    <th style="width: 110px;" class="align-middle">Rentang NUP</th>
                                    <th style="width: 68px;" class="align-middle" title="Tampak Depan">Depan</th>
                                    <th style="width: 68px;" class="align-middle" title="Tampak Atas">Atas</th>
                                    <th style="width: 68px;" class="align-middle" title="Tampak Bawah">Bawah</th>
                                    <th style="width: 68px;" class="align-middle" title="Tampak Belakang">Belakang</th>
                                    <th style="width: 135px;" class="align-middle">Kelengkapan</th>
                                    <th style="width: 85px;" class="align-middle">Aksi</th>
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

            <!-- CONTAINER DYNAMIC PHOTO INPUTS & ITEMS JSON -->
            <div id="dynamicPhotoInputs"></div>

            <!-- BAGIAN 3: TEKS KONSIDERANS & CATATAN (OPSIONAL) -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 10px;">
                <div class="card-header bg-white border-bottom py-2.5" data-toggle="collapse" data-target="#collapseKonsiderans" style="cursor: pointer;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-dark">
                            <i class="fas fa-pen-alt text-secondary mr-2"></i>3. Teks Konsiderans Alasan Teknis &amp; Catatan (Opsional)
                        </h6>
                        <i class="fas fa-chevron-down text-muted"></i>
                    </div>
                </div>
                <div class="collapse show" id="collapseKonsiderans">
                    <div class="card-body p-3">
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-dark">Pernyataan Poin a (Kondisi &amp; Kelayakan)</label>
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

<!-- MODAL 1: PRATINJAU FOTO & TOMBOL UPDATE (UNTUK TOMBOL HIJAU) -->
<div class="modal fade" id="modalPhotoPreview" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header bg-success text-white py-2.5 px-3 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="modal-title font-weight-bold mb-0" id="previewModalTitle">Pratinjau Foto</h6>
                    <small class="text-white opacity-9" id="previewModalSubtitle">-</small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3 text-center bg-dark position-relative">
                <div class="position-relative d-inline-block" style="max-height: 400px; width: 100%;">
                    <img id="previewModalImg" src="" style="max-height: 380px; max-width: 100%; object-fit: contain; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);" alt="Foto Lampiran">
                    <span class="badge badge-warning text-dark font-weight-bold shadow" style="position: absolute; bottom: 8px; right: 8px; font-size: 0.72rem;">
                        <i class="fas fa-certificate mr-1"></i>TimeMark Resmi PU
                    </span>
                </div>
            </div>
            <div class="modal-footer bg-light py-2.5 px-3 d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">
                <button type="button" class="btn btn-outline-danger btn-sm" id="btnPreviewDelete" title="Hapus foto ini">
                    <i class="fas fa-trash-alt mr-1"></i>Hapus Foto
                </button>
                <div class="d-flex align-items-center" style="gap: 6px;">
                    <button type="button" class="btn btn-outline-primary btn-sm font-weight-bold" id="btnPreviewRetakeCamera">
                        <i class="fas fa-camera mr-1"></i>Foto Ulang Kamera
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm font-weight-bold" id="btnPreviewUploadNew">
                        <i class="fas fa-upload mr-1"></i>Unggah File Baru
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 2: PILIHAN INPUT FOTO (UNTUK TOMBOL MERAH) -->
<div class="modal fade" id="modalEmptyAction" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header bg-primary text-white py-2.5 px-3">
                <div>
                    <h6 class="modal-title font-weight-bold mb-0" id="emptyActionTitle">Ambil / Unggah Foto</h6>
                    <small class="text-white opacity-9" id="emptyActionSubtitle">-</small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                <p class="small text-muted mb-3 text-center">Pilih metode pengambilan foto fisik untuk sudut ini:</p>
                <button type="button" class="btn btn-primary btn-block py-2 mb-2 font-weight-bold shadow-sm d-flex align-items-center justify-content-center" id="btnEmptyChooseCamera" style="border-radius: 8px;">
                    <i class="fas fa-camera fa-lg mr-2"></i>Foto Langsung Kamera
                </button>
                <button type="button" class="btn btn-outline-info btn-block py-2 font-weight-bold shadow-sm d-flex align-items-center justify-content-center" id="btnEmptyChooseUpload" style="border-radius: 8px;">
                    <i class="fas fa-upload fa-lg mr-2"></i>Unggah File Gambar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 3: KAMERA BERURUTAN TERPANDU (SEQUENTIAL CAMERA WIZARD & SINGLE SNAP) -->
<div class="modal fade" id="modalCameraWizard" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header bg-dark text-white py-2.5 px-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <i class="fas fa-camera text-warning mr-2"></i>
                    <div>
                        <h6 class="modal-title font-weight-bold mb-0" id="cameraWizardTitle">Pemotretan Terpandu 4 Sudut</h6>
                        <small class="text-light opacity-8" id="cameraWizardSubtitle">Target Aset: -</small>
                    </div>
                </div>
                <button type="button" class="close text-white opacity-8" id="btnCloseCameraWizard" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0 bg-black text-center position-relative" style="background: #000; min-height: 380px;">
                
                <!-- Indikator Langkah 4 Sudut -->
                <div class="wizard-steps-bar p-2 bg-dark d-flex justify-content-center align-items-center flex-wrap" style="gap: 8px; border-bottom: 1px solid #333;">
                    <span class="badge badge-secondary px-2.5 py-1.5 step-badge" id="stepBadge_depan"><i class="fas fa-circle mr-1"></i>1. Depan</span>
                    <i class="fas fa-chevron-right text-muted small"></i>
                    <span class="badge badge-secondary px-2.5 py-1.5 step-badge" id="stepBadge_atas"><i class="fas fa-circle mr-1"></i>2. Atas</span>
                    <i class="fas fa-chevron-right text-muted small"></i>
                    <span class="badge badge-secondary px-2.5 py-1.5 step-badge" id="stepBadge_bawah"><i class="fas fa-circle mr-1"></i>3. Bawah</span>
                    <i class="fas fa-chevron-right text-muted small"></i>
                    <span class="badge badge-secondary px-2.5 py-1.5 step-badge" id="stepBadge_belakang"><i class="fas fa-circle mr-1"></i>4. Belakang</span>
                </div>

                <!-- Video Viewfinder Kamera Langsung -->
                <div class="position-relative d-inline-block w-100" style="max-height: 440px; background: #000; overflow: hidden;">
                    <video id="cameraVideo" autoplay playsinline style="width: 100%; max-height: 440px; object-fit: contain; background: #000;"></video>
                    
                    <!-- Reticle Frame Pemandu Objek -->
                    <div id="cameraReticle" style="position: absolute; top: 10%; left: 10%; width: 80%; height: 80%; border: 2px dashed rgba(255, 255, 255, 0.45); pointer-events: none; border-radius: 8px;">
                        <span class="badge badge-dark opacity-8 p-1 small" style="position: absolute; top: 6px; left: 6px;" id="lblReticleAngle">
                            Arahkan kamera ke bagian DEPAN
                        </span>
                    </div>

                    <!-- Canvas Jepret Gambar Sementara -->
                    <canvas id="cameraCanvas" style="display: none;"></canvas>

                    <!-- Preview Hasil Foto dengan Watermark -->
                    <div id="cameraSnapPreviewWrapper" style="display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: #000;">
                        <img id="cameraSnapPreviewImg" src="" style="width: 100%; height: 100%; object-fit: contain;">
                        <div class="watermark-processing-indicator" id="wmProcessingSpinner" style="display: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(0,0,0,0.75); color: #fff; padding: 12px 20px; border-radius: 8px;">
                            <i class="fas fa-spinner fa-spin mr-2 text-warning"></i>Menyematkan Watermark TimeMark PU...
                        </div>
                    </div>
                </div>

                <!-- Petunjuk Sudut Aktif -->
                <div class="py-2 px-3 bg-dark text-white d-flex justify-content-between align-items-center">
                    <span class="small font-weight-bold" id="lblCurrentAngleInstruction">
                        <i class="fas fa-info-circle text-info mr-1"></i>Ambil foto dari <strong>Tampak Depan</strong>
                    </span>
                    <button type="button" class="btn btn-outline-light btn-xs" id="btnSwitchCamera" title="Ganti Kamera Depan/Belakang">
                        <i class="fas fa-sync-alt mr-1"></i>Ganti Kamera
                    </button>
                </div>
            </div>
            <div class="modal-footer bg-dark py-2 px-3 d-flex justify-content-between">
                <div>
                    <button type="button" class="btn btn-outline-secondary btn-sm text-light" id="btnCancelCameraWizard">
                        Batal
                    </button>
                </div>
                <div id="cameraControlsCapture">
                    <button type="button" class="btn btn-warning btn-sm rounded-pill px-4 font-weight-bold shadow" id="btnSnapPhoto">
                        <i class="fas fa-camera mr-1.5"></i>Jepret Foto
                    </button>
                </div>
                <div id="cameraControlsReview" style="display: none;">
                    <button type="button" class="btn btn-outline-light btn-sm mr-2" id="btnRetakeCurrentAngle">
                        <i class="fas fa-redo mr-1"></i>Ulangi
                    </button>
                    <button type="button" class="btn btn-success btn-sm rounded-pill px-4 font-weight-bold" id="btnAcceptCurrentAngle">
                        <i class="fas fa-check mr-1"></i>Gunakan &amp; Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 4: TAMBAH BARANG MANUAL -->
<div class="modal fade" id="modalManualItem" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-primary text-white py-2.5 px-3">
                <h6 class="modal-title font-weight-bold mb-0">
                    <i class="fas fa-plus-circle mr-1.5"></i>Tambah Barang Manual
                </h6>
                <button type="button" class="close text-white opacity-8" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                <div class="form-group mb-2.5">
                    <label class="small font-weight-bold text-dark">Kategori / Jenis Barang</label>
                    <select id="manualJenisBarang" class="form-control form-control-sm" style="border-radius: 6px;">
                        <option value="MESIN PERALATAN NON TIK" selected>MESIN PERALATAN NON TIK</option>
                        <option value="Peralatan &amp; Mesin">Peralatan &amp; Mesin</option>
                        <option value="Gedung &amp; Bangunan">Gedung &amp; Bangunan</option>
                        <option value="Jaringan">Jaringan</option>
                        <option value="Aset Tetap Lainnya">Aset Tetap Lainnya</option>
                    </select>
                </div>
                <div class="row mb-2.5">
                    <div class="col-md-5">
                        <label class="small font-weight-bold text-dark">Kode Barang</label>
                        <input type="text" id="manualKodeBarang" class="form-control form-control-sm" placeholder="Contoh: 3.05.02.01.003" style="border-radius: 6px;">
                    </div>
                    <div class="col-md-7">
                        <label class="small font-weight-bold text-dark">Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" id="manualNamaBarang" class="form-control form-control-sm font-weight-bold" placeholder="Contoh: AC SPLIT 1.5 PK" style="border-radius: 6px;" required>
                    </div>
                </div>
                <div class="form-group mb-2.5">
                    <label class="small font-weight-bold text-dark">Merk / Tipe / Spesifikasi</label>
                    <input type="text" id="manualMerkTipe" class="form-control form-control-sm" placeholder="Contoh: DAIKIN FTKC35 / INVERTER" style="border-radius: 6px;">
                </div>
                <div class="row mb-2.5">
                    <div class="col-md-6">
                        <label class="small font-weight-bold text-dark">Rentang / Nomor NUP <span class="text-danger">*</span></label>
                        <input type="text" id="manualNupInput" class="form-control form-control-sm" placeholder="Contoh: 1-5 atau 12 atau 1, 2" style="border-radius: 6px;" required>
                        <small class="text-muted" style="font-size: 0.72rem;">Isi <code>1-5</code> untuk otomatis membuat 5 unit.</small>
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="small font-weight-bold text-dark">Kondisi</label>
                        <select id="manualKondisi" class="form-control form-control-sm" style="border-radius: 6px;">
                            <option value="Baik" selected>Baik</option>
                            <option value="Rusak Ringan">Rusak Ringan</option>
                            <option value="Rusak Berat">Rusak Berat</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="small font-weight-bold text-dark">Tahun</label>
                        <input type="text" id="manualTahun" class="form-control form-control-sm text-center" value="<?= date('Y'); ?>" style="border-radius: 6px;">
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-3 d-flex justify-content-between">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-dismiss="modal">
                    Batal
                </button>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 font-weight-bold shadow-sm" id="btnSaveManualItem">
                    <i class="fas fa-check-circle mr-1"></i>Tambahkan ke Daftar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 5: RINCIAN UNIT NUP KELOMPOK BARANG -->
<div class="modal fade" id="modalGroupItemsDetail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-info text-white py-2.5 px-3 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="modal-title font-weight-bold mb-0" id="detailModalTitle">Rincian Unit Barang</h6>
                    <small class="text-white opacity-9" id="detailModalSubtitle">-</small>
                </div>
                <button type="button" class="close text-white opacity-8" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive" style="max-height: 350px;">
                    <table class="table table-sm table-bordered table-hover mb-0" style="font-size: 0.82rem;">
                        <thead class="thead-light text-center sticky-top">
                            <tr>
                                <th style="width: 40px;">No</th>
                                <th style="width: 90px;">NUP</th>
                                <th>Kondisi</th>
                                <th style="width: 80px;">Tahun</th>
                                <th style="width: 50px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyGroupItemsDetail">
                            <!-- Diisi via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <small class="text-muted" id="detailModalCounter">0 unit</small>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 font-weight-bold" data-dismiss="modal">
                    Tutup &amp; Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Global Hidden File Input untuk Unggah Cepat -->
<input type="file" id="globalSlotFileInput" class="d-none" accept="image/*,.heic,.heif">

<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script>
// Data items dan fotos awal dari controller PHP
var selectedItems = <?= json_encode($items ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?> || [];
var existingFotos = <?= json_encode($fotos ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?> || [];
var baseUrl = '<?= rtrim(site_url(), "/"); ?>';

// State Foto Tersimpan (Base64 atau URL dari database)
var groupPhotos = {};
var deletedPhotos = {};

// Inisialisasi groupPhotos dari existingFotos untuk mode edit
existingFotos.forEach(function(f) {
    if (f.kelompok_key) {
        groupPhotos[f.kelompok_key] = groupPhotos[f.kelompok_key] || {};
        ['depan', 'atas', 'bawah', 'belakang'].forEach(function(pos) {
            if (f['foto_' + pos]) {
                groupPhotos[f.kelompok_key][pos] = baseUrl + '/' + f['foto_' + pos].replace(/^\/+/, '');
            }
        });
    }
});

// State Slot Foto & Kamera
var activeGroupKey = null;
var activePos = '';
var activeLabel = '';
var activeItemNama = '';
var activeItemNup = '';
var activeSrc = '';
var singleModeAngle = null;
var currentAngleIndex = 0;
var angleOrder = ['depan', 'atas', 'bawah', 'belakang'];
var currentAngle = 'depan';
var videoStream = null;
var currentFacingMode = 'environment';
var userGpsCoordinates = '0.4851° N, 101.4485° E';
var capturedSessionImages = {};

var angleLabels = {
    'depan': 'Tampak Depan',
    'atas': 'Tampak Atas',
    'bawah': 'Tampak Bawah',
    'belakang': 'Tampak Belakang'
};

document.addEventListener('DOMContentLoaded', function() {
    renderItemsTable();

    // Deteksi Geolocation Browser untuk Watermark Real-time
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(pos) {
            var lat = pos.coords.latitude.toFixed(5);
            var lng = pos.coords.longitude.toFixed(5);
            var latDir = lat >= 0 ? '° N' : '° S';
            var lngDir = lng >= 0 ? '° E' : '° W';
            userGpsCoordinates = Math.abs(lat) + latDir + ', ' + Math.abs(lng) + lngDir;
            $('#cameraGpsText').text(userGpsCoordinates + ' (Satker PPS Riau)');
        }, function(err) {
            userGpsCoordinates = '0.4851° N, 101.4485° E';
            $('#cameraGpsText').text(userGpsCoordinates + ' (Satker PPS Riau)');
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

    // Buka Modal Tambah Manual
    $('#btnAddManualRow').on('click', function() {
        $('#manualNamaBarang').val('');
        $('#manualKodeBarang').val('');
        $('#manualMerkTipe').val('');
        $('#manualNupInput').val('');
        $('#manualKondisi').val('Baik');
        $('#manualTahun').val(new Date().getFullYear().toString());
        $('#modalManualItem').modal('show');
    });

    // Simpan Barang dari Modal Tambah Manual
    $('#btnSaveManualItem').on('click', function() {
        var nama = $('#manualNamaBarang').val().trim();
        var nupRaw = $('#manualNupInput').val().trim();

        if (!nama) {
            alert('Nama barang wajib diisi.');
            $('#manualNamaBarang').focus();
            return;
        }
        if (!nupRaw) {
            alert('Rentang atau nomor NUP wajib diisi.');
            $('#manualNupInput').focus();
            return;
        }

        var nups = parseNupInput(nupRaw);
        if (nups.length === 0) {
            alert('Format NUP tidak valid.');
            return;
        }

        var jenis = $('#manualJenisBarang').val();
        var kode = $('#manualKodeBarang').val().trim();
        var merk = $('#manualMerkTipe').val().trim();
        var kondisi = $('#manualKondisi').val();
        var tahun = $('#manualTahun').val().trim() || new Date().getFullYear().toString();

        nups.forEach(function(n) {
            selectedItems.push({
                inventaris_id: null,
                jenis_barang: jenis,
                kode_barang: kode,
                nama_barang: nama,
                merk_tipe: merk,
                nup: n,
                kondisi: kondisi,
                tahun_perolehan: tahun
            });
        });

        $('#modalManualItem').modal('hide');
        renderItemsTable();
    });

    // Hapus Semua Barang
    $('#btnClearAllItems').on('click', function() {
        if (selectedItems.length > 0 && confirm('Kosongkan seluruh barang yang dipilih dari surat ini?')) {
            selectedItems = [];
            groupPhotos = {};
            deletedPhotos = {};
            renderItemsTable();
        }
    });

    // Submit Validation & Serialisasi Hidden Form Data
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

        // Inject foto base64 dan instruksi hapus foto ke dalam dynamic container
        var photoContainer = $('#dynamicPhotoInputs');
        photoContainer.empty();

        Object.keys(groupPhotos).forEach(function(kKey) {
            var photos = groupPhotos[kKey];
            if (photos) {
                ['depan', 'atas', 'bawah', 'belakang'].forEach(function(pos) {
                    var pData = photos[pos];
                    if (pData && pData.indexOf('data:image') === 0) {
                        photoContainer.append('<input type="hidden" name="foto_base64_' + kKey + '_' + pos + '" value="' + pData + '">');
                    }
                });
            }
        });

        Object.keys(deletedPhotos).forEach(function(delKey) {
            if (deletedPhotos[delKey]) {
                var parts = delKey.split('_');
                var pos = parts.pop();
                var kKey = parts.join('_');
                photoContainer.append('<input type="hidden" name="hapus_' + pos + '_' + kKey + '" value="1">');
                photoContainer.append('<input type="hidden" name="hapus_foto_' + pos + '_' + kKey + '" value="1">');
            }
        });

        $('#btnSubmitSurat').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...');
        return true;
    });

    // Klik Slot Tombol Foto (Bisa Hijau atau Merah)
    $(document).on('click', '.btn-slot-photo', function() {
        var btn = $(this);
        activeGroupKey = btn.data('key');
        activePos = btn.data('pos');
        activeLabel = btn.data('label') || angleLabels[activePos];
        activeItemNama = btn.data('nama');
        activeItemNup = btn.data('nup');
        activeSrc = btn.data('src');
        var status = btn.data('status');

        if (status === 'filled') {
            // FOTO SUDAH ADA (HIJAU): Buka Modal Pratinjau
            $('#previewModalTitle').text(activeItemNama + ' - ' + activeLabel);
            $('#previewModalSubtitle').text('NUP: ' + (activeItemNup || '-'));
            $('#previewModalImg').attr('src', activeSrc);
            $('#modalPhotoPreview').modal('show');
        } else {
            // BELUM ADA FOTO (MERAH): Buka Modal Pilihan Ambil Kamera / Unggah
            $('#emptyActionTitle').text(activeItemNama + ' - ' + activeLabel);
            $('#emptyActionSubtitle').text('NUP: ' + (activeItemNup || '-'));
            $('#modalEmptyAction').modal('show');
        }
    });

    // Modal Merah: Ambil Kamera
    $('#btnEmptyChooseCamera').on('click', function() {
        $('#modalEmptyAction').modal('hide');
        startSingleCameraSnap(activeGroupKey, activePos, activeLabel, activeItemNama, activeItemNup);
    });

    // Modal Merah: Unggah File
    $('#btnEmptyChooseUpload').on('click', function() {
        $('#modalEmptyAction').modal('hide');
        $('#globalSlotFileInput').click();
    });

    // Modal Hijau: Foto Ulang Kamera
    $('#btnPreviewRetakeCamera').on('click', function() {
        $('#modalPhotoPreview').modal('hide');
        startSingleCameraSnap(activeGroupKey, activePos, activeLabel, activeItemNama, activeItemNup);
    });

    // Modal Hijau: Ganti File Unggah
    $('#btnPreviewUploadNew').on('click', function() {
        $('#modalPhotoPreview').modal('hide');
        $('#globalSlotFileInput').click();
    });

    // Modal Hijau: Hapus Foto
    $('#btnPreviewDelete').on('click', function() {
        if (!confirm('Apakah Anda yakin ingin menghapus foto ' + activeLabel + ' ini?')) {
            return;
        }

        if (groupPhotos[activeGroupKey]) {
            delete groupPhotos[activeGroupKey][activePos];
        }
        deletedPhotos[activeGroupKey + '_' + activePos] = true;
        $('#modalPhotoPreview').modal('hide');
        renderItemsTable();
    });

    // Unggah File Gambar (FileReader)
    $('#globalSlotFileInput').on('change', function(e) {
        var file = this.files[0];
        if (!file || !activeGroupKey || !activePos) return;

        var reader = new FileReader();
        reader.onload = function(evt) {
            var dataUri = evt.target.result;
            groupPhotos[activeGroupKey] = groupPhotos[activeGroupKey] || {};
            groupPhotos[activeGroupKey][activePos] = dataUri;
            delete deletedPhotos[activeGroupKey + '_' + activePos];

            $('#globalSlotFileInput').val('');
            renderItemsTable();
        };
        reader.readAsDataURL(file);
    });

    // Mulai Wizard 4 Sudut dari Tombol di Kolom Kelengkapan
    $(document).on('click', '.btn-start-group-wizard', function() {
        activeGroupKey = $(this).data('key');
        activeItemNama = $(this).data('nama');
        activeItemNup = $(this).data('nup');
        singleModeAngle = null;

        $('#cameraWizardTitle').text('Pemotretan Terpandu 4 Sudut');
        $('#cameraWizardSubtitle').text(activeItemNama + ' (NUP: ' + (activeItemNup || '-') + ')');
        $('.wizard-steps-bar').show();

        capturedSessionImages = {};
        $('#modalCameraWizard').modal('show');
        switchCameraStep('depan');
        startCameraStream();
    });

    // Tombol Ganti Kamera Depan / Belakang
    $('#btnSwitchCamera').on('click', function() {
        currentFacingMode = (currentFacingMode === 'environment') ? 'user' : 'environment';
        startCameraStream();
    });

    // Tutup / Batal Modal Kamera
    $('#btnCloseCameraWizard, #btnCancelCameraWizard').on('click', function() {
        stopCameraStream();
        $('#modalCameraWizard').modal('hide');
    });

    // Jepret Foto Saat Ini & Minta Watermark TimeMark PU
    $('#btnSnapPhoto').on('click', function() {
        var video = document.getElementById('cameraVideo');
        var canvas = document.getElementById('cameraCanvas');
        if (!video || !video.videoWidth) return;

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        var ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        var rawBase64 = canvas.toDataURL('image/jpeg', 0.92);

        $('#cameraSnapPreviewImg').attr('src', rawBase64);
        $('#cameraSnapPreviewWrapper').show();
        $('#wmProcessingSpinner').show();
        $('#cameraControlsCapture').hide();
        $('#cameraControlsReview').hide();

        $.ajax({
            url: baseUrl + '/admin/inventaris/pemeliharaan/apply-watermark',
            type: 'POST',
            data: {
                image_data: rawBase64,
                nama_barang: activeItemNama,
                merk_tipe: '',
                nup: activeItemNup,
                sudut: angleLabels[currentAngle],
                coordinates: userGpsCoordinates
            },
            dataType: 'json',
            success: function(res) {
                $('#wmProcessingSpinner').hide();
                if (res.success && res.data_uri) {
                    $('#cameraSnapPreviewImg').attr('src', res.data_uri);
                    capturedSessionImages[currentAngle] = res.data_uri;
                    $('#cameraControlsReview').show();
                } else {
                    capturedSessionImages[currentAngle] = rawBase64;
                    $('#cameraControlsReview').show();
                }
            },
            error: function() {
                $('#wmProcessingSpinner').hide();
                capturedSessionImages[currentAngle] = rawBase64;
                $('#cameraControlsReview').show();
            }
        });
    });

    // Ulangi Jepret Sudut Saat Ini
    $('#btnRetakeCurrentAngle').on('click', function() {
        delete capturedSessionImages[currentAngle];
        $('#cameraSnapPreviewWrapper').hide();
        $('#cameraControlsCapture').show();
        $('#cameraControlsReview').hide();
    });

    // Terima Hasil Foto Sudut Saat Ini
    $('#btnAcceptCurrentAngle').on('click', function() {
        var finalDataUri = capturedSessionImages[currentAngle];
        if (!finalDataUri) return;

        groupPhotos[activeGroupKey] = groupPhotos[activeGroupKey] || {};
        groupPhotos[activeGroupKey][currentAngle] = finalDataUri;
        delete deletedPhotos[activeGroupKey + '_' + currentAngle];

        if (singleModeAngle !== null) {
            stopCameraStream();
            $('#modalCameraWizard').modal('hide');
            renderItemsTable();
        } else {
            if (currentAngleIndex < angleOrder.length - 1) {
                var nextAngle = angleOrder[currentAngleIndex + 1];
                switchCameraStep(nextAngle);
            } else {
                stopCameraStream();
                $('#modalCameraWizard').modal('hide');
                renderItemsTable();
            }
        }
    });

    // Hapus Kelompok Barang dari Tabel
    $(document).on('click', '.btn-remove-group', function() {
        var gKey = $(this).data('key');
        if (confirm('Hapus seluruh barang dalam kelompok ini dari surat?')) {
            selectedItems = selectedItems.filter(function(it) {
                var k = (it.kode_barang || '').trim();
                var n = (it.nama_barang || '').trim();
                var m = (it.merk_tipe || '').trim();
                return md5(k + '::' + n + '::' + m) !== gKey;
            });
            delete groupPhotos[gKey];
            renderItemsTable();
        }
    });

    // Buka Modal Rincian Unit NUP
    $(document).on('click', '.btn-view-group-detail', function() {
        var gKey = $(this).data('key');
        activeGroupKey = gKey;
        renderGroupItemsDetailModal(gKey);
        $('#modalGroupItemsDetail').modal('show');
    });
});

// Render Tabel Kelompok Barang & Status Foto 4 Sudut (Identik dengan foto.php)
function renderItemsTable() {
    var tbody = $('#tbodySelectedItems');
    tbody.empty();

    if (selectedItems.length === 0) {
        tbody.append(
            '<tr>' +
                '<td colspan="11" class="text-center py-5 text-muted">' +
                    '<i class="fas fa-boxes fa-3x mb-2 text-secondary" style="opacity: 0.3;"></i>' +
                    '<div class="font-weight-bold">Belum Ada Barang yang Dipilih</div>' +
                    '<small>Silakan klik <strong>Pilih dari Inventaris Satker</strong> atau <strong>Tambah Manual</strong> di atas.</small>' +
                '</td>' +
            '</tr>'
        );
        $('#selectedItemsCounter').html('Total: <strong>0</strong> barang dipilih');
        return;
    }

    // Kelompokkan selectedItems berdasarkan kode_barang + nama_barang + merk_tipe
    var groupsMap = {};
    selectedItems.forEach(function(it) {
        var k = (it.kode_barang || '').trim();
        var n = (it.nama_barang || '').trim();
        var m = (it.merk_tipe || '').trim();
        var kat = (it.jenis_barang || 'MESIN PERALATAN NON TIK').trim();
        var nup = String(it.nup || '').trim();

        var key = md5(k + '::' + n + '::' + m);
        if (!groupsMap[key]) {
            groupsMap[key] = {
                key: key,
                kode_barang: k,
                nama_barang: n,
                merk_tipe: m,
                kategori: kat,
                items: [],
                nups: []
            };
        }
        groupsMap[key].items.push(it);
        if (nup) {
            groupsMap[key].nups.push(nup);
        }
    });

    // Urutkan grup berdasarkan nama_barang ASC, lalu NUP ASC (numerik)
    var groupList = Object.values(groupsMap);
    groupList.sort(function(a, b) {
        var cmpNama = (a.nama_barang || '').localeCompare(b.nama_barang || '', 'id', { sensitivity: 'base' });
        if (cmpNama !== 0) return cmpNama;

        var minNupA = a.nups.length > 0 ? Math.min.apply(null, a.nups.map(function(x) { return parseInt(x, 10) || 0; })) : 0;
        var minNupB = b.nups.length > 0 ? Math.min.apply(null, b.nups.map(function(x) { return parseInt(x, 10) || 0; })) : 0;
        return minNupA - minNupB;
    });

    var angles = ['depan', 'atas', 'bawah', 'belakang'];

    groupList.forEach(function(g, gIdx) {
        var kKey = g.key;
        var nupSummary = formatNupRange(g.nups);
        g.nup_gabungan = nupSummary;

        var tr = $('<tr id="rowGroup_' + kKey + '"></tr>');

        // 1. No
        tr.append('<td class="text-center text-muted font-weight-bold align-middle" style="width: 40px;">' + (gIdx + 1) + '</td>');

        // 2. Nama Barang
        var namaHtml = '<div class="font-weight-bold text-dark">' + escapeHtml(g.nama_barang || 'Tanpa Nama') + '</div>';
        if (g.kode_barang) {
            namaHtml += '<small class="text-muted font-weight-bold"><code>' + escapeHtml(g.kode_barang) + '</code></small>';
        }
        tr.append('<td class="align-middle">' + namaHtml + '</td>');

        // 3. Kategori
        tr.append(
            '<td class="text-center align-middle" style="width: 140px;">' +
                '<span class="badge badge-light border text-dark font-weight-bold px-2 py-1" style="font-size: 0.74rem;">' +
                    escapeHtml(g.kategori || 'MESIN PERALATAN NON TIK') +
                '</span>' +
            '</td>'
        );

        // 4. Merek / Tipe / Spesifikasi
        tr.append(
            '<td class="align-middle text-secondary small" style="width: 160px;">' +
                escapeHtml(g.merk_tipe || '-') +
            '</td>'
        );

        // 5. Rentang NUP
        tr.append(
            '<td class="text-center align-middle" style="width: 110px;">' +
                '<span class="badge badge-light border text-primary font-weight-bold px-2 py-1" style="font-size: 0.76rem;">' +
                    escapeHtml(nupSummary || '-') +
                '</span>' +
                '<div class="small text-muted mt-0.5 font-weight-bold">' + g.items.length + ' Unit</div>' +
            '</td>'
        );

        // Render 4 Slot Foto (Depan, Atas, Bawah, Belakang)
        var filledCount = 0;
        angles.forEach(function(pos) {
            var isFilled = false;
            var photoSrc = '';

            if (groupPhotos[kKey] && groupPhotos[kKey][pos]) {
                isFilled = true;
                photoSrc = groupPhotos[kKey][pos];
            } else if (!deletedPhotos[kKey + '_' + pos]) {
                var oldF = existingFotos.find(function(ef) { return ef.kelompok_key === kKey; });
                if (oldF && oldF['foto_' + pos]) {
                    isFilled = true;
                    photoSrc = baseUrl + '/' + oldF['foto_' + pos].replace(/^\/+/, '');
                }
            }

            if (isFilled) filledCount++;

            var label = angleLabels[pos];
            var btnHtml = '';
            if (isFilled) {
                btnHtml = '<button type="button" class="btn btn-success btn-sm btn-slot-photo shadow-sm"' +
                    ' id="btnSlot_' + kKey + '_' + pos + '"' +
                    ' data-key="' + kKey + '"' +
                    ' data-pos="' + pos + '"' +
                    ' data-status="filled"' +
                    ' data-src="' + photoSrc + '"' +
                    ' data-nama="' + escapeHtml(g.nama_barang) + '"' +
                    ' data-nup="' + escapeHtml(nupSummary) + '"' +
                    ' data-label="' + escapeHtml(label) + '"' +
                    ' title="' + escapeHtml(label) + ': Foto Tersedia (Klik untuk Pratinjau & Update)"' +
                    ' style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px;">' +
                    '<i class="fas fa-check-circle" style="font-size: 1.1rem;"></i>' +
                '</button>';
            } else {
                btnHtml = '<button type="button" class="btn btn-outline-danger btn-sm btn-slot-photo shadow-sm"' +
                    ' id="btnSlot_' + kKey + '_' + pos + '"' +
                    ' data-key="' + kKey + '"' +
                    ' data-pos="' + pos + '"' +
                    ' data-status="empty"' +
                    ' data-src=""' +
                    ' data-nama="' + escapeHtml(g.nama_barang) + '"' +
                    ' data-nup="' + escapeHtml(nupSummary) + '"' +
                    ' data-label="' + escapeHtml(label) + '"' +
                    ' title="' + escapeHtml(label) + ': Belum Ada Foto (Klik untuk Ambil / Upload Foto)"' +
                    ' style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px;">' +
                    '<i class="fas fa-camera text-danger" style="font-size: 1.05rem;"></i>' +
                '</button>';
            }

            tr.append('<td class="text-center align-middle" id="cellSlot_' + kKey + '_' + pos + '" style="width: 68px;">' + btnHtml + '</td>');
        });

        // 10. Kelengkapan & Tombol Wizard 4 Sudut
        var badgeColor = 'danger';
        if (filledCount === 4) {
            badgeColor = 'success';
        } else if (filledCount > 0) {
            badgeColor = 'warning text-dark';
        }

        tr.append(
            '<td class="text-center align-middle" style="width: 135px;">' +
                '<div class="d-flex flex-column align-items-center" style="gap: 4px;">' +
                    '<span class="badge badge-' + badgeColor + ' px-2 py-1 font-weight-bold group-status-badge" id="badgeGroup_' + kKey + '" style="font-size: 0.74rem;">' +
                        filledCount + ' / 4 Terisi' +
                    '</span>' +
                    '<button type="button" class="btn btn-outline-primary btn-xs font-weight-bold rounded-pill px-2 py-0.5 btn-start-group-wizard shadow-sm"' +
                        ' data-key="' + kKey + '"' +
                        ' data-nama="' + escapeHtml(g.nama_barang) + '"' +
                        ' data-nup="' + escapeHtml(nupSummary) + '"' +
                        ' title="Jepret 4 Sudut Berurutan (Wizard)">' +
                        '<i class="fas fa-camera mr-0.5"></i>4 Sudut' +
                    '</button>' +
                '</div>' +
            '</td>'
        );

        // 11. Aksi (Rincian Unit & Hapus Kelompok)
        tr.append(
            '<td class="text-center align-middle" style="width: 85px;">' +
                '<div class="btn-group btn-group-sm">' +
                    '<button type="button" class="btn btn-outline-info btn-xs btn-view-group-detail" data-key="' + kKey + '" title="Lihat &amp; Kelola Rincian NUP" style="border-radius: 6px 0 0 6px;">' +
                        '<i class="fas fa-list-ol"></i>' +
                    '</button>' +
                    '<button type="button" class="btn btn-outline-danger btn-xs btn-remove-group" data-key="' + kKey + '" title="Hapus Kelompok Barang Ini" style="border-radius: 0 6px 6px 0;">' +
                        '<i class="fas fa-trash-alt"></i>' +
                    '</button>' +
                '</div>' +
            '</td>'
        );

        tbody.append(tr);
    });

    var totalItemsCount = selectedItems.length;
    var totalGroupsCount = groupList.length;
    $('#selectedItemsCounter').html('Total: <strong>' + totalItemsCount + '</strong> barang (' + totalGroupsCount + ' kelompok) dipilih');
}

// Mulai Single Camera Snap
function startSingleCameraSnap(groupKey, pos, label, nama, nup) {
    activeGroupKey = groupKey;
    activePos = pos;
    activeLabel = label;
    activeItemNama = nama;
    activeItemNup = nup;
    singleModeAngle = pos;

    $('#cameraWizardTitle').text('Foto ' + label);
    $('#cameraWizardSubtitle').text(nama + ' (NUP: ' + (nup || '-') + ')');
    $('.wizard-steps-bar').hide();

    capturedSessionImages = {};
    $('#modalCameraWizard').modal('show');
    switchCameraStep(pos);
    startCameraStream();
}

// Beralih Sudut Kamera
function switchCameraStep(angle) {
    currentAngle = angle;
    currentAngleIndex = angleOrder.indexOf(angle);

    $('.step-badge').removeClass('badge-warning text-dark font-weight-bold').addClass('badge-secondary');
    $('#stepBadge_' + angle).removeClass('badge-secondary').addClass('badge-warning text-dark font-weight-bold');

    $('#lblReticleAngle').text('Arahkan kamera ke bagian ' + angle.toUpperCase());
    $('#lblCurrentAngleInstruction').html('<i class="fas fa-camera text-info mr-1"></i>Ambil foto dari <strong>' + angleLabels[angle] + '</strong>');

    $('#cameraSnapPreviewWrapper').hide();
    $('#cameraControlsCapture').show();
    $('#cameraControlsReview').hide();
}

// Mulai Stream Kamera WebRTC
function startCameraStream() {
    stopCameraStream();

    var constraints = {
        video: {
            facingMode: currentFacingMode,
            width: { ideal: 1920 },
            height: { ideal: 1080 }
        },
        audio: false
    };

    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia(constraints).then(function(stream) {
            videoStream = stream;
            var videoElem = document.getElementById('cameraVideo');
            videoElem.srcObject = stream;
            videoElem.play();
        }).catch(function(err) {
            console.warn('Gagal akses kamera resolusi tinggi, coba default:', err);
            navigator.mediaDevices.getUserMedia({ video: true, audio: false }).then(function(stream) {
                videoStream = stream;
                var videoElem = document.getElementById('cameraVideo');
                videoElem.srcObject = stream;
                videoElem.play();
            }).catch(function(fallbackErr) {
                alert('Tidak dapat mengakses kamera perangkat: ' + fallbackErr.message);
            });
        });
    } else {
        alert('Perangkat Anda tidak mendukung akses WebRTC Camera.');
    }
}

// Hentikan Stream Kamera
function stopCameraStream() {
    if (videoStream) {
        videoStream.getTracks().forEach(function(track) { track.stop(); });
        videoStream = null;
    }
}

// Modal Rincian Unit NUP dalam Satu Kelompok
function renderGroupItemsDetailModal(gKey) {
    var tbody = $('#tbodyGroupItemsDetail');
    tbody.empty();

    var groupItems = [];
    selectedItems.forEach(function(it, idx) {
        var k = (it.kode_barang || '').trim();
        var n = (it.nama_barang || '').trim();
        var m = (it.merk_tipe || '').trim();
        if (md5(k + '::' + n + '::' + m) === gKey) {
            groupItems.push({ item: it, originalIndex: idx });
        }
    });

    if (groupItems.length === 0) {
        $('#modalGroupItemsDetail').modal('hide');
        return;
    }

    var firstItem = groupItems[0].item;
    $('#detailModalTitle').text('Rincian: ' + firstItem.nama_barang);
    $('#detailModalSubtitle').html((firstItem.merk_tipe ? escapeHtml(firstItem.merk_tipe) + ' &bull; ' : '') + groupItems.length + ' Unit');
    $('#detailModalCounter').text(groupItems.length + ' unit dalam kelompok ini');

    groupItems.forEach(function(entry, rowIdx) {
        var it = entry.item;
        var oIdx = entry.originalIndex;
        var tr = $('<tr></tr>');
        tr.append('<td class="text-center align-middle font-weight-bold text-muted" style="width: 40px;">' + (rowIdx + 1) + '</td>');
        tr.append('<td><input type="text" class="form-control form-control-sm text-center edit-detail-field font-weight-bold" data-idx="' + oIdx + '" data-field="nup" value="' + escapeHtml(it.nup || '') + '" required></td>');
        
        var kondisiSelect = '<select class="form-control form-control-sm edit-detail-field" data-idx="' + oIdx + '" data-field="kondisi">' +
            '<option value="Baik"' + (it.kondisi === 'Baik' ? ' selected' : '') + '>Baik</option>' +
            '<option value="Rusak Ringan"' + (it.kondisi === 'Rusak Ringan' ? ' selected' : '') + '>Rusak Ringan</option>' +
            '<option value="Rusak Berat"' + (it.kondisi === 'Rusak Berat' ? ' selected' : '') + '>Rusak Berat</option>' +
            '</select>';
        tr.append('<td>' + kondisiSelect + '</td>');

        tr.append('<td><input type="text" class="form-control form-control-sm text-center edit-detail-field" data-idx="' + oIdx + '" data-field="tahun_perolehan" value="' + escapeHtml(it.tahun_perolehan || '') + '"></td>');

        tr.append('<td class="text-center align-middle"><button type="button" class="btn btn-outline-danger btn-xs btn-delete-single-unit" data-idx="' + oIdx + '" title="Hapus unit NUP ini"><i class="fas fa-times"></i></button></td>');

        tbody.append(tr);
    });

    $('.edit-detail-field').off('change keyup').on('change keyup', function() {
        var idx = $(this).data('idx');
        var field = $(this).data('field');
        if (selectedItems[idx]) {
            selectedItems[idx][field] = $(this).val();
        }
        renderItemsTable();
    });

    $('.btn-delete-single-unit').off('click').on('click', function() {
        var idx = $(this).data('idx');
        selectedItems.splice(idx, 1);
        renderGroupItemsDetailModal(gKey);
        renderItemsTable();
    });
}

// Parser input NUP (e.g. "1-5", "1, 2, 3", "10")
function parseNupInput(str) {
    var nups = [];
    var parts = str.split(/[,;\s]+/);
    parts.forEach(function(p) {
        p = p.trim();
        if (!p) return;
        var rangeMatch = p.match(/^(\d+)\s*[-–—]\s*(\d+)$/);
        if (rangeMatch) {
            var a = parseInt(rangeMatch[1], 10);
            var b = parseInt(rangeMatch[2], 10);
            for (var i = Math.min(a, b); i <= Math.max(a, b); i++) {
                nups.push(String(i));
            }
        } else {
            nups.push(p);
        }
    });
    return Array.from(new Set(nups));
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
});

// Helper NUP Range Formatter di Client-Side JS (Format Rapat e.g. "1-2")
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
            ranges.push(start === prev ? String(start) : (start + '-' + prev));
            start = nums[i];
            prev = nums[i];
        }
    }
    ranges.push(start === prev ? String(start) : (start + '-' + prev));

    return ranges.join(', ');
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
