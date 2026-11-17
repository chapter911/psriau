<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php 
    $isAllLokasi    = ($filterLokasi === '' || $filterLokasi === '*' || $filterLokasi === 'all');
    $totalItemAll   = array_sum(array_column($lokasiStats, 'total_item'));
    $totalFotoAll   = array_sum(array_column($lokasiStats, 'item_berfoto'));
    $totalPersenAll = $totalItemAll > 0 ? round(($totalFotoAll / $totalItemAll) * 100) : 0;

    if ($isAllLokasi) {
        $curLokasiLabel = 'Seluruh Lokasi (Komplek Sport Center Kuansing)';
        $curTotal       = $totalItemAll;
        $curFoto        = $totalFotoAll;
        $curPersen      = $totalPersenAll;
    } else {
        $curLokasiLabel = $filterLokasi;
        $currentLokStat = null;
        foreach ($lokasiStats as $lok) {
            if ($lok['lokasi'] === $filterLokasi) {
                $currentLokStat = $lok;
                break;
            }
        }
        $curTotal  = $currentLokStat['total_item'] ?? count($items);
        $curFoto   = $currentLokStat['item_berfoto'] ?? 0;
        $curPersen = $curTotal > 0 ? round(($curFoto / $curTotal) * 100) : 0;
    }
?>
<div class="container-fluid">

    <!-- Top Selection: Filter Lokasi Gedung & Material -->
    <div class="card shadow-sm mb-3" style="border-radius: 12px; border: 1px solid #e9eef5;">
        <div class="card-body py-3">
            <form method="get" action="<?= site_url('admin/inventaris/sekolah-rakyat/foto'); ?>" id="formFilterSurvei">
                <input type="hidden" name="view" value="<?= esc($viewMode ?? 'grid'); ?>">
                <div class="row align-items-end">
                    <!-- Dropdown Lokasi Gedung -->
                    <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                        <label class="font-weight-bold text-muted small mb-1">
                            <i class="fas fa-building text-primary mr-1"></i>1. Lokasi Gedung / Zona
                        </label>
                        <select name="lokasi" id="select_lokasi_survei" class="form-control form-control-sm select2" onchange="onGedungChanged(this.value)">
                            <option value="" <?= $isAllLokasi ? 'selected' : ''; ?>>
                                -- Seluruh Lokasi (<?= $totalFotoAll; ?>/<?= $totalItemAll; ?> item - <?= $totalPersenAll; ?>%) --
                            </option>
                            <?php foreach ($lokasiStats as $lok): ?>
                                <?php 
                                    $persen = $lok['total_item'] > 0 ? round(($lok['item_berfoto'] / $lok['total_item']) * 100) : 0;
                                ?>
                                <option value="<?= esc($lok['lokasi']); ?>" <?= (! $isAllLokasi && $filterLokasi === $lok['lokasi']) ? 'selected' : ''; ?>>
                                    <?= esc($lok['lokasi']); ?> (<?= $lok['item_berfoto']; ?>/<?= $lok['total_item']; ?> item - <?= $persen; ?>%)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Dropdown Material / Jenis BMN -->
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label class="font-weight-bold text-muted small mb-1">
                            <i class="fas fa-layer-group text-info mr-1"></i>2. Material / Jenis Barang
                        </label>
                        <select name="material" id="select_material" class="form-control form-control-sm select2" onchange="$('#formFilterSurvei').submit()">
                            <option value=""><?= $isAllLokasi ? '-- Semua Material (Seluruh Lokasi) --' : '-- Semua Material di Gedung Ini --'; ?></option>
                            <?php foreach ($materialList as $m): ?>
                                <option value="<?= esc($m['jenis_bmn']); ?>" <?= ($filterMaterial === $m['jenis_bmn']) ? 'selected' : ''; ?>>
                                    <?= esc($m['jenis_bmn']); ?> (<?= $m['total_item']; ?> Item)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Dropdown Status Foto -->
                    <div class="col-md-2 col-sm-6 mb-2 mb-md-0">
                        <label class="font-weight-bold text-muted small mb-1">
                            <i class="fas fa-camera text-success mr-1"></i>3. Status Foto
                        </label>
                        <select name="status_foto" class="form-control form-control-sm" onchange="$('#formFilterSurvei').submit()">
                            <option value="">Semua Status</option>
                            <option value="belum" <?= ($filterStatus === 'belum') ? 'selected' : ''; ?>>Belum Difoto</option>
                            <option value="sudah" <?= ($filterStatus === 'sudah') ? 'selected' : ''; ?>>Sudah Difoto</option>
                        </select>
                    </div>

                    <div class="col-md-3 col-sm-6 d-flex align-items-center" style="gap: 6px;">
                        <button type="submit" class="btn btn-sm btn-primary flex-fill shadow-sm">
                            <i class="fas fa-filter mr-1"></i> Terapkan
                        </button>
                        <a href="<?= site_url('admin/inventaris/sekolah-rakyat/foto' . (! $isAllLokasi ? '?lokasi=' . urlencode($filterLokasi) : '')); ?>" class="btn btn-sm btn-outline-secondary shadow-sm" title="Reset Filter Material">
                            <i class="fas fa-undo"></i>
                        </a>
                        <a href="<?= site_url('admin/inventaris/sekolah-rakyat/barang' . (! $isAllLokasi ? '?lokasi=' . urlencode($filterLokasi) : '')); ?>" class="btn btn-sm btn-outline-info shadow-sm" title="Buka Tabel Rincian Barang">
                            <i class="fas fa-table mr-1"></i> Tabel Rincian
                        </a>
                    </div>
                </div>
            </form>

            <!-- Progres Bar Lokasi Gedung / Seluruh Lokasi -->
            <div class="mt-3 pt-2 border-top">
                <div class="d-flex flex-wrap justify-content-between align-items-center small font-weight-bold mb-1">
                    <span class="text-muted">
                        <i class="fas fa-chart-line text-success mr-1"></i>Progres Kelengkapan Foto di <strong><?= esc($curLokasiLabel); ?></strong>:
                    </span>
                    <div class="d-flex align-items-center mt-1 mt-md-0" style="gap: 8px;">
                        <span class="text-primary font-weight-bold mr-2">
                            <?= $curFoto; ?> / <?= $curTotal; ?> Item (<?= $curPersen; ?>%)
                        </span>
                        <?php if (! $isAllLokasi): ?>
                            <a href="<?= site_url('admin/inventaris/sekolah-rakyat/barang/export-excel?lokasi=' . urlencode($filterLokasi)); ?>" class="btn btn-xs btn-outline-success font-weight-bold shadow-sm" title="Unduh Kertas Kerja Khusus Gedung Ini">
                                <i class="fas fa-file-excel mr-1"></i>Excel Gedung Ini
                            </a>
                        <?php endif; ?>
                        <a href="<?= site_url('admin/inventaris/sekolah-rakyat/barang/export-excel'); ?>" class="btn btn-xs btn-success font-weight-bold shadow-sm" title="Unduh Kertas Kerja Seluruh Aset Lengkap">
                            <i class="fas fa-file-excel mr-1"></i>Excel Lengkap
                        </a>
                    </div>
                </div>
                <div class="progress" style="height: 8px; border-radius: 4px;">
                    <div class="progress-bar <?= $curPersen === 100 ? 'bg-success' : 'bg-primary'; ?> progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?= $curPersen; ?>%;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Filter Material Pills (Horizontal Scrollable Strip) -->
    <?php if (! empty($materialList)): ?>
        <div class="d-flex flex-nowrap overflow-auto mb-3 pb-1" style="gap: 6px; -webkit-overflow-scrolling: touch;">
            <a href="<?= site_url('admin/inventaris/sekolah-rakyat/foto' . (! $isAllLokasi ? '?lokasi=' . urlencode($filterLokasi) : '') . (empty($filterStatus) ? '' : ($isAllLokasi ? '?status_foto=' . urlencode($filterStatus) : '&status_foto=' . urlencode($filterStatus)))); ?>" class="btn btn-xs text-nowrap rounded-pill <?= empty($filterMaterial) ? 'btn-primary font-weight-bold shadow-sm px-3' : 'btn-outline-secondary bg-white px-2.5'; ?>">
                <i class="fas fa-th-large mr-1"></i>Semua Material (<?= $curTotal; ?>)
            </a>
            <?php foreach ($materialList as $m): ?>
                <?php 
                    $isMatActive = ($filterMaterial === $m['jenis_bmn']);
                    $pillUrl = site_url('admin/inventaris/sekolah-rakyat/foto?' . http_build_query(array_filter([
                        'lokasi'      => $isAllLokasi ? null : $filterLokasi,
                        'material'    => $m['jenis_bmn'],
                        'status_foto' => $filterStatus,
                        'view'        => $viewMode !== 'grid' ? $viewMode : null,
                    ])));
                ?>
                <a href="<?= $pillUrl; ?>" class="btn btn-xs text-nowrap rounded-pill <?= $isMatActive ? 'btn-primary font-weight-bold shadow-sm px-3' : 'btn-outline-secondary bg-white px-2.5'; ?>">
                    <?= esc($m['jenis_bmn']); ?> <span class="badge <?= $isMatActive ? 'badge-light text-primary' : 'badge-light border'; ?> ml-1"><?= $m['total_item']; ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Info GPS Lapangan & View Mode Switcher -->
    <div class="row align-items-center mb-3">
        <div class="col-md-8 col-12 mb-2 mb-md-0">
            <div class="alert alert-info py-2 px-3 mb-0 d-flex align-items-center justify-content-between shadow-sm" style="border-radius: 8px; font-size: 0.85rem;">
                <div>
                    <i class="fas fa-satellite-dish mr-2"></i>
                    <span id="gpsStatusText">Mendeteksi koordinat GPS lapangan...</span>
                </div>
                <button type="button" class="btn btn-xs btn-light font-weight-bold shadow-sm" onclick="detectGPS()"><i class="fas fa-sync-alt mr-1"></i>Refresh GPS</button>
            </div>
        </div>
        <div class="col-md-4 col-12 d-flex justify-content-md-end">
            <div class="btn-group btn-group-sm shadow-sm" role="group">
                <button type="button" class="btn <?= ($viewMode === 'table') ? 'btn-primary font-weight-bold' : 'btn-outline-secondary bg-white'; ?>" onclick="switchView('table')">
                    <i class="fas fa-table mr-1"></i>Tampilan Tabel
                </button>
                <button type="button" class="btn <?= ($viewMode !== 'table') ? 'btn-primary font-weight-bold' : 'btn-outline-secondary bg-white'; ?>" onclick="switchView('grid')">
                    <i class="fas fa-th-large mr-1"></i>Tampilan Kartu
                </button>
            </div>
        </div>
    </div>

    <!-- Daftar Barang Berdasarkan Material -->
    <?php if (empty($items)): ?>
        <div class="card p-5 text-center text-muted shadow-sm" style="border-radius: 12px;">
            <i class="fas fa-boxes fa-3x mb-3 text-secondary"></i>
            <h5>Tidak ada barang yang sesuai dengan filter lokasi & material ini.</h5>
            <p class="small mb-2">Silakan pilih material lain atau klik tombol reset filter.</p>
            <div>
                <a href="<?= site_url('admin/inventaris/sekolah-rakyat/foto' . (! $isAllLokasi ? '?lokasi=' . urlencode($filterLokasi) : '')); ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-undo mr-1"></i> Tampilkan Semua Material di <?= esc($curLokasiLabel); ?>
                </a>
            </div>
        </div>
    <?php elseif ($viewMode === 'table'): ?>
        <!-- Mode Tabel Ringkas untuk Seluruh / Sebagian Lokasi -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="font-weight-bold text-dark mb-0">
                    <i class="fas fa-table text-primary mr-1"></i>Tabel Dokumentasi Foto BMN (<?= count($items); ?> Item)
                </h6>
                <span class="badge badge-light border text-muted px-2 py-1">
                    <i class="fas fa-building mr-1"></i><?= esc($curLokasiLabel); ?>
                </span>
            </div>
            <div class="card-body p-3">
                <div class="table-responsive">
                    <table id="tbl_foto_sr" class="table table-bordered table-striped table-hover align-middle w-100" style="font-size: 0.88rem;">
                        <thead class="thead-dark">
                            <tr>
                                <th style="width: 40px;" class="text-center align-middle">No</th>
                                <th style="width: 150px;" class="align-middle">Lokasi Gedung / Zona</th>
                                <th style="width: 130px;" class="align-middle">Kode BMN</th>
                                <th class="align-middle">Material / Jenis BMN & Spesifikasi</th>
                                <th style="width: 85px;" class="text-center align-middle">Volume</th>
                                <th style="width: 80px;" class="text-center align-middle">Kondisi</th>
                                <th style="width: 180px;" class="text-center align-middle">Dokumentasi Foto Fisik</th>
                                <th style="width: 100px;" class="text-center align-middle">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $idx => $it): ?>
                                <?php 
                                    $itemFotos = $fotosByItem[$it['id']] ?? [];
                                    $hasFoto = ! empty($itemFotos);
                                ?>
                                <tr>
                                    <td class="text-center align-middle font-weight-bold text-muted"><?= $it['no_urut']; ?></td>
                                    <td class="align-middle">
                                        <span class="badge badge-info px-2 py-1 text-wrap font-weight-normal" style="max-width: 150px; font-size: 0.82rem;">
                                            <i class="fas fa-map-marker-alt mr-1"></i><?= esc($it['lokasi']); ?>
                                        </span>
                                    </td>
                                    <td class="align-middle font-weight-bold text-nowrap">
                                        <span class="badge badge-light border text-dark font-weight-bold p-1"><?= esc($it['kode_bmn']); ?></span>
                                        <div class="small text-muted mt-1" style="font-size: 0.72rem;">
                                            <?php if (str_starts_with($it['kelompok_bmn'], 'I.')): ?>
                                                <span class="badge badge-info px-1">Peralatan</span>
                                            <?php elseif (str_starts_with($it['kelompok_bmn'], 'II.')): ?>
                                                <span class="badge badge-primary px-1">Gedung</span>
                                            <?php else: ?>
                                                <span class="badge badge-success px-1">Jaringan</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold text-primary mb-1"><?= esc($it['jenis_bmn']); ?></div>
                                        <small class="text-muted d-block" style="line-height: 1.35;"><?= esc($it['spesifikasi']); ?></small>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="font-weight-bold"><?= rtrim(rtrim(number_format((float) $it['jumlah'], 2, ',', '.'), '0'), ','); ?></span>
                                        <span class="badge badge-light border d-block mt-1"><?= esc($it['satuan']); ?></span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-light border text-success px-2 py-1"><?= esc($it['kondisi']); ?></span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <?php if ($hasFoto): ?>
                                            <div class="d-flex flex-wrap justify-content-center" style="gap: 4px;">
                                                <?php foreach ($itemFotos as $f): ?>
                                                    <div class="position-relative" style="width: 46px; height: 46px;">
                                                        <img src="<?= base_url($f['foto_watermark'] ?: $f['foto_asli']); ?>" alt="<?= esc($f['judul_foto']); ?>" class="img-thumbnail w-100 h-100" style="object-fit: cover; cursor: pointer; padding: 1px; border-radius: 4px;" onclick="previewImageModal('<?= base_url($f['foto_watermark'] ?: $f['foto_asli']); ?>', '<?= esc($it['jenis_bmn'] . ' - ' . $f['judul_foto']); ?>')" title="<?= esc($f['judul_foto']); ?>">
                                                        <?php if ($canDelete): ?>
                                                            <button type="button" class="btn btn-danger btn-xs position-absolute" style="top: -4px; right: -4px; border-radius: 50%; width: 16px; height: 16px; padding: 0; line-height: 14px; font-size: 10px;" onclick="hapusFoto(<?= $f['id']; ?>)" title="Hapus Foto">&times;</button>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <small class="text-success font-weight-bold d-block mt-1"><?= count($itemFotos); ?> Foto</small>
                                        <?php else: ?>
                                            <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-camera mr-1"></i>Belum</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-primary btn-xs shadow-sm" onclick="openCameraCapture(<?= $it['id']; ?>, '<?= esc($it['jenis_bmn']); ?>')" title="Ambil Foto Kamera">
                                                <i class="fas fa-camera"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-xs" onclick="openFileUpload(<?= $it['id']; ?>, '<?= esc($it['jenis_bmn']); ?>')" title="Upload File Galeri">
                                                <i class="fas fa-folder-open"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php 
            // Kelompokkan item berdasarkan Material (jenis_bmn)
            $groupedItems = [];
            foreach ($items as $it) {
                $groupedItems[$it['jenis_bmn']][] = $it;
            }
        ?>

        <?php foreach ($groupedItems as $materialName => $matItems): ?>
            <!-- Header Kategori Material -->
            <div class="row align-items-center mt-3 mb-2">
                <div class="col-12">
                    <div class="d-flex align-items-center justify-content-between p-2 rounded bg-white shadow-sm border-left" style="border-left: 4px solid #007bff !important;">
                        <h6 class="font-weight-bold text-dark mb-0" style="font-size: 0.95rem;">
                            <i class="fas fa-boxes text-primary mr-2"></i>Material: <span class="text-primary"><?= esc($materialName); ?></span>
                            <span class="badge badge-secondary ml-2 font-weight-normal"><?= count($matItems); ?> Item</span>
                        </h6>
                        <small class="text-muted"><i class="fas fa-building mr-1"></i><?= esc($curLokasiLabel); ?></small>
                    </div>
                </div>
            </div>

            <!-- Kartu Item Barang untuk Material Ini -->
            <div class="row">
                <?php foreach ($matItems as $it): ?>
                    <?php 
                        $itemFotos = $fotosByItem[$it['id']] ?? [];
                        $hasFoto = ! empty($itemFotos);
                    ?>
                    <div class="col-12 col-md-6 col-xl-4 mb-3">
                        <div class="card h-100 shadow-sm border-0" style="border-radius: 12px; border-top: 4px solid <?= $hasFoto ? '#28a745' : '#ffc107'; ?> !important;">
                            <div class="card-header bg-white py-2 px-3 border-0 d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="badge badge-light border text-dark font-weight-bold mr-1"><?= esc($it['kode_bmn']); ?></span>
                                    <?php if (str_starts_with($it['kelompok_bmn'], 'I.')): ?>
                                        <span class="badge badge-info font-weight-normal">Peralatan</span>
                                    <?php elseif (str_starts_with($it['kelompok_bmn'], 'II.')): ?>
                                        <span class="badge badge-primary font-weight-normal">Gedung</span>
                                    <?php else: ?>
                                        <span class="badge badge-success font-weight-normal">Jaringan</span>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <?php if ($hasFoto): ?>
                                        <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i><?= count($itemFotos); ?> Foto</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-exclamation-circle mr-1"></i>Belum Difoto</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="card-body py-2 px-3">
                                <h6 class="font-weight-bold text-primary mb-1" style="font-size: 0.98rem;"><?= esc($it['jenis_bmn']); ?></h6>
                                <div class="mb-2">
                                    <span class="badge badge-light border text-primary" style="font-size: 0.78rem;">
                                        <i class="fas fa-map-marker-alt mr-1"></i><?= esc($it['lokasi']); ?>
                                    </span>
                                </div>
                                <p class="small text-muted mb-2" style="min-height: 38px; line-height: 1.35;"><?= esc($it['spesifikasi']); ?></p>

                                <div class="d-flex justify-content-between small text-muted border-top border-bottom py-1 mb-2">
                                    <span>Volume: <strong><?= rtrim(rtrim(number_format((float) $it['jumlah'], 2, ',', '.'), '0'), ','); ?> <?= esc($it['satuan']); ?></strong></span>
                                    <span>Kondisi: <span class="badge badge-light border text-success"><?= esc($it['kondisi']); ?></span></span>
                                </div>

                                <!-- Galeri Mini Foto Tersimpan -->
                                <div class="d-flex flex-wrap" style="gap: 6px; min-height: 70px;">
                                    <?php if ($hasFoto): ?>
                                        <?php foreach ($itemFotos as $f): ?>
                                            <div class="position-relative" style="width: 65px; height: 65px;">
                                                <img src="<?= base_url($f['foto_watermark'] ?: $f['foto_asli']); ?>" alt="<?= esc($f['judul_foto']); ?>" class="img-thumbnail w-100 h-100" style="object-fit: cover; cursor: pointer; padding: 2px; border-radius: 6px;" onclick="previewImageModal('<?= base_url($f['foto_watermark'] ?: $f['foto_asli']); ?>', '<?= esc($it['jenis_bmn'] . ' - ' . $f['judul_foto']); ?>')">
                                                <?php if ($canDelete): ?>
                                                    <button type="button" class="btn btn-danger btn-xs position-absolute" style="top: -5px; right: -5px; border-radius: 50%; width: 20px; height: 20px; padding: 0; line-height: 18px;" onclick="hapusFoto(<?= $f['id']; ?>)" title="Hapus Foto">
                                                        &times;
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="w-100 bg-light rounded text-center py-3 text-muted small">
                                            <i class="fas fa-camera fa-lg mb-1 d-block text-secondary"></i>
                                            Belum ada foto dokumentasi fisik
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="card-footer bg-white border-0 py-2 px-3">
                                <div class="d-flex" style="gap: 6px;">
                                    <!-- Tombol Kamera Langsung (Capture Kamera HP) -->
                                    <button type="button" class="btn btn-sm btn-primary flex-fill shadow-sm" onclick="openCameraCapture(<?= $it['id']; ?>, '<?= esc($it['jenis_bmn']); ?>')">
                                        <i class="fas fa-camera mr-1"></i> Ambil Foto Kamera
                                    </button>
                                    <!-- Tombol Upload File Galeri -->
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="openFileUpload(<?= $it['id']; ?>, '<?= esc($it['jenis_bmn']); ?>')" title="Pilih Berkas dari Galeri">
                                        <i class="fas fa-folder-open"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal Upload & Kamera Foto -->
<div class="modal fade" id="modalUploadFoto" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title font-weight-bold" id="uploadModalTitle"><i class="fas fa-camera mr-1"></i>Ambil Foto Barang</h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formUploadFoto" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="upload_bmn_id" name="bmn_id">
                    <input type="hidden" id="upload_latitude" name="latitude">
                    <input type="hidden" id="upload_longitude" name="longitude">
                    <input type="hidden" id="upload_image_data" name="image_data">

                    <!-- Input File Kamera (Mobile Auto-Trigger Kamera Belakang) -->
                    <input type="file" id="cameraInput" accept="image/*" capture="environment" style="display: none;" onchange="handleFileSelected(this)">
                    <!-- Input File Normal (Browse Galeri) -->
                    <input type="file" id="fileInput" accept="image/*" style="display: none;" onchange="handleFileSelected(this)">

                    <div class="form-group mb-2">
                        <label class="small font-weight-bold text-muted mb-1">Sudut / Label Foto</label>
                        <select name="judul_foto" id="upload_judul" class="form-control form-control-sm">
                            <option value="Tampak Depan" selected>Tampak Depan</option>
                            <option value="Tampak Samping">Tampak Samping</option>
                            <option value="Label Merk & Spesifikasi">Label Merk & Spesifikasi</option>
                            <option value="Posisi Penempatan Ruangan">Posisi Penempatan Ruangan</option>
                            <option value="Tampak Keseluruhan">Tampak Keseluruhan</option>
                        </select>
                    </div>

                    <!-- Pratinjau Foto Sebelum Diunggah -->
                    <div id="previewContainer" class="text-center p-2 bg-light border rounded mb-2" style="min-height: 200px; display: flex; align-items: center; justify-content: center; flex-direction: column;">
                        <img id="imagePreview" src="" alt="Pratinjau" class="img-fluid rounded shadow-sm" style="max-height: 250px; display: none;">
                        <div id="placeholderText" class="text-muted small">
                            <i class="fas fa-image fa-3x mb-2 text-secondary d-block"></i>
                            Silakan ambil foto melalui kamera atau pilih dari galeri.
                        </div>
                    </div>

                    <div class="d-flex justify-content-center mb-2" style="gap: 8px;">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="$('#cameraInput').click()">
                            <i class="fas fa-camera mr-1"></i> Buka Kamera
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="$('#fileInput').click()">
                            <i class="fas fa-images mr-1"></i> Galeri
                        </button>
                    </div>

                    <div class="form-group mb-0">
                        <label class="small font-weight-bold text-muted mb-1">Catatan Tambahan (Opsional)</label>
                        <input type="text" name="keterangan" id="upload_keterangan" class="form-control form-control-sm" placeholder="Catatan kondisi fisik di lapangan...">
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" id="btnSubmitUpload" class="btn btn-sm btn-success" disabled onclick="submitUploadFoto()">
                        <i class="fas fa-cloud-upload-alt mr-1"></i> Simpan & Beri Watermark
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Pratinjau Foto Penuh -->
<div class="modal fade" id="modalPreviewImage" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header py-2 bg-dark text-white">
                <h6 class="modal-title font-weight-bold" id="previewImageTitle">Foto Watermark</h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center p-2 bg-light">
                <img id="previewImageSrc" src="" alt="Pratinjau" class="img-fluid rounded shadow-sm" style="max-height: 80vh;">
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
let currentCoords = { lat: '-0.5342', lng: '101.5492' };

function detectGPS() {
    $('#gpsStatusText').html('<i class="fas fa-spinner fa-spin mr-1"></i> Mendeteksi GPS...');
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(function(pos) {
            currentCoords.lat = pos.coords.latitude.toFixed(5);
            currentCoords.lng = pos.coords.longitude.toFixed(5);
            $('#upload_latitude').val(currentCoords.lat);
            $('#upload_longitude').val(currentCoords.lng);
            $('#gpsStatusText').html('<span class="text-success"><i class="fas fa-check-circle mr-1"></i> GPS Terkunci: ' + currentCoords.lat + '°, ' + currentCoords.lng + '° (Akurasi: ' + Math.round(pos.coords.accuracy) + 'm)</span>');
        }, function(err) {
            $('#gpsStatusText').html('<span class="text-warning"><i class="fas fa-info-circle mr-1"></i> GPS Perangkat tidak aktif. Menggunakan default lokasi Komplek Sport Center Kuansing (-0.5342°, 101.5492°).</span>');
        }, { enableHighAccuracy: true, timeout: 10000 });
    } else {
        $('#gpsStatusText').text('Browser tidak mendukung geolokasi GPS.');
    }
}

function onGedungChanged(lokasi) {
    const url = new URL('<?= site_url("admin/inventaris/sekolah-rakyat/foto"); ?>');
    if (lokasi) {
        url.searchParams.set('lokasi', lokasi);
    }
    const currentView = '<?= esc($viewMode ?? "grid"); ?>';
    if (currentView && currentView !== 'grid') {
        url.searchParams.set('view', currentView);
    }
    window.location.href = url.toString();
}

function switchView(mode) {
    const url = new URL(window.location.href);
    url.searchParams.set('view', mode);
    window.location.href = url.toString();
}

$(document).ready(function() {
    if ($('#tbl_foto_sr').length) {
        $('#tbl_foto_sr').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json' },
            pageLength: 25,
            ordering: false,
            responsive: true
        });
    }
});

function openCameraCapture(bmnId, namaBarang) {
    $('#upload_bmn_id').val(bmnId);
    $('#uploadModalTitle').html('<i class="fas fa-camera mr-1"></i> Foto: ' + namaBarang);
    resetUploadModal();
    $('#modalUploadFoto').modal('show');
    // Trigger camera on mobile
    setTimeout(() => { $('#cameraInput').click(); }, 300);
}

function openFileUpload(bmnId, namaBarang) {
    $('#upload_bmn_id').val(bmnId);
    $('#uploadModalTitle').html('<i class="fas fa-folder-open mr-1"></i> Foto: ' + namaBarang);
    resetUploadModal();
    $('#modalUploadFoto').modal('show');
    setTimeout(() => { $('#fileInput').click(); }, 300);
}

function resetUploadModal() {
    $('#upload_image_data').val('');
    $('#imagePreview').hide().attr('src', '');
    $('#placeholderText').show();
    $('#btnSubmitUpload').prop('disabled', true);
    $('#upload_latitude').val(currentCoords.lat);
    $('#upload_longitude').val(currentCoords.lng);
}

function handleFileSelected(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();

        reader.onload = function(e) {
            $('#imagePreview').attr('src', e.target.result).show();
            $('#placeholderText').hide();
            $('#upload_image_data').val(e.target.result);
            $('#btnSubmitUpload').prop('disabled', false);
        };
        reader.readAsDataURL(file);
    }
}

function submitUploadFoto() {
    const bmnId = $('#upload_bmn_id').val();
    const btn = $('#btnSubmitUpload');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan & Watermark...');

    const formData = new FormData($('#formUploadFoto')[0]);
    formData.append('<?= csrf_token(); ?>', '<?= csrf_hash(); ?>');

    $.ajax({
        url: '<?= site_url("admin/inventaris/sekolah-rakyat/foto"); ?>/' + bmnId + '/upload',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                $('#modalUploadFoto').modal('hide');
                location.reload();
            } else {
                alert(res.error || 'Gagal menyimpan foto.');
                btn.prop('disabled', false).html('<i class="fas fa-cloud-upload-alt mr-1"></i> Simpan & Beri Watermark');
            }
        },
        error: function() {
            alert('Terjadi kesalahan koneksi server.');
            btn.prop('disabled', false).html('<i class="fas fa-cloud-upload-alt mr-1"></i> Simpan & Beri Watermark');
        }
    });
}

function hapusFoto(fotoId) {
    if (confirm('Apakah Anda yakin ingin menghapus foto dokumentasi ini?')) {
        $.ajax({
            url: '<?= site_url("admin/inventaris/sekolah-rakyat/foto"); ?>/' + fotoId + '/hapus',
            type: 'POST',
            data: {
                <?= csrf_token(); ?>: '<?= csrf_hash(); ?>'
            },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    location.reload();
                } else {
                    alert(res.error || 'Gagal menghapus foto.');
                }
            },
            error: function() {
                alert('Terjadi kesalahan koneksi.');
            }
        });
    }
}

function previewImageModal(src, title) {
    $('#previewImageSrc').attr('src', src);
    $('#previewImageTitle').text(title || 'Foto Watermark');
    $('#modalPreviewImage').modal('show');
}

$(document).ready(function() {
    detectGPS();
});
</script>
<?= $this->endSection(); ?>
