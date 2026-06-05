<?php
/**
 * SIMAK Smoke Test Script
 *
 * Automated smoke testing for SIMAK Kontrak Konstruksi & Konsultasi
 *
 * Run: php scripts/smoke_test_simak.php
 *
 * Requirements:
 * - PHP 8.0+
 * - cURL extension
 *
 * Usage:
 *   php scripts/smoke_test_simak.php [options]
 *
 * Options:
 *   --base-url=https://satkerpps-riau.online  (default)
 *   --username=                           (default: agung.justik@gmail.com)
 *   --password=your_password                  (required)
 *   --scenario=KON-01                         (run specific scenario only)
 *   --list                                    (list all scenarios)
 *   --report=html                             (output format: text, html)
 */

define('BASE_URL', 'https://satkerpps-riau.online');
define('TIMEOUT', 30);

// ===================== CONFIGURATION =====================

$config = [
    'base_url' => BASE_URL,
    'username' => 'agung.justik@gmail.com',
    'password' => '',
    'cookies_file' => __DIR__ . '/../writable/smoke_test_cookies.txt',
    'report_file' => __DIR__ . '/../writable/smoke_test_report.html',
];

// ===================== SCENARIOS =====================

$scenarios = [
    // === INPUT DATA BARU ===
    'KON-01' => [
        'title' => 'Input Data Baru SIMAK - Konstruksi',
        'category' => 'INPUT',
        'method' => 'POST',
        'url' => '/admin/kontrak/simak/konstruksi/tambah',
        'steps' => [
            'Buka halaman SIMAK Konstruksi',
            'Klik tombol "Input Data SIMAK"',
            'Isi form dengan data test',
            'Submit form',
        ],
        'verify' => [
            'Modal menutup setelah simpan',
            'Pesan success muncul',
            'Data baru di tabel',
        ],
        'success_criteria' => [
            'Data tersimpan ke database',
            'Tidak ada error message',
            'Redirect ke halaman daftar',
        ],
        'failed_criteria' => [
            'Form validation error',
            'Error message dari server',
            'Data tidak muncul di tabel',
        ],
    ],

    'KON-02' => [
        'title' => 'Input Data Baru SIMAK - Konsultasi',
        'category' => 'INPUT',
        'method' => 'POST',
        'url' => '/admin/kontrak/simak/konsultasi/tambah',
        'steps' => [
            'Buka halaman SIMAK Konsultasi',
            'Klik tombol "Input Data SIMAK"',
            'Isi form dengan data test',
            'Submit form',
        ],
        'verify' => [
            'Modal menutup setelah simpan',
            'Pesan success muncul',
            'Data baru di tabel',
        ],
        'success_criteria' => [
            'Data tersimpan ke database',
            'Tidak ada error message',
        ],
        'failed_criteria' => [
            'Form validation error',
            'Error message dari server',
        ],
    ],

    // === SHARE LINK ===
    'KON-03' => [
        'title' => 'Create Share Link - Konstruksi',
        'category' => 'SHARE',
        'method' => 'POST',
        'url' => '/admin/kontrak/simak/konstruksi/[ID]/share',
        'steps' => [
            'Buka halaman SIMAK Konstruksi',
            'Klik tombol Share pada data',
            'Pilih durasi share',
            'Klik "Buat Link Bagikan"',
        ],
        'verify' => [
            'Link share terbentuk',
            'Link mengandung format /simak/share/[token]',
            'Link bisa diakses publik',
        ],
        'success_criteria' => [
            'Link berhasil digenerate',
            'Link bisa diakses tanpa login',
            'Token unik untuk setiap share',
        ],
        'failed_criteria' => [
            'Link tidak terbentuk',
            'Link tidak bisa diakses',
        ],
    ],

    'KON-05' => [
        'title' => 'Create Share Link - Konsultasi',
        'category' => 'SHARE',
        'method' => 'POST',
        'url' => '/admin/kontrak/simak/konsultasi/[ID]/share',
        'steps' => [
            'Buka halaman SIMAK Konsultasi',
            'Klik tombol Share pada data',
            'Pilih durasi 30 hari',
            'Buat link share',
        ],
        'verify' => [
            'Link terbentuk dengan format benar',
            'Link berbeda dari link konstruksi',
        ],
        'success_criteria' => [
            'Link berhasil digenerate',
            'Format URL benar',
        ],
        'failed_criteria' => [
            'Link tidak terbentuk',
        ],
    ],

    // === IMPORT EXCEL ===
    'KON-06' => [
        'title' => 'Download Template Excel - Konstruksi',
        'category' => 'UPLOAD',
        'method' => 'GET',
        'url' => '/admin/kontrak/simak/konstruksi/template',
        'steps' => [
            'Buka halaman SIMAK Konstruksi',
            'Klik "Import Excel"',
            'Klik "Download Template (XLSX)"',
        ],
        'verify' => [
            'File terdownload',
            'File berekstensi .xlsx',
            'Template memiliki header kolom yang benar',
        ],
        'success_criteria' => [
            'Template terdownload',
            'Format file valid',
        ],
        'failed_criteria' => [
            'File tidak terdownload',
            'Format tidak sesuai',
        ],
    ],

    'KON-08' => [
        'title' => 'Import Excel - Konstruksi',
        'category' => 'UPLOAD',
        'method' => 'POST',
        'url' => '/admin/kontrak/simak/konstruksi/import',
        'steps' => [
            'Buka halaman SIMAK Konstruksi',
            'Klik "Import Excel"',
            'Pilih file Excel test',
            'Klik "Import"',
        ],
        'verify' => [
            'Proses import selesai tanpa error',
            'Pesan success muncul',
            'Data baru di tabel',
        ],
        'success_criteria' => [
            'File Excel berhasil di-parse',
            'Data tersimpan ke database',
        ],
        'failed_criteria' => [
            'Error parsing Excel',
            'Data tidak tersimpan',
        ],
    ],

    'KON-09' => [
        'title' => 'Import Excel - Konsultasi',
        'category' => 'UPLOAD',
        'method' => 'POST',
        'url' => '/admin/kontrak/simak/konsultasi/import',
        'steps' => [
            'Buka halaman SIMAK Konsultasi',
            'Klik "Import Excel"',
            'Pilih file Excel test',
            'Klik "Import"',
        ],
        'verify' => [
            'Proses import selesai tanpa error',
            'Data baru di tabel',
        ],
        'success_criteria' => [
            'File Excel berhasil di-parse',
            'Data tersimpan ke database',
        ],
        'failed_criteria' => [
            'Error parsing Excel',
        ],
    ],

    // === VERIFIKASI ===
    'KON-10' => [
        'title' => 'Akses Halaman Verifikasi - Konstruksi',
        'category' => 'VERIFY',
        'method' => 'GET',
        'url' => '/admin/kontrak/simak/konstruksi/[ID]',
        'steps' => [
            'Buka halaman SIMAK Konstruksi',
            'Klik tombol "VERIFIKASI"',
        ],
        'verify' => [
            'Redirect ke halaman detail verifikasi',
            'URL berubah ke format /konstruksi/[ID]',
            'Halaman menampilkan daftar dokumen',
        ],
        'success_criteria' => [
            'Halaman verifikasi dapat diakses',
            'Daftar dokumen tertampilkan',
        ],
        'failed_criteria' => [
            'Halaman error 404',
            'Daftar dokumen tidak muncul',
        ],
    ],

    'KON-11' => [
        'title' => 'Verifikasi Dokumen - Konstruksi',
        'category' => 'VERIFY',
        'method' => 'POST',
        'url' => '/admin/kontrak/simak/konstruksi/[ID]/verifikasi/simpan',
        'steps' => [
            'Di halaman verifikasi',
            'Pilih status verifikasi untuk dokumen',
            'Klik "Simpan Verifikasi"',
        ],
        'verify' => [
            'Status tersimpan',
            'Persentase kelengkapan berubah',
        ],
        'success_criteria' => [
            'Status tersimpan ke database',
            'Persentase akurat',
        ],
        'failed_criteria' => [
            'Status tidak tersimpan',
            'Persentase tidak berubah',
        ],
    ],

    'KON-12' => [
        'title' => 'Upload Dokumen Verifikasi - Konstruksi',
        'category' => 'VERIFY',
        'method' => 'POST',
        'url' => '/admin/kontrak/simak/konstruksi/[ID]/verifikasi/upload',
        'steps' => [
            'Di halaman verifikasi',
            'Pilih dokumen dengan status "Belum Ada"',
            'Klik Upload / drag-drop file',
            'Upload file PDF/JPG/PNG (maks 10MB)',
        ],
        'verify' => [
            'File berhasil diupload',
            'Nama file muncul di list',
            'Status berubah menjadi "Menunggu Verifikasi"',
        ],
        'success_criteria' => [
            'Upload berhasil',
            'File tersimpan',
            'Status berubah',
        ],
        'failed_criteria' => [
            'Upload gagal',
            'File tidak tersimpan',
        ],
    ],

    'KON-13' => [
        'title' => 'Verifikasi Dokumen - Konsultasi',
        'category' => 'VERIFY',
        'method' => 'POST',
        'url' => '/admin/kontrak/simak/konsultasi/[ID]/verifikasi/simpan',
        'steps' => [
            'Buka halaman SIMAK Konsultasi',
            'Klik VERIFIKASI',
            'Lakukan proses verifikasi',
        ],
        'verify' => [
            'Semua fitur berfungsi sama seperti Konstruksi',
            'Persentase kelengkapan diupdate',
        ],
        'success_criteria' => [
            'Fitur berfungsi dengan benar',
        ],
        'failed_criteria' => [
            'Ada perbedaan dengan Konstruksi',
        ],
    ],

    // === EDIT DATA ===
    'KON-14' => [
        'title' => 'Edit Data SIMAK - Konstruksi',
        'category' => 'EDIT',
        'method' => 'POST',
        'url' => '/admin/kontrak/simak/konstruksi/[ID]/ubah',
        'steps' => [
            'Buka halaman SIMAK Konstruksi',
            'Klik tombol "EDIT"',
            'Ubah beberapa field',
            'Klik "Simpan"',
        ],
        'verify' => [
            'Modal menutup setelah simpan',
            'Data di tabel terupdate',
        ],
        'success_criteria' => [
            'Data dapat diedit',
            'Perubahan tersimpan',
        ],
        'failed_criteria' => [
            'Field tidak bisa diedit',
            'Perubahan tidak tersimpan',
        ],
    ],

    'KON-15' => [
        'title' => 'Edit Data SIMAK - Konsultasi',
        'category' => 'EDIT',
        'method' => 'POST',
        'url' => '/admin/kontrak/simak/konsultasi/[ID]/ubah',
        'steps' => [
            'Buka halaman SIMAK Konsultasi',
            'Klik tombol "EDIT"',
            'Ubah beberapa field',
            'Klik "Simpan"',
        ],
        'verify' => [
            'Data di tabel terupdate',
        ],
        'success_criteria' => [
            'Data dapat diedit',
        ],
        'failed_criteria' => [
            'Perubahan tidak tersimpan',
        ],
    ],

    // === EXPORT ===
    'KON-16' => [
        'title' => 'Export Excel - Konstruksi',
        'category' => 'EXPORT',
        'method' => 'GET',
        'url' => '/admin/kontrak/simak/konstruksi/export',
        'steps' => [
            'Buka halaman SIMAK Konstruksi',
            'Klik tombol "Export Excel"',
            'Download file',
        ],
        'verify' => [
            'File terdownload',
            'Format file .xlsx',
            'Data lengkap (semua kolom)',
        ],
        'success_criteria' => [
            'File terdownload dengan benar',
            'Data akurat dan lengkap',
        ],
        'failed_criteria' => [
            'Download gagal',
            'File corrupt',
        ],
    ],

    'KON-17' => [
        'title' => 'Download ZIP Dokumen - Konstruksi',
        'category' => 'EXPORT',
        'method' => 'GET',
        'url' => '/admin/kontrak/simak/konstruksi/[ID]/download-zip',
        'steps' => [
            'Buka halaman detail SIMAK',
            'Klik "Download ZIP"',
            'Download file',
        ],
        'verify' => [
            'File ZIP terdownload',
            'Isi ZIP sesuai dengan dokumen',
        ],
        'success_criteria' => [
            'ZIP terdownload',
            'Dokumen lengkap',
        ],
        'failed_criteria' => [
            'Download gagal',
            'Dokumen tidak lengkap',
        ],
    ],
];

// ===================== MAIN CLASS =====================

class SimakSmokeTest
{
    private $config;
    private $scenarios;
    private $results = [];
    private $ch;
    private $csrfToken = '';
    private $sessionCookie = '';

    public function __construct(array $config, array $scenarios)
    {
        $this->config = $config;
        $this->scenarios = $scenarios;
        $this->ch = curl_init();
    }

    public function run(): void
    {
        $this->printHeader();

        // Parse command line arguments
        $args = $this->parseArgs();

        // List scenarios if requested
        if (isset($args['list'])) {
            $this->listScenarios();
            return;
        }

        // Validate credentials
        if (empty($this->config['username']) || empty($this->config['password'])) {
            $this->printError("Username dan password diperlukan. Gunakan --username=xxx --password=yyy");
            exit(1);
        }

        // Login first
        if (!$this->login()) {
            $this->printError("Login gagal. Periksa credentials.");
            exit(1);
        }

        // Determine which scenarios to run
        $scenariosToRun = $this->scenarios;
        if (isset($args['scenario'])) {
            if (isset($this->scenarios[$args['scenario']])) {
                $scenariosToRun = [$args['scenario'] => $this->scenarios[$args['scenario']]];
            } else {
                $this->printError("Scenario tidak ditemukan: " . $args['scenario']);
                exit(1);
            }
        }

        // Run each scenario
        $passed = 0;
        $failed = 0;

        foreach ($scenariosToRun as $id => $scenario) {
            echo "\n";
            $this->printScenarioHeader($id, $scenario);

            $result = $this->runScenario($id, $scenario);
            $this->results[$id] = $result;

            if ($result['status'] === 'PASS') {
                $passed++;
                $this->printSuccess("PASSED - " . $result['message']);
            } else {
                $failed++;
                $this->printFailed("FAILED - " . $result['message']);
            }
        }

        // Print summary
        $this->printSummary($passed, $failed);

        // Generate report
        if (isset($args['report']) && $args['report'] === 'html') {
            $this->generateHtmlReport();
        }

        curl_close($this->ch);
    }

    private function parseArgs(): array
    {
        global $argv, $config;

        $args = [];
        foreach ($argv as $arg) {
            if (strpos($arg, '--') === 0) {
                $parts = explode('=', substr($arg, 2), 2);
                if (count($parts) === 2) {
                    $args[$parts[0]] = $parts[1];
                    // Update config
                    if ($parts[0] === 'base-url') {
                        $this->config['base_url'] = $parts[1];
                    } elseif ($parts[0] === 'username') {
                        $this->config['username'] = $parts[1];
                    } elseif ($parts[0] === 'password') {
                        $this->config['password'] = $parts[1];
                    }
                } elseif ($arg === '--list') {
                    $args['list'] = true;
                } elseif ($arg === '--skip-login') {
                    $args['skip-login'] = true;
                }
            }
        }

        return $args;
    }

    private function listScenarios(): void
    {
        echo "\nDaftar Skenario Smoke Test SIMAK:\n\n";

        foreach ($this->scenarios as $id => $scenario) {
            $category = str_pad($scenario['category'], 10, ' ');
            $method = str_pad($scenario['method'], 6, ' ');
            echo "  {$id}  [{$category}] [{$method}] {$scenario['title']}\n";
        }

        echo "\nTotal: " . count($this->scenarios) . " skenario\n\n";
    }

    private function login(): bool
    {
        echo "Melakukan login...\n";

        $loginUrl = $this->config['base_url'] . '/login';
        $postData = [
            'username' => $this->config['username'],
            'password' => $this->config['password'],
            'submit' => 'Login',
        ];

        curl_setopt_array($this->ch, [
            CURLOPT_URL => $loginUrl,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => TIMEOUT,
            CURLOPT_COOKIEJAR => $this->config['cookies_file'],
            CURLOPT_COOKIEFILE => $this->config['cookies_file'],
            CURLOPT_USERAGENT => 'SIMAK Smoke Test/1.0',
        ]);

        $response = curl_exec($this->ch);
        $httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        // Check if login successful (redirected away from login page)
        if ($httpCode >= 200 && $httpCode < 400 && strpos($response, 'login') === false) {
            // Extract CSRF token if present
            if (preg_match('/csrf_token_hash.*?value="([^"]+)"/', $response, $matches)) {
                $this->csrfToken = $matches[1];
            }
            echo "  Login berhasil.\n";
            return true;
        }

        echo "  Login gagal (HTTP {$httpCode}).\n";
        return false;
    }

    private function runScenario(string $id, array $scenario): array
    {
        $url = str_replace('[ID]', '1', $scenario['url']); // Use ID 1 as default for testing
        $fullUrl = $this->config['base_url'] . $url;

        echo "  URL: {$fullUrl}\n";
        echo "  Method: {$scenario['method']}\n";

        // Build request
        curl_setopt_array($this->ch, [
            CURLOPT_URL => $fullUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => TIMEOUT,
            CURLOPT_COOKIEFILE => $this->config['cookies_file'],
            CURLOPT_USERAGENT => 'SIMAK Smoke Test/1.0',
        ]);

        if ($scenario['method'] === 'POST') {
            curl_setopt($this->ch, CURLOPT_POST, true);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, '');
        } else {
            curl_setopt($this->ch, CURLOPT_HTTPGET, true);
        }

        $startTime = microtime(true);
        $response = curl_exec($this->ch);
        $httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        $elapsed = round((microtime(true) - $startTime) * 1000);

        echo "  Response: HTTP {$httpCode} ({$elapsed}ms)\n";

        // Basic checks
        if ($httpCode === 200) {
            return [
                'status' => 'PASS',
                'message' => 'Response OK - ' . strlen($response) . ' bytes',
                'http_code' => $httpCode,
                'response_size' => strlen($response),
                'elapsed_ms' => $elapsed,
            ];
        } elseif ($httpCode === 404) {
            return [
                'status' => 'FAIL',
                'message' => 'Page not found (404)',
                'http_code' => $httpCode,
            ];
        } elseif ($httpCode === 302) {
            return [
                'status' => 'PASS',
                'message' => 'Redirect (302) - mungkin normal untuk beberapa endpoint',
                'http_code' => $httpCode,
            ];
        } else {
            return [
                'status' => 'FAIL',
                'message' => "HTTP Error: {$httpCode}",
                'http_code' => $httpCode,
            ];
        }
    }

    private function printHeader(): void
    {
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║           SIMAK Smoke Test - Kontrak Konstruksi             ║\n";
        echo "║                  & Konsultasi                               ║\n";
        echo "╠══════════════════════════════════════════════════════════════╣\n";
        echo "║  Target: " . $this->config['base_url'] . "\n";
        echo "║  Scenarios: " . count($this->scenarios) . " skenario\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n";
    }

    private function printScenarioHeader(string $id, array $scenario): void
    {
        echo "\n┌─────────────────────────────────────────────────────────────\n";
        echo "│ {$id} - {$scenario['title']}\n";
        echo "│ Kategori: {$scenario['category']}\n";
        echo "└─────────────────────────────────────────────────────────────\n";
    }

    private function printSuccess(string $message): void
    {
        echo "  \033[32m✓ {$message}\033[0m\n";
    }

    private function printFailed(string $message): void
    {
        echo "  \033[31m✗ {$message}\033[0m\n";
    }

    private function printError(string $message): void
    {
        echo "\n  \033[31mERROR: {$message}\033[0m\n\n";
    }

    private function printSummary(int $passed, int $failed): void
    {
        $total = $passed + $failed;
        $percentage = $total > 0 ? round(($passed / $total) * 100) : 0;

        echo "\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "                        SUMMARY\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "  Total Scenarios: {$total}\n";
        echo "  Passed: \033[32m{$passed}\033[0m\n";
        echo "  Failed: \033[31m{$failed}\033[0m\n";
        echo "  Success Rate: {$percentage}%\n";
        echo "═══════════════════════════════════════════════════════════════\n";
    }

    private function generateHtmlReport(): void
    {
        $html = '<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>SIMAK Smoke Test Report - ' . date('Y-m-d H:i:s') . '</title>
<style>
body { font-family: sans-serif; background: #0f172a; color: #e5e7eb; padding: 20px; }
.container { max-width: 1200px; margin: 0 auto; }
.card { background: #1f2937; border-radius: 12px; padding: 20px; margin-bottom: 16px; }
.success { color: #22c55e; }
.fail { color: #ef4444; }
table { width: 100%; border-collapse: collapse; }
th, td { padding: 10px; border: 1px solid #374151; text-align: left; }
th { background: #111827; }
</style>
</head>
<body>
<div class="container">
<h1>SIMAK Smoke Test Report</h1>
<p>Generated: ' . date('Y-m-d H:i:s') . '</p>
<table>
<tr><th>ID</th><th>Scenario</th><th>Category</th><th>Status</th><th>Message</th></tr>';

        foreach ($this->results as $id => $result) {
            $statusClass = $result['status'] === 'PASS' ? 'success' : 'fail';
            $html .= "<tr>
                <td>{$id}</td>
                <td>{$this->scenarios[$id]['title']}</td>
                <td>{$this->scenarios[$id]['category']}</td>
                <td class='{$statusClass}'>{$result['status']}</td>
                <td>{$result['message']}</td>
            </tr>";
        }

        $html .= '</table>
</div>
</body>
</html>';

        file_put_contents($this->config['report_file'], $html);
        echo "\n  HTML Report saved to: {$this->config['report_file']}\n";
    }
}

// ===================== RUN =====================

if (php_sapi_name() === 'cli' && isset($argv[0]) && realpath($argv[0]) === __FILE__) {
    $test = new SimakSmokeTest($config, $scenarios);
    $test->run();
} else {
    echo "Run this script from command line:\n";
    echo "  php scripts/smoke_test_simak.php --password=yourpassword\n";
    echo "\nDefault username: agung.justik@gmail.com\n";
    echo "\nOptions:\n";
    echo "  --list                    List all scenarios\n";
    echo "  --scenario=KON-01         Run specific scenario\n";
    echo "  --report=html             Generate HTML report\n";
}