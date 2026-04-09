<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<link rel="stylesheet" href="<?= base_url('assets/leaflet/leaflet.css'); ?>">
<style>
    .admin-dashboard {
        display: grid;
        gap: 1rem;
    }

    .dashboard-hero {
        border: 0;
        border-radius: 16px;
        color: #fff;
        background: linear-gradient(125deg, #0f4c81 0%, #0b7f6d 58%, #db6e2a 100%);
        overflow: hidden;
    }

    .dashboard-hero .card-body {
        padding: 1.25rem;
    }

    .dashboard-hero h2 {
        font-size: 1.5rem;
        margin-bottom: .4rem;
    }

    .hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: .6rem;
        margin-top: .9rem;
    }

    .hero-meta .badge {
        font-size: .8rem;
        padding: .45rem .65rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, .18);
        color: #fff;
        font-weight: 600;
    }

    .metric-card {
        border: 1px solid #e7ecf3;
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(20, 28, 41, .06);
    }

    .metric-card .card-body {
        padding: 1rem;
    }

    .metric-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: .55rem;
    }

    .metric-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1rem;
    }

    .metric-icon.events { background: linear-gradient(145deg, #1689a5, #0f6f87); }
    .metric-icon.instagram { background: linear-gradient(145deg, #e74366, #c82476); }
    .metric-icon.slide { background: linear-gradient(145deg, #f59f00, #ea7300); }

    .metric-value {
        font-size: 1.7rem;
        font-weight: 700;
        line-height: 1.1;
        margin-bottom: .2rem;
    }

    .metric-label {
        margin-bottom: .65rem;
        color: #5c697c;
        font-weight: 600;
    }

    .metric-split {
        display: flex;
        gap: .5rem;
        font-size: .85rem;
    }

    .metric-split span {
        padding: .28rem .55rem;
        border-radius: 999px;
        background: #f3f5f9;
        color: #38455a;
        font-weight: 600;
    }

    .panel-card {
        border: 1px solid #e8edf4;
        border-radius: 14px;
        box-shadow: 0 10px 24px rgba(15, 22, 35, .05);
    }

    .panel-card .card-header {
        border-bottom: 1px solid #e8edf4;
        background: #fff;
        padding: .8rem 1rem;
    }

    .panel-card .card-body {
        padding: 1rem;
    }

    .quick-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: .65rem;
    }

    .quick-link {
        border: 1px solid #e4eaf2;
        border-radius: 12px;
        background: #fff;
        color: #1f2f43;
        padding: .8rem .9rem;
        display: flex;
        align-items: center;
        gap: .55rem;
        font-weight: 600;
    }

    .quick-link i {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #eef3fa;
        color: #0d5aa7;
    }

    .table-sm td,
    .table-sm th {
        padding: .6rem;
        vertical-align: middle;
    }

    .map-panel .card-body {
        padding: 1rem;
    }

    .map-filter-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(180px, 1fr));
        gap: .75rem;
        margin-bottom: .85rem;
    }

    .map-box {
        width: 100%;
        height: 55vh;
        min-height: 420px;
        border-radius: 12px;
        border: 1px solid #dce5f2;
        overflow: hidden;
    }

    .map-legend {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        margin-bottom: .7rem;
    }

    .map-legend .badge {
        font-weight: 600;
        font-size: .78rem;
    }

    .map-total {
        text-align: right;
        font-size: .92rem;
        font-weight: 600;
        color: #334155;
    }

    @media (max-width: 768px) {
        .dashboard-hero h2 {
            font-size: 1.2rem;
        }

        .map-filter-grid {
            grid-template-columns: 1fr;
        }

        .map-box {
            min-height: 340px;
            height: 48vh;
        }
    }
</style>

<div class="admin-dashboard">
    <div class="card dashboard-hero">
        <div class="card-body">
            <h2>Selamat datang, <?= esc((string) session()->get('fullName')); ?>.</h2>
            <p class="mb-0">Dashboard ini merangkum performa konten, status publikasi, dan akses cepat pengelolaan portal.</p>
            <div class="hero-meta">
                <span class="badge"><i class="fas fa-user-shield mr-1"></i> <?= esc((string) session()->get('role')); ?></span>
                <span class="badge"><i class="fas fa-clock mr-1"></i> <?= esc(date('d M Y H:i')); ?> WIB</span>
                <span class="badge"><i class="fas fa-globe mr-1"></i> Monitoring Konten Publik</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 col-md-6 col-12 mb-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="metric-top">
                        <strong>Total Acara</strong>
                        <span class="metric-icon events"><i class="fas fa-calendar-days"></i></span>
                    </div>
                    <div class="metric-value"><?= esc((string) $eventCount); ?></div>
                    <p class="metric-label">Konten acara yang dikelola.</p>
                    <div class="metric-split">
                        <span><i class="fas fa-circle text-success mr-1"></i> Publikasi: <?= esc((string) $eventPublishedCount); ?></span>
                        <span><i class="fas fa-circle text-secondary mr-1"></i> Draft: <?= esc((string) $eventDraftCount); ?></span>
                    </div>
                    <a href="<?= site_url('/admin/acara'); ?>" class="btn btn-sm btn-outline-primary mt-3">Kelola Acara</a>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 col-12 mb-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="metric-top">
                        <strong>Feed Instagram</strong>
                        <span class="metric-icon instagram"><i class="fab fa-instagram"></i></span>
                    </div>
                    <div class="metric-value"><?= esc((string) $articleCount); ?></div>
                    <p class="metric-label">Post Instagram yang tersimpan.</p>
                    <div class="metric-split">
                        <span><i class="fas fa-circle text-success mr-1"></i> Publikasi: <?= esc((string) $articlePublishedCount); ?></span>
                        <span><i class="fas fa-circle text-secondary mr-1"></i> Draft: <?= esc((string) $articleDraftCount); ?></span>
                    </div>
                    <a href="<?= site_url('/admin/berita'); ?>" class="btn btn-sm btn-outline-primary mt-3">Kelola Feed</a>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-12 col-12 mb-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="metric-top">
                        <strong>Slide Homepage</strong>
                        <span class="metric-icon slide"><i class="fas fa-images"></i></span>
                    </div>
                    <div class="metric-value"><?= esc((string) $slideCount); ?></div>
                    <p class="metric-label">Jumlah slide hero homepage.</p>
                    <div class="metric-split">
                        <span><i class="fas fa-circle text-success mr-1"></i> Aktif: <?= esc((string) $slideActiveCount); ?></span>
                        <span><i class="fas fa-circle text-secondary mr-1"></i> Nonaktif: <?= esc((string) max(0, $slideCount - $slideActiveCount)); ?></span>
                    </div>
                    <a href="<?= site_url('/admin/pengaturan-home'); ?>" class="btn btn-sm btn-outline-primary mt-3">Kelola Home</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7 col-12 mb-3">
            <div class="card panel-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Aktivitas Konten Terbaru</h3>
                    <span class="badge badge-light">Live Snapshot</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                            <tr>
                                <th>Jenis</th>
                                <th>Judul</th>
                                <th>Status</th>
                                <th>Waktu</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($latestEvents as $event): ?>
                                <tr>
                                    <td><span class="badge badge-info">Acara</span></td>
                                    <td><?= esc($event['title'] ?? '-'); ?></td>
                                    <td>
                                        <?php if ((int) ($event['is_published'] ?? 0) === 1): ?>
                                            <span class="badge badge-success">Publikasi</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Draft</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc((string) ($event['event_date'] ?? '-')); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php foreach ($latestInstagramPosts as $post): ?>
                                <tr>
                                    <td><span class="badge badge-danger">Instagram</span></td>
                                    <td><?= esc($post['title'] ?? '-'); ?></td>
                                    <td>
                                        <?php if ((int) ($post['is_published'] ?? 0) === 1): ?>
                                            <span class="badge badge-success">Publikasi</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Draft</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc((string) ($post['published_at'] ?? '-')); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($latestEvents) && empty($latestInstagramPosts)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Belum ada aktivitas konten terbaru.</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5 col-12 mb-3">
            <div class="card panel-card mb-3">
                <div class="card-header">
                    <h3 class="card-title mb-0">Aksi Cepat</h3>
                </div>
                <div class="card-body">
                    <div class="quick-grid">
                        <a class="quick-link" href="<?= site_url('/admin/acara/tambah'); ?>">
                            <i class="fas fa-plus"></i> Tambah Acara
                        </a>
                        <a class="quick-link" href="<?= site_url('/admin/berita/tambah'); ?>">
                            <i class="fab fa-instagram"></i> Tambah Feed
                        </a>
                        <a class="quick-link" href="<?= site_url('/admin/pengaturan-home'); ?>">
                            <i class="fas fa-sliders-h"></i> Atur Homepage
                        </a>
                        <a class="quick-link" href="<?= site_url('/admin/master/kop-surat'); ?>">
                            <i class="fas fa-file-signature"></i> Master Kop Surat
                        </a>
                    </div>
                </div>
            </div>

            <div class="card panel-card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Checklist Harian Admin</h3>
                </div>
                <div class="card-body">
                    <ul class="mb-0 pl-3">
                        <li class="mb-2">Verifikasi minimal 1 post Instagram sudah status publikasi.</li>
                        <li class="mb-2">Cek jadwal acara mendatang agar tanggal dan lokasi lengkap.</li>
                        <li class="mb-2">Pastikan slider homepage menampilkan konten terbaru.</li>
                        <li>Review menu dan akses pengguna sebelum akhir hari kerja.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="card panel-card map-panel">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <h3 class="card-title mb-0">Peta Sebaran Sekolah</h3>
            <span class="badge badge-light">Referensi: example/Dashboard</span>
        </div>
        <div class="card-body">
            <div class="map-filter-grid">
                <div>
                    <label class="mb-1">Tipe Map</label>
                    <select class="form-control" id="dashboardMapType">
                        <?php foreach (($mapTypes ?? []) as $mapType): ?>
                            <option value="<?= esc((string) ($mapType['id'] ?? '')); ?>" <?= (int) ($mapType['id'] ?? 0) === (int) ($mapDefaultId ?? 1) ? 'selected' : ''; ?>>
                                <?= esc((string) ($mapType['map_name'] ?? 'Leaflet Map')); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="mb-1">NPSN</label>
                    <input type="text" class="form-control" id="dashboardNpsn" placeholder="Contoh: 10498808">
                </div>
                <div>
                    <label class="mb-1">Nama Madrasah</label>
                    <input type="text" class="form-control" id="dashboardNama" placeholder="Nama sekolah">
                </div>
                <div>
                    <label class="mb-1">Kabupaten</label>
                    <select class="form-control" id="dashboardKabupaten">
                        <option value="*">Semua Kabupaten</option>
                        <?php foreach (($kabupatenOptions ?? []) as $kabupaten): ?>
                            <option value="<?= esc($kabupaten); ?>"><?= esc($kabupaten); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="mb-1">Kecamatan</label>
                    <select class="form-control" id="dashboardKecamatan">
                        <option value="*">Semua Kecamatan</option>
                        <?php foreach (($kecamatanOptions ?? []) as $kecamatan): ?>
                            <option value="<?= esc($kecamatan); ?>"><?= esc($kecamatan); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="mb-1">Klasifikasi Kerusakan</label>
                    <select class="form-control" id="dashboardKlasifikasi">
                        <option value="*">Semua Klasifikasi</option>
                        <?php foreach (($klasifikasiOptions ?? []) as $klasifikasi): ?>
                            <option value="<?= esc($klasifikasi); ?>"><?= esc($klasifikasi); ?></option>
                        <?php endforeach; ?>
                        <option value="non_klasifikasi">Belum Klasifikasi</option>
                    </select>
                </div>
                <div class="d-flex align-items-end">
                    <button class="btn btn-primary btn-block" type="button" id="dashboardMapSearchBtn">
                        <i class="fas fa-search mr-1"></i> Cari
                    </button>
                </div>
                <div class="d-flex align-items-end justify-content-end">
                    <div class="map-total w-100">
                        Total Sekolah di Peta: <span id="dashboardMapTotal">0</span>
                    </div>
                </div>
            </div>

            <div class="map-legend">
                <span class="badge badge-danger">Rusak Berat</span>
                <span class="badge badge-warning">Rusak Sedang</span>
                <span class="badge badge-success">Rusak Ringan</span>
                <span class="badge badge-primary">Belum Klasifikasi</span>
            </div>

            <div id="dashboardMapBox" class="map-box"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="dashboardMapDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Sekolah</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-2"><strong>NPSN:</strong> <span id="mapDtlNpsn">-</span></div>
                    <div class="col-md-6 mb-2"><strong>Nama:</strong> <span id="mapDtlNama">-</span></div>
                    <div class="col-md-6 mb-2"><strong>Jenis:</strong> <span id="mapDtlJenis">-</span></div>
                    <div class="col-md-6 mb-2"><strong>NSM:</strong> <span id="mapDtlNsm">-</span></div>
                    <div class="col-md-6 mb-2"><strong>Kabupaten:</strong> <span id="mapDtlKabupaten">-</span></div>
                    <div class="col-md-6 mb-2"><strong>Kecamatan:</strong> <span id="mapDtlKecamatan">-</span></div>
                    <div class="col-md-6 mb-2"><strong>Latitude:</strong> <span id="mapDtlLatitude">-</span></div>
                    <div class="col-md-6 mb-2"><strong>Longitude:</strong> <span id="mapDtlLongitude">-</span></div>
                    <div class="col-md-6 mb-2"><strong>Periode Survey:</strong> <span id="mapDtlPeriode">-</span></div>
                    <div class="col-md-6 mb-2"><strong>Klasifikasi:</strong> <span id="mapDtlKlasifikasi">-</span></div>
                    <div class="col-md-6 mb-2"><strong>Tingkat Kerusakan:</strong> <span id="mapDtlTingkat">-</span></div>
                    <div class="col-md-6 mb-2"><strong>Status Lahan:</strong> <span id="mapDtlStatusLahan">-</span></div>
                    <div class="col-md-6 mb-2"><strong>Status Penanganan:</strong> <span id="mapDtlPenanganan">-</span></div>
                    <div class="col-md-6 mb-2"><strong>Ekspos Status:</strong> <span id="mapDtlEkspos">-</span></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script src="<?= base_url('assets/leaflet/leaflet.js'); ?>"></script>
<script>
(() => {
    if (typeof L === 'undefined') {
        return;
    }

    const mapElement = document.getElementById('dashboardMapBox');
    if (!mapElement) {
        return;
    }

    const inputs = {
        mapType: document.getElementById('dashboardMapType'),
        npsn: document.getElementById('dashboardNpsn'),
        nama: document.getElementById('dashboardNama'),
        kabupaten: document.getElementById('dashboardKabupaten'),
        kecamatan: document.getElementById('dashboardKecamatan'),
        klasifikasi: document.getElementById('dashboardKlasifikasi'),
        total: document.getElementById('dashboardMapTotal'),
        search: document.getElementById('dashboardMapSearchBtn'),
    };

    const modalEl = document.getElementById('dashboardMapDetailModal');
    const detailFields = {
        npsn: document.getElementById('mapDtlNpsn'),
        nama: document.getElementById('mapDtlNama'),
        jenis: document.getElementById('mapDtlJenis'),
        nsm: document.getElementById('mapDtlNsm'),
        kabupaten: document.getElementById('mapDtlKabupaten'),
        kecamatan: document.getElementById('mapDtlKecamatan'),
        latitude: document.getElementById('mapDtlLatitude'),
        longitude: document.getElementById('mapDtlLongitude'),
        periode: document.getElementById('mapDtlPeriode'),
        klasifikasi: document.getElementById('mapDtlKlasifikasi'),
        tingkat: document.getElementById('mapDtlTingkat'),
        statusLahan: document.getElementById('mapDtlStatusLahan'),
        penanganan: document.getElementById('mapDtlPenanganan'),
        ekspos: document.getElementById('mapDtlEkspos'),
    };

    const map = L.map('dashboardMapBox').setView([-0.51544, 101.44415], 8);
    const markerLayer = L.layerGroup().addTo(map);
    let mapScript = '';

    const clearTileLayers = () => {
        map.eachLayer((layer) => {
            if (layer instanceof L.TileLayer) {
                map.removeLayer(layer);
            }
        });
    };

    const applyMapScript = (script) => {
        clearTileLayers();

        const normalized = String(script || '').replace(/http:\/\//g, 'https://');
        let applied = false;

        if (normalized.trim() !== '') {
            try {
                const fn = new Function('map', 'L', normalized);
                fn(map, L);
                applied = true;
            } catch (error) {
                applied = false;
            }
        }

        if (!applied) {
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);
        }
    };

    const getMarkerColor = (klasifikasi) => {
        const text = String(klasifikasi || '').toLowerCase();
        if (text === 'rusak berat') return '#ef4444';
        if (text === 'rusak sedang') return '#f59e0b';
        if (text === 'rusak ringan') return '#10b981';
        return '#2563eb';
    };

    const updateDetailModal = (item) => {
        detailFields.npsn.textContent = item.npsn || '-';
        detailFields.nama.textContent = item.nama || '-';
        detailFields.jenis.textContent = item.jenis || '-';
        detailFields.nsm.textContent = item.nsm || '-';
        detailFields.kabupaten.textContent = item.kabupaten || '-';
        detailFields.kecamatan.textContent = item.kecamatan || '-';
        detailFields.latitude.textContent = item.latitude != null ? String(item.latitude) : '-';
        detailFields.longitude.textContent = item.longitude != null ? String(item.longitude) : '-';
        detailFields.periode.textContent = item.periode || '-';
        detailFields.klasifikasi.textContent = item.survey_klasifikasi_kerusakan || '-';
        detailFields.tingkat.textContent = item.survey_tingat_kerusakan || '-';
        detailFields.statusLahan.textContent = item.status_lahan || '-';
        detailFields.penanganan.textContent = item.status_penanganan || '-';
        detailFields.ekspos.textContent = item.ekspos_status || '-';
    };

    const openDetailModal = (item) => {
        updateDetailModal(item);
        if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
            const instance = window.bootstrap.Modal.getOrCreateInstance(modalEl);
            instance.show();
            return;
        }

        if (typeof $ !== 'undefined') {
            $(modalEl).modal('show');
        }
    };

    const renderMarkers = (markers) => {
        markerLayer.clearLayers();

        if (!Array.isArray(markers) || markers.length === 0) {
            return;
        }

        const bounds = [];
        markers.forEach((item) => {
            const marker = L.circleMarker([item.latitude, item.longitude], {
                radius: 8,
                color: '#fff',
                weight: 1,
                fillColor: getMarkerColor(item.survey_klasifikasi_kerusakan),
                fillOpacity: 0.92,
            });

            marker.bindPopup('<strong>' + (item.nama || '-') + '</strong><br>NPSN: ' + (item.npsn || '-') + '<br>' + (item.kecamatan || '-') + ', ' + (item.kabupaten || '-'));
            marker.on('click', () => openDetailModal(item));
            marker.addTo(markerLayer);
            bounds.push([item.latitude, item.longitude]);
        });

        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [24, 24], maxZoom: 12 });
        }
    };

    const buildQuery = () => {
        const params = new URLSearchParams();
        params.set('map_type', inputs.mapType.value || '1');
        params.set('npsn', inputs.npsn.value || '');
        params.set('nama', inputs.nama.value || '');
        params.set('kabupaten', inputs.kabupaten.value || '*');
        params.set('kecamatan', inputs.kecamatan.value || '*');
        params.set('klasifikasi', inputs.klasifikasi.value || '*');
        return params.toString();
    };

    const loadMapData = async () => {
        const url = '<?= site_url('admin/dashboard/map-data'); ?>?' + buildQuery();

        try {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Mohon Tunggu',
                    text: 'Memuat data peta...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => Swal.showLoading(),
                });
            }

            const response = await fetch(url, { method: 'GET', headers: { 'Accept': 'application/json' } });
            const payload = await response.json();

            if (!response.ok || payload.status !== 'ok') {
                throw new Error(payload.message || 'Gagal memuat data peta.');
            }

            mapScript = payload.map_type && payload.map_type.map_script ? payload.map_type.map_script : mapScript;
            applyMapScript(mapScript);
            renderMarkers(payload.markers || []);
            inputs.total.textContent = String(payload.total || 0);

            if (typeof Swal !== 'undefined') {
                Swal.close();
            }
        } catch (error) {
            inputs.total.textContent = '0';
            markerLayer.clearLayers();
            applyMapScript(mapScript);

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: error && error.message ? error.message : 'Gagal memuat data peta.',
                });
            }
        }
    };

    inputs.search.addEventListener('click', loadMapData);
    inputs.mapType.addEventListener('change', loadMapData);
    inputs.kabupaten.addEventListener('change', loadMapData);
    inputs.kecamatan.addEventListener('change', loadMapData);
    inputs.klasifikasi.addEventListener('change', loadMapData);
    inputs.npsn.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            loadMapData();
        }
    });
    inputs.nama.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            loadMapData();
        }
    });

    applyMapScript(mapScript);
    loadMapData();
})();
</script>
<?= $this->endSection(); ?>
