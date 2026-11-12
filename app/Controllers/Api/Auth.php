<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\LoginHistoryModel;
use App\Models\UserModel;
use App\Models\UserTokenModel;
use App\Services\ApiAuth;
use CodeIgniter\HTTP\ResponseInterface;

class Auth extends BaseController
{
    public function login()
    {
        $usernameInput = '';
        $passwordInput = '';
        $deviceName = 'API Token';

        // Support both application/json and form-data/x-www-form-urlencoded
        $contentType = (string) $this->request->getHeaderLine('Content-Type');
        if (strpos($contentType, 'application/json') !== false) {
            $json = $this->request->getJSON(true);
            $usernameInput = trim((string) ($json['username'] ?? ''));
            $passwordInput = (string) ($json['password'] ?? '');
            $deviceName = trim((string) ($json['device_name'] ?? 'API Token'));
        } else {
            $usernameInput = trim((string) $this->request->getPost('username'));
            $passwordInput = (string) $this->request->getPost('password');
            $deviceName = trim((string) $this->request->getPost('device_name'));
        }

        $deviceName = $deviceName ?: 'API Token';

        // Rate Limiting / Brute-force Protection via Throttler (Maks 5 percobaan gagal per 15 menit per IP)
        $ip = (string) ($this->request->getIPAddress() ?? '');
        $throttleKey = md5('login_api_' . $ip);
        try {
            $throttler = service('throttler');
            if ($throttler->check($throttleKey, 5, 900) === false) {
                $seconds = $throttler->getTokenTime();
                $minutes = (int) ceil($seconds / 60);
                $context = $this->buildLoginContext($usernameInput, $passwordInput);
                $this->logLoginAttempt(false, 'rate_limited', $context, null);

                return $this->response
                    ->setStatusCode(ResponseInterface::HTTP_TOO_MANY_REQUESTS)
                    ->setHeader('Retry-After', (string) $seconds)
                    ->setJSON([
                        'status' => 'error',
                        'message' => "Terlalu banyak percobaan login gagal dari IP Anda ({$ip}). Silakan coba lagi dalam {$minutes} menit ({$seconds} detik).",
                        'retry_after' => $seconds,
                    ]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'API Login Throttler error: ' . $e->getMessage());
        }

        // 1. Validation
        if ($usernameInput === '' || $passwordInput === '') {
            $context = $this->buildLoginContext($usernameInput, $passwordInput);
            $this->logLoginAttempt(false, 'validation_failed', $context, null);

            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setJSON([
                'status' => 'error',
                'message' => 'Username dan password wajib diisi.'
            ]);
        }

        // 2. Fetch User
        $userModel = new UserModel();
        $user = $userModel->where('username', $usernameInput)->first();

        // 3. Verify Password
        if (! $user || ! password_verify($passwordInput, $user['password_hash'])) {
            $context = $this->buildLoginContext($usernameInput, $passwordInput);
            $this->logLoginAttempt(false, 'invalid_credentials', $context, is_array($user) ? $user : null);

            return $this->response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)->setJSON([
                'status' => 'error',
                'message' => 'Kredensial login tidak valid.'
            ]);
        }

        // 4. Verify Active Status
        if ((int) ($user['is_active'] ?? 1) !== 1) {
            $context = $this->buildLoginContext($usernameInput, $passwordInput);
            $this->logLoginAttempt(false, 'inactive_account', $context, $user);

            return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                'status' => 'error',
                'message' => 'Akun Anda nonaktif. Hubungi administrator untuk aktivasi.'
            ]);
        }

        // 4b. Verify Mobile Access
        if ((int) ($user['akses_mobile'] ?? 1) !== 1) {
            $context = $this->buildLoginContext($usernameInput, $passwordInput);
            $this->logLoginAttempt(false, 'no_mobile_access', $context, $user);

            return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                'status' => 'error',
                'message' => 'Akun Anda tidak memiliki akses ke platform Mobile.'
            ]);
        }

        // 5. Generate and Save Token
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);

        $tokenModel = new UserTokenModel();
        $tokenModel->insert([
            'user_id' => $user['id'],
            'token_hash' => $tokenHash,
            'name' => $deviceName,
            'expires_at' => null, // Optional: date('Y-m-d H:i:s', strtotime('+30 days'))
        ]);

        // 6. Log Success
        $context = $this->buildLoginContext($usernameInput, $passwordInput);
        $this->logLoginAttempt(true, null, $context, $user);

        // Reset throttle counter on success
        try {
            service('throttler')->remove($throttleKey);
        } catch (\Throwable $e) {
            // Ignore throttler cache reset error
        }

        $pegawai = $this->resolvePegawaiData($user);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Login berhasil.',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => (int) $user['id'],
                    'username' => $user['username'],
                    'full_name' => $user['full_name'],
                    'role' => $user['role'] ?? 'editor',
                    'pegawai' => $pegawai,
                ]
            ]
        ]);
    }

    public function logout()
    {
        $token = ApiAuth::getToken();
        if ($token) {
            $tokenHash = hash('sha256', $token);
            $tokenModel = new UserTokenModel();
            $tokenModel->where('token_hash', $tokenHash)->delete();
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Logout berhasil. Token telah dicabut.'
        ]);
    }

    public function profile()
    {
        $user = ApiAuth::getUser();
        $pegawai = $this->resolvePegawaiData($user);

        return $this->response->setJSON([
            'status' => 'success',
            'data' => [
                'user' => [
                    'id' => (int) $user['id'],
                    'username' => $user['username'],
                    'full_name' => $user['full_name'],
                    'role' => $user['role'] ?? 'editor',
                    'is_active' => (int) ($user['is_active'] ?? 1),
                    'created_at' => $user['created_at'] ?? null,
                    'updated_at' => $user['updated_at'] ?? null,
                    'pegawai' => $pegawai,
                ]
            ]
        ]);
    }

    private function resolvePegawaiData(array $user): ?array
    {
        try {
            $db = db_connect();
            if (! $db->tableExists('mst_pegawai')) {
                return null;
            }

            $username = strtolower(trim((string) ($user['username'] ?? '')));
            $fullName = strtolower(trim((string) ($user['full_name'] ?? '')));

            $builder = $db->table('mst_pegawai p')
                ->select('p.id, p.nip, p.nama, p.email, p.jenis_pegawai, p.foto, p.eselon, p.golongan, p.masa_kerja, p.is_active');

            if ($db->tableExists('mst_jabatan')) {
                $builder->select('ju.jabatan AS jabatan_utama, jp.jabatan AS jabatan_perbendaharaan')
                    ->join('mst_jabatan ju', 'ju.id = p.jabatan_utama_id', 'left')
                    ->join('mst_jabatan jp', 'jp.id = p.jabatan_perbendaharaan_id', 'left');
            }

            $builder->groupStart();
            if ($username !== '') {
                $builder->where('LOWER(p.nip)', $username);
            }
            if ($fullName !== '') {
                $username !== ''
                    ? $builder->orWhere('LOWER(p.nama)', $fullName)
                    : $builder->where('LOWER(p.nama)', $fullName);
            }
            $builder->groupEnd();

            if ($username === '' && $fullName === '') {
                return null;
            }

            $pegawai = $builder->orderBy('p.id', 'ASC')->get()->getRowArray();
            if (! $pegawai) {
                return null;
            }

            $foto = trim((string) ($pegawai['foto'] ?? ''));
            $fotoUrl = null;
            if ($foto !== '') {
                $fotoUrl = preg_match('/^https?:\/\//i', $foto)
                    ? $foto
                    : base_url(ltrim($foto, '/'));
            }

            return [
                'id' => (int) $pegawai['id'],
                'nip' => $pegawai['nip'] ?? null,
                'nama' => $pegawai['nama'] ?? null,
                'email' => $pegawai['email'] ?? null,
                'jenis_pegawai' => $pegawai['jenis_pegawai'] ?? null,
                'jabatan_utama' => $pegawai['jabatan_utama'] ?? null,
                'jabatan_perbendaharaan' => $pegawai['jabatan_perbendaharaan'] ?? null,
                'eselon' => $pegawai['eselon'] ?? null,
                'golongan' => $pegawai['golongan'] ?? null,
                'masa_kerja' => $pegawai['masa_kerja'] ?? null,
                'foto' => $foto !== '' ? $foto : null,
                'foto_url' => $fotoUrl,
                'is_active' => (int) ($pegawai['is_active'] ?? 1),
            ];
        } catch (\Throwable $e) {
            log_message('error', 'API resolvePegawaiData error: ' . $e->getMessage());
            return null;
        }
    }

    private function buildLoginContext(string $usernameInput, string $passwordInput): array
    {
        return [
            'username_input' => $usernameInput,
            'ip_address' => (string) ($this->request->getIPAddress() ?? ''),
            'user_agent' => substr((string) $this->request->getUserAgent(), 0, 255),
            'http_method' => strtoupper((string) $this->request->getMethod()),
            'request_path' => trim((string) $this->request->getUri()->getPath(), '/'),
            'referer' => substr((string) ($this->request->getServer('HTTP_REFERER') ?? ''), 0, 255),
            'session_id' => substr((string) session_id(), 0, 128),
            'request_payload' => [
                'username' => $usernameInput,
                'password_provided' => trim($passwordInput) !== '',
            ],
            'server_context' => [
                'host' => (string) ($this->request->getServer('HTTP_HOST') ?? ''),
                'forwarded_for' => (string) ($this->request->getServer('HTTP_X_FORWARDED_FOR') ?? ''),
                'forwarded_proto' => (string) ($this->request->getServer('HTTP_X_FORWARDED_PROTO') ?? ''),
                'remote_port' => (string) ($this->request->getServer('REMOTE_PORT') ?? ''),
            ],
        ];
    }

    private function logLoginAttempt(bool $isSuccess, ?string $failureReason, array $context, ?array $user): void
    {
        try {
            $db = db_connect();
            if (! $db->tableExists('login_histories')) {
                return;
            }

            (new LoginHistoryModel())->insert([
                'attempted_at' => date('Y-m-d H:i:s'),
                'is_success' => $isSuccess ? 1 : 0,
                'failure_reason' => $failureReason,
                'username_input' => (string) ($context['username_input'] ?? ''),
                'user_id' => is_array($user) ? (int) ($user['id'] ?? 0) : null,
                'full_name' => is_array($user) ? (string) ($user['full_name'] ?? '') : null,
                'role' => is_array($user) ? (string) ($user['role'] ?? '') : null,
                'account_active' => is_array($user) ? (int) ($user['is_active'] ?? 0) : null,
                'ip_address' => (string) ($context['ip_address'] ?? ''),
                'user_agent' => (string) ($context['user_agent'] ?? ''),
                'http_method' => (string) ($context['http_method'] ?? ''),
                'request_path' => (string) ($context['request_path'] ?? ''),
                'referer' => (string) ($context['referer'] ?? ''),
                'session_id' => (string) ($context['session_id'] ?? ''),
                'request_payload_json' => json_encode($context['request_payload'] ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'server_context_json' => json_encode($context['server_context'] ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            // Logging must not break authentication flow.
        }
    }
}
