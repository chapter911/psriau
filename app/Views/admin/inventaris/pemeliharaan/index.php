<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="content-header pb-2">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold" style="font-size: 1.5rem;">
                    <i class="fas fa-wrench text-warning mr-2"></i>Pemeliharaan Aset BMN
                </h1>
                <p class="text-muted small mb-0 mt-1">
                    Pengelolaan dan penerbitan Surat Keterangan Pemeliharaan Peralatan & Mesin beserta Lampiran Foto Fisik 4 Sudut
                </p>
            </div>
            <div class="col-sm-6 text-sm-right mt-2 mt-sm-0">
                <a href="<?= site_url('admin/inventaris/barang'); ?>" class="btn btn-outline-secondary btn-sm shadow-sm mr-2" style="border-radius: 6px;" title="Buka Daftar Barang BMN">
                    <i class="fas fa-boxes mr-1"></i>Daftar Barang
                </a>
                <?php if (! empty($permissions['can_add'])): ?>
                    <a href="<?= site_url('admin/inventaris/pemeliharaan/tambah'); ?>" class="btn btn-primary btn-sm shadow-sm font-weight-bold" style="border-radius: 6px;">
                        <i class="fas fa-plus-circle mr-1"></i>Buat Surat Pemeliharaan
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- Filter & Search Toolbar -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 10px;">
            <div class="card-body p-3">
                <form method="get" action="<?= site_url('admin/inventaris/pemeliharaan'); ?>" class="row align-items-center">
                    <div class="col-md-5 mb-2 mb-md-0">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white border-right-0" style="border-radius: 6px 0 0 6px;">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                            </div>
                            <input type="text" name="q" value="<?= esc($filters['q'] ?? ''); ?>" class="form-control border-left-0" placeholder="Cari nomor surat, perihal, atau nama pejabat..." style="border-radius: 0 6px 6px 0;">
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-2 mb-md-0">
                        <select name="tahun" class="form-control form-control-sm" style="border-radius: 6px;">
                            <option value="">Semua Tahun Surat</option>
                            <?php foreach ($tahunList as $th): ?>
                                <option value="<?= esc($th); ?>" <?= ((string)($filters['tahun'] ?? '') === (string)$th) ? 'selected' : ''; ?>>Tahun <?= esc($th); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 col-6 mb-2 mb-md-0">
                        <select name="jenis" class="form-control form-control-sm" style="border-radius: 6px;">
                            <option value="">Semua Jenis</option>
                            <option value="TIK" <?= (($filters['jenis'] ?? '') === 'TIK') ? 'selected' : ''; ?>>Peralatan TIK</option>
                            <option value="NON TIK" <?= (($filters['jenis'] ?? '') === 'NON TIK') ? 'selected' : ''; ?>>Non-TIK</option>
                            <option value="UMUM" <?= (($filters['jenis'] ?? '') === 'UMUM') ? 'selected' : ''; ?>>Umum / Lainnya</option>
                        </select>
                    </div>
                    <div class="col-md-2 text-right">
                        <button type="submit" class="btn btn-info btn-sm shadow-sm" style="border-radius: 6px;" title="Terapkan Filter">
                            <i class="fas fa-filter mr-1"></i>Filter
                        </button>
                        <a href="<?= site_url('admin/inventaris/pemeliharaan'); ?>" class="btn btn-outline-secondary btn-sm shadow-sm ml-1" style="border-radius: 6px;" title="Reset Filter">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Daftar Surat Pemeliharaan -->
        <div class="card shadow-sm border-0" style="border-radius: 10px; overflow: hidden;">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-file-alt text-primary mr-2"></i>Arsip Surat Keterangan Pemeliharaan
                </h6>
                <span class="badge badge-light border text-muted px-2.5 py-1">
                    Total: <strong><?= number_format($totalRows); ?></strong> Dokumen
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 45px;" class="text-center">No</th>
                                <th style="width: 220px;">Nomor Surat</th>
                                <th style="width: 110px;">Tanggal</th>
                                <th>Perihal / Judul Surat</th>
                                <th style="width: 180px;">Penandatangan (KPB)</th>
                                <th style="width: 85px;" class="text-center">Jml Item</th>
                                <th style="width: 140px;" class="text-center">Lampiran Foto</th>
                                <th style="width: 165px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($list)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fas fa-folder-open fa-3x mb-3 text-secondary" style="opacity: 0.4;"></i>
                                        <div class="font-weight-bold" style="font-size: 1rem;">Belum Ada Arsip Surat Pemeliharaan</div>
                                        <div class="small">Klik tombol <strong>Buat Surat Pemeliharaan</strong> di atas untuk membuat dokumen baru beserta lampiran fotonya.</div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = $offset + 1; ?>
                                <?php foreach ($list as $row): ?>
                                    <tr>
                                        <td class="text-center text-muted"><?= $no++; ?></td>
                                        <td>
                                            <div class="font-weight-bold text-dark mb-0.5">
                                                <?= esc($row['no_surat']); ?>
                                            </div>
                                            <span class="badge badge-light border text-primary" style="font-size: 0.72rem;">
                                                <i class="fas fa-tag mr-1"></i><?= esc($row['jenis_pemeliharaan']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="font-weight-bold text-dark">
                                                <?= date('d/m/Y', strtotime($row['tanggal_surat'])); ?>
                                            </div>
                                            <small class="text-muted"><?= esc($row['tempat_surat'] ?: 'Pekanbaru'); ?></small>
                                        </td>
                                        <td>
                                            <div class="font-weight-bold text-dark" style="line-height: 1.35;">
                                                <?= esc($row['judul_surat']); ?>
                                            </div>
                                            <?php if (! empty($row['keterangan'])): ?>
                                                <small class="text-muted d-block mt-0.5 text-truncate" style="max-width: 320px;">
                                                    <i class="fas fa-info-circle mr-1"></i><?= esc($row['keterangan']); ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="font-weight-bold text-dark" style="font-size: 0.84rem;">
                                                <?= esc($row['nama_kasatker']); ?>
                                            </div>
                                            <small class="text-muted d-block" style="font-size: 0.76rem;">
                                                NIP. <?= esc($row['nip_kasatker'] ?: '-'); ?>
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-info px-2 py-1 font-weight-bold" style="border-radius: 4px; font-size: 0.78rem;">
                                                <?= number_format($row['total_item'] ?? 0); ?> Aset
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                                $fotoCol = $row['foto_color'] ?? 'danger';
                                                $fotoLbl = $row['foto_label'] ?? 'Belum Ada Foto';
                                                $iconColorClass = ($fotoCol === 'success') ? 'text-success' : (($fotoCol === 'warning') ? 'text-warning' : 'text-danger');
                                            ?>
                                            <div class="d-flex align-items-center justify-content-center" title="<?= esc($fotoLbl); ?>">
                                                <i class="fas fa-camera <?= $iconColorClass; ?> mr-1.5" style="font-size: 1.15rem;"></i>
                                                <span class="badge badge-<?= $fotoCol; ?> px-2 py-1 font-weight-bold" style="border-radius: 4px; font-size: 0.74rem;">
                                                    <?= esc($fotoLbl); ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <?php if (! empty($permissions['can_export'])): ?>
                                                    <a href="<?= site_url('admin/inventaris/pemeliharaan/' . $row['id'] . '/cetak-pdf'); ?>" target="_blank" class="btn btn-outline-danger shadow-sm" style="border-radius: 5px 0 0 5px;" title="Cetak / Pratinjau PDF Resmi Lengkap (Surat + Foto)">
                                                        <i class="fas fa-file-pdf mr-1"></i>PDF
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (! empty($permissions['can_edit'])): ?>
                                                    <a href="<?= site_url('admin/inventaris/pemeliharaan/' . $row['id'] . '/ubah'); ?>" class="btn btn-outline-primary shadow-sm" title="Ubah Surat & Kelola Foto">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (! empty($permissions['can_delete'])): ?>
                                                    <button type="button" class="btn btn-outline-danger shadow-sm btn-delete-surat" data-id="<?= esc($row['id']); ?>" data-nosurat="<?= esc($row['no_surat']); ?>" style="border-radius: 0 5px 5px 0;" title="Hapus Surat Pemeliharaan">
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
            <?php if ($totalPages > 1): ?>
                <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
                    <span class="small text-muted">
                        Menampilkan halaman <strong><?= $currentPage; ?></strong> dari <strong><?= $totalPages; ?></strong>
                    </span>
                    <ul class="pagination pagination-sm m-0">
                        <?php if ($currentPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= site_url('admin/inventaris/pemeliharaan?' . http_build_query(array_merge($filters, ['page' => $currentPage - 1]))); ?>">« Prev</a>
                            </li>
                        <?php endif; ?>
                        <?php for ($p = max(1, $currentPage - 2); $p <= min($totalPages, $currentPage + 2); $p++): ?>
                            <li class="page-item <?= ($p === $currentPage) ? 'active' : ''; ?>">
                                <a class="page-link" href="<?= site_url('admin/inventaris/pemeliharaan?' . http_build_query(array_merge($filters, ['page' => $p]))); ?>"><?= $p; ?></a>
                            </li>
                        <?php endfor; ?>
                        <?php if ($currentPage < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= site_url('admin/inventaris/pemeliharaan?' . http_build_query(array_merge($filters, ['page' => $currentPage + 1]))); ?>">Next »</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<!-- Modal Konfirmasi Hapus Surat -->
<div class="modal fade" id="modalDeleteSurat" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px;">
            <div class="modal-header bg-danger text-white py-2.5 px-3">
                <h6 class="modal-title font-weight-bold" id="modalDeleteTitle">
                    <i class="fas fa-exclamation-triangle mr-1.5"></i>Konfirmasi Hapus Surat
                </h6>
                <button type="button" class="close text-white opacity-8" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formDeleteSurat" method="post" action="">
                <?= csrf_field(); ?>
                <div class="modal-body p-4 text-center">
                    <div class="text-danger mb-3">
                        <i class="fas fa-trash-alt fa-3x" style="opacity: 0.85;"></i>
                    </div>
                    <h6 class="font-weight-bold text-dark mb-2">Apakah Anda yakin ingin menghapus surat ini?</h6>
                    <p class="text-muted small mb-0" id="deleteSuratNotice">
                        Surat dan seluruh berkas foto lampiran terkait akan dihapus permanen dari sistem.
                    </p>
                </div>
                <div class="modal-footer bg-light px-3 py-2 d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3 font-weight-bold shadow-sm">
                        <i class="fas fa-trash mr-1"></i>Ya, Hapus Dokumen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $('.btn-delete-surat').on('click', function() {
        var id = $(this).data('id');
        var noSurat = $(this).data('nosurat');
        var form = document.getElementById('formDeleteSurat');
        form.action = '<?= site_url("admin/inventaris/pemeliharaan"); ?>/' + id + '/hapus';
        document.getElementById('deleteSuratNotice').innerHTML = 'Surat nomor <strong>' + noSurat + '</strong> dan seluruh berkas foto lampirannya akan dihapus permanen.';
        $('#modalDeleteSurat').modal('show');
    });
});
</script>
<?= $this->endSection(); ?>
