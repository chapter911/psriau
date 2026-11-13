<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">
    <!-- Info Box KPI Ringkasan Aset BMN -->
    <div class="row mb-3">
        <div class="col-12 col-sm-6 col-md-4">
            <div class="info-box shadow-sm mb-3">
                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-boxes"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted">Total Aset BMN</span>
                    <span class="info-box-number text-dark" style="font-size: 1.4rem;"><?= number_format($totalAset ?? 0); ?></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <div class="info-box shadow-sm mb-3">
                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted">Sudah Ditempatkan</span>
                    <span class="info-box-number text-success" style="font-size: 1.4rem;"><?= number_format($totalSudah ?? 0); ?></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <div class="info-box shadow-sm mb-3">
                <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-clock text-white"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted">Belum Ditempatkan</span>
                    <span class="info-box-number text-warning" style="font-size: 1.4rem;"><?= number_format($totalBelum ?? 0); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card & Data Table -->
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="font-weight-bold text-muted text-xs mb-1"><i class="fas fa-layer-group mr-1"></i>Paket Pekerjaan</label>
                    <select id="filter_paket" class="form-control form-control-sm select2">
                        <option value="">-- Semua Paket --</option>
                        <?php foreach (($pakets ?? []) as $pkt): ?>
                            <option value="<?= esc($pkt['id']); ?>"><?= esc($pkt['nama_paket']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-2 mb-md-0">
                    <label class="font-weight-bold text-muted text-xs mb-1"><i class="fas fa-school mr-1"></i>Sekolah Target</label>
                    <select id="filter_sekolah" class="form-control form-control-sm select2">
                        <option value="">-- Semua Sekolah --</option>
                        <?php foreach (($sekolahs ?? []) as $sek): ?>
                            <option value="<?= esc($sek['npsn']); ?>"><?= esc($sek['nama']); ?> (<?= esc($sek['npsn']); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="font-weight-bold text-muted text-xs mb-1"><i class="fas fa-filter mr-1"></i>Status</label>
                    <select id="filter_status" class="form-control form-control-sm">
                        <option value="">-- Semua --</option>
                        <option value="sudah_ditempatkan">Ditempatkan</option>
                        <option value="belum_ditempatkan">Belum Ditempatkan</option>
                    </select>
                </div>
                <div class="col-md-3 text-md-right pt-md-3">
                    <button type="button" id="btn_apply_filter" class="btn btn-sm btn-primary mr-1">
                        <i class="fas fa-search mr-1"></i> Terapkan
                    </button>
                    <button type="button" id="btn_reset_filter" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body p-3">
            <div class="table-responsive">
                <table id="tbl_bmn_penempatan" class="table table-bordered table-striped table-hover align-middle w-100" style="font-size: 0.88rem;">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width: 40px;" class="text-center">No</th>
                            <th style="width: 140px;">Kode & NUP</th>
                            <th style="width: 160px;">Kode Register (QR)</th>
                            <th>Nama Barang / Merk</th>
                            <th style="width: 100px;" class="text-center">Kondisi</th>
                            <th style="width: 120px;" class="text-center">Status</th>
                            <th>Lokasi Fisik (Sekolah / Ruang)</th>
                            <th style="width: 140px;">Waktu & Petugas</th>
                            <th style="width: 90px;" class="text-center">Foto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dimuat secara Asinkron (AJAX Server-Side) -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Foto Preview Dokumentasi Lapangan (Asli vs Watermark) -->
<div class="modal fade" id="modalFotoPreview" tabindex="-1" role="dialog" aria-labelledby="modalFotoPreviewLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalFotoPreviewLabel"><i class="fas fa-camera mr-2"></i>Dokumentasi Foto Aset BMN</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div>
                        <h6 class="font-weight-bold text-primary mb-0" id="foto_modal_title">-</h6>
                        <small class="text-muted">Pilih tombol Watermark / Asli di bawah setiap foto untuk melihat versi foto.</small>
                    </div>
                </div>

                <div class="row" id="foto_container">
                    <!-- Foto akan dimuat dinamis -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script>
$(document).ready(function() {
    // Inisialisasi DataTables Server-Side AJAX
    const table = $('#tbl_bmn_penempatan').DataTable({
        processing: true,
        serverSide: true,
        ordering: false,
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        ajax: {
            url: "<?= base_url('admin/inventaris/penempatan'); ?>",
            type: "GET",
            data: function(d) {
                d.paket_id   = $('#filter_paket').val();
                d.sekolah_id = $('#filter_sekolah').val();
                d.status     = $('#filter_status').val();
            },
            error: function (xhr, error, thrown) {
                console.error("DataTables AJAX Error:", xhr.responseText);
            }
        },
        columns: [
            { data: "no", className: "text-center" },
            { data: "kode_nup" },
            { data: "qr_code" },
            { data: "nama_barang" },
            { data: "kondisi", className: "text-center" },
            { data: "status", className: "text-center" },
            { data: "lokasi" },
            { data: "tanggal" },
            { data: "aksi_foto", className: "text-center" }
        ],
        language: {
            processing: '<div class="spinner-border text-primary spinner-border-sm mr-2" role="status"></div> Sedang memuat data dari server...',
            lengthMenu: "Tampilkan _MENU_ baris",
            zeroRecords: "Tidak ada data aset BMN yang sesuai filter",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data BMN",
            infoEmpty: "Menampilkan 0 data",
            infoFiltered: "(disaring dari _MAX_ total data)",
            search: "Cari Aset:",
            paginate: {
                first: '<i class="fas fa-angle-double-left"></i>',
                previous: '<i class="fas fa-angle-left"></i>',
                next: '<i class="fas fa-angle-right"></i>',
                last: '<i class="fas fa-angle-double-right"></i>'
            }
        }
    });

    // Event Filter
    $('#btn_apply_filter').on('click', function() {
        table.ajax.reload();
    });

    $('#btn_reset_filter').on('click', function() {
        $('#filter_paket').val('').trigger('change');
        $('#filter_sekolah').val('').trigger('change');
        $('#filter_status').val('');
        table.ajax.reload();
    });

    // Trigger pencarian enter pada input search bawaan DataTables
    $('#tbl_bmn_penempatan_filter input').unbind().bind('keyup', function(e) {
        if (e.keyCode === 13 || this.value.length === 0 || this.value.length >= 3) {
            table.search(this.value).draw();
        }
    });

    // Event Klik Lihat Foto Dokumentasi (Asli & Watermark)
    $(document).on('click', '.btn-view-foto', function() {
        const btn = $(this);
        const title = btn.data('title') || 'Foto Dokumentasi Aset';
        $('#foto_modal_title').text(title);

        const listFoto = [];
        for (let i = 1; i <= 4; i++) {
            const wm   = btn.data('wm-' + i);
            const asli = btn.data('asli-' + i);
            if (wm || asli) {
                listFoto.push({
                    index: i,
                    wm: wm || asli,
                    asli: asli || wm
                });
            }
        }

        let html = '';
        if (listFoto.length === 0) {
            html = '<div class="col-12 text-center text-muted py-4"><i class="fas fa-image fa-2x mb-2 text-secondary"></i><br>Tidak ada file foto untuk penempatan ini.</div>';
        } else {
            listFoto.forEach(function(item) {
                html += `
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="card border shadow-none h-100 mb-0">
                            <div class="card-header p-2 bg-light d-flex justify-content-between align-items-center">
                                <span class="badge badge-secondary">Foto ${item.index}</span>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-primary btn-xs btn-toggle-mode active" data-target="img_foto_${item.index}" data-url="${item.wm}">Watermark</button>
                                    <button type="button" class="btn btn-outline-secondary btn-xs btn-toggle-mode" data-target="img_foto_${item.index}" data-url="${item.asli}">Asli</button>
                                </div>
                            </div>
                            <div class="card-body p-2 text-center bg-dark" style="border-bottom-left-radius: 4px; border-bottom-right-radius: 4px;">
                                <a href="${item.wm}" target="_blank" id="link_foto_${item.index}">
                                    <img src="${item.wm}" id="img_foto_${item.index}" class="img-fluid rounded" style="max-height: 220px; object-fit: contain;" alt="Foto BMN ${item.index}">
                                </a>
                            </div>
                        </div>
                    </div>
                `;
            });
        }

        $('#foto_container').html(html);
        $('#modalFotoPreview').modal('show');
    });

    // Toggle Foto Asli vs Watermark
    $(document).on('click', '.btn-toggle-mode', function() {
        const btn = $(this);
        const targetId = btn.data('target');
        const newUrl = btn.data('url');

        btn.parent().find('.btn-toggle-mode').removeClass('active btn-primary').addClass('btn-outline-secondary');
        btn.addClass('active btn-primary').removeClass('btn-outline-secondary');

        $('#' + targetId).attr('src', newUrl);
        $('#' + targetId).parent('a').attr('href', newUrl);
    });
});
</script>
<?= $this->endSection(); ?>
