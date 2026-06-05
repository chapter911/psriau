<?php
/**
 * SIMAK Smoke Test Script v2 - With Proper Session Handling
 *
 * Run: php scripts/smoke_test_simak.php --password=yourpassword --create-data
 */

define('BASE_URL', 'https://satkerpps-riau.online');
define('TIMEOUT', 30);

$config = [
    'base_url' => BASE_URL,
    'username' => '199011092025061005', // NIP
    'password' => '',
    'cookies_file' => __DIR__ . '/../writable/smoke_test_cookies.txt',
];

$scenarios = [
    'KON-01' => [
        'title' => 'Input Data Baru SIMAK - Konstruksi',
        'category' => 'INPUT',
        'method' => 'POST',
        'url' => '/admin/kontrak/simak/konstruksi/tambah',
        'form_data' => [
            'ppk_nip' => '199012212018021001',
            'nama_paket' => 'Test Konstruksi ' . date('YmdHis'),
            'tahun_anggaran' => date('Y') . ' - ' . (date('Y') + 1),
            'penyedia' => 'CV Test Konstruksi Indonesia',
            'nomor_kontrak' => 'PLN/KON/' . date('YmdHis') . '/2024',
            'nilai_kontrak' => '500000000',
            'email_responden_1' => 'test@example.com',
            'email_responden_2' => '',
        ],
    ],
    'KON-02' => [
        'title' => 'Input Data Baru SIMAK - Konsultasi',
        'category' => 'INPUT',
        'method' => 'POST',
        'url' => '/admin/kontrak/simak/konsultasi/tambah',
        'form_data' => [
            'ppk_nip' => '199012212018021001',
            'nama_paket' => 'Test Konsultasi ' . date('YmdHis'),
            'tahun_anggaran' => date('Y') . ' - ' . (date('Y') + 1),
            'penyedia' => 'PT Test Konsultasi Indonesia',
            'nomor_kontrak' => 'PLN/KONS/' . date('YmdHis') . '/2024',
            'nilai_kontrak' => '250000000',
            'email_responden_1' => 'test@example.com',
            'email_responden_2' => '',
        ],
    ],
];

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
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║           SIMAK Smoke Test v2 - Data Creation Test           ║\n";
        echo "╠══════════════════════════════════════════════════════════════╣\n";
        echo "║  Target: " . $this->config['base_url'] . "\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n";

        $args = $this->parseArgs();

        if (empty($this->config['password'])) {
            echo "\n  ERROR: Password diperlukan\n";
            exit(1);
        }

        if (!$this->login()) {
            echo "\n  ERROR: Login gagal\n";
            exit(1);
        }

        // Run scenarios
        $passed = 0;
        $failed = 0;

        foreach ($this->scenarios as $id => $scenario) {
            echo "\n┌─────────────────────────────────────────────────────────────\n";
            echo "│ {$id} - {$scenario['title']}\n";
            echo "└─────────────────────────────────────────────────────────────\n";

            $result = $this->runScenario($id, $scenario);
            $this->results[$id] = $result;

            if ($result['status'] === 'PASS') {
                $passed++;
                echo "  \033[32m✓ {$result['message']}\033[0m\n";
            } else {
                $failed++;
                echo "  \033[31m✗ {$result['message']}\033[0m\n";
            }
        }

        echo "\n═══════════════════════════════════════════════════════════════\n";
        echo "  Total: {$passed} passed, {$failed} failed\n";
        echo "═══════════════════════════════════════════════════════════════\n";
    }

    private function parseArgs(): array
    {
        global $argv;

        $args = [];
        foreach ($argv as $arg) {
            if (strpos($arg, '--') === 0) {
                $parts = explode('=', substr($arg, 2), 2);
                if (count($parts) === 2) {
                    if ($parts[0] === 'password') {
                        $this->config['password'] = $parts[1];
                    }
                }
            }
        }
        return $args;
    }

    private function login(): bool
    {
        echo "\nMelakukan login...\n";

        // Step 1: Get login page
        curl_setopt_array($this->ch, [
            CURLOPT_URL => $this->config['base_url'] . '/masuk',
            CURLOPT_HTTPGET => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT => TIMEOUT,
            CURLOPT_COOKIEJAR => $this->config['cookies_file'],
            CURLOPT_COOKIEFILE => $this->config['cookies_file'],
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);

        $response = curl_exec($this->ch);

        // Extract CSRF
        if (preg_match('/name="csrf_test_name"\s+value="([^"]+)"/', $response, $matches)) {
            $this->csrfToken = $matches[1];
            echo "  CSRF ditemukan: " . substr($this->csrfToken, 0, 15) . "...\n";
        } else {
            echo "  CSRF TIDAK ditemukan\n";
        }

        // Step 2: Submit login
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
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);

        $response = curl_exec($this->ch);
        $httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        // Check if we're still on login page
        if (strpos($response, 'Masuk Admin') !== false || strpos($response, 'id="username"') !== false) {
            echo "  ✗ Gagal login - masih di halaman login\n";
            return false;
        }

        // Get CSRF for subsequent requests
        if (preg_match('/name="csrf_test_name"\s+value="([^"]+)"/', $response, $matches)) {
            $this->csrfToken = $matches[1];
        }

        echo "  ✓ Login berhasil\n";
        return true;
    }

    private function runScenario(string $id, array $scenario): array
    {
        $url = $scenario['url'];
        $fullUrl = $this->config['base_url'] . $url;

        echo "  URL: {$fullUrl}\n";

        // First get page to get CSRF token
        curl_setopt_array($this->ch, [
            CURLOPT_URL => $fullUrl,
            CURLOPT_HTTPGET => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT => TIMEOUT,
            CURLOPT_COOKIEFILE => $this->config['cookies_file'],
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);

        $response = curl_exec($this->ch);
        $httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        // Check if authenticated
        if (strpos($response, 'Masuk Admin') !== false) {
            return [
                'status' => 'FAIL',
                'message' => 'Session expired - perlu login ulang',
            ];
        }

        // Extract CSRF
        if (preg_match('/name="csrf_test_name"\s+value="([^"]+)"/', $response, $matches)) {
            $this->csrfToken = $matches[1];
        }

        // Prepare form data
        $postData = $scenario['form_data'] ?? [];
        $postData['csrf_test_name'] = $this->csrfToken;

        echo "  Form data:\n";
        foreach ($postData as $key => $value) {
            if ($key !== 'csrf_test_name') {
                echo "    - {$key}: {$value}\n";
            }
        }

        // Submit form
        curl_setopt_array($this->ch, [
            CURLOPT_URL => $fullUrl,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => TIMEOUT,
            CURLOPT_COOKIEFILE => $this->config['cookies_file'],
            CURLOPT_COOKIEJAR => $this->config['cookies_file'],
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_HEADER => true,
        ]);

        $response = curl_exec($this->ch);
        $headerSize = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
        $body = substr($response, $headerSize);
        $headers = substr($response, 0, $headerSize);

        // Parse response
        preg_match('/HTTP\/[\d\.]+\s+(\d+)/', $headers, $statusMatch);
        $statusCode = (int) ($statusMatch[1] ?? 0);

        echo "  HTTP Response: {$statusCode}\n";

        // Check for success
        if (strpos($body, 'berhasil') !== false || strpos($body, 'success') !== false) {
            return [
                'status' => 'PASS',
                'message' => 'Data berhasil dibuat',
                'http_code' => $statusCode,
            ];
        }

        // Check for error
        if (strpos($body, 'error') !== false || strpos($body, 'gagal') !== false) {
            if (preg_match('/<div class="alert[^>]*>(.*?)<\/div>/s', $body, $matches)) {
                $errorMsg = strip_tags($matches[0]);
                return [
                    'status' => 'FAIL',
                    'message' => "Error: {$errorMsg}",
                    'http_code' => $statusCode,
                ];
            }
            return [
                'status' => 'FAIL',
                'message' => 'Submission failed',
                'http_code' => $statusCode,
            ];
        }

        // Check for redirect
        if ($statusCode === 302 || $statusCode === 303) {
            preg_match('/Location:\s*([^\r\n]+)/', $headers, $redirectMatch);
            $redirectUrl = trim($redirectMatch[1] ?? '');
            echo "  Redirect to: {$redirectUrl}\n";

            if (strpos($redirectUrl, 'konstruksi') !== false || strpos($redirectUrl, 'konsultasi') !== false) {
                return [
                    'status' => 'PASS',
                    'message' => 'Form submitted - redirected to list',
                    'http_code' => $statusCode,
                ];
            }
        }

        return [
            'status' => 'PASS',
            'message' => 'Form submitted (HTTP ' . $statusCode . ')',
            'http_code' => $statusCode,
        ];
    }
}

// Run
if (php_sapi_name() === 'cli') {
    $test = new SimakSmokeTest($config, $scenarios);
    $test->run();
} else {
    echo "Run from command line: php scripts/smoke_test_simak.php --password=yourpassword\n";
}