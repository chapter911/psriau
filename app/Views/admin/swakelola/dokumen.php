<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
$canUploadDoc = ! empty($permissions['edit']) || ! empty($permissions['add']);
$activePaketId = (int) ($paket['id'] ?? 0);
?>
<div class="container-fluid">

    <!-- Paket Switcher Bar (Paket 1 s/d Paket 4 LHA No. 9) -->
    <div class="card shadow-sm border-0 mb-3" style="border-radius: 12px;">
        <div class="card-body py-3 px-4 d-flex flex-wrap justify-content-between align-items-center">
            <div class="d-flex align-items-center mb-2 mb-lg-0">
                <span class="font-weight-bold text-dark mr-3">
                    <i class="fas fa-layer-group text-primary mr-1"></i> Pilih Paket LHA No. 9:
                </span>
                <div class="nav nav-pills flex-wrap">
                    <?php foreach ($paketList as $pItem):
                        $isAct = ((int) $pItem['id'] === $activePaketId);
                        $lhaNo = (int) ($pItem['lha_no'] ?? 1);
                    ?>
                        <a href="<?= base_url('admin/swakelola/dokumen?paket_id=' . $pItem['id']); ?>"
                           class="btn btn-sm <?= $isAct ? 'btn-primary font-weight-bold shadow-sm' : 'btn-outline-secondary'; ?> mr-2 mb-1"
                           style="border-radius: 20px;">
                            Paket <?= $lhaNo; ?> (<?= esc($pItem['pic_label'] ?? $pItem['pic_nama']); ?>)
                            <span class="badge <?= $isAct ? 'badge-light text-primary' : 'badge-secondary'; ?> ml-1">
                                <?= (int) ($pItem['dokumen_ada'] ?? 0); ?>/<?= (int) ($pItem['dokumen_total'] ?? 28); ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <select class="form-control form-control-sm" style="min-width: 260px; border-radius: 8px;" onchange="if(this.value) window.location.href=this.value;">
                    <?php foreach ($paketList as $pItem):
                        $isAct = ((int) $pItem['id'] === $activePaketId);
                        $lhaNo = (int) ($pItem['lha_no'] ?? 1);
                    ?>
                        <option value="<?= base_url('admin/swakelola/dokumen?paket_id=' . $pItem['id']); ?>" <?= $isAct ? 'selected' : ''; ?>>
                            Paket <?= $lhaNo; ?> — <?= esc($pItem['nama_kegiatan']); ?> (PIC: <?= esc($pItem['pic_label'] ?? '-'); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Top Banner -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%); color: #ffffff;">
        <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <div class="mb-1">
                    <span class="badge badge-warning text-dark font-weight-bold px-2 py-1 mr-2">
                        PAKET <?= (int) ($paket['lha_no'] ?? 1); ?> — PIC: <?= esc($paket['pic_nama'] ?? '-'); ?>
                    </span>
                </div>
                <h4 class="font-weight-bold mb-1 text-white">
                    <i class="fas fa-check-double mr-2 text-warning"></i> Checklist 28 Dokumen Swakelola Tipe I
                </h4>
                <p class="text-white-50 mb-0 small">
                    <?= esc($paket['nama_kegiatan'] ?? 'Kegiatan Swakelola'); ?> — TA <?= esc($paket['tahun_anggaran'] ?? '2025'); ?>
                </p>
                <div class="mt-2 text-white-50 small">
                    <span class="mr-3"><i class="fas fa-barcode mr-1"></i> Kode MAK: <strong><?= esc($paket['kode_paket'] ?? '-'); ?></strong></span>
                    <span class="mr-3"><i class="fas fa-money-bill-wave mr-1"></i> Pagu POK Rev 16: <strong>Rp <?= number_format((float)($paket['pagu_anggaran'] ?? 0), 0, ',', '.'); ?></strong></span>
                    <span><i class="fas fa-check-circle mr-1 text-success"></i> Realisasi: <strong>Rp <?= number_format((float)($paket['realisasi_anggaran'] ?? 0), 0, ',', '.'); ?></strong></span>
                </div>
            </div>
            <div class="mt-3 mt-md-0 d-flex flex-column align-items-end">
                <div class="badge <?= $totalAda >= $totalCount ? 'badge-success' : 'badge-info'; ?> px-3 py-2 font-weight-bold mb-2" style="font-size: 1rem; border-radius: 8px;">
                    <i class="fas fa-clipboard-check mr-1"></i> Status: <?= $totalAda; ?> / <?= $totalCount; ?> Dokumen Lengkap (<?= $persenAda; ?>%)
                </div>
                <div class="d-flex">
                    <a href="<?= base_url('admin/swakelola/perjadin?paket_id=' . $activePaketId); ?>" class="btn btn-outline-warning text-white btn-sm font-weight-bold mr-2">
                        <i class="fas fa-plane-departure mr-1"></i> Daftar Perjadin
                    </a>
                    <?php if (! empty($permissions['export'])): ?>
                        <a href="<?= base_url('admin/swakelola/laporan/export-excel?paket_id=' . $activePaketId); ?>" class="btn btn-outline-light btn-sm font-weight-bold mr-2">
                            <i class="fas fa-file-excel mr-1"></i> Export Checklist (.xlsx)
                        </a>
                    <?php endif; ?>
                    <a href="<?= base_url('admin/swakelola/realisasi?paket_id=' . $activePaketId); ?>" class="btn btn-warning text-dark btn-sm font-weight-bold">
                        <i class="fas fa-receipt mr-1"></i> Realisasi SPM
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Single Inline Feedback Alert for Signer Updates -->
    <div id="signerFeedbackBanner" class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-3" style="display: none; border-radius: 10px;" role="alert">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <i class="fas fa-check-circle mr-2"></i>
                <span id="signerFeedbackText" class="font-weight-bold">Penanda tangan diperbarui.</span>
            </div>
            <div class="d-flex align-items-center mt-2 mt-sm-0">
                <button type="button" class="btn btn-sm btn-light font-weight-bold text-primary mr-3" id="btnPreviewAfterSignerSave" style="display: none;">
                    <i class="fas fa-eye mr-1"></i> Preview Hasil Sekarang
                </button>
                <button type="button" class="close position-static p-0 ml-2" onclick="$('#signerFeedbackBanner').fadeOut(150);" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs / Sections -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list-ol mr-2"></i> Rincian 28 Dokumen Berdasarkan 5 Tahapan Swakelola (LHA No. 9)
            </h6>
            <a href="<?= base_url('admin/swakelola/paket'); ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar Paket
            </a>
        </div>
        <div class="card-body p-3">

            <?php foreach ($grouped as $code => $group): ?>
                <?php
                    $tahapTotal = count($group['items']);
                    $tahapAda   = 0;
                    $tahapFiles = 0;
                    foreach ($group['items'] as $gItem) {
                        if (($gItem['status'] ?? '') === 'Ada') {
                            $tahapAda++;
                        }
                        $tahapFiles += count($gItem['sub_files'] ?? []);
                    }
                ?>
                <div class="card border mb-4 shadow-none" style="border-radius: 8px; overflow: hidden;">
                    <div class="card-header bg-light py-2 px-3 d-flex flex-wrap justify-content-between align-items-center">
                        <span class="font-weight-bold text-dark" style="font-size: 0.95rem;">
                            <i class="fas fa-folder-open mr-2 text-primary"></i> <?= esc($group['label']); ?>
                        </span>
                        <div class="d-flex align-items-center mt-1 mt-sm-0">
                            <span class="badge <?= $tahapAda >= $tahapTotal ? 'badge-success' : 'badge-warning text-dark'; ?> px-2 py-1 mr-2">
                                <i class="fas fa-check-circle mr-1"></i> <?= $tahapAda; ?>/<?= $tahapTotal; ?> Item Lengkap
                            </span>
                            <span class="badge badge-info px-2 py-1 font-weight-normal">
                                <i class="fas fa-copy mr-1"></i> <?= $tahapFiles; ?> Berkas Digital
                            </span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="thead-light">
                                <tr style="font-size: 0.85rem;">
                                    <th class="text-center" style="width: 45px;">No</th>
                                    <th>Nama Dokumen Kelengkapan</th>
                                    <th class="text-center" style="width: 100px;">Status</th>
                                    <th>Nama Berkas Digital</th>
                                    <th>Keterangan / Uraian</th>
                                    <th class="text-center" style="width: 250px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($group['items'] as $doc): ?>
                                    <?php
                                        $subFiles = $doc['sub_files'] ?? [];
                                        $isAda    = ($doc['status'] ?? '') === 'Ada' && ! empty($subFiles);
                                    ?>
                                    <tr>
                                        <td class="text-center font-weight-bold align-middle"><?= $doc['nomor_urut']; ?></td>
                                        <td class="font-weight-bold text-dark align-middle" style="font-size: 0.9rem;">
                                            <?= esc($doc['nama_dokumen']); ?>
                                            <?php if (count($subFiles) > 1): ?>
                                                <span class="badge badge-primary ml-1"><?= count($subFiles); ?> Berkas</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center align-middle">
                                            <?php if (($doc['status'] ?? '') === 'Ada'): ?>
                                                <span class="badge badge-success px-2 py-1">
                                                    <i class="fas fa-check mr-1"></i> Ada
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-warning text-dark px-2 py-1">
                                                    <i class="fas fa-clock mr-1"></i> Belum Ada
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="align-middle">
                                            <?php if (empty($subFiles)): ?>
                                                <small class="text-muted font-italic">
                                                    <i class="fas fa-info-circle mr-1"></i> Belum ada berkas diunggah
                                                </small>
                                            <?php elseif (count($subFiles) === 1): ?>
                                                <?php
                                                    $sf = $subFiles[0];
                                                    $ext = strtolower(pathinfo($sf['file_name'], PATHINFO_EXTENSION));
                                                    $iconClass = ($ext === 'pdf') ? 'fa-file-pdf text-danger' : 'fa-file-word text-primary';
                                                    $extBadge = ($ext === 'pdf') ? 'badge-danger' : 'badge-primary';
                                                ?>
                                                <small class="font-weight-bold d-inline-flex align-items-center flex-wrap">
                                                    <span class="badge <?= $extBadge; ?> mr-1 text-uppercase" style="font-size: 0.65rem;"><?= esc($ext); ?></span>
                                                    <i class="fas <?= $iconClass; ?> mr-1"></i> <?= esc($sf['label']); ?>
                                                </small>
                                            <?php else: ?>
                                                <div class="py-1">
                                                    <?php foreach ($subFiles as $idxSf => $sf): ?>
                                                        <?php
                                                            $ext = strtolower(pathinfo($sf['file_name'], PATHINFO_EXTENSION));
                                                            $iconClass = ($ext === 'pdf') ? 'fa-file-pdf text-danger' : 'fa-file-word text-primary';
                                                            $extBadge = ($ext === 'pdf') ? 'badge-danger' : 'badge-primary';
                                                        ?>
                                                        <div class="d-flex justify-content-between align-items-center <?= $idxSf < count($subFiles) - 1 ? 'mb-2 pb-1 border-bottom' : ''; ?>">
                                                            <small class="font-weight-bold mr-2">
                                                                <span class="badge <?= $extBadge; ?> mr-1 text-uppercase" style="font-size: 0.65rem;"><?= esc($ext); ?></span>
                                                                <i class="fas <?= $iconClass; ?> mr-1"></i> <?= esc($sf['label']); ?>
                                                            </small>
                                                            <div class="btn-group btn-group-sm flex-shrink-0">
                                                                <button type="button"
                                                                    class="btn btn-xs btn-info btn-preview-doc py-0 px-2"
                                                                    data-id="<?= $doc['id']; ?>"
                                                                    data-file="<?= esc($sf['file_name'], 'attr'); ?>"
                                                                    data-title="<?= esc($doc['nama_dokumen'] . ' — ' . $sf['label'], 'attr'); ?>"
                                                                    title="Preview <?= esc($sf['label'], 'attr'); ?>">
                                                                    <i class="fas fa-eye mr-1"></i> Preview
                                                                </button>
                                                                <a href="<?= base_url('admin/swakelola/dokumen/download/' . $doc['id']) . '?file=' . rawurlencode($sf['file_name']); ?>"
                                                                    class="btn btn-xs btn-outline-primary py-0 px-2"
                                                                    title="Unduh <?= esc($sf['label'], 'attr'); ?>">
                                                                    <i class="fas fa-download"></i>
                                                                </a>
                                                                <?php if ($canUploadDoc && $ext === 'docx'): ?>
                                                                    <button type="button"
                                                                        class="btn btn-xs btn-outline-warning text-dark btn-edit-signers py-0 px-2"
                                                                        data-id="<?= $doc['id']; ?>"
                                                                        data-urut="<?= $doc['nomor_urut']; ?>"
                                                                        data-tahap="<?= esc($doc['tahap'], 'attr'); ?>"
                                                                        data-nama="<?= esc($doc['nama_dokumen'], 'attr'); ?>"
                                                                        data-file="<?= esc($sf['file_name'], 'attr'); ?>"
                                                                        data-label="<?= esc($sf['label'], 'attr'); ?>"
                                                                        title="Atur TTD khusus berkas <?= esc($sf['label'], 'attr'); ?>">
                                                                        <i class="fas fa-pen-nib mr-1"></i> TTD
                                                                    </button>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="align-middle">
                                            <small class="text-muted"><?= esc($doc['keterangan']); ?></small>
                                        </td>
                                        <td class="text-center align-middle">
                                            <div class="btn-group btn-group-sm">
                                                <?php if (! empty($subFiles)): ?>
                                                    <?php $primaryFile = $subFiles[0]['file_name'] ?? $doc['file_name']; ?>
                                                    <button type="button"
                                                        class="btn btn-info btn-preview-doc"
                                                        data-id="<?= $doc['id']; ?>"
                                                        data-file="<?= esc($primaryFile, 'attr'); ?>"
                                                        data-title="<?= esc($doc['nama_dokumen'], 'attr'); ?>"
                                                        title="Lihat Pratinjau Dokumen Utama">
                                                        <i class="fas fa-eye mr-1"></i> Preview
                                                    </button>
                                                    <a href="<?= base_url('admin/swakelola/dokumen/download/' . $doc['id']) . '?file=' . rawurlencode($primaryFile); ?>" class="btn btn-outline-primary" title="Unduh Berkas Resmi">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ($canUploadDoc && ! empty($doc['docx_files'])): ?>
                                                    <button type="button"
                                                        class="btn btn-outline-warning text-dark btn-edit-signers"
                                                        data-id="<?= $doc['id']; ?>"
                                                        data-urut="<?= $doc['nomor_urut']; ?>"
                                                        data-tahap="<?= esc($doc['tahap'], 'attr'); ?>"
                                                        data-nama="<?= esc($doc['nama_dokumen'], 'attr'); ?>"
                                                        title="Atur Penanda Tangan Dokumen (.docx) pada Poin <?= $doc['nomor_urut']; ?>">
                                                        <i class="fas fa-pen-nib"></i> TTD
                                                    </button>
                                                <?php endif; ?>
                                                <?php if ($canUploadDoc): ?>
                                                    <button type="button"
                                                        class="btn btn-outline-success btn-upload-doc"
                                                        data-id="<?= $doc['id']; ?>"
                                                        data-urut="<?= $doc['nomor_urut']; ?>"
                                                        data-tahap="<?= esc($doc['tahap'], 'attr'); ?>"
                                                        data-nama="<?= esc($doc['nama_dokumen'], 'attr'); ?>"
                                                        data-status="<?= esc($doc['status'], 'attr'); ?>"
                                                        data-file="<?= esc($doc['file_name'] ?? '', 'attr'); ?>"
                                                        data-ket="<?= esc($doc['keterangan'] ?? '', 'attr'); ?>"
                                                        title="Unggah / Perbarui Berkas Dokumen">
                                                        <i class="fas fa-upload"></i> <?= empty($subFiles) ? 'Upload' : ''; ?>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>
    </div>

</div>

<!-- MODAL UPLOAD / EDIT DOKUMEN CHECKLIST -->
<?php if ($canUploadDoc): ?>
<div class="modal fade" id="modalUploadDokumen" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow border-0" style="border-radius: 12px;">
            <form id="formUploadDokumen" method="post" action="" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="modal-header bg-primary text-white py-3">
                    <h6 class="modal-title font-weight-bold">
                        <i class="fas fa-cloud-upload-alt mr-2"></i> Unggah / Perbarui Dokumen Checklist
                    </h6>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-light border mb-3 py-2 px-3">
                        <small class="text-muted d-block">Item Checklist:</small>
                        <strong class="text-dark" id="uploadDocLabel">-</strong>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold small">Pilih Berkas Digital (.pdf / .docx / .xlsx)</label>
                        <input type="file" name="file_dokumen" class="form-control-file border p-2 rounded" accept=".pdf,.docx,.xlsx,.doc,.xls">
                        <small class="text-muted d-block mt-1" id="uploadCurrentFile">Biarkan kosong jika hanya mengubah keterangan/status.</small>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold small">Status Kelengkapan</label>
                        <select name="status" id="uploadDocStatus" class="form-control form-control-sm">
                            <option value="Ada">Ada (Lengkap)</option>
                            <option value="Belum Ada">Belum Ada</option>
                        </select>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold small">Keterangan / Nomor Dokumen</label>
                        <textarea name="keterangan" id="uploadDocKeterangan" rows="3" class="form-control form-control-sm" placeholder="Contoh: SK KPA Nomor ... atau Keterangan dokumen"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary font-weight-bold">
                        <i class="fas fa-save mr-1"></i> Simpan &amp; Unggah
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- MODAL ATUR PENANDA TANGAN DINAMIS PER POIN (.DOCX) -->
<?php if ($canUploadDoc): ?>
<div class="modal fade" id="modalEditSigners" tabindex="-1" role="dialog" aria-hidden="true" style="overscroll-behavior: contain;">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document" style="max-height: calc(100vh - 2rem); margin: 1rem auto;">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px; max-height: calc(100vh - 2rem); display: flex; flex-direction: column; overflow: hidden;">
            <div class="modal-header bg-dark text-white py-3 px-4" style="flex-shrink: 0;">
                <div>
                    <h6 class="modal-title font-weight-bold mb-0 text-white">
                        <i class="fas fa-pen-nib text-warning mr-2"></i> Atur Penanda Tangan Dokumen (Dinamis per Poin)
                    </h6>
                    <small class="text-white-50" id="signerPointSubtitle">-</small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" style="flex: 1 1 auto; overflow-y: auto; -webkit-overflow-scrolling: touch; overscroll-behavior: contain;">
                <form id="formEditSigners" method="post" action="">
                    <input type="hidden" id="signerCsrfInput" name="<?= csrf_token(); ?>" value="<?= csrf_hash(); ?>">
                    <input type="hidden" id="signerDocId" value="">
                    <div id="signerModalAlert" class="alert alert-danger py-2 px-3 small mb-3" style="display: none;"></div>

                    <!-- Baris Pengaturan Umum & Salin Poin -->
                    <div class="row mb-3">
                        <div class="col-md-4 mb-2">
                            <label class="font-weight-bold small mb-1">Format Penampilan TTD</label>
                            <select id="signerLayoutMode" class="form-control form-control-sm">
                                <option value="columns">Blok Sejajar / Kolom (Contoh: Poin 8, 9, 24)</option>
                                <option value="table">Tabel Berbingkai No/Nama/Jabatan/TTD (Contoh: Poin 10, 25)</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="font-weight-bold small mb-1">Tempat &amp; Tanggal Pengesahan</label>
                            <input type="text" id="signerDateText" class="form-control form-control-sm" placeholder="Contoh: Pekanbaru, 21 November 2025">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="font-weight-bold small mb-1">Terapkan ke Berkas Word (.docx)</label>
                            <select id="signerTargetFile" class="form-control form-control-sm">
                                <option value="__all__">Semua berkas .docx pada poin ini</option>
                            </select>
                        </div>
                    </div>

                    <!-- Salin dari Poin Lain -->
                    <div class="card bg-light border mb-3" style="border-radius: 8px;">
                        <div class="card-body py-2 px-3 d-flex flex-wrap justify-content-between align-items-center">
                            <small class="font-weight-bold text-dark mr-2">
                                <i class="fas fa-copy text-primary mr-1"></i> Salin Penanda Tangan dari Poin Lain:
                            </small>
                            <div class="d-flex align-items-center flex-grow-1 flex-md-grow-0 mt-2 mt-md-0" style="min-width: 280px;">
                                <select id="signerCopySourceSelect" class="form-control form-control-sm mr-2">
                                    <option value="">-- Pilih Poin Sumber --</option>
                                    <?php foreach (($copySourcePoints ?? []) as $cp): ?>
                                        <option value="<?= $cp['id']; ?>">
                                            Poin <?= $cp['nomor_urut']; ?> (<?= esc($cp['tahap']); ?>) — <?= esc($cp['nama']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn btn-sm btn-outline-primary flex-shrink-0 font-weight-bold" id="btnCopySignersFromPoint">
                                    <i class="fas fa-download mr-1"></i> Muat TTD
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Daftar Penanda Tangan Dinamis -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="font-weight-bold small text-dark">
                            <i class="fas fa-users text-primary mr-1"></i> Daftar Penanda Tangan (Urut Kiri &rarr; Kanan atau Baris 1 &rarr; N)
                        </span>
                        <button type="button" class="btn btn-sm btn-success font-weight-bold" id="btnAddSignerRow">
                            <i class="fas fa-plus mr-1"></i> Tambah Penanda Tangan
                        </button>
                    </div>

                    <div id="signerRowsContainer">
                        <div class="text-center py-4 text-muted">
                            <div class="spinner-border spinner-border-sm text-primary mr-2"></div> Membaca penanda tangan dari dokumen Word (.docx)...
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light py-2 px-4 d-flex justify-content-between align-items-center" style="flex-shrink: 0;">
                <small class="text-muted">Perubahan akan menulis ulang blok tanda tangan pada berkas <code>.docx</code> secara otomatis.</small>
                <div>
                    <button type="button" class="btn btn-sm btn-secondary mr-1" data-dismiss="modal">Batal</button>
                    <button type="submit" form="formEditSigners" class="btn btn-sm btn-primary font-weight-bold" id="btnSaveSigners">
                        <i class="fas fa-save mr-1"></i> Simpan &amp; Terapkan ke Dokumen
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- MODAL PREVIEW DOKUMEN -->
<div class="modal fade" id="modalPreviewDokumen" tabindex="-1" role="dialog" aria-labelledby="modalPreviewTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document" style="max-width: 92vw;">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden; height: 92vh;">
            <!-- Modal Header -->
            <div class="modal-header bg-dark text-white py-3 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center overflow-hidden mr-3">
                    <span id="previewDocIcon" class="mr-2" style="font-size: 1.3rem;">
                        <i class="fas fa-file-alt text-warning"></i>
                    </span>
                    <div class="text-truncate">
                        <h6 class="modal-title font-weight-bold text-white mb-0 text-truncate" id="previewModalTitle">
                            Pratinjau Dokumen
                        </h6>
                        <small class="text-white-50" id="previewDocSubtitle">-</small>
                    </div>
                </div>
                <div class="d-flex align-items-center flex-shrink-0">
                    <a href="#" target="_blank" class="btn btn-sm btn-outline-light mr-2 font-weight-bold" id="previewBtnNewTab">
                        <i class="fas fa-external-link-alt mr-1"></i> Buka Tab Baru
                    </a>
                    <a href="#" class="btn btn-sm btn-success mr-2 font-weight-bold" id="previewBtnDownload">
                        <i class="fas fa-download mr-1"></i> Unduh
                    </a>
                    <button type="button" class="close text-white ml-2" data-dismiss="modal" aria-label="Close" style="opacity: 0.85;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-0 position-relative" style="background-color: #525659; height: calc(92vh - 65px); overflow: hidden;">
                <div id="previewLoading" class="position-absolute w-100 h-100 flex-column justify-content-center align-items-center" style="display: none; background: rgba(30, 41, 59, 0.88); z-index: 1050; color: #fff;">
                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                    <h6 class="font-weight-bold mb-1" id="previewLoadingText">Memuat berkas dokumen...</h6>
                    <small class="text-white-50">Menyiapkan pratinjau halaman</small>
                </div>

                <!-- Iframe Viewer for PDF -->
                <iframe id="previewIframe" src="" style="width: 100%; height: 100%; border: none; display: none;"></iframe>

                <!-- Div Container for DOCX render -->
                <div id="docxPreviewWrapper" style="width: 100%; height: 100%; overflow-y: auto; padding: 25px; display: none;">
                    <div id="docxContainer" style="max-width: 850px; margin: 0 auto; background: #ffffff; padding: 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); border-radius: 4px; min-height: 100%;"></div>
                </div>

                <!-- Fallback / Error message -->
                <div id="previewError" class="p-5 text-center text-white" style="display: none;">
                    <i class="fas fa-exclamation-circle text-danger mb-3" style="font-size: 3rem;"></i>
                    <h5 class="font-weight-bold" id="previewErrorMsg">Gagal memuat pratinjau</h5>
                    <p class="text-white-50 small mb-4">Anda tetap dapat mengunduh berkas ini secara langsung ke perangkat Anda.</p>
                    <a href="#" class="btn btn-primary" id="previewErrorDownloadBtn">
                        <i class="fas fa-download mr-1"></i> Unduh Berkas Sekarang
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<!-- Local copies of JSZip and docx-preview with CDN fallback -->
<script src="<?= base_url('assets/docx/jszip.min.js'); ?>"></script>
<script>
if (typeof JSZip === 'undefined') {
    document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"><\/script>');
}
</script>

<script src="<?= base_url('assets/docx/docx-preview.min.js'); ?>"></script>
<script>
if (typeof docx === 'undefined') {
    document.write('<script src="https://unpkg.com/docx-preview@0.3.3/dist/docx-preview.min.js"><\/script>');
}
</script>

<style>
#docxContainer {
    font-family: Arial, sans-serif;
    color: #000;
}
#docxContainer table {
    border-collapse: collapse;
    max-width: 100%;
}
#docxContainer img {
    max-width: 100% !important;
    height: auto !important;
}
</style>

<script>
$(document).ready(function() {
    $(document).on('click', '.btn-upload-doc', function() {
        const id     = $(this).data('id');
        const urut   = $(this).data('urut');
        const tahap  = $(this).data('tahap');
        const nama   = $(this).data('nama');
        const status = $(this).data('status') || 'Ada';
        const file   = $(this).data('file') || '';
        const ket    = $(this).data('ket') || '';

        $('#formUploadDokumen').attr('action', '<?= base_url('admin/swakelola/dokumen'); ?>/' + id + '/upload');
        $('#uploadDocLabel').text('[Tahap ' + tahap + ' - No. ' + urut + '] ' + nama);
        $('#uploadDocStatus').val(status);
        $('#uploadDocKeterangan').val(ket);
        if (file) {
            $('#uploadCurrentFile').text('Berkas saat ini: ' + file + ' (Pilih file baru untuk mengganti)');
        } else {
            $('#uploadCurrentFile').text('Belum ada berkas terunggah. Silakan pilih berkas PDF/DOCX.');
        }
        $('#modalUploadDokumen').modal('show');
    });

    function showLoader(text) {
        $('#previewLoadingText').text(text || 'Memuat berkas...');
        $('#previewLoading').css('display', 'flex');
    }

    function hideLoader() {
        $('#previewLoading').css('display', 'none');
    }

    $(document).on('click', '.btn-preview-doc', function() {
        const docId = $(this).data('id') || 0;
        const customFile = $(this).attr('data-file') || '';
        const customTitle = $(this).attr('data-title') || '';
        
        showLoader('Memuat informasi dokumen...');
        $('#previewIframe').hide().attr('src', '');
        $('#docxPreviewWrapper').hide();
        $('#docxContainer').empty();
        $('#previewError').hide();
        
        $('#modalPreviewDokumen').modal('show');

        $.ajax({
            url: '<?= base_url('admin/swakelola/dokumen/preview'); ?>/' + docId,
            type: 'GET',
            data: {
                file: customFile,
                title: customTitle
            },
            dataType: 'json',
            success: function(res) {
                if (res.status !== 'success') {
                    showError(res.message || 'Gagal memuat dokumen');
                    return;
                }

                const sizeKb = res.file_size ? (res.file_size > 1048576 ? (res.file_size / 1048576).toFixed(2) + ' MB' : Math.max(1, Math.round(res.file_size / 1024)) + ' KB') : '-';
                const ketText = res.keterangan ? ' • ' + res.keterangan : '';
                $('#previewModalTitle').text(res.nomor_urut + '. ' + res.nama_dokumen);
                $('#previewDocSubtitle').text(res.file_name + ' (' + sizeKb + ')' + ketText);
                $('#previewBtnDownload').attr('href', res.download_url);
                $('#previewBtnNewTab').attr('href', res.file_url || res.inline_url);
                $('#previewErrorDownloadBtn').attr('href', res.download_url);

                if (!res.file_exists) {
                    showError('Berkas fisik (' + res.file_name + ') belum ditemukan di server.');
                    return;
                }

                if (res.ext === 'pdf') {
                    $('#previewDocIcon').html('<i class="fas fa-file-pdf text-danger"></i>');
                    showLoader('Menyiapkan tampilan PDF...');
                    
                    const pdfSrc = res.file_url || res.inline_url;
                    $('#previewIframe')
                        .off('load')
                        .on('load', function() {
                            hideLoader();
                        })
                        .attr('src', pdfSrc)
                        .show();

                    setTimeout(function() {
                        hideLoader();
                    }, 400);
                } else if (res.ext === 'docx') {
                    $('#previewDocIcon').html('<i class="fas fa-file-word text-primary"></i>');
                    showLoader('Merender dokumen Word (.docx)...');

                    $('#docxPreviewWrapper').show();

                    fetch(res.inline_url)
                        .then(function(response) {
                            if (!response.ok) throw new Error('HTTP ' + response.status);
                            return response.arrayBuffer();
                        })
                        .then(function(buffer) {
                            const container = document.getElementById('docxContainer');
                            if (typeof docx !== 'undefined' && docx.renderAsync) {
                                docx.renderAsync(buffer, container, null, {
                                    className: "docx-preview-content",
                                    inWrapper: false,
                                    ignoreWidth: true,
                                    ignoreHeight: true,
                                    ignoreFonts: true,
                                    breakPages: true
                                }).then(function() {
                                    hideLoader();
                                }).catch(function(err) {
                                    console.warn('Docx render notice:', err);
                                    hideLoader();
                                });

                                setTimeout(function() {
                                    hideLoader();
                                }, 250);
                            } else {
                                hideLoader();
                                showError('Library docx-preview tidak tersedia.');
                            }
                        })
                        .catch(function(err) {
                            hideLoader();
                            console.error('Fetch error:', err);
                            showError('Gagal mengunduh berkas Word untuk pratinjau: ' + err.message);
                        });
                } else {
                    hideLoader();
                    $('#previewDocIcon').html('<i class="fas fa-file text-warning"></i>');
                    showError('Format berkas ' + res.ext.toUpperCase() + ' tidak mendukung pratinjau langsung. Silakan unduh berkas.');
                }
            },
            error: function(xhr, status, error) {
                hideLoader();
                showError('Koneksi ke server gagal: ' + error);
            }
        });
    });

    function showError(msg) {
        hideLoader();
        $('#previewIframe').hide();
        $('#docxPreviewWrapper').hide();
        $('#previewErrorMsg').text(msg);
        $('#previewError').fadeIn(150);
    }

    $('#modalPreviewDokumen').on('hidden.bs.modal', function() {
        hideLoader();
        $('#previewIframe').attr('src', '');
        $('#docxContainer').empty();
    });

    <?php if ($canUploadDoc): ?>
    $('#modalEditSigners').appendTo('body');
    $('#modalEditSigners').on('shown.bs.modal', function() {
        $(this).find('.modal-body').scrollTop(0);
    });

    let signerPegawaiList = [];
    let signerCurrentList = [];
    let lastUpdatedDocId  = 0;
    let lastUpdatedFile   = '';
    let lastUpdatedTitle  = '';

    function escHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function syncSignerStateFromDom() {
        const rows = [];
        $('#signerRowsContainer .signer-item-card').each(function() {
            rows.push({
                action:  $(this).find('.signer-input-action').val() || '',
                jabatan: $(this).find('.signer-input-jabatan').val() || '',
                nama:    $(this).find('.signer-input-nama').val() || '',
                nip:     $(this).find('.signer-input-nip').val() || ''
            });
        });
        if (rows.length > 0) {
            signerCurrentList = rows;
        }
    }

    function renderSignerRows() {
        const container = $('#signerRowsContainer');
        const mode      = $('#signerLayoutMode').val() || 'columns';
        if (!signerCurrentList || signerCurrentList.length === 0) {
            signerCurrentList = [{ action: 'Menetapkan,', jabatan: 'Pejabat Pembuat Komitmen (PPK)', nama: '', nip: '' }];
        }

        let pegawaiOpts = '<option value="">-- Pilih cepat dari Master Pegawai (opsional) --</option>';
        signerPegawaiList.forEach(function(p, idx) {
            pegawaiOpts += '<option value="' + idx + '">' + escHtml(p.nama) + (p.nip ? ' (' + escHtml(p.nip) + ')' : '') + '</option>';
        });

        let html = '';
        signerCurrentList.forEach(function(s, idx) {
            const actionColStyle = (mode === 'table') ? 'style="display:none;"' : '';
            html += ''
                + '<div class="card border mb-2 signer-item-card" data-index="' + idx + '" style="border-radius: 8px;">'
                + '  <div class="card-header bg-light py-1 px-3 d-flex justify-content-between align-items-center">'
                + '    <span class="badge badge-primary">Penanda Tangan #' + (idx + 1) + '</span>'
                + '    <div class="btn-group btn-group-xs">'
                + '      <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 btn-signer-up" data-idx="' + idx + '" ' + (idx === 0 ? 'disabled' : '') + ' title="Geser ke Atas/Kiri"><i class="fas fa-arrow-up"></i></button>'
                + '      <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 btn-signer-down" data-idx="' + idx + '" ' + (idx === signerCurrentList.length - 1 ? 'disabled' : '') + ' title="Geser ke Bawah/Kanan"><i class="fas fa-arrow-down"></i></button>'
                + '      <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 btn-signer-remove" data-idx="' + idx + '" ' + (signerCurrentList.length <= 1 ? 'disabled' : '') + ' title="Hapus Penanda Tangan"><i class="fas fa-trash"></i></button>'
                + '    </div>'
                + '  </div>'
                + '  <div class="card-body py-2 px-3">'
                + '    <div class="form-row">'
                + '      <div class="col-md-6 mb-2">'
                + '        <label class="small font-weight-bold text-muted mb-0">Isi Cepat Master Pegawai</label>'
                + '        <select class="form-control form-control-sm signer-select-pegawai">' + pegawaiOpts + '</select>'
                + '      </div>'
                + '      <div class="col-md-6 mb-2 signer-action-col" ' + actionColStyle + '>'
                + '        <label class="small font-weight-bold mb-0">Keterangan Atas (Sejajar)</label>'
                + '        <input type="text" class="form-control form-control-sm signer-input-action" value="' + escHtml(s.action) + '" placeholder="Contoh: Menetapkan, / Menyusun, / Mengetahui,">'
                + '      </div>'
                + '    </div>'
                + '    <div class="form-row">'
                + '      <div class="col-md-5 mb-2 mb-md-0">'
                + '        <label class="small font-weight-bold mb-0">Jabatan / Kedudukan di TTD</label>'
                + '        <textarea rows="2" class="form-control form-control-sm signer-input-jabatan" placeholder="Contoh: Pejabat Pembuat Komitmen (PPK)&#10;Satker Pelaksanaan Prasarana Strategis Riau,">' + escHtml(s.jabatan) + '</textarea>'
                + '      </div>'
                + '      <div class="col-md-4 mb-2 mb-md-0">'
                + '        <label class="small font-weight-bold mb-0">Nama Lengkap &amp; Gelar <span class="text-danger">*</span></label>'
                + '        <input type="text" class="form-control form-control-sm signer-input-nama" value="' + escHtml(s.nama) + '" required placeholder="Contoh: Nurhidayat Nugroho, S.Ars.">'
                + '      </div>'
                + '      <div class="col-md-3">'
                + '        <label class="small font-weight-bold mb-0">NIP / NRP</label>'
                + '        <input type="text" class="form-control form-control-sm signer-input-nip" value="' + escHtml(s.nip) + '" placeholder="NIP. 19901221 201802 1 001">'
                + '      </div>'
                + '    </div>'
                + '  </div>'
                + '</div>';
        });
        container.html(html);
    }

    function loadSignersForDoc(docId, fileOverride, keepTargetList) {
        $('#signerModalAlert').hide().text('');
        $('#signerRowsContainer').html('<div class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary mr-2"></div> Membaca penanda tangan dari dokumen...</div>');

        $.ajax({
            url: '<?= base_url('admin/swakelola/dokumen/signers'); ?>/' + docId,
            type: 'GET',
            data: fileOverride ? { file: fileOverride } : {},
            dataType: 'json',
            success: function(res) {
                if (res.csrf_hash) {
                    $('#signerCsrfInput').val(res.csrf_hash);
                }
                if (res.status !== 'success') {
                    $('#signerModalAlert').text(res.message || 'Gagal membaca data penanda tangan.').show();
                    $('#signerRowsContainer').empty();
                    return;
                }

                signerPegawaiList = res.pegawai || [];
                signerCurrentList = res.signers || [];
                $('#signerLayoutMode').val(res.layout_mode || 'columns');
                $('#signerDateText').val(res.date_text || '');

                if (!keepTargetList) {
                    const targetSel = $('#signerTargetFile');
                    targetSel.empty();
                    const files = res.docx_files || [];
                    if (files.length > 1) {
                        targetSel.append('<option value="__all__">Semua berkas .docx pada poin ini (' + files.length + ' berkas)</option>');
                    }
                    files.forEach(function(f) {
                        const optLabel = f.label && f.label !== f.file_name
                            ? (f.label + ' [' + f.file_name + ']')
                            : f.file_name;
                        targetSel.append('<option value="' + escHtml(f.file_name) + '">' + escHtml(optLabel) + '</option>');
                    });
                    if (fileOverride) {
                        targetSel.val(fileOverride);
                    } else if (files.length > 1) {
                        targetSel.val('__all__');
                    }
                }

                renderSignerRows();
            },
            error: function(xhr, status, error) {
                $('#signerModalAlert').text('Gagal menghubungi server: ' + error).show();
                $('#signerRowsContainer').empty();
            }
        });
    }

    let currentSignerBaseSubtitle = '';

    $(document).on('click', '.btn-edit-signers', function() {
        const id         = $(this).data('id');
        const urut       = $(this).data('urut');
        const tahap      = $(this).data('tahap');
        const nama       = $(this).data('nama');
        const targetFile = $(this).attr('data-file') || '';
        const targetLbl  = $(this).attr('data-label') || targetFile;

        $('#signerDocId').val(id);
        lastUpdatedTitle = urut + '. ' + nama;
        currentSignerBaseSubtitle = '[Tahap ' + tahap + ' - Poin ' + urut + '] ' + nama;
        $('#signerPointSubtitle').text(targetLbl ? (currentSignerBaseSubtitle + ' — Berkas: ' + targetLbl) : currentSignerBaseSubtitle);
        $('#signerCopySourceSelect').val('');
        $('#modalEditSigners').modal('show');
        loadSignersForDoc(id, targetFile, false);
    });

    $('#signerLayoutMode').on('change', function() {
        syncSignerStateFromDom();
        renderSignerRows();
    });

    $('#signerTargetFile').on('change', function() {
        const docId = $('#signerDocId').val();
        const val   = $(this).val();
        if (val && val !== '__all__') {
            $('#signerPointSubtitle').text(currentSignerBaseSubtitle + ' — Berkas: ' + val);
            if (docId) {
                loadSignersForDoc(docId, val, true);
            }
        } else {
            $('#signerPointSubtitle').text(currentSignerBaseSubtitle + ' — Semua Berkas (.docx)');
            if (docId) {
                loadSignersForDoc(docId, '', true);
            }
        }
    });

    $('#btnCopySignersFromPoint').on('click', function() {
        const srcId = $('#signerCopySourceSelect').val();
        if (!srcId) {
            $('#signerModalAlert').text('Silakan pilih poin sumber terlebih dahulu.').show();
            return;
        }
        $('#signerModalAlert').hide();
        $.ajax({
            url: '<?= base_url('admin/swakelola/dokumen/signers'); ?>/' + srcId,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.csrf_hash) {
                    $('#signerCsrfInput').val(res.csrf_hash);
                }
                if (res.status !== 'success') {
                    $('#signerModalAlert').text(res.message || 'Gagal menyalin TTD dari poin sumber.').show();
                    return;
                }
                signerCurrentList = res.signers || [];
                $('#signerLayoutMode').val(res.layout_mode || 'columns');
                if (res.date_text) {
                    $('#signerDateText').val(res.date_text);
                }
                renderSignerRows();
            }
        });
    });

    $('#btnAddSignerRow').on('click', function() {
        syncSignerStateFromDom();
        signerCurrentList.push({
            action: 'Mengetahui,',
            jabatan: '',
            nama: '',
            nip: ''
        });
        renderSignerRows();
    });

    $(document).on('click', '.btn-signer-up', function() {
        const idx = parseInt($(this).data('idx'), 10);
        syncSignerStateFromDom();
        if (idx > 0 && idx < signerCurrentList.length) {
            const tmp = signerCurrentList[idx - 1];
            signerCurrentList[idx - 1] = signerCurrentList[idx];
            signerCurrentList[idx] = tmp;
            renderSignerRows();
        }
    });

    $(document).on('click', '.btn-signer-down', function() {
        const idx = parseInt($(this).data('idx'), 10);
        syncSignerStateFromDom();
        if (idx >= 0 && idx < signerCurrentList.length - 1) {
            const tmp = signerCurrentList[idx + 1];
            signerCurrentList[idx + 1] = signerCurrentList[idx];
            signerCurrentList[idx] = tmp;
            renderSignerRows();
        }
    });

    $(document).on('click', '.btn-signer-remove', function() {
        const idx = parseInt($(this).data('idx'), 10);
        syncSignerStateFromDom();
        if (signerCurrentList.length > 1) {
            signerCurrentList.splice(idx, 1);
            renderSignerRows();
        }
    });

    $(document).on('change', '.signer-select-pegawai', function() {
        const pIdx = $(this).val();
        if (pIdx === '') return;
        const p = signerPegawaiList[parseInt(pIdx, 10)];
        if (!p) return;
        const card = $(this).closest('.signer-item-card');
        card.find('.signer-input-nama').val(p.nama || '');
        card.find('.signer-input-nip').val(p.nip || '');
        if (!card.find('.signer-input-jabatan').val().trim() && p.jabatan) {
            card.find('.signer-input-jabatan').val(p.jabatan);
        }
    });

    $('#formEditSigners').on('submit', function(e) {
        e.preventDefault();
        syncSignerStateFromDom();
        const docId      = $('#signerDocId').val();
        const layoutMode = $('#signerLayoutMode').val();
        const dateText   = $('#signerDateText').val();
        const targetFile = $('#signerTargetFile').val();
        const csrfName   = $('#signerCsrfInput').attr('name');
        const csrfVal    = $('#signerCsrfInput').val();

        const validRows = signerCurrentList.filter(function(r) {
            return (r.nama || '').trim() !== '';
        });
        if (validRows.length === 0) {
            $('#signerModalAlert').text('Minimal 1 penanda tangan harus memiliki Nama Lengkap.').show();
            return;
        }

        const btn = $('#btnSaveSigners');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-1"></span> Menyimpan...');
        $('#signerModalAlert').hide();

        const postData = {
            layout_mode: layoutMode,
            date_text: dateText,
            target_file: targetFile,
            signers_json: JSON.stringify(validRows)
        };
        postData[csrfName] = csrfVal;

        $.ajax({
            url: '<?= base_url('admin/swakelola/dokumen'); ?>/' + docId + '/signers',
            type: 'POST',
            data: postData,
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan &amp; Terapkan ke Dokumen');
                if (res.csrf_hash) {
                    $('#signerCsrfInput').val(res.csrf_hash);
                }
                if (res.status !== 'success') {
                    $('#signerModalAlert').text(res.message || 'Gagal menyimpan penanda tangan.').show();
                    return;
                }

                $('#modalEditSigners').modal('hide');
                lastUpdatedDocId = docId;
                lastUpdatedFile  = (res.updated_files && res.updated_files.length > 0) ? res.updated_files[0] : '';

                $('#signerFeedbackText').text(res.message);
                if (lastUpdatedFile) {
                    $('#btnPreviewAfterSignerSave').show();
                } else {
                    $('#btnPreviewAfterSignerSave').hide();
                }
                $('#signerFeedbackBanner').stop(true, true).fadeIn(180);
                $('html, body').animate({ scrollTop: 0 }, 250);
            },
            error: function(xhr, status, error) {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan &amp; Terapkan ke Dokumen');
                let msg = 'Gagal menyimpan perubahan: ' + error;
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                if (xhr.responseJSON && xhr.responseJSON.csrf_hash) {
                    $('#signerCsrfInput').val(xhr.responseJSON.csrf_hash);
                }
                $('#signerModalAlert').text(msg).show();
            }
        });
    });

    $('#btnPreviewAfterSignerSave').on('click', function() {
        if (!lastUpdatedDocId || !lastUpdatedFile) return;
        const fakeBtn = $('<button>')
            .addClass('btn-preview-doc')
            .data('id', lastUpdatedDocId)
            .attr('data-file', lastUpdatedFile)
            .attr('data-title', lastUpdatedTitle);
        fakeBtn.appendTo('body').trigger('click').remove();
    });
    <?php endif; ?>
});
</script>
<?= $this->endSection(); ?>
