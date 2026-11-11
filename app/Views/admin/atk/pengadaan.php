<?= $this->extend('layouts/admin'); ?>

<?php
$bulanMap = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
];
$statusBadges = [
    'draft'        => ['badge-secondary', 'Draft'],
    'menunggu_ttd' => ['badge-warning text-dark', 'Menunggu TTD / Verifikasi'],
    'disetujui'    => ['badge-success', 'Disetujui (TTD Basah)'],
    'dibayar'      => ['badge-primary', 'Lunas / Dibayar'],
    'ditolak'      => ['badge-danger', 'Ditolak / Revisi'],
];
?>

<?= $this->section('content'); ?>
<div class="row mb-3">
    <div class="col-md-3 col-sm-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?= (int) ($summary['total_transaksi'] ?? 0); ?></h3>
                <p>Total Transaksi Pengadaan</p>
            </div>
            <div class="icon"><i class="fas fa-file-invoice"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3 style="font-size: 1.65rem;">Rp <?= number_format((float) ($summary['total_nilai'] ?? 0), 0, ',', '.'); ?></h3>
                <p>Total Nilai Pengadaan</p>
            </div>
            <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3><?= (int) ($summary['total_disetujui'] ?? 0); ?></h3>
                <p>Disetujui / Lunas</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3><?= (int) ($summary['total_menunggu'] ?? 0); ?></h3>
                <p>Draft / Menunggu / Revisi</p>
            </div>
            <div class="icon"><i class="fas fa-clock"></i></div>
        </div>
    </div>
</div>

<div class="card card-outline card-primary">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
        <h3 class="card-title mb-2 mb-md-0"><i class="fas fa-boxes mr-1"></i> Daftar Pengadaan ATK &amp; Supplies</h3>
        <div class="card-tools d-flex flex-wrap" style="gap: 6px;">
            <?php if (! empty($permissions['export'])): ?>
                <a href="<?= site_url('/admin/atk/pengadaan/export-excel?tahun=' . (int) $filterTahun . '&bulan=' . urlencode((string) $filterBulan) . '&kategori=' . urlencode((string) $filterKategori)); ?>" class="btn btn-sm btn-success">
                    <i class="fas fa-file-excel mr-1"></i> Export 6 Sheet (.xlsx)
                </a>
            <?php endif; ?>
            <?php if (! empty($permissions['add'])): ?>
                <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalTambahPengadaan">
                    <i class="fas fa-plus mr-1"></i> Buat Pengadaan Baru
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="card-body">
        <form method="get" action="<?= site_url('/admin/atk/pengadaan'); ?>" class="form-row mb-3 align-items-end">
            <div class="col-md-2 col-sm-6 mb-2">
                <label class="small font-weight-bold mb-1">Tahun Anggaran</label>
                <select name="tahun" class="form-control form-control-sm">
                    <?php for ($y = date('Y') + 1; $y >= 2024; $y--): ?>
                        <option value="<?= $y; ?>" <?= (int) $filterTahun === $y ? 'selected' : ''; ?>><?= $y; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2 col-sm-6 mb-2">
                <label class="small font-weight-bold mb-1">Bulan</label>
                <select name="bulan" class="form-control form-control-sm">
                    <option value="">Semua Bulan</option>
                    <?php foreach ($bulanMap as $num => $nama): ?>
                        <option value="<?= $num; ?>" <?= (string) $filterBulan === (string) $num ? 'selected' : ''; ?>><?= $nama; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 col-sm-6 mb-2">
                <label class="small font-weight-bold mb-1">Kategori</label>
                <select name="kategori" class="form-control form-control-sm">
                    <option value="">Semua Kategori</option>
                    <option value="ATK" <?= $filterKategori === 'ATK' ? 'selected' : ''; ?>>Alat Tulis Kantor (ATK)</option>
                    <option value="Supplies Komputer" <?= $filterKategori === 'Supplies Komputer' ? 'selected' : ''; ?>>Supplies Komputer</option>
                </select>
            </div>
            <div class="col-md-3 col-sm-6 mb-2">
                <label class="small font-weight-bold mb-1">Status</label>
                <select name="status" class="form-control form-control-sm">
                    <option value="">Semua Status</option>
                    <?php foreach ($statusBadges as $stKey => $stMeta): ?>
                        <option value="<?= esc($stKey); ?>" <?= $filterStatus === $stKey ? 'selected' : ''; ?>><?= esc($stMeta[1]); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 col-sm-12 mb-2 d-flex" style="gap: 4px;">
                <button type="submit" class="btn btn-sm btn-info flex-fill"><i class="fas fa-filter mr-1"></i> Filter</button>
                <a href="<?= site_url('/admin/atk/pengadaan'); ?>" class="btn btn-sm btn-default" title="Reset Filter"><i class="fas fa-sync-alt"></i></a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm js-datatable" data-order='[[0, "asc"]]'>
                <thead class="thead-light">
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th>Periode</th>
                        <th>Kategori</th>
                        <th>No. Pesanan &amp; BASTP</th>
                        <th>Penyedia</th>
                        <th class="text-center">Item</th>
                        <th class="text-right">Total Nilai</th>
                        <th class="text-center">Dok. TTD</th>
                        <th class="text-center">Status</th>
                        <th style="width: 210px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pengadaanList as $i => $row): ?>
                        <?php
                        $stKey  = (string) ($row['status'] ?? 'draft');
                        $stInfo = $statusBadges[$stKey] ?? ['badge-secondary', ucfirst($stKey)];
                        $pid    = (int) $row['id'];
                        ?>
                        <tr>
                            <td class="text-center"><?= $i + 1; ?></td>
                            <td>
                                <strong><?= esc($bulanMap[(int) $row['bulan']] ?? '-'); ?> <?= (int) $row['tahun_anggaran']; ?></strong>
                                <div class="small text-muted">MAK: <?= esc($row['kode_mata_anggaran'] ?: '-'); ?></div>
                            </td>
                            <td>
                                <span class="badge <?= $row['kategori'] === 'Supplies Komputer' ? 'badge-info' : 'badge-dark'; ?>">
                                    <?= esc($row['kategori']); ?>
                                </span>
                            </td>
                            <td class="small">
                                <div><strong>SPB:</strong> <?= esc($row['no_pesanan'] ?: '-'); ?></div>
                                <div><strong>BASTP:</strong> <?= esc($row['no_bastp'] ?: '-'); ?></div>
                            </td>
                            <td class="small"><?= esc($row['nama_perusahaan'] ?: '-'); ?></td>
                            <td class="text-center"><span class="badge badge-light border"><?= (int) $row['item_count']; ?> item</span></td>
                            <td class="text-right font-weight-bold">Rp <?= number_format((float) $row['total_nilai'], 0, ',', '.'); ?></td>
                            <td class="text-center">
                                <?php if ((int) $row['doc_count'] > 0): ?>
                                    <span class="badge badge-success"><i class="fas fa-file-signature mr-1"></i> <?= (int) $row['doc_count']; ?> berkas</span>
                                <?php else: ?>
                                    <span class="badge badge-light text-muted">Belum ada</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= $stInfo[0]; ?>"><?= esc($stInfo[1]); ?></span>
                                <?php if ($stKey === 'ditolak' && ! empty($row['rejection_reason'])): ?>
                                    <div class="small text-danger mt-1" title="<?= esc($row['rejection_reason']); ?>">
                                        <i class="fas fa-info-circle"></i> <?= esc(mb_strimwidth((string) $row['rejection_reason'], 0, 35, '...')); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= site_url('/admin/atk/pengadaan/' . $pid); ?>" class="btn btn-info" title="Detail, Cetak 6 Dokumen &amp; Upload TTD">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                    <?php if (! empty($permissions['export'])): ?>
                                        <a href="<?= site_url('/admin/atk/pengadaan/' . $pid . '/cetak-pdf/all'); ?>" target="_blank" class="btn btn-danger" title="Cetak Bundel 6 Dokumen PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        <a href="<?= site_url('/admin/atk/pengadaan/' . $pid . '/export-excel'); ?>" class="btn btn-success" title="Export 6 Sheet Excel">
                                            <i class="fas fa-file-excel"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (! empty($permissions['delete'])): ?>
                                        <form method="post" action="<?= site_url('/admin/atk/pengadaan/' . $pid . '/hapus'); ?>" class="d-inline" data-confirm-title="Hapus Pengadaan?" data-confirm-text="Data transaksi dan dokumen terkait akan dihapus permanen.">
                                            <?= csrf_field(); ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (! empty($permissions['add'])): ?>
<!-- Modal Tambah Pengadaan -->
<div class="modal fade" id="modalTambahPengadaan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <form method="post" action="<?= site_url('/admin/atk/pengadaan/tambah'); ?>" data-skip-confirm="1">
                <?= csrf_field(); ?>
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus-circle mr-1"></i> Buat Pengadaan ATK &amp; Supplies Baru</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-2 mb-2">
                            <label class="small font-weight-bold">Tahun</label>
                            <input type="number" name="tahun_anggaran" class="form-control form-control-sm" value="<?= (int) date('Y'); ?>" required>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="small font-weight-bold">Bulan</label>
                            <select name="bulan" class="form-control form-control-sm" required>
                                <?php foreach ($bulanMap as $num => $nama): ?>
                                    <option value="<?= $num; ?>" <?= (int) date('n') === $num ? 'selected' : ''; ?>><?= $nama; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">Kategori Pengadaan</label>
                            <select name="kategori" id="addKategoriSelect" class="form-control form-control-sm" required>
                                <option value="ATK">ATK (Alat Tulis Kantor)</option>
                                <option value="Supplies Komputer">Supplies Komputer</option>
                            </select>
                        </div>
                        <div class="col-md-5 mb-2">
                            <label class="small font-weight-bold">Penyedia (Vendor)</label>
                            <select name="penyedia_id" class="form-control form-control-sm">
                                <?php foreach ($penyediaList as $p): ?>
                                    <option value="<?= (int) $p['id']; ?>"><?= esc($p['nama_perusahaan']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Mata Anggaran</label>
                            <select name="mata_anggaran_id" class="form-control form-control-sm">
                                <option value="">-- Gunakan Default (7987.FAD.002.101.A.521211) --</option>
                                <?php foreach ($mataAnggaranList as $ma): ?>
                                    <option value="<?= (int) $ma['id']; ?>"><?= esc($ma['mata_anggaran']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Kegiatan / Keterangan Utama</label>
                            <input type="text" name="kegiatan_label" class="form-control form-control-sm" value="Dukungan Kegiatan Prasarana Strategis">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Kop Surat</label>
                            <select name="kop_surat_id" class="form-control form-control-sm">
                                <option value="">-- Kop Surat Aktif Default --</option>
                                <?php foreach ($kopSuratList as $k): ?>
                                    <option value="<?= (int) $k['id']; ?>" <?= ! empty($k['is_active']) ? 'selected' : ''; ?>><?= esc($k['title']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">Tgl. Usulan PPK</label>
                            <input type="date" name="tgl_usulan" class="form-control form-control-sm" value="<?= date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">No. Surat Pesanan (SPB)</label>
                            <input type="text" name="no_pesanan" class="form-control form-control-sm" placeholder=".../PES/SATKER/PPS-RIAU/<?= date('Y'); ?>">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">Tgl. Surat Pesanan</label>
                            <input type="date" name="tgl_pesanan" class="form-control form-control-sm" value="<?= date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">Tgl. Batas Terima</label>
                            <input type="date" name="tgl_batas_terima" class="form-control form-control-sm" value="<?= date('Y-m-d', strtotime('+1 day')); ?>">
                        </div>

                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">No. BASTP</label>
                            <input type="text" name="no_bastp" class="form-control form-control-sm" placeholder=".../BASTP/SATKER/PPS-RIAU/<?= date('Y'); ?>">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">Tgl. BASTP / Faktur / Kuitansi</label>
                            <input type="date" name="tgl_bastp" class="form-control form-control-sm" value="<?= date('Y-m-d', strtotime('+1 day')); ?>">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">PPK</label>
                            <select name="ppk_pegawai_id" class="form-control form-control-sm select2">
                                <option value="">-- Default: NURHIDAYAT NUGROHO, S.Ars. --</option>
                                <?php foreach ($pegawaiList as $pg): ?>
                                    <option value="<?= (int) $pg['id']; ?>" <?= stripos((string) $pg['nama'], 'Nurhidayat') !== false ? 'selected' : ''; ?>>
                                        <?= esc($pg['nama']); ?> (<?= esc($pg['nip'] ?? '-'); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">Bendahara Pengeluaran</label>
                            <select name="bendahara_pegawai_id" class="form-control form-control-sm select2">
                                <option value="">-- Default: KH. SRI HANDAYANI, S.Si., M.T. --</option>
                                <?php foreach ($pegawaiList as $pg): ?>
                                    <option value="<?= (int) $pg['id']; ?>" <?= stripos((string) $pg['nama'], 'Sri Handayani') !== false ? 'selected' : ''; ?>>
                                        <?= esc($pg['nama']); ?> (<?= esc($pg['nip'] ?? '-'); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="font-weight-bold mb-0"><i class="fas fa-list mr-1"></i> Rincian Barang</h6>
                        <button type="button" class="btn btn-xs btn-outline-primary" id="btnAddRowBarang">
                            <i class="fas fa-plus mr-1"></i> Tambah Baris Barang
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" id="tblAddItems">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 28%;">Pilih dari Katalog (Opsional)</th>
                                    <th style="width: 26%;">Nama Barang</th>
                                    <th style="width: 11%;">Satuan</th>
                                    <th style="width: 9%;">Volume</th>
                                    <th style="width: 13%;">Harga Satuan</th>
                                    <th style="width: 10%;">Subtotal</th>
                                    <th style="width: 36px;"></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-right">TOTAL NILAI (Rp)</th>
                                    <th class="text-right" id="addGrandTotal">0</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save mr-1"></i> Simpan Pengadaan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script>
(() => {
    const catalog = <?= json_encode($barangList ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const tbody = document.querySelector('#tblAddItems tbody');
    const grandTotalEl = document.getElementById('addGrandTotal');
    const btnAddRow = document.getElementById('btnAddRowBarang');
    if (!tbody || !btnAddRow) return;

    const formatRp = (num) => Math.round(num || 0).toLocaleString('id-ID');
    const catalogById = {};
    catalog.forEach((b) => {
        catalogById[String(b.id)] = b;
    });

    const recalcTotal = () => {
        let total = 0;
        tbody.querySelectorAll('tr').forEach((tr) => {
            const vol = parseFloat(tr.querySelector('.inp-vol')?.value || '0') || 0;
            const harga = parseFloat(tr.querySelector('.inp-harga')?.value || '0') || 0;
            const sub = vol * harga;
            total += sub;
            const subEl = tr.querySelector('.txt-sub');
            if (subEl) subEl.textContent = formatRp(sub);
        });
        if (grandTotalEl) grandTotalEl.textContent = formatRp(total);
    };

    const applyCatalogSelection = (selEl) => {
        const tr = selEl.closest('tr');
        if (!tr) return;
        const item = catalogById[String(selEl.value || '')];
        if (item) {
            tr.querySelector('.inp-nama').value = item.nama_barang || '';
            tr.querySelector('.inp-satuan').value = item.satuan || 'Buah';
            tr.querySelector('.inp-harga').value = Math.round(parseFloat(item.harga_satuan || '0'));
        }
        recalcTotal();
    };

    const buildCatalogOptions = () => {
        let html = '<option value="">-- Pilih / Input Manual --</option>';
        catalog.forEach((b) => {
            html += `<option value="${b.id}">[${b.kategori}] ${b.nama_barang} - Rp ${formatRp(b.harga_satuan)}</option>`;
        });
        return html;
    };

    const addRow = () => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <select name="item_barang_id[]" class="form-control form-control-sm sel-katalog">
                    ${buildCatalogOptions()}
                </select>
            </td>
            <td><input type="text" name="item_nama_barang[]" class="form-control form-control-sm inp-nama" required placeholder="Nama barang..."></td>
            <td><input type="text" name="item_satuan[]" class="form-control form-control-sm inp-satuan" value="Buah" required></td>
            <td><input type="number" step="1" min="1" name="item_volume[]" class="form-control form-control-sm inp-vol text-center" value="1" required></td>
            <td><input type="number" step="1" min="0" name="item_harga_satuan[]" class="form-control form-control-sm inp-harga text-right" value="0" required></td>
            <td class="text-right align-middle font-weight-bold txt-sub">0</td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-xs btn-outline-danger btn-del-row"><i class="fas fa-times"></i></button>
            </td>
        `;
        tbody.appendChild(tr);

        const sel = tr.querySelector('.sel-katalog');
        sel.addEventListener('change', () => applyCatalogSelection(sel));
        if (typeof window.jQuery !== 'undefined') {
            window.jQuery(sel).on('change select2:select', function () {
                applyCatalogSelection(this);
            }).on('select2:clear', function () {
                tr.querySelector('.inp-nama').value = '';
                tr.querySelector('.inp-satuan').value = 'Buah';
                tr.querySelector('.inp-harga').value = 0;
                recalcTotal();
            });
        }
        tr.querySelector('.inp-vol').addEventListener('input', recalcTotal);
        tr.querySelector('.inp-harga').addEventListener('input', recalcTotal);
        tr.querySelector('.btn-del-row').addEventListener('click', () => {
            tr.remove();
            recalcTotal();
        });
    };

    btnAddRow.addEventListener('click', addRow);
    addRow();
})();
</script>
<?= $this->endSection(); ?>

