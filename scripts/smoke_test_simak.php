<?php
/**
 * SIMAK Smoke Test Script - With Data Creation
 *
 * Automated smoke testing for SIMAK Kontrak Konstruksi & Konsultasi
 * Can actually create test data in the database
 *
 * Run: php scripts/smoke_test_simak.php --password=yourpassword --create-data
 *
 * Options:
 *   --password=xxx           (required)
 *   --create-data            Create actual test data in database
 *   --list                   List all scenarios
 *   --scenario=KON-01        Run specific scenario only
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
    'create_data' => false,
];

// ===================== SCENARIOS =====================

$scenarios = [
    'KON-01' => [
        'title' => 'Input Data Baru SIMAK - Konstruksi',
        'category' => 'INPUT',
        'method' => 'POST',
        'url' => '/admin/kontrak/simak/konstruksi/tambah',
        'form_data' => [
            'ppk_nip' => '199012212018021001',
            'nama_paket' => 'Test Paket Konstruksi Smoke ' . date('YmdHis'),
            'tahun_anggaran' => date('Y') . ' - ' . (date('Y') + 1),
            'penyedia' => 'CV Test Konstruksi Indonesia',
            'nomor_kontrak' => 'PLN/SIMAK-KONSTRUKSI/' . date('YmdHis') . '/2024',
            'nilai_kontrak' => '500000000',
            'email_responden_1' => 'test.konstruksi@example.com',
            'email_responden_2' => 'responden.kedua@example.com',
        ],
    ],
    'KON-02' => [
        'title' => 'Input Data Baru SIMAK - Konsultasi',
        'category' => 'INPUT',
        'method' => 'POST',
        'url' => '/admin/kontrak/simak/konsultasi/tambah',
        'form_data' => [
            'ppk_nip' => '199012212018021001',
            'nama_paket' => 'Test Paket Konsultasi Smoke ' . date('YmdHis'),
            'tahun_anggaran' => date('Y') . ' - ' . (date('Y') + 1),
            'penyedia' => 'PT Test Konsultasi Indonesia',
            'nomor_kontrak' => 'PLN/SIMAK-KONSULTASI/' . date('YmdHis') . '/2024',
            'nilai_kontrak' => '250000000',
            'email_responden_1' => 'test.konsultasi@example.com',
            'email_responden_2' => 'responden.kedua@example.com',
        ],
    ],
    'KON-03' => [
        'title' => 'Create Share Link - Konstruksi',
        'category' => 'SHARE',
        'method' => 'POST',
        'url' => '/admin/kontrak/simak/konstruksi/1/share',
        'form_data' => [
            'duration' => '1week',
        ],
    ],
    'KON-05' => [
        'title' => 'Create Share Link - Konsultasi',
        'category' => 'SHARE',
        'method' => 'POST',
        'url' => '/admin/kontrak/simak/konsultasi/1/share',
        'form_data' => [
            'duration' => '30days',
        ],
    ],
    'KON-06' => [
        'title' => 'Download Template Excel - Konstruksi',
        'category' => 'UPLOAD',
        'method' => 'GET',
        'url' => '/admin/kontrak/simak/konstruksi/template',
    ],
    'KON-08' => [
        'title' => 'Import Excel - Konstruksi',
        'category' => 'UPLOAD',
        'method' => 'POST',
        'url' => '/admin/kontrak/simak/konstruksi/import',
    ],
    'KON-09' => [
        'title' => 'Import Excel - Konsultasi',
        'category' => 'UPLOAD',
        'method' => 'POST',
        'url' => '/admin/kontrak/simak/konsultasi/import',
    ],
    'KON-10' => [
        'title' => 'Akses Halaman Verifikasi - Konstruksi',
        'category' => 'VERIFY',
        'method' => 'GET',
        'url' => '/admin/kontrak/simak/konstruksi/1',
    ],
    'KON-11' => [
        'title' => 'Verifikasi Dokumen - Konstruksi',
        'category' => 'VERIFY',
        'method' => 'POST',
        'url' => '/admin/kontrak/simak/konstruksi/1/verifikasi',
    ],
    'KON-12' => [
        'title' => 'Upload Dokumen Verifikasi - Konstruksi',
        'category' => 'VERIFY',
        'method' => 'POST',
        'url' => '/admin/kontrak/simak/konstruksi/1/verifikasi/upload',
    ],
    'KON-13' => [
        'title' => 'Verifikasi Dokumen - Konsultasi',
        'category' => 'VERIFY',
        'method' => 'POST',
        'url' => '/admin/kontrak/simak/konsultasi/1/verifikasi',
    ],
    'KON-14' => [
        'title' => 'Edit Data SIMAK - Konstruksi',
        'category' => 'EDIT',
        'method' => 'POST',
        'url' => '/admin/kontrak/simak/konstruksi/1/ubah',
    ],
    'KON-15' => [
        'title' => 'Edit Data SIMAK - Konsultasi',
        'category' => 'EDIT',
        'method' => 'POST',
        'url' => '/admin/kontrak/simak/konsultasi/1/ubah',
    ],
    'KON-16' => [
        'title' => 'Export Excel - Konstruksi',
        'category' => 'EXPORT',
        'method' => 'GET',
        'url' => '/admin/kontrak/simak/konstruksi/export/excel',
    ],
    'KON-17' => [
        'title' => 'Download ZIP Dokumen - Konstruksi',
        'category' => 'EXPORT',
        'method' => 'GET',
        'url' => '/admin/kontrak/simak/konstruksi/1/export/zip',
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

        if (isset($args['list'])) {
            $this->listScenarios();
            return;
        }

        if (empty($this->config['password'])) {
            $this->printError("Password diperlukan. Gunakan --password=yourpassword");
            exit(1);
        }

        if (!$this->login()) {
            $this->printError("Login gagal. Periksa credentials.");
            exit(1);
        }

        $scenariosToRun = $this->scenarios;
        if (isset($args['scenario'])) {
            if (isset($this->scenarios[$args['scenario']])) {
                $scenariosToRun = [$args['scenario'] => $this->scenarios[$args['scenario']]];
            } else {
                $this->printError("Scenario tidak ditemukan: " . $args['scenario']);
                exit(1);
            }
        }

        $passed = 0;
        $failed = 0;
        $createdIds = [];

        foreach ($scenariosToRun as $id => $scenario) {
            echo "\n";
            $this->printScenarioHeader($id, $scenario);

            $result = $this->runScenario($id, $scenario);
            $this->results[$id] = $result;

            if ($result['status'] === 'PASS') {
                $passed++;
                $this->printSuccess("PASSED - " . $result['message']);

                // Track created data IDs
                if (isset($result['created_id'])) {
                    $createdIds[$id] = $result['created_id'];
                }
            } else {
                $failed++;
                $this->printFailed("FAILED - " . $result['message']);
            }
        }

        $this->printSummary($passed, $failed);

        // Show created data info
        if (!empty($createdIds) && $this->config['create_data']) {
            echo "\n";
            echo "📝 DATA YANG DIBUAT:\n";
            echo "====================\n";
            foreach ($createdIds as $id => $createdId) {
                echo "  {$id}: ID #{$createdId}\n";
            }
            echo "\nSilakan cek halaman SIMAK di browser untuk verifikasi data.\n";
        }
    }

    private function parseArgs(): array
    {
        global $argv;

        $args = [];
        foreach ($argv as $arg) {
            if (strpos($arg, '--') === 0) {
                $parts = explode('=', substr($arg, 2), 2);
                if (count($parts) === 2) {
                    $args[$parts[0]] = $parts[1];
                    if ($parts[0] === 'base-url') {
                        $this->config['base_url'] = $parts[1];
                    } elseif ($parts[0] === 'password') {
                        $this->config['password'] = $parts[1];
                    }
                } elseif ($arg === '--list') {
                    $args['list'] = true;
                } elseif ($arg === '--create-data') {
                    $args['create-data'] = true;
                    $this->config['create_data'] = true;
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

        // First, get the login page to extract CSRF token
        curl_setopt_array($this->ch, [
            CURLOPT_URL => $this->config['base_url'] . '/masuk',
            CURLOPT_HTTPGET => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => TIMEOUT,
            CURLOPT_COOKIEJAR => $this->config['cookies_file'],
            CURLOPT_COOKIEFILE => $this->config['cookies_file'],
            CURLOPT_USERAGENT => 'SIMAK Smoke Test/1.0',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_HEADER => true,
        ]);

        $response = curl_exec($this->ch);
        $headerSize = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
        $body = substr($response, $headerSize);
        $headers = substr($response, 0, $headerSize);

        // Extract CSRF token - try multiple formats
        if (preg_match('/name="csrf_test_name"\s+value="([^"]+)"/', $body, $matches)) {
            $this->csrfToken = $matches[1];
        } elseif (preg_match('/csrf_test_name[^>]*value="([^"]+)"/', $body, $matches)) {
            $this->csrfToken = $matches[1];
        } elseif (preg_match('/csrf_token_hash[^>]*content="([^"]+)"/', $body, $matches)) {
            $this->csrfToken = $matches[1];
        }

        if (!empty($this->csrfToken)) {
            echo "  CSRF token ditemukan: " . substr($this->csrfToken, 0, 20) . "...\n";
        } else {
            echo "  CSRF token TIDAK ditemukan\n";
        }

        // Now submit login form
        $postData = [
            'username' => $this->config['username'],
            'password' => $this->config['password'],
            'csrf_test_name' => $this->csrfToken,
        ];

        curl_setopt_array($this->ch, [
            CURLOPT_URL => $this->config['base_url'] . '/masuk',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => TIMEOUT,
            CURLOPT_COOKIEFILE => $this->config['cookies_file'],
            CURLOPT_COOKIEJAR => $this->config['cookies_file'],
            CURLOPT_USERAGENT => 'SIMAK Smoke Test/1.0',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_HEADER => false,
        ]);

        $response = curl_exec($this->ch);
        $httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        // Check for login success - redirected away from login page
        if ($httpCode >= 200 && $httpCode < 400) {
            if (strpos($response, 'Masuk') === false || strpos($response, 'admin') !== false) {
                echo "  ✓ Login berhasil.\n";
                return true;
            }
        }

        // Check for error messages
        if (strpos($response, 'password') !== false && strpos($response, 'salah') !== false) {
            echo "  ✗ Login gagal: Username atau password salah.\n";
        } else {
            echo "  Login gagal (HTTP {$httpCode}).\n";
        }
        return false;
    }

    private function runScenario(string $id, array $scenario): array
    {
        $url = str_replace('[ID]', '1', $scenario['url']);
        $fullUrl = $this->config['base_url'] . $url;

        echo "  URL: {$fullUrl}\n";
        echo "  Method: {$scenario['method']}\n";

        // Add CSRF token to form data if exists
        $postData = [];
        if (isset($scenario['form_data']) && is_array($scenario['form_data'])) {
            $postData = $scenario['form_data'];
            $postData['csrf_test_name'] = $this->csrfToken;

            if ($this->config['create_data']) {
                echo "  Sending form data:\n";
                foreach ($postData as $key => $value) {
                    if ($key !== 'csrf_token_hash') {
                        echo "    - {$key}: {$value}\n";
                    }
                }
            }
        }

        // Build request
        curl_setopt_array($this->ch, [
            CURLOPT_URL => $fullUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => TIMEOUT,
            CURLOPT_COOKIEFILE => $this->config['cookies_file'],
            CURLOPT_USERAGENT => 'SIMAK Smoke Test/1.0',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);

        if ($scenario['method'] === 'POST') {
            curl_setopt($this->ch, CURLOPT_POST, true);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        } else {
            curl_setopt($this->ch, CURLOPT_HTTPGET, true);
            curl_setopt($this->ch, CURLOPT_POST, false);
        }

        $startTime = microtime(true);
        $response = curl_exec($this->ch);
        $httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        $elapsed = round((microtime(true) - $startTime) * 1000);

        echo "  Response: HTTP {$httpCode} ({$elapsed}ms)\n";

        // Check response for success/error indicators
        $result = [
            'http_code' => $httpCode,
            'response_size' => strlen($response),
            'elapsed_ms' => $elapsed,
        ];

        // For data creation scenarios, check if data was actually created
        if ($id === 'KON-01' || $id === 'KON-02') {
            // Look for success message or created ID in response
            if (strpos($response, 'berhasil') !== false || strpos($response, 'success') !== false) {
                // Try to extract created ID from redirect URL or response
                if (preg_match('/\/(\d+)(?:\/|"|\'|>)/', $response, $matches)) {
                    $result['created_id'] = $matches[1];
                    return [
                        'status' => 'PASS',
                        'message' => "Data berhasil dibuat (ID: {$result['created_id']})",
                        'http_code' => $httpCode,
                        'response_size' => strlen($response),
                        'elapsed_ms' => $elapsed,
                        'created_id' => $result['created_id'],
                    ];
                }
                return [
                    'status' => 'PASS',
                    'message' => "Form submitted successfully",
                    'http_code' => $httpCode,
                    'response_size' => strlen($response),
                    'elapsed_ms' => $elapsed,
                ];
            }

            // Check for error messages
            if (strpos($response, 'error') !== false || strpos($response, 'gagal') !== false) {
                return [
                    'status' => 'FAIL',
                    'message' => "Form submission failed - check response",
                    'http_code' => $httpCode,
                    'response_size' => strlen($response),
                    'elapsed_ms' => $elapsed,
                ];
            }
        }

        // Default check
        if ($httpCode === 200) {
            return [
                'status' => 'PASS',
                'message' => "Response OK - " . strlen($response) . " bytes",
                'http_code' => $httpCode,
                'response_size' => strlen($response),
                'elapsed_ms' => $elapsed,
            ];
        } elseif ($httpCode === 302) {
            return [
                'status' => 'PASS',
                'message' => "Redirect - mungkin sukses",
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
        echo "║  Create Data: " . ($this->config['create_data'] ? 'YES' : 'NO') . "\n";
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
}

// ===================== RUN =====================

if (php_sapi_name() === 'cli' && isset($argv[0]) && realpath($argv[0]) === __FILE__) {
    $test = new SimakSmokeTest($config, $scenarios);
    $test->run();
} else {
    echo "Run this script from command line:\n";
    echo "  php scripts/smoke_test_simak.php --password=yourpassword\n";
    echo "  php scripts/smoke_test_simak.php --password=yourpassword --create-data\n";
    echo "\nOptions:\n";
    echo "  --list                    List all scenarios\n";
    echo "  --create-data             Actually create test data in database\n";
    echo "  --scenario=KON-01         Run specific scenario\n";
}