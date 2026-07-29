<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
$appLogoRaw = $globalSetting['logo_url'] ?? $appSetting['app_logo_url'] ?? '';
$appLogoUrl = ! empty($appLogoRaw) ? media_url((string) $appLogoRaw) : site_url('assets/img/logo.png');
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-4">
                <h1 class="m-0 text-dark">
                    <i class="fas fa-sitemap text-primary mr-2"></i>Struktur Organisasi Satker
                </h1>
                <small class="text-muted">Bagan Piramida / Top-Down Chart Satuan Kerja Pelaksanaan Prasarana Strategis Riau</small>
            </div>
            <div class="col-sm-8 text-right d-flex align-items-center justify-content-end flex-wrap" style="gap: 6px;">
                
                <!-- Zoom Controls Group -->
                <div class="btn-group btn-group-sm shadow-sm" role="group" aria-label="Zoom Controls">
                    <button type="button" class="btn btn-light border" onclick="zoomOut()" title="Zoom Out (-)">
                        <i class="fas fa-search-minus text-secondary"></i>
                    </button>
                    <button type="button" class="btn btn-light border font-weight-bold" onclick="zoomReset()" title="Reset Zoom (100%)">
                        <span id="zoom-percentage-badge" class="badge badge-secondary px-2">100%</span>
                    </button>
                    <button type="button" class="btn btn-light border" onclick="zoomIn()" title="Zoom In (+)">
                        <i class="fas fa-search-plus text-secondary"></i>
                    </button>
                </div>

                <button type="button" class="btn btn-success btn-sm shadow-sm" onclick="openAddBatchModal()">
                    <i class="fas fa-user-plus mr-1"></i> Tambah Anggota Sekaligus
                </button>

                <button type="button" class="btn btn-primary btn-sm shadow-sm" onclick="openAddNodeModal()">
                    <i class="fas fa-plus-circle mr-1"></i> Tambah Posisi
                </button>

                <button type="button" class="btn btn-info btn-sm shadow-sm" id="btn-toggle-mode" onclick="toggleEditMode()">
                    <i class="fas fa-eye mr-1"></i> <span id="mode-text">Mode Preview</span>
                </button>

                <a href="<?= site_url('admin/master/struktur-organisasi/cetak-pdf'); ?>" target="_blank" class="btn btn-success btn-sm shadow-sm font-weight-bold">
                    <i class="fas fa-file-pdf mr-1"></i> Export / Cetak PDF Poster
                </a>

            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- Top Banner Header for Web View -->
        <div class="card shadow-sm border-0 mb-4 overflow-hidden top-web-banner" style="background: linear-gradient(135deg, #0f2027, #203a43, #2c5364); border-radius: 12px;">
            <div class="card-body p-4 text-center text-white position-relative">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <?php if (! empty($appLogoUrl)): ?>
                        <img src="<?= esc($appLogoUrl); ?>" alt="Logo Aplikasi" height="52" class="mr-3" style="object-fit: contain;" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');">
                        <i class="fas fa-shield-alt text-warning fa-2x mr-3 d-none"></i>
                    <?php else: ?>
                        <i class="fas fa-shield-alt text-warning fa-2x mr-3"></i>
                    <?php endif; ?>
                    <div>
                        <h4 class="font-weight-bold text-uppercase mb-0 tracking-wide" style="letter-spacing: 1.5px; color: #f8f9fa;">
                            SATUAN KERJA PELAKSANAAN PRASARANA STRATEGIS RIAU
                        </h4>
                        <span class="badge badge-warning px-3 py-1 font-weight-bold mt-1 text-uppercase" style="font-size: 0.85rem; letter-spacing: 1px;">
                            BAGAN STRUKTUR ORGANISASI TA <?= date('Y'); ?>
                        </span>
                    </div>
                </div>
                <p class="small text-white-50 mb-0">Integrasi Data Master Pegawai & Penunjukan Pejabat Struktur Organisasi Satker PPS. (Klik & tahan mouse untuk menggeser 2D grab canvas)</p>
            </div>
        </div>

        <!-- Org Chart Container -->
        <div class="card shadow-sm border-0 position-relative">
            
            <!-- Floating Zoom & Grab Hint Widget -->
            <div class="org-chart-floating-zoom position-absolute shadow-sm p-1 bg-white rounded-pill border" style="bottom: 20px; right: 30px; z-index: 10;">
                <span class="badge badge-light text-muted px-2 py-1 mr-1 border"><i class="fas fa-hand-rock text-primary mr-1"></i> Klik & Geser untuk Grab</span>
                <button type="button" class="btn btn-sm btn-light rounded-circle" onclick="zoomOut()" title="Zoom Out (-)">
                    <i class="fas fa-minus text-dark"></i>
                </button>
                <span id="zoom-floating-badge" class="font-weight-bold px-2 small text-dark">100%</span>
                <button type="button" class="btn btn-sm btn-light rounded-circle" onclick="zoomIn()" title="Zoom In (+)">
                    <i class="fas fa-plus text-dark"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle ml-1" onclick="zoomReset()" title="Reset (100%)">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>

            <!-- Chart Canvas with Grab-Pan Support & Clean Web Theme -->
            <div class="card-body p-0 overflow-auto position-relative" id="printable-org-chart-area" style="min-height: 600px;">
                <div class="org-tree-outer-container">
                    <div class="org-tree-wrapper" id="org-tree-wrapper">
                        <!-- Tree rendered via JS -->
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- Modal Form Node Struktur Organisasi -->
<div class="modal fade" id="modal-node-struktur" tabindex="-1" role="dialog" aria-labelledby="modalNodeTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="modalNodeTitle">
                    <i class="fas fa-sitemap mr-2"></i> Form Posisi / Node Bagan
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-node-struktur" onsubmit="submitFormNode(event)" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="node_id" name="id" value="">

                    <!-- Posisi Atasan / Parent -->
                    <div class="form-group mb-3">
                        <label for="node_parent_id" class="font-weight-bold text-dark small">Posisi Atasan (Parent Node)</label>
                        <select class="form-control" id="node_parent_id" name="parent_id">
                            <option value="">-- Posisi Paling Atas (Root Level 1) --</option>
                            <?php foreach ($treeNodes as $tn): ?>
                                <option value="<?= $tn['id']; ?>">
                                    <?= str_repeat('— ', max(0, ((int)$tn['level']) - 1)); ?>
                                    <?= esc($tn['jabatan_bagian']); ?>
                                    <?= !empty($tn['nama_pegawai']) ? ' (' . esc($tn['nama_pegawai']) . ')' : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">Pilih posisi atasan langsung di atas node ini.</small>
                    </div>

                    <!-- Judul Posisi / Jabatan Bagan -->
                    <div class="form-group mb-3">
                        <label for="node_jabatan_bagian" class="font-weight-bold text-dark small">Judul Posisi / Jabatan Bagan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="node_jabatan_bagian" name="jabatan_bagian" placeholder="Contoh: Kasatker Pelaksanaan Prasarana Strategis Riau" required>
                    </div>

                    <!-- Sumber Data Pejabat (Radio Option) -->
                    <div class="form-group mb-3 p-3 bg-light rounded border">
                        <label class="font-weight-bold text-dark small d-block mb-2">Sumber Data Pejabat / Pegawai:</label>
                        <div class="custom-control custom-radio custom-control-inline mr-4">
                            <input type="radio" id="source_master" name="source_type" value="master" class="custom-control-input" checked onclick="toggleSourceType('master')">
                            <label class="custom-control-label font-weight-bold text-primary" for="source_master">
                                <i class="fas fa-database mr-1"></i> Pilih dari Master Pegawai
                            </label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="source_manual" name="source_type" value="manual" class="custom-control-input" onclick="toggleSourceType('manual')">
                            <label class="custom-control-label font-weight-bold text-success" for="source_manual">
                                <i class="fas fa-user-edit mr-1"></i> Input Manual (Luar Master)
                            </label>
                        </div>
                    </div>

                    <!-- Option A: Pilih Pejabat dari Master Pegawai -->
                    <div class="form-group mb-3" id="group-source-master">
                        <label for="node_pegawai_id" class="font-weight-bold text-dark small">Pilih Pejabat (Master Pegawai)</label>
                        <select class="form-control select2-pegawai" id="node_pegawai_id" name="pegawai_id" style="width: 100%;">
                            <option value="">-- Belum Dipilih / Kosong --</option>
                            <?php foreach ($pegawaiList as $p): ?>
                                <option value="<?= $p['id']; ?>"
                                        data-foto="<?= esc($p['foto'] ?? ''); ?>"
                                        data-nip="<?= esc($p['nip']); ?>"
                                        data-jabatan="<?= esc($p['jabatan_label'] ?? ''); ?>">
                                    <?= esc($p['nama']); ?> <?= !empty($p['nip']) ? '(NIP. ' . esc($p['nip']) . ')' : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Option B: Input Manual (Luar Master) -->
                    <div id="group-source-manual" class="d-none">
                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="node_nama_manual" class="font-weight-bold text-dark small">Nama Pejabat (Manual)</label>
                                <input type="text" class="form-control" id="node_nama_manual" name="nama_manual" placeholder="Contoh: Ir. DODY HANGGODO, M.P.">
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label for="node_nip_manual" class="font-weight-bold text-dark small">NIP / Identitas / Jabatan Tambahan</label>
                                <input type="text" class="form-control" id="node_nip_manual" name="nip_manual" placeholder="Contoh: Menteri Pekerjaan Umum">
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="node_foto_manual_file" class="font-weight-bold text-dark small">Upload Foto Custom (Opsional)</label>
                            <input type="file" class="form-control-file" id="node_foto_manual_file" name="foto_manual_file" accept="image/*">
                            <small class="form-text text-muted">Format: JPG, PNG, WEBP. Maksimal 2MB.</small>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Kategori Kelompok -->
                        <div class="col-md-6 form-group mb-3">
                            <label for="node_kategori" class="font-weight-bold text-dark small">Kelompok / Pilar</label>
                            <select class="form-control" id="node_kategori" name="kategori_kelompok">
                                <option value="pimpinan">Pimpinan Utama / Dirjen / Menteri (Kartu Posisi Struktural)</option>
                                <option value="kasatker">Kasatker / Kepala Satker (Kartu Posisi Struktural)</option>
                                <option value="subbag">Kasubbag Umum & TU (Kartu Posisi Struktural)</option>
                                <option value="ppk">PPK Prasarana I / II / III (Kartu Posisi Struktural)</option>
                                <option value="bendahara">Bendahara & Pejabat Penguji SPM (Kartu Posisi Struktural)</option>
                                <option value="staf">Tim Teknis / Staf Pelaksana (Masuk Blok Tim)</option>
                                <option value="pendukung">Tim Pendukung Operasional (Security, Cleaning Service, Driver) (Masuk Blok Tim)</option>
                            </select>
                        </div>
                        <!-- Urutan Horizontal -->
                        <div class="col-md-6 form-group mb-3">
                            <label for="node_urutan" class="font-weight-bold text-dark small">Urutan Horizontal</label>
                            <input type="number" class="form-control" id="node_urutan" name="urutan" min="1" value="1">
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3" id="btn-save-node">
                        <i class="fas fa-save mr-1"></i> Simpan Node
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Form Batch / Tambah Beberapa Anggota Sekaligus -->
<div class="modal fade" id="modal-batch-struktur" tabindex="-1" role="dialog" aria-labelledby="modalBatchTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title font-weight-bold" id="modalBatchTitle">
                    <i class="fas fa-user-plus mr-2"></i> Tambah Beberapa Anggota Sekaligus (Blok Tim)
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-batch-struktur" onsubmit="submitFormBatch(event)">
                <div class="modal-body">

                    <!-- Posisi Atasan / Parent -->
                    <div class="form-group mb-3">
                        <label for="batch_parent_id" class="font-weight-bold text-dark small">Posisi Atasan (Parent Node) <span class="text-danger">*</span></label>
                        <select class="form-control" id="batch_parent_id" name="parent_id" required>
                            <option value="">-- Pilih Posisi Atasan (PPK / Kasubbag) --</option>
                            <?php foreach ($treeNodes as $tn): ?>
                                <option value="<?= $tn['id']; ?>">
                                    <?= str_repeat('— ', max(0, ((int)$tn['level']) - 1)); ?>
                                    <?= esc($tn['jabatan_bagian']); ?>
                                    <?= !empty($tn['nama_pegawai']) ? ' (' . esc($tn['nama_pegawai']) . ')' : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">Seluruh anggota yang dipilih akan dimasukkan ke dalam Blok Tim di bawah atasan ini.</small>
                    </div>

                    <div class="row">
                        <!-- Judul Posisi / Peran -->
                        <div class="col-md-6 form-group mb-3">
                            <label for="batch_jabatan_bagian" class="font-weight-bold text-dark small">Judul Posisi / Peran</label>
                            <input type="text" class="form-control" id="batch_jabatan_bagian" name="jabatan_bagian" value="Anggota" placeholder="Contoh: Anggota / Tim Teknis / Security">
                        </div>
                        <!-- Kategori Kelompok -->
                        <div class="col-md-6 form-group mb-3">
                            <label for="batch_kategori" class="font-weight-bold text-dark small">Kelompok / Pilar</label>
                            <select class="form-control" id="batch_kategori" name="kategori_kelompok">
                                <option value="staf" selected>Tim Teknis / Staf Pelaksana (Masuk Blok Tim)</option>
                                <option value="pendukung">Tim Pendukung Operasional (Security, Cleaning Service, Driver) (Masuk Blok Tim)</option>
                                <option value="ppk">PPK (Kartu Posisi Struktural)</option>
                                <option value="bendahara">Bendahara / SPM (Kartu Posisi Struktural)</option>
                                <option value="subbag">Kasubbag TU (Kartu Posisi Struktural)</option>
                            </select>
                        </div>
                    </div>

                    <!-- List Checkbox Pegawai -->
                    <div class="form-group mb-2">
                        <label class="font-weight-bold text-dark small d-flex justify-content-between align-items-center">
                            <span>Pilih Pegawai (Bisa Pilih Banyak):</span>
                            <div>
                                <button type="button" class="btn btn-xs btn-outline-primary" onclick="selectAllPegawaiBatch(true)">Pilih Semua</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="selectAllPegawaiBatch(false)">Hapus Semua</button>
                            </div>
                        </label>
                        <input type="text" class="form-control form-control-sm mb-2" id="search_pegawai_batch" placeholder="🔍 Cari Nama / NIP Pegawai..." onkeyup="filterPegawaiBatch()">
                        
                        <div class="p-3 border rounded bg-light" style="max-height: 260px; overflow-y: auto;" id="container_pegawai_batch">
                            <?php foreach ($pegawaiList as $p): ?>
                                <div class="custom-control custom-checkbox mb-2 pegawai-batch-item" data-search="<?= strtolower(esc($p['nama'] . ' ' . $p['nip'])); ?>">
                                    <input type="checkbox" class="custom-control-input chk-pegawai-batch" id="chk_p_<?= $p['id']; ?>" name="pegawai_ids[]" value="<?= $p['id']; ?>">
                                    <label class="custom-control-label font-weight-normal text-dark" for="chk_p_<?= $p['id']; ?>">
                                        <strong class="text-primary"><?= esc($p['nama']); ?></strong>
                                        <?= !empty($p['nip']) ? '<span class="text-muted small ml-1">(NIP. ' . esc($p['nip']) . ')</span>' : ''; ?>
                                        <?= !empty($p['jabatan_label']) ? '<span class="badge badge-light border ml-1">' . esc($p['jabatan_label']) . '</span>' : ''; ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm px-3" id="btn-save-batch">
                        <i class="fas fa-check-circle mr-1"></i> Simpan Semua Anggota
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detail Node & Pejabat Struktur -->
<div class="modal fade" id="modal-detail-node" tabindex="-1" role="dialog" aria-labelledby="modalDetailTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header text-white p-4 position-relative" style="background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);">
                <div class="d-flex align-items-center">
                    <div class="org-avatar-frame mr-3 mb-0" style="width: 80px; height: 80px; border-color: #d4af37; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">
                        <img id="detail_avatar_img" src="" alt="Avatar Pejabat" style="width:100%; height:100%; object-fit:cover;">
                        <i id="detail_avatar_icon" class="fas fa-user-tie fa-3x text-secondary d-none"></i>
                    </div>
                    <div>
                        <h4 class="font-weight-bold mb-1 text-white" id="detail_nama_pegawai">-</h4>
                        <span class="badge badge-warning px-3 py-1 font-weight-bold" id="detail_jabatan_bagian" style="font-size: 0.85rem;">-</span>
                        <div class="small text-white-50 mt-1" id="detail_nip_pegawai">-</div>
                    </div>
                </div>
                <button type="button" class="close text-white position-absolute" style="top: 16px; right: 20px;" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-id-card mr-2"></i> Informasi Kepegawaian</h6>
                                <table class="table table-sm table-borderless mb-0 small">
                                    <tr>
                                        <td class="text-muted" style="width: 40%;">Sumber Data:</td>
                                        <td class="font-weight-bold" id="detail_sumber_data">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Jenis Pegawai:</td>
                                        <td class="font-weight-bold" id="detail_jenis_pegawai">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Jabatan Utama:</td>
                                        <td class="font-weight-bold" id="detail_jabatan_master">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Golongan / Eselon:</td>
                                        <td class="font-weight-bold" id="detail_golongan_eselon">-</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-info mb-3"><i class="fas fa-sitemap mr-2"></i> Hirarki & Posisi Bagan</h6>
                                <table class="table table-sm table-borderless mb-0 small">
                                    <tr>
                                        <td class="text-muted" style="width: 40%;">Level Hirarki:</td>
                                        <td class="font-weight-bold" id="detail_level_hirarki">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Pilar / Kelompok:</td>
                                        <td class="font-weight-bold" id="detail_kategori_kelompok">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Atasan Langsung:</td>
                                        <td class="font-weight-bold text-dark" id="detail_atasan_langsung">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Jumlah Bawahan:</td>
                                        <td class="font-weight-bold text-success" id="detail_jumlah_bawahan">0 Bawahan</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Daftar Bawahan Langsung -->
                <div class="card border-0 shadow-sm mt-1">
                    <div class="card-body p-3">
                        <h6 class="font-weight-bold text-dark mb-2 small"><i class="fas fa-users-cog text-secondary mr-2"></i> Daftar Bawahan Langsung (Subordinate Direct)</h6>
                        <div id="detail_list_bawahan" class="small">
                            <!-- Populated via JS -->
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer bg-white">
                <button type="button" class="btn btn-primary btn-sm" id="btn-edit-from-detail">
                    <i class="fas fa-edit mr-1"></i> Edit Posisi Ini
                </button>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Clean Light Web Theme Canvas */
    #printable-org-chart-area {
        cursor: grab;
        user-select: none;
        position: relative;
        overflow: auto;
        height: 75vh;
        min-height: 580px;
        max-height: 850px;
        background: #f8fafc;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
    }

    #printable-org-chart-area.is-grabbing {
        cursor: grabbing !important;
        user-select: none !important;
    }

    .org-tree-outer-container {
        display: flex;
        justify-content: center;
        width: max-content;
        min-width: 100%;
        padding: 60px 120px 120px 120px;
    }

    .org-tree-wrapper {
        display: inline-flex;
        justify-content: center;
        transform-origin: top center;
        transition: transform 0.15s ease-out;
    }

    .org-node-tree {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    /* Container for children nodes under a parent */
    .org-node-children-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        padding-top: 24px;
    }

    .org-node-children-wrapper::before {
        content: '';
        position: absolute;
        top: 0;
        left: 50%;
        border-left: 2px solid #0056b3;
        width: 0;
        height: 24px;
    }

    .org-node-children {
        display: flex;
        justify-content: center;
        position: relative;
        padding-top: 24px;
    }

    /* Connecting line for single row children */
    .org-node-children::before {
        content: '';
        position: absolute;
        top: 0;
        left: 50%;
        border-left: 2px solid #0056b3;
        width: 0;
        height: 24px;
    }

    /* Vertical line extending down to multi-row chunked children */
    .org-node-children-row + .org-node-children-row {
        margin-top: 14px;
    }

    .org-node-children-row + .org-node-children-row::before {
        content: '';
        position: absolute;
        top: -14px;
        left: 50%;
        border-left: 2px solid #0056b3;
        width: 0;
        height: 38px;
    }

    .org-node-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        padding: 24px 14px 0 14px;
    }

    /* Top-most root node should not have top padding */
    .org-node-tree > .org-node-item {
        padding-top: 0;
    }

    /* Horizontal line bar and vertical stem into child cards */
    .org-node-item::before, .org-node-item::after {
        content: '';
        position: absolute;
        top: 0;
        height: 24px;
    }

    /* Left horizontal connector */
    .org-node-item::before {
        right: 50%;
        width: 50%;
        border-top: 2px solid #0056b3;
    }

    /* Right horizontal connector + Vertical stem into card */
    .org-node-item::after {
        left: 50%;
        width: 50%;
        border-top: 2px solid #0056b3;
        border-left: 2px solid #0056b3;
    }

    /* Remove left horizontal line for first child in a row */
    .org-node-item:first-child::before {
        border-top: none;
    }

    /* Remove right horizontal line for last child in a row */
    .org-node-item:last-child::after {
        border-top: none;
    }

    /* Single child (only child): no horizontal line, only vertical stem down */
    .org-node-item:only-child::before {
        border-top: none;
    }
    .org-node-item:only-child::after {
        border-top: none;
        border-left: 2px solid #0056b3;
    }

    /* Card Styling for Web View */
    .org-card {
        width: 230px;
        height: 235px;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        border: 2px solid #0056b3;
        overflow: hidden;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .org-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.18);
    }

    /* Pushed level for Group Block alongside Structural Cards to align horizontally with Level 5 */
    .org-node-item-pushed-level {
        padding-top: 259px !important; /* Push card down to Level 5 while keeping top horizontal connector line intact */
    }

    .org-node-item-pushed-level::before,
    .org-node-item-pushed-level::after {
        height: 259px !important; /* Vertical stem line extending seamlessly from Level 4 down to Level 5 card */
    }

    .org-card.level-1 {
        width: 260px;
        height: 245px;
        border-color: #d4af37; /* Gold accent */
        background: linear-gradient(180deg, #ffffff 0%, #fffdf5 100%);
    }

    .org-card.level-2, .org-card.level-3 {
        width: 240px;
        height: 235px;
        border-color: #1d72b8;
    }

    .org-card-clickable {
        cursor: pointer;
    }
    .org-card-clickable:hover .org-pegawai-nama {
        color: #0056b3;
    }

    .org-card-header {
        background: #0056b3;
        color: #ffffff;
        padding: 6px 10px;
        text-align: center;
        font-size: 0.78rem;
        font-weight: 700;
        text-uppercase: uppercase;
        line-height: 1.2;
        letter-spacing: 0.5px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .level-1 .org-card-header {
        background: linear-gradient(135deg, #1b2a4a, #2c3e50);
        color: #f1c40f;
    }

    .level-2 .org-card-header {
        background: #1e3c72;
    }

    .org-card-body {
        padding: 8px 10px;
        text-align: center;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .org-avatar-frame {
        width: 68px;
        height: 68px;
        margin: 0 auto 6px auto;
        border-radius: 50%;
        overflow: hidden;
        border: 3px solid #0056b3;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        background: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .level-1 .org-avatar-frame {
        width: 80px;
        height: 80px;
        border-color: #d4af37;
    }

    .org-avatar-frame img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .org-pegawai-nama {
        font-size: 0.82rem;
        font-weight: 700;
        color: #212529;
        margin-bottom: 2px;
        line-height: 1.2;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .org-pegawai-nip {
        font-size: 0.7rem;
        color: #6c757d;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }

    .org-card-footer-actions {
        padding: 4px 8px;
        display: flex;
        justify-content: center;
        gap: 6px;
        background: #f8f9fa;
        border-top: 1px solid #e9ecef;
        flex-shrink: 0;
    }

    /* GROUP BLOCK CARD FOR TEAM MEMBERS - DYNAMIC HEIGHT & UNIFORM GRID */
    .org-card.org-group-block {
        width: 620px;
        max-width: 100%;
        height: auto !important;
        min-height: 140px;
        border: 2px solid #1e3c72;
        background: #f8fafc;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        overflow: hidden !important;
        display: flex;
        flex-direction: column;
        box-sizing: border-box;
    }

    .org-card.org-group-block-pendukung {
        border-color: #374151 !important;
        background: #f9fafb !important;
    }

    .org-group-header {
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        color: #ffffff;
        padding: 8px 14px;
        font-weight: 700;
        font-size: 0.8rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        letter-spacing: 0.5px;
        text-uppercase: uppercase;
        height: 44px;
        flex-shrink: 0;
        box-sizing: border-box;
    }

    .org-group-body {
        padding: 14px;
        flex-grow: 1;
        box-sizing: border-box;
    }

    .org-group-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        width: 100%;
        box-sizing: border-box;
    }

    .org-member-card {
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        border-radius: 10px;
        padding: 8px 12px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        box-sizing: border-box;
        width: 100%;
        overflow: hidden;
    }

    .org-member-card:hover {
        border-color: #0056b3;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .org-member-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        overflow: hidden;
        border: 2px solid #d4af37;
        margin-right: 10px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #e9ecef;
        position: relative;
    }

    .org-member-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .org-member-details {
        flex: 1 1 auto;
        min-width: 0;
        padding-right: 4px;
    }

    .org-member-nama {
        font-weight: 700;
        font-size: 0.78rem;
        color: #1e293b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.2;
    }

    .org-member-title {
        font-size: 0.7rem;
        color: #64748b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .org-member-actions {
        display: flex;
        gap: 2px;
        flex-shrink: 0;
    }
</style>

<script>
    let rawNodesData = <?= json_encode($treeNodes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    let isEditMode = true;

    // Zoom State
    let currentZoom = 1.0;
    const minZoom = 0.3;
    const maxZoom = 2.0;
    const zoomStep = 0.15;

    // Drag-to-Pan (Grab) State
    let isMouseDown = false;
    let startX = 0;
    let startY = 0;
    let scrollLeftStart = 0;
    let scrollTopStart = 0;
    let isDraggingHandled = false;

    document.addEventListener('DOMContentLoaded', function () {
        renderOrgChart();

        const chartArea = document.getElementById('printable-org-chart-area');
        if (chartArea) {
            // Wheel Zoom (Ctrl/Cmd + Wheel)
            chartArea.addEventListener('wheel', function(e) {
                if (e.ctrlKey || e.metaKey) {
                    e.preventDefault();
                    if (e.deltaY < 0) {
                        zoomIn();
                    } else {
                        zoomOut();
                    }
                }
            }, { passive: false });

            // 2D Drag to Pan (Grab Horizontal & Vertikal)
            chartArea.addEventListener('mousedown', function (e) {
                if (e.button !== 0) return; // Left click only
                if (e.target.closest('button') || e.target.closest('input') || e.target.closest('select')) {
                    return;
                }

                isMouseDown = true;
                isDraggingHandled = false;
                chartArea.classList.add('is-grabbing');

                startX = e.clientX;
                startY = e.clientY;
                scrollLeftStart = chartArea.scrollLeft;
                scrollTopStart = chartArea.scrollTop;
            });

            document.addEventListener('mousemove', function (e) {
                if (!isMouseDown) return;

                const walkX = e.clientX - startX;
                const walkY = e.clientY - startY;

                if (Math.abs(walkX) > 3 || Math.abs(walkY) > 3) {
                    isDraggingHandled = true;
                }

                chartArea.scrollLeft = scrollLeftStart - walkX;
                chartArea.scrollTop = scrollTopStart - walkY;
            });

            document.addEventListener('mouseup', function () {
                if (isMouseDown) {
                    isMouseDown = false;
                    chartArea.classList.remove('is-grabbing');
                }
            });
        }
    });

    function zoomIn() {
        if (currentZoom < maxZoom) {
            currentZoom = Math.min(maxZoom, currentZoom + zoomStep);
            applyZoom();
        }
    }

    function zoomOut() {
        if (currentZoom > minZoom) {
            currentZoom = Math.max(minZoom, currentZoom - zoomStep);
            applyZoom();
        }
    }

    function zoomReset() {
        currentZoom = 1.0;
        applyZoom();
    }

    function applyZoom() {
        const wrapper = document.getElementById('org-tree-wrapper');
        if (wrapper) {
            wrapper.style.transform = `scale(${currentZoom})`;
        }
        const percentStr = Math.round(currentZoom * 100) + '%';
        const badgeHeader = document.getElementById('zoom-percentage-badge');
        if (badgeHeader) badgeHeader.textContent = percentStr;
        const badgeFloating = document.getElementById('zoom-floating-badge');
        if (badgeFloating) badgeFloating.textContent = percentStr;

        centerChartScroll();
    }

    function centerChartScroll() {
        const chartArea = document.getElementById('printable-org-chart-area');
        if (chartArea) {
            setTimeout(() => {
                const scrollMax = chartArea.scrollWidth - chartArea.clientWidth;
                if (scrollMax > 0) {
                    chartArea.scrollLeft = scrollMax / 2;
                }
            }, 100);
        }
    }

    function toggleSourceType(type) {
        if (type === 'manual') {
            document.getElementById('group-source-master').classList.add('d-none');
            document.getElementById('group-source-manual').classList.remove('d-none');
        } else {
            document.getElementById('group-source-manual').classList.add('d-none');
            document.getElementById('group-source-master').classList.remove('d-none');
        }
    }

    function renderOrgChart() {
        const wrapper = document.getElementById('org-tree-wrapper');
        if (!wrapper) return;

        if (!rawNodesData || rawNodesData.length === 0) {
            wrapper.innerHTML = `
                <div class="text-center text-muted my-5 py-5">
                    <i class="fas fa-sitemap fa-3x mb-3 text-secondary"></i>
                    <h5>Bagan Struktur Organisasi Masih Kosong</h5>
                    <p class="small">Klik tombol <strong>"Tambah Posisi"</strong> di atas untuk membuat struktur organisasi.</p>
                </div>
            `;
            return;
        }

        // Build tree map
        const nodeMap = {};
        rawNodesData.forEach(node => {
            nodeMap[node.id] = { ...node, children: [] };
        });

        const rootNodes = [];
        rawNodesData.forEach(node => {
            if (node.parent_id && nodeMap[node.parent_id]) {
                nodeMap[node.parent_id].children.push(nodeMap[node.id]);
            } else {
                rootNodes.push(nodeMap[node.id]);
            }
        });

        let html = '<div class="org-node-tree">';
        rootNodes.forEach(root => {
            html += buildNodeHtml(root);
        });
        html += '</div>';

        wrapper.innerHTML = html;
        applyZoom();
    }

    function buildNodeHtml(node) {
        const hasChildren = node.children && node.children.length > 0;
        const levelClass = 'level-' + (node.level || 1);

        let avatarHtml = '';
        if (node.foto_pegawai && String(node.foto_pegawai).trim() !== '') {
            const fotoClean = String(node.foto_pegawai).replace(/^\/+/, '');
            const fotoUrl = '<?= site_url('/'); ?>' + fotoClean;
            avatarHtml = `
                <img src="${escapeHtml(fotoUrl)}" alt="" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.classList.remove('d-none');">
                <i class="fas fa-user-tie fa-2x text-secondary d-none"></i>
            `;
        } else {
            avatarHtml = `<i class="fas fa-user-tie fa-2x text-secondary"></i>`;
        }

        let editButtonsHtml = '';
        if (isEditMode) {
            editButtonsHtml = `
                <div class="org-card-footer-actions" onclick="event.stopPropagation();">
                    <button type="button" class="btn btn-xs btn-outline-success" onclick="openAddBatchModal(${node.id})" title="Tambah Anggota Sekaligus (Blok Tim)">
                        <i class="fas fa-user-plus"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-primary" onclick="openAddNodeModal(${node.id})" title="Tambah Sub-Node (Posisi Baru)">
                        <i class="fas fa-plus"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-info" onclick="openEditNodeModal(${node.id})" title="Edit Posisi">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-danger" onclick="confirmDeleteNode(${node.id})" title="Hapus Posisi">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
        }

        let nodeHtml = `
            <div class="org-node-item">
                <div class="org-card ${levelClass} org-card-clickable" onclick="openDetailNodeModal(${node.id})">
                    <div class="org-card-header">
                        ${escapeHtml(node.jabatan_bagian)}
                    </div>
                    <div class="org-card-body">
                        <div class="org-avatar-frame">
                            ${avatarHtml}
                        </div>
                        <div class="org-pegawai-nama">
                            ${escapeHtml(node.nama_pegawai || '(Belum Ditentukan)')}
                        </div>
                        <div class="org-pegawai-nip">
                            ${node.nip_pegawai ? (node.nip_pegawai.toLowerCase().includes('menteri') || node.nip_pegawai.toLowerCase().includes('direktur') || isNaN(node.nip_pegawai) ? escapeHtml(node.nip_pegawai) : 'NIP. ' + escapeHtml(node.nip_pegawai)) : (node.nama_jabatan_master ? escapeHtml(node.nama_jabatan_master) : 'Satker PPS Riau')}
                        </div>
                    </div>
                    ${editButtonsHtml}
                </div>
        `;

        if (hasChildren) {
            node.children.sort((a, b) => (parseInt(a.urutan) || 1) - (parseInt(b.urutan) || 1));

            // Separate main structural sub-nodes vs leaf team members
            const mainSubNodes = [];
            const leafMembers = [];

            node.children.forEach(child => {
                const titleLower = (child.jabatan_bagian || '').toLowerCase();
                const katLower = (child.kategori_kelompok || '').toLowerCase();
                const childHasChildren = child.children && child.children.length > 0;

                const isTeamCategory = katLower === 'staf' || katLower === 'pendukung';
                const isTeamTitle = titleLower.startsWith('anggota') ||
                                    titleLower.includes('security') ||
                                    titleLower.includes('satpam') ||
                                    titleLower.includes('cleaning') ||
                                    titleLower.includes('driver') ||
                                    titleLower.includes('sopir') ||
                                    titleLower.includes('ob ');

                const isStructuralKeyword = titleLower.includes('ppk') ||
                                            titleLower.includes('bendahara') ||
                                            titleLower.includes('spm') ||
                                            titleLower.includes('penguji') ||
                                            titleLower.includes('kasatker') ||
                                            titleLower.includes('kasubbag') ||
                                            titleLower.includes('direktur') ||
                                            titleLower.includes('dirjen') ||
                                            titleLower.includes('menteri') ||
                                            titleLower.includes('kabag') ||
                                            titleLower.includes('kepala');

                // Structural Head Card if it has sub-children, OR has structural keyword, OR is not a team category/title
                const isStructuralHead = childHasChildren || isStructuralKeyword || (!isTeamCategory && !isTeamTitle);

                if (isStructuralHead) {
                    mainSubNodes.push(child);
                } else {
                    leafMembers.push(child);
                }
            });

            nodeHtml += '<div class="org-node-children-wrapper">';
            nodeHtml += '<div class="org-node-children org-node-children-row">';

            // 1. Render main structural sub-nodes (PPK I, Bendahara, SPM, etc.)
            mainSubNodes.forEach(c => {
                nodeHtml += buildNodeHtml(c);
            });

            // 2. Render direct leaf team members grouped in a single GROUP BLOCK PANEL alongside sub-nodes
            if (leafMembers.length > 0) {
                const hasMainSubNodes = mainSubNodes.length > 0;
                nodeHtml += buildGroupBlockHtml(node, leafMembers, hasMainSubNodes);
            }

            nodeHtml += '</div>';
            nodeHtml += '</div>';
        }

        nodeHtml += '</div>';
        return nodeHtml;
    }

    function getGroupBlockTitle(members, defaultTitle) {
        if (!members || members.length === 0) return defaultTitle;
        const titles = members.map(m => (m.jabatan_bagian || '').trim()).filter(t => t !== '');
        if (titles.length === 0) return defaultTitle;

        const uniqueTitles = [...new Set(titles)];
        if (uniqueTitles.length === 1 && uniqueTitles[0].toLowerCase() !== 'anggota') {
            return uniqueTitles[0];
        }
        return defaultTitle;
    }

    function promptEditGroupTitle(nodeIdsStr, currentTitle) {
        if (!nodeIdsStr) return;
        const nodeIds = String(nodeIdsStr).split(',').map(n => parseInt(n.trim())).filter(n => n > 0);
        if (nodeIds.length === 0) return;

        Swal.fire({
            title: 'Edit Judul Kelompok Tim',
            input: 'text',
            inputValue: currentTitle,
            inputLabel: 'Ubah nama/judul untuk kelompok tim ini:',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-save mr-1"></i> Simpan Judul',
            cancelButtonText: 'Batal',
            inputValidator: (value) => {
                if (!value || !value.trim()) {
                    return 'Judul kelompok tidak boleh kosong!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                const formData = new FormData();
                nodeIds.forEach(id => formData.append('node_ids[]', id));
                formData.append('judul_baru', result.value.trim());

                fetch('<?= site_url('admin/master/struktur-organisasi/update-judul-kelompok'); ?>', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        reloadChartData();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: data.message || 'Gagal mengubah judul kelompok.'
                        });
                    }
                })
                .catch(err => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Koneksi server terputus.'
                    });
                });
            }
        });
    }

    function buildGroupBlockHtml(parentNode, leafMembers, hasMainSubNodes = false) {
        const teknisMembers = [];
        const pendukungMembers = [];
        const itemClass = hasMainSubNodes ? 'org-node-item org-node-item-pushed-level' : 'org-node-item';

        leafMembers.forEach(m => {
            const titleLower = (m.jabatan_bagian || '').toLowerCase();
            const katLower = (m.kategori_kelompok || '').toLowerCase();

            const isPendukung = katLower === 'pendukung' ||
                titleLower.includes('security') ||
                titleLower.includes('satpam') ||
                titleLower.includes('keamanan') ||
                titleLower.includes('cleaning') ||
                titleLower.includes('kebersihan') ||
                titleLower.includes('cs') ||
                titleLower.includes('driver') ||
                titleLower.includes('sopir') ||
                titleLower.includes('pengemudi') ||
                titleLower.includes('ob') ||
                titleLower.includes('office boy') ||
                titleLower.includes('penjaga') ||
                titleLower.includes('pramusaji') ||
                titleLower.includes('pendukung') ||
                titleLower.includes('teknisi');

            if (isPendukung) {
                pendukungMembers.push(m);
            } else {
                teknisMembers.push(m);
            }
        });

        const teknisTitle = getGroupBlockTitle(teknisMembers, 'TIM TEKNIS & STAF PELAKSANA');
        const pendukungTitle = getGroupBlockTitle(pendukungMembers, 'TIM PENDUKUNG OPERASIONAL');
        const teknisIds = teknisMembers.map(m => m.id).join(',');
        const pendukungIds = pendukungMembers.map(m => m.id).join(',');

        // 1. If BOTH Teknis & Pendukung members exist: render 2 separate Block Cards connected by vertical tree stem
        if (teknisMembers.length > 0 && pendukungMembers.length > 0) {
            return `
                <div class="${itemClass}">
                    <!-- Block 1: Tim Teknis & Staf -->
                    <div class="org-card org-group-block shadow-sm">
                        <div class="org-group-header">
                            <span>
                                <i class="fas fa-user-gear mr-2"></i> ${escapeHtml(teknisTitle)} (${teknisMembers.length})
                                ${isEditMode ? `<button type="button" class="btn btn-xs btn-outline-light font-weight-bold px-2 ml-2" onclick="event.stopPropagation(); promptEditGroupTitle('${teknisIds}', '${escapeHtml(teknisTitle)}')"><i class="fas fa-pen mr-1"></i> Edit Judul</button>` : ''}
                            </span>
                            ${isEditMode ? `<button type="button" class="btn btn-xs btn-light text-primary font-weight-bold px-2" onclick="event.stopPropagation(); openAddBatchModal(${parentNode.id})"><i class="fas fa-plus mr-1"></i> Tambah Anggota</button>` : ''}
                        </div>
                        <div class="org-group-body">
                            <div class="org-group-grid">
                                ${teknisMembers.map(m => renderMemberCardItemHtml(m)).join('')}
                            </div>
                        </div>
                    </div>

                    <!-- Vertical Connector Line down to Block 2 -->
                    <div class="org-node-children-wrapper">
                        <div class="org-node-children org-node-children-row">
                            <div class="org-node-item">
                                <!-- Block 2: Tim Pendukung Operasional (Security, Cleaning Service, Driver) -->
                                <div class="org-card org-group-block org-group-block-pendukung shadow-sm">
                                    <div class="org-group-header bg-dark text-warning border-bottom border-warning">
                                        <span>
                                            <i class="fas fa-shield-alt text-warning mr-2"></i> ${escapeHtml(pendukungTitle)} (${pendukungMembers.length})
                                            ${isEditMode ? `<button type="button" class="btn btn-xs btn-outline-warning font-weight-bold px-2 ml-2" onclick="event.stopPropagation(); promptEditGroupTitle('${pendukungIds}', '${escapeHtml(pendukungTitle)}')"><i class="fas fa-pen mr-1"></i> Edit Judul</button>` : ''}
                                        </span>
                                        ${isEditMode ? `<button type="button" class="btn btn-xs btn-outline-warning font-weight-bold px-2" onclick="event.stopPropagation(); openAddBatchModal(${parentNode.id})"><i class="fas fa-plus mr-1"></i> Tambah Anggota</button>` : ''}
                                    </div>
                                    <div class="org-group-body">
                                        <div class="org-group-grid">
                                            ${pendukungMembers.map(m => renderMemberCardItemHtml(m)).join('')}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            `;
        }

        // 2. If ONLY Pendukung members exist
        if (pendukungMembers.length > 0) {
            return `
                <div class="${itemClass}">
                    <div class="org-card org-group-block org-group-block-pendukung shadow-sm">
                        <div class="org-group-header bg-dark text-warning border-bottom border-warning">
                            <span>
                                <i class="fas fa-shield-alt text-warning mr-2"></i> ${escapeHtml(pendukungTitle)} (${pendukungMembers.length})
                                ${isEditMode ? `<button type="button" class="btn btn-xs btn-outline-warning font-weight-bold px-2 ml-2" onclick="event.stopPropagation(); promptEditGroupTitle('${pendukungIds}', '${escapeHtml(pendukungTitle)}')"><i class="fas fa-pen mr-1"></i> Edit Judul</button>` : ''}
                            </span>
                            ${isEditMode ? `<button type="button" class="btn btn-xs btn-outline-warning font-weight-bold px-2" onclick="event.stopPropagation(); openAddBatchModal(${parentNode.id})"><i class="fas fa-plus mr-1"></i> Tambah Anggota</button>` : ''}
                        </div>
                        <div class="org-group-body">
                            <div class="org-group-grid">
                                ${pendukungMembers.map(m => renderMemberCardItemHtml(m)).join('')}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        return `
            <div class="${itemClass}">
                <div class="org-card org-group-block shadow-sm">
                    <div class="org-group-header">
                        <span>
                            <i class="fas fa-users mr-2"></i> ${escapeHtml(teknisTitle)} (${teknisMembers.length})
                            ${isEditMode ? `<button type="button" class="btn btn-xs btn-outline-light font-weight-bold px-2 ml-2" onclick="event.stopPropagation(); promptEditGroupTitle('${teknisIds}', '${escapeHtml(teknisTitle)}')"><i class="fas fa-pen mr-1"></i> Edit Judul</button>` : ''}
                        </span>
                        ${isEditMode ? `<button type="button" class="btn btn-xs btn-light text-primary font-weight-bold px-2" onclick="event.stopPropagation(); openAddBatchModal(${parentNode.id})"><i class="fas fa-plus mr-1"></i> Tambah Anggota</button>` : ''}
                    </div>
                    <div class="org-group-body">
                        <div class="org-group-grid">
                            ${teknisMembers.map(m => renderMemberCardItemHtml(m)).join('')}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function renderMemberCardItemHtml(m) {
        let avatarHtml = '';
        if (m.foto_pegawai && String(m.foto_pegawai).trim() !== '') {
            const fotoClean = String(m.foto_pegawai).replace(/^\/+/, '');
            const fotoUrl = '<?= site_url('/'); ?>' + fotoClean;
            avatarHtml = `
                <img src="${escapeHtml(fotoUrl)}" alt="" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.classList.remove('d-none');">
                <i class="fas fa-user-tie text-secondary d-none"></i>
            `;
        } else {
            avatarHtml = `<i class="fas fa-user-tie text-secondary"></i>`;
        }

        return `
            <div class="org-member-card" onclick="openDetailNodeModal(${m.id})">
                <div class="org-member-avatar">
                    ${avatarHtml}
                </div>
                <div class="org-member-details">
                    <div class="org-member-nama" title="${escapeHtml(m.nama_pegawai)}">${escapeHtml(m.nama_pegawai || '(Belum Ditentukan)')}</div>
                    <div class="org-member-title">${escapeHtml(m.jabatan_bagian)}</div>
                </div>
                ${isEditMode ? `
                    <div class="org-member-actions" onclick="event.stopPropagation();">
                        <button type="button" class="btn btn-xs btn-link text-info p-0 px-1" onclick="openEditNodeModal(${m.id})" title="Edit"><i class="fas fa-edit"></i></button>
                        <button type="button" class="btn btn-xs btn-link text-danger p-0 px-1" onclick="confirmDeleteNode(${m.id})" title="Hapus"><i class="fas fa-trash"></i></button>
                    </div>
                ` : ''}
            </div>
        `;
    }

    function openDetailNodeModal(nodeId) {
        if (isDraggingHandled) {
            isDraggingHandled = false;
            return;
        }

        const node = rawNodesData.find(n => parseInt(n.id) === parseInt(nodeId));
        if (!node) return;

        // Avatar
        const fotoUrl = node.foto_pegawai ? '<?= site_url('/'); ?>' + node.foto_pegawai : '';
        const avatarImg = document.getElementById('detail_avatar_img');
        const avatarIcon = document.getElementById('detail_avatar_icon');
        if (fotoUrl) {
            avatarImg.src = fotoUrl;
            avatarImg.classList.remove('d-none');
            avatarIcon.classList.add('d-none');
        } else {
            avatarImg.classList.add('d-none');
            avatarIcon.classList.remove('d-none');
        }

        document.getElementById('detail_nama_pegawai').textContent = node.nama_pegawai || '(Belum Ditentukan)';
        document.getElementById('detail_jabatan_bagian').textContent = node.jabatan_bagian || '-';
        document.getElementById('detail_nip_pegawai').textContent = node.nip_pegawai ? (isNaN(node.nip_pegawai) ? node.nip_pegawai : 'NIP. ' + node.nip_pegawai) : 'Satker PPS Riau';

        // Kepegawaian
        document.getElementById('detail_sumber_data').innerHTML = node.pegawai_id ? '<span class="badge badge-primary"><i class="fas fa-database mr-1"></i> Master Pegawai</span>' : '<span class="badge badge-success"><i class="fas fa-user-edit mr-1"></i> Input Manual</span>';
        document.getElementById('detail_jenis_pegawai').textContent = (node.jenis_pegawai || 'Pejabat / Staf Satker').replace(/_/g, ' ').toUpperCase();
        document.getElementById('detail_jabatan_master').textContent = node.nama_jabatan_master || node.jabatan_bagian || '-';
        document.getElementById('detail_golongan_eselon').textContent = `${node.golongan || '-'} / ${node.eselon || '-'}`;

        // Hirarki
        document.getElementById('detail_level_hirarki').textContent = `Level ${node.level || 1}`;
        document.getElementById('detail_kategori_kelompok').textContent = (node.kategori_kelompok || 'utama').toUpperCase();

        // Atasan Info
        const parentNode = rawNodesData.find(p => parseInt(p.id) === parseInt(node.parent_id));
        if (parentNode) {
            document.getElementById('detail_atasan_langsung').textContent = `${parentNode.jabatan_bagian} (${parentNode.nama_pegawai || 'Kosong'})`;
        } else {
            document.getElementById('detail_atasan_langsung').textContent = '— (Pimpinan Tertinggi / Root Node)';
        }

        // Bawahan Info
        const directChildren = rawNodesData.filter(c => parseInt(c.parent_id) === parseInt(node.id));
        document.getElementById('detail_jumlah_bawahan').textContent = `${directChildren.length} Posisi Bawahan`;

        const listBawahanEl = document.getElementById('detail_list_bawahan');
        if (directChildren.length === 0) {
            listBawahanEl.innerHTML = '<span class="text-muted font-italic">Tidak memiliki bawahan langsung.</span>';
        } else {
            let bHtml = '<ul class="list-group list-group-flush mb-0">';
            directChildren.forEach(child => {
                bHtml += `
                    <li class="list-group-item px-0 py-2 bg-transparent d-flex justify-content-between align-items-center border-bottom">
                        <div>
                            <strong class="text-dark d-block">${escapeHtml(child.jabatan_bagian)}</strong>
                            <span class="text-muted small"><i class="fas fa-user mr-1"></i> ${escapeHtml(child.nama_pegawai || 'Belum diisi')}</span>
                        </div>
                        <button type="button" class="btn btn-xs btn-outline-info" onclick="$('#modal-detail-node').modal('hide'); setTimeout(() => openDetailNodeModal(${child.id}), 300);">
                            Detail <i class="fas fa-chevron-right ml-1"></i>
                        </button>
                    </li>
                `;
            });
            bHtml += '</ul>';
            listBawahanEl.innerHTML = bHtml;
        }

        // Set Edit action button
        document.getElementById('btn-edit-from-detail').onclick = function() {
            $('#modal-detail-node').modal('hide');
            openEditNodeModal(node.id);
        };

        $('#modal-detail-node').modal('show');
    }

    function toggleEditMode() {
        isEditMode = !isEditMode;
        const modeText = document.getElementById('mode-text');
        const btnToggle = document.getElementById('btn-toggle-mode');

        if (isEditMode) {
            modeText.textContent = 'Mode Preview';
            btnToggle.className = 'btn btn-info btn-sm shadow-sm';
        } else {
            modeText.textContent = 'Mode Edit';
            btnToggle.className = 'btn btn-warning btn-sm shadow-sm';
        }
        renderOrgChart();
    }

    function openAddNodeModal(parentId = null) {
        document.getElementById('form-node-struktur').reset();
        document.getElementById('node_id').value = '';
        document.getElementById('modalNodeTitle').innerHTML = '<i class="fas fa-plus-circle mr-2"></i> Tambah Posisi / Node Baru';
        
        document.getElementById('source_master').checked = true;
        toggleSourceType('master');

        if (parentId) {
            document.getElementById('node_parent_id').value = parentId;
        }

        $('#modal-node-struktur').modal('show');
    }

    function openAddBatchModal(parentId = null) {
        document.getElementById('form-batch-struktur').reset();
        selectAllPegawaiBatch(false);

        if (parentId) {
            document.getElementById('batch_parent_id').value = parentId;
        }

        $('#modal-batch-struktur').modal('show');
    }

    function selectAllPegawaiBatch(check) {
        const checkboxes = document.querySelectorAll('.chk-pegawai-batch');
        checkboxes.forEach(chk => {
            chk.checked = check;
        });
    }

    function filterPegawaiBatch() {
        const query = (document.getElementById('search_pegawai_batch').value || '').toLowerCase();
        const items = document.querySelectorAll('.pegawai-batch-item');
        items.forEach(item => {
            const text = item.getAttribute('data-search') || '';
            if (text.includes(query)) {
                item.classList.remove('d-none');
            } else {
                item.classList.add('d-none');
            }
        });
    }

    function openEditNodeModal(nodeId) {
        const node = rawNodesData.find(n => parseInt(n.id) === parseInt(nodeId));
        if (!node) return;

        document.getElementById('form-node-struktur').reset();
        document.getElementById('node_id').value = node.id;
        document.getElementById('node_parent_id').value = node.parent_id || '';
        document.getElementById('node_jabatan_bagian').value = node.jabatan_bagian || '';
        document.getElementById('node_kategori').value = node.kategori_kelompok || 'utama';
        document.getElementById('node_urutan').value = node.urutan || 1;

        if (node.pegawai_id) {
            document.getElementById('source_master').checked = true;
            document.getElementById('node_pegawai_id').value = node.pegawai_id || '';
            toggleSourceType('master');
        } else {
            document.getElementById('source_manual').checked = true;
            document.getElementById('node_nama_manual').value = node.nama_manual || '';
            document.getElementById('node_nip_manual').value = node.nip_manual || '';
            toggleSourceType('manual');
        }

        document.getElementById('modalNodeTitle').innerHTML = '<i class="fas fa-edit mr-2"></i> Edit Posisi / Node Bagan';
        $('#modal-node-struktur').modal('show');
    }

    function submitFormNode(e) {
        e.preventDefault();
        const form = document.getElementById('form-node-struktur');
        const formData = new FormData(form);

        const btnSave = document.getElementById('btn-save-node');
        btnSave.disabled = true;
        btnSave.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...';

        fetch('<?= site_url('admin/master/struktur-organisasi/simpan'); ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            btnSave.disabled = false;
            btnSave.innerHTML = '<i class="fas fa-save mr-1"></i> Simpan Node';

            if (data.status === 'success') {
                $('#modal-node-struktur').modal('hide');
                
                // Show single notification using SweetAlert2
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: data.message,
                    timer: 1800,
                    showConfirmButton: false
                });

                // Refresh data
                reloadChartData();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: data.message || 'Terjadi kesalahan saat menyimpan data.'
                });
            }
        })
        .catch(err => {
            btnSave.disabled = false;
            btnSave.innerHTML = '<i class="fas fa-save mr-1"></i> Simpan Node';
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Koneksi ke server terputus/gagal.'
            });
        });
    }

    function submitFormBatch(e) {
        e.preventDefault();
        const form = document.getElementById('form-batch-struktur');
        const formData = new FormData(form);

        const btnSave = document.getElementById('btn-save-batch');
        btnSave.disabled = true;
        btnSave.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...';

        fetch('<?= site_url('admin/master/struktur-organisasi/simpan-batch'); ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            btnSave.disabled = false;
            btnSave.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Simpan Semua Anggota';

            if (data.status === 'success') {
                $('#modal-batch-struktur').modal('hide');
                
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                });

                reloadChartData();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: data.message || 'Gagal menyimpan anggota sekaligus.'
                });
            }
        })
        .catch(err => {
            btnSave.disabled = false;
            btnSave.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Simpan Semua Anggota';
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Koneksi ke server terputus/gagal.'
            });
        });
    }

    function confirmDeleteNode(nodeId) {
        Swal.fire({
            title: 'Hapus Node Posisi?',
            text: "Node posisi bagan ini akan dihapus. Jika memiliki bawahan, hirarki akan menyesuaikan.",
            icon: 'warning',
            showCancelButton: true,
            confirmColor: '#d33',
            cancelColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('id', nodeId);

                fetch('<?= site_url('admin/master/struktur-organisasi/hapus'); ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            text: data.message,
                            timer: 1800,
                            showConfirmButton: false
                        });
                        reloadChartData();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: data.message || 'Gagal menghapus node.'
                        });
                    }
                });
            }
        });
    }

    function reloadChartData() {
        fetch('<?= site_url('admin/master/struktur-organisasi/get-chart-data'); ?>')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                rawNodesData = data.nodes || [];
                renderOrgChart();
            }
        });
    }

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>
<?= $this->endSection(); ?>
