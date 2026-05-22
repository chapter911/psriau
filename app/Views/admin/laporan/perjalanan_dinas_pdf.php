<?php helper('custom'); ?>
<?php
    $data = $data ?? [];
    $periodeMulai = tanggal_indonesia((string) ($data['periode_mulai'] ?? ''));
    $periodeSelesai = tanggal_indonesia((string) ($data['periode_selesai'] ?? ''));
    $renderedDate = tanggal_indonesia(date('Y-m-d'));
    $creatorPegawai = $data['creator_pegawai'] ?? null;
    $diketahuiOleh = $data['diketahui_oleh'] ?? null;
    $pelaksana = $data['pelaksana'] ?? [];
    $photos = $data['foto_dokumentasi'] ?? [];
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        @page { margin: 1.5cm 2cm 2cm 2cm; }
        body { font-family: "Times New Roman", serif; font-size: 11pt; line-height: 1.35; }
        .text-center { text-align: center; }
        .text-justify { text-align: justify; }
        .bold { font-weight: bold; }
        .small { font-size: 10pt; }
        .mb-1 { margin-bottom: 6px; }
        .mb-2 { margin-bottom: 12px; }
        .mb-3 { margin-bottom: 18px; }
        .table { width: 100%; border-collapse: collapse; }
        .table td, .table th { padding: 4px 6px; vertical-align: top; }
        .bordered td, .bordered th { border: 1px solid #000; }
        .signature-table { width: 100%; margin-top: 20px; }
        .signature-table td { width: 50%; vertical-align: top; }
        .signature-box { min-height: 90px; }
        .photo-grid { width: 100%; border-collapse: collapse; }
        .photo-grid td { width: 50%; padding: 4px; vertical-align: top; }
        .photo-item { border: 1px solid #000; padding: 4px; }
        .photo-item img { width: 100%; height: 190px; object-fit: cover; }
        .muted { color: #444; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    <div class="text-center mb-3">
        <?= kop_surat_img_tag('', 'width: 100%; max-height: 140px; object-fit: contain;', 'Kop Surat'); ?>
    </div>

    <div class="text-right mb-2 small"><?= esc('Pekanbaru, ' . $renderedDate); ?></div>

    <div class="text-center bold mb-2" style="font-size: 13pt;">LAPORAN PELAKSANAAN PERJALANAN DINAS</div>
    <div class="text-center mb-3">Nomor: <?= esc((string) ($data['nomor_surat_tugas'] ?? '-')); ?></div>

    <table class="table mb-2">
        <tr>
            <td width="28%">Nomor Surat Tugas</td>
            <td width="2%">:</td>
            <td><?= esc((string) ($data['nomor_surat_tugas'] ?? '-')); ?></td>
        </tr>
        <tr>
            <td>Periode Perjalanan Dinas</td>
            <td>:</td>
            <td><?= esc(trim($periodeMulai . ' s.d ' . $periodeSelesai)); ?></td>
        </tr>
        <tr>
            <td>Kota/Kab. Tujuan Perjalanan Dinas</td>
            <td>:</td>
            <td><?= esc((string) ($data['kota_tujuan'] ?? '-')); ?></td>
        </tr>
    </table>

    <div class="bold mb-1">Pelaksana Perjalanan Dinas</div>
    <table class="table bordered mb-3">
        <tr>
            <th width="8%" class="text-center">No</th>
            <th width="30%">Nama</th>
            <th width="24%">NIP</th>
            <th>Jabatan</th>
        </tr>
        <?php foreach ($pelaksana as $index => $row): ?>
            <tr>
                <td class="text-center"><?= (int) $index + 1; ?></td>
                <td><?= esc((string) ($row['nama'] ?? '-')); ?></td>
                <td><?= esc((string) ($row['nip'] ?? '-')); ?></td>
                <td><?= esc((string) ($row['jabatan'] ?? '-')); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <div class="bold mb-1">Tujuan Perjalanan Dinas</div>
    <div class="text-justify mb-2"><?= nl2br(esc((string) ($data['tujuan'] ?? '-'))); ?></div>

    <div class="bold mb-1">Sasaran Perjalanan Dinas</div>
    <div class="text-justify mb-2"><?= nl2br(esc((string) ($data['sasaran'] ?? '-'))); ?></div>

    <div class="bold mb-1">Laporan Hasil Perjalanan Dinas</div>
    <div class="text-justify mb-3"><?= nl2br(esc((string) ($data['laporan_hasil'] ?? '-'))); ?></div>

    <div class="page-break"></div>

    <div class="text-center bold mb-2" style="font-size: 13pt;">LAMPIRAN DOKUMENTASI PELAKSANAAN PERJALANAN DINAS</div>

    <div class="bold mb-2">Foto Dokumentasi</div>
    <?php if (! empty($photos)): ?>
        <table class="photo-grid mb-3">
            <?php foreach (array_chunk($photos, 2) as $photoRow): ?>
                <tr>
                    <?php foreach ($photoRow as $photo): ?>
                        <td>
                            <div class="photo-item">
                                <img src="<?= esc((string) ($photo['data_uri'] ?? '')); ?>" alt="Foto Dokumentasi">
                                <div class="muted" style="font-size: 9pt; margin-top: 4px;"><?= esc((string) ($photo['name'] ?? 'Foto')); ?></div>
                            </div>
                        </td>
                    <?php endforeach; ?>
                    <?php if (count($photoRow) === 1): ?><td></td><?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <div class="muted mb-3">Tidak ada foto dokumentasi.</div>
    <?php endif; ?>

    <table class="signature-table">
        <tr>
            <td>
                <div class="mb-3">Dibuat Oleh,</div>
                <div class="signature-box">
                    <div class="bold"><?= esc((string) ($creatorPegawai['nama'] ?? ($data['creator_name'] ?? '-'))); ?></div>
                    <div>NIP. <?= esc((string) ($creatorPegawai['nip'] ?? '-')); ?></div>
                    <div class="small"><?= esc((string) ($creatorPegawai['jabatan'] ?? '-')); ?></div>
                </div>
            </td>
            <td>
                <div class="mb-3">Diketahui Oleh,</div>
                <div class="signature-box">
                    <div class="bold"><?= esc((string) ($diketahuiOleh['nama'] ?? '-')); ?></div>
                    <div>NIP. <?= esc((string) ($diketahuiOleh['nip'] ?? '-')); ?></div>
                    <div class="small"><?= esc((string) ($diketahuiOleh['jabatan'] ?? '-')); ?></div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>