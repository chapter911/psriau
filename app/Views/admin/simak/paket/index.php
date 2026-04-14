<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>

<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap justify-content-between align-items-end" style="gap:12px;">
            <div>
                <h3 class="card-title mb-0">Data Paket Simak</h3>
                <small class="text-muted">Kelola data paket konstruksi simak</small>
            </div>
            <div class="d-flex flex-wrap ml-auto" style="gap:8px;">
                <a class="btn btn-sm btn-success" href="<?= site_url('/admin/simak/paket/tambah'); ?>">
                    <i class="fas fa-plus mr-1"></i> Tambah Paket
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover js-simak-paket-table w-100">
                <thead>
                <tr>
                    <th>Nama Paket</th>
                    <th>Tahun Anggaran</th>
                    <th>Penyedia</th>
                    <th>Nomor Kontrak</th>
                    <th>Nilai Kontrak (Rp)</th>
                    <th>Tahapan Pekerjaan</th>
                    <th>Tanggal Pemeriksaan</th>
                    <th>Satker</th>
                    <th>PPK</th>
                    <th>NIP</th>
                    <th>Dibuat Oleh</th>
                    <th class="text-right">Aksi</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.DataTable) {
        return;
    }

    const $ = window.jQuery;
    const tableEl = document.querySelector('.js-simak-paket-table');

    if (!tableEl) {
        return;
    }

    const dt = $(tableEl).DataTable({
        processing: true,
        serverSide: true,
        responsive: false,
        autoWidth: false,
        scrollX: true,
        scrollCollapse: true,
        searching: false,
        order: [[1, 'desc']],
        ajax: {
            url: '<?= site_url('/admin/simak/paket/data'); ?>',
            type: 'GET',
        },
        columns: [
            {
                data: 'nama_paket',
                defaultContent: '-',
            },
            {
                data: 'tahun_anggaran',
                defaultContent: '-',
            },
            {
                data: 'penyedia',
                defaultContent: '-',
            },
            {
                data: 'nomor_kontrak',
                defaultContent: '-',
            },
            {
                data: 'nilai_kontrak',
                defaultContent: '-',
                className: 'text-right',
                render: function (data) {
                    if (!data || data === 0) return '-';
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0,
                    }).format(data);
                }
            },
            {
                data: 'tahapan_pekerjaan',
                defaultContent: '-',
            },
            {
                data: 'tanggal_pemeriksaan',
                defaultContent: '-',
            },
            {
                data: 'satker',
                defaultContent: '-',
            },
            {
                data: 'ppk',
                defaultContent: '-',
            },
            {
                data: 'nip',
                defaultContent: '-',
            },
            {
                data: 'created_by',
                defaultContent: '-',
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-right',
                render: function (row) {
                    const editUrl = row.edit_url || '#';
                    const deleteUrl = row.delete_url || '#';

                    return ''
                        + '<a class="btn btn-sm btn-warning" href="' + editUrl + '">Ubah</a> '
                        + '<form class="inline-form" method="post" action="' + deleteUrl + '"'
                        + ' data-confirm-title="Hapus Paket"'
                        + ' data-confirm-text="Yakin ingin menghapus data paket ini?"'
                        + ' data-confirm-button="Ya, hapus">'
                        + '<input type="hidden" name="<?= csrf_token(); ?>" value="<?= csrf_hash(); ?>">'
                        + '<button type="submit" class="btn btn-sm btn-danger">Hapus</button>'
                        + '</form>';
                }
            }
        ],
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

    $(tableEl).on('submit', 'form.inline-form', function (event) {
        if (this.dataset.confirmed === '1') {
            return;
        }

        event.preventDefault();

        const formEl = this;
        const title = formEl.getAttribute('data-confirm-title') || 'Konfirmasi';
        const text = formEl.getAttribute('data-confirm-text') || 'Yakin ingin melanjutkan?';
        const confirmButtonText = formEl.getAttribute('data-confirm-button') || 'Ya, lanjutkan';

        if (typeof Swal === 'undefined') {
            if (window.confirm(text)) {
                formEl.dataset.confirmed = '1';
                formEl.submit();
            }
            return;
        }

        Swal.fire({
            icon: 'question',
            title: title,
            text: text,
            showCancelButton: true,
            confirmButtonText: confirmButtonText,
            cancelButtonText: 'Batal',
            reverseButtons: true,
            focusCancel: true
        }).then(function (result) {
            if (!result.isConfirmed) {
                return;
            }

            formEl.dataset.confirmed = '1';
            formEl.submit();
        });
    });
});
</script>

<style>
    .dataTables_wrapper .dataTables_filter {
        display: none !important;
    }

    .inline-form {
        display: inline;
    }

    .inline-form button {
        margin-left: 4px;
    }
</style>

<?= $this->endSection(); ?>
