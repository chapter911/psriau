<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">
    <!-- Top Control Bar -->
    <div class="card shadow-sm mb-4" style="border: 1px solid #e9eef5; border-radius: 12px;">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 12px;">
                <!-- Left: Title & Year Selector -->
                <div class="d-flex align-items-center flex-wrap" style="gap: 12px;">
                    <div>
                        <h4 class="mb-0 font-weight-bold text-dark d-flex align-items-center" style="font-size: 1.25rem;">
                            <i class="fas fa-calendar-alt text-danger mr-2"></i> Master Tanggal Merah
                        </h4>
                        <small class="text-muted">Kelola kalender hari libur nasional & cuti bersama terintegrasi API</small>
                    </div>

                    <div class="d-flex align-items-center bg-light p-1 px-2 rounded border">
                        <label for="yearSelector" class="mb-0 mr-2 small font-weight-bold text-secondary">
                            <i class="far fa-calendar text-primary mr-1"></i> Tahun:
                        </label>
                        <select id="yearSelector" class="form-control form-control-sm font-weight-bold text-primary" style="width: 105px; border-radius: 6px; cursor: pointer;">
                            <?php foreach ($yearOptions as $yr): ?>
                                <option value="<?= esc($yr); ?>" <?= $yr === $selectedYear ? 'selected' : ''; ?>>
                                    <?= esc($yr); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Right: Action Buttons -->
                <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                    <!-- View Mode Toggle -->
                    <div class="btn-group btn-group-sm mr-1" role="group">
                        <button type="button" class="btn btn-outline-primary active" id="btnViewCalendar" title="Tampilan Kalender">
                            <i class="fas fa-calendar-week mr-1"></i> Kalender
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="btnViewTable" title="Tampilan Tabel Data">
                            <i class="fas fa-list-ul mr-1"></i> Tabel Data
                        </button>
                    </div>

                    <?php if (! empty($can_add)): ?>
                        <!-- Tarik Data API Button -->
                        <button type="button" class="btn btn-gradient-primary btn-sm px-3 shadow-sm font-weight-bold text-white" id="btnFetchApi" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); border-radius: 6px;">
                            <i class="fas fa-cloud-arrow-down mr-1"></i> Tarik Data API
                        </button>

                        <!-- Tambah Manual Button -->
                        <button type="button" class="btn btn-success btn-sm px-3 shadow-sm font-weight-bold" data-toggle="modal" data-target="#modal-tambah-libur" style="border-radius: 6px;">
                            <i class="fas fa-plus mr-1"></i> Tambah Manual
                        </button>
                    <?php endif; ?>

                    <?php if (! empty($can_export)): ?>
                        <!-- Export Excel Button -->
                        <a href="<?= site_url('/admin/master/tanggal-merah/export?year=' . $selectedYear); ?>" class="btn btn-outline-success btn-sm font-weight-bold" style="border-radius: 6px;" title="Unduh data tahun <?= esc($selectedYear); ?> ke Excel">
                            <i class="fas fa-file-excel mr-1"></i> Export Excel
                        </a>
                    <?php endif; ?>

                    <?php if (! empty($can_delete) && ! empty($items)): ?>
                        <!-- Bersihkan Data Tahun Ini -->
                        <button type="button" class="btn btn-outline-danger btn-sm" data-toggle="modal" data-target="#modal-hapus-tahun" title="Hapus semua data tanggal merah tahun <?= esc($selectedYear); ?>">
                            <i class="fas fa-trash-alt mr-1"></i> Bersihkan Tahun <?= esc($selectedYear); ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat Metric Cards -->
    <div class="row mb-4">
        <div class="col-md-4 col-sm-6 mb-3 mb-md-0">
            <div class="card h-100 shadow-sm border-0" style="border-radius: 12px; background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%); border-left: 5px solid #007bff !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase small font-weight-bold">Total Tanggal Merah</span>
                        <h3 class="mb-0 font-weight-bold text-primary mt-1" id="statTotalHolidays"><?= esc((string) ($stats['total'] ?? 0)); ?> <span class="small font-weight-normal text-muted" style="font-size: 0.9rem;">Hari</span></h3>
                        <small class="text-secondary">Tahun <?= esc((string) $selectedYear); ?></small>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(0, 123, 255, 0.12);">
                        <i class="fas fa-calendar-check text-primary fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 mb-3 mb-md-0">
            <div class="card h-100 shadow-sm border-0" style="border-radius: 12px; background: linear-gradient(135deg, #ffffff 0%, #fff5f5 100%); border-left: 5px solid #dc3545 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase small font-weight-bold">Libur Nasional</span>
                        <h3 class="mb-0 font-weight-bold text-danger mt-1" id="statNationalHolidays"><?= esc((string) ($stats['total_holidays'] ?? 0)); ?> <span class="small font-weight-normal text-muted" style="font-size: 0.9rem;">Hari</span></h3>
                        <small class="text-secondary">Hari Libur Resmi Nasional</small>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(220, 53, 69, 0.12);">
                        <i class="fas fa-flag text-danger fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 mb-3 mb-md-0">
            <div class="card h-100 shadow-sm border-0" style="border-radius: 12px; background: linear-gradient(135deg, #ffffff 0%, #fffdf0 100%); border-left: 5px solid #ffc107 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase small font-weight-bold">Cuti Bersama</span>
                        <h3 class="mb-0 font-weight-bold text-warning mt-1" id="statLeaveDays" style="color: #d39e00 !important;"><?= esc((string) ($stats['total_leave'] ?? 0)); ?> <span class="small font-weight-normal text-muted" style="font-size: 0.9rem;">Hari</span></h3>
                        <small class="text-secondary">Cuti Bersama Pemerintah</small>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(255, 193, 7, 0.15);">
                        <i class="fas fa-umbrella-beach text-warning fa-lg" style="color: #d39e00 !important;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 1: INTERACTIVE CALENDAR VIEW -->
    <div id="sectionCalendarView" class="mb-4">
        <div class="card shadow-sm" style="border: 1px solid #e9eef5; border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center" style="border-bottom: 1px solid #e9eef5; gap: 12px;">
                <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                    <h5 class="mb-0 font-weight-bold text-dark">
                        <i class="fas fa-calendar-day text-danger mr-2"></i> Kalender Tanggal Merah Tahun <?= esc($selectedYear); ?>
                    </h5>
                    <span class="badge badge-pill badge-light border px-2 py-1 small text-muted">
                        <i class="fas fa-info-circle text-primary mr-1"></i> Klik pada tanggal untuk melihat detail / tambah
                    </span>
                </div>

                <!-- Month Filter Tabs -->
                <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                    <button type="button" class="btn btn-sm btn-light border month-tab-btn active" data-month="all" style="border-radius: 6px;">
                        Semua Bulan
                    </button>
                    <?php foreach ($indoMonths as $mNum => $mName): ?>
                        <button type="button" class="btn btn-sm btn-light border month-tab-btn" data-month="<?= esc($mNum); ?>" style="border-radius: 6px;">
                            <?= esc(substr($mName, 0, 3)); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card-body p-3 bg-light">
                <!-- Legend Indicators -->
                <div class="d-flex flex-wrap align-items-center justify-content-center p-2 mb-3 bg-white rounded border shadow-sm" style="gap: 20px;">
                    <div class="d-flex align-items-center">
                        <span class="d-inline-block rounded-circle mr-2" style="width: 14px; height: 14px; background-color: #dc3545; border: 2px solid #fff; box-shadow: 0 0 0 1px #dc3545;"></span>
                        <span class="small font-weight-bold text-secondary">Hari Libur Nasional</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="d-inline-block rounded-circle mr-2" style="width: 14px; height: 14px; background-color: #ff9800; border: 2px solid #fff; box-shadow: 0 0 0 1px #ff9800;"></span>
                        <span class="small font-weight-bold text-secondary">Cuti Bersama</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="d-inline-block rounded-circle mr-2" style="width: 14px; height: 14px; background-color: #f8d7da; border: 1px solid #f5c6cb;"></span>
                        <span class="small font-weight-bold text-secondary">Hari Minggu (Weekend)</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="d-inline-block rounded-circle mr-2" style="width: 14px; height: 14px; background-color: #e3f2fd; border: 1px solid #bbdefb;"></span>
                        <span class="small font-weight-bold text-secondary">Hari Sabtu</span>
                    </div>
                </div>

                <!-- 12 Months Grid -->
                <div class="row" id="calendarMonthGrid">
                    <?php foreach ($calendarMonths as $mNum => $mMonth): ?>
                        <div class="col-xl-4 col-lg-6 col-md-6 mb-4 month-card-wrapper" data-month-num="<?= esc($mNum); ?>">
                            <div class="card h-100 shadow-sm border-0 month-calendar-card" style="border-radius: 12px; overflow: hidden; background: #ffffff;">
                                <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: #ffffff;">
                                    <h6 class="mb-0 font-weight-bold">
                                        <i class="far fa-calendar-alt text-warning mr-1"></i> <?= esc($mMonth['month_name']); ?> <?= esc($selectedYear); ?>
                                    </h6>
                                    <?php
                                        // Count holidays in this month
                                        $monthHolidayCount = 0;
                                        foreach ($mMonth['weeks'] as $week) {
                                            foreach ($week as $dayObj) {
                                                if (! empty($dayObj['is_holiday'])) {
                                                    $monthHolidayCount++;
                                                }
                                            }
                                        }
                                    ?>
                                    <?php if ($monthHolidayCount > 0): ?>
                                        <span class="badge badge-danger badge-pill px-2" title="<?= $monthHolidayCount; ?> hari libur/cuti di bulan ini">
                                            <?= $monthHolidayCount; ?> Libur
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary badge-pill px-2" style="opacity: 0.6;">
                                            -
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body p-2">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm calendar-table text-center mb-0" style="table-layout: fixed; font-size: 0.85rem;">
                                            <thead>
                                                <tr class="bg-light text-muted small">
                                                    <th class="text-danger py-1" style="width: 14.28%;">Min</th>
                                                    <th class="py-1" style="width: 14.28%;">Sen</th>
                                                    <th class="py-1" style="width: 14.28%;">Sel</th>
                                                    <th class="py-1" style="width: 14.28%;">Rab</th>
                                                    <th class="py-1" style="width: 14.28%;">Kam</th>
                                                    <th class="py-1" style="width: 14.28%;">Jum</th>
                                                    <th class="text-primary py-1" style="width: 14.28%;">Sab</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($mMonth['weeks'] as $week): ?>
                                                    <tr>
                                                        <?php foreach ($week as $dayObj): ?>
                                                            <?php if (! empty($dayObj['is_padding'])): ?>
                                                                <td class="calendar-cell-pad bg-light" style="opacity: 0.25;">&nbsp;</td>
                                                            <?php else: ?>
                                                                <?php
                                                                    $isHol = ! empty($dayObj['is_holiday']);
                                                                    $holData = $dayObj['holiday'] ?? null;
                                                                    $isLeave = $holData && ($holData['tipe'] ?? '') === 'leave';
                                                                    $isSunday = ! empty($dayObj['is_sunday']);
                                                                    $isSaturday = ! empty($dayObj['is_saturday']);

                                                                    $cellClass = 'calendar-day-cell ';
                                                                    if ($isHol) {
                                                                        $cellClass .= $isLeave ? 'cell-leave ' : 'cell-holiday ';
                                                                    } elseif ($isSunday) {
                                                                        $cellClass .= 'cell-sunday ';
                                                                    } elseif ($isSaturday) {
                                                                        $cellClass .= 'cell-saturday ';
                                                                    }

                                                                    $tooltipText = '';
                                                                    if ($isHol) {
                                                                        $prefix = $isLeave ? '[Cuti Bersama] ' : '[Libur Nasional] ';
                                                                        $tooltipText = $prefix . esc((string) ($holData['nama_libur'] ?? ''));
                                                                    }
                                                                ?>
                                                                <td class="<?= esc($cellClass); ?>"
                                                                    data-date="<?= esc($dayObj['date']); ?>"
                                                                    data-is-holiday="<?= $isHol ? '1' : '0'; ?>"
                                                                    data-holiday-id="<?= esc((string) ($holData['id'] ?? '')); ?>"
                                                                    data-holiday-name="<?= esc((string) ($holData['nama_libur'] ?? '')); ?>"
                                                                    data-holiday-type="<?= esc((string) ($holData['tipe'] ?? 'holiday')); ?>"
                                                                    data-holiday-day="<?= esc((string) ($holData['hari'] ?? '')); ?>"
                                                                    data-holiday-source="<?= esc((string) ($holData['sumber'] ?? '')); ?>"
                                                                    data-toggle="tooltip"
                                                                    data-placement="top"
                                                                    title="<?= esc($tooltipText); ?>"
                                                                    style="position: relative; height: 42px; vertical-align: middle; cursor: pointer; border-radius: 4px;">
                                                                    <div class="day-number font-weight-bold" style="font-size: 0.92rem;">
                                                                        <?= esc($dayObj['day_number']); ?>
                                                                    </div>
                                                                    <?php if ($isHol): ?>
                                                                        <div class="holiday-dot-indicator" style="width: 6px; height: 6px; border-radius: 50%; margin: 1px auto 0; background-color: <?= $isLeave ? '#ff9800' : '#dc3545'; ?>;"></div>
                                                                    <?php endif; ?>
                                                                </td>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 2: TABLE DATA VIEW -->
    <div id="sectionTableView" class="mb-4" style="display: none;">
        <div class="card shadow-sm" style="border: 1px solid #e9eef5; border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center" style="border-bottom: 1px solid #e9eef5; gap: 12px;">
                <h5 class="mb-0 font-weight-bold text-dark">
                    <i class="fas fa-list-check text-primary mr-2"></i> Daftar Tanggal Merah Tahun <?= esc($selectedYear); ?>
                </h5>
                <div class="d-flex align-items-center" style="gap: 8px;">
                    <span class="badge badge-primary px-3 py-2" style="font-size: 0.85rem;">
                        Total: <?= count($items); ?> Hari
                    </span>
                </div>
            </div>
            <div class="card-body p-3">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-striped w-100 js-datatable-holiday" style="border-radius: 8px;">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">#</th>
                                <th style="width: 140px;" class="text-center">Tanggal</th>
                                <th style="width: 100px;" class="text-center">Hari</th>
                                <th>Keterangan / Nama Hari Libur</th>
                                <th style="width: 150px;" class="text-center">Kategori</th>
                                <th style="width: 100px;" class="text-center">Sumber</th>
                                <?php if (! empty($can_edit) || ! empty($can_delete)): ?>
                                    <th style="width: 130px;" class="text-center">Aksi</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($items as $item): ?>
                                <?php
                                    $isLeave = ($item['tipe'] ?? '') === 'leave';
                                    $tglIndo = date('d M Y', strtotime($item['tanggal']));
                                ?>
                                <tr>
                                    <td class="text-center align-middle"><?= esc((string) $i++); ?></td>
                                    <td class="text-center align-middle font-weight-bold">
                                        <span class="badge badge-light p-2 border" style="font-size: 0.85rem;">
                                            <i class="far fa-calendar text-danger mr-1"></i>
                                            <?= esc($item['tanggal']); ?>
                                        </span>
                                    </td>
                                    <td class="text-center align-middle font-weight-bold text-secondary">
                                        <?= esc((string) ($item['hari'] ?? '-')); ?>
                                    </td>
                                    <td class="align-middle font-weight-bold" style="font-size: 0.95rem;">
                                        <?php if ($isLeave): ?>
                                            <i class="fas fa-umbrella-beach text-warning mr-1" style="color: #ff9800 !important;"></i>
                                        <?php else: ?>
                                            <i class="fas fa-flag text-danger mr-1"></i>
                                        <?php endif; ?>
                                        <?= esc((string) ($item['nama_libur'] ?? '-')); ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <?php if ($isLeave): ?>
                                            <span class="badge badge-warning px-2 py-1 font-weight-bold" style="background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba;">
                                                <i class="fas fa-umbrella-beach mr-1"></i> Cuti Bersama
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-danger px-2 py-1 font-weight-bold" style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;">
                                                <i class="fas fa-flag mr-1"></i> Libur Nasional
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-secondary px-2 py-1" style="font-size: 0.78rem;">
                                            <?= esc((string) ($item['sumber'] ?? 'API')); ?>
                                        </span>
                                    </td>
                                    <?php if (! empty($can_edit) || ! empty($can_delete)): ?>
                                        <td class="text-center align-middle">
                                            <?php if (! empty($can_edit)): ?>
                                                <button type="button" class="btn btn-warning btn-sm btn-edit-holiday"
                                                        data-id="<?= esc((string) $item['id']); ?>"
                                                        data-tanggal="<?= esc((string) $item['tanggal']); ?>"
                                                        data-nama="<?= esc((string) $item['nama_libur']); ?>"
                                                        data-tipe="<?= esc((string) $item['tipe']); ?>"
                                                        title="Ubah data">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if (! empty($can_delete)): ?>
                                                <form action="<?= site_url('/admin/master/tanggal-merah/' . esc((string) $item['id'], 'url') . '/hapus'); ?>" method="post" style="display: inline-block;" onsubmit="return confirm('Yakin ingin menghapus tanggal merah ini: <?= esc($item['nama_libur'], 'js'); ?>?');">
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="fas fa-calendar-xmark fa-2x mb-2 text-secondary d-block"></i>
                                        Belum ada data tanggal merah untuk tahun <?= esc($selectedYear); ?>.<br>
                                        Gunakan tombol <strong>"Tarik Data API"</strong> di atas untuk mengimpor otomatis.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================= -->
<!-- MODAL 1: PREVIEW KONFIRMASI TARIK DATA API               -->
<!-- ======================================================= -->
<div class="modal fade" id="modal-preview-api" tabindex="-1" role="dialog" aria-labelledby="modalPreviewApiLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 14px; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title font-weight-bold" id="modalPreviewApiLabel">
                    <i class="fas fa-cloud-arrow-down mr-2"></i> Preview Sinkronisasi Data Tanggal Merah API Tahun <span id="previewModalYear"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Loading Spinner State -->
            <div id="previewLoadingState" class="modal-body text-center py-5">
                <div class="spinner-border text-primary mb-3" style="width: 3.5rem; height: 3.5rem;" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <h5 class="font-weight-bold text-dark">Menghubungkan ke API Tanggal Merah...</h5>
                <p class="text-muted small mb-0">Mengambil data resmi dari <code>https://tanggalmerah.upset.dev/api/holidays?year=<span class="apiTargetYear"></span></code></p>
            </div>

            <!-- Error State -->
            <div id="previewErrorState" class="modal-body py-4 text-center" style="display: none;">
                <div class="alert alert-danger mb-0 text-left">
                    <h5 class="alert-heading font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i> Gagal Mengambil Data API</h5>
                    <p id="previewErrorMessage" class="mb-0"></p>
                </div>
            </div>

            <!-- Success Content State -->
            <div id="previewContentState" class="modal-body p-3" style="display: none;">
                <!-- Summary Meta Bar -->
                <div class="row mb-3">
                    <div class="col-md-3 col-6 mb-2 mb-md-0">
                        <div class="bg-light p-2 rounded border text-center">
                            <span class="small text-muted d-block font-weight-bold">Total Ditemukan</span>
                            <span class="h5 font-weight-bold text-primary mb-0" id="previewTotalCount">0</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-2 mb-md-0">
                        <div class="bg-light p-2 rounded border text-center">
                            <span class="small text-muted d-block font-weight-bold">Libur Nasional</span>
                            <span class="h5 font-weight-bold text-danger mb-0" id="previewNationalCount">0</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-2 mb-md-0">
                        <div class="bg-light p-2 rounded border text-center">
                            <span class="small text-muted d-block font-weight-bold">Cuti Bersama</span>
                            <span class="h5 font-weight-bold text-warning mb-0" id="previewLeaveCount" style="color: #d39e00 !important;">0</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-2 mb-md-0">
                        <div class="bg-light p-2 rounded border text-center">
                            <span class="small text-muted d-block font-weight-bold">Data Baru di Database</span>
                            <span class="h5 font-weight-bold text-success mb-0" id="previewNewCount">0</span>
                        </div>
                    </div>
                </div>

                <!-- Sync Mode Selector -->
                <div class="p-3 mb-3 rounded border bg-white shadow-sm">
                    <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: 12px;">
                        <div>
                            <span class="font-weight-bold text-dark d-block">Opsi Sinkronisasi:</span>
                            <small class="text-muted">Pilih bagaimana data akan disimpan ke database</small>
                        </div>
                        <div class="d-flex align-items-center flex-wrap" style="gap: 16px;">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="modeAll" name="syncMode" value="all" class="custom-control-input" checked>
                                <label class="custom-control-label font-weight-bold" for="modeAll">
                                    Simpan & Perbarui Semua Data (Rekomendasi)
                                </label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="modeNewOnly" name="syncMode" value="new_only" class="custom-control-input">
                                <label class="custom-control-label font-weight-bold" for="modeNewOnly">
                                    Simpan Data Baru Saja
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Preview -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="font-weight-bold text-secondary small">
                        <i class="fas fa-check-square text-primary mr-1"></i> Centang data yang ingin disimpan:
                    </span>
                    <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" id="btnToggleSelectAll">
                        Pilih Semua / Batal
                    </button>
                </div>

                <div class="table-responsive" style="max-height: 380px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 8px;">
                    <table class="table table-hover table-sm table-striped mb-0" id="tablePreviewApi">
                        <thead class="bg-light" style="position: sticky; top: 0; z-index: 2;">
                            <tr>
                                <th style="width: 45px;" class="text-center">
                                    <input type="checkbox" id="checkSelectAll" checked>
                                </th>
                                <th style="width: 130px;" class="text-center">Tanggal</th>
                                <th style="width: 90px;" class="text-center">Hari</th>
                                <th>Nama Hari Libur / Keterangan</th>
                                <th style="width: 140px;" class="text-center">Kategori</th>
                                <th style="width: 140px;" class="text-center">Status Database</th>
                            </tr>
                        </thead>
                        <tbody id="previewTableBody">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success px-4 font-weight-bold" id="btnConfirmSaveApi" style="border-radius: 6px;">
                    <i class="fas fa-save mr-1"></i> Konfirmasi & Simpan ke Database
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================= -->
<!-- MODAL 2: TAMBAH MANUAL TANGGAL MERAH                     -->
<!-- ======================================================= -->
<div class="modal fade" id="modal-tambah-libur" tabindex="-1" role="dialog" aria-labelledby="modalTambahLiburLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
            <div class="modal-header bg-success text-white py-3">
                <h5 class="modal-title font-weight-bold" id="modalTambahLiburLabel">
                    <i class="fas fa-plus-circle mr-2"></i> Tambah Tanggal Merah Manual
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('/admin/master/tanggal-merah/tambah'); ?>" method="post">
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label for="inputTambahTanggal" class="font-weight-bold text-dark">
                            Tanggal <span class="text-danger">*</span>
                        </label>
                        <input type="date" class="form-control" id="inputTambahTanggal" name="tanggal" required value="<?= esc(sprintf('%04d-01-01', $selectedYear)); ?>">
                        <small class="text-muted">Pilih tanggal hari libur yang ingin didaftarkan.</small>
                    </div>
                    <div class="form-group">
                        <label for="inputTambahNama" class="font-weight-bold text-dark">
                            Keterangan / Nama Libur <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="inputTambahNama" name="nama_libur" required placeholder="Contoh: Hari Raya Idul Fitri 1445 H">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold text-dark d-block">
                            Kategori Hari Libur <span class="text-danger">*</span>
                        </label>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="tipeTambahHoliday" name="tipe" value="holiday" class="custom-control-input" checked>
                            <label class="custom-control-label font-weight-bold text-danger" for="tipeTambahHoliday">
                                <i class="fas fa-flag mr-1"></i> Libur Nasional
                            </label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="tipeTambahLeave" name="tipe" value="leave" class="custom-control-input">
                            <label class="custom-control-label font-weight-bold text-warning" for="tipeTambahLeave" style="color: #d39e00 !important;">
                                <i class="fas fa-umbrella-beach mr-1"></i> Cuti Bersama
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4 font-weight-bold">
                        <i class="fas fa-save mr-1"></i> Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ======================================================= -->
<!-- MODAL 3: UBAH TANGGAL MERAH                             -->
<!-- ======================================================= -->
<div class="modal fade" id="modal-ubah-libur" tabindex="-1" role="dialog" aria-labelledby="modalUbahLiburLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
            <div class="modal-header bg-warning text-dark py-3">
                <h5 class="modal-title font-weight-bold" id="modalUbahLiburLabel">
                    <i class="fas fa-edit mr-2"></i> Ubah Tanggal Merah
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formUbahLibur" action="" method="post">
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label for="inputUbahTanggal" class="font-weight-bold text-dark">
                            Tanggal <span class="text-danger">*</span>
                        </label>
                        <input type="date" class="form-control" id="inputUbahTanggal" name="tanggal" required>
                    </div>
                    <div class="form-group">
                        <label for="inputUbahNama" class="font-weight-bold text-dark">
                            Keterangan / Nama Libur <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="inputUbahNama" name="nama_libur" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold text-dark d-block">
                            Kategori Hari Libur <span class="text-danger">*</span>
                        </label>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="tipeUbahHoliday" name="tipe" value="holiday" class="custom-control-input">
                            <label class="custom-control-label font-weight-bold text-danger" for="tipeUbahHoliday">
                                <i class="fas fa-flag mr-1"></i> Libur Nasional
                            </label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="tipeUbahLeave" name="tipe" value="leave" class="custom-control-input">
                            <label class="custom-control-label font-weight-bold text-warning" for="tipeUbahLeave" style="color: #d39e00 !important;">
                                <i class="fas fa-umbrella-beach mr-1"></i> Cuti Bersama
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 font-weight-bold">
                        <i class="fas fa-save mr-1"></i> Perbarui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ======================================================= -->
<!-- MODAL 4: DETAIL TANGGAL DARI KLIK KALENDER               -->
<!-- ======================================================= -->
<div class="modal fade" id="modal-date-detail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
            <div class="modal-header py-2 px-3 text-white" id="dateDetailHeader" style="background: #1e293b;">
                <h6 class="modal-title font-weight-bold mb-0" id="dateDetailTitle">Detail Tanggal</h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3 text-center" id="dateDetailBody">
                <!-- Dynamically populated -->
            </div>
            <div class="modal-footer bg-light p-2 justify-content-center" id="dateDetailFooter">
                <!-- Actions -->
            </div>
        </div>
    </div>
</div>

<!-- ======================================================= -->
<!-- MODAL 5: HAPUS SEMUA DATA TAHUN INI                      -->
<!-- ======================================================= -->
<?php if (! empty($can_delete)): ?>
<div class="modal fade" id="modal-hapus-tahun" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-danger text-white py-3">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Konfirmasi Bersihkan Data Tahun <?= esc($selectedYear); ?>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('/admin/master/tanggal-merah/hapus-tahun/' . $selectedYear); ?>" method="post">
                <div class="modal-body p-4 text-center">
                    <i class="fas fa-trash-can fa-3x text-danger mb-3 d-block"></i>
                    <h5 class="font-weight-bold text-dark">Hapus Seluruh Data Tahun <?= esc($selectedYear); ?>?</h5>
                    <p class="text-muted small mb-0">
                        Tindakan ini akan menghapus <strong><?= count($items); ?> hari libur & cuti bersama</strong> pada tahun <strong><?= esc($selectedYear); ?></strong> dari database. Data dapat ditarik kembali sewaktu-waktu melalui tombol Tarik Data API.
                    </p>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger px-4 font-weight-bold">
                        <i class="fas fa-trash mr-1"></i> Ya, Hapus Semua
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
/* Modern Calendar Styling */
.month-calendar-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: 1px solid #e2e8f0 !important;
}
.month-calendar-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.06) !important;
}
.calendar-day-cell {
    transition: background-color 0.15s ease, transform 0.15s ease;
}
.calendar-day-cell:hover {
    background-color: #e0f2fe !important;
    transform: scale(1.08);
    z-index: 3;
    box-shadow: 0 4px 8px rgba(0,0,0,0.12);
}
.cell-holiday {
    background-color: #fee2e2 !important;
    color: #b91c1c !important;
    font-weight: 700;
}
.cell-holiday:hover {
    background-color: #fecaca !important;
}
.cell-leave {
    background-color: #fef3c7 !important;
    color: #b45309 !important;
    font-weight: 700;
}
.cell-leave:hover {
    background-color: #fde68a !important;
}
.cell-sunday {
    background-color: #fff1f2;
    color: #e11d48;
}
.cell-saturday {
    background-color: #f0f9ff;
    color: #0284c7;
}
.month-tab-btn.active {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
    color: #ffffff !important;
    border-color: #0056b3 !important;
    font-weight: 700;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize Tooltips
    if (typeof $ !== 'undefined' && $.fn.tooltip) {
        $('[data-toggle="tooltip"]').tooltip({
            trigger: 'hover',
            container: 'body'
        });
    }

    const currentSelectedYear = <?= (int) $selectedYear; ?>;
    const fetchApiUrl = <?= json_encode(site_url('/admin/master/tanggal-merah/fetch-api')); ?>;
    const saveBatchUrl = <?= json_encode(site_url('/admin/master/tanggal-merah/simpan-batch')); ?>;
    const baseEditUrl = <?= json_encode(site_url('/admin/master/tanggal-merah')); ?>;

    let cachedFetchedData = [];

    // 1. Year Selector Change
    const yearSelector = document.getElementById('yearSelector');
    if (yearSelector) {
        yearSelector.addEventListener('change', function () {
            const chosenYear = this.value;
            window.location.href = baseEditUrl + '?year=' + encodeURIComponent(chosenYear);
        });
    }

    // 2. View Mode Toggle (Calendar vs Table)
    const btnViewCalendar = document.getElementById('btnViewCalendar');
    const btnViewTable = document.getElementById('btnViewTable');
    const sectionCalendarView = document.getElementById('sectionCalendarView');
    const sectionTableView = document.getElementById('sectionTableView');

    if (btnViewCalendar && btnViewTable) {
        btnViewCalendar.addEventListener('click', function () {
            btnViewCalendar.classList.add('active');
            btnViewTable.classList.remove('active');
            sectionCalendarView.style.display = 'block';
            sectionTableView.style.display = 'none';
        });

        btnViewTable.addEventListener('click', function () {
            btnViewTable.classList.add('active');
            btnViewCalendar.classList.remove('active');
            sectionCalendarView.style.display = 'none';
            sectionTableView.style.display = 'block';
        });
    }

    // 3. Month Filter Tabs
    const monthTabButtons = document.querySelectorAll('.month-tab-btn');
    const monthCards = document.querySelectorAll('.month-card-wrapper');

    monthTabButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            monthTabButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const monthVal = this.getAttribute('data-month');

            monthCards.forEach(card => {
                if (monthVal === 'all' || card.getAttribute('data-month-num') === monthVal) {
                    card.style.display = 'block';
                    if (monthVal !== 'all') {
                        card.className = 'col-12 mb-4 month-card-wrapper';
                    } else {
                        card.className = 'col-xl-4 col-lg-6 col-md-6 mb-4 month-card-wrapper';
                    }
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // 4. Tarik Data API Button Click
    const btnFetchApi = document.getElementById('btnFetchApi');
    const modalPreviewApi = $('#modal-preview-api');

    if (btnFetchApi) {
        btnFetchApi.addEventListener('click', function () {
            $('#previewModalYear').text(currentSelectedYear);
            $('.apiTargetYear').text(currentSelectedYear);

            $('#previewLoadingState').show();
            $('#previewErrorState').hide();
            $('#previewContentState').hide();
            $('#btnConfirmSaveApi').prop('disabled', true);

            modalPreviewApi.modal('show');

            // Send AJAX POST
            $.ajax({
                url: fetchApiUrl,
                type: 'POST',
                data: { year: currentSelectedYear },
                dataType: 'json',
                success: function (res) {
                    $('#previewLoadingState').hide();

                    if (! res.success) {
                        $('#previewErrorMessage').text(res.message || 'Gagal memuat data dari API.');
                        $('#previewErrorState').show();
                        return;
                    }

                    cachedFetchedData = res.data || [];

                    // Populate summary numbers
                    $('#previewTotalCount').text(res.total_count || 0);
                    $('#previewNationalCount').text((res.meta && res.meta.total_holidays) ? res.meta.total_holidays : 0);
                    $('#previewLeaveCount').text((res.meta && res.meta.total_leave) ? res.meta.total_leave : 0);
                    $('#previewNewCount').text(res.new_count || 0);

                    // Render Preview Table Rows
                    const tbody = document.getElementById('previewTableBody');
                    tbody.innerHTML = '';

                    if (cachedFetchedData.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-3 text-muted">Tidak ada data tanggal merah ditemukan untuk tahun ini.</td></tr>';
                    } else {
                        cachedFetchedData.forEach((item, idx) => {
                            const tr = document.createElement('tr');
                            const isLeave = item.type === 'leave';

                            let diffNote = '';
                            if (item.is_different) {
                                diffNote = `<div class="small text-danger"><i class="fas fa-info-circle mr-1"></i> Data di DB: ${item.existing_name} (${item.existing_type === 'leave' ? 'Cuti' : 'Libur'})</div>`;
                            }

                            tr.innerHTML = `
                                <td class="text-center align-middle">
                                    <input type="checkbox" class="check-holiday-item" data-index="${idx}" checked>
                                </td>
                                <td class="text-center align-middle font-weight-bold">
                                    <span class="badge badge-light border p-1">${item.date}</span>
                                    <div class="small text-muted">${item.date_indo || ''}</div>
                                </td>
                                <td class="text-center align-middle font-weight-bold text-secondary">
                                    ${item.day || '-'}
                                </td>
                                <td class="align-middle font-weight-bold">
                                    <span class="${isLeave ? 'text-warning' : 'text-danger'}">
                                        <i class="${isLeave ? 'fas fa-umbrella-beach' : 'fas fa-flag'} mr-1"></i>
                                    </span>
                                    ${item.name || '-'}
                                    ${diffNote}
                                </td>
                                <td class="text-center align-middle">
                                    <span class="badge ${isLeave ? 'badge-warning' : 'badge-danger'} px-2 py-1">
                                        ${item.type_label || (isLeave ? 'Cuti Bersama' : 'Libur Nasional')}
                                    </span>
                                </td>
                                <td class="text-center align-middle">
                                    <span class="badge ${item.status_badge || 'badge-secondary'} px-2 py-1">
                                        ${item.status_label || '-'}
                                    </span>
                                </td>
                            `;
                            tbody.appendChild(tr);
                        });
                    }

                    $('#previewContentState').show();
                    $('#btnConfirmSaveApi').prop('disabled', cachedFetchedData.length === 0);
                },
                error: function (xhr) {
                    $('#previewLoadingState').hide();
                    $('#previewErrorMessage').text('Terjadi kesalahan jaringan (HTTP ' + xhr.status + ').');
                    $('#previewErrorState').show();
                }
            });
        });
    }

    // 5. Toggle Select All Checkboxes in Preview Table
    const checkSelectAll = document.getElementById('checkSelectAll');
    const btnToggleSelectAll = document.getElementById('btnToggleSelectAll');

    function syncSelectAll(state) {
        const checkboxes = document.querySelectorAll('.check-holiday-item');
        checkboxes.forEach(cb => { cb.checked = state; });
        if (checkSelectAll) checkSelectAll.checked = state;
    }

    if (checkSelectAll) {
        checkSelectAll.addEventListener('change', function () {
            syncSelectAll(this.checked);
        });
    }

    if (btnToggleSelectAll) {
        btnToggleSelectAll.addEventListener('click', function () {
            const first = document.querySelector('.check-holiday-item');
            const newState = first ? ! first.checked : true;
            syncSelectAll(newState);
        });
    }

    // 6. Confirm and Save API Holidays Batch
    const btnConfirmSaveApi = document.getElementById('btnConfirmSaveApi');
    if (btnConfirmSaveApi) {
        btnConfirmSaveApi.addEventListener('click', function () {
            const selectedItems = [];
            const checkboxes = document.querySelectorAll('.check-holiday-item:checked');

            checkboxes.forEach(cb => {
                const idx = parseInt(cb.getAttribute('data-index'), 10);
                if (cachedFetchedData[idx]) {
                    selectedItems.push(cachedFetchedData[idx]);
                }
            });

            if (selectedItems.length === 0) {
                alert('Pilih setidaknya 1 tanggal merah untuk disimpan.');
                return;
            }

            const syncMode = $('input[name="syncMode"]:checked').val() || 'all';

            btnConfirmSaveApi.disabled = true;
            btnConfirmSaveApi.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...';

            $.ajax({
                url: saveBatchUrl,
                type: 'POST',
                data: {
                    year: currentSelectedYear,
                    holidays_json: JSON.stringify(selectedItems),
                    mode: syncMode
                },
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        modalPreviewApi.modal('hide');
                        window.location.reload();
                    } else {
                        btnConfirmSaveApi.disabled = false;
                        btnConfirmSaveApi.innerHTML = '<i class="fas fa-save mr-1"></i> Konfirmasi & Simpan ke Database';
                        alert(res.message || 'Gagal menyimpan data.');
                    }
                },
                error: function (xhr) {
                    btnConfirmSaveApi.disabled = false;
                    btnConfirmSaveApi.innerHTML = '<i class="fas fa-save mr-1"></i> Konfirmasi & Simpan ke Database';
                    alert('Terjadi kesalahan sistem saat menyimpan data.');
                }
            });
        });
    }

    // 7. Click on Calendar Date Cell (Detail or Quick Add)
    const calendarCells = document.querySelectorAll('.calendar-day-cell');
    const modalDateDetail = $('#modal-date-detail');

    calendarCells.forEach(cell => {
        cell.addEventListener('click', function () {
            const date = this.getAttribute('data-date');
            const isHoliday = this.getAttribute('data-is-holiday') === '1';
            const holId = this.getAttribute('data-holiday-id');
            const holName = this.getAttribute('data-holiday-name');
            const holType = this.getAttribute('data-holiday-type');
            const holDay = this.getAttribute('data-holiday-day');
            const holSource = this.getAttribute('data-holiday-source');

            const titleEl = document.getElementById('dateDetailTitle');
            const bodyEl = document.getElementById('dateDetailBody');
            const footerEl = document.getElementById('dateDetailFooter');
            const headerEl = document.getElementById('dateDetailHeader');

            if (isHoliday) {
                const isLeave = holType === 'leave';
                headerEl.style.background = isLeave ? '#b45309' : '#dc3545';
                titleEl.innerHTML = `<i class="${isLeave ? 'fas fa-umbrella-beach' : 'fas fa-flag'} mr-1"></i> ${isLeave ? 'Cuti Bersama' : 'Libur Nasional'}`;

                bodyEl.innerHTML = `
                    <div class="badge badge-light border p-2 mb-2 font-weight-bold" style="font-size: 0.95rem;">
                        <i class="far fa-calendar-alt text-primary mr-1"></i> ${date} (${holDay || '-'})
                    </div>
                    <h5 class="font-weight-bold text-dark mb-2">${holName || '-'}</h5>
                    <div class="small text-muted mb-2">Sumber: <strong>${holSource || 'API'}</strong></div>
                `;

                let actionHtml = `
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                `;

                <?php if (! empty($can_edit)): ?>
                    actionHtml += `
                        <button type="button" class="btn btn-warning btn-sm btn-edit-holiday-direct font-weight-bold"
                                data-id="${holId}" data-tanggal="${date}" data-nama="${holName}" data-tipe="${holType}">
                            <i class="fas fa-edit mr-1"></i> Ubah
                        </button>
                    `;
                <?php endif; ?>

                <?php if (! empty($can_delete)): ?>
                    actionHtml += `
                        <form action="${baseEditUrl}/${holId}/hapus" method="post" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus tanggal merah ini?');">
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>
                    `;
                <?php endif; ?>

                footerEl.innerHTML = actionHtml;
            } else {
                headerEl.style.background = '#1e293b';
                titleEl.innerHTML = '<i class="far fa-calendar mr-1"></i> Tanggal Biasa / Kerja';

                bodyEl.innerHTML = `
                    <div class="badge badge-light border p-2 mb-2 font-weight-bold" style="font-size: 0.95rem;">
                        <i class="far fa-calendar text-primary mr-1"></i> ${date}
                    </div>
                    <p class="text-muted small mb-0">Tanggal ini bukan merupakan hari libur terdaftar di sistem.</p>
                `;

                let actionHtml = `
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                `;

                <?php if (! empty($can_add)): ?>
                    actionHtml += `
                        <button type="button" class="btn btn-success btn-sm font-weight-bold btn-quick-add" data-date="${date}">
                            <i class="fas fa-plus mr-1"></i> Tambah Libur di Tanggal Ini
                        </button>
                    `;
                <?php endif; ?>

                footerEl.innerHTML = actionHtml;
            }

            modalDateDetail.modal('show');
        });
    });

    // 8. Quick Add from Date Detail Modal
    $(document).on('click', '.btn-quick-add', function () {
        const targetDate = $(this).attr('data-date');
        modalDateDetail.modal('hide');
        $('#inputTambahTanggal').val(targetDate);
        $('#modal-tambah-libur').modal('show');
    });

    // 9. Edit Button Handlers
    function openEditModal(id, tanggal, nama, tipe) {
        $('#formUbahLibur').attr('action', baseEditUrl + '/' + encodeURIComponent(id) + '/ubah');
        $('#inputUbahTanggal').val(tanggal);
        $('#inputUbahNama').val(nama);
        if (tipe === 'leave') {
            $('#tipeUbahLeave').prop('checked', true);
        } else {
            $('#tipeUbahHoliday').prop('checked', true);
        }
        $('#modal-ubah-libur').modal('show');
    }

    $(document).on('click', '.btn-edit-holiday', function () {
        const id = $(this).attr('data-id');
        const tanggal = $(this).attr('data-tanggal');
        const nama = $(this).attr('data-nama');
        const tipe = $(this).attr('data-tipe');
        openEditModal(id, tanggal, nama, tipe);
    });

    $(document).on('click', '.btn-edit-holiday-direct', function () {
        modalDateDetail.modal('hide');
        const id = $(this).attr('data-id');
        const tanggal = $(this).attr('data-tanggal');
        const nama = $(this).attr('data-nama');
        const tipe = $(this).attr('data-tipe');
        openEditModal(id, tanggal, nama, tipe);
    });
});
</script>
<?= $this->endSection(); ?>
