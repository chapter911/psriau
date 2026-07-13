<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<link rel="stylesheet" href="<?= esc(media_url('assets/leaflet/leaflet.css')); ?>">
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
        <div>
            <button class="btn btn-danger btn-sm" type="button" id="exportMapPdfBtnMain">
                <i class="fas fa-file-pdf mr-1"></i> Export Peta A3
            </button>
        </div>
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
                <label class="mb-1">Paket</label>
                <select class="form-control" id="dashboardPaket">
                    <option value="*">Semua Paket</option>
                    <?php foreach (($paketOptions ?? []) as $paket): ?>
                        <option value="<?= esc((string) ($paket['id'] ?? ''), 'attr'); ?>"><?= esc((string) ($paket['nama_paket'] ?? '-')); ?></option>
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
                <h5 class="modal-title">Keterangan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" id="map-detail-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="map-tab-sekolah" data-toggle="pill" href="#map-pane-sekolah" role="tab" aria-controls="map-pane-sekolah" aria-selected="true">Madrasah</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="map-tab-survey" data-toggle="pill" href="#map-pane-survey" role="tab" aria-controls="map-pane-survey" aria-selected="false">Survey</a>
                    </li>
                </ul>
                <div class="tab-content pt-3">
                    <div class="tab-pane fade show active" id="map-pane-sekolah" role="tabpanel" aria-labelledby="map-tab-sekolah">
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">NPSN</label>
                            <div class="col-sm-8"><input type="text" class="form-control" id="dtl_npsn" readonly></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">NAMA</label>
                            <div class="col-sm-8"><input type="text" class="form-control" id="dtl_nama" readonly></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">JENIS</label>
                            <div class="col-sm-8"><input type="text" class="form-control" id="dtl_jenis" readonly></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">NSM</label>
                            <div class="col-sm-8"><input type="text" class="form-control" id="dtl_nsm" readonly></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">KABUPATEN</label>
                            <div class="col-sm-8"><input type="text" class="form-control" id="dtl_kabupaten" readonly></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">KECAMATAN</label>
                            <div class="col-sm-8"><input type="text" class="form-control" id="dtl_kecamatan" readonly></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">LATITUDE</label>
                            <div class="col-sm-8"><input type="text" class="form-control" id="dtl_latitude" readonly></div>
                        </div>
                        <div class="form-group row mb-0">
                            <label class="col-sm-4 col-form-label">LONGITUDE</label>
                            <div class="col-sm-8"><input type="text" class="form-control" id="dtl_longitude" readonly></div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="map-pane-survey" role="tabpanel" aria-labelledby="map-tab-survey">
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">PERIODE</label>
                            <div class="col-sm-8"><input type="text" class="form-control" id="dtl_periode" readonly></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">EMIS JUMLAH SISWA</label>
                            <div class="col-sm-8"><input type="text" class="form-control" id="dtl_emis_jumlah_siswa" readonly></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">SURVEY JUMLAH SISWA</label>
                            <div class="col-sm-8"><input type="text" class="form-control" id="dtl_survey_jumlah_siswa" readonly></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">SURVEY TINGKAT KERUSAKAN</label>
                            <div class="col-sm-8"><input type="text" class="form-control" id="dtl_survey_tingat_kerusakan" readonly></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">SURVEY KLASIFIKASI</label>
                            <div class="col-sm-8"><input type="text" class="form-control" id="dtl_survey_klasifikasi_kerusakan" readonly></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">STATUS LAHAN</label>
                            <div class="col-sm-8"><input type="text" class="form-control" id="dtl_status_lahan" readonly></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">STATUS PENANGANAN</label>
                            <div class="col-sm-8"><input type="text" class="form-control" id="dtl_status_penanganan" readonly></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">EKSPOS TINGKAT KERUSAKAN</label>
                            <div class="col-sm-8"><input type="text" class="form-control" id="dtl_ekspos_tingkat_kerusakan" readonly></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">EKSPOS KLASIFIKASI</label>
                            <div class="col-sm-8"><input type="text" class="form-control" id="dtl_ekspos_klasifikasi_kerusakan" readonly></div>
                        </div>
                        <div class="form-group row mb-0">
                            <label class="col-sm-4 col-form-label">EKSPOS STATUS</label>
                            <div class="col-sm-8"><input type="text" class="form-control" id="dtl_ekspos_status" readonly></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="btn-export-peta-pdf-dashboard">
                    <i class="fas fa-file-pdf mr-1"></i> Export Peta A3
                </button>
                <a id="mapOpenGoogleBtn" class="btn btn-outline-primary" href="#" target="_blank" rel="noopener noreferrer" aria-disabled="true">
                    <i class="fas fa-map-marked-alt mr-1"></i> Buka Google Maps
                </a>
                <button type="button" class="btn btn-primary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script src="<?= esc(media_url('assets/leaflet/leaflet.js')); ?>"></script>
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
        paket: document.getElementById('dashboardPaket'),
        kecamatan: document.getElementById('dashboardKecamatan'),
        klasifikasi: document.getElementById('dashboardKlasifikasi'),
        total: document.getElementById('dashboardMapTotal'),
        search: document.getElementById('dashboardMapSearchBtn'),
    };

    const modalEl = document.getElementById('dashboardMapDetailModal');
    const googleMapBtn = document.getElementById('mapOpenGoogleBtn');
    const detailFields = {
        npsn: document.getElementById('dtl_npsn'),
        nama: document.getElementById('dtl_nama'),
        jenis: document.getElementById('dtl_jenis'),
        nsm: document.getElementById('dtl_nsm'),
        kabupaten: document.getElementById('dtl_kabupaten'),
        kecamatan: document.getElementById('dtl_kecamatan'),
        latitude: document.getElementById('dtl_latitude'),
        longitude: document.getElementById('dtl_longitude'),
        periode: document.getElementById('dtl_periode'),
        emisJumlahSiswa: document.getElementById('dtl_emis_jumlah_siswa'),
        surveyJumlahSiswa: document.getElementById('dtl_survey_jumlah_siswa'),
        surveyTingkatKerusakan: document.getElementById('dtl_survey_tingat_kerusakan'),
        surveyKlasifikasiKerusakan: document.getElementById('dtl_survey_klasifikasi_kerusakan'),
        statusLahan: document.getElementById('dtl_status_lahan'),
        statusPenanganan: document.getElementById('dtl_status_penanganan'),
        eksposTingkatKerusakan: document.getElementById('dtl_ekspos_tingkat_kerusakan'),
        eksposKlasifikasiKerusakan: document.getElementById('dtl_ekspos_klasifikasi_kerusakan'),
        eksposStatus: document.getElementById('dtl_ekspos_status'),
    };

    const map = L.map('dashboardMapBox').setView([-0.51544, 101.44415], 8);
    const markerLayer = L.layerGroup().addTo(map);
    const boundaryLayer = L.layerGroup().addTo(map);
    let mapScript = '';
    let activeMarkers = [];
    const markerIconCache = new Map();

    const clearTileLayers = () => {
        map.eachLayer((layer) => {
            if (layer instanceof L.TileLayer) {
                map.removeLayer(layer);
            }
        });
    };

    const clearScaleControls = () => {
        const controls = mapElement.querySelectorAll('.leaflet-control-scale');
        controls.forEach((el) => {
            if (el && el.parentNode) {
                el.parentNode.removeChild(el);
            }
        });
    };

    const applyMapScript = (script) => {
        clearTileLayers();
        clearScaleControls();

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
            '<?= esc(media_url('geojson/provinsi_riau.json')); ?>',
            '<?= esc(media_url('geojson/kabupaten.json')); ?>'
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
            shadowUrl: '<?= esc(media_url('assets/leaflet/images/marker-shadow.png')); ?>',
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

    const setInputValue = (element, value) => {
        if (!element) {
            return;
        }

        element.value = (value === null || value === undefined || String(value).trim() === '') ? '-' : String(value);
    };

    const updateDetailModal = (school, survey) => {
        const schoolData = school || {};
        const surveyData = survey || {};

        setInputValue(detailFields.npsn, schoolData.npsn);
        setInputValue(detailFields.nama, schoolData.nama);
        setInputValue(detailFields.jenis, schoolData.jenis);
        setInputValue(detailFields.nsm, schoolData.nsm);
        setInputValue(detailFields.kabupaten, schoolData.kabupaten);
        setInputValue(detailFields.kecamatan, schoolData.kecamatan);
        setInputValue(detailFields.latitude, schoolData.latitude);
        setInputValue(detailFields.longitude, schoolData.longitude);

        setInputValue(detailFields.periode, surveyData.periode);
        setInputValue(detailFields.emisJumlahSiswa, surveyData.emis_jumlah_siswa);
        setInputValue(detailFields.surveyJumlahSiswa, surveyData.survey_jumlah_siswa);
        setInputValue(detailFields.surveyTingkatKerusakan, surveyData.survey_tingat_kerusakan);
        setInputValue(detailFields.surveyKlasifikasiKerusakan, surveyData.survey_klasifikasi_kerusakan);
        setInputValue(detailFields.statusLahan, surveyData.status_lahan);
        setInputValue(detailFields.statusPenanganan, surveyData.status_penanganan);
        setInputValue(detailFields.eksposTingkatKerusakan, surveyData.ekspos_tingkat_kerusakan);
        setInputValue(detailFields.eksposKlasifikasiKerusakan, surveyData.ekspos_klasifikasi_kerusakan);
        setInputValue(detailFields.eksposStatus, surveyData.ekspos_status);

        if (googleMapBtn) {
            const lat = Number(schoolData.latitude);
            const lng = Number(schoolData.longitude);
            if (Number.isFinite(lat) && Number.isFinite(lng)) {
                googleMapBtn.href = 'https://www.google.com/maps?q=' + encodeURIComponent(String(lat) + ',' + String(lng));
                googleMapBtn.classList.remove('disabled');
                googleMapBtn.setAttribute('aria-disabled', 'false');
                googleMapBtn.removeAttribute('tabindex');
            } else {
                googleMapBtn.href = '#';
                googleMapBtn.classList.add('disabled');
                googleMapBtn.setAttribute('aria-disabled', 'true');
                googleMapBtn.setAttribute('tabindex', '-1');
            }
        }
    };

    const showDetailModal = () => {
        if (typeof $ !== 'undefined') {
            $('#map-tab-sekolah').tab('show');
        }

        if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
            const ModalCtor = window.bootstrap.Modal;

            if (typeof ModalCtor.getOrCreateInstance === 'function') {
                const instance = ModalCtor.getOrCreateInstance(modalEl);
                instance.show();
                return;
            }

            let instance = null;
            if (typeof ModalCtor.getInstance === 'function') {
                instance = ModalCtor.getInstance(modalEl);
            }
            if (!instance) {
                instance = new ModalCtor(modalEl);
            }
            if (instance && typeof instance.show === 'function') {
                instance.show();
                return;
            }
        }

        if (typeof $ !== 'undefined' && typeof $(modalEl).modal === 'function') {
            $(modalEl).modal('show');
        }
    };

    const openDetailModal = async (item) => {
        const npsn = String(item && item.npsn ? item.npsn : '').trim();
        if (npsn === '') {
            updateDetailModal(item || {}, {});
            showDetailModal();
            return;
        }

        try {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Mohon Tunggu',
                    text: 'Mengambil detail data sekolah...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => Swal.showLoading(),
                });
            }

            const endpoint = '<?= site_url('admin/dashboard/map-detail'); ?>?npsn=' + encodeURIComponent(npsn);
            const response = await fetch(endpoint, { method: 'GET', headers: { 'Accept': 'application/json' } });
            const payload = await response.json();

            if (!response.ok || payload.status !== 'ok') {
                throw new Error(payload.message || 'Gagal mengambil detail sekolah.');
            }

            updateDetailModal(payload.school || item || {}, payload.survey || {});
            showDetailModal();
            if (typeof Swal !== 'undefined') {
                Swal.close();
            }
        } catch (error) {
            updateDetailModal(item || {}, {});
            showDetailModal();
            if (typeof Swal !== 'undefined') {
                Swal.close();
            }
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
        params.set('paket_id', inputs.paket.value || '*');
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
            activeMarkers = payload.markers || [];
            renderMarkers(activeMarkers);
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
    inputs.paket.addEventListener('change', loadMapData);
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

    // A3 Landscape PDF Topographic Map Exporter
    function loadExportLibraries() {
        return new Promise((resolve) => {
            let loadedCount = 0;
            const checkResolve = () => {
                loadedCount++;
                if (loadedCount === 2) resolve();
            };

            if (typeof html2canvas !== 'undefined' && typeof window.jspdf !== 'undefined') {
                resolve();
                return;
            }

            if (typeof html2canvas === 'undefined') {
                const s1 = document.createElement('script');
                s1.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
                s1.onload = checkResolve;
                document.head.appendChild(s1);
            } else {
                loadedCount++;
            }

            if (typeof window.jspdf === 'undefined') {
                const s2 = document.createElement('script');
                s2.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
                s2.onload = checkResolve;
                document.head.appendChild(s2);
            } else {
                loadedCount++;
            }
        });
    }

    function createContourLine(center, radius, pointsCount, waveAmplitude) {
        const coordinates = [];
        for (let i = 0; i < pointsCount; i++) {
            const angle = (i / pointsCount) * 2 * Math.PI;
            const offset = Math.sin(angle * 5) * waveAmplitude;
            const r = radius + offset;
            const latOffset = (r * Math.cos(angle)) / 111320;
            const lngOffset = (r * Math.sin(angle)) / (111320 * Math.cos(center[0] * Math.PI / 180));
            coordinates.push([center[0] + latOffset, center[1] + lngOffset]);
        }
        coordinates.push(coordinates[0]);
        return coordinates;
    }

    async function exportMapPdf(lat, lng, schoolName, kabupaten = 'Bengkalis', kecamatan = 'Bengkalis') {
        if (!lat || !lng) {
            Swal.fire('Gagal', 'Koordinat sekolah tidak valid.', 'error');
            return;
        }

        Swal.fire({
            title: 'Mohon Tunggu',
            text: 'Menyiapkan layout peta topografi...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading(),
        });

        try {
            await loadExportLibraries();

            // Create temporary map container in hidden area
            const exportContainer = document.createElement('div');
            exportContainer.id = 'temp-export-container';
            exportContainer.style.position = 'fixed';
            exportContainer.style.left = '-9999px';
            exportContainer.style.top = '-9999px';
            exportContainer.style.width = '1587px';
            exportContainer.style.height = '1123px';
            exportContainer.style.background = 'white';
            exportContainer.style.padding = '20px';
            exportContainer.style.boxSizing = 'border-box';
            exportContainer.style.display = 'flex';
            exportContainer.style.flexDirection = 'column';
            exportContainer.style.justifyContent = 'space-between';
            exportContainer.style.border = '4px double black';
            exportContainer.style.zIndex = '-9999';
            
            // Build interior HTML
            exportContainer.innerHTML = `
                <div style="text-align: center; border: 2px solid black; padding: 10px; margin-bottom: 10px; font-weight: bold; font-size: 24px; font-family: Arial, sans-serif; letter-spacing: 1px; text-transform: uppercase;">
                    PETA LAHAN USULAN SEKOLAH RAKYAT PROVINSI RIAU
                </div>
                <div style="display: flex; justify-content: space-between; height: 1010px; font-family: Arial, sans-serif;">
                    <!-- Left: Map Container -->
                    <div id="export-map-canvas" style="width: 1150px; height: 1010px; border: 2px solid black; position: relative; background: #eaeaea;"></div>
                    
                    <!-- Right: Panel -->
                    <div style="width: 380px; height: 1010px; border: 2px solid black; display: flex; flex-direction: column; justify-content: space-between; padding: 15px; box-sizing: border-box; background: white;">
                        
                        <!-- Ministry logo and header -->
                        <div style="border: 1px solid black; padding: 10px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 160px; box-sizing: border-box;">
                            <svg width="45" height="45" viewBox="0 0 100 100" style="margin-bottom: 6px;">
                                <circle cx="50" cy="50" r="45" fill="#FFD700" stroke="#003399" stroke-width="2"/>
                                <circle cx="50" cy="50" r="28" fill="none" stroke="#003399" stroke-width="6"/>
                                <circle cx="50" cy="50" r="10" fill="#FFD700" stroke="#003399" stroke-width="6"/>
                                <path d="M 50,15 L 50,35 M 50,65 L 50,85 M 15,50 L 35,50 M 65,50 L 85,50 M 25,25 L 39,39 M 61,61 L 75,75 M 25,75 L 39,61 M 61,39 L 75,25" stroke="#003399" stroke-width="6" stroke-linecap="round"/>
                            </svg>
                            <div style="font-weight: bold; font-size: 11px; line-height: 1.2;">KEMENTERIAN PEKERJAAN UMUM</div>
                            <div style="font-size: 9px; font-weight: bold; margin-top: 4px; line-height: 1.2; text-transform: uppercase;">DIREKTORAT JENDERAL PRASARANA STRATEGIS</div>
                            <div style="font-size: 8px; color: #555; margin-top: 2px; line-height: 1.2; text-transform: uppercase;">SATUAN KERJA PELAKSANAAN PRASARANA STRATEGIS RIAU</div>
                        </div>
                        
                        <!-- Compass and Scale -->
                        <div style="border: 1px solid black; padding: 10px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 180px; box-sizing: border-box; margin-top: 10px;">
                            <svg width="60" height="60" viewBox="0 0 100 100" style="margin-bottom: 8px;">
                                <circle cx="50" cy="50" r="45" fill="none" stroke="black" stroke-width="2"/>
                                <line x1="50" y1="5" x2="50" y2="95" stroke="black" stroke-dasharray="2,2"/>
                                <line x1="5" y1="50" x2="95" y2="50" stroke="black" stroke-dasharray="2,2"/>
                                <polygon points="50,10 57,45 50,40 43,45" fill="black" stroke="black"/>
                                <polygon points="50,90 57,55 50,60 43,55" fill="grey" stroke="black"/>
                                <text x="50" y="8" font-family="Arial" font-size="10" font-weight="bold" text-anchor="middle">N</text>
                                <text x="92" y="53" font-family="Arial" font-size="10" font-weight="bold" text-anchor="middle">E</text>
                                <text x="50" y="98" font-family="Arial" font-size="10" font-weight="bold" text-anchor="middle">S</text>
                                <text x="8" y="53" font-family="Arial" font-size="10" font-weight="bold" text-anchor="middle">W</text>
                            </svg>
                            <div style="font-weight: bold; font-size: 11px; margin-bottom: 4px;">SKALA 1:3,000</div>
                            <div style="display: flex; flex-direction: column; align-items: center; width: 100%;">
                                <div style="display: flex; justify-content: space-between; width: 150px; font-size: 9px; font-weight: bold;">
                                    <span>0.12</span>
                                    <span>0.06</span>
                                    <span>0</span>
                                    <span>0.12 KM</span>
                                </div>
                                <div style="width: 150px; height: 10px; border: 1px solid black; display: flex; overflow: hidden; margin-top: 2px;">
                                    <div style="width: 25%; background: black;"></div>
                                    <div style="width: 25%; background: white;"></div>
                                    <div style="width: 25%; background: black;"></div>
                                    <div style="width: 25%; background: white;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Legend -->
                        <div style="border: 1px solid black; padding: 12px; margin-top: 10px; display: flex; flex-direction: column; justify-content: flex-start; height: 210px; box-sizing: border-box;">
                            <div style="font-weight: bold; font-size: 12px; border-bottom: 2px solid black; padding-bottom: 4px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px;">Legenda</div>
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                <div style="display: flex; align-items: center; font-size: 10px;">
                                    <div style="width: 30px; height: 15px; border: 2.5px solid red; background: rgba(255,0,0,0.05); margin-right: 10px;"></div>
                                    <div style="font-weight: bold; color: red; text-transform: uppercase; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 170px;" title="${schoolName}">${schoolName}</div>
                                </div>
                                <div style="display: flex; align-items: center; font-size: 10px;">
                                    <div style="width: 30px; height: 4px; background: #00bcd4; margin-right: 10px; position: relative;">
                                        <span style="position: absolute; top: -7px; left: 0; right: 0; text-align: center; font-size: 7px; color: #00bcd4; font-weight: bold;">45</span>
                                    </div>
                                    <div style="font-weight: bold; text-transform: uppercase;">Kontur 5m</div>
                                </div>
                            </div>
                        </div>

                        <!-- Inset Map -->
                        <div style="border: 1px solid black; padding: 5px; margin-top: 10px; text-align: center; height: 190px; box-sizing: border-box; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #f0f8ff; overflow: hidden;">
                            <div id="export-inset-canvas" style="width: 220px; height: 150px; border: 1px solid black; background: #eaeaea; position: relative;"></div>
                            <div style="font-size: 8px; font-weight: bold; margin-top: 3px; text-transform: uppercase;">Peta Indeks Provinsi Riau</div>
                        </div>

                        <!-- Signatures -->
                        <div style="display: flex; justify-content: space-between; margin-top: 10px; font-size: 8px; height: 120px; box-sizing: border-box;">
                            <div style="border: 1px solid black; width: 48%; padding: 8px; box-sizing: border-box; display: flex; flex-direction: column; justify-content: space-between; text-align: center;">
                                <div style="font-weight: bold; border-bottom: 1px solid black; padding-bottom: 2px; margin-bottom: 4px; text-transform: uppercase;">Divalidasi Oleh:</div>
                                <div style="font-size: 7px; color: #444; font-weight: bold;">KEPALA SATKER PPS RIAU</div>
                                <div style="font-weight: bold; margin-top: 25px; text-decoration: underline; font-size: 8px;">MUHAMMAD YUDI PRASETYA, S.T.</div>
                            </div>
                            <div style="border: 1px solid black; width: 48%; padding: 8px; box-sizing: border-box; display: flex; flex-direction: column; justify-content: space-between; text-align: center;">
                                <div style="font-weight: bold; border-bottom: 1px solid black; padding-bottom: 2px; margin-bottom: 4px; text-transform: uppercase;">Dibuat Oleh:</div>
                                <div style="font-size: 7px; color: #444; font-weight: bold;">STAF SATKER PPS RIAU</div>
                                <div style="font-weight: bold; margin-top: 25px; text-decoration: underline; font-size: 8px;">MUHAMMAD SYAHRIDWAN, S.T.</div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(exportContainer);

            // Initialize temp Leaflet map
            const exportMap = L.map('export-map-canvas', {
                zoomControl: false,
                attributionControl: false,
                fadeAnimation: false,
                zoomAnimation: false
            }).setView([lat, lng], 17);

            // Satellite base layer
            L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 19
            }).addTo(exportMap);

            // Initialize mini inset map using Google Maps styling
            const insetMap = L.map('export-inset-canvas', {
                zoomControl: false,
                attributionControl: false,
                dragging: false,
                touchZoom: false,
                doubleClickZoom: false,
                scrollWheelZoom: false,
                boxZoom: false,
                keyboard: false,
                fadeAnimation: false,
                zoomAnimation: false
            }).setView([-0.51544, 101.44415], 6);

            L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
                maxZoom: 20
            }).addTo(insetMap);

            // Draw Riau boundaries on the inset map
            fetch('<?= esc(media_url('geojson/provinsi_riau.json')); ?>')
                .then(res => res.json())
                .then(geojson => {
                    if (geojson && Array.isArray(geojson.features)) {
                        const features = geojson.features.filter(f => {
                            const props = f && f.properties ? f.properties : {};
                            return String(props.WADMPR || '').trim().toLowerCase() === 'riau';
                        });
                        const riauLayer = L.geoJSON({ type: 'FeatureCollection', features: features.length > 0 ? features : geojson.features }, {
                            style: {
                                color: 'red',
                                weight: 1.5,
                                fillColor: 'red',
                                fillOpacity: 0.25
                            }
                        }).addTo(insetMap);
                        
                        // Fit bounds to show Riau fully
                        insetMap.fitBounds(riauLayer.getBounds(), { padding: [5, 5] });
                    }
                })
                .catch(err => console.error(err));

            // Draw a red circle marker for the school location
            L.circleMarker([lat, lng], {
                color: 'red',
                fillColor: 'red',
                fillOpacity: 1,
                radius: 5,
                weight: 2
            }).addTo(insetMap);

            // Draw simulated contours
            const contours = [
                { radius: 60, elevation: 45 },
                { radius: 120, elevation: 40 },
                { radius: 180, elevation: 35 },
                { radius: 240, elevation: 30 }
            ];
            contours.forEach(c => {
                const coords = createContourLine([lat, lng], c.radius, 32, 8);
                L.polyline(coords, { color: '#00bcd4', weight: 1.5, opacity: 0.8 }).addTo(exportMap);
                
                // Add label
                const midPoint = coords[8];
                L.marker(midPoint, {
                    icon: L.divIcon({
                        className: 'contour-label-temp',
                        html: `<div style="color: #00bcd4; font-size: 9px; font-weight: bold; text-shadow: 1px 1px 1px #000; font-family: Arial;">${c.elevation}</div>`,
                        iconSize: [20, 10]
                    })
                }).addTo(exportMap);
            });

            // Draw school land boundary (red tilted polygon)
            const schoolCoords = [
                [lat + 0.0003, lng - 0.0004],
                [lat + 0.0004, lng + 0.0004],
                [lat - 0.0002, lng + 0.0005],
                [lat - 0.0003, lng - 0.0003]
            ];
            L.polygon(schoolCoords, { color: 'red', fill: false, weight: 2.5 }).addTo(exportMap);

            // Wait for tiles to load
            await new Promise(r => setTimeout(r, 2000));

            // Capture Leaflet map canvas
            const mapCanvas = await html2canvas(document.getElementById('export-map-canvas'), {
                useCORS: true,
                allowTaint: true,
                scale: 1.5
            });

            // Capture Inset Map canvas
            const insetCanvas = await html2canvas(document.getElementById('export-inset-canvas'), {
                useCORS: true,
                allowTaint: true,
                scale: 1.5
            });

            // Replace Leaflet Inset map with captured image
            const insetImg = document.createElement('img');
            insetImg.src = insetCanvas.toDataURL('image/jpeg');
            insetImg.style.width = '100%';
            insetImg.style.height = '100%';
            
            const insetCanvasEl = document.getElementById('export-inset-canvas');
            insetCanvasEl.innerHTML = '';
            insetCanvasEl.appendChild(insetImg);

            // Replace Leaflet map in template with captured image
            const mapImg = document.createElement('img');
            mapImg.src = mapCanvas.toDataURL('image/jpeg');
            mapImg.style.width = '100%';
            mapImg.style.height = '100%';
            
            const mapCanvasEl = document.getElementById('export-map-canvas');
            mapCanvasEl.innerHTML = '';
            mapCanvasEl.appendChild(mapImg);

            // Add coordinates grid labels overlays to the map img container
            const gridOverlay = document.createElement('div');
            gridOverlay.style.position = 'absolute';
            gridOverlay.style.top = '0';
            gridOverlay.style.left = '0';
            gridOverlay.style.width = '100%';
            gridOverlay.style.height = '100%';
            gridOverlay.style.pointerEvents = 'none';
            gridOverlay.style.border = '1px solid black';
            gridOverlay.style.boxSizing = 'border-box';
            
            // Approximate coordinates labels based on actual lat/lng
            const latStr25 = (lat + 0.0005).toFixed(5);
            const latStr50 = lat.toFixed(5);
            const latStr75 = (lat - 0.0005).toFixed(5);
            
            const lngStr25 = (lng - 0.0005).toFixed(5);
            const lngStr50 = lng.toFixed(5);
            const lngStr75 = (lng + 0.0005).toFixed(5);

            gridOverlay.innerHTML = `
                <!-- Horizontal Grid lines -->
                <div style="position: absolute; left: 0; top: 25%; width: 100%; border-top: 1px dashed rgba(255,255,255,0.4);"></div>
                <div style="position: absolute; left: 0; top: 50%; width: 100%; border-top: 1px dashed rgba(255,255,255,0.4);"></div>
                <div style="position: absolute; left: 0; top: 75%; width: 100%; border-top: 1px dashed rgba(255,255,255,0.4);"></div>
                <!-- Vertical Grid lines -->
                <div style="position: absolute; top: 0; left: 25%; height: 100%; border-left: 1px dashed rgba(255,255,255,0.4);"></div>
                <div style="position: absolute; top: 0; left: 50%; height: 100%; border-left: 1px dashed rgba(255,255,255,0.4);"></div>
                <div style="position: absolute; top: 0; left: 75%; height: 100%; border-left: 1px dashed rgba(255,255,255,0.4);"></div>
                
                <!-- Grid Labels (approximate lat/lng labels) -->
                <div style="position: absolute; top: 5px; left: 25%; font-size: 9px; transform: translateX(-50%); font-weight: bold; background: rgba(0,0,0,0.6); color: white; padding: 1px 3px; border-radius: 2px; font-family: Arial;">${lngStr25}°E</div>
                <div style="position: absolute; top: 5px; left: 50%; font-size: 9px; transform: translateX(-50%); font-weight: bold; background: rgba(0,0,0,0.6); color: white; padding: 1px 3px; border-radius: 2px; font-family: Arial;">${lngStr50}°E</div>
                <div style="position: absolute; top: 5px; left: 75%; font-size: 9px; transform: translateX(-50%); font-weight: bold; background: rgba(0,0,0,0.6); color: white; padding: 1px 3px; border-radius: 2px; font-family: Arial;">${lngStr75}°E</div>
                
                <div style="position: absolute; left: 5px; top: 25%; font-size: 9px; transform: translateY(-50%); font-weight: bold; background: rgba(0,0,0,0.6); color: white; padding: 1px 3px; border-radius: 2px; font-family: Arial;">${latStr25}°N</div>
                <div style="position: absolute; left: 5px; top: 50%; font-size: 9px; transform: translateY(-50%); font-weight: bold; background: rgba(0,0,0,0.6); color: white; padding: 1px 3px; border-radius: 2px; font-family: Arial;">${latStr50}°N</div>
                <div style="position: absolute; left: 5px; top: 75%; font-size: 9px; transform: translateY(-50%); font-weight: bold; background: rgba(0,0,0,0.6); color: white; padding: 1px 3px; border-radius: 2px; font-family: Arial;">${latStr75}°N</div>
            `;
            mapCanvasEl.appendChild(gridOverlay);

            // Capture entire layout container
            const layoutCanvas = await html2canvas(exportContainer, {
                useCORS: true,
                allowTaint: true,
                scale: 2
            });

            // Generate PDF
            const imgData = layoutCanvas.toDataURL('image/jpeg', 0.95);
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF({
                orientation: 'landscape',
                unit: 'px',
                format: 'a3'
            });

            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = pdf.internal.pageSize.getHeight();
            pdf.addImage(imgData, 'JPEG', 0, 0, pdfWidth, pdfHeight);
            
            const blobUrl = pdf.output('bloburl');
            window.open(blobUrl, '_blank');

            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Peta berhasil dibuka di tab baru.',
                timer: 2000,
                showConfirmButton: false
            });

        } catch (error) {
            console.error(error);
            Swal.fire('Error', 'Gagal mengeksport peta: ' + error.message, 'error');
        } finally {
            // Clean up DOM
            const el = document.getElementById('temp-export-container');
            if (el) el.remove();
        }
    }

    const exportPdfDashboardBtn = document.getElementById('btn-export-peta-pdf-dashboard');
    if (exportPdfDashboardBtn) {
        exportPdfDashboardBtn.addEventListener('click', function() {
            const lat = Number(detailFields.latitude.value);
            const lng = Number(detailFields.longitude.value);
            const nama = detailFields.nama.value || 'Sekolah';
            const kabupaten = detailFields.kabupaten.value || 'Bengkalis';
            const kecamatan = detailFields.kecamatan.value || 'Bengkalis';

            exportMapPdf(lat, lng, nama, kabupaten, kecamatan);
        });
    }

    async function exportMainMapPdf() {
        Swal.fire({
            title: 'Mohon Tunggu',
            text: 'Menyiapkan layout peta sebaran...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading(),
        });

        try {
            await loadExportLibraries();

            // Compute dynamic 2-column legend content
            const maxVisible = 14;
            const showCount = Math.min(activeMarkers.length, maxVisible);
            const half = Math.ceil(showCount / 2);
            
            let col1Html = '';
            let col2Html = '';
            
            for (let i = 0; i < showCount; i++) {
                const item = activeMarkers[i];
                const color = getMarkerColor(item.survey_klasifikasi_kerusakan);
                const itemHtml = `
                    <div style="display: flex; align-items: center; font-size: 8.5px; margin-bottom: 2px;">
                        <span style="display: inline-block; width: 14px; height: 14px; background-color: ${color}; border-radius: 50%; border: 1px solid white; color: white; font-family: Arial; font-size: 7.5px; font-weight: bold; text-align: center; line-height: 12px; margin-right: 5px; flex-shrink: 0;">
                            ${i + 1}
                        </span>
                        <div style="font-weight: bold; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 145px;" title="${item.nama}">${item.nama}</div>
                    </div>
                `;
                if (i < half) {
                    col1Html += itemHtml;
                } else {
                    col2Html += itemHtml;
                }
            }
            
            if (activeMarkers.length > maxVisible) {
                col2Html += `
                    <div style="font-size: 8px; font-style: italic; color: #555; font-weight: bold; margin-top: 2px; padding-left: 20px;">
                        ... dan ${activeMarkers.length - maxVisible} lainnya
                    </div>
                `;
            } else if (activeMarkers.length === 0) {
                col1Html = '<div style="font-size: 9px; font-style: italic; color: #666;">Tidak ada data sekolah</div>';
            }

            // Create temporary map container in hidden area
            const exportContainer = document.createElement('div');
            exportContainer.id = 'temp-export-container';
            exportContainer.style.position = 'fixed';
            exportContainer.style.left = '-9999px';
            exportContainer.style.top = '-9999px';
            exportContainer.style.width = '1587px';
            exportContainer.style.height = '1123px';
            exportContainer.style.background = 'white';
            exportContainer.style.padding = '20px';
            exportContainer.style.boxSizing = 'border-box';
            exportContainer.style.display = 'flex';
            exportContainer.style.flexDirection = 'column';
            exportContainer.style.justifyContent = 'space-between';
            exportContainer.style.border = '4px double black';
            exportContainer.style.zIndex = '-9999';
            
            // Build interior HTML
            exportContainer.innerHTML = `
                <div style="text-align: center; border: 2px solid black; padding: 10px; margin-bottom: 10px; font-weight: bold; font-size: 24px; font-family: Arial, sans-serif; letter-spacing: 1px; text-transform: uppercase;">
                    PETA SEBARAN SEKOLAH RAKYAT PROVINSI RIAU
                </div>
                <div style="display: flex; justify-content: space-between; height: 1010px; font-family: Arial, sans-serif;">
                    <!-- Left: Map Container -->
                    <div id="export-map-canvas" style="width: 1150px; height: 1010px; border: 2px solid black; position: relative; background: #eaeaea;"></div>
                    
                    <!-- Right: Panel -->
                    <div style="width: 380px; height: 1010px; border: 2px solid black; display: flex; flex-direction: column; justify-content: space-between; padding: 15px; box-sizing: border-box; background: white;">
                        
                        <!-- Ministry logo and header -->
                        <div style="border: 1px solid black; padding: 10px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 160px; box-sizing: border-box;">
                            <svg width="45" height="45" viewBox="0 0 100 100" style="margin-bottom: 6px;">
                                <circle cx="50" cy="50" r="45" fill="#FFD700" stroke="#003399" stroke-width="2"/>
                                <circle cx="50" cy="50" r="28" fill="none" stroke="#003399" stroke-width="6"/>
                                <circle cx="50" cy="50" r="10" fill="#FFD700" stroke="#003399" stroke-width="6"/>
                                <path d="M 50,15 L 50,35 M 50,65 L 50,85 M 15,50 L 35,50 M 65,50 L 85,50 M 25,25 L 39,39 M 61,61 L 75,75 M 25,75 L 39,61 M 61,39 L 75,25" stroke="#003399" stroke-width="6" stroke-linecap="round"/>
                            </svg>
                            <div style="font-weight: bold; font-size: 11px; line-height: 1.2;">KEMENTERIAN PEKERJAAN UMUM</div>
                            <div style="font-size: 9px; font-weight: bold; margin-top: 4px; line-height: 1.2; text-transform: uppercase;">DIREKTORAT JENDERAL PRASARANA STRATEGIS</div>
                            <div style="font-size: 8px; color: #555; margin-top: 2px; line-height: 1.2; text-transform: uppercase;">SATUAN KERJA PELAKSANAAN PRASARANA STRATEGIS RIAU</div>
                        </div>
                        
                        <!-- Compass and Scale -->
                        <div style="border: 1px solid black; padding: 10px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 180px; box-sizing: border-box; margin-top: 10px;">
                            <svg width="60" height="60" viewBox="0 0 100 100" style="margin-bottom: 8px;">
                                <circle cx="50" cy="50" r="45" fill="none" stroke="black" stroke-width="2"/>
                                <line x1="50" y1="5" x2="50" y2="95" stroke="black" stroke-dasharray="2,2"/>
                                <line x1="5" y1="50" x2="95" y2="50" stroke="black" stroke-dasharray="2,2"/>
                                <polygon points="50,10 57,45 50,40 43,45" fill="black" stroke="black"/>
                                <polygon points="50,90 57,55 50,60 43,55" fill="grey" stroke="black"/>
                                <text x="50" y="8" font-family="Arial" font-size="10" font-weight="bold" text-anchor="middle">N</text>
                                <text x="92" y="53" font-family="Arial" font-size="10" font-weight="bold" text-anchor="middle">E</text>
                                <text x="50" y="98" font-family="Arial" font-size="10" font-weight="bold" text-anchor="middle">S</text>
                                <text x="8" y="53" font-family="Arial" font-size="10" font-weight="bold" text-anchor="middle">W</text>
                            </svg>
                            <div style="font-weight: bold; font-size: 11px; margin-bottom: 4px;">SKALA KABUPATEN/PROVINSI</div>
                            <div style="display: flex; flex-direction: column; align-items: center; width: 100%;">
                                <div style="display: flex; justify-content: space-between; width: 150px; font-size: 9px; font-weight: bold;">
                                    <span>20</span>
                                    <span>10</span>
                                    <span>0</span>
                                    <span>20 KM</span>
                                </div>
                                <div style="width: 150px; height: 10px; border: 1px solid black; display: flex; overflow: hidden; margin-top: 2px;">
                                    <div style="width: 25%; background: black;"></div>
                                    <div style="width: 25%; background: white;"></div>
                                    <div style="width: 25%; background: black;"></div>
                                    <div style="width: 25%; background: white;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Legend -->
                        <div style="border: 1px solid black; padding: 12px; margin-top: 10px; display: flex; flex-direction: column; justify-content: flex-start; height: 210px; box-sizing: border-box;">
                            <div style="font-weight: bold; font-size: 12px; border-bottom: 2px solid black; padding-bottom: 4px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px;">Legenda</div>
                            <div style="display: flex; justify-content: space-between; gap: 10px;">
                                <div style="width: 49%; display: flex; flex-direction: column; gap: 5px;">
                                    ${col1Html}
                                </div>
                                <div style="width: 49%; display: flex; flex-direction: column; gap: 5px;">
                                    ${col2Html}
                                </div>
                            </div>
                        </div>

                        <!-- Inset Map -->
                        <div style="border: 1px solid black; padding: 5px; margin-top: 10px; text-align: center; height: 190px; box-sizing: border-box; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #f0f8ff; overflow: hidden;">
                            <div id="export-inset-canvas" style="width: 220px; height: 150px; border: 1px solid black; background: #eaeaea; position: relative;"></div>
                            <div style="font-size: 8px; font-weight: bold; margin-top: 3px; text-transform: uppercase;">Peta Indeks Provinsi Riau</div>
                        </div>

                        <!-- Signatures -->
                        <div style="display: flex; justify-content: space-between; margin-top: 10px; font-size: 8px; height: 120px; box-sizing: border-box;">
                            <div style="border: 1px solid black; width: 48%; padding: 8px; box-sizing: border-box; display: flex; flex-direction: column; justify-content: space-between; text-align: center;">
                                <div style="font-weight: bold; border-bottom: 1px solid black; padding-bottom: 2px; margin-bottom: 4px; text-transform: uppercase;">Divalidasi Oleh:</div>
                                <div style="font-size: 7px; color: #444; font-weight: bold;">KEPALA SATKER PPS RIAU</div>
                                <div style="font-weight: bold; margin-top: 25px; text-decoration: underline; font-size: 8px;">MUHAMMAD YUDI PRASETYA, S.T.</div>
                            </div>
                            <div style="border: 1px solid black; width: 48%; padding: 8px; box-sizing: border-box; display: flex; flex-direction: column; justify-content: space-between; text-align: center;">
                                <div style="font-weight: bold; border-bottom: 1px solid black; padding-bottom: 2px; margin-bottom: 4px; text-transform: uppercase;">Dibuat Oleh:</div>
                                <div style="font-size: 7px; color: #444; font-weight: bold;">STAF SATKER PPS RIAU</div>
                                <div style="font-weight: bold; margin-top: 25px; text-decoration: underline; font-size: 8px;">MUHAMMAD SYAHRIDWAN, S.T.</div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(exportContainer);

            // Initialize temp Leaflet map for main map export
            const exportMap = L.map('export-map-canvas', {
                zoomControl: false,
                attributionControl: false,
                fadeAnimation: false,
                zoomAnimation: false
            });

            // Satellite base layer
            L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 19
            }).addTo(exportMap);

            // Initialize mini inset map using Google Maps styling
            const insetMap = L.map('export-inset-canvas', {
                zoomControl: false,
                attributionControl: false,
                dragging: false,
                touchZoom: false,
                doubleClickZoom: false,
                scrollWheelZoom: false,
                boxZoom: false,
                keyboard: false,
                fadeAnimation: false,
                zoomAnimation: false
            }).setView([-0.51544, 101.44415], 6);

            L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
                maxZoom: 20
            }).addTo(insetMap);

            // Copy/draw Riau boundaries from main map boundaryLayer to insetMap (shading Riau)
            const tempBoundaryGroup = L.featureGroup().addTo(insetMap);
            boundaryLayer.eachLayer((layer) => {
                L.geoJSON(layer.toGeoJSON(), {
                    style: {
                        color: 'red',
                        weight: 1.5,
                        fillColor: 'red',
                        fillOpacity: 0.25
                    }
                }).addTo(tempBoundaryGroup);
            });

            if (tempBoundaryGroup.getLayers().length > 0) {
                insetMap.fitBounds(tempBoundaryGroup.getBounds(), { padding: [5, 5] });
            }

            // Draw a red circle marker for each active school location on the inset map
            activeMarkers.forEach((item) => {
                const lat = Number(item.latitude);
                const lng = Number(item.longitude);
                if (Number.isFinite(lat) && Number.isFinite(lng)) {
                    L.circleMarker([lat, lng], {
                        color: 'red',
                        fillColor: 'red',
                        fillOpacity: 0.8,
                        radius: 2.5,
                        weight: 1
                    }).addTo(insetMap);
                }
            });

            // Copy markers (using L.divIcon for html2canvas compatibility)
            const tempMarkerLayer = L.layerGroup().addTo(exportMap);
            const bounds = [];
            activeMarkers.forEach((item, i) => {
                const lat = Number(item.latitude);
                const lng = Number(item.longitude);
                if (Number.isFinite(lat) && Number.isFinite(lng)) {
                    L.marker([lat, lng], {
                        icon: L.divIcon({
                            className: 'custom-export-pin',
                            html: `<div style="width: 20px; height: 20px; background-color: ${getMarkerColor(item.survey_klasifikasi_kerusakan)}; border: 2px solid white; border-radius: 50%; box-shadow: 0 0 5px rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; color: white; font-family: Arial; font-size: 10px; font-weight: bold;">${i + 1}</div>`,
                            iconSize: [20, 20],
                            iconAnchor: [10, 10]
                        })
                    }).addTo(tempMarkerLayer);
                    bounds.push([lat, lng]);
                }
            });

            // Focus on markers or default Riau center
            if (bounds.length > 0) {
                exportMap.fitBounds(bounds, { padding: [50, 50] });
            } else {
                exportMap.setView([-0.51544, 101.44415], 8);
            }

            // Wait for tiles to load
            await new Promise(r => setTimeout(r, 2000));

            // Capture Leaflet map canvas
            const mapCanvas = await html2canvas(document.getElementById('export-map-canvas'), {
                useCORS: true,
                allowTaint: true,
                scale: 1.5
            });

            // Capture Inset Map canvas
            const insetCanvas = await html2canvas(document.getElementById('export-inset-canvas'), {
                useCORS: true,
                allowTaint: true,
                scale: 1.5
            });

            // Replace Leaflet Inset map with captured image
            const insetImg = document.createElement('img');
            insetImg.src = insetCanvas.toDataURL('image/jpeg');
            insetImg.style.width = '100%';
            insetImg.style.height = '100%';
            
            const insetCanvasEl = document.getElementById('export-inset-canvas');
            insetCanvasEl.innerHTML = '';
            insetCanvasEl.appendChild(insetImg);

            // Replace Leaflet map in template with captured image
            const mapImg = document.createElement('img');
            mapImg.src = mapCanvas.toDataURL('image/jpeg');
            mapImg.style.width = '100%';
            mapImg.style.height = '100%';
            
            const mapCanvasEl = document.getElementById('export-map-canvas');
            mapCanvasEl.innerHTML = '';
            mapCanvasEl.appendChild(mapImg);

            // Add coordinates grid labels overlays to the map img container
            const gridOverlay = document.createElement('div');
            gridOverlay.style.position = 'absolute';
            gridOverlay.style.top = '0';
            gridOverlay.style.left = '0';
            gridOverlay.style.width = '100%';
            gridOverlay.style.height = '100%';
            gridOverlay.style.pointerEvents = 'none';
            gridOverlay.style.border = '1px solid black';
            gridOverlay.style.boxSizing = 'border-box';
            
            const center = exportMap.getCenter();
            const mapLat = center.lat;
            const mapLng = center.lng;
            
            const latStr25 = (mapLat + 0.4).toFixed(4);
            const latStr50 = mapLat.toFixed(4);
            const latStr75 = (mapLat - 0.4).toFixed(4);
            
            const lngStr25 = (mapLng - 0.4).toFixed(4);
            const lngStr50 = mapLng.toFixed(4);
            const lngStr75 = (mapLng + 0.4).toFixed(4);

            gridOverlay.innerHTML = `
                <!-- Horizontal Grid lines -->
                <div style="position: absolute; left: 0; top: 25%; width: 100%; border-top: 1px dashed rgba(255,255,255,0.4);"></div>
                <div style="position: absolute; left: 0; top: 50%; width: 100%; border-top: 1px dashed rgba(255,255,255,0.4);"></div>
                <div style="position: absolute; left: 0; top: 75%; width: 100%; border-top: 1px dashed rgba(255,255,255,0.4);"></div>
                <!-- Vertical Grid lines -->
                <div style="position: absolute; top: 0; left: 25%; height: 100%; border-left: 1px dashed rgba(255,255,255,0.4);"></div>
                <div style="position: absolute; top: 0; left: 50%; height: 100%; border-left: 1px dashed rgba(255,255,255,0.4);"></div>
                <div style="position: absolute; top: 0; left: 75%; height: 100%; border-left: 1px dashed rgba(255,255,255,0.4);"></div>
                
                <!-- Grid Labels (approximate lat/lng labels) -->
                <div style="position: absolute; top: 5px; left: 25%; font-size: 9px; transform: translateX(-50%); font-weight: bold; background: rgba(0,0,0,0.6); color: white; padding: 1px 3px; border-radius: 2px; font-family: Arial;">${lngStr25}°E</div>
                <div style="position: absolute; top: 5px; left: 50%; font-size: 9px; transform: translateX(-50%); font-weight: bold; background: rgba(0,0,0,0.6); color: white; padding: 1px 3px; border-radius: 2px; font-family: Arial;">${lngStr50}°E</div>
                <div style="position: absolute; top: 5px; left: 75%; font-size: 9px; transform: translateX(-50%); font-weight: bold; background: rgba(0,0,0,0.6); color: white; padding: 1px 3px; border-radius: 2px; font-family: Arial;">${lngStr75}°E</div>
                
                <div style="position: absolute; left: 5px; top: 25%; font-size: 9px; transform: translateY(-50%); font-weight: bold; background: rgba(0,0,0,0.6); color: white; padding: 1px 3px; border-radius: 2px; font-family: Arial;">${latStr25}°N</div>
                <div style="position: absolute; left: 5px; top: 50%; font-size: 9px; transform: translateY(-50%); font-weight: bold; background: rgba(0,0,0,0.6); color: white; padding: 1px 3px; border-radius: 2px; font-family: Arial;">${latStr50}°N</div>
                <div style="position: absolute; left: 5px; top: 75%; font-size: 9px; transform: translateY(-50%); font-weight: bold; background: rgba(0,0,0,0.6); color: white; padding: 1px 3px; border-radius: 2px; font-family: Arial;">${latStr75}°N</div>
            `;
            mapCanvasEl.appendChild(gridOverlay);

            // Capture entire layout container
            const layoutCanvas = await html2canvas(exportContainer, {
                useCORS: true,
                allowTaint: true,
                scale: 2
            });

            // Generate PDF
            const imgData = layoutCanvas.toDataURL('image/jpeg', 0.95);
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF({
                orientation: 'landscape',
                unit: 'px',
                format: 'a3'
            });

            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = pdf.internal.pageSize.getHeight();
            pdf.addImage(imgData, 'JPEG', 0, 0, pdfWidth, pdfHeight);
            
            const blobUrl = pdf.output('bloburl');
            window.open(blobUrl, '_blank');

            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Peta sebaran berhasil dibuka di tab baru.',
                timer: 2000,
                showConfirmButton: false
            });

        } catch (error) {
            console.error(error);
            Swal.fire('Error', 'Gagal mengeksport peta: ' + error.message, 'error');
        } finally {
            // Clean up DOM
            const el = document.getElementById('temp-export-container');
            if (el) el.remove();
        }
    }

    const exportBtnMain = document.getElementById('exportMapPdfBtnMain');
    if (exportBtnMain) {
        exportBtnMain.addEventListener('click', exportMainMapPdf);
    }

    applyMapScript(mapScript);
    loadRiauBoundary();
    loadKecamatanOptions(inputs.kabupaten.value, '*').then(loadMapData);
})();
</script>
<?= $this->endSection(); ?>
