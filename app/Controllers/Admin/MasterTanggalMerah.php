<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MstTanggalMerahModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

class MasterTanggalMerah extends BaseController
{
    private const MENU_LINK = 'admin/master/tanggal-merah';

    private const INDO_DAYS = [
        'Sunday'    => 'Minggu',
        'Monday'    => 'Senin',
        'Tuesday'   => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday'  => 'Kamis',
        'Friday'    => 'Jumat',
        'Saturday'  => 'Sabtu',
    ];

    private const INDO_MONTHS = [
        1  => 'Januari',
        2  => 'Februari',
        3  => 'Maret',
        4  => 'April',
        5  => 'Mei',
        6  => 'Juni',
        7  => 'Juli',
        8  => 'Agustus',
        9  => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    public function index()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $selectedYear = (int) ($this->request->getGet('year') ?? date('Y'));
        if ($selectedYear < 2000 || $selectedYear > 2099) {
            $selectedYear = (int) date('Y');
        }

        $model = new MstTanggalMerahModel();
        $items = $model->getByYear($selectedYear);
        $stats = $model->getStatsByYear($selectedYear);
        $calendarMap = $model->getCalendarMapByYear($selectedYear);

        // Build 12 months calendar structure
        $calendarMonths = $this->buildYearCalendar($selectedYear, $calendarMap);

        // Available years for dropdown
        $currentYear = (int) date('Y');
        $yearOptions = range(max(2020, $currentYear - 4), max(2030, $currentYear + 4));
        if (! in_array($selectedYear, $yearOptions, true)) {
            $yearOptions[] = $selectedYear;
            sort($yearOptions);
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        $canManage = $this->canManageMasterData();

        return view('admin/master/tanggal_merah', [
            'pageTitle'       => 'Master Tanggal Merah & Hari Libur',
            'selectedYear'    => $selectedYear,
            'yearOptions'     => $yearOptions,
            'items'           => $items,
            'stats'           => $stats,
            'calendarMap'     => $calendarMap,
            'calendarMonths'  => $calendarMonths,
            'indoMonths'      => self::INDO_MONTHS,
            'can_add'         => $canManage && (bool) ($menuPermissions['add'] ?? false),
            'can_edit'        => $canManage && (bool) ($menuPermissions['edit'] ?? false),
            'can_delete'      => $canManage && (bool) ($menuPermissions['delete'] ?? false),
            'can_export'      => $canManage && (bool) ($menuPermissions['export'] ?? false),
        ]);
    }

    /**
     * AJAX endpoint to fetch holiday data from upset.dev API and prepare diff preview
     */
    public function fetchApi(): ResponseInterface
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Akses ditolak.']);
        }

        $year = (int) ($this->request->getPost('year') ?? $this->request->getGet('year') ?? date('Y'));
        if ($year < 2000 || $year > 2099) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tahun tidak valid.']);
        }

        $apiUrl = "https://tanggalmerah.upset.dev/api/holidays?year={$year}";

        try {
            $client = \Config\Services::curlrequest([
                'timeout'         => 15,
                'connect_timeout' => 10,
                'http_errors'     => false,
                'headers'         => [
                    'Accept'     => 'application/json',
                    'User-Agent' => 'SatkerPPS-Client/1.0',
                ],
            ]);

            $apiResponse = $client->get($apiUrl);
            $statusCode = $apiResponse->getStatusCode();
            $body = (string) $apiResponse->getBody();

            if ($statusCode !== 200) {
                // Fallback to file_get_contents with stream context if cURL fails
                $ctx = stream_context_create([
                    'http' => [
                        'timeout' => 15,
                        'header'  => "Accept: application/json\r\nUser-Agent: SatkerPPS-Client/1.0\r\n",
                    ],
                ]);
                $fallbackBody = @file_get_contents($apiUrl, false, $ctx);
                if ($fallbackBody !== false) {
                    $body = $fallbackBody;
                    $statusCode = 200;
                }
            }

            if ($statusCode !== 200) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => "Gagal mengambil data dari API Tanggal Merah (HTTP status {$statusCode}). Silakan coba sesaat lagi.",
                ]);
            }

            $json = json_decode($body, true);
            if (! is_array($json) || empty($json['success']) || ! isset($json['data']) || ! is_array($json['data'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Format respons dari API Tanggal Merah tidak valid atau tidak memiliki data.',
                ]);
            }

            $apiHolidays = $json['data'];
            $meta = $json['meta'] ?? [
                'total'          => count($apiHolidays),
                'total_holidays' => 0,
                'total_leave'    => 0,
                'year'           => $year,
            ];

            // Compare with existing DB records
            $model = new MstTanggalMerahModel();
            $dbHolidays = $model->getCalendarMapByYear($year);

            $previewData = [];
            $newCount = 0;
            $updatedCount = 0;
            $sameCount = 0;

            foreach ($apiHolidays as $item) {
                $date = trim((string) ($item['date'] ?? ''));
                if ($date === '') {
                    continue;
                }

                $name = trim((string) ($item['name'] ?? ''));
                $type = strtolower(trim((string) ($item['type'] ?? 'holiday')));
                if ($type !== 'leave') {
                    $type = 'holiday';
                }

                $day = trim((string) ($item['day'] ?? ''));
                if ($day === '') {
                    $day = $this->getIndonesianDayName($date);
                }

                $status = 'new';
                $statusLabel = 'Data Baru';
                $statusBadge = 'badge-success';
                $isDifferent = false;
                $existingName = '';
                $existingType = '';

                if (isset($dbHolidays[$date])) {
                    $dbItem = $dbHolidays[$date];
                    $existingName = (string) ($dbItem['nama_libur'] ?? '');
                    $existingType = (string) ($dbItem['tipe'] ?? 'holiday');

                    if ($existingName !== $name || $existingType !== $type) {
                        $status = 'updated';
                        $statusLabel = 'Ada Perubahan';
                        $statusBadge = 'badge-warning';
                        $isDifferent = true;
                        $updatedCount++;
                    } else {
                        $status = 'same';
                        $statusLabel = 'Sudah Tersimpan';
                        $statusBadge = 'badge-secondary';
                        $sameCount++;
                    }
                } else {
                    $newCount++;
                }

                $previewData[] = [
                    'date'          => $date,
                    'date_indo'     => $this->formatIndoDate($date),
                    'day'           => $day,
                    'name'          => $name,
                    'type'          => $type,
                    'type_label'    => $type === 'leave' ? 'Cuti Bersama' : 'Libur Nasional',
                    'status'        => $status,
                    'status_label'  => $statusLabel,
                    'status_badge'  => $statusBadge,
                    'is_different'  => $isDifferent,
                    'existing_name' => $existingName,
                    'existing_type' => $existingType,
                    'selected'      => true,
                ];
            }

            return $this->response->setJSON([
                'success'        => true,
                'year'           => $year,
                'meta'           => $meta,
                'data'           => $previewData,
                'total_count'    => count($previewData),
                'new_count'      => $newCount,
                'updated_count'  => $updatedCount,
                'same_count'     => $sameCount,
                'existing_total' => count($dbHolidays),
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan koneksi saat mengakses API: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Endpoint to commit previewed API holiday data to database
     */
    public function simpanBatch(): ResponseInterface
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Akses ditolak.']);
        }

        if (! $this->canManageMasterData()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Anda tidak memiliki hak akses untuk menyimpan data master.']);
        }

        $year = (int) ($this->request->getPost('year') ?? date('Y'));
        $holidaysJson = (string) $this->request->getPost('holidays_json');
        $mode = (string) ($this->request->getPost('mode') ?? 'all'); // 'all', 'new_only', 'overwrite'

        $holidays = [];
        if ($holidaysJson !== '') {
            $holidays = json_decode($holidaysJson, true);
        }

        if (! is_array($holidays) || empty($holidays)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tidak ada data tanggal merah yang dipilih untuk disimpan.']);
        }

        $model = new MstTanggalMerahModel();
        $username = (string) (session()->get('username') ?? session()->get('name') ?? 'system');
        $now = date('Y-m-d H:i:s');

        $db = db_connect();
        $db->transStart();

        $savedCount = 0;
        $updatedCount = 0;

        foreach ($holidays as $item) {
            $date = trim((string) ($item['date'] ?? ''));
            if ($date === '') {
                continue;
            }

            $name = trim((string) ($item['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $type = strtolower(trim((string) ($item['type'] ?? 'holiday')));
            if ($type !== 'leave') {
                $type = 'holiday';
            }

            $day = trim((string) ($item['day'] ?? ''));
            if ($day === '') {
                $day = $this->getIndonesianDayName($date);
            }

            $itemYear = (int) date('Y', strtotime($date));
            if ($itemYear <= 0) {
                $itemYear = $year;
            }

            $existing = $model->where('tanggal', $date)->first();

            if (is_array($existing) && isset($existing['id'])) {
                if ($mode === 'new_only') {
                    // Skip existing in new_only mode
                    continue;
                }

                $model->update($existing['id'], [
                    'tahun'        => $itemYear,
                    'nama_libur'   => $name,
                    'tipe'         => $type,
                    'hari'         => $day,
                    'sumber'       => 'API',
                    'updated_by'   => $username,
                    'updated_date' => $now,
                ]);
                $updatedCount++;
            } else {
                $model->insert([
                    'tanggal'      => $date,
                    'tahun'        => $itemYear,
                    'nama_libur'   => $name,
                    'tipe'         => $type,
                    'hari'         => $day,
                    'sumber'       => 'API',
                    'created_by'   => $username,
                    'created_date' => $now,
                    'updated_by'   => $username,
                    'updated_date' => $now,
                ]);
                $savedCount++;
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menyimpan data ke database. Silakan coba kembali.',
            ]);
        }

        $totalProcessed = $savedCount + $updatedCount;
        $msg = "Berhasil menyimpan data tanggal merah tahun {$year}: {$savedCount} data baru ditambahkan, {$updatedCount} data diperbarui.";

        session()->setFlashdata('message', $msg);

        return $this->response->setJSON([
            'success'       => true,
            'message'       => $msg,
            'saved_count'   => $savedCount,
            'updated_count' => $updatedCount,
            'total'         => $totalProcessed,
        ]);
    }

    /**
     * Manual addition of a single holiday
     */
    public function create()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/tanggal-merah')->with('error', 'Anda tidak memiliki hak akses untuk menambah tanggal merah.');
        }

        $tanggal = trim((string) $this->request->getPost('tanggal'));
        $namaLibur = trim((string) $this->request->getPost('nama_libur'));
        $tipe = trim((string) $this->request->getPost('tipe'));
        if ($tipe !== 'leave') {
            $tipe = 'holiday';
        }

        if ($tanggal === '' || $namaLibur === '') {
            return redirect()->to('/admin/master/tanggal-merah')->withInput()->with('error', 'Tanggal dan Nama Libur wajib diisi.');
        }

        $tahun = (int) date('Y', strtotime($tanggal));
        $hari = $this->getIndonesianDayName($tanggal);

        $model = new MstTanggalMerahModel();
        $existing = $model->where('tanggal', $tanggal)->first();
        if ($existing !== null) {
            return redirect()->to('/admin/master/tanggal-merah?year=' . $tahun)->withInput()->with('error', "Tanggal {$tanggal} sudah ada di database sebagai: " . esc($existing['nama_libur']));
        }

        $username = (string) (session()->get('username') ?? 'system');
        $now = date('Y-m-d H:i:s');

        $model->insert([
            'tanggal'      => $tanggal,
            'tahun'        => $tahun,
            'nama_libur'   => $namaLibur,
            'tipe'         => $tipe,
            'hari'         => $hari,
            'sumber'       => 'Manual',
            'created_by'   => $username,
            'created_date' => $now,
            'updated_by'   => $username,
            'updated_date' => $now,
        ]);

        return redirect()->to('/admin/master/tanggal-merah?year=' . $tahun)->with('message', 'Tanggal merah baru berhasil ditambahkan.');
    }

    /**
     * Manual update of an existing holiday
     */
    public function edit(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/tanggal-merah')->with('error', 'Anda tidak memiliki hak akses untuk mengubah tanggal merah.');
        }

        $model = new MstTanggalMerahModel();
        $existing = $model->find($id);
        if (! is_array($existing)) {
            return redirect()->to('/admin/master/tanggal-merah')->with('error', 'Data tanggal merah tidak ditemukan.');
        }

        $tanggal = trim((string) $this->request->getPost('tanggal'));
        $namaLibur = trim((string) $this->request->getPost('nama_libur'));
        $tipe = trim((string) $this->request->getPost('tipe'));
        if ($tipe !== 'leave') {
            $tipe = 'holiday';
        }

        if ($tanggal === '' || $namaLibur === '') {
            return redirect()->to('/admin/master/tanggal-merah?year=' . $existing['tahun'])->withInput()->with('error', 'Tanggal dan Nama Libur wajib diisi.');
        }

        // Check uniqueness if date changed
        if ($tanggal !== $existing['tanggal']) {
            $duplicate = $model->where('tanggal', $tanggal)->where('id !=', $id)->first();
            if ($duplicate !== null) {
                return redirect()->to('/admin/master/tanggal-merah?year=' . $existing['tahun'])->withInput()->with('error', "Tanggal {$tanggal} sudah digunakan untuk data lain: " . esc($duplicate['nama_libur']));
            }
        }

        $tahun = (int) date('Y', strtotime($tanggal));
        $hari = $this->getIndonesianDayName($tanggal);
        $username = (string) (session()->get('username') ?? 'system');

        $model->update($id, [
            'tanggal'      => $tanggal,
            'tahun'        => $tahun,
            'nama_libur'   => $namaLibur,
            'tipe'         => $tipe,
            'hari'         => $hari,
            'updated_by'   => $username,
            'updated_date' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin/master/tanggal-merah?year=' . $tahun)->with('message', 'Data tanggal merah berhasil diperbarui.');
    }

    /**
     * Delete a single holiday
     */
    public function delete(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/tanggal-merah')->with('error', 'Anda tidak memiliki hak akses untuk menghapus data master.');
        }

        $model = new MstTanggalMerahModel();
        $existing = $model->find($id);
        if (! is_array($existing)) {
            return redirect()->to('/admin/master/tanggal-merah')->with('error', 'Data tanggal merah tidak ditemukan.');
        }

        $year = (int) ($existing['tahun'] ?? date('Y'));
        $model->delete($id);

        return redirect()->to('/admin/master/tanggal-merah?year=' . $year)->with('message', 'Data tanggal merah berhasil dihapus.');
    }

    /**
     * Delete all holidays for a given year
     */
    public function deleteYear(int $year)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/tanggal-merah?year=' . $year)->with('error', 'Anda tidak memiliki hak akses untuk menghapus data master.');
        }

        $model = new MstTanggalMerahModel();
        $deleted = $model->where('tahun', $year)->delete();

        return redirect()->to('/admin/master/tanggal-merah?year=' . $year)->with('message', "Seluruh data tanggal merah untuk tahun {$year} berhasil dibersihkan.");
    }

    /**
     * Export holiday list to Excel (.xls)
     */
    public function export()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $year = (int) ($this->request->getGet('year') ?? date('Y'));
        $model = new MstTanggalMerahModel();
        $items = $model->getByYear($year);

        $filename = "Master_Tanggal_Merah_{$year}_" . date('Ymd_His') . ".xls";

        header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
        echo '<style>';
        echo 'table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px; }';
        echo 'th { background-color: #007bff; color: #ffffff; font-weight: bold; border: 1px solid #000000; padding: 8px; text-align: center; }';
        echo 'td { border: 1px solid #cccccc; padding: 6px 8px; }';
        echo '.text-center { text-align: center; }';
        echo '.holiday { background-color: #ffebee; color: #c62828; font-weight: bold; }';
        echo '.leave { background-color: #fff8e1; color: #f57f17; font-weight: bold; }';
        echo '</style></head><body>';

        echo "<h2>DAFTAR MASTER TANGGAL MERAH & HARI LIBUR TAHUN {$year}</h2>";
        echo "<p>Satuan Kerja Prasarana Permukiman Strategis Provinsi Riau — Tanggal Ekspor: " . date('d M Y H:i:s') . "</p>";

        echo '<table>';
        echo '<thead>';
        echo '<tr>';
        echo '<th style="width: 40px;">No</th>';
        echo '<th style="width: 110px;">Tanggal</th>';
        echo '<th style="width: 90px;">Hari</th>';
        echo '<th style="width: 320px;">Keterangan / Nama Libur</th>';
        echo '<th style="width: 140px;">Kategori</th>';
        echo '<th style="width: 90px;">Sumber</th>';
        echo '<th style="width: 140px;">Terakhir Diperbarui</th>';
        echo '</tr>';
        echo '</thead><tbody>';

        $no = 1;
        foreach ($items as $item) {
            $isLeave = ($item['tipe'] ?? '') === 'leave';
            $cls = $isLeave ? 'leave' : 'holiday';
            $tipeLabel = $isLeave ? 'Cuti Bersama' : 'Libur Nasional';
            $tglIndo = $this->formatIndoDate($item['tanggal']);

            echo '<tr>';
            echo '<td class="text-center">' . $no++ . '</td>';
            echo '<td class="text-center">' . esc((string) $item['tanggal']) . '<br><small>' . esc($tglIndo) . '</small></td>';
            echo '<td class="text-center">' . esc((string) ($item['hari'] ?? '')) . '</td>';
            echo '<td>' . esc((string) ($item['nama_libur'] ?? '')) . '</td>';
            echo '<td class="text-center ' . $cls . '">' . esc($tipeLabel) . '</td>';
            echo '<td class="text-center">' . esc((string) ($item['sumber'] ?? 'API')) . '</td>';
            echo '<td class="text-center">' . esc((string) ($item['updated_date'] ?? $item['created_date'] ?? '-')) . '</td>';
            echo '</tr>';
        }

        if (empty($items)) {
            echo '<tr><td colspan="7" class="text-center" style="padding: 20px;">Tidak ada data tanggal merah untuk tahun ' . $year . '</td></tr>';
        }

        echo '</tbody></table></body></html>';
        exit;
    }

    /**
     * Build 12 calendar month matrices for interactive calendar rendering
     */
    private function buildYearCalendar(int $year, array $calendarMap): array
    {
        $months = [];

        for ($m = 1; $m <= 12; $m++) {
            $firstDayTimestamp = mktime(0, 0, 0, $m, 1, $year);
            $daysInMonth = (int) date('t', $firstDayTimestamp);
            $startDayOfWeek = (int) date('w', $firstDayTimestamp); // 0 = Sunday, 1 = Monday, ..., 6 = Saturday

            $weeks = [];
            $currentWeek = [];

            // Pad blank cells before first day of month
            for ($pad = 0; $pad < $startDayOfWeek; $pad++) {
                $currentWeek[] = [
                    'is_padding' => true,
                    'day_number' => '',
                    'date'       => '',
                    'is_sunday'  => ($pad === 0),
                    'is_holiday' => false,
                    'holiday'    => null,
                ];
            }

            // Fill actual month days
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $dateStr = sprintf('%04d-%02d-%02d', $year, $m, $day);
                $dayOfWeekIndex = (count($currentWeek) % 7);
                $isSunday = ($dayOfWeekIndex === 0);
                $isSaturday = ($dayOfWeekIndex === 6);

                $holidayInfo = $calendarMap[$dateStr] ?? null;
                $isHoliday = ($holidayInfo !== null);

                $currentWeek[] = [
                    'is_padding'  => false,
                    'day_number'  => $day,
                    'date'        => $dateStr,
                    'is_sunday'   => $isSunday,
                    'is_saturday' => $isSaturday,
                    'is_holiday'  => $isHoliday,
                    'holiday'     => $holidayInfo,
                ];

                if (count($currentWeek) === 7) {
                    $weeks[] = $currentWeek;
                    $currentWeek = [];
                }
            }

            // Pad blank cells at the end of month
            if (! empty($currentWeek)) {
                while (count($currentWeek) < 7) {
                    $currentWeek[] = [
                        'is_padding' => true,
                        'day_number' => '',
                        'date'       => '',
                        'is_sunday'  => (count($currentWeek) === 0),
                        'is_holiday' => false,
                        'holiday'    => null,
                    ];
                }
                $weeks[] = $currentWeek;
            }

            $months[$m] = [
                'month_number' => $m,
                'month_name'   => self::INDO_MONTHS[$m],
                'weeks'        => $weeks,
            ];
        }

        return $months;
    }

    private function getIndonesianDayName(string $date): string
    {
        $englishDay = date('l', strtotime($date));
        return self::INDO_DAYS[$englishDay] ?? $englishDay;
    }

    private function formatIndoDate(string $date): string
    {
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return $date;
        }

        $d = date('j', $timestamp);
        $m = (int) date('n', $timestamp);
        $y = date('Y', $timestamp);

        $monthName = self::INDO_MONTHS[$m] ?? date('M', $timestamp);

        return "{$d} {$monthName} {$y}";
    }

    private function canManageMasterData(): bool
    {
        $role = strtolower(trim((string) session()->get('role')));
        return in_array($role, ['admin', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
    }

    private function denyIfNoMenuAccess(string $menuLink): ?RedirectResponse
    {
        if ($this->hasMenuAccess($menuLink)) {
            return null;
        }

        return redirect()->to('/forbidden?from=' . rawurlencode($menuLink));
    }

    private function hasMenuAccess(string $menuLink): bool
    {
        if ($this->canManageMasterData()) {
            return true;
        }

        $db = db_connect();
        if (! $db->tableExists('menu_akses')) {
            return true;
        }

        $roleId = $this->resolveRoleId((string) session()->get('role'), $db);
        if ($roleId === null) {
            return false;
        }

        $menuId = $this->resolveMenuIdByLink($menuLink, $db);
        if ($menuId === null) {
            return false;
        }

        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';

        return (int) $db->table('menu_akses')
            ->where($roleColumn, $roleId)
            ->where('menu_id', $menuId)
            ->countAllResults() > 0;
    }

    private function resolveMenuPermissions(string $menuLink): array
    {
        $default = [
            'add'      => false,
            'edit'     => false,
            'delete'   => false,
            'export'   => false,
            'import'   => false,
            'approval' => false,
        ];

        $db = db_connect();
        if (! $db->tableExists('menu_akses')) {
            return $default;
        }

        $roleId = $this->resolveRoleId((string) session()->get('role'), $db);
        $menuId = $this->resolveMenuIdByLink($menuLink, $db);
        if ($roleId === null || $menuId === null) {
            return $default;
        }

        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
        $row = $db->table('menu_akses')
            ->select('FiturAdd, FiturEdit, FiturDelete, FiturExport, FiturImport, FiturApproval')
            ->where($roleColumn, $roleId)
            ->where('menu_id', $menuId)
            ->get()
            ->getRowArray();

        if (! is_array($row)) {
            return $default;
        }

        return [
            'add'      => (bool) ((int) ($row['FiturAdd'] ?? 0)),
            'edit'     => (bool) ((int) ($row['FiturEdit'] ?? 0)),
            'delete'   => (bool) ((int) ($row['FiturDelete'] ?? 0)),
            'export'   => (bool) ((int) ($row['FiturExport'] ?? 0)),
            'import'   => (bool) ((int) ($row['FiturImport'] ?? 0)),
            'approval' => (bool) ((int) ($row['FiturApproval'] ?? 0)),
        ];
    }

    private function resolveRoleId(string $role, $db): ?int
    {
        $normalized = strtolower(trim($role));
        if ($normalized === '') {
            return null;
        }

        if ($db->tableExists('access_roles')) {
            $variants = [$normalized];
            if ($normalized === 'super administrator') {
                $variants[] = 'super_administrator';
                $variants[] = 'super-admin';
                $variants[] = 'superadmin';
            } elseif ($normalized === 'super_administrator' || $normalized === 'super-admin' || $normalized === 'superadmin') {
                $variants[] = 'super administrator';
                $variants[] = 'super_administrator';
                $variants[] = 'super-admin';
                $variants[] = 'superadmin';
            }

            $row = $db->table('access_roles')
                ->select('id')
                ->whereIn('role_key', array_values(array_unique($variants)))
                ->where('is_active', 1)
                ->orderBy('id', 'ASC')
                ->get()
                ->getRowArray();

            if (is_array($row) && isset($row['id'])) {
                return (int) $row['id'];
            }
        }

        return match ($normalized) {
            'admin'  => 1,
            'editor' => 2,
            default  => null,
        };
    }

    private function resolveMenuIdByLink(string $menuLink, $db): ?string
    {
        foreach (['menu_lv3', 'menu_lv2', 'menu_lv1'] as $table) {
            if (! $db->tableExists($table) || ! $db->fieldExists('link', $table)) {
                continue;
            }

            $row = $db->table($table)
                ->select('id')
                ->where('LOWER(link)', strtolower(trim($menuLink)))
                ->orderBy('id', 'ASC')
                ->get()
                ->getRowArray();

            if (is_array($row) && isset($row['id'])) {
                return (string) $row['id'];
            }
        }

        return null;
    }
}
