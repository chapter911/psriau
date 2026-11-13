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
                    <span class="info-box-number text-dark" id="kpi_total_aset" style="font-size: 1.4rem;"><?= number_format($totalAset ?? 0); ?></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <div class="info-box shadow-sm mb-3">
                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted">Sudah Ditempatkan</span>
                    <span class="info-box-number text-success" id="kpi_total_sudah" style="font-size: 1.4rem;"><?= number_format($totalSudah ?? 0); ?></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <div class="info-box shadow-sm mb-3">
                <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-clock text-white"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted">Belum Ditempatkan</span>
                    <span class="info-box-number text-warning" id="kpi_total_belum" style="font-size: 1.4rem;"><?= number_format($totalBelum ?? 0); ?></span>
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
                            <th style="width: 135px;" class="text-center">Aksi</th>
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

<!-- Modal Pindah Lokasi Penempatan (Relokasi) -->
<div class="modal fade" id="modalPindahLokasi" tabindex="-1" role="dialog" aria-labelledby="modalPindahLokasiLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow">
            <form id="formPindahLokasi">
                <input type="hidden" name="inventaris_id" id="pindah_inventaris_id" value="">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="modalPindahLokasiLabel">
                        <i class="fas fa-exchange-alt mr-2"></i>Pindah Lokasi Penempatan Aset BMN
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <!-- Info Aset Terpilih -->
                    <div class="card bg-light border-0 mb-3">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-md-7">
                                    <span class="text-muted text-xs d-block">Nama Barang:</span>
                                    <h6 class="font-weight-bold text-dark mb-1" id="pindah_nama_barang">-</h6>
                                    <span class="badge badge-primary mr-1" id="pindah_kode_barang">Kd: -</span>
                                    <span class="badge badge-warning" id="pindah_nup">NUP: -</span>
                                </div>
                                <div class="col-md-5 mt-2 mt-md-0 border-left-md">
                                    <span class="text-muted text-xs d-block">Lokasi Terkini:</span>
                                    <span class="font-weight-bold text-info" id="pindah_lokasi_terkini">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Target Lokasi Baru -->
                    <h6 class="text-dark font-weight-bold border-bottom pb-2 mb-3">
                        <i class="fas fa-map-marker-alt text-danger mr-1"></i>Tentukan Lokasi Baru
                    </h6>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark text-sm mb-1">Sekolah Sasaran <span class="text-danger">*</span></label>
                        <select name="sekolah_id" id="pindah_sekolah_id" class="form-control" required style="width: 100%;">
                            <option value="">-- Pilih Sekolah --</option>
                            <?php foreach (($sekolahs ?? []) as $sek): ?>
                                <option value="<?= esc($sek['npsn']); ?>"><?= esc($sek['nama']); ?> (<?= esc($sek['npsn']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="font-weight-bold text-dark text-sm mb-1">Gedung / Area</label>
                                <select name="gedung_id" id="pindah_gedung_id" class="form-control">
                                    <option value="">-- Pilih Gedung --</option>
                                    <option value="luar_gedung">[Area Luar / Terbuka (Tanpa Gedung)]</option>
                                    <option value="baru">+ Tambah Gedung Baru...</option>
                                </select>
                            </div>
                            <div class="form-group mb-3 d-none" id="wrap_gedung_baru">
                                <input type="text" name="nama_gedung_baru" id="pindah_nama_gedung_baru" class="form-control form-control-sm" placeholder="Ketik nama gedung baru...">
                                <small class="text-muted">Gedung baru akan disimpan otomatis.</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="font-weight-bold text-dark text-sm mb-1">Ruangan</label>
                                <select name="ruangan_id" id="pindah_ruangan_id" class="form-control">
                                    <option value="">-- Pilih Ruangan --</option>
                                    <option value="tanpa_ruangan">[Tanpa Ruangan / Terbuka]</option>
                                    <option value="baru">+ Tambah Ruangan Baru...</option>
                                </select>
                            </div>
                            <div class="form-group mb-3 d-none" id="wrap_ruangan_baru">
                                <input type="text" name="nama_ruangan_baru" id="pindah_nama_ruangan_baru" class="form-control form-control-sm" placeholder="Ketik nama ruangan baru...">
                                <small class="text-muted">Ruangan baru akan disimpan otomatis.</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark text-sm mb-1">Kondisi Barang Saat Ini</label>
                                <select name="kondisi" id="pindah_kondisi" class="form-control">
                                    <option value="baik">Baik</option>
                                    <option value="rusak_ringan">Rusak Ringan</option>
                                    <option value="rusak_berat">Rusak Berat</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark text-sm mb-1">Alasan / Catatan Pemindahan</label>
                                <textarea name="catatan" id="pindah_catatan" class="form-control" rows="2" placeholder="Contoh: Dipindahkan untuk kebutuhan laboratorium komputer..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Batal
                    </button>
                    <button type="submit" id="btn_submit_pindah" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Simpan Pemindahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('pageScripts'); ?>
<script>
$(document).ready(function() {
    let currentCsrfName = '<?= csrf_token(); ?>';
    let currentCsrfHash = '<?= csrf_hash(); ?>';

    function updateCsrf(hash) {
        if (hash) {
            currentCsrfHash = hash;
        }
    }

    // Fungsi refresh box KPI di atas tabel
    function refreshKpi() {
        $.getJSON("<?= base_url('admin/inventaris/penempatan/ajax-kpi'); ?>", function(res) {
            if (res && res.status === 'success') {
                $('#kpi_total_aset').text(res.totalAset);
                $('#kpi_total_sudah').text(res.totalSudah);
                $('#kpi_total_belum').text(res.totalBelum);
            }
        });
    }

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
            { data: "aksi", className: "text-center" }
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

    // ==========================================
    // LOGIKA RELOKASI / PINDAH LOKASI PENEMPATAN
    // ==========================================

    function loadGedungBySekolah(sekolahId, selectedGedungId = null, callback = null) {
        const $gedung = $('#pindah_gedung_id');
        $gedung.html('<option value="">Memuat gedung...</option>');
        $('#wrap_gedung_baru').addClass('d-none');
        $('#pindah_nama_gedung_baru').val('');

        const $ruangan = $('#pindah_ruangan_id');
        $ruangan.html('<option value="">-- Pilih Ruangan --</option><option value="tanpa_ruangan">[Tanpa Ruangan / Terbuka]</option><option value="baru">+ Tambah Ruangan Baru...</option>');
        $('#wrap_ruangan_baru').addClass('d-none');
        $('#pindah_nama_ruangan_baru').val('');

        if (!sekolahId) {
            $gedung.html('<option value="">-- Pilih Gedung --</option><option value="luar_gedung">[Area Luar / Terbuka (Tanpa Gedung)]</option><option value="baru">+ Tambah Gedung Baru...</option>');
            if (callback) callback();
            return;
        }

        $.getJSON("<?= base_url('admin/inventaris/penempatan/ajax-gedung'); ?>/" + encodeURIComponent(sekolahId), function(res) {
            let options = '<option value="">-- Pilih Gedung --</option>';
            options += '<option value="luar_gedung">[Area Luar / Terbuka (Tanpa Gedung)]</option>';
            if (res && res.data && res.data.length > 0) {
                res.data.forEach(function(g) {
                    const sel = (selectedGedungId && String(selectedGedungId) === String(g.id)) ? 'selected' : '';
                    options += `<option value="${g.id}" ${sel}>${g.nama_gedung}</option>`;
                });
            }
            options += '<option value="baru">+ Tambah Gedung Baru...</option>';
            $gedung.html(options);

            if (callback) callback();
        }).fail(function() {
            $gedung.html('<option value="">-- Gagal memuat gedung --</option><option value="luar_gedung">[Area Luar / Terbuka (Tanpa Gedung)]</option><option value="baru">+ Tambah Gedung Baru...</option>');
            if (callback) callback();
        });
    }

    function loadRuanganByGedung(gedungId, selectedRuanganId = null, callback = null) {
        const $ruangan = $('#pindah_ruangan_id');
        $ruangan.html('<option value="">Memuat ruangan...</option>');
        $('#wrap_ruangan_baru').addClass('d-none');
        $('#pindah_nama_ruangan_baru').val('');

        if (!gedungId || gedungId === 'luar_gedung') {
            $ruangan.html('<option value="tanpa_ruangan" selected>[Tanpa Ruangan / Terbuka]</option>');
            if (callback) callback();
            return;
        }

        if (gedungId === 'baru') {
            $ruangan.html('<option value="">-- Pilih Ruangan --</option><option value="tanpa_ruangan">[Tanpa Ruangan / Terbuka]</option><option value="baru">+ Tambah Ruangan Baru...</option>');
            if (callback) callback();
            return;
        }

        $.getJSON("<?= base_url('admin/inventaris/penempatan/ajax-ruangan'); ?>/" + encodeURIComponent(gedungId), function(res) {
            let options = '<option value="">-- Pilih Ruangan --</option>';
            options += '<option value="tanpa_ruangan">[Tanpa Ruangan / Terbuka]</option>';
            if (res && res.data && res.data.length > 0) {
                res.data.forEach(function(r) {
                    const sel = (selectedRuanganId && String(selectedRuanganId) === String(r.id)) ? 'selected' : '';
                    options += `<option value="${r.id}" ${sel}>${r.nama_ruangan}</option>`;
                });
            }
            options += '<option value="baru">+ Tambah Ruangan Baru...</option>';
            $ruangan.html(options);

            if (callback) callback();
        }).fail(function() {
            $ruangan.html('<option value="">-- Gagal memuat ruangan --</option><option value="tanpa_ruangan">[Tanpa Ruangan / Terbuka]</option><option value="baru">+ Tambah Ruangan Baru...</option>');
            if (callback) callback();
        });
    }

    // Klik tombol Pindah Lokasi
    $(document).on('click', '.btn-pindah-lokasi', function() {
        const btn = $(this);
        const invId     = btn.data('id');
        const nama      = btn.data('nama');
        const kode      = btn.data('kode');
        const nup       = btn.data('nup');
        const sekId     = btn.data('sekolah-id');
        const gedId     = btn.data('gedung-id');
        const ruanId    = btn.data('ruangan-id');
        const kondisi   = btn.data('kondisi') || 'baik';
        const lokasiTxt = btn.data('lokasi-text') || '-';

        $('#pindah_inventaris_id').val(invId);
        $('#pindah_nama_barang').text(nama);
        $('#pindah_kode_barang').text('Kd: ' + kode);
        $('#pindah_nup').text('NUP: ' + nup);
        $('#pindah_lokasi_terkini').text(lokasiTxt);
        $('#pindah_kondisi').val(kondisi);
        $('#pindah_catatan').val('');

        $('#pindah_sekolah_id').val(sekId || '');

        loadGedungBySekolah(sekId, gedId, function() {
            if (gedId) {
                loadRuanganByGedung(gedId, ruanId);
            }
        });

        $('#modalPindahLokasi').modal('show');
    });

    // Cascading dropdown Sekolah -> Gedung
    $('#pindah_sekolah_id').on('change', function() {
        const sekolahId = $(this).val();
        loadGedungBySekolah(sekolahId);
    });

    // Cascading dropdown Gedung -> Ruangan & Input manual gedung
    $('#pindah_gedung_id').on('change', function() {
        const gedungVal = $(this).val();
        if (gedungVal === 'baru') {
            $('#wrap_gedung_baru').removeClass('d-none');
            $('#pindah_nama_gedung_baru').focus();
        } else {
            $('#wrap_gedung_baru').addClass('d-none');
            $('#pindah_nama_gedung_baru').val('');
        }

        loadRuanganByGedung(gedungVal);
    });

    // Input manual ruangan jika pilih Baru
    $('#pindah_ruangan_id').on('change', function() {
        const ruanganVal = $(this).val();
        if (ruanganVal === 'baru') {
            $('#wrap_ruangan_baru').removeClass('d-none');
            $('#pindah_nama_ruangan_baru').focus();
        } else {
            $('#wrap_ruangan_baru').addClass('d-none');
            $('#pindah_nama_ruangan_baru').val('');
        }
    });

    // Submit form pemindahan lokasi (AJAX)
    $('#formPindahLokasi').on('submit', function(e) {
        e.preventDefault();

        const $btn = $('#btn_submit_pindah');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...');

        const formData = $(this).serializeArray();
        formData.push({ name: currentCsrfName, value: currentCsrfHash });

        $.ajax({
            url: "<?= base_url('admin/inventaris/penempatan/pindah-lokasi'); ?>",
            type: "POST",
            data: $.param(formData),
            dataType: "json",
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Simpan Pemindahan');
                if (res && res.csrf_hash) updateCsrf(res.csrf_hash);

                $('#modalPindahLokasi').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: res.message || 'Lokasi aset berhasil dipindahkan.',
                    timer: 1500,
                    showConfirmButton: false
                });

                table.ajax.reload(null, false);
                refreshKpi();
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Simpan Pemindahan');
                if (xhr.responseJSON && xhr.responseJSON.csrf_hash) updateCsrf(xhr.responseJSON.csrf_hash);

                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan saat memindahkan lokasi aset.';
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: msg
                });
            }
        });
    });

    // ==========================================
    // LOGIKA HAPUS LOKASI PENEMPATAN ASET BMN
    // ==========================================

    $(document).on('click', '.btn-hapus-lokasi', function() {
        const btn  = $(this);
        const id   = btn.data('id');
        const nama = btn.data('nama');
        const nup  = btn.data('nup');

        Swal.fire({
            title: 'Hapus Lokasi Penempatan?',
            html: `Lokasi penempatan untuk aset <b>${nama}</b> (NUP: ${nup}) akan dihapus.<br><small class="text-muted">Status aset akan dikembalikan menjadi <b>Belum Ditempatkan</b>. Data aset master tetap aman.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i> Ya, Hapus Lokasi',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const postData = {};
                postData[currentCsrfName] = currentCsrfHash;

                $.ajax({
                    url: "<?= base_url('admin/inventaris/penempatan/hapus-lokasi'); ?>/" + id,
                    type: "POST",
                    data: postData,
                    dataType: "json",
                    success: function(res) {
                        if (res && res.csrf_hash) updateCsrf(res.csrf_hash);

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message || 'Lokasi aset berhasil dihapus.',
                            timer: 1500,
                            showConfirmButton: false
                        });

                        table.ajax.reload(null, false);
                        refreshKpi();
                    },
                    error: function(xhr) {
                        if (xhr.responseJSON && xhr.responseJSON.csrf_hash) updateCsrf(xhr.responseJSON.csrf_hash);

                        const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menghapus lokasi penempatan aset.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: msg
                        });
                    }
                });
            }
        });
    });
});
</script>
<?= $this->endSection(); ?>
