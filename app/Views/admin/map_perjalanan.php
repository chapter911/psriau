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
        height: 74vh;
        min-height: 540px;
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
        box-shadow: 0 2px 6px rgba(0,0,0,0.35);
        border: 2px solid #fff;
    }
    .basecamp-icon {
        background: #dc2626;
        border: 2px solid #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 14px;
        box-shadow: 0 0 12px rgba(220,38,38,0.7);
    }
    .hub-icon {
        background: #0284c7;
        border: 2px solid #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 12px;
        box-shadow: 0 2px 8px rgba(2,132,199,0.5);
    }
    .transport-pill-icon {
        background: #ffffff;
        border: 1.5px solid #0f172a;
        border-radius: 20px;
        padding: 2px 7px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        font-weight: 700;
        box-shadow: 0 3px 8px rgba(0,0,0,0.25);
        white-space: nowrap;
        pointer-events: auto;
        cursor: pointer;
    }
    .transport-pill-icon i {
        font-size: 12px;
    }
    .transport-pill-car {
        color: #1e40af;
        border-color: #1e40af;
    }
    .transport-pill-ship {
        color: #c2410c;
        border-color: #c2410c;
        background: #fff7ed;
    }
    .transport-pill-boat {
        color: #0e7490;
        border-color: #0e7490;
        background: #ecfeff;
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
    .legend-indicator {
        display: inline-block;
        width: 22px;
        height: 4px;
        margin-right: 6px;
        vertical-align: middle;
        border-radius: 2px;
    }
</style>

<div class="content-header p-2">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h4 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-route text-primary mr-2"></i> Peta Rencana Perjalanan Labelisasi Aset BMN
                </h4>
                <p class="text-muted small m-0">Rute Spasial Mengikuti Jalan Raya (OSRM), Koridor Laut RORO/Speedboat, dan 29 Titik Sekolah (Pra-Ramadan 1448 H)</p>
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
                        <small class="text-success font-weight-bold">01 Des 2026 – 04 Feb 2027</small>
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
                        <span class="info-box-text text-muted">Batas Akhir (Ramadan)</span>
                        <span class="info-box-number" style="font-size: 1.1rem;">04 Feb 2027</span>
                        <small class="text-danger font-weight-bold">Tuntas Sebelum Puasa</small>
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
                            <option value="2">Pekan 2: Kampar Kiri & Kuantan Singingi (Lintas Tengah)</option>
                            <option value="3">Pekan 3: Dumai & Pulau Rupat (Tol & RORO KMP)</option>
                            <option value="4">Pekan 4: Rokan Hulu (Kepenuhan & Tambusai)</option>
                            <option value="5">Pekan 5: Kepulauan Meranti (Speedboat & Pompong)</option>
                            <option value="6">Pekan 6: Indragiri Hilir (Tembilahan & Mandah)</option>
                            <option value="7">Pekan 7: Pulau Bengkalis Kota (KMP RORO)</option>
                            <option value="8">Pekan 8: Pulau Bengkalis Bantan & Muntai</option>
                        </select>
                    </div>
                    <div class="col-md-5 mb-2 mb-md-0 d-flex align-items-center flex-wrap">
                        <div class="custom-control custom-switch mr-3 mb-1">
                            <input type="checkbox" class="custom-control-input" id="toggleRoadRouting" checked>
                            <label class="custom-control-label small font-weight-bold text-dark" for="toggleRoadRouting">
                                <i class="fas fa-road text-secondary mr-1"></i> Ikuti Garis Jalan
                            </label>
                        </div>
                        <div class="custom-control custom-switch mr-3 mb-1">
                            <input type="checkbox" class="custom-control-input" id="toggleTransportBadges" checked>
                            <label class="custom-control-label small" for="toggleTransportBadges">
                                <i class="fas fa-car-side text-primary mr-1"></i> Ikon Moda
                            </label>
                        </div>
                        <div class="custom-control custom-switch mb-1">
                            <input type="checkbox" class="custom-control-input" id="toggleHubs" checked>
                            <label class="custom-control-label small" for="toggleHubs">
                                <i class="fas fa-anchor text-info mr-1"></i> Pelabuhan Transit
                            </label>
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

                        <!-- Mini Floating Legend -->
                        <div class="p-2 border rounded bg-white mt-2 small d-flex flex-wrap align-items-center justify-content-between">
                            <div>
                                <span class="font-weight-bold mr-2 text-dark"><i class="fas fa-info-circle text-primary mr-1"></i> Legenda Moda Transportasi:</span>
                                <span class="mr-3">
                                    <span class="legend-indicator" style="background: #1e40af;"></span>
                                    <i class="fas fa-car-side text-primary mr-1"></i> <strong>Jalur Darat / Tol</strong> (Mobil Dinas)
                                </span>
                                <span class="mr-3">
                                    <span class="legend-indicator" style="background: #c2410c; border-top: 2px dashed #c2410c; height: 0;"></span>
                                    <i class="fas fa-ship text-warning mr-1"></i> <strong>Kapal RORO KMP</strong> (Mobil Masuk Kapal)
                                </span>
                                <span class="mr-3">
                                    <span class="legend-indicator" style="background: #0e7490; border-top: 2px dotted #0e7490; height: 0;"></span>
                                    <i class="fas fa-water text-info mr-1"></i> <strong>Speedboat / Pompong</strong> (Mobil Dititip di Pelabuhan)
                                </span>
                            </div>
                            <div class="text-muted">
                                <span class="mr-2"><i class="fas fa-building text-danger mr-1"></i> Basecamp Pekanbaru</span>
                                <span><i class="fas fa-anchor text-info mr-1"></i> Pelabuhan Transit</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel Rincian Itinerary -->
            <div class="col-lg-4 mb-3">
                <div class="card itinerary-card h-100">
                    <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-list-ol mr-1 text-primary"></i> Rincian Titik & Rute Kunjungan</h6>
                        <span id="badgeCount" class="badge badge-primary">29 Titik</span>
                    </div>
                    <div class="card-body p-0" style="max-height: 70vh; overflow-y: auto;" id="timelineContainer">
                        <div class="p-3 text-center text-muted"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat data rute & jalan...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= esc(media_url('assets/leaflet/leaflet.js')); ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Pusat Default Provinsi Riau
    const defaultCenter = [0.90, 101.90];
    const map = L.map('mapItinerary', {
        zoomControl: true
    }).setView(defaultCenter, 8);

    // Layer Basemap OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; OpenStreetMap contributors | Satker PPS Riau'
    }).addTo(map);

    let basecampMarker = null;
    let markersLayer = L.layerGroup().addTo(map);
    let routesLayer = L.layerGroup().addTo(map);
    let badgesLayer = L.layerGroup().addTo(map);
    let hubsLayer = L.layerGroup().addTo(map);

    let allData = null;
    let currentFilteredPekan = 'all';
    let followRoads = true;

    // Cache rute jalan OSRM agar tidak berulang kali request
    const osrmRouteCache = {};

    // Fetch data rencana perjalanan dari backend
    fetch('<?= site_url("admin/dashboard/map-perjalanan-data"); ?>')
        .then(response => response.json())
        .then(data => {
            if (data.status !== 'ok') {
                alert('Gagal memuat data perjalanan.');
                return;
            }
            allData = data;
            renderBasecamp(data.basecamp);
            renderTransitHubs(data.transit_hubs);
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
                <div style="min-width: 230px; font-family: inherit;">
                    <div style="font-weight: bold; color: #dc2626; margin-bottom: 4px;">
                        <i class="fas fa-building mr-1"></i> ${bc.nama}
                    </div>
                    <div style="font-size: 11px; color: #555; margin-bottom: 6px;">${bc.lokasi}</div>
                    <span class="badge badge-danger">Homebase Satker PPS Riau</span>
                    <hr style="margin: 6px 0;">
                    <small class="text-muted"><i class="fas fa-car mr-1 text-primary"></i> Titik awal keberangkatan & kepulangan akhir pekan di Pekanbaru.</small>
                </div>
            `);
        basecampMarker.addTo(map);
    }

    function renderTransitHubs(hubs) {
        if (!hubs) return;
        hubsLayer.clearLayers();

        Object.keys(hubs).forEach(key => {
            const h = hubs[key];
            const isRoro = (h.tipe === 'pelabuhan_roro');
            const iconHtml = `<i class="${h.icon}"></i>`;
            const icon = L.divIcon({
                className: 'hub-icon',
                html: iconHtml,
                iconSize: [26, 26],
                iconAnchor: [13, 13]
            });

            const marker = L.marker([h.latitude, h.longitude], { icon: icon })
                .bindPopup(`
                    <div style="min-width: 220px; font-family: inherit;">
                        <span class="badge ${isRoro ? 'badge-warning text-dark' : 'badge-info'} mb-1">
                            <i class="${h.icon} mr-1"></i> ${isRoro ? 'Pelabuhan Kapal RORO' : 'Pelabuhan Speedboat / Muara'}
                        </span>
                        <h6 style="font-weight: bold; margin: 4px 0; color: #0f172a;">${h.nama}</h6>
                        <div style="font-size: 12px; color: #475569; margin-top: 4px;">${h.keterangan}</div>
                    </div>
                `);
            hubsLayer.addLayer(marker);
        });
    }

    // Helper: Query OSRM untuk mendapatkan garis jalan raya sebenarnya
    async function getRoadGeometry(start, end) {
        const cacheKey = `${start[0]},${start[1]}_${end[0]},${end[1]}`;
        if (osrmRouteCache[cacheKey]) {
            return osrmRouteCache[cacheKey];
        }

        const url = `https://router.project-osrm.org/route/v1/driving/${start[1]},${start[0]};${end[1]},${end[0]}?overview=simplified&geometries=geojson`;
        try {
            const res = await fetch(url);
            if (!res.ok) throw new Error('OSRM network error');
            const data = await res.json();
            if (data.code === 'Ok' && data.routes && data.routes.length > 0) {
                // Konversi [lng, lat] ke Leaflet [lat, lng]
                const latLngs = data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
                osrmRouteCache[cacheKey] = latLngs;
                return latLngs;
            }
        } catch (e) {
            console.warn('Fallback straight line used for:', start, end);
        }

        // Fallback garis lurus
        const fallback = [start, end];
        osrmRouteCache[cacheKey] = fallback;
        return fallback;
    }

    async function renderMapData(pekanList, filterPekanId) {
        markersLayer.clearLayers();
        routesLayer.clearLayers();
        badgesLayer.clearLayers();

        const bounds = L.latLngBounds();
        if (allData && allData.basecamp) {
            bounds.extend([allData.basecamp.latitude, allData.basecamp.longitude]);
        }

        let totalRendered = 0;

        for (const p of pekanList) {
            if (filterPekanId !== 'all' && p.id != filterPekanId) {
                continue;
            }

            const isWaterPekan = (p.id === 3 || p.id === 5 || p.id === 6 || p.id === 7 || p.id === 8);

            // Titik simpul rute
            const waypoints = [];
            if (allData && allData.basecamp) {
                waypoints.push({
                    name: allData.basecamp.nama,
                    coord: [allData.basecamp.latitude, allData.basecamp.longitude],
                    isHub: false
                });
            }

            // Tambahkan transit hub bila pekan ini melibatkan rute laut/penyeberangan
            if (p.id === 3 && allData.transit_hubs) {
                // Dumai & Rupat
                waypoints.push({ name: allData.transit_hubs.pelabuhan_dumai.nama, coord: [allData.transit_hubs.pelabuhan_dumai.latitude, allData.transit_hubs.pelabuhan_dumai.longitude], isHub: true });
                waypoints.push({ name: allData.transit_hubs.pelabuhan_rupat.nama, coord: [allData.transit_hubs.pelabuhan_rupat.latitude, allData.transit_hubs.pelabuhan_rupat.longitude], isHub: true, isWater: true });
            } else if (p.id === 5 && allData.transit_hubs) {
                // Meranti
                waypoints.push({ name: allData.transit_hubs.pelabuhan_buton.nama, coord: [allData.transit_hubs.pelabuhan_buton.latitude, allData.transit_hubs.pelabuhan_buton.longitude], isHub: true });
                waypoints.push({ name: allData.transit_hubs.pelabuhan_selatpanjang.nama, coord: [allData.transit_hubs.pelabuhan_selatpanjang.latitude, allData.transit_hubs.pelabuhan_selatpanjang.longitude], isHub: true, isWater: true });
            } else if (p.id === 6 && allData.transit_hubs) {
                // Inhil Mandah
                waypoints.push({ name: allData.transit_hubs.pelabuhan_tembilahan.nama, coord: [allData.transit_hubs.pelabuhan_tembilahan.latitude, allData.transit_hubs.pelabuhan_tembilahan.longitude], isHub: true });
                waypoints.push({ name: allData.transit_hubs.pelabuhan_mandah.nama, coord: [allData.transit_hubs.pelabuhan_mandah.latitude, allData.transit_hubs.pelabuhan_mandah.longitude], isHub: true, isWater: true });
            } else if ((p.id === 7 || p.id === 8) && allData.transit_hubs) {
                // Pulau Bengkalis
                waypoints.push({ name: allData.transit_hubs.pelabuhan_pakning.nama, coord: [allData.transit_hubs.pelabuhan_pakning.latitude, allData.transit_hubs.pelabuhan_pakning.longitude], isHub: true });
                waypoints.push({ name: allData.transit_hubs.pelabuhan_air_putih.nama, coord: [allData.transit_hubs.pelabuhan_air_putih.latitude, allData.transit_hubs.pelabuhan_air_putih.longitude], isHub: true, isWater: true });
            }

            p.sekolah.forEach(s => {
                totalRendered++;
                const latLng = [s.latitude, s.longitude];
                bounds.extend(latLng);
                waypoints.push({ name: s.nama, coord: latLng, isHub: false });

                // Custom Div Marker Sekolah
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

                const isWaterSchool = (p.id === 5 || p.id === 6 || p.id === 7 || p.id === 8 || (p.id === 3 && s.kabupaten.includes('Rupat')));
                const transportBadgeHtml = isWaterSchool
                    ? `<span class="badge badge-warning text-dark"><i class="fas fa-ship mr-1"></i> ${p.moda}</span>`
                    : `<span class="badge badge-primary"><i class="fas fa-car-side mr-1"></i> ${p.moda}</span>`;

                const popupHtml = `
                    <div style="min-width: 250px; font-family: inherit;">
                        <span class="itinerary-badge" style="background: ${p.color}; color: #fff;">Pekan ${p.id}</span>
                        <span class="badge badge-secondary ml-1">${s.nama_paket}</span>
                        <h6 style="font-weight: bold; margin: 6px 0 2px; color: #0f172a;">${s.nama}</h6>
                        <div style="font-size: 11px; color: #64748b; margin-bottom: 6px;">
                            <i class="fas fa-map-marker-alt mr-1"></i> ${s.kecamatan}, Kab. ${s.kabupaten}
                        </div>
                        <table style="width: 100%; font-size: 12px; line-height: 1.4;">
                            <tr><td style="color: #64748b; width: 35%;">Jadwal</td><td>: <strong>${s.jadwal_tgl}</strong></td></tr>
                            <tr><td style="color: #64748b;">Target Aset</td><td>: <strong>${Number(s.estimasi_aset).toLocaleString('id-ID')} unit</strong></td></tr>
                            <tr><td style="color: #64748b;">Akomodasi</td><td>: <span class="badge badge-light border">${s.status_inap}</span></td></tr>
                            <tr><td style="color: #64748b;">Moda</td><td>: ${transportBadgeHtml}</td></tr>
                        </table>
                    </div>
                `;

                const marker = L.marker(latLng, { icon: customIcon }).bindPopup(popupHtml);
                marker.npsn = s.npsn;
                markersLayer.addLayer(marker);
            });

            // Jalur penutup kembali ke Basecamp
            if (allData && allData.basecamp) {
                waypoints.push({
                    name: allData.basecamp.nama,
                    coord: [allData.basecamp.latitude, allData.basecamp.longitude],
                    isHub: false
                });
            }

            // Gambar Garis Rute (Mengikuti Jalan OSRM untuk darat, atau Garis Putus-putus untuk Laut)
            for (let i = 0; i < waypoints.length - 1; i++) {
                const wStart = waypoints[i];
                const wEnd = waypoints[i + 1];

                // Cek apakah ruas ini adalah penyeberangan perairan (air)
                const isWaterSegment = (wStart.isWater || wEnd.isWater);

                if (isWaterSegment) {
                    // Jalur Penyeberangan Laut / Sungai
                    const waterLine = L.polyline([wStart.coord, wEnd.coord], {
                        color: p.color,
                        weight: 4,
                        opacity: 0.9,
                        dashArray: '8, 8'
                    });
                    waterLine.bindTooltip(`🚢 Jalur Laut/Perairan: ${wStart.name} ➔ ${wEnd.name}`, { sticky: true });
                    routesLayer.addLayer(waterLine);

                    // Pasang Ikon Kapal / Speedboat di tengah jalur penyeberangan
                    const midLat = (wStart.coord[0] + wEnd.coord[0]) / 2;
                    const midLng = (wStart.coord[1] + wEnd.coord[1]) / 2;
                    const shipPillIcon = L.divIcon({
                        className: 'transport-pill-wrapper',
                        html: `<div class="transport-pill-icon transport-pill-ship"><i class="fas fa-ship"></i> Kapal RORO / Laut</div>`,
                        iconSize: [120, 24],
                        iconAnchor: [60, 12]
                    });
                    const pillMarker = L.marker([midLat, midLng], { icon: shipPillIcon })
                        .bindPopup(`<strong>Penyeberangan Laut / Muara</strong><br>${wStart.name} ➔ ${wEnd.name}<br>Moda: Kapal RORO KMP / Speedboat`);
                    badgesLayer.addLayer(pillMarker);

                } else {
                    // Jalur Darat (Ikuti Garis Jalan via OSRM bila aktif)
                    let roadCoords = [wStart.coord, wEnd.coord];
                    if (followRoads) {
                        roadCoords = await getRoadGeometry(wStart.coord, wEnd.coord);
                    }

                    const roadLine = L.polyline(roadCoords, {
                        color: p.color,
                        weight: 4,
                        opacity: 0.8
                    });
                    roadLine.bindTooltip(`🚗 Jalur Darat / Tol: ${wStart.name} ➔ ${wEnd.name}`, { sticky: true });
                    routesLayer.addLayer(roadLine);

                    // Pasang Ikon Mobil di tengah jalan utama jika jarak cukup jauh
                    if (roadCoords.length > 5) {
                        const midIndex = Math.floor(roadCoords.length / 2);
                        const midPoint = roadCoords[midIndex];
                        const carPillIcon = L.divIcon({
                            className: 'transport-pill-wrapper',
                            html: `<div class="transport-pill-icon transport-pill-car"><i class="fas fa-car-side"></i> Mobil Dinas</div>`,
                            iconSize: [95, 24],
                            iconAnchor: [48, 12]
                        });
                        const carMarker = L.marker(midPoint, { icon: carPillIcon })
                            .bindPopup(`<strong>Rute Jalan Darat / Tol</strong><br>Moda: Mobil Dinas Operasional Satker<br>Dari: ${wStart.name}<br>Menuju: ${wEnd.name}`);
                        badgesLayer.addLayer(carMarker);
                    }
                }
            }
        }

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

            const isWater = (p.id === 3 || p.id === 5 || p.id === 6 || p.id === 7 || p.id === 8);
            const transportIconClass = isWater ? 'fa-ship text-warning' : 'fa-car-side text-primary';

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
                <div style="font-weight: normal; font-size: 11px; color: #475569; margin-top: 3px;">
                    <i class="fas ${transportIconClass} mr-1"></i> ${p.moda}
                </div>
                <div style="font-weight: normal; font-size: 10px; color: #64748b; margin-top: 2px;">
                    <i class="fas fa-route mr-1"></i> Koridor: ${p.koridor}
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

    // Toggle Garis Jalan Raya (OSRM)
    document.getElementById('toggleRoadRouting').addEventListener('change', function(e) {
        followRoads = e.target.checked;
        if (allData) {
            renderMapData(allData.pekan, currentFilteredPekan);
        }
    });

    // Toggle Badges Ikon Transportasi (Mobil / Kapal)
    document.getElementById('toggleTransportBadges').addEventListener('change', function(e) {
        if (e.target.checked) {
            map.addLayer(badgesLayer);
        } else {
            map.removeLayer(badgesLayer);
        }
    });

    // Toggle Pelabuhan Transit Hubs
    document.getElementById('toggleHubs').addEventListener('change', function(e) {
        if (e.target.checked) {
            map.addLayer(hubsLayer);
        } else {
            map.removeLayer(hubsLayer);
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
