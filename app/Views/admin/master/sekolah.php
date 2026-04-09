<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Sekolah</h3>
        <?php if (! empty($can_add)): ?>
            <div class="float-right">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-tambah-sekolah">Tambah Sekolah</button>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped w-100 nowrap js-datatable">
            <thead>
                <tr style="white-space: nowrap;">
                    <th class="text-center">#</th>
                    <th class="text-center">NPSN</th>
                    <th class="text-center">NAMA</th>
                    <th class="text-center">JENIS</th>
                    <th class="text-center">KABUPATEN</th>
                    <th class="text-center">KECAMATAN</th>
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
                                    data-toggle="modal"
                                    data-target="#modal-peta-sekolah"
                                    data-nama="<?= esc((string) ($item['nama'] ?? ''), 'attr'); ?>"
                                    data-npsn="<?= esc((string) ($item['npsn'] ?? ''), 'attr'); ?>"
                                    data-latitude="<?= esc($latitude, 'attr'); ?>"
                                    data-longitude="<?= esc($longitude, 'attr'); ?>"
                                >Lihat Peta</button>
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
                        <div class="text-right mt-2 mt-md-0">
                            <span class="badge badge-primary">Leaflet</span>
                            <span class="badge badge-secondary">Google Maps Style</span>
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
            <form action="<?= site_url('/admin/master/sekolah/tambah'); ?>" method="post">
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
                    <div class="form-group">
                        <label>Nama Sekolah</label>
                        <input type="text" name="nama" class="form-control" required>
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
                    <div class="form-group">
                        <label>Nama Sekolah</label>
                        <input type="text" id="edit_nama" name="nama" class="form-control" required>
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
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    (function () {
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
        };

        modalEdit.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            if (!trigger) return;

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
        });
    })();

    (function () {
        const mapModal = document.getElementById('modal-peta-sekolah');
        if (!mapModal || typeof L === 'undefined') {
            return;
        }

        const mapContainer = document.getElementById('school-map');
        const schoolName = document.getElementById('map-school-name');
        const schoolSubtitle = document.getElementById('map-school-subtitle');
        const schoolCoordinates = document.getElementById('map-school-coordinates');
        const googleMapButton = document.getElementById('btn-open-google-maps');
        let leafletMap = null;
        let marker = null;

        const googleTile = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
            attribution: '&copy; Google'
        });

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

        const renderMap = (latitude, longitude, label) => {
            const lat = Number(latitude);
            const lng = Number(longitude);

            if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                mapContainer.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 text-muted">Koordinat tidak tersedia.</div>';
                googleMapButton.setAttribute('href', '#');
                googleMapButton.classList.add('disabled');
                googleMapButton.setAttribute('aria-disabled', 'true');
                return;
            }

            mapContainer.innerHTML = '';

            if (!leafletMap) {
                leafletMap = L.map(mapContainer, {
                    zoomControl: true,
                    preferCanvas: true,
                });
                googleTile.addTo(leafletMap);
            }

            if (marker) {
                marker.remove();
            }

            leafletMap.setView([lat, lng], 16);
            marker = L.marker([lat, lng]).addTo(leafletMap);
            marker.bindPopup(label || 'Lokasi sekolah').openPopup();

            window.setTimeout(() => {
                leafletMap.invalidateSize();
                leafletMap.setView([lat, lng], 16);
            }, 150);

            const googleUrl = openGoogleMaps(lat, lng);
            googleMapButton.setAttribute('href', googleUrl);
            googleMapButton.classList.remove('disabled');
            googleMapButton.removeAttribute('aria-disabled');
            googleMapButton.setAttribute('target', '_blank');
            googleMapButton.setAttribute('rel', 'noopener noreferrer');
        };

        mapModal.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            if (!trigger) {
                return;
            }

            const nama = trigger.getAttribute('data-nama') || '-';
            const npsn = trigger.getAttribute('data-npsn') || '-';
            const latitude = trigger.getAttribute('data-latitude') || '';
            const longitude = trigger.getAttribute('data-longitude') || '';

            schoolName.textContent = nama;
            schoolSubtitle.textContent = 'NPSN ' + npsn;
            schoolCoordinates.textContent = 'Lat: ' + formatCoordinate(latitude) + ' | Lng: ' + formatCoordinate(longitude);
            renderMap(latitude, longitude, nama);
        });

        mapModal.addEventListener('hidden.bs.modal', function () {
            if (leafletMap) {
                leafletMap.remove();
                leafletMap = null;
                marker = null;
            }
            mapContainer.innerHTML = '';
        });
    })();
</script>
<?= $this->endSection(); ?>
