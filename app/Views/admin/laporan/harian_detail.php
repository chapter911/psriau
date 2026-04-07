<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<?php
$selectedTitle = $selected_title ?? null;
$reports = $reports ?? [];
$canEdit = (bool) ($can_edit ?? false);
?>
<div class="card">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title mb-0">Daftar Laporan Harian - <?= esc((string) ($selectedTitle['name'] ?? '-')); ?></h3>
        <div class="card-tools ml-auto">
            <?php if ($canEdit): ?>
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalHarianForm">Tambah Laporan Harian</button>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover w-100" id="tableLaporanHarianDetail">
                <thead class="thead-light">
                    <tr>
                        <th style="width:60px;">No</th>
                        <th style="width:130px;">Tanggal</th>
                        <th>Blok Pekerjaan</th>
                        <th style="width:160px;">Personil</th>
                        <th style="width:180px;">Cuaca</th>
                        <th style="width:110px;">Foto</th>
                        <?php if ($canEdit): ?><th style="width:130px;">Aksi</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($reports !== []): ?>
                        <?php $no = 1; foreach ($reports as $report): ?>
                            <?php
                            $sections = $report['sections'] ?? [];
                            $photos = $report['photos'] ?? [];
                            $reportPayload = [
                                'id' => (int) ($report['id'] ?? 0),
                                'sekolah_id' => (int) ($report['sekolah_id'] ?? 0),
                                'report_date' => (string) ($report['report_date'] ?? ''),
                                'personil_pekerja' => (string) ($report['personil_pekerja'] ?? ''),
                                'personil_tukang' => (string) ($report['personil_tukang'] ?? ''),
                                'cuaca_cerah' => (string) ($report['cuaca_cerah'] ?? ''),
                                'cuaca_hujan' => (string) ($report['cuaca_hujan'] ?? ''),
                                'latitude' => (string) ($report['latitude'] ?? ''),
                                'longitude' => (string) ($report['longitude'] ?? ''),
                                'sections' => $sections,
                            ];
                            ?>
                            <tr>
                                <td class="text-center"><?= esc((string) $no++); ?></td>
                                <td class="text-center"><?= esc((string) ($report['report_date'] ?? '-')); ?></td>
                                <td>
                                    <?php if ($sections !== []): ?>
                                        <?php foreach ($sections as $section): ?>
                                            <div class="mb-2">
                                                <strong><?= esc((string) ($section['title'] ?? '-')); ?></strong>
                                                <ol class="mb-0 pl-3">
                                                    <?php foreach (($section['items'] ?? []) as $item): ?>
                                                        <li><?= esc((string) $item); ?></li>
                                                    <?php endforeach; ?>
                                                </ol>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div>Pekerja: <?= esc((string) ($report['personil_pekerja'] ?? '-')); ?></div>
                                    <div>Tukang: <?= esc((string) ($report['personil_tukang'] ?? '-')); ?></div>
                                </td>
                                <td>
                                    <div>Cerah: <?= esc((string) ($report['cuaca_cerah'] ?? '-')); ?></div>
                                    <div>Hujan: <?= esc((string) ($report['cuaca_hujan'] ?? '-')); ?></div>
                                </td>
                                <td class="text-center">
                                    <button
                                        type="button"
                                        class="btn btn-info btn-sm js-view-photos"
                                        data-photos="<?= esc(json_encode($photos, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'attr'); ?>"
                                        <?= $photos === [] ? 'disabled' : ''; ?>
                                        title="Lihat Foto"
                                    >
                                        <i class="fas fa-images"></i>
                                    </button>
                                </td>
                                <?php if ($canEdit): ?>
                                    <td class="text-center">
                                        <button
                                            type="button"
                                            class="btn btn-warning btn-sm js-edit-harian"
                                            data-report="<?= esc(json_encode($reportPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'attr'); ?>"
                                            data-toggle="modal"
                                            data-target="#modalHarianForm"
                                        >
                                            Edit
                                        </button>
                                        <form action="<?= site_url('admin/laporan/harian/' . (int) ($report['id'] ?? 0) . '/hapus'); ?>" method="post" class="d-inline" onsubmit="return confirm('Hapus laporan harian ini?');">
                                            <?= csrf_field(); ?>
                                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                        </form>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPhotoPreview" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mb-0">Preview Foto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="previewPhotoImage" src="" alt="Foto laporan" class="img-fluid rounded" style="max-height:65vh; object-fit:contain;">
                <div class="small text-muted mt-2" id="previewPhotoCounter">0 / 0</div>
                <div class="d-flex flex-wrap justify-content-center mt-3" id="previewPhotoThumbnails"></div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-outline-secondary" id="btnPreviewPrev">Previous</button>
                <button type="button" class="btn btn-outline-secondary" id="btnPreviewNext">Next</button>
            </div>
        </div>
    </div>
</div>

<?php if ($canEdit): ?>
<div class="modal fade" id="modalHarianForm" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0" id="harianModalTitle">Tambah Laporan Harian</h5>
                    <small class="text-muted">Gunakan capture kamera saat mengunggah foto dari HP.</small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formHarian" action="<?= site_url('admin/laporan/harian/tambah'); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <input type="hidden" name="report_id" id="harianReportId" value="">
                <input type="hidden" name="sekolah_id" id="sekolah_id" value="<?= esc((string) ($selectedTitle['id'] ?? 0)); ?>">
                <div class="modal-body modal-body-harian">
                    <div class="form-row">
                        <div class="form-group col-md-8">
                            <label>Sekolah</label>
                            <input type="text" class="form-control" value="<?= esc((string) ($selectedTitle['name'] ?? '-')); ?>" readonly>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="report_date">Tanggal</label>
                            <input type="date" class="form-control" id="report_date" name="report_date" value="<?= esc(date('Y-m-d')); ?>" required>
                        </div>
                    </div>

                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap mb-2">
                            <div>
                                <h6 class="mb-0">Blok Pekerjaan</h6>
                                <small class="text-muted">Satu baris untuk satu blok pekerjaan.</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddSection">Tambah Baris</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width:240px;">Judul Blok</th>
                                        <th>Daftar Pekerjaan (pisahkan per baris)</th>
                                        <th style="width:90px;" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="dailySections"></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="latitude">Latitude</label>
                            <input type="text" class="form-control" id="latitude" name="latitude" readonly required>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="longitude">Longitude</label>
                            <input type="text" class="form-control" id="longitude" name="longitude" readonly required>
                        </div>
                        <div class="form-group col-md-4 d-flex align-items-end">
                            <button type="button" class="btn btn-info btn-block" id="btnGetLocation">Ambil Koordinat Lokasi</button>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="personil_pekerja">Personil - Pekerja</label>
                            <input type="number" class="form-control" id="personil_pekerja" name="personil_pekerja" min="0" step="1" inputmode="numeric" placeholder="Contoh: 5">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="personil_tukang">Personil - Tukang</label>
                            <input type="number" class="form-control" id="personil_tukang" name="personil_tukang" min="0" step="1" inputmode="numeric" placeholder="Contoh: 7">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Cuaca Cerah (Dari - Sampai)</label>
                            <div class="d-flex align-items-center">
                                <input type="time" class="form-control" id="cuaca_cerah_start" step="60">
                                <span class="px-2 text-muted">s/d</span>
                                <input type="time" class="form-control" id="cuaca_cerah_end" step="60">
                            </div>
                            <input type="hidden" id="cuaca_cerah" name="cuaca_cerah">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Cuaca Hujan (Dari - Sampai)</label>
                            <div class="d-flex align-items-center">
                                <input type="time" class="form-control" id="cuaca_hujan_start" step="60">
                                <span class="px-2 text-muted">s/d</span>
                                <input type="time" class="form-control" id="cuaca_hujan_end" step="60">
                            </div>
                            <input type="hidden" id="cuaca_hujan" name="cuaca_hujan">
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label for="photos">Foto Lapangan</label>
                        <input type="file" class="form-control" id="photos" name="photos[]" accept="image/*" capture="environment" multiple>
                        <small class="text-muted d-block mt-1">Di HP, browser akan diarahkan ke kamera jika didukung. Beberapa browser tetap dapat menampilkan galeri.</small>
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

<template id="sectionTemplate">
    <tr class="daily-section-row">
        <td>
            <input type="text" class="form-control form-control-sm" name="section_title[]" placeholder="Contoh: I. Pek. Persiapan" required>
        </td>
        <td>
            <textarea class="form-control form-control-sm" name="section_items[]" rows="3" placeholder="1. Pek. Rakit besi&#10;2. Pek. Cor Tapak Pagar" required></textarea>
        </td>
        <td class="text-center align-middle">
            <button type="button" class="btn btn-sm btn-outline-danger js-remove-section">Hapus</button>
        </td>
    </tr>
</template>
<?php endif; ?>
<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<style>
    #modalHarianForm .modal-body-harian {
        max-height: calc(100vh - 210px);
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }

    #previewPhotoThumbnails {
        gap: 8px;
        max-height: 160px;
        overflow-y: auto;
    }

    .preview-thumb-btn {
        border: 2px solid transparent;
        padding: 0;
        border-radius: 6px;
        background: transparent;
    }

    .preview-thumb-btn.active {
        border-color: #007bff;
    }

    .preview-thumb-btn img {
        width: 72px;
        height: 72px;
        object-fit: cover;
        border-radius: 4px;
        display: block;
    }
</style>
<script>
(() => {
    if (typeof $ === 'undefined' || ! $.fn.DataTable) {
        return;
    }

    const $table = $('#tableLaporanHarianDetail');
    if ($table.length && ! $.fn.dataTable.isDataTable($table)) {
        $table.DataTable({
            responsive: false,
            autoWidth: false,
            scrollX: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Semua']],
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data',
                zeroRecords: 'Data tidak ditemukan',
                emptyTable: 'Belum ada laporan harian untuk sekolah ini.',
                paginate: {
                    first: 'Awal',
                    last: 'Akhir',
                    next: 'Berikutnya',
                    previous: 'Sebelumnya',
                },
            },
        });
    }
})();

(() => {
    const modalEl = document.getElementById('modalPhotoPreview');
    const imageEl = document.getElementById('previewPhotoImage');
    const counterEl = document.getElementById('previewPhotoCounter');
    const thumbnailsEl = document.getElementById('previewPhotoThumbnails');
    const prevButton = document.getElementById('btnPreviewPrev');
    const nextButton = document.getElementById('btnPreviewNext');

    if (!modalEl || !imageEl || !counterEl || !thumbnailsEl || !prevButton || !nextButton) {
        return;
    }

    let photos = [];
    let currentIndex = 0;

    const renderThumbnails = () => {
        thumbnailsEl.innerHTML = '';

        photos.forEach((photo, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'preview-thumb-btn' + (index === currentIndex ? ' active' : '');

            const image = document.createElement('img');
            image.src = photo;
            image.alt = `Thumbnail ${index + 1}`;

            button.appendChild(image);
            button.addEventListener('click', () => {
                currentIndex = index;
                renderPhoto();
            });

            thumbnailsEl.appendChild(button);
        });
    };

    const renderPhoto = () => {
        if (!photos.length) {
            imageEl.src = '';
            counterEl.textContent = '0 / 0';
            thumbnailsEl.innerHTML = '';
            prevButton.disabled = true;
            nextButton.disabled = true;
            return;
        }

        imageEl.src = photos[currentIndex] || '';
        counterEl.textContent = `${currentIndex + 1} / ${photos.length}`;
        prevButton.disabled = photos.length <= 1;
        nextButton.disabled = photos.length <= 1;
        renderThumbnails();
    };

    document.querySelectorAll('.js-view-photos').forEach((button) => {
        button.addEventListener('click', () => {
            let payload = [];
            try {
                payload = JSON.parse(button.getAttribute('data-photos') || '[]');
            } catch (error) {
                payload = [];
            }

            photos = Array.isArray(payload) ? payload.filter((item) => String(item || '').trim() !== '') : [];
            currentIndex = 0;
            renderPhoto();

            if (typeof $ !== 'undefined') {
                $('#modalPhotoPreview').modal('show');
            }
        });
    });

    prevButton.addEventListener('click', () => {
        if (!photos.length) {
            return;
        }

        currentIndex = (currentIndex - 1 + photos.length) % photos.length;
        renderPhoto();
    });

    nextButton.addEventListener('click', () => {
        if (!photos.length) {
            return;
        }

        currentIndex = (currentIndex + 1) % photos.length;
        renderPhoto();
    });

    if (typeof $ !== 'undefined') {
        $('#modalPhotoPreview').on('hidden.bs.modal', () => {
            photos = [];
            currentIndex = 0;
            renderPhoto();
        });
    }
})();

(() => {
    const form = document.getElementById('formHarian');
    const modalTitle = document.getElementById('harianModalTitle');
    const reportIdInput = document.getElementById('harianReportId');
    const sectionsContainer = document.getElementById('dailySections');
    const sectionTemplate = document.getElementById('sectionTemplate');
    const addButton = document.getElementById('btnAddSection');
    const reportDateInput = document.getElementById('report_date');
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const getLocationButton = document.getElementById('btnGetLocation');
    const pekerjaInput = document.getElementById('personil_pekerja');
    const tukangInput = document.getElementById('personil_tukang');
    const cuacaCerahInput = document.getElementById('cuaca_cerah');
    const cuacaHujanInput = document.getElementById('cuaca_hujan');
    const cuacaCerahStartInput = document.getElementById('cuaca_cerah_start');
    const cuacaCerahEndInput = document.getElementById('cuaca_cerah_end');
    const cuacaHujanStartInput = document.getElementById('cuaca_hujan_start');
    const cuacaHujanEndInput = document.getElementById('cuaca_hujan_end');

    if (!form || !modalTitle || !reportIdInput || !sectionsContainer || !sectionTemplate || !addButton || !latitudeInput || !longitudeInput || !getLocationButton || !cuacaCerahInput || !cuacaHujanInput || !cuacaCerahStartInput || !cuacaCerahEndInput || !cuacaHujanStartInput || !cuacaHujanEndInput) {
        return;
    }

    const today = '<?= esc(date('Y-m-d')); ?>';

    const parseTimeRange = (value) => {
        const text = String(value || '').trim();
        const match = text.match(/(\d{2}:\d{2})\s*(?:-|s\/d)\s*(\d{2}:\d{2})/i);
        if (match) {
            return { start: match[1], end: match[2] };
        }

        const exact = text.match(/^(\d{2}:\d{2})$/);
        if (exact) {
            return { start: exact[1], end: '' };
        }

        return { start: '', end: '' };
    };

    const buildTimeRangeValue = (startInput, endInput) => {
        const start = String(startInput.value || '').trim();
        const end = String(endInput.value || '').trim();

        if (start && end) {
            return `${start} - ${end}`;
        }

        if (start) {
            return start;
        }

        return '';
    };

    const resetForm = () => {
        form.reset();
        reportIdInput.value = '';
        form.action = '<?= site_url('admin/laporan/harian/tambah'); ?>';
        modalTitle.textContent = 'Tambah Laporan Harian';
        if (reportDateInput) {
            reportDateInput.value = today;
        }
        latitudeInput.value = '';
        longitudeInput.value = '';
        cuacaCerahStartInput.value = '';
        cuacaCerahEndInput.value = '';
        cuacaHujanStartInput.value = '';
        cuacaHujanEndInput.value = '';
        cuacaCerahInput.value = '';
        cuacaHujanInput.value = '';
        sectionsContainer.innerHTML = '';
        addSection();
    };

    const addSection = (section = {}) => {
        const node = sectionTemplate.content.cloneNode(true);
        const row = node.querySelector('.daily-section-row');
        const titleInput = node.querySelector('input[name="section_title[]"]');
        const itemsInput = node.querySelector('textarea[name="section_items[]"]');
        const removeButton = node.querySelector('.js-remove-section');

        titleInput.value = section.title || '';
        itemsInput.value = Array.isArray(section.items) ? section.items.join('\n') : (section.items || '');

        removeButton.addEventListener('click', () => {
            row.remove();
            if (!sectionsContainer.querySelector('.daily-section-row')) {
                addSection();
            }
        });

        sectionsContainer.appendChild(node);
    };

    addButton.addEventListener('click', () => addSection());

    const setLocationButtonState = (loading) => {
        getLocationButton.disabled = loading;
        getLocationButton.textContent = loading ? 'Mengambil lokasi...' : 'Ambil Koordinat Lokasi';
    };

    const isLocalhost = ['localhost', '127.0.0.1', '::1'].includes(window.location.hostname);
    const randomInRange = (min, max) => (min + Math.random() * (max - min)).toFixed(7);
    const setRandomCoordinates = () => {
        latitudeInput.value = randomInRange(-90, 90);
        longitudeInput.value = randomInRange(-180, 180);
    };

    getLocationButton.addEventListener('click', () => {
        if (isLocalhost) {
            setRandomCoordinates();
            return;
        }

        if (!navigator.geolocation) {
            window.alert('Browser tidak mendukung pengambilan lokasi.');
            return;
        }

        setLocationButtonState(true);
        navigator.geolocation.getCurrentPosition(
            (position) => {
                latitudeInput.value = Number(position.coords.latitude || 0).toFixed(7);
                longitudeInput.value = Number(position.coords.longitude || 0).toFixed(7);
                setLocationButtonState(false);
            },
            () => {
                setLocationButtonState(false);
                window.alert('Lokasi gagal diambil. Pastikan izin lokasi di browser sudah diizinkan.');
            },
            {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0,
            }
        );
    });

    document.querySelectorAll('.js-edit-harian').forEach((button) => {
        button.addEventListener('click', () => {
            let payload = {};
            try {
                payload = JSON.parse(button.getAttribute('data-report') || '{}');
            } catch (error) {
                payload = {};
            }
            form.reset();
            reportIdInput.value = payload.id || '';
            form.action = '<?= site_url('admin/laporan/harian/tambah'); ?>';
            modalTitle.textContent = 'Edit Laporan Harian';
            sectionsContainer.innerHTML = '';

            if (reportDateInput) reportDateInput.value = payload.report_date || '';
            if (latitudeInput) latitudeInput.value = payload.latitude || '';
            if (longitudeInput) longitudeInput.value = payload.longitude || '';
            if (pekerjaInput) pekerjaInput.value = payload.personil_pekerja || '';
            if (tukangInput) tukangInput.value = payload.personil_tukang || '';
            if (cuacaCerahInput) cuacaCerahInput.value = payload.cuaca_cerah || '';
            if (cuacaHujanInput) cuacaHujanInput.value = payload.cuaca_hujan || '';

            const cerahRange = parseTimeRange(payload.cuaca_cerah || '');
            cuacaCerahStartInput.value = cerahRange.start;
            cuacaCerahEndInput.value = cerahRange.end;

            const hujanRange = parseTimeRange(payload.cuaca_hujan || '');
            cuacaHujanStartInput.value = hujanRange.start;
            cuacaHujanEndInput.value = hujanRange.end;

            const sections = Array.isArray(payload.sections) && payload.sections.length ? payload.sections : [{}];
            sections.forEach((section) => addSection(section));
        });
    });

    if (typeof $ !== 'undefined') {
        $('#modalHarianForm').on('hidden.bs.modal', resetForm);
    }

    form.addEventListener('submit', (event) => {
        cuacaCerahInput.value = buildTimeRangeValue(cuacaCerahStartInput, cuacaCerahEndInput);
        cuacaHujanInput.value = buildTimeRangeValue(cuacaHujanStartInput, cuacaHujanEndInput);

        if (isLocalhost && (!latitudeInput.value || !longitudeInput.value)) {
            setRandomCoordinates();
        }

        if (!latitudeInput.value || !longitudeInput.value) {
            event.preventDefault();
            window.alert('Ambil koordinat lokasi terlebih dahulu sebelum menyimpan.');
        }
    });

    addSection();
})();
</script>
<?= $this->endSection(); ?>
