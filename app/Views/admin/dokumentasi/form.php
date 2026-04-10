<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title"><?= esc($pageTitle ?? 'Form Kegiatan Lapangan'); ?></h3>
    </div>
    <form action="<?= site_url($actionUrl); ?>" method="post" enctype="multipart/form-data" id="kegiatanLapanganForm" data-skip-confirm="1">
        <div class="card-body">
            <?= csrf_field(); ?>

            <div class="form-group">
                <label for="title">Judul Kegiatan</label>
                <input type="text" id="title" name="title" class="form-control" value="<?= old('title', $activity['title'] ?? ''); ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="activity_date">Tanggal Kegiatan</label>
                    <input type="date" id="activity_date" name="activity_date" class="form-control" value="<?= old('activity_date', isset($activity['activity_date']) ? date('Y-m-d', strtotime($activity['activity_date'])) : ''); ?>" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="location">Lokasi Kegiatan</label>
                    <input type="text" id="location" name="location" class="form-control" value="<?= old('location', $activity['location'] ?? ''); ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label for="compression_percent">Persentase Kompresi Foto</label>
                <select id="compression_percent" class="form-control">
                    <?php foreach ([30, 40, 50, 60, 70, 80, 90, 100] as $percent): ?>
                        <option value="<?= $percent; ?>" <?= $percent === 30 ? 'selected' : ''; ?>><?= $percent; ?>%</option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted d-block mt-1">Semakin kecil nilainya, semakin kecil ukuran foto hasil proses di browser. Contoh: 30 berarti ukuran foto dibuat 30% dari ukuran asli.</small>
            </div>
            <div class="form-group mb-4">
                <label class="d-block">Foto Kegiatan</label>
                <input type="file" id="activity_photos" name="activity_photos[]" accept="image/*" multiple class="d-none">
                <div class="photo-dropzone" id="photoDropzone">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap:12px;">
                        <div>
                            <strong>Tambah foto kegiatan</strong>
                            <div class="text-muted small">Klik tombol di bawah atau seret beberapa foto ke area ini.</div>
                        </div>
                        <div class="text-right">
                            <button type="button" class="btn btn-outline-primary" id="btnPickPhotos">Pilih Foto</button>
                            <div class="small text-muted mt-1" id="photoCounter"><?= (int) ($existingPhotoCount ?? 0); ?>/50 foto</div>
                        </div>
                    </div>
                    <div class="photo-dropzone__target text-center text-muted">
                        Drop foto di sini untuk upload cepat. Foto akan dikompres otomatis sebelum dikirim.
                    </div>
                    <div class="mt-3 d-flex flex-wrap" id="selectedPhotoPreview"></div>
                </div>
                <small class="text-muted d-block mt-2">Sisa kapasitas upload akan menyesuaikan jumlah foto yang sudah ada pada kegiatan ini.</small>
            </div>

            <?php if (! empty($photos)): ?>
                <div class="form-group">
                    <label class="d-block">Foto yang Sudah Ada</label>
                    <div class="existing-photo-grid">
                        <?php foreach ($photos as $photo): ?>
                            <figure class="existing-photo-card">
                                <img src="<?= esc((string) ($photo['photo_path'] ?? '')); ?>" alt="Foto kegiatan">
                                <figcaption><?= esc((string) ($photo['photo_name'] ?? 'Foto')); ?></figcaption>
                            </figure>
                        <?php endforeach; ?>
                    </div>
                    <small class="text-muted d-block mt-2">Foto lama tetap dipertahankan. Unggah foto baru untuk menambah dokumentasi.</small>
                </div>
            <?php endif; ?>

            <div class="form-group mb-0">
                <label for="created_by">Dibuat Oleh</label>
                <input type="text" id="created_by" class="form-control" value="<?= esc((string) (session()->get('fullName') ?: session()->get('username') ?: 'system')); ?>" readonly>
                <small class="text-muted">Nilai ini diisi otomatis dari sesi login.</small>
            </div>
        </div>
        <div class="card-footer d-flex flex-wrap justify-content-between align-items-center" style="gap:12px;">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="<?= site_url('/admin/dokumentasi/kegiatan-lapangan'); ?>" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>
<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<style>
    .photo-dropzone {
        border: 1px dashed #cfd7e3;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        border-radius: 16px;
        padding: 16px;
        transition: border-color .15s ease, background-color .15s ease, transform .15s ease;
    }

    .photo-dropzone.is-dragover {
        border-color: var(--app-primary);
        background: #eef6ff;
        transform: translateY(-1px);
    }

    .photo-dropzone__target {
        border-radius: 12px;
        border: 1px solid #e6ecf3;
        padding: 26px 18px;
        background: #fff;
    }

    #selectedPhotoPreview,
    .existing-photo-grid {
        gap: 10px;
    }

    .selected-photo-card {
        width: 126px;
        border: 1px solid #dbe3ee;
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
    }

    .selected-photo-card__preview {
        position: relative;
        width: 100%;
        height: 126px;
        background: #f8fafc;
    }

    .selected-photo-card__preview img,
    .existing-photo-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .selected-photo-card__remove {
        position: absolute;
        top: 6px;
        right: 6px;
        width: 26px;
        height: 26px;
        border: 0;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.8);
        color: #fff;
        font-size: 16px;
        line-height: 26px;
        padding: 0;
        cursor: pointer;
    }

    .selected-photo-card__meta {
        padding: 8px 10px 10px;
        font-size: 11px;
        line-height: 1.35;
        color: #475569;
        word-break: break-word;
    }

    .selected-photo-card__name {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 4px;
    }

    .existing-photo-grid {
        display: flex;
        flex-wrap: wrap;
    }

    .existing-photo-card {
        width: 138px;
        border: 1px solid #dbe3ee;
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
        margin: 0;
    }

    .existing-photo-card img {
        height: 110px;
    }

    .existing-photo-card figcaption {
        padding: 8px 10px;
        font-size: 11px;
        color: #475569;
        word-break: break-word;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('kegiatanLapanganForm');
    const fileInput = document.getElementById('activity_photos');
    const pickButton = document.getElementById('btnPickPhotos');
    const dropzone = document.getElementById('photoDropzone');
    const previewContainer = document.getElementById('selectedPhotoPreview');
    const counter = document.getElementById('photoCounter');
    const compressionPercentInput = document.getElementById('compression_percent');
    const maxPhotos = 50;
    const existingPhotoCount = <?= (int) ($existingPhotoCount ?? 0); ?>;
    const selectedItems = [];
    const actionUrl = <?= json_encode(site_url($actionUrl), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    const submitButton = form?.querySelector('button[type="submit"]');

    const supportsDataTransfer = typeof DataTransfer !== 'undefined';

    const showNotice = (message, type = 'info') => {
        let notice = document.getElementById('photoUploadNotice');
        if (!notice) {
            notice = document.createElement('div');
            notice.id = 'photoUploadNotice';
            notice.className = 'alert alert-info mt-3 mb-0';
            dropzone.appendChild(notice);
        }

        notice.className = type === 'danger' ? 'alert alert-danger mt-3 mb-0' : 'alert alert-info mt-3 mb-0';
        notice.textContent = message;
    };

    const syncFileInput = () => {
        if (!supportsDataTransfer) {
            return;
        }

        const dataTransfer = new DataTransfer();
        selectedItems.forEach((item) => dataTransfer.items.add(item.file));
        fileInput.files = dataTransfer.files;
    };

    const updateCounter = () => {
        if (counter) {
            counter.textContent = `${existingPhotoCount + selectedItems.length}/${maxPhotos} foto`;
        }
    };

    const renderPreview = () => {
        previewContainer.innerHTML = '';

        selectedItems.forEach((item, index) => {
            const card = document.createElement('div');
            card.className = 'selected-photo-card';
            card.innerHTML = `
                <div class="selected-photo-card__preview">
                    <img src="${item.previewUrl}" alt="${item.name}">
                    <button type="button" class="selected-photo-card__remove" aria-label="Hapus foto">&times;</button>
                </div>
                <div class="selected-photo-card__meta">
                    <div class="selected-photo-card__name">${item.name}</div>
                    <div>${item.sizeLabel}</div>
                </div>
            `;

            card.querySelector('.selected-photo-card__remove')?.addEventListener('click', () => {
                URL.revokeObjectURL(item.previewUrl);
                selectedItems.splice(index, 1);
                syncFileInput();
                renderPreview();
                updateCounter();
            });

            previewContainer.appendChild(card);
        });
    };

    const resizeFile = (file, scaleFactor = 0.1) => new Promise((resolve, reject) => {
        const image = new Image();
        const objectUrl = URL.createObjectURL(file);

        image.onload = () => {
            const width = Math.max(1, Math.round(image.width * scaleFactor));
            const height = Math.max(1, Math.round(image.height * scaleFactor));
            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;

            const context = canvas.getContext('2d');
            if (!context) {
                URL.revokeObjectURL(objectUrl);
                reject(new Error('Canvas context unavailable'));
                return;
            }

            context.imageSmoothingEnabled = true;
            context.imageSmoothingQuality = 'high';
            context.drawImage(image, 0, 0, width, height);

            const outputType = file.type === 'image/png' ? 'image/png' : 'image/jpeg';
            canvas.toBlob((blob) => {
                URL.revokeObjectURL(objectUrl);

                if (!blob) {
                    reject(new Error('Resize failed'));
                    return;
                }

                const extension = outputType === 'image/png' ? 'png' : 'jpg';
                const baseName = (file.name || 'foto').replace(/\.[^.]+$/, '');
                const newName = `${baseName}-sm.${extension}`;
                resolve(new File([blob], newName, { type: outputType, lastModified: Date.now() }));
            }, outputType, outputType === 'image/jpeg' ? 0.86 : undefined);
        };

        image.onerror = () => {
            URL.revokeObjectURL(objectUrl);
            reject(new Error('Cannot read image'));
        };

        image.src = objectUrl;
    });

    const addFiles = (fileList) => {
        const incomingFiles = Array.from(fileList || []).filter((file) => file && file.type && file.type.startsWith('image/'));
        if (incomingFiles.length === 0) {
            showNotice('Silakan pilih file gambar yang valid.', 'danger');
            return;
        }

        const remainingSlots = maxPhotos - existingPhotoCount - selectedItems.length;
        if (remainingSlots <= 0) {
            showNotice('Batas 50 foto sudah tercapai untuk kegiatan ini.', 'danger');
            return;
        }

        if (incomingFiles.length > remainingSlots) {
            showNotice(`Jumlah foto melebihi batas. Foto yang bisa ditambahkan tinggal ${remainingSlots}.`, 'danger');
            incomingFiles.length = remainingSlots;
        }

        for (const file of incomingFiles) {
            const previewUrl = URL.createObjectURL(file);
            selectedItems.push({
                file,
                previewUrl,
                name: file.name,
                sizeLabel: `${Math.max(1, Math.round(file.size / 1024))} KB (kompresi saat simpan)`,
            });
        }

        syncFileInput();
        renderPreview();
        updateCounter();
        showNotice('Preview foto ditampilkan cepat. Kompresi dilakukan saat Anda klik Simpan.', 'info');
    };

    const buildProgressHtml = () => `
        <div class="text-left">
            <div class="mb-2">Sedang mengunggah data dan foto...</div>
            <div class="progress" style="height: 12px;">
                <div class="progress-bar" id="uploadProgressBar" role="progressbar" style="width: 0%">0%</div>
            </div>
            <div class="small text-muted mt-2" id="uploadProgressText">Menyiapkan file...</div>
        </div>
    `;

    const setProgress = (percent, label) => {
        const progressBar = document.getElementById('uploadProgressBar');
        const progressText = document.getElementById('uploadProgressText');

        if (progressBar) {
            const safePercent = Math.max(0, Math.min(100, Math.round(percent)));
            progressBar.style.width = `${safePercent}%`;
            progressBar.textContent = `${safePercent}%`;
            progressBar.setAttribute('aria-valuenow', String(safePercent));
        }

        if (progressText && label) {
            progressText.textContent = label;
        }
    };

    const compressSelectedFiles = async (scaleFactor) => {
        const compressed = [];
        const total = selectedItems.length;

        for (let index = 0; index < total; index += 1) {
            const item = selectedItems[index];
            setProgress(5 + ((index + 1) / Math.max(total, 1)) * 35, `Memproses kompresi foto ${index + 1}/${total}...`);
            const resizedFile = await resizeFile(item.file, scaleFactor);
            compressed.push({
                file: resizedFile,
                name: resizedFile.name || item.name,
            });
        }

        return compressed;
    };

    const submitWithProgress = async () => {
        const formData = new FormData(form);
        formData.delete('activity_photos[]');
        formData.delete('activity_photos');

        const xhr = new XMLHttpRequest();

        if (submitButton) {
            submitButton.disabled = true;
        }

        Swal.fire({
            title: 'Menyimpan data',
            html: buildProgressHtml(),
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        try {
            const compressionPercent = Math.max(30, Math.min(100, Number(compressionPercentInput?.value || 30)));
            const scaleFactor = compressionPercent / 100;
            const compressedFiles = await compressSelectedFiles(scaleFactor);

            compressedFiles.forEach((item) => {
                formData.append('activity_photos[]', item.file, item.file.name || 'photo.jpg');
            });
        } catch (error) {
            if (submitButton) {
                submitButton.disabled = false;
            }

            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Gagal memproses kompresi foto sebelum upload.',
            });
            return;
        }

        xhr.open('POST', actionUrl, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.upload.addEventListener('progress', (event) => {
            if (!event.lengthComputable) {
                setProgress(40, 'Menghitung ukuran upload...');
                return;
            }

            const uploadPercent = (event.loaded / event.total) * 60;
            const totalPercent = 40 + uploadPercent;
            setProgress(totalPercent, `Mengunggah ${Math.round(totalPercent)}%`);
        });

        xhr.addEventListener('load', () => {
            if (submitButton) {
                submitButton.disabled = false;
            }

            let payload = null;
            try {
                payload = JSON.parse(xhr.responseText || '{}');
            } catch (error) {
                payload = null;
            }

            if (xhr.status >= 200 && xhr.status < 300 && payload && payload.status === 'ok') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: payload.message || 'Data berhasil disimpan.',
                    timer: 900,
                    showConfirmButton: false,
                }).then(() => {
                    window.location.href = payload.redirect || '<?= site_url('/admin/dokumentasi/kegiatan-lapangan'); ?>';
                });
                return;
            }

            const message = payload && payload.message ? payload.message : 'Gagal menyimpan data.';
            const errorDetails = payload && payload.errors && typeof payload.errors === 'object'
                ? Object.values(payload.errors).filter(Boolean).join('<br>')
                : '';
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                html: errorDetails ? `<div>${message}</div><div class="mt-2 text-left">${errorDetails}</div>` : message,
            });
        });

        xhr.addEventListener('error', () => {
            if (submitButton) {
                submitButton.disabled = false;
            }

            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Koneksi bermasalah saat mengunggah data.',
            });
        });

        xhr.addEventListener('loadend', () => {
            if (submitButton) {
                submitButton.disabled = false;
            }
        });

        xhr.send(formData);
    };

    pickButton?.addEventListener('click', () => fileInput.click());
    fileInput?.addEventListener('change', async () => {
        try {
            await addFiles(fileInput.files);
        } finally {
            fileInput.value = '';
        }
    });

    ['dragenter', 'dragover'].forEach((eventName) => {
        dropzone.addEventListener(eventName, (event) => {
            event.preventDefault();
            dropzone.classList.add('is-dragover');
        });
    });

    ['dragleave', 'drop'].forEach((eventName) => {
        dropzone.addEventListener(eventName, (event) => {
            event.preventDefault();
            dropzone.classList.remove('is-dragover');
        });
    });

    dropzone.addEventListener('drop', async (event) => {
        try {
            await addFiles(event.dataTransfer?.files || []);
        } catch (error) {
            showNotice('Gagal memproses salah satu foto. Silakan coba lagi.', 'danger');
        }
    });

    form?.addEventListener('submit', (event) => {
        event.preventDefault();

        if (existingPhotoCount + selectedItems.length === 0) {
            showNotice('Minimal satu foto kegiatan harus dipilih.', 'danger');
            Swal.fire({
                icon: 'warning',
                title: 'Foto belum dipilih',
                text: 'Minimal satu foto kegiatan harus dipilih.',
            });
            return;
        }

        submitWithProgress();
    });

    updateCounter();
});
</script>
<?= $this->endSection(); ?>