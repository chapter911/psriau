<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<link rel="stylesheet" href="<?= esc(media_url('assets/leaflet/leaflet.css')); ?>">
<style>
    .itinerary-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
        background: #fff;
    }
    .itinerary-badge {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
    }
    #mapItinerary {
        width: 100%;
        height: 72vh;
        min-height: 520px;
        border-radius: 10px;
        border: 1px solid #cbd5e1;
    }
    .custom-div-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: #fff;
        font-weight: bold;
        font-size: 11px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        border: 2px solid #fff;
    }
    .basecamp-icon {
        background: #d32f2f;
        border: 2px solid #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 14px;
        box-shadow: 0 0 10px rgba(211,47,47,0.6);
    }
    .legend-box {
        background: rgba(255, 255, 255, 0.95);
        padding: 10px 14px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        font-size: 12px;
        line-height: 1.5;
        max-height: 380px;
        overflow-y: auto;
    }
    .legend-color-dot {
        display: inline-block;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 6px;
        vertical-align: middle;
    }
    .timeline-item {
        cursor: pointer;
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
        padding: 8px 12px;
        border-bottom: 1px solid #f1f5f9;
    }
    .timeline-item:hover {
        background-color: #f8fafc;
        border-left-color: #3b82f6;
    }
    .timeline-item.active {
        background-color: #eff6ff;
        border-left-color: #1d4ed8;
    }
</style>

<div class="content-header p-2">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h4 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-route text-primary mr-2"></i> Peta Rencana Perjalanan Labelisasi Aset BMN
                </h4>
                <p class="text-muted small m-0">Visualisasi Koridor, Rute Transportasi, dan Jadwal Kunjungan 29 Sekolah (Target Pra-Ramadan 1448 H)</p>
            </div>
            <div class="mt-2 mt-md-0">
                <a href="<?= site_url('admin/map'); ?>" class="btn btn-outline-secondary btn-sm mr-1">
                    <i class="fas fa-school mr-1"></i> Peta Sebaran Sekolah
                </a>
                <a href="<?= site_url('Rencana_Monitoring_dan_Labelisasi_BMN_Riau.xlsx'); ?>" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel mr-1"></i> Unduh Jadwal Excel
                </a>
            </div>
        </div>
    </div>
</div>

<div class="content pb-4">
    <div class="container-fluid">
        <!-- Metric Cards -->
        <div class="row">
            <div class="col-md-3 col-6">
                <div class="info-box bg-light elevation-1">
                    <span class="info-box-icon bg-info"><i class="fas fa-calendar-alt"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-muted">Durasi Pelaksanaan</span>
                        <span class="info-box-number" style="font-size: 1.1rem;">8 Pekan Dinas</span>
                        <small class="text-success font-weight-bold">Tuntas s/d 04 Feb 2027</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="info-box bg-light elevation-1">
                    <span class="info-box-icon bg-primary"><i class="fas fa-school"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-muted">Sekolah Sasaran</span>
                        <span class="info-box-number" style="font-size: 1.1rem;">29 Sekolah</span>
                        <small class="text-muted">Riau 2, 5, 6, 7, 8</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="info-box bg-light elevation-1">
                    <span class="info-box-icon bg-success"><i class="fas fa-boxes"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-muted">Estimasi Aset BMN</span>
                        <span class="info-box-number" style="font-size: 1.1rem;">11.455 Aset</span>
                        <small class="text-muted">Target Labelisasi</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="info-box bg-light elevation-1">
                    <span class="info-box-icon bg-danger"><i class="fas fa-moon"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-muted">Awal 1 Ramadan</span>
                        <span class="info-box-number" style="font-size: 1.1rem;">08 Feb 2027</span>
                        <small class="text-danger font-weight-bold">Bebas Dinas Luar Kota</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Kontrol Peta -->
        <div class="card itinerary-card mb-3">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label class="small text-muted font-weight-bold mb-1">Pilih Filter Pekan / Koridor:</label>
                        <select id="selectPekan" class="form-control form-control-sm">
                            <option value="all">Tampilkan Semua Pekan (1 s/d 8) - 29 Sekolah</option>
                            <option value="1">Pekan 1: Kampar (Tol Bangkinang - PP)</option>
                            <option value="2">Pekan 2: Kampar Kiri & Kuantan Singingi</option>
                            <option value="3">Pekan 3: Dumai & Pulau Rupat (Tol & RORO)</option>
                            <option value="4">Pekan 4: Rokan Hulu (Kepenuhan & Tambusai)</option>
                            <option value="5">Pekan 5: Kepulauan Meranti (Speedboat & Pompong)</option>
                            <option value="6">Pekan 6: Indragiri Hilir (Tembilahan & Mandah)</option>
                            <option value="7">Pekan 7: Pulau Bengkalis Kota (KMP RORO)</option>
                            <option value="8">Pekan 8: Pulau Bengkalis Bantan & Muntai</option>
                        </select>
                    </div>
                    <div class="col-md-5 mb-2 mb-md-0 d-flex align-items-center">
                        <div class="custom-control custom-switch mr-3">
                            <input type="checkbox" class="custom-control-input" id="toggleRoutes" checked>
                            <label class="custom-control-label small" for="toggleRoutes">Garis Rute Perjalanan</label>
                        </div>
                        <div class="custom-control custom-switch mr-3">
                            <input type="checkbox" class="custom-control-input" id="toggleBasecamp" checked>
                            <label class="custom-control-label small" for="toggleBasecamp">Kantor Pekanbaru</label>
                        </div>
                    </div>
                    <div class="col-md-3 text-md-right">
                        <button id="btnResetView" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-compress-arrows-alt mr-1"></i> Reset Tampilan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map & Itinerary List Container -->
        <div class="row">
            <div class="col-lg-8 mb-3">
                <div class="card itinerary-card h-100">
                    <div class="card-body p-2 position-relative">
                        <div id="mapItinerary"></div>
                    </div>
                </div>
            </div>

            <!-- Panel Rincian Itinerary -->
            <div class="col-lg-4 mb-3">
                <div class="card itinerary-card h-100">
                    <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-list-ol mr-1 text-primary"></i> Rincian Titik Kunjungan</h6>
                        <span id="badgeCount" class="badge badge-primary">29 Titik</span>
                    </div>
                    <div class="card-body p-0" style="max-height: 65vh; overflow-y: auto;" id="timelineContainer">
                        <div class="p-3 text-center text-muted"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat data rute...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= esc(media_url('assets/leaflet/leaflet.js')); ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi Peta
    const defaultCenter = [0.90, 101.90]; // Pusat Provinsi Riau
    const map = L.map('mapItinerary', {
        zoomControl: true
    }).setView(defaultCenter, 8);

    // Layer OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; OpenStreetMap contributors | Satker PPS Riau'
    }).addTo(map);

    let basecampMarker = null;
    let markersLayer = L.layerGroup().addTo(map);
    let routesLayer = L.layerGroup().addTo(map);
    let allData = null;
    let currentFilteredPekan = 'all';

    // Fetch data rencana perjalanan
    fetch('<?= site_url("admin/dashboard/map-perjalanan-data"); ?>')
        .then(response => response.json())
        .then(data => {
            if (data.status !== 'ok') {
                alert('Gagal memuat data perjalanan.');
                return;
            }
            allData = data;
            renderBasecamp(data.basecamp);
            renderMapData(data.pekan, 'all');
            renderTimeline(data.pekan, 'all');
        })
        .catch(err => {
            console.error('Error fetching itinerary data:', err);
        });

    function renderBasecamp(bc) {
        if (!bc) return;
        const icon = L.divIcon({
            className: 'basecamp-icon',
            html: '<i class="fas fa-building"></i>',
            iconSize: [32, 32],
            iconAnchor: [16, 16]
        });

        basecampMarker = L.marker([bc.latitude, bc.longitude], { icon: icon })
            .bindPopup(`
                <div style="min-width: 220px;">
                    <div style="font-weight: bold; color: #d32f2f; margin-bottom: 4px;">
                        <i class="fas fa-home mr-1"></i> ${bc.nama}
                    </div>
                    <div style="font-size: 11px; color: #555; margin-bottom: 6px;">${bc.lokasi}</div>
                    <span class="badge badge-danger">Homebase Satker PPS Riau</span>
                    <hr style="margin: 6px 0;">
                    <small class="text-muted">Titik awal keberangkatan & kepulangan setiap akhir pekan.</small>
                </div>
            `);
        basecampMarker.addTo(map);
    }

    function renderMapData(pekanList, filterPekanId) {
        markersLayer.clearLayers();
        routesLayer.clearLayers();

        const bounds = L.latLngBounds();
        if (allData && allData.basecamp) {
            bounds.extend([allData.basecamp.latitude, allData.basecamp.longitude]);
        }

        let totalRendered = 0;

        pekanList.forEach(p => {
            if (filterPekanId !== 'all' && p.id != filterPekanId) {
                return;
            }

            const routePoints = [];
            if (allData && allData.basecamp) {
                routePoints.push([allData.basecamp.latitude, allData.basecamp.longitude]);
            }

            p.sekolah.forEach((s, idx) => {
                totalRendered++;
                const latLng = [s.latitude, s.longitude];
                bounds.extend(latLng);
                routePoints.push(latLng);

                // Custom Div Marker
                const markerHtml = `
                    <div class="custom-div-icon" style="background-color: ${p.color}; width: 28px; height: 28px;">
                        P${p.id}
                    </div>
                `;
                const customIcon = L.divIcon({
                    className: 'custom-leaflet-icon',
                    html: markerHtml,
                    iconSize: [28, 28],
                    iconAnchor: [14, 14]
                });

                const popupHtml = `
                    <div style="min-width: 240px; font-family: inherit;">
                        <span class="itinerary-badge" style="background: ${p.color}; color: #fff;">Pekan ${p.id}</span>
                        <span class="badge badge-secondary ml-1">${s.nama_paket}</span>
                        <h6 style="font-weight: bold; margin: 6px 0 2px; color: #1e293b;">${s.nama}</h6>
                        <div style="font-size: 11px; color: #64748b; margin-bottom: 6px;">
                            <i class="fas fa-map-marker-alt mr-1"></i> ${s.kecamatan}, Kab. ${s.kabupaten}
                        </div>
                        <table style="width: 100%; font-size: 12px; line-height: 1.4;">
                            <tr><td style="color: #64748b; width: 35%;">Jadwal</td><td>: <strong>${s.jadwal_tgl}</strong></td></tr>
                            <tr><td style="color: #64748b;">Target Aset</td><td>: <strong>${Number(s.estimasi_aset).toLocaleString('id-ID')} unit</strong></td></tr>
                            <tr><td style="color: #64748b;">Akomodasi</td><td>: <span class="badge badge-light border">${s.status_inap}</span></td></tr>
                            <tr><td style="color: #64748b;">Moda</td><td>: <small>${p.moda}</small></td></tr>
                        </table>
                    </div>
                `;

                const marker = L.marker(latLng, { icon: customIcon }).bindPopup(popupHtml);
                marker.npsn = s.npsn;
                markersLayer.addLayer(marker);
            });

            // Tambahkan rute penutup kembali ke Basecamp
            if (allData && allData.basecamp) {
                routePoints.push([allData.basecamp.latitude, allData.basecamp.longitude]);
            }

            // Polylines rute
            const isWater = (p.id === 5 || p.id === 6 || p.id === 3);
            const polyline = L.polyline(routePoints, {
                color: p.color,
                weight: 3,
                opacity: 0.8,
                dashArray: isWater ? '6, 8' : null // Garis putus-putus untuk rute laut/perairan
            });
            polyline.bindTooltip(`Rute ${p.label} (${p.koridor})`, { sticky: true });
            routesLayer.addLayer(polyline);
        });

        document.getElementById('badgeCount').innerText = `${totalRendered} Titik`;

        if (bounds.isValid()) {
            map.fitBounds(bounds, { padding: [40, 40] });
        }
    }

    function renderTimeline(pekanList, filterPekanId) {
        const container = document.getElementById('timelineContainer');
        container.innerHTML = '';

        pekanList.forEach(p => {
            if (filterPekanId !== 'all' && p.id != filterPekanId) {
                return;
            }

            // Header Pekan
            const headerDiv = document.createElement('div');
            headerDiv.style.background = '#f8fafc';
            headerDiv.style.borderLeft = `4px solid ${p.color}`;
            headerDiv.style.padding = '8px 12px';
            headerDiv.style.fontWeight = 'bold';
            headerDiv.style.fontSize = '12px';
            headerDiv.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <span style="color: ${p.color};">${p.label}</span>
                    <span class="badge badge-light border">${p.periode}</span>
                </div>
                <div style="font-weight: normal; font-size: 11px; color: #64748b; margin-top: 2px;">
                    <i class="fas fa-route mr-1"></i> ${p.koridor}
                </div>
            `;
            container.appendChild(headerDiv);

            p.sekolah.forEach(s => {
                const item = document.createElement('div');
                item.className = 'timeline-item';
                item.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start">
                        <strong style="font-size: 12px; color: #1e293b;">${s.nama}</strong>
                        <span class="badge badge-info" style="font-size: 10px;">${s.estimasi_aset} Aset</span>
                    </div>
                    <div style="font-size: 11px; color: #64748b;">${s.kecamatan}, ${s.kabupaten}</div>
                    <div style="font-size: 11px; color: #0284c7; margin-top: 2px;">
                        <i class="far fa-calendar-alt mr-1"></i> ${s.jadwal_tgl}
                    </div>
                `;

                item.addEventListener('click', () => {
                    document.querySelectorAll('.timeline-item').forEach(el => el.classList.remove('active'));
                    item.classList.add('active');

                    // Cari marker di peta dan buka popup
                    markersLayer.eachLayer(layer => {
                        if (layer.npsn === s.npsn) {
                            map.setView([s.latitude, s.longitude], 13);
                            layer.openPopup();
                        }
                    });
                });

                container.appendChild(item);
            });
        });
    }

    // Filter Change Event
    document.getElementById('selectPekan').addEventListener('change', function(e) {
        currentFilteredPekan = e.target.value;
        if (allData) {
            renderMapData(allData.pekan, currentFilteredPekan);
            renderTimeline(allData.pekan, currentFilteredPekan);
        }
    });

    // Toggle Routes
    document.getElementById('toggleRoutes').addEventListener('change', function(e) {
        if (e.target.checked) {
            map.addLayer(routesLayer);
        } else {
            map.removeLayer(routesLayer);
        }
    });

    // Toggle Basecamp
    document.getElementById('toggleBasecamp').addEventListener('change', function(e) {
        if (basecampMarker) {
            if (e.target.checked) {
                map.addLayer(basecampMarker);
            } else {
                map.removeLayer(basecampMarker);
            }
        }
    });

    // Reset View Button
    document.getElementById('btnResetView').addEventListener('click', function() {
        document.getElementById('selectPekan').value = 'all';
        currentFilteredPekan = 'all';
        if (allData) {
            renderMapData(allData.pekan, 'all');
            renderTimeline(allData.pekan, 'all');
        }
    });
});
</script>
<?= $this->endSection(); ?>
