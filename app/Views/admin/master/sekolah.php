<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
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
                    <th class="text-center">KOORDINAT</th>
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
                        <td><?= esc((string) (($item['latitude'] ?? '-') . ', ' . ($item['longitude'] ?? '-'))); ?></td>
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
</script>
<?= $this->endSection(); ?>
