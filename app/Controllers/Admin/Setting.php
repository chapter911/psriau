<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AppSettingModel;
use App\Models\MenuLv1Model;
use App\Models\MenuLv2Model;
use App\Models\MenuLv3Model;

class Setting extends BaseController
// ...existing code...
{
    private const ADMINLTE_SIDEBAR_DEFAULTS = [
        'sidebar_bg_color'          => '#343A40',
        'sidebar_text_color'        => '#C2C7D0',
        'sidebar_active_bg_color'   => '#007BFF',
        'sidebar_active_text_color' => '#FFFFFF',
    ];

    public function application()
    {
        $settingModel = new AppSettingModel();
        $setting      = $this->getOrCreateAppSetting($settingModel);

        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'app_name'            => 'required|min_length[3]',
                'primary_color'       => 'required|regex_match[/^#[0-9A-Fa-f]{6}$/]',
                'sidebar_bg_color'    => 'required|regex_match[/^#[0-9A-Fa-f]{6}$/]',
                'sidebar_text_color'  => 'required|regex_match[/^#[0-9A-Fa-f]{6}$/]',
                'sidebar_active_bg_color' => 'required|regex_match[/^#[0-9A-Fa-f]{6}$/]',
                'sidebar_active_text_color' => 'required|regex_match[/^#[0-9A-Fa-f]{6}$/]',
                'simak_upload_tutorial_url' => 'permit_empty|valid_url_strict[https]',
                'simak_max_upload_mb' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[1000]',
                'auto_logout_minutes' => 'required|integer|greater_than_equal_to[1]|less_than_equal_to[1440]',
                'preloader_duration_ms' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[10000]',
                'maintenance_mode' => 'permit_empty|in_list[0,1]',
            ];

            $appLogoFile = $this->request->getFile('app_logo_file');
            if ($appLogoFile && $appLogoFile->getError() !== UPLOAD_ERR_NO_FILE) {
                $rules['app_logo_file'] = 'is_image[app_logo_file]|max_size[app_logo_file,2048]|mime_in[app_logo_file,image/jpg,image/jpeg,image/png,image/webp,image/svg+xml]';
            }

            $loginBgFile = $this->request->getFile('login_bg_file');
            if ($loginBgFile && $loginBgFile->getError() !== UPLOAD_ERR_NO_FILE) {
                $rules['login_bg_file'] = 'is_image[login_bg_file]|max_size[login_bg_file,4096]|mime_in[login_bg_file,image/jpg,image/jpeg,image/png,image/webp]';
            }

            if (! $this->validate($rules)) {
                return redirect()->back()->withInput()->with('error', 'Data pengaturan aplikasi belum valid.');
            }

            $appLogoPath = $this->uploadImage('app_logo_file', 'branding');
            $loginBgPath = $this->uploadImage('login_bg_file', 'branding');

            if ($appLogoPath !== null) {
                $this->deleteLocalImage($setting['app_logo_url'] ?? null);
            }

            if ($loginBgPath !== null) {
                $this->deleteLocalImage($setting['login_background_url'] ?? null);
            }

            $maintenanceMode = (string) $this->request->getPost('maintenance_mode');
            $data = [
                'app_name'             => trim((string) $this->request->getPost('app_name')),
                'primary_color'        => strtoupper((string) $this->request->getPost('primary_color')),
                'sidebar_bg_color'     => strtoupper((string) $this->request->getPost('sidebar_bg_color')),
                'sidebar_text_color'   => strtoupper((string) $this->request->getPost('sidebar_text_color')),
                'sidebar_active_bg_color' => strtoupper((string) $this->request->getPost('sidebar_active_bg_color')),
                'sidebar_active_text_color' => strtoupper((string) $this->request->getPost('sidebar_active_text_color')),
                'app_logo_url'         => $appLogoPath ?? ($setting['app_logo_url'] ?? null),
                'login_background_url' => $loginBgPath ?? ($setting['login_background_url'] ?? null),
                'simak_upload_tutorial_url' => $this->normalizeNullableText($this->request->getPost('simak_upload_tutorial_url')),
                'simak_max_upload_mb'  => (int) $this->request->getPost('simak_max_upload_mb'),
                'auto_logout_minutes'  => (int) $this->request->getPost('auto_logout_minutes'),
                'preloader_duration_ms' => (int) $this->request->getPost('preloader_duration_ms'),
                'updated_at'           => date('Y-m-d H:i:s'),
                'updated_by'           => (int) session()->get('userId'),
            ];

            if ($this->hasAppSettingsColumn('maintenance_mode')) {
                $data['maintenance_mode'] = $maintenanceMode === '1' ? 1 : 0;
            }

            $settingModel->update((int) $setting['id'], $data);

            return redirect()->to('/admin/pengaturan/application')->with('message', 'Pengaturan aplikasi berhasil diperbarui.');
        }

        return view('admin/settings/application', [
            'setting'        => $setting,
        ]);
    }

    public function gitPull()
    {
        $redirectTarget = $this->resolveOpsRedirectTarget();

        if (! $this->canAccessProductionUtilities()) {
            return redirect()->to($redirectTarget)->with('error', 'Akses ditolak. Fitur ini hanya untuk super administrator di production.');
        }

        $rootDir = ROOTPATH;

        // 1. Dapatkan nama branch saat ini (default: main)
        [$successBranch, $branchOutput] = $this->runShellCommand('cd ' . escapeshellarg($rootDir) . ' && git rev-parse --abbrev-ref HEAD 2>&1');
        $branch = $successBranch ? trim($branchOutput) : 'main';
        if ($branch === '') {
            $branch = 'main';
        }

        // 2. Coba jalankan git pull --ff-only terlebih dahulu
        [$success, $output] = $this->runShellCommand('cd ' . escapeshellarg($rootDir) . ' && git pull --ff-only 2>&1');

        // 3. Jika gagal dan terdeteksi diverging/conflict/local changes/unfinished merge, lakukan fallback fetch & reset --hard
        if (! $success) {
            $outputLower = strtolower($output);
            $isDiverged = stripos($outputLower, 'diverging') !== false
                || stripos($outputLower, 'fast-forward') !== false
                || stripos($outputLower, 'rebase') !== false;
            $hasLocalChanges = stripos($outputLower, 'local changes') !== false
                || stripos($outputLower, 'overwrite') !== false
                || stripos($outputLower, 'discard') !== false;
            $hasMergeIssues = stripos($outputLower, 'merge_head') !== false
                || stripos($outputLower, 'concluded your merge') !== false
                || stripos($outputLower, 'conflict') !== false;

            if ($isDiverged || $hasLocalChanges || $hasMergeIssues) {
                // Jalankan fetch & reset --hard ke origin/branch
                $resetCmd = 'cd ' . escapeshellarg($rootDir) . ' && git fetch origin ' . escapeshellarg($branch) . ' 2>&1 && git reset --hard origin/' . escapeshellarg($branch) . ' 2>&1';
                [$resetSuccess, $resetOutput] = $this->runShellCommand($resetCmd);
                if ($resetSuccess) {
                    $success = true;
                    $output = "Git pull standar gagal, tetapi otomatis disinkronkan menggunakan fallback reset.\n\nDetail output git pull sebelumnya:\n" . $output . "\n\nDetail output reset:\n" . $resetOutput;
                } else {
                    $output .= "\n\n[Fallback reset juga gagal]:\n" . $resetOutput;
                }
            }
        }

        $message = $success ? 'Git pull selesai.' : 'Git pull gagal.';

        if ($success) {
            if (function_exists('opcache_reset')) {
                @opcache_reset();
            }
        }

        return redirect()->to($redirectTarget)
            ->with($success ? 'message' : 'error', $message)
            ->with('command_result', [
                'title' => 'Hasil Git Pull',
                'success' => $success,
                'output' => $output !== '' ? $output : 'Tidak ada output.',
            ]);
    }

    public function mergeDatabase()
    {
        $redirectTarget = $this->resolveOpsRedirectTarget();

        if (! $this->canAccessProductionUtilities()) {
            return redirect()->to($redirectTarget)->with('error', 'Akses ditolak. Fitur ini hanya untuk super administrator di production.');
        }

        $phpCli = $this->resolvePhpCliCommand();
        [$success, $output] = $this->runShellCommand('cd ' . escapeshellarg(ROOTPATH) . ' && ' . $phpCli . ' spark migrate 2>&1');

        if (! $success && strpos($output, 'CodeIgniter\\CLI\\STDOUT') !== false) {
            $output .= PHP_EOL . PHP_EOL . 'Hint: Proses migrate terdeteksi berjalan dengan PHP non-CLI (fpm/cgi). Pastikan command menggunakan PHP CLI.';
        }

        $message = $success ? 'Merge database (migrate) selesai.' : 'Merge database (migrate) gagal.';

        return redirect()->to($redirectTarget)
            ->with($success ? 'message' : 'error', $message)
            ->with('command_result', [
                'title' => 'Hasil Merge Database',
                'success' => $success,
                'output' => $output !== '' ? $output : 'Tidak ada output.',
            ]);
    }

    public function databaseTables()
    {
        if (! $this->canAccessProductionUtilities()) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Akses ditolak. Fitur ini hanya untuk super administrator di production.',
            ]);
        }

        try {
            $db = \Config\Database::connect();
            $databaseName = $db->database;

            $tables = $db->listTables();

            $infoMap = [];
            $infoQuery = $db->query("
                SELECT 
                    TABLE_NAME as name, 
                    TABLE_ROWS as total_rows,
                    (COALESCE(DATA_LENGTH, 0) + COALESCE(INDEX_LENGTH, 0)) as total_bytes
                FROM INFORMATION_SCHEMA.TABLES 
                WHERE TABLE_SCHEMA = " . $db->escape($databaseName)
            );
            if ($infoQuery) {
                foreach ($infoQuery->getResultArray() as $info) {
                    $infoMap[$info['name']] = [
                        'rows' => (int) ($info['total_rows'] ?? 0),
                        'bytes' => (int) ($info['total_bytes'] ?? 0),
                    ];
                }
            }

            $data = [];
            foreach ($tables as $t) {
                $bytes = (int) ($infoMap[$t]['bytes'] ?? 0);
                $data[] = [
                    'name' => $t,
                    'rows' => (int) ($infoMap[$t]['rows'] ?? 0),
                    'bytes' => $bytes,
                    'size_formatted' => $this->formatBytes($bytes),
                ];
            }

            return $this->response->setJSON([
                'status' => 'ok',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Error fetching database tables: ' . $e->getMessage());

            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Gagal memuat daftar tabel database: ' . $e->getMessage(),
            ]);
        }
    }

    private function formatBytes(int $bytes, int $precision = 1): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        if ($bytes === 0) {
            return '0 B';
        }
        $pow = (int) floor(log($bytes) / log(1024));
        $pow = min($pow, count($units) - 1);
        $value = $bytes / (1024 ** $pow);

        return round($value, $precision) . ' ' . $units[$pow];
    }

    public function extractDatabase()
    {
        $redirectTarget = $this->resolveOpsRedirectTarget();

        if (! $this->canAccessProductionUtilities()) {
            return redirect()->to($redirectTarget)->with('error', 'Akses ditolak. Fitur ini hanya untuk super administrator di production.');
        }

        $rawTables = $this->request->getPost('tables');
        $selectedTables = [];
        if (is_array($rawTables)) {
            $selectedTables = array_values(array_filter(array_map('trim', $rawTables)));
            if (empty($selectedTables)) {
                return redirect()->to($redirectTarget)->with('error', 'Pilih setidaknya 1 tabel untuk diekstrak.');
            }
        }

        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        $backupDir = WRITEPATH . 'backups';
        if (! is_dir($backupDir)) {
            @mkdir($backupDir, 0755, true);
        }

        $filename = 'db_extract_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.sql';
        $filePath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        $db = \Config\Database::connect();
        $databaseName = $db->database;
        $hostname = $db->hostname;
        $username = $db->username;
        $password = $db->password;
        $port = $db->port ?: 3306;

        $dumpedViaShell = false;

        $mysqldumpCmd = $this->resolveMysqldumpCommand();
        if ($mysqldumpCmd !== null) {
            $passParam = $password !== '' ? '--password=' . escapeshellarg((string) $password) : '';
            $tableArgs = '';
            if (! empty($selectedTables)) {
                $escapedTables = array_map(static fn($t) => escapeshellarg((string) $t), $selectedTables);
                $tableArgs = ' ' . implode(' ', $escapedTables);
            }

            $cmd = sprintf(
                '%s --host=%s --port=%s --user=%s %s %s%s > %s 2>&1',
                $mysqldumpCmd,
                escapeshellarg((string) $hostname),
                escapeshellarg((string) $port),
                escapeshellarg((string) $username),
                $passParam,
                escapeshellarg((string) $databaseName),
                $tableArgs,
                escapeshellarg((string) $filePath)
            );
            [$success, $output] = $this->runShellCommand($cmd);
            if ($success && file_exists($filePath) && filesize($filePath) > 0) {
                $dumpedViaShell = true;
            }
        }

        if (! $dumpedViaShell) {
            try {
                $this->generatePhpDatabaseDump($db, $filePath, $selectedTables);
            } catch (\Throwable $e) {
                log_message('error', 'Failed to generate database extract dump: ' . $e->getMessage());
                return redirect()->to($redirectTarget)->with('error', 'Gagal membuat ekstraksi database: ' . $e->getMessage());
            }
        }

        if (! file_exists($filePath) || filesize($filePath) === 0) {
            return redirect()->to($redirectTarget)->with('error', 'File ekstrak database gagal dibuat.');
        }

        $downloadName = ! empty($selectedTables) && count($selectedTables) === 1
            ? 'db_table_' . reset($selectedTables) . '_' . date('Y-m-d_H-i-s') . '.sql'
            : 'database_backup_' . date('Y-m-d_H-i-s') . '.sql';

        return $this->response->download($filePath, null)->setFileName($downloadName);
    }

    private function resolveMysqldumpCommand(): ?string
    {
        $candidates = ['mysqldump', '/usr/bin/mysqldump', '/usr/local/bin/mysqldump', '/usr/mysql/bin/mysqldump', '/opt/homebrew/bin/mysqldump'];
        foreach ($candidates as $cmd) {
            if ($this->hasShellCommand($cmd)) {
                return $cmd;
            }
        }

        return null;
    }

    private function generatePhpDatabaseDump($db, string $filePath, array $selectedTables = []): void
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        $handle = fopen($filePath, 'w');
        if (! $handle) {
            throw new \RuntimeException('Tidak dapat membuat file dump database.');
        }

        /** @var \mysqli|null $mysqli */
        $mysqli = $db->connID instanceof \mysqli ? $db->connID : null;

        $databaseName = $db->database;
        $now = date('Y-m-d H:i:s');

        fwrite($handle, "-- ========================================================\n");
        fwrite($handle, "-- Database Extract Dump\n");
        fwrite($handle, "-- Database: {$databaseName}\n");
        fwrite($handle, "-- Date: {$now}\n");
        fwrite($handle, "-- ========================================================\n\n");
        fwrite($handle, "SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT;\n");
        fwrite($handle, "SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS;\n");
        fwrite($handle, "SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION;\n");
        fwrite($handle, "SET NAMES utf8mb4;\n");
        fwrite($handle, "SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;\n");
        fwrite($handle, "SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';\n");
        fwrite($handle, "SET time_zone = \"+00:00\";\n\n");

        $allTables = $db->listTables();
        if (! empty($selectedTables)) {
            $tables = array_values(array_intersect($allTables, $selectedTables));
        } else {
            $tables = $allTables;
        }

        foreach ($tables as $table) {
            $safeTable = '`' . str_replace('`', '``', $table) . '`';

            fwrite($handle, "-- --------------------------------------------------------\n");
            fwrite($handle, "-- Table structure for table {$safeTable}\n");
            fwrite($handle, "-- --------------------------------------------------------\n");
            fwrite($handle, "DROP TABLE IF EXISTS {$safeTable};\n");

            if ($mysqli) {
                $res = $mysqli->query("SHOW CREATE TABLE {$safeTable}");
                if ($res) {
                    $row = $res->fetch_assoc();
                    $res->free();
                    if ($row) {
                        $createSql = $row['Create Table'] ?? array_values($row)[1] ?? null;
                        if ($createSql) {
                            fwrite($handle, $createSql . ";\n\n");
                        }
                    }
                }
            } else {
                $createTableQuery = $db->query("SHOW CREATE TABLE {$safeTable}");
                if ($createTableQuery) {
                    $createRow = $createTableQuery->getRowArray();
                    $createTableQuery->freeResult();
                    if ($createRow) {
                        $createSql = $createRow['Create Table'] ?? array_values($createRow)[1] ?? null;
                        if ($createSql) {
                            fwrite($handle, $createSql . ";\n\n");
                        }
                    }
                }
            }

            if ($mysqli) {
                // Direct stream write row-by-row into handle to avoid implode/array memory allocation
                $result = $mysqli->query("SELECT * FROM {$safeTable}", MYSQLI_USE_RESULT);
                if ($result) {
                    $bufferCount = 0;
                    $hasWrittenHeader = false;

                    while ($row = $result->fetch_assoc()) {
                        if (! $hasWrittenHeader) {
                            fwrite($handle, "-- Dumping data for table {$safeTable}\n");
                            $hasWrittenHeader = true;
                        }

                        if ($bufferCount === 0) {
                            fwrite($handle, "INSERT INTO {$safeTable} VALUES \n");
                        } else {
                            fwrite($handle, ",\n");
                        }

                        fwrite($handle, '(');
                        $isFirstCol = true;
                        foreach ($row as $value) {
                            if (! $isFirstCol) {
                                fwrite($handle, ', ');
                            }
                            $isFirstCol = false;

                            if ($value === null) {
                                fwrite($handle, 'NULL');
                            } elseif (is_int($value) || is_float($value)) {
                                fwrite($handle, (string) $value);
                            } else {
                                fwrite($handle, "'");
                                fwrite($handle, $mysqli->real_escape_string((string) $value));
                                fwrite($handle, "'");
                            }
                        }
                        fwrite($handle, ')');

                        $bufferCount++;

                        if ($bufferCount >= 50) {
                            fwrite($handle, ";\n");
                            $bufferCount = 0;
                        }
                    }

                    if ($bufferCount > 0) {
                        fwrite($handle, ";\n");
                        $bufferCount = 0;
                    }

                    if ($hasWrittenHeader) {
                        fwrite($handle, "\n");
                    }

                    $result->free();

                    while ($mysqli->more_results()) {
                        $mysqli->next_result();
                        if ($extraRes = $mysqli->store_result()) {
                            $extraRes->free();
                        }
                    }
                }
            } else {
                // Fallback direct stream write for non-mysqli driver
                $query = $db->query("SELECT * FROM {$safeTable}");
                if ($query) {
                    $bufferCount = 0;
                    $hasWrittenHeader = false;

                    while ($row = $query->getUnbufferedRow('array')) {
                        if (! $hasWrittenHeader) {
                            fwrite($handle, "-- Dumping data for table {$safeTable}\n");
                            $hasWrittenHeader = true;
                        }

                        if ($bufferCount === 0) {
                            fwrite($handle, "INSERT INTO {$safeTable} VALUES \n");
                        } else {
                            fwrite($handle, ",\n");
                        }

                        fwrite($handle, '(');
                        $isFirstCol = true;
                        foreach ($row as $value) {
                            if (! $isFirstCol) {
                                fwrite($handle, ', ');
                            }
                            $isFirstCol = false;

                            if ($value === null) {
                                fwrite($handle, 'NULL');
                            } elseif (is_int($value) || is_float($value)) {
                                fwrite($handle, (string) $value);
                            } else {
                                fwrite($handle, $db->escape($value));
                            }
                        }
                        fwrite($handle, ')');

                        $bufferCount++;

                        if ($bufferCount >= 50) {
                            fwrite($handle, ";\n");
                            $bufferCount = 0;
                        }
                    }

                    if ($bufferCount > 0) {
                        fwrite($handle, ";\n");
                        $bufferCount = 0;
                    }

                    if ($hasWrittenHeader) {
                        fwrite($handle, "\n");
                    }

                    $query->freeResult();
                }
            }
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1);\n");
        fwrite($handle, "SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '');\n");
        fclose($handle);
    }

    private function resolvePhpCliCommand(): string
    {
        // Prefer the "php" command from PATH because web SAPIs often point PHP_BINARY to fpm/cgi.
        if ($this->hasShellCommand('php')) {
            return 'php';
        }

        $phpBinary = trim((string) PHP_BINARY);
        if ($phpBinary !== '' && stripos($phpBinary, 'cgi') === false && stripos($phpBinary, 'fpm') === false) {
            return escapeshellarg($phpBinary);
        }

        return 'php';
    }

    private function hasShellCommand(string $command): bool
    {
        $output = [];
        $exitCode = 1;
        @exec('command -v ' . escapeshellarg($command) . ' >/dev/null 2>&1', $output, $exitCode);

        return $exitCode === 0;
    }

    private function resolveOpsRedirectTarget(): string
    {
        $fallback = '/admin/pengaturan/application';
        $candidates = [
            (string) $this->request->getPost('redirect_to'),
            (string) $this->request->getServer('HTTP_REFERER'),
        ];

        foreach ($candidates as $candidate) {
            $candidate = trim($candidate);
            if ($candidate === '') {
                continue;
            }

            if (strpos($candidate, '://') !== false) {
                $parts = parse_url($candidate);
                $host = strtolower((string) ($parts['host'] ?? ''));
                $currentHost = strtolower((string) $this->request->getServer('HTTP_HOST'));
                $currentHost = trim(explode(':', $currentHost)[0] ?? $currentHost);

                if ($host === '' || $host !== $currentHost) {
                    continue;
                }

                $path = (string) ($parts['path'] ?? '');
                $query = (string) ($parts['query'] ?? '');
                if ($path === '' || $path[0] !== '/') {
                    continue;
                }

                return $path . ($query !== '' ? '?' . $query : '');
            }

            if ($candidate[0] === '/') {
                return $candidate;
            }
        }

        return $fallback;
    }

    public function errorLogsByDate()
    {
        if (! $this->canAccessProductionUtilities()) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Akses ditolak.',
            ]);
        }

        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Permintaan tidak valid.',
            ]);
        }

        $fileName = trim((string) $this->request->getGet('file'));
        if ($fileName === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => 'File log tidak valid.',
            ]);
        }

        $availableFiles = $this->getAvailableErrorLogFiles(50);
        if (! in_array($fileName, $availableFiles, true)) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'File log tidak ditemukan.',
            ]);
        }

        $logFile = WRITEPATH . 'logs/' . $fileName;
        if (! is_file($logFile)) {
            return $this->response->setJSON([
                'status' => 'ok',
                'data' => [
                    'file' => $fileName,
                    'content' => '',
                ],
            ]);
        }

        $contentData = $this->readLogTailContent($logFile, 1000);

        return $this->response->setJSON([
            'status' => 'ok',
            'data' => [
                'file' => $fileName,
                'content' => $contentData['content'],
                'isTruncated' => $contentData['isTruncated'],
                'totalLines' => $contentData['totalLines'],
                'displayedLines' => $contentData['displayedLines'],
            ],
        ]);
    }

    public function errorLogDates()
    {
        if (! $this->canAccessProductionUtilities()) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Akses ditolak.',
            ]);
        }

        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Permintaan tidak valid.',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'ok',
            'data' => $this->getAvailableErrorLogFiles(3),
        ]);
    }

    public function resetSidebarDefaults()
    {
        $settingModel = new AppSettingModel();
        $setting      = $this->getOrCreateAppSetting($settingModel);

        $settingModel->update((int) $setting['id'], [
            'sidebar_bg_color'          => self::ADMINLTE_SIDEBAR_DEFAULTS['sidebar_bg_color'],
            'sidebar_text_color'        => self::ADMINLTE_SIDEBAR_DEFAULTS['sidebar_text_color'],
            'sidebar_active_bg_color'   => self::ADMINLTE_SIDEBAR_DEFAULTS['sidebar_active_bg_color'],
            'sidebar_active_text_color' => self::ADMINLTE_SIDEBAR_DEFAULTS['sidebar_active_text_color'],
            'updated_at'                => date('Y-m-d H:i:s'),
            'updated_by'                => (int) session()->get('userId'),
        ]);

        return redirect()->to('/admin/pengaturan/application')->with('message', 'Warna sidebar berhasil direset ke default AdminLTE.');
    }

    public function testEmail()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Permintaan tidak valid.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        $to = trim((string) $this->request->getPost('to_email'));
        $subject = trim((string) $this->request->getPost('subject'));
        $message = trim((string) $this->request->getPost('message'));

        if ($to === '' || filter_var($to, FILTER_VALIDATE_EMAIL) === false) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => 'Alamat email tujuan tidak valid.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        if ($subject === '') {
            $subject = 'Uji Coba Email - SATKER PPS Riau';
        }

        if ($message === '') {
            $message = 'Ini adalah email uji coba dari halaman Pengaturan Aplikasi.';
        }

        $email = \Config\Services::email();
        $emailConfig = config('Email');
        $fromEmail = trim((string) ($emailConfig->fromEmail ?? ''));
        $fromName = trim((string) ($emailConfig->fromName ?? 'SATKER PPS Riau'));

        if ($fromEmail === '') {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Konfigurasi EMAIL_FROM belum diatur.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        try {
            $email->clear(true);
            $email->setFrom($fromEmail, $fromName !== '' ? $fromName : 'SATKER PPS Riau');
            $email->setTo($to);
            $email->setSubject($subject);
            $email->setMessage($message);

            if (! $email->send()) {
                $debugRaw = (string) $email->printDebugger(['headers', 'subject', 'body']);
                $debug = $this->sanitizeMailerDebug(trim(strip_tags($debugRaw)));

                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal mengirim email uji coba.',
                    'error_detail' => $debug !== '' ? $debug : 'Tidak ada detail error dari mailer.',
                    'csrf_hash' => csrf_hash(),
                ]);
            }

            return $this->response->setJSON([
                'status' => 'ok',
                'message' => 'Email uji coba berhasil dikirim ke ' . $to . '.',
                'csrf_hash' => csrf_hash(),
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Terjadi error saat mengirim email uji coba.',
                'error_detail' => $this->sanitizeMailerDebug((string) $e->getMessage()),
                'csrf_hash' => csrf_hash(),
            ]);
        }
    }

    private function sanitizeMailerDebug(string $text): string
    {
        $sanitized = trim($text);
        if ($sanitized === '') {
            return $sanitized;
        }

        // Hide SMTP password-like fields.
        $sanitized = (string) preg_replace('/(SMTPPass|password|passwd|pwd)\s*[:=]\s*[^\s\r\n]+/i', '$1: [REDACTED]', $sanitized);

        // Hide AUTH LOGIN / PLAIN payloads that may include base64 credentials.
        $sanitized = (string) preg_replace('/(AUTH\s+(?:LOGIN|PLAIN)\s+)[A-Za-z0-9+\/=._-]+/i', '$1[REDACTED]', $sanitized);

        // Mask email addresses but keep domain for diagnostics.
        $sanitized = (string) preg_replace_callback(
            '/([A-Z0-9._%+-]+)@([A-Z0-9.-]+\.[A-Z]{2,})/i',
            static function (array $m): string {
                $local = (string) ($m[1] ?? '');
                $domain = (string) ($m[2] ?? '');
                if ($local === '') {
                    return '[REDACTED]@' . $domain;
                }

                return substr($local, 0, 1) . '***@' . $domain;
            },
            $sanitized
        );

        return $sanitized;
    }

    public function menus()
    {
        $menuLv1 = (new MenuLv1Model())
            ->orderBy('ordering', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        $menuLv2 = (new MenuLv2Model())
            ->orderBy('ordering', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        $menuLv3 = (new MenuLv3Model())
            ->orderBy('ordering', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        $menuLv2ByHeader = [];
        foreach ($menuLv2 as $row) {
            $menuLv2ByHeader[(string) $row['header']][] = $row;
        }

        $menuLv3ByHeader = [];
        foreach ($menuLv3 as $row) {
            $menuLv3ByHeader[(string) $row['header']][] = $row;
        }

        return view('admin/settings/menus', [
            'lv1' => $menuLv1,
            'lv2_by_parent' => $menuLv2ByHeader,
            'lv3_by_parent' => $menuLv3ByHeader,
            'lv2' => $menuLv2,
            'menu_access' => [
                'edit' => $this->canManageMenu(),
            ],
        ]);
    }

    public function menusSave()
    {
        if (! $this->canManageMenu()) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Tidak memiliki akses untuk mengubah menu.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $payloadRaw = (string) $this->request->getPost('payload');
        if ($payloadRaw === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => 'Payload tidak valid.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $payload = json_decode($payloadRaw, true);
        if (! is_array($payload)) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => 'Payload tidak valid.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $db = db_connect();
        $db->transStart();

        if (! empty($payload['lv1'])) {
            foreach ($payload['lv1'] as $row) {
                $db->table('menu_lv1')
                    ->where('id', (string) ($row['id'] ?? ''))
                    ->update(['ordering' => (int) ($row['ordering'] ?? 0)]);
            }
        }

        if (! empty($payload['lv2'])) {
            foreach ($payload['lv2'] as $row) {
                $db->table('menu_lv2')
                    ->where('id', (string) ($row['id'] ?? ''))
                    ->update([
                        'ordering' => (int) ($row['ordering'] ?? 0),
                        'header' => (string) ($row['header'] ?? ''),
                    ]);
            }
        }

        if (! empty($payload['lv3'])) {
            foreach ($payload['lv3'] as $row) {
                $db->table('menu_lv3')
                    ->where('id', (string) ($row['id'] ?? ''))
                    ->update([
                        'ordering' => (int) ($row['ordering'] ?? 0),
                        'header' => (string) ($row['header'] ?? ''),
                    ]);
            }
        }

        $db->transComplete();

        if (! $db->transStatus()) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Gagal menyimpan urutan menu.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        return $this->response->setJSON([
            'status' => 'ok',
            'message' => 'Urutan menu berhasil disimpan.',
            'csrfHash' => csrf_hash(),
        ]);
    }

    public function createMenu()
    {
        if (! $this->canManageMenu()) {
            return redirect()->to('/admin/pengaturan/menus')->with('error', 'Tidak memiliki akses untuk menambah menu.');
        }

        $level = (int) $this->request->getPost('level');
        $id = trim((string) $this->request->getPost('id'));
        $label = trim((string) $this->request->getPost('label'));

        if (! in_array($level, [1, 2, 3], true) || $label === '') {
            return redirect()->to('/admin/pengaturan/menus')->withInput()->with('error', 'Data menu belum lengkap.');
        }

        $icon = trim((string) $this->request->getPost('icon'));
        $header = trim((string) $this->request->getPost('header'));

        if ($id === '') {
            $id = $this->generateMenuId($level, $header);
        }

        if ($level === 1 && strlen($id) > 11) {
            return redirect()->to('/admin/pengaturan/menus')->withInput()->with('error', 'ID menu level 1 maksimal 11 karakter.');
        }

        if ((new MenuLv1Model())->find($id) || (new MenuLv2Model())->find($id) || (new MenuLv3Model())->find($id)) {
            return redirect()->to('/admin/pengaturan/menus')->withInput()->with('error', 'ID menu sudah digunakan.');
        }

        if ($level === 1) {
            (new MenuLv1Model())->insert([
                'id' => $id,
                'label' => $label,
                'link' => '#',
                'icon' => $icon !== '' ? $icon : 'fas fa-folder',
                'old_icon' => null,
                'ordering' => $this->getNextOrdering('menu_lv1'),
            ]);
        }

        if ($level === 2) {
            if ($header === '' || ! (new MenuLv1Model())->find($header)) {
                return redirect()->to('/admin/pengaturan/menus')->withInput()->with('error', 'Parent level 1 tidak valid.');
            }

            (new MenuLv2Model())->insert([
                'id' => $id,
                'label' => $label,
                'link' => '#',
                'icon' => 'far fa-circle',
                'header' => $header,
                'ordering' => $this->getNextOrdering('menu_lv2', ['header' => $header]),
            ]);
        }

        if ($level === 3) {
            if ($header === '' || ! (new MenuLv2Model())->find($header)) {
                return redirect()->to('/admin/pengaturan/menus')->withInput()->with('error', 'Parent level 2 tidak valid.');
            }

            (new MenuLv3Model())->insert([
                'id' => $id,
                'label' => $label,
                'icon' => 'far fa-dot-circle',
                'link' => '#',
                'header' => $header,
                'ordering' => $this->getNextOrdering('menu_lv3', ['header' => $header]),
            ]);
        }

        $this->ensureMenuAksesForNewMenu($id);

        return redirect()->to('/admin/pengaturan/menus')->with('message', 'Menu baru berhasil ditambahkan.');
    }

    public function updateMenu(string $id)
    {
        if (! $this->canManageMenu()) {
            return redirect()->to('/admin/pengaturan/menus')->with('error', 'Tidak memiliki akses untuk mengubah menu.');
        }

        $levelFromPost = (int) $this->request->getPost('level');
        $level = in_array($levelFromPost, [1, 2, 3], true) ? $levelFromPost : $this->detectMenuLevelById($id);
        $label = trim((string) $this->request->getPost('label'));
        $header = trim((string) $this->request->getPost('header'));

        if (! in_array($level, [1, 2, 3], true) || $label === '') {
            return redirect()->to('/admin/pengaturan/menus')->withInput()->with('error', 'Data perubahan menu belum lengkap.');
        }

        if ($level === 1) {
            $model = new MenuLv1Model();
            if (! $model->find($id)) {
                return redirect()->to('/admin/pengaturan/menus')->with('error', 'Menu level 1 tidak ditemukan.');
            }

            $model->update($id, [
                'label' => $label,
            ]);
        }

        if ($level === 2) {
            $model = new MenuLv2Model();
            $existing = $model->find($id);
            if (! is_array($existing)) {
                return redirect()->to('/admin/pengaturan/menus')->with('error', 'Menu level 2 tidak ditemukan.');
            }

            if ($header === '' || ! (new MenuLv1Model())->find($header)) {
                return redirect()->to('/admin/pengaturan/menus')->withInput()->with('error', 'Parent level 1 tidak valid.');
            }

            $needsRename = $header !== (string) ($existing['header'] ?? '') || strncmp($id, $header . '-', strlen($header) + 1) !== 0;
            $db = db_connect();

            if ($needsRename) {
                $newId = $this->generateMenuId(2, $header);
                $db->transStart();
                $db->table('menu_lv2')->where('id', $id)->update([
                    'id' => $newId,
                    'label' => $label,
                    'header' => $header,
                ]);
                $db->table('menu_lv3')->where('header', $id)->update(['header' => $newId]);
                $db->table('menu_akses')->where('menu_id', $id)->update(['menu_id' => $newId]);
                $db->transComplete();

                if (! $db->transStatus()) {
                    return redirect()->to('/admin/pengaturan/menus')->with('error', 'Gagal memperbarui ID menu.');
                }

                return redirect()->to('/admin/pengaturan/menus')->with('message', 'Menu berhasil diperbarui.');
            }

            $model->update($id, [
                'label' => $label,
                'header' => $header,
            ]);
        }

        if ($level === 3) {
            $model = new MenuLv3Model();
            $existing = $model->find($id);
            if (! is_array($existing)) {
                return redirect()->to('/admin/pengaturan/menus')->with('error', 'Menu level 3 tidak ditemukan.');
            }

            if ($header === '' || ! (new MenuLv2Model())->find($header)) {
                return redirect()->to('/admin/pengaturan/menus')->withInput()->with('error', 'Parent level 2 tidak valid.');
            }

            $needsRename = $header !== (string) ($existing['header'] ?? '') || strncmp($id, $header . '-', strlen($header) + 1) !== 0;
            if ($needsRename) {
                $newId = $this->generateMenuId(3, $header);
                $db = db_connect();
                $db->transStart();
                $db->table('menu_lv3')->where('id', $id)->update([
                    'id' => $newId,
                    'label' => $label,
                    'header' => $header,
                ]);
                $db->table('menu_akses')->where('menu_id', $id)->update(['menu_id' => $newId]);
                $db->transComplete();

                if (! $db->transStatus()) {
                    return redirect()->to('/admin/pengaturan/menus')->with('error', 'Gagal memperbarui ID menu.');
                }

                return redirect()->to('/admin/pengaturan/menus')->with('message', 'Menu berhasil diperbarui.');
            }

            $model->update($id, [
                'label' => $label,
                'header' => $header,
            ]);
        }

        return redirect()->to('/admin/pengaturan/menus')->with('message', 'Menu berhasil diperbarui.');
    }

    public function updateMenuIcon(string $id)
    {
        if (! $this->canManageMenu()) {
            return redirect()->to('/admin/pengaturan/menus')->with('error', 'Tidak memiliki akses untuk mengubah icon menu.');
        }

        $level = (int) $this->request->getPost('level');
        $icon = trim((string) $this->request->getPost('icon'));

        if (! in_array($level, [1, 2, 3], true) || $icon === '') {
            return redirect()->to('/admin/pengaturan/menus')->with('error', 'Data ganti icon tidak valid.');
        }

        if (in_array($level, [2, 3], true)) {
            return redirect()->to('/admin/pengaturan/menus')->with('error', 'Icon level 2 dan level 3 menggunakan default sistem.');
        }

        if ($level === 1) {
            $model = new MenuLv1Model();
            if (! $model->find($id)) {
                return redirect()->to('/admin/pengaturan/menus')->with('error', 'Menu level 1 tidak ditemukan.');
            }
            $model->update($id, ['icon' => $icon]);
        }

        return redirect()->to('/admin/pengaturan/menus')->with('message', 'Icon menu berhasil diperbarui.');
    }

    public function deleteMenu(string $id)
    {
        if (! $this->canManageMenu()) {
            return redirect()->to('/admin/pengaturan/menus')->with('error', 'Tidak memiliki akses untuk menghapus menu.');
        }

        $level = (int) $this->request->getPost('level');
        if (! in_array($level, [1, 2, 3], true)) {
            return redirect()->to('/admin/pengaturan/menus')->with('error', 'Level menu tidak valid.');
        }

        if ($level === 1) {
            if ((new MenuLv2Model())->where('header', $id)->countAllResults() > 0) {
                return redirect()->to('/admin/pengaturan/menus')->with('error', 'Menu level 1 tidak bisa dihapus karena masih memiliki submenu level 2.');
            }

            (new MenuLv1Model())->delete($id);
        }

        if ($level === 2) {
            if ((new MenuLv3Model())->where('header', $id)->countAllResults() > 0) {
                return redirect()->to('/admin/pengaturan/menus')->with('error', 'Menu level 2 tidak bisa dihapus karena masih memiliki submenu level 3.');
            }

            (new MenuLv2Model())->delete($id);
        }

        if ($level === 3) {
            (new MenuLv3Model())->delete($id);
        }

        db_connect()->table('menu_akses')->where('menu_id', $id)->delete();

        return redirect()->to('/admin/pengaturan/menus')->with('message', 'Menu berhasil dihapus.');
    }

    private function canManageMenu(): bool
    {
        $role = strtolower(trim((string) session('role')));

        return in_array($role, ['admin', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
    }

    private function canAccessProductionUtilities(): bool
    {
        if (ENVIRONMENT !== 'production') {
            return false;
        }

        $role = strtolower(trim((string) session('role')));
        return in_array($role, ['super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
    }

    private function detectMenuLevelById(string $id): int
    {
        if ((new MenuLv1Model())->find($id)) {
            return 1;
        }

        if ((new MenuLv2Model())->find($id)) {
            return 2;
        }

        if ((new MenuLv3Model())->find($id)) {
            return 3;
        }

        return 0;
    }

    private function getNextOrdering(string $table, array $where = []): int
    {
        $builder = db_connect()->table($table)->selectMax('ordering', 'max_ordering');
        foreach ($where as $field => $value) {
            $builder->where($field, $value);
        }

        $row = $builder->get()->getRowArray();
        $maxOrdering = (int) ($row['max_ordering'] ?? 0);

        return $maxOrdering + 1;
    }

    private function generateMenuId(int $level, string $header = ''): string
    {
        $db = db_connect();

        if ($level === 1) {
            $maxSequence = 0;
            $rows = $db->table('menu_lv1')
                ->select('id')
                ->get()
                ->getResultArray();

            foreach ($rows as $row) {
                if (preg_match('/(\d+)$/', (string) ($row['id'] ?? ''), $matches)) {
                    $maxSequence = max($maxSequence, (int) $matches[1]);
                }
            }

            return str_pad((string) ($maxSequence + 1), 2, '0', STR_PAD_LEFT);
        }

        if ($header === '') {
            return '';
        }

        $table = $level === 2 ? 'menu_lv2' : 'menu_lv3';
        $maxSequence = 0;
        $rows = $db->table($table)
            ->select('id')
            ->where('header', $header)
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $candidateId = (string) ($row['id'] ?? '');
            $prefix = $header . '-';
            if (strpos($candidateId, $prefix) !== 0) {
                continue;
            }

            $suffix = substr($candidateId, strlen($prefix));
            if (preg_match('/^(\d+)$/', $suffix, $matches)) {
                $maxSequence = max($maxSequence, (int) $matches[1]);
            }
        }

        return $header . '-' . str_pad((string) ($maxSequence + 1), 2, '0', STR_PAD_LEFT);
    }

    private function ensureMenuAksesForNewMenu(string $menuId): void
    {
        $db = db_connect();
        if (! $db->tableExists('menu_akses')) {
            return;
        }

        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';

        $groupRows = $db->table('menu_akses')
            ->select($roleColumn)
            ->distinct()
            ->get()
            ->getResultArray();

        if ($groupRows === []) {
            $groupRows = [[$roleColumn => 1]];
        }

        foreach ($groupRows as $row) {
            $groupId = (int) ($row[$roleColumn] ?? 0);
            if ($groupId <= 0) {
                continue;
            }

            $exists = $db->table('menu_akses')
                ->where($roleColumn, $groupId)
                ->where('menu_id', $menuId)
                ->countAllResults();

            if ($exists > 0) {
                continue;
            }

            $isAdminGroup = $groupId === 1;
            $db->table('menu_akses')->insert([
                $roleColumn => $groupId,
                'menu_id' => $menuId,
                'FiturAdd' => $isAdminGroup ? 1 : 0,
                'FiturEdit' => $isAdminGroup ? 1 : 0,
                'FiturDelete' => $isAdminGroup ? 1 : 0,
                'FiturExport' => $isAdminGroup ? 1 : 0,
                'FiturImport' => $isAdminGroup ? 1 : 0,
                'FiturApproval' => $isAdminGroup ? 1 : 0,
            ]);
        }
    }

    private function getOrCreateAppSetting(AppSettingModel $model): array
    {
        $setting = $model->first();

        if (is_array($setting)) {
            return $setting;
        }

        $insertData = [
            'app_name'             => 'PLN EPM-Digi',
            'primary_color'        => '#0A66C2',
            'sidebar_bg_color'     => '#2F3A45',
            'sidebar_text_color'   => '#C2CBD5',
            'sidebar_active_bg_color' => '#0A66C2',
            'sidebar_active_text_color' => '#FFFFFF',
            'app_logo_url'         => null,
            'login_background_url' => null,
            'simak_upload_tutorial_url' => 'https://www.youtube.com/watch?v=fHQhAJ-B3qE',
            'auto_logout_minutes'  => 60,
            'preloader_duration_ms' => 500,
            'updated_at'           => date('Y-m-d H:i:s'),
            'updated_by'           => (int) session()->get('userId'),
        ];

        if ($this->hasAppSettingsColumn('maintenance_mode')) {
            $insertData['maintenance_mode'] = 0;
        }

        $id = $model->insert($insertData, true);

        return (array) $model->find((int) $id);
    }

    private function normalizeNullableText($value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function hasAppSettingsColumn(string $columnName): bool
    {
        try {
            $db = db_connect();
            return $db->tableExists('app_settings') && $db->fieldExists($columnName, 'app_settings');
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function uploadImage(string $fieldName, string $directory): ?string
    {
        $file = $this->request->getFile($fieldName);

        if (! $file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (! $file->isValid() || $file->hasMoved()) {
            return null;
        }

        $targetDir = FCPATH . 'uploads/' . $directory;
        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $newName = $file->getRandomName();
        $file->move($targetDir, $newName);

        return '/uploads/' . $directory . '/' . $newName;
    }

    private function deleteLocalImage(?string $path): void
    {
        if (empty($path) || strpos($path, '/uploads/') !== 0) {
            return;
        }

        $filePath = FCPATH . ltrim($path, '/');
        if (is_file($filePath)) {
            unlink($filePath);
        }
    }

    private function getLatestErrorLogFilename(): ?string
    {
        $logFiles = glob(WRITEPATH . 'logs/log-*.php') ?: [];

        if ($logFiles === []) {
            return null;
        }

        usort($logFiles, static function (string $a, string $b): int {
            return filemtime($b) <=> filemtime($a);
        });

        return basename($logFiles[0]);
    }

    private function getAvailableErrorLogFiles(int $limit = 3): array
    {
        $logFiles = array_merge(
            glob(WRITEPATH . 'logs/log-*.log') ?: [],
            glob(WRITEPATH . 'logs/log-*.php') ?: []
        );

        if ($logFiles === []) {
            return [];
        }

        usort($logFiles, static function (string $a, string $b): int {
            return filemtime($b) <=> filemtime($a);
        });

        $files = [];
        foreach ($logFiles as $file) {
            $base = basename($file);
            if (preg_match('/^log-\d{4}-\d{2}-\d{2}\.(log|php)$/', $base) === 1) {
                $files[] = $base;
            }
        }

        $files = array_values(array_unique($files));
        if ($limit > 0) {
            $files = array_slice($files, 0, $limit);
        }

        return $files;
    }

    private function readLogTailContent(string $filePath, int $maxLines = 1000): array
    {
        $contents = @file_get_contents($filePath);
        if (! is_string($contents) || trim($contents) === '') {
            return [
                'content' => '',
                'isTruncated' => false,
                'totalLines' => 0,
                'displayedLines' => 0,
            ];
        }

        $lines = preg_split('/\r\n|\r|\n/', trim($contents)) ?: [];
        $totalLines = count($lines);
        $displayedLines = $totalLines;
        $isTruncated = false;

        if ($maxLines > 0 && $totalLines > $maxLines) {
            $lines = array_slice($lines, -$maxLines);
            $displayedLines = count($lines);
            $isTruncated = true;
        }

        return [
            'content' => implode(PHP_EOL, $lines),
            'isTruncated' => $isTruncated,
            'totalLines' => $totalLines,
            'displayedLines' => $displayedLines,
        ];
    }

    private function runShellCommand(string $command): array
    {
        $output = [];
        $exitCode = 1;

        @exec($command, $output, $exitCode);

        return [$exitCode === 0, trim(implode(PHP_EOL, $output))];
    }

    private function shortenOutput(string $output): string
    {
        $output = trim($output);
        if ($output === '') {
            return '';
        }

        if (strlen($output) > 220) {
            $output = substr($output, 0, 220) . '...';
        }

        return 'Output: ' . $output;
    }

    private function getErrorLogContent(): string
    {
        $logFiles = glob(WRITEPATH . 'logs/log-*.php') ?: [];
        if ($logFiles === []) {
            return 'Belum ada file log error.';
        }

        usort($logFiles, static function (string $a, string $b): int {
            return filemtime($b) <=> filemtime($a);
        });

        $latestLogFile = $logFiles[0];
        $contents      = @file_get_contents($latestLogFile);

        if (! is_string($contents) || trim($contents) === '') {
            return 'File log tersedia, tetapi tidak memiliki isi.';
        }

        $lines = preg_split('/\r\n|\r|\n/', $contents) ?: [];
        $tail  = array_slice($lines, -250);

        return trim(implode(PHP_EOL, $tail));
    }
}
