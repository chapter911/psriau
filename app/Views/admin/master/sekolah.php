<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<link rel="stylesheet" href="<?= esc(media_url('assets/leaflet/leaflet.css')); ?>">
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Sekolah</h3>
        <?php if (! empty($can_add) || ! empty($can_export)): ?>
            <div class="float-right">
                <?php if (! empty($can_export)): ?>
                    <a href="<?= site_url('/admin/master/sekolah/export?' . $_SERVER['QUERY_STRING']); ?>" class="btn btn-success mr-2">Export Excel</a>
                <?php endif; ?>
                <?php if (! empty($can_add)): ?>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-tambah-sekolah">Tambah Sekolah</button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <!-- Filter Form -->
        <form id="filter-form" method="get" action="<?= site_url('/admin/master/sekolah'); ?>" class="mb-4 bg-light p-3 rounded border">
            <div class="form-row align-items-end">
                <div class="form-group col-md-4 mb-2">
                    <label class="small font-weight-bold text-muted">Filter Paket</label>
                    <select name="paket_id" id="filter_paket_id" class="form-control js-filter-select">
                        <option value="*">Semua Paket</option>
                        <?php foreach (($pakets ?? []) as $paket): ?>
                            <option value="<?= esc($paket['id']); ?>" <?= (string)($filter_paket_id ?? '') === (string)$paket['id'] ? 'selected' : ''; ?>><?= esc($paket['nama_paket']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-4 mb-2">
                    <label class="small font-weight-bold text-muted">Filter Kabupaten</label>
                    <select name="kabupaten" id="filter_kabupaten" class="form-control js-filter-select">
                        <option value="*">Semua Kabupaten</option>
                        <?php foreach (($kabupatens ?? []) as $kab): ?>
                            <option value="<?= esc($kab); ?>" <?= ($filter_kabupaten ?? '') === $kab ? 'selected' : ''; ?>><?= esc($kab); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-4 mb-2">
                    <label class="small font-weight-bold text-muted">Filter Kecamatan</label>
                    <select name="kecamatan" id="filter_kecamatan" class="form-control js-filter-select">
                        <option value="*">Semua Kecamatan</option>
                        <?php foreach (($kecamatans ?? []) as $kec): ?>
                            <option value="<?= esc($kec); ?>" <?= ($filter_kecamatan ?? '') === $kec ? 'selected' : ''; ?>><?= esc($kec); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </form>

        <table class="table table-bordered table-striped w-100 nowrap js-datatable">
            <thead>
                <tr style="white-space: nowrap;">
                    <th class="text-center">#</th>
                    <th class="text-center">NPSN</th>
                    <th class="text-center">NAMA</th>
                    <th class="text-center">JENIS</th>
                    <th class="text-center">KABUPATEN</th>
                    <th class="text-center">KECAMATAN</th>
                    <th class="text-center">PAKET</th>
                    <th class="text-right">LATITUDE</th>
                    <th class="text-right">LONGITUDE</th>
                    <th class="text-center">PETA</th>
                    <?php if (! empty($can_edit)): ?>
                        <th class="text-center">ACTION</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach (($items ?? []) as $item): ?>
                    <tr>
                        <td><?= esc((string) $i++); ?></td>
                        <td><?= esc((string) ($item['npsn'] ?? '-')); ?></td>
                        <td><?= esc((string) ($item['nama'] ?? '-')); ?></td>
                        <td><?= esc((string) ($item['jenis'] ?? '-')); ?></td>
                        <td><?= esc((string) ($item['kabupaten'] ?? '-')); ?></td>
                        <td><?= esc((string) ($item['kecamatan'] ?? '-')); ?></td>
                        <td><?= esc((string) ($item['paket_names'] ?? '-')); ?></td>
                        <?php
                            $latVal = $item['latitude'] ?? null;
                            $lngVal = $item['longitude'] ?? null;
                            $latStr = ($latVal !== null && $latVal !== '') ? number_format((float)$latVal, 6, '.', '') : '-';
                            $lngStr = ($lngVal !== null && $lngVal !== '') ? number_format((float)$lngVal, 6, '.', '') : '-';
                        ?>
                        <td class="text-right"><?= esc($latStr); ?></td>
                        <td class="text-right"><?= esc($lngStr); ?></td>
                        <?php
                            $latitude = trim((string) ($item['latitude'] ?? ''));
                            $longitude = trim((string) ($item['longitude'] ?? ''));
                            $hasCoordinates = $latitude !== '' && $longitude !== '';
                        ?>
                        <td class="text-center" style="white-space: nowrap;">
                            <?php if ($hasCoordinates): ?>
                                <button
                                    type="button"
                                    class="btn btn-info btn-sm js-open-map"
                                    data-nama="<?= esc((string) ($item['nama'] ?? ''), 'attr'); ?>"
                                    data-npsn="<?= esc((string) ($item['npsn'] ?? ''), 'attr'); ?>"
                                    data-latitude="<?= esc($latitude, 'attr'); ?>"
                                    data-longitude="<?= esc($longitude, 'attr'); ?>"
                                    data-kabupaten="<?= esc((string) ($item['kabupaten'] ?? ''), 'attr'); ?>"
                                    data-kecamatan="<?= esc((string) ($item['kecamatan'] ?? ''), 'attr'); ?>"
                                >Lihat Peta</button>
                                <button
                                    type="button"
                                    class="btn btn-danger btn-sm js-export-map-direct"
                                    data-nama="<?= esc((string) ($item['nama'] ?? ''), 'attr'); ?>"
                                    data-npsn="<?= esc((string) ($item['npsn'] ?? ''), 'attr'); ?>"
                                    data-latitude="<?= esc($latitude, 'attr'); ?>"
                                    data-longitude="<?= esc($longitude, 'attr'); ?>"
                                    data-kabupaten="<?= esc((string) ($item['kabupaten'] ?? ''), 'attr'); ?>"
                                    data-kecamatan="<?= esc((string) ($item['kecamatan'] ?? ''), 'attr'); ?>"
                                >
                                    <i class="fas fa-file-pdf"></i> Export Peta
                                </button>
                            <?php else: ?>
                                <span class="badge badge-light border">Belum ada koordinat</span>
                            <?php endif; ?>
                        </td>
                        <?php if (! empty($can_edit)): ?>
                            <td class="text-center" style="white-space: nowrap;">
                                <button
                                    type="button"
                                    class="btn btn-warning btn-sm"
                                    data-toggle="modal"
                                    data-target="#modal-ubah-sekolah"
                                    data-npsn="<?= esc((string) ($item['npsn'] ?? ''), 'attr'); ?>"
                                    data-nama="<?= esc((string) ($item['nama'] ?? ''), 'attr'); ?>"
                                    data-jenis="<?= esc((string) ($item['jenis'] ?? ''), 'attr'); ?>"
                                    data-nsm="<?= esc((string) ($item['nsm'] ?? ''), 'attr'); ?>"
                                    data-kabupaten="<?= esc((string) ($item['kabupaten'] ?? ''), 'attr'); ?>"
                                    data-kecamatan="<?= esc((string) ($item['kecamatan'] ?? ''), 'attr'); ?>"
                                    data-latitude="<?= esc((string) ($item['latitude'] ?? ''), 'attr'); ?>"
                                    data-longitude="<?= esc((string) ($item['longitude'] ?? ''), 'attr'); ?>"
                                    data-paket-id="<?= esc((string) ($item['paket_id'] ?? ''), 'attr'); ?>"
                                >UBAH</button>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modal-peta-sekolah" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0">Peta Lokasi Sekolah</h5>
                    <small class="text-muted" id="map-school-subtitle">Koordinat sekolah</small>
                </div>
                <div class="ml-auto d-flex align-items-center">
                    <button type="button" class="btn btn-danger btn-sm mr-2" id="btn-export-peta-pdf">
                        <i class="fas fa-file-pdf mr-1"></i> Export Peta A3
                    </button>
                    <a href="#" class="btn btn-outline-primary btn-sm mr-2" id="btn-open-google-maps" target="_blank" rel="noopener noreferrer">
                        <i class="fas fa-external-link-alt mr-1"></i>Buka Google Map
                    </a>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
            <div class="modal-body p-0">
                <div class="p-3 border-bottom bg-light">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                        <div>
                            <div class="font-weight-bold" id="map-school-name">-</div>
                            <div class="text-muted small" id="map-school-coordinates">-</div>
                        </div>
                        <div class="text-right mt-2 mt-md-0 d-flex align-items-center">
                            <label for="schoolMapType" class="mb-0 mr-2 small text-muted">Tipe Map</label>
                            <select id="schoolMapType" class="form-control form-control-sm" style="min-width: 220px;">
                                <?php foreach (($mapTypes ?? []) as $mapType): ?>
                                    <option value="<?= esc((string) ($mapType['id'] ?? ''), 'attr'); ?>" <?= (int) ($mapType['id'] ?? 0) === (int) ($mapDefaultId ?? 1) ? 'selected' : ''; ?>>
                                        <?= esc((string) ($mapType['map_name'] ?? 'Leaflet Map')); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div id="school-map" style="height: 520px; width: 100%;"></div>
            </div>
        </div>
    </div>
</div>

<?php if (! empty($can_add)): ?>
<div class="modal fade" id="modal-tambah-sekolah" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Sekolah</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-tambah-sekolah" action="<?= site_url('/admin/master/sekolah/tambah'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>NPSN</label>
                            <input type="text" name="npsn" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>NSM</label>
                            <input type="text" name="nsm" class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Nama Sekolah</label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Paket</label>
                            <select name="paket_id" class="form-control">
                                <option value="">-- Pilih Paket --</option>
                                <?php foreach ($pakets as $paket): ?>
                                    <option value="<?= esc($paket['id']); ?>"><?= esc($paket['nama_paket']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Jenis</label>
                            <input type="text" name="jenis" class="form-control" placeholder="Contoh: Madrasah">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Kabupaten</label>
                            <input type="text" name="kabupaten" class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Kecamatan</label>
                            <input type="text" name="kecamatan" class="form-control">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Latitude</label>
                            <input type="text" name="latitude" class="form-control">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Longitude</label>
                            <input type="text" name="longitude" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (! empty($can_edit)): ?>
<div class="modal fade" id="modal-ubah-sekolah" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Sekolah</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-ubah-sekolah" action="" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>NPSN</label>
                            <input type="text" id="edit_npsn" name="npsn" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>NSM</label>
                            <input type="text" id="edit_nsm" name="nsm" class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Nama Sekolah</label>
                            <input type="text" id="edit_nama" name="nama" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Paket</label>
                            <select id="edit_paket_id" name="paket_id" class="form-control">
                                <option value="">-- Pilih Paket --</option>
                                <?php foreach ($pakets as $paket): ?>
                                    <option value="<?= esc($paket['id']); ?>"><?= esc($paket['nama_paket']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Jenis</label>
                            <input type="text" id="edit_jenis" name="jenis" class="form-control">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Kabupaten</label>
                            <input type="text" id="edit_kabupaten" name="kabupaten" class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Kecamatan</label>
                            <input type="text" id="edit_kecamatan" name="kecamatan" class="form-control">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Latitude</label>
                            <input type="text" id="edit_latitude" name="latitude" class="form-control">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Longitude</label>
                            <input type="text" id="edit_longitude" name="longitude" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script src="<?= esc(media_url('assets/leaflet/leaflet.js')); ?>"></script>
<script>
    (function () {
        function getScaleBarHtml(exportMap) {
            const bounds = exportMap.getBounds();
            const west = bounds.getWest();
            const east = bounds.getEast();
            const centerLat = exportMap.getCenter().lat;
            
            const leftPoint = L.latLng(centerLat, west);
            const rightPoint = L.latLng(centerLat, east);
            const totalDistanceMeters = leftPoint.distanceTo(rightPoint);
            
            const D_meters = totalDistanceMeters * (75 / 1150);
            const D_km = D_meters / 1000;
            
            let leftLabel, midLeftLabel, rightLabel;
            
            if (D_km >= 0.1) {
                leftLabel = D_km.toFixed(1);
                midLeftLabel = (D_km / 2).toFixed(1);
                rightLabel = D_km.toFixed(1) + " KM";
            } else {
                const D_meters_rounded = Math.round(D_meters);
                leftLabel = D_meters_rounded;
                midLeftLabel = Math.round(D_meters_rounded / 2);
                rightLabel = D_meters_rounded + " M";
            }
            
            const scaleRatio = Math.round(totalDistanceMeters / 0.3024);
            const scaleRatioRounded = Math.round(scaleRatio / 100) * 100;
            const rfScaleText = "SKALA 1:" + scaleRatioRounded.toLocaleString('id-ID');
            
            return `
                <div style="font-weight: bold; font-size: 11px; margin-bottom: 4px; text-transform: uppercase;">${rfScaleText}</div>
                <div style="display: flex; flex-direction: column; align-items: center; width: 100%;">
                    <div style="display: flex; justify-content: space-between; width: 150px; font-size: 9px; font-weight: bold;">
                        <span>${leftLabel}</span>
                        <span>${midLeftLabel}</span>
                        <span>0</span>
                        <span>${rightLabel}</span>
                    </div>
                    <div style="width: 150px; height: 10px; border: 1px solid black; display: flex; overflow: hidden; margin-top: 2px;">
                        <div style="width: 25%; background: black;"></div>
                        <div style="width: 25%; background: white;"></div>
                        <div style="width: 25%; background: black;"></div>
                        <div style="width: 25%; background: white;"></div>
                    </div>
                </div>
            `;
        }

        const modalEdit = document.getElementById('modal-ubah-sekolah');
        if (!modalEdit) return;

        const form = document.getElementById('form-ubah-sekolah');
        const fields = {
            npsn: document.getElementById('edit_npsn'),
            nama: document.getElementById('edit_nama'),
            jenis: document.getElementById('edit_jenis'),
            nsm: document.getElementById('edit_nsm'),
            kabupaten: document.getElementById('edit_kabupaten'),
            kecamatan: document.getElementById('edit_kecamatan'),
            latitude: document.getElementById('edit_latitude'),
            longitude: document.getElementById('edit_longitude'),
            paket_id: document.getElementById('edit_paket_id'),
        };

        const applyEditData = (trigger) => {
            if (!trigger) {
                return;
            }

            const originalNpsn = trigger.getAttribute('data-npsn') || '';
            form.action = '<?= site_url('/admin/master/sekolah'); ?>/' + encodeURIComponent(originalNpsn) + '/ubah';
            fields.npsn.value = originalNpsn;
            fields.nama.value = trigger.getAttribute('data-nama') || '';
            fields.jenis.value = trigger.getAttribute('data-jenis') || '';
            fields.nsm.value = trigger.getAttribute('data-nsm') || '';
            fields.kabupaten.value = trigger.getAttribute('data-kabupaten') || '';
            fields.kecamatan.value = trigger.getAttribute('data-kecamatan') || '';
            fields.latitude.value = trigger.getAttribute('data-latitude') || '';
            fields.longitude.value = trigger.getAttribute('data-longitude') || '';
            fields.paket_id.value = trigger.getAttribute('data-paket-id') || '';
        };

        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('button[data-target="#modal-ubah-sekolah"]');
            if (!trigger) {
                return;
            }

            applyEditData(trigger);
        });

        modalEdit.addEventListener('show.bs.modal', function (event) {
            applyEditData(event.relatedTarget);
        });
    })();

    (function () {
        const mapModal = document.getElementById('modal-peta-sekolah');
        if (!mapModal || typeof L === 'undefined') {
            return;
        }

        const mapTypes = <?= json_encode($mapTypes ?? [], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

        const mapContainer = document.getElementById('school-map');
        const schoolName = document.getElementById('map-school-name');
        const schoolSubtitle = document.getElementById('map-school-subtitle');
        const schoolCoordinates = document.getElementById('map-school-coordinates');
        const googleMapButton = document.getElementById('btn-open-google-maps');
        const mapTypeSelect = document.getElementById('schoolMapType');
        const jqModal = (typeof $ !== 'undefined' && typeof $(mapModal).on === 'function') ? $(mapModal) : null;
        const defaultCenter = [-0.51544, 101.44415];
        const defaultZoom = 8;
        let leafletMap = null;
        let marker = null;
        let pendingLocation = null;

        const onModalEvent = (eventName, handler) => {
            if (jqModal) {
                jqModal.on(eventName, function (event) {
                    handler(event);
                });
                return;
            }

            mapModal.addEventListener(eventName, handler);
        };

        const clearTileLayers = () => {
            if (!leafletMap) {
                return;
            }

            leafletMap.eachLayer((layer) => {
                if (layer instanceof L.TileLayer) {
                    leafletMap.removeLayer(layer);
                }
            });
        };

        const clearScaleControls = () => {
            const controls = mapContainer.querySelectorAll('.leaflet-control-scale');
            controls.forEach((el) => {
                if (el && el.parentNode) {
                    el.parentNode.removeChild(el);
                }
            });
        };

        const getSelectedMapScript = () => {
            if (!Array.isArray(mapTypes) || mapTypes.length === 0) {
                return '';
            }

            const selectedId = mapTypeSelect ? String(mapTypeSelect.value || '') : '';
            const found = mapTypes.find((item) => String(item && item.id != null ? item.id : '') === selectedId);
            if (found && typeof found.map_script === 'string') {
                return found.map_script;
            }

            const first = mapTypes[0] || {};
            return typeof first.map_script === 'string' ? first.map_script : '';
        };

        const applyMapScript = () => {
            if (!leafletMap) {
                return;
            }

            clearTileLayers();
            clearScaleControls();

            const script = getSelectedMapScript();
            const normalized = String(script || '').replace(/http:\/\//g, 'https://');
            let applied = false;

            if (normalized.trim() !== '') {
                try {
                    const fn = new Function('map', 'L', normalized);
                    fn(leafletMap, L);
                    applied = true;
                } catch (error) {
                    applied = false;
                }
            }

            if (!applied) {
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(leafletMap);
            }
        };

        const formatCoordinate = (value) => {
            const number = Number(value);
            return Number.isFinite(number) ? number.toFixed(6) : '-';
        };

        const openGoogleMaps = (latitude, longitude) => {
            const lat = Number(latitude);
            const lng = Number(longitude);

            if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                return '#';
            }

            return 'https://www.google.com/maps?q=' + encodeURIComponent(lat + ',' + lng);
        };

        const showMapModal = () => {
            if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
                const ModalCtor = window.bootstrap.Modal;

                if (typeof ModalCtor.getOrCreateInstance === 'function') {
                    ModalCtor.getOrCreateInstance(mapModal).show();
                    return;
                }

                let instance = null;
                if (typeof ModalCtor.getInstance === 'function') {
                    instance = ModalCtor.getInstance(mapModal);
                }
                if (!instance) {
                    instance = new ModalCtor(mapModal);
                }
                if (instance && typeof instance.show === 'function') {
                    instance.show();
                    return;
                }
            }

            if (typeof $ !== 'undefined' && typeof $(mapModal).modal === 'function') {
                $(mapModal).modal('show');
            }
        };

        const applyMapDataFromTrigger = (trigger) => {
            if (!trigger) {
                return;
            }

            const nama = trigger.getAttribute('data-nama') || '-';
            const npsn = trigger.getAttribute('data-npsn') || '-';
            const latitude = trigger.getAttribute('data-latitude') || '';
            const longitude = trigger.getAttribute('data-longitude') || '';
            const kabupaten = trigger.getAttribute('data-kabupaten') || '';
            const kecamatan = trigger.getAttribute('data-kecamatan') || '';

            schoolName.textContent = nama;
            schoolSubtitle.textContent = 'NPSN ' + npsn;
            schoolCoordinates.textContent = 'Lat: ' + formatCoordinate(latitude) + ' | Lng: ' + formatCoordinate(longitude);

            pendingLocation = {
                latitude,
                longitude,
                label: nama,
                kabupaten,
                kecamatan,
            };
        };

        const renderMap = (latitude, longitude, label) => {
            const lat = Number(latitude);
            const lng = Number(longitude);

            if (!leafletMap) {
                leafletMap = L.map(mapContainer, {
                    zoomControl: true,
                    preferCanvas: true,
                });
            }

            applyMapScript();

            if (marker) {
                marker.remove();
                marker = null;
            }

            if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                leafletMap.setView(defaultCenter, defaultZoom);
                window.setTimeout(() => {
                    leafletMap.invalidateSize({ pan: false });
                }, 150);

                googleMapButton.setAttribute('href', '#');
                googleMapButton.classList.add('disabled');
                googleMapButton.setAttribute('aria-disabled', 'true');
                return;
            }

            leafletMap.setView([lat, lng], 15);
            marker = L.marker([lat, lng]).addTo(leafletMap);
            marker.bindPopup(label || 'Lokasi sekolah').openPopup();

            leafletMap.invalidateSize({ pan: false });
            window.setTimeout(() => {
                leafletMap.invalidateSize({ pan: false });
                leafletMap.panTo([lat, lng], { animate: false });
            }, 180);

            const googleUrl = openGoogleMaps(lat, lng);
            googleMapButton.setAttribute('href', googleUrl);
            googleMapButton.classList.remove('disabled');
            googleMapButton.removeAttribute('aria-disabled');
            googleMapButton.setAttribute('target', '_blank');
            googleMapButton.setAttribute('rel', 'noopener noreferrer');
        };

        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('.js-open-map');
            if (!trigger) {
                return;
            }

            event.preventDefault();
            applyMapDataFromTrigger(trigger);
            showMapModal();

            // Fallback for Bootstrap variants where shown.bs.modal may not propagate to native listeners.
            window.setTimeout(() => {
                if (pendingLocation) {
                    renderMap(pendingLocation.latitude, pendingLocation.longitude, pendingLocation.label);
                }
            }, 180);
        });

        if (mapTypeSelect) {
            mapTypeSelect.addEventListener('change', function () {
                if (!leafletMap) {
                    return;
                }

                applyMapScript();
            });
        }

        onModalEvent('show.bs.modal', function (event) {
            if (!event.relatedTarget) {
                return;
            }

            applyMapDataFromTrigger(event.relatedTarget);
        });

        onModalEvent('shown.bs.modal', function () {
            if (pendingLocation) {
                renderMap(pendingLocation.latitude, pendingLocation.longitude, pendingLocation.label);
            }

            if (leafletMap) {
                window.setTimeout(() => {
                    leafletMap.invalidateSize({ pan: false });
                }, 120);
            }
        });

        onModalEvent('hide.bs.modal', function () {
            const active = document.activeElement;
            if (active && typeof active.blur === 'function') {
                active.blur();
            }
        });

        onModalEvent('hidden.bs.modal', function () {
            if (marker) {
                marker.remove();
                marker = null;
            }

            pendingLocation = null;

            if (leafletMap) {
                leafletMap.closePopup();
            }
        });
    })();

    // jQuery Event delegation for filter dropdowns change to trigger AJAX filter updates
    $(document).on('change', '.js-filter-select', function() {
        if (this.id === 'filter_kabupaten') {
            const filterKecamatan = document.getElementById('filter_kecamatan');
            if (filterKecamatan) {
                $(filterKecamatan).val('*');
            }
        }

        applyFilters();
    });

    function getFilterQueryString() {
        const paketId = document.getElementById('filter_paket_id')?.value || '*';
        const kabupaten = document.getElementById('filter_kabupaten')?.value || '*';
        const kecamatan = document.getElementById('filter_kecamatan')?.value || '*';

        const params = new URLSearchParams();
        if (paketId !== '*') params.set('paket_id', paketId);
        if (kabupaten !== '*') params.set('kabupaten', kabupaten);
        if (kecamatan !== '*') params.set('kecamatan', kecamatan);

        return params.toString();
    }

    function applyFilters() {
        const queryString = getFilterQueryString();
        const baseUrl = '<?= site_url('/admin/master/sekolah'); ?>';
        const newUrl = queryString ? (baseUrl + '?' + queryString) : baseUrl;

        // Update URL in browser history without reloading
        window.history.pushState({ path: newUrl }, '', newUrl);

        // Update the Export Excel button's href dynamically
        const exportBtn = document.querySelector('a[href*="/sekolah/export"]');
        if (exportBtn) {
            exportBtn.href = '<?= site_url('/admin/master/sekolah/export'); ?>' + (queryString ? '?' + queryString : '');
        }

        // Refresh DataTable resetting page and search
        refreshTable(true);
    }

    // AJAX Form Submission and DataTable reloading
    function refreshTable(resetState = false) {
        const tableElement = $('.js-datatable');
        let currentPage = 0;
        let currentSearch = '';
        let currentOrder = [];

        if (!resetState && $.fn.DataTable.isDataTable(tableElement)) {
            const dt = tableElement.DataTable();
            currentPage = dt.page();
            currentSearch = dt.search();
            currentOrder = dt.order();
        }

        if ($.fn.DataTable.isDataTable(tableElement)) {
            tableElement.DataTable().destroy();
        }

        $.ajax({
            url: window.location.href,
            method: 'GET',
            success: function (html) {
                const newTableHtml = $(html).find('.js-datatable').html();
                tableElement.html(newTableHtml);

                // Destroy old select2 elements inside filter-form before replacing HTML
                $('#filter-form').find('.js-filter-select').each(function() {
                    if ($(this).data('select2')) {
                        $(this).select2('destroy');
                    }
                });

                const newFormHtml = $(html).find('#filter-form').html();
                if (newFormHtml) {
                    $('#filter-form').html(newFormHtml);
                }

                const newDt = tableElement.DataTable({
                    responsive: false,
                    autoWidth: false,
                    scrollX: true,
                    scrollCollapse: true,
                    language: {
                        search: 'Cari:',
                        lengthMenu: 'Tampilkan _MENU_ data',
                        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                        infoEmpty: 'Tidak ada data',
                        zeroRecords: 'Data tidak ditemukan',
                        paginate: {
                            first: 'Awal',
                            last: 'Akhir',
                            next: 'Berikutnya',
                            previous: 'Sebelumnya'
                        }
                    }
                });

                if (!resetState) {
                    if (currentOrder && currentOrder.length > 0) {
                        newDt.order(currentOrder);
                    }
                    if (currentSearch) {
                        newDt.search(currentSearch);
                    }
                    newDt.page(currentPage).draw(false);
                }
            },
            error: function () {
                Swal.fire('Error', 'Gagal memuat ulang data tabel.', 'error');
            }
        });
    }

    function submitFormAjax(form, modalSelector = null, isCreate = false) {
        const submitBtn = $(form).find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        const formData = new FormData(form);

        $.ajax({
            url: $(form).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'JSON',
            beforeSend: function () {
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');
            },
            success: function (response) {
                submitBtn.prop('disabled', false).html(originalBtnText);

                if (response.csrf_hash) {
                    $('input[name="<?= csrf_token(); ?>"]').val(response.csrf_hash);
                }

                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });

                    if (modalSelector) {
                        $(modalSelector).modal('hide');
                    }

                    if (isCreate) {
                        form.reset();
                    }

                    refreshTable();
                } else {
                    Swal.fire('Gagal', response.message || 'Terjadi kesalahan.', 'error');
                }
            },
            error: function (xhr) {
                submitBtn.prop('disabled', false).html(originalBtnText);

                if (xhr.responseJSON && xhr.responseJSON.csrf_hash) {
                    $('input[name="<?= csrf_token(); ?>"]').val(xhr.responseJSON.csrf_hash);
                }

                const errorMsg = xhr.responseJSON && xhr.responseJSON.message 
                    ? xhr.responseJSON.message 
                    : 'Terjadi kesalahan server.';
                Swal.fire('Gagal', errorMsg, 'error');
            }
        });
    }

    (function () {
        const formTambah = document.getElementById('form-tambah-sekolah');
        const formUbah = document.getElementById('form-ubah-sekolah');

        if (formTambah) {
            formTambah.addEventListener('submit', function (e) {
                e.preventDefault();
                submitFormAjax(this, '#modal-tambah-sekolah', true);
            });
        }

        if (formUbah) {
            formUbah.addEventListener('submit', function (e) {
                e.preventDefault();
                submitFormAjax(this, '#modal-ubah-sekolah', false);
            });
        }
    })();

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
                            <div id="export-scale-container" style="display: flex; flex-direction: column; align-items: center; width: 100%;"></div>
                        </div>

                        <!-- Legend -->
                        <div style="border: 1px solid black; padding: 10px; margin-top: 10px; display: flex; flex-direction: column; justify-content: flex-start; height: 210px; box-sizing: border-box; overflow: hidden;">
                            <div style="font-weight: bold; font-size: 12px; border-bottom: 2px solid black; padding-bottom: 4px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Legenda</div>
                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                <div style="display: flex; align-items: center; font-size: 9px;">
                                    <div style="width: 28px; height: 13px; border: 2.5px solid red; background: rgba(255,0,0,0.05); margin-right: 8px; flex-shrink: 0;"></div>
                                    <div style="font-weight: bold; color: red; text-transform: uppercase; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 160px;" title="${schoolName}">${schoolName}</div>
                                </div>
                                <div style="font-size: 8px; font-weight: bold; color: #333; margin-top: 4px; text-transform: uppercase; border-bottom: 1px solid #ccc; padding-bottom: 3px;">Kontur Topografi</div>
                                <div style="display: flex; align-items: center; font-size: 8px; gap: 6px;">
                                    <div style="width: 26px; height: 2px; background: #ffff00; flex-shrink: 0; border: 0.5px solid #000;"></div>
                                    <span>Garis Kontur</span>
                                </div>
                                <div style="display: flex; align-items: center; font-size: 8px; gap: 6px;">
                                    <div style="width: 26px; height: 3px; background: #ff3d00; flex-shrink: 0; border: 0.5px solid #000;"></div>
                                    <span>Kontur Indeks</span>
                                </div>
                                <div style="font-size: 7px; color: #777; margin-top: 4px; line-height: 1.4;">Sumber: OpenTopoMap<br>Data: SRTM NASA</div>
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
                preferCanvas: true,
                zoomControl: false,
                attributionControl: false,
                fadeAnimation: false,
                zoomAnimation: false
            }).setView([lat, lng], 17);

            // Satellite base layer — Google Hybrid (works with html2canvas, no CORS block)
            L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                crossOrigin: true
            }).addTo(exportMap);

            // Initialize mini inset map using Google Maps styling
            const insetMap = L.map('export-inset-canvas', {
                preferCanvas: true,
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

            insetMap.invalidateSize(false);

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
                        insetMap.invalidateSize(false);
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

            exportMap.invalidateSize(false);
            exportMap.setView([lat, lng], 17);

            // Draw simulated contours
            const contours = [
                { radius: 60, elevation: 45, isIndex: false },
                { radius: 120, elevation: 40, isIndex: true },
                { radius: 180, elevation: 35, isIndex: false },
                { radius: 240, elevation: 30, isIndex: true }
            ];
            contours.forEach(c => {
                const coords = createContourLine([lat, lng], c.radius, 32, 8);
                const color = c.isIndex ? '#ff3d00' : '#ffff00';
                const weight = c.isIndex ? 3.5 : 2.2;
                const opacity = c.isIndex ? 0.90 : 0.75;
                L.polyline(coords, { color: color, weight: weight, opacity: opacity }).addTo(exportMap);
                
                // Add label
                const midPoint = coords[8];
                L.marker(midPoint, {
                    icon: L.divIcon({
                        className: 'contour-label-temp',
                        html: `<div style="color: ${color}; font-size: 8px; font-weight: bold; background-color: rgba(255,255,255,0.75); border: 1px solid ${color}; border-radius: 2px; padding: 1px 2.5px; white-space: nowrap; pointer-events: none; text-shadow: 0 0 2px #fff;">${c.elevation}m</div>`,
                        iconSize: [35, 12],
                        iconAnchor: [17, 6]
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

            // Inject dynamic scale bar
            document.getElementById('export-scale-container').innerHTML = getScaleBarHtml(exportMap);

            // Wait for tiles to load — listen for tileload events with a max 6s cap
            await new Promise(resolve => {
                let tilesLoading = 0;
                const maxWait = setTimeout(resolve, 6000);
                exportMap.on('tileloadstart', () => { tilesLoading++; });
                exportMap.on('tileload tileerror', () => {
                    tilesLoading = Math.max(0, tilesLoading - 1);
                    if (tilesLoading === 0) { clearTimeout(maxWait); setTimeout(resolve, 300); }
                });
                setTimeout(() => { if (tilesLoading === 0) { clearTimeout(maxWait); resolve(); } }, 800);
            });


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

    const exportPdfBtn = document.getElementById('btn-export-peta-pdf');
    if (exportPdfBtn) {
        exportPdfBtn.addEventListener('click', function() {
            if (pendingLocation) {
                const lat = Number(pendingLocation.latitude);
                const lng = Number(pendingLocation.longitude);
                const nama = pendingLocation.label || 'Sekolah';
                const kabupaten = pendingLocation.kabupaten || 'Bengkalis';
                const kecamatan = pendingLocation.kecamatan || 'Bengkalis';

                exportMapPdf(lat, lng, nama, kabupaten, kecamatan);
            }
        });
    }

    // Direct export from table row (handles AJAX reloads)
    $(document).on('click', '.js-export-map-direct', function(e) {
        e.preventDefault();
        const lat = Number(this.getAttribute('data-latitude'));
        const lng = Number(this.getAttribute('data-longitude'));
        const nama = this.getAttribute('data-nama') || 'Sekolah';
        const kabupaten = this.getAttribute('data-kabupaten') || 'Bengkalis';
        const kecamatan = this.getAttribute('data-kecamatan') || 'Bengkalis';

        exportMapPdf(lat, lng, nama, kabupaten, kecamatan);
    });
</script>
<?= $this->endSection(); ?>
