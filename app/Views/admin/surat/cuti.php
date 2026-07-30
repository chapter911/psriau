<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<style>
    .cuti-card-header {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        color: #ffffff;
        border-radius: 10px 10px 0 0;
        padding: 16px 20px;
    }
    .form-section-title {
        font-weight: 700;
        font-size: 0.95rem;
        color: #0A66C2;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 6px;
        margin-top: 15px;
        margin-bottom: 15px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .jenis-cuti-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 10px;
    }
    .jenis-cuti-item {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 10px 14px;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #f8fafc;
    }
    .jenis-cuti-item:hover {
        border-color: #0A66C2;
        background: #f0f7ff;
    }
    .jenis-cuti-item input[type="radio"]:checked + span {
        font-weight: 700;
        color: #0A66C2;
    }
    .jenis-cuti-item.selected {
        border-color: #0A66C2;
        background: #e0f2fe;
        box-shadow: 0 0 0 2px rgba(10, 102, 194, 0.2);
    }
    #modalCuti .modal-dialog {
        max-height: calc(100vh - 40px);
        margin-top: 20px;
        margin-bottom: 20px;
    }
    #modalCuti form.modal-content {
        max-height: calc(100vh - 40px);
        display: flex;
        flex-direction: column;
    }
    #modalCuti .modal-body {
        overflow-y: auto !important;
        max-height: calc(100vh - 180px) !important;
    }
</style>

<div class="container-fluid">
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 10px; overflow: hidden;">
        <div class="cuti-card-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="font-weight-bold mb-0"><i class="far fa-calendar-alt mr-2 text-warning"></i> Daftar Pengajuan Cuti</h4>
                <small class="text-light" style="opacity: 0.85;">Kelola pengajuan cuti pegawai Satuan Kerja Pelaksanaan Prasarana Strategis Riau.</small>
            </div>
            <?php if ($can_edit): ?>
                <button type="button" class="btn btn-warning font-weight-bold text-dark shadow-sm" id="btnOpenModalBuat">
                    <i class="fas fa-plus-circle mr-1"></i> Ajukan Cuti
                </button>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tableCuti" class="table table-bordered table-striped table-hover w-100">
                    <thead class="bg-light">
                        <tr>
                            <th width="4%" class="text-center">No</th>
                            <th width="12%">Tgl Pengajuan</th>
                            <th width="20%">Nama / NIP</th>
                            <th width="15%">Jenis Cuti</th>
                            <th width="18%">Periode Cuti</th>
                            <th width="15%">Alasan</th>
                            <th width="10%" class="text-center">Status</th>
                            <th width="12%" class="text-center">Dokumen</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form Pengajuan Cuti -->
<div class="modal fade" id="modalCuti" tabindex="-1" role="dialog" aria-labelledby="modalCutiLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <form id="formCuti" action="<?= site_url('admin/surat/cuti/buat'); ?>" method="post" class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <?= csrf_field(); ?>
            <input type="hidden" id="cuti_id" name="id" value="">
            <input type="hidden" id="pegawai_id" name="pegawai_id" value="<?= esc($current_pegawai['pegawai_id'] ?? ''); ?>">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="modalCutiLabel">
                    <i class="far fa-paper-plane mr-2"></i> Formulir Permintaan dan Pemberian Cuti
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-4">

                    <!-- Section: Tanggal Pengajuan (READONLY / LOCKED) -->
                    <div class="alert alert-secondary d-flex align-items-center mb-3">
                        <i class="fas fa-lock text-muted mr-3 fa-2x"></i>
                        <div>
                            <strong class="d-block text-dark">Tanggal Pengajuan Surat:</strong>
                            <span class="badge badge-info font-weight-bold" style="font-size: 0.95rem;">
                                <?= date('d F Y'); ?>
                            </span>
                            <small class="text-muted d-block mt-1">(Tanggal pengajuan terkunci secara otomatis sesuai tanggal hari ini)</small>
                        </div>
                    </div>
                    <input type="hidden" name="tanggal_pengajuan" value="<?= date('Y-m-d'); ?>">

                    <!-- Section I: DATA PEGAWAI -->
                    <div class="form-section-title">
                        <i class="fas fa-user-id-card mr-1"></i> I. Data Pegawai
                    </div>

                    <?php if (!empty($pegawai_list) && count($pegawai_list) > 1): ?>
                    <div class="form-group">
                        <label class="font-weight-bold">Pilih Pegawai (Opsional untuk Admin)</label>
                        <select class="form-control select2" id="selectPegawai" style="width: 100%;">
                            <option value="">-- Gunakan Data Pegawai Aktif Login --</option>
                            <?php foreach ($pegawai_list as $p): ?>
                                <option value="<?= $p['id']; ?>" 
                                        data-nama="<?= esc($p['nama']); ?>" 
                                        data-nip="<?= esc($p['nip']); ?>" 
                                        data-jabatan="<?= esc($p['jabatan_label'] ?? ''); ?>"
                                        data-masakerja="<?= esc($p['masa_kerja'] ?? ''); ?>">
                                    <?= esc($p['nama']); ?> (NIP: <?= esc($p['nip']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Nama Pegawai <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama" name="nama" value="<?= esc($current_pegawai['nama'] ?? ''); ?>" required placeholder="Masukkan Nama Lengkap">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">N I P <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nip" name="nip" value="<?= esc($current_pegawai['nip'] ?? ''); ?>" required placeholder="Masukkan NIP">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Jabatan</label>
                            <input type="text" class="form-control" id="jabatan" name="jabatan" value="<?= esc($current_pegawai['jabatan'] ?? ''); ?>" placeholder="Masukkan Jabatan">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Masa Kerja</label>
                            <input type="text" class="form-control" id="masa_kerja" name="masa_kerja" value="<?= esc($current_pegawai['masa_kerja'] ?? ''); ?>" placeholder="Contoh: 5 Tahun 2 Bulan">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Unit Kerja</label>
                        <input type="text" class="form-control" id="unit_kerja" name="unit_kerja" value="<?= esc($current_pegawai['unit_kerja'] ?? 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau'); ?>" placeholder="Unit Kerja">
                    </div>

                    <!-- Section II: JENIS CUTI YANG DIAMBIL -->
                    <div class="form-section-title">
                        <i class="fas fa-list-ul mr-1"></i> II. Jenis Cuti Yang Diambil <span class="text-danger">*</span>
                    </div>

                    <div class="jenis-cuti-grid mb-3">
                        <label class="jenis-cuti-item mb-0 selected">
                            <input type="radio" name="jenis_cuti" value="Cuti Tahunan" checked class="mr-2">
                            <span>Cuti Tahunan</span>
                        </label>
                        <label class="jenis-cuti-item mb-0">
                            <input type="radio" name="jenis_cuti" value="Cuti Besar" class="mr-2">
                            <span>Cuti Besar</span>
                        </label>
                        <label class="jenis-cuti-item mb-0">
                            <input type="radio" name="jenis_cuti" value="Cuti Sakit" class="mr-2">
                            <span>Cuti Sakit</span>
                        </label>
                        <label class="jenis-cuti-item mb-0">
                            <input type="radio" name="jenis_cuti" value="Cuti Melahirkan" class="mr-2">
                            <span>Cuti Melahirkan</span>
                        </label>
                        <label class="jenis-cuti-item mb-0">
                            <input type="radio" name="jenis_cuti" value="Cuti Karena Alasan Penting" class="mr-2">
                            <span>Cuti Karena Alasan Penting</span>
                        </label>
                        <label class="jenis-cuti-item mb-0">
                            <input type="radio" name="jenis_cuti" value="Cuti di Luar Tanggungan Negara" class="mr-2">
                            <span>Cuti di Luar Tanggungan Negara</span>
                        </label>
                    </div>

                    <!-- Section III: ALASAN CUTI -->
                    <div class="form-section-title">
                        <i class="fas fa-comment-alt mr-1"></i> III. Alasan Cuti <span class="text-danger">*</span>
                    </div>

                    <div class="form-group">
                        <textarea class="form-control" id="alasan_cuti" name="alasan_cuti" rows="3" required placeholder="Tuliskan alasan pengajuan cuti secara rinci..."></textarea>
                    </div>

                    <!-- Section IV: LAMANYA CUTI -->
                    <div class="form-section-title">
                        <i class="fas fa-business-time mr-1"></i> IV. Lamanya Cuti <span class="text-danger">*</span>
                    </div>

                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label class="font-weight-bold">Jumlah</label>
                            <input type="number" class="form-control" id="lama_cuti_jumlah" name="lama_cuti_jumlah" min="1" value="1" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="font-weight-bold">Satuan</label>
                            <select class="form-control" id="lama_cuti_satuan" name="lama_cuti_satuan">
                                <option value="Hari" selected>Hari</option>
                                <option value="Bulan">Bulan</option>
                                <option value="Tahun">Tahun</option>
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="font-weight-bold">Mulai Tanggal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="font-weight-bold">s/d Tanggal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" required>
                        </div>
                    </div>

                    <!-- Section V: ALAMAT SELAMA MENJALANKAN CUTI -->
                    <div class="form-section-title">
                        <i class="fas fa-map-marker-alt mr-1"></i> V. Alamat & Kontak Selama Menjalankan Cuti
                    </div>

                    <div class="row">
                        <div class="col-md-8 form-group">
                            <label class="font-weight-bold">Alamat Selama Cuti</label>
                            <textarea class="form-control" id="alamat_selama_cuti" name="alamat_selama_cuti" rows="2" placeholder="Masukkan alamat lengkap lokasi selama cuti"></textarea>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold">No. Telepon / HP</label>
                            <input type="text" class="form-control" id="telepon" name="telepon" placeholder="Contoh: 08123456789">
                        </div>
                    </div>

                    <!-- Section VI: CATATAN CUTI -->
                    <div class="form-section-title">
                        <i class="fas fa-clipboard-list mr-1"></i> VI. Catatan Cuti (Sisa Cuti)
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Jumlah Sisa Cuti (Hari)</label>
                            <input type="number" class="form-control" id="catatan_cuti_n" name="catatan_cuti_n" min="0" value="0" placeholder="Masukkan jumlah sisa cuti">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Keterangan Catatan</label>
                            <input type="text" class="form-control" id="catatan_cuti_keterangan" name="catatan_cuti_keterangan" placeholder="Keterangan tambahan (opsional)">
                        </div>
                    </div>

                    <!-- Section VII: PENANDATANGAN -->
                    <div class="form-section-title">
                        <i class="fas fa-file-signature mr-1"></i> VII. Penandatangan & Atasan
                    </div>

                    <div class="row">
                        <div class="col-md-6 bg-light p-3 rounded mb-2">
                            <strong class="text-primary d-block mb-2"><i class="fas fa-user-tie mr-1"></i> Atasan Langsung</strong>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Nama Atasan</label>
                                <input type="text" class="form-control form-control-sm" id="atasan_nama" name="atasan_nama" value="Muhammad Yudi Prasetya, ST">
                            </div>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">NIP Atasan</label>
                                <input type="text" class="form-control form-control-sm" id="atasan_nip" name="atasan_nip" value="198002142014121002">
                            </div>
                            <div class="form-group mb-0">
                                <label class="small font-weight-bold">Jabatan Atasan</label>
                                <input type="text" class="form-control form-control-sm" id="atasan_jabatan" name="atasan_jabatan" value="Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau">
                            </div>
                        </div>

                        <div class="col-md-6 bg-light p-3 rounded mb-2">
                            <strong class="text-primary d-block mb-2"><i class="fas fa-user-shield mr-1"></i> Pejabat Berwenang</strong>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Nama Pejabat</label>
                                <input type="text" class="form-control form-control-sm" id="pejabat_nama" name="pejabat_nama" value="Ir. Agung Hari Prabowo, M.T">
                            </div>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">NIP Pejabat</label>
                                <input type="text" class="form-control form-control-sm" id="pejabat_nip" name="pejabat_nip" value="196910301998031005">
                            </div>
                            <div class="form-group mb-0">
                                <label class="small font-weight-bold">Jabatan Pejabat</label>
                                <input type="text" class="form-control form-control-sm" id="pejabat_jabatan" name="pejabat_jabatan" value="Plt. Sekretariat Direktorat Jenderal Prasarana Strategis">
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Batal</button>
                    <button type="submit" class="btn btn-primary font-weight-bold"><i class="fas fa-save mr-1"></i> Simpan Pengajuan Cuti</button>
                </div>
            </form>
        </div>
    </div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Radio button UI highlight handler
    document.querySelectorAll('.jenis-cuti-item input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', function () {
            document.querySelectorAll('.jenis-cuti-item').forEach(item => item.classList.remove('selected'));
            if (this.checked) {
                this.closest('.jenis-cuti-item').classList.add('selected');
            }
        });
    });

    const computeMasaKerjaFromNip = (nip) => {
        const digits = (nip || '').replace(/\D+/g, '');
        if (digits.length < 14) {
            return '';
        }

        const year = Number(digits.slice(8, 12));
        const month = Number(digits.slice(12, 14));
        if (!year || !month || month < 1 || month > 12 || year < 1950) {
            return '';
        }

        const currentYear = new Date().getFullYear();
        if (year > currentYear) {
            return '';
        }

        const tmtDate = new Date(year, month - 1, 1);
        if (Number.isNaN(tmtDate.getTime())) {
            return '';
        }

        const today = new Date();
        if (tmtDate > today) {
            return '';
        }

        let years = today.getFullYear() - tmtDate.getFullYear();
        let months = today.getMonth() - tmtDate.getMonth();
        if (today.getDate() < tmtDate.getDate()) {
            months -= 1;
        }
        if (months < 0) {
            years -= 1;
            months += 12;
        }

        const parts = [];
        if (years > 0) {
            parts.push(years + ' Tahun');
        }
        if (months > 0) {
            parts.push(months + ' Bulan');
        }

        return parts.length > 0 ? parts.join(' ') : '0 Bulan';
    };

    $('#nip').on('input', function () {
        const computed = computeMasaKerjaFromNip($(this).val());
        if (computed) {
            $('#masa_kerja').val(computed);
        }
    });

    // Select2 integration for employee list selection if present
    if (typeof $.fn.select2 !== 'undefined' && $('#selectPegawai').length > 0) {
        $('#selectPegawai').select2({
            dropdownParent: $('#modalCuti'),
            placeholder: '-- Pilih Pegawai --'
        }).on('change', function () {
            const selectedOpt = $(this).find(':selected');
            if (selectedOpt.val()) {
                $('#nama').val(selectedOpt.data('nama') || '');
                $('#nip').val(selectedOpt.data('nip') || '');
                $('#jabatan').val(selectedOpt.data('jabatan') || '');
                const computed = computeMasaKerjaFromNip(selectedOpt.data('nip') || '');
                $('#masa_kerja').val(computed || selectedOpt.data('masakerja') || '');
                $('#pegawai_id').val(selectedOpt.val());
            }
        });
    }

    // DataTable initialization
    const tableCuti = $('#tableCuti').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "<?= site_url('admin/surat/cuti'); ?>",
            type: "GET"
        },
        columns: [
            {
                data: null,
                className: "text-center",
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { data: "tanggal_pengajuan_formatted" },
            { 
                data: null,
                render: function (data) {
                    return '<strong>' + (data.nama || '-') + '</strong><br><small class="text-muted">NIP: ' + (data.nip || '-') + '</small>';
                }
            },
            { data: "jenis_cuti" },
            { data: "periode_formatted" },
            { data: "alasan_cuti" },
            { data: "status_badge", className: "text-center" },
            { data: "dokumen_html", className: "text-center" },
            { data: "action_html", className: "text-center" }
        ],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
        }
    });

    // Open Modal Create
    $('#btnOpenModalBuat').on('click', function () {
        $('#formCuti')[0].reset();
        $('#cuti_id').val('');
        $('#formCuti').attr('action', "<?= site_url('admin/surat/cuti/buat'); ?>");
        $('#modalCutiLabel').html('<i class="far fa-paper-plane mr-2"></i> Formulir Permintaan dan Pemberian Cuti');
        $('.jenis-cuti-item').removeClass('selected');
        $('.jenis-cuti-item input[value="Cuti Tahunan"]').prop('checked', true).closest('.jenis-cuti-item').addClass('selected');
        $('#modalCuti').modal('show');
    });

    // Edit button click handler
    $('#tableCuti').on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        $.ajax({
            url: "<?= site_url('admin/surat/cuti'); ?>/" + id + "/detail",
            type: "GET",
            dataType: "json",
            success: function (res) {
                if (res.status === 'success' && res.data) {
                    const data = res.data;
                    $('#cuti_id').val(data.id);
                    $('#nama').val(data.nama);
                    $('#nip').val(data.nip);
                    $('#jabatan').val(data.jabatan);
                    $('#masa_kerja').val(data.masa_kerja);
                    $('#unit_kerja').val(data.unit_kerja);
                    $('#alasan_cuti').val(data.alasan_cuti);
                    $('#lama_cuti_jumlah').val(data.lama_cuti_jumlah);
                    $('#lama_cuti_satuan').val(data.lama_cuti_satuan);
                    $('#tanggal_mulai').val(data.tanggal_mulai);
                    $('#tanggal_selesai').val(data.tanggal_selesai);
                    $('#alamat_selama_cuti').val(data.alamat_selama_cuti);
                    $('#telepon').val(data.telepon);
                    $('#catatan_cuti_n').val(data.catatan_cuti_n);
                    $('#catatan_cuti_keterangan').val(data.catatan_cuti_keterangan);
                    $('#atasan_nama').val(data.atasan_nama);
                    $('#atasan_nip').val(data.atasan_nip);
                    $('#atasan_jabatan').val(data.atasan_jabatan);
                    $('#pejabat_nama').val(data.pejabat_nama);
                    $('#pejabat_nip').val(data.pejabat_nip);
                    $('#pejabat_jabatan').val(data.pejabat_jabatan);

                    $('.jenis-cuti-item').removeClass('selected');
                    $('input[name="jenis_cuti"][value="' + data.jenis_cuti + '"]').prop('checked', true).closest('.jenis-cuti-item').addClass('selected');

                    $('#formCuti').attr('action', "<?= site_url('admin/surat/cuti'); ?>/" + id + "/ubah");
                    $('#modalCutiLabel').html('<i class="fas fa-edit mr-2"></i> Edit Pengajuan Cuti');
                    $('#modalCuti').modal('show');
                }
            }
        });
    });

    // Delete confirmation handler
    $('#tableCuti').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        if (confirm('Apakah Anda yakin ingin menghapus pengajuan cuti ini?')) {
            const form = $('<form action="<?= site_url('admin/surat/cuti'); ?>/' + id + '/hapus" method="post">' +
                '<?= csrf_field(); ?>' +
                '</form>');
            $('body').append(form);
            form.submit();
        }
    });

    // Approve confirmation handler
    $('#tableCuti').on('click', '.btn-approve', function () {
        const id = $(this).data('id');
        if (confirm('Apakah Anda yakin ingin menyetujui pengajuan cuti ini?')) {
            const form = $('<form action="<?= site_url('admin/surat/cuti'); ?>/' + id + '/setujui" method="post">' +
                '<?= csrf_field(); ?>' +
                '</form>');
            $('body').append(form);
            form.submit();
        }
    });

    // Reject confirmation handler
    $('#tableCuti').on('click', '.btn-reject', function () {
        const id = $(this).data('id');
        if (confirm('Apakah Anda yakin ingin menolak pengajuan cuti ini?')) {
            const form = $('<form action="<?= site_url('admin/surat/cuti'); ?>/' + id + '/tolak" method="post">' +
                '<?= csrf_field(); ?>' +
                '</form>');
            $('body').append(form);
            form.submit();
        }
    });
});
</script>
<?= $this->endSection(); ?>
