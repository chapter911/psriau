<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<link rel="stylesheet" href="<?= base_url('assets/leaflet/leaflet.css'); ?>">
<style>
    .map-page-card {
        border: 1px solid #e8edf4;
        border-radius: 14px;
        box-shadow: 0 10px 24px rgba(15, 22, 35, .05);
    }

    .map-page-card .card-header {
        border-bottom: 1px solid #e8edf4;
        background: #fff;
        padding: .8rem 1rem;
    }

    .map-page-card .card-body {
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
        height: 68vh;
        min-height: 460px;
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
        .map-filter-grid {
            grid-template-columns: 1fr;
        }

        .map-box {
            min-height: 340px;
            height: 52vh;
        }
    }
</style>

<div class="card map-page-card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
        <h3 class="card-title mb-0">Peta Sebaran Sekolah</h3>
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
                <select class="form-control" id="dashboardKecamatan" disabled>
                    <option value="*">Pilih kabupaten terlebih dahulu</option>
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
    const boundaryLayer = L.layerGroup().addTo(map);
    let mapScript = '';
    const markerIconCache = new Map();

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

    const loadRiauBoundary = async () => {
        const candidates = [
            '<?= base_url('geojson/provinsi_riau.json'); ?>',
            '<?= base_url('geojson/kabupaten.json'); ?>'
        ];

        for (const url of candidates) {
            try {
                const response = await fetch(url, { method: 'GET', headers: { 'Accept': 'application/json' } });
                if (!response.ok) {
                    continue;
                }

                const geojson = await response.json();
                if (!geojson || !Array.isArray(geojson.features)) {
                    continue;
                }

                const sourceFeatures = geojson.features;
                const riauFeatures = sourceFeatures.filter((feature) => {
                    const props = feature && feature.properties ? feature.properties : {};
                    return String(props.WADMPR || '').trim().toLowerCase() === 'riau';
                });

                const features = riauFeatures.length > 0 ? riauFeatures : sourceFeatures;
                if (features.length === 0) {
                    continue;
                }

                const layer = L.geoJSON({ type: 'FeatureCollection', features }, {
                    style: {
                        color: '#2563eb',
                        weight: 1.8,
                        fillColor: '#93c5fd',
                        fillOpacity: 0.08,
                    },
                    onEachFeature: (feature, featureLayer) => {
                        const props = feature && feature.properties ? feature.properties : {};
                        const tooltipText = String(props.WADMKK || props.NAMOBJ || '').trim();
                        if (tooltipText !== '') {
                            featureLayer.bindTooltip(tooltipText);
                        }
                    }
                });

                boundaryLayer.clearLayers();
                layer.addTo(boundaryLayer);
                boundaryLayer.eachLayer((item) => {
                    if (item && typeof item.bringToBack === 'function') {
                        item.bringToBack();
                    }
                });
                return;
            } catch (error) {
                // Try next candidate source.
            }
        }
    };

    const getMarkerColor = (klasifikasi) => {
        const text = String(klasifikasi || '').toLowerCase();
        if (text === 'rusak berat') return '#ef4444';
        if (text === 'rusak sedang') return '#f59e0b';
        if (text === 'rusak ringan') return '#10b981';
        return '#2563eb';
    };

    const getMarkerIcon = (color) => {
        const key = String(color || '#2563eb').toLowerCase();
        if (markerIconCache.has(key)) {
            return markerIconCache.get(key);
        }

        const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 48"><path fill="${key}" stroke="#7f1d1d" stroke-width="1.2" d="M16 1C8.27 1 2 7.27 2 15c0 10.37 11.67 25.72 13.12 27.57a1.1 1.1 0 0 0 1.76 0C18.33 40.72 30 25.37 30 15 30 7.27 23.73 1 16 1z"/><circle cx="16" cy="15" r="6" fill="#fff"/></svg>`;
        const icon = L.icon({
            iconUrl: 'data:image/svg+xml;base64,' + btoa(svg),
            iconSize: [28, 42],
            iconAnchor: [14, 42],
            popupAnchor: [0, -36],
            shadowUrl: '<?= base_url('assets/leaflet/images/marker-shadow.png'); ?>',
            shadowSize: [41, 41],
            shadowAnchor: [12, 41],
        });

        markerIconCache.set(key, icon);
        return icon;
    };

    const setKecamatanOptions = (items, selectedValue = '*') => {
        const values = Array.isArray(items) ? items : [];
        const normalizedSelected = String(selectedValue || '*');

        inputs.kecamatan.innerHTML = '';

        if (values.length === 0) {
            inputs.kecamatan.disabled = true;
            const option = document.createElement('option');
            option.value = '*';
            option.textContent = inputs.kabupaten.value && inputs.kabupaten.value !== '*' ? 'Semua Kecamatan' : 'Pilih kabupaten terlebih dahulu';
            option.selected = true;
            inputs.kecamatan.appendChild(option);
            return;
        }

        inputs.kecamatan.disabled = false;
        const defaultOption = document.createElement('option');
        defaultOption.value = '*';
        defaultOption.textContent = 'Semua Kecamatan';
        defaultOption.selected = normalizedSelected === '*';
        inputs.kecamatan.appendChild(defaultOption);

        values.forEach((name) => {
            const value = String(name || '').trim();
            if (value === '') {
                return;
            }

            const option = document.createElement('option');
            option.value = value;
            option.textContent = value;
            if (value === normalizedSelected) {
                option.selected = true;
            }
            inputs.kecamatan.appendChild(option);
        });
    };

    const loadKecamatanOptions = async (selectedKabupaten, selectedKecamatan = '*') => {
        const kabupaten = String(selectedKabupaten || '').trim();
        if (kabupaten === '' || kabupaten === '*') {
            setKecamatanOptions([], '*');
            return;
        }

        try {
            const endpoint = '<?= site_url('admin/dashboard/map-kecamatan-options'); ?>?kabupaten=' + encodeURIComponent(kabupaten);
            const response = await fetch(endpoint, { method: 'GET', headers: { 'Accept': 'application/json' } });
            const payload = await response.json();

            if (!response.ok || payload.status !== 'ok') {
                setKecamatanOptions([], '*');
                return;
            }

            setKecamatanOptions(payload.kecamatan || [], selectedKecamatan || '*');
        } catch (error) {
            setKecamatanOptions([], '*');
        }
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
            const lat = Number(item.latitude);
            const lng = Number(item.longitude);
            if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                return;
            }

            const marker = L.marker([lat, lng], {
                icon: getMarkerIcon(getMarkerColor(item.survey_klasifikasi_kerusakan)),
                zIndexOffset: 1000,
            });

            marker.bindPopup('<strong>' + (item.nama || '-') + '</strong><br>NPSN: ' + (item.npsn || '-') + '<br>' + (item.kecamatan || '-') + ', ' + (item.kabupaten || '-'));
            marker.on('click', () => openDetailModal(item));
            marker.addTo(markerLayer);
            bounds.push([lat, lng]);
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
    inputs.kabupaten.addEventListener('change', async () => {
        await loadKecamatanOptions(inputs.kabupaten.value, '*');
        await loadMapData();
    });
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
    loadRiauBoundary();
    loadKecamatanOptions(inputs.kabupaten.value, '*').then(loadMapData);
})();
</script>
<?= $this->endSection(); ?>
