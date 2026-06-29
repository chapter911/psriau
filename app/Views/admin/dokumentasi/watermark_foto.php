<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('page_title') ?>
<?= $pageTitle ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title"><?= $pageTitle ?></h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <form id="watermarkForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="foto">Upload Foto <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="foto" name="foto" accept="image/jpeg,image/png,image/webp" required>
                                <label class="custom-file-label" for="foto">Pilih file...</label>
                            </div>
                        </div>
                        <small class="text-muted">Format: JPG, PNG, WebP. Maks: 10MB</small>
                    </div>

                    <div class="form-group">
                        <label for="jam">Jam <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" id="jam" name="jam" required value="<?= date('H:i') ?>">
                    </div>

                    <div class="form-group">
                        <label for="lokasi">Lokasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="lokasi" name="lokasi" placeholder="Contoh: Kantor Pusat PSG" required>
                    </div>

                    <div class="form-group">
                        <label for="logo">Logo (Opsional)</label>
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="logo" name="logo" accept="image/png,image/jpeg,image/webp">
                                <label class="custom-file-label" for="logo">Pilih file...</label>
                            </div>
                        </div>
                        <small class="text-muted">Format: PNG, JPG, WebP. Logo akan ditempatkan di pojok kanan bawah.</small>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary" id="btnProses">
                            <i class="fas fa-image"></i> Proses Watermark
                        </button>
                        <button type="button" class="btn btn-success" id="btnDownload" style="display: none;">
                            <i class="fas fa-download"></i> Download Foto
                        </button>
                        <button type="button" class="btn btn-secondary" id="btnReset" style="display: none;">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                    </div>
                </form>
            </div>

            <div class="col-md-6">
                <div class="text-center" id="previewContainer" style="display: none;">
                    <h5>Preview Hasil</h5>
                    <div class="position-relative d-inline-block">
                        <img id="previewImage" src="" alt="Preview" class="img-fluid rounded shadow" style="max-height: 500px;">
                    </div>
                </div>

                <div class="text-center text-muted" id="placeholderContainer">
                    <div class="my-5 py-5">
                        <i class="fas fa-image fa-5x text-secondary mb-3"></i>
                        <p>Upload foto untuk melihat preview watermark</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(function() {
    let generatedImageData = null;

    // Update label file input
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass('selected').html(fileName || 'Pilih file...');
    });

    // Handle form submit
    $('#watermarkForm').on('submit', function(e) {
        e.preventDefault();

        var formData = new FormData(this);
        var $btn = $('#btnProses');
        var originalText = $btn.html();

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
        $('#previewContainer').hide();
        $('#placeholderContainer').show();

        $.ajax({
            url: '<?= site_url('/admin/dokumentasi/watermark-foto/proses') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.status === 'ok') {
                    generatedImageData = response.image_data;
                    $('#previewImage').attr('src', response.image_data);
                    $('#previewContainer').fadeIn();
                    $('#placeholderContainer').hide();
                    $('#btnDownload').show();
                    $('#btnReset').show();

                    // Update CSRF
                    if (response.csrf_hash) {
                        $('input[name="<?= csrf_token() ?>"]').val(response.csrf_hash);
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                var message = 'Terjadi kesalahan saat memproses.';
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        message = response.message;
                    }
                } catch(e) {}
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: message
                });
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Handle download
    $('#btnDownload').on('click', function() {
        if (!generatedImageData) {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: 'Silakan proses watermark terlebih dahulu.'
            });
            return;
        }

        // Convert base64 to blob and download
        var link = document.createElement('a');
        link.href = generatedImageData;
        link.download = 'foto-watermark-<?= date('YmdHis') ?>.jpg';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    // Handle reset
    $('#btnReset').on('click', function() {
        $('#watermarkForm')[0].reset();
        $('.custom-file-label').html('Pilih file...');
        $('#previewContainer').hide();
        $('#placeholderContainer').show();
        $('#btnDownload').hide();
        $(this).hide();
        generatedImageData = null;
    });
});
</script>
<?= $this->endSection() ?>
