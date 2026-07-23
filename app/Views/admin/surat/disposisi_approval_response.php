<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Persetujuan Disposisi Perjalanan Dinas'); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .response-card {
            max-width: 620px;
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            border: none;
            overflow: hidden;
            background: #fff;
        }
        .status-header {
            padding: 30px 20px;
            text-align: center;
        }
        .status-header.success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }
        .status-header.danger {
            background: linear-gradient(135deg, #dc3545, #f86c6b);
            color: white;
        }
        .status-header.info {
            background: linear-gradient(135deg, #17a2b8, #36b9cc);
            color: white;
        }
        .status-icon {
            font-size: 60px;
            margin-bottom: 15px;
        }
        .detail-table th {
            width: 40%;
            color: #6c757d;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="response-card">
    <?php
        $statusType = match ($status ?? '') {
            'disetujui' => 'success',
            'ditolak', 'error' => 'danger',
            default => 'info',
        };
        $statusIcon = match ($status ?? '') {
            'disetujui' => 'fa-check-double',
            'ditolak'   => 'fa-times-circle',
            'error'     => 'fa-exclamation-triangle',
            default     => 'fa-check-circle',
        };
        $statusTitle = match ($status ?? '') {
            'disetujui' => 'Disposisi Disetujui Lengkap!',
            'ditolak'   => 'Disposisi Ditolak',
            'error'     => !empty($title) ? $title : 'Gagal Memproses',
            default     => !empty($title) ? $title : 'Persetujuan Berhasil Disimpan',
        };
    ?>
    
    <div class="status-header <?= $statusType; ?>">
        <div class="status-icon"><i class="fas <?= $statusIcon; ?>"></i></div>
        <h3 class="mb-1 font-weight-bold"><?= $statusTitle; ?></h3>
        <p class="mb-0 text-white-50"><?= esc($message ?? ''); ?></p>
    </div>

    <div class="card-body p-4">
        <?php if (!empty($disposisi)): ?>
            <h6 class="font-weight-bold text-secondary mb-3 border-bottom pb-2">
                <i class="fas fa-file-alt mr-1"></i> Status Persetujuan & Rincian Disposisi
            </h6>
            
            <div class="card card-body bg-light mb-3 p-3">
                <div class="row align-items-center">
                    <div class="col-6 text-center border-right">
                        <small class="text-muted d-block font-weight-bold">Pejabat Menyetujui (PPK)</small>
                        <?php if (($disposisi['status_menyetujui'] ?? '') === 'disetujui'): ?>
                            <span class="badge badge-success px-2 py-1 mt-1"><i class="fas fa-check"></i> Disetujui</span>
                        <?php elseif (($disposisi['status_menyetujui'] ?? '') === 'ditolak'): ?>
                            <span class="badge badge-danger px-2 py-1 mt-1"><i class="fas fa-times"></i> Ditolak</span>
                        <?php else: ?>
                            <span class="badge badge-secondary px-2 py-1 mt-1"><i class="fas fa-clock"></i> Pending</span>
                        <?php endif; ?>
                    </div>
                    <div class="col-6 text-center">
                        <small class="text-muted d-block font-weight-bold">Kepala Satker (Diketahui)</small>
                        <?php if (($disposisi['status_diketahui'] ?? '') === 'disetujui'): ?>
                            <span class="badge badge-success px-2 py-1 mt-1"><i class="fas fa-check"></i> Disetujui</span>
                        <?php elseif (($disposisi['status_diketahui'] ?? '') === 'ditolak'): ?>
                            <span class="badge badge-danger px-2 py-1 mt-1"><i class="fas fa-times"></i> Ditolak</span>
                        <?php else: ?>
                            <span class="badge badge-secondary px-2 py-1 mt-1"><i class="fas fa-clock"></i> Pending</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <table class="table table-sm table-borderless detail-table">
                <tr>
                    <th>Perihal</th>
                    <td>: <strong><?= esc($disposisi['perihal'] ?? '-'); ?></strong></td>
                </tr>
                <tr>
                    <th>Kota Tujuan</th>
                    <td>: <?= esc($disposisi['kota_tujuan'] ?? '-'); ?> (<?= esc($disposisi['tujuan'] ?? '-'); ?>)</td>
                </tr>
                <tr>
                    <th>Periode</th>
                    <td>: 
                        <?php
                            $tgl1 = !empty($disposisi['periode_mulai']) ? date('d-m-Y', strtotime($disposisi['periode_mulai'])) : '-';
                            $tgl2 = !empty($disposisi['periode_selesai']) ? date('d-m-Y', strtotime($disposisi['periode_selesai'])) : '-';
                            echo $tgl1 === $tgl2 ? $tgl1 : $tgl1 . ' s/d ' . $tglSelesai;
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>Transportasi</th>
                    <td>: <?= esc($disposisi['transportasi'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Pelaksana</th>
                    <td>: 
                        <?php
                            $pelaksana = json_decode((string) ($disposisi['pelaksana_json'] ?? '[]'), true);
                            if (!empty($pelaksana)) {
                                $names = array_column($pelaksana, 'nama');
                                echo esc(implode(', ', $names));
                            } else {
                                echo '-';
                            }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>Status Disposisi Akhir</th>
                    <td>: 
                        <?php if (($disposisi['status'] ?? '') === 'disetujui'): ?>
                            <span class="badge badge-success px-2 py-1"><i class="fas fa-check-double"></i> Disetujui Lengkap</span>
                        <?php elseif (($disposisi['status'] ?? '') === 'ditolak'): ?>
                            <span class="badge badge-danger px-2 py-1"><i class="fas fa-times"></i> Ditolak</span>
                        <?php else: ?>
                            <span class="badge badge-warning px-2 py-1"><i class="fas fa-hourglass-half"></i> Pending (Menunggu Persetujuan Lengkap)</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if (!empty($disposisi['catatan_penolakan']) && strpos((string) $disposisi['catatan_penolakan'], 'Ditolak oleh') !== 0): ?>
                    <tr>
                        <th>Catatan Penolakan</th>
                        <td class="text-danger">: <?= esc($disposisi['catatan_penolakan']); ?></td>
                    </tr>
                <?php endif; ?>
            </table>
        <?php endif; ?>

        <div class="text-center mt-4 pt-2 border-top">
            <a href="<?= site_url('admin/surat/perjalanan-dinas/disposisi'); ?>" class="btn btn-primary px-4">
                <i class="fas fa-arrow-left mr-1"></i> Buka Aplikasi Admin
            </a>
        </div>
    </div>
</div>

</body>
</html>
