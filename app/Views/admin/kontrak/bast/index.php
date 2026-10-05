<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid px-0">
    <!-- Header Card -->
    <div class="card shadow-sm border-0 mb-3" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 py-3 d-flex flex-wrap align-items-center justify-content-between">
            <div>
                <h4 class="font-weight-bold text-dark mb-1">
                    <i class="fas fa-file-contract text-primary mr-2"></i>Berita Acara Serah Terima (BAST)
                </h4>
                <small class="text-muted">
                    Pengelolaan dan penerbitan berkas resmi Berita Acara Serah Terima Hasil Pekerjaan Jasa Konsultansi &amp; Fisik Konstruksi
                </small>
            </div>
            <div class="mt-2 mt-md-0 d-flex flex-wrap align-items-center" style="gap: 10px;">
                <?php 
                    $exportUrl = site_url('admin/kontrak/bast/export-pdf') . (! empty($filterPaketId) || ! empty($filterLingkup) ? '?' . http_build_query(array_filter(['paket_id' => $filterPaketId, 'lingkup_jasa' => $filterLingkup])) : '');
                ?>
                <?php if ($permissions['export'] ?? false): ?>
                    <a href="<?= $exportUrl; ?>" 
                       target="_blank" 
                       class="btn btn-danger font-weight-bold shadow-sm px-3 py-2" 
                       style="border-radius: 8px;"
                       id="btnExportSemuaPdf"
                       title="Export Seluruh Berkas PDF Sekaligus Berdasarkan Filter">
                        <i class="fas fa-file-pdf mr-2"></i>Export Semua PDF (<?= count($bastList); ?>)
                    </a>
                <?php endif; ?>

                <?php if ($permissions['add'] ?? false): ?>
                    <button type="button" class="btn btn-primary font-weight-bold shadow-sm px-3 py-2" id="btnTambahBast" style="border-radius: 8px;">
                        <i class="fas fa-plus-circle mr-2"></i>Buat BAST Baru
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm border-0 mb-3" style="border-radius: 12px; background: #ffffff; border: 1px solid #e9eef5;">
        <div class="card-body py-3 px-4">
            <form method="get" action="<?= site_url('admin/kontrak/bast'); ?>" id="formFilterBast">
                <div class="row align-items-end">
                    <div class="col-md-5 col-sm-6 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-muted mb-1">
                            <i class="fas fa-cube text-primary mr-1"></i> Filter Paket Pekerjaan
                        </label>
                        <select name="paket_id" id="filter_paket" class="form-control form-control-sm" style="border-radius: 8px;">
                            <option value="">-- Semua Paket --</option>
                            <?php foreach ($paketList as $p): ?>
                                <option value="<?= esc($p['id']); ?>" <?= ($filterPaketId == $p['id']) ? 'selected' : ''; ?>>
                                    <?= esc($p['nama_paket']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-muted mb-1">
                            <i class="fas fa-layer-group text-info mr-1"></i> Filter Lingkup Jasa
                        </label>
                        <select name="lingkup_jasa" id="filter_lingkup_jasa" class="form-control form-control-sm" style="border-radius: 8px;">
                            <option value="">-- Semua Lingkup Jasa --</option>
                            <option value="Manajemen Konstruksi" <?= ($filterLingkup === 'Manajemen Konstruksi') ? 'selected' : ''; ?>>Manajemen Konstruksi</option>
                            <option value="Supervisi" <?= ($filterLingkup === 'Supervisi') ? 'selected' : ''; ?>>Supervisi</option>
                            <option value="Fisik" <?= ($filterLingkup === 'Fisik') ? 'selected' : ''; ?>>Fisik</option>
                        </select>
                    </div>

                    <div class="col-md-3 col-sm-12 d-flex mt-2 mt-md-0" style="gap: 8px;">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill font-weight-bold shadow-sm" style="border-radius: 8px;">
                            <i class="fas fa-filter mr-1"></i> Terapkan
                        </button>
                        <a href="<?= site_url('admin/kontrak/bast'); ?>" class="btn btn-outline-secondary btn-sm px-3 shadow-sm" style="border-radius: 8px;" title="Reset Filter">
                            <i class="fas fa-undo mr-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if (! empty($filterPaketId) || ! empty($filterLingkup)): ?>
        <?php
            $activePaketName = '';
            if (! empty($filterPaketId)) {
                foreach ($paketList as $p) {
                    if ($p['id'] == $filterPaketId) {
                        $activePaketName = $p['nama_paket'];
                        break;
                    }
                }
            }
        ?>
        <div class="alert alert-light border shadow-sm py-2 px-3 mb-3 d-flex flex-wrap align-items-center justify-content-between" style="border-radius: 8px; border-left: 4px solid #007bff !important;">
            <div class="small my-1">
                <i class="fas fa-filter text-primary mr-1"></i>
                <strong>Filter Aktif:</strong>
                <?php if (! empty($filterPaketId)): ?>
                    <span class="badge badge-primary px-2 py-1 mr-1">Paket: <?= esc($activePaketName ?: $filterPaketId); ?></span>
                <?php endif; ?>
                <?php if (! empty($filterLingkup)): ?>
                    <span class="badge badge-info px-2 py-1 mr-1">Lingkup: <?= esc($filterLingkup); ?></span>
                <?php endif; ?>
                <span class="text-muted ml-1">(Ditemukan <strong><?= count($bastList); ?></strong> data)</span>
            </div>
            <div class="d-flex align-items-center my-1" style="gap: 8px;">
                <?php if ($permissions['export'] ?? false): ?>
                    <a href="<?= $exportUrl; ?>" 
                       target="_blank" 
                       class="btn btn-sm btn-outline-danger font-weight-bold" 
                       style="border-radius: 6px;"
                       title="Cetak Seluruh Berkas PDF Hasil Filter">
                        <i class="fas fa-file-pdf mr-1"></i> Cetak Hasil Filter (<?= count($bastList); ?>)
                    </a>
                <?php endif; ?>
                <a href="<?= site_url('admin/kontrak/bast'); ?>" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.85rem; border-radius: 6px;">
                    <i class="fas fa-times mr-1"></i> Hapus Filter
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Table Card -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover table-striped w-100 align-middle" id="tableBast" style="font-size: 0.92rem;">
                    <thead class="bg-light text-dark font-weight-bold">
                        <tr>
                            <th style="width: 4%; text-align: center;">No</th>
                            <th style="width: 22%;">Nomor &amp; Judul BAST</th>
                            <th style="width: 24%;">Paket Pekerjaan</th>
                            <th style="width: 24%;">Para Pihak</th>
                            <th style="width: 14%;">Kesesuaian &amp; Bayar</th>
                            <th style="width: 12%; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (! empty($bastList)): ?>
                            <?php $no = 1; foreach ($bastList as $item): ?>
                                <tr>
                                    <td class="text-center font-weight-bold text-muted"><?= $no++; ?></td>
                                    <td>
                                        <div class="font-weight-bold text-dark"><?= esc($item['nomor_bast'] ?: '-'); ?></div>
                                        <div class="badge badge-primary font-weight-normal mt-1">
                                            <?= esc($item['judul_bast']); ?>
                                        </div>
                                        <small class="text-muted d-block mt-1">
                                            <i class="far fa-calendar-alt mr-1"></i><?= ! empty($item['tanggal_bast']) ? date('d-m-Y', strtotime($item['tanggal_bast'])) : '-'; ?> (<?= esc($item['kota_bast']); ?>)
                                        </small>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold text-dark"><?= esc($item['nama_paket']); ?></div>
                                        <small class="text-muted d-block mt-1">
                                            <span class="badge badge-info font-weight-normal mr-1"><?= esc($item['lingkup_jasa']); ?></span>
                                            <span><?= esc($item['jenis_pekerjaan']); ?></span>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="small mb-1">
                                            <strong class="text-secondary"><i class="fas fa-user-tie text-primary mr-1"></i>PPK:</strong>
                                            <span class="font-weight-bold text-dark"><?= esc($item['ppk_nama']); ?></span>
                                        </div>
                                        <div class="small">
                                            <strong class="text-secondary"><i class="fas fa-building text-info mr-1"></i>Penyedia:</strong>
                                            <span class="font-weight-bold text-dark"><?= esc($item['penyedia_nama']); ?></span>
                                            <div class="text-muted small">Wakil: <?= esc($item['penyedia_wakil']); ?> (<?= esc($item['penyedia_jabatan']); ?>)</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="badge <?= ($item['kesesuaian_pekerjaan'] === 'telah sesuai') ? 'badge-success' : 'badge-warning'; ?> px-2 py-1">
                                                <?= esc($item['kesesuaian_pekerjaan']); ?>
                                            </span>
                                        </div>
                                        <div class="small font-weight-bold text-dark mt-1">
                                            Bayar: <span class="text-success"><?= esc($item['persentase_pembayaran']); ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <?php if ($permissions['export'] ?? false): ?>
                                                <a href="<?= site_url('admin/kontrak/bast/' . $item['id'] . '/unduh-docx'); ?>" 
                                                   class="btn btn-outline-primary" 
                                                   title="Unduh Dokumen Word (.docx)"
                                                   data-toggle="tooltip">
                                                    <i class="fas fa-file-word"></i>
                                                </a>
                                                <a href="<?= site_url('admin/kontrak/bast/' . $item['id'] . '/cetak-pdf'); ?>" 
                                                   target="_blank" 
                                                   class="btn btn-outline-danger" 
                                                   title="Cetak / Pratinjau PDF"
                                                   data-toggle="tooltip">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                            <?php endif; ?>

                                            <button type="button" 
                                                    class="btn btn-outline-info btn-preview-bast" 
                                                    data-id="<?= $item['id']; ?>" 
                                                    title="Pratinjau Data Lengkap"
                                                    data-toggle="tooltip">
                                                <i class="fas fa-eye"></i>
                                            </button>

                                            <?php if ($permissions['add'] ?? false): ?>
                                                <button type="button" 
                                                        class="btn btn-outline-success btn-copy-bast" 
                                                        data-id="<?= $item['id']; ?>" 
                                                        title="Duplikat / Salin BAST Ini"
                                                        data-toggle="tooltip">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            <?php endif; ?>

                                            <?php if ($permissions['edit'] ?? false): ?>
                                                <button type="button" 
                                                        class="btn btn-outline-warning btn-edit-bast" 
                                                        data-id="<?= $item['id']; ?>" 
                                                        title="Ubah Data"
                                                        data-toggle="tooltip">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            <?php endif; ?>

                                            <?php if ($permissions['delete'] ?? false): ?>
                                                <button type="button" 
                                                        class="btn btn-outline-secondary btn-delete-bast text-danger" 
                                                        data-id="<?= $item['id']; ?>" 
                                                        data-nomor="<?= esc($item['nomor_bast']); ?>" 
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
<!-- MODAL FORM (TAMBAH / UBAH BAST) -->
<!-- ============================================================ -->
<div class="modal fade" id="modalFormBast" tabindex="-1" role="dialog" aria-labelledby="modalFormBastLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <form id="formBast" autocomplete="off" class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <input type="hidden" name="id" id="bast_id" value="">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title font-weight-bold" id="modalFormBastLabel">
                    <i class="fas fa-file-contract mr-2"></i>Buat Dokumen BAST Baru
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" style="background: #f8fafc;">

                <!-- BAGIAN 1: IDENTITAS DOKUMEN & KOP -->
                <div class="card border-0 shadow-sm mb-3" style="border-radius: 10px;">
                    <div class="card-header bg-white font-weight-bold text-primary py-2 d-flex align-items-center">
                        <i class="fas fa-info-circle mr-2"></i>1. Identitas Dokumen BAST &amp; Kop Surat
                    </div>
                    <div class="card-body py-3">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="small font-weight-bold text-dark">Judul Berita Acara <span class="text-danger">*</span></label>
                                <input type="text" name="judul_bast" id="judul_bast" class="form-control" value="BERITA ACARA SERAH TERIMA I" placeholder="Contoh: BERITA ACARA SERAH TERIMA I" required>
                                <small class="text-muted">Misal: BERITA ACARA SERAH TERIMA I, BERITA ACARA SERAH TERIMA II, atau AKHIR</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small font-weight-bold text-dark">Nomor BAST <span class="text-danger">*</span></label>
                                <input type="text" name="nomor_bast" id="nomor_bast" class="form-control" placeholder="Contoh: BAST/MK-PHTC.06/Gs7/2026" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="small font-weight-bold text-dark">Tanggal BAST <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_bast" id="tanggal_bast" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="small font-weight-bold text-dark">Kota Penerbitan <span class="text-danger">*</span></label>
                                <input type="text" name="kota_bast" id="kota_bast" class="form-control" value="Pekanbaru" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="small font-weight-bold text-dark">Kop Surat Resmi <span class="text-danger">*</span></label>
                                <select name="kop_surat_id" id="kop_surat_id" class="form-control">
                                    <?php foreach ($kopSuratList as $kop): ?>
                                        <option value="<?= esc($kop['id']); ?>" <?= (! empty($defaultKop['id']) && $defaultKop['id'] == $kop['id']) ? 'selected' : ''; ?>>
                                            <?= esc($kop['title']); ?> <?= ! empty($kop['is_active']) ? '(Aktif)' : ''; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BAGIAN 2: PAKET PEKERJAAN & LINGKUP -->
                <div class="card border-0 shadow-sm mb-3" style="border-radius: 10px;">
                    <div class="card-header bg-white font-weight-bold text-primary py-2 d-flex align-items-center justify-content-between">
                        <div><i class="fas fa-cube mr-2"></i>2. Paket Pekerjaan &amp; Lingkup Jasa</div>
                        <span class="badge badge-light border text-info font-weight-normal"><i class="fas fa-magic mr-1"></i>Pilih paket untuk auto-fill data SIMAK</span>
                    </div>
                    <div class="card-body py-3">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="small font-weight-bold text-dark">Pilih Preset Paket (Otomatis Isi)</label>
                                <select id="pilih_paket_preset" class="form-control border-primary">
                                    <option value="">-- Pilih Paket Dari Database SIMAK --</option>
                                    <?php foreach ($paketList as $p): ?>
                                        <option value="<?= esc($p['id']); ?>" data-nama="<?= esc($p['nama_paket']); ?>">
                                            <?= esc($p['nama_paket']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="paket_id" id="paket_id" value="">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small font-weight-bold text-dark">Lingkup Jasa <span class="text-danger">*</span></label>
                                <select name="lingkup_jasa" id="lingkup_jasa" class="form-control" required>
                                    <option value="Manajemen Konstruksi">Manajemen Konstruksi</option>
                                    <option value="Supervisi">Supervisi</option>
                                    <option value="Fisik">Fisik</option>
                                    <option value="Perencanaan">Perencanaan</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small font-weight-bold text-dark">Nama Paket Pekerjaan <span class="text-danger">*</span></label>
                                <textarea name="nama_paket" id="nama_paket" class="form-control" rows="2" placeholder="Nama paket pekerjaan secara lengkap" required></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small font-weight-bold text-dark">Sub Judul Jenis Pekerjaan <span class="text-danger">*</span></label>
                                <input type="text" name="jenis_pekerjaan" id="jenis_pekerjaan" class="form-control" value="PEKERJAAN JASA KONSULTANSI KONSTRUKSI" required>
                                <small class="text-muted">Misal: PEKERJAAN JASA KONSULTANSI KONSTRUKSI atau PEKERJAAN KONSTRUKSI</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BAGIAN 3: PIHAK PERTAMA (PPK) -->
                <div class="card border-0 shadow-sm mb-3" style="border-radius: 10px;">
                    <div class="card-header bg-white font-weight-bold text-primary py-2 d-flex align-items-center">
                        <i class="fas fa-user-tie mr-2"></i>3. PIHAK PERTAMA (Pejabat Penandatangan Kontrak / PPK)
                    </div>
                    <div class="card-body py-3">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="small font-weight-bold text-dark">Pilih Preset Pejabat PPK</label>
                                <select id="pilih_ppk_preset" class="form-control">
                                    <option value="">-- Pilih Pegawai --</option>
                                    <?php foreach ($pegawaiList as $peg): ?>
                                        <option value="<?= esc($peg['id']); ?>" 
                                                data-nama="<?= esc($peg['nama']); ?>" 
                                                data-nip="<?= esc($peg['nip'] ?? ''); ?>"
                                                <?= (! empty($defaultPpk['id']) && $defaultPpk['id'] == $peg['id']) ? 'selected' : ''; ?>>
                                            <?= esc($peg['nama']); ?> (NIP. <?= esc($peg['nip'] ?? '-'); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="ppk_pegawai_id" id="ppk_pegawai_id" value="<?= esc($defaultPpk['id'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small font-weight-bold text-dark">Nama Pejabat PPK <span class="text-danger">*</span></label>
                                <input type="text" name="ppk_nama" id="ppk_nama" class="form-control" value="<?= esc($defaultPpk['nama'] ?? 'Nurhidayat Nugroho, S. Ars'); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small font-weight-bold text-dark">NIP PPK <span class="text-danger">*</span></label>
                                <input type="text" name="ppk_nip" id="ppk_nip" class="form-control" value="<?= esc($defaultPpk['nip'] ?? '199012212018021001'); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small font-weight-bold text-dark">Jabatan PPK <span class="text-danger">*</span></label>
                                <input type="text" name="ppk_jabatan" id="ppk_jabatan" class="form-control" value="Pejabat Penandatangan Kontrak Pelaksanaan Prasarana Strategis, Satuan Kerja Pelaksanaan Prasarana Strategis Riau" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small font-weight-bold text-dark">Satker PPK <span class="text-danger">*</span></label>
                                <input type="text" name="ppk_satker" id="ppk_satker" class="form-control" value="Satuan Kerja Pelaksanaan Prasarana Strategis Riau" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="small font-weight-bold text-dark">Alamat Satker PPK</label>
                                <input type="text" name="ppk_alamat" id="ppk_alamat" class="form-control" value="Jl. Datuk Setia Maharaja No. 15 Pekanbaru, Riau">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BAGIAN 4: PIHAK KEDUA (PENYEDIA) -->
                <div class="card border-0 shadow-sm mb-3" style="border-radius: 10px;">
                    <div class="card-header bg-white font-weight-bold text-primary py-2 d-flex align-items-center">
                        <i class="fas fa-building mr-2"></i>4. PIHAK KEDUA (Penyedia Jasa / Rekanan)
                    </div>
                    <div class="card-body py-3">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="small font-weight-bold text-dark">Nama Badan Usaha / Konsorsium (KSO) <span class="text-danger">*</span></label>
                                <input type="text" name="penyedia_nama" id="penyedia_nama" class="form-control" placeholder="Contoh: PT. Mahakarya Abadi Konsultan KSO CV. Sinergi Lestari Karya" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small font-weight-bold text-dark">Nama Wakil / Direktur Utama <span class="text-danger">*</span></label>
                                <input type="text" name="penyedia_wakil" id="penyedia_wakil" class="form-control" placeholder="Contoh: Bambang Taidi, S.T" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small font-weight-bold text-dark">Jabatan Wakil Penyedia <span class="text-danger">*</span></label>
                                <input type="text" name="penyedia_jabatan" id="penyedia_jabatan" class="form-control" value="Direktur Utama" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="small font-weight-bold text-dark">Alamat Badan Usaha Penyedia</label>
                                <textarea name="penyedia_alamat" id="penyedia_alamat" class="form-control" rows="2" placeholder="Alamat lengkap penyedia"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BAGIAN 5: DASAR PELAKSANAAN -->
                <div class="card border-0 shadow-sm mb-3" style="border-radius: 10px;">
                    <div class="card-header bg-white font-weight-bold text-primary py-2 d-flex align-items-center justify-content-between">
                        <div><i class="fas fa-list-ol mr-2"></i>5. Dasar Pelaksanaan (Kontrak, SPMK, Addendum)</div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnTambahDasar">
                            <i class="fas fa-plus mr-1"></i>Tambah Dasar
                        </button>
                    </div>
                    <div class="card-body py-3">
                        <div id="containerDasarPelaksanaan">
                            <!-- Input dinamis dimasukkan di sini -->
                        </div>
                    </div>
                </div>

                <!-- BAGIAN 6: PASAL 1 - RINCIAN HASIL PEKERJAAN -->
                <div class="card border-0 shadow-sm mb-3" style="border-radius: 10px;">
                    <div class="card-header bg-white font-weight-bold text-primary py-2 d-flex align-items-center justify-content-between flex-wrap">
                        <div><i class="fas fa-tasks mr-2"></i>6. Pasal 1 - Rincian Hasil Pekerjaan Yang Diserahkan</div>
                        <div class="mt-1 mt-md-0" style="gap: 5px;">
                            <button type="button" class="btn btn-sm btn-success mr-1" id="btnLoadPresetMk" title="Muat 6 Item Standar Sesuai Berkas Acuan">
                                <i class="fas fa-bolt mr-1"></i>Muat Preset Standar MK (6 Item)
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnTambahRincian">
                                <i class="fas fa-plus mr-1"></i>Tambah Baris
                            </button>
                        </div>
                    </div>
                    <div class="card-body py-3">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle" id="tableInputRincian">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 8%; text-align: center;">No</th>
                                        <th style="width: 44%;">Uraian Hasil Pekerjaan</th>
                                        <th style="width: 20%;">Jumlah</th>
                                        <th style="width: 20%;">Keterangan</th>
                                        <th style="width: 8%; text-align: center;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Baris dinamis di sini -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- BAGIAN 7: PASAL 2 & 3 - KESESUAIAN & PEMBAYARAN -->
                <div class="card border-0 shadow-sm mb-2" style="border-radius: 10px;">
                    <div class="card-header bg-white font-weight-bold text-primary py-2 d-flex align-items-center">
                        <i class="fas fa-check-double mr-2"></i>7. Ketentuan Hasil Pemeriksaan &amp; Pembayaran
                    </div>
                    <div class="card-body py-3">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="small font-weight-bold text-dark">Pasal 2: Hasil Pemeriksaan Pekerjaan <span class="text-danger">*</span></label>
                                <select name="kesesuaian_pekerjaan" id="kesesuaian_pekerjaan" class="form-control" required>
                                    <option value="telah sesuai" selected>telah sesuai</option>
                                    <option value="belum sepenuhnya sesuai">belum sepenuhnya sesuai</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small font-weight-bold text-dark">Pasal 3: Hak Pembayaran (%) <span class="text-danger">*</span></label>
                                <input type="text" name="persentase_pembayaran" id="persentase_pembayaran" class="form-control" value="100%" placeholder="Contoh: 100% atau 95%" required>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer bg-white border-top-0 py-3">
                <button type="button" class="btn btn-secondary px-3" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary px-4 font-weight-bold shadow-sm" id="btnSimpanBast">
                    <i class="fas fa-save mr-1"></i>Simpan BAST
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL PRATINJAU DETAIL BAST -->
<!-- ============================================================ -->
<div class="modal fade" id="modalPreviewBast" tabindex="-1" role="dialog" aria-labelledby="modalPreviewBastLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title font-weight-bold" id="modalPreviewBastLabel">
                    <i class="fas fa-file-alt mr-2"></i>Pratinjau Dokumen BAST
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4 bg-white" id="previewBastContent">
                <!-- Konten pratinjau diisi oleh JS -->
            </div>
            <div class="modal-footer bg-light py-2 justify-content-between">
                <div>
                    <a href="#" target="_blank" class="btn btn-outline-danger btn-sm font-weight-bold" id="previewBtnPdf">
                        <i class="fas fa-file-pdf mr-1"></i> Cetak PDF
                    </a>
                    <a href="#" class="btn btn-outline-primary btn-sm font-weight-bold" id="previewBtnWord">
                        <i class="fas fa-file-word mr-1"></i> Unduh Word (.docx)
                    </a>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
$(document).ready(function() {
    // Inisialisasi DataTable jika tersedia
    if ($.fn.DataTable && $('#tableBast').length) {
        $('#tableBast').DataTable({
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                infoFiltered: "(disaring dari _MAX_ total data)",
                zeroRecords: "Tidak ditemukan data yang cocok",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Berikutnya",
                    previous: "Sebelumnya"
                }
            },
            pageLength: 25,
            ordering: false
        });
    }

    $('[data-toggle="tooltip"]').tooltip();

    // 1. Auto-fill data saat pilih Paket Preset
    $('#pilih_paket_preset').on('change', function() {
        const val = $(this).val();
        if (!val) {
            $('#paket_id').val('');
            return;
        }

        $('#paket_id').val(val);
        const opt = $(this).find('option:selected');
        $('#nama_paket').val(opt.data('nama') || '');

        // Fetch detail info via AJAX
        $.ajax({
            url: '<?= site_url('admin/kontrak/bast/get-paket-info'); ?>/' + val,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    if (res.penyedia) $('#penyedia_nama').val(res.penyedia);
                    if (res.jenis_pekerjaan) $('#jenis_pekerjaan').val(res.jenis_pekerjaan);
                    if (res.lingkup_jasa) $('#lingkup_jasa').val(res.lingkup_jasa);

                    // Auto-fill Dasar Pelaksanaan jika masih kosong
                    if (res.dasar_pelaksanaan && res.dasar_pelaksanaan.length > 0) {
                        $('#containerDasarPelaksanaan').empty();
                        res.dasar_pelaksanaan.forEach(function(item) {
                            addDasarRow(item);
                        });
                    }
                }
            }
        });
    });

    // 2. Preset PPK dropdown listener
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

    // 3. Dynamic Dasar Pelaksanaan Handlers
    function addDasarRow(val = '') {
        const html = `
            <div class="input-group mb-2 item-dasar">
                <div class="input-group-prepend">
                    <span class="input-group-text small bg-light"><i class="fas fa-file-signature"></i></span>
                </div>
                <input type="text" name="dasar_pelaksanaan[]" class="form-control" value="${val}" placeholder="Contoh: Kontrak/Surat Perjanjian Pekerjaan ... Nomor ... tanggal ..." required>
                <div class="input-group-append">
                    <button type="button" class="btn btn-outline-danger btn-remove-dasar" title="Hapus Dasar Pelaksanaan">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
        `;
        $('#containerDasarPelaksanaan').append(html);
    }

    $('#btnTambahDasar').on('click', function() {
        addDasarRow('');
    });

    $(document).on('click', '.btn-remove-dasar', function() {
        if ($('#containerDasarPelaksanaan .item-dasar').length > 1) {
            $(this).closest('.item-dasar').remove();
        } else {
            $(this).closest('.item-dasar').find('input').val('');
        }
    });

    // 4. Dynamic Rincian Hasil Pekerjaan Handlers
    function addRincianRow(no = '', uraian = '', jumlah = '', ket = '') {
        const rowCount = $('#tableInputRincian tbody tr').length + 1;
        const noVal = no !== '' ? no : rowCount;
        const html = `
            <tr class="item-rincian">
                <td>
                    <input type="text" name="rincian_no[]" class="form-control form-control-sm text-center font-weight-bold" value="${noVal}">
                </td>
                <td>
                    <input type="text" name="rincian_uraian[]" class="form-control form-control-sm" value="${uraian}" placeholder="Uraian dokumen / barang" required>
                </td>
                <td>
                    <input type="text" name="rincian_jumlah[]" class="form-control form-control-sm text-center" value="${jumlah}" placeholder="Contoh: 12 Buku / 1 Berkas" required>
                </td>
                <td>
                    <input type="text" name="rincian_keterangan[]" class="form-control form-control-sm" value="${ket}" placeholder="Contoh: Hardcopy & softcopy">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-rincian" title="Hapus Baris">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#tableInputRincian tbody').append(html);
    }

    $('#btnTambahRincian').on('click', function() {
        addRincianRow();
    });

    $(document).on('click', '.btn-remove-rincian', function() {
        if ($('#tableInputRincian tbody tr').length > 1) {
            $(this).closest('tr').remove();
            // Re-index nomor urut jika diinginkan
            $('#tableInputRincian tbody tr').each(function(idx) {
                $(this).find('input[name="rincian_no[]"]').val(idx + 1);
            });
        }
    });

    // 5. Muat Preset Standar MK (6 Item Berkas Acuan)
    $('#btnLoadPresetMk').on('click', function() {
        $('#tableInputRincian tbody').empty();
        const presetItems = [
            { no: 1, uraian: 'Laporan Mingguan', jumlah: '48 Buku', ket: 'Hardcopy & softcopy' },
            { no: 2, uraian: 'Laporan Bulanan', jumlah: '12 Buku', ket: 'Hardcopy & softcopy' },
            { no: 3, uraian: 'Laporan Akhir Pengawasan Teknis', jumlah: '1 Buku', ket: 'Hardcopy & softcopy' },
            { no: 4, uraian: 'Laporan Khusus (Profil Kegiatan)', jumlah: '1 Buku', ket: 'Hardcopy & softcopy' },
            { no: 5, uraian: 'Laporan Khusus (Dokumen Permohonan SLF)', jumlah: '1 Buku', ket: 'Hardcopy & softcopy' },
            { no: 6, uraian: 'SSD Eksternal', jumlah: '1 Tb', ket: 'Barang' }
        ];

        presetItems.forEach(function(item) {
            addRincianRow(item.no, item.uraian, item.jumlah, item.ket);
        });

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '6 Item Preset Standar MK berhasil dimuat.',
                showConfirmButton: false,
                timer: 2000
            });
        }
    });

    // 6. Tombol Tambah BAST Baru
    $('#btnTambahBast').on('click', function() {
        $('#formBast')[0].reset();
        $('#bast_id').val('');
        $('#modalFormBastLabel').html('<i class="fas fa-file-contract mr-2"></i>Buat Dokumen BAST Baru');
        $('#judul_bast').val('BERITA ACARA SERAH TERIMA I');
        $('#jenis_pekerjaan').val('PEKERJAAN JASA KONSULTANSI KONSTRUKSI');
        $('#lingkup_jasa').val('Manajemen Konstruksi');
        $('#tanggal_bast').val('<?= date('Y-m-d'); ?>');
        $('#kota_bast').val('Pekanbaru');
        $('#kop_surat_id').val('<?= $defaultKop['id'] ?? ''; ?>');
        $('#pilih_ppk_preset').val('<?= $defaultPpk['id'] ?? ''; ?>').trigger('change');
        $('#pilih_paket_preset').val('');
        $('#paket_id').val('');
        $('#penyedia_jabatan').val('Direktur Utama');
        $('#kesesuaian_pekerjaan').val('telah sesuai');
        $('#persentase_pembayaran').val('100%');

        // Reset dasar pelaksanaan
        $('#containerDasarPelaksanaan').empty();
        addDasarRow('Kontrak/Surat Perjanjian Pekerjaan ......................... Nomor ......................... tanggal .........................');
        addDasarRow('Surat Perintah Mulai Kerja (SPMK) Nomor ........................., tanggal .........................');

        // Reset rincian tabel
        $('#tableInputRincian tbody').empty();
        $('#btnLoadPresetMk').trigger('click');

        $('#modalFormBast').modal('show');
    });

    // 7. Simpan Form (Insert / Update) via AJAX (Strict single notification)
    $('#formBast').on('submit', function(e) {
        e.preventDefault();
        const id = $('#bast_id').val();
        const isEdit = (id !== '');
        const url = isEdit 
            ? '<?= site_url('admin/kontrak/bast'); ?>/' + id + '/ubah'
            : '<?= site_url('admin/kontrak/bast/simpan'); ?>';

        const submitBtn = $('#btnSimpanBast');
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...');

        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                submitBtn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Simpan BAST');
                if (res.success) {
                    $('#modalFormBast').modal('hide');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message,
                            confirmButtonColor: '#007bff'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        window.location.reload();
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Menyimpan',
                            text: res.message || 'Periksa kembali kelengkapan formulir Anda.'
                        });
                    } else {
                        alert(res.message || 'Gagal menyimpan.');
                    }
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Simpan BAST');
                let errMsg = 'Terjadi kesalahan sistem.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errMsg = xhr.responseJSON.message;
                }
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errMsg
                    });
                } else {
                    alert(errMsg);
                }
            }
        });
    });

    // 8. Edit BAST
    $(document).on('click', '.btn-edit-bast', function() {
        const id = $(this).data('id');
        loadDataIntoForm(id, false);
    });

    // 9. Duplikat BAST (Copy as New)
    $(document).on('click', '.btn-copy-bast', function() {
        const id = $(this).data('id');
        loadDataIntoForm(id, true);
    });

    function loadDataIntoForm(id, isDuplicate = false) {
        $.ajax({
            url: '<?= site_url('admin/kontrak/bast'); ?>/' + id + '/detail',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (!res.success) {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Gagal mengambil data.' });
                    return;
                }
                const d = res.data;
                $('#formBast')[0].reset();
                $('#bast_id').val(isDuplicate ? '' : d.id);
                $('#modalFormBastLabel').html(isDuplicate 
                    ? '<i class="fas fa-copy mr-2"></i>Duplikat Dokumen BAST (Dokumen Baru)'
                    : '<i class="fas fa-edit mr-2"></i>Ubah Dokumen BAST'
                );

                $('#judul_bast').val(d.judul_bast || 'BERITA ACARA SERAH TERIMA I');
                $('#nomor_bast').val(isDuplicate ? (d.nomor_bast ? d.nomor_bast + '-COPY' : '') : (d.nomor_bast || ''));
                $('#tanggal_bast').val(d.tanggal_bast || '<?= date('Y-m-d'); ?>');
                $('#kota_bast').val(d.kota_bast || 'Pekanbaru');
                $('#kop_surat_id').val(d.kop_surat_id || '<?= $defaultKop['id'] ?? ''; ?>');

                $('#pilih_paket_preset').val(d.paket_id || '');
                $('#paket_id').val(d.paket_id || '');
                $('#nama_paket').val(d.nama_paket || '');
                $('#lingkup_jasa').val(d.lingkup_jasa || 'Manajemen Konstruksi');
                $('#jenis_pekerjaan').val(d.jenis_pekerjaan || 'PEKERJAAN JASA KONSULTANSI KONSTRUKSI');

                $('#ppk_pegawai_id').val(d.ppk_pegawai_id || '');
                $('#pilih_ppk_preset').val(d.ppk_pegawai_id || '');
                $('#ppk_nama').val(d.ppk_nama || '');
                $('#ppk_nip').val(d.ppk_nip || '');
                $('#ppk_jabatan').val(d.ppk_jabatan || '');
                $('#ppk_satker').val(d.ppk_satker || '');
                $('#ppk_alamat').val(d.ppk_alamat || '');

                $('#penyedia_nama').val(d.penyedia_nama || '');
                $('#penyedia_wakil').val(d.penyedia_wakil || '');
                $('#penyedia_jabatan').val(d.penyedia_jabatan || 'Direktur Utama');
                $('#penyedia_alamat').val(d.penyedia_alamat || '');

                $('#kesesuaian_pekerjaan').val(d.kesesuaian_pekerjaan || 'telah sesuai');
                $('#persentase_pembayaran').val(d.persentase_pembayaran || '100%');

                // Populate Dasar Pelaksanaan
                $('#containerDasarPelaksanaan').empty();
                if (d.dasar_pelaksanaan_array && d.dasar_pelaksanaan_array.length > 0) {
                    d.dasar_pelaksanaan_array.forEach(function(item) {
                        addDasarRow(item);
                    });
                } else {
                    addDasarRow('');
                }

                // Populate Rincian Hasil Pekerjaan
                $('#tableInputRincian tbody').empty();
                if (d.rincian_hasil_array && d.rincian_hasil_array.length > 0) {
                    d.rincian_hasil_array.forEach(function(item) {
                        addRincianRow(item.no, item.uraian, item.jumlah, item.keterangan);
                    });
                } else {
                    addRincianRow();
                }

                $('#modalFormBast').modal('show');
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Koneksi ke server bermasalah.' });
            }
        });
    }

    // 10. Pratinjau Modal BAST
    $(document).on('click', '.btn-preview-bast', function() {
        const id = $(this).data('id');
        $.ajax({
            url: '<?= site_url('admin/kontrak/bast'); ?>/' + id + '/detail',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (!res.success) {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Gagal memuat pratinjau.' });
                    return;
                }
                const d = res.data;
                let dasarListHtml = '<ol class="pl-3 mb-0">';
                if (d.dasar_pelaksanaan_array && d.dasar_pelaksanaan_array.length > 0) {
                    d.dasar_pelaksanaan_array.forEach(function(it) {
                        dasarListHtml += `<li>${it}</li>`;
                    });
                } else {
                    dasarListHtml += '<li>-</li>';
                }
                dasarListHtml += '</ol>';

                let rincianRowsHtml = '';
                if (d.rincian_hasil_array && d.rincian_hasil_array.length > 0) {
                    d.rincian_hasil_array.forEach(function(it, idx) {
                        rincianRowsHtml += `
                            <tr>
                                <td class="text-center font-weight-bold">${it.no || (idx + 1)}</td>
                                <td>${it.uraian || '-'}</td>
                                <td class="text-center">${it.jumlah || '-'}</td>
                                <td>${it.keterangan || '-'}</td>
                            </tr>
                        `;
                    });
                } else {
                    rincianRowsHtml = '<tr><td colspan="4" class="text-center text-muted">Tidak ada rincian data</td></tr>';
                }

                const previewHtml = `
                    <div class="border p-4 shadow-sm" style="border-radius: 8px; font-family: 'Times New Roman', serif; font-size: 11pt; line-height: 1.4;">
                        <div class="text-center mb-3">
                            <h5 class="font-weight-bold mb-0 text-dark" style="text-decoration: underline;">${d.judul_bast || 'BERITA ACARA SERAH TERIMA'}</h5>
                            <div class="font-weight-bold">${d.jenis_pekerjaan || ''}</div>
                            <div class="font-weight-bold">${d.nama_paket || ''}</div>
                            <div>Nomor: ${d.nomor_bast || '-'}</div>
                        </div>

                        <p class="text-justify mb-2">
                            Pada hari ini ${d.hari_tanggal_terbilang || '-'}, bertempat di ${d.kota_bast || 'Pekanbaru'}, yang bertanda tangan di bawah ini:
                        </p>

                        <div class="font-weight-bold mb-1">1. PIHAK PERTAMA</div>
                        <table class="table table-sm table-borderless mb-2" style="font-size: 10.5pt;">
                            <tr><td style="width: 15%;">Nama</td><td style="width: 2%;">:</td><td class="font-weight-bold">${d.ppk_nama}</td></tr>
                            <tr><td>NIP</td><td>:</td><td>${d.ppk_nip}</td></tr>
                            <tr><td>Jabatan</td><td>:</td><td>${d.ppk_jabatan}</td></tr>
                            <tr><td>Alamat</td><td>:</td><td>${d.ppk_alamat || '-'}</td></tr>
                        </table>
                        <p class="text-justify mb-2">Dalam hal ini bertindak untuk dan atas nama ${d.ppk_jabatan}, selanjutnya disebut <strong>PIHAK PERTAMA</strong>.</p>

                        <div class="font-weight-bold mb-1 mt-2">2. PIHAK KEDUA</div>
                        <table class="table table-sm table-borderless mb-2" style="font-size: 10.5pt;">
                            <tr><td style="width: 15%;">Nama</td><td style="width: 2%;">:</td><td class="font-weight-bold">${d.penyedia_wakil}</td></tr>
                            <tr><td>Jabatan</td><td>:</td><td>${d.penyedia_jabatan || 'Direktur Utama'}</td></tr>
                            <tr><td>Alamat</td><td>:</td><td>${d.penyedia_alamat || '-'}</td></tr>
                        </table>
                        <p class="text-justify mb-2">Dalam hal ini bertindak untuk dan atas nama ${d.penyedia_nama}, selanjutnya disebut <strong>PIHAK KEDUA</strong>.</p>

                        <p class="text-justify mb-2"><strong>PIHAK PERTAMA</strong> dan <strong>PIHAK KEDUA</strong> secara bersama-sama disebut <strong>PARA PIHAK</strong>.</p>

                        <div class="font-weight-bold mb-1 mt-2">Dasar pelaksanaan:</div>
                        <div class="mb-3">${dasarListHtml}</div>

                        <p class="text-justify mb-2">PARA PIHAK menyatakan sepakat sebagai berikut:</p>

                        <div class="text-center font-weight-bold mb-1">Pasal 1</div>
                        <p class="text-justify mb-2">PIHAK KEDUA menyerahkan kepada PIHAK PERTAMA hasil pekerjaan ${d.jenis_pekerjaan_singkat} Pekerjaan ${d.nama_paket}, berupa:</p>

                        <table class="table table-bordered table-sm mb-3" style="font-size: 10pt;">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th style="width: 6%;">No</th>
                                    <th>Uraian Hasil Pekerjaan</th>
                                    <th style="width: 22%;">Jumlah</th>
                                    <th style="width: 25%;">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>${rincianRowsHtml}</tbody>
                        </table>

                        <div class="text-center font-weight-bold mb-1">Pasal 2</div>
                        <p class="text-justify mb-2">PIHAK PERTAMA menyatakan telah menerima hasil pekerjaan sebagaimana dimaksud dalam Pasal 1 dan telah melakukan pemeriksaan, dengan hasil bahwa pekerjaan <strong>${d.kesesuaian_pekerjaan}</strong> dengan ketentuan dalam kontrak.</p>

                        <div class="text-center font-weight-bold mb-1">Pasal 3</div>
                        <p class="text-justify mb-2">Dengan ditandatanganinya berita acara ini, PIHAK PERTAMA berhak memproses pembayaran sebesar <strong>${d.persentase_pembayaran}</strong> dari nilai kontrak/Addendum sesuai ketentuan dalam kontrak/Addendum.</p>

                        <div class="text-center font-weight-bold mb-1">Pasal 4</div>
                        <p class="text-justify mb-2">Serah terima ini tidak menghapuskan kewajiban PIHAK KEDUA atas tahap pekerjaan berikutnya sampai seluruh pekerjaan diselesaikan sesuai kontrak/Addendum.</p>

                        <div class="text-center font-weight-bold mb-1">Pasal 5</div>
                        <p class="text-justify mb-2">Berita acara ini dibuat rangkap 2 (dua) bermeterai cukup, masing-masing mempunyai kekuatan hukum yang sama, satu untuk PIHAK PERTAMA dan satu untuk PIHAK KEDUA.</p>

                        <p class="text-justify mt-3">Demikian ${d.judul_bast} ini dibuat dengan sebenarnya untuk dipergunakan sebagaimana mestinya.</p>
                    </div>
                `;

                $('#previewBastContent').html(previewHtml);
                $('#previewBtnWord').attr('href', '<?= site_url('admin/kontrak/bast'); ?>/' + d.id + '/unduh-docx');
                $('#previewBtnPdf').attr('href', '<?= site_url('admin/kontrak/bast'); ?>/' + d.id + '/cetak-pdf');
                $('#modalPreviewBast').modal('show');
            }
        });
    });

    // 11. Hapus BAST
    $(document).on('click', '.btn-delete-bast', function() {
        const id = $(this).data('id');
        const nomor = $(this).data('nomor') || 'dokumen ini';

        Swal.fire({
            title: 'Hapus Berita Acara?',
            text: 'Apakah Anda yakin ingin menghapus data BAST ' + nomor + '? Data yang dihapus tidak dapat dipulihkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i> Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= site_url('admin/kontrak/bast'); ?>/' + id + '/hapus',
                    type: 'POST',
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: res.message,
                                confirmButtonColor: '#007bff'
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
